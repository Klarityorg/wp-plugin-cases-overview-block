<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function cases_overview_block_assets() {
	wp_enqueue_style(
		'cases-overview-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', __DIR__),
		['wp-editor']
	);
}

add_action( 'enqueue_block_assets', 'cases_overview_block_assets');

function cases_overview_editor_assets() { // phpcs:ignore
	wp_enqueue_script(
		'cases-overview-block-js',
		plugins_url( '/dist/blocks.build.js', __DIR__),
		['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'],
    filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' )
	);

	wp_enqueue_style(
		'cases-overview-editor-css',
		plugins_url( 'dist/blocks.editor.build.css', __DIR__),
		['wp-edit-blocks'],
    filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
	);
}

add_action( 'enqueue_block_editor_assets', 'cases_overview_editor_assets' );

function get_cases($parentId = null, $requiredMeta = []) {
    $args = [
        'post_type' => 'page',
        'sort_column' => 'menu_order',
        'hierarchical' => 0,
        'echo' => 0
    ];
    if (!is_null($parentId)) {
        $args['parent'] = $parentId;
    }
    if (count($requiredMeta) === 1) {
        $args['meta_key'] = array_keys($requiredMeta)[0];
        $args['meta_value'] = array_values($requiredMeta)[0];
    }
    return get_pages($args);
}

function cases_overview_list($attributes) {
	global $post;
	$isEditContext = isset($_GET['context']) && $_GET['context'] === 'edit';
	$layoutType = $attributes['layout'] ?? '';
	$filter = $attributes['filter'] ?? '';
	$showUnresolved = $attributes['showUnresolved'] ?? false;
	$showResolved = $attributes['showResolved'] ?? false;
	$parent = $filter === 'in_sub_pages' ? $post->ID : null;

    $childpages = array_merge(
        $showUnresolved ? get_cases($parent, ['case_status' => 'new']) : [],
        $showUnresolved ? get_cases($parent, ['case_status' => 'ongoing']) : [],
        $showUnresolved ? get_cases($parent, ['case_status' => 'update']) : [],
        $showResolved ? get_cases($parent, ['case_status' => 'resolved']) : []
    );

	if (count($childpages) > 0) {
        $headerTag = $layoutType === 'case_list' ? 'h3' : 'h4';
        return '<div class="wp-block-klarity-klarity-cases-overview-block row '.$layoutType.'">'
        .implode(
            '',
            array_map(function($page) use($headerTag, $isEditContext) {
                $metadata = get_post_meta($page->ID);

                $headline = isset($metadata['headline'])
                    ? '<div class="headline">'.$metadata['headline'][0].'</div>'
                    : '';
                $markAsNew = isset($metadata['case_status']) && $metadata['case_status'][0] === 'new'
                    ? '<div class="new">'.__('new').'</div>'
                    : '';
                $markAsUpdate = isset($metadata['case_status']) && $metadata['case_status'][0] === 'update'
                    ? '<div class="update">'.__('update').'</div>'
                    : '';

                $shortDescription = get_post_meta($page->ID, 'short_description', true);
                $link = $isEditContext ? 'javascript:void(0)' : get_permalink($page);

                preg_match('/videoThumbnail":"(.+)"/', $page->post_content, $matches);
                $image = $matches[1]
                    ?? wp_get_attachment_image_src(get_post_thumbnail_id($page->ID), 'single-post-thumbnail')[0]
                    ?? 'http://placehold.it/200x200';

                return "
                <div class='col s12 m6'>
                    <a href='$link'>
                        <div class='card'>
                            $markAsNew
                            $markAsUpdate
                            <div class='card-image' style=\"background-image: url('$image')\"></div>
                            <div class='card-content'>
                              $headline
                              <$headerTag>{$page->post_title}</$headerTag>
                              <div class='separator'></div>
                              <div class='description'>$shortDescription</div>
                            </div>
                        </div>
                    </a>
                </div>";
            }, $childpages))
        .'</div>';
    }
    switch($filter) {
        case 'all':
            return __('No pages matched. Make sure that the metadata "case_status" is set on the cases that you want to show here.');
            break;
        case 'in_sub_pages':
            return __('No child-pages. Make sure that this page has child pages and that these child pages have the metadata "case_status" set.');
            break;
    }
	return '';
}

function register_block_callback() {
    if(function_exists( 'register_block_type' ) ) {
        register_block_type( 'klarity/klarity-cases-overview-block', [
            'render_callback' => 'cases_overview_list',
            'attributes' => [
                'layout' => [
                    'type' => 'string',
                    'default' => 'case_list',
                ],
                'filter' => [
                    'type' => 'string',
                    'default' => 'in_sub_pages',
                ],
                'showUnresolved' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'showResolved' => [
                    'type' => 'boolean',
                    'default' => true,
                ]
            ]
        ]);
    }
}
add_action( 'plugins_loaded', 'register_block_callback');

add_shortcode('cases-overview', 'cases_overview_list');

<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function klarity_cases_overview_block_assets() {
  wp_enqueue_style(
    'cases-overview-style-css', // Handle.
    plugins_url('dist/blocks.style.build.css', __DIR__),
    ['wp-editor']
  );
}

add_action('enqueue_block_assets', 'klarity_cases_overview_block_assets');

function klarity_cases_overview_editor_assets() { // phpcs:ignore
  wp_enqueue_script(
    'cases-overview-block-js',
    plugins_url('/dist/blocks.build.js', __DIR__),
    ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'],
    filemtime(plugin_dir_path(__DIR__) . 'dist/blocks.build.js')
  );

  wp_enqueue_style(
    'cases-overview-editor-css',
    plugins_url('dist/blocks.editor.build.css', __DIR__),
    ['wp-edit-blocks'],
    filemtime(plugin_dir_path(__DIR__) . 'dist/blocks.editor.build.css')
  );
}

add_action('enqueue_block_editor_assets', 'klarity_cases_overview_editor_assets');

function get_page_title_for_slug($page_slug) {
  $page = get_page_by_path($page_slug, OBJECT);
  return isset($page);
}

function get_klarity_cases($parentId = null, $requiredMeta = []) {
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

function render_klarity_cases_overview_list($attributes) {

  wp_enqueue_script(
    'case_overview_see_all_see_less-js',
    plugins_url('/src/block/see-all-see-less.js', __DIR__),
    [],
    true
  );

  global $post;
  $layoutType = $attributes['layout'] ?? '';
  $filter = $attributes['filter'] ?? '';
  $showUnresolved = $attributes['showUnresolved'] ?? false;
  $showResolved = $attributes['showResolved'] ?? false;
  $parent = $filter === 'in_sub_pages' ? $post->ID : null;

  $childpages = array_merge(
    $showUnresolved ? get_klarity_cases($parent, ['case_status' => 'new']) : [],
    $showUnresolved ? get_klarity_cases($parent, ['case_status' => 'ongoing']) : [],
    $showUnresolved ? get_klarity_cases($parent, ['case_status' => 'update']) : [],
    $showResolved ? get_klarity_cases($parent, ['case_status' => 'resolved']) : []
  );

  $resolutionClass = $showResolved ? 'resolved_cases' : 'unresolved_cases';

  if (count($childpages) > 0) {
    $headerTag = $layoutType === 'case_list' ? 'h3' : 'h4';
    return "<div class='wp-block-klarity-klarity-cases-overview-block row $layoutType $resolutionClass'>"
      . implode(
        '',
        array_unique(array_map(function ($page) use ($showResolved, $headerTag, $layoutType) {
          $metadata = get_post_meta($page->ID);
          $headline = isset($metadata['headline'])
            ? "<div class='headline'>{$metadata['headline'][0]}</div>"
            : '';
          $markAsNew = isset($metadata['case_status']) && $metadata['case_status'][0] === 'new'
            ? "<div class='new'>{__('new')}</div>"
            : '';
          $markAsUpdate = isset($metadata['case_status']) && $metadata['case_status'][0] === 'update'
            ? "<div class='update'>{__('update')}</div>"
            : '';
          $caseResolution = isset($metadata['case_status']) && $metadata['case_status'][0] === 'resolved'
            ? 'resolved'
            : 'unresolved';

          $shortDescription = get_post_meta($page->ID, 'short_description', true);

          $progressPageExists = isset($metadata['case_progress']);

          preg_match('#videoThumbnail":"([^"]+)"#', $page->post_content, $thumbnailUrlMatch);
          $imageUrl = $thumbnailUrlMatch[1]
            ?? wp_get_attachment_image_src(get_post_thumbnail_id($page->ID), 'single-post-thumbnail')[0]
            ?? 'http://placehold.it/200x200';

          if ($layoutType === 'subcase_list') {
            if ($caseResolution === 'resolved') {
              return "<div class='case-wrapper col s12 m6'>
                <div class='row case $caseResolution'>
                  <div class='col s4 thumbnail-container' style='background-image:url(\"$imageUrl\")'></div>
                  <div class='col s8 description'>$shortDescription</div>
                </div>
              </div>";
            }
            preg_match('#"link":"(https://player.vimeo.com/video/\d+)#', $page->post_content, $videoUrlMatch);
            $videoUrl = $videoUrlMatch[1] ?? null;

            if (isset($videoUrl)) {
              preg_match('#"videoDuration":"([^"]+)#', $page->post_content, $videoDurationMatch);
              $videoDuration = $videoDurationMatch[1] ?? null;
              $videoContent = is_null($videoDuration)
                ? ''
                : "<div class='video-timestamp'>$videoDuration</div>";

              wp_enqueue_script(
                'case_overview_header_video-handler-js',
                plugins_url('/src/block/show-video.js', __DIR__),
                [],
                true
              );
              $cardThumbnail =
                "<div class='video-container' onclick='showVideo(this, \"$videoUrl\")'>
                  <div class='thumbnail-container' style='background-image:url(\"$imageUrl\")'>
                    <img class='play-icon' alt='Play' src='" . plugin_dir_url(__DIR__) . "/assets/play_button.png'/>
                    $videoContent
                  </div>
                </div>";
            }

            if ($progressPageExists) {
              $progress = get_post_meta($page->ID, 'case_progress', true);
              $caseProgress = "<p class='case-progress-title'>Case progress</p><div class='case-progress'>";
              foreach (range(1, 5) as $number) {
                $step = $number <= $progress ? $number : 0;
                $caseProgress .= "<div class='case-block step-".$step."'></div>";
              }
              $caseProgress .= "</div>";
            }

          }
          else {
            $caseProgress = "
              <div class='separator'></div>
              <div class='description'>$shortDescription</div>";
          }

          if (!isset($cardThumbnail)) {
            $cardThumbnail = "<div class='thumbnail-container' style='background-image:url(\"$imageUrl\")'></div>";
          }
          if (!isset($caseProgress)) {
            $caseProgress = '';
          }
          $cardWrapper = "<div class='case $caseResolution card'>
            $markAsNew
            $markAsUpdate
            $cardThumbnail
            <div class='description'>
              $headline
              <$headerTag>{$page->post_title}</$headerTag>
              $caseProgress
            </div>
          </div>";
          if ($layoutType === 'case_list') {
            $isEditContext = isset($_GET['context']) && $_GET['context'] === 'edit';
            $link = $isEditContext ? 'javascript:void(0)' : get_permalink($page);
            $cardWrapper = "<a href='$link'>$cardWrapper</a>";
          }

          return "<div class='case-wrapper col s12 m6'>$cardWrapper</div>";
        }, $childpages)))
      . "<div class='col right see-all'>See all</div>
        <div class='col right see-less hide'>See less</div>
      </div>";
  }
  switch ($filter) {
    case 'all':
      return __('No pages matched. Make sure that the metadata "case_status" is set on the cases that you want to show here.');
      break;
    case 'in_sub_pages':
      return __('No child-pages. Make sure that this page has child pages and that these child pages have the metadata "case_status" set.');
      break;
  }
  return '';
}

function register_klarity_case_block_callback() {
  if (function_exists('register_block_type')) {
    register_block_type('klarity/klarity-cases-overview-block', [
      'render_callback' => 'render_klarity_cases_overview_list',
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

add_action('plugins_loaded', 'register_klarity_case_block_callback');

add_shortcode('klarity-cases-overview', 'render_klarity_cases_overview_list');

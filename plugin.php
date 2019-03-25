<?php
/**
 * Plugin Name: Klarity cases overview block
 * Plugin URI: https://github.com/ahmadawais/create-guten-block/
 * Description: Klarity cases overview block
 * Author: Klarity
 * Author URI: https://github.com/Klarityorg
 * Version: 1.1.5
 * License: MIT
 *
 * @package Klarity
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

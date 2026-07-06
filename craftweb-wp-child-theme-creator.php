<?php
/**
 * Plugin Name: CraftoWeb WP Child Theme Creator
 * Plugin URI: https://craftoweb.com
 * Description: Easily create WordPress child themes with one click.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: CraftoWeb
 * Author URI: https://craftoweb.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: craftoweb-wp-child-theme-creator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CWCTC_VERSION', '1.0.0' );
define( 'CWCTC_PATH', plugin_dir_path( __FILE__ ) );
define( 'CWCTC_URL', plugin_dir_url( __FILE__ ) );

require_once CWCTC_PATH . 'admin/admin-menu.php';
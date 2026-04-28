<?php
/**
 * Plugin Name:       Simple Staff List Rewrite
 * Plugin URI:        https://github.com/your-site/simple-staff-list-rewrite
 * Description:       A modern rewrite of Simple Staff List. Secure, PHP 8.x compatible, responsive grid layout, and flexible shortcode display.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            PixelPaper.net
 * Author URI:        https://pixelpaper.net
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ssl-rewrite
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SSLR_VERSION',     '1.0.0' );
define( 'SSLR_PLUGIN_FILE', __FILE__ );
define( 'SSLR_PATH',        plugin_dir_path( __FILE__ ) );
define( 'SSLR_URL',         plugin_dir_url( __FILE__ ) );
define( 'SSLR_SLUG',        'ssl-rewrite' );

// ─── Autoload classes ─────────────────────────────────────────────────────────
require_once SSLR_PATH . 'includes/class-sslr-post-type.php';
require_once SSLR_PATH . 'includes/class-sslr-shortcode.php';
require_once SSLR_PATH . 'admin/class-sslr-admin.php';

// ─── Activation / Deactivation ────────────────────────────────────────────────
register_activation_hook( __FILE__, 'sslr_activate' );
register_deactivation_hook( __FILE__, 'sslr_deactivate' );

function sslr_activate(): void {
	SSLR_Post_Type::register();
	flush_rewrite_rules();
}

function sslr_deactivate(): void {
	flush_rewrite_rules();
}

// ─── Boot ─────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'sslr_init' );

function sslr_init(): void {
	// Register post type & taxonomy.
	$post_type = new SSLR_Post_Type();
	$post_type->init();

	// Shortcode.
	$shortcode = new SSLR_Shortcode();
	$shortcode->init();

	// Admin.
	if ( is_admin() ) {
		$admin = new SSLR_Admin();
		$admin->init();
	}
}

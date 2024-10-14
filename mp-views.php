<?php
/*
Plugin Name: MP Views
Description: Page view counter for WordPress using a custom database table and AJAX.
Version: 1.0.0
Plugin URI:  https://petrov.net.ua
Author: Mykhailo Petrov
Author URI:  https://petrov.net.ua
Text Domain: mpviews
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'MPVIEWS_THEME_VERSION' ) ) {
	define( 'MPVIEWS_THEME_VERSION', '1.0.0' );
}

if ( ! defined( 'MPVIEWS_PLUGIN_PATH' ) ) {
	define( 'MPVIEWS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MPVIEWS_PLUGIN_URL' ) ) {
define( 'MPVIEWS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'MPVIEWS_PLUGIN_TEXT_DOMAIN' ) ) {
	define( 'MPVIEWS_PLUGIN_TEXT_DOMAIN', 'mpviews' );
}

add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( 'mpviews', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

register_activation_hook( __FILE__, 'mpviews_create_table' );
function mpviews_create_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mpviews';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		post_id BIGINT(20) UNSIGNED NOT NULL,
		views INT(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (id),
		UNIQUE KEY post_id (post_id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_uninstall_hook( __FILE__, 'mpviews_delete_table' );
function mpviews_delete_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mpviews';
	$sql = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query( $sql );
}

add_action( 'wp_ajax_mpviews_increase_page_views', 'mpviews_increase_page_views' );
add_action( 'wp_ajax_nopriv_mpviews_increase_page_views', 'mpviews_increase_page_views' );
function mpviews_increase_page_views() {
	if ( ! isset( $_POST['mpviews_nonce'] ) || ! wp_verify_nonce( $_POST['mpviews_nonce'], 'mpviews_increase_page_views' ) ) {
		wp_die( 'Invalid nonce' );
	}

	if ( isset( $_POST['post_id'] ) ) {
		global $wpdb;
		$post_id = intval( $_POST['post_id'] );
		$table_name = $wpdb->prefix . 'mpviews';

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $table_name (post_id, views) VALUES (%d, 1) ON DUPLICATE KEY UPDATE views = views + 1",
				$post_id
			)
		);

		$new_views = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT views FROM $table_name WHERE post_id = %d",
				$post_id
			)
		);

		echo $new_views;
		wp_die();
	}
	wp_die();
}

add_action( 'wp_enqueue_scripts', 'mpviews_enqueue_scripts' );
function mpviews_enqueue_scripts() {
	wp_enqueue_style(
		'mpviews-ajax-style',
		MPVIEWS_PLUGIN_URL . 'assets/css/mpviews-ajax-style.css',
		array(),
		MPVIEWS_THEME_VERSION
	);
	wp_enqueue_script(
		'mpviews-ajax-script',
		MPVIEWS_PLUGIN_URL . 'assets/js/mpviews-ajax-script.js',
		array( 'jquery' ),
		MPVIEWS_THEME_VERSION,
		true
	);
	wp_localize_script(
		'mpviews-ajax-script',
		'mpviews_ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'mpviews_increase_page_views' )
		)
	);
}

function mpviews_display_page_views( $post_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mpviews';
	$page_views = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT views FROM $table_name WHERE post_id = %d",
			$post_id
		)
	);

	$page_views = $page_views ? intval( $page_views ) : 0;

	echo '<div class="mpviews__counter mpviews-counter" data-post-id="' . esc_attr( $post_id ) . '">';
	if ( $page_views > 0 ) {
		echo '<div class="mpviews-counter__body">';
		echo '<div class="mpviews-counter__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12.0003 3C17.3924 3 21.8784 6.87976 22.8189 12C21.8784 17.1202 17.3924 21 12.0003 21C6.60812 21 2.12215 17.1202 1.18164 12C2.12215 6.87976 6.60812 3 12.0003 3ZM12.0003 19C16.2359 19 19.8603 16.052 20.7777 12C19.8603 7.94803 16.2359 5 12.0003 5C7.7646 5 4.14022 7.94803 3.22278 12C4.14022 16.052 7.7646 19 12.0003 19ZM12.0003 16.5C9.51498 16.5 7.50026 14.4853 7.50026 12C7.50026 9.51472 9.51498 7.5 12.0003 7.5C14.4855 7.5 16.5003 9.51472 16.5003 12C16.5003 14.4853 14.4855 16.5 12.0003 16.5ZM12.0003 14.5C13.381 14.5 14.5003 13.3807 14.5003 12C14.5003 10.6193 13.381 9.5 12.0003 9.5C10.6196 9.5 9.50026 10.6193 9.50026 12C9.50026 13.3807 10.6196 14.5 12.0003 14.5Z"></path></svg>
                </div>';
		echo '<div class="mpviews__count"><span>' . esc_html( $page_views ) . '</span></div>';
		echo '</div>';
	}
	echo '</div>';
}

add_shortcode( 'mpviews_counter', 'mpviews_page_views_shortcode' );
function mpviews_page_views_shortcode() {
	ob_start();
	mpviews_display_page_views( get_the_ID() );
	return ob_get_clean();
}

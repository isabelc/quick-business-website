<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package     Quick Business Website
 * @copyright   Copyright 2013 - 2017 Isabel Castillo
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			qbw_uninstall();
			restore_current_blog();
		}
	}
} else {
	qbw_uninstall();
}

/**
 * Uninstall function.
 *
 * The uninstall function will only proceed if
 * the user explicitly asks for all data to be removed.
 *
 * @return void
 */
function qbw_uninstall() {
	// Make sure that the user wants to remove all the data.
	if ( get_option( 'qbw_delete_data' ) == 'true' ) {
		global $wpdb;
		
		// Delete pages
		wp_delete_post( get_option( 'qbw_reviews_page_id' ), true );
		wp_delete_post( get_option( 'qbw_contact_page_id' ), true );
		wp_delete_post( get_option( 'qbw_staff_page_id' ), true );
		wp_delete_post( get_option( 'qbw_services_page_id' ), true );
		wp_delete_post( get_option( 'qbw_about_page_id' ), true );// for backwards compatibilty
		
		// get all options with our prefix
		$query = $wpdb->get_results( "select * from " . $wpdb->options . " where option_name like 'qbw_%'" );
		// Delete options
		if ( ! empty( $query[0] ) ) {
			foreach ( $query as $option ) {
				delete_option( $option->option_name );
			}
		}

		// Delete Reviews options
		delete_option( 'smar_options' );

		// Delete the Reviews database table
		$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "smareviewsb" );

	}
}

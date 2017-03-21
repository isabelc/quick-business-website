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

		/** Delete All the Custom Post Types */
		$qbw_taxonomies = array( 'smartest_service_category' );
		$qbw_post_types = array( 'smartest_news', 'smartest_services', 'smartest_staff' );

		foreach ( $qbw_post_types as $post_type ) {
			$qbw_taxonomies = array_merge( $qbw_taxonomies, get_object_taxonomies( $post_type ) );
			$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
			if ( $items ) {
				foreach ( $items as $item ) {
					wp_delete_post( $item, true );
				}
			}
		}

		/** Delete All the Terms & Taxonomies */
		foreach ( array_unique( array_filter( $qbw_taxonomies ) ) as $taxonomy ) {
			$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );
			// Delete Terms.
			if ( $terms ) {
				foreach ( $terms as $term ) {
					$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
					$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
					$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
				}
			}
			// Delete Taxonomies.
			$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
		}

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

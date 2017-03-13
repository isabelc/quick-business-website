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
}
else {
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
		wp_delete_post(get_option('qbw_reviews_page_id'), true);
		wp_delete_post(get_option('qbw_contact_page_id'), true);
		wp_delete_post(get_option('qbw_about_page_id'), true);// for backwards compatibilty
		// get all options with our prefix
		$query = $wpdb->get_results( "select * from " . $wpdb->options . " where option_name like 'qbw_%'" );
		// delete options
		if ( ! empty( $query[0] ) ) {
			foreach ( $query as $option ) {
				delete_option( $option->option_name );
			}
		}


		// @todo delete reviews

	
	}
}







































//if uninstall not called from WordPress exit, or if they did not opt in to delete data
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

$od_options = array(
	'od_rewrite_docs_slug',
	'od_change_main_docs_title',
	'od_main_top_sort_by',
	'od_disable_microdata',
	'od_hide_printer_icon',
	'od_hide_print_link',
	'od_title_on_nav_links',
	'od_delete_data_on_uninstall',
	'od_widget_list_toggle',
	'od_single_sort_order',
	'od_single_sort_by',
	'od_list_toggle',
	'od_close_comments',
	'odocs_cleanup_twopointfive'
);

/* Delete all custom terms for passed taxonomy, and the custom term meta options both on Single site and Multisite.
*/
function delete_custom_terms($taxonomy){
	global $wpdb;
	if ( is_multisite() ) { 

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );

			$query = 'SELECT t.name, t.term_id
				FROM ' . $wpdb->terms . ' AS t
				INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
				ON t.term_id = tt.term_id
				WHERE tt.taxonomy = "' . $taxonomy . '"';
		
			$terms = $wpdb->get_results($query);

			foreach ($terms as $term) {
				$t_id = $term->term_id;
				wp_delete_term( $t_id, $taxonomy );
				delete_option( "taxonomy_$t_id" );
			}

		} // end foreach blog_id

	} else {

		// not Multisite

		$query = 'SELECT t.name, t.term_id
				FROM ' . $wpdb->terms . ' AS t
				INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
				ON t.term_id = tt.term_id
				WHERE tt.taxonomy = "' . $taxonomy . '"';
		
		$terms = $wpdb->get_results($query);

		foreach ($terms as $term) {
	
			$t_id = $term->term_id;
	
			wp_delete_term( $t_id, $taxonomy );
	
			delete_option( "taxonomy_$t_id" );
		}

	}
}

global $wpdb;
if( get_option( 'od_delete_data_on_uninstall' ) ) {
	
	// Delete all custom terms for this taxonomy, and the custom term meta options
	delete_custom_terms('isa_docs_category');
	
	// For Single site
	if ( !is_multisite() ) {
	
		foreach ( $od_options as $od_option ) {
			delete_option( $od_option );
		}
	
		// delete Docs posts
		$args = array(	'post_type' => 'isa_docs', 
					'posts_per_page' => -1,
		);
		$all_docs = get_posts( $args );
		foreach ($all_docs as $doc) {
			wp_delete_post($doc->ID, true);
		}
	} 
	// For Multisite
	else {
	
		global $wpdb;
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		$original_blog_id = get_current_blog_id();
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
	
			foreach ( $od_options as $od_option ) {
				delete_option( $od_option );
			}			
		
			// delete Docs posts
			$args = array(	'post_type' => 'isa_docs', 
						'posts_per_page' => -1,
			);
			$all_docs = get_posts( $args );
			foreach ($all_docs as $doc) {
				wp_delete_post($doc->ID, true);
			}
	
		}
	}
}
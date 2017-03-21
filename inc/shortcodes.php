<?php
/**
 * Shortcodes and functions related to displaying our content with shortcodes
 */

add_shortcode( 'qbw_staff', 'qbw_staff_shortcode' );
add_shortcode( 'qbw_services', 'qbw_services_shortcode' );

function qbw_staff_shortcode() {
	$out = '<div id="qbw-staff-wrap">';
	$args = array(
		'posts_per_page' => -1,
		'post_type' => 'smartest_staff',
		'orderby' => 'meta_value_num',
		'meta_key' => '_smab_staff-order-number',
		'order' => 'ASC',
		'no_found_rows' => true );
    
	$staff = new WP_Query( $args );

	if ( $staff->have_posts() ) {
		while ( $staff->have_posts() ) {
			$staff->the_post();

			$out .= '<div class="qbw-staff-item">';
				
			if ( has_post_thumbnail() ) {
				$out .= '<figure><a href="' . esc_url( get_permalink() ) . '">' .
							get_the_post_thumbnail( get_the_ID(), 'post-thumbnail', array( 'class' => 'qbw-staff-item-image' ) ) .
						'</a></figure>';

			}

			$out .= '<div class="qbw-staff-item-content">' .
						'<h4><a href="' . esc_url( get_permalink() ) . '">' .
						esc_html( get_the_title() ) . '</a></h4>' .
					'</div>' .
					qbw_get_staff_meta() .
					'</div>';

		} // endwhile;

	} // end if have_posts
	else {
		$out .= '<h2>' . __('No staff found!', 'quick-business-website') . '</h2>';
	}
	wp_reset_postdata();

	$out .= '</div><!-- #qbw-staff-wrap -->';

	return $out;
	
}

function qbw_services_shortcode() {
	$out = '<div id="qbw-services-wrap">';

	// default sort order
	$args = array( 
		'posts_per_page' => -1, 
		'post_type' => 'smartest_services',
		'orderby' => 'title',
		'order' => 'ASC',
		'no_found_rows' => true );

	// Check if custom sort order is enabled
	if ( get_option( 'qbw_enable_service_sort' ) == 'true' ) {
		$args['orderby'] = 'meta_value_num';
		$args['meta_key'] = '_smab_service-order-number';
	}
	
	// Check if this is a request for a services category taxonomy archive
	if ( false !== ( $cat = get_transient( 'qbw_services_category' ) ) ) {

		// Add the tax query args 
		$args['tax_query'] = array(
						array(
							'taxonomy' => 'smartest_service_category',
							'field' => 'slug',
							'terms' => array( $cat ),
							)
						);
		delete_transient( 'qbw_services_category' );
	}

	$services = new WP_Query( $args );

	if ( $services->have_posts() ) {

		while ( $services->have_posts() ) {
			$services->the_post();

			$out .= '<div class="qbw-service-item">';

			if ( has_post_thumbnail() ) {
				$out .= '<figure><a href="' . esc_url( get_permalink() ) . '">' .
							get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class' => 'qbw-service-item-image' ) ) .
						'</a></figure>';

			}

			$out .= '<div class="qbw-service-item-content">' .
						'<h4><a href="' . esc_url( get_permalink() ) . '">' .
						esc_html( get_the_title() ) . '</a></h4>' .
					'</div>' .
					'</div>';
			
		}

	} else {
		$out .= '<h2>' . __('No services found!', 'quick-business-website') . '</h2>';
	}
	wp_reset_postdata();
	$out .= '</div><!-- #qbw-services-wrap -->';

	return $out;
}

/**
 * Redirect Staff and Services archives to our custom page for a better display.
 */
function qbw_alter_cpt_archive_query( $request ) {
	if ( is_admin() ) {
		return $request;
	}
	// If its a staff archive, not single, force it to our page shortcode rather than interpreting it as a post type archive and using the archive template.
	if ( isset( $request['post_type'] ) && 'smartest_staff' == $request['post_type'] && empty( $request['smartest_staff'] ) ) {
		
		unset( $request['post_type'] );
		$request['page'] = '';
		$request['pagename'] = 'staff';
	}

	// If its a services archive, not single, force it to our page shortcode rather than interpreting it as a post type archive and using the archive template.
	if ( isset( $request['post_type'] ) && 'smartest_services' == $request['post_type'] && empty( $request['smartest_services'] ) ) {
		unset( $request['post_type'] );
		$request['page'] = '';
		$request['pagename'] = 'services';
	}

	// Do the same for Services category taxonomy archives
	if ( isset( $request['smartest_service_category'] ) ) {
		set_transient( 'qbw_services_category', $request['smartest_service_category'], 20 );
		unset( $request['smartest_service_category'] );
		$request['page'] = '';
		$request['pagename'] = 'services';
	}

	return $request;
}
add_filter( 'request', 'qbw_alter_cpt_archive_query' );
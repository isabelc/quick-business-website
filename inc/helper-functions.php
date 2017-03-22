<?php
/**
 * Helper functions
 */
/**
 * Wrapper for wp_kses() with our own allowed HTML tags.
 *
 * Allows tags: a, br, em, strong, and if true is passed for the
 * $img parameter, then allows img.
 * @param string $content The content to filter
 * @param boolean $img Whether to allow img element
 * @return the cleansed string with only our allowed tags
 */
function qbw_kses( $content, $img = false ) {
	$allowed_tags = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
	);			

	if ( $img ) { // allow the image tag
		$allowed_tags['img'] = array(
				'src' => array(),
				'width' => array(),
				'height' => array(),
				'alt' => array(),
				'title' => array()
			);
	}

	return wp_kses( $content, $allowed_tags );
}
function qbw_get_business_name() {
	$n = stripslashes( get_option( 'qbw_business_name' ) );
	if ( empty( $n ) ) {
		$n = get_bloginfo('name');
	}
	return $n;
}
/**
 * Return the LocalBusiness structured data markup
 * @return array $data array to use for JSON-LD format
 */
function qbw_business_structured_data() {
	$options = get_option( 'qbw_options' );
	$keys = array( 'qbw_address_street',
			'qbw_address_suite',
			'qbw_address_city',
			'qbw_address_state',
			'qbw_address_zip',
			'qbw_address_country',
			'qbw_phone_number',
			'qbw_fax_numb'			
	);
	foreach ( $keys as $key ) {
		${$key} = isset( $options[ $key ] ) ? stripslashes( $options[ $key ] ) : '';
	}
	$data = array(
	'@context' => 'http://schema.org',
		'@type' => 'LocalBusiness',
		'name' => qbw_get_business_name(),
		'address' => array(
			'@type' => 'PostalAddress',
			'streetAddress' => $qbw_address_street . ' ' . $qbw_address_suite,
			'addressLocality' => $qbw_address_city,
			'addressRegion' => $qbw_address_state,
			'postalCode' => $qbw_address_zip,
			'addressCountry' => $qbw_address_country
		),
		'telephone' => $qbw_phone_number,
		'faxNumber' => $qbw_fax_numb,
		'priceRange' => '$$$'// @todo at some point, allow this to be set
	);

	if ( $logo = get_theme_mod( 'custom_logo' ) ) {
		$image_attributes = wp_get_attachment_image_src( $logo, 'full' );
	} elseif ( $site_icon = get_site_icon_url() ) {
		$image_attributes = array( $site_icon, 512, 512 );
	}

	if ( ! empty( $image_attributes ) ) {
		$data['image'] = array(
			'@type' => 'ImageObject',
			'url' => $image_attributes[0],
			'width' => $image_attributes[1],
			'height' => $image_attributes[2],
		);
	}

	return $data;
}

/**
 * Get the HTML for the staff meta: job title and social media links.
 */
function qbw_get_staff_meta() {
	global $post;

	$keys = array(
		'job_title',
		'twitter',
		'gplus',
		'facebook',
		'linkedin'
	);
	foreach ( $keys as $key ) {
		${$key} = get_post_meta( $post->ID, "_smab_staff_{$key}", true );
	}
	$meta = '<div id="qbw-staff-meta">';
	if ( $job_title ) {
		$meta .= '<h5>' . esc_html( $job_title ) . '</h5>';
	}
	if ( get_option( 'qbw_old_social_icons') == 'false' ) {
		$twit = 'fa fa-twitter-square';
		$goog = 'fa fa-google-plus-square';
		$face = 'fa fa-facebook-square';
		$link = 'fa fa-linkedin-square';
	} else {
		$twit = 'item-1';
		$goog = 'item-2';
		$face = 'item-3';
		$link = 'item-4';
	}
	$meta .= '<ul id="qbw-staff-socials">';
	if ( $twitter ) {
		$uri = 'https://twitter.com/' . $twitter;
		$meta .= '<li><a class="' . $twit. '" href="' . esc_url( $uri ) . '" title="'. __('Twitter', 'quick-business-website') . '"></a></li>';
	}
	if ( $gplus ) {
		$uri = 'https://plus.google.com/' . $gplus;
		$meta .= '<li><a class="' . $goog .'" href="' . esc_url( $uri ) . '" title="'. __('Google Plus', 'quick-business-website') . '" rel="author"></a></li>';
	}
	if ( $facebook ) {
		$uri = 'https://facebook.com/' . $facebook;
		$meta .= '<li><a class="' . $face. '" href="' . esc_url( $uri ) . '" title="'. __('Facebook', 'quick-business-website') . '"></a></li>';
	}
	if ( $linkedin ) {
		$uri = 'http://www.linkedin.com/' . $linkedin;
		$meta .= '<li><a class="' . $link .'" href="' . esc_url( $uri ) . '" title="'. __('LinkedIn', 'quick-business-website') . '"></a></li>';
	}
	$meta .= '</ul></div>';
	return $meta;
}

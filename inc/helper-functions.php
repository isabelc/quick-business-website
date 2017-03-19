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

/**
 * @test @todo use this everwhere that option qbw_business_name is got.
 */
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

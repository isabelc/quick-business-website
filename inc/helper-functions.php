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
 * Return the structured data markup for the localbusiness address
 */
function qbw_address_structured_data() {
	$data = '';
	$options = get_option( 'qbw_options' );
	$keys = array( 'qbw_address_street',
			'qbw_address_suite',
			'qbw_address_city',
			'qbw_address_state',
			'qbw_address_zip',
			'qbw_address_country'
	);
	foreach ( $keys as $key ) {
		${$key} = isset( $options[ $key ] ) ?
				esc_html( stripslashes( $options[ $key ] ) ) :
				'';
	}
	if ( $qbw_address_street ) {
		$data .= '<p id="qbw-addy-box" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">' . $qbw_address_street . '</span>&nbsp;';
	}
	if ( $qbw_address_suite ) {
		$data .= ' ' . $qbw_address_suite . '&nbsp;';
	}
	if ( $qbw_address_city ) {
		$data .= '<br /><span itemprop="addressLocality">' . $qbw_address_city . '</span>';
	}
	if ( $qbw_address_city && $qbw_address_state ) {
		$data .= ', ';
	}
	if ( $qbw_address_state ) {
		$data .= '<span itemprop="addressRegion">' . $qbw_address_state . '</span>&nbsp;';
	}
	if ( $qbw_address_zip ) {
		$data .= ' <span class="postal-code" itemprop="postalCode">' . $qbw_address_zip . '</span>&nbsp;';
	}
	if ( $qbw_address_country ) {
		$data .= '<br /><span itemprop="addressCountry">' . $qbw_address_country . '</span>&nbsp;';
	}
	if ( $qbw_address_street ) {
		$data .= '</p>'; // close #qbw-addy-box
	}
	return $data;
}

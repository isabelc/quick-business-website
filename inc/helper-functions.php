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
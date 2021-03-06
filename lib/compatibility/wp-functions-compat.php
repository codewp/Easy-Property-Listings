<?php
/**
 * Wordpress core functions for keeping compatibility.
 *
 * @since 2.1.11
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'shortcode_exists' ) ) {
	/**
	 * Whether a registered shortcode exists named $tag
	 *
	 * @since 2.1.11
	 *
	 * @global array $shortcode_tags List of shortcode tags and their callback hooks.
	 *
	 * @param string $tag Shortcode tag to check.
	 * @return bool Whether the given shortcode exists.
	 */
	function shortcode_exists( $tag ) {
		global $shortcode_tags;
		return array_key_exists( $tag, $shortcode_tags );
	}
}

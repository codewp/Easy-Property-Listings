<?php
/**
 * Listing Search widget default view.
 *
 * @package 	easy-property-listings
 * @subpackage  Theme
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var array $args 	Widget arguments.
 * @var array $instance Instance of widget.
 */

echo $args['before_widget'];

$title	= apply_filters( 'widget_title', $instance['title'] );

if ( strlen( trim( $title ) ) ) {
	echo $args['before_title'] . $title . $args['after_title'];
}

echo epl_shortcode_listing_search_callback( $instance );

echo $args['after_widget'];

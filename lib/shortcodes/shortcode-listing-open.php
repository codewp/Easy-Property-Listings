<?php
/**
 * SHORTCODE :: Open For Inspection [listing_open]
 *
 * @package     EPL
 * @subpackage  Shotrcode/map
 * @copyright   Copyright (c) 2014, Merv Barrett
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Only load on front
if ( is_admin() ) {
	return;
}
/**
 * This shortcode allows for you to specify the property type(s) using
 * [listing_open post_type="property,rental"] option. You can also
 * limit the number of entries that display. using  [epl-property-open limit="5"]
 */
function epl_shortcode_property_open_callback( $atts ) {
	$property_types = epl_get_active_post_types();
	if ( ! empty( $property_types ) ) {
		$property_types = array_keys( $property_types );
	}

	$attributes = shortcode_atts( array(
		'title'        => '',
		'post_type'    => $property_types, //Post Type
		'limit'        => '-1', // Number of maximum posts to show
		'template'     => false, // Template. slim, table
		'location'     => '', // Location slug. Should be a name like sorrento
		'tools_top'    => 'off', // Tools before the loop like Sorter and Grid on or off
		'tools_bottom' => 'off', // Tools after the loop like pagination on or off
		'sortby'       => '', // Options: price, date : Default date
		'sort_order'   => 'DESC',
		'view'         => 'default', // Template View of listing-open shortcode
	), $atts );

	if ( is_string( $attributes['post_type'] ) && $attributes['post_type'] == 'rental' ) {
		$meta_key_price = 'property_rent';
	} else {
		$meta_key_price = 'property_price';
	}

	if ( ! is_array( $attributes['post_type'] ) ) {
		$attributes['post_type'] = array_map( 'trim', explode( ',', $attributes['post_type'] ) );
	}

	$args = array(
		'post_type'      =>	$attributes['post_type'],
		'posts_per_page' =>	intval( $attributes['limit'] ),
		'meta_key'       =>	'property_inspection_times',
		'meta_query'     => array(
			array(
				'key'     => 'property_inspection_times',
				'value'   => '',
				'compare' => '!=',
			),
		)
	);

	if ( ! empty( $attributes['location'] ) ) {
		if ( ! is_array( $attributes['location'] ) ) {
			$attributes['location'] = explode( ',', $attributes['location'] );
			$attributes['location'] = array_map( 'trim', $attributes['location'] );

			$args['tax_query'][] = array(
				'taxonomy' => 'location',
				'field'    => 'slug',
				'terms'    => $attributes['location'],
			);
		}
	}

	if ( $attributes['sortby'] != '' ) {
		if ( $attributes['sortby'] == 'price' ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = $meta_key_price;
		} else {
			$args['orderby'] = 'post_date';
			$args['order']   = 'DESC';
		}
		$args['order'] = $attributes['sort_order'];
	}

	if ( isset( $_GET['sortby'] ) ) {
		$orderby = sanitize_text_field( trim( $_GET['sortby'] ) );
		if ( $orderby == 'high' ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = $meta_key_price;
			$args['order']    = 'DESC';
		} elseif ( $orderby == 'low' ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = $meta_key_price;
			$args['order']    = 'ASC';
		} elseif ( $orderby == 'new' ) {
			$args['orderby'] = 'post_date';
			$args['order']   = 'DESC';
		} elseif ( $orderby == 'old' ) {
			$args['orderby'] = 'post_date';
			$args['order']   = 'ASC';
		}
	}

	$query_open = new WP_Query( $args );

	ob_start();
	epl_get_template_part(
		'shortcodes/listing-open/' . ( ! empty( $attributes['view'] ) ? trim( $attributes['view'] ) . '.php' : 'default.php' ),
		array(
			'attributes' => $attributes,
			'query_open' => $query_open,
		)
	);
	return ob_get_clean();
}
add_shortcode( 'home_open_list', 'epl_shortcode_property_open_callback' );
add_shortcode( 'listing_open', 'epl_shortcode_property_open_callback' );

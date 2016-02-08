<?php
/**
 * SHORTCODE :: Listing [listing]
 *
 * @package     EPL
 * @subpackage  Shortcode
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
 * [listing post_type="property,rental" status="current,sold,leased" template="default"] option. You can also
 * limit the number of entries that display. using  [listing limit="5"]
 */
function epl_shortcode_listing_callback( $atts ) {
	$property_types = epl_get_active_post_types();
	if ( ! empty($property_types ) ) {
		$property_types = array_keys( $property_types );
	}

	$attributes = shortcode_atts( array(
		'title'             => '',
		'post_type'         => $property_types, //Post Type
		'status'            => array( 'current', 'sold', 'leased' ),
		'limit'             => '10', // Number of maximum posts to show
		'author'            => '',	// Author of listings.
		'featured'          => 0,	// Featured listings.
		'template'          => false, // Template can be set to "slim" for home open style template
		'location'          => '', // Location slug. Should be a name like sorrento
		'location_id'       => '', // Listing location id.
		'feature'           => '', // Listing Feature slug, another words feature name.
		'feature_id'        => '', // Listing feature id.
		'tools_top'         => 'off', // Tools before the loop like Sorter and Grid on or off
		'tools_bottom'      => 'off', // Tools after the loop like pagination on or off
		'sortby'            => '', // Options: price, date : Default date
		'sort_order'        => 'DESC',
		'listing_open'      => 0, // Open listings.
		'query_object'      => '', // only for internal use . if provided use it instead of custom query
		// Map properties.
		'show_map'          => 0,
		'output_map_div'    => 1,
		'map_div_id'        => '',
		'map_style_height'  => 500,
		'default_latitude'  => '39.911607',
		'default_longitude' => '-100.853613',
		'zoom'              => 1,
		'zoom_events'       => 0,
		'cluster_size'      => -1,
		'map_types'         => array( 'ROADMAP' ),
		'default_map_type'  => 'ROADMAP',
		'auto_zoom'         => 1,
		'clustering'        => true,
		'view'              => 'default',
	), $atts );

	// Map properties.
	if ( $attributes['show_map'] ) {
		$attributes['map_id']            = trim( $attributes['map_id'] );
		$attributes['map_style_height']  = absint( $attributes['map_style_height'] ) ? absint( $attributes['map_style_height'] ) : 500;
		$attributes['default_latitude']  = trim( $attributes['default_latitude'] );
		$attributes['default_longitude'] = trim( $attributes['default_longitude'] );
		$attributes['zoom']              = absint( $attributes['zoom'] );
		$attributes['zoom_events']       = absint( $attributes['zoom_events'] );
		$attributes['cluster_size']      = (int) $attributes['cluster_size'];
	}

	// Pagination.
	$attributes['paged'] = get_query_var( 'paged', 1 ) ? get_query_var( 'paged', 1 ) : 1;
	if ( is_front_page() ) {
		$attributes['paged'] = get_query_var( 'page', 1 ) ? get_query_var( 'page', 1 ) : 1;
	}

	// Getting listings.
	$epl_property_listing = new EPL_Property_Listing( $attributes );
	$query_open           = $epl_property_listing->get_listings();

	if ( is_object( $attributes['query_object'] ) ) {
		$query_open = $attributes['query_object'];
	}

	ob_start();
	epl_get_template_part(
		'shortcodes/listing/' . ( ! empty( $attributes['view'] ) ? trim( $attributes['view'] ) . '.php' : 'default.php' ),
		array(
			'attributes' => $attributes,
			'query_open' => $query_open,
		)
	);
	return ob_get_clean();
}
add_shortcode( 'listing', 'epl_shortcode_listing_callback' );

function epl_sorting_options_callback( $sorters ) {
	foreach ( $sorters as $key => &$sorter ) {
		if ( $sorter['id'] == 'status_asc' || $sorter['id'] == 'status_desc' ) {
			unset( $sorters[ $key ] );
		}
	}
	return $sorters;
}

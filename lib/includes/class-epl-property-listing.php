<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class responsible for listing properties.
 *
 * @since      1.0.0
 *
 * @package    Easy_Property_Listings
 * @subpackage Easy_Property_Listings/lib/incluces
 * @author     Taher Atashbar <taher.atashbar@gmail.com>
 */
class EPL_Property_Listing {

	/**
	 * Properties of the object.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $data = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param array $data Array of object properties.
	 */
	public function __construct( array $data = array() ) {
		$property_types = epl_get_active_post_types();
		if ( ! empty( $property_types ) ) {
			$property_types = array_keys( $property_types );
		}
		// Default values of class properties.
		$this->data = apply_filters( 'epl_property_listing_properties',
			array(
				'title'        => '',
				'post_type'    => apply_filters( 'epl_property_listing_default_post_type', $property_types ), //Post Type
				'status'       => array( 'current', 'sold', 'leased' ),
				'limit'        => '10', // Number of maximum posts to show
				'paged'        => 1,
				'author'       => '',	// Author of listings.
				'featured'     => 0,	// Featured listings.
				'template'     => false, // Template can be set to "slim" for home open style template
				'location'     => '', // Location slug. Should be a name like sorrento
				'location_id'  => '', // Listing location id.
				'feature'      => '', // Listing Feature slug, another words feature name.
				'feature_id'   => '', // Listing feature id.
				'tools_top'    => 'off', // Tools before the loop like Sorter and Grid on or off
				'tools_bottom' => 'off', // Tools after the loop like pagination on or off
				'sortby'       => '', // Options: price, date : Default date
				'sort_order'   => 'DESC',
				'listing_open' => 0, // Open listings.
			)
		);
		// Setting data
		if ( count( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( array_key_exists( $key, $this->data ) ) {
					if ( method_exists( $this, 'set_' . $key ) ) {
						call_user_func( array( $this, 'set_' . $key ), $value );
					} else {
						$this->data[ $key ] = $value;
					}
				}
			}
		}
		// Default filter of listing query.
		add_filter( 'epl_property_listing_query_filter', array( $this, 'query_filter' ), 10, 2 );
	}

	/**
	 * Setting post_type property of the class.
	 *
	 * @since 1.0.0
	 * @param string|array $post_type
	 */
	public function set_post_type( $post_type ) {
		if ( is_string( $post_type ) && trim( $post_type ) ) {
			$this->data['post_type'] = array_filter( explode( ',', $post_type ), 'trim' );
		} else if ( is_array( $post_type ) ) {
			$this->data['post_type'] = array_filter( $post_type, 'trim' );
		}
		if ( is_array( $this->data['post_type'] ) ) {
			array_map( 'trim', $this->data['post_type'] );
		}
		// Using default listing types when post_type is empty.
		else if ( ! is_array( $this->data['post_type'] ) || ! count( $this->data['post_type'] ) ) {
			$property_types = epl_get_active_post_types();
			if ( ! empty( $property_types ) ) {
				$property_types = array_keys( $property_types );
			}
			$this->data['post_type'] = apply_filters( 'epl_property_listing_default_post_type', $property_types );
		}
	}

	/**
	 * Setting status property of the class.
	 *
	 * @since 1.0.0
	 * @param string|array $status
	 */
	public function set_status( $status ) {
		if ( is_string( $status ) ) {
			$this->data['status'] = array_filter( explode( ',', $status ), 'trim' );
		} else if ( is_array( $status ) ) {
			$this->data['status'] = array_filter( $status, 'trim' );
		}
		if ( is_array( $this->data['status'] ) && count( $this->data['status'] ) ) {
			array_map( 'trim', $this->data['status'] );
		}
	}

	/**
	 * Getting properties of the object.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Getting listings.
	 *
	 * @since  1.0.0
	 * @return WP_Query
	 */
	public function get_listings() {
		$query_args = apply_filters( 'epl_property_listing_query_filter',
			array(
				'post_type'      => $this->data['post_type'],
				'posts_per_page' => (int) $this->data['limit'],
				'paged'          =>	absint( $this->data['paged'] ),
				'meta_query'     => array(),
				'tax_query'      => array(),
			),
			$this->data
		);

		$listings = new WP_Query( $query_args );

		return apply_filters( 'epl_property_listing_listings', $listings, $this->data, $query_args );
	}

	/**
	 * Filtering query_args of listings query.
	 *
	 * @since  1.0.0
	 * @param  array  $args
	 * @param  array  $attributes
	 * @return array
	 */
	public function query_filter( array $args, array $attributes ) {
		if ( array( 'rental' ) == array_values( $attributes['post_type'] )  ) {
			$meta_key_price = 'property_rent';
		} else {
			$meta_key_price = 'property_price';
		}
		// Listings of specified author.
		if ( ! empty( $attributes['author'] ) ) {
			if ( is_array( $attributes['author'] ) ) {
				$attributes['author'] = implode( ',', array_map( 'absint', $attributes['author'] ) );
			}
			$args['author'] = trim( $attributes['author'] );
		}
		// Featured listings.
		if ( $attributes['featured'] ) {
			$args['meta_query'][] = array(
				'key'   => 'property_featured',
				'value' => 'yes',
			);
		}
		// Listing location tax query based on location slug.
		if ( ! empty( $attributes['location'] ) ) {
			if ( ! is_array( $attributes['location'] ) ) {
				$attributes['location'] = array_map( 'trim', explode( ',', $attributes['location'] ) );
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'location',
				'field'    => 'slug',
				'terms'    => $attributes['location'],
			);
		}
		// Listing location tax query based on location id.
		if ( ! empty( $attributes['location_id'] ) ) {
			if ( ! is_array( $attributes['location_id'] ) ) {
				$attributes['location_id'] = array_map( 'intval', explode( ',', $attributes['location_id'] ) );
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'location',
				'field'    => 'id',
				'terms'    => $attributes['location_id'],
			);
		}
		// Listing feature tax query based on feature slug.
		if ( ! empty( $attributes['feature'] ) ) {
			if ( ! is_array( $attributes['feature'] ) ) {
				$attributes['feature'] = array_map( 'trim', explode( ',', $attributes['feature'] ) );
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'tax_feature',
				'field'    => 'slug',
				'terms'    => $attributes['feature'],
			);
		}
		// Listing feature tax query based on feature id.
		if ( ! empty( $attributes['feature_id'] ) ) {
			if ( ! is_array( $attributes['feature_id'] ) ) {
				$attributes['feature_id'] = array_map( 'intval', explode( ',', $attributes['feature_id'] ) );
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'tax_feature',
				'field'    => 'id',
				'terms'    => $attributes['feature_id'],
			);
		}
		// Listing status query.
		if ( ! empty( $attributes['status'] ) ) {
			if ( ! is_array( $attributes['status'] ) ) {
				$attributes['status'] = array_map( 'trim', explode( ',', $attributes['status'] ) );
			}
			$args['meta_query'][] = array(
				'key'     => 'property_status',
				'value'   => $attributes['status'],
				'compare' => 'IN',
			);
			add_filter( 'epl_sorting_options', 'epl_sorting_options_callback' );
		}
		// Listing open meta query.
		if ( $attributes['listing_open'] ) {
			// Getting home open listings ids.
			$args['post__in'] = EPL()->listings->get_home_open_listings_ids();
		}
		// Sorting options.
		if ( ! empty ( $attributes['sortby'] ) ) {
			if ( $attributes['sortby'] == 'price' ) {
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] =	$meta_key_price;
			} else {
				$args['orderby']  = 'post_date';
				$args['order']    = 'DESC';
			}
			$args['order']        = $attributes['sort_order'];
		}
		if ( isset( $_GET['sortby'] ) ) {
			$orderby = sanitize_text_field( trim( $_GET['sortby'] ) );
			if ( $orderby == 'high' ) {
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] =	$meta_key_price;
				$args['order']    = 'DESC';
			} else if ( $orderby == 'low' ) {
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] =	$meta_key_price;
				$args['order']    = 'ASC';
			} else if ( $orderby == 'new' ) {
				$args['orderby']  = 'post_date';
				$args['order']    = 'DESC';
			} else if ( $orderby == 'old' ) {
				$args['orderby']  = 'post_date';
				$args['order']    = 'ASC';
			} else if ( $orderby == 'status_desc' ) {
				$args['orderby']  = 'meta_value';
				$args['meta_key'] =	'property_status';
				$args['order']    = 'DESC';
			} else if ( $orderby == 'status_asc' ) {
				$args['orderby']  = 'meta_value';
				$args['meta_key'] =	'property_status';
				$args['order']    = 'ASC';
			}
		}

		return $args;
	}

}

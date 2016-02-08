<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common functionalities between all of listing types.
 *
 * @since      1.0.0
 *
 * @package    Easy_Property_Listings
 * @subpackage Easy_Property_Listings/lib/incluces
 * @author     Taher Atashbar <taher.atashbar@gmail.com>
 */
class EPL_Listings {

	/**
	 * Getting home open listings ids.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_home_open_listings_ids() {
		global $wpdb;

		$return_listings = array();
		$listing_types   = epl_all_post_types();
		if ( count( $listing_types ) ) {
			$listing_types = array_map( 'trim', $listing_types );
			$listing_types = "('" . implode( "','", $listing_types ) . "')";

			$open_listings = $wpdb->get_results( "
				SELECT postmeta.post_id, postmeta.meta_value FROM
				{$wpdb->postmeta} AS postmeta INNER JOIN {$wpdb->posts} AS posts
				ON postmeta.post_id = posts.ID AND posts.post_type in {$listing_types}
				WHERE postmeta.meta_key = 'property_inspection_times' AND
				postmeta.meta_value IS NOT NULL AND postmeta.meta_value != ''
			" );

			if ( count( $open_listings ) ) {
				foreach ( $open_listings as $listing ) {
					$inspection_times = trim( $listing->meta_value );
					if ( strlen( $inspection_times ) ) {
						$list = array_filter( explode( "\n", $inspection_times ) );
						if ( ! empty( $list ) ) {
							// there are inspection times
							$inspect_array = array();
							foreach ( $list as $num => $item ) {
								if ( is_numeric( $item[0] ) ) {
									$timearr = explode( ' ', $item );
									$endtime = current( $timearr ) . ' ' . end( $timearr );
									if ( strtotime( $endtime ) > time() ) {
										$item = trim( $item );
										$inspect_array[ strtotime( $endtime ) ] = $item;
									}
								} else {
									$inspect_array[ $num ]	= $item;
								}
							}
							// check if listing has inspection times.
							if ( count( $inspect_array ) ) {
								ksort( $inspect_array );
								// Listing is home open.
								$return_listings[] = $listing->post_id;
							}
							// update inspection times by removing past dates
							$new_inspection_meta = implode( "\n", $inspect_array );
							if ( preg_replace( '/\s/', '', $new_inspection_meta ) != preg_replace( '/\s/', '', $listing->meta_value ) ) {
								update_post_meta( $listing->post_id, 'property_inspection_times', $new_inspection_meta );
							}
						}
					}
				}
			}
		}

		return apply_filters( 'epl_listings_home_open_listing_ids', $return_listings );
	}

}

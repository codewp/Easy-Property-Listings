<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var $attributes array 		Shortcode Attributes.
 * @var $query_open WP_Query 	Query object for listings.
 */

if ( $query_open->have_posts() ) {
	if ( $attributes['show_map'] && class_exists( 'Easy_Listings_Map' ) ) {
		$elm_gmap_render = new ELM_Public_Google_Map_Render(
			array(
				'listings'          => $query_open,
				'map_id'            => $attributes['map_div_id'],
				'output_map_div'    => $attributes['output_map_div'],
				'map_style_height'  => $attributes['map_style_height'],
				'default_latitude'  => $attributes['default_latitude'],
				'default_longitude' => $attributes['default_longitude'],
				'zoom'              => $attributes['zoom'],
				'zoom_events'       => $attributes['zoom_events'],
				'cluster_size'      => $attributes['cluster_size'],
				'map_types'         => $attributes['map_types'],
				'auto_zoom'         => $attributes['auto_zoom'],
				'clustering'        => $attributes['clustering'],
			)
		);
		$elm_gmap_render->create_map();
	}
	?>
	<div class="loop epl-shortcode">
		<div class="loop-content epl-shortcode-listing <?php echo epl_template_class( $attributes['template'] ); ?>">
			<?php
			if ( $attributes['tools_top'] == 'on' ) {
				do_action( 'epl_property_loop_start' );
			}
			while ( $query_open->have_posts() ) {
				$query_open->the_post();
				$attributes['template'] = str_replace( '_', '-', $attributes['template'] );
				epl_property_blog( $attributes['template'] );
			}
			if ( $attributes['tools_bottom'] == 'on' ) {
				do_action( 'epl_property_loop_end' );
			}
			?>
		</div>
		<div class="loop-footer">
				<?php do_action( 'epl_pagination',array( 'query'	=> $query_open ) ); ?>
		</div>
	</div>
	<?php
	wp_reset_postdata();
} else {
	echo '<h3>' . __( 'Nothing found, please check back later.', 'epl' ) . '</h3>';
}

<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var $attributes array 		Shortcode Attributes.
 * @var $query_open WP_Query 	Query object for listings.
 */

if ( ! empty( $attributes['title'] ) ) {
	echo apply_filters( 'epl_listing_open_shortcode_before_title', '<h2 class="epl-shortcode-title">' ) . $attributes['title'] . apply_filters( 'epl_listing_open_shortcode_after_title', '</h2>' );
}

if ( $query_open->have_posts() ) { ?>
	<div class="loop epl-shortcode">
		<div class="loop-content epl-shortcode-listing-location <?php echo epl_template_class( $attributes['template'] ); ?>">
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
			<?php do_action( 'epl_pagination',array( 'query' => $query_open ) ); ?>
		</div>
	</div>
	<?php
	wp_reset_postdata();
} else {
	echo '<h3 class="epl-shortcode-listing-open epl-alert">' . __( 'Nothing currently scheduled for inspection, please check back later.', 'epl' ) . '</h3>';
}

<?php
/**
 * Shortcodes for the dynamic sections. These work inside the block editor via
 * the Shortcode block, so no custom block JS build step is needed.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders one cart card. Shared by the homepage strip and the archive grid.
 */
function scc_render_cart_card( $post_id ) {
	$statuses = scc_cart_statuses();
	$status   = scc_cart_status( $post_id );
	$price    = scc_cart_price( $post_id );
	$chips    = scc_cart_meta_chips( $post_id );
	$link     = get_permalink( $post_id );

	ob_start();
	?>
	<article class="scc-cart">
		<?php if ( 'available' !== $status ) : ?>
			<span class="scc-cart__status scc-cart__status--<?php echo esc_attr( $status ); ?>">
				<?php echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status ); ?>
			</span>
		<?php else : ?>
			<span class="scc-cart__status"><?php echo esc_html( $statuses['available'] ); ?></span>
		<?php endif; ?>

		<a class="scc-cart__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, 'scc-cart', array( 'loading' => 'lazy', 'alt' => '' ) );
			}
			?>
		</a>

		<div class="scc-cart__body">
			<h3 class="scc-cart__title">
				<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
			</h3>

			<?php if ( $chips ) : ?>
				<ul class="scc-cart__meta">
					<?php foreach ( $chips as $chip ) : ?>
						<li><?php echo esc_html( $chip ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="scc-cart__foot">
				<p class="scc-cart__price">
					<small><?php esc_html_e( 'Asking price', 'salado-custom-carts' ); ?></small>
					<?php echo $price ? esc_html( $price ) : esc_html__( 'Call for price', 'salado-custom-carts' ); ?>
				</p>
				<a class="scc-cart__cta" href="<?php echo esc_url( $link ); ?>">
					<?php esc_html_e( 'View cart', 'salado-custom-carts' ); ?>
				</a>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * [scc_carts count="3" status="available"]
 */
function scc_carts_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'count'  => 3,
			'status' => 'available',
		),
		$atts,
		'scc_carts'
	);

	$args = array(
		'post_type'      => 'scc_cart',
		'posts_per_page' => (int) $atts['count'],
		'no_found_rows'  => true,
	);

	if ( 'any' !== $atts['status'] ) {
		$args['meta_query'] = array(
			'relation' => 'OR',
			array( 'key' => '_scc_status', 'value' => sanitize_key( $atts['status'] ) ),
			array( 'key' => '_scc_status', 'compare' => 'NOT EXISTS' ),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$out = '<div class="scc-carts">';
	foreach ( $query->posts as $post ) {
		$out .= scc_render_cart_card( $post->ID );
	}
	$out .= '</div>';

	wp_reset_postdata();

	return $out;
}
add_shortcode( 'scc_carts', 'scc_carts_shortcode' );

/**
 * [scc_phone] and [scc_email] - so contact details in page copy stay in sync
 * with Settings > Salado Details.
 */
function scc_phone_shortcode() {
	return sprintf(
		'<a href="tel:%s">%s</a>',
		esc_attr( scc_phone_link() ),
		esc_html( scc_detail( 'phone' ) )
	);
}
add_shortcode( 'scc_phone', 'scc_phone_shortcode' );

function scc_email_shortcode() {
	return sprintf(
		'<a href="mailto:%1$s">%1$s</a>',
		esc_attr( scc_detail( 'email' ) )
	);
}
add_shortcode( 'scc_email', 'scc_email_shortcode' );

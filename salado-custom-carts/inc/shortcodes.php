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

			<?php /* A sold cart has no asking price to quote, so the card drops that
			         block entirely rather than inviting a call about it. */ ?>
			<div class="scc-cart__foot<?php echo 'sold' === $status ? ' scc-cart__foot--sold' : ''; ?>">
				<?php if ( 'sold' !== $status ) : ?>
					<p class="scc-cart__price">
						<small><?php esc_html_e( 'Asking price', 'salado-custom-carts' ); ?></small>
						<?php echo $price ? esc_html( $price ) : esc_html__( 'Call for price', 'salado-custom-carts' ); ?>
					</p>
				<?php endif; ?>
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

	// Stock runs out. An empty grid would leave a heading floating over a gap, so
	// say something useful instead and keep the call to action alive.
	if ( ! $query->have_posts() ) {
		$out = sprintf(
			'<div class="scc-empty"><p class="scc-empty__title">%s</p><p class="scc-empty__text">%s</p><a class="scc-btn scc-btn--primary" href="tel:%s">%s</a></div>',
			esc_html__( 'Nothing in stock this minute', 'salado-custom-carts' ),
			esc_html__( 'Carts move quickly. Tell us what you are after - budget, seats, lifted or standard - and we will find one and build it out for you.', 'salado-custom-carts' ),
			esc_attr( scc_phone_link() ),
			sprintf( esc_html__( 'Call %s', 'salado-custom-carts' ), esc_html( scc_detail( 'phone' ) ) )
		);

		// Rather than end on "nothing here", show what has already gone out of
		// the workshop. An empty page proves nothing; recent builds do.
		$out .= scc_recent_builds( (int) $atts['count'] );

		return $out;
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
 * Recently sold carts, shown when there is nothing in stock. Returns an empty
 * string if there is nothing sold either, so a brand new site shows no stray
 * heading.
 */
function scc_recent_builds( $count = 3 ) {
	$query = new WP_Query( array(
		'post_type'      => 'scc_cart',
		'posts_per_page' => max( 1, (int) $count ),
		'no_found_rows'  => true,
		'meta_query'     => array(
			array( 'key' => '_scc_status', 'value' => 'sold' ),
		),
	) );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$out = sprintf(
		'<p class="scc-eyebrow scc-recent__label">%s</p><div class="scc-carts">',
		esc_html__( 'Recently built and sold', 'salado-custom-carts' )
	);

	foreach ( $query->posts as $post ) {
		$out .= scc_render_cart_card( $post->ID );
	}

	$out .= '</div>';

	wp_reset_postdata();

	return $out;
}

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

<?php
/**
 * A single cart. No add-to-cart anywhere - the call to action is a phone call.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$status   = scc_cart_status();
	$statuses = scc_cart_statuses();
	$price    = scc_cart_price();
	$chips    = scc_cart_meta_chips();
	$features = scc_cart_features();
	?>

	<div class="scc-pagehead">
		<div class="scc-container">
			<h1><?php the_title(); ?></h1>
			<?php if ( $chips ) : ?>
				<ul class="scc-cart__meta" style="margin-top:.9rem;">
					<?php foreach ( $chips as $chip ) : ?>
						<li><?php echo esc_html( $chip ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>

	<section class="scc-section scc-section--light">
		<div class="scc-container">
			<div class="scc-split">
				<div class="scc-split__media">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'large' );
					}
					?>
				</div>

				<div>
					<p class="scc-eyebrow">
						<?php echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status ); ?>
					</p>

					<?php /* No price headline on a cart that has already gone - the
					         eyebrow above says SOLD, and the note below explains it. */ ?>
					<?php if ( 'sold' !== $status ) : ?>
						<h2 style="font-size:clamp(1.8rem,4vw,2.6rem);">
							<?php echo $price ? esc_html( $price ) : esc_html__( 'Call for price', 'salado-custom-carts' ); ?>
						</h2>
					<?php endif; ?>

					<?php if ( $features ) : ?>
						<ul class="scc-check">
							<?php foreach ( $features as $feature ) : ?>
								<li>
									<?php echo scc_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<span><?php echo esc_html( $feature ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( 'sold' === $status ) : ?>
						<p><?php esc_html_e( 'This one has found a home - but we build carts like it all the time. Tell us what you are after.', 'salado-custom-carts' ); ?></p>
					<?php endif; ?>

					<div class="scc-hero__cta">
						<a class="scc-btn scc-btn--primary" href="tel:<?php echo esc_attr( scc_phone_link() ); ?>">
							<?php echo scc_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php printf( esc_html__( 'Call %s', 'salado-custom-carts' ), esc_html( scc_detail( 'phone' ) ) ); ?>
						</a>
						<a class="scc-btn scc-btn--outline-navy" href="mailto:<?php echo esc_attr( scc_detail( 'email' ) ); ?>?subject=<?php echo esc_attr( rawurlencode( get_the_title() ) ); ?>">
							<?php esc_html_e( 'Email about this cart', 'salado-custom-carts' ); ?>
						</a>
					</div>
				</div>
			</div>

			<?php if ( get_the_content() ) : ?>
				<div class="scc-content" style="padding-bottom:0;">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$more = new WP_Query( array(
		'post_type'      => 'scc_cart',
		'posts_per_page' => 3,
		'post__not_in'   => array( get_the_ID() ),
		'no_found_rows'  => true,
	) );

	if ( $more->have_posts() ) :
		?>
		<section class="scc-section scc-section--muted">
			<div class="scc-container">
				<div class="scc-sectionhead">
					<h2><?php esc_html_e( 'More carts', 'salado-custom-carts' ); ?></h2>
					<a class="scc-btn scc-btn--outline-navy" href="<?php echo esc_url( get_post_type_archive_link( 'scc_cart' ) ); ?>">
						<?php esc_html_e( 'See every cart', 'salado-custom-carts' ); ?>
					</a>
				</div>
				<div class="scc-carts">
					<?php
					foreach ( $more->posts as $other ) {
						echo scc_render_cart_card( $other->ID ); // phpcs:ignore WordPress.Security.EscapeOutput
					}
					?>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	endif;

endwhile;

get_footer();

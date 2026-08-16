<?php
/**
 * Carts for Sale - the catalogue grid.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="scc-pagehead">
	<div class="scc-container">
		<h1><?php esc_html_e( 'Carts for Sale', 'salado-custom-carts' ); ?></h1>
		<p><?php esc_html_e( 'Carts we have built or gone through ourselves. Sold in person only - call and we will walk you round it.', 'salado-custom-carts' ); ?></p>
	</div>
</div>

<section class="scc-section scc-section--muted">
	<div class="scc-container">
		<?php if ( have_posts() ) : ?>
			<div class="scc-carts">
				<?php
				while ( have_posts() ) :
					the_post();
					echo scc_render_cart_card( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 2 ) ); ?>
		<?php else : ?>
			<div class="scc-empty">
				<p class="scc-empty__title"><?php esc_html_e( 'Nothing in stock this minute', 'salado-custom-carts' ); ?></p>
				<p class="scc-empty__text"><?php esc_html_e( 'Carts move quickly. Tell us what you are after - budget, seats, lifted or standard - and we will find one and build it out for you.', 'salado-custom-carts' ); ?></p>
				<a class="scc-btn scc-btn--primary" href="tel:<?php echo esc_attr( scc_phone_link() ); ?>">
					<?php printf( esc_html__( 'Call %s', 'salado-custom-carts' ), esc_html( scc_detail( 'phone' ) ) ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="scc-section scc-cta">
	<div class="scc-container">
		<h2><?php esc_html_e( 'Not seeing the right cart?', 'salado-custom-carts' ); ?></h2>
		<p class="scc-lede"><?php esc_html_e( 'We can find a used cart and build it out the way you want it - wraps, wheels, lift, seats and a lithium pack.', 'salado-custom-carts' ); ?></p>
		<div class="scc-cta__actions">
			<a class="scc-btn scc-btn--primary" href="tel:<?php echo esc_attr( scc_phone_link() ); ?>">
				<?php echo scc_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php printf( esc_html__( 'Call %s', 'salado-custom-carts' ), esc_html( scc_detail( 'phone' ) ) ); ?>
			</a>
			<a class="scc-btn scc-btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
				<?php esc_html_e( 'Send a message', 'salado-custom-carts' ); ?>
			</a>
		</div>
	</div>
</section>

<?php
get_footer();

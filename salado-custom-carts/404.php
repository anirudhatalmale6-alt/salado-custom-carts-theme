<?php
/**
 * 404. Styled like the rest of the site so a mistyped URL never looks broken.
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
		<h1><?php esc_html_e( 'That page has moved', 'salado-custom-carts' ); ?></h1>
		<p><?php esc_html_e( 'The link you followed does not exist any more. Everything we do is one of these:', 'salado-custom-carts' ); ?></p>
	</div>
</div>

<section class="scc-section scc-section--muted">
	<div class="scc-container">
		<div class="scc-cta__actions" style="justify-content:flex-start;">
			<a class="scc-btn scc-btn--navy" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'salado-custom-carts' ); ?></a>
			<a class="scc-btn scc-btn--outline-navy" href="<?php echo esc_url( get_post_type_archive_link( 'scc_cart' ) ); ?>"><?php esc_html_e( 'Carts for sale', 'salado-custom-carts' ); ?></a>
			<a class="scc-btn scc-btn--outline-navy" href="<?php echo esc_url( home_url( '/lithium-battery-upgrades/' ) ); ?>"><?php esc_html_e( 'Battery upgrades', 'salado-custom-carts' ); ?></a>
			<a class="scc-btn scc-btn--primary" href="tel:<?php echo esc_attr( scc_phone_link() ); ?>"><?php printf( esc_html__( 'Call %s', 'salado-custom-carts' ), esc_html( scc_detail( 'phone' ) ) ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();

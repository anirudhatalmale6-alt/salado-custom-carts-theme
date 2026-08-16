<?php
/**
 * Site footer plus the mobile sticky call bar.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer class="scc-footer">
	<div class="scc-container">
		<div class="scc-footer__grid">
			<div>
				<span class="scc-footer__logo">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.png' ); ?>"
						width="900" height="453"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
				</span>
				<p style="margin-top:1.1rem;max-width:38ch;">
					<?php esc_html_e( 'Custom golf carts, lithium battery upgrades and cart repairs, built and serviced right here in', 'salado-custom-carts' ); ?>
					<?php echo esc_html( scc_detail( 'town' ) ); ?>.
				</p>
			</div>

			<div>
				<h3><?php esc_html_e( 'Get in touch', 'salado-custom-carts' ); ?></h3>
				<ul>
					<li><a href="tel:<?php echo esc_attr( scc_phone_link() ); ?>"><?php echo esc_html( scc_detail( 'phone' ) ); ?></a></li>
					<li><a href="mailto:<?php echo esc_attr( scc_detail( 'email' ) ); ?>"><?php echo esc_html( scc_detail( 'email' ) ); ?></a></li>
					<li><?php echo esc_html( scc_detail( 'town' ) ); ?></li>
					<li><?php echo esc_html( scc_detail( 'hours' ) ); ?></li>
				</ul>
			</div>

			<div>
				<h3><?php esc_html_e( 'Explore', 'salado-custom-carts' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => '',
						'depth'          => 1,
					) );
				} else {
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => '',
						'fallback_cb'    => 'scc_fallback_menu',
						'depth'          => 1,
					) );
				}
				?>
			</div>
		</div>

		<div class="scc-footer__bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'salado-custom-carts' ); ?></span>
			<span><?php esc_html_e( 'Carts are sold in person only - this site does not take online payments.', 'salado-custom-carts' ); ?></span>
		</div>
	</div>
</footer>

<div class="scc-callbar">
	<a href="tel:<?php echo esc_attr( scc_phone_link() ); ?>">
		<?php echo scc_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php esc_html_e( 'Call now', 'salado-custom-carts' ); ?>
	</a>
	<a href="mailto:<?php echo esc_attr( scc_detail( 'email' ) ); ?>">
		<?php echo scc_icon( 'mail', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php esc_html_e( 'Email us', 'salado-custom-carts' ); ?>
	</a>
</div>

<?php wp_footer(); ?>
</body>
</html>

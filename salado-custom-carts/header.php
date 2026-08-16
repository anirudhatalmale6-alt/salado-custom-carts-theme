<?php
/**
 * Site header.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="scc-skip" href="#scc-main"><?php esc_html_e( 'Skip to content', 'salado-custom-carts' ); ?></a>

<div class="scc-topbar">
	<div class="scc-container scc-topbar__inner">
		<span class="scc-topbar__note">
			<?php echo scc_icon( 'truck', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo esc_html( scc_detail( 'pickup_note' ) ); ?>
		</span>
		<span class="scc-topbar__links">
			<a href="mailto:<?php echo esc_attr( scc_detail( 'email' ) ); ?>"><?php echo esc_html( scc_detail( 'email' ) ); ?></a>
			<a href="tel:<?php echo esc_attr( scc_phone_link() ); ?>"><?php echo esc_html( scc_detail( 'phone' ) ); ?></a>
		</span>
	</div>
</div>

<header class="scc-header">
	<div class="scc-container scc-header__inner">
		<a class="scc-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php
			if ( has_custom_logo() ) {
				$logo_id = get_theme_mod( 'custom_logo' );
				echo wp_get_attachment_image( $logo_id, 'full', false, array( 'alt' => get_bloginfo( 'name' ) ) );
			} else {
				printf(
					'<img src="%s" width="900" height="453" alt="%s" />',
					esc_url( get_template_directory_uri() . '/assets/img/logo.png' ),
					esc_attr( get_bloginfo( 'name' ) )
				);
			}
			?>
		</a>

		<nav class="scc-nav" id="scc-nav" aria-label="<?php esc_attr_e( 'Primary', 'salado-custom-carts' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'scc-nav__list',
				'fallback_cb'    => 'scc_fallback_menu',
				'depth'          => 1,
			) );
			?>
		</nav>

		<div class="scc-header__actions">
			<a class="scc-header__phone" href="tel:<?php echo esc_attr( scc_phone_link() ); ?>">
				<?php echo esc_html( scc_detail( 'phone' ) ); ?>
			</a>
			<button class="scc-burger" type="button" aria-expanded="false" aria-controls="scc-nav">
				<span class="scc-burger__bars" aria-hidden="true"></span>
				<?php esc_html_e( 'Menu', 'salado-custom-carts' ); ?>
			</button>
		</div>
	</div>
</header>

<main id="scc-main">

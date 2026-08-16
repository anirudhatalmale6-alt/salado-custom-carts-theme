<?php
/**
 * Salado Custom Carts - theme setup.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SCC_VERSION', '1.1.0' );

/**
 * Business details live in one place so they can be changed once and update
 * everywhere - header, footer, hero, call bar, schema markup.
 * Settings > Salado Details in the dashboard writes to these options.
 */
function scc_detail( $key ) {
	$defaults = array(
		'phone'         => '512-814-6750',
		'email'         => 'sales@SaladoCustomCarts.com',
		// Service and repair bookings go to Andrew, not the general sales inbox.
		'service_email' => 'andrew@saladocustomcarts.com',
		'town'          => 'Salado, Texas',
		'hours'         => 'Mon - Sat by appointment',
		'pickup_note'   => 'FREE local cart pickup and delivery',
	);

	$value = get_option( 'scc_' . $key, '' );

	return '' !== $value ? $value : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/** Phone stripped to digits, for tel: links. */
function scc_phone_link() {
	return preg_replace( '/[^0-9+]/', '', scc_detail( 'phone' ) );
}

/**
 * The "Schedule Your Service" link. A mailto with the subject and the questions
 * already filled in, so the customer just adds their answers and hits send -
 * and the reply lands with enough detail to actually book the job.
 */
function scc_service_link() {
	$body = implode(
		"\r\n",
		array(
			'What the cart is doing (or what you want done):',
			'',
			'Cart make and model:',
			'',
			'Battery type, if you know it:',
			'',
			'Your name and phone:',
			'',
			'Your address, if you would like us to collect it:',
			'',
		)
	);

	// The address itself is left as-is - percent-encoding the @ is legal but some
	// mail clients handle it badly. Only the subject and body are encoded.
	return sprintf(
		'mailto:%s?subject=%s&body=%s',
		sanitize_email( scc_detail( 'service_email' ) ),
		rawurlencode( 'Service request' ),
		rawurlencode( $body )
	);
}

function scc_setup() {
	load_theme_textdomain( 'salado-custom-carts', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 239,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'assets/css/theme.css', 'assets/css/blocks.css' ) );

	add_image_size( 'scc-cart', 900, 675, true );
	add_image_size( 'scc-hero', 1900, 1100, true );

	register_nav_menus( array(
		'primary' => __( 'Primary menu', 'salado-custom-carts' ),
		'footer'  => __( 'Footer menu', 'salado-custom-carts' ),
	) );
}
add_action( 'after_setup_theme', 'scc_setup' );

function scc_assets() {
	wp_enqueue_style( 'scc-theme', get_template_directory_uri() . '/assets/css/theme.css', array(), SCC_VERSION );
	wp_enqueue_style( 'scc-blocks', get_template_directory_uri() . '/assets/css/blocks.css', array( 'scc-theme' ), SCC_VERSION );
	wp_enqueue_script( 'scc-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), SCC_VERSION, true );

	// Headings use Barlow Condensed, body text Inter. Loaded from the theme so
	// there is no third-party font request on every page view.
	wp_enqueue_style( 'scc-fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), SCC_VERSION );
}
add_action( 'wp_enqueue_scripts', 'scc_assets' );

require_once get_template_directory() . '/inc/cpt-carts.php';
require_once get_template_directory() . '/inc/cart-fields.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/settings.php';
require_once get_template_directory() . '/inc/patterns.php';

/**
 * Small icon helper. Inline SVG keeps it to zero extra requests and lets the
 * icons inherit currentColor.
 */
function scc_icon( $name, $size = 22 ) {
	$paths = array(
		'check'    => '<path d="M20 6 9 17l-5-5"/>',
		'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'pin'      => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
		'truck'    => '<path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
		'battery'  => '<rect x="1" y="6" width="18" height="12" rx="2"/><path d="M23 13v-2"/><path d="M7 12h6"/><path d="M10 9v6"/>',
		'wrench'   => '<path d="M14.7 6.3a4 4 0 0 0 5 5l-9 9a2.8 2.8 0 1 1-4-4l9-9z"/>',
		'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
		'arrow'    => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
		'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
		'clock'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Fallback menu, so a brand new install never shows an empty header.
 */
function scc_fallback_menu() {
	$items = array(
		home_url( '/' )                  => __( 'Home', 'salado-custom-carts' ),
		home_url( '/carts-for-sale/' )   => __( 'Carts for Sale', 'salado-custom-carts' ),
		home_url( '/lithium-battery-upgrades/' ) => __( 'Battery Upgrades', 'salado-custom-carts' ),
		home_url( '/appearance-upgrades/' )      => __( 'Appearance Upgrades', 'salado-custom-carts' ),
		home_url( '/performance-upgrades/' )     => __( 'Performance Upgrades', 'salado-custom-carts' ),
		home_url( '/contact/' )          => __( 'Contact', 'salado-custom-carts' ),
	);

	echo '<ul class="scc-nav__list">';
	foreach ( $items as $url => $label ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Body classes used by the stylesheet.
 */
function scc_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'scc-front';
	}
	return $classes;
}
add_filter( 'body_class', 'scc_body_class' );

/**
 * WooCommerce is left installed but dormant during the migration. If it is
 * still active, stop it loading its cart/checkout scripts on every page -
 * this is an informational site with no online sales.
 */
function scc_dequeue_woo_assets() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	wp_dequeue_style( 'woocommerce-general' );
	wp_dequeue_style( 'woocommerce-layout' );
	wp_dequeue_style( 'woocommerce-smallscreen' );
	wp_dequeue_script( 'wc-cart-fragments' );
	wp_dequeue_script( 'woocommerce' );
	wp_dequeue_script( 'wc-add-to-cart' );
}
add_action( 'wp_enqueue_scripts', 'scc_dequeue_woo_assets', 99 );

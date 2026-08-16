<?php
/**
 * Child theme. Deliberately near-empty - it exists so future tweaks survive
 * parent theme updates.
 *
 * @package salado-custom-carts-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scc_child_styles() {
	wp_enqueue_style(
		'scc-child',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'scc-theme' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'scc_child_styles', 20 );

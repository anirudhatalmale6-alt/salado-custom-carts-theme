<?php
/**
 * The "Carts" post type - replaces WooCommerce products for the catalogue.
 *
 * Carts are shown, never sold online, so this is a plain content type with the
 * fields that actually matter for a cart listing.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scc_register_cart_cpt() {
	$labels = array(
		'name'               => __( 'Carts', 'salado-custom-carts' ),
		'singular_name'      => __( 'Cart', 'salado-custom-carts' ),
		'add_new'            => __( 'Add Cart', 'salado-custom-carts' ),
		'add_new_item'       => __( 'Add New Cart', 'salado-custom-carts' ),
		'edit_item'          => __( 'Edit Cart', 'salado-custom-carts' ),
		'new_item'           => __( 'New Cart', 'salado-custom-carts' ),
		'view_item'          => __( 'View Cart', 'salado-custom-carts' ),
		'search_items'       => __( 'Search Carts', 'salado-custom-carts' ),
		'not_found'          => __( 'No carts yet', 'salado-custom-carts' ),
		'not_found_in_trash' => __( 'No carts in the trash', 'salado-custom-carts' ),
		'all_items'          => __( 'All Carts', 'salado-custom-carts' ),
		'menu_name'          => __( 'Carts', 'salado-custom-carts' ),
	);

	register_post_type( 'scc_cart', array(
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => 'carts-for-sale',
		'rewrite'       => array( 'slug' => 'carts', 'with_front' => false ),
		'menu_icon'     => 'dashicons-car',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
		'taxonomies'    => array( 'scc_cart_type' ),
	) );

	register_taxonomy( 'scc_cart_type', 'scc_cart', array(
		'labels'            => array(
			'name'          => __( 'Cart Types', 'salado-custom-carts' ),
			'singular_name' => __( 'Cart Type', 'salado-custom-carts' ),
			'menu_name'     => __( 'Cart Types', 'salado-custom-carts' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'cart-type' ),
	) );
}
add_action( 'init', 'scc_register_cart_cpt' );

/**
 * The archive's own label is "Carts"; the browser tab should say what the page
 * actually is.
 */
function scc_cart_archive_title( $title ) {
	if ( is_post_type_archive( 'scc_cart' ) ) {
		return __( 'Carts for Sale', 'salado-custom-carts' );
	}
	return $title;
}
add_filter( 'post_type_archive_title', 'scc_cart_archive_title' );

/**
 * Status list, used by the edit screen, the badge and the filters.
 */
function scc_cart_statuses() {
	return array(
		'available' => __( 'Available', 'salado-custom-carts' ),
		'pending'   => __( 'Sale Pending', 'salado-custom-carts' ),
		'sold'      => __( 'Sold', 'salado-custom-carts' ),
	);
}

/**
 * Sold carts stay on the site as a portfolio, so ordering puts available
 * carts first and sold carts last rather than hiding them.
 */
function scc_cart_status( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$status  = get_post_meta( $post_id, '_scc_status', true );

	return $status ? $status : 'available';
}

function scc_cart_price( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	return trim( (string) get_post_meta( $post_id, '_scc_price', true ) );
}

/**
 * Admin columns - so the cart list is useful at a glance.
 */
function scc_cart_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['scc_status'] = __( 'Status', 'salado-custom-carts' );
			$new['scc_price']  = __( 'Price', 'salado-custom-carts' );
		}
	}
	return $new;
}
add_filter( 'manage_scc_cart_posts_columns', 'scc_cart_columns' );

function scc_cart_column_content( $column, $post_id ) {
	if ( 'scc_status' === $column ) {
		$statuses = scc_cart_statuses();
		$status   = scc_cart_status( $post_id );
		echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status );
	}
	if ( 'scc_price' === $column ) {
		$price = scc_cart_price( $post_id );
		echo $price ? esc_html( $price ) : '&mdash;';
	}
}
add_action( 'manage_scc_cart_posts_custom_column', 'scc_cart_column_content', 10, 2 );

/**
 * Archive ordering: available first, then pending, then sold.
 */
function scc_cart_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'scc_cart' ) || $query->is_tax( 'scc_cart_type' ) ) {
		$query->set( 'posts_per_page', 24 );
		$query->set( 'meta_key', '_scc_status_order' );
		$query->set( 'orderby', array( 'meta_value_num' => 'ASC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'scc_cart_archive_order' );

/**
 * Keep a numeric sort key in step with the status, so the archive can order by
 * it without a slow post-query sort.
 */
function scc_sync_status_order( $post_id ) {
	if ( 'scc_cart' !== get_post_type( $post_id ) ) {
		return;
	}
	$map   = array( 'available' => 1, 'pending' => 2, 'sold' => 3 );
	$state = scc_cart_status( $post_id );
	update_post_meta( $post_id, '_scc_status_order', isset( $map[ $state ] ) ? $map[ $state ] : 1 );
}
add_action( 'save_post_scc_cart', 'scc_sync_status_order', 20 );

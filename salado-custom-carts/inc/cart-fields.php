<?php
/**
 * Cart detail fields - a plain WordPress meta box, no ACF or other plugin
 * needed. Fields are registered for REST too, so they keep working if the site
 * ever moves to the block editor sidebar.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scc_cart_field_map() {
	return array(
		'_scc_price'    => array( 'label' => __( 'Price', 'salado-custom-carts' ), 'type' => 'text', 'hint' => __( 'e.g. $8,500 - shown as "Asking price". Leave blank to show "Call for price".', 'salado-custom-carts' ) ),
		'_scc_year'     => array( 'label' => __( 'Year', 'salado-custom-carts' ), 'type' => 'text', 'hint' => '' ),
		'_scc_make'     => array( 'label' => __( 'Make', 'salado-custom-carts' ), 'type' => 'text', 'hint' => __( 'Club Car, EZGO, Evolution...', 'salado-custom-carts' ) ),
		'_scc_model'    => array( 'label' => __( 'Model', 'salado-custom-carts' ), 'type' => 'text', 'hint' => '' ),
		'_scc_battery'  => array( 'label' => __( 'Battery', 'salado-custom-carts' ), 'type' => 'text', 'hint' => __( 'e.g. 48V Lithium, 36V Lead-acid', 'salado-custom-carts' ) ),
		'_scc_seats'    => array( 'label' => __( 'Seats', 'salado-custom-carts' ), 'type' => 'text', 'hint' => '' ),
		'_scc_features' => array( 'label' => __( 'Features', 'salado-custom-carts' ), 'type' => 'textarea', 'hint' => __( 'One per line - lift kit, custom wheels, rear seat, lights...', 'salado-custom-carts' ) ),
	);
}

function scc_register_cart_meta() {
	foreach ( array_keys( scc_cart_field_map() ) as $key ) {
		register_post_meta( 'scc_cart', $key, array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}

	register_post_meta( 'scc_cart', '_scc_status', array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
}
add_action( 'init', 'scc_register_cart_meta' );

function scc_cart_meta_box() {
	add_meta_box(
		'scc_cart_details',
		__( 'Cart Details', 'salado-custom-carts' ),
		'scc_cart_meta_box_render',
		'scc_cart',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'scc_cart_meta_box' );

function scc_cart_meta_box_render( $post ) {
	wp_nonce_field( 'scc_save_cart', 'scc_cart_nonce' );
	$statuses = scc_cart_statuses();
	$current  = scc_cart_status( $post->ID );
	?>
	<style>
		.scc-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 24px; }
		.scc-fields .scc-field--wide { grid-column: 1 / -1; }
		.scc-fields label { display: block; font-weight: 600; margin-bottom: 4px; }
		.scc-fields input[type="text"], .scc-fields textarea, .scc-fields select { width: 100%; }
		.scc-fields .description { margin-top: 4px; }
	</style>
	<div class="scc-fields">
		<div class="scc-field">
			<label for="scc_status"><?php esc_html_e( 'Status', 'salado-custom-carts' ); ?></label>
			<select name="scc_status" id="scc_status">
				<?php foreach ( $statuses as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Sold carts stay on the site with a "Sold" badge - they show off your work.', 'salado-custom-carts' ); ?></p>
		</div>

		<?php foreach ( scc_cart_field_map() as $key => $field ) : ?>
			<?php $value = get_post_meta( $post->ID, $key, true ); ?>
			<div class="scc-field <?php echo 'textarea' === $field['type'] ? 'scc-field--wide' : ''; ?>">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				<?php if ( 'textarea' === $field['type'] ) : ?>
					<textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="4"><?php echo esc_textarea( $value ); ?></textarea>
				<?php else : ?>
					<input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
				<?php endif; ?>
				<?php if ( $field['hint'] ) : ?>
					<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

function scc_save_cart_meta( $post_id ) {
	if ( ! isset( $_POST['scc_cart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['scc_cart_nonce'] ) ), 'scc_save_cart' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( scc_cart_field_map() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		$value = 'textarea' === $field['type']
			? sanitize_textarea_field( $raw )
			: sanitize_text_field( $raw );
		update_post_meta( $post_id, $key, $value );
	}

	if ( isset( $_POST['scc_status'] ) ) {
		$status   = sanitize_key( wp_unslash( $_POST['scc_status'] ) );
		$statuses = scc_cart_statuses();
		update_post_meta( $post_id, '_scc_status', isset( $statuses[ $status ] ) ? $status : 'available' );
	}
}
add_action( 'save_post_scc_cart', 'scc_save_cart_meta', 10 );

/**
 * The chips shown under a cart title - only the fields that were filled in.
 */
function scc_cart_meta_chips( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$chips   = array();

	foreach ( array( '_scc_year', '_scc_make', '_scc_battery', '_scc_seats' ) as $key ) {
		$value = trim( (string) get_post_meta( $post_id, $key, true ) );
		if ( '' !== $value ) {
			$chips[] = $value;
		}
	}

	return $chips;
}

function scc_cart_features( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = (string) get_post_meta( $post_id, '_scc_features', true );

	if ( '' === trim( $raw ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
}

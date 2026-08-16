<?php
/**
 * Settings > Salado Details.
 *
 * Phone, email and the pickup line appear in several places on the site. This
 * screen is the single place they are edited.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scc_settings_fields() {
	return array(
		'phone'       => array( 'label' => __( 'Phone number', 'salado-custom-carts' ), 'hint' => __( 'Shown in the header, footer and the mobile Call button.', 'salado-custom-carts' ) ),
		'email'       => array( 'label' => __( 'Email address', 'salado-custom-carts' ), 'hint' => '' ),
		'town'        => array( 'label' => __( 'Town', 'salado-custom-carts' ), 'hint' => __( 'e.g. Salado, Texas', 'salado-custom-carts' ) ),
		'hours'       => array( 'label' => __( 'Hours', 'salado-custom-carts' ), 'hint' => '' ),
		'pickup_note' => array( 'label' => __( 'Pickup line', 'salado-custom-carts' ), 'hint' => __( 'The short line shown in the blue bar at the top of every page.', 'salado-custom-carts' ) ),
	);
}

function scc_register_settings() {
	foreach ( array_keys( scc_settings_fields() ) as $key ) {
		register_setting( 'scc_details', 'scc_' . $key, array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );
	}
}
add_action( 'admin_init', 'scc_register_settings' );

function scc_settings_page() {
	add_options_page(
		__( 'Salado Details', 'salado-custom-carts' ),
		__( 'Salado Details', 'salado-custom-carts' ),
		'manage_options',
		'scc-details',
		'scc_settings_page_render'
	);
}
add_action( 'admin_menu', 'scc_settings_page' );

function scc_settings_page_render() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Salado Details', 'salado-custom-carts' ); ?></h1>
		<p><?php esc_html_e( 'These details appear across the site. Change them here once and every page updates.', 'salado-custom-carts' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'scc_details' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( scc_settings_fields() as $key => $field ) : ?>
					<tr>
						<th scope="row">
							<label for="scc_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
						</th>
						<td>
							<input
								type="text"
								class="regular-text"
								id="scc_<?php echo esc_attr( $key ); ?>"
								name="scc_<?php echo esc_attr( $key ); ?>"
								value="<?php echo esc_attr( scc_detail( $key ) ); ?>" />
							<?php if ( $field['hint'] ) : ?>
								<p class="description"><?php echo esc_html( $field['hint'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

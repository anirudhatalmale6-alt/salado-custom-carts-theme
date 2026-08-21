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
		'email'         => array( 'label' => __( 'Email address', 'salado-custom-carts' ), 'hint' => __( 'General enquiries - shown in the top bar and footer.', 'salado-custom-carts' ) ),
		'service_email' => array( 'label' => __( 'Service / repair email', 'salado-custom-carts' ), 'hint' => __( 'Where quote requests are emailed. Also used by the Request a Quote buttons if no page has the form on it.', 'salado-custom-carts' ) ),
		'town'        => array( 'label' => __( 'Town', 'salado-custom-carts' ), 'hint' => __( 'e.g. Salado, Texas', 'salado-custom-carts' ) ),
		'hours'       => array( 'label' => __( 'Hours', 'salado-custom-carts' ), 'hint' => '' ),
		'pickup_note' => array( 'label' => __( 'Pickup line', 'salado-custom-carts' ), 'hint' => __( 'The short line shown in the blue bar at the top of every page.', 'salado-custom-carts' ) ),
	);
}

/**
 * Spam protection, kept in its own group so it reads as a separate job from the
 * shop's phone number and hours.
 */
function scc_spam_settings_fields() {
	return array(
		'turnstile_site'   => array(
			'label' => __( 'Turnstile site key', 'salado-custom-carts' ),
			'hint'  => __( 'From dash.cloudflare.com > Turnstile > Add site. Free, and it does not require your website to be on Cloudflare.', 'salado-custom-carts' ),
		),
		'turnstile_secret' => array(
			'label' => __( 'Turnstile secret key', 'salado-custom-carts' ),
			'hint'  => __( 'The second key on the same screen. The CAPTCHA only switches on once both boxes are filled in.', 'salado-custom-carts' ),
		),
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
	foreach ( array_keys( scc_spam_settings_fields() ) as $key ) {
		register_setting( 'scc_details', 'scc_' . $key, array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		) );
	}
	register_setting( 'scc_details', 'scc_builtin_captcha', array(
		'type'              => 'string',
		'sanitize_callback' => 'scc_sanitise_checkbox',
		'default'           => '',
	) );
}

/**
 * An unticked checkbox posts nothing at all, so anything that is not exactly
 * '1' means off.
 */
function scc_sanitise_checkbox( $value ) {
	return '1' === (string) $value ? '1' : '';
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

			<h2><?php esc_html_e( 'Spam protection', 'salado-custom-carts' ); ?></h2>
			<p>
				<?php esc_html_e( 'The quote form already blocks the obvious junk on its own, with nothing switched on below. The options here add a visible check on top of that.', 'salado-custom-carts' ); ?>
				<?php
				$blocked = (int) get_option( 'scc_spam_blocked', 0 );
				if ( $blocked > 0 ) {
					echo '<br /><strong>';
					printf(
						/* translators: %d: number of blocked submissions */
						esc_html( _n( '%d spam submission stopped so far.', '%d spam submissions stopped so far.', $blocked, 'salado-custom-carts' ) ),
						(int) $blocked
					);
					echo '</strong> ';
					esc_html_e( 'Anything it was unsure about is held as a draft under Quote Requests rather than deleted.', 'salado-custom-carts' );
				}
				?>
			</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Ask a simple question', 'salado-custom-carts' ); ?></th>
					<td>
						<label for="scc_builtin_captcha">
							<input type="checkbox" id="scc_builtin_captcha" name="scc_builtin_captcha" value="1"
								<?php checked( '1', (string) get_option( 'scc_builtin_captcha', '' ) ); ?> />
							<?php esc_html_e( 'Add a one-line question to the quote form, e.g. "What is four plus three?"', 'salado-custom-carts' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'No account with anyone, nothing sent to a third party, works on any host. This is the simplest option and it is switched on and off right here.', 'salado-custom-carts' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Optional: Cloudflare Turnstile', 'salado-custom-carts' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'You do not need this. It is a stronger CAPTCHA than the question above, but it needs a free Cloudflare account. Leave both boxes empty to skip it entirely. If you do fill them in, Turnstile is used and the question above is hidden, so nobody is asked twice.', 'salado-custom-carts' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<?php foreach ( scc_spam_settings_fields() as $key => $field ) : ?>
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
								value="<?php echo esc_attr( (string) get_option( 'scc_' . $key, '' ) ); ?>" />
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

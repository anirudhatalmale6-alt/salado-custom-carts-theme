<?php
/**
 * The Request a Quote form.
 *
 * The old Contact page said "send us a note using the form below" but the form
 * itself was a page-builder module that did not survive the rebuild, so the
 * page was promising something that was not there.
 *
 * Every submission is saved as a post in the dashboard AND emailed. Saving
 * first matters: if the host ever drops outgoing mail, the enquiry is still
 * sitting in Quote Requests rather than lost.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Somewhere for submissions to land. Not public - it is a private inbox.
 */
function scc_register_enquiry_cpt() {
	register_post_type( 'scc_enquiry', array(
		'labels' => array(
			'name'          => __( 'Quote Requests', 'salado-custom-carts' ),
			'singular_name' => __( 'Quote Request', 'salado-custom-carts' ),
			'menu_name'     => __( 'Quote Requests', 'salado-custom-carts' ),
			'all_items'     => __( 'Quote Requests', 'salado-custom-carts' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 26,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'supports'            => array( 'title' ),
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'has_archive'         => false,
		'rewrite'             => false,
	) );
}
add_action( 'init', 'scc_register_enquiry_cpt' );

function scc_enquiry_fields() {
	return array(
		'name'    => array( 'label' => __( 'Your name', 'salado-custom-carts' ), 'type' => 'text',     'required' => true,  'autocomplete' => 'name' ),
		'phone'   => array( 'label' => __( 'Phone', 'salado-custom-carts' ),     'type' => 'tel',      'required' => true,  'autocomplete' => 'tel' ),
		'email'   => array( 'label' => __( 'Email', 'salado-custom-carts' ),     'type' => 'email',    'required' => false, 'autocomplete' => 'email' ),
		'cart'    => array( 'label' => __( 'Cart make and model', 'salado-custom-carts' ), 'type' => 'text', 'required' => false, 'hint' => __( 'If you have one already. Leave blank if you are after a build.', 'salado-custom-carts' ) ),
		'message' => array( 'label' => __( 'What do you need?', 'salado-custom-carts' ), 'type' => 'textarea', 'required' => true, 'hint' => __( 'Repair, lithium upgrade, lift kit, wheels, a full custom build - tell us as much as you like.', 'salado-custom-carts' ) ),
	);
}

/**
 * Shows the columns that matter in the dashboard list, so he can triage
 * without opening every enquiry.
 */
function scc_enquiry_columns( $columns ) {
	return array(
		'cb'        => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'     => __( 'From', 'salado-custom-carts' ),
		'scc_phone' => __( 'Phone', 'salado-custom-carts' ),
		'scc_what'  => __( 'What they need', 'salado-custom-carts' ),
		'scc_flag'  => __( 'Spam check', 'salado-custom-carts' ),
		'date'      => __( 'Received', 'salado-custom-carts' ),
	);
}
add_filter( 'manage_scc_enquiry_posts_columns', 'scc_enquiry_columns' );

function scc_enquiry_column( $column, $post_id ) {
	if ( 'scc_phone' === $column ) {
		$phone = get_post_meta( $post_id, '_scc_e_phone', true );
		if ( $phone ) {
			printf( '<a href="tel:%s">%s</a>', esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ), esc_html( $phone ) );
		}
	}
	if ( 'scc_what' === $column ) {
		echo esc_html( wp_trim_words( (string) get_post_meta( $post_id, '_scc_e_message', true ), 18 ) );
	}
	if ( 'scc_flag' === $column ) {
		$flag = (string) get_post_meta( $post_id, '_scc_e_spam', true );
		if ( '' === $flag ) {
			printf( '<span style="color:#1a7f37">%s</span>', esc_html__( 'Passed', 'salado-custom-carts' ) );
		} else {
			printf(
				'<span style="color:#b32d2e" title="%s">%s</span><br /><small>%s</small>',
				esc_attr( $flag ),
				esc_html__( 'Looks like spam', 'salado-custom-carts' ),
				esc_html( $flag )
			);
		}
	}
}
add_action( 'manage_scc_enquiry_posts_custom_column', 'scc_enquiry_column', 10, 2 );

/**
 * The full enquiry, shown on the edit screen. Read-only on purpose - this is a
 * record of what someone sent, not something to be edited.
 */
function scc_enquiry_meta_box() {
	add_meta_box( 'scc_enquiry_detail', __( 'Enquiry', 'salado-custom-carts' ), 'scc_enquiry_meta_box_render', 'scc_enquiry', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'scc_enquiry_meta_box' );

function scc_enquiry_meta_box_render( $post ) {
	$flag = (string) get_post_meta( $post->ID, '_scc_e_spam', true );
	if ( '' !== $flag ) {
		printf(
			'<div class="notice notice-warning inline" style="margin:0 0 12px"><p><strong>%s</strong> %s<br /><em>%s</em></p></div>',
			esc_html__( 'Held as suspected spam.', 'salado-custom-carts' ),
			esc_html__( 'It was not emailed to you. If it is genuine, hit Publish and it will move to your real enquiries.', 'salado-custom-carts' ),
			esc_html( $flag )
		);
	}

	echo '<table class="widefat striped"><tbody>';
	foreach ( scc_enquiry_fields() as $key => $field ) {
		$value = (string) get_post_meta( $post->ID, '_scc_e_' . $key, true );
		printf(
			'<tr><th style="width:190px">%s</th><td>%s</td></tr>',
			esc_html( $field['label'] ),
			'' === $value ? '<em>' . esc_html__( 'not given', 'salado-custom-carts' ) . '</em>' : nl2br( esc_html( $value ) )
		);
	}
	printf(
		'<tr><th>%s</th><td>%s</td></tr>',
		esc_html__( 'Sent from', 'salado-custom-carts' ),
		esc_html( (string) get_post_meta( $post->ID, '_scc_e_page', true ) )
	);
	echo '</tbody></table>';
}

/**
 * Where to send the visitor back to.
 *
 * NOT wp_get_referer(): the form posts to its own URL, and wp_get_referer()
 * deliberately returns false when the referer matches the current request. That
 * silently bounced every submission to the homepage, where there is no form and
 * therefore no confirmation message. The page sends its own address instead,
 * run through wp_validate_redirect so it can only ever point at this site.
 */
function scc_quote_return_url() {
	$raw = isset( $_POST['scc_return'] ) ? esc_url_raw( wp_unslash( $_POST['scc_return'] ) ) : '';
	$url = $raw ? wp_validate_redirect( $raw, '' ) : '';

	return $url ? $url : home_url( '/' );
}

/**
 * Handles the POST. Runs on init so a redirect is still possible.
 */
function scc_handle_quote_form() {
	if ( empty( $_POST['scc_quote_submit'] ) ) {
		return;
	}
	if ( ! isset( $_POST['scc_quote_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['scc_quote_nonce'] ) ), 'scc_quote' ) ) {
		return;
	}

	$values = array();
	$errors = array();

	foreach ( scc_enquiry_fields() as $key => $field ) {
		$raw = isset( $_POST[ 'scc_' . $key ] ) ? wp_unslash( $_POST[ 'scc_' . $key ] ) : '';

		if ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} elseif ( 'email' === $field['type'] ) {
			$value = sanitize_email( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}

		if ( ! empty( $field['required'] ) && '' === trim( $value ) ) {
			$errors[] = $field['label'];
		}
		if ( 'email' === $field['type'] && '' !== trim( (string) $raw ) && ! is_email( $value ) ) {
			$errors[] = $field['label'];
		}

		$values[ $key ] = $value;
	}

	$back = scc_quote_return_url();

	/*
	 * Spam screening. Runs before the validation errors below, so a bot filling
	 * the form badly is thrown out rather than being handed a helpful list of
	 * what it got wrong. See inc/antispam.php for what each action means.
	 */
	$screen = scc_screen_submission( $values );

	if ( 'drop' === $screen['action'] ) {
		// Certain bot. Show it the success page so it stops retrying, and save
		// nothing at all.
		scc_count_blocked();
		wp_safe_redirect( add_query_arg( 'quote', 'sent', $back ) . '#request-a-quote' );
		exit;
	}

	if ( 'block' === $screen['action'] ) {
		// A person can fail a CAPTCHA. Give them their typing back and let them
		// try again, rather than pretending it sent.
		scc_count_blocked();
		set_transient( 'scc_quote_old_' . scc_quote_visitor_key(), $values, 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'quote', 'captcha', $back ) . '#request-a-quote' );
		exit;
	}

	if ( $errors ) {
		set_transient( 'scc_quote_old_' . scc_quote_visitor_key(), $values, 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'quote', 'error', $back ) . '#request-a-quote' );
		exit;
	}

	$suspect = 'flag' === $screen['action'];

	// Save first, mail second. A saved enquiry survives a mail failure.
	// Suspected spam is saved too, as a draft, so a wrong guess by the filter
	// costs Andrew a click rather than a customer.
	$post_id = wp_insert_post( array(
		'post_type'   => 'scc_enquiry',
		'post_status' => $suspect ? 'draft' : 'publish',
		'post_title'  => sprintf(
			/* translators: 1: sender name, 2: phone number */
			__( '%1$s - %2$s', 'salado-custom-carts' ),
			$values['name'],
			$values['phone']
		),
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_safe_redirect( add_query_arg( 'quote', 'error', $back ) . '#request-a-quote' );
		exit;
	}

	foreach ( $values as $key => $value ) {
		update_post_meta( $post_id, '_scc_e_' . $key, $value );
	}
	update_post_meta( $post_id, '_scc_e_page', esc_url_raw( $back ) );

	if ( $suspect ) {
		update_post_meta( $post_id, '_scc_e_spam', sanitize_text_field( $screen['reason'] ) );
		scc_count_blocked();
	} else {
		scc_mail_quote( $values, $back );
	}

	wp_safe_redirect( add_query_arg( 'quote', 'sent', $back ) . '#request-a-quote' );
	exit;
}
add_action( 'init', 'scc_handle_quote_form' );

/**
 * A per-visitor key so a failed submission repopulates only for the person who
 * made it. No cookies, no personal data - just the session-ish fingerprint.
 */
function scc_quote_visitor_key() {
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return md5( $agent . '|' . $ip );
}

/**
 * Sends the enquiry on.
 *
 * The From address is on this domain on purpose. Using the visitor's own
 * address as the sender is what gets these mails binned by SPF - their reply
 * address goes in Reply-To instead, so hitting reply still works.
 */
function scc_mail_quote( $values, $source ) {
	$to = sanitize_email( scc_detail( 'service_email' ) );
	if ( ! is_email( $to ) ) {
		return false;
	}

	$domain = wp_parse_url( home_url(), PHP_URL_HOST );
	$domain = preg_replace( '/^www\./', '', (string) $domain );

	$lines = array(
		__( 'New quote request from the website.', 'salado-custom-carts' ),
		'',
	);
	foreach ( scc_enquiry_fields() as $key => $field ) {
		$lines[] = $field['label'] . ': ' . ( '' !== $values[ $key ] ? $values[ $key ] : __( 'not given', 'salado-custom-carts' ) );
	}
	$lines[] = '';
	$lines[] = __( 'Sent from', 'salado-custom-carts' ) . ': ' . $source;
	$lines[] = __( 'A copy is saved under Quote Requests in your dashboard.', 'salado-custom-carts' );

	$headers = array( 'From: ' . get_bloginfo( 'name' ) . ' <noreply@' . $domain . '>' );
	if ( '' !== $values['email'] && is_email( $values['email'] ) ) {
		$headers[] = 'Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>';
	}

	return wp_mail(
		$to,
		sprintf(
			/* translators: %s: sender name */
			__( 'Quote request - %s', 'salado-custom-carts' ),
			$values['name']
		),
		implode( "\r\n", $lines ),
		$headers
	);
}

/**
 * [scc_quote_form]
 */
function scc_quote_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'title' => __( 'Request a Quote', 'salado-custom-carts' ),
	), $atts, 'scc_quote_form' );

	$state = isset( $_GET['quote'] ) ? sanitize_key( wp_unslash( $_GET['quote'] ) ) : '';
	$key   = 'scc_quote_old_' . scc_quote_visitor_key();
	$old   = array();

	/*
	 * Only refill the form when we have just bounced this visitor back with an
	 * error, and clear the stored copy straight away. The visitor key is derived
	 * from IP and user agent, so behind a shared connection two people can land
	 * on the same key - refilling on an ordinary visit would show one person
	 * what the other had typed.
	 */
	if ( in_array( $state, array( 'error', 'captcha' ), true ) ) {
		$stored = get_transient( $key );
		$old    = is_array( $stored ) ? $stored : array();
	}

	if ( in_array( $state, array( 'error', 'captcha', 'sent' ), true ) ) {
		delete_transient( $key );
	}

	ob_start();
	?>
	<div class="scc-quote" id="request-a-quote">
		<?php if ( $atts['title'] ) : ?>
			<h2 class="scc-quote__title"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( 'sent' === $state ) : ?>
			<p class="scc-quote__msg scc-quote__msg--ok">
				<?php echo scc_icon( 'check', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php esc_html_e( 'Thanks - we have got it. We will call you back shortly.', 'salado-custom-carts' ); ?>
			</p>
		<?php elseif ( 'error' === $state ) : ?>
			<p class="scc-quote__msg scc-quote__msg--bad">
				<?php esc_html_e( 'Something was missing. Please check the fields marked below and send it again.', 'salado-custom-carts' ); ?>
			</p>
		<?php elseif ( 'captcha' === $state ) : ?>
			<p class="scc-quote__msg scc-quote__msg--bad">
				<?php esc_html_e( 'Please tick the "I am human" box below and send it again. Your details are still here.', 'salado-custom-carts' ); ?>
			</p>
		<?php endif; ?>

		<form class="scc-quote__form" method="post" action="">
			<?php wp_nonce_field( 'scc_quote', 'scc_quote_nonce' ); ?>
			<input type="hidden" name="scc_return" value="<?php echo esc_url( get_permalink() ); ?>" />

			<?php foreach ( scc_enquiry_fields() as $key => $field ) : ?>
				<?php
				$id      = 'scc-f-' . $key;
				$value   = isset( $old[ $key ] ) ? $old[ $key ] : '';
				$missing = 'error' === $state && ! empty( $field['required'] ) && '' === trim( (string) $value );
				$classes = 'scc-quote__field';
				if ( 'textarea' === $field['type'] ) {
					$classes .= ' scc-quote__field--wide';
				}
				if ( $missing ) {
					$classes .= ' is-missing';
				}
				?>
				<div class="<?php echo esc_attr( $classes ); ?>">
					<label for="<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $field['label'] ); ?>
						<?php if ( empty( $field['required'] ) ) : ?>
							<span class="scc-quote__opt"><?php esc_html_e( 'optional', 'salado-custom-carts' ); ?></span>
						<?php endif; ?>
					</label>

					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea id="<?php echo esc_attr( $id ); ?>" name="scc_<?php echo esc_attr( $key ); ?>" rows="5"
							<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>
					<?php else : ?>
						<input type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $id ); ?>"
							name="scc_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"
							<?php echo isset( $field['autocomplete'] ) ? 'autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : ''; ?>
							<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?> />
					<?php endif; ?>

					<?php if ( ! empty( $field['hint'] ) ) : ?>
						<p class="scc-quote__hint"><?php echo esc_html( $field['hint'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<?php scc_antispam_fields(); ?>

			<div class="scc-quote__actions">
				<button type="submit" name="scc_quote_submit" value="1" class="scc-btn scc-btn--primary">
					<?php esc_html_e( 'Send request', 'salado-custom-carts' ); ?>
				</button>
				<p class="scc-quote__alt">
					<?php esc_html_e( 'Or just call', 'salado-custom-carts' ); ?>
					<a href="tel:<?php echo esc_attr( scc_phone_link() ); ?>"><?php echo esc_html( scc_detail( 'phone' ) ); ?></a>
				</p>
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'scc_quote_form', 'scc_quote_form_shortcode' );

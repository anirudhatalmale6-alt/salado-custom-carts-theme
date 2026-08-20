<?php
/**
 * Spam protection for the Request a Quote form.
 *
 * Andrew reported a lot of spam coming through, 19 Aug 2026. The form already
 * had a WordPress nonce and one honeypot, which stops the crude stuff, so what
 * is getting through is either a bot that renders the page properly or a paid
 * human form-filler. Neither is stopped by a hidden field.
 *
 * Four layers, cheapest first, so most junk is thrown out before it costs
 * anything:
 *
 *   1. Honeypots      - two of them, one with a believable name.
 *   2. Timing         - a signed timestamp. Bots post instantly.
 *   3. Rate limit     - one address cannot send all afternoon.
 *   4. Turnstile      - a real CAPTCHA, once Andrew adds his keys.
 *
 * Plus content scoring, which flags rather than deletes.
 *
 * NOTHING IS EVER SILENTLY DELETED except a honeypot hit, which no human can
 * trigger. Everything else that looks like spam is still saved, marked, and
 * parked as a draft under Quote Requests - it just does not get emailed. A
 * false positive costs Andrew a click, not a customer.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Turnstile is only switched on when BOTH keys are filled in, so an
 * empty settings screen leaves the form working exactly as it did before.
 */
function scc_turnstile_keys() {
	return array(
		'site'   => trim( (string) get_option( 'scc_turnstile_site', '' ) ),
		'secret' => trim( (string) get_option( 'scc_turnstile_secret', '' ) ),
	);
}

function scc_turnstile_active() {
	$keys = scc_turnstile_keys();
	return '' !== $keys['site'] && '' !== $keys['secret'];
}

/**
 * Asks Cloudflare whether the token is genuine.
 *
 * Deliberately fails OPEN. If Cloudflare is unreachable - their outage, a host
 * firewall, a DNS blip - the enquiry is let through and marked, rather than
 * Andrew silently losing every lead for the length of someone else's incident.
 * A quiet form is far more expensive to him than a few junk entries.
 */
function scc_turnstile_verify( $token, $ip ) {
	if ( '' === $token ) {
		return array( 'ok' => false, 'note' => 'turnstile-missing' );
	}

	$keys = scc_turnstile_keys();

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 8,
			'body'    => array(
				'secret'   => $keys['secret'],
				'response' => $token,
				'remoteip' => $ip,
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return array( 'ok' => true, 'note' => 'turnstile-unreachable' );
	}

	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $body ) ) {
		return array( 'ok' => true, 'note' => 'turnstile-unreadable' );
	}

	if ( ! empty( $body['success'] ) ) {
		return array( 'ok' => true, 'note' => '' );
	}

	$codes = isset( $body['error-codes'] ) && is_array( $body['error-codes'] ) ? implode( ',', $body['error-codes'] ) : 'failed';

	return array( 'ok' => false, 'note' => 'turnstile-' . sanitize_text_field( $codes ) );
}

/**
 * The widget itself, plus the two honeypots and the signed timestamp.
 */
function scc_antispam_fields() {
	$stamp = time();
	?>
	<?php /* Hidden from people, irresistible to bots. Two of them: the second has a name worth faking. */ ?>
	<div class="scc-quote__hp" aria-hidden="true">
		<label for="scc-website"><?php esc_html_e( 'Leave this empty', 'salado-custom-carts' ); ?></label>
		<input type="text" id="scc-website" name="scc_website" tabindex="-1" autocomplete="off" />
		<label for="scc-confirm-email"><?php esc_html_e( 'Confirm your email', 'salado-custom-carts' ); ?></label>
		<input type="text" id="scc-confirm-email" name="scc_confirm_email" tabindex="-1" autocomplete="off" />
	</div>

	<?php /* Signed, so the clock cannot simply be edited in the page source. */ ?>
	<input type="hidden" name="scc_t" value="<?php echo esc_attr( $stamp . '|' . wp_hash( $stamp . 'scc_quote' ) ); ?>" />

	<?php if ( scc_turnstile_active() ) : ?>
		<?php $keys = scc_turnstile_keys(); ?>
		<div class="scc-quote__captcha">
			<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $keys['site'] ); ?>" data-theme="light"></div>
		</div>
		<?php
		wp_enqueue_script(
			'scc-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js',
			array(),
			null, // Cloudflare version their own endpoint; a ?ver= string would break caching for them.
			true
		);
		?>
	<?php endif; ?>
	<?php
}

/**
 * Adds async/defer to the Turnstile tag only. WordPress has no clean way to say
 * "this one script is async" without touching the tag directly.
 */
function scc_turnstile_script_tag( $tag, $handle ) {
	if ( 'scc-turnstile' === $handle ) {
		return str_replace( ' src=', ' async defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'scc_turnstile_script_tag', 10, 2 );

/**
 * The visitor's address, taking the proxy header only when the host actually
 * sets one. Trusting REMOTE_ADDR blindly on shared hosting reads the load
 * balancer instead of the visitor, which would rate-limit the whole world into
 * one bucket.
 */
function scc_visitor_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $header ) {
		if ( empty( $_SERVER[ $header ] ) ) {
			continue;
		}
		$raw = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
		$ip  = trim( explode( ',', $raw )[0] );
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}
	}
	return '';
}

/**
 * A definite bot: something no human being can trigger. These are dropped
 * outright and only counted.
 */
function scc_is_certain_bot() {
	return ! empty( $_POST['scc_website'] ) || ! empty( $_POST['scc_confirm_email'] );
}

/**
 * How long the form was on screen, in seconds. Returns null if the stamp is
 * missing or was tampered with.
 */
function scc_form_age() {
	if ( empty( $_POST['scc_t'] ) ) {
		return null;
	}

	$parts = explode( '|', sanitize_text_field( wp_unslash( $_POST['scc_t'] ) ) );
	if ( 2 !== count( $parts ) ) {
		return null;
	}

	list( $stamp, $hash ) = $parts;

	if ( ! hash_equals( wp_hash( $stamp . 'scc_quote' ), $hash ) ) {
		return null;
	}

	return time() - (int) $stamp;
}

/**
 * Counts submissions per address per hour.
 */
function scc_rate_limit_hit( $ip ) {
	if ( '' === $ip ) {
		return 0;
	}
	$key   = 'scc_rl_' . md5( $ip );
	$count = (int) get_transient( $key );
	$count++;
	set_transient( $key, $count, HOUR_IN_SECONDS );

	return $count;
}

/**
 * Reads the message the way a person would and returns the reasons it looks
 * like spam. Scored, not absolute - one weak signal is never enough on its own.
 *
 * Tuned for THIS business. Nobody asking about a golf cart repair needs to send
 * three links, and the give-away words below have never once appeared in a real
 * enquiry here.
 */
function scc_spam_reasons( $values ) {
	$reasons = array();
	$score   = 0;

	$message = (string) $values['message'];
	$name    = (string) $values['name'];
	$blob    = strtolower( $name . ' ' . $message );

	$links = preg_match_all( '#https?://|www\.|\[url#i', $message );
	if ( $links >= 2 ) {
		$score    += 3;
		$reasons[] = sprintf( '%d links', $links );
	} elseif ( 1 === $links ) {
		$score    += 1;
		$reasons[] = 'a link';
	}

	// A name is a name. A link in the name field is never anything else.
	if ( preg_match( '#https?://|www\.#i', $name ) ) {
		$score    += 4;
		$reasons[] = 'link in the name';
	}

	$giveaways = array(
		'seo', 'backlink', 'guest post', 'rank your', 'search engine ranking',
		'crypto', 'bitcoin', 'forex', 'casino', 'viagra', 'cialis', 'escort',
		'payday', 'loan offer', 'increase your traffic', 'web design services',
		'digital marketing agency', 'binary option', 'telegram', 'whatsapp me',
	);
	$hits = array();
	foreach ( $giveaways as $word ) {
		if ( false !== strpos( $blob, $word ) ) {
			$hits[] = $word;
		}
	}
	if ( $hits ) {
		$score    += 2 * count( $hits );
		$reasons[] = 'wording: ' . implode( ', ', array_slice( $hits, 0, 3 ) );
	}

	// Cyrillic or CJK in a Texas golf cart enquiry.
	if ( preg_match( '/[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}]/u', $message ) ) {
		$score    += 3;
		$reasons[] = 'non-Latin script';
	}

	if ( strlen( trim( $message ) ) < 12 ) {
		$score    += 1;
		$reasons[] = 'almost no message';
	}

	return $score >= 3 ? $reasons : array();
}

/**
 * Everything above, in one call. Returns:
 *   drop  - a certain bot, throw it away
 *   block - show the visitor an error and let them try again (failed CAPTCHA)
 *   flag  - save it, mark it, do not email
 *   ''    - a real enquiry
 */
function scc_screen_submission( $values ) {
	if ( scc_is_certain_bot() ) {
		return array( 'action' => 'drop', 'reason' => 'honeypot' );
	}

	$age = scc_form_age();
	if ( null === $age ) {
		return array( 'action' => 'flag', 'reason' => 'form timestamp missing' );
	}
	if ( $age < 3 ) {
		return array( 'action' => 'drop', 'reason' => 'submitted in ' . $age . 's' );
	}
	if ( $age > DAY_IN_SECONDS ) {
		return array( 'action' => 'flag', 'reason' => 'page was open over a day' );
	}

	$ip = scc_visitor_ip();

	if ( scc_turnstile_active() ) {
		$token  = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
		$result = scc_turnstile_verify( $token, $ip );

		if ( ! $result['ok'] ) {
			// A human can genuinely fail this, so tell them rather than
			// pretending it sent.
			return array( 'action' => 'block', 'reason' => $result['note'] );
		}
		if ( '' !== $result['note'] ) {
			// Let through during a Cloudflare wobble, but say so on the record.
			return array( 'action' => 'flag', 'reason' => $result['note'] );
		}
	}

	if ( scc_rate_limit_hit( $ip ) > 5 ) {
		return array( 'action' => 'flag', 'reason' => 'more than 5 from this address in an hour' );
	}

	$content = scc_spam_reasons( $values );
	if ( $content ) {
		return array( 'action' => 'flag', 'reason' => implode( '; ', $content ) );
	}

	return array( 'action' => '', 'reason' => '' );
}

/**
 * A running total, so Andrew can see the thing is earning its keep instead of
 * taking my word for it.
 */
function scc_count_blocked() {
	update_option( 'scc_spam_blocked', (int) get_option( 'scc_spam_blocked', 0 ) + 1, false );
}

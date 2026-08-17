<?php
/**
 * Homepage sections, registered as block patterns.
 *
 * Each one is built from core WordPress blocks carrying a theme class, so the
 * client edits every word and image in the normal editor. Insert them from
 * the block inserter under "Salado Custom Carts".
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function scc_img( $file ) {
	return get_template_directory_uri() . '/assets/img/' . $file;
}

/**
 * The homepage content, as block markup. Also used by the setup routine to
 * populate the front page on a fresh install.
 */
function scc_home_blocks() {
	$hero    = scc_img( 'hero-cart.jpg' );
	$pickup  = scc_img( 'pickup-trailer.jpg' );
	$lithium = scc_img( 'lithium-pack.jpg' );
	$phone   = scc_detail( 'phone' );
	$tel     = scc_phone_link();
	$service = scc_quote_link();

	$blocks = array();

	// ---------------------------------------------------------------- hero
	$blocks[] = '<!-- wp:cover {"url":"' . esc_url( $hero ) . '","dimRatio":80,"overlayColor":"scc-ink","isDark":true,"align":"full","className":"scc-hero-block"} -->
<div class="wp-block-cover alignfull scc-hero-block"><span aria-hidden="true" class="wp-block-cover__background has-scc-ink-background-color has-background-dim-80 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( $hero ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
<!-- wp:paragraph {"className":"scc-eyebrow"} -->
<p class="scc-eyebrow">Salado, Texas &mdash; your neighbor, not a dealership</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1>Lithium upgrades.<span class="scc-accent">Carts built your way.</span></h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We turn tired golf carts into carts worth showing off &mdash; lithium battery conversions, lift kits, wheels, seating and custom wraps. And we pick your cart up and bring it back, free.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"scc-red","textColor":"white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-scc-red-background-color has-text-color has-background wp-element-button" href="tel:' . esc_attr( $tel ) . '">Call ' . esc_html( $phone ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","textColor":"white"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-text-color wp-element-button" href="' . esc_url( $service ) . '">Request a Quote</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div></div>
<!-- /wp:cover -->';

	// ----------------------------------------------------------- trust bar
	$trust = array(
		array( 'Salado local', 'Based here, not two towns over. We know the hills you drive.' ),
		array( 'Free pickup &amp; delivery', 'We collect your cart for upgrades or repairs and bring it back.' ),
		array( '8 year battery warranty', 'LiFePO4 packs are warrantied over 8 years, not a couple of seasons.' ),
		array( 'Built, not just sold', 'Lift kits, wheels, seats, wraps and motor work done in house.' ),
	);

	$cols = '';
	foreach ( $trust as $item ) {
		$cols .= '<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3>' . $item[0] . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $item[1] . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->';
	}

	$blocks[] = '<!-- wp:columns {"align":"full","className":"scc-trust-block"} -->
<div class="wp-block-columns alignfull scc-trust-block">' . $cols . '</div>
<!-- /wp:columns -->';

	// ------------------------------------------------------------- lithium
	$blocks[] = '<!-- wp:group {"align":"full","backgroundColor":"scc-ink","className":"scc-block-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull scc-block-section has-scc-ink-background-color has-background">
<!-- wp:media-text {"mediaId":0,"mediaLink":"' . esc_url( $lithium ) . '","mediaType":"image","mediaWidth":48,"className":"scc-mediatext"} -->
<div class="wp-block-media-text is-stacked-on-mobile scc-mediatext" style="grid-template-columns:48% auto"><figure class="wp-block-media-text__media"><img src="' . esc_url( $lithium ) . '" alt="Lithium battery pack fitted to a golf cart"/></figure><div class="wp-block-media-text__content">
<!-- wp:paragraph {"className":"scc-eyebrow"} -->
<p class="scc-eyebrow">Our specialty</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Lithium battery upgrades</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Tired of topping up batteries with distilled water and scrubbing orange stains off the garage floor? A lithium conversion wakes the whole cart up.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><!-- wp:list-item --><li>LiFePO4 cells with an intelligent Battery Management System</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Up to 50&ndash;60 mile range on a single charge</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Full power from 100% down to 0% &mdash; no fade as it drains</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Up to 25% more speed, and a third of the weight of lead-acid</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Maintenance free, 8 year warranty</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Lead-acid batteries need replacing every couple of years at over $1,000 a time. A lithium pack is warrantied past 8 years &mdash; that is thousands of dollars across the life of the cart.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"scc-red","textColor":"white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-scc-red-background-color has-text-color has-background wp-element-button" href="/lithium-battery-upgrades/">See what is involved</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div></div>
<!-- /wp:media-text -->
</div>
<!-- /wp:group -->';

	// --------------------------------------------------------- pickup band
	$blocks[] = '<!-- wp:group {"align":"full","className":"scc-block-section scc-pickup-block","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull scc-block-section scc-pickup-block">
<!-- wp:media-text {"mediaLink":"' . esc_url( $pickup ) . '","mediaType":"image","mediaWidth":52,"mediaPosition":"right","className":"scc-mediatext"} -->
<div class="wp-block-media-text has-media-on-the-right is-stacked-on-mobile scc-mediatext" style="grid-template-columns:auto 52%"><div class="wp-block-media-text__content">
<!-- wp:paragraph {"className":"scc-eyebrow"} -->
<p class="scc-eyebrow">No trailer? No problem</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Free cart pickup and delivery</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>You do not need to borrow a truck or hire a trailer to get your cart upgraded. We come to you, load it up, do the work, and drop it back in your driveway.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><!-- wp:list-item --><li>Free local pickup and delivery around Salado</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>Battery upgrades, repairs and servicing</li><!-- /wp:list-item -->
<!-- wp:list-item --><li>One call and we handle the rest</li><!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"scc-red","textColor":"white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-scc-red-background-color has-text-color has-background wp-element-button" href="tel:' . esc_attr( $tel ) . '">Book a pickup</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div><figure class="wp-block-media-text__media"><img src="' . esc_url( $pickup ) . '" alt="Salado Custom Carts truck and branded trailer"/></figure></div>
<!-- /wp:media-text -->
</div>
<!-- /wp:group -->';

	// ------------------------------------------------------ carts for sale
	$blocks[] = '<!-- wp:group {"align":"full","backgroundColor":"scc-ink-2","className":"scc-block-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull scc-block-section has-scc-ink-2-background-color has-background">
<!-- wp:paragraph {"className":"scc-eyebrow"} -->
<p class="scc-eyebrow">Ready to drive</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Carts for sale right now</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every cart here is one we have built or gone through ourselves. Sold in person only &mdash; call and we will walk you round it.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[scc_carts count="3" status="available"]
<!-- /wp:shortcode -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-outline","textColor":"white"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-text-color wp-element-button" href="/carts-for-sale/">See every cart</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->';

	// ----------------------------------------------------------------- CTA
	$blocks[] = '<!-- wp:group {"align":"full","backgroundColor":"scc-ink","className":"scc-block-section scc-cta-block","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull scc-block-section scc-cta-block has-scc-ink-background-color has-background">
<!-- wp:heading -->
<h2>Tell us what you want the cart to do</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Whether it is a repair, a service, more range, more speed, a lift and bigger tires, or a full custom build from a used cart &mdash; give us a call and we will talk it through honestly.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"scc-red","textColor":"white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-scc-red-background-color has-text-color has-background wp-element-button" href="tel:' . esc_attr( $tel ) . '">Call ' . esc_html( $phone ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","textColor":"white"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-text-color wp-element-button" href="' . esc_url( $service ) . '">Request a Quote</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->';

	return implode( "\n\n", $blocks );
}

function scc_register_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category( 'salado', array(
		'label' => __( 'Salado Custom Carts', 'salado-custom-carts' ),
	) );

	register_block_pattern( 'salado/homepage', array(
		'title'      => __( 'Full homepage', 'salado-custom-carts' ),
		'categories' => array( 'salado' ),
		'content'    => scc_home_blocks(),
	) );
}
add_action( 'init', 'scc_register_patterns' );

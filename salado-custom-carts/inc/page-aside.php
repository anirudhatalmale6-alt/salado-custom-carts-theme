<?php
/**
 * The sidebar shown beside page content.
 *
 * Ordinary pages are mostly text, and a single reading column left the wide
 * screens looking half empty next to the homepage. This puts the things a
 * visitor actually needs - the phone number, a quote link, and the rest of the
 * services - alongside the copy instead.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service links for the sidebar, taken from the primary menu so it follows
 * whatever the client renames things to. Falls back to the theme's own list.
 * The page being viewed is left out - no point linking to where you already are.
 */
function scc_aside_links() {
	$items    = array();
	$current  = get_the_ID();
	$location = get_nav_menu_locations();

	if ( ! empty( $location['primary'] ) ) {
		$menu_items = wp_get_nav_menu_items( $location['primary'] );

		if ( $menu_items ) {
			foreach ( $menu_items as $item ) {
				// Top level only, and skip Home - the logo already goes there.
				if ( (int) $item->menu_item_parent !== 0 ) {
					continue;
				}
				if ( 'post_type' === $item->type && (int) $item->object_id === (int) $current ) {
					continue;
				}
				// Custom links to the same page slip past the check above, so
				// compare the paths as well.
				$here = wp_parse_url( (string) get_permalink( $current ), PHP_URL_PATH );
				if ( $here && trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' ) === trim( $here, '/' ) ) {
					continue;
				}
				// Skip Home however it is linked - as a page, as a custom link,
				// or with a stale host in the URL. Compare the path only.
				$path = trim( (string) wp_parse_url( $item->url, PHP_URL_PATH ), '/' );
				if ( '' === $path ) {
					continue;
				}
				$items[ $item->url ] = $item->title;
			}
		}
	}

	if ( ! $items ) {
		$items = array(
			home_url( '/carts-for-sale/' )            => __( 'Carts for Sale', 'salado-custom-carts' ),
			home_url( '/lithium-battery-upgrades/' )  => __( 'Lithium Batteries', 'salado-custom-carts' ),
			home_url( '/appearance-upgrades/' )       => __( 'Appearance', 'salado-custom-carts' ),
			home_url( '/performance-upgrades/' )      => __( 'Performance', 'salado-custom-carts' ),
			home_url( '/contact/' )                   => __( 'Contact', 'salado-custom-carts' ),
		);
	}

	return $items;
}

function scc_page_aside() {
	$links = scc_aside_links();
	?>
	<aside class="scc-aside" aria-label="<?php esc_attr_e( 'More information', 'salado-custom-carts' ); ?>">
		<div class="scc-aside__inner">

			<div class="scc-aside__card scc-aside__card--call">
				<p class="scc-aside__label"><?php esc_html_e( 'Talk to a person', 'salado-custom-carts' ); ?></p>
				<a class="scc-aside__phone" href="tel:<?php echo esc_attr( scc_phone_link() ); ?>">
					<?php echo scc_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( scc_detail( 'phone' ) ); ?>
				</a>
				<p class="scc-aside__hours">
					<?php echo scc_icon( 'clock', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( scc_detail( 'hours' ) ); ?>
				</p>
				<a class="scc-btn scc-btn--primary scc-aside__btn" href="<?php echo esc_url( scc_quote_link() ); ?>">
					<?php esc_html_e( 'Request a Quote', 'salado-custom-carts' ); ?>
				</a>
			</div>

			<div class="scc-aside__card scc-aside__card--pickup">
				<p class="scc-aside__pickup">
					<?php echo scc_icon( 'truck', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php echo esc_html( scc_detail( 'pickup_note' ) ); ?></span>
				</p>
				<p class="scc-aside__note">
					<?php esc_html_e( 'We collect your cart, do the work, and bring it back. No trailer needed.', 'salado-custom-carts' ); ?>
				</p>
			</div>

			<?php if ( $links ) : ?>
				<div class="scc-aside__card">
					<p class="scc-aside__label"><?php esc_html_e( 'What we do', 'salado-custom-carts' ); ?></p>
					<ul class="scc-aside__links">
						<?php foreach ( $links as $url => $label ) : ?>
							<li>
								<a href="<?php echo esc_url( $url ); ?>">
									<span><?php echo esc_html( $label ); ?></span>
									<?php echo scc_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="scc-aside__card scc-aside__card--where">
				<p class="scc-aside__label"><?php esc_html_e( 'Where we are', 'salado-custom-carts' ); ?></p>
				<p class="scc-aside__where">
					<?php echo scc_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( scc_detail( 'town' ) ); ?>
				</p>
				<a class="scc-aside__mail" href="mailto:<?php echo esc_attr( scc_detail( 'email' ) ); ?>">
					<?php echo esc_html( scc_detail( 'email' ) ); ?>
				</a>
			</div>

		</div>
	</aside>
	<?php
}

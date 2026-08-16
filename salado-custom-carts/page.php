<?php
/**
 * Standard page.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="scc-pagehead">
		<div class="scc-container">
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="scc-content">
		<div class="scc-container">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();

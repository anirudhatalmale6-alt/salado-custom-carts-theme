<?php
/**
 * Standard page - content column plus sidebar.
 *
 * A page can opt out of the sidebar from the editor (Page Attributes >
 * Template > Full width), which is what the homepage-style pages want.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$with_aside = ! scc_page_is_full_width( get_the_ID() );
	?>
	<div class="scc-pagehead">
		<div class="scc-container">
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="scc-content<?php echo $with_aside ? ' scc-content--aside' : ''; ?>">
		<div class="scc-container<?php echo $with_aside ? ' scc-page' : ''; ?>">
			<div class="scc-page__main">
				<?php the_content(); ?>
			</div>

			<?php
			if ( $with_aside ) {
				scc_page_aside();
			}
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();

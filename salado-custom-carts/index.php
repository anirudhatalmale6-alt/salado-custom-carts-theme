<?php
/**
 * Fallback template.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="scc-pagehead">
	<div class="scc-container">
		<h1><?php echo esc_html( is_home() ? get_bloginfo( 'name' ) : get_the_archive_title() ); ?></h1>
	</div>
</div>

<div class="scc-content">
	<div class="scc-container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
				<?php
			endwhile;
			the_posts_pagination();
		else :
			esc_html_e( 'Nothing found.', 'salado-custom-carts' );
		endif;
		?>
	</div>
</div>
<?php
get_footer();

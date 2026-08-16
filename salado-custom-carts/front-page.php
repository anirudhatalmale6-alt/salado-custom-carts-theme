<?php
/**
 * Front page. The homepage is ordinary block content, so everything on it is
 * edited in Pages > Home like any other page.
 *
 * @package salado-custom-carts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();

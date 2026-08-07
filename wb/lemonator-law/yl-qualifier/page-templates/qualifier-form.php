<?php
/**
 * Page template: full-page qualifier form.
 *
 * Registered via YL_Qualifier_Form — appears in Page → Template as "Qualifier Form".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main yl-qualifier-page">
	<?php
	while ( have_posts() ) {
		the_post();

		if ( get_the_title() ) {
			echo '<div class="yl-qualifier-page__intro">';
			the_title( '<h1 class="entry-title">', '</h1>' );
			if ( has_excerpt() ) {
				echo '<div class="entry-excerpt">';
				the_excerpt();
				echo '</div>';
			}
			echo '</div>';
		}

		echo yl_qualifier_render();
	}
	?>
</main>

<?php
get_footer();

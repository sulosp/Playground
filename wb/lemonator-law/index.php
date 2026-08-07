<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="lemonator-page-content">
	<div class="lemonator-container">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				the_content();
			}
		}
		?>
	</div>
</section>

<?php
get_footer();

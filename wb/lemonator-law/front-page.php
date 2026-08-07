<?php
/**
 * Homepage template — all sections match lemonator.solution21-websites.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/hero' );
get_template_part( 'template-parts/vehicle-classes' );
get_template_part( 'template-parts/claim-process' );
get_template_part( 'template-parts/attorney' );
get_template_part( 'template-parts/locations' );
get_template_part( 'template-parts/contact' );

get_footer();

<?php
/**
 * Lemonator Law theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LEMONATOR_VERSION', '1.0.0' );
define( 'LEMONATOR_DIR', get_template_directory() );
define( 'LEMONATOR_URI', get_template_directory_uri() );

require LEMONATOR_DIR . '/yl-qualifier/yl-qualifier.php';

/**
 * Media uploaded on the live site (same paths when theme is deployed there).
 *
 * @param string $file Filename under uploads/2026/08/.
 */
function lemonator_asset( $file ) {
	return content_url( '/uploads/2026/08/' . ltrim( $file, '/' ) );
}

function lemonator_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'lemonator-law' ),
		)
	);
}
add_action( 'after_setup_theme', 'lemonator_setup' );

function lemonator_enqueue_assets() {
	wp_enqueue_style(
		'lemonator-fonts',
		'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'yl-qualifier', LEMONATOR_URI . '/yl-qualifier/assets/qualifier.css', array(), YL_QUALIFIER_VERSION );
	wp_enqueue_style( 'lemonator-theme', LEMONATOR_URI . '/assets/css/theme.css', array( 'yl-qualifier' ), LEMONATOR_VERSION );

	wp_enqueue_script( 'yl-qualifier', LEMONATOR_URI . '/yl-qualifier/assets/qualifier.js', array(), YL_QUALIFIER_VERSION, true );
	wp_enqueue_script( 'lemonator-theme', LEMONATOR_URI . '/assets/js/theme.js', array(), LEMONATOR_VERSION, true );

	wp_localize_script(
		'yl-qualifier',
		'ylQualifier',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php?action=yl_qualifier_submit' ),
			'restUrl' => rest_url( 'yl-qualifier/v1/submit' ),
			'postUrl' => admin_url( 'admin-post.php' ),
			'nonce'   => wp_create_nonce( 'yl_qualifier_submit' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'lemonator_enqueue_assets' );

function lemonator_fallback_menu() {
	$links = array(
		array( 'href' => '#vehicles', 'label' => 'Vehicles Covered' ),
		array( 'href' => '#process', 'label' => 'Process' ),
		array( 'href' => '#service-area', 'label' => 'Service Area' ),
		array( 'href' => '#about', 'label' => 'About' ),
	);

	echo '<ul class="lemonator-nav__list">';
	foreach ( $links as $link ) {
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( $link['href'] ),
			esc_html( $link['label'] )
		);
	}
	echo '</ul>';
}

function lemonator_handle_contact_form() {
	if ( ! isset( $_POST['lemonator_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lemonator_contact_nonce'] ) ), 'lemonator_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$first = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$last  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$msg   = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( empty( $first ) || empty( $last ) || ! is_email( $email ) || empty( $msg ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$to      = 'sulosp1992@gmail.com';
	$subject = 'Website contact — ' . $first . ' ' . $last;
	$body    = "Name: {$first} {$last}\nEmail: {$email}\n\n{$msg}";
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email );

	wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'contact', 'sent', wp_get_referer() ?: home_url( '/#contact' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_lemonator_contact', 'lemonator_handle_contact_form' );
add_action( 'admin_post_lemonator_contact', 'lemonator_handle_contact_form' );

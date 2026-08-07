<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#F6F2E8">
	<link rel="icon" href="<?php echo esc_url( lemonator_asset( 'lemonator-law-favicon.svg' ) ); ?>" type="image/svg+xml">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="lemonator-skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'lemonator-law' ); ?></a>

<header class="lemonator-header">
	<div class="lemonator-header__inner">
		<a class="lemonator-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( lemonator_asset( 'Lemonator-Law.svg' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="144" height="14">
		</a>

		<nav class="lemonator-nav" aria-label="<?php esc_attr_e( 'Primary', 'lemonator-law' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'lemonator-nav__list',
						'fallback_cb'    => false,
					)
				);
			} else {
				lemonator_fallback_menu();
			}
			?>
		</nav>

		<div class="lemonator-header__actions">
			<a class="lemonator-header__phone" href="tel:+18665053666">Call (866) 505-3666</a>
			<a class="lemonator-btn lemonator-btn--primary lemonator-btn--sm" href="#qualifier">Free Case Review</a>
			<button class="lemonator-nav-toggle" type="button" aria-expanded="false" aria-controls="lemonator-mobile-nav" aria-label="<?php esc_attr_e( 'Toggle menu', 'lemonator-law' ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>

	<div class="lemonator-mobile-nav" id="lemonator-mobile-nav" hidden>
		<?php lemonator_fallback_menu(); ?>
		<a class="lemonator-header__phone" href="tel:+18665053666">Call (866) 505-3666</a>
		<a class="lemonator-btn lemonator-btn--primary" href="#qualifier">Free Case Review</a>
	</div>
</header>

<main id="main-content" class="lemonator-main">

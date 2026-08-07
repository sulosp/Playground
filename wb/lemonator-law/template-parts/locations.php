<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$locations = array(
	array( 'city' => 'Santa Monica', 'zip' => '90404' ),
	array( 'city' => 'West Hills', 'zip' => '91307' ),
	array( 'city' => 'Commerce', 'zip' => '90022' ),
);
?>
<section class="lemonator-section lemonator-locations" id="service-area">
	<div class="lemonator-container lemonator-locations__grid">
		<div class="lemonator-locations__map" aria-hidden="true">
			<div class="lemonator-locations__map-placeholder">
				<span>Southern California</span>
			</div>
		</div>

		<div class="lemonator-locations__content">
			<div class="lemonator-eyebrow lemonator-eyebrow--gold">
				<span class="lemonator-eyebrow__line" aria-hidden="true"></span>
				<p class="lemonator-eyebrow__text">Where We Practice</p>
			</div>
			<h2 class="lemonator-section__title">Serving Southern California vehicle owners</h2>

			<div class="lemonator-locations__cards">
				<?php foreach ( $locations as $location ) : ?>
					<div class="lemonator-location-card">
						<h3><?php echo esc_html( $location['city'] ); ?></h3>
						<p><?php echo esc_html( $location['zip'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

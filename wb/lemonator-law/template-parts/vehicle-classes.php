<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vehicles = array(
	array(
		'code'  => 'CLASS · PC',
		'title' => 'Cars',
		'image' => '506ac43fe0e918cb5359921bc7e0ba05bec72d4e.jpg',
	),
	array(
		'code'  => 'CLASS · RV',
		'title' => 'RVs',
		'image' => 'b1827268851a3f2a24ebda3c728453e55eed25b4.jpg',
	),
	array(
		'code'  => 'CLASS · MC',
		'title' => 'Motorcycles',
		'image' => '0a5a865be73ff28edc1287f009b49f9852b648ee.jpg',
	),
	array(
		'code'  => 'CLASS · VCL',
		'title' => 'Boats',
		'image' => '72dda301d45ee3aaa8b6e62754e7e81508031a23.jpg',
	),
	array(
		'code'  => 'CLASS · ACFT',
		'title' => 'Aircraft',
		'image' => '3528572dfc05d553d3607ed4a0495a0354dd1a6f.jpg',
	),
	array(
		'code'  => 'CLASS · SUV',
		'title' => 'Truck / SUVs',
		'image' => 'ab4ee332b8af2929fd3c899c7522f73cc092ba96.jpg',
	),
);
?>
<section class="lemonator-section lemonator-vehicles" id="vehicles">
	<div class="lemonator-container">
		<div class="lemonator-section__intro">
			<div class="lemonator-eyebrow lemonator-eyebrow--dark">
				<span class="lemonator-eyebrow__line" aria-hidden="true"></span>
				<p class="lemonator-eyebrow__text">California Lemon Law · Strong Beverly Act</p>
			</div>
			<h2 class="lemonator-section__title">If it has a warranty, it can be a lemon.</h2>
		</div>

		<div class="lemonator-vehicles__grid">
			<?php foreach ( $vehicles as $vehicle ) : ?>
				<a class="lemonator-vehicle-card" href="#qualifier" style="--card-bg: url('<?php echo esc_url( lemonator_asset( $vehicle['image'] ) ); ?>')">
					<span class="lemonator-vehicle-card__code"><?php echo esc_html( $vehicle['code'] ); ?></span>
					<span class="lemonator-vehicle-card__title"><?php echo esc_html( $vehicle['title'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

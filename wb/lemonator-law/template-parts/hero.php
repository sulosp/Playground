<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="lemonator-hero" id="qualifier" style="--hero-bg: url('<?php echo esc_url( lemonator_asset( '70060-1024x683.jpg' ) ); ?>')">
	<div class="lemonator-container lemonator-hero__grid">
		<div class="lemonator-hero__content">
			<div class="lemonator-eyebrow">
				<span class="lemonator-eyebrow__line" aria-hidden="true"></span>
				<p class="lemonator-eyebrow__text">California Lemon Law · Strong Beverly Act</p>
			</div>
			<h1 class="lemonator-hero__title">
				Your Vehicle Broke its Promise. <span class="lemonator-accent">State law backs</span> you up.
			</h1>
			<p class="lemonator-hero__lead">
				The Law Offices of Shahram Yermian represents owners and lessees of defective cars, RVs, motorcycles, boats, and aircraft in claims against the manufacturer pursuing a refund, a replacement, or a cash settlement.
			</p>
			<a class="lemonator-btn lemonator-btn--primary" href="#process">Learn More</a>
		</div>

		<div class="lemonator-hero__form">
			<?php include LEMONATOR_DIR . '/yl-qualifier/templates/form.php'; ?>
		</div>
	</div>
</section>

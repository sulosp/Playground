<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$steps = array(
	array(
		'image' => '01.svg',
		'title' => 'Free Case Review',
		'text'  => 'Send your repair orders and purchase paperwork. We assess your claim at no cost.',
	),
	array(
		'image' => '02.svg',
		'title' => 'We Build The Case',
		'text'  => 'A formal demand goes to the manufacturer, backed by your repair history.',
	),
	array(
		'image' => '03.svg',
		'title' => 'Negotiate Or Litigate',
		'text'  => 'Most claims settle. When one doesn\'t, we\'re prepared to take it to trial.',
	),
	array(
		'image' => '0.svg',
		'title' => 'You Get Paid',
		'text'  => 'A refund, replacement, or cash settlement — the manufacturer typically covers our fees.',
	),
);
?>
<section class="lemonator-section lemonator-process" id="process">
	<div class="lemonator-container">
		<div class="lemonator-section__intro lemonator-section__intro--center">
			<div class="lemonator-eyebrow lemonator-eyebrow--gold">
				<span class="lemonator-eyebrow__line" aria-hidden="true"></span>
				<p class="lemonator-eyebrow__text">How A Claim Moves</p>
			</div>
			<h2 class="lemonator-section__title">Four steps, start to resolution.</h2>
		</div>

		<div class="lemonator-process__list">
			<?php foreach ( $steps as $index => $step ) : ?>
				<article class="lemonator-process__item">
					<div class="lemonator-process__media">
						<img src="<?php echo esc_url( lemonator_asset( $step['image'] ) ); ?>" alt="<?php echo esc_attr( 'Step ' . ( $index + 1 ) ); ?>" loading="lazy">
					</div>
					<div class="lemonator-process__body">
						<h3><?php echo esc_html( $step['title'] ); ?></h3>
						<p><?php echo esc_html( $step['text'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

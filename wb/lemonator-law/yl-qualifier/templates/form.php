<?php
/**
 * YL Qualifier form — self-contained template.
 * Include from your theme: <?php include get_stylesheet_directory() . '/yl-qualifier/templates/form.php'; ?>
 *
 * Edit $yl_mail_to below. Requires yl-qualifier.php (or equivalent handler) for submit + email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$yl_mail_to = 'sulosp1992@gmail.com';

$yl_form = wp_parse_args(
	is_array( $yl_form ?? null ) ? $yl_form : array(),
	array(
		'mail_to'        => $yl_mail_to,
		'contact_email'  => $yl_mail_to,
		'contact_label'  => $yl_mail_to,
		'webhook_url'    => '',
		'send_email'     => true,
		'mail_from'      => '',
		'mail_from_name' => 'Lemon Law Qualifier',
		'ajax_url'       => '',
		'rest_url'       => '',
		'post_url'       => '',
		'front_url'      => '',
		'nonce'          => '',
		'rest_nonce'     => '',
	)
);

if ( empty( $yl_form['ajax_url'] ) ) {
	$yl_form['ajax_url'] = admin_url( 'admin-ajax.php?action=yl_qualifier_submit' );
}
if ( empty( $yl_form['rest_url'] ) && function_exists( 'rest_url' ) ) {
	$yl_form['rest_url'] = rest_url( 'yl-qualifier/v1/submit' );
}
if ( empty( $yl_form['post_url'] ) ) {
	$yl_form['post_url'] = admin_url( 'admin-post.php' );
}
if ( empty( $yl_form['front_url'] ) ) {
	$yl_form['front_url'] = add_query_arg( 'yl_qualifier_submit', '1', home_url( '/' ) );
}
if ( empty( $yl_form['nonce'] ) ) {
	$yl_form['nonce'] = wp_create_nonce( 'yl_qualifier_submit' );
}
if ( empty( $yl_form['rest_nonce'] ) ) {
	$yl_form['rest_nonce'] = wp_create_nonce( 'wp_rest' );
}
if ( ! is_email( $yl_form['contact_email'] ) ) {
	$yl_form['contact_email'] = $yl_mail_to;
}
if ( empty( $yl_form['contact_label'] ) ) {
	$yl_form['contact_label'] = $yl_form['contact_email'];
}
?>
<form
	class="yl-qualifier"
	id="yl-qualifier-form"
	method="post"
	novalidate
	data-ajax-url="<?php echo esc_url( $yl_form['ajax_url'] ); ?>"
	data-rest-url="<?php echo esc_url( $yl_form['rest_url'] ); ?>"
	data-post-url="<?php echo esc_url( $yl_form['post_url'] ); ?>"
	data-front-url="<?php echo esc_url( $yl_form['front_url'] ); ?>"
	data-nonce="<?php echo esc_attr( $yl_form['nonce'] ); ?>"
	data-rest-nonce="<?php echo esc_attr( $yl_form['rest_nonce'] ); ?>"
>
	<div class="yl-qualifier__hidden">
		<input type="hidden" name="mail_to" value="<?php echo esc_attr( $yl_form['mail_to'] ); ?>">
		<input type="hidden" name="action" value="yl_qualifier_submit">
		<input type="hidden" name="nonce" value="<?php echo esc_attr( $yl_form['nonce'] ); ?>">
		<input type="hidden" name="send_email" value="<?php echo $yl_form['send_email'] ? '1' : '0'; ?>">
		<?php if ( is_email( $yl_form['mail_from'] ) ) : ?>
		<input type="hidden" name="mail_from" value="<?php echo esc_attr( $yl_form['mail_from'] ); ?>">
		<?php endif; ?>
		<?php if ( ! empty( $yl_form['mail_from_name'] ) ) : ?>
		<input type="hidden" name="mail_from_name" value="<?php echo esc_attr( $yl_form['mail_from_name'] ); ?>">
		<?php endif; ?>
		<?php if ( ! empty( $yl_form['webhook_url'] ) ) : ?>
		<input type="hidden" name="webhook_url" value="<?php echo esc_url( $yl_form['webhook_url'] ); ?>">
		<?php endif; ?>
	</div>

	<header class="yl-qualifier__header">
		<span class="yl-qualifier__badge">30-Second Qualifier</span>
		<div class="yl-qualifier__title-block">
			<h2 class="yl-qualifier__title" data-form-title>See if your vehicle qualifies</h2>
			<p class="yl-qualifier__subtitle" data-form-subtitle>
				Answer a few questions. Our team reviews every submission personally.
				<span class="yl-qualifier__subtitle-accent">All fields marked * are required.</span>
			</p>
		</div>
	</header>

	<div class="yl-qualifier__progress" aria-live="polite" data-form-progress>
		<div class="yl-qualifier__progress-header">
			<span class="yl-qualifier__step-label" data-step-label>Step 1 of 4</span>
			<span class="yl-qualifier__step-name" data-step-name>Vehicle Type</span>
		</div>
		<div class="yl-qualifier__progress-track" role="progressbar" aria-valuemin="1" aria-valuemax="4" aria-valuenow="1" aria-label="Form progress">
			<div class="yl-qualifier__progress-fill" data-progress-fill></div>
		</div>
	</div>

	<div class="yl-qualifier__steps">
		<section class="yl-qualifier__step is-active" data-step="1" aria-labelledby="yl-step-1-title">
			<h3 class="yl-qualifier__sr-only" id="yl-step-1-title">Step 1: Vehicle details</h3>
			<div class="yl-qualifier__row">
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-make">Make <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
					<input class="yl-qualifier__input" type="text" id="yl-make" name="make" placeholder="Toyota" required autocomplete="organization" maxlength="80" aria-describedby="yl-step-1-error">
				</div>
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-model">Model <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
					<input class="yl-qualifier__input" type="text" id="yl-model" name="model" placeholder="Camry" required autocomplete="off" maxlength="80" aria-describedby="yl-step-1-error">
				</div>
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-year">Vehicle Year <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
					<input class="yl-qualifier__input" type="text" id="yl-year" name="vehicle_year" placeholder="2023" required inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" aria-describedby="yl-step-1-error">
				</div>
			</div>

			<div class="yl-qualifier__choice-section" role="group" aria-labelledby="yl-vehicle-type-label">
				<p class="yl-qualifier__label" id="yl-vehicle-type-label">Vehicle type <span class="yl-qualifier__required" aria-hidden="true">*</span></p>
				<div class="yl-qualifier__vehicle-grid" role="radiogroup" aria-describedby="yl-step-1-error">
					<label class="yl-qualifier__choice-option yl-qualifier__vehicle-option"><input type="radio" name="vehicle_type" value="car" required><span class="yl-qualifier__choice-label">Car</span></label>
					<label class="yl-qualifier__choice-option yl-qualifier__vehicle-option"><input type="radio" name="vehicle_type" value="truck-suv"><span class="yl-qualifier__choice-label">Truck / SUV</span></label>
					<label class="yl-qualifier__choice-option yl-qualifier__vehicle-option"><input type="radio" name="vehicle_type" value="motorcycles"><span class="yl-qualifier__choice-label">Motorcycle</span></label>
					<label class="yl-qualifier__choice-option yl-qualifier__vehicle-option"><input type="radio" name="vehicle_type" value="boats"><span class="yl-qualifier__choice-label">Boat</span></label>
					<label class="yl-qualifier__choice-option yl-qualifier__vehicle-option"><input type="radio" name="vehicle_type" value="aircraft"><span class="yl-qualifier__choice-label">Aircraft</span></label>
					<label class="yl-qualifier__choice-option yl-qualifier__vehicle-option"><input type="radio" name="vehicle_type" value="rvs"><span class="yl-qualifier__choice-label">RV</span></label>
				</div>
			</div>
			<p class="yl-qualifier__error" id="yl-step-1-error" data-step-error="1" role="alert">Please complete all required vehicle fields.</p>
		</section>

		<section class="yl-qualifier__step" data-step="2" aria-labelledby="yl-step-2-title">
			<h3 class="yl-qualifier__sr-only" id="yl-step-2-title">Step 2: Acquisition and issue</h3>
			<div class="yl-qualifier__block">
				<p class="yl-qualifier__label">How did you acquire it? <span class="yl-qualifier__required" aria-hidden="true">*</span></p>
				<div class="yl-qualifier__choice-grid yl-qualifier__choice-grid--acquire" role="radiogroup" aria-describedby="yl-step-2-error">
					<label class="yl-qualifier__choice-option"><input type="radio" name="acquisition_method" value="purchased-financed" required><span class="yl-qualifier__choice-label">Purchased (financed)</span></label>
					<label class="yl-qualifier__choice-option"><input type="radio" name="acquisition_method" value="purchased-full"><span class="yl-qualifier__choice-label">Purchased (paid in full)</span></label>
					<label class="yl-qualifier__choice-option"><input type="radio" name="acquisition_method" value="leased"><span class="yl-qualifier__choice-label">Leased</span></label>
					<label class="yl-qualifier__choice-option"><input type="radio" name="acquisition_method" value="not-sure"><span class="yl-qualifier__choice-label">Not sure</span></label>
				</div>
			</div>
			<div class="yl-qualifier__block yl-qualifier__block--tight">
				<label class="yl-qualifier__label" for="yl-issue">What's going on? <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
				<textarea class="yl-qualifier__textarea yl-qualifier__textarea--issue" id="yl-issue" name="issue_description" placeholder="Engine stalls intermittently. Dealership attempted repair twice under warranty..." required rows="4" maxlength="2000" aria-describedby="yl-step-2-error"></textarea>
			</div>
			<p class="yl-qualifier__error" id="yl-step-2-error" data-step-error="2" role="alert">Please complete all required fields.</p>
		</section>

		<section class="yl-qualifier__step" data-step="3" aria-labelledby="yl-step-3-title">
			<h3 class="yl-qualifier__sr-only" id="yl-step-3-title">Step 3: Repair history</h3>
			<div class="yl-qualifier__choice-section yl-qualifier__choice-section--tight" role="group" aria-labelledby="yl-repair-attempts-label">
				<p class="yl-qualifier__label" id="yl-repair-attempts-label">Repair attempts so far <span class="yl-qualifier__required" aria-hidden="true">*</span></p>
				<div class="yl-qualifier__scale-grid" role="radiogroup" aria-describedby="yl-step-3-error">
					<label class="yl-qualifier__scale-option"><input type="radio" name="repair_attempts" value="1" required><span class="yl-qualifier__scale-label">1</span></label>
					<label class="yl-qualifier__scale-option"><input type="radio" name="repair_attempts" value="2"><span class="yl-qualifier__scale-label">2</span></label>
					<label class="yl-qualifier__scale-option"><input type="radio" name="repair_attempts" value="3"><span class="yl-qualifier__scale-label">3</span></label>
					<label class="yl-qualifier__scale-option"><input type="radio" name="repair_attempts" value="4+"><span class="yl-qualifier__scale-label">4+</span></label>
				</div>
			</div>
			<div class="yl-qualifier__choice-section" role="group" aria-labelledby="yl-warranty-label">
				<p class="yl-qualifier__label" id="yl-warranty-label">Is it still under factory warranty? <span class="yl-qualifier__required" aria-hidden="true">*</span></p>
				<div class="yl-qualifier__choice-grid yl-qualifier__choice-grid--3" role="radiogroup" aria-describedby="yl-step-3-error">
					<label class="yl-qualifier__choice-option"><input type="radio" name="under_warranty" value="yes" required><span class="yl-qualifier__choice-label">Yes</span></label>
					<label class="yl-qualifier__choice-option"><input type="radio" name="under_warranty" value="no"><span class="yl-qualifier__choice-label">No</span></label>
					<label class="yl-qualifier__choice-option"><input type="radio" name="under_warranty" value="not-sure"><span class="yl-qualifier__choice-label">Not sure</span></label>
				</div>
			</div>
			<div class="yl-qualifier__info">
				<span class="yl-qualifier__info-icon" aria-hidden="true">&#10003;</span>
				<div class="yl-qualifier__info-text">
					<span class="yl-qualifier__info-title">Based on what you've told us, this may qualify.</span>
					<span class="yl-qualifier__info-subtitle">One more step and a real attorney will review your case — not a bot.</span>
				</div>
			</div>
			<p class="yl-qualifier__error" id="yl-step-3-error" data-step-error="3" role="alert">Please complete all required fields.</p>
		</section>

		<section class="yl-qualifier__step" data-step="4" aria-labelledby="yl-step-4-title">
			<h3 class="yl-qualifier__sr-only" id="yl-step-4-title">Step 4: Contact information</h3>
			<div class="yl-qualifier__row yl-qualifier__row--spaced">
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-first-name">First name <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
					<input class="yl-qualifier__input" type="text" id="yl-first-name" name="first_name" placeholder="Jane" required autocomplete="given-name" maxlength="80" aria-describedby="yl-step-4-error">
				</div>
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-last-name">Last name <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
					<input class="yl-qualifier__input" type="text" id="yl-last-name" name="last_name" placeholder="Rivera" required autocomplete="family-name" maxlength="80" aria-describedby="yl-step-4-error">
				</div>
			</div>
			<div class="yl-qualifier__row yl-qualifier__row--spaced">
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-phone">Phone <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
					<input class="yl-qualifier__input" type="tel" id="yl-phone" name="phone" placeholder="(310) 555-0199" required autocomplete="tel" inputmode="tel" maxlength="20" aria-describedby="yl-step-4-error">
				</div>
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-email">Email <span class="yl-qualifier__required" aria-hidden="true">*</span></label>
					<input class="yl-qualifier__input" type="email" id="yl-email" name="email" placeholder="you@example.com" required autocomplete="email" maxlength="120" aria-describedby="yl-step-4-error">
				</div>
			</div>
			<div class="yl-qualifier__row yl-qualifier__row--spaced">
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-city">City</label>
					<input class="yl-qualifier__input" type="text" id="yl-city" name="city" placeholder="Santa Monica" autocomplete="address-level2" maxlength="80">
				</div>
				<div class="yl-qualifier__field">
					<label class="yl-qualifier__label" for="yl-zip">Zip code</label>
					<input class="yl-qualifier__input" type="text" id="yl-zip" name="zip" placeholder="90404" inputmode="numeric" autocomplete="postal-code" maxlength="10">
				</div>
			</div>
			<p class="yl-qualifier__error" id="yl-step-4-error" data-step-error="4" role="alert">Please complete all required contact fields.</p>
		</section>
	</div>

	<div class="yl-qualifier__status" data-form-status aria-live="polite"></div>

	<div class="yl-qualifier__success-extra" data-success-panel aria-live="polite">
		<div class="yl-qualifier__reference">
			<span data-reference-text>Reference #LMN-000000-0000</span>
			<button type="button" class="yl-qualifier__copy-btn" data-copy-reference aria-label="Copy reference number">&#10697;</button>
		</div>
		<div class="yl-qualifier__summary" data-success-summary></div>
		<p class="yl-qualifier__confirm-note">
			Save your reference number. If anything looks wrong, contact us at
			<a href="mailto:<?php echo esc_attr( $yl_form['contact_email'] ); ?>"><?php echo esc_html( $yl_form['contact_label'] ); ?></a>.
		</p>
		<button type="button" class="yl-qualifier__restart" data-restart-form>Need to submit a different vehicle?</button>
	</div>

	<div class="yl-qualifier__nav">
		<button type="button" class="yl-qualifier__btn yl-qualifier__btn--back" data-back disabled aria-disabled="true">Back</button>
		<button type="button" class="yl-qualifier__btn yl-qualifier__btn--next yl-qualifier__nav-action" data-next>Next</button>
		<button type="submit" class="yl-qualifier__btn yl-qualifier__btn--submit yl-qualifier__nav-action is-hidden" data-submit hidden aria-hidden="true">Submit my case review</button>
	</div>
</form>

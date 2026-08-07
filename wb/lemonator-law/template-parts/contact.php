<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact_status = isset( $_GET['contact'] ) ? sanitize_text_field( wp_unslash( $_GET['contact'] ) ) : '';
?>
<section class="lemonator-section lemonator-contact" id="contact">
	<div class="lemonator-container lemonator-contact__grid">
		<div class="lemonator-contact__form-panel">
			<form class="lemonator-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="lemonator_contact">
				<?php wp_nonce_field( 'lemonator_contact', 'lemonator_contact_nonce' ); ?>

				<div class="lemonator-contact-form__row">
					<label class="lemonator-contact-form__field">
						<span>First name</span>
						<input type="text" name="first_name" required autocomplete="given-name">
					</label>
					<label class="lemonator-contact-form__field">
						<span>Last name</span>
						<input type="text" name="last_name" required autocomplete="family-name">
					</label>
				</div>

				<label class="lemonator-contact-form__field">
					<span>Email</span>
					<input type="email" name="email" required autocomplete="email">
				</label>

				<label class="lemonator-contact-form__field">
					<span>Message</span>
					<textarea name="message" rows="5" required></textarea>
				</label>

				<label class="lemonator-contact-form__checkbox">
					<input type="checkbox" name="consent" value="1" required>
					<span>I agree to be contacted about my inquiry.</span>
				</label>

				<button type="submit" class="lemonator-btn lemonator-btn--primary lemonator-btn--full">Submit</button>

				<?php if ( 'sent' === $contact_status ) : ?>
					<p class="lemonator-contact-form__message lemonator-contact-form__message--success" role="status">Great! We've received your information.</p>
				<?php elseif ( 'error' === $contact_status ) : ?>
					<p class="lemonator-contact-form__message lemonator-contact-form__message--error" role="alert">We couldn't process your submission. Please retry.</p>
				<?php endif; ?>
			</form>
		</div>

		<div class="lemonator-contact__info">
			<img class="lemonator-contact__photo" src="<?php echo esc_url( lemonator_asset( 'e954ee329cebedb9c887bcdd77308a13fb272d93-1024x683.jpg' ) ); ?>" alt="Law office" loading="lazy">

			<ul class="lemonator-contact__details">
				<li>
					<strong>Address</strong>
					<span>3200 Santa Monica Blvd, Santa Monica, CA 90404</span>
				</li>
				<li>
					<strong>Phone</strong>
					<a href="tel:+18665053666">(866) 505-3666</a>
				</li>
				<li>
					<strong>Hours</strong>
					<span>Monday – Friday, 9:00 AM to 5:00 PM.<br>After hours, our answering service will assist you.</span>
				</li>
			</ul>
		</div>
	</div>
</section>

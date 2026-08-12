<?php
/**
 * Title: Contact Form
 * Slug: tanya-barrans/contact-form
 * Categories: featured
 *
 * Posts to the tanya/v1/contact REST route, which files the lead in Follow Up
 * Boss server-side. No key or destination is exposed here. The form works as
 * plain HTML if JavaScript fails, and the direct email and phone stay visible
 * on the page so nobody is ever forced through the form to reach Tanya.
 */
$choices = tanya_contact_choices();
?>
<!-- wp:group {"align":"full","className":"tb-contact-form-section","backgroundColor":"alabaster","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull tb-contact-form-section has-alabaster-background-color has-background">
	<div class="tb-page-shell">

		<!-- wp:paragraph {"className":"tb-eyebrow"} -->
		<p class="tb-eyebrow">Send a Message</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"fontSize":"x-large"} -->
		<h2 class="wp-block-heading has-x-large-font-size">Tell Tanya what you are thinking about.</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"textColor":"muted"} -->
		<p class="has-muted-color has-text-color">There is no wrong question and no obligation. Fill in as much or as little as you like — Tanya reads every message herself and usually replies within one business day.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<form class="tb-contact-form" novalidate>
			<div class="tb-contact-grid">

				<p class="tb-field">
					<label for="tb-c-name">Your name <span class="tb-required" aria-hidden="true">*</span></label>
					<input type="text" id="tb-c-name" name="name" autocomplete="name" required />
				</p>

				<p class="tb-field">
					<label for="tb-c-email">Email address <span class="tb-required" aria-hidden="true">*</span></label>
					<input type="email" id="tb-c-email" name="email" autocomplete="email" required />
				</p>

				<p class="tb-field">
					<label for="tb-c-phone">Phone <span class="tb-optional">(optional)</span></label>
					<input type="tel" id="tb-c-phone" name="phone" autocomplete="tel" />
				</p>

				<p class="tb-field">
					<label for="tb-c-contact">Best way to reach you</label>
					<select id="tb-c-contact" name="contact_method">
						<?php foreach ( $choices['contact'] as $option ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p class="tb-field">
					<label for="tb-c-interest">What is on your mind?</label>
					<select id="tb-c-interest" name="interest">
						<?php foreach ( $choices['interest'] as $option ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p class="tb-field">
					<label for="tb-c-timing">Your timing</label>
					<select id="tb-c-timing" name="timing">
						<?php foreach ( $choices['timing'] as $option ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p class="tb-field tb-field--full">
					<label for="tb-c-message">Anything you would like Tanya to know</label>
					<textarea id="tb-c-message" name="message" rows="5"></textarea>
				</p>

				<!-- Honeypot: hidden from people, tempting to bots. -->
				<div class="tb-contact-hp" aria-hidden="true">
					<label for="tb-c-website">Website</label>
					<input type="text" id="tb-c-website" name="website" tabindex="-1" autocomplete="off" />
				</div>

			</div>

			<p class="tb-contact-consent">By sending this you agree that Tanya may contact you about your enquiry. Your details are stored in her client system and are never sold or shared. See the <a href="/privacy-policy/">Privacy Policy</a>.</p>

			<button type="submit" class="tb-contact-submit">Send to Tanya</button>
			<p class="tb-contact-status" role="status" aria-live="polite"></p>
		</form>
		<!-- /wp:html -->

	</div>
</div>
<!-- /wp:group -->

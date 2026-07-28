<?php
/**
 * Title: Newsletter Signup
 * Slug: tanya-barrans/newsletter
 * Categories: featured
 */
?>
<!-- wp:group {"align":"full","className":"tb-newsletter-feature","backgroundColor":"base","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull tb-newsletter-feature has-base-background-color has-background">

	<!-- wp:group {"className":"tb-newsletter-feature__image","layout":{"type":"default"}} -->
	<div class="wp-block-group tb-newsletter-feature__image" aria-hidden="true" style="background-image:url(/wp-content/themes/tanya-barrans/assets/images/lwyl-coffee.jpg)"></div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"tb-newsletter-feature__content tb-reveal","layout":{"type":"default"}} -->
	<div class="wp-block-group tb-newsletter-feature__content tb-reveal">
		<!-- wp:paragraph {"className":"tb-script-accent"} -->
		<p class="tb-script-accent">Let’s stay in touch</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"fontSize":"x-large"} -->
		<h2 class="wp-block-heading has-x-large-font-size">Notes worth opening.</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"textColor":"graphite"} -->
		<p class="has-graphite-color has-text-color">Useful home ideas, local stories, and honest real estate guidance—delivered with no pressure and no polished filler.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<form class="tb-newsletter-form" novalidate>
			<div class="tb-newsletter-fields">
				<label class="tb-sr-only" for="tb-nl-name">First name</label>
				<input type="text" id="tb-nl-name" name="name" class="tb-newsletter-input" placeholder="First name" autocomplete="given-name" />

				<label class="tb-sr-only" for="tb-nl-email">Email address</label>
				<input type="email" id="tb-nl-email" name="email" class="tb-newsletter-input" placeholder="Email address" autocomplete="email" required />

				<div class="tb-newsletter-hp" aria-hidden="true">
					<label for="tb-nl-website">Website</label>
					<input type="text" id="tb-nl-website" name="website" tabindex="-1" autocomplete="off" />
				</div>

				<button type="submit" class="tb-newsletter-submit">Subscribe</button>
			</div>
			<p class="tb-newsletter-status" role="status" aria-live="polite"></p>
		</form>
		<!-- /wp:html -->

		<!-- wp:paragraph {"className":"tb-newsletter-feature__privacy"} -->
		<p class="tb-newsletter-feature__privacy">No spam. Unsubscribe whenever you like.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

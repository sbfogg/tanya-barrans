<?php
/**
 * Title: Newsletter Signup
 * Slug: tanya-barrans/newsletter
 * Categories: featured
 */
?>
<!-- wp:group {"className":"tb-newsletter tb-reveal","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|40","right":"var:preset|spacing|40"},"margin":{"top":"var:preset|spacing|60"}},"border":{"radius":"8px"}},"backgroundColor":"ink","textColor":"base","layout":{"type":"constrained","contentSize":"600px"}} -->
<div class="wp-block-group tb-newsletter tb-reveal has-base-color has-ink-background-color has-text-color has-background" style="border-radius:8px;margin-top:var(--wp--preset--spacing--60);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"align":"center","className":"tb-eyebrow"} -->
	<p class="has-text-align-center tb-eyebrow">Stay in the Loop</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"textAlign":"center","fontSize":"x-large","textColor":"base"} -->
	<h2 class="wp-block-heading has-text-align-center has-base-color has-text-color has-x-large-font-size">Puget Sound updates, worth reading.</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","textColor":"stone","fontSize":"small"} -->
	<p class="has-text-align-center has-stone-color has-text-color has-small-font-size">Neighborhood guides, honest market notes, and the occasional sourdough recommendation. No spam, unsubscribe anytime.</p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<form class="tb-newsletter-form" novalidate>
		<div class="tb-newsletter-fields">
			<label class="tb-sr-only" for="tb-nl-name">First name</label>
			<input type="text" id="tb-nl-name" name="name" class="tb-newsletter-input" placeholder="First name" autocomplete="given-name" />

			<label class="tb-sr-only" for="tb-nl-email">Email address</label>
			<input type="email" id="tb-nl-email" name="email" class="tb-newsletter-input" placeholder="you@example.com" autocomplete="email" required />

			<!-- Honeypot: hidden from humans, bots tend to fill it. -->
			<div class="tb-newsletter-hp" aria-hidden="true">
				<label for="tb-nl-website">Website</label>
				<input type="text" id="tb-nl-website" name="website" tabindex="-1" autocomplete="off" />
			</div>

			<button type="submit" class="tb-newsletter-submit">Subscribe</button>
		</div>
		<p class="tb-newsletter-status" role="status" aria-live="polite"></p>
	</form>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->

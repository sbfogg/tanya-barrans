<?php
/**
 * Title: Homepage Hero
 * Slug: tanya-barrans/hero
 * Categories: featured
 */
?>
<!-- wp:group {"align":"full","className":"tb-home-hero","backgroundColor":"alabaster","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull tb-home-hero has-alabaster-background-color has-background">

	<!-- wp:html -->
	<img
		class="tb-home-hero__image"
		src="/wp-content/themes/tanya-barrans/assets/images/lwyl-hero.jpg"
		alt=""
		width="1720"
		height="914"
		fetchpriority="high"
		decoding="async"
	/>
	<!-- /wp:html -->

	<!-- wp:group {"className":"tb-home-hero__inner","layout":{"type":"default"}} -->
	<div class="wp-block-group tb-home-hero__inner">

		<!-- wp:group {"className":"tb-home-hero__content tb-hero-arrive","layout":{"type":"default"}} -->
		<div class="wp-block-group tb-home-hero__content tb-hero-arrive">

			<!-- The blueprint headline is a full sentence and the page may only
			     carry one H1, so the script accent lives inside the heading
			     rather than as a separate line above it. -->
			<!-- wp:heading {"level":1,"className":"tb-home-hero__title"} -->
			<h1 class="wp-block-heading tb-home-hero__title">Helping you <span class="tb-script-accent">love<br>where you live.</span></h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"tb-home-hero__dek"} -->
			<p class="tb-home-hero__dek">Local knowledge, honest strategy, and useful inspiration for buying, selling, and creating a life you love in Renton and beyond.</p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"className":"tb-home-hero__buttons","style":{"spacing":{"blockGap":"0.75rem","margin":{"top":"var:preset|spacing|40"}}}} -->
			<div class="wp-block-buttons tb-home-hero__buttons" style="margin-top:var(--wp--preset--spacing--40)">
				<!-- wp:button {"backgroundColor":"charcoal"} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-charcoal-background-color has-background wp-element-button" href="/contact/">Let’s Talk About Your Next Move</a></div>
				<!-- /wp:button -->

				<?php
				/*
				 * This button is the approved layout's secondary hero CTA and
				 * is intended to read "Explore Renton" pointing at
				 * /rooted-in-renton/. That page is withheld until it meets the
				 * blueprint's launch minimum, so the button points at the
				 * Journal — a real, populated destination — in the meantime.
				 * Restoring it is a one-line change to this href and label.
				 */
				?>
				<!-- wp:button {"backgroundColor":"coral","textColor":"charcoal"} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-charcoal-color has-coral-background-color has-text-color has-background wp-element-button" href="/blog/">Read the Journal</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

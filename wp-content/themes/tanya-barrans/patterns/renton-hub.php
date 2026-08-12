<?php
/**
 * Title: Renton Hub
 * Slug: tanya-barrans/renton-hub
 * Categories: featured
 *
 * The Renton destination, branded Rooted in Renton.
 *
 * This is deliberately a shell rather than a page of hand-placed content.
 * Every story area is a Query Loop reading the "Renton" category (term 15),
 * so publishing a post with that category ticked makes it appear here — and
 * scheduling one makes it appear on its publish date. Nobody has to touch
 * this layout to add content.
 *
 * The featured slot takes the newest post; the grid takes the next six, so a
 * story never shows twice. Both carry a no-results state, which is what
 * visitors see until the first Renton post is published.
 *
 * Category IDs are fixed at creation: 15 Renton, 16 Eat & Drink,
 * 17 Parks & Outdoors, 18 Local Businesses, 19 Community Events,
 * 20 Neighborhood Stories.
 */
?>
<!-- wp:group {"align":"full","className":"tb-renton","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull tb-renton">

	<!-- wp:html -->
	<section class="tb-renton-hero" aria-labelledby="renton-title">
		<div class="tb-page-shell tb-renton-hero__inner">
			<p class="tb-renton-badge">Rooted in Renton</p>
			<h1 id="renton-title" class="tb-renton-title">Real places. Local stories. A deeper connection to Renton.</h1>
			<p class="tb-renton-dek">The people, places and everyday details that make this city feel like home — written by someone who actually lives here.</p>
			<p class="tb-renton-hero__links">
				<a class="tb-renton-button" href="#renton-latest">Explore the latest stories</a>
				<a class="tb-renton-link" href="/contact/">Ask Tanya about Renton <span aria-hidden="true">&rarr;</span></a>
			</p>
		</div>
	</section>
	<!-- /wp:html -->

	<!-- wp:group {"className":"tb-renton-featured","layout":{"type":"default"}} -->
	<div class="wp-block-group tb-renton-featured">
		<div class="tb-page-shell">

			<!-- wp:paragraph {"className":"tb-eyebrow"} -->
			<p class="tb-eyebrow">Featured Story</p>
			<!-- /wp:paragraph -->

			<!-- wp:query {"queryId":40,"query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","taxQuery":{"category":[15]},"inherit":false},"align":"wide"} -->
			<div class="wp-block-query alignwide">
				<!-- wp:post-template -->
					<!-- wp:group {"className":"tb-renton-feature-card","layout":{"type":"default"}} -->
					<div class="wp-block-group tb-renton-feature-card">
						<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
						<!-- wp:post-terms {"term":"category","className":"tb-renton-kicker"} /-->
						<!-- wp:post-title {"isLink":true,"fontSize":"x-large"} /-->
						<!-- wp:post-excerpt {"excerptLength":34} /-->
						<!-- wp:post-date {"format":"F j, Y","className":"tb-renton-date"} /-->
					</div>
					<!-- /wp:group -->
				<!-- /wp:post-template -->

				<!-- wp:query-no-results -->
					<!-- wp:paragraph {"className":"tb-renton-empty"} -->
					<p class="tb-renton-empty">The first Renton stories are being written now. In the meantime, tell Tanya what you would like to see covered — a favourite restaurant, a park worth knowing about, or a corner of the city that deserves more attention.</p>
					<!-- /wp:paragraph -->

					<!-- wp:buttons -->
					<div class="wp-block-buttons">
						<!-- wp:button {"backgroundColor":"ink"} -->
						<div class="wp-block-button"><a class="wp-block-button__link has-ink-background-color has-background wp-element-button" href="/contact/">Suggest a Renton story</a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				<!-- /wp:query-no-results -->
			</div>
			<!-- /wp:query -->

		</div>
	</div>
	<!-- /wp:group -->

	<!-- wp:html -->
	<nav class="tb-renton-topics" aria-label="Renton topics">
		<div class="tb-page-shell">
			<a href="/category/renton/eat-drink/">Eat &amp; Drink</a>
			<a href="/category/renton/parks-outdoors/">Parks &amp; Outdoors</a>
			<a href="/category/renton/local-businesses/">Local Businesses</a>
			<a href="/category/renton/community-events/">Community Events</a>
			<a href="/category/renton/neighborhood-stories/">Neighborhood Stories</a>
		</div>
	</nav>
	<!-- /wp:html -->

	<!-- wp:group {"className":"tb-renton-latest","layout":{"type":"default"},"anchor":"renton-latest"} -->
	<div class="wp-block-group tb-renton-latest" id="renton-latest">
		<div class="tb-page-shell">

			<!-- wp:paragraph {"className":"tb-eyebrow"} -->
			<p class="tb-eyebrow">More From Renton</p>
			<!-- /wp:paragraph -->

			<!-- wp:query {"queryId":41,"query":{"perPage":6,"pages":0,"offset":1,"postType":"post","order":"desc","orderBy":"date","taxQuery":{"category":[15]},"inherit":false},"align":"wide"} -->
			<div class="wp-block-query alignwide">
				<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
					<!-- wp:group {"className":"tb-card tb-renton-card","layout":{"type":"default"}} -->
					<div class="wp-block-group tb-card tb-renton-card">
						<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->
						<!-- wp:group {"className":"tb-renton-card__body","layout":{"type":"default"}} -->
						<div class="wp-block-group tb-renton-card__body">
							<!-- wp:post-terms {"term":"category","className":"tb-renton-kicker"} /-->
							<!-- wp:post-title {"isLink":true,"fontSize":"large"} /-->
							<!-- wp:post-date {"format":"F j, Y","className":"tb-renton-date"} /-->
						</div>
						<!-- /wp:group -->
					</div>
					<!-- /wp:group -->
				<!-- /wp:post-template -->

				<!-- wp:query-no-results -->
					<!-- wp:paragraph {"className":"tb-renton-empty"} -->
					<p class="tb-renton-empty">More Renton stories are on the way.</p>
					<!-- /wp:paragraph -->
				<!-- /wp:query-no-results -->
			</div>
			<!-- /wp:query -->

		</div>
	</div>
	<!-- /wp:group -->

	<!-- wp:html -->
	<section class="tb-renton-cta" aria-labelledby="renton-cta-title">
		<div class="tb-page-shell">
			<h2 id="renton-cta-title">Thinking about making Renton home?</h2>
			<p>Or making a move within the city? Tanya can walk you through the neighbourhoods and the market without the sales pressure.</p>
			<p class="tb-renton-cta__links">
				<a class="tb-renton-button tb-renton-button--light" href="/contact/">Ask Tanya a question</a>
				<a class="tb-renton-link tb-renton-link--light" href="/buy/">Explore buying in Renton <span aria-hidden="true">&rarr;</span></a>
			</p>
		</div>
	</section>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->

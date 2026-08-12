<?php
/**
 * Title: Neighborhoods Index
 * Slug: tanya-barrans/neighborhoods-index
 * Categories: featured
 *
 * The landing page behind the Neighborhoods menu. Renton is the only city
 * built out so far; the others are listed as honest "in progress" cards
 * rather than links to empty pages, so nothing in the menu leads nowhere.
 *
 * To add a city later: give it a page, then turn its card below into a link.
 */
?>
<!-- wp:group {"align":"full","className":"tb-hoods","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull tb-hoods">
	<div class="tb-page-shell">

		<!-- wp:paragraph {"className":"tb-eyebrow"} -->
		<p class="tb-eyebrow">Neighborhoods</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":1,"fontSize":"xx-large"} -->
		<h1 class="wp-block-heading has-xx-large-font-size">Where should you live?</h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"textColor":"muted","className":"tb-hoods__dek"} -->
		<p class="has-muted-color has-text-color tb-hoods__dek">Every city around the south end of Lake Washington has its own feel — the commute, the schools, the parks, what a weekend actually looks like. These guides are about what it is genuinely like to live in each one, not a sales pitch for any of them.</p>
		<!-- /wp:paragraph -->

		<!-- wp:html -->
		<ul class="tb-hoods-grid">
			<li class="tb-hoods-card tb-hoods-card--live">
				<a href="/renton/">
					<span class="tb-hoods-card__tag">Rooted in Renton</span>
					<span class="tb-hoods-card__name">Renton</span>
					<span class="tb-hoods-card__copy">Local stories, favourite places and the people who make Renton feel like home.</span>
					<span class="tb-hoods-card__go">Explore Renton <span aria-hidden="true">&rarr;</span></span>
				</a>
			</li>
			<li class="tb-hoods-card tb-hoods-card--soon"><span class="tb-hoods-card__name">Kent</span><span class="tb-hoods-card__copy">Guide in progress.</span></li>
			<li class="tb-hoods-card tb-hoods-card--soon"><span class="tb-hoods-card__name">Covington</span><span class="tb-hoods-card__copy">Guide in progress.</span></li>
			<li class="tb-hoods-card tb-hoods-card--soon"><span class="tb-hoods-card__name">Maple Valley</span><span class="tb-hoods-card__copy">Guide in progress.</span></li>
			<li class="tb-hoods-card tb-hoods-card--soon"><span class="tb-hoods-card__name">Newcastle</span><span class="tb-hoods-card__copy">Guide in progress.</span></li>
		</ul>
		<!-- /wp:html -->

		<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
		<p class="has-muted-color has-text-color has-small-font-size">Looking at somewhere not listed here? <a href="/contact/">Ask Tanya</a> — she covers the wider Puget Sound area and will tell you honestly what she knows about a neighbourhood and what she does not.</p>
		<!-- /wp:paragraph -->

	</div>
</div>
<!-- /wp:group -->

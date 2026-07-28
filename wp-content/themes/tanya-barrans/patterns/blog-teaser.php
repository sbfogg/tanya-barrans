<?php
/**
 * Title: Latest From the Journal
 * Slug: tanya-barrans/blog-teaser
 * Categories: featured
 */
?>
<!-- wp:group {"align":"full","className":"tb-journal-section","backgroundColor":"alabaster","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull tb-journal-section has-alabaster-background-color has-background">

	<!-- wp:group {"align":"wide","className":"tb-journal-section__header","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide tb-journal-section__header">
		<!-- wp:group {"className":"tb-reveal","layout":{"type":"default"}} -->
		<div class="wp-block-group tb-reveal">
			<!-- wp:paragraph {"className":"tb-eyebrow"} -->
			<p class="tb-eyebrow">The Journal</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"fontSize":"xx-large"} -->
			<h2 class="wp-block-heading has-xx-large-font-size">Useful stories for home,<br><em>community, and your next move.</em></h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:paragraph {"className":"tb-text-link"} -->
		<p class="tb-text-link"><a href="/blog/">Explore the Journal <span aria-hidden="true">→</span></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":1,"query":{"perPage":3,"postType":"post","order":"desc","orderBy":"date"},"align":"wide","className":"tb-journal-query"} -->
	<div class="wp-block-query alignwide tb-journal-query">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->

			<!-- wp:group {"className":"tb-journal-card tb-reveal","layout":{"type":"constrained"}} -->
			<div class="wp-block-group tb-journal-card">
				<!-- wp:post-featured-image {"aspectRatio":"4/3","isLink":true} /-->
				<!-- wp:group {"className":"tb-journal-card__content","layout":{"type":"constrained"}} -->
				<div class="wp-block-group tb-journal-card__content">
					<!-- wp:post-terms {"term":"category","className":"tb-journal-card__category"} /-->
					<!-- wp:post-title {"isLink":true,"fontSize":"large"} /-->
					<!-- wp:post-date {"textColor":"graphite","fontSize":"small"} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

</div>
<!-- /wp:group -->

<?php
/**
 * Title: Latest From the Blog
 * Slug: tanya-barrans/blog-teaser
 * Categories: featured
 */
?>
<!-- wp:group {"align":"full","className":"tb-topo-bg","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"alabaster","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull tb-topo-bg has-alabaster-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">

	<!-- wp:paragraph {"align":"center","className":"tb-eyebrow"} -->
	<p class="has-text-align-center tb-eyebrow">The Journal</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"textAlign":"center","fontSize":"xx-large","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
	<h2 class="wp-block-heading has-text-align-center has-xx-large-font-size" style="margin-bottom:var(--wp--preset--spacing--50)">Local knowledge, delivered</h2>
	<!-- /wp:heading -->

	<!-- wp:query {"queryId":1,"query":{"perPage":3,"postType":"post","order":"desc","orderBy":"date"},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->

			<!-- wp:group {"className":"tb-card tb-reveal","style":{"spacing":{"blockGap":"0.75rem"}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
			<div class="wp-block-group tb-card tb-reveal has-base-background-color has-background">
				<!-- wp:post-featured-image {"aspectRatio":"3/2"} /-->
				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|40","left":"var:preset|spacing|30","right":"var:preset|spacing|30"},"blockGap":"0.625rem"}},"layout":{"type":"constrained"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--30)">
					<!-- wp:post-terms {"term":"category","className":"tb-eyebrow"} /-->
					<!-- wp:post-title {"isLink":true,"fontSize":"large"} /-->
					<!-- wp:post-date {"textColor":"muted","fontSize":"small"} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:button {"className":"tb-button-outline","textColor":"ink"} -->
		<div class="wp-block-button tb-button-outline"><a class="wp-block-button__link has-ink-color has-text-color wp-element-button" href="/blog/">Read All Posts</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->

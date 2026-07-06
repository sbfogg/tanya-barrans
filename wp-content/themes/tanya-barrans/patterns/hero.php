<?php
/**
 * Title: Homepage Hero
 * Slug: tanya-barrans/hero
 * Categories: featured
 */
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-hero.svg' ) ); ?>","dimRatio":50,"customGradient":"linear-gradient(180deg,rgba(28,42,36,0.25) 0%,rgba(28,42,36,0.65) 100%)","minHeight":88,"minHeightUnit":"vh","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"860px"}} -->
<div class="wp-block-cover alignfull tb-parallax" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40);min-height:88vh">
	<span aria-hidden="true" class="wp-block-cover__background has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(180deg,rgba(28,42,36,0.25) 0%,rgba(28,42,36,0.65) 100%)"></span>
	<img class="wp-block-cover__image-background tb-parallax-bg" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-hero.svg' ) ); ?>" data-object-fit="cover"/>
	<div class="wp-block-cover__inner-container">

		<!-- wp:paragraph {"align":"center","className":"tb-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|sand"}}},"color":{"text":"var:preset|color|sand"}}} -->
		<p class="has-text-align-center tb-eyebrow has-text-color" style="color:var(--wp--preset--color--sand)">Puget Sound Real Estate</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"hero","textColor":"base"} -->
		<h1 class="wp-block-heading has-text-align-center has-base-color has-text-color has-hero-font-size">Your home, <em>handled with care.</em></h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","textColor":"sand","fontSize":"large"} -->
		<p class="has-text-align-center has-sand-color has-text-color has-large-font-size">Local expertise, honest guidance, and full-service support — from first showing to final signature.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"blockGap":"1rem","margin":{"top":"var:preset|spacing|40"}}}} -->
		<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)">
			<!-- wp:button -->
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/listings/">Find Your Next Home</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"tb-button-outline","textColor":"base"} -->
			<div class="wp-block-button tb-button-outline"><a class="wp-block-button__link has-base-color has-text-color wp-element-button" href="/sell/">Sell With Tanya</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</div>
</div>
<!-- /wp:cover -->

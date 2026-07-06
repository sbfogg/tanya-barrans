<?php
/**
 * Title: About Intro
 * Slug: tanya-barrans/about-intro
 * Categories: featured
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">

	<!-- wp:columns {"align":"wide","verticalAlignment":"center","className":"tb-content-card","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"},"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"radius":"8px"}},"backgroundColor":"base"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center tb-content-card has-base-background-color has-background" style="border-radius:8px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">

		<!-- wp:column {"verticalAlignment":"center","width":"42%","className":"tb-reveal"} -->
		<div class="wp-block-column is-vertically-aligned-center tb-reveal" style="flex-basis:42%">
			<!-- wp:image {"id":17,"sizeSlug":"full","linkDestination":"none","style":{"border":{"radius":"4px"}}} -->
			<figure class="wp-block-image size-full has-custom-border">
				<?php
				echo wp_get_attachment_image( 17, 'full', false, array(
					'class' => 'wp-image-17',
					'style' => 'border-radius:4px',
				) );
				?>
			</figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"58%","className":"tb-reveal"} -->
		<div class="wp-block-column is-vertically-aligned-center tb-reveal" style="flex-basis:58%">

			<!-- wp:paragraph {"className":"tb-eyebrow"} -->
			<p class="tb-eyebrow">Meet Tanya</p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"fontSize":"xx-large"} -->
			<h2 class="wp-block-heading has-xx-large-font-size">A neighbor first, <em>an agent second.</em></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"textColor":"muted"} -->
			<p class="has-muted-color has-text-color">Buying or selling a home is one of the biggest decisions you'll ever make — and you deserve someone who treats it that way. Tanya combines deep local knowledge of the Puget Sound region with straightforward advice and genuine care for the people she works with.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"textColor":"muted"} -->
			<p class="has-muted-color has-text-color">From staging and photography to negotiation and closing, every detail is handled so you can focus on what matters: your next chapter.</p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
			<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--30)">
				<!-- wp:button {"className":"tb-button-outline","textColor":"ink"} -->
				<div class="wp-block-button tb-button-outline"><a class="wp-block-button__link has-ink-color has-text-color wp-element-button" href="/about/">More About Tanya</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->

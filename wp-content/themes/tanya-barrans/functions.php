<?php
/**
 * Tanya Barrans Real Estate — theme setup.
 */

add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_editor_style( 'assets/css/extra.css' );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'tanya-barrans-extra',
		get_theme_file_uri( 'assets/css/extra.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_script(
		'tanya-barrans-reveal',
		get_theme_file_uri( 'assets/js/reveal.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

} );

// Basic SEO: output a meta description per page. Uses the page/post excerpt
// when available, falls back to a sensible site-wide default on the home
// page and anywhere else without one.
add_action( 'wp_head', function () {
	$description = '';

	if ( is_front_page() ) {
		$description = 'Tanya Barrans is a Puget Sound real estate broker with John L Scott, serving Renton, Kent, Covington, Maple Valley, and nearby communities with honest advice and local expertise.';
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$excerpt = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
			$description = wp_trim_words( $excerpt, 30, '…' );
		}
	} elseif ( is_category() || is_tag() || is_archive() ) {
		$description = trim( wp_strip_all_tags( term_description() ) );
	}

	if ( empty( $description ) ) {
		$description = get_bloginfo( 'description' );
	}

	if ( ! empty( $description ) ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
}, 1 );

// Fixed background scene behind the homepage hero and About section — pure
// CSS position:fixed, painted as the very first thing in <body> so later
// opaque sections (credibility, services, etc.) naturally scroll over and
// cover it without any extra work.
add_action( 'wp_body_open', function () {
	if ( ! is_front_page() ) {
		return;
	}
	?>
	<div class="tb-fixed-scene" aria-hidden="true">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-photo.jpg' ) ); ?>" alt=""/>
	</div>
	<?php
} );

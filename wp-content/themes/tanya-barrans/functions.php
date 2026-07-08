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

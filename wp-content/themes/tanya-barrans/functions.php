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

	// Newsletter signup form appears on the blog index and homepage.
	if ( is_home() || is_front_page() ) {
		wp_enqueue_script(
			'tanya-barrans-newsletter',
			get_theme_file_uri( 'assets/js/newsletter.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
		wp_localize_script(
			'tanya-barrans-newsletter',
			'tanyaNewsletter',
			array(
				'endpoint' => esc_url_raw( rest_url( 'tanya/v1/subscribe' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

} );

/**
 * Newsletter signup — server-side Flodesk integration.
 *
 * The front-end form POSTs to this endpoint; the endpoint calls the Flodesk
 * API using the key stored in wp-config.php (TANYA_FLODESK_API_KEY). The key
 * is never sent to the browser. Guards: WP REST nonce, email validation, and
 * a honeypot field to deter bots.
 */
add_action( 'rest_api_init', function () {
	register_rest_route(
		'tanya/v1',
		'/subscribe',
		array(
			'methods'             => 'POST',
			'callback'            => 'tanya_newsletter_subscribe',
			'permission_callback' => '__return_true',
		)
	);
} );

function tanya_newsletter_subscribe( WP_REST_Request $request ) {
	// CSRF / nonce check.
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response( array( 'message' => 'Your session expired. Please refresh the page and try again.' ), 403 );
	}

	// Honeypot: if filled, silently accept (pretend success) so bots move on.
	if ( ! empty( trim( (string) $request->get_param( 'website' ) ) ) ) {
		return new WP_REST_Response( array( 'message' => "You're on the list — thanks!" ), 200 );
	}

	$email = sanitize_email( (string) $request->get_param( 'email' ) );
	$first = sanitize_text_field( (string) $request->get_param( 'name' ) );

	if ( ! $email || ! is_email( $email ) ) {
		return new WP_REST_Response( array( 'message' => 'Please enter a valid email address.' ), 422 );
	}

	if ( ! defined( 'TANYA_FLODESK_API_KEY' ) || ! TANYA_FLODESK_API_KEY ) {
		return new WP_REST_Response( array( 'message' => 'The signup form is not configured yet. Please email Tanya directly.' ), 500 );
	}

	$body = array( 'email' => $email );
	if ( '' !== $first ) {
		$body['first_name'] = $first;
	}

	$response = wp_remote_post(
		'https://api.flodesk.com/v1/subscribers',
		array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( TANYA_FLODESK_API_KEY . ':' ),
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_REST_Response( array( 'message' => 'Something went wrong reaching our email service. Please try again in a moment.' ), 502 );
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code >= 200 && $code < 300 ) {
		return new WP_REST_Response( array( 'message' => "You're on the list — thank you!" ), 200 );
	}

	return new WP_REST_Response( array( 'message' => 'We could not add you right now. Please try again in a moment.' ), 502 );
}

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

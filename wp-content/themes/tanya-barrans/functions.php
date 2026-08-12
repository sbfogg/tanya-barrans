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
	if ( is_home() || is_front_page() || is_single() ) {
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

/**
 * Per-page SEO overrides, keyed by page slug.
 *
 * Without these, <title> falls back to the page's short admin title ("Buy")
 * and the description below stitches a heading onto the first line of body
 * copy, which reads as a fragment in search results. Each value here
 * describes something the page already contains; none of them assert
 * credentials, awards, transaction volume, or performance figures.
 *
 * Titles omit "Tanya Barrans" because WordPress appends the site name as
 * the second title part; including it here renders it twice.
 *
 * @return array<string, array{title: string, description: string}>
 */
function tanya_page_seo_overrides() {
	return array(
		'buy'     => array(
			'title'       => 'Buying a Home in Renton and the Puget Sound',
			'description' => 'Plan your home purchase with Tanya Barrans: how the process works step by step, what to expect along the way, and a low-pressure way to explore your buying power before you talk to anyone.',
		),
		'sell'    => array(
			'title'       => 'Selling Your Home in Renton and the Puget Sound',
			'description' => 'Thinking about selling? See how Tanya Barrans prepares, prices, and markets a home, and get a starting value range for yours before you commit to anything.',
		),
		'about'   => array(
			'title'       => 'About Tanya',
			'description' => 'Meet Tanya Barrans, a Renton-area broker with John L Scott who builds her business on relationships, local knowledge, and honest guidance for buyers and sellers.',
		),
		'contact' => array(
			'title'       => 'Contact Tanya',
			'description' => 'Get in touch with Tanya Barrans by email or phone, or schedule a no-pressure conversation about buying, selling, or getting to know the Renton area.',
		),
	);
}

/**
 * Look up the SEO override for whichever page is being rendered.
 *
 * @return array{title: string, description: string}|null
 */
function tanya_current_page_seo_override() {
	if ( ! is_page() ) {
		return null;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$overrides = tanya_page_seo_overrides();

	return isset( $overrides[ $post->post_name ] ) ? $overrides[ $post->post_name ] : null;
}

// Basic SEO: output a meta description per page. Prefers a curated override,
// then the page/post excerpt, and falls back to a sensible site-wide default.
add_action( 'wp_head', function () {
	$description = '';
	$override    = tanya_current_page_seo_override();

	if ( $override ) {
		$description = $override['description'];
	} elseif ( is_front_page() ) {
		$description = 'Tanya Barrans is a Puget Sound real estate broker with John L Scott, serving Renton, Kent, Covington, Maple Valley, and nearby communities with honest advice and local expertise.';
	} elseif ( is_home() ) {
		$description = 'Explore the Love Where You Live Journal for practical home guidance, Renton neighborhood stories, local recommendations, and honest real estate advice from Tanya Barrans.';
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

/**
 * Canonical URL for the posts page.
 *
 * Core's rel_canonical() only fires on is_singular(), so the Journal index —
 * a static page assigned as the posts page — shipped without a canonical at
 * all. Paged views point at themselves rather than page one, so the archive
 * pages are not treated as duplicates of each other.
 */
add_action( 'wp_head', function () {
	if ( ! is_home() || is_front_page() ) {
		return;
	}

	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( ! $posts_page_id ) {
		return;
	}

	$paged     = (int) get_query_var( 'paged' );
	$canonical = $paged > 1 ? get_pagenum_link( $paged ) : get_permalink( $posts_page_id );

	if ( $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
	}
} );

// The WordPress posts page is stored as "Blog" in the local database, but the
// public-facing publication name is the Tanya-approved Journal title. Core
// pages with a curated title use it in place of the short admin title.
add_filter( 'document_title_parts', function ( $title ) {
	if ( is_home() ) {
		$title['title'] = 'The Love Where You Live Journal';

		return $title;
	}

	$override = tanya_current_page_seo_override();
	if ( $override ) {
		$title['title'] = $override['title'];
	}

	return $title;
} );

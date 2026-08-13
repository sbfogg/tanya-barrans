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

	// Contact form script loads only on the page that carries the form.
	if ( is_page( 'contact' ) ) {
		wp_enqueue_script(
			'tanya-barrans-contact-form',
			get_theme_file_uri( 'assets/js/contact-form.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
		wp_localize_script(
			'tanya-barrans-contact-form',
			'tanyaContact',
			array(
				'endpoint' => esc_url_raw( rest_url( 'tanya/v1/contact' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

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
 * Contact form — server-side handling.
 *
 * The browser POSTs here; this endpoint validates, then files the lead in
 * Follow Up Boss using the key in wp-config.php (TANYA_FUB_API_KEY). That key
 * is admin-level on a CRM holding real client records, so it never leaves the
 * server. Guards: WP REST nonce, a honeypot, server-side validation, and
 * allow-lists on every choice field.
 *
 * Lead safety is the priority: if Follow Up Boss cannot be reached, the
 * submission is emailed to Tanya instead and the visitor is still told it went
 * through, because the lead did reach a real destination. Only if both paths
 * fail does the visitor see an error — and that error hands them the direct
 * email and phone so the enquiry is never simply swallowed.
 */
add_action( 'rest_api_init', function () {
	register_rest_route(
		'tanya/v1',
		'/contact',
		array(
			'methods'             => 'POST',
			'callback'            => 'tanya_contact_submit',
			'permission_callback' => '__return_true',
		)
	);
} );

/** Allowed values for the choice fields, mirrored in the form markup. */
function tanya_contact_choices() {
	return array(
		'interest' => array( 'Buying', 'Selling', 'Both buying and selling', 'A question about the area', 'Something else' ),
		'timing'   => array( 'As soon as possible', 'In the next 3 months', 'In the next 6-12 months', 'Just exploring for now' ),
		'contact'  => array( 'Email', 'Phone call', 'Text message' ),
	);
}

function tanya_contact_submit( WP_REST_Request $request ) {
	$fail = function ( $message, $code = 400 ) {
		return new WP_REST_Response( array( 'message' => $message ), $code );
	};

	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return $fail( 'Your session expired. Please refresh the page and try again.', 403 );
	}

	// Honeypot: bots fill hidden fields. Report success so they move on.
	if ( '' !== trim( (string) $request->get_param( 'website' ) ) ) {
		return new WP_REST_Response( array( 'message' => 'Thanks — your message is on its way to Tanya.' ), 200 );
	}

	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$phone   = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$message = sanitize_textarea_field( (string) $request->get_param( 'message' ) );

	if ( '' === $name ) {
		return $fail( 'Please add your name so Tanya knows who she is replying to.', 422 );
	}
	if ( ! $email || ! is_email( $email ) ) {
		return $fail( 'Please enter a valid email address.', 422 );
	}

	// Choice fields fall back to a neutral value rather than trusting input.
	$choices  = tanya_contact_choices();
	$pick     = function ( $value, $allowed, $default ) {
		$value = sanitize_text_field( (string) $value );
		return in_array( $value, $allowed, true ) ? $value : $default;
	};
	$interest = $pick( $request->get_param( 'interest' ), $choices['interest'], 'Not specified' );
	$timing   = $pick( $request->get_param( 'timing' ), $choices['timing'], 'Not specified' );
	$prefers  = $pick( $request->get_param( 'contact_method' ), $choices['contact'], 'Email' );

	// Follow Up Boss takes a single message body, so the structured answers are
	// summarised into it rather than lost.
	$summary = sprintf(
		"%s\n\n— Looking to: %s\n— Timing: %s\n— Prefers to be contacted by: %s\n— Phone: %s\n\nSent from the website contact form.",
		'' !== $message ? $message : '(no message provided)',
		$interest,
		$timing,
		$prefers,
		'' !== $phone ? $phone : 'not provided'
	);

	$parts      = preg_split( '/\s+/', $name, 2 );
	$first_name = $parts[0];
	$last_name  = isset( $parts[1] ) ? $parts[1] : '';

	$filed = false;

	if ( defined( 'TANYA_FUB_API_KEY' ) && TANYA_FUB_API_KEY ) {
		$person = array(
			'firstName' => $first_name,
			'emails'    => array( array( 'value' => $email ) ),
			'tags'      => array( 'Website Lead' ),
		);
		if ( '' !== $last_name ) {
			$person['lastName'] = $last_name;
		}
		if ( '' !== $phone ) {
			$person['phones'] = array( array( 'value' => $phone ) );
		}

		$response = wp_remote_post(
			'https://api.followupboss.com/v1/events',
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( TANYA_FUB_API_KEY . ':' ),
					'Content-Type'  => 'application/json',
					'X-System'      => 'TanyaBarransWebsite',
				),
				'body'    => wp_json_encode(
					array(
						'source'  => 'Tanya Barrans Website',
						'system'  => 'TanyaBarransWebsite',
						'type'    => 'Inquiry',
						'message' => $summary,
						'person'  => $person,
					)
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$code = wp_remote_retrieve_response_code( $response );
			if ( $code >= 200 && $code < 300 ) {
				$filed = true;
			} else {
				error_log( 'Follow Up Boss rejected a website lead. HTTP ' . $code );
			}
		} else {
			error_log( 'Follow Up Boss unreachable: ' . $response->get_error_message() );
		}
	}

	// Backup path so a CRM outage never costs a lead.
	if ( ! $filed ) {
		$sent = wp_mail(
			'tanya@tanyabarrans.com',
			sprintf( 'Website enquiry from %s', $name ),
			sprintf( "%s\n\nName: %s\nEmail: %s\n", $summary, $name, $email ),
			array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' )
		);
		if ( $sent ) {
			$filed = true;
			error_log( 'Website lead delivered by email fallback rather than Follow Up Boss.' );
		}
	}

	if ( ! $filed ) {
		return $fail( 'Something went wrong sending your message. Please email tanya@tanyabarrans.com or call (425) 537-4728 and it will reach her directly.', 502 );
	}

	return new WP_REST_Response(
		array( 'message' => 'Thanks — your message is on its way. Tanya usually replies within one business day.' ),
		200
	);
}

/**
 * Google Analytics 4.
 *
 * The measurement ID is not a secret — it ships in the page source of every
 * public page — so it lives here rather than in wp-config.php.
 *
 * The tag is gated on the environment type. Local and staging installs must
 * never report into Tanya's property: development traffic would land in the
 * same reports as real visitors and quietly corrupt the numbers she makes
 * decisions from. wp-config.php sets WP_ENVIRONMENT_TYPE to 'local' here,
 * and WordPress defaults to 'production' when nothing is set, so the tag
 * switches itself on when the site is deployed without further changes.
 */
/**
 * Should analytics run on this request?
 *
 * WordPress reports 'production' whenever WP_ENVIRONMENT_TYPE is unset, which
 * is the right default for a live site — analytics work on a new host with no
 * configuration at all. The same default is the trap: a staging or development
 * copy that nobody configured also calls itself production, and quietly files
 * test traffic alongside real visitors. That is close to impossible to spot
 * later, because the numbers simply look higher than they should.
 *
 * An explicit WP_ENVIRONMENT_TYPE is therefore trusted completely — hosts that
 * set it properly, including WP Engine, need no help. The hostname is only
 * consulted when nothing was set, purely to catch the forgotten-staging case.
 */
function tanya_should_load_analytics() {
	// An explicit setting is authoritative; never second-guess it.
	if ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE ) {
		return 'production' === wp_get_environment_type();
	}

	if ( 'production' !== wp_get_environment_type() ) {
		return false;
	}

	// Nothing was configured, so fall back to what the hostname suggests.
	$host = isset( $_SERVER['HTTP_HOST'] )
		? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) )
		: '';

	if ( '' === $host ) {
		return true;
	}

	foreach ( array( 'localhost', '.local', '.test', 'staging.', 'stage.', 'dev.' ) as $marker ) {
		if ( false !== strpos( $host, $marker ) ) {
			return false;
		}
	}

	return true;
}

add_action( 'wp_head', function () {
	$measurement_id = apply_filters( 'tanya_ga4_measurement_id', 'G-K9H4JX6HTY' );

	if ( ! $measurement_id || ! tanya_should_load_analytics() ) {
		return;
	}
	?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo rawurlencode( $measurement_id ); ?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', <?php echo wp_json_encode( $measurement_id ); ?>);
</script>
	<?php
}, 20 );

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

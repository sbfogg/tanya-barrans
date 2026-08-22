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
 * Send WordPress email through authenticated SMTP.
 *
 * Left alone, PHP hands mail to the web server, which sends it from a shared
 * hosting IP that is not listed in tanyabarrans.com's SPF record and cannot
 * sign it with DKIM. Google Workspace receives that domain's mail, so it sees
 * a message claiming to come from the same domain it is delivering to,
 * arriving from an unknown server — the shape of a phishing attempt. It gets
 * quarantined or dropped.
 *
 * The casualties are password resets and the contact form's fallback. Both
 * fail silently, which is the worst way to fail: someone requests a reset,
 * is told to check their email, and nothing ever arrives.
 *
 * Authenticating as a real mailbox fixes it, because the message is then sent
 * and signed by the domain's actual mail provider.
 *
 * Credentials live in wp-config.php, never in this repository. While they are
 * absent every hook here returns untouched and WordPress behaves exactly as
 * it does today, so this is safe to ship before the mailbox exists.
 */
function tanya_smtp_configured() {
	foreach ( array( 'TANYA_SMTP_HOST', 'TANYA_SMTP_USER', 'TANYA_SMTP_PASS' ) as $constant ) {
		if ( ! defined( $constant ) || ! constant( $constant ) ) {
			return false;
		}
	}
	return true;
}

function tanya_mail_from_name() {
	if ( defined( 'TANYA_SMTP_FROM_NAME' ) && TANYA_SMTP_FROM_NAME ) {
		return TANYA_SMTP_FROM_NAME;
	}
	return get_bloginfo( 'name' );
}

// Gmail refuses a From address that is not the authenticated mailbox or one of
// its verified aliases, so the sender must match the account we log in as.
add_filter( 'wp_mail_from', function ( $email ) {
	return tanya_smtp_configured() ? TANYA_SMTP_USER : $email;
} );

add_filter( 'wp_mail_from_name', function ( $name ) {
	return tanya_smtp_configured() ? tanya_mail_from_name() : $name;
} );

add_action( 'phpmailer_init', function ( $mailer ) {
	if ( ! tanya_smtp_configured() ) {
		return;
	}

	$port = defined( 'TANYA_SMTP_PORT' ) ? (int) TANYA_SMTP_PORT : 587;

	$mailer->isSMTP();
	$mailer->Host       = TANYA_SMTP_HOST;
	$mailer->Port       = $port;
	$mailer->SMTPAuth   = true;
	$mailer->Username   = TANYA_SMTP_USER;
	$mailer->Password   = TANYA_SMTP_PASS;
	// 465 is implicit TLS; 587 upgrades with STARTTLS. Both are encrypted.
	$mailer->SMTPSecure = ( 465 === $port ) ? 'ssl' : 'tls';
} );

/**
 * Record delivery failures.
 *
 * The whole problem this addresses is mail failing without anyone noticing,
 * so a failure that leaves no trace would simply recreate it in a new place.
 */
add_action( 'wp_mail_failed', function ( $error ) {
	if ( is_wp_error( $error ) ) {
		error_log( 'Website email failed to send: ' . $error->get_error_message() );
	}
} );

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
 * Is this the real, public site rather than a copy of it?
 *
 * Analytics and robots.txt both hang off this answer: the live site should
 * report traffic and invite crawlers, and every copy of it should do neither.
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
function tanya_is_public_site() {
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

	// Host-provided preview domains belong here as much as ".local" does. The
	// original list missed them, so the Hostinger staging copy called itself
	// production and filed test traffic into the real analytics property for
	// several days before anyone noticed — exactly the failure the comment
	// above warns about.
	$markers = array(
		'localhost',
		'.local',
		'.test',
		'staging.',
		'stage.',
		'dev.',
		'.hostingersite.com',
		'.wpengine.com',
		'.instawp.xyz',
		'.temp.domains',
	);

	foreach ( $markers as $marker ) {
		if ( false !== strpos( $host, $marker ) ) {
			return false;
		}
	}

	return true;
}

add_action( 'wp_head', function () {
	$measurement_id = apply_filters( 'tanya_ga4_measurement_id', 'G-K9H4JX6HTY' );

	if ( ! $measurement_id || ! tanya_is_public_site() ) {
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
		'buy'            => array(
			'title'       => 'Buying a Home in Renton and the Puget Sound',
			'description' => 'How buying a home works step by step, and a low-pressure way to explore your buying power before you talk to anyone.',
		),
		'sell'           => array(
			'title'       => 'Selling Your Home in Renton and the Puget Sound',
			'description' => 'How Tanya Barrans prepares, prices, and markets a home, plus a starting value range for yours before you commit to anything.',
		),
		'about'          => array(
			'title'       => 'About Tanya',
			'description' => 'Meet Tanya Barrans, a Renton-area broker with John L Scott who works on relationships, local knowledge, and honest guidance.',
		),
		'contact'        => array(
			'title'       => 'Contact Tanya',
			'description' => 'Reach Tanya Barrans by email or phone, or schedule a no-pressure conversation about buying, selling, or the Renton area.',
		),
		'renton'         => array(
			'title'       => 'Rooted in Renton',
			'description' => 'Local stories, neighborhood guides, and the places that make Renton worth living in, from broker Tanya Barrans.',
		),
		'neighborhoods'  => array(
			'title'       => 'Neighborhoods',
			'description' => 'Explore the neighborhoods around Renton and the Puget Sound to work out where you actually want to live.',
		),
		'privacy-policy' => array(
			'title'       => 'Privacy Policy',
			'description' => 'What this website collects, why it is collected, and how it is used, including the contact form and newsletter signup.',
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

// Basic SEO: output a meta description per page. The description itself is
// built by tanya_meta_description() so the social tags and structured data
// below describe a page the same way its meta description does.
add_action( 'wp_head', function () {
	$description = tanya_meta_description();

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

/**
 * The description used for this page, in one place.
 *
 * Prefers a curated override, then the page or post excerpt, and falls back
 * to the site tagline. The meta description tag, the Open Graph tags, and the
 * structured data all read from here so a page never describes itself three
 * different ways.
 *
 * @return string
 */
function tanya_meta_description() {
	$description = '';
	$override    = tanya_current_page_seo_override();

	if ( $override ) {
		$description = $override['description'];
	} elseif ( is_front_page() ) {
		$description = 'Tanya Barrans is a Puget Sound real estate broker with John L Scott, serving Renton, Kent, Covington, and Maple Valley.';
	} elseif ( is_home() ) {
		$description = 'Practical home guidance, Renton neighborhood stories, and local recommendations from the Love Where You Live Journal.';
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$raw = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
			$raw = strip_shortcodes( $raw );

			/*
			 * Replace every tag and block comment with a space before stripping.
			 * wp_strip_all_tags() removes them without leaving anything behind,
			 * so text either side of a block boundary welds together: a listing
			 * page described itself as "Featured Listing441 S 51st CourtRenton,
			 * WA$515,00". Block themes hit this on every page.
			 */
			$raw = preg_replace( '/<[^>]*>/', ' ', $raw );

			$description = html_entity_decode( wp_strip_all_tags( $raw ), ENT_QUOTES, 'UTF-8' );
		}
	} elseif ( is_category() || is_tag() || is_archive() ) {
		$description = trim( wp_strip_all_tags( term_description() ) );
	}

	if ( empty( $description ) ) {
		$description = get_bloginfo( 'description' );
	}

	return tanya_trim_description( $description );
}

/**
 * Business facts shared by the social tags and the structured data.
 *
 * Every value here is already published on the site — the footer disclosure,
 * the contact page, or the site description. Nothing asserts a credential,
 * an award, a transaction volume, or a rating, because structured data that
 * claims more than the page shows is worse than none at all. Review and
 * rating markup is deliberately absent: it must come from a verified source
 * rather than being hand-written.
 *
 * @return array<string, mixed>
 */
function tanya_business_facts() {
	return apply_filters(
		'tanya_business_facts',
		array(
			'name'      => 'Tanya Barrans',
			'brokerage' => 'John L Scott Real Estate',
			'telephone' => '+1-425-537-4728',
			'email'     => 'tanya@tanyabarrans.com',
			'areas'     => array( 'Renton', 'Kent', 'Covington', 'Maple Valley' ),
			'profiles'  => array(
				'https://www.facebook.com/tanyabarrans',
				'https://www.instagram.com/tanyabarrans/',
			),
		)
	);
}

/**
 * Image used for link previews.
 *
 * Prefers the post's own featured image so a shared article carries its own
 * artwork, and falls back to the Love Where You Live hero so a share never
 * renders as a bare grey link.
 *
 * @return string
 */
function tanya_social_image_url() {
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && has_post_thumbnail( $post ) ) {
			$image = get_the_post_thumbnail_url( $post, 'full' );
			if ( $image ) {
				return $image;
			}
		}
	}

	return get_theme_file_uri( 'assets/images/lwyl-hero.jpg' );
}

/**
 * Absolute URL of whatever is currently being rendered.
 *
 * Used for og:url and for the structured data identifiers, both of which need
 * the real address of this page rather than the site root.
 *
 * @return string
 */
function tanya_current_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_home() ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );

		return $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/' );
	}

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			return get_permalink( $post );
		}
	}

	global $wp;

	return isset( $wp->request ) && $wp->request
		? home_url( user_trailingslashit( $wp->request ) )
		: home_url( '/' );
}

// Open Graph and Twitter card tags. Without these, every share of an article
// to Facebook, Instagram, or Messages renders as a bare grey link with no
// image, which matters for a brand built on shareable local content.
add_action( 'wp_head', function () {
	$description = tanya_meta_description();
	$image       = tanya_social_image_url();
	$is_article  = is_singular( 'post' );

	$tags = array(
		'og:type'      => $is_article ? 'article' : 'website',
		'og:site_name' => get_bloginfo( 'name' ),
		'og:title'     => wp_get_document_title(),
		'og:url'       => tanya_current_url(),
		'og:locale'    => get_locale(),
	);

	if ( $description ) {
		$tags['og:description'] = $description;
	}

	if ( $image ) {
		$tags['og:image']     = $image;
		$tags['og:image:alt'] = $is_article
			? get_the_title( get_queried_object_id() )
			: 'Tanya Barrans, Puget Sound real estate';
	}

	if ( $is_article ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$tags['article:published_time'] = get_the_date( 'c', $post );
			$tags['article:modified_time']  = get_the_modified_date( 'c', $post );
		}
	}

	foreach ( $tags as $property => $content ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
	}

	// summary_large_image is what turns a share into a full-width photo card
	// rather than a thumbnail beside a line of text.
	$twitter = array(
		'twitter:card'  => 'summary_large_image',
		'twitter:title' => wp_get_document_title(),
	);

	if ( $description ) {
		$twitter['twitter:description'] = $description;
	}

	if ( $image ) {
		$twitter['twitter:image'] = $image;
	}

	foreach ( $twitter as $name => $content ) {
		echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
	}
}, 2 );

// Structured data. This is the main organic-search lever for a local agent:
// it is what lets Google connect the site to a named person, a phone number,
// and a service area for searches like "real estate agent Renton".
add_action( 'wp_head', function () {
	$facts    = tanya_business_facts();
	$home     = home_url( '/' );
	$agent_id = $home . '#agent';

	$areas = array();
	foreach ( $facts['areas'] as $area ) {
		$areas[] = array(
			'@type' => 'Place',
			'name'  => $area,
		);
	}

	// No postal address is published anywhere on the site. Inventing one would
	// be both false and actively harmful in local search results, so the agent
	// is described by the area it serves instead.
	$agent = array(
		'@type'      => 'RealEstateAgent',
		'@id'        => $agent_id,
		'name'       => $facts['name'],
		'url'        => $home,
		'image'      => get_theme_file_uri( 'assets/images/lwyl-hero.jpg' ),
		'logo'       => get_theme_file_uri( 'assets/images/logo-tb-black.png' ),
		'telephone'  => $facts['telephone'],
		'email'      => $facts['email'],
		'areaServed' => $areas,
		'memberOf'   => array(
			'@type' => 'Organization',
			'name'  => $facts['brokerage'],
		),
		'sameAs'     => $facts['profiles'],
	);

	$graph = array(
		$agent,
		array(
			'@type'      => 'WebSite',
			'@id'        => $home . '#website',
			'url'        => $home,
			'name'       => get_bloginfo( 'name' ),
			'publisher'  => array( '@id' => $agent_id ),
			'inLanguage' => get_bloginfo( 'language' ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$permalink = get_permalink( $post );
			$article   = array(
				'@type'            => 'BlogPosting',
				'@id'              => $permalink . '#article',
				'headline'         => get_the_title( $post ),
				'datePublished'    => get_the_date( 'c', $post ),
				'dateModified'     => get_the_modified_date( 'c', $post ),
				'mainEntityOfPage' => array( '@id' => $permalink ),
				'author'           => array( '@id' => $agent_id ),
				'publisher'        => array( '@id' => $agent_id ),
			);

			$description = tanya_meta_description();
			if ( $description ) {
				$article['description'] = $description;
			}

			$image = tanya_social_image_url();
			if ( $image ) {
				$article['image'] = $image;
			}

			$graph[] = $article;
		}
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo '<script type="application/ld+json">'
		. wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. '</script>' . "\n";
}, 3 );

// The author archive at /author/tanyabarrans/ lists the same posts as the
// Journal index with no unique content of its own, so it competes with the
// page we actually want ranked. Drop it from the submitted sitemap and mark
// it noindex; a one-author site has no use for it.
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

add_filter( 'wp_robots', function ( $robots ) {
	if ( is_author() ) {
		$robots['noindex'] = true;
	}

	return $robots;
} );

/**
 * Take control of robots.txt.
 *
 * The staging copy was serving a robots.txt that disallowed Googlebot from
 * the entire site. No such file exists in the web root, so it is injected
 * further up the stack -- which means it would have been carried onto the
 * real domain with nobody able to find the file responsible. A site nobody
 * can crawl is invisible, and the failure is completely silent.
 *
 * Filtering it here settles the question in code that lives in the repo.
 * Priority is deliberately late so this wins over whatever the host adds.
 *
 * Copies of the site are closed to everyone rather than only to Google:
 * duplicate content on a preview domain competes with the real site.
 */
add_filter( 'robots_txt', function ( $output, $public ) {
	// A site explicitly marked private in Settings keeps WordPress's answer.
	if ( ! $public ) {
		return $output;
	}

	if ( ! tanya_is_public_site() ) {
		return "User-agent: *\nDisallow: /\n";
	}

	$rules = "User-agent: *\n";
	$rules .= "Disallow: /wp-admin/\n";
	$rules .= "Allow: /wp-admin/admin-ajax.php\n";

	// Nothing here is secret, but neither belongs in search results.
	$rules .= "Disallow: /wp-json/\n";
	$rules .= "Disallow: /author/\n";
	$rules .= "\nSitemap: " . esc_url_raw( home_url( '/wp-sitemap.xml' ) ) . "\n";

	return $rules;
}, 9999, 2 );

/**
 * Record changes to settings, plugins, and the active theme.
 *
 * WordPress keeps revisions for content — pages, posts, templates, menus —
 * so anything edited in the admin already has a history with an author and a
 * timestamp against it. Options have none. `wp_options` is overwritten in
 * place, so a changed site address, a newly activated plugin, or a flipped
 * indexing setting leaves no trace whatsoever.
 *
 * That gap is not theoretical. During deployment `active_plugins` changed
 * twice in a single day and analytics silently reported a staging copy as
 * production; both were found days later, by accident, and neither could be
 * dated afterwards because nothing recorded them.
 *
 * Only options that can actually break something are watched, so the log
 * stays short enough that reading it is realistic.
 *
 * Entries go to the PHP error log, which sits outside the web root and is
 * therefore not publicly readable — unlike anything written under uploads.
 * Read them with:
 *
 *     grep tanya-audit ~/.logs/error_log_*
 *
 * @return string[]
 */
function tanya_audited_options() {
	return apply_filters(
		'tanya_audited_options',
		array(
			// Break these and the site moves, disappears from search, or dies.
			'siteurl',
			'home',
			'blog_public',
			'permalink_structure',
			'template',
			'stylesheet',
			'active_plugins',

			// Quieter, but each one has caused a real support question.
			'blogname',
			'blogdescription',
			'admin_email',
			'show_on_front',
			'page_on_front',
			'page_for_posts',
			'users_can_register',
			'default_role',
			'timezone_string',
		)
	);
}

/**
 * Render a value short enough to sit on one log line.
 *
 * Serialised arrays such as active_plugins are unreadable raw, and a long
 * value would push the useful part of the line out of view.
 *
 * @param mixed $value Value to render.
 * @return string
 */
function tanya_audit_value( $value ) {
	if ( is_array( $value ) || is_object( $value ) ) {
		$value = wp_json_encode( $value );
	}

	$value = trim( (string) $value );

	if ( '' === $value ) {
		return '(empty)';
	}

	return strlen( $value ) > 140 ? substr( $value, 0, 137 ) . '...' : $value;
}

/**
 * Write one audit line.
 *
 * The user matters as much as the change: "who moved the site address" is the
 * question being asked, and WordPress cannot answer it after the fact.
 *
 * @param string $what   Short description of what happened.
 * @param string $detail Optional before/after or identifier.
 */
function tanya_audit_log( $what, $detail = '' ) {
	$user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$who  = ( $user && $user->exists() ) ? $user->user_login : 'system/cron';

	error_log( sprintf( '[tanya-audit] %s | %s%s', $who, $what, '' !== $detail ? ' | ' . $detail : '' ) );
}

add_action(
	'updated_option',
	function ( $option, $old_value, $value ) {
		if ( ! in_array( $option, tanya_audited_options(), true ) ) {
			return;
		}

		// WordPress fires this on autosave-style rewrites where nothing moved.
		if ( $old_value === $value ) {
			return;
		}

		tanya_audit_log(
			"option changed: {$option}",
			tanya_audit_value( $old_value ) . '  ->  ' . tanya_audit_value( $value )
		);
	},
	10,
	3
);

add_action(
	'added_option',
	function ( $option, $value ) {
		if ( ! in_array( $option, tanya_audited_options(), true ) ) {
			return;
		}

		tanya_audit_log( "option added: {$option}", tanya_audit_value( $value ) );
	},
	10,
	2
);

add_action(
	'deleted_option',
	function ( $option ) {
		if ( ! in_array( $option, tanya_audited_options(), true ) ) {
			return;
		}

		tanya_audit_log( "option deleted: {$option}" );
	}
);

// Plugin and theme changes are the ones most likely to be made by someone
// following an AI's suggestion, and the ones least likely to be remembered.
add_action(
	'activated_plugin',
	function ( $plugin ) {
		tanya_audit_log( 'plugin ACTIVATED', $plugin );
	}
);

add_action(
	'deactivated_plugin',
	function ( $plugin ) {
		tanya_audit_log( 'plugin deactivated', $plugin );
	}
);

add_action(
	'switch_theme',
	function ( $new_name ) {
		tanya_audit_log( 'THEME SWITCHED', $new_name );
	}
);

/**
 * Keep a description inside the length a search engine will actually show.
 *
 * The fallback used wp_trim_words(), which counts words rather than
 * characters. Pages whose opening lines carry long tokens — a street address,
 * a run of place names — produced descriptions approaching 300 characters.
 * Google shows roughly 160, so the tail was wasted and the part worth reading
 * was pushed out of view. Trimming on a word boundary keeps it readable.
 *
 * @param string $description Raw description.
 * @param int    $limit       Maximum characters before the ellipsis.
 * @return string
 */
function tanya_trim_description( $description, $limit = 155 ) {
	$description = trim( preg_replace( '/\s+/', ' ', (string) $description ) );

	$length = function_exists( 'mb_strlen' ) ? mb_strlen( $description ) : strlen( $description );

	if ( '' === $description || $length <= $limit ) {
		return $description;
	}

	$cut = function_exists( 'mb_substr' ) ? mb_substr( $description, 0, $limit ) : substr( $description, 0, $limit );
	$gap = strrpos( $cut, ' ' );

	// Only honour the word boundary if it still leaves a usable sentence.
	if ( false !== $gap && $gap > 60 ) {
		$cut = function_exists( 'mb_substr' ) ? mb_substr( $cut, 0, $gap ) : substr( $cut, 0, $gap );
	}

	return rtrim( $cut, " ,.;:-" ) . '…';
}

/**
 * Pages that exist but are not ready to be found.
 *
 * Three different problems, one answer:
 *
 * - The area pages (Kent, Covington, Maple Valley, Newcastle) are honest
 *   stubs. Each says "the full guide is still being written" and invites a
 *   suggestion. That is a fine holding page for someone who follows a link,
 *   and poor material for a search result: four near-identical pages whose
 *   only difference is the place name reads as thin content, and it is the
 *   local searches these pages will eventually win that get devalued.
 * - Living in Renton and Resources are empty. Everything they render is
 *   header and footer.
 * - The listing template is an internal working copy, titled "LISTING
 *   TEMPLATE - Duplicate Me", which should never have been public at all.
 *
 * They stay published and reachable so work can continue and links keep
 * working. They are simply withheld from search until each is worth finding.
 * Remove a slug from this list the moment its page has real content.
 *
 * @return string[]
 */
function tanya_unfinished_pages() {
	return apply_filters(
		'tanya_unfinished_pages',
		array(
			'kent',
			'covington',
			'maple-valley',
			'newcastle',
			'living-in-renton',
			'resources',
			'listing-template-duplicate-me',
		)
	);
}

/**
 * Resolve the unfinished slugs to post IDs.
 *
 * @return int[]
 */
function tanya_unfinished_page_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids = array();

	foreach ( tanya_unfinished_pages() as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page instanceof WP_Post ) {
			$ids[] = (int) $page->ID;
		}
	}

	return $ids;
}

// Tell search engines to skip them.
add_filter(
	'wp_robots',
	function ( $robots ) {
		$slugs = tanya_unfinished_pages();

		if ( ! empty( $slugs ) && is_page( $slugs ) ) {
			$robots['noindex'] = true;
		}

		return $robots;
	}
);

// And keep them out of the sitemap, which would otherwise invite the crawl
// that the tag above is trying to prevent.
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' !== $post_type ) {
			return $args;
		}

		$ids = tanya_unfinished_page_ids();

		if ( empty( $ids ) ) {
			return $args;
		}

		$existing            = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$args['post__not_in'] = array_merge( $existing, $ids );

		return $args;
	},
	10,
	2
);

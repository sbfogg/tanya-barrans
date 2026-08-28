(function () {
	var prefersReduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	document.documentElement.classList.add( 'tb-motion-ready' );

	// Refresh always starts at the top of the page rather than restoring
	// the previous scroll position — the entrance animations (hero arrive,
	// scroll reveals, eyebrow entrances) are choreographed from the top down,
	// so landing mid-page after a reload reads as broken. Anchor links
	// (#section) are left alone so they still jump to their target.
	if ( 'scrollRestoration' in window.history ) {
		window.history.scrollRestoration = 'manual';
		if ( ! window.location.hash ) {
			window.scrollTo( 0, 0 );
		}
	}

	// Scroll reveal — content fades/slides in the first time it enters the
	// viewport, then stays visible. One-shot on purpose: toggling the reveal
	// off again at the edges caused a feedback loop (the reveal transform
	// moves the element across the observer threshold, which re-hides it,
	// which moves it back — a visible flicker/bounce at screen edges).
	var els = document.querySelectorAll( '.tb-reveal, .tb-eyebrow' );
	if ( prefersReduced || ! ( 'IntersectionObserver' in window ) ) {
		els.forEach( function ( el ) {
			el.classList.add( 'is-visible' );
		} );
	} else {
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting || entry.boundingClientRect.top < 0 ) {
						entry.target.classList.add( 'is-visible' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.15, rootMargin: '0px 0px -8% 0px' }
		);

		els.forEach( function ( el ) {
			observer.observe( el );
		} );

		// A scrollbar drag can jump completely past an element without
		// crossing an IntersectionObserver threshold. Reveal anything the
		// viewport has already reached so labels can never remain stranded.
		function revealReachedElements() {
			els.forEach( function ( el ) {
				if (
					! el.classList.contains( 'is-visible' ) &&
					el.getBoundingClientRect().top <= window.innerHeight * 0.92
				) {
					el.classList.add( 'is-visible' );
					observer.unobserve( el );
				}
			} );
		}

		window.addEventListener( 'scroll', revealReachedElements, { passive: true } );
		revealReachedElements();
	}

	// Stat count-up — .tb-stat numbers (e.g. "10+", "100%", "5★") climb
	// from 0 to their real value when the stats band scrolls into view.
	// The number is parsed from the existing markup, so editing the stat
	// in the pattern automatically updates the animation. One-shot; under
	// reduced motion (or no JS) the final value simply stays as authored.
	var stats = Array.prototype.slice.call( document.querySelectorAll( '.tb-stat' ) );
	if ( prefersReduced || stats.length === 0 || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	var COUNT_DURATION = 1800;

	function animateStat( el ) {
		var match = el.textContent.trim().match( /^(\D*)(\d+)(.*)$/ );
		if ( ! match ) {
			return;
		}
		var prefix = match[ 1 ];
		var target = parseInt( match[ 2 ], 10 );
		var suffix = match[ 3 ];
		var start = null;

		function tick( now ) {
			if ( start === null ) {
				start = now;
			}
			var p = Math.min( 1, ( now - start ) / COUNT_DURATION );
			var eased = 1 - Math.pow( 1 - p, 3 ); // ease-out cubic
			el.textContent = prefix + Math.round( eased * target ) + suffix;
			if ( p < 1 ) {
				window.requestAnimationFrame( tick );
			}
		}
		window.requestAnimationFrame( tick );
	}

	var statObserver = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					statObserver.unobserve( entry.target );
					animateStat( entry.target );
				}
			} );
		},
		{ threshold: 0.6 }
	);

	stats.forEach( function ( el ) {
		statObserver.observe( el );
	} );
})();

/* Parallax added 2026-08-19 — pans full-bleed photos gently within their own box as you
   scroll, instead of the earlier background-attachment:fixed approach (which sized against
   the viewport and cropped short sections). Amplitude is kept small so background-size:cover
   never runs out of slack and shows a gap. Skipped for reduced-motion users and on narrow
   screens, where the effect is barely visible and not worth the scroll listener. */
(function () {
	var prefersReduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var els = document.querySelectorAll( '.tb-editorial-feature__image, .tb-story-quote__image' );
	if ( prefersReduced || els.length === 0 || window.innerWidth < 783 ) {
		return;
	}

	var AMPLITUDE = 8; // max % shift off center in either direction
	var ticking = false;

	function update() {
		var vh = window.innerHeight;
		els.forEach( function ( el ) {
			var rect = el.getBoundingClientRect();
			var center = rect.top + rect.height / 2;
			var span = vh / 2 + rect.height / 2;
			var progress = span > 0 ? ( vh / 2 - center ) / span : 0;
			progress = Math.max( -1, Math.min( 1, progress ) );
			el.style.backgroundPosition = 'center ' + ( 50 + progress * AMPLITUDE ) + '%';
		} );
		ticking = false;
	}

	function onScroll() {
		if ( ! ticking ) {
			window.requestAnimationFrame( update );
			ticking = true;
		}
	}

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll );
	update();
})();

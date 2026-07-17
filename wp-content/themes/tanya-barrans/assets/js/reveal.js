(function () {
	var prefersReduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Refresh always starts at the top of the page rather than restoring
	// the previous scroll position — the entrance animations (hero arrive,
	// scroll reveals, eyebrow fills) are choreographed from the top down,
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
	var els = document.querySelectorAll( '.tb-reveal' );
	if ( prefersReduced || ! ( 'IntersectionObserver' in window ) ) {
		els.forEach( function ( el ) {
			el.classList.add( 'is-visible' );
		} );
	} else {
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
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
	}

	// The eyebrow paintbrush animation lives in paint-eyebrow.js.

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

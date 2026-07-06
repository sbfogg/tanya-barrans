(function () {
	var prefersReduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var els = document.querySelectorAll( '.tb-reveal' );

	if ( prefersReduced || ! ( 'IntersectionObserver' in window ) ) {
		els.forEach( function ( el ) {
			el.classList.add( 'is-visible' );
		} );
		return;
	}

	var observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				entry.target.classList.toggle( 'is-visible', entry.isIntersecting );
			} );
		},
		{ threshold: 0.15, rootMargin: '0px 0px -8% 0px' }
	);

	els.forEach( function ( el ) {
		observer.observe( el );
	} );
})();

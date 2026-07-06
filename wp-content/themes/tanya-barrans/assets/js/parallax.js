(function () {
	var bg = document.querySelector( '.tb-parallax-bg' );
	if ( ! bg ) {
		return;
	}
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	var factor = 0.25; // keep below the CSS buffer (35%) so no edge ever shows
	var ticking = false;

	function update() {
		bg.style.transform = 'translateY(' + window.scrollY * factor + 'px)';
		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		},
		{ passive: true }
	);

	update();
})();

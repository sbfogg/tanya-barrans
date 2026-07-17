/**
 * PaintEyebrow — progressive paintbrush animation for eyebrow labels.
 *
 * Each .tb-eyebrow label is rebuilt as an inline SVG containing, back to
 * front: the visible paint strokes, a dark copy of the phrase, and a white
 * copy of the phrase masked by duplicates of the same strokes. Strokes are
 * generated from the measured text bounds and drawn one at a time with
 * stroke-dashoffset — down, shift right, up, shift right — so at any moment
 * only the paint actually laid down so far exists. The white text mask
 * shares path geometry, dash values, and the same rAF tick as the visible
 * strokes, so letters whiten exactly along the irregular painted edge.
 *
 * The original phrase is preserved in a visually hidden span for screen
 * readers and search engines; the SVG is decorative (aria-hidden). Without
 * JavaScript the original text simply remains. Reduced motion skips the
 * animation and shows the finished painted state immediately.
 */
(function () {
	'use strict';

	var DEBUG_PAINT_EYEBROWS = false;

	var prefersReduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var SVG_NS = 'http://www.w3.org/2000/svg';
	var uid = 0;
	var recentStarts = []; // timestamps, for staggering simultaneous starts

	var defaults = {
		paintColor: '',        // resolved from --tb-paint-color / computed color
		strokeDuration: 90,    // ms spent painting each pass
		moveDuration: 20,      // ms the brush spends shifting sideways
		overlap: 0.45,         // horizontal step as a fraction of brush width
		diagonalLean: 0.15,    // rightward lean as a fraction of band height (~8deg)
		initialDelay: 700,     // ms after entering view (lets section fade land)
		staggerDelay: 120,     // ms added per eyebrow already starting
		visibleThreshold: 0.3
	};

	function randomBetween( min, max ) {
		return min + Math.random() * ( max - min );
	}

	function easeInOutQuad( p ) {
		return p < 0.5 ? 2 * p * p : 1 - Math.pow( -2 * p + 2, 2 ) / 2;
	}

	function PaintEyebrow( element, options ) {
		this.element = element;
		this.options = Object.assign( {}, defaults, options || {} );
		this.passes = [];
		this.played = false;
	}

	PaintEyebrow.prototype.init = function () {
		var self = this;
		if ( this.element.dataset.paintEyebrowInitialized === 'true' ) {
			return;
		}
		this.element.dataset.paintEyebrowInitialized = 'true';

		this.text = this.element.textContent.trim();
		if ( ! this.text ) {
			return;
		}

		document.fonts.ready.then( function () {
			self.readTypography();
			self.createSvg();
			self.measureAndGenerate();
			if ( prefersReduced ) {
				self.completeImmediately();
				return;
			}
			self.observe();
		} );
	};

	PaintEyebrow.prototype.readTypography = function () {
		var styles = window.getComputedStyle( this.element );
		this.typography = {
			fontFamily: styles.fontFamily,
			fontSize: parseFloat( styles.fontSize ),
			fontWeight: styles.fontWeight,
			letterSpacing: styles.letterSpacing === 'normal' ? '0' : styles.letterSpacing,
			color: styles.color
		};
		// SVG <text> does not reliably apply CSS text-transform; bake it in.
		if ( styles.textTransform === 'uppercase' ) {
			this.text = this.text.toUpperCase();
		} else if ( styles.textTransform === 'lowercase' ) {
			this.text = this.text.toLowerCase();
		}
		this.paintColor = this.options.paintColor ||
			styles.getPropertyValue( '--tb-paint-color' ).trim() ||
			this.typography.color;
	};

	PaintEyebrow.prototype.makeText = function ( fill ) {
		var text = document.createElementNS( SVG_NS, 'text' );
		text.textContent = this.text;
		text.setAttribute( 'x', '0' );
		text.setAttribute( 'y', '0' );
		text.setAttribute( 'fill', fill );
		text.style.fontFamily = this.typography.fontFamily;
		text.style.fontSize = this.typography.fontSize + 'px';
		text.style.fontWeight = this.typography.fontWeight;
		text.style.letterSpacing = this.typography.letterSpacing;
		return text;
	};

	PaintEyebrow.prototype.createSvg = function () {
		this.maskId = 'tb-paint-eyebrow-mask-' + ( ++uid );

		this.svg = document.createElementNS( SVG_NS, 'svg' );
		this.svg.setAttribute( 'class', 'tb-paint-eyebrow-svg' );
		this.svg.setAttribute( 'aria-hidden', 'true' );
		this.svg.setAttribute( 'focusable', 'false' );

		var defs = document.createElementNS( SVG_NS, 'defs' );
		var mask = document.createElementNS( SVG_NS, 'mask' );
		this.mask = mask;
		mask.setAttribute( 'id', this.maskId );
		mask.setAttribute( 'maskUnits', 'userSpaceOnUse' );
		this.maskRect = document.createElementNS( SVG_NS, 'rect' );
		this.maskRect.setAttribute( 'fill', 'black' );
		mask.appendChild( this.maskRect );
		this.maskStrokes = document.createElementNS( SVG_NS, 'g' );
		mask.appendChild( this.maskStrokes );
		defs.appendChild( mask );
		this.svg.appendChild( defs );

		this.visibleStrokes = document.createElementNS( SVG_NS, 'g' );
		this.svg.appendChild( this.visibleStrokes );

		this.darkText = this.makeText( this.typography.color );
		this.svg.appendChild( this.darkText );

		this.whiteText = this.makeText( 'var(--wp--preset--color--base, #fff)' );
		this.whiteText.setAttribute( 'mask', 'url(#' + this.maskId + ')' );
		this.svg.appendChild( this.whiteText );
	};

	PaintEyebrow.prototype.measureAndGenerate = function () {
		// The SVG must be rendered to measure text; insert it hidden first.
		this.svg.style.visibility = 'hidden';
		this.element.appendChild( this.svg );
		var bbox = this.darkText.getBBox();

		var fontSize = this.typography.fontSize;
		var marginY = fontSize * 0.2;
		var bandTop = bbox.y - marginY;
		var bandBottom = bbox.y + bbox.height + marginY;
		var bandH = bandBottom - bandTop;
		var brushW = bandH * 0.62;
		var lean = bandH * this.options.diagonalLean;
		var step = brushW * this.options.overlap;
		var paintLeft = bbox.x - brushW * 0.35;
		var paintRight = bbox.x + bbox.width + brushW * 0.35;

		this.generateStrokes( paintLeft, paintRight, bandTop, bandBottom, brushW, lean, step );

		// Fit the viewBox around text and paint, including cap overhang.
		var padX = brushW;
		var padY = brushW * 0.45;
		var vb = {
			x: paintLeft - padX,
			y: bandTop - padY,
			w: ( paintRight - paintLeft ) + padX * 2 + lean,
			h: bandH + padY * 2
		};
		this.svg.setAttribute( 'viewBox', vb.x + ' ' + vb.y + ' ' + vb.w + ' ' + vb.h );
		this.svg.setAttribute( 'width', vb.w );
		this.svg.setAttribute( 'height', vb.h );
		// The mask REGION defaults to -10%..110% of the viewport, which
		// clips content at negative user-space coordinates (our text
		// baseline sits at y=0, so ascenders are negative). Without an
		// explicit region the white text gets masked away and painted
		// letters vanish blush-on-blush. Size the region to the viewBox.
		this.mask.setAttribute( 'x', vb.x );
		this.mask.setAttribute( 'y', vb.y );
		this.mask.setAttribute( 'width', vb.w );
		this.mask.setAttribute( 'height', vb.h );
		this.maskRect.setAttribute( 'x', vb.x );
		this.maskRect.setAttribute( 'y', vb.y );
		this.maskRect.setAttribute( 'width', vb.w );
		this.maskRect.setAttribute( 'height', vb.h );

		if ( DEBUG_PAINT_EYEBROWS ) {
			this.debugDraw( bbox, bandTop, bandBottom, paintLeft, paintRight );
			console.log( '[PaintEyebrow]', this.text, this.passes.length + ' passes' );
		}

		// Swap: keep the phrase for assistive tech, hide the redundant HTML
		// text, and show the SVG — same frame, so nothing flashes.
		var sr = document.createElement( 'span' );
		sr.className = 'tb-sr-only';
		sr.textContent = this.element.childNodes[0] ? this.element.childNodes[0].textContent : this.text;
		while ( this.element.firstChild && this.element.firstChild !== this.svg ) {
			this.element.removeChild( this.element.firstChild );
		}
		this.element.insertBefore( sr, this.svg );
		this.svg.style.visibility = '';
	};

	PaintEyebrow.prototype.makeStrokePath = function ( sx, sy, ex, ey, width, opacity, isMask ) {
		var midX = ( sx + ex ) / 2 + randomBetween( -2, 2 );
		var midY = ( sy + ey ) / 2;
		var path = document.createElementNS( SVG_NS, 'path' );
		path.setAttribute( 'd', 'M' + sx.toFixed( 1 ) + ' ' + sy.toFixed( 1 ) +
			' Q' + midX.toFixed( 1 ) + ' ' + midY.toFixed( 1 ) +
			' ' + ex.toFixed( 1 ) + ' ' + ey.toFixed( 1 ) );
		path.setAttribute( 'fill', 'none' );
		path.setAttribute( 'stroke-linecap', 'round' );
		path.setAttribute( 'stroke-width', width.toFixed( 1 ) );
		if ( isMask ) {
			// Mask paths are luminance-white so any covered pixel whitens text.
			path.setAttribute( 'stroke', '#fff' );
		} else {
			path.setAttribute( 'class', 'tb-paint-eyebrow-stroke' );
			path.setAttribute( 'stroke-opacity', opacity.toFixed( 2 ) );
		}
		return path;
	};

	PaintEyebrow.prototype.generateStrokes = function ( paintLeft, paintRight, bandTop, bandBottom, brushW, lean, step ) {
		var topY = bandTop + brushW * 0.4;
		var bottomY = bandBottom - brushW * 0.4;
		var x = paintLeft;
		var index = 0;
		var schedule = 0;
		var strokeDur = this.options.strokeDuration;
		var moveDur = this.options.moveDuration;

		while ( x <= paintRight ) {
			var paintsDown = index % 2 === 0;
			var topX = x + randomBetween( -2, 2 );
			var bottomX = x + lean + randomBetween( -2, 2 );
			var t = topY + randomBetween( -2, 2 );
			var b = bottomY + randomBetween( -2, 2 );
			var sx = paintsDown ? topX : bottomX;
			var sy = paintsDown ? t : b;
			var ex = paintsDown ? bottomX : topX;
			var ey = paintsDown ? b : t;

			var pass = {
				start: schedule,
				dur: strokeDur * randomBetween( 0.9, 1.1 ),
				paths: []
			};

			// Primary bristle plus one lighter companion per pass.
			var widths = [ brushW * randomBetween( 0.92, 1.08 ), brushW * randomBetween( 0.4, 0.55 ) ];
			var opacities = [ randomBetween( 0.82, 0.95 ), randomBetween( 0.3, 0.45 ) ];
			var offsets = [ 0, brushW * randomBetween( -0.22, 0.22 ) ];
			for ( var i = 0; i < widths.length; i++ ) {
				var vis = this.makeStrokePath( sx + offsets[ i ], sy + randomBetween( -2, 2 ), ex + offsets[ i ], ey + randomBetween( -2, 2 ), widths[ i ], opacities[ i ], false );
				var msk = this.makeStrokePath( sx + offsets[ i ], sy, ex + offsets[ i ], ey, widths[ i ], 1, true );
				this.visibleStrokes.appendChild( vis );
				this.maskStrokes.appendChild( msk );
				var len = vis.getTotalLength() || 1;
				[ vis, msk ].forEach( function ( p ) {
					p.setAttribute( 'stroke-dasharray', len );
					p.setAttribute( 'stroke-dashoffset', len );
					// Round line-caps render a visible dot even at full
					// dashoffset (a zero-length dash boundary sits exactly
					// at the path start), which sprinkles paint specks over
					// the words before the animation begins. Keep every
					// stroke hidden until its own pass starts painting.
					p.style.visibility = 'hidden';
				} );
				pass.paths.push( { vis: vis, msk: msk, len: len } );
			}

			this.passes.push( pass );
			schedule += pass.dur + moveDur;
			x += step + randomBetween( -1.5, 1.5 );
			index++;
		}
		this.totalDuration = schedule;
	};

	PaintEyebrow.prototype.setPassProgress = function ( pass, p ) {
		var started = p > 0;
		var eased = easeInOutQuad( Math.max( 0, Math.min( 1, p ) ) );
		pass.paths.forEach( function ( pair ) {
			var offset = ( pair.len * ( 1 - eased ) ).toFixed( 2 );
			pair.vis.style.visibility = started ? '' : 'hidden';
			pair.msk.style.visibility = started ? '' : 'hidden';
			pair.vis.setAttribute( 'stroke-dashoffset', offset );
			pair.msk.setAttribute( 'stroke-dashoffset', offset );
		} );
	};

	PaintEyebrow.prototype.play = function () {
		if ( this.played ) {
			return;
		}
		this.played = true;
		var self = this;
		var startTime = null;

		function tick( now ) {
			if ( startTime === null ) {
				startTime = now;
			}
			var t = now - startTime;
			var done = true;
			self.passes.forEach( function ( pass ) {
				var p = ( t - pass.start ) / pass.dur;
				if ( p < 1 ) {
					done = false;
				}
				self.setPassProgress( pass, p );
			} );
			if ( ! done ) {
				window.requestAnimationFrame( tick );
			}
		}
		window.requestAnimationFrame( tick );
	};

	PaintEyebrow.prototype.completeImmediately = function () {
		this.played = true;
		var self = this;
		this.passes.forEach( function ( pass ) {
			self.setPassProgress( pass, 1 );
		} );
	};

	PaintEyebrow.prototype.observe = function () {
		var self = this;
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting || self.played ) {
						return;
					}
					observer.unobserve( entry.target );
					// Stagger eyebrows that come into view together.
					var now = Date.now();
					recentStarts = recentStarts.filter( function ( ts ) {
						return now - ts < 250;
					} );
					var delay = self.options.initialDelay + recentStarts.length * self.options.staggerDelay;
					recentStarts.push( now );
					window.setTimeout( function () {
						self.play();
					}, delay );
				} );
			},
			{ threshold: this.options.visibleThreshold }
		);
		observer.observe( this.element );
	};

	PaintEyebrow.prototype.debugDraw = function ( bbox, bandTop, bandBottom, paintLeft, paintRight ) {
		var rect = document.createElementNS( SVG_NS, 'rect' );
		rect.setAttribute( 'x', bbox.x );
		rect.setAttribute( 'y', bbox.y );
		rect.setAttribute( 'width', bbox.width );
		rect.setAttribute( 'height', bbox.height );
		rect.setAttribute( 'fill', 'none' );
		rect.setAttribute( 'stroke', 'red' );
		this.svg.appendChild( rect );
		var band = document.createElementNS( SVG_NS, 'rect' );
		band.setAttribute( 'x', paintLeft );
		band.setAttribute( 'y', bandTop );
		band.setAttribute( 'width', paintRight - paintLeft );
		band.setAttribute( 'height', bandBottom - bandTop );
		band.setAttribute( 'fill', 'none' );
		band.setAttribute( 'stroke', 'blue' );
		this.svg.appendChild( band );
	};

	function initializePaintEyebrows( root ) {
		var scope = root || document;
		var els = scope.querySelectorAll( '[data-paint-eyebrow], .tb-eyebrow' );
		els.forEach( function ( el ) {
			// Only true section eyebrows get painted. Skip navigation-style
			// labels (footer column headings) and anything containing links
			// (blog-card category tags) — rebuilding those as SVG would
			// break the links and misuse the effect on non-eyebrow text.
			if ( ! el.hasAttribute( 'data-paint-eyebrow' ) ) {
				if ( el.closest( 'footer' ) || el.querySelector( 'a' ) ) {
					return;
				}
			}
			new PaintEyebrow( el ).init();
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initializePaintEyebrows();
		} );
	} else {
		initializePaintEyebrows();
	}

	// Safe re-init hook for dynamically added content.
	window.tbInitializePaintEyebrows = initializePaintEyebrows;
})();

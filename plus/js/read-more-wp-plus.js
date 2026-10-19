/**
 * Read More WP Plus: animations, pop-ups, fade-out mode, and scroll-back.
 */
( function () {
	'use strict';

	var rmwp = window.rmwp;

	if ( ! rmwp || ! rmwp.registerAnimation ) {
		return;
	}

	function prefersReducedMotion() {
		return !! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );
	}

	/**
	 * Run a Web Animations API animation, then apply the final state and call done.
	 */
	function run( el, keyframes, speed, done, finish ) {
		finish = finish || function () {};

		if ( ! el.animate || speed <= 0 ) {
			finish();
			done();
			return;
		}

		var animation = el.animate( keyframes, { duration: speed, easing: 'ease', fill: 'forwards' } );

		animation.onfinish = function () {
			finish();
			animation.cancel();
			done();
		};
	}

	// Transforms and heights do not apply to inline elements, so inline toggles fade instead.
	function isInline( el ) {
		return el.tagName === 'SPAN';
	}

	var fade = {
		show: function ( el, speed, done ) {
			rmwp.setVisible( el, true );
			run( el, [ { opacity: 0 }, { opacity: 1 } ], speed, done );
		},
		hide: function ( el, speed, done ) {
			run( el, [ { opacity: 1 }, { opacity: 0 } ], speed, done, function () {
				rmwp.setVisible( el, false );
			} );
		},
	};

	rmwp.registerAnimation( 'fade', fade );

	rmwp.registerAnimation( 'slide', {
		show: function ( el, speed, done ) {
			if ( isInline( el ) ) {
				return fade.show( el, speed, done );
			}
			rmwp.setVisible( el, true );
			el.style.overflow = 'hidden';
			run( el, [ { height: '0px' }, { height: el.scrollHeight + 'px' } ], speed, done, function () {
				el.style.overflow = '';
			} );
		},
		hide: function ( el, speed, done ) {
			if ( isInline( el ) ) {
				return fade.hide( el, speed, done );
			}
			el.style.overflow = 'hidden';
			run( el, [ { height: el.offsetHeight + 'px' }, { height: '0px' } ], speed, done, function () {
				rmwp.setVisible( el, false );
				el.style.overflow = '';
			} );
		},
	} );

	rmwp.registerAnimation( 'fold', {
		show: function ( el, speed, done ) {
			if ( isInline( el ) ) {
				return fade.show( el, speed, done );
			}
			rmwp.setVisible( el, true );
			el.style.transformOrigin = 'top left';
			run( el, [ { transform: 'scale(0)', opacity: 0 }, { transform: 'scale(1)', opacity: 1 } ], speed, done );
		},
		hide: function ( el, speed, done ) {
			if ( isInline( el ) ) {
				return fade.hide( el, speed, done );
			}
			el.style.transformOrigin = 'top left';
			run( el, [ { transform: 'scale(1)', opacity: 1 }, { transform: 'scale(0)', opacity: 0 } ], speed, done, function () {
				rmwp.setVisible( el, false );
			} );
		},
	} );

	/**
	 * Pop-up: show the hidden content in a modal dialog. The native <dialog> element
	 * traps focus, closes with the Escape key, and makes the rest of the page inert.
	 */
	// The open dialog for each button wrapper.
	var dialogs = typeof WeakMap === 'function' ? new WeakMap() : null;

	rmwp.registerMode( 'animation-popUp', {
		expand: function ( t, done ) {
			var dialog = document.createElement( 'dialog' );
			var inner = document.createElement( 'div' );
			var content = document.createElement( 'div' );

			dialog.className = 'rmwp-dialog';
			inner.className = 'rmwp-dialog-inner';
			content.className = 'rmwp-dialog-content';

			while ( t.toggle.firstChild ) {
				content.appendChild( t.toggle.firstChild );
			}

			rmwp.setState( t, true );
			rmwp.setVisible( t.ellipsis, false );

			inner.appendChild( t.wrap );
			inner.appendChild( content );
			dialog.appendChild( inner );
			document.body.appendChild( dialog );

			var opening = true;
			var closeRequested = false;

			// Close on Escape or a backdrop click. If the pop-up is still opening, close it when it finishes.
			var closeOptions = null;
			var requestClose = function ( options ) {
				if ( opening ) {
					closeRequested = true;
					closeOptions = options;
				} else {
					rmwp.toggle( t.wrap, null, options );
				}
			};

			dialog.addEventListener( 'cancel', function ( event ) {
				event.preventDefault();
				requestClose( { restoreFocus: true } );
			} );

			dialog.addEventListener( 'click', function ( event ) {
				if ( event.target === dialog ) {
					requestClose( { restoreFocus: event.detail === 0 } );
				}
			} );

			if ( dialogs ) {
				dialogs.set( t.wrap, dialog );
			}

			if ( dialog.showModal ) {
				dialog.showModal();
			} else {
				dialog.setAttribute( 'open', '' );
			}

			t.button.focus();

			run( dialog, [ { opacity: 0, transform: 'translateY(-1rem)' }, { opacity: 1, transform: 'none' } ], t.speed, function () {
				opening = false;
				done();
				if ( closeRequested ) {
					rmwp.toggle( t.wrap, null, closeOptions );
				}
			} );
		},
		collapse: function ( t, done ) {
			var dialog = dialogs ? dialogs.get( t.wrap ) : null;

			if ( ! dialog ) {
				done();
				return;
			}

			run( dialog, [ { opacity: 1 }, { opacity: 0 } ], t.speed, done, function () {
				var content = dialog.querySelector( '.rmwp-dialog-content' );

				while ( content && content.firstChild ) {
					t.toggle.appendChild( content.firstChild );
				}

				rmwp.setState( t, false );
				rmwp.setVisible( t.ellipsis, true );
				rmwp.moveButton( t, false );

				if ( dialog.close ) {
					dialog.close();
				}
				dialog.parentNode.removeChild( dialog );
				dialogs.delete( t.wrap );

				// Return focus to the button for keyboard users (see moveButton() in the main script).
				if ( t.restoreFocus !== false ) {
					t.button.focus();
				}
			} );
		},
	} );

	/**
	 * Fade-out mode: show the start of the content, faded out, with a button to show the rest.
	 */
	function getFadeHeight( t ) {
		var height = parseInt( window.getComputedStyle( t.toggle ).getPropertyValue( '--rmwp-fade-height' ), 10 );
		return isNaN( height ) ? 200 : height;
	}

	// Only offer the button when the content is taller than the visible height.
	function checkOverflow( t ) {
		if ( t.toggle.classList.contains( 'open' ) ) {
			return;
		}
		var overflowing = t.toggle.scrollHeight > getFadeHeight( t ) + 1;
		t.toggle.classList.toggle( 'rmwp-collapsed', overflowing );
		rmwp.setVisible( t.wrap, overflowing );
	}

	var fadeToggles = [];

	rmwp.registerMode( 'fade', {
		init: function ( t ) {
			fadeToggles.push( t );
			checkOverflow( t );
		},
		expand: function ( t, done ) {
			var from = t.toggle.clientHeight;
			var to = t.toggle.scrollHeight;

			rmwp.setState( t, true );
			t.toggle.classList.remove( 'rmwp-collapsed' );

			run( t.toggle, [ { maxHeight: from + 'px' }, { maxHeight: to + 'px' } ], t.speed, done, function () {
				t.toggle.style.maxHeight = 'none';
				t.toggle.style.overflow = '';
			} );
		},
		collapse: function ( t, done ) {
			var from = t.toggle.scrollHeight;
			var to = getFadeHeight( t );

			t.toggle.style.overflow = 'hidden';

			run( t.toggle, [ { maxHeight: from + 'px' }, { maxHeight: to + 'px' } ], t.speed, done, function () {
				t.toggle.style.maxHeight = to + 'px';
				t.toggle.classList.add( 'rmwp-collapsed' );
				rmwp.setState( t, false );
			} );
		},
	} );

	// Images and fonts can change the content height after the page loads.
	window.addEventListener( 'load', function () {
		fadeToggles.forEach( checkOverflow );
	} );

	/**
	 * Scroll back: after collapsing long content, bring the button back into view.
	 */
	document.addEventListener( 'rmwp:collapsed', function ( event ) {
		var button = event.detail && event.detail.button;

		if ( ! button || button.getAttribute( 'data-scroll' ) !== '1' ) {
			return;
		}

		var rect = button.getBoundingClientRect();

		if ( rect.top < 0 || rect.bottom > window.innerHeight ) {
			button.scrollIntoView( { block: 'center', behavior: prefersReducedMotion() ? 'auto' : 'smooth' } );
		}
	} );
} )();

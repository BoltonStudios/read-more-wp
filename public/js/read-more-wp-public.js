/**
 * Read More WP front-end behavior.
 *
 * Toggles are rendered by the [start-read-more] shortcode or the Read More block.
 * Extensions (such as Read More WP Plus) can register animations and display modes
 * through window.rmwp.registerAnimation() and window.rmwp.registerMode().
 *
 * Events dispatched on the toggle element: "rmwp:expanded" and "rmwp:collapsed".
 */
( function () {
	'use strict';

	var rmwp = window.rmwp = window.rmwp || {};
	var animations = {};
	var modes = {};
	var busy = {};

	/**
	 * Show or hide an element. Also clears inline "display: none" used by markup from earlier versions.
	 */
	function setVisible( el, visible ) {
		if ( ! el ) {
			return;
		}
		el.hidden = ! visible;
		if ( visible && el.style.display === 'none' ) {
			el.style.display = '';
		}
	}

	function prefersReducedMotion() {
		return !! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );
	}

	/**
	 * Collect the elements and settings for a toggle.
	 */
	function getToggle( id, fallback ) {
		var toggle = document.getElementById( 'rmwp-toggle-' + id );
		var wrap = document.getElementById( 'rmwp-button-wrap-' + id );

		if ( ! toggle || ! wrap ) {
			return null;
		}

		var button = wrap.querySelector( 'button' );
		var end = document.getElementById( 'rmwp-toggle-end-' + id );

		// Markup from versions before 1.2.0 has no ID on the end element.
		if ( ! end ) {
			var anchor = toggle.parentElement && toggle.parentElement.classList.contains( 'rmwp-animation-popUp-overlay' ) ? toggle.parentElement : toggle;
			end = anchor.nextElementSibling;
			while ( end && ! end.classList.contains( 'rmwp-toggle-end' ) ) {
				end = end.nextElementSibling;
			}
		}

		var animation = 'none';
		toggle.className.split( /\s+/ ).forEach( function ( name ) {
			if ( name.indexOf( 'animation-' ) === 0 && name.length > 10 ) {
				animation = name.slice( 10 );
			}
		} );

		fallback = fallback || {};

		var speed = parseInt( button.getAttribute( 'data-speed' ), 10 );
		if ( isNaN( speed ) ) {
			speed = parseInt( fallback.speed, 10 );
		}
		if ( isNaN( speed ) ) {
			speed = 500;
		}

		return {
			id: id,
			toggle: toggle,
			wrap: wrap,
			button: button,
			end: end,
			ellipsis: document.getElementById( 'ellipsis-' + id ),
			more: button.getAttribute( 'data-more' ) || fallback.more || button.textContent,
			less: button.getAttribute( 'data-less' ) || fallback.less || 'Read Less',
			mode: toggle.getAttribute( 'data-rmwp-mode' ) || 'toggle',
			animation: animation,
			speed: prefersReducedMotion() ? 0 : speed,
			scroll: button.getAttribute( 'data-scroll' ) === '1',
		};
	}

	/**
	 * Update the classes, ARIA state, and label for the open or closed state.
	 */
	function setState( t, open ) {
		t.toggle.classList.toggle( 'open', open );
		t.button.classList.toggle( 'open', open );
		t.button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		t.button.textContent = open ? t.less : t.more;
	}

	/**
	 * Move the button to the end of the content (open) or back to the break point (closed),
	 * keeping keyboard focus on it.
	 */
	function moveButton( t, toEnd ) {
		var hadFocus = document.activeElement === t.button;

		if ( toEnd && t.end ) {
			t.end.appendChild( t.wrap );
		} else if ( ! toEnd && t.ellipsis && t.ellipsis.parentNode ) {
			t.ellipsis.parentNode.insertBefore( t.wrap, t.ellipsis.nextSibling );
		}

		if ( hadFocus ) {
			t.button.focus( { preventScroll: true } );
		}
	}

	/**
	 * Show or hide the content with the toggle's animation.
	 */
	function animate( t, show, done ) {
		var animation = ( t.speed > 0 && animations[ t.animation ] ) ? animations[ t.animation ] : animations.none;
		animation[ show ? 'show' : 'hide' ]( t.toggle, t.speed, done || function () {} );
	}

	function dispatch( t, name ) {
		var event;
		try {
			event = new CustomEvent( name, { bubbles: true, detail: { id: t.id, button: t.button } } );
		} catch ( e ) {
			event = document.createEvent( 'CustomEvent' );
			event.initCustomEvent( name, true, false, { id: t.id, button: t.button } );
		}
		t.toggle.dispatchEvent( event );
	}

	// Show and hide instantly.
	animations.none = {
		show: function ( el, speed, done ) {
			setVisible( el, true );
			done();
		},
		hide: function ( el, speed, done ) {
			setVisible( el, false );
			done();
		},
	};

	// The default mode: hide the content after the break point.
	modes.toggle = {
		init: function ( t ) {
			setVisible( t.wrap, true );
		},
		expand: function ( t, done ) {
			setState( t, true );
			setVisible( t.ellipsis, false );
			moveButton( t, true );
			animate( t, true, done );
		},
		collapse: function ( t, done ) {
			animate( t, false, function () {
				setState( t, false );
				setVisible( t.ellipsis, true );
				moveButton( t, false );
				done();
			} );
		},
	};

	function getMode( t ) {
		if ( t.mode !== 'toggle' && modes[ t.mode ] ) {
			return modes[ t.mode ];
		}
		// Animations can provide their own mode, e.g. the Plus pop-up.
		if ( modes[ 'animation-' + t.animation ] ) {
			return modes[ 'animation-' + t.animation ];
		}
		return modes.toggle;
	}

	/**
	 * Expand or collapse a toggle.
	 */
	function toggle( id, fallback ) {
		var t = getToggle( id, fallback );

		if ( ! t || busy[ id ] ) {
			return;
		}

		var opening = ! t.toggle.classList.contains( 'open' );
		var mode = getMode( t );

		busy[ id ] = true;

		mode[ opening ? 'expand' : 'collapse' ]( t, function () {
			busy[ id ] = false;
			dispatch( t, opening ? 'rmwp:expanded' : 'rmwp:collapsed' );
		} );
	}

	function init() {
		var buttons = document.querySelectorAll( '.rmwp-button-wrap button' );

		Array.prototype.forEach.call( buttons, function ( button ) {
			var wrap = button.parentNode;
			var id = button.getAttribute( 'data-rmwp-id' ) || ( wrap.id || '' ).replace( 'rmwp-button-wrap-', '' );
			var t = getToggle( id );

			if ( t && ! t.wrap.getAttribute( 'data-rmwp-ready' ) ) {
				t.wrap.setAttribute( 'data-rmwp-ready', '1' );
				var mode = getMode( t );
				( mode.init || modes.toggle.init )( t );
			}
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '.rmwp-button-wrap button[data-rmwp-id]' ) : null;

		if ( button ) {
			event.preventDefault();
			toggle( button.getAttribute( 'data-rmwp-id' ) );
		}
	} );

	rmwp.toggle = toggle;
	rmwp.init = init;
	rmwp.setState = setState;
	rmwp.setVisible = setVisible;
	rmwp.moveButton = moveButton;
	rmwp.animate = animate;
	rmwp.registerAnimation = function ( name, animation ) {
		animations[ name ] = animation;
	};
	rmwp.registerMode = function ( name, mode ) {
		modes[ name ] = mode;
	};

	// Support inline onclick handlers in pages cached before version 1.2.0.
	window.rmwpButtonAction = function ( id, more, less ) {
		toggle( id, { more: more, less: less } );
	};
	window.rmwpPlusButtonAction = function ( id, more, less, speed ) {
		toggle( id, { more: more, less: less, speed: speed } );
	};

	// Wait for extensions (loaded after this script) to register before initializing.
	if ( document.readyState === 'complete' ) {
		setTimeout( init, 0 );
	} else {
		window.addEventListener( 'load', init );
		document.addEventListener( 'DOMContentLoaded', function () {
			setTimeout( init, 0 );
		} );
	}
} )();

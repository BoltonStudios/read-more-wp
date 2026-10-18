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

	// The elements that belong to each button wrapper, found once and then remembered.
	var parts = typeof WeakMap === 'function' ? new WeakMap() : null;

	function byId( id ) {
		return document.querySelectorAll( '[id="' + id + '"]' );
	}

	// The first element with this ID after (or the last one before) a node. Page builders and
	// sliders sometimes repeat the same markup, so IDs are not always unique on the page.
	function nearest( node, id, after ) {
		var list = byId( id );
		var flag = after ? 4 : 2; // Node.DOCUMENT_POSITION_FOLLOWING : Node.DOCUMENT_POSITION_PRECEDING

		if ( after ) {
			for ( var i = 0; i < list.length; i++ ) {
				if ( node.compareDocumentPosition( list[ i ] ) & flag ) {
					return list[ i ];
				}
			}
		} else {
			for ( var j = list.length - 1; j >= 0; j-- ) {
				if ( node.compareDocumentPosition( list[ j ] ) & flag ) {
					return list[ j ];
				}
			}
		}

		return null;
	}

	/**
	 * Find the content, ellipsis, and end marker that belong to a button wrapper.
	 */
	function resolveParts( wrap, id ) {
		var cached = parts && parts.get( wrap );

		if ( cached ) {
			return cached;
		}

		var found = {};
		var container = wrap.parentElement;

		if ( container && container.id === 'rmwp-toggle-end-' + id ) {

			// Fade mode: the button lives in the end marker, after the content.
			found.end = container;
			found.toggle = nearest( container, 'rmwp-toggle-' + id, false );
		} else {
			found.toggle = nearest( wrap, 'rmwp-toggle-' + id, true );
			found.end = found.toggle ? nearest( found.toggle, 'rmwp-toggle-end-' + id, true ) : null;

			// Markup from versions before 1.2.0 has no ID on the end element.
			if ( found.toggle && ! found.end ) {
				var anchor = found.toggle.parentElement && found.toggle.parentElement.classList.contains( 'rmwp-animation-popUp-overlay' ) ? found.toggle.parentElement : found.toggle;
				var next = anchor.nextElementSibling;
				while ( next && ! next.classList.contains( 'rmwp-toggle-end' ) ) {
					next = next.nextElementSibling;
				}
				found.end = next;
			}
		}

		var previous = wrap.previousElementSibling;
		found.ellipsis = previous && previous.id === 'ellipsis-' + id ? previous : nearest( wrap, 'ellipsis-' + id, false );

		if ( parts && found.toggle ) {
			parts.set( wrap, found );
		}

		return found;
	}

	/**
	 * Collect the elements and settings for a toggle.
	 *
	 * @param {Element|string} target   A button, a button wrapper, or a toggle ID.
	 * @param {Object}         fallback Labels and speed from inline onclick handlers (markup before 1.2.0).
	 */
	function getToggle( target, fallback ) {
		var wrap = null;

		if ( typeof target === 'string' ) {
			wrap = document.getElementById( 'rmwp-button-wrap-' + target );
		} else if ( target && target.closest ) {
			wrap = target.closest( '.rmwp-button-wrap' );
		}

		if ( ! wrap ) {
			return null;
		}

		var button = wrap.querySelector( 'button' );
		var id = ( button && button.getAttribute( 'data-rmwp-id' ) ) || ( wrap.id || '' ).replace( 'rmwp-button-wrap-', '' );
		var found = resolveParts( wrap, id );
		var toggle = found.toggle;
		var end = found.end;

		if ( ! toggle || ! button ) {
			return null;
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
			ellipsis: found.ellipsis,
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

	// Whether the end marker comes right after the content (ignoring whitespace).
	function followsContent( t ) {
		var anchor = t.toggle.parentElement && t.toggle.parentElement.classList.contains( 'rmwp-animation-popUp-overlay' ) ? t.toggle.parentElement : t.toggle;
		return anchor.nextElementSibling === t.end;
	}

	/**
	 * Move the button to the end of the content (open) or back to the break point (closed),
	 * keeping keyboard focus on it.
	 */
	function moveButton( t, toEnd ) {
		var hadFocus = document.activeElement === t.button;

		if ( toEnd && t.end && followsContent( t ) ) {
			t.end.appendChild( t.wrap );
		} else if ( toEnd ) {

			// The browser moved the end marker away from the content (e.g., an inline toggle
			// spanning several paragraphs), so show the button right after the revealed content.
			t.toggle.parentNode.insertBefore( t.wrap, t.toggle.nextSibling );
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
	 *
	 * @param {Element|string} target   The clicked button (or its wrapper), or a toggle ID.
	 * @param {Object}         fallback Labels and speed from inline onclick handlers (markup before 1.2.0).
	 */
	function toggle( target, fallback ) {
		var t = getToggle( target, fallback );

		if ( ! t || t.wrap.rmwpBusy ) {
			return;
		}

		var opening = ! t.toggle.classList.contains( 'open' );
		var mode = getMode( t );

		t.wrap.rmwpBusy = true;

		mode[ opening ? 'expand' : 'collapse' ]( t, function () {
			t.wrap.rmwpBusy = false;
			dispatch( t, opening ? 'rmwp:expanded' : 'rmwp:collapsed' );
		} );
	}

	function init() {
		var buttons = document.querySelectorAll( '.rmwp-button-wrap button' );

		Array.prototype.forEach.call( buttons, function ( button ) {
			var t = getToggle( button );

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
			toggle( button );
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
	function legacyTarget( id ) {
		var target = window.event && window.event.target;
		return target && target.closest && target.closest( '.rmwp-button-wrap' ) ? target : id;
	}
	window.rmwpButtonAction = function ( id, more, less ) {
		toggle( legacyTarget( id ), { more: more, less: less } );
	};
	window.rmwpPlusButtonAction = function ( id, more, less, speed ) {
		toggle( legacyTarget( id ), { more: more, less: less, speed: speed } );
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

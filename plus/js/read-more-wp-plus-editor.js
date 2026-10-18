/**
 * Read More WP Plus: block editor controls for the Read More block.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.hooks || ! wp.blockEditor || ! wp.components ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var settings = window.rmwpBlockSettings || {};

	var common = { __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };

	function control( Component, props ) {
		return el( Component, Object.assign( {}, common, props ) );
	}

	function PlusControls( props ) {
		var a = props.attributes;
		var set = props.setAttributes;
		var isFade = a.mode === 'fade';

		var animationOptions = [
			{ value: '', label: __( 'Default (from Settings)', 'read-more-wp' ) },
			{ value: 'none', label: __( 'None', 'read-more-wp' ) },
			{ value: 'slide', label: __( 'Slide', 'read-more-wp' ) },
			{ value: 'fade', label: __( 'Fade', 'read-more-wp' ) },
			{ value: 'fold', label: __( 'Fold', 'read-more-wp' ) },
		];

		if ( ! isFade ) {
			animationOptions.push( { value: 'popUp', label: __( 'Pop-up', 'read-more-wp' ) } );
		}

		var behavior = el(
			PanelBody,
			{ title: __( 'Display (Plus)', 'read-more-wp' ), initialOpen: true },
			control( SelectControl, {
				label: __( 'Mode', 'read-more-wp' ),
				value: a.mode,
				options: [
					{ value: '', label: __( 'Hide the content', 'read-more-wp' ) },
					{ value: 'fade', label: __( 'Show the start, faded out', 'read-more-wp' ) },
				],
				onChange: function ( value ) {
					set( { mode: value, animation: value === 'fade' && a.animation === 'popUp' ? '' : a.animation } );
				},
			} ),
			isFade &&
				control( RangeControl, {
					label: __( 'Visible height (px)', 'read-more-wp' ),
					value: a.fadeHeight,
					min: 50,
					max: 1000,
					step: 10,
					allowReset: true,
					initialPosition: settings.fadeHeight || 200,
					onChange: function ( value ) {
						set( { fadeHeight: value } );
					},
				} ),
			control( SelectControl, {
				label: __( 'Animation', 'read-more-wp' ),
				value: a.animation,
				options: animationOptions,
				onChange: function ( value ) {
					set( { animation: value } );
				},
			} ),
			control( RangeControl, {
				label: __( 'Animation speed (ms)', 'read-more-wp' ),
				value: a.speed,
				min: 0,
				max: 3000,
				step: 50,
				allowReset: true,
				initialPosition: settings.speed || 500,
				onChange: function ( value ) {
					set( { speed: value } );
				},
			} ),
			control( ToggleControl, {
				label: __( 'Scroll back to the button after hiding', 'read-more-wp' ),
				checked: typeof a.scrollBack === 'boolean' ? a.scrollBack : !! settings.scroll,
				onChange: function ( value ) {
					set( { scrollBack: value } );
				},
			} )
		);

		var buttonPanel = el(
			PanelBody,
			{ title: __( 'Button style (Plus)', 'read-more-wp' ), initialOpen: false },
			control( SelectControl, {
				label: __( 'Style', 'read-more-wp' ),
				value: a.buttonStyle,
				options: [
					{ value: '', label: __( 'Default (from Settings)', 'read-more-wp' ) },
					{ value: 'default', label: __( 'Button', 'read-more-wp' ) },
					{ value: 'link', label: __( 'Text link', 'read-more-wp' ) },
					{ value: 'full', label: __( 'Full-width button', 'read-more-wp' ) },
				],
				onChange: function ( value ) {
					set( { buttonStyle: value } );
				},
			} ),
			control( SelectControl, {
				label: __( 'Alignment', 'read-more-wp' ),
				value: a.buttonAlign,
				options: [
					{ value: '', label: __( 'Default (from Settings)', 'read-more-wp' ) },
					{ value: 'left', label: __( 'Left', 'read-more-wp' ) },
					{ value: 'center', label: __( 'Center', 'read-more-wp' ) },
					{ value: 'right', label: __( 'Right', 'read-more-wp' ) },
				],
				onChange: function ( value ) {
					set( { buttonAlign: value } );
				},
			} )
		);

		var colorSettings = [
			{ label: __( 'Button text', 'read-more-wp' ), value: a.buttonColor, onChange: function ( value ) { set( { buttonColor: value || '' } ); } },
			{ label: __( 'Button background', 'read-more-wp' ), value: a.buttonBackground, onChange: function ( value ) { set( { buttonBackground: value || '' } ); } },
			{ label: __( 'Button border', 'read-more-wp' ), value: a.buttonBorder, onChange: function ( value ) { set( { buttonBorder: value || '' } ); } },
		];

		if ( isFade ) {
			colorSettings.push( { label: __( 'Fade (match the background)', 'read-more-wp' ), value: a.fadeColor, onChange: function ( value ) { set( { fadeColor: value || '' } ); } } );
		}

		var colors = PanelColorSettings
			? el( PanelColorSettings, {
					title: __( 'Colors (Plus)', 'read-more-wp' ),
					initialOpen: false,
					disableCustomColors: false,
					colorSettings: colorSettings,
			  } )
			: null;

		return el( InspectorControls, null, behavior, buttonPanel, colors );
	}

	var withPlusControls = wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( props.name !== 'read-more-wp/read-more' ) {
				return el( BlockEdit, props );
			}
			return el( Fragment, null, el( BlockEdit, props ), el( PlusControls, props ) );
		};
	}, 'withRmwpPlusControls' );

	wp.hooks.addFilter( 'editor.BlockEdit', 'read-more-wp/plus-controls', withPlusControls );
} )( window.wp );

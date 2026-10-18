<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.boltonstudios.com/read-more-wp/
 * @since      1.0.0
 *
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/public
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The public-facing functionality of the plugin.
 *
 * Renders the [start-read-more] / [end-read-more] shortcodes and the Read More block
 * with a shared renderer, and loads the front-end assets only on pages that use them.
 *
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/public
 * @author     Aaron Bolton <aaron@boltonstudios.com>
 */
class Read_More_Wp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The user's settings from the General admin tab.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $general_options    The user's settings from the General admin tab.
     */
    private $general_options;

	/**
	 * The closing HTML for each open [start-read-more] toggle, so each [end-read-more]
	 * closes the matching element.
	 *
	 * @since    1.1.8
	 * @access   private
	 * @var      array
	 */
	private $open_toggles;

	/**
	 * Whether any toggle rendered on this page.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      bool
	 */
	private $rendered_toggles;

	/**
	 * The handle used for the front-end script and stylesheet.
	 *
	 * @since    1.2.0
	 */
	const ASSET_HANDLE = 'read-more-wp';

	/**
	 * The name of the Read More block.
	 *
	 * @since    1.2.0
	 */
	const BLOCK_NAME = 'read-more-wp/read-more';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name      = $plugin_name;
		$this->version          = $version;
		$this->open_toggles     = array();
		$this->rendered_toggles = false;
		$this->general_options  = get_option( 'rmwp_general_options' );

		add_action( 'init', array( $this, 'rmwp_init' ) );

		// Unwrap shortcodes that the editor placed in their own paragraphs. Runs after wpautop() and before do_shortcode().
		add_filter( 'the_content', array( $this, 'rmwp_unwrap_shortcode_paragraphs' ), 10 );
		add_filter( 'widget_text_content', array( $this, 'rmwp_unwrap_shortcode_paragraphs' ), 10 );

		add_action( 'wp_footer', array( $this, 'rmwp_print_noscript_styles' ) );
	}

	/**
	 * Register the shortcodes, block, and front-end assets.
	 *
	 * @since    1.0.0
	 */
	public function rmwp_init(){

		add_shortcode( 'start-read-more', array( $this, 'rmwp_construct_start_read_more' ) );
		add_shortcode( 'end-read-more', array( $this, 'rmwp_construct_end_read_more' ) );

		$this->rmwp_register_assets();
		$this->rmwp_register_block();
	}

	/**
	 * Register the front-end stylesheet and script. They are enqueued only when a toggle renders.
	 *
	 * @since    1.2.0
	 */
	public function rmwp_register_assets() {

		wp_register_style( self::ASSET_HANDLE, plugin_dir_url( __FILE__ ) . 'css/read-more-wp-public.css', array(), $this->version );

		wp_register_script(
			self::ASSET_HANDLE,
			plugin_dir_url( __FILE__ ) . 'js/read-more-wp-public.js',
			array(),
			$this->version,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * Enqueue the front-end assets.
	 *
	 * @since    1.2.0
	 */
	public function rmwp_enqueue_assets() {

		wp_enqueue_style( self::ASSET_HANDLE );
		wp_enqueue_script( self::ASSET_HANDLE );

		/**
		 * Fires when a Read More toggle renders, so extensions can enqueue their assets.
		 *
		 * @since 1.2.0
		 */
		do_action( 'rmwp_enqueue_assets' );
	}

	/**
	 * Register the Read More block.
	 *
	 * @since    1.2.0
	 */
	public function rmwp_register_block() {

		$block_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'build/blocks/read-more';

		if ( ! file_exists( $block_dir . '/block.json' ) ) {
			return;
		}

		register_block_type( $block_dir, array( 'render_callback' => array( $this, 'rmwp_render_block' ) ) );

		// Share the default labels and license state with the block editor.
		$editor_handle = generate_block_asset_handle( self::BLOCK_NAME, 'editorScript' );
		$defaults      = $this->rmwp_get_default_options();

		wp_add_inline_script(
			$editor_handle,
			'window.rmwpBlockSettings = ' . wp_json_encode(
				apply_filters(
					'rmwp_block_editor_settings',
					array(
						'more'     => $defaults['more'],
						'less'     => $defaults['less'],
						'ellipsis' => $defaults['ellipsis'],
						'isPlus'   => false,
					)
				)
			) . ';',
			'before'
		);
	}

	/**
	 * The default toggle options, from the General settings.
	 *
	 * @since    1.2.0
	 * @return   array
	 */
	public function rmwp_get_default_options() {

		$general = is_array( $this->general_options ) ? $this->general_options : array();

		return array(
			'more'         => ! empty( $general['rmwp_more_button_label'] ) ? $general['rmwp_more_button_label'] : __( 'Read More', 'read-more-wp' ),
			'less'         => ! empty( $general['rmwp_less_button_label'] ) ? $general['rmwp_less_button_label'] : __( 'Read Less', 'read-more-wp' ),
			'inline'       => false,
			'ellipsis'     => empty( $general['rmwp_ellipsis_toggle'] ),
			'animation'    => 'none',
			'speed'        => 0,
			'mode'         => 'toggle',
			'height'       => 0,
			'scroll'       => false,
			'button_style' => '',
			'button_align' => '',
			'css_vars'     => array(),
		);
	}

	/**
	 * Convert shortcode attributes or block attributes into toggle options.
	 *
	 * Options that require Read More WP Plus are reset here and set by the Plus
	 * extension through the 'rmwp_toggle_options' filter.
	 *
	 * @since    1.2.0
	 * @param    array     $attributes    Shortcode attributes (snake case) or block attributes mapped to shortcode names.
	 * @param    string    $context       'shortcode' or 'block'.
	 * @return   array
	 */
	public function rmwp_get_toggle_options( $attributes, $context ) {

		$attributes = is_array( $attributes ) ? $attributes : array();
		$options    = $this->rmwp_get_default_options();

		foreach ( array( 'more', 'less' ) as $key ) {
			if ( isset( $attributes[ $key ] ) ) {
				$label = sanitize_text_field( (string) $attributes[ $key ] );
				if ( $label !== '' ) {
					$options[ $key ] = $label;
				}
			}
		}

		if ( isset( $attributes['inline'] ) ) {
			$options['inline'] = $this->rmwp_is_truthy( $attributes['inline'] );
		}

		if ( isset( $attributes['ellipsis'] ) ) {
			$options['ellipsis'] = $this->rmwp_is_truthy( $attributes['ellipsis'], true );
		}

		// Blocks are always block-level.
		if ( $context === 'block' ) {
			$options['inline'] = false;
		}

		/**
		 * Filters the options for a Read More toggle.
		 *
		 * @since 1.2.0
		 * @param array  $options    The sanitized toggle options.
		 * @param array  $attributes The raw shortcode or block attributes.
		 * @param string $context    'shortcode' or 'block'.
		 */
		$options = apply_filters( 'rmwp_toggle_options', $options, $attributes, $context );

		// Fade mode always wraps block-level content.
		if ( $options['mode'] === 'fade' ) {
			$options['inline'] = false;
		}

		return $options;
	}

	/**
	 * Build the opening and closing HTML for a toggle.
	 *
	 * @since    1.2.0
	 * @param    array    $options    Options from rmwp_get_toggle_options().
	 * @return   array    array( 'open' => string, 'close' => string )
	 */
	public function rmwp_build_toggle( $options ) {

		$this->rmwp_enqueue_assets();
		$this->rendered_toggles = true;

		$id      = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'rmwp' ) : uniqid( 'rmwp' );
		$tag     = $options['inline'] ? 'span' : 'div';
		$is_fade = ( $options['mode'] === 'fade' );

		// The button. JavaScript handles clicks using these data attributes.
		$button = sprintf(
			'<button type="button" aria-expanded="false" aria-controls="rmwp-toggle-%1$s" data-rmwp-id="%1$s" data-more="%2$s" data-less="%3$s" data-speed="%4$d"%5$s>%6$s</button>',
			esc_attr( $id ),
			esc_attr( $options['more'] ),
			esc_attr( $options['less'] ),
			absint( $options['speed'] ),
			$options['scroll'] ? ' data-scroll="1"' : '',
			esc_html( $options['more'] )
		);

		$wrap_classes = array( 'rmwp-button-wrap' );
		if ( $options['button_style'] !== '' ) {
			$wrap_classes[] = 'rmwp-button-style-' . $options['button_style'];
		}
		if ( $options['button_align'] !== '' ) {
			$wrap_classes[] = 'rmwp-align-' . $options['button_align'];
		}

		$wrap = sprintf(
			'<span class="%1$s" id="rmwp-button-wrap-%2$s"%3$s hidden>%4$s</span>',
			esc_attr( implode( ' ', $wrap_classes ) ),
			esc_attr( $id ),
			$this->rmwp_get_style_attribute( $options['css_vars'] ),
			$button
		);

		// The element containing the hidden (or, in fade mode, truncated) content.
		$toggle_classes = array( 'rmwp-toggle', 'animation-' . $options['animation'] );
		$toggle_attrs   = ' hidden';

		if ( $is_fade ) {
			$height           = absint( $options['height'] );
			$toggle_classes[] = 'rmwp-mode-fade';
			$toggle_classes[] = 'rmwp-collapsed';
			$toggle_attrs     = $this->rmwp_get_style_attribute(
				array_merge(
					$options['css_vars'],
					array(
						'--rmwp-fade-height' => $height . 'px',
						'max-height'         => $height . 'px',
						'overflow'           => 'hidden',
					)
				)
			);
		}

		$open = '';

		if ( ! $is_fade ) {

			// The ellipsis also marks where the button returns to when the content is collapsed.
			$ellipsis_style = $options['ellipsis'] ? '' : ' style="opacity: 0; position: absolute; z-index: -1;"';
			$open          .= '<span class="ellipsis" id="ellipsis-' . esc_attr( $id ) . '" aria-hidden="true"' . $ellipsis_style . '>...</span>';
			$open          .= $wrap;
		}

		$open .= sprintf(
			'<%1$s class="%2$s" id="rmwp-toggle-%3$s" data-rmwp-mode="%4$s"%5$s>',
			$tag,
			esc_attr( implode( ' ', $toggle_classes ) ),
			esc_attr( $id ),
			esc_attr( $options['mode'] ),
			$toggle_attrs
		);

		$close = sprintf(
			'</%1$s><%1$s class="rmwp-toggle-end" id="rmwp-toggle-end-%2$s">%3$s</%1$s>',
			$tag,
			esc_attr( $id ),
			$is_fade ? $wrap : ''
		);

		return array(
			'open'  => $open,
			'close' => $close,
		);
	}

	/**
	 * [start-read-more]
	 *
	 * @since    1.0.0
	 * @param    array|string    $user_attributes    The shortcode attributes.
	 * @return   string
	 */
	public function rmwp_construct_start_read_more( $user_attributes ){

		$parts = $this->rmwp_build_toggle( $this->rmwp_get_toggle_options( $user_attributes, 'shortcode' ) );

		// Remember how to close this toggle in the matching [end-read-more] shortcode.
		$this->open_toggles[] = $parts['close'];

		return $parts['open'];
	}

	/**
	 * [end-read-more]
	 *
	 * @since    1.0.0
	 * @return   string
	 */
	public function rmwp_construct_end_read_more(){

		// Ignore an [end-read-more] without a matching [start-read-more].
		if ( empty( $this->open_toggles ) ) {
			return '';
		}

		return array_pop( $this->open_toggles );
	}

	/**
	 * Render the Read More block.
	 *
	 * @since    1.2.0
	 * @param    array     $attributes    The block attributes.
	 * @param    string    $content       The rendered inner blocks (the hidden content).
	 * @return   string
	 */
	public function rmwp_render_block( $attributes, $content ) {

		$attributes = is_array( $attributes ) ? $attributes : array();

		// Map block attribute names to shortcode attribute names.
		$map = array(
			'moreLabel'        => 'more',
			'lessLabel'        => 'less',
			'showEllipsis'     => 'ellipsis',
			'animation'        => 'animation',
			'speed'            => 'speed',
			'mode'             => 'mode',
			'fadeHeight'       => 'height',
			'scrollBack'       => 'scroll',
			'buttonStyle'      => 'button',
			'buttonAlign'      => 'align',
			'buttonColor'      => 'button_color',
			'buttonBackground' => 'button_background',
			'buttonBorder'     => 'button_border',
			'fadeColor'        => 'fade_color',
		);

		$shortcode_attributes = array();

		foreach ( $map as $block_key => $shortcode_key ) {
			if ( isset( $attributes[ $block_key ] ) && $attributes[ $block_key ] !== '' ) {
				$shortcode_attributes[ $shortcode_key ] = is_bool( $attributes[ $block_key ] ) ? ( $attributes[ $block_key ] ? 'true' : 'false' ) : $attributes[ $block_key ];
			}
		}

		$parts = $this->rmwp_build_toggle( $this->rmwp_get_toggle_options( $shortcode_attributes, 'block' ) );

		$wrapper_attributes = function_exists( 'get_block_wrapper_attributes' ) ? get_block_wrapper_attributes( array( 'class' => 'rmwp-block' ) ) : 'class="rmwp-block"';

		return '<div ' . $wrapper_attributes . '>' . $parts['open'] . $content . $parts['close'] . '</div>';
	}

	/**
	 * Remove paragraph tags the editor wraps around a shortcode on its own line,
	 * e.g. <p class="wp-block-paragraph">[start-read-more]</p>, which would otherwise produce invalid HTML.
	 *
	 * @since    1.2.0
	 * @param    string    $content    The post content.
	 * @return   string
	 */
	public function rmwp_unwrap_shortcode_paragraphs( $content ) {

		if ( strpos( $content, 'read-more' ) === false ) {
			return $content;
		}

		return preg_replace( '#<p(?:\s[^>]*)?>\s*(\[(?:start|end)-read-more\b[^\]]*\])\s*</p>#i', '$1', $content );
	}

	/**
	 * Print styles that reveal the content and hide the buttons when JavaScript is disabled.
	 *
	 * @since    1.2.0
	 */
	public function rmwp_print_noscript_styles() {

		if ( ! $this->rendered_toggles ) {
			return;
		}

		echo '<noscript><style>div.rmwp-toggle[hidden]{display:block!important}span.rmwp-toggle[hidden]{display:inline!important}.rmwp-toggle.rmwp-collapsed{max-height:none!important;overflow:visible!important}.rmwp-toggle.rmwp-collapsed:after{display:none!important}span[id^="ellipsis-rmwp"]{display:none!important}</style></noscript>';
	}

	/**
	 * Build a style attribute from CSS properties.
	 *
	 * @since    1.2.0
	 * @param    array    $properties    CSS property => value pairs. Values must already be sanitized.
	 * @return   string
	 */
	private function rmwp_get_style_attribute( $properties ) {

		if ( empty( $properties ) ) {
			return '';
		}

		$declarations = array();

		foreach ( $properties as $property => $value ) {
			$declarations[] = $property . ': ' . $value;
		}

		return ' style="' . esc_attr( implode( '; ', $declarations ) ) . '"';
	}

	/**
	 * Interpret a shortcode attribute as a boolean.
	 *
	 * Accepts true/false, 1/0, yes/no, on/off, show/hide.
	 *
	 * @since    1.1.8
	 * @param    mixed    $value      The attribute value.
	 * @param    bool     $default    The value to use when the attribute is not recognized.
	 * @return   bool
	 */
	public function rmwp_is_truthy( $value, $default = false ){

		if ( is_bool( $value ) ) {
			return $value;
		}

		$value = strtolower( trim( (string) $value ) );

		if ( in_array( $value, array( 'true', '1', 'yes', 'on', 'show' ), true ) ) {
			return true;
		}

		if ( in_array( $value, array( 'false', '0', 'no', 'off', 'hide', '' ), true ) ) {
			return false;
		}

		return $default;
	}

	/**
	 * Get the value of general_options
	 *
	 * @since    1.0.0
	 */
	public function rmwp_get_general_options(){

		return $this->general_options;
	}
}

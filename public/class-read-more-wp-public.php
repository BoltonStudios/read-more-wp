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
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
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
	 * A stack of open [start-read-more] toggles, so each [end-read-more] closes the matching
	 * element type (div or span) and any extra wrapper (e.g., the pop-up overlay).
	 *
	 * @since    1.1.8
	 * @access   private
	 * @var      array
	 */
	private $open_toggles;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

        // Initialize instance variables.
		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->open_toggles = array();

        // Get user settings.
        $this->general_options = get_option( 'rmwp_general_options' );

        // Run the init() function once the activated plugins have loaded.
        add_action( 'plugins_loaded', array( $this, 'rmwp_init' ) );
    }

    /**
     * Define additional methods.
     * 
	 * @since    1.0.0
	 */

    // Define a helper function to initialize the class object.
    function rmwp_init(){
        
        // Add a new shortcode with the 'start-read-more' tag using the 'construct_start_read_more' callback function.
        add_shortcode( 'start-read-more', array( $this, 'rmwp_construct_start_read_more' ) );
    
        // Add a new shortcode with the 'end-read-more' tag using the 'construct_end_read_more' callback function.
        add_shortcode( 'end-read-more', array( $this, 'rmwp_construct_end_read_more' ) );
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function rmwp_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Read_More_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Read_More_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name. '-public-css', plugin_dir_url( __FILE__ ) . 'css/read-more-wp-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function rmwp_enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Read_More_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Read_More_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-public-js', plugin_dir_url( __FILE__ ) . 'js/read-more-wp-public.js', array( 'jquery' ), $this->version, false );
	}

    /**
	 * [start-read-more]
	 *
	 * @since    1.0.0
	 */
    function rmwp_construct_start_read_more( $user_attributes ){

        // Initialize variables with default values.
        $general_options    = $this->rmwp_get_general_options();
        $rmwp_id            = $this->rmwp_get_unique_id();
        $default_more_label = 'Read More';
        $default_less_label = 'Read Less';
        $hide_ellipsis      = ! empty( $general_options['rmwp_ellipsis_toggle'] );
        $more_label         = isset( $general_options['rmwp_more_button_label'] ) ? $general_options['rmwp_more_button_label'] : $default_more_label;
        $less_label         = isset( $general_options['rmwp_less_button_label'] ) ? $general_options['rmwp_less_button_label'] : $default_less_label;
        $toggle_break       = '';
        $classes            = '';
        $close_wrapper      = '';
        $button_action      = 'free';
        $animation_speed    = null;

        // Combine user attributes with known attributes and fill in defaults when needed.
        $attributes = shortcode_atts(
            array(
                'inline'    => false,
                'ellipsis'  => true,
                'more'      => $more_label,
                'less'      => $less_label,
                'animation' => null,
                'speed'     => null,
            ),
            $user_attributes
        );

        // Labels are plain text; they are escaped once at output.
        $more_label = sanitize_text_field( (string) $attributes['more'] );
        $less_label = sanitize_text_field( (string) $attributes['less'] );
        $inline     = $this->rmwp_is_truthy( $attributes['inline'] );
        $animation  = is_null( $attributes['animation'] ) ? null : sanitize_key( $attributes['animation'] );
        $speed_attr = $attributes['speed'];

        // If the user specified ellipsis=false (or hide/off) in the shortcode attributes, hide the ellipsis.
        if( ! $this->rmwp_is_truthy( $attributes['ellipsis'], true ) ){
            $hide_ellipsis = true;
        }

        // If the More or Less button labels were set to empty strings, use the defaults.
        $more_label = ( $more_label === '' ) ? $default_more_label : $more_label;
        $less_label = ( $less_label === '' ) ? $default_less_label : $less_label;

        // Use a span for inline breaks, otherwise a div.
        $element_type = $inline ? 'span' : 'div';

        // This IF block will be auto removed from the Free version.
        if ( rmwp_fs()->is__premium_only() ) {

            // The following IF will be executed only if the user in a trial mode or have a valid license.
            if ( rmwp_fs()->can_use_premium_code() ) {

                // ... premium only logic ...

                // Initialize variables.
                $plus_options = get_option( 'rmwp_plus_options' );
                $allowed_animations = array( 'none', 'slide', 'fade', 'fold', 'popUp' );

                // If the default animation option is set, assign its the value to the variable.
                $animation_default_setting = isset( $plus_options['rmwp_animation'] ) ? $plus_options['rmwp_animation'] : 'none';

                // If the default animation speed option is set, assign its the value to the variable.
                $animation_speed_default_setting = isset( $plus_options['rmwp_animation_speed'] ) ? $plus_options['rmwp_animation_speed'] : 500;

                // If the animation shortcode attribute is empty, use the animation default setting.
                $animation = ( $animation === null || $animation === '' ) ? $animation_default_setting : $animation;

                // Change the formatting of the pop-up attribute to camelcase (sanitize_key() lowercases it).
                $animation = in_array( strtolower( $animation ), array( 'pop-up', 'popup' ), true ) ? 'popUp' : $animation;

                // Only allow known animations.
                $animation = in_array( $animation, $allowed_animations, true ) ? $animation : 'none';

                // If the speed shortcode attribute is missing or not a number, use the default setting. Speed is always an integer in milliseconds.
                $animation_speed = is_numeric( $speed_attr ) ? $speed_attr : $animation_speed_default_setting;
                $animation_speed = min( absint( $animation_speed ), 10000 );

                // Add the animation attribute to the $classes variable, and prefix it with "animation-".
                $classes .= 'animation-' . $animation;

                // Use the Plus button action.
                $button_action = 'plus';

                // If the user selected the pop-up animation...
                if( $animation == 'popUp' ){

                    // Add the overlay element to the HTML.
                    $toggle_break .= '<' . $element_type . ' id="rmwp-animation-popUp-overlay-' . esc_attr( $rmwp_id ) . '" class="rmwp-animation-popUp-overlay" style="display: none">';

                    // Close the overlay in the matching [end-read-more] shortcode.
                    $close_wrapper = '</' . $element_type . '>';
                }
            }
        }

        // Remember how to close this toggle in the matching [end-read-more] shortcode.
        $this->open_toggles[] = array(
            'element_type'  => $element_type,
            'close_wrapper' => $close_wrapper,
        );

        // Construct the HTML for the element to wrap the hidden, togglable content.
        $toggle_break .= '<' . $element_type . ' class="rmwp-toggle ' . esc_attr( $classes ) . '" id="rmwp-toggle-' . esc_attr( $rmwp_id ) . '" style="display: none">';

        // The ellipsis partly serves as the insertion point for the Read More button,
        // so we do not want to omit it from the DOM; only hide it visually.
        $ellipsis_style = $hide_ellipsis ? ' style="opacity: 0; position: absolute; z-index: -1;"' : '';
        $ellipsis       = '<span class="ellipsis" id="ellipsis-' . esc_attr( $rmwp_id ) . '"' . $ellipsis_style . '>...</span>';

        // Construct the button. The click handler is attached in JavaScript using these data attributes.
        $button  = '<button name="read more" type="button" aria-expanded="false" aria-controls="rmwp-toggle-' . esc_attr( $rmwp_id ) . '"';
        $button .= ' data-rmwp-id="' . esc_attr( $rmwp_id ) . '"';
        $button .= ' data-rmwp-action="' . esc_attr( $button_action ) . '"';
        $button .= ' data-more="' . esc_attr( $more_label ) . '"';
        $button .= ' data-less="' . esc_attr( $less_label ) . '"';
        if( $animation_speed !== null ){
            $button .= ' data-speed="' . esc_attr( $animation_speed ) . '"';
        }
        $button .= '>' . esc_html( $more_label ) . '</button>';

        $read_more  = '<span class="rmwp-button-wrap" id="rmwp-button-wrap-' . esc_attr( $rmwp_id ) . '" style="display: none;">';
        $read_more .= $button;
        $read_more .= '</span>';

        // Assemble and return the output.
        return $ellipsis . $read_more . $toggle_break;
    }

    /**
	 * [end-read-more]
	 *
	 * @since    1.0.0
	 */
    function rmwp_construct_end_read_more(){

        // Ignore an [end-read-more] without a matching [start-read-more].
        if( empty( $this->open_toggles ) ){
            return '';
        }

        // Close the most recently opened toggle.
        $toggle       = array_pop( $this->open_toggles );
        $element_type = $toggle['element_type'];

        return '</' . $element_type . '>' . $toggle['close_wrapper'] . '<' . $element_type . ' class="rmwp-toggle-end"></' . $element_type . '>';
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
    private function rmwp_is_truthy( $value, $default = false ){

        if( is_bool( $value ) ){
            return $value;
        }

        $value = strtolower( trim( (string) $value ) );

        if( in_array( $value, array( 'true', '1', 'yes', 'on', 'show' ), true ) ){
            return true;
        }

        if( in_array( $value, array( 'false', '0', 'no', 'off', 'hide', '' ), true ) ){
            return false;
        }

        return $default;
    }

    /**
     * Generate an identifier for a read-more toggle that is unique within the page.
     *
     * @since    1.1.8
     * @return   string
     */
    private function rmwp_get_unique_id(){

        return function_exists( 'wp_unique_id' ) ? wp_unique_id( 'rmwp' ) : uniqid( 'rmwp' );
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

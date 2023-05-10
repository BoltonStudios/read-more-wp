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
	 * Description
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      boolean    $inline    Description.
	 */
	private $inline;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->inline = false;

        // Run the init() function once the activated plugins have loaded.
        add_action( 'plugins_loaded', array( $this, 'init' ) );

    }


    /**
     * Define additional methods.
     * 
	 * @since    1.0.0
	 */

    // Define a helper function to initialize the class object.
    function init(){
        
        // Add a new shortcode with the 'start-read-more' tag using the 'construct_start_read_more' callback function.
        add_shortcode( 'start-read-more', array( $this, 'construct_start_read_more' ) );
    
        // Add a new shortcode with the 'end-read-more' tag using the 'construct_end_read_more' callback function.
        add_shortcode( 'end-read-more', array( $this, 'construct_end_read_more' ) );
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/read-more-wp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/read-more-wp-public.js', array( 'jquery' ), $this->version, false );

	}

    /**
	 * [start-read-more]
	 *
	 * @since    1.0.0
	 */
    function construct_start_read_more( $user_attributes ){

        // Initialize variables.
        $rmwp_id    = rand(); // Generate a random number to identify this read-more toggle.
        $button     = '<button name="read more" type="button" onclick="rmwpToggleReadMore( '. $rmwp_id .' )">More</button>';
        $ellipsis   = '<span class="ellipsis">...</span>';
        $break      = '<div class="rmwp-toggle" id="rmwp-toggle-'. $rmwp_id .'">';
        $inline     = false;

        $read_more  = '<span class="rmwp-button-wrap" id="rmwp-button-wrap-'. $rmwp_id .'" style="display: none;">';
        $read_more .= $button;
        $read_more .= '</span>';

        // Handle attributes.
        if( isset( $user_attributes ) ){
            
            // Set list of supported attributes and their default values.
            $supported_attributes = array( 'inline' => $inline );

            // Combine user attributes with known attributes and fill in defaults when needed.
            $attributes = shortcode_atts( $supported_attributes, $user_attributes );
            
            // Update the inline variable.
            $inline = $attributes[ 'inline' ];
        } 

        // If the local inline variable is true...
        if( $inline == true ){

            // Set the class instance variable to true.
            // We want to keep track of the inline value to ensure the closing element matches
            // the opening element, whether a span or a div.
            $this->inline = true;

            // Change from the opening element from div to span.
            $break      = '<span class="rmwp-toggle" id="rmwp-toggle-'. $rmwp_id .'">';

        } else{

            // Set the class instance variable to false.
            $this->inline = false;
        }
        
        // Construct the output.
        $output  =  $ellipsis;
        $output .=  $read_more;
        $output .=  $break;
        
        // Return the output.
        return $output;
    }
    
    /**
	 * [end-read-more]
	 *
	 * @since    1.0.0
	 */
    function construct_end_read_more(){
        
        // Construct the output.
        if( $this->inline == true ){

            // Set the closing element as a span.
            $output = '</span>';

        } else{

            // Set the closing element as a div.
            $output = '</div><div class="rmwp-toggle-end">';
        }
        
        // Return the output.
        return $output;
    }

}

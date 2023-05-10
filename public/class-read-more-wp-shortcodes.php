<?php

/**
 * The Read_More_Wp_Shortcodes class
 *
 * Adds the Read More shortcodes to WordPress
 *
 *
 * @link       https://www.boltonstudios.com
 * @since      1.0.0
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/public
 * @author     Aaron Bolton <aaron@boltonstudios.com>
 */
    
if ( ! class_exists( 'Read_More_Wp_Shortcodes' ) ) {
    
    class Read_More_Wp_Shortcodes{

        // Define the constructor method.
        function __construct(){

            // Run the init() function once the activated plugins have loaded.
            add_action( 'plugins_loaded', array( $this, 'init' ) );
        }

        /**
         * Define additional methods.
         */

        // Define a helper function to initialize the class object.
        function init(){
            
            // Add a new shortcode with the 'start-read-more' tag using the 'construct_start_read_more' callback function.
            add_shortcode( 'start-read-more', array( $this, 'construct_start_read_more' ) );
        
            // Add a new shortcode with the 'end-read-more' tag using the 'construct_end_read_more' callback function.
            add_shortcode( 'end-read-more', array( $this, 'construct_end_read_more' ) );
        }

        // [start-read-more]
        function construct_start_read_more(){
            
            // Construct the output.
            $output = '<span class="rmwp-toggle">';
            
            // Return the output.
            return $output;
        }

        // [end-read-more]
        function construct_end_read_more(){
            
            // Initialize variables.
            $rmwp_id = rand(); // Generate a random number to identify this read-more toggle.
            
            // Construct the output.
            $read_more_button = '<span class="rmwp-button-wrap" id="rmwp-button-wrap-'. $rmwp_id .'" style="display: none;"><span class="ellipsis">...</span>';
            $read_more_button .= '<button name="read more" type="button" onclick="rmwpToggleReadMore( '. $rmwp_id .' )">More</button>';
            $read_more_button .= '</span>';
            
            // Construct the output.
            $output = '</span>';
            $output .= $read_more_button;
            
            // Return the output.
            return $output;
        }
    }
}
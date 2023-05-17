<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.boltonstudios.com/read-more-wp/
 * @since      1.0.0
 *
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/includes
 * @author     Aaron Bolton <aaron@boltonstudios.com>
 */
class Read_More_Wp_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
            
        /**
         * Set default settings.
         *
         * @since    1.0.0
         */

        // Initialize variables.
        $general_options = get_option('rmwp_general_options'); // General admin tab settings
        
        // If "rmwp_general_options" option was added (i.e., is not null)...
        if( $general_options != null){

            // Get the value of the "rmwp_ellipsis_toggle" setting.
            $ellipsis_toggle = $general_options[ "rmwp_ellipsis_toggle" ];

             // If "rmwp_ellipsis_toggle" setting option was not added (i.e., is null)...
            if( $ellipsis_toggle == null){

                // Update the value of "rmwp_ellipsis_toggle" with a default setting.
                $general_options[ "rmwp_ellipsis_toggle" ] = 1; // Checked
            }

            // Updates the value of the "rmwp_general_options" option.
            update_option('rmwp_general_options', $general_options, null );
        }
	}
}

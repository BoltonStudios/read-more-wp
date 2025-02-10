<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.boltonstudios.com/read-more-wp/
 * @since             1.0.0
 * @package           Read_More_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       Read More WP
 * Plugin URI:        https://wordpress.org/plugins/read-more-wp/
 * Description:       Create excerpts and hide text with an elegant toggle button to show more.
 * Version:           1.1.6
 * Author:            Aaron Bolton
 * Author URI:        https://www.boltonstudios.com/read-more-wp/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       read-more-wp
 * Domain Path:       /languages
 *
 * @fs_premium_only /plus/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The Freemius SDK comes with a special mechanism to auto deactivate the free version when activating the paid one.
if ( function_exists( 'rmwp_fs' ) ) {

    rmwp_fs()->set_basename( true, __FILE__ );

} else {

    /**
     * Current plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'READ_MORE_WP_VERSION', '1.1.6' );
    define( 'READ_MORE_WP_BASENAME', plugin_basename( __FILE__ ) );

    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-read-more-wp-activator.php
     */
    function activate_read_more_wp() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-read-more-wp-activator.php';
        Read_More_Wp_Activator::rmwp_activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-read-more-wp-deactivator.php
     */
    function deactivate_read_more_wp() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-read-more-wp-deactivator.php';
        Read_More_Wp_Deactivator::rmwp_deactivate();
    }

    register_activation_hook( __FILE__, 'activate_read_more_wp' );
    register_deactivation_hook( __FILE__, 'deactivate_read_more_wp' );

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-read-more-wp.php';
    
    if ( ! function_exists( 'rmwp_fs' ) ) {

        // Create a helper function for easy SDK access.
        function rmwp_fs() {

            global $rmwp_fs;
    
            if ( ! isset( $rmwp_fs ) ) {

                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/freemius/start.php';
    
                $rmwp_fs = fs_dynamic_init( array(
                    'id'                  => '12677',
                    'slug'                => 'read-more-wp',
                    'premium_slug'        => 'read-more-wp-plus',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_42b15e73d8e69906aae9a2d9ecfd9',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Plus',
                    // If your plugin is a serviceware, set this option to false.
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'menu'                => array(
                        'slug'           => 'read-more-wp',
                        'contact'        => false,
                        'support'        => false,
                        'parent'         => array(
                            'slug' => 'options-general.php',
                        ),
                    ),
                ) );
            }
    
            return $rmwp_fs;
        }
    
        // Init Freemius.
        rmwp_fs();

        // Signal that SDK was initiated.
        do_action( 'rmwp_fs_loaded' );
    }

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_read_more_wp() {

        // Load the essential plugin features.
        $plugin = new Read_More_Wp( READ_MORE_WP_BASENAME );
        
        // This IF block will be auto removed from the Free version.
        if ( rmwp_fs()->is__premium_only() ) {

            // The following IF will be executed only if the user in a trial mode or have a valid license.
            if ( rmwp_fs()->can_use_premium_code() ) {

                // ... premium only logic ...
                require_once plugin_dir_path( __FILE__ ) . 'plus/class-read-more-wp-plus.php';
                
                // Load Premium Features and pass the plugin object to be modified
                $plugin_plus = new Read_More_Wp_Plus( $plugin );

                // Load Premium scripts and styles.
                $plugin->rmwp_get_loader()->rmwp_add_action( 'wp_enqueue_scripts', $plugin_plus, 'rmwp_enqueue_scripts' );
                $plugin->rmwp_get_loader()->rmwp_add_action( 'wp_enqueue_scripts', $plugin_plus, 'rmwp_enqueue_styles' );
            }
        }
        
        // Execute all of the plugin hooks with WordPress.
        $plugin->rmwp_run();
    }

    // Start the plugin.
    run_read_more_wp();
}
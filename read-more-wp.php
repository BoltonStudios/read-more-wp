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
 * Version:           1.0.0
 * Author:            Aaron Bolton
 * Author URI:        https://www.boltonstudios.com/read-more-wp/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       read-more-wp
 * Domain Path:       /languages
 *
 * @fs_premium_only /premium/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The Freemius SDK comes with a special mechanism to auto deactivate the free version when activating the paid one.
if ( function_exists( 'rmwp_fs' ) ) {

    ezrwp_fs()->set_basename( true, __FILE__ );

} else {

	if ( ! class_exists( 'Read_More_Wp' ) ) {

        /**
         * Currently plugin version.
         * Start at version 1.0.0 and use SemVer - https://semver.org
         * Rename this for your plugin and update it as you release new versions.
         */
        define( 'READ_MORE_WP_VERSION', '1.0.0' );
        define( 'READ_MORE_WP_BASENAME', plugin_basename( __FILE__ ) );

        /**
         * The code that runs during plugin activation.
         * This action is documented in includes/class-read-more-wp-activator.php
         */
        function activate_read_more_wp() {
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-read-more-wp-activator.php';
            Read_More_Wp_Activator::activate();
        }

        /**
         * The code that runs during plugin deactivation.
         * This action is documented in includes/class-read-more-wp-deactivator.php
         */
        function deactivate_read_more_wp() {
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-read-more-wp-deactivator.php';
            Read_More_Wp_Deactivator::deactivate();
        }

        register_activation_hook( __FILE__, 'activate_read_more_wp' );
        register_deactivation_hook( __FILE__, 'deactivate_read_more_wp' );

        /**
         * The core plugin class that is used to define internationalization,
         * admin-specific hooks, and public-facing site hooks.
         */
        require plugin_dir_path( __FILE__ ) . 'includes/class-read-more-wp.php';

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
            $plugin = new Read_More_Wp();

            //
            $plugin->run();

        }
        run_read_more_wp();
    }
}
<?php

/**
 * The admin settings of the plugin.
 *
 * @link       https://www.boltonstudios.com
 * @since      1.0.0
 *
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/admin
 * @author     Aaron Bolton <aaron@boltonstudios.com>
 */

if ( ! class_exists( 'Read_More_Wp_Settings' ) ) {
    
    class Read_More_Wp_Settings {

        /**
         * The title of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_name    The title of this plugin.
         */
        private $plugin_name;
        
        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_slug    The ID of this plugin.
         */
        private $plugin_slug;

        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        /**
         *  The object that contains methods used to build the plugin settings.
         *
         * @since    1.0.0
         * @access   private
         * @var      array    $settings    The current version of this plugin.
         */
        private $settings;

        /**
         * The tabs that separate plugin options in the admin interface.
         *
         * @since    1.0.0
         * @access   private
         * @var      array    $tabs 
         */
        private $tabs;

        /**
         * The section organize fields in each tab.
         *
         * @since    1.0.0
         * @access   private
         * @var      array    $sections 
         */
        private $sections;
        
        public function __construct( $plugin_name, $plugin_slug, $version ) {

            $this->plugin_name = $plugin_name;
            $this->plugin_slug = $plugin_slug;
            $this->version = $version;
            $this->tabs = $this->create_tabs();
            $this->sections = $this->create_sections();
            $this->settings = $this->create_settings();
        }
        
        /**
         * Create the tabs.
         * @return array
         */
        public function create_tabs() {

            $tabs[] = array(
                'General', //display name
                'rmwp_general', //option group
                'rmwp_general_options' // option name
            );
            return $tabs;
        }
        
        /**
         * Create the sections for the Read More WP Settings page.
         * @return array
         */
        public function create_sections() {

            // General
            $sections[] = array(
                'id'  => 'rmwp_section_for_defaults', // id
                'title'  => __( 'Default Plugin Settings' ), // title
                'page'  => 'rmwp_general' // page
            );
            $sections[] = array(
                'id'  => 'rmwp_section_for_support', // id
                'title'  => __( 'Debugging Information', 'ezrwp_general' ), // title
                'page'  => 'rmwp_general' // page
            );

            return $sections;
        }
        
        /**
         * Create the settings for Read More WP
         * @return array
         */
        public function create_settings() {

            //
            $settings[] = array(
                'id'        => 'rmwp_more_button_label', // id. Used only internally.
                'title'     => __( 'More Button Label' ), // title.
                'callback'  => 'rmwp_more_button_label_field_cb', // callback.
                'tab'       => 'rmwp_general', // page
                'section'   => 'rmwp_section_for_defaults'
            );

            //
            return $settings;
        }

        /**
         * Set the value of tabs
         *
         * @return  self
         */ 
        public function set_tabs($tabs)
        {
            $this->tabs = $tabs;

            return $this;
        }

        /**
         * Get the value of tabs
         */ 
        public function get_tabs()
        {
            return $this->tabs;
        }

        /**
         * Get the value of settings
         */ 
        public function get_settings()
        {
            return $this->settings;
        }

        /**
         * Set the value of settings
         *
         * @return  self
         */ 
        public function set_settings(array $settings)
        {
            $this->settings = $settings;

            return $this;
        }

        /**
         * Get $sections
         *
         * @return  array
         */ 
        public function get_sections()
        {
            return $this->sections;
        }

        /**
         * Set $sections
         *
         * @param  array  $sections  $sections
         *
         * @return  self
         */ 
        public function set_sections(array $sections)
        {
            $this->sections = $sections;

            return $this;
        }
    }
}
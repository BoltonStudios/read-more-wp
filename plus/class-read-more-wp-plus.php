<?php

/**
 * The premium-version functionality of the plugin.
 *
 * @link       https://www.boltonstudios.com
 * @since      1.0.0
 *
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/plus
 */

if ( ! class_exists( 'Read_More_Wp_Plus' ) ) {

    /**
     * Read_More_Wp_Plus Main Class
     */
    class Read_More_Wp_Plus{

        /**
         *
         *
         * @since    1.0.0
         * @access   private
         * @var      Read_More_Wp    $base_plugin
         */
        private $base_plugin;
        
        /**
         *
         *
         * @since    1.0.0
         * @access   protected
         * @var      Read_More_Wp_Admin    $base_plugin_admin
         */
        private $base_plugin_admin;
        
        /**
         *
         *
         * @since    1.0.0
         * @access   private
         * @var      Read_More_Wp_Settings   $base_plugin_settings
         */
        private $base_plugin_settings;
        
        /**
         *
         *
         * @since    1.0.0
         * @access   private
         * @var      Read_More_Wp_Settings    $plus_settings
         */
        private $plus_settings;

        public function __construct($base_plugin)
        {
            $this->base_plugin = $base_plugin;
            $this->base_plugin_settings = $base_plugin->get_plugin_settings();
            $this->base_plugin_admin = $base_plugin->get_plugin_admin();
            $this->plus_settings = $this->add_plus_settings();

            $this->init();
        }
        function init(){
            
            $this->get_base_plugin()->set_plugin_settings(
                $this->get_plus_settings()
            );
        }

        /**
         * Register the Plus stylesheets for the public-facing side of the site.
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
            //echo plugin_dir_url( __FILE__ ) . 'css/read-more-wp-plus.css';
            echo  $this->base_plugin->get_plugin_name();
            wp_enqueue_style( $this->base_plugin->get_plugin_slug() . 'plus', plugin_dir_url( __FILE__ ) . 'css/read-more-wp-plus.css', array(), $this->base_plugin->get_version(), 'all' );
            
        }
    
        /**
         * Register the Plus JavaScript for the public-facing side of the site.
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
    
            wp_enqueue_script( $this->base_plugin->get_plugin_name(), plugin_dir_url( __FILE__ ) . 'plus/js/read-more-wp-plus.js', array( 'jquery' ), $this->base_plugin->get_version(), false );
    
        }

        /**
         *  Add Plus Version Settings
         */
        function add_plus_settings(){
            
            // Define Premium Version Callbacks

            // Lender Reviews Section
            function rmwp_plus_section_cb( $args ) {
                ?>
                <p id="<?php echo esc_attr( $args['id'] ); ?>">
                    Please find the default "plus" settings below. You may override the default settings using the shortcode options.
                </p>
                <hr />
                <p id="<?php echo esc_attr( $args['id'] ); ?>-3">
                    Example shortcode with overrides:<br />[start-read-more animation="fade"][end-read-more]
    
                </p>
                <hr />
                <?php
            }

            // Callback
            function rmwp_plus_animation_select_field_cb( $args ) {
                
                // Initialize varables.
                $setting = '';
                $options = get_option('rmwp_plus_options'); // Current options.

                // If the value for this option exists in the database...
                if( isset( $options[$args['label_for']] ) ){

                    // Assign it to the local $setting variable.
                    $setting = $options[$args['label_for']];
                };

                // Construct the form field output.
                ?>
                    
                <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
                        class="rmwp-setting rmwp-animation"
                        name="rmwp_plus_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
                    <option value="none" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'none', false ) ) : ( '' ); ?>>
                    <?php esc_html_e( 'None', 'rmwp' ); ?>
                    </option>
                    <option value="fade" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'fade', false ) ) : ( '' ); ?>>
                    <?php esc_html_e( 'Fade', 'rmwp' ); ?>
                    </option>
                    <option value="accordion" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'accordion', false ) ) : ( '' ); ?>>
                    <?php esc_html_e( 'Accordion', 'rmwp' ); ?>
                </select>

                <?php
            }

            // Initialize variables.
            $settings = $this->get_base_plugin_settings(); // Default Read_More_Wp_Settings object.
            $premium_settings_tabs = $settings->get_tabs(); // Default tabs.
            $premium_settings_sections = $settings->get_sections(); // Default sections.
            $premium_settings_fields = $settings->get_settings(); // Default settings.

            // Append plus tabs to the default tabs array.
            $premium_settings_tabs["plus"] = array(
                'Plus Options', //display name
                'rmwp_plus', //option group
                'rmwp_plus_options' // option name
            );

            // Update the current instance variable.
            $settings->set_tabs( $premium_settings_tabs );

            // Append plus sections to the default sections array.
            $premium_settings_sections[] = array(
                'id'    => 'rmwp_plus_section', // id
                'title' => __( 'Plus Options', 'rmwp_plus' ), // title
                'page'  => 'rmwp_plus' // page
            );

            // Update the current instance variable.
            $settings->set_sections( $premium_settings_sections );

            // Override settings fields
            $premium_settings_fields[] = array(
                'id'        => 'rmwp_animation', // id. Used only internally
                'title'     => __( 'Animation', 'rmwp_plus' ), // title
                'callback'  => 'rmwp_plus_animation_select_field_cb', // callback
                'tab'       => 'rmwp_plus', // page
                'section'   => 'rmwp_plus_section'
            );

            // Update the current instance variable.
            $settings->set_settings( $premium_settings_fields );

            // Return premium settings
            return $settings;
        }

        // Getters & Setters
        /**
         * Get $base_plugin
         *
         * @return  Read_More_Wp
         */ 
        public function get_base_plugin()
        {
            return $this->base_plugin;
        }

        /**
         * Set $base_plugin
         *
         * @param  Read_More_Wp  $base_plugin  $base_plugin
         *
         * @return  self
         */ 
        public function set_base_plugin(Read_More_Wp $base_plugin)
        {
            $this->base_plugin = $base_plugin;

            return $this;
        }
        /**
         * Get $base_plugin_admin
         *
         * @return  Read_More_Wp_Admin
         */ 
        public function get_base_plugin_admin()
        {
            return $this->base_plugin_admin;
        }

        /**
         * Set $base_plugin_admin
         *
         * @param  Read_More_Wp_Admin  $base_plugin_admin  $base_plugin_admin
         *
         * @return  self
         */ 
        public function set_base_plugin_admin(Read_More_Wp_Admin $base_plugin_admin)
        {
            $this->base_plugin_admin = $base_plugin_admin;

            return $this;
        }

        /**
         * Get $base_plugin_settings
         *
         * @return  Read_More_Wp_Settings
         */ 
        public function get_base_plugin_settings()
        {
            return $this->base_plugin_settings;
        }

        /**
         * Set $base_plugin_settings
         *
         * @param  Read_More_Wp_Settings  $base_plugin_settings  $base_plugin_settings
         *
         * @return  self
         */ 
        public function set_base_plugin_settings(Read_More_Wp_Settings $base_plugin_settings)
        {
            $this->base_plugin_settings = $base_plugin_settings;

            return $this;
        }

        /**
         * Get $plus_settings
         *
         * @return  Read_More_Wp_Settings
         */ 
        public function get_plus_settings()
        {
            return $this->plus_settings;
        }

        /**
         * Set $plus_settings
         *
         * @param  Read_More_Wp_Settings  $plus_settings  $plus_settings
         *
         * @return  self
         */ 
        public function set_plus_settings(Read_More_Wp_Settings $plus_settings)
        {
            $this->plus_settings = $plus_settings;

            return $this;
        }
    }
}
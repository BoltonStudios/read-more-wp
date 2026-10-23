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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Read_More_Wp_Plus Main Class
 *
 * Adds animations, pop-ups, fade-out truncation, button styles, and scroll-back
 * to the toggles rendered by Read_More_Wp_Public.
 */
class Read_More_Wp_Plus{

    /**
     * The base plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      Read_More_Wp    $base_plugin
     */
    private $base_plugin;

    /**
     * The base plugin admin.
     *
     * @since    1.0.0
     * @access   private
     * @var      Read_More_Wp_Admin    $base_plugin_admin
     */
    private $base_plugin_admin;

    /**
     * The base plugin settings.
     *
     * @since    1.0.0
     * @access   private
     * @var      Read_More_Wp_Settings   $base_plugin_settings
     */
    private $base_plugin_settings;

    /**
     * The settings including the Plus tab.
     *
     * @since    1.0.0
     * @access   private
     * @var      Read_More_Wp_Settings    $plus_settings
     */
    private $plus_settings;

    /**
     * The name of the Plus option in the wp_options table.
     *
     * @since    1.2.0
     */
    const OPTION_NAME = 'rmwp_plus_options';

    /**
     * The asset handle for the Plus front-end script and stylesheet.
     *
     * @since    1.2.0
     */
    const ASSET_HANDLE = 'read-more-wp-plus';

    /**
     * Supported animations.
     *
     * @since    1.2.0
     */
    const ANIMATIONS = array( 'none', 'slide', 'fade', 'fold', 'popUp' );

    /**
     * Supported button styles. The empty string is the default style.
     *
     * @since    1.2.0
     */
    const BUTTON_STYLES = array( '', 'link', 'full' );

    /**
     * Supported button alignments. The empty string leaves the button where it is.
     *
     * @since    1.2.0
     */
    const BUTTON_ALIGNMENTS = array( '', 'left', 'center', 'right' );

    public function __construct( $base_plugin ) {

        $this->base_plugin = $base_plugin;
        $this->base_plugin_settings = $base_plugin->rmwp_get_plugin_settings();
        $this->base_plugin_admin = $base_plugin->rmwp_get_plugin_admin();

        $this->rmwp_init();
    }

    /**
     * Add the Plus settings. Runs on init because the labels are translated.
     *
     * @since    1.2.0
     */
    function rmwp_setup_settings(){

        $this->plus_settings = $this->rmwp_add_plus_settings();
        $this->rmwp_get_base_plugin()->rmwp_set_plugin_settings( $this->plus_settings );
    }

    /**
     * Register the Plus hooks.
     *
     * @since    1.0.0
     */
    function rmwp_init(){

        add_action( 'init', array( $this, 'rmwp_setup_settings' ), 5 );

        add_filter( 'rmwp_toggle_options', array( $this, 'rmwp_filter_toggle_options' ), 10, 3 );
        add_filter( 'rmwp_block_editor_settings', array( $this, 'rmwp_filter_block_editor_settings' ) );
        add_filter( 'rmwp_sanitize_' . self::OPTION_NAME, array( $this, 'rmwp_sanitize_options' ) );
        add_action( 'init', array( $this, 'rmwp_register_assets' ) );
        add_action( 'rmwp_enqueue_assets', array( $this, 'rmwp_enqueue_assets' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'rmwp_enqueue_block_editor_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'rmwp_enqueue_admin_assets' ) );
    }

    /**
     * Get the saved Plus options merged with their defaults.
     *
     * @since    1.2.0
     * @return   array
     */
    public function rmwp_get_options() {

        $saved = get_option( self::OPTION_NAME );

        return array_merge(
            array(
                'rmwp_animation'         => 'none',
                'rmwp_animation_speed'   => 500,
                'rmwp_scroll_back'       => '',
                'rmwp_fade_height'       => 200,
                'rmwp_fade_color'        => '',
                'rmwp_button_style'      => '',
                'rmwp_button_align'      => '',
                'rmwp_button_color'      => '',
                'rmwp_button_background' => '',
                'rmwp_button_border'     => '',
                'rmwp_button_radius'     => '',
            ),
            is_array( $saved ) ? $saved : array()
        );
    }

    /**
     * Apply the Plus settings and shortcode/block attributes to a toggle.
     *
     * @since    1.2.0
     * @param    array     $options       The toggle options.
     * @param    array     $attributes    The raw shortcode attributes (block attributes are mapped to these names).
     * @param    string    $context       'shortcode' or 'block'.
     * @return   array
     */
    public function rmwp_filter_toggle_options( $options, $attributes, $context ) {

        $settings = $this->rmwp_get_options();
        $public   = $this->base_plugin->rmwp_get_plugin_public();

        // Animation.
        $animation = isset( $attributes['animation'] ) && $attributes['animation'] !== '' ? $attributes['animation'] : $settings['rmwp_animation'];
        $options['animation'] = $this->rmwp_normalize_animation( $animation );

        // Speed in milliseconds.
        $speed = isset( $attributes['speed'] ) && is_numeric( $attributes['speed'] ) ? $attributes['speed'] : $settings['rmwp_animation_speed'];
        $options['speed'] = min( absint( $speed ), 10000 );

        // Mode: hide the content (toggle) or show its start with a fade (fade).
        $mode = isset( $attributes['mode'] ) ? strtolower( trim( (string) $attributes['mode'] ) ) : '';
        $options['mode'] = ( $mode === 'fade' ) ? 'fade' : 'toggle';

        // Fade height in pixels.
        $height = isset( $attributes['height'] ) && is_numeric( $attributes['height'] ) ? $attributes['height'] : $settings['rmwp_fade_height'];
        $options['height'] = min( max( absint( $height ), 20 ), 5000 );

        // A pop-up has nothing to fade, so fade mode uses an in-place animation instead.
        if ( $options['mode'] === 'fade' && $options['animation'] === 'popUp' ) {
            $options['animation'] = 'slide';
        }

        // Scroll back to the button after collapsing.
        $options['scroll'] = isset( $attributes['scroll'] ) ? $public->rmwp_is_truthy( $attributes['scroll'] ) : ! empty( $settings['rmwp_scroll_back'] );

        // Button style.
        $style = isset( $attributes['button'] ) ? strtolower( trim( (string) $attributes['button'] ) ) : $settings['rmwp_button_style'];
        $style = ( $style === 'default' || $style === 'button' ) ? '' : $style;
        $options['button_style'] = in_array( $style, self::BUTTON_STYLES, true ) ? $style : $settings['rmwp_button_style'];

        // Button alignment.
        $align = isset( $attributes['align'] ) ? strtolower( trim( (string) $attributes['align'] ) ) : $settings['rmwp_button_align'];
        $options['button_align'] = in_array( $align, self::BUTTON_ALIGNMENTS, true ) ? $align : '';

        // Per-toggle colors.
        $colors = array(
            'button_color'      => '--rmwp-button-color',
            'button_background' => '--rmwp-button-background',
            'button_border'     => '--rmwp-button-border',
            'fade_color'        => '--rmwp-fade-color',
        );

        foreach ( $colors as $key => $property ) {
            $color = isset( $attributes[ $key ] ) ? sanitize_hex_color( $attributes[ $key ] ) : '';
            if ( $color ) {
                $options['css_vars'][ $property ] = $color;
            }
        }

        $options['css_vars'] = array_merge( $options['css_vars'], $this->rmwp_get_hover_vars( $options['css_vars'] ) );

        return $options;
    }

    /**
     * Normalize an animation name, e.g. "pop-up" becomes "popUp".
     *
     * @since    1.2.0
     * @param    string    $animation    The animation name.
     * @return   string
     */
    private function rmwp_normalize_animation( $animation ) {

        $animation = strtolower( trim( (string) $animation ) );

        if ( in_array( $animation, array( 'pop-up', 'popup' ), true ) ) {
            return 'popUp';
        }

        return in_array( $animation, self::ANIMATIONS, true ) ? $animation : 'none';
    }

    /**
     * With custom button colors, keep the colors on hover (the stylesheet darkens the button slightly instead).
     *
     * @since    1.2.0
     * @param    array    $vars    CSS custom properties.
     * @return   array
     */
    private function rmwp_get_hover_vars( $vars ) {

        $hover = array();
        $map   = array(
            '--rmwp-button-color'      => '--rmwp-button-hover-color',
            '--rmwp-button-background' => '--rmwp-button-hover-background',
            '--rmwp-button-border'     => '--rmwp-button-hover-border',
        );

        foreach ( $map as $property => $hover_property ) {
            if ( isset( $vars[ $property ] ) ) {
                $hover[ $hover_property ] = $vars[ $property ];
            }
        }

        return $hover;
    }

    /**
     * Tell the block editor that Plus features are available, and share the defaults.
     *
     * @since    1.2.0
     * @param    array    $settings    The block editor settings.
     * @return   array
     */
    public function rmwp_filter_block_editor_settings( $settings ) {

        $options = $this->rmwp_get_options();

        $settings['isPlus']     = true;
        $settings['animation']  = $options['rmwp_animation'];
        $settings['speed']      = absint( $options['rmwp_animation_speed'] );
        $settings['fadeHeight'] = absint( $options['rmwp_fade_height'] );
        $settings['scroll']     = ! empty( $options['rmwp_scroll_back'] );

        return $settings;
    }

    /**
     * Register the Plus front-end assets.
     *
     * @since    1.2.0
     */
    public function rmwp_register_assets() {

        $version = $this->base_plugin->rmwp_get_version();

        wp_register_style( self::ASSET_HANDLE, plugin_dir_url( __FILE__ ) . 'css/read-more-wp-plus.css', array( Read_More_Wp_Public::ASSET_HANDLE ), $version );

        $inline_css = $this->rmwp_get_settings_css();
        if ( $inline_css !== '' ) {
            wp_add_inline_style( self::ASSET_HANDLE, $inline_css );
        }

        wp_register_script(
            self::ASSET_HANDLE,
            plugin_dir_url( __FILE__ ) . 'js/read-more-wp-plus.js',
            array( Read_More_Wp_Public::ASSET_HANDLE ),
            $version,
            array(
                'in_footer' => true,
                'strategy'  => 'defer',
            )
        );
    }

    /**
     * Build CSS for the button and fade settings.
     *
     * @since    1.2.0
     * @return   string
     */
    private function rmwp_get_settings_css() {

        $options = $this->rmwp_get_options();
        $vars    = array();

        foreach ( array( 'color', 'background', 'border' ) as $key ) {
            $color = sanitize_hex_color( $options[ 'rmwp_button_' . $key ] );
            if ( $color ) {
                $vars[ '--rmwp-button-' . $key ] = $color;
            }
        }

        $vars = array_merge( $vars, $this->rmwp_get_hover_vars( $vars ) );

        if ( $options['rmwp_button_radius'] !== '' && is_numeric( $options['rmwp_button_radius'] ) ) {
            $vars['--rmwp-button-radius'] = absint( $options['rmwp_button_radius'] ) . 'px';
        }

        $fade_color = sanitize_hex_color( $options['rmwp_fade_color'] );
        if ( $fade_color ) {
            $vars['--rmwp-fade-color'] = $fade_color;
        }

        if ( empty( $vars ) ) {
            return '';
        }

        $declarations = array();
        foreach ( $vars as $property => $value ) {
            $declarations[] = $property . ':' . $value;
        }

        return ':root{' . implode( ';', $declarations ) . '}';
    }

    /**
     * Enqueue the Plus front-end assets (called when a toggle renders).
     *
     * @since    1.2.0
     */
    public function rmwp_enqueue_assets() {

        wp_enqueue_style( self::ASSET_HANDLE );
        wp_enqueue_script( self::ASSET_HANDLE );
    }

    /**
     * Enqueue the Plus controls for the Read More block.
     *
     * @since    1.2.0
     */
    public function rmwp_enqueue_block_editor_assets() {

        wp_enqueue_script(
            self::ASSET_HANDLE . '-editor',
            plugin_dir_url( __FILE__ ) . 'js/read-more-wp-plus-editor.js',
            array( 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-element', 'wp-hooks', 'wp-i18n' ),
            $this->base_plugin->rmwp_get_version(),
            true
        );
    }

    /**
     * Enqueue the color picker on the plugin settings page.
     *
     * @since    1.2.0
     * @param    string    $hook_suffix    The current admin page.
     */
    public function rmwp_enqueue_admin_assets( $hook_suffix ) {

        if ( $hook_suffix !== 'settings_page_' . $this->base_plugin->rmwp_get_plugin_slug() ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){ $(".rmwp-color-field").wpColorPicker(); });' );
    }

    /**
     * Sanitize the Plus options before saving.
     *
     * @since    1.2.0
     * @param    array    $input    The submitted options.
     * @return   array
     */
    public function rmwp_sanitize_options( $input ) {

        $input  = is_array( $input ) ? $input : array();
        $output = array();

        $output['rmwp_animation']       = $this->rmwp_normalize_animation( isset( $input['rmwp_animation'] ) ? $input['rmwp_animation'] : 'none' );
        $output['rmwp_animation_speed'] = isset( $input['rmwp_animation_speed'] ) ? min( absint( $input['rmwp_animation_speed'] ), 10000 ) : 500;
        $output['rmwp_scroll_back']     = empty( $input['rmwp_scroll_back'] ) ? '' : '1';
        $output['rmwp_fade_height']     = isset( $input['rmwp_fade_height'] ) ? min( max( absint( $input['rmwp_fade_height'] ), 20 ), 5000 ) : 200;

        $style = isset( $input['rmwp_button_style'] ) ? $input['rmwp_button_style'] : '';
        $output['rmwp_button_style'] = in_array( $style, self::BUTTON_STYLES, true ) ? $style : '';

        $align = isset( $input['rmwp_button_align'] ) ? $input['rmwp_button_align'] : '';
        $output['rmwp_button_align'] = in_array( $align, self::BUTTON_ALIGNMENTS, true ) ? $align : '';

        foreach ( array( 'rmwp_fade_color', 'rmwp_button_color', 'rmwp_button_background', 'rmwp_button_border' ) as $key ) {
            $output[ $key ] = isset( $input[ $key ] ) ? (string) sanitize_hex_color( $input[ $key ] ) : '';
        }

        $output['rmwp_button_radius'] = ( isset( $input['rmwp_button_radius'] ) && $input['rmwp_button_radius'] !== '' ) ? min( absint( $input['rmwp_button_radius'] ), 100 ) : '';

        return $output;
    }

    /**
     *  Add Plus Version Settings
     *
     * @since    1.0.0
     * @return   Read_More_Wp_Settings
     */
    function rmwp_add_plus_settings(){

        $settings = $this->rmwp_get_base_plugin_settings();
        $tabs     = $settings->rmwp_get_tabs();
        $sections = $settings->rmwp_get_sections();
        $fields   = $settings->rmwp_get_settings();

        $tabs['plus'] = array(
            __( 'Plus Options', 'read-more-wp' ), // display name
            'rmwp_plus', // option group
            self::OPTION_NAME // option name
        );
        $settings->rmwp_set_tabs( $tabs );

        $sections[] = array(
            'id'       => 'rmwp_plus_section',
            'title'    => __( 'Animation', 'read-more-wp' ),
            'page'     => 'rmwp_plus',
            'callback' => array( $this, 'rmwp_plus_section_cb' ),
        );
        $sections[] = array(
            'id'       => 'rmwp_plus_fade_section',
            'title'    => __( 'Fade-Out Mode', 'read-more-wp' ),
            'page'     => 'rmwp_plus',
            'callback' => array( $this, 'rmwp_plus_fade_section_cb' ),
        );
        $sections[] = array(
            'id'       => 'rmwp_plus_button_section',
            'title'    => __( 'Button Style', 'read-more-wp' ),
            'page'     => 'rmwp_plus',
            'callback' => array( $this, 'rmwp_plus_button_section_cb' ),
        );
        $settings->rmwp_set_sections( $sections );

        $plus_fields = array(
            array( 'rmwp_animation', __( 'Animation', 'read-more-wp' ), 'rmwp_animation_field_cb', 'rmwp_plus_section' ),
            array( 'rmwp_animation_speed', __( 'Animation Speed', 'read-more-wp' ), 'rmwp_animation_speed_field_cb', 'rmwp_plus_section' ),
            array( 'rmwp_scroll_back', __( 'Scroll Back on "Read Less"', 'read-more-wp' ), 'rmwp_scroll_back_field_cb', 'rmwp_plus_section' ),
            array( 'rmwp_fade_height', __( 'Visible Height', 'read-more-wp' ), 'rmwp_fade_height_field_cb', 'rmwp_plus_fade_section' ),
            array( 'rmwp_fade_color', __( 'Fade Color', 'read-more-wp' ), 'rmwp_color_field_cb', 'rmwp_plus_fade_section' ),
            array( 'rmwp_button_style', __( 'Style', 'read-more-wp' ), 'rmwp_button_style_field_cb', 'rmwp_plus_button_section' ),
            array( 'rmwp_button_align', __( 'Alignment', 'read-more-wp' ), 'rmwp_button_align_field_cb', 'rmwp_plus_button_section' ),
            array( 'rmwp_button_color', __( 'Text Color', 'read-more-wp' ), 'rmwp_color_field_cb', 'rmwp_plus_button_section' ),
            array( 'rmwp_button_background', __( 'Background Color', 'read-more-wp' ), 'rmwp_color_field_cb', 'rmwp_plus_button_section' ),
            array( 'rmwp_button_border', __( 'Border Color', 'read-more-wp' ), 'rmwp_color_field_cb', 'rmwp_plus_button_section' ),
            array( 'rmwp_button_radius', __( 'Corner Radius', 'read-more-wp' ), 'rmwp_button_radius_field_cb', 'rmwp_plus_button_section' ),
        );

        foreach ( $plus_fields as $field ) {
            $fields[] = array(
                'id'       => $field[0],
                'title'    => $field[1],
                'callback' => array( $this, $field[2] ),
                'tab'      => 'rmwp_plus',
                'section'  => $field[3],
            );
        }

        $settings->rmwp_set_settings( $fields );

        return $settings;
    }

    // Settings section callbacks.

    public function rmwp_plus_section_cb( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'These are the default Plus settings. You can override them in the Read More block settings or with shortcode options.', 'read-more-wp' ); ?>
        </p>
        <p>
            <?php esc_html_e( 'Example shortcode with overrides:', 'read-more-wp' ); ?><br />
            <code>[start-read-more animation="fade" speed="800" scroll="true"][end-read-more]</code>
        </p>
        <?php
    }

    public function rmwp_plus_fade_section_cb( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'Fade-out mode shows the start of the content up to a set height, fades it out, and adds a button to show the rest.', 'read-more-wp' ); ?>
        </p>
        <p>
            <code>[start-read-more mode="fade" height="200"]<?php esc_html_e( 'Long content…', 'read-more-wp' ); ?>[end-read-more]</code>
        </p>
        <?php
    }

    public function rmwp_plus_button_section_cb( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'Leave a color empty to use the default.', 'read-more-wp' ); ?>
            <?php esc_html_e( 'Shortcode overrides:', 'read-more-wp' ); ?>
            <code>button="link"</code>, <code>align="center"</code>, <code>button_color="#ffffff"</code>, <code>button_background="#0d6efd"</code>.
        </p>
        <?php
    }

    // Settings field callbacks.

    /**
     * Print a select field.
     *
     * @since    1.2.0
     * @param    string    $id         The option key.
     * @param    array     $choices    Value => label pairs.
     */
    private function rmwp_select_field( $id, $choices ) {

        $options = $this->rmwp_get_options();
        $current = (string) $options[ $id ];

        printf( '<select id="%1$s" class="rmwp-setting" name="%2$s[%1$s]">', esc_attr( $id ), esc_attr( self::OPTION_NAME ) );
        foreach ( $choices as $value => $label ) {
            printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $value ), selected( $current, (string) $value, false ), esc_html( $label ) );
        }
        echo '</select>';
    }

    public function rmwp_animation_field_cb( $args ) {

        $this->rmwp_select_field(
            $args['label_for'],
            array(
                'none'  => __( 'None', 'read-more-wp' ),
                'slide' => __( 'Slide', 'read-more-wp' ),
                'fade'  => __( 'Fade', 'read-more-wp' ),
                'fold'  => __( 'Fold', 'read-more-wp' ),
                'popUp' => __( 'Pop-Up', 'read-more-wp' ),
            )
        );
    }

    public function rmwp_animation_speed_field_cb( $args ) {

        $options = $this->rmwp_get_options();
        ?>
        <input type="number" id="<?php echo esc_attr( $args['label_for'] ); ?>" class="rmwp-setting" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>" value="<?php echo esc_attr( $options[ $args['label_for'] ] ); ?>" min="0" max="10000" step="50" required />
        <p class="description"><?php esc_html_e( 'The speed of the animation in milliseconds, from 0 to 10000 (10 seconds).', 'read-more-wp' ); ?></p>
        <?php
    }

    public function rmwp_scroll_back_field_cb( $args ) {

        $options = $this->rmwp_get_options();
        ?>
        <label>
            <input type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>" value="1" <?php checked( '1', $options[ $args['label_for'] ] ); ?> />
            <?php esc_html_e( 'After hiding long content, scroll back to the button if it is off-screen.', 'read-more-wp' ); ?>
        </label>
        <?php
    }

    public function rmwp_fade_height_field_cb( $args ) {

        $options = $this->rmwp_get_options();
        ?>
        <input type="number" id="<?php echo esc_attr( $args['label_for'] ); ?>" class="small-text" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>" value="<?php echo esc_attr( $options[ $args['label_for'] ] ); ?>" min="20" max="5000" step="10" /> px
        <p class="description"><?php esc_html_e( 'How much of the content is visible before visitors click the button.', 'read-more-wp' ); ?></p>
        <?php
    }

    public function rmwp_color_field_cb( $args ) {

        $options = $this->rmwp_get_options();
        ?>
        <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" class="rmwp-color-field" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>" value="<?php echo esc_attr( $options[ $args['label_for'] ] ); ?>" />
        <?php
        if ( $args['label_for'] === 'rmwp_fade_color' ) {
            echo '<p class="description">' . esc_html__( 'Match your page background. Default: white.', 'read-more-wp' ) . '</p>';
        }
    }

    public function rmwp_button_style_field_cb( $args ) {

        $this->rmwp_select_field(
            $args['label_for'],
            array(
                ''     => __( 'Button (default)', 'read-more-wp' ),
                'link' => __( 'Text link', 'read-more-wp' ),
                'full' => __( 'Full-width button', 'read-more-wp' ),
            )
        );
    }

    public function rmwp_button_align_field_cb( $args ) {

        $this->rmwp_select_field(
            $args['label_for'],
            array(
                ''       => __( 'Default', 'read-more-wp' ),
                'left'   => __( 'Left', 'read-more-wp' ),
                'center' => __( 'Center', 'read-more-wp' ),
                'right'  => __( 'Right', 'read-more-wp' ),
            )
        );
        echo '<p class="description">' . esc_html__( 'Applies when the button is on its own line: in blocks, fade-out mode, and after expanding.', 'read-more-wp' ) . '</p>';
    }

    public function rmwp_button_radius_field_cb( $args ) {

        $options = $this->rmwp_get_options();
        ?>
        <input type="number" id="<?php echo esc_attr( $args['label_for'] ); ?>" class="small-text" name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>" value="<?php echo esc_attr( $options[ $args['label_for'] ] ); ?>" min="0" max="100" /> px
        <?php
    }

    // Getters & Setters

    /**
     * Get $base_plugin
     *
     * @return  Read_More_Wp
     */
    public function rmwp_get_base_plugin()
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
    public function rmwp_set_base_plugin(Read_More_Wp $base_plugin)
    {
        $this->base_plugin = $base_plugin;

        return $this;
    }

    /**
     * Get $base_plugin_admin
     *
     * @return  Read_More_Wp_Admin
     */
    public function rmwp_get_base_plugin_admin()
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
    public function rmwp_set_base_plugin_admin(Read_More_Wp_Admin $base_plugin_admin)
    {
        $this->base_plugin_admin = $base_plugin_admin;

        return $this;
    }

    /**
     * Get $base_plugin_settings
     *
     * @return  Read_More_Wp_Settings
     */
    public function rmwp_get_base_plugin_settings()
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
    public function rmwp_set_base_plugin_settings(Read_More_Wp_Settings $base_plugin_settings)
    {
        $this->base_plugin_settings = $base_plugin_settings;

        return $this;
    }

    /**
     * Get $plus_settings
     *
     * @return  Read_More_Wp_Settings
     */
    public function rmwp_get_plus_settings()
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
    public function rmwp_set_plus_settings(Read_More_Wp_Settings $plus_settings)
    {
        $this->plus_settings = $plus_settings;

        return $this;
    }
}

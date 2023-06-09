<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.boltonstudios.com/read-more-wp/
 * @since      1.0.0
 *
 * @package    Read_More_Wp
 * @subpackage Read_More_Wp/admin/partials
 */

$tabs = $this->settings->get_tabs();
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php
    // Check user capabilities
     if ( ! current_user_can( 'manage_options' ) ) {
        return;
     }
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
             
    <?php
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'rmwp_general';
    ?>
     
    <h2 class="nav-tab-wrapper">
    <?php
        $i = 0;
        foreach( $tabs as $tab){
            $option_display_name = $tab[0];
            $option_group = $tab[1];
            $option_name = $tab[2];
            $active_tab_class = ($active_tab == $option_group) ? 'nav-tab-active' : '';
            echo '<a href="?page=read-more-wp&tab='. $option_group .'" id="rmwp-nav-tab-'. $i .'" class="nav-tab '. $active_tab_class .'">'. $option_display_name .'</a>';
            $i++;
        }
    ?>
    </h2>
    <form action="../../wp-admin/options.php" method="post">
    <?php
    
        // Output settings
        settings_fields(  $active_tab );
        do_settings_sections(  $active_tab );

        // Output save settings button
        submit_button( 'Save Settings' );
    ?>
    </form>
</div>
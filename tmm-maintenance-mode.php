<?php

/*
 * Plugin Name: Maintenance Mode by The Mighty Mo! Design Co.
 * Plugin URI: https://www.themightymo.com/
 * Description: Hide the site unless logged in.
 * Author: The Mighty Mo! Design Co. LLC
 * Author URI: https://www.themightymo.com/
 * License: GPLv2 (or later)
 * Version: 1.10
 * GitHub Branch: master
 * GitHub Plugin URI: themightymo/tmm-maintanence-mode
 * GitHub Plugin URI: https://github.com/themightymo/tmm-maintanence-mode
 */

function is_login_page() {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
}

function tmm_maintenance_mode() {
    if ( tmm_is_maintenance_mode_enabled() && !is_user_logged_in() && !is_login_page() ) {
        wp_die('<center><img src="'. plugins_url( 'the-mighty-mo-logo-March-2018-green-200px-new.png' , __FILE__ ).'" /><br>We are building stuff behind the scenes!  Please come back soon!</center><p><center><a href="/wp-login.php">Admin Login</a></center></p>');
    } else {
       // your code for logged out user 
    }
}
add_action('init', 'tmm_maintenance_mode');

function tmm_admin_alert() {
  echo '<style>
    #wpadminbar {
      background:red !important;
    } 
    #wpadminbar:after {
        content: "[THIS SITE IS IN DEVELOPMENT MODE]";
        font-size: 1em;
        font-weight: bold;
        color: #000;
    }
  </style>';
}
add_action('admin_head', 'tmm_admin_alert');
add_action('wp_head', 'tmm_admin_alert');

add_filter( 'get_site_icon_url', '__return_false' );

add_action( 'admin_head', 'prefix_favicon', 100 );
add_action( 'wp_head', 'prefix_favicon', 100 );
function prefix_favicon() {
    //code of the favicon logic
    ?>
        <link rel="icon" class="tobytest" href="<?php echo plugins_url( 'favicon.png' , __FILE__ ); ?>">
    <?php
}

// Add an admin menu for the plugin
function tmm_add_admin_menu() {
    add_menu_page(
        'Maintenance Mode Settings', // Page title
        'Maintenance Mode', // Menu title
        'manage_options', // Capability
        'tmm-maintenance-mode', // Menu slug
        'tmm_admin_page', // Callback function
        'dashicons-admin-tools', // Icon
        81 // Position
    );
}
add_action('admin_menu', 'tmm_add_admin_menu');

// Display the admin page content
function tmm_admin_page() {
    ?>
    <div class="wrap">
        <h1>Maintenance Mode Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('tmm_settings_group');
            do_settings_sections('tmm-maintenance-mode');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings for the plugin
function tmm_register_settings() {
    register_setting('tmm_settings_group', 'tmm_settings');
    add_settings_section('tmm_settings_section', 'Settings', 'tmm_settings_section_callback', 'tmm-maintenance-mode');
    add_settings_field('tmm_checkbox', 'Enable Maintenance Mode', 'tmm_checkbox_callback', 'tmm-maintenance-mode', 'tmm_settings_section');
}
add_action('admin_init', 'tmm_register_settings');

function tmm_settings_section_callback() {
    echo 'Enable or disable maintenance mode:';
}

function tmm_checkbox_callback() {
    $options = get_option('tmm_settings');
    ?>
    <input type="checkbox" name="tmm_settings[tmm_checkbox]" value="1" <?php checked(1, isset($options['tmm_checkbox']) ? $options['tmm_checkbox'] : 0); ?> />
    <?php
}

function tmm_is_maintenance_mode_enabled() {
    $options = get_option('tmm_settings');
    return isset($options['tmm_checkbox']) && $options['tmm_checkbox'] == 1;
}

// Update the maintenance mode function to check the checkbox value
function tmm_maintenance_mode_updated() {
    if (tmm_is_maintenance_mode_enabled() && !is_user_logged_in() && !is_login_page()) {
        wp_die('<center><img src="'. plugins_url( 'the-mighty-mo-logo-March-2018-green-200px-new.png' , __FILE__ ).'" /><br>We are building stuff behind the scenes! Please come back soon!</center><p><center><a href="/wp-login.php">Admin Login</a></center></p>');
    }
}
remove_action('init', 'tmm_maintenance_mode');
add_action('init', 'tmm_maintenance_mode_updated');

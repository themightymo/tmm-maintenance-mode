<?php

/*
 * Plugin Name: Maintenance Mode by The Mighty Mo! Design Co.
 * Plugin URI: https://www.themightymo.com/
 * Description: Hide the site unless logged in.
 * Author: The Mighty Mo! Design Co. LLC
 * Author URI: https://www.themightymo.com/
 * License: GPLv2 (or later)
 * Version: 2.2
 * GitHub Branch: master
 * GitHub Plugin URI: themightymo/tmm-maintanence-mode
 * GitHub Plugin URI: https://github.com/themightymo/tmm-maintanence-mode
 */

function is_login_page() {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
}

function tmm_maintenance_mode() {
    if ( tmm_is_maintenance_mode_enabled() && !is_user_logged_in() && !is_login_page() ) {
        $image_url = tmm_get_maintenance_image();
        $maintenance_text = tmm_get_maintenance_text();
        wp_die('<center><img src="'. esc_url($image_url) .'" style="width:100%; height:auto;" /><br>' . wp_kses_post($maintenance_text) . '</center><p><center><a href="/wp-login.php">Admin Login</a></center></p>');
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
    if (is_multisite()) {
        add_menu_page(
            'Maintenance Mode Settings', // Page title
            'TMM Maintenance Mode', // Menu title
            'manage_network_options', // Capability
            'tmm-maintenance-mode', // Menu slug
            'tmm_admin_page', // Callback function
            'dashicons-hammer', // Icon
            81 // Position
        );
    } else {
        add_menu_page(
            'Maintenance Mode Settings', // Page title
            'TMM Maintenance Mode', // Menu title
            'manage_options', // Capability
            'tmm-maintenance-mode', // Menu slug
            'tmm_admin_page', // Callback function
            'dashicons-hammer', // Icon
            81 // Position
        );
    }
}
add_action('admin_menu', 'tmm_add_admin_menu');
add_action('network_admin_menu', 'tmm_add_admin_menu');

// Display the admin page content
function tmm_admin_page() {
    ?>
    <div class="wrap">
        <h1>Maintenance Mode Settings</h1>
        <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
            <?php
            settings_fields('tmm_settings_group');
            do_settings_sections('tmm-maintenance-mode');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Enqueue media uploader scripts
function tmm_enqueue_media_uploader() {
    wp_enqueue_media();
    wp_enqueue_script('tmm-media-uploader', plugins_url('media-uploader.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'tmm_enqueue_media_uploader');

// Register settings for the plugin
function tmm_register_settings() {
    register_setting('tmm_settings_group', 'tmm_settings');
    add_settings_section('tmm_settings_section', 'Settings', 'tmm_settings_section_callback', 'tmm-maintenance-mode');
    add_settings_field('tmm_checkbox', 'Enable Maintenance Mode', 'tmm_checkbox_callback', 'tmm-maintenance-mode', 'tmm_settings_section');
    add_settings_field('tmm_image', 'Maintenance Image', 'tmm_image_callback', 'tmm-maintenance-mode', 'tmm_settings_section');
    add_settings_field('tmm_text', 'Maintenance Mode Text', 'tmm_text_callback', 'tmm-maintenance-mode', 'tmm_settings_section');
}
add_action('admin_init', 'tmm_register_settings');

function tmm_settings_section_callback() {
    echo 'Enable or disable maintenance mode and set an image:';
}

function tmm_checkbox_callback() {
    $options = get_option('tmm_settings');
    ?>
    <input type="checkbox" name="tmm_settings[tmm_checkbox]" value="1" <?php checked(1, isset($options['tmm_checkbox']) ? $options['tmm_checkbox'] : 0); ?> />
    <?php
}

function tmm_image_callback() {
    $options = get_option('tmm_settings');
    $image_url = isset($options['tmm_image']) ? esc_url($options['tmm_image']) : '';
    ?>
    <input type="text" id="tmm_image" name="tmm_settings[tmm_image]" value="<?php echo $image_url; ?>" />
    <input type="button" class="button-secondary" value="Upload Image" id="tmm_image_upload" />
    <img src="<?php echo $image_url; ?>" style="max-width: 300px; display: <?php echo $image_url ? 'block' : 'none'; ?>; margin-top: 10px;" />
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#tmm_image_upload').click(function(e) {
                e.preventDefault();
                var image = wp.media({
                    title: 'Upload Image',
                    multiple: false
                }).open()
                .on('select', function() {
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    $('#tmm_image').val(image_url);
                    $('#tmm_image').next('img').attr('src', image_url).show();
                });
            });
        });
    </script>
    <?php
}

function tmm_text_callback() {
    $options = get_option('tmm_settings');
    $text = isset($options['tmm_text']) ? esc_textarea($options['tmm_text']) : 'We are building stuff behind the scenes! Please come back soon!';
    ?>
    <textarea name="tmm_settings[tmm_text]" rows="5" cols="50"><?php echo $text; ?></textarea>
    <?php
}

function tmm_is_maintenance_mode_enabled() {
    $options = get_option('tmm_settings');
    return isset($options['tmm_checkbox']) && $options['tmm_checkbox'] == 1;
}

function tmm_get_maintenance_image() {
    $options = get_option('tmm_settings');
    return isset($options['tmm_image']) ? $options['tmm_image'] : plugins_url( 'the-mighty-mo-logo-March-2018-green-200px-new.png' , __FILE__ );
}

function tmm_get_maintenance_text() {
    $options = get_option('tmm_settings');
    return isset($options['tmm_text']) ? $options['tmm_text'] : 'We are building stuff behind the scenes! Please come back soon!';
}

// Update the maintenance mode function to check the checkbox value
function tmm_maintenance_mode_updated() {
    if (tmm_is_maintenance_mode_enabled() && !is_user_logged_in() && !is_login_page()) {
        $image_url = tmm_get_maintenance_image();
        $maintenance_text = tmm_get_maintenance_text();
        wp_die('<center><img src="'. esc_url($image_url) .'" style="width:100%; height:auto;" /><br>' . wp_kses_post($maintenance_text) . '</center><p><center><a href="/wp-login.php">Admin Login</a></center></p>');
    }
}
remove_action('init', 'tmm_maintenance_mode');
add_action('init', 'tmm_maintenance_mode_updated');


/* 
	Show notice in admin bar if the "Discourage search engines from indexing this site" option is checked at /wp-admin/options-reading.php.
*/
// Hook into the admin bar to add our custom text
add_action('admin_bar_menu', 'tmm_add_admin_bar_notice_for_search_engines_blocked', 100);

function tmm_add_admin_bar_notice_for_search_engines_blocked($wp_admin_bar) {
    // Check if the option to discourage search engines is checked
    if (get_option('blog_public') == '0') {
        // Add a notice to the admin bar
        $wp_admin_bar->add_node([
            'id'    => 'sei-notice',
            'title' => '<span style="background-color:red;color: white;">You are blocking search engines.</span>',
            'href'  => admin_url('options-reading.php'), // Link to the Reading Settings page
        ]);
    }
}
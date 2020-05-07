<?php

/*
 * Plugin Name: Maintenance Mode by The Mighty Mo! Design Co.
 * Plugin URI: http://www.themightymo.com/
 * Description: Hide the site unless logged in.
 * Author: The Mighty Mo! Design Co. LLC
 * Author URI: http://www.themightymo.com/
 * License: GPLv2 (or later)
 * Version: 1.1
 */
 
function tmm_maintenance_mode() {
    if ( !current_user_can( 'edit_themes' ) || !is_user_logged_in() ) {
        wp_die('<center><img src="http://www.themightymo.com/wp-content/uploads/2013/06/the-mighty-mo-logo.png" /><br>We are building stuff behind the scenes!  Please come back soon!</center>');
    }
}
add_action('get_header', 'tmm_maintenance_mode');


function tmm_frontend_alert() {
  echo '<style>
    #wpadminbar {
      background:red !important;
    } 
  </style>';
}
add_action('admin_head', 'tmm_frontend_alert');


function tmm_admin_alert() {
  wp_register_style( 'tmm_admin_alert', plugins_url() . '/style.css', false, '1.0.0' );
  wp_enqueue_style( 'tmm_admin_alert' );
}
add_action('admin_enqueue_scripts', 'tmm_admin_alert');    

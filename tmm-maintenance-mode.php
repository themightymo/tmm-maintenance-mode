<?php
/*
 * Plugin Name: Maintenance Mode by The Mighty Mo! Design Co.
 * Plugin URI: http://www.themightymo.com/
 * Description: Hide the site unless logged in.
 * Author: The Mighty Mo! Design Co. LLC
 * Author URI: http://www.themightymo.com/
 * License: GPLv2 (or later)
 * Version: 1.2
 */
 
function tmm_maintenance_mode() {
    if ( !current_user_can( 'edit_themes' ) || !is_user_logged_in() ) {
        wp_die('<center><img src="http://www.themightymo.com/wp-content/uploads/2013/06/the-mighty-mo-logo.png" /><br>We are building stuff behind the scenes!  Please come back soon!</center>');
    }
}
add_action('get_header', 'tmm_maintenance_mode');


function tmm_admin_alert() {
  echo '<style>
    #wpadminbar {
      background:red !important;
    } 
    #wpadminbar:after {
	    content: "[THIS SITE IS IN DEVELOPMENT MODE - Sincerely, TMM Maintenance Mode Plugin]";
	    font-size: 1em;
	    font-weight: bold;
	    color: #000;
    }
  </style>';
}
add_action('admin_head', 'tmm_admin_alert');

<?php

/*
 * Plugin Name: Maintenance Mode by The Mighty Mo! Design Co.
 * Plugin URI: https://www.themightymo.com/
 * Description: Hide the site unless logged in.
 * Author: The Mighty Mo! Design Co. LLC
 * Author URI: https://www.themightymo.com/
 * License: GPLv2 (or later)
 * Version: 1.7
 * GitHub Branch: master
 * GitHub Plugin URI: themightymo/tmm-maintanence-mode
 * GitHub Plugin URI: https://github.com/themightymo/tmm-maintanence-mode
 */
 
function tmm_maintenance_mode() {
    if ( !is_user_logged_in() ) {
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

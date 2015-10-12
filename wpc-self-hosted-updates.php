<?php
/*
Plugin Name: WP Canvas - Self Hosted Updates
Plugin URI: http://webplantmedia.com/starter-themes/wordpresscanvas/features/plugins/wpc-self-hosted-updates/
Description: Easily update self hosted themes and plugins through the WordPress dashboard.
Author: Chris Baldelomar
Author URI: http://webplantmedia.com/
Version: 1.3
License: GPLv2 or later
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin.php' );

	add_action( 'plugins_loaded', array( 'WPC_Self_Hosted_Updates_Admin', 'get_instance' ) );
}

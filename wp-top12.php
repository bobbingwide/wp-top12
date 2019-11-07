<?php
/**
Plugin Name: wp-top12
Depends: oik
Plugin URI: https://www.oik-plugins.com/oik-plugins/wp-top12
Description: Display selected plugins by most downloaded from WordPress.org
Version: 1.0.0-alpha-20191107
Author: bobbingwide
Author URI: https://www.bobbingwide.com/about-bobbing-wide
Text Domain: wp-top12
Domain Path: /languages/
License: GPL2v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2019 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/
wp_top12_plugin_loaded();

/**
 * Implement the "init" action for wp-top12
 * 
 * Even though "oik" may not yet be loaded, let other plugins know that we've been loaded.
 */
function wp_top12_init() {
	do_action( "wp_top12_loaded" );
}  

/**
 * Implement the "oik_loaded" action for wp-top12
 * 
 * Now it's safe to use oik APIs to register the wp-top12 shortcode
 * but it's not necessary until we actually come across a shortcode
 */
function wp_top12_oik_loaded() {
	bw_load_plugin_textdomain( "wp-top12" );
}

/**
 * Implement the "oik_add_shortcodes" action for wp-top12
 * 
 */
function wp_top12_oik_add_shortcodes() {
	bw_add_shortcode( 'wp-top12', 'wp_top12_sc', oik_path( "shortcodes/wp-top12.php", "wp-top12" ), false );
}

/**
 * Dependency checking for wp-top12
 *
 * Version | Dependent
 * ------- | ---------
 * 0.0.0   | oik v3.3 or higher
 */
function wp_top12_activation() {
	static $plugin_basename = null;
	if ( !$plugin_basename ) {
		$plugin_basename = plugin_basename(__FILE__);
		add_action( "after_plugin_row_wp-top12/wp-top12.php", "wp_top12_activation" );
		if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
			require_once( "admin/oik-activation.php" );
		}  
	}  
	$depends = "oik:3.3";
	oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Initialisation when wp-top12 plugin file loaded
 */
function wp_top12_plugin_loaded() {
	add_action( "init", "wp_top12_init" );
	add_action( "oik_loaded", "wp_top12_oik_loaded" );
	add_action( "oik_add_shortcodes", "wp_top12_oik_add_shortcodes" );
	add_action( "admin_notices", "wp_top12_activation" );
}










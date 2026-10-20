<?php
/**
 * Plugin Name: Tucker Core Functionality
 * Plugin URI: https://github.com/mattryanco/tucker-core-functionality
 * Description: This contains core functionality for tuckerclug.org. It should remain activated.
 * Version: 1.0.0
 * Author: Matt Ryan
 * Author URI: https://mattryan.co
 * GitHub Plugin URI: https://github.com/mattryanco/tucker-core-functionality
 * Primary Branch: main
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */

// Define needed constants
define( 'CORE_FUNCTIONALITY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); //location of plugin folder on disk
define( 'CORE_FUNCTIONALITY_PLUGIN_URI', plugin_dir_url( __FILE__ ) );  //location of plugin folder in wp-content
define( 'CORE_FUNCTIONALITY_THEME_DIR', get_stylesheet_directory() );   // Used in checking location of logo file
define( 'CORE_FUNCTIONALITY_THEME_URI', get_stylesheet_directory_uri() );   // Used in checking location of logo file

add_action( 'after_setup_theme','core_setup' );
function core_setup() {
	if( ! function_exists('get_plugin_data') ){
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	define( 'CORE_FUNCTIONALITY_PLUGIN_VERSION', get_plugin_data(__FILE__ )['Version'] ); 
}

/**
 * Get all the include files for the theme.
 *
 * @author CapWebSolutions
 */
function include_core_functionality_inc_files() {
	$files = [
		'lib/functions/',
	];

	foreach ( $files as $include ) {
		$include = trailingslashit( CORE_FUNCTIONALITY_PLUGIN_DIR ) . $include;
		// Allows inclusion of individual files or all .php files in a directory.
		if ( is_dir( $include ) ) {
			foreach ( glob( $include . '*.php' ) as $file ) {
				require $file;  // all php files from directory
			}
		} else {
			require $include;    // single php file
		}
	}
}
include_core_functionality_inc_files();
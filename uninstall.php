<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * The class responsible for handling the configuration
 * of the plugin.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-sokno-shopping-ads-config.php';

Sokno_Shopping_Ads_Config::del_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_ID );
Sokno_Shopping_Ads_Config::del_option( Sokno_Shopping_Ads_Config::CONFIG_LINKED );
Sokno_Shopping_Ads_Config::del_option( Sokno_Shopping_Ads_Config::CONFIG_NEEDS_REV );
Sokno_Shopping_Ads_Config::del_option( Sokno_Shopping_Ads_Config::CONFIG_SETTINGS );
Sokno_Shopping_Ads_Config::del_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_SIGNATURE );

// Clear any cached data that has been removed.
wp_cache_flush();

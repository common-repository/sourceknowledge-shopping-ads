<?php
/**
 * SourceKnowledge Shopping Ads plugin for WooCommerce
 *
 * @link              https://sourceknowledge.com/
 * @since             1.0.0
 * @package           Sokno_Shopping_Ads
 *
 * @wordpress-plugin
 * Plugin Name:       SourceKnowledge Shopping Ads
 * Plugin URI:        https://sourceknowledge.com/shopping-ads-app
 * Description:       Reach in-market shoppers to drive new sales.
 * Version:           1.0.8
 * Author:            SourceKnowledge
 * Author URI:        https://sourceknowledge.com/
 * Developer:         SourceKnowledge
 * Developer URI:     https://sourceknowledge.com/
 * Text Domain:       sourceknowledge-shopping-ads
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0
 * WC tested up to: 5.1.0
 *
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SOKNO_SHOPPING_ADS_VERSION', '1.0.8' );

/**
 * Writes a log entry
 *
 * @param mixed  $log   Log message or object to debug.
 * @param string $level Logging level.
 *
 * @since    1.0.0
 */
function sokno_write_log( $log, $level = 'debug' ) {
	if ( ! function_exists( 'wc_get_logger' ) ) {
		return;
	}
	$logger = wc_get_logger();
	if ( is_array( $log ) || is_object( $log ) ) {
		$msg = (string) $log;
		$logger->log( $level, $msg, $log );
	} else {
		$logger->log( $level, $log );
	}
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sokno-shopping-ads-activator.php
 */
function activate_sokno_shopping_ads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sokno-shopping-ads-config.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sokno-shopping-ads-activator.php';
	Sokno_Shopping_Ads_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sokno-shopping-ads-deactivator.php
 */
function deactivate_sokno_shopping_ads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sokno-shopping-ads-config.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sokno-shopping-ads-deactivator.php';
	Sokno_Shopping_Ads_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sokno_shopping_ads' );
register_deactivation_hook( __FILE__, 'deactivate_sokno_shopping_ads' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sokno-shopping-ads.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sokno_shopping_ads() {

	$basename = plugin_basename( __FILE__ );
	$plugin   = new Sokno_Shopping_Ads( $basename );
	$plugin->run();

}

/**
 * Only run the plugin if WooCommerce is present.
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ?? array(), true ) ) {
	run_sokno_shopping_ads();
}

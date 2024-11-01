<?php
/**
 * Define the configuration functionality
 *
 * Loads and defines the configuration for this plugin.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 */

/**
 * Define the configuration functionality.
 *
 * Loads and defines the configuration for this plugin.
 *
 * @since      1.0.0
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Config {

	const INTEGRATION_NAME = 'sourceknowledge';

	const CONFIG_SITE_ID          = 'site_id';
	const CONFIG_LINKED           = 'linked';
	const CONFIG_NEEDS_REV        = 'config_needs_revision';
	const CONFIG_INSTALL_ENDPOINT = 'install_endpoint';
	const CONFIG_SETTINGS         = 'settings';
	const CONFIG_STATUS_ENDPOINT  = 'status_endpoint';
	const CONFIG_SITE_SIGNATURE   = 'site_signature';
	const CONFIG_INSTALL_TOKEN    = 'install_token';

	const SK_DASHBOARD     = 'https://app.sourceknowledge.com/';
	const INSTALL_ENDPOINT = '/woocommerce/shopping-ads/init?';
	const FINISH_ENDPOINT  = '/woocommerce/shopping-ads/finish?';

	const SK_PLUGINS      = 'https://plugins.sourceknowledge.com/';
	const STATUS_ENDPOINT = '/woocommerce/shopping-ads/status?';

	const LINK_NONCE_ACTION = 'sk_link_nonce';

	/**
	 * Get plugin settings
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_settings() {
		return self::get_option( self::CONFIG_SETTINGS, array() );
	}

	/**
	 * Updates plugin settings
	 *
	 * @param mixed $new_settings New settings to apply.
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function update_settings( $new_settings ) {
		return self::set_option( self::CONFIG_SETTINGS, $new_settings );
	}

	/**
	 * Gets an option from the options bucket
	 *
	 * @param string     $name Option name.
	 * @param mixed|null $default Default value.
	 *
	 * @return mixed|void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_option( $name, $default = null ) {
		return get_option( self::get_option_key( $name ), $default );
	}

	/**
	 * Sets an option in the options bucket
	 *
	 * @param string $name Option name.
	 * @param mixed  $new_value New value to set for $name.
	 * @param string $autoload Autoload.
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function set_option( $name, $new_value, $autoload = 'yes' ) {
		return update_option( self::get_option_key( $name ), $new_value, $autoload );
	}

	/**
	 * Deletes an option in the options bucket
	 *
	 * @param string $name Name of option to delete.
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function del_option( $name ) {
		return delete_option( self::get_option_key( $name ) );
	}

	/**
	 * Gets the SK dashboard absolute url
	 *
	 * @param string $name Endpoint name.
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_dashboard_endpoint( $name ) {
		$base_url = self::get_option( self::CONFIG_INSTALL_ENDPOINT, self::SK_DASHBOARD );

		return $base_url . ltrim( $name, '/' );
	}

	/**
	 * Gets the Plugins status endpoint absolute url
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_plugins_status_endpoint() {
		$base_url = self::get_option( self::CONFIG_STATUS_ENDPOINT, self::SK_PLUGINS );

		return $base_url . ltrim( self::STATUS_ENDPOINT, '/' );
	}

	/**
	 * Gets the internal option key
	 *
	 * @param string $name Option name.
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private static function get_option_key( $name ) {
		return self::INTEGRATION_NAME . '_' . $name;
	}

}

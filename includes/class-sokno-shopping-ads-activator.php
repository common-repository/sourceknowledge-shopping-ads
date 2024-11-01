<?php
/**
 * Fired during plugin activation
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$linked = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_LINKED, false );
		if ( $linked ) {
			self::send_activation_signal();
			return;
		}

		Sokno_Shopping_Ads_Config::set_option( Sokno_Shopping_Ads_Config::CONFIG_NEEDS_REV, true );
	}

	/**
	 * Sends activation signal
	 *
	 * @since    1.0.0
	 */
	private static function send_activation_signal() {
		$url    = Sokno_Shopping_Ads_Config::get_plugins_status_endpoint();
		$sign   = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_SIGNATURE, '' );
		$site   = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_ID, '' );
		$params = array(
			'site'   => $site,
			'sign'   => $sign,
			'status' => 1,
		);

		$response = wp_remote_post(
			$url,
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'blocking'    => true,
				'headers'     => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'        => wp_json_encode( $params ),
				'data_format' => 'body',
			)
		);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			sokno_write_log( "Something went wrong: $error_message" );
		}
	}

}

<?php
/**
 * Contains several utilities.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 */

/**
 * Contains several utilities.
 *
 * Utils used in several parts of the plugin.
 *
 * @since      1.0.0
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Utils {

	/**
	 * Returns true if WooCommerce plugin found.
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function is_wc_available() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Generates the site identifier
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function generate_site_id() {
		$url    = self::get_site_url();
		$domain = wp_parse_url( $url, PHP_URL_HOST );
		if ( empty( $domain ) ) {
			if ( isset( $_SERVER['HTTP_HOST'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$domain = esc_html( wp_unslash( $_SERVER['HTTP_HOST'] ) );
			}
		}

		return $domain;
	}

	/**
	 * WooCommerce 2.1 support for wc_enqueue_js
	 *
	 * @param string $code JS code to queue.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function wc_enqueue_js( $code ) {
		global $wc_queued_js;

		if ( function_exists( 'wc_enqueue_js' ) && empty( $wc_queued_js ) ) {
			wc_enqueue_js( $code );
		} else {
			$wc_queued_js = $code . "\n" . $wc_queued_js;
		}
	}

	/**
	 * Gets the site descriptive name
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_site_name() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Gets the admin email
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_admin_email() {
		return get_bloginfo( 'admin_email' );
	}

	/**
	 * Gets the site url
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_site_url() {
		return site_url();
	}

	/**
	 * Gets the WordPress version
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_wordpress_version() {
		global $wp_version;

		return $wp_version;
	}

	/**
	 * Gets the WooCommerce version
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_woocommerce_version() {
		return WC()->version;
	}

	/**
	 * Gets a WooCommerce api absolute url.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_woocommerce_api_url( string $path ) {
		return get_woocommerce_api_url( $path );
	}

	/**
	 * Gets the WooCommerce absolute url for the authorization endpoint
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_woocommerce_auth_url() {
		return self::get_woocommerce_api_url( 'authorize' );
	}

	/**
	 * Gets the WooCommerce absolute url for the authorization endpoint
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_woocommerce_currency() {
		return get_woocommerce_currency() ?? 'USD';
	}

	/**
	 * Gets the WordPress permalink structure if enabled
	 *
	 * @return string|null
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function get_permalink_structure() {
		return get_option( 'permalink_structure' );
	}

	/**
	 * Gets if the WordPress permalink structure is enabled
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public static function is_permalink_enabled() {
		return ! empty( self::get_permalink_structure() );
	}

	/**
	 * Gets the notification message.
	 *
	 * @param string $message Message content.
	 * @param string $type Message type.
	 *
	 * @since    1.0.0
	 */
	public static function render_message_html( string $message, $type = 'error' ) {
		$params = array(
			'type'    => $type,
			'message' => $message,
		);

		self::render_template( 'sokno-shopping-ads-admin-notification', $params );
	}

	/**
	 * Renders a partial and returns the output.
	 *
	 * @param string $template Template file name with the PHP extension.
	 * @param array  $params Params for template.
	 * @param string $scope Scope, or root path.
	 *
	 * @since    1.0.0
	 */
	public static function render_template( string $template, $params = array(), $scope = 'admin' ) {
		$path     = $scope . '/partials/' . $template . '.php';
		$template = plugin_dir_path( dirname( __FILE__ ) ) . $path;
		include $template;
	}

	/**
	 * Lookups for the field and returns a default value if key is missing.
	 *
	 * @param array  $data Data to lookup.
	 * @param string $field Field name.
	 * @param mixed  $default Default value when not exists.
	 * @param bool   $sanitized If value should be sanitized.
	 *
	 * @return mixed|null
	 * @since    1.0.0
	 */
	public static function get_field_val( array $data, string $field, $default = null, $sanitized = true ) {
		$val = isset( $data[ $field ] ) ? $data[ $field ] : $default;

		if ( ! $sanitized || empty( $val ) ) {
			return $val;
		}

		return esc_html( wp_unslash( $val ) );
	}

	/**
	 * Generates a hash from a given value.
	 *
	 * @param string $val Data to hash.
	 *
	 * @return string
	 * @since    1.0.0
	 */
	public static function hash( string $val ) {
		return hash( 'sha256', $val );
	}

	/**
	 * Generates an error info for sending to SK.
	 *
	 * @param string         $class Class which originated the error.
	 * @param Throwable|null $error Error if it was generated.
	 * @param int            $line Line number which originated the error.
	 *
	 * @return string
	 * @since    1.0.5
	 */
	public static function get_error_info( string $class, \Throwable $error = null, int $line = 0 ) {
		$version = defined( 'SOKNO_SHOPPING_ADS_VERSION' ) ? SOKNO_SHOPPING_ADS_VERSION : '1.0.0';
		if ( ! empty( $error ) ) {
			$line = $error->getLine();
		}

		return "{$version}_{$class}_{$line}";
	}
}

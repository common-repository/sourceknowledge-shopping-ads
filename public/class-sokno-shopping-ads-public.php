<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/admin
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/public
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Public {

	const PARAM_COUPON_CODE = 'sk_coupon';

	const SESSION_COUPON_CODE = 'sk_coupon_code';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sokno-shopping-ads-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Grab coupon code from querystring and passes it to the session storage.
	 *
	 * @see https://stackoverflow.com/questions/48220205/get-a-coupon-code-via-url-and-apply-it-in-woocommerce-checkout-page/48225502
	 * @see https://wordpress.org/plugins/woo-coupon-url/
	 * @since    1.0.0
	 */
	public function capture_coupon_code() {
		$coupon_code = Sokno_Shopping_Ads_Utils::get_field_val( $_GET, self::PARAM_COUPON_CODE, '', false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $coupon_code ) ) {
			$coupon_code = esc_attr( $coupon_code );
		}

		if ( is_admin() || empty( $coupon_code ) || ! $this->is_referral_coupon_valid( $coupon_code ) ) {
			return;
		}

		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		WC()->session->set( self::SESSION_COUPON_CODE, $coupon_code ); // store the coupon code in session.
	}

	/**
	 * Applies the coupon code to the cart.
	 *
	 * @since    1.0.0
	 */
	public function apply_coupon_to_cart() {
		$coupon_code = WC()->session->get( self::SESSION_COUPON_CODE );

		if ( ! empty( $coupon_code ) && ! WC()->cart->has_discount( $coupon_code ) ) {
			WC()->cart->add_discount( $coupon_code ); // apply the coupon discount.
			WC()->session->__unset( self::SESSION_COUPON_CODE ); // remove coupon code from session.
		}
	}

	/**
	 * Checks if coupon code is valid. This implementation will cover future updates because others solutions founded
	 * relied on some deprecated features.
	 *
	 * @see https://stackoverflow.com/a/52376908/7014913
	 *
	 * @param string $coupon_code Coupon code.
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	private function is_referral_coupon_valid( $coupon_code ) {
		try {
			$coupon         = new \WC_Coupon( $coupon_code );
			$discounts      = new \WC_Discounts( WC()->cart );
			$valid_response = $discounts->is_coupon_valid( $coupon );

			return ! is_wp_error( $valid_response );
		} catch ( \Exception $ex ) {
			return false;
		}
	}
}

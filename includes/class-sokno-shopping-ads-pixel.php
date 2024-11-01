<?php
/**
 * Pixel generation related features.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 */

/**
 * Pixel generation related features.
 *
 * This class defines all code necessary to get invocation codes for pixel fires.
 *
 * @since      1.0.0
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Pixel {

	/**
	 * Main javascript invocation code
	 */
	const PIXEL_TEMPLATE = <<<SK_PIXEL
        (function(d,t,u,p,e,f){e=d.createElement(t);f=d.getElementsByTagName(t)[0];
        e.async=1;e.src=u+'?'+p+'&cb='+Math.floor(Math.random()*999999);f.parentNode.insertBefore(e,f);
        })(document,'script', '%s', '%s');
SK_PIXEL;

	/**
	 * Base endpoint for pixel delivery
	 */
	const PIXEL_BASE_ENDPOINT = '//upx.provenpixel.com/woo.js.php';

	/**
	 * Mandatory fields in the request
	 */
	const SHOP_KEY       = 'shop';
	const EVENT_TYPE_KEY = 'event';

	/**
	 * Event Types
	 */
	const EVENT_TYPE_VIEW   = 'view';
	const EVENT_TYPE_CART   = 'cart';
	const EVENT_TYPE_SALE   = 'sale';
	const EVENT_TYPE_SEARCH = 'search';

	/**
	 * Event hook priorities
	 */
	const HOOK_PRIORITY_HIGH = 2;
	const HOOK_PRIORITY_LOW  = 11;

	/**
	 * Settings keys
	 */
	const SET_TRACKING_ENABLED = 'tracking_enabled';

	/**
	 * Default settings for pixels
	 */
	const DEFAULT_SETTINGS = array(
		self::SET_TRACKING_ENABLED => true,
	);

	/**
	 * Main tracking variables
	 */
	const VAR_PRODUCT_ID   = 'product_id';
	const VAR_ORDER_ID     = 'order_id';
	const VAR_ORDER_AMOUNT = 'order_amount';
	const VAR_COUPON_CODE  = 'coupon_code';
	const VAR_ORDERS_COUNT = 'orders_count';
	const VAR_EMAIL_HASH   = 'ehash';
	const VAR_USER_ID      = 'uid';
	const VAR_TR_DATA      = 'trdata';
	const VAR_ERROR        = 'err';
	const VAR_VERSION      = 'ver';

	/**
	 * If pixels are enabled
	 *
	 * @var bool
	 * @since    1.0.0
	 * @access   private
	 */
	private $enabled = true;

	/**
	 * Current plugin version
	 *
	 * @var string
	 * @since    1.0.6
	 * @access   private
	 */
	private $version = null;

	/**
	 * Holds the last event to avoid event fires multiple times
	 *
	 * @var string|null
	 * @since    1.0.0
	 * @access   private
	 */
	private $last_event = null;

	/**
	 * Stores the current settings
	 *
	 * @var array
	 * @since    1.0.0
	 * @access   private
	 */
	private $settings;

	/**
	 * Holds the shop parameter to send to Sk endpoint
	 *
	 * @var string
	 * @since    1.0.0
	 * @access   private
	 */
	private $shop;

	/**
	 * Holds the last parameters sent to Sk endpoint
	 *
	 * @var array
	 * @since    1.0.5
	 * @access   private
	 */
	private $last_params;

	/**
	 * Sokno_Shopping_Ads_Pixel constructor.
	 *
	 * @param array $options Settings options.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function __construct( $options = array() ) {
		$this->settings = array_merge( self::DEFAULT_SETTINGS, $options );
		$this->shop     = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_ID );
		$this->enabled  = ! is_admin() && ! empty( $this->shop ) && $this->settings[ self::SET_TRACKING_ENABLED ];
		if ( defined( 'SOKNO_SHOPPING_ADS_VERSION' ) ) {
			$this->version = SOKNO_SHOPPING_ADS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
	}

	/**
	 * Returns if pixels are enabled
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function is_enabled() {
		return $this->enabled;
	}

	/**
	 * Hooks all actions WP/WC to logic
	 *
	 * @param Sokno_Shopping_Ads_Loader $loader Ads loader.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function hook_actions( Sokno_Shopping_Ads_Loader $loader ) {

		$loader->add_action( 'wp_head', $this, 'apply_filters' );
		// If is not enabled then don't hook any actions.
		if ( ! $this->enabled ) {
			return;
		}

		// Tracking hooks.
		$loader->add_action( 'wp_head', $this, 'inject_base_pixel' );
		$loader->add_action( 'pre_get_posts', $this, 'inject_search_event' );
		$loader->add_action( 'woocommerce_after_cart', $this, 'inject_add_to_cart_redirect_event' );
		$loader->add_action( 'woocommerce_add_to_cart', $this, 'inject_add_to_cart_event', self::HOOK_PRIORITY_HIGH );
		$loader->add_action( 'wc_ajax_sokno_inject_add_to_cart_event', $this, 'inject_ajax_add_to_cart_event' );
		$loader->add_action( 'woocommerce_thankyou', $this, 'inject_gateway_purchase_event', self::HOOK_PRIORITY_HIGH );
		$loader->add_action( 'woocommerce_payment_complete', $this, 'inject_purchase_event', self::HOOK_PRIORITY_HIGH );
	}

	/**
	 * Applies the filters
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function apply_filters() {
		$this->enabled = apply_filters(
			'sourceknowledge_wc_integration_pixel_enabled',
			$this->enabled
		);
	}

	/**
	 * Inject all pages pixel (LEAD)
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function inject_base_pixel() {
		global $post;

		if ( empty( $post ) || empty( $post->ID ) ) {
			return;
		}

		$product    = wc_get_product( $post->ID );
		$product_id = '';
		if ( $product ) {
			$product_id = $product->get_id();
		}

		$data = array(
			self::VAR_PRODUCT_ID => $product_id,
		);

		$data = $this->add_user_data( $data );

		$this->render_event( self::EVENT_TYPE_VIEW, $data );
	}

	/**
	 * Injects pixel for search page
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function inject_search_event() {
		if ( ! is_admin() && is_search() && get_search_query() !== '' ) {
			if ( $this->check_last_event( self::EVENT_TYPE_SEARCH ) ) {
				return;
			}

			$data = array(
				self::VAR_TR_DATA => array(
					'search_term' => get_search_query(),
				),
			);

			$data = $this->add_user_data( $data );

			$this->inject_event( self::EVENT_TYPE_SEARCH, $data );
		}
	}

	/**
	 * Injects cart pixel in cart page and add_to_cart button clicks
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function inject_add_to_cart_event() {
		$cart        = WC()->cart->get_cart();
		$product_ids = $this->get_content_ids_from_cart( $cart );
		$data        = array(
			self::VAR_PRODUCT_ID => implode( ',', $product_ids ),
		);

		$data = $this->add_user_data( $data );

		$this->inject_event( self::EVENT_TYPE_CART, $data );
	}

	/**
	 * Injects cart pixel when set 'redirect to cart', ajax call for button click and
	 * woocommerce_add_to_cart will be skipped.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function inject_add_to_cart_redirect_event() {
		$redirect_checked = get_option( 'woocommerce_cart_redirect_after_add', 'no' );
		if ( 'yes' === $redirect_checked ) {
			$this->inject_add_to_cart_event();
		}
	}

	/**
	 * Inject cart pixel when add_to_cart jquery trigger
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function inject_ajax_add_to_cart_event() {
		ob_start();

		$cart        = WC()->cart->get_cart();
		$product_ids = $this->get_content_ids_from_cart( $cart );
		$data        = array(
			self::VAR_PRODUCT_ID => implode( ',', $product_ids ),
		);

		$data = $this->add_user_data( $data );

		$this->render_event( self::EVENT_TYPE_CART, $data );
		$pixel = ob_get_clean();

		wp_send_json( $pixel );
	}

	/**
	 * Injects sale pixel when transaction completed.
	 *
	 * @param string $order_id The order id.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function inject_purchase_event( $order_id ) {
		if ( $this->check_last_event( self::EVENT_TYPE_SALE ) ) {
			return;
		}

		$data  = array(
			self::VAR_ORDER_ID => $order_id,
		);
		$event = self::EVENT_TYPE_SALE;
		$order = null;

		try {
			$order = wc_get_order( $order_id );

			$data = $this->add_user_data( $data );
			$data = $this->process_order_data( $order, $data );
			$data = $this->process_coupon_codes( $order, $data );
			$data = $this->process_customer_data( $order, $data );
			$data = $this->process_subscription_data( $order, $data );
		} catch ( \Throwable $ex ) {
			// Silently exit, no valid order data.
			$data[ self::VAR_ERROR ] = Sokno_Shopping_Ads_Utils::get_error_info( __CLASS__, $ex );
		}

		$this->inject_event( $event, $data );
	}

	/**
	 * Inject sale pixel for another types of payments in thank you page for COD, BACS CHEQUE.
	 *
	 * @param string $order_id The order id.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function inject_gateway_purchase_event( $order_id ) {
		if ( $this->check_last_event( self::EVENT_TYPE_SALE ) ) {
			return;
		}

		$this->inject_purchase_event( $order_id );
	}

	/**
	 * Return last parameters sent to SK delivery.
	 *
	 * @return array
	 * @since  1.0.5
	 *
	 * @access public
	 */
	public function get_last_parameters() {
		return $this->last_params ?? array();
	}

	/**
	 * Process the order to extract order information.
	 *
	 * @param WC_Order $order Current WooCommerce order.
	 * @param array    $data Tracking data.
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function process_order_data( WC_Order $order, array $data ) {
		$product_ids = array();
		foreach ( $order->get_items() as $item ) {
			$product       = wc_get_product( $item['product_id'] );
			$product_ids[] = $product->get_id();
		}

		$data [ self::VAR_PRODUCT_ID ]   = implode( ',', $product_ids );
		$data [ self::VAR_ORDER_ID ]     = $order->get_id();
		$data [ self::VAR_ORDER_AMOUNT ] = $order->get_total();
		$data [ self::VAR_TR_DATA ]      = array(
			'confirmed_at'   => $order->get_date_completed(),
			'customer_id'    => empty( $order->get_customer_id() ) ? '' : $order->get_customer_id(),
			'products_price' => $order->get_subtotal(),
		);

		return $data;
	}

	/**
	 * Process the order to extract coupon codes information.
	 *
	 * @param WC_Order $order Current WooCommerce order.
	 * @param array    $data Tracking data.
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function process_coupon_codes( WC_Order $order, array $data ) {
		$coupons = $order->get_coupon_codes();
		if ( ! empty( $coupons ) ) {
			$data[ self::VAR_COUPON_CODE ] = implode( ',', $coupons );
		}

		return $data;
	}

	/**
	 * Process the order to extract customer information.
	 *
	 * @param WC_Order $order Current WooCommerce order.
	 * @param array    $data Tracking data.
	 *
	 * @return array
	 * @throws Exception If customer doesn't exists.
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function process_customer_data( WC_Order $order, array $data ) {
		try {
			$customer_id = $order->get_customer_id();
			$customer    = ( ! empty( $customer_id ) ) ? new WC_Customer( $customer_id ) : null;
		} catch ( \Exception $e ) {
			$data[ self::VAR_ERROR ] = Sokno_Shopping_Ads_Utils::get_error_info( __CLASS__, $e );
			$customer                = null;
		}

		// We double check with ID == 0 because it's an usual behavior in wc entities to set the id to 0 if some error.
		if ( ! empty( $customer ) && $customer->get_id() !== 0 ) {
			// Process authenticated order.
			$orders_count = $customer->get_order_count();
			$email        = $customer->get_email() ?? '';
			if ( empty( $email ) ) {
				$email = $customer->get_billing_email() ?? '';
			}
		} else {
			// Process guest order.
			$email        = $order->get_billing_email() ?? '';
			$args         = array(
				'billing_email' => $email,
			);
			$orders_count = count( wc_get_orders( $args ) ?? array() );
		}

		// Set orders count.
		$data[ self::VAR_ORDERS_COUNT ] = is_numeric( $orders_count ) ? $orders_count : '';
		// Set email hash.
		$email = strtolower( trim( $email ) );
		if ( ! empty( $email ) && ! isset( $data[ self::VAR_EMAIL_HASH ] ) ) {
			$data[ self::VAR_EMAIL_HASH ] = Sokno_Shopping_Ads_Utils::hash( $email );
		}
		// Set user id if customer is present.
		if ( ! empty( $customer_id ) && ! isset( $data[ self::VAR_USER_ID ] ) ) {
			$data [ self::VAR_USER_ID ] = $customer_id;
		}

		return $data;
	}

	/**
	 * Process the order to extract subscription information.
	 *
	 * @param WC_Order $order Current WooCommerce order.
	 * @param array    $data Tracking data.
	 *
	 * @return array
	 * @throws Exception If subscription doesn't exists.
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function process_subscription_data( WC_Order $order, array $data ) {
		$subscription_info = $this->get_subscription_info( $order->get_id() );
		if ( ! empty( $subscription_info ) ) {
			$data[ self::VAR_TR_DATA ] = array_merge( $data[ self::VAR_TR_DATA ], $subscription_info );
		}

		return $data;
	}

	/**
	 * Returns the subscription tracking information if exists.
	 *
	 * @param string $order_id The order id.
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function get_subscription_info( $order_id ) {
		$data = array();

		if ( ! function_exists( 'wcs_get_subscriptions_for_order' ) || ! class_exists( 'WC_Subscription' ) ) {
			return $data;
		}

		$subscription_ids = wcs_get_subscriptions_for_order( $order_id );
		if ( empty( $subscription_ids ) ) {
			return $data;
		}

		$data['subscription']     = 1;
		$data['subscription_fee'] = 0;

		foreach ( $subscription_ids as $subscription_id ) {
			try {
				$subscription              = new WC_Subscription( $subscription_id );
				$data['subscription_fee'] += $subscription->get_sign_up_fee();
			} catch ( \Exception $ex ) {
				continue;
			}
		}

		return $data;
	}

	/**
	 * Gets the pixel invocation code. (Javascript Code).
	 *
	 * @param string $event_name The event name.
	 * @param array  $arguments Pixel invocation arguments.
	 *
	 * @return string
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function get_pixel_invocation_code( $event_name, $arguments = array() ) {
		$arguments         = array_merge(
			array(
				self::SHOP_KEY       => $this->shop,
				self::EVENT_TYPE_KEY => $event_name,
				self::VAR_VERSION    => $this->version,
			),
			$arguments
		);
		$query             = http_build_query( $arguments );
		$this->last_params = $arguments;

		return sprintf( self::PIXEL_TEMPLATE, self::PIXEL_BASE_ENDPOINT, $query );
	}

	/**
	 * Prevent double-fires by checking the last event.
	 *
	 * @param string $event_name Event name.
	 *
	 * @return bool
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function check_last_event( $event_name ) {
		return $event_name === $this->last_event;
	}

	/**
	 * Injects the script code to the script queue to be rendered by WC.
	 *
	 * @param string $event_name Event name.
	 * @param array  $params Parameters.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function inject_event( $event_name, $params ) {
		$this->last_event = $event_name;
		if ( Sokno_Shopping_Ads_Utils::is_wc_available() ) {
			$code = $this->get_pixel_invocation_code( $event_name, $params );
			Sokno_Shopping_Ads_Utils::wc_enqueue_js( $code );
		}
	}

	/**
	 * Renders the script tag directly to the output.
	 *
	 * @param string $event_name Event name.
	 * @param array  $params Parameters.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function render_event( $event_name, $params ) {
		$code   = $this->get_pixel_invocation_code( $event_name, $params );
		$script = "<script type='text/javascript'>\n%s\n</script>";
		$code   = sprintf( $script, $code );

		echo $code; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Helper function to iterate through a cart and gather all content ids
	 *
	 * @param array $cart Cart contents.
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function get_content_ids_from_cart( $cart ) {
		$product_ids = array();

		foreach ( $cart as $item ) {
			$product_ids[] = $item['data']->get_id();
		}

		return $product_ids;
	}

	/**
	 * Returns the current user data.
	 *
	 * @return array
	 * @since  1.0.8
	 *
	 * @access private
	 */
	private function get_current_user_data() {
		$user       = wp_get_current_user();
		$user_id    = null;
		$user_email = null;

		if ( ! empty( $user ) && ! empty( $user->ID ) ) {
			$user_id    = $user->ID;
			$user_email = empty( $user->user_email ) ? null : $user->user_email;
		}

		return array( $user_id, $user_email );
	}

	/**
	 * Add the user data to the tracking information.
	 *
	 * @param array $data Tracking data.
	 * @return array
	 * @since  1.0.8
	 *
	 * @access private
	 */
	private function add_user_data( $data ) {
		list($user_id, $user_email) = $this->get_current_user_data();
		if ( ! empty( $user_email ) ) {
			$data[ self::VAR_EMAIL_HASH ] = Sokno_Shopping_Ads_Utils::hash( $user_email );
		}
		if ( ! empty( $user_id ) ) {
			$data[ self::VAR_USER_ID ] = $user_id;
		}

		return $data;
	}
}

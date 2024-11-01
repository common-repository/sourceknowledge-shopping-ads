<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 */

/**
 * The core plugin class.
 *
 * Includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sokno_Shopping_Ads_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The basename of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $basename    The basename of this plugin.
	 */
	protected $basename;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The pixel tracker.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Sokno_Shopping_Ads_Pixel    $pixel    Pixel handling.
	 */
	private $pixel;

	/**
	 * The request handler.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sokno_Shopping_Ads_Request    $request    The request handler.
	 */
	private $request;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @param string $basename The plugin basename.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $basename ) {
		if ( defined( 'SOKNO_SHOPPING_ADS_VERSION' ) ) {
			$this->version = SOKNO_SHOPPING_ADS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sourceknowledge-shopping-ads';
		$this->basename    = $basename;

		$this->load_dependencies();
		$this->set_locale();
		$this->set_pixel();
		$this->handle_requests();
		$this->define_integration_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sokno-shopping-ads-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sokno-shopping-ads-i18n.php';

		/**
		 * The class responsible for handling the configuration
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sokno-shopping-ads-config.php';

		/**
		 * A helper class with some misc utilities.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sokno-shopping-ads-utils.php';

		/**
		 * The class responsible for handling external requests
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sokno-shopping-ads-request.php';

		/**
		 * The class responsible for handling pixel invocation codes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sokno-shopping-ads-pixel.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sokno-shopping-ads-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sokno-shopping-ads-public.php';

		/**
		 * Instantiate the loader
		 */
		$this->loader = new Sokno_Shopping_Ads_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sokno_Shopping_Ads_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sokno_Shopping_Ads_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Initializes pixel logic.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_pixel() {

		$options     = Sokno_Shopping_Ads_Config::get_settings();
		$this->pixel = new Sokno_Shopping_Ads_Pixel( $options );

	}

	/**
	 * Initializes request handling logic.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function handle_requests() {

		$this->request = new Sokno_Shopping_Ads_Request();
		$this->request->hook_actions( $this->loader );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Sokno_Shopping_Ads_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'checks' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_settings_need_revision' );

		$plugin_action_links = 'plugin_action_links_' . $this->basename;
		$this->loader->add_filter( $plugin_action_links, $plugin_admin, 'add_settings_link' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Sokno_Shopping_Ads_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_loaded', $plugin_public, 'capture_coupon_code' );
		$this->loader->add_action( 'woocommerce_before_cart', $plugin_public, 'apply_coupon_to_cart', 10, 0 );

		if ( $this->pixel->is_enabled() ) {
			$this->pixel->hook_actions( $this->loader );
		}
	}

	/**
	 * Register hooks related to integration initialization
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_integration_hooks() {

		$this->loader->add_filter( 'woocommerce_integrations', $this, 'add_integration' );
		$this->loader->add_action( 'plugins_loaded', $this, 'register_integration' );

	}

	/**
	 * Handles logic involved with the WooCommerce integration main initialization handling
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_integration() {

		// If WooCommerce is available then we register the integration.
		if ( Sokno_Shopping_Ads_Utils::is_wc_available() ) {
			// Require here because we need WooCommerce initialization.
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sokno-shopping-ads-integration.php';
		}

	}

	/**
	 * Register the WooCommerce integration
	 * (Makes the integration available under WooCommerce/Settings/Integrations)
	 *
	 * @param array $integrations Array of existing integrations.
	 *
	 * @return array
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_integration( $integrations ) {
		return array_merge( $integrations, array( 'Sokno_Shopping_Ads_Integration' ) );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Sokno_Shopping_Ads_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

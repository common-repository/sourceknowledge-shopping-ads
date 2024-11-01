<?php
/**
 * WooCommerce integration class.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 */

/**
 * WooCommerce integration class.
 *
 * This class defines all code necessary to write a WooCommerce Integration.
 *
 * @since      1.0.0
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Integration extends WC_Integration {

	/**
	 * Site id.
	 *
	 * @var string
	 * @since    1.0.0
	 * @access   private
	 */
	private $site_id;

	/**
	 * Install end point.
	 *
	 * @var string
	 * @since    1.0.0
	 * @access   private
	 */
	private $install_endpoint;

	/**
	 * Sokno_Shopping_Ads_Integration constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->id                 = Sokno_Shopping_Ads_Config::INTEGRATION_NAME;
		$this->method_title       = __(
			'SourceKnowledge Shopping Ads',
			'sourceknowledge-shopping-ads'
		);
		$this->method_description = __(
			'Reach in-market shoppers to drive new sales',
			'sourceknowledge-shopping-ads'
		);

		// Initialize configuration.
		if ( ! $this->init_config() ) {
			return;
		}

		$this->install_endpoint = Sokno_Shopping_Ads_Config::get_dashboard_endpoint( Sokno_Shopping_Ads_Config::INSTALL_ENDPOINT );

		// Load settings.
		$this->init_settings();
	}

	/**
	 * Initializes settings
	 *
	 * @return   void
	 * @since    1.0.0
	 * @access   private
	 */
	public function init_settings() {
		parent::init_settings();

		$saved          = Sokno_Shopping_Ads_Config::get_settings();
		$this->settings = array_merge( $this->settings, $saved );
		$dirty_settings = false;

		// Initialization extra logic.
		$dirty_settings |= $this->load_module_defaults( Sokno_Shopping_Ads_Pixel::DEFAULT_SETTINGS );

		// If there is a new change then save settings.
		if ( $dirty_settings ) {
			Sokno_Shopping_Ads_Config::update_settings( $this->settings );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function admin_options() {
		parent::admin_options();

		$linked         = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_LINKED, false );
		$install_token  = uniqid( 'sk', true );
		$install_params = array(
			'website' => Sokno_Shopping_Ads_Utils::get_site_url(),
			'name'    => Sokno_Shopping_Ads_Utils::get_site_name(),
			'email'   => Sokno_Shopping_Ads_Utils::get_admin_email(),
			'intent'  => $install_token,
		);
		if ( ! $linked ) {
			Sokno_Shopping_Ads_Config::set_option( Sokno_Shopping_Ads_Config::CONFIG_INSTALL_TOKEN, $install_token );
		}
		$install_url = $this->install_endpoint;
		$permalink   = Sokno_Shopping_Ads_Utils::is_permalink_enabled();
		$params      = array(
			'permalink_enabled' => $permalink,
			'linked'            => $linked,
			'install_url'       => $install_url,
			'install_params'    => $install_params,
		);

		$GLOBALS['hide_save_button'] = ! $permalink || ! $linked;

		Sokno_Shopping_Ads_Utils::render_template( 'sokno-shopping-ads-admin-display', $params );
	}

	/**
	 * Load default settings for a module
	 *
	 * @param array $defaults Module defaults.
	 *
	 * @return   bool
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_module_defaults( $defaults ) {
		$dirty = false;

		foreach ( $defaults as $key => $value ) {
			if ( ! isset( $this->settings[ $key ] ) ) {
				$this->settings[ $key ] = $value;
				$dirty                  = true;
			}
		}

		return $dirty;
	}

	/**
	 * Initializes main configuration
	 *
	 * @return   bool
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_config() {
		$site_id_key = Sokno_Shopping_Ads_Config::CONFIG_SITE_ID;
		$site_id     = Sokno_Shopping_Ads_Config::get_option( $site_id_key );
		if ( empty( $site_id ) ) {
			$site_id = Sokno_Shopping_Ads_Utils::generate_site_id();
			Sokno_Shopping_Ads_Config::set_option( $site_id_key, $site_id );
		}

		$this->site_id = $site_id;

		return ! empty( $site_id );
	}

}

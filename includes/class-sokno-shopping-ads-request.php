<?php
/**
 * Contains request endpoints handling.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 */

/**
 * Contains request endpoints handling.
 *
 * This class defines all code necessary to process external requests used in the linking process with SourceKnowledge platform.
 *
 * @since      1.0.0
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/includes
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Request {

	const ADMIN_ACTION_GET_INFO = 'sk_ads_info';
	const ADMIN_ACTION_LINK     = 'sk_ads_link';

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Sokno_Shopping_Ads_Request constructor.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		if ( defined( 'SOKNO_SHOPPING_ADS_VERSION' ) ) {
			$this->version = SOKNO_SHOPPING_ADS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
	}

	/**
	 * Hooks all actions WP/WC to logic
	 *
	 * @param Sokno_Shopping_Ads_Loader $loader Shopping ads loader.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function hook_actions( Sokno_Shopping_Ads_Loader $loader ) {

		$loader->add_filter( 'allowed_redirect_hosts', $this, 'add_valid_redirect_host' );
		$loader->add_action( 'admin_post_' . self::ADMIN_ACTION_GET_INFO, $this, 'get_site_info' );
		$loader->add_action( 'admin_post_nopriv_' . self::ADMIN_ACTION_GET_INFO, $this, 'get_site_info' );
		$loader->add_action( 'admin_post_' . self::ADMIN_ACTION_LINK, $this, 'link_site' );
		$loader->add_action( 'admin_post_nopriv_' . self::ADMIN_ACTION_LINK, $this, 'link_site' );
	}

	/**
	 * Links the site with the external system.
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function link_site() {
		$params   = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$site     = Sokno_Shopping_Ads_Utils::get_field_val( $params, 's', '' );
		$return   = Sokno_Shopping_Ads_Utils::get_field_val( $params, 'r', '' );
		$sign     = Sokno_Shopping_Ads_Utils::get_field_val( $params, 'h', '' );
		$intent   = Sokno_Shopping_Ads_Utils::get_field_val( $params, 'i', '' );
		$last_url = Sokno_Shopping_Ads_Config::get_dashboard_endpoint( Sokno_Shopping_Ads_Config::FINISH_ENDPOINT );
		$site_id  = Sokno_Shopping_Ads_Utils::generate_site_id();
		$linked   = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_LINKED, false );
		$p_sign   = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_SIGNATURE, '' );

		if ( empty( $site ) && empty( $return ) && empty( $sign ) && empty( $intent ) ) {
			exit( 'link_site: the request was empty' );
		}

		if ( empty( $site ) ) {
			exit( 'link_site: site was empty' );
		}

		if ( empty( $return ) ) {
			exit( 'link_site: return was empty' );
		}

		// Verify token authenticity.
		$token = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_INSTALL_TOKEN, '' );
		if ( $linked && ! in_array( $intent, array( $token, $p_sign ), true ) ) {
			exit( 'link_site: Invalid token' );
		}

		Sokno_Shopping_Ads_Config::set_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_ID, $site );
		Sokno_Shopping_Ads_Config::set_option( Sokno_Shopping_Ads_Config::CONFIG_LINKED, true );
		Sokno_Shopping_Ads_Config::set_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_SIGNATURE, $sign );
		if ( ! wp_redirect( $return ) ) { // phpcs:ignore
			exit( 'link_site: Could not redirect to: ' . esc_url_raw( $return ) );
		} else {
			exit;
		}

	}

	/**
	 * Update the site id information
	 *
	 * @return void
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function get_site_info() {
		$site     = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_SITE_ID, '' );
		$linked   = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_LINKED, false );
		$response = array(
			'site'       => $site,
			'linked'     => true === (bool) $linked,
			'name'       => Sokno_Shopping_Ads_Utils::get_site_name(),
			'url'        => Sokno_Shopping_Ads_Utils::get_site_url(),
			'currency'   => Sokno_Shopping_Ads_Utils::get_woocommerce_currency(),
			'wp_version' => Sokno_Shopping_Ads_Utils::get_wordpress_version(),
			'wc_version' => Sokno_Shopping_Ads_Utils::get_woocommerce_version(),
			'version'    => $this->version,
			'api'        => Sokno_Shopping_Ads_Utils::get_woocommerce_auth_url(),
			'permalink'  => Sokno_Shopping_Ads_Utils::get_permalink_structure(),
		);
		wp_send_json( $response );
	}

	/**
	 * Filter for allowed redirect hosts
	 *
	 * @param array $hosts Current allowed hosts.
	 *
	 * @return array
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function add_valid_redirect_host( $hosts ) {
		$dashboard = Sokno_Shopping_Ads_Config::get_dashboard_endpoint( Sokno_Shopping_Ads_Config::FINISH_ENDPOINT );
		$host      = wp_parse_url( $dashboard, PHP_URL_HOST );
		$hosts[]   = $host;

		return $hosts;
	}
}

<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/admin
 * @author     SourceKnowledge <dev@sourceknowledge.com>
 */
class Sokno_Shopping_Ads_Admin {

	/**
	 * Url for plugin settings
	 */
	const ADMIN_SETTINGS_URL = 'admin.php?page=wc-settings&tab=integration&section=%s';

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
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sokno-shopping-ads-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Handles if the after the installation or configuration the plugin settings needs revision
	 *
	 * @since    1.0.0
	 */
	public function handle_settings_need_revision() {
		if ( Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_NEEDS_REV, false ) ) {
			Sokno_Shopping_Ads_Config::set_option( Sokno_Shopping_Ads_Config::CONFIG_NEEDS_REV, false );
			wp_safe_redirect( self::get_settings_url() );
			exit();
		}
	}

	/**
	 * This method add the settings link in the plugin list.
	 * This function takes the existing integration links in the admin dashboard and injects our link.
	 *
	 * @param array $links Settings links.
	 *
	 * @return array
	 * @since    1.0.0
	 */
	public function add_settings_link( $links ) {
		$url      = self::get_settings_url();
		$settings = array(
			'settings' => sprintf( '<a href="%s">%s</a>', $url, 'Settings' ),
		);

		return array_merge( $settings, $links );
	}

	/**
	 * This method checks requisites and creates notification based on conditions.
	 *
	 * @since    1.0.0
	 */
	public function checks() {
		if ( ! Sokno_Shopping_Ads_Utils::is_permalink_enabled() ) {
			/* translators: Placeholders: %1$s - opening <strong> HTML strong tag, %2$s - closing </strong> HTML strong tag, %3$s - opening <a> HTML link tag, %4$s - closing </a> HTML link tag */
			$message = esc_html__(
				'%1$sSourceKnowledge Shopping Ads%2$s requires permalink enabled. Please %3$senable permalink%4$s.',
				'sourceknowledge-shopping-ads'
			);
			$message = vsprintf(
				$message,
				array(
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">',
					'</a>',
				)
			);

			Sokno_Shopping_Ads_Utils::render_message_html( $message, 'error' );

			return;
		}

		$installed = Sokno_Shopping_Ads_Config::get_option( Sokno_Shopping_Ads_Config::CONFIG_LINKED, false );

		if ( ! $installed ) {
			/* translators: Placeholders: %1$s - opening <strong> HTML strong tag, %2$s - closing </strong> HTML strong tag, %3$s - opening <a> HTML link tag, %4$s - closing </a> HTML link tag */
			$message = esc_html__(
				'%1$sSourceKnowledge Shopping Ads%2$s is almost ready. To link your store, please %3$scomplete the setup steps%4$s.',
				'sourceknowledge-shopping-ads'
			);
			$message = vsprintf(
				$message,
				array(
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( self::get_settings_url() ) . '">',
					'</a>',
				)
			);

			Sokno_Shopping_Ads_Utils::render_message_html( $message, 'info' );
		}
	}

	/**
	 * Returns the settings page url
	 *
	 * @access public
	 * @return string|void
	 * @since    1.0.0
	 */
	public static function get_settings_url() {
		$relative = sprintf( self::ADMIN_SETTINGS_URL, Sokno_Shopping_Ads_Config::INTEGRATION_NAME );

		return admin_url( $relative );
	}
}

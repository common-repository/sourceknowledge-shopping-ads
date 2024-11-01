<?php
/**
 * Template for the plugin UI on the WooCommerce integration settings
 *
 * This file is used to markup the admin-facing settings of the plugin.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/admin/partials
 */

?>

<?php if ( ! $params['permalink_enabled'] ) : ?>
	<strong><?php echo esc_html( __( 'This plugin doesn\'t work without permalink enabled.', 'sourceknowledge-shopping-ads' ) ); ?></strong>
	<p>
		<?php echo esc_html( __( 'Please enable permalink support in your settings.', 'sourceknowledge-shopping-ads' ) ); ?>
		<a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>">
			<?php echo esc_html( __( 'Go to permalink settings', 'sourceknowledge-shopping-ads' ) ); ?>
		</a>.
	</p>
<?php elseif ( ! $params['linked'] ) : ?>
	<div id="sokno_shopping_ads_get_started" data-action="<?php echo esc_attr( $params['install_url'] ); ?>">
		<?php foreach ( $params['install_params'] as $key => $val ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>">
		<?php endforeach; ?>
	</div>
	<button class="btn button-primary" type="button" onclick="window.sokno_shopping_ads_admin.get_started();"><?php echo esc_html( __( 'Get Started', 'sourceknowledge-shopping-ads' ) ); ?></button>
<?php else : ?>
	<h3><?php echo esc_html( __( 'You successfully installed the SourceKnowledge integration.', 'sourceknowledge-shopping-ads' ) ); ?></h3>
<?php endif; ?>

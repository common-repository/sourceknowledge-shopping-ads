<?php
/**
 * Template for notification messages
 *
 * This file is used to markup the admin-facing notification.
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/admin/partials
 */

?>

<div class="notice is-dismissible notice-<?php echo esc_attr( $params['type'] ); ?>">
	<p>
		<?php
		$allowed_html = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		);
		echo wp_kses( $params['message'], $allowed_html );
		?>
	</p>
</div>

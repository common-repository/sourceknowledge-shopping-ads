/**
 * Admin related scripts
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/public
 */

(function( $, window ) {
	'use strict';

	function get_started() {
		var $content = $( '#sokno_shopping_ads_get_started' );
		if ($content.length > 0) {
			$content.remove();
			var $form = $( '<form action="' + $content.data( 'action' ) + '" method="post"></form>' );
			$form.append( $content );
			$( window.document.body ).append( $form );
			$form.submit();
		}
	}

	window.sokno_shopping_ads_admin = {
		get_started: get_started
	};

})( jQuery, window );

/**
 * Public scripts
 *
 * @link       https://sourceknowledge.com
 * @since      1.0.0
 *
 * @package    Sokno_Shopping_Ads
 * @subpackage Sokno_Shopping_Ads/public
 */

(function( jQuery ) {
	'use strict';

	document.addEventListener(
		'DOMContentLoaded',
		function() {
			jQuery && jQuery(
				function($){
					$( 'body' ).on(
						'added_to_cart',
						function(event) {
							// Ajax action.
							$.get(
								'?wc-ajax=sokno_inject_add_to_cart_event',
								function(data) {
									$( 'head' ).append( $( data ) );
								}
							);
						}
					);
				}
			);
		},
		false
	);

})( window.jQuery );

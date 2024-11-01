(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$( document ).ready(
		function () {

			/*Initialize Datatable JS*/
			$( '#orderTable' ).DataTable(
				{
					responsive: true,
					"order": [[ 1, "asc" ]]
				}
			);

			/*Validation for return qty control*/
			$( "input[type=number]" ).on(
				"focus",
				function () {
					$( this ).on(
						"keydown",
						function (event) {
							if (event.keyCode === 38 || event.keyCode === 40) {
							} else {
								return false;
							}
						}
					);
				}
			);

			// Hide fields initial
			$( '.return_reason' ).hide();
			$( '.return_or_exchange' ).hide();
			$( '.return_comment' ).hide();

			/*Validation for return qty control*/
			$( document ).on(
				"change",
				".return-qty-handler",
				function () {

					var productID  = parseInt( $( this ).data( 'productid' ) );
					var return_qty = parseInt( $( this ).val() );

					// Check return-qty and hide/show controls accordingly
					if (return_qty > 0) {
						$( '#' + productID + '_return_or_exchange' ).show();
						$( '#' + productID + '_return_reason' ).show();
						$( '#' + productID + '_return_comment' ).show();
					} else {
						$( '#' + productID + '_return_or_exchange' ).hide();
						$( '#' + productID + '_return_reason' ).hide();
						$( '#' + productID + '_return_comment' ).hide();
					}
				}
			);
			$( 'h2.top-notice' ).delay( 4000 ).fadeOut( 300 );
		}
	);

})( jQuery );

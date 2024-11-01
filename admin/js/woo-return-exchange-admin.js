(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

			tinymce.init(
				{
					selector: '#returnAddressTextarea',
					width: 950,
					height: 300,
					plugins: [
					'advlist autolink link image lists charmap print preview hr anchor pagebreak',
					'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
					'table emoticons template paste help'
					],
					toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | ' +
					'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
					'forecolor backcolor emoticons | help',
					menu: {
						favs: {title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons'}
					},
					menubar: 'favs file edit view insert format tools table help',
				}
			);
			tinymce.init(
				{
					selector: '#returnExchangeCompleted',
					width: 950,
					height: 300,
					plugins: [
					'advlist autolink link image lists charmap print preview hr anchor pagebreak',
					'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
					'table emoticons template paste help'
					],
					toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | ' +
					'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
					'forecolor backcolor emoticons | help',
					menu: {
						favs: {title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons'}
					},
					menubar: 'favs file edit view insert format tools table help',
				}
			);
			tinymce.init(
				{
					selector: '#returnExchangeRequested',
					width: 950,
					height: 300,
					plugins: [
					'advlist autolink link image lists charmap print preview hr anchor pagebreak',
					'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
					'table emoticons template paste help'
					],
					toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | ' +
					'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
					'forecolor backcolor emoticons | help',
					menu: {
						favs: {title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons'}
					},
					menubar: 'favs file edit view insert format tools table help',
				}
			);
			tinymce.init(
				{
					selector: '#returnExchangeRejected',
					width: 950,
					height: 300,
					plugins: [
					'advlist autolink link image lists charmap print preview hr anchor pagebreak',
					'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
					'table emoticons template paste help'
					],
					toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | ' +
					'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
					'forecolor backcolor emoticons | help',
					menu: {
						favs: {title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons'}
					},
					menubar: 'favs file edit view insert format tools table help',
				}
			);
			window.setTimeout(
				function () {
					$( ".alert" ).fadeTo( 1000, 0 ).slideUp(
						1000,
						function () {
							$( this ).remove();
						}
					);
				},
				5000
			);
		}
	);
})( jQuery );

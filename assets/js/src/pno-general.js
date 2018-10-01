/*global jQuery:true*/
/*global pno_settings:true*/
(function ($) {

	window.Posterno = window.Posterno || {};

	/**
	 * Cache selectors for future usage.
	 */
	window.Posterno.cacheSelectors = function () {
		window.Posterno.formSelectFields = $('.pno-field-multiselect select, .pno-select-searchable, .pno-field-term-select select');
	}

	/**
	 * Enable bootstrap tooltips functionality.
	 */
	window.Posterno.bootstrapTooltips = function () {
		if ( pno_settings.bootstrap ) {
			$('[data-toggle="tooltip"]').tooltip()
		}
	}

	/**
	 * Remove uploaded files from fields when clicking the remove button.
	 */
	window.Posterno.removeUploadedFiles = function () {
		$(document.body).on('click', '.pno-remove-uploaded-file', function () {
			var dropzone = $(this).data('dropped');
			$( '.' + dropzone ).removeClass('d-none');
			$(this).closest('.pno-uploaded-file').remove();
			return false;
		});
	}

	/**
	 * Transform dropdowns in select2 elements.
	 */
	window.Posterno.select2 = function () {
		window.Posterno.formSelectFields.select2({
			theme: 'default',
			placeholder: $(this).data('placeholder'),
			width: '100%'
		});
	}

	$( document ).ready( function() {
		window.Posterno.cacheSelectors()
		window.Posterno.bootstrapTooltips()
		window.Posterno.removeUploadedFiles()
		window.Posterno.select2()
	} );

} )( jQuery );


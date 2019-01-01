/*global jQuery:true*/
/*global pno_settings:true*/
(function ($) {

	window.Posterno = window.Posterno || {};

	/**
	 * Cache selectors for future usage.
	 */
	window.Posterno.cacheSelectors = function () {
		window.Posterno.listingsLinks = pno_settings.internal_links_new_tab_selectors.join(",");
		window.Posterno.formSelectFields = $('.pno-field-multiselect select, .pno-select-searchable, .pno-field-term-select select, .pno-field-term-multiselect select');
	}

	/**
	 * Enable bootstrap tooltips functionality.
	 */
	window.Posterno.bootstrapTooltips = function () {
		if (pno_settings.bootstrap) {
			$('[data-toggle="tooltip"]').tooltip()
		}
	}

	/**
	 * Remove uploaded files from fields when clicking the remove button.
	 */
	window.Posterno.removeUploadedFiles = function () {
		$(document.body).on('click', '.pno-remove-uploaded-file', function () {
			var dropzone = $(this).data('dropped');
			$('.' + dropzone).removeClass('d-none');
			$(this).closest('.pno-uploaded-file').remove();
			return false;
		});
	}

	/**
	 * Transform dropdowns in select2 elements.
	 */
	window.Posterno.select2 = function () {

		if ( ! window.Posterno.formSelectFields.length ) {
			return
		}

		$( window.Posterno.formSelectFields ).select2({
			theme: 'default',
			placeholder: $(this).data('placeholder'),
			width: '100%'
		});
	}

	/**
	 * Open internal listings links into a new tab.
	*/
	window.Posterno.openInternalLinksNewTab = function () {

		if ( pno_settings.internal_links_new_tab ) {
			$(window.Posterno.listingsLinks).each(function () {
				var a = new RegExp('/' + window.location.host + '/');
				if (a.test(this.href)) {
					$(this).click(function (event) {
						event.preventDefault();
						event.stopPropagation();
						window.open(this.href, '_blank');
					}
					);
				}
			}
			);
		}

	}

	$(document).ready(function () {
		window.Posterno.cacheSelectors()
		window.Posterno.bootstrapTooltips()
		window.Posterno.removeUploadedFiles()
		window.Posterno.select2()
		window.Posterno.openInternalLinksNewTab()
	});

})(jQuery);

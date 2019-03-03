/*global jQuery:true*/
/*global pno_settings:true*/
(function ($) {

	window.Posterno = window.Posterno || {};

	/**
	 * Cache selectors for future usage.
	 */
	window.Posterno.cacheSelectors = function () {
		window.Posterno.listingsLinks = pno_settings.internal_links_new_tab_selectors.join(",");
		window.Posterno.externalLinks = pno_settings.external_links_new_tab_selectors.join(",");
		window.Posterno.formSelectFields = $('.pno-field-multiselect select, .pno-select-searchable, .pno-field-term-select select, .pno-field-term-multiselect select');
		window.Posterno.singleMaps = $('.pno-single-listing-map');
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

	/**
	 * Open external listings links into a new tab.
	*/
	window.Posterno.openExternalLinksNewTab = function () {
		$(window.Posterno.externalLinks).each(function () {
			if ( pno_settings.external_links_rel_attributes ) {
				var a = $(this);
				if (location.hostname !== this.hostname) {
					var originalRel = (this.rel === undefined) ? '' : this.rel.toLowerCase();
					var newRel = originalRel.split(" ");
					if (originalRel.indexOf('noopener') === -1) {
						newRel.push('noopener');
					}
					if (originalRel.indexOf('noreferrer') === -1) {
						newRel.push('noreferrer');
					}
					if (originalRel.indexOf('nofollow') === -1) {
						newRel.push('nofollow');
					}
					a.attr('rel', newRel.join(" ").trim());
				}
			}
			if ( pno_settings.external_links_new_tab ) {
				var anchor = new RegExp('/' + window.location.host + '/');
				if (!anchor.test(this.href)) {
					$(this).click(function (event) {
						event.preventDefault();
						event.stopPropagation();
						window.open(this.href, '_blank');
					});
				}
			}
		});
	}

	/**
	 * Create the maps within the single listing page.
	 * At the moment only Google Maps is supported.
	*/
	window.Posterno.createSingleListingMap = function () {
		$(window.Posterno.singleMaps).each(function () {

			var singleMap = $(this)

			if ( singleMap.length ) {

				var singleLat = singleMap.data('lat')
				var singleLng = singleMap.data('lng')
				var singleZoom = singleMap.data('zoom')

				var map = new google.maps.Map( singleMap[0], {
					center: {
						lat: parseFloat( singleLat ),
						lng: parseFloat( singleLng )
					},
					zoom: parseFloat( singleZoom ),
					fullscreenControl: false,
					streetViewControl: false,
					mapTypeControl: false,
				});

				// Create coordinates for the starting marker.
				var singleLatLng = {
					lat: parseFloat(singleLat),
					lng: parseFloat(singleLng),
				};

				var marker = new google.maps.Marker({
					position: singleLatLng,
					map: map,
				});

			}

		});
	}

	$(document).ready(function () {
		window.Posterno.cacheSelectors()
		window.Posterno.bootstrapTooltips()
		window.Posterno.removeUploadedFiles()
		window.Posterno.select2()
		window.Posterno.openInternalLinksNewTab()
		window.Posterno.openExternalLinksNewTab()
		window.Posterno.createSingleListingMap()
	});

})(jQuery);

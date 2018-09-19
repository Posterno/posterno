/*global jQuery:true*/
/*global pnoMultiselects:true*/
/*global pno_submission:true*/
jQuery(document).ready(function ($) {

	window.pnoMultiselects = {};

	/**
	 * Initialize all the functionalities for the select2 scripts.
	 */
	pnoMultiselects.init = function () {

		pnoMultiselects.cacheSelectors();
		pnoMultiselects.transformSelect2();

	}

	/**
	 * Collect all selectors for the select2 functionality.
	 */
	pnoMultiselects.cacheSelectors = function () {

		pnoMultiselects.formSelectFields = $('.pno-field-multiselect select, .pno-select-searchable');
		pnoMultiselects.listingSubmissionCategory = $('.pno-listings-category-selector');

	}

	/**
	 * Transform each selector into a select2 element.
	 */
	pnoMultiselects.transformSelect2 = function () {

		pnoMultiselects.formSelectFields.select2({
			theme: 'default',
			placeholder: $(this).data('placeholder'),
		});

		// Listing submission category field
		pnoMultiselects.listingSubmissionCategory.select2({
			theme: 'default',
			placeholder: $(this).data('placeholder'),
			maximumSelectionLength: pno_submission.max_multiselect > 1 ? pno_submission.max_multiselect : false,
		});

	}

	pnoMultiselects.init();

});

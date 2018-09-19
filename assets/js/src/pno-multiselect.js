jQuery(document).ready(function ($) {
	$('.pno-field-multiselect select, .pno-select-searchable, .pno-listings-category-selector').select2({
		theme: 'default',
		placeholder: $(this).data('placeholder'),
	});
});

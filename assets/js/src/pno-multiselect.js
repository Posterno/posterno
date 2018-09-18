jQuery(document).ready(function ($) {
	$('.pno-field-multiselect select, .pno-select-searchable, .pno-category-dropdown').select2({
		theme: 'default',
		placeholder: $(this).data('placeholder'),
	});
});

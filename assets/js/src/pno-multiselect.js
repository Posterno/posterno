jQuery(document).ready(function ($) {
	$('.pno-field-multiselect select, .pno-select-searchable').select2({
		theme: 'default',
		placeholder: $(this).data('placeholder'),
	});
});

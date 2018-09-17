jQuery(document).ready(function ($) {
	$('.pno-field-multiselect select').select2({
		theme: 'default',
		placeholder: $(this).data('placeholder'),
	});
});

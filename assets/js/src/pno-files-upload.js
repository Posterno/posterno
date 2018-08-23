jQuery(document).ready(function ($) {

	$(document.body).on('click', '.pno-remove-uploaded-file', function () {
		var dropzone = $(this).data('dropped');
		$( '.' + dropzone ).removeClass('d-none');
		$(this).closest('.pno-uploaded-file').remove();
		return false;
	});

});


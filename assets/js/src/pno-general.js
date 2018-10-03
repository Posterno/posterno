/*global jQuery:true*/
/*global pno_settings:true*/
/*global Dropzone:true*/
/*global pno_submission:true*/
(function ($) {

	window.Posterno = window.Posterno || {};

	/**
	 * Cache selectors for future usage.
	 */
	window.Posterno.cacheSelectors = function () {
		window.Posterno.formSelectFields = $('.pno-field-multiselect select, .pno-select-searchable, .pno-field-term-select select');
		window.Posterno.dropzoneFields = $('[data-toggle="dropzone"]')
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

	/**
	 * Instantiate dropzones within forms.
	 */
	window.Posterno.dropzoneInit = function () {

		// Disable auto discover for all elements:
		Dropzone.autoDiscover = false;

		window.Posterno.dropzoneFields.each(function () {

			var dropzoneMaxFiles = $(this).data('max-files')
			var dropzonePostUrl  = $(this).data('dropzone-url')
			var dropzonePreview  = $(this).find( '.dz-preview' )
			var dropzoneMaxSize  = $(this).data('max-size')
			var dropzoneComponents = $(this).next('.pno-dropzone-components')

			var PosternoDropzone = new Dropzone( $(this).get(0), {
				url: dropzonePostUrl,
				thumbnailWidth: null,
				thumbnailHeight: null,
				maxFiles: dropzoneMaxFiles,
				maxFilesize: dropzoneMaxSize,
				previewsContainer: dropzonePreview.get(0),
				previewTemplate: dropzonePreview.html(),
				dictDefaultMessage: pno_submission.dropzone.dictDefaultMessage, // Default: Drop files here to upload
				dictFallbackMessage: pno_submission.dropzone.dictFallbackMessage, // Default: Your browser does not support drag'n'drop file uploads.
				dictFileTooBig: pno_submission.dropzone.dictFileTooBig, // Default: File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.
				dictInvalidFileType: pno_submission.dropzone.dictInvalidFileType, // Default: You can't upload files of this type.
				dictResponseError: pno_submission.dropzone.dictResponseError, // Default: Server responded with {{statusCode}} code.
				dictCancelUpload: pno_submission.dropzone.dictCancelUpload, // Default: Cancel upload
				dictUploadCanceled: pno_submission.dropzone.dictUploadCanceled, // Default: Upload canceled.
				dictCancelUploadConfirmation: pno_submission.dropzone.dictCancelUploadConfirmation, // Default: Are you sure you want to cancel this upload?
				dictRemoveFile: pno_submission.dropzone.dictRemoveFile, // Default: Remove file
				dictMaxFilesExceeded: pno_submission.dropzone.dictMaxFilesExceeded, // Default: You can not upload any more files.
			});

			PosternoDropzone.on('dragenter', function () {
				$(this).addClass( 'pno-dropzone-dragging' )
			});

			PosternoDropzone.on('dragleave', function () {
				$(this).removeClass('pno-dropzone-dragging');
			});

			PosternoDropzone.on('drop', function () {
				$(this).removeClass('pno-dropzone-dragging');
			});

			PosternoDropzone.on("error", function (file, error, xhr) {
				window.Posterno.drozoneShowError( dropzoneComponents, file, error, xhr )
			});

		});

	}

	/**
	 * Display the error message container if anything has gone wrong during file upload.
	 * Decide wether to display a generic error message or the one returned via ajax.
	 */
	window.Posterno.drozoneShowError = function( component, file, error, xhr ) {
		component.find('.pno-dropzone-error').removeClass('d-none')
	}

	/**
	 * Hide the error message for the given dropzone.
	 */
	window.Posterno.dropzoneHideError = function( component ) {
		component.find('.pno-dropzone-error').addClass('d-none')
	}

	$( document ).ready( function() {
		window.Posterno.cacheSelectors()
		window.Posterno.bootstrapTooltips()
		window.Posterno.removeUploadedFiles()
		window.Posterno.select2()
		window.Posterno.dropzoneInit()
	} );

} )( jQuery );

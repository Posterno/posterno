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
	 * Instantiate dropzones within forms.
	 */
	window.Posterno.dropzoneInit = function () {

		if ( ! window.Posterno.dropzoneFields.length ) {
			return
		}

		// Disable auto discover for all elements:
		Dropzone.autoDiscover = false;

		window.Posterno.dropzoneFields.each(function () {

			var theDropzoneElement = $(this)
			var dropzoneMaxFiles = $(this).data('max-files')
			var dropzonePostUrl = $(this).data('dropzone-url')
			var dropzonePreview = $(this).find('.dz-preview')
			var dropzoneMaxSize = $(this).data('max-size')
			var dropzoneMultiple = $(this).data('multiple')
			var dropzoneFieldID = $(this).data('field-id')
			var dropzoneAcceptedFiles = $(this).data('file-types')
			var dropzoneComponents = $(this).next('.pno-dropzone-components')

			var dropzoneStoredFiles = []

			var PosternoDropzone = new Dropzone($(this).get(0), {
				url: dropzonePostUrl,
				thumbnailWidth: null,
				thumbnailHeight: null,
				maxFiles: dropzoneMaxFiles,
				maxFilesize: dropzoneMaxSize,
				previewsContainer: dropzonePreview.get(0),
				previewTemplate: dropzonePreview.html(),
				addRemoveLinks: false,
				acceptedFiles: dropzoneAcceptedFiles,
				params: {
					max_size: dropzoneMaxSize,
					max_files: dropzoneMaxFiles,
					multiple: dropzoneMultiple,
					field_id: dropzoneFieldID
				},
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

			dropzonePreview.html('')

			window.Posterno.dropzoneGetFilesToMock(PosternoDropzone, dropzoneComponents)

			PosternoDropzone.on('dragenter', function () {
				theDropzoneElement.addClass('pno-dropzone-dragging')
			});

			PosternoDropzone.on('dragleave', function () {
				theDropzoneElement.removeClass('pno-dropzone-dragging');
			});

			PosternoDropzone.on('drop', function () {
				theDropzoneElement.removeClass('pno-dropzone-dragging');
			});

			PosternoDropzone.on('sending', function (file) {
				window.Posterno.dropzoneShowProgress(dropzoneComponents)
			});

			PosternoDropzone.on('totaluploadprogress', function (progress) {
				var progressBar = dropzoneComponents.find('.pno-dropzone-progress .progress-bar')
				window.Posterno.dropzoneSetProgress( progressBar, progress )
			});

			PosternoDropzone.on('queuecomplete', function (progress) {
				window.Posterno.dropzoneHideProgress(dropzoneComponents)
			});

			PosternoDropzone.on('success', function (file, response) {
				file.WordPressURL = response.data.files[0].url
				file.WordPressPATH = response.data.files[0].file

				dropzoneStoredFiles.push( {
					path: response.data.files[0].file,
					url: response.data.files[0].url
				} )

				window.Posterno.dropzoneStoreResponse( dropzoneComponents, dropzoneStoredFiles )
			});

			PosternoDropzone.on("error", function (file, error, xhr) {
				PosternoDropzone.removeFile(file)
				window.Posterno.drozoneShowError(dropzoneComponents, file, error, xhr)
			});

			PosternoDropzone.on('removedfile', function (file) {

				window.Posterno.dropzoneHideError( dropzoneComponents )
				window.Posterno.dropzoneRemoveFilesFromServer( file )

				// Find the the index of the file to remove from the array of uploaded files.
				var removeFilePath = file.WordPressPATH

				var index = dropzoneStoredFiles.map(function (e) {
					return e.path;
				}).indexOf( removeFilePath );

				dropzoneStoredFiles.splice( index, 1 );

				// Update the stored array of the field containing all uploaded files.
				window.Posterno.dropzoneStoreResponse( dropzoneComponents, dropzoneStoredFiles )

			});
/*
			var mockFile = {
				name: "myimage.jpg",
				size: 12345,
				type: 'image/jpeg'
			};
			PosternoDropzone.options.addedfile.call(PosternoDropzone, mockFile);
			PosternoDropzone.options.thumbnail.call(PosternoDropzone, mockFile, "http://someserver.com/myimage.jpg");
			mockFile.previewElement.classList.add('dz-success');
			mockFile.previewElement.classList.add('dz-complete'); */

		});

	}

	/**
	 * Display the error message container if anything has gone wrong during file upload.
	 * Display the error returned from the api if any
	 */
	window.Posterno.drozoneShowError = function (component, file, error, xhr) {
		var errorAlertContainer = component.find('.pno-dropzone-error')
		errorAlertContainer.removeClass('d-none')
		if ( error.data !== undefined && error.data.message !== undefined ) {
			errorAlertContainer.find( '.alert' ).text( error.data.message )
		} else {
			errorAlertContainer.find( '.alert' ).text( error )
		}
	}

	/**
	 * Hide the error message for the given dropzone.
	 */
	window.Posterno.dropzoneHideError = function (component) {
		component.find('.pno-dropzone-error').addClass('d-none')
	}

	/**
	 * Display the hidden progress bar of the dropzone.
	 * Also set the value to 0% progress.
	 */
	window.Posterno.dropzoneShowProgress = function (component) {
		var progressComponent = component.find('.pno-dropzone-progress')
		var progressBar = component.find('.pno-dropzone-progress .progress-bar')
		if (progressComponent.length) {
			progressComponent.removeClass('d-none')
			window.Posterno.dropzoneSetProgress(progressBar, '0')
		}
	}

	/**
	 * Updates the progress of a given progress bar element.
	 */
	window.Posterno.dropzoneSetProgress = function (progressBar, progress) {
		progressBar.attr('aria-valuenow', progress)
		progressBar.text(progress + '%')
		progressBar.css('width', progress + '%')
	}

	/**
	 * Hide the progress bar of a given component and reset the progress percentage.
	 */
	window.Posterno.dropzoneHideProgress = function (component) {
		var progressComponent = component.find('.pno-dropzone-progress')
		var progressBar = component.find('.pno-dropzone-progress .progress-bar')

		progressComponent.addClass('d-none')
		if (progressComponent.length) {
			window.Posterno.dropzoneSetProgress(progressBar, '0')
		}

	}

	/**
	 * Store the response from the api into the hidden input field so it can
	 * be processed later when submitting the form.
	 */
	window.Posterno.dropzoneStoreResponse = function ( component, response ) {
		var hiddenInput = component.find( 'input[type=hidden]' )
		hiddenInput.val(JSON.stringify(response))
	}

	/**
	 * Cleanup the value attribute of the hidden input file for the dropzone field.
	 */
	window.Posterno.dropzoneResetStoredResponse = function( component ) {
		var hiddenInput = component.find('input[type=hidden]')
		if ( hiddenInput.length ) {
			hiddenInput.val('')
		}
	}

	/**
	 * Retrieve the response that is stored into the hidden field after images have been uploaded.
	 */
	window.Posterno.dropzoneGetStoredResponse = function ( component ) {
		var hiddenInput = component.find('input[type=hidden]')
		return hiddenInput.val()
	}

	/**
	 * Remove files from the server.
	 */
	window.Posterno.dropzoneRemoveFilesFromServer = function( file ) {

		if ( file.WordPressPATH && file.WordPressURL ) {
			$.post({
				url: pno_submission.ajax,
				data: {
					'action': 'pno_remove_dropzone_file',
					'file_path': file.WordPressPATH,
					'file_url': file.WordPressURL,
					'nonce': pno_submission.dropzone_remove_file_nonce
				},
				//success: function (data) {},
				//error: function (errorThrown) {
				//	console.error(errorThrown);
				//}
			});
		}

	}

	/**
	 * Retrieve files stored on the server when viewing the edit form.
	*/
	window.Posterno.dropzoneGetFilesToMock = function ( myDropzone, component ) {
		var hiddenInput = component.find('input[type=hidden]')
		if ( hiddenInput.length > 0 ) {

			if ( ! hiddenInput.val() ) {
				return
			}

			var storedImages = JSON.parse( hiddenInput.val() )

			if ( myDropzone.options.maxFiles > 1 ) {

				for (var i = 0; i < storedImages.length; i++) {
					var mockFile = {
						name: storedImages[i].image_name,
						size: storedImages[i].image_size,
					};

					myDropzone.emit("addedfile", mockFile);
					myDropzone.emit("thumbnail", mockFile, storedImages[i].image_url);
					myDropzone.emit("complete", mockFile);
					myDropzone.files.push(mockFile);

					var existingFileCount = storedImages.length; // The number of files already uploaded
					myDropzone.options.maxFiles = myDropzone.options.maxFiles - existingFileCount;
				}

			} else {

				var mockFile = {
					name: storedImages.image_name,
					size: storedImages.image_size,
				};

				myDropzone.emit("addedfile", mockFile);
				myDropzone.emit("thumbnail", mockFile, storedImages.image_url);
				myDropzone.emit("complete", mockFile);
				myDropzone.files.push(mockFile);

				var existingFileCount = 1; // The number of files already uploaded
				myDropzone.options.maxFiles = myDropzone.options.maxFiles - existingFileCount;
				myDropzone.element.classList.add('dz-max-files-reached')

			}

		}

	}

	$(document).ready(function () {
		window.Posterno.cacheSelectors()
		window.Posterno.bootstrapTooltips()
		window.Posterno.removeUploadedFiles()
		window.Posterno.select2()
		window.Posterno.dropzoneInit()
	});

})(jQuery);

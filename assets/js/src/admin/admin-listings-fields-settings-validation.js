/*global pno_listing_cf:true*/
/*global $:true*/

$(document).on('carbonFields.apiLoaded', function (e, api) {

	var typesToHide = ['listing-category', 'listing-tags', 'listing-location', 'listing-opening-hours', 'social-profiles']

	if ( ! pno_listing_cf.is_default ) {
		$('select[name=_listing_field_type] option').each(function () {
			if ( $.inArray( $(this).val(), typesToHide) !== -1 ) {
				$(this).remove();
			}
		});
	}

	if ( pno_listing_cf.is_default && pno_listing_cf.taxonomy.length > 0 ) {
		$('input[name=_listing_field_taxonomy]').parent().parent().hide()
	}

	$(document).on('carbonFields.validateField', function (e, fieldName, error) {

		if (fieldName === 'listing_field_meta_key' && pno_listing_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if (value !== pno_listing_cf.field_id) {
				return pno_listing_cf.error_message;
			}
		} else if (fieldName === 'listing_field_meta_key' && !pno_listing_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if ($.inArray(value, pno_listing_cf.restricted_keys) !== -1) {
				return pno_listing_cf.reserved_message;
			}
		}

		if (fieldName === 'listing_field_type' && pno_listing_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if (value !== pno_listing_cf.field_type) {
				return pno_listing_cf.error_message;
			}
		}

		return error;
	});

	$(document).on('carbonFields.fieldUpdated', function (e, fieldName) {
		if (fieldName === 'listing_field_is_required' && pno_listing_cf.is_default && pno_listing_cf.field_id === 'listing_title') {
			if (api.getFieldValue('listing_field_is_required') === false) {
				api.setFieldValue('listing_field_is_required', true);
			}
		}
	});

});

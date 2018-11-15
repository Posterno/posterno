/*global pno_listing_cf:true*/
/*global $:true*/

$(document).on('carbonFields.apiLoaded', function (e, api) {

	var typesToHide = ['listing-category', 'listing-tags', 'listing-location', 'opening-hours', 'social-profiles']

	if ( ! pno_listing_cf.is_default ) {
		$('select[name=_listing_field_type] option').each(function () {
			if ( $.inArray( $(this).val(), typesToHide) !== -1 ) {
				$(this).remove();
			}
		});
	}

	$(document).on('carbonFields.validateField', function (e, fieldName, error) {

		if (fieldName === 'listing_field_meta_key' && pno_listing_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if (value !== pno_listing_cf.field_id) {
				return pno_listing_cf.messages.no_meta_key_changes;
			}
		} else if (fieldName === 'listing_field_meta_key' && !pno_listing_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if ($.inArray(value, pno_listing_cf.restricted_keys) !== -1) {
				return pno_listing_cf.messages.reserved_key;
			}
		}

		if (fieldName === 'listing_field_type' && pno_listing_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if (value !== pno_listing_cf.field_type) {
				return pno_listing_cf.messages.no_type_changes;
			}
		}

		return error;
	});

});

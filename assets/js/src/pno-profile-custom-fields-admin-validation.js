$(document).on('carbonFields.apiLoaded', function (e, api) {
	$(document).on('carbonFields.validateField', function (e, fieldName, error) {

		if (fieldName === 'profile_field_meta_key' && pno_user_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if (value !== pno_user_cf.field_id) {
				return pno_user_cf.messages.no_meta_key_changes;
			}
		} else if (fieldName === 'profile_field_meta_key' && ! pno_user_cf.is_default ) {
			var value = api.getFieldValue(fieldName);
			if ($.inArray(value, pno_user_cf.restricted_keys) !== -1) {
				return pno_user_cf.messages.reserved_key;
			}
		}

		if (fieldName === 'profile_field_type' && pno_user_cf.is_default) {
			var value = api.getFieldValue(fieldName);
			if (value !== pno_user_cf.field_type) {
				return pno_user_cf.messages.no_type_changes;
			}
		}

		return error;
	});

	// Make the required setting for the email field always required.
	$(document).on('carbonFields.fieldUpdated', function (e, fieldName) {
		if (fieldName === 'profile_field_is_required' && pno_user_cf.is_default && pno_user_cf.field_id === 'email') {
			if ( api.getFieldValue( 'profile_field_is_required' ) === false ) {
				api.setFieldValue('profile_field_is_required', true);
			}
		}
	});

});

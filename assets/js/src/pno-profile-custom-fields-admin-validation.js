$(document).on('carbonFields.apiLoaded', function (e, api) {
	$(document).on('carbonFields.validateField', function (e, fieldName, error) {
		console.log('Field being validated: ' + fieldName);
		console.log('Current error, if any: ' + error);

		// Add your own validation logic here
		// To raise an error return any string which will serve as the user-friendly error message
		// To not raise an error return `null`
		// To not interfere with the built-in validation return the `error` variable argument

		// This example will raise an error if the field's value is not an even number
		// If the value is even, it will proceed with validation as usual
		if (fieldName === 'field_meta_key') {
			var value = api.getFieldValue(fieldName);
			if ( value !== pno_user_cf.field_id ) {
				return pno_user_cf.messages.no_meta_key_changes;
			}
		}
		return error;
	});
});

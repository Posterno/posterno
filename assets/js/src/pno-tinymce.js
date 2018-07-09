(function () {

	// Default Values
	var yes_no = [{
			text: 'Yes',
			value: 'yes'
		},
		{
			text: 'No',
			value: 'no'
		},
	];
	var no_yes = [{
			text: 'No',
			value: 'no'
		},
		{
			text: 'Yes',
			value: 'yes'
		},
	];
	var true_false = [{
			text: 'Yes',
			value: 'true'
		},
		{
			text: 'No',
			value: 'false'
		},
	];
	var false_true = [{
			text: 'No',
			value: 'false'
		},
		{
			text: 'Yes',
			value: 'true'
		},
	];

	tinymce.PluginManager.add('pno_shortcodes_mce_button', function (editor, url) {
		editor.addButton('pno_shortcodes_mce_button', {
			title: pnotinymce.title,
			type: 'menubutton',
			icon: 'icon pno-shortcodes-icon',
			menu: [

				/** Forms **/
				{
					text: pnotinymce.forms.title,
					menu: [

						{
							text: pnotinymce.forms.login,
							onclick: function () {
								editor.insertContent('[pno_login_form]');
							}
						},
						{
							text: pnotinymce.forms.registration,
							onclick: function () {
								editor.insertContent('[pno_registration_form]');
							}
						},

					]
				}, // End forms

			]
		});
	});
})();

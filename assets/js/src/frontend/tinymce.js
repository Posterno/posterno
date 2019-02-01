(function () {

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
						{
							text: pnotinymce.forms.password,
							onclick: function () {
								editor.insertContent('[pno_password_recovery_form]');
							}
						},
						{
							text: pnotinymce.forms.submission,
							onclick: function () {
								editor.insertContent('[pno_listing_submission_form]');
							}
						},
						{
							text: pnotinymce.forms.editing,
							onclick: function () {
								editor.insertContent('[pno_listing_editing_form]');
							}
						},

					]
				}, // End forms

				// Links.
				{
					text: pnotinymce.links.title,
					menu: [

						// Login link.
						{
							text: pnotinymce.links.login.title,
							onclick: function () {
								editor.windowManager.open({
									title: pnotinymce.links.login.title,
									body: [{
											type: 'textbox',
											name: 'redirect',
											label: pnotinymce.links.login.redirect,
											value: ''
										},
										{
											type: 'textbox',
											name: 'label',
											label: pnotinymce.links.login.label,
											value: 'Login'
										},
									],
									onsubmit: function (e) {
										editor.insertContent('[pno_login_link redirect="' + e.data.redirect + '" label="' + e.data.label + '" ]');
									}
								});
							}
						},

						// Logout link.
						{
							text: pnotinymce.links.logout.title,
							onclick: function () {
								editor.windowManager.open({
									title: pnotinymce.links.logout.title,
									body: [{
											type: 'textbox',
											name: 'redirect',
											label: pnotinymce.links.logout.redirect,
											value: ''
										},
										{
											type: 'textbox',
											name: 'label',
											label: pnotinymce.links.logout.label,
											value: 'Logout'
										},
									],
									onsubmit: function (e) {
										editor.insertContent('[pno_logout_link redirect="' + e.data.redirect + '" label="' + e.data.label + '" ]');
									}
								});
							}
						},

					]
				},
				// End links.

				// Pages.
				{
					text: pnotinymce.pages.title,
					menu: [

						{
							text: pnotinymce.pages.dashboard,
							onclick: function () {
								editor.insertContent('[pno_dashboard]');
							}
						},
						{
							text: pnotinymce.pages.profile,
							onclick: function () {
								editor.insertContent('[pno_profile]');
							}
						},

					]
				},
				// End pages.

				// Listings.
				{
					text: pnotinymce.listings.title,
					menu: [

						{
							text: pnotinymce.listings.types,
							onclick: function () {
								editor.insertContent('[pno_listings_types]');
							}
						},
						{
							text: pnotinymce.listings.recent.title,
							onclick: function () {
								editor.windowManager.open({
									title: pnotinymce.listings.recent.title,
									body: [
										{
											type: 'textbox',
											name: 'max',
											label: pnotinymce.listings.recent.max,
											value: '10'
										},
										{
											type: 'textbox',
											name: 'layout',
											label: pnotinymce.listings.recent.layout,
											value: 'grid'
										}
									],
									onsubmit: function (e) {
										editor.insertContent('[pno_recent_listings max="' + e.data.max + '"]');
									}
								});
							}
						},

					]
				},
				// End Listings.

			]
		});
	});
})();

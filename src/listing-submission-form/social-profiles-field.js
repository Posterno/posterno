/*global Vue:true*/
Vue.component('pno-social-profile-field', {
	data() {
		return {
			definedSocialProfiles: [{
				social: '',
				url: ''
			}],
		}
	},
	mounted() {
		// Load profiles from the database into the field when viewing the listing editing form.
		var savedProfiles = this.getSavedProfiles()

		if ( savedProfiles.length > 0 ) {
			this.definedSocialProfiles = []
			savedProfiles.forEach( ( profile ) => {

				var socialID = ''
				var socialURL = ''

				if ( profile.social !== undefined ) {
					socialID = profile.social
				} else if ( profile.social_id !== undefined ) {
					socialID = profile.social_id
				}

				if ( profile.url !== undefined ) {
					socialURL = profile.url
				} else if ( profile.social_url ) {
					socialURL = profile.social_url
				}

				this.definedSocialProfiles.push({
					social: socialID,
					url: socialURL
				})

			} );
		}
	},
	methods: {
		/**
		 * Add new social media field.
		 */
		addNewSocialProfile() {
			this.definedSocialProfiles.push({
				social: '',
				url: ''
			})
		},

		/**
		 * Delete social media field.
		 */
		deleteSocialProfile(index) {
			if (index !== -1) {
				this.definedSocialProfiles.splice(index, 1);
			}
		},
		/**
		 * Detect the value stored into the database that it's loaded
		 * when viewing the listing editing form.
		 *
		 */
		getSavedProfiles() {
			return document.getElementById('pno-field-listing_social_media_profiles').value ? JSON.parse( document.getElementById('pno-field-listing_social_media_profiles').value ) : false
		}
	},
	watch: {
		/**
		 * Store changes to the social media field into the hidden social profile field container
		 * so that we can then use the data via php during save.
		 */
		definedSocialProfiles: {
			handler: function () {
				document.getElementById('pno-field-listing_social_media_profiles').value = JSON.stringify(this.definedSocialProfiles);
			},
			deep: true
		}
	}
});

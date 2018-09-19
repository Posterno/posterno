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
	},
	watch: {
		/**
		 * Store changes to the social media field into the hidden social profile field container
		 * so that we can then use the data via php during save.
		 */
		definedSocialProfiles: {
			handler: function () {
				document.getElementById('listing_social_media_profiles').value = JSON.stringify(this.definedSocialProfiles);
			},
			deep: true
		}
	}
});

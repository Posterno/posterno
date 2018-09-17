/**
 * Enhance the frontend listing submission form.
 */

/*global Vue:true*/
new Vue({
	data() {
		return {
			definedSocialProfiles: [ { social: '', url: '' } ]
		}
	},
	methods: {
		/**
		 * Add new social media field.
		 */
		addNewSocialProfile() {
			this.definedSocialProfiles.push( {
				social: '',
				url: ''
			} )
		},

		/**
		 * Delete social media field.
		 */
		deleteSocialProfile( index ) {
			if (index !== -1) {
				this.definedSocialProfiles.splice(index, 1);
			}
		},

		/**
		 * Last minute operations before submitting the listing to the server.
		 */
		submitListing() {

			// "Store" social media profiles within the hidden field.
			document.getElementById('listing_social_media_profiles').value = JSON.stringify( this.definedSocialProfiles );

		}

	},
}).$mount('#pno-form-listing-submit')

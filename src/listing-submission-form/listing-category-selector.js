/*global Vue:true*/
Vue.component('pno-listing-category-selector', {
	data() {
		return {
			selectedCategories: [],
		}
	},
	mounted() {

		// Load selected categories of a listing from the database when viewing the edit form.
		var savedCategories = this.getSavedCategories()

		if ( savedCategories.length > 0 ) {
			savedCategories.forEach((category, index) => {
				this.selectedCategories.push( category.term_id )
			});
		}

	},
	watch: {
		/**
		 * Watch for changes to the vue model and store changes into the frontend field
		 * so that we can use it via php when submitting the form.
		 */
		selectedCategories: {
			handler: function () {
				document.getElementById('listing_categories').value = JSON.stringify(this.selectedCategories);
			},
			deep: true
		}
	},
	methods: {
		/**
		 * Get categories loaded into the field from the database.
		 *
		 * @returns
		 */
		getSavedCategories() {
			return document.getElementById('listing_categories').value ? JSON.parse( document.getElementById('listing_categories').value ) : false
		}
	}
});

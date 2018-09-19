/*global Vue:true*/
Vue.component('pno-listing-category-selector', {
	data() {
		return {
			selectedCategories: [],
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
	}
});

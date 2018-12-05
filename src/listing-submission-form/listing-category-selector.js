/*global Vue:true*/
/*global pno_submission:true*/
Vue.component('pno-listing-category-selector', {
	data() {
		return {
			selectedCategories: [],
		}
	},
	mounted() {

		// Load selected categories of a listing from the database when viewing the edit form.
		var savedCategories = this.getSavedCategories()

		/*
		if ( savedCategories.length > 0 ) {
			savedCategories.forEach( (category) => {
				if ( category.parent > 0 ) {
					this.selectedSubcategories.push( category.term_id )
				} else {
					this.selectedCategories.push( category.term_id )
				}
			});
		}*/

	},
	watch: {
		/**
		 * When parent categories are selected, save the value in preparation for storage
		 * and determine if we're going to show the sub categories.
		 */
		selectedCategories: {
			handler: function () {
				this.storeSelectedCategories()
			},
		},
	},
	methods: {
		/**
		 * Adjust the output of the selected options to display the data-name attribute.
		 * We're doing this because the sub options have symbols to create hierarchy within the list,
		 * so those need to be hidden when the option is selected.
		 */
		renderSelectedOption( content ) {
			return content.element.dataset.name
		},
		/**
		 * Store the selected categories and subcategories within the form's field
		 * in preparation for storage into the database.
		 */
		storeSelectedCategories() {
			document.getElementById('pno-field-listing_categories').value = JSON.stringify(this.selectedCategories);
		},
		/**
		 * Get categories loaded into the field from the database.
		 */
		getSavedCategories() {
			return document.getElementById('pno-field-listing_categories').value ? JSON.parse( document.getElementById('pno-field-listing_categories').value ) : false
		}
	}
});

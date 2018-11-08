/*global Vue:true*/
/*global pno_submission:true*/
import axios from 'axios'

Vue.component('pno-listing-category-selector', {
	data() {
		return {
			selectedCategories: [],
			selectedSubcategories: [],
			availableSubcategories: [],

			displaySubcategories: false,
			loading: false,
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

				if ( this.subcategoriesSelectable() && this.selectedCategories !== null && this.selectedCategories.length > 0 ) {
					this.displaySubcategories = true
					this.loadSubcategories()
				} else {
					this.displaySubcategories = false
				}

			},
			deep: true
		}
	},
	methods: {
		/**
		 * Determine if sub categories are selectable.
		 */
		subcategoriesSelectable() {
			return pno_submission.subcategories_on_submission === '1' ? true : false
		},
		/**
		 * Load sub categories based on parent categories that have been selected.
		 *
		 */
		loadSubcategories() {

			this.loading = true

			axios.get( pno_submission.ajax, {
				params: {
					nonce: pno_submission.get_subcategories_nonce,
					categories: this.selectedCategories,
					action: 'pno_get_subcategories'
				}
			})
			.then( response => {
				this.loading = false
				if ( response.data.data ) {

					// Reset available sucategories.
					this.availableSubcategories = []

					// Add all the new found subcategories.
					response.data.data.forEach( ( subCategory ) => {
						this.availableSubcategories.push( {
							'id': subCategory.term_id,
							'text': subCategory.name
						} )
					});

				}
			})
			.catch( error => {
				this.loading = false
				this.availableSubcategories = []
			})

		},
		/**
		 * Get categories loaded into the field from the database.
		 */
		getSavedCategories() {
			return document.getElementById('listing_categories').value ? JSON.parse( document.getElementById('listing_categories').value ) : false
		}
	}
});

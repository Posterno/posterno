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

		if ( savedCategories.parent !== undefined ) {
			savedCategories.parent.forEach( (category) => {
				this.selectedCategories.push( category )
			});
		}
		if ( savedCategories.sub !== undefined ) {
			savedCategories.sub.forEach( (category) => {
				this.selectedSubcategories.push(category)
			});
		}

	},
	watch: {
		/**
		 * When parent categories are selected, save the value in preparation for storage
		 * and determine if we're going to show the sub categories.
		 */
		selectedCategories: {
			handler: function () {
				this.storeSelectedCategories()
				if ( this.subcategoriesSelectable() && this.selectedCategories !== null && this.selectedCategories.length > 0 ) {
					this.displaySubcategories = true
					this.loadSubcategories()
				} else {
					this.displaySubcategories = false
				}
			},
		},
		/**
		 * When sub categories are selected, save the value in preparation for storage.
		 */
		selectedSubcategories: {
			handler: function() {
				this.storeSelectedCategories()
			}
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
		 * Store the selected categories and subcategories within the form's field
		 * in preparation for storage into the database.
		 */
		storeSelectedCategories() {
			var categoriesToSave = {
				'parent': this.selectedCategories,
				'sub': this.selectedSubcategories,
			}
			document.getElementById('pno-field-listing_categories').value = JSON.stringify(categoriesToSave);
		},
		/**
		 * Get categories loaded into the field from the database.
		 */
		getSavedCategories() {
			return document.getElementById('pno-field-listing_categories').value ? JSON.parse( document.getElementById('pno-field-listing_categories').value ) : false
		}
	}
});

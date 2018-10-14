/*global Vue:true*/
/*global jQuery:true*/
/*global pno_submission:true*/
import EventBus from './event-bus'
import axios from 'axios'

Vue.component('pno-listing-tags-selector', {
	data() {
		return {
			selectedTags: [],
			availableTags: [],
			loading: false,
			timeout: null,
		}
	},
	watch: {
		/**
		 * Watch for changes to the vue model and store changes into the frontend field
		 * so that we can use it via php when submitting the form.
		 */
		selectedTags: {
			handler: function () {
				document.getElementById('listing_tags').value = JSON.stringify(this.selectedTags);
			},
			deep: true
		}
	},
	mounted() {

		var vm = this

		this.loadStarterTags()

		/**
		 * Catch changes within the listings category selector and load appropriate tags.
		 */
		EventBus.$on( 'category-changed', function (payLoad) {

			clearTimeout(this.timeout);

			// Load tags via ajax.
			this.timeout = setTimeout(function () {
				vm.loading = true
				vm.loadTags( payLoad )
			}, 500);

		});
	},
	methods: {
		/**
		 * Determine if there are tags available.
		 */
		tagsAreAvailable() {
			if ( Array.isArray( this.availableTags ) && this.availableTags.length > 0 ) {
				return true
			} else {
				return false
			}
		},
		/**
		 * Add the "pno-category-selected" class to the field's element
		 */
		addElementClass() {
			jQuery('.pno-field-listing_tags').addClass("pno-category-selected");
		},
		/**
		 * Remove the "pno-category-selected" class to the field's element
		 */
		removeElementClass() {
			jQuery('.pno-field-listing_tags').removeClass("pno-category-selected");
		},
		/**
		 * Load some tags on page first load.
		 */
		loadStarterTags() {

			this.loading = true
			this.addElementClass()

			axios.get( pno_submission.ajax, {
				params: {
					nonce: pno_submission.get_starter_tags_nonce,
					action: 'pno_get_tags'
				}
			})
			.then( response => {
				this.loading = false
				this.availableTags = response.data.data
			})
			.catch( error => {
				this.loading = false
				this.availableTags = []
				this.removeElementClass()
			})

		},
		/**
		 * Load tags related to the selected listings categories.
		 *
		 * @param {mixed} selectedCategories list of selected categories.
		 */
		loadTags( selectedCategories ) {

			this.loading = true
			this.addElementClass()

			axios.get( pno_submission.ajax, {
				params: {
					categories: selectedCategories,
					nonce: pno_submission.get_tags_nonce,
					action: 'pno_get_tags_from_categories'
				}
			})
			.then( response => {
				this.loading = false
				this.availableTags = response.data.data
			})
			.catch( error => {
				this.loading = false
				this.availableTags = []
				this.removeElementClass()
			})

		}
	}
});

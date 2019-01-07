/*global Vue:true*/
/*global pno_submission:true*/
import Treeselect from '@riophae/vue-treeselect'
import EventBus from './event-bus'

Vue.component('pno-listing-category-selector', {
	components: {
		Treeselect
	},
	props: {
		taxonomy: '',
		emitterid: false,
		terms: false
	},
	data() {
		return {
			value: null,
			options: [],
		}
	},
	mounted() {

		// Parse the terms json string into an object.
		this.options = JSON.parse(this.terms)

		// Load selected categories of a listing from the database when viewing the edit form.
		var savedCategories = this.getSavedCategories()

		if ( savedCategories.length > 0 ) {

			this.value = []

			savedCategories.forEach((category) => {
				this.value.push(category)
			});
		}

	},
	watch: {
		/**
		 * When parent categories are selected, save the value in preparation for storage
		 * and determine if we're going to show the sub categories.
		 */
		value: {
			handler: function () {

				this.emitMethod(this.value)

				document.getElementById('pno-field-listing_categories').value = JSON.stringify(this.value);

			},
		},
	},
	methods: {
		/**
		 * Emit a global event so that some of our components can interact with do
		 * and run specific functionalities.
		 *
		 * @param {*} payLoad
		 */
		emitMethod(payLoad) {
			if (this.emitterid) {
				EventBus.$emit(this.emitterid, payLoad);
			}
		},
		/**
		 * Get categories loaded into the field from the database.
		 */
		getSavedCategories() {
			return document.getElementById('pno-field-listing_categories').value ? JSON.parse(document.getElementById('pno-field-listing_categories').value) : false
		}
	},
});

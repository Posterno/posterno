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

	},
	watch: {
		/**
		 * When parent categories are selected, save the value in preparation for storage
		 * and determine if we're going to show the sub categories.
		 */
		value: {
			handler: function () {

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
	},
});

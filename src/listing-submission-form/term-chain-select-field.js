/*global Vue:true*/
/*global pno_submission:true*/
import Treeselect from '@riophae/vue-treeselect'

Vue.component( 'pno-term-chain-select-field', {
	components: {
		Treeselect
	},
	props: {
		taxonomy: '',
		terms: false
	},
	beforeMount() {

		// Parse the terms json string into an object.
		this.options = JSON.parse(this.terms)

	},
	data() {
		return {
			value: null,
			options: [],
		}
	},
	methods: {

	},
	watch: {
		/**
		 * Watch for changes to the vue model and store changes into the frontend field
		 * so that we can use it via php when submitting the form.
		 */
		selectedParentTerms: {
			handler: function ( newValue, oldValue ) {

			},
			deep: true
		}
	},
});


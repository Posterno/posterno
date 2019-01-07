/*global Vue:true*/
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
		value: {
			handler: function () {
				var selectedTerms = JSON.stringify(this.value)
				var HolderID = this.$el.nextElementSibling.id
				var HolderClass = this.$el.nextElementSibling.className
				if ( HolderClass === "pno-chain-select-value-holder" ) {
					document.getElementById(HolderID).value = selectedTerms;
				}
			},
			deep: true
		}
	},
});

/*global Vue:true*/
import EventBus from './event-bus'
import _isEmpty from 'lodash.isempty'
import Treeselect from '@riophae/vue-treeselect'

Vue.component( 'pno-term-chain-select-field', {
	components: {
		Treeselect
	},
	props: {
		taxonomy: '',
		emitterid: false,
		terms: false
	},
	beforeMount() {

		// Parse the terms json string into an object.
		this.options = JSON.parse(this.terms)

	},
	mounted() {

		let storedValues = this.getSavedValues()

		if ( ! _isEmpty( storedValues ) ) {

			var values = JSON.parse(storedValues)

			if ( values.length > 0 ) {

				this.value = []

				values.forEach( (term) => {
					this.value.push( term )
				})

			} else if ( values !== '[]' && values.length === undefined ) {
				this.value = values
			}

		}

	},
	data() {
		return {
			value: null,
			options: [],
		}
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
		getSavedValues() {

			var HolderID = this.$el.nextElementSibling.id
			var HolderClass = this.$el.nextElementSibling.className

			if ( HolderClass === "pno-chain-select-value-holder" ) {
				return document.getElementById(HolderID).value
			}

			return false
		}
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

				this.emitMethod(this.value)

				if ( HolderClass === "pno-chain-select-value-holder" ) {
					document.getElementById(HolderID).value = selectedTerms;
				}

			},
			deep: true
		}
	},
});


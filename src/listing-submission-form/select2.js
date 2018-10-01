/*global Vue:true*/
/*global jQuery:true*/
import EventBus from './event-bus'

Vue.component('pno-select2', {
	data() {
		return {
			select2: null,
			emitterid: null,
		}
	},
	model: {
		event: 'change',
		prop: 'value'
	},
	props: {
		placeholder: {
			type: String,
			default: ''
		},
		options: {
			type: Array,
			default: () => []
		},
		disabled: {
			type: Boolean,
			default: false
		},
		settings: {
			type: Object,
			default: () => {}
		},
		value: null
	},
	watch: {
		/**
		 * Watch for changes to the select options.
		 * @param {*} val
		 */
		options(val) {
			this.setOption(val);
		},

		/**
		 * Watch for changes to the selected value.
		 * @param {*} val
		 */
		value(val) {
			this.setValue(val);
		}
	},
	methods: {
		/**
		 * Set new options within select2 dropdown.
		 * @param {*} val
		 */
		setOption(val = []) {
			this.select2.empty();
			this.select2.select2({
				...this.settings,
				data: val,
				width: '100%'
			});
			this.setValue(this.value);
		},
		/**
		 * Emit a global event so that some of our components can interact with do
		 * and run specific functionalities.
		 *
		 * @param {*} payLoad
		 */
		emitMethod( payLoad ) {
			if ( this.emitterid ) {
				EventBus.$emit( this.emitterid, payLoad);
			}
		},
		/**
		 * Set the value of component when picking an option.
		 * @param {*} val
		 */
		setValue(val) {
			if (val instanceof Array) {
				this.select2.val([...val]);
			} else {
				this.select2.val([val]);
			}
			this.select2.trigger('change');
			this.emitMethod( val )
		}
	},
	mounted() {
		this.select2 = jQuery(this.$el)
			.find('select')
			.select2({
				...this.settings,
				data: this.options,
				placeholder: jQuery(this.$el).data('placeholder'),
				width: '100%'
			})
			.on('select2:select select2:unselect', ev => {
				const {
					id,
					text,
					selected
				} = ev['params']['data'];

				const selectValue = this.select2.val();
				this.$emit('change', selectValue);
				this.$emit('select', {
					id,
					text,
					selected
				});
			});

		this.emitterid = jQuery(this.$el).data('emitterid')
		this.setValue(this.value);
	},
	beforeDestroy() {
		this.select2.select2('destroy');
	}
});

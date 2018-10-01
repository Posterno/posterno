/*global Vue:true*/
/*global jQuery:true*/
Vue.component('pno-flatpickr', {
	props: {
		value: {
			default: null,
			validator(value) {
				return value === null || value instanceof Date || typeof value === 'string' || value instanceof String || value instanceof Array || typeof value === 'number'
			}
		},
		config: {
			type: Object,
			default: () => ({
				wrap: false,
				defaultDate: null,
			})
		},
	},
	data() {
		return {
			fp: null
		}
	},
	mounted() {
		this.fp = jQuery(this.$el)
			.flatpickr({});
	},
	beforeDestroy() {
		if (this.fp) {
			this.fp.destroy();
			this.fp = null;
		}
	}
});

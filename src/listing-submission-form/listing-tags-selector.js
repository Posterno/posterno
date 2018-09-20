/*global Vue:true*/
import EventBus from './event-bus'

Vue.component('pno-listing-tags-selector', {
	data() {
		return {
			selectedTags: [],
			loading: false,
		}
	},
	mounted() {
		EventBus.$on( 'category-changed', function (payLoad) {
			console.log(payLoad)
		});
	}
});

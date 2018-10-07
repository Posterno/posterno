/*global Vue:true*/
Vue.component('pno-listing-location-selector', {
	data() {
		return {
			address: '',
			customAddress: '',
			coordinates: {
				lat: '',
				lng: '',
			},
		}
	},
});

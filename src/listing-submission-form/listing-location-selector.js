/*global Vue:true*/
/*global pno_settings:true*/
const loadGoogleMapsApi = require('load-google-maps-api')

Vue.component('pno-listing-location-selector', {
	data() {
		return {
			address: '',
			customAddress: '',
			coordinates: {
				lat: '',
				lng: '',
			},

			pinLock: true,
			customCoordinates: false,
			allowCustomAddress: false,
		}
	},
	mounted() {

		loadGoogleMapsApi({
			key: pno_settings.googleMapsApiKey,
		}).then(function (googleMaps) {
			new googleMaps.Map(document.querySelector('.pno-listing-submission-map'), {
				center: {
					lat: 40.7484405,
					lng: -73.9944191
				},
				zoom: 12
			})
		}).catch(function (error) {
			console.error(error)
		})

	},
	methods: {
		/**
		 * Retrieve the provider selected within the admin panel.
		 */
		getMapProvider() {
			return pno_settings.mapProvider !== undefined ? pno_settings.mapProvider : 'googlemaps'
		},
		/**
		 * Toggle the pinLock property on click. Flag to allow the user drag/click to set the map marker's position.
		 */
		togglePinLock() {
			this.pinLock = !this.pinLock;
		},
		/**
		 * Toggle display of the custom coordinates fields.
		 */
		toggleCustomCoordinates() {
			this.customCoordinates = !this.customCoordinates;
		},
		/**
		 * Toggle display of the custom address fields.
		 */
		toggleCustomAddress() {
			this.allowCustomAddress = !this.allowCustomAddress;
		}
	}
});

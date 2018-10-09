/*global Vue:true*/
/*global pno_settings:true*/
let PosternoMapApi = null

if (pno_settings.mapProvider === 'googlemaps') {
	PosternoMapApi = require('load-google-maps-api')
}

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

			mapObject: null,
			markerObject: null,
			geocoderObject: null,
			error: false,
			errorMessage: '',
		}
	},
	/**
	 * One page load, instantiate the map through the selected provider.
	 * Create a marker on the starting position selected into the admin panel.
	 */
	mounted() {

		var vm = this

		PosternoMapApi({
			key: pno_settings.googleMapsApiKey,
		}).then(function (googleMaps) {

			vm.mapObject = new googleMaps.Map(document.querySelector('.pno-listing-submission-map'), {
				center: {
					lat: parseFloat(pno_settings.startingLatitude),
					lng: parseFloat(pno_settings.startingLongitude),
				},
				zoom: parseFloat(pno_settings.mapZoom),
				fullscreenControl: false,
				streetViewControl: false,
				mapTypeControl: false,
			})

			// Store geocoding instance.
			vm.geocoderObject = new googleMaps.Geocoder

			// Create coordinates for the starting marker.
			var myLatLng = {
				lat: parseFloat(pno_settings.startingLatitude),
				lng: parseFloat(pno_settings.startingLongitude),
			};

			var marker = new googleMaps.Marker({
				position: myLatLng,
				map: vm.mapObject,
				draggable: vm.markerIsDraggable(),
			});

			// When the marker has been dropped, grab coordinates and geocode an address.
			googleMaps.event.addListener(marker, 'dragend', function (event) {
				let position = marker.getPosition()
				vm.setCoordinates(position.lat(), position.lng())
				vm.setAddressFromCoordinates(position.lat(), position.lng())
			});

			vm.markerObject = marker

		}).catch(function (error) {
			vm.setError( error )
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

			// Update the marker's draggable status within Google Maps.
			if (this.getMapProvider() === 'googlemaps') {
				if (this.markerObject.getDraggable() === true) {
					this.markerObject.setDraggable(false)
				} else {
					this.markerObject.setDraggable(true)
				}
			}

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
		},
		/**
		 * Determine if the toggled status for the dragging property of the marker on the map.
		 */
		markerIsDraggable() {
			return this.pinLock === false ? true : false
		},
		/**
		 * Update the component's coordinates and refresh the map if needed.
		 */
		setCoordinates(lat, lng) {
			this.coordinates.lat = lat
			this.coordinates.lng = lng
		},
		/**
		 * Retrieve a geocoded address from given coordinates.
		 */
		setAddressFromCoordinates(lat, lng) {

			var vm = this

			var latlng = {
				lat: parseFloat(lat),
				lng: parseFloat(lng)
			};

			if (this.getMapProvider() === 'googlemaps') {
				this.geocoderObject.geocode({
					'location': latlng
				}, function ( results, status ) {
					if ( status === 'OK' ) {
						if ( results[0] ) {
							vm.address = results[0].formatted_address
						} else {
							this.setError( pno_settings.labels.addressNotFound )
						}
					} else {
						this.setError( status )
					}
				});
			}

		},
		/**
		 * Trigger an error message to be displayed on the frontend.
		 */
		setError( message ) {
			this.error = true
			this.errorMessage = message
			setTimeout( () => {
				this.clearError()
			}, 3000)
		},
		/**
		 * Remove the error message from the frontend.
		 */
		clearError() {
			this.error = false
			this.errorMessage = null
		}
	}
});

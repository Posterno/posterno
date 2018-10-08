/*global Vue:true*/
/*global pno_settings:true*/
let PosternoMapApi = null

if ( pno_settings.mapProvider === 'googlemaps' ) {
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
			markerObject: null
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
					lat: parseFloat( pno_settings.startingLatitude ),
					lng: parseFloat( pno_settings.startingLongitude ),
				},
				zoom: parseFloat( pno_settings.mapZoom ),
				fullscreenControl: false,
				streetViewControl: false,
				mapTypeControl: false,
			})

			// Create coordinates for the starting marker.
			var myLatLng = {
				lat: parseFloat( pno_settings.startingLatitude ),
				lng: parseFloat( pno_settings.startingLongitude ),
			};

			var marker = new googleMaps.Marker({
				position: myLatLng,
				map: vm.mapObject,
				draggable: vm.markerIsDraggable(),
			});

			googleMaps.event.addListener(marker, 'dragend', function (event) {
				let position = marker.getPosition()
				vm.setCoordinates( position.lat(), position.lng() )
			});

			vm.markerObject = marker

		}).catch( function (error) {
			console.error( error )
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

			if ( this.getMapProvider() === 'googlemaps' ) {
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
		setCoordinates( lat, lng ) {
			this.coordinates.lat = lat
			this.coordinates.lng = lng
		}
	}
});

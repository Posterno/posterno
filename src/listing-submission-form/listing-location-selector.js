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
			coordinates: {
				lat: '',
				lng: '',
			},
			pinLock: true,
			customCoordinates: false,

			mapObject: null,
			markerObject: null,
			geocoderObject: null,
			autocompleteObject: null,
			error: false,
			errorMessage: '',
			geolocationLoading: false,
			coordinatesBtnDisabled: true,
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
			libraries: [ 'places' ]
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

			// Initialize the autocomplete.
			var autoCompleteInput = document.getElementById('pno-address-autocomplete')
			vm.autocompleteObject = new googleMaps.places.Autocomplete(autoCompleteInput);

			// Bind the map's bounds (viewport) property to the autocomplete object,
			// so that the autocomplete requests use the current map bounds for the
			// bounds option in the request.
			vm.autocompleteObject.bindTo('bounds', vm.mapObject);

			vm.autocompleteObject.addListener('place_changed', function () {

				var place = vm.autocompleteObject.getPlace();

				// User entered the name of a Place that was not suggested and
				// pressed the Enter key, or the Place Details request failed.
				if ( ! place.geometry ) {
					return;
				}

				var lat = place.geometry.location.lat(),
					lng = place.geometry.location.lng();

				vm.setCoordinates( lat, lng )
				vm.setMapLocation( lat, lng )
				vm.setMarkerLocation( lat, lng )

				if ( place.formatted_address !== undefined ) {
					vm.address = place.formatted_address
				}

			});

			// Load address stored in the database when viewing the editing database.
			var savedAddress = vm.getSavedAddressLocation()

			if ( savedAddress.hasOwnProperty('lat') && savedAddress.hasOwnProperty('lng') ) {
				var savedLat = savedAddress.lat
				var savedLng = savedAddress.lng
				vm.setCoordinates( savedLat, savedLng )
				vm.setAddressFromCoordinates( savedLat, savedLng )
				vm.setMapLocation( savedLat, savedLng )
				vm.setMarkerLocation( savedLat, savedLng )
			}

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

			this.clearError()

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
							vm.setError( pno_settings.labels.addressNotFound )
						}
					} else {
						if ( status === 'ZERO_RESULTS' ) {
							vm.setError( pno_settings.labels.addressNotFound )
							vm.setCoordinates( '', '' ) // Reset coordinates value.
						} else {
							vm.setError( status )
						}
					}
				});
			}

		},
		/**
		 * Load user's current position from the browser.
		 */
		geolocate() {

			var vm = this

			this.geolocationLoading = true

			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function (position) {

					vm.setCoordinates( position.coords.latitude, position.coords.longitude )
					vm.setAddressFromCoordinates( position.coords.latitude, position.coords.longitude )
					vm.setMapLocation( position.coords.latitude, position.coords.longitude )
					vm.setMarkerLocation( position.coords.latitude, position.coords.longitude )

					vm.geolocationLoading = false

				}, function () {
					vm.geolocationLoading = false
					vm.setError( pno_settings.labels.geolocationFailed )
				});

			} else {

				this.geolocationLoading = false
				this.setError( pno_settings.labels.geolocationNotSupported )

			}

		},
		/**
		 * Move the center of the map to another position given coordinates.
		 */
		setMapLocation( lat, lng ) {

			this.clearError()

			if ( lat && lng ) {
				if (this.getMapProvider() === 'googlemaps') {

					this.mapObject.setCenter({
						lat: parseFloat(lat),
						lng: parseFloat(lng)
					})

					this.mapObject.setZoom( parseFloat( pno_settings.mapZoom ) )

				}
			}

		},
		/**
		 * Adjust the position of the marker on the map given coordinates.
		 */
		setMarkerLocation( lat, lng ) {

			this.clearError()

			if ( lat && lng ) {
				var latlng = {
					lat: parseFloat(lat),
					lng: parseFloat(lng)
				};

				if (this.getMapProvider() === 'googlemaps') {
					this.markerObject.setPosition( latlng )
				}
			}

		},
		/**
		 * Save coordinates typed through the custom fields,
		 * verify they're valid and geocode them through the maps api.
		 */
		saveCustomCoordinates() {

			this.setAddressFromCoordinates( this.coordinates.lat, this.coordinates.lng )

			setTimeout( () => {
				if ( this.coordinates.lat === '' || this.coordinates.lng === '' ) {
					this.disableCoordinatesSaving()
				} else if ( this.coordinates.lat !== '' && this.coordinates.lng !== '' ) {
					this.setMapLocation( this.coordinates.lat, this.coordinates.lng )
					this.setMarkerLocation( this.coordinates.lat, this.coordinates.lng )
					this.disableCoordinatesSaving()
				}
			}, 500 )

		},
		/**
		 * Enable the "save coordinates" button when user manually types custom coordinates.
		 */
		enableCoordinatesSave() {
			if ( this.coordinatesBtnDisabled === true ) {
				this.coordinatesBtnDisabled = false
			}
		},
		/**
		 * Disable the button to save custom coordinates.
		 */
		disableCoordinatesSaving() {
			this.coordinatesBtnDisabled = true
		},
		/**
		 * Trigger an error message to be displayed on the frontend.
		 */
		setError( message ) {
			this.error = true
			this.errorMessage = message
		},
		/**
		 * Remove the error message from the frontend.
		 */
		clearError() {
			this.error = false
			this.errorMessage = null
		},
		/**
		 * Save vue's data within the hidden input field on the
		 */
		saveField() {
			var data = {
				coordinates: this.coordinates,
				address: this.address,
			}
			document.getElementById('pno-field-listing_location').value = JSON.stringify(data);
		},
		/**
		 * Get the location stored into the database to display within the edit form.
		 */
		getSavedAddressLocation() {
			return document.getElementById('pno-field-listing_location').value ? JSON.parse(document.getElementById('pno-field-listing_location').value) : false
		}
	},
	watch: {
		/**
		 * Watch for changes to the vue model and store changes into the frontend field
		 * so that we can use it via php when submitting the form.
		 */
		address: {
			handler: function () {
				this.saveField()
			},
		},
		coordinates: {
			handler: function () {
				this.saveField()
			},
			deep: true
		},
	}
});

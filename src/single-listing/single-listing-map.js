/*global Vue:true*/
/*global pno_settings:true*/
let PosternoMapApi = null

if (pno_settings.mapProvider === 'googlemaps') {
	PosternoMapApi = require('load-google-maps-api')
}

Vue.component( 'pno-single-listing-map', {
	props: {
		lat: '',
		lng: '',
	},
	data() {
		return {
			mapObject: null,
			markerObject: null,
		}
	},
	mounted() {

		var vm = this

		PosternoMapApi({
			key: pno_settings.googleMapsApiKey,
			libraries: [ 'places' ]
		}).then(function (googleMaps) {

			vm.mapObject = new googleMaps.Map(document.querySelector('.pno-single-listing-map'), {
				center: {
					lat: parseFloat(vm.lat),
					lng: parseFloat(vm.lng),
				},
				zoom: parseFloat(pno_settings.mapZoom),
				fullscreenControl: false,
				streetViewControl: false,
				mapTypeControl: false,
			})

			// Create coordinates for the starting marker.
			var myLatLng = {
				lat: parseFloat(vm.lat),
				lng: parseFloat(vm.lng),
			};

			var marker = new googleMaps.Marker({
				position: myLatLng,
				map: vm.mapObject,
				draggable: false,
			});

			vm.markerObject = marker

		}).catch(function (error) {
			console.error( error )
		})

	},

});

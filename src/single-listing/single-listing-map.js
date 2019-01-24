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

			var mapContainers = document.querySelectorAll('.pno-single-listing-map'), i;

			for (i = 0; i < mapContainers.length; ++i) {
				//divs[i].style.color = "green";

				vm.mapObject = new googleMaps.Map( mapContainers[i] , {
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

			}

		}).catch(function (error) {
			console.error( error )
		})

	},

});

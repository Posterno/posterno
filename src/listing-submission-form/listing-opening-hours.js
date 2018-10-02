/*global Vue:true*/
/*global pno_submission:true*/
Vue.component('pno-listing-opening-hours-selector', {
	data() {
		return {
			timeslots: {
				monday: {
					type: 'hours',
					hours: [{
						opening: '',
						closing: ''
					}]
				},
				tuesday: {
					type: '',
					hours: [{
						opening: '',
						closing: ''
					}]
				},
				wednesday: {
					type: '',
					hours: [{
						opening: '',
						closing: ''
					}]
				},
				thursday: {
					type: '',
					hours: [{
						opening: '',
						closing: ''
					}]
				},
				friday: {
					type: '',
					hours: [{
						opening: '',
						closing: ''
					}]
				},
				saturday: {
					type: '',
					hours: [{
						opening: '',
						closing: ''
					}]
				},
				sunday: {
					type: '',
					hours: [{
						opening: '',
						closing: ''
					}]
				},
			},
		}
	},
	methods: {
		/**
		 * Verify if the user can add hours to a given day depending on the time slot selected.
		 *
		 * @param {string} day day string to verify.
		 */
		canEnterHours( day = false ) {
			if ( day ) {
				return this.timeslots[ day ].type === 'hours'
			} else {
				return false
			}
		},
		/**
		 * Add new time sets to a given day.
		 *
		 * @param {string} day the day where we're adding new time sets.
		 */
		addHours( day = false ) {

			if ( ! day ) {
				return false
			}

			this.timeslots[ day ].hours.push({
				opening: '',
				closing: ''
			})

		},
		/**
		 * Remove a timeset from a given day.
		 *
		 * @param {string} day day from which we're going to remove a timeset.
		 * @param {int} index the object index that we're going to remove.
		 */
		deleteHours(day, index) {
			if (index !== -1) {
				this.timeslots[ day ].hours.splice(index, 1);
			}
		},
		/**
		 * Maybe reset timeslot hours if the selected timeslot does not allow for hours.
		 *
		 * @param {string} day the day we're probably going to reset timeslots for.
		 */
		maybeResetTimeslots( day ) {
			if ( ! this.canEnterHours( day ) ) {
				this.timeslots[day].hours = [{
					opening: '',
					closing: ''
				}]
			}
		}
	},
	watch: {
		/**
		 * Watch for changes to the vue model and store changes into the frontend field
		 * so that we can use it via php when submitting the form.
		 */
		timeslots: {
			handler: function () {
				document.getElementById('listing_opening_hours').value = JSON.stringify(this.timeslots);
			},
			deep: true
		}
	}
});

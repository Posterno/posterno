/**
 * Enhance the frontend listing submission form with custom inline templates.
 */

/*global Vue:true*/
import './select2.js'
import './flatpickr.js'
import './social-profiles-field.js'
import './listing-category-selector.js'
import './listing-tags-selector.js'
import './listing-opening-hours.js'
import './listing-location-selector.js'

new Vue().$mount('#pno-form-listing_submission_form, #pno-form-listing_editing_form')

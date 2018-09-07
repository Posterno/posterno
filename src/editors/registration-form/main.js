import Vue from 'vue'
import App from './App.vue'
import VueWP from '@alessandrotesoro/vuewp'
import VModal from 'vue-js-modal'

Vue.use(VueWP)
Vue.use(VModal, {
	dialog: true,
	dynamic: true
})
Vue.config.productionTip = false

new Vue({
	render: h => h(App)
}).$mount('#posterno-registration-form-page')

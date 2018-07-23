import Vue from 'vue'
import App from './App.vue'
import router from './router'
import VueWP from '@alessandrotesoro/vuewp'
import VModal from 'vue-js-modal'

Vue.use(VueWP)
Vue.use(VModal, {
	dialog: true,
	dynamic: true
})
Vue.config.productionTip = false

new Vue({
	router,
	render: h => h(App)
}).$mount('#posterno-custom-fields-page')

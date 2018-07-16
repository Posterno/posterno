import Vue from 'vue'
import App from './App.vue'

import VueWP from '@alessandrotesoro/vuewp';
Vue.use(VueWP);

Vue.config.productionTip = false

new Vue({
	render: h => h(App)
}).$mount('#posterno-custom-fields-page')

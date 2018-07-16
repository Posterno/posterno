import Vue from 'vue'
import App from './App.vue'
import router from './router'
import VueWP from '@alessandrotesoro/vuewp';

Vue.use(VueWP);
Vue.config.productionTip = false

new Vue({
	router,
	render: h => h(App)
}).$mount('#posterno-custom-fields-page')

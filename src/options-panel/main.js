import Vue from 'vue'
import VueWP from '@alessandrotesoro/vuewp';
import App from './App.vue'

Vue.config.productionTip = false

Vue.use(VueWP);

new Vue({
  render: h => h(App)
}).$mount('#posterno-options-page')

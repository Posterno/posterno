import Vue from 'vue'
import Router from 'vue-router'
import EditorSelector from './editor-selector.vue'
import Editor from './editor.vue'

Vue.use(Router)

export default new Router({
	routes: [
		{
			path: '/',
			name: 'home',
			component: EditorSelector
		},
		{
			path: '/profile-fields',
			name: 'profile-fields',
			component: Editor,
			props: {
				type: 'users'
			}
		},
		{
			path: '/listings-fields',
			name: 'listings-fields',
			component: Editor,
			props: {
				type: 'listings'
			}
		}
	]
})

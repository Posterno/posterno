import Vue from 'vue'
import Router from 'vue-router'
import EditorSelector from './editor-selector.vue'
import ProfileEditor from './profile-editor.vue'
import RegistrationEditor from './registration-editor.vue'
import ListingsEditor from './listings-editor.vue'

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
			component: ProfileEditor,
			props: {
				type: 'profile'
			}
		},
		{
			path: '/listings-fields',
			name: 'listings-fields',
			component: ListingsEditor,
			props: {
				type: 'listings'
			}
		},
		{
			path: '/registration-form',
			name: 'registration-form',
			component: RegistrationEditor,
			props: {
				type: 'registration'
			}
		}
	]
})

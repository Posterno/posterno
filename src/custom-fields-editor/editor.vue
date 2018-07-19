<template>
	<div id="pno-custom-fields-editor-wrapper" class="wrap">

		<h1>
			<img :src="logo_url">
			{{labels[type].title}}
			<ul class="title-links">
				<li>
					<a href="#" class="page-title-action back-link" @click="$router.go(-1)">
						<span class="dashicons dashicons-arrow-left-alt"></span>
					</a>
				</li>
				<li>
					<a href="https://posterno.com/addons" target="_blank" class="page-title-action">{{labels.addons}}</a>
				</li>
				<li>
					<a href="https://docs.posterno.com/" target="_blank" class="page-title-action">{{labels.documentation}}</a>
				</li>
			</ul>
		</h1>

		<div class="tables-wrapper">

			<wp-notice type="success" dismissible v-if="success"><strong>{{labels.success}}</strong></wp-notice>
			<wp-notice type="error" dismissible v-if="error"><strong>{{error_message}}</strong></wp-notice>

			<wp-button type="primary">{{labels[type].add_new}}</wp-button>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="move-col">
							<span class="dashicons dashicons-menu"></span>
						</th>
						<th scope="col" class="column-primary">{{labels.table.title}}</th>
						<th scope="col">{{labels.table.type}}</th>
						<th scope="col" class="icon-col">{{labels.table.required}}</th>
						<th scope="col" class="icon-col">{{labels.table.privacy}}</th>
						<th scope="col" class="icon-col">{{labels.table.editable}}</th>
						<th scope="col">{{labels.table.actions}}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-if="fields && !loading" v-for="(field, id) in fields" :key="id">
						<td class="order-anchor align-middle">
							<span class="dashicons dashicons-menu"></span>
						</td>
						<td class="column-primary">
							<strong>{{field.title}}</strong>
						</td>
						<td>
							{{field.type}}
						</td>
						<td>
							<span class="dashicons dashicons-yes" v-if="isRequired(field.required)"></span>
						</td>
						<td></td>
						<td></td>
						<td>
							<a href="#" class="button"><span class="dashicons dashicons-edit"></span> {{labels.table.edit}}</a>
							<a href="#" class="button error"><span class="dashicons dashicons-trash"></span> {{labels.table.delete}}</a>
						</td>
					</tr>
					<tr class="no-items" v-if="fields < 1 && ! loading">
						<td class="colspanchange" colspan="7">
							<strong>{{labels.table.not_found}}</strong>
						</td>
					</tr>
					<tr class="no-items" v-if="loading">
						<td class="colspanchange" colspan="7">
							<wp-spinner></wp-spinner>
						</td>
					</tr>
				</tbody>
			</table>

		</div>

	</div>
</template>

<script>
import axios from 'axios'
import qs from 'qs'

export default {
	name: 'editor',
	props: {
		type: '',
	},
	data() {
		return {
			logo_url:      pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:        pno_fields_editor.labels,

			// App status.
			loading:       true,
			success:       false,
			error:         false,
			error_message: '',

			// DB Data.
			fields:        []
		}
	},
	/**
	 * On page load, retrieve the registered fields.
	 */
	mounted() {
		this.load_fields()
	},
	methods: {

		/**
		 * Load registered fields for the enquired type.
		 */
		load_fields() {

			this.loading = true
			this.success = false
			this.error   = false

			axios.get( pno_fields_editor.rest + 'posterno/v1/custom-fields/' + this.type , {
				headers: {
					'X-WP-Nonce': pno_fields_editor.nonce
				},
				params: {
					nonce:  pno_fields_editor.nonce,
				}
			})
			.then( response => {

				if ( typeof response.data === 'object' ) {
					this.fields = response.data
				}

				this.loading = false

			})
			.catch( e => {

				this.loading = false
				this.success = false
				this.error = true

				if ( e.response.data.message ) {
					this.error_message = e.response.data.message
				} else if( typeof e.response.data === 'string' || e.response.data instanceof String ) {
					this.error_message = e.response.data
				}

			})

		},

		/**
		 * Determine if the field is a required one or not.
		 */
		isRequired( is_required ) {
			return is_required === true ? true : false
		},

	}
}
</script>

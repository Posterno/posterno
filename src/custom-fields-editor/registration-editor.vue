<template>

	<div id="pno-custom-fields-editor-wrapper" class="wrap">

		<h1>
			<img :src="logo_url">
			{{labels.registration.title}}
			<ul class="title-links hidden-xs-only">
				<li>
					<router-link to="/" class="page-title-action back-link">
						<span class="dashicons dashicons-arrow-left-alt"></span>
					</router-link>
				</li>
				<li>
					<a href="https://posterno.com/addons" target="_blank" class="page-title-action">{{labels.addons}}</a>
				</li>
				<li>
					<a href="https://docs.posterno.com/" target="_blank" class="page-title-action">{{labels.documentation}}</a>
				</li>
			</ul>
		</h1>

		<div id="registration-form-editor-wrapper" class="tables-wrapper">

			<wp-notice type="success" dismissible v-if="success"><strong>{{labels.success}}</strong></wp-notice>
			<wp-notice type="error" dismissible v-if="error"><strong>{{error_message}}</strong></wp-notice>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="hidden-xs-only move-col">
							<span class="dashicons dashicons-menu"></span>
						</th>
						<th scope="col" class="column-primary">{{labels.table.title}}</th>
						<th scope="col">{{labels.table.required}}</th>
						<th scope="col">{{labels.table.role}}</th>
						<th scope="col">{{labels.table.actions}}</th>
					</tr>
				</thead>
				<tbody>
					<tr class="no-items" v-if="! loading && upsells.enabled.registration">
						<td class="colspanchange inline-notice-container" colspan="5">
							<wp-notice type="info" alternative class="inline-table-notice" v-html="labels.upsells.registration"></wp-notice>
						</td>
					</tr>
					<tr v-if="fields && !loading" v-for="(field, id) in fields" :key="id">
						<td class="order-anchor align-middle hidden-xs-only">
							<span class="dashicons dashicons-menu"></span>
						</td>
						<td>
							<strong>{{field.name}}</strong>
						</td>
						<td>
							<span class="dashicons dashicons-yes" v-if="isRequired(field.required)"></span>
						</td>
						<td class="column-primary" data-colname="Event">
							<code v-if="field.role" v-html="getRole( field.role )"></code>
							<code v-else>{{labels.table.roles.all}}</code>
						</td>
						<td>
							<a :href="field._links.admin[0].href" class="button"><span class="dashicons dashicons-edit"></span> {{labels.table.edit}}</a>
							<a href="#/registration-form" class="button error" v-if="! field.default" @click="deleteField( field.id, field.title )"><span class="dashicons dashicons-trash"></span> {{labels.table.delete}}</a>
						</td>
					</tr>
					<tr class="no-items" v-if="fields < 1 && ! loading">
						<td class="colspanchange" colspan="5">
							<strong>{{labels.table.not_found}}</strong>
						</td>
					</tr>
					<tr class="no-items" v-if="loading">
						<td class="colspanchange" colspan="5">
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
import balloon from 'balloon-css'
import draggable from 'vuedraggable'
import _find from 'lodash.find'

export default {
	name: 'registration-editor',
	components: {
		draggable,
	},
	data() {
		return {
			logo_url:      pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:        pno_fields_editor.labels,
			roles:         pno_fields_editor.roles,
			upsells:       pno_fields_editor.upsells,

			// App status.
			loading:       true,
			sorting:       false,
			success:       false,
			error:         false,
			error_message: '',

			// DB Data.
			fields:        []
		}
	},
	mounted() {
		this.loadFields()
	},
	methods: {

		/*
		 * Load registration fields from the post type.
		 */
		loadFields() {

			this.loading = true
			this.success = false
			this.error   = false

			axios.get( pno_fields_editor.rest + 'posterno/v1/custom-fields/registration', {
				headers: {
					'X-WP-Nonce': pno_fields_editor.nonce
				},
			})
			.then( response => {

				// Convert the object retrieved from the api,
				// to an array so it can be made sortable by the script.
				if ( typeof response.data === 'object' ) {
					let new_fields = []
					var result = Object.keys(response.data).map( function(key) {
						new_fields.push( response.data[key] )
					})
					this.fields = new_fields
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

		/**
		 * Retrieve the role name from the stored object.
		 */
		getRole( role ) {
			var roles = _find( this.roles, { 'value': role } )
			let foundRole = ''
			if ( roles.label !== undefined ) {
				foundRole = roles.label
			}
			return foundRole
		}

	}
}
</script>

<style lang="scss">

	#registration-form-editor-wrapper {
		margin-top: 20px;
	}

</style>

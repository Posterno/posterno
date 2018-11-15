<template>
	<div id="pno-custom-fields-editor-wrapper" class="wrap">

		<h1>
			<img :src="logo_url">
			{{labels.profile.title}}
			<ul class="title-links hidden-xs-only">
				<li>
					<a href="https://posterno.com/addons" target="_blank" class="page-title-action">{{labels.addons}}</a>
				</li>
				<li>
					<a href="https://docs.posterno.com/" target="_blank" class="page-title-action">{{labels.documentation}}</a>
				</li>
			</ul>
		</h1>

		<v-dialog/>
		<modals-container/>

		<div class="tables-wrapper">

			<wp-notice type="success" dismissible v-if="success"><strong>{{labels.success}}</strong></wp-notice>
			<wp-notice type="error" dismissible v-if="error"><strong>{{error_message}}</strong></wp-notice>

			<wp-button type="primary" @click="showAddNewModal()">{{labels.profile.add_new}}</wp-button> <wp-spinner class="sorting-spinner" v-if="sorting"></wp-spinner>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="move-col" :data-balloon="labels.profile.field_order" data-balloon-pos="right">
							<span class="dashicons dashicons-menu"></span>
						</th>
						<th scope="col" class="column-primary">{{labels.table.title}}</th>
						<th scope="col">{{labels.table.type}}</th>
						<th scope="col" class="icon-col">{{labels.table.required}}</th>
						<th scope="col" class="icon-col" v-if="privacy">{{labels.table.privacy}}</th>
						<th scope="col" class="icon-col">{{labels.table.editable}}</th>
						<th scope="col">{{labels.table.actions}}</th>
					</tr>
				</thead>
				<draggable v-model="fields" :element="'tbody'" :options="{handle:'.order-anchor', animation:150}" @end="onSortingEnd">
					<tr v-if="fields && !loading" v-for="(field, id) in fields" :key="id">
						<td class="order-anchor align-middle">
							<span class="dashicons dashicons-menu"></span>
						</td>
						<td class="column-primary">
							<strong>{{field.name}}</strong>
						</td>
						<td>
							<code>{{field.type_nicename}}</code>
						</td>
						<td>
							<span class="dashicons dashicons-yes" v-if="isRequired(field.required)"></span>
						</td>
						<td v-if="privacy">

						</td>
						<td>
							<span :data-balloon="labels.profile.field_admin_only" data-balloon-pos="down" v-if="isAdminOnly(field.editable)">
								<span class="dashicons dashicons-lock"></span>
							</span>

							<span class="dashicons dashicons-yes" v-else></span>
						</td>
						<td>
							<a :href="field._links.admin[0].href" class="button"><span class="dashicons dashicons-edit"></span></a>
							<a href="#/profile-fields" class="button error" v-if="! field.default" @click="deleteField( field.id, field.name )"><span class="dashicons dashicons-trash"></span></a>
						</td>
					</tr>
					<tr class="no-items" v-if="fields < 1 && ! loading">
						<td class="colspanchange" colspan="6">
							<strong>{{labels.table.not_found}}</strong>
						</td>
					</tr>
					<tr class="no-items" v-if="loading">
						<td class="colspanchange" colspan="6">
							<wp-spinner></wp-spinner>
						</td>
					</tr>
				</draggable>
			</table>

		</div>

	</div>
</template>

<script>
import axios from 'axios'
import qs from 'qs'
import orderBy from 'lodash.orderby'
import balloon from 'balloon-css'
import draggable from 'vuedraggable'
import AddNewModal from '../../modals/add-new-field'
import DeleteFieldModal from '../../modals/delete-field'

export default {
	name: 'editor',
	props: {
		type: 'profile',
	},
	components: {
		draggable,
	},
	data() {
		return {
			logo_url:      pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:        pno_fields_editor.labels,
			pages:         pno_fields_editor.pages,
			privacy:       false,

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
	/**
	 * On page load, retrieve the registered fields.
	 */
	mounted() {
		this.load_fields()

		if ( pno_fields_editor.trashed ) {
			this.success = true
		}

	},
	methods: {

		/**
		 * Load registered fields for the enquired type.
		 */
		load_fields() {

			this.loading = true
			this.success = false
			this.error   = false

			axios.get( pno_fields_editor.rest + 'posterno/v1/custom-fields/profile', {
				headers: {
					'X-WP-Nonce': pno_fields_editor.nonce
				},
				params: {
					nonce:  pno_fields_editor.nonce,
				}
			})
			.then( response => {

				// Convert the object retrieved from the api,
				// to an array so it can be made sortable by the script.
				if ( typeof response.data === 'object' ) {
					let new_fields = []
					var result = Object.keys(response.data).map( function(key) {
						new_fields.push( response.data[key] )
					})
					new_fields = orderBy( new_fields, 'priority', 'asc');
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
		 * Determine if the field is admin only field or not.
		 */
		isAdminOnly( editability ) {
			return editability === 'admin_only' ? true : false
		},

		/**
		 * Process saving of the priority for the fields.
		 */
		onSortingEnd( event ) {

			this.success = false
			this.error   = false
			this.loading = false
			this.sorting = true

			axios.post(
				pno_fields_editor.rest + 'posterno/v1/custom-fields/profile/update-priority',
				qs.stringify( {
					fields: this.fields
				} ),
				{
					headers: {
						'X-WP-Nonce': pno_fields_editor.nonce
					}
				}
			)
			.then( response => {
				this.error   = false
				this.sorting = false
				this.load_fields()
				this.success = true
			})
			.catch( e => {
				this.loading = false
				this.sorting = false
				this.success = false
				this.error = true

				if ( e.response.data.message ) {
					this.error_message = e.response.data.message
				} else if( typeof e.response.data === 'string' || e.response.data instanceof String ) {
					this.error_message = e.response.data
				}

			} );

		},

		/**
		 * Displays the add new profile field modal.
		 */
		showAddNewModal() {

			this.$modal.show( AddNewModal, {
				type: 'profile',
				priority: this.fields.length + 1
			},{ height: '300px' })

		},

		/*
		 * Displays the modal to delete a non default field.
		 */
		deleteField( id, name ) {
			this.$modal.show( DeleteFieldModal, {
				type: 'profile',
				field_id: id,
				name: name,
				/**
				 * Pass a function to the component so we can
				 * then update the app status from the child component response.
				 */
				updateStatus: () => {
					this.load_fields()
					this.success = true
				}
			},{ height: '230px', width: '450px' })

		}

	}
}
</script>

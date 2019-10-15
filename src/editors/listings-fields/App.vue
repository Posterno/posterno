<template>
	<div id="pno-custom-fields-editor">

		<AdminHeader :links="adminLinks">
			{{labels.listing.title}}
		</AdminHeader>

		<div id="pno-custom-fields-editor-wrapper" class="wrap">

			<v-dialog/>
			<modals-container/>

			<div class="tables-wrapper">

				<wp-notice type="success" dismissible v-if="success"><strong>{{labels.success}}</strong></wp-notice>
				<wp-notice type="error" dismissible v-if="error"><strong>{{error_message}}</strong></wp-notice>

				<wp-button type="primary" @click="addNewField()">{{labels.listing.add_new}}</wp-button> <wp-spinner class="sorting-spinner" v-if="sorting"></wp-spinner>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col" class="move-col" :data-balloon="labels.profile.field_order" data-balloon-pos="right">
								<span class="dashicons dashicons-menu"></span>
							</th>
							<th scope="col" class="column-primary">{{labels.table.title}}</th>
							<th scope="col">{{labels.table.type}}</th>
							<th scope="col" class="icon-col">{{labels.table.required}}</th>
							<th scope="col">{{labels.table.actions}}</th>
						</tr>
					</thead>
					<draggable v-model="fields" :tag="'tbody'" handle=".order-anchor" v-bind="draggableOptions()" @end="updateFieldsPriority()">
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
							<td>
								<a :href="field._links.admin[0].href" class="button">{{labels.table.edit}}</a>
								<a href="#" class="button error" v-if="! field.default" @click="deleteField( field.id, field.name )">{{labels.table.delete}}</a>
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
					</draggable>
				</table>

			</div>

		</div>

	</div>

</template>

<script>
import AdminHeader from '../../components/pno-admin-header'

import axios from 'axios'
import qs from 'qs'
import orderBy from 'lodash.orderby'
import balloon from 'balloon-css'
import draggable from 'vuedraggable'
import AddNewModal from '../../modals/add-new-listing-field'
import DeleteFieldModal from '../../modals/delete-field'

export default {
	name: 'listings-fields-editor',
	components: {
		AdminHeader,
		draggable,
	},
	data() {
		return {
			logo_url:      pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:        pno_fields_editor.labels,
			pages:         pno_fields_editor.pages,
			adminLinks: [
				{
					title: pno_fields_editor.labels.addons,
					url: 'https://posterno.com/addons'
				},
				{
					title: pno_fields_editor.labels.import,
					url: pno_fields_editor.import_url
				},
				{
					title: pno_fields_editor.labels.export,
					url: pno_fields_editor.export_url
				},
				{
					title: pno_fields_editor.labels.documentation,
					url: 'https://docs.posterno.com/'
				}
			],

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

		if ( pno_fields_editor.trashed ) {
			this.success = true
		}

	},
	methods: {

		/**
		 * Draggable options for the table.
		 */
		draggableOptions() {
			return {
				animation:150
			}
		},

		/**
		 * Determine if the field is a required one or not.
		 */
		isRequired( is_required ) {
			return is_required === true ? true : false
		},

		/*
		 * Update field's priority within the database.
		 */
		updateFieldsPriority( event ) {

			this.success = false
			this.error   = false
			this.loading = false
			this.sorting = true

			axios.post(
				pno_fields_editor.rest + 'posterno/v1/custom-fields/listing/update-priority',
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
				this.loadFields()
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
		 * Show the add new field modal.
		 */
		addNewField() {

			this.$modal.show( AddNewModal, {
				type: 'listing',
				priority: this.fields.length + 1
			},{ height: '300px' })

		},

		/**
		 * Show the delete field modal.
		 */
		deleteField(id, name) {

			this.$modal.show( DeleteFieldModal, {
				type: 'listing',
				field_id: id,
				name: name,
				/**
				 * Pass a function to the component so we can
				 * then update the app status from the child component response.
				 */
				updateStatus: () => {
					this.loadFields()
					this.success = true
				}
			},{ height: '230px', width: '450px' })

		},

		/**
		 * Load listings fields from the database.
		 */
		loadFields() {

			this.loading = true
			this.success = false
			this.error   = false

			axios.get( pno_fields_editor.rest + 'posterno/v1/custom-fields/listing', {
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

		}

	}
}
</script>

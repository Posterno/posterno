<template>
	<div id="pno-custom-fields-editor-wrapper" class="wrap">

		<h1>
			<img :src="logo_url">
			{{labels.listing.title}}
			<ul class="title-links hidden-xs-only">
				<li>
					<a :href="pages.selector" class="page-title-action back-link">
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
						<th scope="col" class="icon-col">{{labels.table.default}}</th>
						<th scope="col" class="icon-col">{{labels.table.required}}</th>
						<th scope="col" class="icon-col">{{labels.table.editable}}</th>
						<th scope="col">{{labels.table.actions}}</th>
					</tr>
				</thead>
				<draggable v-model="fields" :element="'tbody'" :options="{handle:'.order-anchor', animation:150}" @end="updateFieldsPriority()">
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

						</td>
						<td>
							<span class="dashicons dashicons-yes" v-if="isRequired(field.required)"></span>
						</td>
						<td>
							<span :data-balloon="labels.profile.field_admin_only" data-balloon-pos="down" v-if="isAdminOnly(field.editable)">
								<span class="dashicons dashicons-lock"></span>
							</span>

							<span class="dashicons dashicons-yes" v-else></span>
						</td>
						<td>
							<a :href="field._links.admin[0].href" class="button"><span class="dashicons dashicons-edit"></span> {{labels.table.edit}}</a>
							<a href="#/profile-fields" class="button error" v-if="! field.default" @click="deleteField( field.id, field.name )"><span class="dashicons dashicons-trash"></span> {{labels.table.delete}}</a>
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

export default {
	name: 'listings-fields-editor',
	components: {
		draggable,
	},
	data() {
		return {
			logo_url:      pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:        pno_fields_editor.labels,
			pages:         pno_fields_editor.pages,

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
	methods: {

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

		updateFieldsPriority( event ) {

		},

		addNewField() {

		},

		deleteField() {

		},

		loadFields() {

		}

	}
}
</script>

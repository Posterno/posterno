<template>
	<div tabindex="0" class="pno-modal">
		<button type="button" class="media-modal-close" @click="$emit('close')">
			<span class="media-modal-icon">
				<span class="screen-reader-text">Close panel</span>
			</span>
		</button>

		<div class="media-modal-content">
			<div class="media-frame mode-select wp-core-ui hide-menu" id="__wp-uploader-id-0">
				<div class="media-frame-title">
					<h1>{{ labels[type].add_new }}<span class="dashicons dashicons-arrow-down"></span></h1>
				</div>

				<div class="media-frame-content">

					<wp-notice type="error" alternative v-if="error">{{error_message}}</wp-notice>

					<form action="#" method="post">
						<label for="field-name"><span>{{labels.modal.field_name}}</span></label>
						<input type="text" name="field-name" id="field-name" value="" v-model="field_name">
						<label for="field-type"><span>{{labels.modal.field_type}}</span></label>
						<select name="field-type" id="field-type" v-model="field_type">
							<option v-for="( found_type, type_id ) in field_types" :value="type_id" :key="type_id">{{found_type}}</option>
						</select>
					</form>
				</div>

				<div class="media-frame-toolbar">
					<div class="media-toolbar">
						<div class="media-toolbar-secondary">
						</div>

						<div class="media-toolbar-primary search-form">
							<button type="button" class="button media-button button-primary button-large media-button-select" :disabled="loading" @click="createField()">{{ labels[type].add_new }}</button>
							<wp-spinner v-if="loading"></wp-spinner>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import axios from 'axios'
import qs from 'qs'

export default {
	name: 'add-new-field',
	props: {
		type: '',
		priority: 0,
	},
	data() {
		return {
			labels:        pno_fields_editor.labels,
			loading:       false,
			error:         false,
			error_message: '',

			field_name:    '',
			field_type:    'text',
			field_types:   pno_fields_editor.field_types
		}
	},
	methods: {

		/*
		 * Query the api and create a new field.
		 */
		createField() {

			this.loading = true

			axios.post(
				pno_fields_editor.rest + 'posterno/v1/custom-fields/' + this.type + '/create',
				qs.stringify( {
					field_name: this.field_name,
					field_type: this.field_type,
					priority: this.priority,
				} ),
				{
					headers: {
						'X-WP-Nonce': pno_fields_editor.create_field_nonce
					}
				}
			)
			.then( response => {
				window.location.replace( decodeURIComponent( response.data ) )
			})
			.catch( e => {

				this.loading = false
				this.error = true

				if ( e.response.data.message ) {
					this.error_message = e.response.data.message
				} else if( typeof e.response.data === 'string' || e.response.data instanceof String ) {
					this.error_message = e.response.data
				}

			} );

		}
	}
}
</script>


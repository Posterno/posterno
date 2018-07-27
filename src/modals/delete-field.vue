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
					<h1>{{ labels.table.delete }} "{{name}}"<span class="dashicons dashicons-arrow-down"></span></h1>
				</div>

				<div class="media-frame-content">

					<wp-notice type="error" alternative v-if="error">{{error_message}}</wp-notice>

					<p><strong>{{labels.modal.about_to_delete}} {{name}}.</strong> {{labels.modal.delete_message}}</p>

				</div>

				<div class="media-frame-toolbar">
					<div class="media-toolbar">
						<div class="media-toolbar-secondary">
						</div>

						<div class="media-toolbar-primary search-form">
							<button type="button" class="button media-button button-primary button-large media-button-select" :disabled="loading" @click="deleteField()">{{ labels.table.delete }}</button>
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
	name: 'delete-field',
	props: {
		type: '',
		field_id: 0,
		name: '',
		updateStatus: ''
	},
	data() {
		return {
			labels:        pno_fields_editor.labels,
			loading:       false,
			error:         false,
			error_message: '',
		}
	},
	methods: {
		deleteField() {

			this.loading = true

			axios.delete(
				pno_fields_editor.rest + 'posterno/v1/custom-fields/' + this.type + '/' + this.field_id,
				{
					headers: {
						'X-WP-Nonce': pno_fields_editor.delete_field_nonce
					}
				}
			)
			.then( response => {

				this.loading = false,
				this.updateStatus()
				this.$emit('close')

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

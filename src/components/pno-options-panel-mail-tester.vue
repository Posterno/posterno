<template>
	<div class="pno-email-tester">

		<wp-notice alternative v-if="success">
			<strong>{{success_message}}</strong>
		</wp-notice>

		<wp-notice type="error" alternative v-if="error">
			<strong>{{error_message}}</strong>
		</wp-notice>

		<input
			type="text"
			name="email-address-for-test"
			id="email-address-for-test"
			class="regular-text"
			v-model="email"
			:disabled="loading"
		>
		<br/><br/>
		<wp-button :disabled="loading" @click="sendMail()">{{btn_label}}</wp-button> <wp-spinner v-if="loading"></wp-spinner>
		<br/><br/>
	</div>
</template>

<script>
import axios from 'axios'
import qs from 'qs'

export default {
	name: 'mail-tester',
	data() {
		return {
			email:   pno_settings_page.labels.emails.value,
			loading: false,
			success: false,
			error:   false,
			error_message: '',
			success_message: pno_settings_page.labels.emails.success,
			btn_label: pno_settings_page.labels.emails.button
		}
	},
	methods: {
		/**
		 * Send a test email to the specified address.
		 */
		sendMail() {

			this.success = false
			this.error   = false
			this.loading = true

			axios.post(
				pno_settings_page.rest + 'posterno/v1/options/sendtestmail',
				qs.stringify( {
					email: this.email
				} ),
				{
					headers: {
						'X-WP-Nonce': pno_settings_page.email_nonce
					}
				}
			)
			.then( response => {
				this.loading = false
				this.success = true
				this.error = false
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

			} );

		}
	}
}
</script>

<style lang="scss">

	.pno-email-tester {
		.spinner {
			margin: 5px 0 0 5px;
		}
		.vue-wp-notice {
			p {
				margin: 0.25em 0;
			}
		}
	}

</style>

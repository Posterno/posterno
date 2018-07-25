<template>

	<div id="pno-custom-fields-editor-wrapper" class="wrap">

		<h1>
			<img :src="logo_url">
			{{labels.registration.title}}
			<ul class="title-links hidden-xs-only">
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

		<div id="registration-form-editor-wrapper">

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="hidden-xs-only">
							<span class="dashicons dashicons-menu"></span>
						</th>
						<th scope="col" class="column-primary">Start Date</th>
						<th scope="col">End Date</th>
						<th scope="col">Location</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="hidden-xs-only">
							<span class="dashicons dashicons-menu"></span>
						</td>
						<td>
							WordCamp Metropolis
							<button type="button" class="toggle-row">
								<span class="screen-reader-text">Show more details</span>
							</button>
						</td>
						<td class="column-primary" data-colname="Event">
							2020-01-01
						</td>
						<td>
							2020-01-04
						</td>
					</tr>
				</tbody>
			</table>

		</div>

	</div>

</template>

<script>
export default {
	name: 'registration-editor',
	data() {
		return {
			logo_url:      pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:        pno_fields_editor.labels,

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
	}
}
</script>

<style lang="scss">

	#registration-form-editor-wrapper {
		margin-top: 20px;
	}

</style>

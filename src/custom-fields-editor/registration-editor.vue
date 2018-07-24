<template>

	<div id="pno-custom-fields-editor-wrapper" class="wrap">

		<h1>
			<img :src="logo_url">
			{{labels.registration.title}}
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
}
</script>

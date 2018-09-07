<template>
	<div id="pno-custom-fields-editor-wrapper" class="wrap">
		<h1>
			<img :src="logo_url">
			{{labels.title}}
			<ul class="title-links hidden-xs-only">
				<li>
					<a href="https://posterno.com/addons" target="_blank" class="page-title-action">{{labels.addons}}</a>
				</li>
				<li>
					<a href="https://docs.posterno.com/" target="_blank" class="page-title-action">{{labels.documentation}}</a>
				</li>
			</ul>
		</h1>

		<wp-row :gutter="24">
			<wp-col :xs="24" :md="5">
				<div class="editor-box">
					<h2><span class="dashicons dashicons-list-view"></span></h2>
					<h2>{{labels.custom_listings}}</h2>
					<a href="#" class="button">
						{{labels.custom_fields}}
					</a>
				</div>
			</wp-col>
			<wp-col :xs="24" :md="5">
				<div class="editor-box">
					<h2><span class="dashicons dashicons-id-alt"></span></h2>
					<h2>{{labels.custom_users}}</h2>
					<a :href="pages.profile" class="button">
						{{labels.custom_fields}}
					</a>
				</div>
			</wp-col>
			<wp-col :xs="24" :md="5">
				<div class="editor-box">
					<h2><span class="dashicons dashicons-admin-users"></span></h2>
					<h2>{{labels.custom_registration}}</h2>
					<a :href="pages.registration" class="button">
						{{labels.custom_fields}}
					</a>
				</div>
			</wp-col>
		</wp-row>

	</div>
</template>

<script>
export default {
	name: 'App',
	data() {
		return {
			logo_url: pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:   pno_fields_editor.labels,
			pages:    pno_fields_editor.pages
		}
	}
}
</script>

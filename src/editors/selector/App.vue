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

		<div class="card">
			 <wp-row :gutter="20">
				<wp-col :xs="24" :md="4" class="hidden-sm-and-down">
					<h2><span class="dashicons dashicons-list-view"></span></h2>
				</wp-col>
				<wp-col :span="20">
					<h2>{{labels.custom_listings}}</h2>

					<br/><br/>
				</wp-col>
			</wp-row>
		</div>

		<div class="card">
			 <wp-row :gutter="20">
				<wp-col :xs="24" :md="4" class="hidden-sm-and-down">
					<h2><span class="dashicons dashicons-id-alt"></span></h2>
				</wp-col>
				<wp-col :span="20">
					<h2>{{labels.custom_users}}</h2>
					<p v-html="labels.profile.description"></p>

					<br/><br/>
				</wp-col>
			</wp-row>
		</div>

		<div class="card">
			 <wp-row :gutter="20">
				<wp-col :xs="24" :md="4" class="hidden-sm-and-down">
					<h2><span class="dashicons dashicons-admin-users"></span></h2>
				</wp-col>
				<wp-col :xs="24" :md="20">
					<h2>{{labels.custom_registration}}</h2>
					<p v-html="labels.registration.description"></p>

					<br/><br/>
				</wp-col>
			</wp-row>
		</div>

	</div>
</template>

<script>
export default {
	name: 'App',
	data() {
		return {
			logo_url: pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:   pno_fields_editor.labels
		}
	}
}
</script>

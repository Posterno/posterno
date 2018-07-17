<template>
	<div id="pno-custom-fields-editor-wrapper" class="wrap">

		<h1>
			<img :src="logo_url">
			{{labels[type].title}}
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

		<div class="tables-wrapper">

			<wp-notice type="success" dismissible v-if="success"><strong>{{labels.success}}</strong></wp-notice>
			<wp-notice type="error" dismissible v-if="error"><strong>{{error_message}}</strong></wp-notice>

			<wp-button type="primary">{{labels[type].add_new}}</wp-button>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="move-col">
							<span class="dashicons dashicons-move"></span>
						</th>
						<th scope="col" class="column-primary">{{labels.table.title}}</th>
						<th scope="col">{{labels.table.type}}</th>
						<th scope="col">{{labels.table.required}}</th>
						<th scope="col">{{labels.table.privacy}}</th>
						<th scope="col">{{labels.table.editable}}</th>
						<th scope="col">{{labels.table.actions}}</th>
					</tr>
				</thead>
				<tbody>
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
				</tbody>
			</table>

		</div>

	</div>
</template>

<script>
export default {
	name: 'editor',
	props: {
		type: '',
	},
	data() {
		return {
			logo_url:      pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:        pno_fields_editor.labels,

			// App status.
			loading:       false,
			success:       false,
			error:         false,
			error_message: '',

			// DB Data.
			fields:        []
		}
	},
	methods: {
	}
}
</script>

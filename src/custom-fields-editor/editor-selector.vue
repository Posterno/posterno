<template>
	<div id="pno-custom-fields-editor-wrapper" class="wrap">
		<h1>
			<img :src="logo_url">
			{{labels.title}}
			<ul class="title-links">
				<li>
					<a href="https://posterno.com/addons" target="_blank" class="page-title-action">{{labels.addons}}</a>
				</li>
				<li>
					<a href="https://docs.posterno.com/" target="_blank" class="page-title-action">{{labels.documentation}}</a>
				</li>
			</ul>
		</h1>

		<div class="theme-browser rendered">
			<div class="themes wp-clearfix">
				<div class="theme add-new-theme">
					<router-link to="/profile-fields">
						<div class="theme-screenshot pno-customize-users">
							<span></span>
						</div>
						<h2 class="theme-name">{{labels.custom_users}}</h2>
					</router-link>
				</div>
			</div>
		</div>

	</div>
</template>

<script>
export default {
	name: 'editor-selector',
	data() {
		return {
			logo_url: pno_fields_editor.plugin_url + '/assets/imgs/logo.svg',
			labels:   pno_fields_editor.labels
		}
	}
}
</script>

<style lang="scss">

#pno-custom-fields-editor-wrapper {

	margin-top: 0;

	h1 {
		img {
			float: left;
			height: 28px;
			margin-right: 10px;
			margin-top: 10px;
		}
	}

	.theme-browser {
		margin-top: 30px;
	}

	.pno-customize-users {
		span:after {
			content: '\f110';
			background: none;
		}
	}

	.theme-browser .theme.add-new-theme {
		width: 25%;
	}

	.theme-browser .theme.add-new-theme a:hover span::after,
	.theme-browser .theme.add-new-theme a:focus span::after {
		background: none;
		color: #fff;
	}

	.title-links {
		display: inline-block;
		margin-bottom: 0;
		margin-top: 10px;
		margin-left: 20px;
		li {
			display: inline-block;
			margin-bottom: 0;
		}
	}

	.back-link {
		padding: 4px !important;
		border-radius: 9999px !important;
		.dashicons {
			width: 16px;
			height: 16px;
			font-size: 16px;
			vertical-align: inherit;
			position: relative;
			top: 3px;
		}
	}

}

</style>

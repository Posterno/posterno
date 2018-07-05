<template>
	<div id="posterno-settings-panel" class="wrap">
		<wp-container>
			<!-- Header -->
			<wp-header height="auto">
				<wp-row :gutter="0">
					<wp-col :span="16" class="header-content">
						<h1>
							<img :src="logoURL" :alt="labels.page_title">
							<span>{{labels.page_title}}</span>
						</h1>
						<ul class="title-links">
							<!--<li><a href="http://wpusermanager.com/addons/" class="page-title-action">View Addons</a></li>-->
							<li><a href="https://docs.posterno.com/" class="page-title-action" target="_blank">{{labels.readDocs}}</a></li>
						</ul>
					</wp-col>
					<wp-col :span="8" class="save-area">
						<wp-spinner></wp-spinner>
						<wp-button type="primary">{{labels.save}}</wp-button>
					</wp-col>
				</wp-row>
			</wp-header>
			<!-- end header -->
			<wp-main>Main</wp-main>
		</wp-container>
  	</div>
</template>

<script>
export default {
	name: "app",
	data() {
		return {
			logoURL: pno_settings_page.pluginURL + '/assets/imgs/logo.svg',
			labels: pno_settings_page.labels
		}
	}
};
</script>

<style lang="scss">
body.listings_page_posterno-settings {

	#posterno-settings-panel {
		margin: 0;
	}

	#wpcontent {
    	padding-left: 0;
  	}

	.vuewp-header {
		background-color: #fff;
		box-shadow: 0 1px 0 rgba(200,215,225,0.5), 0 1px 2px #DDD;
		margin-bottom: 40px;
		padding: 20px;
	}

	.save-area {
		text-align: right;
		.spinner {
			margin-right: 10px;
			margin-top: 5px;
		}
	}

	.header-content {
		h1 {
			margin: 0;
			font-size: 23px;
			font-weight: 500;
			padding: 0;
			display: inline-block;
			img {
				float:left;
				height: 28px;
			}
			span {
				position: relative;
				left: 8px;
				margin-right: 25px;
			}
		}
	}

	.title-links {
		display: inline-block;
		margin-bottom: 0;
		margin-top: 10px;
		li {
			display: inline-block;
			margin-bottom: 0;
		}
	}

}
</style>

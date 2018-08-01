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
					<router-link to="/listings-fields" class="button">
						{{labels.custom_fields}}
					</router-link>
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
					<router-link to="/profile-fields" class="button">
						{{labels.custom_fields}}
					</router-link>
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
					<router-link to="/registration-form" class="button">
						{{labels.custom_form}}
					</router-link>
					<br/><br/>
				</wp-col>
			</wp-row>
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

	.card {
		&:hover {
			.dashicons {
				color: #0073aa;
			}
		}
		.dashicons {
			font-size: 70px;
		}
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

	.tables-wrapper {
		margin-top: 30px;

		table {
			margin-top: 20px;

			td {
				vertical-align: middle;
			}

			a.button {
				margin-right: 5px;
				span {
					margin-top: 3px;
				}
				&.error:hover {
					span {
						color: #dc3232;
					}
				}
			}

			.icon-col {
				width: 100px;
			}

			.dashicons-yes {
				color: #46b450;
			}

		}

		.sortable-ghost {
			background: #fffecc;
		}

		.sortable-chosen {
			background: #fffecc;
		}

		.sorting-spinner {
			margin-left:10px;
			margin-top: 4px;
		}

	}

	.move-col {
		width: 30px;
		border-right: 1px solid #e1e1e1;
		text-align: center !important;
	}

	.vue-wp-notice {
		margin-bottom: 20px;
	}

	.order-anchor {
		border-right: 1px solid #e1e1e1;
		text-align: center !important;
		&:hover {
			cursor: move;
		}
	}

	.v--modal {
		box-shadow: 0 5px 15px rgba(0,0,0,.7);
		border-radius: 0;
	}

	.v--modal-overlay {
		background: rgba(0, 0, 0, 0.7);
		z-index: 99999;
	}

}

.pno-modal {

	.media-modal-content {
		background: #efefef;
		min-height: auto;
		height: auto;
	}
	.media-frame-content {
		top: 50px;
		padding: 20px 16px;
		label {
			display: inline-block;
			font-weight: bold;
			color: #000;
			margin-bottom: 5px;
			font-size: 13px;
		}
		input,
		textarea,
		select {
			display: block;
			width: 100%;
			margin-bottom: 15px;
			font-size: 13px !important;
		}
	}
	.spinner {
		margin-top:20px;
	}
}

.inline-notice-container {
	padding: 0 !important;
}
.inline-table-notice {
	margin: 0 !important;
	padding: 1em 1em !important;
}

.upsell-btn {
	display: block !important;
	text-align: center;
}

.upsell-metabox {
	margin-top: 30px;
}

</style>

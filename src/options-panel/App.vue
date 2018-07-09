<template>
	<div id="posterno-settings-panel" class="wrap">
		<wp-container>
			<!-- Header -->
			<wp-header height="auto">
				<wp-row :gutter="0">
					<wp-col :span="16" class="header-content">
						<h1>
							<img :src="logo_url" :alt="labels.page_title">
							<span>{{labels.page_title}}</span>
						</h1>
						<ul class="title-links">
							<li>
								<a href="https://posterno.com/addons" class="page-title-action" target="_blank">{{labels.addons}}</a>
							</li>
							<li><a href="https://docs.posterno.com/" class="page-title-action" target="_blank">{{labels.read_docs}}</a></li>
						</ul>
					</wp-col>
					<wp-col :span="8" class="save-area">
						<wp-spinner v-if="loading"></wp-spinner>
						<wp-button type="primary" :disabled="loading" @click="save()">{{labels.save}}</wp-button>
					</wp-col>
				</wp-row>
			</wp-header>
			<!-- end header -->
			<!-- Options Panel Content -->
			<wp-main>

				<wp-notice type="success" dismissible v-if="success">
					<strong>{{labels.settings_saved}}</strong>
				</wp-notice>

				<wp-notice type="error" v-if="error && error_message">
					<span class="dashicons dashicons-warning"></span> <strong>{{error_message}}</strong>
				</wp-notice>

				<!-- navigation tabs -->
				<wp-tabs>
					<wp-tab-item v-for="( tab, id ) in settings_tabs" :key="id" :label="tab">

						<!-- subsections -->
						<WPNavBarFilter v-if="settings_tab_has_sections( id )">
							<WPNavBarFilterItem v-for="( section, section_id ) in settings_sections[ id ]" :key="section_id" :label="section">

								<div class="settings-form-wrapper in-section">

									<!-- options generator -->
										<table class="form-table">
											<tbody>
												<tr v-for="( setting, setting_id ) in registered_settings[ section_id ]" :key="setting_id">
													<th scope="row">
														<label :for="setting_id">{{setting.label}}</label>
													</th>
													<td>

														<input v-if="setting.type == 'text'" :placeholder="setting.placeholder" type="text" class="regular-text" :name="setting_id" :id="setting_id" v-model="settings[setting_id]">

														<textarea v-else-if="setting.type == 'textarea'" :placeholder="setting.placeholder" cols="50" rows="5" :name="setting_id" :id="setting_id" v-model="settings[setting_id]"></textarea>

														<select v-else-if="setting.type == 'select'" :name="setting_id" :id="setting_id" v-model="settings[setting_id]">
															<option v-for="( option_label, option_id ) in setting.options" :key="option_id"  :value="option_id">{{ option_label }}</option>
														</select>

														<div class="radio-wrapper" v-else-if="setting.type == 'radio'">
															<p v-for="( option_label, option_id ) in setting.options" :key="option_id">
																<label>
																	<input :name="setting_id" :id="setting_id" :value="option_id" class="tog" type="radio" v-model="settings[setting_id]">
																{{ option_label }}</label>
															</p>
														</div>

														<span v-else-if="setting.type == 'checkbox'" class="checkbox-wrapper">
															<input :name="setting_id" :id="setting_id" v-model="settings[setting_id]" type="checkbox">
														</span>

														<div class="multi-check-wrapper" v-else-if="setting.type == 'multicheckbox'">
															<span class="checkbox-wrapper" v-for="( option_label, option_id ) in setting.options" :key="option_id">
																<input :id="option_id" :value="option_id" v-model="settings[setting_id]" type="checkbox">
																<label :for="option_id">{{option_label}}</label>
															</span>
														</div>

														<multiselect
															v-else-if="setting.type == 'multiselect'"
															v-model="settings[setting_id]"
															:options="setting.options"
															track-by="value"
															label="label"
															:placeholder="setting.placeholder"
															:multiple="setting.multiple"
															selectLabel=""
															deselectLabel=""
															:selectedLabel="labels.multiselect.selected"
															>
														</multiselect>

														<p class="description" v-if="setting.description">{{setting.description}}</p>

													</td>
												</tr>
											</tbody>
										</table>
										<!-- end options generator -->

								</div>

							</WPNavBarFilterItem>
						</WPNavBarFilter>
						<div class="settings-form-wrapper no-section" v-else>

							<!-- options generator -->
							<table class="form-table">
								<tbody>
									<tr v-for="( setting, setting_id ) in registered_settings[ id ]" :key="setting_id">
										<th scope="row">
											<label :for="setting_id">{{setting.label}}</label>
										</th>
										<td>

											<input v-if="setting.type == 'text'" :placeholder="setting.placeholder" type="text" class="regular-text" :name="setting_id" :id="setting_id" v-model="settings[setting_id]">

											<textarea v-else-if="setting.type == 'textarea'" :placeholder="setting.placeholder" cols="50" rows="5" :name="setting_id" :id="setting_id" v-model="settings[setting_id]"></textarea>

											<select v-else-if="setting.type == 'select'" :name="setting_id" :id="setting_id" v-model="settings[setting_id]">
												<option v-for="( option_label, option_id ) in setting.options" :key="option_id"  :value="option_id">{{ option_label }}</option>
											</select>

											<div class="radio-wrapper" v-else-if="setting.type == 'radio'">
												<p v-for="( option_label, option_id ) in setting.options" :key="option_id">
													<label>
														<input :name="setting_id" :id="setting_id" :value="option_id" class="tog" type="radio" v-model="settings[setting_id]">
													{{ option_label }}</label>
												</p>
											</div>

											<span v-else-if="setting.type == 'checkbox'" class="checkbox-wrapper">
												<input :name="setting_id" :id="setting_id" v-model="settings[setting_id]" type="checkbox">
											</span>

											<div class="multi-check-wrapper" v-else-if="setting.type == 'multicheckbox'">
												<span class="checkbox-wrapper" v-for="( option_label, option_id ) in setting.options" :key="option_id">
													<input :id="option_id" :value="option_id" v-model="settings[setting_id]" type="checkbox">
													<label :for="option_id">{{option_label}}</label>
												</span>
											</div>

											<multiselect
												v-else-if="setting.type == 'multiselect'"
												v-model="settings[setting_id]"
												:options="setting.options"
												track-by="value"
												label="label"
												:placeholder="setting.placeholder"
												:multiple="setting.multiple"
												selectLabel=""
												deselectLabel=""
												:selectedLabel="labels.multiselect.selected"
												>
											</multiselect>

											<p class="description" v-if="setting.description">{{setting.description}}</p>

										</td>
									</tr>
								</tbody>
							</table>
							<!-- end options generator -->

						</div>

					</wp-tab-item>
				</wp-tabs>
				<!-- end navigation tabs -->

				<div class="footer-save">
					<wp-button type="primary" :disabled="loading" @click="save()">{{labels.save}}</wp-button>
					<wp-spinner v-if="loading"></wp-spinner>
				</div>

			</wp-main>
			<!-- end options panel content -->
		</wp-container>
  	</div>
</template>

<script>
import WPNavBarFilter from '../global-components/WPNavBarFilter.vue'
import WPNavBarFilterItem from '../global-components/WPNavBarFilterItem.vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import lodash_has from 'lodash.has'
import axios from 'axios'
import qs from 'qs'

export default {
	name: "app",
	components: {
		WPNavBarFilter,
		WPNavBarFilterItem,
		Multiselect,
	},
	data() {
		return {
			// Visual stuff.
			logo_url:            pno_settings_page.plugin_url + '/assets/imgs/logo.svg',
			labels:              pno_settings_page.labels,

			// Manage the status of the app.
			success:             false,
			error:               false,
			loading:             false,
			error_message:       false,

			// Database stuff.
			settings_tabs:       pno_settings_page.settings_tabs,
			settings_sections:   pno_settings_page.settings_sections,
			registered_settings: pno_settings_page.registered_settings,
			settings:            pno_settings_page.vuejs_model

		}
	},
	methods: {
		/**
		 * Verify if a settings tab has subsections.
		 */
		settings_tab_has_sections( tab_id ) {
			return lodash_has( this.settings_sections, tab_id );
		},
		/**
		 * Save the settings to the database.
		 */
		save() {

			this.success = false
			this.error   = false
			this.loading = true

			axios.post(
				pno_settings_page.rest + 'posterno/v1/options/save',
				qs.stringify( {
					settings: this.settings
				} ),
				{
					headers: {
						'X-WP-Nonce': pno_settings_page.nonce
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
};
</script>

<style lang="scss">
body.listings_page_posterno-settings {

	.update-nag {
		display: none;
	}

	#posterno-settings-panel {
		margin: 0;
	}

	#wpcontent {
    	padding-left: 0;
  	}

	.vuewp-header {
		background-color: #fff;
		box-shadow: 0 1px 0 rgba(200,215,225,0.5), 0 1px 2px #DDD;
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

	.nav-tab {
		&:focus,
		&:active {
			outline: none;
			box-shadow: none;
		}
	}

	.wp-tabs {
		margin-top: 10px;
	}

	.vue-wp-notice {
		margin-bottom: 20px;
	}

	.vuewp-main {
		overflow: initial;
	}

	.wp-filter-bar-wrapper {
		margin-top: 10px;
	}

	.footer-save {
		margin-top: 30px;
		.spinner {
			margin-top: 5px;
			margin-left: 10px;
		}
	}

	.description {
		font-style: normal;
	}

	.checkbox-wrapper ~ p.description {
		display: inline-block;
		position: relative;
	}

	.multi-check-wrapper {
		.checkbox-wrapper {
			display: block;
			margin-bottom: 7px;
			label {
				position: relative;
				top: -2px;
			}
		}
		~ .description {
			margin-top: 10px;
		}
	}

	.multiselect {
		max-width: 500px;
	}

	.multiselect__tags {
		border-radius: 0;
		border-color: #ddd;
		cursor: pointer;
	}

	.multiselect--above.multiselect--active .multiselect__input,
	.multiselect--active:not(.multiselect--above) .multiselect__input {
		border-radius: 3px;
	}

	.multiselect__tag,
	.multiselect__option--selected.multiselect__option--highlight {
		background: #0073aa;
		color: #fff;
		border-radius: 0;
	}

	.multiselect__tag-icon::after {
		color: #fff;
	}

	.multiselect__option--highlight {
		background: #ddd;
		color: #222;
	}

	.multiselect__tag-icon {
		border-radius: 0;
		margin-left: 5px;
		&:hover {
			background: #dc3232;
		}
	}

	.dashicons-warning {
		margin-right: 10px;
		color: #dc3232;
	}

	.multiselect__content-wrapper {
    	box-shadow: 0 3px 5px rgba(0,0,0,.2);
    	border: 1px solid #ddd;
		border-radius: 0;
		margin-top: 5px;
	}

}
</style>

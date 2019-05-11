<?php
/**
 * The template for displaying the social profiles field.
 *
 * This is a Vuejs inline template.
 *
 * This template can be overridden by copying it to yourtheme/posterno/form-fields/social-profiles-field.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 * @package posterno
 */

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$enabled_networks    = pno_get_option( 'listings_social_profiles' );
$registered_networks = pno_get_registered_social_media();

$has_errors = $data->field->hasErrors() ? true : false;

?>

<div class="pno-social-profiles-selector">

	<!-- start inline vue template -->
	<pno-social-profile-field inline-template>
		<div>
			<div class="form-row" v-for="(option, index) in definedSocialProfiles" :key="index">
				<div class="col-md-6">
					<pno-select2 inline-template v-model="definedSocialProfiles[index].social" data-placeholder="<?php esc_html_e( 'Select network', 'posterno' ); ?>">
						<div class="pno-select2-wrapper">
							<select class="form-control">
								<?php if ( is_array( $enabled_networks ) && ! empty( $enabled_networks ) ) : ?>
									<?php foreach ( $enabled_networks as $network_id ) : ?>
										<?php if ( isset( $registered_networks[ $network_id ] ) ) : ?>
											<option value="<?php echo esc_attr( $network_id ); ?>"><?php echo esc_html( $registered_networks[ $network_id ] ); ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>
					</pno-select2>
				</div>
				<div class="col-md-6">
					<div class="input-group mb-3">
						<input
							type="text"
							class="form-control"
							v-model="definedSocialProfiles[index].url"
						>
						<div class="input-group-append" v-if="index > 0">
							<button @click="deleteSocialProfile( index )" class="btn btn-outline-secondary" type="button">
								<i class="fas fa-trash-alt"></i>
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="text-right">
				<button class="btn btn-light btn-sm" type="button" @click="addNewSocialProfile()">
					<?php esc_html_e( 'Add new profile', 'posterno' ); ?>
				</button>
			</div>

			<input
				type="hidden"
				name="<?php echo esc_attr( $data->field->getName() ); ?>"
				id="pno-field-<?php echo esc_attr( $data->field->getName() ); ?>"
				<?php if ( $has_errors ) : ?>
				class="form-control is-invalid"
				<?php endif; ?>
				value="<?php echo ! empty( $data->field->getValue() ) ? esc_attr( $data->field->getValue() ) : ''; ?>"
			>
		</div>
	</pno-social-profile-field>
	<!-- end inline vue template -->

</div>


<?php
/**
 * The template for displaying the social profiles field.
 *
 * This is a Vuejs inline template.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/social-profiles-field.php
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

?>

<div class="pno-social-profiles-selector">

	<?php if ( ! empty( $data->description ) ) : ?>
		<small class="form-text text-muted">
			<?php echo wp_kses( $data->description ); ?>
		</small>
	<?php endif; ?>

	<!-- start inline vue template -->
	<pno-social-profile-field inline-template>
		<div>
			<div class="row" v-for="(option, index) in definedSocialProfiles" :key="index">
				<div class="col-md-3">
					<pno-select2 inline-template v-model="definedSocialProfiles[index].social" data-placeholder="<?php esc_html_e( 'Select network' ); ?>">
						<div class="pno-select2-wrapper">
							<select class="custom-select">
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
				<div class="col-md-9">
					<div class="input-group mb-3">
						<input
							type="text"
							class="form-control"
							placeholder="<?php echo empty( $data->placeholder ) ? '' : esc_attr( $data->placeholder ); ?>"
							aria-label="<?php echo empty( $data->placeholder ) ? '' : esc_attr( $data->placeholder ); ?>"
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
					<?php esc_html_e( 'Add new profile' ); ?>
				</button>
			</div>

			<input
				type="hidden"
				name="<?php echo esc_attr( isset( $data->name ) ? $data->name : $data->key ); ?>"
				id="<?php echo esc_attr( $data->key ); ?>"
				value="<?php echo isset( $data->value ) ? esc_attr( $data->value ) : ''; ?>"
			>
		</div>
	</pno-social-profile-field>
	<!-- end inline vue template -->

</div>


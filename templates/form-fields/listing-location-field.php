<?php
/**
 * The template for displaying the listing location field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/listing-location-field.php
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

?>

<pno-listing-location-selector inline-template>

	<div id="pno-listing-location-selector">

		<div class="alert alert-danger" role="alert" v-if="error">
			{{errorMessage}}
			<button type="button" class="close" aria-label="Close" @click="clearError()">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>

		<div class="input-group mb-3">
			<input type="text" class="form-control" v-model="address" <?php echo $data->get_attributes(); //phpcs:ignore ?>>
			<div class="input-group-append">
				<span class="input-group-text">
					<div class="pno-loading" v-if="geolocationLoading === true"></div>
					<a href="#" @click.prevent.stop="geolocate( $event )" v-else>
						<i class="fas fa-compass"></i>
					</a>
				</span>
			</div>
		</div>

		<div class="form-row mb-3" v-show="customCoordinates">
			<div class="col">
				<input type="text" class="form-control" v-model="coordinates.lat" placeholder="<?php esc_html_e( 'Latitude' ); ?>">
			</div>
			<div class="col">
				<input type="text" class="form-control" v-model="coordinates.lng" placeholder="<?php esc_html_e( 'Longitude' ); ?>">
			</div>
		</div>

		<div class="form-row mb-3" v-if="allowCustomAddress">
			<div class="col">
				<input type="text" class="form-control" placeholder="<?php esc_html_e( 'Enter custom address' ); ?>">
			</div>
		</div>

		<nav class="nav nav-pills nav-justified">
			<a class="nav-item nav-link" href="#" @click.prevent.stop="togglePinLock()">
				<span v-if="pinLock === true">
					<i class="fas fa-lock mr-2"></i>
					<?php esc_html_e( 'Unlock pin location' ); ?>
				</span>
				<span v-else>
					<i class="fas fa-lock-open mr-2"></i>
					<?php esc_html_e( 'Lock pin location' ); ?>
				</span>
			</a>
			<a class="nav-item nav-link" href="#" @click.prevent.stop="toggleCustomCoordinates()">
				<span v-if="customCoordinates === true">
					<i class="fas fa-times-circle mr-2"></i>
					<?php esc_html_e( 'Hide coordinates' ); ?>
				</span>
				<span v-else>
					<i class="fas fa-map-pin mr-2"></i>
					<?php esc_html_e( 'Enter coordinates' ); ?>
				</span>
			</a>
			<a class="nav-item nav-link" href="#" @click.prevent.stop="toggleCustomAddress()">
				<span v-if="allowCustomAddress === true">
					<i class="fas fa-times-circle mr-2"></i>
					<?php esc_html_e( 'Hide custom address' ); ?>
				</span>
				<span v-else>
					<i class="fas fa-map-marked mr-2"></i>
					<?php esc_html_e( 'Set custom address' ); ?>
				</span>
			</a>
		</nav>

		<div class="pno-listing-submission-map mt-3"></div>

	</div>

</pno-listing-location-selector>

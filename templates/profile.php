<?php
/**
 * The template for displaying members profiles.
 *
 * This template can be overridden by copying it to yourtheme/pno/profile.php
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
defined( 'ABSPATH' ) || exit;

// Retrieve assigned user id.
$user_id = isset( $data->user_id ) ? absint( $data->user_id ) : pno_get_queried_user_id();

?>

<div class="container" id="pno-profile-wrapper">

	<div class="row">

		<div class="col-4">

		</div>

		<div class="col-8">

		</div>

	</div>

</div>

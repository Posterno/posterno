<?php
/**
 * The template for displaying the listing type field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/listing-type-field.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data->default = empty( $data->default ) ? current( array_keys( $data->options ) ) : $data->default;
$default       = ! empty( $data->value ) ? $data->value : $data->default;

?>

<?php if ( ! empty( $data->description ) ) : ?>
	<small class="form-text text-muted">
		<?php echo $data->description; ?>
	</small>
<?php endif; ?>

<div class="row">
	<?php foreach ( $data->options as $option_key => $value ) : ?>
		<div class="col-sm-12 col-md-4">
			<div class="card">
				<div class="card-body">
					<div class="custom-control custom-radio">
						<input type="radio" class="custom-control-input" id="<?php echo esc_attr( $option_key ); ?>" name="<?php echo esc_attr( isset( $data->name ) ? $data->name : $data->key ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $default, $option_key ); ?> />
						<label class="custom-control-label" for="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $value ); ?></label>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>



<?php
/**
 * The template for displaying the editor field.
 *
 * This template can be overridden by copying it to yourtheme/pno/form-fields/editor-field.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$editor = apply_filters(
	'pno_wp_editor_args', array(
		'textarea_name' => isset( $data->name ) ? $data->name : $data->key,
		'media_buttons' => false,
		'textarea_rows' => 8,
		'quicktags'     => false,
		'tinymce'       => array(
			'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
			'paste_as_text'                 => true,
			'paste_auto_cleanup_on_paste'   => true,
			'paste_remove_spans'            => true,
			'paste_remove_styles'           => true,
			'paste_remove_styles_if_webkit' => true,
			'paste_strip_class_attributes'  => true,
			'toolbar1'                      => 'bold,italic,|,bullist,numlist,|,link,unlink,|,undo,redo',
			'toolbar2'                      => '',
			'toolbar3'                      => '',
			'toolbar4'                      => '',
		),
	)
);
wp_editor( isset( $data->value ) ? wp_kses_post( $data->value ) : '', $data->key, $editor );
if ( ! empty( $data->description ) ) :
?><small class="form-text text-muted"><?php echo wp_kses_post( $data->description ); ?></small><?php endif; ?>

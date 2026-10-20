<?php
/**
 * Tweak and manage media
 *
 * This file includes any customizations to media and library management 
 *
 * @package      Core_Functionality
 * @since        1.0.0
 * @link         https://github.com/capwebsolutions/taps-core-functionality
 * @author       Matt Ryan <matt@capwebsolutions.com>
 * @copyright    Copyright (c) 2026, Matt Ryan
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Automatically set the image Title, Alt-Text, Caption & Description upon upload
--------------------------------------------------------------------------------------*/
add_action( 'add_attachment','set_image_meta_upon_image_upload' );
function set_image_meta_upon_image_upload( $post_ID ) {

	// Normalize the filename of uploaded media files for consistency.
	$filename = get_attached_file( $post_ID );

	if ( $filename && file_exists( $filename ) ) {
		$pathinfo  = pathinfo( $filename );
		$dir       = $pathinfo['dirname'];
		$base_name = $pathinfo['filename'];
		$ext       = ! empty( $pathinfo['extension'] ) ? '.' . strtolower( $pathinfo['extension'] ) : '';

		// Normalize filename:
		// - lowercase only
		// - spaces to single dash
		// - ()!#$ to dash
		// - collapse repeated dashes
		$new_base_name = strtolower( $base_name );
		$new_base_name = preg_replace( '/[()!#\$]+/', '-', $new_base_name );
		$new_base_name = preg_replace( '/\s+/', '-', $new_base_name );
		$new_base_name = preg_replace( '/[^a-z0-9-]+/', '-', $new_base_name );
		$new_base_name = preg_replace( '/-+/', '-', $new_base_name );
		$new_base_name = trim( $new_base_name, '-' );

		if ( '' === $new_base_name ) {
			$new_base_name = 'file';
		}

		$new_base_name = $new_base_name . '-' . $post_ID;
		$new_file_name = $new_base_name . $ext;
		$new_path      = trailingslashit( $dir ) . $new_file_name;

		if ( $new_path !== $filename && rename( $filename, $new_path ) ) {
			update_attached_file( $post_ID, $new_path );
		}
	}

	// Only set title and alt text automatically for image attachments.
	if ( wp_attachment_is_image( $post_ID ) ) {
 
		$my_image_title = get_post( $post_ID )->post_title;
 
		// Sanitize the title:  remove hyphens, underscores & extra spaces:
		$my_image_title = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $my_image_title );
 
		// Sanitize the title:  capitalize first letter of every word (other letters lower case):
		$my_image_title = ucwords( strtolower( $my_image_title ) );
 
		// Create an array with the image meta (Title, Caption, Description) to be updated
		// Note:  comment out the Excerpt/Caption or Content/Description lines if not needed
		$my_image_meta = array(
			'ID'		=> $post_ID,			// Specify the image (ID) to be updated
			'post_title'	=> $my_image_title,		// Set image Title to sanitized title
			'post_excerpt'	=> $my_image_title,		// Set image Caption to sanitized title
		);
 
		// Set the image Alt-Text
		update_post_meta( $post_ID, '_wp_attachment_image_alt', $my_image_title );
 
		// Set the image meta (e.g. Title, Excerpt, Content)
		wp_update_post( $my_image_meta );
 
	} 
}

// Set default featured image if none is set
function set_default_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( empty( $post_thumbnail_id ) && empty( $html ) && 'car' === get_post_type( $post_id ) ) {
		$upload_dir = wp_get_upload_dir();
		$default_image_url = $upload_dir['baseurl'] . '/uploads/no-image-currently-in-the-shop-1209.jpg';
		$html = '<img src="' . esc_url( $default_image_url ) . '" class="wp-post-image" alt="Default Image"/>';
	}
	return $html;
}
add_filter( 'post_thumbnail_html','set_default_featured_image', 10, 5 );
 
function set_default_featured_image_url( $url, $post_id ) {
    if ( empty( get_post_thumbnail_id( $post_id ) ) ) {
		$upload_dir = wp_get_upload_dir();
		$url = $upload_dir['baseurl'] . '/uploads/no-image-currently-in-the-shop-1209.jpg';
    }
    return $url;
}
add_filter( 'default_post_thumbnail_url','set_default_featured_image_url', 10, 2 );

/**
 * Create Custom Image Size
 *
 * @return void
 */
function create_custom_image_sizes() {
	// Add custom image size for front page gallery
	add_image_size( 'front-car-gallery-size', 266, 177, true );
	// Image size for car gallery on 'The Cars' interior page
	add_image_size( 'interior-car-gallery-size', 133, 101, true );
	// Image size for car gallery on each car's post detail page
	add_image_size( 'individual-car-detail-size', 300, 200, true );
}

add_action( 'after_setup_theme', 'create_custom_image_sizes' );

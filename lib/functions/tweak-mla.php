<?php
/**
 * Tweak and manage customizations specific to Media Library Assistant plugin
 * 
 * Requires Media Library Assistant plugin to be installed and activated.
 *
 * @package      Core_Functionality
 * @since        1.0.0
 * @link         https://github.com/capwebsolutions/taps-core-functionality
 * @author       Matt Ryan <matt@capwebsolutions.com>
 * @copyright    Copyright (c) 2026, Matt Ryan
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Check if Media Library Assistant is active and exit early if not
if ( ! class_exists( 'MLA' ) ) {
	return;
}

add_filter( 'mb_settings_pages', 'tuckerclub_mla_settings_pages' );
add_filter( 'rwmb_meta_boxes', 'tuckerclub_mla_settings_fields' );

function tuckerclub_mla_settings_pages( $settings_pages ) {
	$settings_pages[] = [
		'menu_title' => __( 'MLA Upload Tags', 'taps-core-functionality' ),
		'page_title' => __( 'MLA Upload Tags', 'taps-core-functionality' ),
		'id'         => 'tuckerclub-mla-settings',
		'parent'     => 'upload.php',
		'capability' => 'manage_options',
		'style'      => 'no-boxes',
		'columns'    => 1,
	];

	return $settings_pages;
}

function tuckerclub_mla_settings_fields( $meta_boxes ) {
	$meta_boxes[] = [
		'title'          => __( 'Media upload tag options', 'taps-core-functionality' ),
		'id'             => 'tuckerclub-mla-upload-tags',
		'settings_pages' => [ 'tuckerclub-mla-settings' ],
		'fields'         => [
			[
				'name' => __( 'Add "primary" tag to all newly uploaded media image files.', 'taps-core-functionality' ),
				'id'   => 'tuckerclub_mla_primary_tag',
				'type' => 'switch',
				'desc' => __( 'Assign the primary tag to images uploaded after this option is enabled.', 'taps-core-functionality' ),
			],
			[
				'name' => __( 'Add "archives" tag to all newly uploaded media image files.', 'taps-core-functionality' ),
				'id'   => 'tuckerclub_mla_archive_tag',
				'type' => 'switch',
				'desc' => __( 'Assign the archives tag to images uploaded after this option is enabled.', 'taps-core-functionality' ),
			],
			[
				'name' => __( 'Add car number "nnnn" to newly uploaded media image files.', 'taps-core-functionality' ),
				'id'   => 'tuckerclub_mla_car_number_tag',
				'type' => 'switch',
				'desc' => __( 'Enable to add car number to images uploaded.', 'taps-core-functionality' ),
			],
			[
				'name' => __( 'Car number "nnnn".', 'taps-core-functionality' ),
				'id'   => 'tuckerclub_mla_car_number_value',
                'std' => '0000',
                'placeholder' => '0000',
				'desc' => __( 'Specify the car number as 4 digits. It will be applied to all images uploaded while this setting is active.', 'taps-core-functionality' ),
			],            
		],
	];

	return $meta_boxes;
}

/**
 * Update media library item to extract car number from file and add it as taxonomy. 
 */

$switch_value = function_exists( 'rwmb_meta' )
    ? rwmb_meta( 'tuckerclub_assign_category_from_filename', [ 'object_type' => 'setting' ], 'tuckerclub-mla-settings' )
    : false;
if ( $switch_value ) {
    add_filter( 'mla_update_attachment_metadata_postfilter', 'tuckerclub_assign_category_from_filename', 10, 2 );
} else {
    remove_filter( 'mla_update_attachment_metadata_postfilter', 'tuckerclub_assign_category_from_filename', 10, 2 );
}

$switch_value = function_exists( 'rwmb_meta' )
    ? rwmb_meta( 'tuckerclub_assign_primary_tag_from_filename', [ 'object_type' => 'setting' ], 'tuckerclub-mla-settings' )
    : false;
if ( $switch_value ) {
    add_filter( 'mla_update_attachment_metadata_postfilter', 'tuckerclub_assign_primary_tag_from_filename', 10, 2 );
} else {
    remove_filter( 'mla_update_attachment_metadata_postfilter', 'tuckerclub_assign_primary_tag_from_filename', 10, 2 );
}
// add_filter( 'mla_update_attachment_metadata_postfilter', 'tuckerclub_assign_primary_tag_from_filename', 10, 2 );

$switch_value = function_exists( 'rwmb_meta' )
    ? rwmb_meta( 'tuckerclub_mla_car_number_tag', [ 'object_type' => 'setting' ], 'tuckerclub-mla-settings' )
    : false;
if ( $switch_value ) {
    add_filter( 'mla_update_attachment_metadata_postfilter', 'tuckerclub_assign_car_number_category', 10, 2 );
} else {
    remove_filter( 'mla_update_attachment_metadata_postfilter', 'tuckerclub_assign_car_number_category', 10, 2 );
}

function tuckerclub_assign_category_from_filename( $metadata, $post_id ) {

    $file = get_attached_file( $post_id );
    $filename = basename( $file );

    // Find first 4-digit sequence between 1000 and 1052 inclusive
    if ( preg_match( '/(\d{4})/', $filename, $matches ) && 
         intval( $matches[1] ) >= 1000 && 
         intval( $matches[1] ) <= 1052 ) {
        $code = $matches[1];

        // Taxonomy slug for Att. Category
        $taxonomy = 'attachment_category';

        // Create term if it doesn't exist
        $term = term_exists( $code, $taxonomy );
        if ( ! $term ) {
            $term = wp_insert_term( $code, $taxonomy );
        }

        // Assign term to attachment
        if ( ! is_wp_error( $term ) ) {
            wp_set_object_terms( $post_id, intval( $term['term_id'] ), $taxonomy, false );
        }
    }

    return $metadata;
}

function tuckerclub_assign_primary_tag_from_filename( $metadata, $post_id ) {

    if ( ! wp_attachment_is_image( $post_id ) ) {
        return $metadata;
    }

    $file = get_attached_file( $post_id );
    $filename = basename( $file );

    // Find first 4-digit sequence between 1000 and 1052 inclusive
    if ( ! preg_match( '/(\d{4})/', $filename, $matches ) ||
         intval( $matches[1] ) < 1000 ||
         intval( $matches[1] ) > 1052 ) {
        return $metadata;
    }

    $prim_tag = 'primary';
    $archive_tag = 'archive';
    $taxonomy = 'attachment_tag';

    $add_primary_tag = function_exists( 'rwmb_meta' )
        ? rwmb_meta( 'tuckerclub_mla_primary_tag', [ 'object_type' => 'setting' ], 'tuckerclub-mla-settings' )
        : false;
    $add_archive_tag = function_exists( 'rwmb_meta' )
        ? rwmb_meta( 'tuckerclub_mla_archive_tag', [ 'object_type' => 'setting' ], 'tuckerclub-mla-settings' )
        : false;

    $terms = [];
    if ( $add_primary_tag ) {
        $terms[] = $prim_tag;
    }
    if ( $add_archive_tag ) {
        $terms[] = $archive_tag;
    }

    if ( empty( $terms ) ) {
        return $metadata;
    }

    foreach ( $terms as $term_name ) {
        $term = term_exists( $term_name, $taxonomy );
        if ( ! $term ) {
            $term = wp_insert_term( $term_name, $taxonomy );
        }

        if ( ! is_wp_error( $term ) ) {
            wp_set_object_terms( $post_id, intval( $term['term_id'] ), $taxonomy, true );
        }
    }

    return $metadata;
}

/**
 * Undocumented function
 *
 * @param [type] $metadata
 * @param [type] $post_id
 * @return void
 */
function tuckerclub_assign_car_number_category( $metadata, $post_id ) {
   
    // Before doing anything, check if the files name contains a car number. 
    // If it does, return without doing anything.
    // The Att. category has already been set on this image file. 
    $file = get_attached_file( $post_id );
    $filename = basename( $file );
    if ( preg_match( '/(\d{4})/', $filename, $matches ) &&
         intval( $matches[1] ) >= 1000 &&
         intval( $matches[1] ) <= 1052 ) {
        return $metadata;
    }

    $add_car_number_category = function_exists( 'rwmb_meta' )
        ? rwmb_meta( 'tuckerclub_mla_car_number_value', [ 'object_type' => 'setting' ], 'tuckerclub-mla-settings' )
        : false;

    // Taxonomy slug for Att. Category
    $taxonomy = 'attachment_category';

    // Create term if it doesn't exist
    $term = term_exists( $add_car_number_category, $taxonomy );
    if ( ! $term ) {
        $term = wp_insert_term( $add_car_number_category, $taxonomy );
    }

    // Assign term to attachment
    if ( ! is_wp_error( $term ) ) {
        wp_set_object_terms( $post_id, intval( $term['term_id'] ), $taxonomy, false );
    }
}


// Example concept based on MLA documentation
add_filter('mla_gallery_link_url', 'tucker_mla_cpt_link', 10, 3);
function tucker_mla_cpt_link($link_url, $attachment_id, $gallery_id) {
    // Example: Check if the image has a specific taxonomy term
    $terms = get_the_terms($attachment_id, 'attachment_tag');
    
    if ($terms && !is_wp_error($terms)) {
        // Logic to find the related CPT post
		// Get the post ID of the related Car CPT that is named after the attachment's taxonomy term
		$cpt_post = get_page_by_title($terms[0]->name, OBJECT, 'car');
		$cpt_post_id = $cpt_post ? $cpt_post->ID : 0;
        // Return the CPT permalink instead of the default media file link
        return get_permalink($cpt_post_id); 
    }
    
    return $link_url;
}
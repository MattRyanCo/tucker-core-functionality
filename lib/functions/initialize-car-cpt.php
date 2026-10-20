<?php 
/**
 * Initialize the Car custom post type with reusable pattern
 *
 * @package   Core_Functionality
 * @since     1.0.0
 * @link      https://github.com/capwebsolutions/taps-core-functionality
 * @author    Matt Ryan <matt@capwebsolutions.com>
 * @copyright Copyright (c) 2026, Matt Ryan
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


/**
 * Get the ID of a reusable block pattern by its title.
 *
 * @link https://studiok40.com/smarter-block-templates-for-custom-post-types/
 * 
 * @param string $title The title of the reusable block pattern.
 * @return int|false The ID of the reusable block pattern, or false if not found.
 */

add_action('init', 'tuckerclub_car_post');
function tuckerclub_car_post()
{
    // Grab pattern ID for the reusable block pattern titled "Car Details".
    //  This to be used as default content for block. 
    $pattern_id = tuckerclub_get_pattern_id_by_title( 'Car Details' );
    $template = $pattern_id ? [
        [ 'core/block', [ 'ref' => $pattern_id ] ]
    ] : [];


    $labels = [
    'name'                     => esc_html__('Cars', 'tuckerclub'),
    'singular_name'            => esc_html__('Car', 'tuckerclub'),
    'add_new'                  => esc_html__('Add New', 'tuckerclub'),
    'add_new_item'             => esc_html__('Add New Car', 'tuckerclub'),
    'edit_item'                => esc_html__('Edit Car', 'tuckerclub'),
    'new_item'                 => esc_html__('New Car', 'tuckerclub'),
    'view_item'                => esc_html__('View Car', 'tuckerclub'),
    'view_items'               => esc_html__('View Cars', 'tuckerclub'),
    'search_items'             => esc_html__('Search Cars', 'tuckerclub'),
    'not_found'                => esc_html__('No cars found.', 'tuckerclub'),
    'not_found_in_trash'       => esc_html__('No cars found in Trash.', 'tuckerclub'),
    'parent_item_colon'        => esc_html__('Parent Car:', 'tuckerclub'),
    'all_items'                => esc_html__('All Cars', 'tuckerclub'),
    'archives'                 => esc_html__('Car Archives', 'tuckerclub'),
    'attributes'               => esc_html__('Car Attributes', 'tuckerclub'),
    'insert_into_item'         => esc_html__('Insert into car', 'tuckerclub'),
    'uploaded_to_this_item'    => esc_html__('Uploaded to this car', 'tuckerclub'),
    'featured_image'           => esc_html__('Featured image', 'tuckerclub'),
    'set_featured_image'       => esc_html__('Set featured image', 'tuckerclub'),
    'remove_featured_image'    => esc_html__('Remove featured image', 'tuckerclub'),
    'use_featured_image'       => esc_html__('Use as featured image', 'tuckerclub'),
    'menu_name'                => esc_html__('Cars', 'tuckerclub'),
    'filter_items_list'        => esc_html__('Filter cars list', 'tuckerclub'),
    'filter_by_date'           => esc_html__('', 'tuckerclub'),
    'items_list_navigation'    => esc_html__('Cars list navigation', 'tuckerclub'),
    'items_list'               => esc_html__('Cars list', 'tuckerclub'),
    'item_published'           => esc_html__('Car published.', 'tuckerclub'),
    'item_published_privately' => esc_html__('Car published privately.', 'tuckerclub'),
    'item_reverted_to_draft'   => esc_html__('Car reverted to draft.', 'tuckerclub'),
    'item_scheduled'           => esc_html__('Car scheduled.', 'tuckerclub'),
    'item_updated'             => esc_html__('Car updated.', 'tuckerclub'),
    ];
    $args = [
    'label'               => esc_html__('Cars', 'tuckerclub'),
    'labels'              => $labels,
    'description'         => '',
    'public'              => true,
    'hierarchical'        => false,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'show_ui'             => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'show_in_rest'        => true,
    'query_var'           => true,
    'can_export'          => true,
    'delete_with_user'    => false,
    'has_archive'         => true,
    'rest_base'           => '',
    'show_in_menu'        => true,
    'menu_position'       => 20,
    'menu_icon'           => 'dashicons-car',
    'capability_type'     => 'post',
    'supports'            => [
        'title', 
        'editor', 
        'thumbnail', 
        'excerpt', 
        'revisions', 
        'custom-fields'
    ],
    'taxonomies'          => [
        'attachment_category',
        'attachment_tag',
    ],
    'rewrite'             => [
        'with_front' => false,
    ],
    'template' => $template
    ];

    register_post_type('car', $args);
}


/**
 * Get the ID of a reusable block pattern by its title.
 *
 * @link https://studiok40.com/smarter-block-templates-for-custom-post-types/
 * 
 * @param string $title The title of the reusable block pattern.
 * @return int|false The ID of the reusable block pattern, or false if not found.
 */
function tuckerclub_get_pattern_id_by_title( $title ) {
    $patterns = get_posts( array(
        'post_type'   => 'wp_block',
        'post_status' => 'publish',
        'title'       => $title,
        'numberposts' => 1,
    ) );

    return ! empty( $patterns ) ? $patterns[0]->ID : false;
}

/**
 * Convert reusable block to regular block when creating a new car post
 *
 * @param array $data The post data.
 * @param array $postarr The original post data.
 * @return array Modified post data.
 */
function tuckerclub_convert_reusable_block_to_regular_block( $data, $postarr ) {

    if ( $data['post_type'] === 'car' && $data['post_status'] === 'auto-draft' ) {

        $reusable = get_page_by_title( 'Car Details', OBJECT, 'wp_block' );

        if ( $reusable ) {
            $data['post_content'] = $reusable->post_content;
            $data['post_status']  = 'draft';
            $data['post_name']    = 'Tucker 0000'; // Set a default slug for the new car post
            $data['post_title']   = 'Tucker 0000'; // Set a default title for the new car post
        }
    }

    return $data;
}
add_filter( 'wp_insert_post_data', 'tuckerclub_convert_reusable_block_to_regular_block', 10, 2 );

/**
 * Add custom meta boxes for the Car post type
 *
 * @param array $meta_boxes The existing meta boxes.
 * @return array Modified meta boxes.
 */
// add_filter( 'rwmb_meta_boxes', 'tuckerclub_custom_fields' );

// function tuckerclub_custom_fields( $meta_boxes ) {
//     $prefix = 'tucker_';

//     $meta_boxes[] = [
//         'post_types' => ['car'],
//         'context'    => 'side',
//         'revision'   => true,
//         'title'      => __( 'Tucker Fields', 'tuckerclub' ),
//         'id'         => 'tucker-fields',
//         'fields'     => [
//             [
//                 'name'          => __( 'Car Number (metabox)', 'tuckerclub' ),
//                 'id'            => $prefix . 'car-number',
//                 'type'          => 'text',
//                 'admin_columns' => [
//                     'position' => 'after title',
//                     'sort'     => true,
//                 ],
//                 'limit_type'    => 'character',
//             ],
//         ],
//     ];

//     return $meta_boxes;
// }
<?php
// Legal services custom post type

function create_services_cpt()
{
    $labels = array(
        'name'                  => 'Services',
        'singular_name'         => 'Service',
        'menu_name'             => 'Services',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Service',
        'edit_item'             => 'Edit Service',
        'view_item'             => 'View Service',
        'search_items'          => 'Search Services'
    );

    $args = array(
        'label'                 => 'Service',
        'description'           => 'Legal services',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-clipboard',
        'show_in_rest'          => true
    );

    register_post_type('services', $args);
}
add_action('init', 'create_services_cpt', 0);

// Add meta box for service type
function service_type_meta_box()
{
    add_meta_box(
        'service_type_box',
        'Service Type',
        'service_type_callback',
        'services',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'service_type_meta_box');

function service_type_callback($post)
{
    wp_nonce_field('service_type_save', 'service_type_nonce');

    $services = array(
        'Copyright infringement',
        'Patent applications',
        'Licensing agreements',
        'IP due diligence',
        'Trademark protection'
    );

    echo '<p>Select service type:</p>';
    echo '<select name="service_type" style="width: 100%;">';

    foreach ($services as $service) {
        echo '<option value="' . esc_attr($service) . '">' . esc_html($service) . '</option>';
    }

    echo '</select>';
    echo '<p>Featured image is required.</p>';
}

// Save the service type
function save_service_type($post_id)
{
    if (!isset($_POST['service_type_nonce']) || !wp_verify_nonce($_POST['service_type_nonce'], 'service_type_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['service_type'])) {
        update_post_meta($post_id, '_service_type', sanitize_text_field($_POST['service_type']));
    }
}

add_action('save_post_services', 'save_service_type');

// Register service type for REST API
function register_service_type_meta()
{
    register_post_meta('services', '_service_type', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
}
add_action('init', 'register_service_type_meta');

// Add service type to REST API response
function add_service_type_to_rest()
{
    register_rest_field('services', 'service_type', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_service_type', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));
}
add_action('rest_api_init', 'add_service_type_to_rest');

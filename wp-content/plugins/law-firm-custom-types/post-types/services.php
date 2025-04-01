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

function service_meta_box()
{
    add_meta_box(
        'service_properties_box',
        'Service Properties',
        'service_properties_callback',
        'services',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'service_meta_box');

function service_properties_callback($post)
{
    wp_nonce_field('service_properties_save', 'service_properties_nonce');

    $service_type = get_post_meta($post->ID, '_service_type', true);
    $tagline = get_post_meta($post->ID, '_service_tagline', true);
    $slug = get_post_meta($post->ID, '_service_slug', true);
    $metadesc = get_post_meta($post->ID, '_service_metadesc', true);

    // Service Type
    echo '<p><label for="service_type"><strong>Service Type:</strong></label></p>';
    echo '<input type="text" id="service_type" name="service_type" value="' . esc_attr($service_type) . '" style="width: 100%;" />';

    // Tagline
    echo '<p><label for="service_tagline"><strong>Tagline:</strong></label></p>';
    echo '<input type="text" id="service_tagline" name="service_tagline" value="' . esc_attr($tagline) . '" style="width: 100%;" />';

    // Custom Slug
    echo '<p><label for="service_slug"><strong>Custom Slug:</strong></label></p>';
    echo '<input type="text" id="service_slug" name="service_slug" value="' . esc_attr($slug) . '" style="width: 100%;" />';

    // Meta Description
    echo '<p><label for="service_metadesc"><strong>Meta Description:</strong></label></p>';
    echo '<textarea id="service_metadesc" name="service_metadesc" style="width: 100%; height: 80px;">' . esc_textarea($metadesc) . '</textarea>';
}

function save_service_properties($post_id)
{
    if (!isset($_POST['service_properties_nonce']) || !wp_verify_nonce($_POST['service_properties_nonce'], 'service_properties_save')) {
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

    if (isset($_POST['service_tagline'])) {
        update_post_meta($post_id, '_service_tagline', sanitize_text_field($_POST['service_tagline']));
    }

    if (isset($_POST['service_slug'])) {
        $slug = sanitize_title($_POST['service_slug']);
        update_post_meta($post_id, '_service_slug', $slug);

        // Update post slug if custom slug is provided
        if (!empty($slug)) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => $slug
            ));
        }
    }

    if (isset($_POST['service_metadesc'])) {
        error_log('Saving metadesc: ' . $_POST['service_metadesc']);

        delete_post_meta($post_id, '_service_metadesc');
        add_post_meta($post_id, '_service_metadesc', wp_kses_post($_POST['service_metadesc']), true);
    }
}

add_action('save_post_services', 'save_service_properties');

function register_service_properties_meta()
{
    register_post_meta('services', '_service_type', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));

    register_post_meta('services', '_service_tagline', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));

    register_post_meta('services', '_service_slug', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));

    register_post_meta('services', '_service_metadesc', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'string',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
}
add_action('init', 'register_service_properties_meta');

function add_service_properties_to_rest()
{
    register_rest_field('services', 'service_type', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_service_type', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));

    register_rest_field('services', 'tagline', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_service_tagline', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));

    register_rest_field('services', 'custom_slug', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_service_slug', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));

    register_rest_field('services', 'meta_description', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_service_metadesc', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));
}
add_action('rest_api_init', 'add_service_properties_to_rest');

function services_custom_columns($columns)
{
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;

        if ($key === 'title') {
            $new_columns['service_type'] = 'Service Type';
        }
    }

    return $new_columns;
}
add_filter('manage_services_posts_columns', 'services_custom_columns');

function services_custom_column_content($column, $post_id)
{
    if ($column === 'service_type') {
        $service_type = get_post_meta($post_id, '_service_type', true);
        echo esc_html($service_type);
    }
}
add_action('manage_services_posts_custom_column', 'services_custom_column_content', 10, 2);

function services_sortable_columns($columns)
{
    $columns['service_type'] = 'service_type';
    return $columns;
}
add_filter('manage_edit-services_sortable_columns', 'services_sortable_columns');

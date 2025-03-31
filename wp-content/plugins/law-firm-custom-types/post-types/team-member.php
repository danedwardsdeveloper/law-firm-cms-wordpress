<?php

function create_team_member_cpt()
{
    $labels = array(
        'name'                  => 'Team Members',
        'singular_name'         => 'Team Member',
        'menu_name'             => 'Team',
        'name_admin_bar'        => 'Team Member',
        'archives'              => 'Team Member Archives',
        'attributes'            => 'Team Member Attributes',
        'parent_item_colon'     => 'Parent Team Member:',
        'all_items'             => 'All Team Members',
        'add_new_item'          => 'Add New Team Member',
        'add_new'               => 'Add New',
        'new_item'              => 'New Team Member',
        'edit_item'             => 'Edit Team Member',
        'update_item'           => 'Update Team Member',
        'view_item'             => 'View Team Member',
        'view_items'            => 'View Team Members',
        'search_items'          => 'Search Team Member',
        'not_found'             => 'Not found',
        'not_found_in_trash'    => 'Not found in Trash',
        'featured_image'        => 'Profile Photo',
        'set_featured_image'    => 'Set profile photo',
        'remove_featured_image' => 'Remove profile photo',
        'use_featured_image'    => 'Use as profile photo',
    );

    $args = array(
        'label'                 => 'Team Member',
        'description'           => 'Team Members',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-groups',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );

    register_post_type('team_member', $args);
}
add_action('init', 'create_team_member_cpt', 0);

function team_member_meta_boxes()
{
    add_meta_box(
        'team_member_details',
        'Team Member Details',
        'team_member_details_callback',
        'team_member',
        'normal',
        'high'
    );

    add_meta_box(
        'team_member_seo',
        'SEO Details',
        'team_member_seo_callback',
        'team_member',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'team_member_meta_boxes');

function team_member_details_callback($post)
{
    wp_nonce_field('team_member_save_meta', 'team_member_meta_nonce');

    $role = get_post_meta($post->ID, '_team_member_role', true);

?>
    <p>
        <label for="team_member_role">Role/Position:</label>
        <input type="text" id="team_member_role" name="team_member_role" value="<?php echo esc_attr($role); ?>" style="width: 100%;">
    </p>
    <p>
        <strong>Display Description:</strong>
        <br>
        <em>Use the main content editor above for the team member's display description.</em>
    </p>
    <p>
        <strong>Profile Photo:</strong>
        <br>
        <em>Use the Featured Image panel to set the team member's photo.</em>
    </p>
<?php
}

function team_member_seo_callback($post)
{
    $meta_title = get_post_meta($post->ID, '_team_member_meta_title', true);
    $meta_desc = get_post_meta($post->ID, '_team_member_meta_desc', true);

?>
    <p>
        <label for="team_member_meta_title">Meta Title:</label>
        <input type="text" id="team_member_meta_title" name="team_member_meta_title" value="<?php echo esc_attr($meta_title); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="team_member_meta_desc">Meta Description:</label>
        <textarea id="team_member_meta_desc" name="team_member_meta_desc" style="width: 100%;" rows="4"><?php echo esc_textarea($meta_desc); ?></textarea>
    </p>
<?php
}

function save_team_member_meta($post_id)
{
    if (!isset($_POST['team_member_meta_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['team_member_meta_nonce'], 'team_member_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if ('team_member' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    if (isset($_POST['team_member_role'])) {
        update_post_meta($post_id, '_team_member_role', sanitize_text_field($_POST['team_member_role']));
    }

    if (isset($_POST['team_member_meta_title'])) {
        update_post_meta($post_id, '_team_member_meta_title', sanitize_text_field($_POST['team_member_meta_title']));
    }

    if (isset($_POST['team_member_meta_desc'])) {
        update_post_meta($post_id, '_team_member_meta_desc', sanitize_textarea_field($_POST['team_member_meta_desc']));
    }
}
add_action('save_post', 'save_team_member_meta');

function team_member_register_rest_fields()
{
    register_rest_field('team_member', 'role', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_team_member_role', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));

    register_rest_field('team_member', 'meta_title', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_team_member_meta_title', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));

    register_rest_field('team_member', 'meta_desc', array(
        'get_callback' => function ($post) {
            return get_post_meta($post['id'], '_team_member_meta_desc', true);
        },
        'update_callback' => null,
        'schema' => null,
    ));

    register_rest_field('team_member', 'featured_image_url', array(
        'get_callback' => function ($post) {
            if (has_post_thumbnail($post['id'])) {
                $img_id = get_post_thumbnail_id($post['id']);
                $img_url = wp_get_attachment_image_src($img_id, 'full');
                return $img_url[0];
            }
            return null;
        },
        'update_callback' => null,
        'schema' => null,
    ));
}
add_action('rest_api_init', 'team_member_register_rest_fields');

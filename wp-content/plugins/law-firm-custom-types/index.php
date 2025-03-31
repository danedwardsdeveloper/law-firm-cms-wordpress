<?php

/**
 * Plugin Name: Law firm
 * Description: Adds custom post types
 */

define('LF_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once LF_PLUGIN_DIR . 'post-types/team-member.php';
require_once LF_PLUGIN_DIR . 'post-types/services.php';

register_activation_hook(__FILE__, 'lf_plugin_activate');

function lf_plugin_activate()
{
    flush_rewrite_rules();
}

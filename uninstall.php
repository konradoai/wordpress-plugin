<?php
/**
 * Removes the plugin's settings when it is deleted from the Plugins screen.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('konrado_ai_widget_id');
delete_option('konrado_ai_identify_users');
delete_option('konrado_ai_signing_secret');

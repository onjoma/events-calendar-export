<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Currently no cleanup needed as we don't store any data in the database
// If you add any database tables in the future, clean them up here

<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/includes
 */
class Lindawp_Events_Export_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Currently not doing anything on deactivation
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // No cleanup needed at this time
    }
}

<?php
/**
 * Fired during plugin activation
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/includes
 */
class Lindawp_Events_Export_Activator {

    /**
     * Activate the plugin.
     *
     * Check if The Events Calendar plugin is active
     *
     * @since    1.0.0
     */
    public static function activate() {
        if (!class_exists('Tribe__Events__Main')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('This plugin requires The Events Calendar plugin to be installed and activated.', 'lindawp-events-export'),
                'Plugin dependency check',
                array('back_link' => true)
            );
        }
    }
}

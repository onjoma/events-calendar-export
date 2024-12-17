<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://onjomapeter.com
 * @since             1.0.0
 * @package           Lindawp_Events_Export
 *
 * @wordpress-plugin
 * Plugin Name:       LindaWP Events Export
 * Plugin URI:        https://onjomapeter.com
 * Description:       Export The Events Calendar events to PDF.
 * Version:           1.0.0
 * Author:            Peter
 * Author URI:        https://onjomapeter.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lindawp-events-export
 * Domain Path:       /languages
 * Requires at least: 5.2
 * Requires PHP:      7.2
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('LINDAWP_EVENTS_EXPORT_VERSION', '1.0.0');

/**
 * Required minimum version of The Events Calendar.
 */
define('LINDAWP_EVENTS_EXPORT_TEC_MIN_VERSION', '6.0.0');

/**
 * Required minimum version of mPDF.
 */
define('LINDAWP_EVENTS_EXPORT_MPDF_MIN_VERSION', '8.0.0');

/**
 * Check plugin dependencies and versions
 *
 * @return bool True if all dependencies are met
 */
function lindawp_events_export_check_dependencies() {
    $deps_met = true;

    // Check for The Events Calendar
    if (!class_exists('Tribe__Events__Main')) {
        add_action('admin_notices', 'lindawp_events_export_dependency_notice');
        $deps_met = false;
    } else {
        // Check The Events Calendar version
        $tec = Tribe__Events__Main::VERSION;
        if (version_compare($tec, LINDAWP_EVENTS_EXPORT_TEC_MIN_VERSION, '<')) {
            add_action('admin_notices', 'lindawp_events_export_tec_version_notice');
            $deps_met = false;
        }
    }

    // Check for Composer autoloader
    if (!file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
        add_action('admin_notices', 'lindawp_events_export_mpdf_notice');
        $deps_met = false;
    } else {
        require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
        // Check mPDF version if autoloader exists
        if (class_exists('\\Mpdf\\Mpdf')) {
            $reflection = new ReflectionClass('\\Mpdf\\Mpdf');
            if (defined('\\Mpdf\\Mpdf::VERSION')) {
                $mpdf_version = \Mpdf\Mpdf::VERSION;
                if (version_compare($mpdf_version, LINDAWP_EVENTS_EXPORT_MPDF_MIN_VERSION, '<')) {
                    add_action('admin_notices', 'lindawp_events_export_mpdf_version_notice');
                    $deps_met = false;
                }
            }
        }
    }

    // Check PHP version
    if (version_compare(PHP_VERSION, '7.2', '<')) {
        add_action('admin_notices', 'lindawp_events_export_php_version_notice');
        $deps_met = false;
    }

    return $deps_met;
}

/**
 * Display dependency notice for The Events Calendar
 */
function lindawp_events_export_dependency_notice() {
    $class = 'notice notice-error';
    $message = sprintf(
        /* translators: %s: Link to The Events Calendar plugin */
        __('LindaWP Events Export requires The Events Calendar plugin to be installed and activated. Please %s to continue.', 'lindawp-events-export'),
        '<a href="' . esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true&width=600&height=550')) . '" class="thickbox">' . __('install and activate The Events Calendar', 'lindawp-events-export') . '</a>'
    );
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
}

/**
 * Display version notice for The Events Calendar
 */
function lindawp_events_export_tec_version_notice() {
    $class = 'notice notice-error';
    $message = sprintf(
        /* translators: %s: Minimum required version */
        __('LindaWP Events Export requires The Events Calendar version %s or higher. Please update The Events Calendar to continue.', 'lindawp-events-export'),
        LINDAWP_EVENTS_EXPORT_TEC_MIN_VERSION
    );
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

/**
 * Display dependency notice for mPDF
 */
function lindawp_events_export_mpdf_notice() {
    $class = 'notice notice-error';
    $message = __('LindaWP Events Export requires mPDF library. Please run composer install in the plugin directory.', 'lindawp-events-export');
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

/**
 * Display version notice for mPDF
 */
function lindawp_events_export_mpdf_version_notice() {
    $class = 'notice notice-error';
    $message = sprintf(
        /* translators: %s: Minimum required version */
        __('LindaWP Events Export requires mPDF version %s or higher. Please update the mPDF library.', 'lindawp-events-export'),
        LINDAWP_EVENTS_EXPORT_MPDF_MIN_VERSION
    );
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

/**
 * Display PHP version notice
 */
function lindawp_events_export_php_version_notice() {
    $class = 'notice notice-error';
    $message = sprintf(
        /* translators: %s: Minimum required PHP version */
        __('LindaWP Events Export requires PHP version %s or higher. Please update PHP to continue.', 'lindawp-events-export'),
        '7.2'
    );
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

/**
 * The code that runs during plugin activation.
 */
function activate_lindawp_events_export() {
    // Check dependencies before activation
    if (!lindawp_events_export_check_dependencies()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('This plugin cannot be activated because it requires all dependencies to be installed and up to date.', 'lindawp-events-export'),
            esc_html__('Plugin Activation Error', 'lindawp-events-export'),
            array('back_link' => true)
        );
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-lindawp-events-export-activator.php';
    Lindawp_Events_Export_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_lindawp_events_export() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-lindawp-events-export-deactivator.php';
    Lindawp_Events_Export_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_lindawp_events_export');
register_deactivation_hook(__FILE__, 'deactivate_lindawp_events_export');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-lindawp-events-export.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_lindawp_events_export() {
    // Check dependencies before running the plugin
    if (lindawp_events_export_check_dependencies()) {
        $plugin = new Lindawp_Events_Export();
        $plugin->run();
    }
}
add_action('plugins_loaded', 'run_lindawp_events_export');

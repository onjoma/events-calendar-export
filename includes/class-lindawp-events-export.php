<?php
/**
 * The core plugin class.
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/includes
 */

/**
 * The core plugin class.
 */
class Lindawp_Events_Export {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Lindawp_Events_Export_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('LINDAWP_EVENTS_EXPORT_VERSION')) {
            $this->version = LINDAWP_EVENTS_EXPORT_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'lindawp-events-export';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lindawp-events-export-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lindawp-events-export-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lindawp-events-export-pdf.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-lindawp-events-export-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-lindawp-events-export-public.php';

        $this->loader = new Lindawp_Events_Export_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Lindawp_Events_Export_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Lindawp_Events_Export_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_post_export_events', $plugin_admin, 'handle_export_events');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Lindawp_Events_Export_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp', $plugin_public, 'handle_frontend_export_events');
        $this->loader->add_shortcode('lindawp_events_export_form', $plugin_public, 'display_export_form');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}

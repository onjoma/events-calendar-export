<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/admin
 */

/**
 * The admin-specific functionality of the plugin.
 */
class Lindawp_Events_Export_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string $plugin_name       The name of this plugin.
     * @param    string $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version    = $version;

        // Register the export events action
        add_action( 'admin_post_export_events', array( $this, 'handle_export_events' ) );
        
        // Add scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @since    1.0.0
     * @param    string $hook_suffix The current admin page.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        // Only load on plugin admin pages
        if ( 'toplevel_page_lindawp-events-export' !== $hook_suffix && 
             'events-export_page_lindawp-events-export-pdf-settings' !== $hook_suffix ) {
            return;
        }

        // Enqueue admin styles
        wp_enqueue_style(
            $this->plugin_name . '-admin',
            plugin_dir_url( __FILE__ ) . 'css/lindawp-events-export-admin.css',
            array(),
            $this->version,
            'all'
        );

        // Enqueue admin scripts
        wp_enqueue_script(
            $this->plugin_name . '-admin',
            plugin_dir_url( __FILE__ ) . 'js/lindawp-events-export-admin.js',
            array( 'jquery' ),
            $this->version,
            true
        );

        // Enqueue WordPress media uploader scripts
        if ( 'events-export_page_lindawp-events-export-pdf-settings' === $hook_suffix ) {
            wp_enqueue_media();
        }

        // Localize script
        wp_localize_script(
            $this->plugin_name . '-admin',
            'lindawpEventsExport',
            array(
                'errorMessages' => array(
                    'invalidDateRange' => esc_html__( 'End date must be after start date.', 'lindawp-events-export' ),
                ),
                'nonce'         => wp_create_nonce( 'export_events_nonce' ),
            )
        );
    }

    /**
     * Add plugin admin menu
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Events Calendar Listings Export', 'lindawp-events-export'),
            __('Events Export', 'lindawp-events-export'),
            'manage_options',
            'lindawp-events-export',
            array($this, 'display_plugin_admin_page'),
            'dashicons-media-document',
            6
        );

        // Add PDF Settings submenu
        add_submenu_page(
            'lindawp-events-export',
            __('PDF Settings', 'lindawp-events-export'),
            __('PDF Settings', 'lindawp-events-export'),
            'manage_options',
            'lindawp-events-export-pdf-settings',
            array($this, 'display_pdf_settings_page')
        );
    }

    /**
     * Render the admin page
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once 'partials/lindawp-events-export-admin-display.php';
    }

    /**
     * Render the PDF settings page
     *
     * @since    1.0.0
     */
    public function display_pdf_settings_page() {
        include_once 'partials/lindawp-events-export-pdf-settings.php';
    }

    /**
     * Register plugin settings
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'lindawp_events_export_pdf_settings',
            'lindawp_events_export_pdf_logo',
            array(
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'default' => '',
            )
        );
    }

    /**
     * Handle the export events action
     *
     * @since    1.0.0
     */
    public function handle_export_events() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'lindawp-events-export' ) );
        }

        // Verify nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'export_events_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'lindawp-events-export' ) );
        }

        // Validate required fields
        if ( ! isset( $_POST['start_date'] ) || ! isset( $_POST['end_date'] ) ) {
            wp_die( esc_html__( 'Please provide both start and end dates.', 'lindawp-events-export' ) );
        }

        $start_date = sanitize_text_field( wp_unslash( $_POST['start_date'] ) );
        $end_date   = sanitize_text_field( wp_unslash( $_POST['end_date'] ) );

        // Validate date formats
        if ( ! $this->validate_date( $start_date ) || ! $this->validate_date( $end_date ) ) {
            wp_die( esc_html__( 'Invalid date format. Please use YYYY-MM-DD format.', 'lindawp-events-export' ) );
        }

        // Validate date range
        if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
            wp_die( esc_html__( 'End date must be after start date.', 'lindawp-events-export' ) );
        }

        // Validate date range is not too large
        $date_diff = abs( strtotime( $end_date ) - strtotime( $start_date ) );
        $days_diff = floor( $date_diff / ( 60 * 60 * 24 ) );
        if ( $days_diff > 365 ) {
            wp_die( esc_html__( 'Date range cannot exceed one year.', 'lindawp-events-export' ) );
        }

        try {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lindawp-events-export-pdf.php';
            $pdf_exporter = new Lindawp_Events_Export_PDF();
            $pdf_exporter->export_events_to_pdf( $start_date, $end_date );
        } catch ( Exception $e ) {
            wp_die( 
                esc_html__( 'Error generating PDF: ', 'lindawp-events-export' ) . esc_html( $e->getMessage() ),
                esc_html__( 'PDF Generation Error', 'lindawp-events-export' ),
                array( 'back_link' => true )
            );
        }
    }

    /**
     * Validate date format
     *
     * @since    1.0.0
     * @param    string $date Date string to validate.
     * @return   boolean True if valid date in YYYY-MM-DD format.
     */
    private function validate_date( $date ) {
        $d = DateTime::createFromFormat( 'Y-m-d', $date );
        return $d && $d->format( 'Y-m-d' ) === $date;
    }
}

<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/public
 */

/**
 * The public-facing functionality of the plugin.
 */
class Lindawp_Events_Export_Public {

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
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Handle the frontend export events action
     *
     * @since    1.0.0
     */
    public function handle_frontend_export_events() {
        if (isset($_POST['export_events']) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'export_events_nonce')) {
                wp_die('Security check failed');
            }

            $start_date = sanitize_text_field($_POST['start_date']);
            $end_date = sanitize_text_field($_POST['end_date']);

            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lindawp-events-export-pdf.php';
            $pdf_exporter = new Lindawp_Events_Export_PDF();
            $pdf_exporter->export_events_to_pdf($start_date, $end_date);
        }
    }

    /**
     * Display the export form shortcode
     *
     * @since    1.0.0
     * @return   string    The HTML content for the export form.
     */
    public function display_export_form() {
        ob_start();
        ?>
        <div class="lindawp-events-export-form">
            <h2><?php esc_html_e('Export Events', 'lindawp-events-export'); ?></h2>
            <p><?php esc_html_e('Please Choose the period of the events you would like to export and then click Export Events', 'lindawp-events-export'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('export_events_nonce'); ?>
                <label for="start_date"><?php esc_html_e('Start Date:', 'lindawp-events-export'); ?></label>
                <input type="date" id="start_date" name="start_date" required>
                
                <label for="end_date"><?php esc_html_e('End Date:', 'lindawp-events-export'); ?></label>
                <input type="date" id="end_date" name="end_date" required>
                
                <input type="submit" name="export_events" value="<?php esc_attr_e('Export Events', 'lindawp-events-export'); ?>" class="button button-primary">
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}

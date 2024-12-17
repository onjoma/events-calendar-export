<?php
/**
 * Handle PDF generation functionality
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/includes
 */

/**
 * Handle PDF generation functionality
 */
class Lindawp_Events_Export_PDF {

    /**
     * Export events to PDF
     *
     * @since    1.0.0
     * @param    string    $start_date    Start date for events query.
     * @param    string    $end_date      End date for events query.
     * @throws   Exception If PDF generation fails.
     */
    public function export_events_to_pdf($start_date, $end_date) {
        try {
            // Check for The Events Calendar plugin
            if (!class_exists('Tribe__Events__Main')) {
                throw new Exception(__('The Events Calendar plugin is required but not active.', 'lindawp-events-export'));
            }

            // Check for mPDF
            if (!class_exists('Mpdf\Mpdf')) {
                if (!file_exists(plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php')) {
                    throw new Exception(__('mPDF library not found. Please run composer install.', 'lindawp-events-export'));
                }
                require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
            }

            // Increase PHP limits
            $this->increase_php_limits();

            $events = $this->get_events($start_date, $end_date);

            if (!$events->have_posts()) {
                throw new Exception(__('No events found for the selected date range.', 'lindawp-events-export'));
            }

            // Configure mPDF
            $mpdf_config = $this->get_mpdf_config();
            
            try {
                $mpdf = new \Mpdf\Mpdf($mpdf_config);
            } catch (\Mpdf\MpdfException $e) {
                error_log('LindaWP Events Export - mPDF initialization error: ' . $e->getMessage());
                throw new Exception(__('Failed to initialize PDF generator.', 'lindawp-events-export'));
            }

            // Add custom CSS
            $stylesheet = $this->get_pdf_stylesheet();
            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

            // Add cover page with logo
            $this->add_cover_page($mpdf, $start_date, $end_date);

            // Process each event
            while ($events->have_posts()) {
                $events->the_post();
                $event_id = get_the_ID();
                
                try {
                    $this->add_event_to_pdf($mpdf, $event_id, $events);
                } catch (Exception $e) {
                    error_log(sprintf(
                        'LindaWP Events Export - Error processing event %d: %s',
                        $event_id,
                        $e->getMessage()
                    ));
                    // Continue with next event
                    continue;
                }
            }

            wp_reset_postdata();

            // Clean up temp directory
            $this->cleanup_temp_files($mpdf_config['tempDir']);

            // Generate filename
            $filename = $this->generate_filename($start_date, $end_date);

            // Output PDF
            try {
                $mpdf->Output($filename, 'D');
            } catch (\Mpdf\MpdfException $e) {
                error_log('LindaWP Events Export - PDF output error: ' . $e->getMessage());
                throw new Exception(__('Failed to generate PDF file.', 'lindawp-events-export'));
            }
            exit;

        } catch (Exception $e) {
            error_log('LindaWP Events Export - Fatal error: ' . $e->getMessage());
            wp_die(
                esc_html($e->getMessage()),
                __('PDF Generation Error', 'lindawp-events-export'),
                array('back_link' => true)
            );
        }
    }

    /**
     * Increase PHP limits for PDF generation
     */
    private function increase_php_limits() {
        $success = true;
        
        if (!ini_set('pcre.backtrack_limit', '5000000')) {
            error_log('LindaWP Events Export - Failed to set pcre.backtrack_limit');
            $success = false;
        }
        
        if (!ini_set('memory_limit', '256M')) {
            error_log('LindaWP Events Export - Failed to set memory_limit');
            $success = false;
        }
        
        if (!set_time_limit(300)) {
            error_log('LindaWP Events Export - Failed to set time limit');
            $success = false;
        }

        if (!$success) {
            error_log('LindaWP Events Export - Warning: Some PHP limits could not be increased');
        }
    }

    /**
     * Get mPDF configuration
     *
     * @return array mPDF configuration
     */
    private function get_mpdf_config() {
        return [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'tempDir' => wp_upload_dir()['basedir'] . '/mpdf_temp',
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch',
            'defaultheaderline' => 0,
            'defaultfooterline' => 0,
            'default_font' => 'dejavusans',
        ];
    }

    /**
     * Convert URL to local file path
     *
     * @param string $url URL to convert
     * @return string|false Local file path or false if conversion fails
     */
    private function url_to_path($url) {
        // Get upload directory info
        $upload_dir = wp_upload_dir();
        
        // Convert URL to path
        $path = str_replace(
            $upload_dir['baseurl'],
            $upload_dir['basedir'],
            $url
        );
        
        return file_exists($path) ? $path : false;
    }

    /**
     * Generate cover page
     *
     * @param \Mpdf\Mpdf $mpdf mPDF instance
     * @param string $start_date Start date
     * @param string $end_date End date
     */
    private function add_cover_page($mpdf, $start_date, $end_date) {
        $logo_url = get_option('lindawp_events_export_pdf_logo');
        $logo_html = '';
        
        if (!empty($logo_url)) {
            $logo_path = $this->url_to_path($logo_url);
            
            if ($logo_path) {
                try {
                    // Get image dimensions
                    list($width, $height) = getimagesize($logo_path);
                    $max_width = 200; // Maximum width in pixels
                    
                    // Calculate new height maintaining aspect ratio
                    $new_height = ($max_width / $width) * $height;
                    
                    $logo_html = sprintf(
                        '<div style="text-align: center; margin-bottom: 30px;"><img src="%s" style="width: %.2fpx; height: %.2fpx;"></div>',
                        $logo_path,
                        $max_width,
                        $new_height
                    );
                } catch (Exception $e) {
                    error_log('LindaWP Events Export - Logo processing error: ' . $e->getMessage());
                }
            }
        }

        $site_name = get_bloginfo('name');
        $date_format = get_option('date_format');
        
        $html = sprintf(
            '<div style="text-align: center; padding-top: 100px;">
                %s
                <h1 style="font-size: 24px; margin-bottom: 20px;">%s</h1>
                <h2 style="font-size: 20px; margin-bottom: 30px;">%s</h2>
                <p style="font-size: 16px;">%s - %s</p>
            </div>',
            $logo_html,
            esc_html($site_name),
            __('Events Calendar Export', 'lindawp-events-export'),
            date_i18n($date_format, strtotime($start_date)),
            date_i18n($date_format, strtotime($end_date))
        );

        $mpdf->AddPage();
        $mpdf->WriteHTML($html);
        $mpdf->AddPage();
    }

    /**
     * Add event to PDF
     *
     * @param \Mpdf\Mpdf $mpdf     mPDF instance
     * @param int        $event_id  Event ID
     * @param WP_Query   $events    Events query object
     * @throws Exception If event processing fails
     */
    private function add_event_to_pdf($mpdf, $event_id, $events) {
        // Generate and write content in chunks
        $title_content = $this->generate_event_title($event_id);
        $mpdf->WriteHTML($title_content, \Mpdf\HTMLParserMode::HTML_BODY);

        $image_content = $this->generate_event_image($event_id);
        if (!empty($image_content)) {
            $mpdf->WriteHTML($image_content, \Mpdf\HTMLParserMode::HTML_BODY);
        }

        $description_content = $this->generate_event_description($event_id);
        $mpdf->WriteHTML($description_content, \Mpdf\HTMLParserMode::HTML_BODY);

        $meta_content = $this->generate_event_meta($event_id);
        $mpdf->WriteHTML($meta_content, \Mpdf\HTMLParserMode::HTML_BODY);

        // Only add a new page if this is not the last event
        if ($events->current_post + 1 !== $events->post_count) {
            $mpdf->AddPage();
        }
    }

    /**
     * Generate filename for PDF
     *
     * @param string $start_date Start date
     * @param string $end_date   End date
     * @return string Filename
     */
    private function generate_filename($start_date, $end_date) {
        $formatted_start_date = date_i18n(get_option('date_format'), strtotime($start_date));
        $formatted_end_date = date_i18n(get_option('date_format'), strtotime($end_date));
        
        return sprintf(
            'events-%s-to-%s.pdf',
            sanitize_file_name($formatted_start_date),
            sanitize_file_name($formatted_end_date)
        );
    }

    /**
     * Get PDF stylesheet
     *
     * @return string Stylesheet
     */
    private function get_pdf_stylesheet() {
        return '
            body { font-family: "dejavusans"; }
            h2 { color: #333; margin-bottom: 10px; }
            .event-image { width: 100%; max-width: 600px; margin: 10px 0; }
            .event-image img { max-width: 100%; height: auto; }
            .event-description { margin: 15px 0; }
            .event-meta { color: #666; margin: 5px 0; }
            .screen-reader-text { position: absolute; left: -1000px; width: 1px; height: 1px; top: auto; overflow: hidden; }
        ';
    }

    /**
     * Generate event title content
     *
     * @param int $event_id Event ID
     * @return string HTML content
     */
    private function generate_event_title($event_id) {
        return '<h2>' . esc_html(get_the_title($event_id)) . '</h2>';
    }

    /**
     * Generate event image content
     *
     * @param int $event_id Event ID
     * @return string HTML content
     */
    private function generate_event_image($event_id) {
        if (!has_post_thumbnail($event_id)) {
            return '';
        }

        $image_id = get_post_thumbnail_id($event_id);
        $image_path = get_attached_file($image_id);

        if (!$image_path || !file_exists($image_path)) {
            return '';
        }

        // Get image dimensions
        $image_size = getimagesize($image_path);
        if (!$image_size) {
            return '';
        }

        // Resize image if too large
        if ($image_size[0] > 1200) {
            $image = wp_get_image_editor($image_path);
            if (!is_wp_error($image)) {
                $image->resize(1200, null, false);
                $image_path = $image->save();
                if (!is_wp_error($image_path)) {
                    $image_path = $image_path['path'];
                }
            }
        }

        // Get image data and mime type
        $image_data = file_get_contents($image_path);
        $mime_type = mime_content_type($image_path);

        if (!$image_data || !$mime_type) {
            return '';
        }

        $base64_image = base64_encode($image_data);
        return '<div class="event-image" role="img" aria-label="' . esc_attr(get_the_title($event_id)) . '">
                <img src="data:' . $mime_type . ';base64,' . $base64_image . '" 
                     alt="' . esc_attr(get_the_title($event_id)) . '" />
                </div>';
    }

    /**
     * Generate event description content
     *
     * @param int $event_id Event ID
     * @return string HTML content
     */
    private function generate_event_description($event_id) {
        $description = get_the_content(null, false, $event_id);
        return '<div class="event-description" role="region" aria-label="' . esc_attr__('Event Description', 'lindawp-events-export') . '">' 
               . wp_kses_post(nl2br($description)) . '</div>';
    }

    /**
     * Generate event meta content
     *
     * @param int $event_id Event ID
     * @return string HTML content
     */
    private function generate_event_meta($event_id) {
        // Check if The Events Calendar is active
        if (!class_exists('Tribe__Events__Main')) {
            return '<p role="alert">' . esc_html__('The Events Calendar plugin is required.', 'lindawp-events-export') . '</p>';
        }

        $start_date = tribe_get_start_date($event_id, true, 'Y-m-d H:i:s');
        $end_date = tribe_get_end_date($event_id, true, 'Y-m-d H:i:s');
        $venue_id = tribe_get_venue_id($event_id);
        
        $content = '<div class="event-meta" role="region" aria-label="' . esc_attr__('Event Details', 'lindawp-events-export') . '">';
        
        if (!empty($start_date) && !empty($end_date)) {
            $date_label = esc_html__('Date: ', 'lindawp-events-export');
            $content .= '<p>
                            <span class="screen-reader-text">' . $date_label . '</span>
                            <span aria-label="' . esc_attr__('Event Date Range', 'lindawp-events-export') . '">' . 
                            $date_label . 
                            esc_html(date_i18n(get_option('date_format'), strtotime($start_date))) . ' - ' . 
                            esc_html(date_i18n(get_option('date_format'), strtotime($end_date))) . 
                            '</span>
                        </p>';
        }

        if (!empty($start_date)) {
            $time_label = esc_html__('Time: ', 'lindawp-events-export');
            $content .= '<p>
                            <span class="screen-reader-text">' . $time_label . '</span>
                            <span aria-label="' . esc_attr__('Event Start Time', 'lindawp-events-export') . '">' . 
                            $time_label . 
                            esc_html(date_i18n(get_option('time_format'), strtotime($start_date))) . 
                            '</span>
                        </p>';
        }

        if ($venue_id) {
            $venue_name = tribe_get_venue($event_id);
            $address = array_filter(array(
                tribe_get_address($venue_id),
                tribe_get_city($venue_id),
                tribe_get_state($venue_id),
                tribe_get_zip($venue_id),
                tribe_get_country($venue_id)
            ));

            if (!empty($venue_name)) {
                $venue_label = esc_html__('Venue: ', 'lindawp-events-export');
                $content .= '<p>
                                <span class="screen-reader-text">' . $venue_label . '</span>
                                <span aria-label="' . esc_attr__('Event Venue', 'lindawp-events-export') . '">' . 
                                $venue_label . esc_html($venue_name) . 
                                '</span>
                            </p>';
            }

            if (!empty($address)) {
                $address_label = esc_html__('Address: ', 'lindawp-events-export');
                $content .= '<p>
                                <span class="screen-reader-text">' . $address_label . '</span>
                                <span aria-label="' . esc_attr__('Venue Address', 'lindawp-events-export') . '">' . 
                                $address_label . esc_html(implode(', ', $address)) . 
                                '</span>
                            </p>';
            }

            $full_address = implode(', ', $address);
            if (!empty($full_address)) {
                $maps_url = 'https://www.google.com/maps/search/' . urlencode($full_address);
                $content .= '<p><a href="' . esc_url($maps_url) . '" 
                                  target="_blank" 
                                  rel="noopener noreferrer" 
                                  onclick="window.open(this.href, \'_blank\'); return false;"
                                  aria-label="' . esc_attr__('Open venue location in Google Maps (opens in new tab)', 'lindawp-events-export') . '">' . 
                           esc_html__('View on Google Maps', 'lindawp-events-export') . 
                           '<span class="screen-reader-text">' . esc_html__(' (opens in new tab)', 'lindawp-events-export') . '</span></a></p>';
            }
        }
        $content .= '</div>';
        return $content;
    }

    /**
     * Get events for the specified date range
     *
     * @since    1.0.0
     * @param    string    $start_date    Start date for events query.
     * @param    string    $end_date      End date for events query.
     * @return   WP_Query  Events query result.
     */
    private function get_events($start_date, $end_date) {
        $args = array(
            'post_type' => 'tribe_events',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_EventStartDate',
                    'value' => array($start_date, $end_date),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        );

        return new WP_Query($args);
    }

    /**
     * Clean up temporary files
     *
     * @param string $temp_dir Path to temp directory
     */
    private function cleanup_temp_files($temp_dir) {
        if (!is_dir($temp_dir)) {
            return;
        }

        $files = glob($temp_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

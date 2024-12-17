<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p><?php esc_html_e('Please Choose the period of the events you would like to export and then click Export Events', 'lindawp-events-export'); ?></p>
    
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="events-export-form" class="lindawp-events-export-form">
        <?php wp_nonce_field('export_events_nonce'); ?>
        <input type="hidden" name="action" value="export_events">
        
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="start_date"><?php esc_html_e('Start Date:', 'lindawp-events-export'); ?></label>
                </th>
                <td>
                    <input type="date" id="start_date" name="start_date" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="end_date"><?php esc_html_e('End Date:', 'lindawp-events-export'); ?></label>
                </th>
                <td>
                    <input type="date" id="end_date" name="end_date" class="regular-text" required>
                    <p class="description error-message" id="date-error">
                        <?php esc_html_e('End date must be after start date.', 'lindawp-events-export'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Export Events', 'lindawp-events-export'), 'primary', 'submit', true, array('id' => 'submit-btn')); ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    function validateDates() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var submitBtn = $('#submit-btn');
        var errorMsg = $('#date-error');
        
        if (startDate && endDate) {
            if (new Date(endDate) < new Date(startDate)) {
                errorMsg.addClass('error');
                submitBtn.prop('disabled', true);
                return false;
            } else {
                errorMsg.removeClass('error');
                submitBtn.prop('disabled', false);
                return true;
            }
        }
        return true;
    }

    // Add event listeners for date changes
    $('#start_date, #end_date').on('change', validateDates);

    // Validate on form submission
    $('#events-export-form').on('submit', function(e) {
        if (!validateDates()) {
            e.preventDefault();
        }
    });
});
</script>

<style>
    .error-message.error {
        color: #dc3232;
    }
</style>

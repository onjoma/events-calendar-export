/**
 * Admin JavaScript for LindaWP Events Export
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/admin/js
 */

(function( $ ) {
    'use strict';

    /**
     * Validate date range
     * @return {boolean} True if dates are valid
     */
    function validateDates() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var submitBtn = $('#submit-btn');
        var errorMsg = $('#date-error');
        
        if (startDate && endDate) {
            if (new Date(endDate) < new Date(startDate)) {
                errorMsg.show();
                submitBtn.prop('disabled', true);
                return false;
            } else {
                errorMsg.hide();
                submitBtn.prop('disabled', false);
                return true;
            }
        }
        return true;
    }

    $(document).ready(function() {
        // Add event listeners for date changes
        $('#start_date, #end_date').on('change', validateDates);

        // Validate on form submission
        $('#events-export-form').on('submit', function(e) {
            if (!validateDates()) {
                e.preventDefault();
            }
        });
    });

})( jQuery );

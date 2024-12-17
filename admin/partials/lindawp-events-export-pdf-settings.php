<?php
/**
 * Provide a admin area view for the plugin's PDF settings
 *
 * @link       https://onjomapeter.com
 * @since      1.0.0
 *
 * @package    Lindawp_Events_Export
 * @subpackage Lindawp_Events_Export/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('lindawp_events_export_pdf_settings');
        do_settings_sections('lindawp_events_export_pdf_settings');
        ?>
        
        <div class="lindawp-pdf-settings-section">
            <h2><?php esc_html_e('PDF Layout and Styling Options:', 'lindawp-events-export'); ?></h2>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="pdf_logo"><?php esc_html_e('Custom Logo for PDF Header', 'lindawp-events-export'); ?></label>
                    </th>
                    <td>
                        <div class="logo-preview-wrapper">
                            <?php
                            $logo_url = get_option('lindawp_events_export_pdf_logo');
                            $has_logo = !empty($logo_url);
                            ?>
                            <img id="logo-preview" src="<?php echo esc_url($logo_url); ?>" 
                                 style="max-width: 200px; <?php echo $has_logo ? '' : 'display: none;'; ?>">
                        </div>
                        <input type="hidden" id="pdf_logo" name="lindawp_events_export_pdf_logo" 
                               value="<?php echo esc_attr($logo_url); ?>">
                        <input type="button" id="upload_logo_button" class="button" 
                               value="<?php esc_attr_e('Upload Logo', 'lindawp-events-export'); ?>">
                        <input type="button" id="remove_logo_button" class="button" 
                               value="<?php esc_attr_e('Remove Logo', 'lindawp-events-export'); ?>" 
                               <?php echo $has_logo ? '' : 'style="display: none;"'; ?>>
                        <p class="description">
                            <?php esc_html_e('Upload a logo to be displayed in the header of your PDF exports. Recommended size: 200x100 pixels.', 'lindawp-events-export'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var mediaUploader;
    
    $('#upload_logo_button').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: '<?php esc_html_e('Choose Logo', 'lindawp-events-export'); ?>',
            button: {
                text: '<?php esc_html_e('Use this logo', 'lindawp-events-export'); ?>'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#pdf_logo').val(attachment.url);
            $('#logo-preview').attr('src', attachment.url).show();
            $('#remove_logo_button').show();
        });
        
        mediaUploader.open();
    });
    
    $('#remove_logo_button').on('click', function(e) {
        e.preventDefault();
        $('#pdf_logo').val('');
        $('#logo-preview').hide();
        $(this).hide();
    });
});
</script>

<style>
.logo-preview-wrapper {
    margin-bottom: 10px;
}
#logo-preview {
    border: 1px solid #ddd;
    padding: 5px;
    margin-bottom: 10px;
}
#remove_logo_button {
    margin-left: 10px;
}
.lindawp-pdf-settings-section {
    background: #fff;
    padding: 20px;
    margin: 20px 0;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
</style>

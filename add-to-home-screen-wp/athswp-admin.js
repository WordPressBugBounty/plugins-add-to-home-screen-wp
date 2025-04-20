jQuery(document).ready(function($) {
    // Handle icon upload buttons
    $('.upload-icon-button').click(function(e) {
        e.preventDefault();
        var button = $(this);
        var inputId = button.data('input');
        var customUploader = wp.media({
            title: 'Select or Upload Icon',
            button: { text: 'Use this Icon' },
            multiple: false,
            library: { type: 'image' }
        }).on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            $('#' + inputId).val(attachment.url);
        }).open();
    });

    // Toggle custom URL field visibility
    $('#new_athswp_frontend_pwa_start_url').change(function() {
        if ($(this).val() === 'homepage_with_path') {
            $('#new_athswp_pwa_custom_url').show();
        } else {
            $('#new_athswp_pwa_custom_url').hide();
        }
    });

    // Trigger change on page load to set initial state
    $('#new_athswp_frontend_pwa_start_url').trigger('change');
});
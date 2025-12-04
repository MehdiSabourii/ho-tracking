/**
 * Admin JavaScript for HO Tracking
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        var uploadForm = $('#ho-tracking-upload-form');
        var uploadButton = $('#upload-button');
        var spinner = uploadForm.find('.spinner');
        var messageDiv = $('#upload-message');
        
        uploadForm.on('submit', function(e) {
            e.preventDefault();
            
            var fileInput = $('#tracking_file')[0];
            if (!fileInput.files.length) {
                showMessage('Please select a file to upload', 'error');
                return;
            }
            
            var formData = new FormData();
            formData.append('action', 'ho_tracking_upload');
            formData.append('nonce', hoTracking.nonce);
            formData.append('tracking_file', fileInput.files[0]);
            formData.append('clear_existing', $('#clear_existing').is(':checked') ? 'true' : 'false');
            
            // Disable form elements
            uploadButton.prop('disabled', true);
            spinner.addClass('is-active');
            messageDiv.hide();
            
            $.ajax({
                url: hoTracking.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                        uploadForm[0].reset();
                    } else {
                        showMessage(response.data.message || 'Upload failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showMessage('An error occurred: ' + error, 'error');
                },
                complete: function() {
                    uploadButton.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
        
        function showMessage(message, type) {
            messageDiv
                .removeClass('success error')
                .addClass(type)
                .html('<p>' + message + '</p>')
                .show();
        }
    });
    
})(jQuery);

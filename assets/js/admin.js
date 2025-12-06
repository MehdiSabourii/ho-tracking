/**
 * Admin JavaScript for HO Tracking
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Upload form handling
        var uploadForm = $('#ho-tracking-upload-form');
        var uploadButton = $('#upload-button');
        var uploadSpinner = uploadForm.find('.spinner');
        var uploadMessageDiv = $('#upload-message');
        
        uploadForm.on('submit', function(e) {
            e.preventDefault();
            
            var fileInput = $('#tracking_file')[0];
            if (!fileInput.files.length) {
                showUploadMessage('Please select a file to upload', 'error');
                return;
            }
            
            var file = fileInputElem.files[0];
            var fileName = file.name;
            
            // Confirm before clearing existing data
            var clearExisting = $('#clear_existing').is(':checked');
            if (clearExisting) {
                if (!confirm('Are you sure you want to delete all existing tracking records? This action cannot be undone.')) {
                    return;
                }
            }
            
            var formData = new FormData();
            formData.append('action', 'ho_tracking_upload');
            formData.append('nonce', hoTracking.nonce);
            formData.append('tracking_file', file);
            formData.append('clear_existing', clearExisting ? 'true' : 'false');
            
            // Disable form elements
            uploadButton.prop('disabled', true);
            uploadSpinner.addClass('is-active');
            uploadMessageDiv.hide();
            fileInput.prop('disabled', true);
            spinner.addClass('is-active');
            messageDiv.hide();
            
            // Show progress message
            showMessage('Uploading and processing ' + fileName + '...', 'info');
            
            $.ajax({
                url: hoTracking.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: 60000, // 60 seconds timeout
                success: function(response) {
                    if (response.success) {
                        showUploadMessage(response.data.message, 'success');
                        uploadForm[0].reset();
                    } else {
                        showUploadMessage(response.data.message || 'Upload failed', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showUploadMessage('An error occurred: ' + error, 'error');
                },
                complete: function() {
                    uploadButton.prop('disabled', false);
                    uploadSpinner.removeClass('is-active');
                }
            });
        });
        
        function showUploadMessage(message, type) {
            uploadMessageDiv
                .removeClass('success error')
                .addClass(type)
                .html('<p>' + message + '</p>')
                .show();
        }
        
        // Settings form handling
        var settingsForm = $('#ho-tracking-settings-form');
        var saveButton = $('#save-settings-button');
        var settingsSpinner = settingsForm.find('.spinner');
        var settingsMessageDiv = $('#settings-message');
        
        settingsForm.on('submit', function(e) {
            e.preventDefault();
            
            var visibleColumns = [];
            $('input[name="visible_columns[]"]:checked').each(function() {
                visibleColumns.push($(this).val());
            });
            
            // Disable form elements
            saveButton.prop('disabled', true);
            settingsSpinner.addClass('is-active');
            settingsMessageDiv.hide();
            
            $.ajax({
                url: hoTracking.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ho_tracking_save_settings',
                    nonce: hoTracking.settingsNonce,
                    visible_columns: visibleColumns
                },
                success: function(response) {
                    if (response.success) {
                        showSettingsMessage(response.data.message, 'success');
                    } else {
                        showSettingsMessage(response.data.message || 'Failed to save settings', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showSettingsMessage('An error occurred: ' + error, 'error');
                },
                complete: function() {
                    saveButton.prop('disabled', false);
                    settingsSpinner.removeClass('is-active');
                }
            });
        });
        
        function showSettingsMessage(message, type) {
            settingsMessageDiv
                .removeClass('success error')
                .addClass(type)
                .html('<p>' + escapeHtml(message) + '</p>')
                .show();
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: messageDiv.offset().top - 100
            }, 300);
        }
        
        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    });
    
})(jQuery);

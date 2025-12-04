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
        var fileInput = $('#tracking_file');
        
        // File input validation
        fileInput.on('change', function() {
            var file = this.files[0];
            messageDiv.hide();
            
            if (file) {
                // Check file size (10MB max)
                var maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    showMessage('File size exceeds maximum allowed size (10MB). Please select a smaller file.', 'error');
                    this.value = '';
                    return;
                }
                
                // Check file extension
                var fileName = file.name;
                var fileExt = fileName.split('.').pop().toLowerCase();
                var allowedExts = ['csv', 'xls', 'xlsx'];
                
                if (allowedExts.indexOf(fileExt) === -1) {
                    showMessage('Invalid file format. Please upload a CSV, XLS, or XLSX file.', 'error');
                    this.value = '';
                    return;
                }
                
                // Show file info
                var fileSize = (file.size / 1024).toFixed(2) + ' KB';
                if (file.size > 1024 * 1024) {
                    fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                }
                console.log('Selected file: ' + fileName + ' (' + fileSize + ')');
            }
        });
        
        uploadForm.on('submit', function(e) {
            e.preventDefault();
            
            var fileInputElem = fileInput[0];
            if (!fileInputElem.files.length) {
                showMessage('Please select a file to upload.', 'error');
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
                        showMessage(response.data.message, 'success');
                        uploadForm[0].reset();
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : 'Upload failed. Please try again.';
                        showMessage(errorMsg, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = 'An error occurred while uploading the file.';
                    
                    if (status === 'timeout') {
                        errorMsg = 'Upload timeout. The file may be too large or the server is busy. Please try again.';
                    } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMsg = xhr.responseJSON.data.message;
                    } else if (xhr.status === 0) {
                        errorMsg = 'Network error. Please check your internet connection and try again.';
                    } else if (xhr.status === 413) {
                        errorMsg = 'File is too large. Please upload a smaller file.';
                    } else if (error) {
                        errorMsg = 'Error: ' + error;
                    }
                    
                    showMessage(errorMsg, 'error');
                    console.error('Upload error:', status, error, xhr);
                },
                complete: function() {
                    uploadButton.prop('disabled', false);
                    fileInput.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        });
        
        function showMessage(message, type) {
            messageDiv
                .removeClass('success error info')
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

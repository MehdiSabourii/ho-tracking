/**
 * Admin JavaScript for HO Tracking
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Upload form handler
        var uploadForm = $('#ho-tracking-upload-form');
        var uploadButton = $('#upload-button');
        var spinner = uploadForm.find('.spinner');
        var messageDiv = $('#upload-message');
        
        if (uploadForm.length) {
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
        }
        
        // Management page handlers
        var modal = $('#edit-record-modal');
        var modalContent = modal.find('.modal-content');
        
        // Select all checkbox
        $('#select-all-records').on('change', function() {
            $('.record-checkbox').prop('checked', $(this).prop('checked'));
        });
        
        // Bulk actions
        $('#bulk-action-apply').on('click', function() {
            var action = $('#bulk-action-selector').val();
            if (!action) return;
            
            var selectedIds = $('.record-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                showAdminMessage('Please select at least one record', 'error');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm(hoTracking.confirm_bulk_delete)) return;
                
                $.ajax({
                    url: hoTracking.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ho_tracking_bulk_delete',
                        nonce: hoTracking.nonce,
                        record_ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            showAdminMessage(response.data.message, 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAdminMessage(response.data.message, 'error');
                        }
                    }
                });
            }
        });
        
        // Delete single record
        $(document).on('click', '.delete-record', function() {
            if (!confirm(hoTracking.confirm_delete)) return;
            
            var recordId = $(this).data('record-id');
            var row = $(this).closest('tr');
            
            $.ajax({
                url: hoTracking.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ho_tracking_delete_record',
                    nonce: hoTracking.nonce,
                    record_id: recordId
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                        });
                        showAdminMessage(response.data.message, 'success');
                    } else {
                        showAdminMessage(response.data.message, 'error');
                    }
                }
            });
        });
        
        // Edit record - open modal
        $(document).on('click', '.edit-record', function() {
            var recordId = $(this).data('record-id');
            var row = $(this).closest('tr');
            
            // Get current values from the row
            var tracking_code = row.find('.tracking-code strong').text();
            var recipient_name = row.find('td:eq(2)').text();
            var status = row.find('.status-badge').text().trim();
            var date_sent = row.find('td:eq(4)').text();
            var date_delivered = row.find('td:eq(5)').text();
            
            // Populate modal fields
            $('#edit-record-id').val(recordId);
            $('#edit-tracking-code').val(tracking_code);
            $('#edit-recipient-name').val(recipient_name);
            $('#edit-status').val(status);
            $('#edit-date-sent').val(date_sent !== '—' ? date_sent : '');
            $('#edit-date-delivered').val(date_delivered !== '—' ? date_delivered : '');
            $('#edit-notes').val('');
            
            modal.fadeIn(200);
        });
        
        // Close modal
        $('.modal-close, .modal-overlay').on('click', function() {
            modal.fadeOut(200);
        });
        
        // Update record form submission
        $('#edit-record-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=ho_tracking_update_record';
            formData += '&nonce=' + hoTracking.nonce;
            
            var submitButton = $(this).find('button[type="submit"]');
            var formSpinner = $(this).find('.spinner');
            
            submitButton.prop('disabled', true);
            formSpinner.addClass('is-active');
            
            $.ajax({
                url: hoTracking.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showAdminMessage(response.data.message, 'success');
                        modal.fadeOut(200);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showAdminMessage(response.data.message, 'error');
                    }
                },
                complete: function() {
                    submitButton.prop('disabled', false);
                    formSpinner.removeClass('is-active');
                }
            });
        });
        
        // Clear all data button
        $('#clear-all-data').on('click', function() {
            if (!confirm('Are you sure you want to delete ALL tracking records? This action cannot be undone!')) {
                return;
            }
            
            if (!confirm('This is your final warning. All data will be permanently deleted. Continue?')) {
                return;
            }
            
            var button = $(this);
            button.prop('disabled', true).text('Deleting...');
            
            $.ajax({
                url: hoTracking.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ho_tracking_upload',
                    nonce: hoTracking.nonce,
                    clear_existing: 'true'
                },
                success: function(response) {
                    showAdminMessage('All data has been cleared', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function() {
                    showAdminMessage('Failed to clear data', 'error');
                    button.prop('disabled', false).text('Clear All Data');
                }
            });
        });
        
        // Copy shortcode button
        $('.button-copy').on('click', function() {
            var text = $(this).data('clipboard-text');
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(text).select();
            document.execCommand('copy');
            tempInput.remove();
            
            var button = $(this);
            var originalText = button.text();
            button.text('Copied!');
            setTimeout(function() {
                button.text(originalText);
            }, 2000);
        });
        
        // Helper functions
        function showMessage(message, type) {
            messageDiv
                .removeClass('success error')
                .addClass(type)
                .html('<p>' + message + '</p>')
                .show();
        }
        
        function showAdminMessage(message, type) {
            var messageContainer = $('#message-container');
            if (!messageContainer.length) {
                messageContainer = $('<div id="message-container"></div>').appendTo('body');
            }
            
            var messageEl = $('<div class="admin-message ' + type + '">' + message + '</div>');
            messageContainer.append(messageEl);
            
            setTimeout(function() {
                messageEl.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);
        }
    });
    
})(jQuery);

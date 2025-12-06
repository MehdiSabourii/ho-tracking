/**
 * Frontend JavaScript for HO Tracking
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        var searchForm = $('#ho-tracking-search-form');
        var searchInput = $('#tracking-search-input');
        var loadingDiv = $('#tracking-loading');
        var noResultsDiv = $('#tracking-no-results');
        var tableContainer = $('#tracking-table-container');
        
        // Load all records on page load
        performSearch('');
        
        // Handle search form submission
        searchForm.on('submit', function(e) {
            e.preventDefault();
            var searchTerm = searchInput.val().trim();
            performSearch(searchTerm);
        });
        
        // Real-time search as user types (with debouncing)
        var searchTimeout;
        searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                var searchTerm = searchInput.val().trim();
                performSearch(searchTerm);
            }, 500); // Wait 500ms after user stops typing
        });
        
        function performSearch(searchTerm) {
            // Show loading
            loadingDiv.show();
            noResultsDiv.hide();
            tableContainer.hide();
            
            $.ajax({
                url: hoTracking.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ho_tracking_search',
                    nonce: hoTracking.nonce,
                    search: searchTerm
                },
                timeout: 30000, // 30 seconds timeout
                success: function(response) {
                    loadingDiv.hide();
                    
                    if (response.success && response.data && response.data.data && response.data.data.length > 0) {
                        displayResults(response.data.data);
                    } else if (!response.success && response.data && response.data.message) {
                        noResultsDiv.html('<p>' + escapeHtml(response.data.message) + '</p>').show();
                        tableContainer.hide();
                    } else {
                        noResultsDiv.html('<p>No tracking information found. Please check your tracking code and try again.</p>').show();
                        tableContainer.hide();
                    }
                },
                error: function(xhr, status, error) {
                    loadingDiv.hide();
                    var errorMsg = 'An error occurred while searching. Please try again.';
                    
                    if (status === 'timeout') {
                        errorMsg = 'Search timeout. Please try again.';
                    } else if (xhr.status === 0) {
                        errorMsg = 'Network error. Please check your internet connection.';
                    }
                    
                    noResultsDiv.html('<p>' + errorMsg + '</p>').show();
                    console.error('Search error:', status, error, xhr);
                }
            });
        }
        
        function displayResults(data) {
            var visibleColumns = hoTracking.visibleColumns || ['tracking_code', 'recipient_name', 'status', 'date_sent', 'date_delivered', 'notes'];
            
            // Define column labels in both English and Persian
            var columnLabels = {
                'tracking_code': 'Tracking Code / کد رهگیری',
                'recipient_name': 'Recipient Name / نام گیرنده',
                'status': 'Status / وضعیت',
                'date_sent': 'Date Sent / تاریخ ارسال',
                'date_delivered': 'Date Delivered / تاریخ تحویل',
                'notes': 'Notes / توضیحات'
            };
            
            var html = '<table class="tracking-table">';
            html += '<thead>';
            html += '<tr>';
            
            // Add headers for visible columns
            $.each(visibleColumns, function(index, column) {
                if (columnLabels[column]) {
                    html += '<th>' + escapeHtml(columnLabels[column]) + '</th>';
                }
            });
            
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            $.each(data, function(index, row) {
                html += '<tr>';
                
                $.each(visibleColumns, function(colIndex, column) {
                    if (columnLabels[column]) {
                        var label = columnLabels[column].split(' / ')[0]; // Use English label for data-label
                        var value = '';
                        
                        switch(column) {
                            case 'tracking_code':
                                value = '<strong>' + escapeHtml(row.tracking_code || '-') + '</strong>';
                                break;
                            case 'recipient_name':
                                value = escapeHtml(row.recipient_name || '-');
                                break;
                            case 'status':
                                value = formatStatus(row.status);
                                break;
                            case 'date_sent':
                                value = escapeHtml(row.date_sent || '-');
                                break;
                            case 'date_delivered':
                                value = escapeHtml(row.date_delivered || '-');
                                break;
                            case 'notes':
                                value = escapeHtml(row.notes || '-');
                                break;
                        }
                        
                        html += '<td data-label="' + escapeHtml(label) + '">' + value + '</td>';
                    }
                });
                
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            
            tableContainer.html(html).show();
        }
        
        function formatStatus(status) {
            if (!status) {
                return '-';
            }
            
            var statusLower = status.toLowerCase();
            var statusClass = 'status-badge';
            
            if (statusLower.includes('delivered') || statusLower.includes('تحویل')) {
                statusClass += ' status-delivered';
            } else if (statusLower.includes('transit') || statusLower.includes('در حال ارسال')) {
                statusClass += ' status-in-transit';
            } else {
                statusClass += ' status-pending';
            }
            
            return '<span class="' + statusClass + '">' + escapeHtml(status) + '</span>';
        }
        
        function escapeHtml(text) {
            if (!text) return '';
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

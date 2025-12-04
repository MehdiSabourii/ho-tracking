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
            var html = '<table class="tracking-table">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>Tracking Code</th>';
            html += '<th>Recipient Name</th>';
            html += '<th>Status</th>';
            html += '<th>Date Sent</th>';
            html += '<th>Date Delivered</th>';
            html += '<th>Notes</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            $.each(data, function(index, row) {
                html += '<tr>';
                html += '<td data-label="Tracking Code"><strong>' + escapeHtml(row.tracking_code || '-') + '</strong></td>';
                html += '<td data-label="Recipient Name">' + escapeHtml(row.recipient_name || '-') + '</td>';
                html += '<td data-label="Status">' + formatStatus(row.status) + '</td>';
                html += '<td data-label="Date Sent">' + escapeHtml(row.date_sent || '-') + '</td>';
                html += '<td data-label="Date Delivered">' + escapeHtml(row.date_delivered || '-') + '</td>';
                html += '<td data-label="Notes">' + escapeHtml(row.notes || '-') + '</td>';
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

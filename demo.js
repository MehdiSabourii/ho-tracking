/**
 * Demo JavaScript for HO Tracking GitHub Pages
 * Static implementation with sample data
 */

(function () {
    'use strict';

    // Sample tracking data
    const sampleData = [
        {
            id: 1,
            tracking_code: 'TR123456789',
            recipient_name: 'علی احمدی',
            status: 'تحویل شده',
            date_sent: '1402/10/15',
            date_delivered: '1402/10/18',
            notes: 'مرسوله با موفقیت تحویل داده شد'
        },
        {
            id: 2,
            tracking_code: 'TR987654321',
            recipient_name: 'سارا محمدی',
            status: 'در حال ارسال',
            date_sent: '1402/10/20',
            date_delivered: '-',
            notes: 'در مسیر به مقصد'
        },
        {
            id: 3,
            tracking_code: 'TR456789123',
            recipient_name: 'رضا کریمی',
            status: 'در انتظار ارسال',
            date_sent: '1402/10/22',
            date_delivered: '-',
            notes: 'در انتظار ارسال'
        },
        {
            id: 4,
            tracking_code: 'TR111222333',
            recipient_name: 'فاطمه رضایی',
            status: 'تحویل شده',
            date_sent: '1402/10/12',
            date_delivered: '1402/10/14',
            notes: 'تحویل در محل'
        },
        {
            id: 5,
            tracking_code: 'TR444555666',
            recipient_name: 'محمد حسینی',
            status: 'در حال ارسال',
            date_sent: '1402/10/21',
            date_delivered: '-',
            notes: 'رسیده به مرکز توزیع'
        },
        {
            id: 6,
            tracking_code: 'TR777888999',
            recipient_name: 'مریم صادقی',
            status: 'تحویل شده',
            date_sent: '1402/10/10',
            date_delivered: '1402/10/13',
            notes: 'تحویل به گیرنده'
        }
    ];

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.getElementById('ho-tracking-search-form');
        const searchInput = document.getElementById('tracking-search-input');
        const loadingDiv = document.getElementById('tracking-loading');
        const noResultsDiv = document.getElementById('tracking-no-results');
        const tableContainer = document.getElementById('tracking-table-container');

        // Load all records on page load
        performSearch('');

        // Handle search form submission
        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const searchTerm = searchInput.value.trim();
                performSearch(searchTerm);
            });
        }

        // Real-time search as user types (with debouncing)
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    const searchTerm = searchInput.value.trim();
                    performSearch(searchTerm);
                }, 500); // Wait 500ms after user stops typing
            });
        }

        function performSearch(searchTerm) {
            // Show loading
            loadingDiv.style.display = 'block';
            noResultsDiv.style.display = 'none';
            tableContainer.style.display = 'none';

            // Simulate network delay for realistic demo
            setTimeout(function () {
                loadingDiv.style.display = 'none';

                // Filter data based on search term
                let results = sampleData;
                if (searchTerm) {
                    results = sampleData.filter(function (item) {
                        const searchLower = searchTerm.toLowerCase();
                        return (
                            item.tracking_code.toLowerCase().includes(searchLower) ||
                            item.recipient_name.toLowerCase().includes(searchLower) ||
                            item.status.toLowerCase().includes(searchLower)
                        );
                    });
                }

                if (results.length > 0) {
                    displayResults(results);
                } else {
                    noResultsDiv.innerHTML = '<p>هیچ اطلاعات رهگیری یافت نشد. لطفاً کد رهگیری خود را بررسی کنید.</p>';
                    noResultsDiv.style.display = 'block';
                    tableContainer.style.display = 'none';
                }
            }, 300); // 300ms delay
        }

        function displayResults(data) {
            const visibleColumns = ['tracking_code', 'recipient_name', 'status', 'date_sent', 'date_delivered', 'notes'];

            // Define column labels in both English and Persian
            const columnLabels = {
                'tracking_code': 'کد رهگیری',
                'recipient_name': 'نام گیرنده',
                'status': 'وضعیت',
                'date_sent': 'تاریخ ارسال',
                'date_delivered': 'تاریخ تحویل',
                'notes': 'توضیحات'
            };

            let html = '<table class="tracking-table">';
            html += '<thead>';
            html += '<tr>';

            // Add headers for visible columns
            visibleColumns.forEach(function (column) {
                if (columnLabels[column]) {
                    html += '<th>' + escapeHtml(columnLabels[column]) + '</th>';
                }
            });

            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            data.forEach(function (row) {
                html += '<tr>';

                visibleColumns.forEach(function (column) {
                    if (columnLabels[column]) {
                        const label = columnLabels[column];
                        let value = '';

                        switch (column) {
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

            tableContainer.innerHTML = html;
            tableContainer.style.display = 'block';
        }

        function formatStatus(status) {
            if (!status) {
                return '-';
            }

            const statusLower = status.toLowerCase();
            let statusClass = 'status-badge';

            if (statusLower.includes('تحویل شده') || statusLower.includes('delivered')) {
                statusClass += ' status-delivered';
            } else if (statusLower.includes('در حال ارسال') || statusLower.includes('transit')) {
                statusClass += ' status-in-transit';
            } else {
                statusClass += ' status-pending';
            }

            return '<span class="' + statusClass + '">' + escapeHtml(status) + '</span>';
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, function (m) { return map[m]; });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    });

})();

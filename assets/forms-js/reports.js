$(document).ready(function () {
    
    // Load Most Borrowed Books
    function loadMostBorrowedBooks() {
        $.ajax({
            type: "POST",
            url: "services/ajax_functions.php",
            data: { action: "get_most_borrowed_books" },
            dataType: "json",
            success: function (data) {
                if (data.success) {
                    let html = '';
                    data.books.forEach(function(book) {
                        html += `<tr>
                            <td>${book.title}</td>
                            <td>${book.author}</td>
                            <td>${book.isbn || 'N/A'}</td>
                            <td>${book.category}</td>
                            <td><span class="badge bg-primary">${book.borrow_count}</span></td>
                            <td>${book.available_qty}/${book.qty}</td>
                        </tr>`;
                    });
                    $("#most-borrowed-tbody").html(html);
                } else {
                    $("#most-borrowed-tbody").html('<tr><td colspan="6" class="text-center">No data available</td></tr>');
                }
            },
            error: function() {
                $("#most-borrowed-tbody").html('<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>');
            }
        });
    }

    // Load Active Borrowers
    function loadActiveBorrowers() {
        $.ajax({
            type: "POST",
            url: "services/ajax_functions.php",
            data: { action: "get_active_borrowers" },
            dataType: "json",
            success: function (data) {
                if (data.success) {
                    let html = '';
                    data.borrowers.forEach(function(borrower) {
                        html += `<tr>
                            <td>${borrower.username}</td>
                            <td>${borrower.email}</td>
                            <td><span class="badge bg-info">${borrower.membership_type}</span></td>
                            <td>${borrower.total_borrowed}</td>
                            <td><span class="badge bg-warning">${borrower.currently_borrowed}</span></td>
                            <td>${borrower.total_returned}</td>
                        </tr>`;
                    });
                    $("#active-borrowers-tbody").html(html);
                } else {
                    $("#active-borrowers-tbody").html('<tr><td colspan="6" class="text-center">No data available</td></tr>');
                }
            },
            error: function() {
                $("#active-borrowers-tbody").html('<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>');
            }
        });
    }

    // Load Overdue Books
    function loadOverdueBooks() {
        $.ajax({
            type: "POST",
            url: "services/ajax_functions.php",
            data: { action: "get_overdue_books" },
            dataType: "json",
            success: function (data) {
                if (data.success) {
                    let html = '';
                    data.overdue.forEach(function(item) {
                        html += `<tr>
                            <td>${item.title}</td>
                            <td>${item.username}</td>
                            <td><span class="badge bg-info">${item.membership_type}</span></td>
                            <td>${item.due_date}</td>
                            <td><span class="badge bg-danger">${item.days_overdue} days</span></td>
                            <td>LKR ${parseFloat(item.fine_amount || 0).toFixed(2)}</td>
                        </tr>`;
                    });
                    $("#overdue-books-tbody").html(html);
                } else {
                    $("#overdue-books-tbody").html('<tr><td colspan="6" class="text-center">No data available</td></tr>');
                }
            },
            error: function() {
                $("#overdue-books-tbody").html('<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>');
            }
        });
    }

    // Load Fine Collection
    function loadFineCollection() {
        $.ajax({
            type: "POST",
            url: "services/ajax_functions.php",
            data: { action: "get_fine_collection" },
            dataType: "json",
            success: function (data) {
                if (data.success) {
                    let html = '';
                    data.fines.forEach(function(fine) {
                        let statusClass = fine.fine_status == 'paid' ? 'success' : 'warning';
                        let paymentClass = fine.payment_status == 'completed' ? 'success' : 'secondary';
                        html += `<tr>
                            <td>${fine.username}</td>
                            <td>${fine.book_title}</td>
                            <td>LKR ${parseFloat(fine.amount).toFixed(2)}</td>
                            <td><span class="badge bg-${statusClass}">${fine.fine_status}</span></td>
                            <td><span class="badge bg-${paymentClass}">${fine.payment_status || 'pending'}</span></td>
                            <td>${fine.fine_created_at}</td>
                        </tr>`;
                    });
                    $("#fine-collection-tbody").html(html);
                } else {
                    $("#fine-collection-tbody").html('<tr><td colspan="6" class="text-center">No data available</td></tr>');
                }
            },
            error: function() {
                $("#fine-collection-tbody").html('<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>');
            }
        });
    }

    // Load Category Statistics
    function loadCategoryStats() {
        $.ajax({
            type: "POST",
            url: "services/ajax_functions.php",
            data: { action: "get_category_stats" },
            dataType: "json",
            success: function (data) {
                if (data.success) {
                    let html = '';
                    data.categories.forEach(function(cat) {
                        html += `<tr>
                            <td><strong>${cat.category}</strong></td>
                            <td>${cat.total_books}</td>
                            <td>${cat.total_copies}</td>
                            <td><span class="badge bg-success">${cat.available_copies}</span></td>
                            <td><span class="badge bg-warning">${cat.borrowed_copies}</span></td>
                        </tr>`;
                    });
                    $("#category-stats-tbody").html(html);
                } else {
                    $("#category-stats-tbody").html('<tr><td colspan="5" class="text-center">No data available</td></tr>');
                }
            },
            error: function() {
                $("#category-stats-tbody").html('<tr><td colspan="5" class="text-center text-danger">Error loading data</td></tr>');
            }
        });
    }

    // Load initial data
    loadMostBorrowedBooks();

    // Tab change event
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).data('bs-target');
        
        switch(target) {
            case '#most-borrowed':
                loadMostBorrowedBooks();
                break;
            case '#active-borrowers':
                loadActiveBorrowers();
                break;
            case '#overdue-books':
                loadOverdueBooks();
                break;
            case '#fine-collection':
                loadFineCollection();
                break;
            case '#category-stats':
                loadCategoryStats();
                break;
        }
    });
});

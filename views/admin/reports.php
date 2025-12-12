<?php

require_once BASE_PATH . "/models/Reports.php";
require_once BASE_PATH . "/views/layouts/app.php";


function content()
{
    $reportsModel = new Reports();
    $dashboardStats = $reportsModel->getDashboardStats();
?>

    <div class="container-xxl container-p-y">

        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Reports</h4>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Total Books</h6>
                        <h3><?= $dashboardStats['books']['total_books'] ?? 0 ?></h3>
                        <small>Available: <?= $dashboardStats['books']['available_copies'] ?? 0 ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Total Users</h6>
                        <h3><?= $dashboardStats['users']['total_users'] ?? 0 ?></h3>
                        <small>Students: <?= $dashboardStats['users']['students'] ?? 0 ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Active Borrowings</h6>
                        <h3><?= $dashboardStats['borrowing']['active_borrowings'] ?? 0 ?></h3>
                        <small>Overdue: <?= $dashboardStats['borrowing']['overdue_borrowings'] ?? 0 ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Total Fines</h6>
                        <h3>LKR <?= number_format($dashboardStats['fines']['total_fine_amount'] ?? 0, 2) ?></h3>
                        <small>Pending: LKR <?= number_format($dashboardStats['fines']['pending_amount'] ?? 0, 2) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#most-borrowed" aria-controls="most-borrowed">Most Borrowed Books</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#active-borrowers" aria-controls="active-borrowers">Active Borrowers</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#overdue-books" aria-controls="overdue-books">Overdue Books</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#fine-collection" aria-controls="fine-collection">Fine Collection</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#category-stats" aria-controls="category-stats">Category Statistics</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Most Borrowed Books -->
            <div class="tab-pane fade show active" id="most-borrowed" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-body table-responsive">
                        <h5>Most Borrowed Books</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Category</th>
                                    <th>Borrow Count</th>
                                    <th>Available/Total</th>
                                </tr>
                            </thead>
                            <tbody id="most-borrowed-tbody">
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Active Borrowers -->
            <div class="tab-pane fade" id="active-borrowers" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-body table-responsive">
                        <h5>Active Borrowers</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Membership Type</th>
                                    <th>Total Borrowed</th>
                                    <th>Currently Borrowed</th>
                                    <th>Returned</th>
                                </tr>
                            </thead>
                            <tbody id="active-borrowers-tbody">
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Overdue Books -->
            <div class="tab-pane fade" id="overdue-books" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-body table-responsive">
                        <h5>Overdue Books</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>User</th>
                                    <th>Membership</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Fine Amount</th>
                                </tr>
                            </thead>
                            <tbody id="overdue-books-tbody">
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Fine Collection -->
            <div class="tab-pane fade" id="fine-collection" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-body table-responsive">
                        <h5>Fine Collection Report</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Book</th>
                                    <th>Fine Amount</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody id="fine-collection-tbody">
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Category Statistics -->
            <div class="tab-pane fade" id="category-stats" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-body table-responsive">
                        <h5>Books by Category</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Books</th>
                                    <th>Total Copies</th>
                                    <th>Available</th>
                                    <th>Borrowed</th>
                                </tr>
                            </thead>
                            <tbody id="category-stats-tbody">
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5" />

    </div>

    <script src="<?= asset('forms-js/reports.js') ?>"></script>

<?php
}
?>

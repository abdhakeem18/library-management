<?php

require_once BASE_PATH . "/models/borrow.php";
require_once BASE_PATH . "/models/book.php";
require_once BASE_PATH . "/models/User.php";
require_once BASE_PATH . "/views/layouts/app.php";


function content()
{

    $userModel = new User();
    $users = $userModel->getAll();

    $booksModel = new book();
    $books = $booksModel->getAll();

    $borrowModel = new borrow("DESC", "status");
    
    // Check and update overdue borrowings with membership-based fines
    $borrowModel->updateOverdueBorrowings();
    
    $data = $borrowModel->getBorrowWithBookAndUser();

?>

    <div class="container-xxl container-p-y">

        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> borrowing

            <button
                type="button"
                class="btn btn-primary float-end"
                data-bs-toggle="modal"
                data-bs-target="#createBorrowed">
                Add borrowed book
            </button>
        </h4>

        <div class="card">
            <h5 class="card-header">Users</h5>
            <div class="m-4">
                <div id="delete-alert-container"></div>
            </div>
            <div class="table-responsive text-nowrap" style="min-height: 200px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Book Name</th>
                            <th>Member Name</th>
                            <th>borrow_date</th>
                            <th>due_date</th>
                            <th>return_date</th>
                            <th>Fine Amount</th>
                            <th>status</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php
                        if (is_array($data) && count($data) > 0) {
                            foreach ($data as $key => $borrow) {
                        ?>
                                <tr>
                                    <td><i class="fab fa-angular fa-lg text-danger me-3"></i> <strong><?= $borrow['id'] ?? '' ?></strong></td>
                                    <td><?= $borrow['title'] ?></td>
                                    <td><?= $borrow['username'] ?></td>
                                    <td> <?= $borrow['borrow_date'] ?></td>
                                    <td> <?= $borrow['due_date'] ?></td>
                                    <td> <?= $borrow['return_date'] ?? 'Not Returned' ?></td>
                                    <td>
                                        <?php 
                                        $fineAmount = floatval($borrow['fine_amount'] ?? 0);
                                        if ($fineAmount > 0) {
                                            echo '<span class="text-danger fw-bold">LKR ' . number_format($fineAmount, 2) . '</span>';
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = strtolower($borrow['status']);
                                        if ($status == 'borrowed') {
                                            echo '<span class="badge bg-warning">Borrowed</span>';
                                        } elseif ($status == 'overdue') {
                                            echo '<span class="badge bg-danger">Overdue</span>';
                                        } elseif ($status == 'returned') {
                                            echo '<span class="badge bg-success">Returned</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Allow edit for both borrowed and overdue status
                                        if ($borrow['status'] === "borrowed" || $borrow['status'] === "overdue") {
                                        ?>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item edit-borrow-btn" data-id="<?= $borrow['id']; ?>"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="alert alert-warning">
                                        No borrowing records found.
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->

        <hr class="my-5" />
    </div>

    <div class="modal fade" id="createBorrowed" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="create-form" action="<?= url('services/ajax_functions.php') ?>" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCenterTitle">Add borrowing book</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create-borrow">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameWithTitle" class="form-label">Book Name</label>

                                <select name="book_id" id="" class="form-control" required>
                                    <?php
                                    foreach ($books as $key => $book) {
                                        // dd($book);
                                        echo "<option value='{$book['id']}'>{$book['title']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row ">
                            <div class="col mb-3">
                                <label for="emailWithTitle" class="form-label">Member Name</label>
                                <select name="member_no" id="" class="form-control" required>
                                    <?php
                                    foreach ($users as $key => $user) {
                                        // dd($user);
                                        echo "<option value='{$user['id']}'>{$user['username']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="row pr-0">
                                <div class="col mb-3 pr-0">
                                    <label for="emailWithTitle" class="form-label">Borrow Date</label>
                                    <input
                                        required
                                        type="date"
                                        name="borrow_date"
                                        id=""
                                        class="form-control"
                                        min="<?= date('Y-m-d'); ?>" />
                                </div>
                            </div>

                            <div class="row pr-0">
                                <div class="col mb-3 pr-0">
                                    <label for="emailWithTitle" class="form-label">Due Date</label>
                                    <input
                                        required
                                        type="date"
                                        name="due_date"
                                        id=""
                                        class="form-control"
                                        min="<?= date('Y-m-d', strtotime('+5 days')); ?>" />
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <div id="additional-fields">
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <div id="alert-container"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="button" class="btn btn-primary" id="create">add</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    </div>
    <!--update modal  -->

    <div class="modal fade" id="edit-borrow-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="edit-form" action="<?= url('services/ajax_functions.php') ?>" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCenterTitle">edit borrowing book</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit-borrow">
                        <input type="hidden" id="borrow_id" name="id" value="">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameWithTitle" class="form-label">Book Name</label>

                                <select name="book_id" id="book_id" class="form-control" required>
                                    <?php
                                    foreach ($books as $key => $book) {
                                        // dd($book);
                                        echo "<option value='{$book['id']}'>{$book['title']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col mb-3">
                            <label for="emailWithTitle" class="form-label">Member Name</label>
                            <select name="member_no" id="member_no" class="form-control" required>
                                <?php
                                foreach ($users as $key => $user) {
                                    // dd($user);
                                    echo "<option value='{$user['id']}'>{$user['username']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row pr-0">


                            <div class="col mb-3 pr-0">
                                <label for="dateWithTitle" class="form-label">Due Date</label>
                                <input
                                    required
                                    type="date"
                                    name="due_date"
                                    id="due_date"
                                    class="form-control" />
                            </div>
                            <div class="col mb-3 pr-0">
                                <label for="dateWithTitle" class="form-label">Retuen Date</label>
                                <input

                                    type="date"
                                    name="retrun_date"
                                    id=""
                                    class="form-control"
                                    <?= date('Y-m-d'); ?> />
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <div id="additional-fields">
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <div id="alert-container"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="button" class="btn btn-primary" id="update-borrow">Update</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
    </div>
    <script src="<?= asset('forms-js/borrow.js') ?>"></script>

<?php
}
?>
<?php

require_once BASE_PATH . "/models/Reservation.php";
require_once BASE_PATH . "/views/layouts/app.php";


function content()
{
    $reservationModel = new Reservation();
    $data = $reservationModel->getAllReservations();
?>

    <div class="container-xxl container-p-y">

        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Reservations

            <button
                type="button"
                class="btn btn-primary float-end"
                data-bs-toggle="modal"
                data-bs-target="#create-reservation-modal">
                Add New Reservation
            </button>
        </h4>

        <div class="row m-3">
            <div class="col-6">
                <div class="d-flex align-items-center m-3">
                    <input type="text" id="searchInput" class="form-control border-0 shadow-none" placeholder="Search Book Name  " aria-label="Search..." />
                </div>
            </div>
            <div class="col-2">
                <div class="form-group my-3">
                    <button class="btn btn-outline-danger d-inline" id="clear">x</button>
                </div>
            </div>
        </div>
        <hr>

        <!-- Bootstrap Table -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Book Title</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Reservation Date</th>
                        <th>Status</th>
                        <th>Notified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (is_array($data) && count($data) > 0) {
                        foreach ($data as $key => $reservation) {
                            $statusClass = $reservation['status'] == 'active' ? 'success' : ($reservation['status'] == 'fulfilled' ? 'info' : 'secondary');
                    ?>
                            <tr>
                                <td><?= $reservation['id'] ?? '' ?></td>
                                <td><strong><?= $reservation['book_title'] ?? '' ?></strong></td>
                                <td><?= $reservation['username'] ?? '' ?></td>
                                <td><?= $reservation['email'] ?? '' ?></td>
                                <td><?= date('M d, Y', strtotime($reservation['reservation_date'])) ?? '' ?></td>
                                <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($reservation['status']) ?? '' ?></span></td>
                                <td><?= $reservation['notified'] ? '<i class="bx bx-check-circle text-success"></i>' : '<i class="bx bx-x-circle text-danger"></i>' ?></td>
                                <td>
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <?php if ($reservation['status'] == 'active') { ?>
                                            <a class="dropdown-item fulfill-reservation-btn" data-id="<?= $reservation['id']; ?>"><i class="bx bx-check me-1"></i> Fulfill</a>
                                            <a class="dropdown-item cancel-reservation-btn" data-id="<?= $reservation['id']; ?>"><i class="bx bx-x me-1"></i> Cancel</a>
                                        <?php } ?>
                                        <a class="dropdown-item delete-reservation-btn" data-id="<?= $reservation['id']; ?>"><i class="bx bx-trash me-1"></i> Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-warning">
                                    No reservations found.
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <hr class="my-5" />

    </div>

    <!-- Create Reservation Modal -->
    <div class="modal fade" id="create-reservation-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="create-reservation-form" action="services/ajax_functions.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Reservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_reservation">

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Book</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="book_id" required>
                                    <option value="">Select Book</option>
                                    <?php
                                    require_once BASE_PATH . "/models/book.php";
                                    $bookModel = new book();
                                    $books = $bookModel->getAll();
                                    foreach ($books as $book) {
                                        echo "<option value='{$book['id']}'>{$book['title']} - {$book['author']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">User</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="user_id" required>
                                    <option value="">Select User</option>
                                    <?php
                                    require_once BASE_PATH . "/models/User.php";
                                    $userModel = new User();
                                    $users = $userModel->getAll();
                                    foreach ($users as $user) {
                                        if ($user['permission'] != 'admin') {
                                            echo "<option value='{$user['id']}'>{$user['username']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="alert-container"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= asset('forms-js/reservation.js') ?>"></script>

    <!-- search bar script -->
    <script>
        $(document).ready(function() {
            $("#searchInput").on("input", function() {
                var searchTerm = $(this).val().toLowerCase();

                $("tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
                });
            });

            $('#clear').on('click', function() {
                $("#searchInput").val('');
                $("tbody tr").show();
            });
        });
    </script>

<?php
}
?>
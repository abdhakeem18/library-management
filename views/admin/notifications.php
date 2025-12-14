<?php

require_once BASE_PATH . "/models/Notification.php";
require_once BASE_PATH . "/views/layouts/app.php";


function content()
{
    $notificationModel = new Notification();
    $data = $notificationModel->getAllNotifications();
?>

    <div class="container-xxl container-p-y">

        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Notifications

            <button
                type="button"
                class="btn btn-primary float-end"
                data-bs-toggle="modal"
                data-bs-target="#create-notification-modal">
                Send Notification
            </button>
        </h4>

        <div class="row m-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Total Notifications</h6>
                        <h3><?= is_array($data) ? count($data) : 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6>Unread</h6>
                        <h3><?= is_array($data) ? count(array_filter($data, fn($n) => !$n['is_read'])) : 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>

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
                        <th>User</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (is_array($data) && count($data) > 0) {
                        foreach ($data as $key => $notification) {
                            $typeClass = $notification['type'] == 'overdue' ? 'danger' : ($notification['type'] == 'due_date' ? 'warning' : 'info');
                            $readClass = $notification['is_read'] ? 'success' : 'secondary';
                    ?>
                            <tr class="<?= !$notification['is_read'] ? 'table-active' : '' ?>">
                                <td><?= $notification['id'] ?? '' ?></td>
                                <td><?= $notification['username'] ?? '' ?></td>
                                <td><span class="badge bg-<?= $typeClass ?>"><?= str_replace('_', ' ', ucfirst($notification['type'])) ?? '' ?></span></td>
                                <td><?= substr($notification['message'], 0, 50) ?? '' ?>...</td>
                                <td><span class="badge bg-<?= $readClass ?>"><?= $notification['is_read'] ? 'Read' : 'Unread' ?></span></td>
                                <td><?= date('M d, Y H:i', strtotime($notification['created_at'])) ?? '' ?></td>
                                <td>
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <?php if (!$notification['is_read']) { ?>
                                            <a class="dropdown-item mark-read-btn" data-id="<?= $notification['id']; ?>"><i class="bx bx-check me-1"></i> Mark as Read</a>
                                        <?php } ?>
                                        <a class="dropdown-item view-notification-btn" data-id="<?= $notification['id']; ?>" data-message="<?= htmlspecialchars($notification['message']) ?>"><i class="bx bx-show me-1"></i> View</a>
                                        <a class="dropdown-item delete-notification-btn" data-id="<?= $notification['id']; ?>"><i class="bx bx-trash me-1"></i> Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert alert-warning">
                                    No notifications found.
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <hr class="my-5" />

        <!-- Alert container for page-level messages -->
        <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    </div>

    <!-- Create Notification Modal -->
    <div class="modal fade" id="create-notification-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="create-notification-form" action="<?= url('services/ajax_functions.php') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_notification">

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
                                        echo "<option value='{$user['id']}'>{$user['username']} - {$user['email']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Type</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="type" required>
                                    <option value="general">General</option>
                                    <option value="due_date">Due Date Reminder</option>
                                    <option value="overdue">Overdue Notice</option>
                                    <option value="reservation_available">Reservation Available</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Message</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="message" rows="4" required></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div id="alert-container"></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send Notification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Notification Modal -->
    <div class="modal fade" id="view-notification-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="notification-message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset('forms-js/notification.js') ?>"></script>

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
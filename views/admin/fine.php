<?php

require_once BASE_PATH . "/models/fine_fee.php";
require_once BASE_PATH . "/models/borrow.php";
require_once BASE_PATH . "/models/payment.php";
require_once BASE_PATH . "/views/layouts/app.php";


function content()
{

    $fine_feeModel = new fineFee();
    $data = $fine_feeModel->getAll("DESC", "status");

    $borrowModel = new borrow();
    $paymentModel = new Payment();
?>

    <div class="container-xxl container-p-y">

        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> fine_fee

            <!-- <button
            type="button"
            class="btn btn-primary float-end"
            data-bs-toggle="modal"
            data-bs-target="#createUser">
            Add fine
        </button> -->
        </h4>

        <div class="card">

            <!-- /.card-header -->
            <div class="card-body p-0 table-responsive" style="min-height: 200px;">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th class="">borrow id</th>
                            <th class="">Member Name</th>
                            <th class="">payed type</th>
                            <th class="">total</th>
                            <th class="">status</th>
                            <!-- <th class="text-center" style="width: 200px">Options</th> -->
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php
                        if (is_array($data) && count($data) > 0) {
                            foreach ($data as $key => $fine_fee) {
                        ?>
                                <tr>
                                    <td><?= $fine_fee['id']; ?></td>
                                    <td class="b_id"><?= $fine_fee['borrow_id']; ?></td>
                                    <td class="m_name"><?= $borrowModel->getBorrowMemberName($fine_fee['borrow_id']); ?></td>
                                    <td><?= $fine_fee['payment_id'] == 0 ? "" : $paymentModel->getPayedType($fine_fee['payment_id']); ?></td>
                                    <td class="total"><?= $fine_fee['amount']; ?></td>
                                    <td>
                                        <?php if ($fine_fee['status'] == "pending") { ?>
                                            <span class="badge bg-warning"><?= $fine_fee['status']; ?></span>
                                        <?php } else { ?>
                                            <span class="badge bg-success"><?= $fine_fee['status']; ?></span>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">

                                                <a class="dropdown-item edit-fine_fee-btn" data-id="<?= $fine_fee['id']; ?>"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                                <!-- <a class="dropdown-item delete-fine_fee-btn" data-id="<?= $fine_fee['id']; ?>"><i class="bx bx-trash me-1"></i> Delete</a> -->

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        } else {
                            ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-warning">
                                        No fines found.
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->

        <!-- Udpate fine -->

        <div class="modal fade" id="edit-fine_fee-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form id="update-form" action="<?= url('services/ajax_functions.php') ?>" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">member_no</h5>
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>Borrow ID: <label id="e_borrow_id"></label></div>
                            <div>Member Name: <label id="e_mem_name"></label></div>
                            <div class="mb-3">Amount: <label id="e_amount"></label></div>
                            <input type="hidden" name="action" value="edit_fine_fee">
                            <input type="hidden" id="fine_id" name="id" value="">
                            <input type="hidden" id="_amount" name="amount" value="">
                            <div class="row ">
                                <div class="mb-3">
                                    <label for="exampleFormControlSelect1" class="form-label">Pied Type</label>
                                    <select class="form-select" id="is_paid" aria-label="Default select example" id="pied_type" name="pied_type" required>
                                        <option value="card">card</option>
                                        <option value="Cash">Cash</option>
                                        <option value="by_admin">By Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col mb-3">
                                    <label for="nameWithTitle" class="form-label">additional</label>
                                    <textarea
                                        type="text"
                                        name="additional"
                                        id=""
                                        class="form-control"></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="button" class="btn btn-primary" id="update-fine_fee">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
        <script src="<?= asset('forms-js/fine.js') ?>"></script>
    <?php
}
    ?>
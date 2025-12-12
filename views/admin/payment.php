<?php

require_once BASE_PATH . "/models/Payment.php";
require_once BASE_PATH . "/models/fine_fee.php";
require_once BASE_PATH . "/models/borrow.php";
require_once BASE_PATH . "/views/layouts/app.php";


function content()
{

    $paymentModel = new Payment();
    $data = $paymentModel->getAll("DESC");

    $fineModel = new fineFee();
    $borrowModel = new borrow();

?>

    <div class="container-xxl container-p-y">

        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Payment
        </h4>

        <div class="card">

            <!-- /.card-header -->
            <div class="card-body p-0 table-responsive">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th class="">paid for</th>
                            <th class="">Member Name</th>
                            <th class="">payed type</th>
                            <th class="">additional</th>
                            <th class="">amount</th>
                            <th class="">created at</th>
                            <th class="">status</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php
                        if (is_array($data) && count($data) > 0) {
                            foreach ($data as $key => $payment) {
                                $fineId = $fineModel->getAllByColumnValue("payment_id", $payment['id']);

                        ?>
                                <tr>
                                    <td><?= $payment['id'] ?></td>
                                    <td><?= $payment['paid_for'] ?></td>
                                    <td><?= $borrowModel->getBorrowMemberName($fineId[0]['borrow_id']) ?></td>
                                    <td><?= $payment['payed_type'] ?></td>
                                    <td><?= $payment['additional'] ?></td>
                                    <td><?= $payment['amount'] ?></td>
                                    <td><?= $payment['created_at'] ?></td>
                                    <td><?= $payment['status'] ?></td>

                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-warning">
                                        No payments found.
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

<?php
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
<script src="<?= asset('forms-js/payment.js') ?>"></script>
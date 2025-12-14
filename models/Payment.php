<?php

require_once 'BaseModel.php';

class Payment extends BaseModel
{
    public $id;
    public $user_id;
    public $amount;
    public $payment_type;
    public $additional;
    public $status;
    public $created_at;

    protected function getTableName()
    {
        return "payment";
    }


    protected function addNewRec()
    {
        $param = array(
            ':user_id' => $this->user_id,
            ':amount' => $this->amount,
            ':payment_type' => $this->payment_type,
            ':status' => $this->status
        );
        
        try {
            return $this->pm->run(
                "INSERT INTO payment(user_id, amount, payment_type, status) 
                 VALUES(:user_id, :amount, :payment_type, :status)",
                $param
            );
            
            
        } catch (Exception $e) {
            error_log("Payment insert failed: " . $e->getMessage());
            return false;
        }
    }

    protected function updateRec()
    {

        $param = array(
            ':amount' => $this->amount,
            ':payment_type' => $this->payment_type,
            ':status' => $this->status,
            ':created_at' => $this->created_at,
            ':updated_at' => $this->updated_at,
        );
        return $this->pm->run(
            "UPDATE INTO
             payment
              SET 
                  name = :name, 
                  treatment_fee = :treatment_fee,
                  registration_fee = :registration_fee,
                  is_active = :is_active 
              WHERE id = :id",
            $param
        );
        // $result = $this->pm->run(
        //      "UPDATE 
        //      payments 
        //     SET 
        //         quantity = :quantity, 
        //          treatment_fee_paid = :treatment_fee_paid
        //      WHERE id = :id",
        //  );
    }

    function deletepayment($id)
    {
        $payment = new payment();
        $payment->deleteRec($id);

        if ($payment) {
            return true; // payment udapted successfully
        } else {
            return false; // payment update failed (likely due to database error)
        }
    }

    function updatepayment($amount, $payment_type, $status, $created_at, $updated_at) {}

    public function getPayedType($id)
    {
        $payment_type = $this->pm->run("SELECT payment.payment_type FROM payment WHERE id = $id");

        if(!empty($payment_type)){
            return ($payment_type[0]['payment_type']);
        }
        return "";
    }

    public function getLastInsertedPaymentId()
    {
        $result = $this->pm->run('SELECT MAX(id) as lastInsertedId FROM payment', null, true);
        return $result['lastInsertedId'] ?? 100;
    }

    public function getAllPaymentWithBorrowing()
    {
        return $this->pm->run(
            "SELECT payment.id, payment.user_id, payment.amount, payment.payment_type, payment.status, payment.created_at,
                    borrowing.id AS borrowing_id, borrowing.book_id, borrowing.borrow_date, borrowing.due_date, borrowing.return_date
             FROM payment
             LEFT JOIN fine_fee ON payment.id = fine_fee.payment_id
             LEFT JOIN borrowing ON fine_fee.borrow_id = borrowing.id
             ORDER BY payment.created_at DESC"
        );
    }
}

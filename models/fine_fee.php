<?php

require_once 'BaseModel.php';

class fineFee extends BaseModel
{
    public $id;
    public $borrow_id;
    public $payment_id;
    public $amount;
    public $status;

    protected function getTableName()
    {
        return "fine_fee";
    }

    protected function addNewRec()
    {
        $params = array(
            ':borrow_id' => $this->borrow_id,
            ':amount' => $this->amount
        );

        $result = $this->pm->run(
            "INSERT INTO 
                fine_fee(
                    borrow_id, 
                    amount
                )
            VALUES(
                :borrow_id, 
                :amount
                )",
            $params
        );

        //  Check the result and return success or failure accordingly
        return $result ? true : false;
    }

    protected function updateRec()
    {
        $params = array(
            ":id" => $this->id,
            ':payment_id' => $this->payment_id,
            ':status' => $this->status
        );

        $result = $this->pm->run(
            "UPDATE fine_fee
             SET 
             payment_id = :payment_id,
             status = :status
             WHERE id = :id",
            $params);

        return $result ? true : false;

    }

}

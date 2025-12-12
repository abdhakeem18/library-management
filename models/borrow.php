<?php

require_once 'BaseModel.php';

class borrow extends BaseModel
{
    public $id;
    public $book_id;
    public $user_id;
    public $member_no; // Keep for backward compatibility
    public $borrow_date;
    public $due_date;
    public $return_date;
    public $status;
    public $fine_amount;

    protected function getTableName()
    {
        return "borrowing";
    }

    public function getById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run(
            "SELECT *, 
            m.username AS member_name, 
            b.title AS book_name, 
            bb.id AS id 
     FROM borrowedbooks AS bb
     JOIN members AS m ON m.id = bb.user_id
     JOIN books AS b ON b.id = bb.book_id
     WHERE bb.id = :id;",
            $param,
            true
        );
    }


    protected function addNewRec()
    {
        $userId = $this->user_id ?? $this->member_no;

        $params = array(
            ':book_id' => $this->book_id,
            ':user_id' => $userId,
            ':borrow_date' => $this->borrow_date,
            ':due_date' => $this->due_date,
        );

        $result = $this->pm->run("INSERT INTO borrowing(book_id, user_id, borrow_date, due_date) 
        VALUES(:book_id, :user_id, :borrow_date, :due_date)", $params);

        return $result;
    }

    protected function updateRec()
    {
        $userId = $this->user_id ?? $this->member_no;

        $params = array(
            ':id' => $this->id,
            ':book_id' => $this->book_id,
            ':user_id' => $userId,
            ':return_date' => $this->return_date,
            ':due_date' => $this->due_date,
            ":status" => $this->status,
            ':fine_amount' => $this->fine_amount ?? 0
        );

        return $this->pm->run(
            "UPDATE borrowing
             SET 
             book_id = :book_id,
             user_id = :user_id,
             return_date = :return_date,
             due_date = :due_date,
             status = :status,
             fine_amount = :fine_amount
             WHERE id = :id",
            $params
        );
    }



    public function getborrowById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("SELECT * FROM " . $this->getTableName() . " WHERE id = :id", $param, true);
    }

    public function updateBorrow($id, $book_id, $member_no, $due_date, $return_date, $status, $fine_amount = 0)
    {
        $borrow = new borrow();
        $borrow->id = $id;
        $borrow->book_id = $book_id;
        $borrow->member_no = $member_no;
        $borrow->due_date = $due_date;
        if (!empty($return_date)) {
            $borrow->return_date = $return_date;
        }
        $borrow->status = $status;
        $borrow->fine_amount = $fine_amount;

        $borrow->updateRec();

        if ($borrow) {
            return true; 
        } else {
            return false; 
        }
    }

    public function getBorrowWithBookAndUser()
    {
        return $this->pm->run("
        SELECT borrow.*, books.title, users.username
        FROM " . $this->getTableName() . " AS borrow
        LEFT JOIN books ON books.id = borrow.book_id
        LEFT JOIN users ON users.id = borrow.user_id");
    }

    public function getBorrowWithFine($userId)
    {

        return $this->pm->run("
        SELECT borrow.id, borrow.user_id, fine.borrow_id, fine.status FROM " . $this->getTableName() . " AS borrow
        LEFT JOIN fine_fee AS fine ON fine.borrow_id = borrow.id WHERE borrow.user_id = $userId AND fine.status = 'Unpaid'");
    }


    public function getBorrowMemberName($id)
    {
        $borrowDetail = $this->pm->run("
        SELECT borrow.*, users.username
        FROM " . $this->getTableName() . " AS borrow
        LEFT JOIN users ON users.id = borrow.user_id WHERE borrow.id = $id");

        return ($borrowDetail[0]['username']);
    }

    /**
     * Check and update overdue borrowings
     * Calculate late fees based on membership type:
     * - Students: LKR 50/day
     * - Faculty: LKR 20/day
     * - Guests: LKR 100/day
     */
    public function updateOverdueBorrowings()
    {
        // Get all borrowed items 
        $overdueBooks = $this->pm->run("
            SELECT b.id, b.due_date, b.fine_amount, b.user_id, u.membership_type
            FROM " . $this->getTableName() . " AS b
            JOIN users AS u ON u.id = b.user_id
            WHERE b.status = 'borrowed' 
            AND b.due_date < CURDATE()
        ");

        if (is_array($overdueBooks) && !empty($overdueBooks)) {
            foreach ($overdueBooks as $book) {
                // Calculate days overdue
                $dueDate = new DateTime($book['due_date']);
                $today = new DateTime();
                $daysOverdue = $today->diff($dueDate)->days;
                
                // Determine fine rate based on membership type
                $finePerDay = 50.00; // Default (Student)
                $membershipType = strtolower($book['membership_type'] ?? 'student');
                
                if ($membershipType === 'faculty') {
                    $finePerDay = 20.00;
                } elseif ($membershipType === 'guest') {
                    $finePerDay = 100.00;
                } 
                
                // Calculate fine
                $fine = $daysOverdue * $finePerDay;
                
                // Update status to overdue and set fine amount
                $this->pm->run("
                    UPDATE " . $this->getTableName() . " 
                    SET status = 'overdue', 
                        fine_amount = :fine 
                    WHERE id = :id
                ", [
                    ':fine' => $fine,
                    ':id' => $book['id']
                ]);
            }
        }
    }
}

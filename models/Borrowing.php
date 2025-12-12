<?php

require_once 'BaseModel.php';
require_once 'patterns/FineStrategy.php';

class Borrowing extends BaseModel
{
    public $id;
    public $book_id;
    public $user_id;
    public $borrow_date;
    public $due_date;
    public $return_date;
    public $status;
    public $fine_amount;

    protected function getTableName()
    {
        return 'borrowing';
    }

    protected function addNewRec()
    {
        $param = array(
            ':book_id' => $this->book_id,
            ':user_id' => $this->user_id,
            ':borrow_date' => $this->borrow_date,
            ':due_date' => $this->due_date,
            ':status' => $this->status ?? 'borrowed'
        );
        
        return $this->pm->run(
            "INSERT INTO borrowing (book_id, user_id, borrow_date, due_date, status) 
             VALUES (:book_id, :user_id, :borrow_date, :due_date, :status)",
            $param
        );
    }

    protected function updateRec()
    {
        $param = array(
            ':return_date' => $this->return_date,
            ':status' => $this->status,
            ':fine_amount' => $this->fine_amount ?? 0,
            ':id' => $this->id
        );
        
        return $this->pm->run(
            "UPDATE borrowing 
             SET return_date = :return_date, 
                 status = :status, 
                 fine_amount = :fine_amount 
             WHERE id = :id",
            $param
        );
    }

    public function update()
    {
        return $this->updateRec();
    }

    public function getLastId()
    {
        $result = $this->pm->run("SELECT LAST_INSERT_ID() as id", array(), true);
        return $result['id'] ?? null;
    }

    public function __construct()
    {
        parent::__construct();
    }

    // Strategy Pattern - Get fine calculation strategy based on membership type
    public function getFineStrategy($membershipType)
    {
        $fineContext = new FineContext();
        
        switch ($membershipType) {
            case 'Student':
                $fineContext->setStrategy(new StudentFineStrategy());
                break;
            case 'Faculty':
                $fineContext->setStrategy(new FacultyFineStrategy());
                break;
            case 'Guest':
                $fineContext->setStrategy(new GuestFineStrategy());
                break;
            default:
                $fineContext->setStrategy(new GuestFineStrategy());
        }
        
        return $fineContext;
    }

    public function getUserWithMembershipType($userId)
    {
        $param = array(':user_id' => $userId);
        return $this->pm->run(
            "SELECT id, fullname, email, membership_type, borrowing_limit 
             FROM users 
             WHERE id = :user_id",
            $param,
            true
        );
    }

    public function save()
    {
        return $this->addNewRec();
    }

    public function getBorrowingById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run(
            "SELECT br.*, b.title as book_title, b.author, b.isbn, 
                   u.fullname, u.email, u.membership_type 
             FROM borrowing br 
             JOIN books b ON br.book_id = b.id 
             JOIN users u ON br.user_id = u.id 
             WHERE br.id = :id",
            $param,
            true
        );
    }

    public function getAllBorrowings()
    {
        return $this->pm->run(
            "SELECT br.*, b.title as book_title, b.author, u.fullname, u.email 
             FROM borrowing br 
             JOIN books b ON br.book_id = b.id 
             JOIN users u ON br.user_id = u.id 
             ORDER BY br.borrow_date DESC"
        );
    }

    public function getByUserId($userId)
    {
        $param = array(':user_id' => $userId);
        return $this->pm->run(
            "SELECT br.*, b.title as book_title, b.author, b.isbn 
             FROM borrowing br 
             JOIN books b ON br.book_id = b.id 
             WHERE br.user_id = :user_id 
             ORDER BY br.borrow_date DESC",
            $param
        );
    }

    public function getActiveBorrowings($userId = null)
    {
        if ($userId) {
            $param = array(':user_id' => $userId);
            return $this->pm->run(
                "SELECT br.*, b.title as book_title, b.author, b.isbn, 
                       u.fullname, u.email, u.membership_type 
                 FROM borrowing br 
                 JOIN books b ON br.book_id = b.id 
                 JOIN users u ON br.user_id = u.id 
                 WHERE br.status = 'borrowed' AND br.user_id = :user_id
                 ORDER BY br.due_date ASC",
                $param
            );
        } else {
            return $this->pm->run(
                "SELECT br.*, b.title as book_title, b.author, b.isbn, 
                       u.fullname, u.email, u.membership_type 
                 FROM borrowing br 
                 JOIN books b ON br.book_id = b.id 
                 JOIN users u ON br.user_id = u.id 
                 WHERE br.status = 'borrowed'
                 ORDER BY br.due_date ASC"
            );
        }
    }

    public function getOverdueBorrowings()
    {
        return $this->pm->run(
            "SELECT br.*, b.title as book_title, b.author, 
                   u.fullname, u.email, u.contact_number, u.membership_type,
                   DATEDIFF(NOW(), br.due_date) as days_overdue 
             FROM borrowing br 
             JOIN books b ON br.book_id = b.id 
             JOIN users u ON br.user_id = u.id 
             WHERE br.status = 'borrowed' AND br.due_date < CURDATE() 
             ORDER BY days_overdue DESC"
        );
    }

    public function checkBorrowingLimit($userId)
    {
        $user = $this->getUserWithMembershipType($userId);
        
        $param = array(':user_id' => $userId);
        $result = $this->pm->run(
            "SELECT COUNT(*) as active_count 
             FROM borrowing 
             WHERE user_id = :user_id AND status = 'borrowed'",
            $param,
            true
        );
        
        return $result['active_count'] < $user['borrowing_limit'];
    }

    public function countActiveBorrowings($userId)
    {
        $param = array(':user_id' => $userId);
        $result = $this->pm->run(
            "SELECT COUNT(*) as count 
             FROM borrowing 
             WHERE user_id = :user_id AND status = 'borrowed'",
            $param,
            true
        );
        return $result['count'];
    }

    // Calculate fine using Strategy Pattern
    public function calculateFine($borrowId)
    {
        $borrow = $this->getBorrowingById($borrowId);
        
        if (!$borrow || $borrow['status'] != 'borrowed') {
            return 0;
        }

        $dueDate = strtotime($borrow['due_date']);
        $currentDate = strtotime(date('Y-m-d'));
        
        if ($currentDate <= $dueDate) {
            return 0;
        }

        $daysOverdue = floor(($currentDate - $dueDate) / (60 * 60 * 24));
        
        // Use Strategy Pattern
        $fineContext = $this->getFineStrategy($borrow['membership_type']);
        return $fineContext->calculateFine($daysOverdue);
    }

    public function deleteRec($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("DELETE FROM borrowing WHERE id = :id", $param);
    }
}

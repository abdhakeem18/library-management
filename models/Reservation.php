<?php

require_once 'BaseModel.php';

class Reservation extends BaseModel
{
    public $id;
    public $book_id;
    public $user_id;
    public $reservation_date;
    public $status;
    public $notified;

    protected function getTableName()
    {
        return 'reservations';
    }

    public function __construct()
    {
        parent::__construct();
    }

    protected function addNewRec()
    {
        $param = array(
            ':book_id' => $this->book_id,
            ':user_id' => $this->user_id,
            ':status' => $this->status ?? 'active'
        );
        
        try {
            $lastId = $this->pm->insertAndGetLastRowId(
                "INSERT INTO reservations (book_id, user_id, reservation_date, status, notified) 
                 VALUES (:book_id, :user_id, NOW(), :status, 0)",
                $param
            );
            
            if ($lastId > 0) {
                $this->id = $lastId;
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Reservation insert failed: " . $e->getMessage());
            return false;
        }
    }

    public function save()
    {
        if (isset($this->id) && $this->id > 0) {
            return $this->updateRec();
        } else {
            return $this->addNewRec();
        }
    }

    protected function updateRec()
    {
        $param = array(
            ':status' => $this->status,
            ':notified' => $this->notified ?? 0,
            ':id' => $this->id
        );
        
        try {
            $this->pm->run(
                "UPDATE reservations 
                 SET status = :status, notified = :notified 
                 WHERE id = :id",
                $param
            );
            return true;
        } catch (Exception $e) {
            error_log("Reservation update failed: " . $e->getMessage());
            return false;
        }
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

    public function getReservationById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run(
            "SELECT r.*, b.title as book_title, u.username, u.email 
             FROM reservations r 
             JOIN books b ON r.book_id = b.id 
             JOIN users u ON r.user_id = u.id 
             WHERE r.id = :id",
            $param,
            true
        );
    }

    public function getAllReservations()
    {
        return $this->pm->run(
            "SELECT r.*, b.title as book_title, u.username, u.email 
             FROM reservations r 
             JOIN books b ON r.book_id = b.id 
             JOIN users u ON r.user_id = u.id 
             ORDER BY r.reservation_date DESC"
        );
    }

    public function getByUserId($userId)
    {
        $param = array(':user_id' => $userId);
        return $this->pm->run(
            "SELECT r.*, b.title as book_title, b.author, b.isbn 
             FROM reservations r 
             JOIN books b ON r.book_id = b.id 
             WHERE r.user_id = :user_id 
             ORDER BY r.reservation_date DESC",
            $param
        );
    }

    public function getActiveReservations()
    {
        return $this->pm->run(
            "SELECT r.*, b.title as book_title, u.fullname, u.email 
             FROM reservations r 
             JOIN books b ON r.book_id = b.id 
             JOIN users u ON r.user_id = u.id 
             WHERE r.status = 'active' 
             ORDER BY r.reservation_date ASC"
        );
    }

    public function hasActiveReservations($bookId)
    {
        $param = array(':book_id' => $bookId);
        $result = $this->pm->run(
            "SELECT COUNT(*) as count 
             FROM reservations 
             WHERE book_id = :book_id AND status = 'active'",
            $param,
            true
        );
        return $result['count'] > 0;
    }

    public function getNextActiveReservation($bookId)
    {
        $param = array(':book_id' => $bookId);
        return $this->pm->run(
            "SELECT r.*, b.title as book_title, u.fullname, u.email 
             FROM reservations r 
             JOIN books b ON r.book_id = b.id 
             JOIN users u ON r.user_id = u.id 
             WHERE r.book_id = :book_id AND r.status = 'active' AND r.notified = 0 
             ORDER BY r.reservation_date ASC 
             LIMIT 1",
            $param,
            true
        );
    }

    public function fulfillReservation($reservationId)
    {
        $this->id = $reservationId;
        $this->status = 'fulfilled';
        $this->notified = 1;
        
        return $this->updateRec();
    }

    public function cancelReservation($reservationId)
    {
        $this->id = $reservationId;
        $this->status = 'cancelled';
        
        return $this->updateRec();
    }

    public function deleteRec($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("DELETE FROM reservations WHERE id = :id", $param);
    }

    public function getUserReservationForBook($userId, $bookId)
    {
        $param = array(':user_id' => $userId, ':book_id' => $bookId);
        return $this->pm->run(
            "SELECT * FROM reservations 
             WHERE user_id = :user_id AND book_id = :book_id AND status = 'active'",
            $param,
            true
        );
    }

    public function countActiveReservationsByUser($userId)
    {
        $param = array(':user_id' => $userId);
        $result = $this->pm->run(
            "SELECT COUNT(*) as count 
             FROM reservations 
             WHERE user_id = :user_id AND status = 'active'",
            $param,
            true
        );
        return $result['count'];
    }

    public function getReservationStats()
    {
        return $this->pm->run(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'fulfilled' THEN 1 ELSE 0 END) as fulfilled,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM reservations",
            array(),
            true
        );
    }
}

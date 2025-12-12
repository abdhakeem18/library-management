<?php

// Command Pattern for Library Actions

interface Command
{
    public function execute();
    public function undo();
    public function getLogInfo();
}

class BorrowBookCommand implements Command
{
    private $bookId;
    private $userId;
    private $borrowId;
    private $executed = false;

    public function __construct($bookId, $userId)
    {
        $this->bookId = $bookId;
        $this->userId = $userId;
    }

    public function execute()
    {
        require_once BASE_PATH . '/models/Borrowing.php';
        require_once BASE_PATH . '/models/book.php';
        
        $borrowing = new Borrowing();
        $book = new book();
        
        // Calculate due date based on membership type
        $user = $borrowing->getUserWithMembershipType($this->userId);
        $fineContext = $borrowing->getFineStrategy($user['membership_type']);
        $dueDays = $fineContext->getDueDateDays();
        
        $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));
        
        $borrowing->book_id = $this->bookId;
        $borrowing->user_id = $this->userId;
        $borrowing->borrow_date = date('Y-m-d');
        $borrowing->due_date = $dueDate;
        $borrowing->status = 'borrowed';
        
        if ($borrowing->save()) {
            // Get last insert ID from database
            $this->borrowId = $borrowing->getLastId();
            $this->executed = true;
            
            // Update book availability
            $book->decreaseAvailability($this->bookId);
            
            return true;
        }
        return false;
    }

    public function undo()
    {
        if (!$this->executed) {
            return false;
        }

        require_once BASE_PATH . '/models/Borrowing.php';
        require_once BASE_PATH . '/models/book.php';
        
        $borrowing = new Borrowing();
        $book = new book();
        
        // Delete the borrow record
        if ($borrowing->deleteRec($this->borrowId)) {
            // Restore book availability
            $book->increaseAvailability($this->bookId);
            $this->executed = false;
            return true;
        }
        return false;
    }

    public function getLogInfo()
    {
        return "Borrow Command - Book ID: {$this->bookId}, User ID: {$this->userId}, Executed: " . ($this->executed ? 'Yes' : 'No');
    }
}

class ReturnBookCommand implements Command
{
    private $borrowId;
    private $previousState;
    private $executed = false;

    public function __construct($borrowId)
    {
        $this->borrowId = $borrowId;
    }

    public function execute()
    {
        require_once BASE_PATH . '/models/Borrowing.php';
        require_once BASE_PATH . '/models/book.php';
        
        $borrowing = new Borrowing();
        $book = new book();
        
        // Get current borrow record
        $borrowRecord = $borrowing->getBorrowingById($this->borrowId);
        if (!$borrowRecord) {
            return false;
        }

        $this->previousState = $borrowRecord;
        
        // Update return date and status
        $borrowing->id = $this->borrowId;
        $borrowing->return_date = date('Y-m-d');
        $borrowing->status = 'returned';
        
        // Calculate fine if overdue
        $dueDate = strtotime($borrowRecord['due_date']);
        $returnDate = strtotime(date('Y-m-d'));
        
        if ($returnDate > $dueDate) {
            $daysOverdue = floor(($returnDate - $dueDate) / (60 * 60 * 24));
            
            // Get user's membership type for fine calculation
            $user = $borrowing->getUserWithMembershipType($borrowRecord['user_id']);
            $fineContext = $borrowing->getFineStrategy($user['membership_type']);
            $fineAmount = $fineContext->calculateFine($daysOverdue);
            
            $borrowing->fine_amount = $fineAmount;
        } else {
            $borrowing->fine_amount = 0;
        }
        
        if ($borrowing->update()) {
            // Increase book availability
            $book->increaseAvailability($borrowRecord['book_id']);
            $this->executed = true;
            
            // Check for reservations and notify
            $this->checkAndNotifyReservations($borrowRecord['book_id']);
            
            return true;
        }
        return false;
    }

    private function checkAndNotifyReservations($bookId)
    {
        require_once BASE_PATH . '/models/Reservation.php';
        require_once BASE_PATH . '/models/patterns/Observer.php';
        
        $reservation = new Reservation();
        $nextReservation = $reservation->getNextActiveReservation($bookId);
        
        if ($nextReservation) {
            $notificationManager = NotificationManager::getInstance();
            $notificationManager->notifyReservationAvailable(
                $nextReservation['user_id'],
                $nextReservation['book_title']
            );
        }
    }

    public function undo()
    {
        if (!$this->executed || !$this->previousState) {
            return false;
        }

        require_once BASE_PATH . '/models/Borrowing.php';
        require_once BASE_PATH . '/models/book.php';
        
        $borrowing = new Borrowing();
        $book = new book();
        
        // Restore previous state
        $borrowing->id = $this->borrowId;
        $borrowing->return_date = null;
        $borrowing->status = $this->previousState['status'];
        $borrowing->fine_amount = 0;
        
        if ($borrowing->update()) {
            // Decrease book availability
            $book->decreaseAvailability($this->previousState['book_id']);
            $this->executed = false;
            return true;
        }
        return false;
    }

    public function getLogInfo()
    {
        return "Return Command - Borrow ID: {$this->borrowId}, Executed: " . ($this->executed ? 'Yes' : 'No');
    }
}

class ReserveBookCommand implements Command
{
    private $bookId;
    private $userId;
    private $reservationId;
    private $executed = false;

    public function __construct($bookId, $userId)
    {
        $this->bookId = $bookId;
        $this->userId = $userId;
    }

    public function execute()
    {
        require_once BASE_PATH . '/models/Reservation.php';
        
        $reservation = new Reservation();
        $reservation->book_id = $this->bookId;
        $reservation->user_id = $this->userId;
        $reservation->status = 'active';
        
        if ($reservation->save()) {
            // Get last insert ID from database
            $this->reservationId = $reservation->getLastId();
            $this->executed = true;
            return true;
        }
        return false;
    }

    public function undo()
    {
        if (!$this->executed) {
            return false;
        }

        require_once BASE_PATH . '/models/Reservation.php';
        
        $reservation = new Reservation();
        if ($reservation->deleteRec($this->reservationId)) {
            $this->executed = false;
            return true;
        }
        return false;
    }

    public function getLogInfo()
    {
        return "Reserve Command - Book ID: {$this->bookId}, User ID: {$this->userId}, Executed: " . ($this->executed ? 'Yes' : 'No');
    }
}

class CancelReservationCommand implements Command
{
    private $reservationId;
    private $previousState;
    private $executed = false;

    public function __construct($reservationId)
    {
        $this->reservationId = $reservationId;
    }

    public function execute()
    {
        require_once BASE_PATH . '/models/Reservation.php';
        
        $reservation = new Reservation();
        $reservationRecord = $reservation->getById($this->reservationId);
        
        if (!$reservationRecord) {
            return false;
        }

        $this->previousState = $reservationRecord;
        
        $reservation->id = $this->reservationId;
        $reservation->status = 'cancelled';
        
        if ($reservation->update()) {
            $this->executed = true;
            return true;
        }
        return false;
    }

    public function undo()
    {
        if (!$this->executed || !$this->previousState) {
            return false;
        }

        require_once BASE_PATH . '/models/Reservation.php';
        
        $reservation = new Reservation();
        $reservation->id = $this->reservationId;
        $reservation->status = $this->previousState['status'];
        
        if ($reservation->update()) {
            $this->executed = false;
            return true;
        }
        return false;
    }

    public function getLogInfo()
    {
        return "Cancel Reservation Command - Reservation ID: {$this->reservationId}, Executed: " . ($this->executed ? 'Yes' : 'No');
    }
}

// Command Invoker with logging
class CommandInvoker
{
    private $commandHistory = [];
    private $currentIndex = -1;

    public function execute(Command $command)
    {
        if ($command->execute()) {
            // Clear redo history
            $this->commandHistory = array_slice($this->commandHistory, 0, $this->currentIndex + 1);
            
            // Add command to history
            $this->commandHistory[] = $command;
            $this->currentIndex++;
            
            // Log the command
            $this->logCommand($command);
            
            return true;
        }
        return false;
    }

    public function undo()
    {
        if ($this->currentIndex >= 0) {
            $command = $this->commandHistory[$this->currentIndex];
            if ($command->undo()) {
                $this->currentIndex--;
                error_log("Undo: " . $command->getLogInfo());
                return true;
            }
        }
        return false;
    }

    public function redo()
    {
        if ($this->currentIndex < count($this->commandHistory) - 1) {
            $this->currentIndex++;
            $command = $this->commandHistory[$this->currentIndex];
            if ($command->execute()) {
                error_log("Redo: " . $command->getLogInfo());
                return true;
            }
        }
        return false;
    }

    private function logCommand(Command $command)
    {
        error_log("Command executed: " . $command->getLogInfo());
    }

    public function getCommandHistory()
    {
        return $this->commandHistory;
    }
}

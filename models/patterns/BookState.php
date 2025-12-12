<?php

// State Pattern for Book Availability

interface BookState
{
    public function borrow($book);
    public function returnBook($book);
    public function reserve($book);
    public function getStateName();
}

class AvailableState implements BookState
{
    public function borrow($book)
    {
        if ($book->available_qty > 0) {
            $book->available_qty--;
            if ($book->available_qty == 0) {
                $book->setState(new BorrowedState());
            }
            return true;
        }
        return false;
    }

    public function returnBook($book)
    {
        return false; // Cannot return an available book
    }

    public function reserve($book)
    {
        return false; // No need to reserve available books
    }

    public function getStateName()
    {
        return 'Available';
    }
}

class BorrowedState implements BookState
{
    public function borrow($book)
    {
        return false; // Cannot borrow if all copies are borrowed
    }

    public function returnBook($book)
    {
        $book->available_qty++;
        
        // Check if there are reservations
        require_once BASE_PATH . '/models/Reservation.php';
        $reservationModel = new Reservation();
        $hasReservations = $reservationModel->hasActiveReservations($book->id);
        
        if ($hasReservations) {
            $book->setState(new ReservedState());
        } else {
            $book->setState(new AvailableState());
        }
        return true;
    }

    public function reserve($book)
    {
        // Can reserve borrowed books
        return true;
    }

    public function getStateName()
    {
        return 'Borrowed';
    }
}

class ReservedState implements BookState
{
    public function borrow($book)
    {
        // Only the person who reserved can borrow
        return false;
    }

    public function returnBook($book)
    {
        $book->available_qty++;
        
        require_once BASE_PATH . '/models/Reservation.php';
        $reservationModel = new Reservation();
        $hasReservations = $reservationModel->hasActiveReservations($book->id);
        
        if ($hasReservations) {
            $book->setState(new ReservedState());
        } else {
            $book->setState(new AvailableState());
        }
        return true;
    }

    public function reserve($book)
    {
        return true; // Can add more reservations
    }

    public function getStateName()
    {
        return 'Reserved';
    }
}

class BookContext
{
    private $state;
    public $id;
    public $available_qty;

    public function setState(BookState $state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function borrow()
    {
        return $this->state->borrow($this);
    }

    public function returnBook()
    {
        return $this->state->returnBook($this);
    }

    public function reserve()
    {
        return $this->state->reserve($this);
    }

    public function getStateName()
    {
        return $this->state->getStateName();
    }
}

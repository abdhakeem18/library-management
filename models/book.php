<?php

require_once 'BaseModel.php';

class book extends BaseModel
{

    public $title;
    public $description;
    public $category;
    public $author;
    public $date_published;
    public $qty;
    public $isbn;
    public $available_qty;
    public $availability_status;

    protected function getTableName()
    {
        return "books";
    }


    protected function addNewRec()
    {
        $param = array(
            ':title' => $this->title,
            ':description' => $this->description,
            ':category' => $this->category,
            ':author' => $this->author,
            ':date_published' => $this->date_published,
            ':qty' => $this->qty,
            ':isbn' => $this->isbn,
            ':available_qty' => $this->available_qty ?? $this->qty,
            ':availability_status' => 'Available'
        );
        return $this->pm->run(
            "INSERT INTO 
            books(title, description, category, author, date_published, qty, isbn, available_qty, availability_status) 
            values(:title, :description, :category, :author, :date_published, :qty, :isbn, :available_qty, :availability_status)",
            $param
        );
    }

    protected function updateRec()
    {
        $param = array(
            ':title' => $this->title,
            ':description' => $this->description,
            ':category' => $this->category,
            ':author' => $this->author,
            ':date_published' => $this->date_published,
            ':qty' => $this->qty,
            ':isbn' => $this->isbn,
            ':available_qty' => $this->available_qty,
            ':availability_status' => $this->availability_status,
            ':id' => $this->id
        );

        return $this->pm->run(
            "UPDATE
              books 
              SET 
                  title = :title, 
                  description = :description,
                  category = :category,
                  author = :author,
                  date_published = :date_published,
                  qty = :qty,
                  isbn = :isbn,
                  available_qty = :available_qty,
                  availability_status = :availability_status
              WHERE id = :id",
            $param
        );
    }

    function deleteBook($id)
    {
        $bookModel = new book();
        $bookModel->deleteRec($id);

        if ($bookModel) {
            return true; // bool udapted successfully
        } else {
            return false; // bool update failed (likely due to database error)
        }
    }


    function updateBook($id, $title, $description, $category, $author, $date_published, $qty)
    {
        $bookModel = new book();
        
        // Get existing book data to preserve ISBN and availability
        $existingBook = $bookModel->getbookById($id);
        
        $bookModel->id = $id;
        $bookModel->title = $title;
        $bookModel->description = $description;
        $bookModel->category = $category;
        $bookModel->author = $author;
        $bookModel->date_published = $date_published;
        
        // Calculate qty change
        $qtyDiff = $qty - $existingBook['qty'];
        $bookModel->qty = $qty;
        $bookModel->available_qty = $existingBook['available_qty'] + $qtyDiff;
        
        // Update availability status based on State pattern
        if ($bookModel->available_qty > 0) {
            $bookModel->availability_status = 'Available';
        } elseif ($bookModel->available_qty == 0) {
            // Check if there are reservations
            require_once BASE_PATH . '/models/Reservation.php';
            $reservation = new Reservation();
            $hasReservations = $reservation->hasActiveReservations($id);
            $bookModel->availability_status = $hasReservations ? 'Reserved' : 'Borrowed';
        }
        
        $bookModel->isbn = $existingBook['isbn'];
        $bookModel->updateRec();

        if ($bookModel) {
            return true; // User udapted successfully
        } else {
            return false; // User update failed (likely due to database error)
        }
    }


    function getbookById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("SELECT * FROM " . $this->getTableName() . " WHERE id = :id", $param, true);
    }
    
    // State Pattern - Decrease availability when book is borrowed
    function decreaseAvailability($bookId)
    {
        $book = $this->getbookById($bookId);
        if ($book && $book['available_qty'] > 0) {
            $newQty = $book['available_qty'] - 1;
            $status = $newQty > 0 ? 'Available' : 'Borrowed';
            
            // Check for reservations if qty becomes 0
            if ($newQty == 0) {
                require_once BASE_PATH . '/models/Reservation.php';
                $reservation = new Reservation();
                $hasReservations = $reservation->hasActiveReservations($bookId);
                $status = $hasReservations ? 'Reserved' : 'Borrowed';
            }
            
            $param = array(
                ':available_qty' => $newQty,
                ':availability_status' => $status,
                ':id' => $bookId
            );
            
            return $this->pm->run(
                "UPDATE books SET available_qty = :available_qty, availability_status = :availability_status WHERE id = :id",
                $param
            );
        }
        return false;
    }
    
    // State Pattern - Increase availability when book is returned
    function increaseAvailability($bookId)
    {
        $book = $this->getbookById($bookId);
        if ($book && $book['available_qty'] < $book['qty']) {
            $newQty = $book['available_qty'] + 1;
            
            // Check for reservations
            require_once BASE_PATH . '/models/Reservation.php';
            $reservation = new Reservation();
            $hasReservations = $reservation->hasActiveReservations($bookId);
            
            $status = $hasReservations ? 'Reserved' : 'Available';
            
            $param = array(
                ':available_qty' => $newQty,
                ':availability_status' => $status,
                ':id' => $bookId
            );
            
            return $this->pm->run(
                "UPDATE books SET available_qty = :available_qty, availability_status = :availability_status WHERE id = :id",
                $param
            );
        }
        return false;
    }
    
    // Get borrowed history for a book
    function getBorrowHistory($bookId)
    {
        $param = array(':book_id' => $bookId);
        return $this->pm->run(
            "SELECT br.*, u.fullname, u.email 
             FROM borrowing br 
             JOIN users u ON br.user_id = u.id 
             WHERE br.book_id = :book_id 
             ORDER BY br.borrow_date DESC",
            $param
        );
    }
}
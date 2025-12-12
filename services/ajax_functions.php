<?php

// Start output buffering to prevent any unwanted output
ob_start();

if (!defined('ENTRY_POINT')) {
    define('ENTRY_POINT', true);
}

require_once dirname(__DIR__) . '/helpers/AppManager.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Payment.php';
require_once BASE_PATH . '/models/book.php';
require_once BASE_PATH . '/models/fine_fee.php';
require_once BASE_PATH . '/models/borroW.php';


$sm = AppManager::getSM();

$userId = $sm->getAttribute("userId");

// Get action from POST or GET
$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Define target directory
$target_dir = BASE_PATH . "../assets/uploads/";
$REFERER = $_SERVER['HTTP_REFERER'];
//create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_user') {

    try {
        $username = $_POST['user_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $permission = $_POST['permission'] ?? "member";

        // Get file information
        $image = $_FILES["image"] ?? null;
        $imageFileName = null;

        // Check if file is uploaded
        if (isset($image) && !empty($image)) {
            // Check if there are errors
            if ($image["error"] > 0) {
                echo json_encode(['success' => false, 'message' => "Error uploading file: " . $image["error"]]);
                exit;
            } else {
                // Check if file is an image
                if (getimagesize($image["tmp_name"]) !== false) {
                    // Check file size (optional)
                    if ($image["size"] < 500000) { // 500kb limit
                        // Generate unique filename
                        $new_filename = uniqid() . "." . pathinfo($image["name"])["extension"];

                        // Move uploaded file to target directory
                        if (move_uploaded_file($image["tmp_name"], $target_dir . $new_filename)) {
                            $imageFileName = $new_filename;
                        } else {
                            echo json_encode(['success' => false, 'message' => "Error moving uploaded file."]);
                            exit;
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => "File size is too large."]);
                        exit;
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => "Uploaded file is not an image."]);
                    exit;
                }
            }
        }


        $userModel = new User();
        $created =  $userModel->createUser($username, $password, $permission, $email);

        if ($created) {

            if (!$userId) {
                $sm->setAttribute("userId", $userModel->getUserByUsernameOrEmail($username, $email)['id']);
                $sm->setAttribute("username", $username);
                $sm->setAttribute("permission", $permission);

                header('location: /');
                exit;
            } else {
                $sm->setAttribute("ts-status", "success");
                $sm->setAttribute("ts-message", "User created successfully!");
                echo json_encode(['success' => true, 'message' => "User created successfully!"]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user. May be user already exist!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

//Get user by id
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id']) && isset($_GET['action']) &&  $_GET['action'] == 'get_user') {

    try {
        $user_id = $_GET['user_id'];
        $userModel = new User();

        $user = $userModel->getUserById($user_id);
        if ($user) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "User retrieved successfully!");
            echo json_encode(['success' => true, 'message' => "User created successfully!", 'data' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user. May be user already exist!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

//Delete by user id
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_id']) && isset($_GET['action']) && $_GET['action'] == 'delete_user') {
    try {
        $user_id = $_GET['user_id'];
        $permission = $_GET['permission'];

        $userModel = new User();

        // Proceed to delete the user if doctor deletion was successful or not needed
        $userDeleted = $userModel->deleteUser($user_id);

        if ($userDeleted) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "User deleted successfully!");
            echo json_encode(['success' => true, 'message' => 'User deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

//update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {

    try {
        $username = $_POST['user_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? "";
        $cpassword = $_POST['confirm_password'] ?? "";
        $permission = $_POST['permission'] ?? '';
        $is_active = $_POST['is_active'] == 1 ? 1 : 0;
        $id = $_POST['id'];

        // Validate inputs
        if (empty($username) || empty($email) || empty($password) || empty($cpassword)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing!']);
            exit;
        }

        // Validate inputs
        if (($password) != $cpassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match..!']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            exit;
        }

        $userModel = new User();
        $updated =  $userModel->updateUser($id, $username, $password, $permission, $email, $is_active);
        if ($updated) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "User updated successfully!");
            echo json_encode(['success' => true, 'message' => "User updated successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user. May be user already exist!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}


//create book

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create-book') {
    try {
        if (empty($_POST['title']) && empty($_POST['category']) && empty($_POST['author']) && empty($_POST['date_published'] && empty($_POST['qty']))) {
            echo json_encode(['success' => false, 'message' => 'Error: please fill all the datas.']);
        } else {
            $book = new book();

            $book->title = $_POST['title'];
            $book->description = $_POST['description'];
            $book->category = $_POST['category'];
            $book->author = $_POST['author'];
            $book->date_published = $_POST['date_published'];
            $book->qty = $_POST['qty'];
            $book->save();

            if ($book) {
                $sm->setAttribute("ts-status", "success");
                $sm->setAttribute("ts-message", "book created successfully!");
                echo json_encode(['success' => true, 'message' => "book created successfully!"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create book. May be book already exist!']);
            }

            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}


// get book by id

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['book_id']) && isset($_GET['action']) &&  $_GET['action'] == 'get_book') {

    try {
        $book_id = $_GET['book_id'];
        $bookModel = new Book();

        $book = $bookModel->getbookById($book_id);
        if ($book) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "Book retrieved successfully!");
            echo json_encode(['success' => true, 'message' => "Book created successfully!", 'data' => $book]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create book. May be book already exist!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}


// delete by book id


if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'delete_book') {
    try {
        $book_id = $_GET['id'];

        $bookModel = new book();
        $bookDeleted = $bookModel->deleteBook($book_id);

        if ($bookDeleted) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "Book deleted successfully!");
            echo json_encode(['success' => true, 'message' => 'Book deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete Book.']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}


//update book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_book') {

    try {
        $author = $_POST['author'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $date_published = $_POST['date_published'];
        $qty = $_POST['qty'];
        $id = $_POST['id'];

        // Validate inputs
        if (empty($author) || empty($description) || empty($category) || empty($date_published) || empty($qty)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing!']);
            exit;
        }

        $bookModel = new book();
        $updated =  $bookModel->updatebook($id, $title, $description, $category, $author, $date_published, $qty);
        if ($updated) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "Book updated successfully!");
            echo json_encode(['success' => true, 'message' => "Book updated successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update book. May be book already exist!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}







//create borrow book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create-borrow') {
    try {
        $userId = $_POST['user_id'] ?? $_POST['member_no'] ?? null;
        
        if (empty($_POST['book_id']) || empty($userId) || empty($_POST['borrow_date'])) {
            echo json_encode(['success' => false, 'message' => 'Error: please fill all the data.']);
        } else {
            $borrowing = new borrow();
            $bookModel = new book();
            $userModel = new User();

            $user = $userModel->getById($userId);
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'User not found!']);
                exit;
            }

            $bookDetails = $bookModel->getBookById($_POST['book_id']);
            if (!$bookDetails) {
                echo json_encode(['success' => false, 'message' => 'Book not found!']);
                exit;
            }

            // Check if book is available
            if ($bookDetails['available_qty'] <= 0) {
                echo json_encode(['success' => false, 'message' => 'Book is not available. You can reserve it instead.']);
                exit;
            }

            // Check for active borrowings or unpaid fines
            $checkBorrowed = $borrowing->getAllByColumnValue("user_id", $userId, "AND (status = 'borrowed' OR status = 'overdue')");
             
            $hasNoBorrowings = (is_array($checkBorrowed) && empty($checkBorrowed)) || !is_array($checkBorrowed);
             
            if ($hasNoBorrowings) {
                $borrowDate = $_POST['borrow_date'];
                
                // Use the due_date from the form if provided, otherwise calculate based on membership
                if (!empty($_POST['due_date'])) {
                    $dueDate = $_POST['due_date'];
                } else {
                    $membershipType = strtolower($user['membership_type'] ?? 'student');
                    
                    $daysToAdd = 14;
                    if ($membershipType === 'faculty') {
                        $daysToAdd = 30;
                    } elseif ($membershipType === 'guest') {
                        $daysToAdd = 7; 
                    }
                    
                    $dueDate = date('Y-m-d', strtotime($borrowDate . ' + ' . $daysToAdd . ' days'));
                }
                
                $borrowing->book_id = $_POST['book_id'];
                $borrowing->user_id = $userId;
                $borrowing->borrow_date = $borrowDate;
                $borrowing->due_date = $dueDate;
                $result = $borrowing->save();

                if ($result !== -1) {
                    $bookModel->decreaseAvailability($_POST['book_id']);
                    
                    $sm->setAttribute("ts-status", "success");
                    $sm->setAttribute("ts-message", "Borrowing created successfully!");
                    echo json_encode([
                        'success' => true, 
                        'message' => "Borrowing created successfully! Due date: $dueDate"
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create borrowing record!']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'User has not returned the book yet.']);
            }

            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);

        exit;
    }
}

// get borrow by id

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['action']) &&  $_GET['action'] == 'get_borrow') {

    try {
        $borrow_id = $_GET['id'];

        // print_r($_GET['id']);
        $borrowModel = new Borrow();

        $borrow = $borrowModel->getborrowById($borrow_id);
        if ($borrow) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "Borrow retrieved successfully!");
            echo json_encode(['success' => true, 'data' => $borrow]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to get borrow details!']);
        }

        exit;
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// update borrow by id

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !empty($_POST['id']) && $_POST['action'] === 'edit-borrow') {

    try {
        $id = $_POST['id'];
        $book_id = $_POST['book_id'];
        $member_no = $_POST['member_no'];
        $due_date = $_POST['due_date'];
        $return_date = $_POST['retrun_date'] ?? "";
        $status = (!empty($_POST['retrun_date']) ? "returned" : "borrowed");

        // Validate inputs
        if (empty($book_id) || empty($member_no) || empty($due_date)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing!']);
            exit;
        }

        $borrowModel = new borrow();
        
        // Calculate fine if return date is after due date
        $fine_amount = 0;
        if (!empty($return_date) && $return_date > $due_date) {
            // Get user membership type for fine calculation
            $userModel = new User();
            $user = $userModel->getById($member_no);
            
            $membershipType = strtolower($user['membership_type'] ?? 'student');
            
            // Set fine rate based on membership type
            $finePerDay = 50; // Default for students
            if ($membershipType === 'faculty') {
                $finePerDay = 20;
            } elseif ($membershipType === 'guest') {
                $finePerDay = 100;
            }

            $date1 = new DateTime($due_date);
            $date2 = new DateTime($return_date);
            $interval = $date1->diff($date2);
            $fine_amount = $finePerDay * $interval->days;
        }
        
        $edited = $borrowModel->updateBorrow($id, $book_id, $member_no, $due_date, $return_date, $status, $fine_amount);

        // If book is being returned, increase available quantity
        if (!empty($return_date) && $status === 'returned') {
            $bookModel = new book();
            $bookModel->increaseAvailability($book_id);
        }

        // Save fine to fine_fee table if there's a fine amount
        if ($fine_amount > 0) {
            $fineModel = new fineFee();
            $fineModel->borrow_id = $id;
            $fineModel->amount = $fine_amount;
            $fineModel->save();
        }

        if ($edited) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "Borrow updated successfully!");
            echo json_encode(['success' => true, 'message' => "Borrow updated successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update borrow details!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}


//create payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'payment') {
    try {
        if (empty($_POST['payment_id']) && empty($_POST['amount']) && empty($_POST['payed_type']) && empty($_POST['status']) && empty($_POST['created_at']) && empty($_POST['updated_at'])) {
            echo json_encode(['success' => false, 'message' => 'Error: please fill all the datas.']);
        } else {
            $borrowing = new payment();

            $payment->amount = $_POST['amount'];
            $payment->payed_type = $_POST['payed_type'];
            $payment->status = $_POST['status'];
            $payment->created_at = $_POST['created_at'];
            $payment->updated_at = $_POST['updated_at'];
            $payment->save();


            if ($payment) {
                $sm->setAttribute("ts-status", "success");
                $sm->setAttribute("ts-message", "payment created successfully!");
                echo json_encode(['success' => true, 'message' => "payment created successfully!"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create payment!']);
            }

            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);

        exit;
    }
}


// get payment by id

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['action']) &&  $_GET['action'] == 'get_payment') {

    try {
        $payment_id = $_GET['id'];

        // print_r($_GET['id']);
        $paymentModel = new payment();

        $payment = $paymentModel->getById($payment_id);
        if ($payment) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "Payment retrieved successfully!");
            echo json_encode(['success' => true, 'data' => $payment]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to get borrow details!']);
        }

        exit;
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}


// update payment by id

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !empty($_POST['id']) && $_POST['action'] === 'edit_payment') {

    try {
        $amount = $_POST['amount'];
        $payed_type = $_POST['payed_type'];
        $status = $_POST['status'];
        $created_at = $_POST['created_at'];
        $updated_at = $_POST['updated_at'] ?? "";


        // Validate inputs
        if (empty($amount) || empty($payed_type) || empty($status)  || empty($created_at) || empty($updated_at)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing!']);
            exit;
        }


        $paymentModel = new payment();
        $edited =  $paymentModel->updatepayment($amount, $payed_type, $status, $created_at, $updated_at);
        if ($edited) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "payment updated successfully!");
            echo json_encode(['success' => true, 'message' => "payment updated successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update payment details!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}



//Delete by payment id
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user_id']) && isset($_GET['action']) && $_GET['action'] == 'delete_payment') {
    try {
        $payment_id = $_GET['payment_id'];
        $permission = $_GET['permission'];

        $paymentModel = new payment();

        // Proceed to delete the user if doctor deletion was successful or not needed
        $paymentdeleted = $paymentModel->deletepayment($payment_id);

        if ($paymentDeleted) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "payment deleted successfully!");
            echo json_encode(['success' => true, 'message' => 'payment deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete payment.']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}


// get fine_fee by id

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && isset($_GET['action']) &&  $_GET['action'] == 'get_fine_fee') {

    try {
        $id = $_GET['id'];

        // print_r($_GET['id']);
        $fine_feeModel = new fineFee();

        $fine_fee = $fine_feeModel->getById($id);
        if ($fine_fee) {
            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "Fine fee retrieved successfully!");
            echo json_encode(['success' => true, 'data' => $fine_fee]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to get borrow details!']);
        }

        exit;
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}



// update fine_fee by id

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !empty($_POST['id']) && $_POST['action'] === 'edit_fine_fee') {

    try {
        $amount = $_POST['amount'];
        $payed_type = $_POST['pied_type'];
        $additional = $_POST['additional'];
        $fine_id = $_POST['id'];

        // Validate inputs
        if (empty($amount) || empty($payed_type)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing!']);
            exit;
        }

        // Get the fine record to find borrow_id, then get user_id from borrow
        $fineModel = new fineFee();
        $fine = $fineModel->getById($fine_id);
        
        $borrowModel = new borrow();
        $borrow = $borrowModel->getborrowById($fine['borrow_id']);

        // Create payment record
        $paymentModel = new Payment();
        $paymentModel->user_id = $borrow['user_id'];
        $paymentModel->amount = $amount;
        $paymentModel->payment_type = $payed_type;
        $paymentModel->status = "completed";
        $payment_result = $paymentModel->save();

        if ($payment_result != -1) {
            // Get last inserted payment ID
            $payment_id = $paymentModel->getLastInsertedPaymentId();
            
            $fineModel->id = $fine_id;
            $fineModel->payment_id = $payment_id;
            $fineModel->status = "paid";
            $fineModel->save();

            $sm->setAttribute("ts-status", "success");
            $sm->setAttribute("ts-message", "fine updated successfully!");
            echo json_encode(['success' => true, 'message' => "Payment processed successfully!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create payment record!']);
        }
    } catch (PDOException $e) {
        // Handle database connection errors
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Reservation Management
if ($action == 'create_reservation') {
    require_once BASE_PATH . '/models/Reservation.php';
    require_once BASE_PATH . '/models/book.php';
    
    $reservation = new Reservation();
    $book = new book();
    
    $book_id = $_POST['book_id'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    
    // Validate required fields
    if (!$book_id || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Book ID and User ID are required!', 'debug' => $_POST]);
        exit;
    }
    
    // Check if book exists
    $bookDetails = $book->getBookById($book_id);
    if (!$bookDetails) {
        echo json_encode(['success' => false, 'message' => 'Book not found!']);
        exit;
    }
    
    // Check if user already has active reservation for this book
    $existingReservations = $reservation->getByUserId($user_id);
    if (is_array($existingReservations)) {
        foreach ($existingReservations as $res) {
            if ($res['book_id'] == $book_id && $res['status'] == 'active') {
                echo json_encode(['success' => false, 'message' => 'User already has an active reservation for this book!']);
                exit;
            }
        }
    }
    
    // Create reservation - set properties on the object
    $reservation->book_id = $book_id;
    $reservation->user_id = $user_id;
    $reservation->status = 'active';
    
    $result = $reservation->save();
    
    // Clean any output buffer before sending JSON
    ob_clean();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reservation created successfully!', 'id' => $reservation->id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create reservation!']);
    }
    exit;
}

if ($action == 'fulfill_reservation') {
    require_once BASE_PATH . '/models/Reservation.php';
    require_once BASE_PATH . '/models/Notification.php';
    
    $reservation = new Reservation();
    $notificationModel = new Notification();
    $reservation_id = $_POST['reservation_id'];
    
    // Get reservation details before fulfilling
    $reservationDetails = $reservation->getReservationById($reservation_id);
    
    if ($reservationDetails) {
        $result = $reservation->fulfillReservation($reservation_id);
        
        if ($result) {
            // Create notification for user
            $message = "Your reserved book '{$reservationDetails['book_title']}' is now available for pickup. Please collect it within 24 hours.";
            $notificationModel->save([
                'user_id' => $reservationDetails['user_id'],
                'type' => 'reservation_available',
                'message' => $message
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Reservation fulfilled and user notified successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to fulfill reservation!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Reservation not found!']);
    }
    exit;
}

if ($action == 'cancel_reservation') {
    require_once BASE_PATH . '/models/Reservation.php';
    
    $reservation = new Reservation();
    $reservation_id = $_POST['reservation_id'];
    
    $result = $reservation->cancelReservation($reservation_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel reservation!']);
    }
    exit;
}

if ($action == 'delete_reservation') {
    require_once BASE_PATH . '/models/Reservation.php';
    
    $reservation = new Reservation();
    $reservation_id = $_POST['reservation_id'];
    
    $result = $reservation->deleteRec($reservation_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reservation deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete reservation!']);
    }
    exit;
}

// Notification Management
if ($action == 'create_notification') {
    require_once BASE_PATH . '/models/Notification.php';
    
    $notification = new Notification();
    
    $data = [
        'user_id' => $_POST['user_id'],
        'type' => $_POST['type'],
        'message' => $_POST['message']
    ];
    
    $result = $notification->save($data);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Notification sent successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send notification!']);
    }
    exit;
}

if ($action == 'mark_notification_read') {
    require_once BASE_PATH . '/models/Notification.php';
    
    $notification = new Notification();
    $notification_id = $_POST['notification_id'];
    
    $result = $notification->markAsRead($notification_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read!']);
    }
    exit;
}

if ($action == 'delete_notification') {
    require_once BASE_PATH . '/models/Notification.php';
    
    $notification = new Notification();
    $notification_id = $_POST['notification_id'];
    
    $result = $notification->deleteRec($notification_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Notification deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete notification!']);
    }
    exit;
}

// Reports
if ($action == 'get_most_borrowed_books') {
    require_once BASE_PATH . '/models/Reports.php';
    
    $reports = new Reports();
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
    
    $result = $reports->getMostBorrowedBooks($limit);
    
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

if ($action == 'get_active_borrowers') {
    require_once BASE_PATH . '/models/Reports.php';
    
    $reports = new Reports();
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
    
    $result = $reports->getActiveBorrowers($limit);
    
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

if ($action == 'get_overdue_books') {
    require_once BASE_PATH . '/models/Reports.php';
    
    $reports = new Reports();
    
    $result = $reports->getOverdueBooks();
    
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

if ($action == 'get_fine_collection') {
    require_once BASE_PATH . '/models/Reports.php';
    
    $reports = new Reports();
    
    $result = $reports->getFineCollectionReport();
    
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}

if ($action == 'get_category_stats') {
    require_once BASE_PATH . '/models/Reports.php';
    
    $reports = new Reports();
    
    $result = $reports->getBooksByCategory();
    
    echo json_encode(['success' => true, 'data' => $result]);
    exit;
}


dd('Access denied..!');

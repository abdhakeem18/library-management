<?php

require_once 'BaseModel.php';

class Reports extends BaseModel
{
    protected function getTableName()
    {
        return 'borrowing'; // Default table
    }

    protected function addNewRec()
    {
        // Not used for reports
        return false;
    }

    protected function updateRec()
    {
        // Not used for reports
        return false;
    }

    public function __construct()
    {
        parent::__construct();
    }

    // Most Borrowed Books Report
    public function getMostBorrowedBooks($limit = 10)
    {
        return $this->pm->run(
            "SELECT 
                b.id,
                b.title,
                b.author,
                b.isbn,
                b.category,
                COUNT(br.id) as borrow_count,
                b.qty,
                b.available_qty
            FROM books b
            LEFT JOIN borrowing br ON b.id = br.book_id
            GROUP BY b.id
            ORDER BY borrow_count DESC
            LIMIT " . (int)$limit
        );
    }

    // Active Borrowers Report
    public function getActiveBorrowers($limit = 10)
    {
        return $this->pm->run(
            "SELECT 
                u.id,
                u.username,
                u.email,
                u.membership_type,
                COUNT(br.id) as total_borrowed,
                SUM(CASE WHEN br.status = 'borrowed' THEN 1 ELSE 0 END) as currently_borrowed,
                SUM(CASE WHEN br.status = 'returned' THEN 1 ELSE 0 END) as total_returned
            FROM users u
            LEFT JOIN borrowing br ON u.id = br.user_id
            WHERE u.permission != 'admin'
            GROUP BY u.id
            HAVING total_borrowed > 0
            ORDER BY total_borrowed DESC
            LIMIT " . (int)$limit
        );
    }

    // Overdue Books Report
    public function getOverdueBooks()
    {
        return $this->pm->run(
            "SELECT 
                br.id as borrow_id,
                br.borrow_date,
                br.due_date,
                DATEDIFF(NOW(), br.due_date) as days_overdue,
                br.fine_amount,
                b.title,
                b.author,
                b.isbn,
                u.id as user_id,
                u.username,
                u.email,
                u.membership_type
            FROM borrowing br
            JOIN books b ON br.book_id = b.id
            JOIN users u ON br.user_id = u.id
            WHERE br.status = 'borrowed' 
            AND br.due_date < CURDATE()
            ORDER BY days_overdue DESC"
        );
    }

    // Books Due Today or Tomorrow
    public function getBooksDueSoon($days = 2)
    {
        return $this->pm->run(
            "SELECT 
                br.id as borrow_id,
                br.borrow_date,
                br.due_date,
                DATEDIFF(br.due_date, NOW()) as days_until_due,
                b.title,
                b.author,
                b.isbn,
                u.id as user_id,
                u.username,
                u.email,
                u.membership_type
            FROM borrowing br
            JOIN books b ON br.book_id = b.id
            JOIN users u ON br.user_id = u.id
            WHERE br.status = 'borrowed' 
            AND br.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL " . (int)$days . " DAY)
            ORDER BY br.due_date ASC"
        );
    }

    // Fine Collection Report
    public function getFineCollectionReport($startDate = null, $endDate = null)
    {
        $sql = "SELECT 
                ff.id,
                ff.amount,
                ff.status as fine_status,
                ff.created_at as fine_created_at,
                u.username,
                u.email,
                u.membership_type,
                b.title as book_title,
                br.borrow_date,
                br.due_date,
                br.return_date,
                p.amount as payment_amount,
                p.payment_date,
                p.payed_type as payment_type,
                p.status as payment_status
            FROM fine_fee ff
            JOIN borrowing br ON ff.borrow_id = br.id
            JOIN users u ON br.user_id = u.id
            JOIN books b ON br.book_id = b.id
            LEFT JOIN payment p ON ff.payment_id = p.id";
        
        if ($startDate && $endDate) {
            $sql .= " WHERE ff.created_at BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        }
        
        $sql .= " ORDER BY ff.created_at DESC";
        
        return $this->pm->run($sql);
    }

    // Revenue Report
    public function getRevenueReport($startDate = null, $endDate = null)
    {
        $sql = "SELECT 
                DATE(payment_date) as date,
                COUNT(*) as payment_count,
                SUM(amount) as total_revenue,
                payment_type
            FROM payment
            WHERE status = 'completed'";
        
        if ($startDate && $endDate) {
            $sql .= " AND payment_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        }
        
        $sql .= " GROUP BY DATE(payment_date), payment_type ORDER BY date DESC";
        
        return $this->pm->run($sql);
    }

    // Book Category Report
    public function getBooksByCategory()
    {
        return $this->pm->run(
            "SELECT 
                category,
                COUNT(*) as total_books,
                SUM(qty) as total_copies,
                SUM(available_qty) as available_copies,
                SUM(qty - available_qty) as borrowed_copies
            FROM books
            GROUP BY category
            ORDER BY total_books DESC"
        );
    }

    // Member Statistics
    public function getMembershipStatistics()
    {
        return $this->pm->run(
            "SELECT 
                membership_type,
                COUNT(*) as total_members,
                SUM(CASE WHEN permission = 'user' THEN 1 ELSE 0 END) as active_members
            FROM users
            WHERE permission != 'admin'
            GROUP BY membership_type"
        );
    }

    // Dashboard Statistics
    public function getDashboardStats()
    {
        // Total books
        $bookStats = $this->pm->run(
            "SELECT 
                COUNT(*) as total_books,
                SUM(qty) as total_copies,
                SUM(available_qty) as available_copies,
                SUM(qty - available_qty) as borrowed_copies
            FROM books",
            array(),
            true
        );

        // User statistics
        $userStats = $this->pm->run(
            "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN membership_type = 'Student' THEN 1 ELSE 0 END) as students,
                SUM(CASE WHEN membership_type = 'Faculty' THEN 1 ELSE 0 END) as faculty,
                SUM(CASE WHEN membership_type = 'Guest' THEN 1 ELSE 0 END) as guests
            FROM users
            WHERE permission != 'admin'",
            array(),
            true
        );

        // Borrowing statistics
        $borrowingStats = $this->pm->run(
            "SELECT 
                COUNT(*) as total_borrowings,
                SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as active_borrowings,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_borrowings,
                SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_borrowings
            FROM borrowing",
            array(),
            true
        );

        // Fine statistics
        $fineStats = $this->pm->run(
            "SELECT 
                COUNT(*) as total_fines,
                SUM(amount) as total_fine_amount,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount
            FROM fine_fee",
            array(),
            true
        );

        // Reservation statistics
        $reservationStats = $this->pm->run(
            "SELECT 
                COUNT(*) as total_reservations,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_reservations,
                SUM(CASE WHEN status = 'fulfilled' THEN 1 ELSE 0 END) as fulfilled_reservations,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_reservations
            FROM reservations",
            array(),
            true
        );

        return [
            'books' => $bookStats,
            'users' => $userStats,
            'borrowing' => $borrowingStats,
            'fines' => $fineStats,
            'reservations' => $reservationStats
        ];
    }

    // Borrowing History for a User
    public function getUserBorrowingHistory($userId)
    {
        $param = array(':user_id' => $userId);
        return $this->pm->run(
            "SELECT 
                br.id,
                br.borrow_date,
                br.due_date,
                br.return_date,
                br.status,
                br.fine_amount,
                b.title,
                b.author,
                b.isbn,
                CASE 
                    WHEN br.status = 'borrowed' AND br.due_date < CURDATE() 
                    THEN DATEDIFF(NOW(), br.due_date)
                    ELSE 0 
                END as days_overdue
            FROM borrowing br
            JOIN books b ON br.book_id = b.id
            WHERE br.user_id = :user_id
            ORDER BY br.borrow_date DESC",
            $param
        );
    }

    // Monthly Borrowing Trend
    public function getMonthlyBorrowingTrend($months = 6)
    {
        return $this->pm->run(
            "SELECT 
                DATE_FORMAT(borrow_date, '%Y-%m') as month,
                COUNT(*) as total_borrowings,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned,
                SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as still_borrowed
            FROM borrowing
            WHERE borrow_date >= DATE_SUB(NOW(), INTERVAL " . (int)$months . " MONTH)
            GROUP BY DATE_FORMAT(borrow_date, '%Y-%m')
            ORDER BY month DESC"
        );
    }
}

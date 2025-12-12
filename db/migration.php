<?php

if (!defined('ENTRY_POINT')) {
    http_response_code(403);
    exit('Forbidden');
}

define("DB_UPDATE", true);

class DbMigration
{
    private $pdo;

    public function __construct()
    {
        try {

            // Database connection using PDO
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Create necessary tables
            if (DB_UPDATE) {
                $this->createTables();
            }
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function createTables()
    {
        // Users table with contact_number and membership_type
        $query_users = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(200) NOT NULL,
            `email` VARCHAR(200) NOT NULL,
            `contact_number` VARCHAR(20) DEFAULT NULL,
            `password` VARCHAR(240) NOT NULL,
            `permission` ENUM('user','admin','member') NOT NULL DEFAULT 'user',
            `membership_type` ENUM('Student','Faculty','Guest') DEFAULT 'Student',
            `is_active` TINYINT(5) NOT NULL DEFAULT 0,
            `borrowing_limit` INT(11) DEFAULT 3,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;";
        $this->pdo->exec($query_users);

        // Books table with ISBN and availability_status
        $query_books = "CREATE TABLE IF NOT EXISTS `books` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(200) NOT NULL,
            `description` TEXT,
            `category` VARCHAR(50) NOT NULL,
            `author` VARCHAR(100) NOT NULL,
            `isbn` VARCHAR(20) UNIQUE,
            `date_published` VARCHAR(25) NOT NULL,
            `qty` INT(11) NOT NULL DEFAULT 0,
            `available_qty` INT(11) NOT NULL DEFAULT 0,
            `availability_status` ENUM('Available','Borrowed','Reserved') DEFAULT 'Available',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->pdo->exec($query_books);

        // Borrowing table
        $query_borrowing = "CREATE TABLE IF NOT EXISTS `borrowing` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `book_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `borrow_date` DATE NOT NULL,
            `due_date` DATE NOT NULL,
            `return_date` DATE DEFAULT NULL,
            `status` ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
            `fine_amount` DECIMAL(10,2) DEFAULT 0.00,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->pdo->exec($query_borrowing);

        // Reservations table
        $query_reservations = "CREATE TABLE IF NOT EXISTS `reservations` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `book_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `reservation_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `status` ENUM('active','fulfilled','cancelled') DEFAULT 'active',
            `notified` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->pdo->exec($query_reservations);

        // Notifications table
        $query_notifications = "CREATE TABLE IF NOT EXISTS `notifications` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `type` ENUM('due_date','overdue','reservation_available','general') NOT NULL,
            `message` TEXT NOT NULL,
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->pdo->exec($query_notifications);

        // Fine Fee table
        $query_fine_fee = "CREATE TABLE IF NOT EXISTS `fine_fee` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `borrow_id` INT(11) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `payment_id` INT(11) DEFAULT NULL,
            `status` ENUM('pending','paid') DEFAULT 'pending',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`borrow_id`) REFERENCES `borrowing`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->pdo->exec($query_fine_fee);

        // Payment table
        $query_payment = "CREATE TABLE IF NOT EXISTS `payment` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `payment_type` VARCHAR(20) NOT NULL,
            `status` ENUM('pending','completed','failed') DEFAULT 'pending',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->pdo->exec($query_payment);

        // Insert dummy data
        $this->insertDummyData();
    }

    private function insertDummyData()
    {
        // Password for all users: Admin@123
        $this->pdo->exec("INSERT IGNORE INTO `users` (`id`, `username`, `email`, `contact_number`, `password`, `permission`, `membership_type`, `is_active`, `borrowing_limit`) VALUES
            (1, 'admin', 'admin@library.com', '0771234567', '\$2y\$10\$i.dcKfgPcPYI82qLiJR.hO0RquQ0Zf99z2qMsdD0yUqLCa7IsJSs6', 'admin', 'Faculty', 1, 10),
            (2, 'john_student', 'john@example.com', '0771234568', '\$2y\$10\$i.dcKfgPcPYI82qLiJR.hO0RquQ0Zf99z2qMsdD0yUqLCa7IsJSs6', 'user', 'Student', 1, 3),
            (3, 'jane_faculty', 'jane@example.com', '0771234569', '\$2y\$10\$i.dcKfgPcPYI82qLiJR.hO0RquQ0Zf99z2qMsdD0yUqLCa7IsJSs6', 'user', 'Faculty', 1, 5),
            (4, 'michael_guest', 'michael@example.com', '0771234570', '\$2y\$10\$i.dcKfgPcPYI82qLiJR.hO0RquQ0Zf99z2qMsdD0yUqLCa7IsJSs6', 'member', 'Guest', 1, 2)");

        // Insert sample books with ISBN and availability
        $this->pdo->exec("INSERT IGNORE INTO `books` (`id`, `title`, `description`, `category`, `author`, `isbn`, `date_published`, `qty`, `available_qty`, `availability_status`) VALUES
            (1, 'The Great Gatsby', 'A classic American novel set in the Jazz Age', 'Fiction', 'F. Scott Fitzgerald', '978-0-7432-7356-5', '1925', 10, 10, 'Available'),
            (2, 'To Kill a Mockingbird', 'A novel about racial injustice in the Deep South', 'Fiction', 'Harper Lee', '978-0-06-112008-4', '1960', 8, 8, 'Available'),
            (3, '1984', 'A dystopian social science fiction novel', 'Science Fiction', 'George Orwell', '978-0-452-28423-4', '1949', 12, 12, 'Available'),
            (4, 'Pride and Prejudice', 'A romantic novel of manners', 'Romance', 'Jane Austen', '978-0-14-143951-8', '1813', 7, 7, 'Available'),
            (5, 'The Catcher in the Rye', 'A story about teenage rebellion', 'Fiction', 'J.D. Salinger', '978-0-316-76948-0', '1951', 9, 9, 'Available'),
            (6, 'Harry Potter and the Sorcerers Stone', 'A young wizard discovers his magical heritage', 'Fantasy', 'J.K. Rowling', '978-0-439-70818-8', '1997', 15, 15, 'Available'),
            (7, 'The Hobbit', 'A fantasy novel about a hobbit adventure', 'Fantasy', 'J.R.R. Tolkien', '978-0-547-92822-7', '1937', 11, 11, 'Available'),
            (8, 'The Da Vinci Code', 'A mystery thriller novel', 'Mystery', 'Dan Brown', '978-0-307-47425-0', '2003', 10, 10, 'Available'),
            (9, 'The Alchemist', 'A philosophical novel about following dreams', 'Fiction', 'Paulo Coelho', '978-0-06-231500-7', '1988', 13, 13, 'Available'),
            (10, 'The Hunger Games', 'A dystopian novel about survival', 'Science Fiction', 'Suzanne Collins', '978-0-439-02348-1', '2008', 14, 14, 'Available')");
    }

    // Run a query and fetch results
    public function run($query, $params = null, $fetchFirstRecOnly = false)
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            if ((stripos($query, 'DELETE') === 0)) {
                return $stmt->rowCount();
            }
            if ($fetchFirstRecOnly) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            // dd($e->getMessage());
            error_log("Query execution failed: " . $e->getMessage());
            return -1;
        }
    }

    // Insert a record and get the last inserted ID
    public function insertAndGetLastRowId($query, $params = null)
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            return -1;
        }
    }

    // Count records
    public function getCount($query, $params = null)
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['c'] ?? 0;
        } catch (PDOException $e) {
            error_log("Count query failed: " . $e->getMessage());
            return 0;
        }
    }

}

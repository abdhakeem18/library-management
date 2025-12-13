# Library Management System

A comprehensive web-based Library Management System built with PHP, MySQL, and Bootstrap 5. This system enables efficient management of library operations including book cataloging, borrowing, reservations, fines, and user management.

## Start the Application
   
   Using PHP built-in server:
   ```bash
   php -S localhost:8000 -f index.php
   ```

## Default Login Credentials

### Admin Account
- **Email:** admin@library.com
- **Password:** Admin@123


## Features

### 📚 Book Management
- Add, edit, and delete books
- Track book quantities and availability
- ISBN support
- Category-based organization
- Search and filter functionality

### 👥 User Management
- Multiple user roles (Admin, Member, User)
- Membership types (Student, Faculty, Guest)
- User borrowing limits based on membership type
- Active/inactive user status management

### 📖 Borrowing System
- Book checkout and return processing
- Due date tracking
- Automatic overdue status detection
- Borrowing history tracking
- Fine calculation for overdue books

### 🔖 Reservation System
- Book reservation functionality
- Automatic notification system
- Reservation status tracking (Active, Fulfilled, Cancelled)

### 💰 Fine & Payment Management
- Automatic fine calculation for overdue books
- Payment processing and tracking
- Multiple payment types support
- Fine collection reports

### 📊 Reports & Analytics
- Most borrowed books report
- Active borrowers statistics
- Overdue books tracking
- Fine collection reports
- Category-wise book statistics
- Dashboard with key metrics

## Technologies Used

- **Backend:** PHP 8.2
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript, jQuery
- **UI Framework:** Bootstrap 5
- **Additional Libraries:** 
  - DataTables
  - SweetAlert2
  - Chart.js
  - FontAwesome

## Installation

### Prerequisites
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Web server (Apache/Nginx) or PHP built-in server
- Composer (optional)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd library_managment_system
   ```

2. **Configure Database**
   - Create a new MySQL database
   - Import the database schema:
     ```bash
     mysql -u root -p database_name < library_system.sql
     ```
   - Or use the provided migration file

3. **Configure Application**
   - Open `config.php` and update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'your_database_name');
     define('DB_USERNAME', 'your_username');
     define('DB_PASSWORD', 'your_password');
     ```

4. **Database Migration**
   - Set `DB_UPDATE` to `true` in `db/migration.php` to create tables and seed data
   - Run the application once, then set it back to `false`


5. **Access the Application**
   ```
   http://localhost:8000
   ```


> ⚠️ **Important:** Change these default passwords in production!

## Project Structure

```
library_managment_system/
├── assets/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   ├── forms-js/         # AJAX form handlers
│   ├── plugins/          # Third-party libraries
│   ├── img/              # Images and icons
│   └── uploads/          # File uploads
├── db/
│   └── migration.php     # Database migration and seeding
├── helpers/
│   ├── AppManager.php    # Application utilities
│   └── SessionManager.php # Session management
├── models/
│   ├── BaseModel.php     # Base model class
│   ├── book.php          # Book model
│   ├── borrow.php        # Borrowing model
│   ├── Reservation.php   # Reservation model
│   ├── fine_fee.php      # Fine model
│   ├── Payment.php       # Payment model
│   └── User.php          # User model
├── services/
│   ├── ajax_functions.php # AJAX endpoint handler
│   ├── auth.php          # Authentication service
│   └── logout.php        # Logout handler
├── views/
│   ├── admin/            # Admin pages
│   │   ├── dashboard.php
│   │   ├── book.php
│   │   ├── borrowing.php
│   │   ├── reservations.php
│   │   ├── fine.php
│   │   ├── payment.php
│   │   └── users.php
│   ├── auth/             # Authentication pages
│   │   ├── login.php
│   │   └── register.php
│   └── layouts/          # Layout components
│       ├── app.php
│       ├── header.php
│       ├── sidebar.php
│       └── footer.php
├── web/
│   └── route.php         # Route configuration
├── config.php            # Database configuration
├── index.php             # Application entry point
└── README.md             # This file
```

## Database Schema

### Tables
- **users** - User accounts and profiles
- **books** - Book catalog
- **borrowing** - Borrowing transactions
- **reservations** - Book reservations
- **fines** - Fine records
- **payments** - Payment transactions

### Key Relationships
- Users can borrow multiple books
- Books can have multiple borrowing records
- Books can be reserved by users
- Fines are linked to borrowing records
- Payments are linked to fines

## Usage

### For Administrators

1. **Managing Books**
   - Navigate to Dashboard → Books
   - Add new books with title, author, ISBN, category, etc.
   - Update quantities and track availability

2. **Managing Users**
   - Navigate to Dashboard → Users
   - Create new user accounts
   - Assign roles and membership types
   - Set borrowing limits

3. **Processing Borrowing**
   - Navigate to Dashboard → Borrowing
   - Issue books to users
   - Process returns
   - View borrowing history

4. **Handling Reservations**
   - Navigate to Dashboard → Reservations
   - View active reservations
   - Fulfill or cancel reservations

5. **Managing Fines**
   - Navigate to Dashboard → Fines
   - View overdue fines
   - Process fine payments

6. **Viewing Reports**
   - Navigate to Dashboard → Reports
   - View most borrowed books
   - Track active borrowers
   - Monitor overdue books
   - Generate fine collection reports

### For Library Members

1. Login with credentials
2. Browse available books
3. Reserve books
4. View borrowing history
5. Check due dates and fines

## Key Features Explained

### Automatic Fine Calculation
- System automatically calculates fines for overdue books
- Configurable fine rates per day
- Real-time fine status updates

### Membership Types
- **Student:** Standard borrowing limit (3 books)
- **Faculty:** Higher borrowing limit (5 books)
- **Guest:** Limited borrowing limit (2 books)

### Book Availability Tracking
- Real-time quantity updates
- Automatic status changes (Available/Borrowed/Reserved)
- Reservation notifications

### Alert System
- Bootstrap-based alert notifications
- Success/error message handling
- Consistent user feedback across all forms

## Security Features

- Password hashing using bcrypt
- SQL injection prevention using PDO prepared statements
- Session management for authentication
- Role-based access control
- CSRF protection ready

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Known Issues

- None currently reported

## Future Enhancements

- [ ] Email notification system
- [ ] PDF report generation
- [ ] Barcode scanning for books
- [ ] Mobile responsive design improvements
- [ ] RESTful API development
- [ ] Book recommendation system
- [ ] Multi-language support

## License

This project is developed for educational purposes.

## Support

For support, email support@library.com or create an issue in the repository.

## Acknowledgments

- Bootstrap team for the UI framework
- jQuery and DataTables for enhanced functionality
- FontAwesome for icons
- All contributors and testers

---

**Version:** 1.0.0  
**Last Updated:** December 2025  
**Developed By:** Abdul Hakeem

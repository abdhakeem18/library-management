<?php

// Observer Pattern for Notifications

interface Observer
{
    public function update($data);
}

interface Subject
{
    public function attach(Observer $observer);
    public function detach(Observer $observer);
    public function notify($data);
}

class NotificationSubject implements Subject
{
    private $observers = [];

    public function attach(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function detach(Observer $observer)
    {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }

    public function notify($data)
    {
        foreach ($this->observers as $observer) {
            $observer->update($data);
        }
    }
}

class EmailObserver implements Observer
{
    public function update($data)
    {
        // Send email notification
        $this->sendEmail($data);
    }

    private function sendEmail($data)
    {
        // Email sending logic
        error_log("Email sent to user {$data['user_id']}: {$data['message']}");
    }
}

class DatabaseObserver implements Observer
{
    public function update($data)
    {
        // Save notification to database
        $this->saveNotification($data);
    }

    private function saveNotification($data)
    {
        require_once BASE_PATH . '/models/Notification.php';
        $notification = new Notification();
        $notification->user_id = $data['user_id'];
        $notification->type = $data['type'];
        $notification->message = $data['message'];
        $notification->save();
    }
}

class SMSObserver implements Observer
{
    public function update($data)
    {
        // Send SMS notification
        $this->sendSMS($data);
    }

    private function sendSMS($data)
    {
        // SMS sending logic
        error_log("SMS sent to user {$data['user_id']}: {$data['message']}");
    }
}

// Notification Manager using Observer Pattern
class NotificationManager
{
    private static $instance = null;
    private $subject;

    private function __construct()
    {
        $this->subject = new NotificationSubject();
        
        // Attach all observers
        $this->subject->attach(new DatabaseObserver());
        $this->subject->attach(new EmailObserver());
        // Uncomment to enable SMS
        // $this->subject->attach(new SMSObserver());
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new NotificationManager();
        }
        return self::$instance;
    }

    public function sendNotification($userId, $type, $message)
    {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $this->subject->notify($data);
    }

    public function notifyDueDate($userId, $bookTitle, $dueDate)
    {
        $message = "Reminder: Your borrowed book '{$bookTitle}' is due on {$dueDate}.";
        $this->sendNotification($userId, 'due_date', $message);
    }

    public function notifyOverdue($userId, $bookTitle, $daysOverdue, $fineAmount)
    {
        $message = "Your book '{$bookTitle}' is {$daysOverdue} days overdue. Fine: LKR {$fineAmount}";
        $this->sendNotification($userId, 'overdue', $message);
    }

    public function notifyReservationAvailable($userId, $bookTitle)
    {
        $message = "Good news! The book '{$bookTitle}' you reserved is now available.";
        $this->sendNotification($userId, 'reservation_available', $message);
    }
}

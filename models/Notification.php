<?php

require_once 'BaseModel.php';

class Notification extends BaseModel
{
    public $id;
    public $user_id;
    public $type;
    public $message;
    public $is_read;
    public $created_at;

    protected function getTableName()
    {
        return 'notifications';
    }

    public function __construct()
    {
        parent::__construct();
    }

    protected function addNewRec()
    {
        $param = array(
            ':user_id' => $this->user_id,
            ':type' => $this->type,
            ':message' => $this->message
        );
        
        return $this->pm->run(
            "INSERT INTO notifications (user_id, type, message, is_read, created_at) 
             VALUES (:user_id, :type, :message, 0, NOW())",
            $param
        );
    }

    protected function updateRec()
    {
        $param = array(
            ':is_read' => $this->is_read,
            ':id' => $this->id
        );
        
        return $this->pm->run(
            "UPDATE notifications SET is_read = :is_read WHERE id = :id",
            $param
        );
    }

    public function save()
    {
        return $this->addNewRec();
    }

    public function markAsRead($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("UPDATE notifications SET is_read = 1 WHERE id = :id", $param);
    }

    public function markAllAsRead($userId)
    {
        $param = array(':user_id' => $userId);
        return $this->pm->run("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0", $param);
    }

    public function getNotificationById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run(
            "SELECT n.*, u.fullname, u.email 
             FROM notifications n 
             JOIN users u ON n.user_id = u.id 
             WHERE n.id = :id",
            $param,
            true
        );
    }

    public function getAllNotifications()
    {
        return $this->pm->run(
            "SELECT n.*, u.fullname, u.email 
             FROM notifications n 
             JOIN users u ON n.user_id = u.id 
             ORDER BY n.created_at DESC"
        );
    }

    public function getByUserId($userId)
    {
        $param = array(':user_id' => $userId);
        return $this->pm->run(
            "SELECT * FROM notifications 
             WHERE user_id = :user_id 
             ORDER BY created_at DESC",
            $param
        );
    }

    public function getUnreadByUserId($userId)
    {
        $param = array(':user_id' => $userId);
        return $this->pm->run(
            "SELECT * FROM notifications 
             WHERE user_id = :user_id AND is_read = 0 
             ORDER BY created_at DESC",
            $param
        );
    }

    public function getUnreadCount($userId)
    {
        $param = array(':user_id' => $userId);
        $result = $this->pm->run(
            "SELECT COUNT(*) as count 
             FROM notifications 
             WHERE user_id = :user_id AND is_read = 0",
            $param,
            true
        );
        return $result['count'];
    }

    public function getByType($userId, $type)
    {
        $param = array(':user_id' => $userId, ':type' => $type);
        return $this->pm->run(
            "SELECT * FROM notifications 
             WHERE user_id = :user_id AND type = :type 
             ORDER BY created_at DESC",
            $param
        );
    }

    public function deleteRec($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("DELETE FROM notifications WHERE id = :id", $param);
    }

    public function getRecentNotifications($userId, $limit = 10)
    {
        $param = array(':user_id' => $userId, ':limit' => $limit);
        return $this->pm->run(
            "SELECT * FROM notifications 
             WHERE user_id = :user_id 
             ORDER BY created_at DESC 
             LIMIT " . (int)$limit,
            array(':user_id' => $userId)
        );
    }

    public function getNotificationStats($userId = null)
    {
        if ($userId) {
            $param = array(':user_id' => $userId);
            return $this->pm->run(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN type = 'due_date' THEN 1 ELSE 0 END) as due_date,
                    SUM(CASE WHEN type = 'overdue' THEN 1 ELSE 0 END) as overdue,
                    SUM(CASE WHEN type = 'reservation_available' THEN 1 ELSE 0 END) as reservation,
                    SUM(CASE WHEN type = 'general' THEN 1 ELSE 0 END) as general
                FROM notifications
                WHERE user_id = :user_id",
                $param,
                true
            );
        } else {
            return $this->pm->run(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN type = 'due_date' THEN 1 ELSE 0 END) as due_date,
                    SUM(CASE WHEN type = 'overdue' THEN 1 ELSE 0 END) as overdue,
                    SUM(CASE WHEN type = 'reservation_available' THEN 1 ELSE 0 END) as reservation,
                    SUM(CASE WHEN type = 'general' THEN 1 ELSE 0 END) as general
                FROM notifications",
                array(),
                true
            );
        }
    }
}

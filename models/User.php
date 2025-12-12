<?php

require_once 'BaseModel.php';

class User extends BaseModel
{
    public $username;
    public $permission;
    public $is_active;
    private $email;
    private $password;

    function getTableName()
    {
        return 'users';
    }

    protected function addNewRec()
    {
        // Hash the password before storing it
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $param = array(
            ':username' => $this->username,
            ':password' => $this->password,
            ':permission' => $this->permission,
            ':email' => $this->email,
            ':is_active' => $this->is_active
        );

        return $this->pm->run("INSERT INTO " . $this->getTableName() . "(username, password,permission,email,is_active) values(:username, :password,:permission,:email,:is_active)", $param);
    }

    protected function updateRec()
    {
        // Check if the new username or email already exists (excluding the current user's record)
        $existingUser = $this->getUserByUsernameOrEmailWithId($this->username, $this->email, $this->id);
        if ($existingUser) {
            // Handle the error (return an appropriate message or throw an exception)
            return false; // Or throw an exception with a specific error message
        }

        // Hash the password if it is being updated
        if (!empty($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }

        $param = array(
            ':username' => $this->username,
            ':password' => $this->password,
            ':permission' => $this->permission,
            ':email' => $this->email,
            ':is_active' => $this->is_active,
            ':id' => $this->id
        );
        return $this->pm->run(
            "UPDATE " . $this->getTableName() . " 
            SET 
                username = :username, 
                password = :password,
                permission = :permission,  
                email = :email,
                is_active = :is_active
            WHERE id = :id",
            $param
        );
    }

    public function getUserByUsernameOrEmailWithId($username, $email, $excludeUserId = null)
    {
        $param = array(':username' => $username, ':email' => $email);

        $query = "SELECT * FROM " . $this->getTableName() . " 
                  WHERE (username = :username and email = :email)";

        if ($excludeUserId !== null) {
            $query .= " AND id != :excludeUserId";
            $param[':excludeUserId'] = $excludeUserId;
        }

        $result = $this->pm->run($query, $param);

        return $result; // Return the user if found, or false if not found
    }

    public function getUserByUsernameOrEmail($username, $email)
    {
        $param = array(
            ':username' => $username,
            ':email' => $email
        );

        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE username = :username AND email = :email";
        $result = $this->pm->run($sql, $param);

        if (!empty($result)) {  // Check if the array is not empty
            $user = $result[0]; // Assuming the first row contains the user data
            return $user;
        } else {
            return null;
        }
    }


    function createUser($username, $password, $permission, $email, $is_active = 1)
    {
        $userModel = new User();

        // Check if username or email already exists
        $existingUser = $userModel->getUserByUsernameOrEmail($username, $email);
        if ($existingUser) {
            // Handle the error (return an appropriate message or throw an exception)
            return false; // Or throw an exception with a specific error message
        }

        $user = new User();
        $user->username = $username;
        $user->password = $password;
        $user->permission = $permission;
        $user->email = $email;
        $user->is_active = $is_active;
        $user->addNewRec();

        if ($user) {
            return $user; // User created successfully
        } else {
            return false; // User creation failed (likely due to database error)
        }
    }

    function updateUser($id, $username, $password, $permission, $email, $is_active = 1)
    {
        $userModel = new User();

        // Check if username or email already exists
        $existingUser = $userModel->getUserByUsernameOrEmailWithId($username, $email, $id);
        if ($existingUser) {
            // Handle the error (return an appropriate message or throw an exception)
            return false; // Or throw an exception with a specific error message
        }

        $user = new User();
        $user->id = $id;
        $user->username = $username;
        $user->password = $password;
        $user->permission = $permission;
        $user->email = $email;
        $user->is_active = $is_active;
        $user->updateRec();

        if ($user) {
            return true; // User udapted successfully
        } else {
            return false; // User update failed (likely due to database error)
        }
    }

    function deleteUser($id)
    {
        $user = new User();
        $user->deleteRec($id);

        if ($user) {
            return true; // User udapted successfully
        } else {
            return false; // User update failed (likely due to database error)
        }
    }

    public function getLastInsertedUserId()
    {
        $result = $this->pm->run('SELECT MAX(id) as lastInsertedId FROM users', null, true);
        return $result['lastInsertedId'] ?? 100;
    }


    public function getUserById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("SELECT * FROM users WHERE id = :id", $param, true);
    }
}

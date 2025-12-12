<?php
// Define an abstract class named BaseModel
abstract class BaseModel
{
    // Protected property to hold the database manager instance
    protected $pm;

    // Public properties representing common fields in database tables
    public $id;
    public $created_at;
    public $updated_at;

    // Constructor method to initialize the database manager instance
    public function __construct()
    {
        // Access the AppManager to get the database manager instance
        $this->pm = AppManager::getPM();
    }

    // Abstract method to get the table name associated with the model (to be implemented by child classes)
    abstract protected function getTableName();

    // Abstract method to add a new record to the database (to be implemented by child classes)
    abstract protected function addNewRec();

    // Abstract method to update an existing record in the database (to be implemented by child classes)
    abstract protected function updateRec();

    // Method to retrieve all records from the associated table
    public function getAll($order = "ASC", $col = "id")
    {
        return $this->pm->run("SELECT * FROM " . $this->getTableName() ." ORDER BY `$col` $order");
    }

    // Method to retrieve all active records from the associated table
    public function getAllActive()
    {
        return $this->pm->run("SELECT * FROM " . $this->getTableName() . " WHERE is_active = 1");
    }

    // Method to retrieve a record by its ID from the associated table
    public function getById($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("SELECT * FROM " . $this->getTableName() . " WHERE id = :id", $param, true);
    }

    // Method to retrieve all records based on a specific column and value from the associated table
    public function getAllByColumnValue($column, $value, $additional = "")
    {
        return $this->pm->run("SELECT * FROM " . $this->getTableName() . " WHERE $column = '$value' $additional");
    }
    

    // Method to save a record (either update an existing record or add a new one)
    public function save()
    {
        if (isset($this->id) && $this->id > 0) {
            return $this->updateRec();
        } else {
            return $this->addNewRec();
        }
    }

    // Method to delete a record by its ID from the associated table
    public function deleteRec($id)
    {
        $param = array(':id' => $id);
        return $this->pm->run("DELETE FROM " . $this->getTableName() . " WHERE id = :id", $param);
    }
}

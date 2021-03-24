<?php

namespace App\classes;
use PDOException;
use App\classes\Db;

class AllFunctionalities extends Db
{

    public function update($table, $column, $column_ans, $identifier, $identifier_ans)
    {
        try {
            $query = "UPDATE $table SET $column =? WHERE $identifier = ?";
            $result = $this->connect()->prepare($query);
            return $result->execute([$column_ans, $identifier_ans]);
    
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function updateOr($table, $column, $column_ans, $identifier1, $identifier2, $identifier_ans)
    {
        try {
            $query = "UPDATE $table SET $column =? WHERE $identifier1 = ? OR $identifier2 = ?";
            $result = $this->connect()->prepare($query);
            return $result->execute([$column_ans, $identifier_ans, $identifier_ans]);
    
        } catch (PDOException $e) {
            showError($e);
        }
    }



    // UPDATE MULTIPLE PARAMETER DYNAMICALLY

    public function updateMultiple(array $data, string $table, string $identifier, string $ref)
    {
        try {
            $implodeKey = implode('=?, ', array_keys($data));
            $implodeKey = rtrim($implodeKey, ", $ref");
            $implodeValue = array_values($data);
            $sql = "UPDATE $table SET $implodeKey WHERE $identifier =?";
            return $this->connect()->prepare($sql)->execute($implodeValue);
        
        } catch (PDOException $e) {
            showError($e);
        }
    }

    /**
     * Undocumented function
     *
     * @param array $data - the array from the $_POST
     * @param string $table
     * @param [type] $identifier this is either id or email or username
     * @return void
     */

    
    public function updateMultiplePOST(array $data, string $table, $identifier)
    {
        try {
            if (isset($data['submit'])) {
                unset($data['submit']); // remove submit if present
            }

            $id = $data[$identifier]; // store $data['id]
            unset($data[$identifier]); // unset id
            $implodeKey = implode('=?, ', array_keys($data));

            $data[$identifier] = $id;
            $implodeValue = array_values($data);

            $sql = "UPDATE $table SET $implodeKey=? WHERE $identifier =?";
            // example - 'UPDATE register SET title=?, first_name=?, second_name=? WHERE id =?'
            return $this->connect()->prepare($sql)->execute($implodeValue);

        } catch (PDOException $e) {
            showError($e);
        }
    }
}

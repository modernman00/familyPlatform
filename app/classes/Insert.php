<?php

namespace App\classes;

use Exception;
use PDOException;

class Insert extends Db
{
    public function submitForm($table, $field)
    {
        try {

            // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($field);
            $col = implode(', ', $key);
            // extract values
            $value = array_values($field);
            $placeholder = implode(', :', $key);
            // prep statement using placeholder :name
            $stmt = "INSERT INTO $table ($col) VALUES (:$placeholder)";
            $query = $this->connect()->prepare($stmt);
            if (!$query) {
                throw new Exception("Not able to insert data", 1);
            }
            foreach ($field as $keys => $values) {
                $query->bindValue(":$keys", $values);
            }
            $outcome = $query->execute();
            if (!$outcome) {
                throw new Exception("Not able to execute data", 1);
            }
        } catch (PDOException $e) {
            showError($e);
        } catch (\Throwable $th) {
            showError($e);
        }

    }

    public function selectForm($table, $column)
    {
        try {
            // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($column);
            $col = implode(', ', $key);
            // extract values
            $value = array_values($column);
            $val = implode(', :', $value);
            // prep statement using placeholder :name
            $stmt = "INSERT INTO $table ($col) VALUES (:$val)";
            $query = $this->connect()->prepare($stmt);
            foreach ($column as $key => $value) {
                $query->bindParam(":$key", $value);
            }
            return $query->execute();
        } catch (PDOException $e) {
            showError($e);
        } catch (\Throwable $e) {
            showError($e);
        }
        //
    }


    public function insertDataRedirect($field, $de, $redirect)
    {
        try {
            $implodeDBCol = implode(', ', array_keys($field)); //remember, $field is an array
            $implodePlaceholder = implode(', :', array_keys($field));
            $sql = "INSERT INTO $de ($implodeDBCol) VALUES (:$implodePlaceholder)";
            $stmt = $this->connect()->prepare($sql);
            foreach ($field as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmtExec = $stmt->execute();
            if ($stmtExec) {
                header($redirect);
            }
            return $stmtExec;
        } catch (PDOException $e) {
            showError($e);
        }
    }
}

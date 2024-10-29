<?php

namespace App\classes;

use Exception;
use PDOException;

use App\classes\Transaction;

class Insert extends Db
{
    public function submitForm($table, $field)
    {
        try {

            // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($field);
            $col = implode(', ', $key);
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
            // $last_id = $query->lastInsertId();
            if (!$outcome) {
                throw new Exception("Not able to execute data", 1);
            }
        } catch (PDOException $e) {
            showError($e);
        } catch (\Throwable $e) {
            showError($e);
        }
    }

    public static function submitForm2($table, $field)
    {
        Transaction::beginTransaction();
        try {


            // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($field);
            $col = implode(', ', $key);
            $placeholder = implode(', :', $key);

            // prep statement using placeholder :name
            $stmt = "INSERT INTO $table ($col) VALUES (:$placeholder)";

            $query = parent::connect2()->prepare($stmt);
            if (!$query) {
                throw new Exception("Not able to insert data", 1);
            }
            foreach ($field as $keys => $values) {
                $query->bindValue(":$keys", $values);
            }
            $outcome = $query->execute();
            if (!$outcome) {
                http_response_code(406); // sets the response to 406
                echo http_response_code(); // echo the new response code
                throw new Exception("Not able to execute data", 1);
            }

            $_SESSION['LAST_INSERT_ID'] = Transaction::lastId();

            Transaction::commit();

            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        } catch (\Throwable $e) {
            Transaction::rollback();
            showError($e);
        }
    }

    /**
     * 
     * @param mixed $table - database table
     * @param mixed $field - post array
     * @param mixed $lastIdCol the column you want to return the last id - e.g id, no, eventNo 
     * @return mixed 
     */

    public static function submitFormDynamicLastId($table, $field, $lastIdCol)
    {
        try {
            // Prepare the SQL statement
            $columns = implode(', ', array_keys($field));
            $placeholders = ':' . implode(', :', array_keys($field));
            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";

            $connection = parent::connect2();
            
            $stmt = $connection->prepare($query);


            foreach ($field as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $outcome = $stmt->execute();

            if (!$outcome) {
                msgException(406, "Unable to execute the query.");
            }

            $lastInsertedId = $connection->lastInsertId($lastIdCol);

            $dynamicTable = strtoupper($table);
            $_SESSION["LAST_INSERT_ID_$dynamicTable"] = $lastInsertedId;

            // msgSuccess(200, $lastInsertedId);

            return $lastInsertedId;
        } catch (PDOException $e) {
            showError($e);
        } catch (\Throwable $e) {
            showError($e);
        }
    }


    /**
     * 
     * @param mixed $table THE TABLE
     * @param mixed $field  THE POST ARRAY
     * @return mixed 
     * @throws \PDOException 
     */

    public static function submitFormDynamic($table, $field)
    {

        try {
            $DYNAMIC = strtoupper($table);

            // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($field);
            $col = implode(', ', $key);
            $placeholder = implode(', :', $key);

            // prep statement using placeholder :name
            $stmt = "INSERT INTO $table ($col) VALUES (:$placeholder)";

            $query = parent::connect2()->prepare($stmt);
            if (!$query) {
                throw new Exception("Not able to insert data", 1);
            }
            foreach ($field as $keys => $values) {
                $query->bindValue(":$keys", $values);
            }
            $outcome = $query->execute();
            if (!$outcome) {
                msgException(406, "Unable to execute the query.");
            }

            $lastId = parent::connect2()->lastInsertId();

            if(!$lastId) msgException(
                errCode: 406, 
                msg: "Unable to connect to"
            );

            $_SESSION["LAST_INSERT_ID_$DYNAMIC"] = $lastId;

            msgSuccess(200,  $lastId);

            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        } catch (\Throwable $e) {
            Transaction::rollback();
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
                exit();
            }
            return $stmtExec;
        } catch (PDOException $e) {
            showError($e);
        }
    }
}

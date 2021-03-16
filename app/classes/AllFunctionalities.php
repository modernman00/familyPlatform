<?php

namespace App\classes;

use PDO;
use PDOException;
use Exception;
use App\classes\Db;

class AllFunctionalities extends Db
{

    public function submitForm($table, $field)
    {
        try {
            // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($field);
            $col = implode(', ', $key);
            // extract values
            // $value = array_values($field);
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
            return $query->execute();
        } catch (\PDOException $e) {
            showError($e);
            echo $e->getLine();
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
        //
    }

    // SELECT AND COUNT
    public function select_count_double($de, $dev, $from, $dev2, $from2)
    {
        try {
            $query = "SELECT * FROM $de WHERE $dev > ? AND $dev2 = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$from, $from2]);
            return $result->rowCount();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_count_double_dynamic($de, $col, $value)
    {
        try {
            $query = "SELECT * FROM $de WHERE $col[0] = ? AND $col[1] = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$value[0], $value[1]]);
            return $result->rowCount();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_count($de, $dev, $from)
    {
        try {
            $query = "SELECT * FROM $de WHERE $dev = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$from]);
            return $result->rowCount();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    // SELECT DISTINCT

    public function selectDistinct($firstselect, $secondselect, $from)
    {
        try {
            $query = "SELECT DISTINCT $firstselect, $secondselect FROM $from";
            $result = $this->connect()->prepare($query);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    // DELETE A COLUMN

    public function delete_column($de, $dev, $from)
    {
        try {
            $query = "DELETE * FROM $de WHERE $dev = ? LIMIT 1";
            $result = $this->connect()->prepare($query);
            return $result->execute([$from]);
            
        } catch (PDOException $e) {
            showError($e);
        }
    }



    public function delete2($de, $dev, $from, $redirect)
    {

        try {
            $query = "DELETE FROM $de WHERE $dev = ? LIMIT 1";
            $result = $this->connect()->prepare($query);
            $outcome = $result->execute([$from]);
    
            if ($outcome) {
                header("Location: $redirect");
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }


    public function deleteUpdate($table, $datetime, $id, $id_ans, $redirect)
    {
        try {
            $query = "UPDATE $table SET deleted_at =? , status ='deleted' WHERE $id = ? LIMIT 1";
            $result = $this->connect()->prepare($query);
            $outcome = $result->execute([$datetime, $id_ans]);
            if ($outcome) {
                header("Location: $redirect");
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }
    // be careful with this function -> didnt work on the last project -> check carefully before USE.

    public function selectall_join($table, $table2, $para)
    {

        try {
            $query = "SELECT * FROM $table INNER JOIN $table2 ON $table2.$para = $table.$para";
            $result = $this->connect()->prepare($query);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function selectall($table)
    {

        try {
            $query = "SELECT * FROM $table ORDER BY date_created DESC";
            $result = $this->connect()->prepare($query);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            showError($e);
        }
    }


    public function selectCountAll($table)
    {

        try {
            $query = "SELECT COUNT(*) FROM $table";
            return $this->connect()->query($query)->fetchColumn();
        } catch (PDOException $e) {
            showError($e);
        }
    }



    // SELECT A COLUMN FROM THE TABLE

    public function selectAllColumn($table, $column)
    {

        try {
            $query = "SELECT $column FROM $table";
            $result = $this->connect()->prepare($query);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    // SELECT ALL AND JOIN TWO TABLES
    public function selectall2($table, $table2, $para)
    {
        try {
            $query = "SELECT * FROM $table INNER JOIN $table2 ON $table.$para = $table2.$para";
            $result = $this->connect()->prepare($query);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_join($table, $table2, $para, $ref)
    {

        try {
            $query = "SELECT * FROM $table INNER JOIN $table2 ON $table2.$para = $table.$para WHERE $table.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$ref]);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            showError($e);
        }
    }



    // select all data function
    public function select($table)
    {
        try {
            $sql = "SELECT * FROM $table";
            $result = $this->connect()->query($sql);
            if ($result->rowCount() > 0) { // rowCount is an inbuilt function
                while ($row = $result->fetch()) {
                    $data[] = $row; //took all the record and put them into an empty $array('' => , );
                    ;
                }
                return $data; //this is the data array
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_from($de, $dev, $from)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $dev = ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from]);
            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_from_withLimit($de, $dev, $from, $orderBy, $direction, $limit)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $dev = ? ORDER BY $orderBy $direction LIMIT $limit";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from]);
            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function selectIsNot($de, $dev, $from)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $dev != ? ORDER BY id DESC";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from]);
            return $result->fetchAll();
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function selectFromIsNot($de, $dev, $from, $from2, $from3, $limit, $offset)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $dev != ? AND $dev != ? AND $dev != ? ORDER BY id DESC LIMIT $limit OFFSET $offset ";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from, $from2, $from3]);
            return $result->fetchAll();
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_from_greaterThan($de, $dev, $from)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $dev > ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from]);
            return $result->fetchAll();
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_from_doubleAnd($de, $dev, $from, $dev2, $from2)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $dev > ? AND $dev2 = ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from, $from2]);
            return $result->fetchAll();
            
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_from_doubleOr($de, $dev, $from, $dev2, $from2)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $dev > ? OR $dev2 = ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from, $from2]);
            return $result->fetchAll();
            
        } catch (PDOException $e) {
            showError($e);
        }
    }


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




    public function insertData($field, $de, $redirect)
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

    public function insertData_NoRedirect($field, $de)
    {
        try {
            $implodeDBCol = implode(', ', array_keys($field)); //remember, $field is an array
            $implodePlaceholder = implode(', :', array_keys($field));
            $sql = "INSERT INTO $de ($implodeDBCol) VALUES (:$implodePlaceholder)";
            $stmt = $this->connect()->prepare($sql);
            foreach ($field as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    //SELECT FROM THE DB, CHECK IF THE PROFILE EXIST AND INSERT DATA
    public function select_column($de, $id, $id_ans, $data)
    {
        try {
            $sql = "SELECT * FROM $de WHERE $id = ?";
            $stmt = $this->connectSql()->prepare($sql);
            $stmt->bind_param("s", $id_ans);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows < 1) {
                $this->insertData_NoRedirect($data, $de);
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }



    public function insert_direct($des, $field, $value, $arrayCombined, $redirect)
    {
        try {
            $sql = "INSERT INTO $des ($field) VALUES (:$value)";
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute($arrayCombined);
            $redirect;
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

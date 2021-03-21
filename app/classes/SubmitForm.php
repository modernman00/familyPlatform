<?php 

namespace App\classes;


class SubmitForm extends Db 
{

    static public function submitForm($table, $field)
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
}
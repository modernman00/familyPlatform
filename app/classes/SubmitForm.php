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
            $query = self::connect2()->prepare($stmt);
            if (!$query) {
                http_response_code(417);
                throw new \Exception("Not able to insert data", 1);
            }
            foreach ($field as $keys => $values) {
               if(!$query->bindValue(":$keys", $values)){
                   throw new \Exception("Not able to insert data");
               } 
            }
            return $query->execute();
        } catch (\PDOException $e) {
            showError($e);
        } catch (\Throwable $th) {
              showError($th);
        }
        //
    }
}
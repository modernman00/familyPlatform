<?php

namespace App\classes;

class Select extends Db
{
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
            $stmt = "SELECT * FROM $table ($col) VALUES (:$val)";
            $query = $this->connect()->prepare($stmt);
            foreach($column as $key => $value){
                $query->bindParam(":$key", $value);
            }
            return $query->execute();

        } catch (\PDOException $e) {
            echo $e->getMessage();
        } catch (\Throwable $th) {
           echo $e->getMessage();
        }
       
       
        // 


    }

 
}

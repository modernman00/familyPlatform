<?php

namespace App\classes;

class Insert extends Db
{
    public function submitForm($table, $array)
    {
        try {
        
             // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($array);
            $col = implode(', ', $key);
            // extract values
            $value = array_values($array);
            $val = implode(', :', $value);

            // prep statement using placeholder :name
            $stmt = "SELECT * FROM $table ($col) WHERE email  ";
            $query = $this->connect()->prepare($stmt);
            foreach($array as $key => $value){
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

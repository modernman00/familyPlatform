<?php

namespace App\classes;

use App\classes\Db;
use \PDOException;

class Update extends Db
{

    function __construct(public string $table) {}

    public function updateTable ($column, $column_ans, $identifier, $identifier_ans){
        try { $query = "UPDATE $this->table SET $column =? WHERE $identifier = ?";
        $result = parent::connect2()->prepare($query);
        $result ->execute([$column_ans, $identifier_ans]);
        return $result;
        }
        catch (PDOException $e){echo $e->getMessage(), PHP_EOL;}
      }

   
}

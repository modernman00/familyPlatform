<?php

namespace App\classes;

use App\classes\Db;
use \PDOException;

class Update extends Db
{

  public function __construct(public string $table)
  {
  }

  public function updateTable($column, $columnAnswer, $identifier, $identifierAnswer)
  {
    try {
      $query = "UPDATE $this->table SET $column =? WHERE $identifier = ?";
      $result = parent::connect2()->prepare($query);
      $result->execute([$columnAnswer, $identifierAnswer]);
      return $result;
    } catch (PDOException $e) {
      echo $e->getMessage(), PHP_EOL;
    }
  }
}

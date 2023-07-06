<?php

namespace App\classes;

use App\classes\Db;
use \PDOException;

class Update extends Db
{

  public function __construct(public string $table)
  {
  }

  /**
   * @param array|false|null|string $columnAnswer
   *
   * @psalm-param 'token' $column
   * @psalm-param 'id' $identifier
   */
  public function updateTable(string $column, array|string|false|null $columnAnswer, string $identifier, string $identifierAnswer)
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

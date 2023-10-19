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
    public function updateTable(string $column, string $columnAnswer, string $identifier, string $identifierAnswer)
    {
        try {
            $query = "UPDATE $this->table SET $column =? WHERE $identifier = ?";
            $result = parent::connect2()->prepare($query);
            $result->execute([$columnAnswer, $identifierAnswer]);
            return $result;
        } catch (PDOException $e) {
            showError($e);
            PHP_EOL;
        }
    }

    public function updateTableMulti(string $column, string $columnAnswer, array $identifiers)
    {
        try {
            // Build the SET clause for the column to update
            $setClause = "$column = ?";

            // Build the WHERE clause for each identifier
            $whereClause = '';
            $params = [$columnAnswer];

            foreach ($identifiers as $identifier => $value) {
                if ($whereClause !== '') {
                    $whereClause .= ' AND ';
                }
                $whereClause .= "$identifier = ?";
                $params[] = $value;
            }

            // Construct the full SQL query
            $query = "UPDATE $this->table SET $setClause WHERE $whereClause";

            $result = parent::connect2()->prepare($query);

            $result->execute($params);
            return $result;
        } catch (PDOException $e) {
            showError($e);
            PHP_EOL;
        }
    }

    public function updateTwoColumns(array $columns, array $columnAnswers, array $identifiers)
    {
        try {
            // Build the SET clause for the columns to update
            $setClause = implode(' = ?, ', $columns) . ' = ?';

            // Build the WHERE clause for each identifier
            $whereClause = '';
            $params = array_merge($columnAnswers, array_values($identifiers));

            foreach ($identifiers as $identifier => $value) {
                if ($whereClause !== '') {
                    $whereClause .= ' AND ';
                }
                $whereClause .= "$identifier = ?";
            }

            // Construct the full SQL query
            $query = "UPDATE $this->table SET $setClause WHERE $whereClause";

            $result = parent::connect2()->prepare($query);
            $result->execute($params);
            return $result;
        } catch (PDOException $e) {
            showError($e);
            PHP_EOL;
        }
    }


    public function updateMultiplePOST(array $data, string $identifier): bool
    {
        try {
            if (isset($data['submit'])) {
                unset($data['submit']); // remove submit if present
            }

            // Check if the identifier exists in $data
            if (!isset($data[$identifier])) {
                msgException(406, "identifier does not exist");
            }
             $implodeValue = array_values($data);

            // $id = $data[$identifier]; // store $data['id]
            unset($data[$identifier]); // unset identifier
            $implodeKey = implode('=?, ', array_keys($data));

            // Construct the SQL query
            $sql = "UPDATE $this->table SET $implodeKey=? WHERE $identifier =?";

            $stmt = $this->connect()->prepare($sql);

            return $stmt->execute($implodeValue);

        } catch (PDOException $e) {
            showError($e);
            return false;
        }
    }
}

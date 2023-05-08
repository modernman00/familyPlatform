<?php

declare(strict_types=1);

namespace App\classes;

use PDO;
use PDOException;

class InnerJoin extends Db
{

    /**
     *
     * @param string $firstTable the first table in the array
     * @param string $para the id parameter
     * @param array $table table name
     * @param mixed $id id
     */
    public function joinParamOr(string $firstTable, string $para, array $table, mixed $id)
    {
        try {
            $buildInnerJoinQuery = array_map(
                fn ($tab) =>"
                INNER JOIN $tab ON $firstTable.$para = $tab.$para",
                $table
            );

            $innerQueryToString = join(" ", $buildInnerJoinQuery);

            $query = "SELECT * FROM $firstTable $innerQueryToString WHERE $firstTable.$para=? OR $table[0].$para = ?";

            $result = $this->connect()->prepare($query);

            $result->execute([$id, $id]);

            return $result->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
             showError($e);
  
        }
    }


    /**
     *
     * @param string $firstTable the first table in the array
     * @param string $para the id parameter
     * @param array $table table names `(do not include the first table)
     * @param string $paraWhere - the para to use with the WHERE keyword
     * @param mixed $bind bind variable
     */

    public function joinParam(string $firstTable, string $para, string $paraWhere, array $table, mixed $bind)
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " INNER JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ",   $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString WHERE $firstTable.$paraWhere = ?";
            $result = $this->connect()->prepare($query2);
            $result->execute([$bind]);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            showError($e);
            return false;
        }
    }

    public function joinAll(string $firstTable, string $para, array $table, string $orderBy): array
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " INNER JOIN $tab ON $firstTable.$para = $tab.$para", $table);
            $innerQueryToString = join(" ",   $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString ORDER BY $orderBy  DESC";
            $result = $this->connect()->prepare($query2);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            showError($e);
        }
    }

    /**
     * firstTable -> the first table in the array
     * para - id 
     * table -> the array of db tables
     * orderBy -> the input you want to order it by - date, age etc
     */

    public static function joinAll2(string $firstTable, string $para, array $table, string $orderBy): array
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " INNER JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ", $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString ORDER BY $orderBy  DESC";
            $result = self::connect2()->prepare($query2);
            $result->execute();
            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public static function joinAll4(string $firstTable, string $para, array $table, string $orderBy): array
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " RIGHT JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ", $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString ORDER BY $orderBy  DESC";
            $result = self::connect2()->prepare($query2);
            $result->execute();
            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
        }
    }


    public static function joinAll3(string $firstTable, string $para, array $table, string $orderBy)
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " INNER JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ",   $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString ORDER BY $orderBy  DESC";
            $result = self::connect2()->prepare($query2);
            $result->execute();
            $jsResult = $result->fetchAll(PDO::FETCH_OBJ);
            echo json_encode($jsResult, JSON_PRETTY_PRINT);
        } catch (PDOException $e) {
            showError($e);
        }
    }



    public function joinParamAnd(string $firstTable, string $para, array $table, mixed $id): array
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " INNER JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ",   $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString WHERE $firstTable.$para = ? AND $table[0].$para = ?";
            $result = $this->connect()->prepare($query2);
            $result->execute([$id, $id]);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            showError($e);
        }
    }
}

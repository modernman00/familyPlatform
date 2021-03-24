<?php
declare(strict_types =1);

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
     *
     * @return void
     */
    public function joinParamOr(string $firstTable, string $para, array $table, mixed $id) : array
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => "INNER JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ",   $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString WHERE $firstTable.$para = ? OR $table[0].$para = ?";
            $result = $this->connect()->prepare($query2);
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
     *
     * @return array
     */

    public function joinParam(string $firstTable, string $para, string $paraWhere, array $table, mixed $bind) : array
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
        }
    }

    public function joinAll(string $firstTable, string $para, array $table) : array
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " INNER JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ",   $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString";
            $result = $this->connect()->prepare($query2);
            $result->execute();
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            showError($e);
        }
    }

        public function joinParamAnd(string $firstTable, string $para, array $table, mixed $id) : array
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
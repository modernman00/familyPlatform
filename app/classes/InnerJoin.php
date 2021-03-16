<?php

namespace App\classes;

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
    public function index(string $firstTable, string $para, array $table, mixed $id)
    {
        try {
            $buildInnerJoinQuery = array_map(fn ($tab) => " INNER JOIN $tab ON $firstTable.$para = $tab.$para ", $table);
            $innerQueryToString = join(" ",   $buildInnerJoinQuery);
            $query2 = "SELECT * FROM $firstTable  $innerQueryToString WHERE $firstTable.$para = ? OR $table[0].$para = ?";
            $result = $this->connect()->prepare($query2);
            $result->execute([$id, $id]);
            return $result->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            showError($e);
        }
    }
}

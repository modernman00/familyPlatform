<?php

namespace App\classes;

use PDOException;
use App\classes\Db;

class Select extends Db
{
    /**
     * Undocumented function
     *
     * @param [type] $selection - the selection match(SELECT_OR, SELECT_AND, SELECT_ALL, SELECT_ONE, SELECT_GREATER, SELECT_GREATER_EQUAL, SELECT_COUNT_TWO, SELECT_COUNT_ONE, SELECT_COUNT_ALL, SELECT_DISTINCT, SELECT_AVERAGE, SELECT_AVERAGE_ALL, SELECT_SUM_ALL)
     * @param [type] $table
     * @param [type] $identifier1 (id) - where id = ?
     * @param [type] $identifier2 (email) - where email = ?
     * @param [type] $column use if selecting by column
     * @param [type] $orderBy (example) ORDER BY id DESC; use if you want order by
     *@param [type] $limit (example) LIMIT 5; use if you want order
     * @return string
     */
    public static function formAndMatchQuery($selection, $table, $identifier1 = null, $identifier2 = null, $column = null, $orderBy = null, $limit = null)
    {
        return match ($selection) {
            'SELECT_OR' => "SELECT * FROM $table WHERE $identifier1 =? OR $identifier2 = ? $orderBy $limit",
            'SELECT_AND' => "SELECT * FROM $table WHERE $identifier1 =? AND $identifier2 = ? $orderBy $limit",
            'SELECT_NOT' => "SELECT * FROM $table WHERE $identifier1 !=? AND $identifier2 = ? $orderBy $limit",
            'SELECT_NOT_AND' => "SELECT * FROM $table WHERE $identifier1 !=? AND $identifier2 != ? $orderBy $limit",
            'SELECT_NOT_OR' => "SELECT * FROM $table WHERE $identifier1 !=? OR $identifier2 != ? $orderBy $limit",
            'SELECT_ALL' => "SELECT * FROM $table $orderBy $limit",
            'SELECT_ONE' => "SELECT * FROM $table WHERE $identifier1 = ? $orderBy " .$limit,
            'SELECT_COL' => "SELECT $column FROM $table $orderBy $limit",
            'SELECT_GREATER' => "SELECT * FROM $table WHERE $identifier1 > ? $orderBy $limit",
            'SELECT_GREATER_EQUAL' => "SELECT * FROM $table WHERE $identifier1 > ? OR $identifier2 = ? $orderBy $limit",
            'SELECT_COUNT_TWO' => "SELECT * FROM $table WHERE $identifier1 = ? AND $identifier2 = ?",
            'SELECT_COUNT_ONE' => "SELECT * FROM $table WHERE $identifier1 = ?",
            'SELECT_COUNT_ALL' => "SELECT * FROM $table",
            'SELECT_DISTINCT' => "SELECT DISTINCT $identifier1, $identifier2 FROM $table $orderBy $limit",
            'SELECT_AVERAGE' => "SELECT AVG($column) FROM $table WHERE $identifier1 = ?",
            'SELECT_AVERAGE_ALL' => "SELECT AVG($column) as total FROM $table",
            'SELECT_SUM_ALL' => "SELECT SUM($column) as total FROM $table",
            default => null
        };
    }

    /**
     * 
     * @param string $table 
     * @param string $query SELECT * FROM account WHERE id = ? || SELECT * FROM $table WHERE $dev = ? AND $dev2 = ?
     * @param array|null $bind = ['woguns@ymail.com', "wale@loaneasyfinance.com"]; 
     * @return array|void 
     */

    public function selectFn(string $query, array $bind = null): string|array|int
    {
        try {
            $sql = $query;
            $result = $this->connect()->prepare($sql);
            $result->execute($bind);
            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
        }
    }

     public static function selectFn2(string $query, array $bind = null): string|array|int
    {
        try {
            $sql = $query;
            $result = self::connect2()->prepare($sql);
            $result->execute($bind);
            return $result->fetchAll();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $query - SELECT * FROM account WHERE id = ? || SELECT * FROM $table WHERE $dev = ? AND $dev2 = ?
     * @param array $bind = ['woguns@ymail.com', "wale@loaneasyfinance.com"];
     *
     * @return mixed
     */
    public function selectCountFn(string $query, array $bind = null): string|array|int
    {
        try {
            $sql = $query;
            $result = $this->connect()->prepare($sql);
            $result->execute($bind);
            return $result->rowCount();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function selectCountAll($table)
    {

        try {
            $query = "SELECT COUNT(*) FROM $table";
            return $this->connect()->query($query)->fetchColumn();
        } catch (PDOException $e) {
            showError($e);
        }
    }

    /**
     * 
     * @param array $array [selection, table, identifier1, identifier2(null), bind]
     * @param mixed $callback the Select function albeit in string example - selectCountFn, selectFn
     * @param string $switch to switch between ONE_IDENTIFIER or TWO_IDENTIFIERS
     * @return mixed 
     */

    public function combineSelect(array $array, $callback, string $switch)
    {
        try {

            $query = match($switch) {
                "ONE_IDENTIFIER" => $this->formAndMatchQuery(selection: $array['selection'], table: $array['table'], identifier1: $array['identifier1']),
                "TWO_IDENTIFIERS" => $this->formAndMatchQuery(selection: $array['selection'], table: $array['table'], identifier1: $array['identifier1'], identifier2: $array['identifier2']),
            };

            return $this->$callback($query, $array['bind']);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

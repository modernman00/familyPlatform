<?php

namespace App\classes;

use PDOException;

class Delete extends Db
{

    static function formAndMatchQuery($selection, $table, $identifier1 = null, $identifier2 = null, $column = null, $limit = null)
    {
        return match ($selection) {
            'DELETE_OR' => "DELETE FROM $table WHERE $identifier1 =? OR $identifier2 = ? $limit",
            'DELETE_AND' => "DELETE FROM $table WHERE $identifier1 =? AND $identifier2 = ? $limit",
            'DELETE_ALL' => "DELETE FROM $table $limit",
            'DELETE_ONE' => "DELETE FROM $table WHERE $identifier1 = ? $limit",
            'DELETE_COL' => "DELETE $column FROM $table $limit",
            'DELETE_UPDATE' => "UPDATE $table SET status ='deleted' WHERE $identifier1 = ? LIMIT 1",
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

    public static function deleteFn(string $query, array $bind = null): string|int
    {
        try {
            $sql = $query;
            $result = parent::connect2()->prepare($sql);
            return $result->execute($bind);
        } catch (PDOException $e) {
            showError($e);
        }
    }
}

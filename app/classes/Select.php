<?php

namespace App\classes;

use PDO;
use PDOException;
use App\classes\Db;

class Select extends Db
{
    public function selectForm($table, $column)
    {
        try {
             // EXTRACT THE KEY FOR THE COL NAME
            $key = array_keys($column);
            $col = implode(', ', $key);
            // extract values
            $value = array_values($column);
            $val = implode(', :', $value);

            // prep statement using placeholder :name
            $stmt = "SELECT * FROM $table ($col) VALUES (:$val)";
            $query = $this->connect()->prepare($stmt);
            foreach ($column as $key => $value) {
                $query->bindParam(":$key", $value);
            }
            return $query->execute();
        } catch (\PDOException $e) {
            showError($e);
        } catch (\Throwable $e) {
            showError($e);
        }


        //
    }

    public function selectAllSum($col, $alias, $table)
    {
        try {
            $sql = "SELECT SUM($col) as $alias
        FROM $table";
            $result = $this->connect()->prepare($sql);
            $result->execute();
            $outcome = $result->fetchAll();
            foreach ($outcome as $outcome) {
            }
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function selectSumCond($col, $alias, $table, $where, $whereAns)
    {
        try {
            $sql = "SELECT SUM($col) as $alias
        FROM $table WHERE $where = ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$whereAns]);
            $outcome = $result->fetchAll();
            foreach ($outcome as $outcome) {
            }
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function selectAllAvg($col, $alias, $table)
    {
        try {
            $sql = "SELECT AVG($col) as $alias
        FROM $table";
            $result = $this->connect()->prepare($sql);
            $result->execute([$table]);
            $outcome = $result->fetchAll();
            foreach ($outcome as $outcome) {
            }
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function selectAvgCond($col, $alias, $table, $where, $whereAns)
    {
        try {
            $sql = "SELECT AVG($col) as $alias
        FROM $table WHERE $where = ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$whereAns]);
            $outcome = $result->fetchAll();
            foreach ($outcome as $outcome) {
            }
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }



    public function select_from($table, $dev, $from)
    {
        try {
            $sql = "SELECT * FROM $table WHERE $dev = ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from]);
            $outcome = $result->fetchAll();
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function select_from_double($table, $dev, $from, $dev2, $from2)
    {
        try {
            $sql = "SELECT * FROM $table WHERE $dev = ? AND $dev2 = ?";
            $result = $this->connect()->prepare($sql);
            $result->execute([$from, $from2]);
            $outcome = $result->fetchAll();
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    // SELECT ALL - COLUMN
    public function selectAllColumn($table, $column)
    {

        try {
            $query = "SELECT $column FROM $table";
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }




    // SELECT AND COUNT

    public function select_count_double($table, $dev, $from, $dev2, $from2)
    {
        $query = "SELECT * FROM $table WHERE $dev > ? AND $dev2 = ?";
        $result = $this->connect()->prepare($query);
        $result->execute([$from, $from2]);
        $rowCount = $result->rowCount();
        return $rowCount;
    }

    public function select_count_double_dynamic($table, $col, $value)
    {
        $query = "SELECT * FROM $table WHERE $col[0] = ? AND $col[1] = ?";
        $result = $this->connect()->prepare($query);
        $result->execute([$value[0], $value[1]]);
        $rowCount = $result->rowCount();
        return $rowCount;
    }

    public function select_count($table, $dev, $from)
    {
        $query = "SELECT * FROM $table WHERE $dev = ?";
        $result = $this->connect()->prepare($query);
        $result->execute([$from]);
        $rowCount = $result->rowCount();
        return $rowCount;
    }

    // COUNT ALL

    public function selectCountAll($table, $dev)
    {
        $query = "SELECT $dev FROM $table";
        $result = $this->connect()->prepare($query);
        $result->execute();
        $rowCount = $result->rowCount();
        return $rowCount;
    }


    // SELECT DISTINCT

    public function selectDistinct($firstselect, $secondselect, $from)
    {
        try {
            $query = "SELECT DISTINCT $firstselect, $secondselect FROM $from";
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyCondition2($table, $table2, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			INNER JOIN $table2 ON $table.$para = $table2.$para
			WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    // JOIN MANY TABLES

    public function joinMany($table, $table2, $table3, $para)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para";
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinMany5($table, $table2, $table3, $table4, $table5, $para)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   ";
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinMany4($table, $table2, $table3, $table4, $para)
    {
        try {
            $query = "SELECT * FROM $table 
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   ";
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyArray($tableArr, $para)
    {
        try {
            $query = "";
            for ($i = 1; $i < count($tableArr); $i++) {
                $query = "SELECT * FROM $tableArr[0]
			INNER JOIN $tableArr[$i] ON $tableArr[0].$para = $tableArr[$i].$para";
            }
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    /**
     * join many tables with an id
     */

    public function joinManyCondition($table, $table2, $table3, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyCondition4($table, $table2, $table3, $table4, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }
    /**
     * Join 5 tables
     */

    public function joinManyCondition5($table, $table2, $table3, $table4, $table5, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyDecision5($table, $table2, $table3, $table4, $table5, $whereTable, $condition1, $condition2, $para, $condAns, $condAns2)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   WHERE $whereTable.$condition1 = ? AND $whereTable.$condition2 = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$condAns, $condAns2]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyCondition6($table, $table2, $table3, $table4, $table5, $table6, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   INNER JOIN $table6 ON $table.$para = $table6.$para
			   WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyCondition7($table, $table2, $table3, $table4, $table5, $table6, $table7, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   INNER JOIN $table6 ON $table.$para = $table6.$para
			   INNER JOIN $table7 ON $table.$para = $table7.$para
			   WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);

            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyCondition8($table, $table2, $table3, $table4, $table5, $table6, $table7, $table8, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   INNER JOIN $table6 ON $table.$para = $table6.$para
			   INNER JOIN $table7 ON $table.$para = $table7.$para
			   INNER JOIN $table8 ON $table.$para = $table8.$para
			   WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyCondition9($table, $table2, $table3, $table4, $table5, $table6, $table7, $table8, $table9, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   INNER JOIN $table6 ON $table.$para = $table6.$para
			   INNER JOIN $table7 ON $table.$para = $table7.$para
			   INNER JOIN $table8 ON $table.$para = $table8.$para
			   INNER JOIN $table9 ON $table.$para = $table9.$para
			   WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinMany9($table, $table2, $table3, $table4, $table5, $table6, $table7, $table8, $table9, $para)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   INNER JOIN $table6 ON $table.$para = $table6.$para
			   INNER JOIN $table7 ON $table.$para = $table7.$para
			   INNER JOIN $table8 ON $table.$para = $table8.$para
			   INNER JOIN $table9 ON $table.$para = $table9.$para";
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinManyCondition10($table, $table2, $table3, $table4, $table5, $table6, $table7, $table8, $table9, $table10, $para, $id)
    {
        try {
            $query = "SELECT * FROM $table
			   INNER JOIN $table2 ON $table.$para = $table2.$para
			   INNER JOIN $table3 ON $table.$para = $table3.$para
			   INNER JOIN $table4 ON $table.$para = $table4.$para
			   INNER JOIN $table5 ON $table.$para = $table5.$para
			   INNER JOIN $table6 ON $table.$para = $table6.$para
			   INNER JOIN $table7 ON $table.$para = $table7.$para
			   INNER JOIN $table8 ON $table.$para = $table8.$para
			   INNER JOIN $table9 ON $table.$para = $table9.$para
			   INNER JOIN $table10 ON $table.$para = $table10.$para
			   WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }



    /**
     * jOIN TWO TABLES
     * THE para is the key ('id')
     */

    public function joinTwo($table, $table2, $para)
    {

        try {
            $query = "SELECT * FROM $table INNER JOIN $table2 ON $table.$para = $table2.$para";
            $result = $this->connect()->prepare($query);
            $result->execute();
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function joinTwoCondition($table, $table2, $para, $id)
    {

        try {
            $query = "SELECT * FROM $table INNER JOIN $table2 ON $table.$para = $table2.$para WHERE $table.$para = ? OR $table2.$para = ?";
            $result = $this->connect()->prepare($query);
            $result->execute([$id, $id]);
            $outcome = $result->fetchAll(PDO::FETCH_ASSOC);
            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }
}

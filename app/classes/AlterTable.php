<?php

namespace App\classes;

use App\classes\allFunctionalities;

class AlterTable extends allFunctionalities
{


    /**
     * dataArr - this is array of the new column name to be updated ['payment', 'dob]
     */

    public function __construct(public string $table, public ?array $dataArray = null)
    {}

    public function addNewColArr($lastData)
    {
        for ($x = 0; $x < count($this->dataArray); $x++) {
            $newColumn = $this->dataArray[$x];
            $query =  "ALTER TABLE $this->table ADD $newColumn TEXT NULL AFTER $lastData";
            $lastData = $newColumn;
            $result = $this->connect()->prepare($query);
            $result->execute();
            // return $outcome;
        }
        // return $outcome;
    }


    public function addNewCol($colName, $dataType, $lastData)
    {
        $query =  " ALTER TABLE $this->table ADD $colName $dataType NULL AFTER $lastData";
        $result = $this->connect()->prepare($query);
        return $result->execute();
      
    }

    public function alterAutoIncrement($start)
    {
        $query =  "ALTER TABLE `$this->table` AUTO_INCREMENT = $start";
        $result = $this->connect()->prepare($query);
        return $result->execute();
        
    }
}

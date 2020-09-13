<?php

namespace App\classes;

use App\classes\allFunctionalities;

class AlterTable extends allFunctionalities 
{

    public $table;
    public $dataArray;

    /**
     * dataArr - this is array of the new column name to be updated ['payment', 'dob]
     */

    function __construct($table, $dataArr = null)
    {
        $this->table = $table;
        $this->dataArray = $dataArr;        
    }

    public function addNewColArr($lastData) 
    {
        for($x =0; $x < count($this->dataArray); $x++) {
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
		$outcome = $result->execute();
		return $outcome;
    }

    public function alterAutoIncrement($start) 
    {     
        $query =  "ALTER TABLE `$this->table` AUTO_INCREMENT = $start";
        $result = $this->connect()->prepare($query);
		$outcome = $result->execute();
		return $outcome;
    }

}

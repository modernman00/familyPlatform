<?php
declare(strict_types =1);

namespace App\model;

use App\classes\InnerJoin;
use App\classes\Select;

class SingleCustomerData extends InnerJoin
{
	public function getPersonalData()
	{
		try {
			$query = "SELECT DISTINCT lastName, firstName, id FROM personal";
			$result = $this->connect()->query($query);
			return $result->fetchAll(\PDO::FETCH_ASSOC);
		} catch (\PDOException $e) {
			showError($e);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param string $custId THE ID OF THE CUST
	 * @param array $table THE TABLE TO INNERJOIN
	 * ONLY USE THIS FUNCTION TO GET A SINGLE STATE
	 * @return array
	 */ 
	public function getCustomerData(string $custId, array $table) : array
	{ 
		try {
			$para = "id";
			// remove the first element of the array
			$firstTable = array_shift($table); 
			$query = $this->joinParamOr(firstTable:$firstTable, para:$para, table:$table, id:$custId);
			
			if (!$query) {
				throw new \Exception("Error Processing Request - query", 301);
			}

			foreach ($query as $query);
			unset($query[0]['password']);
			return $query;
		} catch (\PDOException $e) {
			showError($e);
		}
	}
		/**
	 * Undocumented function
	 *
	 * @param string $custId THE ID OF THE CUST
	 * @param array $table THE TABLE TO INNERJOIN
	 * WATCH OUT WHEN USING ON THE account table to remove the password key
	 * @return array
	 */ 
	public function getCustomerData2(string $custId, array $table) : array
	{ 
		try {
		
			$id = "id";
			$firstTable = array_shift($table); // remove the first element of the array
			$query = $this->joinParamOr(firstTable:$firstTable, para:$id, table:$table, id:$custId);
			
			if (!$query) {
				throw new \Exception("Error Processing Request - query", 1);
			}
			return $query;

		} catch (\PDOException $e) {
			showError($e);
		}
	}

	  public static function getCustById($custId, $table): array
    {
        $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: $table, identifier1: "id");
        return Select::selectFn2(query: $query, bind: [$custId]);
    }



}

<?php
declare(strict_types =1);

namespace App\model;

use App\classes\InnerJoin;

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

	public function getCustomerData(string $custId, array $table) : array
	{ 
		try {
		
			$id = "id";
			$firstTable = array_shift($table); // remove the first element of the array
			$query = $this->joinParamOr(firstTable:$firstTable, para:$id, table:$table, id:$custId);
			
			if (!$query) {
				throw new \Exception("Error Processing Request - query", 1);
			}

			foreach ($query as $query);
			unset($query['password']);
			return $query;
		} catch (\PDOException $e) {
			showError($e);
		}
	}

}

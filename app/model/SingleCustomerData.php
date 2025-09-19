<?php

declare(strict_types=1);

namespace App\model;

use App\classes\InnerJoin;
use App\classes\Select;

class SingleCustomerData extends InnerJoin
{
	public function getPersonalData()
	{
		try {
			$result = "SELECT DISTINCT lastName, firstName, id FROM personal";
			$result = $this->connect()->query($result);
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
	public function getCustomerData(string|int $custId, array $table): array | bool
	{
		try {
			$para = "id";
			// remove the first element of the array
			$firstTable = array_shift($table);
			$result = $this->joinParamOr(firstTable: $firstTable, para: $para, table: $table, id: $custId);

			$result ?? \msgException(401, 'result not found');

			$result = $result[0];
			unset($result['password']);


			// foreach ($result as $result);
			// unset($result[0]['password']);
			return $result;
		} catch (\PDOException $e) {
			showError($e);
			return false;
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
	public function getCustomerData2(string $custId, array $table): array | bool
	{
		try {

			$id = "id";
			$firstTable = array_shift($table); // remove the first element of the array
			$result = $this->joinParamOr(firstTable: $firstTable, para: $id, table: $table, id: $custId);

			if (!$result) {
				throw new \Exception("Error Processing Request - query", 1);
			}
			$result = $result[0];
			unset($result['password']);
			return $result;
		} catch (\PDOException $e) {
			showError($e);
			return false;
		}
	}

	public static function getCustById($custId, $table): array
	{
		$result = Select::formAndMatchQuery(selection: "SELECT_ONE", table: $table, identifier1: "id");
		return Select::selectFn2(query: $result, bind: [$custId]);
	}
}

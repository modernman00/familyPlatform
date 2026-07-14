<?php

declare(strict_types=1);

namespace App\model;

use App\classes\InnerJoin;
use App\classes\Select;


class SingleCustomerData extends InnerJoin
{


	/**
	 * Undocumented function
	 *
	 * @param string $custId THE ID OF THE CUST
	 * @param array $table THE TABLE TO INNERJOIN
	 * ONLY USE THIS FUNCTION TO GET A SINGLE STATE
	 * @return array
	 */
	public function getCustomerData(string $custId, array $table): array | bool
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


	public static function getCustById(int|string|null $custId, string $table): array|string|int
	{
		$result = Select::formAndMatchQuery(selection: "SELECT_ONE", table: $table, identifier1: "id");
		return Select::selectFn2(query: $result, bind: [$custId]);
	}
}

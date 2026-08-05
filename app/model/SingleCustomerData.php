<?php

declare(strict_types=1);

namespace App\model;

use Src\InnerJoin;
use Src\Select;


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
			$result = $this->joinParam(para: $para, paraWhere: $para, table: $table, bind: $custId);

			if (!is_array($result) || empty($result)) {
				\msgException(401, 'result not found');
				return false;
			}
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
		if ($result === null) {
			return [];
		}
		return Select::selectFn2(query: $result, bind: [$custId]);
	}
}

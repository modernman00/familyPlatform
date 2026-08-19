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
	 * @return array|bool
	 */
	public function getCustomerData(string $custId, array $table): array | bool
	{
		try {
			if (empty($table)) {
				return false;
			}

			// 1. Fetch the base data from the first table (usually 'personal')
			$firstTable = array_shift($table);
			$firstTable = \Src\Utility::checkInput($firstTable);
			$baseDataRows = \Src\SelectFn::selectAllRowsById($firstTable, 'id', $custId);

			if (!is_array($baseDataRows) || empty($baseDataRows)) {
				\msgException(401, 'result not found');
				return false;
			}

			$mergedData = $baseDataRows[0]; // Start with the first table's row

			// 2. Fetch from remaining tables and array_merge if data exists
			foreach ($table as $tab) {
				$tab = \Src\Utility::checkInput($tab);
				$rowData = \Src\SelectFn::selectAllRowsById($tab, 'id', $custId);
				
				if (is_array($rowData) && !empty($rowData)) {
					// We take the first row (since getCustomerData is for a single flat profile state)
					$mergedData = array_merge($mergedData, $rowData[0]);
				}
			}

			// 3. Ensure the primary ID is retained in case a JOINed table overwrote it with null/empty
			$mergedData['id'] = $baseDataRows[0]['id'];

			if (isset($mergedData['password'])) {
				unset($mergedData['password']);
			}

			return $mergedData;
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

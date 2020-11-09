<?php

namespace App\model;

use App\classes\Select;

class SingleCustomerData extends Select
{
	public function getPersonalData()
	{
		try {
			$query = "SELECT DISTINCT lastName, firstName, id FROM personal";
			$result = $this->connect()->query($query);
			$outcome = $result->fetchAll(\PDO::FETCH_ASSOC);
			return $outcome;
		} catch (\PDOException $e) {
			showError($e);
		}
	}

	public function getCustomerData($custNo)
	{
		try {
			$query = $this->joinManyCondition4('personal', 'siblings', 'contact', 'otherFamily', 'id', $custNo);
			foreach ($query as $query);
			unset($query['password']);
			return $query;
		} catch (\PDOException $e) {
			showError($e);
		}
	}
}

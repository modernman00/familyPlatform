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
			$query = $this->joinManyCondition7('personal', 'interest', 'siblings', 'contact', 'otherFamily', 'post',  'profile_pics', 'id', $custNo);
			echo 7;
			// printArr($query);

			if (!$query) {

				$query = $this->joinManyCondition6('personal', 'interest', 'siblings', 'contact', 'otherFamily', 'post', 'id', $custNo);
				echo 6;
				// printArr($query);

			}
			if (!$query) {

				$query = $this->joinManyCondition6('personal', 'interest', 'siblings', 'contact', 'otherFamily', 'profile_pics', 'id', $custNo);
				echo 6.1;
				// printArr($query);

			}
			if (!$query) {

				$query = $this->joinManyCondition5('personal', 'interest', 'siblings', 'contact', 'otherFamily', 'id', $custNo);
				echo 5;
				// printArr($query);
			}

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

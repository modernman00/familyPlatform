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

			if(!$query) {
				$query = $this->joinManyCondition6('personal', 'interest', 'contact', 'otherFamily', 'post',  'profile_pics', 'id', $custNo);
			}
			if (!$query) {
				$query = $this->joinManyCondition5('personal', 'interest', 'contact', 'otherFamily', 'post', 'id', $custNo);
			}
			if (!$query) {
				$query = $this->joinManyCondition5('personal', 'interest', 'contact', 'otherFamily', 'profile_pics', 'id', $custNo);
			}
			if (!$query) {
				$query = $this->joinManyCondition4('personal', 'interest', 'contact', 'otherFamily', 'id', $custNo);
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

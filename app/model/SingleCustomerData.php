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

	public function getCustomerData($custNo)
	{ 
		try {
			$table = ['interest', 'contact', 'otherFamily', 'post'];
			$id = "id";
			$firstTable = "personal";

			$query = $this->joinParamOr($firstTable, $id, $table, $custNo);

			// $query = $this->joinManyCondition7('personal', 'interest', 'siblings', 'contact', 'otherFamily', 'post',  'profile_pics', 'id', $custNo);

			// if(!$query) {
			// 	$query = $this->joinManyCondition6('personal', 'interest', 'contact', 'otherFamily', 'post',  'profile_pics', 'id', $custNo);
			// }
			// if (!$query) {
			// 	$query = $this->joinManyCondition5('personal', 'interest', 'contact', 'otherFamily', 'post', 'id', $custNo);
			// }
			// if (!$query) {
			// 	$query = $this->joinManyCondition5('personal', 'interest', 'contact', 'otherFamily', 'profile_pics', 'id', $custNo);
			// }
			// if (!$query) {
			// 	$query = $this->joinManyCondition4('personal', 'interest', 'contact', 'otherFamily', 'id', $custNo);
			// }
			
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

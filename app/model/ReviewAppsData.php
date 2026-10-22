<?php

namespace App\model;

use Src\InnerJoin;
class ReviewAppsData extends InnerJoin
{
	/**
	 * @return array|bool|null
	 */
	public function index()
	{
		try {
			$table = ['account', 'personal', 'contact', 'otherFamily', 'work'];
			return $this->joinParam(para: 'id', paraWhere: 'status', table: $table, bind: "new");
		} catch (\Throwable $th) {
			showError($th);
			return false;
		}	
	}

	/**
	 * @return array|bool|null
	 */
	public function getWithId(string | int $id) {
		try {
			$table = ['account', 'personal', 'contact', 'otherFamily', 'work'];
			return $this->joinParam(para: 'id', paraWhere: 'id', table: $table, bind: $id);
		} catch (\Throwable $th) {
			showError($th);
			return false;
		}	
	}
}

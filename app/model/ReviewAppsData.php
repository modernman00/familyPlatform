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
			$table = ['personal',  'contact', 'otherFamily', 'work'];
		return $this->joinParam(firstTable: 'account', para: 'id', paraWhere: 'status', table : $table, bind : "new" );
		} catch (\Throwable $th) {
			showError($th);
		}	
	}

	/**
	 * @return array|bool|null
	 */
	public function getWithId(string | int $id) {
		try {
			$table = ['personal',  'contact', 'otherFamily', 'work'];
		return $this->joinParam(firstTable: 'account', para: 'id', paraWhere: 'id', table : $table, bind : $id );
		} catch (\Throwable $th) {
			showError($th);
		}	
	}
}

<?php

namespace App\model;

use App\classes\InnerJoin;
class ReviewAppsData extends InnerJoin
{
	public function index()
	{
		try {
			$table = ['personal', 'interest', 'contact', 'otherFamily', 'work'];
		return $this->joinParam(firstTable: 'account', para: 'id', paraWhere: 'status', table : $table, bind : "new" );
		} catch (\Throwable $th) {
			showError($th);
		}	
	}

	public function getWithId(string | int $id) {
		try {
			$table = ['personal', 'interest', 'contact', 'otherFamily', 'work'];
		return $this->joinParam(firstTable: 'account', para: 'id', paraWhere: 'id', table : $table, bind : $id );
		} catch (\Throwable $th) {
			showError($th);
		}	
	}
}

<?php

namespace App\model;
use App\classes\Db;

class ReviewAppsData extends Db
{
    public function index()
    {
        	try {
			$query = "SELECT * FROM personal
			   INNER JOIN work ON personal.id = work.id
			--    INNER JOIN siblings ON personal.id = siblings.id
			--    INNER JOIN kids ON personal.id = kids.id
			   INNER JOIN interest ON personal.id = interest.id
			   INNER JOIN contact ON personal.id = contact.id
			   INNER JOIN otherFamily ON personal.id = otherFamily.id
			   
			   INNER JOIN account ON personal.id = account.id
			   WHERE account.status = ?";
			$result = $this->connect()->prepare($query);
			$result->execute(['new']);
			$outcome = $result->fetchAll(\PDO::FETCH_ASSOC);
			return $outcome;
		} catch (\PDOException $e) {
			showError($e);
		}
    }
}

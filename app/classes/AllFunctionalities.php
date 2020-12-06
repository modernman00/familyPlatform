<?php

namespace App\classes;

use \PDO;
use \PDOException;
use Exception;
use App\classes\Db;


class AllFunctionalities extends Db
{

	private $des = 'referral';

	public function submitForm($table, $field)
	{
		try {

			// EXTRACT THE KEY FOR THE COL NAME
			$key = array_keys($field);
			$col = implode(', ', $key);



			// extract values
			// $value = array_values($field);
			$placeholder = implode(', :', $key);

			// prep statement using placeholder :name
			$stmt = "INSERT INTO $table ($col) VALUES (:$placeholder)";
			$query = $this->connect()->prepare($stmt);
			if (!$query) throw new Exception("Not able to insert data", 1);

			foreach ($field as $keys => $values) {

				$query->bindValue(":$keys", $values);
			}
			$outcome = $query->execute();

			if (!$outcome) throw new Exception("Not able to execute data", 1);
		} catch (\PDOException $e) {
			echo $e->getMessage();
			echo $e->getLine();
		} catch (\Throwable $th) {
			echo $th->getMessage();
		}


		// 


	}



	// SELECT AND COUNT

	public function select_count_double($de, $dev, $from, $dev2, $from2)
	{
		try {
			$query = "SELECT * FROM $de WHERE $dev > ? AND $dev2 = ?";
			$result = $this->connect()->prepare($query);
			$result->execute([$from, $from2]);
			$rowCount = $result->rowCount();
			return $rowCount;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function select_count_double_dynamic($de, $col, $value)
	{
		try {
			$query = "SELECT * FROM $de WHERE $col[0] = ? AND $col[1] = ?";
			$result = $this->connect()->prepare($query);
			$result->execute([$value[0], $value[1]]);
			$rowCount = $result->rowCount();
			return $rowCount;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function select_count($de, $dev, $from)
	{
		try {
			$query = "SELECT * FROM $de WHERE $dev = ?";
			$result = $this->connect()->prepare($query);
			$result->execute([$from]);
			$rowCount = $result->rowCount();
			return $rowCount;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	// SELECT DISTINCT

	public function selectDistinct($firstselect, $secondselect, $from)
	{
		try {
			$query = "SELECT DISTINCT $firstselect, $secondselect FROM $from";
			$result = $this->connect()->prepare($query);
			$result->execute();
			$outcome = $result->fetchAll(PDO::FETCH_ASSOC);
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	// DELETE A COLUMN

	public function delete_column($de, $dev, $from)
	{

		try {
			$query = "DELETE * FROM $de WHERE $dev = ? LIMIT 1";
			$result = $this->connect()->prepare($query);
			$outcome = $result->execute([$from]);
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}



	public function delete2($de, $dev, $from, $redirect)
	{

		try {
			$query = "DELETE FROM $de WHERE $dev = ? LIMIT 1";
			$result = $this->connect()->prepare($query);
			$outcome = $result->execute([$from]);
			if ($outcome) {
				header("Location: $redirect");
			}
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}


	public function deleteUpdate($table, $datetime, $id, $id_ans, $redirect)
	{
		try {
			// $datetime = date('Y-m-d h:i:s');
			$query = "UPDATE $table SET deleted_at =? , status ='deleted' WHERE $id = ? LIMIT 1";
			$result = $this->connect()->prepare($query);
			$outcome = $result->execute([$datetime, $id_ans]);
			if ($outcome) {
				header("Location: $redirect");
			}
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}
	// be careful with this function -> didnt work on the last project -> check carefully before USE.

	public function selectall_join($table, $table2, $para)
	{

		try {
			$query = "SELECT * FROM $table INNER JOIN $table2 ON $table2.$para = $table.$para";
			// $query ="SELECT $table.*, $table.* INNER JOIN $table2 ON $table.$para = $table2.$para";
			$result = $this->connect()->prepare($query);
			$result->execute();
			$outcome = $result->fetchAll(PDO::FETCH_ASSOC);
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function selectall($table)
	{

		try {
			$query = "SELECT * FROM $table ORDER BY date_created DESC";
			$result = $this->connect()->prepare($query);
			$result->execute();
			$outcome = $result->fetchAll(PDO::FETCH_ASSOC);
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}


	public function selectCountAll($table)
	{

		try {
			$query = "SELECT COUNT(*) FROM $table";
			$result = $this->connect()->query($query)->fetchColumn();
			return $result;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}



	// SELECT A COLUMN FROM THE TABLE

	public function selectAllColumn($table, $column)
	{

		try {
			$query = "SELECT $column FROM $table";
			$result = $this->connect()->prepare($query);
			$result->execute();
			$outcome = $result->fetchAll(PDO::FETCH_ASSOC);
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	// SELECT ALL AND JOIN TWO TABLES
	public function selectall2($table, $table2, $para)
	{
		try {
			$query = "SELECT * FROM $table INNER JOIN $table2 ON $table.$para = $table2.$para";
			$result = $this->connect()->prepare($query);
			$result->execute();
			$outcome = $result->fetchAll(PDO::FETCH_ASSOC);
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function select_join($table, $table2, $para, $ref)
	{

		try {
			$query = "SELECT * FROM $table INNER JOIN $table2 ON $table2.$para = $table.$para WHERE $table.$para = ?";
			// $query ="SELECT $table.*, $table.* INNER JOIN $table2 ON $table.$para = $table2.$para";
			$result = $this->connect()->prepare($query);
			$result->execute([$ref]);
			$outcome = $result->fetchAll(PDO::FETCH_ASSOC);
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}



	// select all data function
	public function select($table)
	{
		try {
			$sql = "SELECT * FROM $table";
			$result = $this->connect()->query($sql);
			if ($result->rowCount() > 0) { // rowCount is an inbuilt function
				while ($row = $result->fetch()) {
					$data[] = $row; //took all the record and put them into an empty $array('' => , );
					;
				}
				return $data; //this is the data array
			}
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function select_from($de, $dev, $from)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $dev = ?";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

		public function select_from_withLimit($de, $dev, $from, $orderBy, $direction,  $limit)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $dev = ? ORDER BY $orderBy $direction LIMIT $limit";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function selectIsNot($de, $dev, $from)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $dev != ? ORDER BY id DESC";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function selectFromIsNot($de, $dev, $from, $from2, $from3,  $limit, $offset)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $dev != ? AND $dev != ? AND $dev != ? ORDER BY id DESC LIMIT $limit OFFSET $offset ";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from, $from2, $from3]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function select_from_greaterThan($de, $dev, $from)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $dev > ?";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function select_from_doubleAnd($de, $dev,  $from, $dev2, $from2)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $dev > ? AND $dev2 = ?";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from, $from2]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function select_from_doubleOr($de, $dev,  $from, $dev2, $from2)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $dev > ? OR $dev2 = ?";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from, $from2]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function select_Or($de, $from)
	{
		try {
			$sql = "SELECT * FROM $de WHERE email = ? OR username = ?";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from, $from]);
			$outcome = $result->fetchAll();
			return $outcome;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function select_Or_Count($de, $from)
	{
		try {
			$sql = "SELECT * FROM $de WHERE email = ? OR username = ?";
			$result = $this->connect()->prepare($sql);
			$result->execute([$from, $from]);
			$rowCount = $result->rowCount();
			return $rowCount;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}



	public function select_from_double($de, $dev, $from, $dev2, $from2)
	{
		$sql = "SELECT * FROM $de WHERE $dev = ? OR code = ?";
		$result = $this->connect()->prepare($sql);
		$result->execute([$from, $from2]);
		//$outcome = $result->fetchAll();
		$columnCount = $result->fetchColumn();
		return $columnCount;
		// if($columnCount >0){
		//   return "You have already registered";
		// }
		// else{

		//   $sql = "SELECT * FROM $de WHERE $dev = ?";
		// $result = $this->connect()->prepare($sql);
		// $result->execute([$from]);
		// $columnCount = $result->fetchColumn();
		// if($columnCount >0){
		//   return "You have already registered";
		// }
		// }
		//return $outcome;
	}


	public function editForm($id, $name, $city, $designation)
	{
		try {
			$sql = "UPDATE crud SET name=?, city=?, designation=? WHERE id = ?";
			$stmt = $this->connect()->prepare($sql)->execute([$name, $city, $designation, $id]);
			if ($stmt) {
				header('Location: indextwo.php');
			}
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function update($table, $column, $column_ans, $identifier, $identifier_ans)
	{
		try {
			$query = "UPDATE $table SET $column =? WHERE $identifier = ?";
			$result = $this->connect()->prepare($query);
			$result->execute([$column_ans, $identifier_ans]);
			return $result;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function updateOr($table, $column, $column_ans, $identifier1, $identifier2, $identifier_ans)
	{
		try {
			$query = "UPDATE $table SET $column =? WHERE $identifier1 = ? OR $identifier2 = ?";
			$result = $this->connect()->prepare($query);
			$result->execute([$column_ans, $identifier_ans, $identifier_ans]);
			return $result;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}




	public function insertData($field, $de, $redirect)
	{
		try {
			$implodeDBCol = implode(', ', array_keys($field)); //remember, $field is an array
			$implodePlaceholder = implode(', :', array_keys($field));
			$sql = "INSERT INTO $de ($implodeDBCol) VALUES (:$implodePlaceholder)";
			$stmt = $this->connect()->prepare($sql);
			foreach ($field as $key => $value) {
				$stmt->bindValue(':' . $key, $value);
			}
			$stmtExec = $stmt->execute();
			if ($stmtExec) {
				header($redirect);
			}
			return $stmtExec;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	public function insertData_NoRedirect($field, $de)
	{
		try {
			$implodeDBCol = implode(', ', array_keys($field)); //remember, $field is an array
			$implodePlaceholder = implode(', :', array_keys($field));
			$sql = "INSERT INTO $de ($implodeDBCol) VALUES (:$implodePlaceholder)";
			$stmt = $this->connect()->prepare($sql);
			foreach ($field as $key => $value) {
				$stmt->bindValue(':' . $key, $value);
			}
			return $stmt->execute();
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	//SELECT FROM THE DB, CHECK IF THE PROFILE EXIST AND INSERT DATA
	public function select_column($de, $id,  $id_ans, $data)
	{
		try {
			$sql = "SELECT * FROM $de WHERE $id = ?";
			$stmt = $this->connect()->prepare($sql);
			$stmt->bind_param("s", $id_ans);
			$stmt->execute();
			$stmt->store_result();
			if ($stmt->num_rows < 1) {
				$this->insertData_NoRedirect($data, $de);
			}
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}



	public function insert_direct($des, $field, $valuea, $arraycombine, $redirect)
	{
		try {
			$sql = "INSERT INTO $des ($field) VALUES (:$valuea)";
			$stmt = $this->connect()->prepare($sql);
			$stmt->execute($arraycombine);
			$redirect;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	// UPDATE MULTIPLE PARAMETER DYNAMICALLY

	public function updateMultiple(array $data, string $dbtable, string $identifier, $identifier_ans, string $idORref)
	{
		try {
			$implodeKey = implode('=?, ', array_keys($data));
			$implodeKey = rtrim($implodeKey, ", $idORref");
			$implodeValue = array_values($data);
			$sql = "UPDATE $dbtable SET $implodeKey WHERE $identifier =?";
			$stmt = $this->connect()->prepare($sql)->execute($implodeValue);
			return $stmt;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $data - the array from the $_POST 
	 * @param string $dbtable
	 * @param [type] $identifier this is either id or email or username
	 * @return void
	 */
	public function updateMultiplePOST(array $data, string $dbtable,  $identifier)
	{
		try {
			unset($data['submit']); // remove submit if present
			$ans = $data[$identifier]; // store $data['id]
			unset($data[$identifier]); // unset id
			$implodeKey = implode('=?, ', array_keys($data));

			$data[$identifier] = $ans;
			$implodeValue = array_values($data);

			$sql = "UPDATE $dbtable SET $implodeKey=? WHERE $identifier =?";
			// example - 'UPDATE register SET title=?, first_name=?, second_name=? WHERE id =?'			

			$stmt = $this->connect()->prepare($sql)->execute($implodeValue);

			return $stmt;
		} catch (PDOException $e) {
			echo $e->getMessage(), PHP_EOL;
		}
	}
}

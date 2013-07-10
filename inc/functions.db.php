<?php

function query_db($query, $params=false) {
	if(isset($_SESSION['demo']) && !empty($_SESSION['demo'])) {
		if(strpos(strtolower($query), 'insert') !== false) {
			return false;
		} else if(strpos(strtolower($query), 'update') !== false) {
			return false;
		}
	}
	
	$connection= new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8",
		dbhost, dbname), dbuser, dbpassword,
		array(1002 => "set names utf8")); // 1002=PDO::MYSQL_ATTR_INIT_COMMAND

	$statement= $connection->prepare($query);
	if(!$statement) {
		$error= $connection->errorInfo();
		debug($error);
		
		return false;
	}
	
	// Bind keys to statement
	if(is_array($params) && count($params) > 0) {
		foreach($params as $key => $value) {
			$statement->bindValue(":{$key}", $value);
		}
	}

	$response= $statement->execute();
	if(!$response) {
		$error= $statement->errorInfo();
		throw new Exception($error[2]);
		
		return false;
	}

	if(substr(strtolower($query), 0, 6) === 'insert') {
        return intval($connection->lastInsertId());
    } else if(substr(strtolower($query), 0, 6) === 'update') {
        return intval($statement->rowCount());
    } else {
    	return $statement;
    }
}

/**
 * Query the DB. Return as array of associative arrays.
 *
 * Typically to return an iteratable list of rows from a DB table.
 *
 * @param string $query
 * @return string
 */
function query_db_assoc($query, $params=array(), $id_as_key=false) {
	$cache_key= md5($query);
	if(strpos(strtolower($query), 'select') === 0) {
		if(apc_exists($cache_key)) {
			return apc_fetch($cache_key);
		}
	}
	
	$result= query_db($query, $params);
	if(!$result) {
		return array();
	}

	$records= array();
	while($row= $result->fetch(PDO::FETCH_ASSOC)) {
		if($id_as_key) {
			$records[intval($row['id'])]= $row;
		} else {
			$records[]= $row;
		}
	}
	
	apc_add($cache_key, $records, 60*5);
	
	return $records;
}

/**
 * Query an object from the DB
 *
 * @param string $query
 * @param array $params
 * @return mixed
 */
function query_db_object($query, $params=array()) {
	$result= query_db($query, $params);
	if(!$result) {
		return false;
	}
	
	return $result->fetch(PDO::FETCH_ASSOC);
}

/**
 * Query a single value from the DB
 *
 * @param string $query
 * @param array $params
 * @return mixed
 */
function query_db_value($query, $params=null) {
	$result= query_db($query, $params);
	if($result= $result->fetch(PDO::FETCH_ASSOC)) {
		return current($result);
	} else {
		return false;
	}
}

/**
 * Return an array of single values (e.g. a single column)
 *
 * @param string $query
 * @param array $params
 * @return mixed
 */
function query_db_values($query, $params=null) {
	$result= query_db($query, $params);
	
	if(!$result) {
		return false;
	}
	
	$records= array();
	while($row= $result->fetch(PDO::FETCH_ASSOC)) {
		$records[]= current($row);
	}
	
	return $records;
}
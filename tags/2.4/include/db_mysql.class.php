<?php
class dbstuff {
	var $querynum = 0;
	var $queries = array();
	var $version = '';
	var $charset = '';
	var $dbhost = '';
	var $dbuser = '';
	var $dbpw = '';
	var $dbname = '';
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $charset = '', $pconnect = 1) {
		if($pconnect) {
			if(!mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				$this->halt('Can not connect to MySQL server');
			}
		} else {
			if(!mysql_connect($dbhost, $dbuser, $dbpw)) {
				$this->halt('Can not connect to MySQL server');
			}
		}
		$version = $this->version();
		$this->charset = $charset;
		$this->version = $version;
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpw = $dbpw;
		$this->dbname = $dbname;
		if($version > '4.1') {
			mysql_query("SET NAMES '{$charset}'");
		}
		if($version > '5.0') {
			mysql_query("SET sql_mode=''");
		}
		if($dbname) {
			mysql_select_db($dbname);
		}
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function query($sql) {
		global $debug;
		if(!$query = @mysql_query($sql)) $this->halt($this->error(), $sql);
		if($debug) error_log($sql."\n", 3, AK_ROOT.'/logs/queries');
		$this->querynum++;
		$this->queries[] = $sql;
		return $query;
	}

	function querytoarray($sql, $num = 1000) {
		global $timedifference, $thetime;
		$results = array();
		$query = $this->query($sql);
		$i = 1;
		while($row = $this->fetch_array($query)) {
			$results[] = $row;
			if($i ++ >= $num) break;
		}
		return $results;
	}

	function close() {
		return mysql_close();
	}

	function get_by($what, $from, $where = '') {
		global $tablepre, $debug;
		$sql = "SELECT {$what} FROM {$tablepre}_{$from}";
		if($where != '') {
			$sql .= " WHERE {$where}";
		}
		$sql .= " LIMIT 1";
		$row = $this->get_one($sql);
		if($row === false) {
			return false;
		} elseif(count($row) == 1) {
			return current($row);
		} else {
			return $row;
		}
	}

	function list_by($what, $from, $where = '', $orderby = '', $limit = '') {
		global $tablepre;
		$sql = "SELECT {$what} FROM {$tablepre}_{$from}";
		if($where != '') $sql .= " WHERE {$where}";
		if($orderby != '') $sql .= " ORDER BY {$orderby}";
		if(!empty($limit)) $sql .= " LIMIT {$limit}";
		return $this->query($sql);
	}

	function insert($table, $values) {
		global $tablepre;
		$sql = "INSERT INTO {$tablepre}_{$table}";
		$keysql = '';
		$valuesql = '';
		foreach($values as $key => $value) {
			$keysql .= "`$key`,";
			$valuesql .= "'".addslashes($value)."',";
		}
		$sql = $sql.'('.substr($keysql, 0, -1).')VALUES('.substr($valuesql, 0, -1).')';
		return $this->query($sql);
	}

	function update($table, $values, $where) {
		global $tablepre;
		$sql = "UPDATE {$tablepre}_{$table} SET";
		$keysql = '';
		$valuesql = '';
		foreach($values as $k => $v) {
			$v = addslashes($v);
			$sql .= "`$k`='$v',";
		}
		$sql = substr($sql, 0, -1);
		$sql .= " WHERE {$where}";
		return $this->query($sql);
	}

	function delete($table, $where) {
		global $tablepre;
		$sql = "DELETE FROM {$tablepre}_{$table} WHERE {$where}";
		return $this->query($sql);
	}

	function get_one($sql) {
		$arr = $this->querytoarray($sql, 1);
		if(isset($arr[0])) {
			return $arr[0];
		} else {
			return false;
		}
	}

	function get_field($sql) {
		$arr = $this->get_one($sql);
		if(!empty($arr)) {
			return current($arr);
		} else {
			return '';
		}
	}

	function insert_id() {
		return mysql_insert_id();
	}

	function getalltables() {
		$tables = array();
		$sql = "SHOW TABLES";
		$query = $this->query($sql);
		$tables = array();
		while($table = $this->fetch_array($query)) {
			$tables[] = current($table);
		}
		return $tables;
	}

	function getallfields($table) {
		$fields = array();
		$results = $this->querytoarray("EXPLAIN $table");
		foreach($results as $result) {
			$fields[] = $result['Field'];
		}
		return $fields;
	}

	function error() {
		return mysql_error();
	}

	function version() {
		return mysql_get_server_info();
	}

	function halt($message = '', $sql = '') {
		debug($sql."\n".$message, 1);
	}
	
	function getcreatetable($table) {
		$sql = "SHOW CREATE TABLE `{$table}`";
		$result = current($this->querytoarray($sql));
		return $result['Create Table'];
	}
}
?>
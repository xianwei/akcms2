<?php
class dbstuff2 {
	var $querynum = 0;
	var $queries = array();
	var $version = '';
	var $dbname;
	var $db;
	function open($dbfile) {
		$this->db = sqlite_open($dbfile);
		$this->version = sqlite_libversion();
		$this->dbname = $dbfile;
		$this->query("BEGIN;");
	}

	function commit() {
		$this->query('COMMIT;');
	}

	function fetch_array($query) {
		return sqlite_fetch_array($query, SQLITE_ASSOC);
	}

	function query($sql) {
		global $debug;
		$sql = str_replace('`', '', $sql);
		$sql = str_replace('ORDER BY rand()', '', $sql);
		$query = @sqlite_query($this->db, $sql);
		if($query === false) debug($sql."\n".sqlite_error_string(sqlite_last_error($this->db)), 1);
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
		$this->commit();
		return sqlite_close($this->db);
	}

	function get_by($what, $from, $where = '') {
		global $tablepre;
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
			$valuesql .= "'".ak_addslashes($value)."',";
		}
		$sql = $sql.'('.substr($keysql, 0, -1).')VALUES('.substr($valuesql, 0, -1).')';
		return $this->query($sql);
	}

	function update($table, $values, $where) {
		global $tablepre;
		$sql = "UPDATE {$tablepre}_{$table} SET ";
		$keysql = '';
		$valuesql = '';
		foreach($values as $k => $v) {
			$v = ak_addslashes($v);
			$sql .= "`$k`='$v',";
		}
		$sql = substr($sql, 0, -1);
		$sql .= " WHERE {$where}";
		return $this->query($sql);
	}
	
	function replace($table, $values, $distinctvalues = array()) {
		global $tablepre;
		$sql = "REPLACE INTO {$tablepre}_{$table}";
		$keysql = '';
		$valuesql = '';
		foreach(array_merge($values, $distinctvalues) as $key => $value) {
			$keysql .= "`$key`,";
			$valuesql .= "'".ak_addslashes($value)."',";
		}
		$sql = $sql.'('.substr($keysql, 0, -1).')VALUES('.substr($valuesql, 0, -1).')';
		return $this->query($sql);
		/*如果有版本兼容问题，则用下方的自己实现的replace代替
		global $tablepre;
		$wheres = array();
		foreach($distinctvalues as $k => $v) {
			$wheres[] = "$k='".ak_addslashes($v)."'";
		}
		if($this->get_by('*', $table, implode(' AND ', $wheres))) {
			return $this->update($table, $values, implode(' AND ', $wheres));
		} else {
			return $this->update($table, array_merge($values, $distinctvalues));
		}
		*/
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
		return sqlite_last_insert_rowid($this->db);
	}

	function getalltables() {
		$tables = array();
		$query = $this->query("SELECT * FROM sqlite_master");
		while($table = $this->fetch_array($query)) {
			if($table['type'] == 'table') $tables[] = $table['name'];
		}
		return $tables;
	}

	function getallfields($table) {
		$fields = array();
		$query = $this->query("SELECT * FROM sqlite_master WHERE name ='$table'");
		if(!$field = $this->fetch_array($query)) return false;
		$sql = $field['sql'];
		$_pos1 = strpos($sql, '(');
		if($_pos1 === false) return false;
		$sql = substr($sql, $_pos1 + 1, -1);
		$fs = explode(',', $sql);
		foreach($fs as $f) {
			if(strpos($f, 'PRIMARY KEY(') === 0) continue;
			$_pos2 = strpos($f, ' ');
			$fields[] = substr($f, 0, $_pos2);
		}
		return $fields;
	}
}
?>
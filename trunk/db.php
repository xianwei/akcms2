<?php
require_once './include/common.inc.php';
require_once './include/admin.inc.php';
$db = mysqldb();
$me = 'db.php';
if(!isset($get_action)) {
	aexit('');
} elseif($get_action == 'exportdb') {
	$dumpsql = 'data/dump.sql';
	$tablefile = 'tables.tmp';
	$step = 1000;
	$sql = '';
	if(!isset($get_run)) {
		if(!isset($post_submit)) {
			displaytemplate('admincp_exportdb.htm');
			aexit();
		} else {
			setcookie('packsize', $post_packsize);
			$tables = $db->getalltables();
			foreach($tables as $key => $table) {
				if($post_mode == 'common') {
					if(strpos($table, $tablepre) !== 0) unset($tables[$key]);
				}
			}
			$logs = implode("\n", $tables);
			writetofile($logs, $tablefile);
			foreach($tables as $table) {
				$_sql = $db->getcreatetable($table);
				$_sql = str_replace("\n", '', $_sql);
				$sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
				$sql .= $_sql.";\n";
			}
			setcookie('labelid', 1);
			$dumpsql = $dumpsql.'-1';
			writesql($sql);
			$url = $me.'?action=exportdb&run=1&r='.random(6);
			gotourl($url);
		}
	} else {
		$packsize = $cookie_packsize * 1024;
		if($packsize > 0) {
			if(!isset($cookie_labelid)) {
				aexit();
			} else {
				$labelid = $cookie_labelid;
			}
			$dumpsql = $dumpsql.'-'.$labelid;
		}
		if(isset($get_mark) && isset($get_table)) {
			$start = $get_mark;
			$table = $get_table;
		} else {
			$start = 0;
			$table = readlinefromfile($tablefile);
			if($table == 'empty' || $table == 'noexist') {
				@unlink($tablefile);
				setcookie('labelid', '');
				adminmsg($lan['exportsuccess']);
			}
		}
		$total = $db->get_field("SELECT COUNT(0) FROM {$table}");
		$query = $db->query("SELECT * FROM `{$table}` LIMIT $start,$step");
		while($row = $db->fetch_array($query)) {
			$values = array();
			foreach($row as $value) {
				$values[] = "'".addslashes($value)."'";
			}
			$values = implode(',', $values);
			$values = str_replace(";\r\n", "; \n", $values);
			$values = str_replace(";\n", "; \n", $values);
			$sql = "REPLACE INTO `{$table}` VALUES($values);\n";
			writesql($sql);
		}
		if($total > $start + $step) {
			$url = $me.'?action=exportdb&run=1&table='.$table.'&mark='.($start + $step).'&r='.random(6);
		} else {
			$url = $me.'?action=exportdb&run=1&r='.random(6);
		}
		gotourl($url);
		adminmsg($lan['exportcontinue']);
	}
} elseif($get_action == 'display') {
	displaytemplate('admincp_importdb.htm');
} elseif($get_action == 'importdb') {
	$dumpsql = 'data/dump.sql';
	$sql = '';
	$data = '';
	$step = 1000;
	if(isset($get_start)) {
		list($labelid, $position) = explode(':', $cookie_mark);
	} else {
		$labelid = 1;$position = 0;
		setcookie('mark', "1:0");
	}
	if(!$fp = @fopen($dumpsql.'-'.$labelid, 'r')) {
		setcookie('mark', "1:0");
		exit('import finished!');
	}
	fseek($fp, $position);
	$line = '';
	$i = 0;
	while(!feof($fp)) {
		$line .= fgets($fp, 1024);
		if(substr($line, -2) != ";\n") continue;
		$line = trim($line);
		if(empty($line)) continue;
		$line = str_replace("; \n", ";\n", $line);
		
		$db->query($line);
		
		$line = '';
		++ $i;
		if($i >= $step) break;
	}
	if(feof($fp)) {
		++ $labelid;
		setcookie('mark', "$labelid:0");
	} else {
		setcookie('mark', $labelid.':'.ftell($fp));
	}
	fclose($fp);
	adminmsg($lan['importcontinue'], $me.'?action=importdb&start=1', 1);
}
aexit();

function gotourl($url) {
	global $dumpsql, $packsize, $labelid;
	if($packsize > 0) {
		if(file_exists($dumpsql) && filesize($dumpsql) > $packsize) {
			setcookie('labelid', $labelid + 1);
		}
	}
	echo "<script>function go() {document.location=\"$url\";}setTimeout(\"go()\", 100);</script>";
}

function writesql($sql) {
	global $dumpsql;
	error_log($sql, 3, $dumpsql);
}
?>
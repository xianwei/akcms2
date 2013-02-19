<?php
require_once './include/common.inc.php';
require_once './include/admin.inc.php';
$me = 'db.php';
$dumpsql = 'data/dump.sql';
if(!isset($get_action)) {
	aexit('');
} elseif($get_action == 'exportdb') {
	$tablefile = 'data/tables.tmp';
	$step = 1000;
	$sql = '';
	if(!isset($get_run)) {
		if(!isset($post_submit)) {
			displaytemplate('admincp_exportdb.htm');
			runinfo();
			aexit();
		} else {
			setcookie('packsize', $post_packsize);
			$tables = $db->getalltables();
			foreach($tables as $key => $table) {
				if(strpos($table, $tablepre.'_') !== 0) {
					unset($tables[$key]);
				} else {
					$tables[$key] = str_replace($tablepre.'_', '', $tables[$key]).'#';
				}
			}
			$logs = implode("\n", $tables);
			writetofile($logs, $tablefile);
			writesql($logs."\n");
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
			if(substr($table, -1) == '#') $table = $tablepre.'_'.substr($table, 0, -1);
		}
		$total = $db->get_field("SELECT COUNT(0) FROM {$table}");
		$query = $db->query("SELECT * FROM {$table} LIMIT $start,$step");
		while($row = $db->fetch_array($query)) {
			if(strpos($table, $tablepre.'_') === 0) {
				$_table = str_replace($tablepre.'_', '', $table).'#';
			} else {
				$_table = $table;
			}
			$_row = array(
				'table' => $_table,
				'value' => $row
			);
			$_row = base64_encode(serialize($_row));
			writesql($_row."\n");
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
	$sql = '';
	$data = '';
	$step = 1000;
	if(isset($get_start)) {
		list($labelid, $position) = explode(':', $cookie_mark);
	} else {
		$labelid = 1;$position = 0;
		setcookie('mark', "1:0");
		$_tables = readfromfile($dumpsql);
		$_tables = tidyitemlist($_tables, "\n", 0);
		$tables = explode("\n", $_tables);
		foreach($tables as $table) {
			if(substr($table, -1) == '#') $table = $tablepre.'_'.substr($table, 0, -1);
			$db->query("DELETE FROM {$table}");
		}
		adminmsg($lan['importcontinue'], $me.'?action=importdb&start=1', 1);
	}
	if(!$fp = @fopen($dumpsql.'-'.$labelid, 'r')) {
		setcookie('mark', "1:0");
		updatecache();
		exit('import finished!');
	}
	fseek($fp, $position);
	$line = '';
	$i = 0;
	while(!feof($fp)) {
		$line .= fgets($fp, 1024);
		if(substr($line, -1) != "\n") continue;
		if(trim($line) == '') continue;
		$_a = unserialize(base64_decode($line));
		if(substr($_a['table'], -1) == '#') $_a['table'] = substr($_a['table'], 0, -1);
		$db->insert($_a['table'], $_a['value']);
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
	gotourl($me.'?action=importdb&start=1');
	adminmsg($lan['importcontinue']);
}
runinfo();
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
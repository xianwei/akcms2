<?php
$sql = '';
$nodb = 1;
require_once './include/common.inc.php';
require_once('install/install.sql.php');
if(frequence($_SERVER['SCRIPT_NAME'], '/') < 2) exit('please move akcms to a folder before install it.');
if(file_exists(AK_ROOT.'./include/install.lock')) exit('akcms installed!<br>if you want to reinstall it please remove /include/install.lock first!<br><b style="color:red">Notice:reinstall will destory all data!</b>');
if(!empty($get_language)) {
	$language = $get_language;
} elseif(!empty($post_language)) {
	$language = $post_language;
} else {
	$language = 'english';
}
includelanguage();
$array_paths = array(
	'../',
	'./cache/',
	'./templates/',
	'./templates_c/'
);
$array_files = array(
	'./config.inc.php',
);
$message = '';
foreach($array_paths as $path) {
	if(!is_writable($path)) {
		$message .= '"'.$path.'"'.$lan['isunwritable'].'<br>';
	}
}
foreach($array_files as $file) {
	if(!is_writable($file)) {
		$message .= '"'.$file.'"'.$lan['isunwritable'].'<br>';
	}
}
if(!empty($message)) {
	exit($message);
}
$smarty->assign('header_charset', $header_charset);
$smarty->assign('lan', $lan);
$smarty->assign('language', $language);
$smarty->assign('sysname', $sysname);
$smarty->assign('sysedition', $sysedition);
$smarty->assign('header_charset', $header_charset);

if(isset($post_installsubmit)) {
	if(!preg_match('/^[0-9a-zA-Z]+$/i', $post_tablepre_tag)) {
		adminmsg($lan['tablepreerror'], 'back', 3, 1);
	} else {
		$tablepre = $post_tablepre_tag;
	}
	if(!preg_match('/^[0-9a-zA-Z_\/\.]+$/i', $post_dbname_tag)) {
		adminmsg($lan['dbnameerror'], 'back', 3, 1);
	}
	if($post_dbtype == 'mysql') {
		if(!$db_conn = @mysql_connect($post_dbhost_tag, $post_dbuser_tag, $post_dbpw_tag)) {
			adminmsg($lan['connecterror'], 'back', 3, 1);
		} else {
			if(!mysql_select_db($post_dbname_tag, $db_conn)) {
				if($post_charset_tag == 'utf8') {
					$mysql_charset = ' DEFAULT CHARACTER SET utf8 COLLATE utf8_bin';
				} elseif($post_charset_tag == 'gbk') {
					$mysql_charset = ' DEFAULT CHARACTER SET gbk COLLATE gbk_chinese_ci';
				} elseif($post_charset_tag == 'english') {
					$mysql_charset = ' DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci';
				}
				$createdatabasesql = 'CREATE DATABASE '.$post_dbname_tag;
				if(mysql_get_server_info() > '4.1') $createdatabasesql = $createdatabasesql.$mysql_charset;
				mysql_query($createdatabasesql, $db_conn);
			}
			mysql_close($db_conn);
			$codekey = random(6);
			$str_config = '';
			$array_config = array('dbhost', 'dbuser', 'dbpw', 'dbname', 'template_path', 'codekey', 'tablepre', 'cookiepre', 'charset', 'timedifference');
			foreach($array_config as $config) {
				$post = 'post_'.$config.'_tag';
				$str_config .= '$'.$config.' = '.'\''.(isset($$post) ? $$post : $$config).'\''.";\n";
				$$config = isset($$post) ? $$post : $$config;
			}
			$str_config .= '$debug = 0;'."\n";
			$str_config = "<?php\n".$str_config."?>";
			if($fp = @fopen('./config.inc.php', 'w')) {
				fwrite($fp, $str_config);
				fclose($fp);
			}
			require_once AK_ROOT.'./include/db_mysql.class.php';
			$db = new dbstuff;
			$db->connect($post_dbhost_tag, $post_dbuser_tag, $post_dbpw_tag, $post_dbname_tag, 0);
			foreach($createtablesql as $key => $value) {
				$value['charset'] = $post_charset_tag;
				$createtablesql = table2mysql($tablepre.'_'.$key, $value);
				runquery($createtablesql);
			}
			foreach($insertsql as $key => $value) {
				$tablename = str_replace('ak_', $tablepre.'_', $value['tablename']);
				if($value['tablename'] == 'settings' && $value['value']['variable'] == 'language') $value['value']['value'] = $language;
				$db->insert($tablename, $value['value']);
			}
			createfore();
			ak_touch(AK_ROOT.'./include/install.lock');
			updatecache();
			adminmsg($lan['installsuccess'], 'login.php');
		}
	} elseif($post_dbtype == 'sqlite') {
		if(!$db_conn = sqlite_open($post_dbname_tag)) {
			adminmsg($lan['connecterror'], 'back', 3, 1);
		} else {
			sqlite_close($db_conn);
			$dbname = $post_dbname_tag;
			$tablepre = $post_tablepre_tag;
			$codekey = random(6);
			$str_config = '';
			$array_config = array('dbname', 'template_path', 'codekey', 'tablepre', 'cookiepre', 'charset', 'timedifference');
			foreach($array_config as $config) {
				$post = 'post_'.$config.'_tag';
				$str_config .= '$'.$config.' = '.'\''.(isset($$post) ? $$post : $$config).'\''.";\n";
			}
			$str_config .= '$debug = 0;'."\n";
			$str_config = "<?php\n".$str_config."?>";
			if($fp = @fopen('./config.inc.php', 'w')) {
				fwrite($fp, $str_config);
				fclose($fp);
			}
			require_once AK_ROOT.'./include/db_sqlite.class.php';
			$db = new dbstuff2;
			$db->open(AK_ROOT.$dbname);
			foreach($createtablesql as $key => $value) {
				$value['charset'] = $post_charset_tag;
				$createtablesql = table2sqlite($tablepre.'_'.$key, $value);
				runquery($createtablesql);
			}
			foreach($insertsql as $key => $value) {
				$tablename = str_replace('ak_', $tablepre.'_', $value['tablename']);
				if($value['tablename'] == 'settings' && $value['value']['variable'] == 'language') $value['value']['value'] = $language;
				$db->insert($tablename, $value['value']);
			}
			createfore();
			ak_touch(AK_ROOT.'./include/install.lock');
			updatecache();
			adminmsg($lan['installsuccess'], 'login.php');
		}
	}
	
} elseif(!isset($_GET['language'])) {
	displaytemplate('chooselanguage.htm');
} elseif(!isset($_GET['dbtype'])) {
	$mysqlsupport = 1;
	$sqlitesupport = 1;
	if(!function_exists('mysql_connect')) $mysqlsupport = 0;
	if(!function_exists('sqlite_open')) $sqlitesupport = 0;
	$smarty->assign('language', $language);
	$smarty->assign('sqlitesupport', $sqlitesupport);
	$smarty->assign('mysqlsupport', $mysqlsupport);
	displaytemplate('choosedb.htm');
} else {
	$smarty->assign('sysname', $sysname);
	$smarty->assign('sysedition', $sysedition);
	$smarty->assign('servertime', date('Y-m-d H:i:s', time()));
	$smarty->assign('tablepre', $tablepre);
	$smarty->assign('language', $language);
	$smarty->assign('timedifference', $timedifference);
	if($get_dbtype == 'mysql') {
		isset($dbhost) || $dbhost = '';
		isset($dbuser) || $dbuser = '';
		isset($dbpw) || $dbpw = '';
		$smarty->assign('dbhost', $dbhost);
		$smarty->assign('dbuser', $dbuser);
		$smarty->assign('dbpw', $dbpw);
		$smarty->assign('dbname', $dbname);
		displaytemplate('install_mysql.htm');
	} else {
		$dbname = 'data/'.random(6).'.db.php';
		$smarty->assign('dbname', $dbname);
		displaytemplate('install_sqlite.htm');
	}
}

function applylanguage($sql, $language) {
	$array_chinese = array(
		'language' => 'chinese',
		'attachtemplate' => 'ÃèÊö:[description]<br>¸½¼þ:<a href="[url]" target="_blank">[filename]</a>([size] KB)<br><br>',
	);
	$array_english = array(
		'language' => 'english',
		'attachtemplate' => 'description:[description]<br>attachment:<a href="[url]" target="_blank">[filename]</a>([size] KB)<br><br>',
	);
	$array_template = array();
	foreach($array_english as $key => $message) {
		$array_template[] = "[$key]";
	}
	$arrayname = "array_{$language}";
	$sql = str_replace($array_template, $$arrayname, $sql);
	return $sql;
}
?>
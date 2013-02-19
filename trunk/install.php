<?php
$sql = <<<EOT
DROP TABLE `ak_admins`;
CREATE TABLE `ak_admins` (
`id` mediumint(8) unsigned NOT NULL auto_increment,
`editor` varchar(20) NOT NULL,
`password` char(32) NOT NULL,
`freeze` tinyint(4) NOT NULL default '0',
`items` int(11) NOT NULL default '0',
PRIMARY KEY(`id`)
) [engine][mysqlcharset];
INSERT INTO `ak_admins` (`id`, `editor`, `password`) VALUES (1, 'admin', '0cc175b9c0f1b6a831c399e269772661');

DROP TABLE `ak_attachments`;
CREATE TABLE `ak_attachments` (
`id` int(11) NOT NULL auto_increment,
`itemid` int(11) NOT NULL,
`filename` varchar(100) NOT NULL,
`filesize` int(11) NOT NULL,
`description` varchar(255) NOT NULL,
`dateline` int(11) NOT NULL,
PRIMARY KEY(`id`),
KEY `itemid` (`itemid`)
) [engine][mysqlcharset];

DROP TABLE `ak_categories`;
CREATE TABLE `ak_categories` (
`id` smallint(5) unsigned NOT NULL auto_increment,
`categoryup` smallint(6) NOT NULL default '0',
`category` varchar(50) NOT NULL,
`alias` varchar(50) NOT NULL,
`keywords` varchar(50) NOT NULL,
`description` varchar(255) NOT NULL,
`items` mediumint(9) NOT NULL default '0',
`allitems` mediumint(9) NOT NULL default '0',
`pv` int(9) NOT NULL default '0',
`orderby` mediumint(9) NOT NULL default '0',
`path` varchar(30) NOT NULL,
`itemtemplate` varchar(30) NOT NULL,
`defaulttemplate` varchar(30) NOT NULL,
`listtemplate` varchar(30) NOT NULL,
`html` tinyint(4) NOT NULL default '0',
`usefilename` tinyint(4) NOT NULL default '0',
`storemethod` varchar(50) default '0',
`categoryhomemethod` varchar(50) default '0',
`categorypagemethod` varchar(50) default '0',
`fid` smallint(6) NOT NULL default '0',
`itemextfields` text NOT NULL,
PRIMARY KEY(`id`)
) [engine][mysqlcharset];
INSERT INTO `ak_categories` (`categoryup` , `category` , `description` , `items` , `allitems` , `orderby` , `path` , `itemtemplate` , `defaulttemplate` , `listtemplate` , `html` ) VALUES ('0', 'default', '', '0', '0', '0', '', '', '', '', '0');

DROP TABLE `ak_comments`;
CREATE TABLE `ak_comments` (
`id` int(9) unsigned NOT NULL auto_increment,
`itemid` int(9) NOT NULL,
`username` varchar(50) NOT NULL,
`title` varchar(255) NOT NULL,
`message` text NOT NULL,
`dateline` int(9) NOT NULL,
`ip` char(15) NOT NULL,
`goodnum` int(9) NOT NULL default '0',
`badnum` int(9) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `itemid` (`itemid`,`dateline`,`ip`),
KEY `dateline` (`dateline`,`ip`)
) [engine][mysqlcharset];

DROP TABLE `ak_scores`;
CREATE TABLE `ak_scores` (
`id` int(9) unsigned NOT NULL auto_increment,
`itemid` int(9) NOT NULL,
`score` tinyint(6) NOT NULL,
`dateline` int(9) NOT NULL,
`ip` char(15) NOT NULL,
PRIMARY KEY (`id`),
KEY `itemid` (`itemid`,`dateline`,`ip`),
KEY `dateline` (`dateline`,`ip`)
) [engine][mysqlcharset];

DROP TABLE `ak_filenames`;
CREATE TABLE `ak_filenames` (
`htmlid` int(10) unsigned NOT NULL auto_increment,
`filename` varchar(255) NOT NULL,
`type` varchar(10) NOT NULL,
`dateline` int(11) NOT NULL,
`id` int(11) NOT NULL,
`page` smallint(6) NOT NULL,
PRIMARY KEY(`htmlid`),
UNIQUE KEY `filename` (`filename`),
UNIQUE KEY `type` (`type`,`id`,`page`)
) [engine][mysqlcharset];

DROP TABLE `ak_items`;
CREATE TABLE `ak_items` (
`id` int(10) unsigned NOT NULL auto_increment,
`title` char(100) NOT NULL,
`aimurl` char(100) NOT NULL default '',
`shorttitle` char(50) NOT NULL default '',
`category` smallint(6) NOT NULL,
`section` smallint(6) DEFAULT '0',
`author` char(30) NOT NULL,
`editor` char(25) NOT NULL,
`source` char(40) NOT NULL,
`orderby` mediumint(9) DEFAULT '0',
`orderby2` mediumint(9) DEFAULT '0',
`orderby3` mediumint(9) DEFAULT '0',
`orderby4` mediumint(9) DEFAULT '0',
`dateline` int(11) DEFAULT '0',
`lastupdate` int(11) DEFAULT '0',
`lastreplay` int(11) DEFAULT '0',
`pageview` mediumint(9) NOT NULL default '0',
`template` char(30) NOT NULL,
`filename` char(255) NOT NULL,
`attach` tinyint(4) NOT NULL default '0',
`picture` char(255) NOT NULL,
`latesthtml` int(11) DEFAULT '0',
`keywords` char(255) NOT NULL default '',
`digest` char(255) NOT NULL default '',
`titlecolor` char(7) NOT NULL default '',
`titlestyle` char(1) NOT NULL default '',
`tid` int(11) NOT NULL default 0,
`commentnum` int(11) NOT NULL default 0,
`scorenum` int(11) NOT NULL default 0,
`totalscore` int(11) NOT NULL default 0,
`avgscore` float NOT NULL default 0,
`ext` tinyint(4) NOT NULL default 0,
PRIMARY KEY(`id`),
KEY `category` (`category`,`section`,`editor`),
KEY `editor` (`editor`),
KEY `dateline` (`dateline`)
) [engine][mysqlcharset];

INSERT INTO `ak_items`(title, category, template, filename)values('default',0,'page_home.htm', '/index.htm');

DROP TABLE `ak_item_exts`;
CREATE TABLE `ak_item_exts` (
`id` int(10) unsigned NOT NULL,
`value` text NOT NULL,
PRIMARY KEY(`id`)
) [engine][mysqlcharset];

DROP TABLE `ak_sections`;
CREATE TABLE `ak_sections` (
`id` smallint(5) unsigned NOT NULL auto_increment,
`section` varchar(50) NOT NULL,
`description` varchar(255) NOT NULL,
`alias` varchar(50) NOT NULL,
`keywords` varchar(255) NOT NULL,
`sectionhomemethod` varchar(50) default '',
`sectionpagemethod` varchar(50) default '',
`defaulttemplate` varchar(30) NOT NULL,
`listtemplate` varchar(30) NOT NULL,
`items` int(11) NOT NULL,
`html` tinyint(4) NOT NULL default '0',
`orderby` mediumint(9) NOT NULL,
PRIMARY KEY(`id`)
) [engine][mysqlcharset];
INSERT INTO `ak_sections` (`id`, `section`, `description`, `items`, `orderby`) VALUES (1, 'default', '', 0, 0);

DROP TABLE `ak_settings`;
CREATE TABLE `ak_settings` (
`variable` varchar(25) NOT NULL,
`value` text NOT NULL,
`type` char(6) NOT NULL,
`standby` varchar(255) NOT NULL,
PRIMARY KEY(`variable`)
) [engine][mysqlcharset];
INSERT INTO `ak_settings` (`variable`, `value`, `type`, `standby`) VALUES
('sitename', 'AKCMS', 'char', ''),
('language', '[language]', 'select', 'chinese,english'),
('ipp', '10', 'select', '10,20,30,40,50,60'),
('html', '0', 'bin', '1,0'),
('usefilename', '1', 'bin', '1,0'),
('htmlexpand', '.htm', 'char', ''),
('maxattachsize', '2048', 'int', ''),
('defaultfilename', 'index.htm', 'char', ''),
('forbidinclude', '0', 'bin', '0,1'),
('forbidstat', '0', 'bin', '0,1'),
('forbidautorefresh', '0', 'bin', '0,1'),
('forbidspider','0','bin', '0,1'),
('forbidclearspace','1','bin', '0,1'),
('statcachesize', '128', 'int', ''),
('keywordslink', '', 'char', ''),
('emailreport', 'no', 'select', 'no,byday,byweek,bymonth'),
('adminemail', 'your@email.com', 'char', ''),
('bbstype', 'nobbs', 'select', 'nobbs,discuz,phpwind,phpbb'),
('bbstablepre', '', 'char', ''),
('blogtype','noblog', 'select', 'noblog,xspace'),
('blogtablepre', '', 'char', ''),
('homepage', '', 'char', ''),
('systemurl', '', 'char', ''),
('smtpemail','akcms_snedmail@126.com','char',''),
('smtphost','smtp.126.com','char',''),
('smtpport','25','int',''),
('smtpaccount','akcms_sendmail','char',''),
('smtppassword','mantou','pass',''),
('attachtemplate', '[attachtemplate]', 'char', ''),
('itemcolorshow', '1', 'bin', '1,0'),
('itemstyleshow', '1', 'bin', '1,0'),
('itemshorttitleshow', '1', 'bin', '1,0'),
('itemaimurlshow', '1', 'bin', '1,0'),
('itemauthorshow', '1', 'bin', '1,0'),
('itemsourceshow', '1', 'bin', '1,0'),
('itemsectionshow', '1', 'bin', '1,0'),
('itemtemplateshow', '1', 'bin', '1,0'),
('itemfilenameshow', '1', 'bin', '1,0'),
('itemdigestshow', '1', 'bin', '1,0'),
('itemkeywordsshow', '1', 'bin', '1,0'),
('itempictureshow', '1', 'bin', '1,0'),
('itemordershow', '1', 'bin', '1,0'),
('itemattachshow', '1', 'bin', '1,0'),
('itemhtmlshow', '1', 'bin', '1,0'),
('autoparsekeywords', '0', 'bin', '1,0'),
('autoparsefilename', '0', 'bin', '1,0'),
('storemethod', '[categorypath]/[f]', 'char', ''),
('categoryhomemethod', '[categorypath]/index.htm', 'char', ''),
('categorypagemethod', '[categorypath]/index-[page].htm', 'char', ''),
('sectionhomemethod', '[sectionalias]/index.htm', 'char', ''),
('sectionpagemethod', '[sectionalias]/index-[page].htm', 'char', ''),
('imagemethod', 'pictures/[y]/[m]/[id]-[f]', 'char', ''),
('attachmethod', 'attaches/[y]/[m]/[id]-[f]', 'char', ''),
('previewmethod', 'previews/[y]/[m]/[id]-[f]', 'char', ''),
('attachimagequality', '80', 'select', '10,20,30,40,50,60,70,80,90,100'),
('attachwatermarkposition', '9', 'select', '-1,0,1,2,3,4,5,6,7,8,9'),
('richtext', '1', 'bin', '1,0'),
('commentfrequencelimit', '30', 'int', ''),
('scorefrequencelimit', '30', 'int', ''),
('commentneedcaptcha', '1', 'bin', '1,0'),
('extfields', 'a:0:{}', 'text', '')
;

DROP TABLE `ak_stats`;
CREATE TABLE `ak_stats` (
`dateline` int(11) NOT NULL,
`value` int(11) NOT NULL default '0',
`type` tinyint(4) NOT NULL default '0',
`finished` tinyint(4) NOT NULL default '0',
`bywhat` char(5) NOT NULL default 'day',
UNIQUE KEY `dateline` (`dateline`,`type`,`bywhat`)
) [engine][mysqlcharset];

DROP TABLE `ak_texts`;
CREATE TABLE `ak_texts` (
`id` int(10) unsigned NOT NULL auto_increment,
`itemid` int(11) NOT NULL,
`text` text NOT NULL,
`page` smallint(6) NOT NULL default '0',
PRIMARY KEY(`id`),
UNIQUE KEY `itemid_2` (`itemid`,`page`)
) [engine][mysqlcharset];

DROP TABLE `ak_crons`;
CREATE TABLE `ak_crons` (
`id` int(11) NOT NULL auto_increment,
`type` smallint(6) NOT NULL,
`day` smallint(6) NOT NULL,
`date` smallint(6) NOT NULL,
`hour` smallint(6) NOT NULL,
`minute` smallint(6) NOT NULL,
`itemid` int(11) NOT NULL,
`lasttime` int(11) NOT NULL,
`job` VARCHAR( 10 ) NOT NULL,
PRIMARY KEY(`id`),
UNIQUE KEY `itemid` (`itemid`,`job`)
) [engine][mysqlcharset];

DROP TABLE `ak_variables`;
CREATE TABLE `ak_variables` (
`variable` varchar(30) NOT NULL,
`description` text NOT NULL,
`value` text NOT NULL,
PRIMARY KEY(`variable`)
) [engine][mysqlcharset];

DROP TABLE `ak_spiders`;
CREATE TABLE `ak_spiders` (
`id` smallint(5) unsigned NOT NULL auto_increment,
`spidername` varchar(50) NOT NULL,
`rule` smallint(5) NOT NULL,
`lasttime` int(11) NOT NULL,
`data` text NOT NULL,
PRIMARY KEY(`id`)
) [engine][mysqlcharset];

DROP TABLE `ak_spiderrules`;
CREATE TABLE `ak_spiderrules` (
`id` smallint(5) unsigned NOT NULL auto_increment,
`spiderrulename` varchar(50) NOT NULL,
`exampleurl` text NOT NULL,
`title` varchar(50) NOT NULL,
`aimurl` varchar(50) NOT NULL,
`shorttitle` varchar(50) NOT NULL,
`author` varchar(50) NOT NULL,
`source` varchar(50) NOT NULL,
`editor` varchar(50) NOT NULL,
`orderby` mediumint(9) NOT NULL,
`html` tinyint(4) NOT NULL,
`digest` varchar(255) NOT NULL,
`text` varchar(255) NOT NULL,
`keywords` varchar(50) NOT NULL,
`filename` varchar(255) NOT NUll,
`picture` varchar(50) NOT NULL,
`field1` varchar(255) NOT NULL,
`field2` varchar(255) NOT NULL,
`field3` varchar(255) NOT NULL,
`field4` varchar(255) NOT NULL,
`field5` varchar(255) NOT NULL,
`field6` varchar(255) NOT NULL,
`field7` varchar(255) NOT NULL,
`field8` varchar(255) NOT NULL,
`field9` varchar(255) NOT NULL,
`field10` varchar(255) NOT NULL,
`field11` varchar(255) NOT NULL,
`field12` varchar(255) NOT NULL,
`field13` varchar(255) NOT NULL,
`field14` varchar(255) NOT NULL,
`field15` varchar(255) NOT NULL,
`field16` varchar(255) NOT NULL,
`field17` varchar(255) NOT NULL,
`field18` varchar(255) NOT NULL,
`field19` varchar(255) NOT NULL,
`field20` varchar(255) NOT NULL,
`replace` varchar(255) NOT NULL,
`items` int(11) NOT NULL,
`extfields` text not null default '',
PRIMARY KEY(`id`)
) [engine][mysqlcharset];

DROP TABLE `ak_spidercatched`;
CREATE TABLE `ak_spidercatched` (
`id` int(11) unsigned NOT NULL auto_increment,
`key` char(16) NOT NULL,
`url` varchar(255) NOT NULL,
`dateline` int(11) NOT NULL,
`rule` smallint(5) NOT NULL,
`itemid` int(11) NOT NULL,
PRIMARY KEY(`id`),
UNIQUE KEY `key` (`key`)
) [engine][mysqlcharset];

DROP TABLE `ak_visits`;
CREATE TABLE `ak_visits` (
`id` int(11) unsigned NOT NULL auto_increment,
`sid` char(16) NOT NULL,
`dateline` int(11) NOT NULL,
`referer` varchar(255) NOT NULL,
`itemid` int(11) NOT NULL,
`type` varchar(10) NOT NULL,
`ip` varchar(15) NOT NULL,
PRIMARY KEY(`id`),
UNIQUE KEY `ip` (`ip`,`dateline`)
)[engine][mysqlcharset];

DROP TABLE `ak_captchas`;
CREATE TABLE `ak_captchas` (
`sid` char(6) NOT NULL,
`captcha` char(4) NOT NULL,
`dateline` int(11) NOT NULL,
PRIMARY KEY(`sid`)
)ENGINE = MEMORY[mysqlcharset];
EOT;
$nodb = 1;
require_once './include/common.inc.php';
if(frequence($_SERVER['PHP_SELF'], '/') < 2) exit('please move akcms to a folder before install it.');
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
	if(!preg_match('/^[0-9a-zA-Z_]+$/i', $post_dbname_tag)) {
		adminmsg($lan['dbnameerror'], 'back', 3, 1);
	}
	if(!preg_match('/^[0-9a-zA-Z]+$/i', $post_tablepre_tag)) {
		adminmsg($lan['tablepreerror'], 'back', 3, 1);
	} else {
		$tablepre = $post_tablepre_tag;
	}
	
	if(!$db_conn = @mysql_connect($post_dbhost_tag, $post_dbuser_tag, $post_dbpw_tag)) {
		adminmsg($lan['connecterror'], 'back', 3, 1);
	} else {
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
		if($post_charset_tag == 'utf8') {
			$mysql_charset = ' DEFAULT CHARACTER SET utf8 COLLATE utf8_bin';
		} elseif($post_charset_tag == 'gbk') {
			$mysql_charset = ' DEFAULT CHARACTER SET gbk COLLATE gbk_chinese_ci';
		} elseif($post_charset_tag == 'english') {
			$mysql_charset = ' DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci';
		}
		if(!mysql_select_db($post_dbname_tag, $db_conn)) {
			$createdatabasesql = 'CREATE DATABASE '.$post_dbname_tag;
			if(mysql_get_server_info() > '4.1') {
				$createdatabasesql .= $mysql_charset;
			}
			mysql_query($createdatabasesql, $db_conn);
		}
		mysql_close($db_conn);
		$tablepre = $post_tablepre_tag;
		require_once AK_ROOT.'./include/db_mysql.class.php';
		$db = new dbstuff;
		$db->connect($post_dbhost_tag, $post_dbuser_tag, $post_dbpw_tag, $post_dbname_tag, 0);
		if($db->version > '4.1') {
			$sql = str_replace('[engine]', 'ENGINE=MyISAM', $sql);
		} else {
			$mysql_charset = '';
			$sql = str_replace('[engine]', '', $sql);
		}
		querysql($sql, $mysql_charset);
		createfore();
		if(!empty($post_theme)) {
			installtheme('default', 1);
		}
		ak_touch(AK_ROOT.'./include/install.lock');
		updatecache('settings');
		adminmsg($lan['installsuccess'], 'login.php');
	}
} elseif(!isset($_GET['language'])) {
	displaytemplate('chooselanguage.htm');
} else {
	$smarty->assign('sysname', $sysname);
	$smarty->assign('sysedition', $sysedition);
	$smarty->assign('servertime', date('Y-m-d H:i:s', time()));
	$smarty->assign('dbhost', $dbhost);
	$smarty->assign('dbuser', $dbuser);
	$smarty->assign('dbpw', $dbpw);
	$smarty->assign('dbname', $dbname);
	$smarty->assign('tablepre', $tablepre);
	$smarty->assign('language', $language);
	$smarty->assign('timedifference', $timedifference);
	displaytemplate('install.htm');
}
function querysql($sql, $mysql_charset) {
	global $lang, $dbcharset, $tablepre, $db, $language;
	$sql = str_replace("\r", "\n", str_replace('`ak_', '`'.$tablepre.'_', $sql));
	$sql = str_replace('[mysqlcharset]', $mysql_charset, $sql);
	$sql = str_replace('DROP TABLE', 'DROP TABLE IF EXISTS', $sql);
	$sql = applylanguage($sql, $language);
	runquery($sql);
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
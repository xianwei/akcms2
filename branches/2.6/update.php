<?php
require_once './include/common.inc.php';
require_once './include/admin.inc.php';
if(isset($post_sourceversion)) {
	if($post_sourceversion <= '2.3') {
		$sql = <<<EOT
REPLACE INTO `ak_settings` (`variable`,`value`,`type`,`standby`)VALUES
('commentneedcaptcha', '1', 'bin', '1,0'),
('usefilename', '1', 'bin', '1,0'),
('storemethod', '[categorypath]/[f]', 'char', ''),
('categoryhomemethod', '[categorypath]/index.htm', 'char', ''),
('categorypagemethod', '[categorypath]/index-[page].htm', 'char', ''),
('sectionhomemethod', '[sectionalias]/index.htm', 'char', ''),
('sectionpagemethod', '[sectionalias]/index-[page].htm', 'char', ''),
('imagemethod', 'pictures/[y]/[m]/[id]-[f]', 'char', ''),
('attachmethod', 'attaches/[y]/[m]/[id]-[f]', 'char', ''),
('previewmethod', 'previews/[y]/[m]/[id]-[f]', 'char', '')
;
ALTER TABLE `ak_categories` CHANGE `storemethod` `storemethod` VARCHAR( 50 ) DEFAULT '';
ALTER TABLE `ak_categories` ADD `categorypagemethod` VARCHAR( 50 ) DEFAULT '';
ALTER TABLE `ak_categories` ADD `categoryhomemethod` VARCHAR( 50 ) DEFAULT '';
ALTER TABLE `ak_variables` CHANGE `value` `value` TEXT NOT NULL;
ALTER TABLE `ak_variables` ADD `description` TEXT NOT NULL;
ALTER TABLE `ak_categories` ADD `usefilename` TINYINT(4) NOT NULL;
ALTER TABLE `ak_sections` ADD `sectionhomemethod` varchar(50) NOT NULL;
ALTER TABLE `ak_sections` ADD `sectionpagemethod` varchar(50) NOT NULL;

ALTER TABLE `ak_sections` ADD `defaulttemplate` varchar(30) NOT NULL;
ALTER TABLE `ak_sections` ADD `listtemplate` varchar(30) NOT NULL;
ALTER TABLE `ak_sections` ADD `html` tinyint(4) NOT NULL;
UPDATE `ak_settings` SET value=CONCAT('.',value) WHERE variable='htmlexpand';
EOT;
		$sql = str_replace('`ak_', "`{$tablepre}_", $sql);
		runquery($sql);
		@rename(AK_ROOT.'templates/'.$template_path.'/,category.htm', AK_ROOT.'templates/'.$template_path.'/,category_home.htm');
	}
	if($post_sourceversion <= '2.4') {
		$sql = <<<EOT
ALTER TABLE `ak_settings` CHANGE `value` `value` TEXT NOT NULL;
REPLACE INTO `ak_settings` (`variable`,`value`,`type`,`standby`)VALUES
('extfields', 'a:0:{}', 'text', '')
;
ALTER TABLE `ak_categories` ADD `itemextfields` text NOT NULL;
ALTER TABLE `ak_items` ADD `orderby2` mediumint(9) DEFAULT '0';
ALTER TABLE `ak_items` ADD `orderby3` mediumint(9) DEFAULT '0';
ALTER TABLE `ak_items` ADD `orderby4` mediumint(9) DEFAULT '0';
ALTER TABLE `ak_item_exts` ADD `value` TEXT NOT NULL;
ALTER TABLE `ak_spiderrules` ADD `extfields` text NOT NULL;
ALTER TABLE `ak_stats` CHANGE `by` `bywhat` CHAR( 5 ) NOT NULL DEFAULT 'day'
EOT;
		$sql = str_replace('`ak_', "`{$tablepre}_", $sql);
		runquery($sql);
	}
	if($post_sourceversion <= '2.5') {
		$db->query("ALTER TABLE `{$tablepre}_spiderrules` ADD `data` TEXT NOT NULL");
		$query = $db->list_by('*', 'spiderrules');
		while($row = $db->fetch_array($query)) {
			$row['replace'] = str_replace(',', "\n", $row['replace']);
			$data = serialize($row);
			$db->update('spiderrules', array('data' => $data), "id='{$row['id']}'");
		}
		$db->query("INSERT INTO `{$tablepre}_categories`(id, category, html, usefilename)VALUES('-1', 'keywords', '-1', '-1')");
		$db->query("INSERT INTO `{$tablepre}_settings`(variable, value, type, standby)VALUES('globalkeywordstemplate', '<a href=\"[url]\" title=\"[digest]\">[keyword]</a>', 'char', '')");
	}
	updatecache();
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>AKCMS升级</title>
</head>

<body>
<form  method="post" action="update.php">
  请选择旧版本版本号
  <select name="sourceversion">
  	<option value="2.3">2.3</option>
  	<option value="2.4">2.4</option>
  	<option value="2.5">2.5</option>
  </select>
  <input type="submit" name="Submit" value="提交" />
</form>
说明：<br>
1 2.6之前的版本的采集设置因变动较大无法升级，请重新设置，给您造成的不便敬请谅解。<br>
</body>
</html>

<?php
require_once '../include/common.inc.php';
require_once '../include/admin.inc.php';
if(!isset($get_sure)) {
	adminmsg('������ֻ��ִ�д�AKCMS2.5��2.6������������������رմ��ڣ����������·����ӿ�ʼ������', 'update2.5-2.6.php?sure=1', 10000000, 0);
} else {
	$db->query("ALTER TABLE `{$tablepre}_spiderrules` ADD `data` TEXT NOT NULL");
	$query = $db->list_by('*', 'spiderrules');
	while($row = $db->fetch_array($query)) {
		$row['replace'] = str_replace(',', "\n", $row['replace']);
		$data = serialize($row);
		$db->update('spiderrules', array('data' => $data), "id='{$row['id']}'");
	}
	$db->query("ALTER TABLE `ak_items` CHANGE `lastreplay` `lastreply` INT( 11 ) DEFAULT '0'");
	$db->query("INSERT INTO `{$tablepre}_categories`(id, category, html, usefilename)VALUES('-1', 'keywords', '-1', '-1')");
	$db->query("INSERT INTO `{$tablepre}_settings`(variable, value, type, standby)VALUES('globalkeywordstemplate', '<a href=\"[url]\" title=\"[digest]\">[keyword]</a>', 'char', '')");
	updatecache();
	debug('�ɹ���2.5������2.6��');
}
?>
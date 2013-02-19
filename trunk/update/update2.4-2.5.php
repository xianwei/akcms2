<?php
require_once '../include/common.inc.php';
require_once '../include/admin.inc.php';
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
if(!isset($get_sure)) {
	adminmsg('本程序只能执行从AKCMS2.4到2.5的升级！不想升级请关闭窗口，升级请点击下方链接开始升级！', 'update2.4-2.5.php?sure=1', 10000000, 0);
} else {
	$sql = str_replace('`ak_', "`{$tablepre}_", $sql);
	runquery($sql);
	debug('成功从2.4升级到2.5！');
}
?>
<?php
require_once '../include/common.inc.php';
require_once '../include/admin.inc.php';
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
if(!isset($get_sure)) {
	adminmsg('本程序只能执行从AKCMS2.3到2.4的升级！不想升级请关闭窗口，升级请点击下方链接开始升级！', 'update2.3-2.4.php?sure=1', 10000000, 0);
} else {
	$sql = str_replace('`ak_', "`{$tablepre}_", $sql);
	runquery($sql);
	@rename(AK_ROOT.'templates/'.$template_path.'/,category.htm', AK_ROOT.'templates/'.$template_path.'/,category_home.htm');
	debug('成功从2.3升级到2.4！');
}
?>
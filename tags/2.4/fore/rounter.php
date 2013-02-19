<?php
//本文件不能自己执行必须由前台调用
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
if(empty($get_filename)) exit('error');
$sql = "SELECT * FROM {$tablepre}_filenames WHERE filename='$get_filename'";
$db = db();

if($html = $db->get_one($sql)) {
	if($html['type'] == 'item') {
		foredisplay($html['id'], 'item');
	}
} else {
	exit('error2');
}
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
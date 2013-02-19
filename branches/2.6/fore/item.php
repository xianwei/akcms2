<?php
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
if(isset($get_id)) {
	$id = $get_id;
}
if(!isset($id)) exit('');
if(!isset($template)) $template = '';
foredisplay($id, 'item', $template);
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
<?php
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
if(empty($get_filename)) rountererror();
$filename = ak_addslashes($get_filename);
if($html = $db->get_by('*', 'filenames', "filename='$filename'")) {
	foredisplay($html['id'], 'item');
} else {
	rountererror();
}

function rountererror() {
	global $get_filename, $homepage;
	error_log($get_filename."\n", 3, AK_ROOT."logs/error_rounter.txt");
	header("HTTP/1.0 404 Not Found");
}
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
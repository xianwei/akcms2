<?php
if(file_exists('./resetpassword.php')) {
	exit('please remove resetpassword.php first.');
}
if(empty($admin_id)) {
	if(empty($_SERVER['QUERY_STRING'])) {
		header("location:".AK_URL."login.php");
	} else {
		header("location:".AK_URL."login.php?preurl=".urlencode($_SERVER['REQUEST_URI']));
	}
}
$db = db();
$language = isset($setting_language) ? $setting_language : 'english';
includelanguage();
$smarty->assign('lan', $lan);
$smarty->assign('sysname', $sysname);
$smarty->assign('sysedition', $sysedition);
$smarty->assign('header_charset', $header_charset);
if(!isset($setting_sitename)) $setting_sitename = '';
$smarty->assign('sitename', $setting_sitename);
?>
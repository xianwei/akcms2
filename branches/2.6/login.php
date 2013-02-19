<?php
require_once './include/common.inc.php';
$db = db();
includelanguage();
if(isset($post_loginsubmit)) {
	if($editor = $db->get_by('*', 'admins', "editor='$post_username'")) {
		if(md5($post_password) == $editor['password']) {
			if($editor['freeze'] == 1) adminmsg($lan['youarefreeze'], 'admincp.php', 3, 1);
			$encoded = authcode($post_username, 'ENCODE');
			if(!empty($post_rememberlogin)) {
				setcookie('auth', $encoded, $thetime + 24 * 3600 * 365 * 10);
			} else {
				setcookie('auth', $encoded);
			}
			if(empty($post_preurl)) {
				$preurl = 'admincp.php';
			} else {
				$preurl = 'admincp.php?preurl='.urlencode($post_preurl);
			}
			adminmsg($lan['login_success'], $preurl);
		} else {
			adminmsg($lan['login_failed'], 'login.php', 3, 1);
		}
	} else {
		adminmsg($lan['login_failed'], 'login.php', 3, 1);
	}
} else {
	$smarty->assign('lan', $lan);
	$smarty->assign('sysname', $sysname);
	$smarty->assign('sysedition', $sysedition);
	$smarty->assign('header_charset', $header_charset);
	!isset($get_preurl) && $get_preurl = '';
	if(strpos(strtolower($get_preurl), 'script') !== false) $get_preirl = '';
	$smarty->assign('preurl', $get_preurl);
	displaytemplate('login.htm');
}
?>
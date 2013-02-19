<?php
require_once './include/common.inc.php';
require_once './include/admin.inc.php';
checkcreator();
!isset($get_action) && $get_action = '';
if($get_action == '') {
	$paths = readpathtoarray(AK_ROOT.'/themes', 1);
	$themes = '';
	foreach($paths as $path) {
		if(substr($path, 0, 1) == '.' || !is_dir(AK_ROOT.'/themes/'.$path)) continue;
		$readme = readfromfile(AK_ROOT.'/themes/'.$path.'/readme.txt');
		$readme = nl2br($readme);
		$themes .= "<tr><td width=\"50\" valign=\"top\">{$path}</td><td valign=\"top\"><b>{$lan['readme']}</b><br>{$readme}</td><td width=\"50\" align=\"center\" valign=\"middle\"><a href=\"theme.php?action=import&theme={$path}\">".$lan['install']."</a></td><td width=\"100\" align=\"center\" valign=\"middle\"><a href=\"theme.php?action=import&theme={$path}&sql=1\">".$lan['installandimportdata']."</a></td></tr>";
	}
	$smarty->assign('themes', $themes);
	displaytemplate('admincp_themes.htm');
} elseif($get_action == 'import') {
	if(isset($get_theme)) $theme = $get_theme;
	if($theme == '') exit('error');
	installtheme($theme, isset($get_sql));
	adminmsg($lan['themeinstallsuccess']);
}
aexit();
?>
<?php
require_once 'include/common.inc.php';
require_once 'include/admin.inc.php';
$db = db();
!isset($get_action) && $get_action = '';
if($get_action == 'phpinfo') {
	if(function_exists('phpinfo')) {
		phpinfo();
	} else {
		adminmsg('phpinfo()'.$lan['functiondisabled'], '', 0, 1);
	}
} elseif($get_action == 'checkwritable') {
	$array_paths = array(
		'../',
		'./cache/',
		'./templates/',
		'./templates_c/'
	);
	$array_files = array(
		'./config.inc.php'
	);
	$message = '';
	foreach($array_paths as $path) {
		if(!is_writable($path)) {
			$message .= $path . $lan['isunwritable'].'<br>';
		}
	}
	foreach($array_files as $file) {
		if(!is_writable($file)) {
			$message .= $file . $lan['isunwritable'].'<br>';
		}
	}
	if(!empty($message)) {
		adminmsg($lan['writableerror'].'<br>'.$message, 'admincp.php?action=welcome', 3, 1);
	} else {
		adminmsg($lan['writableok'], 'welcome.php');
	}
} elseif($get_action == 'optimize') {
	if('sqlite' == $__dbtype) {
		adminmsg($lan['sqliteunsupport'], 'welcome.php', 3, 1);
	}
	$sql = "OPTIMIZE TABLE `{$tablepre}_admins` , `{$tablepre}_attachments` , `{$tablepre}_categories` , `{$tablepre}_filenames` , `{$tablepre}_items` , `{$tablepre}_sections` , `{$tablepre}_settings` , `{$tablepre}_texts` , `{$tablepre}_variables`";
	$db->query($sql);
	adminmsg($lan['operatesuccess'], 'welcome.php');
} elseif($get_action == 'updatecache') {
	updatecache();
	refreshpv();
	adminmsg($lan['operatesuccess'], 'welcome.php');
} elseif($get_action == 'copyfront') {
	createfore();
	adminmsg($lan['operatesuccess'], 'welcome.php');
} elseif($get_action == 'runsql') {
	if(isset($post_sql))  {
		$query = $db->query($post_sql);
		$body = '';
		if(is_resource($query)) {
			while($result = $db->fetch_array($query)) {
				if(empty($header)) {
					$header = '<tr class="header">';
					foreach(array_keys($result) as $field) {
						$header .= '<td>'.htmlspecialchars($field).'</td>';
					}
					$header .= '</tr>';
				}
				$body .= "<tr>";
				foreach($result as $value) {
					$body .= "<td>".htmlspecialchars($value)."</td>";
				}
				$body .= "</tr>";
			}
			if($body != '') {
				$result = "<table class=\"commontable\" cellspacing=\"1\" cellpadding=\"4\">{$header}{$body}</table><br>";
				$smarty->assign('result', $result);
			}
		}
	}
	displaytemplate('admincp_runsql.htm');
} elseif($get_action == 'updatefilenames') {
	$db->query("DELETE FROM {$tablepre}_filenames");
	$query = $db->query("SELECT * FROM {$tablepre}_items");
	while($item = $db->fetch_array($query)) {
		$filename = htmlname($item['id'], $item['category'], $item['dateline'], $item['filename']);
		$db->query("INSERT INTO {$tablepre}_filenames(filename,type,dateline,id,page)VALUES('$filename','item','$thetime','{$item['id']}','0')");
	}
	adminmsg($lan['operatesuccess']);
} else {
	includecache('infos');
	$servertime = date('Y-m-d H:i:s', time());
	$correcttime = date('Y-m-d H:i:s', $thetime);
	isset($_ENV['TERM']) && $os = $_ENV['TERM'];
	$max_upload = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled';
	$maxexetime = ini_get('max_execution_time');
	$smarty->assign('items', $infos['items']);
	$smarty->assign('pvs', $infos['pvs']);
	$smarty->assign('editors', $infos['editors']);
	$smarty->assign('attachmentsizes', empty($infos['attachmentsizes']) ? 0 : $infos['attachmentsizes']);
	$smarty->assign('attachments', $infos['attachments']);
	$smarty->assign('admin_id', $admin_id);
	$smarty->assign('os', $os);
	$smarty->assign('phpversion', PHP_VERSION);
	$smarty->assign('dbversion', $db->version);
	$smarty->assign('akversion', $sysedition);
	$smarty->assign('iscreator', iscreator());
	$smarty->assign('maxupload', $max_upload);
	$smarty->assign('maxexetime', $maxexetime);
	$smarty->assign('servertime', $servertime);
	$smarty->assign('correcttime', $correcttime);
	$smarty->assign('dbtype', $__dbtype);
	displaytemplate('admincp_welcome.htm');
}
runinfo();
aexit();
?>
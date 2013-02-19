<?php
require_once(AK_ROOT.'include/messages.inc.php');
function msg_visit($message, $url_forward = '', $timeout = 3, $flag = 0) {//$flag表明：信息还是警告0信息1警告
	global $smarty, $globalvariables;
	includecache('globalvariables');
	foreach($globalvariables as $id => $v) {
		$smarty->assign('v_'.$id, $v);
	}
	if($flag == 0) {
		$flag = 'info';
	} else {
		$flag = 'warning';
	}
	$variables = array(
		'message' => $message,
	);
	
	$smarty->assign('flag', $flag);
	$smarty->assign('url_forward', $url_forward);
	$smarty->assign('timeout', $timeout);
	$smarty->assign('timeout_micro', $timeout * 1000);
	$html = render_template($variables, 'message.htm');
	echo($html);
	aexit_visit();
}

function aexit_visit() {
	exit('');
}

if(!empty($get_utf8)) {
	foreach($_GET as $key => $value) {
		$_GET[$key] = utf8togbk($value);
	}
}
?>
<?php
error_reporting(E_ALL);
set_magic_quotes_runtime(0);
if(substr(PHP_OS, 0, 3) == 'WIN' || (isset($_ENV['OS']) && strstr($_ENV['OS'], 'indow'))) {
	$os = 'windows';
	$separator = '\\';
	$lr = "\r\n";
} else {
	$os = 'linux';
	$separator = '/';
	$lr = "\n";
}
if(isset($_SERVER['REQUEST_URI']) || isset($_SERVER['HTTP_X_REWRITE_URL'])) {
	$callmode = 'web';
	$host = $_SERVER['SERVER_NAME'];
	$currenturl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_X_REWRITE_URL'];
	ob_start();
} else {
	$callmode = 'command';
}

$start_time =  microtime(1);
define('AK_ROOT', substr(dirname(__FILE__), 0, -7));
require_once AK_ROOT.'./include/admin.func.php';
require_once AK_ROOT.'./config.inc.php';
require_once AK_ROOT.'./include/runconfig.inc.php';
require_once AK_ROOT.'./include/common.func.php';
define('FORE_ROOT', AK_ROOT.'../');
if($callmode == 'web') {
	$_p1 = strrpos(substr(AK_ROOT, 0, -1), $separator);
	$system_root = substr(AK_ROOT, $_p1 + 1, -1);
	$_p2 = ak_strrpos($currenturl, "/{$system_root}/") + 1;
	define('FORE_URL', substr($currenturl, 0, $_p2));
	define('AK_URL', FORE_URL.$system_root.'/');
}
if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}

$magic_quotes_gpc = get_magic_quotes_gpc();
$register_globals = @ini_get('register_globals');

if($register_globals) {
	if(is_array($_POST)) {
		foreach($_POST as $key => $value) {
			unset($$key);
		}
	}
	if(is_array($_GET)) {
		foreach($_GET as $key => $value) {
			unset($$key);
		}
	}
	if(is_array($_COOKIE)) {
		foreach($_COOKIE as $key => $value) {
			unset($$key);
		}
	}
}
if($magic_quotes_gpc) {
	$_POST = unaddslashes($_POST);
	$_GET = unaddslashes($_GET);
	$_COOKIE = unaddslashes($_COOKIE);
}
@extract($_POST, EXTR_PREFIX_ALL, 'post');
@extract($_GET, EXTR_PREFIX_ALL, 'get');
@extract($_FILES, EXTR_PREFIX_ALL, 'file');
@extract($_COOKIE, EXTR_PREFIX_ALL, 'cookie');
$mtime = explode(' ', microtime());
!isset($timedifference) && $timedifference = 0;
$thetime = time() + $timedifference * 3600;
$timestamp = time() + $timedifference * 3600;
$wee = getwee();
if($charset == 'gbk') {
	$header_charset = 'gbk';
	$db_setname = 'gbk';
} elseif($charset == 'utf8') {
	$header_charset = 'utf-8';
	$db_setname = 'utf8';
} elseif($charset == 'english') {
	$header_charset = 'iso-8859-1';
	$db_setname = 'latin1';
}
if(!isset($nodb)) $db = db();
header('Content-Type:text/html;charset='.$header_charset);
if(!preg_match('/install.php/i', $_SERVER['PHP_SELF']) && !preg_match('/language.php/i', $_SERVER['PHP_SELF'])) {
	includecache('settings');
}
@extract($settings, EXTR_PREFIX_ALL, 'setting');
if('web' == $callmode) {
	if(!empty($setting_homepage)) {
		if(substr($setting_homepage, -1) != '/') $setting_homepage .= '/';
		$homepage = $setting_homepage;
	} else {
		$homepage = FORE_URL;
	}
	if(!empty($setting_systemurl)) {
		if(substr($setting_systemurl, -1) != '/') $setting_systemurl .= '/';
		$systemurl = $setting_systemurl;
	} else {
		$systemurl = AK_URL;
	}
}
$language = isset($setting_language) ? $setting_language : 'english';
if(isset($cookie_auth)) {
	$admin_id = authcode($cookie_auth, 'DECODE');
}
$onlineip = '127.0.0.1';
if('web' == $callmode) {
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	$onlineip = preg_replace("/^([\d\.]+).*/", "\\1", $onlineip);
}
require(AK_ROOT.'./smarty/libs/Smarty.class.php');

$smarty=new Smarty;
$smarty->template_dir = AK_ROOT."./templates";
$smarty->compile_dir = AK_ROOT."./templates_c";
$smarty->config_dir = AK_ROOT."./configs/";
$smarty->cache_dir = AK_ROOT."./cache/";
$smarty->left_delimiter = "<{";
$smarty->right_delimiter = "}>";
function authcode($string, $operation, $key = '') {
	$key = md5($key ? $key : $GLOBALS['codekey']);
	$key_length = strlen($key);

	$string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
	$string_length = strlen($string);

	$rndkey = $box = array();
	$result = '';

	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($key[$i % $key_length]);
		$box[$i] = $i;
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
			return substr($result, 8);
		} else {
			return '';
		}
	} else {
		return str_replace('=', '', base64_encode($result));
	}
}
?>
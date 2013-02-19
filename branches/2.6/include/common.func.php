<?php
function alert($text) {
	return '<font color=red><b>'.$text.'</b></font>';
}
function green($text) {
	return '<font color=green><b>'.$text.'</b></font>';
}
function b($text) {
	return '<b>'.$text.'</b>';
}
function iscreator($id = '') {
	global $admin_id;
	empty($id) && isset($admin_id) && $id = $admin_id;
	if(strtolower($id) == 'admin') return 1;
	return 0;
}
function disabled($text) {
	global $lan;
	return '<font color="gray" title="'.$lan['disabled'].'"><b>'.$text.'</b></font>';
}
function available($text) {
	global $lan;
	return '<font color="green" title="'.$lan['available'].'"><b>'.$text.'</b></font>';
}
function writetofile($text, $filename) {
	$path = pathinfo($filename);
	if(!is_dir($path['dirname'])) {
		ak_mkdir($path['dirname']);
	}
	if(!$fp = fopen($filename, 'w')) {
		return false;
	} else {
		flock($fp, LOCK_EX);
		fwrite($fp, $text);
		fclose($fp);
		return true;
	}
}
function readfromfile($filename) {
	if(substr($filename, 0, 7) != 'http://' && !is_readable($filename)) {
		return '';
	}
	if(PHP_VERSION < '4.3.0') {
		if(!$fp = fopen($filename, 'r')) {
			return false;
		} else {
			flock($fp, LOCK_EX);
			$return = '';
			while (!feof($fp)) {
				$return .= fgets($fp, 4096);
			}
			fclose($fp);
			return $return;
		}
	} else {
		return file_get_contents($filename);
	}
}

function ak_mkdir($dirname) {
	$a_path = explode('/', $dirname);
	if(count($a_path) == 0) {
		mkdir($dirname);
	} else {
		array_pop($a_path);
		$path = @implode('/', $a_path);
		if(is_dir($path.'/')) {
			@mkdir($dirname);
		} else {
			ak_mkdir($path);
			@mkdir($dirname);
		}
	}
}

function ak_touch($file) {
	$dir = dirname($file);
	ak_mkdir($dir);
	touch($file);
}

function ak_copy($source, $target) {
	global $os;
	if(!file_exists(dirname($target))) {
		ak_mkdir(dirname($target));
	}
	if(is_dir($source)) {
		return copydir($source, $target);
	}
	if(strpos($source, 'http://') !== false) {//下载
		if(ini_get('allow_url_fopen') == '1') {
			if(!$fp = fopen($source, 'r')) {
				return -1;
			} else {
				$data = '';
				while (!feof($fp)) {
					$data .= fgets($fp, 4096);
				}
				if($fp = fopen($target, 'w')) {
					fwrite($fp, $data);
					fclose($fp);
				} else {
					return -2;
				}
			}
		} else {
			if($os != 'windows' && function_exists('curl_init')) {
				$ch = curl_init($source);
				$fp = fopen($target, "w");

				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);

				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
			} else {
				return false;
			}
		}
	} else {
		return copy($source, $target);
	}
}

function copydir($dirf, $dirt) {
	if(!is_dir($dirf)) return false;
	$mydir = @opendir($dirf);
	@mkdir($dirt);
	while($file = @readdir($mydir)) {
		if((is_dir("$dirf/$file")) && ($file!=".") && ($file!="..")) {
			if(!@copydir("$dirf/$file","$dirt/$file"))return false;
		} elseif(is_file("$dirf/$file")) {
			if(!@copy("$dirf/$file","$dirt/$file"))return false;
		}
	}
	return true;
}

function table_start($title = '', $colspan = 10) {
	global $lan;
	return "<table width=\"98%\" border=\"0\" align=\"center\" cellpadding=\"5\" cellspacing=\"1\" class=\"commontable\"><tr class=\"header\">\n".
	"<td colspan=\"{$colspan}\"><div class=\"righttop\"><a href=\"http://www.akcms.com/manual/setting.htm\" target=\"_blank\">{$lan['help']}</a></div>{$title}</td>\n".
	"</tr>";
}

function table_end() {
	return "</table>\n<div class=\"block2\"></div>";
}

function table_next($title = '', $colspan = 10) {
	$output = table_end();
	$output .= table_start($title, $colspan);
	return $output;
}

function debug($variable, $exit = 0, $type = 0) {
	//type 0 输出到页面
	//type 1 alert打出来
	//type 2 js中alert打出来（不用加前后<script></script>）
	//type 3 print_r()，一般适用于命令行模式
	global $__callmode;
	if('command' == $__callmode) $type = 3;
	if($type != 3) $variable = ak_htmlspecialchars($variable);
	if(is_array($variable) || is_object($variable)) {
		$info = print_r($variable, 1);
	} else {
		$info = $variable;
	}
	if($type == 0) {
		$info = str_replace("\n", '<br>', $info);
		$info = str_replace(" ", '&nbsp;', $info);
		echo "<div class=\"mininum\" style=\"border:1px dashed #222222;margin:10px;font: 12px Verdan;line-height: 20px;background-color: #FFFFE0;padding: 10px;text-align:left;\">".$info."</div>";
	} elseif($type == 1) {
		$info = str_replace("\n", '\n', $info);
		echo "<script>alert(\"".$info."\");</script>";
	} elseif($type == 2) {
		$info = str_replace("\n", '\n', $info);
		echo "alert(\"".$info."\");";
	} elseif($type == 3) {
		echo($info."\n");
	}

	if($exit == 1) {
		exit('');
	}
}

function checkfilename($filename, $noempty = '') {
	global $lan, $system_root;
	if(empty($filename)) {
		if($noempty == '') {
			return '';
		} else {
			return $lan['noempty'];
		}
	}
	$fileext = fileext($filename);
	if($fileext == 'php') {
		return $lan['nophp'];
	}
	if(!preg_match('/^[\/\._0-9a-zA-Z\-]*$/i', $filename)) {
		return $lan['specialcharacter'];
	}
	if(preg_match('/^(\/)?[0-9]+\..*$/i', $filename)) {
		return $lan['filenameallnum'];
	}
	$array_forbiddenpaths = array($system_root, 'attach', 'headpic', 'images');
	foreach($array_forbiddenpaths as $p) {
		if(preg_match('/^\/'.$p.'\//i', $filename)) {
			return $lan['pathforbidden'];
		}
	}

	if(preg_match('/\.\.\//i', $filename)) {
		return $lan['parentpathforbidden'];
	}
	if(preg_match('/index_[0-9]*/i', $filename)) {
		return $lan['indexnameforbidden'];
	}
	return '';
}

function a_is_int($number) {
	if(substr($number, 0, 1) == '-') $number = substr($number, 1);
	return preg_match ("/^([0-9]+)$/", $number);
}

function random($length, $numeric = 0) {
        mt_srand((double)microtime() * 1000000);
        if($numeric) {
                $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
                $hash = '';
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
                $max = strlen($chars) - 1;
                for($i = 0; $i < $length; $i++) {
                        $hash .= $chars[mt_rand(0, $max)];
                }
        }
        return $hash;
}

function fileext($filename) {
        return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

function ispicture($filename) {
	$ext = fileext($filename);
	if(in_array($ext, array('gif', 'jpg', 'jpeg', 'png', 'bmp'))) return true;
	return false;
}

function uploadfile($file, $newname) {
	$file['tmp_name'] = str_replace('\\\\', '\\', $file['tmp_name']);
	if(!is_uploaded_file($file['tmp_name'])) return false;
	$path = pathinfo($newname);
	if(!is_dir($path['dirname'])) ak_mkdir($path['dirname']);
	return move_uploaded_file($file['tmp_name'], $newname);
}

function uploadtmpfile($file, $newname) {
	$file = str_replace('\\\\', '\\', $file);
	if(!is_uploaded_file($file)) return false;
	$path = pathinfo($newname);
	if(!is_dir($path['dirname'])) ak_mkdir($path['dirname']);
	return move_uploaded_file($file, $newname);
}
function showalert($show_message) {
	global $header_charset;
	$html = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset={$header_charset}\" /></head>\n";
	$html .= "<script>alert(\"{$show_message}\");</script>\n";
	$html .= "</html>";
	exit($html);
}
function refreshparent() {
	echo '<script>window.parent.location.reload();</script>';
}
function refreshself($timeout) {
	$script = <<<EOF
<script language="javascript">
setTimeout("document.location.reload()", [timeout]);
</script>
EOF;
	$script = str_replace('[timeout]', $timeout, $script);
	exit($script);
}

function amove_uploaded_file($source, $target) {//封装move_uploaded_file
	$target_path = dirname($target);
	if(!is_dir($target_path)) {
		@ak_mkdir($target_path);
	}
	return move_uploaded_file($source, $target);
}

function unaddslashes($str) {
	global $__dbtype;
	if(is_array($str)) {
		foreach($str as $key => $val) {
			$str[$key] = unaddslashes($val);
		}
	} else {
		if($__dbtype == 'mysql') {
			$str = stripslashes($str);
		} else {
			$str = stripslashes($str);
		}
	}
	return $str;
}

function ak_addslashes($value, $db_type = '') {
	global $__dbtype;
	if($db_type == '') $db_type = $__dbtype;
	if(is_array($value)) {
		foreach($value as $k => $v) {
			$value[$k] = ak_addslashes($v, $db_type);
		}
	} else {
		if($db_type == 'mysql') {
			$value = addslashes($value);
		} elseif($db_type == 'sqlite') {
			if(!empty($value)) $value = sqlite_escape_string($value);
		}
	}
	return $value;
}

function htmltitle($title, $color = '', $style = '') {
	$output = $title;
	if(!empty($style)) {
		if($style == 'b') $output = "<b>{$output}</b>";
		if($style == 'i') $output = "<i>{$output}</i>";
	}
	if(!empty($color)) $output = "<font color=\"#{$color}\">{$output}</font>";
	return $output;
}

function displaytemplate($template) {
	global $smarty, $lan;
	$templatefilename = $smarty->template_dir.'/'.$template;
	if(file_exists($templatefilename)) {
		$smarty->display($template);
	} else {
		adminmsg($lan['templatenotexist'], '', 0, 1);
	}
}

function texttemplate($template) {
	global $smarty, $lan;
	$templatefilename = $smarty->template_dir.'/'.$template;
	if(file_exists($templatefilename)) {
		return $smarty->text($template);
	} else {
		adminmsg($lan['templatenotexist'], '', 0, 1);
	}
}

function includecache($cachename, $forceupdate = 0) {
	global $$cachename;
	$cachefilename = AK_ROOT."cache/cache_{$cachename}.php";
	if(file_exists($cachefilename) && $forceupdate == 0) {
		require_once $cachefilename;
	} else {
		require_once AK_ROOT.'include/cache.func.php';
		updatecache($cachename);
		require_once $cachefilename;
	}
}

function includelanguage($name = '') {
	global $language, $charset, $lan, $smarty;
	$languagefilename = AK_ROOT."./language/{$language}/{$charset}.php";
	if(!file_exists($languagefilename)) exit($charset.' language not exist');
	require_once($languagefilename);
	$smarty->assign('lan', $lan);
}

function sendmail($to, $title, $content, $from = '', $type = 'HTML') {
	global $setting_adminemail, $setting_smtphost, $setting_smtpport, $setting_smtpaccount, $setting_smtppassword, $smtp, $setting_smtpemail;
	require_once(AK_ROOT.'./include/mail.inc.php');
	empty($from) && $from = $setting_smtpemail;
	empty($smtp) && $smtp = new smtp($setting_smtphost, $setting_smtpport, true, $setting_smtpaccount, $setting_smtppassword);
	$smtp->debug = 0;
	$to = tidyitemlist($to, ',', 0);
	$array_to = explode(',', $to);
	foreach($array_to as $to) {
		if(empty($to) || strpos($to, '@') === false) continue;
		$res = $smtp->sendmail($to, $from, $title, $content, $type);
		if($res === false) return false;
	}
	return true;
}

function ak_utf8_encode($var) {
	if(is_array($var)) {
		foreach($var as $id => $value) {
			$var[$id] = ak_utf8_encode($value);
		}
		return $var;
	} else {
		return iconv('GBK', 'UTF-8', $var);
	}
}

function utf8togbk($var) {
	if(is_array($var)) {
		foreach($var as $id => $value) {
			$var[$id] = utf8togbk($value);
		}
		return $var;
	} else {
		return @iconv('UTF-8', 'GBK', $var);
	}
}

function gbktoutf8($var) {
	if(is_array($var)) {
		foreach($var as $id => $value) {
			$var[$id] = gbktoutf8($value);
		}
		return $var;
	} else {
		return iconv('GBK', 'UTF-8', $var);
	}
}

function tidyitemlist($str, $separator = ',', $int = 1) {//ok 在列表大的时候会有性能问题
	$array = explode($separator, $str);
	$array = array_unique($array);
	$array2 = array();
	foreach($array as $item) {
		$item = trim($item);
		if($item != '' && !in_array($item, $array2) && (!$int || a_is_int($item))) {
			$array2[] = $item;
		}
	}
	return implode($separator, $array2);
}

function arraytoselect($array) {
	global $lr;
	if(!is_array($array)) {
		$array = Array($array);
	}
	$output = '';
	foreach($array as $id => $value) {
		$output .= "<option value=\"$id\">$value</option>{$lr}";
	}
	return $output;
}
function ak_substr($str, $start, $len = 0xFFFFFFFF, $strip = '', $charset_force = '') {
	global $charset;
	$old_length = strlen($str);
	$return = '';
	if(!empty($charset_force)) {
		$charset_str = $charset_force;
	} else {
		$charset_str = $charset;
	}
	if($charset_str == 'gbk') {
		$return = gbk_substr($str, $start, $len);
	} elseif($charset_str == 'utf8') {
		$return = utf8_substr($str, $start, $len);
	} else {
		$return = substr($str, $start, $len);
	}
	$new_length = strlen($return);
	if($new_length < $old_length) {
		$return .= $strip;
	}
	return $return;
}

function gbk_substr($str, $start, $len=0xFFFFFFFF) {
	if($start<0) {
		$start = strlen($str) + $start;
	}
	if($len<0) {
		$len = strlen($str) - $start + $len;
	}
	$tmp="";
	$result="";
	$strlen=strlen($str);
	$begin=0;
	$subLen=0;
	for($i=0; $i < $start + $len && $i < $strlen; $i++) {
		if($i < $start) {
			if(ord($str[$i]) >= 161 && ord($str[$i]) <= 247 && ord($str[$i+1]) >= 161 && ord($str[$i+1]) <= 254) $i++;
		} else {
			$begin = $i;
			for(; $i < $start + $len && $i < $strlen; $i ++) {
				if(ord($str[$i]) >= 161 && ord($str[$i]) <= 247 && ord($str[$i+1]) >= 161 && ord($str[$i+1]) <= 254) $i++;
			}
			return substr($str, $begin, $i - $begin);
		}
	}
}

function utf8_substr($str, $start, $len)
{
    for($i=0;$i<$len;$i++)
    {
        $temp_str=substr($str,0,1);
        if(ord($temp_str) > 127){
            $i++;
        if($i<$len)    {
            $new_str[]=substr($str,0,3);
            $str=substr($str,3);
            }
        }
    else {
        $new_str[]=substr($str,0,1);
        $str=substr($str,1);
        }
    }
    return join($new_str);
}

function ak_replace($find, $replace, $str, $caseless = 1, $count = -1) {//$caseless是否区分大小写，0为不区分，最好参数能兼容数组
	if(!is_array($find)) {
		$find = array($find);
	}
	if(!is_array($replace)) {
		$replace = array($replace);
	}
	if(count($find) != count($replace)) return false;
	if($caseless == 1) {
		foreach($find as $id => $f) {
			if(strpos($str, $f) === false) continue;
			$str = str_replace_count($f, $replace[$id], $str, $count);
		}
	} else {
		foreach($find as $id => $f) {
			$f = str_replace('/', '\/', $f);
			if(!preg_match("/{$f}/i", $str)) continue;
			$str = preg_replace("/{$f}/i", $replace[$id], $str, $count);
		}
	}
	return $str;
}

function ak_array_replace($finds, $replaces, $str) {
	$_str = $str;
	foreach($finds as $key => $value) {
		$r = '';
		if(isset($replaces[$key])) $r = $replaces[$key];
		$_str = ak_replace($value, $r, $_str);
	}
	return $_str;
}

function str_replace_count($search, $replace, $string, $count) {
	if($count < 0) {
		return str_replace($search, $replace, $string);
	} elseif($count == 0) {
		return $string;
	} else {
		return str_replace_count($search, $replace, str_replace_once($search, $replace, $string), $count - 1);
	}
}

function str_replace_once($search, $replace, $string) {
	$pos = strpos($string, $search);
	if($pos === false) return true;
	$return = '';
	$s1 = substr($string, 0, $pos);
	$s2 = substr($string, $pos + strlen($search));
	return $s1.$replace.$s2;
}

function in_string($string, $findme) {//string为字符串，findme可以是字符串也可以是字符串数组，如果找到返回1，没找到返回0
	if(!is_array($findme)) {
		$findme = array($findme);
	}
	foreach($findme as $find) {
		if($find == '') continue;
		if(strpos($string, $find) === false) {
			continue;
		} else {
			return 1;
		}
	}
	return 0;
}

function getfield($start, $end, $content, $conv = 1) {
	global $setting_language;
	if(empty($start) || empty($end) || empty($content)) return false;
	if(!empty($conv)) {
		$start = convtag($start);
		$end = convtag($end);
	}
	$start_position = strpos($content, $start);
	if($start_position === false) return false;
	$start_position += strlen($start);
	$end_position = strpos($content, $end, $start_position);
	if($end_position === false) return false;
	$content = substr($content, $start_position, $end_position - $start_position);
	return $content;
}

function tidybody($text) {
	$text = str_replace("\t", '', $text);
	$text = str_replace("\r", '', $text);
	$text = str_replace("\n", '', $text);
	$text = preg_replace("/<script.*?<\/\s*?script>/is", '', $text);
	$text = preg_replace("/<style.*?<\/\s*?style>/is", '', $text);
	$text = trim(strip_tags($text, '<img><center><div><p><br>'));
	$text = preg_replace("/<\/p>/i", "\n\n", $text);
	$text = preg_replace("/<br.*?>/i", "\n", $text);
	//$text = trim(strip_tags($text, '<img><center><div>'));
	$text = trim(strip_tags($text, '<img><center>'));
	$text = preg_replace("/\n\n\n*/", "\n\n", $text);
	return $text;
}

function convtag($tag) {
	$array_replace = array(
		'[r]',
		'[n]',
		'[rn]',
		'[t]'
	);
	$array_to = array(
		"\r",
		"\n",
		"\r\n",
		"\t"
	);
	$tag = ak_replace($array_replace, $array_to, $tag);
	return $tag;
}

function frequence($text, $keyword) {//从一个字符串中检查出现某个关键字的次数
	if(strlen($text) < strlen($keyword) || $keyword == '') return 0;
	$position = 0;
	$time = 0;
	while($position < strlen($text)) {
		$position = strpos($text, $keyword, $position);
		if($position !== false) {
			$time ++;
			$position ++;
		} else {
			break;
		}
	}
	return $time;
}

function ak_htmlspecialchars($array) {
	if(!is_array($array)) {
		$isvariable = 1;
		$array = array($array);
	}
	foreach($array as $key => $value) {
		if(is_array($value)) {
			$array[$key] = ak_htmlspecialchars($value);
		} else {
			$array[$key] = htmlspecialchars($value);
		}
	}
	if(!isset($isvariable)) {
		return $array;
	} else {
		return $array[0];
	}
}

function readlinefromfile($filename) { //这个函数不会返回空字符串，如果是空就取第一个不为空的行，如果整个文件结束了，返回“empty”
	if(!is_readable($filename)) return 'noexist';
	$tmp = AK_ROOT.'./cache/'.md5(microtime());
	$offset = 0;
	$temp_fp = fopen($tmp, 'w');
	if($fp = @fopen($filename, 'r+')) {
		flock($fp, LOCK_EX);
		$line = '';
		$result = '';
		while(!feof($fp)) {
			$line = fgets($fp);
			if(empty($fetchedflag)) {
				$offset += strlen($line);
				if(trim($line) != '') {
					$result = $line;
					$fetchedflag = 1;
				}
			} else {
				fwrite($temp_fp, $line);
			}
		}
		fclose($temp_fp);
		flock($fp, LOCK_UN);
		fclose($fp);
		copy($tmp, $filename);
		unlink($tmp);
		if($result == '') return 'empty';
		return trim($result);
	} else {
		fclose($temp_fp);
		@unlink($tmp);
		return 'noexist';
	}
}

function ak_md5($string, $short = 0) {
	if($short == 0) {
		return md5($string);
	} else {
		return substr(md5($string), 8, 16);
	}
}

function getkeywords($title, $content, $num = 6, $returnstring = 1) {
	require_once AK_ROOT.'/include/keywords.func.php';
	return core_getkeywords($title, $content, $num, $returnstring);
}

function getpinyin($string) {
	global $setting_language, $charset;
	if($setting_language == 'chinese' && $charset == 'gbk') {
		require_once(AK_ROOT.'/include/pinyin.func.php');
		$string = core_pinyin($string);
		return $string;
	} else {
		return $string;
	}
}

function string2filename($str) {
	$strlen = strlen($str);
	$return = '';
	for($i = 0; $i < $strlen; $i ++) {
		$o = ord($str[$i]);
		if(($o >= 48 && $o <= 57) || ($o >= 65 && $o <= 90) || ($o >= 97 && $o <= 122)) {
			$return .= $str[$i];
		} else {
			$return .= '-';
		}
	}
	return $return;
}

function if_openurl_enabled() {
	if(ini_get('allow_url_fopen') == "1") {
		return true;
	} else {
		return false;
	}
}

function readpathtoarray($path, $shortfilename = 0) {
	$fp = opendir($path);
	$return = array();
	while (false !== ($file = readdir($fp))) {
		if($file == '.' || $file == '..') continue;
		if($shortfilename == 1) {
			$return[] = $file;
		} else {
			$return[] = $path.'/'.$file;
		}
	}
	closedir($fp);
	return $return;
}

function getwee($dateline = 0, $type = 'day') {
	global $thetime;
	empty($dateline) && $dateline = $thetime;
	list($year, $month, $day) = explode('-', date('Y-m-d', $dateline));
	if($type == 'day') {
		return mktime(0, 0, 0, $month, $day, $year);
	} elseif($type == 'month') {
		return mktime(0, 0, 0, $month, 1, $year);
	} elseif($type == 'year') {
		return mktime(0, 0, 0, 1, 1, $year);
	}
}

function getbarwidth($barwidth, $value, $max) {
	if($max == 0) return 0;
	return ceil($barwidth * $value / $max);
}

function addtime($length, $type, $start = 0) {
	global $thetime;
	$start == 0 && $start = $thetime;
	list($y, $m, $d) = explode('-', date('Y-m-d', $start));
	if($type == 'day') {
		$d += $length;
	} elseif($type == 'month') {
		$m += $length;
	} elseif($type == 'year') {
		$y += $length;
	}
	return mktime(0, 0, 0, $m, $d, $y);
}

function convcharset($from, $to, $str) {
	if(function_exists('iconv')) {
		if(is_array($str)) {
			foreach($str as $key => $value) {
				$str[$key] = convcharset($from, $to, $value);
			}
			return $str;
		} elseif(is_string($str)) {
			return iconv($from, $to, $str);
		}
	} else {
		return $str;
	}
}

function querytoarray($url) {
	$return = array();
	$parsed = parse_url($url);
	if(!isset($parsed['query'])) return array();
	$array_query = explode('&', $parsed['query']);
	foreach($array_query as $query) {
		$keyvalue = explode('=', $query);
		if(isset($keyvalue[1])) {
			$return[$keyvalue[0]] = $keyvalue[1];
		}
	}
	return $return;
}

function getarrayvalue($array, $keys) {
	if(!is_array($array)) return '';
	if(!is_array($keys)) $keys = array($keys);
	foreach($keys as $key) {
		if(isset($array[$key])) return $array[$key];
	}
	return '';
}

function isen($text) {
	$en = 0;
	for($i = 0; $i < strlen($text); $i ++) {
		if(ord($text[$i]) < 161) {
			$en ++;
		} else {
			$en --;
		}
	}
	if($en > 0) {
		return 1;
	} else {
		return 0;
	}
}

function get_rule_field($field, $position = 0) {//由<a>\n<b>返回a(position = 0)或b
	$array_field = explode("\n", $field);
	if(count($array_field) != 2) return '';
	if($position !=0 && $position != 1) return '';
	return substr($array_field[$position], 1, -1);
}

function multi_replace($text, $rule) {//批量替换，text为待替换的字符串，rule为这样的字符串：a->b\nc->d代表的意思是a替换成b，c替换成d
//和ak_repalce冲突
	if($text == '') return '';
	$array_replace = array();
	$array_to = array();
	$rule = str_replace("\r", '', $rule);
	$array_rule_replace = explode("\n", $rule);
	foreach($array_rule_replace as $a_replace) {
		$array_replace_to = explode('->', $a_replace);
		if(count($array_replace_to) == 2) {
			$array_replace[] = $array_replace_to[0];
			$array_to[] = $array_replace_to[1];
		}
	}
	return ak_replace($array_replace, $array_to, $text);
}

function readfromurl($url) {
	global $charset;
	$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; Maxthon)';//debug
	if(function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		$content = curl_exec($ch);
		curl_close($ch);
	} elseif(ini_get('allow_url_fopen') == '1') {
		@ini_set('user_agent', $agent);
		$content = readfromfile($url);
	} else {
		return 'ERROR:spider disabled!';
	}
	if(function_exists('iconv')) {
		if(strpos($content, 'charset=utf') !== false) {
			$sourcecharset = 'UTF-8';
		}
		if(isset($sourcecharset) && $sourcecharset != $charset) {
			$content = iconv($sourcecharset, $charset, $content);
		}
	}
	return $content;
}

function akmicrotime() {
	$mtime = microtime();
	$s = substr($mtime, 11, 10);
	$ms = substr($mtime, 1, 9);
	return number_format($s.$ms, 4, '.', '');
}


function monitor($sign = '', $id = 0) {
	global $exe_times, $exe_signs;
	if(is_array($sign)) $sign = reset($sign);
	if(!isset($exe_times)) $exe_times[$id] = array();
	if(!isset($exe_signs)) $exe_signs[$id] = array();
	$exe_times[$id][] = akmicrotime();
	$exe_signs[$id][] = $sign;
}

function monitor_log($logfile = '', $id = 0) {
	global $exe_times, $exe_signs;
	if(!isset($exe_times) || !isset($exe_signs)) return false;
	$exe_times[$id][] = akmicrotime();
	$exe_signs[$id][] = 'END';
	$total = msformat(end($exe_times[$id]) - $exe_times[$id][0]);
	for($i = count($exe_times[$id]) - 1; $i >= 1; $i --) {
		$exe_times[$id][$i] = msformat($exe_times[$id][$i] - $exe_times[$id][$i - 1]);
	}
	$exe_times[$id][0] = date('Y-m-d H:i:s', $exe_times[$id][0]);
	$exes = array();
	for($i = 0; $i < count($exe_times[$id]); $i ++) {
		$exes[] = "{$exe_times[$id][$i]}({$exe_signs[$id][$i]})";
	}
	$exes[] = "$total(TOTAL)";
	if($logfile == '') {
		debug($exes);
	} else {
		error_log(implode("\t", $exes)."\n", 3, AK_ROOT.'./logs/'.$logfile);
	}
	unset($GLOBALS['exe_times'][$id]);
	unset($GLOBALS['exe_signs'][$id]);
}

function msformat($number) {
	return number_format($number, 3, '.', '');
}

function ak_filetime($filename) {
	global $timedifference;
	return filemtime($filename) + $timedifference * 3600;
}

function clearhtml($html, $force = 0) {
	$html = str_replace("\r", '', $html);
	$html = str_replace("\n", '', $html);
	$html = preg_replace("/\s+/", ' ', $html);
	$html = str_replace("> <", '><', $html);
	return $html;
}

function ak_rmdir($dir) {
	if(!file_exists($dir)) return;
	if($handle = opendir("$dir")) {
		while(false !== ($item = readdir($handle))) {
		if($item != "." && $item != "..") {
			if(is_dir("$dir/$item")) {
				ak_rmdir("$dir/$item");
			} else {
				unlink("$dir/$item");
			}
		}
	}
	closedir($handle);
	rmdir($dir);
	}
}

function decode_htmlspecialchars($str) {
	$str = str_replace('&nbsp;', ' ', $str);
	return $str;
}

function killrepeatspace($str) {
	$str = preg_replace('/(\s+)/i', ' ', $str);
	return $str;
}

function clearspider($str) {//采集到的内容清理，先干掉所有的标签，再清理多余空白，不适用于正文
	$str = decode_htmlspecialchars($str);
	return killrepeatspace(strip_tags($str));
}

function convspecialchars($str) {//转换采集内容中的特殊字符
	$str = str_replace('&#39;', '\'', $str);
	$str = str_replace('&#8216;', '\'', $str);
	$str = str_replace('&#8217;', '\'', $str);
	$str = str_replace('&#8221;', '"', $str);
	return $str;
}

function removespecialchars($str) {//干掉特殊字符，用空格替换
	$str = preg_replace('/&[a-z0-9#]{1,10};/i', ' ', $str);
	return $str;
}

function killup($path) {//干掉URL中的../
	$p = strpos($path, '/..');
	if($p === false) return $path;
	$p2 = ak_strrpos(substr($path, 0, $p), '/');
	$newpath = substr($path, 0, $p2).substr($path, $p + 3);
	return killup($newpath);
}

function clearfilename($filename) {//清理URL中的?以后的东西，和#以后的东西，返回文件本身
	$p1 = strpos($filename, '?');
	$p2 = strpos($filename, '#');
	if($p1 !== false && $p2 !== false) {
		return substr($filename, 0, min($p1, $p2));
	} else {
		if($p1 !== false) {
			return substr($filename, 0, $p1);
		}
		if($p2 !== false) {
			return substr($filename, 0, $p2);
		}
		return $filename;
	}
}

function getdomain($url) {//从url中截取域名
	$p1 = strpos($url, '://') + 3;
	$p2 = strpos($url, '/', $p1);
	return substr($url, $p1, $p2 - $p1);
}

function geturlpath($url) {
	if(substr($url, -1) == '/') return $url;
	$pos = ak_strrpos($url, '/'); return substr($url, 0, $pos + 1);
}

function myxor($string, $key = '') {
	if('' == $string) return '';
	if('' == $key) $key = 'akcms';
	$len1 = strlen($string);
	$len2 = strlen($key);
	if($len1 > $len2) $key = str_repeat($key, ceil($len1 / $len2));
	return $string ^ $key;
}

function runquery($sql) {
	global $db;
	$ret = array();
	$num = 0;
	$sql = str_replace("\r\n", "\n", $sql);
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		$ret[$num] = '';
		foreach($queries as $query) {
			if(preg_match('/^--/i', $query) || preg_match('/^#/i', $query)) {
				continue;
			}
			$ret[$num] .= $query;
		}
		$num++;
	}
	unset($sql);
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			$db->query($query);
		}
	}
}

function ak_strrpos($string, $findme) {
	if(PHP_VERSION < '5.0') {
		$string = strrev($string);
		$findme = strrev($findme);
		$_pos1 = strpos($string, $findme);
		return strlen($string) - $_pos1 - strlen($findme);
	} else {
		return strrpos($string, $findme);
	}
}

function sortbylength($array) {
	$_a = array();
	foreach($array as $key => $string) {
		$_a[$key] = strlen($string);
	}
	asort($_a);
	$return = array();
	foreach($_a as $key => $value) {
		$return[] = $array[$key];
	}
	return $return;
}

function calfilenamefromurl($url) {
	$_pos = ak_strrpos($url, '/');
	return substr($url, $_pos + 1);
}

function ak_unserialize($str) {
	$return = @unserialize($str);
	if($return === false) {
		return '';
	} else {
		return $return;
	}
}

function table2mysql($key, $data) {
	$mysqlversion = mysql_get_server_info();
	$sql = '';
	$sql .= "DROP TABLE IF EXISTS `{$key}`;\n";
	$sql .= "CREATE TABLE `$key`(\n";
	foreach($data['fields'] as $k => $v) {
		if($v['type'] != 'text') {
			$sql .= "`$k` {$v['type']}({$v['length']})";
			if(!empty($v['unsigned'])) $sql .= " unsigned";
			if(empty($v['null'])) $sql .= " NOT NULL";
			if(isset($v['default'])) $sql .= " default '{$v['default']}'";
			if(!empty($v['auto_increment'])) $sql .= ' auto_increment';
		} else {
			$sql .= "`$k` text";
		}
		$sql .= ",\n";
	}
	foreach($data['indexs'] as $k => $v) {
		if($v['type'] == 'primary') {
			$sql .= "PRIMARY KEY(`{$k}`),\n";
		} else {
			foreach($v['value'] as $_k => $_v) {
				$v['value'][$_k] = "`{$_v}`";
			}
			if($v['type'] == 'unique') $sql .= "UNIQUE ";
			$sql .= "KEY `$k`(".implode(',', $v['value'])."),\n";
		}
	}
	if(!empty($data['indexs'])) $sql = substr($sql, 0, -2)."\n";
	$sql .= ")";
	if($mysqlversion < 4) {
		if(isset($data['engine'])) {
			if($data['engine'] == 'memory')	$sql .= " TYPE=HEAP";
		} else {
			$sql .= " TYPE=MYISAM";
		}
	} else {
		if(isset($data['engine'])) {
			if($data['engine'] == 'memory')	$sql .= " ENGINE=MEMORY";
		} else {
			$sql .= " ENGINE=MYISAM";
		}
	}
	if(isset($data['charset']) && mysql_get_server_info() > '4.1') {
		if($data['charset'] == 'utf8') {
			$sql .= ' DEFAULT CHARACTER SET utf8 COLLATE utf8_bin';
		} elseif($data['charset'] == 'gbk') {
			$sql .= ' DEFAULT CHARACTER SET gbk COLLATE gbk_chinese_ci';
		} elseif($data['charset'] == 'english') {
			$sql .= ' DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci';
		}
	}
	return $sql;
}

function table2sqlite($key, $data) {
	$sql = '';
	$indexs_sql = '';
	$sql .= "CREATE TABLE {$key}(\n";
	foreach($data['fields'] as $k => $v) {
		if($v['type'] == 'int' || $v['type'] == 'mediumint' || $v['type'] == 'smallint') $v['type'] = 'INTEGER';
		if($v['type'] == 'text') {
			$sql .= "{$k} text,\n";
			continue;
		}
		if($v['type'] == 'INTEGER') {
			$sql .= "{$k} {$v['type']}";
		} else {
			$sql .= "{$k} {$v['type']}({$v['length']})";
		}
		if(isset($v['default'])) $sql .= " default '{$v['default']}'";
		$sql .= ",\n";
	}
	foreach($data['indexs'] as $k => $v) {
		if($v['type'] == 'primary') {
			$sql .= "PRIMARY KEY({$k})\n";
		} else {
			$indexs_sql .= "CREATE";
			if($v['type'] == 'unique') $indexs_sql .= " UNIQUE";
			$indexs_sql .= " INDEX {$k} on {$key}(".implode(',', $v['value']).");\n";
		}
	}
	$sql .= ");\n".$indexs_sql;
	return $sql;
}
?>
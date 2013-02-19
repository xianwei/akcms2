<?php
function arrayeval($array, $level = 0) {
	$space = '';
	for($i = 0; $i <= $level; $i++) {
		$space .= "\t";
	}
	$evaluate = "Array\n$space(\n";
	$comma = $space;
	foreach($array as $key => $val) {
		$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
		$val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
		if(is_array($val)) {
			$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
		} else {
			$evaluate .= "$comma$key => $val";
		}
		$comma = ",\n$space";
	}
	$evaluate .= "\n$space)";
	return $evaluate;
}

function coreupdatecache($cachename = '', $designate = array()) {
	global $db, $tablepre, $settings, $codekey;
	if(empty($cachename) || $cachename == 'categories') {
		$a_category = array();
		$sql = "SELECT * FROM {$tablepre}_categories ORDER BY `orderby` DESC,id";
		$query = $db->query($sql);
		while($var = $db->fetch_array($query)) {
			$a_category[$var['id']] = $var;
		}
		writetocache('categories', '$categories = '.arrayeval($a_category));
		unset($a_category);
	}
	if(empty($cachename) || $cachename == 'sections') {
		$a_section = array();
		$sql = "SELECT * FROM {$tablepre}_sections";
		$query = $db->query($sql);
		while($var = $db->fetch_array($query)) {
			$a_section[$var['id']] = $var;
		}
		writetocache('sections', '$sections = '.arrayeval($a_section));
		unset($a_section);
	}
	if(empty($cachename) || $cachename == 'templates') {
		global $template_path;
		$a_template = array();
		$dir = AK_ROOT.'templates/'.$template_path.'/';
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if($filename != '.' && $filename != '..' && substr($filename, 0, 1) == ',') {
				$a_template[] = substr($filename, 1);
			}
		}
		writetocache('templates', '$templates = '.arrayeval($a_template));
		unset($a_template);
	}
	if(empty($cachename) || $cachename == 'globalvariables') {
		$a_variable = array();
		$sql = "SELECT * FROM {$tablepre}_variables ORDER BY variable";
		$query = $db->query($sql);
		while($var = $db->fetch_array($query)) {
			$a_variable[$var['variable']] = $var['value'];
		}
		writetocache('globalvariables', '$globalvariables = '.arrayeval($a_variable));
		unset($a_variable);
	}
	if(empty($cachename) || $cachename == 'settings') {
		$a_setting = array();
		$sql = "SELECT * FROM {$tablepre}_settings";
		$query = $db->query($sql);
		while($var = $db->fetch_array($query)) {
			$a_setting[$var['variable']] = $var['value'];
		}
		writetocache('settings', '$settings = '.arrayeval($a_setting));
		unset($a_setting);
	}
	if((empty($cachename) || $cachename == 'infos')) {
		$items = $db->get_by('COUNT(*)', 'items', 'category>0');
		$pvs = $db->get_by('SUM(pageview)', 'items');
		$editors = $db->get_field("SELECT COUNT(*) FROM {$tablepre}_admins WHERE freeze=0");
		$attachmentsizes = $db->get_field("SELECT SUM(filesize) FROM {$tablepre}_attachments");
		$attachments = $db->get_field("SELECT COUNT(*) FROM {$tablepre}_attachments");
		$array_infos = array(
			'items' => $items,
			'pvs' => $pvs,
			'editors' => $editors,
			'attachmentsizes' => $attachmentsizes,
			'attachments' => $attachments
		);
		writetocache('infos', '$infos = '.arrayeval($array_infos));
	}
	if(empty($cachename) || $cachename == 'crons') {
		$query = $db->query("SELECT * FROM {$tablepre}_crons");
		$a_crons = array();
		while($var = $db->fetch_array($query, 1)) {
			$a_crons[$var['id']] = $var;
			$a_crons[$var['id']]['nexttime'] = cronnexttime($var);
		}
		writetocache('crons', '$crons = '.arrayeval($a_crons));
		unset($a_crons);
	}
	if(empty($cachename) || $cachename == 'spiders') {
		$query = $db->query("SELECT * FROM {$tablepre}_spiders");
		$spiders = array();
		while($spider = $db->fetch_array($query)) {
			$_v = unserialize($spider['data']);
			$spider = array_merge($spider, $_v);
			unset($spider['data']);
			$spiders[$spider['id']] = $spider;
		}
		writetocache('spiders', '$spiders = '.arrayeval($spiders));
		unset($spiders);
	}
	if(empty($cachename) || $cachename == 'spiderrules') {
		$query = $db->list_by('*', 'spiderrules');
		$spiderrules = array();
		while($spiderrule = $db->fetch_array($query)) {
			$ruleid = $spiderrule['id'];
			$spiderrule = ak_unserialize($spiderrule['data']);
			for($i = 1; $i <= 20; $i ++) {
				$spiderrule['field'.$i.'_start'] = get_rule_field($spiderrule['field'.$i], 0);
				$spiderrule['field'.$i.'_end'] = get_rule_field($spiderrule['field'.$i], 1);
				unset($spiderrule['field'.$i]);
			}
			$spiderrule['id'] = $ruleid;
			$spiderrules[$ruleid] = $spiderrule;
		}
		writetocache('spiderrules', '$spiderrules = '.arrayeval($spiderrules));
		unset($spiderrules);
	}
	if(empty($cachename) || $cachename == 'plugins') {
		$paths = readpathtoarray(AK_ROOT.'/plugins');
		$plugins = array();
		foreach($paths as $path) {
			if(is_dir($path)) continue;
			if(!is_readable($path)) continue;
			if(fileext($path) == 'php') $plugins[] = calfilenamefromurl($path);
		}
		writetocache('plugins', '$plugins = '.arrayeval($plugins));
		unset($spiderrules);
	}
	if(empty($cachename) || $cachename == 'keywords') {
		$query = $db->list_by('*', 'items', 'category=-1', 'orderby desc');
		$_keywords = '';
		while($keyword = $db->fetch_array($query)) {
			$_k = $keyword['title']."\t".$keyword['aimurl']."\t".$keyword['digest']."\n";
			if(frequence($_k, "\t") > 2) continue;
			$_keywords .= $_k;
		}
		writetofile($_keywords, AK_ROOT.'cache/'.$codekey.'_cache_keywords.txt');
	}
}

function writetocache($script, $cachedata = '') {
	$dir = AK_ROOT.'./cache/';
	if(!is_dir($dir)) mkdir($dir, 0777);
	if($fp = @fopen("{$dir}cache_{$script}.php", 'w')) {
		$text = "<?php\n//AKCMS cache file, DO NOT modify me!\n".
			"//Created on ".date("M j, Y, G:i")."\n\n".'$'."{$script}loaded = 1;\n\n$cachedata?>";
		fwrite($fp, $text);
		fclose($fp);
	} else {
		exit("{$dir}cache_{$script}.php is unwriteable.");
	}
}

function getcachevars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(is_array($val)) {
			$evaluate .= "\$$key = ".arrayeval($val).";\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}
?>
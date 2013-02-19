<?php
require_once(AK_ROOT."include/section.func.php");
require_once(AK_ROOT."include/category.func.php");

function checkcreator() {
	global $lan;
	if(iscreator() != 1) adminmsg($lan['forcreatoronly'], '', 0, 1);
}

function db() {
	global $__dbtype;
	if($__dbtype == 'mysql') {
		return mysqldb();
	} elseif($__dbtype == 'sqlite') {
		return sqlitedb();
	}
}

function mysqldb() {
	global $dbhost, $dbuser, $dbpw, $dbname, $db_setname;
	static $mysqldb;
	if(isset($mysqldb)) return $mysqldb;
	require_once AK_ROOT.'include/db_mysql.class.php';
	$mysqldb = new dbstuff;
	$mysqldb->connect($dbhost, $dbuser, $dbpw, $dbname, $db_setname);
	return $mysqldb;
}

function sqlitedb() {
	global $dbname;
	static $sqlitedb;
	if(isset($sqlitedb)) return $sqlitedb;
	require_once AK_ROOT.'./include/db_sqlite.class.php';
	$sqlitedb = new dbstuff2;
	$sqlitedb->open(AK_ROOT.$dbname);
	return $sqlitedb;
}

function closedb() {
	global $db;
	if(isset($db)) {
		$db->close();
	}
}

function go($url, $crumb = 1) {
	if($crumb) {
		header("location:{$url}&crumb=".random(6));
	} else {
		header("location:{$url}");
	}
	aexit();
}

function captcha($sid) {
	global $db, $tablepre, $thetime;
	$expire = 60 * 5;
	$captcha = random(4, 1);
	$sid = ak_addslashes($sid);
	$sql = "REPLACE INTO {$tablepre}_captchas(sid,captcha,dateline)VALUES('$sid','$captcha','$thetime')";
	$db->query($sql);
	$sql = "DELETE FROM {$tablepre}_captchas WHERE dateline < ($thetime - $expire)";
	$db->query($sql);
	require_once(AK_ROOT.'./include/image.func.php');
	corecaptcha($captcha);
}

function adminmsg($message, $url_forward = '', $timeout = 3, $flag = 0) {//$flag表明：信息还是警告0信息1警告
	global $smarty, $lan, $systemurl;
	if($flag == 0) {
		$flag = 'info';
	} else {
		$flag = 'warning';
	}
	$smarty->assign('akurl', $systemurl);
	$smarty->assign('lan', $lan);
	$smarty->assign('flag', $flag);
	$smarty->assign('message', $message);
	$smarty->assign('url_forward', $url_forward);
	$smarty->assign('timeout', $timeout);
	$smarty->assign('timeout_micro', $timeout * 1000);
	$smarty->display('message.htm');
	runinfo();
	aexit();
}

function get_select($type, $root = 0) {
	if($type == 'category') {
		return rendercategoryselect();
	} elseif($type == 'section') {
		includecache('sections');
		global $sections;
		$selectsections = '';
		foreach($sections as $section) {
			$selectsections .= "<option value=\"$section[id]\">".htmlspecialchars($section['section'])."</option>\n";
		}
		return $selectsections;
	} elseif($type == 'spiderrules') {
		includecache('spiderrules');
		global $spiderrules;
		$selectspiderrules = '';
		foreach($spiderrules as $spiderrule) {
			$selectspiderrules .= "<option value=\"{$spiderrule['id']}\">".$spiderrule['spiderrulename']."</option>\n";
		}
		return $selectspiderrules;
	}
}

function get_select_templates() {
	includecache('templates');
	global $templates;
	$selecttemplates = '';
	foreach($templates as $template) {
		$selecttemplates .= "<option value=\"$template\">".$template."</option>\n";
	}
	return $selecttemplates;
}

function batchdeleteitem($array_id) {
	global $db, $tablepre;
	$ids = implode(',', $array_id);
	$db->delete('texts', "itemid IN ({$ids})");
	$query = $db->list_by('*', 'attachments', "itemid IN ({$ids})");
	while($attach = $db->fetch_array($query)) {
		@unlink(FORE_ROOT.$attach['filename']);
	}
	$db->delete('attachments', "itemid IN ({$ids})");
	$db->delete('filenames', "id IN ({$ids})");
	$array_sections = array();
	$array_categories = array();
	$array_editors = array();
	$query = $db->list_by('*', 'items', "id IN ($ids)");
	while($item = $db->fetch_array($query)) {
		$array_sections[] = $item['section'];
		$array_categories[] = $item['category'];
		$array_editors[] = $item['editor'];
		@unlink(FORE_ROOT.htmlname($item['id'], $item['category'], $item['dateline'], $item['filename']));
		if(!empty($item['picture'])) @unlink(FORE_ROOT.$item['picture']);
	}
	$db->delete('items', "id IN ({$ids})");
	refreshitemnum($array_categories, 'category');
	refreshitemnum($array_sections, 'section');
	refreshitemnum($array_editors, 'editor');
}

function foresearch() {//此方法仅供前端调用
	global $template;
	if(!isset($template)) $template = 'search.htm';
	$variables = get_search_data($template);
	$html = render_template($variables, $template);
	echo $html;
}

function foredisplay($id, $type = 'item', $template = '') {//此方法仅供前端调用
	//模板前不需要加,
	global $setting_forbidclearspace;
	if(strpos($template, ',') !== false) {
		$templates = explode(',', $template);
		$_key = array_rand($templates);
		$template = $templates[$_key];
	}
	if($type == 'item') {
		$variables = get_item_data($id);
		if(empty($variables)) exit;
		$variables = addrequest($variables);
		$html = render_template($variables, $template);
	} elseif($type == 'category') {
		$variables = get_category_data($id, $template);
		if(empty($variables)) exit;
		$variables = addrequest($variables);
		$html = render_template($variables, $template);
	} elseif($type == 'section') {
		$variables = get_section_data($id);
		if(empty($variables)) exit;
		$variables = addrequest($variables);
		$html = render_template($variables, $template);
	} else {
		if('' == $template) exit;
		$variables = addrequest(array());
		$html = render_template($variables, $template);
	}
	if(empty($setting_forbidclearspace)) $html = clearhtml($html);
	echo $html;
}

function addrequest($pagevariable) {//将get、post传入的参数增加到变量中去
	foreach($_POST as $key => $value) {
		$pagevariable['post_'.$key] = htmlspecialchars($value);
	}
	foreach($_GET as $key => $value) {
		$pagevariable['get_'.$key] = htmlspecialchars($value);
	}
	return $pagevariable;
}

function akinclude($params) {
	global $template_path;
	if(!isset($params['pagevariables'])) {
		$pagevariables = array();
	} else {
		$pagevariables = $params['pagevariables'];
	}
	$pagevariables['subtemplate'] = 1;//告诉渲染模板方法在渲染的时候不要给模板名加上,
	if(empty($params['expire'])) {
		echo render_template($pagevariables, $params['file']);
	} else {
		$params['type'] = 'template';
		$data = getcachedata($params);
		if($data == '') {
			$data = render_template($pagevariables, $params['file']);
			setcachedata($params, $data);
		}
		echo $data;
	}
}

function akincludeurl($params) {
	global $host;
	if(!isset($params['url'])) return;
	if(substr($params['url'], 0, 1) == '/') $params['url'] = 'http://'.$host.$params['url'];
	if(strpos($params['url'], 'http://') === false) return;
	if(!isset($params['expire'])) echo readfromurl($params['url']);
	if(isset($params['expire'])) {
		$params['type'] = 'url';
		$data = getcachedata($params);
		if($data == '') {
			$data = readfromurl($params['url']);
			setcachedata($params, $data);
		}
		echo $data;
	}
}

function includetemplateplugins() {
	global $html_smarty, $plugins;
	includecache('plugins');
	foreach($plugins as $plugin) {
		$pluginkey = str_replace('.template.php', '', $plugin);
		require_once(AK_ROOT.'plugins/'.$plugin);
		$html_smarty->register_function($pluginkey, $pluginkey);
	}
}

function render_template($pagevariables, $template = '', $createhtml = 0) {
	if($template == '') {
		if(isset($pagevariables['template'])) {
			$template = $pagevariables['template'];
		} else {
			return false;
		}
	}
	global $template_path, $smarty, $lan, $thetime, $system_root, $lr, $setting_keywordslink, $header_charset, $setting_homepage, $categories, $globalvariables, $sections, $setting_attachtemplate, $setting_storemethod, $itemexts, $html_smarty, $homepage, $setting_forbidclearspace, $setting_defaultfilename, $sysname, $sysedition;
	$html_smarty = new Smarty;
	includecache('categories');
	includecache('sections');
	includecache('globalvariables');
	require_once AK_ROOT.'./include/getdata.func.php';
	$html_smarty->template_dir = AK_ROOT."./templates/$template_path";
	$html_smarty->compile_dir = AK_ROOT."./templates_c";
	$html_smarty->config_dir = AK_ROOT."./configs/";
	$html_smarty->cache_dir = AK_ROOT."./cache/";
	$html_smarty->left_delimiter = "<{";
	$html_smarty->right_delimiter = "}>";
	$html_smarty->assign('charset', $header_charset);
	$html_smarty->assign('pagevariables', $pagevariables);
	$html_smarty->assign('thetime', $thetime);
	$html_smarty->register_function("akinclude", "akinclude");
	$html_smarty->register_function("akincludeurl", "akincludeurl");
	$html_smarty->register_function("getitems", "getitems");
	$html_smarty->register_function("getcategories", "getcategories");
	$html_smarty->register_function("getthreads", "getthreads");
	$html_smarty->register_function("getbbsinfo", "getbbsinfo");
	$html_smarty->register_function("getxspaceblogers", "getxspaceblogers");
	$html_smarty->register_function("getxspaceblogs", "getxspaceblogs");
	$html_smarty->register_function("getblogs", "getblogs");
	$html_smarty->register_function("getmessages", "getmessages");
	$html_smarty->register_function("getbbsusers", "getbbsusers");
	$html_smarty->register_function("getcomments", "getcomments");
	$html_smarty->register_function("getlists", "getlists");
	$html_smarty->register_function("monitor", "monitor");
	$html_smarty->register_function("getindexs", "getindexs");
	includetemplateplugins();
	foreach($globalvariables as $key => $v) {
		$html_smarty->assign('v_'.$key, $v);
	}
	foreach($pagevariables as $key => $value) {
		$html_smarty->assign($key, $value);
	}
	if(empty($pagevariables['subtemplate'])) $template = ','.$template;
	$templatefile = AK_ROOT."templates/$template_path/".$template;
	if(!file_exists($templatefile)) exit($template.' lose.');
	$text = $html_smarty->text($template);
	if(strpos($text, '[powered]') === false) {
		$text = preg_replace('/<\/body>/i', "[powered]{$lr}</body>", $text);
	}
	if(strpos($text, '[inc]') === false) {
		$text = preg_replace('/<\/body>/i', "[inc]{$lr}</body>", $text);
	}
	if(!empty($pagevariables['_pageid'])) {
		$id = $pagevariables['_pageid'];
		$type = $pagevariables['_pagetype'];
		$inc = getinc($id, $type);
	} else {
		$inc = getinc();
	}
	$text = ak_replace('[powered]', "<center style=\"margin: 0px;font-size:10px;font-family:verdana;\"><a href=\"http://www.akcms.com\" target=\"_blank\">Powered by {$sysname}</a></center>", $text);
	$text = ak_replace('[inc]', $inc, $text);
	$text = ak_replace('[home]', $homepage, $text);
	$text = ak_replace('[n]', "\n", $text);
	if(empty($setting_forbidclearspace)) $text = clearhtml($text);
	if(!empty($pagevariables['html']) && !empty($createhtml)) {
		$filename = $pagevariables['htmlfilename'];
		$_s = calfilenamefromurl($filename);
		if(strpos($_s, '.') === false) {
			$filename .= '/'.$setting_defaultfilename;
		} elseif(substr($_s, -1) == '/') {
			$filename .= $setting_defaultfilename;
		}
		writetofile($text, $filename);
	}
	return $text;
}

function get_item_data($id, $html = 0) {
	global $template_path, $smarty, $db, $tablepre, $lan, $thetime, $system_root, $lr, $setting_keywordslink, $header_charset, $setting_homepage, $setting_html, $categories, $sections, $setting_attachtemplate, $setting_storemethod, $itemexts, $html_smarty, $setting_richtext, $homepage;
	$variables['_pagetype'] = 'item';
	$variables['_pageid'] = $id;
	if(!$item = $db->get_by('*', 'items', "id='".ak_addslashes($id)."'")) return array();
	if($item['template'] == '') {
		$variables['template'] = getcategorytemplate($item['category']);
	} else {
		$variables['template'] = $item['template'];
	}
	
	$sql = "SELECT text FROM {$tablepre}_texts WHERE itemid='{$id}' AND page='0' LIMIT 1";
	$text = $db->get_field($sql);
	$texttitle = $item['title'];
	$textshorttitle = empty($item['shorttitle']) ? $texttitle : $item['shorttitle'];
	$title = htmltitle($texttitle, $item['titlecolor'], $item['titlestyle']);
	$shorttitle = htmltitle($textshorttitle, $item['titlecolor'], $item['titlestyle']);
	if(!empty($item['attach'])) {
		$query = $db->query("SELECT * FROM {$tablepre}_attachments WHERE itemid='{$id}' ORDER BY id");
		$attachs = array();
		$attach_img = '';
		while($attach = $db->fetch_array($query)) {
			$attachs[$attach['id']] = $attach;
			if(strpos($text, '[attach]'.$attach['id'].'[/attach]') === false) {
				if(ispicture($attach['filename'])) {
					$attach_img .= '[attach]'.$attach['id'].'[/attach]';
				} else {
					$text = $text.'[attach]'.$attach['id'].'[/attach]';
				}
			}
		}
		$text = $attach_img.$text;
		unset($attach_img);
		foreach($attachs as $i => $a) {
			if(ispicture($a['filename'])) {
				$attach_show = "<center><img src=\"[home]{$a['filename']}\"><br>{$a['description']}</center>";
			} else {
				$attach_template = !empty($setting_attachtemplate) ? $setting_attachtemplate : 'description:[description]<br>filename:<a href="[url]" target="_blank">[filename]</a>([size] K)<br><br>';
				$array_attach_from = array('[description]', '[filename]', '[size]', '[url]', '[ext]', '[home]');
				$array_attach_to = array($a['description'], $a['filename'], ceil($a['filesize'] / 1024), $homepage.$a['filename'], fileext($a['filename']), $homepage);
				$attach_show = ak_replace($array_attach_from, $array_attach_to, $attach_template);
			}
			$text = ak_replace('[attach]'.$i.'[/attach]', $attach_show, $text);
		}
	}
	$text = renderkeywords($text, $item['keywords']);
	$str_keywords = tidyitemlist($item['keywords'], ',', 0);
	includecache('categories');
	$category = !empty($item['category']) ? $categories[$item['category']] : array();
	includecache('sections');
	$section = !empty($item['section']) ? $sections[$item['section']] : array();
	if($item['category'] > 0) {
		if($categories[$item['category']]['categoryup'] > 0) {
			$upcategory = $categories[$item['category']]['categoryup'];
			$upcategoryname = $categories[$upcategory]['category'];
		} else {
			$upcategory = $item['category'];
			$upcategoryname = $category['category'];
		}
	}
	list($y, $m, $d, $h, $i, $s) = explode(',', date('Y,m,d,H,i,s', $item['dateline']));
	if($item['lastupdate'] == 0) $item['lastupdate'] = $item['dateline'];
	list($last_y, $last_m, $last_d, $last_h, $last_i, $last_s) = explode(',', date('Y,m,d,H,i,s', $item['lastupdate']));
	$url = htmlurl($id, $item['category'], $item['dateline'], $item['filename']);
	if(!empty($item['ext'])) {
		$itemextvalues = ak_unserialize($db->get_by('value', 'item_exts', "id='{$id}'"));
		if(is_array($itemextvalues)) {
			$variables = array_merge($variables, $itemextvalues);
		}
	}
	if($item['category'] == 0 || $categories[$item['category']]['html'] == 1 || ($categories[$item['category']]['html'] == 0 && $setting_html == 1)) {
		$variables['html'] = 1;
	}
	$variables['home'] = '[home]';
	$variables['id'] = $id;
	$variables['title'] = $title;
	$variables['shorttitle'] = $shorttitle;
	$variables['texttitle'] = $texttitle;
	$variables['textshorttitle'] = $textshorttitle;
	if(empty($setting_richtext)) {
		$variables['data'] = nl2br($text);
	} else {
		$variables['data'] = $text;
	}
	$variables['keyword'] = $str_keywords;//和下面的这个区别？？
	$variables['keywords'] = $item['keywords'];
	$variables['category'] = $item['category'];
	if(!empty($item['category']) && $item['category'] > 0) {
		$variables['categoryname'] = $category['category'];
		$variables['upcategory'] = $upcategory;
		$variables['upcategoryname'] = $upcategoryname;
		$variables['categorypath'] = $category['path'];
		$variables['categoryalias'] = $category['alias'];
		$variables['categorydescription'] = $category['description'];
		$variables['categorykeywords'] = $category['keywords'];
	}
	$variables['section'] = $item['section'];
	if(!empty($item['section'])) {
		$variables['sectionname'] = $section['section'];
		$variables['sectionalias'] = $section['alias'];
		$variables['sectiondescription'] = $section['description'];
		$variables['sectionkeywords'] = $section['keywords'];
	}
	$variables['editor'] = $item['editor'];
	$variables['author'] = $item['author'];
	$variables['source'] = $item['source'];
	if(!empty($item['picture']) && substr($item['picture'], 0, 7) !== 'http://') {
		$variables['picture'] = $homepage.$item['picture'];
	}
	$variables['pageview'] = $item['pageview'];
	$variables['url'] = $url;
	$variables['digest'] = $item['digest'];
	$variables['aimurl'] = $item['aimurl'];
	$variables['y'] = $y;
	$variables['m'] = $m;
	$variables['d'] = $d;
	$variables['h'] = $h;
	$variables['i'] = $i;
	$variables['s'] = $s;
	$variables['last_y'] = $last_y;
	$variables['last_m'] = $last_m;
	$variables['last_d'] = $last_d;
	$variables['last_h'] = $last_h;
	$variables['last_i'] = $last_i;
	$variables['last_s'] = $last_s;

	$variables['commentnum'] = $item['commentnum'];
	$variables['scorenum'] = $item['scorenum'];
	$variables['totalscore'] = $item['totalscore'];
	$variables['avgscore'] = $item['avgscore'];

	$variables['time'] = date('Y-m-d H:i:s', $thetime);
	$variables['htmlfilename'] = FORE_ROOT.htmlname($item['id'], $item['category'], $item['dateline'], $item['filename']);
	foreach($_GET as $key => $value) {
		if(!isset($variables[$key])) $variables[$key] = $value;
	}
	return $variables;
}

function get_search_data($template) {
	//可以接受的参数：template,通过这个检查出ipp
	global $db, $tablepre, $template_path, $system_root;
	$variables = array();
	if(empty($_GET['keywords']) || trim($_GET['keywords']) == '') exit('error');
	$template_content = readfromfile($system_root.'/templates/'.$template_path.'/,'.$template);
	preg_match("/<{getitems type=\"search\".*num=\"([0-9]+)\"/i", $template_content, $matches);
	$ipp = (empty($matches[1]) ? 10 : $matches[1]);
	$variables['ipp'] = $ipp;

	$sql_where = "1";
	if(isset($_GET['keywords'])) {
		$keywords = ak_addslashes($_GET['keywords']);
		if($keywords != '') {
			$sql_where .= " AND title LIKE '%$keywords%'";
		}
	} else {
		$keywords = '';
	}
	if(isset($_GET['category'])) {
		$category = $_GET['category'];
		$category = tidyitemlist($category);
		if($category != '') {
			if(strpos($category, ',') !== false) {
				$sql_where .= " AND category IN ($category)";
			} else {
				$sql_where .= " AND category=$category";
			}
		}
	} else {
		$category = 0;
	}
	$sql = "SELECT COUNT(*) FROM {$tablepre}_items WHERE {$sql_where}";
	isset($_GET['page']) ? $page = $_GET['page'] : $page = 1;
	$start = ($page - 1) * $ipp + 1;
	$itemnum = $db->get_field($sql);
	$variables['searchresultnum'] = $itemnum;
	$variables['keywords'] = $keywords;
	$variables['keywordsinhtml'] = htmlspecialchars($keywords);
	$variables['category'] = $category;
	$variables['page'] = $page;
	$variables['start'] = $start;
	$variables['_pageid'] = 0;
	$variables['_pagetype'] = 'search';
	return $variables;
}

function batchhtml($ids) {
	if(is_numeric($ids)) $ids = array($ids);
	foreach($ids as $id) {
		$variables = get_item_data($id);
		if(empty($variables)) continue;
		if(ifcategoryhtml($variables['category'])) $html = render_template($variables, '', 1);
	}
}

function getkeywordscache() {
	global $codekey;
	$_keywords = array();
	if($fp = @fopen(AK_ROOT.'cache/'.$codekey.'_cache_keywords.txt', 'r')) {
		while(!feof($fp)) {
			$_line = trim(fgets($fp));
			if(empty($_line)) continue;
			$_f = explode("\t", $_line);
			if(!isset($_f[1])) $_f[1] = '';
			if(!isset($_f[2])) $_f[2] = '';
			$_keywords[] = $_f;
		}
		fclose($fp);
	}
	return $_keywords;
}

function core_htmlname($id, $category = 0, $dateline = 0, $filename = '') {
//获得文件存放地址
	global $setting_htmlexpand, $categories, $setting_storemethod, $setting_usefilename;
	includecache('categories');
	if(empty($setting_usefilename)) return '';
	$dateline = empty($dateline) ? time() : $dateline;
	list($year, $month, $day) = explode(' ', date('Y m d', $dateline));
	if($category == 0) {
		$path = '.';
	} else {
		$path = empty($categories[$category]['path']) ? $category : $categories[$category]['path'];
		$up = $categories[$category]['categoryup'];
		if($up != 0) {
			$path = (empty($categories[$up]['path']) ? $up : $categories[$up]['path']).'/'.$path;
		}
	}
	if(empty($categories[$category]['storemethod'])) {
		$storemethod = $setting_storemethod;
	} else {
		$storemethod = $categories[$category]['storemethod'];
	}
	$path = str_replace('[categorypath]', $path, $storemethod);
	$path = str_replace('[y]', $year, $path);
	$path = str_replace('[m]', $month, $path);
	$path = str_replace('[d]', $day, $path);
	$path = str_replace('[id]', $id, $path);

	if(empty($filename)) {
		$filename = "{$id}{$setting_htmlexpand}";
	} else {
		if(preg_match('/^\//i', $filename)) {
			return substr($filename, 1);
		}
	}
	$path = str_replace('[f]', $filename, $path);
	return $path;
}

function htmlname($id, $category = 0, $dateline = 0, $filename = '') {
	$html = core_htmlname($id, $category, $dateline, $filename);
	return $html;
}

function htmlurl($id, $category = 0, $dateline = 0, $filename = '') {
//本方法获得文章的URL
	global $homepage;
	return $homepage.core_htmlname($id, $category, $dateline, $filename);
}

function multi($count, $perpage, $page, $url) {
	global $lan;
	$num = ceil($count / $perpage);//total page num
	$str_index = '';
	$page > 4 ? $start = $page - 4 : $start = 1;
	$num - $page > 4 ? $end = $page + 4 : $end = $num;
	for($i = $start; $i <= $end; $i ++) {
		$str_index = $str_index."<a href={$url}&page={$i}>&nbsp;<font color=\"white\">{$i}</font>&nbsp;</a>";
	}
	$str_index .= '<br>'.$lan['itemnum'].$count.'&nbsp;/&nbsp;'.$lan['numperpage'].$perpage.'&nbsp;/&nbsp;'.$lan['pagenum'].$num.'&nbsp;/&nbsp;'.$lan['prepage'].$page;
	return $str_index;
}

function inputshow($settings, $variable) {
	global $lan;
	$output = '';
	if(!is_array($variable)) {
		$variable = array($variable);
	}
	foreach($variable as $v) {
		$input = '';
		if(!isset($settings[$v])) {
			continue;
		}
		$setting = $settings[$v];
		if($setting['type'] == 'int') {
			$input = '<input type="text" name="'.$v.'" value="'.htmlspecialchars($setting['value']).'" size="15">';
		} elseif($setting['type'] == 'char') {
			$input = '<input type="text" name="'.$v.'" value="'.htmlspecialchars($setting['value']).'" size="50">';
		} elseif($setting['type'] == 'pass') {
			$input = '<input type="password" name="'.$v.'" value="'.$setting['value'].'" size="50">';
		} elseif($setting['type'] == 'bin') {
			if(!isset($lan[$v.'_text']) || !isset($setting['standby'])) {
				continue;
			}
			$array_text = explode(',', $lan[$v.'_text']);
			$array_value = explode(',', $setting['standby']);
			$i = 0;
			foreach($array_value as $value) {
				$i ++;
				if($setting['value'] == $value) {
					$input .= '<input type="radio" name="'.$v.'" id="'.$v.$i.'" value="'.$value.'" checked>&nbsp;<label for="'.$v.$i.'">'.current($array_text).'</label>&nbsp;';
				} else {
					$input .= '<input type="radio" name="'.$v.'" id="'.$v.$i.'" value="'.$value.'">&nbsp;<label for="'.$v.$i.'">'.current($array_text).'</label>&nbsp;';
				}
				next($array_text);
			}
		} elseif($setting['type'] == 'select') {
			if(!isset($lan[$v.'_text']) || !isset($setting['standby'])) {
				continue;
			}
			$array_text = explode(',', $lan[$v.'_text']);
			$array_value = explode(',', $setting['standby']);
			$input = "<select name=\"{$v}\">";
			foreach($array_value as $value) {
				if($setting['value'] == $value) {
					$input .= '<option value="'.$value.'" selected>'.current($array_text).'</option>';
				} else {
					$input .= '<option value="'.$value.'">'.current($array_text).'</option>';
				}
				next($array_text);
			}
			$input .= "</select>";
		}
		$title = isset($lan[$v.'_title']) ? '<b>'.$lan[$v.'_title'].'</b>' : $v;
		$description = isset($lan[$v.'_description']) ? $lan[$v.'_description'] : '';
		$input = "<tr><td>{$title}<br>{$description}</td><td valign=\"top\" width=\"300\">{$input}</td></tr>\n";
		$output .= $input;
	}
	return $output;
}

function extinputshow($fields, $item = array()) {
	global $lan, $db, $tablepre;
	$output = '';
	if(!is_array($fields)) {
		$fields = array($fields);
	}
	$query = $db->query("SELECT * FROM {$tablepre}_settings WHERE variable LIKE 'f%'");
	$settings = array();
	while($setting = $db->fetch_array($query)) {
		$settings[$setting['variable']] = $setting;
	}
	foreach($fields as $field) {
		$input = '';
		if(!isset($settings[$field])) continue;
		$setting = $settings[$field];
		$value = isset($item[$field]) ? $item[$field] : '';
		if($setting['type'] == 'int' || $setting['type'] == 'float') {
			$input = '<input type="text" name="'.$field.'" value="'.htmlspecialchars($value).'" size="15">';
		} elseif($setting['type'] == 'char') {
			$input = '<input type="text" name="'.$field.'" value="'.htmlspecialchars($value).'" size="50">';
		} elseif($setting['type'] == 'bin') {
			$keyvalue = explode(',', $setting['standby']);//数据保存格式：中文,chinese,英文,english,法文,french
			for($i = 0; $i < count($keyvalue) - 1; $i += 2) {
				if($keyvalue[$i + 1] == $value) {
					$input .= '<input type="radio" name="'.$field.'" value="'.$value.'" checked>&nbsp;'.$keyvalue[$i].'&nbsp;';
				} else {
					$input .= '<input type="radio" name="'.$field.'" value="'.$value.'">&nbsp;'.$keyvalue[$i].'&nbsp;';
				}
			}
		} elseif($setting['type'] == 'select') {//数据保存格式：中文,chinese,英文,english,法文,french
			$keyvalue = explode(',', $setting['standby']);
			$input = "<select name=\"{$field}\">";
			for($i = 0; $i < count($keyvalue) - 1; $i += 2) {
				if($keyvalue[$i + 1] == $value) {
					$input .= '<option value="'.$keyvalue[$i + 1].'" selected>'.$keyvalue[$i].'</option>';
				} else {
					$input .= '<option value="'.$keyvalue[$i + 1].'">'.$keyvalue[$i].'</option>';
				}
			}
			$input .= "</select>";
		}
		$title = isset($settings[$field]['value']) ? $settings[$field]['value'] : $field;
		$input = "<tr><td>{$title}</td><td valign=\"top\">{$input}</td></tr>\n";
		$output .= $input;
	}
	return $output;
}

function refreshpv($ids = '') {
	global $tablepre, $db, $categories;
	includecache('categories');
	if(is_array($ids)) {
		$ids = array_unique($ids);
	} elseif(!empty($ids)) {
		$ids = array($ids);
	} else {
		$ids = array_keys($categories);
	}
}

function refreshitemnum($ids, $type = 'category') {
	global $tablepre, $db, $categories;
	if(is_array($ids)) {
		$ids = array_unique($ids);
	} else {
		$ids = array($ids);
	}
	if($type == 'category') {
		includecache('categories');
		foreach($ids as $id) {
			if($id == 0) continue;
			$items = $db->get_by('COUNT(*)', 'items', "category='$id'");
			if($categories[$id]['categoryup'] == 0) {
				$allids = array();
				$allids[] = $id;
				foreach($categories as $category) {
					if($category['categoryup'] == $id) {
						$allids[] = $category['id'];
					}
				}
				$allids = implode(',', $allids);
				$sql = "SELECT COUNT(*) AS total FROM {$tablepre}_items WHERE category IN ($allids)";
				$allitems = $db->get_field($sql);
			} else {
				refreshitemnum($categories[$id]['categoryup']);
				$allitems = $items;
			}
			$db->query("UPDATE {$tablepre}_categories SET items='$items',allitems='$allitems' WHERE id='$id'");
		}
	} elseif($type == 'section') {
		foreach($ids as $id) {
			$items = $db->get_by('COUNT(*)', 'items', "section='$id'");
			$db->query("UPDATE {$tablepre}_sections SET items='$items' WHERE id='$id'");
		}
	} elseif($type == 'editor') {
		if(count($ids) == 0) {
			$ids = array();
			$sql = "SELECT * FROM {$tablepre}_admins";
			$query = $db->query($sql);
			while($id = $db->fetch_array($query)) {
				$ids[] = $id['editor'];
			}
		}
		foreach($ids as $id) {
			$items = $db->get_by('COUNT(*)', 'items', "editor='$id'");
			$db->query("UPDATE {$tablepre}_admins SET items='$items' WHERE editor='$id'");
		}
	}
}

function checkpath($path, $up = 0) {
	global $lan, $system_root, $db, $tablepre, $categories;
	includecache('categories');
	if(!empty($path)) {
		if(preg_match('/^[0-9]+$/i', $path)) {
			return $lan['pathnameallnum'];
		}
		if(!preg_match('/^[_0-9a-zA-Z\-_]*$/i', $path)) {
			return $lan['pathspecialcharacter'];
		}
		$sql = "SELECT * FROM {$tablepre}_categories WHERE categoryup='$up' AND path='$path' LIMIT 1";
		if($db->get_one($sql)) {
			return $lan['categorypathused'];
		}
	} else {
		return '';
	}
}

function runinfo($message = '') {
	global $db, $debug, $sysname, $sysedition, $mtime, $systemurl;
	$str_debug = $message;
	$endmtime = explode(' ', microtime());
	$exetime = number_format($endmtime[1] + $endmtime[0] - $mtime[1] - $mtime[0], 3);
	if(isset($db)) {
		if(empty($debug)) {
			$str_debug .= "<center><div style=\"width: 100px;margin-top: 10px;\" class=\"mininum\">".$db->querynum.'&nbsp;queries&nbsp;Time:'.$exetime.'</div>';
		} else {
			$str_debug .= "<center><div style=\"width: 100px;margin-top: 10px;\" class=\"mininum\"  onclick=\"show_query_debug()\">".$db->querynum.'&nbsp;queries&nbsp;Time:'.$exetime.'</div>';
			$str_debug .= "<div style=\"display: none;margin-top: 30px;\" id=\"query_debug\">\n";
			$str_debug .= "<span>".count($db->queries)." queries:</span>";
			foreach($db->queries as $query) {
				$str_debug .= "<li>".htmlspecialchars($query)."</li>\n";
			}
			$str_debug .= "</div></center>\n";
			$js = "<script language=\"javascript\">\n";
			$js .= "function show_query_debug() {\n";
			$js .= "var debug = document.getElementById(\"query_debug\");\n";
			$js .= "if(debug.style.display == \"block\") {\n";
			$js .= "	debug.style.display = \"none\";\n";
			$js .= "} else {\n";
			$js .= "	debug.style.display = \"block\";\n";
			$js .= "}\n";
			$js .= "}\n";
			$js .= "</script>\n";
			$str_debug .= $js;
		}
	}
	$str_sysinfo = "<center class=\"mininum\" style=\"margin-top: 5px;\"><a href=\"http://www.akcms.com\" target=\"_blank\">Copyright &copy; 2007-2009 {$sysname}&nbsp;{$sysedition}</a></center>";
	$str_debug = ak_replace("</body>", "$str_debug\n$str_sysinfo\n</body>", ob_get_contents());
	ob_end_clean();
        ob_start('ob_gzhandler');
        echo($str_debug);
}

function aexit() {
        closedb();
	exit();
}

function updatecache($cachename = '', $designate = array()) {
	require_once AK_ROOT.'./include/cache.func.php';
	return coreupdatecache($cachename, $designate);
}

function getinc($id = 0, $type = 'item') {
	global $sysname, $sysedition, $system_root, $setting_forbidinclude;
	$return = '';
	if($type == 'item') {
		if(empty($setting_forbidinclude)) {
			$return .= <<<EOF
<script>
document.write('<script src="[home]akcms_inc.php?i={$id}"><\/script>');
</script>
EOF;
		}
	} elseif($type == 'category') {
		if(empty($setting_forbidinclude)) {
			$return .= <<<EOF
<script>
document.write('<script src="[home]akcms_inc.php?i=c{$id}"><\/script>');
</script>
EOF;
		}
	}
	return $return;
}

function createfore() {
	global $system_root;
	$config_data = "<?php\n$"."system_root = '{$system_root}';\n$"."foreload = 1;\n?>";
	writetofile($config_data, '../akcms_config.php');
	$files = readpathtoarray(AK_ROOT.'install', 1);
	foreach($files as $file) {
		if($file == 'config.inc.php') continue;
		if($file == 'install.sql.php') continue;
		if(strpos($file, '.php') !== false) ak_copy(AK_ROOT.'install/'.$file, FORE_ROOT.$file);
	}
}

function cronnexttime($cron) {//返回一个计划任务的下次执行时间，参数是一个数组 0 不刷新 1 每天刷新 2 每月几号 3 星期几 4 间隔几分钟
	global $timestamp;
	list($y, $m, $d, $h, $i, $s) = explode(',', date('Y,m,d,H,i,s', $timestamp));
	is_numeric($cron['hour']) || $cron['hour'] = 0;
	is_numeric($cron['minute']) || $cron['minute'] = 0;
	if($cron['type'] == 1) {
		$todaytime = mktime($cron['hour'], $cron['minute'], 0, $m, $d, $y);
		if($todaytime < $cron['lasttime']) {//刚执行过
			$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m, $d + 1, $y);
		} else {
			$nexttime = $todaytime;
		}
	} elseif($cron['type'] == 2) {
		if($d == $cron['date']) {
			if($h > $cron['hour'] || ($h == $cron['hour'] && $i > $cron['minute'])) {//刚执行过
				$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m + 1, $d, $y);
			} else {
				$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m, $d, $y);
			}
		} elseif($d > $cron['date']) {
			$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m + 1, $d, $y);
		} else {//$d < $cron['date']
			$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m, $cron['date'], $y);
		}
	} elseif($cron['type'] == 3) {
		$w = date('w', $timestamp);
		if($w == $cron['day']) {
			if($h > $cron['hour'] || ($h == $cron['hour'] && $i > $cron['minute'])) {//刚执行过
				$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m, $cron['day'] + 7, $y);
			} else {
				$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m, $cron['day'], $y);
			}
		} else {
			$dayadd = ($cron['day'] + 7 - $w) % 7;
			$nexttime = mktime($cron['hour'], $cron['minute'], 0, $m, $cron['day'] + $dayadd, $y);
		}
	} else {
		$nexttime = $cron['lasttime'] + $cron['minute'];
	}
	return $nexttime;
}

function renderkeywords($text, $keywords) {
	global $setting_keywordslink, $setting_globalkeywordstemplate;
	$replace = array();
	$to = array();
	if(!empty($setting_globalkeywordstemplate)) {
		$globalkeywords = getkeywordscache();
		foreach($globalkeywords as $_k) {
			$replace[] = $_k[0];
			$_to = str_replace('[url]', $_k[1], $setting_globalkeywordstemplate);
			$_to = str_replace('[keyword]', $_k[0], $_to);
			$_to = str_replace('[digest]', $_k[2], $_to);
			$to[] = $_to;
		}
	}
	if(!empty($setting_keywordslink)) {
		if($keywords != '') {
			$keywords = tidyitemlist($keywords, ',', 0);
			$keywords = explode(',', $keywords);
			$keywords = sortbylength($keywords);
		}
		$_keywordslink = "<span class=\"ak_keyword\"><a href=\"{$setting_keywordslink}\" target=\"_blank\">[keywordtext]</a></span>";
		foreach($keywords as $keyword) {
			$keyword = trim($keyword);
			if(empty($keyword)) continue;
			if(in_array($keyword, $replace)) continue;
			$_to = ak_replace('[keyword]', urlencode($keyword), $_keywordslink);
			$_to = ak_replace('[keywordtext]', $keyword, $_to);
			$replace[] = $keyword;
			$to[] = $_to;
		}
	}
	foreach($replace as $_k => $_v) {
		$text = replacekeyword($text, $_v, $to[$_k]);
	}
	return $text;
}

function replacekeyword($text, $replace, $to) {
	$_replace2 = array();
	$_to2 = array();
	$_replace = array();
	$_to = array();
	preg_match_all('/<a(.*?)>(.*?)<\/a>/i', $text, $matchs);
	foreach($matchs[0] as $match) {
		if(strpos($match, $replace) === false) continue;
		$_replace[] = $match;
		$_to[] = md5($match);
	}
	if(isset($_replace) && isset($_to)) {
		$text = ak_replace($_replace, $_to, $text, 1, 1);
	}
	preg_match_all('/<(.*?)>/i', $text, $matchs);
	foreach($matchs[0] as $match) {
		if(in_string($match, $replace) == 0) continue;
		$_replace2[] = $match;
		$_to2[] = md5($match);
	}
	if(isset($_replace2) && isset($_to2)) {
		$text = ak_replace($_replace2, $_to2, $text, 1, 1);
	}
	$text = ak_replace($replace, $to, $text, 1, 1);
	if(isset($_replace) && isset($_to)) {
		$text = ak_replace($_to, $_replace, $text, 1, 1);
	}
	if(isset($_replace2) && isset($_to2)) {
		$text = ak_replace($_to2, $_replace2, $text, 1, 1);
	}
	return $text;
}

function operatespiderlist($spiderid, $write = 1, $listurl = '') {
	global $spider_items_process, $tablepre, $db, $thetime;
	$sql = "SELECT * FROM {$tablepre}_spiders WHERE id='$spiderid'";
	$spider = $db->get_one($sql);
	$spiderdata = unserialize($spider['data']);
	if($listurl == '') {
		$u = $spiderdata['listurl'];
	} else {
		$u = $listurl;
	}
	$item_urls = array();

	if(substr($u, -1) == '/') {
		$url_path = $u;
	} else {
		$pathinfo = pathinfo($u);
		$url_path = $pathinfo['dirname'];
	}
	$us = array();
	if(isset($spiderdata['startid']) && isset($spiderdata['endid'])) {
		for($i = $spiderdata['startid']; $i <= $spiderdata['endid']; $i ++) {
			$us[] = str_replace('(*)', $i, $u);
		}
	} else {
		$us[] = $u;
	}
	foreach($us as $url) {
		$domaininfo = parse_url($url);
		$domain = $domaininfo['scheme'].'://'.$domaininfo['host'];
		$text = readfromurl($url);
		$text = getfield($spiderdata['start'], $spiderdata['end'], $text, 0);
		$text = strip_tags($text, '<a>');
		preg_match_all("'<\s*a.*?href\s*=(.+?)(\s+.*?)?>(.*?)<\s*/a\s*>'isx",$text,$matchs);
		$urls = array();
		foreach($matchs[1] as $key => $link) {
			$title = $matchs[3][$key];
			$urlcharacters = explode(',', $spiderdata['urlcharacter']);
			$titlecharacters = explode(',', $spiderdata['titlecharacter']);
			$urlskips = explode(',', $spiderdata['urlskip']);
			$titleskips = explode(',', $spiderdata['titleskip']);
			if((count($urlcharacters) > 1 || !empty($urlcharacters[0])) && in_string($link, $urlcharacters) == 0) continue;
			if((count($titlecharacters) > 1 || !empty($titlecharacters[0])) && in_string($title, $titlecharacters) == 0) continue;
			if((count($urlskips) > 1 || !empty($urlskips[0])) && in_string($link, $urlskips) == 1) continue;
			if((count($titleskips) > 1 || !empty($titleskips[0])) && in_string($title, $titleskips) == 1) continue;
			$link = str_replace('\'', '', $link);
			$link = str_replace('"', '', $link);
			if(strpos($link, 'http://') === false) {
				if(substr($link, 0, 1) == '/') {
					$link = $domain.$link;
				} else {
					$link = $url_path.'/'.$link;
				}
			}
			if(!in_array($link, $urls)) {
				$urls[] = $link;
				$title = strip_tags(trim($title));
				$title = str_replace("\n", '', $title);
				$title = str_replace("\r", '', $title);
				$item_urls[] = $spiderid.','.$spider['rule'].','.$link.','.$title;
			}
		}
	}
	foreach($item_urls as $key => $_item) {
		list(,,$_url,) = explode(',', $_item);
		$sql = "SELECT id FROM {$tablepre}_spidercatched WHERE `key`='".ak_md5($_url, 1)."'";
		if($catched = $db->get_one($sql, 1)) {
			unset($item_urls[$key]);
		}
	}
	$item_urls = array_reverse($item_urls);
	$content = implode("\n", $item_urls);
	if(!empty($write)) {
		error_log($content."\n", 3, $spider_items_process);
		$db->query("UPDATE {$tablepre}_spiders SET lasttime='$thetime' WHERE id='$spiderid'");
	} else {
		return $content;
	}
}

function spiderurl($ruleid, $url, $linktext = '') {//根据采集规则和URL得到一个结果数组
	global $spiderrules, $itemexts, $db, $tablepre, $dateline;
	$return = array();
	includecache('spiderrules');
	if(!isset($spiderrules[$ruleid])) return false;
	$rule = $spiderrules[$ruleid];
	$content = "<url:{$url}>\n".convspecialchars(readfromurl($url));
	$content = "<title:{$linktext}>\n".$content;
	$content = str_replace("\r\n", "\n", $content);
	if(!empty($rule['character']) && strpos($content, (string)$rule['character']) === false) {
		return array();
	}
	if(!empty($rule['skip']) && strpos($content, (string)$rule['skip']) !== false) {
		return array();
	}
	$array_replace = array('[linktext]');
	$array_to = array($linktext);
	for($i = 0; $i < 20; $i ++) {
		$num = $i + 1;
		$field_start = $rule["field{$num}_start"];
		$field_end = $rule["field{$num}_end"];
		if(!empty($field_start) && !empty($field_end)) {
			$field[$i + 1] = getfield($field_start, $field_end, $content);
			if($field[$i + 1] === false) {
				$field[$i + 1] = '';
			} else {
				$array_replace[] = "[field{$num}]";
				$array_to[] = trim($field[$i + 1]);
			}
		}
	}

	$array_rule_replace = explode("\n", $rule['replace']);
	foreach($array_rule_replace as $a_replace) {
		$array_replace_to = explode('|', trim($a_replace));
		if(count($array_replace_to) == 2) {
			$_c = count($array_to);
			$_r = $array_replace_to[0];
			$_r = str_replace('[linktext]', $linktext, $_r);
			$_r = str_replace('[n]', "\n", $_r);
			$_t = $array_replace_to[1];
			$_t = str_replace('[linktext]', $linktext, $_t);
			$_t = str_replace('[n]', "\n", $_t);
			for($i = 0; $i < $_c; $i ++) {
				//$_r = str_replace($array_replace[$i], $array_to[$i], $_r);
				//$_t = str_replace($array_replace[$i], $array_to[$i], $_t);
			}
			$array_replace[] = $_r;
			$array_to[] = $_t;
		}
	}
	$return['title'] = ak_replace($array_replace, $array_to, $rule['title']);
	$return['title'] = clearspider($return['title']);

	$return['aimurl'] = ak_replace($array_replace, $array_to, $rule['aimurl']);
	$return['aimurl'] = clearspider($return['aimurl']);

	$return['shorttitle'] = ak_replace($array_replace, $array_to, $rule['shorttitle']);
	$return['shorttitle'] = clearspider($return['shorttitle']);

	$return['author'] = ak_replace($array_replace, $array_to, $rule['author']);
	$return['author'] = clearspider($return['author']);

	$return['source'] = ak_replace($array_replace, $array_to, $rule['source']);
	$return['source'] = clearspider($return['source']);

	$return['editor'] = ak_replace($array_replace, $array_to, $rule['editor']);
	$return['editor'] = clearspider($return['editor']);

	$return['text'] = ak_replace($array_replace, $array_to, $rule['text']);

	if($rule['keywords'] == '[auto]') {
		$return['keywords'] = getkeywords($return['title'], $return['text']);
	} else {
		$return['keywords'] = ak_replace($array_replace, $array_to, $rule['keywords']);
		$return['keywords'] = clearspider($return['keywords']);
	}
	if($rule['filename'] == '[autobytitle]' || $rule['filename'] == '[auto]') {
		$return['filename'] = calfilenamebytitle($return['title']);
	} elseif($rule['filename'] == '[autobykeywords]') {
		if($return['keywords'] != '') {
			$return['filename'] = calfilename($return['title'], $return['text'], 0, 0, $dateline);
		} else {
			$return['filename'] = calfilenamebykeywords($return['keywords'], 0, 0, $dateline);
		}
	} else {
		$return['filename'] = ak_replace($array_replace, $array_to, $rule['filename']);
		$return['filename'] = clearspider($return['filename']);
	}
	$return['keywords'] = tidykeywords($return['keywords']);

	$return['digest'] = ak_replace($array_replace, $array_to, $rule['digest']);
	$return['digest'] = clearspider($return['digest']);
	$return['digest'] = substr($return['digest'], 0, 255);

	$return['picture'] = ak_replace($array_replace, $array_to, $rule['picture']);
	$return['picture'] = clearspider($return['picture']);

	$extfields = ak_unserialize($rule['extfields']);
	if(is_array($extfields)) {
		foreach($extfields as $k => $v) {
			$return[$k] = ak_array_replace($array_replace, $array_to, $v);
			$return[$k] = clearspider($return[$k]);
		}
	}
	foreach($return as $key => $value) {
		if(strpos($value, '[field') !== false) {
			$return[$key] = preg_replace('/\[field[0-9]+\]/i', '', $return[$key]);
		}
	}
	$return['text'] = tidybody($return['text']);
	return $return;
}

function calfilename($title, $text, $category, $itemid, $dateline) {//自动命名
	global $setting_htmlexpand, $db, $tablepre;
	//$setting_nameby
	//0 id
	//1 title
	//2 keywords
	$setting_nameby = 1;//debug 此设置暂时写死
	if(empty($setting_nameby)) {
		return '';
	} elseif($setting_nameby == 1) {
		$filename_base = str_replace(' ', '-', $title);
	} else {
		$array_keywords_whole = getkeywords($title, $text, 6, 0);
		if(!is_array($array_keywords_whole) || empty($array_keywords_whole)) {
			return '';
		}
		$filename_base = implode('-', $array_keywords_whole);
	}
	$filename_base = string2filename(getpinyin($filename_base, 1));
	$filename_base = substr($filename_base, 0, 249);//249 = 255 - strlen('_1.htm')
	$i = 0;
	while(1) {
		if($i == 0) {
			$num = '';
		} else {
			$num = '-'.$i;
		}
		$filename = $filename_base.$num.$setting_htmlexpand;
		$htmlfilename = htmlname($itemid, $category, $dateline, $filename);
		$sql = "SELECT * FROM {$tablepre}_filenames WHERE filename='$htmlfilename'";
		if(!$db->get_one($sql)) break;
		$i ++;
	}
	return $filename;
}

function calfilenamebytitle($title, $category = 0, $itemid = 0, $dateline = 0) {//根据题目算出文件名
	global $setting_htmlexpand;
	$title = splittitlebykeywords($title);
	$filename_base = substr(getpinyin($title), 0, 200);
	$i = 0;
	while(1) {
		if($i == 0) {
			$num = '';
		} else {
			$num = '-'.$i;
		}
		$filename = $filename_base.$num.$setting_htmlexpand;
		$htmlfilename = htmlname($itemid, $category, $dateline, $filename);
		if(!getidbyfilename($htmlfilename)) break;
		$i ++;
	}
	return $filename;
}

function calfilenamebykeywords($array_keywords_whole, $category, $itemid, $dateline) {
	global $setting_htmlexpand, $db, $tablepre;
	$keywordslength = 0;
	$array_keywords = array();
	if(!is_array($array_keywords_whole)) {
		if($itemid != 0) return $itemid.$setting_htmlexpand;
		return '';
	}
	foreach($array_keywords_whole as $keyword) {
		//if($keywordslength > 20) break;
		$pinyin = getpinyin($keyword);
		$keywordslength += strlen($pinyin);
		$array_keywords[] = $pinyin;
	}
	$i = 0;
	while(1) {
		if($i == 0) {
			$num = '';
		} else {
			$num = '-'.$i;
		}
		$filename = implode('-', $array_keywords).$num.$setting_htmlexpand;
		$htmlfilename = htmlname($itemid, $category, $dateline, $filename);
		$sql = "SELECT * FROM {$tablepre}_filenames WHERE filename='$htmlfilename'";
		if(!$db->get_one($sql)) break;
		$i ++;
	}
	return $filename;
}

function splittitlebykeywords($title) {
	$dickeywords = getdickeywords();
	foreach($dickeywords as $dickeyword) {
		$title = str_replace($dickeyword, "-".$dickeyword."-", $title);
	}
	if(substr($title, 0, 1) == '-') $title = substr($title, 1);
	if(substr($title, -1) == '-') $title = substr($title, 0, -1);
	$title = getpinyin($title);
	$title = str_replace('--', '-', $title);
	return $title;
}

function getdickeywords() {
	$dics = readfromfile(AK_ROOT.'./dic/custom.participle.dic');
	$dics .= "\n".$dics."\n".readfromfile(AK_ROOT.'./dic/common.participle.dic')."\n";
	$array_dic = explode("\n", $dics);
	$skipdic = readfromfile(AK_ROOT.'./dic/skip.participle.dic');
	$array_skipkeywords = explode("\n", $skipdic);
	foreach($array_skipkeywords as $skipkeyword) {
		$dics = str_replace($skipkeyword, '', $dics);
		$dics = str_replace("\n\n", "\n", $dics);
	}
	$dics = substr($dics, 1, -1);
	return explode("\n", $dics);
}

function report() {
	global $setting_adminemail, $setting_smtpemail, $setting_sitename, $thetime, $lan;
	$text = render_template(array(), 'report.htm');
	$title = date('Y-m-d', $thetime - 24* 3600);
	$title .= "- {$setting_sitename} {$lan['statisticsreport']}";
	sendmail($setting_adminemail, $title, $text, $setting_smtpemail, 'HTML');
}

function getsysteminfos($variable) {
	global $infos;
	includecache('infos');
	return $infos[$variable];
}

function addwatermark($image, $watermark = '', $position = 0, $quality = -1) {
	global $setting_attachwatermarkposition, $setting_attachimagequality;
	if($watermark == '') {
		if(file_exists(AK_ROOT.'./images/admin/watermark.png')) {
			$watermark = AK_ROOT.'./images/admin/watermark.png';
		} else {
			$watermark = AK_ROOT.'./images/admin/watermark.gif';
		}
	}
	$position == 0 && $position = $setting_attachwatermarkposition;
	$position == 0 && $position = rand(1, 9);
	if($position == -1) return true;
	$quality == -1 && $quality = $setting_attachimagequality;
	require_once(AK_ROOT.'./include/image.func.php');
	return coreaddwatermark($image, $watermark, $position, $quality);
}

function encodeip($ip) {
	$d = explode('.', $ip);
	if(!isset($d[3])) return 'wrong ip';
	$d[3] = '*';
	return implode('.', $d);
}

function tidykeywords($keywords) {
	$keywords = ak_replace(' ', ',', $keywords);
	return tidyitemlist($keywords, ',', 0);
}

function updateitemfilename($ids) {//未完成
	global $db, $tablepre;
	$_ids = implode(',', $ids);
	$query = $db->query("SELECT * FROM {$tablepre}_items WHERE id IN ($_ids)");
	while($item = $db->fetch_array($query)) {
		$filename = htmlname($item['id'], $item['category'], $item['dateline'], $item['filename']);
		$sql = "UPDATE {$tablepre}_filenames SET filename=''";
	}
}

function refreshcommentnum($id) {
	global $db, $tablepre;
	$sql = "SELECT COUNT(*) FROM {$tablepre}_comments WHERE itemid='$id'";
	$commentnum = $db->get_field($sql);
	$sql = "UPDATE {$tablepre}_items SET commentnum='$commentnum' WHERE id='$id'";
	debug($sql);
	$db->query($sql);
}

function deletecommentbyip($ip) {
	global $db, $tablepre;
	$sql = "DELETE FROM {$tablepre}_comments WHERE ip='$ip'";
	$db->query($sql);
}

function get_upload_filename($filename, $id, $category, $type = 'attach') {
	global $setting_attachmethod, $setting_imagemethod, $setting_previewmethod, $thetime;
	if($type == 'attach') {
		$return = $setting_attachmethod;
	} elseif($type == 'image') {
		$return = $setting_imagemethod;
	} elseif($type == 'preview') {
		$return = $setting_previewmethod;
	}
	list($y, $m, $d) = explode('-', date('Y-m-d', $thetime));
	$categorypath = $path = get_category_path($category);
	$filename = random(6).'.'.fileext($filename);
	$return = str_replace('[y]', $y, $return);
	$return = str_replace('[m]', $m, $return);
	$return = str_replace('[d]', $d, $return);
	$return = str_replace('[f]', $filename, $return);
	$return = str_replace('[id]', $id, $return);
	$return = str_replace('[categorypath]', $categorypath, $return);
	return $return;
}

function replaceintocrons($type, $day, $date, $hour, $minute, $itemid, $job) {
	global $tablepre, $db;
	if($db->get_one("SELECT * FROM {$tablepre}_crons WHERE itemid='$itemid' AND job='$job'")) {
		$db->query("UPDATE {$tablepre}_crons SET `type`='$type',`day`='$day',`date`='$date',`hour`='$hour',`minute`='$minute' WHERE itemid='$itemid'");
	} else {
		$value = array(
			'type' => $type,
			'day' => $day,
			'date' => $date,
			'hour' => $hour,
			'minute' => $minute,
			'itemid' => $itemid,
			'job' => $job,
		);
		$db->insert('crons', $value);
	}
}

function extfieldinput($field) {
	$type = $field['type'];
	$standby = htmlspecialchars($field['standby']);
	if($type == 'string' || $type == 'number') {
		return "<input type=\"text\" name=\"ext_{$field['alias']}\" value=\"{$field['standby']}\" size=\"60\">";
	} elseif($type == 'select') {
		$return = '';
		$items = explode("\n", $standby);
		foreach($items as $item) {
			$f = explode(',', trim($item));
			$v = $t = $f[0];
			if(isset($f[1])) $t = $f[1];
			$return .= "<option value=\"{$v}\">{$t}</option>\n";
		}
		return "<select name=\"ext_{$field['alias']}\">{$return}</select>";
	} elseif($type == 'radio') {
		$return = '';
		$items = explode("\n", $standby);
		$i = 0;
		foreach($items as $item) {
			$i ++;
			$f = explode(',', trim($item));
			$v = $t = $f[0];
			if(isset($f[1])) $t = $f[1];
			$id = "{$field['alias']}_{$i}";
			$return .= "<input type=\"radio\" id=\"$id\" name=\"ext_{$field['alias']}\" value=\"{$v}\"><label for=\"{$id}\">{$t}</label>\n";
		}
		return $return;
	}
}

function extfields() {
	global $settings;
	return unserialize($settings['extfields']);
}

function getidbyfilename($filename) {
	global $db;
	$id = $db->get_by('id', 'filenames', "filename='$filename'");
	return $id;
}
?>
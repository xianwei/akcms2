<?php
function get_category_data($id, $template = 'default') {
	global $template_path, $smarty, $db, $tablepre, $lan, $thetime, $system_root, $lr, $setting_keywordslink, $header_charset, $setting_homepage, $setting_defaultfilename, $setting_html, $categories, $sections, $setting_attachtemplate, $setting_storemethod, $html_smarty;
	$db = db();
	includecache('categories');
	$subcategories = array();
	$variables = array();
	$variables['_pagetype'] = 'category';
	$variables['_pageid'] = $id;
	$sql = "SELECT * FROM {$tablepre}_categories WHERE id='{$id}' LIMIT 1";
	if(!$category = $db->get_one($sql)) return array();
	if($category['categoryup'] == 0) {
		$sql = "SELECT id FROM {$tablepre}_categories WHERE categoryup='$id' ORDER BY orderby";
		$query = $db->query($sql);
		while($subcategory = $db->fetch_array($query)) {
			$subcategories[] = $subcategory['id'];
		}
	}
	$variables['template'] = get_category_template($id, 'default');
	$variables['subcategories'] = $subcategories;
	$variables['home'] = '[home]';
	$variables['category'] = $category['id'];
	$variables['categoryname'] = $category['category'];
	$variables['path'] = $category['path'];
	$variables['categoryup'] = $category['categoryup'];
	$variables['orderby'] = $category['orderby'];
	$variables['keywords'] = $category['keywords'];
	$variables['description'] = $category['description'];
	$variables['items'] = $category['items'];//不精确
	$variables['allitems'] = $category['allitems'];//不精确
	$variables['pv'] = $category['pv'];
	$variables['html'] = $category['html'];//debug
	if($category['html'] == 0) {
		$variables['html'] = $setting_html;
	} else {
		$variables['html'] = $category['html'];
	}
	$variables['storemethod'] = $category['storemethod'];
	$variables['fid'] = $category['fid'];
	$variables['ipp'] = get_category_ipp($id);
	

	$sql = "SELECT COUNT(id) FROM {$tablepre}_items WHERE category='$id'";
	$total = $db->get_field($sql);
	$variables['total'] = $total;

	foreach($_GET as $key => $value) {
		$variables[$key] = $value;
	}
	$path = get_category_path($id);
	if($category['categoryup'] == 0) {
		$variables['categoryupname'] = '';
		$variables['categoryuppath'] = '';
		$variables['htmlfilename'] = FORE_ROOT.$path.'/'.$setting_defaultfilename;
	} else {
		$variables['categoryupname'] = $categories[$category['categoryup']]['category'];
		$variables['categoryuppath'] = $categories[$category['categoryup']]['path'];
		$variables['htmlfilename'] = FORE_ROOT.$path.'/'.$setting_defaultfilename;
	}
	$variables['hometemplate'] = get_category_template($id);
	$variables['pagetemplate'] = get_category_template($id, 'list');
	$variables['categoryhomemethod'] = get_category_homemethod($id);
	$variables['categorypagemethod'] = get_category_pagemethod($id);
	return $variables;
}

function batchcategoryhtml($ids) {
	if(!is_array($ids)) $ids = array($ids);
	foreach($ids as $id) {
		$variables = get_category_data($id);
		render_template($variables, '', 1);
	}
}

function operatecreatecategoryprocess() {
	global $batch_categories_process;
	$step = 50;
	$process = readfromfile($batch_categories_process);
	if(strlen($process) < 5) return true;
	if(strpos($process, ',') === false) return false;
	list($id, $page, $maxpage) = explode(',', $process);
	$stepmax = min($maxpage, $page + $step);
	$_pos1 = strpos($process, "\n");
	if($stepmax == $maxpage) {
		$process = substr($process, $_pos1);
	} else {
		$process = "$id,$stepmax,$maxpage".substr($process, $_pos1 + 1);
	}
	writetofile($process, $batch_categories_process);
	$variables = get_category_data($id);
	$variables['template'] = $variables['pagetemplate'];
	$path = get_category_path($id);
	$pagemethod = str_replace('[categorypath]', $path, $variables['categorypagemethod']);
	for($i = $page + 1; $i <= $stepmax; $i ++) {
		$variables['page'] = $i;
		$filename = str_replace('[page]', $i, $pagemethod);
		$variables['htmlfilename'] = FORE_ROOT.$filename;
		render_template($variables, '', 1);
	}
}

function get_category_template($id, $type = 'default') {//type包括：default,item,list
	global $categories;
	includecache('categories');
	$default_template = 'category_home.htm';
	$list_template = 'category_list.htm';
	$item_template = 'item_display.htm';
	if($type == 'default') {
		$template = empty($categories[$id]['defaulttemplate']) ? (!empty($categories[$id]['categoryup']) && !empty($categories[$categories[$id]['categoryup']]['defaulttemplate']) ? $categories[$categories[$id]['categoryup']]['defaulttemplate'] : $default_template) : $categories[$id]['defaulttemplate'];
	} elseif($type == 'list') {
		$template = empty($categories[$id]['listtemplate']) ? (!empty($categories[$id]['categoryup']) && !empty($categories[$categories[$id]['categoryup']]['listtemplate']) ? $categories[$categories[$id]['categoryup']]['listtemplate'] : $list_template) : $categories[$id]['listtemplate'];
	} elseif($type == 'item') {
		$template = empty($categories[$id]['itemtemplate']) ? (!empty($categories[$id]['categoryup']) && !empty($categories[$categories[$id]['categoryup']]['itemtemplate']) ? $categories[$categories[$id]['categoryup']]['itemtemplate'] : $item_template) : $categories[$id]['itemtemplate'];
	}
	return $template;
}

function get_category_pagemethod($id) {
	global $setting_categorypagemethod, $categories;
	$pagemethod = $categories[$id]['categorypagemethod'];
	if($pagemethod == '') {
		$pagemethod = $setting_categorypagemethod;
	}
	return $pagemethod;
}

function get_category_storemethod($id) {
	global $setting_storemethod, $categories;
	if($id == 0) return '';
	$storemethod = $categories[$id]['storemethod'];
	if($storemethod == '') return $setting_storemethod;
	return $storemethod;
}

function get_category_path($id) {
	global $categories;
	if($id == 0) return '';
	$path = $categories[$id]['path'];
	if($path == '') $path = $id;
	if($categories[$id]['categoryup'] != 0)
	$path = get_category_path($categories[$id]['categoryup']).'/'.$path;
	if(substr($path, 0, 1) == '/') return substr($path, 1);
	return $path;
}

function get_category_homemethod($id) {
	global $setting_categoryhomemethod, $categories;
	$categoryhomemethod = $categories[$id]['categoryhomemethod'];
	if($categoryhomemethod == '') {
		$categoryhomemethod = $setting_categoryhomemethod;
	}
	return $categoryhomemethod;
}

function get_category_ipp($id) {
	global $template_path;
	$template = AK_ROOT.'/templates/'.$template_path.'/,'.get_category_template($id, 'list');
	$template_content = readfromfile($template);
	preg_match("/<{getitems bandindex=\"1\".*?num=\"([0-9]+)\"/i", $template_content, $matches);
	$ipp = (empty($matches[1]) ? 10 : $matches[1]);
	return $ipp;
}

function getcategorypath($id, $up = 0, $path = '') {//本方法返回分类的实际存放目录
	global $categories;
	includecache('categories');
	$truepath = FORE_ROOT;
	if($up != 0) {
		$categoryuppath = !empty($categories[$up]['path']) ? $categories[$up]['path'] : $up;
		$truepath .= $categoryuppath.'/';
	}
	$path = empty($path) ? $categories[$id]['path'] : $path;
	$path = empty($path) ? $id : $path;
	$truepath .= $path;
	return $truepath;
}

function getcategoryurl($id) {//本方法返回分类的url访问位置
	global $categories, $setting_html;
	includecache('categories');
	$truepath = '[home]';
	if($id == 0) return '';
	if($setting_html == 1) {
		$path = $categories[$id]['path'];
		$up = $categories[$id]['categoryup'];
		if($up != 0) {
			$categoryuppath = !empty($categories[$up]['path']) ? $categories[$up]['path'] : $up;
			$truepath .= $categoryuppath.'/';
		}
		$path = empty($path) ? $id : $path;
		$truepath .= $path;
	} else {
		$truepath .= 'category.php?id='.$id;
	}
	return $truepath;
}

function ifcategoryhtml($id) {//返回一个category是否生成静态化0,1
	global $categories, $setting_html;
	includecache('categories');
	if(0 == $id) return 1;
	if($categories[$id]['html'] == -1) return 0;
	if($categories[$id]['html'] == 0) if($setting_html && ifcategoryhtml($categories[$id]['categoryup'])) {return 1;} else {return 0;}
	if($categories[$id]['html'] == 1) if(ifcategoryhtml($categories[$id]['categoryup'])) {return 1;} else {return 0;}
}

function getcategorytemplate($id) {
	global $categories;
	includecache('categories');
	if(empty($categories[$id]['itemtemplate'])) {
		$template = 'item_display.htm';
	} else {
		$template = $categories[$id]['itemtemplate'];
	}
	return $template;
}

function includesubcategories($stringcategories) {
	global $categories;
	$outputcategories = explode(',',$stringcategories);
	includecache('categories');
	foreach($categories as $category) {
		if(in_array($category['categoryup'], $outputcategories) && !in_array($category['id'], $outputcategories)) {
			$outputcategories[] = $category['id'];
		}
	}
	return implode(',', $outputcategories);
}
?>
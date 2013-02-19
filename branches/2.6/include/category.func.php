<?php
function get_category_data($id, $template = 'default') {
	global $template_path, $smarty, $db, $tablepre, $lan, $thetime, $system_root, $lr, $setting_keywordslink, $header_charset, $setting_homepage, $setting_defaultfilename, $setting_html, $categories, $sections, $setting_attachtemplate, $setting_storemethod, $html_smarty;
	includecache('categories');
	$subcategories = array();
	$variables = array();
	$variables['_pagetype'] = 'category';
	if(!a_is_int($id)) return false;
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
	$variables['alias'] = $category['alias'];
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
	$variables['ipp'] = get_category_ipp($id);
	$sql = "SELECT COUNT(id) FROM {$tablepre}_items WHERE category='$id'";
	$total = $db->get_field($sql);
	$variables['total'] = $total;

	foreach($_GET as $key => $value) {
		$variables[$key] = $value;
	}
	$path = get_category_path($id);
	$_homemethod = FORE_ROOT.get_category_homemethod($id);
	$_homemethod = str_replace('[categorypath]', $path, $_homemethod);
	$variables['htmlfilename'] = $_homemethod;
	if($category['categoryup'] == 0) {
		$variables['categoryupname'] = '';
		$variables['categoryuppath'] = '';
	} else {
		$variables['categoryupname'] = $categories[$category['categoryup']]['category'];
		$variables['categoryuppath'] = $categories[$category['categoryup']]['path'];
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
	global $categories, $setting_html, $homepage, $setting_defaultfilename, $setting_categoryhomemethod;
	includecache('categories');
	$truepath = $homepage;
	if($id == 0) return '';
	$url = '';
	$path = $categories[$id]['path'];
	$up = $categories[$id]['categoryup'];
	if($up != 0) {
		$categoryuppath = !empty($categories[$up]['path']) ? $categories[$up]['path'] : $up;
		$truepath .= $categoryuppath.'/';
	}
	$path = empty($path) ? $id : $path;
	$truepath .= $path;
	$url = $setting_categoryhomemethod;
	$url = str_replace('[id]', $id, $url);
	$url = str_replace('[categorypath]', $truepath, $url);
	return $url;
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

function batchcategorypagehtml($ids) {
	global $db, $batch_categories_process;
	if(!is_array($ids)) $ids = array($ids);
	$process = '';
	foreach($ids as $id) {
		$items_total = $db->get_by('COUNT(*)', 'items', "category='$id'");
		$ipp = get_category_ipp($id);
		$maxid = ceil($items_total / $ipp);
		$process .= "$id,0,$maxid\n";
	}
	writetofile($process, $batch_categories_process);
}

function getcategorybypath($path, $parentid = 0) {
	global $categories;
	includecache('categories');
	foreach($categories as $category) {
		if($category['categoryup'] != $parentid) continue;
		if($category['path'] == $path || $category['id'] == $path) return $category['id'];
	}
	return false;
}

function rendercategorytree($root = 0, $layer = 0) {
	global $db, $categories, $lan;
	$_tree = '';
	$_sub = '';
	$array_categories = $categories;
	foreach($array_categories as $category) {
		if($category['categoryup'] == $root) {
			$_sub .= rendercategorytree($category['id'], $layer + 1);
		}
	}
	if($root > 0) {
		$_tree .= "<div id='category_{$root}'>\n";
		if($_sub != '') {
			$_tree .= "<img src='images/admin/-.gif'>";
		} else {
			$_tree .= "<img src='images/admin/empty.gif'>";
		}
		$_tree .= "<img src='images/admin/folder.gif'><a href='admincp.php?action=editcategory&id={$root}'>{$categories[$root]['category']}</a>&nbsp;&nbsp;&nbsp;[<a href='admincp.php?action=newcategory&parent={$categories[$root]['id']}'>+{$lan['addsubcategory']}</a>]&nbsp;[<a href='admincp.php?action=newitem&category={$categories[$root]['id']}'>+{$lan['item_new']}</a>]&nbsp;[<a href='admincp.php?action=editcategory&id={$root}'>{$lan['edit']}</a>]&nbsp;[<a href='admincp.php?action=deletecategory&id={$root}'>{$lan['delete']}</a>]&nbsp;(ID:{$root})({$lan['itemnum']}:{$categories[$root]['items']})</div>\n";
	} elseif($root == 0) {
		$_tree .= "<div id='category_0'><img src='images/admin/folder.gif'>{$lan['allcategory']}[<a href='admincp.php?action=newcategory&parent=0'>{$lan['addsubcategory']}</a>]</div>";
	}
	if($_sub != '') {
		$_tree .= "<div id='c_{$root}' class='layer_{$layer}'>\n{$_sub}</div>";
	}
	return $_tree;
}

function rendercategoryselect($id = 0, $layer = 0) {
	global $db, $categories;
	$_tree = '';
	$_sub = '';
	$array_categories = $categories;
	foreach($array_categories as $category) {
		if($category['categoryup'] == $id) {
			$_sub .= rendercategoryselect($category['id'], $layer + 1);
		}
	}
	if($id > 0) {
		$_tree .= "<option value=\"$id\">".str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $layer + 1).htmlspecialchars($categories[$id]['category'])."</option>\n".$_sub;
	} else {
		$_tree .= $_sub;
	}
	return $_tree;
}
?>
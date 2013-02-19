<?php
//本文件不能自己执行必须由前台调用
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
if(isset($get_id)) {
	$id = $get_id;
} elseif(isset($get_path)) {
	includecache('categories');
	$path = trim($get_path);
	foreach($categories as $category) {
		if($category['path'] == $path) {
			$id = $category['id'];
			break;
		}
	}
} elseif(isset($get_alias)) {
	includecache('categories');
	$alias = trim($get_alias);
	foreach($categories as $category) {
		if($category['alias'] == $alias) {
			$id = $category['id'];
			break;
		}
	}
} elseif(isset($get_category)) {
	includecache('categories');
	$name = trim($get_category);
	foreach($categories as $category) {
		if($category['category'] == $name) {
			$id = $category['id'];
			break;
		}
	}
}
if(!isset($id)) exit('');
$global_category = $id;
if(!isset($template)) {
	if(isset($get_page)) {
		$template = get_category_template($id, 'list');
	} else {
		$template = get_category_template($id, 'default');
	}
}
foredisplay($id, 'category', $template);
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
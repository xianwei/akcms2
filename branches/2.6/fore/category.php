<?php
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
includecache('categories');
if(isset($get_id)) {
	$id = $get_id;
} elseif(isset($get_path)) {
	$fullpath = trim($get_path);
	$paths = explode('/', $fullpath);
	$parentid = 0;
	foreach($paths as $path) {
		$id = getcategorybypath($path, $parentid);
		if($id === false) header("HTTP/1.0 404 Not Found");
		$parentid = $id;
	}
} elseif(isset($get_alias)) {
	$alias = trim($get_alias);
	foreach($categories as $category) {
		if($category['alias'] == $alias) {
			$id = $category['id'];
			break;
		}
	}
} elseif(isset($get_category)) {
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
<?php
//本文件不能自己执行必须由前台调用
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
if(isset($get_id)) {
	$id = $get_id;
} elseif(isset($get_alias)) {
	includecache('sections');
	$alias = trim($get_alias);
	foreach($sections as $section) {
		if($section['alias'] == $alias) {
			$id = $section['id'];
			break;
		}
	}
} elseif(isset($get_section)) {
	includecache('sections');
	$name = trim($get_section);
	foreach($sections as $section) {
		if($section['section'] == $name) {
			$id = $section['id'];
			break;
		}
	}
}
if(!isset($id)) exit('');
if(isset($get_page)) {
	$template = get_section_template($id, 'list');
} else {
	$template = get_section_template($id, 'default');
}
foredisplay($id, 'section', $template);
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
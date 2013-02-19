<?php
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
$title = empty($post_title) ? '' : $post_title;
$category = empty($post_category) ? '' : $post_category;
$content = empty($post_content) ? '' : $post_content;
$digest = empty($post_digest) ? '' : $post_digest;
if(!empty($allowcategories)) {
	if(!in_array($category, $allowcategories)) {
		exit('x');
	}
}
if(!empty($denycategories)) {
	if(in_array($category, $denycategories)) {
		exit('y');
	}
}
$sql = "INSERT INTO {$tablepre}_items(title,digest,category,dateline)VALUES('$title','$digest','$category','$thetime')";
$db->query($sql);
debug($db->insert_id());
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
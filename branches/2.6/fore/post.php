<?php
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
isset($title) || $title = empty($post_title) ? '' : $post_title;
isset($content) || $content = empty($post_content) ? '' : $post_content;
isset($digest) || $digest = empty($post_digest) ? '' : $post_digest;
isset($category) || $category = empty($post_category) ? '' : $post_category;
if(empty($category)) {
	$message = isset($um['parametererror']) ? $um['parametererror'] : 'Parameter ERROR';
}
if(!empty($allowcategories) && !in_array($category, $allowcategories)) {
	$message = isset($um['parametererror']) ? $um['parametererror'] : 'Parameter ERROR';
}
if(!empty($denycategories) && in_array($category, $denycategories)) {
	$message = isset($um['parametererror']) ? $um['parametererror'] : 'Parameter ERROR';
}
if(trim($title) == '') $message = isset($um['titleempty']) ? $um['titleempty'] : 'Title can\'t be empty';
if(empty($message)) {
	$value = array(
		'title' => $title,
		'digest' => $digest,
		'category' => $category,
		'dateline' => $thetime
	);
	$db->insert('items', $value);
	$id = $db->insert_id();
	$value = array(
		'itemid' => $id,
		'text' => nl2br($content)
	);
	$db->insert('texts', $value);
	$message = isset($um['success']) ? $um['success'] : 'Success';
}
$variables = array(
	'message' => $message,
);
$html = render_template($variables, 'message.htm');
echo $html;
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
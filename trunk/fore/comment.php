<?php
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
(!isset($post_itemid) || !isset($post_comment)) && exit($paramserror);
if(!empty($setting_commentneedcaptcha)) {
	$captcha = $post_captcha;
	$sid = $post_sid;
	if(empty($sid) || empty($captcha)) {
		msg_visit($captchaerror);
	}
	$captchakey = $db->get_field("SELECT captcha FROM {$tablepre}_captchas WHERE sid='$sid'");
	if($captcha != $captchakey) {
		msg_visit($captchaerror);
	}
	$captchakey = $db->query("DELETE FROM {$tablepre}_captchas WHERE sid='$sid'");
}
$denyips = explode("\n", readfromfile($comment_deny_ip_dic));
if(in_array($onlineip, $denyips)) msg_visit($ipdenied);
$itemid = $post_itemid;
$comment = $post_comment;
$username = isset($post_username) ? $post_username : '';
$title = isset($post_title) ? $post_title : '';

$sql = "SELECT scorenum,totalscore,avgscore FROM {$tablepre}_items WHERE id='$itemid'";
if(!$item = $db->get_one($sql)) exit($paramserror);

$sql = "INSERT INTO {$tablepre}_comments(itemid,username,title,message,dateline,ip)VALUES('$itemid','$username','$title','$comment','$thetime','$onlineip')";
$db->query($sql);

$sql = "UPDATE {$tablepre}_items SET commentnum=commentnum+1 WHERE id='$itemid'";
$db->query($sql);

$language = $lan;

$variables = array(
	'message' => $commentsuccess,
);
$html = render_template($variables, 'message.htm');
echo $html;
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
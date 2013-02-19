<?php
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
(!isset($post_itemid) || !isset($post_score)) && exit('params error.');
$itemid = $post_itemid;
if(strlen($itemid) > 10) exit('error');
$score = $post_score;
$sql = "INSERT INTO {$tablepre}_scores(itemid,score,dateline,ip)VALUES('$itemid','$score','$thetime','$onlineip')";
$db->query($sql);

$sql = "SELECT scorenum,totalscore,avgscore FROM {$tablepre}_items WHERE id='$itemid'";
$item = $db->get_one($sql);
$scorenum = $item['scorenum'];
$totalscore = $item['totalscore'];
$avgscore = $item['avgscore'];

$scorenum ++;
$totalscore += $score;
$avgscore = $totalscore / $scorenum;

$sql = "UPDATE {$tablepre}_items SET scorenum='$scorenum',totalscore='$totalscore',avgscore='$avgscore' WHERE id='$itemid'";
$db->query($sql);
$language = $lan;

$variables = array(
	'message' => $language['scoresuccess'],
	'url_forward' => '/news-'.$itemid.'.htm',
);
$html = render_template($variables, 'message.htm');
echo $html;
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
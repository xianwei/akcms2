<?php
//���ļ������Լ�ִ�б�����ǰ̨����
$system_root_visitor = $system_root;
require_once $mypath.$system_root_visitor.'/include/common.inc.php';
require_once $mypath.$system_root_visitor.'/include/visitor.inc.php';
$sid = $get_sid;
if(empty($sid)) exit;
captcha($sid);
require_once $mypath.$system_root_visitor.'/include/exit.php';
?>
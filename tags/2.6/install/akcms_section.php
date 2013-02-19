<?php
$i = strpos(__FILE__, 'akcms_section.php');
$mypath = substr(__FILE__, 0, $i);
include $mypath.'akcms_config.php';
include $mypath.$system_root.'/fore/section.php';
?>
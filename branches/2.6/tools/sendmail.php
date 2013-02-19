<?php
require_once '../include/command_common.inc.php';
$res = sendmail('sunyubo@qihoo.net', random(6), random(10));
print_r($res);
?>
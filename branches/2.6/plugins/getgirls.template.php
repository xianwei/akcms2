<?php
function getgirls($params) {
	$type = $params['type'];
	$num = $params['num'];
	echo "You have {$num} {$type} girls now.";
}
?>
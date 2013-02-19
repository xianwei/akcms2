<?php
if(file_exists('./resetpassword.php')) {
	exit('please remove resetpassword.php first.');
}
if(file_exists('./include/install.lock')) {
	require_once './include/common.inc.php';
	if(empty($admin_id)) {
		header("location:login.php");
	} else {
		header("location:admincp.php");
	}
} else {
	header("location:install.php");
}
?>
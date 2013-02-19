<?php
$timestamp = time();
$errmsg = '';
$dberror = $this->error();
$dberrno = $this->errno();
if($dberrno == 1114) {
	exit('Error:too many connections.');
} else {
	if($message) {
		$errmsg = "<b>Error info</b>: $message\n\n";
	}
	$errmsg .= "<b>Time</b>: ".gmdate("Y-n-j g:ia", $timestamp)."\n";
	$errmsg .= "<b>Script</b>: ".$_SERVER['PHP_SELF']."\n\n";
	if($sql) {
		$errmsg .= "<b>SQL</b>: ".htmlspecialchars($sql)."\n";
	}
	$errmsg .= "<b>Error</b>:  $dberror\n";
	$errmsg .= "<b>Errno.</b>:  $dberrno";

	echo "</table></table></table></table></table>\n";
	echo "<p style=\"font-family: Verdana, Tahoma; font-size: 11px; background: #FFFFFF;\">";
	echo nl2br($errmsg);
	echo '</p>';
	exit;
}
?>
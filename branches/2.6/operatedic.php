<?php
include('include/common.func.php');
$commondic = readfromfile('dic/source.common.participle.dic');
$dics = explode("\n", $commondic);
$dics = array_reverse(sortbylength($dics));
$dics = array_unique($dics);
foreach($dics as $key => $dic) {
	if(substr($dic, 0, 1) == '#') {
		unset($dics[$key]);
	}
}
debug(count($dics));
$dic = implode("\n", $dics);
writetofile(substr($dic, 0, -1), 'dic/common.participle.dic');
?>
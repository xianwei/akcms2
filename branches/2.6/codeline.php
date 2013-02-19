<?php
$nodb = 1;
include('include/common.inc.php');
$files = readpathtoarray('.');
$phps = array();
while($file = array_pop($files)) {
	if(is_dir($file)) {
		$files = array_merge($files, readpathtoarray($file));
	} else {
		if((substr($file, -4) == '.php' || substr($file, -4) == '.htm' || substr($file, -4) == '.css' || substr($file, -3) == '.js')&& strpos($file, 'templates_c') === false && strpos($file, 'smarty') === false && strpos($file, '/editor/') === false && strpos($file, 'tools') === false && strpos($file, '/cache/') === false && strpos($file, '/language/english') === false && strpos($file, '/language/chinese') === false && strpos($file, '/templates/') === false && strpos($file, '/data/') === false) {
			$phps[] = $file;
		}
	}
}
$totalline = 0;
foreach($phps as $php) {
	$text = readfromfile($php);
	$lines = frequence($text, "\n") + 1;
	$totalline += $lines;
	debug($lines."\t".$php);
}
debug($totalline."\tºÏ¼Æ");
?>
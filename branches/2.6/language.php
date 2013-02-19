<?php
include('./include/common.inc.php');
include('./include/cache.func.php');
include(AK_ROOT.'language/index.php');
$temp_words = array();
$array_languages = array('chinese', 'english');
$array_charsets = array('gbk', 'utf8', 'english');
foreach($array_languages as $language) {
	$temp_words = array();
	foreach($array_words as $key => $word) {
		$temp_words[$key] = $word[$language];
	}
	$str_package = "<?php\n".'$lan = '.arrayeval($temp_words).';'."\n?>";
	foreach($array_charsets as $charset) {
		if($charset == 'utf8') $str_package = ak_utf8_encode($str_package);
		writetofile($str_package, AK_ROOT.'language/'.$language.'/'.$charset.'.php');
	}
}
exit('语言包重新生成完毕！');
?>
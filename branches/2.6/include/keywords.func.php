<?php
function core_getkeywords($title, $content, $num = 6, $returnstring = 1) {
	$title = removespecialchars($title);
	$content = removespecialchars(tidybody($content));
	$en = isen($title);
	if($en == 0) {
		$gradstep = 4;
		$gradsplit = str_repeat(chr(94), 2);
		$repeattitle = 4;
		$keywordmaxlength = 12;
		$message = grad($title, $gradstep, $gradsplit).str_repeat($title, $repeattitle).$content;
		$dics = readfromfile(AK_ROOT.'./dic/custom.participle.dic');
		$dics .= "\n".readfromfile(AK_ROOT.'./dic/common.participle.dic');
		$array_dic = explode("\n", $dics);
		$array_keywords = array();
		$message1 = $message;
		foreach($array_dic as $dic) {
			if(!empty($dic) && strpos($message, $dic) !== false) {
				$array_keywords[$dic] = frequence($message, $dic);
				$message = str_replace($dic, $gradsplit, $message);
			}
		}
		$skipdic = readfromfile(AK_ROOT.'./dic/skip.participle.dic');
		$array_skip = explode("\n", $skipdic);
		foreach($array_keywords as $key => $value) {
			if(in_array($key, $array_skip)) {
				unset($array_keywords[$key]);
			}
			if(strlen($key) == 1) {
				unset($array_keywords[$key]);
			}
		}
		foreach($array_keywords as $key => $value) {
			foreach($array_keywords as $key2 => $value2) {
				if(strlen($key2) < strlen($key) && $value2 <= $value + 1 && strpos($key, $key2) !== false) {
					unset($array_keywords[$key2]);
				}
			}
		}
		arsort($array_keywords);
		$return = array();
		$i = 0;
		foreach($array_keywords as $key => $keyword) {
			if($i ++ >= $num) break;
			$return[] = $key;
		}
		if(empty($returnstring)) return $return;
		return implode(',', $return);
	} else {//english
		$array_title = explode(' ', tidyitemlist($title, ' ', 0));
		$content = tidyitemlist($content, ' ', 0);
		
		$count_words = count($array_title);
		for($i = 0; $i < $count_words; $i ++) {
			$content .= str_repeat(' '.$array_title[$i], ($count_words - $i) / 2);
		}
		$array_content = explode(' ', $content);
		$array_keywords = array_count_values($array_content);
		
		$skips = readfromfile(AK_ROOT.'./dic/skip.participle.dic');
		$array_skips = explode("\n", $skips);
		foreach($array_keywords as $key => $value) {
			if(in_array($key, $array_skips)) {
				unset($array_keywords[$key]);
			}
			if(strlen($key) == 1) {
				unset($array_keywords[$key]);
			}
		}
		arsort($array_keywords);
		$return = array();
		$i = 0;
		foreach($array_keywords as $key => $keyword) {
			if($i ++ >= $num) break;
			$return[] = $key;
		}
		if(empty($returnstring)) return $return;
		return implode(',', $return);
	}
}

function grad($text, $step = 1, $split) {//返回一个字符串的梯度叠加
	$return = '';
	for($i = $step; $i < strlen($text); $i = $i + $step) {
		$return .= $split.substr($text, 0, $i);
	}
	return $return;
}
?>
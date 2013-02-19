<?php
function core_getkeywords($title, $content, $num = 6, $returnstring = 1) {
	$leastlength = 2;
	if($title == '' || $content == '') return '';

	$title = removespecialchars($title);
	$content = removespecialchars($content);
	$en = isen($title);
	if($en == 0) {//chinese
		//参数调优区
		$gradstep = 4;
		$gradsplit = str_repeat(chr(94), 2);
		$repeattitle = 4;
		$keywordmaxlength = 12;
		$title = ini_operate($title, 'chinese');
		$content = ini_operate($content, 'chinese');
		$message = grad($title, $gradstep, $gradsplit).str_repeat($title, $repeattitle).$content;
		if(strlen($message) < $leastlength) return '';
		$dics = readfromfile(AK_ROOT.'./dic/custom.dic');
		$dics .= "\n".readfromfile(AK_ROOT.'./dic/common.dic');
		$array_dic = explode("\n", $dics);
		$array_keywords = array();
		$array_keywords2 = array();//非词典取出来的
		$message1 = $message;
		foreach($array_dic as $dic) {
			if(!empty($dic) && strpos($message, $dic) !== false) {
				$array_keywords[$dic] = frequence($message, $dic);
				$message = str_replace($dic, $gradsplit, $message);
			}
		}
		$message2 = ini_operate($message, 'chinese');
		$message_length = strlen($message2);
		for($length = $keywordmaxlength; $length >= 4; $length = $length - 2) {
			for($i = 0; $i < $message_length - $length; $i = $i + 2) {
				$keyword = substr($message, $i, $length);
				if(strpos($keyword, $gradsplit) !== false) continue;
				$keyword = "$keyword";
				if(array_key_exists($keyword, $array_keywords2)) {
					$array_keywords2[$keyword] ++;
				} else {
					$array_keywords2[$keyword] = 1;
				}
			}
		}
		$skipdic = readfromfile(AK_ROOT.'./dic/skip.dic');
		$array_skip = explode("\n", $skipdic);
		foreach($array_keywords as $key => $value) {
			if(in_array($key, $array_skip)) {
				unset($array_keywords[$key]);
			}
			if(strlen($key) == 1) {
				unset($array_keywords[$key]);
			}
		}
		foreach($array_keywords2 as $key => $value) {
			if($value == 1) {
				unset($array_keywords2[$key]);
			}
			if(strpos($key, $gradsplit) !== false) {
				unset($array_keywords2[$key]);
			}
		}
		arsort($array_keywords2);
		foreach($array_keywords2 as $key => $value) {
			if(strlen($key) * $value * 100 / $message_length > 10) {
				if(strlen($key) == 4) $value -= 2;
				if(strlen($key) == 6) $value -= 1;
				$array_keywords[$key] = $value;
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
		$title = ini_operate($title, 'english');
		$content = ini_operate($content, 'english');
		$array_title = explode(' ', tidyitemlist($title, ' ', 0));
		$content = tidyitemlist($content, ' ', 0);
		
		$count_words = count($array_title);
		for($i = 0; $i < $count_words; $i ++) {
			$content .= str_repeat(' '.$array_title[$i], ($count_words - $i) / 2);
		}
		$array_content = explode(' ', $content);
		$array_keywords = array_count_values($array_content);
		
		$skips = readfromfile(AK_ROOT.'./dic/skip.dic');
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

function ini_operate($text, $language = 'english') {//返回的东西全是纯汉字
	$return = '';
	if($language != 'english') {
		for($i = 0; $i < strlen($text); $i ++) {
			if(ord($text[$i]) < 161) continue;
			$return .= $text[$i];
		}
		$empties = readfromfile(AK_ROOT.'./dic/empty.dic');
		$array_empty = explode("\n", $empties);
		foreach($array_empty as $empty) {
			if(!empty($empty)) {
				$return = ak_replace($empty, '', $return);
			}
		}
	} else {
		for($i = 0; $i < strlen($text); $i ++) {
			$ord = ord($text[$i]);
			if(($ord >= 48 && $ord <= 57) || $ord == 32 || ($ord >= 97 && $ord <= 122)) {
				$return .= $text[$i];
			} elseif($ord >= 65 && $ord <= 90) {
				$return .= chr(ord($text[$i]) + 32);
			} else {
				$return .= ' ';
			}
		}
		$empties = readfromfile(AK_ROOT.'./dic/empty.dic');
		$array_empty = explode("\n", $empties);
		foreach($array_empty as $empty) {
			if(!empty($empty)) {
				$return = ak_replace($empty, '', $return);
			}
		}
	}
	return $return;
}
?>
<?php
require_once $mypath.$system_root.'/include/common.inc.php';
$config_timeout = 10;
if(isset($get_action) && $get_action == 'spider' && empty($setting_forbidspider)) {
	includecache('spiderrules');
	includecache('spiders');
	$item = readlinefromfile($spider_items_process);
	$timeout = $config_timeout;
	if($item == 'empty' || $item == 'noexist') {
		$timeout = 1800000;
		debug('spiders have finished all job.');
	} else {
		list($spiderid, $id, $url, $linktext) = explode(',', $item);
		$rule = $spiderrules[$id];
		$spider = $spiders[$spiderid];
		$category = $spider['category'];
		$section = $spider['section'];
		$key = ak_md5($url, 1);
		if($catched = $db->get_by('id', 'spidercatched', "`key`='$key'")) {
			debug('spidered!!');
			refreshself(1000);
		} else {
			$timeout = $config_timeout;
		}
		$result = spiderurl($id, $url, $linktext);
		if($result === false) {
			refreshself($timeout);
		} elseif(empty($result)) {
			refreshself($timeout);
		}
		$result['title'] = trim($result['title']);
		if(empty($result['title']) || $result['title'] == $rule['title']) {
			echo 'ERROR:title empty!<br>'.$url;
			error_log(date('Y-m-d H:i:s')."\t".$spiderid."\t".$id."\t".$url."\n", 3, $system_root.'/logs/spidererror');
			$timeout = 1000;
			refreshself($timeout);
		}
		$result['title'] = str_replace('&nbsp;', ' ', $result['title']);
		$result['text'] = str_replace('&nbsp;', ' ', $result['text']);
		includecache('categories');
		if($rule['keywords'] == '[auto]' && strlen(trim($result['title']))> 4 && strlen(trim($result['text'])) > 50) {
			$result['keywords'] = getkeywords($result['title'], $result['text']);
		}
		if($rule['filename'] == '[auto]') {
			if(!empty($result['keywords'])) {
				$filename = calfilenamebykeywords(explode(',', $result['keywords']), $category, 0, $timestamp);
			} elseif(empty($setting_autoparsekeywords)) {
				$filename = calfilename($title, $text, $category, 0, $timestamp);
			} else {
				$filename = '';
			}
		} else {
			$filename = '';
		}
		$extfields = ak_unserialize($rule['extfields']);
		$data = array();
		if(is_array($extfields)) {
			foreach($extfields as $k => $v) {
				$data[$k] = $result[$k];
			}
		}
		$ext_flag = 0;
		if(!empty($data)) $ext_flag = 1;
		$data = serialize($data);
		$value = array(
			'title' => $result['title'],
			'shorttitle' => $result['shorttitle'],
			'category' => $category,
			'section' => $section,
			'author' => $result['author'],
			'source' => $result['source'],
			'editor' => $result['editor'],
			'orderby' => $rule['orderby'],
			'dateline' => $thetime,
			'digest' => $result['digest'],
			'aimurl' => $result['aimurl'],
			'filename' => $filename,
			'keywords' => $result['keywords'],
			'picture' => $result['picture'],
			'ext' => $ext_flag
		);
		$db->insert('items', $value);
		$itemid = $db->insert_id();
		debug($itemid.' done');
		if(!empty($result['text'])) {
			if($setting_richtext) {
				$result['text'] = nl2br($result['text']);
			}
			$value = array(
				'itemid' => $itemid,
				'text' => $result['text']
			);
			$db->insert('texts', $value);
		}
		$value = array(
			'id' => $itemid,
			'value' => $data
		);
		$db->replace('item_exts', $value);
		$value = array(
			'key' => $key,
			'url' => $url,
			'dateline' => $thetime,
			'rule' => $id,
			'itemid' => $itemid
		);
		$db->insert('spidercatched', $value);
		$value = array(
			'filename' => htmlname($itemid, $category, $thetime, $filename),
			'type' => 'item',
			'dateline' => $thetime,
			'id' => $itemid,
			'page' => 0
		);
		$db->insert('filenames', $value);
		if(!empty($rule['html'])) {
			batchhtml(array($itemid));
		}
	}
	refreshself($timeout);
}
//自动采集结束

//写iframe调用自动采集开始
if(empty($setting_forbidspider)) {
	$iframe = <<<EOT
	document.write("<iframe src=\"[home]akcms_inc.php?action=spider\" style=\"display:none\"></iframe>");
EOT;
	$iframe = ak_replace('[home]', $homepage, $iframe);
	echo $iframe;
}
//写iframe调用自动采集结束

//统计开始
if(empty($setting_forbidstat)) {
	dealwithstatcache();
	if(isset($get_i)) {
		$request = $get_i;
		if(!isset($cookie_sid)) {
			$cookie_sid = ak_md5($onlineip.$thetime, 1);
			setcookie('sid', $cookie_sid);
		}
		addtostatcache($request, $cookie_sid, $thetime, $onlineip);
	}
}
//统计结束

if(empty($setting_forbidautorefresh) || empty($setting_forbidspider)) {
	includecache('crons');
	$array_batch = array();
	$array_spider = array();
	if(empty($crons)) exit('');
	foreach($crons as $cron) {
		if($cron['nexttime'] < $timestamp) {
			if($cron['job'] == 'page') {
				$array_batch[] = $cron['itemid'];
			} elseif($cron['job'] == 'spider') {
				$array_spider[] = $cron['itemid'];
			} elseif($cron['job'] == 'cate') {
				$cron_cate = 1;
				batchcategoryhtml($cron['itemid'], 0, 'rss');
				batchcategoryhtml($cron['itemid'], 0, 'default');
				batchcategoryhtml($cron['itemid'], 0, 'list');
				$db->query("UPDATE {$tablepre}_crons SET lasttime='$thetime' WHERE job='cate' AND itemid='{$cron['itemid']}'");
			} elseif($cron['job'] == 'report') {
				$cron_report = 1;
				report();
				$db->query("UPDATE {$tablepre}_crons SET lasttime='$thetime' WHERE job='report'");
			}
		}
	}
	if(!empty($array_batch)) {
		batchhtml($array_batch);
		$ids = implode(',', $array_batch);
		$db->query("UPDATE {$tablepre}_crons SET lasttime='$thetime' WHERE job='page' AND itemid IN ($ids)");
	}
	if(!empty($array_spider)) {
		foreach($array_spider as $spider) {
			operatespiderlist($spider);
			$db->query("UPDATE {$tablepre}_crons SET lasttime='$thetime' WHERE job='spider' AND itemid=$spider");
		}
	}
	if(!empty($array_batch) || !empty($array_spider) || isset($cron_cate) || isset($cron_report)) {
		updatecache('crons');
	}
}

function addtostatcache($id, $sid = '', $thetime = 0, $ip) {
	global $statcachefilename;
	$log = $id."\t".$sid."\t".$thetime."\t".$ip."\n";
	error_log($log, 3, $statcachefilename);
}

function dealwithstatcache() {
	global $thetime, $statcachefilename, $tablepre, $db, $setting_statcachesize, $timedifference;
	$wee = getwee($thetime);
	if(!file_exists($statcachefilename)) return;
	$lastmodified = filemtime($statcachefilename) + $timedifference * 3600;
	if(filesize($statcachefilename) > $setting_statcachesize || $wee > $lastmodified) {
		$year = date('Y', $lastmodified);
		$month = date('m', $lastmodified);
		$day = date('d', $lastmodified);
		rename($statcachefilename, $statcachefilename.'.tmp');
		$cache = readfromfile($statcachefilename.'.tmp');
		unlink($statcachefilename.'.tmp');
		$array_cache = explode("\n", $cache);
		$array_cache_operated = array();
		foreach($array_cache as $cache) {
			$array_field = explode("\t", $cache);
			if(count($array_field) >= 4) {
				$array_cache_operated[] = $array_field[0];
				if(substr($array_field[0], 0, 1) == 'c') {
					$type = 'category';
					$itemid = substr($array_field[0], 1);
				} else {
					$type = 'item';
					$itemid = $array_field[0];
				}
			}
		}
		$visit = array_count_values($array_cache_operated);
		foreach($visit as $id => $count) {
			if(empty($id)) continue;
			if(substr($id, 0, 1) == 'c') {
				$id = ak_replace('c', '', $id);
				$sql = "UPDATE {$tablepre}_categories SET pv=pv+{$count} WHERE id='$id'";
				$db->query($sql);
			} else {
				$sql = "UPDATE {$tablepre}_items SET pageview=pageview+{$count} WHERE id='$id'";
				$db->query($sql);
			}
		}
	}
}
?>
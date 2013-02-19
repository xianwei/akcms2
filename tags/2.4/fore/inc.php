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
		debug($item);
		list($spiderid, $id, $url, $linktext) = explode(',', $item);
		$rule = $spiderrules[$id];
		$spider = $spiders[$spiderid];
		$category = $spider['category'];
		$section = $spider['section'];
		$key = ak_md5($url, 1);
		$db = db();
		$sql = "SELECT id FROM {$tablepre}_spidercatched WHERE `key`='$key'";
		if($catched = $db->get_one($sql, 1)) {
			debug('spidered!!');
			refreshself(1000);
		} else {
			$timeout = $config_timeout;
		}
		$result = spiderurl($id, $url, $linktext);
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
		$result = ak_addslashes($result);
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
		includecache('itemexts');
		$extflag = 0;
		foreach($itemexts as $ext) {
			if(isset($result[$ext['Field']])) {
				$extflag = 1; break;
			}
		}
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
			'ext' => $extflag
		);
		$db->insert('items', $value);
		$itemid = $db->insert_id();
		debug($itemid.' done');
		if(!empty($result['text'])) {
			if($setting_richtext) {
				$result['text'] = nl2br($result['text']);
			}
			$sql = "INSERT INTO {$tablepre}_texts(itemid,text)VALUES('$itemid','".$result['text']."')";
			$db->query($sql);
		}
		if($extflag == 1) {
			$array_key = array('id');
			$array_value = array("'".$itemid."'");
			foreach($itemexts as $ext) {
				if(isset($result[$ext['Field']])) {
					$array_key[] = "`{$ext['Field']}`";
					$array_value[] = "'{$result[$ext['Field']]}'";
				}
			}
			$sql_ext_key = implode(',', $array_key);
			$sql_ext_value = implode(',', $array_value);
			$db->query("REPLACE INTO {$tablepre}_item_exts($sql_ext_key)VALUES($sql_ext_value)");
		}
		$db->query("INSERT INTO {$tablepre}_spidercatched(`key`,url,dateline,`rule`,itemid)VALUES('$key','".ak_addslashes($url)."','$timestamp','$id','$itemid')");
		if($filename != '') {
			$filename = htmlname($itemid, $category, $thetime, $filename);
			$db->query("INSERT INTO {$tablepre}_filenames(filename,type,dateline,id,page)VALUES('$filename','item','$thetime','$itemid','0')");
		}
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
		!isset($get_referer) && $get_referer = '';
		if(!isset($cookie_sid)) {
			$cookie_sid = ak_md5($onlineip.$thetime, 1);
			setcookie('sid', $cookie_sid);
		}
		addtostatcache($request, $get_referer, $cookie_sid, $thetime, $onlineip);
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

function addtostatcache($id, $referer = '', $sid = '', $thetime = 0, $ip) {
	global $statcachefilename;
	$log = $id."\t".$referer."\t".$sid."\t".$thetime."\t".$ip."\n";
	error_log($log, 3, $statcachefilename);
}

function dealwithstatcache() {
	global $thetime, $statcachefilename, $tablepre, $db, $setting_statcachesize, $timedifference;
	$db = db();
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
		$visitsql = "REPLACE INTO {$tablepre}_visits(sid,dateline,referer,itemid,type,ip)VALUES";
		foreach($array_cache as $cache) {
			$array_field = explode("\t", $cache);
			if(count($array_field) >= 5) {
				$array_cache_operated[] = $array_field[0];
				if(substr($array_field[0], 0, 1) == 'c') {
					$type = 'category';
					$itemid = substr($array_field[0], 1);
				} else {
					$type = 'item';
					$itemid = $array_field[0];
				}
				$visitsql .= "('{$array_field[2]}','{$array_field[3]}','{$array_field[1]}','$itemid','$type','{$array_field[4]}'),";
			}
		}
		if(strpos($visitsql, '\'') !== false) {
			$visitsql = substr($visitsql, 0, -1);
			$db->query($visitsql);
		}
		$visit = array_count_values($array_cache_operated);
		$visit_all = $db->get_field("SELECT COUNT(*) FROM {$tablepre}_visits WHERE dateline>='$wee' AND dateline<'".($wee + 24 * 3600)."'");
		$ip_all = $db->get_field("SELECT COUNT(distinct sid) FROM {$tablepre}_visits WHERE dateline>='$wee' AND dateline<'".($wee + 24 * 3600)."'");
		$db->query("REPLACE INTO {$tablepre}_stats(dateline,value,type,`by`)VALUES('$wee','$visit_all', 0, 0)");
		$db->query("REPLACE INTO {$tablepre}_stats(dateline,value,type,`by`)VALUES('$wee','$ip_all', 1, 0)");
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
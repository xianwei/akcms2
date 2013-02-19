<?php
require_once './include/common.inc.php';
require_once './include/admin.inc.php';
if(!empty($setting_forbidinclude) || !empty($setting_forbidstat)) adminmsg($lan['statdisabled'], '', 0, 1);
$barwidth = 400;
$pvs = array();
$str_pvs = '';
if(!isset($get_action)) {
	!isset($get_max) && $get_max = 15;
	$end = getwee() + 1;
	$start = $end - ($get_max - 1) * 24 * 3600 - 1;
	if(isset($get_month)) {
		if(strpos($get_month, '-') !== false) {
			list($y, $m) = explode('-', $get_month);
		} else {
			$y = substr($get_month, 0, 4);
			$m = substr($get_month, 4, 2);
		}
		$get_max = 31;
		$start = mktime(0, 0, 0, $m, 1, $y);
		$end = mktime(0, 0, 0, $m + 1, 1, $y);
	}
	$array_stats = getstatby($start, $end);
	foreach($array_stats[1] as $key => $stat) {
		$ip_width = getbarwidth($barwidth, $stat['ip'], $array_stats[0]);
		$pv_width = getbarwidth($barwidth, $stat['pv'], $array_stats[0]);
		$date = date('Y-m-d', $key);
		$str_pvs .= "<tr bgcolor=\"#FFFFFF\"><td class=\"beforebar\"><a href=\"pv.php?action=detail&id={$date}\">{$date}</a></td><td><div class=\"bar1\" style=\"width:{$pv_width}px;\"><div class=\"bar2\" title=\"pv:{$stat['pv']}\"><div class=\"bar3\" style=\"width:{$ip_width}px;\" title=\"ip:{$stat['ip']}\"></div></div></div><div style=\"margin-left:10px\" class=\"mininum\">({$stat['ip']}/{$stat['pv']})(<a href=\"pv.php?action=itemrank&by={$date}\">{$lan['pvbyitem']}</a>)</div></td></tr>";
	}
} elseif($get_action == 'month') {
	!isset($get_max) && $get_max = 15;
	$end = getwee($thetime, 'month') + 1;
	$start = addtime($get_max * -1, 'month', $end) - 1;
	$array_stats = getstatby($start, $end, 'month');
	foreach($array_stats[1] as $key => $stat) {
		$ip_width = getbarwidth($barwidth, $stat['ip'], $array_stats[0]);
		$pv_width = getbarwidth($barwidth, $stat['pv'], $array_stats[0]);
		$date = date('Y-m', $key);
		$str_pvs .= "<tr bgcolor=\"#FFFFFF\"><td class=\"beforebar\"><a href=\"pv.php?month={$date}\">{$date}</a></td><td><div class=\"bar1\" style=\"width:{$pv_width}px;\"><div class=\"bar2\" title=\"pv:{$stat['pv']}\"><div class=\"bar3\" style=\"width:{$ip_width}px;\" title=\"ip:{$stat['ip']}\"></div></div></div><div style=\"margin-left:10px\" class=\"mininum\">({$stat['ip']}/{$stat['pv']})(<a href=\"pv.php?action=itemrank&by={$date}\">{$lan['pvbyitem']}</a>)</div></td></tr>";
	}
} elseif($get_action == 'detail') {
	includecache('categories');
	list($y, $m, $d) = explode('-', $get_id);
	$time_start = mktime(0, 0, 0, $m, $d, $y);
	$time_end = mktime(0, 0, 0, $m, $d + 1, $y);
	empty($get_page) && $get_page = 1;
	$vpp = 50;
	$start = ($get_page - 1) * $vpp;
	$total = $db->get_field("SELECT COUNT(*) FROM {$tablepre}_visits WHERE dateline>='$time_start' AND dateline<'$time_end'");
	$index = '';
	for($i = 1; $i <= ceil($total / $vpp); $i ++) {
		$index .= "<a href=\"pv.php?action=detail&page={$i}&id={$get_id}\">$i</a>&nbsp;";
	}
	$query = $db->query("SELECT * FROM {$tablepre}_visits WHERE dateline>='$time_start' AND dateline<'$time_end' LIMIT $start, $vpp");
	$str_pvs .= "<tr>
		<td width=\"75\">{$lan['time']}</td>
		<td>{$lan['id']}</td>
		<td>{$lan['ip']}</td>
		<td>{$lan['visitpage']}</td>
		<td>{$lan['referer']}</td>
		<td>{$lan['se']}</td>
		<td>{$lan['keyword']}</td>
		</tr>";
	while($detail = $db->fetch_array($query)) {
		$dateline = date('H:i:s', $detail['dateline']);
		if($detail['type'] == 'category') {
			$url = isset($categories[$detail['itemid']]) ? $categories[$detail['itemid']]['category'] : '--已删除栏目--';
		} elseif($detail['type'] == 'item') {
			$url = "<a href=\"admincp.php?action=preview&id={$detail['itemid']}\">{$detail['itemid']}</a>";
		}
		if(strpos($detail['referer'], '//') !== false) {
			list(, $short_referer) = explode('//', $detail['referer']);
			$referer = "<a href=\"{$detail['referer']}\" target=\"_blank\">".substr($short_referer , 0, 50).'</a>';
		} else {
			$referer = '';
		}
		list($se, $keywords) = parse_se($detail['referer']);
		$str_pvs .= "<tr>
		<td width=\"75\">{$dateline}</td>
		<td><a href=\"pv.php?action=sid&sid={$detail['sid']}\">{$detail['sid']}</a></td>
		<td>{$detail['ip']}</td>
		<td>{$url}</td>
		<td>$referer</td>
		<td>$se</td>
		<td>$keywords</td>
		</tr>";
	}
	$str_pvs .= "<tr>
		<td colspan=\"10\">{$lan['recordnum']}:{$total}<br>$index</td>
		</tr>";
} elseif($get_action == 'sid') {
	includecache('categories');
	if(empty($get_sid)) aexit();
	$query = $db->query("SELECT * FROM {$tablepre}_visits WHERE sid='$get_sid'");
	$str_pvs .= "<tr>
		<td>{$lan['time']}</td>
		<td>{$lan['visitpage']}</td>
		<td>{$lan['referer']}</td>
		<td>{$lan['se']}</td>
		<td>{$lan['keyword']}</td>
		</tr>";
	while($detail = $db->fetch_array($query)) {
		!isset($ip) && $ip = $detail['ip'];
		$dateline = date('H:i:s', $detail['dateline']);
		if($detail['type'] == 'category') {
			$url = isset($categories[$detail['itemid']]) ? $categories[$detail['itemid']]['category'] : '';
		} elseif($detail['type'] == 'item') {
			$url = "<a href=\"admincp.php?action=preview&id={$detail['itemid']}\">{$detail['itemid']}</a>";
		}
		if(strpos($detail['referer'], '//') !== false) {
			list(, $short_referer) = explode('//', $detail['referer']);
			$referer = "<a href=\"{$detail['referer']}\" target=\"_blank\">".substr($short_referer , 0, 65).'</a>';
		} else {
			$referer = '';
		}
		list($se, $keywords) = parse_se($detail['referer']);
		$str_pvs .= "<tr><td>{$dateline}</td><td>{$url}</td><td>$referer</td><td>$se</td><td>$keywords</td></tr>";
	}
	$str_pvs .= "<tr><td colspan=\"10\">IP:{$ip}</td></tr>";
} elseif($get_action == 'itemrank') {
	includecache('categories');
	$str_pvs = '';
	$time = $get_by;
	switch(frequence($time, '-')) {
		case 1://月份
			list($year, $month) = explode('-', $time);
			$start = mktime(0, 0, 0, $month, 1, $year);
			$end = mktime(0, 0, 0, $month + 1, 1, $year);
			break;
		case 2://日期
			list($year, $month, $day) = explode('-', $time);
			$start = mktime(0, 0, 0, $month, $day, $year);
			$end = mktime(0, 0, 0, $month, $day + 1, $year);
			break;
	}
	$sql = "SELECT COUNT(*) as pv,itemid,type FROM {$tablepre}_visits WHERE dateline>=$start AND dateline<$end GROUP BY itemid ORDER BY pv DESC LIMIT 100";
	$query = $db->query($sql);
	$items = array();
	$itemids = array();
	$categoryids = array();
	while($item = $db->fetch_array($query)) {
		if(empty($item['itemid'])) continue;
		$items[$item['itemid']]['pv'] = $item['pv'];
		if($item['type'] == 'category') {
			$items[$item['itemid']]['type'] = $item['type'];
			$categoryids[] = $item['itemid'];
		} elseif($item['type'] == 'item') {
			$items[$item['itemid']]['type'] = $item['type'];
			$itemids[] = $item['itemid'];
		}
	}
	$ids = implode(',', $itemids);
	$sql = "SELECT * FROM {$tablepre}_items WHERE id IN ($ids)";
	$query = $db->query($sql);
	while($item = $db->fetch_array($query)) {
		$items[$item['id']]['title'] = $item['title'];
		$items[$item['id']]['editor'] = $item['editor'];
		$items[$item['id']]['dateline'] = $item['dateline'];
		$items[$item['id']]['totalpv'] = $item['pageview'];
		$items[$item['id']]['category'] = $item['category'];
	}
	if(!empty($categoryids)) {
		$ids = implode(',', $categoryids);
		$sql = "SELECT * FROM {$tablepre}_categories WHERE id IN ($ids)";
		$query = $db->query($sql);
		while($category = $db->fetch_array($query)) {
			$items[$category['id']]['category'] = $category['category'];
			$items[$category['id']]['totalpv'] = $category['pv'];
		}
	}
	$str_pvs .= "<tr><td></td><td>{$lan['pageview']}</td><td>{$lan['totalpageview']}</td><td>{$lan['category']}</td><td>{$lan['title']}</td><td>{$lan['editor']}</td><td>{$lan['time']}</td></tr>\n";
	$i = 1;
	foreach($items as $id => $item) {
		if($item['type'] == 'item') {
			if($item['category'] == 0) {
				$category = '';
			} else {
				$category = $categories[$item['category']]['category'];
			}
			$str_pvs .= "<tr><td>{$i}</td><td>{$item['pv']}</td><td>{$item['totalpv']}</td><td>".$category."</td><td>{$item['title']}&nbsp;[<a href=\"admincp.php?action=preview&id={$id}\" target=\"_blank\">{$lan['preview']}</a>]&nbsp;[<a href=\"admincp.php?action=edititem&id={$id}\" target=\"_blank\">{$lan['edit']}</a>]</td><td>{$item['editor']}</td><td>".date('Y-m-d', $item['dateline'])."</td></tr>";
		} else {
			$str_pvs .= "<tr><td>{$i}</td><td>{$item['pv']}</td><td>{$item['totalpv']}</td><td>{$item['category']}</td><td></td><td></td><td></td></tr>";
		}
		$i ++;
	}
}
$smarty->assign('pvs', $str_pvs);
displaytemplate('admincp_pvs.htm');
aexit();
?>
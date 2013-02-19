<?php
function getdiscuzthreads($params) {
	global $thetime;
	$datas = array();
	$params = operatediscuzthreadsparams($params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getthreadsdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getthreadsdata($params) {//ok
	global $cache_memory, $db;
	$db = db();
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	includecache('categories');

	$sql = getthreadsql($params);
	$threads = $db->querytoarray($sql);
	$j = 0;//序号
	$datas = array();
	foreach($threads as $thread) {
		$j ++;
		list($thread['y'], $thread['m'], $thread['d'], $thread['h'], $thread['i'], $thread['s'], $thread['sy'], $thread['sm'], $thread['sd']) = explode(' ', date('Y m d H i s y n j', $thread['time']));
		list($thread['last_y'], $thread['last_m'], $thread['last_d'], $thread['last_h'], $thread['last_i'], $thread['last_s'], $thread['last_sy'], $thread['last_sm'], $thread['last_sd']) = explode(' ', date('Y m d H i s y n j', $thread['lastpost']));
		if($params['length'] != 0) {
			$thread['title'] = ak_substr($thread['title'], 0, $params['length'], $params['strip']);
		}
		$thread['id'] = $j;
		$datas[] = $thread;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getthreadsql($params) {//ok
	$discuztablepre = 'cdb_';
	$tablename = $discuztablepre.'threads';
	$fields['fid'] = 'fid';
	$fields['digest'] = 'digest';
	$fields['tid'] = 'tid';
	$fields['title'] = 'subject';
	$fields['pv'] = 'views';
	$fields['uid'] = 'authorid';
	$fields['user'] = 'author';
	$fields['replies'] = 'replies';
	$fields['time'] = 'dateline';
	$fields['lastpost'] = 'lastpost';
	$undeleted = 'displayorder >= 0';
	$select_field = '';
	foreach($fields as $key => $field) {
		$select_field .= "`$field` as `$key`,";
	}
	$select_field = substr($select_field, 0, -1);
	$sql_where = '1';
	if(!empty($params['forum'])) {
		if(strpos($params['forum'], ',') === false) {
			$sql_where .= " AND {$fields['fid']} = '{$params['forum']}'";
		} else {
			$sql_where .= " AND {$fields['fid']} IN ({$params['forum']})";
		}
	} elseif(!empty($params['skipforum'])) {
		if(strpos($params['skipforum'], ',') === false) {
			$sql_where .= " AND {$fields['fid']} <> '{$params['skipforum']}'";
		} else {
			$sql_where .= " AND {$fields['fid']} NOT IN ({$params['skipforum']})";
		}
	}
	if($params['replyinseconds'] > 0) {
		$timepoint = $thetime - intval($params['replyinseconds']);
		$sql_where .= " AND {$fields['lastpost']}>'{$timepoint}'";
	}
	if(!empty($params['id'])) {
		$ids = tidyitemlist($params['id']);
		$array_ids = explode(',', $ids);
		if(count($array_ids) > 1) {
			$sql_where .= " AND id IN ($ids)";
		} else {
			$sql_where .= " AND id = '$ids'";
		}
	}
	if(!empty($params['digest'])) {
		$sql_where .= " AND `digest`>='{$params['digest']}'";
	}
	if(!empty($params['skip'])) {
		$skip = tidyitemlist($params['skip']);
		$sql_where .= " AND id NOT IN ($skip)";
	}
	$sqlorderby = operatethreadsorder($params['orderby'], 'threads');
	$params['start'] = $params['start'] - 1;
	return "SELECT $select_field FROM $tablename WHERE {$sql_where} AND {$undeleted} ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
}

function operatediscuzthreadsparams($params) {
	global $lr;
	$start = isset($params['start']) && a_is_int($params['start']) ? $params['start'] : 1;
	$num = empty($params['num']) ? 10 : intval($params['num']);
	$colspan = !empty($params['colspan']) && a_is_int($params['colspan']) ? $params['colspan'] : 0;//循环几次后插入修正符
	$overflow = empty($params['overflow']) ? '' : $params['overflow'];//修正符
	$expire = !empty($params['expire']) && a_is_int($params['expire']) ? $params['expire'] : 0;//缓存有效期，单位秒
	$length = !empty($params['length']) && a_is_int($params['length']) ? $params['length'] : 0;//长度限制
	$strip = empty($params['strip']) ? '' : $params['strip'];//超出长度限制后显示的字符
	$orderby = empty($params['orderby']) ? '' : tidyitemlist($params['orderby'], ',', 0);
	$bandindex = empty($params['bandindex']) ? 0 : 1;
	$emptymessage = empty($params['emptymessage']) ? '' : $params['emptymessage'];
	$return = array(
		'start' => $start,
		'num' => $num,
		'colspan' => $colspan,
		'overflow' => $overflow,
		'expire' => $expire,
		'type' => $type,
		'length' => $length,
		'strip' => $strip,
		'orderby' => $orderby,
		'bandindex' => $bandindex,
		'emptymessage' => $emptymessage,
	);
	
	$return['newinseconds'] = !empty($params['newinseconds']) && a_is_int($params['newinseconds']) ? $params['newinseconds'] : 0;//最近几秒新增
	$return['replyinseconds'] = !empty($params['replyinseconds']) && a_is_int($params['replyinseconds']) ? $params['replyinseconds'] : 0;//最近几秒修改
	$template = empty($params['template']) ? '[title]<br>' : $params['template'];
	$return['forum'] = empty($params['forum']) ? '' : tidyitemlist($params['forum']);
	$return['skipforum'] = empty($params['skipforum']) ? '' : tidyitemlist($params['skipforum']);
	$return['id'] = empty($params['id']) ? '' : tidyitemlist($params['id']);
	$return['skipid'] = empty($params['skipid']) ? '' : tidyitemlist($params['skipid']);
	$return['digest'] = empty($params['digest']) ? 0 : $params['digest'];
	$return['top'] = empty($params['top']) ? 0 : $params['top'];
	$return['uid'] = empty($params['uid']) ? 0 : $params['uid'];
	$return['orderby'] = empty($return['orderby']) ? 'tid' : $return['orderby'];
	
	$template = ak_replace('()', '"', $template);
	$template = ak_replace('[lr]', $lr, $template);
	$return['template'] = $template;

	$key = ak_md5(serialize($return), 1);
	$return['cachekey'] = $key;
	return $return;
}

function operatethreadsorder($rule) {
	$arrayname = array(
		'tid' => 'tid',
		'views' => 'pv',
		'replies' => 'replies',
		'time' => 'time',//?
		'lastpost' => 'lastpost'
	);
	$rules = explode(',', $rule);
	$return = '';
	foreach($rules as $rule) {
		$array_temp = explode('_', $rule);
		if(!isset($array_temp[0]) || !array_key_exists($array_temp[0], $arrayname)) {
			continue;
		}
		if(isset($array_temp[1]) && $array_temp[1] == 'reverse') {
			$return .= ','.$arrayname[$array_temp[0]].' DESC';
		} else {
			$return .= ','.$arrayname[$array_temp[0]];
		}
	}
	return substr($return, 1);
}
?>
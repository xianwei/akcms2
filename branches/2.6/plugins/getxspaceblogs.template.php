<?php
function getxspaceblogs($params) {
	global $thetime;
	$datas = array();
	$params = operatexspaceblogsparams($params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getxspaceblogsdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getxspaceblogsdata($params) {//ok
	global $db;
	$db = db();
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	
	$sql = getxspaceblogsql($params);
	$query = $db->query($sql);
	$datas = array();
	while($blog = $db->fetch_array($query)) {
		list($blog['y'], $blog['m'], $blog['d'], $blog['h'], $blog['i'], $blog['s'], $blog['sy'], $blog['sm'], $blog['sd']) = explode(' ', date('Y m d H i s y n j', $blog['time']));
		if($blog['lastpost'] == 0) $blog['lastpost'] = $blog['time'];
		list($blog['last_y'], $blog['last_m'], $blog['last_d'], $blog['last_h'], $blog['last_i'], $blog['last_s'], $blog['last_sy'], $blog['last_sm'], $blog['last_sd']) = explode(' ', date('Y m d H i s y n j', $blog['lastpost']));
		if($params['length'] != 0) {
			$blog['title'] = ak_substr($blog['title'], 0, $params['length'], $params['strip']);
		}
		$blogs[] = $blog;
	}
	$j = 0;
	$datas = array();
	foreach($blogs as $blog) {
		$j ++;
		$blog['id'] = $j;
		$datas[] = $blog;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getxspaceblogsql($params) {
	global $db;
	$tablename = 'supe_spaceitems';//写死
	$fields['username'] = 'username';
	$fields['itemid'] = 'itemid';
	$fields['uid'] = 'uid';
	$fields['pv'] = 'viewnum';
	$fields['replynum'] = 'replynum';
	$fields['goodrate'] = 'goodrate';
	$fields['badrate'] = 'badrate';
	$fields['time'] = 'dateline';
	$fields['lastpost'] = 'lastpost';
	$fields['title'] = 'subject';
	$fields['digest'] = 'digest';

	$select_field = '';
	foreach($fields as $key => $field) {
		$select_field .= "`$field` as `$key`,";
	}
	$select_field = substr($select_field, 0, -1);
	$sql_where = '1';
	if(!empty($params['id'])) {
		$ids = tidyitemlist($params['id']);
		$array_ids = explode(',', $ids);
		if(count($array_ids) > 1) {
			$sql_where .= " AND {$fields['uid']} IN ($ids)";
		} else {
			$sql_where .= " AND {$fields['uid']} = '$ids'";
		}
	}
	if(!empty($params['digest'])) {
		$sql_where .= " AND {$fields['digest']} > 0";
	}
	$params['start'] = $params['start'] - 1;
	$sqlorderby = operatexspaceblogsorder($params['orderby']);
	return "SELECT $select_field FROM $tablename WHERE {$sql_where} AND type='blog' ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
}

function operatexspaceblogsparams($params) {
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
	
	$template = empty($params['template']) ? '[title]<br>' : $params['template'];
	$return['orderby'] = empty($return['orderby']) ? 'id' : $return['orderby'];
	$return['digest'] = empty($params['digest']) ? 0 : $params['digest'];
	
	$template = ak_replace('()', '"', $template);
	$template = ak_replace('[lr]', $lr, $template);
	$return['template'] = $template;

	$key = ak_md5(serialize($return), 1);
	$return['cachekey'] = $key;
	return $return;
}

function operatexspaceblogsorder($rule) {
	$arrayname = array(
		'id' => 'itemid',
		'lastpost' => 'lastpost',
		'dateline' => 'dateline',
		'username' => 'username',
		'pv' => 'viewnum',
		'replaynum' => 'replaynum'
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
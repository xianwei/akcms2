<?php
function getitems($params) {
	global $cache_memory;
	$datas = array();
	$params = operateparams('items', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getitemsdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getitemsdata($params) {
	global $db, $itemexts, $tablepre, $categories, $sections, $homepage;
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	includecache('categories');
	includecache('sections');
	
	$sql = getitemsql($params);
	$query = $db->query($sql);
	if($params['bandindex'] == 1) {
		global $global_total, $global_ipp;
		$sql = str_replace('*', 'count(id)', $sql);
		$_limitpos = strpos($sql, 'LIMIT');
		$sql = substr($sql, 0, $_limitpos);
		$global_total = $db->get_field($sql);
		$global_ipp = $params['num'];
	}
	$items = array();
	while($item = $db->fetch_array($query)) {
		$items[$item['id']] = $item;
	}
	$itemexts = extfields();
	if(!empty($itemexts)) {
		$ids = array();
		foreach($items as $item) {
			$ids[] = $item['id'];
		}
		if(!empty($ids)) {
			$_ids = implode(',', $ids);
			$query = $db->list_by('*', 'item_exts', "id IN ($_ids)");
			while($ext = $db->fetch_array($query)) {
				$values = unserialize($ext['value']);
				if(is_array($values)) {
					foreach($values as $key => $value) {
						$items[$ext['id']][$key] = $value;
					}
				}
			}
		}
	}
	$j = 0;
	$datas = array();
	foreach($items as $item) {
		$j ++;
		list($item['y'], $item['m'], $item['d'], $item['h'], $item['i'], $item['s'], $item['sy'], $item['sm'], $item['sd'], $item['F'], $item['M'], $item['l'], $item['D'], $item['r']) = explode(' ', date('Y m d H i s y n j F M l D r', $item['dateline']));
		if($item['lastupdate'] == 0) $item['lastupdate'] = $item['dateline'];
		list($item['last_y'], $item['last_m'], $item['last_d'], $item['last_h'], $item['last_i'], $item['last_s'], $item['last_sy'], $item['last_sm'], $item['last_sd']) = explode(' ', date('Y m d H i s y n j', $item['lastupdate']));
		$item['url'] = htmlurl($item['id'], $item['category'], $item['dateline'], $item['filename']);
		$_texttitle = $item['texttitle'] = $item['title'];
		$_textshorttitle = $item['textshorttitle'] = empty($item['shorttitle']) ? $item['texttitle'] : $item['shorttitle'];
		if($params['length'] != 0) {
			$_texttitle = ak_substr($item['texttitle'], 0, $params['length'], $params['strip']);
			$_textshorttitle = ak_substr($item['textshorttitle'], 0, $params['length'], $params['strip']);
		}
		$item['title'] = htmltitle($_texttitle, $item['titlecolor'], $item['titlestyle']);
		$item['shorttitle'] = htmltitle($_textshorttitle, $item['titlecolor'], $item['titlestyle']);
		$item['texttitle'] = $_texttitle;
		$item['textshorttitle'] = $_textshorttitle;
		$item['pv'] = $item['pageview'];
		$item['sectionid'] = $item['section'];
		$item['section'] = $sections[$item['sectionid']]['section'];
		$item['categoryid'] = $item['category'];
		$item['category'] = $categories[$item['categoryid']]['category'];
		$item['categorypath'] = $categories[$item['categoryid']]['path'];
		$item['categoryhomepath'] = get_category_path($item['categoryid']);
		$item['categoryup'] = $categories[$item['categoryid']]['categoryup'];
		$item['itemid'] = $item['id'];
		$item['id'] = $j;
		if(!empty($item['picture']) && substr($item['picture'], 0, 7) !== 'http://') {
			$item['picture'] = $homepage.$item['picture'];
		}
		if(empty($item['picture'])) $item['picture'] = $params['nopicture'];
		if(!empty($params['show_text'])) {
			$item['text'] = $db->get_field("SELECT text FROM {$tablepre}_texts WHERE itemid='{$item['itemid']}'");
		} else {
			$item['text'] = '';
		}
		$datas[] = $item;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getitemsql($params) {
	global $tablepre, $db, $thetime;
	$sql_where = '1';
	if(!empty($params['category'])) {
		if($params['includesubcategory']) {
			$categories = includesubcategories($params['category']);
		} else {
			$categories = $params['category'];
		}
		$sql_where .= " AND category IN ({$categories})";
	} elseif(!empty($params['skipcategory'])) {
		if($params['includesubcategory']) {
			$skipcategories = includesubcategories($params['skipcategory']);
		} else {
			$skipcategories = $params['skipcategory'];
		}
		$sql_where .= " AND category NOT IN ({$skipcategories})";
	} else {
		$sql_where .= " AND category<>0";
	}
	if(!empty($params['section'])) {
		$sql_where .= " AND section IN ({$params['section']})";
	} elseif(!empty($params['skipsection'])) {
		$sql_where .= " AND section NOT IN ({$params['skipsection']})";
	}
	if(!empty($params['id'])) {
		$sql_where .= " AND id IN ({$params['id']})";
	} elseif(!empty($params['skip'])) {
		$sql_where .= " AND id NOT IN ({$params['skip']})";
	}
	if($params['editinseconds'] != 0) {
		$timepoint = $thetime - intval($params['editinseconds']);
		$sql_where .= " AND lastupdate>'{$timepoint}'";
	}
	if($params['newinseconds'] != 0) {
		$timepoint = $thetime - intval($params['newinseconds']);
		$sql_where .= " AND dateline>'{$timepoint}'";
	}
	if($params['last'] != 0) {
		$sql_where .= " AND id>'{$params['last']}'";
	}
	if($params['next'] != 0) {
		$sql_where .= " AND id<'{$params['next']}'";
	}
	if(!empty($params['order'])) $sql_where .= " AND orderby>='{$params['order']}'";
	if(!empty($params['orderby2'])) $sql_where .= " AND orderby2='{$params['orderby2']}'";
	if(!empty($params['orderby3'])) $sql_where .= " AND orderby3='{$params['orderby3']}'";
	if(!empty($params['orderby4'])) $sql_where .= " AND orderby4='{$params['orderby4']}'";
	if($params['picture'] == 1) {
		$sql_where .= " AND picture<>''";
	} elseif($params['picture'] == -1) {
		$sql_where .= " AND picture=''";
	}
	if(!empty($params['author'])) {
		$sql_where .= " AND author='{$params['author']}'";
	}
	if(!empty($params['keywords'])) {
		$array_keywords = explode(',', $params['keywords']);
		$sql_keywords = '0';
		foreach($array_keywords as $keyword) {
			if(!empty($keyword)) {
				$sql_keywords .= " OR title LIKE '%{$keyword}%' OR keywords LIKE '%{$keyword}%'";
			}
		}
		$sql_where .= " AND ($sql_keywords)";
	}
	if($params['timelimit'] == 1) $sql_where .= " AND dateline < '$thetime'";
	if(!empty($params['where'])) $sql_where .= " AND ".$params['where'];
	$sqlorderby = order_operate($params['orderby'], 'items');
	if($sqlorderby == 'random') {
		$sql = "SELECT id FROM {$tablepre}_items WHERE {$sql_where} ORDER BY rand() LIMIT 0,{$params['num']}";
		$query = $db->query($sql);
		$inids = array();
		while($id = $db->fetch_array($query)) {
			$inids[] = $id['id'];
		}
		if(!empty($inids)) {
			$inids = implode(',', $inids);
			$sql_where = " id IN ($inids)";
			$params['sqlorderby'] = '';
		} else {
			$sql_where = '0';
		}
		$sqlorderby = '';
	} elseif($sqlorderby != '') {
		$sqlorderby = 'ORDER BY '.$sqlorderby;
	} else {
		$sqlorderby = '';
	}
	$params['start'] = $params['start'] - 1;
	if($params['start'] < 0) $params['start'] = 0;
	$sql = "SELECT * FROM {$tablepre}_items WHERE {$sql_where} {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
	return $sql;
}

function getcategories($params) {//ok
	global $cache_memory;
	$params = operateparams('categories', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getcategoriesdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getcategoriesdata($params) {
	global $db, $categories;
	includecache('categories');
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	if(!empty($params['childcategory'])) {
		$_c = $params['childcategory'];
		while($_c != 0) {
			$_categories[] = $categories[$_c];
			$_c = $categories[$_c]['categoryup'];
		}
		$_categories = array_reverse($_categories);
	} else {
		$sql = getcategoriessql($params);
		$_categories = $db->querytoarray($sql);
	}
	$j = 0;//序号
	$datas = array();
	foreach($_categories as $category) {
		$j ++;
		$category['url'] = getcategoryurl($category['id']);
		$category['categoryid'] = $category['id'];
		$category['categoryuppath'] = $categories[$category['categoryup']]['path'];
		$category['id'] = $j;
		$datas[] = $category;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getcategoriessql($params) {//ok
	global $tablepre;
	$sql_where = '1';
	if(!empty($params['rootcategory'])) {
		$sql_where .= " AND categoryup ='{$params['rootcategory']}'";
	}
	if(!empty($params['skipsub'])) {
		$sql_where .= " AND categoryup ='0'";
	}
	if(!empty($params['id'])) {
		if(strpos($params['id'], ',') === false) {
			$sql_where .= " AND id = '{$params['id']}'";
		} else {
			$sql_where .= " AND id IN ({$params['id']})";
		}
	}
	if(!empty($params['skipid'])) {
		$sql_where .= " AND id NOT IN ({$params['skipid']})";
	}
	$sql_where .= " AND id <> -1";
	$params['start'] = $params['start'] - 1;
	$sqlorderby = order_operate($params['orderby'], 'categories');
	return "SELECT * FROM {$tablepre}_categories WHERE {$sql_where} ORDER BY {$sqlorderby} LIMIT {$params['start']}{$params['num']}";
}

function getcomments($params) {
	global $cache_memory;
	$params = operateparams('comments', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getcommentsdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getcommentsdata($params) {
	global $db;
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	$sql = getcommentssql($params);
	$comments = $db->querytoarray($sql);
	$j = 0;
	$datas = array();
	foreach($comments as $comment) {
		$j ++;
		$comment['id'] = $j;
		$comment['secretip'] = encodeip($comment['ip']);
		$comment['message'] = htmlspecialchars($comment['message']);
		$comment['titie'] = htmlspecialchars($comment['title']);
		$comment['username'] = htmlspecialchars($comment['username']);
		list($comment['y'], $comment['m'], $comment['d'], $comment['h'], $comment['i'], $comment['s'], $comment['sy'], $comment['sm'], $comment['sd']) = explode(' ', date('Y m d H i s y n j', $comment['dateline']));
		$datas[] = $comment;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getcommentssql($params) {
	global $tablepre;
	$field = array();
	$sql_where = '1';
	if(!empty($params['itemid'])) {
		$sql_where .= " AND itemid = '{$params['itemid']}'";
	}
	$sqlorderby = order_operate($params['orderby'], 'comments');
	$params['start'] = $params['start'] - 1;
	return "SELECT itemid,username,title,message,dateline,ip FROM {$tablepre}_comments WHERE {$sql_where} ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
}

function getlists($params) {
	global $cache_memory;
	$datas = array();
	$params = operateparams('lists', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getlistsdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getlistsdata($params) {
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	$lists = explode($params['sc'], $params['list']);
	$j = 0;
	$datas = array();
	foreach($lists as $list) {
		$j ++;
		if($params['num'] > 0 && $j > $params['num']) break;
		$item['item'] = $list;
		$item['iteminurl'] = urlencode($list);
		$item['iteminhtml'] = htmlspecialchars($list);
		$item['id'] = $j;
		$datas[] = $item;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function renderdata($data, $options) {
	//$data是一个二维数组，所有要渲染的数据都在这里，$options是一个一维配置选项数组，包括，模板，overflow，colspan都在这个数组中
	$html = '';
	$array_templates = array();
	$_array = array();
	foreach($data as $value) {
		$_array = array_merge($_array, $value);
	}
	if(count($data) > 0) $keys = array_keys($_array);
	if(count($keys) == 0) return $options['emptymessage'];
	foreach($keys as $key) {
		$array_templates[$key] = "[$key]";
	}
	$template = $options['template'];
	$i = 0;
	foreach($data as $id => $record) {
		$html .= ak_array_replace($array_templates, $record, $template);
		$i ++;
		if(isset($options['colspan']) && $options['colspan'] > 0) {
			if($i % $options['colspan'] == 0 && isset($data[$id + 1])) $html .= $options['overflow'];
		}
	}
	$html = str_replace('[n]', "\n", $html);
	$html = str_replace('()', '"', $html);
	return $html;
}

function operateparams($type, $params) {
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
	if($type == 'items') {
		$return['newinseconds'] = !empty($params['newinseconds']) && a_is_int($params['newinseconds']) ? $params['newinseconds'] : 0;//最近几秒新增
		$return['editinseconds'] = !empty($params['editinseconds']) && a_is_int($params['editinseconds']) ? $params['editinseconds'] : 0;//最近几秒修改
		$return['section'] = empty($params['section']) ? '' : tidyitemlist($params['section']);
		$return['skipsection'] = empty($params['skipsection']) ? '' : tidyitemlist($params['skipsection']);
		$return['category'] = empty($params['category']) ? '' : tidyitemlist($params['category']);
		$return['skipcategory'] = empty($params['skipcategory']) ? '' : tidyitemlist($params['skipcategory'].',0,');
		if($return['category'] == '' && $return['skipcategory'] == '') $return['skipcategory'] = '-1';
		$return['id'] = empty($params['id']) ? '' : tidyitemlist($params['id']);
		$return['skip'] = empty($params['skip']) ? '' : tidyitemlist($params['skip']);
		$return['timelimit'] = !empty($params['timelimit']) ? 1 : 0;
		$return['includesubcategory'] = empty($params['includesubcategory']) ? 0 : 1;//是否包含下级分类，默认不包含
		$template = empty($params['template']) ? '[title]<br>' : $params['template'];
		$return['show_text'] = strpos($template, '[text]') !== false ? 1 : 0;
		$return['order'] = empty($params['order']) ? 0 : $params['order'];
		$return['keywords'] = isset($params['keywords']) ? $params['keywords'] : '';
		$return['picture'] = isset($params['picture']) ? $params['picture'] : 0;//>0带图<0不带图0随便
		$return['orderby'] = empty($return['orderby']) ? 'id' : $return['orderby'];
		$return['nopicture'] = empty($params['nopicture']) ? '' : $params['nopicture'];
		$return['last'] = empty($params['last']) ? 0 : $params['last'];
		$return['next'] = empty($params['next']) ? 0 : $params['next'];
		$return['where'] = empty($params['where']) ? '' : $params['where'];
		if(isset($params['page']) && a_is_int($params['page']) && $params['page'] > 0) $return['start'] =  ($params['page'] - 1) * $num + 1;
	} elseif($type == 'threads') {
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
	} elseif($type == 'messages') {
		$return['tid'] = empty($params['tid']) ? 0 : $params['tid'];
		$template = empty($params['template']) ? '[message]<br>' : $params['template'];
		$return['orderby'] = empty($return['orderby']) ? 'mid' : $return['orderby'];
	} elseif($type == 'xspaceblogers') {
		$template = empty($params['template']) ? '[username]<br>' : $params['template'];
		$return['orderby'] = empty($return['orderby']) ? 'id' : $return['orderby'];
	} elseif($type == 'xspaceblogs') {
		$template = empty($params['template']) ? '[title]<br>' : $params['template'];
		$return['orderby'] = empty($return['orderby']) ? 'id' : $return['orderby'];
		$return['digest'] = empty($params['digest']) ? 0 : $params['digest'];
	} elseif($type == 'index') {
		$return['page'] = empty($params['page']) || !a_is_int($params['page']) ? 1 : $params['page'];
		global $global_ipp, $global_total, $global_category;
		$return['ipp'] = isset($global_ipp) ? $global_ipp : 10;
		isset($params['ipp']) && $return['ipp'] = $params['ipp'];
		$return['total'] = isset($global_total) ? $global_total : 0;
		isset($params['total']) && $return['total'] = $params['total'];
		$return['keywords'] = empty($params['keywords']) ? '' : $params['keywords'];
		$return['category'] = empty($params['category']) ? '' : $params['category'];
		$baseurl = empty($params['baseurl']) ? '' : $params['baseurl'];
		if(isset($global_category)) $baseurl = str_replace('[category]', urlencode($global_category), $baseurl);
		$baseurl = str_replace('[categoryid]', urlencode($return['categoryid']), $baseurl);
		$return['baseurl'] = $baseurl;
		$template = empty($params['template']) ? '[indexs]' : $params['template'];
		$return['linktemplate'] = empty($params['linktemplate']) ? '[link]' : $params['linktemplate'];
	} elseif($type == 'categories') {
		$template = empty($params['template']) ? '[category]&nbsp;' : $params['template'];
		$return['rootcategory'] = isset($params['rootcategory']) && a_is_int($params['rootcategory']) ? $params['rootcategory'] : 0;
		$return['childcategory'] = isset($params['childcategory']) && a_is_int($params['childcategory']) ? $params['childcategory'] : 0;
		$return['id'] = isset($params['id']) ? tidyitemlist($params['id']) : '';
		$return['skipid'] = isset($params['skipid']) ? tidyitemlist($params['skipid']) : '';
		$return['skipsub'] = empty($params['skipsub']) ? 0 : 1;
		$return['orderby'] = empty($return['orderby']) ? 'id' : $return['orderby'];
	} elseif($type == 'comments') {
		$template = empty($params['template']) ? '[message]<br>' : $params['template'];
		$return['itemid'] = empty($params['itemid']) || !a_is_int($params['itemid']) ? 0 : $params['itemid'];
		$return['orderby'] = empty($return['orderby']) ? 'id_reverse' : $return['orderby'];
	} elseif($type == 'lists') {
		$template = empty($params['template']) ? '[item]<br>' : $params['template'];
		$return['sc'] = empty($params['sc']) ? ',' : $params['sc'];
		$return['list'] = empty($params['list']) ? '' : $params['list'];
		$return['num'] = empty($params['num']) ? -1 : $params['num'];
	}
	$template = ak_replace('()', '"', $template);
	$template = ak_replace('[lr]', $lr, $template);
	$return['template'] = $template;

	$key = ak_md5(serialize($return), 1);
	$return['cachekey'] = $key;
	return $return;
}

function order_operate($rule, $type = 'items') {
	if(!in_array($type, array('items', 'categories', 'comments'))) {
		return '';
	}
	if(strpos($rule, 'random') !== false) return 'random';
	$array_items_field = array(
		'id' => 'id',
		'orderby' => 'orderby',
		'orderby2' => 'orderby2',
		'orderby3' => 'orderby3',
		'orderby4' => 'orderby4',
		'time' => 'dateline',
		'update' => 'lastupdate',
		'pv' => 'pageview',
		'title' => 'title',
	);
	$array_categories_field = array(
		'orderby' => 'orderby',
		'id' => 'id'
	);
	$array_comments_field = array(
		'id' => 'id',
		'time' => 'dateline',
		'goodnum' => 'goodnum',
		'badnum' => 'badnum'
	);
	$arrayname = "array_{$type}_field";
	$arrayname = $$arrayname;
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

function getindexs($params) {
	global $exe_times, $db, $tablepre, $categories, $lr, $thetime, $cache_memory, $itemexts;
	includecache('categories');
	$datas = array();
	$params = operateparams('index', $params);
	$datas[0]['last'] = ceil($params['total'] / $params['ipp']);
	if($datas[0]['last'] == 1) return '';
	$_indexs = '';
	$_start = max($params['page'] - 3, 1);
	$_end = min($_start + 9, ceil($params['total'] / $params['ipp']));
	if($_end == 0) return '';
	for($i = $_start; $i <= $_end; $i ++) {
		$_url = str_replace('[page]', $i, $params['baseurl']);
		foreach($_GET as $key => $value) {
			$_url = str_replace("[$key]", rawurlencode($value), $_url);
		}
		if($params['page'] == $i) {
			$_indexs .= str_replace('[link]', "<span id=\"current\">$i</span>", $params['linktemplate']);
		} else {
			$_indexs .= str_replace('[link]', "<a href=\"{$_url}\">$i</a>", $params['linktemplate']);
		}
	}
	$datas[0]['indexs'] = $_indexs;
	$html = renderdata($datas, $params);
	echo $html;
}

function setcachedata($params, $data) {//ok 2008-05-22 22:20
	$cachefile = getcachefile($params);
	writetofile(serialize($data), $cachefile);
}

function getcachedata($params) {//ok 2008-5-16 22:23
	global $thetime;
	if($params['expire'] <= 0) return '';
	$cachefile = getcachefile($params);
	if(is_readable($cachefile)) {
		if($thetime - ak_filetime($cachefile) > $params['expire']) {
			touch($cachefile);
			return '';
		}
		$data = unserialize(readfromfile($cachefile));
		if($data == false) return '';
		return $data;
	} else {
		return '';
	}
}

function getcachefile($params) {//ok 2008-05-22 22:20
	$key = ak_md5(serialize($params), 1);
	return AK_ROOT.'./cache/'.$params['type'].'/'.$key;
}
?>
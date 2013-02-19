<?php
//getdata系列函数
//1 getblogs 
//2 getblogers 
//3 getitems ---ok
//4 getcategories ---ok
//5 getthreads ---ok
//6 getmessages ---ok
//7 getbbsusers ---ok
//8 getinfo（下期）
//9 getrss（下期）
//10 getlists

function getitems($params) {//ok 2008-05-17 0:49
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

function getitemsdata($params) {//ok 2008-05-17 0:49
	global $db, $itemexts, $tablepre, $categories, $sections;
	$db = db();
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
	includecache('itemexts');
	if(!empty($itemexts)) {
		$ids = array();
		foreach($items as $item) {
			$ids[] = $item['id'];
		}
		if(!empty($ids)) {
			$_ids = implode(',', $ids);
			$sql = "SELECT * FROM {$tablepre}_item_exts WHERE id IN ($_ids)";
			$query = $db->query($sql);
			while($ext = $db->fetch_array($query)) {
				foreach($ext as $key => $value) {
					if($key == 'id' || $key == 'type') continue;
					$items[$ext['id']][$key] = $value;
				}
			}
			foreach($ids as $id) {
				foreach($itemexts as $e) {
					if($e['Field'] == 'type' || $e['Field'] == 'id') continue;
					if(!isset($items[$id][$e['Field']])) $items[$id][$e['Field']] = '';
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
		$item['categoryup'] = $categories[$item['categoryid']]['categoryup'];
		$item['itemid'] = $item['id'];
		$item['id'] = $j;
		$item['rootpicture'] = $item['picture'];

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

function getitemsql($params) {//ok2008-5-16 22:19
	global $tablepre, $db, $thetime;
	$db = db();
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
	if($params['picture'] == 1) {
		$sql_where .= " AND picture<>''";
	} elseif($params['picture'] == -1) {
		$sql_where .= " AND picture=''";
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

function getbbsusers($params) {//ok 2008-05-18 21:58
	//参数group,skipgroup,ids,skipids
	//模板uid,username,headpic,gender,regip,lastip,reg_y......reg_s,lastv_y......lastv_s,lasta_y......lastv_s,lastp_y......lastp_s,onlinetime,posts,credits,point1,point2,point3,point4,email
	global $thetime;
	confirmbbsconfig(1);
	$datas = array();
	$params = operateparams('bbsusers', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getbbsusersdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getbbsusersdata($params) {//ok 2008-5-19 19:43
	global $db;
	$db = db();
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	$sql = getbbsuserssql($params);
	$users = $db->querytoarray($sql);
	$j = 0;//序号
	$datas = array();
	foreach($users as $user) {
		$j ++;
		list($user['reg_y'], $user['reg_m'], $user['reg_d'], $user['reg_h'], $user['reg_i'], $user['reg_s'], $user['reg_sy'], $user['reg_sm'], $user['reg_sd']) = explode(' ', date('Y m d H i s y n j', $user['regdate']));
		list($user['lastv_y'], $user['lastv_m'], $user['lastv_d'], $user['lastv_h'], $user['lastv_i'], $user['lastv_s'], $user['lastv_sy'], $user['lastv_sm'], $user['lastv_sd']) = explode(' ', date('Y m d H i s y n j', $user['lastvisit']));
		list($user['lastp_y'], $user['lastp_m'], $user['lastp_d'], $user['lastp_h'], $user['lastp_i'], $user['lastp_s'], $user['lastp_sy'], $user['lastp_sm'], $user['lastp_sd']) = explode(' ', date('Y m d H i s y n j', $user['lastpost']));
		if($params['length'] != 0) {
			$user['username'] = ak_substr($user['username'], 0, $params['length'], $params['strip']);
		}
		$user['uid'] = $user['uid'];
		$user['id'] = $j;
		$datas[] = $user;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getbbsuserssql($params) {//ok 2008-5-19 19:40
	global $setting_bbstablepre, $setting_bbstype, $thetime;
	$sql_where = '1';
	if($setting_bbstype == 'discuz') {
		$group_field = 'groupid';
		$id_field = 'uid';
		$select = ' *';
	}//phpwind暂不支持
	if(!empty($params['skipgroup'])) {
		$sql_where .= " AND {$group_field} NOT IN ({$params['skipgroup']})";
	} elseif(!empty($params['group'])) {
		$sql_where .= " AND {$group_field} IN ({$params['group']})";
	}
	if(!empty($params['id'])) {
		$sql_where .= " AND {$id_field} IN ({$params['id']})";
	} elseif(!empty($params['skipid'])) {
		$sql_where .= " AND {$id_field} IN ({$params['skipid']})";
	}
	$sqlorderby = order_operate($params['orderby'], 'bbsusers');
	$params['start'] = $params['start'] - 1;
	return "SELECT{$select} FROM {$setting_bbstablepre}members WHERE {$sql_where} ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
}

function getthreads($params) {//ok 2008-5-19 20:29
	global $thetime;
	$datas = array();
	confirmbbsconfig(1);
	$params = operateparams('threads', $params);
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
	global $cache_memory, $db, $setting_bbstype, $setting_bbstablepre;
	$db = db();
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	includecache('itemexts');
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
	global $setting_bbstablepre, $setting_bbstype;
	$field = array();
	if($setting_bbstype == 'discuz') {
		$tablename = $setting_bbstablepre.'threads';
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
	} elseif($setting_bbstype == 'phpwind') {
		$tablename = $setting_bbstablepre.'threads';
		$fields['fid'] = 'fid';
		$fields['digest'] = 'digest';
		$fields['tid'] = 'tid';
		$fields['title'] = 'subject';
		$fields['pv'] = 'hits';
		$fields['uid'] = 'authorid';
		$fields['user'] = 'author';
		$fields['replies'] = 'replies';
		$fields['time'] = 'postdate';
		$fields['lastpost'] = 'lastpost';
		$undeleted = ' displayorder >= 0';//phpwind的对应的未作检查
	}
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
	$sqlorderby = order_operate($params['orderby'], 'threads');
	$params['start'] = $params['start'] - 1;
	return "SELECT $select_field FROM $tablename WHERE {$sql_where} AND {$undeleted} ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
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

function getcategoriesdata($params) {//ok2008-05-21 0:04
	global $db, $categories;
	$db = db();
	includecache('categories');
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	$sql = getcategoriessql($params);
	$_categories = $db->querytoarray($sql);
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
	$params['start'] = $params['start'] - 1;
	$sqlorderby = order_operate($params['orderby'], 'items');
	return "SELECT * FROM {$tablepre}_categories WHERE {$sql_where} ORDER BY {$sqlorderby} LIMIT {$params['start']}{$params['num']}";
}

function getmessages($params) {
	global $cache_memory;
	confirmbbsconfig(1);
	$params = operateparams('messages', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getmessagesdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getmessagesdata($params) {
	global $db;
	$db = db();
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	$sql = getmessagesql($params);
	$messages = $db->querytoarray($sql);
	$j = 0;//序号
	$datas = array();
	foreach($messages as $message) {
		$j ++;
		$message['id'] = $j;
		$datas[] = $message;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getmessagesql($params) {//ok
	global $setting_bbstablepre, $setting_bbstype;
	$field = array();
	if($setting_bbstype == 'discuz') {
		$tablename = $setting_bbstablepre.'posts';
		$fields['fid'] = 'fid';
		$fields['tid'] = 'tid';
		$fields['title'] = 'subject';
		$fields['uid'] = 'authorid';
		$fields['user'] = 'author';
		$fields['time'] = 'dateline';
		$fields['ip'] = 'useip';
		$fields['message'] = 'message';
	} elseif($setting_bbstype == 'phpwind') {
		//phpwind暂未支持
	}
	$select_field = '';
	foreach($fields as $key => $field) {
		$select_field .= "`$field` as `$key`,";
	}
	$select_field = substr($select_field, 0, -1);
	$sql_where = '1';
	if(!empty($params['tid'])) {
		$sql_where .= " AND tid = '{$params['tid']}'";
	}
	$sqlorderby = order_operate($params['orderby'], 'messages');
	$params['start'] = $params['start'] - 1;
	return "SELECT $select_field FROM $tablename WHERE {$sql_where} ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
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
	$db = db();
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

function getbbsinfo($params) {//废弃，暂时保留
	global $db, $tablepre, $setting_bbstablepre, $setting_bbstype, $thetime;
	$db = db();
	if(!in_array($setting_bbstype, array('phpwind', 'discuz')) || empty($setting_bbstablepre)){
		echo 'error:please complete your bbs config first.';
	}
	if($setting_bbstype == 'discuz') {
		if($params['type'] == 'regusers') {
			return $db->get_field("SELECT COUNT(*) FROM {$setting_bbstablepre}members");
		} elseif($params['type'] == 'threads') {
			return $db->get_field("SELECT COUNT(*) FROM {$setting_bbstablepre}threads");
		} elseif($params['type'] == 'posts') {
			return $db->get_field("SELECT COUNT(*) FROM {$setting_bbstablepre}posts");
		} elseif($params['type'] == 'newuser') {
			return $db->get_field("SELECT username FROM {$setting_bbstablepre}members ORDER BY uid DESC");
		}
	} elseif($setting_bbstype == 'phpwind') {
		if($params['type'] == 'regusers') {
			return $db->get_field("SELECT COUNT(*) FROM {$setting_bbstablepre}members");
		} elseif($params['type'] == 'threads') {
			return $db->get_field("SELECT COUNT(*) FROM {$setting_bbstablepre}threads");
		} elseif($params['type'] == 'posts') {
			return $db->get_field("SELECT COUNT(*) FROM {$setting_bbstablepre}posts");
		} elseif($params['type'] == 'newuser') {
			return $db->get_field("SELECT username FROM {$setting_bbstablepre}members ORDER BY uid DESC");
		}
	}
}

function getxspaceblogers($params) {//ok 2008-09-16 22:07
	global $cache_memory;
	$datas = array();
	$params = operateparams('xspaceblogers', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getxspaceblogersdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getxspaceblogersdata($params) {//ok 2008-09-16 22:07
	global $db;
	$db = db();
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	
	$sql = getxspaceblogersql($params);
	$query = $db->query($sql);
	$datas = array();
	while($bloger = $db->fetch_array($query)) {
		list($bloger['y'], $bloger['m'], $bloger['d'], $bloger['h'], $bloger['i'], $bloger['s'], $bloger['sy'], $bloger['sm'], $bloger['sd']) = explode(' ', date('Y m d H i s y n j', $bloger['time']));
		if($bloger['lastpost'] == 0) $bloger['lastpost'] = $bloger['time'];
		list($bloger['last_y'], $bloger['last_m'], $bloger['last_d'], $bloger['last_h'], $bloger['last_i'], $bloger['last_s'], $bloger['last_sy'], $bloger['last_sm'], $bloger['last_sd']) = explode(' ', date('Y m d H i s y n j', $bloger['lastpost']));
		$blogers[] = $bloger;
	}
	$j = 0;
	$datas = array();
	foreach($blogers as $bloger) {
		$j ++;
		$bloger['id'] = $j;
		$datas[] = $bloger;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getxspaceblogersql($params) {//ok 2008-09-16 22:07
	global $db;
	$tablename = 'supe_userspaces';
	$fields['username'] = 'username';
	$fields['uid'] = 'uid';
	$fields['blogs'] = 'spaceblognum';
	$fields['pv'] = 'viewnum';
	$fields['time'] = 'dateline';
	$fields['lastpost'] = 'lastpost';
	$fields['spacename'] = 'spacename';

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
	$params['start'] = $params['start'] - 1;
	$sqlorderby = order_operate($params['orderby'], 'xspaceblogers');
	return "SELECT $select_field FROM $tablename WHERE {$sql_where} ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
}

function getxspaceblogs($params) {
	global $cache_memory;
	$datas = array();
	$params = operateparams('xspaceblogs', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getxspaceblogsdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getxspaceblogsdata($params) {
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
	$sqlorderby = order_operate($params['orderby'], 'xspaceblogs');
	return "SELECT $select_field FROM $tablename WHERE {$sql_where} AND type='blog' ORDER BY {$sqlorderby} LIMIT {$params['start']},{$params['num']}";
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
	if(count($data) > 0) $keys = array_keys($data[0]);
	if(count($keys) == 0) return $options['emptymessage'];
	foreach($keys as $key) {
		$array_templates[$key] = "[$key]";
	}
	$template = $options['template'];
	$i = 0;
	foreach($data as $id => $record) {
		$html .= str_replace($array_templates, $record, $template);
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
		$return['id'] = empty($params['id']) ? '' : tidyitemlist($params['id']);
		$return['skip'] = empty($params['skip']) ? '' : tidyitemlist($params['skip']);
		$return['includesubcategory'] = empty($params['includesubcategory']) ? 0 : 1;//是否包含下级分类，默认不包含
		$template = empty($params['template']) ? '[title]<br>' : $params['template'];
		$return['show_text'] = strpos($template, '[text]') !== false ? 1 : 0;
		$return['order'] = empty($params['order']) ? 0 : $params['order'];
		$return['keywords'] = isset($params['keywords']) ? $params['keywords'] : '';
		$return['picture'] = isset($params['picture']) ? $params['picture'] : 0;//>0带图<0不带图0随便
		$return['orderby'] = empty($return['orderby']) ? 'id' : $return['orderby'];
		$return['last'] = empty($params['last']) ? 0 : $params['last'];
		$return['next'] = empty($params['next']) ? 0 : $params['next'];
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
		$return['rootcategory'] = isset($params['rootcategory']) && a_is_int($params['rootcategory']) ? $params['rootcategory'] : 0;//取某个主分类的下级分类
		$return['id'] = isset($params['id']) ? tidyitemlist($params['id']) : '';
		$return['skipid'] = isset($params['skipid']) ? tidyitemlist($params['skipid']) : '';
		$return['skipsub'] = empty($params['skipsub']) ? 0 : 1;
		$return['orderby'] = empty($return['orderby']) ? 'id' : $return['orderby'];
	} elseif($type == 'bbsusers') {
		$template = empty($params['template']) ? '[username]<br>' : $params['template'];
		$return['group'] = isset($params['group']) ? tidyitemlist($params['group']) : 0;
		$return['skipgroup'] = isset($params['skipgroup']) ? tidyitemlist($params['skipgroup']) : 0;
		$return['id'] = isset($params['id']) ? tidyitemlist($params['id']) : 0;
		$return['skipid'] = isset($params['skipid']) ? tidyitemlist($params['skipid']) : 0;
		$return['orderby'] = empty($return['orderby']) ? 'regdate_reverse' : $return['orderby'];
	} elseif($type == 'blogs') {
		if(empty($params['template'])) {
			$template = '[title]<br>';
		} else {
			$template = $params['template'];
		}
		if(isset($params['uids'])) {
			$return['uids'] = tidyitemlist($params['uids']);
		} else {
			$return['uids'] = 0;
		}
		if(isset($params['skipuids'])) {
			$return['skipuids'] = tidyitemlist($params['skipuids']);
		} else {
			$return['skipuids'] = 0;
		}
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
	if(!in_array($type, array('items', 'categories', 'threads', 'messages', 'bbsusers', 'messages', 'comments', 'xspaceblogers', 'xspaceblogs'))) {
		return '';
	}
	if(strpos($rule, 'random') !== false) return 'random';
	$array_items_field = array(
		'id' => 'id',
		'orderby' => 'orderby',
		'time' => 'dateline',
		'update' => 'lastupdate',
		'pv' => 'pageview',
		'title' => 'title',
	);
	$array_threads_field = array(
		'tid' => 'tid',
		'views' => 'pv',
		'replies' => 'replies',
		'time' => 'time',//?
		'lastpost' => 'lastpost'
	);
	$array_messages_field = array(
		'mid' => 'pid',
		'time' => 'time',
	);
	$array_categories_field = array(
		'orderby' => 'orderby',
		'id' => 'id'
	);
	$array_bbsusers_field = array(
		'credits1' => 'extcredits1',
		'credits2' => 'extcredits2',
		'credits3' => 'extcredits3',
		'credits4' => 'extcredits4',
		'credits5' => 'extcredits5',
		'credits6' => 'extcredits6',
		'credits7' => 'extcredits7',
		'credits8' => 'extcredits8',
		'uid' => 'uid',
		'regdate' => 'regdate',
		'lastvisit' => 'lastvisit',
		'lastactivity' => 'lastactivity',
		'lastpost' => 'lastpost',
		'posts' => 'posts',
		'point' => 'credits',//积分
	);
	$array_comments_field = array(
		'id' => 'id',
		'time' => 'dateline',
		'goodnum' => 'goodnum',
		'badnum' => 'badnum'
	);
	$array_xspaceblogers_field = array(
		'id' => 'uid',
		'lastpost' => 'lastpost',
		'dateline' => 'dateline',
		'username' => 'username',
		'pv' => 'viewnum',
		'blogs' => 'spaceblognum'
	);
	$array_xspaceblogs_field = array(
		'id' => 'itemid',
		'lastpost' => 'lastpost',
		'dateline' => 'dateline',
		'username' => 'username',
		'pv' => 'viewnum',
		'replaynum' => 'replaynum'
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
	includecache('itemexts');
	$datas = array();
	$params = operateparams('index', $params);
	$datas[0]['last'] = ceil($params['total'] / $params['ipp']);
	$_indexs = '';
	$_start = max($params['page'] - 3, 1);
	$_end = min($_start + 9, ceil($params['total'] / $params['ipp']));
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

function getblogs($params) {//OK 2008-05-17 0:51//暂不支持，暂时保留
	global $exe_times, $db, $lr, $thetime, $cache_memory;
	$datas = array();
	$params = operateparams('blog', $params);
	if(isset($cache_memory[$params['cachekey']])) {
		$datas = $cache_memory[$params['cachekey']];
	} else {
		$datas = getblogsdata($params);
		$cache_memory[$params['cachekey']] = $datas;
	}
	$html = renderdata($datas, $params);
	echo $html;
}

function getblogsdata($params) {//暂不支持，暂时保留
	global $db;
	$db = db();
	$datas = getcachedata($params);
	if(!empty($datas)) return $datas;
	$sql = getblogsql($params);
	$blogs = $db->querytoarray($sql);
	foreach($blogs as $blog) {
		$j ++;
		
		$datas[] = $blog;
	}
	$cache_memory[$params['cachekey']] = $datas;
	if($params['expire'] > 0) setcachedata($params, $datas);
	return $datas;
}

function getblogsql($params) {//暂不支持，暂时保留
//getblogs
//参数digest,grade
//模板id,username,uid,title,post_y......post_s,last_y......post_s,pv,replies,
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
<?php
require_once 'include/common.inc.php';
require_once 'include/admin.inc.php';
if(!isset($get_action)) {
	if(empty($get_preurl)) {
		$body_url = 'welcome.php';
	} else {
		$body_url = urldecode($get_preurl);
	}
	$smarty->assign('body_url', $body_url);
	displaytemplate('admincp_frame.htm');
	exit();
} elseif($get_action == 'menu') {
	$smarty->assign('admin_id', $admin_id);
	$smarty->assign('iscreator', iscreator());
	displaytemplate('admincp_menu.htm');
	exit();
} elseif($get_action == 'top') {
	$smarty->assign('homepage', $homepage);
	$smarty->assign('admin_id', $admin_id);
	displaytemplate('admincp_top.htm');
	exit();
} elseif($get_action == 'categories') {
	checkcreator();
	$forum_select = getforums_select();
	$query = $db->list_by('*', 'categories', '', 'id');
	$str_categories = '';
	$categories = array();
	while($category = $db->fetch_array($query)) {
		$categories[] = $category;
	}
	$extfields = extfields();
	$extfieldshtml = '';
	foreach($extfields as $field) {
		$extfieldshtml .= "<input name='extfield[]' value='{$field['alias']}' type='checkbox' id='extfield_{$field['alias']}'><label for='extfield_{$field['alias']}'>{$field['name']}</label><br>";
	}
	foreach($categories as $category) {
		if($category['categoryup'] == 0) {
			$subcategories = '';
			$str_categories .= "<tr><td>{$category['id']}</td>\n
			<td><a href=\"admincp.php?action=editcategory&id={$category['id']}\">{$category['category']}</a>&nbsp;(<a title=\"{$lan['item_new']}\" href=\"admincp.php?action=newitem&category={$category['id']}\">+</a>)</td>\n
			<td>{$category['description']}</td>\n
			<td class=\"mininum\">{$category['pv']}</td>\n
			<td class=\"mininum\">({$category['items']}/{$category['allitems']})</td>
			<td align=\"center\"><a href=\"admincp.php?action=createcategory&id={$category['id']}\">{$lan['createdefault']}</a></td><td align=\"center\"><a href=\"admincp.php?action=createcategory&id={$category['id']}&job=page\">{$lan['createlist']}</a></td>
			<td class=\"mininum\">{$category['orderby']}</td>\n
			<td align='center'><a href=\"javascript:deletecategory({$category['id']})\">".alert($lan['delete'])."</a></td></tr>\n";
			foreach($categories as $subcategory) {
				if($subcategory['categoryup'] == $category['id']) {
					$subcategories .= "<a href=\"admincp.php?action=editcategory&id={$subcategory['id']}\">{$subcategory['category']}</a><span class=\"mininum\">({$subcategory['items']})</span>&nbsp;";
				}
			}
			if(!empty($subcategories)) $str_categories .= "<tr><td></td><td colspan=\"8\">{$subcategories}</td></tr>";
		}
	}
	if($str_categories == '') $str_categories = '<tr><td colspan="10">'.$lan['category_no'].'</td></tr>';
	$selecttemplates = get_select_templates();
	$selectitemtemplates = get_select_templates();
	$selectcategories = get_select('category', 1);
	$smarty->assign('selecttemplates', $selecttemplates);
	$smarty->assign('selectitemtemplates', $selectitemtemplates);
	$smarty->assign('selectcategories', $selectcategories);
	$smarty->assign('str_categories', $str_categories);
	$smarty->assign('setting_storemethod', $setting_storemethod);
	$smarty->assign('setting_categoryhomemethod', $setting_categoryhomemethod);
	$smarty->assign('setting_categorypagemethod', $setting_categorypagemethod);
	$smarty->assign('forum_select', $forum_select);
	$smarty->assign('extfieldshtml', $extfieldshtml);
	displaytemplate('admincp_categories.htm');
} elseif($get_action == 'newcategory') {
	checkcreator();
	if(empty($post_category)) adminmsg($lan['nocategoryname'], 'back', 3, 1);
	!a_is_int($post_order) && $post_order = 0;
	$pathchecked = checkpath($post_path);
	if($pathchecked != '') adminmsg($pathchecked, 'back', 3, 1);
	empty($post_fid) && $post_fid = 0;
	if(!empty($post_alias)) {
		$post_alias = trim($post_alias);
		if($db->get_by('id', 'categories', "alias='$post_alias'")) adminmsg($lan['aliasused'], 'back', 3, 1);
	}
	$itemextfields = '';
	if(isset($post_extfield)) $itemextfields = implode(',', $post_extfield);
	$values = array(
		'categoryup' => $post_categoryup,
		'category' => $post_category,
		'alias' => $post_alias,
		'orderby' => $post_order,
		'description' => $post_description,
		'keywords' => $post_keywords,
		'path' => $post_path,
		'itemtemplate' => $post_itemtemplate,
		'defaulttemplate' => $post_defaulttemplate,
		'listtemplate' => $post_listtemplate,
		'html' => $post_html,
		'usefilename' => $post_usefilename,
		'storemethod' => $post_storemethod,
		'categoryhomemethod' => $post_categoryhomemethod,
		'categorypagemethod' => $post_categorypagemethod,
		'fid' => $post_fid,
		'itemextfields' => $itemextfields
	);
	$db->insert('categories', $values);
	$categoryid = $db->insert_id();
	includecache('categories', 1);
	if($post_html == 1 && ($post_categoryup ==0 || $categories[$post_categoryup]['html'] == 1)) {
		$path = empty($post_path) ? $db->insert_id() : $post_path;
		if($post_categoryup == 0) {
			$truepath = FORE_ROOT.$path;
		} else {
			$truepath = FORE_ROOT.(!empty($categories[$post_categoryup]['path']) ? $categories[$post_categoryup]['path'] : $post_categoryup).'/'.$path;
		}
		if(!is_dir($truepath)) ak_mkdir($truepath);
	}
	adminmsg($lan['addcategoryok'], 'admincp.php?action=categories');
} elseif($get_action == 'deletecategory') {
	checkcreator();
	if(!isset($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong'], 'back', 3, 1);
	$item = $db->get_by('*', 'items', "category='$get_id'");
	if($item !== false) adminmsg($lan['delcategoryhasitem'], 'back', 3, 1);
	$category = $db->get_by('*', 'categories', "categoryup='$get_id'");
	if($category !== false) adminmsg($lan['delcategoryhassub'], 'back', 3, 1);
	$db->delete('categories', "id='$get_id'");
	$db->delete('crons', "itemid='$get_id' AND job='cate'");
	updatecache('categories');
	adminmsg($lan['operatesuccess'], 'admincp.php?action=categories');
} elseif($get_action == 'editcategory') {
	checkcreator();
	if(!isset($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong'], '', 3, 1);
	$id = $get_id;
	if(!isset($post_category_edit_submit)) {
		refreshitemnum($id);
		$category = $db->get_by('*', 'categories', "id='$id'");
		if($category == false) adminmsg($lan['nocategory'], '', 3, 1);
		$forum_select = getforums_select();
		$smarty->assign('forum_select', $forum_select);
		$extfields = extfields();
		$extfieldshtml = '';
		foreach($extfields as $field) {
			$extfieldshtml .= "<input name='extfield[]' value='{$field['alias']}' type='checkbox' id='extfield_{$field['alias']}'><label for='extfield_{$field['alias']}'>{$field['name']}</label><br>";
		}
		$smarty->assign('extfieldshtml', $extfieldshtml);
		$selectcategories = get_select('category', 0);
		$selecttemplates = get_select_templates();
		$selectitemtemplates = get_select_templates();
		$smarty->assign('selecttemplates', $selecttemplates);
		$smarty->assign('selectitemtemplates', $selectitemtemplates);
		$smarty->assign('selectcategories', $selectcategories);
		$smarty->assign('id', $get_id);
		$smarty->assign('category', htmlspecialchars($category['category']));
		$smarty->assign('alias', htmlspecialchars($category['alias']));
		$smarty->assign('orderby', $category['orderby']);
		$smarty->assign('path', $category['path']);
		$smarty->assign('categoryup', $category['categoryup']);
		$smarty->assign('html', $category['html']);
		$smarty->assign('usefilename', $category['usefilename']);
		$smarty->assign('itemtemplate', $category['itemtemplate']);
		$smarty->assign('defaulttemplate', $category['defaulttemplate']);
		$smarty->assign('listtemplate', $category['listtemplate']);
		$smarty->assign('storemethod', $category['storemethod']);
		$smarty->assign('categoryhomemethod', $category['categoryhomemethod']);
		$smarty->assign('categorypagemethod', $category['categorypagemethod']);
		$smarty->assign('fid', $category['fid']);
		$smarty->assign('description', htmlspecialchars($category['description']));
		$smarty->assign('keywords', htmlspecialchars($category['keywords']));
		$smarty->assign('itemextfields', explode(',', $category['itemextfields']));
		$smarty->assign('setting_storemethod', $setting_storemethod);
		$smarty->assign('setting_categoryhomemethod', $setting_categoryhomemethod);
		$smarty->assign('setting_categorypagemethod', $setting_categorypagemethod);
		displaytemplate('admincp_category_edit.htm');
	} else {
		includecache('categories');
		if(empty($post_category)) adminmsg($lan['nocategoryname'], 'back', 3, 1);
		if(!a_is_int($post_order)) $post_order = 0;
		if($get_id == $post_categoryup) adminmsg($lan['upperisself'], 'back', 3, 1);
		if(!empty($post_alias)) {
			$post_alias = trim($post_alias);
			if($db->get_by('*', 'categories', "alias='$post_alias' AND id<>'$get_id'")) adminmsg($lan['aliasused'], 'back', 3, 1);
		}
		$category = $db->get_by('*', 'categories', "id='$id'");
		if($category['path'] != $post_path) {
			$pathchecked = checkpath($post_path);
			if($pathchecked != '') adminmsg($pathchecked, 'back', 3, 1);
		}
		if($post_html == 1 && ifcategoryhtml($get_id)) {
			$old_truepath = getcategorypath($get_id, $category['categoryup'], $category['path']);
			$truepath = getcategorypath($get_id, $post_categoryup, $post_path);
			@rename($old_truepath, $truepath);
			$path = empty($post_path) ? $get_id : $post_path;
			if($post_categoryup == 0) {
				$truepath = FORE_ROOT.$path;
			} else {
				$truepath = FORE_ROOT.(!empty($categories[$post_categoryup]['path']) ? $categories[$post_categoryup]['path'] : $post_categoryup).'/'.$path;
			}
			if(!is_dir($truepath)) ak_mkdir($truepath);
		} else {
			if(ifcategoryhtml($get_id)) {
				$old_truepath = getcategorypath($get_id, $category['categoryup'], $category['path']);
				@rmdir($old_truepath);
			}
		}
		!isset($post_fid) && $post_fid = 0;
		$itemextfields = '';
		if(isset($post_extfield)) $itemextfields = implode(',', $post_extfield);
		$value = array(
			'categoryup' => $post_categoryup,
			'category' => $post_category,
			'alias' => $post_alias,
			'orderby' => $post_order,
			'description' => $post_description,
			'keywords' => $post_keywords,
			'path' => $post_path,
			'itemtemplate' => $post_itemtemplate,
			'defaulttemplate' => $post_defaulttemplate,
			'listtemplate' => $post_listtemplate,
			'html' => $post_html,
			'usefilename' => $post_usefilename,
			'storemethod' => $post_storemethod,
			'categorypagemethod' => $post_categorypagemethod,
			'categoryhomemethod' => $post_categoryhomemethod,
			'fid' => $post_fid,
			'itemextfields' => $itemextfields
		);
		$db->update('categories', $value, "id='$get_id'");
		updatecache('categories');
		adminmsg($lan['operatesuccess'], 'admincp.php?action=categories');
	}
} elseif($get_action == 'sections') {
	checkcreator();
	$query = $db->list_by('*', 'sections', '', 'id');
	$str_sections = '';
	while($section = $db->fetch_array($query)) {
		$str_sections .= "<tr><td>{$section['id']}</td><td><a href=\"admincp.php?action=editsection&id={$section['id']}\">{$section['section']}</a></td><td>{$section['description']}</td><td>{$section['items']}</td><td align=\"center\"><a href=\"admincp.php?action=createsection&id={$section['id']}\">{$lan['createdefault']}</a></td><td align=\"center\"><a href=\"admincp.php?action=createsection&id={$section['id']}&job=page\">{$lan['createlist']}</a></td><td>{$section['orderby']}</td><td align='center'>".
		($section['id'] != 1 ? "<a href=\"javascript:deletesection({$section['id']})\">".alert($lan['delete'])."</a>" : $lan['delete'])."</td></tr>\r\n";
	}
	if($str_sections == '') $str_sections = '<tr><td colspan="10">'.$lan['section_no'].'</td></tr>';
	$selecttemplates = get_select_templates();
	$smarty->assign('str_sections', $str_sections);
	$smarty->assign('selecttemplates', $selecttemplates);
	$smarty->assign('setting_sectionhomemethod', htmlspecialchars($setting_sectionhomemethod));
	$smarty->assign('setting_sectionpagemethod', htmlspecialchars($setting_sectionpagemethod));
	displaytemplate('admincp_sections.htm');
} elseif($get_action == 'newsection') {
	checkcreator();
	if(empty($post_section)) adminmsg($lan['nosectionname'], 'back', 3, 1);
	if(!a_is_int($post_order)) $post_order = 0;
	$value = array(
		'section' => $post_section,
		'alias' => $post_alias,
		'orderby' => $post_order,
		'description' => $post_description,
		'keywords' => $post_keywords,
		'sectionpagemethod' => $post_sectionpagemethod,
		'sectionhomemethod' => $post_sectionhomemethod,
		'html' => $post_html,
		'listtemplate' => $post_listtemplate,
		'defaulttemplate' => $post_defaulttemplate,
	);
	$db->insert('sections', $value);
	updatecache('sections');
	adminmsg($lan['operatesuccess'], 'admincp.php?action=sections');
} elseif($get_action == 'deletesection') {
	checkcreator();
	if(!isset($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong'], 'admincp.php?action=sections', 3, 1);
	if(intval($get_id) == 1) adminmsg($lan['defaultsectionnodel'], 'admincp.php?action=sections', 3, 1);
	$db->delete('sections', "id='$get_id'");
	updatecache('sections');
	adminmsg($lan['operatesuccess'], 'admincp.php?action=sections');
} elseif($get_action == 'editsection') {
	checkcreator();
	if(!isset($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong'], '', 3, 1);
	if(!isset($post_section_edit_submit)) {
		$section = $db->get_by('*', 'sections', "id='$get_id'");
		if(empty($section)) adminmsg($lan['nosection'], '', 0, 1);
		$selecttemplates = get_select_templates();
		$smarty->assign('selecttemplates', $selecttemplates);
		$smarty->assign('id', $get_id);
		$smarty->assign('section', htmlspecialchars($section['section']));
		$smarty->assign('alias', htmlspecialchars($section['alias']));
		$smarty->assign('orderby', $section['orderby']);
		$smarty->assign('description', htmlspecialchars($section['description']));
		$smarty->assign('keywords', htmlspecialchars($section['keywords']));
		$smarty->assign('sectionhomemethod', htmlspecialchars($section['sectionhomemethod']));
		$smarty->assign('sectionpagemethod', htmlspecialchars($section['sectionpagemethod']));
		$smarty->assign('defaulttemplate', htmlspecialchars($section['defaulttemplate']));
		$smarty->assign('listtemplate', htmlspecialchars($section['listtemplate']));
		$smarty->assign('html', $section['html']);
		$smarty->assign('setting_sectionhomemethod', htmlspecialchars($setting_sectionhomemethod));
		$smarty->assign('setting_sectionpagemethod', htmlspecialchars($setting_sectionpagemethod));
		displaytemplate('admincp_section_edit.htm');
	} else {
		if(empty($post_section)) adminmsg($lan['nosectionname'], 'back', 0, 1);
		if(!a_is_int($post_order)) $post_order = 0;
		$value = array(
			'section' => $post_section,
			'alias' => $post_alias,
			'orderby' => $post_order,
			'description' => $post_description,
			'keywords' => $post_keywords,
			'sectionpagemethod' => $post_sectionpagemethod,
			'sectionhomemethod' => $post_sectionhomemethod,
			'html' => $post_html,
			'listtemplate' => $post_listtemplate,
			'defaulttemplate' => $post_defaulttemplate,
		);
		$db->update('sections', $value, "id='$get_id'");
		updatecache('sections');
		adminmsg($lan['operatesuccess'], 'admincp.php?action=sections');
	}
} elseif($get_action == 'newitem') {
	includecache('categories');
	if(!isset($post_title)) {
		foreach($settings as $key => $setting) {
			if(preg_match("/^item(.*)show$/is", $key, $matchs)) {
				$smarty->assign($matchs[1].'_show', $setting);
			}
		}
		$category = 0;
		if(isset($get_category)) $category = $get_category;
		$selectcategories = get_select('category');
		$selectsections = get_select('section');
		$selecttemplates = get_select_templates();
		$smarty->assign('selectcategories', $selectcategories);
		$smarty->assign('selectsections', $selectsections);
		$smarty->assign('selecttemplates', $selecttemplates);
		$smarty->assign('maxattachsize', $setting_maxattachsize * 1024);
		$smarty->assign('usefilename', $setting_usefilename);
		$smarty->assign('data', '');
		$smarty->assign('category', $category);
		$smarty->assign('richtext', $setting_richtext);
		displaytemplate('admincp_item_new.htm');
	} else {
		if(empty($post_title)) adminmsg($lan['notitle'], 'back', 3, 1);
		if(empty($post_category) || !a_is_int($post_category)) adminmsg($lan['nocategory'], 'back', 3, 1);
		!isset($post_shorttitle) && $post_shorttitle = '';
		!isset($post_filename) && $post_filename = '';
		!isset($post_order) && !a_is_int($post_order) && $post_order = 0;
		!isset($post_order2) && !a_is_int($post_order2) && $post_order2 = 0;
		!isset($post_order3) && !a_is_int($post_order3) && $post_order3 = 0;
		!isset($post_order4) && !a_is_int($post_order4) && $post_order4 = 0;
		!isset($post_section) && $post_section = 0;
		!isset($post_author) && $post_author = '';
		!isset($post_source) && $post_source = '';
		!isset($post_template) && $post_template = '';
		!isset($post_filename) && $post_filename = '';
		!isset($post_titlecolor) && $post_titlecolor = '';
		!isset($post_titlestyle) && $post_titlestyle = '';
		!isset($post_aimurl) && $post_aimurl = '';
		!isset($post_data) && $post_data = '';
		!isset($post_keywords) && $post_keywords = '';
		$post_keywords = str_replace(' ', ',', $post_keywords);
		!isset($post_digest) && $post_digest = '';
		!isset($file_attach['name']) && $file_attach['name'] = array();
		!isset($file_attach['tmp_name']) && $file_attach['tmp_name'] = array();
		if(empty($post_keywords) && !empty($setting_autoparsekeywords)) $post_keywords = getkeywords($post_title, $post_data, 6);

		if($setting_usefilename) {
			if(!empty($post_filename) && strpos($post_filename, '.') === false) $post_filename .= $setting_htmlexpand;
			$filenamechecked = checkfilename($post_filename);
			if($filenamechecked != '') adminmsg($filenamechecked, 'back', 3, 1);
			if(!empty($post_filename)) {
				$htmlfilename = htmlname(0, $post_category, $thetime, $post_filename);
				if($db->get_by('id', 'filenames', "filename='$htmlfilename'")) adminmsg($lan['fileexist'], 'back', 6, 1);
			} else {
				if(!empty($setting_autoparsefilename)) {
					$post_filename = calfilename($post_title, $post_data, $post_category, 0, $timestamp);
				}
			}
		}
		$picture = '';
		if(!empty($post_picture)) {
			$picture = $post_picture;
		} elseif(!empty($file_uploadpicture) && !empty($file_uploadpicture['name'])) {
			$headpicext = fileext($file_uploadpicture['name']);
			if(!ispicture($file_uploadpicture['name'])) adminmsg($lan['pictureexterror'], 'back', 3, 1);
			$filename = get_upload_filename($file_uploadpicture['name'], 0, $post_category, 'preview');
			if(uploadfile($file_uploadpicture, FORE_ROOT.$filename) != false) $picture = $filename;
		}

		//ext start
		$ext = 0;
		$extvalue = array();
		foreach($_POST as $key => $value) {
			if(strpos($key, 'ext_') === 0) {
				$key = substr($key, 4);
				$extvalue[$key] = $value;
			}
		}
		if(!empty($extvalue)) {
			$ext = 1;
			$extvalue = serialize($extvalue);
		}
		$extinsertvalue = array('value' => $extvalue);
		//ext end
		$values = array(
			'title' => $post_title,
			'shorttitle' => $post_shorttitle,
			'category' => $post_category,
			'section' => $post_section,
			'author' => $post_author,
			'source' => $post_source,
			'editor' => $admin_id,
			'orderby' => $post_order,
			'orderby2' => $post_order2,
			'orderby3' => $post_order3,
			'orderby4' => $post_order4,
			'dateline' => $thetime,
			'template' => $post_template,
			'filename' => $post_filename,
			'picture' => $picture,
			'latesthtml' => 0,
			'keywords' => $post_keywords,
			'digest' => $post_digest,
			'titlecolor' => $post_titlecolor,
			'titlestyle' => $post_titlestyle,
			'aimurl' => $post_aimurl,
			'ext' => $ext
		);
		$db->insert('items', $values);
		$itemid = $db->insert_id();
		if(!empty($post_data)) {
			$value = array(
				'itemid' => $itemid,
				'text' => $post_data,
			);
			$db->insert('texts', $value);
		}
		$extinsertvalue['id'] = $itemid;
		if($ext) $db->insert('item_exts', $extinsertvalue);
		$hasattach = 0;
		$filenames = array();
		foreach($file_attach['name'] as $id => $a) {
			if(!empty($a)) {
				$filenames[$id] = get_upload_filename($file_attach['name'][$id], $itemid, $post_category);
				if(in_array(fileext($file_attach['name'][$id]), array('php'))) {
					adminmsg($lan['attachexterror'], 'back', 3, 1);
				}
				if($file_attach['error'][$id] == 2) {
					adminmsg($lan['attachtoobig'][0].$setting_maxattachsize.$lan['attachtoobig'][1], 'back', 3, 1);
				}
			}
		}
		foreach($file_attach['tmp_name'] as $id => $a) {
			if(!empty($a)) {
				$filename = $filenames[$id];
				if(uploadtmpfile($a, FORE_ROOT.$filename) != false) {
					if(ispicture($filename)) addwatermark(FORE_ROOT.$filename);
					$value = array(
						'itemid' => $itemid,
						'filename' => $filename,
						'filesize' => $file_attach['size'][$id],
						'description' => $post_description[$id],
						'dateline' => $timestamp,
					);
					$db->insert('attachments', $value);
					$hasattach = 1;
				}
			}
		}
		if($hasattach == 1) $db->update('items', array('attach' => 1), "id='$itemid'");
		if($setting_usefilename) {
			$filename = htmlname($itemid, $post_category, $thetime, $post_filename);
			$value = array(
				'filename' => $filename,
				'type' => 'item',
				'dateline' => $thetime,
				'id' => $itemid,
				'page' => 0,
			);
			if(!empty($filename)) $db->insert('filenames', $value);
		}
		batchhtml(array($itemid));
		refreshitemnum(array($post_category), 'category');
		refreshitemnum(array($post_section), 'section');
		refreshitemnum(array($admin_id), 'editor');
		adminmsg($lan['operatesuccess'], 'admincp.php?action=items');
	}
} elseif($get_action == 'items') {
	if(isset($post_batchsubmit)) {
		if(isset($post_batch)) {
			if($post_batchtype == 'delete') {
				batchdeleteitem($post_batch);
				adminmsg($lan['operatesuccess'], $_SERVER['HTTP_REFERER']);
			} elseif($post_batchtype == 'createhtml') {
				batchhtml($post_batch);
				adminmsg($lan['operatesuccess'], 'back');
			} elseif($post_batchtype == 'setorder') {
				empty($post_neworder) && $post_neworder = 0;
				if(!a_is_int($post_neworder)) $post_neworder = 0;
				$ids = implode(',', $post_batch);
				$value = array(
					'orderby' => $post_neworder,
				);
				$db->update('items', $value, "id IN ($ids)");
				adminmsg($lan['operatesuccess'], $_SERVER['HTTP_REFERER']);
			} elseif($post_batchtype == 'setcategory') {
				empty($post_newcategory) && $post_newcategory = 1;
				if(!a_is_int($post_newcategory)) $post_newcategory = 1;
				$ids = implode(',', $post_batch);
				$value = array(
					'category' => $post_newcategory,
				);
				$db->update('items', $value, "id IN ($ids)");
				updateitemfilename($post_batch);
				adminmsg($lan['operatesuccess'], $_SERVER['HTTP_REFERER']);
			}
		} else {
			adminmsg($lan['noitembatch'], 'back', 3, 1);
		}
	}
	includecache('categories');
	includecache('sections');
	$selectcategories = get_select('category');
	$selectsections = get_select('section');
	$smarty->assign('selectcategories', $selectcategories);
	$smarty->assign('selectsections', $selectsections);
	$sql_condition = 'category>0 ';
	$url_condition = '';
	if(!empty($get_id)) {
		$ids = tidyitemlist($get_id);
		$sql_condition .= " AND id IN ({$ids})";
		$url_condition .= "&id={$get_id}";
	}
	if(!empty($get_key)) {
		$sql_condition .= " AND title LIKE '%{$get_key}%'";
		$url_condition .= "&key={$get_key}";
	}
	if(!empty($get_editor)) {
		$sql_condition .= " AND editor='{$get_editor}'";
		$url_condition .= "&editor={$get_editor}";
	}
	if(!empty($get_category)) {
		$sql_condition .= " AND category='$get_category'";
		$url_condition .= "&category={$get_category}";
	}
	if(!empty($get_section)) {
		$sql_condition .= " AND section='{$get_section}'";
		$url_condition .= "&section={$get_section}";
	}
	empty($get_orderby) && $get_orderby = 'id';
	!in_array($get_orderby, array('id', 'orderby', 'pageview', 'dateline')) && $get_orderby = 'id';
	$url_condition .= "&orderby={$get_orderby}";
	$ipp = $settings['ipp'];
	$page = isset($get_page) ? $get_page : 1;
	isset($post_page) && $page = $post_page;
	!a_is_int($page) && $page = 1;
	$start_id = ($page - 1) * $ipp;
	$url = 'admincp.php?action=items'.$url_condition;
	$count = $db->get_by('COUNT(id)', 'items', $sql_condition);
	$str_index = multi($count, $ipp, $page, $url);
	$smarty->assign('str_index', $str_index);
	$query = $db->list_by('*', 'items', $sql_condition, " `$get_orderby` DESC,id DESC", "$start_id,$ipp");
	$str_items = '';
	while($item = $db->fetch_array($query)) {
		$createhtml_text = '<a href="admincp.php?action=createhtml&id='.$item['id'].'&category='.$item['category'].'" target="work">'.$lan['createhtml'].'</a>';
		$attach = $item['attach'] ? '<img src="images/admin/attach.gif" alt="'.$lan['haveattach'].'">&nbsp;' : '';
		$picture = $item['picture'] ? '<a href="'.$item['picture'].'" target="_blank"><img src="images/admin/picture.gif" alt="'.$lan['havepicture'].'" border="0"></a>&nbsp;' : '';
		$checkbox = "<input type=\"checkbox\" name=\"batch[]\" value=\"{$item['id']}\">";
		$category = isset($categories[$item['category']]) ? $categories[$item['category']]['category'] : $lan['deleted'];
		$section = isset($sections[$item['section']]) ? $sections[$item['section']]['section'] : $lan['deleted'];
		$title = htmltitle(htmlspecialchars($item['title']), $item['titlecolor'], $item['titlestyle']);
		$str_items .= "<tr><td>{$item['id']}</td><td title=\"{$lan['author']}:{$item['author']}\">{$checkbox}{$attach}{$picture}<a href=\"admincp.php?action=edititem&id={$item['id']}\">".$title."</a></td><td>{$item['editor']}</td><td title=\"{$lan['section']}:{$section}\">{$category}</td><td class=\"mininum\">{$item['orderby']}</td><td class=\"mininum\">{$item['pageview']}</td><td class=\"mininum\"><a href=\"admincp.php?action=comments&id={$item['id']}\">{$item['commentnum']}</a></td><td align='center' title='".date('H:i:s', $item['dateline'])."' class=\"mininum\">".date('Y-m-d', $item['dateline'])."</td><td align='center'><a href=\"admincp.php?action=deleteitem&id={$item['id']}\" onclick=\"return confirmdelete()\">".alert($lan['delete'])."</a></td><td align='center'><a href=\"admincp.php?action=preview&id={$item['id']}\" target=\"_blank\">{$lan['preview']}</a></td><td align='center'>{$createhtml_text}</td></tr>\n";
	}
	if($str_items == '') $str_items = '<tr><td colspan="15">'.$lan['item_no'].'</td></tr>';
	$smarty->assign('str_items', $str_items);
	$smarty->assign('indexurl', $url);
	displaytemplate('admincp_items.htm');
} elseif($get_action == 'specialpages') {
	if(!isset($get_job) && !isset($get_id)) {
		$query = $db->list_by('*', 'items', 'category=0', 'id');
		$str_pages = '';
		while($page = $db->fetch_array($query)) {
			$createhtml_text = '<a href="admincp.php?action=createhtml&id='.$page['id'].'&category=0" target="work">'.$lan['createhtml'].'</a>';
			$delete_text = "<a href=\"admincp.php?action=deleteitem&id={$page['id']}\" onclick=\"return confirmdelete()\">".alert($lan['delete'])."</a>";
			$str_pages .= "<tr><td>{$page['id']}</td><td><a href=\"admincp.php?action=specialpages&id={$page['id']}\">{$page['title']}</a></td><td><a href=\"admincp.php?action=template&template=,{$page['template']}\">{$page['template']}</a></td><td>{$page['filename']}</td><td>{$page['pageview']}</td><td align='center'><a href=\"admincp.php?action=preview&id={$page['id']}\" target=\"_blank\">{$lan['preview']}</td><td align='center'>{$delete_text}</td><td align='center'>{$createhtml_text}</td></tr>\n";
		}
		if($str_pages == '') $str_pages = '<tr><td colspan="10">'.$lan['specialpage_no'].'</td></tr>';
		$selecttemplates = get_select_templates();
		$smarty->assign('forbidautorefresh', $setting_forbidautorefresh);
		$smarty->assign('str_pages', $str_pages);
		$smarty->assign('str_templates', $selecttemplates);
		displaytemplate('admincp_specialpages.htm');
	} elseif(isset($get_job) && $get_job == 'newpage') {
		if(empty($post_pagename) || empty($post_template) || empty($post_filename)) adminmsg($lan['allarerequired'], 'back', 3, 1);
		if(!empty($post_filename) && strpos($post_filename, '.') === false) $post_filename .= $setting_htmlexpand;
		$filenamechecked = checkfilename($post_filename, 'noempty');
		if($filenamechecked != '') adminmsg($filenamechecked, 'back', 3, 1);
		if(!empty($post_filename)) {
			$htmlfilename = htmlname(0, 0, $thetime, $post_filename);
			if($db->get_by('id', 'filenames', "filename='$htmlfilename'")) adminmsg($lan['fileexist'], 'back', 6, 1);
		}
		$value = array(
			'title' => $post_pagename,
			'template' => $post_template,
			'filename' => $post_filename,
			'dateline' => $thetime,
			'author' => $admin_id
		);
		$db->insert('items', $value);
		$itemid = $db->insert_id();
		if(!empty($post_type)) {
			if($post_type == 4) $post_minute = $post_distance;
			replaceintocrons($post_type, $post_day, $post_date, $post_hour, $post_minute, $itemid, 'page');
			updatecache('crons');
		}
		if(!empty($post_data)) $db->insert('texts', array('itemid' => $itemid, 'text' => $post_data));
		$filename = htmlname($itemid, 0, $thetime, $post_filename);
		if(!empty($filename)) {
			$value = array(
				'filename' => $filename,
				'type' => 'page',
				'dateline' => $thetime,
				'id' => $itemid,
				'page' => 0
			);
			$db->insert('filenames', $value);
			batchhtml(array($itemid));
		}
		adminmsg($lan['operatesuccess'], 'admincp.php?action=specialpages');
	} elseif(!empty($get_id)) {
		if(!a_is_int($get_id)) adminmsg($lan['parameterwrong'], '', 3, 1);
		if(!isset($post_saveeditpage)) {
			$page = $db->get_by('*', 'items', "id='$get_id'");
			if(empty($page)) adminmsg($lan['parameterwrong'], '', 3, 1);
			$text = $db->get_by('text', 'texts', "itemid='$get_id'");
			list($day, $date, $hour, $minute, $distance, $type) = array(0, 1, '', '', '', 0);
			if($cron = $db->get_by('*', 'crons', "itemid='$get_id' AND job='page'")) {
				$type = $cron['type'];
				if($type > 0 && $type < 4) {
					$hour = $cron['hour'];
					$minute = $cron['minute'];
				}
				if($type == 2) $date = $cron['date'];
				if($type == 3) $day = $cron['day'];
				if($type == 4) $distance = $cron['minute'];
			}
			$selecttemplates = get_select_templates();
			$smarty->assign('id', $get_id);
			$smarty->assign('pagename', $page['title']);
			$smarty->assign('filename', $page['filename']);
			$smarty->assign('data', $text);
			$smarty->assign('template', $page['template']);
			$smarty->assign('str_templates', $selecttemplates);
			$smarty->assign('date', $date);
			$smarty->assign('day', $day);
			$smarty->assign('hour', $hour);
			$smarty->assign('minute', $minute);
			$smarty->assign('distance', $distance);
			$smarty->assign('type', $type);
			displaytemplate('admincp_specialpage.htm');
		} else {
			$page = $db->get_by('*', 'items', "id='$get_id'");
			if(empty($page)) adminmsg($lan['parameterwrong'], '', 3, 1);
			if(empty($post_pagename) || empty($post_template) || empty($post_filename)) adminmsg($lan['allarerequired'], 'back', 3, 1);
			if(!preg_match('/^\//', $post_filename)) adminmsg($lan['pagepathroot'], 'back', 3, 1);
			if(!empty($post_filename) && strpos($post_filename, '.') === false) $post_filename .= $setting_htmlexpand;
			$filenamechecked = checkfilename($post_filename, 'noempty');
			if($filenamechecked != '') adminmsg($filenamechecked, 'back', 3, 1);
			$htmlfilename = htmlname(0, 0, $thetime, $post_filename);
			if($page = $db->get_by('id', 'filenames', "filename='$htmlfilename'")) {
				if($page != $get_id) adminmsg($lan['fileexist'], 'back', 6, 1);
			}
			$value = array(
				'title' => $post_pagename,
				'filename' => $post_filename,
				'template' => $post_template,
			);
			$db->update('items', $value, "id='$get_id'");
			if($db->get_by('*', 'texts', "itemid='$get_id'")) {
				if(empty($post_data)) {
					$db->delete('texts', "itemid='$get_id'");
				} else {
					$db->update('texts', array('text' => $post_data), "itemid='$get_id'");
				}
			} else {
				if(!empty($post_data)) {
					$db->insert('texts', array('text' => $post_data, 'itemid' => $get_id));
				}
			}
			if($db->get_by('*', 'filenames', "id='$get_id' AND type='page'")) {
				$value = array(
					'filename' => $htmlfilename
				);
				$db->update('filenames', $value, "id='$get_id' AND type='page'");
			} else {
				$value = array(
					'filename' => $htmlfilename,
					'type' => 'page',
					'id' => $get_id,
					'dateline' => $thetime,
					'page' => 0
				);
				$db->insert('filenames', $value);
			}
			if(!empty($post_type)) {
				if($post_type == 4) $post_minute = $post_distance;
				replaceintocrons($post_type, $post_day, $post_date, $post_hour, $post_minute, $get_id, 'page');
			} else {
				$db->delete('crons', "itemid='$get_id'");
			}
			updatecache('crons');
			batchhtml(array($get_id));
			adminmsg($lan['operatesuccess'], 'admincp.php?action=specialpages');
		}
	}
} elseif($get_action == 'deleteitem') {
	if(!isset($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong'], '', 3, 1);
	batchdeleteitem(array($get_id));
	adminmsg($lan['operatesuccess'], $_SERVER['HTTP_REFERER']);
} elseif($get_action == 'edititem') {
	if(!isset($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong'], '', 3, 1);
	if(!isset($post_title)) {
		foreach($settings as $key => $setting) {
			if(preg_match("/^item(.*)show$/is", $key, $matchs)) {
				$smarty->assign($matchs[1].'_show', $setting);
			}
		}
		$item = $db->get_by('*', 'items', "id='$get_id'");
		if($item === false) adminmsg($lan['parameterwrong'], '', 3, 1);
		if(empty($item['category'])) go("admincp.php?action=specialpages&id={$get_id}");
		$attachs = '';
		$query = $db->list_by('*', 'attachments', "itemid='$get_id'");
		while($attach = $db->fetch_array($query)) {
			$date = date('Y-m-d', $attach['dateline']);
			$attachs .= "<tr><td>{$attach['id']}</td><td><a href=\"{$homepage}{$attach['filename']}\" target=\"_blank\">".basename($attach['filename'])."</a>&nbsp;[<a href=\"javascript:delattach({$attach['id']})\">{$lan['del']}</a>][<a href=\"javascript:copyattachtag('{$attach['id']}')\">{$lan['ins']}</a>]</td><td>{$date}</td><td title=\"{$attach['description']}\">".(strlen($attach['description']) > 25 ? substr($attach['description'], 0, 60) : $attach['description'])."  </td><td align=\"right\">{$attach['filesize']}&nbsp;B</td></tr>";
		}
		$attachs = empty($attachs) ? "<tr><td colspan=\"10\">{$lan['attach_no']}</td></tr>" : $attachs;
		if(!$text = $db->get_by('text', 'texts', "itemid='$get_id' AND page=0")) $text = '';
		$selectcategories = get_select('category');
		$selectsections = get_select('section');
		$selecttemplates = get_select_templates();
		$smarty->assign('id', $get_id);
		$smarty->assign('item', $item);
		$smarty->assign('title', htmlspecialchars($item['title']));
		$smarty->assign('picture', $item['picture']);
		if(substr($item['picture'], 0, 7) == 'http://') {
			$smarty->assign('truepicture', $item['picture']);
		} else {
			$smarty->assign('truepicture', $homepage.$item['picture']);
		}
		$smarty->assign('richtext', $setting_richtext);
		$smarty->assign('orderby', $item['orderby']);
		$smarty->assign('attachflag', $item['attach']);
		$smarty->assign('titlecolor', $item['titlecolor']);
		$smarty->assign('titlestyle', $item['titlestyle']);
		$smarty->assign('shorttitle', $item['shorttitle']);
		$smarty->assign('digest', $item['digest']);
		$smarty->assign('keywords', $item['keywords']);
		$smarty->assign('attachs', $attachs);
		$smarty->assign('author', htmlspecialchars($item['author']));
		$smarty->assign('source', htmlspecialchars($item['source']));
		$smarty->assign('aimurl', $item['aimurl']);
		$smarty->assign('data', htmlspecialchars($text));
		$smarty->assign('category', htmlspecialchars($item['category']));
		$smarty->assign('section', htmlspecialchars($item['section']));
		$smarty->assign('selectcategories', $selectcategories);
		$smarty->assign('selectsections', $selectsections);
		$smarty->assign('selecttemplates', $selecttemplates);
		$smarty->assign('filename', htmlspecialchars($item['filename']));
		$smarty->assign('template', htmlspecialchars($item['template']));
		$smarty->assign('commentnum', $item['commentnum']);
		$smarty->assign('maxattachsize', $setting_maxattachsize * 1024);
		$smarty->assign('usefilename', $setting_usefilename);
		if(!empty($item['ext'])) {
			$extrecord = ak_unserialize($db->get_by('value', 'item_exts', "id='$get_id'"));
			if(is_array($extrecord)) {
				foreach($extrecord as $k => $v) {
					$extrecord[$k] = addslashes($v);
				}
				$smarty->assign('extrecord', $extrecord);
			}
		}
		displaytemplate('admincp_item_edit.htm');
	} else {
		if(empty($post_title)) adminmsg($lan['notitle'], 'back', 3, 1);
		!isset($post_shorttitle) && $post_shorttitle = '';
		!isset($post_filename) && $post_filename = '';
		!isset($post_order) && $post_order = 0;
		!isset($post_order2) && $post_order2 = 0;
		!isset($post_order3) && $post_order3 = 0;
		!isset($post_order4) && $post_order4 = 0;
		!isset($post_section) && $post_section = 0;
		!isset($post_author) && $post_author = '';
		!isset($post_source) && $post_source = '';
		!isset($post_template) && $post_template = '';
		!isset($post_filename) && $post_filename = '';
		!isset($post_html) && $post_html = 1;
		!isset($post_titlecolor) && $post_titlecolor = '';
		!isset($post_titlestyle) && $post_titlestyle = '';
		!isset($post_aimurl) && $post_aimurl = '';
		!isset($file_attach['name']) && $file_attach['name'] = array();
		!isset($file_attach['tmp_name']) && $file_attach['tmp_name'] = array();
		!a_is_int($post_order) && $post_order = 0;
		if($setting_usefilename) {
			if(!empty($post_filename) && strpos($post_filename, '.') === false && substr($post_filename, strlen($setting_htmlexpand) * -1) != $setting_htmlexpand) $post_filename .= $setting_htmlexpand;
			$filenamechecked = checkfilename($post_filename);
			if($filenamechecked != '') adminmsg($filenamechecked, 'back', 3, 1);
			if(!empty($post_filename)) {
				$htmlfilename = htmlname($get_id, $post_category, $thetime, $post_filename);
				if($existfile = $db->get_by('*', 'filenames', "filename='$htmlfilename'")) {
					if($existfile['type'] != 'item' || $existfile['id'] != $get_id) {
						adminmsg($lan['fileexist'], 'back', 6, 1);
					}
				}
			}
		}
		!isset($post_keywords) && $post_keywords = '';
		$post_keywords = str_replace(' ', ',', $post_keywords);
		!isset($post_digest) && $post_digest = '';
		!isset($post_shorttitle) && $post_shorttitle = '';
		!isset($post_titlestyle) && $post_titlestyle = '';
		!isset($post_titlecolor) && $post_titlecolor = '';
		!isset($post_aimurl) && $post_aimurl = '';
		$item = $db->get_by('*', 'items', "id='$get_id'");
		$filenames = array();
		foreach($file_attach['name'] as $id => $a) {
			if(!empty($a)) {
				$filenames[$id] = get_upload_filename($file_attach['name'][$id], $get_id, $post_category);
				if(in_array(fileext($file_attach['name'][$id]), array('php'))) adminmsg($lan['attachexterror'], 'back', 3, 1);
				if($file_attach['error'][$id] == 2) adminmsg($lan['attachtoobig'][0].$setting_maxattachsize.$lan['attachtoobig'][1], 'back', 3, 1);
			}
		}
		$hasattach = 0;
		foreach($file_attach['tmp_name'] as $id => $a) {
			if(!empty($a)) {
				$filename = $filenames[$id];
				if(uploadtmpfile($a, FORE_ROOT.$filename)) {
					if(ispicture($filename)) addwatermark(FORE_ROOT.$filename);
					$value = array(
						'itemid' => $get_id,
						'filename' => $filename,
						'filesize' => $file_attach['size'][$id],
						'description' => $post_description[$id],
						'dateline' => $thetime
					);
					$db->insert('attachments', $value);
					$hasattach = 1;
				}
			}
		}
		$picture = $item['picture'];
		if(!empty($post_newpicture)) {
			$picture = $post_newpicture;
		} elseif(!empty($file_uploadpicture['name'])) {
			$headpicext = fileext($file_uploadpicture['name']);
			if(!ispicture($file_uploadpicture['name'])) adminmsg($lan['pictureexterror'], 'back', 3, 1);
			$filename = get_upload_filename($file_uploadpicture['name'], 0, $post_category, 'preview');
			if(uploadfile($file_uploadpicture, FORE_ROOT.$filename)) $picture = $filename;
		} elseif(!empty($post_picture) && $post_picture == 'del'){
			$picture = '';
		}
		if(isset($picture) && $item['picture'] != $picture) {
			if(preg_match('/^headpic\//', $item['picture'])) {
				@unlink(FORE_ROOT.$item['picture']);
			}
		}
		if($item['filename'] != $post_filename || $post_category != $item['category']) {
			@unlink(FORE_ROOT.htmlname($get_id, $item['category'], $item['dateline'], $item['filename']));
		}
		//ext start
		$ext = 0;
		$extvalue = array();
		foreach($_POST as $key => $value) {
			if(strpos($key, 'ext_') === 0) {
				$key = substr($key, 4);
				$extvalue[$key] = $value;
			}
		}
		if(!empty($extvalue)) {
			$ext = 1;
			$extvalue = serialize($extvalue);
		}
		$extinsertvalue = array('value' => $extvalue);
		$extinsertvalue['id'] = $get_id;
		$db->replace('item_exts', $extinsertvalue);
		//ext end
		$value = array(
			'title' => $post_title,
			'orderby' => $post_order,
			'orderby2' => $post_order2,
			'orderby3' => $post_order3,
			'orderby4' => $post_order4,
			'author' => $post_author,
			'source' => $post_source,
			'category' => $post_category,
			'section' => $post_section,
			'filename' => $post_filename,
			'template' => $post_template,
			'attach' => $hasattach,
			'picture' => $picture,
			'digest' => $post_digest,
			'keywords' => $post_keywords,
			'shorttitle' => $post_shorttitle,
			'titlestyle' => $post_titlestyle,
			'titlecolor' => $post_titlecolor,
			'aimurl' => $post_aimurl,
			'ext' => $ext,
			'lastupdate' => $thetime
		);
		$db->update('items', $value, "id='$get_id'");
		if(empty($post_data)) {
			$db->delete('texts', "itemid='$get_id'");
		} else {
			$data = array(
				'text' => $post_data,
			);
			if($db->get_by('*', 'texts', "itemid='$get_id'")) {
				$db->update('texts', $data, "itemid='$get_id'");
			} else {
				$data['itemid'] = $get_id;
				$db->insert('texts', $data);
			}
		}
		if($item['category'] != $post_category) refreshitemnum(array($item['category'], $post_category), 'category');
		if($item['section'] != $post_section) refreshitemnum(array($item['section'], $post_section), 'section');
		if($setting_usefilename) {
			$filename = htmlname($get_id, $post_category, $thetime, $post_filename);
			if(!empty($filename)) {
				$values = array(
					'filename' => $filename,
					'dateline' => $thetime
				);
				$distinctvalues = array(
					'type' => 'item',
					'id' => $get_id,
					'page' => 0
				);
				$db->replace('filenames', $values, $distinctvalues);
			}
		}
		batchhtml(array($get_id));
		adminmsg($lan['operatesuccess'], 'admincp.php?action=items');
	}
} elseif($get_action == 'comments') {
	(empty($get_id) || !a_is_int($get_id)) && adminmsg($lan['parameterwrong'], '', 3, 1);
	$item = $db->get_by('*', 'items', "id='$get_id'");
	$str_item = "<tr bgcolor='#FFFFFF'><td><a href='admincp.php?action=edititem&id={$get_id}'>{$item['title']}</a></td></tr>";
	$smarty->assign('item', $str_item);
	$query = $db->list_by('*', 'comments', "itemid='$get_id'", 'dateline');
	$str_comments = '';
	$i = 0;
	while($comment = $db->fetch_array($query)) {
		$i ++;
		$str_comments .= "<tr bgcolor='#FFFFFF'><td>{$lan['title']}:".htmlspecialchars($comment['title'])."&nbsp;|
		{$lan['name']}:".htmlspecialchars($comment['username'])."&nbsp;|
		{$lan['time']}:".date('Y-m-d H:i:s', $comment['dateline'])."&nbsp;|
		{$lan['ip']}:{$comment['ip']}</td></tr>
		<tr bgcolor='#FFFFFF'><td>".htmlspecialchars($comment['message'])."<hr><a href='admincp.php?action=deletecomment&id={$comment['id']}&itemid={$comment['itemid']}' onclick='return confirmdelete()'>{$lan['delete']}</a>&nbsp;<a href='admincp.php?action=commentdenyip&ip={$comment['ip']}&itemid={$comment['itemid']}' onclick='return confirmdenyip()'>{$lan['denyip']}</a></td></tr>";
	}
	if($i != $item['commentnum']) {
		$value = array(
			'commentnum' => $i
		);
		$db->update('items', $value, "id='$get_id'");
	}
	$str_comments == '' && $str_comments = "<tr bgcolor='#FFFFFF'><td>{$lan['commentempty']}</td></tr>";
	$smarty->assign('comments', $str_comments);
	displaytemplate('admincp_comments.htm');
} elseif($get_action == 'deletecomment') {
	(empty($get_id) || !a_is_int($get_id)) && adminmsg($lan['parameterwrong'], '', 3, 1);
	$db->delete('comments', "id='{$get_id}'");
	refreshcommentnum($get_itemid);
	adminmsg($lan['operatesuccess'], 'admincp.php?action=comments&id='.$get_itemid);
} elseif($get_action == 'commentdenyip') {
	empty($get_ip) && adminmsg($lan['parameterwrong'], '', 3, 1);
	$commentdenyips_data = readfromfile($comment_deny_ip_dic);
	$commentdenyips = explode("\n", $commentdenyips_data);
	if(!in_array($get_ip, $commentdenyips)) {
		if($commentdenyips_data == '') {
			$commentdenyips_data = $get_ip;
		} else {
			$commentdenyips_data = "\n".$get_ip;
		}
		writetofile($commentdenyips_data, $comment_deny_ip_dic);
	}
	deletecommentbyip($get_ip);
	refreshcommentnum($get_itemid);
	adminmsg($lan['operatesuccess'], 'admincp.php?action=comments&id='.$get_itemid);
} elseif($get_action == 'preview') {
	if(empty($get_id)) adminmsg($lan['parameterwrong'], '', 3, 1);
	$item = $db->get_by('*', 'items', "id='{$get_id}'");
	$targeturl = htmlurl($item['id'], $item['category'], $item['dateline'], $item['filename']);
	if(empty($targeturl)) exit;
	go($targeturl, 0);
} elseif($get_action == 'createhtml') {
	if(empty($get_id) || !isset($get_category)) showalert($lan['parameterwrong']);
	includecache('categories');
	if($get_category != 0 && ($categories[$get_category]['html'] == -1 || ($setting_html == 0 && $categories[$get_category]['html'] == 0))) showalert($lan['functiondisabled']);
	batchhtml(array($get_id));
	showalert($lan['operatesuccess']);
} elseif($get_action == 'templates') {
	checkcreator();
	if(!isset($post_templatename)) {
		$str_maintemplates = '';
		$str_subtemplates = '';
		$dh  = opendir($templatedir);
		$files = array();
		while(false !== ($filename = readdir($dh))) {
			if($filename != '.' && $filename != '..') $files[] = $filename;
		}
		list($i, $j) = array(0, 0);
		sort($files);
		foreach($files as $id => $file) {
			if(substr($file, 0, 1) == ',') {
				if(substr($file, -4) != '.htm') continue;
				$i ++;
				$file = substr($file, 1);
				$str_maintemplates .= "<tr><td>{$i}</td>
					<td><a href=\"admincp.php?action=template&template=,{$file}\">{$file}&nbsp;{$lan['edit']}</a></td>";
			} else {
				if(substr($file, 0, 1) == '.') continue;
				if(substr($file, -4) != '.htm') continue;
				$j ++;
				$str_subtemplates .= "<tr><td>{$j}</td>
					<td><a href=\"admincp.php?action=template&template={$file}\">{$file}&nbsp;{$lan['edit']}</a></td>
				</tr>";
			}
		}
		$smarty->assign('str_maintemplates', $str_maintemplates);
		$smarty->assign('str_subtemplates', $str_subtemplates);
		displaytemplate('admincp_templates.htm');
	} else {
		if(empty($post_templatename) || !preg_match('/^[0-9a-zA-Z_]+$/i', $post_templatename)) adminmsg($lan['templatenameerror'], 'back', 3, 1);
		$prefix = $post_prefix;
		$filename = $templatedir.$prefix.$post_templatename.'.htm';
		if(file_exists($filename)) adminmsg($lan['templateexit'] , 'back', 3, 1);
		$text = $lan['newtemplate'];
		if(!writetofile($text, $filename)) adminmsg($lan['cantcreatetemplate'] , 'back', 3, 1);
		updatecache('templates');
		go('admincp.php?action=templates');
	}
}elseif($get_action == 'template') {
	checkcreator();
	if(!isset($get_job)) {
		if(!is_writable($templatedir.$get_template)) adminmsg($lan['templatenotwritable'], '', 3, 1);
		$str_template = htmlspecialchars(readfromfile($templatedir.$get_template));
		$smarty->assign('str_template', $str_template);
		$smarty->assign('templatename', $templatedir.$get_template);
		$smarty->assign('template', $get_template);
		displaytemplate('admincp_template.htm');
	} elseif($get_job == 'delete') {
		$filename = $templatedir.$get_template;
		if(preg_match('/^,/i', $get_template)) {
			$template = substr($get_template, 1);
			if($db->get_by('*', 'items', "template='$template'")) adminmsg($lan['deltemplatehasused'], 'admincp.php?action=templates', 3, 1);
		}
		if(!file_exists($filename)) adminmsg($lan['notemplate'] , 'admincp.php?action=templates');
		if(unlink($filename) === false) {
			adminmsg($lan['cantdeltemplate'] , 'admincp.php?action=templates');
		} else {
			adminmsg($lan['operatesuccess'] , 'admincp.php?action=templates');
		}
	} elseif($get_job == 'save') {
		if(!is_writable($templatedir.$post_template)) adminmsg($lan['templatenotwritable'], '', 3, 1);
		if(!writetofile($post_html, $templatedir.$post_template)) adminmsg($lan['templatenotwritable'], '', 3, 1);
		adminmsg($lan['operatesuccess'], 'admincp.php?action=templates');
	}
	updatecache('templates');
}elseif($get_action == 'variables') {
	checkcreator();
	if(!isset($get_job)) {
		$query = $db->list_by('*', 'variables');
		$str_variables = '';
		$i = 0;
		while($v = $db->fetch_array($query)) {
			$i ++;
			$str_variables .= "<form action=\"admincp.php?action=variables&job=edit\" method=\"post\">
				<tr><td width=\"30\">{$i}</td>
					<td width=\"120\">{$v['variable']}<input type=\"hidden\" value=\"{$v['variable']}\" name=\"variable\"></td>
					<td><textarea cols=\"30\" rows=\"3\" name=\"description\" class=\"mustoffer\" onfocus=\"must(this)\">".htmlspecialchars($v['description'])."</textarea></td>
					<td><textarea cols=\"30\" rows=\"3\" name=\"value\" class=\"mustoffer\" onfocus=\"must(this)\">".htmlspecialchars($v['value'])."</textarea></td>
					<td><input type=\"submit\" name=\"edit\" value=\"{$lan['edit']}\"></td>";
			$str_variables .= "<td><input type=\"button\" name=\"edit\" value=\"{$lan['delete']}\" onclick=\"deletevariable('{$v['variable']}')\"></td>";
			$str_variables .= "</tr></form>";
		}
		$smarty->assign('variables', $str_variables);
		displaytemplate('admincp_variables.htm');
	} elseif($get_job == 'new') {
		if(!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/i', $post_variable)) adminmsg($lan['variablenamerror'], 'back', 3, 1);
		if($db->get_by('*', 'variables', "variable='$post_variable'")) adminmsg($lan['variableexist'], 'back', 3, 1);
		$value = array(
			'variable' => $post_variable,
			'description' => $post_description,
			'value' => $post_value
		);
		$db->insert('variables', $value);
		updatecache('globalvariables');
		go('admincp.php?action=variables');
	} elseif($get_job == 'edit') {
		$value = array(
			'value' => $post_value,
			'description' => $post_description
		);
		$db->update('variables', $value, "variable='$post_variable'");
		updatecache('globalvariables');
		go('admincp.php?action=variables');
	} elseif($get_job == 'delete') {
		$db->delete('variables', "variable='$get_variable'");
		updatecache('globalvariables');
		go('admincp.php?action=variables');
	}
} elseif($get_action == 'spiders') {
	if(!isset($get_job)) {
		includecache('categories');
		includecache('sections');
		includecache('spiderrules');
		$query = $db->list_by('*', 'spiders');
		$spiderslist = '';
		while($spider = $db->fetch_array($query)) {
			if($spider['lasttime'] == 0) {
				$lasttime = '-';
			} else {
				$lasttime = date('Y-m-d H:i:s', $spider['lasttime']);
			}
			$spiderslist .= "<tr><td>{$spider['id']}</td>
			<td><a href=\"admincp.php?action=spiders&job=editspider&id={$spider['id']}\">{$spider['spidername']}</a></td>
			<td>{$lasttime}</td><td>".$spiderrules[$spider['rule']]['spiderrulename']."</td>
			<td><a href=\"admincp.php?action=spiders&job=previewspider&id={$spider['id']}\" target=\"_blank\">{$lan['preview']}</a></td>
			<td><a href=\"admincp.php?action=spiders&job=runnow&id={$spider['id']}\" target=\"_blank\">{$lan['runnow']}</a></td>
			<td><a href=\"javascript:deletespider({$spider['id']})\">".alert($lan['delete'])."</td></tr>";
		}
		if(empty($spiderslist)) $spiderslist = "<tr><td colspan=\"10\">".$lan['nospider']."</td></tr>";
		$query = $db->list_by('*', 'spiderrules');
		$spiderruleslist = '';
		while($rule = $db->fetch_array($query)) {
			$spiderruleslist .= "<tr><td>{$rule['id']}</td>";
			$spiderruleslist .= "<td><a href=\"admincp.php?action=spiders&job=editrule&id={$rule['id']}\">{$rule['spiderrulename']}</a></td>";
			$spiderruleslist .= "<td>{$rule['orderby']}</td><td><a href=\"admincp.php?action=spiders&job=previewrule&id={$rule['id']}\" target=\"_blank\">{$lan['preview']}</a></td><td><a href=\"javascript:deleterule({$rule['id']})\">".alert($lan['delete'])."</a></td></tr>";
		}
		if(empty($spiderruleslist)) $spiderruleslist = "<tr><td colspan=\"10\">".$lan['nospiderrule']."</td></tr>";
		$selectcategories = get_select('category');
		$selectsections = get_select('section');
		$selecttemplates = get_select_templates();
		$selectrules = get_select('spiderrules');
		$smarty->assign('selectcategories', $selectcategories);
		$smarty->assign('selectsections', $selectsections);
		$smarty->assign('selecttemplates', $selecttemplates);
		$smarty->assign('selectrules', $selectrules);
		$smarty->assign('spiderruleslist', $spiderruleslist);
		$smarty->assign('spiderslist', $spiderslist);
		//ext
		$exts = '';
		$extfields = extfields();
		foreach($extfields as $ext) {
			$exts .= '<tr><td width="50">'.$ext['name'].'</td><td><input type="text" size="30" name="ext_'.$ext['alias'].'"></td></tr>';
		}
		$smarty->assign('exts', $exts);
		//ext

		$fields = '';
		for($i = 0; $i < 20; $i ++) {
			$num = $i + 1;
			if($num <= 1) {
				$class = " class=\"mustoffer\" onfocus=\"must(this)\"";
			} else {
				$class = '';
			}
			$fields .= "<tr><td>field{$num}{$lan['starttag']}</td><td><input type=\"text\" value=\"\" name=\"fieldstart[]\" size=\"60\"{$class}></td></tr><tr bgcolor=\"#F8F8F8\"><td>field{$num}{$lan['endtag']}</td><td><input type=\"text\" value=\"\" name=\"fieldend[]\" size=\"60\"{$class}></td></tr>";
		}
		$smarty->assign('fields', $fields);
		displaytemplate('admincp_spiders.htm');
	} elseif($get_job == 'addspider') {
		if(!a_is_int($post_rule) || empty($post_spidername) || empty($post_listurl)) adminmsg($lan['spiderinforequired'], 'back', 3, 1);
		$data = array(
			'category' => $_POST['category'],
			'section' => $_POST['section'],
			'listurl' => $_POST['listurl'],
			'startid' => $_POST['startid'],
			'endid' => $_POST['endid'],
			'start' => $_POST['start'],
			'end' => $_POST['end'],
			'urlcharacter' => $_POST['urlcharacter'],
			'urlskip' => $_POST['urlskip'],
			'titlecharacter' => $_POST['titlecharacter'],
			'titleskip' => $_POST['titleskip']
		);
		$data = serialize($data);
		$value = array(
			'spidername' => $post_spidername,
			'rule' => $post_rule,
			'lasttime' => 0,
			'data' => $data
		);
		$db->insert('spiders', $value);
		$spiderid = $db->insert_id();
		if(!empty($post_type)) {
			if($post_type == 4) $post_minute = $post_distance;
			replaceintocrons($post_type, $post_day, $post_date, $post_hour, $post_minute, $spiderid, 'spider');
			updatecache('crons');
		}
		updatecache('spiders');
		adminmsg($lan['operatesuccess'], 'admincp.php?action=spiders');
	} elseif($get_job == 'editspider') {
		if(!isset($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong'], 'back');
		if(isset($post_saveeditspider)) {
			if(!a_is_int($post_rule) || empty($post_spidername) || empty($post_listurl)) adminmsg($lan['spiderinforequired'], 'back', 3, 1);
			$data = array(
				'category' => $_POST['category'],
				'section' => $_POST['section'],
				'listurl' => $_POST['listurl'],
				'startid' => $_POST['startid'],
				'endid' => $_POST['endid'],
				'start' => $_POST['start'],
				'end' => $_POST['end'],
				'urlcharacter' => $_POST['urlcharacter'],
				'urlskip' => $_POST['urlskip'],
				'titlecharacter' => $_POST['titlecharacter'],
				'titleskip' => $_POST['titleskip']
			);
			$data = serialize($data);
			$value = array(
				'spidername' => $post_spidername,
				'rule' => $post_rule,
				'data' => $data,
			);
			$db->update('spiders', $value, "id='$get_id'");
			if(!empty($post_type)) {
				if($post_type == 4) $post_minute = $post_distance;
				replaceintocrons($post_type, $post_day, $post_date, $post_hour, $post_minute, $get_id, 'spider');
			} else {
				$db->delete('crons', "itemid='$get_id' AND job='spider'");
			}
			updatecache('crons');
			updatecache('spiders');
			adminmsg($lan['operatesuccess'], 'admincp.php?action=spiders');
		}
		$spider = $db->get_by('*', 'spiders', "id='{$get_id}'");
		list($day, $date, $hour, $minute, $distance, $type) = array(0, 1, '', '', '', 0);
		if($cron = $db->get_by('*', 'crons', "itemid='$get_id' AND job='spider'")) {
			$type = $cron['type'];
			if($type > 0 && $type < 4) {
				$hour = $cron['hour'];
				$minute = $cron['minute'];
			}
			if($type == 2) $date = $cron['date'];
			if($type == 3) $day = $cron['day'];
			if($type == 4) $distance = $cron['minute'];
		}
		$selectcategories = get_select('category');
		$selectsections = get_select('section');
		$selectrules = get_select('spiderrules');
		$smarty->assign('selectrules', $selectrules);
		$smarty->assign('selectcategories', $selectcategories);
		$smarty->assign('selectsections', $selectsections);
		$smarty->assign('forbidautorefresh', $setting_forbidautorefresh);
		$smarty->assign('id', $get_id);
		$smarty->assign('spidername', $spider['spidername']);
		$smarty->assign('rule', $spider['rule']);
		$data = unserialize($spider['data']);
		$data = ak_htmlspecialchars($data);
		$smarty->assign('category', $data['category']);
		$smarty->assign('section', $data['section']);
		$smarty->assign('listurl', $data['listurl']);
		$smarty->assign('startid', $data['startid']);
		$smarty->assign('endid', $data['endid']);
		$smarty->assign('start', $data['start']);
		$smarty->assign('end', $data['end']);
		$smarty->assign('urlcharacter', $data['urlcharacter']);
		$smarty->assign('urlskip', $data['urlskip']);
		$smarty->assign('titlecharacter', $data['titlecharacter']);
		$smarty->assign('titleskip', $data['titleskip']);
		$smarty->assign('date', $date);
		$smarty->assign('day', $day);
		$smarty->assign('hour', $hour);
		$smarty->assign('minute', $minute);
		$smarty->assign('distance', $distance);
		$smarty->assign('type', $type);
		displaytemplate('admincp_spider.htm');
	} elseif($get_job == 'addrule') {
		if(empty($post_update)) {
			if(empty($post_rulename)) adminmsg($lan['norulename'], 'back', 3, 1);
			if(empty($post_exampleurl)) adminmsg($lan['noexampleurl'], 'back', 3, 1);
			if(empty($post_data)) adminmsg($lan['nodata'], 'back', 3, 1);
			if(empty($post_title)) adminmsg($lan['notitle'], 'back', 3, 1);
			if(empty($post_fieldstart[0]) || empty($post_fieldend[0])) adminmsg($lan['nofield1'], 'back', 3, 1);
		}
		$value = array(
			'spiderrulename' => $post_rulename,
			'exampleurl' => $post_exampleurl,
			'title' => $post_title,
			'aimurl' => $post_aimurl,
			'shorttitle' => $post_shorttitle,
			'author' => $post_author,
			'source' => $post_source,
			'editor' => $post_editor,
			'orderby' => $post_order,
			'html' => $post_html,
			'digest' => $post_digest,
			'keywords' => $post_keywords,
			'filename' => $post_filename,
			'picture' => $post_picture,
			'text' => $post_data,
			'replace' => $post_replace,
			'character' => $post_character,
			'skip' => $post_skip,
		);
		for($i = 1; $i <= 20; $i ++) {
			$field = "<{$post_fieldstart[$i - 1]}>\n<{$post_fieldend[$i - 1]}>";
			$value["field{$i}"] = $field;
		}
		//ext start
		foreach($_POST as $k => $v) {
			if(strpos($k, 'ext_') === 0) {
				$k = substr($k, 4);
				$extvalue[$k] = $v;
			}
		}
		if(!empty($extvalue)) {
			$ext = 1;
			$value['extfields'] = serialize($extvalue);
		}
		//ext end
		$db->insert('spiderrules', $value);
		$ruleid = $db->insert_id();
		updatecache('spiderrules');
		go("admincp.php?action=spiders&job=editrule&id={$ruleid}");
	} elseif($get_job == 'editrule') {
		if(empty($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong']);
		$rule = $db->get_by('*', 'spiderrules', "id='$get_id'");
		$extfieldsrule = ak_unserialize($rule['extfields']);
		$fields = '';
		for($i = 0; $i < 20; $i ++) {
			$num = $i + 1;
			$rule['field'.$num.'_start'] = get_rule_field($rule['field'.$num]);
			$rule['field'.$num.'_end'] = get_rule_field($rule['field'.$num], 1);
			$start = htmlspecialchars($rule['field'.$num.'_start']);
			$end = htmlspecialchars($rule['field'.$num.'_end']);
			$fields .= "<tr><td>field{$num}{$lan['starttag']}</td><td><input type=\"text\" value=\"{$start}\" name=\"fieldstart[]\" size=\"60\"></td></tr><tr bgcolor=\"#F8F8F8\"><td>field{$num}{$lan['endtag']}</td><td><input type=\"text\" value=\"{$end}\" name=\"fieldend[]\" size=\"60\"></td></tr>\n";
		}
		$selecttemplates = get_select_templates();
		$smarty->assign('selecttemplates', $selecttemplates);
		$smarty->assign('content', !empty($content) ? htmlspecialchars($content) : '');
		$rule = ak_htmlspecialchars($rule);
		$smarty->assign('rule', $rule);
		$smarty->assign('fields', $fields);
		$smarty->assign('id', $get_id);

		//ext
		$exts = '';
		$extfields = extfields();
		foreach($extfields as $ext) {
			$v = '';
			if(isset($extfieldsrule[$ext['alias']])) $v = $extfieldsrule[$ext['alias']];
			$exts .= '<tr><td width="50">'.$ext['name'].'</td><td><input type="text" value="'.$v.'" size="30" name="ext_'.$ext['alias'].'"></td></tr>';
		}
		$smarty->assign('exts', $exts);
		//ext

		displaytemplate('admincp_spiderrule.htm');
	} elseif($get_job == 'previewrule') {
		if(empty($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong']);
		$rule = $db->get_by('*', 'spiderrules', "id='$get_id'");
		$result = spiderurl($get_id, $rule['exampleurl']);
		foreach($result as $key => $value) {
			if($key == 'text') continue;
			$result[$key] = htmlspecialchars($value);
		}
		$result['text'] = nl2br($result['text']);
		$smarty->assign('result', $result);
		displaytemplate('admincp_spiderrulepreview.htm');
	} elseif($get_job == 'saveeditrule') {
		if(empty($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong']);
		if(empty($post_rulename)) adminmsg($lan['norulename'], 'back', 3, 1);
		if(empty($post_exampleurl)) adminmsg($lan['noexampleurl'], 'back', 3, 1);
		if(empty($post_data)) adminmsg($lan['nodata'], 'back', 3, 1);
		if(empty($post_title)) adminmsg($lan['notitle'], 'back', 3, 1);
		if(empty($post_fieldstart[0]) || empty($post_fieldend[0])) adminmsg($lan['nofield1'], 'back', 3, 1);
		$value = array(
			'spiderrulename' => $post_rulename,
			'exampleurl' => $post_exampleurl,
			'title' => $post_title,
			'aimurl' => $post_aimurl,
			'shorttitle' => $post_shorttitle,
			'author' => $post_author,
			'source' => $post_source,
			'editor' => $post_editor,
			'orderby' => $post_order,
			'html' => $post_html,
			'digest' => $post_digest,
			'keywords' => $post_keywords,
			'filename' => $post_filename,
			'picture' => $post_picture,
			'text' => $post_data,
			'replace' => $post_replace,
			'character' => $post_character,
			'skip' => $post_skip,
		);
		for($i = 1; $i <= 10; $i ++) {
			$field = "<{$post_fieldstart[$i - 1]}>\n<{$post_fieldend[$i - 1]}>";
			$value["field{$i}"] = $field;
		}
		//ext start
		foreach($_POST as $k => $v) {
			if(strpos($k, 'ext_') === 0) {
				$k = substr($k, 4);
				$extvalue[$k] = $v;
			}
		}
		if(!empty($extvalue)) {
			$ext = 1;
			$value['extfields'] = serialize($extvalue);
		}
		//ext end
		$db->update('spiderrules', $value, "id='$get_id'");
		updatecache('spiderrules');
		go("admincp.php?action=spiders&job=editrule&id={$get_id}");
	} elseif($get_job == 'delspider') {
		if(empty($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong']);
		$db->delete('spiders', "id='$get_id'");
		$db->delete('crons', "itemid='$get_id' AND job='spider'");
		updatecache('crons');
		updatecache('spiders');
		go('admincp.php?action=spiders');
	} elseif($get_job == 'delrule') {
		if(empty($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong']);
		$db->delete('spiderrules', "id='$get_id'");
		updatecache('spiderrules');
		go('admincp.php?action=spiders');
	} elseif($get_job == 'runnow') {
		if(empty($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong']);
		operatespiderlist($get_id);
		go($homepage.'akcms_inc.php?action=spider');
	} elseif($get_job == 'previewspider') {
		if(empty($get_id) || !a_is_int($get_id)) adminmsg($lan['parameterwrong']);
		$preview = nl2br(operatespiderlist($get_id, 0));
		$smarty->assign('preview', $preview);
		displaytemplate('admincp_spiderpreview.htm');
	}
} elseif($get_action == 'createcategory') {
	includecache('settings');
	if(empty($setting_html)) adminmsg($lan['createhtml'].$lan['functiondisabled'].'<br><br><a href="setting.php?action=functions">'.$lan['open'].'</a>', '', 0, 1);
	if(isset($get_id)) {
		if(empty($get_id)) {
			$query = $db->list_by('*', 'categories', '', 'ID');
			$categories = array();
			$batchcategories = array();
			foreach($categories as $c) {
				$batchcategories[] = $c['id'];
			}
			batchcategoryhtml($batchcategories);
			adminmsg($lan['operatesuccess'], 'admincp.php?action=createcategory');
		} else {
			if(!isset($get_job) || $get_job == 'default') {
				batchcategoryhtml($get_id);
				adminmsg($lan['operatesuccess'], 'admincp.php?action=createcategory');
			} elseif($get_job == 'page') {
				batchcategorypagehtml($get_id);
				adminmsg($lan['operatesuccess'], 'admincp.php?action=createcategory&job=process');
			}
		}
	} elseif(isset($post_cid)) {
		foreach($post_cid as $cid) {
			batchcategoryhtml($cid);
		}
		adminmsg($lan['operatesuccess'], 'admincp.php?action=createcategory');
	} elseif(isset($get_job) && $get_job == 'process') {
		if(operatecreatecategoryprocess() === true) {
			adminmsg($lan['operatesuccess']);
		} else {
			adminmsg($lan['operatesuccess'], 'admincp.php?action=createcategory&job=process', 0);
		}
	} else {
		includecache('categories');
		$categorieslist = '';
		foreach($categories as $c) {
			if($c['html'] == 1 || ($setting_html && $c['html'] == 0)) {
				$categorieslist .= "<tr><td><input type=\"checkbox\" name=\"cid[]\" value=\"{$c['id']}\"></td>";
				$categorieslist .= "<td><a href='admincp.php?action=editcategory&id=2'>{$c['category']}</a></td>";
				$categorieslist .= "<td><a href=\"admincp.php?action=createcategory&id={$c['id']}\">{$lan['createcategorydefault']}</a></td>";
				$categorieslist .= "<td><a href=\"admincp.php?action=createcategory&id={$c['id']}&job=page\">{$lan['createcategorylist']}</a></td></tr>";
			}
		}
		$smarty->assign('categorieslist', $categorieslist);
		displaytemplate('admincp_createcategory.htm');
	}
} elseif($get_action == 'createitem') {
	includecache('settings');
	if(empty($setting_html)) adminmsg($lan['createhtml'].$lan['functiondisabled'].'<br><br><a href="setting.php?action=functions">'.$lan['open'].'</a>', '', 0, 1);
	if(isset($get_category)) {
		if(!empty($get_category)) {
			$where = "category='$get_category'";
		} else {
			$where = "category>0";
		}
		if(empty($get_step)) $get_step = 10;
		$query = $db->list_by('id', 'items', $where);
		$items = array();
		$i = 0;
		while($item = $db->fetch_array($query)) {
			$items[] = $item['id'];
			$i ++;
		}
		$all = count($items);
		$items = implode('|', $items);
		if($i == 0) {
			adminmsg($lan['noitem'], 'admincp.php?action=createitem');
		} else {
			writetofile($items, $batch_items_process);
			adminmsg($lan['batchitemready'][0].$i.$lan['batchitemready'][1], 'admincp.php?action=createitem&process=1&step='.$get_step.'&all='.$all, 3);
		}
	} else {
		if(!empty($get_process)) {
			if(empty($get_step)) $get_step = 10;
			if(empty($get_all)) $get_all = 0;
			$process = readfromfile($batch_items_process);
			$array_items = explode('|', $process);
			$countitems = count($array_items);
			if(!empty($array_items[0])) {
				$item = array_slice($array_items, 0, $get_step);
				$array_items[] = 0;//增加一个无用的数组元素，为下个函数做准备
				$array_items = array_slice($array_items, $get_step, -1);
				$left = count($array_items);
				$items = implode('|', $array_items);
				writetofile($items, $batch_items_process);
				batchhtml($item);
				$countitems = $countitems - 1;
				if(!empty($get_all)) {
					$finished = $get_all - $left;
					$finishedpercent = ceil(($finished / $get_all) * 100);
				}
				adminmsg($lan['batchiteminfo'][0].$finishedpercent.$lan['batchiteminfo'][1].$finished.$lan['batchiteminfo'][2],'admincp.php?action=createitem&process=1&step='.$get_step.'&all='.$get_all, 1);
			} else {//执行结束
				adminmsg($lan['operatesuccess']);
			}
		} else {//在url中既没有category也没有process开始标志，等待用户操作
			$categories = get_select('category');
			$smarty->assign('categories', $categories);
			displaytemplate('admincp_createitem.htm');
		}
	}
} elseif($get_action == 'createsection') {//debug
	includecache('settings');
	if(empty($setting_html)) adminmsg($lan['createhtml'].$lan['functiondisabled'].'<br><br><a href="setting.php?action=functions">'.$lan['open'].'</a>', '', 0, 1);
	if(isset($get_id)) {
		if(empty($get_id)) {
			includecache('sections');
			$query = $db->list_by('*', 'sections', '', 'id');
			$categories = array();
			$batchcategories = array();
			foreach($categories as $c) {
				$batchcategories[] = $c['id'];
			}
			batchsectionhtml($batchcategories);
			adminmsg($lan['operatesuccess'], 'admincp.php?action=createcategory');
		} else {
			if(!isset($get_job) || $get_job == 'default') {
				batchsectionhtml($get_id);
				adminmsg($lan['operatesuccess'], 'admincp.php?action=sections');
			} elseif($get_job == 'page') {
				batchsectionpagehtml($get_id);
				adminmsg($lan['operatesuccess'], 'admincp.php?action=createsection&job=process');
			}
		}
	} elseif(isset($post_cid)) {
		foreach($post_cid as $cid) {
			batchsectionhtml($cid);
		}
		adminmsg($lan['operatesuccess'], 'admincp.php?action=sections');
	} elseif(isset($get_job) && $get_job == 'process') {
		if(operatecreatesectionprocess() === true) {
			adminmsg($lan['operatesuccess']);
		} else {
			adminmsg($lan['operatesuccess'], 'admincp.php?action=createsection&job=process', 0);
		}
	}
} elseif($get_action == 'delattach') {
	if($attach = $db->get_by('*', 'attachments', "id='{$get_id}'")) {
		@unlink(FORE_ROOT.$attach['filename']);
		$db->delete('attachments', "id='{$get_id}'");
		if(!$db->get_by('*', 'attachments', "itemid='{$attach['itemid']}'")) {
			$db->update('items', array('attach' => 0), "id='{$attach['itemid']}'");
		}
		showalert($lan['attachdeleted']);
	} else {
		showalert($lan['attachnotfound']);
	}
} elseif($get_action == 'changepassword') {
	if(isset($get_submit)) {
		if($post_newpassword != $post_newpassword2) adminmsg($lan['repeatpassworderror'], 'back', 3, 1);
		if(empty($post_oldpassword) || empty($post_newpassword)) adminmsg($lan['passwordempty'], 'back', 3, 1);
		if($user = $db->get_by('*', 'admins', "editor='$admin_id'")) {
			if($user['password'] != md5($post_oldpassword)) adminmsg($lan['oldpassworderror'], 'back', 3, 1);
			$newpassword = md5($post_newpassword);
			$db->update('admins', array('password' => $newpassword), "editor='$admin_id'");
			adminmsg($lan['operatesuccess']);
		} else {
			adminmsg($lan['nothisuser'], 'back', 3, 1);
		}
	} else {
		displaytemplate('admincp_changepass.htm');
	}
} elseif($get_action == 'manageaccounts') {
	checkcreator();
	if(!isset($get_job)) {
		$query = $db->list_by('*', 'admins', '', 'id');
		$str_users = '';
		while($user = $db->fetch_array($query)) {
			if($user['editor'] != 'admin') {
				$status = empty($user['freeze']) ? available($lan['active']) : disabled($lan['frozen']);
				$changestatus = empty($user['freeze']) ? "<a href=\"admincp.php?action=manageaccounts&job=freeze&id={$user['id']}\">{$lan['freeze']}</a>" : "<a href=\"admincp.php?action=manageaccounts&job=active&id={$user['id']}\">{$lan['activate']}</a>";
				$reset = "<a href=\"admincp.php?action=manageaccounts&job=reset&id={$user['id']}\">".alert($lan['reset'])."</a>";
				if($user['items'] == 0) {
					$delete = "<a href=\"admincp.php?action=manageaccounts&job=delete&editor={$user['editor']}\">".alert($lan['delete'])."</a>";
				} else {
					$delete = "-";
				}
			} else {
				$status = available($lan['active']);
				$changestatus = '-';
				$reset = '-';
				$delete = "-";
			}
			$str_users .= "<tr>
			<td>{$user['editor']}</td>
			<td>{$delete}</td>
			<td>{$status}</td>
			<td>{$changestatus}</td>
			<td>{$reset}</td>
			<td class=\"mininum\">{$user['items']}</td>
			</tr>";
		}
		$smarty->assign('users', $str_users);
		displaytemplate('admincp_manageaccounts.htm');
	} elseif($get_job == 'newaccount') {
		if(empty($post_account) || empty($post_password)) adminmsg($lan['accountorpasswordempty'], 'back', 3, 1);
		if($db->get_by('*', 'admins', "editor='$post_account'")) adminmsg($lan['accountexist'], 'back', 3, 1);
		$value = array(
			'editor' => $post_account,
			'password' => md5($post_password)
		);
		$db->insert('admins', $value);
		adminmsg($lan['accoundpassword']."{$post_account}/{$post_password}<br>".$lan['operatesuccess'], 'admincp.php?action=manageaccounts');
	} elseif($get_job == 'freeze' || $get_job == 'active') {
		$array_admins_status = array(
			'freeze' => 1,
			'active' => 0
		);
		if(empty($get_id) || $get_id == 1) adminmsg($lan['parameterwrong'], 'back', 3, 1);
		$db->update('admins', array('freeze' => $array_admins_status[$get_job]), "id='$get_id'");
		adminmsg($lan['operatesuccess'], 'admincp.php?action=manageaccounts');
	} elseif($get_job == 'delete') {
		if(empty($get_editor) || $get_editor == 'admin') adminmsg($lan['parameterwrong'], 'back', 3, 1);
		if($db->get_by('*', 'items', "author='$get_editor'")) adminmsg($lan['accounthasitems'], 'back', 3, 1);
		$db->delete('admins', "editor='$get_editor'");
		adminmsg($lan['operatesuccess'], 'admincp.php?action=manageaccounts');
	} elseif($get_job == 'reset') {
		$default_password = 'akcms';
		if(empty($get_id) || $get_id == 1) adminmsg($lan['parameterwrong'], 'back', 3, 1);
		$password = md5($default_password);
		$db->update('admins', array('password' => $password), "id='$get_id'");
		adminmsg($lan['passwordreset'], 'admincp.php?action=manageaccounts');
	}
}elseif($get_action == 'logout') {
	setcookie('auth', '');
	refreshpv();
	adminmsg($lan['logout_success'], 'login.php');
} elseif($get_action == 'itemexts') {
	$extfields = unserialize($settings['extfields']);
	if(!empty($get_job) && $get_job == 'clear') {
		$db->update('settings', array('value' => ''), "variable='extfields'");
		updatecache('settings');
	}
	if(!empty($get_job) && $get_job == 'newext') {
		if(isset($extfields[$post_alias])) adminmsg($lan['extfieldexist'], 'admincp.php?action=itemexts', 3, 1);
		$extfields[$post_alias] = array('alias' => $post_alias, 'name' => $post_name, 'type' => $post_type, 'standby' => $post_standby);
		$value = array('value' => serialize($extfields));
		$db->update('settings', $value, "variable='extfields'");
		updatecache('settings');
		go('admincp.php?action=itemexts');
	}
	$exts = '';
	if($extfields) {
		foreach($extfields as $key => $extfield) {
			$exts .= "<tr><td>{$extfield['name']}</td><td>{$key}</td><td>{$lan[$extfield['type']]}</td><td>".extfieldinput($extfield)."</td></tr>";
		}
	}
	$smarty->assign('exts', $exts);
	displaytemplate('admincp_itemexts.htm');
} elseif($get_action == 'manual') {
	debug('manual');
} elseif($get_action == 'setitemextfields') {
	if(empty($get_category)) exit();
	includecache('categories');
	$extfields = extfields();
	$category = $get_category;
	$itemextfields = explode(',', $categories[$category]['itemextfields']);
	$extfieldshtml = '';
	foreach($itemextfields as $field) {
		if(empty($field)) continue;
		$extfieldshtml .= '<tr><td width="50">'.$extfields[$field]['name'].'</td><td>'.extfieldinput($extfields[$field]).'</td></tr>';
	}
	if($extfieldshtml == '') aexit();
	echo('<table width="99%" border="0" cellpadding="5" cellspacing="1" class="commontable" align="center">
<tr class="header">
	<td colspan="2">'.$lan['extfields'].'</td>
</tr>');
	echo $extfieldshtml;
	aexit('</table>');
} else {
	adminmsg($lan['nodefined'], '', 0, 1);
}
aexit();
?>
<?php
function get_section_data($id) {
	global $template_path, $smarty, $db, $tablepre, $lan, $thetime, $system_root, $lr, $setting_keywordslink, $header_charset, $setting_homepage, $setting_defaultfilename, $setting_html, $sections, $setting_attachtemplate, $setting_storemethod, $html_smarty;
	includecache('sections');
	$variables = array();
	$variables['_pagetype'] = 'section';
	$variables['_pageid'] = $id;
	if(!isset($sections[$id])) return array();
	$variables['section'] = $id;
	$section = $sections[$id];
	$variables['sectionname'] = $section['section'];
	$variables['alias'] = $section['alias'];
	$variables['orderby'] = $section['orderby'];
	$variables['keywords'] = $section['keywords'];
	$variables['description'] = $section['description'];
	$variables['items'] = $section['items'];
	if($section['html'] == 0) {
		$variables['html'] = $setting_html;
	} else {
		$variables['html'] = $section['html'];
	}
	$variables['ipp'] = get_section_ipp($id);
	$total = $db->get_by('COUNT(id)', 'items', "section='$id'");
	$variables['total'] = $total;
	foreach($_GET as $key => $value) {
		$variables[$key] = $value;
	}
	$variables['hometemplate'] = get_section_template($id);
	$variables['pagetemplate'] = get_section_template($id, 'list');
	$variables['sectionhomemethod'] = get_section_homemethod($id);
	$variables['sectionpagemethod'] = get_section_pagemethod($id);
	return $variables;
}

function batchsectionhtml($ids) {
	if(!is_array($ids)) $ids = array($ids);
	foreach($ids as $id) {
		$variables = get_section_data($id);
		$filename = $variables['sectionhomemethod'];
		$filename = str_replace('[sectionalias]', $variables['alias'], $filename);
		$filename = str_replace('[sectionname]', $variables['sectionname'], $filename);
		$filename = str_replace('[sectionid]', $variables['section'], $filename);
		$variables['htmlfilename'] = FORE_ROOT.$filename;
		render_template($variables, $variables['hometemplate'], 1);
	}
}

function batchsectionpagehtml($ids) {
	global $db, $batch_sections_process;
	if(!is_array($ids)) $ids = array($ids);
	$process = '';
	foreach($ids as $id) {
		$items_total = $db->get_by('COUNT(*)', 'items', "section='$id'");
		$ipp = get_section_ipp($id);
		$maxid = ceil($items_total / $ipp);
		$process .= "$id,0,$maxid\n";
	}
	writetofile($process, $batch_sections_process);
}

function operatecreatesectionprocess() {
	global $batch_sections_process;
	$step = 50;
	$process = readfromfile($batch_sections_process);
	if(strlen($process) < 5) return true;
	if(strpos($process, ',') === false) return false;
	list($id, $page, $maxpage) = explode(',', $process);
	$stepmax = min($maxpage, $page + $step);
	$_pos1 = strpos($process, "\n");
	if($stepmax == $maxpage) {
		$process = substr($process, $_pos1);
	} else {
		$process = "$id,$stepmax,$maxpage".substr($process, $_pos1 + 1);
	}
	writetofile($process, $batch_sections_process);
	$variables = get_section_data($id);
	$variables['template'] = $variables['pagetemplate'];
	$pagemethod = str_replace('[sectionalias]', $variables['alias'], $variables['sectionpagemethod']);
	for($i = $page + 1; $i <= $stepmax; $i ++) {
		$variables['page'] = $i;
		$filename = str_replace('[page]', $i, $pagemethod);
		$variables['htmlfilename'] = FORE_ROOT.$filename;	
		render_template($variables, '', 1);
	}
}

function get_section_template($id, $type = '') {//home,list,rss
	global $sections;
	includecache('sections');
	$default_template = 'section_home.htm';
	$list_template = 'section_list.htm';
	if($type == '' || $type == 'home') {
		$template = empty($sections[$id]['defaulttemplate']) ? $default_template : $sections[$id]['defaulttemplate'];
	} elseif($type == 'list') {
		$template = empty($sections[$id]['listtemplate']) ? $list_template : $sections[$id]['listtemplate'];
	}
	return $template;
}

function get_section_homemethod($id) {
	global $setting_sectionhomemethod, $sections;
	$sectionhomemethod = $sections[$id]['sectionhomemethod'];
	if($sectionhomemethod == '') {
		$sectionhomemethod = $setting_sectionhomemethod;
	}
	return $sectionhomemethod;
}

function get_section_pagemethod($id) {
	global $setting_sectionpagemethod, $sections;
	$sectionpagemethod = $sections[$id]['sectionpagemethod'];
	if($sectionpagemethod == '') {
		$sectionpagemethod = $setting_sectionpagemethod;
	}
	return $sectionpagemethod;
}

function get_section_ipp($id) {
	global $template_path;
	$template = AK_ROOT.'/templates/'.$template_path.'/,'.get_section_template($id, 'list');
	$template_content = readfromfile($template);
	preg_match("/<{getitems bandindex=\"1\".*?num=\"([0-9]+)\"/i", $template_content, $matches);
	$ipp = (empty($matches[1]) ? 10 : $matches[1]);
	return $ipp;
}
?>
<?php
require_once './include/common.inc.php';
require_once './include/admin.inc.php';
checkcreator();
if(!isset($post_setting_submit)) {
	empty($get_action) && $get_action = '';
	$settings = array();
	$query = $db->list_by('*', 'settings');
	while($setting = $db->fetch_array($query)) {
		$settings[$setting['variable']] = $setting;
	}
	$str_settings = '';
	if(empty($get_action) || $get_action == 'generally') {
		$str_settings .= table_start($lan['generallysetting']);
		$str_settings .= inputshow($settings, array('sitename', 'language', 'htmlexpand', 'statcachesize', 'defaultfilename', 'homepage', 'systemurl', 'storemethod', 'categoryhomemethod', 'categorypagemethod', 'sectionhomemethod', 'sectionpagemethod', 'attachmethod', 'previewmethod', 'imagemethod'));
		$str_settings .= table_end();
	}
	if(empty($get_action) || $get_action == 'functions') {
		$str_settings .= table_start($lan['functionssetting']);
		$str_settings .= inputshow($settings, array('html', 'usefilename', 'forbidinclude', 'forbidstat', 'forbidautorefresh', 'forbidspider', 'autoparsekeywords', 'autoparsefilename', 'forbidclearspace'));
		$str_settings .= table_end();
	}
	if(empty($get_action) || $get_action == 'cp') {
		$str_settings .= table_start($lan['cpsetting']);
		$str_settings .= inputshow($settings, array('ipp', 'richtext'));
		$str_settings .= table_end();
	}
	if(empty($get_action) || $get_action == 'item') {
		$str_settings .= table_start($lan['itemsetting']);
		$str_settings .= inputshow($settings, array('itemcolorshow', 'itemstyleshow', 'itemshorttitleshow', 'itemaimurlshow', 'itemauthorshow', 'itemsourceshow', 'itemsectionshow', 'itemtemplateshow', 'itemfilenameshow', 'itemdigestshow', 'itemkeywordsshow', 'itempictureshow', 'itemordershow', 'itemattachshow'));
		$str_settings .= table_end();
	}
	if(empty($get_action) || $get_action == 'front') {
		$str_settings .= table_start($lan['frontsetting']);
		$str_settings .= inputshow($settings, array('attachtemplate', 'keywordslink', 'commentneedcaptcha'));
		$str_settings .= table_end();
	}
	if(empty($get_action) || $get_action == 'bbs') {
		$str_settings .= table_start($lan['bbssetting']);
		$str_settings .= inputshow($settings, array('bbstype', 'bbstablepre'));
		$str_settings .= table_end();
	}
	if(empty($get_action) || $get_action == 'blog') {
		$str_settings .= table_start($lan['blogsetting']);
		$str_settings .= inputshow($settings, array('blogtype', 'blogtablepre'));
		$str_settings .= table_end();
	}
	if(empty($get_action) || $get_action == 'email') {
		$str_settings .= table_start($lan['emailsetting']);
		$str_settings .= inputshow($settings, array('emailreport', 'adminemail', 'smtpemail', 'smtphost', 'smtpport', 'smtpaccount', 'smtppassword'));
		$str_settings .= table_end();
	}

	if(empty($get_action) || $get_action == 'attach') {
		$str_settings .= table_start($lan['attachsetting']);
		$str_settings .= inputshow($settings, array('attachimagequality', 'attachwatermarkposition', 'maxattachsize'));
		$str_settings .= table_end();
	}
	$smarty->assign('action', $get_action);
	$smarty->assign('str_settings', $str_settings);
	displaytemplate('admincp_setting.htm');
} else {
	$changes = array();
	foreach($settings as $variable => $setting) {
		$post_variable = 'post_'.$variable;
		if(isset($$post_variable)) {
			if($setting != $$post_variable) {
				$changes[] = $variable;
				$sql = "UPDATE {$tablepre}_settings SET value='{$$post_variable}' WHERE variable='$variable'";
				$db->query($sql);
			}
		}
	}
	updatecache('settings');
	if(in_array('emailreport', $changes)) {
		if(!empty($post_emailreport) && $post_emailreport == 'no') {
			$db->query("DELETE FROM {$tablepre}_crons WHERE itemid='1' AND job='report'");
		} elseif(!empty($post_emailreport) && $post_emailreport == 'byday') {
			$type = 1;
			$day = 0;
			$date = 0;
			$hour = 0;
			$minute = 0;
		} elseif(!empty($post_emailreport) && $post_emailreport == 'byweek') {
			$type = 2;
			$day = 0;
			$date = 1;
			$hour = 0;
			$minute = 0;
		} elseif(!empty($post_emailreport) && $post_emailreport == 'bymonth') {
			$type = 3;
			$day = 0;
			$date = 1;
			$hour = 0;
			$minute = 0;
		}
		if(!empty($post_emailreport) && $post_emailreport != 'no') {
			$db->query("REPLACE INTO {$tablepre}_crons(type,day,date,hour,minute,itemid,job)VALUES('$type','$day','$date','$hour','$minute','1','report')");
		}
		updatecache('crons');
	}
	adminmsg($lan['operatesuccess'], 'setting.php?action='.$post_action);
}
aexit();
?>
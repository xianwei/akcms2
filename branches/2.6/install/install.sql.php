<?php
$createtablesql = array();
$createtablesql['admins'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'editor' => array(
			'type' => 'varchar',
			'length' => 20,
		),
		'password' => array(
			'type' => 'char',
			'length' => 32,
		),
		'freeze' => array(
			'type' => 'tinyint',
			'length' => 4,
			'default' => 0,
		),
		'items' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		)
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		)
	),
);

$createtablesql['attachments'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'itemid' => array(
			'type' => 'int',
			'length' => 11,
		),
		'filename' => array(
			'type' => 'varchar',
			'length' => 100,
		),
		'filesize' => array(
			'type' => 'int',
			'length' => 11,
		),
		'description' => array(
			'type' => 'varchar',
			'length' => 255,
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 11,
		)
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'itemid' => array(
			'type' => 'key',
			'value' => array('itemid')
		),
	),
);

$createtablesql['categories'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'auto_increment' => 1,
		),
		'categoryup' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
		'category' => array(
			'type' => 'varchar',
			'length' => 50,
		),
		'alias' => array(
			'type' => 'varchar',
			'length' => 50,
		),
		'keywords' => array(
			'type' => 'varchar',
			'length' => 50,
		),
		'description' => array(
			'type' => 'varchar',
			'length' => 255,
		),
		'items' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
		'allitems' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
		'pv' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
		'orderby' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
		'path' => array(
			'type' => 'varchar',
			'length' => 30,
		),
		'itemtemplate' => array(
			'type' => 'varchar',
			'length' => 30,
		),
		'defaulttemplate' => array(
			'type' => 'varchar',
			'length' => 30,
		),
		'listtemplate' => array(
			'type' => 'varchar',
			'length' => 30,
		),
		'html' => array(
			'type' => 'tinyint',
			'length' => 4,
			'default' => 0
		),
		'usefilename' => array(
			'type' => 'tinyint',
			'length' => 4,
			'default' => 0
		),
		'storemethod' => array(
			'type' => 'varchar',
			'length' => 50,
		),
		'categoryhomemethod' => array(
			'type' => 'varchar',
			'length' => 50,
		),
		'categorypagemethod' => array(
			'type' => 'varchar',
			'length' => 50,
		),
		'itemextfields' => array(
			'type' => 'text',
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
	),
);

$createtablesql['comments'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'itemid' => array(
			'type' => 'int',
			'length' => 9,
		),
		'username' => array(
			'type' => 'varchar',
			'length' => 50,
		),
		'title' => array(
			'type' => 'varchar',
			'length' => 255
		),
		'message' => array(
			'type' => 'text',
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 9
		),
		'ip' => array(
			'type' => 'char',
			'length' => 15
		),
		'goodnum' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
		'badnum' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'itemid2' => array(
			'type' => 'key',
			'value' => array('itemid', 'dateline', 'ip'),
		),
		'dateline' => array(
			'type' => 'key',
			'value' => array('dateline', 'ip'),
		)
	),
);

$createtablesql['scores'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'itemid' => array(
			'type' => 'int',
			'length' => 11,
		),
		'score' => array(
			'type' => 'int',
			'length' => 11,
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 11
		),
		'ip' => array(
			'type' => 'char',
			'length' => 15
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'itemid3' => array(
			'type' => 'key',
			'value' => array('itemid', 'dateline', 'ip'),
		),
		'dateline2' => array(
			'type' => 'key',
			'value' => array('dateline', 'ip'),
		)
	),
);

$createtablesql['filenames'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'htmlid' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'filename' => array(
			'type' => 'varchar',
			'length' => 255,
		),
		'type' => array(
			'type' => 'varchar',
			'length' => 10,
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 11
		),
		'id' => array(
			'type' => 'int',
			'length' => 11
		),
		'page' => array(
			'type' => 'smallint',
			'length' => 6
		),
	),
	'indexs' => array(
		'htmlid' => array(
			'type' => 'primary'
		),
		'filename' => array(
			'type' => 'unique',
			'value' => array('filename'),
		),
		'type' => array(
			'type' => 'unique',
			'value' => array('type', 'id', 'page'),
		)
	),
);

$createtablesql['items'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'title' => array(
			'type' => 'char',
			'length' => 100,
		),
		'aimurl' => array(
			'type' => 'char',
			'length' => 100,
		),
		'shorttitle' => array(
			'type' => 'char',
			'length' => 50,
		),
		'category' => array(
			'type' => 'smallint',
			'length' => 6,
			'default' => 0
		),
		'section' => array(
			'type' => 'smallint',
			'length' => 6,
			'default' => 0
		),
		'author' => array(
			'type' => 'char',
			'length' => 30,
		),
		'editor' => array(
			'type' => 'char',
			'length' => 25,
		),
		'source' => array(
			'type' => 'char',
			'length' => 40,
		),
		'orderby' => array(
			'type' => 'mediumint',
			'length' => 9,
			'default' => 0,
		),
		'orderby2' => array(
			'type' => 'mediumint',
			'length' => 9,
			'default' => 0,
		),
		'orderby3' => array(
			'type' => 'mediumint',
			'length' => 9,
			'default' => 0,
		),
		'orderby4' => array(
			'type' => 'mediumint',
			'length' => 9,
			'default' => 0,
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 11
		),
		'lastupdate' => array(
			'type' => 'int',
			'length' => 11
		),
		'lastreply' => array(
			'type' => 'int',
			'length' => 11
		),
		'pageview' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0,
		),
		'template' => array(
			'type' => 'char',
			'length' => 30
		),
		'filename' => array(
			'type' => 'char',
			'length' => 255
		),
		'attach' => array(
			'type' => 'tinyint',
			'length' => 4,
			'default' => 0
		),
		'picture' => array(
			'type' => 'char',
			'length' => 255
		),
		'latesthtml' => array(
			'type' => 'int',
			'length' => 11
		),
		'keywords' => array(
			'type' => 'char',
			'length' => 255
		),
		'digest' => array(
			'type' => 'char',
			'length' => 255
		),
		'titlecolor' => array(
			'type' => 'char',
			'length' => 7
		),
		'titlestyle' => array(
			'type' => 'char',
			'length' => 1
		),
		'commentnum' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0,
		),
		'scorenum' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0,
		),
		'totalscore' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0,
		),
		'avgscore' => array(
			'type' => 'float',
			'length' => 11,
			'default' => 0
		),
		'ext' => array(
			'type' => 'tinyint',
			'length' => 4,
			'default' => 0
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'category' => array(
			'type' => 'key',
			'value' => array('category', 'section', 'editor'),
		),
		'editor' => array(
			'type' => 'key',
			'value' => array('editor'),
		),
		'dateline3' => array(
			'type' => 'key',
			'value' => array('dateline'),
		)
	),
);

$createtablesql['item_exts'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'value' => array(
			'type' => 'text',
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		)
	),
);

$createtablesql['sections'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'section' => array(
			'type' => 'varchar',
			'length' => 50
		),
		'description' => array(
			'type' => 'varchar',
			'length' => 255
		),
		'alias' => array(
			'type' => 'varchar',
			'length' => 50
		),
		'keywords' => array(
			'type' => 'varchar',
			'length' => 255
		),
		'sectionhomemethod' => array(
			'type' => 'varchar',
			'length' => 50
		),
		'sectionpagemethod' => array(
			'type' => 'varchar',
			'length' => 50
		),
		'defaulttemplate' => array(
			'type' => 'varchar',
			'length' => 30
		),
		'listtemplate' => array(
			'type' => 'varchar',
			'length' => 30
		),
		'items' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0
		),
		'html' => array(
			'type' => 'tinyint',
			'length' => 4
		),
		'orderby' => array(
			'type' => 'mediumint',
			'length' => 9,
			'default' => 0
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		)
	),
);

$createtablesql['settings'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'variable' => array(
			'type' => 'varchar',
			'length' => 25,
		),
		'value' => array(
			'type' => 'text',
		),
		'type' => array(
			'type' => 'char',
			'length' => 6,
		),
		'standby' => array(
			'type' => 'varchar',
			'length' => 255,
		),
	),
	'indexs' => array(
		'variable' => array(
			'type' => 'primary'
		)
	),
);

$createtablesql['texts'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'itemid' => array(
			'type' => 'int',
			'length' => 11,
			'default' => 0,
		),
		'text' => array(
			'type' => 'text',
		),
		'page' => array(
			'type' => 'smallint',
			'length' => 6,
			'default' => 0,
		)
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'itemid_2' => array(
			'type' => 'unique',
			'value' => array('itemid', 'page')
		)
	),
);

$createtablesql['crons'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'name' => array(
			'type' => 'char',
			'length' => 30
		),
		'type' => array(
			'type' => 'smallint',
			'length' => 6,
		),
		'day' => array(
			'type' => 'smallint',
			'length' => 6,
		),
		'date' => array(
			'type' => 'smallint',
			'length' => 6,
		),
		'hour' => array(
			'type' => 'smallint',
			'length' => 6,
		),
		'minute' => array(
			'type' => 'smallint',
			'length' => 6,
		),
		'itemid' => array(
			'type' => 'int',
			'length' => 11,
		),
		'lasttime' => array(
			'type' => 'int',
			'length' => 11,
		),
		'job' => array(
			'type' => 'varchar',
			'length' => 10,
		),
		'data' => array(
			'type' => 'text'
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'itemid4' => array(
			'type' => 'unique',
			'value' => array('itemid', 'job')
		)
	),
);

$createtablesql['variables'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'variable' => array(
			'type' => 'varchar',
			'length' => 30
		),
		'description' => array(
			'type' => 'text',
		),
		'value' => array(
			'type' => 'text',
		)
	),
	'indexs' => array(
		'variable' => array(
			'type' => 'primary'
		)
	),
);

$createtablesql['spiders'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'smallint',
			'length' => 5,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'spidername' => array(
			'type' => 'varchar',
			'length' => 50
		),
		'rule' => array(
			'type' => 'smallint',
			'length' => 5,
		),
		'lasttime' => array(
			'type' => 'int',
			'length' => 11,
		),
		'data' => array(
			'type' => 'text'
		)
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		)
	),
);

$createtablesql['spiderrules'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'smallint',
			'length' => 5,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'extfields' => array(
			'type' => 'text'
		),
		'data' => array(
			'type' => 'text'
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		)
	),
);

$createtablesql['spidercatched'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'key' => array(
			'type' => 'char',
			'length' => 16
		),
		'url' => array(
			'type' => 'varchar',
			'length' => 255
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 11
		),
		'rule' => array(
			'type' => 'smallint',
			'length' => 5
		),
		'itemid' => array(
			'type' => 'int',
			'length' => 11
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'key' => array(
			'type' => 'unique',
			'value' => array('key')
		)
	),
);

$createtablesql['visits'] = array(
	'charset' => 'gbk',
	'fields' => array(
		'id' => array(
			'type' => 'int',
			'length' => 11,
			'unsigned' => 1,
			'auto_increment' => 1,
		),
		'sid' => array(
			'type' => 'char',
			'length' => 16
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 11
		),
		'referer' => array(
			'type' => 'varchar',
			'length' => 255
		),
		'itemid' => array(
			'type' => 'int',
			'length' => 11
		),
		'type' => array(
			'type' => 'varchar',
			'length' => 10
		),
		'ip' => array(
			'type' => 'char',
			'length' => 15
		),
	),
	'indexs' => array(
		'id' => array(
			'type' => 'primary'
		),
		'ip' => array(
			'type' => 'unique',
			'value' => array('ip', 'dateline')
		)
	),
);

$createtablesql['captchas'] = array(
	'engine' => 'memory',
	'charset' => 'gbk',
	'fields' => array(
		'sid' => array(
			'type' => 'char',
			'length' => 6,
		),
		'captcha' => array(
			'type' => 'char',
			'length' => 4
		),
		'dateline' => array(
			'type' => 'int',
			'length' => 11
		)
	),
	'indexs' => array(
		'sid' => array(
			'type' => 'primary'
		)
	),
);

$insertsql = array();
$insertsql[] = array(
	'tablename' => 'admins',
	'value' => array(
		'editor' => 'admin',
		'password' => '0cc175b9c0f1b6a831c399e269772661'
	)
);
$insertsql[] = array(
	'tablename' => 'categories',
	'value' => array(
		'id' => 1,
		'category' => 'default',
		'html' => 0,
		'usefilename' => 0
	)
);
$insertsql[] = array(
	'tablename' => 'categories',
	'value' => array(
		'id' => -1,
		'category' => 'keywords',
		'html' => -1,
		'usefilename' => -1
	)
);
$insertsql[] = array(
	'tablename' => 'items',
	'value' => array(
		'title' => 'default',
		'template' => 'page_home.htm',
		'filename' => '/index.htm'
	)
);
$insertsql[] = array(
	'tablename' => 'sections',
	'value' => array(
		'section' => 'default'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'sitename',
		'value' => 'AKCMS',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'language',
		'value' => 'english',
		'type' => 'select',
		'standby' => 'chinese,english'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'ipp',
		'value' => '10',
		'type' => 'select',
		'standby' => '10,20,30,40,50,60'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'html',
		'value' => '0',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'usefilename',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'htmlexpand',
		'value' => '.htm',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'maxattachsize',
		'value' => '2048',
		'type' => 'int',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'defaultfilename',
		'value' => 'index.htm',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'forbidinclude',
		'value' => '0',
		'type' => 'bin',
		'standby' => '0,1'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'forbidstat',
		'value' => '0',
		'type' => 'bin',
		'standby' => '0,1'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'forbidautorefresh',
		'value' => '0',
		'type' => 'bin',
		'standby' => '0,1'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'forbidspider',
		'value' => '0',
		'type' => 'bin',
		'standby' => '0,1'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'forbidclearspace',
		'value' => '1',
		'type' => 'bin',
		'standby' => '0,1'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'statcachesize',
		'value' => '128',
		'type' => 'int',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'keywordslink',
		'value' => '',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'emailreport',
		'value' => 'no',
		'type' => 'select',
		'standby' => 'no,byday,byweek,bymonth'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'adminemail',
		'value' => 'your@email.com',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'homepage',
		'value' => '',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'systemurl',
		'value' => '',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'smtpemail',
		'value' => 'akcms@126.com',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'smtphost',
		'value' => 'smtp.126.com',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'smtpport',
		'value' => '25',
		'type' => 'int',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'smtpaccount',
		'value' => 'akcms_sendmail',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'smtppassword',
		'value' => '',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'attachtemplate',
		'value' => '[attachtemplate]',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemcolorshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemstyleshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemshorttitleshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemaimurlshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemauthorshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemsourceshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemsectionshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemtemplateshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemfilenameshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemdigestshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemkeywordsshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itempictureshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemordershow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemattachshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'itemhtmlshow',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'autoparsekeywords',
		'value' => '0',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'autoparsefilename',
		'value' => '0',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'storemethod',
		'value' => '[categorypath]/[f]',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'categoryhomemethod',
		'value' => '[categorypath]/index.htm',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'categorypagemethod',
		'value' => '[categorypath]/index-[page].htm',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'sectionhomemethod',
		'value' => '[sectionalias]/index.htm',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'sectionpagemethod',
		'value' => '[sectionalias]/index-[page].htm',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'imagemethod',
		'value' => 'pictures/[y]/[m]/[id]-[f]',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'attachmethod',
		'value' => 'attaches/[y]/[m]/[id]-[f]',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'previewmethod',
		'value' => 'previews/[y]/[m]/[id]-[f]',
		'type' => 'char',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'attachimagequality',
		'value' => '80',
		'type' => 'select',
		'standby' => '10,20,30,40,50,60,70,80,90,100'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'attachwatermarkposition',
		'value' => '9',
		'type' => 'select',
		'standby' => '-1,0,1,2,3,4,5,6,7,8,9'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'richtext',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);

$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'commentfrequencelimit',
		'value' => '30',
		'type' => 'int',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'scorefrequencelimit',
		'value' => '30',
		'type' => 'int',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'commentneedcaptcha',
		'value' => '1',
		'type' => 'bin',
		'standby' => '1,0'
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'extfields',
		'value' => 'a:0:{}',
		'type' => 'text',
		'standby' => ''
	)
);
$insertsql[] = array(
	'tablename' => 'settings',
	'value' => array(
		'variable' => 'globalkeywordstemplate',
		'value' => '<a href="[url]" title="[digest]">[keyword]</a>',
		'type' => 'char',
		'standby' => ''
	)
);
?>
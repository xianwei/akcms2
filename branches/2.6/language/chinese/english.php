<?php
$lan = Array
	(
	'id' => 'id',
	'ip' => 'IP',
	'alias' => '别名',
	'import' => '导入',
	'export' => '导出',
	'sys' => 'ak',
	'path' => '目录',
	'home' => '首页',
	'character' => '特征',
	'default' => '默认',
	'withglobal' => '执行全局设置',
	'text' => '正文',
	'variable' => '变量',
	'value' => '内容',
	'content' => '内容',
	'edit' => '编辑',
	'delete' => '删除',
	'status' => '状态',
	'welcome' => '欢迎您',
	'preview' => '预览',
	'save' => '保存',
	'manage' => '管理',
	'batch' => '批量',
	'reset' => '重置',
	'template' => '模板',
	'activate' => '激活',
	'frozen' => '被冻结',
	'disabled' => '不可用',
	'available' => '可用',
	'yes' => '是',
	'no' => '否',
	'freeze' => '冻结',
	'active' => '可用',
	'del' => '删',
	'ins' => '插',
	'language' => '语言',
	'chinese' => '中文',
	'english' => '英文',
	'attach' => '附件',
	'picture' => '缩略图',
	'or' => '或',
	'date' => '日期',
	'author' => '作者',
	'source' => '来源',
	'category' => '栏目',
	'section' => '主题',
	'pageview' => 'PV',
	'totalpageview' => '总PV',
	'query' => '查询',
	'key' => '关键字',
	'username' => '帐户',
	'password' => '密码',
	'login' => '登录',
	'description' => '描述',
	'order' => '排序值',
	'set' => '设置',
	'orderby' => '排序方式',
	'title' => '题目',
	'optimize' => '优化数据表',
	'editor' => '编辑',
	'data' => '内容',
	'install' => '安装',
	'font' => '字体',
	'color' => '颜色',
	'style' => '样式',
	'bold' => '粗体',
	'italic' => '斜体',
	'digest' => '摘要',
	'keywords' => '关键字',
	'aimurl' => '目标地址',
	'charset' => '字符集',
	'start' => '开始',
	'process' => 'Process',
	'tip' => '小提示',
	'deleted' => '已删除',
	'account' => '帐户',
	'servertime' => '服务器时间',
	'cp' => '控制面板',
	'forcreatoronly' => '对不起，本功能只有超级管理员才能使用。',
	'templatenotexist' => '一个或多个模板缺失，请联系管理员确定所有的模板已经正确上传到服务器。',
	'filename' => '文件名',
	'addspider' => '增加定时采集器',
	'spidertiming' => '采集器定时设置',
	'filesize' => '文件大小',
	'maintemplate' => '主模板',
	'systemtemplate' => '系统模板',
	'systemvariable' => '系统变量',
	'subtemplate' => '子模板',
	'clickfortarget' => '如果你的浏览器未自动跳转，请点击这里。',
	'parameterwrong' => '参数不正确！',
	'operatesuccess' => '该操作执行成功！',
	'nodefined' => '该操作未被定义',
	'specialcharacter' => '不允许使用特殊字符，只允许：“a-z，A-Z，0-9，_，-，.，/”',
	'pathspecialcharacter' => '不允许使用特殊字符，只允许：“a-z，A-Z，0-9，_，-”',
	'allarerequired' => '所有项都是必填项，请完整填写。',
	'cantwrite2file' => '写入文件失败，可能是该目录没有写入权限。',
	'invertselection' => '反选',
	'pvs' => '页面浏览数',
	'nostatyet' => 'There\'s no stat yet.',
	'createitembatch' => '批量生成文章静态页',
	'createcategorybatch' => '批量生成栏目静态页',
	'createcategorydefault' => '生成栏目首页',
	'createcategorylist' => '生成栏目列表页',
	'choosecategory' => '请选择栏目',
	'choosestep' => '每次生成几篇',
	'batchitemready' => Array
		(
		0 => '批量生成HTML准备就绪，共',
		1 => '篇文章。'
		),
	'batchiteminfo' => Array
		(
		0 => '完成 ',
		1 => '% 生成了 ',
		2 => ' 篇文章静态页。'
		),
	'login_success' => '成功登录！',
	'item_num' => '文章',
	'logout_success' => '成功退出！',
	'login_failed' => '您的帐户密码不正确',
	'youarefreeze' => '您的帐户被冻结，请联系管理员',
	'dbuser' => '数据库用户名',
	'dbpw' => '数据库密码',
	'dbname' => '数据库名',
	'dbhost' => '数据库地址（一般为localhost）',
	'localhost' => '本地',
	'tablepre' => '表名前缀',
	'connecterror' => '连接数据库失败，请检查数据库信息。',
	'installsuccess' => 'AKCMS 系统安装成功！管理员帐号："admin"，密码："a"',
	'dbnameerror' => '数据库名只能包含“a-z,A-Z,0-9,_”！',
	'tablepreerror' => '表名前缀只能包含“a-z,A-Z,0-9”！',
	'gbk' => '中文gbk',
	'utf8' => 'UTF-8',
	'timedifference' => '时差',
	'timedifference_description' => '举例：如果服务器时间比你本地时间早8小时，这个值应该是“-8”',
	'resetpassword' => '重设密码',
	'newaccount' => '新帐户',
	'changepassword' => '修改密码',
	'oldpassword' => '旧密码',
	'newpassword' => '新密码',
	'newpassword2' => '重复输入',
	'accountmanage' => '帐户管理',
	'changestatus' => '修改状态',
	'passwordreset' => '密码被重置为“akcms”',
	'accountorpasswordempty' => '帐户密码必须被指定',
	'accountexist' => '这个帐户已经存在',
	'accoundpassword' => '帐户/密码',
	'accounthasitems' => '这个帐户不能被删除，因为有文章输入这个帐户，如果您仅仅是不想这个帐户登录，您可以冻结他',
	'oldpassworderror' => '旧密码不正确',
	'repeatpassworderror' => '两次输入的密码不一致',
	'passwordempty' => '密码不能为空',
	'nothisuser' => '没有这个用户！',
	'itemnum' => '文章数',
	'helpcenter' => '帮助中心',
	'help' => '帮助',
	'officialsite' => '官方站点',
	'officialbbs' => '官方论坛',
	'os' => '操作系统',
	'systeminfo' => '系统信息',
	'siteinfo' => '站点信息',
	'sitepvs' => '全站浏览量',
	'siteitems' => '全站文章数',
	'siteattachments' => '全站附件数',
	'siteattachmentsizes' => '全站附件总大小',
	'siteeditors' => '全站编辑数',
	'tools' => '管理员工具',
	'maxupload' => '最大上传文件限制',
	'maxexetime' => '最大执行时间',
	'time' => '时间',
	'phpversion' => 'PHP 版本',
	'mysqlversion' => 'Mysql 版本',
	'akversion' => 'AKCMS 版本',
	'correcttime' => '校正时间',
	'functiondisabled' => '功能被禁用',
	'updatecache' => '更新全部缓存',
	'checkwritable' => '检查权限',
	'isunwritable' => ' 不可写',
	'writableerror' => '部分必须的目录/文件不可写',
	'writableok' => '所有的目录/文件的权限正常',
	'fonts' => Array
		(
		0 => 'Arial',
		1 => 'Arial Black',
		2 => 'Courier New'
		),
	'fontsize' => '字体大小',
	'fontcolor' => '字体颜色',
	'attach_no' => '没有附件',
	'item_edit' => '编辑文章',
	'item_no' => '没有符合条件的文章',
	'specialpage_no' => '还没有创建页面',
	'item_new' => '增加新文章',
	'item_manage' => '管理文章',
	'specialpages_manage' => '管理页面',
	'specialpage_edit' => '编辑页面',
	'pagename' => '页面名',
	'allcategory' => '所有栏目',
	'allsection' => '所有主题',
	'pagenum' => '页数',
	'numperpage' => '每页显示数',
	'prepage' => '当前页',
	'shorttitle' => '短题目',
	'deletepicture' => '删除缩略图',
	'deletepictureok' => '缩略图已经被删除，您需要保存文章才能使此操作生效。',
	'notitle' => '题目必须填写！',
	'nodata' => '内容必须填写！',
	'pleasechoose' => '请选择',
	'inputurl' => 'Please input the url.',
	'inputtext' => 'Please input the text.',
	'ifcreatehtml' => '创建静态页？',
	'nophp' => '指定文件名的扩展名不能是“PHP”',
	'noempty' => '文件名不能为空',
	'pictureexterror' => '缩略图文件的扩展名只能是：“jpg,jpeg,gif,png”',
	'attachexterror' => '附件扩展名不能是“PHP”',
	'clickfororipic' => '点击查看原始图片',
	'filenamecantbephp' => '文件扩展名不能是“php”',
	'pathforbidden' => '文件名路径被禁止，请重新选择',
	'indexnameforbidden' => '指定文件名不能包含“index”',
	'parentpathforbidden' => '指定文件名中不能出现父路径：“../”',
	'copyattachtag' => '向内容中插入附件的标签已经复制到剪贴板中，请粘贴到内容中的任何位置。',
	'suredelpage' => '确定删除这个页面？',
	'pagepathroot' => '网页的文件名必须是绝对路径，即：以根目录“/”开始',
	'haveattach' => '有附件',
	'havepicture' => '有缩略图',
	'attachnum' => '增加附件数',
	'newpage' => '增加一个页面',
	'limit255' => '最多255字符',
	'noitem' => '未选择任何文章',
	'suredelitem' => '确定删除这篇文章？',
	'suredelext' => '确定删除这个扩展字段？',
	'suredelattach' => '确定删除这个附件？',
	'noitembatch' => '请至少选择一篇文章才能批量操作',
	'fileexist' => '您指定的文件名已经被使用，请重新指定，比如：在前面加一个前缀',
	'attachtoobig' => Array
		(
		0 => '文件过大被禁止上传，最大允许 ',
		1 => ' KB'
		),
	'attachdeleted' => '成功删除这个附件',
	'attachnotfound' => '没找到这个附件',
	'createhtml' => '生成静态页',
	'content_item' => '内容管理',
	'system_setting' => '系统管理',
	'batchcreatehtml' => '生成静态页',
	'system_categories' => '栏目管理',
	'system_sections' => '主题管理',
	'system_templates' => '模板管理',
	'system_variables' => '变量管理',
	'system_spiders' => '采集器管理',
	'setting' => '系统设置',
	'system_specialpage' => '管理页面',
	'category_manage' => '栏目管理',
	'timingspider_manage' => '定时采集器管理',
	'spidername' => '采集器名',
	'lastwork' => '上次采集时间',
	'spiderrule' => '采集规则',
	'logout' => '退出',
	'createitem' => '批量生成文章',
	'createcategory' => '批量生成栏目',
	'html_title' => '是否允许创建HTML静态网页?',
	'html_description' => '静态网页更有利于搜索引擎的抓取',
	'html_text' => '开启,关闭',
	'ifusefilename' => '启用文件名？',
	'usefilename_title' => '是否为文章启用文件名？',
	'usefilename_description' => '仅当通过ID动态访问的时候不需要文件名',
	'usefilename_text' => '开启,关闭',
	'emailreport_title' => '邮件报告',
	'emailreport_description' => '网站运行情况报告会发送到您的管理员信箱',
	'emailreport_text' => '不报告,每天报告,每周报告,每月报告',
	'statisticsreport' => '网站运行报告',
	'smtpemail_title' => 'SMTP信箱',
	'smtpemail_description' => '通过这个信箱发邮件',
	'smtpemail_text' => '必须与smtp帐号对应',
	'language_title' => '后台语言',
	'language_description' => '控制面板的语言，不影响顾客看到的页面',
	'language_text' => '中文,英文',
	'commentneedcaptcha_title' => '评论需要验证码',
	'commentneedcaptcha_description' => '此功能是为了防止灌水机',
	'commentneedcaptcha_text' => '需要验证码,不需要验证码',
	'ipp_title' => '管理文章中每页显示文章数',
	'ipp_description' => '适用于管理文章列表，每页显示的文章数，不影响顾客看到的页面',
	'ipp_text' => '10,20,30,40,50,60,100',
	'attachtemplate_title' => '附件显示模板',
	'attachtemplate_description' => '比如：“描述：[description]&lt;br&gt;附件：[filename]([size] B)”',
	'itemcolorshow_title' => '是否显示标题颜色？',
	'itemcolorshow_text' => '显示,不显示',
	'itemstyleshow_title' => '是否显示标题样式？',
	'itemstyleshow_text' => '显示,不显示',
	'itemaimurlshow_title' => '是否显示目标网址？',
	'itemaimurlshow_text' => '显示,不显示',
	'itemshorttitleshow_title' => '是否显示短标题？',
	'itemshorttitleshow_text' => '显示,不显示',
	'itemauthorshow_title' => '是否显示作者？',
	'itemauthorshow_text' => '显示,不显示',
	'itemsourceshow_title' => '是否显示来源？',
	'itemsourceshow_text' => '显示,不显示',
	'itemsectionshow_title' => '是否显示主题？',
	'itemsectionshow_text' => '显示,不显示',
	'itemkeywordsshow_title' => '是否显示关键字？',
	'itemkeywordsshow_text' => '显示,不显示',
	'itempictureshow_title' => '是否显示缩略图？',
	'itempictureshow_text' => '显示,不显示',
	'itemordershow_title' => '是否显示排序值？',
	'itemordershow_text' => '显示,不显示',
	'itemattachshow_title' => '是否显示添加附件？',
	'itemattachshow_text' => '显示,不显示',
	'itemtemplateshow_title' => '是否显示选择模板？',
	'itemtemplateshow_text' => '显示,不显示',
	'itemfilenameshow_title' => '是否显示指定文件名？',
	'itemfilenameshow_text' => '显示,不显示',
	'itemdigestshow_title' => '是否显示选择摘要？',
	'itemdigestshow_text' => '显示,不显示',
	'htmlexpand_title' => 'Html扩展名',
	'htmlexpand_description' => '比如：htm,html。设置为“”（空）或“/”将生成目录型网页',
	'maxattachsize_title' => '附件最大大小',
	'maxattachsize_description' => '单位：KB',
	'defaultfilename_title' => '默认页的文件名',
	'defaultfilename_description' => '比如："index.htm"',
	'forbidinclude_text' => '开启,关闭',
	'forbidinclude_title' => '引用动态内容',
	'forbidinclude_description' => '如果禁止的话，前台页面速度最快，但是统计和自动刷新功能将无法使用。',
	'forbidstat_text' => '开启,关闭',
	'forbidstat_title' => '统计',
	'forbidstat_description' => '如果您使用第三方的统计服务，您可以禁止统计以提高速度。',
	'forbidautorefresh_title' => '自动刷新',
	'forbidautorefresh_description' => '如果你不想让页面自动刷新，您可以禁用这个设置以提高速度。',
	'forbidautorefresh_text' => '开启,关闭',
	'forbidspider_title' => '采集',
	'forbidspider_description' => '如果你不想启用采集功能，您可以禁用这个设置以提高速度。',
	'forbidspider_text' => '开启,关闭',
	'forbidclearspace_title' => '清除模板中多余的空格',
	'forbidclearspace_description' => '清除空格可以减少页面的大小，加快打开速度，节省网络开销；但是如果影响到你的特殊的代码，请禁用本功能',
	'forbidclearspace_text' => '开启,关闭',
	'statcachesize_title' => '统计缓存大小',
	'statcachesize_description' => '如果你想要更实时的统计数据，你可以减少这个缓存的大小；如果你想更高的负载量，你可以增加这个值。推荐值：5120（单位：字节）',
	'keywordslink_title' => '关键字连接',
	'keywordslink_description' => '文章内容中的关键字会被替换成带有这个地址的链接。比如这里可以填写："http://www.google.cn/search?q=[keyword]"（用[keyword]代表这个关键字本身）',
	'adminemail_title' => '管理员 E-mail',
	'adminemail_description' => '编辑可以通过这个信箱与你联系，而且如果你需要，访问统计报告将发送到这个信箱中。',
	'nobbs' => '未设置论坛',
	'bbstype_title' => '论坛型号',
	'bbstype_description' => '论坛数据库必须和主站使用同一个数据库',
	'bbstype_text' => '未安装论坛,discuz,phpwind,phpbb',
	'bbstablepre_title' => '论坛数据的表前缀',
	'bbstablepre_description' => '比如：Discuz!论坛的表前缀默认为“cdb_”',
	'richtext_title' => '富文本',
	'richtext_description' => '新增和编辑文章的时候使用富文本编辑框',
	'richtext_text' => '开启,关闭',
	'homepage_title' => '首页地址',
	'homepage_description' => '这个值如果留空将自动获取，如果指定将以指定的为准，"http://url", "http://url/", "http://url/path", "http://url/path/"四种形式都可以。',
	'systemurl_title' => '后台地址',
	'systemurl_description' => '这个值如果留空将自动获取，如果指定将以指定的为准，"http://url", "http://url/", "http://url/path", "http://url/path/"四种形式都可以。',
	'smtphost_title' => 'SMTP服务器地址',
	'smtphost_description' => '比如“smtp.gmail.com”',
	'smtpport_title' => 'SMTP端口',
	'smtpport_description' => '一般是25',
	'smtpaccount_title' => 'SMTP帐户',
	'smtpaccount_description' => '通常情况下就是您的E-mail帐户中@以前的部分。',
	'smtppassword_title' => 'SMTP密码',
	'smtppassword_description' => '通常情况下就是您的E-amil的密码',
	'autoparsekeywords_title' => '自动分析关键字',
	'autoparsekeywords_text' => '开启,关闭',
	'autoparsefilename_title' => '自动设置文件名(测试中功能)',
	'autoparsefilename_text' => '开启,关闭',
	'upperisself' => '上级栏目不能是自身',
	'category_name' => '栏目名',
	'category_no' => '没有栏目',
	'category_new' => '增加栏目',
	'category_edit' => '编辑栏目',
	'section_manage' => '主题管理',
	'section_name' => '主题名',
	'section_no' => '没有主题',
	'itemtemplate' => '最终页模板',
	'defaulttemplate' => '首页模板',
	'listtemplate' => '列表页模板',
	'section_new' => '增加主题',
	'section_edit' => '编辑主题',
	'categoryup' => '上级栏目',
	'rootcategory' => '一级栏目',
	'subcategory' => '二级栏目',
	'nocategoryname' => '栏目名必须填写！',
	'nosectionname' => '主题名必须填写！',
	'addcategoryok' => '增加栏目名成功！',
	'nocategory' => '文章必须选择所属栏目',
	'nosection' => '未发现这个主题',
	'defaultsectionnodel' => '默认主题不能删除',
	'suredelcategory' => '确定删除这个栏目？',
	'suredelsection' => '确定删除这个主题？',
	'suredelvariable' => '确定删除这个变量？如果有模板或者文章使用这个变量，删除它将导致错误。',
	'suredeltemplate' => '确定删除这个模板？如果这个模板被使用，删除它将导致错误。',
	'templatenameerror' => '模板名只允许(0-9)(a-z)(A-Z)而且不能为空。',
	'templateexit' => '该模板已经存在，请换一个模板名',
	'newtemplate' => '新模板已经被创建，你现在就可以修改它',
	'cantcreatetemplate' => '创建模板失败！',
	'notemplate' => '该模板不存在',
	'cantdeltemplate' => '删除模板失败！',
	'templatenotwritable' => '该模板不可用！',
	'variablenamerror' => '变量名只允许(0-9)(a-z)(A-Z)而且不能为空。',
	'maintemplatetip' => '可以直接用于生成HTML',
	'subtemplatetip' => '只能被其他模板调用，比如：“footer.htm”，“header.htm”，“nav.htm”',
	'delcategoryhasitem' => '不能删除这个栏目，因为这个栏目下还有文章！',
	'delcategoryhassub' => '不能删除这个栏目，因为这个栏目下还有子栏目！',
	'deltemplatehasused' => '不能删除这个模板，因为这个模板已经被使用！',
	'categorypathused' => '该目录已经被使用，请重新选择',
	'aliasused' => '该别名已经被使用，请重新选择',
	'addnewtemplate' => '增加一个新模板',
	'addnewvariable' => '增加一个新变量',
	'returntodefault' => '返回站点首页',
	'returntosystemdefault' => '返回控制面板首页',
	'pictureurl' => '请输入图片地址',
	'uploadpicture' => '上传缩略图',
	'picture_tip' => '如果同时指定了缩略图的图片地址则忽略上传缩略图。',
	'timing' => '定时',
	'notiming' => '不定时',
	'timingdistance' => '定时间隔（单位：分钟）',
	'timingday' => '定时于星期几',
	'timinghour' => '定时于几时',
	'timingminute' => '定时于几分',
	'everyday' => '每天',
	'weekdays' => Array
		(
		0 => '星期天',
		1 => '星期一',
		2 => '星期二',
		3 => '星期三',
		4 => '星期四',
		5 => '星期五',
		6 => '星期六'
		),
	'runnow' => '立即执行',
	'listurl' => '内容列表的地址',
	'liststarttag' => '列表区域开始标签',
	'listendtag' => '列表区域结束标签',
	'spiderlisturlcharacter' => '网址采集特征',
	'spiderlisturlskip' => '网址跳过特征',
	'spiderlisttitlecharacter' => '标题采集特征',
	'spiderlisttitleskip' => '标题跳过特征',
	'spiderrulemanage' => '采集规则管理',
	'rulename' => '规则名',
	'addrule' => '增加规则',
	'editrule' => '修改规则',
	'starttag' => '开始标签',
	'endtag' => '结束标签',
	'exampleurl' => '网址举例',
	'norulename' => '规则名称必须填写',
	'noexampleurl' => '网址举例必须填写',
	'nofield1' => 'Field 1 必须填写开始和结束',
	'replace' => '替换',
	'test' => '测试',
	'suredelspider' => '你确定要删除这个采集器？',
	'suredelspiderrule' => '你确定要删除这个采集规则？',
	'nospider' => '没有任何采集器',
	'nospiderrule' => '没有任何采集规则',
	'skip' => '跳过',
	'update' => '更新',
	'spider' => '采集',
	'storemethod_title' => '默认静态页面文件存放路径',
	'storemethod_description' => '用[categorypath]代表栏目的目录，用[y]代表年份[m]代表月份[d]代表日期[f]代表文件名。例如：[categorypath]/[y]/[f]会生成类似/book/2008/1234.htm这样的文件',
	'force' => '人工指定',
	'automatism' => '自动判断',
	'sitename_title' => '网站名',
	'sitename_description' => '只在后台显示',
	'blogtype_title' => '博客型号',
	'blogtype_description' => '博客数据库必须和主站使用同一个数据库',
	'blogtype_text' => '未安装博客,X-space',
	'blogtablepre_title' => '博客数据的表前缀',
	'blogtablepre_description' => '比如：X-space博客的表前缀默认为“supe_”',
	'db' => '数据库',
	'exportdb' => '备份数据库',
	'importdb' => '恢复数据库',
	'managedb' => '管理数据库',
	'maxfilesize' => '分卷大小',
	'exportsuccess' => '数据备份成功！备份文件存放于"/data/"目录下',
	'importconfirm' => '导入数据将导致数据库原来的数据被清空并且不可恢复！你确定要导入吗？',
	'importinfo' => '请将数据文件上传到/data/文件夹下，然后点击“导入”按钮。',
	'keyword' => '关键字',
	'generallysetting' => '基本设置',
	'functionssetting' => '功能开关设置',
	'cpsetting' => '后台设置',
	'frontsetting' => '前台设置',
	'itemsetting' => '字段显示设置',
	'bbssetting' => '论坛设置',
	'blogsetting' => '博客设置',
	'emailsetting' => '邮件设置',
	'wholesetting' => '完整设置',
	'spider_edit' => '编辑采集器',
	'spiderinforequired' => '采集器名和列表网页地址必须填写',
	'exportcontinue' => '备份完一组，立即备份下一组',
	'importcontinue' => '导入完一组，立即导入下一组',
	'processready' => '队列准备完毕，立即开始处理',
	'up' => '上升',
	'down' => '下降',
	'attachimagequality_title' => '图片附件质量',
	'attachimagequality_description' => '数字越大表示图像质量越高文件越大，反之质量越低文件越小，建议设置为80',
	'attachimagequality_text' => '10,20,30,40,50,60,70,80,90,100',
	'attachwatermarkposition_title' => '图片水印位置',
	'attachwatermarkposition_description' => '',
	'attachwatermarkposition_text' => '无水印,随机,左上角,上,右上角,左,中,右,左下角,下,右下角',
	'attachsetting' => '附件设置',
	'readme' => '说明',
	'modifyorder' => '修改排序值',
	'modifycategory' => '修改栏目',
	'neworder' => '新排序值',
	'newcategory' => '新栏目',
	'open' => '打开',
	'runsql' => '执行SQL语句',
	'addfield' => '增加字段',
	'extfieldslist' => '扩展字段列表',
	'field' => '字段',
	'type' => '类型',
	'option' => '选项',
	'length' => '长度',
	'commentnum' => '评论数',
	'comment' => '评论',
	'name' => '姓名',
	'denyip' => '封IP',
	'suredelcomment' => '确定删除这个评论？',
	'suredenycommentip' => '确定删除这个IP的全部评论？',
	'commentempty' => '无评论',
	'managecomment' => '管理评论',
	'updatefilenames' => '更新文件名',
	'leaveempty' => '留空则执行全局设置',
	'storemethod' => '静态页存放路径',
	'timingupdate' => '定时刷新',
	'categorypagemethod' => '栏目分页存放路径',
	'categorypagemethod_title' => '默认栏目分页存放路径',
	'categorypagemethod_description' => '用[categorypath]代表栏目的目录，用[page]代表页码，例如：[categorypath]/index-[page].htm，会生成类似/book/index-5.htm这样的分页',
	'sectionpagemethod' => '主题分页存放路径',
	'sectionpagemethod_title' => '默认主题分页存放路径',
	'sectionpagemethod_description' => '用[sectionalias]代表主题的别名，[sectionname]代表主题名，[sectionid]代表主题的ID，用[page]代表页码，例如：[sectionalias]/index-[page].htm，会生成类似/foreign/index-3.htm这样的分页',
	'attachmethod_title' => '默认附件存放路径',
	'attachmethod_description' => '用[categorypath]代表栏目的目录，用[y]代表年，[m]代表月，[d]代表日，[id]代表对应文章的ID，[f]代表文件名本身例如：attach/[y]/[m]/[id]-[f]，会生成类似/attach/2009/01/4534-fggksl.zip这样的附件地址',
	'globalkeywordstemplate_title' => '全局关键字的正文显示模板',
	'globalkeywordstemplate_description' => '用[keyword]代表关键词，用[url]代表目标URL，用[digest]代表摘要',
	'previewmethod_title' => '默认缩略图存放路径',
	'previewmethod_description' => '用[categorypath]代表栏目的目录，用[y]代表年，[m]代表月，[d]代表日，[f]代表文件名本身，例如：preview/[y]/[m]/[f]会生成类似/preview/2009/01/fuekdw.jpg这样的缩略图',
	'imagemethod_title' => '默认图片存放路径',
	'imagemethod_description' => '用[y]代表年，[m]代表月，[d]代表日，[f]代表文件名本身，例如：picture/[y]/[f]，上传的图片会保存到/picture/2009/kladas.jpg这样的路径下',
	'categoryhomemethod' => '栏目首页存放路径',
	'categoryhomemethod_description' => '用[categorypath]代表栏目的目录，例如：[categorypath]/index.htm，会生成类似/book/index.htm这样的栏目首页。',
	'categoryhomemethod_title' => '默认栏目首页文件存放路径',
	'sectionhomemethod' => '主题首页存放路径',
	'sectionhomemethod_description' => '用[sectionalias]代表主题的别名，用[sectionalias]代表主题的别名，[sectionname]代表主题名，[sectionid]代表主题的ID，例如：[sectionalias]/index.htm，会生成类似/foreign/index.htm这样的主题首页',
	'sectionhomemethod_title' => '默认主题首页文件存放路径',
	'rememberlogin' => '记住登录',
	'modifyglobalsetting' => '修改全局设置',
	'createcategorysuccess' => '创建栏目静态页成功！',
	'availablethemes' => '可用模板列表',
	'installandimportdata' => '安装并导入数据',
	'themeinstallsuccess' => '模板安装成功！',
	'installtheme' => '导入模板与数据',
	'downthemeinofficial' => '请从官方网站下载模板，保证安全',
	'howtousetheme' => '安装方法：下载后解压缩上传到/themes/目录下，然后通过此界面安装',
	'pagetemplate' => '页面模板',
	'categorytemplate' => '栏目模板',
	'sectiontemplate' => '主题模板',
	'categorydescription' => '默认网站首页模板为page_home.htm<br>默认内容页模板为item_display.htm<br>默认栏目首页模板为category_home.htm<br>默认栏目列表页模板为category_list.htm<br>默认搜索结果模板为search_list.htm',
	'attachmethod' => '附件存储地址',
	'variablestip' => '变量调用方法：比如：sitename变量，在模板中这样调用<{$v_sitename}>',
	'createdefault' => '生成首页',
	'createlist' => '生成列表',
	'variableexist' => '该变量已经存在',
	'string' => '字符串',
	'number' => '数字',
	'select' => '下拉菜单',
	'radio' => '单选按钮',
	'standby' => '备选值',
	'fieldname' => '字段名',
	'extfields' => '扩展字段',
	'extfieldexist' => '此扩展字段已存在，扩展字段的别名不能重复。',
	'extfieldempty' => '字段名和字段别名都必须填写。',
	'mustbeletter' => '必须是字母或数字',
	'pleasechoosedb' => '请选择数据库类型',
	'dbtypedescription' => '数据库类型怎么选？',
	'support' => '支持',
	'nosupport' => '不支持',
	'servertest' => '服务器测试',
	'dbtype' => '数据库类型',
	'version' => '版本',
	'sqliteunsupport' => 'SQlite版本不支持此功能。',
	'mangagecrons' => '定时器管理',
	'cronname' => '定时器名',
	'cronerror' => '定时器名、URL和定时周期都必须填写',
	'uselastcategory' => '使用上次的栏目',
	'pagesource' => '网页源码',
	'addsubcategory' => '增加子栏目'
	);
?>
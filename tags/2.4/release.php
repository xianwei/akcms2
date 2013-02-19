<?php
copy('install/config.inc.php', 'config.inc.php');
$nodb = 1;
include('include/common.func.php');
include('language/index.php');
@ak_rmdir('document');
@ak_rmdir('tools');
@unlink('language/index.php');
@unlink('language.php');
@unlink('include/install.lock');
@unlink('release.php');
@unlink('to-do.txt');
@unlink('update.log');
@unlink('codeline.php');
@unlink('testlist.txt');
@unlink('unittest.php');
$commoninc = readfromfile('include/common.inc.php');
$commoninc = str_replace('E_ALL', '0', $commoninc);
writetofile($commoninc, 'include/common.inc.php');
exit("AKCMS处于待发布状态！\n");
?>
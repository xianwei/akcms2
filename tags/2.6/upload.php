<?php
require_once './include/common.inc.php';
require_once './include/admin.inc.php';
includelanguage();
$filename = $file_uploadfile['name'];
$fileext = fileext($filename);
if(in_array($fileext, array('php'))) debug($lan['attachexterror'], 1, 1);
$newfilename = get_upload_filename($filename, 0, 0, 'image');
uploadtmpfile($file_uploadfile['tmp_name'], FORE_ROOT.$newfilename);
if(ispicture($filename)) addwatermark(FORE_ROOT.$newfilename);
$picurl = $homepage.$newfilename;
?>
<script>
parent.document.getElementById("txtUrl").value = "<?php echo $picurl;?>";
parent.document.getElementById("preview").src = "<?php echo $picurl;?>";
</script>
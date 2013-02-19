<?php
function coreaddwatermark($sourcefile = '', $watermarkfile = '', $position = 9, $quality = 80) {
	!isset($setting_attachimagequality) && $setting_attachimagequality = 80;
	$sourceinfo = getimagesize($sourcefile);
	$watermarkinfo = getimagesize($watermarkfile);
	list($s_w, $s_h) = $sourceinfo;
	list($w_w, $w_h) = $watermarkinfo;
	switch($sourceinfo['mime']) {
		case 'image/jpeg':
			$source = imageCreateFromJPEG($sourcefile);
			break;
		case 'image/gif':
			$gifdata = readfromfile($sourcefile);
			if(strpos($gifdata, 'NETSCAPE2.0') !== false) {
				return false;
			}
			$source = imageCreateFromGIF($sourcefile);
			break;
		case 'image/png':
			$source = imageCreateFromPNG($sourcefile);
			break;
		default:
			return false;
	}
	switch($position) {
		case 1:
			$x = +5;
			$y = +5;
			break;
		case 2:
			$x = ($s_w - $w_w) / 2;
			$y = +5;
			break;
		case 3:
			$x = $s_w - $w_w - 5;
			$y = +5;
			break;
		case 4:
			$x = +5;
			$y = ($s_h - $w_h) / 2;
			break;
		case 5:
			$x = ($s_w - $w_w) / 2;
			$y = ($s_h - $w_h) / 2;
			break;
		case 6:
			$x = $s_w - $w_w - 5;
			$y = ($s_h - $w_h) / 2;
			break;
		case 7:
			$x = +5;
			$y = $s_h - $w_h - 5;
			break;
		case 8:
			$x = ($s_w - $w_w) / 2;
			$y = $s_h - $w_h - 5;
			break;
		case 9:
			$x = $s_w - $w_w - 5;
			$y = $s_h - $w_h - 5;
			break;
	}
	if(substr($watermarkfile, -4) == '.png') {
		$watermark = imageCreateFrompng($watermarkfile);
	} else {
		$watermark = imageCreateFromGIF($watermarkfile);
	}
	imageCopy($source, $watermark, $x, $y, 0, 0, $w_w, $w_h);
	imageJPEG($source, $sourcefile, $quality);
}

function cutpic($picture, $width, $position = 'bottom', $quality = 80) {
	$temppicture = md5(microtime()).'.jpg';
	$source = $picture;
	$sourceinfo = getimagesize($source);
	list($w, $h) = $sourceinfo;
	$s_x = 0;
	$s_y = 0;
	$s_w = $w;
	$s_h = $h;
	$source = imagecreatefromjpeg($source);
	if($position == 'bottom') {
		$h -= $width;
		$s_h -= $width;
	} elseif($position == 'top') {
		$h -= $width;
		$s_y = $width;
	} elseif($position == 'left') {
		$w -= $width;
		$s_x = $width;
	} elseif($position == 'right') {
		$w -= $width;
		$s_w -= $width;
	}
	$target = imagecreatetruecolor($w, $h);
	imageCopy($target, $source, 0, 0, $s_x, $s_y, $s_w, $s_h);
	imageJPEG($target, $temppicture, $quality);
	copy($temppicture, $picture);
	unlink($temppicture);
}

function corecaptcha($captcha) {
	$width = 38;
	$height = 15;
	$im = imagecreate($width, $height);

	$bg = imagecolorallocate($im, 255, 255, 255);
	$textcolor = imagecolorallocate($im, 0, 0, 255);
	for($i = 0; $i < $width; $i ++) {
		for($j = 0; $j < $height; $j ++) {
			$bgcrumb = imagecolorallocate($im, rand(200,255), rand(200,255), rand(200,255));
			if(empty($bgcrumb) || $bgcrumb == -1) $bgcrumb = rand(2, 255);
			imagefilledrectangle($im, $i, $j, $i + 1, $j + 1, $bgcrumb);
		}
	}
	imagestring($im, 5, 2, 0, $captcha, $textcolor);
	header("Content-type: image/png");
	imagepng($im);
}
?>
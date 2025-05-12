<?php
$version="ezgenerator utils 1.19";
/*
	utils.php
	http://www.ezgenerator.com
	Copyright (c) 2005-2015 Image-line
*/
require_once '../ezg_data/functions.php';
Session::intStart();

define('ER_FILENOTFOUND',1);
define('ER_NOFILE',2);
define('ER_BADFILEFORMAT',3);
define('STREAM_BUFFER',4096);
define('STREAM_TIMEOUT',86400);
define('USE_OB',false);

function abnormalExit($errCode)
{
	switch ($errCode)
	{
		case ER_NOFILE:
			 $msg='File not specified';
			 break;
		case ER_FILENOTFOUND:
			 $msg='File not found';
			 break;
		case ER_BADFILEFORMAT:
			 $msg='Illegal file format';
			 break;
		default:
			 $msg='Unknown error';
			 break;
	}
	die('ERROR: '.$msg);
}

function getMIME($fileName)
{
	switch(strtolower(substr(strrchr($fileName,"."),1)))
	{
		case "zip":
			 $contentType="application/zip zip";
			 break;
		case "mp3":
			 $contentType="audio/mpeg";
			 break;
		case "pdf":
			 $contentType="application/pdf";
			 break;
		case "txt":
			 $contentType="text/plain";
			 break;
		case "htm":
			 $contentType="text/html";
			 break;
		case "html":
			 $contentType="text/html";
			 break;
		case "jpg":
			 $contentType="image/jpeg";
			 break;
		case "jpeg":
			 $contentType="image/jpeg";
			 break;
		case "gif":
			 $contentType="image/gif";
			 break;
		default:
			 $contentType="application/octet-stream";
	}
	return $contentType;
}

function return_video()
{
	$h=isset($_REQUEST['h'])?intval($_REQUEST['h']):510;
	$w=isset($_REQUEST['w'])?intval($_REQUEST['w']):853;
	$v=$_REQUEST['v'];
	print '<!DOCTYPE html>
	 <html>
	 <head>
	 <meta charset="UTF-8">
	 <meta name="viewport" content="width=device-width">
	 </head>
	 <body>
	<p><iframe style="border: none;max-width:100%" src="http://www.youtube.com/embed/'.$v.'" width="'.$w.'" height="'.$h.'" allowfullscreen="allowfullscreen"></iframe></p>
	</body></html>';
}


function addWatermark()
{
		list($imPath,$position)=explode('|',Crypt::decrypt($_REQUEST['w']));

		
		$stampPath='../innovaeditor/assets/watermark.png';

		$info = getimagesize($imPath);
		$ext=$info[2];
		if($ext==IMAGETYPE_JPEG)
			$im = imagecreatefromjpeg($imPath);
		else if($ext==IMAGETYPE_GIF)
			$im = imagecreatefromgif($imPath);
		else if($ext==IMAGETYPE_PNG)
			$im = imagecreatefrompng($imPath);
		else
			exit;

		$stamp = imagecreatefrompng($stampPath);

		$sx = imagesx($stamp);
		$sy = imagesy($stamp);
		
		if($position==1)
			imagecopy($im,$stamp,($info[0]-$sx)/2, ($info[1]-$sy)/2,0,0,imagesx($stamp),imagesy($stamp));					
		else
			imagecopy($im,$stamp,imagesx($im)- $sx-10,imagesy($im)-$sy-10,0,0,imagesx($stamp),imagesy($stamp));

		if($ext==IMAGETYPE_JPEG)
		{
			header('Content-type: image/jpeg');
			imagejpeg($im);
		}
		else if($ext==IMAGETYPE_PNG)
		{
			header('Content-type: image/png');
			imagepng($im);
		}
		else
		{
			header('Content-type: image/gif');
			imagegif($im);		
		}
			
		imagedestroy($im);
}

function return_file()
{
	if(!isset($_GET['filename']))
		abnormalExit(ER_NOFILE);

	$fileName=stripslashes($_GET['filename']);
	$realfname=$fileName;
	$ext=strtolower(substr(strrchr($fileName,"."),1));

	if(!preg_match('/^[0-9a-zA-Z_]+$/u',$ext))
		abnormalExit(ER_BADFILEFORMAT);
	switch($ext)
	{
		case "php": abnormalExit(ER_BADFILEFORMAT);
		case "ezg": abnormalExit(ER_BADFILEFORMAT);
	}
	if(basename($fileName) != $fileName)
		abnormalExit(ER_BADFILEFORMAT);

	if(isset($_GET['dir']))
	{
		$isMLuser = isset($_SESSION['mediaLibAdmin'])||isset($_SESSION['MLUser']);
		if(!preg_match('/^[0-9a-zA-Z_]+$/u',$_GET['dir']) && !$isMLuser)
			abnormalExit(ER_BADFILEFORMAT);
		$fileName='../'.$_GET['dir'].'/'.$fileName;
	}
	elseif((!file_exists($fileName))&&(file_exists('../'.$fileName)))
		$fileName='../'.$fileName;

	$file=@fopen($fileName,'r') or abnormalExit(ER_FILENOTFOUND);
	$fileSize=filesize($fileName);

	$sm=ini_get('safe_mode');
	if(!$sm && function_exists('set_time_limit') && strpos(ini_get('disable_functions'),'set_time_limit')===false)
		set_time_limit(STREAM_TIMEOUT);

	$partialContent=false;
	if(isset($_SERVER['HTTP_RANGE']))
	{
		$rangeHeader=explode('-',substr($_SERVER['HTTP_RANGE'],strlen('bytes=')));
		if($rangeHeader[0]>0)
		{
			$posStart=intval($rangeHeader[0]);
			$partialContent=true;
		}
		else
			$posStart=0;
		if($rangeHeader[1]>0)
		{
			$posEnd=intval($rangeHeader[1]);
			$partialContent=true;
		}
		else
			$posEnd=$fileSize-1;
	}
	else
	{
		$posStart=0;
		$posEnd=$fileSize-1;
	}
	/************** HEADERS ***************/
	header("Content-type: ".getMIME($fileName));
	header('Content-Disposition: attachment; filename="'.$realfname.'"');
	header("Content-Length: ".($posEnd - $posStart + 1));
	header('Date: '.gmdate('D, d M Y H:i:s \G\M\T',time()));
	header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T',filemtime($fileName)));
	header('Accept-Ranges: bytes');
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header("Expires: ".gmdate("D, d M Y H:i:s \G\M\T", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y"))));
	if($partialContent)
	{
		header("HTTP/1.0 206 Partial Content");
		header("Status: 206 Partial Content");
		header("Content-Range: bytes ".$posStart."-".$posEnd."/".$fileSize);
	}

	if($sm) fpassthru($file);
	else
	{
		fseek($file,$posStart);
		if(USE_OB) ob_start();
		while(($posStart+STREAM_BUFFER < $posEnd) && (connection_status()==0))
		{
			echo fread($file,STREAM_BUFFER);
			if(USE_OB)
				ob_flush();
			flush();
			$posStart +=STREAM_BUFFER;
		}
		if(connection_status()==0)
			echo fread($file,$posEnd-$posStart+1);
		if(USE_OB)
			ob_end_flush();
	}
	fclose($file);
}

function process_it()
{
	global $version;
	if (isset($_GET['action']))
	{
		$action=$_GET['action'];
		if($action=='download')
			return_file();
		elseif($action=='random')
			random_html($_REQUEST['id'],$_REQUEST['root'],$_REQUEST['cat']);
		elseif($action=='phpinfo')
		{
			if((isset($_GET['pwd']))&&(crypt($_GET['pwd'],'admin')=='adPTFL0iJCHec'))
				phpinfo();
		}
		elseif($action=='video')
			return_video();
		elseif($action=='version')
			echo $version;
	}
	elseif(isset($_REQUEST['w']))
			addWatermark();
}

process_it();
?>

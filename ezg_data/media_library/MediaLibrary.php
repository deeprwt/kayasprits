<?php

/*
 * http://www.ezgenerator.com
 * Copyright (c) 2013 Image Line
 */

/**
 * Description of MediaLibrary
 *
 * @author Joe
 */
define('BR','<br />');
include_once ML_ROOT_PATH.'MediaFolder.php';

class MediaLibrary
{

	private $userLoc;
	private $action;
	private $page;
	private $rootFile;
	private $rootPath;
	private $viewMode;
	private $defResize;
	private $resizeDisabled;
	private $EZGId;
	private $EZGRoot;
        private $folderN;

	public function __construct($rootFile,$rootPath='users',$defResize='600')
	{
		$this->defResize=$defResize;
		$this->rootFile=$rootFile;
		$this->rootPath=$rootPath;
		$this->setLoc();
		$this->page=isset($_REQUEST['page'])&&(int)$_REQUEST['page']>0?$_REQUEST['page']:1;
		$this->setViewMode();
		$this->setActon();
		$this->createUsersFldr();
		$this->EZGRoot = isset($_REQUEST['root'])?$_REQUEST['root']:'';
		$this->EZGId=isset($_REQUEST['id'])?$_REQUEST['id']:'image_url';
		$this->resizeDisabled=isset($_REQUEST['resize']) && intval($_REQUEST['resize']) == 0;

		return;
	}

	private function setViewMode()
	{
		if(isset($_REQUEST['vmode'])&&$_REQUEST['vmode']!='')
		{
			$this->viewMode = $_SESSION['MLvmode']=$_REQUEST['vmode'];
			return;
		}
		if(isset($_SESSION['MLvmode']))
		{
			$this->viewMode=$_SESSION['MLvmode'];
			return;
		}
		$this->viewMode='norm';

	}
	private function createUsersFldr()
	{
		$made=false;
		if(!is_dir($this->rootPath))
			$made =  mkdir($this->rootPath,0777);
		return $made && is_readable($this->rootPath) && is_writable($this->rootPath);
	}

	private function setLoc()
	{
		$adm = isset($_SESSION['mediaLibAdmin']);
		$usr = isset($_SESSION['MLUser']);
		$cookieName = 'ml_loc';
		if($adm) $cookieName .= $_SESSION['mediaLibAdmin'];
		if($usr) $cookieName .= $_SESSION['MLUser'];
		$this->userLoc=0;
		$loc = isset($_REQUEST['loc']) && $_REQUEST['loc']!=''?$_REQUEST['loc']:FALSE;
		if($loc===FALSE && isset($_COOKIE[$cookieName])) $loc = $_COOKIE[$cookieName];
		if($adm && $loc) $this->userLoc=$loc;
		if($usr)
			$this->userLoc = $loc && strpos($loc,$_SESSION['MLUser'])===0?$loc:$_SESSION['MLUser'];
		setcookie($cookieName,$this->userLoc,0,'/');
	}
	private function setActon()
	{
		$this->action=isset($_REQUEST['action'])?$_REQUEST['action']:'index';
		if(isset($_REQUEST['uploadFiles']))
			$this->action	='uploadFiles';
	}

	private function doTheAction(MediaFolder $folder)
	{
		switch($this->action)
		{
			case 'uploadFiles':
				$folder->uploadFiles();
				break;
			case 'delSelected':
				$folder->delFiles();
				break;
			case 'createFolder':
				$folder->createSubFolder();
				break;
		}
		$folder->organize();
	}


	public function parse() {
        $this->folderN = new MediaFolder($this->rootFile, $this->rootPath, $this->userLoc, $this->page, $this->viewMode);
        $folder = $this->folderN;
        $this->doTheAction($folder);
        $files = $folder->getFilesList();
        switch ($this->viewMode) {
            case 'thumb': include_once ML_ROOT_PATH . 'view/libview_thumb.php';
                break;
            case 'min': include_once ML_ROOT_PATH . 'view/libview_min.php';
                break;
            case 'norm':
            default:
                include_once ML_ROOT_PATH . 'view/libview.php';
                break;
        }

    }

   function buildMlHead() {
        // build head section for ML ca/view/libview.php and libview_thumb_view.php
        $head =  '<!DOCTYPE html>' . F_LF
            . '<html>' . F_LF
            . '<head>' . F_LF
                .'<script type="text/javascript" src="' . ML_ROOT_PATH . 'js/core.js"></script>' . F_LF
                . '<script type="text/javascript" src="' . ML_ROOT_PATH . 'js/jquery.html5_upload.js"></script>' . F_LF
                . '<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet"/>' . F_LF
                . '<title> Media Library - ' . (is_numeric($this->userLoc) && $this->userLoc == 0 ? 'Admin' : $this->userLoc) . '</title>' . F_LF
                . '<link type="text/css" href="' . ML_ROOT_PATH . 'css/lib.css" rel="stylesheet">' . F_LF
                . '<link href="http://vjs.zencdn.net/4.3/video-js.css" rel="stylesheet">' . F_LF
                . '<script src="http://vjs.zencdn.net/4.3/video.js"></script>' . F_LF
                . '<script type="text/javascript">' . F_LF
                . '$(function() {' . F_LF
                . '  var upload_new = $("#upload_field"), old_ies = $("#old_ies"), button_new =$("#btn_new");' . F_LF
                . '  if (window.File) {' . F_LF
                . '     old_ies.hide();' . F_LF
                . '	upload_new.show();' . F_LF
                . '  }else{' . F_LF
                . '     old_ies.show();' . F_LF
                . '     upload_new.hide(); ' . F_LF
                . '     button_new.hide();' . F_LF
                . '  }'
                . '});'
                . '</script>' . F_LF
                . '</head>' . F_LF;

        echo $head;
    }

    function buildMlBodyBegin() {
        $folder = $this->folderN;
        $errors = null;
        // build body section for ML ca/view/libview.php and libview_thumb_view.php
        $bodyBg = '<body>' . F_LF
                . '<div class="container a_n a_navtop">' . F_LF
                . '<div class="a_navt w99">' . F_LF
                . '<form   id="old_ies"  method="post" action="' . $folder->getRootFile() . HREF_GLUE . 'uploadFiles&loc='
                . $folder->getUserLoc()/* . '&root=' . $this->EZGRoot . '&media_type=' . $folder->getMediaType() .*/ . '"'
                . ' enctype="multipart/form-data">' . F_LF
                . '<input id="loc" type="hidden" value="' . $this->userLoc . '"/>' . F_LF
								. '<input id="protected" type="hidden" value="'.($folder->isProtected()?'TRUE':'FALSE') .'"/>'.F_LF
                . '<input id="media_type" type="hidden" value="' . $folder->getMediaType() . '"/>' . F_LF
                . '<input id="__file__" type="hidden" value="' . $folder->getRootFile() . '" />' . F_LF
                . '<input id="vmode" type="hidden" value="' . $this->viewMode . '" />' . F_LF
                . '<input id="fldId" type="hidden" value="' . $this->EZGId . '"/>' . F_LF
                . '<input id="ezgRoot" type="hidden" value="' . $this->EZGRoot . '" />' . F_LF
                . '<div style="float: right;">' . F_LF
                . '</div>' . F_LF
                . '<input type="file" name="user_file[]"/>' . F_LF
                . '<input type="submit"  value="Upload"/>' . F_LF
                . '</form>' . F_LF
                . '<button id="btn_new" class="uploadOpener typeFilter">' . LBL_UPLOAD_FILES . '</button>' . F_LF
                . '<div class="fileuploader sub_bg topic_bg gone" id="fileuploader">' . F_LF
                . '<div id="drop-zone" class="rvts8">' . F_LF
                . '<span class="dZoneLabel">' . LBL_DROP_FILES . '</span>' . F_LF
                . '<br/>'
                . '<span class="rvts8">' . F_LF
                . '<input name="user_file[]" type="file" multiple="multiple" id="upload_field" rel="' . $folder->getRootFile() . HREF_GLUE . ' uploadFiles&loc=' . $folder->getUserLoc() /* . '&root=' . $this->EZGRoot . '&media_type=' . $folder->getMediaType() .*/ . '"/></span>' . F_LF
                . '<div id = "progress_report rvts8">' . F_LF
                . '<div id = "progress_report_name"></div>' . F_LF
                . '<div id = "progress_report_status" ></div>' . F_LF
                . '<progress id = "progress_report_bar" min = "0" max = "100"></progress>' . F_LF
                . '</div>' . F_LF
                . '</div>' . F_LF
                . '<div id="resizeZone">' . F_LF
                . '<input id="resizeChecker" type="checkbox" name="resizeMediaFlag" value="resize" checked="checked" />' . F_LF
                . '<span class="rvts8">' . LBL_RESIZE_TO . '</span>' . F_LF
                . '<input id="resizeVal" type="text" name="resizeMediaVal" value="' . $this->defResize . '" />' . F_LF
                . '<span class="rvts8">px.</span>' . F_LF
                . '</div>' . F_LF
                . '</div>' . F_LF
                . '<div class="video_container gone">' . F_LF
                . '<video id="MLVideo" class="video-js vjs-default-skin" controls preload="auto" width="640" height="360">' . F_LF
                . '</video>' . F_LF
                . '</div>' . F_LF
                . '<div class="image_container gone">' . F_LF
                . '<div><span class="close_container">X</span></div>' . F_LF
                . '<div class="image_area"></div>' . F_LF
                . '</div>' . F_LF
                . '</div>' . F_LF
                . '<div class="a_navt atable w99">' . F_LF
                // filters
                . $folder->displayFilters();
								$bodyBg .='</div>' . F_LF
                . '</div>' . F_LF
                . '<div class="fileslist">' . F_LF;
        echo $bodyBg;
		}

    function buildbuildMlBodyEnd() {
        // builds end of body section for ML ca/view/libview.php and libview_thumb_view.php
        $bodyEnd = '<div class="clear-both"></div>' . F_LF
                . '</div>' . F_LF
                . '</body>' . F_LF
                . '</html>' . F_LF;
        echo  $bodyEnd;
    }

}
?>

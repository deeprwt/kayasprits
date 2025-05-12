<?php

/*
 * http://www.ezgenerator.com
 * Copyright (c) 2013-2015 Image Line
 */

/**
 * Description of MediaFolder
 *
 * @author Joe
 */
include_once ML_ROOT_PATH.'MediaFile.php';
include_once ML_ROOT_PATH.'MediaNavigation.php';
include_once ML_ROOT_PATH.'MediaFilters.php';

class MediaFolder
{

	private $files;
	private $name;
	private $isRoot;
	private $isSubfolder;
	private $userLoc;
	private $isAdmin;
	private $isProtected;
	private $page;
	private $filesLimit;
	private $navigation;
	private $filters;
	private $mediaType;
	private $totFilesCount;

	private $recentlyAdded;

	private $rootFile;
	private $rootPath;

	private $errors;
	private $notifications;
	private $errorData;

	private $searchNeedle;
	private $typeNeedle;

	private $allowedTypes;

	public function __construct($rootFile, $rootPath, $userLoc, $page, $viewMode)
	{
		$this->errorData=array('file'=>'','reason'=>'');
		$this->rootFile=$rootFile;
		$this->rootPath=$rootPath;
		$this->isProtected = FALSE;
		$this->files=array();
		$this->allowedTypes=array('mp3','vaw',
			'jpg','jpeg','png','gif','bmp',
			'avi','cam','flv','mov','mpeg','mpg','swf','wmv','mp4','ogg' ,'webm',
			'doc','docx','xls','xlsx','xlsb','txt','rtf','csv','gpx',
			'pdf','ppt','pps','pptx',
			'zip','rar','7z');
		$this->userLoc=$userLoc;
		$this->isSubfolder=strpos($this->userLoc,'/')!==FALSE;
		$this->isAdmin = isset($_SESSION['mediaLibAdmin']);
		$this->isRoot=is_numeric($userLoc) && $userLoc==0;
		$this->name	=$rootPath.(!$this->isRoot?'/'.$userLoc:'');
		$this->errors='';
		$this->mediaType='All';
		$this->notifications='';
		$this->searchNeedle=isset($_REQUEST['search'])&&$_REQUEST['search']!=''?$_REQUEST['search']:NULL;
		$this->mediaType = $this->typeNeedle=isset($_REQUEST['media_type'])&&$_REQUEST['media_type']!=''
			?$_REQUEST['media_type']:NULL;
		$this->page=$page;
		$this->recentlyAdded=false;
		if($viewMode=='thumb') $this->filesLimit=60;
		else $this->filesLimit=20;
		$this->totFilesCount=0;
		$this->checkAccess();
		if(!$this->checkDir())
		{
			$dirErrorMsg = 'Folder and/or inner thumbs folder not accessible!';
			$dirErrorMsg .= ' >>> File: '.$this->errorData['file'];
			$dirErrorMsg .= ' >>> Reason: '.$this->errorData['reason'];
			$this->setSingleError($dirErrorMsg);
			//TODO ErrorHandler::logSystemError($rel_path,$db,$dirErrorMsg);
		}
		$this->readFolder();
		$this->organize();
		$this->setupNavigation();
		$this->filters = new MediaFilters($viewMode);
		if(isset($_REQUEST['protect'])) $this->protect();
		if(isset($_REQUEST['unprotect'])) $this->unprotect();
	}

	private function setupNavigation()
	{
		$totalPages = ceil($this->getFilesCount()/$this->filesLimit);
		$this->navigation=new MediaNavigation($totalPages,$this->page);
	}
	private function checkAccess()
	{
		if($this->isRoot && !$this->isAdmin)
		{
			header('HTTP/1.0 403 Forbidden');
			echo '<h1>Forbidden 403</h1>';
			exit;
		}
	}
	public function getRootFile()
	{
		return $this->rootFile;
	}

	public function getLoggedUser()
	{
		if($this->isAdmin) return 'Admin';
		if(!$this->isAdmin && $this->isRoot) return 'Guest';
		return $this->userLoc;

	}
	//prepare folder for visualization
	//executle all manipulations in proper order
	public function organize()
	{
		$this->applyFilters();
		$this->totFilesCount = $this->getFilesCount();
		$this->sort();
	}

	private function getExtension($fileName)
	{
	  $pos_dot=strrpos($fileName,".");
	  return ($pos_dot!==false)?substr($fileName,$pos_dot+1):'';
	}

	private function readFolder()
	{
		//don't list files if folrder access is messed. Some functions (like $fileInfo->getSize()) may
		//cause the script to stop and user will get blank page without any information
		if($this->checkDir())
		{
			foreach(new DirectoryIterator($this->name) as $fileInfo)
			{
				if($fileInfo->getFilename() == '.htaccess' && $this->isSubfolder) {
					$this->isProtected = TRUE;
				}
				if((!$fileInfo->isDir() && !in_array(strtolower($this->getExtension($fileInfo->getFilename())),$this->allowedTypes))
					|| $fileInfo->isDot() || ($fileInfo->isDir() && ($fileInfo->getFilename()=='thumbs') || $fileInfo->getFilename()=='generated' || $fileInfo->getFilename()=='.svn'))
					continue;
				$fileName = ($fileInfo->isDir()&&!$this->isRoot?$this->userLoc.'/':'').$fileInfo->getFilename();
				$newFile=new MediaFile($this->rootFile,$fileName,$fileInfo->getPath(),$fileInfo->getSize(),$fileInfo->isDir());
				$newFile->setModedDate($fileInfo->getMTime());
				if($fileInfo->isDir() && !$this->checkDir($fileInfo->getPath().'/'.basename($fileName))) $newFile->setNotAccessible();
				$this->addFile($newFile);
				unset($newFile);
			}
		}
		if(!$this->isRoot)
		{
			if(!$this->isSubfolder)
			{
				if($this->isAdmin)
					$this->addFile(new MediaFile($this->rootFile,'0','',0,TRUE,TRUE,$this->isSubfolder));
			}
			else
				$this->addFile(new MediaFile($this->rootFile,dirname($this->userLoc),'',0,TRUE,TRUE,$this->isSubfolder));
		}
	}

	private function applyFilters()
	{
		$this->filter('search');
		$this->filter('mediaType');
		if(isset($_REQUEST['noFullThumb']))
			$this->filter('full/thumb');
	}
	//for now only used to search media titles (names), but in future we can extend this to filter media size, etc
	private function filter($type='search')
	{
		switch($type)
		{
			case 'search':
				if($this->searchNeedle===NULL) return;
				foreach($this->files as $key => $file)
					if(stripos($file->getFullName(),$this->searchNeedle)===false)
						unset($this->files[$key]);
				break;
			case 'mediaType':
				if($this->mediaType===NULL || $this->mediaType=='All') return;
				foreach($this->files as $key => $file)
					if($file->getType()!='dir' && $file->getTypeGroup() != $this->typeNeedle)
						unset($this->files[$key]);
				break;
			case 'full/thumb':
				foreach($this->files as $key=>$file)
					if(preg_match('/^.*_(thumb|full)\..*$/i',$file->getFullName()))
						unset($this->files[$key]);
		}
	}

	public function getMediaType()
	{
		return $this->mediaType===NULL?'All':$this->mediaType;
	}
	/*
	 * $by {'Name','Size','Path','Type'}
	 */
	private function sort($by='Name',$direction=SORT_ASC)
	{
		$filter = array();
		$filterDir = array();
		$get = 'get'.$by;
		foreach($this->files as $key => $row)
		{
			$filterDir[$key]=$row->isDir();
			$filter[$key]=$row->{$get}();
		}
		$filter = array_map('strtolower', $filter);
		array_multisort($filterDir,SORT_DESC,$filter,$direction,SORT_STRING,$this->files);
	}


	public function addFile($file)
	{
		if($file instanceof MediaFile)
			$this->files[]=$file;
	}

	public function delFile($fileName)
	{
		foreach($this->files as $fk => $file)
			if($file->getFullName()==$fileName)
			{
				unset($this->files[$fk]);
				return true;
			}
		return false;
	}

	private function rrmdir($dir)
	{
		if(is_dir($dir))
		{
			$objects=scandir($dir);
			foreach($objects as $object)
			{
				if($object!="."&&$object!="..")
				{
					if(filetype($dir."/".$object)=="dir")
						$this->rrmdir($dir."/".$object);
					else
						unlink($dir."/".$object);
				}
			}
			reset($objects);
			return rmdir($dir);
		}
		return false;
	}

	public function getFile($fileName)
	{
		foreach($this->files as $file)
			if($file->getName()==$fileName)
				return $file;
		return null;
	}

	public function getFilesCount()
	{
		return count($this->files);
	}

	public function getFilesLimit()
	{
		return $this->filesLimit;
	}

	public function getTotalFilesCount()
	{
		return $this->totFilesCount;
	}

	public function getFilesList()
	{

		if($this->getFilesCount()>$this->filesLimit)
		{
			if($this->recentlyAdded!==false)
			{
				$neededKey=false;
				foreach($this->files as $key=>$file)
				{
					if($file->getFullName()==$this->recentlyAdded)

					{
						$neededKey=$key;
						break;
					}
				}
				$this->page = ceil($neededKey/$this->filesLimit);
				$this->setupNavigation();
			}
			$filesInPages=array_chunk($this->files,$this->filesLimit);
			$this->files = isset($filesInPages[$this->page-1])?$filesInPages[$this->page-1]:end($filesInPages);
		}
		return $this->files;
	}

	public function getName()
	{
		return trim($this->name);
	}

	public function getUserLoc()
	{
		return $this->userLoc;
	}

	public function setSingleError($msg)
	{
		$this->errors = $msg;
	}

	public function setErrorLine($msg)
	{
		$this->errors .= $msg.BR;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function setNotificationLine($msg)
	{
		$this->notifications .= $msg.BR;
	}

	public function getNotifications()
	{
		return $this->notifications;
	}

	public function createSubFolder()
	{
		if(!isset($_REQUEST['nFldName'])) return;
		$fName = $_REQUEST['nFldName'];
		$fPath = $this->name.'/'.$fName;
		if(!is_dir($fPath)){
			if(mkdir($fPath))
			{
				$libName = (!$this->isRoot?$this->userLoc.'/':'').$fName;
				$this->addFile(new MediaFile($this->rootFile,$libName,'',0,true,false,$this->isSubfolder));
			}
                }
		else{
			$error_str='This folder already exists!';
			echo 'error_folder_ex:'.$error_str;
			exit;
		}
	}
	private function checkDir($path=NULL)
  {
    $path = $path!==NULL?$path:$this->name;
    $thumbsFolder = $path.'/thumbs';
		$dirsCreated = true;
		$dirsAccessible = true;
    if(!is_dir($path))
    {
      @mkdir($path,0777,true);
    }

    if(!is_dir($thumbsFolder))
    {
      @mkdir($thumbsFolder,0777,true);
    }

		if(!$readablePath = is_readable($path)) {
			$this->errorData['file'] = $path;
			$this->errorData['reason'] = 'Current user folder not readable';
			$dirsAccessible = false;
		}
		elseif(!$readableThumbs = is_readable($thumbsFolder)) {
			$this->errorData['file'] = $thumbsFolder;
			$this->errorData['reason'] = 'Thumbs folder in current folder not readable';
			$dirsAccessible = false;
		}
		elseif(!$writablePath = is_writable($path)) {
			$this->errorData['file'] = $path;
			$this->errorData['reason'] = 'Current user folder not writable';
			$dirsAccessible = false;
		}
		elseif(!$writableThumbs = is_writable($thumbsFolder)) {
			$this->errorData['file'] = $thumbsFolder;
			$this->errorData['reason'] = 'Thumbs folder in current folder not writable';
			$dirsAccessible = false;
		}
		elseif(!$executablePath = is_executable($path)) {
			$this->errorData['file'] = $path;
			$this->errorData['reason'] = 'Current user folder not executable';
			$dirsAccessible = false;
		}
		elseif(!$executableThumbs = is_executable($thumbsFolder)) {
			$this->errorData['file'] = $thumbsFolder;
			$this->errorData['reason'] = 'Thumbs folder in current folder not executable';
			$dirsAccessible = false;
		}
		elseif(!$dirCreated = is_dir($path)) {
			$this->errorData['file'] = $path;
			$this->errorData['reason'] = 'Current user folder was not created';
			$dirsCreated = false;
		}
		elseif(!$thumbCreated = is_dir($thumbsFolder)) {
			$this->errorData['file'] = $thumbsFolder;
			$this->errorData['reason'] = 'Thumbs folder in current folder was not created';
			$dirsCreated = false;
		}

    $dirsAccessible = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') || $dirsAccessible;

    return $dirsCreated && $dirsAccessible;
  }


	private function protect()
	{
			if(!$this->isSubfolder) return;
			$fileLoc = "./{$this->name}/.htaccess";
			$content = "Order Deny,Allow\nDeny from All\n";
			file_put_contents($fileLoc,$content);
			$fileLoc = "./{$this->name}/thumbs/.htaccess";
			$content = "Order Deny,Allow\nAllow from All\n";
			file_put_contents($fileLoc,$content);

	}

	private function unprotect()
	{
		if(!$this->isSubfolder) return;
		if(is_file($this->name.'/.htaccess'))
			unlink($this->name.'/.htaccess');
		if(is_file($this->name.'/thumbs/.htaccess'))
			unlink($this->name.'/thumbs/.htaccess');
	}

	public function isProtected() {
		return $this->isProtected;
	}

	public function prepareProtectButton() {
		if($this->isSubfolder)
			return '<button class="'.($this->isProtected?'un':'').'protect_folder">'.($this->isProtected?LBL_UNPROTECT_FOLDER:LBL_PROTECT_FOLDER).'</button>';
	}
	public function uploadFiles()
	{
		if(!isset($_FILES['user_file']))
			return;
		//Loop through each file
		$maxSize = isset($_REQUEST['resize'])&&$_REQUEST['resize']>0?$_REQUEST['resize']:FALSE;
		$resized = false;
		for($i=0; $i<count($_FILES['user_file']['name']); $i++)
		{
			//Get the temp file path
			$tmpFilePath=$_FILES['user_file']['tmp_name'][$i];
			$tmpFileName=$_FILES['user_file']['name'][$i];
			$pieces = explode('.',$tmpFileName);
			if(count($pieces)!=2) continue; //skip files with no or more than one ext
			$ext=end($pieces);
			if(!in_array(strtolower($ext),$this->allowedTypes)) continue;
			//Make sure we have a filepath
			if($tmpFilePath!="")
			{
				//Setup our new file path
				$newFilePath=$this->dontOverride("./{$this->name}/".$tmpFileName);
				if($maxSize && preg_match('/(jpg|jpeg|png|bmp|gif)$/i',$ext))
				{
					$newFilePathThumb="./{$this->name}/thumbs/".basename($newFilePath);
					include_once ML_ROOT_PATH.'SimpleImage.php';
					$image = new SimpleImage();
					$image->load($tmpFilePath);
					$size = getImageSize($tmpFilePath);
					if($size[0]>$maxSize)
					{
						$image->resizeToWidth($maxSize);
						$image->save($newFilePath,$image->image_type);
						$resized=TRUE;
					}
					elseif($size[1]>$maxSize)
					{
						$image->resizeToHeight($maxSize);
						$image->save($newFilePath,$image->image_type);
						$resized=TRUE;
					}
					if($size[1]>$size[0])
						$image->resizeToHeight(225,FALSE);  //portrait thumb
					else
						$image->resizeToWidth(225,FALSE);  //thumb
					$image->save($newFilePathThumb,$image->image_type);
				}

				//Upload the file into the temp dir
				if(!$resized)
					$moved = move_uploaded_file($tmpFilePath,$newFilePath);

				if($moved || $resized)
				{
					$newFile = new MediaFile($this->rootFile,basename($newFilePath),$this->name,filesize($newFilePath));
					$this->addFile($newFile);
					$this->recentlyAdded=basename($newFilePath);
					unset($newFile);
					$this->setNotificationLine('File '.$tmpFileName.' was uploaded!');
				}
				else
					$this->setErrorLine('File '.$tmpFileName.' was not uploaded (move failed)!');
			}
		}
	}

	/*
	 * Instead of overriding the same file, it searches for first free number suffix
	 */
	private function dontOverride($filepath)
	{
		if(is_file($filepath))
		{
			$pos=strrpos($filepath,'.');
			$filepath=substr_replace($filepath,'<lAsTdOt>',$pos,strlen('.'));
			$i=1;
			list($r,$x) =explode('<lAsTdOt>',$filepath);
			do
			{
				$filepath = $r.'_'.$i++.'.'.$x;
			}
			while(is_file($filepath));
		}

		return $filepath;
	}

	public function delFiles()
	{
		if(!isset($_REQUEST['media'])) return;
		foreach($_REQUEST['media'] as $file)
		{
			if($file=='..') continue; //do not delete up-folder.
			$filePath = $this->name.'/'.basename($file);
			if(is_file($filePath))
			{
				if(unlink($filePath))
				{
					unlink($this->name.'/thumbs/'.basename($file));
					$this->delFile($file);
				}
			}
			elseif(is_dir($filePath))
			{
				if($this->rrmdir($filePath))
					$this->delFile($file);
			}
		}

	}

	public function displayNav()
	{
		$this->navigation->display();
	}

	public function displayFilters()
	{
		ob_start();
		$this->filters->display($this->errors);
		return ob_get_clean();
	}

	public function getCPage()
	{
		return $this->page;
	}

	public function getRecentlyAdded()
	{
		return $this->recentlyAdded;
	}
}

?>

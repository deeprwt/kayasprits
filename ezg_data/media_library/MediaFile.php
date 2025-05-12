<?php
/*
 * http://www.ezgenerator.com
 * Copyright (c) 2013 Image Line
 */

/**
 * Description of MediaFile
 *
 * @author Joe
 */
class MediaFile
{

	private $name;
	private $path;
	private $size;
	private $type;
	private $modedDate;
	private $isDir;
	private $rootFile;
	private $isParLink;
	private $inSubfolder;
	private $isProtected;
	private $isNotAccessible;

	private $EZGRoot;
	private $EZGId;

	public function __construct($rootFile,$name,$path,$size,$isDir=FALSE,$isParLink=FALSE,$inSubfolder=FALSE)
	{
		$this->rootFile=$rootFile;
		$this->path	= $path;
		$this->size	= $isDir?'-':$size;
		$this->type	= $isDir?'dir':substr($name,strrpos($name,'.')+1);
		$this->name	= $isDir?$name:str_replace('.'.$this->type,'',$name);
		$this->isDir=$isDir;
		$this->isProtected = $this->isDir && is_file($this->path.'/'.basename($this->name).'/.htaccess');
		$this->isParLink=$isParLink;
		$this->inSubfolder = $inSubfolder;
		$this->EZGRoot = isset($_REQUEST['root'])?$_REQUEST['root']:'';
		$this->EZGId = isset($_REQUEST['id'])?$_REQUEST['id']:'image_url';
		$this->isNotAccessible = FALSE;
	}

	public function isDir()
	{
		return $this->isDir;
	}

	public function setNotAccessible()
	{
    $this->isNotAccessible = TRUE;
  }

	public function setModedDate($date)
	{
		$this->modedDate=$date;
	}

	public function getModedDate($mode='Y/m/d g:i')
	{
		return $this->modedDate==0?'-':date($mode,$this->modedDate);
	}

	public function getName($mark = FALSE)
  {
    if($this->isDir)
    {
      if($this->isParLink) return '..';
      return basename($this->name).
        ($this->isProtected && $mark ? ' <i class="fa fa-lock"></i>':'').
        ($this->isNotAccessible && $mark ? ' <i class="fa fa-bolt"></i>':'');
    }
    return $this->name;
  }

	public function getPath()
	{
		return $this->path;
	}

	public function getSize($formated=false,$noDirSize=false)
	{
		if($noDirSize && $this->isDir) return '';
		return $formated && !$this->isDir?self::formatSizeUnits($this->size):$this->size;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getTypeGroup()
	{
		switch(strtolower($this->type))
		{
			case 'mp3':
			case 'vaw':
				return 'Audio';
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'bmp':
				return 'Images';
			case 'avi':
			case 'cam':
			case 'flv':
			case 'mov':
			case 'mpeg':
			case 'mpg':
			case 'swf':
			case 'wmv':
			case 'mp4':
			case 'ogg':
			case 'webm':
				return 'Videos';
			case 'dir':
				return 'Dirs';
			default:
				return 'All';
		}
	}

	public function getFullName($mark=FALSE)
	{
		return $this->getName().$this->getExtension().
        ($this->isProtected && $mark ? ' <i class="fa fa-lock"></i>':'').
        ($this->isNotAccessible && $mark ? ' <i class="fa fa-bolt"></i>':'');
	}

	public function getExtension()
	{
		return ($this->isDir?'':'.'.$this->type);
	}

	public function getFullPath($thumb=false)
	{
		$tmb = $thumb?'thumbs/':'';
		if($this->isDir)
		{
			$ret=$this->rootFile.HREF_GLUE.'root='.$this->EZGRoot.'&id='.$this->EZGId.
				(!$this->isParLink||$this->inSubfolder?'&loc='.$this->name:'');
			if(!$this->inSubfolder&&$this->isParLink)
				$ret .= '&loc='.$this->name; //added to set &loc=0 to the root
			return $ret;
		}
		if($thumb && !is_file($this->path.'/'.$tmb.$this->getFullName()))
		{
			if(!$this->isDir && preg_match('/(jpg|jpeg|png|bmp|gif)$/i',$this->getExtension()))
			{
					$newFilePathThumb=$this->path.'/'.$tmb.$this->getFullName();
					$tmpFilePath=$this->path.'/'.$this->getFullName();
					include_once ML_ROOT_PATH.'SimpleImage.php';
					$image = new SimpleImage();
					$image->load($tmpFilePath);
					$image->resizeToWidth(150,FALSE);  //thumb
					$image->save($newFilePathThumb,$image->image_type);
					unset($image);
			}
			else $tmb='';
		}
		return $this->path.'/'.$tmb.$this->getFullName();
	}

	public function getEZGRoot()
	{
		return $this->EZGRoot;
	}

	public function copy()
	{
		return;
	}

	public static function formatSizeUnits($bytes)
	{
		if($bytes>=1073741824)
		{
			$bytes=number_format($bytes/1073741824,2).' GB';
		}
		elseif($bytes>=1048576)
		{
			$bytes=number_format($bytes/1048576,2).' MB';
		}
		elseif($bytes>=1024)
		{
			$bytes=number_format($bytes/1024,2).' KB';
		}
		else
		{
			$bytes=$bytes.' B';
		}

		return $bytes;
	}

}

?>

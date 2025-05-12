<?php
define('LBL_TITLE',$this->ca->lang_l('title'));
define('LBL_FILE_SIZE',$this->ca->lang_l('size'));
define('LBL_FILE_DATE',$this->ca->lang_l('date'));
define('LBL_TYPE',$this->ca->lang_l('type'));
define('LBL_DELETE',$this->ca->lang_l('delete'));
define('LBL_DOWNLOAD',$this->ca->lang_l('download'));
define('LBL_ADD_FOLDER',$this->ca->lang_l('add folder'));
define('LBL_UPLOAD_FILES',$this->ca->lang_l('upload files'));
define('LBL_SEACRH_MEDIA',$this->ca->lang_l('search'));
define('LBL_DROP_FILES',$this->ca->lang_l('drop files'));
define('LBL_RESIZE_TO',$this->ca->lang_l('resize to'));
define('LBL_SHOW_MORE',$this->ca->lang_l('show more'));
define('LBL_PROTECT_FOLDER',$this->ca->lang_l('protect folder'));
define('LBL_UNPROTECT_FOLDER',$this->ca->lang_l('unprotect folder'));


define('ML_ROOT_PATH','../ezg_data/media_library/');
define('HREF_GLUE','&');
//$_REQUEST['vmode']='norm';
$_REQUEST['noFullThumb']=1;
require_once '../ezg_data/functions.php';
Session::intStart();
/*if(isset($_GET['action'])&&$_GET['action']=='admin_login')
  $_SESSION['mediaLibAdmin']=true;
if(isset($_GET['action'])&&$_GET['action']=='admin_logout')
  unsetSessions();
if(isset($_GET['user_login']))
  $_SESSION['MLUser'] =$_GET['uid']=$_POST['uid']=$_REQUEST['uid']=$_GET['user_login'];
if(isset($_GET['user_logout']))
  unsetSessions();

function unsetSessions()
{
  if(isset($_SESSION['MLUser']))
    unset($_SESSION['MLUser']);
  if(isset($_SESSION['mediaLibAdmin']))
    unset($_SESSION['mediaLibAdmin']);
}*/

if($this->ca->user->isAdmin() || $this->ca->user->isAdminUser())
  $_SESSION['mediaLibAdmin']=$this->ca->user->isAdmin() || $this->ca->user->isAdminUser();
elseif(isset($_SESSION['cur_user'.$this->f->proj_id]))
  $_SESSION['MLUser']=$_SESSION['cur_user'.$this->f->proj_id];
include_once ML_ROOT_PATH.'MediaLibrary.php';
$lib=new MediaLibrary('centraladmin.php?process=media_library'/*basename(__FILE__)*/,'../innovaeditor/assets',$this->f->innova_asset_def_size);
$lib->parse();
?>

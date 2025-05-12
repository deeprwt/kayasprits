<?php
//$rootPath = isset($_REQUEST['root']) && $_REQUEST['root']=='../'?'../':'';
define('ML_ROOT_PATH_ML','../../ezg_data/media_library/');
define('ML_ROOT_PATH','');
define('HREF_GLUE','?');
$_REQUEST['noFullThumb']=1;
require_once '../../ezg_data/functions.php';
Session::intStart();
/*if(isset($_GET['action'])&&$_GET['action']=='admin_login')
	$_SESSION['mediaLibAdmin']=true;
if(isset($_GET['action'])&&$_GET['action']=='admin_logout')
	unsetSessions();
if(isset($_GET['user_login']))
	$_SESSION['MLUser']	=$_GET['uid']=$_POST['uid']=$_REQUEST['uid']=$_GET['user_login'];
if(isset($_GET['user_logout']))
	unsetSessions();

function unsetSessions()
{
	if(isset($_SESSION['MLUser']))
		unset($_SESSION['MLUser']);
	if(isset($_SESSION['mediaLibAdmin']))
		unset($_SESSION['mediaLibAdmin']);
}*/

if(isset($_SESSION['SID_ADMIN'.$f->proj_id]))
	$_SESSION['mediaLibAdmin']=true;
elseif(isset($_SESSION['cur_user'.$f->proj_id])){
	$data_user=User::getUser($_SESSION['cur_user'.$f->proj_id],'');
	if($data_user['user_admin']==1) $_SESSION['mediaLibAdmin']=true;
	else $_SESSION['MLUser']=$_SESSION['cur_user'.$f->proj_id];
}
include_once ML_ROOT_PATH_ML.'MediaLibrary.php';
$lib=new MediaLibrary(basename(__FILE__),'../assets',$f->innova_asset_def_size);
$lib->parse();
?>

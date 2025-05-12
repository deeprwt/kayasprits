<?php
/*
	centraladmin.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/

define('CA_USER_TABLES_CNT',6);
define('POLL_QUESTIONS_FIELD_COUNT',8);
define('CA_LOGIN_COOKIE_EXPIRE',30);//days
define('CA_DB_UPDATEID',6);
define('USER_ACCESS_FILEDS',6);
define('MESSENGER_MESSAGES_FILEDS',10);
define('MESSENGER_FRIENDS_FILEDS',6);
define('MESSENGER_SETTINGS_FILEDS',4);
$ca_pref=(is_dir('ezg_data'))?'':'../';
include_once($ca_pref.'ezg_data/functions.php');
include_once($ca_pref.'ezg_data/mysql.php');

if(!isset($thispage_id))
 $thispage_id=(isset($_GET['pageid'])?intval($_GET['pageid']):'');

class FBCustom
{

	public static $LOG_IN_OUT_URL;

	public $handler;
	public $fbUser;

	public function __construct($key,$secret)
	{
		global $thispage_id;
		$this->handler = new Facebook(array(
			'appId'  => $key,
			'secret' => $secret,
			'cookie' => true
		));

		$this->getFBUser();
		if($this->fbUser)
			self::$LOG_IN_OUT_URL = 'fbloging.php?logout';
		else
			self::$LOG_IN_OUT_URL = $this->handler->getLoginUrl(array(
				'scope'=>'email',
				'redirect_uri'=>Linker::buildSelfURL('centraladmin.php').'?process=fblogin&pageid='.$thispage_id)
				);
	}

	private function getFBUser()
	{
		$fbuser=$this->handler->getUser();		// Get User ID

		// We may or may not have this data based on whether the user is logged in.
		// If we have a $user id here, it means we know the user is logged into
		// Facebook, but we don't know if the access token is valid. An access
		// token is invalid if the user logged out of Facebook.

		if ($fbuser) {
			try // Proceed knowing you have a logged in user who's authenticated.
			{
				$this->fbUser=$this->handler->api('/me?fields=email,first_name,last_name,name,picture');
			}
			catch (FacebookApiException $e)
			{
				error_log($e);
				$this->fbUser=null;
				$this->handler->setSession(null);
			}
		}
	}
}

class CaClass
{
	public $version='ezgenerator centraladmin V4 - 5.7.5.21 mysql';

	public $ca_lang_set_fname;
	public $ca_user_fieldtypes=array('userinput','checkbox','listbox','avatar');

	protected $ca_myprofile_actions=array('changepass','editprofile','myprofile',"mymessenger",'vieworders','stat','showprofile');
	protected $ca_admin_actions=array("index","users","groups","polls","fixgroups","remusrfromgrp","messenger",
																		"assigntogroup","processuser","processgroup","confcounter",
																		"resetcounter","log","media_library","maillog","history","confreg",
																		"regfields","getfield","pendingreg","settings","export","confreglang",
																		'login','mail_users','fvalues','import','import2','toggleFold',
																		"log_errors",'analytics','setRpp');
	protected $ca_other_actions=array();
	protected $ca_user_actions=array("changepass","editprofile","mymessenger","myprofile",'register','register2',
		 "forgotpass","forgotpass2",'login','showprofile');
	public $ca_abs_url;
	public $template_in_root=false;
	protected $ca_template_file;

	public $ca_reg_lang_settings_keys=array('sr_email_subject','sr_email_msg','sr_notif_subject','sr_notif_msg','sr_forgotpass_subject0','sr_forgotpass_msg0','sr_forgotpass_subject','sr_forgotpass_msg','sr_activated_subject','sr_activated_msg','sr_admin_activated_subject','sr_admin_activated_msg','sr_blocked_subject','sr_blocked_msg','sr_created_account_subject','sr_created_account_msg');
	public $ca_lang_l=array();
	public $ca_lang='';
	public $ca_lang_template='';
	public $ca_l='';
	public $ca_l_amp='';
	public $ca_ulang_id=0;//user interface current language
	public $ca_rel_path;
	public $ca_prefix='';
	public $ca_access_type=array();
	public $ca_access_type_ex=array();

	public $ca_action_id='';
	public $ca_site_url;
	public $thispage_id_inca;

	//global vars used to prevent multiple call of same query (get_calendar_categories function)
	public $calendar_pages=false;
	protected $tb_cal_cat=false;

	public $user;
	public $f;
	public $isFBLogged=false; 	//logged via FB flag

	//classes
	public $login;
	public $groups;
	public $registrations;
	public $sitemap;
	public $page_is_mobile;
	public $users;
	public $macros;
	public $user_is_admin;
	public $ca_scripts='';
	public $ca_css='';
	public $ca_dependencies=array();
	public $inp_width='width:500px;';

	public function __construct($ca_pre,$thispage_id)
	{
		global $user,$f,$ca_pref;

		$this->f=$f;
		$this->ca_rel_path=$ca_pref;
		$this->ca_other_actions=array_merge(array("logout","logoutadmin","version","register","register2","fblogin",
			 "captcha","loggedinfo","loggeduser","loggedusername","logged","logoutinfo","forgotpass","forgotpass2","sitemap","prev","next"),
			 $this->ca_myprofile_actions);
		$this->page_is_mobile=Mobile::detect(2);
		if($this->page_is_mobile)
			$this->inp_width='width:83%';

		$this->ca_prefix=$ca_pre;
		$this->ca_lang_set_fname=$this->ca_prefix.'ezg_data/ca_lang_set.txt';

		$this->ca_template_file=Detector::defineSourcePage(
				$this->ca_prefix,(isset($_GET['lang'])?strtoupper(Formatter::stripTags($_GET['lang'])):''));

		$this->ca_abs_url=Linker::buildSelfURL('centraladmin.php',false,$this->ca_rel_path);
		if(strpos($this->ca_abs_url,'documents/')===false)
		  $this->ca_abs_url=Linker::buildSelfURL('documents/centraladmin.php',false,$this->ca_rel_path);

		if(strpos($this->ca_template_file,'/')===false)
		{
			$this->ca_template_file='../'.$this->ca_template_file;
			$this->template_in_root=true;
		}

		$this->ca_site_url=str_replace('documents/centraladmin.php','',$this->ca_abs_url);
		$this->thispage_id_inca=$thispage_id;

		$this->user=isset($user) && $user instanceof User ? $user : new User();

		$this->login =new ca_login($this);
		$this->groups =new ca_groups($this);
		$this->users = new ca_users($this);
		$this->registrations = new ca_registrations($this,$this->user);
		$this->sitemap = new ca_sitemap($this);
		$this->macros = new ca_macros($this);
	}

	public function lang_l($label)
	{
		$r=(isset($this->ca_lang_l[$label])?$this->ca_lang_l[$label]:$label);
		return $r;
	}

	// basic functions
	public function ca_update_language_set($new_lang='')
	{
		$user_actions=array_merge(array('forgotpass','register','forgotpass2','register2'),$this->ca_myprofile_actions);
		$this->ca_lang=$this->f->ca_settings['language'];
		if(in_array($this->ca_action_id,$this->ca_myprofile_actions))
		{
			Session::intStart('private');
			if($this->user->userCookie())
				$logged_user=$this->user->getUserCookie();
		}
		if(!empty($new_lang))
		{
			$this->ca_lang=strtoupper($new_lang);
			$rl_flag=true;
		}
		elseif(isset($_REQUEST['lang'])&&in_array($this->ca_action_id,$user_actions))
		{
			$this->ca_lang=strtoupper(Formatter::stripTags($_REQUEST['lang']));
			$rl_flag=true;
		}
		elseif(isset($logged_user)&&isset($_COOKIE[$logged_user.'_lang']))
		{
			$this->ca_lang=strtoupper(Formatter::stripTags($_COOKIE[$logged_user.'_lang']));
			$rl_flag=true;
		}
		elseif(isset($_COOKIE['ca_lang']))
			$this->ca_lang=strtoupper(Formatter::stripTags($_COOKIE['ca_lang']));
		elseif($this->ca_lang!='')
			$this->ca_lang=strtoupper($this->ca_lang);
		elseif(isset($_REQUEST['lang']))
			$this->ca_lang=strtoupper(Formatter::stripTags($_REQUEST['lang']));
		else
		{
			$sitemap_data=CA::getSitemap($this->ca_prefix,false);
			$this->ca_lang=$this->f->inter_languages_a[array_search($sitemap_data[0][16],$this->f->site_languages_a)];
		}

		if(!array_key_exists($this->ca_lang,$this->f->names_lang_sets))
			$this->ca_lang='EN';

		$lang_set_new=File::readLangSet($this->ca_lang_set_fname,$this->ca_lang,'ca');
		if(isset($lang_set_new['lang_l']))
			$this->ca_lang_l=$lang_set_new['lang_l'];

		$this->ca_l=(isset($_GET['lang']) && $this->ca_lang!='EN'?'&lang='.$this->ca_lang:'');

		$this->ca_l_amp=(isset($_GET['lang'])&&$this->ca_lang!='EN'?'&amp;lang='.$this->ca_lang:'');
		$this->ca_access_type=array('0'=>$this->lang_l('view'),'1'=>$this->lang_l('edit'),'2'=>$this->lang_l('admin'));
		$this->ca_access_type_ex=array('0'=>$this->lang_l('view'),'1'=>$this->lang_l('edit'),'2'=>$this->lang_l('page level'),'-1'=>'no access','5'=>$this->lang_l('admin'));
		$this->ca_ulang_id=array_search($this->ca_lang,$this->f->inter_languages_a);
		$myprofile_labels=CA::getMyprofileLabels($this->thispage_id_inca,$this->ca_prefix,(isset($rl_flag)?$this->ca_lang:''));
		$this->ca_lang_l=array_merge($myprofile_labels,$this->ca_lang_l);

		$lang_labels=ca_labels::fetch_labels($this->ca_lang);
		foreach($this->ca_reg_lang_settings_keys as $k=> $v)
		{
			if(isset($lang_labels[$v]))
				$this->ca_lang_l[$v]=$lang_labels[$v];
		}

		$all_reg_fields_array=array_merge($this->f->ca_users_fields_array,array('repeat password'=>0,'code'=>1));
		foreach($all_reg_fields_array as $k=> $v)
		{
			if(isset($lang_labels[$k]) && !empty($lang_labels[$k]))
				$this->ca_lang_l[$k]=$lang_labels[$k];
		}
	}

	public function ca_addassets($output,$root)
	{
		if(strpos($output,'onclick="openAsset')!=false)
		{
			$output=Builder::includeBrowseDialog($output,$this->ca_site_url);
			$in_lang=isset($this->f->names_lang_sets[$this->ca_lang])?$this->f->names_lang_sets[$this->ca_lang]:'english';
			$in_lang=(in_array($in_lang,$this->f->innova_lang_list))?$in_lang:'english';
			$langl=isset($this->f->innova_lang_list[$in_lang])?$this->f->innova_lang_list[$in_lang]:$this->f->innova_lang_list['english'];

			if(!$this->f->tiny)
			{
				$editor_js=str_replace(array('%EDITOR_LANGUAGE%','%RELPATH%','%XLANGUAGE%'),
					array($in_lang,$this->ca_site_url,$langl),
					$this->f->editor_js);
				$output=str_replace('</head>',' '.$editor_js.' </head>',$output);
			}
		}
		return $output;
	}

# ----------------- build HTML functions

	public function ca_resolve_template($lang_templ_page,&$ca_template,&$templ_root)
	{
		if($lang_templ_page && $this->ca_lang_template!='')
		{
			$ca_template=$this->ca_lang_template;
			$templ_root=(strpos($ca_template,'/')===false);
			if($templ_root)
				$ca_template='../'.$ca_template;
		}
		else
		{
			$ca_template=$this->ca_template_file;
			$templ_root=$this->template_in_root;
		}
	}

	public function GT($html_output,$ignore_fullScreen=false,$title='',$lang_templ_page=false,$css='')
	{
		$ca_template=$this->ca_lang_template;
		$templ_root=(strpos($ca_template,'/')===false);
		$this->ca_resolve_template($lang_templ_page,$ca_template,$templ_root);

		if(strpos($ca_template,'template_source')!==false)
		{
			$dm=$this->sitemap->get_templateSource();
			if(in_array($this->ca_action_id,$this->ca_user_actions)&&Mobile::detect($dm))
				$this->f->template_source=str_replace('template_source','i_template_source',$this->f->template_source);
		}

		$contents=Formatter::fmtInTemplate($ca_template,$html_output,'','','',true,false,false,(strpos($ca_template,'.php')!==false),$ignore_fullScreen);

		$contents=str_replace(Formatter::GFSAbi($contents,'<title>','</title>'),'<title>'.(($title=='')?$this->lang_l('administration panel'):$title).'</title>',$contents);
		if((!$this->f->ca_fullscreen || $ignore_fullScreen) && $templ_root)
		{
			$contents=str_replace('</title>','</title>
				 <base href="'.$this->ca_site_url.'">',$contents);
			$contents=str_replace('<img id="ima_avatar" src="../innova','<img id="ima_avatar" src="innova',$contents);
		}


		$contents=preg_replace("'<\?php.*?\?>'si",'',$contents);
		if($css!='')
			$contents=str_replace('<!--scripts-->','<!--scripts-->
				<style type="text/css">
				 '.$css.'
				</style>
				',$contents);
		$contents=$this->ca_addassets($contents,$templ_root);
		if(strpos($contents,'passreginput')!==false)
		{
			$rel_path=($this->template_in_root?'':'../');
			$contents=Builder::includeScript('$(document).ready(function(){$(".passreginput").pmeter();})',$contents,array(),$rel_path);
		}
		if($this->page_is_mobile)
			$contents=str_replace('detect.js','nodetect.js',$contents);//temp fix for mobile, has to be removed

		if(!$ignore_fullScreen && $this->f->ca_fullscreen)
			$contents=str_replace(array('href="documents/','src="extimages/','src="jquery_utils','src="documents/','href="extimages/','$(document).ready(function(){$(".hidden").updateMenu(true);});'),
							array('href="','src="../extimages/','src="../jquery_utils','src="','href="../extimages/',''),$contents);



		return $contents;
	}

	public function get_calendar_categories($username='',$lang='')
	{
		global $db;

		$categories=array();
		if($this->calendar_pages===false)
			$this->calendar_pages=$this->sitemap->ca_get_pages_list(CALENDAR_PAGE,$lang);
		if($username!==false&&$username=='')
			$username=$this->user->getUserCookie();
		$user_account=$username!==false?User::mGetUser($username,$db,'username',true,false):false;

		foreach($this->calendar_pages as $v)
		{
			$prot_page_info=$this->sitemap->get_page_info($v['pageid']);
			if($user_account!==false&&$v['protected']=='TRUE'&&!User::mHasReadAccess($user_account,$prot_page_info))
				continue;
			$file_contents='';
			if(strpos($v['url'],'../')===false)
				$v['url']='../'.$v['url'];
			$fp=@fopen($v['url'],'r');
			if($fp)
			{
				$file_contents=fread($fp,4096);
				fclose($fp);
			}
			if(!empty($file_contents))
			{
				if(strpos($file_contents,'$em_enabled=TRUE;')!==false||strpos($file_contents,'$em_enabled=true;')!==false)
				{
					$new_cat_id=array();
					$new_cat_name=array();
					$new_cat_vis=array();
					$cat_ids_arr[]=1;
					$cat_names_arr[]="General";
					$cat_visib_arr[]='yes';

					if($this->tb_cal_cat===false)
						$this->tb_cal_cat=$db->get_tables($v['pageid'].'_categories');
					if(!empty($this->tb_cal_cat))
					{
						$cat_all_raw=$db->fetch_all_array('SELECT cid,cname,visible FROM '.$db->pre.$v['pageid'].'_categories');
						foreach($cat_all_raw as $v_cal)
						{
							if($v_cal['cid']>0)
							{
								$new_cat_id[]=$v_cal['cid'];
								$new_cat_name[]=Formatter::unEsc($v_cal['cname']);
								$new_cat_vis[]=$v_cal['visible'];
							}
						}
						if(!empty($new_cat_id))
						{
							$cat_names_arr=$new_cat_name;
							$cat_ids_arr=$new_cat_id;
							$cat_visib_arr=$new_cat_vis;
						}

						foreach($cat_names_arr as $kk=> $vv)
						{
							if(isset($cat_visib_arr[$kk])&&$cat_visib_arr[$kk]=='yes')
								$categories[]=array('pageid'=>$v['pageid'],'pagename'=>$v['name'],'catid'=>$cat_ids_arr[$kk],'catname'=>str_replace('"','',$vv));
						}
					}
				}
			}
		}
		return $categories;
	}

	public function get_ca_setting($key)
	{
		return isset($this->f->ca_settings[$key])?$this->f->ca_settings[$key]:'';
	}

	public function user_navigation($only_username=false,$return_flag=false,$profile_link=true) //called from outside
	{
		 $this->macros->user_navigation($only_username,$return_flag,$profile_link);
	}

	protected function ca_check_data()
	{
		global $db;

		$tb_a=$db->get_tables('ca_users');
		$return=false;
		if(count($tb_a)!=CA_USER_TABLES_CNT || isset($_REQUEST['check'])){
			$return = CA::create_ca_db($this->ca_prefix);
			CA::insert_setting(array(
				'user_access_fields'=>USER_ACCESS_FILEDS,
				'messenger_messages_fields'=>MESSENGER_MESSAGES_FILEDS,
				'messenger_friends_fields'=>MESSENGER_FRIENDS_FILEDS,
				'messenger_settings_fields'=>MESSENGER_SETTINGS_FILEDS)
			);
		}

		CA::fetchDBSettings($db);
		if(!isset($this->f->ca_settings['data_check']) || $this->f->ca_settings['data_check']!=CA_DB_UPDATEID)
		{
			$return = CA::create_ca_db($this->ca_prefix);
			CA::insert_setting(array(
				'data_check'=>CA_DB_UPDATEID,
				'user_access_fields'=>USER_ACCESS_FILEDS,
				'messenger_messages_fields'=>MESSENGER_MESSAGES_FILEDS,
				'messenger_friends_fields'=>MESSENGER_FRIENDS_FILEDS,
				'messenger_settings_fields'=>MESSENGER_SETTINGS_FILEDS)
			);
		}
		
		CA::fetchDBSettings($db);
		if(!$return && (!isset($this->f->ca_settings['user_access_fields']) || $this->f->ca_settings['user_access_fields']!=USER_ACCESS_FILEDS || 
		!isset($this->f->ca_settings['messenger_messages_fields']) || $this->f->ca_settings['messenger_messages_fields']!=MESSENGER_MESSAGES_FILEDS ||
		!isset($this->f->ca_settings['messenger_friends_fields']) || $this->f->ca_settings['messenger_friends_fields']!=MESSENGER_FRIENDS_FILEDS ||
		!isset($this->f->ca_settings['messenger_settings_fields']) || $this->f->ca_settings['messenger_settings_fields']!=MESSENGER_SETTINGS_FILEDS)){
			CA::create_ca_db($this->ca_prefix);
			CA::insert_setting(array(
				'user_access_fields'=>USER_ACCESS_FILEDS,
				'messenger_messages_fields'=>MESSENGER_MESSAGES_FILEDS,
				'messenger_friends_fields'=>MESSENGER_FRIENDS_FILEDS,
				'messenger_settings_fields'=>MESSENGER_SETTINGS_FILEDS)
			);
		}

		if($this->f->site_url!=''&&!isset($this->f->ca_settings['site_check']))
		{
			CA::insert_setting(array('site_check'=>'1'));
			$errno='';
			$errstr='';
			$trackback_url="http://miro.image-line.com/max4_jquery/documents/index2.php?action=trackback&entry_id=1367094307&title=site_test";
			$trackback_url=parse_url($trackback_url);
			$query_string='title='.urlencode($this->f->site_url).'&url='.urlencode($this->f->site_url).'&blog_name=ezg&excerpt=MySQL';
			$http_request='POST '.$trackback_url['path'].(isset($trackback_url['query'])?'?'.$trackback_url['query']:'')." HTTP/1.0\r\n"
				.'Host: '.$trackback_url['host']."\r\n"
				.'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'."\r\n"
				.'Content-Length: '.strlen($query_string)."\r\n"
				."User-Agent: EZGenerator/"
				."\r\n\r\n"
				.$query_string;
			$fs=@fsockopen($trackback_url['host'],80,$errno,$errstr,4);
			@fputs($fs,$http_request);
			@fclose($fs);
		}

		if(isset($_REQUEST['action'])&&$_REQUEST['action']=='dbcheck')
			Linker::redirect($_REQUEST['url']);
	}

	protected function handle_fvalues()
	{
		$data=$this->sitemap->ca_get_pages_list();
		$res='';
		foreach($data as $page)
		{
			if(isset($page['url']))
			{ //set proper internal URLs
				$pg_url=$page['url'];
				$pg_url=$this->ca_site_url.str_replace('../','',$pg_url);
				$res.=$pg_url.'<><><>'.$page['name'].'#';
			}
		}
		if(strlen($res)>1)
			$res=substr($res,0,strlen($res)-1);
		print $res;
		exit;
	}

	public function getAvatarPath($value)
	{
		$av_path=$value==''?$this->get_ca_setting('c_avatar'):$value;
		if($av_path!='')
		  $av_path=strpos($av_path,'http')!==false?$av_path:$this->ca_site_url.$av_path;
		return $av_path;
	}

	public function detect_profile_template(&$ca_gt_page)
	{
		$ca_page_id=(isset($_REQUEST['id'])?intval($_REQUEST['id']):0);
		$lang='EN';
		if(isset($_REQUEST['lang']))
			$lang=$_REQUEST['lang'];
		elseif($ca_page_id>0)
		{
			$pageid_info=CA::getPageParams($ca_page_id,'../',true);
			if(isset($pageid_info[16]))
				$lang=$this->f->inter_languages_a[array_search($pageid_info[16],$this->f->site_languages_a)];
		}

		$ca_sitemap=CA::getSitemap($this->f->ca_rel_path,false,true);

		foreach($this->f->ca_profile_templates_a as $v)
			if($v!='0' && isset($ca_sitemap[$v]) && $ca_sitemap[$v]['22']==$lang)
			{
				$ca_gt_page=$ca_sitemap[$v][1];
				return true;
			}

		foreach($this->f->ca_profile_templates_a as $v)
			if($v!='0' && isset($ca_sitemap[$v]))
			{
				$ca_gt_page=$ca_sitemap[$v][1];
				return true;
			}

		return false;
	}

	public function process()
	{
		global $db;

		$this->ca_action_id=(empty($_GET)&&empty($this->thispage_id_inca) && !isset($_REQUEST['check']))?'index':'';
		$this->ca_action_id=(isset($_REQUEST['process'])?Formatter::stripTags($_REQUEST['process']):$this->ca_action_id);
		if($this->ca_action_id=='up')
			$this->macros->get_userpages();
		if($this->ca_action_id=='slideshow')
			$this->macros->get_slideshow_page();
		if(($this->ca_action_id!='')&&!in_array($this->ca_action_id,$this->ca_other_actions)&&!in_array($this->ca_action_id,$this->ca_admin_actions))
			$this->ca_action_id='index';

		$db=DB::dbInit($this->f->db_charset,($this->f->uni?$this->f->db_charset:''));
		if($db!==false)
			$this->ca_check_data();
		else
			exit;

		if(isset($this->f->ca_settings['user_fields']))
			$this->f->ca_users_fields_array=unserialize($this->f->ca_settings['user_fields']);

		if(!isset($this->f->ca_users_fields_array['avatar']))
			$this->f->ca_users_fields_array['avatar']=array("display"=>"avatar","type"=>"varchar(255)", "opt"=>"NOT NULL default ''","itype"=>"avatar","system"=>"1","req"=>"1","hidinprof"=>"0","hidinreg"=>"1");

		$this->ca_update_language_set();
		// needed here in order to define $this->f->mobile_detected
		$this->ca_lang_template=Detector::defineSourcePage(
			$this->ca_prefix,
			$this->ca_lang,
			$this->page_is_mobile,
			(in_array($this->ca_action_id,$this->ca_user_actions)||in_array($this->ca_action_id,$this->ca_myprofile_actions)?true:'')
			);

		if(in_array($this->ca_action_id,$this->ca_myprofile_actions)&&$this->user->getUserCookie()=='')
			$this->ca_action_id='index';
		else
			Session::intStart("private");

		if($this->ca_action_id=='logout'||$this->ca_action_id=="logoutadmin")
			$this->login->logout_user();
		elseif($this->ca_action_id=="version")
			echo $this->version;
		elseif($this->ca_action_id=='setRpp')
		{
			CA::insert_setting(array('max_rec_on_admin'=>intval($_REQUEST['rpp'])));
			echo '1';
			return;
		}
		elseif($this->ca_action_id=="next"||$this->ca_action_id=="prev")
			$this->macros->redirect_to_nextprev_page($this->ca_action_id);
		elseif($this->ca_action_id=="register"||$this->ca_action_id=="register2")
			$this->registrations->process_register($this->ca_action_id=="register2");
		elseif(in_array($this->ca_action_id,array("loggedinfo","loggeduser","logged","loggedusername")))
			$this->macros->handle_logged_macros();
		elseif($this->ca_action_id=='fblogin')
			$this->login->process_fblogin(true);//this here is the $float param. Not sure how to determine it yet
		elseif($this->ca_action_id=="forgotpass"||$this->ca_action_id=="forgotpass2")
			$this->registrations->process_forgotpass();
		elseif($this->ca_action_id=='sitemap')
			ca_sitemap::sitemap_dump($this->ca_prefix);
		elseif(in_array($this->ca_action_id,$this->ca_admin_actions)) //admins interface actions
		{
			Session::intStart();
			$logged_user_data=$this->user->mGetLoggedUser($db,'');
			$this->user_is_admin=$logged_user_data['user_admin']===1;
			if($this->user->isEZGAdminNotLogged())
			{
				if(!$this->user_is_admin)
				{
					if(isset($_REQUEST['pv_username']))
						$this->login->handle_logins();
					else  //not logged and not login, show default login form
					{
						$output=$this->login->build_login_form('',true);
						print $output;
						exit;
					}
				}
			}

			if(in_array($this->ca_action_id,array("assigntogroup",'groups',"fixgroups","assigntogroup","remusrfromgrp","processgroup")))
			{
				$groups_screen =new ca_groups_screen($this);
				$groups_screen->handle_screen($logged_user_data);
			}
			if($this->ca_action_id=="polls")
			{
				$poll_screen = new ca_poll_screen($this);
				$poll_screen->handle_screen();
			}
			if($this->ca_action_id=="index")
			{
				$admin_sitemap =new ca_sitemap_screen($this);
				$admin_sitemap->show_sitemap();
			}
			elseif(in_array($this->ca_action_id,array("users","processuser","pendingreg","mail_users",'import','import2',"export","assigntogroup")))
			{
				 $manage_users = new ca_manage_users_screen($this);
				 $manage_users->handle_screen($logged_user_data);
			}
			elseif(in_array($this->ca_action_id,array("confcounter","resetcounter","confreg",'regfields','getfield',"confreglang","settings")))
			{
				$manage_settings = new ca_settings_screen($this);
				$manage_settings->handle_screen();
			}
			elseif($this->ca_action_id=="log" || $this->ca_action_id=="log_errors")
			{
				$ca_log = new ca_log_screen($this);
				$ca_log->handle_screen();
	 		}
			elseif($this->ca_action_id == 'media_library')
			{
				$ml = new ca_media_library($this);
				$ml->handle_screen();
			}
			elseif($this->ca_action_id == 'analytics' && $this->f->ca_fullscreen)
			{
				$ml = new ca_analytics_screen($this);
				$ml->handle_screen();
			}
			elseif($this->ca_action_id=="history")
			{
				$ca_history = new ca_history_screen($this);
				$ca_history->handle_screen();
			}
			elseif($this->ca_action_id=="maillog")
			{
				$logged_user_data=$this->user->mGetLoggedUser($db,'',true);
				$ca_maillog = new ca_maillog_screen($this);
				$ca_maillog->handle_screen($logged_user_data);
			}
			elseif($this->ca_action_id=="messenger")
			{
				$messenger=new ca_messenger_screen($this,$logged_user_data);
				$messenger->process_messenger();
			}
			elseif($this->ca_action_id=="toggleFold")
				CA::setCaMiniCookie(isset($_REQUEST['fold'])&&($_REQUEST['fold']=='1'));
			elseif($this->ca_action_id=='fvalues')
				$this->handle_fvalues();
		}
		elseif(in_array($this->ca_action_id,$this->ca_myprofile_actions)) // user profile actions
		{
			Session::intStart();
			$logged_user_data=$this->user->mGetLoggedUser($db,'',true);
			$user_is_admin=$logged_user_data['user_admin']===1;
			if(isset($logged_user_data['uid'])&&$logged_user_data['uid']>-1) //access allowed
			{
				$userName=$logged_user_data['username'];
				$all_view_access=$this->user->mHasAllReadAccess($logged_user_data,true);

				if(isset($_GET['setlang']))
				{
					setcookie($userName.'_lang',strtoupper(Formatter::stripTags($_GET['setlang'])),mktime(23,59,59,1,1,2037),str_replace('http://'.Linker::getHost(),'',$this->ca_site_url));
					$this->ca_update_language_set(strtoupper(Formatter::stripTags($_GET['setlang'])));
				}
				if($this->ca_action_id=='showprofile')
				{
					$ca_gt_page='';
					if($all_view_access && $this->detect_profile_template($ca_gt_page))
					{
						$profile= new custom_profile($this);
						$profile->process_custom_profile($logged_user_data,$ca_gt_page);
					}
					else
					{
						$lp=CA::getDBSettings($db,'landing_page')==1;
						if($lp)
						{
							$this->ca_action_id="editprofile";
							$editprofile_screen = new ca_editprofile_screen($this,$logged_user_data);
							$editprofile_screen->process_editprofile();
						}
						else
						{
							$user_sitemap =new ca_user_sitemap_screen($this,$logged_user_data);
							$user_sitemap->show_sitemap();
						}
					}
				}
				elseif($this->ca_action_id=="changepass")
				{
					$ca_editpassword = new ca_editpassword_screen($this,$logged_user_data);
					$ca_editpassword->process_changepass();
				}
				elseif($this->ca_action_id=="editprofile")
				{
					$editprofile_screen = new ca_editprofile_screen($this,$logged_user_data);
					$editprofile_screen->process_editprofile();
				}
				elseif($this->ca_action_id=="vieworders")
				{
					$vieworders_screen = new ca_vieworders_screen($this,$logged_user_data);
					$vieworders_screen->process_vieworders();
				}
				elseif($this->ca_action_id=="stat" && ($user_is_admin||$this->f->ca_settings['sr_users_seecounter']=='1'))
				{
					 $user_sitemap =new ca_user_sitemap_screen($this,$logged_user_data);
					 $user_sitemap->show_graphs($user_is_admin);
				}
				elseif($this->ca_action_id=="myprofile")
				{
					$user_sitemap =new ca_user_sitemap_screen($this,$logged_user_data);
					$user_sitemap->show_sitemap();
				}
				elseif($this->ca_action_id=="mymessenger")
				{
					$messenger=new ca_messenger_screen($this,$logged_user_data);
					$messenger->process_messenger();
				}
			}
		}
		else
			$this->login->handle_logins();
	}
}

class ca_functions
{
	public static function format_output_date($dt)
	{
		return date('d M Y H:i:s',Date::tzone($dt));
	}

	public static function is_nulldate($d)
	{
		 return $d=='0000-00-00 00:00:00' || $d=='' || $d==NULL;
	}

	public static function ca_un_esc($s)
	{
		return htmlspecialchars(str_replace(array('\\\\','\\\'','%%%'),array('\\','\'','"'),$s),ENT_QUOTES);
	}
}

class ca_labels
{
	public static function fetch_labels($lang)
	{
		global $db;

		$lang_labels=array();
		$lang_labels_raw=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.'ca_lang_labels
			WHERE lang= "'.addslashes($lang).'"');
		if(!empty($lang_labels_raw))
		{
			foreach($lang_labels_raw as $v)
				$lang_labels[$v['lkey']]=$v['lval'];
		}
		return $lang_labels;
	}

	public static function insert_label($data)
	{
		global $db;

		$k=$data['lkey'];
		$v=$data['lval'];
		$l=$data['lang'];
		$exist_rec=$db->query_first('
			SELECT *
			FROM '.$db->pre.'ca_lang_labels
			WHERE lkey="'.$k.'"AND lang="'.$l.'"');
		if(empty($exist_rec))
			$db->query_insert('ca_lang_labels',array('lkey'=>$k,'lval'=>$v,'lang'=>$l));
		else
			$db->query_update('ca_lang_labels',array('lkey'=>$k,'lval'=>$v,'lang'=>$l),'lkey="'.$k.'" AND lang="'.$l.'"');
	}
}

class ca_db
{
	public static function db_count($table,$where='')
	{
		global $db;

		$count_raw=$db->fetch_all_array('
			SELECT COUNT(*)
			FROM '.$db->pre.$table.($where!=''?'
			WHERE '.$where:''),true);
		if($count_raw===false)
			return 0;
		else
			return $count_raw[0]['COUNT(*)'];
	}

	public static function remove_user($user_id)
	{
		global $db;
		if(is_array($user_id))
		{
			$usrs=array();
			foreach($user_id as $uid)
			{
				$usrid=explode('|',$uid); //returns uid in [0] (and maybe self_reg_hash in [1])
				if($usrid[0] != (int) $usrid[0])
					continue; //this users id is not integer
				$usrs[]=$usrid[0];
			}
			$usrs=implode(',',$usrs);
			$db->query('DELETE FROM '.$db->pre.'ca_users WHERE uid IN ('.$usrs.')');
			$db->query('DELETE FROM '.$db->pre.'ca_users_access WHERE user_id IN ('.$usrs.')');
			$db->query('DELETE FROM '.$db->pre.'ca_users_news WHERE user_id IN ('.$usrs.')');
		}
		else
		{
			if($user_id != (int) $user_id)
				return false;
			$db->query('DELETE FROM '.$db->pre.'ca_users WHERE uid = '.$user_id);
			$db->query('DELETE FROM '.$db->pre.'ca_users_access WHERE user_id = '.$user_id);
			$db->query('DELETE FROM '.$db->pre.'ca_users_news WHERE user_id = '.$user_id);
		}
	}
}

class ca_objects
{
	public $ca;
	public $f;

	public function __construct($ca_object)
	{
		global $f;

		if($ca_object instanceof CaClass)
			$this->ca=$ca_object;

		$this->f=$f;
	}
}

class ca_sitemap_viewer extends ca_objects
{

	public function get_pageloads(&$counter_stat,&$unique_stat,&$mobile_stat,&$events_on,&$events_stat)
	{
		global $db;

		$counter_stat['loads']=$counter_stat['unique']=$counter_stat['first']=$counter_stat['returning']=0;
		$events_stat=array();

		$counter_totals_raw=$db->fetch_all_array('SELECT * FROM '.$db->pre.'counter_totals');
		$counter_pages_raw=$db->fetch_all_array('SELECT * FROM '.$db->pre.'counter_pageloads');
		$counter_pages_raw_unique=$db->fetch_all_array('
			SELECT page_id,count(*)
			FROM '.$db->pre.'counter_details
			WHERE (visit_type="r" OR visit_type ="f")
			GROUP BY page_id');

		$counter_pages_mobile=$db->fetch_all_array('
			SELECT page_id,count(*)
			FROM '.$db->pre.'counter_details
			WHERE mobile=1
			GROUP BY page_id');

		foreach($counter_totals_raw as $k=> $v)
			$counter_stat[$v['total_type']]=$v['total'];

		foreach($counter_pages_raw as $k=> $v)
		{
			if($events_on||isset($v['eventcount']))
			{
				$events_stat[$v['page_id']]=$v['eventcount'];
				$events_on=true;
			}
			$counter_stat[$v['page_id']]=$v['total'];
		}

		foreach($counter_pages_mobile as $k=>$v)
			$mobile_stat[$v['page_id']]=$v['count(*)'];

		foreach($counter_pages_raw_unique as $k=> $v)
			$unique_stat[$v['page_id']]=$v['count(*)'];
	}

	public function build_sitemap_area(&$output,$logged_user_data=null,$sitemap_params=array())
	{
		if(!is_array($sitemap_params)||empty($sitemap_params))
			$sitemap_params=array('page_title','admin_link','page_loads','unique_visitors');

		if($logged_user_data==null)
			$user_is_admin=$all_edit_access=$all_view_access=true;
		else
		{
			$user_is_admin=$logged_user_data['user_admin']===1;
			$show_counter_data=$this->f->counter_on&&($user_is_admin||$this->f->ca_settings['sr_users_seecounter']=='1');
			$all_edit_access=(isset($logged_user_data['access'])&&$logged_user_data['access'][0]['section']=='ALL'&&intval($logged_user_data['access'][0]['access_type'])>0);
			$all_view_access=$this->ca->user->mHasAllReadAccess($logged_user_data,true);
		}

		$pages_list=$this->ca->sitemap->ca_get_pages_list();
		$events_stat=$counter_stat=$unique_stat=$mobile_stat=array();
		$events_on=false;
		$show_counter_data=$this->f->counter_on&&($user_is_admin||$this->f->ca_settings['sr_users_seecounter']=='1');

		if($show_counter_data)
		{
			$search_results=array("name"=>"Search",
				 "id"=>"0",
				 "url"=>'documents/search.php',
				 "protected"=>"FALSE",
				 "pprotected"=>false,
				 "hidden"=>"FALSE",
				 "section"=>"0",
				 "subpage"=>"0",
				 "subpage_url"=>"",
				 "lang"=>"Internal Search",
				 "pageid"=>"1",
				 "editable"=>"FALSE",
				 "adminurl"=>"");
			$pages_list[]=$search_results;
			$this->get_pageloads($counter_stat,$unique_stat,$mobile_stat,$events_on,$events_stat);
		}

		if(in_array('page_title',$sitemap_params))
			$cap_arrays[]=$this->ca->lang_l('page name');
		if(in_array('admin_link',$sitemap_params))
			$cap_arrays[]=$this->ca->lang_l('admin link');
		if($show_counter_data)
		{
			if(in_array('page_loads',$sitemap_params))
				$cap_arrays[]=$this->ca->lang_l('pageloads');
			if(in_array('unique_visitors',$sitemap_params))
				$cap_arrays[]=$this->ca->lang_l('unique visitors');
		}
		if($user_is_admin) $cap_arrays[]='ID';
		$table_data=array();

		$lang_flag=$cat_flag='';
		$global_forms=array();

		foreach($this->f->subminiforms as $fid=> $p_id)
		{
			$sc_p_id=$p_id;
			$is_page=($p_id>0&&isset($pages_list[$p_id]));
			if(!$is_page && $p_id>0)
			{
				$p_id=CA::getParentPage($p_id,$pages_list);
				$is_page=$p_id!==false;
			}
			$page_url=$is_page?$pages_list[$p_id]['url']:'javascript:void(0);';
			$sub_parent_url=($p_id=='0')?'../documents/':((strpos($page_url,'../')===false)?'../':'../'.Formatter::GFS($page_url,'../','/').'/');
			$sub_dir=$this->ca->ca_site_url.str_replace('../','',$sub_parent_url);

			$sub_url='href="'.$sub_dir.'ezgmail_'.$sc_p_id.'_'.$fid.'.php?action=index';
			$page_text='<span class="rvts8">&nbsp;&nbsp; ('.$this->ca->lang_l('request').')</span>';
			$admin_text='
				<span class="rvts8">[</span>
				<a class="rvts12" '.$sub_url.'">'
					.$this->ca->lang_l('edit').'
				</a>
				<span class="rvts8">]</span>';
			if($is_page)
				$pages_list[$p_id]['forms'][]=array($page_text,$admin_text);
			else
				$global_forms[]=array($page_text,$admin_text);
		}

		foreach($this->f->subminiforms_news as $fid=> $p_id)
		{
			$sc_p_id=$p_id;
			$is_page=($p_id>0&&isset($pages_list[$p_id]));
			if(!$is_page && $p_id>0)
			{
				$p_id=CA::getParentPage($p_id,$pages_list);
				$is_page=$p_id!==false;
			}
			$page_url=$is_page?$pages_list[$p_id]['url']:'javascript:void(0);';
			$sub_parent_url=($p_id=='0')?'../documents/':((strpos($page_url,'../')===false)?'../':'../'.Formatter::GFS($page_url,'../','/').'/');
			$sub_dir=$this->ca->ca_site_url.str_replace('../','',$sub_parent_url);
			$sub_url='href="'.$sub_dir.'newsletter_'.$fid.'.php?action=index';
			$page_text='<span class="rvts8">&nbsp;&nbsp; ('.$this->ca->lang_l('newsletter').')</span>';

			$admin_text='
				<span class="rvts8">[</span>
					<a class="rvts12" '.$sub_url.'">'
					.$this->ca->lang_l('edit').'
					</a>
				<span class="rvts8">]</span>';
			if($is_page)
				$pages_list[$p_id]['forms'][]=array($page_text,$admin_text);
			else
				$global_forms[]=array($page_text,$admin_text);
		}

		foreach($pages_list as $k=> $v)
		{
			$page_text=$admin_text=$counter_text='';
			$edit_flag=$all_edit_access;
			$view_flag=($all_edit_access||$all_view_access);

			if(isset($v['ext']) && $v['ext']=='TRUE')
				continue;
			else if(isset($v['id']))
			{
				if(!$user_is_admin)
				{
					foreach($logged_user_data['access'] as $u_access_v)
					{
						$is_sec=(isset($v['section']) && $v['section']==$u_access_v['section']);
						if(isset($v['section']))
						{
							if($u_access_v['page_id']!=0)
							{
								if($v['pageid']==$u_access_v['page_id'])
								{
									$at=$u_access_v['access_type'];
									$edit_flag=$view_flag=false;

									if($at==EDIT_ACCESS || $at==EDIT_OWN_ACCESS || $at==ADMIN_ON_PAGE)
										$edit_flag=$view_flag=true;
									elseif($at==VIEW_ACCESS)
										$view_flag=true;

									break;
								}
							}
							elseif($is_sec && $u_access_v['access_type']==VIEW_ACCESS)
							{
								$view_flag=true;
								break;
							}
							elseif($is_sec && ($u_access_v['access_type']==EDIT_ACCESS || $u_access_v['access_type']==ADMIN_ACCESS||$u_access_v['access_type']==ADMIN_ON_PAGE))
							{
								$edit_flag=$view_flag=true;
								break;
							}
						}
					}

				}

				$ca_ctrl=(in_array($v['id'],$this->f->sp_pages_ids)||$v['editable']=='TRUE'||isset($v['forms']));
				$user_see_all=$this->f->ca_settings['sr_users_see_all']=='1';
				if($user_is_admin || ($ca_ctrl&&$edit_flag || $v['protected']=='TRUE' && $view_flag) || $user_see_all)
				{
					if($lang_flag!=$v['lang'])
					{
						$lang_flag=$v['lang'];
						$table_data[]='<span class="a_lang_label">'.$lang_flag.'</span>';
					}

					if($cat_flag!='')
					{
						$table_data[]='<span class="a_lang_label">'.$cat_flag.'</span>';
						$cat_flag='';
					}

					$v_url=$this->ca->ca_site_url.str_replace('../','',$v['url']);

					$is_view=$v['id']==BLOG_VIEW||$v['id']==PHOTOBLOG_VIEW;
					$page_text.='
						<span class="rvts8">'.(($v['subpage']=='1')||$is_view?'&nbsp;&nbsp;&nbsp;&nbsp;- ':':: ').'</span>
						<a target="_blank" class="rvts8 nodec" href="'.$v_url.'">'.$v['name'].'</a>';

					$row_hidden=false;
					$ptype=intval($v['id']);
					if(in_array($ptype,$this->f->sp_pages_ids) && in_array($ptype,array(OEP_PAGE,SURVEY_PAGE))===false)
					{
						$c_table_n='';
						$pid=$v['pageid'];
						if(in_array($ptype,array(BLOG_PAGE,PHOTOBLOG_PAGE,PODCAST_PAGE)))
						{
							$c_table_n=$pid.'_posts';
							$c_where='publish_status="published"';
						}
						elseif($ptype==CALENDAR_PAGE)
						{
							$c_table_n=$pid.'_events';
							$c_where='1';
						}
						elseif($ptype==GUESTBOOK_PAGE)
						{
							$c_table_n=$pid.'_posts';
							$c_where='approved=1';
						}
						elseif($ptype==NEWSLETTER_PAGE)
						{
							$c_table_n=$pid.'_subscribers';
							$c_where='ss_confirmed=1';
						}
						elseif($ptype==REQUEST_PAGE)
						{
							$c_table_n='ca_email_data';
							$c_where='page_id="'.$pid.'"';
						}
						elseif(in_array($ptype,array(SHOP_PAGE,CATALOG_PAGE)))
						{
							$c_table_n=$pid.'_data';
							$c_where='publish=1';
						}

						$count=ca_db::db_count($c_table_n,$c_where);
						$page_text.='<span class="rvts8"> ('.$count.')</span>';
					}
					else $row_hidden=in_array($ptype,array(
									SHOP_CATEGORY_PAGE,SHOP_PRODUCT_PAGE,SHOP_CART_PAGE,SHOP_CHECK_PAGE,
									SHOP_RETURN_PAGE,SHOP_ERROR_PAGE,CATALOG_CATEGORY_PAGE,CATALOG_PRODUCT_PAGE));

					if($edit_flag && $ca_ctrl)
					{
						if(in_array($ptype,$this->f->sp_pages_ids)||$v['editable']=='TRUE')
						{
							$admin_url=$this->ca->ca_site_url.str_replace('../','',$v['adminurl']);
							$admin_text.='<span class="rvts8">[</span>
								<a class="rvts12" href="'.$admin_url.'">'.$this->ca->lang_l('edit').'</a>
								<span class="rvts8">]</span>';
						}
					}
					if($edit_flag && isset($v['forms']))
					{
						$page_text.=F_BR;
						$admin_text.=F_BR;
						foreach($v['forms'] as $fv)
						{
							$page_text.=F_BR.$fv[0];
							$admin_text.=F_BR.$fv[1];
						}
					}

					$row_data=array();
					if(in_array('page_title',$sitemap_params))
						$row_data[]=$page_text.($v['protected']=='TRUE'?'&nbsp;&nbsp;<span class="rvts8"><i title="'.$this->ca->lang_l('protected').'" class="fa fa-lock"></i><span>':'');
					if(in_array('admin_link',$sitemap_params))
						$row_data[]=$admin_text;

					if($show_counter_data)
					{
						$loads=(isset($counter_stat[$v['pageid']]))?$counter_stat[$v['pageid']]:$this->ca->lang_l('na');
						$mobile_loads=(isset($mobile_stat[$v['pageid']]))?'/'.$mobile_stat[$v['pageid']]:'';
						$url=$this->ca->ca_abs_url.'?process='.($user_is_admin?'index':'stat').'&amp;stat=detailed'.$this->ca->ca_l.'&amp;pid='.$v['pageid'].'&amp;purl='.$v_url.'&amp;pname='.$v['name'];

						if(in_array('page_loads',$sitemap_params))
						{
							if($loads==$this->ca->lang_l('na'))
								$row_data[]='<span class="rvts8">'.$loads.'</span>';
							else
							{
								$row_data[]=$edit_flag?'
									<div class="rvts8 ca_details_c">'.$loads.$mobile_loads.'
										<div class="ca_details"><i class="fa fa-area-chart" onclick="document.location=\''.$url.'&amp;f=a\'" title="'.$this->ca->lang_l('details').'"></i></div>
									</div>':'';
							}
						}
						if(in_array('unique_visitors',$sitemap_params))
						{
							if($loads==$this->ca->lang_l('na'))
								$row_data[]='';
							else
							{
								$loads=(isset($unique_stat[$v['pageid']]))?$unique_stat[$v['pageid']]:$this->ca->lang_l('na');
								$row_data[]=$edit_flag?'
									<div class="rvts8 ca_details_c">'.$loads.'
										<div class="ca_details"><i class="fa fa-area-chart" onclick="document.location=\''.$url.'&amp;f=u\'" title="'.$this->ca->lang_l('details').'"></i></div>
									</div>':'';
							}
						}
					}

					if($user_is_admin)
						$row_data[]='<span class="rvts8'.($row_hidden?' row_hidden':'').'">'.$v['pageid'].'</span>';
					$table_data[]=$row_data;
				}
			}
			elseif(!empty($v['name']))
				$cat_flag=$v['name'];
		}

		$table_data[]='&nbsp;';
		if($user_is_admin||$all_edit_access)
			foreach($global_forms as $k=> $v)
			{
				$row_data=array();
				if(in_array('page_title',$sitemap_params))
					$row_data[]=$v[0];
				if(in_array('admin_link',$sitemap_params))
					$row_data[]=$v[1];
				if($show_counter_data)
				{
					if(in_array('page_loads',$sitemap_params))
						$row_data[]='';
					if(in_array('unique_visitors',$sitemap_params))
						$row_data[]='';
				}
				if($user_is_admin)
					$row_data[]='';
				$table_data[]=$row_data;
			}

		if($show_counter_data && $all_edit_access)
		{
			$d_st='<span class="rvts8">[</span><a class="rvts12" href="'.$this->ca->ca_abs_url.'?process='.($user_is_admin?'index':'stat').'&amp;stat=detailed'.$this->ca->ca_l;
			$d_end=$this->ca->lang_l('details').'</a><span class="rvts8">]</span>';
			$l=$counter_stat['loads'];
			$u=$counter_stat['unique'];
			$frst=$counter_stat['first'];
			$r=$counter_stat['returning'];

			$counter_text='
				<p><span class="rvts8">'.$this->ca->lang_l('total pageloads').': '.$l.'</span>&nbsp;&nbsp;'.($l!='0'?$d_st.'&amp;f=h">'.$d_end:'').'</p>
				<p><span class="rvts8">'.$this->ca->lang_l('unique visitors').': '.$u.'</span>&nbsp;&nbsp;'.($u!='0'?$d_st.'&amp;f=u">'.$d_end:'').'</p>
				<p><span class="rvts8">'.$this->ca->lang_l('first time visitors').': '.$frst.'</span>&nbsp;&nbsp;'.($frst!='0'?$d_st.'&amp;f=f">'.$d_end:'').'</p>
				<p><span class="rvts8">'.$this->ca->lang_l('returning visitors').': '.$r.'</span>&nbsp;&nbsp;'.($r!='0'?$d_st.'&amp;f=r">'.$d_end:'').'</p>';
		}
		$row_data=array();
		if(in_array('page_title',$sitemap_params))
			$row_data[]='';
		if(in_array('admin_link',$sitemap_params))
			$row_data[]='';
		if($show_counter_data)
		{
			if(in_array('page_loads',$sitemap_params))
				$row_data[]=$counter_text;
			if(in_array('unique_visitors',$sitemap_params))
				$row_data[]='';
		}
		if($user_is_admin)
			$row_data[]='';
		$table_data[]=$row_data;
		$output.=Builder::adminTable('',$cap_arrays,$table_data);
	}
}

class ca_log
{
	public static function write_log($change,$uid,$user,$result='')
	{
		global $db;

		$ip=Detector::getIP();
		$typechange=array(
			 "reg"=>"Register",
			 "conf"=>"Confirmation",
			 "confadmin"=>"Confirmation (Admin)",
			 "forgotpass"=>"Forgotten pass",
			 "changepass"=>"Change pass",
			 "editprofile"=>"Edit profile",
			 "resend"=>"Confirmation email resend",
			 "login"=>"Login",
			 "logout"=>"Logout",
			 "imp"=>"Import");
		$uid=$uid<0?0:$uid;
		$db->query_insert('ca_log',
			array('date'=>Date::buildMysqlTime(),
					'activity'=>$typechange[$change],
					'user'=>$user,
					'result'=>$result,
					'ip'=>$ip,
					'uid'=>$uid));
	}

	public static function clear_log()
	{
		global $db;

		$db->query('DELETE FROM '.$db->pre.'ca_log');
	}

	public static function clear_errors(){
		global $db;
		$db->query('DELETE FROM '. $db->pre . 'user_errors');
	}
}

class ca_users extends ca_objects
{
	public function edit_user_acccess($user_id,$access_data=array())
	{
		global $db;

		$res=$db->query('
			DELETE
			FROM '.$db->pre.'ca_users_access
			WHERE user_id='.$user_id);
		if($res!==false)
		{
			if(empty($access_data))
				$access_data=$this->build_access_array($user_id);
			foreach($access_data as $acc)
				$db->query_insert('ca_users_access',$acc);
		}

		return $access_data;
	}

	public function build_access_array($user_id)
	{
		global $db;

		$access_data=array();
		if(isset($_POST["select_all"])&&$_POST["select_all"]=='no')
		{
			$section_range=$this->ca->sitemap->ca_get_prot_pages_list(true);
			foreach($section_range as $val)
			{
				$pid=$val['id'];
				$limit_own_post=isset($_POST["limit_own_post".$pid])?$_POST["limit_own_post".$pid]:'0';
				if(isset($_POST["access_to_page".$pid]))
					$access_data[]=array('user_id'=>$user_id,'section'=>'0','page_id'=>$pid,
						 'access_type'=>$_POST["access_to_page".$pid],'limit_own_post'=>$limit_own_post);
			}
		}
		elseif(isset($_POST["select_all"])&&$_POST["select_all"]=='yesw')
			$access_data[]=array('user_id'=>$user_id,'section'=>"ALL",'access_type'=>1);
		elseif(isset($_POST["select_all"])&&$_POST["select_all"]=='yeswa')
			$access_data[]=array('user_id'=>$user_id,'section'=>"ALL",'access_type'=>9);
		elseif(isset($_POST["select_all"])&&$_POST["select_all"]=='from_group')
		{
			$access=$db->query_singlevalue('
				 SELECT custom_data
				 FROM '.$db->pre.'ca_users_groups
				 WHERE id='.(int)$_POST['move_to_grp']);
			$access=str_replace(-9999,$user_id,$access);
			$access_data=unserialize($access);
			$this->ca->groups->assign_users_to_group(array($user_id),(int)$_POST['move_to_grp']);
		}
		else
			$access_data[]=array('user_id'=>$user_id,'section'=>"ALL",'access_type'=>0);

		return $access_data;
	}

	public function build_news_array($user_id)
	{
		$result=array();
		if(isset($_POST["news_for"])) //news - event manager
		{
			foreach($_POST["news_for"] as $v)
			{
				if(strpos($v,'%')!==false)
					list($p,$c)=explode('%',$v);
				else
				{
					$p=$v;
					$c='';
				}
				$result[]=array('user_id'=>$user_id,'page_id'=>$p,'category'=>$c);
			}
		}
		return $result;
	}

	public function set_password($uid,$pwd)
	{
		global $db;
		if($uid!==false)
			$db->query('
				UPDATE '.$db->pre.'ca_users
				SET password="'.crypt($pwd).'"
				WHERE uid='.$uid);
	}

	public function edit_details($user_id,$user_data)
	{
		global $db;
		$db->query_update('ca_users',$user_data,'uid='.$user_id);
		$db->query('
			DELETE
			FROM '.$db->pre.'ca_users_news
			WHERE user_id='.$user_id);
		$news_data=$this->ca->users->build_news_array($user_id);
		foreach($news_data as $news)
			$db->query_insert('ca_users_news',$news);
	}

	public function get_form_fields($edit_access=false)
	{
		global $db;

		$user_data=array();
		$field_names=$db->db_fieldnames('ca_users');
		foreach($field_names as $v)
		{
			if($v=='password')
			{
				if(isset($_REQUEST[$v]))
					 $user_data[$v]=crypt($_REQUEST[$v]);
			}
			elseif($edit_access && $v=='pass_changeable')
				$user_data[$v]=isset($_REQUEST[$v])?'1':'0';
			elseif(isset($_REQUEST[$v]))
				$user_data[$v]=Formatter::stripTags($_REQUEST[$v]);
			elseif($edit_access && $this->f->ca_users_fields_array[$v]['itype']=='checkbox')
				$user_data[$v]=isset($_REQUEST[$v])?'1':'0';
		}

		return $user_data;
	}

	public function duplicated_user_check($user)
	{
		global $db;

		$user_exists=$db->query_first('
			SELECT *
			FROM '.$db->pre.'ca_users
			WHERE username="'.addslashes($user).'"');
		return ($this->ca->login->ca_usernameCheck($user) || !empty($user_exists));
	}

	public function duplicated_email_check($email,$uid=0)
	{
		global $db;

		$mails=$db->fetch_all_array('
			SELECT email
			FROM '.$db->pre.'ca_users
			WHERE email="'.addslashes(Formatter::stripTags($email)).'"'.($uid>0?' AND uid != '.$uid:''));
		return (count($mails)>0);
	}

	public function change_status($uid,$status)
	{
		global $db;
		$db->query('
			UPDATE '.$db->pre.'ca_users
			SET status='.$status.'
			WHERE uid='.$uid);
	}

	public function remove_user($uid)
	{
		global $db;

		ca_db::remove_user($uid);
		$this->ca->calendar_pages=$this->ca->sitemap->ca_get_pages_list(CALENDAR_PAGE);
		foreach($this->ca->calendar_pages as $v)
		{
			$exist_tb=$db->get_tables($v['pageid'].'_registration','');
			if(!empty($exist_tb))
				$db->query('
					DELETE
					FROM '.$db->pre.$v['pageid'].'_registration
					WHERE user='.$uid);
			$this->ca->groups->remove_usr_from_grp($uid);
		}
	}

	public function set_useraccess($uid)
	{
		global $db;

		$access=array();
		$access_str=$this->f->ca_settings['sr_access'];
		if(strpos($this->f->ca_settings['sr_access'],'from_group')!==false)
		{
			$access=explode('|',$this->f->ca_settings['sr_access']);
			$this->ca->groups->assign_users_to_group(array($uid),$access[1]);
			return;
		}

		if($access_str=='')
			$access_str='ALL%%0';
		$temp_access=explode('|',$access_str);

		foreach($temp_access as $v)
		{
			$t=explode('%%',$v);
			$page_level_str=Formatter::GFS($v,'(',')');
			if(!empty($page_level_str))
				$t[1]=str_replace('('.$page_level_str.')','',$t[1]);
			if($t[1]=='2')
			{
				$page_level_arr=explode(';',$page_level_str);
				foreach($page_level_arr as $vv)
				{
					$value=explode('%',$vv);
					$access[]=array('section'=>$t[0],'page_id'=>$value[0],'access_type'=>$value[1]);
				}
			}
			else
				$access[]=array('section'=>$t[0],'access_type'=>$t[1],'page_id'=>0);
		}

		$tempUser = new User();
		$tempUser->setData(array('uid'=>$uid));
		$tempUser->setAccess($db,$access);
		unset($tempUser);
	}


	public function register_new_user($post_user,$norm_reg,$predefData,&$siteUser)
	{
		global $db;

		$lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
		$hasPredefData = is_array($predefData) && !empty($predefData);
		$uniqueid=md5(uniqid(mt_rand(),true));
		$user_id=0;

		$user_data=$this->get_form_fields();
		$user_data['creation_date']=Date::buildMysqlTime();
		$user_data['self_registered']=1;
		$user_data['self_registered_id']=$uniqueid;
		$user_data['status']=($this->f->ca_settings['sr_require_approval']=='1'?0:1);
		$emial_require_approval = ($this->f->ca_settings['sr_emial_require_approval']=='1'?1:0);
		$user_data['confirmed']=0;
		if($hasPredefData)
			$user_data=array_merge($user_data,$predefData);
		if($post_user===null && isset($user_data['username']))
			$post_user=$user_data['username'];
		$newUser = new User();
		$newUser->setData($user_data);
		if(!$predefData)
		{
			if(isset($_POST['email']))
				$send_to_email=Formatter::stripTags($_POST["email"]);
			$link=$this->ca->ca_abs_url.'?id='.$uniqueid.'&process=register'.$this->ca->ca_l;
			$more_macros=array(
				 '%confirmurl%'=>$link,
				 '%confirmlink%'=>'<a href="'.$link.'">'.$link.'</a>',
				 '%username%'=>$post_user);
			$content=Formatter::parseMailMacros($this->ca->lang_l('sr_email_msg'),$newUser->getAllData(),$more_macros);
			$subject=Formatter::parseMailMacros($this->ca->lang_l('sr_email_subject'),$newUser->getAllData(),$more_macros);
			$result=MailHandler::sendMailCA($db,$content,$subject,$send_to_email);
			# admin notification
			if(!$user_data['status'] && $emial_require_approval)
			{
				$link=$this->ca->ca_abs_url.'?&process=pendingreg'.$this->ca->ca_l;
				$more_macros=array(
					'%adminlink%'=>'<a href="'.$link.'">'.$link.'</a>',
					'%username%'=>$post_user);
				$content=Formatter::parseMailMacros($this->ca->lang_l('sr_admin_activated_msg'),$newUser->getAllData(),$more_macros);
				$subject=Formatter::parseMailMacros($this->ca->lang_l('sr_admin_activated_subject'),$newUser->getAllData(),$more_macros);
				MailHandler::sendMailCA($db,$content,$subject,$this->f->ca_settings['sr_admin_email']);
			}
		}
		else
			$result=1;

		if($result=="1")
		{
			$log_msg='fail';
			$label=$lang_r['registration failed'];

			$user_id=$newUser->register($db);
			if($user_id!==false)
			{
				$this->set_useraccess($user_id);

				$news_data=$this->build_news_array($user_id);
				$newUser->setNews($news_data);
				$newUser->registerNews($db);

				$log_msg='success, email SENT';
				$label=$lang_r['registration was successful'];
			}
		}
		else
		{
			$log_msg='fail, email FAILED ('.Formatter::stripTags($result).')';
			$label='Email FAILED.';
		}
		$output=F_BR.'<div class="rvps1">'.($norm_reg?'<h5>'.$label.'</h5>':'<span class="field_label">'.$label.'</span>').'</div>';
		ca_log::write_log('reg',$user_id,$post_user,$log_msg);
		$siteUser=$newUser->getAllData();
		return $output;
	}

	//uploads avatar and returns the file path on success (or false on fail)
	public function upload_avatar($uid,$newFile,$copyFromURL=false)
	{
		$avatar_path='innovaeditor/assets/avatars/';
		$av_path=$this->ca->ca_prefix.$avatar_path;
		$newFileName=$copyFromURL?substr($newFile,strrpos($newFile,'/')+1):$newFile['name'];
		$fExt=strtolower(substr($newFileName,strpos($newFileName,'.')+1));
		//check the folder
		if(!is_dir($av_path))
			mkdir($av_path,0775);
		$dest=$av_path.$uid.'_avatar.'.$fExt;
		$ret_dest=$avatar_path.$uid.'_avatar.'.$fExt;
		if($copyFromURL)
			copy($newFile,$dest);//Joe: as $newFile is http(s) link, php5 is required for this to work
		else
			move_uploaded_file($newFile['tmp_name'],$dest);
		//all passed, return the file path
		return $ret_dest;
	}
}

class ca_groups extends ca_objects
{

	public function assign_users_to_group($users,$grp_id)
	{
		global $db;

		if($grp_id>0)
		{
			$grp_access=$db->query_singlevalue('
				SELECT custom_data
				FROM '.$db->pre.'ca_users_groups
				WHERE id='.$grp_id);

			foreach($users as $uid)
			{
				if((int)$uid>0)
				{
					$db->query('
						INSERT INTO '.$db->pre.'ca_users_groups_links (id,group_id,user_id)
						VALUES (NULL,'.$grp_id.','.$uid.')
						ON DUPLICATE KEY UPDATE group_id='.$grp_id.';');
					$grp_access_tmp=str_replace('-9999',$uid,$grp_access);
					$grp_access_tmp=unserialize($grp_access_tmp);
					$this->ca->users->edit_user_acccess($uid,$grp_access_tmp);
				}
			}
		}
	}

	public function groupReadOnlyAccess($grp)
	{
		$ro=array(VIEW_ACCESS,NO_ACCESS);
		$access_data=unserialize($grp['custom_data']);
		if(count($access_data)>1) //page level access
		{
			$access=1;
			foreach($access_data as $v)
				if(!in_array($v['access_type'],$ro))
					$access=0;
		}
		else
			$access=$access_data[0]['access_type']==0?1:0;
		return $access;
	}

	public function build_groups($title,$has_empty=true,$script='',$selected=-1)
	{
		global $db;

		$grps=$db->fetch_all_array('SELECT id,name,custom_data FROM '.$db->pre.'ca_users_groups');
		$appnd='';
		if(count($grps)>0)
		{
			$appnd='<span class="rvts8"> '.$title.':</span> <select id="grp_selector" name="move_to_grp">';
			if($has_empty)
				$appnd .= '<option value="-1" '.($selected==-1?'checked="checked"':'').' selected="selected"> - </option>';
			foreach($grps as $grp)
			{
				$access=$this->groupReadOnlyAccess($grp);
				$appnd.='<option rel="'.$access.'" value="'.$grp['id'].'" '.($selected==$grp['id']?'selected="selected"':'').'>'.$grp['name'].'</option>';
			}
			$appnd .='</select>';
			if($script!='')
				$appnd .= $script;
		}
		else
			$appnd='<span class="rvst8">'.$this->ca->lang_l('none groups').'</span>';

		return $appnd;
	}

	public function remove_usr_from_grp($uid)
	{
		global $db;

		$db->query('DELETE FROM '.$db->pre.'ca_users_groups_links WHERE user_id='.$uid);
		$_REQUEST['flag']='view';
	}

	public function fix_missing_users_links()
	{
		global $db;

		$db->query('
			DELETE `lnk`
			FROM `'.$db->pre.'ca_users_groups_links` AS `lnk`
			LEFT JOIN `'.$db->pre.'ca_users` AS `usr` ON `usr`.`uid`=`lnk`.`user_id`
			WHERE `usr`.`uid` IS NULL');
	}
}

class ca_macros extends ca_objects //external macros
{
	public function get_userpages()
	{
		global $db;

		$result='';
		Session::intStart("private");
		$db=DB::dbInit($this->f->db_charset,($this->f->uni?$this->f->db_charset:''));
		if($this->ca->user->isAdmin())
			$result='all';
		elseif($this->ca->user->userCookie())
		{
			$user_account=User::mGetUser($this->ca->user->getUserCookie(),$db,'username',true,false);

			if($user_account['access'][0]['section']=='ALL')
				$result='all';
			else
			{
				$controlled_pages=$protected_pages=$special_ids=array();
				$this->ca->login->assign_protected_pageIds($controlled_pages,$protected_pages,$special_ids);
				$page_ids=array_keys($protected_pages);
				$ct_ids=array();

				foreach($user_account['access'] as $v)
				{
					$pid=intval($v['page_id']);
					if(in_array($pid,$page_ids))
					{
						$at=intval($v['access_type']);
						if(in_array($pid,$special_ids))
							$access=($at==1)||($at==3)||($at==0)||($at==5);
						else
							$access=$at==0;
						if($access)
						{
							$result.='mi'.$pid.'|';
							if(!in_array($protected_pages[$pid],$ct_ids))
								$ct_ids[]=$protected_pages[$pid];
						}
					}
				}
				foreach($ct_ids as $v)
					$result.='ci'.$v.'|';
			}
		}
		if($result=='')
			$result='none';
		echo 'menue--'.$result.'--menue';

		if(isset($this->f->ca_settings['categories_inmenu'])&&$this->f->inmenu===true)
		{
			$categories_array = unserialize($this->f->ca_settings['categories_inmenu']);
			echo 'categ--'.json_encode($categories_array).'--categ';
			echo 'ssmenu--'.$this->f->ssmenu.'--ssmenu';
                        echo 'ttype--'.$this->f->ttype.'--ttype';
		}
		echo 'cookie_login--'.$this->f->ca_settings['cookie_login'].'--cookie_login';
		exit;
	}

	public function resolveUser(&$user_data,&$is_admin,&$is_user,&$user_is_admin,&$username)
	{
		global $db;

		$user_data=$this->ca->user->mGetLoggedUser($db,'');
		if($user_data!==false)
		{
			$is_admin=$user_data['uid']==-1;
			$is_user=$user_data['uid']>-1;
			$user_is_admin=$user_data['user_admin']===1;
			$username=$user_data['username'];
		}
	}

	public function user_navigation_float($return_flag=false)
	{
		$vert=isset($_REQUEST['vert'])&&($_REQUEST['vert']==1);
		$glu=$vert?'':' | ';
		$labels=CA::getMyprofileLabels($this->ca->thispage_id_inca,$this->ca->ca_prefix);
		$logged_as_label=(isset($_GET['logged_l'])?Formatter::sth(Formatter::stripTags($_GET['logged_l'])):'logged as');
		$pageid_info=CA::getPageParams($this->ca->thispage_id_inca,$this->ca->ca_prefix);
		$root=isset($pageid_info[1])&&strpos($pageid_info[1],'../')===false;
		$thispage_dir=$root?'documents/':'../documents/';
		$ca_url=$thispage_dir.'centraladmin.php?process=';
		$username='';
		$is_user=$is_admin=$user_is_admin=$user_data=false;
		$this->resolveUser($user_data,$is_admin,$is_user,$user_is_admin,$username);

		if(strtolower($logged_as_label)=='username')
			$heading=$username;
		elseif($is_admin||$is_user)
		{
			$ca_editlink=CA::defineAdminLink($pageid_info);
			$ref_url=(isset($pageid_info[1])?$pageid_info[1]:'');

			$heading='<li class="logged_info user">'.str_replace('%%username%%',$username,$labels['welcome']).'</li>';
			if($is_admin||$user_is_admin)
			{
				if(isset($pageid_info[4])&&in_array($pageid_info[4],$this->f->sp_pages_ids))
					$heading.='<li>'.$glu.'<a href="'.$ca_editlink.$this->ca->ca_l_amp.'">'.$labels['edit'].'</a></li>';
				$heading.='<li>'.$glu.'<a href="'.$ca_url.'index'.$this->ca->ca_l_amp.'">'.$labels['administration panel'].'</a></li>'.
					'<li class="logout_float">'.$glu.'<a href="'.$ca_url.'logoutadmin&amp;pageid='.$this->ca->thispage_id_inca.$this->ca->ca_l_amp.'">'.$labels['logout'].'</a></li>';
			}
			else
			{
				$access_type='';
				if(isset($pageid_info[4]) &&
						in_array($pageid_info[4],$this->f->sp_pages_ids) &&
						User::mHasWriteAccess2($username,$user_data,$pageid_info,$access_type))
					$heading.='<li>'.$glu.'<a href="'.$ca_editlink.$this->ca->ca_l_amp.'">'.$labels['edit'].'</a></li>';

				$ca_detailed_url='';$ca_detailed_label='';
				CA::get_user_profile_link($this->ca->thispage_id_inca,$thispage_dir,$this->ca->ca_lang,$ref_url,$ca_detailed_url,$ca_detailed_label);

				$logout_id=array_search($this->ca->thispage_id_inca,$this->f->ca_profile_templates_a);
				$logout_link=$logout_id===false?'&amp;pageid='.$this->ca->thispage_id_inca:'';

				$heading.=
					'<li>'.$glu.'<a href="'.$ca_detailed_url.'">'.$labels[$ca_detailed_label].'</a></li>'.
					'<li class="logout_float">'.$glu.'<a href="'.$ca_url.'logout'.$logout_link.$this->ca->ca_l_amp.'">'.$labels['logout'].'</a></li>';
			}
		}
		else
			$heading='<li class="logged_info guest">'.$labels['welcome guest'].'</li>';

		if(isset($_REQUEST['lang']) && in_array($_REQUEST['lang'],$this->f->inter_languages_a))
			$lang=$_REQUEST['lang'];
		else
			$lang=$this->f->inter_languages_a[0];
		$root=isset($_REQUEST['root'])?intval($_REQUEST['root']):1;

		$reg_links='';
		if($this->f->ca_settings['sr_enable'])
			$reg_links=$this->ca->lang_l('not a member').
				 ' <a id="login_register" href="'.$ca_url.'register2&amp;lang='.$lang.'&amp;root='.($root?'1':'0').'&amp;vert='.($vert?'1':'0').'">'.$this->ca->lang_l('register').'</a> ';
		if($this->f->ca_settings['pwchange_enable'])
			 $reg_links.=($reg_links!=''?'|':'').' <a id="forgot_pwd" href="'.$ca_url.'forgotpass2&amp;lang='.$lang.'&amp;vert='.($vert?'1':'0').'">'.$this->ca->lang_l('forgot password').'</a>';
		if($return_flag)
			return array($heading,$reg_links);
		else
			print $heading;
	}

	public function user_navigation($only_username=false,$return_flag=false,$profile_link=true)
	{
		global $db;

		$profile_link=!isset($_REQUEST['noprofile']);//noprofile : remove profile linke from logged info
		$labels=CA::getMyprofileLabels($this->ca->thispage_id_inca,$this->ca->ca_prefix);
		$logged_as_label=(isset($_GET['logged_l'])?Formatter::sth(Formatter::stripTags($_GET['logged_l'])):'logged as');
		$pageid_info=CA::getPageParams($this->ca->thispage_id_inca,$this->ca->ca_prefix);
		$thispage_dir=(isset($pageid_info[1])&&strpos($pageid_info[1],'../')===false)?'documents/':'../documents/';
		$username='';

		$is_admin=$is_user=false;
		$user_data=$this->ca->user->mGetLoggedUser($db,'');
		if($user_data!==false)
		{
			$is_admin=$user_data['uid']==-1;
			$is_user=$user_data['uid']>-1;
			$user_is_admin=$user_data['user_admin']===1;
			$username=$user_data['username'];
		}

		$heading='';
		if(strtolower($logged_as_label)=='username'||$only_username)
			$heading=$username;
		elseif($is_admin||$is_user)
		{
			$ca_url=$thispage_dir.'centraladmin.php?process=';
			$ref_url=(isset($pageid_info[1])?$pageid_info[1]:'');

			$heading.='<span class="rvts8 logged_span">'
								.str_replace('%%username%%',$username,$labels['welcome']).
								'</span> ';
			$sp_page=isset($pageid_info[4])&&in_array($pageid_info[4],$this->f->sp_pages_ids);
			if($is_admin||$user_is_admin)
			{
				if($sp_page)
					$heading.='| <a class="rvts12 logged_link" href="'.CA::defineAdminLink($pageid_info).$this->ca->ca_l_amp.'">'.$labels['edit'].'</a>';
				$heading.='| <a class="rvts12 logged_link" href="'.$ca_url.'index'.$this->ca->ca_l_amp.'">'.$labels['administration panel'].'</a> '
					.'| <a class="rvts12 logged_link" href="'.$ca_url.'logoutadmin&amp;pageid='.$this->ca->thispage_id_inca.$this->ca->ca_l_amp.'">'.$labels['logout'].'</a>';
			}
			else
			{
				$access_type='';
				if($sp_page&&User::mHasWriteAccess2($username,$user_data,$pageid_info,$access_type))
					$heading.='| <a class="rvts12 logged_link" href="'.CA::defineAdminLink($pageid_info).$this->ca->ca_l_amp.'">'.$labels['edit'].'</a>';

				if($profile_link)
				{
					$ca_detailed_url=$ca_detailed_label='';
					CA::get_user_profile_link($this->ca->thispage_id_inca,$thispage_dir,$this->ca->ca_lang,$ref_url,$ca_detailed_url,$ca_detailed_label);
					$heading.='| <a class="rvts12 logged_link" href="'.$ca_detailed_url.'">'.$labels[$ca_detailed_label].'</a>';
				}

				$heading.='| <a class="rvts12 logged_link" href="'.$ca_url.'logout&amp;pageid='.$this->ca->thispage_id_inca.$this->ca->ca_l_amp.'">'
						.$labels['logout'].'</a>';
			}
		}
		if($return_flag)
			return $heading;
		else
			print $heading;
	}

	public function handle_logged_macros()
	{
		if(!isset($_SERVER['HTTP_REFERER']))
		{
			Linker::redirect("centraladmin.php?process=index",false);
			exit;
		}
		else
		{
			if($this->ca->ca_action_id=="loggedinfo")
				$logged_info=$this->user_navigation(false,true);
			elseif($this->ca->ca_action_id=="loggedusername")
			{
				$is_user=$is_admin=$user_is_admin=$user_data=false;
				$this->resolveUser($user_data,$is_admin,$is_user,$user_is_admin,$username);
				$logged_info=$username;
			}
			elseif($this->ca->ca_action_id=="logged")
			{
				$logged_info=$this->user_navigation_float(true);
				if($this->f->ca_settings['fb_login']==1)
				{
					include_once($this->ca->ca_prefix.'ezg_data/fbsdk/src/facebook.php');
					$fbKey=$this->f->ca_settings['fb_key'];
					$fbSecret=$this->f->ca_settings['fb_secret'];
					$fb=new FBCustom($fbKey,$fbSecret);
					if($fb->fbUser===null &&!$this->ca->user->isCurrUserLogged())
						$logged_info[0] .= '<li>|</li><li id="login_fb"><a href="'.FBCustom::$LOG_IN_OUT_URL.'">'.$this->ca->lang_l('fb login').'</a></li>';
				}
				echo json_encode($logged_info);
				exit;
			}
			else
				$logged_info=$this->user_navigation(true,true);

			$out=isset($_REQUEST['nodw'])?$logged_info:"\ndocument.write(' $logged_info ');\n";
			echo $out;
		}
	}

	protected function redirect_to_nextprev_page($ca_action)
	{
		$all_pages=$this->ca->sitemap->ca_get_pages_list();
		$new_page='';
		foreach($all_pages as $k=> $v)
		{
			if(isset($v['pageid'])&&$v['pageid']==$_REQUEST['id'])
			{
				$c_lang=$v['lang'];
				$orig_page=$v['url'];
				$new_i=($ca_action=="next"?$k+1:$k-1);

				if(isset($all_pages[$new_i]['pageid']))
				{
					if($all_pages[$new_i]['hidden']=='FALSE'&&$all_pages[$new_i]['lang']==$c_lang)
						$new_page=$all_pages[$new_i]['url'];
					elseif($all_pages[$new_i]['lang']==$c_lang)
					{
						while(!isset($all_pages[$new_i]['hidden'])||$all_pages[$new_i]['hidden']=='TRUE')
						{
							if($ca_action=="next")
								$new_i++;
							else
								$new_i--;
						}
						if($all_pages[$new_i]['hidden']=='FALSE'&&$all_pages[$new_i]['lang']==$c_lang)
							$new_page=$all_pages[$new_i]['url'];
					}
				}
			}
		}
		if(empty($new_page)) $new_page=$orig_page;
		$new_page=(strpos($new_page,'../')===false?'../':'').$new_page;
		Linker::redirect($new_page,false);
		exit;
	}

	public function get_slideshow_page()
	{
		$sl_id=isset($_REQUEST['sid'])?intval($_REQUEST['sid']):0;
		$custom_css=isset($_REQUEST['css'])?htmlentities(trim(strip_tags($_REQUEST['css']))):'';
		if($sl_id!=0)
		{
			$slideshow=new Slideshow();
			$src='{%SLIDESHOW_ID('.$sl_id.')%}';
			$js=$css='';
			$dependencies=array();
			$slideshow->replaceSlideshowMacro($src,'../',$js,$css,$dependencies,true);
			if($src!='')
			{
				$src='<html><head><meta charset="'.$this->f->site_charsets_a[0].'"><script src="https://ajax.googleapis.com/ajax/libs/jquery/'.$this->f->jquery_ver.'/jquery.min.js"></script><script type="text/javascript" src="../jquery_utils.js"></script><!--scripts--><!--endscripts-->'.($custom_css!=''?'<style>'.$custom_css.'</style>':'').'</head><body>'.$src.'</body></html>';
				$src=Builder::includeScript($js,$src,$dependencies,'../');
				if($css!='')
					$src=Builder::includeCss($src,$css);
				print $src;
			}
		}
		exit;
	}
}

class ca_sitemap extends ca_objects //sitemap array handling
{
	private $ca_sitemap_arr=null;

	static function sitemap_dump($path_prefix)
	{
		$fc=(isset($_GET['pwd'])&&crypt($_GET['pwd'],'admin')=='adPTFL0iJCHec')?File::read($path_prefix.'sitemap.php'):'';
		print str_replace(array('<?php echo "hi"; exit; /*','*/ ?>'),array('',''),$fc);
		exit;
	}

	private function init_sitemapArray()
	{
		if($this->ca_sitemap_arr==null)
			$this->ca_sitemap_arr=CA::getSitemap($this->ca->ca_prefix);
	}

	public function get_templateSource()
	{
		$this->init_sitemapArray();
		$dm='0';
		foreach($this->ca_sitemap_arr as $v)
		{
			if(strpos($v[1],'template_source')!==false)
			{
				$dm=$v[24];
				break;
			}
		}
		return $dm;
	}

	public function get_shop()
	{
		$this->init_sitemapArray();
		$has_shop=false;
		foreach($this->ca_sitemap_arr as $v)
			if($v[4]==SHOP_PAGE)
			{
				$has_shop=true;
				break;
			}
		return $has_shop;
	}

	public function get_loginPage()
	{
		$this->init_sitemapArray();
		$id=false;
		foreach($this->ca_sitemap_arr as $v)
			if(isset($v[10]))
			{
				$id=$v[10];
				break;
			}
		return $id;
	}

	public function get_page_info($page_id) // gets info for protected page
	{
		$this->init_sitemapArray();
		$forms=array_merge($this->f->subminiforms,$this->f->subminiforms_news);
		if(array_key_exists($page_id,$forms) || ($page_id==0&&isset($_GET['pageid']) && array_key_exists($_GET['pageid'],$forms)))
			$page_id=$forms[isset($_GET['pageid'])?$_GET['pageid']:$page_id];

		$page=CA::getPageParamsArray($this->ca_sitemap_arr,$page_id);

		if(empty($page)) //checking if not logging on parent pages
		{
			$page_id=CA::getParentPage($page_id,$this->ca_sitemap_arr);
			$page=CA::getPageParamsArray($this->ca_sitemap_arr,$page_id);
		}

		return $page;
	}



	public function ca_get_pages_list($type_id='',$lang='')
	{
		$pages=array();

		$ca_sitemap_arr_cats_incl=CA::getSitemap($this->ca->ca_prefix,true);
		$cat_counter=1;
		foreach($ca_sitemap_arr_cats_incl as $v)
		{
			$buffer=array();
			$p_name=strpos($v[0],'#')!==false&&strpos($v[0],'#')==0?str_replace('#','',$v[0]):$v[0];
			if(isset($v[10]) && ($lang==''||$v[22]==$lang))
			{
				$buffer['name']=trim($p_name);
				$buffer['id']=trim($v[4]);
				$buffer['url']=$v[1];
				$protection=Validator::checkProtection($v);
				$buffer['protected']=($protection>1?'TRUE':'FALSE');
				$buffer['pprotected']=($protection==3); //we may use this in future
				$buffer['hidden']=$v[20];
				$buffer['section']=$v[7];
				$buffer['subpage']=$v[3];
				$buffer['subpage_url']=$v[18];
				$buffer['lang']=$v[16];
				$p_id=$v[10];
				$buffer['pageid']=$p_id;
				$buffer['editable']=$v[23];
				$buffer['ext']=isset($v[28])?$v[28]:'FALSE';
				if(in_array($v[4],$this->f->sp_pages_ids)||$v[23]=='TRUE')
					$buffer ['adminurl']=CA::defineAdminLink($v);
				$buffer['sub_ids']=$v['sub_ids'];
			}
			else
			{
				$p_id='ct_'.$cat_counter++;
				$buffer=array('name'=>trim($p_name));
			}
			if($type_id==''||isset($buffer['id'])&&$buffer['id']==$type_id)
				$pages[$p_id]=$buffer;
		}
		return $pages;
	}

	public function ca_get_prot_pages_list($include_editable=false)
	{
		$this->init_sitemapArray();
		$forms=array_merge($this->f->subminiforms,$this->f->subminiforms_news);
		$pages=array();
		foreach($this->ca_sitemap_arr as $v)
		{
			if(isset($v[10]))
			{
				$p_id=$v[10];

				$p_name=strpos($v[0],'#')!==false&&strpos($v[0],'#')==0?str_replace('#','',trim($v[0])):trim($v[0]);
				$ca_control=(in_array($v[4],$this->f->sp_pages_ids) ||
						Validator::checkProtection($v)>1 ||
						(in_array($p_id,$this->f->ca_earea_pages)) ||
						($include_editable&&$v[23]=='TRUE') ||
						in_array($p_id,$forms)
					);
				if($ca_control)
				{
					$temp=array('name'=>$p_name,'url'=>$v[1],'typeid'=>$v[4],'section'=>$v[7],'menu'=>$v[11],
						'protected'=>(Validator::checkProtection($v)>1?'TRUE':'FALSE'),'id'=>$p_id,
						'udp'=>$v[27]);
					$pages[]=$temp;
				}
			}
		}
		return $pages;
	}

}


class ca_login extends ca_objects
{
	protected $ca_admin_username="admin";
	protected $ca_admin_pwd="80177534a0c99a7e3645b52f2027a48b";

	public function ca_usernameCheck($user)
	{
		return $this->ca_admin_username==$user;
	}

	public function process_fblogin($float)
	{
		global $db;
		include_once($this->ca->ca_prefix.'ezg_data/fbsdk/src/facebook.php');
		$fbKey = $this->f->ca_settings['fb_key'];
		$fbSecret = $this->f->ca_settings['fb_secret'];
		$fb = new FBCustom($fbKey,$fbSecret);
		if($fb->fbUser===null)
		{
			$this->ca->registrations->process_register($float);
			return;
		}

		$siteUser = User::mGetUser($fb->fbUser['email'],$db,'email',false,false);
		if(empty($siteUser))
		{
			$username=$base_username=preg_replace('/[^a-zA-z0-9_]/','',strtolower($fb->fbUser['first_name'].'_'.$fb->fbUser['last_name']));
			$it=1;
			$siteUserName=User::mGetUser($username,$db,'username',false,false);
			while(!empty($siteUserName))
			{
				$username=$base_username.'_'.$it++;
				$siteUserName=User::mGetUser($username,$db,'username',false,false);
			}
			$newUserData = array(
				'email'=>$fb->fbUser['email'],
				'first_name'=>$fb->fbUser['first_name'],
				'surname'=>$fb->fbUser['last_name'],
				'display_name'=>$fb->fbUser['name'],
				'password'=>User::generateRandPass(),
				'username'=>$username,
				'confirmed'=>1
			);
			$this->ca->users->register_new_user(null,false,$newUserData,$siteUser);

			//check if avatar is used in this site and re-use the FB avatar
			foreach($this->f->ca_users_fields_array as $fld)
				if($fld['itype']=='avatar')
				{
					$db->query_update('ca_users',array(
						'avatar'=>$this->ca->users->upload_avatar($siteUser['uid'],$fb->fbUser['picture']['data']['url'],true)
						),'uid='.$siteUser['uid']);
					break;
				}
		}
		$this->ca->user->setData($siteUser);
		$this->ca->isFBLogged=true;
		if(!empty($this->f->ca_settings['auto_login_redirect_loc']))
			$redirect_loc = $this->f->ca_settings['auto_login_redirect_loc'];
		else
		{
			$access_type='';
			$prot_page_info=$this->ca->sitemap->get_page_info($this->ca->thispage_id_inca);
			$prot_page_name=$prot_page_info[1];
			$lr=$this->f->ca_settings['login_redirect_option'];
			if($lr=='profile')
			{
				$lp=false;
				$action=CA::get_user_profile_action($lp);
				$redirect_loc=(strpos($prot_page_name,'../')===false?'documents/':'').'centraladmin.php?process='.$action;
			}
			elseif($lr=='page' || !User::mHasWriteAccess2($this->ca->user->getUname(),$this->ca->user->getAllData(),$prot_page_info,$access_type))
				$redirect_loc=$prot_page_name;
			else
				$redirect_loc=CA::defineAdminLink($prot_page_info,true);
			//Joe: because we use sitemap URLs (from root) in CA file
			//when page is in root, it's direct and you need the ../
			//but if page is in some folder the ../ is added, so no extra ../ is needed
			$redirect_loc = (strpos($prot_page_name,'../')===false?'../':'').$redirect_loc;
		}
		$this->process_auto_login('-1',$redirect_loc);

		return;
	}

	protected function ca_error($id,$delay,$user_account=array())
	{
		$uid=isset($user_account['uid'])?$user_account['uid']:0;
		if($delay)
			$this->set_delay($uid);

		$ccheck=(isset($_POST['cc'])&&$_POST['cc']=='1');
		$issues=array();

		if($id==0)
			$err_msg='<h1>Username & Password are not set for Online Administration.</h1><h2>To solve the problem, go to Project Settings -> Online Administration and set Username & Password.</h2>';
		else
		{
			$delay_cookie_id=md5('cookie_delay'.$uid);
			$del_cookie_val=isset($_COOKIE[$delay_cookie_id])?(int)$_COOKIE[$delay_cookie_id]:1;
			$attempts_left=3-$del_cookie_val>0?3-$del_cookie_val:0;
			if(!$this->f->ca_settings['usr_blocking'])
				 $err_msg=$this->ca->lang_l('incorrect credentials');
			else
			{
				if(!empty($user_account))
				{
					if($user_account['confirmed']=='0')
						$err_msg=$this->ca->lang_l('unconfirmed_msg');
					elseif($id==13)
						$err_msg=$this->ca->lang_l('account_expired_msg');
					elseif($user_account['status']!='1')
					{
						if($user_account['confirmed']=='1')
						  $err_msg=$user_account['status']=='3'?$this->ca->lang_l('temp_blocked_err_msg'):$this->ca->lang_l('blocked_err_msg');
						elseif($user_account['confirmed']=='2')
						  $err_msg=$this->ca->lang_l('require_approval');
					}
					else
						$err_msg=$attempts_left==0?
						  $this->ca->lang_l('temp_blocked_err_msg'):
						  str_replace('%%attempt%%',$attempts_left,$this->ca->lang_l('use correct username'));
					$this->log_incorrect_login($uid,$user_account['username']);
				}
				else
				{
					$err_msg=$attempts_left==0?
						  $this->ca->lang_l('temp_blocked_err_msg'):
						  str_replace('%%attempt%%',$attempts_left,$this->ca->lang_l('use correct username'));
					$this->log_incorrect_login();
				}
			}
		}

		$issues[]='error|'.$err_msg;

		if($ccheck)
		{
			$errors_output=implode('|',$issues);
			$useic=(!$this->f->uni&&$this->f->charset_lang_map[$this->ca->ca_lang]!='iso-8859-1'&&function_exists("iconv"));
			if($useic)
				$errors_output=iconv($this->f->charset_lang_map[$this->ca->ca_lang],"utf-8",$errors_output);
			if(count($issues)>0)
				print '0'.$errors_output;
			else
				print '1';
			exit;
		}

		$contents='';
		if(isset($_GET['ref_url'])&&$_GET['ref_url']!='')
			$contents=$this->build_login_form(Formatter::stripTags($_GET['ref_url'])); //event manager
		elseif(isset($_REQUEST["default_login"]))
			$contents=$this->build_login_form();

		$contents=str_replace('<!--page-->','<!--page-->'.'<div class="rvps1"><h5>'.$err_msg.'</h5></div>',$contents);
		print $contents;
		exit;
	}

	public function logout_user()
	{
		global $db;

		$logged=$this->ca->user->mGetLoggedUser($db,'');

		if($this->ca->ca_action_id=='logoutadmin')
			ca_log::write_log('logout',-1,$this->f->admin_nickname,'success');
		if($this->ca->ca_action_id=='logout'&&$this->ca->user->isAdmin())
			ca_log::write_log('logout',-1,$this->f->admin_nickname,'success');
		elseif($this->ca->user->userCookie())
			ca_log::write_log('logout',$logged['uid'],$this->ca->user->getUserCookie(),'success');

		$group_redirect_url=(!$this->ca->user->isDataEmpty()&&$this->ca->user->getData('group_logout_redirect')!==null)?$this->ca->user->getData('group_logout_redirect'):'';

		$this->ca->user->logout($db);

		$logout_redirect_url=$this->f->ca_settings['logout_redirect_url'];

		if(!empty($group_redirect_url))
			$redirect_page_name=$group_redirect_url;
		elseif(!empty($logout_redirect_url))
			$redirect_page_name=(strpos($logout_redirect_url,'http')===false?$this->f->site_url:'').$logout_redirect_url;
		elseif(isset($_GET['ref_url']))
			$redirect_page_name=Formatter::stripTags($_GET['ref_url']);
		elseif(isset($_GET['pageid'])&&intval($_GET['pageid'])>0)
		{
			$p_id=intval($_GET['pageid']);
			$prot_page_info=$this->ca->sitemap->get_page_info($p_id);
			while(empty($prot_page_info)&&$p_id>0)
				$prot_page_info=$this->ca->sitemap->get_page_info($p_id--);
			$prot_page_name=$prot_page_info[1];
			$redirect_page_name=(strpos($prot_page_name,'../')===false)?'../'.$prot_page_name:$prot_page_name;
		}
		else
		{
			$pos=strpos($this->f->home_page,'http://');
			$redirect_page_name=($pos!==false)?substr($this->f->home_page,$pos):'../'.$this->f->home_page;
		}
		Linker::redirect($redirect_page_name,false);
	}

	protected function log_incorrect_login($uid=0,$username='')
	{
		global $db;
		$ip=Detector::getIP();
		$this->clear_incorrect_logins();
		$db->query_insert('ca_users_incorrect_logins',array('ip'=>$ip,'user_id'=>$uid));
		if(!$this->f->ca_settings['usr_blocking'])
			return;

		$curr_attempts=$db->query_singlevalue('
			SELECT COUNT(*)
			FROM `'.$db->pre.'ca_users_incorrect_logins`
			WHERE '.($uid?' user_id='.$uid:' ip="'.$ip.'"'));

		if(($uid && $curr_attempts>2)||$curr_attempts>4) //it's a blocking time!
		{
			if($uid)
			{
				$this->block_user($uid); //block user for the $blockInterval minutes
				ca_log::write_log('login',$uid,$username,'blocked User');
			}
			else
				$this->block_incorrect_ip($ip);//do nothing
		}
	}

	protected function block_incorrect_ip($ip)
	{
		return false;
	}

	public function clear_incorrect_logins($id=null)
	{
		global $db;

		$where_user='';
		if($id!==null && is_numeric($id) && $id>0) //clean incorrect logins for currently logged user
			$where_user = 'user_id='.$id.' OR ';

		$blockInterval=5; //block interval for incorrect login
		$db->query('
			DELETE
			FROM `'.$db->pre.'ca_users_incorrect_logins`
			WHERE '.$where_user.'attempt_time < NOW() - INTERVAL '.$blockInterval.' MINUTE');
	}

	protected function set_delay($uid=0)
	{
		$delay=20;
		$delay_cookie_id=md5('cookie_delay'.$uid);
		$delay_c=isset($_COOKIE[$delay_cookie_id])?(int)$_COOKIE[$delay_cookie_id]:1;
		if($delay_c>0)
			$delay=$delay_c*5;
		$max_exec=intval(ini_get('max_execution_time'));
		$delay=($max_exec+2>=$delay)?$delay:$max_exec-2;
		sleep($delay);
		setcookie($delay_cookie_id,$delay_c+1,time()+600,'/'); //block in db is 5 min, cookie is 10 min
	}

	protected function set_admin_cookie()
	{
		if(!isset($_COOKIE['visit_from_admin']))  // counter needed to ignore hits from site admin
		{
			$ts=time();
			$expire_ts=mktime(23,59,59,date('n',$ts),date('j',$ts),2037);
			setcookie('visit_from_admin',md5(uniqid(mt_rand(),true)),$expire_ts,'/');
		}
	}

	public function process_auto_login($redirect_time,$redirect_location)
	{
		$this->ca->user->autoLogin($this->ca,$redirect_time,$redirect_location);
	}

	public function build_login_form($ref_url='',$default=false)
	{
		$contents=$login_page_scripts=$curr_lang='';
		$fromca=isset($_GET['indexflag']);
		$is_adminLink=isset($_GET['adminlink']);
		$lform_in_earea=$default_login=false;
		$lister_array=array(SHOP_PAGE,CATALOG_PAGE);
		$pageid_info=0;
		if($this->ca->thispage_id_inca>0)
		{
			$pageid_info=$this->ca->sitemap->get_page_info($this->ca->thispage_id_inca);
			$curr_lang=$this->f->inter_languages_a[array_search($pageid_info[16],$this->f->site_languages_a)];
		}
		if($curr_lang==''&&isset($_REQUEST['lang']))
			$curr_lang=Formatter::stripTags($_REQUEST['lang']);

		$this->ca->ca_update_language_set($curr_lang);
		if($curr_lang!='')
		{
			$this->ca->ca_l=$curr_lang;
			$this->ca->ca_l_amp='&amp;lang='.$curr_lang;
		}

		$r=isset($_REQUEST['r'])?'&amp;r=1':'';
		$return_url=isset($_REQUEST['return_url'])?'&amp;return_url='.Formatter::stripTags($_REQUEST['return_url']):'';
		$this->ca->ca_l_amp.=$r.$return_url;

		if(!$default)
		{
			foreach($this->f->ca_loginids as $v) //use first available login form if any
			{
				$login_page_info=$this->ca->sitemap->get_page_info($v);
				if(isset($login_page_info[22]) && $login_page_info[22]==$pageid_info[22])
				{
					$use_login_pageid=$v;
					break;
				}
			}
		}

		$prot_page_info=$pageid_info;
		$prot_page_name=$prot_page_info[1];
		$prot_page_inroot=strpos($prot_page_name,'../')===false;
		$footer='';

		if(isset($use_login_pageid))  // page with login form
		{
			$login_page_info=$this->ca->sitemap->get_page_info($use_login_pageid);

			if(in_array(intval($login_page_info[4]),array(CALENDAR_PAGE,BLOG_PAGE,PHOTOBLOG_PAGE,PODCAST_PAGE,GUESTBOOK_PAGE,OEP_PAGE,SURVEY_PAGE)))
			{
				$l_dir=(strpos($login_page_info[1],'../')===false)?'':'../'.Formatter::GFS($login_page_info[1],'../','/').'/';
				$login_page_name=$l_dir.$use_login_pageid.(Validator::checkProtection($login_page_info)>1?'.php':'.html');
			}
			elseif(in_array($login_page_info[4],array(SHOP_PAGE,CATALOG_PAGE,REQUEST_PAGE)))
			{
				$l_dir=(strpos($login_page_info[1],'../')===false)?'':'../'.Formatter::GFS($login_page_info[1],'../','/').'/';
				$login_page_name=$l_dir.($login_page_info[4]==REQUEST_PAGE?($use_login_pageid+1):$use_login_pageid).'.html';
			}
			else
				$login_page_name=$login_page_info[1];

			if(Mobile::detect($login_page_info[24]))
			{
				if(strpos($login_page_name,'/')===false)
					$login_page_name='i_'.$login_page_name;
				else
				{
					$t_name=substr($login_page_name,strrpos($login_page_name,'/')+1);
					$login_page_name=str_replace($t_name,'i_'.$t_name,$login_page_name);
				}
			}

			$login_page_inroot=strpos($login_page_name,'../')===false;
			if($login_page_inroot&&(!$prot_page_inroot||$this->ca->ca_prefix=='../'))
				$login_page_name='../'.$login_page_name;
			elseif(!$login_page_inroot && $prot_page_inroot && !$fromca)
				$login_page_name=str_replace('../','',$login_page_name);

			$contents=File::read($login_page_name);
			$contents=Formatter::clearMacros($contents,$login_page_info[4]);

			if($prot_page_inroot)
				$contents=str_replace('="../','="',$contents);
			if(!$prot_page_inroot&&$login_page_inroot)
				$contents=str_replace(array('href="documents/centraladmin.php?','src="','url("images','url(images','url("extimages','url(extimages'),array('href="../documents/centraladmin.php?','src="../','url("../images','url(../images','url("../extimages','url(../extimages'),$contents);
		}
		else  // default login
		{
			$default_login=true;
			if($this->f->ca_settings['pwchange_enable'])
				$footer.='
					<p id="login_changepass">
						<a class="rvts12" href="'.$this->ca->ca_abs_url.'?process=forgotpass'.$this->ca->ca_l_amp.'">'.$this->ca->lang_l('forgot password').'</a>
					</p>';
			
			$inside=$this->f->form_labels_pos=='inside';
			$theme=$this->f->form_class!=='';
			$label_class=$theme?'':' rvts8';

			$label=($inside?'':'
							<label for="%s" class="desc">
									 <span class="label_title'.$label_class.'">%s</span>
							</label>');
			
			if($this->f->ca_settings['fb_login']==1)
			{
				include_once($this->ca->ca_prefix.'ezg_data/fbsdk/src/facebook.php');
				$fbKey=$this->f->ca_settings['fb_key'];
				$fbSecret=$this->f->ca_settings['fb_secret'];
				$fb=new FBCustom($fbKey,$fbSecret);
				if($fb->fbUser===null &&!$this->ca->user->isCurrUserLogged())
					$footer.= '
						<p id="login_fb">
							<a href="'.FBCustom::$LOG_IN_OUT_URL.'">'.$this->ca->lang_l('fb login').'</a>
						</p>';
			}
			if($this->f->ca_settings['sr_enable'])
				$footer.='
					<p id="login_register">
						<a class="rvts12" href="'.$this->ca->ca_abs_url.'?process=register'.$this->ca->ca_l_amp.'">'.$this->ca->lang_l('not a member').' '.$this->ca->lang_l('register').'</a>
					</p>';

			$contents='<!--page--><!--defLogin-->
				<div class="emd" style="width:100% !important;max-width: 100% !important;">
				<style type="text/css">@import url("//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css");</style>
				<div id="div_login_def" class="form_table emLoginDlg '.$this->f->form_class.'">
					<div class="loginDef_preloader"></div>
					<a href="" class="emcloseBtn"><i class="fa fa-times fa-1x"></i></a>
					<div class="emLoginBody">
						<form name="login_def" id="login_def" method="post" action="'.$this->ca->ca_abs_url.'?pageid='.$this->ca->thispage_id_inca.$this->ca->ca_l_amp.($ref_url!=''?'&amp;ref_url='.urlencode($ref_url):'').'">
							<input type="hidden" name="default_login" value="1">
							<ul class="liveFormInner '.$this->f->form_class.' sr_defaultlogin'.($this->f->form_labels_pos=='left'?' labelsLeft':'').'">
								<li>
									<h1>'.$this->ca->lang_l('protected area').'</h1>
								</li>
								<li>
									<span class="loginDef_input_container">'
										.sprintf($label,'pv_username',$this->ca->lang_l('username')).'
										<div>
										<span id="icon_username">
										  <input id="pv_username" class="field text large'.($this->f->form_class==''?' input1':'').'" name="pv_username" type="text"'.($inside?' placeholder="'.$this->ca->lang_l('username').'"':'').' required>
										</span>
										<span class="rvts12 frmhint" id="login_def_pv_username"></span>
										<div>
									</span>
								</li>
								<li>
									<span class="loginDef_input_container">'
										.sprintf($label,'pv_password',$this->ca->lang_l('password')).'
										<div>
										<span id="icon_password">
										  <input id="pv_password" class="field text large'.($this->f->form_class==''?' input1':'').'" name="pv_password" type="password"'.($inside?' placeholder="'.$this->ca->lang_l('password').'"':'').' required>
										</span>
										<span class="rvts12 frmhint" id="login_def_pv_password"></span>
										</div>
									</span>
								</li>'.
								($this->f->ca_settings['cookie_login']?'
								<li style="margin-bottom: 15px;">
									 <label id="title7" class="desc"></label>
									 <div>
									 <p><input value="1" class="field checkbox" id="id_remember" name="pv_remember" type="checkbox">
									 <label for="pv_remember">'.$this->ca->lang_l('Remember me').'</label></p></div>
		 						</li>':'').
								'<li>
									<div class="loginDef_input_container" style="width:100%">
										<input class="loginBtn" type="submit" value="'.$this->ca->lang_l('login').'">
									</div>
									<span class="rvts12 frmhint" id="login_def_error"></span>
								</li>
								<li>'.
									$footer.'
								</li>
							</ul>
						</form>
					</div>
<script type="text/javascript">
$(document).ready(
	 function(){
		$(".emLoginDlg").center();
		$("#login_def").utils_frmvalidate(1,0,1,0,0,0,0);
		$(".emcloseBtn").click(function(){$(".emd").remove();return false;});
		$(".def_login_submit").click(function(){$("#login_def").submit();});
	 });
</script>
            </div>
            </div>
            <!--/defLogin--><!--/page-->';

				$this->ca->ca_css.=str_replace('h2,','h1,',$this->f->form_css);
		}

		if((!isset($_GET['pageid'])||$fromca||$ref_url!='')||in_array($pageid_info[4],$lister_array))
		{
			$rep_what=Formatter::GFSAbi($contents,'method="post" action="','">'); // login form action fixation
			$url_st=$this->ca->ca_abs_url."?pageid=";
			if($fromca)
				$rep_with=$url_st.$this->ca->thispage_id_inca."&amp;indexflag=index".$this->ca->ca_l_amp;
			elseif(isset($_GET['pageid'])&&$ref_url!='')
				$rep_with=$url_st.$this->ca->thispage_id_inca.$this->ca->ca_l_amp.'&amp;ref_url='.urlencode($ref_url);
			elseif(isset($_GET['pageid'])&&in_array($pageid_info[4],$lister_array))
				$rep_with=$url_st.intval($_GET['pageid']).$this->ca->ca_l_amp;
			elseif($prot_page_name=='')
				$rep_with=$this->ca->ca_abs_url.'?process=index';
			else
				$rep_with=$prot_page_name.(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']!==''?'?'.$_SERVER['QUERY_STRING']:'');   //QUERY_STRING part : to login directly to page entry, has to be checked
			$contents=str_replace($rep_what,'method="post" action="'.$rep_with.'">',$contents);

			if(in_array(intval($prot_page_info[4]),array(CALENDAR_PAGE,BLOG_PAGE,PHOTOBLOG_PAGE,PODCAST_PAGE,GUESTBOOK_PAGE,OEP_PAGE,SURVEY_PAGE)))
			{
				$this->f->dir='';
				if(!$prot_page_inroot)
					$this->f->dir='../'.Formatter::GFS($prot_page_info[1],'../','/').'/';
				elseif(Validator::checkProtection($prot_page_info)==1)
					$this->f->dir='../';

				$this->f->dir=str_replace('//','/',$this->f->dir);
				$prot_page_name_fixed=$this->f->dir.$this->ca->thispage_id_inca.(Validator::checkProtection($prot_page_info)>1?'.php':'.html');
			}
			elseif(in_array($prot_page_info[4],$lister_array))
			{
				$this->f->dir='../'.Formatter::GFS($prot_page_info[1],'../','/').'/';
				$prot_page_name_fixed=$this->f->dir.$this->ca->thispage_id_inca.'.html';
			}
			elseif(intval($prot_page_info[4])==NEWSLETTER_PAGE)
			{
				if(!$prot_page_inroot)
					$prot_page_name_fixed=$prot_page_name;
				elseif(Validator::checkProtection($prot_page_info)==1)
					$prot_page_name_fixed='../'.$prot_page_name;
				else
					$prot_page_name_fixed=$prot_page_name;
				$prot_page_name_fixed=str_replace('//','/',$prot_page_name_fixed);
			}
			else
				$prot_page_name_fixed=$prot_page_name;

			if($prot_page_name!==null)
			{
				if(Mobile::detect($prot_page_info[24]))
				{
					if(strpos($prot_page_name_fixed,'/')===false)
						$prot_page_name_fixed='i_'.$prot_page_name_fixed;
					else
					{
						$t_name=substr($prot_page_name_fixed,strrpos($prot_page_name_fixed,'/')+1);
						$prot_page_name_fixed=str_replace($t_name,'i_'.$t_name,$prot_page_name_fixed);
					}
				}

				if(strpos($prot_page_name_fixed,'../')===false&&isset($_GET['indexflag']))
					$prot_page_name_fixed='../'.$prot_page_name_fixed;

				if(file_exists($prot_page_name_fixed))
					$protpage_content=File::read($prot_page_name_fixed);
				else
					$protpage_content='<html><head></head><body><h1>missing page</h1></body></html>';

				if(strpos($contents,'<!--page-->')!==false)
					$replace_with=Formatter::GFS($contents,'<!--page-->','<!--/page-->');
				else
					$replace_with=Formatter::GFS($contents,Formatter::GFSAbi($contents,'<body','>'),'</body>');

				$temp=Formatter::GFS($protpage_content,'<!--login-->','<!--/login-->');
				$float_login=strpos($temp,'class="frm_login"')!==false;
				$login_page_scripts=$float_login?'':Formatter::GFS($contents,'<!--scripts-->','<!--endscripts-->');

				if(strpos($protpage_content,'<!--page-->')!==false)
					$for_replace=Formatter::GFS($protpage_content,'<!--page-->','<!--/page-->');
				else
					$for_replace=Formatter::GFS($protpage_content,Formatter::GFSAbi($protpage_content,'<body','>'),'</body>');

				$contents=str_replace($for_replace,$replace_with,$protpage_content);

				if($this->f->ca_settings['protect_footer'])
				{
					$footer=Formatter::GFS($contents,'<!--footer-->','<!--/footer-->');
					$contents=str_replace($footer,'',$contents);
				}
			}

			if(!isset($use_login_pageid)||$use_login_pageid!=$this->ca->thispage_id_inca)
			{
				$temp_for_js=$login_page_scripts;
				$login_page_scripts_new='';
				$temp_for_js=str_replace(Formatter::GFSAbi($temp_for_js,'<!--menu_java-->','<!--/menu_java-->'),'',$temp_for_js);
				while(strpos($temp_for_js,'<script')!==false)
				{
					$script_t=Formatter::GFSAbi($temp_for_js,'<script','</script>');
					if(strpos($contents,$script_t)===false)
						$login_page_scripts_new.=$script_t;
					$temp_for_js=str_replace($script_t,'',$temp_for_js);
				}
				while(strpos($temp_for_js,'<style')!==false)
				{
					$style_t=Formatter::GFSAbi($temp_for_js,'<style','</style>');
					if(strpos($contents,$style_t)===false)
						$login_page_scripts_new.=$style_t;
					$temp_for_js=str_replace($style_t,'',$temp_for_js);
				}
				while(strpos($temp_for_js,'<link rel="stylesheet"')!==false)
				{
					$style_t=Formatter::GFSAbi($temp_for_js,'<link rel="stylesheet"','>');
					if(strpos($contents,$style_t)===false)
						$login_page_scripts_new.=$style_t;
					$temp_for_js=str_replace($style_t,'',$temp_for_js);
				}
				if(!empty($login_page_scripts_new))
					$login_page_scripts=$login_page_scripts_new;
				$contents=str_replace('<!--endscripts-->',$login_page_scripts.'<!--endscripts-->',$contents);
			}
			$contents=str_replace(Formatter::GFS($contents,'<!--counter-->','<!--/counter-->'),'',$contents);
			$contents=preg_replace("'<\?php.*?\?>'si",'',$contents);
			if(strpos($prot_page_info[1],'../')===false)
			{
				$dn=dirname($_SERVER['PHP_SELF']);
				$url=$this->f->http_prefix.Linker::getHost().str_replace('//','/',str_replace('documents','',$dn=='\\'?'':$dn).'/');
				$contents=str_replace('</title>','</title>'.F_LF.'<base href="'.$url.'">',$contents);
			}

			$earea=Formatter::GFSAbi($contents,'<!--%areap(','<!--areaend-->');
			$cl='documents/centraladmin.php?pageid=';
			if(((strpos($earea,'action="../'.$cl)!==false)||(strpos($earea,'action="'.$cl)!==false))&&!isset($use_login_pageid))
			{
				$lform_in_earea=true;
				if(strpos($earea,'action="../'.$cl)!==false)
				{
					$act=Formatter::GFSAbi($earea,'action="../'.$cl,'"');
					$earea_new=str_replace($act,'action="../'.$cl.$this->ca->thispage_id_inca.'"',$earea);
				}
				else
				{
					$act=Formatter::GFSAbi($earea,'action="'.$cl,'"');
					$earea_new=str_replace($act,'action="'.$cl.$this->ca->thispage_id_inca.'"',$earea);
				}
				$contents=str_replace($earea,$earea_new,$contents);
				$contents=str_replace(Formatter::GFS($contents,'<!--page-->','<!--/page-->'),F_BR.'<div class="rvps1">'.$this->ca->lang_l('login form msg').'</div>',$contents);
			}

			if(isset($float_login) && $float_login)
			{
				$contents=str_replace(Formatter::GFS($contents,'<!--page-->','<!--/page-->'),F_BR.'<div class="rvps1">'.$this->ca->lang_l('login form msg').'</div>',$contents);
				while(strpos($contents,'<!--%area')!==false)
					$contents=str_replace(Formatter::GFSAbi($contents,'<!--%area','<!--areaend-->'),'',$contents);

				if($ref_url!='')
				{
					$ext_form_area=Formatter::GFS($contents,'<!--login-->','<!--/login-->');
					$ext_form_area_new=str_replace('action="../'.$cl,'action="../documents/centraladmin.php?ref_url='.urlencode($ref_url).'&amp;pageid=',$ext_form_area);
					$ext_form_area_new=str_replace('action="'.$cl,'action="documents/centraladmin.php?ref_url='.urlencode($ref_url).'&amp;pageid=',$ext_form_area_new);
					$contents=str_replace($ext_form_area,$ext_form_area_new,$contents);
				}
				if(isset($_REQUEST['r']))
				{
					$ext_form_area=Formatter::GFS($contents,'<!--login-->','<!--/login-->');
					$act=Formatter::GFS($ext_form_area,'action="','"');
					$ext_form_area_new=str_replace('action="'.$act,'action="'.$act.'&amp;r=1',$ext_form_area);
					$contents=str_replace($ext_form_area,$ext_form_area_new,$contents);
				}
				if(strpos($contents,'float_login({});')!==false)
					$contents=str_replace('float_login({});','float_login({op:true});',$contents);
				elseif(strpos($contents,'float_login({')!==false)
					$contents=str_replace('float_login({','float_login({op:true,',$contents); //float login with params found

				if(strpos($_SERVER['QUERY_STRING'],'entry_id')!==false)
				{
					$rep_what=Formatter::GFSAbi($contents,'<form action="','"'); // login form action fixation
					$rep_with=$prot_page_name.(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']!==''?'?'.$_SERVER['QUERY_STRING']:'');   //QUERY_STRING part : to login directly to page entry, has to be checked
					$contents=str_replace($rep_what,'<form action="'.$rep_with.'"',$contents);
				}
			}
		}

		$contents=str_replace(array('GMload();','GUnload();'),array('',''),$contents);

		if(!$lform_in_earea && !isset($float_login))
		{
			if($default_login)
			{
				$lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
				$contents=$this->ca->GT($contents,true,$lang_r['forgotten password'],true);
				$contents=Builder::includeScript(str_replace('%ID%','forgotpass',$this->f->frmvalidation),$contents);
				if(strpos($contents,'<base')!==false)
					$contents=str_replace('"../documents/','"documents/',$contents);
				else
					$contents=str_replace(array('action="documents/','href="documents/'),array('action="../documents/','href="../documents/'),$contents);
			}
		}

		if($this->ca->ca_css!=='')
			 $contents=Builder::includeCss($contents,$this->ca->ca_css);

		if($is_adminLink&&isset($float_login)&&!$float_login&&$default_login&&$prot_page_info[6]==="FALSE")
			$contents=Formatter::GFS($contents,'<!--defLogin-->','<!--/defLogin-->');

		Linker::relToAbs($contents,strpos($contents,'../jquery_utils')!==false?'../':'');
		return str_replace('</title>','</title>'.F_LF.'<meta name="robots" content="noindex,nofollow">',$contents);
	}

	public function block_timeout_reset($uid)
	{
		global $db;

		$curr_attempts=$db->query_singlevalue('SELECT COUNT(*) FROM `'
			.$db->pre.'ca_users_incorrect_logins` WHERE user_id='.$uid);
		if($curr_attempts==0)
		{//unblock user if timeout expired
			$this->block_user($uid,true);
			return true; //user unblocked
		}
		return false; //user still blocked
	}

	public function block_user($id,$unblock=false) // block/unblock user
	{
		global $db;

		$status=$unblock?1:3;
		$db->query_update('ca_users',array('status'=>$status),' uid ='.$id);
		if($status==1)
		{
			$delay_cookie_id=md5('cookie_delay'.$id);
			setcookie($delay_cookie_id,0,time(),'/');
			$this->clear_incorrect_logins($id);
		}
	}

	public function assign_protected_pageIds(&$controlled_pages,&$protected_pages,&$special_ids)
	{
		$controlled_pages=$this->ca->sitemap->ca_get_prot_pages_list();

		foreach($controlled_pages as $v)
		{
			if($v['protected']=='TRUE'||in_array($v['typeid'],$this->f->sp_pages_ids))
				$protected_pages[$v['id']]=$v['menu'];
			if(in_array($v['typeid'],$this->f->sp_pages_ids))
				$special_ids[]=$v['id'];
		}
	}

	public function handle_logins()
	{
		global $db;

		$access_flag=false;
		$ccheck=(isset($_POST['cc'])&&$_POST['cc']=='1');
		if(empty($_POST) && empty($this->ca->thispage_id_inca) && !isset($_GET['pageid']))
		{
			Linker::redirect("centraladmin.php?process=index",false);
			exit;
		}

		if(!Detector::check_cross_domain(true)) exit;

		$ca_miniform=(isset($_GET['pageid'])&&!isset($_GET['indexflag'])&&!isset($_GET['ref_url'])&&!empty($_POST));

		//remember user (use cookie login time)
		$pv_remember=isset($_POST['pv_remember']);
		//are we trying to login?
		$wewantlogin=(isset($_POST['pv_username'])&&isset($_POST['pv_password']));
		if($wewantlogin)
		{
			$pv_username=trim(Formatter::stripTags($_POST['pv_username']));
			$pv_password=trim($_POST['pv_password']);
			$pass_filled=md5($pv_password);

			if($pv_username=='' || $pv_password=='')
				$this->ca_error('1',false);
			elseif(strtolower($this->ca_admin_username)=='admin' && ($this->ca_admin_pwd==md5('admin')||$this->ca_admin_pwd==md5('Admin')||$this->ca_admin_pwd==md5('ADMIN')))
				$this->ca_error(0,false);

			$isitadmin=($pv_username==$this->ca_admin_username);
			$this->clear_incorrect_logins();
		}
		else
			$isitadmin=$this->ca->user->isAdmin();

		if(isset($_GET['pageid']) || $ca_miniform) // when login page or miniform is directly accessed
		{
			if(intval($_GET['pageid'])==0 && $this->ca->thispage_id_inca=="0")	//login page accessed directly, not from admin link
			{
				$controlled_pages=$protected_pages=$special_ids=array();
				$this->assign_protected_pageIds($controlled_pages,$protected_pages,$special_ids);
				$protected_pages=array_keys($protected_pages);

				$redirect_to_page='';
				$user_account=User::mGetUser($pv_username,$db,'username',true,false);

				if($pv_username==$this->ca_admin_username && $this->ca_admin_pwd==$pass_filled)
					$redirect_to_page=(isset($protected_pages[0]))?$protected_pages[0]:'admin';
				elseif(empty($user_account))
					$this->ca_error('2',true,$user_account);
				else
				{
					if($user_account['password']==crypt($pv_password,$user_account['password']))
					{
						if(!ca_functions::is_nulldate($user_account['expired_date']) && Date::tzoneSql($user_account['expired_date'])<time())
							$this->ca_error('13',false,$user_account);
						elseif($user_account['confirmed']!='1')
							$this->ca_error('11',false,$user_account);
						if($user_account['status']=='3')
							$this->block_timeout_reset($user_account['uid']);
						if($user_account['access'][0]['section']!='ALL')
						{
							foreach($user_account['access'] as $v)
							{
								if($v['page_id']!=0)
								{
									if(in_array($v['page_id'],$protected_pages))
									{
										if((($v['access_type']==1||$v['access_type']==9)&&in_array($v['page_id'],$special_ids))||($v['access_type']==0&&!(in_array($v['page_id'],$special_ids))))
										{
											$redirect_to_page=$v['page_id'];
											break;
										}
									}
								}
								elseif(isset($protected_pages[0]))
								{
									foreach($controlled_pages as $page)
										if(($page['protected']=='TRUE'||in_array($page['typeid'],$this->f->sp_pages_ids))&&($page['section']==$v['section']))
										{
											$redirect_to_page=$page['id'];
											break;
										}
								}
							}
						}
						elseif(isset($protected_pages[0]))
							$redirect_to_page=$protected_pages[0];
					}
					else
						$this->ca_error('3',true,$user_account);
				}

				if(empty($redirect_to_page))
				{
					$this->block_user($user_account['uid'],true);
					print '<h1>The system does not know where to redirect you.</h1>';
					exit;
				}
				else
				{
					$prot_page_info=$this->ca->sitemap->get_page_info($redirect_to_page);
					$this->ca->thispage_id_inca=$prot_page_info[10];
				}
			}
		}

		$logged_onchild=false;
		$prot_page_name='';
		$prot_page_info=array();
		if(!empty($this->ca->thispage_id_inca))
		{
			$prot_page_info=$this->ca->sitemap->get_page_info($this->ca->thispage_id_inca);
			$prot_page_name=$prot_page_info[1];
			$logged_onchild=$prot_page_info[10]!=$this->ca->thispage_id_inca;  //logged on child page

			$ca_rss_call_in_prot_page=isset($_GET['action']) && $_GET['action']=='rss'; // public rss when page is protected
			if($ca_rss_call_in_prot_page && in_array(intval($prot_page_info[4]),array(CALENDAR_PAGE,BLOG_PAGE,PHOTOBLOG_PAGE,PODCAST_PAGE,GUESTBOOK_PAGE,SHOP_PAGE,CATALOG_PAGE))) // public rss for protected page
				$rss_public_on=$db->query_singlevalue('SELECT sval FROM '.$db->pre.$this->ca->thispage_id_inca.'_settings WHERE skey="public_rss"');
		}

		//start of actual pwd protection check
		if(isset($rss_public_on)&&$rss_public_on=='1')
			$access_flag=true;
		elseif(isset($_REQUEST['action'])&&$_REQUEST['action']=='uploadbatch' && $prot_page_info[4]==PHOTOBLOG_PAGE) //access checked in photoblog
			$access_flag=true;
		elseif($this->ca->user->isEZGAdminNotLogged())
		{
			//not yet logged
			if($this->ca->user->userCookie())
				$user_account=User::mGetUser($this->ca->user->getUserCookie(),$db,'username',true,false);
			if(!$this->ca->user->userCookie()||User::mHasReadAccess($user_account,$prot_page_info)==false)
			{
				if(!isset($pv_username)&&!isset($pv_password))
				{
					$ref_url=(isset($_GET['ref_url']))?Formatter::stripTags($_GET['ref_url']):'';
					$contents=$this->build_login_form($ref_url);
					print $contents;
					exit;
				}
				elseif(!isset($pv_username)||!isset($pv_password)||$pv_username==''||$pv_password=='')
					$this->ca_error('6',false);
				elseif($isitadmin) //is it admin?
				{
					if($pass_filled==$this->ca_admin_pwd)
					{
						$this->ca->user->login(User::FLAG_ADMIN,null,$this->ca_admin_username);
						ca_log::write_log('login',-1,$this->f->admin_nickname,'success');
						$this->set_admin_cookie(); // for counter - to ignore hits from site admin
						$access_flag=true;
					}
					else
						$this->ca_error('7',true);  //wrong username or password
				}
				else //user
				{
					$user_account=User::mGetUser($pv_username,$db,'username',false,false);
					if(empty($user_account))
						$user_account=User::mGetUser($pv_username,$db,'email',false,false); //username not found, check e-mail
					if(empty($user_account))
						$this->ca_error('8',true,$user_account);  //wrong >username< or password
					else
					{
						$newUsr = new User();
						$newUsr->setData($user_account);
						$expData=$newUsr->getData('expired_date');
						if($newUsr->getData('confirmed')!='1')
							$this->ca_error('11',false,$newUsr->getAllData());
						elseif(!ca_functions::is_nulldate($expData) && Date::tzoneSql($expData)<time())
							$this->ca_error('13',false,$newUsr->getAllData());
						elseif($newUsr->getData('status')!='1')
						{
							if($newUsr->getData('status')=='3')
							{ //temp block
								$block_reset=$this->block_timeout_reset($newUsr->getId());
								if(!$block_reset)
									$this->ca_error('12',true,$newUsr->getAllData());
							}
							else
								$this->ca_error('12',true,$newUsr->getAllData()); //perm block
						}
						else
						{
							$log_check=$newUsr->getData('password')==crypt($pv_password,$newUsr->getData('password'));
							if($log_check)
							{
								$this->ca->user->login(User::FLAG_VISITOR,$newUsr->getAllData(),'',$db);
								$this->ca->user->setUserCookie();

								ca_log::write_log('login',$this->ca->user->getId(),$pv_username,'success');
								if($this->f->ca_settings['cookie_login']&&$pv_remember)
									Cookie::setLongtimeLogin($this->ca->user->getAllData(),time()+CA_LOGIN_COOKIE_EXPIRE*24*60*60);
								$access_flag=true;
							}
							else
								$this->ca_error('8',true,$newUsr->getAllData());  //wrong username or >password<

							$this->block_user($this->ca->user->getId(),true);
							if($access_flag && isset($_REQUEST['pv_username'])&&$ccheck)
							{
								print '1';
								exit;
							}

							if(empty($prot_page_info) || !User::mHasReadAccess($this->ca->user->getAllData(),$prot_page_info)) //redirect to profile when user has no access to current page
							{
								$lp=false;
								$action=CA::get_user_profile_action($lp);
								if(isset($user_account['group_login_redirect']) && $user_account['group_login_redirect']!='')
									$load_page=$user_account['group_login_redirect'];
								else
									$load_page=$this->ca->ca_abs_url.'?process='.$action;
								Linker::redirect($load_page,false);
								exit;
							}
						}
					}
				}
			}
			else
				$access_flag=true;
		}
		else
			$access_flag=true;  //end of actual pwd protection check

		if(isset($_REQUEST['pv_username'])&&$ccheck)
		{
			print '1';
			exit;
		}

		$this->ca->user->mGetLoggedUser($db,'');

		if(isset($_GET['pageid']) && $access_flag)
		{
			$load_page=$prot_page_name;
			$index_flag=isset($_GET['indexflag']);
			$access_type='';

			if($index_flag||Validator::checkProtection($prot_page_info)!==false)
			{
				$writeaccess_flag=$isitadmin||User::mHasWriteAccess2($this->ca->user->getUname(),$this->ca->user->getAllData(),$prot_page_info,$access_type);
				$protection_type=Validator::checkProtection($prot_page_info);
				//3 partially protected, 2 fully protected 1 only admin part protected
				$lr=$this->f->ca_settings['login_redirect_option'];
				// page -> log to page area, admin --> log to admin section of page, profile --> log user to his profile page
				//new code
				if(isset($_REQUEST['return_url'])&&$_REQUEST['return_url']!=''){
					$return_url=base64_decode($_REQUEST['return_url']);
					Linker::redirect($return_url,false);
					exit;
				}
				//special handling for shop partially protected
				if($protection_type==3 && (isset($_REQUEST['r']) || $logged_onchild))
					$load_page=$prot_page_name.'?action=basket';
				elseif($logged_onchild && $prot_page_info[4]==SHOP_PAGE && !$writeaccess_flag && ($this->ca->thispage_id_inca-$prot_page_info[10])==4) //login on basket page
					$load_page=$prot_page_name.'?action=basket';
				//always go to profile
				elseif($lr=='profile'||!$writeaccess_flag)
				{
					if($lr=='profile'||$lr=='admin')
					{
						$lp=false;
						$action=CA::get_user_profile_action($lp);
						$load_page=(strpos($prot_page_name,'../')===false?'documents/':'').'centraladmin.php?process='.$action;
					}
					else
						$load_page=$prot_page_name;
				}
				elseif($lr=='page'&&$wewantlogin) //we don't want to go admin directly, just login
					$load_page=$prot_page_name;
				else
					$load_page=CA::defineAdminLink($prot_page_info,true);
			}
			else
			{
				if($prot_page_info[4]==SHOP_PAGE && $prot_page_info[25]=='PP' && isset($_REQUEST['r']))
					$load_page.='?action=checkout';
			}

			if(isset($redirect_to_page)&&$redirect_to_page=='admin')
				$load_page='../documents/centraladmin.php?process=index';
			if(isset($_GET['ref_url']))
				$load_page=Formatter::stripTags($_GET['ref_url']); //event manager
			elseif(strpos($prot_page_name,'../')===false)
				$load_page='../'.$load_page;

			if(!$this->ca->user->isDataEmpty() && $this->ca->user->getData('group_login_redirect')!==null &&
					 $this->ca->user->getData('group_login_redirect')!='')
				$load_page=$this->ca->user->getData('group_login_redirect');
			Linker::redirect($load_page,false); //redirect after login
			exit;
		}
	}
}

class ca_registrations extends ca_objects
{
	private $user;

	public function __construct($ca_object,$user)
	{
		parent::__construct($ca_object);
		$this->user=$user;
	}

	private function resolveRegLabel($lang_r,$id)
	{
		$label=$this->ca->lang_l($id);
		if($label==$id && isset($lang_r[$id]))
			$label=$lang_r[$id];
		return $label;
	}

	public function build_register_form($float)
	{
		$lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
		$lang_r['first_name']=$lang_r['name'];
		$sr_termsofuse_urls=$this->f->ca_settings['sr_terms_url'];
		$sr_notes=$this->f->ca_settings['sr_notes'];
		$rel_path=isset($_GET['root'])&&$_GET['root']=='1'?'':'../';

		$norm_reg=($this->ca->ca_action_id=='register');
		$vert=isset($_REQUEST['vert'])&&($_REQUEST['vert']==1);
		$vert=$norm_reg||$vert;
		$theme=$this->f->form_class!=='';
		$inside=$this->f->form_labels_pos=='inside';
		$desc_class=$theme?'desc':'desc2';
		$label_class=$theme?'label_title':'rvts8';
		$input_class=$theme?'text large':'signguest_input input1';

		$isize=$norm_reg?'style="width:98%" ':'';
		$span8=$norm_reg?'class="label_title"':'class="field_label"';
		$input1=$norm_reg?'class="'.$input_class.'"':'class="field"';
		$input='<input '.$input1.' type="%s" name="%s" value="%s" %s%s>';
		$star='<span class="req req_on" id="req_1"> * </span>';
		if($norm_reg)
			$trtd2='<li>';
		else
			$trtd2=($vert)?'<tr><td>':'<tr><td></td><td>';
		$trtd2end=$norm_reg?'</li>':'</td></tr>';

		if($sr_termsofuse_urls!='')
		{
			if(strpos($sr_termsofuse_urls,'http')===false)
				$sr_termsofuse_urls=$this->ca_site_url.str_replace('../','',$sr_termsofuse_urls);
		}
		$output='<br>
			 <form id="selfreg" name="selfreg" action="'.$this->ca->ca_abs_url."?process=".$this->ca->ca_action_id.$this->ca->ca_l_amp.(isset($_GET['charset'])?'&amp;charset='.Formatter::sth(Formatter::stripTags($_GET['charset'])):'').'" method="post">
				<div'.($norm_reg?' style="margin: 20px auto;text-align:left;"':'').' class="'.($norm_reg?"ca_form sr_register":"sr_register2").'">';

		$output.=$norm_reg?
				'<ul class="liveFormInner '.$this->f->form_class.'">':
				'<table class="form_table" style="width:100%">';

		if($norm_reg)
			$output.='<li class="fheader"><h1>'.$lang_r['registration'].'</h1><br></li>';

		foreach($this->f->ca_users_fields_array as $k=> $v)
		{
			$label=$this->resolveRegLabel($lang_r,$k);
			$hidden=isset($v['hidinreg'])&&$v['hidinreg']=='1';
			$req=(!(isset($v['req'])&&$v['req']=='0'))?$star:'';

			if($norm_reg)
			{
				$line='<li class="dragable f_edit">'
							.($inside?'':'
							<label for="'.$k.'" class="'.$desc_class.'">
									 <span class="'.$label_class.'">%s</span>%s
							</label>
							').'<div>';
				$endline='
							</div>
							<span class="rvts12 frmhint" id="%s"></span>
						</li>';
			}
			else
			{
				$line='<tr><td class="col1">
						 <span '.$span8.'>%s%s</span>'.($vert?F_BR:'</td><td class="col2">');
				$endline='<span class="rvts12 frmhint" id="%s"></span></td></tr>';
			}

			if($v['itype']=='')
			{
				if($k=='username' || $k=='email')
					$output.=sprintf($line,$label,$star).
								sprintf($input,'text',$k,'',$isize,($inside?' placeholder="'.$label.'"':'')).
								sprintf($endline,'selfreg_'.$k);
				elseif($k=='password')
				{
					$pass_str_labels=Password::checkStrenght('',$this->ca->thispage_id_inca,1); //this will get the array with the labels only
					$output.=sprintf($line,$label,$star).'
						 <input id="passreginput" class="'.($norm_reg?$input_class:'field').' passreginput" type="password" rel="" name="password" value="" '.$isize.' '.($inside?' placeholder="'.$label.'"':'').'>'
						 .Password::showMeter($pass_str_labels,'right',$float?'4':'6')
						 .sprintf($endline,'selfreg_password');

					$label=$this->resolveRegLabel($lang_r,'repeat password');
					$output.=sprintf($line,$label,$star).
							sprintf($input,'password','repeatedpassword','',$isize,($inside?' placeholder="'.$label.'"':'')).
							sprintf($endline,'selfreg_repeatedpassword');
				}
			}
			elseif($v['itype']=="userinput")
				$output.=$hidden?'':sprintf($line,$label,$req).
										sprintf($input,'text',$k,'',$isize,($inside?' placeholder="'.$label.'"':'')).
										sprintf($endline,'selfreg_'.$k);
			elseif($v['itype']=="checkbox")
			{
				if(!$hidden)
				{
					if($norm_reg)
						$output.='
							 <li li class="dragable">
								<div>
									 <input type="checkbox" checked="checked" style="margin:6px 4px 6px 0;" value="1" name="'.$k.'">
									 <span class="label_title rvts8">'.$label.$req.'</span>
									<br>
								';
					else
						$output.='
							 <tr><td class="col1">
								<span '.$span8.'>'.$label.$req.'</span>
							</td>
							<td class="col2"><input type="checkbox" name="'.$k.'" value="1">';

					$output.=sprintf($endline,'selfreg_'.$k);
				}
			}
			elseif($v['itype']=="listbox")
			{
				if(!$hidden)
				{
					$data=explode(';',$v['values']);
					$output.=sprintf($line,$label,$req).Builder::buildSelect($k,$data,'',$isize,'value','',' class="'.($norm_reg?'input1':'field').'"').'</span>'.
							sprintf($endline,'selfreg_'.$k);
				}
			}
			elseif($v['itype']=='memo')
			{
				if(!$hidden)
				{
					$output.=sprintf($line,$label,$req).
								'<textarea name="'.$k.'"'.$input1.'></textarea>'.
								sprintf($endline,'selfreg_'.$k);
				}
			}
		}

		if($this->f->ca_settings['sr_disable_captcha']!='1'&&($norm_reg||(!$this->f->reCaptcha&&!$this->f->slidingCaptcha)))
		{
			$dname=$this->resolveRegLabel($lang_r,'code');
			$output.=sprintf($line,$dname,'').
					sprintf($input,'text','captchacode','','style="width:40px"','');
			if($norm_reg)
				$output.='<span class="captcha"></span>';
			else
				$output.='<span class="captcha"><img src="'.$rel_path.'ezg_data/captcha.php?'.time().'" style="vertical-align:text-bottom"></span>';
			$output.=sprintf($endline,'selfreg_code');
		}

		if(!empty($sr_termsofuse_urls))
		{
			$output.=$trtd2;
			$sr_agree_msg_fixed=$this->resolveRegLabel($lang_r,'I agree with terms');
			if($sr_termsofuse_urls!='')
			{
				$pattern=Formatter::GFS($sr_agree_msg_fixed,'%%','%%');
				$sr_agree_msg_fixed=str_replace('%%'.$pattern.'%%','<a class="rvts12" href="'.$sr_termsofuse_urls.'">'.$pattern.'</a>',$sr_agree_msg_fixed);
			}
			else
				$sr_agree_msg_fixed=str_replace('%%','',$sr_agree_msg_fixed);
			$output.='<input type="checkbox" name="agree" value="agree"> <span '.$span8.'> *';
			$output.=$sr_agree_msg_fixed.'</span>'.$trtd2end;
		}
		$output.=$trtd2.'<span '.$span8.'> </span><span class="rvts12 frmhint" id="selfreg_agree"></span>'.$trtd2end;
		if(isset($sr_notes)&&!empty($sr_notes))
			$output.=$trtd2.'<span '.(strpos($span8, 'field_label"')!==false?str_replace('field_label"','field_label sr_notes"',$span8):$span8).'>'.$sr_notes.'</span>'.$trtd2end;

		if($this->f->ca_settings['sr_cals_block'])
		{
			$calendar_categories=$this->ca->get_calendar_categories('',$this->ca->ca_lang);
			if(!empty($calendar_categories)) //event manager
			{
				$output.=$trtd2.'<span '.$span8.'><b>'.$lang_r['want to receive notification'].F_BR.' </b></span>'.$trtd2end;
				foreach($calendar_categories as $k=> $v)
					$output.=$trtd2.'<input type="checkbox" name="news_for[]" value="'.$v['pageid'].'%'.$v['catid'].'"> <span '.$span8.'>'.$v['pagename'].' - '.$v['catname'].'</span>'.$trtd2end;

				$output.=$trtd2.'<span '.$span8.'> </span>'.$trtd2end;
			}
		}

		$output.=$trtd2.'<span '.$span8.'>(*) '.$lang_r['required fields'].'</span>'.$trtd2end;
		$output.=$trtd2.'<input type="submit" value="'.$lang_r['submit_btn'].'">
			 <span id="selfreg_error" class="rvts12 frmhint"></span>
			 <input type="hidden" name="save" value="save">'.$trtd2end;

		$output.=($norm_reg?'</ul>':'</table>').'</div></form>';

		if($norm_reg)
		{
		  $this->ca->ca_css.=str_replace('h2,','h1,',$this->f->form_css);
		  if($this->f->ca_settings['customcode']!='')
				$output.=$this->f->ca_settings['customcode'];
		}

		return $output;
	}

	protected function build_forgotpass_form()
	{
		$theme=$this->f->form_class!=='';
		$inside=$this->f->form_labels_pos=='inside';
		$label_class=$theme?'':' rvts8';

		$label=($inside?'
							<div>':'
							<label for="%s" class="desc">
									 <span class="label_title'.$label_class.'">%s</span>
							</label>
							<div>');

		$lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
		$lang_r['first_name']=$lang_r['name'];
		if($this->ca->ca_action_id=='forgotpass')
		{
			$output='
			<div id="div_login_def" class="form_table">
				<form id="forgotpass" name="forgotpass" action="'.$this->ca->ca_abs_url.'?process='.$this->ca->ca_action_id.$this->ca->ca_l_amp.'" method="post">
				<ul style="margin: 30px auto 100px;width" class="ca_form liveFormInner '.$this->f->form_class.' sr_forgotpass">
				<li><h1>'.$lang_r['forgotten password'].'</h1></li>
				<li style="padding-bottom:6px;"><span>'.$lang_r['forgot password message'].'</span></li>
				<li class="dragable f_edit">'.
				   sprintf($label,'id_username',$lang_r['username']).'
						<input type="text" value="'.(isset($_POST['submit'])?Formatter::sth(Formatter::stripTags($_POST['username'])):'').'" name="username" class="field text large'.($this->f->form_class==''?' input1':'').'" id="id_username"'.($inside?' placeholder="'.$lang_r['username'].'"':'').'>
						<span id="forgotpass_username" class="rvts12 frmhint"></span>
					</div>
				</li>
				<li class="dragable f_edit">'.
						sprintf($label,'id_email',$lang_r['email']).'
						<input type="text" value="'.(isset($_POST['submit'])?Formatter::sth(Formatter::stripTags($_POST['email'])):'').'" name="email" class="field text large'.($this->f->form_class==''?' input1':'').'" id="id_email"'.($inside?' placeholder="'.$lang_r['email'].'"':'').'>
						<span id="forgotpass_email" class="rvts12 frmhint"></span>
					</div>
				</li>
				<li><br>
					<input type="submit" value="'.$lang_r['submit_btn'].'">
					<span id="forgotpass_error" class="rvts12 frmhint"></span>
					<input type="hidden" name="save" value="save">
				</li>
				</ul>
				</form>
			</div>';

			$this->ca->ca_css.=str_replace('h2,','h1,',$this->f->form_css);

			return $output;
		}
		else
			return $this->build_forgotpass_float();
	}

	protected function build_forgotpass_float()
	{
		$lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
		$lang_r['first_name']=$lang_r['name'];
		$pre=(isset($_GET['root']) && $_GET['root']=='0')?'../':'';
		$vert=isset($_REQUEST['vert']);
		$glu=$vert?F_BR:'</td><td class="col2">';

		$output=F_BR.'
			<form id="forgotpass" name="forgotpass" action="'.$pre.'documents/centraladmin.php?process='.$this->ca->ca_action_id.$this->ca->ca_l_amp.'" method="post">
				<div class="sr_forgotpass2">
					<table class="form_table" align="right">
						<tr><td '.($vert?'':'colspan="2"').'>
							<span class="field_label">'.$lang_r['forgot password message'].F_BR.F_BR.'</span>
						</td></tr>
						<tr><td class="col1">
							<span class="field_label">'.$lang_r['username'].'</span>'
							.$glu.'
							<input class="field" type="text" name="username" value="'.(isset($_POST['submit'])?Formatter::sth(Formatter::stripTags($_POST['username'])):'').'" >
							<span id="forgotpass_username" class="rvts12 frmhint"></span>
						</td></tr>
						<tr><td class="col1">
							<span class="field_label">'.$lang_r['email'].'</span>'
							.$glu.'
							<input class="field" type="text" name="email" value="'.(isset($_POST['submit'])?Formatter::sth(Formatter::stripTags($_POST['email'])):'').'" >
							<span id="forgotpass_email" class="rvts12 frmhint"></span>
						</td></tr>
						<tr><td>'.($vert?'':'</td><td>').'
							<input type="submit" value="'.$lang_r['submit_btn'].'">
							<span id="forgotpass_error" class="rvts12 frmhint"></span>
							<input type="hidden" name="save" value="save">
						</td></tr>
					</table>
				</div>
			</form>';
		return $output;
	}

	public function process_forgotpass()
	{
		global $db;

		$lang_f=$this->f->lang_f[$this->ca->ca_ulang_id];
		$lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
		$lang_r['first_name']=$lang_r['name'];
		$norm_reg=($this->ca->ca_action_id=='forgotpass');
		$errors=array();
		if(isset($_POST['save']))
		{
			$ccheck=isset($_POST['cc'])&&$_POST['cc']=='1';
			$useic=(!$this->f->uni&&$this->f->charset_lang_map[$this->ca->ca_lang]!='iso-8859-1'&&function_exists("iconv"));

			if(!empty($_POST["username"]))
			{
				$usr=Formatter::stripTags(trim($_POST["username"]));
				$user_data=User::mGetUser($usr,$db,'username',false,false);
			}
			if(!empty($_POST["email"]))
			{
				$email=Formatter::stripTags(trim($_POST["email"]));
				$user_data=User::mGetUser($email,$db,'email',false,false);
			}

			if(!isset($usr)&&!isset($email))
				$errors[]=($ccheck?'username|':'').$lang_r['you have to fill'];
			elseif(isset($usr)&&empty($user_data))
				$errors[]=($ccheck?'username|':'').$lang_r['unexisting'];
			elseif(isset($email)&&!Validator::validateEmail($email))
				$errors[]=($ccheck?'email|':'').$lang_f['Email not valid'];
			elseif(isset($email)||isset($usr))
			{
				if(!isset($user_data['email'])||$user_data['email']=='')
					$errors[]=($ccheck?'email'.'|':'').$lang_r[isset($email)?'email not found':'no email for user'];
			}

			if($ccheck)
			{
				$errors_output=implode('|',$errors);
				if($useic)
					$errors_output=iconv($this->f->charset_lang_map[$this->ca->ca_lang],"utf-8",$errors_output);

				if(count($errors)>0)
				{
					print '0'.$errors_output;
					exit;
				}
				else if($norm_reg)
				{
					print '1';
					exit;
				}
			}
			if(count($errors)>0)
				$output=implode(F_BR,$errors).$this->build_forgotpass_form();
			else
			{
				$uniqueid=md5(uniqid(mt_rand(),true));
				$send_to_email=$user_data['email'];
				$confirm_url=$this->ca->ca_abs_url.'?process=forgotpass&confirm='.$uniqueid.'&lang='.
							$this->f->inter_languages_a[$this->ca->ca_ulang_id];
				$confirm_link='<a href="'.$confirm_url.'">'.$confirm_url.'</a>';
				CA::insert_setting(array('fp_'.$uniqueid=>$user_data['uid']));

				$more_macros=array('%confirmlink%'=>$confirm_link,'%confirmurl%'=>$confirm_url);
				$content=Formatter::parseMailMacros($this->ca->lang_l('sr_forgotpass_msg0'),$user_data,$more_macros);
				$subject=Formatter::parseMailMacros($this->ca->lang_l('sr_forgotpass_subject0'),$user_data,$more_macros);
				$result=MailHandler::sendMailCA($db,$content,$subject,$send_to_email);

				$output=F_BR.($norm_reg?'<h1 style="margin:30px 0;">':'<span class="field_label">')
							.$lang_r['check email for instructions'].($norm_reg?'</h1>':'</span>');
			}
		}
		elseif(isset($_GET["confirm"]))
		{
			$uniqueid=trim(Formatter::stripTags($_GET["confirm"]));
			$new_pass=substr(md5(mt_rand()),0,12);
			CA::fetchDBSettings($db);
			$user_id=(isset($this->ca->f->ca_settings['fp_'.$uniqueid]))?$this->ca->f->ca_settings['fp_'.$uniqueid]:'';
			if(!empty($user_id))
			{
				$user_data=User::mGetUser($user_id,$db,'uid',false,false);
				$username=$user_data['username'];
				$send_to_email=$user_data['email'];
				$more_macros=array('%newpassword%'=>$new_pass,'%username%'=>$username);
				$content=Formatter::parseMailMacros($this->ca->lang_l('sr_forgotpass_msg'),$user_data,$more_macros);
				$subject=Formatter::parseMailMacros($this->ca->lang_l('sr_forgotpass_subject'),$user_data,$more_macros);
				$result=MailHandler::sendMailCA($db,$content,$subject,$send_to_email);
				if($result=="1")
				{
					$res=$db->query_update('ca_users',array('password'=>crypt($new_pass)),'uid='.$user_id);
					$this->ca->login->block_user($user_id,true); //unblock user if new pass request confirmed
					$log_msg="success, email SENT";
					if($res!==false)
						$db->query('DELETE FROM '.$db->pre.'ca_settings WHERE skey="fp_'.$uniqueid.'"');
					$label=$lang_r['check email for new password'];
				}
				else
				{
					$log_msg='fail, email FAILED ('.Formatter::stripTags($result).')';
					$label='Email FAILED. Try again.';
				}
				$output=F_BR.'<h5>'.$label.'</h5>';
				ca_log::write_log('forgotpass',$user_id,$username,$log_msg);
			}
			else
				$output=F_BR.'<h5>'.$lang_r['check email for new password'].'</h5> <a class="rvts12" href="'.$this->ca->ca_abs_url.'?process=forgotpass'.'">'.$this->ca->lang_l('forgotten password').'</a>';
		}
		else
			$output=$this->build_forgotpass_form();
		if($norm_reg)
		{
			$output=$this->ca->GT($output,true,$lang_r['forgotten password'],true);
			$output=Builder::includeScript(str_replace('%ID%','forgotpass',$this->f->frmvalidation),$output);
		}
		else
			$output=str_replace('%ID%','forgotpass',$this->f->frmvalidation2).F_LF.$output;

		if($this->ca->ca_css!=='')
			$output=Builder::includeCss($output,$this->ca->ca_css);

		print $output;
	}

	protected function confirm_registration()
	{
		global $db;

		$sr_id=Formatter::stripTags($_GET['id']);
		$user_data=$db->query_first('SELECT * FROM '.$db->pre.'ca_users WHERE self_registered_id="'.addslashes($sr_id).'"');
		$this->user->setData($user_data);
		if(!$this->user->isDataEmpty()&&$this->user->getData('confirmed')==0)
		{
			$time=time();
			$confirm_value=($this->f->ca_settings['sr_require_approval']=='1'?2:1);
			$db->query_update('ca_users',array('confirmed'=>$confirm_value,'creation_date'=>Date::buildMysqlTime($time)),' uid='.$this->user->getId());

			$output='<br><span class="rvts8">'.$this->ca->lang_r['registration was completed'].'</span><br>'
						  .$this->f->ca_settings['sr_confirm_message'];
			$log_msg='success';

			//admin notification
			$cnt='register_id= '.$sr_id.'<br>'.'username= '.$this->user->getUname().'<br>'
				.'email= '.$this->user->getData('email').'<br>'.'date= '.date('Y-m-d G:i',Date::tzone($time)).'<br>';
			foreach($this->f->ca_users_fields_array as $k=> $v)
			{
				if($k=='password' || $k=='self_registered_id' || $k=='self_registered')
					 continue;
				if($this->user->getData($k)!==null && $v['itype']=="userinput")
					$cnt.=$this->ca->lang_l($k).'= '.ca_functions::ca_un_esc($this->user->getData($k)).'<br>';
				elseif($this->user->getData($k)!==null)
					$cnt.=$k.'= '.ca_functions::ca_un_esc($this->user->getData($k)).'<br>';
			}
			$cnt.='IP= '.Detector::getIP().'<br>'
				.'HOST= '.Detector::getRemoteHost().'<br>'
				.'OS= '.(isset($_SERVER['HTTP_USER_AGENT'])?Detector::defineOS($_SERVER['HTTP_USER_AGENT']):"").'<br>';

			$more_macros=array('%user_details%'=>$cnt,'%reg_id%'=>$sr_id);
			$content=Formatter::parseMailMacros($this->ca->lang_l('sr_notif_msg'),$this->user->getAllData(),$more_macros);
			$subject=Formatter::parseMailMacros($this->ca->lang_l('sr_notif_subject'),$this->user->getAllData(),$more_macros);
			$result=MailHandler::sendMailCA($db,$content,$subject);
			$log_msg.=$result=="1"?", notification SENT":", notification FAILED";

			if(!isset($_GET['flag']))
			{
				ca_log::write_log('conf',$this->user->getId(),$this->user->getUname(),$log_msg);
				$redirect_time=$this->f->ca_settings['auto_login_redirect_time'];
				$redirect_location=$this->f->ca_settings['auto_login_redirect_loc'];
				if(isset($this->f->ca_settings['auto_login'])&&$this->f->ca_settings['auto_login']==1)
					$this->ca->login->process_auto_login($redirect_time,$redirect_location);
			}
			else
			{
				ca_log::write_log('confadmin',$this->user->getId(),$this->user->getUname(),$log_msg);
				$manage_users = new ca_users($this);
				$manage_users->pending_users($output);
				exit;
			}
		}
		else
			$output='<br><h5>'.$this->ca->lang_r['registration was completed'].'</h5>';
		return $output;
	}

	protected function check_registration($norm_reg,$float)
	{
		Session::intStart();
		$lang_f=$this->f->lang_f[$this->ca->ca_ulang_id];
		$errors=array();
		$captcha_used=($this->f->ca_settings['sr_disable_captcha']!='1')&&($norm_reg||(!$this->f->reCaptcha&&!$this->f->slidingCaptcha));
		if($captcha_used&&!Session::isSessionSet($this->f->cap_id)&&!Captcha::isRecaptchaPosted())
		{
			echo "You are not allowed to register.";
			exit;
		}
		else
		{
			foreach($_POST as $k=> $v)
				if(!is_array($v))
					$_POST[$k]=trim($v);

			$ccheck=isset($_POST['cc'])&&$_POST['cc']=='1';
			$useic=(!$this->f->uni&&$this->f->charset_lang_map[$this->ca->ca_lang]!='iso-8859-1'&&function_exists("iconv"));

			$post_user=Formatter::stripTags($_POST['username']);
			if(empty($_POST['username']))
				$errors[]=($ccheck?'username'.'|':'').$lang_f['Required Field'];
			elseif(!preg_match("/^[A-Za-z_.@0-9-]+$/",$post_user))
				$errors[]=($ccheck?'username|':'').$this->ca->lang_r['can contain only'];
			elseif($this->ca->users->duplicated_user_check($post_user))
				$errors[]=($ccheck?'username|':'').$this->ca->lang_r['username exists'];

			if(empty($_POST['email']))
				$errors[]=($ccheck?'email|':'').$lang_f['Required Field'];
			elseif(!Validator::validateEmail(Formatter::stripTags($_POST['email'])))
				$errors[]=($ccheck?'email|':'').$lang_f['Email not valid'];
			elseif($this->ca->users->duplicated_email_check(Formatter::stripTags($_POST['email'])))
				$errors[]=($ccheck?'email|':'').$lang_f['email in use'];

			foreach($this->f->ca_users_fields_array as $k=> $v)
			{
				$req=!(isset($v['req'])&&$v['req']=='0');
				$hidden=isset($v['hidinreg'])&&$v['hidinreg']=='1';
				if($req&&!$hidden)
				{
					if(empty($_POST[$k]))
					{
						if($v['itype']=="checkbox")
							$errors[]=($ccheck?$k.'|':'').$lang_f['Checkbox unchecked'];
						elseif($v['itype']=="userinput"||$v['itype']=="listbox")
							$errors[]=($ccheck?$k.'|':'').$lang_f['Required Field'];
					}
					elseif($v['itype']=="listbox")
					{
						$values=explode(';',$this->f->ca_users_fields_array[$k]['values']);
						if($values[0]==$_POST[$k])
							$errors[]=($ccheck?$k.'|':'').$lang_f['Required Field'];
					}
				}
			}

			if(empty($_POST['password']))
				$errors[]=($ccheck?'password|':'').$lang_f['Required Field'];
			else
			{
				$pwd=trim($_POST['password']);
				$pwd_res=Password::checkStrenght($pwd,$this->ca->thispage_id_inca);

				if(!$pwd_res['pass_is_ok'])
					$errors[]=($ccheck?'password|':'').$pwd_res['msg'];
				elseif(empty($_POST['repeatedpassword']))
					$errors[]=($ccheck?'repeatedpassword|':'').$this->ca->lang_r['repeat password'];
				elseif($pwd!=$_POST['repeatedpassword'])
					$errors[]=($ccheck?'repeatedpassword|':'').$this->ca->lang_r['password and repeated password'];
				elseif(strtolower($post_user)==strtolower($_POST['password']))
					$errors[]=($ccheck?'username|':'').$this->ca->lang_r['username equal password'];
			}
			if($captcha_used&&!Captcha::isValid())
				$errors[]=($ccheck?'code|':'').$lang_f['Captcha Message'];
			if(!isset($_POST['agree'])&&!empty($this->f->ca_settings['sr_terms_url']))
				$errors[]=($ccheck?'agree|':'').$this->ca->lang_r['you must agree with terms'];
			if(!empty($errors))
				$errors[]=($ccheck?'error|':'').$lang_f['validation failed'];

			if($ccheck)
			{
				$errors_output=implode('|',$errors);
				if($useic)
					$errors_output=iconv($this->f->charset_lang_map[$this->ca->ca_lang],"utf-8",$errors_output);

				if(count($errors)>0)
				{
					print '0'.$errors_output;
					exit;
				}
				else if($norm_reg)
				{
					print '1';
					exit;
				}
			}
			if(count($errors)>0)
				$output=implode(F_BR,$errors).$this->build_register_form($float);
			else
			{
				$siteUser='';
				$output=$this->ca->users->register_new_user($post_user,$norm_reg,null,$siteUser);
			}
		}
		return $output;
	}

	public function process_register($float)
	{
		if(!$this->f->ca_settings['sr_enable'])
		{
			print $this->ca->GT('<h1>Self-registration is not enabled for this site!</h1>',true);
			exit;
		}

		$this->ca->lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
		$this->ca->lang_r['first_name']=$this->ca->lang_r['name'];
		$norm_reg=($this->ca->ca_action_id=='register');

		if(isset($_POST['save']))
			$output=$this->check_registration($norm_reg,$float);
		elseif(isset($_GET['id']))
			$output=$this->confirm_registration();
		else
			$output=$this->build_register_form($float);

		if($norm_reg)
		{
			$output=$this->ca->GT($output,true,$this->ca->lang_r['registration'],true);
			$output=Builder::includeScript(str_replace('%ID%','selfreg',$this->f->frmvalidation),$output);
			$rel_path=($this->ca->template_in_root?'':'../');

			Captcha::includeCaptchaJs($output,$rel_path,$this->ca->ca_scripts,$this->ca->ca_dependencies);
			if($this->ca->ca_scripts!='')
				$output=Builder::includeScript($this->ca->ca_scripts,$output,$this->ca->ca_dependencies,$rel_path);

			$output=str_replace('<link type="text/css" href="documents/ca.css" rel="stylesheet">','',$output);
		}
		else
		{
			$rel_path=isset($_REQUEST['root'])&&($_REQUEST['root']=='0')?'../':'';

			$output=str_replace('%ID%','selfreg',$this->f->frmvalidation2).F_LF.
				str_replace('<span class="captcha"><img','<span class="captcha"><img onclick="$(\'.captcha img\').remove();loadCaptcha(\''.$rel_path.'\');" ',$output);
		}

		if($this->ca->ca_css!=='')
			$output=Builder::includeCss($output,$this->ca->ca_css);

		print $output;
	}
}

class ca_graphs extends ca_objects
{
	private $os_stat=array();
	private $year_stat=array();
	private $br_stat=array();
	private $last30_stat=array();
	private $res_stat=array();
	private $month_ids;
	private $pid;
	private $purl;

	public function __construct($ca_object)
	{
		parent::__construct($ca_object);

		foreach($this->f->browsers as $k=> $v)
			$this->br_stat[$k]=0;
		foreach($this->f->os as $k=> $v)
			$this->os_stat[$k]=0;
		$this->year_stat=array_fill(0,12,0);
		$this->pid=isset($_GET['pid'])?intval($_GET['pid']):'';

		if($this->pid>0)
			$this->purl=$this->ca->ca_site_url.str_replace('../','',$_GET['purl']);
	}


	protected function month_graphs($x_call,$mon_caption,$last30_d,$h_lbl,$mo,&$month_chart_label)
	{
		$gr=$labels=$data=array();
		$tot=0;
		foreach($this->last30_stat as $k=> $v)
		{
			$gr[$k+1]=$v;
			$labels[$k]=$last30_d[$k];
			$tot+=$v;
		}

		$data[]='[\'Day\',\''.$h_lbl.'\']';
		if($x_call)
			$gr_arr[]=array('Day',$h_lbl);
		foreach($gr as $k=> $v)
		{
			if(isset($labels[$k-1]))
				if($x_call)
					$gr_arr[]=array($labels[$k-1],$v);
				else
					$data[]="['".$labels[$k-1]."',".$v."]";
		}
		$month_chart_label=($mo==0?$this->ca->lang_l('last 30').' ':'').$mon_caption.' ('.$tot.' '.$h_lbl.')';
		if($x_call&&$x_call=='m')
		{
			print json_encode(array('data'=>$gr_arr,'label'=>$month_chart_label));
			exit;
		}
		return $data;
	}

	protected function year_graphs($x_call,$today,$h_lbl,$dd,&$year_chart_label)
	{
		$gr=$labels=$data=array();
		$tot=0;
		foreach($this->year_stat as $k=> $v)
		{
			$gr[$this->ca->lang_l($this->f->month_names[$this->month_ids[$k+1]-1])]=$v;
			$labels[$k]=substr($this->f->month_names[$this->month_ids[$k+1]-1],0,3);
			$tot+=$v;
		}

		$data[]='[\'Month\',\''.$h_lbl.'\']';
		if($x_call)
			$gr_arr[]=array('Month','Hits');
		foreach($gr as $k=> $v)
		{
			if($x_call)
				$gr_arr[]=array($k,$v);
			else
				$data[]="['".$k."',".$v."]";
		}
		$year_chart_label=$this->ca->lang_l('last year').' '.($dd['mon']!=12?($today['year']-1).' - ':'').$today['year'].'  ('.$tot.' '.$h_lbl.')';

		if($x_call&&$x_call=='y')
		{
			print json_encode(array('data'=>$gr_arr,'label'=>$year_chart_label));
			exit;
		}
		return $data;
	}

	protected function browsers_graphs()
	{
		$md=max($this->br_stat)/50;
		$other=0;
		$gr=$data=array();
		foreach($this->br_stat as $k=> $v)
		{
			if($v<$md)
				$other+=$v;
			else
				$gr[$this->f->browsers[$k]]=$v;
		}
		$gr['other']=$other;

		$data[]='[\'Browser\',\'%\']';
		foreach($gr as $k=> $v)
			$data[]="['".$k."',".$v."]";
		return $data;
	}

	protected function resolution_graphs()
	{
		$md=(!empty($this->res_stat))?max($this->res_stat)/100:0;
		$other=0;
		$gr=$data=array();
		foreach($this->res_stat as $k=> $v)
		{
			if($v<$md)
				$other+=$v;
			else
				$gr[$k]=$v;
		}
		$gr['other']=$other;

		$data[]='[\'resolution\',\'%\']';
		foreach($gr as $k=> $v)
			$data[]="['".$k."',".$v."]";
		return $data;
	}

	protected function os_graphs()
	{
		$md=max($this->os_stat)/200;
		$other=0;
		$gr=$data=array();
		foreach($this->os_stat as $k=> $v)
		{
			if($v<$md)
				$other+=$v;
			else
				$gr[$this->f->os[$k]]=$v;
		}
		$gr['other']=$other;

		$data[]='[\'Os\',\'%\']';
		foreach($gr as $k=> $v)
			$data[]="['".$k."',".$v."]";
		return $data;
	}

	protected function read_data($now,$today,$flt,$start,$end,$mo)
	{
		global $db;

		$month_offsets=array();
		$month_offsets[12]=$now-($today['mday']*86400);
		$this->month_ids[12]=$today['mon'];
		$cc=1;
		for($i=11; $i>0; $i--)
		{
			$this->month_ids[$i]=(($today['mon']-$cc)>0)?$today['mon']-$cc:12+($today['mon']-$cc);
			$mj=(($today['mon']-$cc)>0)?$today['year']:$today['year']-1;
			$month_offsets[$i]=$month_offsets[$i+1]-(Date::daysInMonth($this->month_ids[$i],$mj)*86400);
			$cc++;
		}

		$que='
			SELECT browser,os,resolution,date
			FROM '.$db->pre.'counter_details
			WHERE  page_id '.($this->pid>0?'= '.$this->pid:' > 1').'
			AND date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)';

		if($flt!='a'&&$flt!='h')
		{
			$que.=' AND ';
			$que.=($flt=='u')?'(visit_type="r" OR visit_type="f")':'visit_type="'.$flt.'"';
		}

		$records=$db->query($que,MYSQLI_USE_RESULT);
		$ff=array(17,29,28,27,26,25,24,23,22,21,3);
		while($val=$records->fetch_assoc())
		{
			foreach($month_offsets as $k=> $v)
				if(strtotime($val['date'])>$v)
				{
					$this->year_stat[$k-1]+=1;
					break;
				}
			if(strtotime($val['date'])>$start && strtotime($val['date'])<$end)
			{
				if($val['browser']==0 && $val['os']==13)
					$val['browser']=4;
				elseif(in_array($val['browser'],$ff)) //firefox together
					$val['browser']=3;
				$this->br_stat[$val['browser']]+=1;
				if($val['os']==3)
					$val['os']=15;
				$this->os_stat[$val['os']]+=1;

				if(strpos($val['resolution'],'screen.width')!=false)
					$val['resolution']='1024x768';
				$re=$val['resolution'];
				if($re=='')
					$re='other';
				$this->res_stat[$re]=(isset($this->res_stat[$re]))?$this->res_stat[$re]+1:1;
				if($mo==0)
					$dday=(int)floor((strtotime($val['date'])-$start)/86400);
				else
					$dday=intval(date('d',strtotime($val['date'])))-1;
				$this->last30_stat[$dday]+=1;
			}
		}
		$records->close();
	}

	protected function resolve_header()
	{
		$header=' - '.$this->ca->lang_l('graph stat').' ';
		if($this->pid>0)
			$header.=' <a class="nodec" target="_blank" href="'.$_GET['purl'].'" title="'.$this->purl.'">"'.$_GET['pname'].'"</a> '.$this->ca->lang_l('page');
		else
		{
			$filter=$_GET['f'];
			if($filter=='h')
				$header.=$this->ca->lang_l('total pageloads');
			else if($filter=='u')
				$header.=$this->ca->lang_l('unique visitors');
			else if($filter=='f')
				$header.=$this->ca->lang_l('first time visitors');
			else if($filter=='r')
				$header.=$this->ca->lang_l('returning visitors');
		}
		return $header;
	}

	public function sitemap_graphs($admin,&$header_text,&$js,&$scripts,$x_call=false)
	{
		$type=(isset($this->f->ca_settings['chart_type']))?$this->f->ca_settings['chart_type']:'a';

		$display=(isset($this->f->ca_settings['display'])?$this->f->ca_settings['display']:'0');
		$flt=(isset($_GET['f'])?Formatter::stripTags($_GET['f']):($display=='0'?'u':'h'));
		if($flt=='h'||$flt=='a')
			$h_lbl=$this->ca->lang_l('hits');
		elseif($flt=='u')
			$h_lbl=$this->ca->lang_l('unique visitors');
		elseif($flt=='f')
			$h_lbl=$this->ca->lang_l('first time visitors');
		elseif($flt=='r')
			$h_lbl=$this->ca->lang_l('returning visitors');

		$mo=isset($_GET['mo'])?intval($_GET['mo']):0;

		$query_st_time=Date::microtimeFloat();

		$d=time();
		$d+=86400;
		$dd=getdate($d);
		$now=mktime(0,0,0,$dd['mon'],$dd['mday'],$dd['year']);
		$m=$dd['mon']-12+$mo;
		if($m<1)	$m+=12;

		$y=($dd['mon']-12+$mo)>0?$dd['year']:$dd['year']-1;
		$today=getdate($now-86400);
		$days_in_mon=Date::daysInMonth($mo==0?$today['mon']:$m,$mo==0?$today['year']:$y);
		$this->last30_stat=array_fill(0,$mo==0?30:$days_in_mon,0);

		$last30_d=array();
		$offset=$today['mday']-30;
		$mon_caption=$this->f->month_names[($mo==0?$today['mon']:$m)-1];
		if($mo!=0)
		{
			for($i=1; $i<=$days_in_mon; $i++)
				$last30_d[]=$i;
		}
		else if($offset<0)
		{
			$days_in_prev_m=Date::daysInMonth($today['mon']-1,$today['year']);
			for($i=$days_in_prev_m-abs($offset)+1; $i<=$days_in_prev_m; $i++)
				$last30_d[]=$i;
			for($i=1; $i<=$today['mday']; $i++)
				$last30_d[]=$i;
			$mon_caption=$this->f->month_names[(($today['mon']-2)==-1?11:$today['mon']-2)].' - '.$this->f->month_names[$today['mon']-1];
		}
		else
			for($i=$offset; $i<=$today['mday']; $i++)
				$last30_d[]=$i;

		$query_st_time=Date::microtimeFloat();
		if($mo==0)
		{
			$start=$now-86400*30;
			$end=$now+1;
		}
		else
		{
			$start=mktime(0,0,0,$m,1,$y);
			$end=mktime(0,0,0,$m==12?1:$m+1,1,$m==12?$y+1:$y);
		}

		$this->read_data($now,$today,$flt,$start,$end,$mo);
		$header_text=$this->resolve_header();

		$month_chart_label=$year_chart_label='';
		$month_chart_data=$this->month_graphs($x_call,$mon_caption,$last30_d,$h_lbl,$mo,$month_chart_label);
		$year_chart_data=$this->year_graphs($x_call,$today,$h_lbl,$dd,$year_chart_label);

		$browser_chart_data=$this->browsers_graphs();
		$res_chart_data=$this->resolution_graphs();
		$os_chart_data=$this->os_graphs();

		$html='
			<table class="atable graphs '.$this->f->atbg_class.'" style="margin:0 auto;background:white;">
			<tr>
				<td class="'.$this->f->atbg_class.'" colspan="3"><div id="month_chart"></div></td>
			</tr>
			<tr>
				<td class="'.$this->f->atbg_class.'" colspan="3"><div id="year_chart"></div></td>
			<tr>
				<td class="'.$this->f->atbg_class.'"><div id="browser_chart"></div></td>
				<td class="'.$this->f->atbg_class.'"><div id="res_chart"></div></td>
				<td class="'.$this->f->atbg_class.'"><div id="os_chart"></div></td>
			</tr>
			</table>';

		$abs_url=$this->ca->ca_abs_url.'?process=index'.$this->ca->ca_l_amp.'&amp;f='.$flt
			.($this->pid>0?'&amp;pid='.$this->pid.'&purl='.$this->purl.'&pname='.$_GET['pname']:'');

		if($admin)
			$html.='<br>
				<input type="button" value=" '.$this->ca->lang_l('detailed stat').' " onclick="document.location=\''.$abs_url.'&amp;stat=olddetailed\'">
				<input type="button" value=" '.$this->ca->lang_l('chart type').' " onclick="document.location=\''.$abs_url.'&amp;stat=detailed&amp;t='.($type=='c'?'a':'c').'\'">';
		$html.=F_BR.F_BR.CA::formatNotice(str_replace('***',round(Date::microtimeFloat()-$query_st_time,4),$this->ca->lang_l('page generated')));
		$html=$this->f->navlist.$html.$this->f->navend;

		$scripts='<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
		$js='
			function fixGoogleCharts(strChartContainer) {return function () {$("svg","#"+strChartContainer).each( function() {$(this).find("g").each(function() {if(!$(this).attr("clip-path")) return;if($(this).attr("clip-path").indexOf("url(#")==-1) return;$(this).attr("clip-path","url("+document.location+$(this).attr("clip-path").substring(4))});});}}
			google.load("visualization","1", {\'packages\':["corechart"]});
			google.setOnLoadCallback(drawChart);
				function drawChart() {
					var av_w=$(".a_caption").width(),pie_w=av_w/3;

					var options_h={
					title: "",height:300,legend:{position:"none"},chartArea:{width:"90%"},colors:["'.$this->f->chart_colors[1].'"],backgroundColor:{fill:"transparent"},
					hAxis:{textStyle: {color: "'.$this->f->chart_colors[3].'",fontSize:12},gridlines:{color:"'.$this->f->chart_colors[2].'"},minorGridlines:{color:"'.$this->f->chart_colors[2].'"} },
					vAxis:{textStyle: {color: "'.$this->f->chart_colors[3].'"},gridlines:{color:"'.$this->f->chart_colors[2].'"} },
					titleTextStyle:{color:"'.$this->f->chart_colors[3].'"}
					};

					var options_v={title: "",width:pie_w,backgroundColor:{fill:"transparent"}
					,legend:{textStyle: {color: "'.$this->f->chart_colors[3].'"}}
					,titleTextStyle:{color:"'.$this->f->chart_colors[3].'"}
					};

					var data_y=google.visualization.arrayToDataTable([ '.implode(',',$year_chart_data).' ]);
					var options_y=options_h;options_y.title="'.$year_chart_label.'";
					var chart_y=new google.visualization.'.($type=='c'?'ColumnChart':'AreaChart').'(document.getElementById(\'year_chart\'));

					google.visualization.events.addListener(chart_y,\'select\',selectHandler);
					'.($type=='c'?'google.visualization.events.addListener(chart_y,"ready",fixGoogleCharts("year_chart"));':'').'
					chart_y.draw(data_y, options_y);
					function selectHandler() {
						var selectedItem=chart_y.getSelection()[0];
						var value=selectedItem.row+1;
						$.getJSON(window.location+\'&x=m&mo=\'+value, function(r) {
							data_m=google.visualization.arrayToDataTable(r.data);
							options_m.title=r.label;
							chart_m.draw(data_m,options_m);
						});
					}

					var data_m=google.visualization.arrayToDataTable([ '.implode(',',$month_chart_data).' ]);
					var options_m=options_h;options_m.title="'.$month_chart_label.'";options_m.colors[0]="'.$this->f->chart_colors[0].'";
					var chart_m=new google.visualization.'.($type=='c'?'ColumnChart':'AreaChart').'(document.getElementById(\'month_chart\'));
					'.($type=='c'?'google.visualization.events.addListener(chart_m,"ready",fixGoogleCharts("month_chart"));':'').'
					chart_m.draw(data_m,options_m);


					var data_b=google.visualization.arrayToDataTable([ '.implode(',',$browser_chart_data).' ]);
					var options_b=options_v;options_b.title="'.$this->ca->lang_l('browser').'";
					var chart_b=new google.visualization.PieChart(document.getElementById(\'browser_chart\'));
					chart_b.draw(data_b, options_b);

					var data_r=google.visualization.arrayToDataTable([ '.implode(',',$res_chart_data).' ]);
					var options_r=options_v;options_r.title="'.$this->ca->lang_l('resolution').'";
					var chart_r=new google.visualization.PieChart(document.getElementById(\'res_chart\'));
					chart_r.draw(data_r, options_r);

					var data_o=google.visualization.arrayToDataTable([ '.implode(',',$os_chart_data).' ]);
					var options_o=options_v;options_o.title="'.$this->ca->lang_l('os').'";
					var chart_o=new google.visualization.PieChart(document.getElementById(\'os_chart\'));
					chart_o.draw(data_o, options_o);
				}
	';

		return $html;
	}

}

class ca_screens extends admin_screens
{
	public $ca;
	public $f;
	public $dp_array=array();

	public function __construct($ca_object)
	{
		global $f;

		if($ca_object instanceof CaClass)
			$this->ca=$ca_object;

		$this->f=$f;
	}

	//$dependencies can accept both array of $dependencies and text
	final public function screen_output($output)
	{
		$pref=$this->ca->template_in_root? '':'../';
		$output=Builder::includeScript($this->ca->ca_scripts,$output,$this->ca->ca_dependencies,$pref);
		if($this->ca->ca_css!=='')
			 $output=Builder::includeCss($output,$this->ca->ca_css);
		if(count($this->dp_array)>0)
		  $output=Builder::includeDatepicker($output,$this->f->month_names,$this->f->day_names,$this->dp_array);

		if($this->ca->ca_action_id == 'analytics')
			$output=str_replace(
			'<meta name="keywords"',
			'<link rel="stylesheet" href="analytics/main.css">
			<script src="analytics/platform.js"></script>
			<link rel="import" href="analytics/ga-auth.html">
			<link rel="import" href="analytics/ga-dashboard.html">
			<link rel="import" href="analytics/ga-chart.html">
			<link rel="import" href="analytics/ga-viewpicker.html">
			<link rel="import" href="analytics/ga-datepicker.html">
			<link rel="import" href="analytics/ga-activeusers.html">
			<meta name="keywords"',$output);
		$output=str_replace('%RTL%','',$output);
		print $output;
	}
	public function get_newMessages($logged_uid)
	{
		global $db;

		$isAdmin=$this->ca->user->isAdmin();
		return $db->query_singlevalue('SELECT COUNT(*)
			FROM '.$db->pre.'ca_messenger_messages as m
			'.(!$isAdmin?'RIGHT JOIN '.$db->pre.'ca_messenger_settings as s ON s.uid=0 AND s.skey="ch_disable_messenger_users" AND s.sval=0 ':'')
			.'WHERE m.receiver_uid='.$logged_uid.' AND m.read_receiver=0 AND m.del_receiver=0');
	}

	public function form_buttons($btn1_name='save',$cancel=true,$cancel_action='onclick="javascript:history.back();"')
	{
		$res='
			<div style="margin: 5px 0">
				<input name="'.$btn1_name.'" type="submit" value="'.$this->ca->lang_l('save').'">'.
				($cancel?'<input type="button" value=" '.$this->ca->lang_l('cancel').' " '.$cancel_action.'>':'').'
			</div>';
		return $res;
	}
}

class ca_admin_screens extends ca_screens
{
	public function output($output,$caption='')
	{
		$output=Formatter::fmtAdminScreen($output,$this->build_menu($caption));
		$output=$this->ca->GT($output,false);
		parent::screen_output($output);
	}

	public function build_menu($caption='')
	{
		global $db;

		$logged_user=$this->ca->user->mGetLoggedUser($db);
		$count_newMsg=$this->get_newMessages($logged_user['uid']);
		$url_base=$this->ca->ca_abs_url.'?process=';
		$data=array();
		$data[]=Navigation::addEntry($this->ca->lang_l('site map'),$url_base."index".$this->ca->ca_l_amp,$this->ca->ca_action_id=='index','sitemap');
		if($this->f->ca_settings['ga_clientID']!='' && $this->f->ca_fullscreen)
			$data[]=Navigation::addEntry($this->ca->lang_l('analytics'),$url_base."analytics".$this->ca->ca_l_amp,$this->ca->ca_action_id=='analytics','analytics');

		$data[]=Navigation::addEntry($this->ca->lang_l('manage users'),$url_base."users".$this->ca->ca_l_amp,in_array($this->ca->ca_action_id,array('users','pendingreg','processuser','assigntogroup','import','import2','mail_users')),'users');
		$data[]=Navigation::addEntry($this->ca->lang_l('manage groups'),$url_base."groups".$this->ca->ca_l_amp,in_array($this->ca->ca_action_id,array('groups','fixgroups','processgroup','remusrfromgrp')),'groups');
		$data[]=Navigation::addEntry($this->ca->lang_l('manage polls'),$url_base."polls".$this->ca->ca_l_amp,$this->ca->ca_action_id=='polls','polls');
		$data[]=Navigation::addEntry($this->ca->lang_l('email log'),$url_base."maillog".$this->ca->ca_l_amp,$this->ca->ca_action_id=='maillog','maillog');
		$data[]=Navigation::addEntry($this->ca->lang_l('site_history'),$url_base.'history'.$this->ca->ca_l_amp,$this->ca->ca_action_id=='history','site_history');
		$data[]=Navigation::addEntry($this->ca->lang_l('log'),$url_base."log".$this->ca->ca_l_amp,$this->ca->ca_action_id=='log','log');
		$data[]=Navigation::addEntry($this->ca->lang_l('log errors'),$url_base."log_errors".$this->ca->ca_l_amp,$this->ca->ca_action_id=='log_errors','log_errors','','',true);
		$data[]=Navigation::addEntry($this->ca->lang_l('media'),$url_base."media_library".$this->ca->ca_l_amp,$this->ca->ca_action_id=='media_library','pic');
		$data[]=Navigation::addEntry($this->ca->lang_l('messages').($count_newMsg>0?' ('.$count_newMsg.')':''),$url_base."messenger".$this->ca->ca_l_amp,$this->ca->ca_action_id=='messenger','messenger','','messenger_link');
		$data[]=Navigation::addEntry($this->ca->lang_l('settings'),$url_base."settings".$this->ca->ca_l_amp,in_array($this->ca->ca_action_id,array('settings','confreglang','regfields','confreg','resetcounter','confcounter')),'settings');
		$data[]=Navigation::addEntry($this->ca->lang_l('logout'),$url_base."logoutadmin".$this->ca->ca_l_amp,$this->ca->ca_action_id=='logoutadmin','logout',$this->f->admin_nickname,'a_right last');

		$output=Navigation::admin2($data,$caption);
		return $output;
	}

	public function check_section_range($standalone,$username='',$access_data='') // check section range screen
	{
		$random_int=round(microtime(true) * 1000);
		$section_range=$this->ca->sitemap->ca_get_prot_pages_list(true);
		if($username!='')
		{
			if(!empty($access_data))
			{
				foreach($access_data as $k=> $v)
				{
					if($v['page_id']!=0)
						$access_by_page[$v['page_id']]=$v['access_type'];
					else
						$a_type=$v['access_type'];
					$limit_own_post_a[$v['page_id']]=$v['limit_own_post'];
				}
			}
		}
		$pro=$unpro='';
		$line='
			<div style="position:relative;">
				<div style="padding: 3px 0 3px 10px;">
					:: <a class="rvts12" target="_blank" title="%s" href="%s">%s</a>
				</div>
				<div style="position:absolute;right:0px;width:120px;top:0px" align="right">%s</div>
			</div>';

		$output='<div style="width:285px;"><div style="padding-left:15px;" align="left">';
		foreach($section_range as $k=> $v)
		{
			$fixed_url=$this->ca->ca_site_url.str_replace('../','',$v['url']);
			$url=str_replace('..','',$v['url']);
			$limit_own_posts_t=array();
			$is_lister=$v['typeid']==SHOP_PAGE || $v['typeid']==CATALOG_PAGE;

			if($v['typeid']==CALENDAR_PAGE ||$v['typeid']==BLOG_PAGE||$v['typeid']==PODCAST_PAGE||$v['typeid']==PHOTOBLOG_PAGE || $is_lister)
			{
				if($v['protected']=='TRUE')
					$access_type_f=array('0'=>'view','1'=>'edit','3'=>'edit own posts','2'=>'no access','5'=>'admin');
				else
					$access_type_f=array('0'=>'no access','1'=>'edit','3'=>'edit own posts','5'=>'admin'); //edit own
				if($is_lister)
					$limit_own_posts_t=array('0'=>'no limit','1'=>'50','2'=>'20','3'=>'10','4'=>'5','5'=>'1');
			}
			elseif($v['typeid']==SURVEY_PAGE)
			{
				if($v['protected']=='TRUE')
					$access_type_f=array('0'=>'view','1'=>'edit','2'=>'no access','5'=>'admin');
				else
					$access_type_f=array('0'=>'view','1'=>'edit','5'=>'admin'); //edit own
			}
			elseif($v['typeid']==OEP_PAGE)
			{
				if($v['protected']=='TRUE')
					$access_type_f=array('0'=>'view','1'=>'edit','2'=>'no access','5'=>'admin');
				else
					$access_type_f=array('0'=>'no access','1'=>'edit','5'=>'admin'); //edit own
				if($v['udp']=='TRUE')
					$access_type_f['4']='admin edit';
			}
			else
			{
				if($v['protected']=='TRUE')
					$access_type_f=in_array($v['typeid'],$this->f->sp_pages_ids)?array('0'=>'view','1'=>'edit','2'=>'no access'):array('0'=>'view','2'=>'no access');
				else
					$access_type_f=array('0'=>'no access','1'=>'edit', '5'=>'admin'); //edit own
			}

			if(!$standalone)
			{
				if(isset($access_by_page)&&isset($access_by_page[$v['id']]))
					$default=$access_by_page[$v['id']];
				else
					$default='2';
				$combo=Builder::buildSelect('access_to_page'.$v['id'],$access_type_f,$default,'style="width:110px"'.(!empty($limit_own_posts_t)?' onchange="var th_v=$(this).val();$(\'.limit_own_post'.$v['id'].$random_int.'\').css(\'visibility\',th_v==\'3\'?\'visible\':\'hidden\')"':''));
				if(!empty($limit_own_posts_t)){
					if(isset($limit_own_post_a)&&isset($limit_own_post_a[$v['id']]))
						$default_limit=$limit_own_post_a[$v['id']];
					else
						$default_limit='0';
					$dropdown_limit=Builder::buildSelect('limit_own_post'.$v['id'],$limit_own_posts_t,$default_limit,'style="width:80px; position:absolute;'.($default=='3'?'':'visibility:hidden').'"','key','',' class="input1 limit_own_post'.$v['id'].$random_int.'"');
					$combo.=$dropdown_limit;
				}
			}
			elseif(isset($access_by_page))
				$combo='<span class="rvts8">[ '.(isset($access_by_page[$v['id']])&&isset($access_type_f[$access_by_page[$v['id']]])?$access_type_f[$access_by_page[$v['id']]]:'no access').' ]</span>';
			else
				$combo='<span class="rvts8">[ '.(isset($a_type)?$this->ca->ca_access_type[$a_type]:$this->ca->ca_access_type_ex['2']).' ]</span>';

			if($v['protected']=='TRUE')
				$pro.=sprintf($line,$url,$fixed_url,$v['name'],$combo);
			elseif($v['protected']=='FALSE')
				$unpro.=sprintf($line,$url,$fixed_url,$v['name'],$combo);
		}
		$pro_label=($pro!='')?F_BR.$this->ca->lang_l('protected pages'):'';
		$unpro_label=($unpro!='')?$this->ca->lang_l('unprotected pages'):'';
		$output.=sprintf('<span class="rvts8">%s</span>'.F_BR."%s".F_BR.'<span class="rvts8">%s</span>'.F_BR.'%s',$pro_label,$pro,$unpro_label,$unpro);
		return $output.'</div></div>';
	}


	public function get_access_typelabel($at)
	{
		switch($at)
		{
			case '0': return $this->ca->lang_l('view all');
			case '1': return $this->ca->lang_l('edit all');
			case '9': return $this->ca->lang_l('admin');
		}
		return '';
	}

	public function build_extra_access_option($selected=-1,$start_with_br=true)
	{
		$grps_html=$this->ca->groups->build_groups($this->ca->lang_l('use group'),false,'',$selected);
		$inactive_radio=strpos($grps_html,'<select')===false;
		$output=($start_with_br?F_BR:'').'<input type="radio" name="select_all" id="select_all_from_grp" value="from_group"'
			.($selected!=-1?' checked="checked"':'')
			.($inactive_radio?'disabled="disabled"':'')
			.' onclick="$(\'#selected_holder\').hide();">';
		$output .= $grps_html;

		return $output;
	}

	protected function prepare_page_vars(&$curr_page,&$orderby,&$asc,&$orderby_pfix,&$asc_pfix)
	{
		$curr_page=isset($_GET['page'])?intval($_GET['page']):1;
		list($orderby,$asc)=Filter::orderBy('username','ASC');
		$orderby_pfix=($orderby=='username')?'':'&amp;orderby='.$orderby;
		$asc_pfix='&amp;asc='.$asc;
	}

	public function build_access_line($user_is_admin,$extra_access_line=false,$usrid=0,$access_data='')
	{
		$select_all_flag=isset($_POST['select_all']);
		$select_all_val=($select_all_flag)?$_POST["select_all"]:'undefined';
		$access_all=isset($access_data[0]) && $access_data[0]['section']=='ALL';

		if($usrid==0)
		{
			$is_visitor=(!$select_all_flag||$select_all_val=='yes');
			$is_editor=($select_all_flag&&$select_all_val=='yesw');
			$is_useradmin=($select_all_flag&&$select_all_val=='yeswa');
			$page_level=($select_all_flag&&$select_all_val=='no');
		}
		else
		{
			$is_visitor=($access_all&&$access_data[0]['access_type']==0);
			$is_editor=($access_all&&$access_data[0]['access_type']==1);
			$is_useradmin=($access_all&&$access_data[0]['access_type']==9);
			$page_level=($select_all_val=='no'||isset($access_data[0]) && $access_data[0]['section']!='ALL'||!isset($access_data[0]));

			$selected_sec_ids=$selected_sec_access=array();
			if($access_data!=''&&!$access_all)
			{
				foreach($access_data as $v)
				{
					$selected_sec_ids[]=$v['section'];
					$selected_sec_access[]=($v['page_id']==0?$v['access_type']:'2');
				}
			}
		}

		$access_line='
			<p><input type="radio" name="select_all" value="yes" '.($is_visitor?'checked="checked"':'').' onclick="$(\'#selected_holder'.$usrid.'\').hide();">
			<span class="rvts8" title="'.$this->ca->lang_l('adduser_msg2').'">'.$this->ca->lang_l('view all').'</span></p>
			<p><input type="radio" name="select_all" value="yesw" '.($is_editor?'checked="checked"':'').' onclick="$(\'#selected_holder'.$usrid.'\').hide();">
			<span class="rvts8" title="'.$this->ca->lang_l('adduser_msg3').'">'.$this->ca->lang_l('edit all').'</span></p>'.
			($user_is_admin?'':'<p><input type="radio" name="select_all" value="yeswa" '.($is_useradmin?'checked="checked"':'').' onclick="$(\'#selected_holder'.$usrid.'\').hide();">
				 <span class="rvts8">'.$this->ca->lang_l('admin').'</span></p>').'
			<p><input type="radio" name="select_all" value="no" '.($page_level?'checked="checked"':'').' onclick="$(\'#selected_holder'.$usrid.'\').show();">
			<span class="rvts8"> '.$this->ca->lang_l('page level').' </span></p>
			<div id="selected_holder'.$usrid.'" style="display:'.($page_level?'block':'none').';">
				<div>'.
					($usrid==0?$this->check_section_range(0):$this->check_section_range(0,($access_all?'':$usrid),$access_data)).'
				</div>
			</div>'.
			($extra_access_line!==false?$extra_access_line:'');

		return '<div class="sub_bg" style="width:auto;padding:8px;margin:4px;border-radius:4px">'.$access_line.'</div>';
	}

}

class ca_visitor_screens extends ca_screens
{
	public $has_shop;
	public $logged_user_data;

	public function __construct($ca_object,$logged_user_data)
	{
		parent::__construct($ca_object);
		$this->has_shop=$this->ca->sitemap->get_shop();
		$this->logged_user_data=$logged_user_data;
	}

	public function output($output,$caption='')
	{
		$output=Formatter::fmtAdminScreen($output,$this->build_menu($caption));
		$output=$this->ca->GT($output,false,'',true);
		parent::screen_output($output);
	}

	public function build_menu($caption='')
	{
		$logged_as=$this->logged_user_data['username'];
		$count_newMsg=$this->get_newMessages($this->logged_user_data['uid']);

		$url_base=$this->ca->ca_abs_url.'?'.($this->ca->thispage_id_inca>0?'pageid='.$this->ca->thispage_id_inca.'&amp;':'').'process=';
		$data=array();
		if($this->f->ca_settings['show_sitemap'])
		$data[]=Navigation::addEntry($this->ca->lang_l('site map'),
				$url_base."myprofile".$this->ca->ca_l_amp,
				in_array($this->ca->ca_action_id,array('myprofile','stat','showprofile')),'sitemap');

		if($this->f->ca_settings['profilechange_enable'])
			$data[]=Navigation::addEntry($this->ca->lang_l('profile'),
					$url_base."editprofile".$this->ca->ca_l_amp,
					in_array($this->ca->ca_action_id,array('editprofile')),'profile');

		if($this->has_shop && $this->f->ca_settings['show_orders'])
			$data[]=Navigation::addEntry($this->ca->lang_l('my orders'),
					$url_base."vieworders".$this->ca->ca_l_amp,
					$this->ca->ca_action_id=='vieworders','vieworders');

		if($this->f->ca_settings['pwchange_enable']&&$this->logged_user_data['pass_changeable']==1&&!$this->ca->user->isFBLogged())
			$data[]=Navigation::addEntry($this->ca->lang_l('change password'),
					$url_base."changepass".$this->ca->ca_l_amp,
					$this->ca->ca_action_id=='changepass','changepass');

		$data[]=Navigation::addEntry($this->ca->lang_l('messages').($count_newMsg>0?' ('.$count_newMsg.')':''),
					$url_base."mymessenger".$this->ca->ca_l_amp,
					in_array($this->ca->ca_action_id,array('mymessenger')),'mymessenger','','messenger_link');

		$data[]=Navigation::addEntry($this->ca->lang_l('logout'),
					$url_base.'logout'.$this->ca->ca_l_amp,'','logout',$logged_as,'a_right last');
		$output=Navigation::admin2($data,$caption);
		return $output;
	}

}

class ca_manage_users_screen extends ca_admin_screens
{
	public function handle_screen($logged_user_data)
	{
		if($this->ca->ca_action_id=="users"  || $this->ca->ca_action_id=="assigntogroup")
			$this->manage_users($logged_user_data);
		elseif($this->ca->ca_action_id=="processuser")
			$this->process_users($logged_user_data);
		elseif($this->ca->ca_action_id=="pendingreg")
			$this->pending_users();
		elseif($this->ca->ca_action_id=="mail_users")
			$this->mail_users();
		elseif($this->ca->ca_action_id=='import'||$this->ca->ca_action_id=='import2')
			$this->users_import();
		elseif($this->ca->ca_action_id=="export")
			$this->manage_users_export(isset($_REQUEST['asPDF']),isset($_REQUEST['paper'])?$_REQUEST['paper']:'A4',isset($_REQUEST['orientation'])?$_REQUEST['orientation']:'portrait');
	}

	public function manage_users($logged_user_data)
	{
		global $db;
		if(isset($_REQUEST['removeuser']))
		{
			if(is_array($_REQUEST['removeuser']))
				foreach($_REQUEST['removeuser'] as $uid)
					$this->ca->users->remove_user((int) $uid);
		}
		$output='';
		$user_is_admin=$logged_user_data['user_admin']===1;
		$user_id=$logged_user_data['uid'];

		$c_page=$orderby=$asc=$orderby_pfix=$asc_pfix='';
		$this->prepare_page_vars($c_page,$orderby,$asc,$orderby_pfix,$asc_pfix);

		if(isset($_GET['q'])&&!empty($_GET['q'])) //search
		{
			$ss=$db->escape(Formatter::stripTags($_GET['q']));
			$fn=$sn='';
			foreach($this->f->ca_users_fields_array as $k=> $v)
			{
				if($k=='first_name')
					$fn='OR first_name LIKE "%'.$ss.'%" ';
				elseif($k=='surname')
					$sn='OR surname LIKE "%'.$ss.'%"';
			}

			$where='confirmed=1 AND (username LIKE "%'.$ss.'%" OR display_name LIKE "%'.$ss.'%" '.$fn.$sn.' OR email LIKE "%'.$ss.'%")';
		}
		else
			$where='confirmed=1';

		$total_records=ca_db::db_count('ca_users',$where);
		$start=($c_page-1)*Navigation::recordsPerPage();
		if($start>$total_records)
		{
			$c_page=1;
			$start=0;
		}
		$limit=(($total_records>Navigation::recordsPerPage()&&Navigation::recordsPerPage()>0)?'LIMIT '.$start.', '.Navigation::recordsPerPage().'':'');
		if($orderby=='details')
		{
			 $orderbya=array();
			 if(isset($this->f->ca_users_fields_array['first_name']))
				  $orderbya[]='first_name';
			 if(isset($this->f->ca_users_fields_array['surname']))
				  $orderbya[]='surname';
			 if(isset($this->f->ca_users_fields_array['email']))
				  $orderbya[]='email';
			 $orderbyTemp=implode(',',$orderbya);
		}
		else
			 $orderbyTemp=$orderby;
		$que='
			SELECT u.*, g.name as group_name
			FROM '.$db->pre.'ca_users u
			LEFT JOIN '.$db->pre.'ca_users_groups_links l ON u.uid=l.user_id
			LEFT JOIN '.$db->pre.'ca_users_groups g ON g.id=l.group_id
			WHERE '.$where.'
			ORDER BY '.$orderbyTemp.' '.$asc.' '.$limit;
		$users_data=$db->fetch_all_array($que);

		$users_access=array();
		if(!empty($users_data))
		{
			$users_list=array();
			foreach($users_data as $k=> $v)
				$users_list[]=$v['uid'];
			$where='user_id IN ('.implode(',',$users_list).')';
			$users_access=$this->ca->user->mGetUserAccess($db,$where);
			$news_access=$this->ca->user->mGetUserNews($db,$where);
		}

		$nav='
			<div>
				<div style="float:left;">
					<input type="button" value="'.$this->ca->lang_l('add user').'" onclick="document.location=\''.$this->ca->ca_abs_url.'?process=processuser'.$this->ca->ca_l_amp.'\'">
					<input type="button" value="'.$this->ca->lang_l('import').'" onclick="document.location=\''.$this->ca->ca_abs_url.'?process=import'.$this->ca->ca_l_amp.'\'">
					<input type="button" value="'.$this->ca->lang_l('unconfirmed users').'" onclick="document.location=\''.$this->ca->ca_abs_url.'?process=pendingreg'.$this->ca->ca_l_amp.'\'">
					<input type="button" value="'.$this->ca->lang_l('export').'" onclick="document.location=\''.$this->ca->ca_abs_url.'?process=export'.$this->ca->ca_l_amp.'\'">
					<input type="button" value="'.$this->ca->lang_l('export pdf').'" onclick="document.location=\''.$this->ca->ca_abs_url.'?process=export&asPDF&paper=A4&orientation=\'+document.getElementById(\'pdforient\').value+\''.$this->ca->ca_l_amp.'\'">
					<select id="pdforient" name="paper">
						<option value="portrait" selected="selected">'.$this->ca->lang_l('portrait').'</option>
						<option value="landscape">'.$this->ca->lang_l('landscape').'</option>
					</select>
				</div>
				<div style="text-align:right;">
					<input type="text" id="q" name="q" value="">
					<input type="button" name="search" class="ca_button" value="&#xf002"
					title="'.$this->ca->lang_l('search').'" onclick="document.location=\''.$this->ca->ca_abs_url.'?process=users&q=\'+$(\'#q\').val();">
				</div>
			</div>
			<div style="clear:both;"></div>
			';

		$this->ca->ca_scripts='$(document).ready(function(){$(".inner_ca_frm").utils_frmvalidate(1,0,0,0,0,0,0);});';

		$nav.=Navigation::pageCA($total_records,$this->ca->ca_abs_url.'?process=users'.$orderby_pfix.$asc_pfix,0,$c_page);
		$url=$this->ca->ca_abs_url."?process=processuser".$this->ca->ca_l_amp;

		$table_data=$cap_arrays=array();
		if(!empty($users_data))
		{
			//form tags added here are trick to make nested forms work (if nested forms, first nested one is removed by the browser)
			$cap_arrays=array('<input type="checkbox" onclick="$(\'.mng_entry_chck\').prop(\'checked\',$(this).is(\':checked\'));">',
				'username'=>array($this->ca->ca_abs_url.'?process=users&amp;orderby=username','none',$this->ca->lang_l('user')),
				'group_name'=>array($this->ca->ca_abs_url.'?process=users&amp;orderby=group_name','none',$this->ca->lang_l('group')),
				'details'=>array($this->ca->ca_abs_url.'?process=users&amp;orderby=details','none',$this->ca->lang_l('details')),
				$this->ca->lang_l('access to'),
				'status'=>array($this->ca->ca_abs_url.'?process=users&amp;orderby=status','none',$this->ca->lang_l('status')));

			$cap_arrays[$orderby][1]='underline';
			if($asc=='ASC')
				$cap_arrays[$orderby][0]=$cap_arrays[$orderby][0].'&amp;asc=DESC';

			foreach($users_data as $value)
			{
				if(!empty($value))
				{
					$usr=$value['username'];
					$usrid=$value['uid'];
					$usraccess=(isset($users_access[$usrid])?$users_access[$usrid]:array());
					$usrnews=(isset($news_access[$usrid])?$news_access[$usrid]:array());

					$userArea='<span class="rvts8">'.$usr.'</span>
						 <div id="editaccess_'.$usrid.'" style="padding-top:10px;display:none;">'
							.$this->edit_useracces_form($logged_user_data,$usr,$usrid,$usraccess).'
						</div>
						<div id="editdetails_'.$usrid.'" style="padding-top:10px;display:none;">'
						  .$this->edit_user_form($usr,$usrid,$value,$usrnews).'
						</div>
						<div id="editpass_'.$usrid.'" style="padding-top:10px;display:none;">'
							.$this->edit_userpass_form($usr,$usrid).'
						</div>';
					$details='<span class="rvts8">'.(isset($value['first_name'])?Formatter::strToUpper(str_replace('&quot;','"',ca_functions::ca_un_esc($value['first_name'])))." ":'')
						.(isset($value['surname'])?Formatter::strToUpper(str_replace('&quot;','"',ca_functions::ca_un_esc($value['surname']))):'')
						.F_BR.ca_functions::ca_un_esc($value['email'])."</span>";

					$details=User::getAvatarFromData($value,$db,$value['username'],$this->ca->ca_site_url,'right').$details;

					$sv_eac='sv(\'editaccess_'.$usrid.'\');';
					$svc_eat='svc(\'editaccess_'.$usrid.'\');';
					$sv_edet='sv(\'editdetails_'.$usrid.'\');';
					$svc_edet='svc(\'editdetails_'.$usrid.'\');';
					$sv_epas='sv(\'editpass_'.$usrid.'\');';
					$svc_epas='svc(\'editpass_'.$usrid.'\');';

					$access='';
					$unique=array();
					$range=$otheruser_is_admin=false;
					foreach($usraccess as $v)
					{
						if(!isset($unique[$v['section']]))
						{
							$unique[$v['section']]=$v;
							if($v['section']=='ALL')
							{
								$access.='<span class="rvts8">'.$this->get_access_typelabel($v['access_type']).'</span>';
								$otheruser_is_admin=$v['access_type']=='9';
							}
							else
							{
								$sv_chr='sv(\'check_range_'.$usrid.'_'.$v['section'].'\');';
								$svc_chr='svc(\'check_range_'.$usrid.'_'.$v['section'].'\');';
								$section_name='';
								if(empty($section_name))
									$section_name=$v['section'];

								$href='javascript:void(0);" onclick="'.$sv_chr.$svc_eat.$svc_edet.$svc_epas;
								$access.='
									<span class="rvts8">'.$this->ca->ca_access_type_ex[empty($v['page_id'])?$v['access_type']:'2'].' </span>'.F_BR.'
									<div id="check_range_'.$usrid.'_'.$v['section'].'" style="padding-top:10px;display:none;">'.
										$this->check_section_range(1,$usr,$usraccess).'
									</div>
									<span class="rvts8">[</span><a class="rvts12" href="'.$href.'">'.$this->ca->lang_l('check range').'</a>
									<span class="rvts8">]</span> <br>';
								$range=true;
							}
							break;
						}
					}

					$user_nav=array();
					if((!$user_is_admin||!$otheruser_is_admin)&&empty($value['group_name']))
						$user_nav[$this->ca->lang_l('edit access')]='javascript:void(0);" onclick="'.$sv_eac.$svc_edet.$svc_epas.($range?$svc_chr:'');
					if(!$user_is_admin||$user_id=$value['uid'])
						$user_nav[$this->ca->lang_l('details')]='javascript:void(0);" onclick="'.$svc_eat.$sv_edet.$svc_epas.($range?$svc_chr:'');
					if(!$user_is_admin||$user_id=$value['uid'])
						$user_nav[$this->ca->lang_l('password')]='javascript:void(0);" onclick="'.$svc_eat.$svc_edet.$sv_epas.($range?$svc_chr:'');
					if(!$user_is_admin||!$otheruser_is_admin)
						$user_nav[$this->ca->lang_l('remove')]=$url."&amp;removeuser=".$usrid.'" onclick="javascript:return confirm(\''.$this->ca->lang_l('remove MSG').'\')';

					$active=$value['status']==1;
					$expired=!ca_functions::is_nulldate($value['expired_date']) && Date::tzoneSql($value['expired_date'])<time();
					if($expired)
					{
						$status_value=$this->ca->lang_l('expired');
						$status_link_label=$this->ca->lang_l('block');
						$act='block';
						$style='background:orange;padding:2px;color:white;font-weight:bold';
					}
					elseif($active)
					{
						$status_value=$this->ca->lang_l('active');
						$status_link_label=$this->ca->lang_l('block');
						$act='block';
						$style='';
					}
					else
					{
						$status_value=$value['status']==3?$this->ca->lang_l('temp blocked'):$this->ca->lang_l('blocked');
						$status_link_label=$this->ca->lang_l('activate');
						$act='activate';
						$style='background:red;padding:2px;color:white;font-weight:bold';
					}

					$row_data=array(
						 '<input type="checkbox" class="mng_entry_chck" value="'.$usrid.'" name="user_id[]">',
						 array($userArea,$user_nav),
						 '<span class="rvts8">'.$value['group_name'].'</span>',
						 $details,
						 ($access==''?'<span class="rvts8">No access</span>':$access),
						 array(
							  '<div class="rvts8" style="'.$style.'">'.$status_value.'</div>',
							  array($status_link_label=>$url."&amp;".$act."=".$usrid)
							  )
					);
					$table_data[]=$row_data;
				}
			}
			$appnd='
				<form method="post" action="'.$this->ca->ca_abs_url.'?process=assigntogroup'.'" id="mng_usr_tbl_frm">
					<input type="button" onclick="cloneSelectedToForm(\'removeuser\',\'#\'); return false;" value="'.$this->ca->lang_l('remove').'">
					<input type="button" value=" '.$this->ca->lang_l('users mailing').' " onclick="cloneSelectedToForm(\'user_id\',\''.$this->ca->ca_abs_url.'?process=mail_users\',\'mail_users\');return false;">
					<span class="rvts8" style="width:20px;">&nbsp;</span> '
					 .$this->ca->groups->build_groups(
								$this->ca->lang_l('move selected to'),
								true,
								'<button onclick="cloneSelectedToForm(); return false;" value="submit">'.$this->ca->lang_l('save').'</button>'
					 ).'
				</form>';
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$appnd,'','');
		}
		else
		{
			$table_data[]=array('<span class="rvts8">'.$this->ca->lang_l('none users').'</span>');
			$output.=Builder::adminTable($nav,array(),$table_data);
		}

		$this->output($output);
	}

	public function users_import()
	{
		global $db;

		$bool_fields=array();
		foreach($this->f->ca_users_fields_array as $k=> $v)
			if($v['itype']=="checkbox")
				$bool_fields[]=$k;
		$user_data=$this->ca->user->mGetLoggedUser($db,'');
		$settings=array(
			'script_path'=>$_SERVER['PHP_SELF'],
			'rel_path'=>($this->ca->template_in_root?'':'../'),
			'user_data'=>$user_data,
			'act_up'=>'?process=import',
			'act_imp'=>'?process=import2',
			'act_cancel'=>'?process=users',
			'act_imp_redirect'=>'?process=users',
			'lang_l'=>ca_labels::fetch_labels($this->ca->ca_lang),
			'db_system_fields'=>array('uid','creation_date','expired_date','self_registered','self_registered_id',
					'confirmed','status','pass_changeable'),
			'db_boolean_fields'=>$bool_fields,
			'data_table'=>'ca_users',
			'lg_amp'=>'',
			'module_object'=>$this
		);
		$output=ImportHandler::import($settings,'CA');
		$this->output($output);
	}

	protected function edit_useracces_form($logged_user_data,$username,$usrid,$access_data)
	{
		$user_is_admin=$logged_user_data['user_admin']===1;

		$frmID='ca_frm_editaccess_'.$usrid;
		$output='
			<div style="text-align:left">
				<form id="'.$frmID.'" class="inner_ca_frm" action="'.$this->ca->ca_abs_url."?process=processuser".$this->ca->ca_l_amp.'" method="post">
					 <input type="hidden" name="save" value="save">
					 <input type="hidden" name="flag" value="editaccess">'
					.($usrid>0?'<input type="hidden" name="uid" value="'.$usrid.'">':'').
					'<input type="hidden" name="username" value="'.$username.'">'
					.$this->build_access_line($user_is_admin,false,$usrid,$access_data).'
					<span id="'.$frmID.'_error" class="rvts12 frmhint"></span>'
					.$this->form_buttons('save',true,'onclick="'.(($usrid>0)?'sv(\'editaccess_'.$usrid.'\');':'document.location=\''.$this->ca->ca_abs_url."?process=users".$this->ca->ca_l_amp.'\'').'""').'
				</form>
			</div>';
		return $output;
	}

	protected function edit_userpass_form($username,$usrid)
	{
		$frmID='ca_frm_editpass_'.$usrid;
		$output=($usrid>0?'<input type="hidden" name="uid" value="'.$usrid.'">':'');
		$output.='<input type="hidden" name="username" value="'.$username.'">';

		$pass_str_labels=Password::checkStrenght('',$this->ca->thispage_id_inca,1); //this will get the array with the labels only
		$output.=
			'<p><span class="rvts8 a_editcaption">'.$this->ca->lang_l('password').$this->f->fmt_star.'</span></p>
			<input class="input1 passreginput" type="password" name="password" style="width:280px" rel="'.$usrid.'">'
			.Password::showMeter($pass_str_labels,'right','6',$usrid).'
			<span class="rvts12 frmhint" id="'.$frmID.'_password"></span>
			<p><span class="rvts8 a_editcaption">'.$this->ca->lang_l('repeat password').$this->f->fmt_star.'</span></p>
			<input class="input1 reppassinput" type="password" name="repeatedpassword" style="width:280px" rel="">
			<span class="rvts12 frmhint" id="'.$frmID.'_repeatedpassword"></span>';

		$output='
			<div style="text-align:left">
				<form id="'.$frmID.'" class="inner_ca_frm" action="'.$this->ca->ca_abs_url."?process=processuser".$this->ca->ca_l_amp.'" method="post">
					<input type="hidden" name="save" value="save">
					<input type="hidden" name="flag" value="editpass">'
					 .$output.'
					<span id="'.$frmID.'_error" class="rvts12 frmhint"></span>'
					.$this->form_buttons('save',true,'onclick="'.(($usrid>0)?'sv(\'editpass_'.$usrid.'\');':'document.location=\''.$this->ca->ca_abs_url."?process=users".$this->ca->ca_l_amp.'\'').'""').'
				</form>
			</div>';
		return $output;
	}

	protected function edit_user_form($username,$usrid,$data,$news_data)
	{
		$input='<input class="input1" type="text" name="%s" id="%s" value="%s" style="width:%spx">';
		$span8='<p><span class="rvts8 a_editcaption">%s</span></p>';

		$frmID='ca_frm_editdetails_'.$usrid;
		$output='';

		if($usrid>0)
			$output.='<input type="hidden" name="uid" value="'.$usrid.'">';

		$output.='<input type="hidden" name="old_username" value="'.$username.'">'.sprintf($input,'username','username',$username,'280')
				.'<span class="rvts12 frmhint" id="'.$frmID.'_username"></span>';
		$output.=sprintf($span8,$this->ca->lang_l('email')).
				sprintf($input,'email','email',($data!=''?$data['email']:(isset($_POST['save'])?$_POST['email']:'')),'280')
				.'<span class="rvts12 frmhint" id="'.$frmID.'_email"></span>';

		foreach($this->f->ca_users_fields_array as $k=> $v)
		{
			$dname=$this->ca->lang_l($k);
			if($data=='')
				$value=isset($_POST['save'])&&isset($_POST[$k])?ca_functions::ca_un_esc($_POST[$k]):'';
			else
				$value=($data!==''&&isset($data[$k]))?ca_functions::ca_un_esc($data[$k]):'';

			if($v['itype']=="userinput")
				$output.=sprintf($span8,$dname).sprintf($input,$k,$k,$value,'280');
			elseif($v['itype']=="checkbox")
				$output.='<p><input type="checkbox" '
						.($value=='1'?'checked="checked"':'').' name="'.$k.'" value="1"> <span class="rvts8 a_editcaption">'.$dname.'</span></p>';
			elseif($v['itype']=="avatar")
			{
				$av_path=$this->ca->getAvatarPath($value);
				$output.='
						<p><span class="rvts8 a_editcaption">'.$dname.'</span></p>
						<input class="input1" type="text" name="'.$k.'" id="'.$k.$usrid.'" value="'.$av_path.'" style="width:206px">
						<input type="button" value="'.$this->ca->lang_l('browse').'" onclick="openAsset(\''.$k.$usrid.'\')" name="btnAsset2" class="input1"><br>
						<img id="ima_'.$dname.$usrid.'" src="'.$av_path.'" alt="" style="'.(($av_path=='')?'display:none;':'').'height:'.$this->f->avatar_size.'px;padding-top: 5px;">';
			}
			elseif($v['itype']=="listbox")
			{
				$dt=explode(';',$v['values']);
				$output.=sprintf($span8,$dname).Builder::buildSelect($k,$dt,$value,'style="width:282px"','value');
			}
			elseif($v['itype']=="memo")
				$output.=sprintf($span8,$dname).'<textarea class="input1" name="'.$k.'" value="'.$value.'" style="width:280px"></textarea>';
			$output.='<span class="rvts12 frmhint" id="'.$frmID.'_'.$k.'"></span>';
		}

		if($data!='')
		{
			$creation_date=$data['creation_date'];
			$output.=CA::formatNotice($this->ca->lang_l('creation date').': '.($creation_date!=''?date('r',Date::tzoneSql($creation_date)):'NA'),true);
		}

		$calendar_categories=$this->ca->get_calendar_categories($username);

		if(!empty($calendar_categories))
		{
			$news_for=array();
			if(!empty($news_data))
			{
				foreach($news_data as $val)
					$news_for[]=$val['page_id'].'%'.$val['category'];
			}

			$output.='<br>
					<fieldset style="padding:3px;width:270px;">
						<legend>'.sprintf($span8,$this->ca->lang_l('want to receive notification')).'</legend><br>';
			foreach($calendar_categories as $k=> $v)
			{
				$ckbox_value=$v['pageid'].'%'.$v['catid'];
				$output.='<input type="checkbox" name="news_for[]" value="'.$ckbox_value.'" '.
						(in_array($ckbox_value,$news_for)?'checked="checked" ':'').'> <span class="rvts8">'.$v['pagename'].' - '.$v['catname'].'</span>'.F_BR;
			}
			$output.='<br></fieldset>';
		}

		$is_nulldate=ca_functions::is_nulldate($data['expired_date']);
		$expDate=$is_nulldate?strtotime(date("Y-m-d H:i:s").' + 1 year'):Date::tzoneSql($data['expired_date']);
		$cd=Builder::dateTimeInput2('expired_date_'.$usrid,'expired_date',$expDate,'24',$this->f->month_names,'',true,false);
		$this->dp_array[]='expired_date_'.$usrid;

		$output.='<br>
						<input type="checkbox" name="expire" onclick="if($(\'.date_unpub\').is(\':visible\'))$(\'.date_unpub\').hide();else $(\'.date_unpub\').css(\'\display\',\'inline-block\');" '.(!$is_nulldate?'checked="checked"':'').'  style="height: 27px;" />
						<span class="rvts8 a_editcaption">'.$this->ca->lang_l('expire').'</span>
						<div class="date_unpub" style="display:'.($is_nulldate?'none':'inline-block').'">'.$cd.'</div>
			<p><input type="checkbox" '.($data['pass_changeable']?'checked="checked"':'').' name="pass_changeable" value="1">  <span class="rvts8 a_editcaption"> '.$this->ca->lang_l('pass changeable').'</span></p>';

		$output='
			<div style="text-align:left">
				<form id="'.$frmID.'" class="inner_ca_frm" action="'.$this->ca->ca_abs_url."?process=processuser".$this->ca->ca_l_amp.'" method="post">
					 <input type="hidden" name="save" value="save">
					 <input type="hidden" name="flag" value="editdetails">
					 <p><span class="rvts8 a_editcaption">'.$this->ca->lang_l('username').$this->f->fmt_star.'</span></p>'
					.$output.'
					<span id="'.$frmID.'_error" class="rvts12 frmhint"></span>'
					.$this->form_buttons('save',true,'onclick="'.(($usrid>0)?'sv(\'editdetails_'.$usrid.'\');':'document.location=\''.$this->ca->ca_abs_url."?process=users".$this->ca->ca_l_amp.'\'').'""').'
				</form>
			</div>';
		return $output;
	}

	protected function add_user_form($msg,$logged_user_data) //admin add user
	{
		$saving=isset($_POST['save']);
		$username=$saving?ca_functions::ca_un_esc($_POST['username']):'';
		$user_is_admin=$logged_user_data['user_admin']===1;

		$input='<input class="input1" type="text" name="%s" id="%s" value="%s" style="width:%spx">';
		$input_ps='<input class="input1 %s" type="password" name="%s" autocomplete="off" rel="" style="width:280px"><br>';

		$table_data=array();
		$table_data[]=array($this->ca->lang_l('username').$this->f->fmt_star,
			'<input type="hidden" name="flag" value="add">
			<input type="hidden" name="old_username" value="'.$username.'">'.
			sprintf($input,'username','username',$username,'280').
			'<span id="add_usr_ca_frm_username" class="rvts12 frmhint"></span>');
		$table_data[]=array($this->ca->lang_l('email'),
			 sprintf($input,'email','email',($saving)?$_POST['email']:'','280').
			 '<span id="add_usr_ca_frm_email" class="rvts12 frmhint"></span>');

		foreach($this->f->ca_users_fields_array as $k=> $v)
		{
			$dname=$this->ca->lang_l($k);
			$value=$saving&&isset($_POST[$k])?Formatter::stripTags($_POST[$k]):'';
			if($v['itype']=="userinput")
				$table_data[]=array($dname,sprintf($input,$k,$k,($saving)?ca_functions::ca_un_esc($_POST[$k]):'','280'));
			elseif($v['itype']=="checkbox")
				$table_data[]=array('','<input type="checkbox" name="'.$k.'" value="1"> <span class="rvts8 a_editcaption">'.$dname.'</span>');
			elseif($v['itype']=="avatar")
			{
				$av_path=$this->ca->getAvatarPath($value);

				$x=sprintf($input,$k,$k,$av_path,'206').' <input type="button" value="'.$this->ca->lang_l('browse').'" onclick="openAsset(\''.$k.'\')" name="btnAsset2" class="input1">
					<br><img id="ima_'.$k.'" src="'.$av_path.'" alt="" style="'.(($av_path=='')?'display:none;':'').'height:'.$this->f->avatar_size.'px;padding-top: 5px;">';
				$table_data[]=array($dname,$x);
			}
			elseif($v['itype']=="listbox")
			{
				$data=explode(';',$v['values']);
				$table_data[]=array($dname,Builder::buildSelect($k,$data,'','style="width:282px"','value'));
			}
		}
		$pass_str_labels=Password::checkStrenght('',$this->ca->thispage_id_inca,1); //this will get the array with the labels only
		$table_data[]=array($this->ca->lang_l('password').$this->f->fmt_star,
			 sprintf($input_ps,'passreginput','password').Password::showMeter($pass_str_labels).
			 '<span id="add_usr_ca_frm_password" class="rvts12 frmhint"></span>');
		$table_data[]=array($this->ca->lang_l('repeat password').$this->f->fmt_star,
			 sprintf($input_ps,'reppassinput','repeatedpassword').
			 '<span id="add_usr_ca_frm_repeatedpassword" class="rvts12 frmhint"></span>');

		// sections and access
		$access_line=$this->build_access_line($user_is_admin,$this->build_extra_access_option(-1,false));
		$table_data[]=array($this->ca->lang_l('access to'),$access_line);

		// event manager
		$news_line='';
		$calendar_categories=$this->ca->get_calendar_categories(false);
		if(!empty($calendar_categories))
		{
			$news_for=array();
			if(isset($data['news'])&&!empty($data['news']))
			{
				foreach($data['news'] as $val)
					$news_for[]=$val['page_id'].'%'.$val['category'];
			}
			$news_line.=F_BR;
			foreach($calendar_categories as $k=> $v)
			{
				$ckbox_value=$v['pageid'].'%'.$v['catid'];
				$news_line.='<input type="checkbox" name="news_for[]" value="'.$ckbox_value.'" '.
					(in_array($ckbox_value,$news_for)?'checked="checked" ':'').'> <span class="rvts8">'.$v['pagename'].' - '.$v['catname'].'</span>'.F_BR;
			}
		}
		if(!empty($news_line))
			$table_data[]=array($this->ca->lang_l('want to receive notification'),$news_line);

		$table_data[]='<span class="rvts8">('.$this->f->fmt_star.') '.$this->ca->lang_l('required fields').'</span>';

		$expDate=strtotime(date("Y-m-d H:i:s").' + 1 year');
		$cd=Builder::dateTimeInput2('expired_date','expired_date',$expDate,'24',$this->f->month_names,'',true,false);
		$this->dp_array[]='expired_date';

		$table_data[]='<input type="checkbox" name="expire" onclick="if($(\'.date_unpub\').is(\':visible\'))$(\'.date_unpub\').hide();else $(\'.date_unpub\').css(\'\display\',\'inline-block\');" style="height: 27px;" />
						<span class="rvts8 a_editcaption">'.$this->ca->lang_l('expire').'</span>
						<div class="date_unpub" style="display:none">'.$cd.'</div';
		$table_data[]='<p><input type="checkbox" checked="checked" name="pass_changeable" value="1">  <span class="rvts8 a_editcaption"> '.$this->ca->lang_l('pass changeable').'</span></p>';

		$end=$this->form_buttons('save',true,'onclick="document.location=\''.$this->ca->ca_abs_url."?process=users".$this->ca->ca_l_amp.'\'"');
		$form='
			 <form id="add_usr_ca_frm" class="inner_ca_frm" action="'.$this->ca->ca_abs_url."?process=processuser".$this->ca->ca_l_amp.'" method="post">
				<div style="text-align:left;">'.($msg!=''?$msg.F_BR:'');
		$output=Builder::addEntryTable($table_data,$end,'','',false,'','',$form,'</div></form>');
		$this->ca->ca_scripts='$(document).ready(function(){$(".inner_ca_frm").utils_frmvalidate(1,0,0,0,0,0,0);});';

		return $output;
	}

	protected function confirm_pending_user($sr_id)
	{
		global $db;

		$user_info=User::mGetUser($sr_id,$db,'self_registered_id',false,false);
		$user_id=$user_info['uid'];
		if($user_id!='')
		{
			$db->query_update('ca_users',array('confirmed'=>1,'status'=>1),' uid='.$user_id);

			$link=$this->ca->ca_abs_url.'?id='.$user_id.'&process=register'.$this->ca->ca_l;
			$more_macros=array('%confirmlink%'=>'<a href="'.$link.'">'.$link.'</a>');

			$content=Formatter::parseMailMacros($this->ca->lang_l('sr_activated_msg'),$user_info,$more_macros);
			$subject=Formatter::parseMailMacros($this->ca->lang_l('sr_activated_subject'),$user_info,$more_macros);
			MailHandler::sendMailCA($db,$content,$subject,$user_info['email']);
		}
	}

	protected function resend_conf_mail($sr_id)
	{
		global $db;

		$user_info=User::mGetUser($sr_id,$db,'self_registered_id',false,false);

		$link=$this->ca->ca_abs_url.'?id='.$sr_id.'&process=register'.$this->ca->ca_l;
		$more_macros=array('%confirmlink%'=>'<a href="'.$link.'">'.$link.'</a>');

		$log_msg='success';

		$content=Formatter::parseMailMacros($this->ca->lang_l('sr_email_msg'),$user_info,$more_macros);
		$subject=Formatter::parseMailMacros($this->ca->lang_l('sr_email_subject'),$user_info,$more_macros);
		$result=MailHandler::sendMailCA($db,$content,$subject,$user_info["email"]);

		if($result=="1")
		{
			$log_msg.=", email SENT";
			$msg=F_BR.$this->ca->lang_l('email resent').' '.Formatter::strToUpper($user_info['username']);
		}
		else
		{
			$log_msg='fail, email FAILED ('.Formatter::stripTags($result).')';
			$msg='Email FAILED. Try again.';
		}
		ca_log::write_log('resend',$user_info['uid'],$user_info['username'],$log_msg);
	}

	public function pending_users($msg='')
	{
		global $db;

		if(isset($_REQUEST['removeuser']))   // REMOVE USER
		{
			$user_id=$_REQUEST['removeuser'];
			ca_db::remove_user($user_id);
			$msg=F_BR.$this->ca->lang_l('user removed');
		}
		elseif(isset($_REQUEST['resend']))   // RE_SEND CONFIRMATION EMAIL TO USER
		{
			$sr_id=$_REQUEST['resend'];
			if(is_array($sr_id))
			{
				foreach($sr_id as $sid)
				{
					$s_id=explode('|',$sid);
					if(isset($s_id[1]))
						$this->resend_conf_mail($s_id[1]);
				}
			}
			else
				$this->resend_conf_mail(strip_tags($sr_id));
		}
		elseif(isset($_REQUEST['confirm']))
		{
			$sr_id=$_REQUEST['confirm'];
			if(is_array($sr_id))
			{
				foreach($sr_id as $sid)
				{
					$s_id=explode('|',$sid);
					if(isset($s_id[1]))
						$this->confirm_pending_user($s_id[1]);
				}
			}
			else
				$this->confirm_pending_user(strip_tags($sr_id));
		}

		$users_array=User::mGetAllUsers($db,'confirmed <> 1');
		if(!empty($users_array))
		{
			$users_list=array();
			foreach($users_array as $k=> $v)
				$users_list[]=$v['uid'];
			$where='user_id IN ('.implode(',',$users_list).')';
			$users_access=$this->ca->user->mGetUserAccess($db,$where);
		}

		$output=($msg!=''?'<span class="rvts8">'.$msg.'</span>'.F_BR.F_BR:'');
		if(!empty($users_array))
		{
			$cap_arrays=array('<input type="checkbox" onclick="$(\'.mng_entry_chck.u\').prop(\'checked\',$(this).is(\':checked\'));">
				<input type="checkbox" onclick="$(\'.mng_entry_chck.c\').prop(\'checked\',$(this).is(\':checked\'));">',
				$this->ca->lang_l('user'),$this->ca->lang_l('details'),$this->ca->lang_l('access to'),$this->ca->lang_l('status'));
			$table_data=array();
			$url=$this->ca->ca_abs_url."?process=";
			foreach($users_array as $value)
			{
				if(!empty($value))
				{
					$usr=$value['username'];
					$usrid=$value['uid'];
					$confirmed=$value['confirmed']==2; //confirmed via mail
					if($confirmed)
					{
						$status_value=$this->ca->lang_l('confirmed');
						$xclass='c';
						$status_link_label=$this->ca->lang_l('activate');
					}
					else
					{
						$status_value=$this->ca->lang_l('unconfirmed');
						$xclass='u';
						$status_link_label=$this->ca->lang_l('confirm');
					}
					$full_name=(isset($value['first_name'])?ca_functions::ca_un_esc($value['first_name'])." ":'').(isset($value['surname'])?ca_functions::ca_un_esc($value['surname']):'');
					$userArea='<span class="rvts8">'.$usr.'</span>';
					$user_nav=array($status_link_label=>$url."pendingreg&amp;confirm=".$value['self_registered_id'].$this->ca->ca_l_amp);
					if(!$confirmed)
						$user_nav[$this->ca->lang_l('resend')]=$url."pendingreg&amp;resend=".$value['self_registered_id'].$this->ca->ca_l_amp.'" onclick="javascript:return confirm(\''.$this->ca->lang_l('resend MSG').' '.Formatter::strToUpper($usr)." - ".$full_name.'?\')';
					$user_nav[$this->ca->lang_l('remove')]=$url."pendingreg&amp;removeuser=".$usrid.$this->ca->ca_l_amp.'" onclick="javascript:return confirm(\''.$this->ca->lang_l('remove MSG').'\')';
					$details='<span class="rvts8">'.Formatter::strToUpper($full_name).F_BR.$value['email']."</span>";

					$access='<span class="rvts8">';

					if(!isset($users_access[$usr]))
						$access.=$this->ca->lang_l('view all').'</span>';
					else
					{
						foreach($value['access'] as $k=> $v) //ALL-write
						{
							if($v['section']=='ALL')
								$access.=($v['access_type']=='0'?$this->ca->lang_l('view all'):$this->ca->lang_l('edit all')).'</span>';
							else
							{
								$section_name='';
								if(empty($section_name))
									$section_name=$v['section'];
								$access.=$section_name.' ('.$this->ca->ca_access_type_ex[$v['access_type']].')</span>';
							}
						}
					}
					$inp_chck='<input type="checkbox" class="mng_entry_chck '.$xclass.'" '.($xclass=='c'?'style="margin-left:27px"':'').'value="'.$usrid.'|'.$value['self_registered_id'].'" name="user_id[]">';

					$status='<div class="rvts8"'.($confirmed?'':' style="background:red;padding:2px;color:white;font-weight:bold"').'>'.$status_value.'</div>';

					$row_data=array($inp_chck,array($userArea,$user_nav),$details,$access,$status);
					$table_data[]=$row_data;
				}
			}
			$appnd_script='
				<button onclick="cloneSelectedToForm(\'confirm\'); return false;" value="submit">'.$this->ca->lang_l('confirm').'</button>
				<button onclick="cloneSelectedToForm(\'resend\'); return false;" value="submit">'.$this->ca->lang_l('resend').'</button>
				<button onclick="cloneSelectedToForm(\'removeuser\'); return false;" value="submit">'.$this->ca->lang_l('remove').'</button>';
			$appnd='<form method="post" action="'.$this->ca->ca_abs_url.'?process=pendingreg'.'" id="mng_usr_tbl_frm">'.$appnd_script.'</form>';
			$output.=Builder::adminTable('',$cap_arrays,$table_data,$appnd,'','');
		}
		else
		{
			$table_data[]=array('<span class="rvts8">'.$this->ca->lang_l('none users').'</span>');
			$output.=Builder::adminTable('',array(),$table_data);
		}
		$this->output($output);
	}

	public function process_users($logged_user_data)  //process add/edit/remove user
	{
		global $db;

		if(isset($_GET['q']))
		{
			$this->manage_users($logged_user_data);
			exit;
		}

		$lang_f=$this->f->lang_f[$this->ca->ca_ulang_id];
		$output='';
		$access_data=$news_data=$errors=array();
		$ccheck=isset($_POST['cc'])&&$_POST['cc']=='1';
		$flag=isset($_POST['flag'])?$_POST['flag']:''; //action flag - add, editdetails, editpass, editaccess

		if($flag!='')
		{
			$userid=(isset($_POST['uid'])?$_POST['uid']:0);

			if($flag=='add'&&!preg_match("/^[A-Za-z_.@0-9-]+$/",$_POST['username']))
				$errors[]=($ccheck?'username|':'').$this->ca->lang_l('can contain only');
			if($flag=='add'||$flag=='editdetails')
			{
				if(empty($_POST['username']))
					$errors[]=($ccheck?'username|':'').$this->ca->lang_l('fill in').' '.$this->ca->lang_l('username');
				elseif((strtolower($_POST['username'])!=strtolower($_POST['old_username']))&&$this->ca->users->duplicated_user_check($_POST['username']))
					$errors[]=($ccheck?'username|':'').$this->ca->lang_l('username exists');
				elseif(!empty($_POST["email"]))
				{
					if(!Validator::validateEmail($_POST["email"]))
						$errors[]=($ccheck?'email|':'').$this->ca->lang_l('nonvalid email');
					elseif($this->ca->users->duplicated_email_check($_POST['email'],$userid))
						$errors[]=($ccheck?'email|':'').$this->ca->lang_l('email in use');
				}
			}
			if($flag=='add'||$flag=='editpass')
			{
				if(empty($_POST['password']))
					$errors[]=($ccheck?'password|':'').$this->ca->lang_l('fill in').' '.$this->ca->lang_l('password');
				elseif(empty($_POST['repeatedpassword']))
					$errors[]=($ccheck?'repeatedpassword|':'').$this->ca->lang_l('repeat password');
				elseif($_POST['password']!=$_POST['repeatedpassword'])
					$errors[]=($ccheck?'repeatedpassword|':'').$this->ca->lang_l('password and repeated password');
				elseif(strlen(trim($_POST['password']))<5)
					$errors[]=($ccheck?'password|':'').$this->ca->lang_l('your password should be');
				elseif(strtolower($_POST['username'])==strtolower($_POST['password']))
					$errors[]=($ccheck?'username|':'').$this->ca->lang_l('username equal password');
			}
			if(!empty($errors))
				$errors[]=($ccheck?'error|':'').$lang_f['validation failed'];
			if($ccheck)
			{
				$errors_output=implode('|',$errors);
				$useic=(!$this->f->uni&&$this->f->charset_lang_map[$this->ca->ca_lang]!='iso-8859-1'&&function_exists("iconv"));
				if($useic)
					$errors_output=iconv($this->f->charset_lang_map[$this->ca->ca_lang],"utf-8",$errors_output);

				if(count($errors)>0)
					print '0'.$errors_output;
				else
					print '1';
				exit;
			}

			$user_id=isset($_POST["uid"])?intval($_POST["uid"]):'';

			$user_data=$this->ca->users->get_form_fields(true);

			if($flag=='add' || $flag=='editdetails')
			{
				if(isset($_REQUEST['expire']))
					 $user_data['expired_date']=Date::buildMysqlTime(Date::pareseInputDate('expired_date','24',$this->f->month_names));
				else
					 $user_data['expired_date']=NULL;
			}

			if($flag=='add')
			{
				$user_data['creation_date']=Date::buildMysqlTime();
				$user_id=$db->query_insert('ca_users',$user_data);
				if($user_id!==false)
				{
					$access_data=$this->ca->users->build_access_array($user_id);
					$news_data=$this->ca->users->build_news_array($user_id);
					foreach($access_data as $k=> $acc)
						$db->query_insert('ca_users_access',$acc);
					foreach($news_data as $k=> $news)
						$db->query_insert('ca_users_news',$news);
				}
			}
			elseif($flag=='editpass')
				$this->ca->users->set_password($user_id,$_POST['password']);
			elseif($flag=='editaccess')
				$this->ca->users->edit_user_acccess($user_id);
			elseif($flag=='editdetails')
				$this->ca->users->edit_details($user_id,$user_data);

			$this->manage_users($logged_user_data);
			exit;
		}
		elseif(isset($_GET['removeuser'])) // REMOVE USER
		{
			$this->ca->users->remove_user(intval($_GET['removeuser']));
			$this->manage_users($logged_user_data);
			exit;
		}
		elseif(isset($_GET['activate'])||isset($_GET['block'])) // CHANGE STATUS
		{
			$user_id=Formatter::stripTags(isset($_GET['activate'])?$_GET['activate']:$_GET['block']);
			$this->ca->users->change_status($user_id,isset($_GET['activate'])?1:0);
			$user_data=User::getUser($user_id,$this->ca->ca_prefix,'',$user_id);
			if(!empty($user_data['email']))
			{
				$content=(isset($_GET['activate']))?$this->ca->lang_l('sr_activated_msg'):$this->ca->lang_l('sr_blocked_msg');
				$subject=(isset($_GET['activate']))?$this->ca->lang_l('sr_activated_subject'):$this->ca->lang_l('sr_blocked_subject');

				$content=Formatter::parseMailMacros($content,$user_data,NULL);
				$subject=str_replace(array('%%site%%','%site%'),$this->ca->ca_site_url,$subject);
				MailHandler::sendMailCA($db,$content,$subject,$user_data['email']);
			}
			$this->manage_users($logged_user_data);
			exit;
		}
		else
			$output.=$this->add_user_form('',$logged_user_data);

		$this->output($output,$flag=='add'?' - '.$this->ca->lang_l('add user'):'');
	}

	public function mail_users()
	{
		global $db;

		//these can be used to pre-define mail for users in future
		$subject=isset($_REQUEST['subject'])?$_REQUEST['subject']:'';
		$msg=isset($_REQUEST['message'])?$_REQUEST['message']:'';

		$predef_users=array();

		if(isset($_REQUEST['grp_id'])&&(int)$_REQUEST['grp_id']>0)
		{
			$usrs=$db->fetch_all_array('
				SELECT u.uid,u.email
				FROM '.$db->pre.'ca_users AS u
				INNER JOIN '.$db->pre.'ca_users_groups_links AS l ON l.user_id=u.uid AND l.group_id='.(int)$_REQUEST['grp_id']);
			foreach($usrs as $usr)
				$predef_users[$usr['uid']]=$usr['email'];
		}
		elseif(isset($_REQUEST['user_id']))
		{
			$usrs=array();
			foreach($_REQUEST['user_id'] as $v)
				$usrs[]=intval($v);

			$usrs=$db->fetch_all_array('
				SELECT uid,email
				FROM '.$db->pre.'ca_users
				WHERE uid IN ('.implode(',',$usrs).')');

			foreach($usrs as $usr)
				$predef_users[$usr['uid']]=$usr['email'];
		}
		$msg=str_replace('src="innovaeditor/','src="../innovaeditor/',$msg);
		$innova_def=$innova_js='';
		Editor::getEditor('english',$this->ca->ca_site_url,false,'',$innova_def,$innova_js,0,'en',$this->ca->ca_site_url);
		$settings=array(
			'lang_l'=>$this->ca->ca_lang_l,
			'mailData'=>array('content'=>$msg,'subject'=>$subject),
			'innova_def'=>$innova_def,
			'page_id'=>0,
			'page_encoding'=>'',
			'from_email'=>'',
			'print_output'=>false,
			'predefinedRecipients'=>$predef_users
		);
		list($output,$mailer_js)=MailHandler::mailer($settings,'CA');
		$this->ca->ca_scripts=(isset($mailer_js)&&$mailer_js!='')?$mailer_js:'';
		$this->ca->ca_dependencies[]=$innova_js;
		$this->output($output);
	}

	public function manage_users_export($as_pdf=false,$paper='letter',$orientation='portrait')
	{
		global $db;

		$output='';
		$users_array=User::mGetAllUsers($db);
		$lf="\r\n";
		if($as_pdf)
			$output.='<head><meta http-equiv="content-type" content="text/html; charset=utf8">
				<style type="text/css">
					 table{font:12px arial;}
					 .t_head {font-weight:bold;}
					 .even{background:#fff;}
					 .odd{background:#f5f5f5;}
					 .td{border-bottom:1px solid white;border-right:1px solid white;}
				</style>
				</head>
				<body>
				<table>';
		if(!empty($users_array))
		{
			$delim=$as_pdf?'':',';
			$fld_s=$as_pdf?'<td>':'"';
			$fld_e=$as_pdf?'</td>':'"';
			if($as_pdf)
				$output.='<tr class="t_head">';
			if($as_pdf)
				$field_names=array($this->ca->lang_l('username'),$this->ca->lang_l('email'));
			else
				$field_names=array('username','email','creation_date','self_registered','status','confirmed');

			foreach($this->f->ca_users_fields_array as $k=> $v)
			{
				if(in_array($v['itype'],$this->ca->ca_user_fieldtypes))
					if(!$as_pdf||($as_pdf&&$v['itype']!='avatar'))
						$field_names[]=$k;
			}
			foreach($field_names as $k=> $v)
				$output.=($k==0?'':$delim).$fld_s.Formatter::sth(urldecode($v)).$fld_e;

			if($as_pdf)
				$output.='</tr>';
			$output.=$lf;
			$cnt=1;
			foreach($users_array as $value)
			{
				if($as_pdf)
					$output.='<tr class="'.($cnt++%2==0?'even':'odd').'">';

				$output.=$fld_s.Formatter::sth(urldecode($value['username'])).$fld_e;
				$output.=$delim.$fld_s.Formatter::sth(urldecode($value['email'])).$fld_e;
				$output.=$as_pdf?'':$delim.$fld_s.$value['creation_date'].$fld_e;
				$output.=$as_pdf?'':$delim.$fld_s.($value['self_registered']==1?'Yes':'No').$fld_e;
				$output.=$as_pdf?'':$delim.$fld_s.($value['status']==1?'Active':$value['status']==2?'Blocked':'Temp blocked').$fld_e;
				$output.=$as_pdf?'':$delim.$fld_s.($value['confirmed']==1?'Yes':'No').$fld_e;
				foreach($this->f->ca_users_fields_array as $k=> $v)
				{
					if($v['itype']=="checkbox")
						$output.=$delim.$fld_s.($value[$k]==1?'Yes':'No').$fld_e;
					elseif($v['itype']=="userinput"||$v['itype']=="listbox")
						$output.=$delim.$fld_s.ca_functions::ca_un_esc(urldecode($value[$k])).$fld_e;
					elseif($v['itype']=='avatar'&&!$as_pdf)
						$output.=$delim.$fld_s.Formatter::sth(urldecode($value['avatar'])).$fld_e;
				}
				if($as_pdf)
					$output.='</tr>';
				$output.=$lf;
			}
			if($as_pdf)
				$output.='</table></body></html>';
		}
		if($as_pdf)
		{
			$output=$this->generate_export_pdf($output,$paper,$orientation);
			output_generator::sendFileHeaders('users_export.pdf');
		}
		else
			output_generator::sendFileHeaders('users_export.csv');
		print $output;
		exit;
	}

	protected function generate_export_pdf($pdfc,$paper='letter',$orientation='portrait')
	{
		if($pdfc=='')
			return '';
		return output_generator::generate_pdf($pdfc,'users_export.html','users_export.pdf','../',true,$paper,$orientation);
	}
}

class ca_log_screen extends ca_admin_screens
{
	public $script_path = "documents/centraladmin.php?process=log_errors";

	public function handle_screen()
	{
		if($this->ca->ca_action_id=="log")
			$this->show_log();
		elseif($this->ca->ca_action_id=="log_errors")
			$this->actionLogErrors();
	}

	protected function clear_error_logs()
	{
		global $db;

		if(isset($_GET['s']))
		{
			$data=$db->query_first('
				SELECT *
				FROM '.$db->pre.'user_errors
				WHERE id = ' .intval($_GET['eid']));
			if(empty($data)) return;

			$que='DELETE FROM '.$db->pre.'user_errors
				WHERE err_desc = "' .$data['err_desc'].'"
				AND err_line = ' .$data['err_line'];
		}
		else
			$que='
				DELETE FROM '.$db->pre.'user_errors
				WHERE id = ' .intval($_GET['eid']);

		$db->query($que);
	}

	protected function actionLogErrors()
	{
		global $db;
		if(isset($_GET['clear']))
			$this->clear_error_logs();
		if (isset($_GET['clean']))
			ca_log::clear_errors();

		$output = '';
		$max = Navigation::recordsPerPage();
		$total_count = ca_db::db_count('user_errors');
		$empty_set = true;

		if ($total_count > 0)
		{
			$c_page = (isset($_GET['page']) ? intval($_GET['page']) : 1);
			$orderby = isset($_GET['order']) ? $_GET['order'] : 'd';
			$order_desc = isset($_GET['desc']) ? $_GET['desc'] : 'd';
			$search_filter = isset($_REQUEST['err_file']) ? $db->escape($_REQUEST['err_file']) : '';
			switch ($orderby) {
				case 't': $orderby = 'err_type';
					break;
				case 'l': $orderby = 'err_level';
					break;
				case 'ln': $orderby  = 'err_line';
					break;
				case 'ds': $orderby = 'err_desc';
					break;
				case 'fl': $orderby = 'err_file';
					break;
				case 'y':
					default: $orderby = 'err_date';
			}
			$where = $search_filter == '' ? '' : ' WHERE err_file LIKE "%' . $search_filter . '%"';
			$order_desc = $order_desc == 'a' ? ' ASC ' : ' DESC ';
			$order_desc_opp = $order_desc == ' ASC ' ? 'd' : 'a';
			$rec_count = $db->query_singlevalue('SELECT count(*) FROM ' . $db->pre . 'user_errors' . $where . ' ORDER BY ' . $orderby . $order_desc);
			$show_error_records = $db->fetch_all_array('SELECT * FROM ' . $db->pre . 'user_errors' . $where . ' ORDER BY ' . $orderby .
			        $order_desc . (($rec_count > $max && $max != 0) ? 'LIMIT ' . ($c_page - 1) * $max . ', ' . $max . '' : ''));

			if (!empty($show_error_records))
			{
				$empty_set = false;
				$where = $search_filter != '' ? '&amp;uname_like=' . $search_filter : '';
				$nav = Navigation::pageCA($rec_count,$this->ca->ca_abs_url."?process=log_errors&amp;order=" . substr($orderby, 0, 1) . '&amp;desc=' . strtolower(substr($order_desc, 1, 1)) . $where, 0, $c_page);

				$cur_url = preg_replace('/&order=[a,d,u]{1}/', '', Linker::currentPageUrl());
				$cur_url = preg_replace('/&desc=[a,d]{1}/', '', $cur_url);

				$cap_arrays = array(
					array($cur_url . '&amp;order=y&amp;desc=' . $order_desc_opp . $where, $orderby == 'err_date' ? 'underline' : 'none', 'date'),
					array($cur_url . '&amp;order=fl&amp;desc=' . $order_desc_opp . $where, $orderby == 'err_file' ? 'underline' : 'none', 'file'),
					array($cur_url . '&amp;order=ln&amp;desc=' . $order_desc_opp . $where, $orderby == 'err_line' ? 'underline' : 'none', 'line'),
					array($cur_url . '&amp;order=ds&amp;desc=' . $order_desc_opp . $where, $orderby == 'err_desc' ? 'underline' : 'none', 'desription'),
					array($cur_url . '&amp;order=l&amp;desc=' . $order_desc_opp . $where, $orderby == 'err_level' ? 'underline' : 'none', 'level'),
					array($cur_url . '&amp;order=t&amp;desc=' . $order_desc_opp . $where, $orderby == 'err_type' ? 'underline' : 'none', 'type'),
				);

				$table_data = array();

				foreach($show_error_records as $value)
				{
					if(!empty($value))
					{
						$row_data_err = array(
							array('<span class="rvts8">' . $value['err_date'] . '</span>',
							array('delete'=>$this->script_path.'&amp;clear&amp;eid=' .$value['id']. '&amp;page='.$c_page,
										'delete similar'=>$this->script_path.'&amp;clear&amp;eid=' .$value['id']. '&amp;s=1&amp;page='.$c_page)),
										'<span class="rvts8">'.$value['err_file'].'</span>',
										'<span class="rvts8">'.$value['err_line'].'</span>',
										'<span class="rvts8">'.$value['err_desc'].'<span>',
										'<span class="rvts8">'.$value['err_level'].'</span>',
										'<span class="rvts8">'.$value['err_type'].'</span>',
							);
						$table_data[] = $row_data_err;
					}
				}
				$append = $this->ca->user_is_admin ? '' : '<form method="post" action="'.$this->ca->ca_abs_url.'?process=log_errors&clean' . $this->ca->ca_l_amp . '">'
					. '<input type="submit" value=" ' . $this->ca->lang_l('clear log errors') . ' " onclick="javascript:return confirm(\'' . $this->ca->lang_l('clear log errors MSG') . '\')"></form>';
					$output  = Builder::adminTable($nav,$cap_arrays, $table_data, $append);
			}
}

			if ($empty_set)
			{
				$table_data[] = array('','<span class="rvts8">Empty</span>');
				$output = Builder::addEntryTable($table_data,'');
			}
			$this->ca->ca_scripts = '$(document).ready(function(){assign_edits();});';
			$this->output($output);
	}

	protected function show_log()
	{
		global $db;

		if(isset($_GET['clean']))
			ca_log::clear_log();
		$output='';
		$max=Navigation::recordsPerPage();
		$total_count=ca_db::db_count('ca_log');
		$empty_set=true;
		if($total_count>0)
		{
			$c_page=(isset($_GET['page'])?intval($_GET['page']):1);
			$orderby=isset($_GET['order'])?$_GET['order']:'d';
			$order_desc=isset($_GET['desc'])?$_GET['desc']:'d';
			$search_filter=isset($_REQUEST['uname_like'])?$db->escape($_REQUEST['uname_like']):'';
			switch($orderby)
			{
				case 'u': $orderby='user';
					break;
				case 'a': $orderby='activity';
					break;
				case 'r': $orderby='result';
					break;
				case 'd':
				default: $orderby='date';
					break;
			}
			$where=$search_filter==''?'':' WHERE user LIKE "%'.$search_filter.'%"';
			$order_desc=$order_desc=='a'?' ASC ':' DESC ';
			$order_desc_opp=$order_desc==' ASC '?'d':'a';
			$rec_count=$db->query_singlevalue('
				SELECT count(*)
				FROM '.$db->pre.'ca_log'.$where.'
				ORDER BY '.$orderby.$order_desc);
			$show_records=$db->fetch_all_array('
				SELECT cl.*,cau.avatar,cau.username
				FROM '.$db->pre.'ca_log cl
				LEFT JOIN '.$db->pre.'ca_users AS cau ON cau.uid = cl.uid '
				.$where.'
				ORDER BY '.$orderby.
				$order_desc.(($rec_count>$max&&$max!=0)?'LIMIT '.($c_page-1)*$max.', '.$max.'':''));

			if(!empty($show_records))
			{
				$empty_set=false;
				$where=$search_filter!=''?'&amp;uname_like='.$search_filter:'';
				$nav=Navigation::pageCA($rec_count,$this->ca->ca_abs_url."?process=log&amp;order=".substr($orderby,0,1).'&amp;desc='.strtolower(substr($order_desc,1,1)).$where,0,$c_page);
				$cur_url=preg_replace('/&order=[a,d,u]{1}/','',Linker::currentPageUrl());
				$cur_url=preg_replace('/&desc=[a,d]{1}/','',$cur_url);
				$cur_url=preg_replace('/&uname_like=[a-zA-Z0-9]*/','',$cur_url);
				$uname_filter=Filter::build('uname_like_filter',$search_filter,'document.location=\''
						.$cur_url.'&uname_like=\'+$(\'input[id=uname_like_filter]\').val(); return false;');
				$cap_arrays=array(
					array($cur_url.'&amp;order=d&amp;desc='.$order_desc_opp.$where,$orderby=='date'?'underline':'none',$this->ca->lang_l('date')),
					array($cur_url.'&amp;order=a&amp;desc='.$order_desc_opp.$where,$orderby=='activity'?'underline':'none',$this->ca->lang_l('activity')),
					array($cur_url.'&amp;order=u&amp;desc='.$order_desc_opp.$where,$orderby=='user'?'underline':'none',$this->ca->lang_l('user'),$uname_filter),
					array($cur_url.'&amp;order=r&amp;desc='.$order_desc_opp.$where,$orderby=='result'?'underline':'none',$this->ca->lang_l('result')));

				$table_data=array();
				foreach($show_records as $value)
				{
					if(!empty($value))
					{
						$avatar=User::getAvatarFromData($value,$db,$value['user'],$this->ca->ca_site_url,'right');
						$row_data=array('<span class="rvts8">'.ca_functions::format_output_date(strtotime($value['date'])).'</span>',
							'<span class="rvts8">'.$this->ca->lang_l(strtolower($value['activity'])).'</span>',
							'<span class="rvts8">'.$value['user'].'</span> '.($value['ip']!=''?Builder::ipLocator($value['ip']):'').$avatar,
							'<span class="rvts8">'.$this->ca->lang_l(strtolower($value['result'])).'</span>');
						$table_data[]=$row_data;
					}
				}
				$append=$this->ca->user_is_admin?'':'<form method="post" action="'.$this->ca->ca_abs_url.'?process=log&clean'.$this->ca->ca_l_amp.'">'
					.'<input type="submit" value=" '.$this->ca->lang_l('clear log').' " onclick="javascript:return confirm(\''.$this->ca->lang_l('clear log MSG').'\')"></form>';
				$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$append);
			}
		}

		if($empty_set)
		{
			$table_data[]=array('','<span class="rvts8">Empty</span>');
			$output=Builder::addEntryTable($table_data,'');
		}
		$this->ca->ca_scripts='$(document).ready(function(){assign_edits();});';
		$this->output($output);
	}
}

class ca_maillog_screen extends ca_admin_screens
{
	public function handle_screen($logged_user_data)
	{
		$this->show_maillog($logged_user_data);
	}

	protected function clear_maillog()
	{
		global $db;

		$db->query('DELETE FROM '.$db->pre.'ca_email_data');
	}

	protected function export_csv()
	{
		global $db;

		$lf="\r\n";
		$output='"recipient","sender","subject","message","received"'.$lf;
		$records=$db->fetch_all_array('
			SELECT send_to,msgfrom,subject,message_text,message_html,created
			FROM '.$db->pre.'ca_email_data
			WHERE  success="1"
			ORDER BY created DESC ');

		foreach($records as $v)
		{
			$body=$v['message_text']==''?$v['message_html']:$v['message_text'];
			$body=str_replace(F_LF,F_BR,$body);
			$output.='"'.str_replace('"','""',$v['send_to'])
					  .'","'.str_replace('"','""',$v['msgfrom'])
					  .'","'.str_replace('"','""',$v['subject'])
					  .'","'.str_replace('"','""',$body)
					  .'","'.$v['created'].'"'.$lf;
		}
		output_generator::sendFileHeaders("email_list.csv");
		echo $output; exit;
	}


	protected function show_maillog($logged_user_data)
	{
		global $db;

		if(isset($_GET['clean']))
			$this->clear_maillog();
		if(isset($_GET['export_csv']))
			$this->export_csv();

		$sitemap_list=CA::getSitemap($this->ca->ca_prefix,false,true);
		$output='';
		$cur_url=Linker::currentPageUrl();
		$max=Navigation::recordsPerPage();
		$empty_set=true;

		if(isset($_GET['eid']))
		{
			$rr=$db->query_first('
				SELECT * ,created AS date
				FROM '.$db->pre.'ca_email_data
				WHERE id='.intval($_GET['eid']));
			$res=MailHandler::sendMailStat($db,$rr['page_id'],array($rr['send_to']),$rr['msgfrom'],$rr['message_html'],$rr['message_text'],$rr['subject'],(isset($sitemap_list[$rr['page_id']])?$sitemap_list[$rr['page_id']][17]:$this->f->db_charset),'','','','','',$rr['bcc']);
		}
		elseif(isset($_GET['did']))
			$res=$db->query('
				DELETE
				FROM '.$db->pre.'ca_email_data
				WHERE id='.intval($_GET['did']));

		$total_count=ca_db::db_count('ca_email_data');

		if($total_count>0)
		{
			$c_page=(isset($_GET['page'])?intval($_GET['page']):1);
			$page_filter=isset($_REQUEST['page_like'])?$db->escape($_REQUEST['page_like']):'';
			$rec_filter=isset($_REQUEST['rec_like'])?$db->escape($_REQUEST['rec_like']):'';
			$subj_filter=isset($_REQUEST['subj_like'])?$db->escape($_REQUEST['subj_like']):'';
			$filter_params=array('send_to'=>$rec_filter,'subject'=>$subj_filter);
			$where='';
			Formatter::filterParamsToQuery($where,$filter_params);
			list($orderby,$order_desc)=Filter::orderBy('created','DESC');
			$order_desc_opp=$order_desc=='ASC'?'DESC':'ASC';
			$rec_count=$db->query_singlevalue('
				SELECT count(*)
				FROM '.$db->pre.'ca_email_data '.$where.'
				ORDER BY '.$orderby.' '.$order_desc);
			$allRecords=$db->fetch_all_array('
				SELECT *,created AS date
				FROM '.$db->pre.'ca_email_data '
				.$where.'
				ORDER BY '.$orderby.' '.$order_desc.' '.(($rec_count>$max&&$max!=0)?'
				LIMIT '.($c_page-1)*$max.', '.$max.'':''));

			if(!empty($allRecords))
			{
				$empty_set=false;
				$where=$page_filter!=''?'&amp;page_like='.$page_filter:'';
				$where .= $rec_filter!=''?'&amp;rec_like='.$rec_filter:'';
				$where .= $subj_filter!=''?'&amp;subj_like='.$subj_filter:'';
				$cur_url=preg_replace('/&orderby=(created|page_id|subject|success|send_to)/','',$cur_url);
				$cur_url=preg_replace('/&asc=(ASC|DESC)/','',$cur_url);
				$cur_url=preg_replace('/&page_like=[a-zA-Z0-9]*/','',$cur_url);
				$cur_url=preg_replace('/&rec_like=[a-zA-Z0-9]*/','',$cur_url);
				$cur_url=preg_replace('/&subj_like=[a-zA-Z0-9]*/','',$cur_url);
				$cap_arrays=array(
					array($cur_url.'&amp;orderby=created&amp;asc='.$order_desc_opp.$where,$orderby=='created'?'underline':'none',$this->ca->lang_l('date')),
					array($cur_url.'&amp;orderby=page_id&amp;asc='.$order_desc_opp.$where,$orderby=='page_id'?'underline':'none',$this->ca->lang_l('page'),
						 Filter::build('page_like_filter',$page_filter,'process_edits();')),
					array($cur_url.'&amp;orderby=send_to&amp;asc='.$order_desc_opp.$where,$orderby=='send_to'?'underline':'none',$this->ca->lang_l('recipient/from'),
						 Filter::build('rec_like_filter',$rec_filter,'process_edits();')),
					array($cur_url.'&amp;orderby=subject&amp;asc='.$order_desc_opp.$where,$orderby=='subject'?'underline':'none',$this->ca->lang_l('subject/message/attachments'),
						 Filter::build('subj_like_filter',$subj_filter,'process_edits();')),
					array($cur_url.'&amp;orderby=success&amp;asc='.$order_desc_opp.$where,$orderby=='success'?'underline':'none',$this->ca->lang_l('status')));
				$table_data=array();
				foreach($allRecords as $k=>$value)
				{
					if(!empty($value))
					{
						$p_name=(isset($sitemap_list[$value['page_id']])?$sitemap_list[$value['page_id']][0]:($value['page_id']=='0'?$this->ca->lang_l('administration'):($value['page_id']=='tella'?$this->ca->lang_l('tell a friend'):$value['page_id'])));
						if($page_filter!=''&&stripos($p_name,$page_filter)===false)
						{
							$rec_count--;
							continue;
						} //faster than joining tables to get proper id from page name
						$msg_data=$value['message_text']==''?
								 strip_tags(str_replace(array('<br /></div>','<br />','</div>',F_BR),F_LF,$value['message_html'])):
								 $value['message_text'];
						$msg_data=str_replace(array(F_LF.F_LF.F_LF.F_LF,F_LF.F_LF.F_LF,F_LF.F_LF,F_LF),F_BR,$msg_data);
						$row_data=array();
						$entry='<span class="rvts8">'.ca_functions::format_output_date(strtotime($value['date'])).'</span>';
						$entry_nav=array($this->ca->lang_l('remove')=>$this->ca->ca_abs_url.'?process=maillog&amp;did='.$value['id'].'&amp;page='.$c_page);
						$row_data[]=$this->ca->user_is_admin?$entry:array($entry,$entry_nav);

						$row_data[]='<span class="rvts8">'.$p_name.'</span>';
						//$row_data[]='<div style="word-wrap:break-word;width:150px;">
						$row_data[]='<div>
							<span class="rvts8">'.htmlspecialchars($value['send_to']).'</span><br>
							<span class="rvts8">'.htmlspecialchars($value['msgfrom']).'</span><br>
							<span class="rvts8">'.htmlspecialchars($value['reply_to']).'</span><br>'
							.(isset($value['ip'])&&$value['ip']!=''?Builder::ipLocator($value['ip']):'').'</div>';
						$entry='<div style="word-wrap:break-word;width:250px;"><span class="rvts8">'.$value['subject'].'</span>
							<div id="detail_'.$k.'" style="display:none" class="rvts8"><br><br>'.$msg_data.'</div>'
							.($value['attachments']!==''?'<br><br><span class="rvts8">'.$value['attachments'].'</span><br></div>':'');
						$entry_nav=array($this->ca->lang_l('details')=>'javascript:void(0);" onclick="javascript:sv(\'detail_'.$k.'\')');
						$row_data[]=array($entry,$entry_nav);
						$entry='<span class="rvts8">'.($value['success']=='1'?$this->ca->lang_l('sent'):$this->ca->lang_l('failed').F_BR.$value['success']).'</span>';
						$entry_nav=array($this->ca->lang_l('re-send')=>$this->ca->ca_abs_url.'?process=maillog&amp;eid='.$value['id'].'&amp;page='.$c_page);
						$row_data[]=array($entry,$entry_nav);
						$table_data[]=$row_data;
					}
				}
				$user_is_admin=$logged_user_data['user_admin']===1;
				$append=$user_is_admin?'':'
					<form method="post" action="'.$this->ca->ca_abs_url.'?process=maillog&clean'.$this->ca->ca_l_amp.'">
						  <input type="submit" value=" '.$this->ca->lang_l('clear log').' " onclick="javascript:return confirm(\''.$this->ca->lang_l('clear log MSG').'\')">
					</form>';
				$nav=Navigation::pageCA($rec_count,$this->ca->ca_abs_url."?process=maillog&amp;orderby=$orderby&amp;asc={$order_desc}{$where}",0,$c_page);
				$append.='<input type="button" onclick="document.location=\''.$cur_url.'&export_csv\'" value=" export as csv ">';

				$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$append);
			}
		}
		if($empty_set)
		{
			$table_data[]=array('','<span class="rvts8">Empty</span>');
			$output=Builder::addEntryTable($table_data,'');
		}
		$this->ca->ca_scripts='$(document).ready(function(){ assign_edits();})
			function process_edits()
			{
				document.location=\''.$cur_url.'&page_like=\'+$(\'input[id=page_like_filter]\').val() +\'&rec_like=\'+$(\'input[id=rec_like_filter]\').val()+\'&subj_like=\'+$(\'input[id=subj_like_filter]\').val();
				return false;
			};';
		$this->output($output);
	}
}

class ca_analytics_screen extends ca_admin_screens
{
	public function handle_screen()
	{
		$this->show_analytics();
	}

	protected function show_analytics()
	{

		$html='
<header class="Banner">
<ga-auth clientid="'.$this->f->ca_settings['ga_clientID'].'"></ga-auth>
</header>
  <ga-dashboard>
    <section id="controls">
      <ga-viewpicker></ga-viewpicker>
      <ga-datepicker
        startDate="30daysAgo"
        endDate="today">
      </ga-datepicker>
		<ga-activeusers></ga-activeusers>
    </section>

    <section id="charts">
      <ga-chart
        title="Timeline"
        type="LINE"
		  width="800"
        metrics="ga:sessions,ga:pageviews"
        dimensions="ga:date">
      </ga-chart>

      <ga-chart
        title="Sessions (by country)"
        type="GEO"
		  width="800"
        metrics="ga:sessions"
        dimensions="ga:country">
      </ga-chart>

      <ga-chart
        title="Top Browsers"
        type="COLUMN"
		  width="800"
        metrics="ga:sessions"
        dimensions="ga:browser"
        sort="-ga:sessions"
        maxResults="5">
      </ga-chart>

      <ga-chart
        title="Top pages"
        type="TABLE"
				width="800"
        metrics="ga:sessions"
        dimensions="ga:pagePath"
        sort="-ga:sessions"
        maxResults="8">
      </ga-chart>
    </section>

  </ga-dashboard>
';

		$this->output($this->f->navlist.$html.$this->f->navend);
	}
}

class ca_poll_screen extends ca_admin_screens
{
	public function handle_screen()
	{
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if($do=='delete')
			$this->delete_poll();
		elseif($do=='edit')
			$this->edit_poll();
		elseif($do=="add")
			$this->add_poll();
		elseif($do=='save')
			$this->save_poll();
		$this->manage_polls();
	}

	private function check_db_poll()
	{
		global $db;

		$tb_a=$db->get_tables('poll');
		if(empty($tb_a))
		{
			include_once($this->ca->ca_rel_path.'ezg_data/data.php');
			create_polldb($db);
		}
		else
		{
			$field_names=$db->db_fieldnames('poll_questions');
			if(count($field_names)!=POLL_QUESTIONS_FIELD_COUNT)
			{
				include_once($this->ca->ca_rel_path.'ezg_data/data.php');
				create_polldb($db,1);
			}
		}
	}
	public function manage_polls()
	{
		global $db;

		$this->check_db_poll();
		$output='';
		$url=$this->ca->ca_abs_url."?process=polls&amp;";

		$polls= $db->fetch_all_array('SELECT question, created, qid, online FROM `'.$db->pre.'poll_questions` ORDER BY created DESC' );
		$total_records=count($polls);
		$curr_page=$order_by=$asc=$orderby_pfix=$asc_pfix='';
		$this->prepare_page_vars($curr_page,$order_by,$asc,$orderby_pfix,$asc_pfix);
		$nav='<input type="button" value=" '.$this->ca->lang_l('add poll').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=polls&amp;do=add\'">';
		$nav.=Navigation::pageCA($total_records,$this->ca->ca_abs_url.'?process=polls'.$orderby_pfix.$asc_pfix,0,$curr_page);
		$cap_arrays=array($this->ca->lang_l('question'),$this->ca->lang_l('date'));
		$table_data=array();
		if(!empty($polls))
		{
			foreach($polls as $poll_elem)
			{
				$js_view = 'view_poll_from_widget('.$poll_elem['qid'].');';
				$name_nav=array();
				if($poll_elem['online'] === '1')
					$name_nav[$this->ca->lang_l('edit')]=$url.'poll_id='.$poll_elem['qid'].'&amp;do=edit';
				$name_nav[$this->ca->lang_l('view')]='javascript:void(0);" onclick="'.$js_view;
				$name_nav[$this->ca->lang_l('remove')]=$url.'poll_id='.$poll_elem['qid'].'&amp;do=delete';
				$view_output = '<div><div id="polls_'.$poll_elem['qid'].'" style="display:none;padding:3px"></div></div>';
				$row_data=array(
						array('<span class="rvts8">'.$poll_elem['question'].$view_output.'</span>',$name_nav),
						'<span class="rvts8">'.$poll_elem['created'].'</span>'
						);
				$table_data[]=$row_data;
			}
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data);
		}
		else
		{
			$table_data[]=array('<span class="rvts8">'.$this->ca->lang_l('none polls').'</span>');
			$output.=Builder::adminTable($nav,array(),$table_data);
		}
		$this->ca->ca_dependencies[]='documents/poll.css';
		$this->output($output);
	}

	protected function delete_poll()
	{
		global $db;
		$qid=intval($_REQUEST['poll_id']);
		$db->query('DELETE FROM '.$db->pre.'poll_questions WHERE qid='.$qid);
		$db->query('DELETE FROM '.$db->pre.'poll_votes WHERE qid='.$qid);
		$db->query('DELETE FROM '.$db->pre.'poll_options WHERE qid='.$qid);
	}

	protected function edit_poll()
	{
		global $db;
		$qid=intval($_REQUEST['poll_id']);
		$query = 'SELECT p_o.id, p_o.qid, p_o.value, p_o.oid, p_q.question, p_q.template
				FROM `'.$db->pre.'poll_options` AS p_o,  `'.$db->pre.'poll_questions` AS p_q
				WHERE (p_o.qid='.$qid.') AND (p_q.qid='.$qid.') AND (p_q.qid > 5000) ORDER BY p_o.oid ASC';
		$poll_info = $db->fetch_all_array($query);
		$output=$this->build_add_poll_form($poll_info);
		$this->output($output);
		exit;
	}

	public function add_poll()
	{
		$output=$this->build_add_poll_form();
		$this->output($output);
		exit;
	}

	protected function save_poll()
	{
		global $db;
		array_walk($_POST, 'Formatter::stripTags');
		$post_qid=isset($_POST['qid'])?(int)$_POST['qid']:'';
		$post_question=isset($_POST['question'])?$_POST['question']:'';
		$post_template=isset($_POST['template_class'])?$_POST['template_class']:'';
		$post=$post_options_id=array();
		foreach($_POST as $key=>$value)
		{
			if(preg_match('#^option_#i', $key) === 1)
			{
				$post[]['value'] = $value;
				$count_opt = count($post);
				$last_key = $count_opt -1;
				$post[$last_key]['oid'] = $count_opt;
				$post[$last_key]['id'] = '';
			}
			elseif(preg_match('#^db_id_#i', $key) === 1)
			{
				$count_opt = count($post);
				$last_key = $count_opt -1;
				$post[$last_key]['id'] = $value;
				$post_options_id[]  = $value;
			}
		}
		if(count($post) >= 2 && $post_question !== '')
		{
			if($post_qid !== '')	//edit exist question
			{
				$query = 'SELECT p_o.value, p_o.oid, p_o.id, p_q.qid,  p_q.question, p_q.template
						FROM '.$db->pre.'poll_questions as p_q, '.$db->pre.'poll_options as p_o
						WHERE (p_q.qid="'.$post_qid.'") AND (p_q.qid = p_o.qid) AND (p_q.qid > 5000)
						ORDER BY p_o.oid';
				$db_info = $db->fetch_all_array($query);
				$query_voted = '
					SELECT qid, oid
					FROM '.$db->pre.'poll_votes
					WHERE qid='.$post_qid.' AND qid > 5000';
				$voted_db = $db->fetch_all_array($query_voted);
				if(!empty($db_info))
				{
					$db_oid = array();
					$db_qid = $db_info[0]['qid'];
					$db_question = $db_info[0]['question'];
					$db_template = $db_info[0]['template'];
					foreach($db_info as $key=>$value)
							$db_oid[] = $value['oid'];
					foreach($voted_db as $key=>$value)
					{
						if(!in_array($value['oid'], $db_oid))
						 {
							if(!isset($flag_db_error))
							{
								$res=$db->query('
									DELETE
									FROM '.$db->pre.'poll_votes
									WHERE qid='.$db_qid.' AND oid='.$value['oid']);
								if($res !== false)
									unset($voted_db[$key]);
								else
									$flag_db_error = 1;
							}
						}
					}
					foreach($db_info as $k_db=>$v_db)
					{
						if(!in_array($v_db['id'],$post_options_id))
						{
							if(!isset($flag_db_error))
							{
								$res=$db->query('
									DELETE FROM '.$db->pre.'poll_options
									WHERE id='.$v_db[id]);
								if($res !== false)
									unset($db_info[$k_db]);
								else
									$flag_db_error = 1;
							}
							foreach($voted_db as $k_voted=>$v_voted)
							{
								if($v_voted['oid'] == $v_db['oid'])
								{
									if(!isset($flag_db_error))
									{
										$res=$db->query('
											DELETE FROM '.$db->pre.'poll_votes
											WHERE oid='.$v_db['oid'].' AND qid='.$db_qid) ;
										if($res !== false)
											unset($voted_db[$k_voted]);
										else
											$flag_db_error = 1;
									}
								}
							}
						}
					}
					$i=0;
					foreach($db_info as $k=>$v)
					{
						unset ($db_info[$k]);
						$db_info[$i]['value'] = $v['value'];
						$db_info[$i]['oid'] = $v['oid'];
						$db_info[$i]['id'] = $v['id'];
						$db_info[$i]['qid'] = $v['qid'];
						$db_info[$i]['question'] = $v['question'];
						$i++;
					}
					$i=0;
					foreach($voted_db as $k=>$v)
					{
						unset ($voted_db[$k]);
						$voted_db[$i]['qid'] = $v['qid'];
						$voted_db[$i]['oid'] = $v['oid'];
						$i++;
					}
					foreach($post as $v_post)
					{
						if($v_post['id'] !== '') //existing option
						{
							foreach($db_info as $k_db=>$v_db)
							{
								if($v_db['id'] == $v_post['id'])
								{
									foreach($voted_db as $k_voted=>$v_voted)
									{
										if($v_voted['oid'] === $v_db['oid'] )
										{
											if(!isset($flag_db_error))
											{
												$res=$db->query_update('poll_votes',array('oid'=>$v_post['oid']),'oid='.$v_db['oid'].' AND qid='.$db_qid);
												if($res !== false)
													$voted_db[$k_voted]['oid'] = $v_post['oid'];
												else
													$flag_db_error = 1;
											}
										}
									}
									if(!isset($flag_db_error))
									{
										$res=$db->query_update('poll_options',array('oid'=>$v_post['oid'], 'value'=>$v_post['value']),'id='.$v_post['id']);
										if($res !== false)
										{
											$db_info[$k_db]['oid'] = $v_post['oid'];
											$db_info[$k_db]['value'] = $v_post['value'];
										}
										else
											$flag_db_error = 1;
									}
									break;
								}
							}
						}
						else //add new option in poll_options
						{
							if(!isset($flag_db_error))
							{
								$res=$db->query_insert('poll_options',array('qid'=>$db_qid,'oid'=>$v_post['oid'],'value'=>$v_post['value']));
								if($res !== false)
								{
									$count_opt = count($db_info);
									$db_info[$count_opt]['value'] = $v_post['value'];
									$db_info[$count_opt]['oid'] = $v_post['oid'];
									$db_info[$count_opt]['qid'] =$db_qid;
								}
								else
									$flag_db_error = 1;
							}
						}
					}
					if($db_question !== $post_question || $db_template !== $post_template)
					{
						if(!isset($flag_db_error))
						{
							$res=$db->query_update('poll_questions',array('question'=>$post_question,'template'=>$post_template),'qid='.$db_qid);
							if($res === false)
								$flag_db_error = 1;
						}
					}
				}//end in not empty db
			}//end if edit exist question
			else //add new question  //insert in poll_question and in poll_options
			{
				if(isset($this->f->ca_settings['last_poll_qid']))
						$next_qid = $this->f->ca_settings['last_poll_qid']+1;
				else
				{
					$query_last_qid = '
						SELECT MAX(qid) as qid
						FROM '.$db->pre.'poll_questions
						WHERE qid>5000 LIMIT 1';
					$last_question = $db->query_singlevalue($query_last_qid);
					if($last_question !== null && $last_question !== '')
						$next_qid = (int)$last_question + 1;
					else
						$next_qid = 5001;
				}

				$res=$db->query_insert('poll_questions',array('qid'=>$next_qid,'question'=>$post_question,
					 'created'=>Date::buildMysqlTime(),'rev'=>'2','online'=>true,'template'=>$post_template));
				if($res !== false)
				{
					CA::insert_setting(array('last_poll_qid'=>$next_qid));
					foreach($post as $k=>$v)
					{
						if(!isset($flag_db_error))
							$res=$db->query_insert('poll_options',array('qid'=>$next_qid,'value'=>$v['value'],'oid'=>$v['oid']));
						if($res === false)
							$flag_db_error = 1;
					}
				}
			}
		}
	}

	public function build_add_poll_form($poll_info=array())
	{
		$options_values = array(0=>'',1=>'');
		$options_bd_id = array();
		$name=$hidden_input_qid=$ans_preview='';
		$template='template1';
		foreach($poll_info as $key=>$option_info)
		{
			if($key==0)
			{
				$name = $option_info['question'];
				$template = $option_info['template'];
				$hidden_input_qid = Builder::buildInput('qid',$option_info['qid'],'','','hidden');
			}
			$options_values[$key] = $option_info['value'];
			$options_bd_id[$key] = $option_info['id'];
		}
		$table_data=array();
		$table_data[]=array(false,$this->ca->lang_l('question').$this->f->fmt_star,'',Builder::buildInput('question',$name,$this->ca->inp_width,'','text','onkeyup="$(\'.poll_title\').html(this.value);"').$hidden_input_qid);
		$table_data[]=array(false,$this->ca->lang_l('options').'<span id="options_title"></span>');

		foreach($options_values as $key=>$value)
		{
			$option_id = $key+1;
			$prev_id='option_'.$option_id;
			$ans_preview.='<p class="'.$prev_id.'"><input type="radio" id="'.'x'.$prev_id.'" value="1" name="poll_answer"><label id="l'.$prev_id.'" for="x'.$prev_id.'">'.$value.'</p>';
			$table_data[]=array(true,
				 Builder::buildInput('option_'.$option_id,$value,$this->ca->inp_width,'','text','onkeyup="$(\'#l'.$prev_id.'\').html(this.value);"','','','',0,'option_'.$option_id)
				 .'<input type="button" class="input1" onclick="remove_option($(this))" value="remove" id="remove_option_'.$option_id.'"/><input type="hidden" name="db_id_'.$options_bd_id[$key].'" value="'.$options_bd_id[$key].'"/>');
		}

		$end='<input type="button" id="add_option" value="'.$this->ca->lang_l('add option').'" onclick="add_option_poll()"><br/>
				<span class="rvts8 a_editcaption">'.$this->ca->lang_l('template').'</span><br>'
				.Builder::buildSelect('template_class',$this->f->poll_templates_classes,$template,'','value','onchange="$(\'#poll\').attr(\'class\',\'\').addClass(this.value);"')
				.'<div><div id="poll" class="'.$template.'">
						<h1 class="poll_title">'.$name.'</h1>
						<div class="pc">'.
							$ans_preview.'
							<p><input type="button" value="Submit" class="poll_subm_btn"></p>
						</div>
					</div>
					</div></div>'
					.$this->form_buttons('save',true,'onclick="document.location=\''.$this->ca->ca_abs_url."?process=polls".$this->ca->ca_l_amp.'\'"').'
				<span class="rvts8">('.$this->f->fmt_star.') '.$this->ca->lang_l('required fields').'</span>';
		$output = Builder::addEntryTable($table_data,$end,'','',true,'',$this->ca->ca_abs_url,
					'<form action="'.$this->ca->ca_abs_url.'?process=polls&amp;do=save'.'" method="post"><div style="text-align:left;"></div>','</form>',1,0,true,true);
		$this->ca->ca_scripts.='
			$(document).ready(function(){$(".col_toggler").click(function() {
				$("#sort_table").sortable({items: "div.dr"});});
				$("form").submit(function(e) {
					 if($("#question").val()=="") {
						e.preventDefault();
						$("#question").css("border","solid 1px #f00").focus();}});
					 $("input[name=\'save\']").click(function(){
					 if(count_options_ofpoll("option_") < 2){alert(\''.$this->ca->lang_l("two or more options").'\');
					var form = $(this).closest("form");}});});
			';
		$this->ca->ca_dependencies[]='jquery-ui.css';
		$this->ca->ca_dependencies[]='jquery-ui.min.js';
		$this->ca->ca_dependencies[]='documents/poll.css';
		return $output;
	}
}

class ca_groups_screen extends ca_admin_screens
{
	public function handle_screen($logged_user_data)
	{
		if($this->ca->ca_action_id=="assigntogroup")
		{
			if(!is_array($_POST['user_id'])&&!is_numeric($_POST['user_id']))
				$this->manage_groups();
			else
				$this->ca->groups->assign_users_to_group($_POST['user_id'],(int)$_POST['move_to_grp']);
		}
		elseif($this->ca->ca_action_id=="groups")
			$this->manage_groups();
		elseif($this->ca->ca_action_id=="fixgroups")
		{
			$this->ca->groups->fix_missing_users_links();
			$this->manage_groups();
		}
		elseif($this->ca->ca_action_id=="remusrfromgrp")
		{
			$this->ca->groups->remove_usr_from_grp((int)$_REQUEST['uid']);
			$this->process_user_groups($logged_user_data);
		}
		elseif($this->ca->ca_action_id=="processgroup")
			$this->process_user_groups($logged_user_data);
	}

	public function manage_groups()
	{
		global $db;

		$output='';
		$url=$this->ca->ca_abs_url."?process=processgroup".$this->ca->ca_l_amp;
		$send_url=$this->ca->ca_abs_url."?process=mail_users".$this->ca->ca_l_amp;
		$groups=$db->fetch_all_array('
			SELECT g.id, g.name, g.description, g.custom_data  ,
			CASE WHEN l.group_id IS NULL THEN 0 ELSE COUNT(g.name) END AS cnt, g.creation_date, g.modified_date
			FROM `'.$db->pre.'ca_users_groups` AS g
			LEFT JOIN `'.$db->pre.'ca_users_groups_links` AS l ON l.group_id=g.id
			GROUP BY g.name');
		$total_records=count($groups);
		$curr_page=$order_by=$asc=$orderby_pfix=$asc_pfix='';
		$this->prepare_page_vars($curr_page,$order_by,$asc,$orderby_pfix,$asc_pfix);
		$nav='
			<div>
				<input type="button" value=" '.$this->ca->lang_l('add group').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=processgroup'.$this->ca->ca_l_amp.'\'">
			</div>';
		$nav.=Navigation::pageCA($total_records,$this->ca->ca_abs_url.'?process=groups'.$orderby_pfix.$asc_pfix,0,$curr_page);
		$cap_arrays=array($this->ca->lang_l('title'),$this->ca->lang_l('description'),$this->ca->lang_l('access to'),$this->ca->lang_l('users count'));
		$table_data=array();
		if(!empty($groups))
		{
			foreach($groups as $group_elem)
			{
				$name_nav=array(
					$this->ca->lang_l('edit')=>$url.'&amp;grp_id='.$group_elem['id'].'&amp;do=edit',
					$this->ca->lang_l('users in group')=>$url.'&amp;grp_id='.$group_elem['id'].'&amp;do=view',
					$this->ca->lang_l('users mailing')=>$send_url.'&amp;grp_id='.$group_elem['id'],
					$this->ca->lang_l('remove')=>$url.'&amp;grp_id='.$group_elem['id'].'&amp;do=remove');

				$cust_data=unserialize($group_elem['custom_data']);
				$access=count($cust_data)>1?$this->ca->lang_l('page level'):$this->get_access_typelabel($cust_data[0]['access_type']);

				$row_data=array(
					array('<span class="rvts8">'.$group_elem['name'].'</span>',$name_nav),
					'<span class="rvts8">'.$group_elem['description'].'</span>',
					'<span class="rvts8">'.$access.'</span>',
					'<span class="rvts8">'.$group_elem['cnt'].'</span>');
				$table_data[]=$row_data;
			}
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data);
		}
		else
		{
			$table_data[]=array('<span class="rvts8">'.$this->ca->lang_l('none groups').'</span>');
			$output.=Builder::adminTable($nav,array(),$table_data);
		}
		$this->ca->ca_scripts='$(document).ready(function(){
			$("#mail_button").click(function() {$("#mail_form_div").toggle();});
			$("#mail_users_form").submit(function(e){e.preventDefault();
			$(this).hide();$("#loader").show();});$("#btn_send_mails").click(function(){
				$.get("'.$this->ca->ca_abs_url.'",$("#mail_users_form").submit().serialize(),function(re) {
					 $("#mail_form_div").html(re);
					 $("#loader").hide();
					 } );
				});
			})';
		$this->output($output);
	}

	public function process_user_groups($logged_user_data)
	{
		global $db;
		$output='';
		$flag=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if(isset($_POST['save']))
		{
			$name=(isset($_POST['name'])?Formatter::stripTags($_POST['name']):'');

			if($name!='')
			{
				$desc=(isset($_POST['desc'])?Formatter::stripTags($_POST['desc']):'');
				$r_link=(isset($_POST['r_link'])?Formatter::stripTags($_POST['r_link']):'');
				$r_out_link=(isset($_POST['r_out_link'])?Formatter::stripTags($_POST['r_out_link']):'');

				$access_data=serialize($this->ca->users->build_access_array(-9999)); //user_id marked as int to be replaced easily later
				if($flag=='add')
					$db->query_insert('ca_users_groups',array('name'=>$name,'description'=>$desc,'login_redirect'=>$r_link,'logout_redirect'=>$r_out_link,'creation_date'=>'now()',
						'modified_date'=>'now()','custom_data'=>$access_data));
				elseif($flag=='edit')
				{
					$grp_id=(int)$_REQUEST['group_id'];
					$db->query_update('ca_users_groups',array('name'=>$name,'description'=>$desc,'login_redirect'=>$r_link,'logout_redirect'=>$r_out_link,'modified_date'=>'now()',
						'custom_data'=>$access_data),'id='.$grp_id);
					$usrs_in_grp=$db->fetch_all_array('SELECT user_id FROM '.$db->pre.'ca_users_groups_links WHERE group_id='.$grp_id);
					$usrs_arr=array();
					foreach($usrs_in_grp as $usr)
						$usrs_arr[]=$usr['user_id']; //make proper users array
					$this->ca->groups->assign_users_to_group($usrs_arr,$grp_id); //apply new access to the users
					CA::insert_setting(array('sr_access'=>'from_group|'.$grp_id.'|'.$access_data));
				}
				$this->manage_groups();
			}
			else
				$output.=$this->build_add_user_group_form($logged_user_data);
		}
		elseif($flag=='remove')
		{
			$db->query('
				DELETE
				FROM '.$db->pre.'ca_users_groups
				WHERE id='.(int)$_REQUEST['grp_id']);
			$db->query('
				DELETE
				FROM '.$db->pre.'ca_users_groups_links
				WHERE group_id='.(int)$_REQUEST['grp_id']);
			$this->manage_groups();
		}
		elseif($flag=='view')
		{
			$usrs_table=array();
			$usrs=$db->fetch_all_array('
				SELECT u.username, u.uid
				FROM '.$db->pre.'ca_users AS u
				INNER JOIN '.$db->pre.'ca_users_groups_links AS l ON l.user_id=u.uid AND l.group_id='.(int)$_REQUEST['grp_id']);
			foreach($usrs as $usr)
				$usrs_table[]=array(array(
						'<span class="rvts8">'.$usr['username'].'</span>',
						array(
							$this->ca->lang_l('remove')=>$this->ca->ca_abs_url.'?process=remusrfromgrp&do=view&grp_id='
							.(int)$_REQUEST['grp_id'].'&uid='.$usr['uid'])));
			$output.=Builder::adminTable('',array($this->ca->lang_l('users in group')),$usrs_table);
		}
		else
			$output.=$this->build_add_user_group_form($logged_user_data);

		$this->output($output,$flag=='add'?' - group':'');
	}

	public function build_add_user_group_form($logged_user_data,$msg='')
	{
		global $db;

		$user_is_admin=$logged_user_data['user_admin']===1;
		$flag=(isset($_REQUEST['do'])&&($_REQUEST['do']=='add'||$_REQUEST['do']=='edit'))?$_REQUEST['do']:'add';
		$saving=isset($_POST['save']);
		$name=$desc=$cust_data=$grp_id=$r_link=$r_out_link='';
		if(!$saving)
		{
			$group_id=isset($_GET['grp_id'])?(int)$_GET['grp_id']:-1;
			$grp_info=$db->fetch_all_array('SELECT id, name, description, custom_data, login_redirect,logout_redirect FROM '.$db->pre.'ca_users_groups WHERE id='.$group_id);
			if(!empty($grp_info))
			{
				$name=$grp_info[0]['name'];
				$desc=$grp_info[0]['description'];
				$r_link=$grp_info[0]['login_redirect'];
				$r_out_link=$grp_info[0]['logout_redirect'];
				$cust_data=$grp_info[0]['custom_data'];
				$cust_data=unserialize($cust_data);
				$grp_id=$grp_info[0]['id'];
			}
		}
		else
		{
			$name=ca_functions::ca_un_esc($_POST['name']);
			$desc=Formatter::stripTags($_POST['desc']);
			$r_link=addslashes(Formatter::stripTags($_POST['r_link']));
			$r_out_link=addslashes(Formatter::stripTags($_POST['r_out_link']));
		}
		$table_data=array();
		$table_data[]=array($this->ca->lang_l('title').$this->f->fmt_star,
			 '<input type="hidden" name="group_id" value="'.$grp_id.'">
			 <input type="hidden" name="do" value="'.$flag.'">
			 <input type="hidden" name="old_name" value="'.$name.'">
			 <input class="input1" type="text" name="name" id="name" value="'.$name.'" style="'.$this->ca->inp_width.'">');

		$table_data[]=array($this->ca->lang_l('description'),
			 '<textarea class="input1" name="desc" id="desc" style="'.$this->ca->inp_width.'">'.$desc.'</textarea>');
		$table_data[]=array($this->ca->lang_l('redirect login'),Builder::buildInput('r_link',$r_link,$this->ca->inp_width,'','text','','','','r_link'));
		$table_data[]=array($this->ca->lang_l('redirect page'),Builder::buildInput('r_out_link',$r_out_link,$this->ca->inp_width,'','text','','','','r_out_link'));
		// sections and access
		if($saving)
			$access_line=$this->build_access_line($user_is_admin);
		else
			$access_line=$this->build_access_line($user_is_admin,false,-9999,$cust_data);

		$table_data[]=array($this->ca->lang_l('access to'),$access_line);

		$table_data[]='<span class="rvts8">('.$this->f->fmt_star.') '.$this->ca->lang_l('required fields').'</span>';
		$end=$this->form_buttons('save',true,'onclick="document.location=\''.$this->ca->ca_abs_url."?process=groups".$this->ca->ca_l_amp.'\'"');
		$output=($msg!=''?$msg.F_BR:'').Builder::addEntryTable($table_data,$end,'','',false,'',$this->ca->ca_abs_url,
				'<form action="'.$this->ca->ca_abs_url."?process=processgroup".$this->ca->ca_l_amp.'" method="post">
					 <div style="text-align:left;"></div>');
		$this->ca->ca_scripts.="
			$(document).ready(function(){
				$('form').submit(function(e) {if($('#name').val()=='') {e.preventDefault(); $('#name').css('border','solid 1px #f00').focus();}});
			});
			";
		return $output;
	}
}

class ca_history_screen extends ca_admin_screens
{
	public function handle_screen()
	{
		$this->site_history();
	}

	protected function clear_history()
	{
		global $db;
		$id=intval($_GET['did']);
		$db->query('
			DELETE
			FROM '.$db->pre.'site_history
			WHERE id '.($id==0?' > 0':' = '.$id));
	}

	protected function site_history()
	{
		global $db;

		$sitemap_list=CA::getSitemap($this->ca->ca_prefix,false,true);
		$output='';
		$cur_url=Linker::currentPageUrl();
		$max=Navigation::recordsPerPage();
		$empty_set=true;

		if(!$this->ca->user_is_admin && isset($_GET['did']))
			$this->clear_history();
		$total_count=ca_db::db_count('site_history');

		if($total_count>0)
		{
			$c_page=(isset($_GET['page'])?intval($_GET['page']):1);
			$page_filter=isset($_REQUEST['page_like'])?$db->escape($_REQUEST['page_like']):'';
			$det_filter=isset($_REQUEST['det_like'])?$db->escape($_REQUEST['det_like']):'';
			$user_filter=isset($_REQUEST['user_like'])?$db->escape($_REQUEST['user_like']):'';
			$usr_id='';
			if($user_filter!='')
			{
				$usr_id=User::getUser($user_filter,$this->ca->ca_prefix);
				$usr_id=isset($usr_id['uid'])?$usr_id['uid']:'';
			}
			$filter_params=array('user_id'=>$usr_id, 'dump'=>$det_filter);
			$where='';
			Formatter::filterParamsToQuery($where,$filter_params);
			list($orderby,$order_desc)=Filter::orderBy('cd','DESC');
			$order_desc_opp=$order_desc=='ASC'?'DESC':'ASC';

			$page_title_str='';
			$p=0;
			foreach($sitemap_list as $page_id=>$v){
				$page_title_str.='("'.$page_id.'", "'.$v[0].'")'.($p==count($sitemap_list)-1?'':',');
				$p++;
			}
			$removed_pages_str='';
			$all_pages_history=$db->fetch_all_array('SELECT page_id FROM '.$db->pre.'site_history GROUP BY page_id');
			foreach($all_pages_history as $v){
				if(strpos($page_title_str,'("'.$v['page_id'].'"')===false)
					$removed_pages_str.=($page_title_str!=''&&$removed_pages_str==''?', ':'').'("'.$v['page_id'].'", "'.$v['page_id'].'"),';
			}

			$query=$db->query('
			CREATE TEMPORARY TABLE IF NOT EXISTS '.$db->pre.'pages_titles_list 
			(
				id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				page_id INT NOT NULL UNIQUE,
				title TEXT NOT NULL
			)
			ENGINE=MYISAM');

			$show_records=array();
			if($query!==false)
			{
				$db->query('INSERT INTO '.$db->pre.'pages_titles_list (page_id, title) VALUES '.$page_title_str.substr_replace($removed_pages_str, "", -1));
				$show_records=$db->fetch_all_array('
					SELECT sh.page_id,sh.table_id,sh.entry_id,sh.dump,sh.creation_date as cd,sh.user_id,cau.avatar,cau.username
					FROM '.$db->pre.'site_history AS sh
					LEFT JOIN '.$db->pre.'ca_users AS cau ON cau.uid = sh.user_id '
					.($page_filter!=''?' INNER JOIN '.$db->pre.'pages_titles_list as p_t ON p_t.page_id=sh.page_id  AND CONCAT_WS(" ",p_t.title,sh.table_id,sh.entry_id) LIKE "%'.$page_filter.'%"':'')
					.$where
					.'ORDER BY '.$orderby.' '.$order_desc);
				$db->query('DROP TABLE '.$db->pre.'pages_titles_list');
			}
			if(!empty($show_records))
			{
				$empty_set=false;
				$where=$page_filter!=''?'&amp;page_like='.$page_filter:'';
				$where .= $det_filter!=''?'&amp;det_like='.$det_filter:'';
				$where .= $user_filter!=''?'&amp;user_like='.$user_filter:'';
				$cur_url=preg_replace('/&orderby=(cd|page_id|user_id)/','',$cur_url);
				$cur_url=preg_replace('/&asc=(ASC|DESC)/','',$cur_url);
				$cur_url=preg_replace('/&page_like=[a-zA-Z0-9]*/','',$cur_url);
				$cur_url=preg_replace('/&det_like=[a-zA-Z0-9]*/','',$cur_url);
				$cur_url=preg_replace('/&user_like=[a-zA-Z0-9]*/','',$cur_url);
				$cap_arrays=array(
					array($cur_url.'&amp;orderby=cd&amp;asc='.$order_desc_opp.$where,$orderby=='cd'?'underline':'none',$this->ca->lang_l('date')),
					array($cur_url.'&amp;orderby=page_id&amp;asc='.$order_desc_opp.$where,$orderby=='page_id'?'underline':'none',$this->ca->lang_l('page'),
						 Filter::build('page_like_filter',$page_filter,'process_edits();')),
					$this->ca->lang_l('details').Filter::build('det_like_filter',$det_filter,'process_edits();'),
					array($cur_url.'&amp;orderby=user_id&amp;asc='.$order_desc_opp.$where,$orderby=='user_id'?'underline':'none',$this->ca->lang_l('user'),
						 Filter::build('user_like_filter',$user_filter,'process_edits();'))
				);
				$table_data=array();
				$rec_count=count($show_records);
				for($j=($c_page-1)*$max; $j<($c_page*$max); $j++)
				{
					if($rec_count>$j)
					{
						$recId=$j;
						$value=$show_records[$recId];
						if(!empty($value))
						{
							$dumpTitle=$dump=$last_key='';
							$dump_a=array();
							$dump_ar=explode("\n",$value['dump']);
							foreach($dump_ar as $k=> $v)
							{
								if(strpos($v,' => ')!==false)
								{
									$last_key=Formatter::GFS($v,"'","'");
									$dump_a[$last_key]=Formatter::GFS($v,"=> '","");
								}
								elseif($last_key!='')
									$dump_a[$last_key].=$v;
							}
							foreach($dump_a as $k=> $v)
							{
								if($k=='content'||$k=='html_description'||$k=='details'||$k=='htmldata')
									$dump.='<span class="rvts8"><b>'.$k.'</b>=<br>
													<textarea style="width:98%;height:100px;" readonly="readonly">'.rtrim($v,",')").'</textarea>
											  </span><br>';
								elseif($k=='thumbnail_url'||$k=='image1')
									$dump.='<span class="rvts8"><b>'.$k.'</b>='.rtrim($v,",')").'</span><br>
											  <img style="max-width:150px" src="'.str_replace('../','',rtrim($v,",')")).'"></br>';
								elseif($k!='exif_data')
									$dump.='<span class="rvts8"><b>'.$k.'</b>='.rtrim($v,",')").'</span><br>';
								if($k=='htmldata')
									 $dumpTitle='<span class="rvts8">'.substr(Formatter::stripTags(rtrim($v,",')")),0,50).'...</span><br>';
								if($k=='title')
									 $dumpTitle='<span class="rvts8">'.rtrim($v,",')").'</span><br>';
							}
							if($value['user_id']=='-1')
								$username=$this->f->admin_nickname;
							else
								$username=intval($value['user_id'])>0?User::fetchUserName($value['user_id'],''):$value['user_id'];
							$avatar=User::getAvatarFromData($value,$db,$username,$this->ca->ca_site_url,'right');
							$p_name=(isset($sitemap_list[$value['page_id']])?$sitemap_list[$value['page_id']][0]:($value['page_id']=='0'?$this->ca->lang_l('administration'):($value['page_id']=='tella'?$this->ca->lang_l('tell a friend'):$value['page_id'])))
								.F_BR.$value['table_id'].F_BR.$value['entry_id'];

							$row_data=array();
							$entry='<span class="rvts8">'.(isset($value['cd'])?ca_functions::format_output_date(strtotime($value['cd'])):'x').'</span>';
							$entry_nav=array($this->ca->lang_l('remove')=>$this->ca->ca_abs_url.'?process=history&amp;did='.$value['id'].'&amp;page='.$c_page);
							$row_data[]=$this->ca->user_is_admin?$entry:array($entry,$entry_nav);
							$row_data[]='<span class="rvts8">'.$p_name.'</span>';

							$entry_nav=array($this->ca->lang_l('details')=>'javascript:void(0);" onclick="javascript:sv(\'detail_'.$recId.'\')');
							$entry=$dumpTitle.'<div id="detail_'.$recId.'" style="display:none;word-wrap:break-word" class="rvts8">'.$dump.'</div>';
							$row_data[]=array($entry,$entry_nav);
							$row_data[]='<div class="divTable"><div><span class="rvts8">'.$username.'</span></div><div>'.$avatar.'</div></div>';

							$table_data[]=$row_data;
						}
					}
				}
				$append=$this->ca->user_is_admin?'':'<form method="post" action="'.$this->ca->ca_abs_url.'?process=history&did=0'.$this->ca->ca_l_amp.'">
					 <input type="submit" value=" '.$this->ca->lang_l('clear log').' " onclick="javascript:return confirm(\''.$this->ca->lang_l('clear log MSG').'\')">
					</form>';
				$nav=Navigation::pageCA($rec_count,$this->ca->ca_abs_url.'?process=history&amp;orderby='.$orderby.'&amp;asc='.$order_desc.$where,0,$c_page);
				$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$append);
			}
		}
		if($empty_set)
		{
			$table_data[]=array('','<span class="rvts8">Empty</span>');
			$output=Builder::addEntryTable($table_data,'');
		}
		$this->ca->ca_scripts='
			$(document).ready(function(){assign_edits();});
			function process_edits()
			{
				document.location=\''.preg_replace('/&page=[a-zA-Z0-9]*/','',$cur_url).'&page_like=\'+$(\'input[id=page_like_filter]\').val() +\'&det_like=\'+$(\'input[id=det_like_filter]\').val() +\'&user_like=\'+$(\'input[id=user_like_filter]\').val();
			 	return false;
			};';
		$this->output($output);
	}
}

class ca_settings_screen extends ca_admin_screens
{
	public function handle_screen()
	{
		if($this->ca->ca_action_id=="confcounter")
			$this->counter_settings();
		elseif($this->ca->ca_action_id=="resetcounter")
			$this->reset_counter();
		elseif($this->ca->ca_action_id=="confreg")
			$this->registration_settings();
		elseif($this->ca->ca_action_id=='regfields')
			$this->registration_fields();
		elseif($this->ca->ca_action_id=='getfield')
			$this->get_regfield();
		elseif($this->ca->ca_action_id=="confreglang")
			$this->registration_language_settings();
		elseif($this->ca->ca_action_id=="settings")
			$this->show_settings();
	}

	protected function settings_buttons()
	{
		$result='
			<input class="settings_tab'.($this->ca->ca_action_id=="settings"?' active':'').'" type="button" value=" '.$this->ca->lang_l('settings').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=settings\'">
			<input class="settings_tab'.($this->ca->ca_action_id=="confreg"?' active':'').'" type="button" value=" '.$this->ca->lang_l('registration settings').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=confreg\'">
			<input class="settings_tab'.($this->ca->ca_action_id=="regfields"?' active':'').'" type="button" value=" '.$this->ca->lang_l('registration fields').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=regfields\'">
			<input class="settings_tab'.($this->ca->ca_action_id=="confreglang"?' active':'').'" type="button" value=" '.$this->ca->lang_l('language').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=confreglang\'">';
		if($this->f->counter_on)
			$result.=' <input class="settings_tab'.($this->ca->ca_action_id=="confcounter"?' active':'').'" type="button" value=" '.$this->ca->lang_l('counter settings').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=confcounter\'">';
		return $result;
	}

	public function reset_counter()
	{
		global $db;

		$end='';
		if(isset($_GET['confirmreset']))
		{
			$db_tables=array('counter_details','counter_totals','counter_pageloads');
			foreach($db_tables as $v)
				$db->query('DELETE FROM '.$db->pre.$v);
			CA::insert_setting(array("c_cookie_suffix"=>time()));
			$table_data[]=array('','<span class="rvts8">'.$this->ca->lang_l('reset done').'</span>');
		}
		else
		{
			$table_data[]=array('','<span class="rvts8">'.$this->ca->lang_l('reset MSG1').'</span>');
			$end='<input type="button" value=" '.$this->ca->lang_l('confirm counter reset').' " onclick="document.location=\''
				.$this->ca->ca_abs_url.'?process=resetcounter&amp;confirmreset=confirm'.$this->ca->ca_l_amp.'\'" onclick="javascript:return confirm(\''.$this->ca->lang_l('reset MSG2').'\')">';
		}
		$output=Builder::addEntryTable($table_data,$end);
		$this->output($output,' - '.$this->ca->lang_l('reset counter'));
	}

	protected function save_settings()
	{
		global $db;
		setcookie('ca_lang',strtoupper(Formatter::stripTags($_POST['lang'])),mktime(23,59,59,1,1,2037),str_replace('http://'.Linker::getHost(),'',$this->ca->ca_site_url));
		$arr=array('language'=>$_POST['lang'],'tzone_offset'=>$_POST['tzone_offset'],'landing_page'=>$_POST['landing_page'],
			'logout_redirect_url'=>$_POST['logout_redirect_url'],'login_redirect_option'=>$_POST['login_redirect_option'],
			'stat_hide_ips'=>(isset($_POST['stat_hide_ips'])?1:0),
			'sr_users_seecounter'=>(isset($_POST['users_seecounter'])?1:0),
			'sr_users_see_all'=>(isset($_POST['sr_users_see_all'])?1:0),
			'show_orders'=>(isset($_POST['show_orders'])?1:0),
			'show_sitemap'=>(isset($_POST['show_sitemap'])?1:0),
			'pwchange_enable'=>(isset($_POST['pwchange_enable'])?1:0),
			'profilechange_enable'=>(isset($_POST['profilechange_enable'])?1:0),
			'cookie_login'=>(isset($_POST['cookie_login'])?1:0),
			'protect_footer'=>(isset($_POST['protect_footer'])?1:0),
			'protect_downloads'=>(isset($_POST['protect_downloads'])?1:0),
			'usr_blocking'=>(isset($_POST['usr_blocking'])?1:0),
			'monitor_users'=>(isset($_POST['monitor_users'])?1:0),
			'sr_enable'=>(isset($_POST['sr_enable'])?1:0),
			'ga_clientID'=>(isset($_POST['ga_clientID'])?$_POST['ga_clientID']:'')
		);

		CA::insert_setting($arr);
		CA::fetchDBSettings($db);
	}

	public function show_settings()
	{
		$end='';
		if(isset($_POST['submit']))
		{
			$this->save_settings();
			$this->ca->ca_update_language_set(strtoupper(Formatter::stripTags($_POST['lang'])));
			$table_data[]=array('','<span class="rvts8">'.$this->ca->lang_l('settings saved').'</span>');
		}
		else
		{
			$table_data[]=array(
					$this->ca->lang_l('language'),
					Builder::buildSelect('lang',$this->f->names_lang_sets,strtoupper($this->f->ca_settings['language']))
					);
			$table_data[]=array(
					$this->ca->lang_l('set tzone'),
					'<input class="input1" name="tzone_offset" type="text" value="'.$this->f->ca_settings['tzone_offset'].'" size="3">'
					);
			$temp_ar=array(
					'admin'=>$this->ca->lang_l('administration panel'),
					'page'=>$this->ca->lang_l('page'),'profile'=>$this->ca->lang_l('profile')
					);
			$table_data[]=array(
					$this->ca->lang_l('redirect login'),
					Builder::buildSelect('login_redirect_option',$temp_ar,$this->f->ca_settings['login_redirect_option'])
					);
			$temp_ar=array(
					'0'=>$this->ca->lang_l('site map'),
					'1'=>$this->ca->lang_l('profile')
					);
			$table_data[]=array(
					$this->ca->lang_l('landing page'),
					Builder::buildSelect('landing_page',$temp_ar,$this->f->ca_settings['landing_page'])
					);
			$table_data[]=array(
					$this->ca->lang_l('redirect page'),
					'<input class="input1" type="text" name="logout_redirect_url" style="width:350px" value="'.$this->f->ca_settings['logout_redirect_url'].'"><br><br>
					<span class="rvts8 a_editnotice">'.$this->ca->lang_l('redirect page msg').'</span>'
					);

			if($this->f->ca_fullscreen)
				$table_data[]=array(
					$this->ca->lang_l('analytics client id'),
					'<input style="width:90%" type="text" name="ga_clientID" value="'.$this->f->ca_settings['ga_clientID'].'">');

			$table_data[]=array(
				$this->ca->lang_l('users back-end'),
				Builder::buildCheckbox('sr_enable',$this->f->ca_settings['sr_enable'],$this->ca->lang_l('sr_enable')).F_BR.
				Builder::buildCheckbox('users_seecounter',$this->f->ca_settings['sr_users_seecounter'],$this->ca->lang_l('users_seecounter')).F_BR.
				Builder::buildCheckbox('sr_users_see_all',$this->f->ca_settings['sr_users_see_all'],$this->ca->lang_l('sr_users_see_all')).F_BR.
				Builder::buildCheckbox('stat_hide_ips',$this->f->ca_settings['stat_hide_ips'],$this->ca->lang_l('stat_hide_ips')).F_BR.
				Builder::buildCheckbox('show_orders',$this->f->ca_settings['show_orders'],$this->ca->lang_l('show orders')).F_BR.
				Builder::buildCheckbox('show_sitemap',$this->f->ca_settings['show_sitemap'],$this->ca->lang_l('show sitemap')).F_BR.
				Builder::buildCheckbox('pwchange_enable',$this->f->ca_settings['pwchange_enable'],$this->ca->lang_l('pwchange_enable')).F_BR.
				Builder::buildCheckbox('profilechange_enable',$this->f->ca_settings['profilechange_enable'],$this->ca->lang_l('profilechange_enable')).F_BR.
				Builder::buildCheckbox('cookie_login',$this->f->ca_settings['cookie_login'],$this->ca->lang_l('cookie_login')).F_BR.
				Builder::buildCheckbox('usr_blocking',$this->f->ca_settings['usr_blocking'],$this->ca->lang_l('usr_blocking')).F_BR.
				Builder::buildCheckbox('monitor_users',$this->f->ca_settings['monitor_users'],$this->ca->lang_l('monitor_users')).F_BR.
				Builder::buildCheckbox('protect_footer',$this->f->ca_settings['protect_footer'],$this->ca->lang_l('protect_footer')).F_BR.
				Builder::buildCheckbox('protect_downloads',$this->f->ca_settings['protect_downloads'],$this->ca->lang_l('protect_downloads')).F_BR
				);

			$end=$this->form_buttons('submit');
		}
		$buttons=$this->settings_buttons();
		$output=$this->f->navtop.$buttons.$this->f->navend.'<br class="ca_br" />';
		$output.=Builder::addEntryTable($table_data,$end,'','',false,'','',
				'<form action="'.$this->ca->ca_abs_url.'?process=settings" method="post">');
		$this->output($output,' - '.$this->ca->lang_l('settings'));
	}

	protected function ca_get_msg_tpl_macros($tpl,$use_ca_user_flds=false)
	{
		$for_admin=false;
		$macros=array();

		switch($tpl)
		{
			case 'sr_notif_msg':
				$macros['perm']=array('%user_details%','%reg_id%');
				$for_admin=true;
				break;
			case 'sr_email_msg':
				$macros['perm']=array('%confirmlink%','%confirmurl%');
				break;
			case 'sr_forgotpass_msg':
				$macros['perm']=array('%newpassword%');
				break;
			case 'sr_forgotpass_msg0':
				$macros['perm']=array('%confirmlink%','%confirmurl%');
				break;
			case 'sr_activated_msg':
				$macros['perm']=array();
				break;
			case 'sr_blocked_msg':
				$macros['perm']=array();
				break;
			case 'sr_created_account_msg':
				$macros['perm']=array('generated_password');
				break;
			case 'sr_admin_activated_msg':
				$macros['perm']=array('%adminlink%');
				break;
			default: $macros['perm']=array();
				break;
		}

	//merge the common macros with the permanent ones
		$macros['perm']=array_unique(array_merge($macros['perm'],Formatter::parseMailMacros(NULL,NULL,NULL,true)));
		if(!$for_admin)
		{
			$excluded_ca_admin_macros=array('%whois%');
			foreach($macros['perm'] as $k=> $v)
				if(in_array($v,$excluded_ca_admin_macros))
					unset($macros['perm'][$k]);
		}

		if($use_ca_user_flds)
		{
			$excluded_ca_fields=array('password','self_registered','self_registered_id','avatar','status','display_name','confirmed');
			foreach($this->f->ca_users_fields_array as $k=> $v)
				if(!in_array($k,$excluded_ca_fields))
					$macros['usr_flds'][]='%'.$k.'%';
		}
		return $macros;
	}

	protected function ca_buld_macros_select($tpl,$use_ca_user_flds=false)
	{
		$macros=array('--  '.$this->ca->lang_l('select macro').' --');
		$tpl_macros=$this->ca_get_msg_tpl_macros($tpl,$use_ca_user_flds);
		$macros=array_merge($macros,$tpl_macros['perm'],array('-2'=>'----------------'),$tpl_macros['usr_flds']);
		$macros=str_replace('%','',$macros);
		return Builder::buildSelect('macros_'.$tpl,$macros,0,'','key','',' class="input1 macros_dropdown"');
	}

	public function registration_language_settings()
	{
		$end='';
		$cur_lang=(isset($_GET['sr_lang'])?$_GET['sr_lang']:'EN');
		$all_reg_fields_array=array_merge($this->f->ca_users_fields_array,array('repeat password'=>0,'code'=>1));
		$visible_system_regFields=array('email','username','password','repeat password','code');

		if(isset($_POST['submit']))
		{
			$post_lang=$_POST['language'];
			foreach($this->ca->ca_reg_lang_settings_keys as $k=> $v)
			{
				if($v=='repeat password'||$v=='want to receive notification')
					$setting_v=$_POST[str_replace(' ','_',$v)];
				else
					 $setting_v=(isset($_POST[$v]))?trim($_POST[$v]):'';
				ca_labels::insert_label(array('lkey'=>$v,'lval'=>$setting_v,'lang'=>$post_lang));
			}
			foreach($all_reg_fields_array as $k=> $v)
			{
				if(in_array($k,$visible_system_regFields) || in_array($v['itype'],$this->ca->ca_user_fieldtypes))
				{
					$kr=str_replace(' ','_',$k);
					$val=isset($_POST[$kr])?trim($_POST[$kr]):'';
					ca_labels::insert_label(array('lkey'=>$k,'lval'=>$val,'lang'=>$post_lang));
				}
			}

			$table_data[]=array('','<span class="rvts8">'.$this->ca->lang_l('settings saved').'</span>');
			$this->ca->ca_update_language_set();
		}
		else
		{
			$lang_set_sr=File::readLangSet($this->ca->ca_lang_set_fname,$cur_lang,'ca');
			$sr_lang_l=(isset($lang_set_sr['lang_l']))?$lang_set_sr['lang_l']:$this->ca->ca_lang_l;

			$lang_labels=ca_labels::fetch_labels($cur_lang);
			foreach($this->ca->ca_reg_lang_settings_keys as $k=> $v)
			{
				if(isset($lang_labels[$v]))
					$sr_lang_l[$v]=Formatter::unEsc($lang_labels[$v]);
			}

			$input='<input class="input1" type="text" name="%s" value="%s" style="'.$this->ca->inp_width.'" maxlength="250">';
			$area='<textarea class="input1" name="%s" cols="35" rows="7" style="'.$this->ca->inp_width.'">%s</textarea>%s';
			$jstring='onchange="document.location=\''.$this->ca->ca_abs_url.'?process=confreglang&amp;sr_lang=\' + this.options[this.selectedIndex].value;"';

			$lang_sets=array();
			foreach($this->f->inter_languages_a as $k=> $v)
				$lang_sets[$v]=$this->f->names_lang_sets[$v];
			if(!isset($lang_sets['EN']))
				$lang_sets['EN']='English';

			$table_data[]=array($this->ca->lang_l('edit_language'),Builder::buildSelect("language",$lang_sets,$cur_lang,'','key',$jstring));
			$labels=array('em_reg_subject','em_reg_msg','em_reg_notify_subject','em_reg_notify_msg','em_confirm_subject',
					'em_confirm_msg','em_forgotpwd_subject','em_forgotpwd_msg','em_activation_subject',
					'em_activation_msg','em_admin_activated_subject','em_admin_activated_msg','em_blocked_subject','em_blocked_msg','em_created_account_subject','em_created_account_msg');
			$innova_def=$innova_js='';
			Editor::getEditor('english',$this->ca->ca_site_url,false,'',$innova_def,$innova_js,0,'en',$this->ca->ca_site_url);
			$this->ca->ca_dependencies[]=$innova_js;
			foreach($this->ca->ca_reg_lang_settings_keys as $k=> $v)
			{
				if(array_key_exists($v,$sr_lang_l))
				{
					$label=$this->ca->lang_l($labels[$k]);
					$setting_value=str_replace('##','<br>',Formatter::sth($sr_lang_l[$v]));
					$area_note='';
					$is_area=($v=='sr_notif_msg'||$v=='sr_email_msg'||$v=='sr_forgotpass_msg'||$v=='sr_forgotpass_msg0'||$v=='sr_activated_msg'||$v=='sr_blocked_msg'||$v=='sr_created_account_msg'
						||$v=='sr_admin_activated_msg');
					if($is_area)
					{
						$area1=str_replace('<textarea class="input1"','<textarea class="input1 mceEditor"'.' id="txtContent_'.$v.'"',$area);
						$editor_parsed=str_replace(array('oEdit1','450px','htmlarea'),array('oEdit1_'.$v,'250px','txtContent_'.$v),$innova_def);
					}
					$hint=$is_area?$editor_parsed.F_BR.$this->ca_buld_macros_select($v,true):'';
					$table_data[]=array($label,sprintf($is_area?$area1:$input,$v,$setting_value,$area_note).$hint);
				}
			}

			foreach($all_reg_fields_array as $k=>$v)
			{
				if(in_array($k,$visible_system_regFields))
				{
					 $label=isset($lang_labels[$k])?$lang_labels[$k]:'';
					 $kr=str_replace(' ','_',$k);
					 $table_data[]=array($this->ca->lang_l($k),sprintf($input,$kr,$label));
				}
				elseif(in_array($v['itype'],$this->ca->ca_user_fieldtypes))
				{
					 $label=isset($lang_labels[$k])?$lang_labels[$k]:$this->ca->lang_l($k);
					 $table_data[]=array($this->ca->lang_l($k),sprintf($input,$k,$label));
				}

			}
			$end=$this->form_buttons('submit');
		}
		$buttons=$this->settings_buttons();
		$output=$this->f->navtop.$buttons.$this->f->navend.'<br class="ca_br" />
			 <div style="text-align:left">
				<form method="post" action="'.$this->ca->ca_abs_url.'?process=confreglang">'
				.Builder::addEntryTable($table_data,$end).'
				</form>
			</div>';
		$this->ca->ca_scripts='$(document).ready(function(){
			$(".macros_dropdown").change(function(){
				var $tar=$(this).attr("id").substr(7,$(this).attr("id").length);
				$txt=$(this).find(":selected").text();
				if($txt != "##") $txt="%"+$txt+"%";
				'.($this->f->tiny?'
				var active_editor=tinymce.activeEditor;
				var textarea_id="txtContent_"+$tar;
				if(active_editor.id==textarea_id)
					active_editor.execCommand("mceInsertContent", false, $txt);
				':
				'var editor="oEdit1_"+$tar;
				var obj = oUtil.obj;
				if(obj.oName==editor) obj.insertHTML($txt);
				').'
				$(this).val(0);
			});
		});';

		$this->output($output,' - '.$this->ca->lang_l('language'));
	}

	public function registration_settings()
	{
		global $db;

		$selected_radio=-1;
		$input='<input class="input1" type="text" name="%s" value="%s" style="'.$this->ca->inp_width.'" maxlength="255">'.F_BR;

		CA::fetchDBSettings($db);
		$table_data=$access=array();
		$end='';
		$saving=isset($_POST['save'])||isset($_POST['saveandapply']);

		if(!$saving)
		{
			if(isset($this->f->ca_settings['sr_access'])&&$this->f->ca_settings['sr_access']!='')
				$expl_access=explode('|',$this->f->ca_settings['sr_access']);
			if(isset($expl_access))
			{
				if($expl_access[0]!='from_group')
				{
					foreach($expl_access as $k=> $v)
					{
						$t=explode('%%',$v);
						$page_level_str=Formatter::GFS($v,'(',')');
						if(!empty($page_level_str))
							$t[1]=str_replace('('.$page_level_str.')','',$t[1]);
						if($t[1]=='2')
						{
							$page_level_arr=explode(';',$page_level_str);
							foreach($page_level_arr as $vv)
							{
								$value=explode('%',$vv);
								$access[]=array('section'=>$t[0],'page_id'=>$value[0],'access_type'=>$value[1]);
							}
						}
						else
							$access[]=array('section'=>$t[0],'access_type'=>$t[1],'page_id'=>0);
					}
				}
				else
				{
					$access=unserialize($expl_access[2]);
					$selected_radio=$expl_access[1];
				}
			}

			$admin_email_value=(isset($_GET['admin_email'])?$_GET['admin_email']:$this->f->ca_settings['sr_admin_email']);
			$admin_mail_line=sprintf($input,'admin_email',$admin_email_value).F_BR.CA::formatNotice($this->ca->lang_l('confreg_msg2'));
			$table_data[]=array($this->ca->lang_l('admin email'),$admin_mail_line);
			$table_data[]=array('',Builder::buildCheckbox('require_approval',$this->f->ca_settings['sr_require_approval'],$this->ca->lang_l('require_approval')));
			$table_data[]=array('',Builder::buildCheckbox('emial_require_approval',$this->f->ca_settings['sr_emial_require_approval'],$this->ca->lang_l('emial_require_approval')));
			$table_data[]=array('',Builder::buildCheckbox('notify_profilechange',$this->f->ca_settings['notify_profilechange'],$this->ca->lang_l('notify on profile change')));

			$fb_login_label='
				<span class="rvts8 a_editcaption">'.$this->ca->lang_l('facebook login').'</span>
				<div id="fb_settings">'.
				'<p><span class="rvts8 a_editcaption">'.$this->ca->lang_l('app id:').'</span></p>'.
				Builder::buildInput('fb_key',$this->f->ca_settings['fb_key'],$this->ca->inp_width).
				'<p><span class="rvts8 a_editcaption">'.$this->ca->lang_l('app secret:').'</span></p>'.
				Builder::buildInput('fb_secret',$this->f->ca_settings['fb_secret'],$this->ca->inp_width,'','text','','','').
				'</div>';
			$table_data[]=array('',
				 Builder::buildCheckbox('fb_login',$this->f->ca_settings['fb_login'],$fb_login_label));

			$auto_login_timers=array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10);
			$auto_login_label=str_replace(array('%%time%%','%%location%%'),array(
				Builder::buildSelect('auto_login_redirect_time',$auto_login_timers,$this->f->ca_settings['auto_login_redirect_time']),
				Builder::buildInput('auto_login_redirect_loc',$this->f->ca_settings['auto_login_redirect_loc'],$this->ca->inp_width,'','text','','','','auto_login_redirect_loc')
				),$this->ca->lang_l('auto_login_label'));
			$table_data[]=array($this->ca->lang_l('after confirmation'),Builder::buildCheckbox('auto_login',$this->f->ca_settings['auto_login'],$auto_login_label));


			$select_all_flag=empty($access)||$access[0]['section']=='ALL';
			$checked_all_read=($select_all_flag&&!empty($access)&&$access[0]['access_type']=='0')&&$selected_radio==-1;
			$checked_all_write=(!empty($access)&&$access[0]['section']=='ALL'&&$access[0]['access_type']=='1')&&$selected_radio==-1;
			$checked_selected=(!empty($access)&&$access[0]['section']!='ALL')&&$selected_radio==-1;

			$selected_sec_ids=$selected_sec_access=array();
			if(!empty($access)&&$selected_radio==-1)
			{
				foreach($access as $k=> $v)
				{
					$selected_sec_ids[]=$v['section'];
					$selected_sec_access[]=($v['page_id']=='0'?$v['access_type']:'2');
				}
			}
			$access_line='
				<input type="radio" class="xselect" id="select_all_read" name="select_all" value="yes" '.($checked_all_read?'checked="checked"':'').' onclick="$(\'#selected_holder\').hide();">
				<span class="rvts8">'.$this->ca->lang_l('view all').' <span style="font-size:80%"> ('.$this->ca->lang_l('adduser_msg2').')</span></span><br>
				<input type="radio" class="xselect" id="select_all_edit" name="select_all" value="yesw" '.($checked_all_write?'checked="checked"':'').' onclick="$(\'#selected_holder\').hide();">
				 <span class="rvts8">'.$this->ca->lang_l('edit all').' <span style="font-size:80%"> ('.$this->ca->lang_l('adduser_msg3').')</span></span><br>
				<div id="selected_holder" style="display:'.($checked_selected?'block':'none').';">
					<div>'.$this->check_section_range(0,($selected_radio==-1?'none':''),$access).'</div>
				</div>'
				.$this->build_extra_access_option($selected_radio,false).'<br>
				<input type="submit" value="'.$this->ca->lang_l('apply_to_all').'" name="saveandapply">
				<div style="padding-top:10px;">'.CA::formatNotice($this->ca->lang_l('confreg_msg7')).'</div>';

			$table_data[]=array($this->ca->lang_l('access to'),$access_line);
			$table_data[]=array($this->ca->lang_l('in registration form'),
				Builder::buildCheckbox('sr_disable_captcha',$this->f->ca_settings['sr_disable_captcha'],$this->ca->lang_l('disable captcha')).F_BR.
				Builder::buildCheckbox('sr_cals_block',$this->f->ca_settings['sr_cals_block'],$this->ca->lang_l('cals_block')));

			$terms_line=sprintf($input,'terms_url',(isset($_GET['terms_url'])?$_GET['terms_url']:$this->f->ca_settings['sr_terms_url'])).F_BR.CA::formatNotice($this->ca->lang_l('confreg_msg1'));
			$table_data[]=array($this->ca->lang_l('terms url'),$terms_line);

			$notes_line='<textarea class="input1" name="notes" style="'.$this->ca->inp_width.'" cols="20" rows="5">'.(isset($_GET['notes'])?$_GET['notes']:$this->f->ca_settings['sr_notes']).'</textarea>'.F_BR
				.F_BR.CA::formatNotice($this->ca->lang_l('confreg_msg5'));
			$table_data[]=array($this->ca->lang_l('notes'),$notes_line);

			$confirm_line='<textarea class="input1" name="confirm_message" style="'.$this->ca->inp_width.'" cols="20" rows="5">'.(isset($_GET['confirm_message'])?$_GET['confirm_message']:$this->f->ca_settings['sr_confirm_message']).'</textarea>'.F_BR
				.F_BR.CA::formatNotice($this->ca->lang_l('confreg_msg6'));
			$table_data[]=array($this->ca->lang_l('confirm_message'),$confirm_line);

			$end=$this->form_buttons();
			$this->ca->ca_scripts.='
				$(document).ready(function() {
					 check_inputs();
					 $(".xselect,#select_all_from_grp,#grp_selector").change(function() {check_inputs();});
				});
				';
		}
		else
		{
			$require_app=(isset($_POST['require_approval'])?$_POST['require_approval']:'0');

			$sections=array();
			if(isset($_POST["select_all"])&&$_POST["select_all"]=='no')
			{
				$page_access_arr=array();
				$section_range=$this->ca->sitemap->ca_get_prot_pages_list(true);
				foreach($section_range as $val)
				{
					$pid=$val['id'];
					if(isset($_POST["access_to_page".$pid]))
						$page_access_arr[]=$pid.'%'.Formatter::stripTags($_POST["access_to_page".$pid]);
				}
				if(!empty($page_access_arr))
					$page_access_str=implode(';',$page_access_arr);
				$sections[]='0%%2'.(!empty($page_access_str)?'('.$page_access_str.')':'');
			}
			elseif(isset($_POST["select_all"]) && $_POST["select_all"]=='yesw')
			{
				$sections []="ALL%%1";
				$require_app='1';
			} //ALL-write
			elseif(isset($_POST["select_all"]) && $_POST["select_all"]=='from_group')
			{
				$grp_id=(int)$_POST['move_to_grp'];
				$grp_access=$db->query_singlevalue('SELECT custom_data FROM '.$db->pre.'ca_users_groups WHERE id='.$grp_id);
				$sections []="from_group|$grp_id|$grp_access";
				$grReadOnly=$this->ca->groups->groupReadOnlyAccess(array('custom_data'=>$grp_access));
				if(!$grReadOnly) $require_app='1'; //edit acess may occur, admin must approve such access
			}
			else
				$sections[]="ALL%%0"; //ALL-read

	//if req_app is active, no auto-login. Cannot auto-login without being confirmed by admin
			$auto_login=isset($_POST['auto_login'])&&!$require_app?$_POST['auto_login']:'0';
			$auto_login_redirect_time=isset($_POST['auto_login_redirect_time'])?$_POST['auto_login_redirect_time']:5;
			$auto_login_redirect_loc=isset($_POST['auto_login_redirect_loc'])?$_POST['auto_login_redirect_loc']:'';
			$fb_login=isset($_POST['fb_login'])&&!$require_app?$_POST['fb_login']:'0';
			$fb_key=isset($_POST['fb_key'])?$_POST['fb_key']:'';
			$fb_secret=isset($_POST['fb_secret'])?$_POST['fb_secret']:'';
			$notify_profilechange=isset($_POST['notify_profilechange'])?$_POST['notify_profilechange']:'';
			$emial_require_approval=isset($_POST['emial_require_approval'])?$_POST['emial_require_approval']:'0';

			$captcha=isset($_POST['sr_disable_captcha'])?'1':'0';
			$arr=array('sr_admin_email'=>$_POST['admin_email'],'sr_terms_url'=>$_POST['terms_url'],'sr_notes'=>$_POST['notes'],
				'sr_confirm_message'=>$_POST['confirm_message'],'sr_access'=>implode('|',$sections),'sr_require_approval'=>$require_app,
				'sr_emial_require_approval'=>$emial_require_approval,
				'sr_disable_captcha'=>$captcha,
				'auto_login'=>$auto_login,'auto_login_redirect_time'=>$auto_login_redirect_time,
				'auto_login_redirect_loc'=>$auto_login_redirect_loc,
				'fb_login'=>$fb_login,
				'fb_key'=>$fb_key,
				'fb_secret'=>$fb_secret,
				'notify_profilechange'=>$notify_profilechange
				);
			CA::insert_setting($arr);
			CA::fetchDBSettings($db);

			if(isset($_POST['saveandapply']))
			{
				$db->query('DELETE FROM '.$db->pre.'ca_users_access');
				$users_data=$db->fetch_all_array('SELECT uid FROM '.$db->pre.'ca_users WHERE confirmed=1');
				foreach($users_data as $k=> $v)
					$this->ca->users->set_useraccess($v['uid']);

				$table_data[]=array('','<span class="rvts8">Access applied to all existing users.</span>');
			}
			else
				$table_data[]=array('','<span class="rvts8">'.$this->ca->lang_l('settings saved').'</span>');
		}
		$buttons=$this->settings_buttons();
		$output=$this->f->navtop.$buttons.$this->f->navend.'<br class="ca_br" />';
		$output.=Builder::addEntryTable($table_data,$end,'','',false,'',$this->ca->ca_abs_url,
				'<form name="frm" action="'.$this->ca->ca_abs_url.'?process=confreg'.$this->ca->ca_l_amp.'" method="post">');

		$this->output($output,' - '.$this->ca->lang_l('registration settings'));
	}

	protected function get_field_line($type,$name,$value,$req,$hidinprof,$hidinreg)
	{
		$input='<input class="input1" type="text" id="%s" name="%s" value="%s" style="width:300px">';
		$memo='<textarea class="input1" id="%s" name="%s" value="%s" style="width:300px"></textarea>';
		$move_btns=' <input type="button" value=" &uarr; " onclick="moveUpRow(\'div_%s\')">
			 <input type="button" value=" &darr; " onclick="moveDownRow(\'div_%s\')">';
		$required='<input type="checkbox" name="required_%s" '.($req?'checked="checked"':'').' value="1">
			 <span class="rvts8 a_editcaption" style="font-size: 10px">'.$this->ca->lang_l('required').'</span>';
		$profile_hidden='<input type="checkbox" name="hidinprof_%s" '.($hidinprof?'checked="checked"':'').' value="1">
			 <span class="rvts8 a_editcaption" style="font-size: 10px">'.$this->ca->lang_l('hide in profile').'</span>';
		$reg_hidden='<input type="checkbox" name="hidinreg_%s" '.($hidinreg?'checked="checked"':'').' value="1">
			 <span class="rvts8 a_editcaption" style="font-size: 10px">'.$this->ca->lang_l('hide on registration').'</span>';

		$move='<div style="float:right;display:inline">
					 <input type="button" value=" '.$this->ca->lang_l('remove').' " onclick="$(\'#div_%s\').html(\''.$this->ca->lang_l('remove').'\');">'
					 .$move_btns.'
				</div>';
		$move2='<div style="float:right;display:inline">'.$move_btns.'</div>';
		$ft='<input type="hidden" name="%s" value="%s">';
		$res='';

		$dname=$this->ca->lang_l($name);
		if($dname!=$name)
			$dname.=' ('.$name.')';
		if($type=="userinput")
			$res=array($dname,'<div id="div_'.$name.'">'.sprintf($input,$name,'field_'.$name,'')
				.sprintf($required,$name).sprintf($profile_hidden,$name)
				.sprintf($reg_hidden,$name)
				.sprintf($move,$name,$name,$name)
				.sprintf($ft,'ftype_'.$name,'userinput').'</div>');
		elseif($type=="memo")
			$res=array($dname,'<div id="div_'.$name.'">'.sprintf($memo,$name,'field_'.$name,'')
				.sprintf($required,$name).sprintf($profile_hidden,$name)
				.sprintf($reg_hidden,$name)
				.sprintf($move,$name,$name,$name)
				.sprintf($ft,'ftype_'.$name,'memo').'</div>');
		elseif($type=="checkbox")
			$res=array('','<div id="div_'.$name.'">'.sprintf($ft,'field_'.$name,'')
				 .'<input type="checkbox" name="'.$name.'" value="1">
						<span style="width:278px;display:inline-block;" class="rvts8 a_editcaption">'.$dname.'</span>'
				 .sprintf($required,$name).sprintf($profile_hidden,$name)
				 .sprintf($reg_hidden,$name)
				 .sprintf($move,$name,$name,$name).sprintf($ft,'ftype_'.$name,'checkbox').'</div>');
		elseif($type=="avatar")
		{
			$av_path=$this->ca->getAvatarPath('');
			$ima='<br>
				 <img id="ima_avatar" src="'.$av_path.'" alt="" style="'.(($av_path=='')?'display:none;':'').'height:'.$this->f->avatar_size.'px;padding-top: 5px;">';

			$res=array($dname,'
				<div id="div_'.$name.'">
					<div style="dispplay:inline;float:left">
						 <input class="input1" type="text" id="'.$name.'" name="field_'.$name.'" value="'.$av_path.'" style="width:238px">
						 <input type="button" value="'.$this->ca->lang_l('browse').'" onclick="openAsset(\''.$name.'\')" id="btnAsset2" name="btnAsset2" class="input1">'
						 .$ima.'
					</div>'
					.sprintf($ft,$name,'0')
					.sprintf($required,$name)
					.sprintf($move2,$name,$name)
					.sprintf($ft,'ftype_'.$name,'avatar').'
				</div>');
		}
		elseif($type=="listbox")
		{
			$data=explode(';',$value);
			$html='<div id="div_'.$name.'">'.Builder::buildSelect('field_'.$name,$data,'','style="width:302px"','value')
				.sprintf($required,$name)
				.sprintf($profile_hidden,$name).sprintf($move,$name,$name,$name)
				.sprintf($reg_hidden,$name)
				.'<br><span class="rvts8 a_editcaption">'.$this->ca->lang_l('values').'</span><br>'.sprintf($input,'nfv_'.$name,'nfv_'.$name,$value).'
				<input type="button" value=" '.$this->ca->lang_l('update').' " onclick="updateRegField(\''.$name.'\',\''.$this->ca->ca_abs_url.'\',\''.$this->f->atbg_class.'\');">'.sprintf($ft,'ftype_'.$name,'listbox').'</div>';
			$res=array($dname,$html);
		}
		elseif($type==""&&$name!='self_registered_id'&&$name!='display_name')
			$res=array($dname,'<div id="div_'.$name.'">'.sprintf($input,$name,'field_'.$name,'').sprintf($move2,$name,$name).'</div>');
		else
			$res='<input type="hidden" name="field_'.$name.'" value=""><input type="hidden" name="required_'.$name.'" value="1">';
		return $res;
	}

	public function get_regfield()
	{
		print Builder::getEntryTableRows(array($this->get_field_line($_REQUEST['ft'],$_REQUEST['nfn'],$_REQUEST['nfv'],true,false,false)));
	}

	public function ca_update_db_fields($fields_array)
	{
		global $db;

		$cur_field_names=$db->db_fieldnames('ca_users');

		foreach($fields_array as $k=> $v)
		{
			if(!in_array($k,$cur_field_names))
				$db->query('ALTER TABLE '.$db->pre.'ca_users ADD '.$k.' '.$v['type'].' '.$v['opt']);
		}
		foreach($cur_field_names as $k=> $v)
			if(!array_key_exists($v,$fields_array))
			{
				if(isset($this->f->ca_users_fields_array[$v]) && $this->f->ca_users_fields_array[$v]['system']=='1')
					break;
				elseif($v=='pass_changeable' || $v=='expired_date')
					continue; //skip this field and go to the next one (pass_changer flag)
				else
					$db->query('ALTER TABLE '.$db->pre.'ca_users DROP '.$v);
			}
		$this->f->ca_users_fields_array=$fields_array;
		$s=serialize($fields_array);
		$this->f->ca_settings['customcode']=$_POST['customcode'];
		$this->f->ca_settings['customcodeprofile']=$_POST['customcodeprofile'];
		CA::insert_setting(array('user_fields'=>$s,'customcode'=>$_POST['customcode'],'customcodeprofile'=>$_POST['customcodeprofile']));
	}

	public function registration_fields()
	{
		if(count($_POST)>0)
		{
			$users_fields_array=array();
			foreach($_POST as $k=> $v)
			{
				if(strpos($k,'field_')===0)
				{
					$fname=Formatter::GFS($k,'field_','');
					$req=isset($_POST['required_'.$fname]);
					$hidInProf=isset($_POST['hidinprof_'.$fname])&&$_POST['hidinprof_'.$fname]=='1';
					$hidInReg=isset($_POST['hidinreg_'.$fname])&&$_POST['hidinreg_'.$fname]=='1';
					if(isset($this->f->ca_users_fields_array[$fname])) //existing
					{
						$field=$this->f->ca_users_fields_array[$fname];
						$field['req']=($req)?'1':'0';
						$field['hidinprof']=$hidInProf?'1':'0';
						$field['hidinreg']=$hidInReg?'1':'0';
						if($field["itype"]=='listbox')
							$field["values"]=$_POST['nfv_'.$fname];
						elseif($field["itype"]=='avatar')
							CA::insert_setting(array('c_avatar'=>$_POST[$k]));
						$users_fields_array[$fname]=$field;
					}
					else //new field
					{
						$ftype=$_POST['ftype_'.$fname];
						$field=array(
								"display"=>$fname,
								"itype"=>$ftype,
								"system"=>0,
								"hidinprof"=>($hidInProf?'1':'0'),
								"hidinreg"=>($hidInReg?'1':'0'),
								"req"=>($req?'1':'0'),
								"type"=>'varchar(255)',
								"opt"=>"NOT NULL default ''"
								);

						if($ftype=='checkbox')
						{
							$field["type"]='tinyint(1)';
							$field["opt"]='';
						}
						elseif($ftype=='memo')
						{
							$field["type"]='text';
							$field["opt"]='';
						}
						elseif($ftype=='listbox')
							$field["values"]=$_POST['nfv_'.$fname];

						$users_fields_array[$fname]=$field;
					}
				}
			}
			$this->ca_update_db_fields($users_fields_array);
		}

		$input='<input id="nfv" class="input1" type="text" name="%s" value="%s" style="width:300px">';
		$hidden='';

		foreach($this->f->ca_users_fields_array as $k=> $v)
		{

			$req=isset($v['req'])&&($v['req']=='0')?false:true;
			$hidInProf=isset($v['hidinprof'])&&($v['hidinprof']=='1')?1:0;
			$hidinreg=isset($v['hidinreg'])&&($v['hidinreg']=='1')?1:0;
			$val=isset($v['values'])?$v['values']:'';
			$r=$this->get_field_line($v['itype'],$k,$val,$req,$hidInProf,$hidinreg);
			if(is_array($r))
				$table_data[]=$r;
			else
				$hidden.=$r;
		}
		$end=$hidden.$this->form_buttons('submit',true,'onclick="document.location=\''.$this->ca->ca_abs_url.'?process=regfields\'"');
		$field_types=array(
			 'userinput'=>$this->ca->lang_l('text'),
			 'checkbox'=>$this->ca->lang_l('checkbox'),
			 'listbox'=>$this->ca->lang_l('listbox'),
			 'memo'=>$this->ca->lang_l('memo'));//,
		$table_data[]=array('','<div class="empty" style="border-top:1px dashed #000;"></div>');
		$table_data[]=array($this->ca->lang_l('new field'),
			 '<input id="nfn" class="input1" type="text" onkeydown="f(this)" onkeyup="f(this)" onblur="f(this)" onclick="f(this)" name="nfn" value="" style="width:300px"> '.
			  Builder::buildSelect('ft',$field_types,'').'
				<input type="button" value=" '.$this->ca->lang_l('add').' " onclick="addRegfield(\''.$this->ca->ca_abs_url.'\',\''.$this->f->atbg_class.'\');"><br>
				<span class="rvts8 a_editcaption">'.$this->ca->lang_l('values').'</span><br>'.sprintf($input,'nfv',''));
		$table_data[]=array($this->ca->lang_l('custom code'),'<textarea name="customcode" class="input1" style="width:60%">'.$this->f->ca_settings['customcode'].'</textarea>');
		$table_data[]=array($this->ca->lang_l('custom code profile'),'<textarea name="customcodeprofile" class="input1" style="width:60%">'.$this->f->ca_settings['customcodeprofile'].'</textarea>');

		$this->ca->ca_scripts='$(document).ready(handleRegAvatar);';

		$buttons=$this->settings_buttons();
		$output=$this->f->navtop.$buttons.$this->f->navend.'<br class="ca_br">';
		$output.=Builder::addEntryTable($table_data,$end,'','',false,'','',
				'<form action="'.$this->ca->ca_abs_url.'?process=regfields" method="post">');
		$this->output($output,' - '.$this->ca->lang_l('settings'));
	}

	public function counter_settings()
	{
		global $db;

		$total=0;
		$counter_pages_raw=$db->fetch_all_array('
			SELECT page_id,count(*) as total
			FROM '.$db->pre.'counter_details
			GROUP BY page_id');
		if(is_array($counter_pages_raw))
			foreach($counter_pages_raw as $v)
			{
				$db->query_update('counter_pageloads',array('total'=>$v['total']),'page_id='.$v['page_id']);
				$total=$total+intval($v['total']);
			}
		$db->query_update('counter_totals',array('total'=>$total),'total_type="loads"');

		$visit_len_list=array('1800'=>'30 min','3600'=>'1 h','7200'=>'2 h','10800'=>'3 h','216000'=>'6 h','432000'=>'12 h','864000'=>'24 h');
		$number_digits_list=array(4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10);
		$show_list=array('show unique visitors','show pageloads');
		$counter_type=array('text','graphical');

		CA::fetchDBSettings($db);
		$set=$this->f->ca_settings;
		$table_data=array();
		$end='';
		if(!isset($_POST['save']))
		{
			$counter_size=(isset($_GET['size'])?$_GET['size']:$set['c_size']);

			$table_data[]=array($this->ca->lang_l('display'),Builder::buildSelect('display',$show_list,(isset($_GET['display'])?$_GET['display']:$set['c_display'])));
			$table_data[]=array($this->ca->lang_l('number of digits'),Builder::buildSelect('number_digits',$number_digits_list,(isset($_GET['num_digits'])?$_GET['num_digits']:$set['c_number_digits'])));
			$table_data[]=array($this->ca->lang_l('maximum visit length'),Builder::buildSelect('max_visit_len',$visit_len_list,(isset($_GET['v_length'])?$_GET['v_length']:$set['c_max_visit_len'])));
			$table_data[]=array($this->ca->lang_l('unique start offset'),Builder::buildInput('u_st_count',(isset($_GET['u_offset'])?$_GET['u_offset']:$set['c_unique_start_count']),'','','text','size="10"'));
			$table_data[]=array($this->ca->lang_l('pageloads start offset'),Builder::buildInput('l_st_count',(isset($_GET['l_offset'])?$_GET['l_offset']:$set['c_loads_start_count']),'','','text','size="10"'));
			$table_data[]=array($this->ca->lang_l('counter type'),Builder::buildSelect('graphical',$counter_type,(isset($_GET['graphical'])?$_GET['graphical']:$set['c_graphical'])));

			$counter_type='';
			$inp='<div style="text-align:left;height:25px;padding-left:10px;">
					 <input type="radio" name="size" value="%s" %s><img style="position:absolute;" src="'.$this->ca->ca_site_url.'ezg_data/c%s.gif" alt="">
					</div>';
			$cnt=count($this->f->counter_images)+1;
			for($i=1; $i<$cnt; $i++)
				$counter_type.=sprintf($inp,$i,($counter_size==$i)?'checked="checked"':'',$i);
			$table_data[]=array('',$counter_type);
			$end=$this->form_buttons();
			$end.='<div style="text-align: right"><input type="button" value=" '.$this->ca->lang_l('reset counter').' " onclick="document.location=\''.$this->ca->ca_abs_url.'?process=resetcounter'.$this->ca->ca_l_amp.'\'"></div>';
		}
		else
		{
			$arr=array('c_max_visit_len'=>$_POST['max_visit_len'],'c_number_digits'=>($_POST['number_digits']),'c_size'=>$_POST['size'],'c_display'=>$_POST['display'],'c_loads_start_count'=>$_POST['l_st_count'],'c_unique_start_count'=>$_POST['u_st_count'],'c_graphical'=>$_POST['graphical']);
			CA::insert_setting($arr);
			CA::fetchDBSettings($db);
			$table_data[]=array('','<span class="rvts8">'.$this->ca->lang_l('settings saved').'</span>');
		}
		$buttons=$this->settings_buttons();
		$output=$this->f->navtop.$buttons.$this->f->navend.'<br class="ca_br" />';
		$output.=Builder::addEntryTable($table_data,$end,'','',false,'','',
				'<form name="frm" action="'.$this->ca->ca_abs_url.'?process=confcounter'.$this->ca->ca_l_amp.'" method="post">');
		$this->output($output,' - '.$this->ca->lang_l('counter settings'));
	}
}

class ca_media_library extends ca_admin_screens
{
	public function handle_screen()
	{
		$this->media_library();
	}

	protected function media_library()
	{
		$rel_path=strpos($this->ca->ca_lang_template,'/')===false?'':'../';
		define('ROOT_PATH',$rel_path);

		ob_start();
		include '../ezg_data/media_library/medialib.php';
		$mlData=ob_get_clean();
		if(!$this->f->ca_fullscreen && $rel_path=='') //<base> tag...
		{
			$mlData=str_replace('../', '', $mlData);
			$mlData=str_replace('centraladmin.php', 'documents/centraladmin.php', $mlData);
		}
		$mlHeaders=Formatter::GFS($mlData,'<head>','</head>');

		$mlData='<input id="frm_3" type="hidden"> <input id="frm_2" type="hidden">'.Formatter::GFS($mlData,'<body>','</body>');
		$output=Formatter::fmtAdminScreen($mlData, $this->build_menu());
		$output=$this->ca->GT($output,false);
		$output=str_replace('</head>',$mlHeaders.'</head>',$output);

		$output=Builder::multiboxImages($output,$this->ca->ca_site_url,true);
		print $output;
	}
}

class ca_sitemap_screen extends ca_admin_screens
{

	public function show_sitemap() // site map screen
	{
		$output=$header_text='';

		if(isset($_GET['stat']))
		{
			$x_call=isset($_GET['x'])&&($_GET['x']=='m'||$_GET['x']=='y');
			if(isset($_GET['t'])) //stats type
			{
				$type=$_GET['t']=='c'?'c':'a';
				$this->f->ca_settings['chart_type']=$type;
				CA::insert_setting(array('chart_type'=>$type));
			}

			if($_GET['stat']=='detailed')
			{
				$graphs= new ca_graphs($this->ca);
				$output=$graphs->sitemap_graphs(true,$header_text,$this->ca->ca_scripts,$this->ca->ca_dependencies,$x_call);
			}
			else
				$output=$this->visits_list($header_text);
		}
		else
		{
			$sitemap_viewer= new ca_sitemap_viewer($this->ca);
			$sitemap_viewer->build_sitemap_area($output);
		}

		$this->output($output,$header_text);
	}

	private function visits_list(&$header_text)
	{
		global $db;

		$output='';
		$pg=isset($_GET['pid'])?intval($_GET['pid']):'';
		$screen=(isset($_GET['page'])?$_GET['page']:1);
		$flt=isset($_GET['f'])?Formatter::stripTags($_GET['f']):'h';
		$pages_list=CA::getSitemap('../',false,true);

		$records_count=0;
		$que='';

		if($flt!='a'&&$flt!='h')
		{
			$que.=' AND ';
			$que.=($flt=='u')?'(visit_type="r" OR visit_type="f")':'visit_type="'.$flt.'"';
		}

		$count_raw=$db->fetch_all_array('
			SELECT COUNT(*)
			FROM '.$db->pre.'counter_details
			WHERE ('.($pg===''?'1':'page_id='.$pg).$que.' )');
		$records_count=$count_raw[0]['COUNT(*)'];

		$start=($screen-1)*Navigation::recordsPerPage();
		$max=Navigation::recordsPerPage();

		$records=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.'counter_details
			WHERE page_id '.($pg!==""?'= '.$pg:' > 1').' '.$que.'
			ORDER BY date DESC '.(($records_count>$max&&$max!=0)?'LIMIT '.$start.', '.$max.'':''));

		$purl='';
		if(isset($_GET['pid']))
			$purl=$this->ca->ca_site_url.str_replace('../','',$_GET['purl']);

		$url_part=$this->ca->ca_abs_url."?process=index&amp;stat=olddetailed".$this->ca->ca_l_amp."&amp;".(isset($_GET['f'])?"&amp;f=".Formatter::stripTags($_GET['f']):'').(isset($_GET['pid'])?"&amp;pid=".$_GET['pid']."&purl=".$purl."&pname=".$_GET['pname']:'');
		$header_text.=' - '.$this->ca->lang_l('detailed stat').' '.(isset($_GET['pid'])?' <a target="_blank" href="'.$_GET['purl'].'" title="'.$purl.'">"'.Formatter::stripTags($_GET['pname']).'"</a> '.$this->ca->lang_l('page'):'');

		$is_search=strpos($purl,'documents/search.php')!==false;

		$nav=Navigation::pageCA($records_count,$url_part,0,$screen);
		if(isset($_GET['pid']))
			$cap_arrays[]=$this->ca->lang_l('page name');
		$cap_arrays=array(
			$this->ca->lang_l('date'),$this->ca->lang_l('browser'),$this->ca->lang_l('os'),($pg===1?'Results':$this->ca->lang_l('resolution')));
		if(!$this->f->ca_settings['stat_hide_ips'])
			$cap_arrays[] = Formatter::strToUpper($this->ca->lang_l('ip')).' / '.$this->ca->lang_l('host');
		$cap_arrays[] = $this->ca->lang_l('referrer');
		if(!isset($_GET['pid']))
			$cap_arrays[]=$this->ca->lang_l('page name');
		if($is_search)
			$cap_arrays[]=$this->ca->lang_l('hit');
		$table_data=array();

		foreach($records as $v)
		{
			$fixed_date=Date::tzoneSql($v['date']);
			$ref=isset($v['referrer'])?$v['referrer']:'NA';
			$q='';
			if(strpos($ref,'q=')!==false)
			{
				$q=Formatter::GFS($ref,'q=','&');
				if($q!='') $q='<span class="rvts8">'.Formatter::GFS($ref,'q=','&').'</span>'.F_BR;
			}
			if(strpos($ref,'.google')!==false)
			{
				$refl='Google Search';
				if($q=='')
				{
					if(strpos($ref,'url=')!==false)
					{
						$q='<span class="rvts8">'.Formatter::GFS($ref,'url=','&').'</span>'.F_BR;
						$refl.=' (url)';
					}
				}
			}
			elseif(strpos($ref,'search.yahoo')!==false)
			{
				$refl='Yahoo Search';
				$q=(strpos($ref,'p=')!==false)?'<span class="rvts8">'.Formatter::GFS($ref,'p=','&').'</span>'.F_BR:'';
			}
			elseif(strpos($ref,'bing.')!==false)
				$refl='Bing Search';
			elseif(strpos($ref,'yandex.')!==false)
			{
				$refl='Yandex Search';
				$q=(strpos($ref,'text=')!==false)?'<span class="rvts8">'.Formatter::GFS($ref,'text=','&').'</span>'.F_BR:'';
			}
			elseif($ref=='/')
				$refl='home';
			else
			{
				$refa=pathinfo($ref);
				$refl=$refa['basename']==''?$ref:$refa['basename'];
				$refl=Formatter::GFS($refl,'','?');
			}

			$row_data=array('<span class="rvts8">'.date('j-M-y H:i:s',$fixed_date)."</span>",
				'<span class="rvts8">'.$this->f->browsers[$v['browser']]."</span>",'<span class="rvts8">'.$this->f->os[$v['os']]."</span>",'<span class="rvts8">'.$v['resolution']."</span>");
			if(!$this->f->ca_settings['stat_hide_ips'])
				$row_data[] = '<span class="rvts8">'.Builder::ipLocator($v['ip']).'</span>'.F_BR.'<span class="rvts8">'.$v['host'].'</span>';
			$refs=$is_search?$this->f->site_url.$ref:$ref;
			$row_data[] ='<span class="rvts8">'.($ref!='NA'?$q.'<a target="_blank" href="'.$refs.'" alt="">'.wordwrap($refl,30,"<br />\n",true).'</a>':$this->ca->lang_l('na')).'</span>';


			if(!isset($_GET['pid']))
			{
				$p_data=(isset($pages_list[$v['page_id']]))?$pages_list[$v['page_id']]:array();
				if(!empty($p_data))
				{
					$p_url=$this->ca->ca_site_url.str_replace('../','',$p_data[1]);
					$p_n=strpos($p_data[0],'#')!==false&&strpos($p_data[0],'#')==0?str_replace('#','',$p_data[0]):$p_data[0];
					$row_data[]='<a class="rvts12" href="'.$p_url.'" alt="'.$p_url.'" title="'.$p_url.'">'.$p_n.'</a>';
				}
				else
					$row_data[]='';
			}

			if($is_search)
			{
				$sign=$v['hit']==1?'+':'-';
				$rd=(isset($v['hit_link'])&&($v['hit_link']!=''))?'<b><a class="no_u" target="_blank" href="'.$v['hit_link'].'">'.$sign.'</a></b>':$sign;
				$row_data[]='<span class="rvts8">'.$rd.'</span>';
			}
			$table_data[]=$row_data;
		}
		$output.=Builder::adminTable($nav,$cap_arrays,$table_data);
		return $output;
	}
}

class ca_user_sitemap_screen extends ca_visitor_screens
{

	public function show_sitemap()
	{
		$output='';
		$sitemap_viewer= new ca_sitemap_viewer($this->ca);
		$sitemap_viewer->build_sitemap_area($output,$this->logged_user_data);
		$this->output($output);
		exit;
	}

	public function show_graphs($user_is_admin)
	{
		$header_text='';
		$graphs= new ca_graphs($this->ca);
		$output=$graphs->sitemap_graphs($user_is_admin,$header_text,$this->ca->ca_scripts,$this->ca->ca_dependencies);
		$this->output($output);
	}

}

class ca_messenger_screen extends ca_screens
{
	public $logged_user_data;
	public $isAdmin;
	public $logged_userId;
	public $fr_status_array=array();
	public $ch_settings=array();

	public function __construct($ca_object,$logged_user_data)
	{
		parent::__construct($ca_object);
		$this->logged_user_data=$logged_user_data;
		$this->fr_status_array=array($this->ca->lang_l('unconfirmed'),$this->ca->lang_l('active'),$this->ca->lang_l('blocked'));
		$this->isAdmin=$this->ca->user->isAdmin();
		$this->logged_userId=$this->isAdmin&&!$this->logged_user_data['user_admin']===1?-1:$this->logged_user_data['uid'];
		$this->fetchChatSettings();
	}

	public function output($output,$caption='')
	{
		$ca_screen_obj=$this->isAdmin?new ca_admin_screens($this->ca,$this->logged_user_data):new ca_visitor_screens($this->ca,$this->logged_user_data);
		$output=Formatter::fmtAdminScreen($output,$ca_screen_obj->build_menu($caption));
		$output=$this->ca->GT($output,false);
		parent::screen_output($output);
	}

	public function process_messenger()
	{
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if(!$this->isAdmin&&$this->ch_settings['ch_disable_messenger_users'])
			$this->show_disabled_messenger();
		if($do=='friends')
			$this->show_friends();
		elseif($do=='block'||$do=='activate'||$do=='confirm')
			$this->action_friendship($do);
		elseif($do=='delete_message')
			$this->delete_message();
		elseif($do=='send_message')
			$this->send_message();
		elseif($do=='read_message')
			$this->read_message();
		elseif($do=='settings')
			$this->show_messenger_settings();
		else
			$this->show_messages($do);
	}

	protected function fetchChatSettings()
	{
		global $db;
		
		$records=$db->fetch_all_array('SELECT * FROM '.$db->pre.'ca_messenger_settings WHERE uid=0 OR uid='.$this->logged_userId,1);
		if($records!==false)
		{
			if(!empty($records))
				foreach($records as $v)
					$this->ch_settings[$v['skey']]=$v['sval'];
		}

		if(!isset($this->ch_settings['ch_message_template']))
			$this->ch_settings['ch_message_template']='Delivered message from %username%<br/>%message%<br/>Sent from %sitename%';
		if(!isset($this->ch_settings['ch_friendship_msg_template']))
			$this->ch_settings['ch_friendship_msg_template']='Friendship request from %username%.<br/>To confirm login to %sitename% and visit messages->friends admin panel.';
		if(!isset($this->ch_settings['ch_friendship_sbj_template']))
			$this->ch_settings['ch_friendship_sbj_template']='Friendship request from %username%';
		if(!isset($this->ch_settings['ch_disable_messenger_users']))
			$this->ch_settings['ch_disable_messenger_users']=0;
		if(!isset($this->ch_settings['ch_only_friends']))
			$this->ch_settings['ch_only_friends']=1;
		if(!isset($this->ch_settings['ch_send_toadmin']))
			$this->ch_settings['ch_send_toadmin']=0;
		if(!isset($this->ch_settings['ch_send_emails']))
			$this->ch_settings['ch_send_emails']=0;
		if(!isset($this->ch_settings['ch_disable_blocked']))
			$this->ch_settings['ch_disable_blocked']=0;
		if(!isset($this->ch_settings['ch_livesearch']))
			$this->ch_settings['ch_livesearch']=0;
	}

	protected function insert_messengerSetting($data,$uid)
	{
		global $db;

		foreach($data as $k=>$v)
		{
			$exist_rec=$db->query_first('SELECT * FROM '.$db->pre.'ca_messenger_settings WHERE uid='.$uid.' AND skey="'.$k.'"');
			if(empty($exist_rec))
				$db->query_insert("ca_messenger_settings",array('uid'=>$uid,'skey'=>$k,'sval'=>$v));
			else
				$db->query_update("ca_messenger_settings",array('skey'=>$k,'sval'=>$v),'uid='.$uid.' AND skey="'.$k.'"');
		}
	}
	
	protected function show_messenger_settings()
	{
		global $db;

		$table_data=array();
		$end='';
		$innova_def=$innova_js='';
		Editor::getEditor('english',$this->ca->ca_site_url,false,'',$innova_def,$innova_js,0,'en',$this->ca->ca_site_url);
		$this->ca->ca_dependencies[]=$innova_js;
		
		$count=$this->get_newMsgFreinds();
		$url=$this->ca->ca_abs_url."?process=".($this->isAdmin?'':'my')."messenger".$this->ca->ca_l_amp;
		$nav='
			<div>
				<div style="float:left;">
					<input class="settings_tab" type="button" onclick="document.location=\''.$url.'&amp;do=received_messages\'" value="'.$this->ca->lang_l('received messages').(isset($count['new_msg'])&&$count['new_msg']>0?' ('.$count['new_msg'].')':'').'">
					<input class="settings_tab" type="button" onclick="document.location=\''.$url.'&amp;do=sent_messages\'" value="'.$this->ca->lang_l('sent messages').'">'
					.($this->isAdmin||!$this->ch_settings['ch_only_friends']?'':'
					<input type="button" class="settings_tab" value="'.$this->ca->lang_l('friends').(isset($count['new_fr'])&&$count['new_fr']>0?' ('.$count['new_fr'].')':'').'" onclick="document.location=\''.$url.'&amp;do=friends\'" />')
					.'
					<input type="button" class="settings_tab active" value="'.$this->ca->lang_l('settings').'" onclick="document.location=\''.$url.'&amp;do=settings\'">
				</div>
			</div>
			<div style="clear:both;"></div>
		';
		$table_data=array();	
		if(!isset($_POST['save']))
		{
			if($this->isAdmin)
			{
				$table_data[]=array(Builder::buildCheckbox('ch_disable_messenger_users',$this->ch_settings['ch_disable_messenger_users'],$this->ca->lang_l('ch_disable_messenger_users')));
				$table_data[]=array(Builder::buildCheckbox('ch_send_emails',$this->ch_settings['ch_send_emails'],$this->ca->lang_l('ch_send_emails')));
				$table_data[]=array(Builder::buildCheckbox('ch_only_friends',$this->ch_settings['ch_only_friends'],$this->ca->lang_l('ch_only_friends')));
				$table_data[]=array(Builder::buildCheckbox('ch_livesearch',$this->ch_settings['ch_livesearch'],$this->ca->lang_l('ch_livesearch')));
				$table_data[]=array(Builder::buildCheckbox('ch_send_toadmin',$this->ch_settings['ch_send_toadmin'],$this->ca->lang_l('ch_send_toadmin')));

				$entry='<textarea class="input1'.($this->f->tiny?' mceEditor':'').'" id="htmlarea1" name="ch_message_template" style="width:100%" rows="4" cols="30">'
				  .$this->ch_settings['ch_message_template'].'
				</textarea>'.str_replace(array('oEdit','htmlarea'),array('oEdit1','htmlarea1'),$innova_def);
				$table_data[]=array($this->ca->lang_l('ch_message_template'),$entry);

				$table_data[]=array($this->ca->lang_l('ch_friendship_sbj_template'),'<input class="input1" name="ch_friendship_sbj_template" style="'.$this->ca->inp_width.'" value="'.$this->ch_settings['ch_friendship_sbj_template'].'" />');
			
				$entry='<textarea class="input1'.($this->f->tiny?' mceEditor':'').'" id="htmlarea2" name="ch_friendship_msg_template" style="width:100%" rows="4" cols="30">'
				  .$this->ch_settings['ch_friendship_msg_template'].'
				</textarea>'.str_replace(array('oEdit','htmlarea'),array('oEdit2','htmlarea2'),$innova_def);
				$table_data[]=array($this->ca->lang_l('ch_friendship_msg_template'),$entry);
			}
			else
				$table_data[]=array(Builder::buildCheckbox('ch_disable_blocked',$this->ch_settings['ch_disable_blocked'],$this->ca->lang_l('ch_disable_blocked')));
			
			$end=$this->form_buttons();
		}
		else
		{
			if($this->isAdmin)
				$arr=array(
				'ch_disable_messenger_users'=>(isset($_POST['ch_disable_messenger_users'])?1:0),
				'ch_send_emails'=>(isset($_POST['ch_send_emails'])?1:0),
				'ch_send_toadmin'=>(isset($_POST['ch_send_toadmin'])?1:0),
				'ch_only_friends'=>(isset($_POST['ch_only_friends'])?1:0),
				'ch_livesearch'=>(isset($_POST['ch_livesearch'])?1:0),
				'ch_message_template'=>$_POST['ch_message_template'],
				'ch_friendship_msg_template'=>$_POST['ch_friendship_msg_template'],
				'ch_friendship_sbj_template'=>$_POST['ch_friendship_sbj_template']);
			else
				$arr=array('ch_disable_blocked'=>(isset($_POST['ch_disable_blocked'])?1:0));
			$this->insert_messengerSetting($arr,$this->isAdmin?0:$this->logged_userId);
			$table_data[]=array('','<span class="rvts8">'.$this->ca->lang_l('settings saved').'</span>');
		}
		$output=Builder::addEntryTable($table_data,$end,'',$nav,false,'','',
				'<form name="frm" action="'.$url.'&do=settings" method="post">');
		$this->output($output,' - '.$this->ca->lang_l('settings'));
	}
	
	protected function show_disabled_messenger()
	{
		$table_data[]=array('<span class="rvts8">'.$this->ca->lang_l('disabled messages').'</span>');
		$output=Builder::adminTable('',array(),$table_data);
		$this->output($output);
	}
	
	protected function send_email($user_data,$message,$subject)
	{
		if(isset($user_data['email'])&&$user_data['email']!='')//send email to user
		{
			$send_from=(($this->f->sendmail_from=='')? $this->f->admin_email: $this->f->sendmail_from);
			$send_to=$user_data['email'];
			if(in_array('UTF-8',$this->f->site_charsets_a))
				$page_charset='UTF-8';
			else
				$page_charset=$this->f->site_charsets_a[0];
			MailHandler::sendMailStat($db,0,$send_to,$send_from,$message,'',$subject,$page_charset);
		}
		else //send email to admin
			MailHandler::sendMailCA($db,$message,$subject);
	}

	public function map_function($v)
	{
		$arr = explode('|', $v);
		if(count($arr)==2)
			return Formatter::stripTags($arr[1]);
		return '';
	}

	public function send_message()
	{
		global $db;
		Session::intStart();
		$valid_post=true;
		$url=$this->ca->ca_abs_url."?process=".($this->isAdmin?'':'my')."messenger".$this->ca->ca_l_amp;
		if(!isset($_POST['message'])||Formatter::stripTags($_POST['message'])==''){
			$valid_post=false;
			Session::setVarArr('messenger_error','message',$this->ca->lang_l('empty message'));
		}
		if(!isset($_POST['friend_uid_sel'])){
			$valid_post=false;
			Session::setVarArr('messenger_error','user',$this->ca->lang_l('none user selected'));
		}

		if($valid_post)
		{
			$friends_uid=array();
			$friends_username=array();
			if(is_array($_POST['friend_uid_sel'])){
				$friends_uid=array_map('intval',$_POST['friend_uid_sel']);
				$friends_username=array_map(array($this,'map_function'),$_POST['friend_uid_sel']);
			}
			else{
				$friends_uid[]=intval($_POST['friend_uid_sel']);
				$arr = explode('|', Formatter::stripTags($_POST['friend_uid_sel']));
				$friends_username[]=$arr[1];
			}
			$friends_uid=array_filter(array_unique($friends_uid));
			$friends_username=array_filter(array_unique($friends_username));

			if(count($friends_username)==count($friends_uid))
			{
				$message=Formatter::stripTags($_POST['message']);
				$subject=isset($_POST['subject'])&&$_POST['subject']!=''?Formatter::stripTags($_POST['subject']):'';
				foreach($friends_uid as $key=>$uid)
				{
					$user_data=$uid!=-1?User::getUser('','','',$uid):array('uid'=>$uid,'user_admin'=>1,'confirmed'=>1,'status'=>1);
					if(!empty($user_data))
					{
						$enable_send_msg=($user_data['user_admin']==1&&$this->ch_settings['ch_send_toadmin'])||$user_data['user_admin']!==1||$this->isAdmin;
						if($user_data['uid']!=$this->logged_userId&&$user_data['confirmed']&&$user_data['status']&&$enable_send_msg)
						{
							$result=array();
							if($this->isAdmin){
								if($user_data['user_admin']==1||!$this->ch_settings['ch_disable_messenger_users'])
									$result[0]='admin';
							}
							elseif($this->ch_settings['ch_only_friends']&&$user_data['user_admin']!==1){
								$que='SELECT id 
									FROM '.$db->pre.'ca_messenger_friends
									WHERE (uid='.$this->logged_userId.' AND friend_uid='.$user_data['uid'].') OR (uid='.$user_data['uid'].' AND friend_uid='.$this->logged_userId.') and my_status=1 and friend_status=1';
								$result=$db->fetch_all_array($que);
							}
							else
								$result[0]='to_all';
							if(!empty($result))
							{
								$data=array('receiver_uid'=>$user_data['uid'],'sender_uid'=>$this->logged_userId,'message'=>$message,'subject'=>$subject,'sender_ip'=>Detector::getIP());
								$res=$db->query_insert('ca_messenger_messages',$data);
								if($this->ch_settings['ch_send_emails']&&$res!==false){
									$message_email=$this->replaceMailMacros($this->ch_settings['ch_message_template'],$user_data['username'],$this->f->site_url,$message);
									$this->send_email($user_data,$is_active_confrimed,$subject);
								}
							}
							else
								Session::setVarArr('messenger_error',$uid,$this->ca->lang_l('cant send to').' '.$friends_username[$key]);
						}
						else
							Session::setVarArr('messenger_error',$uid,$this->ca->lang_l('cant send to').' '.$friends_username[$key]);
					}
					else
						Session::setVarArr('messenger_error',$uid,$this->ca->lang_l('cant send to').' '.$friends_username[$key]);
				}
			}
		}
		header("Location: ".$url.'&do=sent_messages');
		exit;
	}
	
	protected function delete_message()
	{
		global $db;

		$url=$this->ca->ca_abs_url."?process=".($this->isAdmin?'':'my')."messenger".$this->ca->ca_l_amp;
		if(isset($_REQUEST['message_id']))
		{
			$message_ids = array();
			if(is_array($_REQUEST['message_id']))
				$message_ids = array_map('intval', $_REQUEST['message_id']);
			else
				$message_ids[] = intval($_REQUEST['message_id']);
			foreach($message_ids as $msg_id)
			{
				$message_db=$db->fetch_all_array('SELECT * 
				FROM '.$db->pre.'ca_messenger_messages 
				WHERE id='.$msg_id.' AND (receiver_uid='.$this->logged_userId.' OR sender_uid='.$this->logged_userId.')');
				if($message_db!==false&&!empty($message_db)&&count($message_db)==1)
				{
					$sender_uid=$message_db[0]['sender_uid'];
					$del_receiver=$message_db[0]['del_receiver'];
					$del_sender=$message_db[0]['del_sender'];
					$is_sender=$sender_uid==$this->logged_userId;
					if($is_sender)
					{
						if(!$del_receiver)
							$db->query_update('ca_messenger_messages',array('del_sender'=>1),'id='.$msg_id);
						else
							$db->query('DELETE FROM '.$db->pre.'ca_messenger_messages WHERE id='.$msg_id);
					}
					else{
						if(!$del_sender)
							$db->query_update('ca_messenger_messages',array('del_receiver'=>1),'id='.$msg_id);
						else
							$db->query('DELETE FROM '.$db->pre.'ca_messenger_messages WHERE id='.$msg_id);
					}
				}
			}
		}
		$return=isset($_REQUEST['r'])&&$_REQUEST['r']=='sent_messages'?'sent_messages':'received_messages';
		header("Location: ".$url.'&do='.$return);
		exit;
	}
	
	protected function action_friendship($do)
	{
		global $db;

		if(!isset($_GET['friend_id'])&&intval($_GET['friend_id'])==0)
			exit;
		$friend_uid=intval($_GET['friend_id']);
		$url=$this->ca->ca_abs_url."?process=mymessenger".$this->ca->ca_l_amp;
		$result=false;

		if($this->isAdmin||!$this->ch_settings['ch_only_friends']){
			header("Location: ".$url);
			exit;
		}

		$user_data=$friend_uid!=-1?User::getUser('','','',$friend_uid):array('uid'=>$friend_uid,'user_admin'=>1,'confirmed'=>1,'status'=>1);
		if(!empty($user_data))
		{
			$enable_send_msg=($user_data['user_admin']==1&&$this->ch_settings['ch_send_toadmin'])||$user_data['user_admin']!==1;
			if($user_data['uid']!=$this->logged_userId&&$user_data['confirmed']&&$user_data['status']&&$enable_send_msg)
			{
				$que='SELECT * 
				FROM '.$db->pre.'ca_messenger_friends
				WHERE (uid='.$this->logged_userId.' AND friend_uid='.$user_data['uid'].') OR (uid='.$user_data['uid'].' AND friend_uid='.$this->logged_userId.')';
				$freind_arr=$db->fetch_all_array($que);
			
				if(!empty($freind_arr))
				{
					$is_myfriendship=$freind_arr[0]['uid']==$this->logged_userId;
					$reaction=$do=='confirm'||$do=='activate'?1:2;
					if($is_myfriendship)
						$result=$db->query_update('ca_messenger_friends',array('my_status'=>$reaction),'uid='.$this->logged_userId.' AND friend_uid='.$friend_uid);
					else
						$result=$db->query_update('ca_messenger_friends',array('friend_status'=>$reaction),'friend_uid='.$this->logged_userId.' AND uid='.$friend_uid);
				}
			}
		}

		echo $result!==false?$reaction.'|'.$this->fr_status_array[$reaction]:'error';
		exit;
	}
	
	protected function read_message()
	{
		global $db;
		$result=false;

		if(isset($_REQUEST['message_id'])&&intval($_REQUEST['message_id'])>0)
			$result=$db->query_update('ca_messenger_messages',array('read_receiver'=>1),'id='.intval($_REQUEST['message_id']).' AND receiver_uid='.$this->logged_userId);
		echo $result!==false?'1':'0';
	}

	protected function replaceMailMacros($src,$username,$sitename,$message)
	{
		return $src = str_replace(array('%username%','%sitename%','%message%'),array($username,$sitename,$message),$src);
	}

	protected function send_freindship(&$errors, $db)
	{
		if(isset($_POST['username'])&&$_POST['username']!='')
		{
			$username=Formatter::stripTags($_POST['username']);
			$message=Formatter::stripTags($_POST['message']);
			$user_data=User::getUser($username,'');
			if(!empty($user_data)&&$user_data['uid']!=$this->logged_userId&&$user_data['user_admin']!==1&&$user_data['confirmed']&&$user_data['status'])
			{
				$que='SELECT * 
				FROM '.$db->pre.'ca_messenger_friends
				WHERE (uid='.$this->logged_userId.' AND friend_uid='.$user_data['uid'].') OR (uid='.$user_data['uid'].' AND friend_uid='.$this->logged_userId.')';
				$result=$db->query_singlevalue($que);
				if($result===null) // friendship is not sent yet to this username
				{
					$friendship_array=array('uid'=>$this->logged_userId,'friend_uid'=>$user_data['uid'],'friend_status'=>0,'my_status'=>1);
					$result=$db->query_insert('ca_messenger_friends',$friendship_array);
					if($result!=false)
					{
						$subject=$this->replaceMailMacros($this->ch_settings['ch_friendship_sbj_template'],$user_data['username'],$this->f->site_url,'');
						$data=array('receiver_uid'=>$user_data['uid'],'sender_uid'=>$this->logged_userId,'message'=>$message,'subject'=>$subject,'sender_ip'=>Detector::getIP());
						$res=$db->query_insert('ca_messenger_messages',$data);
						if($this->ch_settings['ch_send_emails']&&$res!==false){
							$message=$this->replaceMailMacros($this->ch_settings['ch_friendship_msg_template'],$user_data['username'],$this->f->site_url,$message);
							$this->send_email($user_data,$message,$subject);
						}
					}
				}
				else
					$errors[]=$this->ca->lang_l('user friend');
			}
			else
				$errors[]=$this->ca->lang_l('cant send friendship to').' '.$username;
		}
		else
			$errors[]=$this->ca->lang_l('empty username');
	}

	protected function livesearch_users($db)
	{
		$searchstring=Formatter::strToLower($_REQUEST['username']);
		$ss=$db->escape(trim(strip_tags(str_replace("\\",'',$searchstring))));
		$livesearch_id=intval($_REQUEST['livesearch_id']);

		$output='';
		if($ss!='')
		{
			$que='SELECT u.*, f.uid as freind_already
			FROM '.$db->pre.'ca_users as u
			LEFT JOIN '.$db->pre.'ca_messenger_friends as f ON ((f.friend_uid="'.$this->logged_userId.'" AND f.uid=u.uid) OR (f.uid="'.$this->logged_userId.'" AND f.friend_uid=u.uid)) 
			RIGHT JOIN '.$db->pre.'ca_users_access as ac ON u.uid=ac.user_id AND ac.access_type<>9
			WHERE (u.username LIKE "%'.$ss.'%" OR u.display_name LIKE "%'.$ss.'%" OR u.first_name LIKE "%'.$ss.'%" OR u.surname LIKE "%'.$ss.'%") AND u.uid<>"'.$this->logged_userId.'" 
			GROUP BY u.uid
			ORDER BY u.username';
			$arr_users=$db->fetch_all_array($que);
			if(!empty($arr_users))
			{
				$li='';
				foreach($arr_users as $value)
				{
					if($value['freind_already']==null){
						$li.=
						'<li>'
							.(isset($value['avatar'])&&$value['avatar']!=''?'<div class="sh_avatar">'.User::getAvatarImage($value['avatar'],$value['username'],$this->ca->ca_site_url,'left').'</div>':'')
							.'<div class="sh_username" onClick="select_user(this,'.$livesearch_id.')">'.$value['username'].'</div>'
							.($value['display_name']!=''?'<span class="sh_display_name"> ('.$value['display_name'].')</span>':'')
							.($value['first_name']!=''||$value['surname']!=''?'<div class="sh_fullname">'.$value['first_name'].' '.$value['surname'].'</div>':'')
						.'</li>';
					}
				}
				if($li!='')
				{
					$output='<div class="sh_results_wrap">
						<ul>'.$li.'</ul>
						<div class="sh_close"><i class="fa fa-times" onclick="$(this).parents(\'.pre-search-content\').hide()"></i></div>
					</div>';
				}
			}
		}

		echo $output;
		exit;
	}

	protected function show_friends()
	{
		global $db;

		$output='';
		$url=$this->ca->ca_abs_url."?process=mymessenger".$this->ca->ca_l_amp;
		if($this->isAdmin||!$this->ch_settings['ch_only_friends']){
			header("Location: ".$url);
			exit;
		}
		if($this->ch_settings['ch_livesearch']){
			$this->ca->ca_css.='.a_n.a_listing{z-index:1}
				.pre-search-content{position:absolute;width: 250px !important;}
				.sh_results_wrap ul{list-style-type: none;padding:5px;max-height:200px;overflow:auto}
				.sh_results_wrap li{height:36px}
				.sh_results_wrap{border: 1px solid #8C8C8C;}
				.sh_close{text-align: right;padding: 2px;font-size: 13px;}
				.sh_close i{cursor:pointer}
				.sh_avatar img{height:32px !important;max-width:32px;overflow: hidden;margin: auto auto;}
				.sh_avatar{float:left;width:32px;margin-right: 2px;border: 1px solid #ADADAD;display: flex;border-radius: 2px;}
				.sh_username{font-weight: bold;display:inline-block;cursor: pointer;}
				.sh_display_name, .sh_fullname{font-size: 10px;}';
			$this->ca->ca_scripts.='
			function select_user(th,livesearch_id)
			{
				var search_result = $("#sid"+livesearch_id);
				if(search_result.length)
				{
					var inp = search_result.prev("input");
					inp.val($(th).text());
					setTimeout(function(){
						search_result.hide();
					},400);
				}
			}';
		}

		$errors=array();
		if(isset($_POST['send_freindship']))
			$this->send_freindship($errors, $db);
		elseif(isset($_REQUEST['livesearch_id'])&&$this->ch_settings['ch_livesearch'])
			$this->livesearch_users($db);

		$c_page=isset($_GET['page'])?intval($_GET['page']):1;
		list($orderby,$asc)=Filter::orderBy('created','DESC');
		$orderby_pfix=($orderby=='created')?'':'&amp;orderby='.$orderby;
		$asc_pfix='&amp;asc='.$asc;
		
		$where='(f.uid='.$this->logged_userId.' OR f.friend_uid='.$this->logged_userId.')';
		if($this->ch_settings['ch_disable_blocked'])
			$where.=' AND if(f.friend_uid<>"'.$this->logged_userId.'",f.my_status,f.friend_status)=1';
		if(isset($_GET['q'])&&!empty($_GET['q'])) //search
		{
			$ss=$db->escape(Formatter::stripTags($_GET['q']));
			$where.=' AND (u.username LIKE "%'.$ss.'%")';
		}

		$que='SELECT u.username, u.confirmed, u.status, f.*, if(f.friend_uid<>"'.$this->logged_userId.'",f.friend_uid,f.uid) as user_id_messenger, CONCAT(u.first_name," ",u.surname) as full_name
		FROM '.$db->pre.'ca_messenger_friends as f
		RIGHT JOIN '.$db->pre.'ca_users as u ON u.uid=if(f.friend_uid<>"'.$this->logged_userId.'",f.friend_uid,f.uid)
		WHERE '.$where.'
		ORDER BY '.$orderby.' '.$asc;
		$friends_array=$db->fetch_all_array($que);
		$total_records=count($friends_array);

		$start=($c_page-1)*Navigation::recordsPerPage();
		if($start>$total_records)
		{
			$c_page=1;
			$start=0;
		}
		elseif($start>0)
			$start++;
		$limit=Navigation::recordsPerPage()+$start;

		$count=$this->get_newMsgFreinds();
		$nav='
			<div>
				<div style="float:left;">
					<input class="settings_tab" type="button" onclick="document.location=\''.$url.'&amp;do=received_messages\'" 
					value="'.$this->ca->lang_l('received messages').(isset($count['new_msg'])&&$count['new_msg']>0?' ('.$count['new_msg'].')':'').'" />
					<input class="settings_tab" type="button" onclick="document.location=\''.$url.'&amp;do=sent_messages\'" value="'.$this->ca->lang_l('sent messages').'" />
					<input type="button" id="friends_btn" class="settings_tab active" value="'.$this->ca->lang_l('friends').(isset($count['new_fr'])&&$count['new_fr']>0?' ('.$count['new_fr'].')':'').'" 
					onclick="document.location=\''.$url.'&amp;do=friends\'" data-value="'.(isset($count['new_fr'])&&$count['new_fr']>0?$count['new_fr']:0).'" />
					<input type="button" class="settings_tab" value="'.$this->ca->lang_l('settings').'" onclick="document.location=\''.$url.'&amp;do=settings\'" />
				</div>
				<div style="text-align:right;">
					<input type="text" id="q" name="q" value="">
					<input type="button" name="search" class="ca_button" value="&#xf002"
					title="'.$this->ca->lang_l('search').'" onclick="document.location=\''.$url.'&amp;do=friends&amp;q=\'+$(\'#q\').val();">
				</div>
			</div>
			<br/>
			<div>
				<input type="button" value="'.$this->ca->lang_l('send friendship').'" onclick="javascript:sv(\'send_freindship_form\');">
				<div class="'.$this->f->atbgr_class.'">'.$this->send_freindship_form($url).'</div>
			</div>
			<div style="clear:both;"></div>
		';

		if(!empty($errors)){
			$errors_str='';
			foreach($errors as $er)
				$errors_str.='<div style="color:red">'.$er.'</div>';
			$nav.=$errors_str;
		}
		$nav.=Navigation::pageCA($total_records,$url.'&amp;do=friends'.$orderby_pfix.$asc_pfix,0,$c_page);

		$red_style=' style="background:red;padding:2px;color:white;font-weight:bold;display: inline-block;"';
		$fr_actions_array=array($this->ca->lang_l('confirm'),$this->ca->lang_l('block'),$this->ca->lang_l('activate'));
		$this->ca->ca_scripts.='
		var fr_status_array=["'.implode('","', $fr_actions_array).'"];
		var array_actions=["confirm","block","activate"];
		$(document).ready(function() {
			$("a[rel=\'toggle_action\']").on("click", function(){
				action = $(this).attr("data-action");
				friend_id = $(this).attr("data-uid");
				toggle_action(action,friend_id,this);
			});
			'.($this->ch_settings['ch_livesearch']?'$(".src_username").livesearch({"lives_type":"users"});':'').'
		});
		function toggle_action(action,friend_id,th)
		{
			$.post("'.$url.'&friend_id="+friend_id+"&do="+array_actions[action],function(re){
				if(re!="error")
				{
					arr_re=re.split("|");
					if(arr_re.length==2){
						red_style=arr_re[0]==2?\''.$red_style.'\':"";
						$(".my_status_"+friend_id).html("<span "+red_style+">"+arr_re[1]+"</span>");
						$(th).attr("data-action",arr_re[0]).html(fr_status_array[arr_re[0]]);
						var new_fr=$("#friends_btn").attr("data-value");
						if(new_fr>0){
							$("#friends_btn").attr("data-value",new_fr-1);
							$("#friends_btn").val("'.$this->ca->lang_l('friends').'"+(new_fr==1?"":" ("+(new_fr-1)+")"));
						}
					}
				}
			});
		}';

		$table_data=$cap_arrays=array();
		if(!empty($friends_array))
		{
			$cap_arrays=array(
				'username'=>array($url.'&amp;do=friends&amp;orderby=username','none',$this->ca->lang_l('user')),
				'friend_status'=>array($url.'&amp;do=friends&amp;orderby=friend_status','none',$this->ca->lang_l('user friendship status')),
				'my_status'=>array($url.'&amp;do=friends&amp;orderby=my_status','none',$this->ca->lang_l('my friendship status')),
				'created'=>array($url.'&amp;do=friends&amp;orderby=created','none',$this->ca->lang_l('date')));
			$cap_arrays[$orderby][1]='underline';
			if($asc=='DESC')
				$cap_arrays[$orderby][0]=$cap_arrays[$orderby][0].'&amp;asc=ASC';
			for($i=$start;$i<$limit;$i++)
			{
				$value=$friends_array[$i];
				if(!empty($value))
				{
					$username=$value['username'];
					$user_fullname=trim($value['full_name'])!=''?'<span class="rvts8" style="display:block">'.trim($value['full_name']).'</span>':'';
					$is_active_confrimed=$value['confirmed']&&$value['status'];
					$friend_uid=$value['friend_uid'];
					$uid=$value['uid'];
					$user_id_messenger=$value['user_id_messenger'];

					$freind_status=$value['friend_status'];
					$my_status=$value['my_status'];
					$created=Date::format_date(strtotime($value['created']),$this->f->inter_languages_a,$this->f->month_names,$this->f->day_names,$mode,$params).' '
					.Date::formatTimeSql($value['created'],$this->f->time_format_a[0],'short');
					$user_data=User::getUser('','','',$user_id_messenger);
					$avatar=isset($user_data['avatar'])?User::getAvatarImage($user_data['avatar'],$user_data['username'],$this->ca->ca_site_url,'right'):'';
					
					$user_nav=array();
					$userArea='<span class="rvts12">'.$username.'</span>'.$avatar.$user_fullname;
					$sv_eac='';
					if($is_active_confrimed){
						$userArea.=$this->send_message_form($url.'&amp;do=send_message','',$user_id_messenger,'0',true,$username);
						$sv_eac='sv(\'send_message_form'.$user_id_messenger.'\');';
					}else
						$userArea.='<span class="not_active_user">('.$this->ca->lang_l('not active user').')</span>';

					$user_status_label=$my_status_label=$user_red_style=$my_red_style='';
					if($user_id_messenger==$friend_uid)  // My friendship (I sent a friendship to user)
					{
						if($is_active_confrimed)
							$user_nav[$fr_actions_array[$my_status]]='javascript:void(0);" rel="toggle_action" data-uid="'.$user_id_messenger.'" data-action="'.$my_status.'';
						$my_status_label=$this->fr_status_array[$my_status];
						$user_status_label=$this->fr_status_array[$freind_status];
						
						if($freind_status==0 || $freind_status==2)
							$user_red_style=$red_style;
						if($my_status==0 || $my_status==2)
							$my_red_style=$red_style;
					}
					else
					{
						if($is_active_confrimed)
							$user_nav[$fr_actions_array[$freind_status]]='javascript:void(0);" rel="toggle_action" data-uid="'.$user_id_messenger.'" data-action="'.$freind_status.'';
						$my_status_label=$this->fr_status_array[$freind_status];
						$user_status_label=$this->fr_status_array[$my_status];

						if($freind_status==0 || $freind_status==2)
							$my_red_style=$red_style;
						if($my_status==0 || $my_status==2)
							$user_red_style=$red_style;
					}

					if($freind_status==1 && $my_status==1 && $is_active_confrimed){
						$user_nav[$this->ca->lang_l('compose message')]='javascript:void(0);" onclick="'.$sv_eac.'';
					}
					$row_data=array(
						 array($userArea,$user_nav),
						 '<div class="user_status_'.$user_id_messenger.' rvts8"'.$user_red_style.'>'.$user_status_label.'</div>',
						  '<div class="my_status_'.$user_id_messenger.' rvts8"><span '.$my_red_style.'>'.$my_status_label.'</span></div>',
						 '<span class="rvts8">'.$created.'</span>'
					);
					$table_data[]=$row_data;
				}
			}
			$this->ca->ca_css.='.not_active_user{font-size:11px;color:#FF5050}';
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data,'','','');
		}
		else
		{
			$table_data[]=array('<span class="rvts8">'.$this->ca->lang_l('none friends').'</span>');
			$output.=Builder::adminTable($nav,array(),$table_data);
		}
		$this->output($output,' - '.$this->ca->lang_l('friends'));
	}

	protected function get_newMsgFreinds()
	{
		global $db;
	
		$counts['new_msg']=$db->query_singlevalue('SELECT COUNT(*)
			FROM '.$db->pre.'ca_messenger_messages
			WHERE receiver_uid='.$this->logged_userId.' AND read_receiver=0 AND del_receiver=0');
		if(!$this->isAdmin&&$this->ch_settings['ch_only_friends'])
			$counts['new_fr']=$db->query_singlevalue('SELECT COUNT(*)
				FROM '.$db->pre.'ca_messenger_friends
				WHERE friend_uid='.$this->logged_userId.' AND friend_status=0');

		return $counts;
	}
	
	protected function show_messages($do)
	{
		global $db;

		Session::intStart();
		$output='';
		$is_sent_msg=$do=='sent_messages';
		$url=$this->ca->ca_abs_url."?process=".($this->isAdmin?'':'my')."messenger".$this->ca->ca_l_amp;

		$c_page=isset($_GET['page'])?intval($_GET['page']):1;
		list($orderby,$asc)=Filter::orderBy('created','DESC');
		$orderby_pfix=($orderby=='created')?'':'&amp;orderby='.$orderby;
		$asc_pfix='&amp;asc='.$asc;

		$where=$is_sent_msg?'c.sender_uid='.$this->logged_userId.' AND c.del_sender=0':'c.receiver_uid='.$this->logged_userId.' AND c.del_receiver=0';
		if(isset($_GET['q'])&&!empty($_GET['q'])) //search
		{
			$ss=$db->escape(Formatter::stripTags($_GET['q']));
			$fn=$sn='';
			$where.=' AND (c.message LIKE "%'.$ss.'%" OR u.username LIKE "%'.$ss.'%" OR c.subject LIKE "%'.$ss.'%")';
		}

		$que='
		SELECT COUNT(*)
		FROM (
			SELECT c.id
			FROM '.$db->pre.'ca_messenger_messages as c
			LEFT JOIN '.$db->pre.'ca_users as u ON u.uid=c.'.($is_sent_msg?'receiver_uid':'sender_uid')
			.' LEFT JOIN '.$db->pre.'ca_messenger_friends as f ON
				(f.uid='.$this->logged_userId.' AND f.friend_uid=c.'.($is_sent_msg?'receiver_uid':'sender_uid').') OR
				(f.friend_uid='.$this->logged_userId.' AND f.uid=c.'.($is_sent_msg?'receiver_uid':'sender_uid').')'
			.' WHERE '.$where
			.' GROUP by c.id
			) as Z';
		$total_records=$db->query_singlevalue($que);
		$start=($c_page-1)*Navigation::recordsPerPage();
		if($start>$total_records)
		{
			$c_page=1;
			$start=0;
		}
		$limit=(($total_records>Navigation::recordsPerPage()&&Navigation::recordsPerPage()>0)?'LIMIT '.$start.', '.Navigation::recordsPerPage().'':'');

		$que='
			SELECT c.*, u.username, f.my_status, f.friend_status, CONCAT(u.first_name," ",u.surname) as full_name
			FROM '.$db->pre.'ca_messenger_messages as c
			LEFT JOIN '.$db->pre.'ca_users as u ON u.uid=c.'.($is_sent_msg?'receiver_uid':'sender_uid')
			.' LEFT JOIN '.$db->pre.'ca_messenger_friends as f ON
				(f.uid='.$this->logged_userId.' AND f.friend_uid=c.'.($is_sent_msg?'receiver_uid':'sender_uid').') OR
				(f.friend_uid='.$this->logged_userId.' AND f.uid=c.'.($is_sent_msg?'receiver_uid':'sender_uid').')'
			.' WHERE '.$where
			.' GROUP by c.id'
			.' ORDER BY '.$orderby.' '.$asc.' '.$limit;
		$arr_messages=$db->fetch_all_array($que);

		$dropdown_friends=$this->build_dropdown_friends();
		$count=$this->get_newMsgFreinds();
		$nav='
			<div>
				<div style="float:left;">
					<input class="settings_tab'.(!$is_sent_msg||$do==''?' active':'').'" id="received_messages_btn" data-value="'.(isset($count['new_msg'])&&$count['new_msg']>0?$count['new_msg']:0).'"
					type="button" onclick="document.location=\''.$url.'&amp;do=received_messages\'" value="'.$this->ca->lang_l('received messages').(isset($count['new_msg'])&&$count['new_msg']>0?' ('.$count['new_msg'].')':'').'" />
					<input class="settings_tab'.($is_sent_msg?' active':'').'" type="button" onclick="document.location=\''.$url.'&amp;do=sent_messages\'" value="'.$this->ca->lang_l('sent messages').'" />'
					.($this->isAdmin||!$this->ch_settings['ch_only_friends']?'':'
					<input type="button" class="settings_tab" value="'.$this->ca->lang_l('friends').(isset($count['new_fr'])&&$count['new_fr']>0?' ('.$count['new_fr'].')':'').'" onclick="document.location=\''.$url.'&amp;do=friends\'" />')
					.'
					<input type="button" class="settings_tab" value="'.$this->ca->lang_l('settings').'" onclick="document.location=\''.$url.'&amp;do=settings\'">
				</div>
				<div style="text-align:right;">
					<input type="text" id="q" name="q" value="">
					<input type="button" name="search" class="ca_button" value="&#xf002" title="'.$this->ca->lang_l('search').'" onclick="document.location=\''.$url.'&do='.($is_sent_msg?'sent_messages':'received_messages').'&q=\'+$(\'#q\').val();">
				</div>
			</div>
			<br/>
			<div>
				<input type="button" value="'.$this->ca->lang_l('compose message').'" onclick="javascript:sv(\'send_message_form0\');">
				<div class="'.$this->f->atbgr_class.'">'.$this->send_message_form($url.'&do=send_message',$dropdown_friends).'</div>
			</div>
			<div style="clear:both;"></div>
		';
		if(Session::isSessionSet('messenger_error')){
			$errors='';
			foreach(Session::getVar('messenger_error') as $error)
				$errors.='<div style="color:red">'.$error.'</div>';
			$nav.=$errors;
			Session::unsetVar('messenger_error');
		}
		$nav.=Navigation::pageCA($total_records,$url.'&amp;do='.($is_sent_msg?'sent_messages':'received_messages').$orderby_pfix.$asc_pfix,0,$c_page);

		$table_data=$cap_arrays=array();
		$this->ca->ca_css.='
			.i_read{font-size: 16px;}
			.i_read.fa-folder-open-o{color:green}
			.i_read.fa-folder-o{color:red}
			.not_active_user{font-size:11px;color:#FF5050}
			.a_n.a_listing{z-index:1}'; 
		$this->ca->ca_css.='
			.wrap_niceSelect.avatar{width:225px;}
			.wrap_ul_niceSelect.avatar li{display:inline-block; position:relative;width: 50%;cursor: pointer;-moz-user-select: none;-webkit-user-select: none;-ms-user-select: none;}
			.wrap_ul_niceSelect.avatar li span{width: 25px;height: 25px;background-size: contain;display: inline-block;background-repeat: no-repeat;background-position: 50%;}
			.wrap_ul_niceSelect.avatar li:hover > .option_content{margin-left:1px}
			.wrap_ul_niceSelect.avatar .option_content{display: inline-block;vertical-align: top;height: 25px;line-height: 11px;padding-left: 2px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;width: 71%;font-size: 11px;}
			.wrap_ul_niceSelect.avatar .option_content.not_extra{line-height: 25px;}
			.wrap_ul_niceSelect.avatar .option_content div{font-size:9px; color:grey;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;}
			.wrap_ul_niceSelect.avatar ul{padding: 0px 5px 0px 7px;}
			.wrap_ul_niceSelect.avatar .selected_option{top: 0px;left: 1px;width: 21px;line-height: 22px;height: 21px;text-align: center;text-shadow: 0px 0px 4px rgb(0, 0, 0);border: 1px dotted rgba(0, 45, 20, 0.42);border-radius: 21px;font-size: 19px;color: #CCFFC6;}
			.wrap_niceSelect.avatar .groupicon{background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMC8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvVFIvMjAwMS9SRUMtU1ZHLTIwMDEwOTA0L0RURC9zdmcxMC5kdGQnPjxzdmcgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+PGc+PHBhdGggZD0iTTksOWMwLTEuNywxLjMtMywzLTNzMywxLjMsMywzYzAsMS43LTEuMywzLTMsM1M5LDEwLjcsOSw5eiBNMTIsMTRjLTQuNiwwLTYsMy4zLTYsMy4zVjE5aDEydi0xLjdDMTgsMTcuMywxNi42LDE0LDEyLDE0eiAgICIvPjwvZz48Zz48Zz48Y2lyY2xlIGN4PSIxOC41IiBjeT0iOC41IiByPSIyLjUiLz48L2c+PGc+PHBhdGggZD0iTTE4LjUsMTNjLTEuMiwwLTIuMSwwLjMtMi44LDAuOGMyLjMsMS4xLDMuMiwzLDMuMiwzLjJsMCwwLjFIMjN2LTEuM0MyMywxNS43LDIxLjksMTMsMTguNSwxM3oiLz48L2c+PC9nPjxnPjxnPjxjaXJjbGUgY3g9IjE4LjUiIGN5PSI4LjUiIHI9IjIuNSIvPjwvZz48Zz48cGF0aCBkPSJNMTguNSwxM2MtMS4yLDAtMi4xLDAuMy0yLjgsMC44YzIuMywxLjEsMy4yLDMsMy4yLDMuMmwwLDAuMUgyM3YtMS4zQzIzLDE1LjcsMjEuOSwxMywxOC41LDEzeiIvPjwvZz48L2c+PGc+PGc+PGNpcmNsZSBjeD0iNS41IiBjeT0iOC41IiByPSIyLjUiLz48L2c+PGc+PHBhdGggZD0iTTUuNSwxM2MxLjIsMCwyLjEsMC4zLDIuOCwwLjhjLTIuMywxLjEtMy4yLDMtMy4yLDMuMmwwLDAuMUgxdi0xLjNDMSwxNS43LDIuMSwxMyw1LjUsMTN6Ii8+PC9nPjwvZz48L3N2Zz4=");}
			.wrap_niceSelect.avatar .usericon{background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMC8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvVFIvMjAwMS9SRUMtU1ZHLTIwMDEwOTA0L0RURC9zdmcxMC5kdGQnPjxzdmcgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+PGNpcmNsZSBjeD0iMTIiIGN5PSI4IiByPSI0Ii8+PHBhdGggZD0iTTEyLDE0Yy02LjEsMC04LDQtOCw0djJoMTZ2LTJDMjAsMTgsMTguMSwxNCwxMiwxNHoiLz48L3N2Zz4=");}';
		//niceSelect plugin css. On change here may need change in lister.css, because they use similar styling 
		$this->ca->ca_css.='
			.wrap_niceSelect{position:relative;height: 23px;font-size: 12px;}
			.filter_niceSelect{margin: 5px 0px 5px 0px;}
			.filter_niceSelect input{font-size: 12px;display: block;margin: 0 auto;width: 71%;padding: 1px;padding-left: 10%;border: 1px solid #FCC89D;border-radius: 4px;-webkit-border-radius: 4px;}
			.filter_niceSelect input:focus {outline: none;border: 1px solid #FF9F4E;box-shadow: 0px 0px 8px #FF9F4E;-webkit-box-shadow: 0px 0px 8px #FF9F4E;}
			.filter_niceSelect span{width: 11px;height: 11px;background-size: contain;display: block;position: absolute;top: 8px;left: 10%;background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaGVpZ2h0PSIyNHB4IiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCAyNCAyNCIgd2lkdGg9IjI0cHgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6c2tldGNoPSJodHRwOi8vd3d3LmJvaGVtaWFuY29kaW5nLmNvbS9za2V0Y2gvbnMiIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48dGl0bGUvPjxkZXNjLz48ZGVmcy8+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIiBpZD0ibWl1IiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSI+PGcgaWQ9IkFydGJvYXJkLTEiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC01MzkuMDAwMDAwLCAtNDA3LjAwMDAwMCkiPjxnIGlkPSJzbGljZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjE1LjAwMDAwMCwgMTE5LjAwMDAwMCkiLz48cGF0aCBkPSJNNTU1LjQ3NzI3Niw0MjEuMzU1OTU2IEM1NTYuNDM3MTIsNDE5Ljk3OTM4MyA1NTcsNDE4LjMwNTQyNSA1NTcsNDE2LjUgQzU1Nyw0MTEuODA1NTc5IDU1My4xOTQ0MjEsNDA4IDU0OC41LDQwOCBDNTQzLjgwNTU3OSw0MDggNTQwLDQxMS44MDU1NzkgNTQwLDQxNi41IEM1NDAsNDIxLjE5NDQyMSA1NDMuODA1NTc5LDQyNSA1NDguNSw0MjUgQzU1MC4zMDUxNDgsNDI1IDU1MS45Nzg4NjgsNDI0LjQzNzI5MyA1NTMuMzU1MzIxLDQyMy40Nzc3MTkgTDU1My4zNTU4OTIsNDIzLjQ3NzIxMiBMNTU5LjY1OTQzMyw0MjkuNzgwNzUzIEM1NTkuNzc2MzA5LDQyOS44OTc2MjkgNTU5Ljk2MjIwNiw0MjkuOTAxMjI1IDU2MC4wODIyMTEsNDI5Ljc4MTIyIEw1NjEuNzgxMjIsNDI4LjA4MjIxMSBDNTYxLjg5NzgzOCw0MjcuOTY1NTkzIDU2MS44OTI0MTcsNDI3Ljc3MTA5NyA1NjEuNzgwNzUzLDQyNy42NTk0MzMgTDU1NS40NzcyNzYsNDIxLjM1NTk1NiBaIE01NDguNSw0MjMgQzU1Mi4wODk4NTEsNDIzIDU1NSw0MjAuMDg5ODUxIDU1NSw0MTYuNSBDNTU1LDQxMi45MTAxNDkgNTUyLjA4OTg1MSw0MTAgNTQ4LjUsNDEwIEM1NDQuOTEwMTQ5LDQxMCA1NDIsNDEyLjkxMDE0OSA1NDIsNDE2LjUgQzU0Miw0MjAuMDg5ODUxIDU0NC45MTAxNDksNDIzIDU0OC41LDQyMyBaIiBmaWxsPSIjRkJCNzdFIiBpZD0iY29tbW9uLXNlYXJjaC1sb29rdXAtZ2x5cGgiLz48L2c+PC9nPjwvc3ZnPg==");}
			.select_niceSelect, .wrap_ul_niceSelect{width:100%}
			.select_niceSelect.openSelect{border-bottom: 0 white !important;}
			.select_niceSelect.openSelect .arrow_1{border-width: 0 5px 6px 5px;border-color: transparent transparent #808080 transparent;}
			.select_niceSelect.openSelect .arrow_1.hover_arrow{border-color: transparent transparent  #414141 transparent !important;}
			.select_niceSelect .arrow_niceSelect{width: 0;height: 0;border-style: solid;position: absolute;}
			.select_niceSelect .arrow_1{border-width: 6px 5px 0 5px;border-color: #808080 transparent transparent transparent;top: 8px;right: 7px;}
			.select_niceSelect .arrow_1.hover_arrow{border-color:#414141 transparent transparent transparent !important;}
			.select_niceSelect.openSelect .arrow_2{border-width: 0 4px 5px 4px;top: 10px;border-color: transparent transparent white transparent;}
			.select_niceSelect .arrow_2{top: 7px;right: 8px;border-width: 5px 4px 0 4px;border-color: white transparent transparent transparent;}
			.select_niceSelect{display: block;background: white;border: 1px solid grey;position: absolute;cursor: pointer;}
			.wrap_ul_niceSelect{display: none;border-radius: 0px 0px 4px 4px;border: 1px solid grey;border-top-width: 0;top: 21px;position: absolute;z-index: 999999;background-color: #fff;}
			.wrap_ul_niceSelect ul{text-align: left;margin-bottom: 10px;list-style-type: none;overflow: auto;max-height: 200px;}
			.wrap_ul_niceSelect .selected_option {position: absolute;display: none;width: 21px;line-height: 21px;font-size: 17px;color: #fff;text-shadow: 1px 0 2px rgba(0, 0, 0, 0.3);}
			.wrap_ul_niceSelect ul::-webkit-scrollbar {width: 6px;}
			.wrap_ul_niceSelect ul::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 3px rgba(0,0,0,0.3);border-radius: 6px;}
			.wrap_ul_niceSelect ul::-webkit-scrollbar-thumb {border-radius: 10px;-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5);}
			.submit_niceSelect{margin: 0 auto;margin-bottom: 10px;text-align: center;line-height: 25px;background: #FF7400;font-size: 15px;color: #FFFFFF;text-transform: capitalize;cursor: pointer;width:120px}
			.submit_niceSelect:hover{background-color: #E76900;}
			.firstValue_niceSelect{text-align: left;min-height: 15px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;width:88%;padding: 3px 7px 3px 7px;}';
		$this->ca->ca_scripts.='
		function delete_multiple_messages(){
			var str="";
			$(".mng_entry_chck").each(function(){
				if(this.checked)
					str+="&message_id[]="+(parseInt(this.value)||0);
			});
			document.location="'.$url.'&do=delete_message"+str+"&r='.($is_sent_msg?'sent_messages':'received_messages').'";
		}
		function read_message(msg_id){
			var msg_div=$("#read_receiver_"+msg_id);
			if(msg_div.hasClass("fa-folder-o"))
			{
				$.post("'.$url.'&message_id="+msg_id+"&do=read_message",function(re){
					if(re=="1")
					{
						$("#read_receiver_"+msg_id).removeClass("fa-folder-o").addClass("fa-folder-open-o");
						var new_msg=$("#received_messages_btn").attr("data-value");
						if(new_msg>0){
							$("#received_messages_btn").attr("data-value",new_msg-1);
							$("#received_messages_btn").val("'.$this->ca->lang_l('received messages').'"+(new_msg==1?"":" ("+(new_msg-1)+")"));
							$(".messenger_link").find("a.nav_link").html("'.$this->ca->lang_l('messages').'"+(new_msg==1?"":" ("+(new_msg-1)+")"));
						}
						received_messages_btn
					}
				});
			}
		}
		$(document).ready(function(){
			$(".niceSelect").niceSelect({lang_l:{confirm:"'.$this->ca->lang_l('confirm').'",close:"'.$this->ca->lang_l('close').'"},type:"avatar"});
		});';

		if(!empty($arr_messages))
		{
			$cap_arrays=array(
				'<input type="checkbox" onclick="$(\'.mng_entry_chck\').prop(\'checked\',$(this).is(\':checked\'));">',
				'subject'=>array($url.'&amp;do='.($is_sent_msg?'sent_messages':'received_messages').'&amp;orderby=subject','none',$this->ca->lang_l('subject')),
				'username'=>array($url.'&amp;do='.($is_sent_msg?'sent_messages':'received_messages').'&amp;orderby=username','none',$this->ca->lang_l('user')),
				'created'=>array($url.'&amp;do='.($is_sent_msg?'sent_messages':'received_messages').'&amp;orderby=created','none',$this->ca->lang_l('date')));
			if(!$is_sent_msg)
				$cap_arrays['read_receiver']=array($url.'&amp;do='.($is_sent_msg?'sent_messages':'received_messages').'&amp;orderby=read_receiver','none',$this->ca->lang_l('read status'));
			$cap_arrays[$orderby][1]='underline';
			if($asc=='DESC')
				$cap_arrays[$orderby][0]=$cap_arrays[$orderby][0].'&amp;asc=ASC';
			$table_data=array();

			foreach($arr_messages as $value)
			{
				if(!empty($value))
				{
					$message_id=$value['id'];
					$message=$value['message'];
					$subject=$value['subject']!=''?$value['subject']:$this->ca->lang_l('No subject');
					$read_receiver_status=$value['read_receiver'];
					$receiver_uid=$value['receiver_uid'];
					$sender_uid=$value['sender_uid'];
					
					$uid=$is_sent_msg?$receiver_uid:$sender_uid;
					$no_send=false;
					
					$user_data=User::getUser('','','',$uid);
					$avatar=isset($user_data['avatar'])?User::getAvatarImage($user_data['avatar'],$user_data['username'],$this->ca->ca_site_url,'right'):'';
					$user_fullname=$not_active='';
					if($user_data!==false&&!empty($user_data))
					{
						$user_fullname=trim($value['full_name'])!=''?'<span class="rvts8" style="display:block">'.trim($value['full_name']).'</span>':'';
						if($user_data['user_admin']){// user admin
							$username='<b>'.$value['username'].'</b>';
							if(!$this->ch_settings['ch_send_toadmin'])
								$no_send=true;
						}
						else
						{
							$username=$value['username'];
							if($this->isAdmin)
								$no_send=false;
							else
							{
								if(($value['my_status']!=1||$value['friend_status']!=1)&&$this->ch_settings['ch_only_friends']){
									$no_send=true;
									if(!$this->isAdmin)
										$not_active='<span class="not_active_user">('.$this->ca->lang_l('not active friend').')</span>';
								}
								if(!$this->ch_settings['ch_only_friends'])
									$no_send=false;
							}
						}
						if(!$user_data['confirmed']||!$user_data['status']){
							$no_send=true;
							$not_active='<span class="not_active_user">('.$this->ca->lang_l('not active user').')</span>';
						}
					}
					else
					{
						if($uid==-1){ // ezg admin
							$avatar=User::getAvatarImage($this->f->admin_avatar,$this->ca->lang_l('admin'),$this->ca->ca_site_url,'right');
							$user_fullname='<span class="rvts8" style="display:block">'.$this->f->admin_nickname.'</span>';
							$username='<b>'.$this->ca->lang_l('admin').'</b>';
							if(!$this->ch_settings['ch_send_toadmin'])
								$no_send=true;
						}
						else{
							$username='<span class="not_active_user">'.$this->ca->lang_l('removed user').'</span>';
							$no_send=true;
						}
					}

					$created=Date::format_date(strtotime($value['created']),$this->f->inter_languages_a,$this->f->month_names,$this->f->day_names,$mode,$params).' '
					.Date::formatTimeSql($value['created'],$this->f->time_format_a[0],'short');

					$msg_nav=$user_nav=array();					
					$msgArea='<span class="rvts8" style="width: 430px; display: block;">'.$subject.'</span>'
						.'<pre id="liveview_message'.$message_id.'" style="display:none;margin:10px;padding-bottom:10px;white-space:-moz-pre-wrap;
						white-space:-pre-wrap;white-space:-o-pre-wrap;white-space: pre-wrap;word-wrap:break-word;width:400px">'.$message.'</pre>'
						.$this->reply_message_form($url.'&do=send_message',$message_id,$uid,$message,$subject,$username)
						.$this->forward_message_form($url.'&do=send_message',$dropdown_friends,$message_id,$message);

					$userArea='<span class="rvts12">'.$username.'</span>'.$avatar.$user_fullname.$not_active.$this->send_message_form($url.'&do=send_message',$dropdown_friends,$uid,$message_id,true,$username);
					$user_sv='sv(\'send_message_form'.$message_id.'\');';
					$msg_sv='sv(\'liveview_message'.$message_id.'\'); '.($read_receiver_status||$is_sent_msg?'':'read_message(\''.$message_id.'\');').'';
					if(!$is_sent_msg)
						$msg_rpl='sv(\'reply_message_form'.$message_id.'\');';
					$msg_frw='sv(\'forward_message_form'.$message_id.'\');';
					
					if(!$no_send)
						$user_nav[$this->ca->lang_l('compose message')]='javascript:void(0);" onclick="'.$user_sv.';';
					$msg_nav[$this->ca->lang_l('view')]='javascript:void(0);" onclick="'.$msg_sv;
					$msg_nav[$this->ca->lang_l('delete')]='javascript:void(0);" onclick="document.location=\''.$url.'&do=delete_message&message_id='.$message_id.'&r='.($is_sent_msg?'sent_messages':'received_messages').'\'';
					if(!$is_sent_msg&&!$no_send)
						$msg_nav[$this->ca->lang_l('reply')]='javascript:void(0);" onclick="'.$msg_rpl;
					$msg_nav[$this->ca->lang_l('forward')]='javascript:void(0);" onclick="'.$msg_frw;
					$append='<input class="ca_button" name="del_entry" type="button" value="&#xf00d" title=" '.$this->ca->lang_l('delete checked messages')
					.' " onclick="if(confirm(\''.$this->ca->lang_l('del_ch_message').'\')) delete_multiple_messages(); else return false; ">';
					$row_data=array(
						'<input type="checkbox" class="mng_entry_chck" value="'.$message_id.'" name="message_id[]">',
						 array($msgArea,$msg_nav),
						 array($userArea,$user_nav),
						 '<span class="rvts8">'.$created.'</span>'
					);
					if(!$is_sent_msg)
						$row_data[4]= '<div id="read_receiver_'.$message_id.'" class="i_read fa '.($read_receiver_status?'fa-folder-open-o':'fa-folder-o').'"></div>';
					$table_data[]=$row_data;
				}
			}
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$append,'','');
		}
		else
		{
			$table_data[]=array('<span class="rvts8">'.$this->ca->lang_l('none messages').'</span>');
			$output.=Builder::adminTable($nav,array(),$table_data);
		}
		$this->output($output,' - '.$this->ca->lang_l($is_sent_msg?'sent messages':'received messages'));
	}

	protected function send_freindship_form($url)
	{
		return '
		<div id="send_freindship_form" style="display:none;margin:10px;padding-bottom:10px;">
			<form action="'.$url.'&amp;do=friends" method="post">
				<span class="rvts8">'.$this->ca->lang_l('username').' *</span></br>
				<input type="text" name="username" style="width:242px;" class="src_username"><br/>
				<span class="rvts8">'.$this->ca->lang_l('message').'</span><br/>
				<textarea name="message" style="width:400px; height:85px">'.$message.'</textarea>'
				.'<br/><br/><input type="submit" name="send_freindship" value="'.$this->ca->lang_l('send').'" />
			</form>
		</div>';
	}

	protected function reply_message_form($url,$msg_id='0',$uid='0',$message,$subject,$username)
	{
		return '
		<div id="reply_message_form'.$msg_id.'" style="display:none;margin:10px;padding-bottom:10px;">
			<form action="'.$url.'" method="post">
				<span class="rvts8">'.$this->ca->lang_l('subject').'</span><br/>
				<input type="text" name="subject" style="width:400px" value="Re: '.$subject.'" /><br/>
				<span class="rvts8">'.$this->ca->lang_l('message').' *</span><br/>
				<textarea required name="message" style="width:400px; height:85px">'."---------------------------&#13;&#10;".$message."&#13;&#10;---------------------------&#13;&#10;</textarea>".
				'<input type="hidden" name="friend_uid_sel" value="'.$uid.'|'.$username.'">
				<br/><br/><input type="submit" value="'.$this->ca->lang_l('send').'"/>
				<input type="button" value="'.$this->ca->lang_l('cancel').'"  onclick="$(\'#reply_message_form'.$msg_id.'\').hide()"/>
			</form>
		</div>';
	}

	protected function forward_message_form($url,$dropdown_friends,$msg_id='0',$message)
	{
		$disable_submit=false;
		if($dropdown_friends!='')
			$select_users='<span class="rvts8">'.$this->ca->lang_l('to').' *</span><br/>'.$dropdown_friends;
		else{
			$select_users=$this->ca->lang_l('none users');
			$disable_submit=true;
		}
		return '
		<div id="forward_message_form'.$msg_id.'" style="display:none;margin:10px;padding-bottom:10px;">
			<form action="'.$url.'" method="post">
				<span class="rvts8">'.$this->ca->lang_l('subject').'</span><br/>
				<input type="text" name="subject" style="width:400px"/><br/>
				<span class="rvts8">'.$this->ca->lang_l('message').' *</span><br/>
				<textarea name="message" style="width:400px; height:85px">'.$message.'</textarea><br/>'
				.$select_users
				.'<br/><br/><input type="submit" '.($disable_submit?'disabled':'').' value="'.$this->ca->lang_l('send').'"/>
				<input type="button" value="'.$this->ca->lang_l('cancel').'"  onclick="$(\'#forward_message_form'.$msg_id.'\').hide()"/>
			</form>
		</div>';
	}

	protected function send_message_form($url,$dropdown_friends,$uid='0',$msg_id='0',$is_user_selected=false,$username='')
	{
		$select_users='';
		$disable_submit=false;
		if($is_user_selected)
			$select_users='<input type="hidden" name="friend_uid_sel" value="'.$uid.'|'.$username.'">';
		else
		{
			if($dropdown_friends!='')
				$select_users='<span class="rvts8">'.$this->ca->lang_l('to').' *</span><br/>'.$dropdown_friends;
			else{
				$select_users=$this->ca->lang_l('none users');
				$disable_submit=true;
			}
		}
		return '
		<div id="send_message_form'.($msg_id>0?$msg_id:$uid).'" style="display:none;margin:10px;padding-bottom:10px;">
			<form action="'.$url.'" method="post">
				<span class="rvts8">'.$this->ca->lang_l('subject').'</span><br/>
				<input type="text" name="subject" style="width:400px"/><br/>
				<span class="rvts8">'.$this->ca->lang_l('message').' *</span><br/>
				<textarea name="message" style="width:400px; height:85px"></textarea><br/>'
				.$select_users
				.'<br/><br/><input type="submit" '.($disable_submit?'disabled':'').' value="'.$this->ca->lang_l('send').'"/>
				<input type="button" value="'.$this->ca->lang_l('cancel').'"  onclick="$(\'#send_message_form'.($msg_id>0?$msg_id:$uid).'\').hide()"/>
			</form>
		</div>';
	}

	protected function build_dropdown_friends()
	{
		global $db;
		
		$select='';
		if(!$this->isAdmin)
		{
			if($this->ch_settings['ch_only_friends'])
			{
				$where='(f.uid='.$this->logged_userId.' OR f.friend_uid='.$this->logged_userId.') AND f.my_status=1 AND friend_status=1';
				$que='SELECT u.avatar, u.username, if(f.friend_uid<>"'.$this->logged_userId.'",CONCAT(f.friend_uid,"|",u.username),CONCAT(f.uid,"|",u.username)) as user_id_messenger, 
				CONCAT(u.first_name," ",u.surname) as full_name
				FROM '.$db->pre.'ca_messenger_friends as f
				RIGHT JOIN '.$db->pre.'ca_users as u ON u.uid=if(f.friend_uid<>"'.$this->logged_userId.'",f.friend_uid,f.uid) AND u.confirmed=1 AND u.status=1'
				.' WHERE '.$where
				.' GROUP by u.username'
				.' ORDER BY u.username ASC';
				if($this->ch_settings['ch_send_toadmin'])
					$que_admins='SELECT u.avatar, CONCAT("[a]"," ",u.username) as username, CONCAT(u.uid,"|[a] ",u.username) as user_id_messenger, CONCAT(u.first_name," ",u.surname) as full_name
					FROM '.$db->pre.'ca_users as u
					RIGHT JOIN '.$db->pre.'ca_users_access as ac ON u.uid=ac.user_id AND ac.access_type=9 
					WHERE u.confirmed=1 AND u.status=1 AND u.uid<>'.$this->logged_userId
					.' GROUP by u.username'
					.' ORDER BY u.username ASC';
			}
			else
				$que='SELECT u.avatar, if(ac.access_type=9,CONCAT("[a]"," ",u.username), u.username) as username, CONCAT(u.uid,"|",if(ac.access_type=9,CONCAT("[a]"," ",u.username), u.username)) as user_id_messenger, 
				CONCAT(u.first_name," ",u.surname) as full_name
				FROM '.$db->pre.'ca_users as u'
				.($this->ch_settings['ch_send_toadmin']?' LEFT JOIN '.$db->pre.'ca_users_access as ac ON u.uid=ac.user_id':' RIGHT JOIN '.$db->pre.'ca_users_access as ac ON u.uid=ac.user_id AND ac.access_type<>9')
				.' WHERE uid<>'.$this->logged_userId.' AND confirmed=1 AND status=1'
				.' GROUP by u.username'
				.' ORDER BY username ASC';
		}
		else
			$que='SELECT u.avatar, if(ac.access_type=9,CONCAT("[a]"," ",u.username), u.username) as username, CONCAT(u.uid,"|",if(ac.access_type=9,CONCAT("[a]"," ",u.username), u.username)) as user_id_messenger,
			CONCAT(u.first_name," ",u.surname) as full_name
			FROM '.$db->pre.'ca_users as u'
			.($this->ch_settings['ch_disable_messenger_users']?' RIGHT JOIN '.$db->pre.'ca_users_access as ac ON u.uid=ac.user_id AND ac.access_type=9'
			:' LEFT JOIN '.$db->pre.'ca_users_access as ac ON u.uid=ac.user_id')
			.' WHERE uid<>'.$this->logged_userId.' AND confirmed=1 AND status=1'
			.' GROUP by u.username'
			.' ORDER BY u.username ASC';
		
		$query_res=$db->query($que);
		if($query_res&&$query_res->num_rows!==0)
		{
			$select='<select rel="0" name="friend_uid_sel[]" multiple="multiple" class="niceSelect" style="display:none">
			<option value="all" data-style="">all</option>';
			while ($row = @mysqli_fetch_assoc($query_res)) 
			{
				$row=array_map("stripslashes", $row);
				if(!isset($row['avatar']) || $row['avatar']=='')
					$av_path=CA::getDBSettings($db,'c_avatar');
				else
					$av_path=$row['avatar'];
				$av_path=strpos($av_path,'http')!==false||$av_path==''?$av_path:$this->ca->ca_site_url.$av_path;
				$select.='<option value="'.$row['user_id_messenger'].'" data-style="'.$av_path.'" data-extra-content="'.$row['full_name'].'">'.$row['username'].'</option>';
			}
			mysqli_free_result($query_res);
	
			$admin_options='';
			if(isset($que_admins))
			{
				$query_res=$db->query($que_admins);
				if($query_res&&$query_res->num_rows!==0)
				{
					while ($row = @mysqli_fetch_assoc($query_res)) 
					{
						$row=array_map("stripslashes", $row);
						if(!isset($row['avatar']) || $row['avatar']=='')
							$av_path=CA::getDBSettings($db,'c_avatar');
						else
							$av_path=$row['avatar'];
						$av_path=strpos($av_path,'http')!==false||$av_path==''?$av_path:$this->ca->ca_site_url.$av_path;
						$admin_options.='<option value="'.$row['user_id_messenger'].'" data-style="'.$av_path.'" data-extra-content="'.$row['full_name'].'">'.$row['username'].'</option>';
					}
					mysqli_free_result($query_res);
				}
			}
			$select.=$admin_options;
		
			$admin_main='';
			if(($this->ch_settings['ch_send_toadmin']&&$this->logged_userId!=-1)||($this->isAdmin&&$this->logged_userId!=-1))
			{
				$av_path=strpos($this->f->admin_avatar,'http')!==false||$this->f->admin_avatar==''?$this->f->admin_avatar:$this->ca->ca_site_url.$this->f->admin_avatar;
				$admin_main.='<option value="-1|'.$this->ca->lang_l('admin').'" data-style="'.$av_path.'" data-extra-content="'.$this->f->admin_nickname.'">[a] '.$this->ca->lang_l('admin').'</option>';
			}
			$select.=$admin_main.'</select>';
		}
		return $select;
	}
}

class ca_editprofile_screen extends ca_visitor_screens
{

	public function process_editprofile()
	{
		global $db;

 		$lang_f=$this->f->lang_f[$this->ca->ca_ulang_id];
		$this->ca->lang_r=$this->f->lang_reg[$this->ca->ca_ulang_id];
		$username=$this->logged_user_data['username'];
		$noauth=!Editor::innovaCheckAuth($username,$this->logged_user_data);
		$update_array=array();
		$ajax_call=isset($_POST['ajc'])&&(int)$_POST['ajc']===1;

		if(isset($_POST['cc']))
		{
			$ccheck=$_POST['cc']=='1';
			$issues=array();
			if(!Validator::validateEmail($_POST['email']))
				$issues[]=($ccheck?'email|':'').$lang_f['Email not valid'];
			else if(empty($_POST['email']))
				$issues[]=($ccheck?'email|':'').$lang_f['Required Field'];
			else if($this->ca->users->duplicated_email_check($_POST['email'],$this->logged_user_data['uid']))
				$issues[]=($ccheck?'email|':'').$lang_f['Email in use'];
			else
				$update_array['email']=Formatter::stripTags($_POST['email']);

			foreach($this->f->ca_users_fields_array as $k=> $v)
			{
				if(in_array($v['itype'],$this->ca->ca_user_fieldtypes))
				{
					$req=!(isset($v['req'])&&$v['req']=='0');

					if($k!='avatar')
					{
						if($req&&(!isset($_POST[$k])||empty($_POST[$k])))
							$issues[]=($ccheck?$k.'|':'').$lang_f['Required Field'];
						elseif(isset($_POST[$k]))
							$update_array[$k]=Formatter::stripTags($_POST[$k]);
						elseif($v['itype']=='checkbox')
							$update_array[$k]='0'; //custom profile checkboxes not checked
					}
					else
					{
						if($noauth)
						{
							if(isset($_FILES[$k]['name']))
								$file_name=$_FILES[$k]['name'];
							else
							{
								$file_name=$_POST['up_'.$k];
								if(strpos($file_name,'fakepath')!=false)
									$file_name=Formatter::GFS($file_name,'fakepath\\','');
							}
							if($file_name=='')
								$file_name=$this->logged_user_data[$k];
						}
						else
							$file_name=Formatter::stripTags($_POST[$k]);

						$file_name=str_replace('../','',$file_name);
						if($req && $file_name=='')
							$issues[]=($ccheck?$k.'|':'').$lang_f['Required Field'];
						elseif($file_name!='' && !File::is_image($file_name))
							$issues[]=($ccheck?'avatar|':'').$this->ca->lang_r['wrong_ext'];
						elseif($noauth)
						{
							if(!$ccheck&&isset($_FILES[$k]['name'])&&$_FILES[$k]['name']!='') //only do something if it's avatar field and file is uploaded
							{
								$avatar_dest_path=$this->ca->users->upload_avatar($this->logged_user_data['uid'],$_FILES[$k]);
								$update_array[$k]=$avatar_dest_path;
							}
						}
						else
							$update_array[$k]=$file_name;
					}
				}
			}

			if(!empty($issues))
				$issues[]=($ccheck?'error|':'').$lang_f['validation failed'];

			if($ccheck)
			{
				$output=implode('|',$issues);
				if(count($issues)>0)
					print '0'.$output;
				else
					print '1';
				exit;
			}

			if(empty($issues) && isset($this->logged_user_data['username']) && $this->logged_user_data['username']==$username)
			{
				$uid=$this->logged_user_data['uid'];
				$res=$db->query_update('ca_users',$update_array,'uid='.$uid);
				if($res!==false)
				{
					$news_data=$this->ca->users->build_news_array($uid);
					$db->query('DELETE FROM '.$db->pre.'ca_users_news WHERE user_id='.$uid);
					foreach($news_data as $k=> $news)
						$db->query_insert('ca_users_news',$news);
				}

				if(isset($_GET['ref_url']))
				{
					$u=$_GET['ref_url'];
					$u=str_replace('../','',$u);
				}
				if($res!==false)
					ca_log::write_log('editprofile',$uid,$username,'success');

				if($this->f->ca_settings['notify_profilechange'])
				{
					$subject='User profile change';
					$content='';
					$rec=$db->query_first('
						SELECT *
						FROM '.$db->pre.'ca_users
						WHERE uid = '.$uid);

					foreach($this->f->ca_users_fields_array as $k=> $v)
					{
						if(!in_array($k,array('password','status','confirmed','self_registered_id','self_registered','display_name')))
						{
							$dname=$this->ca->lang_l($k);
							$value=$k=='creation_date'?ca_functions::format_output_date(strtotime($rec[$k])):$rec[$k];
							$content.=$dname.'='.$value.'<br>';
						}
					}
					$content.=$this->ca->lang_l('last modified').'='.ca_functions::format_output_date(time()).'<br>';

					MailHandler::sendMailCA($db,$content,$subject,$this->f->ca_settings['sr_admin_email']);
				}

				$msgOK='<span class="rvts8">'.$this->ca->lang_l('changes saved').'</span>';
				if($ajax_call)
				{
					print $msgOK;
					exit;
				}
				Linker::checkReturnURL();
				$table_data=array();
				$table_data[]=array('',$msgOK);
				$output=Builder::addEntryTable($table_data);
			}
		}
		else
			$output=$this->build_editprofile_form($username,$noauth);

		$this->ca->ca_scripts.='
			 $(document).ready(function() {
			 frr=$(\'#myprofile\');
			 if(frr!=null){frr.prepend(\'<input type="hidden" id="cc" name="cc" value="1"/>\');
			 frr.submit(function(e){
				e.preventDefault();
				$(".frmhint").empty();
				$("#myprofile .input1").removeClass("inputerror");
				var ff="";
				$(":file").each(function(i){
					 ff+="&up_"+$(this).attr("name")+"="+$(this).val();});
				$.post(frr.attr(\'action\'),frr.serialize()+ff,function(re){
					if(re==\'1\'){
						cc=$(".myprofile #cc");
						cc.val("0");'.
						($noauth?'
						frr.unbind("submit");
						frr.submit();':'
						submitAjaxR(frr,1);').'
						cc.val(\'1\');
					}
					else if(re.charAt(0)==\'0\') {
						errors=re.substring(1).split(\'|\');
						for(i=0;i<errors.length;i=i+2) {
							$(\'#myprofile_\'+errors[i]).append(\'<br />\'+errors[i+1]);
							$(\'#myprofile input[name=\'+errors[i]+\']\').addClass(\'inputerror\');
						}
					}
					else $(\'#myprofile_error\').html(re);});
				})
			}});';
		$this->output($output);
	}

	protected function build_editprofile_form($username,$noauth)
	{
	 	$err='<span id="myprofile_%s" class="rvts12 frmhint"></span>';
		$lab_required=' (<em><small>'.$this->ca->lang_l('required').'</small></em>)';
		$table_data=array();
		$mail=$this->logged_user_data['email'];
		$table_data[]=array($this->ca->lang_l('username'),
			 Builder::buildInput('username',$username,'width:280px;','','text','disabled="disabled"'));
		$table_data[]=array($this->ca->lang_l('email').$lab_required,
			 Builder::buildInput('email',$mail,'width:280px;').sprintf($err,'email'));
		foreach($this->f->ca_users_fields_array as $k=> $v)
		{
			$dname=$this->ca->lang_l($k);
			$req=(!(isset($v['req'])&&$v['req']=='0'))?$lab_required:'';
			$hidden=isset($v['hidinprof'])&&$v['hidinprof']=='1';

			if($hidden)
				$dname=$req='';

			$value=$this->logged_user_data[$k];
			$value=ca_functions::ca_un_esc($value);

			if($v['itype']=="userinput")
				$table_data[]=array($dname.$req,
					 Builder::buildInput($k,$value,'width:280px;','','text','','','','',false,'',$hidden?'hidden ca_br':'').sprintf($err,$k));
			elseif($v['itype']=="checkbox")
				$table_data[]=array('','<input type="checkbox" name="'.$k.'" '.($value=='1'?'checked="checked"':'')
					.' value="1" '.($hidden?'class="hidden ca_br" ':'').'> <span class="rvts8 a_editcaption'.($hidden?' hidden ca_br':'').'">'.$dname.$req.'</span>');
			elseif($v['itype']=="avatar")
			{
				if($noauth)
					$x=Builder::buildInput($k,$value,'width:280px;','','file','','','','',false,$k,$hidden?'hidden ca_br':'');
				else
					$x=Builder::buildInput($k,$value,'width:280px;','','text','','','','',false,$k,$hidden?'hidden ca_br':'').
						' <input type="button" value="'.$this->ca->lang_l('browse').'" onclick="openAsset(\''.$k.'\')" name="btnAsset2" class="input1">';

				$av_path=$this->ca->getAvatarPath($value);
				$x.='<br>'.'<img id="ima_avatar" src="'.$av_path.'" alt="" style="'.(($av_path=='')?'display:none;':'').'height:60px;padding-top: 5px;">';
				$table_data[]=array($dname,$x.sprintf($err,$k));
			}
			elseif($v['itype']=="listbox")
			{
				$xdata=explode(';',$v['values']);
				$table_data[]=array($dname.$req,Builder::buildSelect($k,$xdata,$value,'style="width:283px;'.($hidden?' display:none;':'').'"','value').sprintf($err,$k));
			}
		}

		$calendar_categories=$this->ca->get_calendar_categories($username,$this->ca->ca_lang);
		if(!empty($calendar_categories))
		{
			$news_for=array();
			if(isset($this->logged_user_data['news'])&&!empty($this->logged_user_data['news']))
			{
				foreach($this->logged_user_data['news'] as $val)
					$news_for[]=$val['page_id'].'%'.$val['category'];
			}

			$news_line='';
			foreach($calendar_categories as $k=> $v)
			{
				$ckbox_value=$v['pageid'].'%'.$v['catid'];
				$news_line.='<input type="checkbox" name="news_for[]"" value="'.$ckbox_value.'" '.
					(in_array($ckbox_value,$news_for)?'checked="checked" ':'').'> <span class="rvts8">'.$v['pagename'].' - '.$v['catname'].'</span>'.F_BR;
			}
			$news_line.='<br>';
			$table_data[]=array($this->ca->lang_l('want to receive notification'),$news_line);
		}
		$end=$this->form_buttons('save',false).'
			<div class="myprofileerror">
				<span class="rvts12 frmhint" id="myprofile_error"></span>
			</div>';
		if(isset($_REQUEST['saved']))
			 $end.='<span class="rvts8">'.$this->ca->lang_l('changes saved').'</span>';

		$output=Builder::addEntryTable($table_data,$end,'','',false,'','',
				'<form id="myprofile" class="myprofile" name="myprofile" action="'.$this->ca->ca_abs_url.'" method="post" enctype="multipart/form-data">
						<input type="hidden" name="process" value="editprofile">'
						.(isset($_REQUEST['pageid'])?'<input type="hidden" name="pageid" value="'.$_REQUEST['pageid'].'">':'').'
						<input type="hidden" name="r" value="'.Linker::buildReturnURL(false,'saved=1').'">');

		if($this->f->ca_settings['customcodeprofile']!='')
			$output.=$this->f->ca_settings['customcodeprofile'];

		return $output;
	}
}

class ca_vieworders_screen extends ca_visitor_screens
{
	public function process_vieworders() // prints list of all shops that redirect to user orders overview
	{
		$output=$this->build_view_orders();
		$this->output($output);
	}

	public function build_view_orders($templ_in_root=false,$dont_redirect=false)
	{
		$output='';
		$table_data=array();
		$pages_list=$this->ca->sitemap->ca_get_pages_list();
		$last_url='';
		$ca_template='';

		$reqOrdScript='
<script type="text/javascript">
function sv(id){$("#"+id).toggle();};
function svc(id){$("#"+id).hide();}
function request_orders(ev){
	if($(ev.target).attr("onClick") != undefined) return true;
	ev.preventDefault();
	$(".orders_shop_url").css("font-weight","");
	$(this).css("font-weight","bold");
	$.get($(this).attr("href")+"&hidetpl",function(data){
		$("#cust_prof_orders").hide().html(data).slideDown("slow");
		$(".a_n.a_listing").css("height","auto");
		$(".a_detail").css("visibility","hidden").each(function() {
				$(this).find("a:first").click(request_order);
		});
	});
};
$(".orders_shop_url").click(request_orders);
if($(".orders_shop_url").length==1)
	 $(".orders_shop_url").trigger("click");
</script>
<div id="cust_prof_orders"></div>';
		$this->ca->ca_resolve_template(true,$ca_template,$templ_in_root);
		foreach($pages_list as $pv)
		{
			if(isset($pv['id']) && $pv['id']==SHOP_PAGE)
			{
				if($pv['protected']=='TRUE' && !$pv['pprotected'])
				{
					$page_info=CA::getPageParams($pv['pageid'],$this->ca->rel_path);
					if(!User::mHasReadAccess($this->ca->user->getAllData(),$page_info))
						continue;
				}
				$url=str_replace('documents/centraladmin.php','',$this->ca->ca_abs_url).str_replace('../','',$pv['url']);
				$table_data[]='<a class="rvts8 orders_shop_url" href="'.$url.'?myorders=1'.($this->f->ca_settings['pwchange_enable']
								&& $this->ca->logged_user_data['pass_changeable']?'&p=1':'').($this->f->ca_settings['profilechange_enable']?'&pr=1':'').'">'. $pv['name'].'</a><br>';
				$last_url=$url;
			}
		}
		if(count($table_data)==1 && $last_url!='')
		{
			if($dont_redirect)
				return $table_data[0].F_BR.$reqOrdScript;
			else
				Linker::redirect($last_url.'?myorders=1'); //single shop, don't list 1 shop, show orders directly
		}
		$output=Builder::adminTable('','',$table_data).$reqOrdScript;

		$uid=$this->logged_user_data['uid'];
		if(!$uid)
		{
			print $this->ca->login->build_login_form();
			exit;
		}//just to be sure user is logged
		return $output;
	}
}

class ca_editpassword_screen extends ca_visitor_screens
{
	public function process_changepass()
	{
		global $db;

		$output='';
		$lang_f=$this->f->lang_f[$this->ca->ca_ulang_id];
		$ajax_call=isset($_POST['ajc'])&&(int)$_POST['ajc']===1;
		$msg=array();
		$ccheck=isset($_POST['cc'])&&(int)$_POST['cc']===1;
		if(isset($_POST['save'])||$ccheck)
		{
			if(empty($_POST['oldpassword']))
				$msg['oldpassword']=$this->ca->lang_l('fill in').' '.$this->ca->lang_l('old password');
			elseif($this->logged_user_data['password']!=crypt($_POST['oldpassword'],$this->logged_user_data['password']))
				$msg['oldpassword']=$this->ca->lang_l('wrong old');

			if(empty($_POST['newpassword']))
				$msg['newpassword']=$this->ca->lang_l('fill in').' '.$this->ca->lang_l('new password');
			else
			{
				$n_pwd=trim($_POST['newpassword']);
				$n_pwd_res=Password::checkStrenght($n_pwd,$this->ca->thispage_id_inca);

				if(!$n_pwd_res['pass_is_ok'])
					$msg['newpassword']=$n_pwd_res['msg'];
				elseif(empty($_POST['repeatedpassword']))
					$msg['repeatedpassword']=$this->ca->lang_l('repeat password');
				elseif($n_pwd!=$_POST['repeatedpassword'])
					$msg['repeatedpassword']=$this->ca->lang_l('password and repeated password');
				elseif($this->logged_user_data['pass_changeable']==0)
					$msg['passnotchangeable']='pass not changeable';
			}
			if(!empty($msg))
			{
				if($ccheck)
				{
					$err_str='0';
					foreach($msg as $msgk=> $msgv)
						$err_str.=$msgk.'|'.$msgv.'|';
					$err_str.='error|'.$lang_f['validation failed'];
					print $err_str;
					exit;
				}
				else
					$output=$this->build_changepass_form($msg);
			}
			else
			{
				if($ccheck)
				{
					print '1';
					exit;
				}
				if(isset($this->logged_user_data['username']))
				{
					$res=$db->query_update('ca_users',array('password'=>crypt($_POST['newpassword'])),'uid='.$this->logged_user_data['uid']);

					$show_msg='<span class="rvts8">'.$this->ca->lang_l('changes saved').'</span>';
					if(isset($_GET['ref_url']))
					{
						$u=$_GET['ref_url'];
						if(strpos($_GET['ref_url'],'/')===false&&$this->ca->template_in_root==false)
							$u='../'.$u;
					}
					if($res!==false)
						ca_log::write_log('changepass',$this->logged_user_data['uid'],$this->logged_user_data['username'],'success');
					if($ajax_call)
					{
						print $show_msg;
						exit;
					}

					Linker::checkReturnURL();
					$table_data=array();
					$table_data[]=array('',$show_msg);
					$output=Builder::addEntryTable($table_data);
				}
			}
		}
		else
			$output=$this->build_changepass_form($msg);

		$this->output($output);
	}

	public function build_changepass_form($msg,$use_return=false)
	{
		$pass_str_labels=Password::checkStrenght('',$this->ca->thispage_id_inca,1); //this will get the array with the labels only
		$hint=F_BR.'<span id="changepass_%s" class="rvts12 frmhint">%s</span>';
		$lab_required=' (<em><small>'.$this->ca->lang_l('required').'</small></em>)';
		$table_data=array();
		$table_data[]=array($this->ca->lang_l('old password').$lab_required,'<input class="input1" type="password" name="oldpassword" value="" style="width:280px">'.sprintf($hint,'oldpassword',(isset($msg['oldpassword'])?$msg['oldpassword']:'')));
		$table_data[]=array($this->ca->lang_l('new password').$lab_required,'<input class="input1 passreginput" type="password" name="newpassword" rel="" value="" style="width:280px">'.sprintf($hint,'newpassword',(isset($msg['newpassword'])?$msg['newpassword']:''))
			.F_BR.Password::showMeter($pass_str_labels));
		$table_data[]=array($this->ca->lang_l('repeat password').$lab_required,'<input class="input1" type="password" name="repeatedpassword" value="" style="width:280px">'.sprintf($hint,'repeatedpassword',(isset($msg['repeatedpassword'])?$msg['repeatedpassword']:'')));
		$end=$this->form_buttons('save',false).'<div class="myprofileerror"><span class="rvts12 frmhint" id="changepass_error"></span></div>';

		$form='<form id="changepass" class="myprofile" action="'.$this->ca->ca_abs_url.'?process=changepass'.$this->ca->ca_l_amp.(isset($_GET['pageid'])?'&amp;pageid='.$_GET['pageid']:'').'" method="post">'
			.($use_return?'<input type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">':'');
		$output=Builder::addEntryTable($table_data,$end,'','',false,'','',$form);
		return $output;
	}

}

class custom_profile extends ca_objects
{
	public function strip_macro($macro)
	{
		return substr($macro,1,-1);
	}

	public function process_custom_profile($logged_user_data,$ca_gt_page)
	{
		$templ_root=(strpos($ca_gt_page,'/')===false);
		$root_prefix=$templ_root?'':'../';
		$ca_gt_path=((strpos($ca_gt_page,'../')===false)&&$this->f->ca_rel_path!=''?'../':'').$ca_gt_page; //search output params

		if($this->f->ca_rel_path=='')
			$ca_gt_path=str_replace('../','',$ca_gt_path);

		$ca_tpl=File::read($ca_gt_path);
		$ca_template_content_src=$ca_template_content=PageHandler::getArea($ca_tpl);
		if($templ_root)
			$ca_tpl=str_replace('</title>','</title>
				 <base href="'.str_replace('documents/centraladmin.php','',$this->ca->ca_abs_url).'">',$ca_tpl);

		$sitemap_params=array();
		$sitemap_output='';
		if(strpos($ca_template_content,'%SITE_MAP')!==false)
		{
			if(strpos($ca_template_content,'%SITE_MAP(')!==false)
			{
				$sitemap_params=array_map('self::strip_macro',explode(',',Formatter::GFS($ca_template_content,'%SITE_MAP(',')%')));
				$sitemap_viewer= new ca_sitemap_viewer($this->ca);
				$sitemap_viewer->build_sitemap_area($sitemap_output,$logged_user_data,$sitemap_params);
				$ca_template_content=str_replace(Formatter::GFSAbi($ca_template_content,'%SITE_MAP(',')%'),$sitemap_output,$ca_template_content);
			}
			elseif(strpos($ca_template_content,'%SITE_MAP%')!==false)
			{
				$sitemap_viewer= new ca_sitemap_viewer($this->ca);
				$sitemap_viewer->build_sitemap_area($sitemap_output,$logged_user_data,$sitemap_params);
				$ca_template_content=str_replace('%SITE_MAP%',$sitemap_output,$ca_template_content);
			}
		}
		if(strpos($ca_template_content,'%CHANGE_PASS')!==false)
		{
			if(strpos($ca_template_content,'%CHANGE_PASS(')!==false)
			{
				$ch_pass_obj=Formatter::GFSAbi($ca_template_content,'%CHANGE_PASS(',')%');
				$ca_template_content=str_replace($ch_pass_obj,$this->build_changepass_form_custom($ch_pass_obj,'',true),$ca_template_content);
			}
			elseif(strpos($ca_template_content,'%CHANGE_PASS%')!==false)
			{
				$ca_editpassword = new ca_editpassword_screen($this->ca,$logged_user_data);
				$ca_template_content=str_replace('%CHANGE_PASS%',$ca_editpassword->build_changepass_form('',true),$ca_template_content);
			}
		}
		if(strpos($ca_template_content,'%MYORDERS%'))
		{
			$vieworders_screen = new ca_vieworders_screen($this->ca,$logged_user_data);
			$ca_template_content=str_replace('%MYORDERS%',$vieworders_screen->build_view_orders(true,true),$ca_template_content);
		}
		if(strpos($ca_template_content,'%PROFILE')!==false)
		{
			$noauth=!Editor::innovaCheckAuth($logged_user_data['username'],$logged_user_data);
			if(strpos($ca_template_content,'%PROFILE(')!==false)
			{
				$img_params=Formatter::GFS($ca_template_content,'%SCALE[',']%');
				$scale_array=explode(',',$img_params);
				$ca_template_content=str_replace(Formatter::GFSAbi($ca_template_content,'%SCALE[%image',']%'),'%image%',$ca_template_content);
				$profile_obj=Formatter::GFSAbi($ca_template_content,'%PROFILE(',')%');
				$ca_template_content=str_replace($profile_obj,
						$this->build_edit_profile_form_custom($profile_obj,$logged_user_data,$noauth,$scale_array),$ca_template_content);
			}
			elseif(strpos($ca_template_content,'%PROFILE%')!==false)
			{
				$editprofile_screen = new ca_editprofile_screen($this->ca,$logged_user_data);
				$ca_template_content=str_replace('%PROFILE%',
						$editprofile_screen->build_editprofile_form($logged_user_data['username'],$noauth),$ca_template_content);
			}
		}

		$output=str_replace($ca_template_content_src,$ca_template_content,$ca_tpl);

		$val_js='
$(document).ready(function() {
	frr=$(\'#myprofile\');
	if(frr!=null){
	frr.prepend(\'<input type="hidden" id="cc" name="cc" value="1"/>\');
	frr.submit(function(e){
		e.preventDefault();
		$(".frmhint").empty();
		$("#myprofile .input1").removeClass("inputerror");
		var ff="";$(":file").each(function(i){
				ff+="&up_"+$(this).attr("name")+"="+$(this).val();
		});
		$.post(frr.attr("action"),frr.serialize()+ff,function(re){
			if(re=="1"){
				cc=$(".myprofile #cc");
				cc.val("0");
				submitAjaxR(frr,0);
				cc.val("1");
			}
			else if(re.charAt(0)=="0") {
				errors=re.substring(1).split("|");
				for(i=0;i<errors.length;i=i+2) {
					$("#myprofile_"+errors[i]).append("<br>"+errors[i+1]);
					$("#myprofile input[name="+errors[i]+"]").addClass("inputerror");
				}
			}
			else
				$("#myprofile_error").html(re);
		});
	})
	};
	$("#changepass").utils_frmvalidate(0,0,0,0,0,0,0,submitAjaxR);
});';
		$output=Builder::includeScript($val_js,$output,array($root_prefix.'documents/ca'));
		$output=$this->ca->ca_addassets($output,$templ_root);
		print $output;
		exit;
	}

	protected function build_changepass_form_custom($tpl,$msg,$use_return=false)
	{
		$tpl=Formatter::GFS($tpl,'%CHANGE_PASS(',')%');
		$theme=$this->f->form_class!=='';
		$label_class=$theme?'label_title':'rvts8';
		$input_class=$theme?'text large':'signguest_input input1';
		$button_class=$theme?'button':'input1';

		$span_lbl='<span class="'.$label_class.'">%s</span>';
		$hint=F_BR.'<span id="changepass_%s" class="'.$label_class.' frmhint">%s</span>';
		$lab_required=' (<em><small>'.$this->ca->lang_l('required').'</small></em>)';

		$pass_str_labels=Password::checkStrenght('',$this->ca->thispage_id_inca,1); //this will get the array with the labels only
		$add_labels=strpos($tpl,'%addlabels%')!==false;
		$tpl=str_replace('%addlabels%','',$tpl);

		$old_pwd=($add_labels?sprintf($span_lbl,$this->ca->lang_l('old password').$lab_required):'')
			.'<input class="'.$input_class
			.'" type="password" id="oldpassword" name="oldpassword" value="" style="width:280px">'
			.sprintf($hint,'oldpassword',(isset($msg['oldpassword'])?$msg['oldpassword']:''));
		$new_pwd=($add_labels?sprintf($span_lbl,$this->ca->lang_l('new password').$lab_required):'')
			.'<input class="'.$input_class
			.' passreginput" type="password" id="newpassword" name="newpassword" rel="" value="" style="width:280px">'
			.sprintf($hint,'newpassword',(isset($msg['newpassword'])?$msg['newpassword']:''))
			.F_BR.Password::showMeter($pass_str_labels);
		$repeat_pwd=($add_labels?sprintf($span_lbl,$this->ca->lang_l('repeat password').$lab_required):'')
			.'<input class="'.$input_class
			.'" type="password" id="repeatedpassword" name="repeatedpassword" value="" style="width:280px">'
			.sprintf($hint,'repeatedpassword',(isset($msg['repeatedpassword'])?$msg['repeatedpassword']:''));

		$tpl=str_replace('%save_btn%','<input name="save" class="'.$button_class.'" type="submit" value="'
			.$this->ca->lang_l('save').'">',$tpl);
		$tpl=str_replace(array('%old_pass%','%new_pass%','%repeat_pass%'),array($old_pwd,$new_pwd,$repeat_pwd),$tpl);

		return '<form id="changepass" class="myprofile" action="'.$this->ca->ca_abs_url."?process=changepass"
				.$this->ca->ca_l_amp."&amp;pageid=".(isset($_GET['pageid'])?intval($_GET['pageid']):'0').'" method="post">'.($use_return?'<input type="hidden" name="r" value="'.Linker::buildReturnURL(false)
				.'">':'').$tpl.'<div class="myprofileerror"><span class="rvts12 frmhint" id="changepass_error"></span></div></form>';
	}

	protected function build_edit_profile_form_custom($profile_obj,$user_data,$noauth,$scale_array)
	{
		$lab_required=' (<em><small>'.$this->ca->lang_l('required').'</small></em>)';

		$profile_obj=Formatter::GFS($profile_obj,'%PROFILE(',')%');
		$theme=$this->f->form_class!=='';
		$label_class=$theme?'label_title':'rvts8';
		$input_class=$theme?'text large':'signguest_input input1';
		$button_class=$theme?'button':'input1';

		$add_labels=strpos($profile_obj,'%addlabels%')!==false;
		$no_inputs=strpos($profile_obj,'%noinputs%')!==false;
		$profile_obj=str_replace(array('%addlabels%','%noinputs%'),'',$profile_obj);
		$span='<span class="%s" style="width:%spx;margin-left:5px;">%s</span>';

		$input='<input class="%s" type="text" name="%s" id="%s" value="%s" style="width:%spx;" %s>';
		$err='<span id="myprofile_%s" class="'.$label_class.' frmhint"></span>';
		$input_f='<input class="'.$input_class.' %s" type="file" name="%s" id="%s" value="%s" style="width:%spx" %s>';

		$has_avatar_img=strpos($profile_obj,'%image%')!==false;

		foreach($this->f->ca_users_fields_array as $k=> $v)
		{
			if($k=='password')
				continue;
			$custom_section='';
			if($add_labels)
			{
				$dname=$this->ca->lang_l($k);
				$req=(!(isset($v['req'])&&$v['req']=='0'))?$lab_required:'';
			}
			else
			{
				$dname='';
				$req='';
			}
			$value=ca_functions::ca_un_esc($user_data[$k]);

			if($v['itype']=="userinput"||$k=='username'||$k=='email')
			{
				if($no_inputs)
					$custom_section=$dname.$req.sprintf($span.$err,$label_class,'280',$value,$k);
				else
					$custom_section=$dname.$req.sprintf($input.$err,$input_class,$k,$k,$value,'280',($k=='username'?' disabled="disabled"':''),$k);
			}
			elseif($v['itype']=="checkbox")
				if($no_inputs)
				{
					$custom_section=$dname.$req.sprintf($span.$err,$label_class,'280',($value=='1'?'+':'-'),$k);
				}
				else
				{
					$custom_section='<input type="checkbox" name="'.$k.'" '.($value=='1'?'checked="checked"':'')
						.' value="1"> <span class="'.$label_class.' a_editcaption">'.$dname.$req.'</span>';
				}
			elseif($v['itype']=="avatar")
			{
				$av_path=$this->ca->getAvatarPath($value);
				if($no_inputs)
					$x=sprintf($span,$label_class,'280',$value);
				else
				{
					if($noauth)
						$x=sprintf($input_f,$input_class,$k,$k,'','280','');
					else
						$x=sprintf($input,$input_class,$k,$k,$value,'280','').' <input type="button" value="'
							.$this->ca->lang_l('browse').'" onclick="openAsset(\''.$k.'\')" name="btnAsset2" class="'.$button_class.'">';
				}
				$x.=F_BR.'<img id="ima_'.$k.'" src="'.$av_path.'" alt="" style="'.(($av_path=='')?'display:none;':'')
					.'height:60px;padding-top: 5px;">';
				$custom_section=$dname.$x.sprintf($err,$k);
				if($has_avatar_img&&$av_path!='')
					$profile_obj=str_replace('%image%','<img id="ima_p_'.$k.'" src="'.$av_path.'" alt="" style="width:'.$scale_array[1].'px; padding-top: 5px;">',$profile_obj);
				if($custom_section==''||strpos($profile_obj,'%avatar%')===false)
					$profile_obj.='<input type="hidden" name="up_'.$k.'" value="'.$av_path.'">';
			}
			elseif($v['itype']=="listbox")
			{
				$xdata=explode(';',$v['values']);
				$custom_section=$dname.$req.Builder::buildSelect($k,$xdata,$value,'style="width:283px;"','value').sprintf($err,$k);
			}

			if($custom_section!=''&&strpos($profile_obj,'%'.$k.'%')!==false)
				$profile_obj=str_replace('%'.$k.'%',$custom_section,$profile_obj);
			elseif(!(isset($v['req'])&&$v['req']=='0'))
				$profile_obj.='<input type="hidden" name="'.$k.'" value="'.$value.'">';
		}

		if($no_inputs)
			$profile_obj=str_replace('%save_btn%','',$profile_obj);
		else
			$profile_obj=str_replace('%save_btn%','<input name="save" class="'.$button_class
				.'" type="submit" value="'.$this->ca->lang_l('save').'">',$profile_obj);
		return '
			<form id="myprofile" class="myprofile" name="myprofile" action="'.$this->ca->ca_abs_url.'" method="post" enctype="multipart/form-data">
				<input type="hidden" name="process" value="editprofile">
				<input type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">'
				.$profile_obj.'
				<div class="myprofileerror">
					 <span class="rvts12 frmhint" id="myprofile_error"></span>
				</div>
			</form>';
	}
}

$central_admin=new CaClass($ca_pref,$thispage_id);
$central_admin->process();
?>

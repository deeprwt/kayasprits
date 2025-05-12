<?php
/*
	page.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/

interface PageWithComments
{
	public function reindex_search($entry_id,$flag='add');
	public function manage_comments();
	public function format_date($timestamp,$mode='long',$new_date_params='');
	public function format_dateSql($time,$mode='long',$new_date_params='');
	public function db_fetch_entry($entry_id,$where='');
	public function build_permalink($data,$for_bmarks=false,$action='',$use_abs=false,$view='');
	public function db_count($table, $where='');
}

//Ezgplugin (Observer) and Subject classes - to let us extend the core with plugins
abstract class EZGPlugin
{
    public function __construct($subject = null) {
        if (is_object($subject) && $subject instanceof Subject) {
            $subject->attachEZGPlugin($this);
        }
    }

    public function update($subject,$params) {
        // looks for an plugin(observer) method with the state name
        if (method_exists($this, $subject->getState())) {
            call_user_func_array(array($this, $subject->getState()), array($subject,$params));
        }
    }
}

abstract class Subject
{
    protected $plugins;
    protected $state;

    public function __construct() {
        $this->plugins = array();
        $this->state = null;
    }

    public function attachEZGPlugin(EZGPlugin $plugin) {
        $i = array_search($plugin, $this->plugins);
        if ($i === false) {
            $this->plugins[] = $plugin;
        }
    }

    public function detachEZGPlugin(EZGPlugin $plugin) {
        if (!empty($this->plugins)) {
            $i = array_search($plugin, $this->plugins);
            if ($i !== false) {
                unset($this->plugins[$i]);
            }
        }
    }

    public function getState() {
        return $this->state;
    }

    public function setState($state,$params=array()) {
        $this->state = $state;
        $this->notify($params);
    }

    public function notify($params) {
        if (!empty($this->plugins)) {
            foreach ($this->plugins as $plugin) {
                $plugin->update($this,$params);
            }
        }
    }

    public function getEZGPlugins() {
        return $this->plugins;
    }
}

//base class for both live pages (blog, photoblog ...) and page handlers (newsletter, request...)
//the subject lets us set event handling and notify the plugins (observers) for given event
abstract class PageClass extends Subject
{
	public $pversion='ezgenerator v4 - Page 5.9.18 mysql';
	protected $page_is_mobile;
	protected $pg_id;
	protected $pg_pre;
	protected $pg_name;
	protected $pg_dir;
	public $site_base;
	protected $ca_url_base;
	protected $script_path;
	public $full_script_path;
	protected $rel_path;
	public $pg_settings;
	public $all_settings=array();
	protected $innova_def;
 	protected $innova_js;
 	protected $page_type;
 	protected $lang_set;
 	protected $data_tablename;
 	protected $data_table_idfield;
 	protected $data_table_cidfield;
	protected $data_table_publish_field;
 	protected $data_pre;
 	protected $id_param;
	public $page_scripts='';
	public $page_css='';
	public $page_dependencies=array();
	public $inp_width='width:500px;';
	public $admin_email='';

	protected $user;
	protected $f;

	public function __construct($pid,$pdir,$pname,$relpath,$settings,$ptype)
	{
		global $user,$f;

		parent::__construct();
		$this->f=$f;
		$this->pg_id=$pid;
		$this->pg_dir=$pdir;
		$this->pg_name=$pname;
		$this->rel_path=$relpath;
		$this->script_path=$this->full_script_path=Linker::buildSelfURL($pname);
		$path=str_replace('../','',$pdir.$pname);
		$this->site_base=str_replace(array($path,'documents/'),'',$this->script_path);
		$this->ca_url_base=$this->site_base.'documents/centraladmin.php';
		$this->pg_settings=$settings;
		$this->pg_pre=$pid.'_';
		$this->page_type=$ptype;
		$this->data_tablename='posts';
		$this->data_table_idfield='entry_id';
		$this->data_table_publish_field='publish_status';
		$this->id_param='entry_id';
		$this->data_table_cidfield='category';
		$this->data_pre=$this->pg_pre;

		switch($ptype)
		{
			case PODCAST_PAGE:
			case BLOG_PAGE: $this->lang_set='blog_lang_set.txt';
				break;
			case PHOTOBLOG_PAGE: $this->lang_set='photo_lang_set.txt';
				break;
			case GUESTBOOK_PAGE: $this->lang_set='guest_lang_set.txt';
				break;
			case NEWSLETTER_PAGE: $this->lang_set='newsletter_lang_set.txt';
				break;
			case CALENDAR_PAGE:
						$this->lang_set='cal_lang_set.txt';
						$this->data_tablename='events';
						$this->id_param='event_id';
						$this->data_table_idfield='id';
				break;
			case CATALOG_PAGE:
			case SHOP_PAGE :
					$this->lang_set='lister_lang_set.txt';
					$this->data_tablename='data';
					$this->data_table_idfield='pid';
					$this->data_table_cidfield='cid';
					$this->id_param='iid';
					$this->data_pre=$this->pg_settings['g_data'].'_';
					$this->data_table_publish_field='publish';
				break;
			case SURVEY_PAGE:
				$this->lang_set='survey_lang_set.txt';
				break;
		}

		$this->user=isset($user) && $user instanceof User ? $user : new User();
		$this->loadPugins();

		$this->page_is_mobile=Mobile::detect(isset($this->pg_settings['mobile_detect_mode'])?$this->pg_settings['mobile_detect_mode']:'2');
		if($this->page_is_mobile)
			$this->inp_width='width:83%';

		$this->admin_email=isset($this->pg_settings['admin_emails_arr'][0])?$this->pg_settings['admin_emails_arr'][0]:'';
		if($this->admin_email=='your@email.here')
			$this->admin_email='';

	}

	//this function should be moved to admin page class at some point
	public function build_stat_with_icon($statVal,$iconClass,$iconTitle,$statHref='',$w=60)
	{
		$res='<span class="inline_stat" style="display:inline-block;min-width:'.$w.'px;" title="'.$iconTitle.'">';
		if($statHref!='')
			$res.='<a target="_blank" class="rvts12" style="text-decoration:none" href="'.$statHref.'">';
		$res.='<span class="rvts8"><i class="fa '.$iconClass.'"></i> '.$statVal.'</span>';
		if($statHref!='')
			$res.='</a>';
		$res.='</span>';

		return $res;
	}

	public function relToAbs(&$output)
	{
		if($this->use_alt_plinks)
			Linker::relToAbs($output,$this->pg_dir);
	}

	private function loadPugins()
	{
		$location =$this->rel_path.'ezg_data/EZGplugins/'.$this->pg_id.'/';
		if(!is_dir($location))
			return false;
		foreach(scandir($location) as $file)
		{
			if($file!=='.' && $file!=='..' &&preg_match('/^[a-zA-Z_]{3,}\.php$/',$file))
			{
				$pluginName =str_replace('.php','',$file);
				include($location.$file);
				$this->attachEZGPlugin(new $pluginName);
			}
		}
		$this->setState('loadPlugins');
	}

	public function __destruct()
	{
		if(isset($_REQUEST['getmemorypeak']))
		{
			$bytes = memory_get_peak_usage();

			$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
			$factor = floor((strlen($bytes) - 1) / 3);
			$HRSize = sprintf("%.2f", $bytes / pow(1024, $factor)) . @$size[$factor];

			echo '<script>alert("Memory peak: '.$HRSize.'");</script>'.F_LF;
			echo 'Memory peak usage: '.$HRSize;
		}
	}

	public function &__get($prop) //allows protected/private elements to be grabbed as public
	{
		return $this->$prop;
	}

	public function __set($name,$value)
	{
		$this->$name = $value;
	}

	public abstract function process();

	protected abstract function check_data();

	public function lang_l($label)
	{
		$r=(isset($this->pg_settings['lang_l'][$label])?$this->pg_settings['lang_l'][$label]:$label);
		if($r=='-empty-') $r='';
		return $r;
	}

	protected function db_fetch_settings()
	{
		global $db;
		$records=$db->fetch_all_array('SELECT * FROM '.$db->pre.$this->pg_pre.'settings',true);
		if(!empty($records))
		{
			$this->all_settings=array();
			foreach($records as $v)
				$this->all_settings[$v['skey']]=$v['sval'];
		}

		if(!isset($this->all_settings['translit']))
			$this->all_settings['translit']='0';
	}

	public function db_insert_settings($data)
	{
		global $db;

		foreach($data as $k=>$v)
		{
			$rec=$db->query_first('
				SELECT *
				FROM '.$db->pre.$this->pg_pre.'settings
				WHERE skey = "'.$k.'"');
			if(empty($rec) || !$rec)
				$db->query_insert($this->pg_id."_settings",array('skey'=>$k,'sval'=>$v),false,true);
			else
				$db->query_update($this->pg_id."_settings", array('sval'=>$v),'skey = "'.$k.'"');
		}
	}

	protected function set_setting($key,$value)
	{
		$this->all_settings[$key]=$value;
	}

	public function clean_setting($key)
	{
		global $db;
		$db->query('
			DELETE
			FROM '.$db->pre.$this->pg_pre.'settings
			WHERE skey = "'.$key.'"');
		$this->db_fetch_settings();
	}

	public function get_setting($key,$default='')
	{
		$res=$default;
		if(isset($this->all_settings[$key]))
			$res=$this->all_settings[$key];
		return $res;
	}

	protected function is_setting_set($key)
	{
		return isset($this->all_settings[$key]);
	}

	protected function version()
	{
		echo $this->version;
		exit;
	}

	protected function update_language_set($action,$admin_actions,$user_actions)
	{
		$pageid_info=CA::getPageParams($this->pg_id,$this->rel_path);
		$pageLanguage=isset($pageid_info[16])?$this->f->inter_languages_a[array_search($pageid_info[16],$this->f->site_languages_a)]:'EN';
		$this->page_lang=$pageLanguage;
/*
		if(in_array($action,$admin_actions))  // lang from CA cookie
		{
			$t_lang=Cookie::getLangCookie();
			if($t_lang!='')
				$this->page_lang=Formatter::strToUpper($t_lang);
		}

		if(!array_key_exists($this->page_lang,$this->f->names_lang_sets))
			$this->page_lang=$pageLanguage; // if lang set does not exist, use lang set from sitemap.php

		if($this->page_lang!=$pageLanguage)
		{
			$lang_set_results=File::readLangSet($this->rel_path.'ezg_data/'.$this->lang_set,$this->page_lang,$this->pg_settings['page_type']);
			if(isset($lang_set_results['lang_l']))
				$this->pg_settings['lang_l']=$lang_set_results['lang_l'];
		}
		else
*/
		$this->pg_settings['lang_l']=array_merge($this->pg_settings['lang_u'],$this->pg_settings['lang_l']);

		if(in_array($action,$user_actions))
			$this->pg_settings['lang_l']=array_merge($this->pg_settings['lang_l'],$this->pg_settings['lang_u'],$this->pg_settings['lang_uc']);

		foreach($this->pg_settings['lang_uc'] as $k=>$v)
		{
			if(in_array($k,$this->f->day_names))
				$new_day_name[]=trim($v);
			elseif(in_array($k,$this->f->month_names))
				$new_month_name[]=trim($v);
		}

		if(isset($new_day_name))
			$this->day_name=$new_day_name;
		if(isset($new_month_name))
			$this->month_name=$new_month_name;

		Editor::updateLang($this->page_lang,$this->rel_path,$this->innova_js,$this->innova_def);
	}
}

//LivePages are pages with live output (blog,photoblog,catalog,guestbook)
abstract class LivePageClass extends PageClass
{
	protected $img_file_types=array('jpg','jpeg','png','gif');
	protected $is_lister=false;
	protected $is_blogger=false;
	protected $c_page;
	protected $c_page_amp;
	protected $commentModule;
	protected $rankingVisitsModule;
	protected $blockedIpModule;
	protected $errorHandler;
	protected $RelatedPostsHandler;
	protected $SidebarBuilder;
	protected $categoriesModule;

	public function __construct($pid,$pdir,$pname,$relpath,$settings,$ptype)
	{
		parent::__construct($pid,$pdir,$pname,$relpath,$settings,$ptype);
		$this->innova_def='';
		$this->innova_js='';
		$this->is_lister=$this->page_type==CATALOG_PAGE||$this->page_type==SHOP_PAGE;
		$this->is_blogger=$this->page_type==BLOG_PAGE||$this->page_type==PHOTOBLOG_PAGE||$this->page_type==PODCAST_PAGE;
		Editor::getEditor($this->pg_settings['ed_lang'],$this->site_base,$this->pg_settings['rtl'],
				  $this->pg_settings['ed_bg'],$this->innova_def,$this->innova_js,false,
				  $this->pg_settings['tiny_lang'],'',$this->page_is_mobile);
		$this->setCurrPage();
		$this->commentModule=new Comment($this);
		$this->rankingVisitsModule =new RankingAndVisits($this);
		$this->blockedIpModule=new BlockedIps($this);
		$this->errorHandler=new Errors($this->pg_id,
			isset($_REQUEST['action'])?Formatter::stripTags($_REQUEST['action']):'');
		$this->RelatedPostsHandler=new RelatedPosts($this);
		$this->SidebarBuilder=new SidebarBuilderClass($this);
		$this->categoriesModule=new Categories($this);
	}

	public function send_admin_notification($action,$type_label,$post_plink,$data,$send_to_author='')  // send notification
	{
		global $db;

		if($this->admin_email=='')
			return false;

		$subject=str_replace('%TYPE%',$type_label,$this->pg_settings['notification_subject']);
		$is_html=strpos($this->pg_settings['notification_body'],'</')!==false;
		$is_table=strpos($this->pg_settings['notification_body'],'<table')!==false;
		$body=($this->pg_settings['notification_body']=='')?
			'<p>%TYPE% was posted at %URL%</p>
			%FORM_DATA%':$this->pg_settings['notification_body'];
		if(!$is_table)
		  $body='<div style="margin: 100px 15%">'.$body.'</div>';

		$email=isset($data['email'])?$data['email']:'';
		$name=isset($data['visitor'])?$data['visitor']:(isset($data['posted_by'])?$data['posted_by']:$data['blog_name'].' >> '.$data['title']);
		$msg=isset($data['comments'])?$data['comments']:(isset($data['content'])?html_entity_decode($data['content']):'');

		$ip=isset($data['ip'])?$data['ip']:Detector::getIP();
		$ts=isset($data['date'])?strtotime($data['date']):Date::tzone(time());
		$dt=$this->format_date($ts);

		foreach($data as $k=>$v)
		  if($k!='date')
				$body=str_replace('%'.$k.'%',$v,$body);

		$form_data='';
		if(isset($data['title']))
			 $form_data.='<h1>'.$data['title'].'</h1><br>';
		if(isset($data['visitor']))
			 $form_data.='<div><b>'.$this->lang_l('author').':</b> '.$data['visitor'].($this->pg_settings['comments_email_enabled']?' ['.$email.']':'').'</div>';
		if(isset($data['posted_by']))
			 $form_data.='<div><b>'.$this->lang_l('posted by').':</b> '.$data['posted_by'].($this->pg_settings['comments_email_enabled']?' ['.$email.']':'').'</div>';
		$form_data.='<div><b>'.$this->lang_l('date').':</b> '.$dt.'</div>';
		if($msg!='')
			 $form_data.='<div><br><br> '.$msg.'</div>';
		if(isset($data['blog_name']))
			 $form_data.='<div><b>'.$this->lang_l('pinging blog').':</b> '.$data['blog_name'].' '.$data['url'].'</div>';
		if(isset($data['thumbnail_url']) && $data['thumbnail_url']!='')
			 $form_data.='<div><br><img src="'.$data['thumbnail_url'].'"></div>';

		$media='';
		if(isset($data['image_url']) && $data['image_url']!='')
		{
			$media='<img class="embed_image" style="max-width:100%" src="'.$this->get_file_url($data['image_url'],$this->rel_path).'">';
			$form_data.='<div><br>'.$media.'</div>';
		}

		$post_url='<a href="'.$post_plink.'">'.Formatter::GFS($post_plink,'','?').'</a>';
		$body=str_replace(array(
				'%name%','%email%','%comment%','%data%','%TYPE%','%URL%','%PAGEURL%',
				'%FORM_DATA%','%date%','%url%','%ip%','%whois%','%media%'),
				array(
				$name,$email,$msg,$msg,$type_label,$post_url,$post_url,
				$form_data,$dt,(isset($data['url'])?$data['url']:''),$ip,'<a href="http://en.utrace.de/?query='.$ip.'">'.$this->lang_l('view').'</a>',
				$media),
			$body);

		$script_url=$this->full_script_path;
		$app_fl=!isset($data['approved']) || $data['approved']==1;

		$id_a=array(
			 'comments'=>'comment_id='.(isset($data['comment_id'])?$data['comment_id']:'').'&cc=1',
			 'index'=>'entry_id='.$data['entry_id'],
			 'trackbacks'=>'tb_id='.(isset($data['trackback_id'])?$data['trackback_id']:''));

		$body=str_replace(
			array('%approveurl%','%unapproveurl%','%deleteurl%','%spamurl%','%adminurl%','%unapprovedcount%',F_LF),
			array(
				 $app_fl?'':'<a href="'.$script_url.'?action='.$action.'&do=approve&'.$id_a[$action].'">'.$this->lang_l('approve').'</a>',
				 !$app_fl? '':'<a href="'.$script_url.'?action='.$action.'&do=unapprove&'.$id_a[$action].'">'.$this->lang_l('unapprove').'</a>',
				 '<a href="'.$script_url.'?action='.($action=='index'?'del_entry&':$action.'&do=delete&').$id_a[$action].'">'.$this->lang_l('delete').'</a>',
				 ($action=='index'?'':'<a href="'.$script_url.'?action='.$action.'&do=spam&'.$id_a[$action].'">'.$this->lang_l('spam').'</a>'),
				 '<a href="'.$script_url.'?action='.$action.'">'.$this->lang_l('administration panel').'</a>',
				 $this->db_count((isset($data['visitor'])?'comments':'trackbacks'),'approved=0'),
				 ($is_html?'':'<br>')),
			$body);

		$author_data=array();
		if($send_to_author!='')
			$author_data=User::getUser($send_to_author,$this->rel_path,'',intval($send_to_author));
		$result=MailHandler::sendMailStat($db,$this->pg_id,$this->pg_settings['admin_emails_arr'],$this->pg_settings['from_email'],
			$body,$body,$subject,$this->pg_settings['page_encoding'],'','','',$send_to_author,$author_data);
		return $result;
	}

	public function setCurrPage($plink_arr=false)
	{
		if(!isset($_GET['page']) && isset($plink_arr[2]) && is_array($plink_arr) && is_numeric($plink_arr[2]))
		{
			$this->c_page=(int) $plink_arr[2];
			return;
		}

		if(isset($_GET['page']) && intval($_GET['page'])>0 && intval($_GET['page'])<1000000)
		{
			$this->c_page=intval($_GET['page']);
			if($this->c_page<1)
				$this->c_page=1;
			$this->c_page_amp=$this->c_page>1?'&amp;page='.$this->c_page:'';
		}
		else
		{
			$this->c_page=1;
			$this->c_page_amp='';
		}
		if($this->c_page<1)
			$this->c_page=1;
	}
	public abstract function build_permalink_cat($category='',$absurls=false,$force_view=false,$action='');

	public function format_dateSql($time,$mode='long',$new_date_params='')
	{
		if($new_date_params=='auto')
			 $new_date_params=Date::get_date_format($this->page_lang,$mode);

		return $this->format_date(strtotime($time),$mode,$new_date_params);
	}

	public function format_date($timestamp,$mode='long',$new_date_params='')
	{
		if(!empty($new_date_params))
			$params=$new_date_params;
		else
			$params=$mode=='long'?$this->datetime_params:$this->date_params;

		$res=Date::format_date($timestamp,$this->page_lang,$this->month_name,$this->day_name,$mode,$params);
		return $res;
	}
	//views handling
	public function get_view($id)
	{
		foreach($this->pg_settings['views'] as $k=>$v)
			if($id==$v['id'])
				return $k;
		return false;
	}

	function prepareView($view_id=0)
	{
		if($view_id==0 && $this->page_is_mobile)
			$view_id=9999;

		$def_view = false;
		$def_view_key = false;
		$req_view = false;

		if(count($this->pg_settings['views'])>0)
		{
			foreach($this->pg_settings['views'] as $k=>$v)
			{
				$kr=str_replace(' ','_',$k);
				if(isset($_REQUEST[$k]) || isset($_REQUEST[$kr]))
					$req_view = $v;
				elseif($view_id==$v['id'])
				{
					$def_view = $v;
					$def_view_key = $k;
				}
			}

			if($req_view) //view from URL used
			{
				$this->replaceView($req_view);
				return 1;
			}
			if($def_view)
			{
				$this->replaceView($def_view);
				unset($_REQUEST[$def_view_key]);
				return 2;
			}
			if(count($this->pg_settings['views']==1)) //only mobile view defined
				return false;
			return 3; //nothing matched (should not reach this point, if it's here something is wrong)
		}
		else
			return false;
	}

	protected function replaceView($v)
	{
		if($v['id'] == 9999)
		{
			$this->page_is_mobile=true;
			if(strpos($this->pg_settings['template_path'],'i_')===false)
				$this->pg_settings['template_path']=str_replace($this->pg_settings['template_fname'],'i_'.$this->pg_settings['template_fname'],$this->pg_settings['template_path']);
		}
		else
		{
			$this->pg_settings['template_fname']=$v['id'].'.html';
			if($this->page_is_mobile && (file_exists($this->pg_dir.'i_'.$v['id'].'.html')))
				$this->pg_settings['template_fname']='i_'.$v['id'].'.html';
			$this->pg_settings['template_path']=$this->pg_dir.$this->pg_settings['template_fname'];

		}
		$this->pg_settings['offSettings']=$v;
	}

	public function make_view()
	{
		return $this->pg_settings['offSettings']['view']!=''
				  && isset($_REQUEST[$this->pg_settings['offSettings']['view']])?$this->pg_settings['offSettings']['view'].($this->use_alt_plinks?'':'&amp;'):'';
	}

	public function linkedToView()
	{
		 return (isset($this->pg_settings['offSettings']['linked_to_view']) &&
					$this->pg_settings['offSettings']['linked_to_view']!='')?$this->pg_settings['offSettings']['linked_to_view']:'';
	}

	public function build_view_combo($selected,$name='viewid')
	{
		$output = '<select name="'.$name.'">';
		$output .= '<option class="input1" value="0"> - </option>';
		foreach($this->pg_settings['views'] as $k=>$v)
			$output .= '<option '.($v['id'] == $selected ?'selected="selected"':'').' value="'.$v['id'].'">'.$k.'</option>';
		$output.='</select>';

		return $output;
	}

	public function canUseURL($for='post')
	{
		return $for == 'comment' ? (!$this->pg_settings['forbid_urls'] || $this->pg_settings['comments_require_approval']) : !$this->pg_settings['forbid_urls'];
	}

	protected function init_db()
	{
		global $db;
		$mysql_charset=$this->f->uni?'utf8':'';
		$db=DB::dbInit($mysql_charset,$mysql_charset);

		if($db!==false)
			$this->check_data();
		else
			exit;
	}

	public function manage_comments()
	{
		global $db;
		if(isset($_REQUEST['deletefromip']))
			$db->query('
				DELETE
				FROM '.$db->pre.$this->pg_pre.'comments
				WHERE ip = "'.addSlashes($_REQUEST['deletefromip']).'"');

		$total_count=$this->db_count('comments');
		$start=($this->c_page-1)*Navigation::recordsPerPage();
		if($this->is_lister)
		{
			$records=$db->fetch_all_array('
				SELECT ct.* , dt.pid, dt.cid, dt.image1, dt.name,cau.*
				FROM '.$db->pre.$this->pg_pre.'comments AS ct
				LEFT JOIN '.$db->pre.$this->g_datapre.'data AS dt ON ct.entry_id = dt.pid
				LEFT JOIN '.$db->pre.'ca_users AS cau ON cau.uid = ct.uid
				ORDER BY date DESC '
					.(($total_count>Navigation::recordsPerPage() && Navigation::recordsPerPage()!=0)?
							'LIMIT '.$start.', '.Navigation::recordsPerPage().'':''));
		}
		else
		{
			$table_name='posts';
			$field_name='entry_id';
			if($this->page_type==GUESTBOOK_PAGE)
				$flist=',p.content';
			elseif($this->page_type==CALENDAR_PAGE)
			{
				$table_name='events';
				$field_name='id';
				$flist=',p.short_description, p.category';
			}
			elseif($this->page_type==PHOTOBLOG_PAGE)
				$flist=',p.title, p.category, p.permalink, p.image_url, p.thumbnail_url';
			else
				$flist=',p.title, p.category, p.permalink, p.image_url';

			$records=$db->fetch_all_array('
				SELECT ct.*'.$flist.',cau.*
				FROM '.$db->pre.$this->pg_pre.'comments AS ct
				LEFT JOIN '.$db->pre.$this->pg_pre.$table_name.' AS p ON ct.entry_id = p.'.$field_name.'
				LEFT JOIN '.$db->pre.'ca_users AS cau ON cau.uid = ct.uid
				ORDER BY ct.date DESC '
					.(($total_count>Navigation::recordsPerPage()&&Navigation::recordsPerPage()!=0)?
							'LIMIT '.$start.', '.Navigation::recordsPerPage().'':''));
		}

		$output=$this->commentModule->comments_admin($total_count,$records);
		return $output;
	}

	public function get_file_url($image_field,$rel_path=false)
	{
		$result='';
		if(!empty($image_field))
		{
			if((strpos($image_field,'http://')!==false) || (strpos($image_field,'www.')!==false)  ||
					(strpos($image_field,'https://')!==false))
					  $result=$image_field;
			else
			{
				if(strpos($image_field,'php/')==false && strpos($image_field,'/')==false)
					$image_field='php/'.$image_field;
				$result=Formatter::sth($image_field);

				if(substr($result,0,4)!='php/')
					$result=str_replace('../','',$result);
				else
					$result=str_replace('../','',$this->rel_path.$this->pg_settings['page_type'].'/'.$result);
				if($result!='' && $rel_path!==false)
					$result=$rel_path.$result;
			}
		}
		return $result;
	}
}

class rich_snippets
{
	public static function prepare_scope($scope,$sc_tag,$fields,&$src)
	{
		 $sc=Formatter::GFS($src,'<SCOPE_'.$scope.'>','</SCOPE>');
		 if($sc!='')
		 {
			 $sc_parsed=$sc;
			 foreach($fields as $k=>$v)
				 $sc_parsed=str_replace('%'.$k.'%',rich_snippets::wrap_prop($v,'%'.$k.'%'),$sc_parsed);
			 $src=str_replace('<SCOPE_'.$scope.'>'.$sc.'</SCOPE>',$sc_tag.$sc_parsed.'</div>',$src);
		 }
	}

	public static function wrap_prop($prop,$item)
	{
		return '<span'.rich_snippets::get_prop($prop).'>'.$item.'</span>';
	}

	public static function getIsoDate($sqldate)
	{
		 return date('Y-m-d\TH:i:sO',strtotime($sqldate));
	}

	public static function wrap_prop_time($prop,$item,$sqldate)
	{
		return '<time datetime="'.rich_snippets::getIsoDate($sqldate).'"'.rich_snippets::get_prop($prop).'>'.$item.'</time>';
	}
	public static function get_prop($prop,$content='')  //itemprop="startDate" content="2013-10-12T22:00"
	{
		return ' itemprop="'.$prop.'"'.($content!=''?' content="'.$content.'"':'');
	}

	public static function get_schema($schema)
	{
		return ' itemscope itemtype="https://schema.org/'.$schema.'"';
	}

	public static function jsonLD_schema($params,$type)
	{
		$str_params='';
		$counter=0;
		foreach($params as $key=>$value){
			$str_params.='"'.$key.'":"'.$value.'"'.($counter!=(count($params)-1)?", ":'');
			$counter++;
		}
		return '<script type="application/ld+json">
			{
				"@context": "https://schema.org",
				"@type": "'.$type.'",
				'.$str_params.'
			}
			</script>';
	}
}

class structData_jsonld
{
	public static function build_json_ld($arrey_params_json)
	{
		return '<script type="application/ld+json">{"@context":"https://schema.org", '.structData_jsonld::get_object_json($arrey_params_json,'').' }</script>';
	}

	protected static function get_object_json($array,$old_str)
	{
		$str='';
		$count_array=count($array);
		$i=0;
		foreach($array as $key=>$value){
			$str.=structData_jsonld::get_prop_json($key,$value,$old_str).($i!=($count_array-1)?', ':'');
			$i++;
		}
		return $str;
	}

	protected static function get_prop_json($prop,$value,$str='')
	{
		$prop='"'.$prop.'": ';
		if(!is_array($value))
			 $str.=$prop.'"'.$value.'"';
		else{
			$sub_str=structData_jsonld::get_object_json($value,$str);
			$str.=$prop.'{ '.$sub_str.' }';
		}
		return $str;
	}
}

class Meta
{
	public static function keywordsArray($keywords)
	{
		 $k_a=array_map('trim',explode(',',trim($keywords)));
		 foreach($k_a as $k=>$v)
			 if($v=='')
				 unset($k_a[$k]);
		 return $k_a;
	}

	public static function keywords_unique(&$keywords)
	{
		$keywords=self::keywordsArray($keywords);
		$keywords=implode(', ',array_unique($keywords));
	}

	public static function replaceKeywords(&$page,$data,$default_field)
	{
		$key_tag=Formatter::GFSAbi($page,'<meta name="keywords" content="','"');
		if(!empty($key_tag) && isset($data[$default_field]) && !empty($data[$default_field]))
			$page=str_replace($key_tag,'<meta name="keywords" content="'.Formatter::sth2($data[$default_field]).'"',$page);
	}

	public static function replaceTitle(&$page,$data,$default_field,$default_field2='')
	{
		$old_title=Formatter::GFS($page,'<title>','</title>');
		if(strpos($old_title,'%')!==false)
		{
			$new_title=$old_title;
			foreach($data as $k=>$v)
				if(!is_array($v))
					$new_title=str_replace('%'.$k.'%',$v,$new_title);
			if($new_title=='' && isset($data['name']))
				$new_title=$data['name'];
		}
		else
		{
			$new_title=isset($data[$default_field])?$data[$default_field]:'';
			if($new_title=='' && $default_field2!='' && isset($data[$default_field2]))
				$new_title=$data[$default_field2];
			$new_title=$new_title==''?
				$old_title:
				($old_title==''?$new_title:$old_title.' - '.$new_title);
		}

		$page=str_replace(Formatter::GFSAbi($page,'<title>','</title>'),'<title>'.$new_title.'</title>',$page);
		if(strpos($page,'<h1 id="i_title">')!==false)
			$page=str_replace(Formatter::GFSAbi($page,'<h1 id="i_title">','</h1>'),'<h1 id="i_title">'.$new_title.'</h1>',$page);
	}

	public static function replaceDesc(&$page,$data,$default_field,$default_field2='',$max_length_field2=0)
	{
		$old_desc=Formatter::GFSAbi($page,'<meta name="description" content="','>');
		$new_desc=$old_desc;
		$defData=trim(strip_tags($data[$default_field]));
		$desc_field=($defData=='' && $default_field2!='')?$default_field2:$default_field;
		if(strpos($new_desc,'%')===false && isset($data[$desc_field]) && $data[$desc_field]!='')
			$new_desc=str_replace(Formatter::GFSAbi($new_desc,'content="','"'),
														'content="%'.$desc_field.'%"',$new_desc);

		if(is_array($data))
		foreach($data as $k=>$v)
		{
			if(!is_array($v))
			{
				if($k==$default_field2 &&  $max_length_field2>0)
				{
					$val=Formatter::sth2($data['content']);
					$val=preg_replace("'<[/!]*?[^<>]*?>'si"," ",$val);
					if(strlen($val)>$max_length_field2)
						$val=trim(Formatter::splitHtmlContent($val,$max_length_field2));
				}
				else
					$val=Formatter::stripTags($v);

				$val=htmlspecialchars($val,ENT_COMPAT);
				Formatter::hideFromGuests($val);

				$new_desc=str_replace('%'.$k.'%',$val,$new_desc);
			}
		}
		Formatter::replacePollMacro_null($new_desc);
		$new_desc=preg_replace(array("'{%SLIDESHOW_ID\(.*?\)%}'si","'{%HTML5PLAYER_ID\(.*?\)%}'si"),'',$new_desc);
		$page=str_replace($old_desc,$new_desc,$page);
		return $old_desc!=$new_desc?Formatter::GFS($new_desc,'<meta name="description" content="','"'):'';
	}
}

class BlockedIps extends page_objects
{

  public function db_blockedips($db)
	{
		$records=$db->fetch_all_array('SELECT ip FROM '.$db->pre.'blocked_ips');
		$res=array();
		foreach($records as $v)
			$res[]=$v['ip'];
		return $res;
	}

	public function db_is_ip_blocked($db,$ip)
	{
		$ip=$db->escape($ip);
		$records=$db->fetch_all_array('SELECT id FROM '.$db->pre.'blocked_ips WHERE ip= \''.$ip.'\'');
		if(empty($records))
			return false;
		else
			return true;
	}

	public function ip_blocking()
	{
		global $db;
		$ip=$_GET['ip'];
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if($do=='blockip')
		{
			if(!$this->db_is_ip_blocked($db,$ip))
			{
				$db->query("INSERT INTO ".$db->pre."blocked_ips (id, ip) VALUES (NULL, '$ip')");
				$msg=$this->page->lang_l('IP is blocked');
			}
			else
				$msg=$this->page->lang_l('IP already blocked');
		}
		else
		{
			$db->query('DELETE FROM '.$db->pre.'blocked_ips WHERE ip = "'.$ip.'"');
			$msg=$this->page->lang_l('IP is unblocked');
		}
		return '<span class="rvts8">'.$msg.'</span>'.F_BR;
	}
}

class SidebarBuilderClass extends page_objects
{
	public function recentcomments_sidebar($records,$param='')
	{
		$output='';
		if($param=='') $param='
			<div class="recent">
					<span class="rvts8">'.($this->f->uni?'&#9678;':'::').'</span>
					<a class="rvts12" rel="recent" title="%item_url_title%" href="%item_url%">%title%</a>
					<div class="rvts8 date"> %date%</div>
			</div>';

		if(count($records)>0)
		{
			$this->date_params='';
			if(stripos($param,'%date[')!==false)
				$this->date_params=Formatter::GFS(str_replace('%DATE[','%date[',$param),'%date[',']%');

			foreach($records as $v)
			{
				if($v['approved']==1)
				{
					$t_comments=$title_url_val=Formatter::stripTags(str_replace(F_LF,'&nbsp;',$v['comments']));
					$t_comments=$this->shorten_title($t_comments);
					if(strlen($t_comments)>60)
						$t_comments=Formatter::splitHtmlContent($t_comments,60);
					$output.=str_ireplace(
						array('%postedby%','%title%','%date%','%item_url_title%','%item_url%','%date['.$this->page->date_params.']%'),
						array($v['visitor'],$t_comments,$this->page->format_dateSql($v['date'],'short'),$title_url_val,
									$this->page->build_permalink($v,false,'',true),$this->page->format_dateSql($v['date'],'long',$this->date_params))
						,$param);
				}
			}
		}
		return '<div class="blog_recent_comments">'.$output.'</div>';
	}

	public function entries_sidebar($records,$param,$fulllist,$category_id,$id)
	{
		$cur_category='';$parent=-1;
		$cname=false;
		if($category_id!==false)
		{
			$cname=$this->page->categoriesModule->get_category_info($category_id,'name',$parent);
			if(!$this->page->use_alt_plinks && $cname!==false)
				$cur_category='&amp;category='.urlencode($cname);
		}
		$entry_id=isset($_REQUEST['entry_id'])?intval($_REQUEST['entry_id']):0;

		$output='';
		if($param=='')
		{
			$param=$fulllist?
				'<a title="%item_url_title%" href="%item_url%">%title%</a>':
				'<div class="sidebar_item%class%">
					<span class="rvts8">'.($this->f->uni?'&#9733;':'::').'</span>
					<a class="rvts12%class%" title="%item_url_title%" href="%item_url%">%title%</a>
					<div>
						<span class="rvts8 date">%date%</span>
					</div>
				</div>';
		}
		elseif(strpos($param,'sticky')===0)//macro params start with sticky
		{
			$params=explode(',',$param);
			$sticky_limit=isset($params[1])?$params[1]:2;
			$sticky_title=isset($params[2])?$params[2]:$this->page->lang_l('related title');
			$param='
			<div class="entries_related_body_wrap" style="position:relative;padding: 10px 0;color:inherit;">
				<a class="rvts12" title="%title%" href="%item_url%" style="text-decoration: none;color:inherit;">
				<div class="entries_related_media" style="max-width:80px;max-height:80px;margin-right: 10px;float:left;overflow:hidden;">%media%</div>
				<div class="entries_related_body" style="color:inherit;">
					 <span class="entries_related_title" style="font-weight:bold;color:inherit;word-wrap: break-word;">%title%</span>
					 <div class="entries_related_content" style="margin-top:3px;text-decoration:none;word-wrap: break-word;">%content%</div>
				</div>
				<div style="clear:both;"></div>
				</a>
		  </div> limit:"'.$sticky_limit.'" sticky:"'.$sticky_title.'"';
		}
		$new_date_params=$thumb_params='';
		$thumb_width=$thumb_height=80;
		$init_thumbs_used=true;
		if(strpos($param,'%date[')!==false)
			$new_date_params=Formatter::GFS($param,'%date[',']%');
		elseif(strpos($param,'%DATE[')!==false)
			$new_date_params=Formatter::GFS($param,'%DATE[',']%');

		$med_f=(strpos($param,"%media")!==false)?'media':'image';
		if(strpos($param,'%'.$med_f.'[')!==false)
		{
			$thumb_params=Formatter::GFS($param,'%'.$med_f.'[',']%');
			list($thumb_width,$thumb_height)=explode(',',$thumb_params);
			$init_thumbs_used=false;
		}
		$last_category='';
		$cnt=0;
		$bm_present=(strpos($param,'%bookmark%')!==false);
		$sticky=strpos($param,'sticky:"')!==false?Formatter::GFS($param,'sticky:"','"'):false;
		$limit_entries=strpos($param,'limit:')!==false?Formatter::GFS($param,'limit:"','"'):false;
		$limit_entries=(int)$limit_entries>0?(int)$limit_entries:false;

		if($fulllist)
			$output.='<ul style="list-style-type:none">';
		$ct_open=$cts_open=false;

		foreach($records as $v)
		{
			$cnt++;
			$category=isset($v['cname'])?$v['cname']:'';
			$active=$entry_id>0 && $v[($this->page->is_lister?'pid':'entry_id')]==$entry_id;
			if($fulllist)
			{
				if($last_category!=$category)
				{
					$class=isset($v['pid'])&&($v['pid']!=-1)?'blog_subcategory':'blog_category';
					if($ct_open)
						$output.='</li></ul>';
					$output.='<li class="'.$class.'"'.($bm_present?' id="'.preg_replace('/[\s\W]+/','+',$category).'"':'').'>
						<p class="toggler">'.$category.'</p><ul class="blog_pages'.($active?' active':'').'" style="list-style-type:none">';
					$ct_open=true;
				}
				$output.='<li class="blog_entry_'.(Unknown::isOdd($cnt)?'odd':'even').($entry_id>0&&$v['entry_id']==$entry_id?' active':'').'">';
			}

			$image_field=$this->page->is_lister?'image1':'image_url';
			$title_value=$title_url_val=Formatter::sth2($v[$this->page->is_lister?'name':'title']);
			$content=Formatter::stripTags($v[$this->page->is_lister?'html_description':'content']);
			if(strlen($content)>100)
				$content=Formatter::substrUni($content,0,100).'...';
			$bookmark=$bm_present?preg_replace('/[\s\W]+/','+',$title_value):'';
			$title_value=$this->shorten_title($title_value);
			$date_value=$this->page->format_dateSql($v['creation_date'],'short');
			$date_value_l=$this->page->format_dateSql($v['creation_date'],'long',$new_date_params);
			$image_line='';
			if($this->page->is_lister||!isset($v['mediafile_url']))
				$mf='';
			else
			{
				//med_file_url - blog, thumb_url - photoblog
				$media_file = isset($v['mediafile_url'])?$v['mediafile_url']:$v['thumbnail_url'];
				$mf = Formatter::sth($media_file);
			}
			$media_fname=substr($mf,strrpos($mf,"/")+1);
			$listen_url=(strpos($mf,'http')===false?$this->page->rel_path:'').str_replace($media_fname,rawurlencode($media_fname),$mf);
			if($this->page->page_type==BLOG_PAGE &&
					empty($v['image_url']) &&
					Video::youtube_vimeo_check($listen_url)) //check if yt used in yt filed and use it's image as url
				$v['image_url']=Video::getVideoImage(urldecode($listen_url));
			if(!empty($v[$image_field]))
			{
				$im_url=Formatter::sth($this->page->get_file_url($v[$image_field],''));
				$ext=substr($im_url,strrpos($im_url,".")+1);
				$image_mp3_fname=substr($im_url,strrpos($im_url,"/")+1);
				$src=str_replace($image_mp3_fname,rawurlencode($image_mp3_fname),$im_url);
				if(!$this->page->is_lister && strpos($src,'http')===false)
					$src=$this->page->rel_path.$src;
				if(in_array(Formatter::strToLower($ext),array('jpg','jpeg','png','gif','tif','tiff','bmp')))
					$image_line='<img title="'.$title_value.'" src="'.$src.'" style="width:'.$thumb_width.'px;'
						.($init_thumbs_used?'':'height:'.$thumb_height.'px;').($sticky?' border: 1px solid #d4d4d4;':'').'">';
			}
			$parsed_line=str_ireplace(array('%title%','%title_bookmark%'),array($title_value,Translit::simpleTranslit($title_value)),$param);

			if(!$this->page->is_lister)
			{
				foreach(array('author','subtitle','free_field','excerpt') as $index)
					if(!isset($v[$index])) $v[$index]=''; //prepare these for the photoblog
				$parsed_line=str_replace(array('%subtitle%','%author%','%postedby%','%free_field%','%excerpt%'),
				array($v['subtitle'],$v['author'],User::getUserName($v['posted_by'],$this->page->rel_path),$v['free_field'],$v['excerpt']),
				$parsed_line);
			}
			else
			{
				$plink=$this->page->build_permalink($v,false,'',true);
				$parsed_line=str_replace('%SHOP_DETAIL%',$plink,$parsed_line);
				$parsed_line=$this->page->ReplaceFieldsData(false,$v,$parsed_line,true,$v['pid'],true,false);
			}

			$parsed_line=str_replace(array('%category%','%date%','%DATE%','%item_url_title%','%bookmark%','%viewed%','%ranked%','%class%')
				,array($category,$date_value,$date_value,$title_url_val,$bookmark,$v['visits_count'],
				($v['ranking_count']>0? round($v['ranking_total']/$v['ranking_count'],1):0),
				($active?' active':'')),$parsed_line);

			if($fulllist)
			{
				Formatter::hideFromGuests($v['content']);
				$parsed_line=str_replace('%content%',$v['content'],$parsed_line);
				if(strpos($parsed_line,'ToggleBody')>0)
					$parsed_line=Formatter::parseDropdown($parsed_line,$cnt);
			}
			$parsed_line=str_replace(
				array('%date['.$new_date_params.']%','%DATE['.$new_date_params.']%','%'.$med_f.'['.$thumb_params.']%','%'.$med_f.'%','%content%'),
				array($date_value_l,$date_value_l,$image_line,$image_line,$content),
				$parsed_line);

			if($sticky)
				$parsed_line=str_replace(Formatter::GFSAbi($param,'sticky:"','"'),'',$parsed_line);
			if($limit_entries)
				$parsed_line=str_replace(Formatter::GFSAbi($param,'limit:"','"'),'',$parsed_line);

			if($fulllist)
				$cur_category='&amp;category='.urlencode($category);
			$output.=str_replace(array('%item_url%','%permalink%'),
							$this->page->build_permalink($v,false,'',true).$cur_category,$parsed_line);
			$last_category=$category;
			if($fulllist)
				$output.='</li>';
			if($limit_entries && $cnt>=$limit_entries) //limit reached, stop here
				break;
		}

		if($fulllist)
		{
	    if($ct_open) $output.='</ul></li>';
			$output.='</ul>';
		}
		$dl=strpos($param,'<dt')!==false;
		$res='<'.($dl?'dl':'div').' class="entries_sidebar" id="'.$id.'">'.$output.'</'.($dl?'dl':'div').'>';
		if($sticky && count($records))
		{
			$this->page->page_css.='#hdp_'.$id.'{position:fixed !important;right:-400px;bottom:20px;padding:10px;width:300px;z-index:2000;background: white;border-radius: 5px 0 0 5px;box-shadow:0 4px 10px #666666; color:#4e4e4e;}';
			$this->page->page_scripts.='
			$(document).ready(function() {$("#hdp_'.$id.'").detach().appendTo("body").css("display","block");});
			jQuery(function($){
				$(window).scroll(function() {
					var st=$(window).scrollTop(),entryBotReached=(st + $(window).height() < ($("#entry_bot_mark").offset().top)) || st==0 ? 0 : 1;
					if(entryBotReached)
						$("#hdp_'.$id.'").stop().animate({"right":"0"});
					else
						$("#hdp_'.$id.'").stop().animate({"right":"-400px" });
				});
			});
			';
			$res=$this->hidden_div($id,$res,$sticky,'<div id="entry_bot_mark"></div>','x','');
		}
		return $res;
	}

	public function shorten_title($src)
	{
		if(isset($this->page->pg_settings['offSettings']) && $this->page->pg_settings['offSettings']['sidebarLenght']>0 && strlen($src)>$this->page->pg_settings['offSettings']['sidebarLenght'])
			return Formatter::substrUni($src,0,$this->page->pg_settings['offSettings']['sidebarLenght'] - 4).' ...';
		return $src;
	}

	protected function hidden_div($id,$content,$title,$hidden_div_css='',$close_label='X',$out_div_extra_st='',$mid_div_extra_st='',$in_div_extra_st='')
	{
		$output=$hidden_div_css.'
		<div id="hdp_'.$id.'" '.$out_div_extra_st.'>
		  <div id="hdp_'.$id.'_title">
				<span>'.$title.'</span>
				<a id="hdpc_hdp_'.$id.'" style="float:right;text-decoration:none;" href="javascript:hide_Hdiv(\'hdp_'.$id.'\');">'.$close_label.'</a>
		  </div>
		  <div id="hdps_hdp_'.$id.'" '.$mid_div_extra_st.'>
				<div id="hd_hdp_'.$id.'" '.$in_div_extra_st.'>'.$content.'</div>
		  </div>
		</div>';
		return $output;
	}
}

class RelatedPosts extends page_objects
{
	public function get_related_posts($entry_data,$where_public)
	{
		global $db;
		$entries_related=array();
		$id_field=$this->page->is_lister?'pid':'entry_id';
		$date_field=$this->page->is_lister?'dt.updated':'creation_date';
		if($this->page->is_lister) {
			$field_names=$db->db_fieldnames($this->page->pg_pre.'data');
			$is_weight=in_array('weight',$field_names);
			$orderby=$this->page->pg_settings['g_orderby']!=''&&$this->page->pg_settings['g_orderby']!='none'?$this->page->pg_settings['g_orderby']:'';
			$order_type=$this->pg_settings['g_orderbydesc']?'DESC':'ASC';
		}
		else {
			$is_weight=true;
			$orderby=$this->page->order_by;
			$order_type=$this->page->order_type;
		}
		$keywords='';$ids=array();
		if(isset($entry_data['keywords']))
		{
			$keywords.=$entry_data['keywords'].',';
			$ids[]=$entry_data[$id_field];
		}
		elseif(isset($entry_data) && is_array($entry_data))
			foreach($entry_data as $k=>$v)
			{
				$keywords.=$v['keywords'].',';
				$ids[]=$v[$id_field];
			}
		$keywords=str_replace(',,',',',$keywords);
		$tags_array=array_unique(explode(',',Formatter::sth($keywords)));
		if(end($tags_array)=='')
			array_pop($tags_array);

		if(count($tags_array)>0)
		{
			$keywords=$id_que='';
			foreach($tags_array as $k=>$v)
				if(trim($v)!='')
					$keywords.=trim($v).'|';
			$key_que='"'.substr($keywords,0,strlen($keywords)-1).'"';

			foreach($ids as $k=>$v)
				$id_que.=$id_field.' != '.$v.' AND ';
			$id_que=substr($id_que,0,strlen($id_que)-4);

			$que='
				SELECT dt.*,ct.cname,'.$date_field.' AS creation_date
				FROM '.$db->pre.$this->page->data_pre.($this->page->is_lister?'data':'posts').' as dt
				LEFT JOIN '.$db->pre.$this->page->pg_pre.'categories AS ct ON (ct.cid=dt.category)
				WHERE '.$where_public.' AND (keywords REGEXP '. $key_que. ') AND '.$id_que
				.($is_weight?'ORDER BY weight ASC '.($orderby!=''?',':''):($orderby!=''?'ORDER BY ':''))
				.($orderby!=''?$orderby.' '.$order_type:'')
				.' LIMIT 0,20';

			$entries_related=$db->fetch_all_array($que);
		}

		return $this->order_related_posts($entries_related,$entry_data);
	}

	protected function order_related_posts($rel_entries,$curr_entry,$limit=10)
	{
		$cat_field=$this->page->is_lister?'cid':'category';
		$tmp_rel=array();
		$return_arr=array();
		$tot_max=0;
		$c_cat=$curr_entry[$cat_field];
		$c_kwrds=explode(',',$curr_entry['keywords']);
		foreach($rel_entries as $rk=>$re)
		{
			$r_tot=0;
			$re_kwrds=explode(',',$re['keywords']);
			if($re[$cat_field] == $c_cat)
				$r_tot++;
			$r_tot += count(array_intersect($c_kwrds,$re_kwrds));
			$tmp_rel[$r_tot][]=$rk;
			if($r_tot > $tot_max)
				$tot_max=$r_tot;
		}
		if(empty($tmp_rel))
			return $tmp_rel;
		krsort($tmp_rel);
		foreach($tmp_rel as $tv)
			foreach($tv as $t)
			{
				$return_arr[]=$rel_entries[$t];
				$limit--;
				if($limit<1) break 2; //limit reached. No more posts needed
			}
		return $return_arr;
	}
}

class Images
{
	 public static function parse_scale_macros($src)
	 {
		while(strpos($src,'%SCALE(')!==false)
		{
			$scale=Formatter::GFS($src,'%SCALE(',')%');
			$src=str_replace('%SCALE('.$scale.')%','%SCALE['.$scale.']%',$src);
		}
		return $src;
	 }

	 public static function parseScale($data,&$psrc,$fname,$use_rs,$ttasurl,&$val)
	 {
		  $param=Formatter::GFSAbi($psrc,'%SCALE[%'.$fname.'%',']%');
		  $psrc=str_replace('<div class="rvps0">'.$param.'</div>',$param,$psrc);
		  if(strpos($psrc,$param)===false) return;
		  $params_a=explode(',',Formatter::GFS($param,'%SCALE[',']%'));
		  $float=isset($params_a[5]) && $params_a[5]!=''?'float:'.$params_a[5].';margin:0 4px 4px 0;':'';
		  $id=isset($params_a[6])?' id="'.$params_a[6].'"':'';
		  $itemprop=$use_rs && $id==''?item_snippets::get_prop('image'):'';
		  $type=isset($params_a[3])?$params_a[3]:'';
		  $lazy_load=isset($params_a[7])&&$params_a[7];
		  if($val!='')
		  {
				$full=$val;
				$style='';
				$cp=count($params_a);
				$crop=false;
				if($cp>2)
				{
					$h=($params_a[2]=='')?0:intval($params_a[2]);
					$w=($params_a[1]=='')?0:intval($params_a[1]);
					$crop=$h>0&&$w>0;
					$style=($h>0)?'height:'.$h.'px;':($w>0?'width:'.$w.'px;':'');

					if($w>0 && $w<90 && $h<90 && strpos($val,'.png')===false)
					{
						$temp=Formatter::str_lreplace('/','/thumbs/',$val);
						if(file_exists($temp))
							$val=$temp;
					}
				}

				if(isset($data[$fname.'_title']))
					$title=$data[$fname.'_title'];
				else
					$title=(isset($data['name'])?$data['name']:'');
				$alt=(isset($data[$fname.'_alt'])?$data[$fname.'_alt']:'');

				if($type!='')
				{
					$xurl=isset($params_a[4])?$params_a[4]:'javascript:void(0);';

					if($type=='multibox')
					{
						$imgsrc=(isset($params_a[4])&&($params_a[4]!=''))?$params_a[4]:$full;
						$rel=$imgsrc=='%SHOP_DETAIL%'?'noDesc,width:800,height:600':'lightbox,noDesc';
							$val='
								<a href="'.$imgsrc.'" rel="'.$rel.'" title="'.$title.'" class="mbox" style="'.$float.'">
									<img'.$id.' src="'.$val.'" style="'.$style.'" alt="'.$alt.'"'.$itemprop.'>
								</a>';
					}
					elseif($type=='url')
					{
						$rel='';$class='';
						if(strpos($xurl,'#')!==false)
						{
								 $xurl=$full.'" onmouseover="$(\''.$xurl.'\').attr(\'src\',\''.$full.'\');';
								 $class=' class="mbox no_u"';
								 $rel=' rel="lightbox,noDesc"';
							}
						$val='
						<a'.$rel.$class.' href="'.$xurl.'" title="'.$title.'"><img'.$id.' class="img_'.$fname.($lazy_load?' imgLoader_lazyload':'').'" '.($lazy_load?'data-':'').'src="'.$val.'" style="'.$style.$float.'" alt="'.$alt.'"'.$itemprop.'></a>';

					}
					elseif($type=='xzoom')
						$val='<a href="'.$xurl.'" title="'.$title.'">
								<img'.$id.' class="xzoom" src="'.$full.'" style="'.$style.$float.'" alt="'.$alt.'"'.$itemprop.'>
								</a>';
					else //tooltip
					{
						$url=($ttasurl)?'%SHOP_DETAIL%':'javascript:void(0);';
						$val=Builder::tooltip($url,'','','',$full,'<img'.$id.' class="img_'.$fname.'" src="'.$val.'" style="'.$style.'" alt="'.$alt.'"'.$itemprop.'>'); //tooltip
					}
				}
				else
					$val='<img'.$id.' class="img_'.$fname.'" src="'.$val.'" style="'.$style.$float.'" title="'.$title.'" alt="'.$alt.'"'.$itemprop.'>';

				if($crop)
					 $val='<div class="thumb_mask" style="display:inline-block;margin-right:2px;width:'.$w.'px;height:'.$h.'px;overflow:hidden;'.$float.'">'.$val.'</div>';
			}
			$psrc=str_replace($param,$val,$psrc);
	}
}

class tags_cloud extends page_objects
{
	public function parse_tagcloud(&$output,$archive=0)
	{
		while(strpos($output,'%TAGS_CLOUD')!==false)
		{
			$macro=Formatter::GFSAbi($output,'%TAGS_CLOUD','%');
			$tc=$this->get_tagcloud($macro);
			if($archive)
				$tc=str_replace($this->page->use_alt_plinks?'/tag/':'?tag=',$this->page->use_alt_plinks?'/archive/tag/':'?action=archive&tag=',$tc);

			$output=str_replace($macro,'<div class="tags_cloud">'.$tc.'</div>',$output);
		}
	}

	public function clean_tagcloud()
	{
		$this->page->db_insert_settings(array('tags_cloud'=>''));
		$this->page->db_insert_settings(array('tags_cloud_alpha'=>''));
	}

	public function get_tagcloud($macro)
	{
		$sc=$this->get_param($macro);
		$tc=isset($this->page->all_settings[$sc])?$this->page->all_settings[$sc]:'';
		if($tc==='')
		{
			$this->rebuild_tagcloud($macro);
			$tc=$this->page->all_settings[$sc];
		}
		return $tc;
	}

	private function get_param($macro)
	{
		$alpha=strpos($macro,'alpha')!==false;
		return $alpha?'tags_cloud_alpha':'tags_cloud';
	}

	public function get_tags()
	{
		global $db;

		$where=' (keywords IS NOT NULL) AND (keywords != "") '.($this->page->where_public_user==''?'':' AND ').$this->page->where_public_user;
		$tags=$db->fetch_all_array('
					 SELECT keywords
					 FROM '.$db->pre.$this->page->data_pre.$this->page->data_tablename.'
					 WHERE '.$where);
		return $tags;
	}
	//tags related
	protected function rebuild_tagcloud($macro)
	{
		$sc=$this->get_param($macro);
		$do_build=$this->page->all_settings[$sc]=='';
		if($do_build)
		{
			$min_occs = -1;
			$max = 5000;
			$alpha_cols=$max_font_size=0;
			if(strpos($macro,'%TAGS_CLOUD(')!==false)
			{
				$tag_opts=explode(',',Formatter::GFS($macro,'%TAGS_CLOUD(',')%'));
				if($tag_opts[0]=='alpha')
					$alpha_cols=intval($tag_opts[1]);
				else
				{
					$max=isset($tag_opts[0])?intval($tag_opts[0]):5000;
					$min_occs=isset($tag_opts[1])?intval($tag_opts[1]):1;
					$max_font_size=isset($tag_opts[2])?intval($tag_opts[2]):0;
				}
			}

			$url_for_tc=($this->page->use_alt_plinks? str_replace('/'.$this->page->pg_name,'',$this->page->script_path): $this->page->pg_name.'?');
			$data=Builder::buildTagCloud(
					  $url_for_tc,
					  $this->get_tags(),
					  $max,
					  '',
					  false,
					  $this->page->use_alt_plinks,
					  $min_occs,
					  $alpha_cols,
					  $max_font_size);
			if($data=='')
				$data='.';
			$this->page->db_insert_settings(array($sc=>$data));
			$this->page->all_settings[$sc]=$data;
		}
	}

	public function update_tags()
	{
		global $db;

		$field=addslashes(Formatter::GFS($_REQUEST['id'],'','_'));
		if($this->page->page_type==PHOTOBLOG_PAGE && $field=='id')
		{
			$this->page->db_insert_settings(array($_REQUEST['id']=>$_REQUEST['data']));
			echo '1';
			exit;
		}
		$entry_id=Formatter::intVal($_REQUEST['id']);
		$db->query('
			UPDATE '.$db->pre.$this->page->pg_pre.'posts
			SET `'.$field.'` = "'.addslashes($_REQUEST['data']).'"
			WHERE entry_id = '.$entry_id);

		if($field=='keywords')
		{
			$this->clean_tagcloud();
			echo $this->page->get_keywords_html($_REQUEST['data']);
		}
		else
		{
			$this->page->reindex_search($entry_id);
			echo '1';
		}
	}
}

class audio_video extends page_objects
{
	public function parse_html5_audiovideo(&$src,$media_ext,$media_path)
	{
		$tag=Formatter::GFSAbi($src,'%html5player','%');
		if($tag!='%html5player%')
		{
			$params=Formatter::GFS($tag,'%html5player[',']%');
			$audio=$media_ext=='.mp3';
			$h=$audio?30:480;
			$w=$audio?250:640;
			if($params!='')
				list($w,$h)=explode(',',$params);

			if($audio)
				$src=str_replace($tag,'<audio preload="metadata" controls style="width:'.$w.'px">
									 <source src="'.$media_path.'" type="audio/mpeg">
									 Your browser does not support the audio tag.
									</audio>',$src);
			elseif($media_ext=='.mp4')
				$src=str_replace($tag, '<video preload="metadata" width="'.$w.'" height="'.$h.'" controls>
									 <source src="'.$media_path.'" type="video/mp4">
									 Your browser does not support the video tag.
									</video>',$src);
			else
				$src=str_replace($tag,'',$src);
		}
	}

	public function handle_youtube_vimeo_player(&$parsed_line,$yt,$media_value,$v,&$image_line,$blog_yt=false)
	{
		$flash_string=Formatter::GFSAbi($parsed_line,'<script type="text/javascript">','</script>');
		$flash_string2=Formatter::GFSAbi($parsed_line,'<div id="%ID%">','</div>');
		$html5_string=Formatter::GFSAbi($parsed_line,'<div class="html5_button">','</div>');
		$html5macro=Formatter::GFSAbi($parsed_line,'%html5player','%');

		$h=0;$w=0;
		if($html5macro!='%html5player%')
		{
			$params=Formatter::GFS($html5macro,'%html5player[',']%');
			if($params!='')
				list($w,$h)=explode(',',$params);
		}

		if($yt)
		{
			if(strpos($media_value,'vimeo.com'))
					$youtube_vimeo_player=$this->get_vimeo($media_value,$w,$h);
			else
				$youtube_vimeo_player=$this->get_youtube($media_value,$w,$h);

			if($blog_yt)
				$yt_string='%video%';
			else
				$yt_string=$html5_string!='<div class="html5_button"></div>'?$html5_string:$flash_string;

			if(($this->page->page_type==PODCAST_PAGE) || $blog_yt)
				$parsed_line=str_replace(array($yt_string,$html5macro),$youtube_vimeo_player,$parsed_line);
			else
				$image_line=$youtube_vimeo_player;

			$parsed_line=str_replace($flash_string2,'',$parsed_line);
		}
		elseif(isset($media_value) && strpos($media_value,'soundcloud')!==false)
		{
			$yt_string=$blog_yt?'%video%':$flash_string2;
			$parsed_line=str_replace(array($html5macro,$yt_string),$this->get_soundcloud($media_value,$v['entry_id']),$parsed_line);
		}
		else
			$parsed_line=str_replace(array($flash_string,$flash_string2),'',$parsed_line);

		$parsed_line=str_replace(Formatter::GFSAbi($parsed_line,'<noscript>','</noscript>'), '', $parsed_line);
	}

	protected function get_soundcloud($media,$id)
	{
		return '<div id=\'sc_'.$id.'\'></div><script type=\'text/javascript\'>$.getJSON(\'http://soundcloud.com/oembed?url='.urlEncode($media).'&format=js&callback=?\',function(d) {$(\'#sc_'.$id.'\').html(d.html);});</script>';
	}

	public function get_player($media_folder,$media_filename,$media_ext_lower,$id)
	{
		$f_def_playermp3_js='
		<script type="text/javascript">
		  swfobject.embedSWF("'.$this->f->ca_rel_path.'extdocs/singlemp3.swf","%ID%","320","20","9.0.0",false,false,{bgcolor:"#FFFFFF",wmode:"opaque",allowScriptAccess:"sameDomain",flashvars:"audurl=%listenurl3%"});
		</script>
		<div id="%ID%"></div>';

		$f_def_player_js='
		<script type="text/javascript">
		  swfobject.embedSWF("'.$this->f->ca_rel_path.'extdocs/single.swf","%ID%","320","260","9.0.0",false,false,{bgcolor:"#FFFFFF",wmode:"opaque",allowScriptAccess:"sameDomain",flashvars:"audurl=%listenurl3%"});
		</script>
		<div id="%ID%"></div>';
		$this->page->page_dependencies[]=$this->page->rel_path.'swfobject';
		$fullplayer=Formatter::strToLower($media_ext_lower)!='mp3';
		$player_js=(!$fullplayer?$f_def_playermp3_js:$f_def_player_js);
		$player_js=str_replace(array('%listenurl3%','%ID%'),
				array(($fullplayer&&$this->page->rel_path==''?'../':'').$this->page->rel_path.$media_folder.$media_filename,'player_'.$id),
				$player_js);
		return $player_js;
	}

	public function get_youtube($media,$w=0,$h=0)
	{
		$url_you_embed=str_replace(array('http://','https://'),'',$media);
		$short_yt=strpos($url_you_embed,'youtu.be/')!==false;
		$url_id='';
		if(strpos($url_you_embed,'v=')!==false)
			$url_id=substr($url_you_embed, strpos($url_you_embed,'v=')+2);
		elseif(strpos($url_you_embed,'/v/')!==false)
			$url_id=substr($url_you_embed, strpos($url_you_embed,'/v/')+3);
		elseif($short_yt)
		{
			$url_id=str_replace('youtu.be/','',$url_you_embed);
			$url_you_embed='www.youtube.com/';
		}
		if(strpos($url_id,'&')!==false)
			$url_id=substr($url_id, 0, strpos($url_id,'&'));
		$url_you_embed_final='https://'.substr($url_you_embed, 0, strpos($url_you_embed,'/')) .'/embed/'.$url_id.'?wmode=opaque';
		if($h>0)
			$yt_size=array($w,$h);
		else
			$yt_size=explode('x',$this->page->all_settings['youtube_size']);

		return '<iframe class="yt_auto" type="text/html" style="max-width:100%;border:none;'.($this->page->page_is_mobile?'':'width:'.$yt_size[0].'px;height:'.$yt_size[1].'px').'" src="'.$url_you_embed_final.'"></iframe>';
	}

	protected function get_vimeo($media,$w=0,$h=0)
	{
		$url=parse_url($media);
		if($url['host'] !== 'vimeo.com'&&$url['host'] !== 'www.vimeo.com')
			return false;
		$id=(int) substr($url['path'], 1);
		if($h>0)
			$yt_size=array($w,$h);
		else
			$yt_size=explode('x',$this->page->all_settings['youtube_size']);

		return '<iframe class="yt_auto" style="border:none;'.($this->page->page_is_mobile?'':'width:'.$yt_size[0].'px;height:'.$yt_size[1].'px').'" src="http://player.vimeo.com/video/'.$id.'" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
	}
}

class RankingAndVisits extends page_objects
{
	public function handle_intern_ranking()
	{
		 $v=intval($_GET['value']);
		 print $v.'<input type="hidden" name="ranking_count" value="1"><input type="hidden" name="ranking_total" value="'.$v.'">';
		 exit;
	}

	public function save_ranking_visits($entry_id,$intern=0,$doVisits=1)
	{
		global $db;

		if($intern)
			$this->handle_intern_ranking();
		$host=Detector::getRemoteHost();
		if(strpos($host,'googlebot.com')===false)
		{
			$do_ranking=$this->page->ranking_enabled && $this->page->action_id=='ranking';
			if($do_ranking)
			{
				$rating_value=intval($_GET['value']);
				if($rating_value>0 && $rating_value<=MAX_RANKING)
				{
					$db->query('
						UPDATE '.$db->pre.($this->page->is_lister?$this->page->g_datapre:$this->page->pg_pre).$this->page->data_tablename.'
						SET ranking_count = ranking_count + 1, ranking_total = ranking_total + '.$rating_value.'
						WHERE '.$this->page->data_table_idfield.' = '.$entry_id);
					Cookie::setEntryCookie($entry_id,$this->page->pg_id,'ranking_');
				}
				$entry_upd=$this->page->db_fetch_entry($entry_id);
				if($entry_upd!==false)
				{
					$cur_rank_val=$this->page->f->ranking_average?($entry_upd['ranking_count']>0? round($entry_upd['ranking_total']/$entry_upd['ranking_count'],1): 0):$entry_upd['ranking_total'];
					print $cur_rank_val;
				}
				exit;
			}

			$do_movisited=$doVisits && !isset($_COOKIE['visit_from_admin']) && !Cookie::entryIsCookie($entry_id,$this->page->pg_id,'visited_');
			if($do_movisited)
			{
				Cookie::setEntryCookie($entry_id,$this->page->pg_id,'visited_');
				$db->query('
					UPDATE '.$db->pre.($this->page->is_lister?$this->page->g_datapre:$this->page->pg_pre).$this->page->data_tablename.'
					SET visits_count = visits_count + 1
					WHERE '.$this->page->data_table_idfield.' = '.$entry_id);
			}
		}
	}

	public function get_rankingScript()
	{
		return ($this->page->use_alt_plinks)? str_replace("ranking({","ranking({url:'".$this->page->script_path."',",$this->page->f->ranking_script):$this->page->f->ranking_script;
	}

	public function build_ranking($data,$state=-1)
	{
		if(!isset($data['ranking_count']))
			return '';
		$cur_rank_val=$this->page->f->ranking_average?($data['ranking_count']>0? round($data['ranking_total']/$data['ranking_count'],1): 0):$data['ranking_total'];
		return Builder::buildRanking($data,$cur_rank_val,$data[$this->page->data_table_idfield],$this->page->pg_id,$state);
	}
}

class Categories extends page_objects
{
	public $category_array=array();
	public $current_category='';

	public function build_categories_list($recheck_custom=false,$linked_id=0,$update_inmenu=0)
	{
		global $db;

		$this->category_array=array();
		$que='SELECT '.$this->page->data_table_cidfield.' AS xcid, COUNT(*) as count
			FROM '.$db->pre.$this->page->data_pre.$this->page->data_tablename.'
			WHERE '.$this->page->where_public.'
			GROUP BY xcid';

		$cat_counts=$db->fetch_all_array($que,false,'xcid');

		$que='SELECT *
			 FROM '.$db->pre.$this->page->data_pre.'categories
			 ORDER BY IF(parentid>-1,parentid*10+1,cid*10)';
		$cat_all_raw=$db->fetch_all_array($que);
		foreach($cat_all_raw as $k=>$v)
			$cat_all_raw[$k]['count']=isset($cat_counts[$v['cid']])?$cat_counts[$v['cid']]['count']:0;

		if($linked_id>0)
		{
			$raw=$db->fetch_all_array('
				SELECT *
				FROM '.$db->pre.$linked_id.'_categories
				ORDER BY cid');
			$cat_all_raw_linked=array();
			foreach($raw as $k=>$v)
				$cat_all_raw_linked[$v['cid']]=$v;
		}

		foreach($cat_all_raw as $k=>$v)
		{

			if($this->page->page_type==CALENDAR_PAGE)
				$v['viewid']=0;
			$view_id=$v['viewid']!='0'?$v['viewid']:$this->page->all_settings['catviewid'];
			$view=$this->page->get_view($view_id);
			if($view===false)
				$view='';

			$this->category_array[$v['cid']]=array(
				'id'=>$v['cid'],
				'pid'=>$v['parentid'],
				'kids'=>array(),
				'name'=>$v['cname'],
				'color'=>$v['ccolor'],
				'description'=>(isset($v['description'])?$v['description']:''),
				'description2'=>(isset($v['description2'])?$v['description2']:''),
				'image1'=>(isset($v['image1'])&&$v['image1']!=''?$v['image1']:''),
				'count'=>intval($v['count']),
				'viewid'=>$v['viewid'],
				'view'=>$view,
				'page_title'=>(isset($v['page_title'])?$v['page_title']:''),
				'ct_permalink'=>(isset($v['ct_permalink'])?$v['ct_permalink']:''),
				);

			if($linked_id>0 && isset($cat_all_raw_linked[$v['cid']]))
			{
				$z=$cat_all_raw_linked[$v['cid']];
				$this->category_array[$v['cid']]['linked']=
					array(
					'id'=>$z['cid'],
					'pid'=>$this->category_array[$v['cid']]['pid'],
					'kids'=>array(),
					'name'=>$z['cname'],
					'color'=>$z['ccolor'],
					'description'=>(isset($z['description'])?$z['description']:''),
					'description2'=>(isset($z['description2'])?$z['description2']:''),
					'image1'=>(isset($z['image1'])&&$z['image1']!=''?$z['image1']:''),
					'count'=>intval($this->category_array[$v['cid']]['count']),
					'viewid'=>$v['viewid'],
					'view'=>$view,
					'page_title'=>$z['page_title'],
					'ct_permalink'=>(isset($z['ct_permalink'])?$z['ct_permalink']:'')
					);
			}

			if($this->page->page_type==CALENDAR_PAGE)
			{
				$this->category_array[$v['cid']]['mark']=$v['cmark'];
				$this->category_array[$v['cid']]['visible']=$v['visible'];
				$this->category_array[$v['cid']]['markcolor']=$v['cmark_color'];
				$this->category_array[$v['cid']]['restricted']=$v['restricted'];
				$this->category_array[$v['cid']]['category_type']=$v['category_type'];
			}

			if($this->page->is_lister)
				$this->category_array[$v['cid']]['disable_add_items']=$v['disable_add_items'];
		}

		$custom_order=isset($this->page->all_settings['sort_category']) && (strpos($this->page->all_settings['sort_category'],'|')!==false);
		if($linked_id>0 && !$custom_order) // if this page categories sort is not been set, try to get sort of linked page
		{
			$sort_linked=$db->query_first('
				SELECT sval
				FROM '.$db->pre.$this->page->data_pre.'settings
				WHERE skey="sort_category"');
			if($sort_linked!==false && (strpos($sort_linked['sval'],'|')!==false)) { // if linked page has sorted categories
				$this->page->db_insert_settings(array('sort_category'=>$sort_linked['sval'])); // set this page categories sort to linked sort
				$this->page->db_fetch_settings();
				$custom_order=true;
			}
		}
		if(!$custom_order)
		{
			$temp=array();
			foreach($this->category_array as $k=> $v)
			{
				if($v['pid']==-1)
					$last=$v['name'];
				if($v['pid']>-1)
				{
					$par=$this->category_array[$v['pid']];
					if($par['pid']==-1)
						$temp[$v['id']]=$last.'#'.$v['name'];
					else
						$temp[$v['id']]=$this->category_array[$par['pid']]['name'].'#'.$par['name'].'#'.$v['name'];
				}
				else
					$temp[$v['id']]=$v['name'];
			}

			natcasesort($temp);
			$s='';
			foreach($temp as $k=>$v)
				$s.=$k.'|';
			$this->page->all_settings['sort_category']=$s;
		}

		$sort=$this->page->all_settings['sort_category'];
		if($sort!='')
		{
			$sort_a=explode('|',Formatter::GFS($sort,'','-'));
			if($sort_a[count($sort_a)-1]=='')
				array_pop($sort_a);

			if($linked_id>0 && (count($cat_all_raw) != count($sort_a)))
				$recheck_custom=true;
			if($custom_order && $recheck_custom)
			{
				foreach($sort_a as $k=>$v) //remove non-existing
				{
					if(array_key_exists($v,$this->category_array)===false)
					{
						unset($sort_a[$k]);
						$this->page->db_insert_settings(array('sort_category'=>implode('|',$sort_a).'-'));
						break;
					}
				}

				foreach($this->category_array as $k=>$v)  //missing in list?
				{
					if(array_search($k,$sort_a)===false)
					{
						if($v['pid']>-1)
						{
							$temp=array();
							foreach($sort_a as $kx=>$vx)
							{
								$temp[]=$vx;
								if($vx==$v['pid'])
									$temp[]=$k;
							}
							$sort_a=$temp;
						}
						else
							$sort_a[]=strval($k);
						$this->page->db_insert_settings(array('sort_category'=>implode('|',$sort_a).'-'));
						break;
					}
				}

				$last_parent=-1;  //wrong parent?
				foreach($sort_a	as $k=>$v)
				{
					$parent=$this->category_array[$v]['pid'];
					if($parent==-1)
						$last_parent=$v;
					elseif($last_parent!=$parent)
					{
						$temp=array();
						foreach($sort_a as $kx=>$vx)
						{
							if($vx!=$v)
								$temp[]=$vx;
							if($vx==$parent)
								$temp[]=$v;
						}
						$sort_a=$temp;
						$this->page->db_insert_settings(array('sort_category'=>implode('|',$sort_a).'-'));
						break;
					}
				}
			}
			$temp=array();
			foreach($sort_a as $k=>$v)
				if(isset($this->category_array[$v]))
					$temp[$v]=$this->category_array[$v];
			$this->category_array=$temp;
		}

		foreach($this->category_array as $k=>$v)
		{
			$kids=array();
			foreach($this->category_array as $vv)
				if($vv['pid']==$k)
					 $kids[]=array('cid'=>$vv['id'],'count'=>intval($vv['count']));

			$this->category_array[$k]['kids']=$kids;
			if(isset($this->category_array[$k]['linked']))
				$this->category_array[$k]['linked']['kids']=$kids;
		}

		if($update_inmenu)
			$this->update_settings_categ_inmenu();
	}

	public function ext_category_list()
	{
		$result=$this->category_sidebar(true,isset($_REQUEST['category_vlist'])?'ver':'hor','',true);
		print "document.write('".str_replace(array("\r\n","\n"),'',$result)."');";
		exit;
	}

	public function getCategoryPageSet($limit,&$total_count)
	{
		$catsToShow=$this->category_array;

		$q=isset($_REQUEST['q'])?addslashes($_REQUEST['q']):'';
		if($q<>'')
			 foreach($catsToShow as $k=>$v) if(stripos($v['name'],$q)===false) unset($catsToShow[$k]);
		$total_count=count($catsToShow);
		if(count($catsToShow)<=$limit)
			return $catsToShow;
		$cntr = 0;
		$res = array();


		foreach($catsToShow as $key => $cat)
		{
			$cntr++;
			if ($cntr <= ($this->page->c_page-1)*$limit || $cntr > $this->page->c_page*$limit) continue;
			$res[$key]=$cat;
		}

		return $res;
	}
	public function get_categoryidbyname($name)
	{
		$name=strtolower($name);
		foreach($this->category_array as $v)
			if(strtolower($v['name'])==$name)
				 return $v['id'];
		return false;
	}
	public function get_categoryData($cid)
	{
		return $this->category_array[$cid];
	}
	public function get_categoriesCount()
	{
		return count($this->category_array);
	}
	public function get_categoryView($cid,&$view_id)
	{
		if($cid!==false&&isset($this->category_array[$cid])&&$this->category_array[$cid]['viewid']!="0")
			$view_id=$this->category_array[$cid]['viewid'];
	}
	public function get_postsCount()
	{
		$cnt=0;
		foreach($this->category_array as $v)
			$cnt+=$v['count'];
		return $cnt;
	}
	public function get_category_breadcrumb($id,$asurl,&$cdata)
	{
		if($id==null) return '';
		$result='';
		$cdata=$this->category_array[$id];
		$cname=$cdata['name'];
		$ccolor=$cdata['color'];
		$parent_id=$cdata['pid'];

		while($parent_id>-1)
		{
			$parent_color=$this->category_array[$parent_id]['color'];
			$parent_name=$this->category_array[$parent_id]['name'];
			$pcount=$this->category_array[$parent_id]['count'];
			if($asurl && $pcount>0)
			{
				$parent_url=$this->page->build_permalink_cat($parent_name,false,$this->category_array[$parent_id]['view']);
				$result='<a class="rvts12" style="color:'.$parent_color.'" href="'.$parent_url.'">'.Formatter::sth($parent_name).'</a> &mdash; '.$result;
			}
			else
				$result='<span style="font-weight:normal;color:'.$parent_color.'">'.Formatter::sth($parent_name).'</span> &mdash; '.$result;

			$parent_id=$this->category_array[$parent_id]['pid'];
		}

		if($asurl)
		{
			$url=$this->page->build_permalink_cat($cname,false,$this->category_array[$id]['view']);
			$result.='<a class="rvts12" style="color:'.$ccolor.'" href="'.$url.'">'.Formatter::sth($cname).'</a>';
		}
		else
			$result.='<span style="font-weight:normal;color:'.$ccolor.'">'.Formatter::sth($cname).'</span>';
		return $result;
	}

	public function get_category_plinkname($category)
	{
		foreach($this->category_array as $v)
		{
			$cv=isset($v['linked'])?$v['linked']:$v;
			if($cv['name']==$category && isset($cv['ct_permalink']) && $cv['ct_permalink']!='')
				 return $cv['ct_permalink'];
		}
		return $category;
	}

	public function get_category_from_plinkname($ct_permalink,&$cid)
	{
		foreach($this->category_array as $v)
		{
			$cv=isset($v['linked'])?$v['linked']:$v;
	   	if(isset($cv['ct_permalink']) && $cv['ct_permalink']!='' && $cv['ct_permalink']==$ct_permalink)
			{
				 $cid=$cv['id'];
				 return $cv['name'];
			}
		}
		foreach($this->category_array as $v)
		{
			$cv=isset($v['linked'])?$v['linked']:$v;
			if($cv['name']==$ct_permalink)
			{
				 $cid=$cv['id'];
				 return $cv['name'];
			}
		}
		if($ct_permalink=='all')
			return 'all';
		return '';
	}

	public function get_category_info($id,$field,&$parent)
	{
		foreach($this->category_array as $v)
		{
			if($v['id']==$id)
			{
				$parent=$v['pid'];
				return isset($v['linked'])?$v['linked'][$field]:$v[$field];
			}
		}
		return false;
	}

	public function get_categoryid()
	{
		$category_id=false;
		if(isset($_GET['subcat'])&&(isset($_GET['category'])))
		{
			$subcat=stripslashes(Formatter::unEsc($_GET['subcat']));
			$subcat_p=str_replace('+',' ',$subcat);
			$category=stripslashes(Formatter::unEsc($_GET['category']));
			$category_p=str_replace('+',' ',$category);

			foreach($this->category_array as $k=>$v)
			{
				if($v['pid']>-1)
				{
					$cv=isset($v['linked'])?$v['linked']:$v;
					$cpv=isset($this->category_array[$v['pid']]['linked'])?$this->category_array[$v['pid']]['linked']:$this->category_array[$v['pid']];
					if(($cv['name']==$subcat || $cv['name']==$subcat_p) && ($cpv['name']==$category || $cpv['name']==$category_p))
					{
						 $this->current_category=$cpv['name'];
						 return $v['id'];
					}
				}
			}
		}
		elseif(isset($_GET['category']))
		{
			$category=stripslashes(Formatter::unEsc($_GET['category']));
			$category_p=str_replace('+',' ',$category);
			//first run, ignore subs
			foreach($this->category_array as $k=> $v)
			{
				$cv=isset($v['linked'])?$v['linked']:$v;
				if($cv['pid']==-1&&($cv['name']==$category||$cv['name']==$category_p))
				{
					$this->current_category=$cv['name'];
					return $v['id'];
				}
			}

			foreach($this->category_array as $k=>$v)
			{
				$cv=isset($v['linked'])?$v['linked']:$v;
				if($cv['name']==$category || $cv['name']==$category_p)
				{
					 $this->current_category=$cv['name'];
					 return $v['id'];
				}
			}
		}
		elseif(isset($_GET['category_id']))
		{
			$cid=intval($_GET['category_id']);
			foreach($this->category_array as $k=>$v) if($v['id']==$cid)
			{
				$this->current_category=$v['name'];
				return $v['id'];
			}
		}
		return $category_id;
	}

	public function get_category_name($id,&$parent) //accepts both category id and category name
	{
		if(isset($this->category_array[$id]))
		{
			$v=$this->category_array[$id];
			$parent=$v['pid'];
			return isset($v['linked'])?$v['linked']['name']:$v['name'];
		}

		foreach($this->category_array as $v)
		  if($v['name']==$id)
		  {
				$parent=$v['pid'];
				return isset($v['linked'])?$v['linked']['name']:$v['name'];
		  }
		return '';
	}

	public function is_category_disabled($id)
	{
		if(isset($this->category_array[$id]))
		{
			$v=$this->category_array[$id];
			return isset($v['disable_add_items'])&&$v['disable_add_items'];
		}

		foreach($this->category_array as $v)
			if($v['name']==$id)
				return isset($v['disable_add_items'])&&$v['disable_add_items'];
		return false;
	}

	public function get_category_colorbar($cid,$action,$url_suffix,&$ct_color)
	{
		$sname='';
		$parent=-1;
		$cname=$this->get_category_name($cid,$parent);
		if($parent>-1)
		{
			$sname=$cname;$par=-1;
			$cname=$this->get_category_name($parent,$par);
		}
		$ct_color=$this->get_category_info($cid,'color',$parent);

		$rel=' rel="cc:'.$ct_color.'"';
		$category='<p><a class="rvts8" style="text-decoration:none"'.$rel.' href="javascript:void(0);" onclick="document.location=\''.$this->page->script_path.'?action='.$action.'&amp;category='.urlencode($cname).$url_suffix.'\'" title="' .urlencode($cname).'">'.$cname.' </a></p>';
		if($sname!='')
			$category.='<p>-- <a class="rvts8" style="text-decoration:none"'.$rel.' href="javascript:void(0);" onclick="document.location=\''.$this->page->script_path.'?action='.$action.'&amp;category='.urlencode($sname).$url_suffix.'\'" title="' .urlencode($sname).'">'.$sname.' </a></p>';
		return $category;
	}

	public function get_category_kids_count($id,$get_ids=false)
	{
		$count_kids=0;
		$ids=array();
		if(isset($this->category_array[$id]))
		{
			$ct=$this->category_array[$id];

			if(count($ct['kids'])>0)
				foreach($ct['kids'] as $v)
				{
					$count_kids+=$v['count'];
					if($ct['pid']!=$v['cid'])
					{
						 if($get_ids)
						 {
								list($kids_count,$kids_ids)=$this->get_category_kids_count($v['cid'],true);
								$count_kids+=$kids_count;
								$ids=array_merge($kids_ids,$ids);
						 }
						 else
								$count_kids+=$this->get_category_kids_count($v['cid']);
					}
					if($get_ids) $ids[] = $v['cid'];
				}
			if($get_ids)
				return array($count_kids,$ids);
		}
		return $count_kids;
	}

	public function get_active_categories($cats_array,$item_category=-1)
	{
		$category_id=$this->get_categoryid();
		if($category_id===false && $item_category>-1)
			$category_id=$item_category;
		if($category_id===false)
			return array();
		foreach($cats_array as $v)
			if($v['id'] == $category_id)
			{
				if($v['pid']>-1)
				{
					$pp=isset($this->category_array[$v['pid']])?$this->category_array[$v['pid']]:false;
					if($pp && $pp['pid']>-1)
						return array($v['id'],$v['pid'],$pp['pid']);
					else
						return array($v['id'],$v['pid']);
				}
				else
					return array($v['id']);
			}
		return array();
	}

	protected function build_sidebar_menu($cat_array,&$i,$direction,$collaps,$parent_active,$parents_only)
	{
		$level=$cat_array[$i]['level'];
		$sub_style=($collaps?'display:none;':'');

		$cat_active=$cat_array[$i]['active'];
		$html=F_LF;
		if($level==1)
			$html.='<ul class="'.$direction.'_cat_list" style="list-style-type:none">';
		else
			$html.='<ul class="ver_cat_list_sub" style="'.($cat_active || $parent_active?'':$sub_style).'">';

		while(isset($cat_array[$i]) && $level==$cat_array[$i]['level'])
		{
			$cdata=$cat_array[$i];
			$parent_active=$active=$cdata['active'];
			$cnt=$cdata['count'];
			$html.='
				 <li class="vcl_level'.$level.' '.($level!=1?($level==3?'vcl_ss':'vcl_s'):'vcl_m').($active?' active':'').(!$parents_only && $cnt==0?' vcl_toggle':'').'">
					  '.$cdata['line'];
			$i++;
			if(isset($cat_array[$i]) && $level<$cat_array[$i]['level'])
				$html.=self::build_sidebar_menu($cat_array,$i,$direction,$collaps,$parent_active,$parents_only);

			$html.='</li>';
		}
		$html.=F_LF.($direction=='hor' && $level==1?'<li class="clear"></li>':'').'</ul>';

		return $html;
	}

	protected function collapsSidebar($cat_array,$collaps,&$js,&$css,$parents_only,$direction='ver')
	{
		$i=0;
		$output=$this->build_sidebar_menu($cat_array,$i,$direction,$collaps,true,$parents_only);

		$js_src='
		$(document).ready(function(){
			$(".vcl_toggle").each(function(){
			 $(this).children().eq(0).off("click").on("click",function(e){
			  $(this).next().next().toggle();
			  e.preventDefault();
		    });
		  });
		});';

		$css_src='
		.vcl_ma{font-weight: bold;}
		.vcl_sa{padding-left:10px;}
		.vcl_m.active a.vcl_ma{text-decoration:none;}
		.vcl_s.active a{text-decoration:none;}
		.hor_cat_list .vcl_m{float:left;}
		.vcl_ss{margin-left:15px;}';
		if(strpos($js,$js_src)===false)
			$js.=$js_src;
		if(strpos($css,$css_src)===false)
			$css.=$css_src;
		return $output;
	}

	protected function parse_sidebar_line($param,$data,$a_class,$action,$mode,$active,$count,$count_kids,$absurls)
	{
		$level=1;
		if(isset($this->category_array[$data['pid']]['pid']) && $this->category_array[$data['pid']]['pid']>-1)
			$level=3;
		elseif ($data['pid']>-1)
			$level=2;

		$cv=isset($data['linked'])?$data['linked']:$data;
		$name=Formatter::unEsc($cv['name']);
		$title=htmlspecialchars($name,ENT_QUOTES);
		$plink=$this->page->build_permalink_cat(
						$this->page->is_lister?$data['id']:$name,
						$absurls,
						$data['view'],
						$action).(($mode<>'')?'&amp;mode='.$mode:'');

		$parsed_line=$param;

		if(strpos($parsed_line,'%SCALE[%image1%')!==false)
		{
			$val=$cv['image1']==''?'http://www.ezgenerator.com/services/missing.png':$cv['image1'];
			Images::parseScale($cv,$parsed_line,'image1',0,0,$val);
		}

		$parsed_line=str_ireplace(
			array($a_class,'rvts4','%title%','%name%','%category_color%','%item_url%','%count%','collaps','%description%','%description2%','%image1%'),
			array($a_class.($level>1?' vcl_sa':' vcl_ma'),
					($level>1?($active?'rvts8':'rvts12'):($active?'rvts0':'rvts4')),
					$title,
					$name,
					isset($cv['color'])?$cv['color']:'',
					$plink,
					$count+$count_kids,
					'',
					isset($cv['description'])?$cv['description']:'',
					isset($cv['description2'])?$cv['description2']:'',
					isset($data['image1'])?$data['image1']:''),
			$parsed_line);

			return array(
					'line'=>$parsed_line,
					'active'=>$active,
					'level'=>$level,
					'count'=>$count,
					'kids'=>$count_kids);
	}


	public function category_sidebar($external,$direction='ver',$param='',$absurls=false,$mode='',
		$logged=false,$action='',$collaps=true,$parents_only=false,$item_category=-1,$limit=false)
	{
 		if($param=='')
			$param='<a class="rvts4 cat_list_href" rel="category" href="%item_url%">%title%</a>  <span class="rvts8 cat_list_cnt">(%count%)'.($direction=='hor'?'&nbsp;':'').'</span>';
		$collaps = strpos($param, 'collaps')!==false ? 'collaps':$collaps;
		$a_class=Formatter::GFSAbi($param,'<a class="','"');
		$a_class=substr($a_class,0,strlen($a_class)-1);
		$cats_for_sidebar=array();
		$active_cats=$this->get_active_categories($this->category_array,$item_category);

		foreach($this->category_array as $k=>$v)
		{
			$active=in_array($v['id'],$active_cats);
			$visible=true;
			if($this->page->page_type==CALENDAR_PAGE)
			{
				$restricted=$this->get_category_restricted($k)&&!$logged;
				$visible=(!$restricted && $this->get_category_visible($k));
			}

			if($limit!==false && !in_array($v['id'],$limit))
				$visible=false;

			if($visible)
			{
				$id=$v['id'];
				if($v['name']=='all')
					$count=$this->get_postsCount();
				else
					$count=$v['count'];
				$count_kids=$this->get_category_kids_count($id);

				if($count>0 || $count_kids>0)
				{
					if($parents_only && $v['pid']>0)
						continue;

					$cats_for_sidebar[]=$this->parse_sidebar_line($param,$v,$a_class,$action,$mode,$active,$count,$count_kids,$absurls);
				}
			}
		}

		$output=(count($cats_for_sidebar)>0)?self::collapsSidebar($cats_for_sidebar,$collaps,$this->page->page_scripts,$this->page->page_css,$parents_only,$direction):''; //replace the macro with nothing

		if($external && !$this->page->is_lister)
			$output='<style type="text/css">'.$this->page->page_css.'</style><script type="text/javascript">'.$this->page->page_scripts.'</script>'.$output;

		return '<div class="entries_sidebar'.($limit!==false?' subcategories':'').'" id="category_sidebar">'.$output.'</div>';
	}
	public function build_category_combo($label,$name,$selected,$jstring,$style,$int,$type,$field,
			$alt_links=false,$remove_restricted=false,$logged=false,$show_count=true,$parents_only=false,$cid=false,$add_new_item=false,$manage_prod=false)
	{
		$selected=is_array($selected)?$selected:array($selected);
		$selected_enc=urlencode($selected[0]);

		$r='<select '.($int?'':'class="input1" ').$jstring.' '.$style.' name="'.$name.'" id="'.$name.'">';
		if(($type==1 || $type==3) && $label!='')
			$r.='<option value="all"'.($selected[0]=='All categories'?' selected="selected"':'').'>'.$label.'</option>';
		elseif($type==2 || $type==4) //Joe: 4 means having none (-1) when all possible cats are listed. Nested subcat system
			$r.='<option value="-1"'.($selected[0]=='-1'?' selected="selected"':'').'>none</option>';
		foreach($this->category_array as $k=>$v)
		{
			if($this->page->page_type==CALENDAR_PAGE && $remove_restricted)
			{
				$restricted=$this->get_category_restricted($k)&&!$logged;
				$visible=(!$restricted && $this->get_category_visible($k));
			}
			else
				$visible=true;

			if($visible)
			{
				$cv=isset($v['linked'])?$v['linked']:$v;
				$id=$v['id'];
				$count=$v['count'];
				$parent=-1;
				$count_kids=0;
				if(isset($v['pid']))
				{
					$parent=$v['pid'];
					$count_kids=$this->get_category_kids_count($id);
				}
				if($parent==-1||$type!=2)
				{
					if($type!=3 || $count> 0 || $count_kids>0)
					{
						if($parents_only && $v['pid']>0) continue;
						$view=(isset($v['view']) && $v['view']!='')?($alt_links?'/':'&').$v['view']:'';
						$bg='background: -webkit-linear-gradient(left,transparent 94%,'.$v['color'].' 94%,'.$v['color'].' 100%);background: linear-gradient(to right,transparent 94%,'.$v['color'].' 94%,'.$v['color'].' 100%);';

						$cname=urlencode($cv[$field]);
						if($this->page->is_lister && $field=='name' && $v['pid']>-1)
						{
							$vp=$this->category_array[$v['pid']];
							$cvp=isset($vp['linked'])?$vp['linked']:$vp;
							$cname=urlencode($cvp[$field]).($this->page->use_alt_plinks?'/':'&amp;subcat=').$cname;
						}
						$dash='';

						if($v['pid']>-1)
						{
							$level=$this->category_array[$v['pid']]['pid']>-1?3:2;
							if($level==3)
								$dash='&mdash;&nbsp;&mdash;';
							else
								$dash='&mdash;';
						}
						else $level=1;

						$r.='<option style="'.$bg.'" value="'.$cname.$view.'"';
						$is_selected=in_array($cname,$selected) || $cname==$selected_enc;
						$disable_add_items=($add_new_item||!$is_selected)&&
							$manage_prod&&$this->page->is_lister&&isset($v['disable_add_items'])&&$v['disable_add_items'];

						if($is_selected)
							$r.=$disable_add_items?'':' selected="selected"';
						if(($cid!==false && ($cid==$v['id'] || $cid==$parent || $level==3)) || $disable_add_items)
							$r.=' disabled=""';
						$r.='>'.$dash.urldecode($cv['name']).($show_count?' ('.$v['count'].')':'').'</option>';
					}
				}
			}
		}
		$r.='</select>';
		return $r;
	}
	public function build_category_cloud($remove_restricted=false)
	{
		$all_cats=array();
		if($remove_restricted)
			$logged=$this->page->user->mGetLoggedAs()!='';
		foreach($this->category_array as $k=>$v)
		{
			if($remove_restricted)
			{
				$restricted=$this->get_category_restricted($k)&&!$logged;
				$visible=(!$restricted && $this->get_category_visible($k));
			}
			else
				$visible=true;

			if($visible)
			{
				$id=$v['id'];
				$count=$v['count'];
				$parent=-1;$count_kids=0;
				if(isset($v['pid']))
				{
					$parent=$v['pid'];
					$count_kids=$this->get_category_kids_count($id);
				}
				if($count> 0 || $count_kids>0)
					$all_cats[$v['name']]=$count;
			}
		}
		$url_for_tc=($this->page->use_alt_plinks? str_replace('/'.$this->page->pg_name,'',$this->page->script_path): $this->page->pg_name.'?');
		return Builder::buildTagCloud($url_for_tc,$all_cats,1000,'',true,$this->page->use_alt_plinks);
	}

	public function sort_category()
	{
		global $db;

		if(isset($_REQUEST['ca_order']))
			$sid='sort_category';
		else
		{
			$this->page->user->mGetLoggedUser($db,'');
			$sid='sort'.$this->page->user->getId();
		}
		$sort=(isset($this->page->all_settings[$sid])?$this->page->all_settings[$sid]:'');
		if(isset($_REQUEST['sort']))
		{
			$s=$_REQUEST['sort'];$r='';
			foreach($s as $k=>$v)
				$r.=strval(intval($v)).'|';
			$r.=Formatter::GFSAbi($sort,'-','');
		}
		else
		{
			$r=Formatter::GFS($sort,'','-');
			$t=explode('|',Formatter::GFS($sort,'-',''));
			$id=intval($_REQUEST['toggle']);$aid=abs($id);
			foreach($t as $k=>$v)
				if($v==$aid)
					unset($t[$k]);
			if($id<0)
				$t[]=abs($aid);
			$r.='-'.implode('|',$t);
		}
		$this->page->db_insert_settings(array($sid=>$r));
		if($sid=='sort_category')
		{
			$this->page->db_fetch_settings();
			$this->build_categories_list(true,$this->page->is_lister?$this->page->g_linkedid:null,1);
		}
		exit;
	}

	public function category_legend($src,$mode,$logged)
	{
		$macro=Formatter::GFSAbi($src,'%CALENDAR_LEGEND','%');
		if($macro!='')
		{
			$cols=intval(Formatter::GFS($macro,'%CALENDAR_LEGEND(',')%'));
			$col_width=$cols<2?100:intval(100/$cols)-1;
			$output = '<div class="category_legend">'.F_LF;
			foreach($this->category_array as $k=>$v)
			{
				$restricted=$this->get_category_restricted($k)&&!$logged;
				if(!$restricted)
				{
					$va=Formatter::unEsc($v['name']);
					$url=$this->page->pg_name.'?'.($k>0?'category='.urlencode($va).($mode!=''?'&amp;':''):'').($mode!=''?'mode='.$mode:'');
					$output.='<div class="category_legend_entry"  style="width:'.$col_width.'%;">
						<span class="category_legend_col" style="background: '.$v['color'].';"></span>
						<a class="rvts8 category_legend_label" href="'.$url.'">'.$va.'</a>
						</div>';
				}
			}
			$output.= '</div>'.F_LF;

			$src=str_replace($macro,$output,$src);
		}
		return $src;
	}

	public function parse_category_meta(&$src,$category_id)
	{
		 $cdata=($category_id=='all'?
					array('name'=>$this->page->lang_l('all categories'),'description'=>''):
					$this->get_categoryData($category_id));

		 Meta::replaceDesc($src,$cdata,'description','description2');
		 Meta::replaceTitle($src,$cdata,'page_title','name');
	}

	public function get_categoryViewDefined($cid)
	{
		return isset($this->category_array[$cid])?$this->category_array[$cid]['view']:'';
	}

	public function category_searchlist()
	{
		$output='<input type="checkbox" class="allcat_chb" name="category[]" value="All categories" checked="checked">&nbsp;<span>'.$this->page->lang_l('all categories').'</span>' .F_BR;

		foreach($this->category_array as $v)
		{
			$va=Formatter::unEsc($v['name']);
			$va_enc=htmlspecialchars($va,ENT_QUOTES);
			$count=$v['count'];
			if($count>0)
				$output.='<label>&nbsp;'.($v['pid']!='-1'?'&nbsp;&nbsp;':'').'<input type="checkbox" class="cat_chb" name="category[]" value="'.$va_enc.'">&nbsp;<span>'.$va_enc.' ('.$count.')</span></label>' .F_BR;
			elseif($v['pid']=='-1')
			{
				$kids_count=$this->get_category_kids_count($v['id']);
				if($kids_count>0)
					 $output.='<label>&nbsp;<input type="checkbox" disabled="disabled" class="cat_chb" name="category[]" value="'.$va_enc.'">&nbsp;<span>'.$va_enc.' ('.($count+$kids_count).')</span></label>' .F_BR;
			}
		}
		return $output;
	}

	public function category_nameexists_check($cname,$cparent)
	{
		foreach($this->category_array as $v)
			if($v['name'] == $cname && $v['pid'] == $cparent)
				 return $v['cid'];
		return false;
	}

	protected function save_category()
	{
		global $db;

		$data=array();
		if($this->page->page_type==CALENDAR_PAGE)
		{
			$_POST['visible']=(isset($_POST['cat_invisible']))?'no':'yes';
			$_POST['restricted']=isset($_POST['restricted'])?1:0;
			$_POST['category_type']=intval($_POST['category_type']);
		}
		$field_names=$db->db_fieldnames($this->page->data_pre.'categories');
		foreach($field_names as $v)
			if(isset($_POST[$v]))
				$data[$v]=$_POST[$v];

		$cid=$this->get_nextCategoryId();
		$data['cid']=$cid;
		$db->query_insert($this->page->data_pre.'categories',$data);
		return $cid;
	}

	public function duplicate_category()
	{
		global $db;
		$cid=isset($_REQUEST['category_id'])?intval($_REQUEST['category_id']):0;
		$old=$db->query_first('SELECT *
			FROM '.$db->pre.$this->page->data_pre.'categories
			WHERE cid = '.$cid);

		foreach($old as $k=>$v)
			$_POST[$k]=$v;
		$_POST['cname'].='_duplicate';
		$cnt=1;
		while($this->category_nameexists_check($_POST['cname']))
		  $_POST['cname'].='_'.$cnt++;

		unset($_POST['id']);
		unset($_POST['cid']);
		$this->save_category();
		$this->page->db_fetch_settings();
		$this->build_categories_list(true,$this->page->is_lister?$this->page->g_linkedid:null,1);
	}

	public function add_category_ajax($print=1)
	{
		if(empty($_POST['cname']) || empty($_POST['ccolor']) || $_POST['ccolor']=='#')
			print $this->page->lang_l('cat err msg');
		else
		{
			if($this->category_nameexists_check($_POST['cname'],$_POST['parentid'])!==false)
			{
				print $this->page->lang_l('cat exists');
				exit;
			}
			$cid=$this->save_category();
			if($print)
				print '#'.$cid;
		  else
				return $cid;
		}
		exit;
	}

	public function add_category(&$output)
	{
		$cid=$this->category_nameexists_check($_POST['cname'],$_POST['parentid']);
		if($cid!==false)
			$output.=$this->page->lang_l('cat exists');
		else
		{
			$cid=$this->save_category();
			$this->page->db_fetch_settings();
			$this->build_categories_list(true,$this->page->is_lister?$this->page->g_linkedid:null,1);
		}
		return $cid;
	}

	public function edit_category()
	{
		global $db;

		$field_names=$db->db_fieldnames($this->page->data_pre.'categories');
		$data=array();
		$cid=isset($_REQUEST['category_id'])?intval($_REQUEST['category_id']):0;
		if($cid<0)
			return;
		if($this->page->page_type==CALENDAR_PAGE)
		{
			$_REQUEST['visible']=(isset($_POST['cat_invisible']))?'no':'yes';
			$_REQUEST['restricted']=isset($_POST['restricted'])?1:0;
		}
		if($this->page->is_lister)
			$_REQUEST['disable_add_items']=isset($_POST['disable_add_items'])?1:0;

		foreach($field_names as $v)
			if(isset($_REQUEST[$v]))
				 $data[$v]=$_REQUEST[$v];

		if(!empty($data) && count($data)> 0)
		{
			if($this->page->is_lister && $this->page->g_linked)
			{
				if($this->page->g_linked && isset($this->category_array[$cid]['linked']))
					$db->query_update($this->page->pg_id.'_categories',$data,'cid='.$cid);
				else
				{
					$data['cid']=$cid;
					$db->query_insert($this->page->pg_id.'_categories',$data);
				}
			}
			else
				 $db->query_update($this->page->data_pre.'categories',$data,'cid='.$cid);
			$this->page->db_fetch_settings();
			$this->build_categories_list(true,$this->page->is_lister?$this->page->g_linkedid:null,1);
		}
	}

	public function publish_category($flag)
	{
		global $db;
		$cid=isset($_REQUEST['category_id'])?intval($_REQUEST['category_id']):0;

		if($this->page->is_lister)
			$status=($flag?'1':'0');
		else
			$status=($flag?'published':'unpublished');
		$db->query('UPDATE '.$db->pre.$this->page->pg_pre.$this->page->data_tablename.'
					 SET '.$this->page->data_table_publish_field.' = "'.$status.'"
					 WHERE '.$this->page->data_table_cidfield.' = '.$cid);
	}

	public function delete_category(&$output)
	{
		global $db;
		$cid=isset($_REQUEST['category_id'])?intval($_REQUEST['category_id']):0;
		if($cid<1)
			return;

		$cat_count=$db->fetch_all_array('SELECT COUNT(*)
			FROM '.$db->pre.$this->page->pg_pre.$this->page->data_tablename.'
			WHERE '.$this->page->data_table_cidfield.' = '.$cid.'
			GROUP BY '.$this->page->data_table_cidfield);

		if(!empty($cat_count) && $cat_count[0]['COUNT(*)']>0)
			$output.=$this->page->lang_l('category del warning');
		else
		{
			$table=$this->page->is_lister?($this->page->g_linked?$this->page->pg_pre:$this->page->g_datapre):$this->page->pg_pre;
			$db->query('
				DELETE FROM '.$db->pre.$table.'categories
				WHERE cid = '.$cid);
		}
		$this->page->db_fetch_settings();
		$this->build_categories_list(true,$this->page->is_lister?$this->page->g_linkedid:null,1);
	}

	public function build_categ_inmenu_array()
	{
		 $result=array();
		 foreach($this->category_array as $v)
		 {
			 if($this->page->pg_settings['inmenu_sub'] || $v['pid']==-1)
			 {
				 $vLinked=isset($v['linked'])?$v['linked']:$v;
				 $result[]=array(
					'id'=>$v['id'],
					'pid'=>$v['pid'],
					'kids'=>($this->page->pg_settings['inmenu_sub']?$v['kids']:array()),
					'name'=>$vLinked['name'],
					'count'=>$v['count'],
					'permalink_cat'=>$this->page->build_permalink_cat($this->page->is_lister?$v['id']:$v['name'],true,$v['view']),
					'ttype'=>$this->f->ttype);
			 }
		 }
		 return $result;
	}

	public function update_settings_categ_inmenu()
	{
		 if($this->page->pg_settings['inmenu'])
		 {
			 if(!isset($this->f->ca_settings['categories_inmenu']))
				$array_old_cat=array();
			 else
				$array_old_cat = unserialize($this->f->ca_settings['categories_inmenu']);

			 $array_old_cat[$this->page->pg_id] = $this->build_categ_inmenu_array();
			  CA::insert_setting(array('categories_inmenu'=>serialize($array_old_cat)));
		 }
		 else
			 $this->delete_settings_categ_inmenu();
	 }

	 public function delete_settings_categ_inmenu()
    {
		if(isset($this->f->ca_settings['categories_inmenu']))
		{
				$page_id = $this->page->pg_id;
				$array_old_cat = unserialize($this->f->ca_settings['categories_inmenu']);
				if(isset($array_old_cat[$page_id]) && is_array($array_old_cat[$page_id]))
				{
					unset($array_old_cat[$page_id]);
					CA::insert_setting(array('categories_inmenu'=>serialize($array_old_cat)));
				}
		}
	 }

	public function include_categories_inmenu($output)
	{
		if($this->page->pg_settings['inmenu'])
		{
			$category_id=$this->get_categoryid();
			$c_id=$menu='';
			$hasSmenu=0;
			foreach($this->category_array as $v)
			{
				if($c_id!=$v['id'])
				{
					$vLinked=isset($v['linked'])?$v['linked']:$v;
					$c_id=$v['id'];
					$level1=$v['pid']==-1;
					$has_kids=count($v['kids'])>0;
					$kids_count=0;
					if($has_kids)
						foreach($v['kids'] as $kk=>$vv)
							$kids_count+=$vv['count'];
					if($v['count']>0 || $kids_count>0)
					{
						if($level1)
						{
							$temp=F_LF.str_replace(
										array('%MenuItemUrl%','%MenuItemText%'),
										array($this->page->build_permalink_cat($this->page->is_lister?$c_id:$vLinked['name'],false,$v['view']),
											$vLinked['name']),
										$this->f->ssmenu);
							if($category_id!==false && $category_id==$v['id'])
								$temp=str_replace(array('<a','class="'),array('<a id="sa"','class="active '),$temp);
							$smenu='';
							if($kids_count>0 && $this->page->pg_settings['inmenu_sub'])
								foreach($v['kids'] as $kk=>$vv)
								{
									if($vv['count']>0 && isset($this->category_array[$vv['cid']]))
									{
										 $kidData=$this->category_array[$vv['cid']];
										 $kidDataLinked=isset($kidData['linked'])?$kidData['linked']:$kidData;
										 $sitem=F_LF.str_replace(
													array('%MenuItemUrl%','%MenuItemText%'),
													array($this->page->build_permalink_cat($this->page->is_lister?$vv['cid']:$kidData['name'],false,$kidData['view']),
														  ($this->f->ttype==4?'':'&rarr; ').$kidDataLinked['name'],''),
													$this->f->ssmenu);
										 if($category_id!==false && $category_id==$kidData['id'])
											 $sitem=str_replace(array('<a','class="'),array('<a id="sa"','class="active '),$sitem);
										 $smenu.=$sitem;
									}
								}
							if($this->f->ttype==4 && trim($smenu)!=='')
							{
								$menu.=str_replace('</li>','<ul>'.$smenu.'</ul></li>',$temp);
								$hasSmenu=1;
							}
							else
								$menu.=$temp.$smenu;
						}
					}
				}
			}
			$pos=strpos($output,'<!--pid_'.$this->page->pg_id.'-->');
			if($pos!==false)
			{
				if($hasSmenu)
					 $output=str_replace('<!--pid_'.$this->page->pg_id.'-->',$menu,$output);
				else
					 $output=str_replace('</li><!--pid_'.$this->page->pg_id.'-->','<ul>'.$menu.'</ul></li>',$output);
			}
			else
			{
				if($this->f->ttype==4)
				{
					if(strpos($output,'<!--pidc_'.$this->page->pg_id.'-->'))
						$output=str_replace('<!--pidc_'.$this->page->pg_id.'-->','<ul>'.$menu.'</ul>',$output);
               else
					{
						$old_menu=Formatter::GFS($output,'<li id="ci'.$this->page->pg_id.'">','</li>');
						$pos_2=strpos($old_menu,'<ul>');
						if($pos_2!==false)
						{
							$old_menu_new=substr_replace($old_menu,$menu,($pos_2+4),0);
							$output=str_replace($old_menu,$old_menu_new, $output);
						}
					}
				}
				else
					$output=str_replace('<!--pidc_'.$this->page->pg_id.'-->',$menu,$output);

				if(strpos($output,'<li id="mi'.$this->page->pg_id))
				{
					$smenu=Formatter::GFSAbi($output,'<li id="mi'.$this->page->pg_id.'">','</li>');
					$output=str_replace($smenu,$smenu.$menu, $output);
				}
			}
		}
		return $output;
	}


	public function get_nextCategoryId()
	{
		$next_id=0;
		foreach($this->category_array as $v)
			if($v['id']>$next_id)
				$next_id=$v['id'];
		$next_id++;
		return $next_id;
	}
	public function get_category_restricted($id)
	{
		return $this->category_array[$id]['restricted']==1;
	}

	public function get_category_visible($id)
	{
		return isset($this->category_array[$id])?$this->category_array[$id]['visible']=='yes':false;
	}

	public function get_category_mark($id)
	{
		$cm=$this->category_array[$id]['mark'];
		if($cm=='') $cm='NA';
		return $cm;
	}

	public function get_category_markcolor($id)
	{
		$cm=$this->category_array[$id]['markcolor'];
		if($cm=='')$cm='#ffffff';
		return $cm;
	}

	public function quick_addcategory()
	{
		$result=
			'<p><span class="rvts8 a_editcaption">'.$this->page->lang_l('name').'</span></p>
			<input class="input1" type="text" value="" name="cname" id="cname" style="width:370px;">&nbsp;
			<input class="color input1" type="text" value="#ff3300" name="ccolor" id="ccolor" style="width:60px;background:#ff3300"><br>';
		if($this->page->page_type==CALENDAR_PAGE)
			$result.='
				<p>
					 <span class="rvts8 a_editcaption">'.$this->page->lang_l('mark').'</span>
				</p>'
				  .Builder::buildSelect('cmark',$this->page->marks_array,'',' title="'.$this->page->lang_l('mark days').'" ', 'value').
				' <input class="color input1" type="text" value="#ff3300" name="cmark_color" id="cmark_color" style="width:60px;background:#ff3300"><br><br>
				<p>
					 <input type="checkbox" name="cat_invisible" value="no">
					 <span class="rvts8 a_editcaption">'.$this->page->lang_l('invisible').'</span>
				</p>
				<p>
					 <input id="restricted" type="checkbox" name="restricted" value="1">
					 <span class="rvts8 a_editcaption">'.$this->page->lang_l('restricted to registered').'</span>
				</p>
				<p>
					 <span class="rvts8 a_editcaption">'.$this->page->lang_l('category type').'</span>
				</p>'
				  .Builder::buildSelect('category_type',$this->page->category_type_array,'',' title="" ','key').
				'<br>';

		$result.='<span class="rvts8 a_editcaption">parent</span><br>'.
			$this->build_category_combo($this->page->lang_l('all categories'),'parentid',-1,'','',false,4,'id').'<br>
			<input class="input1" type="button" name="add_category" onclick="add_category_ajax();" value="' .$this->page->lang_l('save').'" />';
		$result='<div id="new_cat" style="padding:10px 0px 0px 65px;display:none">'.$result.'</div>';
		return $result;
	}

	public function get_admin_category_nav()
	{
		$drag=$this->page->user->isAdminOnPage($this->page->pg_id);
		$nav='<input type="button" value="'.$this->page->lang_l('add category').'" onclick="javascript:sv(\'reply_to_\');">';
		if($drag)
			$nav.='<input type="button" onclick="document.location=\''.$this->page->script_path.'?action=categories&dragmode=1\'" value="'.$this->page->lang_l('change categories order').'">';
		return $nav;
	}

	public function get_admin_category_entry($data,$logged_as_admin,$show_id,$err_on_submit)
	{
		$cid=$data['id'];
		$cv=isset($data['linked'])?$data['linked']:$data;
		$adding=$cid=='';
		$cname=str_replace('"','&quot;',$cv['name']);
		$ccolor=$cv['color'];
		$pid=$data['pid'];
		$title=isset($cv['page_title'])?$cv['page_title']:'';
		$views=$logged_as_admin && ($this->page->page_type==PHOTOBLOG_PAGE || $this->page->page_type==BLOG_PAGE);

		$entry='
			<input class="input1" type="hidden" name="category_id" value="'.$cid.'">
			<input '.(isset($this->page->all_settings['translit']) && $this->page->all_settings['translit']?'onkeyup="translitTo($(this).val(),\'ct_permalink'.$cid.'\')"':'').' class="input1" type="text" value="'.($err_on_submit? $_POST['cname']: $cname).'" name="cname" id="cname'.$cid.'" style="width:360px;">&nbsp;
			<input class="color input1" type="text" value="'.$ccolor.'" name="ccolor" id="ccolor'.$cid.'" style="width:60px;background:'.$ccolor.'"><br>';

		if($this->page->use_alt_plinks)
			$entry.='<br><span class="rvts8 a_editcaption">'.$this->page->lang_l('permalink').'</span><br>'.
			'<input id="ct_permalink'.$cid.'" class="input1" type="text" value="'.($err_on_submit? $_POST['ct_permalink']: (isset($cv['ct_permalink'])?$cv['ct_permalink']:'')).'" name="ct_permalink" id="ct_permalink'.$cid.'" style="width:370px;">&nbsp<br>';

		$entry.=Builder::buildInput('image1',$cv['image1'],'width:360px;','','text',' id="image1_'.$cid.'" onchange="fixima(this.value,\'image1_'.$cid.'\');"','',$this->page->lang_l('image')).'
			<input class="input1" type="button" name="btnAsset" onclick="openAsset(\'image1_'.$cid.'\')" value="'.$this->page->lang_l('browse').'"/><br><br>
			<img id="ima_image1_'.$cid.'" src="'.$cv['image1'].'" alt="" style="'.(($cv['image1']=='')?'display:none;':'').'height:60px;padding-top:3px;">';

		if($this->page->page_type==BLOG_PAGE||$this->page->page_type==SHOP_PAGE){
			Editor::addSlideshow_Plugin_Editor($this->page->innova_def,$this->page->innova_js,$this->page->rel_path,$this->page->pg_settings['ed_lang'],$this->page->lang_l('slideshow'),$this->page->pg_id);
			Editor::addhtml5Player_Plugin_Editor($this->page->innova_def,$this->page->innova_js,$this->page->rel_path,$this->page->pg_settings['ed_lang'],$this->page->lang_l('html5player'),$this->page->pg_id);
		}
		if($this->page->page_type==CALENDAR_PAGE)
			$entry.='<p><span class="rvts8 a_editcaption">'.$this->page->lang_l('mark').'</span></p>'.
				Builder::buildSelect('cmark',$this->page->marks_array,$data['mark'],' title="'.$this->page->lang_l('mark days').'" ', 'value').'
				<input class="color input1" type="text" value="'.$data['markcolor'].'" name="cmark_color" id="cmark_color'.$cid.'" style="width:60px;background:' .$data['markcolor'].'"><br><br>
				<p><input type="checkbox" name="cat_invisible" value="no" '.($data['visible']!='yes'?' checked="checked"':'').'>
				<span class="rvts8 a_editcaption">'.$this->page->lang_l('invisible').'</span></p>
				<p><input type="checkbox" name="restricted" value="1" '.($data['restricted']?' checked="checked"':'').'>
				<span class="rvts8 a_editcaption">'.$this->page->lang_l('restricted to registered').'</span></p>
				<p><span class="rvts8 a_editcaption">'.$this->page->lang_l('category type').'</span></p>'.
				Builder::buildSelect('category_type',$this->page->category_type_array,$data['category_type'],' title="" ', 'key').'
				<br>';
		else
			$entry.='<textarea class="mceEditor" id="txtContent'.$cid.'" name="description" style="width:100%" rows="4" cols="30">'.$data['description'].'</textarea>'.
				($adding?str_replace(array('oEdit1','htmlarea','450px'),array('oEdit1'.$cid,'txtContent'.$cid,'250px'),$this->page->innova_def).F_BR:'');
		if($adding)
			$this->page->innova_js=str_replace('".mceEditor"','"#txtContent"',$this->page->innova_js);

		$entry.='<span class="rvts8 a_editcaption">'.$this->page->lang_l('parent')  .'</span><br>'
				  .$this->build_category_combo($this->page->lang_l('all categories'),'parentid',$pid,'','',false,4,'id',false,false,false,true,false,$cid).F_BR;

		if(isset($cv['description2']))
			$entry.='<p><span class="rvts8 a_editcaption">'.$this->page->lang_l('description').'2</span></p>
				<textarea name="description2" style="width:98%" rows="4" cols="30">'.$cv['description2'].'</textarea>';
		if($views && count($this->page->pg_settings['views']))
			$entry.='<br><span class="rvts8 a_editcaption">'.$this->page->lang_l('view').'</span><br>'.$this->page->build_view_combo($data['viewid']).F_BR;

		$entry.='<br><span class="rvts8 a_editcaption">'.$this->page->lang_l('title').'</span><br>'.
			'<input class="input1" type="text" value="'.($err_on_submit? $_POST['page_title']: $title).'" name="page_title" id="page_title'.$cid.'" style="width:370px;">&nbsp<br>';

		if($this->page->is_lister)
			$entry.='<br/>'.Builder::buildCheckbox('disable_add_items',isset($cv['disable_add_items'])&&$cv['disable_add_items']?true:false,'').'<span class="rvts8 a_editcaption">'.$this->page->lang_l('disable add items').'</span><br/>';
//buttons
		$entry.='<br><input class="input1" onclick="return vAddCategory(\''.$cid.'\',\''.$this->page->lang_l('cat err msg').'\')" type="submit" name="submit" value="'.$this->page->lang_l('save').'">&nbsp;
			<input class="input1" type="button" value="'.$this->page->lang_l('cancel').'" onclick="javascript:sv(\'reply_to_'.$cid.'\');"><br><br>';

		$result=
		'<div'.($adding?' class="'.$this->page->f->atbgr_class.'"':'').' id="reply_to_'.$cid.'" style="'.($adding?'padding: 0 10px;':'').'display:'.(($show_id!=='')&&($show_id==$cid)?'visible':'none').';">'.
			($adding?'<span class="rvts8 a_editcaption">'.$this->page->lang_l('category').'</span>':'').'
			<form method="post" action="'.$this->page->script_path.'?action=categories&amp;do='.($adding?'add':'edit').'">
				<div class="edit_cat">'.$entry.'</div>
			</form>
		</div>';

		return $result;
	}

	public function get_admin_category_list($logged_as_admin,$drag_mode,$show_id,$err_on_submit,&$totalcount)
	{
		global $db;

		$table_data=array();

		$views=$this->page->page_type==PHOTOBLOG_PAGE || $this->page->page_type==BLOG_PAGE;
		$catsToShow = $drag_mode?
			$this->category_array:
			$this->getCategoryPageSet(Navigation::recordsPerPage(),$totalcount);

		$cat_count=array();
		$count_raw=$db->fetch_all_array('SELECT '.$this->page->data_table_cidfield.', COUNT(*)
			FROM '.$db->pre.$this->page->pg_pre.$this->page->data_tablename.'
			GROUP BY '.$this->page->data_table_cidfield);

		foreach($count_raw as $v)
			 $cat_count[$v[$this->page->data_table_cidfield]]=$v['COUNT(*)'];

		foreach($catsToShow as $k=>$v)
		{
			$cv=isset($v['linked'])?$v['linked']:$v;
			$ct_count=isset($cat_count[$v['id']])?$cat_count[$v['id']]:0;
			$view=$views && $v['viewid']>0?$this->page->get_view($v['viewid']):false;
			$view=$view===false?'':' ('.$view.')';
			$parId = $v['pid'];
			$parParId = $parId>-1 && isset($this->category_array[$parId]['pid'])?$this->category_array[$parId]['pid']:-1;
			$ddash=$parId>-1 && $parParId>-1;
			$entry_nav=array();
			$entry_nav[$this->page->lang_l('edit')]='javascript:void(0);" onclick="javascript:sv(\'reply_to_'.$k.'\');add_Editor(\'reply_to_'.$k.'\');';
			if($ct_count==0 && empty($v['kids']))
				$entry_nav[$this->page->lang_l('delete')]=$this->page->script_path .'?action=categories&amp;do=delete&amp;category_id='.$k.'" onclick="javascript:return confirm(\''.$this->page->lang_l('del category msg').'\');';

			$entry_nav[$this->page->lang_l('duplicate')]=$this->page->script_path .'?action=categories&amp;do=duplicate&amp;category_id='.$k.'" onclick="javascript:return confirm(\''.$this->page->lang_l('duplicate category msg').'\');';

			$entry='
				<div class="dra">
					<div style="width:435px;'.($drag_mode?'margin:3px;':'').'">
						<span class="rvts8 a_editcaption">'
						  .($parId==-1?'<b>':'')
						  .(!$drag_mode&&$parId>-1?($ddash?'&mdash; &mdash;': '&mdash; '):'').$cv['name'].$view
						  .($parId==-1?'</b>':'').'
						</span>
					</div>'.
					($drag_mode?'':$this->get_admin_category_entry($cv,$logged_as_admin,$show_id,$err_on_submit)).
				'</div>';

			$colorStyle=$drag_mode?'float:right;height:'.($drag_mode?'20':'35').'px;width:5px;':'margin-top:2px;height:13px;width:40px;';
			$color='<div title="'.str_replace('"','&quot;',$cv['name']).'" style="'.$colorStyle.'background:' .$cv['color'].';">&nbsp;</div>';
			$row_data=array($color,array($entry,$entry_nav));
			$entry_nav=array();
			if($ct_count>0 && (!$this->page->is_lister || $this->page->g_linkedid==0))
			{
				$entry_nav[$this->page->lang_l('publish')]=$this->page->script_path .'?action=categories&amp;do=publish&amp;category_id='.$k.'" onclick="javascript:return confirm(\''.$this->page->lang_l('publish category msg').'\');';
				$entry_nav[$this->page->lang_l('unpublish')]=$this->page->script_path .'?action=categories&amp;do=unpublish&amp;category_id='.$k.'" onclick="javascript:return confirm(\''.$this->page->lang_l('unpublish category msg').'\');';
			}

			if(!$drag_mode)
				$row_data[] = array('<span class="rvts8">('.$ct_count.')</span>',$entry_nav);
			if($drag_mode)
			{
				$row_data['pid']=$parId;
				$row_data['ppid']=$parParId;
			}
			$table_data[$v['id']]=$row_data;
		}
		return $table_data;
	}
}

class Comment
{
	private $page;
	public $scraperHandler;
	private $fmt_input_com='<input class="comments_input input1" type="text" name="%s" value="%s" id="comments_%s" maxlength="50" style="width: 98%%;" >';

	public function __construct($pg)
	{
		if($pg instanceof LivePageClass)
			$this->page=$pg;
		$this->scraperHandler=new ScraperHandler($pg);
	}

	//admin functions (will be later moved to separate class)
	private function edit_comment_form($comment_id,$entry_id,$data)
	{
		$span8='<p><span class="rvts8 a_editcaption" style="line-height:16px">%s</span></p>';

		$output='<div id="edit_'.$comment_id.'" style="padding:10px 0px 0px 0px;display:none;"><form action="'.$this->page->script_path.'?action=comments&amp;entry_id='.intval($entry_id)
		.$this->page->c_page_amp.'" method="post" enctype="multipart/form-data"><input type="hidden" name="approved" value="'.$data['approved'].'">';
		if(isset($data['ip']))
			$output.=sprintf($this->page->f->fmt_hidden,'comment_id',$data['comment_id']);

		$output.=sprintf($span8,$this->page->lang_l('your name')).sprintf($this->fmt_input_com,'visitor',Formatter::sth($data['visitor']),'visitor_'.$comment_id)
		.sprintf($span8,$this->page->lang_l('email address')).sprintf($this->fmt_input_com,'email',$data['email'],'email_'.$comment_id)
		.sprintf($span8,$this->page->lang_l('url')).sprintf($this->fmt_input_com,'url',Formatter::sth($data['url']),'url_'.$comment_id)
		.sprintf($span8,$this->page->lang_l('comments'))
		.'<textarea class="input1" name="comments" cols="50" rows="10" style="overflow:hidden; width: 98%">'.Formatter::sth2($data['comments']).'</textarea><br>'
		.CommentHandler::buildHintDiv($this->page->pg_settings['lang_l'],'admin').'<br>';
		if(isset($data['ip']) )
			$output.=sprintf($this->page->f->fmt_hidden,'ip',$data['ip']).sprintf($this->page->f->fmt_hidden,'host',$data['host']).sprintf($this->page->f->fmt_hidden,'agent',Formatter::sth($data['agent']));

		$output.='<br>
			<input class="input1" name="Post" type="submit" value=" '.$this->page->lang_l('save').' ">
			<input class="input1" type="button" name="Cancel" value="'.$this->page->lang_l('cancel').'" onclick="javascript:sv(\'edit_'.$comment_id.'\');">
		</form>
		</div>';

		return $output;
	}

	public function comments_admin($data_count,$data)
	{
		global $db;
		$output='';
		$append='<input type="button" value=" '.$this->page->lang_l('check blocked ips').' " onclick="document.location=\''.$this->page->script_path.'?action=comments&amp;do=check_blockedip\'">'.F_BR.F_BR;
		if(!empty($data))
		{
			$nav=Navigation::pageCA($data_count,$this->page->script_path.'?action=comments',0,$this->page->c_page,$this->page->nav_labels);

			$cap_arrays=array(
				'',$this->page->lang_l('comments'),$this->page->lang_l('author'),$this->page->lang_l('status'));
			$table_data=array();
			$blocked_ips=$this->page->blockedIpModule->db_blockedips($db);

			foreach($data as $value)
			{
				$approve_fl=($value['approved']==0? false: true);
				$url=Formatter::sth($value['url']);
				$uft_flag=(strpos(Formatter::strToLower($this->page->pg_settings['page_encoding']),'utf')!==false);
				$e_id=$value['entry_id'];
				$c_id=$value['comment_id'];
				$rating=isset($value['rating'])?$value['rating']:0;
				$comments_value=str_replace(F_LF,F_BR,Formatter::sth2($value['comments']));
				$entry_url=$this->page->build_permalink($value,false,'',true);
				$dt=Formatter::mySubstr($this->page->month_name[date('n',Date::tzoneSql($value['date']))-1],0,3,$uft_flag).Date::formatTimeSql($value['date'],$this->page->pg_settings['time_format'],'long');
				$entry_time=$this->page->build_stat_with_icon($dt,'fa-clock-o',$this->page->lang_l('published'),$entry_url,145);

				$entry='
					<a class="rvts8" style="text-decoration:none;" href="'.$entry_url.'">'.$comments_value.'</a>'.F_BR.$entry_time.'
					<div id="reply_to_'.$c_id.'" style="padding:10px 0px 0px 0px;display:none;">
						 <span class="rvts8 a_editcaption">'.$this->page->lang_l('reply').'</span><br>
						  <form method="post" action="'.$this->page->script_path.'?action=comments&amp;do=reply&amp;entry_id='.$e_id.'&amp;page='.$this->page->c_page.'" enctype="multipart/form-data">
							 <textarea class="input1" name="comments" cols="70" rows="8" width="100%"></textarea><br>'
							 .(!empty($value['email'])?'<p><input type="checkbox" name="sendtouser" value="1"><span class="rvts8">'.$this->page->lang_l('send email').'</span></p>':'').'
							 <input type="hidden" name="parent_id" value="'.$value['id'].'">
							 <input type="hidden" name="related_comment_id" value="'.$c_id.'">
							 <input class="input1" type="submit" name="submit" value="'.$this->page->lang_l('save').'">
							 <input class="input1" type="button" name="Cancel" value="'.$this->page->lang_l('cancel').'" onclick="javascript:sv(\'reply_to_'.$c_id.'\');">
						  </form>
					</div>'
					.$this->edit_comment_form($c_id,$value['entry_id'],$value);

				$entry_nav=array($this->page->lang_l('edit')=>'javascript:void(0);" onclick="javascript:svc(\'reply_to_'.$c_id.'\');sv(\'edit_'.$c_id.'\');',
				$this->page->lang_l('delete')=>$this->page->script_path.'?action=comments&amp;do=delete&amp;comment_id=' .$c_id.'&amp;cc=1'.Linker::buildReturnURL(true).'" onclick="javascript:return confirm(\''.$this->page->lang_l('del comment msg').'\');',
				$this->page->lang_l('reply')=>'javascript:void(0);" onclick="javascript:svc(\'edit_'.$c_id.'\');sv(\'reply_to_'.$c_id.'\');',
				$this->page->lang_l(($approve_fl?'unapprove':'approve'))=>'javascript:void(0);" onclick="toggle_comment(this,'.$c_id.')');
				if($approve_fl)
					 $entry_nav[$this->page->lang_l('spam')]=$this->page->script_path.'?action=comments&amp;do=spam&amp;comment_id='.$c_id.'&amp;cc=1&amp;ip='.$value['ip'].Linker::buildReturnURL(true);

				$visitor=($url!=''? '<a class="rvts8" href="'.(strpos($url,'http')===false?'http://':'').$url.'"> '
					.Formatter::sth($value['visitor']).'</a>':'<span class="rvts8">'.Formatter::sth($value['visitor']).'</span>');
				if(!empty($value['email']))
					$visitor.='<br><span class="rvts8">'.$value['email'].' </span>';

				if($this->page->page_type==GUESTBOOK_PAGE)
					$inner=Formatter::substrUni(Formatter::stripTags($value['content']),0,30).' ...';
				elseif($this->page->page_type==CALENDAR_PAGE)
					$inner=$value['short_description'];
				else
					$inner=$value[($this->page->is_blogger?'title':'name')];

				if($this->page->is_lister || $this->page->page_type==PHOTOBLOG_PAGE)
				{
					 if($this->page->is_lister)
						 $img=$value['image1']!=''?$value['image1']:'';
					 else
						 $img='../'.($value['thumbnail_url']!=''?$value['thumbnail_url']:$value['image_url']);

					 if($img!='')
						 $inner='<img title="'.$inner.'" src="'.$img.'" style="width:46px;margin:2px;border-radius:3px;">';
				}

				$post='<a class="rvts12" style="text-decoration:none" href="'.$entry_url.'">'.$inner.'</a>';

				$iphost_nav=array();
				$visitor.=F_BR.(!empty($value['ip'])?Builder::ipLocator($value['ip']):'').F_BR;
				$visitor.=($value['ip']!=$value['host'] && !empty($value['ip']))?'<span class="rvts8">'.$value['host'].'</span>':'';
				$avatar=$this->commentAvatar($value,$value['visitor'],'right');
				$visitor='<div class="divTable"><div>'.$visitor.'</div><div>'.$avatar.'</div></div>';

				if(!empty($value['ip']) )
				{
					$ipb=array_search($value['ip'],$blocked_ips)!==false;
					$iphost_nav[$this->page->lang_l(($ipb?'unblock ip':'block ip'))]=$this->page->script_path.'?action=comments&amp;do='.($ipb?'unblockip':'blockip').'&amp;ip='.$value['ip'];
				}
				$approved='<span class="rvts8" id="status_'.$c_id.'">'.$this->page->lang_l($value['approved']==0?'unapproved':'approved').'</span>';

				if($rating>0)
					$entry.='<br>'.Builder::buildAdminRanking(1,$rating,'#FF0000','');

				$row_data=array($post,array($entry,$entry_nav),array($visitor,$iphost_nav),$approved);
				$table_data[]=$row_data;
			}
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$append);
		}
		else
		{
			$table_data[]=array('','<span class="rvts8">'.$this->page->lang_l('no comments posted').'</span>');
			$output=Builder::adminTable('',array(),$table_data,$append);
		}
		return $output;
	}

	public function edit_comment($entry_id,$full_access=false) //edit by admin
	{
		global $db;

		Session::intStart('private');
		$this->page->user->mGetLoggedUser($db,'');

		$data['comment_id']=intval($_REQUEST['comment_id']);
		$data['entry_id']=intval($entry_id);

		$field_names=$db->db_fieldnames($this->page->pg_pre.'comments');
		foreach($field_names as $v)
		  if(isset($_REQUEST[$v]))
		  {
				if($v=='comments')
					$data[$v]=CommentHandler::parseComment($_REQUEST[$v],$full_access,$this->page->user->isCurrUserLogged(),$this->page->canUseURL('comment'));
				else
					$data[$v]=Formatter::stripTags($_REQUEST[$v]);
		  }
		$db->query_update($this->page->pg_pre.'comments',$data,'comment_id = '.$data['comment_id']);
		$this->page->reindex_search($data['entry_id']);

		return $this->page->manage_comments();
	}

	public function toggle_comment($comment_id,$flag='toggle')
	{
		global $db;
		$data=$this->db_fetch_comment($comment_id);
		$result='0';
		if(!empty($data))
		{
			$update_needed=true;
			$was_approved=$data['approved'];
			if($flag=='approve')
				$update_needed=!$was_approved;
			elseif($flag=='unapprove')
				$update_needed=$was_approved;

			if($update_needed)
			{
				$entry_id=$data['entry_id'];
				$db->query('
					UPDATE '.$db->pre.$this->page->pg_pre.'comments
					SET approved = '.($was_approved?0:1).'
					WHERE comment_id = '.$comment_id);
				$this->db_update_comment_count($entry_id,($was_approved?'-':'+'));
				$this->page->reindex_search($entry_id);
				$result=$this->page->lang_l(($was_approved?'approve':'unapprove')).'|'.$this->page->lang_l($was_approved?'unapproved':'approved');
			}
		}
		if($flag=='toggle')
		{
			echo $result;
			exit;
		}
	}

	public function spam_comment($comment_id)
	{
		$this->page->blockedIpModule->ip_blocking();
		$this->del_comment($comment_id,true);
	}

	public function del_comment($comment_id,$access_all=false)
	{
		global $db;

		$rec=$this->db_fetch_comment($comment_id);
		if(isset($_REQUEST['cc']))
		{
			$this->page->user->mGetLoggedUser($db,'');
			if($this->page->user->isCurrUserLogged())
			{
				$del_c_q = 'DELETE FROM '.$db->pre.$this->page->pg_pre.'comments WHERE comment_id = ' . $comment_id;
				if (!$this->page->user->isAdmin() && !$access_all)
					$del_c_q .= ' AND uid = ' . (int) $this->page->user->getId();
				$db->query($del_c_q);
			}
			else
			{
				print 'error|cannot delete this';
				exit;
			}
		}
		if($rec['approved']==1) $this->db_update_comment_count($rec['entry_id'],'-');
		$this->page->reindex_search($rec['entry_id']);
		if(isset($_REQUEST['cc']) && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'=comments')===false)
		{
			print '1';
			exit;
		}
		Linker::checkReturnURL();
	}

	public function reply_comment($entry_id)
	{
		global $db;

		$ts=time();
		$data['comment_id']=$ts;
		$data['entry_id']=$entry_id;
		$data['date']=Date::buildMysqlTime($ts);
		$data['visitor']=$this->page->user->getUname();
		$data['email']=$this->page->user->getData('email');
		$data['approved']=1;

		$field_names=$db->db_fieldnames($this->page->pg_pre.'comments');
		foreach($field_names as $v)
			if(isset($_REQUEST[$v]))
				$data[$v]=Formatter::stripTags($_REQUEST[$v]);

		$data['ip']=Detector::getIP();
		$data['host']=(isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:'');
		$data['agent']=(isset($_SERVER['HTTP_USER_AGENT'])?Detector::defineOS($_SERVER['HTTP_USER_AGENT']):'');

		$db->query_insert($this->page->pg_pre.'comments',$data);
		$this->db_update_comment_count($entry_id);
		$this->page->reindex_search($entry_id);

		if(isset($_REQUEST['sendtouser']))
		{
			$data_parent=$this->db_fetch_comment(intval($_REQUEST['related_comment_id']));
			$entry_data=$this->page->db_fetch_entry($entry_id);
			$entry_url=$this->page->build_permalink($entry_data,false,'',true);

			$title=isset($entry_data['name'])?$entry_data['name']:$entry_data['title'];

			$body='<div style="padding: 20px 0 20px 50px">
						<b>'.$data['visitor'].':  </b>'.$data['comments'].'
					 </div>
					 <div style="padding: 20px 0 20px 100px">
						<b>'.$data_parent['visitor'].':  </b>'.$data_parent['comments'].'
					 </div>
					 <div style="padding: 20px 0 20px 50px;">
						<a href="'.$entry_url.'">'.$title.'</a>
					 </div>';
			$subject=$this->page->lang_l('comment reply');
			MailHandler::sendMailStat($db,
							$this->page->pg_id,
							$data_parent['email'],
							$this->page->pg_settings['from_email'],
							$body,$body,
							$subject,
							$this->page->pg_settings['page_encoding'],'','','');
		}
	}

	private function commentAvatar($data,$userName,$float='none')
	{
		global $db;
		if(!isset($data['avatar']))
			$data['avatar']='';
		$avatar=User::getAvatarFromData($data,$db,$userName,$this->page->site_base,$float);
		return $avatar;
	}

	private function comments_html($comments_records,$params='',$can_edit=false,$logged_user_id=null,$print_request=false)
	{
		$hide_commentlink=($this->page->pg_settings['comments_hidenotlogged'] && !Cookie::isAdmin() && !$this->page->user->userCookie());

		$output='';
		if(!empty($comments_records))
		{
			if($params=='')
			{
				$output.='<div class="blog_comments"><h4>'.Formatter::strToUpper($this->page->lang_l('comments'))."</h4>";
				$params_parsed=F_BR.'<p class="blog_comments_entry_head"><span class="rvts8">[ '.$this->page->lang_l('posted by').' '
						.'<b>%name%</b>, %date% ]</span></p>'
						.'<p class="blog_comments_entry_body" style="padding-left:10px"><span class="rvts8">%comment%</span></p>';
			}
			else
				$params_parsed=$params;
			$this->rearrange_comments($comments_records);
			$cmnt_prev_lvl = -1; //starting from -1, because the base level is 0
			foreach($comments_records as $cmnt_params => $cmnt)
			{
				list($cmnt_id,$cmnt_par_id,$cmnt_lvl) = explode(':',$cmnt_params);
	//todo check if there is problem to use params 1 and 2 in this list and if no - uset them instead
				$is_last_lvl_cmnt = $this->check_last_level_comment($comments_records,$cmnt_params);
				if($cmnt['approved']==1)
				{
					$is_user_allowed_to_del = $logged_user_id !== null && $logged_user_id == $cmnt['uid'];
					if($print_request)
						$is_user_allowed_to_del=false;
					$url=$cmnt['url'];
					$comments_value=str_replace(F_LF,F_BR,Formatter::sth2($cmnt['comments']));
					$date_value=$this->page->format_dateSql($cmnt['date']);
					$name_value=(empty($url))?Formatter::sth($cmnt['visitor']):'<a class="rvts12" href="'.(strpos($url,'http')===false? 'http://':'').Formatter::sth($url).'">'.Formatter::sth($cmnt['visitor']).'</a>';

					$avatar=$this->commentAvatar($cmnt,$cmnt['visitor']);
					$rating=isset($cmnt['rating']) && $cmnt['rating']>0?
									'<span class="ranking"><span class="ranking_result" rel="0" data="0"><span>'.$cmnt['rating'].'</span></span></span>'
									:'';

					$entry=str_replace(
						array('%date%','%comment%','%name%','%user:name%','%user:avatar%','%user:avatar:left%','%rating%'),
						array('<a class="rvts12" name="comment-'.$cmnt['comment_id'].'"></a>'.$date_value
							.($can_edit || $is_user_allowed_to_del ?'  <input type="button" class="ui ui_shandle_ic5" onclick="if(confirm(\''.$this->page->lang_l('del comment msg').'\')) deleteC(this,'.$cmnt['comment_id'].','.$cmnt['entry_id'].');" style="display:'.($is_last_lvl_cmnt ? 'inline':'none').';" >':'')
							,$comments_value,$name_value,$name_value,$avatar,str_replace(':none',':left',$avatar),$rating),
						$params_parsed);
					$closing_divs='';
					for($cmnt_prev_lvl;$cmnt_prev_lvl >= $cmnt_lvl; $cmnt_prev_lvl--)
						$closing_divs.='</div>';
					$cmnt_prev_lvl = $cmnt_lvl;
					$output.=$closing_divs.'<div class="blog_comments_entry level-'.$cmnt_lvl.'" '.($cmnt_lvl!=0?'style="margin-left:30px;"':'').' >'.$entry;
					if(!$hide_commentlink)
						$output.='<div class="reply_area" id="e'.$cmnt['entry_id'].'"><span class="rvts12 reply_button" id="i'.$cmnt['id'].'" style="display:block;float:left;border: none;cursor:pointer;">'.$this->page->lang_l('reply').'</span>'.F_LF.'</div>';

					$output.=F_BR.F_LF;
				}
			}
			for($cmnt_prev_lvl;$cmnt_prev_lvl>=0;$cmnt_prev_lvl--)
				$output.='</div>';
			if($params=='')
				$output.=F_BR."</div>";
		}

		return $output;
	}

	private function rearrange_comments(&$comments)
	{
		$tmp_cmnts = array();
		$this->get_children_comments($comments,$tmp_cmnts);

		$comments = $tmp_cmnts;
	}

	private function get_children_comments($comments,&$arr,$id=0,$level=0)
	{
		foreach($comments as $v)
		{
			if($v['parent_id'] == $id)
			{
				$arrKey=$v['id'].':'.$id.':'.$level;
				$arr[$arrKey] = $v;
				$this->get_children_comments($comments,$arr,$v['id'],$level+1);
			}
		}

		return $arr;
	}

	private function check_last_level_comment($comments,$this_cmnt_params)
	{
		list($curr_id,$curr_par_id,$curr_lvl) = explode(':',$this_cmnt_params);
		foreach($comments as $cmnt_params => $cmnt)
		{
			list($cmnt_id,$cmnt_par_id,$cmnt_lvl) = explode(':',$cmnt_params);
			if($cmnt_lvl > $curr_lvl && $cmnt_par_id == $curr_id)
				return false; //children found
		}
		return true; //no children found for this comment
	}

	protected function parseRating($comments,$entry_id,&$output)
	{
		$cnt=$votes=0;
		if(strpos($output,'%rating%')!==false)
		{
			foreach($comments as $v)
			{
				if($v['rating']>0)
				{
					$cnt+=$v['rating'];
					$votes++;
				}
			}

			$output=str_replace('%rating%',Builder::buildRating($votes,($cnt>0? round($cnt/$votes,1): 0),$entry_id),$output);
		}
	}

	public function display_comments($comments_on,$entry_id,$output,$can_edit=false,
			  $print_request=false,$comments_records=false,$is_view=false)
	{
		if(intval($entry_id)<1 || !$comments_on)
			return str_replace(array('%comments%','%commentsform%','%commentsformbody%'),'',$output);
		$cform=strpos($output,'%commentsform')!==false;
		$cform_hidden=false;
		if(strpos($output,'%commentsobject%')!==false)
		{
			$output=str_replace('%commentsobject%',$this->page->comments_object,$output);
			$this->page->comments_object='';
		}

		if($this->page->pg_settings['enable_comments'])
		{
			if($this->page->comments_object!='')
				$output.=$this->page->comments_object;
			if($comments_records===false)
				$comments_records=$this->db_fetch_comments($entry_id);

			$buffer=$this->comments_html($comments_records,$this->page->comments_params,$can_edit,$this->page->user->getId(),$print_request);

			if(strpos($output,'%commentsbody%')!==false)
			{
				$output=str_replace(array('%commentsbody%','%comments%'),array($buffer,''),$output);
				if(!$cform)
					$output.='%commentsform_hidden%'.F_BR;
			}
			elseif(strpos($output,'%comments%')!==false)
			{
				$output=Formatter::objDivReplacing('%comments%',$output);
				$output=str_replace('%comments%',$buffer,$output);
				if(!$cform)
				{
					$output.='%commentsform%'.F_BR;
					$cform=true;
					if(count($this->page->pg_settings['views'])>1 && $is_view)
						$cform_hidden=true;
				}
			}

			$output=str_replace(array('%commentsform%','%commentsform_hidden%','%commentsformbody%'),
					array($this->comment_form($entry_id,'',$cform_hidden),$this->comment_form($entry_id,'',true),
						$this->comment_form($entry_id,$this->page->commentsform_params))
					,$output);

			if(isset($this->page->pg_settings['rating']) && $this->page->pg_settings['rating'])
					$this->parseRating($comments_records,$entry_id,$output);

		}
		return $output;
	}

	protected function is_contentbuilder()
	{
		if(!isset($this->page->all_settings['contentbuilder']))
			return false;
		return $this->page->all_settings['contentbuilder'];
	}

	public function comment_form($entry_id,$params='',$hidden=false)
	{
		global $thispage_id,$db;

		$user_data=$this->page->user->mGetLoggedValues($this->page->rel_path,$db);
		$is_logged=isset($user_data['name']);
		if($this->page->pg_settings['comments_hidenotlogged'] && !$is_logged)
			return '';

		$visitor_v=isset($user_data['name']) && $user_data['name']?$user_data['name']:'';
		if($visitor_v=='' && isset($user_data['username']) && $user_data['username']!='')
			$visitor_v=$user_data['username'];
		$email_v=isset($user_data['email'])?$user_data['email']:'';

		$captcha='';
		$can_build_captcha=(!isset($thispage_id));
		if($can_build_captcha)
		{
			if(!$is_logged)
			{
				if($this->page->f->slidingCaptcha)
					$captcha.='<input type="hidden" name="code" value="">';
				elseif(!$this->page->f->reCaptcha)
					$captcha.='<input class="input1" type="text" id="comments_code_'.$entry_id.'" name="code" size="4" maxlength="4" value="" autocomplete="off">&nbsp;';
				$captcha.='<span class="captcha"></span><span id="blog_'.$entry_id.'_code" class="rvts12 frmhint"></span>';
			}
		}
		$submit_btn='<input class="input1" id="comments_submit_'.$entry_id.'" name="Post" type="submit"'.($this->page->pg_settings['mini_editor']&&!$this->is_contentbuilder()?' onclick="set_comment_value($(this)) "':'').'value=" '.$this->page->lang_l('submit_btn').' ">';

		$form_start='<form id="blog_'.$entry_id.'" name="blog" action="'.$this->page->script_path.'?'.$this->page->id_param.'='.($hidden?$entry_id:intval($entry_id)).$this->page->c_page_amp.'" method="post">';
		$form_end='
			<input id="par_id_fld_'.$entry_id.'" type="hidden" name="parent_id" value="0"><br>
			<span id="blog_'.$entry_id.'_er_error" class="rvts12 frmhint"></span>
			<input type="hidden" name="sign_id" value="comment">
			<input type="hidden" name="action" value="comment">
			</form>';

		$vis_value=sprintf($is_logged?$this->page->f->fmt_hidden:$this->fmt_input_com,'visitor',$visitor_v,$entry_id.'visitor').'<span class="rvts12 frmhint" id="blog_'.$entry_id.'_visitor"></span>';
		$email_value=sprintf(($this->page->pg_settings['comments_email_enabled']?($is_logged?$this->page->f->fmt_hidden:$this->fmt_input_com):$this->page->f->fmt_hidden),'email',$email_v,$entry_id.'email').'<span class="rvts12 frmhint" id="blog_'.$entry_id.'_email"></span>';
		$cols='cols="'.(isset($this->page->page_is_mobile) && $this->page->page_is_mobile?'35':'50').'"';

		if($this->page->pg_settings['mini_editor']&&!$this->is_contentbuilder())
		{
			$innova_def='';
			Editor::getEditor($this->page->pg_settings['ed_lang'],$this->page->rel_path,$this->page->pg_settings['rtl'],
			$this->page->pg_settings['ed_bg'],$innova_def,$this->page->innova_js,3,$this->page->pg_settings['tiny_lang']);
			if($this->page->pg_settings['forbid_urls'])
				 $innova_def=str_replace(',"LinkDialog"','',$innova_def);
			$editor_parsed=str_replace(array('oEdit1','htmlarea'),array('oEditC_'.$entry_id,'txtContentC_'.$entry_id),$innova_def);
			$this->page->innova_js=Editor::addGoogleFontsToInnova('',$this->page->innova_js);
			$comments_value='<textarea class="mceEditor comments_comments"  id="txtContentC_'.$entry_id.'" name="comments" rows="3" '.$cols.' style="overflow:hidden; width: 98%"></textarea>'.$editor_parsed;
		}
		else
			$comments_value='<textarea class="input1 comments_comments" name="comments" rows="3" '.$cols.' style="overflow:hidden; width: 98%"></textarea>'
			.CommentHandler::buildHintDiv($this->page->pg_settings['lang_l']);
		$comments_value.='<span class="rvts12 frmhint" id="blog_'.$entry_id.'_comments"></span><br>';
		$rating=isset($this->page->pg_settings['rating'])&&$this->page->pg_settings['rating']?'<span class="ranking">
			<input type="hidden" class="rating_value" name="rating" value="0">
			<span class="ranking_result" rel="0" data="3">
				<span>5</span>
			</span>
			</span><br>':'';

		if($params=='')
		{
			$output=$form_start;
			if($is_logged)
				$output.=$vis_value.$email_value;
			else
			{
				$output.=sprintf('<span class="rvts8">%s</span>',$this->page->lang_l('your name').$this->page->f->fmt_star).F_BR.$vis_value.F_BR;
				$output.=($this->page->pg_settings['comments_email_enabled']?sprintf('<span class="rvts8">%s</span>',$this->page->lang_l('email address').($this->page->pg_settings['comments_require_email']?$this->page->f->fmt_star:'')):'').F_BR.$email_value.F_BR;
			}
			$output.=sprintf('<span class="rvts8">%s</span>',$this->page->lang_l('comments').$this->page->f->fmt_star).F_BR.$comments_value;
			if($can_build_captcha && !$is_logged) $output.=sprintf('<span class="rvts8">%s</span>',$this->page->lang_l('verification code').$this->page->f->fmt_star)
				.F_BR.$captcha.F_BR;

			$output.='<input type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">';
			$output.=sprintf('<span class="rvts8">%s</span>',"<em>".$this->page->lang_l('comment note')."</em>").F_BR.$rating.
					  $submit_btn.$form_end;
		}
		else
		{
			$output=$form_start.$params.$form_end;
			$output=str_replace(array('%name%','%email%','%comment%','%captcha%','%submit%','%cancel%','%rating%'),
					  array($vis_value,$email_value,$comments_value,$captcha,$submit_btn,'',$rating), $output);
		}
		return '<div class="blog_comments_form rvps0" id="c'.$entry_id.'" style="margin-bottom:4px;'.($hidden?'display:none':'').'">'.$output.'</div>'
				.($hidden?'<span rel="f'.$entry_id.'" class="rvts12 reply_post_button"
					style="display:block;border:none;cursor:pointer;">'.$this->page->lang_l('reply').'</span>':'');
	}

	public function replace_comments_macros($src,$inside_blog_object)
	{
		if(strpos($src,'%commentsbody[')!==false)
		{
			$co=(strpos($src,'%COMMENTS_OBJECT(')!==false);
			$cform=(strpos($src,'%commentsform')!==false);
			$this->page->comments_params=Formatter::GFS($src,'%commentsbody[',']%'); // comments block
			$src=str_replace('%commentsbody['.$this->page->comments_params.']%',($co || $inside_blog_object)?'%commentsbody%':'',$src);

			$this->page->commentsform_params=Formatter::GFS($src,'%commentsformbody[',']%'); // comments form
			$src=str_replace('%commentsformbody['.$this->page->commentsform_params.']%',($co || $inside_blog_object)?'%commentsformbody%':'',$src);

			if($co)
			{
				$this->page->comments_object=Formatter::GFS($src,'%COMMENTS_OBJECT(',')%'); // comments form
				$src=str_replace('%COMMENTS_OBJECT('.$this->page->comments_object.')%',$inside_blog_object?'%commentsobject%':'',$src);
			}
			if(!$cform && !$this->page->page_type==GUESTBOOK_PAGE)
				$src.='%commentsform%';
		}
		return $src;
	}

	public function db_fetch_comments($entry_id,$where='',$limit='')
	{
		global $db;

		if(intval($entry_id)>0)
			$where=' co.entry_id = '.$entry_id.' '.($where!=''?' AND '.$where:'');

		$que='
			SELECT co.*,cau.* FROM '.$db->pre.$this->page->pg_pre.'comments co
			LEFT JOIN '.$db->pre.'ca_users cau ON cau.uid = co.uid '
			.($where!=''?' WHERE '.$where: '').'
			ORDER BY co.date ASC '.$limit;
		$records=$db->fetch_all_array($que);
		return $records;
	}

	public function db_fetch_comment($comment_id, $where='')
	{
		global $db;

		$records=$db->query_first('
			 SELECT *
			 FROM '.$db->pre.$this->page->pg_pre.'comments
			 WHERE comment_id = '.$comment_id.($where!=''?' AND '.$where: ''));
		return $records;
	}

	public function save_comment($entry_id,$full_access=false) //add new comment
	{
		global $db,$thispage_id;

		$output='';

		if(!Detector::check_cross_domain()){
			echo "This is illegal operation.";
			exit;
		}

		Session::intStart('private');
		$loggedUser = Cookie::isAdmin() || $this->page->user->userCookie();

		if(Validator::isAbleToBuildImg() && !$loggedUser && !User::isLogged('CAPTCHA_CODE') && !Captcha::isRecaptchaPosted() && !isset($thispage_id))
		{
			echo "This is illegal operation.";
			exit;
		}

		$user = $loggedUser ?$this->page->user->mGetLoggedUser($db):false;
		$uid = $user?$user['uid']:'null';
		$ccheck=isset($_POST['cc']) && $_POST['cc']=='1';
		$forbid_comment_urls = (!$this->page->canUseURL() || !$loggedUser);
		$ip=Detector::getIP();
		if($this->page->pg_settings['mini_editor']&&isset($_POST['comments'])&&strpos($_POST['comments'],'http')!==false)
			$_POST['comments']=str_replace(Linker::buildFullURL(),$this->page->rel_path,$_POST['comments']);
		$this->page->pg_settings['lang_uc']['your IP is blocked']=ucfirst($this->page->pg_settings['lang_u']['your IP is blocked']);
		$errors=Validator::valdiateCommentsForm('visitor','comments','email',$forbid_comment_urls,$this->page->pg_settings['comments_email_enabled'],$this->page->pg_settings['comments_require_email'],
																			$this->page->pg_settings['lang_uc'],($ip!='' && $this->page->blockedIpModule->db_is_ip_blocked($db,$ip)),$this->page->pg_settings['comments_mustbelogged'],true);

		if($ccheck)
		{
			$errors_output=implode('|',$errors);
			$useic=(!$this->page->f->uni && $this->page->pg_settings['page_encoding']!='iso-8859-1' && function_exists("iconv"));
			if($useic)
				$errors_output=iconv($this->page->pg_settings['page_encoding'],"utf-8",$errors_output);

			if(count($errors)>0)
				print '0'.$errors_output;
			elseif($this->page->pg_settings['comments_require_approval'])
				print '1|'.$this->page->lang_l('post waiting approval');
			else
				print '1';
			exit;
		}
		elseif(count($errors)>0)
		{
			foreach($_POST as $k=>$v)
			{
				if($k=='Post') continue;
				$data[$k]=Formatter::stripTags(trim($v));
			}
			$output.=implode(F_BR,$errors).F_BR.'<a class="rvts4" href="javascript:history.back();">back</a>';
			return $output;
		}
		else
		{
			$ts=time();
			$data['comment_id']=$ts;
			$data['date']=Date::buildMysqlTime($ts);
			$data['entry_id']=intval($entry_id);

			$field_names=$db->db_fieldnames($this->page->pg_pre.'comments');
			foreach($field_names as $k=>$v)
				if(isset($_REQUEST[$v]))
				{
					 if($v == 'comments'){
						 $data[$v] = CommentHandler::parseComment($_REQUEST[$v],$full_access,$loggedUser,$this->page->canUseURL('comment'),$this->page->pg_settings['mini_editor']);
						 if($this->page->pg_settings['mini_editor'])
							$data[$v] = Editor::replaceClasses($data[$v]);
					 }
					 else
						 $data[$v]=Formatter::stripTags($_REQUEST[$v]);
				}

			$data['ip']=Detector::getIP();
			$data['host']=(isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:'');
			$data['agent']=(isset($_SERVER['HTTP_USER_AGENT'])?Detector::defineOS($_SERVER['HTTP_USER_AGENT']):'');
			$data['approved']=($this->page->pg_settings['comments_require_approval']?0:1);
			$data['uid']= $uid;
			$db->query_insert($this->page->pg_pre.'comments',$data);
			if($data['approved']==1)
				$this->db_update_comment_count($entry_id);

			$entry_data=$this->page->db_fetch_entry($entry_id);
			$send_to_entry_author=(isset($entry_data['posted_by']) && $entry_data['posted_by']!=0 && $entry_data['posted_by']!=-1)?$entry_data['posted_by']:'';

			if($this->page->pg_settings['notify_comment'])
				$this->page->send_admin_notification('comments',
								$this->page->lang_l('comment'),
								$this->page->build_permalink($entry_data,true,'',true),
								array_merge($entry_data,$data,array('date'=>date('Y-m-d H:i:s',$ts))),
								$send_to_entry_author);
			$this->page->reindex_search($data['entry_id']);

			Linker::checkReturnURL();
			Linker::redirect($this->page->build_permalink($entry_data,$this->page->use_alt_plinks,'',true),false);
			exit;
		}
	}

	public function db_update_comment_count($entry_id,$sign='+',$field_name='comment_count')
	{
		global $db;
		$db->query('UPDATE '.$db->pre.$this->page->data_pre.$this->page->data_tablename.'
			SET '.$field_name.' = '.$field_name.' '.$sign.' 1
			WHERE '.$this->page->data_table_idfield.' = '.$entry_id);
	}

	public function add_comments_js(&$pg_src)
	{
		$js='';
		if($this->page->pg_settings['mini_editor'])
			$js.='
			function set_comment_value(th)
			{
				var $entry_id=th.attr("id").replace("comments_submit_",""), textareaId = "txtContentC_"+$entry_id, content="";
				if(typeof window["oEditC_"+$entry_id] !== "undefined")
					content=window["oEditC_"+$entry_id].getXHTMLBody().trim();
				else{
					content=window.tinymce.get(textareaId).getContent();
					post_id="txtContentC_post"+$entry_id;
					if($("#"+post_id).length==0)
						$("<textarea name=\'comments\' id=\'"+post_id+"\' style=\'position:absolute;top:-9999px;visibility:hidden\'>").insertAfter($("#"+textareaId));
					textareaId=post_id;
				}
				$("#"+textareaId).html(content!=""?content:"");
			}
			';
		$js.='
		function move_textarea_comment($entry_id,$target,th,reply_button)
		{
			var mini_editor=$target.children().find(".mceEditor");
			if(mini_editor.length)
			{
				if($("#editor_"+$entry_id).length)
					$("#editor_"+$entry_id).remove();
				$("<div id=\'editor_"+$entry_id+"\'></div>").insertAfter(mini_editor);
				if(typeof window["oEditC_"+$entry_id] !== "undefined")
					$target.children().find("table").remove();
				else{
					var textareaId = "txtContentC_"+$entry_id;
					window.tinymce.execCommand("mceRemoveEditor", false, textareaId);
				}
			}
			if(reply_button){
				if($target.closest(".comments_container").length>0) $target=$target.closest(".comments_container");
				$target.detach().appendTo(th.closest(".blog_comments_entry")).hide().animate({height: "toggle"});
			}else
				$target.detach().insertBefore(th).hide().animate({height: "toggle"});
			if(mini_editor.length)
			{
				if(typeof window["oEditC_"+$entry_id] !== "undefined")
					window["oEditC_"+$entry_id].REPLACE("txtContentC_"+$entry_id, "editor_"+$entry_id);
				else
					window.tinymce.execCommand("mceAddEditor", false, textareaId);
			}
		}
		$(document).bind("ready",function(){';

		if($this->page->f->captcha_size=='recaptcha')
		$js.='
		$(".comments_input").focus(function() {
			cp=$(this).parent().find(".captcha");
			if(!$(cp).find("#recaptcha_area").length){$(".recaptcha_nothad_incorrect_sol").replaceWith(\'<span class="captcha"></span>\');
			loadReCaptcha(cp);}
		});';

		$js.='
		//yt handling
		$(".og_scraper").each(function() {
		  var th=$(this);
			$(this).find(".og_ezg_title_href").click( function(e){
			  var url=$(this).attr("href"),src="";
			  var p=url.indexOf("watch?v=");
				if(p>-1) src="//www.youtube.com/embed/"+url.substr(p+8);
				else {var p=url.indexOf("vimeo.com/");if(p>-1) src="//player.vimeo.com/video/"+url.substr(p+10);}
				if(src!="") $(th).append(\'<iframe width="560" height="315" src="\'+src+\'" frameborder="0" allowfullscreen></iframe>\');e.preventDefault();$(this).unbind();});
		});

		if($(".comments_comments") != undefined)
		$(".comments_comments").autogrow().css("resize","none");
		$(".comment_tag_lbl").css("cursor","pointer");
		$(".reply_button").on("click",function(){
			var $entry_id=$(this).parent().attr("id").substr(1),$target=$(".blog_comments_form[id=\'c"+$entry_id+"\']");
			move_textarea_comment($entry_id,$target,$(this),true);
			$("#par_id_fld_"+$entry_id).val($(this).attr("id").substr(1));
			$target.children().find(".comments_comments").focus();
			$(this).closest(".post_wrap").children(".reply_post_button").show();
		});
		$(".reply_post_button").on("click",function(){
			var $entry_id=$(this).attr("rel").substr(1),$target=$(".blog_comments_form[id=\'c"+$entry_id+"\']");
			move_textarea_comment($entry_id,$target,$(this),false);
			$("#par_id_fld_"+$entry_id).val(0);
			$target.children().find(".comments_comments").focus();
			$(this).hide();
		});
		$(".comment_tag_lbl").on("click",function(){
			var $title=$(this).attr("title"),img=$title.indexOf("<img")!==-1;
			if(img)
			{
				var url=prompt("'.$this->page->lang_l('write url').'","");
				$title=(url===null || url=="")?"":"<img src=\'"+url+"\'>";
			}
			var $target=$(this).parent().siblings(".comments_comments");
			$target.insertAtCaret($title);
		});
	});';

		if(strpos($pg_src,'id="blog')!==false)
			$js.=str_replace('%ID%','blog',$this->page->f->frmvalidation);
		$pg_src=Builder::includeScript($js,$pg_src,array(),$this->page->rel_path);
	}

	public function update_commentrss($entry_id='')
	{
		$fl_flag=($this->page->page_type==BLOG_PAGE && $this->page->fl_studio_flag);
		$max=$this->page->pg_settings['max_items_in_rss']; $com_total=$this->page->db_count('comments', 'approved = 1');
		if(intval($entry_id>0))
			$comments_data=$this->db_fetch_comments($entry_id,' approved = 1');
		else
			$comments_data=$this->db_fetch_comments(null,' approved = 1',(($com_total>$max && $max!=0)? ' LIMIT 0, '.$max:''));
		$pub_date=(!empty($comments_data[0])? strtotime($comments_data[0]['date']): Date::tzone(time()));

		if($entry_id!='')
		{
			$entry_data=$this->page->db_fetch_entry($entry_id,$this->page->where_public);
			$permalink=$this->page->build_permalink($entry_data,false,'',true);
		}
		$rss_title=($entry_id==''? $this->page->lang_l('comments'):$this->page->lang_l('comments on').' '. str_replace('&#039;',"'",Formatter::sth2($entry_data['title'])));

		$rss_data=array();
		foreach($comments_data as $v)
		{
			if($v['approved']==1)
			{
				if($entry_id=='')
				{
					$entry_data=$this->page->db_fetch_entry($v['entry_id'],$this->page->where_public);
					$permalink=$this->page->build_permalink($entry_data,false,'',true);
				}
				$com_plink=$permalink.'#comment-'.$v['comment_id'];
				$posted_by=str_replace(array('&#039;','&'), array("'",'&amp;'), Formatter::sth2($v['visitor']));
				$title=($entry_id!=''? '': $this->page->lang_l('comments on').' '.str_replace('&','&amp;',Formatter::sth($entry_data['title'])).', ').$this->page->lang_l('posted by').' '.$posted_by;
				$description=str_replace(array(F_LF,'&','&quot;','&nbsp;','<'), array(F_BR,'&amp;','"',' ','&#60;'), Formatter::sth2($v['comments']));
				$description=preg_replace("'<[/!]*?[^<>]*?>'si"," ",$description);

				$rss_item=array('title'=>$title,'description'=>$description,'link'=>$com_plink,'guid'=>$com_plink,'pubDate'=>date('r',strtotime($v['date'])), 'dc:creator'=>$posted_by);
				$rss_data[]=$rss_item;
			}
		}
		$link=($entry_id!=''? $permalink: $this->page->full_script_path);
		$atom_link=($entry_id!=''? $this->page->build_permalink($entry_data,false,'rss',true): (!$this->page->use_alt_plinks? $this->page->full_script_path.'?action=commentsrss': str_replace($this->page->pg_name,'',$this->page->full_script_path).'comments/rss/'));
		$new_data=RSS::build($rss_data,$this->page->pg_settings['rss_settings'],$this->page->pg_settings['page_encoding'],$link,$pub_date,'xmlns:dc="http://purl.org/dc/elements/1.1/"',$fl_flag, $atom_link, $rss_title);
		header("Content-Type:text/xml; charset=".$this->page->pg_settings['page_encoding']); echo $new_data;
	}
}

class ScraperHandler
{
	private $page;

	public function __construct($pg)
	{
		if($pg instanceof LivePageClass)
			$this->page=$pg;
		return;
	}

	public function getScript()
	{
		return '
			function remUrlHandler(el){ //this is called from the html got from the ajax call
				$scraper = $(el).parents().find("div.og_ezg_scraper");
				if($scraper.attr("rel") != "")
				{
					$body = $("#"+$scraper.attr("rel")).contents().find("body");
					$hiddenTextArea = $scraper.parents("div.content_editor").children("textarea[name=\'content_x\']");
				}
				else
				{
					$body = $scraper.siblings(".comments_comments");
					$hiddenTextArea = $scraper.closest("form").children("textarea[name=\'content_x\']");
				}
				$body.removeClass("parsed");
				$hiddenTextArea.remove();
				$scraper.remove();
			};
			function updateTextArea(el,btn,data)
			{
				if(btn != ""){
					data = $(btn).closest("div.og_scraper").html();
					$(btn).siblings().removeClass("og_curr_btn");
					$(btn).addClass("og_curr_btn");
				}
				data = data.replace(/<!--close_btn_start-->.*<!--close_btn_end-->/g, "");
				data = data.replace(/<!--nav_area_start-->.*<!--nav_area_end-->/g, "");
				el.text(data);
			};
			$(document).ready(function(){
				function handleURL(el,act,targetType){
					var urlPattern = /(((http|ftp|https):\/\/|www\.)[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?)([\s]|&nbsp;|<\/div><div>)/;
					if(act == "paste")
						urlPattern = /((http|ftp|https):\/\/|www\.)[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/;
					$body = $(el);
					if(targetType == "innovaLive")
						$bodyData = $body.html();
					else if(targetType == "comment")
						$bodyData = $body.val();
					$url = $bodyData.match(urlPattern);
					if($url !== null && !$body.hasClass("parsed"))
					{
						$scraperDiv = $("<div>").addClass("og_ezg_scraper").attr("rel",$body.attr("rel"));
						if(targetType == "innovaLive")
						{
							$tarLoc = $("#"+$body.attr("rel"),parent.document).closest("table[name^=\'idArea\']");
							$tarLoc.append($scraperDiv.html("Loading ..."));
						}
						else if(targetType == "comment")
							$tarLoc = $scraperDiv.html("Loading ...").insertAfter($body);
						$n = new Date().getTime();
						$hiddenTextArea = $("<textarea>").attr({name: "content_x",style: "display:none;",id: "cnt_x"+$n});
						$body.addClass("parsed");
						$url=$url[1];
						console.log($url);
						if($url.indexOf("http") == -1) $url="http://"+$url;
						$encodedURL = encodeURIComponent($url);
						$.get("'.$this->page->script_path.'?action=scraper_data&url="+$encodedURL, function(data){
							data = data.replace(/<!--og_rel-->/g, "cnt_x"+$n);
							$scraperDiv.html(data);
							updateTextArea($hiddenTextArea,"",data)
							console.log(data);
							if(targetType == "innovaLive")
								$tarLoc.parents("div.content_editor").append($hiddenTextArea);
							else if(targetType == "comment")
								$tarLoc.closest("form").append($hiddenTextArea);
						});
					}
				};

				//prepare innova Live iframe bodies (leaving turn back way to the parent iframe element)
				$("iframe").each(function() {
					$(this).contents().find("body").attr("rel",$(this).attr("id"));
				});
				//prepare comment bodies (leaving turn back way to the parent iframe element)
				$(".comments_comments").each(function() {
					$(this).attr("rel","");
				});
				$("body", $("iframe").contents()).on("keyup", function(e) {
					el = this;
					handleURL(el,"keyup","innovaLive");
				});
				$("body", $("iframe").contents()).on("paste", function(e) {
					el = this;
					handleURL(el,"paste","innovaLive");
				});
				$(".comments_comments").on("keyup", function(e) {
					el = this;
					handleURL(el,"keyup", "comment");
				});
			});
			';
	}

	public function getCss()
	{
		return '
		.og_ezg_img_nav_btn:hover,.og_curr_btn{background: gray !important;}
		.og_ezg_img_nav_btn{border: 1px solid gray;float:left;cursor:pointer;width:7px;margin-left:9px;height:7px;}
		.og_ezg_close_btn{color:#ccc;font: bold 12px verdana;text-decoration: none;}
		.og_ezg_close_btn:hover{background: #949494;padding:0 2px;border-radius:1px;}
		';
	}
}

class header
{
	 public static function display_header($id)
	 {
		switch($id)
		{
		  case 404:	header("HTTP/1.0 404 Not Found");
		}
	 }

}

class Errors
{
	private $targetAction;
	private $targetPageId;

	private $f;

	public function __construct($pageId,$action)
	{
		global $f;
		$this->f=$f;
		$this->targetPageId=$pageId;
		$this->targetAction=$action;
	}

	public function setMsg($msg)
	{
		Session::setVarArr('error-'.$this->targetPageId.'-'.$this->targetAction,'',$msg);
		return true;
	}

	public function displayErrors($ajax_suspend=false)
	{
		if($ajax_suspend)
			return '';
		$res = '';
		foreach(Session::getVar('error-'.$this->targetPageId.'-'.$this->targetAction) as $err)
			$res .= $err.F_BR.F_LF;
		Session::unsetVar('error-'.$this->targetPageId.'-'.$this->targetAction); //clear errors
		return $res.F_LF;
	}

	function displayLast()
	{
		return ((Session::isSessionSet('last_err'))?Session::getVar('last_err'):'No previous errors');
	}

	public function hasErrors()
	{
		return Session::isSessionSet('error-'.$this->targetPageId.'-'.$this->targetAction)
				&&is_array(Session::getVar('error-'.$this->targetPageId.'-'.$this->targetAction))
				&&count(Session::getVar('error-'.$this->targetPageId.'-'.$this->targetAction)>0);
	}
}

class page_objects
{
	public $page;
	public $f;

	public function __construct($pg)
	{
		global $f;
		$this->f=$f;
		if($pg instanceof LivePageClass)
			$this->page=$pg;
	}
}

class page_rss extends page_objects
{
	 public function get_rss_category_where(&$where)
	 {
		  $cwhere='';
		  $category=Formatter::stripTags(isset($_GET['category'])?$_GET['category']:'');
		  if($category!='')
		  {
				$category=explode("|",Formatter::unEsc(urldecode($category)));
				if(count($category)>0)
				{
					 foreach($category as $v)
					 {
						  $category_id=$this->page->categoriesModule->get_categoryidbyname($v);
						  if($category_id!==false)
								$cwhere.=($cwhere==''?'':' OR ').'category='.$category_id;
					 }
				}
		  }
		  if($cwhere!='')
				$where.=($where==''?'':' AND ').' ( '.$cwhere.' ) ';
	 }
}

class page_admin_screens extends admin_screens
{
	public $page;
	public $f;

	public function __construct($pg)
	{
		global $f;
		$this->f=$f;
		if($pg instanceof LivePageClass)
			$this->page=$pg;
		elseif($pg instanceof PageClass)
			$this->page=$pg;
	}

	final public function screen_output($output)
	{
		$output=Builder::includeScript($this->page->page_scripts,$output,$this->page->page_dependencies,$this->page->rel_path);
		if($this->page->page_css!=='')
			 $output=Builder::includeCss($output,$this->page->page_css);
		$output=str_replace('%RTL%','',$output);
		print $output;
	}
}

?>

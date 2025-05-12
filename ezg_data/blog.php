<?php
/*
	blog.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2017 Image-line
*/
$GLOBALS['calling_page']='blog';
define('BLOGPODCAST_POSTS_FIELDS_COUNT',40);
define('CATEGORY_FIELDS_COUNT',11);
define('COMMENTS_FIELDS_COUNT',14);
define('TRACKBACKS_FIELDS_COUNT',12);

class BlogPageClass extends LivePageClass implements PageWithComments
{
	public $version='ezgenerator v4 - Blog Page 5.10.28 mysql';
	protected $action_id='';
	protected $is_blog;
 	protected $day_name;
 	protected $month_name;
 	protected $entry_status=array();
 	protected $edit_own_posts_only=false;
 	protected $ranking_enabled=true;
 	protected $media_types=array('asf','avi','wm','wma','wmd','wmv','mp3','wav','au','aif','aiff','mov','qt','3gp','m4a','mp4','m4v','mpg','mpeg','swf','flv','ogg','pdf');
	protected $entry_accessibility=array();
	protected $nav_labels=array();
	protected $innova_on_output=false;
	protected $date_params='';
	protected $datetime_params='';
	protected $comments_params='';
	protected $commentsform_params='';
	protected $comments_object='';
	protected $admin_actions=array('postentry','index','edit_entry','del_entry','pub_entry','unpub_entry','duplicate','comments'
		,'trackbacks','settings','categories','configpass','save_entry_simple','page_view','del_comment',
		'mail_entry','add_category_ajax','toggle_hidden','wp_import');
 	protected $use_alt_plinks=false;
 	protected $plink_format='';
 	protected $comments_feed_in_rss=true;
 	protected $date_used;
 	protected $order_by;
 	protected $order_type;
 	protected $where_pub;
 	protected $where_public;
	protected $where_archived;
	protected $where_archived_public;
 	protected $where_public_user;
 	protected $page_info='';
 	public $fl_studio_flag;
 	protected $db_domain_fname;
 	protected $page_lang;
	protected $trackbacks;
	protected $entries;
	protected $rss;
	public $write_access;
	public $av_object;

	public function __construct($pid,$pdir,$pname,$relpath,$settings)
	{
		parent::__construct($pid,$pdir,$pname,$relpath,$settings,$settings['page_type']=='blog'?BLOG_PAGE:PODCAST_PAGE);

		$this->is_blog=$this->pg_settings['page_type']=='blog';
		if($this->pg_settings['from_email']=='')
			$this->pg_settings['from_email']=$this->admin_email;
		$this->prepareView();

		if($this->canUseURL('comment'))
			$this->f->comments_allowed_tags['extra']=array_merge($this->f->comments_allowed_tags['extra'],array('<a>','<img>'));

		$this->month_name=$this->f->month_names;
		$this->day_name=$this->f->day_names;
		$this->trackbacks=new trackbacks($this);
		$this->entries=new entries($this);
		$this->rss=new blogrss($this);
		$this->av_object=new audio_video($this);

		$this->db_domain_fname=$this->rel_path.$this->pg_settings['page_type'].'/'.$this->pg_pre."db_domain.ezg.php"; //fl studio only
		$this->fl_studio_flag=file_exists($this->db_domain_fname);

		if(isset($_REQUEST['q']) && (!isset($_GET['action']) || $_GET['action']!='index'))
		{
			include_once($this->rel_path.'documents/search.php');
			if(!isset($_REQUEST['action']))
				exit;
		}

		$this->date_used=$this->pg_settings['offSettings']['orderBy']=='modified_date'?'modified_date':'creation_date';
		$this->order_by=$this->pg_settings['offSettings']['orderBy'];

		$this->order_type=($this->pg_settings['offSettings']['reversed']?'ASC':'DESC');
		$this->where_pub='publish_status="published" ';
		$this->where_public=$this->where_pub.' AND accessibility="public"';
		$this->where_archived ='( publish_status="published" OR publish_status="archived" ) ';
		$this->where_archived_public = $this->where_archived.' AND accessibility="public"';
		$timenow=Date::buildMysqlTime();
		$this->where_public_user='((publish_status="published") OR (publish_status="scheduled" AND (creation_date <= "'.$timenow.'" )))';

	 	if(isset($_REQUEST['showAll']) && intval($_REQUEST['showAll'])==1)
			$this->pg_settings['offSettings']['maxEntries']=0;
	}

	public function reindex_search($entry_id,$flag='add')
	{
		global $db;

		$p_params=CA::getPageParams($this->pg_id,$this->rel_path);
		$p_lang=array_search($p_params[16],$this->f->site_languages_a)+1;
		$hidden_insearch=$p_params[20]=='TRUE';
		if($hidden_insearch)
			return false;
		Search::checkDB($db);

		$data=$this->db_fetch_entry($entry_id);
		if($data===false || $data['publish_status']!='published' || $data['accessibility']!='public')
			$flag='del';

		if($flag=='add')
		{
			Meta::keywords_unique($data['keywords']);
			$content=$data['content'].'<junk>'.$data['title'].' '.$data['keywords'].' '.$data['excerpt'].' '
				.(!$this->is_blog? ' '.$data['subtitle'].' '.$data['author']:'').' '
				.basename($data['image_url']).' '.basename($data['mediafile_url']).'</junk>';
			$coms=$this->commentModule->db_fetch_comments($data['entry_id'],' co.approved=1');
			if(!empty($coms))
				foreach($coms as $v) $content.=' '.$v['visitor'].' '.$v['comments'];

			$parent=-1;
			$content.=' '.$this->categoriesModule->get_category_info($data['category'],'name',$parent);
			Search::reindexDBAdd($db,$this->pg_id,$data['entry_id'],$p_lang,
							$this->build_permalink($data,false,'',true),
							$data['title'],
							$content,
							$data['modified_date'],
							$data['keywords'],
							$data['category'],
							$this->page_type,'','',$data['creation_date']);
		}
		elseif($flag=='del')
			Search::reindexDBDel($db,'page_id='.$this->pg_id.' AND entry_id="'.$entry_id.'"');

		return 1;
	}

	public function get_keywords_html($keywords,$schema=false)
	{
		$tags_array=Meta::keywordsArray($keywords);
		$tags='';
		foreach($tags_array as $v)
			$tags.='&bull;&nbsp;<a class="rvts12" style="text-decoration:none" href="'.$this->build_permalink_tag($v).'">'.($schema?rich_snippets::wrap_prop("keywords",$v):$v).'</a>&nbsp;';
		return $tags;
	}

	public function build_permalink($data,$for_bmarks=false,$action='',$use_abs=false,$view='')
	{
		$return_url='';
		$requestView=$view; //backup
		$view=(isset($this->pg_settings['offSettings']['linked_to_view']) && $this->pg_settings['offSettings']['linked_to_view']!='')
			?$this->pg_settings['offSettings']['linked_to_view'].($this->use_alt_plinks?'':'&amp;')
			:$this->make_view();
		if($view=='')
			$view=$requestView; //in case no view is detected and there is some parsed
		//TODO: Maybe request view must have highest prior and these offSettings checks to be skipped when view is parsed
		if($view=='--root--')
			$view='';

		$title_for_url=Formatter::titleForLink($data['title']);
		$entry_id=$data['entry_id'];

		if($this->use_alt_plinks)
		{
			$parent=-1;
			if(trim($data['permalink'])!='')
				$pl_format=urldecode($data['permalink']).'/';
			else
			{
				$creation_date=$entry_id;
				if(isset($data[$this->date_used]))
					$creation_date=strtotime($data[$this->date_used]);
				$title_for_url=urlencode(Formatter::strToLower($data['title']));
				$pl_format=str_replace(
						  array('%year%','%monthnum%','%postname%','%category%'),
						  array(date('Y',$creation_date),date('m',$creation_date),$title_for_url,urlencode($this->categoriesModule->get_category_info($data['category'],'name',$parent))),
						  $this->plink_format);
			}
			$return_url=($use_abs?str_replace($this->pg_name,'',$this->script_path):'')
					.$pl_format //this must be whole piece for the search plinks to work!
					.($action!=''? $action.'/':'').($view!=''?$view."/":'');
		}
		else
		{
			if($for_bmarks)
				$url=($use_abs?$this->full_script_path:'').'?'.str_replace('&amp;','%26',$view).'entry_id='.$entry_id;
			else
			{
				$url=($use_abs?$this->full_script_path:$this->script_path).'?'.$view.($action!=''? 'action='.$action.'&amp;': '').'entry_id='.$entry_id;
				if($this->all_settings['plink_type']=='default')
					$url.='&amp;title='.$title_for_url;
			}
			$return_url=$url;
		}

		return Linker::removeURLMultiSlash($return_url);
	}
	public function build_permalink_tag($tag)
	{
		$view=$this->make_view();

		if($this->use_alt_plinks)
			return str_replace($this->pg_name,'',$this->script_path)."tag/".urlencode($tag)."/".($view!=''?$view."/":'');
		else
			return $this->script_path.'?'.$view.'tag='.urlencode($tag);
	}

	public function build_permalink_cat($category='',$absurls=false,$force_view=false,$action='')
	{
		$view=($force_view===false || $force_view=="")?$this->make_view():$force_view;
		if(isset($this->all_settings['catviewid']) && $this->all_settings['catviewid']!='0')
			$view=''; //prevent duplication and improper URLs

		if($this->use_alt_plinks)
		{
			$url=str_replace($this->pg_name,'',$this->script_path);
			$category=$this->categoriesModule->get_category_plinkname($category);
			return str_replace($this->pg_name,'',$url)."category/".($category==''? '': urlencode($category)."/").($view!=''?$view."/":'');
		}

		$url=$absurls?$this->full_script_path:$this->script_path;
		if($force_view!==false)
			$view=$view!==''?$view.'&amp;':'';
		return $url.'?'.$view.'category='.($category==''? '': urlencode($category));
	}

	public function build_permalink_arch($mon,$year)
	{
		$view=$this->make_view();

		if($this->use_alt_plinks)
			return str_replace($this->pg_name,'',$this->script_path).$year."/".$mon."/";

		return $this->full_script_path.'?'.$view.'mon='.$mon.'&amp;year='.$year;
	}

	// ------------------------------- MySQL functions -------------------------
	public function db_count($table, $where='')
	{
		global $db;

		$count_raw=$db->fetch_all_array('
			SELECT COUNT(*)
			FROM '.($table!='site_search_index'? $db->pre.$this->pg_pre:$db->pre).$table.($where!=''? '
			WHERE '.$where:''));
		$count=isset($count_raw[0])?$count_raw[0]['COUNT(*)']:0;
		return $count;
	}

	public function db_fetch_entry($entry_id,$where='')
	{
		global $db;

		$records=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->pg_pre.'posts
			WHERE entry_id='.intval($entry_id).($where!=''?' AND '.$where: ''));
		return $records;//false if not found
	}

	public function db_fetch_entries($entry_ids,$where='')
	{
		global $db;

		$entry_ids=array_unique($entry_ids);
		$where=$where==''?'(':$where.' AND (';
		foreach($entry_ids as $v)
			$where.=' entry_id='.$v.' OR ';
		$where.=' entry_id=1)';

		$records=array();
		$records_temp=$db->fetch_all_array('SELECT *	FROM '.$db->pre.$this->pg_pre.'posts WHERE '.$where);
		foreach($records_temp as $v)
			$records[$v['entry_id']]=$v;
		return $records;
	}
	protected function db_scheduled_check()
	{
		global $db;

		$today=time();
		$t_mon=date('n',$today); $t_day=date('j',$today); $t_year=date('Y',$today);
		$t_hour=date('H',$today); $t_min=date('i',$today); $t_sec=date('s',$today);
		$db->query('UPDATE '.$db->pre.$this->pg_pre.'posts SET publish_status =
			CASE
				WHEN (publish_status="scheduled" AND (creation_date <= "'.$t_year.'-'.$t_mon.'-'.$t_day.' '.$t_hour.':'.$t_min.':'.$t_sec.'")) THEN "published"
				WHEN (publish_status="published" AND (unpublished_date <= "'.$t_year.'-'.$t_mon.'-'.$t_day.' '.$t_hour.':'.$t_min.':'.$t_sec.'") AND (unpublished_date > 0)) THEN "unpublished"
				ELSE publish_status END');
	}

	public function db_fetch_entries_slice($total_count,$max,$where='',$order='DESC',$orderby='',$use_weight=true)
	{
		global $db;

		if($orderby=='')
			$orderby=$this->order_by;
		if($use_weight)
			$orderby=' weight ASC, '.$orderby;
		$start=($this->c_page-1)*$max;
		$records=$db->fetch_all_array('
			SELECT *	FROM ' .$db->pre.$this->pg_pre.'posts'.($where!=''? '
			WHERE '.$where:'').'
			ORDER BY '.$orderby.' '.$order.' ' .(($total_count>$max && $max!=0)? 'LIMIT '.$start.', '.$max.'':''));
		return $records;
	}

	public function db_fetch_settings()
	{
		parent::db_fetch_settings();
		if(!isset($this->all_settings['weight_forusers']))
			$this->all_settings['weight_forusers']='0';
		if(!isset($this->all_settings['concept_mode']))
			$this->all_settings['concept_mode']='0';
		if(!isset($this->all_settings['disable_trackbacks']))
			$this->all_settings['disable_trackbacks']='1';
		if(!isset($this->all_settings['mbox_grouped']))
			$this->all_settings['mbox_grouped']='1';
		if(!isset($this->all_settings['unique_titles']))
			$this->all_settings['unique_titles']='0';
		if(!isset($this->all_settings['rem_kw_exc_from_search']))
			$this->all_settings['rem_kw_exc_from_search']='1';
		if(!isset($this->all_settings['replace_keywords']))
			$this->all_settings['replace_keywords']='0';
		if(!isset($this->all_settings['youtube_size']))
			$this->all_settings['youtube_size']='480x385';
		if(!isset($this->all_settings['viewid']))
			$this->all_settings['viewid']='0';
		if(!isset($this->all_settings['catviewid']))
			$this->all_settings['catviewid']='0';
		if(!isset($this->all_settings['tagviewid']))
			$this->all_settings['tagviewid']='0';
		if(!isset($this->all_settings['searchviewid']))
			$this->all_settings['searchviewid']='0';
		if(!isset($this->all_settings['plink_type']))
			$this->all_settings['plink_type']='default';
		if(!isset($this->all_settings['error_404_page']))
			$this->all_settings['error_404_page']='';
		if(!isset($this->all_settings['blacklist']))
			$this->all_settings['blacklist']='';                
		if(!isset($this->all_settings['og_desc']))
			$this->all_settings['og_desc']='1';
		if(!isset($this->all_settings['hideHidden']))
			$this->all_settings['hideHidden']=1;
		if(!isset($this->all_settings['contentbuilder']))
			$this->all_settings['contentbuilder']='0';

		$this->plink_format='';
		$this->use_alt_plinks=$this->all_settings['plink_type']=='month_name' || $this->all_settings['plink_type']=='title';

		if($this->use_alt_plinks)
		{
			$this->full_script_path=Linker::buildSelfURL($this->pg_name,true);
		   $this->plink_format=$this->all_settings['plink_type']=='month_name'?'/%year%/%monthnum%/%postname%/':'/%postname%/';
		}
		$this->script_path=($this->use_alt_plinks? $this->full_script_path: $this->script_path); //used in user's screen
	}

	public function db_search_in_entries($search_string,$flag='user',$posted_by='',$category_id='',$cat_name='')
	{
		global $db;

		$result=array();
		if($flag=='user')
			$result=process_search($this->pg_id,$cat_name,$this->rel_path,$this->all_settings['rem_kw_exc_from_search']==1,$this->date_used);
		else
		{
			$search_string=str_replace('\"','"',$search_string);
			$exact=($search_string[0]=='"' && $search_string[strlen($search_string)-1]=='"');
			if($exact)
				$search_string=Formatter::GFS($search_string,'"','"');
			$sa=explode(" ",$search_string);
			$posted_by_id=User::getUserID($posted_by,$this->rel_path);

			foreach($sa as $ss)
			{
				$entries_records=$db->fetch_all_array('SELECT *, creation_date FROM '.$db->pre.$this->pg_pre.'posts
					 WHERE ('.($flag=='admin'?1:0).' OR '.$this->where_public.')
					 AND (title LIKE "%'.$ss.'%" OR excerpt LIKE "%'.$ss.'%"
					 OR content LIKE "%'.$ss.'%" OR keywords LIKE "%'.$ss.'%" '.(!$this->is_blog? '
					 OR subtitle LIKE "%'.$ss.'%" OR author LIKE "%'.$ss.'%"':'').')'. ($posted_by!=''? '
					 AND posted_by='.$posted_by_id: '').($category_id!=''? ' AND category='.$category_id: ''));
				$comments_records=$db->fetch_all_array('SELECT t1.* FROM '.$db->pre.$this->pg_pre.'posts AS t1, '.$db->pre.$this->pg_pre.'comments AS t2
					 WHERE ('.($flag=='admin'?1:0).' OR t1.publish_status="published" AND t1.accessibility="public")
					 AND t2.approved=1 AND (t2.visitor LIKE "%'.$ss.'%" OR t2.comments LIKE "%'.$ss.'%"
					 OR t2.url LIKE "%'.$ss.'%") AND t1.entry_id=t2.entry_id'.($posted_by!=''? '
					 AND t1.posted_by='.$posted_by_id: ''));
				$buffer=array_merge($entries_records,$comments_records);
				$result=array_merge($buffer,$result);
			}
			$result=Filter::multiUnique($result);
		}
		return $result;
	}

	protected function check_data()
	{
		global $db;

		$tb_a=$db->get_tables($this->pg_pre);
		$result=false;
		if(empty($tb_a))
		{
			include_once('data.php');
			Search::checkDB($db);
			$result=create_blogdb($db,$this->pg_id,$this->pg_settings['page_type'],$this->rel_path,$this->f->db_folder,$this->pg_pre);
		}

		$this->db_fetch_settings();

		if(!$result &&
			((!isset($this->all_settings['POSTS_FIELDS_COUNT']) || $this->all_settings['POSTS_FIELDS_COUNT']!=BLOGPODCAST_POSTS_FIELDS_COUNT) ||
			(!isset($this->all_settings['CATEGORIES_FIELDS_COUNT']) || $this->all_settings['CATEGORIES_FIELDS_COUNT']!=CATEGORY_FIELDS_COUNT) ||
			(!isset($this->all_settings['COMMENTS_FIELDS_COUNT']) || $this->all_settings['COMMENTS_FIELDS_COUNT']!=COMMENTS_FIELDS_COUNT)	||
			(!isset($this->all_settings['TRACKBACKS_FIELDS_COUNT']) || $this->all_settings['TRACKBACKS_FIELDS_COUNT']!=TRACKBACKS_FIELDS_COUNT))
		)
		{
			include_once('data.php');
			$result=create_blogdb($db,$this->pg_id,$this->pg_settings['page_type'],$this->rel_path,$this->f->db_folder,$this->pg_pre);
		}

		if($result)
		{
			 $this->db_insert_settings(array(
				 'POSTS_FIELDS_COUNT'=>BLOGPODCAST_POSTS_FIELDS_COUNT,
				 'CATEGORIES_FIELDS_COUNT'=>CATEGORY_FIELDS_COUNT,
				 'COMMENTS_FIELDS_COUNT'=>COMMENTS_FIELDS_COUNT,
				 'TRACKBACKS_FIELDS_COUNT'=>TRACKBACKS_FIELDS_COUNT));
		}

		$this->categoriesModule->build_categories_list();
	}

	protected function update_language_set($action,$admin_actions,$user_actions)
	{
		parent::update_language_set($action,$admin_actions,$user_actions);

		$this->nav_labels=array('first'=>$this->lang_l('first'),'prev'=>$this->lang_l('prev'),'next'=>$this->lang_l('next'),'last'=>$this->lang_l('last'),'home'=>$this->lang_l('home'), 'load more'=>$this->lang_l('load more'));
		$this->entry_status=array('unpublished'=>$this->lang_l('unpublished'),'published'=>$this->lang_l('published'),
			 'pending'=>$this->lang_l('pending preview'),'scheduled'=>$this->lang_l('scheduled'),
			 'template'=>$this->lang_l('template'),'archived'=>$this->lang_l('archived'));
		$this->entry_accessibility=array('hidden'=>$this->lang_l('hidden'),'public'=>$this->lang_l('public'));
	}

	protected function get_scraper($url)
	{
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL,'http://miro.image-line.com/scraper/scraper.php?url='.urlencode($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

		$data=curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	protected function handle_download()
	{
		global $db;

		$id=intval($_REQUEST['entry_id']);
		$data=$db->query_first('SELECT download_count,mediafile_url FROM '.$db->pre.$this->pg_pre.'posts WHERE entry_id='.$id);
		if($data!==false)
		{
		  $db->query('UPDATE '.$db->pre.$this->pg_pre.'posts SET download_count=download_count + 1 WHERE entry_id='.$id);
		  $mf=Formatter::sth($data['mediafile_url']);
		  output_generator::downloadFile($this->rel_path.$mf);
		}
		$this->setState('handle_download');
		exit;
	}

	protected function handle_autopost()
	{
		//as this is release version, autopost is blocked (security not ready yet)
		/*
		$username=Formatter::stripTags($_POST['username']);
		$by_email=Formatter::stripTags($_POST['useremail']);

		$logged_user=User::getUser($username, $this->rel_path, $by_email);
		if(isset($_POST['save_entry']))
			$res=$this->entries->save_entry($logged_user,true);
			echo $res;
		exit;
		*/
	}

	protected function handle_fvals()
	{
		$fid=isset($_REQUEST['fid'])&&$_REQUEST['fid']!=''?Formatter::stripTags($_REQUEST['fid']):'';
		if($fid=='keywords')
		{
			$tc=new tags_cloud($this);
			$kwds = $tc->get_tags();
			$response = array();
			foreach($kwds as $entry)
				if($entry['keywords']!='')
					$response =array_merge($response,explode(',',$entry['keywords']));
			$response=array_unique(array_map('trim',$response));
			sort($response);
			$response=implode('#',$response);
			echo $response;
		}

		exit;
	}
	protected function uploadFile($input_file,$path)
	{

		$array_upload=array();
		$errors=array(0=>'There is no error, the file uploaded with success',
					1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini',
					2=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
					3=>'The uploaded file was only partially uploaded',
					4=>'No file was uploaded',
					6=>'Missing a temporary folder');
		if(isset($_FILES[$input_file]))
		{
			if(!empty($_FILES[$input_file]['name']))
			{
				$fname=basename($_FILES[$input_file]['name']);
				if(in_array(substr(strtolower($fname),strrpos($fname,'.')+1),array('xml')))
				{
					clearstatcache();
					if(file_exists($path.$fname)){
						$array_upload['error']='File exists';
						return $array_upload;
					}
					if(!file_exists($path))
						mkdir($path,0755);
					if(move_uploaded_file($_FILES[$input_file]['tmp_name'], $path.$fname)){
						$array_upload['success_upload']=$path.$fname;
						return $array_upload;
					}
				}
				else
					$array_upload['error']='File type not supported';

				if(0!=$_FILES[$input_file]['error'])
				{
					$errn=$_FILES[$input_file]['error'];
					$array_upload['error']=$errors[$errn];
				}
			}
			else{
				$array_upload['error']='Empty file name';
			}
		}
		else{
			$array_upload['error']='No file to upload';
		}
		return $array_upload;
	}
	public function process()
	{
		global $db;

		$this->action_id=(isset($_REQUEST['action']))? Formatter::stripTags($_REQUEST['action']):'frontpage';
		$this->write_access=false;

		if($this->action_id=="logout")
		{
			Session::intStart('private');
			Session::unsetSession();
			$this->action_id='frontpage';
		}

		if($this->action_id=="version")
			$this->version();

		$this->init_db();

		if($this->action_id=="autopost")
		{
			$this->action_id='frontpage';
			$this->handle_autopost();
		}
		if($this->action_id=="download")
			$this->handle_download();

		if(isset($_REQUEST['most_visited']))
		{
			$limit=intval($_REQUEST['most_visited']);
			$bo= new  blog_output($this);
			$mv=$bo->db_mostVisited(' LIMIT 0, '.$limit.' ','');
			echo $this->SidebarBuilder->entries_sidebar($mv,
							'<p><span class="mv_date">%date% </span><a target="_top" href="%item_url%" class="rvts4">%title%</a></p>',
							false,$this->category_id,'entries_mostvisited');
			exit;
		}
		elseif(isset($_REQUEST['recent_entries']))
		{
			$bo= new  blog_output($this);
			$entries=$bo->get_recententries($this->pg_settings['offSettings']['maxRecent']);
			echo $this->SidebarBuilder->entries_sidebar($entries,
							'<a class="rvts4" href="'.$this->full_script_path.'#%title_bookmark%">%title%</a>',
							false,$this->category_id,'entries_recent');
			exit;
		}
		if(isset($_REQUEST['tags_cloud']))
		{
			echo $this->all_settings['tags_cloud'];
			exit;
		}

		$this->update_language_set($this->action_id,$this->admin_actions,array('frontpage','trackback','rss','ranking','preview','logout','download','captcha'));
		if($this->action_id=='fvalues')
			$this->handle_fvals();
		if(isset($_REQUEST['sort']) || isset($_REQUEST['toggle']))
			$this->action_id='addsorting';
		if($this->action_id=="frontpage")
			$this->db_scheduled_check();
		$this->page_info=CA::getPageParams($this->pg_id,$this->rel_path);

		if(isset($_REQUEST['category_vlist']) || isset($_REQUEST['category_hlist']))
			 $this->categoriesModule->ext_category_list();


		if($this->action_id=="miniview" || $this->action_id=="frontpage" || $this->action_id=="trackback" || $this->action_id=="rss" || $this->action_id=="commentsrss"
			|| ($this->action_id=="ranking")
			|| $this->action_id=='page_view' || $this->action_id=='comment' || $this->action_id=="entrypreview")
		{
			$bo= new  blog_output($this);
			$bo->handle_screen();
		}
		else
		{
			Session::intStart('private');
			$this->write_access=$is_admin_logged=$this->user->isEZGAdminLogged();
			if(!$is_admin_logged)
			{
				if($this->user->userCookie())
					$logged_user=$this->user->getUserCookie();
				if(!$this->user->userCookie() || User::mhasWriteAccess($logged_user,$this->page_info,$db)==false)
					$this->write_access=false;
				else
					$this->write_access=true;
			}
			if($this->action_id=='timeline_data')
			{
				$tl= new timeline($this);
				$tl->get_timeline_data();
				exit;
			}
			elseif($this->action_id=='timeline')
			{
				$tl= new timeline($this);
				$tl->show_timeline($this->write_access);
				exit;
			}
			elseif($this->action_id=='wp_import')
			{
				if(isset($_REQUEST['wp_import']))
				{
					$uploaddir=$this->rel_path.'innovaeditor/assets/admin/';
					$upload_array=$this->uploadFile('file',$uploaddir);
					if(isset($upload_array['success_upload']))
					{
						include_once('wp_import.php');
						$wpi=new WP_import($this);
						$wpi->import($upload_array['success_upload']);
					}
					else if(isset($upload_array['error']))
						echo json_encode('Error: '.$upload_array['error']);
				}
				exit;
			}
			if($this->action_id=='add_category_ajax')
				$this->categoriesModule->add_category_ajax();
			if($this->action_id=='scraper_data')
			{
				if(isset($_REQUEST['url']))
					echo $this->get_scraper($_REQUEST['url']);
				exit;
			}

			if($this->action_id=='del_comment' && $this->write_access && isset($_REQUEST['comment_id'])) //on frontpage
			{
				$comment_id=intval($_REQUEST['comment_id']);
				$this->commentModule->del_comment($comment_id);
			}

			if(!$this->write_access)
			{
				Linker::redirect($this->rel_path."documents/centraladmin.php?pageid=".$this->pg_id."&indexflag=index",false);
				exit;
			}
			if($this->action_id=='updatetl')
			{
				$tc=new tags_cloud($this);
				$tc->update_tags();
			}
			if($this->action_id=='addsorting')
				$this->categoriesModule->sort_category();

			if(in_array($this->action_id,$this->admin_actions))
			{
				$logged_user=$this->user->mGetLoggedUser($db,$this->admin_email);
				if($logged_user===false)
					exit();
				$this->edit_own_posts_only=$this->user->isAdmin()?false:User::userEditOwn($db,$this->user->getId(),$this->page_info);

				$access_all_flag=($this->edit_own_posts_only==false);  // when logged as limited user
				$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
				if(($do=='blockip' || $do=='unblockip') && $access_all_flag)
					$this->blockedIpModule->ip_blocking();

				if(isset($_POST['save_entry_simple']))
				{
					$this->entries->save_entry_simple();
					Linker::checkReturnURL();
				}
				elseif(in_array($this->action_id,array('postentry','edit_entry','toggle_hidden')) || isset($_POST['save_entry']) || isset($_POST['add_category']))
				{
					$edit_entry = new edit_entry_screen($this);
					$edit_entry->handle_screen();
				}
				elseif($this->action_id=='comments')
				{
					$manage_comments = new manage_comments_screen($this);
					$manage_comments->handle_screen();
				}
				elseif($this->action_id=='trackbacks')
				{
					$manage_trackbacks = new manage_trackbacks_screen($this);
					$manage_trackbacks->handle_screen();
				}
				elseif($this->action_id=="settings" && $access_all_flag)
				{
					$manage_settings = new manage_settings_screen($this);
					$manage_settings->handle_screen();
				}
				elseif($this->action_id=='categories' && $access_all_flag)
				{
					$manage_categories = new manage_categories_screen($this);
					$manage_categories->handle_screen();
				}
				else
				{
					$manage_entries = new manage_entries_screen($this);
					$manage_entries->handle_screen();
				}
			}
		}
		exit;
	}
}

class entries extends page_objects
{
	public function save_entry($autopost=false)
	{
		global $db;

		if(!isset($_POST['publish_status']))
			$_POST['publish_status']='published';
		if(!isset($_POST['accessibility']))
			$_POST['accessibility']='public';
		if(isset($_POST['save_simple']))
			$_POST['allow_comments']='true';

		$new_post=!isset($_POST['entry_id']);

		if(($new_post || isset($_POST['editing_entry'])) && $this->page->all_settings['unique_titles']==1)
		{
			$whr ='';
			$title_changed=false;
			if(isset($_POST['editing_entry']))
			{
				$currTitle=$db->fetch_all_array('SELECT title FROM '.$db->pre.$this->page->pg_pre.'posts WHERE entry_id="'.(int)$_POST['editing_entry'].'"');
				if(count($currTitle)>1)
				{
					unset($_REQUEST['r']);
					return '<div class="a_n a_listing">
								'.$this->page->lang_l('Posts table is messed! Contact support!').' <br /> <a href="javascript:history.go(-1);">'.$this->page->lang_l('back').'</a>
							  </div>';
				}
				elseif(count($currTitle)==1)
				{
					$title_changed=$currTitle[0]['title']!=Formatter::stripTags($_POST['title']);
					$whr=' AND entry_id != "'.(int) $_POST['editing_entry'].'"';
				}
			}
			if($new_post || $title_changed)
			{
				$sameTitlePosts=$db->fetch_all_array('
					SELECT title
					FROM '.$db->pre.$this->page->pg_pre.'posts
					WHERE title="'.addslashes(urldecode($_POST['title'])).'"'.$whr);
				if(count($sameTitlePosts)>0)
				{
					unset($_REQUEST['r']); //otherwise editing entry won't show the error
					return '<div class="a_n a_listing">'.$this->page->lang_l('Title already exists!').' <br /> <a href="javascript:history.go(-1);">'.$this->page->lang_l('back').'</a></div>';
				}
			}
		}

		$field_names=$db->db_fieldnames($this->page->pg_pre.'posts');
		foreach($field_names as $k=>$v)
		{
			if(isset($_POST[$v]))
			{
				//when extra url handler is used
				if($v == 'content' && $this->page->canUseURL() && isset($_POST['content_x']) && $_POST['content_x'] != '')
					$data['content']=$_POST['content'].$_POST['content_x'];
				else if($v == 'category')
					$data[$v] = intval(trim($_POST[$v]));
				else
					$data[$v]=trim($_POST[$v]);
			}
			elseif(in_array($v,array('subtitle','pinged_url','free_field')))
				$data[$v]='';
		}

		if(isset($_REQUEST['dt64']))
			$data['content']=base64_decode($_REQUEST['dt64']);
		elseif(isset($_REQUEST['dt']))
			$data['content']=$_REQUEST['dt'];

		if($this->page->all_settings['contentbuilder']){
			$user=$this->page->user->mGetLoggedUser($db);
			$data['content']=Editor::replaceData64image_contentBuilder($data['content'],$this->page->rel_path,$user['username']);
		}

		if(!$this->f->tiny)
			Formatter::cleanWordInput($data['content']);

		//grab ping url here (trackback)
		if(isset($_POST['Ping_urls']) && ($_POST['publish_status']=='template' || (!$new_post && $_POST['entry_id']=='0')))
			$data['pinged_url']=Formatter::stripTags($_POST['Ping_urls']);
		$timestamp=time();
		$data['entry_id']=isset($_POST['entry_id'])? intval($_POST['entry_id']):$timestamp;

		$as_template=$_POST['publish_status']=='template' || $data['entry_id']=='0';
		if($as_template)
		{
			$_POST['entry_id']='0';
			$data['entry_id']=0;
		}

		if(!$this->page->is_blog)
		{
			$data['mediafile_url']=stripslashes($_POST['mediafile_url']);
			$data['duration']=implode(':',array($_POST['Hour'],$_POST['Min'],$_POST['Sec']));
			$data['block']=(isset($_POST['block'])?'yes':"no");
			$data['allow_downloads']=(isset($_POST['allow_downloads'])?1:0);
		}
		$data['allow_comments']=(isset($_POST['allow_comments'])? $_POST['allow_comments']:'false');
		$data['allow_pings']=(isset($_POST['allow_pings'])? $_POST['allow_pings']:'false');
		$data['excerpt']=(isset($_POST['excerpt'])?$_POST['excerpt']:'');
		$data['weight']=(isset($_POST['weight'])?intval($_POST['weight']):100);
		$data['free_field']=(isset($_POST['free_field'])?$_POST['free_field']:'');
		$data['creation_date']=Date::buildMysqlTime(Date::pareseInputDate('creation_date',$this->page->pg_settings['time_format'],$this->page->month_name));

		if(isset($_POST['unpublish_on']))
			$data['unpublished_date']=Date::buildMysqlTime(Date::pareseInputDate('unpublished_date',$this->page->pg_settings['time_format'],$this->page->month_name));
		else
			unset($data['unpublished_date']);

		$sm=ini_get('safe_mode');
		if(!$this->page->is_blog && !$sm && function_exists('set_time_limit') && strpos(ini_get('disable_functions'),'set_time_limit')===false)
			set_time_limit(30);
		if(!$new_post)
			$exist_rec=$this->page->db_fetch_entry(intval($_POST['entry_id']));
		if(isset($exist_rec) && $exist_rec!==false && $exist_rec['posted_by']==0 || $new_post)
			$data['posted_by']=$this->page->user->getId();

		if(!$this->page->is_blog)
		{
			if(!empty($_POST['mediafile_url']))
			{
				$data['mediafile_url']=str_replace('../','',$data['mediafile_url']);
				$data['mediafile_size']=(strpos($data['mediafile_url'],'http://')!==false)?0:filesize($this->page->rel_path.$data['mediafile_url']);
			}
			else
				$data['mediafile_url']=$_POST['External_Media'];
		}
		else
			$data['mediafile_url']=isset($_POST['External_Media'])?$_POST['External_Media']:'';

		$data['image_url']=isset($_POST['image_url'])?str_replace('../','',$_POST['image_url']):'';
		$data['image_rss']=isset($_POST['image_rss'])?str_replace('../','',$_POST['image_rss']):'';
		$data['image_thumb']=Video::youtube_vimeo_check($data['mediafile_url'])?Video::getVideoImage($data['mediafile_url']):'';
		$data['modified_date']=Date::buildMysqlTime($timestamp);
		$data['permalink']=(isset($_POST['permalink'])?$_POST['permalink']:'');

		$data['content']=str_replace('\\','&#92;',$data['content']);//jpc
		$data['content']=Editor::replaceClasses($data['content']);
		if(isset($_POST['slideshow_type']))
		{
		  $data['slideshow_type']=intval($_POST['slideshow_type']);
		  $data['slideshow']=isset($_POST['slides'])?implode('|',$_POST['slides']):'';
		}

		if($new_post || !is_array($exist_rec))
		{
			if(!$as_template)
				$data['pinged_url']=''; //joe: This forces every new post to has empty tb for some reason...
			$db->query_insert($this->page->pg_pre.'posts',$data);
		}
		else
			$db->query_update($this->page->pg_pre.'posts',$data,'entry_id='.$data['entry_id']);

		History::add($this->page->pg_id,$this->page->pg_pre.'posts',$data['entry_id'],$this->page->user->getId(),$data);

		if(!empty($_POST['Ping_urls']) && !$as_template)
		{
			$send_tb_to=explode(',',Formatter::stripTags(trim($_POST['Ping_urls'])));
			$ping_valid=true;
			foreach($send_tb_to as $k=>$v)
				if(strpos(strtolower($v),'http')===false)
					$ping_valid=false;

			if($ping_valid)
			{
				$excerpt=(!empty($data['excerpt']))?$_POST['excerpt']:$_POST['content'];
				if(strlen($excerpt)>150)
					$excerpt=Formatter::splitHtmlContent($excerpt,600);
				$permalink=$this->page->build_permalink($data,true,'',true);

				$exist_rec=$this->page->db_fetch_entry($data['entry_id']);
				$exist_pinged=$exist_rec!==false && explode(' ',$exist_rec['pinged_url']);

				$pinged_new='';
				foreach($send_tb_to as $k=>$v)
				{
					if($new_post || !in_array($v,$exist_pinged))
					{
						$res=$this->page->trackbacks->send_trackback($v,$exist_rec['title'],$excerpt,$permalink);
						if($res!==false)
							$pinged_new.=' '.$v;
					}
				}
				if($pinged_new!=='')
					$db->query('
						UPDATE '.$db->pre.$this->page->pg_pre.'posts
						SET pinged_url=CONCAT(pinged_url, "'.$pinged_new.'")
						WHERE entry_id='.$exist_rec['entry_id']);
			}
		}

		$tc=new tags_cloud($this->page);
		$tc->clean_tagcloud();

		$this->page->reindex_search($data['entry_id']);
		$this->page->rss->rebuild_rssfeed();
		RSS::clearCache($this->page->rel_path);

		if($this->page->pg_settings['notify_post'] && $new_post)
		{
			$data['posted_by']=$this->page->user->getUname();
			$this->page->send_admin_notification('index',$this->page->lang_l('content'),$this->page->build_permalink($data,true,'',true),$data);
		}
		$this->page->setState('save_entry');
		return $autopost?'OK':'';
	}

	public function save_entry_simple()
	{
		global $db;

		if(!isset($_POST['entry_id']))
			return '';
		else
			$data['entry_id']=intval($_POST['entry_id']);

		$data['content']=$_POST['content'];
		//when extra url handler is used

		$data['modified_date']=Date::buildMysqlTime();

		if(isset($_REQUEST['dt64']))
			$data['content']=base64_decode($_REQUEST['dt64']);
		elseif(isset($_REQUEST['dt']))
			$data['content']=$_REQUEST['dt'];

		if($this->page->canUseURL() && isset($_POST['content_x']) && $_POST['content_x'] != '')
			$data['content'] .= $_POST['content_x'];

		$data['content']=str_replace('\\','&#92;',$data['content']);//jpc
		$data['content']=Editor::replaceClasses($data['content']);

		if($this->page->all_settings['contentbuilder']){
			$user=$this->page->user->mGetLoggedUser($db);
			$data['content']=Editor::replaceData64image_contentBuilder($data['content'],$this->page->rel_path,$user['username']);
		}

		$db->query_update($this->page->pg_pre.'posts',$data,'entry_id='.$data['entry_id']);

		History::add($this->page->pg_id,$this->page->pg_pre.'posts',$data['entry_id'],$this->page->user->getId(),$data);

		$tc=new tags_cloud($this->page);
		$tc->clean_tagcloud();
		$this->page->reindex_search($data['entry_id']);
		$this->page->rss->rebuild_rssfeed();
		RSS::clearCache($this->page->rel_path);

		$this->page->setState('save_entry_simple');
		return;
	}

	public function delete_entry($operation_allowed)
	{
		global $db,$thispage_id;

		$entry_ids=(is_array($_REQUEST['entry_id']))? $_REQUEST['entry_id']: array($_REQUEST['entry_id']);
		$entry_range=implode(',',$entry_ids);
		if(!empty($entry_range))
		{
			$res=$db->query('
				DELETE
				FROM '.$db->pre.$this->page->pg_pre.'posts
				WHERE entry_id IN ('.$entry_range.')'.$operation_allowed);
			if($res)
			{
				$db->query('DELETE FROM '.$db->pre.$this->page->pg_pre.'comments WHERE entry_id IN ('.$entry_range.')');
				$this->page->trackbacks->delete_trackbacks_forentries($entry_range);
				foreach($entry_ids as $entr)
					$this->page->reindex_search($entr,'del');
			}
			$tc=new tags_cloud($this->page);
			$tc->clean_tagcloud();
		}
		elseif($entry_range==0)
			$db->query('DELETE FROM '.$db->pre.$this->page->pg_pre.'posts WHERE entry_id=0');

		if(!isset($thispage_id))
			$this->page->rss->rebuild_rssfeed();
	}

	public function publish_entry($entry_id,$operation_allowed)
	{
		global $db,$thispage_id;

		$db->query('UPDATE '.$db->pre.$this->page->pg_pre.'posts
			 SET publish_status="'.($this->page->action_id=='pub_entry'?'published':"unpublished").'"
			 WHERE entry_id='.$entry_id.$operation_allowed);

		if(!isset($thispage_id))
			$this->page->rss->rebuild_rssfeed();

		if($this->page->action_id=='pub_entry')
			$this->page->reindex_search($entry_id);
		else
			$this->page->reindex_search($entry_id,'del');
		$this->page->setState('un_pub_entry');
	}

	public function duplicate_entry($entry_id)
	{
		global $db,$thispage_id;

		$ts=time();
		$record=$db->query_first('SELECT * FROM '.$db->pre.$this->page->pg_pre.'posts WHERE entry_id='.$entry_id);
		$record=array_reverse($record);array_pop($record);
		$record['entry_id']=$ts;
		$record['visits_count']=0;
		$record['comment_count']=0;
		$record['modified_date']=Date::buildMysqlTime($ts);
		$record['creation_date']=Date::buildMysqlTime($ts);
		$record['posted_by']=$this->page->user->getId();
		$record['title']=Formatter::strToUpper($this->page->lang_l('duplicate')).' '.$record['title'];
		$record['publish_status']='unpublished'; //don't publish duplicated posts immediately

		if(!empty($record['image_url']))
		{
			$orig_file_name=$record['image_url'];
			$ext=substr($orig_file_name,strrpos($orig_file_name,"."));
			$new_file_name=str_replace($ext,'_'.$ts.$ext,$orig_file_name);
			copy($this->page->get_file_url($orig_file_name,$this->page->rel_path),$this->page->get_file_url($new_file_name,$this->page->rel_path));
			$record['image_url']=$new_file_name;
		}
		if(!$this->page->is_blog && !empty($record['mediafile_url']) && strpos($record['mediafile_url'],'youtube.')===false)
		{
			$orig_m_file_name=$record['mediafile_url'];
			$ext=substr($orig_m_file_name,strrpos($orig_m_file_name,"."));
			$new_m_file_name=str_replace($ext,'_'.$ts.$ext,$orig_m_file_name);
			copy($this->page->get_file_url($orig_m_file_name,$this->page->rel_path),$this->page->get_file_url($new_m_file_name,$this->page->rel_path));
			$record['mediafile_url']=$new_m_file_name;
		}
		$db->query_insert($this->page->pg_pre.'posts',$record);

		if(!isset($thispage_id))
			 $this->page->rss->rebuild_rssfeed();
		$this->page->reindex_search($entry_id);
	}

	public function mail_entry($entry_id,$period_id,$access_all_flag,&$mailer_js)
	{
		$record=$this->page->db_fetch_entry($entry_id);
		$record['permalink']='<a class="rvts4" href="'.$this->page->build_permalink($record,true,'',true).'">'.$this->page->lang_l('full article').'</a>';
		$record['postedby']=User::getUserName($record['posted_by'],$this->page->rel_path);
		if(!$this->page->user->isAdmin() && !$access_all_flag && $record['posted_by']!=$this->page->user->getId())
			$output=$this->manage_entries($period_id);
		else
		{
			$subjTemplate='%title%';
			$bodyTemplate='<h1>%title%</h1><div>%content/excerpt%</div><p>%permalink%</p>';
			$settings=array(
				'lang_l'=> $this->page->pg_settings['lang_l'],
				'mailData'=> $record,
				'innova_def' => $this->page->innova_def,
				'page_id'=> $this->page->pg_id,
				'page_encoding' => $this->page->pg_settings['page_encoding'],
				'from_email' => $this->page->pg_settings['from_email'],
				'print_output' => false,
				'subjTemplate' => $subjTemplate,
				'bodyTemplate' => $bodyTemplate
			);
			list($output,$mailer_js)=MailHandler::mailer($settings,'BL',$this->page);
			$this->page->setState('mail_entry');
		}
		$this->page->innova_on_output=true;
		return $output;
	}
}

class timeline extends page_objects
{
	public function show_timeline($write,$h='100%',$inline=false)
	{
		$hc=isset($_REQUEST['hc']);
		$full_mode=isset($_REQUEST['full_mode'])&&$_REQUEST['full_mode']=='1'?'1':'0';
		$res=output_generator::showTimeline($this->page->rel_path,$this->page->pg_name,'?action=timeline_data&full_mode='.$month_mode,
				  $write,$this->page->pg_settings['timeline_init_zoom'],$this->page->pg_settings['timeline_lang'],
				  $this->page->pg_settings['timeline_reversed'],60,false,$h,$inline,$hc);
		$this->page->setState('show_timeline');
		if($inline) return $res;
	}
	public function get_timeline_data()
	{
		global $db;

		$force_full_mode=isset($_REQUEST['full_mode'])&&$_REQUEST['full_mode']=='1';
		$write=false;
		$media_field=(!$this->page->is_blog?'mediafile_url':'image_url');
		$periods_ar=array();
		$entries_full=$db->fetch_all_array('
			SELECT entry_id,title,content,'.$media_field.','.$this->page->order_by.'
			FROM '.$db->pre.$this->page->pg_pre.'posts
			WHERE '.$this->page->where_pub.'
			ORDER BY ' .$this->page->order_by.' ASC ,id ');
		$montmode=!$force_full_mode&&count($entries_full)>80;
		$temp_array=array();
		foreach($entries_full as $k=>$v)
		{
			$date_ts=strtotime($v[$this->page->order_by]);
			$dt=$montmode?date('F Y',$date_ts):date($date_ts);
			if(isset($temp_array[$dt]))
			{
				$temp_array[$dt]['count']=$temp_array[$dt]['count']+1;
				$urls=array('t'=>$v['title'],'e'=>$v['entry_id'],'d'=>date('d',$date_ts));
				$temp_array[$dt]['urls'][]=$urls;
			}
			else
			{
				$m=date('n',$date_ts);
				$y=date('Y',$date_ts);
				$temp_array[$dt]=array(
					 'count'=>1,
					 'id'=>date('F Y',$date_ts),
					 'ids'=>"id_".$y.'_'.$m,
					 'day'=>date('d',$date_ts),
					 'month'=>$m,'year'=>$y,
					 'entry_id'=>$v['entry_id'],
					 'title'=>$v['title'],
					 'content'=>$v['content'],
					 'media'=>((strpos($v[$media_field],'http')===false)?$this->page->rel_path:"").$v[$media_field]);
			}
		}
		foreach($temp_array as $k=>$v)
			$periods_ar[$k]=$v;

		$data=array();
		$fst=false;
		$startdate=0;
		foreach($periods_ar as $k=>$v)
		{
			if(!$fst)
			{
				$startdate=strval(intval($v['year'])).",".$v['month'];
				$fst=!$fst;
			}
			$edit=$write?'  <input type="button" onclick="editTL(\''.$v['ids'].'\')" class="ui_shandle_ic6">':'';
			$th='';$media_parsed='';
			//handling media
			if($v['media']!='')
			{
			 	$media=pathinfo($v['media']);
			 	if(isset($media['extension']))
			 	{
					$ext=strtolower($media['extension']);
					if(in_array(strtolower($ext),$this->page->img_file_types))
						$media_parsed="<img class='int_media-image' src='".$v['media']."'>";
					else
						$media_parsed='';
				}
				elseif(Video::youtube_vimeo_check($v['media']))
					$media_parsed="<div class='int_media-container'>".$this->page->av_object->get_youtube($v['media'])."</div>";
				elseif(strpos($v['media'],'soundcloud')!==false)
					$media_parsed='';
			}

			if($montmode)
			{
				$th=F_BR;
				if(isset($v['urls']))
					foreach($v['urls'] as $vv)
						$th.="<h3><div class='tl_date'><div class='tl_mon'></div><div class='tl_day'>".$vv['d']."</div></div> <span onclick='handleEn(this)' class='entry' title='".$vv['t']
							."' rel='".$vv['e']."'>".$vv['t']."</span></h3><div style='clear:left' id='ev_".$vv['e']."'></div>";
			}
			$data[]=array("date"=>$v['year'].",".$v['month'].($montmode?'':",".$v['day'])
				,"title"=>"<span class='".$v['ids']."' onclick='showFirstEn(this)'>".$v['title']."</span>".$edit
				,"text"=>$media_parsed.$v['content'].$th
				,"media"=>""
				,"credit"=>""
				,"caption"=>'');
		}
		output_generator::timelineData($startdate,'','','',$data);
		$this->page->setState('get_timeline_data');
	}
}

class blogrss extends page_rss
{
	protected $rssCacheFile;

	public function __construct($pg)
	{
		parent::__construct($pg);
		$this->rssCacheFile=$this->page->rel_path.'innovaeditor/assets/rss_cache'.$this->page->pg_id.'.xml';
	}

	public function output_rssfeed()  // used only when rss feed is saved in database
	{                
		$generated=0;
                $content=$this->get_rssfeed($generated);
		if(!$this->page->pg_settings['rss_cache'] || $generated)
		  $content=$this->split_rss_to_items($content,
				  (isset($_GET['items'])?intval($_GET['items']):0),
				  (isset($_GET['category'])?Formatter::stripTags($_GET['category']):''));

		Formatter::hideFromGuests($content);

		header("Content-Type: text/xml; charset=".$this->page->pg_settings['page_encoding']);
		echo $content;
	}

	protected function get_rssfeed(&$generated)
	{
                $generated=0;
		if(isset($_REQUEST['q']) || isset($_REQUEST['items']) || isset($_REQUEST['tag']) || isset($_REQUEST['orderby']))
                {
                        $generated=1;
                        return $this->generate_rssfeed();                        
                }
                else if($this->page->pg_settings['rss_cache'] && strlen($this->page->all_settings['rss_cache'])>0)                                         
			return str_replace('&amp;fl=1','',$this->page->all_settings['rss_cache']);                                                   
		else
			return $this->generate_rssfeed();
	}

	public function rebuild_rssfeed()
	{
		if($this->page->pg_settings['rss_cache'])
		{
			$data=$this->generate_rssfeed(true);
			$this->page->db_insert_settings(array('rss_cache'=>$data));
			$this->page->all_settings['rss_cache']=$data;
			file_put_contents($this->rssCacheFile,$data);
		}
	}

	public function generate_rssfeed($isFl=false)
	{
		global $db;

		clearstatcache();
	 	$media_types_itunes=array("m4a"=>"audio/x-m4a","mp3"=>"audio/mpeg","mov"=>"video/quicktime","mp4"=>"video/mp4","m4v"=>"video/x-m4v", "pdf"=>"application/pdf");

		$new_data='';
		$fl_flag=($this->page->is_blog && $this->page->fl_studio_flag);
		$this->page->full_script_path2=str_replace("/".$this->page->pg_name,'',$this->page->full_script_path);

		$uid=isset($_GET['uid'])?intval($_GET['uid']):0;
		$image=isset($_GET['image'])?intval($_GET['image']):1;
		$image_width=intval(isset($_GET['iwidth'])?$_GET['iwidth']:$this->page->pg_settings['rss_image_width']);
		$where=($uid!=0)?' posted_by='.$uid:'';
		$this->get_rss_category_where($where);

		$tag=Formatter::stripTags(isset($_GET['tag'])?$_GET['tag']:'');
		if($tag!=='')
		{
			$tag=explode("|",Formatter::unEsc(urldecode($tag)));
			if(count($tag)>0)
				foreach($tag as $k=>$v)
					$where.=($where==''?'':' OR ').'keywords like "%'.$v.'%"';
		}

		if(isset($_REQUEST['q']) && $_REQUEST['q']!='')
		{
			$q=Formatter::unEsc(urldecode($_REQUEST['q']));
			$delim=' AND ';
			if(strpos($q,'|')!==false)
			{
				$q=explode ('|', $q);
				$delim=' OR ';
			}
			else
				$q=explode(' ',$q); //explodes + or if simple word

			if($where!='')
				$where.=' AND ';
			$where.=' (';
			foreach($q as $subq)
				$where.=' title like "%'.$subq.'%" OR content like "%'.$subq.'%" OR';
			$where.=' true )';
		}

		if(isset($_REQUEST['orderby']) && $_REQUEST['orderby'] == 'views')
			 $this->page->order_by='visits_count ASC, '.$this->page->order_by;

		$max=$this->page->pg_settings['max_items_in_rss'];
		if(isset($_GET['items']))
			$max=intval($_GET['items']);
		if($max>0)
			$entry_total=$this->page->db_count('posts', $where);
		else
			$max=0;

		$min=($this->page->c_page-1)*$max;
		$where=($where!='')?$this->page->where_public.' AND '.$where:$this->page->where_public;
		$que='
			SELECT * FROM '.$db->pre.$this->page->pg_pre.'posts'.($where!=''? '
			WHERE '.$where:'').'
			ORDER BY weight ASC,'.$this->page->order_by.' '.$this->page->order_type.' '.($max>0 && $entry_total>$max? '
			LIMIT '.$min.', '.$max.'':'');

		$entries_records=$db->fetch_all_array($que);

		if(!empty($entries_records[0]))
			$publish_date=Date::tzoneSql($entries_records[0][$this->page->date_used]);
		else
			$publish_date=Date::tzone(time());

		$rss_data=array();
		foreach($entries_records as $k=>$v)
		{
			$rss_item=array();
			$title=str_replace('&#039;',"'",Formatter::sth2($v['title']));
			$title=(empty($v['title'])?'empty':str_replace('&','&amp;',$title));

			$mf=Formatter::sth($v['mediafile_url']);
			$media_fname=substr($mf,strrpos($mf,"/")+1);
			$listen_url=(strpos($mf,'http')===false?$this->page->rel_path:'').str_replace($media_fname,rawurlencode($media_fname),$mf);

			if($this->page->is_blog&&empty($v['image_url'])&&Video::youtube_vimeo_check($listen_url)) //check if yt used in yt filed and use it's image as url
				$v['image_url']=$v['image_thumb'];

			if(!empty($v['image_rss']))
				$v['image_url']=$v['image_rss'];

			if(!empty($v['image_url']))
			{
				$image_field_value=$this->page->get_file_url($v['image_url'],'');
				$image_fname=substr($image_field_value,strrpos($image_field_value,"/")+1);
				$img_exists=file_exists($this->page->rel_path.$image_field_value);
			}
			settype($this->page->pg_settings['max_lines_in_rss_desc'],"integer");
			if($this->page->pg_settings['use_excerpt_in_rss'] && !empty($v['excerpt']))
				$description=str_replace(F_LF,F_BR,Formatter::sth2($v['excerpt']));
			elseif($this->page->pg_settings['max_lines_in_rss_desc']==0)
				$description=Formatter::sth2($v['content']);
			else
			{
				$temp=Formatter::sth2($v['content']);
				$max_chrl=$this->page->pg_settings['max_lines_in_rss_desc']*60;
				$description=(strlen($temp)>$max_chrl)?Formatter::splitHtmlContent($temp,$max_chrl):$temp;
			}
			$description=Editor::fixInnovaPaths($description,$this->page->pg_name,$this->page->full_script_path,$this->page->rel_path);

			if($fl_flag)
				$description=str_replace(array("<br>","<br />","<BR>"),array("%%%","%%%","%%%"),$description); //fl blog only
			$description=str_replace('&','&amp;',$description);

			if($this->page->pg_settings['use_html_inrss'])
				$description=str_replace(array('<','>'),array('&lt;','&gt;'),$description);
			else
				$description=preg_replace("'<[/!]*?[^<>]*?>'si"," ",$description);

			if($fl_flag)
				$description=str_replace("%%%",F_BR,$description); //fl blog only

			$description=str_replace(array('&quot;','&nbsp;','<'),array('"',' ','&#60;'),$description);

			$permalink=$this->page->build_permalink($v,false,'',true);

			$description=str_replace('%permalinkurl%',$permalink,$description);
			if(strpos($description,'%permalink%')!==false)
			{
				 $description=str_replace('%permalink%','<a href="'.$permalink.'">'.$this->page->lang_l('permalink').'</a>',$description);
				 $description=str_replace(array('<','>'),array('&lt;','&gt;'),$description);
			}

			if($fl_flag && !empty($v['rss_link_info']))
			{
				if(strpos($v['rss_link_info'],'http://')!==false)
					$link_line=str_replace('&','&amp;',$v['rss_link_info']);
				else
					$link_line=$this->f->http_prefix.str_replace('&','&amp;',$v['rss_link_info']);
			}
			else
				$link_line=$permalink;


			$desc_line='';
			if(strpos($this->page->rel_path,'../')!==false)
				$full_path_to_script_fixed=str_replace(substr($this->page->full_script_path2,strrpos($this->page->full_script_path2,'/')),'',$this->page->full_script_path2);
			$src_pref=(strpos($this->page->rel_path,'../')===false?$this->page->full_script_path2:$full_path_to_script_fixed);

			if(!empty($v['image_url']) && $img_exists)
			{
				$ext=substr($image_field_value, (strrpos($image_field_value,".")+1));
				$image_rawencode=str_replace($image_fname,rawurlencode($image_fname),$image_field_value);
				if($this->page->is_blog && $image && in_array(Formatter::strToLower($ext),array('jpg','jpeg','png','gif','tif','tiff','bmp')))
				{
					$desc_line.='&#60;a target="_blank" href="'.$link_line.'">&#60;img src="'.$src_pref."/".$image_rawencode. '"'.($this->page->pg_settings['rtl']?' dir="rtl"':'');
					$desc_line.=' alt="'.$title.'" style="max-width:'.$image_width.'px !important;float:left;'.($this->page->pg_settings['use_html_inrss']?"padding: 0 5px 5px 0;":"margin: 3px 4px 3px 0;").'" target="_blank">&#60;/a> ';
				}
			}
			Formatter::hideFromGuests($description);
			$desc_line.=(empty($v['content'])?' ':$description).'&lt;div style="clear:both"&gt;&lt;/div&gt;';
			$rss_item=array('title'=>$title,
					'description'=>$desc_line,
					'link'=>$link_line.($isFl?'&amp;fl=1':''));

			if($this->page->is_blog && !empty($v['image_url']))
			{
				if(strpos($image_fname,'.mp3')!==false && $img_exists)
				{
					$enclose_url=$src_pref."/".$image_rawencode;
					$rss_item['enclosure']=array(
						'url'=>$enclose_url,
						'length'=>filesize($this->page->rel_path.$image_field_value),
						'type'=>'audio/mpeg'
						);
				}
			}
			elseif(!$this->page->is_blog)
			{
				$media_field_value=Formatter::sth2($v['mediafile_url']);
				$media_fname=substr($media_field_value,strrpos($media_field_value,"/")+1);
				$media_rawencode=str_replace($media_fname,rawurlencode($media_fname),$media_field_value);

				if(Video::youtube_vimeo_check($media_field_value))
					$rss_item['enclosure']=array(
						'url'=>(strpos($media_rawencode,'http')===false?$this->f->http_prefix:'').$media_rawencode,
						'length'=>60000,
						'type'=>'video/quicktime');
				else
				{
					$media_ext=substr($media_field_value,strrpos($media_field_value,'.')+1);
					$rss_item['enclosure']=array(
						'url'=>(strpos($media_rawencode,'http')===false?$src_pref."/":'').$media_rawencode,
						'length'=>intval($v['mediafile_size'])>0?$v['mediafile_size']:60000,
						'type'=>(isset($media_types_itunes[$media_ext])?$media_types_itunes[$media_ext]:($media_ext=='flv'? 'video/x-flv':''))
						);
				}
			}

			//media tag for blog prepared below
			if(!empty($v['image_url']))
			{
				 $med_url=$med_type='';
				 $med_size=-1;
				 if($this->page->is_blog && Video::youtube_vimeo_check($image_field_value))
				 {
					 $med_url=(strpos($image_field_value,'http')===false?$this->f->http_prefix:'').$image_field_value;
					 $med_url=substr($med_url,0,  strrpos($med_url, '/')).'/'
							 .urlencode(substr($med_url, strrpos($med_url, '/')+1));
					 $med_type='video/quicktime';
				 }
				 elseif(strpos($image_field_value,'http')!==false)
					 $med_url=substr($image_field_value,0,  strrpos($image_field_value, '/')).'/'
						 .urlencode(substr($image_field_value, strrpos($image_field_value, '/')+1));
				 else
				 {
					 $med_url=$src_pref."/".$image_rawencode;
					 $med_size=file_exists($this->page->rel_path.$image_field_value)?filesize($this->page->rel_path.$image_field_value):0;
				 }
				 if($med_url != '')
				 {
					 $rss_item['media:content']['url']=$med_url;
					 $med_type=Detector::getMime(pathinfo($med_url,PATHINFO_EXTENSION),'');
					 if($med_size > -1)
						 $rss_item['media:content']['fileSize']=$med_size;
					 if($med_type != '')
						 $rss_item['media:content']['type']=$med_type;
				 }
			}

			$parent=-1;
			$category_line=str_replace('&#039;',"'",Formatter::sth($this->page->categoriesModule->get_category_info($v['category'],'name',$parent)));
			if($fl_flag)	 //fl blog only
			{
				$domain=(isset($v['rss_domain_info'])?str_replace (array('<','>'),array('&lt;','&gt;'),Formatter::sth($v['rss_domain_info'])):'');
				if(!empty($domain))
					$rss_item['category']=array('domain'=>$domain, 'value'=>$category_line);
				else
					$rss_item['category']=$category_line;
			}
			else
				$rss_item['category']=$category_line;

			if($this->page->pg_settings['enable_comments'] && $v['allow_comments']=='true')
			{
				$rss_item['comments']=$permalink;
				if($this->page->comments_feed_in_rss)
				{
					$rss_item['wfw:commentRss']=$this->page->build_permalink($v,false,'rss',true);
					$rss_item['slash:comments']=$v['comment_count'];
				}
			}
			$rss_item['guid']=$permalink.($isFl?'&amp;fl=1':'');
			$rss_item['pubDate']=date('r',Date::tzoneSql($v[$this->page->date_used]));

			if(!$this->page->is_blog)
			{
				$summary=preg_replace("'<[/!]*?[^<>]*?>'si"," ",Formatter::sth2($v['content']));
				if(!empty($v['subtitle']))
					$rss_item['itunes:subtitle']=Formatter::sth($v['subtitle']);
				Formatter::hideFromGuests($summary);
				$rss_item['itunes:summary']=str_replace (array('&','<','>','\'','"','&nbsp;'),array('&amp;','&lt;','&gt;','&apos;','&quot;',''),$summary);
				if($v['duration']!='00:00:00')
					$rss_item['itunes:duration']=$v['duration'];
				if($v['explicit']!='no')
					$rss_item['itunes:explicit']=$v['explicit'];
				if($v['block']=='yes')
					$rss_item['itunes:block']=$v['block'];

				if(!empty($v['author']))
					$rss_item['itunes:author']=Formatter::sth($v['author']);
				elseif(!empty($this->page->pg_settings['rss_settings']['Author (iTunes)']))
					$rss_item['itunes:author']=Formatter::sth($this->page->pg_settings['rss_settings']['Author (iTunes)']);

				if(!empty($v['keywords']))
					$rss_item['itunes:keywords']=Formatter::sth($v['keywords']);
			}
			$rss_data[]=$rss_item;
		}

		$more_xmlns=($this->page->comments_feed_in_rss? 'xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/"': '');
		$more_xmlns .= ' xmlns:media="http://search.yahoo.com/mrss/"';
                if ($this->page->all_settings['blacklist']!='')
                    $more_xmlns .= ' newsindex="'.base64_encode($this->page->all_settings['blacklist']).'"';                

		$new_data=RSS::build($rss_data,$this->page->pg_settings['rss_settings'],$this->page->pg_settings['page_encoding'],
				  $this->page->full_script_path,$publish_date,$more_xmlns,$fl_flag, ($this->page->use_alt_plinks? str_replace($this->page->pg_name,'',$this->page->script_path).'rss/':''));

		Formatter::replacePollMacro_null($new_data);

		return $new_data;
	}


	protected function split_rss_to_items($content,$limit='',$category='')
	{
		$items=array();
		$t=$this->page->fl_studio_flag?' ':'';
		$buffer='<?xml'.Formatter::GFS($content,'<?xml','<item>');

		$pos=strpos($content,'<item');
		while($pos!=false)
		{
			$item=Formatter::GFSAbi($content,'<item','/item>');
			$content=str_replace($item,'',$content);
			$items[]=$item;
			$pos=strpos($content,'<item');
		}

		if($category !== '')
		{
			$sub_items=array();
			$cats=explode('|',$category);
			foreach($cats as $cat)
			{
				$cat=str_replace('&','&amp;',$cat);
				foreach($items as  $item)
					if(strpos($item,'<category>'.$cat.'</category>')!==false)
						$sub_items[]=$item;
			}
			$items=$sub_items;
		}

		//search engine
		if(isset($_REQUEST['q']) && $_REQUEST['q']!='')
		{
			$sub_items=array();
			$q=Formatter::unEsc(urldecode($_REQUEST['q']));
			$glue='|';
			if(strpos($q, ' '))
				$glue=' ';
			$words=explode($glue,$q);

			foreach($items as $item)
			{
				$has_all_words=true;
				foreach($words as $word)
				{
					$title=Formatter::GFS($item, '<title>', '</title>');
					$desc=Formatter::GFS($item,'<description>','</description>');
					if($glue=='|')
					{
						if(stripos($title,$word)!==false || stripos($desc,$word)!==false)
							$sub_items[]=$item;
					}
					else
					{
						if(stripos($title,$word)===false && stripos($desc,$word)===false)
							$has_all_words=false;
					}
				}
				if($glue===' ' && $has_all_words)
					$sub_items[]=$item;
			}
			$items=$sub_items;
		}

		if($limit>0 && count($items) > $limit)
			$items=array_slice($items,0,$limit);
		foreach($items as $v)
			$buffer.=$t.$v.F_LF;

		$buffer.=$t."</channel>".F_LF.$t."</rss>";
		return $buffer;
	}

}

class trackbacks extends page_objects
{
	public function trackbacks_html($records)
	{
		$output='';
		if(!empty($records))
		{
			foreach($records as $v)
			{
				if($v['approved']==1)
				{
					$output.='
					 <p>
						<span class="rvts8">[ '.$this->page->format_dateSql($v['date']).',</span>
						  <a class="rvts12" href="'.Formatter::sth($v['url']).'">'.Formatter::sth($v['blog_name']).' >> '.Formatter::sth2(html_entity_decode($v['title'],ENT_QUOTES)).'</a><span class="rvts8"> ]
						</span>
					 </p><br>
					 <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						  <span class="rvts8">'.Formatter::sth2(html_entity_decode($v['excerpt'],ENT_QUOTES)).' </span>
					 </p><br>';
				}
			}
			$output='<div>
						  <h4>'.Formatter::strToUpper($this->page->lang_l('trackbacks')).'</h4><br>'
						  .$output.'
						</div>';
		}
		return $output;
	}

	public function delete_trackbacks_fromId()
	{
		global $db;
		$db->query('DELETE FROM '.$db->pre.$this->page->pg_pre.'trackbacks WHERE ip="'.addSlashes($_REQUEST['ip']).'"');
		Linker::checkReturnURL();
	}

	public function toggle_trackback($trackback_id,$flag='toggle')
	{
		global $db;

		$data=$this->db_fetch_trackback($trackback_id);
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
					UPDATE '.$db->pre.$this->page->pg_pre.'trackbacks
					SET approved = '.($was_approved?0:1).'
					WHERE trackback_id = '.$trackback_id);
				$this->page->commentModule->db_update_comment_count($entry_id,($was_approved?'-':'+'),'trackback_count');

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

	public function db_fetch_trackback($trackback_id, $where='')
	{
		global $db;

		$records=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->page->pg_pre.'trackbacks
			WHERE trackback_id = '.$trackback_id.($where!=''?' AND '.$where: ''));
		return $records;
	}

	public function delete_trackback($trackback_id)
	{
		global $db;

		$data=$this->db_fetch_trackback($trackback_id);
		$db->query('
			DELETE
			FROM '.$db->pre.$this->page->pg_pre.'trackbacks
			WHERE trackback_id='.$trackback_id);
		if($data['approved']==1)
			 $this->page->commentModule->db_update_comment_count($data['entry_id'],'-','trackback_count');
		Linker::checkReturnURL();
	}

	public function delete_trackbacks_forentries($entry_range)
	{
		global $db;

		$db->query('
			 DELETE
			 FROM '.$db->pre.$this->page->pg_pre.'trackbacks
			 WHERE entry_id IN ('.$entry_range.')',true);
	}

	public function get_trackbacks_html($entry_id,&$entries_records)
	{
		global $db;

		$result='';
		if($entries_records!==false)
		{
			$trackbacks_records=$db->fetch_all_array('
				SELECT *
				FROM '.$db->pre.$this->page->pg_pre.'trackbacks
				WHERE entry_id='.$entry_id.'
				ORDER BY date DESC');
			$result=$this->trackbacks_html($trackbacks_records);
		}
		return $result;
	}

	public function build_trackback()
	{
		global $db;

		$output='<?xml version="1.0" encoding="utf-8"?>'.F_LF.'<response>'.F_LF.'<error>';
		if(!isset($_REQUEST['entry_id']))
			exit;

		$entry_id=intval($_REQUEST['entry_id']);
		$entry_data=$this->page->db_fetch_entry($entry_id);

		if($entry_data!==false && $entry_data['allow_pings']=='true')
		{
			if(isset($_POST['title']) && isset($_POST['excerpt']) && isset($_POST['url']) && isset($_POST['blog_name']))
			{
				$ip=Detector::getIP();
				if($ip!='' && $this->page->blockedIpModule->db_is_ip_blocked($db,$ip))
					$output.='1</error>'.F_LF.'<message>Your IP is blocked</message>';
				else
				{
					$ts=time();
					$data['entry_id']=$entry_id;
					$data['trackback_id']=$ts;
					$data['date']=Date::buildMysqlTime($ts);
					$data['blog_name']=$_POST['blog_name']=='null'?'no blog name':Formatter::stripTags($_POST['blog_name']);
					$data['url']=Formatter::stripTags($_POST['url']);
					$data['title']=$_POST['blog_name']=='null'?'no blog title':Formatter::stripTags($_POST['title']);
					$excerpt=urldecode($_POST['excerpt']);
					if(strlen($excerpt)>150 && $data['blog_name']!='ezg')
						 $excerpt=Formatter::splitHtmlContent($excerpt,600);
					$data['excerpt']=Formatter::sth($excerpt);
					$data['approved']=0;
					$data['ip']=Detector::getIP();
					$data['host']=(isset($_SERVER['REMOTE_HOST'])? $_SERVER['REMOTE_HOST']: '');
					$data['agent']=(isset($_SERVER['HTTP_USER_AGENT'])? Detector::defineOS($_SERVER['HTTP_USER_AGENT']): '');
					$db->query_insert($this->page->pg_pre.'trackbacks',$data);
					$output.='0</error>';
					if($this->page->pg_settings['notify_track'])
					{
						$posted_by_name=User::getUserName($entry_data['posted_by'],$this->page->rel_path);
						$author=($posted_by_name!='admin')?$posted_by_name:'';
						$this->page->send_admin_notification('trackbacks',$this->page->lang_l('trackbacks'),$this->page->build_permalink($entry_data,true,'trackback',true),
							array_merge($entry_data,$data,array('date'=>$ts)),$author);
					}
				}
				$output.=F_LF.'</response>';
				header("Content-Type: text/xml; ");
				echo $output;
				exit;
			}
		}
	}

	public function send_trackback($trackback_url,$title,$excerpt,$permalink)
	{
		if(empty($trackback_url)) return;
		$errno=$errstr='';
		$title=urlencode(Formatter::unEsc($title));
		$excerpt=urlencode(Formatter::unEsc($excerpt));
		$blog_name=urlencode(strpos($this->page->pg_settings['rss_settings']['Title'],'Type title here')===false? Formatter::unEsc($this->page->pg_settings['rss_settings']['Title']): '');
		$url=urlencode($permalink);

		$query_string="title=$title&url=$url&blog_name=$blog_name&excerpt=$excerpt";
		$trackback_url=parse_url($trackback_url);
		$http_request='POST '.$trackback_url['path']. (isset($trackback_url['query'])? '?'.$trackback_url['query']:'')." HTTP/1.0\r\n"
			.'Host: '.$trackback_url['host']."\r\n"
			.'Content-Type: application/x-www-form-urlencoded; charset='.$this->page->pg_settings['page_encoding']."\r\n"
			.'Content-Length: '.strlen($query_string)."\r\n"
			."User-Agent: EZGenerator/"
			."\r\n\r\n"
			.$query_string;
		$trackback_url['port']=80;
		$fs=@fsockopen($trackback_url['host'],$trackback_url['port'],$errno,$errstr,4);
		$res=@fputs($fs,$http_request);
		@fclose($fs);
		return $res;
	}

}

class blog_admin_screens extends page_admin_screens
{

	final public function output($output)
	{
		$output=Formatter::fmtAdminScreen($output,$this->build_menu(''));
		$output=Formatter::fmtInTemplate($this->page->pg_settings['template_path'],$output,$this->page->page_info[0].' &raquo; '.$this->page->lang_l('administration panel'));
		$output=Builder::includeBrowseDialog($output,$this->page->rel_path,$this->page->pg_settings['ed_lang']);

	//add-edit post validation
		if(strpos($output,'vForm()')!==false)
		{
			$this->page_scripts.='function vForm(){mfb=document.forms["b_edit_form"];if(mfb["title"].value==""){alert("'.strtoupper($this->page->lang_l('title'))." ".$this->page->lang_l('field is required').'!");return false}'.F_LF;
			if(!$this->page->is_blog)
				$this->page_scripts.='else if(mfb["mediafile_url"].value=="" && mfb["External_Media"].value==""){alert("'.$this->page->lang_l('please, either select or upload media file').'");return false};';
			$this->page_scripts.='}';
		}

		if($this->page->innova_on_output)
			$output=Builder::appendScript($this->page->innova_js,$output);

		if(strpos($output,'id="creation_date"')!==false || strpos($output,'.sortable(')!==false)
			$output=Builder::includeDatepicker($output,$this->page->month_name,$this->page->day_name,array('creation_date','unpublished_date'));

		parent::screen_output($output);
	}

	protected function build_menu($caption)
	{
		global $db;

		$data=array();
		$url_base=$this->page->script_path.'?action=';

		$data[]=Navigation::addEntry($this->page->lang_l('write post'),$url_base.'postentry',$this->page->action_id=='postentry','write_post');
		$data[]=Navigation::addEntry($this->page->lang_l('posts'),$url_base.'index',in_array($this->page->action_id,array('index','duplicate','mail_entry','edit_entry','unpub_entry','pub_entry')),'posts');

		if($this->page->edit_own_posts_only==false || $this->page->user->isMainAdmin())
		{
			$data[]=Navigation::addEntry($this->page->lang_l('categories'),$url_base.'categories',$this->page->action_id=='categories','categories');
			if($this->page->pg_settings['enable_comments'])
				$data[]=Navigation::addEntry($this->page->lang_l('comments'),$url_base.'comments',$this->page->action_id=='comments','comments');
			if(!$this->page->all_settings['disable_trackbacks'])
				$data[]=Navigation::addEntry($this->page->lang_l('trackbacks'),$url_base.'trackbacks',$this->page->action_id=='trackbacks','trackbacks');
		}

		if($this->page->user->isAdminOnPage($this->page->pg_id))
		{
			$data[]=Navigation::addEntry($this->page->lang_l('other settings'),$url_base.'settings',$this->page->action_id=='settings','settings');
                        $data[]=$this->page->user->isAdmin()?
                            Navigation::addEntry($this->page->lang_l('administration panel'),$this->page->ca_url_base.'?process=index',false,'administration','','last'):
                            Navigation::addEntry($this->page->lang_l('sitemap'),$this->page->ca_url_base.'?process=myprofile&amp;pageid='.$this->page->pg_id,false,'sitemap','','last');
			$data[]=Navigation::addEntry($this->page->lang_l('logout'),$this->page->ca_url_base.'?process=logoutadmin&amp;pageid='.$this->page->pg_id,false,'logout',$this->page->user->getUname(),'a_right');
		}
		else
		{
			if(CA::getDBSettings($db,'landing_page')==1)
				$data[]=Navigation::addEntry($this->page->lang_l('profile'),$this->page->ca_url_base.'?process=editprofile&amp;pageid='.$this->page->pg_id,false,'profile','','last');
			else
				$data[]=Navigation::addEntry($this->page->lang_l('sitemap'),$this->page->ca_url_base.'?process=myprofile&amp;pageid='.$this->page->pg_id,false,'sitemap','','last');
			$data[]=Navigation::addEntry($this->page->lang_l('logout'),$this->page->ca_url_base.'?process=logout&amp;pageid='.$this->page->pg_id,false,'logout',$this->page->user->getUname(),'a_right');
		}

		$output=Navigation::admin2($data,$caption,$this->page->action_id=='page_view',$this->page->page_info[0],$this->page->script_path);
		return $output;
	}

}

class manage_settings_screen extends blog_admin_screens
{

	public function handle_screen()
	{
		if(isset($_REQUEST['reindex']))
			$this->reindex();
		if(isset($_POST['submit']))
			$this->save_settings();

		$this->mng_settings();
	}

	protected function reindex()
	{
		global $db;

		$success=false;
		$all=$db->fetch_all_array('SELECT entry_id FROM '.$db->pre.$this->page->pg_pre.'posts');
		foreach($all as $v)
			$success=$this->page->reindex_search($v['entry_id']);

		echo $success===false?'Is this blog hidden in search?':$this->page->lang_l('reindexed');
		exit;
	}

	protected function save_settings()
	{
		$settings=array(
			'weight_forusers'=>(isset($_POST['weight_forusers']))?'1':'0',
			'concept_mode'=>(isset($_POST['concept_mode']))?'1':'0',
			'disable_trackbacks'=>(isset($_POST['disable_trackbacks']))?'1':'0',
			'mbox_grouped'=>(isset($_POST['mbox_grouped']))?'1':'0',
			'public_rss'=>(isset($_POST['public_rss']))?'1':'0',
			'unique_titles'=>(isset($_POST['unique_titles']))?'1':'0',
			'translit'=>(isset($_POST['translit']))?'1':'0',
			'rem_kw_exc_from_search'=>(isset($_POST['rem_kw_exc_from_search']))?'1':'0',
			'replace_keywords'=>(isset($_POST['replace_keywords']))?'1':'0',
			'youtube_size'=>$_POST['youtube_size'],
			'viewid'=>$_POST['viewid'],
			'catviewid'=>$_POST['catviewid'],
			'tagviewid'=>$_POST['tagviewid'],
			'searchviewid'=>$_POST['searchviewid'],
			'og_desc'=>(isset($_POST['og_desc']))?'1':'0',
			'contentbuilder'=>(isset($_POST['contentbuilder']))?'1':'0',
			'error_404_page'=>$_POST['error_404_page'],
                        'blacklist'=>$_POST['blacklist']
		);
		if(!$this->f->use_linefeed)
			$settings['plink_type']=$_POST['plink_type'];
		$this->page->db_insert_settings($settings);
                if ($this->page->all_settings['blacklist']!='')
                    $this->page->rss->rebuild_rssfeed();                
		echo $this->page->lang_l('settings saved');
		exit;
	}

	protected function mng_settings()
	{
		$plink_type=$this->page->all_settings['plink_type'];
		$end='';

		$table_data=array();
		$table_data[]=array('',Builder::buildCheckbox('public_rss',$this->page->all_settings['public_rss'],$this->page->lang_l('public rss')));
		$table_data[]=array('',Builder::buildCheckbox('mbox_grouped',$this->page->all_settings['mbox_grouped'],$this->page->lang_l('group multibox')));
		$table_data[]=array('',Builder::buildCheckbox('weight_forusers',$this->page->all_settings['weight_forusers'],$this->page->lang_l('weight option')));
		$table_data[]=array('',Builder::buildCheckbox('concept_mode',$this->page->all_settings['concept_mode'],$this->page->lang_l('concept mode')));
		$table_data[]=array('',Builder::buildCheckbox('og_desc',$this->page->all_settings['og_desc'],$this->page->lang_l('og description')));
		$table_data[]=array('',Builder::buildCheckbox('disable_trackbacks',$this->page->all_settings['disable_trackbacks'],$this->page->lang_l('disable trackbacks')));
		$table_data[]=array('',Builder::buildCheckbox('unique_titles',$this->page->all_settings['unique_titles'],$this->page->lang_l('unique_title')));
		$table_data[]=array('',Builder::buildCheckbox('rem_kw_exc_from_search',$this->page->all_settings['rem_kw_exc_from_search'],$this->page->lang_l('rem_kw_exc_from_search')));
		$table_data[]=array('',Builder::buildCheckbox('replace_keywords',$this->page->all_settings['replace_keywords'],$this->page->lang_l('replace keywords')));
		$table_data[]=array('',Builder::buildCheckbox('translit',$this->page->all_settings['translit'],$this->page->lang_l('translit')));
		if(!file_exists($this->page->rel_path.'contentbuilder/scripts/contentbuilder.js'))
			$table_data[]=array('','<span class="rvts8 a_editnotice">'.$this->page->lang_l('enable contentbuilder').'</span>');
		else
			$table_data[]=array('',Builder::buildCheckbox('contentbuilder',$this->page->all_settings['contentbuilder'],$this->page->lang_l('contentbuilder')));
		$res_array=array('480x385'=>'480x385','560x340'=>'560x340','640x385'=>'640x385','853x505'=>'853x505','1280x745'=>'1280x745');
		$table_data[]=array($this->page->lang_l('youtube embed size'),Builder::buildSelect('youtube_size',$res_array,$this->page->all_settings['youtube_size'],'','','',''));
		if(count($this->page->pg_settings['views']))
		{
			$table_data[]=array($this->page->lang_l('default entry view'),$this->page->build_view_combo($this->page->all_settings['viewid']));
			$table_data[]=array($this->page->lang_l('default category view'),$this->page->build_view_combo($this->page->all_settings['catviewid'],'catviewid'));
			$table_data[]=array($this->page->lang_l('default tags view'),$this->page->build_view_combo($this->page->all_settings['tagviewid'],'tagviewid'));
			$table_data[]=array($this->page->lang_l('default search view'),$this->page->build_view_combo($this->page->all_settings['searchviewid'],'searchviewid'));
		}

		$table_data[]=array($this->page->lang_l('error 404 page'),
			Builder::buildInput('error_404_page',$this->page->all_settings['error_404_page'],'width:99%;','','','').'<br/><span class="rvts8"><i>'
			.$this->page->lang_l('Redirect customers to 404 error page if entry typed in browser is wrong. Only name of the page is needed which <b>must be located in web root</b>. Example: 404page.html').'</i></span>');
		$table_data[]=array($this->page->lang_l('blacklist'),
			Builder::buildInput('blacklist',$this->page->all_settings['blacklist'],'width:99%;','','textarea',''));
                               
		if(!$this->f->use_linefeed)
		{
			$request=dirname(Linker::requestUri()).'/';
			$pagelink=str_replace('/'.$this->page->pg_name,'',$this->page->full_script_path);
			$plink='<div style="padding: 10px;">
			<input class="forminput" type="radio" name="plink_type" value="default" onclick="$(\'#htaccess_month_title\').hide();" ' .($plink_type=='default'?'checked="checked"':'').'>'
			.CA::formatCaption(' Default - ').CA::formatNotice($this->page->full_script_path."?entry_id=".time() ."&amp;title=post-title</i>").F_BR
			.'<input class="forminput" type="radio" name="plink_type" value="default_onlyid" onclick="$(\'#htaccess_month_title\').hide();" ' .($plink_type=='default_onlyid'?'checked="checked"':'').'>'
			.CA::formatCaption(' Only Id - ').CA::formatNotice($this->page->full_script_path.'?entry_id='.time().'</i>').F_BR
			.'<input class="forminput" type="radio" name="plink_type" value="month_name" onclick="$(\'#htaccess_month_title\').show();" '.($plink_type=='month_name'?'checked="checked"':'').'>'
			.CA::formatCaption(' Month and title - ').CA::formatNotice($pagelink.'/'.date('Y',time()).'/'.date('m',time()).'/post-title/').F_BR
			.'<input class="forminput" type="radio" name="plink_type" value="title" onclick="$(\'#htaccess_month_title\').show();" '.($plink_type=='title'?'checked="checked"':'').'>'
			.CA::formatCaption(' Title - ').CA::formatNotice($pagelink.'/post-title/').'</div>
			<div id="htaccess_month_title" style="display:'.($plink_type=='month_name'||$plink_type=='title'?'block':'none').';padding: 0 33px;">
	 			 <em class="rvts8" style="color: red;">
	   			 In order to have this feature work, you will have to manually create file .htaccess in '.$request.' directory on server and put the following content inside this file:</em>'.F_BR.F_BR.'
	 				  <span class="rvts8">&lt;IfModule mod_rewrite.c&gt;'.F_BR .'RewriteEngine On'.F_BR.'RewriteBase '.$request.F_BR.'RewriteRule ^'.str_replace('.php','',$this->page->pg_name) .'\.php$ - [L]'.F_BR.'RewriteCond %{REQUEST_FILENAME} !-f'.F_BR .'RewriteCond %{REQUEST_FILENAME} !-d'.F_BR.'RewriteRule . '.$request .$this->page->pg_name .' [L]'.F_BR.'&lt;/IfModule&gt;</span>
			</div>';
			$table_data[]=array($this->page->lang_l('permalink'),$plink);
		}

		$end='
		  <div style="float:left">
			 <input type="button" onclick="$.post(\''.$this->page->pg_name.'\',$(\'#blog_settings\').serialize(),function(data) {$(\'#save_result\').html(data)});" value=" '.$this->page->lang_l('save').' ">
          <input type="button" onclick="$.post(\''.$this->page->pg_name.'\',{action:\'settings\',reindex:\'1\'},function(data) {$(\'#save_result\').html(data)});" value=" '.$this->page->lang_l('reindex').' ">
          <span id="save_result" class="rvts8"></span>
			 <input type="hidden" name="action" value="settings">
			 <input type="hidden" name="submit" value="true">
          <input type="button" value="'.$this->page->lang_l('wp import').'" onclick="$(\'#wp_import_div\').css(\'display\',\'block\');">
		  </div>
		  <div id="wp_import_div" style="display:none; float:left">
				<input type="file" id="wp_import_file" name="wp_import_file">
            <input type="button" value="'.$this->page->lang_l('upload').'" onclick="wp_post(\''.$this->page->rel_path.'\',\''.$this->page->pg_name.'\')">
            <span style="margin-left:3px;" id="imported_file" class="rvts8"></span>
		  </div>';
		$js_wp_import='
		  var error_type_wp_import=true;
		  $(function() {
				$(\'#wp_import_file\').change( function() {
					 var filename = $(this).val();
					 if ( ! /\.xml$/.test(filename)) {
						  alert(\'Please select a xml file\');
						  window.error_type_wp_import=false;
					 }else{
						  window.error_type_wp_import=true;
					 }
				});
		  });';
		$this->page->page_scripts.=$js_wp_import;
		$output=Builder::addEntryTable($table_data,$end,'','',false,'','','<form id="blog_settings" action="'.$this->page->script_path.'?action=settings" method="post" enctype="multipart/form-data">');
		$this->page_scripts.='$(document).ready(function(){$(".forminput").change(function(){$("#save_result").html("")}); });';
		$this->output($output);
	}
}

class manage_trackbacks_screen extends blog_admin_screens
{

	public function handle_screen()
	{
		$access_all_flag=($this->page->edit_own_posts_only==false);
		if($access_all_flag)
		{
			$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
			if(isset($_REQUEST['tb_id']))
				$trackback_id=intval($_REQUEST['tb_id']);
			if($do=='delete' || $do=='spam')
			{
				if($do=='spam')
					$this->page->blockedIpModule->ip_blocking();
				$this->page->trackbacks->delete_trackback($trackback_id);
			}
			elseif($do=='toggle' || $do=='approve' || $do=='unapprove')
				$this->page->trackbacks->toggle_trackback($trackback_id,$do);
			elseif($do=='export')
				$this->export_trackbacks();
			elseif($do=='deletefromip')
				$this->page->trackbacks->delete_trackbacks_fromId();
		}

		return $this->manage_trackbacks();
	}

	protected function export_trackbacks()
	{
		 global $db;

		 $data=$db->fetch_all_array('
			SELECT date,excerpt
			FROM '.$db->pre.$this->page->pg_pre.'trackbacks
			ORDER BY date DESC ');

		 $output='';

		 foreach($data as $v)
		 	$output.='"'.str_replace('"','""',$v['date']).'","'.str_replace('"','""',$v['excerpt']).'"'."\r\n";

		output_generator::sendFileHeaders("trackbacks.csv");
		echo $output;
		exit;
	}

	protected function manage_trackbacks() // manage trackbacks
	{
		global $db;

		$data_count=$this->page->db_count('trackbacks');
		$start=($this->page->c_page-1)*Navigation::recordsPerPage();

		$data=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.$this->page->pg_pre.'trackbacks
			ORDER BY date DESC '
			.(($data_count>Navigation::recordsPerPage() && Navigation::recordsPerPage()!=0)?
			'LIMIT '.$start.', '.Navigation::recordsPerPage().'':''));

		$append='<input type="button" value=" '.$this->page->lang_l('check blocked ips').' " onclick="document.location=\''.$this->page->script_path.'?action=comments&amp;check_blockedip=1\'">
					<input type="button" value=" '.$this->page->lang_l('export').' " onclick="document.location=\''.$this->page->script_path.'?action=trackbacks&amp;do=export\'">
					<br>';
		$output='';
		$r=Linker::buildReturnURL(false);
		if(!empty($data))
		{
			$nav=Navigation::pageCA($data_count,$this->page->script_path.'?action=trackbacks',0,$this->page->c_page);
			$cap_arrays=array($this->page->lang_l('pinging blog'),$this->page->lang_l('title').'&'.$this->page->lang_l('excerpt'),$this->page->lang_l('ip').'&'.$this->page->lang_l('host'), $this->page->lang_l('status'));
			$table_data=array();
			$blocked_ips=$this->page->blockedIpModule->db_blockedips($db);

			foreach($data as $value)
			{
				$tb_id=$value['trackback_id'];
				$tb_url=$value['url'];
				$tb_ip=$value['ip'];

				$uft_flag=(strpos(Formatter::strToLower($this->page->pg_settings['page_encoding']),'utf')!==false? true: false);
				$tobeap=($value['approved']==0? false: true);
				$entry_nav=array(
					 $this->page->lang_l('delete')=>$this->page->script_path.'?action=trackbacks&amp;do=delete&amp;tb_id='.$tb_id.'&amp;r='.Linker::buildReturnURL(false).'" onclick="javascript:return confirm(\''.$this->page->lang_l('del trackback msg').'\')"',
					 $this->page->lang_l($tobeap?'unapprove':'approve')=>$this->page->script_path.'?action=trackbacks&amp;do='.($tobeap?'unapprove':'approve').'&amp;tb_id='.$tb_id);
				if($tobeap)
					$entry_nav[$this->page->lang_l('spam')]=$this->page->script_path.'?action=trackbacks&amp;do=spam&amp;tb_id='.$tb_id.'&amp;ip='.$tb_ip;

				$ping_blog_url=(strpos($tb_url,'?')!==false? substr($tb_url,0,strpos($tb_url,'?')): $tb_url);

				$pingsrc='<a class="rvts12" style="text-decoration:none;" href="'.Formatter::sth2($ping_blog_url).'">'.Formatter::strToUpper(Formatter::sth($value['blog_name'])) .'</a><br>
				<span class="rvts8" style="white-space:nowrap">'.Formatter::mySubstr($this->page->month_name[date('n',Date::tzoneSql($value['date']))-1],0,3,$uft_flag)
				.Date::formatTimeSql($value['date'],$this->page->pg_settings['time_format'],'long').'</span>';

				$entry='<a class="rvts12" style="text-decoration:none;" href="'.Formatter::sth2($tb_url).'">' .Formatter::sth2(html_entity_decode($value['title'],ENT_QUOTES)).'</a><br>
					 <p class="rvts8" style="word-wrap: break-word;max-width:600px;">'.Formatter::sth2(html_entity_decode($value['excerpt'],ENT_QUOTES)).'</p>';

				$iphost_nav=array();
				$iphost=(!empty($value['ip'])?Builder::ipLocator($value['ip']):'').F_BR;
				$iphost.=($value['ip']!=$value['host'] && !empty($value['ip']))?'<span class="rvts8">'.$value['host'].'</span>':'';
				if(!empty($value['ip']))
				{
					$ipb=array_search($value['ip'],$blocked_ips)!==false;
					$iphost_nav[$this->page->lang_l($ipb?'unblock ip':'block ip')]=$this->page->script_path.'?action=trackbacks&amp;do='.(($ipb)?'unblockip':'blockip').'&amp;ip='.$value['ip'];
					$iphost_nav['delete all from this ip']=$this->page->script_path.'?action=trackbacks&amp;do=deletefromip&amp;ip='.$value['ip'].'&amp;r='.$r;
				}
				$status='<span class="rvts8">'.$this->page->lang_l($value['approved']==0?'unapproved':'approved').'</span>';

				$row_data=array($pingsrc,array($entry,$entry_nav),array($iphost,$iphost_nav),$status);
				$table_data[]=$row_data;
			}
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$append);
		}
		else
		{
			$table_data[]=array('','<span class="rvts8">'.$this->page->lang_l('no trackbacks').'</span>');
			$output=Builder::adminTable('',array(),$table_data,$append);
		}
		$this->output($output);
	}
}

class manage_comments_screen extends blog_admin_screens
{

	public function handle_screen()
	{
		$output='';
		$entry_id=isset($_REQUEST['entry_id'])?intval($_REQUEST['entry_id']):0;
		if(isset($_REQUEST['comment_id']))
			$comment_id=intval($_REQUEST['comment_id']);
		$access_all_flag=($this->page->edit_own_posts_only==false);
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if($do=='reply' && isset($_POST['submit']) && $entry_id>0)
		{
			$this->page->commentModule->reply_comment($entry_id);
			$this->page->setState('reply_comment');
		}
		elseif(($do=='toggle' || $do=='approve' || $do=='unapprove') && $access_all_flag)
			$this->page->commentModule->toggle_comment($comment_id,$do);
		elseif($do=='spam' && $access_all_flag)
		{
			$this->page->commentModule->spam_comment($comment_id);
			exit;
		}
		elseif($do=='delete' && isset($comment_id) && $access_all_flag)    // delete comment
		{
			$this->page->commentModule->del_comment($comment_id,true);
			$this->page->setState('del_comment');
		}
		elseif($do=='check_blockedip' && $access_all_flag)
		{
			$blocked_ip = new blocked_ip_screen($this->page);
			$blocked_ip->handle_screen();
			exit;
		}

		if($access_all_flag)	// manage comments
		{
			if(isset($_POST['Post']))
				$output=$this->page->commentModule->edit_comment($entry_id,'full_access');
			else
				$output=$this->page->manage_comments();
		}

		$this->output($output);
	}
}

class edit_entry_screen extends blog_admin_screens
{
	public function handle_screen()
	{
		$entry_id=isset($_REQUEST['entry_id'])?intval($_REQUEST['entry_id']):0;
		$access_all_flag=($this->page->edit_own_posts_only==false);
		if($this->page->action_id=='postentry')
		{
			$output=$this->edit_entry_form();
			$this->output($output);
		}
		elseif($this->page->action_id == 'edit_entry' && $entry_id>0)
		{
			$entries_records=$this->page->db_fetch_entry($entry_id);
			if($access_all_flag || $entries_records['posted_by']==$this->page->user->getId())
			{
				$output=$this->edit_entry_form($entries_records);
				$this->output($output);
			}
		}
		elseif(isset($_POST['save_entry']) || isset($_POST['add_category']))
		{
			$output=$this->page->entries->save_entry();
			$this->page->categoriesModule->build_categories_list(true,0,1);
			Linker::checkReturnURL();
			$manage_entries = new manage_entries_screen($this->page);
			$manage_entries->handle_screen();
		}
		elseif($this->page->action_id=='toggle_hidden')
		{
			$hideHidden=$this->page->get_setting('hideHidden')=='1'?'0':'1';
			$this->page->db_insert_settings(array('hideHidden'=>$hideHidden));
			echo $this->page->get_setting('hideHidden');
			exit;
		}
	}

	private function add_image_field($label,$fname,$data,$notice)
	{
		$ima='';
		$v=isset($data[$fname])?$this->page->get_file_url($data[$fname],$this->page->rel_path):'';
		$ext=substr($v,strrpos($v,".")+1);
		$file_name=substr($v,strrpos($v,"/")+1);

		if($v=='' || in_array(Formatter::strToLower($ext),$this->page->img_file_types))
				$ima.=F_BR.'<img id="ima_'.$fname.'" src="'.$v.'" alt="" style="'.(($v=='')?'display:none;':'').'height:60px;padding-top: 5px;">';
			elseif($data[$fname]!='' && (!in_array(Formatter::strToLower($ext),$this->page->media_types)) && (strpos($v,'youtube.')===false))
				$ima.="<p><a class='rvts12' href='".$this->page->rel_path.str_replace($file_name,rawurlencode($file_name),$v)."'>".Formatter::sth($file_name).'</a></p><br>';

		return array(true,$label,'',
				Builder::buildInput($fname,$v,$this->page->inp_width,'','text',' id="'.$fname.'" onchange="fixima(this.value,\''.$fname.'\');"')
				.' <input class="input1" type="button" name="btnAssepage-title" id="btnAsset2" onclick="openAsset(\''.$fname.'\')" value=" '.$this->page->lang_l('browse').' ">'
				.$ima.$notice);
	}

	public function edit_entry_form($data=null,$simple=false,$fpage=false,$mini=false,$category_id=null)  // edit_entry form
	{
		$cancel_loc=Linker::checkReturnURL(true)?Linker::checkReturnURL(true,true):$this->page->script_path."?action=index".$this->page->c_page_amp;
		$contentbuilder=$this->page->all_settings['contentbuilder'];
		$addnew=$data==null || !isset($data['entry_id']);
		if($addnew)
		{
			$template=$this->page->db_fetch_entry(0);
			if($template!==false)
			{
				foreach($template as $k=>$v)
					if($k!='entry_id' )
						$data[$k]=$v;
				$data['publish_status']='published';
			}
			$tit='';
		}
		else
		{
			$tit='<input type="hidden" name="entry_id" value="'.$data['entry_id'].'"><input type="hidden" name="modified_date" value="'.strtotime($data['modified_date']).'">';
			if(!$this->page->is_blog)
				$tit.='<input type="hidden" name="mediafile_size" value="'.$data['mediafile_size'].'">';
		}
		$textarea_content=isset($data['content'])?Formatter::sth2($data['content']):'';
		if($contentbuilder&&$mini)
		{
			$editor_parsed=Editor::getContentBuilder_js($this->page->rel_path,!$addnew?$data['entry_id']:'1');
			$ta='<div id="contentarea'.(!$addnew?$data['entry_id']:'1').'" class="containerCB" style="width:100%;height:auto;">'.$textarea_content.'</div>'.$editor_parsed;
			$this->page->page_css.='.containerCB{min-height:300px !important;border:1px inset}';
			Editor::getContentBuilder_scripts($this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies,true,$this->page->is_blog);
			$lang=isset($this->page->pg_settings['ed_lang'])?$this->page->pg_settings['ed_lang']:'english';
			Editor::addSlideshow_Plugin_contentBuilder($this->page->page_scripts,$this->page->page_dependencies,$this->page->rel_path,$lang,$this->page->lang_l('slideshow'),$this->page->pg_id);
		}
		else
		{
			$textarea_content=Editor::replaceClassesEdit($textarea_content);
			$this->page->innova_js=Editor::addGoogleFontsToInnova($textarea_content,$this->page->innova_js);
			Editor::addPolldropbox_InnovaEditor($this->page->innova_def,$this->page->innova_js);
			Editor::addSlideshow_Plugin_Editor($this->page->innova_def,$this->page->innova_js,$this->page->rel_path,$this->page->pg_settings['ed_lang'],$this->page->lang_l('slideshow'),$this->page->pg_id);
			Editor::addhtml5Player_Plugin_Editor($this->page->innova_def,$this->page->innova_js,$this->page->rel_path,$this->page->pg_settings['ed_lang'],$this->page->lang_l('html5player'),$this->page->pg_id);
			$ta='<textarea class="input1'.($this->f->tiny?' mceEditor':'').'" id="htmlarea" name="content" style="width:100%;height:'.(!$this->page->is_blog || $mini?"220":"350").'px">'.$textarea_content.'</textarea>';
			$ta.=$this->page->innova_def;
		}

		$this->page->innova_on_output=true;
		if($mini)
		{
			$this->page->c_page_amp.=Linker::buildReturnURL();
			reset($this->page->categoriesModule->category_array);

			$output='
				<div id="edit_entry_form">
					<form action="'.$this->page->script_path."?action=index".$this->page->c_page_amp.'" method="post" enctype="multipart/form-data" id="b_edit_form" name="b_edit_form"
					'.($contentbuilder?' class="cb_form_'.(!$addnew?$data['entry_id']:'1').'" onsubmit="return save_cb('.(!$addnew?'\''.$data['entry_id'].'\'':'1').')"':'').'>
						  <input type="text" style="width:84%;margin:5px;" value="" name="title" placeholder="'.$this->page->lang_l('title').'" class="input" required>
						  <div class="content_editor">'.
								$ta.'
								<input type="hidden" name="save_simple" value="1">'
								.(!$addnew?'<input type="hidden" name="editing_entry" value="'.$data['entry_id'].'">':'').'
								<input type="hidden" name="category" value="'.($category_id===false||!isset($this->page->categoriesModule->category_array[$category_id])?key($this->page->categoriesModule->category_array):$category_id).'">
								<input type="hidden" name="save_entry" value="1">
								<input class="input1'.($contentbuilder?' save_button':'').'" onclick="return vForm();" type="submit" value=" '.$this->page->lang_l('save').' ">
						  </div>
					</form>
				</div>';
			$output=str_replace('height="350px"','height="100px"',$output);
			return $output;
		}

		if($simple)
			$ta='<div id="edit_content-1" class="content_editor" style="display:block;text-align:left;clear:both;">'.$ta.'</div>';
		$table_data=array();

		$tit.=Builder::buildInput(
						'title',
						isset($data['title'])? Formatter::sth($data['title']):"",
						$this->page->inp_width,
						'',
						'text',
						$this->page->all_settings['translit']?' onkeyup="translitTo($(this).val(),\'permalink\')" required':'required'
						);
		$c=$this->page->lang_l('title').$this->f->fmt_star;
		$hideHidden=$this->page->get_setting('hideHidden');
		$table_data[]=$simple?array($c,$tit):array(false,$c,'',$tit,'','<span class="hide_toggler ui_icon fa fa-toggle-'.($hideHidden?'off':'on').'"></span>');

		if($addnew && isset($_GET['category']))
		{
			$category_id=$this->page->categoriesModule->get_categoryid();
			if($category_id!==false)
				$data['category']=$category_id;
		}
		$cat=$this->page->categoriesModule->build_category_combo($this->page->lang_l('all categories'),'category',intval($data['category']),''," style='".$this->page->inp_width."' ",false,0,'id');
		if(!$simple && !$this->page->edit_own_posts_only)
			$cat.='&nbsp;<input type="button" class="input1" value=" + " title="'.$this->page->lang_l('add category').'" onclick="$(\'#new_cat\').toggle();">'.
				F_BR.$this->page->categoriesModule->quick_addcategory();

		$cat.=($simple?'<input type="hidden" name="save_simple" value="1">':'')
			.(Linker::checkReturnURL(true)?'<input type="hidden" name="r" value="'.Linker::checkReturnURL(true).'">':'');
		$c=$this->page->lang_l('category');
		$table_data[]=$simple?array($c,$cat):array(!$simple,$c,'',$cat);

		//check if it's only the editor requested via ajax.
		if(isset($_REQUEST['ar']) && intval($_REQUEST['ar']) == 1)
		{
			print $textarea_content;
			exit;
		}

		if(!$addnew)
			$ta.=CA::formatNotice($this->page->lang_l('posted on').": ".$this->page->month_name[date('n',Date::tzoneSql($data['creation_date']))-1]
			.Date::formatTimeSql($data['creation_date'],$this->page->pg_settings['time_format'],'long')."&nbsp;&nbsp;"
			.(!empty($data['posted_by'])? $this->page->lang_l('posted by').': '.Formatter::strToUpper(User::getUserName($data['posted_by'], $this->page->rel_path)):'').'&nbsp;&nbsp;'
			.$this->page->lang_l('last modified on').": ".$this->page->month_name[date('n',Date::tzoneSql($data['modified_date']))-1]
			.Date::formatTimeSql($data['modified_date'],$this->page->pg_settings['time_format'],'long'));

		$k='mediafile_url';
		$m_file=isset($data['mediafile_url'])?$this->page->get_file_url($data['mediafile_url'],$this->page->rel_path):'';
		$yt=Video::youtube_vimeo_check($m_file);
		if(!$this->page->is_blog)
		{
			if(isset($data['duration']))
				$duration_array=explode(':',$data['duration']);
			else
				$duration_array=array(
					 isset($data['Hour'])?$data['Hour']:date('H'),
					 isset($data['Min'])?$data['Min']:date('i'),
					 isset($data['Sec'])?$data['Sec']:date('s'));

			$md=Builder::buildInput($k,($yt || empty($m_file)?'':$m_file),$this->page->inp_width,'','text',' id="'.$k.'" onchange="fixima(this.value,\''.$k.'\');"')
			.' <input class="input1" type="button" name="btnAsset" id="btnAsset" onclick="openAsset(\''.$k.'\')" value=" '
			.$this->page->lang_l('browse').' ">';
			$md.='<br><span class="rvts8">'.$this->page->lang_l('advanced player notice').'</span><br><br>';

			if(!isset($data['mediafile_size']))
				$data['mediafile_size']=0;
			$h_a=array('00','01','02','03','04','05','06','07','08','09','10');
			$f_min_sec=array();
			for($n=0; $n<60; $n++)
				$f_min_sec[]=($n<10)?'0'.strval($n):strval($n);
			$md.='<span class="rvts8 a_editcaption">'.$this->page->lang_l('duration').'</span><br>'
				.Builder::buildSelect('Hour',$h_a,$duration_array[0],'','value').'<span class="rvts8"> h</span> '
				.Builder::buildSelect('Min',$f_min_sec,$duration_array[1],'','value').'<span class="rvts8"> min </span> '
				.Builder::buildSelect('Sec',$f_min_sec,$duration_array[2],'','value').'<span class="rvts8"> sec </span><span class="rvts8">'
				.($data['mediafile_size']>0?CA::formatCaption($this->page->lang_l('size').' '.round($data['mediafile_size']/1024).' KB '):' ').' &nbsp;</span>';
			$explicit=array('no','clean','yes');
			$md.=CA::formatCaption($this->page->lang_l('explicit')).'&nbsp;&nbsp;'
				.Builder::buildSelect('explicit',$explicit,(isset($data['explicit'])?$data['explicit']:"no"),'','value').'&nbsp;&nbsp;
				<span class="rvts8">'.$this->page->lang_l('block').'</span>
				<input type="checkbox" name="block" value="block" '.(isset($data['block'])&& $data['block']=="yes"?"checked='checked'":"").'>
				<span class="rvts8">'.$this->page->lang_l('allow_downloads').'</span>
				<input type="checkbox" name="allow_downloads" value="allow_downloads" '.($data==null || isset($data['allow_downloads'])&& $data['allow_downloads']=='1'?"checked='checked'":"").'><br>
				<span class="rvts8 a_editcaption">'.$this->page->lang_l('author').'</span><br>'.
				Builder::buildInput('author',isset($data['author'])?Formatter::sth($data['author']):"",$this->page->inp_width);
			if(!$simple)
				$table_data[]=array(true,$this->page->lang_l('select media file'),'',$md);

			$in=Builder::buildInput('subtitle',isset($data['subtitle'])? Formatter::sth($data['subtitle']):"",$this->page->inp_width);
			$c=$this->page->lang_l('subtitle');
			$table_data[]=$simple?array($c,$in):array(true,$c,'',$in);
		}

		$c=$this->page->lang_l(!$this->page->is_blog?'description':'content');
		$table_data[]=$simple?array($c,$ta):array(false,$c,'',$ta);

		$exc="<textarea class='input1".($this->f->tiny && $this->page->is_blog?' mceNoEditor':'')."' name='excerpt' style='".$this->page->inp_width."height:70px'>".(isset($data['excerpt'])?Formatter::sth($data['excerpt']):'')."</textarea>".F_BR;
		if(!$simple)
			$table_data[]=array(true,$this->page->lang_l('excerpt'),'',$exc);

		$kwds = isset($data['keywords'])? Formatter::sth($data['keywords']):'';
		$tags_line=Builder::buildInput('keywords',$kwds,$this->page->inp_width,'','text','','','','keywords',true);
		$tags_line.=CA::formatNotice($this->page->lang_l('tags_delimiter'));
		$c=$this->page->lang_l('tags').' ('.$this->page->lang_l('keywords').')';
		$table_data[]=$simple?array($c,$tags_line):array(!$simple,$c,'',$tags_line);

		if(!$simple)
		{
			$table_data[]=array(true,$this->page->lang_l('free field'),'',
				 Builder::buildInput('free_field',isset($data['free_field'])? Formatter::sth($data['free_field']):'',$this->page->inp_width)
				 );
			if($this->page->use_alt_plinks)
				 $table_data[]=array(true,$this->page->lang_l('permalink'),'',
						'<input id="permalink" type="text" style="'.$this->page->inp_width.'" value="'.(isset($data['permalink'])? Formatter::sth($data['permalink']):'').'" name="permalink" class="input1">'
				 );
		}

	 	$c=$this->page->lang_l('video');
		$table_data[]=$simple?array($c,$in):array(true,$c,'',
			 Builder::buildInput('External_Media',!$this->page->is_blog?(!$yt?'':$m_file):$m_file,$this->page->inp_width));

		if(!$simple)
		{
			$data['image_thumb']=isset($data['mediafile_url'])?(Video::youtube_vimeo_check($data['mediafile_url'])?Video::getVideoImage($data['mediafile_url']):''):'';

			$table_data[]=$this->add_image_field($this->page->lang_l('upload file'),'image_url',$data,($this->page->is_blog? F_BR.CA::formatNotice($this->page->lang_l('media file note')):''));
			$table_data[]=$this->add_image_field($this->page->lang_l('rss image'),'image_rss',$data,F_BR.CA::formatNotice($this->page->lang_l('leave empty to use main image')));

			$table_data[] = Slideshow::buildSlideShow_editor($data,true,$this->page->lang_l('slideshow'),$this->page->lang_l('browse'));

			if($this->page->fl_studio_flag && $this->page->is_blog)
			{
				$table_data[]=array(true,'Category domain (for RSS)','',
					 Builder::buildInput('rss_domain_info',
								(!empty($data['rss_domain_info '])? Formatter::sth($data['rss_domain_info ']):(isset($_POST['rss_domain_info'])? $_POST['rss_domain_info'] :'')),
								$this->page->inp_width));
				$table_data[]=array(true,'Link url (for RSS)','',
					 Builder::buildInput('rss_link_info',
						  (!empty($data['rss_link_info'])? Formatter::sth($data['rss_link_info']):(isset($_POST['rss_link_info'])? $_POST['rss_link_info'] :'')),
						  $this->page->inp_width));
			}

			$pinged_urls=(isset($data['pinged_url'])? explode(' ', $data['pinged_url']): '');
			$pi='';
			if(!empty($pinged_urls) && !$addnew)
			{
				foreach($pinged_urls as $k=>$vvv)
					$pi.='<span class="rvts8">'.$vvv.'</span><br>';
				$pi='&nbsp;<a class="rvts12" href="javascript:void(0);" onclick="javascript:sv(\'al_pin\');">'.$this->page->lang_l('already pinged').'</a><br>
					<div id="al_pin" style="display:none;"><br>'.$pi.'</div>';
			}
			if(!$this->page->all_settings['disable_trackbacks'])
				$table_data[]=array(true,
					$this->page->lang_l('send trackback to'),'',
					'<input class="input1" type="text" name="Ping_urls" value="'.(isset($data['pinged_url'])?$data['pinged_url']:'').'" style="'.$this->page->inp_width.'">'
					.$pi
				);

			if($this->page->pg_settings['enable_comments'])
			{
				$dt='<input type="checkbox" name="allow_comments" value="true" '
				.(isset($data['allow_comments']) && $data['allow_comments']=='false'? '': "checked='checked'").'>'.CA::formatCaption($this->page->lang_l('allow comments'))
				.F_BR;
				if(!$this->page->all_settings['disable_trackbacks'])
					$dt.=' <input type="checkbox" name="allow_pings" value="true" '
					.(isset($data['allow_pings']) && $data['allow_pings']=='true'? "checked='checked'": '').'>'
					.CA::formatCaption($this->page->lang_l('allow trackbacks'));
				$table_data[]=array(true,$this->page->lang_l('discussion').F_BR,'',$dt);
			}
		}
		if($addnew)
		{
			$date=Date::pareseInputDate('creation_date',$this->page->pg_settings['time_format'],$this->page->month_name);
			$cd='<div class="date_edit" style="display:none">'
						.Builder::dateTimeInput('creation_date',$date,$this->page->pg_settings['time_format'],$this->page->month_name).'
					</div>
					<div class="inm">
						<span class="rvts8"><b>'.$this->page->lang_l('immediately').'</b></span>
						<a class="rvts12" href="javascript:void(0);" onclick="$(\'.date_edit\').show();$(\'.inm\').hide();">edit</a>
					</div>';
		}
		else
		{
			$date=Date::tzoneSql($data['creation_date']);
			$cd=Builder::dateTimeInput('creation_date',$date,$this->page->pg_settings['time_format'],$this->page->month_name);
		}

		if(!$simple)
		{
			 $status_select=($this->page->edit_own_posts_only && $this->page->all_settings['concept_mode'])
				 ?'<input type="hidden" name="publish_status" value="pending">
					<input type="edit" value="'.$this->page->entry_status['pending'].'" readonly=readonly>'
				 :Builder::buildSelect('publish_status',$this->page->entry_status,!isset($data['publish_status'])?"published":$data['publish_status']);

			$unpublished=isset($data['unpublished_date']) && strtotime($data['unpublished_date'])>0;

			$cd2=$this->page->lang_l('unpublish').F_BR
				.'<input type="checkbox" name="unpublish_on" value="true" onclick="$(\'.date_unpub\').toggle();"  '
				.(isset($data['unpublished_date']) && $data['unpublished_date']>0? "checked='checked'":'').'>
				<div class="date_unpub" style="float:right;display:'.($unpublished?'block':'none').'">'
					.Builder::dateTimeInput('unpublished_date',$unpublished?Date::tzoneSql($data['unpublished_date']):Date::tzone(time()),$this->page->pg_settings['time_format'],$this->page->month_name)
				.'</div>';

			$use_weight=($this->page->user->isAdmin() || $this->page->all_settings['weight_forusers']);

			$table_data[]=array(true,$this->page->lang_l('publish').F_BR,$this->page->lang_l('status'),$status_select,
				$this->page->lang_l('accessibility'),Builder::buildSelect('accessibility',
				$this->page->entry_accessibility,!isset($data['accessibility'])?"public":$data['accessibility']),
				$use_weight?$this->page->lang_l('weight'):'',
				'<input type="text" name="weight" '.($use_weight?'':'type="hidden"').'title="'.$this->page->lang_l('weight explanation').'" style="width:60px" value="'.(isset($data['weight'])? Formatter::sth($data['weight']):'100').'" id="post_weight" class="input1">',
				$this->page->lang_l('published'),$cd,$cd2,'');
		}

		if(!$addnew)  // author changer
		{
			$is_admin_logged=$this->page->user->isEZGAdminLogged();
			if($is_admin_logged)
			{
				$users=User::mGetUsersPG($this->page->pg_id,$this->page->rel_path,true);
				$users[-1]=$this->f->admin_nickname;
				natcasesort($users);
				$table_data[]=array(true,$this->page->lang_l('author'),'',Builder::buildSelect('posted_by',$users,$data['posted_by']));
			}
		}

		$cancel_onlick=$simple ? 'onclick="javascript:swap(\'post_init_content_0\',\'edit_post_0\');"' : 'onclick="document.location=\''.$cancel_loc.'\'"';
		$end='<input '.($simple?'class="input1" ':'').'name="save_entry" onclick="return vForm();" type="submit" value=" '.$this->page->lang_l('save').' ">'.
			(!$addnew?'<input type="hidden" name="editing_entry" value="'.$data['entry_id'].'">':'').
			' <input '.($simple?'class="input1" ':'').'type="button" value=" '.$this->page->lang_l('cancel').' " '.$cancel_onlick.'>'.F_BR.F_BR;

		$sort=(isset($this->page->all_settings['sort'.$this->page->user->getId()])?$this->page->all_settings['sort'.$this->page->user->getId()]:'');
		if($this->page->user->getId()!='-1' && $sort=='')
			$sort=isset($this->page->all_settings['sort-1'])?$this->page->all_settings['sort-1']:'';
		if($simple)
			$this->page->c_page_amp.=Linker::buildReturnURL();
		$inner=Builder::addEntryTable($table_data,$end,'','',!$fpage,$sort,$this->page->pg_name,'','',1,$hideHidden);

		$output='<br class="ca_br">
			 <div id="edit_entry_form">
				<form action="'.$this->page->script_path.'?action=index'.$this->page->c_page_amp.'" method="post" enctype="multipart/form-data" id="b_edit_form" name="b_edit_form">'
				  .$inner.'
				</form>
			</div>';
		return $output;
	}
}

class manage_entries_screen extends blog_admin_screens
{

	public function handle_screen()
	{
	  $access_all_flag=($this->page->edit_own_posts_only==false);
		$operation_allowed=' AND ('.($access_all_flag?1:0).' OR posted_by='.$this->page->user->getId().')';
		$period_id=isset($_REQUEST['period_id'])?urldecode(trim($_REQUEST['period_id'])):'';
		$entry_id=isset($_REQUEST['entry_id'])?intval($_REQUEST['entry_id']):0;

		if(isset($_REQUEST['del_entry']) || $this->page->action_id=='del_entry')	// delete entries
		{
			$this->page->entries->delete_entry($operation_allowed);
			$this->page->categoriesModule->build_categories_list(true,0,1);
			Linker::checkReturnURL();
			$this->manage_entries($period_id);
		}
		elseif($this->page->action_id=='pub_entry' || $this->page->action_id=='unpub_entry')  // publish/unpublish entry
		{
			$this->page->entries->publish_entry($entry_id,$operation_allowed);
			$this->page->categoriesModule->build_categories_list(true,0,1);
			Linker::checkReturnURL();
			$this->manage_entries($period_id);
		}
		elseif($this->page->action_id=='duplicate' && $entry_id>0)	// duplicate entry
		{
			$this->page->entries->duplicate_entry($entry_id);
			$this->manage_entries($period_id);
			$this->page->setState('duplicate_entry');
		}
		elseif($this->page->action_id == 'mail_entry' && $entry_id>0)
		{
			$mailer_js='';
			$output=$this->page->entries->mail_entry($entry_id,$period_id,$access_all_flag,$mailer_js);
			$this->output($output,$mailer_js);
		}
		else
			$this->manage_entries();
	}

	protected function visitStats()
	{
		global $db;

		$lf="\r\n";
		$output='"hits","title","url"'.$lf;
		$records=$db->fetch_all_array('
			 SELECT title,visits_count,entry_id,permalink
			 FROM '.$db->pre.$this->page->pg_pre.'posts
			 WHERE '.$this->page->where_public.'
			 ORDER BY visits_count DESC ');
		$count=0;

		foreach($records as $v)
		{
			$output.=str_pad($v['visits_count'],7," ",STR_PAD_LEFT).',"'.str_replace('"',"'",$v['title']).'","'.$this->page->build_permalink($v,false,'',true).'"'.$lf;
			$count+=intval($v['visits_count']);
		}

		$output.='-------------------------------------------------------------------'.$lf;
		$output.=str_pad($count,7," ",STR_PAD_LEFT).$lf;

		output_generator::sendFileHeaders("visits.csv");
		echo $output;
		exit;
	}

	protected function entries_admin($data_to_show,$category_id,$period_id,$periods_array,$entries_total,$published_total,$pending_total,$draft_total,$scheduled_total)
	{
		global $db;

		$return_param=Linker::buildReturnURL();
		$prepend=$append='';
		$cap_arrays=$table_data=array();
		$parent =-1;
		$category_name=($category_id!==false)?$this->page->categoriesModule->get_category_info($category_id,'name',$parent):'';

		$uft_flag=strpos(Formatter::strToLower($this->page->pg_settings['page_encoding']),'utf')!==false;
		$curr_status=(isset($_GET['status'])?$_GET['status']:'');
		$amp_st=($curr_status!=''?'&amp;status='.$_GET['status']:'');
		$amp_st1=($curr_status!=''?'&status='.$_GET['status']:'');
		$amp_cat=($category_name!=='')?'&amp;category='.urlencode($category_name):'';
		$amp_per=($period_id!=='')?'&amp;period_id='.$period_id:'';
		$amp_search=(isset($_REQUEST['q']))?'&amp;q='.$_REQUEST['q']:'';

		array_unshift($periods_array,'All periods');
		foreach($periods_array as $v)
			$periods_array_t[$v]=($v=='All periods')?$this->page->lang_l('all periods'):$v;
		$periods_array=$periods_array_t;

		$pass_total=$entries_total;
		if(isset($_GET['status']))
		{
			$status=$_GET['status'];
			if($status=='unpublished')
				$pass_total=$draft_total;
			elseif($status=='pending')
				$pass_total=$pending_total;
			elseif($status=='scheduled')
				$pass_total=$scheduled_total;
			else
				$pass_total=$published_total;
		}
		elseif(isset($_REQUEST['q']))
		{
			$pass_total=count($data_to_show);
			$data_to_show=array_slice($data_to_show,($this->page->c_page-1)*Navigation::recordsPerPage(),Navigation::recordsPerPage());
		}
		elseif(isset($_REQUEST['do']))
			 $this->visitStats();

		$onclick="document.location='".$this->page->script_path."?action=index".$amp_st1."&period_id='+$('#Selected_Period').val()+'&category='+$('#Selected_Category').val();";
		$left_content=$this->page->categoriesModule->build_category_combo($this->page->lang_l('all categories'),'Selected_Category',$category_name,'','',true,1,'name').
		  Builder::buildSelect('Selected_Period',$periods_array,($period_id==''?'All periods':$period_id),'','','','').
		  ' <input type="button" name="filter" class="ca_button" value="&#xf0b0" title="'.$this->page->lang_l('filter').'" onclick="'.$onclick.'">';
		$right_content='<form id="mngEntriesFrm" method="post" action="'.$this->page->script_path.'?action=index" enctype="multipart/form-data">';
		$right_content.=' <input type="text" name="q" value="" > <input class="ca_button" type="submit" value="&#xf002" title=" '.$this->page->lang_l('search').' "></form>';
		$fast_nav_array=array();
		$fast_nav_array[]=array('label'=>$this->page->lang_l('all'),'count'=>$entries_total,'url'=>$this->page->script_path.'?action=index'.$amp_cat.$amp_per);
		$fast_nav_array[]=array('label'=>$this->page->entry_status['published'],'count'=>$published_total,'url'=>$this->page->script_path.'?action=index&amp;status=published'.$amp_cat.$amp_per,'status'=>'published');
		$fast_nav_array[]=array('label'=>$this->page->entry_status['pending'],'count'=>$pending_total,'url'=>$this->page->script_path.'?action=index&amp;status=pending'.$amp_cat.$amp_per,'status'=>'pending');
		$fast_nav_array[]=array('label'=>$this->page->entry_status['unpublished'],'count'=>$draft_total,'url'=>$this->page->script_path.'?action=index&amp;status=unpublished'.$amp_cat.$amp_per,'status'=>'unpublished');
		$fast_nav_array[]=array('label'=>$this->page->entry_status['scheduled'],'count'=>$scheduled_total,'url'=>$this->page->script_path.'?action=index&amp;status=scheduled'.$amp_cat.$amp_per,'status'=>'scheduled');
		$fast_nav_selected=$curr_status;

		$prepend.=Filter::adminBar(array($fast_nav_array,$fast_nav_selected),$left_content,$right_content);

		if(!empty($data_to_show))
		{
			Session::intStart();
			$users_data=User::getAllUsers(false,true,true);
			$can_send_mail=false;
			if($this->page->user->isMainAdmin())
				$can_send_mail=$can_edit=true;
			else
			{
				if($this->page->user->isAdminUser())
					$can_send_mail=true;
				$has_user_cookie=$this->page->user->userCookie();
				$can_edit=($has_user_cookie && User::mhasWriteAccess($this->page->user->getUserCookie(),$this->page->page_info,$db));
				if($can_edit) //if can edit, then it has user cookie and logged_user info is already got
					$this->page->edit_own_posts_only=User::userEditOwn($db,$this->page->user->getId(),$this->page->page_info);
			}

			list($orderby,$asc)=Filter::orderBy('title','DESC');
			$orderby_pfix=($orderby=='title')?'':'&amp;orderby='.$orderby;
			$asc_pfix=($asc=='')?'':'&amp;asc='.$asc;
			$nav=Navigation::pageCA($pass_total,$this->page->script_path.'?action=index'.$amp_cat.$amp_st.$amp_per.$amp_search.$orderby_pfix.$asc_pfix,0,$this->page->c_page);
			$append.=$nav;
			$prepend.=$nav;

			$page=(isset($_REQUEST['page']))?'&amp;page='.intval($_REQUEST['page']):'';

			$cap_arrays=array(
				''=>'<input type="checkbox" onclick="$(\'.mng_entry_chck\').prop(\'checked\',$(this).is(\':checked\'));">',
	 			'title'=>array($this->page->script_path.'?action=index'.$page.$amp_cat.$amp_per,'none',$this->page->lang_l('title'),'    '),
				'author'=>$this->page->lang_l('author')
			);

			if($this->page->pg_settings['enable_comments'])
				$cap_arrays['title'][4]=array($this->page->script_path.'?action=index&amp;orderby=comment_count'.$page.$amp_cat.$amp_per,'none','<i class="fa fa-comments-o"></i>','');
			if(!$this->page->all_settings['disable_trackbacks'])
				$cap_arrays['title'][5]=array($this->page->script_path.'?action=index&amp;orderby=trackback_count'.$page.$amp_cat.$amp_per,'none','<i class="fa fa-exchange"></i>','');

			$cap_arrays['title'][6]=array($this->page->script_path.'?action=index&amp;orderby=visits_count'.$page.$amp_cat.$amp_per,'none','<i class="fa fa-eye"></i>','');
			$cap_arrays['title'][7]=array($this->page->script_path.'?action=index&amp;do=visits_count_stats'.$page.$amp_cat.$amp_per,'none','<i class="fa fa-info-circle"></i>','');

			if(!$this->page->is_blog)
				$cap_arrays['title'][8]=array($this->page->script_path.'?action=index&amp;orderby=download_count'.$page.$amp_cat.$amp_per,'none','<i class="fa fa-download-alt"></i>','');

			$cap_arrays['publish_status']=array($this->page->script_path.'?action=index&amp;orderby=publish_status'.$page.$amp_cat.$amp_per,'none',$this->page->lang_l('status'));
			$cap_arrays['category']=$this->page->lang_l('category');

			switch($orderby)
			{
				case 'comment_count':
					 $orderby=4;
					 break;
				case 'visits_count':
					 $orderby=6;
					 break;
				case 'trackback_count':
					 $orderby=5;
					 break;
				case 'download_count':
					 $orderby=7;
					 break;
				default:break;
			}
			if(is_int($orderby))
			{
				$cap_arrays['title'][$orderby][1]='underline';
				if($asc=='DESC')
					$cap_arrays['title'][$orderby][0].='&amp;asc=ASC';
			}
			elseif(isset($cap_arrays[$orderby]))
			{
				$cap_arrays[$orderby][1]='underline';
				if($asc=='DESC')
					$cap_arrays[$orderby][0].='&amp;asc=ASC';
			}

			$users_data=User::getAllUsers(false,true,true,$db);

			foreach($data_to_show as $value)
			{
				$non_published=($value['publish_status']=="pending" || $value['publish_status']=="unpublished" || $value['publish_status']=="scheduled");
				$inp='<input class="mng_entry_chck" type="checkbox" name="entry_id[]" value="'.$value['entry_id'].'">&nbsp;';
				$entry_url=$this->page->script_path.'?entry_id='.$value['entry_id'];
				$mf='';
				if(isset($value['mediafile_url'])&&(!empty($value['mediafile_url'])))
					$mf=Formatter::sth($value['mediafile_url']);
				elseif(isset($value['External_Media'])&&(!empty($value['External_Media'])))
					$mf=Formatter::sth($value['External_Media']);

				$entry=($mf!='')?$this->page->build_stat_with_icon('','fa-video-camera',$this->page->lang_l('video'),$mf,20):'';
				$entry.='<a class="rvts8 nodec post_title" target="_blank" href="'.$entry_url.($non_published?'&amp;action=entrypreview':'').'">' .Formatter::sth2($value['title']).'</a><br>';

				$entry_allow_downloads=(!$this->page->is_blog)?'<span class="rvts8">'.$this->page->lang_l($value['allow_downloads']?'downloads_on':'downloads_off').'</span>':'';

				$dt=Formatter::mySubstr($this->page->month_name[date('n',Date::tzoneSql($value['creation_date']))-1],0,3,$uft_flag).
					Date::formatTimeSql($value['creation_date'],$this->page->pg_settings['time_format'],'long');
				$eurl=$entry_url.($non_published? '&amp;action=entrypreview':'');
				$entry.=$this->page->build_stat_with_icon($dt,'fa-clock-o',$this->page->lang_l('published'),$eurl,145);
				$entry_pub=($value['publish_status']=="published");

				$dl=$this->page->script_path.'?action=del_entry&amp;entry_id='.$value['entry_id'].$this->page->c_page_amp.$return_param.'" onclick="javascript:return confirm(\''.$this->page->lang_l('del entry msg').'\');';
				$el=$this->page->script_path.'?action=edit_entry&amp;entry_id='.$value['entry_id'].$this->page->c_page_amp.$return_param;
				$pl=$this->page->script_path.'?action='.($entry_pub?'unpub_entry':'pub_entry').'&amp;entry_id='.$value['entry_id'].$return_param;
				$dul=$this->page->script_path.'?action=duplicate&amp;entry_id='.$value['entry_id'].$this->page->c_page_amp;
				if($value['entry_id']=='0')
					$entry_nav=array($this->page->lang_l('edit')=>$el,$this->page->lang_l('delete')=>$dl);
				else
					$entry_nav=array($this->page->lang_l('edit')=>$el,$this->page->lang_l('duplicate')=>$dul,$this->page->lang_l('delete')=>$dl,$this->page->lang_l(($entry_pub?'unpublish':'publish'))=>$pl);
				if($this->page->edit_own_posts_only && $this->page->all_settings['concept_mode'])
					unset($entry_nav[$this->page->lang_l(($entry_pub?'unpublish':'publish'))]);
				if($non_published)
					$entry_nav['preview']=$this->page->script_path.'?action=entrypreview&amp;entry_id='.$value['entry_id'].$this->page->c_page_amp.'" target="_blank';

				$usr=isset($users_data[$value['posted_by']])?$users_data[$value['posted_by']]:$users_data[-1];
				$posted_by_name=$usr['username'];
				$can_send_mail=$can_edit && (!$this->page->edit_own_posts_only || $posted_by_name==$this->page->user->getUserCookie());
				if($value['entry_id']!='0')
				{
					$entry_nav[$this->page->lang_l('print')]=array('class'=>'mbox','extra_tags'=>'rel="noDesc['.$value['entry_id'].'],width:1000px,height:600px"','url'=>$this->page->script_path.'?print=1&amp;print_id='.$value['entry_id'].'&amp;entry_id='.$value['entry_id']);

					if($can_send_mail)
						$entry_nav[$this->page->lang_l('send mail')]=$this->page->script_path.'?action=mail_entry&amp;entry_id='.$value['entry_id'];
				}
				$postedby='<span class="rvts8">'.$usr['username'].'</span>'.
						User::getAvatarFromData($usr,$db,$usr['username'],$this->page->site_base,'right');

				$ct_color='';
				$ct_colorbar=$this->page->categoriesModule->get_category_colorbar($value['category'],'index','',$ct_color);

				$publish='<span class="rvts8">'.$this->page->entry_status[$value['publish_status']].'</span>';
				if($value['accessibility'] != 'public')
					$publish .= F_BR.'<span class="rvts8">'.$value['accessibility'].'!</span>';

				if($this->page->pg_settings['enable_comments'])
					$entry.=$this->page->build_stat_with_icon($value['comment_count'],'fa-comments',$this->page->lang_l('comments'),$this->page->script_path.'?entry_id='.$value['entry_id']);
				if(!$this->page->all_settings['disable_trackbacks'])
					$entry.=$this->page->build_stat_with_icon($value['trackback_count'],'fa-exchange',$this->page->lang_l('trackbacks'),$this->page->script_path.'?action=trackback&amp;entry_id='.$value['entry_id']);
				$entry.=$this->page->build_stat_with_icon(empty($value['visits_count'])?'0':$value['visits_count'],'fa-eye',$this->page->lang_l('visits'));
				if(!$this->page->is_blog)
					$entry.=$this->page->build_stat_with_icon(intval($value['download_count']),'fa-download',$this->page->lang_l('downloads'));

				if($this->page->user->isAdmin() || $this->page->all_settings['weight_forusers'])
					$entry.=$this->page->build_stat_with_icon(Formatter::sth2($value['weight']),'fa-ellipsis-v',$this->page->lang_l('weight'));

				$entry.=Builder::buildAdminRanking($value['ranking_count'],$value['ranking_total'],$ct_color,$this->page->lang_l('rankings'));
				$row_data=array($inp,array($entry,$entry_nav),$postedby);

				$row_data[]=$publish.F_BR.$entry_allow_downloads;
				$row_data[]=$ct_colorbar;
				$table_data[]=$row_data;
			}
			$append='<input class="ca_button" name="del_entry" type="submit" value="&#xf00d" title=" '.$this->page->lang_l('delete checked posts').' " onclick="javascript:return confirm(\''.$this->page->lang_l('del entry msg').'\');">'.$append;
		}
		else
		{
			if($category_name!='')
				$msg=$this->page->lang_l('category is empty');
			elseif(isset($_GET['status']) || isset($_REQUEST['q']))
				$msg=$this->page->lang_l('no posts found');
			else
				$msg=$this->page->lang_l('blog is empty');
			$table_data[]=array('','<span class="rvts8">'.$msg."</span>");
		}
		$output=Builder::adminTable($prepend.'<form method="post" action="'.$this->page->script_path.'?action=index" enctype="multipart/form-data">',$cap_arrays,$table_data,$append)."</form>";
		return $output;
	}


	protected function manage_entries($period_id='')
	{
		global $db;

		$pending_total=$draft_total=$published_total=$entry_total=$scheduled_total=$template_total=0;
		$periods_array=array();
		$periods_array_raw=$db->fetch_all_array('
			SELECT DATE_FORMAT(creation_date, "%c") as mon, DATE_FORMAT(creation_date, "%Y") as year
			FROM '.$db->pre.$this->page->pg_pre.'posts');
		$periods_array_raw=Filter::multiUnique($periods_array_raw);
		foreach($periods_array_raw as $k=>$v)
			$periods_array[]=$this->page->month_name[$v['mon']-1].' '.$v['year'];

		$where='';
		$category_id=$this->page->categoriesModule->get_categoryid();
		if($category_id!==false)
			$where.='category='.$category_id.' AND ';
		if($period_id!='' &&  in_array($period_id,$periods_array))
		{
			list($mon_name,$year)=explode(' ',$period_id);
			$mon=array_search(trim($mon_name),$this->page->month_name)+1;
			$where.='creation_date > "'.$year.'-'.$mon.'-01 00:00:00" AND creation_date < "'.$year.'-'.$mon.'-31 23:59:59" AND ';
		}
		if($this->page->edit_own_posts_only && !$this->page->user->isAdmin())
			$where.='posted_by='.$this->page->user->getId().' AND ';

		$count_raw=$db->fetch_all_array('
			SELECT publish_status,COUNT(*) as count
			FROM '.$db->pre.$this->page->pg_pre.'posts '.(($where!='')?'
			WHERE '.$where.' 1':'').'
			GROUP BY publish_status');
		foreach($count_raw as $k=>$v)
		{
			if($v['publish_status']=='published')
				$published_total=$v['count'];
			elseif($v['publish_status']=='unpublished')
				$draft_total=$v['count'];
			elseif($v['publish_status']=='pending')
				$pending_total=$v['count'];
			elseif($v['publish_status']=='scheduled')
				$scheduled_total=$v['count'];
			elseif($v['publish_status']=='template')
				$template_total=1;
			else
				$entry_total=$v['count'];
		}
		$entry_total=intval($entry_total+$draft_total+$published_total+$pending_total+$template_total);

		if(isset($_GET['status']))
			$where.='publish_status="'.(array_key_exists(trim($_GET['status']),$this->page->entry_status)? $_GET['status']: '').'" AND ';
		if(!$this->page->user->isAdmin())
			$where.='publish_status != "template" AND ';
		$where.='1';
		list($orderby,$asc)=Filter::orderBy('',($this->page->order_by=='title')?'ASC':'DESC');
		$entries_records=$this->page->db_fetch_entries_slice($entry_total,Navigation::recordsPerPage(),$where,$asc,$orderby,$orderby=='');

		if(!$this->page->is_blog && isset($entries_records[0]) && !isset($entries_records[0]['allow_downloads']))
		{
			include_once('data.php');
			create_blogdb($db,$db->pre.$this->page->pg_pre,$this->page->pg_id,$this->page->pg_settings['page_type'],'','','','',false,false,true);
			$entries_records=$this->page->db_fetch_entries_slice($entry_total,Navigation::recordsPerPage(),$where,$asc,$orderby);
		}

		if(isset($_REQUEST['q']))   // search
		{
			$search_string=trim($_REQUEST['q']);
			if(!empty($search_string))
			{
				$ss=$db->escape(Formatter::stripTags($search_string));
				$by_user=($this->page->edit_own_posts_only && !$this->page->user->isAdmin())?$this->page->user->getUserCookie():'';
				$entries_records=$this->page->db_search_in_entries($ss, 'admin', $by_user);
			}
		}
		$output=$this->entries_admin($entries_records,$category_id,$period_id,$periods_array,$entry_total,$published_total,$pending_total,$draft_total,$scheduled_total);
		$this->output($output);
	}

}

class blocked_ip_screen extends blog_admin_screens
{

	public function handle_screen()
	{
		global $db;
	  	$all_blocked_ips=$db->fetch_all_array('SELECT * FROM '.$db->pre.'blocked_ips');
		$output=Formatter::fmtBlockedIPs($all_blocked_ips,$this->page->script_path,$this->page->lang_l('unblock ip'),$this->page->lang_l('no blocked ips'));
		$this->output($output);
	}
}

class manage_categories_screen extends blog_admin_screens
{
	public function handle_screen()
	{
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		$output='';

		if($do=='delete')
			$this->page->categoriesModule->delete_category($output);
		elseif($do=='edit')
			$this->page->categoriesModule->edit_category();
		elseif($do=='add')
			$this->page->categoriesModule->add_category($output);
		elseif($do=='publish')
			$this->page->categoriesModule->publish_category(true);
		elseif($do=='unpublish')
			$this->page->categoriesModule->publish_category(false);
		elseif($do=='duplicate')
			$this->page->categoriesModule->duplicate_category(false);

		$this->mng_categories($do,$output);
	}

	protected function mng_categories($do,$output)
	{
		$drag_mode=isset($_REQUEST['dragmode']) && $this->page->user->isAdminOnPage($this->page->pg_id);
		$err_on_submit='';

		$logged_as_admin=$this->page->user->isEZGAdminLogged();

		if(!$drag_mode)
		{
			$nav=$this->page->categoriesModule->get_admin_category_nav();
			$nav.=$this->page->categoriesModule->get_admin_category_entry(
					array('id'=>'',
							'name'=>($do=='add' && isset($_POST['cname'])?Formatter::stripTags($_POST['cname']):''),
							'color'=>'#ff3300',
							'image1'=>isset($_POST['image1'])? $_POST['image1']:'',
							'description'=>'',
							'pid'=>-1,
							'viewid'=>'',
							'title'=>'',
							'ct_category'=>''),
					$logged_as_admin,'',$err_on_submit);
			$this->page->innova_on_output=true;
		}

		$total_count=$this->page->categoriesModule->get_categoriesCount();
		$table_data=array();
		$table_data=$this->page->categoriesModule->get_admin_category_list($logged_as_admin,$drag_mode,'',$err_on_submit,$this->cat_count,$total_count);
		$filter=isset($_REQUEST['q'])?addslashes($_REQUEST['q']):'';
		$cap_arrays=array('',
			 'category'=>array(
				  $this->page->pg_name.'?action=categories',
				  'none',
				  $this->page->lang_l('category'),
				  '<form id="ct_form" method="post" action="?action=categories">'.Filter::build('q',$filter,'$(\'#ct_form\').submit()').'</form>'),
				  );
		if(!$drag_mode)
			$cap_arrays[] = $this->page->lang_l('posts');

		if($drag_mode)
		{
			$end='<input type="button" onclick="ca_order();" value="'.$this->page->lang_l('save').'">
			<input type="button" onclick="document.location=\''.$this->page->script_path.'?action=categories\'" value="'.$this->page->lang_l('cancel').'">';
			$output.=Builder::adminDraggableSection($cap_arrays,$table_data,$end,'',$this->page->script_path,'',true);
		}
		else
		{
			$nav.=Navigation::pageCA($total_count,$this->page->script_path.$this->page->pg_name."?action=categories",0,$this->page->c_page);
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data,'','','');
		}

		$this->page->page_scripts='$(document).ready(function(){assign_edits();});';
		$this->output($output);
	}
}

class blog_output
{
	public $page;
	public $f;
	protected $entry_id;
	protected $mon;
	protected $year;
	protected $tag;
	protected $no_posts_msg;
	protected $count_all_entries;
	protected $category_id;
	protected $is_view;
	protected $view;
	protected $description;
	protected $force_nav;
	protected $add_nav;
	protected $nav_par;
	protected $navigation_section='';
	protected $append_nav_url='';
	protected $can_edit=false;
	protected $category_header='';
	protected $search_styles='';
	protected $item_category=-1;
	protected $entries_full=array();
	protected $entries_records=array();
	protected $blogobj_params='';
	protected $canonical='';
	protected $header=0;

	public function __construct($pg)
	{
		global $f,$db;
		$this->f=$f;
		if($pg instanceof LivePageClass)
			$this->page=$pg;

		if($this->page->use_alt_plinks)
			$this->resolve_permalink();
		$this->entry_id=(isset($_REQUEST['entry_id']) && intval($_REQUEST['entry_id'])>0) ?intval($_REQUEST['entry_id']):0;
		$this->mon=isset($_GET['mon'])?intval($_GET['mon']):0;
		$this->year=isset($_GET['year'])?intval($_GET['year']):date("Y");
		$this->tag=isset($_REQUEST['tag'])?Formatter::stripTags($_REQUEST['tag']):'';
		$this->no_posts_msg=F_BR.($this->f->error_iframe!==false?$this->f->error_iframe : $this->page->lang_l('no posts found'));
		$this->count_all_entries=0;
		$this->category_id=$this->page->categoriesModule->get_categoryid();
		$this->description='';

		if($this->entry_id>0 && isset($_REQUEST['fl']))
			$db->query_insert("rss_log",array('ip'=>Detector::getIP(),'url'=>$this->entry_id,'date'=>Date::buildMysqlTime()),false,true);
	}

	public function handle_screen()
	{
		if($this->page->action_id=="rss" || $this->page->action_id=="commentsrss")
		{
			$this->rss_output($this->entry_id);
			exit;
		}
		elseif(!$this->page->all_settings['disable_trackbacks'] && $this->page->action_id=="trackback" && isset($_POST['url']))
			$this->page->trackbacks->build_trackback();
		else
			$this->prepare_output();
	}

	protected function rss_output($entry_id)
	{
		if($entry_id>0 && $this->page->pg_settings['enable_comments'])
			 $this->page->commentModule->update_commentrss($entry_id);
		elseif($this->page->action_id=="commentsrss")
			 $this->page->commentModule->update_commentrss();
		else
			$this->page->rss->output_rssfeed();
	}

	protected function resolve_permalink()
	{
		global $db;

		if(isset($_REQUEST['category']) && !isset($_REQUEST['q']))
			Linker::customErrorRedirect($this->page->rel_path,$this->page->page_id);//only redirects when custom error page avail.

		$uri=Linker::requestUri();
		$request_plink=$this->f->http_prefix.Linker::getHost().$uri;
		$url_data=explode("/",Formatter::stripTags($uri));
		$fullpath_data=explode("/",$this->page->full_script_path);
		$url_data_t=array();
		foreach($url_data as $k=>$v)
			if(!in_array($v,$fullpath_data) && $v!='')
				$url_data_t[]=$v;
		$url_data=$url_data_t;

		if(isset($url_data[0]) && !empty($url_data[0]) && (strpos($url_data[0],$this->page->pg_name.'?')===false))
		{
			$view_in_request='';
			$last_param=end($url_data);
			if(isset($this->page->pg_settings['views'][$last_param]))
			{
				$_REQUEST[$last_param]='';
				$view_in_request=$last_param;
				array_pop($url_data);
			}

			if(count($url_data[0])==0)
				$this->page->action_id='';
			elseif($url_data[0]=='rss')
				$this->page->action_id='rss';
			elseif(isset($url_data[1]) && $url_data[1]=='rss')
				$this->page->action_id='commentsrss';
			elseif(isset($url_data[2]) && $url_data[2]=='rss')
			{
				$this->page->action_id='rss';
				$_GET['category']=$url_data[1];
			}
			elseif($url_data[0]=='category')
			{
				$cid=0;
				$_REQUEST['category']=$_GET['category']=$this->page->categoriesModule->get_category_from_plinkname($url_data[1],$cid);
				if(isset($url_data[2]))
					$_GET['page']=$url_data[2];
			}
			elseif($url_data[0]=='tag')
			{
				$_REQUEST['tag']=$url_data[1];
				if(isset($url_data[2]))
					$_GET['page']=$url_data[2];
			}
			elseif(!isset($url_data[2]) && intval($url_data[0])>0 && intval($url_data[0])<1000)
				$_GET['page']=$url_data[0]; //when frontpage is paged
			elseif(strlen($url_data[0])==4 && intval($url_data[0])>1000 && (!isset($url_data[2]) || is_numeric($url_data[2])))
			{
				$_GET['year']=intval($url_data[0]);
				$_GET['mon']=intval($url_data[1]);
			}  //?? to fix for page
			elseif(intval($url_data[0])>1000)
			{
				$_GET['year']=intval($url_data[0]);
				$_GET['mon']=intval($url_data[1]);
				$entry_title=urldecode($url_data[2]);
				$entry_title_nosp=str_replace('-',' ',$entry_title);
				if(strpos($request_plink,'/ranking/')!==false)
				{
					$request_plink=str_replace($url_data[3].'/ranking/','',$request_plink);
					$_GET['value']=$url_data[3];
					$_GET['action']='ranking';
					$this->page->action_id='ranking';
				}
				elseif(strpos($request_plink,'/trackback/')!==false && $this->page->is_blog)
				{
					$request_plink=str_replace('trackback/','',$request_plink);
					$this->page->action_id='trackback';
				}
				elseif(strpos($request_plink,'/rss/')!==false)
				{
					$request_plink=str_replace('rss/','',$request_plink);
					$this->page->action_id='rss';
				}
				$mm=intval($_GET['mon']);
				$yy=intval($_GET['year']);
				$called_entry=$db->fetch_all_array('SELECT *, DATE_FORMAT(creation_date, "%m") as mon,
					DATE_FORMAT(creation_date, "%Y") as year FROM '.$db->pre.$this->page->pg_pre.'posts
					WHERE (permalink="'.addslashes($request_plink).'"
					OR creation_date > "'.$yy.'-'.$mm.'-01 00:00:00" AND creation_date < "'.$yy.'-'.$mm.'-31 23:59:59")
					AND '.$this->page->where_public);
				$number_no_posts = 0;
				if(isset($called_entry[1]))
				{
					foreach($called_entry as $k=>$v)
					{
						if(strtolower($request_plink)==strtolower($this->page->build_permalink($v,false,'',true,$view_in_request)))
						{
							$_REQUEST['entry_id']=$v['entry_id'];
							break;
						}
						if($v['title']==$entry_title || $v['title']==$entry_title_nosp)
						{
							$_REQUEST['entry_id']=$v['entry_id'];
							break;
						}else{
							$number_no_posts++;
						}
					}
					if(count($called_entry)==$number_no_posts && $this->page->all_settings['error_404_page']!=''){
						// no posts found => redirect to 404 page
						$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
						header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
						header('Location: '.$url_404_page);
					}
				}
				elseif(!empty($called_entry[0])){
					$_REQUEST['entry_id']=$called_entry[0]['entry_id'];
					$found_post=$called_entry[0]['title']==$entry_title || $called_entry[0]['title']==$entry_title_nosp;
					if(!$found_post && $this->page->all_settings['error_404_page']!=''){
						// no posts found => redirect to 404 page
						$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
						header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
						header('Location: '.$url_404_page);
					}
				}
			}
			else //permalink
			{
				if($this->page->all_settings['plink_type']=='title')
				{
		  			 $data=$db->query_first('SELECT entry_id FROM '.$db->pre.$this->page->pg_pre.'posts
						  WHERE title="'.addslashes($url_data[0]). '" OR
								title="'.str_replace('-',' ',addslashes($url_data[0])). '" OR
									 permalink="'.addslashes($url_data[0]). '"');
					if(isset($data['entry_id']))
						$_REQUEST['entry_id']=$data['entry_id'];
					else if($this->page->all_settings['error_404_page']!=''){
						// no posts found => redirect to 404 page
						$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
						header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
						header('Location: '.$url_404_page);
					}
				}
				else
				{
					 $data=$db->query_first('SELECT entry_id FROM '.$db->pre.$this->page->pg_pre.'posts
						  WHERE permalink="'.addslashes($url_data[0]). '"');
					if(isset($data['entry_id']))
						$_REQUEST['entry_id']=$data['entry_id'];
					else if($this->all_settings['error_404_page']!=''){
						// no posts found => redirect to 404 page
						$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
						header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
						header('Location: '.$url_404_page);
					}
				}
			}
			$this->page->setCurrPage($url_data);
		}

		//in case there is print request and entry id is not detected via re-write URl
		if(isset($_REQUEST['print_id'])&&!isset($_REQUEST['entry_id']))
			$_REQUEST['entry_id']=$_REQUEST['print_id'];
	}

	protected function replace_param($key,$value,$src)
	{
		$result=$src;
		$result=str_replace(array('<p class="rvps1">'.$key.'</p>','<p class="rvps2">'.$key.'</p>','<p>'.$key.'</p>'),array('<div class="rvps1">'.$value.'</div>','<div class="rvps2">'.$value.'</div>',$value),$result);
		return str_replace($key,$value,$result);
	}

	protected function search_box_html()
	{
	  $output='<form name="b_search" action="'.$this->page->script_path.'?action='.$this->page->action_id.'" method="post">
		 <input class="input1" id="search_edit" type="text" name="q" value="">
		 <input class="input1" id="search_btn" type="submit" name="search" value="'.$this->page->lang_l('search').'">
		</form>';
		return $output;
	}

	protected function categories_combo($cat_id,$param='')
	{
		$w=is_numeric($param)?$param:'147';
		$show_count=$param!='false';
		$jstring="onchange=\"document.location='".$this->page->build_permalink_cat()."' + this.options[this.selectedIndex].value;\"";
		$output=$this->page->categoriesModule->build_category_combo($this->page->lang_l('all categories'),'Selected_Category',$cat_id,$jstring,' style="width:'.$w.'px"',
			false,3,'name',$this->page->use_alt_plinks,false,false,$show_count);
		return $output;
	}
	//type 0:only categories 1:first entry is 'all categories' 2: first entry 'none' 3: same as 1, but also hide empty categories

	protected function db_fetch_entry_nextprev($entry_id,$where,$order,&$prev_pl,&$next_pl,&$prev_title,&$next_title)
	{
		global $db;

		$records=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->page->pg_pre.'posts
			WHERE entry_id='.$entry_id.($where!=''?' AND '.$where: ''));

		$current_stamp=$records[$this->page->order_by];
		if($where!='')
			$where.=' AND ';
		$where1=$where.$this->page->order_by.' > "'.addslashes($current_stamp).'"';
		$where2=$where.$this->page->order_by.' < "'.addslashes($current_stamp).'"';
		$order1=($order='DESC')?'ASC':'DESC';

		$pn_records=$db->fetch_all_array('(
			SELECT entry_id,category,title,permalink,"next" AS direction, creation_date
			FROM ' .$db->pre.$this->page->pg_pre.'posts
			WHERE '.$where1.'
			ORDER BY weight ASC,'.$this->page->order_by.' '.$order1.'
			LIMIT 1)
			UNION (
			SELECT entry_id,category,title,permalink,"prev" AS direction, creation_date
			FROM ' .$db->pre.$this->page->pg_pre.'posts
			WHERE '.$where2.'
			ORDER BY weight ASC,'.$this->page->order_by.' '.$order.'
			LIMIT 1)');

		foreach($pn_records as $v)
		{
			if($v['direction']=='next')
			{
				$next_pl=$this->page->build_permalink($v,false,'',true);
				$next_title=$v['title'];
			}
			else
			{
				$prev_pl=$this->page->build_permalink($v,false,'',true);
				$prev_title=$v['title'];
			}
		}
		return $records;
	}

	protected function calendar($template)
	{
		global $db;

		$template=Formatter::objDivReplacing('%CALENDAR%',$template);
		if(isset($_GET['mon']) && isset($_GET['year']))
		{
			$curr_mon=intval($_GET['mon']);
			if($curr_mon<1)
				$curr_mon=1;
			$curr_year=intval($_GET['year']);
		}
		else
		{
			$curr_date=Date::tzone(time());
			$curr_mon=intval(date('n',$curr_date));
			$curr_year=intval(date('Y',$curr_date));
		}

		$all_indexes=$db->fetch_all_array('
			SELECT creation_date
			FROM '.$db->pre.$this->page->pg_pre.'posts
			WHERE '.$this->page->where_public.' AND creation_date BETWEEN "'.$curr_year.'-'.$curr_mon.'-01" AND DATE_ADD("'.$curr_year.'-'.$curr_mon.'-01", INTERVAL 1 MONTH) ');

		$posts_per_day=Unknown::defPostPerDay($curr_mon,$curr_year,$all_indexes,'creation_date');
		$template=str_replace('%CALENDAR%',Builder::buildCalendar($curr_mon,$curr_year,1,$posts_per_day,$this->page->script_path,$this->page->month_name,$this->page->day_name,false,'?'),$template);
		return $template;
	}

	protected function archive_sidebar($archive_data,$param,$id)
	{
		$output='';
		if($param=='')
			$param='
				<div class="archive%active%">
					<span class="rvts8">'.($this->f->uni?'&#9724;':'::').'</span>
						<a class="rvts12%active%" rel="archives" title="%item_url_title%" href="%item_url%">%title%</a>
					<span class="rvts8 count"> (%count%)</span>
				</div>';

		$act_year=isset($_GET['year'])?intval($_GET['year']):0;
		$act_mon=isset($_GET['mon'])?intval($_GET['mon']):0;

		foreach($archive_data as $v)
		{
			$title=$title_url_val=$v['id'];
			$p_link=$this->page->build_permalink_arch($v['month'],$v['year']);
			$active=$v['month']==$act_mon && $v['year']==$act_year;
			$output.=str_replace(
				array('%title%','%item_url%','%count%','%item_url_title%','%active%'),
				array($title,$p_link,$v['count'],$title_url_val,($active?' active':'')),$param);
		}
		return '<div class="entries_sidebar" id="'.$id.'">'.$output.'</div>';
	}

	protected function archive_sidebar_collaps($archive_data)
	{
		$output='';
		$active=false;
		$ts=time();
		$act_year=isset($_GET['year'])?intval($_GET['year']):0;
		$act_mon=isset($_GET['mon'])?intval($_GET['mon']):0;

		foreach($archive_data as $year=>$v)
		{
			$count_per_year=0;
			$buff='';
			foreach($v as $mon=>$value)
			{
				$active=$mon==$act_mon && $year==$act_year;
				$count=count($value);
				$count_per_year +=$count;

				$buff.='
					<div class="sidcol month'.($active?' active':'').'">
						<span class="rvts8">'.($this->f->uni?'&#9724;':'::').'</span>
							<a class="rvts12'.($active?' active':'').'" href="javascript:void(0);"	onclick="$(\'#mon'.$mon.$year.'\').toggle();">'
							.$this->page->month_name[$mon-1].' ('.$count.')</a>
					</div>';

				$buff_recs='';
				foreach($value as $post)
					$buff_recs.='<a class="rvts12 sidcoll" href="'.$this->page->build_permalink($post,false,'',true).'">'.Formatter::sth($post['title']).'</a>'.F_BR;

				$buff.='
					<div id="mon'.$mon.$year.'" style="padding-left:18px;display:'.($act_mon==$mon || ( date('m',$ts)==$mon && date('Y',$ts)==$year) || (date('n',$ts)==$mon && date('Y',$ts)==$year)?'block':'none').'">'
						.$buff_recs.
					'</div>';
			}
			$output.='
				<div class="sidcol'.($active?' active':'').'">
					<span class="rvts8">'.($this->f->uni?'&#9724;':'::').'</span>
						<a class="rvts12'.($active?' active':'').'" href="javascript:void(0);" onclick="$(\'#year'.$year.'\').toggle();">'.
							$year.' ('.$count_per_year.')
						</a>
				</div>
				<div id="year'.$year.'" style="display:'.($act_year==$year || date('Y',$ts)==$year?'block':'none') .'">
     				<div style="padding-left:18px;">'
					  .$buff.'
					</div>
				</div>';
		}
		return '<div class="entries_sidebar" id="entries_archive">'.$output.'</div>';
	}

	public function get_recententries($max_r,$orderby='',$ordertype='',$fulllist=false)
	{
		global $db;

		$category_id=$fulllist?false:$this->page->categoriesModule->get_categoryid();
		$ct=($category_id!==false)?' AND dt.category='.$category_id.' ':'';
		$entries_recent=array();
		$limit_expr=($max_r>0)? 'LIMIT 0, '.$max_r.'':'';
		if($orderby=='')
			$orderby=$this->page->date_used;
		if($ordertype=='')
			$ordertype=$this->page->order_type;
		$entries_recent=$db->fetch_all_array('
			SELECT *,creation_date
			FROM '.$db->pre.$this->page->pg_pre.'posts as dt
			JOIN '.$db->pre.$this->page->pg_pre.'categories AS ct
			ON (ct.cid=dt.category)
			WHERE '.$this->page->where_public.$ct.'
			ORDER BY '.$orderby.' '.$ordertype.' '.$limit_expr);
		return $entries_recent;
	}

	protected function entries_fulllist($records,$param,$category_id,$full_in_category)
	{
		if($full_in_category)
		{
			$new_records=array();
			foreach($this->page->categoriesModule->category_array as $v)
			{
				if($v['count']>0)
		    {
		      foreach($records as $r=>$rv)
		      	if($rv['cid']==$v['id'])
		      	{
						$rv['pid']=$v['pid'];
						$new_records[]=$rv;
						unset($records[$r]);
					}
				}
			}
		  $records=$new_records;
		}
		return $this->page->SidebarBuilder->entries_sidebar($records,$param,true,$category_id,'entries_full');
	}

	protected function build_array($entry_id,&$periods_ar,&$archive_entries_collaps,$collaps_used)
	{
		global $db;

		$title=$collaps_used?'title,':'';
		$this->entries_full=$db->fetch_all_array('
			SELECT entry_id,category,permalink,'.$title.$this->page->date_used.'
			FROM '.$db->pre.$this->page->pg_pre.'posts
			WHERE '.$this->page->where_archived_public.'
			ORDER BY weight ASC,' .$this->page->order_by.' '.$this->page->order_type);
		$temp_array=array();
		foreach($this->entries_full as $k=>$v)
		{
			$date_ts=strtotime($v[$this->page->date_used]);
			$dt=date('F Y',$date_ts);
			if(isset($temp_array[$dt]))
				$temp_array[$dt]['count']=$temp_array[$dt]['count']+1;
			else
			{
				$m=date('n',$date_ts);
				$y=date('Y',$date_ts);
				$id=$this->page->month_name[$m-1].' '.$y;
				$temp_array[$dt]=array('count'=>1,'id'=>$id,'month'=>$m,'year'=>$y);
			}
			if($collaps_used)
			{
				$m=date('n',$date_ts);
				$y=date('Y',$date_ts);
				$archive_entries_collaps[$y][$m][]=array('title'=>$v['title'],'entry_id'=>$v['entry_id'],'category'=>$v['category'],'permalink'=>$v['permalink'],$this->page->date_used=>$date_ts);
			}
		}
		foreach($temp_array as $k=>$v)
			$periods_ar[$k]=$v;
	}

	protected function parse_og(&$template)
	{
		if($this->entry_id>0 && isset($this->entries_records[0]))
		{
			$data=$this->entries_records[0];
			$title=Formatter::sth($data['title']);
			if($this->page->all_settings['og_desc'])
			{
				$og_tags['title']=Formatter::sth($data['title']);
				if($this->description!='')
					$og_tags['description']=$this->description;
			}
			$og_tags['url']=(isset($title)?$this->page->build_permalink($data,false,'',true):$this->page->full_script_path);

			if(!$this->page->is_blog)
			{
				$media_url=$data['mediafile_url'];
				$con=Formatter::sth2($data['content']).' '.Formatter::sth($media_url);
				if($media_url!=='')
				{
					$video_fname=basename($media_url);
					$media_ext=strtolower(substr($video_fname,strpos($video_fname,'.')+1));
					$full_scrv=str_replace("/".$this->page->pg_name,'',$this->page->full_script_path);
					if($this->page->rel_path=='../')
						$full_scrv=str_replace(substr($full_scrv,strrpos($full_scrv,'/')),'',$full_scrv);

					if($media_ext=='mp3')
					{
						$og_tags['type']='audio';
						$og_tags['audio']=$full_scrv.'/'.$media_url;
						$og_tags['audio:title']=$data['title'];
						$og_tags['audio:artist']=$data['author'];
						$og_tags['audio:type']='application/mp3';
					}
					elseif(in_array($media_ext,$this->page->media_types))
					{
						$og_tags['type']='video';
						$og_tags['video']=$full_scrv.'/'.$media_url;
					}
				}
			}
			else
				$og_tags['type']='article';

			if(!empty($data['image_url']))
			{
				$media_url=$data['image_url'];
				$media_ext=substr($media_url,strrpos($media_url,".")+1);
				if(strpos($data['image_url'],'http://')===false && in_array(strtolower($media_ext),$this->page->img_file_types))
				{
					$image_fname=basename($media_url);
					$buff_img=str_replace($image_fname,rawurlencode($image_fname),$media_url);
					$full_scr=str_replace("/".$this->page->pg_name,'',$this->page->full_script_path);
					$og_url=$media_url;
					if($this->page->rel_path=='../')
						$og_url=str_replace(substr($full_scr,strrpos($full_scr,'/')),'',$full_scr);
					$og_url=($this->page->rel_path=='../'? $og_url: $full_scr)."/". $buff_img;
					$og_tags['image']=$og_url;
					list($width, $height) = @getimagesize($this->page->rel_path.$media_url);
					if($width!=null && $height!=null){
						$og_tags['image:width']=$width;
						$og_tags['image:height']=$height;
					}
				}
				if($media_ext=='mp3')
				{
					$og_tags['type']='audio';
					$full_scrv=str_replace("/".$this->page->pg_name,'',$this->page->full_script_path);
					$og_tags['audio']=$full_scrv.'/'.$media_url;
					$og_tags['audio:type']='application/mp3';
				}
			}
			else
			{
				$con=Formatter::sth2($data['content']).' '.Formatter::sth($data['image_url']);
				if(strpos($con,'youtube.')!==false && (strpos($con,'/p/')===false) ) {
					$og_tags['image']=Image::buildYTImage(Formatter::GFS($con,'http://www.youtube.com','"'));
					$og_tags['image:width']='480';
					$og_tags['image:height']='360';
				}
			}

			$fb_api_id='';
			if(strpos($template,'FB.init')!==false)
				$fb_api_id=Formatter::GFS($template,"appId:'","'");

			$template=Builder::ogMeta($template,$og_tags,$fb_api_id);
		}
	}

	protected function parse_meta(&$template)
	{
		if($this->category_id!==false)
			 $this->page->categoriesModule->parse_category_meta($template,$this->category_id);
		elseif($this->tag!='' || $this->mon>0)
		{
			$new_title=$this->mon>0?Formatter::sth($this->page->month_name[$this->mon-1].' '.$this->year):$this->tag;
			Meta::replaceTitle($template,array('title'=>$new_title),'title');
			Meta::replaceKeywords($template,array('keywords'=>$new_title),'keywords');
		}
		elseif(isset($_REQUEST['entry_id']))
		{
			$data=count($this->entries_records)==1?$this->entries_records[0]:$this->entries_records;
			if(isset($data['title']))
			{
				Meta::replaceTitle($template,$data,'title','');
				$this->description=Meta::replaceDesc($template,$data,'excerpt','content',120);
				Meta::replaceKeywords($template,$data,'keywords');
			}
		}

		$rss_meta='<link rel="alternate" title="'.Formatter::GFS($template,'<title>','</title>').'" href="'.$this->page->script_path.'?action=rss" type="application/rss+xml">'.F_LF;
		if($this->page->pg_settings['enable_comments'])
			$rss_meta.='<link rel="alternate" title="'.$this->page->lang_l('comments').'" href="'.$this->page->script_path.'?action=commentsrss" type="application/rss+xml">';

		if(isset($_REQUEST['entry_id']) && isset($this->entries_records['title']))
		{
			$title=Formatter::sth($this->entries_records['title']);
			$rss_meta.=F_LF.'<link rel="alternate" title="'.$this->page->lang_l('comments on').' '.$title.'" href="'.$this->page->build_permalink($this->entries_records,false,'rss',true).'" type="application/rss+xml">';
		}

		$template=str_replace('<!--rss_meta-->',$rss_meta,$template);
	}

	protected function page_navigation($pg_prefix='&amp;',$addhome=true)
	{
		$url_for_pgnav=($this->page->use_alt_plinks?
			 str_replace($this->page->pg_name,'',$this->page->script_path):
			 $this->page->script_path);

		$url_for_pgnav.=($this->page->use_alt_plinks?
			$this->append_nav_url:
			'?'.$this->append_nav_url);

		if($this->add_nav)
		  $this->navigation_section=Navigation::page($this->count_all_entries
				,$url_for_pgnav
				,$this->page->pg_settings['offSettings']['maxEntries'],$this->page->c_page,$this->page->lang_l('of')
				,'nav',$this->page->nav_labels,$pg_prefix,'',$this->page->use_alt_plinks,$addhome,$this->page->script_path
				,false,$this->nav_par,$this->page->make_view());

		return $this->navigation_section;
	}

	protected function resolve_view()
	{
		$view_id=0;
		if($this->category_id!==false && $this->page->all_settings['catviewid']!="0")
			$view_id=$this->page->all_settings['catviewid'];
		$this->page->categoriesModule->get_categoryView($this->category_id,$view_id);
		if($this->entry_id>0)
			$view_id=$this->page->all_settings['viewid'];
		if(isset($_REQUEST['tag'])||$this->mon>0)
			$view_id=$this->page->all_settings['tagviewid'];
		if(isset($_REQUEST['q']))
			$view_id=$this->page->all_settings['searchviewid'];
		$this->is_view=$this->page->prepareView($view_id);
		$this->view=$this->page->make_view();
	}

	protected function direct_editing_global(&$template)
	{
		global $db;

		$this->page->user->mGetLoggedUser($db,$this->page->admin_email);
	//added to handle blog simple editing
		$se_script='
		$(document).bind("ready",function(){
		  $(".edit_inline").on("click", function(){
				var th=$(this).prev();
				th.addClass("edit_mode");
				entry_id=$(this).attr("rel");
				$("#post_init_content_"+entry_id).hide();'.
				($this->f->tiny&&!$this->page->all_settings['contentbuilder']?'tinyMCE.execCommand("mceAddEditor",true,"txtContent"+entry_id);':'').'
				$("#edit_post_"+entry_id).show();
		  });});
		  function swap(vis_id,hid_id){
				$("#"+hid_id).hide();
				$("#"+vis_id).show().parent().removeClass("edit_mode");
		  };';
		if($this->page->user->isAdmin() || $this->page->user->userCookie())
		{
			if(!$this->page->all_settings['contentbuilder'])
				$template=Builder::appendScript($this->page->innova_js,$template);
			$this->page->page_scripts.=Builder::getDirectEditJS('post_wrap',$this->page->script_path);
			if($this->page->canUseURL())
			{	//added to handle scraper
				$this->page->page_scripts.=$this->page->commentModule->scraperHandler->getScript();
				$this->page->page_css.=$this->page->commentModule->scraperHandler->getCss();
			}
		}
		elseif($this->page->pg_settings['mini_editor']&&!$this->page->all_settings['contentbuilder'])
				$template=Builder::appendScript($this->page->innova_js,$template);
		$this->page->page_css.=Builder::getDirectEditCSS($this->page->rel_path);
		if($this->page->user->isAdmin() || ($this->page->user->userCookie() && User::mhasWriteAccess($this->page->user->getUserCookie(),$this->page->page_info,$db)))
		{
			$this->page->page_scripts.=$se_script;
			if(strpos($template,'%WRITE_POST')!==false)
			{
				$direct_write=strpos($template,'%WRITE_POST_DIRECT%')!==false;
				$wpb_action=$this->page->pg_name.'?action=postentry'.($this->category_id!==false?'&category='.intval($this->category_id):'');
				$wpb_btn ='<button class="input1 add_entry_btn" onclick="document.location=\''.$wpb_action.Linker::buildReturnURL().'\'" >'.$this->page->lang_l('write post').'</button>';
				$edit_entry = new edit_entry_screen($this->page);
				$small_form=$edit_entry->edit_entry_form(null,true,true,$direct_write,$this->category_id);
	 			$wpb_btn_plus='<div id="write_post_simple_area">  <div id="post_init_content_0"></div>
					<div class="edit_post" id="edit_post_0" style="display:'.($direct_write?'block':'none').';">'.$small_form.'</div></div>'.
					(!$direct_write?'<input type="button" rel="0" value="'.$this->page->lang_l('write post').' +" class="input1 edit_inline add_entry_btn"> ':'');
				if($direct_write)
				{
					$template=str_replace(array('%WRITE_POST_BUTTON%','%WRITE_POST_PLUS%','%WRITE_POST%'),'',$template);
					$template=str_replace('%WRITE_POST_DIRECT%',$wpb_btn_plus,$template);
				}
				else
					$template=str_replace(array('%WRITE_POST_BUTTON%','%WRITE_POST_PLUS%','%WRITE_POST%'),array($wpb_btn_plus.$wpb_btn,$wpb_btn_plus,$wpb_btn),$template);
			}
		}
		else $template=str_replace(array('%WRITE_POST_BUTTON%','%WRITE_POST_PLUS%','%WRITE_POST%','%WRITE_POST_DIRECT%'),'',$template);
	}

	public function db_mostVisited($limit_expr,$where_category)
	{
		global $db;

		return $db->fetch_all_array('
			SELECT *,creation_date FROM '.$db->pre.$this->page->pg_pre.'posts AS dt
			JOIN '.$db->pre.$this->page->pg_pre.'categories AS ct ON (ct.cid=dt.category)
			WHERE visits_count <> 0 AND '.$this->page->where_public.$where_category.'
			ORDER BY visits_count DESC '.$limit_expr);
	}

	protected function db_topRanked($limit_expr,$where_category)
	{
		global $db;

		return $db->fetch_all_array('
			SELECT *,creation_date, ROUND(ranking_total/ranking_count,1) AS ranking
			FROM '.$db->pre.$this->page->pg_pre.'posts AS dt
			JOIN '.$db->pre.$this->page->pg_pre.'categories AS ct ON (ct.cid=dt.category)
			WHERE ranking_total <> 0 AND '.$this->page->where_public.$where_category.'
			ORDER BY ranking DESC '.$limit_expr);
	}

	protected function parse_macros($periods_ar,$collaps_used,$archive_entries_collaps,$nav,$output,&$template)
	{
		global $db;
		$entries_related=$entries_most_visited=$entries_top_rank=array();

		$where_category=($this->category_id===false)?'':' AND category='.$this->category_id;
		if($this->entry_id>0 && $this->page->action_id!='entrypreview')
			 $this->page->rankingVisitsModule->save_ranking_visits($this->entry_id); // visits, ranking

		$limit_expr=($this->page->pg_settings['offSettings']['maxRecent']>0)
				  ? 'LIMIT 0, '.$this->page->pg_settings['offSettings']['maxRecent'].'':'';

		if(strpos($template,'%BLOG_MOST_VISITED')!==false) // most visited
			$entries_most_visited=$this->db_mostVisited($limit_expr,$where_category);

		if(strpos($template,'%BLOG_TOP_RANK')!==false) // top rank
			$entries_top_rank=$this->db_topRanked($limit_expr,$where_category);

		if($this->entry_id>0 && strpos($template,'%BLOG_RELATED')!==false) //that means it's single entry
			$entries_related=$this->page->RelatedPostsHandler->get_related_posts($this->entries_records[0],$this->page->where_public);

		if($this->page->errorHandler->hasErrors()) $output=$this->page->errorHandler->displayErrors().$output;
		$template=str_replace('%BLOG_OBJECT%',$output,$template);
		$template=str_replace(array('%BLOG_HOME_LINK%','%BLOG_LINK%','%BLOG_HOME_URL%','%HOME_LINK%'),
			array('<a class="rvts12" href="'.$this->page->script_path.'">'.$this->page->lang_l('home').'</a>',$this->page->script_path,$this->page->script_path,$this->page->script_path),
			$template);
		if($this->add_nav)
		  $template=$this->replace_param($nav.$this->nav_par.'%',$this->navigation_section,$template);
		$template=$this->replace_param('%NO_BLOG_NAVIGATION%','',$template);
		if(strpos($template,'%POSTS_COUNT%')!==false)
			$template=str_replace('%POSTS_COUNT%',$this->page->categoriesModule->get_postsCount(),$template);
		$template=Formatter::objDivReplacing('%BLOG_SEARCH%',$template);
		$template=Formatter::objDivReplacing('%BLOG_CAT_SEARCH%',$template);
		$template=str_replace('%BLOG_SEARCH%',$this->search_box_html(),$template);
		if(strpos($template,'%BLOG_CAT_SEARCH%')!==false)
			$template=str_replace('%BLOG_CAT_SEARCH%',Search::catBox($this->page->script_path.'?action='.$this->page->action_id
					  ,$this->page->pg_settings['lang_l'] ,$this->page->page_scripts),$template);
		if(strpos($template,'%CAT_SEARCH%')!==false)
			$template=str_replace('%CAT_SEARCH%',$this->page->categoriesModule->category_searchlist(),$template);
		if(strpos($template,'%CATEGORY_LIST_VER%')!==false)
			$template=str_replace('%CATEGORY_LIST_VER%',$this->page->categoriesModule->category_sidebar(false,'ver','',
					  false,'',false,'',true,false,$this->item_category),$template);
		if(strpos($template,'%CATEGORY_LIST_HOR%')!==false)
			$template=str_replace('%CATEGORY_LIST_HOR%',$this->page->categoriesModule->category_sidebar(false,'hor','',
					  false,'',false,'',true,false,$this->item_category),$template);
		if(strpos($template,'%BLOG_ARCHIVE%')!==false)
			$template=str_replace('%BLOG_ARCHIVE%',$this->archive_sidebar($periods_ar,'','entries_archive'),$template);
		if(strpos($template,'%BLOG_RELATED%')!==false)
			$template=str_replace('%BLOG_RELATED%',$this->page->SidebarBuilder->entries_sidebar($entries_related,'',false,$this->category_id,'entries_related'),$template);
		$template=Formatter::objDivReplacing('%BLOG_ARCHIVE_COLLAPS%',$template);
		if($collaps_used)
			$template=str_replace('%BLOG_ARCHIVE_COLLAPS%',$this->archive_sidebar_collaps($archive_entries_collaps),$template);

		$entries_obj='BLOG_RECENT_'.(!$this->page->is_blog?"EPISODES":"ENTRIES");
		if(strpos($template,'%'.$entries_obj.'%')!==false)
		{
			$entries_recent=$this->get_recententries($this->page->pg_settings['offSettings']['maxRecent']);
			$template=str_replace('%'.$entries_obj.'%',$this->page->SidebarBuilder->entries_sidebar($entries_recent,'',false,$this->category_id,'entries_recent'),$template);
		}
		if(strpos($template,'FULLLIST%')!==false)
		{
		  $par=(strpos($template,'%FULLLIST%')!==false)?'%FULLLIST%':'%BLOG_FULLLIST%';
		  $entries_recent=$this->get_recententries(0,'cname, weight '.($par=='%FULLLIST%'?'ASC':'DESC').', title','ASC',$par=='%FULLLIST%');
		  $template=str_replace($par,$this->entries_fulllist($entries_recent,'',$this->category_id,$par=='%FULLLIST%'),$template);
		}

		if(strpos($template,'%TIMELINE(')!==false)
		{
			$tl= new timeline($this->page);
			$tp=Formatter::GFSAbi($template,'%TIMELINE(',')%');
			$h=Formatter::GFS($tp,'%TIMELINE(',')%');
			$timeline_output=$tl->show_timeline(false,$h,true);
			$template=str_replace($tp,$timeline_output,$template);
			$template=str_replace('<!--scripts-->','<!--scripts-->'.F_LF.'<link rel="stylesheet" href="ezg_data/timeline/timeline.css" />',$template);
		}

		if(strpos($template,'%BLOG_MOST_VISITED%')!==false)
			$template=str_replace('%BLOG_MOST_VISITED%',$this->page->SidebarBuilder->entries_sidebar($entries_most_visited,'',false,$this->category_id,'entries_mostvisited'),$template);
		if(strpos($template,'%BLOG_TOP_RANK%')!==false)
			$template=str_replace('%BLOG_TOP_RANK%',$this->page->SidebarBuilder->entries_sidebar($entries_top_rank,'',false,$this->category_id,'entries_toprank'),$template);
		if(strpos($template,'%BLOG_CATEGORY_FILTER%')!==false)
			$template=str_replace('%BLOG_CATEGORY_FILTER%',
					  $this->categories_combo($this->category_id!==false?$this->page->categoriesModule->current_category:'All'),$template);

		if($this->page->pg_settings['enable_comments'] && strpos($template,'_RECENT_COMMENTS')!==false)
		{
			$limit_expr=($this->page->pg_settings['offSettings']['maxComments']>0)? 'LIMIT 0, '.$this->page->pg_settings['offSettings']['maxComments'].'':'';
			$recent_comments=$db->fetch_all_array('
				SELECT *
				FROM '.$db->pre.$this->page->pg_pre.'comments as CC, '.$db->pre.$this->page->pg_pre.'posts as PP
				WHERE CC.approved=1 AND CC.entry_id=PP.entry_id AND PP.publish_status="published"
				ORDER BY date DESC '.$limit_expr);
		}
		if($this->page->pg_settings['enable_comments'] && strpos($template,'%BLOG_RECENT_COMMENTS%')!==false)
			$template=str_replace('%BLOG_RECENT_COMMENTS%',$this->page->SidebarBuilder->recentcomments_sidebar($recent_comments),$template);

		if(strpos($template,'%CALENDAR%')!==false) $template=$this->calendar($template);

		$objects=array($entries_obj,'BLOG_ARCHIVE','CATEGORY_LIST_VER','CATEGORY_LIST_HOR','BLOG_MOST_VISITED',
		'BLOG_RECENT_COMMENTS','BLOG_CATEGORY_FILTER','BLOG_TOP_RANK','BLOG_FULLLIST','FULLLIST','BLOG_RELATED');
		foreach($objects as $key=>$object)
		{
			if(strpos($template,'%'.$object.'(')!==false)
			{
				$template=Formatter::objClearing($object,$template);
				$obj_content_t=Formatter::GFS($template,'%'.$object.'(',')%');
				$obj_content=Formatter::pTagClearing($obj_content_t);
				$template=str_replace('%'.$object.'('.$obj_content_t.')%','%'.$object.'('.$obj_content.')%',$template);
				$template=Formatter::objDivReplacing('%'.$object.'('.$obj_content.')%',$template);
				$for_replace='%'.$object.'('.$obj_content.')%';
				if($key==0)
				{
					$entries_recent=$this->get_recententries($this->page->pg_settings['offSettings']['maxRecent']);
					$template=str_replace($for_replace,$this->page->SidebarBuilder->entries_sidebar($entries_recent,$obj_content,false,$this->category_id,'entries_recent'),$template);
				}
				elseif($key==1)
					$template=str_replace($for_replace,$this->archive_sidebar($periods_ar,$obj_content,'entries_archive'),$template);
				elseif($key==2)
					$template=str_replace($for_replace,$this->page->categoriesModule->category_sidebar(false,'ver',$obj_content,
												false,'',false,'',true,false,$this->item_category),$template);
				elseif($key==3)
					$template=str_replace($for_replace,$this->page->categoriesModule->category_sidebar(false,'hor',$obj_content,
												false,'',false,'',true,false,$this->item_category),$template);
				elseif($key==4)
					$template=str_replace($for_replace,$this->page->SidebarBuilder->entries_sidebar($entries_most_visited,$obj_content,false,$this->category_id,'entries_recent'),$template);
				if($key==5 && !$this->page->pg_settings['enable_comments'])
					$template=str_replace($for_replace,'',$template);
				elseif($key==5)
					$template=str_replace($for_replace,$this->page->SidebarBuilder->recentcomments_sidebar($recent_comments,$obj_content),$template);
				elseif($key==6)
					$template= str_replace($for_replace,
						  $this->categories_combo(($this->category_id!==false?$this->page->categoriesModule->current_category:'All'),$obj_content),$template);
				elseif($key==7)
					$template=str_replace($for_replace,$this->page->SidebarBuilder->entries_sidebar($entries_top_rank,$obj_content,false,$this->category_id,'entries_toprank'),$template);
				elseif($key==8 || $key==9)
				{
					$entries_recent=$this->get_recententries(0,'cname, weight '.($key==9?'ASC':'DESC').', title','ASC',$key==9);
					$template=str_replace($for_replace,$this->entries_fulllist($entries_recent,$obj_content,$this->category_id,$key==9),$template);
				}
				elseif($key==10)
					$template=str_replace($for_replace,$this->page->SidebarBuilder->entries_sidebar($entries_related,$obj_content,false,$this->category_id,'entries_related'),$template);
			}
		}
	}

	protected function mbox_grouping($cnt,$id,$group)
	{
		$cnt=$group?
			 str_replace(array('class="multibox','rel="lightbox'),'class="multibox LB'.$id,$cnt):
			 preg_replace_callback(array('/class="multibox/','/rel="lightbox/'),create_function('$matches','global $cn;$cn++; return "'.'class=\"multibox LB'.$id.'-".$cn;'),$cnt);
		return $cnt;
	}

	public function keywords_replace_callback($p)
	{
		if(strpos($p[0],'<') === false)
			return $p[0];
		else
			return  str_replace($p[1],'<a href="'.$this->page->build_permalink_tag(Formatter::strToLower($p[1])).'">'.$p[1].'</a>',$p[0]);
	}

	public function keywordsReplace(&$content,$key)
	{
		$content=preg_replace_callback('/(\b'.$key.'\b)(.*?>)/i',array($this,'keywords_replace_callback'),$content);
	}


	protected function body_section_html() // build blog frontpage
	{
		global $db;

		$output=$im_url='';
		$fpage=(!isset($_REQUEST['entry_id']));
		$schema=($this->f->html=='HTML5')&&!$fpage;
		$contentbuilder=$this->page->all_settings['contentbuilder'];

		if(empty($this->entries_records))
			$output=$this->page->lang_l('no posts found');
		else
		{
			$image_scale_params=$this->blogobj_params;
			$this->can_edit=$this->page->user->userCanEdit($this->page->page_info,$this->page->rel_path,$this->page->edit_own_posts_only);
			//print vars
			$curr_url=Linker::currentPageUrl();
			$apx=(strpos($curr_url, '?')!==false)?'&amp;print=1':'?print=1';
			$print_request=isset($_REQUEST['print']) && $_REQUEST['print'] == 1 && isset($_REQUEST['print_id']);
			if($print_request)
				$this->can_edit=false;

			$counter=0;
			$users_data=User::getAllUsers(false,true,true,$db);

			//getting all comments
			$entry_ids=$comments_a=array();
			foreach($this->entries_records as $k=>$v)
			{
				if($v['entry_id']!=null)
				{
					$entry_ids[]=$v['entry_id'];
					$comments_a[$v['entry_id']]=array();
				}
			}
			$entry_ids_where_str = !empty($entry_ids)?'entry_id = '.implode(' OR entry_id = ',$entry_ids):'';

			$temp=$this->page->commentModule->db_fetch_comments(0,$entry_ids_where_str);
			foreach($temp as $k=>$v)
				$comments_a[$v['entry_id']][]=$v;

			foreach($this->entries_records as $k=>$v)
			{
				$usr=isset($users_data[$v['posted_by']])?$users_data[$v['posted_by']]:$users_data[-1];
				$avatar=User::getAvatar($v['posted_by'],$db,$this->page->site_base);

				$posted_by_name=$usr['username'];
				$postedby_value=($schema?rich_snippets::wrap_prop("author",$posted_by_name):$posted_by_name);
				$can_edit_this_post=$this->can_edit && (!$this->page->edit_own_posts_only || $posted_by_name==$this->page->user->getUserCookie());
				if($print_request && $_REQUEST['print_id'] != $v['entry_id'])
					continue;
				$counter++;
				$im_src=$date_value=$footer_line=$trackbacks_url=$comments_count=$comments_line=$category_line=$trackbacks_line=$trackbacks_count='';
				$yt=false;

	 			$date_value=trim($this->page->format_dateSql($v['creation_date'],'short'));
				$time_value=trim(Date::formatTimeSql($v['creation_date'],$this->page->pg_settings['time_format'])); //time/date
				$datetime_value=$this->page->format_dateSql($v['creation_date']);
				$mod_datetime_value=$this->page->format_dateSql($v['modified_date']);

				if($schema)
				{
					$date_value=rich_snippets::wrap_prop_time('dateCreated',$date_value,$v['creation_date']);
					$datetime_value=rich_snippets::wrap_prop_time('dateCreated',$datetime_value,$v['creation_date']);
					$mod_datetime_value=rich_snippets::wrap_prop_time('dateModified',$mod_datetime_value,$v['modified_date']);
				}

				$unpub_date=($v['unpublished_date']=='0000-00-00 00:00:00')?'Continuous':$this->page->format_dateSql($v['unpublished_date']);

				$title_value_text=Formatter::sth2($v['title']);
				$title_value='<span class="title_'.$v['entry_id'].'"'.($schema?rich_snippets::get_prop("name"):'').'>'.$title_value_text.'</span>'.
							($can_edit_this_post?' <input type="button" onclick="editTL(\'title_'.$v['entry_id'].'\');return false;" class="ui_shandle_ic6">':'');

				$med_f=(strpos($image_scale_params,"%media"))?'%media%':'%image%';
				$med_f_n=Formatter::GFSAbi($image_scale_params,'%'.str_replace('%','',$med_f),'%');

				$mf=Formatter::sth($v['mediafile_url']);
				$media_fname=substr($mf,strrpos($mf,"/")+1);
				$listen_url=(strpos($mf,'http')===false?$this->page->rel_path:'').str_replace($media_fname,rawurlencode($media_fname),$mf);
				if(!$this->page->is_blog)
					$listen_line='<a class="rvts12" href="'.$listen_url.'">'.$this->page->lang_l('listen').'</a>';

				$image_line=''; $image_line_float_r='';
				if($this->page->is_blog&&empty($v['image_url']) && Video::youtube_vimeo_check($listen_url)) //check if yt used in yt filed and use its image as url
					$v['image_url']=$v['image_thumb'];
				$im_url='';
				if(!empty($v['image_url']))// image or mp3 link
				{
					$im_url=$this->page->get_file_url($v['image_url'],$this->page->rel_path);
					$media_ext=substr($im_url,strrpos($im_url,".")+1);
					$media_ext_lower=Formatter::strToLower($media_ext);
					$image_mp3_fname=substr($im_url,strrpos($im_url,"/")+1);
					$im_src=(strpos($im_url,'http')===false?$this->page->rel_path:'').str_replace($image_mp3_fname,rawurlencode($image_mp3_fname),$im_url);
					$yt=Video::youtube_vimeo_check($im_url);

					if($this->page->is_blog)
						$this->blogobj_params=str_replace(array('%listenurl3%','"%ID%"'),array($im_url,'"player_'.$counter.'"'),$this->blogobj_params);

					if(in_array($media_ext_lower,$this->page->img_file_types))
					{
						if(strpos($image_scale_params,'%SCALE['.$med_f_n)!==false)
						{
							$img_param=Formatter::GFSAbi($image_scale_params,'%SCALE['.$med_f_n,']%');
							$params_a=explode(',',Formatter::GFS($img_param,'SCALE[',']'));
							$hw_param='';
							$cp=count($params_a);
							if($cp>2)
							{
								if($params_a[2]=='')
									$params_a[2]='0';
								if($params_a[1]=='')
									$params_a[1]='0';
								$hw_param=($params_a[2]!='0')?'height="'.$params_a[2].'"':(($params_a[1]!='0')?'width="'.$params_a[1].'"':'');
							}
							if($cp==3)
								$image_line='<img class="blog_image" src="'.$im_src.'" '.$hw_param.' alt="'.$title_value_text.'"'.($schema?rich_snippets::get_prop("image"):'').'>';
							elseif($cp>3)
							{
								if($params_a[3]=='multibox')
								{
									$imgsrc=(isset($params_a[4])&&($params_a[4]!=''))?$params_a[4]:$im_src;
									$image_line='<a href="'.$imgsrc.'" rel="lightbox,noDesc" title="'.$im_url.'" class="mbox"><img class="blog_image" src="'.$im_src.'" '.$hw_param.' alt="'.$im_url.'"'.($schema?rich_snippets::get_prop("image"):'').'></a>';
								}
								elseif($params_a[3]=='url')
									$image_line='<a href="'.$params_a[4].'" title="'.$im_url.'"><img class="blog_image" src="'.$im_src.'" '.$hw_param.' alt="'.$im_url.'"'.($schema?rich_snippets::get_prop("image"):'').'></a>';
							}
							$this->blogobj_params=str_replace($img_param,$med_f_n,$this->blogobj_params);
						}
						if($image_line != '')
						{
							$iml_tmp=$image_line;
							$image_line=str_replace('>',' style="float:left;padding: 0 5px 5px 0;">',$iml_tmp);
							$image_line_float_r=str_replace('>',' style="float:right;padding: 0 0 5px 5px;">',$iml_tmp);
						}
						else
						{
							$image_line='<img class="blog_image" style="float:left;padding: 0 5px 5px 0;" alt="'.$im_url.'" src="'.$im_src.'"'.($schema?rich_snippets::get_prop("image"):'').'>';
							$image_line_float_r='<img class="blog_image" style="float:right;padding: 0 0 5px 5px;" alt="'.$im_url.'" src="'.$im_src.'"'.($schema?rich_snippets::get_prop("image"):'').'>';
						}
					}
					elseif($media_ext_lower=='pdf')
						$image_line='<a href="'.$im_src.'" target="_blank"><img class="system_img" src="http://www.ezgenerator.com/features/pdficon_large.png" alt="Download PDF" title=""></a>';
					elseif($media_ext_lower=='doc')
						$image_line='<a href="'.$im_src.'" target="_blank"><img class="system_img" src="http://www.ezgenerator.com/features/word.png" alt="Download Microsoft Word Document" title=""></a>';
					elseif(!in_array($media_ext_lower,$this->page->media_types) && !$yt)
					{
						$image_line='<a class="rvts12" href="'.$im_src.'" target="_blank">'.$image_mp3_fname.'</a>';
						$image_line_float_r=$image_line;
					}
				}
				$this->blogobj_params=str_replace(Formatter::GFSAbi($this->blogobj_params,'%SCALE['.$med_f_n,']%'),$med_f_n,$this->blogobj_params);

				$permalink_url=$this->page->build_permalink($v,false,'',true);
				$plink_for_bmarks=$this->page->build_permalink($v,true,'',true);
				$permalink_line='<a'.($schema?rich_snippets::get_prop("url"):'').' class="rvts12" href="'.$permalink_url.'">'.$this->page->lang_l('permalink').'</a>';
				$fullarticle_line='';
				if(!empty($v['content']))
					$fullarticle_line='<a class="rvts12" href="'.$permalink_url.'">'.$this->page->lang_l('full article').'</a>';
				$is_excerpt=$this->page->pg_settings['offSettings']['excerptOnFront'] && !isset($_REQUEST['entry_id']) && !empty($v['excerpt']);

				$content_line='<div'.($schema?rich_snippets::get_prop("articleBody"):'').' class="post_content'.($can_edit_this_post?' post_editable':'').'" style="display:block;position:relative;">';  // content
	//adding hidden editor if the user has edit access

				if($can_edit_this_post && !$print_request)
				{
					$textarea_content=Formatter::sth2($v['content']);
					if($contentbuilder)
						$editor_parsed=Editor::getContentBuilder_js($this->page->rel_path,$v['entry_id']);
					else{
						$editor_parsed=str_replace('oEdit1','oEdit1'.$v['entry_id'],$this->page->innova_def);
						$editor_parsed=str_replace('htmlarea','txtContent'.$v['entry_id'],$editor_parsed);
						$textarea_content=Editor::replaceClassesEdit($textarea_content);
						$this->page->innova_js=Editor::addGoogleFontsToInnova($textarea_content,$this->page->innova_js);
					}

					$content_line.='
						<div class="edit_post" id="edit_post_'.$v['entry_id'].'" style="display:none;">
							<form method="post" action="'.$this->page->script_path.'?action=save_entry_simple" '.($contentbuilder?'class="cb_form_'.$v['entry_id'].'" onsubmit="return save_cb(\''.$v['entry_id'].'\')"':'').'>
								<input class="input1" type="hidden" name="entry_id" value="'.$v['entry_id'].'">
								<input class="input1" type="hidden" name="save_entry_simple" value="1">
								<input class="input1" type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">
								<div id="edit_content'.$v['entry_id'].'" class="content_editor" style="display:block;text-align:left;clear:both;">'.
								($contentbuilder?
									'<div id="contentarea'.$v['entry_id'].'" class="containerCB" style="width:100%">'.$textarea_content.'</div>'.$editor_parsed:
									'<textarea class="mceEditor" id="txtContent'.$v['entry_id'].'" name="content" style="width:100%" rows="4" cols="30">'.$textarea_content.'</textarea>'
									.$editor_parsed)
									.'<input class="input1'.($contentbuilder?' save_button':'').'" type="submit" name="submit" value="'.$this->page->lang_l('save').'">&nbsp;
									<input class="input1'.($contentbuilder?' close_button':'').'" type="button" value="'.$this->page->lang_l('cancel').'" onclick="javascript:swap(\'post_init_content_'.$v['entry_id'].'\',\'edit_post_'.$v['entry_id'].'\');'.($contentbuilder?' hide_contentbuilder();':'').'"><br><br>
								</div>
							</form>
						</div>
						<div id="post_init_content_'.$v['entry_id'].'" style="display:block;">';
				}
	//	end of the hidden editor code
				if($is_excerpt)
				{
					$content_line.=str_replace(F_LF,F_BR,Formatter::sth($v['excerpt'])).F_BR;
					if($v['content']!='<br>' && strpos($v['excerpt'],'%permalink%')===false)
						$content_line.='<a class="rvts12" href="'.$permalink_url.'">'.$this->page->lang_l('full').'</a>';
				}
				else
				{
					$cnt=($contentbuilder?'<div class="containerCB">'.Formatter::sth2($v['content']).'</div>':Formatter::sth2($v['content']));

					if(strpos($cnt,'{%POLL_ID(') !== false) {
					 	$this->page->page_scripts.=Formatter::replacePollMacro($cnt,$this->page->rel_path,$this->page->page_lang);
					}

					if($this->page->all_settings['replace_keywords']==1 && $v['keywords']!='')
					{
						 $keywords=Meta::keywordsArray($v['keywords']);
						 foreach($keywords as $key)
							$this->keywordsReplace($cnt,$key);
					}

					if($this->page->page_is_mobile)
						$cnt=str_replace(array('width="640" height="360"','width="560" height="315"','width="700" height="398"','width="700" height="394"'),
								  'width="98%"',$cnt);
					Formatter::hideFromGuests($cnt);
					if($this->f->xhtml_on)
						$cnt=str_replace("<br>","<br />",$cnt);
					$cnt=str_replace("\'","'",$cnt);
					$cnt=$this->mbox_grouping($cnt,$v['id'],$this->page->all_settings['mbox_grouped']==1);

					if(strpos($cnt,'{%SLIDESHOW_ID(') !== false)
					{
						$slideshow=new Slideshow();
						$slideshow->replaceSlideshowMacro($cnt,$this->page->rel_path,$this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies);
					}

					if(strpos($cnt,'{%HTML5PLAYER_ID(') !== false)
					{
						$slideshow=new Slideshow();
						$slideshow->replace_html5playerMacro($cnt,$this->page->rel_path,$this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies);
					}

					if(!$fpage || $this->page->pg_settings['offSettings']['maxLines']==0 || $print_request)
						$content_line.=$cnt;
					else
					{
						$max_chrl=$this->page->pg_settings['offSettings']['maxLines']*60;
						if(strlen($cnt)>$max_chrl)
						{
							$splitted=Formatter::splitHtmlContent($cnt,$max_chrl);
							$content_line.=$splitted;
							if(strlen($cnt)>strlen($splitted) && strpos($this->blogobj_params,'%fullarticleurl%')===false)
								$content_line.='<br><a class="rvts12" href="'.$permalink_url.'">'.$this->page->lang_l('full').'</a>';
						}
						else
							$content_line.=$cnt;
					}
				}
				$content_line.='</div>';
				if($can_edit_this_post && !$print_request)
				{
					$content_line.='</div>
						<div class="rvps2"><input type="button" rel="'.$v['entry_id'].'" value="" title="'.$this->page->lang_l('edit').' +" class="edit_inline ui_shandle_ic6" style="background-color: #d7d7d7;margin: 1px 0;">
							<input type="button" onclick="document.location=\''.$this->page->script_path.'?action=edit_entry&amp;entry_id='.$v['entry_id'].Linker::buildReturnURL().'\'" value="" title="'.$this->page->lang_l('edit').'" class="ui_shandle_ic4" style="background-color: #d7d7d7;margin: 1px 0;background-position:-32px -80px">
						</div>';
				}

				$content_line.="<div style='clear:left'></div>";

				$content_line=str_replace('src="/documents','src="../documents',$content_line);  // fix for path issue
				$content_line=str_replace('src="/','src="../../',$content_line);
				$content_line=Editor::fixInnovaPaths($content_line,$this->page->pg_name,$this->page->full_script_path,$this->page->rel_path);


				if(!$this->page->is_blog)
				{
					$subtitle_value=Formatter::sth($v['subtitle']);	 // subtitle
					$author_value=Formatter::sth($v['author']);		//author
					$filesize_value='';

					if($v['mediafile_size']>1024)
						$filesize_value=round($v['mediafile_size']/1024).' KB ';
					elseif(!empty($v['mediafile_size']))
						$filesize_value=$v['mediafile_size'].' b ';

					$download_url=$this->page->script_path."?action=download&amp;entry_id=".$v['entry_id'];
					$allow_downloads=isset($v['allow_downloads'])?$v['allow_downloads']=='1':true;
					$download_line=$allow_downloads?'<a class="rvts12" href="'.$download_url.'">'.$this->page->lang_l('download').'</a>':'';
				}

				$comments_on=($this->page->pg_settings['enable_comments'] && $v['allow_comments']=='true');
				$trackbacks_on=($v['allow_pings']=='true');

				if($comments_on)
				{
					$comments_count=$v['comment_count'];
					$comments_line='<a class="rvts12" href="'.$permalink_url.'">'.$this->page->lang_l('comments').'</a> <span class="rvts8">('.$v['comment_count'].')</span>';
				}

				$cdata=$trackbacks_html=$tags_line='';
				$category_line=$this->page->categoriesModule->get_category_breadcrumb($v['category'],true,$cdata);
				$category_url=$this->page->build_permalink_cat(urlencode($cdata['name']),false,$this->page->categoriesModule->get_categoryViewDefined($v['category']));
				$print_url='<a class="mbox blog_print_btn_img cat_'.$v['category'].'" rel="noDesc['.$v['entry_id'].'],width:1000px,height:600px" href="'.$curr_url.$apx.'&amp;print_id='.$v['entry_id'].'">'
										.Builder::printImgHtml($this->page->rel_path).'</a>';

				$footer_line.='<span class="rvts8"> | </span>'.$permalink_line.'<span class="rvts8"> | </span>'.
						($comments_on?$comments_line.'<span class="rvts8"> | </span>':'')
						.$category_line;

				if($trackbacks_on)
				{
					$trackbacks_count=$v['trackback_count'];
					$trackbacks_url=$this->page->build_permalink($v,false,'trackback',true);
					$trackbacks_line='<a class="rvts12" href="'.$trackbacks_url.'">'.$this->page->lang_l('trackback url').'</a> <span class="rvts8">('.$trackbacks_count.')</span>';

					if(strpos($this->blogobj_params,'%trackbacks%')!==false)
					{
						$trackbacks_records=$db->fetch_all_array('SELECT * FROM '.$db->pre.$this->page->pg_pre.'trackbacks WHERE entry_id='.$v['entry_id'].' ORDER BY date DESC');
						$trackbacks_html=$this->page->trackbacks->trackbacks_html($trackbacks_records);
					}
				}

				if(isset($v['keywords']))
				{
					$tags_line=$this->page->get_keywords_html(Formatter::sth($v['keywords']),$schema);
					if($can_edit_this_post)
						$tags_line='<span rel="'.$v['keywords'].'" class="el_rel keywords_'.$v['entry_id'].'">'.$tags_line.'</span> <input type="button" onclick="editTL(\'keywords_'.$v['entry_id'].'\');return false;" class="ui_shandle_ic6">';
				}

				if($this->blogobj_params=='')
				{
					$output.='
						 <span class="rvts8">'.$date_value.'</span><br>
						 <span class="rvts0"><b>'.$title_value.'</b></span><br>';
					if($this->page->is_blog)
						$output.='
							<br><p>'.$image_line.'</p><br>'.$content_line.'<br><br>
							<span class="rvts8">'.$this->page->lang_l('posted by').' '.$postedby_value.'</span>';
					else
					{
						$duration_line=($v['duration']!='00:00:00'?'<span class="rvts8">'.$v['duration'].'</span>':''); // duration
						$output.='
							<span class="rvts8">'.$subtitle_value.'</span><br><br>'.$content_line.'<br>
							<span class="rvts8">'.($duration_line!=''? $this->page->lang_l('duration')." ".$duration_line.", ": "").$this->page->lang_l('size').': '.'
							<span class="rvts8">'.$filesize_value.'</span>
							</span> '.$listen_line.' '.$download_line.'<br>';
					}
					$output.='
						 <span class="rvts8"> '.$datetime_value.'</span> '.$footer_line.'
						 <hr style="border-style:dotted;height:1pt" class="hr">';
				}
				else
				{
					$parsed_line=$this->blogobj_params;

					$parsed_line=str_replace(array('=%title%','%title%','%TITLE%','%date%','%DATE%','%datetimefooter%','%trackbacks%','%DATE[','%DATETIME['),
						array('='.$title_value_text,$title_value,$title_value,$date_value,Formatter::strToUpper($date_value),$datetime_value,$trackbacks_html,'%date[','%datetime['),
						$parsed_line);

					while(strpos($parsed_line,'%date[')!==false)
					{
						$this->page->date_params=Formatter::GFS($parsed_line,'%date[',']%');
						$date_value=trim($this->page->format_dateSql($v['creation_date'],'short'));
						$parsed_line=str_replace('%date['.$this->page->date_params.']%',$date_value,$parsed_line);
					}
					while(strpos($parsed_line,'%datetime[')!==false)
					{
						$this->page->datetime_params=Formatter::GFS($parsed_line,'%datetime[',']%');
						$datetime_value=$this->page->format_dateSql($v['creation_date']);
						$parsed_line=str_replace('%datetime['.$this->page->datetime_params.']%',$datetime_value,$parsed_line);
					}

					$parsed_line=str_replace('<fb:like','<fb:like href="'.$this->page->full_script_path."?entry_id=".$v['entry_id'].'"',$parsed_line);

					if(strpos($parsed_line,'<fb:comments')!==false)
					{
						$tmp=Formatter::GFSAbi($parsed_line,'<fb:comments','>');
						$href=Formatter::GFSAbi($tmp,'href="','"');
						$tmp_rep=str_replace($href,'href="'.$this->page->full_script_path."?entry_id=".$v['entry_id'].'"',$tmp);
						$parsed_line=str_replace($tmp,$tmp_rep,$parsed_line);
					}

					if(isset($_REQUEST['entry_id']) && strpos($parsed_line,'%fullarticleurl%')!==false)
						$parsed_line=preg_replace("'<a.*%fullarticleurl%.*>.*</a>'",'',$parsed_line);

					$slideshow=new Slideshow();
					$slideshow->parse_slideshow($this->page->rel_path,$v['slideshow']!=''?explode('|',$v['slideshow']):array(),true,
						$parsed_line,$this->page->page_scripts,$this->page->page_css,$v['slideshow_type'],$v['id']);
					$slideshow->updateSlideshowDependencies($this->page->page_scripts,$this->page->page_dependencies);
					if(strpos($parsed_line,'ToggleBody')>0)
						$parsed_line=Formatter::parseDropdown($parsed_line,$counter);

					$free_field=($this->page->fl_studio_flag && $v['free_field']=='')?'http://forum.image-line.com/':$v['free_field'];

					$parsed_line=str_replace(
							  array('<div id="tab','%content%','%excerpt%','%freefield%','%publish_status%'),
							  array('<div id="tab'.$counter,
									$content_line,
									'<span class="post_excerpt">'.str_replace(F_LF,F_BR,Formatter::sth($v['excerpt'])).'</span>',
									Formatter::sth($free_field),$v['publish_status']),
							  $parsed_line);

					$parsed_line=str_replace(
								array('%permalinkurl%','%commentsurl%','%fullarticleurl%','%permalinkabsurl%'),
								$permalink_url,
								$parsed_line);
					$parsed_line=str_replace(array('%shorturl%','%permalink%','%fullarticle%','%footer%','%commentslink%','%commentscount%','%trackbackslink%','%trackbacksurl%','%categoryurl%'),
						array($plink_for_bmarks,$permalink_line,($fpage?$fullarticle_line:''),$footer_line,($fpage?$comments_line:''),$comments_count,$trackbacks_line,$trackbacks_url,$category_url)
						,$parsed_line);
					$parent=-1;
					$parsed_line=str_replace(array('%unpublish_date%','%modified_date%','%user:avatar%','%trackbackscount%','%print button%','%category%',
						'%category_name%','%category_name_encoded%','%category_color%','%category_description%'),
						array($unpub_date,$mod_datetime_value,$avatar,$trackbacks_count,($print_request?'':$print_url),$category_line,
						$cdata['name'],urlencode($cdata['name']),'#'.$cdata['color'],'<div class="category_description">'.$this->page->categoriesModule->get_category_info($v['category'],'description',$parent).'</div>')
						,$parsed_line);

					$parsed_line=str_ireplace(array('%dateago%','%datetime%','%time%','%postedby%','%author%','%entry_id%'),
						array(Date::dateAgo($v['creation_date'],$this->page->pg_settings['lang_uc']),
							 $datetime_value,
							 $time_value,
							 $postedby_value,
							 $postedby_value,
							 $v['entry_id']),
						$parsed_line);
					$parsed_line= str_replace(array('%tags%','%TAGS%'),$tags_line,$parsed_line);

					$parsed_line=str_replace('%ranking%',$this->page->rankingVisitsModule->build_ranking($v),$parsed_line);
					$parsed_line= str_replace(array('%viewed%','%visits%'),$v['visits_count'],$parsed_line);

					$path_prefix=$media_filename=$media_filename_unenc=$media_folder=$media_ext='';

					if(!$this->page->is_blog  || (!empty($v['image_url']))) // media
					{
						$media_value_unenc=$this->page->get_file_url($v[(!$this->page->is_blog?'mediafile_url':'image_url')],'');
						$media_value=str_replace(" ","+",$media_value_unenc);
						$media_filename_unenc=substr($media_value_unenc,strrpos($media_value_unenc,"/")+1);
						$media_filename=substr($media_value,strrpos($media_value,"/")+1);
						$media_folder=str_replace($media_filename,'',$media_value);
						$media_ext=Formatter::GFSAbi(Formatter::strToLower($media_filename),'.','');
						if(strpos(strtolower($media_value),'http')===false)
							$path_prefix=(($media_ext=='.flv' || $media_ext=='.mp4')?'../':$this->page->rel_path);

						$yt=Video::youtube_vimeo_check($media_value);
						if($yt && (strpos($media_filename,'http')===false))
							$media_filename='http://'.$media_filename;
					}
					if(!$this->page->is_blog && strpos('.mp3|.flv|.swf|.mp4|',$media_ext)!==false  && $media_ext!='.' ) // flash player
					{
						$parsed_line=str_replace('%ID%','player_'.$counter,$parsed_line);
						if(strpos($parsed_line,'%listenurl2%')!==false)
							$parsed_line=str_replace('%listenurl2%',$media_filename,$parsed_line);
						if(strpos($parsed_line,'%listenurl3%')!==false)
						{
							$parsed_line=str_replace(Formatter::GFS($parsed_line,'audurl=','%listenurl3%'),'',$parsed_line);
							$lu=$yt?$media_filename:$path_prefix.$media_folder.$media_filename.($im_src!=''?'&image='.$im_src:'').'&title='.$title_value_text;
							$parsed_line=str_replace('%listenurl3%',$lu,$parsed_line);
						}

						if(strpos($media_folder,'podcast/php')===false)
							$parsed_line=str_replace('mp3dir='.$this->page->rel_path."podcast/php",'mp3dir='.$this->page->rel_path.$media_folder,$parsed_line);
					}
					elseif((!$this->page->is_blog) || $yt)
						$this->page->av_object->handle_youtube_vimeo_player($parsed_line,$yt,$media_value,$v,$image_line);
					elseif($this->page->is_blog)
					{
						 if(!empty($v['image_url']) && (strpos('.mp3|.flv|.swf|.mp4|','.'.$media_ext_lower)!==false))
						 {
								if($media_ext_lower=='mp3')
								{
									$player_js='<div class="player">%html5player[]%</div>';
									$this->page->av_object->parse_html5_audiovideo($player_js,'.mp3',$path_prefix.$v['image_url']);
								}
								else
									$player_js=$this->page->av_object->get_player($media_folder,$media_filename,$media_ext_lower,$v['entry_id']);

								$image_line='<div style="z-index:1;float:left;padding: 0 5px;">'.$player_js.'</div>';
								$image_line_float_r='<div style="z-index:1;float:left;padding: 0 0 5px 5px;">'.$player_js.'</div>';
						}
						else
						{
							$yt_in_yt_fld=Video::youtube_vimeo_check($listen_url);
							$scloud=strpos($listen_url,'soundcloud')!==false;
							if($yt_in_yt_fld || $scloud)
								$this->page->av_object->handle_youtube_vimeo_player($parsed_line,$yt_in_yt_fld,urldecode($listen_url),$v,$image_line,true);
						}
					}

					$this->page->av_object->parse_html5_audiovideo($parsed_line,$media_ext,$path_prefix.$media_folder.$media_filename_unenc);

					if(!$this->page->is_blog)
					{
						 $parsed_line=str_replace(
							array('%description%','%subtitle%','%author%','%listen%','%listenurl%','%download%','%download_count%','%size%','%duration%'),
							array($content_line,$subtitle_value,$schema?rich_snippets::wrap_prop("author",$author_value):$author_value,$listen_line,$listen_url,($yt?'':$download_line),$v['download_count'],($yt?'':$filesize_value),($v['duration']!='00:00:00'?$v['duration']:''))
						,$parsed_line);

						$p=strpos($parsed_line,'%downloadurl%');

						if(($yt && $p!==false) || !$allow_downloads)
						{
							$t=substr($parsed_line,0,$p);
							$last_a_pos=strrpos($t,'<a'); $a_start=substr($t,$last_a_pos).'%downloadurl%';
							$parsed_line=str_replace(Formatter::GFSAbi($parsed_line,$a_start,'</a>'),'',$parsed_line);
						}
						else
							$parsed_line=str_replace('%downloadurl%',$download_url,$parsed_line);
					}

					$parsed_line=str_replace(
						array('%image%','%video%'),
						array($image_line,''),$parsed_line);

					if(isset($img_param))
						 $parsed_line=str_replace($img_param,strpos($img_param,'right')?$image_line_float_r:$image_line,$parsed_line);

					if(strpos($med_f_n,'right'))
						$parsed_line=str_replace($med_f_n,$image_line_float_r,$parsed_line);
					elseif(strpos($med_f_n,'left'))
						$parsed_line=str_replace($med_f_n,$image_line,$parsed_line);

					$parsed_line=str_replace(
							array('<div align="left">'.$med_f.'</div>',$med_f,'%media_url%'),
							array(str_replace('style="float:left;padding: 0 5px 5px 0;"','',$image_line),
								 str_replace('style="float:left;padding: 0 5px 5px 0;"','',$image_line),$im_url),
							$parsed_line);

					$titleNorm=Translit::simpleTranslit($title_value_text);
					$output.='<div class="post_wrap'.(Unknown::isOdd($counter)?' odd':' even').($counter==1?' first':'')
							.'"'.($schema?rich_snippets::get_schema('BlogPosting'):'').' id="'.$titleNorm.'">'.$parsed_line.'</div>';
				}

				$output=$this->page->commentModule->display_comments($comments_on,$v['entry_id'],
						  $output,$can_edit_this_post,$print_request,$comments_a[$v['entry_id']],$this->is_view);
			}

			$output='
				<div class="blog_container">'
					.$output.'
					<div class="clear"></div>
				</div>';
		}

		if(strpos($output,'init_contentbuilder(')!==false)
		{
			Editor::getContentBuilder_scripts($this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies,true,$this->page->is_blog);
			$lang=isset($this->page->pg_settings['ed_lang'])?$this->page->pg_settings['ed_lang']:'english';
			Editor::addSlideshow_Plugin_contentBuilder($this->page->page_scripts,$this->page->page_dependencies,$this->page->rel_path,$lang,$this->page->lang_l('slideshow'),$this->page->pg_id);
		}

		if(strpos($output,'<!--cbuilder_slideshow-->')!==false)
		{
			$slideshow=new Slideshow();
			$slideshow->replaceSlideshow_contentBuilder($output,$this->page->rel_path,$this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies);
		}

		if($contentbuilder)
			Editor::getContentBuilder_scripts($this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies,false);

		if(strpos($output,'%COPY[') !== false)
			Formatter::replaceCopyMacro($output);

		return $output;
	}

	protected function trackbacks_view()
	{
		$entry_record=$this->page->db_fetch_entry($this->entry_id,$this->page->where_public);
		$this->entries_records=array($entry_record);
	   $output=$this->body_section_html();
		$output.=$this->page->trackbacks->get_trackbacks_html($this->entry_id,$this->entries_records);

		return $output;
	}

	protected function tag_view(&$template)
	{
		$tag=addslashes(Formatter::strToLower($this->tag));
		$where='keywords LIKE "%'.$tag.'%" AND '.$this->page->where_public;
		$this->count_all_entries=$this->page->db_count('posts',$where);
		$this->entries_records=$this->page->db_fetch_entries_slice($this->count_all_entries,$this->page->pg_settings['offSettings']['maxEntries'],$where,$this->page->order_type);
		$this->append_nav_url=($this->page->use_alt_plinks? 'tag/'.urlencode($tag).'/'.($this->view!=''?$this->view."/":''):$this->view.'tag='.urlencode($tag));
		if(empty($this->entries_records))
		{
			if($this->page->all_settings['error_404_page']!=''){
				$this->tag='';
				$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
				header('Location: '.$url_404_page);
			} else {
				$this->tag='';
				$output=$this->no_posts_msg;
				$this->header=404;
			}
		}
		else
		{
			 $this->tag=strpos($this->entries_records[0]['keywords'],$this->tag)!==false
						?$this->tag
						:$this->entries_records[0]['keywords'];
			 $output=$this->body_section_html();
		}

		$this->page_navigation();
		$tags_title=Formatter::sth($tag);
		$_title='<h1 class="page-title">'.$this->page->lang_l('archives tag').' <span style="font-weight:normal">'.$tags_title.'</span></h1>';
		$template=str_replace('%BLOG_OBJECT',$_title.'%BLOG_OBJECT',$template);
		return $output;
	}

	protected function search_view()
	{
		global $db;
		$cat_name='';
		$this->count_all_entries=0;
		$search_string=trim($_REQUEST['q']);
		if(empty($search_string))
			$output=$this->no_posts_msg;
		else
		{
			if(isset($_REQUEST['category']))
			{
				$cat_array=(is_array($_REQUEST['category']))?$_REQUEST['category']:array(0=>$_REQUEST['category']);
				foreach($cat_array as $k=>$v)
				{
					$category=Formatter::stripTags($v);
					$category=Formatter::unEsc(urldecode($category));
					if($category=='All categories')
						break;
					else
						$cat_name[]=$category;
				}
			}

			$ss=$db->escape(Formatter::stripTags($search_string));
			$output=$this->page->db_search_in_entries($ss,'user','','',$cat_name);
			if($this->page->use_alt_plinks)
			{
				$search_result_ids=array();
				while(strpos($output,$this->page->full_script_path.'?entry_id=')!==false)
				{
					$item_id=Formatter::GFS($output,'href="'.$this->page->full_script_path.'?entry_id=','"');
					$search_result_ids[]=$item_id;
					$output=str_replace('href="'.$this->page->full_script_path.'?entry_id='.$item_id.'"','href="%%'.$item_id.'%%"',$output);
					$output=str_replace($this->page->full_script_path.'?entry_id='.$item_id,'%%'.$item_id.'%%',$output);
				}
				$search_result_range=implode(',',$search_result_ids);
				if(!empty($search_result_range))
				{
					$this->entries_records=$db->fetch_all_array('
						SELECT *
						FROM '.$db->pre.$this->page->pg_pre.'posts
						WHERE '.$this->page->where_public.' AND entry_id IN ('.$search_result_range.')');
				}
				foreach($this->entries_records as $k=>$v)
					$output=str_replace('%%'.$v['entry_id'].'%%',$this->page->build_permalink($v,false,'',true),$output);
				$output=str_replace($this->page->pg_name,'',$output); //miro fix for wrong search url's
			}
			$this->search_styles=Formatter::GFSAbi($output,'<style type="text/css">','</style>');
			if(!empty($this->search_styles))
				$output=str_replace($this->search_styles,'',$output);
		}

		return $output;
	}

	protected function entry_view()
	{
		$where=($this->page->action_id=='entrypreview')?'':$this->page->where_archived;
		$prev=$next=$prev_title=$next_title='';
		$entry_record=$this->db_fetch_entry_nextprev($this->entry_id,$where,$this->page->order_type,
				  $prev,$next,$prev_title,$next_title);
		$this->entries_records=array($entry_record);
		if($entry_record===false)
		{
			if($this->page->all_settings['error_404_page']!=''){
				$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
				header('Location: '.$url_404_page);
			} else {
				$output=$this->no_posts_msg;
				$this->header=404;
			}
		}
		else
		{
			$this->item_category=$entry_record['category'];

			if($this->page->pg_settings['enable_comments'] && $entry_record['allow_comments']=='true') //comments
			{
				Session::intStart('private');
				if(isset($_POST['sign_id']))
					$output=$this->page->commentModule->save_comment($this->entry_id); // save comment
				else // all comments for specific entry
				{
					$can_edt=false;
					if(!$this->is_view && strpos($this->blogobj_params,'%comments%')===false)
						$this->blogobj_params.='%comments%';
					$out=$this->body_section_html();

					$output=$this->page->commentModule->display_comments(true,$this->entry_id,$out,$can_edt,false,false,$this->is_view);
					if(strpos($output,'class="blog_comments_form')===false)
						$output.=$this->page->commentModule->comment_form($this->entry_id);
				}
			}
			elseif(!empty($entry_record))
				 $output=$this->body_section_html();
			else
			{
				 $hidden_entry=$this->page->db_fetch_entry($this->entry_id,'publish_status="published"');
				 $this->entries_records=array($hidden_entry);
				 if($hidden_entry!==false)
					 $output=$this->body_section_html();
				 else
					 $output=$this->f->error_iframe!==false?$this->f->error_iframe : $this->page->lang_l('no posts found');
			}
			$this->navigation_section=Navigation::entry($prev,$next,$prev_title,$next_title,$this->page->script_path,$this->page->nav_labels,$this->nav_par);
			$this->canonical=$this->page->build_permalink($entry_record,false,'',true);
		}
		return $output;
	}

	protected function archive_view(&$template)
	{
		$where=$this->page->date_used.' > \''.$this->year.'-'.$this->mon.'-01 00:00:00\' AND '.$this->page->date_used.' < \''.$this->year.'-'.$this->mon.'-31 23:59:59\' AND '.$this->page->where_archived_public;

		$this->count_all_entries=$this->page->db_count('posts',$where);
		$this->entries_records=$this->page->db_fetch_entries_slice($this->count_all_entries,$this->page->pg_settings['offSettings']['maxEntries'],$where,$this->page->order_type);
		$this->append_nav_url=($this->page->use_alt_plinks? $this->year.'/'.$this->mon.'/'.($this->view!=''?$this->view."/":''):$this->view.'mon='.$this->mon.'&amp;year='.$this->year);

		$output=$this->body_section_html();
		$this->page_navigation();
		$label=Formatter::sth(Formatter::strToUpper($this->page->month_name[$this->mon-1]).' '.$this->year);
		$_title='<h1 class="page-title">'.$this->page->lang_l('archives monthly').' <span style="font-weight:normal">'.$label.'</span></h1>';
		$template=str_replace('%BLOG_OBJECT',$_title.F_BR.'%BLOG_OBJECT',$template);
		return $output;
	}

	protected function category_view(&$template)
	{
		if($this->category_id===false)
		{
			if($this->page->all_settings['error_404_page']!=''){
				$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
				header('Location: '.$url_404_page);
			} else {
				$output=$this->no_posts_msg;
				$this->header=404;
			}
		}
		else
		{
		  $cdata='';
		  $crumb_name=$this->page->categoriesModule->get_category_breadcrumb($this->category_id,false,$cdata);
		  $category=$cdata['name'];
		  $color=$cdata['color'];
		  list($kids_count,$kids_ids)=$this->page->categoriesModule->get_category_kids_count($this->category_id,true);
		  $kids_ids=implode(',', array_merge(array($this->category_id),$kids_ids));
		  $this->count_all_entries=$this->page->categoriesModule->category_array[$this->category_id]['count']+$kids_count;
		  $this->entries_records=$this->page->db_fetch_entries_slice($this->count_all_entries,$this->page->pg_settings['offSettings']['maxEntries'],$this->page->where_public.' AND category IN ('.$kids_ids.')',$this->page->order_type);
		  $url=str_replace($this->page->pg_name,'',$this->page->script_path);
		  $this->append_nav_url=($this->page->use_alt_plinks?
					 str_replace($url,'',$this->page->build_permalink_cat($category))	:
					 $this->view.'category='.urlencode($category));

		  if(empty($this->entries_records))
		  {
			if($this->page->all_settings['error_404_page']!=''){
				$url_404_page = Linker::buildSelfURL('',false,$this->page->rel_path).$this->page->all_settings['error_404_page'];
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
				header('Location: '.$url_404_page);
			} else {
				$output=$this->no_posts_msg;
				$this->header=404;
			}
		  }
		  else
				$output= $this->body_section_html();

		  $this->page_navigation();

		  if($this->page->action_id=='miniview')
				$_title='';
		  else
		  {
				$parent=0;
				$this->description=$this->page->categoriesModule->get_category_info($this->category_id,'description',$parent);

				if(strpos($this->description,'{%SLIDESHOW_ID(') !== false)
				{
					$slideshow=new Slideshow();
					$slideshow->replaceSlideshowMacro($this->description,$this->page->rel_path,$this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies);
				}

				if(strpos($this->description,'{%HTML5PLAYER_ID(') !== false)
				{
					$slideshow=new Slideshow();
					$slideshow->replace_html5playerMacro($this->description,$this->page->rel_path,$this->page->page_scripts,$this->page->page_css,$this->page->page_dependencies);
				}

				if($this->category_header!='')
				{
					 $category_header_parsed=str_replace(
						 array('%name%','%color%','%description%','%crumb%','&lt;CATEGORY_HEADER&gt;','&lt;/CATEGORY_HEADER&gt;','%image%'),
	 					 array($category,$color,$this->description,$crumb_name,'','',
							  ($cdata['image1']!=''?'<img class="ct_image" src="'.$cdata['image1'].'" alt="">':'')),
						 $this->category_header);
					 $template=str_replace($this->category_header,$category_header_parsed,$template);
				}
				else
				{
					 $cat_description=strpos($output,'class="category_description"')===false?'<div class="category_description">'.$this->description.'<div style="clear:both;"></div></div>':'';
					 $_title='<h1 class="page-title">'.$crumb_name.'</h1>'.$cat_description;
					 $template=str_replace('%BLOG_OBJECT',$_title.'%BLOG_OBJECT',$template);
				}
				$template=$this->mbox_grouping($template,$cdata['id'],$this->page->all_settings['mbox_grouped']==1);
		  }
		}
		$this->canonical=$this->page->build_permalink_cat($category);
		return $output;
	}

	public function home_view()
	{
		$this->count_all_entries=count($this->entries_full);
		$this->entries_records=$this->page->db_fetch_entries_slice($this->count_all_entries,$this->page->pg_settings['offSettings']['maxEntries'],$this->page->where_public,$this->page->order_type);
		$output=$this->body_section_html();

		$this->page_navigation($this->page->use_alt_plinks?
				($this->view!=''?$this->view."/":''):
				($this->view!=''?'?'.$this->view:''),false);
		return $output;
	}

	public function prepare_output()
	{
		$output='';
		$this->resolve_view();

		if($this->page->action_id=='miniview')
			$this->page->pg_settings['template_path']='versioninfotemplate.html';
		$template=File::read($this->page->pg_settings['template_path']);
		$template=Images::parse_scale_macros($template);

		$template=str_replace(array('%PODCAST_','%BLOG_CATEGORY_LIST_VER','%BLOG_CATEGORY_LIST_'),
						array('%BLOG_','%CATEGORY_LIST_'),$template);//to be removed soon, parsed in ezg

		$this->nav_par='';
		$nav='%BLOG_NAVIGATION';
		$this->add_nav=strpos($template,$nav)!==false;
		if($this->add_nav)
		{
			$this->force_nav=false;
			$this->nav_par=Formatter::GFS($template,$nav,'%');
		}
		else $this->force_nav=(strpos($template,'%NO_BLOG_NAVIGATION')===false);

		$this->category_header=(strpos($template,'&lt;CATEGORY_HEADER&gt;')!==false)?
		  Formatter::GFSAbi($template,'&lt;CATEGORY_HEADER&gt;','&lt;/CATEGORY_HEADER&gt;'):'';

		$template=Builder::buildLoggedInfo(
						$template,
						$this->page->pg_id,
						$this->page->rel_path,
						'lang='.
						$this->page->page_lang);

		if(strpos($template,'%BLOG_OBJECT(')!==false)
		{
			$template=Formatter::objClearing("BLOG_OBJECT",$template);
			$blogobj_params_t=Formatter::GFS($template,'%BLOG_OBJECT(',')%');
			$this->blogobj_params=Formatter::pTagClearing($blogobj_params_t);
			$template=str_replace("%BLOG_OBJECT(".$blogobj_params_t,"%BLOG_OBJECT(".$this->blogobj_params,$template);
			$template=Formatter::objDivReplacing('%BLOG_OBJECT('.$this->blogobj_params.')%',$template);
			$template=str_replace(array('%BLOG_OBJECT('.$this->blogobj_params.')%','%DATE[','%DATETIME['),
					  array('%BLOG_OBJECT%','%date[','%datetime['),$template);

			$this->page->datetime_params=(strpos($this->blogobj_params,'%datetime[')!==false)? Formatter::GFS($this->blogobj_params,'%datetime[',']%'):'';
			$this->page->date_params=(strpos($this->blogobj_params,'%date[')!==false)?Formatter::GFS($this->blogobj_params,'%date[',']%'):'';

			$this->blogobj_params=$this->page->commentModule->replace_comments_macros($this->blogobj_params,true);
		}
		else
		{
			$template=Formatter::objDivReplacing('%BLOG_OBJECT%',$template);
			$template=$this->page->commentModule->replace_comments_macros($template,false);
		}

		if($this->page->action_id=='miniview')
			 $template=Formatter::GFS($template,Formatter::GFSAbi($template,'<body','>'),'</body>');

		if(strpos($template,'%TAGS_CLOUD')!==false)
		{
			$tc=new tags_cloud($this->page);
			$tc->parse_tagcloud($template);
		}

		if(strpos($template,'%CATEGORY_CLOUD(')!==false)
			$template=str_replace(Formatter::GFSAbi($output,'%CATEGORY_CLOUD(',')%'),$this->page->categoriesModule->build_category_cloud(),$template);


		$periods_ar=$archive_entries_collaps=array();
		$collaps_used=(strpos($template,'%BLOG_ARCHIVE_COLLAPS%')!==false);
		$this->build_array($this->entry_id,$periods_ar,$archive_entries_collaps,$collaps_used);

// building output
		if(!$this->page->all_settings['disable_trackbacks'] && $this->entry_id>0 && $this->page->action_id=='trackback')
			 $output=$this->trackbacks_view();
		elseif($this->entry_id>0 && $this->page->action_id!='ranking')
			 $output=$this->entry_view();
 		elseif(isset($_REQUEST['q']) )
			 $output=$this->search_view();
		elseif($this->tag!='')
			 $output=$this->tag_view($template);
 		elseif($this->mon>0)
			 $output=$this->archive_view($template);
		elseif(isset($_REQUEST['category']) && $_REQUEST['category']!=='all')
			 $output=$this->category_view($template);
		else
			 $output=$this->home_view();

		$this->parse_macros($periods_ar,$collaps_used,$archive_entries_collaps,
			  $nav,$output,$template);

		$this->parse_meta($template);
		$this->parse_og($template);

		$template=str_replace(array('%BLOG_TITLE%','%commentsbody%','%commentsformbody%','%LINK_TO_ADMIN%','%permalinkurl%'),
				array('','','',
					 $this->page->rel_path.'documents/centraladmin.php?pageid='.$this->page->pg_id.'&amp;indexflag=index',
					 $this->page->full_script_path),
				$template);

		if($this->category_header!='')
			$template=str_replace($this->category_header,'',$template);

		if($this->page->use_alt_plinks)
		{
			$base_url=str_replace($this->page->pg_name,'',$this->page->script_path);
			$template=str_replace('</title>','</title>'.F_LF.'<base href="'.$base_url.'">',$template);
			$template=str_replace($this->page->script_path.'?action=rss"',$base_url.'rss/"',$template);
			$template=str_replace($this->page->script_path.'?action=commentsrss"',$base_url.'comments/rss/"',$template);
		}
		Captcha::includeCaptchaJs($template,$this->page->rel_path,$this->page->page_scripts,$this->page->page_dependencies);

		$this->direct_editing_global($template);

		$this->page->page_css.='.img_comment_maxw{max-width:400px;}';
		if(isset($this->page->pg_settings['offSettings']['columns']) && $this->page->pg_settings['offSettings']['columns']>1)
			$this->page->page_css.='.post_wrap{float:left;width:'.(floor(100/intval($this->page->pg_settings['offSettings']['columns']))).'%;overflow:hidden;}';

		if(strpos($template,'class="xzoom"')!==false && strpos($template,'$(document).ready(function(){$(".xzoom").xzoom(')===false)
			$this->page->page_scripts.='$(document).ready(function(){$(".xzoom").xzoom();});';

		if(strpos($this->blogobj_params,'%ranking%')!==false)
			$this->page->page_scripts.=$this->page->rankingVisitsModule->get_rankingScript();

		if(strpos($template,'yt_auto')!=false)
		  $this->page->page_scripts.='$(document).ready(function(){$("body").fitVids();});';

		if(strpos($this->page->page_scripts,'.poll({')!==false)
		  $this->page->page_dependencies[]='documents/poll.css';

		Formatter::replaceIfMacro($template);

		if($this->search_styles!='')
			 $template=str_replace('</head>',$this->search_styles.'</head>',$template);
		if($this->page->page_scripts!=='' || $this->page->page_dependencies)
			 $template=Builder::includeScript($this->page->page_scripts,$template,$this->page->page_dependencies,$this->page->rel_path);
		if($this->page->page_css!=='')
			 $template=Builder::includeCss($template,$this->page->page_css);

	//print handling
		$print_action=isset($_REQUEST['print']) && $_REQUEST['print']==1;
		if($print_action)
			output_generator::printEntry('blog_print_btn',$this->page->rel_path,$output,$template);
	//end of print handling

		$output=Builder::multiboxImages($template,$this->page->rel_path);

		if($this->canonical!='')
			$output=str_replace('<!--scripts-->',F_LF.'<link rel="canonical" href="'.$this->canonical.'"><!--scripts-->',$output);

		$output=$this->page->categoriesModule->include_categories_inmenu($output);

		if(strpos($output,'vForm()')!==false)
		{
			$val_js=
			'function vForm(){
				mfb=document.forms["b_edit_form"];
				if(mfb["title"].value==""){
					alert("'.strtoupper($this->page->lang_l('title'))." ".$this->page->lang_l('field is required').'!");
					return false
				};
			};';
			$output=Builder::includeScript($val_js,$output);
		}
		if($this->page->pg_settings['enable_comments'])
		  $this->page->commentModule->add_comments_js($output);
		$output=Builder::includeGFonts($output);

		if($this->page->action_id=='page_view')
		{
			$apanel=$this->page->build_admin_menu();
			$output=Formatter::formatPageView($output,$apanel,$this->page->rel_path);
		}

		$this->page->setState('show_frontpage');
		if($this->header>0)
			header::display_header($this->header);

		$this->page->relToAbs($output);

		print $output;
	}
}

$settings=array();
$settings['page_type']=$page_type;
$settings['template_fname']=$blog_template_fname;
$settings['template_path']=$blog_template;
$settings['views']=$views;
$settings['mobile_detect_mode']=$mobile_detect_mode;
$settings['admin_emails_arr']=$admin_emails_arr;
$settings['from_email']=$from_email;
$settings['notify_post']=$notify_post;
$settings['notify_comment']=$notify_comment;
$settings['notify_track']=$notify_track;
$settings['notification_subject']=$notification_subject;
$settings['notification_body']=$notification_body;
$settings['enable_comments']=$enable_comments;
$settings['forbid_urls']=$forbid_urls;
$settings['comments_require_approval']=$comments_require_approval;
$settings['comments_mustbelogged']=$comments_mustbelogged;
$settings['comments_hidenotlogged']=$comments_hidenotlogged;
$settings['comments_require_email']=$comments_require_email;
$settings['comments_email_enabled']=$comments_email_enabled;
$settings['time_format']=$time_format;
$settings['rss_image_width']=$rss_image_width;
$settings['rss_cache']=$rss_cache;
$settings['use_excerpt_in_rss']=$use_excerpt_in_rss;
$settings['max_lines_in_rss_desc']=$max_lines_in_rss_desc;
$settings['max_items_in_rss']=$max_items_in_rss;
$settings['use_html_inrss']=$use_html_inrss;
$settings['rss_settings']=$rss_settings;
$settings['page_encoding']=$page_encoding;
$settings['lang_l']=$lang_l;
$settings['lang_u']=$lang_u;
$settings['lang_uc']=$lang_uc;
$settings['ed_lang']=$ed_lang;
$settings['tiny_lang']=isset($tiny_lang)?$tiny_lang:'en';
$settings['ed_bg']=$ed_bg;
$settings['rtl']=$rtl;

$settings['timeline_init_zoom']=$timeline_init_zoom;
$settings['timeline_lang']=$timeline_lang;
$settings['timeline_reversed']=$timeline_reversed;
$settings['inmenu']=$inmenu;
$settings['inmenu_sub']=isset($inmenu_sub)?$inmenu_sub:false;
$settings['mini_editor']=isset($mini_editor)?$mini_editor:false;

$offSettings=array(
		'view'=>'',
		'maxEntries'=>$max_entries,
		'maxLines'=>$max_lines_per_entry,
		'maxRecent'=>$max_recent_entries,
		'maxComments'=>$max_recent_comments,
		'excerptOnFront'=>$use_excerpt_on_frontpage,
		'sidebarLenght'=>$sidebar_max_entries_chars,
		'reversed'=>$reversed,
		'orderBy'=>$posts_orderby,
		'columns'=>(isset($bcolumns)?$bcolumns:1),
		'linked_to_view'=>(isset($linked_to_view)?$linked_to_view:'')
		);

$settings['offSettings']=$offSettings;
	//mobile view hardcoded and forbidden as name of custom view
$settings['views']['mobile']=$offSettings;
$settings['views']['mobile']['view']='mobile';
$settings['views']['mobile']['id']=9999;
$settings['views']['mobile']['columns']=1;

$blog=new BlogPageClass($page_id,$script_dir,$script_name,$rel_path,$settings);
$blog->process();

?>

<?php
/*
	search.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/

$AccChars=array("á","à","â","ã","à","Á","À","Â","Ã","À","ç","Ç","ð","é","è","ê","É","È","Ê","í","ì","î","Í","Ì","Î","ó","ò","ô","Ó","Ò","Ô","‘","Š","ú","ù","û","Ú","Ù","Û","ý","Ý","ž","Ž","ä","Ä","ë","Ë","ï","Ï","ö","Ö","ü","Ü","ÿ","Ÿ","å","Å","ø","Ø","æ","Æ","œ","Œ","ß");
$NormChars=array("a","a","a","a","a","A","A","A","A","A","c","C","d","e","e","e","E","E","E","i","i","i","I","I","I","o","o","o","O","O","O","s","S","u","u","u","U","U","U","y","Y","z","Z","a","A","e","E","i","I","o","O","u","U","y","Y","aa","Aa","oe","Oe","ae","AE","oe","OE","ss");

class SearchHandlerClass
{
	public $version='ezgenerator v4 - search 5.3.11 mysql';
	public $rel_path;
	public $self_url;
	protected $s_php_pages_ids=array('20','136','137','138','143','144','181','190');
	public $s_gt_page;
	protected $s_full_path_to_script;
	protected $lang='EN';
	protected $s_charset;
	protected $s_using_template_page=false;
	protected $query_st_time;
	protected $action_id;
	protected $call_from_outside;
	protected $ajax;
	protected $livesearch;
	protected $s_advanced_search=true;
	protected $s_internal_use=false;
	protected $s_index_template_areas=true;
	protected $s_partial=false;
	protected $showresulttime=false;
	protected $s_clean_range='all';
	protected $lang_l=array("0"=>array("all these words"=>"all these words","Result"=>"Result","Page"=>"Page","From"=>"From","first"=>"first","prev"=>"prev","next"=>"next","last"=>"last","no matches found"=>"No matches found","Search"=>"Search","search box empty"=>"Search box is empty. Please, type search keyword in it.","page created in"=>"Page generated in","seconds"=>"seconds","last modified"=>"Last modified","find pages that"=>"Find web pages that have","this exact wording"=>"this exact wording or phrase","one or more"=>"one or more of these words","search in"=>"Search in","whole site"=>"whole site","only in"=>"only in","current page"=>"current page","by last modified date"=>"Search by last modified date","anytime"=>"anytime","past 24 hours"=>"past 24 hours","past week"=>"past week","past month"=>"past month","past year"=>"past year","pages updated"=>"pages updated","advanced search"=>"advanced search","search again"=>"search again","load more"=>"load more","created date"=>"Created","search by date"=>"Search by date"));
	protected $detail_stat_id=-1;
	protected $loadmore=true;

	protected $f;
	protected $user;

	public function __construct($relpath,$from_outside)
	{
		global $f,$user;

		if($f instanceof FuncConfig)
			$this->f = $f;
		else
			die('Core not loaded! (12582)');
		$this->user = $user;
		$this->rel_path=$relpath;
		$this->call_from_outside=$from_outside;

		$this->s_gt_page=Detector::defineSourcePage('../','',isset($_REQUEST['mobile_search']),true);
		$this->ajax=isset($_REQUEST['result']);
		$this->livesearch=isset($_REQUEST['x']) && (int) $_REQUEST['x']===1; //used in x-tra search

		$this->self_url=Linker::buildSelfURL('search.php');
		$this->s_full_path_to_script=substr($this->self_url,0,strrpos($this->self_url, "/"));
		$s_page_id=(isset($_REQUEST['id'])? intval($_REQUEST['id']):0);
		if($s_page_id>0)
		{
			$pageid_info=CA::getPageParams($s_page_id,'../',true);
			if(isset($pageid_info[16]))
				$this->lang=$this->f->inter_languages_a[array_search($pageid_info[16],$this->f->site_languages_a)];
		}

		$key=array_search($this->lang,$this->f->inter_languages_a);
		$this->s_charset=(isset($this->f->site_charsets_a[$key])?$this->f->site_charsets_a[$key]:$this->f->site_charsets_a[0]);
		$this->lang_l=$this->lang_l[$key];

		if(!empty($this->f->search_templates_a))
		{
			$s_sitemap=CA::getSitemap($this->rel_path,false,true);
			if(isset($this->f->search_templates_a[0]) && $this->f->search_templates_a[0]!='0')
			{
				$this->s_gt_page=$s_sitemap[$this->f->search_templates_a[0]][1];
				$this->s_using_template_page=true;
			}
			foreach($this->f->search_templates_a as $v)
			{
				if($v!='0' && $s_sitemap[$v]['22']==$this->lang)
				{
					$this->s_gt_page=$s_sitemap[$v][1];
					$this->s_using_template_page=true;
				}
			}
		}

	}

	protected function lang_l($label)
	{
		$r=(isset($this->lang_l[$label])?$this->lang_l[$label]:$label);
		return $r;
	}

	public function s_GTs($template_content,$html_output,$query='',$id='',$charset='')
	{

		$search_part='';
		$indir=(strpos($this->s_gt_page,'../')===false);

		if(!empty($id))
		{
			if($charset!=='')
				$template_content=str_replace(Formatter::GFS($template_content,'charset=','"'),$charset,$template_content);
			if(strpos($template_content,'<!--search-->')!==false)
			{
				$search_part=F_BR.Formatter::GFS($template_content,'<!--search-->','<!--/search-->');
				$search_part=str_replace('name="q"','name="q" value="'.str_replace(array('\\"','"'),array('&#34;','&#34;'),Formatter::unEsc($query)).'"',$search_part);
			}
		}
		$ts=(strpos($this->f->template_source,'../')!==false)?$this->f->template_source:'../'.$this->f->template_source;

		if(strpos($template_content,'%SEARCH_OBJECT%')!==false)
			$pattern='%SEARCH_OBJECT%';
		elseif(file_exists($ts) && strpos($template_content,'%CONTENT%')!==false)
			$pattern='%CONTENT%';
		else
			$pattern=PageHandler::getArea($template_content,false,isset($_REQUEST['mobile_search']));

		if($search_part!='')
			$html_output=$search_part.$html_output;
		$template_content=str_replace($pattern,$html_output,$template_content);
		if($indir)
			$template_content=str_replace('</title>','</title>'.F_LF.'<base href="'.str_replace('documents','',$this->s_full_path_to_script).'">',$template_content);

		return $template_content;
	}
	protected function  replace_accents($src)
	{
		global $AccChars,$NormChars;

		$res=str_replace($AccChars,$NormChars,$src);
		return $res;
	}
	protected function preg_pos($sPattern,$sSubject,&$occurrences,&$score)
	{

		$sSubject=str_replace(array('&#8221;','&#8220;','&#8216;','&#8217;'),array('”','“','‘','’'),$sSubject);
		$sPattern=Formatter::strToLower($this->replace_accents($sPattern));
		$sSubject=Formatter::strToLower($this->replace_accents($sSubject));
		$wildcardPos=false;
		if(strpos($sPattern,'*')!==false)
		{
			$wildcardPos=strpos($sPattern,'*');
			$wc='*';
		}
		elseif(strpos($sPattern,'?')!==false)
		{
			$wildcardPos=strpos($sPattern,'?');
			$wc='?';
		}

		if($wildcardPos!==false && $wildcardPos==strlen($sPattern)-1)
			$sPattern_='/('.str_replace($wc,'',$sPattern).')/i';
		elseif($wildcardPos!==false && $wildcardPos==0)
			$sPattern_='/('.str_replace($wc,'',$sPattern).')\W\Z/i';
		elseif($wildcardPos!==false)
			$sPattern_='/('.str_replace($wc,'.\w*?',$sPattern).')/i';
		else
			$sPattern_='/'.($this->f->uni?'':'\b').'('.$sPattern.')'.($this->f->uni?'':'\b').'/i';

		$occurrences=@preg_match_all($sPattern_,$sSubject,$aMatches,PREG_OFFSET_CAPTURE);
		if($occurrences>0)
		{
			$keywords=explode('|',$sPattern);
			foreach($aMatches[0] as $k=>$v)
				$temp_arr[]=$v[0];
			$string_with_matches=implode(' ',$temp_arr);
			foreach($keywords as $k=>$word)
			{
				if(!empty($word) && strpos($string_with_matches,$word)!==false)
					$score++;
			}
			return $aMatches;
		}
		else
			return false;
	}

	protected function strpos($haystack,$needle,$offset=0)
	{
		if($this->f->use_mb)
			return mb_strpos($haystack,$needle,$offset);
		else
			return strpos($haystack,$needle,$offset);
	}
	protected function strrpos($haystack,$needle,$offset=0)
	{
		if($this->f->use_mb)
			return mb_strrpos($haystack,$needle,$offset);
		else
			return strrpos($haystack,$needle,$offset);
	}
	protected function substr($str,$start,$len=null)
	{
		if($this->f->use_mb)
			return mb_substr($str,intval($start),intval($len));
		else
			return substr($str,intval($start),intval($len));
	}
	protected function cut_result($haystack,$key_words_s,$needle_pos='',$haystack_limit=0)
	{
		if($haystack_limit==0)
			$haystack_limit=$this->livesearch?50:400;

		$haystack=str_replace(array('&#8221;', '&#8220;', '&#8216;', '&#8217;'), array('”', '“', '‘', '’'), $haystack);
		if($needle_pos=='')
		{
			$key_words_s_a=explode("|",$key_words_s);
			if($key_words_s_a[0]!='')
				$needle_pos=strpos($haystack, $key_words_s_a[0]);
		}
		if(strlen($haystack)>$haystack_limit)
		{
			$x=0;
			$y=$haystack_limit;
			$before_needle=$this->substr($haystack,0,$needle_pos);
			foreach(array('.','!','?') as $char)
			{
				$match_found =$this->strrpos($before_needle,$char.' ',$x);
				if($match_found!==false)
					$x = $match_found;
			}
			$after_needle=$this->substr($haystack,$needle_pos,$y);
			$y=$this->strrpos($after_needle,' ')===false?$needle_pos:$this->strrpos($after_needle,' ');
			$res_block=$this->substr($haystack,$x>0?$x+1:$x,$y);

		}
		else
			$res_block=$haystack;

		$wildcardPos=false;
		if(strpos($key_words_s,'*')!==false)
		{
			$wildcardPos=strpos($key_words_s,'*');
			$wc='*';
			$key_words_s=str_replace($wc,'.\w*?',$key_words_s);
		}
		elseif(strpos($key_words_s,'?')!==false)
		{
			$wildcardPos=strpos($key_words_s,'?');
			$wc='?';
			$key_words_s=str_replace($wc,'.\w*?',$key_words_s);
		}

		$key_words_s_a=explode("|",$key_words_s);
		$orig_res_block=$res_block;
		$res_block=Formatter::strToLower(($res_block));

		foreach($key_words_s_a as $v)
		{
			$v=Formatter::strToLower(($v));
			$v=str_replace(array('(',')'),array('\(','\)'),$v);
			$res_block=preg_replace("/(\W|\A|)(".$v.")(\W|\Z|)/iu", "$1[;:]$2[:;]$3",$res_block);
		}

		if($res_block!=$orig_res_block)
		{
			for($i=0;$i<strlen($res_block);$i++)
			{
				if($res_block[$i]=='[')
				{
					if(substr($res_block,$i,4)=='[;:]')
						$orig_res_block=substr($orig_res_block,0,$i).'[;:]'.substr($orig_res_block,$i,100000);
					elseif (substr($res_block,$i,4)=='[:;]')
						$orig_res_block=substr($orig_res_block,0,$i).'[:;]'.substr($orig_res_block,$i,100000);
				}
			}
		}

		$res_block=str_replace("[;:]",'<span class="search_highlight"><b>',$orig_res_block);
		$res_block=str_replace("[:;]",'</b></span>',$res_block);
		$res_block=$res_block.(strlen($haystack)>100?" <b>...</b> ":" ");
		return $res_block;
	}

	protected function update_page_prioritytable($pages_list)
	{
		global $db;
		$db->query('DELETE FROM '.$db->pre.'site_search_page_priority_index');
		$db->query_insert('site_search_page_priority_index',array('page_id'=>0,'page_priority'=>11));
		foreach($pages_list as $v)
			if(isset($v[26]))
			{
				$id=$v[10];
				$db->query_insert('site_search_page_priority_index',array('page_id'=>$id,'page_priority'=>$v[26]));
				if($v[24]!='0') //here is mobile page. Put same priority as the normal one
				{
					$mb_p_id = $id + 100000;
					$db->query_insert('site_search_page_priority_index',array('page_id'=>$mb_p_id,'page_priority'=>$v[26]));
				}
			}

	}

	protected function reindexMobile($v,$p_id,$p_lang,$dir)
	{
		global $db;
		if($v['24']!='0')
		{
			$mob_page_src = $this->getProperMobilePagePath($v,$dir);
			if($mob_page_src === false)
				return;
			$keywords=$title='';
			$content = $v[24]!='0'? PageHandler::getContent($mob_page_src,$this->s_index_template_areas,$keywords,$title):'';
			$p_id = $p_id + 100000; //offset to check mobile pages. If p_id > 100000, it's mobile page
			$date=file_exists($mob_page_src)?Date::buildMysqlTime(filemtime($mob_page_src)):'';
			Search::reindexDBAdd($db,$p_id,'',$p_lang,'',$title,$content,$date,$keywords,'',-1);
		}
	}

	protected function getProperMobilePagePath($pg_info,$dir,$file_ext='')
	{
		$path = Detector::checkSourcePage($pg_info,$pg_info[10],true,true);
		if(strpos($path, $dir)===false)
			$path = $dir.$path;
		if(!file_exists($path))
		{
			switch($file_ext)
			{
				case 'php':
					$path = str_replace('.php', '.html', $path);
					if(!file_exists($path))
						return false;
					break;
				case 'html':
					$path = str_replace('.html', '.php', $path);
					if(!file_exists($path))
						return false;
					break;
				default:
					if(strpos($path, '.php')!==false)
						$this->getProperMobilePagePath ($pg_info, $dir,'php');
					elseif(strpos($path, '.html')!==false)
						$this->getProperMobilePagePath ($pg_info, $dir,'html');
					else
						return false; //extension not found (at least it's neither php, not html), so just drop this file
					break;
			}

		}
		return $path;
	}

	protected function reindex($auto=false)
	{
		global $db;

		$data=array('action'=>'site search re-index','IP'=>Detector::getIP());
		History::add(0,'site_search_index','','',$data);
		$pages_list=CA::getSitemap($this->rel_path);
		$this->update_page_prioritytable($pages_list);

		$db_struct=$db->db_fields('site_search_index');
		$field_names=array();
		foreach($db_struct as $k=>$v)
		{
			$field_names[]=$v['Field'];
			if($v['Field']=='page_title' && $v['Type']=='varchar(255)')
				$db->query('ALTER TABLE '.$db->pre.'site_search_index MODIFY page_title text');
		}

		if(!in_array('user_id',$field_names))
		{
			$db->query('ALTER TABLE '.$db->pre."site_search_index ADD user_id varchar (100) NOT NULL default ''");
			$r=array();
			foreach($pages_list as $k=>$v)
				if($v[4]=='20')
					$r[]=$k;
			if(count($r)>0) $db->query('
				UPDATE '.$db->pre.'site_search_index
				SET user_id = entry_id
				WHERE page_id IN ('.implode(',',$r).')');
		}

		if($this->s_clean_range!='all')
		{
			list($min_p,$max_p)=explode('-',$this->s_clean_range);
			settype($min_p,'integer'); settype($max_p,'integer');
		}

		$output='';
		$pages_range_arr=array();

		foreach($pages_list as $k=>$v)
		{
			if(strpos($v[1],'http:')===false && strpos($v[1],'https:')===false)
			{
				$p_id=$v[10];
				settype($p_id,'integer');
				$pages_range_arr[]=$p_id;
			}
		}

		if(!empty($pages_range_arr))
		{
			$pages_range=implode(',',$pages_range_arr);
			$db->query('
				 DELETE FROM '.$db->pre.'site_search_index
				 WHERE page_id NOT IN ('.$pages_range.') '.($this->s_clean_range=='all'?'':'
					AND (page_id >= '.$min_p.')
					AND (page_id <= '.$max_p.')'));
		}

		$keywords=$title='';

		foreach($pages_list as $k=>$v)
		{
			$p_lang=array_search($v[16],$this->f->site_languages_a)+1;
			$p_id=intval($v[10]);
			$p_editable=$v[23]=='TRUE';

			if(strpos($v[1],'http:')===false && strpos($v[1],'https:')===false )
			{
				$dir=(strpos($v[1],'../')===false)?'../':Formatter::GFSAbi($v[1],'../','/');

				if($v[4]=='148' || $v[4]=='150')
					$content='';
				elseif(!in_array($v[4],$this->s_php_pages_ids) && !$p_editable)	// for NORMAL, PHP REQUEST and SUBSCRIBE pages
				{
					$fname=strpos($v[1],'../')===false? '../'.$v[1]: $v[1];
					if($v[4]=='133')
					{
						if(empty($v[9]))
							$fname=$dir.$p_id. (Validator::checkProtection($v)>1?'.php':'.html');
						elseif(strpos($v[9],'.')===false)
							$fname=$dir.$v[9]. (Validator::checkProtection($v)>1?'.php':'.html');
						else
							$fname=$dir.$v[9];
					}
					$content=PageHandler::getContent($fname,$this->s_index_template_areas,$keywords,$title);
					Search::reindexDBAdd($db,$p_id,'',$p_lang,'',
							  $title,$content,Date::buildMysqlTime(filemtime($fname)),$keywords,'',-1);

					$this->reindexMobile($v,$p_id,$p_lang,$dir);
				}
				else // for special PHP pages
				{
					if($v[4]=='20' || $p_editable) // OEP   $v[23]
					{
						$exist_rec=$db->query_first('
							SELECT *
							FROM '.$db->pre.'site_search_index
							WHERE page_id='.$p_id);
						if(empty($exist_rec))
						{							
							$main_fname=$p_editable?$dir.$v[9]:$dir.$p_id.(Validator::checkProtection($v)>1? '.php': '.html');
							$content=PageHandler::getContent($main_fname,$this->s_index_template_areas,$keywords,$title);
							$date=Date::buildMysqlTime(filemtime($main_fname));
							Search::reindexDBAdd($db,$p_id,'',$p_lang,'',
									  $title,$content,$date,$keywords,'',-1);
							$this->reindexMobile($v,$p_id,$p_lang,$dir);
						}
					}
					elseif(in_array($v[4], array('181','190'))) // shop/catalog
					{
						$main_fname=$dir.$p_id.'.html';

						$content=PageHandler::getContent($main_fname,$this->s_index_template_areas,$keywords,$title);
						$content=Formatter::clearMacros($content,$v[4]);

						$limit=($v[4]=='181')?4:2;
						for($i=1; $i<=$limit; $i++)
						{
							$tmp_fname = $dir.($p_id+7-$i).'.html';
							$temp=PageHandler::getContent($tmp_fname,$this->s_index_template_areas,$keywords,$title);
							$content.=' '.Formatter::clearMacros($temp,$v[4]);
						}
						$temp='';
						$date=Date::buildMysqlTime(filemtime($main_fname));
						Search::reindexDBAdd($db,$p_id,'',$p_lang,'',
								  $title,$content,$date,$keywords,'',-1);
						$this->reindexMobile($v,$p_id,$p_lang,$dir);
					}
					elseif(in_array($v[4],array('136','137','138','143','144')))  // blog, pblog, cal, podcast, guestbook
					{
						$main_fname=$dir.$p_id. (Validator::checkProtection($v)>1? '.php': '.html');
						$content=PageHandler::getContent($main_fname,$this->s_index_template_areas,$keywords,$title);
						$content=Formatter::clearMacros(Formatter::clearHtml($content), $v[4]);
						$date=Date::buildMysqlTime(filemtime($main_fname));
						Search::reindexDBAdd($db,$p_id,'',$p_lang,'',
								  $title,$content,$date,$keywords,'',-1);
						$this->reindexMobile($v,$p_id,$p_lang,$dir);
					}
				}
				$content='';
			}
		}

		$output='<div style="position:relative"><span class="rvts8" style="font-variant:small-caps"><b>Site Search successfully reindexed!</b></span>
		<a style="position:absolute;right:130px;" href="../documents/centraladmin.php?process=index" target="_top">Go to Online Administration</a>
		<a style="position:absolute;right:10px;" href="../'.($this->f->intro_page!=''?$this->f->intro_page:$this->f->home_page).'" target="_top">Remove Frame</a></div>';
		if($auto==false) print $output;
	}

	protected function build_advsearch_form($page_info,$cats_list,$id,$result='',$q='')
	{
		global $db;

		$pages_list_with_cat=CA::getSitemap($this->rel_path,true);
		$menu_cat_list=array();

		$user_type=0;$all_read=false;
		Session::intStart();
		if(Cookie::isAdmin())
			$user_type=2;
		if($this->user->userCookie())
		{
			$user_account=User::mGetUser($this->user->getUserCookie(),$db,'username');
			$user_type=1;
			$all_read=User::mHasAllReadAccess($user_account);
		}

		$last_cat='';$cat_pages_count=0;
		foreach($pages_list_with_cat as $v)
		{
			if($v[0]!='')
			{
				if(!isset($v[10]))
				{
					if($cat_pages_count>0)
						$menu_cat_list[$last_cat]=$last_cat;
					$cat_pages_count=0;
					$last_cat=$v[0];
				}
				else
				{
					if($user_type==2)
						$cat_pages_count++;
					elseif($user_type==1)
					{
						if($v[20]=='TRUE' && !$all_read)
						{
							$page_id=intval($v[10]);
							$page_info=CA::getPageParams($page_id,$this->rel_path);
							if(User::mHasReadAccess($user_account,$page_info))
								$cat_pages_count++;
						}
						else
							$cat_pages_count++;
					}
					elseif(isset($v[20]) && $v[20]=='FALSE')
						$cat_pages_count++;
				}
			}
		}
		if($cat_pages_count>0)
			$menu_cat_list[$last_cat]=$last_cat;
		$ptype=isset($page_info[4])?$page_info[4]:0;
		$root=isset($_REQUEST['root'])?$_REQUEST['root']:(strpos($this->s_gt_page,'../')===false);
		$prefix=$root?'':'../';
		$period_list=array($this->lang_l('anytime'),$this->lang_l('past 24 hours'),$this->lang_l('past week'),$this->lang_l('past month'),$this->lang_l('past year'));
		$search_btn='<input class="input1 search_again adv_search_button" style="display:'.($result==''?'none':'block').'" type="button" value="'.$this->lang_l($result==''?'search again':'advanced search').'">';
		$offset=$this->s_internal_use?53:0;

		$src='
		  <div class="search_container">
				<form class="advsearch" action="'.$prefix.'documents/search.php" method="post">
					 <div class="search_query" style="display:'.($result==''?'block':'none').'">
						  <div id="search_title" class="search_title search_header">
								<span><b>'.$this->lang_l('advanced search').'</b></span>
						  </div>
						  <input type="hidden" name="action" value="search">
						  <input type="hidden" name="result" value="1">
						  <div class="search_section news_bg">
								<div class="search_title">
									 <span><b>'.$this->lang_l('find pages that').':</b></span>
								</div>
								<div class="search_p">
									 <div class="search_ld">
										  <span class="rvts8 search_span">'.$this->lang_l('all these words').':</span>
									 </div>
									 <div class="search_rd">
										  <input class="input1 search_ed" type="text" id="qw" '.($q!=''?'value="'.$q.'"':'').' name="qw">
									 </div>
									 <div style="clear:left"></div>
								</div>
								<div class="search_p">
									 <div class="search_ld">
										  <span class="rvts8 search_span">'.$this->lang_l('this exact wording').':</span>
									 </div>
									 <div class="search_rd">
										  <input class="input1 search_ed" type="text" id="qx" name="qx">
									 </div>
									 <div style="clear:left"></div>
								</div>
								<div class="search_p">
									 <div class="search_ld">
										  <span class="rvts8 search_span">'.$this->lang_l('one or more').':</span>
									 </div>
									 <div class="search_rd">
										  <input class="input1 search_ed" type="text" id="qa" name="qa">
										  <input type="hidden" name="id" value="'.$id.'">
										  <input class="input1" type="hidden" name="q" value="">
									 </div>
									<div style="clear:left"></div>
								</div>
						  </div>
						  <div class="search_section news_bg">
								<div class="search_title">
									 <span><b>'.$this->lang_l('search in').':</b></span>
								</div>
								<div class="search_p">
									 <div class="search_ld">
										  <input type="radio" name="t" value="a" checked="checked">
										  <span class="rvts8 search_span">'.$this->lang_l('whole site').'</span>
									 </div>
									 <div style="clear:left"></div>
								</div>
								<div class="search_p">
									 <div class="search_ld">
										  <input type="radio" name="t" value="m">
										  <span class="rvts8 search_span">'.$this->lang_l('only in').'</span>
									 </div>
									 <div class="search_rd">'
										  .Builder::buildSelect('m',$menu_cat_list,'all','','key','',' class="input1 search_cmb"').'
									 </div>
									 <div style="clear:left"></div>
								</div>';
		if($id!='')
		{
			$src.='
								<div class="search_p">
									 <div class="search_ld">
										  <input type="radio" name="t" value="p">
										  <span class="rvts8 search_span">'.$this->lang_l('current page').' ('.(isset($page_info[0])? $page_info[0]:'').')</span>
									 </div>
									 <div class="search_rd">';
			if($id!='' && in_array($ptype,array('136','137','138','143','181','190')) )
				$src.=Builder::buildSelect('cid',$cats_list,'all','','key','',' class="input1 search_cmb"');
			$src.='
									 </div>
									 <div style="clear:left"></div>
								</div>';
		}
		$src.='		  </div>
						  <div class="search_section news_bg">
								<div class="search_title">
									 <span><b>'.$this->lang_l('search by date'). ':</b></span>
								</div>
								<div class="search_p">
									 <div class="search_ld">
										  <span class="rvts8 search_span">'.$this->lang_l('pages updated').':</span>
									 </div>
									 <div class="search_rd">'
										  .Builder::buildSelect('p',$period_list,'all','','key','',' class="input1 search_cmb"').'
									 </div>

									 <div style="clear:left"></div>
								</div>
								<div class="search_p">
									 <div class="search_ld">
										  <span class="rvts8 search_span">'.$this->lang_l('pages created').':</span>
									 </div>
									 <div class="search_rd">'
										  .Builder::buildSelect('p1',$period_list,'all','','key','',' class="input1 search_cmb1"').'
									 </div>
									 
									 <div style="clear:left"></div>
								</div>
						  </div>
						  <p><input class="input1 adv_search_button" type="submit" value="'.$this->lang_l('Search').'"></p>
					 </div>
				</form>
		  </div>
		  <div class="search_section search_results" style="display:'.($result==''?'none':'block').'">'.$result.'</div>';
		$src.=$search_btn;

		$js='
		  <script type="text/javascript">
		  $(document).ready(function() {
				$(".search_again").click(function(){
					 $(".search_results").slideUp("slow",function() {
						  $(".search_query").slideDown("slow");
						  $(".search_again").hide();
						  $("html, body").animate({ scrollTop: $("#search_title").offset().top-20-'.$offset.' }, 1000);
					 });
				});
		  frs=$(".advsearch");
		  frs.submit(function(event){
				event.preventDefault();
				$.post(frs.attr("action"),frs.serialize(),function(re){
					 $(".search_query").slideUp(400,function(){
						  $(".search_results").html(re).slideDown("slow");
						  $(".search_again").show();
					 });

				});
		  })
		  });
		  </script>';

		return $js.$src;
	}

	protected function get_page_categories($page_info)
	{
		global $db;

		$cats_list_raw=$db->fetch_all_array('SELECT * FROM '.$db->pre.$page_info[10].'_' .'categories');
		$cats_list=array('all'=>'All categories');
		if(!empty($cats_list_raw))
		{
			foreach($cats_list_raw as $v)
				if($page_info[4]!='136' || $v['cid']!=0)
					$cats_list[$v['cid']]=$v['cname'];
		}
		return $cats_list;
	}

	protected function data_sorting($records,$occ_id,$prior_id,$score_id,$swap_score_prior=false) // sorting search results
	{
		if(!empty($records))
		{
			$score=$occurrences=$priority=array();
			foreach($records as $key=>$row)
			{
				$occurrences[$key]=$row[$occ_id];
				$priority[$key]=$row[$prior_id];
				$score[$key]=$row[$score_id];
			}
			list($firstParam,$secondParam)=$swap_score_prior?array($score,$priority):array($priority,$score);
			array_multisort($firstParam,SORT_DESC,SORT_NUMERIC,$secondParam,SORT_DESC,SORT_NUMERIC,$occurrences,SORT_DESC,SORT_NUMERIC,$records);
		}
		return $records;
	}
	
	public function framedtable($caption,$body) //only used on il site
	{
		return '
		<div class="t1 block">
		  <div class="t1_caption">'.$caption.'
		  </div>
		  <div blBody>
				<div class="t1_body">'.$body.'</div>
		  </div>
		</div>';
	}

	public function process($search_in_page='',$cat_name='',$only_entries=false,$remove_junk=true,$date_used='creation_date')
	{
		global $db;

		$this->action_id=isset($_REQUEST['action'])?Formatter::stripTags($_REQUEST['action']):'search';
		if($this->action_id=='asearch' && !$this->s_advanced_search)
			$this->action_id='search';
		if($this->action_id=="index")
		{
			$indexUrl=($this->f->intro_page!=''?$this->f->intro_page:$this->f->home_page);
			if(stripos($indexUrl,'http')===false)
				$indexUrl='../'.$indexUrl;
			echo '<frameset rows="40,*" frameborder="0" framespacing="0">
							<frame src="search.php?action=reindex" frameborder="0" scrolling="no">
							<frame src="'.$indexUrl.'" frameborder="0">
						</frameset>';
			exit;
		}
		$mobile_search = isset($_REQUEST['mobile_search']) && $_REQUEST['mobile_search']==1;

		$db=DB::dbInit($this->f->db_charset,($this->f->uni?$this->f->db_charset:''));
		if($db!==false)
			Search::checkDB($db);
		else
			exit;

		if($this->action_id=="reindex")
		{
			$this->reindex();
			exit;
		}
		if($this->action_id=="version")
			echo $this->version;
		$cats_list=array();$page_info=array();
		$this->query_st_time=Date::microtimeFloat();
		$id='';

		if($this->action_id=="asearch" || $this->action_id=="search" || $this->call_from_outside)
		{
			$body_section=$query=$page_info='';
			$languageid=1;
			$language=$this->f->site_languages_a[0];
			$show_occurrences=false;
			$all_pages_list=CA::getSitemap($this->rel_path,false,true);

			$s_gt_path=((strpos($this->s_gt_page,'../')===false) && $this->rel_path!=''?'../':'').$this->s_gt_page; //search output params
			if($this->rel_path=='')
				$s_gt_path=str_replace('../','',$s_gt_path);

   		if($mobile_search) //mobile template
			{
			 	$temp=str_replace($this->s_gt_page,'i_'.$this->s_gt_page,$s_gt_path);
				if(file_exists($temp))
					$s_gt_path=$temp;
			}

			$s_output_params='';$date_params='';
			if($this->livesearch)
				$s_template_content='
				%SEARCH_OBJECT(
				<p><b><a class="rvts12 livesearch_a" href="%url%">%title%</a></b></p>
				<span class="rvts8 livesearch_content">%content%</span>
				)%';
			else
				$s_template_content=File::read($s_gt_path);
			if(strpos($s_template_content,'%SEARCH_OBJECT(')!==false)
			{
				$s_template_content=Formatter::objClearing("SEARCH_OBJECT",$s_template_content);
				$output_params_t=Formatter::GFS($s_template_content,'%SEARCH_OBJECT(',')%');
				$s_output_params=Formatter::pTagClearing($output_params_t);

				$s_template_content=str_replace("%SEARCH_OBJECT(".$output_params_t,"%SEARCH_OBJECT(".$s_output_params,$s_template_content);
				$s_template_content=Formatter::objDivReplacing('%SEARCH_OBJECT('.$s_output_params.')%',$s_template_content);
				$s_template_content=str_replace('%SEARCH_OBJECT('.$s_output_params.')%','%SEARCH_OBJECT%',$s_template_content);

				$date_params=(strpos($s_output_params,'%date[')!==false)?Formatter::GFS($s_output_params,'%date[',']%'):Formatter::GFS($s_output_params,'%DATE[',']%');
				$s_output_params=str_replace(array('%date['.$date_params.']%','%occurances%'),array('%date%','%occurrences%'),$s_output_params);
				$show_occurrences=!strpos($s_output_params,'%occurances%')===false;
			}

			$max_results=$this->s_internal_use?20:10;
			if(isset($_REQUEST['mr']) && !empty($_REQUEST['mr']))
				$max_results=intval($_REQUEST['mr']);
			$page=(isset($_REQUEST['page']))?intval(Formatter::stripTags($_REQUEST['page'])):1;
			$search_in_cur_lang=(isset($_REQUEST['sa']))?Formatter::stripTags($_REQUEST['sa']):'true';

			if(isset($_REQUEST['id']) || $search_in_page!='')
			{
				$id=intval(isset($_REQUEST['id'])?$_REQUEST['id']:$search_in_page);
				$page_info=CA::getPageParams($id,$this->rel_path,true);
				if($page_info!='')
				{
					$languageid=array_search($page_info[16],$this->f->site_languages_a)+1;
					$language=$page_info[16];
				}
			}

			if($id!='' && in_array($page_info[4], array('136','137','138','143','181','190')))
				$cats_list=$this->get_page_categories($page_info);

			$date_filter='';
			$si=isset($_REQUEST['t'])?$_REQUEST['t']:'';
			if($si!='')  // advanced search
			{
				$in_page_cat='';
				if($si=='p') //page search
				{
					$search_in_page=intval($_REQUEST['id']);
					if(isset($_REQUEST['cid']) && $_REQUEST['cid']!='') //in category by id
					{
						$in_page_cat=intval($_REQUEST['cid']);
						if($in_page_cat>0)
						{
							$in_page_cat--;
							if(isset($cats_list[$in_page_cat]))
								$cat_name=$cats_list[$in_page_cat];
						}
					}
					elseif(isset($_REQUEST['c'])) //in category by name
					{
						$in_page_cat=str_replace('+',' ',Formatter::stripTags($_REQUEST['c']));
						if(array_search($in_page_cat,$cats_list)!==false)
							$cat_name=$in_page_cat;
					}
				}
				elseif($si=='m' && isset($_REQUEST['m'])) //menu search
				{
					$search_in_cat=str_replace('+',' ',Formatter::stripTags($_REQUEST['m']));
					if($search_in_cat!='all' && $search_in_cat!='')
					{
						$pages_range_arr=array();

						foreach($all_pages_list as $k=>$v)
							if($v[2]==('#'.$search_in_cat))
								$pages_range_arr[]=$k;

						if(count($pages_range_arr)>0)
							$pages_range=implode(',',$pages_range_arr);
					}
				}

				$for_modifided_period=isset($_REQUEST['p'])?intval($_REQUEST['p']):0;
				$for_created_period=isset($_REQUEST['p1'])?intval($_REQUEST['p1']):0;
				$today=time();

				if($for_modifided_period!=0||$for_created_period!=0)
				{
					$period=$for_created_period!=0?$for_created_period:$for_modifided_period;
					$date_used=$for_modifided_period!=0?'modified_date':'creation_date';
					if($period==1)
						$offset=60*60*24;
					elseif($period==2)
						$offset=60*60*24*7;
					elseif($period==3)
						$offset=60*60*24*30;
					elseif($period==4)
						$offset=60*60*24*365;
					$date_offset="'".Date::buildMysqlTime($today-$offset)."'";
					$date_filter.=$date_used=='modified_date'?'modified_date >= '.$date_offset.' ':'if(creation_date > 0 ,creation_date >= '.$date_offset.',modified_date >= '.$date_offset.') ';
				}
			}
		}

		if($this->action_id=="asearch") // advanced search
		{
			$asearch_form=$this->build_advsearch_form($page_info,$cats_list,$id);
			$output=$this->s_GTs($s_template_content,$asearch_form,'',$id);
			$nav=Formatter::GFSAbi($output,'%NAVIGATION','%');
			$output=str_replace(array($nav,'%navigation%','%HEADER%','%GENERATEDTIME%','%ADVANCED_SEARCH_LINK%','%ADVANCED_SEARCH%'),'',$output);
			print $output;
			exit;
		}
		elseif($this->action_id=="xsearch") // x search
		{
			$xSearch = new xSearch($this);
			$xSearch->buildXSearchForm();
			exit;
		}
		elseif($this->action_id=="asearchjs") // external advanced search
		{
			$asearch_form=$this->build_advsearch_form($page_info,$cats_list,isset($id)?$id:'');
			print "document.write('".$asearch_form."');";
			exit;
		}
		elseif($this->action_id=="search" || $this->call_from_outside)
		{
			$first_rec=$db->query_first('SELECT * FROM '.$db->pre.'site_search_index LIMIT 0,1'); // check if table is empty
			if(empty($first_rec))
				$this->reindex(true);

			$use_params=($s_output_params!='');  //search output params
			if($use_params && $this->call_from_outside)
				$s_template_styles=Formatter::GFSAbi($s_template_content,'<style type="text/css">','</style>');

			if(isset($_REQUEST['string']) && !isset($_REQUEST['q']))
				$_REQUEST['q']=$_REQUEST['string'];
			if(isset($_REQUEST['query']) && !isset($_REQUEST['q']))
				$_REQUEST['q']=$_REQUEST['query'];

			$adv=isset($_REQUEST['qw']) || isset($_REQUEST['qa']) || isset($_REQUEST['qx']);

			if(isset($_REQUEST['q']) || $adv)
			{
				$all_words_search=isset($_REQUEST['qw']) && !empty($_REQUEST['qw']);
				$phrase_search=isset($_REQUEST['qx']) && !empty($_REQUEST['qx']);

				if($all_words_search)
					$query=$_REQUEST['qw'];  // advanced search
				elseif($phrase_search)
					$query=$_REQUEST['qx'];
				elseif(isset($_REQUEST['qa']) && !empty($_REQUEST['qa']))
					$query=$_REQUEST['qa'];
				else
					$query=$_REQUEST['q'];

				if($this->ajax && !$this->f->uni && function_exists('iconv'))
					$query=iconv("UTF-8",$this->s_charset."//IGNORE",$query);

				//$query=addslashes(htmlspecialchars(strip_tags(trim($query))));
				$query=Formatter::unEsc(Formatter::stripTags(trim($query)));
				$query=trim(str_replace(array('|',"'",'`','/',"\\"),'',$query));
				if($query=='"')
					$query='';
				if($phrase_search)
					$query='"'.str_replace('"','',$query).'"';

				if($query!='')
				{
					$q_pos=strpos($query,'"'); // opening " (if used)
					$qcl_pos=strrpos($query,'"'); // closing " (if used)
					if(($q_pos===0) && $qcl_pos==(strlen($query)-1))
					{
						$query=str_replace('"','',$query);
						$cleared_string=$query;
						$key_words=array($cleared_string);
					}
					else
					{
						$query=str_replace('"','',$query);
						$key_words=(strpos($query,' ')!==false? explode(' ',$query): array($query));
						$cleared_string=$query;
					}

					$key_words_t=array();
					$key_words_s=array();
					if(count($key_words)==1 && strlen($key_words[0]<5))
						 $this->s_partial=true;
					foreach($key_words as $k=>$v)
					{
						$v=trim($v);
						if($v!='')
						{
							$key_words_s[]=trim($v);
							if($this->s_partial && !$phrase_search)
								$v.='*';
							$key_words_t[]=trim($v);
						}
					}
					$key_words=$key_words_t;
					$key_words_str=implode('|', $key_words_s);

					if(!$this->f->use_linefeed && (in_array('Windows-1251',$this->f->site_charsets_a) || $this->f->uni))
						$linux_cyrillic_fl=true;
					else
						$linux_cyrillic_fl=false;

					$multi_key=substr_count($key_words_str,'|')>0;

					if(strpos($query,'"'))
						$pattern=$query;
					else
						$pattern="'".$query."'";

					$pattern=str_replace('?','*',$pattern);

					if(is_array($cat_name))
					{
						$cat_where=' AND ( ';
						foreach($cat_name as $k=>$v)
							$cat_where.=' page_content LIKE "%'.$v.'%" OR ';
						$cat_where=substr($cat_where,0,strlen($cat_where)-3).' )';
					}
					else
						$cat_where=(($cat_name!='')? ' AND page_content LIKE "%'.$cat_name.'%"': '');

					// advanced search
					$date_where=($date_filter!=''?' AND '.$date_filter:'');
					$page_where=($search_in_page!=''?' AND page_id = '.$search_in_page.($only_entries?' AND entry_id <> "" ':''):(isset($pages_range)?' AND page_id IN ('.$pages_range.')': ''));
					$cat_where=(isset($in_page_cat) && $in_page_cat!=''?' AND (cat_id = "'.$in_page_cat.'"'.str_replace('AND','OR',$cat_where).')': $cat_where);
					$lang_where=(($search_in_cur_lang=='false')?'':' AND page_lang = '.$languageid);
					$pattern_m=str_replace(array('”', '“', '‘', '’'), array('&#8221;', '&#8220;', '&#8216;', '&#8217;'), $pattern);

					Session::intStart('private',true); //true means regenerating id
					$logged_admin=Cookie::isAdmin();
					if($this->user->userCookie())
					{
						$logged_user=$this->user->getUserCookie();
						$user_account=User::getUser($logged_user,$this->rel_path);
						$user_where=' AND (user_id = "'.$logged_user.'" OR user_id = "" )';
					}
					else
						$user_where=($logged_admin?'':' AND user_id = ""');

					$searchable_range_arr=array();
//$v[20] = hide in search					$v[23] = normal editable page				$v[4]=='20'  --> editable page    ($v[4]=='20') ||
					foreach($all_pages_list as $k=>$v)
					{
						if(($v[20]=='FALSE') || ($logged_admin || (isset($logged_user) && User::mHasReadAccess($user_account,$v))))
						{
							$searchable_range_arr[]=intval($k);
							if($v[24]!='0')
								$searchable_range_arr[] = intval($k)+100000;//also add mobile pages in this range
						}
					}
					if(($page_where=='') && count($searchable_range_arr)>0)
					{
						$searchable_range_arr[]='0';
						$searchable_range=implode(',',$searchable_range_arr);
						$page_where=' AND prior.page_id IN ('.$searchable_range.')';
					}
					$pattern_m=Formatter::stripQuotes($pattern_m);

					$xtraQueryShortWords='';
					if($this->s_partial && !$phrase_search)
					{
						$pattern_m=$pattern_m.'*';
						if(!$this->s_internal_use)
							foreach($key_words_t as $k=>$v)
							{
								$v=Formatter::stripQuotes($v);
								if($v!='*')
									$xtraQueryShortWords.=' OR page_content LIKE "%'.substr($v,0,strlen($v)-1).'%" ';
							}
					}
					$xtraQueryShortWords.=' ) ';

					$que='
						SELECT s.page_id,page_title,page_keywords,page_url_params,entry_id,cat_id,page_content AS page_content,page_priority,
						'.($date_used=='modified_date'?'modified_date as date_mc, \'modified_date\' as date_type':'if(creation_date>0 ,creation_date,modified_date) as date_mc, if(creation_date>0,\'creation_date\',\'modified_date\') as date_type').'
						FROM '.$db->pre.'site_search_index AS s
						INNER JOIN '.$db->pre.'site_search_page_priority_index as prior USING (page_id) '.
						($phrase_search?'
							WHERE (page_content LIKE "%'.addslashes($pattern_m).'%" '.$xtraQueryShortWords
							:'
							WHERE (MATCH (page_content) AGAINST ("'.addslashes($pattern_m).'" IN BOOLEAN MODE)'.$xtraQueryShortWords).
						($mobile_search?'
							AND (s.page_id>100000 OR (s.page_id IN (SELECT page_id-100000 AS page_id
							FROM '.$db->pre.'site_search_index
							WHERE page_id > 100000)) AND (entry_id <> "" OR entry_id IS NOT NULL))'
							:
							($page_where==''?'AND s.page_id<100000':'')
						).
						$page_where.
						$date_where.
						$cat_where.
						$lang_where.
						$user_where.'
						AND (expired_date = 0 OR expired_date > NOW())
						GROUP BY s.page_id,entry_id
						ORDER BY page_priority';

					$match_result=$db->fetch_all_array($que,1);

					if($match_result===false)
					{
						$this->update_page_prioritytable($all_pages_list);
						$match_result=$db->fetch_all_array($que);
					}

					$count_res=count($match_result);
					$results=array();

					if($this->f->counter_on && strlen(trim($query))>2) //search.php hits in counter
					{
						$stat_detailed=Builder::detailedStat(time(),1,false,false,$query,$count_res);
						$this->detail_stat_id=$db->query_insert('counter_details',$stat_detailed);
						Counter::handleSearchSessionHit($db,$stat_detailed,$this->detail_stat_id);
						$page_total=$db->query_singlevalue('SELECT total FROM '.$db->pre.'counter_pageloads WHERE page_id = 1');
						if(is_null($page_total))
							$db->query_insert('counter_pageloads',array("page_id"=>1,"total"=>1,"eventcount"=>0));
						else
							$db->query('
								UPDATE '.$db->pre.'counter_pageloads
								SET total=total + 1
								WHERE page_id = 1');
					}

					foreach($match_result as $k=>$val)
					{
						$p_content=Formatter::unEsc($val['page_content']);
						$p_keywords=Formatter::unEsc($val['page_keywords']);
						$p_title=Formatter::unEsc($val['page_title']);
						if(strpos($p_content,'%hidden_text(')!==false) //
						{
							Formatter::hideFromGuests($p_content);
							$patterns = explode(' ',$pattern_m);
							$has_visible_res = false;
							$missing_required_word = false;
							foreach($patterns as $pat)
							{
								$ptrn = $pat[0] == '+' ? substr($pat,1) : $pat;
								if(strpos(strtolower($p_content),strtolower($ptrn))!==false)
									$has_visible_res = true;
								elseif($pat[0] == '+')
									$missing_required_word = true;
							}
							if(!$has_visible_res || $missing_required_word)
							{
								$count_res = $count_res > 0 ? $count_res - 1 : 0;
								continue; //loop to the next match result
							}
						}
						if($remove_junk)
							$p_content=str_replace(Formatter::GFSAbi($p_content,'<junk>','</junk>'),'',$p_content);

						$p_content=str_replace('#13#10',F_BR,$p_content);
						$pid=$val['page_id'];

						if(isset($all_pages_list[$pid]) || $pid=='0' || $pid>100000/*mobile pages*/)
						{
							if($pid=='0')
							{
								$pdata=null;
								$page_url=$val['page_url_params'];
								$p_t_id=0;
							}
							else
							{
								$pid_to_check = $pid>100000?$pid-100000:$pid;
								$pdata=$all_pages_list[$pid_to_check];
								if(strpos($val['page_url_params'],'http')!==false)
									$page_url=$val['page_url_params'];
								elseif(strpos($val['page_url_params'],'/')!==false)
								{
									if(strpos($pdata[1],'/')!==false)
										$page_url=substr($pdata[1],0,strrpos($pdata[1],'/')).$val['page_url_params'];
									elseif(strpos($val['page_url_params'],'/')===0)
										$page_url=substr($val['page_url_params'],1);
									else
										$page_url=$val['page_url_params'];
								}
								else
									$page_url=$pdata[1].$val['page_url_params'];
							  $p_t_id=$pdata[4];
							}
							$occurrences=$score=$key_occurrences=$key_score=0;

							$entry_details=array();
							$entry_title='';
							if($val['entry_id']!='' && $p_t_id!='181' && $p_t_id!='190' && strtolower($pdata[23])=='false')
							{
								$entry_details=$db->query_first('
									SELECT *
									FROM '.$db->pre.$pid.'_' .($p_t_id=='136'?'events':'posts').'
									WHERE '.($p_t_id=='136'?'event_id = "'.$val['entry_id'].'"':'entry_id = '.$val['entry_id']));
								if($p_t_id=='144')
									$entry_title=(isset($entry_details['name'])?$entry_details['name']:'').' '.(isset($entry_details['surname'])?$entry_details['surname']:'');
								else
									$entry_title=$entry_details[$p_t_id=='136'?'short_description':'title'];
							}
							if($search_in_page=='')
								$entry_title=$val['page_title'].($entry_title==$val['page_title']?'':($entry_title!=''?' &gt;&gt; ':'').$entry_title);
							if($entry_title=='')
								$entry_title=$val['page_title'];

							if(!$this->f->uni)
							{
								$matches=$this->preg_pos($key_words_str,$p_content,$occurrences,$score);
								if(is_array($matches))
								{
									if($multi_key && strpos($p_content,$cleared_string)!==false)
										$score+=100;
									$cut_content=$this->cut_result($p_content,$key_words_str,$matches[0][0][1]);
									$results["$page_url"]=array($val['page_title'],$page_url,$cut_content,$pid,strtotime($val['date_mc']),$occurrences,
									$entry_title,$p_t_id,$val['entry_id'],$val['page_priority'],$score);
								}
							}
							else
							{
								$matches=$this->preg_pos($key_words_str,$p_content,$occurrences,$score);
								if($multi_key && strpos($p_content,$cleared_string)!==false)
									$score+=100;
								if($this->s_internal_use)
								{
									$head_kwords_end = strpos($p_content,'<!--keywords-->');
									if($head_kwords_end !== false)
									{
										$kw_matches=$this->preg_pos($key_words_str,substr($p_content, 0, $head_kwords_end+1),$kw_occs,$scr);
										if($kw_occs > 0)
											$occurrences += $kw_occs*10;
									}
									else
									{
										$matches=$this->preg_pos($key_words_str,$p_keywords,$key_occurrences,$key_score);
										if($key_occurrences>0)
											 $score.=$key_score;
									}
								}
								else
								{
									$matches=$this->preg_pos($key_words_str,$p_keywords,$key_occurrences,$key_score);
									if($key_occurrences>0)
										 $score.=$key_score;
								}
								$matches=$this->preg_pos($key_words_str,$p_title,$key_occurrences,$key_score);
								if($key_occurrences>0)
									 $score.=$key_score;

								$cut_content=$this->cut_result($p_content,$key_words_str,'',$val['page_priority']==11?200:0);
								$results["$page_url"]=array(
									 $val['page_title'],
									 $page_url,
									 $cut_content,
									 $pid,
									 strtotime($val['date_mc']),
									 //strtotime($val['created_date']),
									 $occurrences,
									 $entry_title,
									 $p_t_id,
									 $val['entry_id'],
									 $val['page_priority'],
									 $score,
									 $val['page_keywords'],
									 $val['date_type']);
							}
						}
					}

					$count_res=count($results);

					$main_result=$main_result_head='';
					$res_header=$this->lang_l('Result').': '.Formatter::sth($query);
					if(strpos($s_template_content,'%HEADER%')!==false  && !$this->call_from_outside)
						$s_template_content=str_replace('%HEADER%',$res_header,$s_template_content);
					else
						$main_result_head='<span class="rvts24">'.$res_header.'</span>';

					if(empty($results))
						$main_result.='<div class="search_summary"><span class="rvts8">'.$this->lang_l('no matches found').'</span></div>';
					else
					{
						//keep field positions CORRECT here:
						// sort by occurrences (5-th), page prior(9-th) and score (10-th)
						$res_overscored=array();
						foreach($results as $resK => $resV)
							if($resV[10]>100)
							{
								$res_overscored[]=$resV;
								unset($results[$resK]);
							}
						$res_overscored=$this->data_sorting($res_overscored,5,9,10,true);
						$results=$this->data_sorting($results,5,9,10);
						$results=array_merge($res_overscored,$results);

						$match_result=array();
						$search_help=$this->s_internal_use && !$this->call_from_outside;
						$help_match_result=$search_help?array():''; //holds the html results
						$cnt=0;

						foreach($results as $k=>$v)
						{
							if($search_help && $v[9]==11)
							{
								$help_match_result[$k] = $v;
								unset($results[$k]);
								continue;
							}
							if($cnt>=($page-1)*$max_results)
								$match_result[]=$v;
							$cnt++;
							if($cnt>=($page)*$max_results)
								break;
						}
						if($search_help) $count_res -=count($help_match_result); //fix script pages resuts count

						if(!isset($id))
							$id=null;
						$match_result=$this->prepare_search_output($match_result,$max_results,$query,$id,$search_in_cur_lang,
								  $show_occurrences,$date_params,$s_output_params,$main_result,$use_params,$s_template_content,
								  $count_res,$page,$all_pages_list);

						if($search_help)
						{
							$help_match_result=array_slice($help_match_result,0,10); //keep only 10 help pages
							if(count($help_match_result)>0)
								$help_match_result=$this->prepare_search_output($help_match_result,$max_results,$query,$id,
									  $search_in_cur_lang,$show_occurrences,$date_params,$s_output_params,$main_result,
									  $use_params,$s_template_content,$count_res,$page,$all_pages_list,'empty_nav',false);
							else
								$help_match_result='no results';
							$help_match_result = $this->framedtable('Results from Online Help',$help_match_result);
						}
					}

					//main result returns "no match found" while match result returns actual matches
					if($this->livesearch)
					{
	 					 print '<div class="livesearch" style="padding: 5px;">'.($main_result!=''?$main_result:$match_result).'</div>';
						 exit;
					}

					$forum_search=false;
					$forum_result=$kb_result='';

					if(!$this->call_from_outside)
					{
						if($this->s_internal_use)
						{
							$query=str_replace(' ', '|', strtolower($query));
							$content=file_get_contents('http://support.image-line.com/knowledgebase/search2.php?s='.$query);
							$k_empty=false;
							if(strpos($content,'<!--int-->')===false)
							{
								$content='no results';
								$k_empty=true;
							}
							$content=str_replace('href="','href="http://support.image-line.com/knowledgebase/',$content);
							$content=str_replace(Formatter::GFS($content,'<div style="clear:both; text-align:center; color:black; font-size:80%">','</div>'),'',$content);
							$kb_result.=$this->framedtable('Results from Knowledgebase',$content);
							$postfix='';
							$ajax_data = array();
							$forum_search=(isset($_REQUEST['forum']) && ($_REQUEST['forum']=='1'));
							if($forum_search)
							{
								if((int)$_REQUEST['category_number'])
								{
									$postfix .= '&fid[]='.$_REQUEST['category_number'];
									$ajax_data['fid[]'] = $_REQUEST['category_number'];
								}
								if((int)$_REQUEST['user_id'])
								{
									$postfix .= '&user_id='.$_REQUEST['user_id'];
									$ajax_data['user_id'] = $_REQUEST['user_id'];
								}
							}

							$forum_url = 'http://forum.image-line.com/search.php?keywords='.$query.'&sr=topics&search_id=external'.$postfix;
							$content=
									  '
										<div class="forum_result"></div>
										<script>
										var forum_loaded=0;
										function load_forum()
										{
										  if(!forum_loaded)
										  {
												$(".forum_result").html("<h1>Please wait, loading ....</h1>");
												forum_loaded=1;
												$.get("'.$forum_url.'",function(data){
													if(data.indexOf("<head>")>-1) data="No results!";
													else data=data.replace(/".?\//g,"\"http://forum.image-line.com/");
										  			$(".forum_result").html(data);
												});
										  }
										}

									  function inview(){
											return (($(".forum_result").offset().top - $(window).scrollTop()) < window.innerHeight);
										}

	 									$(document).ready(function(){
										  $(window).scroll(function(){ if(inview()) load_forum(); });
										  if(inview()) load_forum();
										});
									  </script>';

							if($forum_search)
							{
								$ch = curl_init();
								$forum_url = 'http://forum.image-line.com/search.php?keywords='.$query.'&sr=topics&search_id=external'.$postfix;
								$ajax_data = array_merge($ajax_data, array(
																				'keywords' => $query,
																				'sr' => 'topics',
																				'search_id' => 'external'
																				));
								$forum_url = preg_replace('/\s/', '%20', $forum_url);
								curl_setopt($ch, CURLOPT_URL, $forum_url);
								$curl_cookies = "";
								foreach ($_COOKIE as $k=>$v)
									$curl_cookies .= $k . "=". rawurlencode(stripslashes($v)) . ";";
								$curl_cookies .= 'FORWARDED_FOR_ADDR='.$_SERVER['REMOTE_ADDR'];
								curl_setopt($ch, CURLOPT_COOKIE, $curl_cookies);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
								$ref=(isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:'';
								curl_setopt($ch, CURLOPT_REFERER,$ref);
								$content = curl_exec($ch);
								curl_close($ch);

								$f_empty=false;
								if(strpos($content,'No posts were found because the word'))
								{
									$content='No topics or posts met your search criteria <br />Click here for <a href="/search.php?kw=fl studio">(Advanced Forum Search)</a><br />';
									$f_empty=true;
								}
								elseif (strpos($content,'Each word must consist of at least'))
								{
	 								$content='For the forum each word must consist of at least 3 characters and must not contain more than 14 characters excluding wildcards <br />Click here for <a href="/search.php?kw=fl studio">(Advanced Forum Search)</a><br />';
									$f_empty=true;
								}
								elseif(!strpos($content,'breadChr'))
								{
									$a_data = '';
									foreach ($ajax_data as $k => $v )
										 $a_data .= ($a_data == '' ? '' :', ').$k.': "'.$v.'"';
									$content= file_get_contents($forum_url) ;
									if(!strpos($content,'breadChr'))
									{
										 $content='Error, please try again <br />'.$content;
										 $f_empty=true;
									}
								}
								$content=str_replace('./styles/imageline','http://forum.image-line.com/styles/imageline',$content);
								$content=str_replace('src="images/smiles/','src="http://forum.image-line.com/images/smiles/',$content);
								$content=str_replace('href="/','href="http://forum.image-line.com/',$content);
								$content=str_replace('href="./','href="http://forum.image-line.com/',$content);
								$forum_result=$content;
							}
							else
								$forum_result='
									  <div class="search_heading"><span class="rvts24">Results from support forum</span></div>'
									  .$content.'
									';

							$main_result_head.='
								<div id="vt_wrap">
								<a href="http://support.image-line.com/videotutorials/?search='.$query.'" target="_blank" class="topic_t">
									 <img id="yt_logo" src="../images/ytlogo.png" alt="Results from Video Tutorials">
									 <span id="yt_link">Click here to see Results from Video Tutorials</span></a>
								</div>';
						}
					}

					$main_result_head='<div class="search_heading">'.$main_result_head.'</div>';
					if($main_result!='')
					{
						if($forum_search)
							$body_section.=$forum_result.'<br>'.$main_result_head.$main_result.$kb_result;
						else
							$body_section.=$main_result_head.$main_result.$kb_result.$forum_result;
					}
					else
					{
						$rcol=$kb_result.'<br>'.$help_match_result;
						if($this->s_internal_use)
						{
						  $match_result='<div id="main_result">'.$match_result.'</div>';
						  $rcol='<div class="kb_result'.($forum_search?'_forum':'').'">'.$rcol.'</div></div><div class="clear"></div>';
						}
						if($forum_search)
							$body_section.=$main_result_head.$forum_result.F_BR.F_BR.$rcol;
						else
							$body_section.=$main_result_head.$match_result.$rcol.$forum_result;
					}

					$res_generated=$this->lang_l('page created in').' '. round(Date::microtimeFloat() - $this->query_st_time,4);
					if(strpos($s_template_content,'%GENERATEDTIME%')!==false && !$this->call_from_outside)
						$s_template_content=str_replace('%GENERATEDTIME%',$res_generated,$s_template_content);
					elseif(!$use_params && $this->showresulttime)
						$body_section.=F_BR.'<div class="search_time"><span class="rvts8">'.$res_generated.' '.$this->lang_l('seconds').'</div></span>';
				}
				else
				{
					$body_section.=F_BR.'<div class="search_summary"><span class="rvts8">'.$this->lang_l('search box empty').'</span></div>'.F_BR;
					if(!$use_params)
						$body_section='<div class="search_container">'.$body_section.'</div>';
					$nav=Formatter::GFSAbi($s_template_content,'%NAVIGATION','%');
					$s_template_content=str_replace(array($nav,'%navigation%','%HEADER%','%GENERATEDTIME%','%ADVANCED_SEARCH_LINK%'),'',$s_template_content);
				}
			}
			$nav=Formatter::GFSAbi($s_template_content,'%NAVIGATION','%');
			$s_template_content=str_replace(array($nav,'%navigation%','%HEADER%','%GENERATEDTIME%','%ADVANCED_SEARCH_LINK%','%ADVANCED_SEARCH%'),'',$s_template_content);

			if($this->call_from_outside)
				return ($use_params?$s_template_styles:'').$body_section;

			$pi=isset($page_info[17])?$page_info[17]:'';

			if($this->s_using_template_page)
			{
				$s_template_content=str_replace(array('&lt;SEARCH_BODY&gt;','&lt;/SEARCH_BODY&gt;'),array('<SEARCH_BODY>','</SEARCH_BODY>'),$s_template_content);
				$search_body=Formatter::GFS($s_template_content,'<SEARCH_BODY>','</SEARCH_BODY>');
				if($search_body=='')
					$search_body=PageHandler::getArea($s_template_content);
				$template_body=$this->s_GTs($search_body,$body_section,$query,$id,$pi);

				$indir=(strpos($this->s_gt_page,'../')===false);
				if($indir)
					$s_template_content=str_replace('</title>','</title>'.F_LF.'<base href="'.str_replace('documents','',$this->s_full_path_to_script).'">',$s_template_content);
			}

			if(isset($_REQUEST['result']))
			{
				if($this->s_using_template_page)
					print $template_body;
				else
				{
					if($this->f->uni)
						print $body_section;
					elseif(function_exists('iconv'))
						print iconv($this->s_charset,"UTF-8//IGNORE",$body_section);
					else
						print $body_section;
				}
				exit;
			}
			if($this->s_advanced_search && !$this->call_from_outside)
			{
				$q=isset($_REQUEST['q'])?Formatter::stripQuotes(Formatter::stripTags($_REQUEST['q'])):'';
				if($this->s_using_template_page)
				{
					$body_repl=$this->build_advsearch_form($page_info,$cats_list,isset($id)?$id:'',$template_body,$q);
					$output=str_replace($search_body,$body_repl,$s_template_content);
				}
				else
				{
					$asearch_form=$this->build_advsearch_form($page_info,$cats_list,isset($id)?$id:'',$body_section,$q);
					$output=$this->s_GTs($s_template_content,$asearch_form,$query,$id,$pi);
				}
			}
			else
				$output=$this->s_GTs($s_template_content,$body_section,$query,$id,$pi);

			print $output;
		}
	}

	function prepare_search_output($results,$max_results,$query,$id,$search_in_cur_lang,$show_occurrences,
			  $date_params,$s_output_params,$main_result,$use_params,&$s_template_content,$count_res,$page,
			  $all_pages_list,$empty_nav=false,$add_counter=true)
	{
		global $db,$script_path;

		if(!isset($script_path))
			$script_path='';
		if(empty($results))
			return '';
		if($max_results!=0)
		{
			if($this->call_from_outside)
				$nav_url=$script_path.'?action=frontpage&amp;q='.urlencode($query).($id!== null?'&amp;id='.$id:'');
			else
				$nav_url=($this->call_from_outside? $script_path.'?': (strpos($this->s_gt_page,'../')===false?'documents/':'../documents/') .'search.php?action=search&amp;').'q='.urlencode($query).($id!== null?'&amp;id='.$id:'').'&amp;mr='.$max_results .'&amp;sa='.$search_in_cur_lang
					.(isset($_REQUEST['mobile_search'])?'&amp;mobile_search=1':'');

			$def_nav=strpos($s_template_content,'%NAVIGATION')!==false && !$this->call_from_outside;
			$params='';
			$nav=Formatter::GFSAbi($s_template_content,'%NAVIGATION','%');
			if($this->loadmore === false)  //12345 nav
				$params=$def_nav?Formatter::GFS($nav,'%NAVIGATION(',')%'):'';
			else // Load more navigation
				$params = 'loadmore, search';
			if($empty_nav)
				$res_nav = '<div class="user_nav"></div>';
			else
				$res_nav=Navigation::page($count_res,$nav_url,$max_results,$page,$this->lang_l('From'),'nav',$this->lang_l,'&amp;','',false,false,'',false,$params);
			if(!$this->livesearch) {
				if($def_nav)
					$s_template_content=str_replace($nav,$res_nav,$s_template_content);
				else
				{
					$navigation_div = '<div class="search_nav">'.$res_nav.'</div>';
					$main_result.= ($this->loadmore === false)?$navigation_div:'';
				}
			}
		}
		$counter=($page-1)*$max_results;
		if($show_occurrences || $_SERVER['REMOTE_ADDR'] == '195.144.71.12' || $_SERVER['REMOTE_ADDR'] == '78.21.57.124')
			$show_occurrences=false;

		$main_result.=(!$use_params?'<div class="search_blocks'.($this->livesearch?'_dropdown':'').'">':'');
		foreach($results as $v)
		{
			$counter++;
			$lm_date='';
			if(isset($v[4]) && !empty($v[4]))
			{
				$lm_date.=(!$use_params?($v[12]=='modified_date'?$this->lang_l('last modified'):$this->lang_l('created date')).': ':'');
				if(!$use_params)
					$lm_date.=date('j M Y',intval($v[4])).(!$use_params?' - ':'');
				else
					$lm_date.=Date::format(intval($v[4]),$date_params,$this->f->month_names,$this->f->day_names,"long");
			}
      if(strpos($v[1],'http')===false)
      {
        if($this->call_from_outside)
          $url=$this->f->http_prefix.Linker::getHost().$_SERVER['PHP_SELF'].substr($v[1],strrpos($v[1],'?'));
        else
          $url=$this->f->http_prefix.str_replace('documents','',Linker::getHost().dirname($_SERVER['PHP_SELF'])) .str_replace('../','',$v[1]);
      }
      else
        $url=$v[1];
  		$url_rel = '';
    	if(isset($v['url_rel']))
      	 $url_rel=' rel="'.$v['url_rel'].'"';

      Formatter::replacePollMacro_null($v[2]);

			if(!$use_params)
			{
				$main_result.='
				<div class="search_row '.($counter%2?'search_even':'search_odd').'">
					 <div class="search_title">
						  '.($add_counter?'<span class="rvts0"><b>'.$counter.'.</b></span>&nbsp;':'').'
						  <a class="rvts4 s_res_url" href="'.$url.'" '.$url_rel.'>'.$v[6].'</a>
					 </div>
					 <div class="search_content">
						  <span class="rvts8">'.Formatter::sth2($v[2]).'</span>
					 </div>'.
					 (!$this->livesearch?'
					 <div class="search_info">
					 	<span class="rvts8">'.($show_occurrences?$v[5].' '.$v[9].' '.$v[10].' '.$v[11]:'').$lm_date.'URL: '.$url.'</span>
					 </div>':'').'
				</div>';
			}
			else
			{
				if(in_array($v[7],array('136','137','138','143')) && $v[8]!='')  // blog, pblog, cal, podcast
				{
					$category_info=$db->query_first('SELECT cats.cname as category
						 FROM '.$db->pre.$v[3].'_'.($v[7]=='136'?'events':'posts').' AS posts, '.$db->pre.$v[3].'_categories AS cats
						 WHERE posts.'.($v[7]=='136'?'event_id = "'.$v[8].'"':'entry_id = '.$v[8]).'
						 AND posts.category=cats.cid');
					$category=$category_info['category'];
				}
				else
					$category=str_replace('#','',$all_pages_list[$v[3]][2]);
				$parsed='<div class="'.(($counter%2)?'search_even':'search_odd').'">'.$s_output_params.'</div>';
				$parsed=str_replace('%occurances%','%occurrences%',$parsed);
				$parsed=str_replace(array('%counter%','%title%','%date%','%content%','%url%','%category%','%occurrences%'),
														array($counter,$v[6],$lm_date,Formatter::sth2($v[2]),$url,(isset($category)?$category:''),($show_occurrences?$v[5].' '.$v[9].' '.$v[10]:'')),$parsed);
				$main_result.=$parsed;
			}
		}
		$main_result.=(!$use_params?'</div>':'');
		$main_result .= ($this->loadmore === true)&&isset($navigation_div)?$navigation_div:'';
		$counter_loc = '';
		if($this->call_from_outside)
			 $counter_loc=$this->f->http_prefix.Linker::getHost().$_SERVER['PHP_SELF'].'documents/counter.php';
		else
			 $counter_loc=$this->f->http_prefix.str_replace('documents','',Linker::getHost().dirname($_SERVER['PHP_SELF'])).'documents/counter.php';
		$js='
			<script type="text/javascript">
				$(document).ready(function() {
				$(".search_container a").click(function(e){
					e.preventDefault();
					var hr=$(this).attr("href");
					$.post("'.$counter_loc.'",{lsid:'.$this->detail_stat_id.',action:"handleHit",url:hr})
					.always(function() {window.location = hr;});
					return true;
				});
			});
			</script>';

		return $main_result.$js;
	}
}

class xSearch
{
	public $search;
	public $f;

	public function __construct($search)
	{
		global $f;
		$this->f=$f;
		if($search instanceof SearchHandlerClass)
			$this->search=$search;
	}

	public function buildXSearchForm()
	{
		 $html='
		  <div id="wiz_area">
		  <form method="POST" action="'.$this->search->self_url.'" class="search_page" id="search_form">
				<input type="text" name="q" placeholder="Search" autocomplete="off" class="search_inp">
				<input type="submit" value="Search" class="search_btn">
				<div id="live_results"></div>
				<div class="search_desc">
				<p class="large_text">
					 <a class="large_text_url" href="'.$this->search->self_url.'?action=asearch">Advanced Search</a>
				</p>
		  </div>
		  </form>';

		 $css='
		  #search_form *{box-sizing:border-box;}
		  #search_form,.videos{width:80%;display:inline-block;background:#eaeaea;padding:8px 15px;margin-bottom:12px;;position:relative;}
		  .search_inp{width:100%;padding-left:10px;padding-right:150px;height:52px;font-size:34px;border:none;}
		  .search_btn{background:#f26522;color:white;padding:9px 20px;font-size:24px;border:none;position:absolute;right:16px;top:9px;}
		  .large_text{font-size:17px;padding-top: 10px;color: black;}
		  .large_text_url{color:#f26522;text-decoration:none;}
		  .large_text_url:hover{text-decoration:underline;}
		  .videos img{height:150px;}

		  .videos h2{margin:13px 0;color:black;}
		  #search_form ul{list-style:none;margin:13px 0;}
		  .videos li{display:inline-block;width:30%;}
		  .videos p{margin:8px 0;}

		  #wiz_area{text-align:center;margin: 20px 0;}
		  #wiz_area a{color:#f26522;text-decoration:none;}
		  #wiz_area a:hover{text-decoration:underline;}
		  #live_results{background:white;padding:0;}
		  #live_results li{padding:4px 0;position:relative;}
		  #live_results .search_odd,#live_results .search_even{margin:0;background:white;position:relative;}
		  #live_results .livesearch_content{position: absolute;right: 10px;top:5px;}
		  #live_results .livesearch_a{font: 16px/24px tahoma;letter-spacing: 1px;padding-left: 7px;}
		  .res_category {position: absolute;right: 0;}
		  ';

		 $js='
		  $(document).ready(function(){
				$(".search_inp").livesearch();
		  });
		  ';
		  $s_gt_path=((strpos($this->search->s_gt_page,'../')===false) && $this->search->rel_path!=''?'../':'').$this->search->s_gt_page; //search output params
		  $output=$this->search->s_GTs(File::read($s_gt_path),$html);

		  $output=Builder::includeScript($js,$output,array());
		  $output=Builder::includeCss($output,$css);
		  print $output;
	}
}

if(!isset($GLOBALS['calling_page']))
{
	include_once ('../ezg_data/functions.php');
	include_once('../ezg_data/mysql.php');

	$search=new SearchHandlerClass('../',false);
	$search->process();
}

function process_search($search_in_page='',$cat_name='',$rel_path='',$remove_junk=false,$date_used='creation_date')
{
	$search=new SearchHandlerClass($rel_path,true);
	return $search->process($search_in_page,$cat_name,true,$remove_junk,$date_used);
}

?>

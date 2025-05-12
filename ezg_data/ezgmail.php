<?php
/*
	ezgemail.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/

define('ADM_ACT_INDEX','index');
define('VER_ACT','version');
define('ADM_ACT_EXPORTCSV','export_csv');

if($f->use_mysql)
{
	include_once($rel_path.'ezg_data/mysql.php');
}

class FormHandlerClass
{
	public $version='EZGenerator form handler 5.2.22';
	protected $pg_id;
	protected $pg_name;
	protected $pg_dir;
	protected $pg_settings;
	protected $rel_path;
	protected $useic;
	protected $script_path;
 	protected $day_name;
 	protected $month_name;

	protected $f;
	protected $user;
	protected $page_info='';

	public function __construct($pid,$pdir,$pname,$relpath,$settings)
	{
		global $f,$user;

		$this->f=$f;
		$this->user=$user;
		$this->pg_id=$pid;
   	$this->pg_dir=$pdir;
		$this->pg_name=$pname;
		$this->script_path=$pdir.$pname;
   	$this->rel_path=$relpath;
		$this->pg_settings=$settings;
		$this->useic=(!$this->f->uni && $this->pg_settings['page_encoding']!='iso-8859-1' && function_exists("iconv"));
		$this->update_language_set();
	}

	protected function get_fields()
	{
		$vars=array();
		foreach($_POST as $k=>$v)
		{
			$vars[$k]='';
			if(is_array($v))
				foreach($v as $sv)
					$vars[$k].=$sv.';';
			else
				$vars[$k]=trim($v);
			$x=strtolower(urldecode($vars[$k]));
			if((strpos($x,'mime-version')!== false)||(strpos($x,'content-type:')!== false))
				die("Why ?? :(");
		}
		return $vars;
	}

	protected function getExt($fname)
	{
		$sTmp=$fname;$sExt="";
		while($sTmp!="")
		{
			$sTmp=strstr($sTmp,".");
			if($sTmp!="")
			{
				$sTmp=substr($sTmp,1);
				$sExt=$sTmp;
			}
		}
		return strtolower($sExt);
	}

	protected function isTypeAllowed($fname)
	{
		if($this->pg_settings['AllowedTypes']=="*")
			return true;
		if((strpos('|'.$this->pg_settings['AllowedTypes'].'|','|'.$this->getExt($fname).'|')!==false )&&(substr_count($fname,'.')==1))
			return true;
		else
			return false;
	}

	protected function check_fields($vars,$miniform)  //required fields starts with _re
	{
		$issues=array();
		$ccheck=isset($_POST['cc']) && $_POST['cc']=='1';

		if($this->pg_settings['use_captcha'])
		{
			Session::intStart();
			if(!Captcha::isValid())
				$issues[]=($ccheck?'captchacode|':'').$this->lang_uc('Captcha Message');
		}
		if($this->pg_settings['mail_fields'])
		{
			foreach($this->pg_settings['mail_fields'] as $k=>$v)
			{
				if(isset($vars[$v]))
				{
					if(in_array($v,$this->pg_settings['re_fields']) || $vars[$v]!='')
					{
						if(!Validator::validateEmail($vars[$v]))
							$issues[]=($ccheck?$v.'|':'').$this->lang_uc('Email not valid');
					}
					if(isset($vars[$v.'_confirm']))
					{
						if($vars[$v] != $vars[$v.'_confirm'])
							$issues[]=($ccheck?$v.'_confirm|':'').$this->lang_uc('Emails do not match');
					}
				}
				if($issues)break;
			}
		}
		$files=false;
		foreach($vars as $k=>$v)
		{
			if(strpos($k,'up_')===0)
			{
				$fieldname=Formatter::GFS($k,'up_','');
				if(strpos($v,'fakepath')!=false)
					$v=Formatter::GFS($v,'fakepath\\','');
				if(strpos($fieldname,'file_')!==false || !in_array($fieldname,$this->pg_settings['re_upfields']))
					$fieldname='error';
				if($v!='')
					$files=true;
				if(($v!='')&&(!$this->isTypeAllowed($v)))
					$issues[]=($ccheck?$fieldname.'|':'').$v." is not allowed!";
			}
		}
		foreach($this->pg_settings['re_fields'] as $k=>$v)
		{
			$vx=str_replace(' ','_',$v);
			if($vx!='')
			{
				$vxc=str_replace('[]','',$vx);
				if(in_array($vx,$this->pg_settings['re_upfields']))
				{
					 $fieldset=isset($vars['up_'.$vx])&&($vars['up_'.$vx]!='');
					 if($ccheck && !$fieldset && !$files)
						  $issues[]=($ccheck?$vx.'|':'').$this->lang_uc('Required Field');
				}
				elseif(!isset($vars[$vxc]))
					 $issues[]=($ccheck?$vx.'|':'').$this->lang_uc('Checkbox unchecked');
				elseif(!strlen($vars[$vxc]))
					 $issues[]=($ccheck?$vx.'|':'').$this->lang_uc('Required Field');
			}
		}
		if(!empty($issues))
			$issues[]=($ccheck?'error|':'').$this->lang_uc('validation failed');

		if(count($_FILES))
		{
			$files=array_keys($_FILES);
			foreach($files as $file)
			{
				$fname=$_FILES[$file]['name'];
				if(($fname!='')&&(!$this->isTypeAllowed($fname)))
					$issues[]=($ccheck?'error|':'').$fname." is not allowed!";
			}
		}

		if($this->pg_settings['forbid_urls'])
		{
			$tmp=strtolower(implode("|",$vars));
			if((strpos($tmp,'http')!==false || strpos($tmp,'href')!==false || strpos($tmp,'www.')!==false))
				$issues[]=($ccheck?"error":"")."|Url's are not allowed!";
		}

		if($ccheck)
		{
			$output=implode('|',$issues);
			if($this->useic)
				$output=iconv($this->pg_settings['page_encoding'],"utf-8",$output);
			if($miniform)
			{
				$upload=isset($_REQUEST['ccu']);
				if(count($issues)>0)
				{
					print '0'.$output;
					exit;
				}
				elseif($upload)
				{
					$output=($this->useic)?iconv($this->pg_settings['page_encoding'],"utf-8",$this->pg_settings['submit_body']):$this->pg_settings['submit_body'];
					print '1|'.$output;
					exit;
				}
			}
			else
			{
				if(count($issues)>0)
					print '0'.$output;
				else
					print '1';
				exit;
			}
		}

		return $issues;
	}

	protected function redirect($vars)
	{
		if(strpos(strtolower($this->pg_settings['submit_url']),'http://') !== false)
		{
			header('Location: '.$this->pg_settings['submit_url']);
			header('Status: 303');
		}
		else
		{
			$submit_path=$this->pg_settings['submit_url'];
			$page_is_mobile=($this->pg_settings['mobile_detect_mode']>0)?Mobile::detect($this->pg_settings['mobile_detect_mode']):false;

			if($page_is_mobile)
			{
				if($this->pg_dir!=='')
					$temp=str_replace($this->pg_dir,$this->pg_dir.'i_',$this->pg_settings['submit_url']);
				else
					$temp='i_'.$this->pg_settings['submit_url'];
				if(file_exists($temp))
					$submit_path=$temp;
			}
			$src=File::read($submit_path);

			foreach($vars as $rs=>$v)
				$src=str_replace('%'.$rs.'%',$v,$src);
			print $src;
		}
		exit();
	}

	protected function auto_reply($vars,$file_cnt_array,$file_name_array,$file_type_array)
	{
		$date_time=$date_time=Date::format_date(Date::tzone(time()),$this->page_lang,$this->month_name,$this->day_name);
		$result='';
		$r_message=str_replace('\"','"',$this->pg_settings['reply_message']);
		$r_message=str_replace('%date%',$date_time,$r_message);
		$r_message=str_replace('##',strpos($r_message,'<')!==false?'':'<br \>', $r_message);
		$r_subject=$this->pg_settings['reply_subject'];
		$r_subject=str_replace('%date%',$date_time,$r_subject);
		if(strpos($r_message,'%date[')!==false)
			$r_message=$this->parse_date($r_message);
		foreach($vars as $k=>$v)
		{
			$r_message=str_ireplace('%'.$k.'%',$v,$r_message);
			$r_subject=str_ireplace('%'.$k.'%',$v,$r_subject);
		}

		$rcpt='';
		if($this->pg_settings['mail_fields'] && isset($this->pg_settings['mail_fields'][0]) && $this->pg_settings['mail_fields'][0]!='')
			$rcpt=$vars[$this->pg_settings['mail_fields'][0]];
		elseif(isset($vars['Email']))
			$rcpt=$vars['Email'];

		if(strpos($r_message,'%ATTACHMENTS%')===false)
			$file_cnt_array=$file_name_array=$file_type_array='';
		else
			$r_message=str_replace('%ATTACHMENTS%','',$r_message);

		$htmlbody='';
		$body=$r_message;
		if($this->pg_settings['visual'] || strpos($body,'<')!==false)
		{
			$htmlbody=$r_message;
			$body='';
		}

		if($rcpt!=='')
		{
			if($this->f->use_mysql)
				$result=MailHandler::sendMailStat(null,$this->pg_id,array($rcpt),$this->pg_settings['reply_from'],$htmlbody,$body,$r_subject,$this->pg_settings['page_encoding'],$file_cnt_array,$file_name_array,$file_type_array);
			else
				$result=MailHandler::sendMail(array($rcpt),$this->pg_settings['reply_from'],$htmlbody,$body,$r_subject,$this->pg_settings['page_encoding'],$file_cnt_array,$file_name_array,$file_type_array);
		}

		return $result;
	}

	protected function _build_fields($vars,$html)
	{
		$hidden=array('REQUEST_SEND','cc','_x','_y','.x','.y');
		$pa_fields="";
		foreach($vars as $k=>$v)
		{
			$suff=(strlen($k)> 2)?(strtolower(substr($k,strlen($k)-2))):'';
			if(!in_array($k,$hidden) && !in_array($suff,$hidden))
			{
				$field=(isset($this->pg_settings['field_labels'][$k])?$this->pg_settings['field_labels'][$k]:$k).'= '.$v.($this->pg_settings['visual']?'<br />':'');
				$field=$html?'<div>'.$field.'</div>':$field.F_LF;
				$pa_fields.=$field;
			}
		}
		return $pa_fields;
	}

	protected function update_language_set()
	{
		foreach($this->pg_settings['lang_uc'] as $k=>$v)
		{
			if(in_array($k,$this->f->day_names))
				$this->day_name[]=trim($v);
			elseif(in_array($k,$this->f->month_names))
				$this->month_name[]=trim($v);
		}
	}

	protected function parse_date($src)
	{
		$date_time=Date::format(Date::tzone(time()),Formatter::GFS($src,'%date[',']%'),$this->month_name,$this->day_name,'short',false);
		$src=str_replace(Formatter::GFSAbi($src,'%date[',']%'),$date_time,$src);
		return $src;
	}

	protected function attach_files(&$file_cnt_array,&$file_name_array,&$file_type_array)
	{
		$files=array();
		$file_count=0;
		if(count($_FILES))
		{
			$files=array_keys($_FILES);
			foreach($files as $file)
				if($_FILES[$file]['name']!='')
					$file_count++;
		}

		if($file_count>0)
		{
			$file_cnt_array=$file_name_array=$file_type_array=array();
			foreach($files as $file)
			{
				$file_name=$_FILES[$file]['name'];
				$file_type=$_FILES[$file]['type'];
				$file_tmp_name=$_FILES[$file]['tmp_name'];

				if($this->pg_settings['max_imagesize']>0)
				{
					 $sExt=$this->getExt($file_name);
					 if ($sExt=="gif" || $sExt=="jpg" || $sExt=="jpeg" || $sExt=="png")
					 {
						$new_fname=$this->rel_path.'innovaeditor/assets/'.$file_name;
						if(file_exists($new_fname))
							$file_tmp_name=$new_fname;
						else
						{
							move_uploaded_file($file_tmp_name,$new_fname);
							$file_tmp_name=$new_fname;
							Image::scale($file_tmp_name,$this->pg_settings['max_imagesize'],'rescaleimage',80);
						}
					 }
				}

				if(!strlen($file_type))
					$file_type="application/octet-stream";
				if($file_type == 'application/x-msdownload')
					$file_type="application/octet-stream";
				$file_cnt_array[]=$file_tmp_name;
				$file_name_array[]=$file_name;
				$file_type_array[]=$file_type;
			}
		}
	}

	protected function send_mail2($vars,$file_cnt_array,$file_name_array,$file_type_array)
	{
		$date_time=$date_time=Date::format_date(Date::tzone(time()),$this->page_lang,$this->month_name,$this->day_name);
		if($this->pg_settings['main_message']=='')
			$this->pg_settings['main_message']='%FORM_DATA%';
		$body=$this->pg_settings['main_message'];
		$is_html=strip_tags($body)!=$body;

		$fields=$this->_build_fields($vars,$is_html);
		if($this->useic && $this->pg_settings['submit_url']=='')
			$fields=iconv("utf-8",$this->pg_settings['page_encoding'],$fields);
		$fields=str_replace("\'","'",$fields);
		$fields=str_replace('\"','"',$fields);
		$fields.='date= '.$date_time;

		if($this->useic && $this->pg_settings['submit_url']=='')
			foreach($vars as $k=>$v)
				$vars[$k]=iconv("utf-8",$this->pg_settings['page_encoding'],$v);

		$r_subject=$this->pg_settings['subject'];
		foreach($vars as $k=>$v)
			$r_subject=str_ireplace('%'.$k.'%',$v,$r_subject);

		$reply_to='';
		if(count($this->pg_settings['mail_fields'])>0)
		{
			$reply_to=$vars[$this->pg_settings['mail_fields'][0]];
			$f=array('Name','name','Last_Name','last_name','Last_name');
			foreach($f as $k=>$v)
			{
				if(isset($vars[$v]))
				{
					$reply_to='"'.$vars[$v].'" <'.$vars[$this->pg_settings['mail_fields'][0]].'>';
					break;
				}
			}
		}
		$send_from=$this->pg_settings['reply_from'];

		$body=str_replace(array('%FORM_DATA%','%IP%','%date%','\"'),array($fields,Detector::getIP(),$date_time,'"'),$body);
		if(strpos($body,'%date[')!==false)
		{
			$body=str_replace('date= '.$date_time,'',$body);
			$body=$this->parse_date($body);
		}

		foreach($vars as $k=>$v)
		{
			$kb=$k.'[]';
			$body=str_ireplace(array('%'.$k.'%','%'.$kb.'%'),$v,$body);
		}

		foreach($this->pg_settings['field_labels'] as $k=>$v)
		{
			$kb=str_replace('[]','',$k);
			$body=str_ireplace(array('%'.$k.'%','%'.$kb.'%'),'',$body);
		}

		$htmlbody='';
		if($this->pg_settings['visual'] || $is_html)
		{
			$htmlbody=$body;
			$body='';
		}

		if($this->f->use_mysql)
			$result=MailHandler::sendMailStat(null,$this->pg_id,$this->pg_settings['sendto_array'],$send_from,
					  $htmlbody,$body,$r_subject,$this->pg_settings['page_encoding'],
					  $file_cnt_array,$file_name_array,$file_type_array,'','',
					  $this->pg_settings['send_to_bcc'],$reply_to);
		else
			$result=MailHandler::sendMail($this->pg_settings['sendto_array'],$send_from,
					  $htmlbody,$body,$r_subject,$this->pg_settings['page_encoding'],
					  $file_cnt_array,$file_name_array,$file_type_array,'','',$this->pg_settings['send_to_bcc'],$reply_to);
		return $result;
	}

	protected function export_csv()
	{
		global $db;

		$db=DB::dbInit($this->f->db_charset,($this->f->uni?$this->f->db_charset:''));
		$lf="\r\n";

		Session::intStart('private');
		$this->check_access();

		$output='';
		foreach($this->pg_settings['field_labels'] as $k=>$v)
			$output.='"'.$v.'",';
		$output.='"received"'.$lf;

		$records=$db->fetch_all_array('
			SELECT message_text,message_html,created
			FROM '.$db->pre.'ca_email_data
			WHERE page_id="'.$this->pg_id.'" AND success="1"
			ORDER BY created DESC ');

		$fields=array_keys($this->pg_settings['field_labels']);

		foreach($records as $k=>$v)
		{
			$html=$v['message_text']=='';
			$body=$html?$v['message_html']:$v['message_text'];
			$line='';
			$hit=0;
			$openingTag=$html?'>':F_LF;
			$closingTag=$html?'<':F_LF;
			foreach($fields as $v2)
			{
				 $val=Formatter::GFS($body,$openingTag.$v2.'= ',$closingTag);
				 if($val=='')
					 $val=Formatter::GFS($body,$openingTag.$this->pg_settings['field_labels'][$v2].'= ',$closingTag);
				 $val=str_replace(array($lf,"\n",'"'),array('</br>','</br>','""'),$val);
				 $line.='"'.$val.'",';
				 if($val!='')
					 $hit=1;
			}
			$line.='"'.$v['created'].'"'.$lf;
			if($hit)
				$output.=$line;
		}
		output_generator::sendFileHeaders("email_list.csv");
		echo $output;
		exit;
	}

	protected function lang_l($label)
	{
		$r=(isset($this->pg_settings['lang_l'][$label])?$this->pg_settings['lang_l'][$label]:$label);
		return $r;
	}

  protected function lang_uc($label)
  {
      $r=(isset($this->pg_settings['lang_uc'][$label])?$this->pg_settings['lang_uc'][$label]:$label);
      return $r;
  }

	protected function show_admin($action)
	{
		global $db;

		$db=DB::dbInit($this->f->db_charset,($this->f->uni?$this->f->db_charset:''));

		Session::intStart('private');
		//--------------DO NOT DUMP OR PRINT ANYTHING BEFORE THIS LINE!!! (in this function)-------------------------
		$this->check_access();

		$this->page_info=CA::getPageParams($this->pg_id,$this->rel_path);
		$user_account=$this->user->mGetLoggedUser($db,'');
		$is_admin=$user_account!==false && $user_account['uid']==-1;
		$user_is_admin=$user_account['user_admin']==1;
		$edit_own_posts_only=$is_admin?false:User::userEditOwn($db,$this->user->getId(),$this->page_info);

		$output='';

		// show tables (should be new function if more modules are added in this script)
		$sitemap_list=CA::getSitemap($this->rel_path,false,true);
		$max=Navigation::recordsPerPage();
		$empty_set=true;

		if(isset($_GET['eid']))
		{
			$rr=$db->query_first('
				SELECT *
				FROM '.$db->pre.'ca_email_data
				WHERE id='.intval($_GET['eid']));
			$res=MailHandler::sendMailStat($db,$rr['page_id'],array($rr['send_to']),$rr['msgfrom'],$rr['message_html'],$rr['message_text'],$rr['subject'],(isset($sitemap_list[$rr['page_id']])?$sitemap_list[$rr['page_id']][17]:$this->f->db_charset),'','','','','',$rr['bcc']);
		}
		elseif(isset($_GET['did']))
			$res=$db->query('
				DELETE
				FROM '.$db->pre.'ca_email_data
				WHERE id='.intval($_GET['did']));

		$total_count=$this->ca_db_count('ca_email_data','page_id="'.$this->pg_id.'"');

		if($total_count>0)
		{
			$c_page=(isset($_GET['page'])?intval($_GET['page']):1);

			$show_records=$db->fetch_all_array('
				SELECT * FROM '.$db->pre.'ca_email_data
				WHERE page_id="'.$this->pg_id.'" '.
					  ($edit_own_posts_only?' AND msgfrom LIKE LIKE "%= '.$user_account['email'].'%" OR message_html LIKE "%= '.$user_account['email'].'%" ':'').'
				ORDER BY created DESC '.(($total_count>$max && $max!=0)? '
				LIMIT '.($c_page-1) * $max.', '.$max.'':''));

			if(!empty($show_records))
			{
				$nav=Navigation::pageCA($total_count,'?action='.ADM_ACT_INDEX,0,$c_page);
				$empty_set=false;
				$cap_arrays=array($this->lang_l('date'),$this->lang_l('page'),$this->lang_l('recipient/from'),$this->lang_l('subject/message/attachments'),$this->lang_l('status'));
				$table_data=array();
				foreach($show_records as $value)
				{
					if(!empty($value))
					{
						$pg_name=(isset($sitemap_list[$value['page_id']])?$sitemap_list[$value['page_id']][0]:($value['page_id']=='0'?$this->lang_l('administration'):($value['page_id']=='tella'? $this->lang_l('tell a friend'): $value['page_id'])));
						$date_value=date('d M Y h:i:s',Date::tzoneSql($value['created']));
						$body=$value['message_text']==''?
								 strip_tags(str_replace(array('<br /></div>','<br />','</div>',F_BR),F_LF,$value['message_html'])):
								 $value['message_text'];
						$row_data=array('
							<span class="rvts8">'.$date_value.'</span><br>
								[<a class="rvts12" href="?action='.ADM_ACT_INDEX.'&amp;did='.$value['id'].'&amp;page='.$c_page.'">'.$this->lang_l('remove').'</a>]
							','
							<span class="rvts8">'.$pg_name.'</span>',
							'
							<div style="word-wrap:break-word;width:150px;">
								<span class="rvts8">'.htmlentities($value['send_to']).'</span><br>
								<span class="rvts8">'.htmlentities($value['msgfrom']).'</span> '
							.(isset($value['ip'])&&$value['ip']!=''?Builder::ipLocator($value['ip']):'').'
							</div>',
							'
							<div style="word-wrap:break-word;width:250px;">
								<span class="rvts8">'.$value['subject'].'</span><br><br>
								<span class="rvts8">'.str_replace(F_LF,F_BR,$body).'</span>'
							.($value['attachments']!==''?F_BR.F_BR.'<span class="rvts8">'.$value['attachments'].'</span><br>':'').'
							</div>',
							'<span class="rvts8">'.($value['success']=='1'?$this->lang_l('sent'):$this->lang_l('failed').'<br>'.$value['success']).'</span><br>
							[<a class="rvts12" href="?action='.ADM_ACT_INDEX.'&amp;eid='.$value['id'].'&amp;page='.$c_page.'">'.$this->lang_l('re-send').'</a>]');
						$table_data[]=$row_data;
					}
				}
				$append='<input type="button" onclick="document.location=\''.'?action=export_csv\'" value=" export as csv ">';
				$output.=Builder::adminTable($nav,$cap_arrays,$table_data,$append);
			}
		}
		if($empty_set)
		{
			$table_data[]=array('','<span class="rvts8">Empty</span>');
			$output.=Builder::addEntryTable($table_data,'');
		}
		$output=Formatter::fmtAdminScreen($output,$this->_build_menu($action,$user_account,$is_admin,$user_is_admin));
		$output=Formatter::fmtInTemplate($this->pg_dir.$this->pg_name,$output,$this->lang_l('administration'),'','',true,false,false,substr($this->pg_name, -4) === '.php');
		print $output;
		exit;
	}

	protected function _build_menu($action,$logged_user,$is_admin,$user_is_admin)
	{
		global $thispage_id,$db;

		$logged_as=$logged_user['username'];
		$url_base='?action=';
		$data=array();
		$data[]=Navigation::addEntry($this->lang_l('mails log'),$url_base.ADM_ACT_INDEX,$action==ADM_ACT_INDEX,'log');
		$ca_url_base=$this->rel_path.'documents/centraladmin.php';

	  if($is_admin || $user_is_admin)
	  {
			$data[]=Navigation::addEntry($this->lang_l('administration'),$ca_url_base.'?process=index',false,'administration','','last');
			$data[]=Navigation::addEntry($this->lang_l('logout'),$ca_url_base.'?process=logoutadmin',false,'logout',$logged_as,'a_right');
		}
		else
		{
			if(CA::getDBSettings($db,'landing_page')==1)
				$data[]=Navigation::addEntry($this->lang_l('profile'),$ca_url_base.'?process=editprofile&amp;pageid='.$thispage_id,false,'profile','','last');
			else
				$data[]=Navigation::addEntry($this->lang_l('sitemap'),$ca_url_base.'?process=myprofile&amp;pageid='.$thispage_id,false,'sitemap','','last');
			$data[]=Navigation::addEntry($this->lang_l('logout'),$ca_url_base.'?process=logout&amp;pageid='.$thispage_id,false,'logout',$logged_as,'a_right');
		}

		$output=Navigation::admin2($data,'',false,$this->page_info[0],$this->script_path);
		return $output;
	}

	protected function ca_db_count($table,$where='')
	{
		global $db;

		$count=0;
		$fa=$db->get_tables($table);
		if(!empty($fa))
		{
			$count_raw=$db->fetch_all_array('
				SELECT COUNT(*)
				FROM '.$db->pre.$table.($where!=''?'
				WHERE '.$where:''));
			$count=$count_raw[0]['COUNT(*)'];
		}
		return $count;
	}

	protected function check_access()
	{
		global $db;
		 Session::intStart('private');
		//--------------DO NOT DUMP OR PRINT ANYTHING BEFORE THIS LINE!!! (in this function)-------------------------
		$this->page_info=CA::getPageParams($this->pg_id,$this->rel_path);
		$this->user->mGetLoggedUser($db,'');
		if(!$this->user->isAdmin())
		{
			$access_type='';
			if(!$this->user->userCookie() || !User::mHasWriteAccess2($this->user->getUname(),$this->user->getAllData(),$this->page_info,$access_type))
			{
				Linker::redirect($this->rel_path."documents/centraladmin.php?pageid=".$this->pg_id."&indexflag=index",false);
				exit;
			}
		}
		return;
	}

	public function process()
	{
		$action=(isset($_REQUEST['action']) && $_REQUEST['action']!=='')?Formatter::stripTags($_REQUEST['action']):false;

		if($action!==false && $this->f->use_mysql)
		{
			switch($action)
			{
				case VER_ACT :
					exit($this->version);
					break;
				case ADM_ACT_EXPORTCSV :
					$this->export_csv();
					break;
				case ADM_ACT_INDEX:
					$this->show_admin($action);
					break;
				default:
					$this->check_access();
					break;
			}
		}

		if(($this->pg_settings['send_to']=='your@email.com') || ($this->pg_settings['send_to']==''))
			print '0error|please define recipient e-mail on request page settings panel!';
		else
		{
			$formfields=$this->get_fields();
			if(empty($formfields))
				exit($this->version);
			if($this->check_fields($formfields,$this->pg_settings['submit_url']==''))
				exit($this->version);

	 		$file_cnt_array='';$file_name_array='';$file_type_array='';
		  	$this->attach_files($file_cnt_array,$file_name_array,$file_type_array);
			$errors=$this->send_mail2($formfields,$file_cnt_array,$file_name_array,$file_type_array);
			if($errors=='1')
			{
				if($this->f->use_mysql && intval($this->pg_id)>0)
					Counter::updateEventCount(null,$this->pg_id);
				if($this->pg_settings['reply_enabled'])
					$this->auto_reply($formfields,$file_cnt_array,$file_name_array,$file_type_array);
				if($this->pg_settings['submit_url']!='')
					$this->redirect($formfields);
				else
				{
					$output=($this->useic)?iconv($this->pg_settings['page_encoding'],"utf-8",$this->pg_settings['submit_body']):$this->pg_settings['submit_body'];
					$output=str_replace('##', '<br \>', $output);
					print '<span class="submit_message">'.$output.'</span>';
				}
			}
			else
				print '0error|sending failed<br>'.$errors;
		}
	}
}

$settings=array();
$settings['visual']=$visual;
$settings['reply_enabled']=$reply_enabled;
$settings['lang_l']=$lang_l;
$settings['lang_uc']=$lang_uc;
$settings['reply_subject']=$reply_subject;
$settings['reply_from']=$reply_from;
$settings['reply_message']=$reply_message;
$settings['sendto_array']=$sendto_array;
$settings['main_message']=$main_message;
$settings['submit_body']=$submit_body;
$settings['max_imagesize']=$max_imagesize;
$settings['submit_url']=$submit_url;
$settings['subject']=$subject;
$settings['mobile_detect_mode']=isset($mobile_detect_mode)?$mobile_detect_mode:0;
$settings['AllowedTypes']=$AllowedTypes;
$settings['mail_fields']=$mail_fields;
$settings['field_labels']=$field_labels;
$settings['re_fields']=$re_fields;
$settings['re_upfields']=$re_upfields;
$settings['page_encoding']=$page_encoding;
$settings['forbid_urls']=$forbid_urls;
$settings['send_to_bcc']=$send_to_bcc;
$settings['send_to']=$send_to;
$settings['use_captcha']=$use_captcha;

$form_mailer=new FormHandlerClass($p_id,$p_dir,$p_name,$rel_path,$settings);
$form_mailer->process();

?>
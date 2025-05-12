<?php

/**
 * http://www.ezgenerator.com
 * Copyright (c) 2012-2015 Image-line
 *
 * @author Atanas
 */

/*
	$settings param example:

	$settings=array(
		 *'lang_l'=> null,
		 *'innova_def'=> null,
		  'mailData'=> null, (array(title,content) normally)
		  'page_id'=> null,
		  'from_email'=> null,
		  'page_encoding'=> null,
		  'print_output'=> null, (true - print output (for ajax calls) or false - return ourput)
		  'predefinedRecipients'=> null,
		  'bodyTemplate'=>=> null, (string that holds the body template)
		  'subjTemplate'=> null  (string, that hold the subject template)
	);
*/
class Mailer
{

	public static $version='mailer 5.2.5';

	protected $lang_l;
	protected $mailData;
	protected $innova_def;
	protected $send_to_bcc;
	protected $page_id;
	protected $from_email;
	protected $page_encoding;
	protected $print_output;
	protected $predefinedRecipients;
	protected $output;
	protected $subjTemplate;
	protected $bodyTemplate;

	protected $scriptsCode;
	protected $mailSubject;
	protected $mailBody;
	protected $recipients;
	protected $mailsLimit;//how many mials at once can be sent
	protected $mailCounter;//used to count mail sendings when limit is set and used

	protected $f;
	protected $user;

	public function __construct($settings)
	{
		global $f,$user;

		$this->f=$f;
		$this->user=$user;
		$this->Setup($settings);
		return;
	}
	public function process()
	{
		if(isset($_REQUEST['sendMails']))
			$this->sendEvent ();
		else
			$this->buildMailForm ();
		return;
	}
	public function output()
	{
		$this->output='<div class="a_n a_listing">'.$this->output.'</div>';
		if($this->print_output)
		{
			print $this->output;
			exit;
		}
		return array($this->output,$this->scriptsCode);
	}
	protected function Setup($settings)
	{
		//predefining parsed params to prevend missing (uninitialized) params
		$this->lang_l=array();
		$this->innova_def='';
		$this->mailData='';
		$this->send_to_bcc=false;
		$this->page_id=0;
		$this->from_email='';
		$this->page_encoding='UTF-8';
		$this->print_output=false;
		$this->predefinedRecipients=array();
		foreach ($settings AS $key => $val)
			$this->{$key}=$val; //setup parsed params

		$this->output='';
		$this->scriptsCode='$(document).ready(function(){selectGroup();});';
		$this->recipients=array();
		$this->setMailsLimit();
		$this->setMailCounter();
	}

	protected function setMailsLimit()
	{
		$this->mailsLimit=0;
		if(isset($_REQUEST['mLimit']))
		{
			$mLimit=(int) $_REQUEST['mLimit'];
			if($mLimit>0) $this->mailsLimit=$mLimit;
		}
	}
	protected function setMailCounter()
	{
		$this->mailCounter=0;
		if(isset($_REQUEST['mCnt']))
		{
			$mCnt=(int) $_REQUEST['mCnt'];
			if($mCnt>0) $this->mailCounter=$mCnt;
		}
	}
	protected function lang_l($lbl)
	{
		return isset($this->lang_l[$lbl]) ? $this->lang_l[$lbl] : $lbl;
	}
	protected function getSubmittedUsers()
	{
		if(isset($_REQUEST['submittedMails']) && $_REQUEST['submittedMails'] != '')
			$this->addRecipients(explode(',', $_REQUEST['submittedMails']));

		if(isset($_REQUEST['build_emails_list']) && is_array($_REQUEST['build_emails_list']) && count($_REQUEST['build_emails_list'])>0 )
			$this->addRecipients($_REQUEST['build_emails_list']);
	}
	protected function addRecipients($rcps)
	{
		if(!is_array($rcps) || empty($rcps))
			return;
		if($this->hasRecipients())
			$this->recipients=array_merge($this->recipients, $rcps);
		else
			$this->recipients=$rcps;
	}
	protected function hasRecipients()
	{
		return count($this->recipients) > 0;
	}
	protected function polishRecipients()
	{
		foreach($this->recipients as $rk => $rv)
		{
			$trimmed=trim($rv);
			if($trimmed == '')
			{
				unset ($this->recipients[$rk]);
				continue;
			}
			$this->recipients[$rk]=$trimmed;
		}
		$this->recipients=array_unique($this->recipients);
	}
	protected function generateFullRecipients()
	{
		global $db;

		$users_data=$db->fetch_all_array('SELECT * FROM '.$db->pre.'ca_users WHERE confirmed=1 ');
		foreach($users_data as $user)
			if(in_array($user['email'],$this->recipients))
				foreach($this->recipients as $rk=>$rv)
					if($rv == $user['email']) $this->recipients[$rk]=$user;
	}
	protected function prepareMailBody()
	{
		$mb='';
		if (isset($_REQUEST['mailBody']) && !empty($_REQUEST['mailBody']))
			$mb=$_REQUEST['mailBody'];

		$mb=$this->parseDataParams($mb);

		return $mb;
	}
	protected function prepareMailSubject()
	{
		$ms='no subject';
		if(isset($_REQUEST['mailSubj']) && !empty($_REQUEST['mailSubj']))
			$ms = Formatter::stripTags($_REQUEST['mailSubj']);

		$ms=$this->parseDataParams($ms);
		return $ms;
	}
	protected function prepareBCC()
	{
		$mBody=$this->prepareMailBody();
		if($this->f->mail_usebcc && !preg_match('/%[a-zA-Z]+%/',$mBody) && !preg_match('/%%[a-zA-Z]+%%/',$mBody))
				$this->send_to_bcc=true;
	}
	protected function sendEvent()
	{
		$this->getSubmittedUsers();
		if(!$this->hasRecipients())
		{
			$this->output=$this->lang_l('no recip');
			return;
		}
		$this->polishRecipients();
		$this->prepareBCC();
		$this->sendMail();
	}
	protected function prepareRecipientsForNextBulk()
	{//get current bulk and remove it from the entire set
		$currentBulkRecip=$this->recipients;
		if($this->mailsLimit>0)
		{
			$currentBulkRecip=array_slice($this->recipients,0,$this->mailsLimit);
			$this->recipients=array_slice($this->recipients,$this->mailsLimit);
		}
		else
			$this->recipients=array(); //all recipients grabbed, nothing left for further use
		return $this->getMailsFromIDs($currentBulkRecip);
	}
	protected function getMailsFromIDs($rcpnts)
	{
		global $db;
		$extra_mails=array();
		$ret_arr=array();
		foreach($rcpnts as $k=>$r) if(!is_numeric($r))
		{
			$extra_mails[]=$r;
			unset($rcpnts[$k]);
		}
		if(!empty($rcpnts))
		{
			$emails=$db->fetch_all_array('SELECT email FROM '.$db->pre.'ca_users WHERE uid IN ('
				.implode(',',$rcpnts).')');
			foreach($emails as $e)
				$ret_arr[]=$e['email'];
		}
		return array_merge($extra_mails,$ret_arr);
	}
	protected function buildNextBulk()
	{
		$this->output .= F_BR.F_BR.'<form action="'.  Linker::currentPageUrl().'" method="POST">';
		$this->output .= '<textarea name="mailBody" style="display: none;">'.$this->prepareMailBody().'</textarea>';
		$this->output .= '<input type="hidden" name="mailSubj" value="'.$this->prepareMailSubject().'">';
		$this->output .= '<input type="hidden" name="mCnt" value="'.($this->mailCounter+1).'">';
		$this->output .= '<input type="hidden" name="mLimit" value="'.($this->mailsLimit).'">';
		foreach($this->recipients as $recip)
		{
			if(is_array($recip))
				$this->output .= '<input type="hidden" name="build_emails_list[]" value="'.$recip['email'].'">';
			else
				$this->output .= '<input type="hidden" name="build_emails_list[]" value="'.$recip.'">';
		}
		$this->output .= '<input class="input1" type="submit" name="sendMails" value="Submit ('
		.(($this->mailCounter+1)*$this->mailsLimit).'-'.(($this->mailCounter+2) * $this->mailsLimit).')">';
		$this->output .= '</form>';
		return;
	}
	protected function buildMailForm()
	{
		$tbl_data=array();
		$this->output .= '<form action="'.  Linker::currentPageUrl().'" method="POST">';
		$tbl_data[]=array($this->lang_l('mail recipients'),$this->buildUsersSection());
		$tbl_data[]=array($this->lang_l('mail subject'),$this->buildSubjectSection());
		$tbl_data[]=array($this->lang_l('mail body'),$this->buildBodySection());
		$tbl_data[]=array($this->lang_l('send in bulks'),$this->buildBulkSection());
		$end='<input class="input1" onclick="markOption();" type="submit" name="sendMails" value="Submit">';
		$this->output .= str_replace('a_n a_listing','a_listing',Builder::addEntryTable($tbl_data,$end));
		$this->output .= '<script>
				$(document).ready(function(){selectGroup();});
				</script>';
		$this->output .= '</form>';
		return;
	}
	protected function buildBulkSection()
	{
		$output='<input type="text" value="0" class="input1" name="mLimit" style="width:50px;"></br>';
		return $output;
	}
	protected function buildUsersSection()
	{
		global $db;

		$all_users=User::mGetAllUsers($db,'confirmed=1',true);
		$groups_data=array('-'=>'');
		foreach($all_users as $usr)
		{
			$grp_name=$usr['group_name']==''?'--'.$this->lang_l('ungrouped').'--':$usr['group_name'];
			if(isset($groups_data[$grp_name])) $groups_data[$grp_name].=','.$usr['uid'];
			else $groups_data[$grp_name] =$usr['uid'];
		}
		$u_list='<span class="a_tabletitle">'.$this->lang_l('group').': </span>'.F_BR
			.Builder::buildSelect('usr_grps_select',$groups_data,'------','','swap');
		$u_list .= Builder::doubleSelector($all_users,$this->lang_l('full users list'),$this->lang_l('selected users'),
				'emails_select','build_emails_list',$this->predefinedRecipients);
		$u_list.='<br>
			 <div style="width:500px">
				<span class="a_tabletitle">'.$this->lang_l('additional emails').'</span><br>
				<textarea name="submittedMails" class="input1" style="width:510px;"></textarea>
			</div>';
		return $u_list;
	}
	protected function buildSubjectSection()
	{
		$output='<input type="text" value="'.$this->mailSubject.'" class="input1" name="mailSubj" style="width:510px;"><br>';
		return $output;
	}
	protected function buildBodySection()
	{
		$this->mailBody=Editor::replaceClassesEdit($this->mailBody);
		$editor_parsed=str_replace(array('oEdit1','htmlarea','450px'),
				array('oEdit1description','txtContentdescription','250px'),$this->innova_def);
		$output = '<textarea class="mceEditor" id="txtContentdescription" name="mailBody" style="width:510px;">'
				.$this->mailBody.'</textarea>'.$editor_parsed.F_BR;
		return $output;
	}
	protected function sendMail(){
		return;
	}

	protected function parseDataParams($tpl)
	{
		return $tpl;
	}
}

class BlogMailer extends Mailer
{
	public $page;

	public function __construct($settings,$pg)
	{
		$this->page=$pg;
		parent::__construct($settings);
	}

	final protected function Setup($settings)
	{
		parent::Setup($settings);
		$this->mailSubject=$this->parseDataParams($this->subjTemplate);
		$mData=$this->parseDataParams($this->bodyTemplate);
		$this->mailBody=Formatter::sth2($mData);
	}

	final protected function sendMail()
	{
		global $db;

		$send_to=$this->prepareRecipientsForNextBulk();
		if($this->send_to_bcc)
			$result=MailHandler::sendMailStat($db,$this->page_id,$this->from_email,$this->from_email,$this->prepareMailBody(),'',$this->prepareMailSubject(),$this->page_encoding,'','','','',array(),  implode (';', $send_to));
		else
		{
			foreach($send_to as $recp)
				$result=MailHandler::sendMailStat($db,$this->page_id,$recp,$this->from_email,$this->prepareMailBody(),'',$this->prepareMailSubject(),$this->page_encoding,'','','','',array());
		}

		if($result!=1)
			$this->output .= '<span class="rvts8">'.$result.'</span>';
		else
		{
			$this->output .= '<span class="rvts8">E-mails sent to:</span>'.F_BR;
			foreach($send_to as $recp)
				$this->output .= '<span class="rvts8">'.$recp.'</span>'.F_BR;
		}
		if(!empty($this->recipients))
			$this->buildNextBulk();
	}

	final protected function parseDataParams($tpl)
	{
		$has_permalink=strpos($tpl,'%permalink%')!==false;
		$content_excerpt_mode=strpos($tpl,'%content/')!==false;
		$excerpt_mode=$content_excerpt_mode	&& isset($this->mailData['excerpt'])
							&& $this->mailData['excerpt']!='';
		$limited_content_mode=strpos($tpl,'%content(')!==false;
		$must_add_permalink= $excerpt_mode || $limited_content_mode;
		$res=$tpl;
		if(!$has_permalink && $must_add_permalink)
			$res.='<br/> %permalink%';

		if($content_excerpt_mode)
		{
			$cntnt=isset($this->mailData['excerpt']) && $this->mailData['excerpt']!=''?$this->mailData['excerpt']:
				$this->mailData['content'];
			$res=str_replace('%content/excerpt%',$cntnt,$res);
		}
		if($limited_content_mode)
		{
			$cntnt_limit=Formatter::GFS($res,'%content(',')%');
			$res=str_replace(Formatter::GFSAbi($res,'%content(',')%'),substr($this->mailData['content'],0,$cntnt_limit).'...',$res);
		}

		$unpub_date=($this->mailData['unpublished_date']=='0000-00-00 00:00:00')?
						'Continuous':
						$this->page->format_dateSql($this->mailData['unpublished_date']);
		$datetime_value=$this->page->format_dateSql($this->mailData['creation_date']);

		$res=str_replace(array('%unpublish_date%','%datetime%'),
						array($unpub_date,$datetime_value),
						$res);

		foreach($this->mailData as $mdk=>$mdv)
			if(!is_array($mdv))
				$res=str_replace('%'.$mdk.'%',$mdv,$res);

		return $res;
	}

}

class CAMailer extends Mailer
{
	final protected function Setup($settings)
	{
		parent::Setup($settings);
		$this->mailSubject=$this->mailData['subject'];
		$this->mailBody=Formatter::sth2($this->mailData['content']);
	}

	final protected function parseDataParams($tpl)
	{
		return $tpl;
	}

	final protected function sendMail()
	{
		global $db;
		$this->generateFullRecipients();
		$send_to=$this->prepareRecipientsForNextBulk();
		$msg_user=$this->prepareMailBody();
		$msg_subject=$this->prepareMailSubject();
		if($this->send_to_bcc && strpos($msg_user.$msg_subject,'%')===false)
		{
			$mails_str='';
			foreach($send_to as $v)
				$mails_str.=','.$this->getMailValue($v);
			$mails_str =substr($mails_str,1); //remove first comma

			$this->output .=  F_BR.$this->lang_l('sending to').': '.F_BR.str_replace(',',F_BR,$mails_str);
			$res=MailHandler::sendMailCA(null,$msg_user,$msg_subject,'',$mails_str);

			$this->output .=  F_BR.' .....'.($res=='1'?$this->lang_l('sent'):$res);
		}
		else
		{
			foreach($send_to as $v)
			{
				$msg=$msg_user;
				$subject=$msg_subject;
				if(strpos($msg.$subject,'%')!==false)
				{
					$user=$db->query_first('
					 SELECT *
					 FROM '.$db->pre.'ca_users
					 WHERE email = "'.$v.'"');

					 if(!empty($user))
						  foreach($user as $f=>$fv)
						  {
								$msg=str_replace('%'.$f.'%',$fv,$msg);
								$subject=str_replace('%'.$f.'%',$fv,$subject);
						  }
				}
				$this->output .= F_BR.$this->lang_l('sending to').': '.$this->getMailValue($v);
				$res=MailHandler::sendMailCA(null,$msg,$subject,$this->getMailValue($v));

				$this->output .= ' .....'.($res=='1'?$this->lang_l('sent'):$res);
			}
		}
		if(!empty($this->recipients))
			$this->buildNextBulk();
	}

	final private function getMailValue($mail)
	{
		if(is_array($mail))
			return $mail['email'];
		return $mail;
	}
}
?>

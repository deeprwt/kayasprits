<?php
/*
	counter.php	ezgenerator counter
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/

class CounterHandlerClass
{
	public $version='ezgenerator v4 - counter 5.3.0.8 mysql';
	protected $c_max_visit_len=1800;
	protected $c_number_digits=8;
	protected $c_display=0;// 1- page loads; 0- unique
	protected $c_unique_start_count=0;
	protected $c_loads_start_count=0;
	protected $c_graphical='1';
	protected $action_id;
	protected $user;

	public function __construct($relpath,$from_outside)
	{
		global $f,$user;

		if($f instanceof FuncConfig)
			$this->f = $f;
		else
			die('Core not loaded! (12582)');
		$this->user=$user;
	}

	protected function returnCounterHtml($digits,$imaPath,$height,$width,$value)
	{
		$line='<div class="counter_image" style="padding:0;float:left;width:%spx;height:%spx;background:url(%s) %spx 0;"></div>';
		$result='<div style="width:'.($width*$digits).'px;height:'.$height.'px;display:inline-block">';
		$value_string=sprintf('%'.$digits.'d',$value);
		for($i=0;$i<($digits);$i++)
			$result.=sprintf($line,$width,$height,$imaPath,-($value_string[$i]*$width));
		$result.='</div>';
		return $result;
	}

	protected function return_counter($counts)
	{
		$number_of_digits=isset($this->f->ca_settings['c_number_digits'])? $this->f->ca_settings['c_number_digits']:$this->c_number_digits;
		$graphical=isset($this->f->ca_settings['c_graphical'])? $this->f->ca_settings['c_graphical']:$this->c_graphical;
		$direct=isset($_REQUEST['d']);

		if($graphical=='1')
		{
			$id=isset($this->f->ca_settings['c_size'])?$this->f->ca_settings['c_size']:'1';
			if($id=='')
				$id='1';
			$root=isset($_REQUEST['root']) && $_REQUEST['root']=='true';
			$root_http=isset($_REQUEST['root']) && $_REQUEST['root']=='http';
			$dir_pr=$root?'':($root_http?str_replace(array('documents/','counter.php'),'',Linker::buildSelfURL('counter.php')):'../');
			$ipath=$dir_pr.'ezg_data/c'.intval($id).'.gif';
			$wh=explode('|',$this->f->counter_images[$id-1]);
			$code=$this->returnCounterHtml(intval($number_of_digits),$ipath,intval($wh[1]),intval($wh[0]),$counts);
			echo $direct?$code:"document.write('".$code."');";
		}
		else
		{
			$digits_string='';
			$max_d=strlen($counts);
			if($number_of_digits>$max_d)
			{
				for($i=0;$i<($number_of_digits-$max_d);$i++)
					 $digits_string.='0';
			}
			$digits_string.=$counts;
			$digits_string='<span class="counter_text">'.$digits_string.'</span>';
			echo $direct?$digits_string:"\ndocument.write(' $digits_string ');\n";
		}
	}

	protected function updateCounterDB($p_id)
	{
		global $db;

		$timestamp=time();
		$firsttime_flag=$uniq_flag=false;
		$cookie_suffix=isset($this->f->ca_settings['c_cookie_suffix'])? $this->f->ca_settings['c_cookie_suffix']: '';

		$this->c_max_visit_len=isset($this->f->ca_settings['c_max_visit_len'])? $this->f->ca_settings['c_max_visit_len']:$this->c_max_visit_len;

		if(!isset($_COOKIE['u_mvl'.$cookie_suffix]))
		{
			setcookie('u_mvl'.$cookie_suffix, md5(uniqid(mt_rand(),true)),$timestamp+$this->c_max_visit_len);
			$uniq_flag=true;
		}
		if(!isset($_COOKIE['f_time'.$cookie_suffix]))
		{
			$expire_timestamp=mktime(23,59,59, date('n',$timestamp),date('j',$timestamp),2037);
			setcookie('f_time'.$cookie_suffix, md5(uniqid(mt_rand(),true)),$expire_timestamp);
			$firsttime_flag=true;
		}
		$stat_detailed=Builder::detailedStat($timestamp, $p_id, $uniq_flag, $firsttime_flag);
		$db->query_insert('counter_details',$stat_detailed);

		$page_total=$db->query_singlevalue('
			SELECT total
			FROM '.$db->pre.'counter_pageloads
			WHERE page_id='.$p_id);
		if(is_null($page_total))
		{
			$res=$db->query_insert('counter_pageloads',array("page_id"=>$p_id,"total"=>1,"eventcount"=>0),true);
			if($res===false)
				Counter::updateEventCount($db,$p_id);
		}
		else
			$db->query('
				UPDATE '.$db->pre.'counter_pageloads
				SET total=total + 1
				WHERE page_id='.$p_id);

		$vt=$stat_detailed['visit_type'];
		$que='
			UPDATE '.$db->pre.'counter_totals
			SET total=total + 1
			WHERE total_type in ("loads"'.($vt!='h'?',"unique"':'').($vt=='r'?',"returning"':'').($vt=='f'?',"first"':'').')';
		$db->query($que);
	}

	protected function getCounts()
	{
		global $db;

		$display=isset($this->f->ca_settings['c_display'])?$this->f->ca_settings['c_display']:$this->c_display;
		$counts=$db->query_singlevalue('
			 SELECT total
			 FROM '.$db->pre.'counter_totals
			 WHERE total_type="'.($display==1? 'loads': 'unique').'"',true);

		if($counts===NULL)
		{
		  $db->query_insert('counter_totals',array("total_type"=>"loads","total"=>0),true);
		  $db->query_insert('counter_totals',array("total_type"=>"unique","total"=>0),true);
		  $db->query_insert('counter_totals',array("total_type"=>"first","total"=>0),true);
		  $db->query_insert('counter_totals',array("total_type"=>"returning","total"=>0),true);
		}

		$loads_start_count=isset($this->f->ca_settings['c_loads_start_count'])? $this->f->ca_settings['c_loads_start_count']:$this->c_loads_start_count;
		$unique_start_count=isset($this->f->ca_settings['c_unique_start_count'])? $this->f->ca_settings['c_unique_start_count']:$this->c_unique_start_count;

		$counts+=intval($display==1?$loads_start_count:$unique_start_count);
		return $counts;
	}

	public function process()
	{
		global $db;

		$this->action_id=isset($_REQUEST['action'])? Formatter::stripTags($_REQUEST['action']):'hit';
		if($this->action_id=="version")
		{
			echo $this->version;
			exit;
		}

		$db=DB::dbInit($this->f->db_charset,($this->f->uni?$this->f->db_charset:''));
		if($db===false) exit;
		Counter::dbCheck($db);

		if($this->action_id=='handleHit') //handle hit via ajax
		{
			Counter::handleSearchSessionHit($db);
			return;
		}

		if(!isset($_REQUEST['pid']) || intval($_REQUEST['pid'])<1)
			exit;
		$p_id=intval($_REQUEST['pid']);

		if($this->action_id=="events")
		{
			CA::fetchDBSettings($db);

			$eventcount=$db->query_singlevalue('
				SELECT eventcount
				FROM '.$db->pre.'counter_pageloads
				WHERE page_id='.$p_id,true);
			$eventcount=(($eventcount===false)||is_null($eventcount))?0:$eventcount;
			$this->return_counter($eventcount);
			exit;
		}
		elseif($this->action_id=="hit")
		{
			CA::fetchDBSettings($db);

			Session::intStart();
			$username=($this->user->userCookie())?$this->user->getUserCookie():'';
			User::append($db,$username);

			if(!isset($_COOKIE['visit_from_admin']))
				$this->updateCounterDB($p_id);

			if(!(isset($_REQUEST['visible'])&& $_REQUEST['visible']==0)) //if counter visible
			{
				$counts=$this->getCounts();
				$this->return_counter($counts);
			}
		}
	}
}

include_once ('../ezg_data/functions.php');
include_once('../ezg_data/mysql.php');

$counter=new CounterHandlerClass('../',false);
$counter->process();

?>

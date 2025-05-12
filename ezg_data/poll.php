<?php
$version="ezgenerator poll 0.5";
/*
	poll.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2014 Image-line
*/

define('POLL_QUESTIONS_FIELD_COUNT',8);

include_once('functions.php');
include_once('mysql.php');

$ini=array();

$mysql_charset=($f->uni)?'utf8':'';
$db=DB::dbInit($mysql_charset,$mysql_charset);
if($db!==false)check_data();
else exit;

function check_data()
{
	global $db;

	$tb_a=$db->get_tables('poll');
	if(empty($tb_a))
	{
		include_once('data.php');
		create_polldb($db);
	}
	else
	{
		$field_names=$db->db_fieldnames('poll_questions');
		if(count($field_names)!=POLL_QUESTIONS_FIELD_COUNT)
	 	{
		  include_once('data.php');
		  create_polldb($db);
		}
	}
}

function update_question($poll_id,$addnew)
{
	global $db,$ini;

	if(isset($ini[$poll_id]))
	{
		if($addnew)
		{
			$data=array();
			$data['question']=$ini[$poll_id]['title'];
			$data['rev']=$ini[$poll_id]['rev'];
			$data['qid']=$poll_id;
			$data['created']='NOW()';
			$db->query_insert('poll_questions',$data);
		}
		else
		{
			$db->query('UPDATE '.$db->pre.'poll_questions SET question = "'.$ini[$poll_id]['title'].'" , rev = '.$ini[$poll_id]['rev'].' WHERE qid='.$poll_id);
			$db->query('DELETE FROM '.$db->pre.'poll_options WHERE qid='.$poll_id);
		}

		$answers=explode('|',$ini[$poll_id]['data']);
		$option_id=1;
		foreach($answers as $v)
		{
			$data=array();
			$data['qid']=$poll_id;
			$data['oid']=$option_id++;
			$data['value']=$v;
			$db->query_insert('poll_options',$data);
		}
	}
}

$poll_id=isset($_REQUEST['poll_id'])?intval($_REQUEST['poll_id']):1;
$poll_aid=isset($_REQUEST['poll_answer'])?intval($_REQUEST['poll_answer']):0;
$poll_langid=isset($_REQUEST['lang_id'])?array_search($_REQUEST['lang_id'],$f->inter_languages_a):0;
if(isset($ini[$poll_id]))$lang=explode('|',$ini[$poll_id]['lang']);
else $lang=array($f->lang_f[$poll_langid]['submit_btn'],$f->lang_f[$poll_langid]['votes'],$f->lang_f[$poll_langid]['total votes'],$f->lang_f[$poll_langid]['loading']);

if($poll_aid==0)
{
	$que='SELECT question,rev,template FROM '.$db->pre.'poll_questions WHERE qid = '.$poll_id;
	$data=$db->query_first($que);

	if(!is_array($data) || (isset($ini[$poll_id]) && $ini[$poll_id]['rev']!=$data['rev']))
	{
		update_question($poll_id,!is_array($data));
		$data=$db->query_first($que);
	}
	if($data!=null)
	{
		 echo '<!--'.($data['template']==''?($poll_id>5000?'template1':'poll_'.$poll_id):$data['template']).'-->';
		 echo '<h1 class="poll_title">'.$data['question'].'</h1>';
	}
	if((isset($_GET["result"]) && $_GET["result"]==1) || isset($_COOKIE["vt_".$poll_id])) //if already voted or asked for result
	{
		showresults($poll_id);
		exit;
	}
	else //display options with radio buttons
	{
		$data=$db->fetch_all_array('SELECT id,oid,value FROM '.$db->pre.'poll_options WHERE qid='.$poll_id.' ORDER BY oid');
		echo '<div id="formcontainer" ><form method="post" id="pollform" action="'.Linker::buildSelfURL('poll.php',true).'" >';
		echo '<input type="hidden" name="poll_id" value="'.$poll_id.'" />';
		foreach($data as $k=>$v)
		{
			echo '<p><input type="radio" name="poll_answer" value="'.$v['oid'].'" id="option-'.$v['oid'].'" />
			<label for="option-'.$v['oid'].'" >'.$v['value'].'</label></p>';
		}
		echo '<p><input class="poll_subm_btn" type="submit" value="'.$lang[0].'" /></p></form>';
	}
}
else
{
	if(!isset($_COOKIE["vt_".$poll_id]))
	{
		$data=$db->fetch_all_array('SELECT * FROM '.$db->pre.'poll_options WHERE oid='.$poll_aid);
		if(count($data)>0)
		{
			$query='INSERT INTO '.$db->pre.'poll_votes(qid,oid,voted_on,ip) VALUES('.$poll_id.','.$poll_aid.',"'.date('Y-m-d H:i:s').'","'.$_SERVER['REMOTE_ADDR'].'")';
			if($db->query($query))	setcookie("vt_".$poll_id,'yes',time()+86400*300);
		}
	}
	showresults($poll_id);
}

function showresults($qid)
{
	global $poll_aid,$db,$lang;
	$total=0;
	$data=$db->fetch_all_array(
	'SELECT options.oid, options.value, COUNT(*) as votes FROM '.$db->pre.'poll_votes AS votes, '.$db->pre.'poll_options AS options '
	.'WHERE options.qid='.$qid.' AND votes.oid=options.oid AND votes.qid='.$qid
	.' AND votes.oid IN(SELECT oid FROM '.$db->pre.'poll_options WHERE qid="'.$qid.'") GROUP BY votes.oid'
	);
	foreach($data as $k=>$v) $total+=$v['votes'];
	$i=1;
	foreach($data as $k=>$v)
	{
		$percent=round(($v['votes']*100)/$total);
		echo '<div class="option" ><p>'.$v['value'].' (<em>'.$percent.'%, '.$v['votes'].' '.$lang[1].'</em>)</p>';
		echo '<div class="bar bar'.$i++;
		if($poll_aid==$v['oid']) echo ' yourvote';
		echo '" rel="'.$percent.'" style="width: '.$percent.'%; " ></div></div>';
	}
	echo '<p>'.$lang[2].': '.$total.'</p>';
}

?>

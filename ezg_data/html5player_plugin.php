<?php
require_once 'functions.php';
define("MAX_TACKS",200);  //Max tracks per one mp3 player
define('HTML5PLAYER_IMAGES_FIELD_COUNT',7);
define('HTML5PLAYER_SETTINGS_FIELD_COUNT',9);
Session::intStart();

$page_id=isset($_REQUEST['pid'])?intval($_REQUEST['pid']):'';
$lang=isset($_REQUEST['lang'])?htmlentities($_REQUEST['lang']):'english';
$cbuilder=isset($_REQUEST['cbuilder']);
$html5player_path='html5player_plugin.php?lang='.$lang.'&pid='.$page_id.($cbuilder?'&cbuilder=true':'');
if(!User::mHasWriteAccess_outsite($page_id)) exit;


if(isset($_POST['edit_sid'])&&!$cbuilder)//editable html5player
{
	global $db;

	$sid=intval($_POST['edit_sid']);
	$tb_a=$db->get_tables('html5player');
	if(empty($tb_a)){
	    print json_encode(array('error')); 
	    exit;
	}
	$data=$db->fetch_all_array('
	    SELECT *
	    FROM '.$db->pre.'html5player_settings as s
	    LEFT JOIN '.$db->pre.'html5player_tracks as i ON s.id=i.player_id
	    WHERE s.id='.$sid.'
	    ORDER BY i.id ASC');
	if(!empty($data))
	{
		$track_url=$img_url='';
		$captions=$titles=$hrefs=array();
		$str_settings=$data[0]['settings'];
		
		$count_data=count($data);
		$i=0;
		foreach($data as $v){
			$track_url.=$v['track_url'];
			$img_url.=$v['img_url'];
			$titles[]=$v['title'];
			$captions[]=$v['caption'];
			if(++$i!==$count_data){
				$track_url.='|';
				$img_url.='|';
			}				
		}
		print json_encode(array(
		    $str_settings,
			$track_url,
			$img_url,
		    json_encode($titles),
		    json_encode($captions)
		));
	}
	else
	    print json_encode(array('error'));
	exit;
}

$html5player=new Slideshow();
$html5player->set_calang_file();
$sc_browse=Builder::getBrowseDialog('','../',$lang,true,true);


if(isset($_POST['save_html5player'])&&$page_id!=''&&!$cbuilder)
{
	global $db;
	$tb_a=$db->get_tables('html5player');

	$location='Location: '.$html5player_path;
	$track_urls=isset($_POST['track_url'])?array_map('Formatter::stripTags',$_POST['track_url']):array();
	$track_imgs=isset($_POST['track_img_url'])?array_map('Formatter::stripTags',$_POST['track_img_url']):array();
	$track_titles=isset($_POST['track_title'])?array_map('Formatter::stripTags',$_POST['track_title']):array();
	$track_captions=isset($_POST['track_caption'])?array_map('Formatter::stripTags',$_POST['track_caption']):array();
	
	$error='&error='.$html5player->lang_s('Missing information!');
	$count_tracks=count($track_urls);
	
	if($count_tracks==0&&isset($_POST['player_id'])&&!empty($tb_a))
	{
		$player_id=intval($_POST['player_id']);
		$res1=$db->query('DELETE FROM '.$db->pre.'html5player_settings WHERE id='.$player_id);
		$res2=$db->query('DELETE FROM '.$db->pre.'html5player_tracks WHERE player_id='.$player_id);
		if($res1!==false&&$res2!==false)
			header($location.'&delete=ok');
		else
			header($location.'&error='.$html5player->lang_s("Can't delete player!"));
		exit;
	}
	//var_dump('count_tracks='.$count_tracks.' '.' track_captions='.count($track_captions).' track_imgs='.count($track_imgs).' $track_titles='.count($track_titles));
	//exit;
	if($count_tracks==0 || $count_tracks>MAX_TACKS || $count_tracks!==count($track_titles) ||
	$count_tracks!==count($track_captions) || $count_tracks!==count($track_imgs)){ 
		header($location.$error);
		exit;
	}
	//html5player info	
	$f_settings=$html5player->html5player_settings;
	$post_settings=isset($_POST['html5player_settings'])?$_POST['html5player_settings']:array();
	if(count($post_settings)!=count($f_settings)){
		header($location.$error);
		exit;
	}
	$html5player_settings=implode('|',array_map('floatval',$post_settings));//.......
	
	if(empty($tb_a))
	{
		include_once('data.php');
		create_html5playerdb($db);
	}else{
		$field_names_tracks=$db->db_fieldnames('html5player_tracks');
		$field_names_settings=$db->db_fieldnames('html5player_settings');
		if(count($field_names_tracks)!=HTML5PLAYER_IMAGES_FIELD_COUNT || count($field_names_settings)!=HTML5PLAYER_SETTINGS_FIELD_COUNT)
	 	{
			include_once('data.php');
			create_html5playerdb($db);
		}
	}
	$records=array(
		'settings'=>$html5player_settings,
		'date'=>'NOW()',
		'date_modify'=>'NOW()',
		'ip'=>Detector::getIP(),
		'pid'=>$page_id
	);
	if(isset($_POST['player_id'])){
		$player_id=intval($_POST['player_id']);
		unset($records['date']);
		$result=$db->query_update('html5player_settings',$records,'id = '.$player_id);
		if($result!==false) { $last_id=$player_id; $edit=true;}
	}else $last_id=$db->query_insert('html5player_settings',$records);
		
	if($last_id!==false)
	{
		$track_id=false;
		if(isset($edit))
			$result_del=$db->query('DELETE FROM '.$db->pre.'html5player_tracks WHERE player_id='.$last_id);
		if(!isset($result_del) || $result_del!==false)
		{
			for($i=0;$i<$count_tracks;$i++)
			{
				$records=array(
					'player_id'=>$last_id,
					'title'=>$track_titles[$i],
					'track_url'=>$track_urls[$i],
					'img_url'=>$track_imgs[$i],
					'caption'=>$track_captions[$i]
				);
				$track_id=$db->query_insert('html5player_tracks',$records);
				if($track_id===false) break;
			}
		}
		if($track_id!==false){
			if(isset($edit)) header($location.'&edit=ok');
			else header($location.'&insert='.urlencode('{%HTML5PLAYER_ID('.$last_id.')%}'));
			exit;
		}
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="../jquery_utils.js"></script>
<script type="text/javascript" src="html5player_js.js"></script>
<?=$f->tiny?'':'<script type="text/javascript" src="../innovaeditor/scripts/innovaeditor.js"></script>'?>
<script type="text/javascript" src="musicmetadata.js"></script>
<link rel="stylesheet" type="text/css" href="style_slideshow_plugin.css">
<link rel="stylesheet" type="text/css" href="simple_player.css">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<?='<script type="text/javascript">'.
$sc_browse.
Slideshow::get_js_var($html5player->html5player_settings,'html5player_').
'var max_tracks='.MAX_TACKS.';'.
'var title_last="'.$html5player->lang_s('Make last').'";'.
'var title_first="'.$html5player->lang_s('Make first').'";'.
'var max_tracks_text="'.$html5player->lang_s('exceeds maximum 200 tracks').'";'.
'var page_id='.$page_id.';'.
'var editor_type="'.($cbuilder?'cbuilder':($f->tiny?'tiny':'innova')).'";'.
'var error_text="'.$html5player->lang_s('Missing information!').'";'.
'</script>'
?>
<script type="text/javascript">
function modalDialogShow(url,width,height){
	mDialogShow(url,width,height);
}
</script>
</head>
<body>
	<div class="slideshow_plugin_wraparound">	
		<form action="<?=$html5player_path?>" method="post" style="margin:0">
			<div class="thumbs_wraparound">
				<input class="input1" type="button" name="btnAssepage-title" id="btnAsset2" onclick="openAsset('html5player')" value="<?=$html5player->lang_s('browse')?>"/>
				<table>
					<tr>
						<td class="arrows_container">
							<a class="arrows_slides up_arrow" rel="" onclick="move_slide(this,'move_up')" title="<?=$html5player->lang_s('Move up')?>"><i class="fa fa-arrow-up"></i></a>
							<a class="arrows_slides down_arrow" rel="" onclick="move_slide(this,'move_down')" title="<?=$html5player->lang_s('Move down')?>"><i class="fa fa-arrow-down"></i></a>
							<a class="arrows_slides first_arrow"  rel="" onclick="move_slide(this,'move_first')" title=""><i class="fa fa-fast-backward fa-rotate-90"></i></a>
							<a class="arrows_slides last_arrow"  rel="" onclick="move_slide(this,'move_last')" title=""><i class="fa fa-fast-backward fa-rotate-270"></i></a>
							<a class="del_btn"  rel="" onclick="delete_all_slides()" title="<?=$html5player->lang_s('Remove all')?>"><i class="fa fa-trash-o"></i></a>
							<input type="hidden" id="del_confurm" value="<?=$html5player->lang_s("Remove all?")?>" />
						</td>
					</tr>
					<tr>
						<td>
							<div id="thumbs_html5player" class="thumbs_container"></div>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="slideshow_info_wraparound">
				<div class="tabs">
					<ul class="tab-links">
						<li class="active"><a href="#tab1"><?=$html5player->lang_s('Track info')?></a></li>
						<li onclick="build_html5player()"><a href="#tab2"><?=$html5player->lang_s('Preview')?></a></li>
					</ul>
					<div class="tab-content">
						<div id="tab1" class="tab active">
							<div id="image_url_container"></div>
							<div id="track_url_container"></div>
							<table>
								<tr>
									<td>
										<span><?=$html5player->lang_s("title")?>:</span></br>
										<input type='text'  value='' class="image_info image_title" rel="" />
										<div id="track_title_container"></div>
									</td>
								</tr>
								<tr>
									<td>
										<span><?=$html5player->lang_s("description")?>:</span></br>
										<textarea value='' class="image_info image_caption" rel=""></textarea><br/>
										<div id="track_caption_container"></div>
									</td>
								</tr>
							</table>
						</div>
						
						<div id="tab2" class="tab">
							<table>
								<tr>
									<td colspan='4' style="height: 355px; vertical-align: top; padding:5px"><div id="html5player_preview"></div></td>
								</tr>
								<tr class="tr_settings tr_html5player_settings" >
									<td colspan='4' style="padding-top:10px">
									<table style="width:auto"><tr>
										<?=$html5player->buildSlideshow_settings_html($html5player->html5player_settings,'html5player_');?>
									</tr></table>
									</td>
								</tr>														
							</table>
						</div>
					</div>
				</div>
			</div>
			
			<div class="submit">
				<table>
					<tr>
						<td style="text-align:left">
							<?=$html5player->lang_s('Total tacks: ')?><span class="total_tracks"></span>
						</td>
						<td class="results" style="text-align:center">
							<?php
								if(isset($_GET['insert'])){
									$macro=urldecode($_GET['insert']);
									echo '<script type="text/javascript">
									insert_html5player_editor("<p class=\'html5player_editor\'>'.$macro.'</p>");
									</script>';
								}
								elseif(isset($_GET['edit'])){
									echo '<script type="text/javascript">									
									insert_html5player_editor("");
									</script>';
								}
								elseif(isset($_GET['delete'])){
									echo '<script type="text/javascript">
									delete_selected_slideshow();
									</script>';
								}
								elseif(isset($_GET['error']))
									echo '<span class="error">'.htmlentities($_GET['error']).'</span>';
							?>
						</td>
						<td style="text-align:right">
							<input type="<?=$cbuilder?'button" class="cbuilder_submit"':'submit"'?> value="<?=$html5player->lang_s('Save and Insert')?>" name="save_html5player" />
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</body>
</html>
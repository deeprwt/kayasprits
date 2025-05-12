<?php
require_once 'functions.php';
define("MAX_IMAGES",200);  //Max images per one slideshow
define('SlIDESHOW_IMAGES_FIELD_COUNT',7);
define('SlIDESHOW_SETTINGS_FIELD_COUNT',9);
Session::intStart();

$page_id=isset($_REQUEST['pid'])?intval($_REQUEST['pid']):'';
$lang=isset($_REQUEST['lang'])?htmlentities($_REQUEST['lang']):'english';
$cbuilder=isset($_REQUEST['cbuilder']);
$slideshow_path='slideshow_plugin.php?lang='.$lang.'&pid='.$page_id.($cbuilder?'&cbuilder=true':'');
if(!User::mHasWriteAccess_outsite($page_id)) exit;

if(isset($_POST['edit_sid'])&&!$cbuilder)//editable slideshow
{
	global $db;

	$sid=intval($_POST['edit_sid']);
	$tb_a=$db->get_tables('slideshow');
	if(empty($tb_a)){
	    print json_encode(array('error')); 
	    exit;
	}
	$data=$db->fetch_all_array('
	    SELECT *
	    FROM '.$db->pre.'slideshow_settings as s
	    LEFT JOIN '.$db->pre.'slideshow_images as i ON s.id=i.sid
	    WHERE s.id='.$sid.'
	    ORDER BY i.id ASC');
	if(!empty($data))
	{
		$str_urls='';
		$captions=$titles=$hrefs=array();
		$str_type=$data[0]['type'];
		$str_settings=$data[0]['settings'];
		$str_url_target=$data[0]['url_target'];
		$global_view=$data[0]['global_view'];
		$thumbs_size=$data[0]['thumbs_size'];
		
		$count_data=count($data);
		$i=0;
		foreach($data as $v){
			$titles[]=$v['title'];
			$hrefs[]=$v['href'];
			$captions[]=$v['caption'];
			$str_urls.=$v['url'];
			if(++$i!==$count_data) $str_urls.=',';				
		}
		print json_encode(array(
		    $str_type,
		    $str_settings,
		    $str_url_target,
		    json_encode($titles),
		    json_encode($hrefs),
		    $str_urls,
		    json_encode($captions),
			$global_view,
			$thumbs_size
		));
	}
	else
	    print json_encode(array('error'));
	exit;
}

$slideshow=new Slideshow();
$slideshow->set_calang_file();

if(isset($_POST['save_slideshow'])&&$page_id!=''&&!$cbuilder)
{
	global $db;
	$tb_a=$db->get_tables('slideshow');

	$location='Location: '.$slideshow_path;
	$slide_urls=isset($_POST['slide_url'])?$_POST['slide_url']:array();
	$target_url=isset($_POST['url_target'])?intval($_POST['url_target']):'';
	$slide_hrefs=isset($_POST['slide_href'])?$_POST['slide_href']:array();
	$slide_titles=isset($_POST['slide_title'])?$_POST['slide_title']:array();
	$slide_captions=isset($_POST['slide_caption'])?$_POST['slide_caption']:array();
	$type=isset($_POST['slideshow_type'])?intval($_POST['slideshow_type']):'';
	$global_view=isset($_POST['global_view'])?intval($_POST['global_view']):0;
	$thumbs_size=isset($_POST['thumbs_size'])?intval($_POST['thumbs_size']):0;
	
	$error='&error='.$slideshow->lang_s('Missing information!');
	$count_images=count($slide_urls);
	
	if($count_images==0&&isset($_POST['sid'])&&!empty($tb_a))
	{
		$sid=intval($_POST['sid']);
		$res1=$db->query('DELETE FROM '.$db->pre.'slideshow_settings WHERE id='.$sid);
		$res2=$db->query('DELETE FROM '.$db->pre.'slideshow_images WHERE sid='.$sid);
		if($res1!==false&&$res2!==false)
			header($location.'&delete=ok');
		else
			header($location.'&error='.$slideshow->lang_s("Can't delete slideshow!"));
		exit;
	}

	if($count_images==0 || $count_images>MAX_IMAGES || $count_images!==count($slide_titles) || 
	$count_images!==count($slide_hrefs) || $count_images!==count($slide_captions) ){ 
		header($location.$error);
		exit;
	}
	//slideshow info	
	$slideshow_settings='';
	if($type===0||$type===1) //slideshow nivoSlider
	{
		$f_settings=$slideshow->nivoSlider_settings;
		$post_settings=isset($_POST['nivoSlider_settings'])?$_POST['nivoSlider_settings']:array();
		if(count($post_settings)!=count($f_settings)){
			header($location.$error);
			exit;
		}
		$slideshow_settings=implode('|',array_map('floatval',$post_settings));//[0]=>effect,[1]=>slice,[2]=>boxCols,[3]=>boxRows,[4]=>animSpeed,[5]=>pauseTime
	}
	elseif($type===2||$type===3) //multibox slideshow
	{
		if($f->nivo_box){
			$f_settings=$slideshow->nivoLightbox_settings;		
			$post_settings=isset($_POST['nivoLightbox_settings'])?$_POST['nivoLightbox_settings']:array();
		}else{
			$f_settings=$slideshow->fancybox_settings;		
			$post_settings=isset($_POST['fancybox_settings'])?$_POST['fancybox_settings']:array();;
		}
		
		if(count($post_settings)!=count($f_settings)){
			header($location.$error);
			exit;
		}
		$slideshow_settings=implode('|',array_map('floatval',$post_settings));
	}
	elseif($type===4){ //slideshow2 type
		$f_settings=$slideshow->slideshow2_settings;
		$post_settings=isset($_POST['slideshow2_settings'])?$_POST['slideshow2_settings']:array();
		if(count($post_settings)!=count($f_settings)){
			header($location.$error);
			exit;
		}
		$slideshow_settings=implode('|',array_map('floatval',$post_settings));//[0]=>slideShowSpeed,[1]=>swipeSpeed
	}
	if($slideshow_settings==''){
		header($location.$error);
		exit;
	}
	
	if(empty($tb_a))
	{
		include_once('data.php');
		create_slideshowdb($db);
	}else{
		$field_names_images=$db->db_fieldnames('slideshow_images');
		$field_names_settings=$db->db_fieldnames('slideshow_settings');
		if(count($field_names_images)!=SlIDESHOW_IMAGES_FIELD_COUNT || count($field_names_settings)!=SlIDESHOW_SETTINGS_FIELD_COUNT)
	 	{
			include_once('data.php');
			create_slideshowdb($db);
		}
	}
	$records=array(
		'type'=>$type,
		'settings'=>$slideshow_settings,
		'date'=>'NOW()',
		'date_modify'=>'NOW()',
		'ip'=>Detector::getIP(),
		'pid'=>$page_id,
		'global_view'=>$global_view,
		'thumbs_size'=>$thumbs_size
	);
	if(isset($_POST['sid'])){
		$sid=intval($_POST['sid']);
		unset($records['date']);
		$result=$db->query_update('slideshow_settings',$records,'id = '.$sid);
		if($result!==false) { $last_id=$sid; $edit=true;}
	}else $last_id=$db->query_insert('slideshow_settings',$records);
		
	if($last_id!==false)
	{
		$image_id=false;
		if(isset($edit))
			$result_del=$db->query('DELETE FROM '.$db->pre.'slideshow_images WHERE sid='.$last_id);
		if(!isset($result_del) || $result_del!==false)
		{
			for($i=0;$i<$count_images;$i++)
			{
				$records=array(
					'sid'=>$last_id,
					'title'=>$slide_titles[$i],
					'href'=>str_replace(array("http://","https://"),'',$slide_hrefs[$i]),
					'url'=>$slide_urls[$i],
					'caption'=>$slide_captions[$i],
					'url_target'=>$target_url
				);
				$image_id=$db->query_insert('slideshow_images',$records);
				if($image_id===false) break;
			}
		}
		if($image_id!==false){
			if(isset($edit)) header($location.'&edit=ok');
			else header($location.'&insert='.urlencode('{%SLIDESHOW_ID('.$last_id.')%}'));
			exit;
		}
	}
}

$sc_browse=Builder::getBrowseDialog('','../',$lang,true,true);
$sc=$f->nivo_box?'nivo-lightbox':'fancybox';
$box=$f->nivo_box?'nivoLightbox':'multibox';
$sc_nivo='<script type="text/javascript" src="../extimages/scripts/'.$sc.'.js"></script>
<link rel="stylesheet" type="text/css" href="../extimages/scripts/'.$sc.'.css" media="screen" />';
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="../jquery_utils.js"></script>
<script src="jquery_slideshow_plugin.js"></script>
<?=$f->tiny?'':'<script type="text/javascript" src="../innovaeditor/scripts/innovaeditor.js"></script>'?>
<?=$sc_nivo?>
<script src="../extimages/scripts/slideshow2.js" type="text/javascript"></script>
<link type="text/css" href="../extimages/scripts/slideshow2.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="style_slideshow_plugin.css">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<?='<script type="text/javascript">'.
$sc_browse.
Slideshow::get_js_var($slideshow->nivoSlider_settings,'nivoSlider_').
Slideshow::get_js_var(($f->nivo_box?$slideshow->nivoLightbox_settings:$slideshow->fancybox_settings),($f->nivo_box?'nivoLightbox_':'fancybox_')).
Slideshow::get_js_var($slideshow->slideshow2_settings,'slideshow2_').
Slideshow::get_js_var($slideshow->url_target_types,'url_').
'var max_images='.MAX_IMAGES.';'.
'var nivo_box=parseInt("'.$f->nivo_box.'");'.
'var title_last="'.$slideshow->lang_s('Make last').'";'.
'var title_first="'.$slideshow->lang_s('Make first').'";'.
'var max_images_text="'.$slideshow->lang_s('exceeds maximum 200 images').'";'.
'var page_id='.$page_id.';'.
'var editor_type="'.($cbuilder?'cbuilder':($f->tiny?'tiny':'innova')).'";'.
'var error_text="'.$slideshow->lang_s('Missing information!').'";'.
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
		<form action="<?=$slideshow_path?>" method="post" style="margin:0">
			<div class="thumbs_wraparound">
				<input class="input1" type="button" name="btnAssepage-title" id="btnAsset2" onclick="openAsset('slideshow')" value="<?=$slideshow->lang_s('browse')?>"/>
				<table>
					<tr>
						<td class="arrows_container">
							<a class="arrows_slides up_arrow" rel="" onclick="move_slide(this,'move_up')" title="<?=$slideshow->lang_s('Move up')?>"><i class="fa fa-arrow-up"></i></a>
							<a class="arrows_slides down_arrow" rel="" onclick="move_slide(this,'move_down')" title="<?=$slideshow->lang_s('Move down')?>"><i class="fa fa-arrow-down"></i></a>
							<a class="arrows_slides first_arrow"  rel="" onclick="move_slide(this,'move_first')" title=""><i class="fa fa-fast-backward fa-rotate-90"></i></a>
							<a class="arrows_slides last_arrow"  rel="" onclick="move_slide(this,'move_last')" title=""><i class="fa fa-fast-backward fa-rotate-270"></i></a>
							<a class="del_btn"  rel="" onclick="delete_all_slides()" title="<?=$slideshow->lang_s('Remove all')?>"><i class="fa fa-trash-o"></i></a>
							<input type="hidden" id="del_confurm" value="<?=$slideshow->lang_s("Remove all?")?>" />
						</td>
					</tr>
					<tr>
						<td>
							<div id="thumbs_slideshow" class="thumbs_container"></div>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="slideshow_info_wraparound">
				<div class="tabs">
					<ul class="tab-links">
						<li class="active"><a href="#tab1"><?=$slideshow->lang_s('Image')?></a></li>
						<li><a href="#tab2"><?=$slideshow->lang_s('Preview')?></a></li>
					</ul>
					<div class="tab-content">
						<div id="tab1" class="tab active">
							<table>
								<tr>
									<td><div id="big_image"></div></td>
								</tr>
								<tr>
									<td class="top_input">
										<span><?=$slideshow->lang_s("title")?>:</span></br>
										<input type='text'  value='' class="image_info image_title" rel="" />
										<div id="images_title_container"></div>
									</td>
								</tr>
								<tr>
									<td>
										<span><?=$slideshow->lang_s("URL")?>:</span></br>
										<input type='text' value='' class="image_info image_href" rel="" /><br/>
										<div id="image_href_container"></div>
									</td>
								</tr>
								<tr>
									<td>
										<span><?=$slideshow->lang_s("description")?>:</span></br>
										<textarea value='' class="image_info image_caption" rel=""></textarea><br/>
										<div id="image_caption_container"></div>
									</td>
								</tr>
							</table>
						</div>
						
						<div id="tab2" class="tab">
							<table>
								<tr>
									<td colspan='4' style="height: 355px; vertical-align: top; padding:5px"><div id="slideshow_preview"></div></td>
								</tr>

								<tr class="tr_slideshow_type">
									<td class="top_input" style="width:20%">
										<span><?=$slideshow->lang_s('slideshow types')?>:</span><br/>
										<?=Builder::buildSelect2('slideshow_type','slideshow_type',$slideshow->slideshow_types,1,'','key');?>
									</td>
									<td class="top_input" style="width:13%">
										<span><?=$slideshow->lang_s('target')?>:</span><br/>
										<?=Builder::buildSelect2('url_target','url_target',$slideshow->url_target_types['target'],0,'','key');?>
									</td>
									<td class="top_input" style="width:20%">
										<?=Builder::buildCheckbox('global_view',false,$slideshow->lang_s('global view'),'','global_view');?>
									</td>
									<td class="top_input" style="width:43%">
										<div class="thumbs_size_div" style="opacity:0;">
											<span><?=$slideshow->lang_s('all thumbs same size')?>:</span><br/>
											<?=Builder::buildInput('thumbs_size','0','width:40px;','','text','','','','',false,'thumbs_size','thumbs_size');?>
										</div>
									</td>
								</tr>
								
								<tr class="tr_settings type_nivoSlider" style="display:none">
									<td  colspan='4' style="padding-top:10px">
										<table><tr>
											<?=$slideshow->buildSlideshow_settings_html($slideshow->nivoSlider_settings, 'nivoSlider_');?>
										</tr></table>
									</td>
								</tr>
								
								<tr class="tr_settings type_fancybox" style="display:none">
									<td colspan='4' style="padding-top:10px">
									<table><tr>
										<?=$slideshow->buildSlideshow_settings_html($f->nivo_box?$slideshow->nivoLightbox_settings:$slideshow->fancybox_settings,$f->nivo_box?'nivoLightbox_':'fancybox_');?>
									</tr></table>
									</td>
								</tr>

								<tr class="tr_settings type_slideshow2" style="display:none">
									<td colspan='4' style="padding-top:10px">
									<table><tr>
										<?=$slideshow->buildSlideshow_settings_html($slideshow->slideshow2_settings, 'slideshow2_');?>
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
							<?=$slideshow->lang_s('Total images: ')?><span class="total_images"></span>
						</td>
						<td class="results" style="text-align:center">
							<?php
								if(isset($_GET['insert'])){
									$macro=urldecode($_GET['insert']);
									echo '<script type="text/javascript">
									insert_slideshow_editor("<p class=\'slideshow_editor\'>'.$macro.'</p>");
									</script>';
								}
								elseif(isset($_GET['edit'])){
									echo '<script type="text/javascript">									
									insert_slideshow_editor("");
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
							<input type="<?=$cbuilder?'button" class="cbuilder_submit"':'submit"'?> value="<?=$slideshow->lang_s('Save and Insert')?>" name="save_slideshow" />
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</body>
</html>
/*!
 * jQuery Slideshow Plugin for ezg 1.0.4
 *
 */
var hrefEnabled_types=new Array('0','1');  //sldieshow, slideshow thumbnails
var captionEnabled_types=new Array('0','1','4');  //sldieshow, slideshow thumbnails, slideshow II
var targetEnabled_types=new Array('0','1');  //sldieshow, slideshow thumbnails
var all_imgs_array=[];
function setAssetValue(val,id,titles,hrefs,captions,edit_cbuilder)
{
	titles = typeof titles !== 'undefined' ? titles : [];
	hrefs = typeof hrefs !== 'undefined' ? hrefs : [];
	captions = typeof captions !== 'undefined' ? captions : [];
	edit_cbuilder = typeof edit_cbuilder !== 'undefined' ? edit_cbuilder : false;
	if(!$.isArray(val)) val=val.split();
	$.each(val,function(i,c) 
	{
		if(total_images()>=max_images){alert(max_images_text);return;}
		var image_url_thumb='';
		if(edit_cbuilder){
			c=parent.resolveImage_cbuilder(c,'');
			if(c.indexOf('innovaeditor/')!=-1){
				c='../'+c;
			}
		}
		if(c.indexOf('innovaeditor/')!=-1){
			var	position=c.lastIndexOf("/")+1;
			image_url_thumb=[c.slice(0, position), 'thumbs/', c.slice(position)].join('');
		}
		else{
			c='../'+c;
			image_url_thumb=c;
		}
		var date_obj=new Date();
		var gtime=date_obj.getTime()+i;
		var image_name=get_fname_from_url(c);
		//thumbs
		$("#thumbs_slideshow").append($(
		"<div class='thumb_images_container mouseout_image' id='thumb_"+gtime+"' onclick=\"focus_this_image(this, '"+c+"', '"+gtime+"')\">"+
		"<input type=hidden name='slide_url[]' value='"+c+"'/>"+
		"<div class='img_thumb slideshowplugin_img' style='background-image:url(\""+image_url_thumb+"\"); width:95px; height:95px'></div>"+
		"<div id='del_"+gtime+"' class='delete_slide mouseout_delbtn'><i class='fa fa-times'></i></div></div>"
		));
		
		//images title
		$("#images_title_container").append($(
		"<input type='hidden'  name='slide_title[]' value='"+(titles.length>0?titles[i]:image_name)+"' id='title_"+gtime+"' />"
		));
		
		//images href
		$("#image_href_container").append($(
		"<input type='hidden'  name='slide_href[]' value='"+(hrefs.length>0?hrefs[i]:'')+"' id='href_"+gtime+"' />"
		));
		
		//images caption
		$("#image_caption_container").append($(
		"<input type='hidden'  name='slide_caption[]' value='"+(captions.length>0?captions[i]:'')+"' id='caption_"+gtime+"' />"
		));
			
		$("#thumb_"+gtime).hover(
		  function() {
			$("#del_"+gtime).removeClass("mouseout_delbtn").addClass("mouseover_delbtn");
			$(this).removeClass("mouseout_image").addClass("mouseover_image");
		  }, function() {
			$("#del_"+gtime).removeClass("mouseover_delbtn").addClass("mouseout_delbtn");
			$(this).removeClass("mouseover_image").addClass("mouseout_image");
		  }
		);
		$("#del_"+gtime).click(
			function(){
				if($(this).parent().hasClass('clicked_img_thumb')) {
					$(".img_big").remove();
					$(".image_title").val('');
					$(".image_href").val('');
					$(".image_caption").val('');
				}
				$(this).parent().remove();
				$("#title_"+gtime).remove();
				$("#href_"+gtime).remove();
				$("#caption_"+gtime).remove();

				change_slideshow(get_slideshow_type());
				set_total_images();
			}
		);
		if($(".thumb_images_container").length==1){
			focus_this_image($("#thumb_"+gtime), c, gtime);
		}
	});
	
	change_slideshow(get_slideshow_type());
	
	if($('.error').length>0) $('.error').remove();
	set_total_images();
	var count_images=total_images();
	if(count_images>50&&count_images<=100){
		$('.last_arrow').attr('title','+10');
		$('.first_arrow').attr('title','-10');
	}else if(count_images>100){
		$('.last_arrow').attr('title','+20');
		$('.first_arrow').attr('title','-20');
	}else{
		$('.last_arrow').attr('title',title_last);
		$('.first_arrow').attr('title',title_first);
	}
}

function delete_all_slides(ans)
{
	$(document).ready(function()
	{
		if($(".thumb_images_container").length>0)
		{
			var answer = typeof ans !== 'undefined' ? ans : confirm($("#del_confurm").val());
			if (answer)
			{
				$("#thumbs_slideshow").empty();
				$(".img_big").remove();
				$("#images_title_container").empty();
				$("#image_href_container").empty();
				$("#image_caption_container").empty();
				$(".image_title").val('');
				$(".image_href").val('');
				$(".image_caption").val('');
				$("#slideshow_preview").empty();
				set_total_images();
			}
			else return false;			
		}
	});
}

function scrollto_slide(thumb_id){
	if($("#"+thumb_id).length==0)return;
	var relativeY=$(".thumbs_container").scrollTop() - $(".thumbs_container").offset().top + $("#"+thumb_id).offset().top;
	$('.thumbs_container').animate({
        scrollTop: relativeY
    }, 500);
}

function move_slide(t,action)
{
	var thumb_id=$(t).attr("rel");
	if(thumb_id==''||$("#"+thumb_id).length==0) return;
	thumb_id_num=thumb_id.replace('thumb_','');
	if($("#href_"+thumb_id_num).length==0||$("#title_"+thumb_id_num).length==0||$("#caption_"+thumb_id_num).length==0) return;
	var $type=get_slideshow_type();
	
	switch(action){
	case "move_up":
		var prev=$("#"+thumb_id).prev();
		if(prev.length==0||prev.attr("id").indexOf("thumb_")<0)return;
		$("#"+thumb_id).insertBefore(prev);
		
		$("#href_"+thumb_id_num).insertBefore($("#href_"+thumb_id_num).prev());
		$("#title_"+thumb_id_num).insertBefore($("#title_"+thumb_id_num).prev());
		$("#caption_"+thumb_id_num).insertBefore($("#caption_"+thumb_id_num).prev());
		
		scrollto_slide(thumb_id);
		change_slideshow($type);
		break;
	case "move_down":
		var next = $("#"+thumb_id).next();
		if(next.length==0||next.attr("id").indexOf("thumb_")<0)return;
		$("#"+thumb_id).insertAfter(next);
		
		$("#href_"+thumb_id_num).insertAfter($("#href_"+thumb_id_num).next());
		$("#title_"+thumb_id_num).insertAfter($("#title_"+thumb_id_num).next());
		$("#caption_"+thumb_id_num).insertAfter($("#caption_"+thumb_id_num).next());
		
		scrollto_slide(thumb_id);
		change_slideshow($type);
		break;
	case "move_first":
		var index_child=$("#"+thumb_id).index()+1;
		var count_images=total_images();
		var thumb_first=$('.thumb_images_container:first');
		if(thumb_first.length==0||thumb_first.is($("#"+thumb_id)))return;
		
		if(count_images>50&&count_images<=100){
			if(index_child-10>0){
				thumb_first=$('.thumb_images_container:nth-child('+(index_child-10)+')');
			}
		}else if(count_images>100){
			if(index_child-20>0){
				thumb_first=$('.thumb_images_container:nth-child('+(index_child-20)+')');
			}
		}
		
		$("#"+thumb_id).insertBefore(thumb_first);
		
		var thumb_first_id_num=thumb_first.attr("id").replace("thumb_",'');
		$("#href_"+thumb_id_num).insertBefore($("#href_"+thumb_first_id_num));
		$("#title_"+thumb_id_num).insertBefore($("#title_"+thumb_first_id_num));
		$("#caption_"+thumb_id_num).insertBefore($("#caption_"+thumb_first_id_num));
		
		scrollto_slide(thumb_id);
		change_slideshow($type);
		break;
	case "move_last":
		var index_child=$("#"+thumb_id).index()+1;
		var count_images=total_images();
		var thumb_last=$('.thumb_images_container:last');
		if(thumb_last.length==0||thumb_last.is($("#"+thumb_id)))return;
		
		if(count_images>50&&count_images<=100){
			if(count_images>index_child+10){
				thumb_last=$('.thumb_images_container:nth-child('+(index_child+10)+')');
			}
		}else if(count_images>100){
			if(count_images>index_child+20){
				thumb_first=$('.thumb_images_container:nth-child('+(index_child+20)+')');
			}
		}
		
		$("#"+thumb_id).insertAfter(thumb_last);
		
		var thumb_last_id_num=thumb_last.attr("id").replace("thumb_",'');
		$("#href_"+thumb_id_num).insertAfter($("#href_"+thumb_last_id_num));
		$("#title_"+thumb_id_num).insertAfter($("#title_"+thumb_last_id_num));
		$("#caption_"+thumb_id_num).insertAfter($("#caption_"+thumb_last_id_num));
		
		scrollto_slide(thumb_id);
		change_slideshow($type);
		break;
	}
}

function focus_this_image(t, image_url_big, get_time)
{
	if($(t).length==0||$(t).hasClass("clicked_img_thumb")) return;
	var thumb_id=htmlEntities($(t).attr("id"));
	if(thumb_id === 'undefined') return;
	var image_title_id="title_"+get_time;
	if($("#"+image_title_id).length==0) return;
	var image_href_id="href_"+get_time;
	if($("#"+image_href_id).length==0) return;
	var image_caption_id="caption_"+get_time;
	if($("#"+image_caption_id).length==0) return;
	
	//add class cliked on thumbs
	if($(".thumb_images_container").hasClass("clicked_img_thumb")){
		$(".thumb_images_container").removeClass("clicked_img_thumb");
	}
	$(t).addClass("clicked_img_thumb");
	
	//big images
	if($(".img_big").length){
		$(".img_big").remove();
	}
	$("#big_image").append($("<div class='img_big slideshowplugin_img' style='background-image:url(\""+image_url_big+"\"); width:415px; height:250px'></div>"));
	
	//set rel of arrows and del buuton with this thumb_id
	$(".del_btn").attr("rel",thumb_id);
	$(".arrows_slides").each(function() {$(this).attr("rel",thumb_id);});
	
	//images title
	if($(".image_title").length>0){
		$(".image_title").val( htmlEntities($("#"+image_title_id).val()) );
		$('.image_title').attr("rel",image_title_id);
	}
	
	//image href
	if($(".image_href").length>0&&$(".image_href").is(":not(:disabled)")){
		$(".image_href").val( htmlEntities($("#"+image_href_id).val()) );
		$(".image_href").attr("rel",image_href_id);
	}
	
	//image caption
	if($(".image_caption").length>0&&$(".image_caption").is(":not(:disabled)")){
		$(".image_caption").val( htmlEntities($("#"+image_caption_id).val()) );
		$(".image_caption").attr("rel",image_caption_id);
	}
}

function change_slideshow($type)
{
	if(editor_type!='cbuilder')
		$('.thumbs_size_div').css('opacity',$type==2?'1':'0');

	if(isNaN(parseInt($type))||$type=='') return;
	if($type==2||$type==3) build_multiboxSlideshow($type);
	else if($type==0||$type==1) build_nivoSlider($type);
	else if($type==4) build_slideshow2($type);
}

function imageSrc_from_bg(bg){
	return  bg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
}

function buildSlideshow2_structure()
{
	if($(".preview").length>0){ $('.preview').unbind().removeData().remove(); }
	if($(".mbox_preview").length>0){ $('.mbox_preview').unbind().removeData().remove(); }
	$('#slideshow_preview').empty();
	if($(".thumb_images_container").length==0) return;
	
	var maxw=280;
	var random_id=Math.floor( Math.random() * ( 1 + 10000 - 1000 ) ) + 1000;
	var $js_structore='<script type="text/javascript"> var slides'+random_id+'=new Array();';
	var $thumb_structure='<tr>';
	var $navigation='';
	$.each($(".thumb_images_container"), function(key){
		var this_thumb=$(this).children('.img_thumb');
		if(this_thumb.length==0) return;
		var image_url_thumb=imageSrc_from_bg(this_thumb.css('background-image'));
		var image_id_thumb=htmlEntities($(this).attr('id'));
		var image_title=htmlEntities($("#title_"+image_id_thumb.replace('thumb_','')).val());
		var image_url_big=image_url_thumb.replace('thumbs/','');
		var image_caption=return_setting_name(arr_slideshow2_useCaption, 'slideshow2_useCaption')=='auto'?htmlEntities($("#caption_"+image_id_thumb.replace('thumb_','')).val()):'';
		
		$thumb_structure+='<td><div><img class="s_'+random_id+'_thumb tid_'+key+'" onclick="openCI(slides'+random_id+'_obj,'+key+','+random_id+')" style="border-radius:3px;" alt="" src="'+image_url_thumb+'"></div></td>';
		$thumb_structure+='</td>';
		
		$js_structore+='slides'+random_id+'['+key+']= new Slide("'+image_url_big+'",210,'+maxw+',"'+image_title+'","'+image_caption+'");';
	});
	$thumb_structure+='</tr>';
	$js_structore+='slides'+random_id+'_options={slideShowSpeed:'+return_setting_value(arr_slideshow2_slideShowSpeed, 'slideshow2_slideShowSpeed')*1000+
	',swipeSpeed:'+return_setting_value(arr_slideshow2_swipeSpeed, 'slideshow2_swipeSpeed')*1000+'};';
	$js_structore+='slides'+random_id+'_obj = new SlideShow(slides'+random_id+',210,'+maxw+','+random_id+',0,slides'+random_id+'_options,true);'
	$js_structore+=return_setting_name(arr_slideshow2_autoRun, 'slideshow2_autoRun')=='auto'?'setTimeout("RunShow(slides'+random_id+'_obj,'+random_id+')", slides'+random_id+'_options.slideShowSpeed);':'';
	$js_structore+='</script>';

	$table_structure='<table cellpadding="1" cellspacing="2" style="margin:0 auto;width:'+maxw+'px;">'+
	'<tr><td colspan="4"><table cellpadding="0" cellspacing="0" style="width:'+maxw+'px;">'+
	'<tr><td colspan="2"><div class="rvps0"><img style="border-radius:6px;" alt="" name="mainview'+random_id+'" id="arunmainview'+random_id+'" src=""></div></td></tr>';
	
	$navigation='<tr><td><div><a href="javascript:void Prev(slides'+random_id+'_obj,'+random_id+');"><img alt="" src=""></a>'+
	'<a href="javascript:void Next(slides'+random_id+'_obj,'+random_id+');"><img alt="" src=""></a>'+
	'<a href="javascript:void RunShow(slides'+random_id+'_obj,'+random_id+');"><img alt="" name="startstop'+random_id+'" src=""></a>'+
	'</div></td><td><div><span id="captionDiv_'+random_id+'"></span></div></td></tr>';
	
	$table_structure+=return_setting_name(arr_slideshow2_useNavigation, 'slideshow2_useNavigation')=='auto'?$navigation:'';
	$table_structure+='</table></td></tr>'+$thumb_structure+'</table>';
	
	$('#slideshow_preview').html($table_structure);
	$($js_structore).appendTo($('#slideshow_preview'));
}

function buildPreview_images($type)
{
	if($(".preview").length>0){ $('.preview').unbind().removeData().remove(); }
	if($(".mbox_preview").length>0){ $('.mbox_preview').unbind().removeData().remove(); }
	$('#slideshow_preview').empty();

	$.each($(".thumb_images_container"), function(key)
	{
		var this_thumb=$(this).children('.img_thumb');
		if(this_thumb.length==0) return;
		var image_url_thumb=imageSrc_from_bg(this_thumb.css('background-image'));
		var image_id_thumb=htmlEntities($(this).attr('id'));
		var image_title=htmlEntities($("#title_"+image_id_thumb.replace('thumb_','')).val());
		var image_href=$type==0||$type==1?htmlEntities($("#href_"+image_id_thumb.replace('thumb_','')).val()):'';
		var image_caption=$type==0||$type==1?htmlEntities($("#caption_"+image_id_thumb.replace('thumb_','')).val()):'';
		var has_href=image_href!=='undefined'&&image_href!=='';
		var has_caption=image_caption!=='undefined'&&image_caption!=='';
		
		if(image_url_thumb!=='undefined'&&image_url_thumb!='')
		{
			var image_url_big=image_url_thumb.replace('thumbs/','');
			var data_thumb='';
			var img_thumb='';
			if($type!=0)
			{
				if($type==1)data_thumb="data-thumb='"+image_url_thumb+"'";
				else img_thumb='<img src="'+($type==3?image_url_big:image_url_thumb)+'" />';					
	
			}
			//preview_images
			$("#slideshow_preview").append($(
				(($type==2||$type==3)?"<a class='mbox_preview "+($type==3?'dummy':'')+"' rel='lightbox[LB1],noDesc' href='"+image_url_big+"' "+(key>0&&$type==3?"style='display:none'":'')+" title='"+image_title+"'>":"")+//multiboxSlider
				(has_href?"<a class='img_nivoSlider' href='http://"+image_href.replace('http://','').replace('https://','')+"' target='_blank'>":"")+
				(($type==0||$type==1)?"<img src='"+image_url_big+"' "+(has_href?'':"class='img_nivoSlider'")+" "+data_thumb+" title='"+image_title+'~'+(has_caption?image_caption:'')+"'/>":"")+//nivoSlider
				(has_href?"</a>":"")+
				img_thumb+
				(($type==2||$type==3)?"</a>":"")
			));
		}
	});
}

function dis_enable_slideshow(enable_class)
{
	$(".tr_settings").each(function(){
		if($(this).hasClass(enable_class)){
			$(this).show();
		}else $(this).hide();
	});
}
	
function build_nivoSlider($type)
{
	buildPreview_images($type);
	if($(".img_nivoSlider").length==0) return;
	$(".img_nivoSlider").wrapAll("<div class='preview nivoSlider' />");
	dis_enable_slideshow("type_nivoSlider");
	
	$(".preview").nivoSlider({
		effect:return_setting_name(arr_nivoSlider_effect, 'nivoSlider_effect'),
		slices: return_setting_value(arr_nivoSlider_slices, 'nivoSlider_slices'),                     // For slice animations
		boxCols: return_setting_value(arr_nivoSlider_boxCols, 'nivoSlider_boxCols'),                     // For box animations
		boxRows: return_setting_value(arr_nivoSlider_boxRows, 'nivoSlider_boxRows'),                     // For box animations
		animSpeed: return_setting_value(arr_nivoSlider_animSpeed, 'nivoSlider_animSpeed')*1000,                 // Slide transition speed
		pauseTime: return_setting_value(arr_nivoSlider_pauseTime, 'nivoSlider_pauseTime')*1000,               // How long each slide will show
		controlNav:($type==1)?true:false,
		captionAni:true,
		controlNavThumbs:($type==1)?true:false
	});
}

function build_slideshow2($type)
{
	buildSlideshow2_structure();
	dis_enable_slideshow("type_slideshow2");
}

function build_multiboxSlideshow($type)
{
	buildPreview_images($type);
	if($(".mbox_preview").length==0) return;
	dis_enable_slideshow("type_fancybox");
	
	if(!nivo_box)
	{
		$(".mbox_preview").multibox({
			zicon:true,
			plugin_preview_width:400,
			transitionIn:return_setting_name(arr_fancybox_transitionIn, 'fancybox_transitionIn'),  // slideshow open effects: 'elastic', 'fade' or 'none'
			transitionOut:return_setting_name(arr_fancybox_transitionOut, 'fancybox_transitionOut'), // slideshow close effects: 'elastic', 'fade' or 'none'
			speedIn: return_setting_value(arr_fancybox_speedIn,'fancybox_speedIn')*1000,       //slideshow open speed	
			speedOut: return_setting_value(arr_fancybox_speedOut,'fancybox_speedOut')*1000,       //slideshow close speed   
			slideshowDelay: return_setting_value(arr_fancybox_slideshowDelay,'fancybox_slideshowDelay')*1000,        // Slides delay (default:4000)			
			changeFade: return_setting_name(arr_fancybox_changeFade, 'fancybox_changeFade')  // Slides transition speed	(default:fast)
		})
		.css("margin",function(){return $type==2?"2px":"0px"});
	
	}else{
		$(".mbox_preview").nivoLightbox({
			effect:'"'+return_setting_name(arr_nivoLightbox_effect, 'nivoLightbox_effect')+'"',
		}).css("margin",function(){return $type==2?"2px":"0px"});
	}
	
	$(".mbox_preview").wrapAll("<div class='preview_box' />");
	var w=$type==2?"129px":"415px";
	var h=$type==2?"80px":"250px";	
	$.each($(".mbox_preview").children('img'), function(i,ch){
		var img_url=ch.src;
		$(ch).wrap("<div class='slideshowplugin_img' style='background-image: url(\""+img_url+"\"); width:"+w+"; height:"+h+"'></div>");
		$(ch).hide();
	});
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function return_setting_name(arr,id){
	if($("#"+id).length==0) return arr[0];
	var v=parseInt($("#"+id).val());
	return (v in arr)?arr[v]:arr[0];
}

function return_setting_value(arr,id)
{
	if($("#"+id).length==0) return arr.value;
	var v=parseFloat($("#"+id).val());
	return v>=arr.min&&v<=arr.max?v:arr.value;
}

function get_fname_from_url(url){
	var src = url.split('/');
	var file_name = src[src.length - 1];
	return file_name.split('.')[0];
}

function total_images(){
	return $(".thumb_images_container").length;
}

function set_total_images(){
	$('.total_images').text(function(){
		return total_images();
	});
}

function dis_enable_slice_box(effect_val)
{
	if(effect_val>=0&&effect_val<=5){
		//slice effect
		$("#nivoSlider_boxCols").attr("disabled",true);
		$("#nivoSlider_boxRows").attr("disabled",true);
		$("#nivoSlider_slices").attr("disabled",false);
	}
	else if(effect_val>=8&&effect_val<=12){
		//box effect
		$("#nivoSlider_boxCols").attr("disabled",false);
		$("#nivoSlider_boxRows").attr("disabled",false);
		$("#nivoSlider_slices").attr("disabled",true);	
	}
	else{
		//all others
		$("#nivoSlider_boxCols").attr("disabled",true);
		$("#nivoSlider_boxRows").attr("disabled",true);
		$("#nivoSlider_slices").attr("disabled",true);
	}
}
function dis_enable_imageInfo(type)
{
	dis_enable_inputElem(type,"image_href",hrefEnabled_types);
	dis_enable_inputElem(type,"image_caption",captionEnabled_types);
	var url_target=$("#url_target"); 
	if(url_target.length==0) return;
	if($.inArray(type, targetEnabled_types)!=-1){
		url_target.attr("disabled",false);
	}else{
		url_target.attr("disabled",true);
	}	
}

function dis_enable_inputElem(type,el_class,enabled_arr)

{
	el=$("."+el_class);
	if(el.length==0) return;
	if($.inArray(type, enabled_arr)!=-1){
		el.attr("disabled",false);
		var inputElem_id=el.attr("rel");
		if($("#"+inputElem_id).length==0)return;
		el.val( htmlEntities($("#"+inputElem_id).val()) );
	}else{
		el.val('').attr("disabled",true);
	}
}

function check_value_min_max(t)
{
	var id=t.attr('id');
	if(typeof eval('arr_'+id) != 'undefined' && eval('arr_'+id) instanceof Object)
	{	
		if("min" in eval('arr_'+id)){
			if(eval('arr_'+id).min > t.val()){
				t.val(eval('arr_'+id).min);
			}
		}
		if("max" in eval('arr_'+id)){
			if(eval('arr_'+id).max < t.val()){
				t.val(eval('arr_'+id).max);
			}
		}
	}
}

function change_imgWidth_mutibox(t)
{
	t.css({"display":"block","width":"100%","padding":"0"});
	var img=t.contents().find('img');
	var body=t.contents().find('body');
	if(img.length==0||body.length==0) return;
	
	body.css("margin","0");
	if(img.height()>img.width())
		img.css("width","100%");
	else
		img.css("height","100%");
}

function onkeyUp(){
	$(".image_info").keyup(function() {
		var image_info=$(this).attr("rel");
		if($("#"+image_info).length==0)return;
		$("#"+image_info).val(htmlEntities($(this).val()));
		var type=get_slideshow_type();
		change_slideshow(type);
	});
}

function getSelected_slideshow()
{
	var arr_editor={};
	if (parent.oUtil + '' == 'undefined') return arr_editor;
	var oEditor = parent.oUtil.oEditor;
	
	var oSel;
	var oEl;
	var p_Sel='';
	if (navigator.appName.indexOf('Microsoft') != -1) {
		oSel = GetSelection();
		if (oSel.parentElement) p_Sel = GetElement(oSel.parentElement(), "P");
		else p_Sel = GetElement(oSel.item(0), "P");
	}
	else {
		if (!oEditor.getSelection()) return;
		oSel = oEditor.getSelection();
		p_Sel = GetElement(parent.getSelectedElement(oSel), "P");
	}
	if(p_Sel&&p_Sel.className=='slideshow_editor')
		oEl=p_Sel;
		
	if (oEl) 
	{
		if (oEl.nodeName=="P"&&oEl.className=='slideshow_editor') {

			if (navigator.appName.indexOf('Microsoft') != -1) {
				var sType = oEditor.document.selection.type;
				if (sType != "Control") {
					try {
						var range = oEditor.document.body.createTextRange();
						range.moveToElementText(oEl);
						range.select();
					} catch (e) { return; }
				}
			}
			else {
				var range = oEditor.document.createRange();
				range.selectNodeContents(oEl);
				oSel.addRange(range);
			}
			
		}
	}
	return {oSel:oSel,oEl:oEl};
}

function delete_selected_slideshow()
{
	if(editor_type=='innova')
	{
		parent.oUtil.obj.setFocus();
		var sel_arr=getSelected_slideshow();
		if (!jQuery.isEmptyObject(sel_arr)&&sel_arr.oEl){
			if (sel_arr.oEl.nodeName == "P"&&sel_arr.oEl.className=='slideshow_editor'){
				var object=sel_arr.oSel.focusNode.parentNode;
				$(object).remove();
				window.parent.$('.box_close').click();
			}
		}
	}
	else if(editor_type=='tiny')
	{
		if(parent.tinymce + '' !== 'undefined'){
			var object=parent.tinymce.activeEditor.selection.getNode();			
			$(object).remove();
			parent.tinymce.activeEditor.windowManager.close();
		}
	}
	else if(editor_type=='cbuilder')
	{
		if(parent.window.obj_cbuilder!=='undefined'&&parent.window.obj_cbuilder.length){
			var sid=$('input[name="sid"]').val();
			var embed_slideshow=parent.window.obj_cbuilder;
			$(embed_slideshow).remove();
			window.parent.$('.box_close').click();
		}
	}
}

function get_between_two_strings(str,between1,between2){
	if(str.indexOf(between1) != -1&&str.indexOf(between2) != -1)
		return str.substring(str.indexOf(between1) + between1.length,str.indexOf(between2));
	else return '';
}

function build_slideshow_form_db(selected)
{
	var sldieshow_string=get_between_two_strings(selected,"{%SLIDESHOW_ID(",")%}");			
	var slideshow_id=parseInt(sldieshow_string.replace( /[^\d.]/g, '' ));

	if($('input[name="sid"]').length>0&&slideshow_id>0){			
		var old_s=parseInt($('input[name="sid"]').val());
		if(slideshow_id!==old_s){
			delete_all_slides(true);
			$('input[name="sid"]').remove();
		}
	}
	
	if(slideshow_id>0&&$('input[name="sid"]').length==0)
	{
		$.post( "slideshow_plugin.php", { pid: page_id, edit_sid: slideshow_id} ).done(function(data) 
		{
			var s_obj = jQuery.parseJSON(data);
			
			if(s_obj.length==9&&s_obj[0]!=='error')
			{	
				var type=s_obj[0];
				var settings=s_obj[1].split('|');
				var url_target=s_obj[2];
				var titles=jQuery.parseJSON(s_obj[3]);
				var hrefs=jQuery.parseJSON(s_obj[4]);
				var urls=s_obj[5].split(',');	
				var captions=jQuery.parseJSON(s_obj[6]);
				var global_view=s_obj[7];
				var thumbs_size=parseInt(s_obj[8])||0;

				setAssetValue(urls,'',titles,hrefs,captions);
				$("#slideshow_type").val(type);
				$("#global_view").prop("checked", (global_view==1?true:false));
				$("#thumbs_size").val((thumbs_size>0?thumbs_size:0));
				$("#url_target").val(url_target);
				
				dis_enable_imageInfo(type);	
				run_slideshow(settings,type,slideshow_id);
			}
			else{			
				$("<span>").attr({'class':'error'}).text(error_text).appendTo('td.results');
			}
		});
	}
}

function Realtime_Sldieshow_innova() 
{
	var sel_arr=getSelected_slideshow();
	if(sel_arr.oEl)
		build_slideshow_form_db(sel_arr.oEl.innerHTML);
}
			
function setSettings(class_settings, settings)
{
	if($("."+class_settings).length==settings.length){
		$("."+class_settings).each(function(index,element) {
			$(this).val(settings[index]>=100?settings[index]/1000:settings[index]);
		});
	}
}

function build_Settings_cbuilder(sstype)
{
	var settings=''; 
	if(sstype==0||sstype==1)
		settings=getSettings_cbuilder("nivoSlider_settings");
	else if(sstype==2||sstype==3)
	{
		settings=nivo_box?getSettings_cbuilder("nivoLightbox_settings"):getSettings_cbuilder("fancybox_settings");
	}
	else if(sstype==4){
		settings=getSettings_cbuilder("slideshow2_settings");
	}
	return settings;
}

function getSettings_cbuilder(class_settings)
{
	var settings=''; 
	$("."+class_settings).each(function(i,v) {
		this_value=$(this).val();
		if($(this).attr('id')=="nivoSlider_effect"){	 
			this_value+=arr_nivoSlider_effect[this_value];
		}
		else if($(this).attr('id')=="fancybox_transitionIn"){	
			this_value+=arr_fancybox_transitionIn[this_value];
		}
		else if($(this).attr('id')=="fancybox_transitionOut"){
			this_value+=arr_fancybox_transitionOut[this_value];
		}
		else if($(this).attr('id')=="fancybox_changeFade"){
			this_value+=arr_fancybox_changeFade[this_value];
		}
		else if($(this).attr('id')=="nivoLightbox_effect"){
			this_value+=arr_nivoLightbox_effect[this_value];
		}
		
		settings+=(i!=0?'|':'')+this_value;
	});
	return settings;
}

function getQueryVariable(variable)
{
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == variable){return pair[1];}
       }
       return(false);
}

function get_slideshow_type(){
	return $("#slideshow_type").length>0?$("#slideshow_type").val():'';
}

function _loadimages(imgArr,callback) 
{
	var imagesLoaded = 0;
	window.all_imgs_array=[];
	function _loadAllImages(callback){
		var img = new Image();
		$(img).attr('src',imgArr[imagesLoaded]);
		if (img.complete || img.readyState === 4) {
			window.all_imgs_array.push({'img_path':imgArr[imagesLoaded],'img_width':img.width,'img_height':img.height});
			imagesLoaded++;
			if(imagesLoaded == imgArr.length)
				callback();
			else
				_loadAllImages(callback);
		} 
		else {
			$(img).load(function(){	
				window.all_imgs_array.push({'img_path':imgArr[imagesLoaded],'img_width':img.width,'img_height':img.height});
				imagesLoaded++;
				if(imagesLoaded == imgArr.length)
					callback();
				else 
					_loadAllImages(callback);
			}).error(function(){
				imagesLoaded++;
				window.all_imgs_array.push({'img_path':imgArr[imagesLoaded],'img_width':0,'img_height':0});
				if(imagesLoaded == imgArr.length)
					callback();
				else 
					_loadAllImages(callback);
			});
		}
	};		
	_loadAllImages(callback);
}

function edit_slidehsow_cbuilder()
{
	$(".box_loading", window.parent.document).hide();
	var sid=$('input[name="sid"]').val();
	var embed_slideshow=parent.window.obj_cbuilder;
	
	var data_ss_enc=$(embed_slideshow).attr('data-slideshow');
	var data_ss_dec=$.parseJSON(data_ss_enc);
	
	var uniqueId=parent.IDGenerator();
	var ss_images='', ss_titles='', ss_captions='', ss_hrefs='', ss_target='';
	
	var ss_type=$("#slideshow_type").val();
	var ss_global_view=$("#global_view").is(':checked')?1:0;
	ss_options=build_Settings_cbuilder(ss_type);
	
	var ss_target=$("#url_target").val();
	var images_dim='';
	$.each(window.all_imgs_array, function(i,v){
		ss_images+=(i!=0?'|':'')+this.img_path;
		images_dim+=(i!=0?'|':'')+this.img_width+','+this.img_height;
	});
	
	$.each($("input[name='slide_title[]']"), function(i,v){
		ss_titles+=(i!=0?'|':'')+$(this).val();
	});
	$.each($("input[name='slide_caption[]']"), function(i,v){
		ss_captions+=(i!=0?'|':'')+$(this).val();
	});
	$.each($("input[name='slide_href[]']"), function(i,v){
		ss_hrefs+=(i!=0?'|':'')+$(this).val();
	});
	
	var data_slideshow={ss_type:ss_type, ss_options:ss_options, ss_images:ss_images, ss_titles:ss_titles, ss_captions:ss_captions, ss_hrefs:ss_hrefs, ss_target:ss_target,ss_sid:uniqueId,rel_path:data_ss_dec['rel_path'],ss_images_dim:images_dim,ss_global_view:ss_global_view};
	
	data_ss_enc = JSON.stringify(data_slideshow); 
	var builded_ss_data=parent.cbuilder_slideshow_structure(uniqueId, data_ss_enc);
	var ss_class='embed-slideshow';
	
	if(ss_type==2||ss_type==3){
		ss_class+=ss_type==2?' multibox':' multibox_single';
		if($(embed_slideshow).hasClass('ss_rounded')){
			ss_class+=' ss_rounded';
		}
	}
	$(embed_slideshow).empty();
	$(embed_slideshow).attr({'data-slideshow':data_ss_enc,'class':''}).addClass(ss_class);
	$(embed_slideshow).html('<!--ss_content'+uniqueId+'-->'+builded_ss_data['ss_html']+'<!--end_ss_content'+uniqueId+'-->');
	parent.cbuilder_append_ss_js(builded_ss_data['ss_js'], parent.document);
	window.parent.$('.box_close').click();			
}

function insert_slideshow_editor(HTML) 
{
	if(editor_type=='innova')
	{	//innova editor
		if (parent.oUtil+''!=='undefined'){
			if(HTML!=''){
				parent.oUtil.obj.setFocus();
				parent.oUtil.obj.insertHTML(HTML);
			}
			window.parent.$('.box_close').click();
		}
	}
	else if(editor_type=='tiny')
	{	//tiny editor
		if(parent.tinymce!=='undefined'){
			if(HTML!='')
				parent.tinymce.activeEditor.execCommand('mceInsertContent', false, HTML);
			parent.tinymce.activeEditor.windowManager.close();
		}
	}
}

function run_slideshow(ssoptions,sstype,ssid)
{
	if(sstype==0||sstype==1)
		setSettings("nivoSlider_settings",ssoptions);
	else if(sstype==2||sstype==3)
	{
		if(nivo_box)
			setSettings("nivoLightbox_settings",ssoptions);
		else
			setSettings("fancybox_settings",ssoptions);
	}
	else if(sstype==4){
		setSettings("slideshow2_settings",ssoptions);
	}
	change_slideshow(sstype);
	$("<input>").attr({
		'type':'hidden',
		'name':'sid'
	}).val(ssid).appendTo("form");
}

function build_slideshow_from_cbuilder()
{
	if(parent.window.obj_cbuilder!=='undefined'&&parent.window.obj_cbuilder.length)
	{
		var embed_slideshow=parent.window.obj_cbuilder;
		
		var data_ss_enc=$(embed_slideshow).attr('data-slideshow');
		var data_ss_dec=$.parseJSON(data_ss_enc);
		
		var ssoptions=data_ss_dec['ss_options'].split('|');
		var sstype=data_ss_dec['ss_type'];
		var ssid=data_ss_dec['ss_sid'];
		var ssimages=data_ss_dec['ss_images'].split('|');
		var sstitles=data_ss_dec['ss_titles'].split('|');
		var sscaptions=data_ss_dec['ss_captions'].split('|');
		var sstarget=data_ss_dec['ss_target'];
		var sshrefs=data_ss_dec['ss_hrefs'].split('|');
		var ssglobal_view=data_ss_dec['ss_global_view'];
		
		setAssetValue(ssimages,'',sstitles,sshrefs,sscaptions,true);
		$("#slideshow_type").val(sstype);
		$("#global_view").prop("checked",(ssglobal_view==1?true:false));
		$("#url_target").val(sstarget);
		
		dis_enable_imageInfo(sstype);
		$.each(ssoptions, function(i,v){
			if(/^\d+$/.test(v)===false){
				var num=v.match(/\d/g);
				ssoptions[i] = num.join("");
			}
		});		
		run_slideshow(ssoptions,sstype,ssid);
	}
}

function insert_cbuilder(){
	$(".box_loading", window.parent.document).show();
	var array_imgages=[];
	$.each($("input[name='slide_url[]']"), function(i,v){
		array_imgages[i]=$(this).val();
	});
	_loadimages(array_imgages,edit_slidehsow_cbuilder);
} 
$(document).ready(function() 
{
	if(getQueryVariable('error')===false)
	{
		// edit slideshow
		if(editor_type=='innova')
		{
			Realtime_Sldieshow_innova();
			parent.oUtil.onSelectionChanged = new Function("Realtime_Sldieshow_innova()");
		}
		else if(editor_type=='tiny')
		{
			if(parent.tinymce!=='undefined'){
				build_slideshow_form_db(parent.tinymce.activeEditor.selection.getNode().innerHTML);
			}
		}
		else if(editor_type=='cbuilder'){
			build_slideshow_from_cbuilder();
		}
	}
	
	$('.tabs .tab-links a').on('click', function(e)  {
        var currentAttrValue = $(this).attr('href');
        $('.tabs ' + currentAttrValue).show().siblings().hide();
        $(this).parent('li').addClass('active').siblings().removeClass('active');
        e.preventDefault();
    });
	
	onkeyUp();
	
	$("#slideshow_type").change(function() {
		var type=$(this).val();
		dis_enable_imageInfo(type);
		change_slideshow(type);
	});
	
	$(".f_settings").change(function() {
		check_value_min_max($(this));
		change_slideshow(get_slideshow_type());
	});
	
	$("#nivoSlider_effect").change(function() {
		var effect_val=$(this).val();
		dis_enable_slice_box(effect_val);
	});
	
	$("#nivoSlider_animSpeed").addClass('seconds');
	$("#nivoSlider_pauseTime").addClass('seconds');
	$("#fancybox_speedIn").addClass('seconds');
	$("#fancybox_speedOut").addClass('seconds');
	$("#fancybox_slideshowDelay").addClass('seconds');
	$("#slideshow2_slideShowSpeed").addClass('seconds');
	$("#slideshow2_swipeSpeed").addClass('seconds');	

	$("#nivoSlider_boxCols").attr("disabled",true);
	$("#nivoSlider_boxRows").attr("disabled",true);
	
	$(".cbuilder_submit").click(function(){
		$('.seconds').each(function (){
			$(this).val(function (index, value) { return parseFloat(value)*1000; });
		});
		if(editor_type=='cbuilder'){
			if($(".thumb_images_container").length==0)
				delete_selected_slideshow();
			else{
				insert_cbuilder();
			}	
		}
	});
	
	$("form").submit(function() {
		$("#nivoSlider_boxCols").removeAttr("disabled");
		$("#nivoSlider_boxRows").removeAttr("disabled");
		$("#nivoSlider_slices").removeAttr("disabled");
		$('.seconds').each(function (){
			$(this).val(function (index, value) { return parseFloat(value)*1000; });
		});
		
		if($("#slideshow_type").length==0) return;
		var type=$("#slideshow_type").val();
		if($.inArray(type, captionEnabled_types)==-1){//empty caption input for not enabled caption types
			$("input[name='slide_caption[]']").val('');
		}
		if($.inArray(type, hrefEnabled_types)==-1){//empty href input for not enabled href types
			$("input[name='slide_href[]']").val('');
		}
	});
});
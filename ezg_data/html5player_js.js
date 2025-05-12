function setAssetValue(val,id,titles,captions,img_urls,edit_cbuilder)
{
	titles = typeof titles !== 'undefined' ? titles : [];
	captions = typeof captions !== 'undefined' ? captions : [];
	img_urls = typeof img_urls !== 'undefined' ? img_urls : [];
	if(!$.isArray(val)) val=val.split();
	$.each(val,function(i,c) 
	{
		var total_tr=total_tracks();
		if(total_tr>=max_tracks){alert(max_tracks_text);return;}
		if(edit_cbuilder){
			c=parent.resolveImage_cbuilder(c,'');
			if(c.indexOf('innovaeditor/')!=-1)
				c='../'+c;
		}
		if(c.indexOf('innovaeditor/')==-1)
			c='../'+c;
		var date_obj=new Date();
		var gtime=date_obj.getTime()+i;
		var filename=get_fname_from_url(c);
		var is_image=c.match(/\.(jpg|jpeg|png|)$/);
		var is_audio_video=c.match(/\.(ogg|mp3|mp4|wav|oga|m4a|acc)$/);
		if(is_image!=null&&is_audio_video==null&&$('.clicked_img_thumb').length)
		{
			var clicked_img_id=$('.clicked_img_thumb').attr('id').replace('thumb_','');
			var	position=c.lastIndexOf("/")+1;
			var image_url_thumb=[c.slice(0, position), 'thumbs/', c.slice(position)].join('');
			if($('#img_url_'+clicked_img_id).length&&$("#img_"+clicked_img_id).length){
				$('#img_url_'+clicked_img_id).val(image_url_thumb);
				$("#img_"+clicked_img_id).css('background-image','url("'+image_url_thumb+'")');
			}
			return;
		}
		if(is_audio_video==null)
			return;

		//thumbs
		$("#thumbs_html5player").append($(
		"<div class='thumb_images_container mouseout_image' id='thumb_"+gtime+"' onclick=\"focus_this_image(this, '"+c+"', '"+gtime+"')\">"+
		"<div class='img_thumb slideshowplugin_img' id='img_"+gtime+"' style='"+(typeof img_urls[i] != "undefined" && img_urls[i] != ''?'background-image:url(\"'+img_urls[i]+"\");":'')+"width:95px; height:95px'></div>"+
		"<div id='del_"+gtime+"' class='delete_slide mouseout_delbtn'><i class='fa fa-times'></i></div>"+
		"<div class='add_image_icon' title='browse for image' onclick='openAsset(\"html5player\")'><i class='fa fa-camera'></i></div>"+
		"<div class='tracks_number'>#"+(total_tr+1)+"</div>"+
		"</div>"
		));
				
		//track title
		$("#track_title_container").append($(
		"<input type='hidden'  name='track_title[]' value='"+(typeof titles[i] != "undefined"?titles[i]:filename)+"' id='title_"+gtime+"' />"
		));
		//track caption
		$("#track_caption_container").append($(
		"<input type='hidden'  name='track_caption[]' value='"+(typeof captions[i] != "undefined"?captions[i]:'')+"' id='caption_"+gtime+"' />"
		));
		//track img_urls
		$("#image_url_container").append($(
		"<input type='hidden'  name='track_img_url[]' value='"+(typeof img_urls[i] != "undefined"?img_urls[i]:'')+"' id='img_url_"+gtime+"' />"
		));
		//track href
		$("#track_url_container").append($(
		"<input type='hidden'  name='track_url[]' value='"+c+"' id='track_url_"+gtime+"' />"
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
					$(".image_caption").val('');
				}
				$(this).parent().remove();
				$("#title_"+gtime).remove();
				$("#img_url_"+gtime).remove();
				$("#track_url_"+gtime).remove();
				$("#caption_"+gtime).remove();
				change_numbers_tracks();
				build_html5player();
				set_total_tracks();
			}
		);
		if($(".thumb_images_container").length==1){
			focus_this_image($("#thumb_"+gtime), c, gtime);
		}
		if (typeof img_urls[i] == "undefined" || img_urls[i] == '') 
		{
			var xhr = new XMLHttpRequest();
			xhr.responseType = "arraybuffer";
			xhr.open("get", c, true);
			xhr.onload = function(e) {
				var data=e.target.response;
				musicmetadata(data, function (err, result) {
					if (err) return;
					if (result.picture.length > 0) 
					{
						var picture = result.picture[0];
						var url = arrayBufferToBase64(picture.data);
						var image1 = $('#img_'+gtime);
						if(image1.length)
						{
							var svg_track='url("data:image/'+picture.format+'+xml;base64,'+url+'")';
							$('#img_url_'+gtime).attr('data-64',svg_track);
							image1.animate({opacity: 0.3}, 200, function() {
								$(this)
								.css({'background-image': svg_track})
								.animate({opacity: 1});
							});
						}
					}
				});
			}
			xhr.send();
		}
	});
	set_total_tracks();
	setTimeout(function(){
		build_html5player();	
	},500);
}
function change_numbers_tracks(){
	$.each($('.tracks_number'), function(i,v){
		$(v).html('#'+(i+1));
	});
}
function Uint8ToString(u8a){
  var CHUNK_SZ = 0x8000;
  var c = [];
  for (var i=0; i < u8a.length; i+=CHUNK_SZ) {
    c.push(String.fromCharCode.apply(null, u8a.subarray(i, i+CHUNK_SZ)));
  }
  return c.join("");
}
function total_tracks(){
	return $(".thumb_images_container").length;
}
function set_total_tracks(){
	$('.total_tracks').html(function(){
		return total_tracks();
	});
}
function arrayBufferToBase64(buffer) {
    var binary = '';
    var bytes = new Uint8Array( buffer );
    var len = bytes.byteLength;
    for (var i = 0; i < len; i++) {
        binary += String.fromCharCode( bytes[ i ] );
    }
    return window.btoa( binary );
}
function get_fname_from_url(url){
	var src = url.split('/');
	var file_name = src[src.length - 1];
	return file_name.split('.')[0];
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
				$("#thumbs_html5player").empty();
				$("#track_title_container").empty();
				$("#track_url_container").empty();
				$("#image_url_container").empty();
				$("#track_caption_container").empty();
				$('.simple_player').remove();
				$("#html5player_preview").empty();
				set_total_tracks();
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
	if($("#title_"+thumb_id_num).length==0||$("#caption_"+thumb_id_num).length==0) return;

	switch(action){
	case "move_up":
		var prev=$("#"+thumb_id).prev();
		if(prev.length==0||prev.attr("id").indexOf("thumb_")<0)return;
		$("#"+thumb_id).insertBefore(prev);
		
		$("#img_url_"+thumb_id_num).insertBefore($("#img_url_"+thumb_id_num).prev());
		$("#track_url_"+thumb_id_num).insertBefore($("#track_url_"+thumb_id_num).prev());
		$("#title_"+thumb_id_num).insertBefore($("#title_"+thumb_id_num).prev());
		$("#caption_"+thumb_id_num).insertBefore($("#caption_"+thumb_id_num).prev());
		
		scrollto_slide(thumb_id);
		build_html5player();
		change_numbers_tracks();
		break;
	case "move_down":
		var next = $("#"+thumb_id).next();
		if(next.length==0||next.attr("id").indexOf("thumb_")<0)return;
		$("#"+thumb_id).insertAfter(next);
		
		$("#img_url_"+thumb_id_num).insertAfter($("#img_url_"+thumb_id_num).next());
		$("#track_url_"+thumb_id_num).insertAfter($("#track_url_"+thumb_id_num).next());
		$("#title_"+thumb_id_num).insertAfter($("#title_"+thumb_id_num).next());
		$("#caption_"+thumb_id_num).insertAfter($("#caption_"+thumb_id_num).next());
		
		scrollto_slide(thumb_id);
		build_html5player();
		change_numbers_tracks();
		break;
	case "move_first":
		var index_child=$("#"+thumb_id).index()+1;
		var count_images=total_tracks();
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
		$("#img_url_"+thumb_id_num).insertBefore($("#img_url_"+thumb_first_id_num));
		$("#track_url_"+thumb_id_num).insertBefore($("#track_url_"+thumb_first_id_num));
		$("#title_"+thumb_id_num).insertBefore($("#title_"+thumb_first_id_num));
		$("#caption_"+thumb_id_num).insertBefore($("#caption_"+thumb_first_id_num));
		
		scrollto_slide(thumb_id);
		build_html5player();
		change_numbers_tracks();
		break;
	case "move_last":
		var index_child=$("#"+thumb_id).index()+1;
		var count_images=total_tracks();
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
		$("#img_url_"+thumb_id_num).insertAfter($("#img_url_"+thumb_last_id_num));
		$("#track_url_"+thumb_id_num).insertAfter($("#track_url_"+thumb_last_id_num));
		$("#title_"+thumb_id_num).insertAfter($("#title_"+thumb_last_id_num));
		$("#caption_"+thumb_id_num).insertAfter($("#caption_"+thumb_last_id_num));
		
		scrollto_slide(thumb_id);
		build_html5player();
		change_numbers_tracks();
		break;
	}
}

function focus_this_image(t, tack_path, get_time)
{
	if($(t).length==0||$(t).hasClass("clicked_img_thumb")) return;
	var thumb_id=htmlEntities($(t).attr("id"));
	if(thumb_id === 'undefined') return;
	var image_title_id="title_"+get_time;
	if($("#"+image_title_id).length==0) return;
	var image_caption_id="caption_"+get_time;
	if($("#"+image_caption_id).length==0) return;

	//add class cliked on thumbs
	if($(".thumb_images_container").hasClass("clicked_img_thumb")){
		$(".thumb_images_container").removeClass("clicked_img_thumb");
	}
	$(t).addClass("clicked_img_thumb");
	
	//set rel of arrows and del buuton with this thumb_id
	$(".del_btn").attr("rel",thumb_id);
	$(".arrows_slides").each(function() {$(this).attr("rel",thumb_id);});
	
	//images title
	if($(".image_title").length>0){
		$(".image_title").val( htmlEntities($("#"+image_title_id).val()) );
		$('.image_title').attr("rel",image_title_id);
	}

	//image caption
	if($(".image_caption").length>0&&$(".image_caption").is(":not(:disabled)")){
		$(".image_caption").val( htmlEntities($("#"+image_caption_id).val()) );
		$(".image_caption").attr("rel",image_caption_id);
	}
}

function build_html5player()
{
	var image_hrefs=$('#image_url_container').find('input');
	var track_hrefs=$('#track_url_container').find('input');
	var track_titles=$('#track_title_container').find('input');
	var track_captions=$('#track_caption_container').find('input');
	
	//console.log(image_hrefs.length,track_hrefs.length,track_titles.length,track_captions.length);
	if(track_hrefs.length>0&&track_hrefs.length==image_hrefs.length&&track_hrefs.length==track_titles.length&&track_hrefs.length==track_captions.length)
	{
		var is_video_type=return_setting_name(arr_html5player_type, 'html5player_type')=='video';
		$('.simple_player').remove();
		$('#html5player_preview').empty();
		var li='';
		var structure='';
		if(is_video_type){
			structure=
			'<div class="simple_player video">'+
				'<div class="video-holder">'+
					'<video preload="metadata" tabindex="0" src="'+track_hrefs[0].value+'">'+
						'<source src="'+track_hrefs[0].value+'">'+
					'</video>'+
				'</div>'+
				'<ul>';
		}
		else{
			structure=
			'<div class="simple_player">'+
				'<audio preload="metadata" tabindex="0" controls="" src="'+track_hrefs[0].value+'">'+
					'<source src="'+track_hrefs[0].value+'">'+
				'</audio>'+
				'<ul>';
		}
		$.each(track_hrefs, function(i,v){
			var image=image_hrefs[i].value;
			var image_data64=$(image_hrefs[i]).attr('data-64');
			if(image==''&&image_data64!=undefined)
				image=image_data64.replace('url("','').replace('")','');
			li+=
			'<li>'+
				(is_video_type?'<div class="video_img" style="background-image:url('+image+');"></div>':'')+
				'<span>'+(i+1)+'.</span>'+
				'<div class="sp_track_info sp_marquee_parent">'+
					'<a href="'+v.value+'">'+track_titles[i].value+'</a>'+
				'</div>'+
				(track_captions[i].value!=''?'<a onclick="$(\'.sp_music_comment_'+i+'\').toggle();" class="sp_comments_icon"></a>':'')+
				'<a href="'+v.value+'" class="sp_download_link" title="download" download></a>'+
				'<div class="sp_music_comment_'+i+' sp_track_comment">'+track_captions[i].value+'</div>'+
				(image!=''?'<img src="'+image+'" />':'')+
			'</li>';
		});
		structure+=li+'</ul></div>';
		$('#html5player_preview').html(structure);
		run_player();
	}
}

function run_player()
{
	if($('.simple_player').length){
		$('.simple_player').simpleplayer({
			type:return_setting_name(arr_html5player_type, 'html5player_type'),
			volume:return_setting_value(arr_html5player_volume, 'html5player_volume'),
			current:return_setting_value(arr_html5player_current, 'html5player_current'),
			loop:return_setting_name(arr_html5player_loop, 'html5player_loop'),
			width:return_setting_value(arr_html5player_width, 'html5player_width')
		});
	}
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

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function onkeyUp()
{
	$(".image_info").keyup(function() {
		var image_info=$(this).attr("rel");
		if($("#"+image_info).length==0)return;
		$("#"+image_info).val(htmlEntities($(this).val()));
	});
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
function insert_html5player_editor(HTML) 
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

function Realtime_Sldieshow_innova() 
{
	var sel_arr=getSelected_slideshow();
	if(sel_arr.oEl)
		build_slideshow_form_db(sel_arr.oEl.innerHTML);
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
	if(p_Sel&&p_Sel.className=='html5player_editor')
		oEl=p_Sel;
		
	if (oEl) 
	{
		if (oEl.nodeName=="P"&&oEl.className=='html5player_editor') {

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
			if (sel_arr.oEl.nodeName == "P"&&sel_arr.oEl.className=='html5player_editor'){
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
			var sid=$('input[name="player_id"]').val();
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
	var sldieshow_string=get_between_two_strings(selected,"{%HTML5PLAYER_ID(",")%}");			
	var player_id=parseInt(sldieshow_string.replace( /[^\d.]/g, '' ));

	if($('input[name="player_id"]').length>0&&player_id>0){			
		var old_s=parseInt($('input[name="player_id"]').val());
		if(player_id!==old_s){
			delete_all_slides(true);
			$('input[name="player_id"]').remove();
		}
	}
	if(player_id>0&&$('input[name="player_id"]').length==0)
	{
		$.post( "html5player_plugin.php", { pid: page_id, edit_sid: player_id} ).done(function(data) 
		{
			var s_obj = jQuery.parseJSON(data);

			if(s_obj.length==5&&s_obj[0]!=='error')
			{	
				var settings=s_obj[0].split('|');
				var track_urls=s_obj[1].split('|');
				var img_urls=s_obj[2].split('|');
				var titles=jQuery.parseJSON(s_obj[3]);
				var captions=jQuery.parseJSON(s_obj[4]);

				setAssetValue(track_urls,'',titles,captions,img_urls);
				build_edited_html5player(settings,player_id);
			}
			else{			
				$("<span>").attr({'class':'error'}).text(error_text).appendTo('td.results');
			}
		});
	}
}
function build_edited_html5player(settings,id)
{
	setSettings('html5player_settings',settings);
	$("<input>").attr({
		'type':'hidden',
		'name':'player_id'
	}).val(id).appendTo("form");
	build_html5player();
	run_player();
}
function setSettings(class_settings, settings)
{
	if($("."+class_settings).length==settings.length){
		$("."+class_settings).each(function(index,element) {
			$(this).val(settings[index]);
		});
	}
}

$(document).ready(function()
{
	onkeyUp();
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
	$(".cbuilder_submit").click(function(){
		if(editor_type=='cbuilder'){
			if($(".thumb_images_container").length==0)
				delete_selected_slideshow();
			else{
				insert_cbuilder();
			}	
		}
	});
	
	$(".html5player_settings").change(function() {
		check_value_min_max($(this));
		build_html5player();
	});
	$('.tabs .tab-links a').on('click', function(e)  {
        var currentAttrValue = $(this).attr('href');
        $('.tabs ' + currentAttrValue).show().siblings().hide();
        $(this).parent('li').addClass('active').siblings().removeClass('active');
        e.preventDefault();
    });
});

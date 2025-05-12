/*!
 * jQuery Slideshow II for ezg 1.0.4
 *
 */
var lastshow_id = -1;
var slideshow = null;
var sliding = false;
var t;
var slideshow2_all_objects=[];
var slideshow2_index=-1, min_lazyLoad=5;

function lazy_load_thumbs(object_ss,distance)
{
	if(!object_ss.lazyload) return;
	var max_thumbs=Math.round(object_ss.maxwidth_thumbcont/object_ss.thumb_width),
	distance_thumb=object_ss.current_thumb*object_ss.thumb_width+distance,
	max=Math.round(distance_thumb/object_ss.thumb_width);
	
	for(var i=object_ss.currentindex;i<=(max_thumbs + max);i++){
		$(".tid_div_"+object_ss.id+"_"+i).lazyLoad().css('opacity','1');
	}
}

function lazy_load(object_ss,swipe_counter,first_img)
{
	if(!object_ss.lazyload) return;
	var next_img = first_img!='' ? first_img : (object_ss.currentindex),
	swipe_counter = swipe_counter!='' ? swipe_counter : 0;
	$(".img_"+object_ss.id+'_'+(next_img+swipe_counter)).lazyLoad(); //add lazy load to current image
	$(".img_"+object_ss.id+'_'+(next_img+swipe_counter+1)).lazyLoad(); //add lazy load to next image
	$(".img_"+object_ss.id+'_'+(next_img+swipe_counter-1)).lazyLoad(); //add lazy load to prev image
}

function switchslide(object_ss,speed){
	speed = typeof speed !== 'undefined' ? speed : object_ss.swipeSpeed;
	swipeImageContainer(object_ss.maxwidth * object_ss.currentindex, speed, object_ss.id);
	setopa(object_ss.id,object_ss.currentindex);
	caption(object_ss,object_ss.id,object_ss.currentindex);
}

function previousImage(object_ss,doingshow) {
	if($(".slideshow2_"+object_ss.id).length==0) return;
	if(!doingshow) killss(object_ss);
	object_ss.currentindex = Math.max(object_ss.currentindex - 1, 0);
	window.slideshow2_all_objects[object_ss.idx].currentindex=object_ss.currentindex;
	switchslide(object_ss);
	lazy_load(object_ss,'','');
}

function nextImage(object_ss,doingshow) {
	if($(".slideshow2_"+object_ss.id).length==0) return;
	if(!doingshow) killss(object_ss);
	object_ss.currentindex  = Math.min(object_ss.currentindex + 1, object_ss.slides.length- 1);
	window.slideshow2_all_objects[object_ss.idx].currentindex=object_ss.currentindex;
	switchslide(object_ss);
	lazy_load(object_ss,'','');
}

function swipeImageContainer(distance, duration, id) {
	var slideshow2_imgs=$("#slideshow2_imgs_navigation"+id);
	slideshow2_imgs.css("transition-duration", (duration / 1000).toFixed(1) + "s");
	var value = (distance < 0 ? "" : "-") + Math.abs(distance).toString();
	slideshow2_imgs.css("transform", "translate(" + value + "px,0)");
}

function swipeThumbContainer(distance, duration, id){
	var slideshow2_thumb_navigation=$(".slideshow2_thumb_navigation"+id);
	slideshow2_thumb_navigation.css("transition-duration", (duration / 1000).toFixed(1) + "s");
	var value = (distance < 0 ? "" : "-") + Math.abs(distance).toString();
	slideshow2_thumb_navigation.css("transform", "translate(" + value + "px,0)");
}

function scoll_toThumb(id,object_ss,direction)
{
	var slideshow2_thumb_navigation=$(".slideshow2_thumb_navigation"+id);
	var wrap=$("#slideshow2_thumb_wrap"+id);
	var wrap_left=wrap.offset().left;
	var thumbs_container_left=slideshow2_thumb_navigation.offset().left;
	var thumb_left=object_ss.currentindex*object_ss.thumb_width;
	var notvisible_plus=wrap_left-thumbs_container_left;
	var notvisible_minus=thumbs_container_left-wrap_left;

	var max_current_thumb=((object_ss.count_images*object_ss.thumb_width)-object_ss.maxwidth_thumbcont)/object_ss.thumb_width;
	var perthumb_count=max_current_thumb/object_ss.count_images;
	if(notvisible_plus>thumb_left||((notvisible_minus+thumb_left+object_ss.thumb_width)>object_ss.maxwidth_thumbcont))
	{
		if(direction=='prev'){
			object_ss.current_thumb = Math.max((object_ss.currentindex)*perthumb_count,0);
		}else{
			object_ss.current_thumb = Math.min((object_ss.currentindex+1)*perthumb_count,max_current_thumb);
			lazy_load_thumbs(object_ss,object_ss.thumb_width);
		}
		swipeThumbContainer(object_ss.thumb_width*object_ss.current_thumb,object_ss.swipeSpeed,id);
	}
}

//---responsive functionality---//
function add_responsive_slideshow2()
{
	$.each(window.slideshow2_all_objects, function(i,el)
	{
		if(("obj" in this)&&$(".slideshow2_"+this.obj.id).length!=0)
		{
			var imgs_arr=this.slideshow2_imgs_array[0];
			var thumb_nav_arr=this.thumb_nagivation_array[0];
			var imgs_wrap=this.slideshow2_imgs_wrap_array[0];
			var parent_w=(imgs_wrap.this_parent!==false&&imgs_wrap.this_parent.length)?imgs_wrap.this_parent.width():0;
			var real_imgs=this.obj.slides;
			if(parent_w>0&&imgs_wrap.this_w>parent_w)
			{				
				var new_width=parent_w-20;
				imgs_wrap.this_el.addClass('slideshow2_imgs_wrap_responsive').css({'width':new_width+'px'});
				var slide_width=new_width*imgs_arr.imgs.length;
				imgs_arr.this_el.css({'width':slide_width+'px'});
				$.each(imgs_arr.imgs, function(j,img_el){
					var prop=img_el.this_h/img_el.this_w;
					var new_height=new_width*prop;
					$bg=real_imgs[j].width>new_width||real_imgs[j].height>new_height?'contain':'inherit';
					img_el.this_el.css({'width':new_width+'px','height':new_height+'px','background-size':$bg});
				});
				if(thumb_nav_arr.this_el != '')
					thumb_nav_arr.this_el.attr('style', 'width:'+new_width+'px !important;');
						
				set_maxwidth(this.obj);
				switchslide(this.obj,10);	
			}
			else if(imgs_wrap.this_el.hasClass('slideshow2_imgs_wrap_responsive'))
			{
				imgs_wrap.this_el.removeClass('slideshow2_imgs_wrap_responsive').css({'width':imgs_wrap.this_w+'px'});
				imgs_arr.this_el.css({'width':imgs_arr.this_w+'px'});
				$.each(imgs_arr.imgs, function(j,img_el){
					$bg=real_imgs[j].width>img_el.this_w||real_imgs[j].height>img_el.this_h?'contain':'inherit';
					img_el.this_el.css({'width':img_el.this_w+'px','height':img_el.this_h+'px'});
				});
				if(thumb_nav_arr.this_el != '')
					thumb_nav_arr.this_el.attr('style', 'width:'+imgs_wrap.this_w+'px !important;');
					
				set_maxwidth(this.obj);
				switchslide(this.obj,10);
				lazy_load_thumbs(this.obj,0);			
			}
		}
	});
}

function set_responsive_slideshow2_arrays()
{
	var slideshow_content=$(".slideshow2_imgs_wrap"), thumb_wrap=$(".slideshow2_thumb_wrap"), sldieshow_imgs=$(".slideshow2_imgs_navigation"), slideshow2_ct=$(".slideshow2_ct");
	if(sldieshow_imgs.length==0||slideshow_content.length==0||thumb_wrap.length==0||slideshow2_ct.length==0
	||slideshow_content.length!==thumb_wrap.length||slideshow_content.length!==slideshow2_ct.length||slideshow_content.length!==sldieshow_imgs.length) return false;	
	slideshow_content.each(function(i,el)
	{
		var this_id=$(el).attr('id').split('slideshow2_imgs_wrap').join('');
		var slideshow2_imgs_wrap_array=[], slideshow2_imgs_array=[], thumb_nagivation_array=[];
		var slide_ct=slideshow2_ct[i];
		var slide_img=sldieshow_imgs[i];
		var parent_slide_ct=get_parent(slide_ct);
		
		slideshow2_imgs_wrap_array.push({'this_el':$(el),'this_w':$(el).width(),'this_parent':parent_slide_ct});
		var thumb_nav=thumb_wrap[i];
		if($(thumb_nav).length){
			thumb_nagivation_array.push({'this_el':$(thumb_nav)});
		}else
			thumb_nagivation_array.push({'this_el':''});
			
		var imgs_array=[];
		$(slide_img).find('div').each(function(j,img){
			imgs_array.push({'this_el':$(img),'this_w':$(img).width(),'this_h':$(img).height()});
		});
		slideshow2_imgs_array.push({'this_el':$(slide_img),'this_w':$(slide_img).width(),'imgs':imgs_array});
		
		$.each(window.slideshow2_all_objects, function(j,v){
			if(this.obj.id==this_id&&!('slideshow2_imgs_wrap_array' in window.slideshow2_all_objects[j])){
				window.slideshow2_all_objects[j]['slideshow2_imgs_wrap_array']=slideshow2_imgs_wrap_array;
				window.slideshow2_all_objects[j]['thumb_nagivation_array']=thumb_nagivation_array;
				window.slideshow2_all_objects[j]['slideshow2_imgs_array']=slideshow2_imgs_array;
			}
		})
	});
	return true;
}

function exist_slideshow2(id)
{
	var exist_id=false;
	$.each(window.slideshow2_all_objects, function(i,v){
		if(v.obj.id==id){
			exist_id=true;
			return false;
		}
	});
	return exist_id;
}

$(window).load(function() {
	if(set_responsive_slideshow2_arrays()===false) return;
	add_responsive_slideshow2();
	$(window).smartresize(function(){add_responsive_slideshow2();});
});
//---End responsive functionality---//

function SlideShow(slides,maxh,maxw,id,r,options,slideshow_plugin)
{	
	if(exist_slideshow2(id)) return;
	$(".s_"+id+"_thumb").removeClass('slideshow2_old_imgs');
	
	options = typeof options !== 'undefined' ? options : [];
	slideshow_plugin = typeof slideshow_plugin !== 'undefined' ? slideshow_plugin : false;
	var len_options = $.map(options, function(n, i) { return i; }).length;
	var object_ss=this;
	
	if(len_options>0){
		object_ss.slideShowSpeed=options.slideShowSpeed;
		object_ss.swipeSpeed=options.swipeSpeed;
	}else{
		object_ss.slideShowSpeed=5000;
		object_ss.swipeSpeed=500;
	}
	object_ss.thumb_width=slideshow_plugin?42:62;
	object_ss.swipe_thumb_counter=1;
	object_ss.swipe_thumbs_distance=0;
	window.slideshow2_index++;
	object_ss.idx=window.slideshow2_index;
	object_ss.count_images=slides.length;
	object_ss.lazyload=object_ss.count_images>window.min_lazyLoad;
	if(!$("link[title='font-awesome']").length)
		$('<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" title="font-awesome" />').appendTo('head');
	object_ss.slides=slides;object_ss.maxwidth=maxw;object_ss.maxheight=maxh;object_ss.currentindex=0;object_ss.id=id;object_ss.maxwidth_thumbcont=maxw;object_ss.maxw_old=maxw;
	
	object_ss.iid=$('#arunmainview'+id).length?document.getElementById('arunmainview'+id):"img[name=mainview"+id+"]";
	$(object_ss.iid).removeClass('slideshow2_old_imgs');
	mab=$(object_ss.iid).css('margin-bottom');
	mat=$(object_ss.iid).css('margin-top');
	mar=$(object_ss.iid).css('margin-right');
	mal=$(object_ss.iid).css('margin-left');
	var images_all='';
	$.each(object_ss.slides, function(i,img){
		images_all+='<div class="slideshow2_imgs'+id+' img_'+id+'_'+i+(object_ss.lazyload?' lazyLoaderBg':'')+'" title="'+img.title+'" style="width:'+object_ss.maxwidth+'px; height:'+object_ss.maxheight+'px;'+
		(object_ss.lazyload?'" data-src="url(\''+img.url+'\')"':'background-image:url(\''+img.url+'\');"')+'></div>';
	});
	$(object_ss.iid).css({'display':'none'}).after(images_all);
	lazy_load(object_ss,'',0);
	
	$('.slideshow2_imgs'+id).wrapAll('<div class="slideshow2_imgs_navigation" id="slideshow2_imgs_navigation'+id+'" style="width:'+(object_ss.maxwidth*object_ss.slides.length)+'px;" />');
	$('#slideshow2_imgs_navigation'+id).wrap('<div class="slideshow2_imgs_wrap" id="slideshow2_imgs_wrap'+id+'"  style="height:'+'auto'+'; width:'+object_ss.maxwidth+'px;margin:'+mat+' '+mar+' '+mab+' '+mal+'" />');
	
	var slideshow2_imgs_wrap=$("#slideshow2_imgs_wrap"+id);
        
	slideshow2_imgs_wrap.swipe({
		triggerOnTouchEnd: true,
		allowPageScroll: "vertical",
		threshold: 75,
		swipeStatus:function(event, phase, direction, distance)
		{
			set_maxwidth(object_ss);
			if (phase == "move" && (direction == "left" || direction == "right")) {
				var duration = 0;
				if (direction == "left") {
					swipeImageContainer(object_ss.maxwidth * object_ss.currentindex + distance, duration, id);
				} else if (direction == "right") {
					swipeImageContainer(object_ss.maxwidth * object_ss.currentindex - distance, duration, id);
				}
			}
			else if (phase == "cancel") {
                swipeImageContainer(object_ss.maxwidth *  object_ss.currentindex, object_ss.slideShowSpeed, id);
            } else if (phase == "end") {
                if (direction == "right") {
                    previousImage(object_ss,false);
					scoll_toThumb(id,object_ss,'prev');
                } else if (direction == "left") {
                    nextImage(object_ss,false);
					scoll_toThumb(id,object_ss,'next');
					lazy_load(object_ss,1,'');
                }
            }
		}
	});

	var caption_span=$("#captionDiv_"+id);
	var container=$('<div class="slideshow2_ct slideshow2_'+id+'"><div class="slideshow2_main_'+id+'"></div><div class="slideshow2_cap"><div class="slideshow2_btns nav_'+id+'"></div><div class="slideshow2_title cap_'+id+'"></div></div><div class="slideshow2_thumbs_'+id+'"></div></div>');

	var inner_table=$('#arunmainview'+id+',#mainview'+id).closest('table');
	var main_table=$('.s_'+id+'_thumb').first().closest('table');
	//inside table
	if(main_table.length>0 && inner_table.length>0 && $(inner_table).parents('table').attr('id')==$(main_table).attr('id')) {
		$(main_table).before(container);
		$('.slideshow2_'+id).attr('id',$(main_table).attr('id'));
	}
	else
		$('#arunmainview'+id+',#mainview'+id).before(container);

	$('#arunmainview'+id+',#mainview'+id).appendTo('.slideshow2_main_'+id);
	slideshow2_imgs_wrap.appendTo('.slideshow2_main_'+id);

	if(caption_span.length==1)
	{
		$(caption_span).appendTo('.cap_'+id);

		$('<a class="slideshow2_navima" href="javascript:void Prev(slides'+id+'_obj,'+id+');" data-edit="1"><i class="fa fa-chevron-left fa-2x"></i></a>').appendTo('.nav_'+id);
		$('<a class="slideshow2_navima" href="javascript:void Next(slides'+id+'_obj,'+id+');"  data-edit="1"><i class="fa fa-chevron-right fa-2x"></i></a>').appendTo('.nav_'+id);
		$('<a class="slideshow2_navima" href="javascript:void RunShow(slides'+id+'_obj,'+id+');" data-edit="1"><i class="fa fa-play fa-2x stopstart_'+id+'"></i></a>').appendTo('.nav_'+id);
		$('.slideshow2_main_'+id +'.slideshow2_navima').each(function(){
			$(this).bind('touchstart',function(){
				$(this).addClass('slideshow2_hoverClass');
			});
			$(this).bind('touchend',function(){
				$(this).removeClass('slideshow2_hoverClass');
			});
			$(this).mousedown('click', function(){
				$(this).addClass("slideshow2_hoverClass");
			});
			$(this).mouseup(function(){
				$(this).removeClass("slideshow2_hoverClass");
			});
		});
	}
	else
       $('.slideshow2_'+id+' .slideshow2_cap').hide();


	buildSwipeThumbnails(object_ss,id,maxw);

	if(main_table.length>0)
       $(main_table).remove();
	setopa(id,0);
	caption(object_ss,id,object_ss.currentindex);
}

function buildSwipeThumbnails(object_ss,id,maxw)
{
	$(".s_"+id+"_thumb").each(function(i){
		if(i==object_ss.count_images) return false;
		$(this).wrap("<div class='d_"+id+"_thumb tid_div_"+id+"_"+i+"' />");
		var attrib=object_ss.lazyload?'data-src':'style';
		var value=object_ss.lazyload?'url(\''+$(this).attr('src')+'\')':'background-image:url(\''+$(this).attr('src')+'\'); opacity:1';
		$(".tid_div_"+id+"_"+i).click( function() {
			if(object_ss.swipe_thumbs_distance==0)
				openCI(object_ss,i);
			object_ss.swipe_thumbs_distance=0;
		 }).addClass("slideshow2_thumb_div").attr(attrib,value).attr('data-fixed','1').appendTo('.slideshow2_thumbs_'+id);
		  
	});
	
	max_width_thumb_container=object_ss.count_images*object_ss.thumb_width;
	$('.slideshow2_thumbs_'+id).addClass('slideshow2_thumb_navigation slideshow2_thumb_navigation'+id).css("width",max_width_thumb_container+'px').attr({'align':'center','id':'thumb_tr_'+id});
	
	var slideshow2_thumb_navigation=$(".slideshow2_thumb_navigation"+id);
	if(slideshow2_thumb_navigation.length>1){
		var first_thumb_navig='';
		slideshow2_thumb_navigation.each(function(i,el){
			if(i==0) first_thumb_navig = $(el);
			else{
				$.each($(el).find('td'), function(){
					first_thumb_navig.append($(this));
				});
				$(el).remove();
			}
		});
	}

	slideshow2_thumb_navigation.wrapAll("<div class='slideshow2_thumb_wrap' id='slideshow2_thumb_wrap"+id+"' style='width:"+object_ss.maxwidth_thumbcont+"px' />");
	object_ss.current_thumb=0;
	var swipe_directon='';
	var swipe_distance=0;
	var thumbnails_wrap=$("#slideshow2_thumb_wrap"+id);
	
	lazy_load_thumbs(object_ss,0);
	thumbnails_wrap.mouseleave(function(){
		set_maxwidth(object_ss);
		if(swipe_directon=='left'){
			min_1=Math.min(object_ss.maxwidth_thumbcont/object_ss.thumb_width, swipe_distance/object_ss.thumb_width);
			object_ss.current_thumb = Math.min(object_ss.current_thumb + min_1, ((object_ss.count_images*object_ss.thumb_width)-object_ss.maxwidth_thumbcont)/object_ss.thumb_width);
			swipeThumbContainer(object_ss.thumb_width*object_ss.current_thumb, object_ss.swipeSpeed, id);
		}else if(swipe_directon=='right'){
			min_2 = Math.min(object_ss.maxwidth_thumbcont/object_ss.thumb_width, swipe_distance/object_ss.thumb_width);
			object_ss.current_thumb = Math.max(object_ss.current_thumb - min_2,0);
			swipeThumbContainer(object_ss.thumb_width*object_ss.current_thumb, object_ss.swipeSpeed, id);
		}
		$(this).swipe("disable");
	});
	
	thumbnails_wrap.mouseover(function(){
		$(this).swipe("enable");
	});
	thumbnails_wrap.on('touchstart', function(){
		$(this).swipe("enable");
	});

	thumbnails_wrap.swipe({
		triggerOnTouchEnd: true,
		allowPageScroll: "vertical",
		threshold: 3,
		swipeStatus:function(event, phase, direction, distance) {
			set_maxwidth(object_ss);
			if (phase == "move" &&(direction == "left" || direction == "right")) {
				var duration = 0;
				if (direction == "left") {
					swipeThumbContainer((object_ss.thumb_width*object_ss.current_thumb)+distance, duration, id);
					swipe_directon=direction;
					swipe_distance=distance;
				} else if (direction == "right") {
					swipeThumbContainer((object_ss.thumb_width*object_ss.current_thumb)-distance, duration, id);
					swipe_directon=direction;
					swipe_distance=distance;
				}
				object_ss.swipe_thumbs_distance=distance;
				lazy_load_thumbs(object_ss,distance);
			}
			else if (phase == "cancel") {
				object_ss.swipe_thumbs_distance=0;
				swipeThumbContainer(object_ss.thumb_width*object_ss.current_thumb, object_ss.swipeSpeed, id);
				swipe_directon='';
				swipe_distance=0;
			} else if (phase == "end") {
					swipe_directon='';
					swipe_distance=0;
				if (direction == "right") {
					min_2 = Math.min(object_ss.maxwidth_thumbcont/object_ss.thumb_width, distance/object_ss.thumb_width);
					object_ss.current_thumb = Math.max(object_ss.current_thumb - min_2,0);
					swipeThumbContainer(object_ss.thumb_width*object_ss.current_thumb, object_ss.swipeSpeed, id);				
				} else if (direction == "left") {
					min_1=Math.min(object_ss.maxwidth_thumbcont/object_ss.thumb_width, distance/object_ss.thumb_width);
					object_ss.current_thumb = Math.min(object_ss.current_thumb + min_1, ((object_ss.count_images*object_ss.thumb_width)-object_ss.maxwidth_thumbcont)/object_ss.thumb_width);
					swipeThumbContainer(object_ss.thumb_width*object_ss.current_thumb, object_ss.swipeSpeed, id);
				}
			}
		}
	});
	all_next_notuse_tr=thumbnails_wrap.nextAll('tr');
	all_next_notuse_tr.each(function(){
	if($(this).find('img').length==0){
			$(this).remove();
		}
	});
	window.slideshow2_all_objects.push({'obj':object_ss});
}

function Slide(url,h,w,title,caption){
	this.url=url;
	this.title=title;
	this.caption = typeof caption != 'undefined' ? caption : title;
	this.height=h;
	this.width=w;
}

function openCI(ss,index)
{
	if($(".slideshow2_"+ss.id).length==0) return;
	killss(ss);
	sliding=false;
	ss.currentindex=index;
	window.slideshow2_all_objects[ss.idx].currentindex=ss.currentindex;
	lazy_load(ss,'','');
	switchslide(ss);
}
function showCI(ss,index){openCI(ss,index);}
function caption(ss,id,index){$('#captionDiv_'+id).html(ss.slides[index].caption);}
function set_maxwidth(sl)
{
	if($("#slideshow2_imgs_wrap"+sl.id).hasClass('slideshow2_imgs_wrap_responsive')){
		sl.maxwidth=$("#slideshow2_imgs_navigation"+sl.id).find('div').width();
		sl.maxwidth_thumbcont=$("#slideshow2_thumb_wrap"+sl.id).width();
	}
	else{
		sl.maxwidth=sl.maxw_old;
		sl.maxwidth_thumbcont=sl.maxw_old;
	}
}
function Next(sl,id){set_maxwidth(sl);nextImage(sl,false);scoll_toThumb(id,sl,'next');}
function Prev(sl,id){set_maxwidth(sl);previousImage(sl,false);scoll_toThumb(id,sl,'prev');}
function setopa(sid,id){$(".d_"+sid+"_thumb").removeClass("slideshow2_thumb_selected");$(".tid_div_"+sid+"_"+id).addClass("slideshow2_thumb_selected");}
function killss(ss){clearTimeout(t);startstop_icon('start',ss.id);}
function startstop_icon(action,id)
{
	if($(".stopstart_"+id).length){
		if(action=='start')
			$(".stopstart_"+id).removeClass('fa-stop').addClass('fa-play');
		else
			$(".stopstart_"+id).removeClass('fa-play').addClass('fa-stop');
	}
}
function RunShow(sl,id){runSlideShow(sl,true,id);}
function runSlideShow(ss,startshow,id)
{
	if(startshow)
	{
		ssl=document.images['startstop'+lastshow_id];
		if((lastshow_id != id)&&(lastshow_id != -1)&&(sliding))
		{
			slideshow=ss;
			lastshow_id=id;
			startstop_icon('stop',ss.id);
			return;
		}
		if(sliding)
		{
			sliding=false;
			startstop_icon('start',ss.id);
			return;
		}
		else
		{
			slideshow=ss;
			sliding=true;lastshow_id=id;
			startstop_icon('stop',ss.id);

		}
	}
	if(sliding)
	{
		nextImage(slideshow,true);
		t=setTimeout('runSlideShow(slideshow,false,lastshow_id)',ss.slideShowSpeed);
	}
}
/*Lazy load plugin for slideshow2*/
$.fn.lazyLoad = function() {
	var el = this;
	if($(el).length==0) return el;
	var attrib = "data-src",
	source = $(el).attr(attrib);
	if (source==null) return el;
	var image_url=source.replace(/^url\(["']?/, '').replace(/["']?\)$/, ''),
	img = new Image();
	img.src = image_url;
	img.onload = function(){
		$(el).css("background-image", source).removeClass('lazyLoaderBg').removeAttr(attrib);
	}
	return el;
};

/*
* @fileOverview TouchSwipe - jQuery Plugin
* @version 1.6.6
*
* @author Matt Bryson http://www.github.com/mattbryson
* @see https://github.com/mattbryson/TouchSwipe-Jquery-Plugin
* @see http://labs.skinkers.com/touchSwipe/
* @see http://plugins.jquery.com/project/touchSwipe
*
* Copyright (c) 2010 Matt Bryson
* Dual licensed under the MIT or GPL Version 2 licenses.
*
*/
(function(m){"function"===typeof define&&define.amd&&define.amd.jQuery?define(["jquery"],m):m(jQuery)})(function(m){function R(e){!e||void 0!==e.allowPageScroll||void 0===e.swipe&&void 0===e.swipeStatus||(e.allowPageScroll="none");void 0!==e.click&&void 0===e.tap&&(e.tap=e.click);e||(e={});e=m.extend({},m.fn.swipe.defaults,e);return this.each(function(){var a=m(this),q=a.data("TouchSwipe");q||(q=new S(this,e),a.data("TouchSwipe",q))})}function S(e,a){function q(b){if(!0!==c.data("TouchSwipe_intouch")&&
!(0<m(b.target).closest(a.excludedElements,c).length)){var d=b.originalEvent?b.originalEvent:b,k,ma=s?d.touches[0]:d;g="start";s?h=d.touches.length:b.preventDefault();n=0;w=p=null;y=z=l=0;t=1;x=0;f=R();H=S();T=I=0;if(!s||h===a.fingers||"all"===a.fingers||E()){if(U(0,ma),J=A(),2==h&&(U(1,d.touches[1]),z=y=V(f[0].start,f[1].start)),a.swipeStatus||a.pinchStatus)k=u(d,g)}else k=!1;if(!1===k)return g="cancel",u(d,g),k;a.hold&&(W=setTimeout(m.proxy(function(){c.trigger("hold",[d.target]);a.hold&&(k=a.hold.call(c,
d,d.target))},this),a.longTapThreshold));K(!0);return null}}function X(b){var d,k,c,e=b.originalEvent?b.originalEvent:b;if("end"!==g&&"cancel"!==g&&!ea()){var q,v=fa(s?e.touches[0]:e);F=A();s&&(h=e.touches.length);a.hold&&clearTimeout(W);g="move";2==h&&(0==z?(U(1,e.touches[1]),z=y=V(f[0].start,f[1].start)):(fa(e.touches[1]),y=V(f[0].end,f[1].end),w=1>t?"out":"in"),t=(y/z*1).toFixed(2),x=Math.abs(z-y));if(h===a.fingers||"all"===a.fingers||!s||E()){d=v.start;k=v.end;d=Math.atan2(k.y-d.y,d.x-k.x);d=
Math.round(180*d/Math.PI);0>d&&(d=360-Math.abs(d));d=p=45>=d&&0<=d?"left":360>=d&&315<=d?"left":135<=d&&225>=d?"right":45<d&&135>d?"down":"up";if("none"===a.allowPageScroll||E())b.preventDefault();else switch(k="auto"===a.allowPageScroll,d){case "left":(a.swipeLeft&&k||!k&&"horizontal"!=a.allowPageScroll)&&b.preventDefault();break;case "right":(a.swipeRight&&k||!k&&"horizontal"!=a.allowPageScroll)&&b.preventDefault();break;case "up":(a.swipeUp&&k||!k&&"vertical"!=a.allowPageScroll)&&b.preventDefault();
break;case "down":(a.swipeDown&&k||!k&&"vertical"!=a.allowPageScroll)&&b.preventDefault()}b=v.start;d=v.end;n=Math.round(Math.sqrt(Math.pow(d.x-b.x,2)+Math.pow(d.y-b.y,2)));l=F-J;b=p;d=n;d=Math.max(d,ga(b));H[b].distance=d;if(a.swipeStatus||a.pinchStatus)q=u(e,g);if(!a.triggerOnTouchEnd||a.triggerOnTouchLeave){b=!0;if(a.triggerOnTouchLeave){c=m(this);var r=c.offset();b=r.left;d=r.left+c.outerWidth();k=r.top;c=r.top+c.outerHeight();v=v.end;b=v.x>b&&v.x<d&&v.y>k&&v.y<c}!a.triggerOnTouchEnd&&b?g=Y("move"):
a.triggerOnTouchLeave&&!b&&(g=Y("end"));"cancel"!=g&&"end"!=g||u(e,g)}}else g="cancel",u(e,g);!1===q&&(g="cancel",u(e,g))}}function Z(b){var d=b.originalEvent;ea()&&(h=T);F=A();l=F-J;$()||!aa()?(g="cancel",u(d,g)):a.triggerOnTouchEnd||0==a.triggerOnTouchEnd&&"move"===g?(b.preventDefault(),g="end",u(d,g)):!a.triggerOnTouchEnd&&a.tap?(g="end",B(d,g,"tap")):"move"===g&&(g="cancel",u(d,g));K(!1);return null}function C(){y=z=J=F=h=0;t=1;T=I=0;K(!1)}function ba(b){b=b.originalEvent;a.triggerOnTouchLeave&&
(g=Y("end"),u(b,g))}function ha(){c.unbind(L,q);c.unbind(M,C);c.unbind(ca,X);c.unbind(da,Z);D&&c.unbind(D,ba);K(!1)}function Y(b){var d=b,c=a.maxTimeThreshold?l>=a.maxTimeThreshold?!1:!0:!0,f=aa(),e=$();!c||e?d="cancel":!f||"move"!=b||a.triggerOnTouchEnd&&!a.triggerOnTouchLeave?!f&&"end"==b&&a.triggerOnTouchLeave&&(d="cancel"):d="end";return d}function u(b,d){var c=void 0;ia()&&ja()||ja()?c=B(b,d,"swipe"):(ka()&&E()||E())&&!1!==c&&(c=B(b,d,"pinch"));la()&&a.doubleTap&&!1!==c?c=B(b,d,"doubletap"):
l>a.longTapThreshold&&10>n&&a.longTap&&!1!==c?c=B(b,d,"longtap"):(1===h||!s)&&(isNaN(n)||n<a.threshold)&&a.tap&&!1!==c&&(c=B(b,d,"tap"));"cancel"===d&&C(b);"end"===d&&(s?0==b.touches.length&&C(b):C(b));return c}function B(b,d,k){var e=void 0;if("swipe"==k){c.trigger("swipeStatus",[d,p||null,n||0,l||0,h,f]);if(a.swipeStatus&&(e=a.swipeStatus.call(c,b,d,p||null,n||0,l||0,h,f),!1===e))return!1;if("end"==d&&ia()){c.trigger("swipe",[p,n,l,h,f]);if(a.swipe&&(e=a.swipe.call(c,b,p,n,l,h,f),!1===e))return!1;
switch(p){case "left":c.trigger("swipeLeft",[p,n,l,h,f]);a.swipeLeft&&(e=a.swipeLeft.call(c,b,p,n,l,h,f));break;case "right":c.trigger("swipeRight",[p,n,l,h,f]);a.swipeRight&&(e=a.swipeRight.call(c,b,p,n,l,h,f));break;case "up":c.trigger("swipeUp",[p,n,l,h,f]);a.swipeUp&&(e=a.swipeUp.call(c,b,p,n,l,h,f));break;case "down":c.trigger("swipeDown",[p,n,l,h,f]),a.swipeDown&&(e=a.swipeDown.call(c,b,p,n,l,h,f))}}}if("pinch"==k){c.trigger("pinchStatus",[d,w||null,x||0,l||0,h,t,f]);if(a.pinchStatus&&(e=a.pinchStatus.call(c,
b,d,w||null,x||0,l||0,h,t,f),!1===e))return!1;if("end"==d&&ka())switch(w){case "in":c.trigger("pinchIn",[w||null,x||0,l||0,h,t,f]);a.pinchIn&&(e=a.pinchIn.call(c,b,w||null,x||0,l||0,h,t,f));break;case "out":c.trigger("pinchOut",[w||null,x||0,l||0,h,t,f]),a.pinchOut&&(e=a.pinchOut.call(c,b,w||null,x||0,l||0,h,t,f))}}if("tap"==k){if("cancel"===d||"end"===d)clearTimeout(N),clearTimeout(W),a.doubleTap&&!la()?(r=A(),N=setTimeout(m.proxy(function(){r=null;c.trigger("tap",[b.target]);a.tap&&(e=a.tap.call(c,
b,b.target))},this),a.doubleTapThreshold)):(r=null,c.trigger("tap",[b.target]),a.tap&&(e=a.tap.call(c,b,b.target)))}else if("doubletap"==k){if("cancel"===d||"end"===d)clearTimeout(N),r=null,c.trigger("doubletap",[b.target]),a.doubleTap&&(e=a.doubleTap.call(c,b,b.target))}else"longtap"!=k||"cancel"!==d&&"end"!==d||(clearTimeout(N),r=null,c.trigger("longtap",[b.target]),a.longTap&&(e=a.longTap.call(c,b,b.target)));return e}function aa(){var b=!0;null!==a.threshold&&(b=n>=a.threshold);return b}function $(){var b=
!1;null!==a.cancelThreshold&&null!==p&&(b=ga(p)-n>=a.cancelThreshold);return b}function ka(){var b=h===a.fingers||"all"===a.fingers||!s,d=0!==f[0].end.x,c;c=null!==a.pinchThreshold?x>=a.pinchThreshold:!0;return b&&d&&c}function E(){return!!(a.pinchStatus||a.pinchIn||a.pinchOut)}function ia(){var b=a.maxTimeThreshold?l>=a.maxTimeThreshold?!1:!0:!0,d=aa(),c=h===a.fingers||"all"===a.fingers||!s,e=0!==f[0].end.x;return!$()&&e&&c&&d&&b}function ja(){return!!(a.swipe||a.swipeStatus||a.swipeLeft||a.swipeRight||
a.swipeUp||a.swipeDown)}function la(){if(null==r)return!1;var b=A();return!!a.doubleTap&&b-r<=a.doubleTapThreshold}function ea(){var b=!1;I&&A()-I<=a.fingerReleaseThreshold&&(b=!0);return b}function K(b){!0===b?(c.bind(ca,X),c.bind(da,Z),D&&c.bind(D,ba)):(c.unbind(ca,X,!1),c.unbind(da,Z,!1),D&&c.unbind(D,ba,!1));c.data("TouchSwipe_intouch",!0===b)}function U(b,a){f[b].identifier=void 0!==a.identifier?a.identifier:0;f[b].start.x=f[b].end.x=a.pageX||a.clientX;f[b].start.y=f[b].end.y=a.pageY||a.clientY;
return f[b]}function fa(b){var a=void 0!==b.identifier?b.identifier:0;a:{for(var c=0;c<f.length;c++)if(f[c].identifier==a){a=f[c];break a}a=void 0}a.end.x=b.pageX||b.clientX;a.end.y=b.pageY||b.clientY;return a}function R(){for(var b=[],a=0;5>=a;a++)b.push({start:{x:0,y:0},end:{x:0,y:0},identifier:0});return b}function ga(a){if(H[a])return H[a].distance}function S(){var a={};a.left=O("left");a.right=O("right");a.up=O("up");a.down=O("down");return a}function O(a){return{direction:a,distance:0}}function V(a,
c){var e=Math.abs(a.x-c.x),f=Math.abs(a.y-c.y);return Math.round(Math.sqrt(e*e+f*f))}function A(){return(new Date).getTime()}var P=s||G||!a.fallbackToMouseEvents,L=P?G?Q?"MSPointerDown":"pointerdown":"touchstart":"mousedown",ca=P?G?Q?"MSPointerMove":"pointermove":"touchmove":"mousemove",da=P?G?Q?"MSPointerUp":"pointerup":"touchend":"mouseup",D=P?null:"mouseleave",M=G?Q?"MSPointerCancel":"pointercancel":"touchcancel",n=0,p=null,l=0,z=0,y=0,t=1,x=0,w=0,H=null,c=m(e),g="start",h=0,f=null,J=0,F=0,I=0,
T=0,r=0,N=null,W=null;try{c.bind(L,q),c.bind(M,C)}catch(na){m.error("events not supported "+L+","+M+" on jQuery.swipe")}this.enable=function(){c.bind(L,q);c.bind(M,C);return c};this.disable=function(){ha();return c};this.destroy=function(){ha();c.data("TouchSwipe",null);return c};this.option=function(b,c){if(void 0!==a[b]){if(void 0===c)return a[b];a[b]=c}else m.error("Option "+b+" does not exist on jQuery.swipe.options");return null}}var s="ontouchstart"in window,Q=window.navigator.msPointerEnabled&&
!window.navigator.pointerEnabled,G=window.navigator.pointerEnabled||window.navigator.msPointerEnabled;m.fn.swipe=function(e){var a=m(this),q=a.data("TouchSwipe");if(q&&"string"===typeof e){if(q[e])return q[e].apply(this,Array.prototype.slice.call(arguments,1));m.error("Method "+e+" does not exist on jQuery.swipe")}else if(!(q||"object"!==typeof e&&e))return R.apply(this,arguments);return a};m.fn.swipe.defaults={fingers:1,threshold:75,cancelThreshold:null,pinchThreshold:20,maxTimeThreshold:null,fingerReleaseThreshold:250,
longTapThreshold:500,doubleTapThreshold:200,swipe:null,swipeLeft:null,swipeRight:null,swipeUp:null,swipeDown:null,swipeStatus:null,pinchIn:null,pinchOut:null,pinchStatus:null,click:null,tap:null,doubleTap:null,longTap:null,hold:null,triggerOnTouchEnd:!0,triggerOnTouchLeave:!1,allowPageScroll:"auto",fallbackToMouseEvents:!0,excludedElements:"label, button, input, select, textarea, a, .noSwipe"};m.fn.swipe.phases={PHASE_START:"start",PHASE_MOVE:"move",PHASE_END:"end",PHASE_CANCEL:"cancel"};m.fn.swipe.directions=
{LEFT:"left",RIGHT:"right",UP:"up",DOWN:"down",IN:"in",OUT:"out"};m.fn.swipe.pageScroll={NONE:"none",HORIZONTAL:"horizontal",VERTICAL:"vertical",AUTO:"auto"};m.fn.swipe.fingers={ONE:1,TWO:2,THREE:3,ALL:"all"}});
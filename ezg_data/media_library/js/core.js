window.searchTimer = null;
window.videoPlayer = null;
window.forceRefresh=false;
$(document).ready(function() {
  if(window.location.toString().indexOf('&protect')!=-1 ||
          window.location.toString().indexOf('&unprotect')!=-1) {
   window.location = window.location.toString().replace('&protect','').replace('&unprotect','');
	}
	prepareActions();
	$('#resizeChecker').change(toggleResize);
	$('#resizeVal').keyup(function(){
		toggleResize.call($('#resizeChecker').get(0));
	});
	$('.typeFilter').click(function(){
		$('.typeFilter').removeClass('underlined');
		$(this).addClass('underlined');
		$('#media_type').val($(this).attr('rel'));
		window.forceRefresh=true;
		postData();
	});
	$('.vmode').click(function() {
		$('.selectedFilter').removeClass('selectedFilter');
		$(this).addClass('selectedFilter');
		$('#vmode').val($(this).attr("rel"));
		$('.nav_active').text(1);
		postData();
	});
	$('#libsearch').keyup(doTheSearch);
	toggleResize.call($('#resizeChecker').get(0));
	window.videoPlayer = videojs("MLVideo", {techOrder:['html5','flash']}, function() {
		this.on("ended", function () {
			$('.video_container').hide('slow');
		});
	});
	$('.uploadOpener').click(function(){
		$(this).toggleClass('gone');
		$('.fileuploader').toggleClass('gone');
	});
});


//actions that need to be re-assigned every time new data is set on the page
//(elements inside the container)
function prepareActions()
{
	$('#checkall').change(function() {
		$('.mediacheckbox').prop('checked', $(this).prop('checked'));
	});
	$('.delete_btn').click(function() {
		if(!confirm('Do you really want to delete selected items?')) return false;
		var media = $('.mediacheckbox:checked').map(function(i, n) {
			return $(n).val();
		}).get();
		postData(media,'delSelected');
		$('#checkall').prop('checked',false);
	});
	$('.addFldr_btn').click(function(){
		var fldrName = prompt('New folder name:');
		if(fldrName===null) return;
		fldrName=fldrName.replace(/[^a-zA-Z0-9_\x7f-\xff]+/g,'_');
		postData(null,'createFolder',fldrName);
	});
	$('.protect_folder').click(function() {
    window.location += '&protect';
	});
	$('.unprotect_folder').click(function() {
    window.location += '&unprotect';
	});
	$('.nav').click(function(e){
		e.preventDefault();
		if($(this).hasClass('nav_next'))
			$(this).text(parseInt($('.nav_active:first').text())+1);
		if($(this).hasClass('nav_prev'))
			$(this).text(parseInt($('.nav_active:first').text())-1);
		$('.nav').removeClass('nav_active');
		$(this).addClass('nav_active');
		postData();
	});
	$('.mediafile').hover(function(){
		$(this).addClass('hovered');
		$('.entrynav',this).toggleClass('hidden');
	},
	function() {
		$(this).removeClass('hovered');
		$('.entrynav',this).toggleClass('hidden');
	});
	if($('.nav_active').text()==='1'||$('#vmode').val()!=='thumb')
		$('.delBtn').click(handleDelBtn);
	else
		$('.delBtn.recentlyAdded').click(handleDelBtn);
	$('.delBtn').removeClass('recentlyAdded');
	$('.downloadBtn').click(function(e){
		e.preventDefault;
		e.stopPropagation;
		window.open($(this).attr('rel'),'_blank');
	});
	if($('.mbox').length)
	{
		if(typeof $('.mbox').multibox === 'function')
			$('.mbox').multibox({zicon:false});
		else if(typeof $('.mbox').nivoLightbox === 'function')
			$('.mbox').nivoLightbox({zicon:false});
	}
	$('.playBtn').click(function(e){
		loadMedia('media',$(this).attr('rel'),e,true);
	});
	$('.entryhref').click(function(e){
		//if($('img',this).length)
		//	loadMedia('img',$('img',this).attr('src').replace('thumbs/',''),e);
		/*else*/ if($('.playBtn',$(this).parents('td')).length)
			loadMedia('media',$('.playBtn',$(this).parents('td')).attr('rel'),e,true);
	});
	$('#showMoreButton').click(function(){
		$('.nav_active').text(parseInt($('.nav_active').text())+1);
		$(this).remove();
		postData();
	});
	/*$('.dir').click(function(e){
		e.preventDefault();
		e.stopPropagation();
		window.location=$(this).attr('href');
	});*/
    // styles regular and  thumb view in ca media library
    styleOldIe();
}

function handleDelBtn(e)
{

		e.preventDefault();
		var $parEl = $(this).parents('tr');
		if(!$parEl.length)
			$parEl = $(this).parents('.mediafile-thumb');
		$('.mediacheckbox',$parEl).prop('checked',true);
		$('.delete_btn').click();
		if($('#vmode').val()==='thumb')
		{
			$parEl.remove();
			window.forceRefresh=false;
		}
}

function loadMedia(type,data,event,animate)
{
	event.preventDefault();
	event.stopPropagation();
	if(animate)
		$('html, body').animate({scrollTop:0}, 'slow');
	if(type==='img')
	{
		$('.image_area','.image_container').html('<img src="'+data+'"/>');
		$('.image_container').show();
	}
	else if(type==='media')
	{
		var relData = data.split('|');
		$('.video_container').show('slow');
		window.videoPlayer.src({type: relData[0],src: relData[1]});
		window.videoPlayer.play();
	}
}

function doTheSearch()
{
	resetTimer();
}
function resetTimer() {
    clearTimeout(window.searchTimer);
    window.searchTimer = setTimeout(postData, 500);
};

function postData(media,action,fName)
{
	$data=new Object();
	$data.page=$('.nav_active:first').text();
	$data.loc=$('#loc').val();
	$data.search=$('#libsearch').val();
	$data.media_type=$('#media_type').val();
	$data.vmode=$('#vmode').val();
	if(media) $data['media[]']=media;
	if(action) $data.action=action;
	if(fName) $data.nFldName=fName;
	if($data.vmode==='thumb'&&$data.action==='delSelected')
		$.post($('#__file__').val(),$data);
	else
		$.post($('#__file__').val(),$data,refreshContent);
}
function refreshContent(data){
	if(data.indexOf('error_folder_ex:')!=-1){
		alert(data.split(':')[1]);
		return;
	}
	var $newHeading = $(".listheading",data).html();
	$(".listheading").html($newHeading);
	if($('#vmode').val()==='thumb' && !window.forceRefresh && $('.nav_active').hasClass('gone'))
	{
		var $newLits = $(".fileslist_inner",data).html().replace(/delBtn/g,'delBtn recentlyAdded');
		$('.remOnMore').remove();
		$('.fileslist_inner').append($newLits);
	}
	else
	{
		var $newLits = $(".fileslist",data).html();
		$(".fileslist").html($newLits);
	}
	var $newFooter = $(".listfooter",data).html();
	$(".listfooter").html($newFooter);
	prepareActions();
	window.forceRefresh=false;
}

function toggleResize()
{
	var $newRel = $('#upload_field').attr('rel').split('&resize')[0];;
	if($(this).prop('checked'))
		$newRel += '&resize='+$('#resizeVal').val();
	$('#upload_field').attr('rel',$newRel);
}
function styleOldIe() {
    var styled_old = {
        "max-width": "150%",
        "height": "auto",
        "border": "0px",
        "width": "100px",
        "overflow": "hidden"
    }, styled_new = {
        "height": "auto",
        "border": "0px",
        "overflow": "hidden"
    }, thumbs = {
        "position": "relative",
        "top": "-200px",
        "width": "95px",
        "z-ndex": "-10",
        "background-color": "silver",
        "opacity": "0.7",
        "overflow": "hidden"
    }, small_img_thumb = {
        "border": "0px"
    };

    var target_css = $(".img-thumb-big"), media_thumbs = $(".mediatext-thumb"), btn_new = $("#new_btn"), old_upload = $("#old_ies"), img_thumb = $(".img-thumb");
    if (window.File) {
        target_css.css(styled_new);
    } else {
        target_css.css(styled_old);
        img_thumb.css(small_img_thumb);
        media_thumbs.css(thumbs);
        btn_new.css("display", "none");
        old_upload.css("display", "block");
    }
}

$(function() {
	$("#upload_field").html5_upload({
		url: function(){return $('#upload_field').attr('rel');},
		sendBoundary: window.FormData || $.browser.mozilla,

		onStart: function(event, total) {
			//return true;
			return confirm("You are trying to upload " + total + " files. Are you sure?");
		},
		onProgress: function(event, progress, name, number, total) {
			console.log(progress, number);
		},
		setName: function(text) {
			$("#progress_report_name").text(text);
		},
		setStatus: function(text) {
			$("#progress_report_status").text(text);
		},
		setProgress: function(val) {
			$("#progress_report_bar").show().val(Math.ceil(val * 100));
		},
		onFinishOne: function(event, response, name, number, total) {
			window.forceRefresh=true;
			refreshContent(response);
			window.forceRefresh=false;
		},
		onFinish: function (event,total){
			$("#progress_report_bar").hide();
		},
		onError: function(event, name, error) {
			alert('error while uploading file ' + name);
		}
	});
});



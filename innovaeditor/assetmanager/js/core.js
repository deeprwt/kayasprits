window.searhTimer = null;
window.forceRefresh=false;
if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g, '');
  };
}

$(document).ready(function() {
	prepareActions();
	$('#resizeChecker').change(toggleResize);
	$('#resizeVal').keyup(function(){
		toggleResize.call($('#resizeChecker').get(0));
	});
	$('.typeFilter').click(function(){
		$('.typeFilter').removeClass('selectedFilter');
		$(this).addClass('selectedFilter');
		$('#media_type').val($(this).attr('rel'));
		window.forceRefresh=true;
		postData();
	});
	$('.vmode').click(function(){
		$('#vmode').val($(this).attr("rel"));
		$('.activeNav').text(1);
		postData();
		$('.vmode').removeClass('selectedFilter');
		$(this).addClass('selectedFilter');
	});
	$('#libsearch').keyup(doTheSearch);
	toggleResize.call($('#resizeChecker').get(0));

});

//actions that need to be re-assigned every time new data is set on the page
//(elements inside the container)
function prepareActions()
{
        //alert("Shown");
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
	$('.naventry').click(function(){
		$('.naventry').removeClass('activeNav');
		$(this).addClass('activeNav');
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
	$('.delBtn').unbind("click").click(handleDelBtn);
	/*$activeNavNum = $('.activeNav').text().trim();
	if($activeNavNum==='1' || $activeNavNum==='')
		$('.delBtn').click(handleDelBtn);
	else
		$('.delBtn.recentlyAdded').click(handleDelBtn);
	$('.delBtn').removeClass('recentlyAdded');*/


	$('.entryhref').click(function(e){
		var $ext=$(this).attr('href').replace(/\.\.\//g,'');
		if($ext.indexOf('&loc=')!==-1 || $ext === 'assetmanager.php?root=&id='+$('#fldId').val()) return;
		e.preventDefault();
		//var $base=location.protocol+'//'+location.host.replace(':80','')+location.pathname.replace('assetmanager/assetmanager.php','');
		var $base = $(this).attr('rel')+'innovaeditor/';
		var $fldId = $('#fldId').val();
		$('.entryhref').removeClass('recent').parent().removeClass('selectedData');
		$(this).addClass('recent').parent().addClass('selectedData');

		var media=$('.mediacheckbox:checked').map(function(i,n) {
			return $base+$ext.substring(0,$ext.lastIndexOf("/")+1)+ $(n).val();
		}).get();

                var selection=$base+$ext;
                if(media.length) selection=media;

		if($('#'+$fldId,window.parent.document).length) $('#mybox',window.parent.document).hide();
		if(window.parent.fileclick)
			window.parent.fileclick($base+$ext);
		if(opener)
		{
			if(window.opener && window.opener.tinyfck_field)
			{
				$('#'+window.opener.tinyfck_field, window.opener.tinyfck.document).val($base+$ext).focus(); /*Niki: focus input so that tiny script(from 4.2.1 version) will know for this change*/
				window.opener.tinyfck_field=false; //reset it (it must be set by editor each time)
			}
			else opener.setAssetValue(selection,$fldId);

			/*if($('#'+$fldId,opener.document).length) window.close();*/
			/*Joe: Not sure why above check was used. In case something is wrong, try to revert*/
			window.close();
		}
                else if(parent.setAssetValue) parent.setAssetValue(selection,$fldId);
	});

	if($('.entryhref.recent').length)
	{
		$('.entryhref.recent').focus();
		$('.fileslist').animate({scrollTop: $(".entryhref.recent").offset().top - 100},'slow');
		if(!opener)
			$('.entryhref.recent').click();
	}
	$('#showMoreButton').click(function(){
		$('.activeNav').text(parseInt($('.activeNav').text().trim())+1);
		$(this).remove();
		postData();
	});
        $(".img-thumb").css("border", "transparent");
        $(".img-thumb-big").css("border", "transparent");
}

function handleDelBtn(e)
{
		e.preventDefault();
		e.stopPropagation();
		var isThumbDel=$(this).hasClass('delThumb');
		if(isThumbDel)
		{
				$parEl = $(this).parents('.mediafile-thumb');
				if(!confirm('Do you really want to delete selected items?')) return false;
				var media=$(this).attr('rel');
				postData(media,'delSelected');
		}
		else
		{
			var $parEl = $(this).parents('.mediafile');
			$('.mediacheckbox',$parEl).prop('checked',true);
			$('.delete_btn').click();
		}
		if($('#vmode').val()==='thumb')
		{
			$parEl.remove();
			window.forceRefresh=false;
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
	$data.page=$('.activeNav').text().trim();
	$data.loc=$('#loc').val().trim();
	$data.search=$('#libsearch').val().trim();
	$data.media_type=$('#media_type').val().trim();
	$data.vmode=$('#vmode').val().trim();
	$data.root=$('#ezgRoot').val().trim();
	$data.id=$('#fldId').val().trim();
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
	var $newLits = $(".fileslist",data).html();
	if($('#vmode').val()==='thumb' && !window.forceRefresh && $('.nav_active').hasClass('gone'))
		$('.fileslist').append($newLits);
	else
		$(".fileslist").html($newLits);
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
			refreshContent(response);
		},
		onFinish: function (event,total){
			$("#progress_report_bar").hide();
		},
		onError: function(event, name, error) {
			alert('error while uploading file ' + name);
		}
	});
});



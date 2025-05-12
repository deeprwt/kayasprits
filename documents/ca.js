// online administration scripts

$(document).ready(function () {
	var isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0));
	$(".ca_toggle").click(isTouch ? 'touchend' : 'click', function(){
		var folded = $(this).hasClass("fa-chevron-right");
		if (folded){
			$(".a_body").removeClass("small");
			$(this).toggleClass('icon_right').toggleClass('fa-chevron-left')
			.removeClass('fa-chevron-right').removeClass('icon_left');
			setCookie("ca_folded", "0", "1000", "/");
		}
		else{
			$(".a_body").addClass("small");
			$(this).toggleClass('icon_left').toggleClass('fa-chevron-right')
			.removeClass('fa-chevron-left').removeClass('icon_right');
			setCookie("ca_folded", "1", "1000", "/");
		}
	});
});

function assign_edits() {
	 $(".autosize").each(function () {
		  if ($(this).val() != "")
				$(this).css("width", "auto");
	 });
	 $(".direct_edit").keydown(function () {
		  if ($(this).hasClass("autosize"))
				$(this).css("width", "auto");
		  $(this).next().show();
	 });
}
;

function sv(id) {
	 $('#' + id).toggle();
}
;
function add_Editor(id)
{
	var cid=id.replace('reply_to_','');
	if($("#editor_"+cid).length==0&&$('#txtContent'+cid).length)
	{
		$("<div id=\'editor_"+cid+"\'></div>").insertAfter($('#txtContent'+cid));
		if(typeof window["oEdit1"] !== "undefined") //InnovaEditor
		{
			window['oEdit1'+cid]=new InnovaEditor("oEdit1"+cid);
			window['oEdit1'+cid].arrCustomButtons=window['oEdit1'].arrCustomButtons;
			window['oEdit1'+cid].groups=window['oEdit1'].groups;
			window['oEdit1'+cid].arrStyle=window['oEdit1'].arrStyle;
			window['oEdit1'+cid].flickrUser=window['oEdit1'].flickrUser;
			window['oEdit1'+cid].css=window['oEdit1'].css;
			window['oEdit1'+cid].fileBrowser=window['oEdit1'].fileBrowser;
			window['oEdit1'+cid].customColors=window['oEdit1'].customColors;
			window['oEdit1'+cid].mode=window['oEdit1'].mode;
			window['oEdit1'+cid].REPLACE("txtContent"+cid, "editor_"+cid);
		}
		else if(tinyMCE !== "undefined") //tinyMCE
			tinyMCE.execCommand('mceAddEditor',false,'txtContent'+cid);
	}
}
function svc(id) {
	 $("#" + id).hide();
}

function add_category_ajax() {
	 var data = {};
	 data.cname = $("#cname").val();
	 data.ccolor = $("#ccolor").val();
	 data.parentid = $("#parentid").val();
	 if ($("#cmark").size() > 0)
		  data.cmark = $("#cmark").val();
	 if ($("#cmark_color").size() > 0)
		  data.cmark_color = $("#cmark_color").val();
	 if ($("#cat_invisible").size() > 0)
		  data.cat_invisible = $("#cat_invisible").val();
	 if ($("#restricted").size() > 0)
		  data.restricted = $("#restricted").val();
	 $.post("?action=add_category_ajax", data, function (re) {
		  if (re.charAt(0) == "#") {
				$("#category").append("<option value=\"" + re.substring(1) + "\" selected=\"selected\" style=\"background:" + $("#ccolor").val() + ";color:#000000\">" + $("#cname").val() + "</option>");
				$("#new_cat").hide();
		  }
		  else
				alert(re);
	 });
}

function vAddCategory(id, msg) {
	 if ($("#cname" + id).val() == "" || $("#ccolor" + id).val() == "") {
		  alert(msg);
		  return false;
	 }
	 ;
	 return true;
}
;

function vAddFeature(id, msg) {
	 if ($(feature_name).val() == "") {
		  alert(msg);
		  return false;
	 }
	 ;
	 return true;
}
;

function toggle_comment(th, id)
{
	 $.post("?action=comments&do=toggle", {comment_id: id}, function (re) {
		  if (re == 0)
				alert('error!');
		  else {
				re = re.split('|');
				$("#status_" + id).html(re[1]);
				$(th).html(re[0]);
		  }
	 });
}

var act = null;

function s_roll(id, tg, th, cn)
{
	 if (act == null) {
		  th.className = cn;
		  if (id != '')
				$('#' + id).css('visibility', (tg ? "visible" : "hidden"));
	 }
	 ;
}

function request_order(ev) {
	 ev.preventDefault();
	 var th = $(this).parents("tr"), thcols = $("td", th).length;
	 ex = ($(th).next().hasClass('order_detail'));
	 $(".order_detail").remove();
	 if (ex)
		  return;
	 $.get($(this).attr("href") + "&hidetpl",
				function (data) {
					 x = $(data).html();
					 $(th).closest('tr').after('<tr class="order_detail"><td colspan="' + thcols + '">' + x + '</td></tr>');
				}
	 );
}
;

function rzCC(s) {
	 for (var exp = /-([a-z])/; exp.test(s); s = s.replace(exp, RegExp.$1.toUpperCase()))
		  ;
	 return s;
}
;
function pad(nr) {
	 while (nr.length < 2)
		  nr = '0' + nr;
	 return nr;
}
;
function rzGetStyle(e, a) {
	 var v = null;
	 if (document.defaultView && document.defaultView.getComputedStyle) {
		  var cs = document.defaultView.getComputedStyle(e, null);
		  if (cs && cs.getPropertyValue)
				v = cs.getPropertyValue(a);
	 }
	 if (!v && e.currentStyle)
		  v = e.currentStyle[rzCC(a)];
	 return v;
}
;
function convert(color) {
	 if (color.match(/^rgb/)) {
		  var c = color.replace(/rgb\((.+)\)/, '$1').replace(/\s/g, '').split(',');
		  color = pad(parseInt(c[0]).toString(16)) + pad(parseInt(c[1]).toString(16)) + pad(parseInt(c[2]).toString(16));
	 }
	 return color.replace('#', '');
}
function rzGetBg(e) {
	 var v = rzGetStyle(e, 'background-color');
	 while (!v || v == 'transparent' || v == 'rgba(0, 0, 0, 0)') {
		  if (e == document.body)
				v = '#fff';
		  else {
				e = e.parentNode;
				v = rzGetStyle(e, 'background-color');
		  }
	 }
	 ;
	 return convert(v);
}
;

//online administration

function check_inputs()
{
	 var s_all = $('input[name=select_all]:checked');
	 var r_app = $('input[name=require_approval]');
	 var a_l = $('input[name=auto_login]');
	 var fb_l = $('input[name=fb_login]');
	 var r_app_on = 1;
	 if (s_all.val() === 'yesw')
		  r_app_on = 0;
	 else if (s_all.val() === 'from_group')
		  r_app_on = $('#grp_selector option:selected').attr('rel') == 1;
	 if (r_app_on)
		  r_app.removeAttr('disabled');
	 else
		  r_app.attr('checked', true).attr('disabled', 'disabled');

	 if (r_app.is(':checked'))
	 {
		  a_l.attr('checked', false).attr('disabled', 'disabled');
		  fb_l.attr('checked', false).attr('disabled', 'disabled');
	 }
	 else
	 {
		  a_l.removeAttr('disabled');
		  fb_l.removeAttr('disabled');
	 }
	 if (fb_l.is(':checked'))
		  $('#fb_settings').show();
	 else
		  $('#fb_settings').hide();
}
;

function cloneSelectedToForm(lbl, formAction) {
	 var $frm = $("#mng_usr_tbl_frm");
	 if ($('#grp_selector').val() != -1 || lbl !== undefined) {
		  $(".mng_entry_chck:checked").each(function () {
				if (lbl !== undefined)
					 $frm.append($(this).clone(true, true).attr("name", lbl + "[]").hide());
				else
					 $frm.append($(this).clone(true, true).hide());
		  });
		  if (formAction !== undefined) {
				if (formAction == '#')
					 $frm.attr("action", window.location.toString());
				else
					 $frm.attr("action", formAction);
		  }
		  $frm.submit();
	 }
}
;

// calendar inputs
function moveOptionLeft() {
	 l = $("#left_select")[0];
	 r = $("#right_select")[0];
	 var j = 0;
	 if (l.options.length > 0)
		  j = l.options.length;
	 for (i = 0; i < r.options.length; i++) {
		  if (r.options[i].selected) {
				l.options[j] = new Option(r.options[i].text, r.options[i].value);
				j++;
		  }
	 }
	 for (m = r.options.length - 1; m >= 0; m--) {
		  if (r.options[m].selected == true) {
				r.options[m] = null;
		  }
	 }
}

function moveOptionRight() {
	 l = $("#left_select")[0];
	 r = $("#right_select")[0];
	 var j = 0;
	 if (r.options.length > 0)
		  j = r.options.length;
	 for (i = 0; i < l.options.length; i++) {
		  if (l.options[i].selected) {
				r.options[j] = new Option(l.options[i].text, l.options[i].value);
				r.options[j].selected = true;
				j++;
		  }
	 }
	 for (m = l.options.length - 1; m >= 0; m--) {
		  if (l.options[m].selected)
				l.options[m] = null;
	 }
}

function moverightAll(whenEmpty) {
	 markl(whenEmpty);
	 moveOptionRight();
}

function markl(whenEmpty) {
	 if (whenEmpty) {
		  elR = $("#right_select")[0];
		  if (elR.options.length)
				return true;
	 }
	 el = $("#left_select")[0];
	 for (i = 0; i < el.options.length; i++)
		  el.options[i].selected = true;
}

function toggle_admin_check(el) {
	 var target = $(el).parents("tr").next();
	 if ($(el).is(":checked"))
		  target.hide();
	 else
		  target.show();
}

function markOption() {
	 el = $("#right_select")[0];
	 for (i = 0; i < el.options.length; i++) {
		  el.options[i].selected = true;
	 }
}

function selectGroup()
{
	 $("#usr_grps_select").change(function () {
		  var val = $(this).find(":selected").val();
		  var grp = val.split(",");
		  var el = $("#left_select")[0];
		  for (i = 0; i < el.options.length; i++) {
				var res = $.inArray(el.options[i].value, grp);
				if (res != -1)
					 el.options[i].selected = true;
		  }
		  moveOptionRight();
	 });
}
function view_poll_from_widget(poll_id)
{
	 var output = '#polls_' + poll_id;
	 if ($(output).css('display') == 'none')
	 {
		  $.ajax({
				url: 'ezg_data/poll.php?poll_id=' + poll_id + '&result=1',
				success:
						  function (data)
						  {
								$(output).html(data);
								$(output).find('.poll_title').remove();
								var template_poll = data.match('<!--(.*)-->')[1];
								$(output).addClass(template_poll).show();
						  }
		  });
	 }
	 else {
		  $(output).hide();
	 }
}

function count_options_ofpoll(id_of_option)
{
	 var selector = "input[id^='" + id_of_option + "']";
	 var array_options = $(selector);
	 var count_options = 0;
	 return  count_options = array_options.length;
}

function remove_option(this_option)
{
	 id = this_option.attr('id');
	 $('.' + id.replace('remove_', '')).remove();
	 this_option.closest('div[id^="sort_"]').remove();
}

function add_option_poll()
{
	 var selector = "input[id^='option_']";
	 var array_options = $(selector);
	 var after_id = 1;
	 count_options = array_options.length;
	 if (count_options >= 1 && count_options <= 99)
	 {
		  var selector = "input[id^='option_']:last";
		  var last = $(selector);
		  var array_id = [];
		  for (var i = 0; i < array_options.length; i++) {
				var curent_id = $(array_options[i]).attr('id');
				number = curent_id.replace(/\D/g, '');
				array_id[i] = parseInt(number, 10);
		  }
		  var last_numer_id = Math.max.apply(Math, array_id);
		  after_id = last_numer_id + 1;
	 }
	 var dragg_begin_div = '<div id="sort_' + (after_id + 1) + '" class="sort_row  dr" style="position:relative;"><div class="topic_bg" style="clear:left;margin: 0 2px 2px 0;padding:3px;"><div class="ui_shandle" onmouseover="$(this).addClass(\'ui_shandle_highlight\');" onmouseout="$(this).removeClass(\'ui_shandle_highlight\');"><span class="rvts8 a_editcaption">';
	 var insert_input_option = '<input class="input1" type="text" name="option_' + after_id + '" id="option_' + after_id + '" onkeyup="$(\'#loption_' + after_id + '\').html(this.value);" style="width:500px">';
	 var insert_remove_btn = '<input type="button" class="input1" onclick="remove_option($(this))" value="remove" id="remove_option_' + after_id + '"/>';
	 var dragg_end_divs = '</span><a class="ui_shandle_ic2"></a></div></div></div>';
	 var insert_all = dragg_begin_div + insert_input_option + insert_remove_btn + dragg_end_divs;
	 if (count_options == 0)
		  $('#options_title').closest('div[id^="sort_"]').after(insert_all);
	 else
		  $(last).closest('div[id^="sort_"]').after(insert_all);

	 $('.pc p').last().prepend('<p class="option_' + after_id + '"><input type="radio" id="xoption_' + after_id + '" value="1" name="poll_answer"><label id="loption_' + after_id + '" for="xoption_' + after_id + '"></p>');
}

function submitAjaxR(el, dl) {
	 $.post(
				$(el).attr("action"),
				$(el).serialize() + "&ajc=1&save=1",
				function (re) {
					 if (re.charAt(0) == "1")
						  re = re.substr(1);
					 if (dl)
						  ealert(re);
					 else
						  $("#" + $(el).attr("id") + "_error").html(re);
				}
	 );
}
;



///dragables

var fixHelper = function (e, ui) {
	 ui.children().each(function () {
		  $(this).width($(this).width());
	 });
	 return ui;
};

var arrToBeMoved = new Array();

var initDragging = function (bgClass, bgcClass) {
	 $(".ui_shandle").hover(function () {
		  $(this).addClass(bgcClass).removeClass(bgClass);
	 },
				function () {
					 $(this).addClass(bgClass).removeClass(bgcClass);
				});
	 $(".tbody").sortable({handle: ".ui_shandle_ic2", helper: fixHelper, placeholder: "ui-state-highlight",
		  update: function (e, ui) {
				$cRel = ui.item.attr("rel").split(":");
				$cM = $cRel[0];
				$cS = $cRel[1];
				$cSS = $cRel[2];
				$idx = ui.item.index();
				$pRel = $idx == 0 ? null : $(this).children().eq($idx - 1).attr("rel").split(":");
				$nRel = $idx == $(this).children().length - 1 ? null : $(this).children().eq($idx + 1).attr("rel").split(":");
				if ($pRel !== null)
				{
					 $pM = $pRel[0];
					 $pS = $pRel[1];
					 $pSS = $pRel[2];
				}
				else
					 $pM = $pS = $pSS = -1;
				if ($nRel !== null)
				{
					 $nM = $nRel[0];
					 $nS = $nRel[1];
					 $nSS = $nRel[2];
				}
				else
					 $nM = $nS = $nSS = -1;
				$check = false;
				if ($cSS > -1)  //subsub
				{
					 if ($cSS == $pSS && $cS == $pS)
						  $check = true;
					 if ($pSS == -1 && $cSS == $pS && $cS == $pM)
						  $check = true;
				}
				else  //main or sub
				{
					 if ($cS > -1) //sub
					 {
						  if ($cS == $pS && $pS != $nSS)
								$check = true;
						  if ($pS == -1 && $cS == $pM)
								$check = true;
						  if ($nS == -1 && $cS == $pSS)
								$check = true;
					 }
					 else //main
					 {
						  if ($pM != $nS && $pSS == -1)
								$check = true;
						  if ($pM == -1 && $nS == -1)
								$check = true;
						  if ($nM == -1)
								$check = true;
					 }
					 if ($check)
					 {
						  $toAppend = ui.item;
						  for (id in arrToBeMoved)
						  {
								$("#" + arrToBeMoved[id]).hide().insertAfter($toAppend).show("slow");
								$toAppend = $("#" + arrToBeMoved[id]);
						  }
					 }
				}
				if (!$check)
				{
					 $(this).sortable("cancel").slideDown();
					 $(this).closest("div").attr("style", "border: #f00 solid 3px;").animate({borderWidth: 0}, 200);
				}
				else
					 $(this).closest("div").attr("style", "border: #0f0 solid 3px;").animate({borderWidth: 0}, 200);
				for (id in arrToBeMoved)
					 $("#" + arrToBeMoved[id]).show("slow");

		  },
		  stop: function (e, ui) {
				for (id in arrToBeMoved)
					 $("#" + arrToBeMoved[id]).show("slow");
		  },
		  start: function (e, ui) {
				$cRel = ui.item.attr("rel").split(":");
				$cM = $cRel[0];
				$cS = $cRel[1];
				$cSS = $cRel[2];
				arrToBeMoved = new Array();
				if ($cSS > -1)
					 return true;
				$idx = ui.item.index();
				if ($idx == $(this).children().length - 2) {
					 $nM = $nS = $nSS = -1;
				}
				else
				{
					 $nextEl = $(this).children().eq(++$idx);
					 if ($nextEl.hasClass("ui-state-highlight"))
						  $nextEl = $(this).children().eq(++$idx);

					 if ($nextEl.attr("rel") == undefined)
						  $nRel = new Array(-1, -1, -1);
					 else
						  $nRel = $nextEl.attr("rel").split(":");
					 $nM = $nRel[0];
					 $nS = $nRel[1];
					 $nSS = $nRel[2];
				}
				if ($cS > -1)
				{
					 while ($nSS == $cS)
					 {
						  arrToBeMoved.push($nextEl.attr("id"));
						  $nextEl = $(this).children().eq(++$idx);
						  if ($nextEl.attr("rel") == undefined)
								$nSS = -1;
						  else
								$nSS = $nextEl.attr("rel").split(":")[2];
					 }
				}
				else
				{
					 while ($nS > -1)
					 {
						  arrToBeMoved.push($nextEl.attr("id"));
						  $nextEl = $(this).children().eq(++$idx);
						  if ($nextEl.attr("rel") == undefined)
								$nS = -1;
						  else
								$nS = $nextEl.attr("rel").split(":")[1];
					 }
				}

				for (id in arrToBeMoved)
				{
					 $("#" + arrToBeMoved[id]).hide("slow");
				}
		  }
	 });
};

//wp import
function wp_post(relPath, pgName)
{
	 if (window.error_type_wp_import === false)
		  return false;
	 var file_data = $('#wp_import_file').prop('files')[0];
	 var form_data = new FormData();
	 form_data.append('file', file_data);
	 form_data.append('wp_import', 'true');
	 $('#imported_file').html($('<img>').attr('src', relPath + 'extimages/scripts/loader.gif').css({'height': '18px', 'vertical-align': 'text-bottom'}));
	 $.ajax({
		  url: pgName + '?action=wp_import',
		  cache: false,
		  dataType: 'json',
		  data: form_data,
		  type: 'post',
		  contentType: false,
		  processData: false,
		  success: function (data, textStatus, jqXHR)
		  {
				if (typeof data.error === 'undefined')
				{
					 // Success so call function to process the form
					 $('#imported_file').html(data);
				}
				else
				{
					 // Handle errors here
					 $('#imported_file').html(data.error);
				}
		  },
		  error: function (jqXHR, textStatus, errorThrown)
		  {
				// Handle errors here
				$('#imported_file').html(textStatus);
		  }
	 });
}

function getRow(id) {
	 row = $("#" + id);
	 if ($(row).prop("tagName") != 'TR')
		  row = row.parents("tr");
	 return row;
}

function moveUpRow(id) {
	 row = getRow(id);
	 if (row.prev().prop('id') != 'tr_head')
		  row.insertBefore(row.prev());
}
function moveDownRow(id) {
	 row = getRow(id);
	 if (row.prev().prop('id') != 'tr_foot')
		  row.insertAfter(row.next());
}

//ca registration screen

function addRegfield(p, c) {
	 nfnv = $("#nfn").val();
	 nfvv = $("#nfv").val();
	 if (nfnv == "") {
		  alert("Please define field name!");
		  return;
	 }
	 ftv = $("#ft").val();
	 $.post(p, {process: "getfield", nfn: nfnv, ft: ftv, nfv: nfvv}, function (re) {
		  $(".empty").parents("." + c).before(re);
	 });
}
;

function updateRegField(nfnv, p, c) {
	 nfvv = $("#nfv_" + nfnv).val();
	 $.post(p, {process: "getfield", nfn: nfnv, ft: "listbox", nfv: nfvv}, function (re) {
		  $("#nfv_" + nfnv).parents("." + c).replaceWith(re);
	 });
}

function handleRegAvatar() {
	 $("#ft").change(function () {
		  if ($("#ft").val() == "avatar")
				$("#nfn").val("avatar");
	 });
}

//Bundles Builder
$.fn.bundleBuilder = function(options) {
	var defaults = {
		products:'[]',
		lang_l:{
			no_prodcuts:"No published products in stock!",
			close:"close",
			discount:"discount",
			price:"price",
			all_categories:"All categories",
			total_price:"Total price",
			primay_title:"Primary Item"
		},
		decimal:'2',
		id:Math.floor(Math.random() * 999999),
		primaryItem:0
	},
	settings=$.extend({}, defaults, options),
	bundle=$(this);
	all_products = JSON.parse(settings.products);

	function build_prodcut_row(i,v,max_count,thisID,primaryItem)
	{
		var real_pid=get_real_product(v),
		op_str='',op_array=[],cop_str='',cop_array=[],categ=[],pr_str='';
		var selected_product=false, selected_categ=false, selected_price=false;
		var min_step=cal_min_step(), old_price='';
		var disc_v=v.hasOwnProperty('discount')?v.discount.match(/\[\-\d+(\.\d+)?\%\]/g)[0].match(/\d+(\.\d+)?/g):'0';
		
		$.each(all_products, function()
		{
			if($.inArray(this.cid,categ)==-1)
			{
				sub_categ=this.pp_id!=''&&this.pp_id>-1?'— —':(this.parent_categ!=''?'—':'');
				if(this.parent_cid!=''&&this.parent_categ!=''&&$.inArray(this.parent_cid,categ)==-1)
				{
					categ.push(this.parent_cid);
					$bg='background: -webkit-linear-gradient(left,transparent 94%,'+this.parent_ccolor+' 94%,'+this.parent_ccolor+' 100%);background: linear-gradient(to right,transparent 94%,'+this.parent_ccolor+' 94%,'+this.parent_ccolor+' 100%);';
					cop_array.push({'cid':this.parent_cid,'content':'<option class="cat_'+this.parent_cid+'" style="'+$bg+'" %selected_category% value="'+this.parent_cid+'">'+
					this.parent_categ+' (%count_prodcuts%)</option>','count_pr':0,'parent_categ':''});
				}
				$bg='background: -webkit-linear-gradient(left,transparent 94%,'+this.ccolor+' 94%,'+this.ccolor+' 100%);background: linear-gradient(to right,transparent 94%,'+this.ccolor+' 94%,'+this.ccolor+' 100%);';
				var c_content={'cid':this.cid,'content':'<option class="cat_'+this.cid+'" style="'+$bg+'" %selected_category% value="'+this.cid+'">'+
						sub_categ+this.category+' (%count_prodcuts%)</option>','count_pr':0,'parent_categ':this.parent_categ,'sub_categ':sub_categ};
				if(sub_categ=='— —')
				{
					index=$.inArray(this.parent_cid,categ);
					if(index!=-1){
						inx=index+1;
						for(var j=index;j<=all_products.length;j++){
							if(cop_array[j]!=undefined && cop_array[j].sub_categ=='— —')
								inx++;
						}
						categ.splice(inx, 0, this.cid);
						cop_array.splice(inx, 0, c_content);
						
					}
				}else{
					categ.push(this.cid);
					cop_array.push(c_content);
				}
			}
			op_array.push({'cid':this.cid,'content':'<option class="pr_'+this.pid+'" '+(real_pid==this.pid?'selected':'')+' data-categ="'+this.cid+'" value="'+this.pid+'" %dispalyed_prodcuts%>'+
			this.name+'</option>'});

			if(selected_product===false&&real_pid==this.pid)
				selected_product=this.pid;
			if(selected_categ===false&&real_pid==this.pid)
				selected_categ=this.cid;
			if(selected_price===false&&real_pid==this.pid){
				selected_price=$.bundleFunc().calc_new_price(this.price,disc_v);
				old_price=$.bundleFunc().number_format(parseFloat(this.price)||0,settings.decimal,'.','');
			}
			pr_str+='<option value="'+this.pid+'"'+(selected_product==this.pid? 'selected ':'')+'>'+this.price+'</option>';
		});
		$.each(op_array, function(){
			if($.inArray(this.cid,categ)!=-1){
				categ_obj=objectFindByKey(cop_array,'cid',this.cid);
				if(categ_obj!== null)
					cop_array[categ_obj.indx].count_pr+=1;
			}
			op_str+=this.content.replace('%dispalyed_prodcuts%',(this.cid==selected_categ||selected_product===false?'':'style="display:none" disabled'));
		});
		$.each(cop_array, function(){
			cop_str+=this.content.replace('%selected_category%',(this.cid==selected_categ?'selected':'')).replace('%count_prodcuts%',this.count_pr);
		});
		var dic_str='<input style="width:70px;margin-left:5px;" type="number" max="100" min="0" step="'+min_step+'" class="input_discount_'+thisID+'" onchange="$.bundleFunc({this_id:'+thisID+'}).change_inputDisc(this)" id="i_disc_'+thisID+'_'+i+'" rel="'+i+'" value="'+disc_v+'" />';
		var new_price='<input style="width:80px;margin: 0px 5px 0px 5px;" type="number" max="'+(old_price>0?old_price:'')+'" min="0" step="'+min_step+'" class="input_price_'+thisID+'" onchange="$.bundleFunc({this_id:'+thisID+'}).change_inputPrice(this)" id="i_price_'+thisID+'_'+i+'" rel="'+i+'" value="'+(selected_price!==false?selected_price:0)+'" />';
		var primary_box='<input type="radio" title="'+settings.lang_l.primay_title+'" name="primary_box_'+thisID+'" '+((selected_product!==false&&selected_product==primaryItem)||(thisID=='0'&&i==0)?'checked="checked"':'')+' class="primary_box_'+thisID+'" id="primary_box_'+thisID+'_'+i+'" onclick="$.bundleFunc({this_id:'+thisID+'}).click_primaryItem(this)" value="'+(selected_product===false?0:selected_product)+'">';

		var row = primary_box+'<select class="select_categories_'+thisID+'" style="width:120px;text-overflow: ellipsis;overflow: hidden;" onchange="$.bundleFunc({this_id:'+thisID+'}).change_selectCateg(this)" id="s_categ_'+thisID+'_'+i+'" rel="'+i+'">'+'<option value="all" '+(selected_categ===false?'selected':'')+'>'+settings.lang_l.all_categories+'</option>'+cop_str+'</select>'+
		'<select class="select_products_'+thisID+'" style="width:290px;text-overflow: ellipsis;overflow: hidden;" onchange="$.bundleFunc({this_id:'+thisID+'}).change_selectProd(this)" id="s_products_'+thisID+'_'+i+'" rel="'+i+'">'+
		(selected_product===false?'<option selected value=""></option>':'')+op_str+'</select>'+
		'<select class="select_prices_'+thisID+'" id="s_price_'+thisID+'_'+i+'" rel="'+i+'" style="display:none">'+(selected_product===false?'<option selected value=""></option>':'')+pr_str+'</select>'+
		dic_str+new_price+
		'<input type="button" class="rmv_button" onclick="$.bundleFunc({this_id:'+thisID+'}).remove_bundle(this)" rel="'+i+'" value=" x " >'+
		'<input class="add_button" onclick="$.bundleFunc({this_id:'+thisID+',pitem:'+primaryItem+'}).clone_bundle()" type="button" rel="'+i+'" value="+" >';
		row = '<div class="sortoption dr'+(i==max_count-1?' last':'')+'" id="c_sort_'+thisID+'_'+i+'"  rel="'+i+'">'+
					'<div class="ui_shandle">'+
						'<span class="fa fa-arrows ui_shandle_ic2 ui-sortable-handle" style="padding-top: 7px;"></span>'+
							row+
					'</div>'+
			'</div>';
		return row;
	};

	function build_bundle()
	{
		$("#bundle_table_"+settings.id).sortable("destroy").remove();
		$("#bundle_prodcuts_"+settings.id).remove();
		var bundle_prodcuts = $('<div>', {'id':'bundle_prodcuts_'+settings.id}).css({'width':'690px','margin-top':'5px'}).insertAfter(bundle);
		
		if(!all_products.length)
		{
			bundle_prodcuts.html('<p class="rvts12">'+settings.lang_l.no_prodcuts+'</p>');
			return false;
		}
		else // there are real, published products in stock 
		{
			var pr_fromInput=get_products_fromInput();
			$rows='';
			if(pr_fromInput.length)
			{
				$.each(pr_fromInput, function(i,v){
					$rows +=build_prodcut_row(i,v,pr_fromInput.length,settings.id,settings.primaryItem);
				});
			}
			else // empty input so build two empty products rows
			{
				for(var i=0;i<2;i++){
					$rows +=build_prodcut_row(i,'',2,settings.id,settings.primaryItem);
				}
			}
			bundle_prodcuts.html('<div id="bundle_table_'+settings.id+'" class="ui-sortable">'+
				'<div style="display:inline-block; width:70px;text-align:center;margin-left:442px"><span style="font-size:11px">'+settings.lang_l.discount+' (%)</span></div>'+
				'<div style="display:inline-block; width:80px;margin-left:5px;text-align:center;"><span style="font-size:11px">'+settings.lang_l.price+'</span></div>'+
				$rows+'</div>'+
				'<div class="total_price" style="margin-top: 10px;margin-left:459px;"><span style="font-size:11px">'+settings.lang_l.total_price+'</span><input type="text" id="total_price_'+settings.id+
				'" value="0" onchange="$.bundleFunc({this_id:'+settings.id+'}).change_mainPrice()" style="width:80px;margin-left:5px"></div>');
			$("#bundle_table_"+settings.id+" .ui_shandle").hover(function(){$(this).addClass("ui_shandle_highlight");},function(){$(this).removeClass("ui_shandle_highlight");});
			$("#bundle_table_"+settings.id).sortable({handle:".ui_shandle_ic2",placeholder:"ui-state-highlight",update: function(event, obj){
				var last_item=$("#bundle_table_"+settings.id+" .sortoption:last");
				if($(obj.item).hasClass("last"))
				{
					var last_id=last_item.attr("rel").match(/\d+$/)[0]
					,item_id=$(obj.item).attr("rel").match(/\d+$/)[0];
					if(item_id>last_id){
						last_item.addClass("last");
						$(obj.item).removeClass("last");//
					}
				}
				else if($(obj.item).index()==last_item.index()){
					$(this).find(".last").removeClass('last');
					$(obj.item).addClass("last");
				}
				$(this).find(".sortoption").each(function(i,v){
					$(v).attr('rel',i);
				});
				$.bundleFunc({this_id:settings.id}).edit_input();
			}});
			$.bundleFunc({this_id:settings.id}).edit_input(true);
			$("#bundle_name_"+settings.id).on('change', function(){
				var name=$(this).val().replace(/([~!@#$%^&*()+={}\[\]\|\\:;'"<>,.\/?])+/g, '').replace(/^(-)+|(-)+$/g,'');
				$(this).val(name.trim());
			});			
		}
	};

	function objectFindByKey(array, key, value) {
		for (var i = 0; i < array.length; i++) {
			if (array[i][key] === value) {
				return {'obj':array[i],'indx':i};
			}
		}
		return null;
	}

	function cal_min_step()
	{
		var step=parseInt(settings.decimal)>1?'0.':'';
		for(var i=1;i<parseInt(settings.decimal);i++)
			step+='0';
		return step+='1';
	};

	function get_products_fromInput()
	{
		var array_values=[];
		var str=bundle[0].defaultValue.trim();
		if(str!='')
		{
			var values=str.split(';');
			$.each(values, function(){
				if(this!=''){
					var id_disc= this.replace('(','').replace('})','').split('{');
					array_values.push({
						"item_id":(id_disc.hasOwnProperty("0")?parseInt(id_disc[0]):0),
						"discount":(id_disc.hasOwnProperty("1")?id_disc[1]:'0')
					});
				}
			});
		}

		return array_values;
	};

	function get_real_product(v)
	{
		var is_real=false;
		if(v=='')
			return is_real;
		$.each(all_products, function(){
			if(this.pid==v.item_id){
				is_real=this.pid;
				return false;
			}
		});
		return is_real;
	};

	(function ($) {
		$.extend({
			bundleFunc: function (opt) {
				var c={};
				c.number_format = function(number, decimals, dec_point, thousands_sep) {
					if(!isNaN(parseFloat(number)) && number%1 === 0)
						return number;
					var n = number, prec = decimals;
					var toFixedFix = function (n,prec) {
						var k = Math.pow(10,prec);
						return (Math.round(n*k)/k).toString();
					};
					n = !isFinite(+n) ? 0 : +n;
					prec = !isFinite(+prec) ? 0 : Math.abs(prec);
					var sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
					var dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
					var s = (prec > 0) ? toFixedFix(n, prec) : toFixedFix(Math.round(n), prec);
					var abs = toFixedFix(Math.abs(n), prec);
					var _, i;
					if (abs >= 1000) {
						_ = abs.split(/\D/);
						i = _[0].length % 3 || 3;
						_[0] = s.slice(0,i + (n < 0)) + _[0].slice(i).replace(/(\d{3})/g, sep+'$1');
						s = _.join(dec);
					} else
						s = s.replace('.', dec);
					var decPos = s.indexOf(dec);
					if (prec >= 1 && decPos !== -1 && (s.length-decPos-1) < prec)
						s += new Array(prec-(s.length-decPos-1)).join(0)+'0';
					else if (prec >= 1 && decPos === -1)
						s += dec+new Array(prec).join(0)+'0';
					return s;
				}
				c.calc_new_price = function(price,disc){
					return c.number_format(parseFloat(price)*(1-parseFloat(disc/100)),settings.decimal,'.','');
				};
				c.calc_new_disc= function(price_input,price_select){
					return c.number_format(100*((price_select-price_input)/price_select),settings.decimal,'.','');
				};
				c.edit_input = function(only_price){
					var sel_prod=$('.select_products_'+opt.this_id);
					var i_disc=$('.input_discount_'+opt.this_id);
					var i_prices=$('.input_price_'+opt.this_id);
					var error_arr={};
					if(sel_prod.length&&sel_prod.length==i_disc.length&&sel_prod.length==i_prices.length)
					{
						var input_str='',total_price=0,tem_option='',j=0,flag_diff=false;
						var sel_primary=$('.primary_box_'+opt.this_id+':checked');
						
						sel_prod.each(function(i,v)
						{
							var pid=parseInt($(v).val());
							if(i==0){
								$('#bundle_'+opt.this_id).find('input[type="submit"]').prop('disabled',pid!=sel_primary.val());
								$('#bundle_error_'+opt.this_id).css('display',pid!=sel_primary.val()&&!isNaN(pid)?'block':'none');
							}
							if(!isNaN(pid))
							{
								var disc=c.number_format(parseFloat($(i_disc[i]).val()),settings.decimal,'.','');
								input_str+='('+pid+'{[-'+disc+'%]});';
								total_price+=parseFloat($(i_prices[i]).val());
								j++;
							}
						});

						if(only_price!==true)
							$("#bundle_product_"+opt.this_id).val(input_str);
						$("#total_price_"+opt.this_id).val(c.number_format(total_price,settings.decimal,'.',''));
					}
				};
				c.edit_price = function(id){
					var price_input=$('#i_price_'+opt.this_id+'_'+id);
					var dicount_input=$('#i_disc_'+opt.this_id+'_'+id);
					var price_select=$('#s_price_'+opt.this_id+'_'+id);
					if(price_input.length&&dicount_input.length&&price_select.length)
					{
						var disc_val=dicount_input.val();
						disc_val=disc_val>100?100:disc_val<0?0:(!isNaN(parseFloat(disc_val))?disc_val:0);
						dicount_input.val(c.number_format(disc_val,settings.decimal,'.',''));
						var new_price=c.calc_new_price(price_select.find('option:selected').text(),disc_val);
						price_input.val(new_price);
						var old_price=price_select.find('option:selected').text();
						old_price=c.number_format(parseFloat(old_price)||0,settings.decimal,'.','');
						price_input.attr({'max':old_price>0?old_price:''});
					}
				};
				c.edit_discount = function(id){

					var price_input=$('#i_price_'+opt.this_id+'_'+id);
					var dicount_input=$('#i_disc_'+opt.this_id+'_'+id);
					var price_select=$('#s_price_'+opt.this_id+'_'+id);
					if(price_input.length&&dicount_input.length&&price_select.length)
					{
						p_sel=parseFloat(price_select.find('option:selected').text());
						price_value=parseFloat(price_input.val())||0;
						max=price_input.prop('max');
						price_value=price_value>max?parseFloat(max):price_value<0?0:price_value;
						price_input.val(c.number_format(price_value,settings.decimal,'.',''));
						if(p_sel >= price_value){
							var new_disc=c.calc_new_disc(price_value,p_sel);
							dicount_input.val(new_disc);
						}
					}
				};
				c.clone_bundle = function(){
					last_item = $("#bundle_table_"+opt.this_id+" .sortoption:last").removeClass("last");
					last_rel=parseInt(last_item.attr('rel'));
					new_row = build_prodcut_row(last_rel+1,'',$("#bundle_table_"+opt.this_id+" .sortoption").length,opt.this_id, opt.pitem);
					$(new_row).appendTo($('#bundle_table_'+opt.this_id)).addClass("last");
					$(new_row).find('.ui_shandle').hover(function(){$(this).addClass("ui_shandle_highlight");},function(){$(this).removeClass("ui_shandle_highlight");});
					c.edit_price(last_rel+1);
					c.edit_input();
					c.change_selectCateg($(new_row).find('.select_categories_'+opt.this_id));
				};
				c.remove_bundle = function(th){
					var id=$(th).attr("rel");
					if($('#primary_box_'+opt.this_id+'_'+id)[0].checked)
						$("#bundle_primaryItem_"+opt.this_id).val('false');
					$(th).parents(".sortoption").remove();
					c.edit_price(id);
					c.edit_input();
				};
				c.change_inputPrice = function(th){
					var id=$(th).attr("rel");
					c.edit_discount(id);
					c.edit_input();
				};
				c.change_inputDisc = function(th){
					var id=$(th).attr("id").match(/\d+$/)[0];
					c.edit_price(id);
					c.edit_input();
				};
				c.change_selectProd = function(th){
					value=parseInt($(th).val());
					var id=$(th).attr("rel");
					sel_price=$('#s_price_'+opt.this_id+'_'+id);
					primary_box=$('#primary_box_'+opt.this_id+'_'+id).val(isNaN(value)?0:value);
					if(primary_box[0].checked)
						$("#bundle_primaryItem_"+opt.this_id).val(isNaN(value)?0:value);
					sel_price.val(value).change();
					c.edit_price(id);
					c.edit_input();
				};
				c.change_selectCateg = function(th){
					var cid=parseInt($(th).val())||'all';
					var id=$(th).attr("rel");
					if($('#s_products_'+opt.this_id+'_'+id).length)
					{
						value=0;
						sel=$('#s_products_'+opt.this_id+'_'+id);
						sel_price=$('#s_price_'+opt.this_id+'_'+id);
						sel.find('option').each(function(){
							if($(this).attr('data-categ')==cid){
								if(value==0)
									value=parseInt($(this).val());
								$(this).css('display','block').attr('disabled',false);
							}
							else if(cid=='all'){
								if(value==0)
									value=parseInt($(this).val());
								$(this).css('display','block').attr('disabled',false);
							}
							else
								$(this).css('display','none').attr('disabled',true);
						});
						sel.val(value).change();
						sel_price.val(value).change();
					}
				};
				c.change_mainPrice = function(){
					var new_total_price=parseFloat($("#total_price_"+opt.this_id).val())||0;
					var old_total_price=0;
					var total_disc=0;
					if(new_total_price<0){
						$("#total_price_"+opt.this_id).val(0);
						new_total_price=0;
					}
					$.each($(".select_prices_"+opt.this_id),function(){
						old_total_price+=parseFloat($(this).find('option:selected').text())||0;
					});
					if(old_total_price>0&&old_total_price>=new_total_price)
						total_disc=c.calc_new_disc(new_total_price,old_total_price);
					else
						total_disc=0;
					$.each($(".input_discount_"+opt.this_id), function(){
						var id=$(this).attr("rel");
						$(this).val(total_disc);
						c.edit_price(id);
						c.edit_input();
					});
					
				}
				c.click_primaryItem = function(th){
					if(th.checked){
						$("#bundle_primaryItem_"+opt.this_id).val($(th).val());
						c.edit_input();
					}
				}
				return c;
			}
		});
	})(jQuery);
	build_bundle();
};

//Option Builder
function optionBuilder(o_edit_icon,onclick,onchange,enable_color)
{
	var option_id=o_edit_icon.attr('id').replace('o_edit_',''),
	option=$("#"+option_id),
	$val=option.val().trim(), color_option=0;
	if(enable_color===true){
		if($(".c_icon"+option_id).hasClass("selIcon")){
			if(!confirm("This will remove colors for all options. Sure to continue?"))
				return;
			$(".c_icon"+option_id).removeClass("selIcon");
			color_option=0;
		}
		else
			color_option=1;
	}
	else if($val.indexOf('})')!='-1')
		color_option=1;
	$("#option_table_"+option_id).sortable("destroy").remove();
	$(".values_wrap_"+option_id).remove();
	if(onclick===true&&o_edit_icon.hasClass('selIcon') || (onchange===true&&$val=='')){
		o_edit_icon.removeClass('selIcon');
		return;
	}
	else
		o_edit_icon.addClass('selIcon');

	var option_values=get_optionValues($val,color_option),
	$table_rows='',$first_label='';
	$.each(option_values, function(i,v){
		if(window.first_isLabel&&i==0){
			$first_label+='<input class="v_name input1" type="text" value="'+v.v_name+'" style="width:130px;display:block;" onkeyup="change_optionValue(\''+option_id+'\','+color_option+')" />'+
			'<input type="hidden" class="v_price" value=""/><input type="hidden" class="v_extra" value="">';
		}
		else
		$table_rows+='<div id="sort_'+option_id+i+'" class="sortoption dr'+(i==option_values.length-1?' last':'')+'"><div class="ui_shandle">'+
		'<span class="fa fa-arrows ui_shandle_ic2 ui-sortable-handle" style="padding-top: 7px;"></span>'+
		'<input class="v_name input1" type="text" value="'+v.v_name+'" style="width:130px;" onkeyup="change_optionValue(\''+option_id+'\','+color_option+')" />'+
		'<input class="v_price input1" type="text" value="'+v.v_price+'" placeholder="price offset" title="Example: +1.10 EUR"  style="width:100px;" onkeyup="change_optionValue(\''+option_id+'\','+color_option+')" />'+
		'<input class="v_extra '+(color_option?'color':'')+' input1" type="'+(color_option?'text':'hidden')+'" value="'+(color_option?v.v_extra:'')+'"  style="width:60px;'+(color_option?'background-color:#'+v.v_extra+';':'')+'" onchange="change_optionValue(\''+option_id+'\','+color_option+')"/>'+

		'<input type="button" class="rmv_button" onclick="removeOptionValue(this,\''+option_id+'\','+color_option+')" value=" x " >'+
		'<input class="add_button" type="button" value="+" onclick="cloneLastValue(\''+option_id+'\','+color_option+')">'+
		'<a class="c_palette_icon'+(color_option?' selIcon':'')+' c_icon'+option_id+'" onclick="optionBuilder($(\'#o_edit_'+option_id+'\'),false,false,true);"><i class="fa fa-paint-brush"></i></a></div>'+
		'</div>';
	});
	option.parent().append('<div class="values_wrap_'+option_id+'" style="width:450px">'+$first_label+'<div id="option_table_'+option_id+'"  class="ui-sortable"">'+$table_rows+'</div>');
	$("#option_table_"+option_id+" .ui_shandle").hover(function(){$(this).addClass("ui_shandle_highlight");},function(){$(this).removeClass("ui_shandle_highlight");});
	$("#option_table_"+option_id).sortable({handle:".ui_shandle_ic2",placeholder:"ui-state-highlight",update: function(event, obj){
		var last_item=$("#option_table_"+option_id+" .sortoption:last");
		if($(obj.item).hasClass("last"))
		{
			var last_id=get_valueId(last_item,option_id)
			,item_id=get_valueId($(obj.item),option_id);
			if(item_id>last_id){
				last_item.addClass("last");
				$(obj.item).removeClass("last");
			}
		}
		else if($(obj.item).index()==last_item.index()){
			$(this).find(".last").removeClass('last');
			$(obj.item).addClass("last");
		}
		$(this).find(".sortoption").each(function(i,v){
			$(v).prop('id','sort_'+option_id+i);
		});
		change_optionValue(option_id,color_option);
	}});
	change_optionValue(option_id,color_option);
}
function get_optionValues(str,color_option)
{
	var array_values=[];
	if(str!='')
	{
		var values=str.split(';');
		$.each(values, function(){
			if(this!=''){
				var name_extra = this.replace('(','').replace('})','').replace('}','').split('{'),
				name_price = name_extra[0].replace(']','').split('[');	
				array_values.push({
					"v_name":name_price[0].trim(),
					"v_extra":(name_extra.hasOwnProperty("1")?name_extra[1].trim():''),
					"v_price":(name_price.hasOwnProperty("1")?name_price[1].trim():'')
				});
			}
		});
	}
	if(array_values.length==0){
		if(window.first_isLabel)
			array_values.push({"v_name":'fist label',"v_extra":'','v_price':''});
		array_values.push({"v_name":(color_option?"red":'option1'),"v_extra":(color_option?"ff0000":""),'v_price':''});
		array_values.push({"v_name":(color_option?"green":'option2'),"v_extra":(color_option?"00ff00":""),'v_price':''});
		array_values.push({"v_name":(color_option?"blue":'option3'),"v_extra":(color_option?"0000ff":""),'v_price':''});
	}
	
	return array_values;
}
function optionBuilder_xsel(option)
{
	var o_edit_icon=option.parent().find(".o_edit_icon");
	if(o_edit_icon.length==0)
		return;
	optionBuilder(o_edit_icon);
}
function removeOptionValue(th,option_id,color_option)
{
	$(th).parents(".sortoption").remove();
	change_optionValue(option_id,color_option);
}
function cloneLastValue(option_id,color_option)
{
	cl=$("#option_table_"+option_id+" .sortoption:last").clone();
	var $id=get_valueId(cl,option_id);
	cl.prop("id","sort_"+option_id+($id+1));
	$("#option_table_"+option_id+" .sortoption:last" ).removeClass("last");
	$(cl).appendTo("#option_table_"+option_id);
	$(cl).find('.ui_shandle').hover(function(){$(this).addClass("ui_shandle_highlight");},function(){$(this).removeClass("ui_shandle_highlight");});
	change_optionValue(option_id,color_option);
}
function change_optionValue(option_id,color_option)
{
	jscolor.init();
	setTimeout(function(){
		var values_wrap=$(".values_wrap_"+option_id),

		v_name=values_wrap.find(".v_name"),
		v_extra=values_wrap.find(".v_extra"),
		v_price=values_wrap.find(".v_price"),

		str='';
		v_name.each(function(i,v){
			var name=$(v).val().trim(),
			extra='',price_str='';
			if(v_price.hasOwnProperty(i)){
				price_str=$(v_price[i]).val().trim();

			}
			if(v_extra.hasOwnProperty(i))
				extra=$(v_extra[i]).val().replace('#','').trim();
			value_str=name+(price_str!=''?'['+price_str+']':'')+(extra!=''?'{'+extra+'}':'');
			str+=(color_option?(window.first_isLabel&&i==0?value_str:'('+value_str+')'):value_str)+(i==(v_name.length-1)?'':'; ');

		});
		$("#"+option_id).val(str);
	}, 30);
}
function get_valueId(item,option_id){
	return parseInt(item.prop('id').replace('sort_','').replace(option_id,''));
}
//------

function f(o) {
	 o.value = o.value.toLowerCase().replace(/([^0-9a-z_])/g, "");
}


function translit(str) {
	 var arr = new Array();
	 arr[0] = ['а', 'a'];
	 arr[1] = ['б', 'b'];
	 arr[2] = ['в', 'v'];
	 arr[3] = ['г', 'g'];
	 arr[4] = ['ґ', 'g'];
	 arr[5] = ['д', 'd'];
	 arr[6] = ['е', 'e'];
	 arr[7] = ['є', 'ye'];
	 arr[8] = ['ж', 'zh'];
	 arr[9] = ['з', 'z'];
	 arr[10] = ['и', 'i'];
	 arr[11] = ['і', 'i'];
	 arr[12] = ['ї', 'yi'];
	 arr[13] = ['й', 'y'];
	 arr[14] = ['к', 'k'];
	 arr[15] = ['л', 'l'];
	 arr[16] = ['м', 'm'];
	 arr[17] = ['н', 'n'];
	 arr[18] = ['о', 'o'];
	 arr[19] = ['п', 'p'];
	 arr[20] = ['р', 'r'];
	 arr[21] = ['с', 's'];
	 arr[22] = ['т', 't'];
	 arr[23] = ['у', 'u'];
	 arr[24] = ['ф', 'f'];
	 arr[25] = ['х', 'h'];
	 arr[26] = ['ц', 'c'];
	 arr[27] = ['ч', 'ch'];
	 arr[28] = ['ш', 'sh'];
	 arr[29] = ['щ', 'sch'];
	 arr[30] = ['ь', ''];
	 arr[31] = ['ю', 'yu'];
	 arr[32] = ['я', 'ya'];
	 arr[33] = ['ы', 'y'];
	 arr[34] = ['ё', 'yo'];
	 arr[35] = ['э', 'e'];
	 arr[36] = ['ъ', ''];
	 arr[37] = ['ье','ie'];
	 arr[38] = ['ß', 'ss'];
	 arr[39] = ['ö','oe'];
	 arr[40] = ['ä','ae'];
	 arr[41] = ['ö','oe'];
	 arr[42] = ['ü','ue'];

	 str = str.toLowerCase();
	 for (i = 0; i < arr.length; i++) {
		  str = str.replace(new RegExp(arr[i][0], 'g'), arr[i][1]);
	 }
	 str=str.replace(/[\"\']/g,'');
	 str=str.replace(/[^-a-z0-9_]+/g,'-') ;

	 return str;
}

function translitTo(v,t) {
	 $('#'+t).val(translit(v));
}

function rotate(id,v){
    $('#'+id).css({ 'transform': 'rotate(' + v + 'deg)'});
}

function setRPP(t,path){	 
	 $.post(path+'documents/centraladmin.php', {process: "setRpp", rpp: $(t).val()}, function (re) {
		  location.reload();
	 });
	 
}
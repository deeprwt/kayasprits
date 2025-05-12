<?php
/*
	http://www.ezgenerator.com
	Copyright (c) 2004-2017 Image-line
*/

$return_page=Linker::currentPageUrl();
define('CATEGORY_FIELDS_COUNT',13);
define('WISHLIST_FIELDS_COUNT',3);
define('BUNDLES_FIELDS_COUNT',7);
define('BUNDLES_ORDERLINES_FIELDS_COUNT',8);
define('MODE_LIST',1);
define('MODE_CATEGORY',2);
define('MODE_DETAIL',3);
define('MODE_CART',4);
define('MODE_CHECKOUT',5);
define('F_DROPDOWN',2);
define('F_RADIO',1);
define('F_CHECKBOX',0);
define('LAY_VERTICAL',0);
define('LAY_HORIZONTAL',1);
define('LAY_HORIZONTAL_PLUS',2);
define('COUNT_ATTACMENTS',3);

class ListerPageClass extends LivePageClass implements PageWithComments
{
	public $version='ezgenerator v4 - Lister Page 6.5 mysql';

	protected $action_id;
	protected $edit_own_posts_only=false;
	protected $limit_own_post=0;
	protected $nav_labels=array('first'=>'first','prev'=>'prev','next'=>'next','last'=>'last');
	protected $month_name;
	protected $day_name;
	public $ranking_enabled=true;
	public $session_transaction_id=0;
	public $parseGfonts;
	protected $global_pagescr='';
	protected $permalink='';
	protected $protected;
	protected $editor_actions=array('new_product','stock','orders','pending','comments','login','products');
	protected $admin_actions=array('settings','pp','move_confirm','del_comment','features',
	'categories','toggle_hidden','toggle_cols','import','import2',
	'taxes','coupons','addsorting','bundles','cleancatsorting','cleanfieldsorting','fvalues');
	protected $int_tags=array('<SHOP>','</SHOP>','<SHOP_BODY>','</SHOP_BODY>','<SHOP_HEADER>','</SHOP_HEADER>','<SHOP_FOOTER>','</SHOP_FOOTER>','<LISTER_BODY>',
	'</LISTER_BODY>','<LISTER_HEADER>','</LISTER_HEADER>','<LISTER_FOOTER>','</LISTER_FOOTER>','<LISTER>','</LISTER>','<QUANTITY>','</QUANTITY>');

	protected $g_page_prefix='';
	public $g_abs_path='';
	protected $g_ship_usefield;
	protected $g_datapre;
	protected $g_pagenr=1;
	protected $g_orderbyid_user=0;
	protected $g_linked=false;
	protected $g_linkedid=0;
	protected $where_public=' publish=1 ';
	protected $where_public_user=' publish=1 ';
	protected $use_publish_dates=false;
	protected $g_data_where2;
	protected $g_use_rel=false;
	protected $g_use_com=false;
	public $g_use_pby=false;
	protected $g_catid;
	protected $g_cat;
	public $mode=0;
	protected $g_code;//used by image-line
	protected $use_friendly=true;
	protected $page_info='';
	protected $searchModule;
	protected $filtersModule;
	protected $featuresModule;
	protected $optionsModule;
	protected $wishlistModule;
	protected $recentlyViewedModule;
	public $tag_object=null;
	public $shop=false;
  //shop only
	protected $couponsModule;
	protected $serialsModule;
	public $InvoiceModule;
	protected $bundleModule;
	public $paymentModule=false;
	protected $stockModule;
	protected $g_symlinkpath;
	protected $basket;
	protected $session_total=0;
	protected $session_vat_1=0;
	protected $session_vat_2=0;
	protected $session_vat_c=0;
	protected $session_vat_as=0;
	public $g_callback2=false;
	protected $session_on=false;
	protected $livesearch=false;
	public $internal=false;  // IL Site
	public $innova_on_output=false;

	public function __construct($pid,$pdir,$pname,$relpath,$settings)
	{
		parent::__construct($pid,$pdir,$pname,$relpath,$settings,$settings['page_type']);

		$this->admin_actions=array_merge($this->editor_actions,$this->admin_actions);

		$this->g_page_prefix=($this->page_is_mobile?'i_':'');
		$this->g_datapre=$this->pg_settings['g_data'].'_';
		$this->g_linked=$this->pg_id!=$this->pg_settings['g_data'];
		if($this->g_linked)
			$this->g_linkedid=$this->pg_id;

		$this->month_name=$this->f->month_names;
		$this->day_name=$this->f->day_names;
		$this->searchModule=new ListerSearch($this);
		$this->filtersModule=new Filters($this);
		$this->featuresModule=new Features($this);
		$this->optionsModule=new Options($this);
		$this->wishlistModule=new Wishlist($this);
		$this->recentlyViewedModule=new recentlyViewed($this);
		$this->parseGfonts=false;

		if($this->pg_settings['template_path']=='')
		  $this->pg_settings['template_path']=$this->pg_dir.$this->pg_id.'.html';

		if($this->page_is_mobile)
			$this->pg_settings['template_path']=str_replace($this->pg_settings['template_fname'],'i_'.$this->pg_settings['template_fname'],$this->pg_settings['template_path']);

		if(isset($_REQUEST['showAll']) && intval($_REQUEST['showAll'])==1)
			$this->pg_settings['g_catpgmax']=0;

		if(!isset($this->pg_settings['ed_lang']))
			$this->pg_settings['ed_lang']='english';
		$this->g_abs_path=($this->pg_settings['g_use_abs_path'])?dirname($this->full_script_path).'/':'';
		$this->use_friendly=isset($this->pg_settings['g_fields_array']['friendlyurl']);
		$this->use_publish_dates=isset($this->pg_settings['g_fields_array']['published_date'])&&isset($this->pg_settings['g_fields_array']['unpublished_date']);
		$this->shop=$settings['page_type']==SHOP_PAGE;
		if($this->shop)
		{
		  include_once($this->rel_path.'ezg_data/shop_modules.php');
		  $this->g_symlinkpath=$this->rel_path.'innovaeditor/assets/admin/symlinks/';
		  $this->InvoiceModule=new Invoice($this);
		  $this->couponsModule=new Coupons($this);
		  $this->serialsModule=new Serials($this);
		  $this->stockModule=new Stock($this);
		  $this->bundleModule=new Bundles($this);
		  foreach($this->pg_settings['g_checkout_str'] as $k=>$v)
		  {
				if($k!=='')
				{
					include_once($this->rel_path.'ezg_data/'.str_replace('.','',$k).'_module.php');
					$this->paymentModule=new paymentHandler($this);
					break;
				}
		  }
		}
	}

	function get_lister_categoryID($cname,$default='')
	{
		$cname=Formatter::stripTags(urldecode($cname));
		foreach($this->categoriesModule->category_array as $v)
		{
			$category=isset($v['linked'])?$v['linked']['name']:$v['name'];
			if($category==$cname)
				return $v['id'];
			elseif(str_replace('+',' ',$category)==$cname)
				return $v['id'];
		}
		if(intval($cname)>0)
			return intval($cname);
		$id=$this->categoriesModule->get_categoryidbyname(trim($cname));
		if($id!==false)
			return $id;
		return $default;
	}

	public function float_val($ptString) 
	{
		if (strlen($ptString) == 0) {
			return false;
		}
		$pString = str_replace(" ", "", $ptString);
		if (substr_count($pString, ",") > 1)
			$pString = str_replace(",", "", $pString);
		if (substr_count($pString, ".") > 1)
			$pString = str_replace(".", "", $pString);
		$pregResult = array();
		$commaset = strpos($pString,','); 
		if ($commaset === false) {$commaset = -1;}
			$pointset = strpos($pString,'.');
		if ($pointset === false) {$pointset = -1;}
			$pregResultA = array();
		$pregResultB = array();
		if ($pointset < $commaset) {
			preg_match('#(([-]?[0-9]+(\.[0-9])?)+(,[0-9]+)?)#', $pString, $pregResultA); 
		}
		preg_match('#(([-]?[0-9]+(,[0-9])?)+(\.[0-9]+)?)#', $pString, $pregResultB);
		if ((isset($pregResultA[0]) && (!isset($pregResultB[0])
		  || strstr($preResultA[0],$pregResultB[0]) == 0
		  || !$pointset))) {
			$numberString = $pregResultA[0];
			$numberString = str_replace('.','',$numberString);
			$numberString = str_replace(',','.',$numberString);
		}
		elseif (isset($pregResultB[0]) && (!isset($pregResultA[0])
		  || strstr($pregResultB[0],$preResultA[0]) == 0
		  || !$commaset)) {
			$numberString = $pregResultB[0];
			$numberString = str_replace(',','',$numberString);
		}
		else {
			return false;
		}
		$result = (float)$numberString;
		return $result;
	}

	public function nrf($v,$useConversion=0)
	{
		$v=$this->float_val($v);

		if($useConversion)
			$v=$this->float_val($v*$this->float_val($this->all_settings['conversion_rate']));
		return number_format($v,$this->pg_settings['g_price_decimals'],$this->pg_settings['g_decimal_sign'],$this->pg_settings['g_thousands_sep']);
	}

	public function nrf2($v,$no_thous_sep=false,$useConversion=0)
	{
		$v=$this->float_val($v);

		if($useConversion)
		$v=$this->float_val($v*$this->float_val($this->page->all_settings['conversion_rate']));
			return number_format($v,$this->pg_settings['g_price_decimals'],'.',($no_thous_sep?'':','));
	}


	public function get_price_value($data)
	{
		if(isset($data['sale_price']) && intval($data['sale_price'])>0)
			return $this->float_val($data['sale_price']*$this->float_val($this->all_settings['conversion_rate']));
		else
			return $this->float_val($data[$this->pg_settings['g_pricefield']]*$this->float_val($this->all_settings['conversion_rate']));
	}

	public function get_shippingweight($data)
	{
		if(isset($data['shippingweight']) && $this->float_val($data['shippingweight'])>0)
			return $this->float_val($data['shippingweight']);
		else
			return 0.00;
	}
	public function get_output_price($price)
	{
		if($this->pg_settings['g_thousands_sep']==",")
			return $this->float_val($price);
		else
		{
			$float_price=$this->float_val(str_replace(',', '.',str_replace('.','',$price)));
			return $float_price;
		}
	}

	public function shop_js_functions($src,&$js)
	{
		$ui_fields=($this->get_setting('ui_req')=='1')?$this->get_userinput_fields():array();
		$js.='
		var use_ajax_rq='.($this->all_settings['use_ajax']?'1':'0').';
		'.(strpos($src,'multibuy_ajax')!==false?'
		$(document).ready(function(){
			if($(".bundle_all_wrap").length==0)
				$("input.quantity").val(0);
		});
		var multibuy_qua_err;
		function multibuy_ajax(forms_id,qua_class)
		{
			forms_id=typeof forms_id !== "undefined" ? forms_id : "qua_";
			qua_class=typeof qua_class !== "undefined" ? qua_class : "quantity";
			if(!use_ajax_rq) return false;
			window.multibuy_qua_err=0;
			var qua_forms=$("form[id^="+forms_id+"]"),
			qua_forms_c=qua_forms.length,
			items_data=[],i=0;
			qua_forms.each(function(){
				var num_id=$(this).attr("id").replace(forms_id,"");
				if(/^\d+$/.test(num_id)&&$(this).find("input[name=\'action\']").val()=="add")
				{
					obj_item=validate_qua(forms_id+num_id,true,true,qua_forms_c,qua_class);
					if (obj_item === Object(obj_item))
					{
						items_data[i]=new Object();
						$.each(obj_item,function(){
							if(this.name!="r"&&this.name!="action")
								items_data[i][this.name]=this.value;
						});
						i++;
					}
				}
			});
			if(items_data.length)
			{
				var cart=get_Cartdiv();
				if(cart!==false)
				{
					$(".multibuy_loader").show();
					$.ajax({
						type: "post",
						url: $(qua_forms[0]).attr("action")+"?action=multibuyajax"+(is_basketCart()?"&basket_cart=1":""),
						data: {"items" : JSON.stringify(items_data)},
						success: function(re) {
							$(".multibuy_loader").hide();
							replace_cart(re,cart.attr("id"));
						}
					});
				}
			}
		}':'')
		.'function remove_item_ajax(d_url,budle_id)
		{
			budle_id=typeof budle_id !== "undefined" ? budle_id : "";
			if(budle_id!="")
			{
				$str = "iid_arr[]="+ $(".bundle_id"+budle_id).map(function() {return parseInt(this.value)||0;}).get().join("&iid_arr[]=");
				$str += "&bundle_info[]="+ $(".bundle_id"+budle_id).map(function() {return this.getAttribute("data-info")}).get().join("&bundle_info[]=");
				d_url = d_url.replace(d_url.match(/(iid\=)\d+/g),$str);
			}
			var cart=get_Cartdiv();
			if(!use_ajax_rq||!cart)
				window.location=d_url;
			else if(cart!==false)
			{
				$.ajax({
					type: "get",
					url: d_url.replace(/(action=remove)/g,"action=removeajax").replace(/(action=cleanup)/g,"action=cleanupajax").replace(/(cleanup=)/g,"action=cleanupajax")
					+(is_basketCart()?"&basket_cart=1":""),
					success: function(re) {
						replace_cart(re,cart.attr("id"));
					}
				});
			}
		}
		function update_basket(id,budle_id)
		{
			budle_id=typeof budle_id !== "undefined" ? budle_id : 0;
			$str="";
			if(budle_id!=""){
				$str = "iid_arr[]="+ $(".bundle_id"+budle_id).map(function() {return parseInt(this.value)||0;}).get().join("&iid_arr[]=");
				$str += "&bundle_info[]="+ $(".bundle_id"+budle_id).map(function() {return this.getAttribute("data-info")}).get().join("&bundle_info[]=");
			}
			var cart=get_Cartdiv();
			var frm=$("#"+id);
			frm_ser=frm.serialize();
			if($str!="")
				frm_ser=frm_ser.replace(frm_ser.match(/(iid\=)\d+/g),$str);
			if(cart!==false&&is_basketCart()&&frm.find("input[name=\'action\']").val()=="update"&&use_ajax_rq){
				$.get(frm.attr(\'action\'),frm_ser.replace(/(action=update)/g,"action=updateajax")+"&basket_cart=1", function(re){
					replace_cart(re,cart.attr("id"));
				});
			}
			else{
				$.get(frm.attr(\'action\'),frm_ser, function(re){
					location.reload();
				});
			}
		}
		function add_item_ajax(frm)
		{
			var cart=get_Cartdiv();
			if(cart!==false&&use_ajax_rq){
				if(frm.find("input[name=\'action\']").val()!="add")
					return true;
				$.get(frm.attr("action"),frm.serialize().replace(/(action=add)/g,"action=addajax")+(is_basketCart()?"&basket_cart=1":""),function(re){
					replace_cart(re,cart.attr("id"));
				});
				return false;
			}
			return true;
		}
		function replace_cart(re,cart_id)
		{
			if($("#"+cart_id).length==0)
				return false;
			$("#"+cart_id).empty().replaceWith(re);
			hdiv=$("#"+cart_id).parents(".hiddenDiv");
			if(hdiv.length==1)
				show_Hdiv(hdiv.attr("id"),1000,null,1,1,1,0);
		}
		function get_Cartdiv(){return $("#minicart").length==1?$($("#minicart")):(is_basketCart()?$("#basketcart"):false)}
		function is_basketCart(){return $("#basketcart").length==1}
		function validate_opt(id,sid){
			 sel=$("#"+id+ " ."+sid).is("select");
			 if(sel) {
				  opt=$("#"+id+ " ."+sid).val();
				  fv=$("#"+id+" ."+sid+" :selected").text();
			 } else {
				  opt=$("input[name=\'"+sid+"\']:checked").val();
				  fv=$("input[name=\'"+sid+"\']").first().next().text();
			 }
			 if(opt==""){ealert(fv+"!");return false;}
			 return true;
		}
		function validate_qua(id,ch,multi_buy,qua_count,qua_class){
		multi_buy=typeof multi_buy !== "undefined" ? multi_buy : false;
		qua_count=typeof qua_count !== "undefined" ? qua_count : 0;
		qua_class=typeof qua_class !== "undefined" ? qua_class : "quantity";
		qua=parseInt($("#"+id+ " ."+qua_class).val()) || 0;
		//if(qua<1) qua=1;
		var m=ch?1:0;
		if(qua<m){
			if(multi_buy){
				if(++window.multibuy_qua_err==qua_count)
					ealert("'.$this->lang_l('quantity').'");
			}
			else
				ealert("'.$this->lang_l('quantity').'");
			return false;
		};'
		.(count($ui_fields)>0?'
		var al=0,uifields=new Array("'.implode('","',$ui_fields).'");
		$.each( uifields,function(i,v){
			if($("#ui_"+v+"_"+id).val()=="") {
				al=1;return false;
			};
		});
		if(al) {ealert("'.$this->lang_l('input required').'");return false;};':'');

		if($this->stockModule->hide_empty())
			$js.='sto=parseInt($("#"+id+ " .stock").val());
				 if(qua>sto){ealert("'.$this->lang_l("more than in stock!").' ("+sto+")");return false;};';

		foreach($this->optionsModule->options as $k=>$opt)
			if($this->get_setting($opt.'_req')=='1')
				$js.='if($("#"+id+" .subtype'.$k.'").length) if(!validate_opt(id,"subtype'.$k.'")) return false;';
		$js.='if(multi_buy) return $("#"+id).serializeArray();else return add_item_ajax($("#"+id))};';
	}

	public function finalize_output($src)
	{
		if($this->pg_settings['g_ssl_checkout'])
		{
			$url=str_replace('http:','https:',$this->full_script_path);
			$src=str_replace('"'.$this->pg_name.'?action=checkout"','"'.$url.'?action=checkout"',$src);
		}
		$src=Builder::multiboxImages($src,$this->rel_path);

		if(preg_match('/%bundles%/i',$src))
			$this->bundleModule->replaceBundleMacro($src);
		if(strpos($src,'class="xzoom')!==false)
			$this->page_scripts.='$(document).ready(function(){$(".xzoom").xzoom();});';

		if(strpos($src,'class="niceSelect')!==false)
		{
			$this->page_scripts.='$(document).ready(function(){$(".niceSelect").niceSelect({lang_l:{"confirm":"'.$this->lang_l('confirm').'","close":"'.$this->lang_l('close').'"}});});';
			$this->page_dependencies[]='documents/lister.css';
		}
		if(strpos($src,'validate_qua')!==false||strpos($src,'add_item_ajax')!==false||strpos($src,'multibuy_ajax')!==false||strpos($src,'remove_item_ajax')!==false)
			$this->shop_js_functions($src,$this->page_scripts);
		if(strpos($src,'update_price')!==false)
		{
			$js='
	function addCommas(ds,ts,nStr){
		nStr+="";
		x=nStr.split(ds);
		x1=x[0];x2=x.length>1?ds+x[1]:"";
		var rgx=/(\d+)(\d{3})/;
		if(rgx.test(x1)){
			x1=x1.replace(rgx,"$1"+ts+"$2");
		};
		return x1+x2;
	}

	function update_price(th,id,price){
		m=-1;v=$(th).val();cd='.($this->pg_settings['g_decimal_sign']==','?'1':'0').';
		p=v.indexOf("[");
		if(p==-1) {nv=price;v=0;}
		else {
		  v=v.substr(p);
			p=v.indexOf(" ");
			pc=v.indexOf("%");
			m=v.indexOf("*");
			if(m>-1)
				v=v.replace("*","");
			if(p==-1)
				p=v.indexOf("]");
			v=v.substr(1,p-1);
			if(cd)
				v=v.replace(",",".");
			else
				v=v.replace(",","");
			v=parseFloat(v);
			if(pc>-1) v+="%";
		}
		if($(th).is(":radio"))
			$(th).parent().attr("rel",v);
		else
			$(th).attr("rel",v);

		nv=price;
		bp=$(th).parents(".post_content,form").find(".ups").each(function(){
		   off=$(this).attr("rel");
			pc=off.indexOf("%");
			if(m>-1)
				nv+=price*(parseFloat(off)-1);
			else if(pc>-1)
				nv+=price*(parseFloat(off)/100);
			else
				nv+=parseFloat(off);
		});
		nv=nv.toFixed('.$this->pg_settings['g_price_decimals'].');
		if(cd)nv=nv.replace(".",",");
		nv=addCommas("'.$this->pg_settings['g_decimal_sign'].'","'.$this->pg_settings['g_thousands_sep'].'",nv);
		$(".price_value_"+id).html(nv);
	};';

			$this->page_scripts.=$js;
		}

		Captcha::includeCaptchaJs($src,$this->rel_path,$this->page_scripts,$this->page_dependencies);

		if($this->pg_settings['enable_comments'] && strpos($src,'comments_comments')!==false)
		{
			$this->commentModule->add_comments_js($src);
			$this->page_css.=Builder::getDirectEditCSS($this->rel_path);
			$this->page_scripts.=Builder::getDirectEditJS('post_wrap',$this->script_path);
		}
		elseif($this->user->userCanEdit($this->page_info,$this->rel_path,$this->edit_own_posts_only))
			$this->page_css.=Builder::getDirectEditCSS($this->rel_path);

		if(strpos($src,'class="ranking"')!==false)
			$this->page_scripts.=$this->rankingVisitsModule->get_rankingScript();

		if(strpos($src,'{%POLL_ID(') !== false)
		{
			$this->page_scripts.=Formatter::replacePollMacro($src,$this->rel_path,$this->page_lang);
			$this->page_dependencies[]='documents/poll.css';
		}

		if(strpos($src,'{%SLIDESHOW_ID(') !== false)
		{
			$slideshow=new Slideshow();
			$slideshow->replaceSlideshowMacro($src,$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
		}

		if(strpos($src,'{%HTML5PLAYER_ID(') !== false)
		{
			$slideshow=new Slideshow();
			$slideshow->replace_html5playerMacro($src,$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
		}

		if(strpos($src,'%WISHLIST_LINK%')!==false)
			$this->wishlistModule->replace_WihslistLink($src);

		if($this->page_scripts!='')
			$src=Builder::includeScript($this->page_scripts,$src,$this->page_dependencies,$this->rel_path);
		if($this->page_css!='')
			$src=Builder::includeCss($src,$this->page_css);

		if($this->parseGfonts)
		  $src=Builder::includeGFonts($src);
		return $src;
	}

	public function parse_categories($page)
	{
		$categories_param='%CATEGORIES';
		$page=str_replace(array('%SHOP_CATEGORIES','%LISTER_CATEGORIES'),'%CATEGORIES',$page);
		if(strpos($page,$categories_param.'(') !==false)
		{
			$cat_string=Formatter::GFS($page,$categories_param.'(',')%');
			$page=str_replace($categories_param.'('.$cat_string.')%',sprintf('<a class="rvts4" href="%s">%s</a>',$this->g_abs_path.$this->pg_name,$cat_string),$page);
		}
		if(strpos($page,$categories_param.'%')>0)
		{
			$cat_string=$this->show_category(1);
			$page=str_replace($categories_param.'%',$cat_string,$page);
		}
		return $page;
	}

	public function resolve_template($mode)
	{
		switch($mode)
		{
		 case MODE_LIST : return $this->pg_id.'.html';
		 case MODE_CATEGORY : return $this->pg_settings['g_cid'].".html";
		 case MODE_DETAIL : return $this->pg_settings['g_did'].".html";
		 case MODE_CART: return ($this->pg_id+4).".html";
		 case MODE_CHECKOUT : return ($this->pg_id+3).".html";
		 case MODE_WISHLIST : return ($this->pg_id+7).".html";
		}
		return '';
	}

	public function get_pagetemplate($pagename,$usefolder=false)
	{
		$page='';
		$pname=$this->g_page_prefix.$pagename;
		if(!file_exists($pname))
			$pname=$pagename;
		if($usefolder)
			$pname=$this->pg_dir.$pname;
		$fs=filesize($pname);
		if($fs==0 && $this->g_page_prefix!='')
		{
			$pname=$this->g_page_prefix.$pagename;
			if($usefolder)
				$pname=$this->pg_dir.$pname;
		}
		$page=File::read($pname);

		if(strpos($page,'%COMPARE_ADD%')!==false)
		  $page=str_replace('%COMPARE_ADD%',$this->featuresModule->handle_compare('addbtn'),$page);
		$page=ListerFunctions::replace_lister_tags($page);
		return $page;
	}

	protected function count_vat($price)
	{
		if($this->session_vat_1==0 && $this->session_vat_2==0)
			$vat=0;
		elseif($this->session_vat_c==1)
		{
			$vat1=$price*($this->session_vat_1/100);
			$vat=$this->session_vat_2>0?$vat1*($this->session_vat_2/100):$vat1;
		}
		else
		{
			$vat1=$price*($this->session_vat_1/100);
			$vat2=$price*($this->session_vat_2/100);
			$vat=$vat1+$vat2;
		}
		return $vat;
	}

	protected function parse_totals($src,$price_total,$vat_total,$shop_shipping,$itc,$itc2,$force_vat=false,$products_array=array())
	{
		$sub_tot_exc=$sub_tot=$shop_vat_sub=$tot_exc=$tot=$shop_shipping_exc=0.00;

		if(!$force_vat)
			$vat_tot=0;

		$src=$this->parse_totals2($src,$price_total,$vat_total,$shop_shipping,$itc,$itc2,
			$sub_tot_exc,$sub_tot,$shop_vat_sub,$tot_exc,$tot,$vat_tot,$shop_shipping_exc,$force_vat,$products_array);

		return $src;
	}

	protected function parse_totals2($src,$price_total,$vat_total,$shop_shipping,$itc,$itc2,
	&$sub_tot_exc,&$sub_tot,&$shop_vat_sub,&$tot_exc,&$tot,&$vat_tot,&$shop_shipping_exc,$force_vat=false,$products_array=array())
	{
		$coupon_used=$this->couponsModule->is_coupon();

		$currency=$this->pg_settings['g_currency'];
		$shipping_vat=$this->all_settings['g_ship_vat'];
		$shop_shipping_exc=0;
		if($this->pg_settings['g_excmode'])
		{
			$this->session_total=$price_total;
			$sub_tot_exc_bc=$price_total;
			if($coupon_used)
			{
				$this->couponsModule->update_price($price_total);
				if($this->pg_settings['g_tax_handling'])
					$vat_total=$this->count_vat($price_total);
				else
					$vat_total=round(($price_total*($products_array[0]['ol_vat']/100)),$this->pg_settings['g_price_decimals']);
			}
			$sub_tot_exc=$price_total;
			$vat_tot=$vat_total;
			$sub_tot=$price_total+$vat_tot;
			$shop_vat_sub=$vat_tot;
			if($shop_shipping!=0)
			{
				if($this->pg_settings['g_tax_handling'])
				{
					if(!$force_vat && $this->session_vat_as)
						$vat_tot+=$this->count_vat($shop_shipping);
				}
				else $vat_tot+=$shop_shipping*($shipping_vat/100);
				$price_total+=$shop_shipping;
				$shop_shipping_exc=$shop_shipping;
				if($this->pg_settings['g_tax_handling'])
				{
					if(!$force_vat && $this->session_vat_as)
						$shop_shipping+=$this->count_vat($shop_shipping);
				}
				else $shop_shipping+=$shop_shipping*($shipping_vat/100);
			}
			$tot_exc=$price_total;
			$tot=$price_total+$vat_tot;
		}
		else
		{
			$sub_tot=$price_total;
			$vat_tot=$vat_total;
			$sub_tot_exc=$price_total-$vat_tot;
			$shop_vat_sub=$vat_tot;
			if($shop_shipping!=0)
			{
				$shop_shipping_exc-=($shop_shipping*$shipping_vat)/($shipping_vat+100);
				$price_total+=$shop_shipping;
				$vat_tot+=($shop_shipping*$shipping_vat)/($shipping_vat+100);
			}
			$tot=$price_total;
			if($coupon_used)
			{
				$tot-=$shop_shipping;
				$this->couponsModule->update_price($tot);
				$tot+=$shop_shipping;
				$vat_tot=$tot*$shipping_vat/($shipping_vat+100);//not correct, assume shipping vat is same as products vat
			}
			$tot_exc=$price_total-$vat_tot;
			$this->session_total=$tot;
			$sub_tot_exc_bc=$tot;
		}

		//before shipping
		$src=str_replace(array('%SHOP_SUB_TOTAL%','%SUB_TOTAL%'),$this->nrf($this->pg_settings['g_excmode']?$sub_tot_exc:$sub_tot),$src);
		$src=str_replace('%SUB_TOTAL_BC%',$this->nrf($sub_tot_exc_bc),$src);
		$src=str_replace(array('%SHOP_SUB_TOTAL_EX%','%SUB_TOTAL_EX%'),$this->nrf($sub_tot_exc),$src);
		$src=str_replace('%SHOP_SUB_TOTAL_VAT%',$this->nrf($shop_vat_sub),$src);
		//after shipping
		$src=str_replace(array('%SHOP_VAT_TOTAL%','%VAT_TOTAL%'),$this->nrf($vat_tot),$src);
		$src=str_replace(array('%SHOP_TOTAL%','%TOTAL%'),$this->nrf($tot),$src);
		$src=str_replace('%SHOP_TOTAL_DS%',number_format(floatval($tot),2,'.',''),$src);
		$src=str_replace('%SHOP_TOTAL_NC%',number_format(floatval($tot),$this->pg_settings['g_price_decimals'],',',''),$src);
		$src=str_replace('%SHOP_TOTAL_EX%',$tot_exc,$src);

		$src=str_replace(array('%SHOP_SHIPPING%','%SHIPPING%'),$this->nrf($this->pg_settings['g_excmode']?$shop_shipping_exc:$shop_shipping),$src);
		$src=str_replace(array('%SHOP_SHIPPING_EX%','%SHOP_ITEMS_COUNT%','%SHOP_ITEMS_COUNT2%'),array($this->nrf($shop_shipping_exc),$itc,$itc2),$src);
		$src=$this->couponsModule->coupon_macro($src,$sub_tot_exc,$this->mode);

		$src=str_replace(array('%SHOP_CARTCURRENCY%','%CURRENCY%'),$currency,$src);
		return $src;
	}

	public function parse_minicart($src,$cat_id,$g_pagenr)
	{
		$src=str_replace(array('&lt;MINI_CART&gt;','&lt;/MINI_CART&gt;'),array('<MINI_CART>','</MINI_CART>'),$src);
		$src=str_replace(array('&lt;BASKET_CART&gt;','&lt;/BASKET_CART&gt;'),array('<BASKET_CART>','</BASKET_CART>'),$src);
		if(strpos($src,'<MINI_CART>') !== false)
		{
			$src=$this->cart('minicart',$cat_id,'',$g_pagenr,'',$src,'','','',0,'');
			$this->parse_page_id($src);
		}
		if(strpos($src,'<BASKET_CART>') !== false)
		{
			$result=$this->cart('show',$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'');
			$src=str_replace(Formatter::GFSAbi($src,'<BASKET_CART>','</BASKET_CART>'),'<div id="basketcart">'.$result.'</div>',$src);
		}
		return $src;
	}

	protected function fdownload()
	{
		global $db;
		$k=$_REQUEST['fname'];
		$rec_id=intval($_REQUEST['id']);
		$ftype=array_key_exists($k,$this->pg_settings['g_fields_array'])?
				  $this->pg_settings['g_fields_array'][$k]['itype']:
				  '';
		if($ftype=='file')
		{
			$fpath=$db->query_singlevalue('SELECT '.$k.' FROM '.$db->pre.$this->g_datapre.'data WHERE pid ='.$rec_id);
			if($fpath!=='')
			{
				 if(array_key_exists($k.'_count',$this->pg_settings['g_fields_array']))
					$db->query('
						UPDATE '.$db->pre.$this->g_datapre.'data
						SET '.$k.'_count = '.$k.'_count + 1
						WHERE pid='.$rec_id);

				 $this->process_file($fpath,basename($fpath));
			}
		}
		exit;
	}

	protected function readfile_chunked($file)
	{
		$chunksize=1024 * 1024;
		$buffer='';
		$cnt=0;
		$handle=@fopen($file,'r');
		if($size=@filesize($file))
			header("Content-Length: ".$size);
		if(false===$handle)
			return false;
		while(!@feof($handle))
		{
			$buffer=@fread($handle,$chunksize);
			echo $buffer;
			$cnt+=strlen($buffer);
		}
		$status=@fclose($handle);
		if($status)
			return $cnt;
		return $status;
	}

	protected function process_file($f_path,$fname)
	{
		if($this->pg_settings['g_symlink_used'])
		{
			$symlink = new Symlink($this,$this->g_symlinkpath,$this->session_transaction_id,$this->pg_settings);
			$f_path=Linker::relPathBetweenURLs($f_path,$this->g_symlinkpath);
			$symlink->checkSymlinksDeletion();
			$fileLink=$symlink->make($f_path,$fname);
			if($fileLink)
				header("Location: ".$fileLink);
			else
				echo "Unavailable";
			exit;
		}

		$safe_mode=ini_get('safe_mode');
		if(!$safe_mode)
			set_time_limit(86400);

		$ext=end(explode(".",strtolower($fname)));
		header("Cache-Control: ");
		header("Pragma: ");
		$mime=Detector::getMime($ext);
		header("Content-type: ".$mime);
		header("Content-disposition: attachment;filename=".urlencode($fname));
		header("Content-Transfer-Encoding: binary\n");
		$this->readfile_chunked($f_path);
		exit;
	}

	public function _build_fields($vars,$delim,$friendly)
	{
		$pa_fields='';
		foreach($vars as $rs=>$v)
		{
			$rsx=($friendly && isset($this->pg_settings['field_labels'][$rs]))?$this->pg_settings['field_labels'][$rs]:$rs;
			if($rsx!='cc')
				$pa_fields.=$rsx.'='.$v.$delim;
		}
		return $pa_fields;
	}

	public function send_mail($html_msg,$subject,$sfrom,$to,$pdf='',$use_attachments=false)
	{
		global $HTTP_POST_FILES,$db;

		$files_content=$files_names=$files_types=array();
		if(count($HTTP_POST_FILES))
		{
			$files=array();
			$files=array_keys($HTTP_POST_FILES);
			if(count($files))
			{
				foreach($files as $file)
				{
					$file_name=$HTTP_POST_FILES[$file]['name'];
					$file_type=$HTTP_POST_FILES[$file]['type'];
					$file_tmp_name=$HTTP_POST_FILES[$file]['tmp_name'];
					if(!strlen($file_type))
						$file_type="application/octet-stream";
					if($file_type=='application/x-msdownload')
						$file_type="application/octet-stream";
					$files_content[]=$file_tmp_name;
					$files_names[]=$file_name;
					$files_types[]=$file_type;
				}
			}
		}
		if($pdf!='')
		{
			$tmpfname=tempnam($this->rel_path."innovaeditor/assets/","invoice_");
			File::write($tmpfname,$pdf);
			$files_content[]=$tmpfname;
			$files_names[]=($this->all_settings['invoice_file_name']!=''?$this->all_settings['invoice_file_name']:'invoice').'.pdf';
			$files_types[]='application/pdf';
		}
		if($use_attachments)
		{
			for($i=1;$i<=COUNT_ATTACMENTS;$i++){
				if(isset($this->all_settings['attachment'.$i])&&$this->all_settings['attachment'.$i]!=''&&file_exists($this->all_settings['attachment'.$i])){
					$att = $this->all_settings['attachment'.$i];
					$last_backslash = strrpos($att,'/');
					$basename = substr($att, $last_backslash+1, strlen($att));
					if($basename!=''&&pathinfo($att,PATHINFO_EXTENSION)!='') {
						$data = file_get_contents($att);
						$tmpfname = tempnam($this->rel_path."innovaeditor/assets/","attachment".$i.'_');
						File::write($tmpfname,$data);
						$files_content[] = $tmpfname;
						$files_names[] = $basename;
						$files_types[] = mime_content_type($att);
					}
				}
			}
		}

		$to_array=explode(';',$to);
		return MailHandler::sendMailStat($db,$this->pg_id,$to_array,$sfrom,$html_msg,
				  '',$subject,$this->pg_settings['page_encoding'],
				  $files_content,$files_names,$files_types);
	}

	public function getHtmlTemplate($html_output,$scripts='')
	{
		$title=$this->page_info[0].' &raquo; '.$this->lang_l('administration panel');
		$result=Formatter::fmtInTemplate($this->pg_dir.$this->pg_id.'.html',$html_output,$title,$scripts);
		return $result;
	}

	public function parse_page_id(&$src)
	{
		$src=str_replace(array('%SHOP_PAGE_ID%_lister.php','%SHOP_PAGE_ID%'),
				  array($this->full_script_path,$this->pg_id),$src);
	}

	protected function check_fields($vars,$miniform)  //required fields with _re
	{
		$issues=array();
		$ccheck=isset($_POST['cc']) && $_POST['cc']=='1';

		if($this->pg_settings['g_mail_fields'])
		{
			foreach($this->pg_settings['g_mail_fields'] as $k=>$v)
			{
				if(isset($vars[$v]))
				{
					if(!Validator::validateEmail($vars[$v]))
						$issues[]=($ccheck?$v.'|':'').$this->pg_settings['lang_uc']['Email not valid'];
					if(isset($vars[$v.'_confirm']))
					{
						if($vars[$v] != $vars[$v.'_confirm'])
							$issues[]=($ccheck?$v.'_confirm|':'').$this->pg_settings['lang_uc']['Emails do not match'];
					}
				}
				if($issues)
					break;
			}
		}

		foreach($this->pg_settings['re_fields'] as $k=>$v)
		{
			$vx=str_replace(' ','_',$v);$label=$v;
			if(isset($this->pg_settings['field_labels'][$v]))
				$label=$this->pg_settings['field_labels'][$v];

			if(in_array($vx,$this->pg_settings['re_upfields']))
			{
				if((!isset($_FILES[$vx]['name']))||($_FILES[$vx]['name']==''))
					$issues[]=($ccheck?$vx.'|':'').$this->pg_settings['lang_uc']['Required Field'];
			}
			elseif(!isset($vars[$vx]))
				$issues[]=($ccheck?$vx.'|':'').$this->pg_settings['lang_uc']['Checkbox unchecked'];
			elseif(!strlen($vars[$vx]))
				$issues[]=($ccheck?$vx.'|':'').$this->pg_settings['lang_uc']['Required Field'];
			elseif($vx=='cardno')
			{
				$cnr=intval($vars[$vx]);
				if ((!ListerFunctions::is_valid_luhn($cnr)) || $cnr==4222222222222 || $cnr==4111111111111111 || $cnr==4012888888881881)
					$issues[]=($ccheck?$vx.'|':'').'Sorry, your credit card number is invalid, please check and enter it again!';
			}
		}

		if(!empty($issues))
			$issues[]=($ccheck?'error|':'').$this->pg_settings['lang_uc']['validation failed'];

		if($ccheck)
		{
			$output=implode('|',$issues);
			$useic=(!$this->f->uni && $this->pg_settings['page_encoding']!='iso-8859-1' && function_exists("iconv"));
			if($useic)
				$output=iconv($this->pg_settings['page_encoding'],"utf-8",$output);
			if($miniform)
			{
				if(count($issues)>0)
				{
					print '0'.$output;
					exit;
				}
			}
			else
			{
				if(count($issues)>0)
					print '0'.$output;
				else
					print '1';
				exit;
			}
		}

		if($issues)
		{
			$issues=F_BR.join(F_BR,$issues);
			$result=$this->get_pagetemplate(($this->pg_id+2).".html");
			$result=str_replace(Formatter::GFS($result,'<!--page-->',"<!--/page-->"),'%ERRORS%',$result);
			$result=str_replace("%ERRORS%",$issues,$result);
			$this->parse_page_id($result);
			print $result;
			Session::setVar('frmfields',$vars);
		}
		elseif(Session::isSessionSet('frmfields'))
			Session::unsetVar('frmfields');

		return $issues;
	}

	protected function parse_itemline($pt,$il,$itemid,$bcounter,$itemname,$itemprice,$itemvat,$itemshipping,$catdata_a,$itemcode,
	&$item_options,&$userdata,&$line_category)
	{
		$il=str_replace(array('<ITEM_ID>.<ITEM_QUANTITY>','<ITEM_INDEX>','<ITEM_ID>','<ITEM_QUANTITY>','<ITEM_CODE>'),
										array('<ITEM_ID>,<ITEM_QUANTITY>',$itemid,$this->basket->bas_itemid[$bcounter],$this->basket->bas_amount[$bcounter],$itemcode),$il);

		if(isset($this->pg_settings['g_checkout_callback_on'][$pt]) && $this->paymentModule!==false)
			$this->paymentModule->parseItemLineMacros($il,$itemid,$itemprice);
		else
		  $il=str_replace('<ITEM_AMOUNT>',$this->nrf2($itemprice),$il);

		$il=str_replace('<ITEM_VAT>',$this->nrf2($itemvat),$il);
		$il=str_replace('<ITEM_TAX>',number_format(($itemvat*$itemprice)/100,$this->pg_settings['g_price_decimals']),$il);
		$il=str_replace('<ITEM_SHIPPING>',$this->nrf2($itemshipping),$il);

		foreach($this->optionsModule->options as $k=>$opt)
		{
			$sub_val=isset($this->basket->bas_options[$bcounter][$k])?$this->basket->bas_options[$bcounter][$k]:'';

			if(strpos($il,'<ITEM_SUBNAME'.$k.'>'))
				$il=str_replace('<ITEM_SUBNAME'.$k.'>',$sub_val,$il);
			elseif($sub_val != '')
			{
				$item_options[$k]=' '.str_replace('=',' ',$sub_val);
				$off=Formatter::GFSAbi($item_options[$k],'[',']');
				$item_options[$k]=str_replace($off,'',$item_options[$k]);
			}
		}

		if(strpos($il,'<ITEM_USERDATA>'))
			$il=str_replace('<ITEM_USERDATA>',$this->basket->bas_userdata[$bcounter],$il);
		elseif($this->basket->bas_userdata[$bcounter] != '')
			$userdata=' '.$this->basket->bas_userdata[$bcounter];
		$userdata=str_replace('=',' ',$userdata);

		$options=is_array($item_options)?implode(' ',$item_options):'';
		if(strpos($il,'<ITEM_DESCRIPTION>')!==false)
			$il=str_replace(array('<ITEM_DESCRIPTION>','<ITEM_NAME>'),array($options,$itemname),$il);
		else
			$il=str_replace('<ITEM_NAME>',$itemname.$options,$il);
		$line_category=$catdata_a;
		$il=str_replace('<ITEM_CATEGORY>',$line_category,$il);
		return $il;
	}

	public function parse_itemlinecoupon($pt,$il,$itemid,$coupon_id,$couponamount)
	{
		$il=str_replace(array('<ITEM_ID>.<ITEM_QUANTITY>','<ITEM_INDEX>','<ITEM_ID>','<ITEM_QUANTITY>','<ITEM_NAME>','<ITEM_CATEGORY>','<ITEM_CODE>','<ITEM_DESCRIPTION>'),
										array('<ITEM_ID>,<ITEM_QUANTITY>',$itemid,$coupon_id,'1','discount','','',''),$il);
		$amPP=$this->nrf2($couponamount);
		$am=$this->nrf2(-$couponamount);

		if(isset($this->pg_settings['g_checkout_callback_on'][$pt]) && $this->paymentModule!==false)
			$this->paymentModule->parseItemLineMacros($il,$itemid,$couponamount);
		else
		  $il=str_replace('<ITEM_AMOUNT>',$am,$il);

		$il=str_replace('<ITEM_VAT>',$this->nrf2(0),$il);
		$il=str_replace('<ITEM_TAX>',$this->nrf2(0),$il);
		$il='&discount_amount_cart='.$amPP;
		return $il;
	}

	protected function parse_itemlineship($pt,$il,$itemid,$shop_shipping,$ship_vat)
	{
		$il=str_replace(array('<ITEM_ID>.<ITEM_QUANTITY>','<ITEM_INDEX>','<ITEM_ID>','<ITEM_QUANTITY>','<ITEM_NAME>','<ITEM_CATEGORY>','<ITEM_CODE>','<ITEM_DESCRIPTION>'),
										array('<ITEM_ID>,<ITEM_QUANTITY>',$itemid,'1000000','1','shipping','','',''),$il);

		if(isset($this->pg_settings['g_checkout_callback_on'][$pt]) && $this->paymentModule!==false)
			$this->paymentModule->parseItemLineMacros($il,$itemid,$shop_shipping);
		else
		  $il=str_replace('<ITEM_AMOUNT>',$this->nrf2($shop_shipping),$il);

		$il=str_replace('<ITEM_VAT>',$this->nrf2($this->all_settings['g_ship_vat']),$il);
		$il=str_replace('<ITEM_TAX>',number_format(($ship_vat*$shop_shipping)/100,$this->pg_settings['g_price_decimals']),$il);
		return $il;
	}

	protected function parse_shop_cart($pt,$src,$transId,&$products_array,&$items_lines_bw,$formfields,
			  &$vat_tot,&$price_total,&$cartstring,&$bundle_ordelines)
	{
		global $db;

		$item_vars_string=Formatter::GFS($src,'<ITEM_VARS>','</ITEM_VARS>');
		if($item_vars_string=='')
			$item_vars_string=Formatter::GFS($src,'<ITEM_VARS_LINE>','</ITEM_VARS_LINE>');
		$src=str_replace($item_vars_string,'',$src);
		$item_hashvars_string=Formatter::GFS($src,'<ITEM_HASHVARS>','</ITEM_HASHVARS>');
		$src=str_replace($item_hashvars_string,'',$src);

		$price_total=$vat_total=$shop_shipping=$shippingweight_total=0.00;
		$itemcounter=$products_count=0;
		$cart_string=$items_lines_bw=$items_hashlines=$description_total=$items_lines='';
		$products_array=array();

		$cartstring=$this->cart('show_final',0,'',0,'','','','','',0,'','');
		$src=str_replace('%SHOP_CART%','<div id="shop_cart">'.$cartstring.'</div>',$src);
		$src=str_replace(array('%SHOP_CARTCURRENCY%','%CURRENCY%'),$this->pg_settings['g_currency'],$src);

		if(count($this->basket->bas_itemid))
		{
			$bundle_primary_line=$bundle_description='';
			$bundle_price=$bundle_amount=$this_bundle_count=0;
			$itemid_bw=0;
			foreach($this->basket->bas_itemid as $k=>$basketid)  //getting record values
			{
				$record_line=$db->query_first('
					SELECT *
					FROM '.$db->pre.$this->g_datapre.'data
					WHERE pid ='.intval($basketid));
				$catdata_a=$record_line['cid'];

				$is_bundle=$this->basket->bas_bundle_primaryId[$k]!=null&&$this->basket->bas_bundle_discount[$k]!=null&&$this->basket->bas_bundle_id[$k]!=null
				&&$this->basket->bas_bundle_name[$k]!=null&&$this->basket->bas_bundle_count[$k]!=null;
				if($is_bundle)
				{
					$bundle_id=$this->basket->bas_bundle_id[$k];
					$bundle_discount=$this->basket->bas_bundle_discount[$k];
					$bundle_primary=$this->basket->bas_bundle_primaryId[$k];
					$bundle_name=$this->basket->bas_bundle_name[$k];
					$bundle_count=$this->basket->bas_bundle_count[$k];
					$this_bundle_count++;
					$is_primary=$bundle_primary==$basketid;
					$bundle_description.=$record_line['name'].($this_bundle_count==$bundle_count?'':' + ');
					$itemid_bw=$is_primary?$itemid_bw+1:$itemid_bw;
				}
				else
				{
					$bundle_primary_line=$bundle_description='';
					$bundle_price=$bundle_amount=$this_bundle_count=0;
					$itemid_bw++;
				}
				$itemid=$k+1;
				$bundle_ordelines[]=array(
					'ol_orderid'=>$transId,
					'ol_pid'=>$basketid,
					'ol_bundle_id'=>$is_bundle?$bundle_id:0,
					'ol_bundle_primary'=>$is_bundle?$bundle_primary:0,
					'ol_bundle_name'=>$is_bundle?$bundle_name:'',
					'ol_bundle_discount'=>$is_bundle?$bundle_discount:'',
					'ol_bundle_count'=>$is_bundle?$bundle_count:0
				);

				if($this->stockModule->hide_empty() && $this->basket->bas_amount[$k] > $record_line['stock'])
				{
					echo $record_line['name'].': '.$this->get_setting('stock_mess').'! <a href="javascript:history.back();">Back</a>';
					exit;
				}

				$itemname=$itemname_unenc=$record_line['name'];
				if($pt!='ideal')
					$itemname=urlencode($itemname);
				$itemprice=$this->get_price_value($record_line);
				$price_offset=$this->get_price_offset($k,$itemprice);
				$bundle_offset=0;
				if($this->basket->bas_bundle_discount[$k]!=null){
					$bundle_discount=$this->basket->bas_bundle_discount[$k];
					$off=Formatter::GFS($bundle_discount,'[',']');
					if(strpos($off,'%')!==false)
						$bundle_offset=$itemprice*((float)str_replace('%','',$off)/100);
				}
				$itemprice=$itemprice+$price_offset+$bundle_offset;
				$itemcode=$record_line['code'];
				if($this->pg_settings['g_excmode'] && $this->pg_settings['g_tax_handling'])
					$itemvat=($this->count_vat(1))*100;
				else
					$itemvat=($this->pg_settings['g_vatfield']!='')?floatval($record_line[$this->pg_settings['g_vatfield']]):0.00;
				$itemshipping=$this->g_ship_usefield?floatval($record_line['shipping']):0.00;

				$item_options=array();
				$userdata=$line_category='';

				$items_lines=$items_lines.$this->parse_itemline($pt,$item_vars_string,$itemid,$k,$itemname,$itemprice,$itemvat,$itemshipping,$catdata_a,$itemcode,$item_options,$userdata,$line_category);
				if($item_hashvars_string !== '')
					$items_hashlines=$items_hashlines.$this->parse_itemline($pt,$item_hashvars_string,$itemid,$k,$itemname,$itemprice,$itemvat,$itemshipping,$catdata_a,$itemcode,$item_options,$userdata,$line_category);
//bankwire
				$options=implode(' ',$item_options);

				if($is_bundle)
				{
					$bundle_primary_line=$is_primary?'':$bundle_primary_line.'';
					$bundle_amount+=$this->basket->bas_amount[$k];
					$bundle_price+=$this->nrf2($itemprice);
					if($bundle_count==$this_bundle_count)
					{
						$bundle_primary_line=$itemid_bw.". ".$bundle_amount."x ".$bundle_name.' <i>'.$bundle_description.'</i>'
						.'  '.str_replace('**',' ',$this->basket->bas_userdata[$k])
						.' '.$bundle_price.' '.$this->pg_settings['g_currency'].'<br>';
						
						$items_lines_bw.=$bundle_primary_line;
						$bundle_primary_line=$bundle_description='';
						$bundle_price=$bundle_amount=$this_bundle_count=0;
					}
				}
				else
				{
					$items_lines_bw.=$itemid_bw.". ".$this->basket->bas_amount[$k]."x ".$itemname_unenc.$options
						.'  '.str_replace('**',' ',$this->basket->bas_userdata[$k])
						.' ID['.$this->basket->bas_itemid[$k].'] '.$this->nrf($itemprice).' '.$this->pg_settings['g_currency'].' '
						.($itemcode!==''?$this->lang_l('code').'['.$itemcode.']':'').'<br>';
				}
	//pending strings
				$temp=array('ol_orderid'=>$transId,
					 'ol_pid'=>$this->basket->bas_itemid[$k],
					 'ol_amount'=>$this->basket->bas_amount[$k],
					 'ol_price'=>$this->nrf2($itemprice,true),
					 'ol_vat'=>$itemvat,
					 'ol_shipping'=>$itemshipping,
					 'ol_userdata'=>$this->basket->bas_userdata[$k]);

				 foreach($this->basket->bas_options[$k] as $subtype_id=>$subtype_val)
					$temp['ol_option'.$subtype_id]=$this->optionsModule->getSelectedValue($record_line,$subtype_id,$subtype_val);

				$products_array[]=$temp;

				$description_total=($description_total=='')? $itemname : $description_total.','.$itemname;

				$price_total+=($itemprice*$this->basket->bas_amount[$k]);
				$shippingweight_total+=$this->get_shippingweight($record_line)*$this->basket->bas_amount[$k];
//vat
				if($itemvat>0)
				{
					if($this->pg_settings['g_excmode'])
					{
						if($this->pg_settings['g_tax_handling'])
							$vat_total+=round(($this->count_vat($itemprice*$this->basket->bas_amount[$k])),$this->pg_settings['g_price_decimals']);
						else
							$vat_total+=round(($itemprice*$this->basket->bas_amount[$k])*($itemvat/100),$this->pg_settings['g_price_decimals']);
					}
					else
						$vat_total+=(($itemprice*$this->basket->bas_amount[$k])*($itemvat)) / ($itemvat+100);
				}

				if($itemshipping!=0)
					$shop_shipping+=($this->basket->bas_amount[$k]*$itemshipping);
				$itemcounter+=$this->basket->bas_amount[$k];
				$products_count++;
				$cart_string=$cart_string.$this->basket->bas_catid[$k].','.$this->basket->bas_itemid[$k].','.$this->basket->bas_amount[$k].'|';
			}
		}

		if($price_total==0)
			$items_lines='';
		else
			$shop_shipping=$this->count_shipping($price_total,$itemcounter,$shop_shipping,$shippingweight_total);

		if($this->pg_settings['g_excmode'])
			$items_lines_bw.="SubTotal=".$this->nrf($price_total)." ".$this->pg_settings['g_currency'].'<br><br>';

		if($shop_shipping!=0)
		{
			$itemid++;
			if($this->pg_settings['g_excmode'] && $this->pg_settings['g_tax_handling'])
				$shipvat=($this->count_vat(1))*100;
			else
				$shipvat=$this->all_settings['g_ship_vat'];
			if(strpos($item_vars_string,'<ITEM_SHIPPING>')===false)
				$items_lines.=$this->parse_itemlineship($pt,$item_vars_string,$itemid,$shop_shipping,$shipvat);
			if($item_hashvars_string !== '')
				$items_hashlines.=$this->parse_itemlineship($pt,$item_hashvars_string,$itemid,$shop_shipping,$shipvat);
			$products_count++;
//bankwire
			$items_lines_bw.='<br>'.$this->lang_l('shipping').'='.$this->nrf($shop_shipping).' '.$this->pg_settings['g_currency'].'<br><br>';
//pe
			$pa=array(
					'ol_orderid'=>$transId,
					'ol_pid'=>'1000000',
					'ol_amount'=>'1',
					'ol_price'=>$this->nrf2($shop_shipping,true),
					'ol_vat'=>$this->all_settings['g_ship_vat'],
					'ol_shipping'=>$itemshipping,
					'ol_userdata'=>'');
			foreach($this->optionsModule->options as $k=>$opt)
					$pa['ol_'.$opt]='';

			$products_array[]=$pa;
		}

		$this->couponsModule->addCouponLines($items_lines_bw,$price_total);
		$this->couponsModule->addCoupontoCartLines($pt,$item_vars_string,$itemid,$items_lines,$price_total);

		$sub_tot_exc=$sub_tot=$shop_vat_sub=$tot_exc=$tot=$vat_tot=$shop_shipping_exc=floatval(0);
		$src=$this->parse_totals2($src,$price_total,$vat_total,$shop_shipping,$products_count,$itemcounter,
				$sub_tot_exc,$sub_tot,$shop_vat_sub,$tot_exc,$tot,$vat_tot,$shop_shipping_exc,false,$products_array);

		$return_url=str_replace(':','%3A',$this->full_script_path).'?';
		$src=str_replace(array('%SHOP_RETURN_URL%','%SHOP_CALLBACK_URL%','%SHOP_CURRENCY%','%SHOP_CANCELRETURN_URL%',
			'%SHOP_RETURN_URL_ENC%','%SHOP_CANCELRETURN_URL_ENC%'),
			array($return_url.'action=return',$return_url.'action=callback',$this->pg_settings['g_currency'],$return_url.'action=cancel',
			urlencode($this->full_script_path.'?action=return'),urlencode($this->full_script_path.'?action=cancel'))
			,$src);
		$price_f=number_format($tot,$this->pg_settings['g_price_decimals']);

		if(is_array($formfields))
			foreach($formfields as $k=>$v)
				$src=str_replace('%'.$k.'%',$v,$src);

		$src=str_replace('%SHOP_TOTAL_CENTS%',$price_f*100,$src);
		if($this->paymentModule!==false)
			$this->paymentModule->parseCartMacros($src,$tot);
		$src=str_replace('%SHOP_TOTAL%',$price_f,$src);


		$price_f=number_format($tot,$this->pg_settings['g_price_decimals']);
		$price_f=preg_replace('/[^0-9]/','_',$price_f);
		$src=str_replace(
				array('%SHOP_DATETIME%','%SHOP_TRANS_NR%','%SHOP_TRANS_ID%','%SHOP_DESCRIPTION_TOTAL%','%SHOP_CART_STRING%'),
				array(Date::tzoneNow(),$transId,$transId.'_'.sha1($price_f.'_'.$transId),$description_total,$cart_string),
				$src);
		$src=str_replace('<ITEM_VARS></ITEM_VARS>',$items_lines,$src);
		$src=str_replace('<ITEM_VARS_LINE></ITEM_VARS_LINE>',$items_lines,$src);
		$src=str_replace('<ITEM_HASHVARS></ITEM_HASHVARS>',$items_hashlines,$src);

		$hasstring=Formatter::GFSAbi($src,'%HASH(',')%');
		if($hasstring !== '')
			$src=str_replace($hasstring,ListerFunctions::CountHash(Formatter::GFS($src,'%HASH(',')%')),$src);

		$src=str_replace('%SHOP_IPUSER%',Detector::getIP(),$src);
		if($this->pg_settings['g_excmode'])
			$items_lines_bw.="Tax=".$this->nrf($vat_tot)." ".$this->pg_settings['g_currency'].'<br><br><br>';

		$items_lines_bw.="Order Total=".$this->nrf($tot)." ".$this->pg_settings['g_currency'].'<br><br><br>';

		return $src;
	}

	protected function checkout()
	{
		global $db;

		$this->global_pagescr='';
		$this->mode=MODE_CHECKOUT;
		$page=$this->get_pagetemplate($this->resolve_template($this->mode));
		$form=Formatter::GFSAbi($page,'<form name="frm"','</form>');
		$formx=$this->user->replaceUserFields($form,$db);
		if($form!=$formx)
			$page=str_replace($form,$formx,$page);

		$page=str_replace('%CHECKOUT_ACTION%',$this->pg_name.'?action=pay',$page);
		$page=$this->parse_minicart($page,0,0);

		if(Session::isSessionSet('frmfields'))
		{
			$vars=Session::getVar('frmfields');
			$old_frm=Formatter::GFSAbi($page,'<form name="frm"','</form>');
			$new_frm=$old_frm;
			foreach($vars as $k=>$v)
			{
				$v=strip_tags($v);
				if(strpos($new_frm,'name="'.$k.'">')!==false)
				{
					$select=Formatter::GFSAbi($new_frm,'name="'.$k.'">','</select>');
					$ss=($this->f->xhtml_on)?' selected="selected">':' selected>';
					$nselect=str_replace($ss,'>',$select);
					$nselect=str_replace('value="'.$v.'">','value="'.$v.'"'.$ss,$nselect);
					$new_frm=str_replace($select,$nselect,$new_frm);
				}
				elseif(strpos($new_frm,'name="'.$k.'" value="')!==false)
				{
					$input=Formatter::GFSAbi($new_frm,'name="'.$k.'" value="','>');
					$old_val=Formatter::GFSAbi($input,'value="','"');
					$ninput=str_replace($old_val,'value="'.$v.'"',$input);
					$new_frm=str_replace($input,$ninput,$new_frm);
				}
				else
					$new_frm=str_replace('name="'.$k.'"','name="'.$k.'" value="'.$v.'"',$new_frm);
			}
			$page=str_replace($old_frm,$new_frm,$page);
		}

		$dummy1=$dummy2=$dummy3=$cartstring ='';
		$vat_total=$price_total=0;
		$page=$this->parse_shop_cart('',$page,$this->session_transaction_id,$dummy1,$dummy2,'',$vat_total,$price_total,$cartstring,$dummy3);
		$page=str_replace($this->int_tags,'',$page);

		$this->parse_page_id($page);
		$page=$this->build_category_lists(0,$page);

		if($this->global_pagescr !== '')
			$page=str_replace('<!--scripts-->','<!--scripts-->'.$this->global_pagescr,$page);

		if($this->get_setting('min_order')>0 && ($price_total<$this->get_setting('min_order')))
		{
			$js='$(document).ready(function(){
				if($("#frm_error").length)
					$("#frm_error").html(\''.$this->get_setting('min_order_alert').'\');
			});';
			$page=str_replace('$(\'.frm\').submit();"','ealert(\''.$this->get_setting('min_order_alert').'\');"',$page);
			$page=Builder::includeScript($js,$page);
		}

		if($this->pg_settings['g_tax_handling'])
		{
			$sc='
			function checkstate(){$("#state,#x_state").trigger("change");};
			$(document).ready(
				function(){'
					.($this->pg_settings['g_check_shipping_list']!=''?'$("#shipping_list").val("'.$this->pg_settings['g_check_shipping_list'].'")':'').'
					$("#state,#country,#shipping_list").change(function(){
					cnt=$("#country").val();st=$("#state").val();shipping_list=$("#shipping_list").val();
					$.post("'.$this->pg_name.'?action=checkout",{do:"update_tax",country:cnt,state:st,shipping_list:shipping_list},function(re){$("#shop_cart").html(re);});});
					$("#x_state,#x_ship_to_state,#x_country,#shipping_list").change(function(){cnt=$("#x_country").val();cnts=$("#x_ship_to_country").val();sta=$("#x_state").val();stas=$("#x_ship_to_					    state").val();st=stas;cn=cnts;if(stas===undefined || stas=="" || cnts===undefined || cnts=="") {st=sta;cn=cnt;}
					shipping_list=$("#shipping_list").val();
					$.post("'.$this->pg_name.'?action=checkout",{do:"update_tax",country:cn,state:st,shipping_list:shipping_list},function(re){$("#shop_cart").html(re);});});var t=setTimeout("					    checkstate()",2000);})';
			$page=Builder::includeScript($sc,$page);
		}
		elseif($this->all_settings['g_ship_type']==7) //country based ship
		{
			$sc='
			$(document).ready(
				function(){'
					.($this->pg_settings['g_check_country']!=''?'$("#country,#x_ship_to_country").val("'.$this->pg_settings['g_check_country'].'")':'').'
					$("#country,#x_ship_to_country").change(function(){
						cnt=$("#country").val();
						$.post("'.$this->pg_name.'?action=checkout",{do:"update_shipping",country:cnt},function(re){
							 $("#shop_cart").html(re);});});
			})';
			$page=Builder::includeScript($sc,$page);
		}
		elseif($this->all_settings['g_ship_type']==9)  //custom shipping list
		{
			$sc='
				$(document).ready(
					function(){'
						.($this->pg_settings['g_check_shipping_list']!=''?'$("#shipping_list").val("'.$this->pg_settings['g_check_shipping_list'].'")':'').'
						$("#shipping_list").change(function(){
							cnt=$(this).val();
							$.post("'.$this->pg_name.'?action=checkout",{do:"update_shipping",shipping_list:cnt},function(re){
							$("#shop_cart").html(re);});});
				})';
			$page=Builder::includeScript($sc,$page);
		}
		$page=Builder::buildLoggedInfo($page,$this->pg_id,$this->rel_path);

		$this->setState('checkout');
		return $page;
	}

	protected function get_userinput_fields()
	{
		$result=array();
		foreach($this->pg_settings['g_fields_array'] as $k=>$v)
			if($v['itype']=='userinput')
				$result[]=$k;

		return $result;
	}

	protected function get_price_offset($id,$itemprice)
	{
		$offset=0.00;

		if($this->basket->bas_options[$id]!=null)
			foreach($this->basket->bas_options[$id] as $v)
			{
				$off=Formatter::GFS($v,'[',']');
				if(strpos($off,' ')!==false)
					$off=Formatter::GFS($off,'',' ');
				if($off!=='')
				{
					if(strpos($off,'%')!==false)
						$offset=$offset+$itemprice*((float)str_replace('%','',$off)/100);
					else
					{
						if($this->pg_settings['g_decimal_sign']==',')
						  $off=str_replace(array($this->pg_settings['g_thousands_sep'],$this->pg_settings['g_decimal_sign']),
											array('','.'),$off);
						else
						  $off=str_replace($this->pg_settings['g_thousands_sep'],'',$off);

						if(strpos($off,'*')!==false)
							$offset=$offset+$itemprice*((float)str_replace('*','',$off)-1);
						else
							$offset=$offset+floatval($off);
					}
				}
			}

		return $offset;
	}

//mini cart
	protected function show_minicart($current_cat_id,$current_page_id,$src,$searchstring)
	{
		$itemcounter=0;
		$shop_shipping=$vat_total=$price_total=$shippingweight_total=0.00;

		if($src==='')
		{
			$page=$this->get_pagetemplate($this->pg_id.'.html',true);
			if(strpos($page,'<MINI_CART>')===false)
				$page=$this->get_pagetemplate($this->pg_settings['g_cid'].".html");
		}
		else
			$page=$src;

		$minicart=$minicart_src=Formatter::GFSAbi($page,'<MINI_CART>','</MINI_CART>');
		$minicart=str_replace(array('href="../rnd/%SHOP_','href="rnd/%SHOP_'),'href="%SHOP_',$minicart);

		$items_result='<ul style="line-height:1.3em;list-style-type:none;margin:0;padding:0;">';

		$items_string_full=Formatter::GFSAbi($minicart,'%ITEMS(',')%');
		if($items_string_full=='%ITEMS()%')
			$items_string_full=Formatter::GFSAbi($page,'%ITEMS(',')%');

		$items_string=$items_string_full;
		$items_string=str_replace('%ITEMS(</span></p>','%ITEMS(',$items_string);
		$items_string=str_replace('<p><span class="rvts8">)%',')%',$items_string);
		$items_string=str_replace('%ITEMS(</p>','%ITEMS(',$items_string);
		$items_string=str_replace('<p>)%',')%',$items_string);
		$items_string=Formatter::GFS($items_string,'%ITEMS(',')%');

		$scale='';
		if(strpos($items_string,'%SCALE[')!==false)
		{
			$scale=Formatter::GFSAbi($items_string,'%SCALE[',']%');
			$items_string=str_replace($scale,'%SCALE%',$items_string);
		}

		if($scale!='')
			$items_string=str_replace('%SCALE%',$scale,$items_string);

		if(count($this->basket->bas_itemid))
		{
			$products_array=array();
			$data_array=array();
			$cart_string='';
			$this->replace_cart_lines($items_result,$products_array,$data_array,$itemcounter,$price_total,$vat_total,$shop_shipping,$cart_string,
				'<li style="clear:both">'.$items_string.'</li>',$current_cat_id,$current_page_id,$searchstring,$shippingweight_total);

			$minicart='<div id="minicart">'.$minicart.'</div>';
		}
		else
		{
			$minicart='<div id="minicart">'.$this->pg_settings['lang_u']['cart empty'].'</div>';
			return ($src==='')?$minicart:str_replace($minicart_src,$minicart,$page);
		}

		if($items_string !== '')
			$minicart=str_replace($items_string_full,$items_result.'</ul>',$minicart);
		$shop_shipping=($price_total>0 || $this->g_ship_usefield)?$this->count_shipping($price_total,$itemcounter,$shop_shipping,$shippingweight_total):0;
		$minicart=$this->parse_totals($minicart,$price_total,$vat_total,$shop_shipping,count($this->basket->bas_itemid),$itemcounter,false,$products_array);//mini-cart

		$cleanuplink=$this->g_abs_path.$this->pg_name.'?cleanup='.Linker::buildReturnURL();
		if(strpos($minicart,'%SHOP_CLEANUP_BUTTON%"')!==false)
			$minicart=str_replace('%SHOP_CLEANUP_BUTTON%"','javascript:void(0);" onclick="remove_item_ajax(\''.$cleanuplink.'\')" ',$minicart);
		$minicart=preg_replace('/%SHOP_CLEANUP_BUTTON%+[^"]+/i','javascript:void(0);" onclick="remove_item_ajax(\''.$cleanuplink.'\')',$minicart);

		$result=($src==='')?$minicart:str_replace($minicart_src,$minicart,$page);

		return $result;
	}

	protected function replace_cart_lines(&$result,&$products_array,&$data_array,&$itemcounter,&$price_total,&$vat_total,&$shop_shipping,&$cart_string,
		$line,$current_cat_id,$current_page_id,$searchstring,&$shippingweight_total)
	{
		global $db;

		if(count($this->basket->bas_itemid))
		{
			$bundle_primary_line=$bundle_description=$items_arr_ids='';
			$bundle_price=$this_bundle_count=$bundle_vatval=$bundle_linetotal=$bundle_shipping=0;
			$quantinty_btn=Formatter::GFSAbi($line,'<QUANTITY>','</QUANTITY>');
			foreach($this->basket->bas_itemid as $k=>$basketid)
			{
				$data=$db->query_first('
					SELECT *
					FROM '.$db->pre.$this->g_datapre.'data
					WHERE pid='.intval($basketid));

				$is_bundle=$this->basket->bas_bundle_primaryId[$k]!=null&&$this->basket->bas_bundle_discount[$k]!=null&&$this->basket->bas_bundle_id[$k]!=null
				&&$this->basket->bas_bundle_name[$k]!=null&&$this->basket->bas_bundle_count[$k]!=null;
				
				$pid=$this->basket->bas_itemid[$k];
				if($is_bundle)
				{
					$bundle_id=$this->basket->bas_bundle_id[$k];
					$bundle_discount=$this->basket->bas_bundle_discount[$k];
					$bundle_primary=$this->basket->bas_bundle_primaryId[$k];
					$bundle_name=$this->basket->bas_bundle_name[$k];
					$bundle_count=$this->basket->bas_bundle_count[$k];
					$this_bundle_count++;
					$is_primary=$bundle_primary==$pid;
					$bundle_description.=$data['name'].($this_bundle_count==$bundle_count?'':' + ');
					$itemline=!$is_primary?$quantinty_btn:$line;

					if($is_primary)
					{
						$data['name']=$bundle_name;
						$itemline=str_replace(array('%code%','%category%','%parent_category%'),'',$itemline);
						if($image_pos=strpos($itemline,'%SCALE[')!==false)
						{
							$m=Formatter::GFSAbi($itemline,'%SCALE[',']%');
							$params_a=explode(',',Formatter::GFS($itemline,'%SCALE[',']%'));
							$cp=count($params_a);
							if($cp>2)
							{
								$h=($params_a[2]=='')?0:intval($params_a[2]);
								$w=($params_a[1]=='')?0:intval($params_a[1]);
								if($h>0&&$w>0&&$bundle_count>1)
								{
									$child_images_str='';
									$ch_w=round($w/2.5);
									$ch_h=round($h/2.5);
									for($j=2;$j<=$bundle_count;$j++)
										$child_images_str.='%CHILD_SCALE'.$j.'[%ch_image1%,'.$ch_w.','.$ch_h.']%';
									if($child_images_str!='')
										$itemline=str_replace($m,$m.$child_images_str,$itemline);
								}
							}
						}
					}
					else
					{
						$val_ch=$data['image1'];
						if($this->all_settings['watermark'])
							$val_ch=$this->rel_path.'documents/utils.php?w='.Crypt::encrypt(($this->rel_path==''?'../':'').$val_ch.'|'.$this->get_setting('watermark_position')).'&i='.basename($val_ch);
						if(strpos($bundle_primary_line,'%CHILD_SCALE'.$this_bundle_count.'[')!==false)
						{
							$bundle_primary_line=str_replace('%CHILD_SCALE'.$this_bundle_count.'[','%SCALE[',$bundle_primary_line);
							Images::parseScale(array(),$bundle_primary_line,'ch_image1',false,true,$val_ch);
						}
					}
				}
				else
				{
					$bundle_primary_line=$bundle_description=$items_arr_ids='';
					$bundle_price=$this_bundle_count=$bundle_vatval=$bundle_linetotal=$bundle_shipping=0;
					$itemline=$line;
				}

				$pr=$this->get_price_value($data);
				if($this->pg_settings['g_tax_handling'])
					$itemvat=$this->count_vat(1);
				else
					$itemvat=($this->pg_settings['g_vatfield'] != '')?(floatval($data[$this->pg_settings['g_vatfield']])):(0.00);
				$itemshipping=($this->g_ship_usefield)?(floatval($data['shipping'])):(0.00);

				$itemline=str_replace('%QUANTITY%',$this->basket->bas_amount[$k],$itemline);

				$itemline=$this->replaceFieldsFinal(true,$data,$itemline,false,$this->basket->bas_itemid[$k],
					$this->basket->bas_options[$k],$this->basket->bas_userdata[$k],false);
				$btn_url=$this->g_abs_path.$this->pg_name.'?action=remove&amp;iid='.$this->basket->bas_itemid[$k].'&amp;category='.$current_cat_id.'&amp;page='.$current_page_id.Linker::buildReturnURL();
				if($searchstring != '')
					$btn_url=$btn_url.'&amp;q='.$searchstring;

				if($this->basket->bas_options[$k]!=null)
					foreach($this->basket->bas_options[$k] as $subtype_id=>$subtype_val)
						if($subtype_val!='')
							$btn_url.='&amp;subtype'.$subtype_id.'='.$subtype_val;
				if($this->basket->bas_userdata[$k] != '')
					$btn_url.='&amp;userdata='.$this->basket->bas_userdata[$k];

				// delete button
				$itemline=ListerFunctions::parse_button('SHOP_DELETE_BUTTON',$itemline,$btn_url,$is_bundle,$is_bundle?$is_primary:false,$is_bundle?$bundle_id:'');
				// quantity form
				$btn_url=$this->g_abs_path.$this->pg_name;

				$btn_string=Formatter::GFS($itemline,'<QUANTITY>','</QUANTITY>');


				$btn_parsed='<form id="quacart_'.$pid.($is_bundle?'_'.$bundle_id:'').'" name="quacart_'.$pid.($is_bundle?'_'.$bundle_id:'').'" method="get" action="'.$btn_url.'" onsubmit="return validate_qua(\'quacart_'.$pid.($is_bundle?'_'.$bundle_id:'').'\',false)" '.($is_bundle&&!$is_primary?'style="display:none"':'').'>'
					.$btn_string;
				$btn_parsed.='<input type="hidden" name="action" value="update">
					<input type="hidden" name="iid" value="'.$pid.'">';
				if($this->basket->bas_options[$k]!=null)
					foreach($this->basket->bas_options[$k] as $subtype_id=>$subtype_val)
						$btn_parsed.='<input type="hidden" name="subtype'.$subtype_id.'" value="'.$subtype_val.'">';

				$btn_parsed.='<input type="hidden" name="category" value="'.$this->basket->bas_catid[$k].'">
					<input type="hidden" name="page" value="'.$current_page_id.'">
					<input class="stock" type="hidden" name="stock" value="'.$data['stock'].'">
					<input type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">
					<input type="hidden" name="userdata" value="'.$this->basket->bas_userdata[$k].'">';
				if($is_bundle)
				{
					$bundle_info=array('bundle_id'=>$bundle_id,'bundle_primaryItem'=>$bundle_primary,'bundle_name'=>$bundle_name,'bundle_discount'=>$bundle_discount);
					$btn_parsed.='<input type="hidden" class="bundle_id'.$bundle_id.'" value="'.$pid.'" data-info=\''.htmlspecialchars(json_encode($bundle_info), ENT_QUOTES, 'UTF-8').'\'>';
				}
				$btn_parsed.='</FORM>';
				$btn_parsed=str_replace(array('document.%QFORMNAME%.submit();','%FORMNAME%','%QFORMNAME%'),
					array('if(validate_qua(\'quacart_'.$pid.($is_bundle?'_'.$bundle_id:'').'\',false)) update_basket(\'quacart_'.$pid.($is_bundle?'_'.$bundle_id:'').'\''.($is_bundle?','.$bundle_id:'').')','quacart_'.$pid.($is_bundle?'_'.$bundle_id:''),'quacart_'.$pid.($is_bundle?'_'.$bundle_id:'')),$btn_parsed);

				$itemline=str_replace($btn_string,$btn_parsed,$itemline);

				$itemprice=floatval($pr);
				$price_offset=$this->get_price_offset($k,$itemprice);
				$bundle_offset=0;
				if($is_bundle)
				{
					$off=Formatter::GFS($bundle_discount,'[',']');
					if(strpos($off,'%')!==false)
						$bundle_offset=$itemprice*((float)str_replace('%','',$off)/100);
				}
				$itemprice=$itemprice+$price_offset+$bundle_offset;

				$products_array[]=array('ol_orderid'=>'','ol_pid'=>$this->basket->bas_itemid[$k],
				'ol_amount'=>$this->basket->bas_amount[$k],
				'ol_price'=>$this->nrf2($itemprice,true),
				'ol_vat'=>$itemvat,'ol_shipping'=>$itemshipping,
				'ol_userdata'=>$this->basket->bas_userdata[$k]);

				if($this->basket->bas_options[$k]!=null)
					foreach($this->basket->bas_options[$k] as $subtype_id=>$subtype_val)
						$products_array['ol_option'.$subtype_id]=$subtype_val;

				$rep=$this->build_permalink($data,false,'',true);
				$itemline=str_replace(array('%SHOP_DETAIL%','%shorturl%','%PERMALINK%'),array($rep,urlencode($rep),$rep),$itemline);

				$itemcounter+=$this->basket->bas_amount[$k];
				$price_total+=($itemprice*$this->basket->bas_amount[$k]);
				$shippingweight_total+=$this->get_shippingweight($data)*$this->basket->bas_amount[$k];
				//vat
				$vat_val=0;
				if($itemvat>0)
				{
					if($this->pg_settings['g_excmode'])
					{
						if($this->pg_settings['g_tax_handling'])
							$vat_val=round(($this->count_vat($itemprice*$this->basket->bas_amount[$k])),$this->pg_settings['g_price_decimals']);
						else
							$vat_val=round((($itemprice*$this->basket->bas_amount[$k])*($itemvat/100)),$this->pg_settings['g_price_decimals']);
					}
					else
						$vat_val=(($itemprice*$this->basket->bas_amount[$k])*($itemvat)) / ($itemvat+100);
					$vat_total+=$vat_val;
				}
				$data_array[]=$this->data_array_entry('','','','','','',$this->basket->bas_amount[$k],
						  $itemprice,'',$itemvat,$vat_val,'',$this->basket->bas_options[$k]);
				if($is_bundle)
				{
					$bundle_primary_line=$is_primary?$itemline:$bundle_primary_line.$itemline;
					$bundle_price+=$this->nrf2($itemprice);
					$bundle_linetotal+=$this->basket->bas_amount[$k]*$itemprice;
					$bundle_shipping+=$itemshipping*$this->basket->bas_amount[$k];
					$bundle_vatval+=$vat_val;
					$items_arr_ids.='&amp;iid_arr[]='.$this->basket->bas_itemid[$k];
					if($bundle_count==$this_bundle_count)
					{
						$del_btn_url=$this->g_abs_path.$this->pg_name.'?action=remove'.$items_arr_ids.'&amp;category='.$current_cat_id.'&amp;page='.$current_page_id.Linker::buildReturnURL();
						$bundle_primary_line=str_replace(array('%SHOP_CARTPRICE%','%CARTPRICE%','%SERIAL%',
						'%LINEVATTOTAL%','%LINETOTAL%','%SHIPPING%','%REMOVE_URL%','%bundle_description%','%ID%'),
						array($bundle_price,$bundle_price,'',
						$this->nrf($bundle_vatval), $this->nrf($bundle_linetotal), $bundle_shipping, $del_btn_url, $bundle_description, ''),$bundle_primary_line);

						$result.=$bundle_primary_line;
						$bundle_primary_line=$bundle_description=$items_arr_ids='';
						$bundle_price=$this_bundle_count=$bundle_vatval=$bundle_linetotal=$bundle_shipping=0;
					}
				}
				else
				{
					$del_btn_url=$this->g_abs_path.$this->pg_name.'?action=remove&amp;iid='.$this->basket->bas_itemid[$k].'&amp;category='.$current_cat_id.'&amp;page='.$current_page_id.Linker::buildReturnURL();
					$itemline=str_replace(array('%SHOP_CARTPRICE%','%CARTPRICE%','%SERIAL%','%LINEVATTOTAL%','%bundle_description%',
						'%LINETOTAL%','%SHIPPING%','%ID%','%REMOVE_URL%'),
					array(
						$this->nrf($itemprice),$this->nrf($itemprice),'',$this->nrf($vat_val),'',
						$this->nrf($this->basket->bas_amount[$k]*$itemprice),$itemshipping*$this->basket->bas_amount[$k], $this->basket->bas_itemid[$k],$del_btn_url),$itemline);
					$result.=$itemline;
				}

				if($itemshipping!=0)
					$shop_shipping=$shop_shipping+($this->basket->bas_amount[$k]*$itemshipping);

				$cart_string=$cart_string.$this->basket->bas_catid[$k].','.$this->basket->bas_itemid[$k].','.$this->basket->bas_amount[$k].'|';
			}
		}
		else
			$result='<div class="rvps1">'.$this->pg_settings['lang_u']['cart empty'].'</div>';

	}


	protected function show_cart($cart_only,$current_cat_id,$current_page_id,$action,$searchstring)
	{
		$page=$this->get_pagetemplate(($this->pg_id+4).".html");
		if(!$cart_only)
		{
			$page=Builder::buildLoggedInfo($page,$this->pg_id,$this->rel_path);
			$this->mode=MODE_CART;
		}

		$category_section2=Formatter::GFSAbi($page,'<CATEGORIES_BODY>','</CATEGORIES_BODY>');
		if($category_section2!='')
			$page=str_replace($category_section2,$this->parse_cat_list($category_section2),$page);

		$page=$this->parse_minicart($page,0,0);

		$page_body=Formatter::GFS($page,'<LISTER_BODY>','</LISTER_BODY>');

		if($cart_only)
		{
			$bound=strpos($page,'<CART>')!==false?'CART':'LISTER';
			$page_head=Formatter::GFS($page,'<'.$bound.'>','<LISTER_BODY>');
			$page_foot=Formatter::GFS($page,'</LISTER_BODY>','</'.$bound.'>');
		}
		else
		{
			$page_head=Formatter::GFS($page,'','<LISTER_BODY>');
			$page_foot=Formatter::GFS($page,'</LISTER_BODY>','');
		}

		$itemcounter=$shop_shipping=0;
		$price_total=$vat_total=$shippingweight_total=0.00;
		$result=$cart_string='';
		$data_array=$products_array=array();

		$this->replace_cart_lines($result,$products_array,$data_array,$itemcounter,$price_total,$vat_total,$shop_shipping,$cart_string ,
			$page_body,$current_cat_id,$current_page_id,$searchstring,$shippingweight_total);

		$checkout_link=$this->g_abs_path.$this->pg_name.'?action=checkout"';

		if($itemcounter==0)
			$checkout_link='';
		else
			$shop_shipping=$this->count_shipping($price_total,$itemcounter,$shop_shipping,$shippingweight_total);

		$page_foot=$this->couponsModule->coupon_macro($page_foot,$price_total,0);
		$page_head=$this->couponsModule->coupon_macro($page_head,$price_total,0);
		$page_foot=$this->parse_totals($page_foot,$price_total,$vat_total,$shop_shipping,count($this->basket->bas_itemid),$itemcounter,false,$products_array);//show cart
		$page_head=$this->parse_totals($page_head,$price_total,$vat_total,$shop_shipping,count($this->basket->bas_itemid),$itemcounter,false,$products_array);

		if(strpos($page_foot,'<VAT>')!==false)
			$this->parse_vatdetail($page_foot,$shop_shipping,$data_array);

		if($action=='show_final' || $itemcounter==0)
		{
			$temp=Formatter::GFSAbi($page_foot,'<a href="<SHOP_URL>','</a>');
			$temp=Formatter::GFSAbi($page_foot,'<a href="%SHOP_CHECKOUT%"','</a>');
			$page_foot=str_replace($temp,'',$page_foot);
			$temp=Formatter::GFSAbi($page_foot,'<a href="'.$this->pg_name.'?action=pay','</a>');
			$temp2=Formatter::GFSAbi($page_foot,'<a href="'.$this->pg_name.'?action=checkout','</a>');
			$ra=array($temp,$temp2);

			if(strpos($page_foot,'class="'.$this->f->art_prefix.'button"')!==false)
			{
				$ra[]=Formatter::GFSAbi($page_foot,'<a class="'.$this->f->art_prefix.'button" href="'.$this->pg_name.'?action=pay','</a>');
				$ra[]=Formatter::GFSAbi($page_foot,'<a class="'.$this->f->art_prefix.'button" href="'.$this->pg_name.'?action=checkout','</a>');
			}
			if($this->f->buttonhtml!='')
			{
				$ra[]=str_replace('%BUTTON%',$temp2,$this->f->buttonhtml);
				$ra[]=str_replace('%BUTTON%',$temp,$this->f->buttonhtml);
			}
			$page_foot=str_replace(array_reverse($ra),'',$page_foot);
		}
		else
			$page_foot=str_replace('%SHOP_CHECKOUT%',$checkout_link,$page_foot);

		$page_foot=str_replace('%SHOP_CART_STRING%',$cart_string,$page_foot);
		$result=$page_head.$result.$page_foot;

		if(!$cart_only)
			$result=str_replace(array('<CART>','</CART>'),array('<div id="shop_cart">','</div>'),$result);

		$result=str_replace(array('%SHOP_CARTCURRENCY%','%CURRENCY%'),$this->pg_settings['g_currency'],$result);
		$this->parse_page_id($result);

		$cleanuplink=$this->g_abs_path.$this->pg_name.'?cleanup='.Linker::buildReturnURL();

		if(strpos($result,'%SHOP_CLEANUP_BUTTON%"')!==false)
			$result=str_replace('%SHOP_CLEANUP_BUTTON%"','javascript:void(0);" onclick="remove_item_ajax(\''.$cleanuplink.'\')" ',$result);
		$result=preg_replace('/%SHOP_CLEANUP_BUTTON%+[^"]+/i','javascript:void(0);" onclick="remove_item_ajax(\''.$cleanuplink.'\')',$result);

		if($this->global_pagescr !== '')
			$result=str_replace('<!--scripts-->','<!--scripts-->'.$this->global_pagescr,$result);
		$result=$this->build_category_lists(0,$result);
		$result=str_replace($this->int_tags,'',$result);
		$result=Builder::buildLoggedInfo($result,$this->pg_id,$this->rel_path);
		$this->ReplaceMacros($result);
		return $result;
	}

	protected function process_order()
	{
		global $db;

		$formfields=$this->get_fields();
		$direct=false;
		if(isset($formfields['ec_PaymentMethod']))
			$payment_method=strtolower($formfields['ec_PaymentMethod']);
		else
		{
			$payment_method='paypal';
			$direct=true;
		}
		$abs_docs=str_replace(str_replace('../','',$this->script_path),'',$this->full_script_path);

		if(isset($_POST[$this->pg_settings['g_check_email']]))
		{
			if($this->check_fields($formfields,false))
				return;
		}
		elseif(!$direct)
		{
			echo 'default email field is not correctly set!';
			exit;
		}

		$data=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->pg_pre.'settings
			WHERE skey="id"');
		$safe_transaction_id=$data['sval'];
		$safe_transaction_id++;
		$data=array();
		$data['sval']=$safe_transaction_id;
		$db->query_update($this->pg_id.'_settings',$data,'skey="id"');

		$date_time=date('Y-m-d H:i:s');

		$items_lines_bw=$cartstring='';
		$vat_total=$price_total=0;
		$ctrl_str=(isset($this->pg_settings['g_checkout_str'][$payment_method]))?$this->pg_settings['g_checkout_str'][$payment_method]:'';
		$pm=strtolower($payment_method);
		$products_array=$bundle_ordelines=array();
		$postdata=$this->parse_shop_cart($pm,$ctrl_str,$safe_transaction_id,$products_array,$items_lines_bw,
				  $formfields,$vat_total,$price_total,$cartstring,$bundle_ordelines);
		$cartstring=str_replace(Formatter::GFSAbi($cartstring,'<COUPON_AREA>','</COUPON_AREA>'),'',$cartstring);

		if(($price_total<=0 && $this->get_setting('allow_null_cart')=='0') ||
				($this->get_setting('min_order')>0 && ($price_total<$this->get_setting('min_order'))))
		{
			Linker::redirect($this->full_script_path,false);
			exit;
		}
//writing order data to pending orders
		$this->session_transaction_id=$safe_transaction_id;

		$data=array();
		$data['orderid']=$this->session_transaction_id;
		$data['pdate']=$date_time;
		$data['form_fields']=$this->_build_fields($formfields,'|',false);
		if($this->pg_settings['g_excmode'] && $this->pg_settings['g_tax_handling'])
			$data['vat1']=$vat_total;
		$data['coupon']=$this->couponsModule->get_session_coupon();
		$uid=$this->user->mGetUserID($db);
		if($uid>0)
			$data['userid']=$uid;

		$db->query_insert($this->pg_id.'_pending_orders',$data);
		foreach($products_array as $v)
			$db->query_insert($this->pg_id.'_orderlines',$v);
		foreach($bundle_ordelines as $v)
			$db->query_insert($this->pg_id.'_bundles_orderlines',$v);
		$ip=Detector::getIP();
		$date=Date::tzoneNow();

		$data['form_fields']=$this->_build_fields($formfields,'<br>',false);
		History::add($this->pg_id,$this->pg_settings['g_data'].'_pending_orders',$this->session_transaction_id,$uid,$data);

		$bwmess=$this->get_setting('bw_notif_message');
		$bwmess=strpos($bwmess,'<br')===false?str_replace(array("\r\n","\n"),'<br />',$bwmess):$bwmess;
		$customer_msg=$this->parse_returnpage($payment_method,$this->session_transaction_id,'',true,false,$bwmess);
		if(!isset($this->pg_settings['g_checkout_callback_on'][$pm])) //bankwire
		{
			$parsed_mess=$this->pg_settings['order_notification_body'];
			$mail_subject=$this->pg_settings['order_notification_subject'];
			$parsed_fields=$this->_build_fields($formfields,'<br>',true);

			//send e-mail to us
			$_send_from=$this->get_shop_from();

			if($this->pg_settings['send_admin_notification'])
			{
				foreach($formfields as $k=>$v)
				{
					$parsed_mess=str_replace('%'.$k.'%',$v,$parsed_mess);
					$mail_subject=str_replace('%'.$k.'%',$v,$mail_subject);
				}
				$abs_url=str_replace(" ","%20",$this->full_script_path.'?action=pending');
				$mail_mess=str_replace(
					array('%SHOP_NAME%','%TYPE%','%PAYMENT_TYPE%','%ORDERS_LINK%',
							'%SHOP_ORDER_ID%','%ORDER_ID%',
							'%FORM_DATA%','%SHOP_CART%','%SHOP_CART_LINES%','%SHOP_CART_LINES_USER%','%SHOP_IPUSER%','%ip%',
							'%whois%','%ORDER_DATE%','%date%'),
					array($this->pg_settings['g_shop_name'],$payment_method,$payment_method,$abs_url,
							$this->session_transaction_id,$this->session_transaction_id,
							$parsed_fields,$cartstring,$items_lines_bw,$customer_msg,$ip,$ip,
						   'http://en.utrace.de/?query='.$ip,$date,$date),
							$parsed_mess);

				$mail_subject=str_replace(
					array('%SHOP_NAME%','%TYPE%','%PAYMENT_TYPE%','%ORDERS_LINK%','%SHOP_ORDER_ID%','%ORDER_ID%'),
					array($this->pg_settings['g_shop_name'],$payment_method,$payment_method,$abs_url,$this->session_transaction_id,$this->session_transaction_id),
					$mail_subject);
				$mail_mess=$this->couponsModule->coupon_macro_return($mail_mess);
	 			$mail_mess='<html><head><link type="text/css" href="'.$abs_docs.'documents/textstyles_nf.css" rel="stylesheet"></head><body><div id="xm1"><div id="xm2">'.$mail_mess.'</div></div></body></html>';

				$result=$this->send_mail($mail_mess,$mail_subject,$_send_from,$this->pg_settings['g_send_to']);
			}

			if($this->get_setting('auto_confirm')=='1')
			{
				$_REQUEST['em']='1';
				$this->move_confirm($this->session_transaction_id,false,false);
			}
			elseif($this->pg_settings['set_bankwire_email'])  //send e-mail to customer
			{
				$abs_url=str_replace(" ","%20",$this->full_script_path.'?action=order&id='.$this->session_transaction_id.'_'.crypt($this->session_transaction_id,'jhjshdjhj98'));
				$to=$formfields[$this->pg_settings['g_check_email']];



				$result=$this->send_mail($customer_msg,$this->get_setting('bw_notif_subject'),$_send_from,$to);
			}

			$this->setState('process_order');
			Linker::redirect($this->full_script_path.'?action=return_ok&payment='.$payment_method,false);
		}
		else
		{
			$form_data=$this->paymentModule->collectFormData($postdata);

			if($this->pg_settings['send_admin_notification'])
			{
				$parsed_mess=$this->pg_settings['order_notification_body'];
				$parsed_fields=$this->_build_fields($formfields,'<br>',true);

				foreach($formfields as $key => $value)
				{
					if($key=='image1'&&strpos($parsed_mess,'%image1_path%')!==false)
					{
						$full_image=Linker::buildSelfURL('',true,$this->rel_path).str_replace('../','',$value);
						$parsed_mess=str_replace(array('%'.$key.'%','%image1_path%'),array($value,$full_image),$parsed_mess);
					}
					else
						$parsed_mess=str_replace('%'.$key.'%',$value,$parsed_mess);
				}
				$abs_url=$this->full_script_path.'?action=pending';
				$abs_url=str_replace(" ","%20",$abs_url);
//send e-mail to us
				$mail_mess=str_replace(
					array('%SHOP_NAME%','%TYPE%','%PAYMENT_TYPE%','%ORDERS_LINK%',
							'%SHOP_ORDER_ID%','%ORDER_ID%',
							'%FORM_DATA%','%SHOP_CART%','%SHOP_CART_LINES%','SHOP_CART_LINES_USER','%SHOP_IPUSER%','%ip%','%whois%',
						   '%ORDER_DATE%','%date%'),
					array($this->pg_settings['g_shop_name'],$payment_method,$payment_method,$abs_url,
							$this->session_transaction_id,$this->session_transaction_id,
							$parsed_fields,$cartstring,$items_lines_bw,$customer_msg,$ip,$ip,$ip,'http://en.utrace.de/?query='.$ip,
							$date,$date),$parsed_mess);

				$mail_mess=$this->couponsModule->coupon_macro_return($mail_mess);
				$_send_from=$this->get_shop_from();
				$mail_subject=str_replace(
							array('%SHOP_NAME%','%TYPE%','%PAYMENT_TYPE%','%ORDERS_LINK%','%SHOP_ORDER_ID%','%ORDER_ID%'),
							array($this->pg_settings['g_shop_name'],$payment_method,$payment_method,$abs_url,$this->session_transaction_id,$this->session_transaction_id),
							$this->pg_settings['order_notification_subject']);
				$mail_mess='<html><head><link type="text/css" href="'.$abs_docs.'documents/textstyles_nf.css" rel="stylesheet"></head><body><div id="xm1"><div id="xm2">'.$mail_mess.'</div></div></body></html>';

				$this->send_mail($mail_mess,$mail_subject,$_send_from,$this->pg_settings['g_send_to']);
			}

			if($pm!='ogone')
			{
				$postdata=str_replace(' ','%20',$postdata);
				$postdata=$postdata.$form_data;
			}
			$this->setState('process_order');
			Linker::redirect($postdata,false);
		}
	}

	protected function return_file()
	{
		$id=0;
		$pa_id=$trid='';
		if(isset($_REQUEST['id']))
			$id=intval($_REQUEST['id']);
		if(isset($_REQUEST['trid']))
			$trid=intval($_REQUEST['trid']);
		if(isset($_REQUEST['pa_id']))
			$pa_id=Formatter::stripTags($_REQUEST['pa_id']);

		if($this->session_transaction_id==0)
		{
			$sh=sha1($trid.'_'.$this->pg_id);
			if(isset($_COOKIE[$sh]) && $_COOKIE[$sh]==sha1($trid))
				$this->session_transaction_id=$trid;
		}

		if(($pa_id=='')||(!$this->pg_settings['g_callback_mail']))
			$order_string=$this->return_order($this->session_transaction_id,false);
		else
		{
			$order_string=$this->return_order($trid,false);
			if(strpos($order_string,'|payer_id='.$pa_id.'|') !== false)
				$this->session_transaction_id=$trid;
			elseif(strpos($order_string,'payment_status=moved+email'.$pa_id.'|') !== false)
				$this->session_transaction_id=$trid;
		}

		if($this->session_transaction_id>0)
		{
			if(($id>0)&&($order_string !== ''))
			{
				$data=$this->get_pending_order($this->session_transaction_id);
				$item=false;
				foreach($data as $v)
					if($v['ol_pid']==$id)
					{
						$item=$v;
						break;
					}
				if($item!==false)
				{
					$fname=$item['download'];
					if((strpos(strtolower($fname),'.html')!==false)||(strpos(strtolower($fname),'.php')!==false))
					{
						header("Location: ".$fname);
						exit;
					}
					elseif(strpos($fname,'/')!==false)
					{
						$audioname_encoded=$fname;
						$fname=basename($fname);
					}
					if(isset($audioname_encoded))
						$this->process_file($audioname_encoded,$fname);
					else
						echo 'file is missing ['.$id.'], '.$this->lang_l('contact administrator');
				}
			}
			else
				echo 'error order['.$this->session_transaction_id.'], '.$this->lang_l('contact administrator');
		}
		else
			echo 'error order['.$trid.'], '.$this->lang_l('contact administrator');
	}

	public function is_order_ok($payment)
	{
		global $db;
		$tr_id=$this->session_transaction_id;
		$fx_id=(isset($this->pg_settings['g_callback_str'][$payment]['SHOP_RET_ORDERID']))?
			$this->pg_settings['g_callback_str'][$payment]['SHOP_RET_ORDERID']:
			'custom';
		$cb_param=$fx_id.'='.$tr_id.'_';
		$data=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->pg_pre.'orders
			WHERE orderid='.intval($tr_id));
		$result=(is_array($data) && (strpos($data['orderline'],$cb_param)!==false))?1:0;
		if($result==0 && $payment='paypal')  //check for pending orders
			$result=$this->paymentModule->checkPendingOrder($cb_param);
		return $result;
	}

	protected function return_ok($pt)
	{
		$result='';

		if(isset($_REQUEST['payment']))
			$payment_type=Formatter::stripTags($_REQUEST['payment']);
		elseif($pt!=='')
			$payment_type=$pt;
		else
		{
			$data=$this->get_pending_order($this->session_transaction_id,false);
			$payment_type=strtolower(Formatter::GFS($data['form_fields'],'ec_PaymentMethod=','|'));
			if($payment_type=='')
				$payment_type='paypal';
		}

		$abs_url=$this->full_script_path.'?action=return&payment='.$payment_type;
		$sto_array=(strpos($this->pg_settings['g_send_to'],';')!==false)?explode(';',$this->pg_settings['g_send_to']):array($this->pg_settings['g_send_to']);

		if(isset($this->pg_settings['g_checkout_callback_on'][$payment_type]))
		{
			if($this->pg_settings['g_checkout_callback_on'][$payment_type]=='TRUE' && $this->paymentModule!==false){
				$result=$this->paymentModule->callbackOrderValid($abs_url,$sto_array);
				if($result=='')
					$result=$this->parse_returnpage($payment_type,$this->session_transaction_id,'',false,false);
			}
			else
			{
				$data=$this->get_pending_order($this->session_transaction_id,false);
				$parsed_fields=str_replace('|','<br>',$data['form_fields']);
				$abs_url=$this->full_script_path.'?action=pending';$abs_url=str_replace(" ","%20",$abs_url);
//send e-mail to us
				$mail_mess=$this->pg_settings['order_notification_body'];
				$ip=Detector::getIP();
				$mail_mess=str_replace(array('%SHOP_NAME%','%PAYMENT_TYPE%','%ORDERS_LINK%','%SHOP_ORDER_ID%','%ORDER_ID%','%FORM_DATA%','%SHOP_CART%','%SHOP_IPUSER%','%ORDER_DATE%'),
					array($this->pg_settings['g_shop_name'],$payment_type,$abs_url,$this->session_transaction_id,$this->session_transaction_id,$parsed_fields,'',$ip,Date::tzoneNow()),$mail_mess);
				$mail_subject=str_replace(
								array('%SHOP_NAME%','%TYPE%','%PAYMENT_TYPE%','%ORDERS_LINK%','%SHOP_ORDER_ID%','%ORDER_ID%'),
								array($this->pg_settings['g_shop_name'],$payment_type,$payment_type,$abs_url,$this->session_transaction_id,$this->session_transaction_id),
								$this->pg_settings['order_notification_subject']);

				$_send_from=$this->get_shop_from();
				$result=$this->send_mail($mail_mess,$mail_subject,$_send_from,$this->pg_settings['g_send_to']);
				$result=$this->parse_returnpage($payment_type,$this->session_transaction_id,'',false,false);
			}
		}
		else
			$result=$this->parse_returnpage($payment_type,$this->session_transaction_id,'',false,$this->get_setting('auto_confirm')=='1');

		if(strpos($result,'remove_item_ajax')!==false){
			$js='';
			$this->shop_js_functions($result,$js);
			$result.='<script>'.$js.'</script>';
		}

		$this->cleanup_cart();

		print $result;
	}

	public function get_pending_order($id,$include_product_data=true)
	{
		global $db;

		if($include_product_data)
		{
			$data=$db->fetch_all_array('
				SELECT *
				FROM '.$db->pre.$this->pg_pre.'pending_orders AS po
				LEFT OUTER JOIN '.$db->pre.$this->pg_pre.'orderlines AS ol ON po.orderid=ol.ol_orderid
				LEFT JOIN '.$db->pre.$this->g_datapre.'data AS dt ON ol.ol_pid=dt.pid
				WHERE po.orderid='.intval($id)
				.' ORDER BY ol.id ASC');
			$data_bundles=$db->fetch_all_array('
				SELECT ol_orderid, ol_pid, ol_bundle_id, ol_bundle_primary, ol_bundle_name, ol_bundle_name, ol_bundle_discount, ol_bundle_count
				FROM '.$db->pre.$this->pg_pre.'bundles_orderlines 
				WHERE ol_orderid='.intval($id)
				.' ORDER BY id ASC');
			if(count($data_bundles)>0)
			{
				foreach ($data as $key=>&$v){
					if(isset($data_bundles[$key]))
						$v += $data_bundles[$key];
				}
			}
		}
		else
			 $data=$db->query_first('
				SELECT *
				FROM '.$db->pre.$this->pg_pre.'pending_orders
				WHERE orderid = '.intval($id));

		return $data;
	}

	protected function replace_rel_paths($src)
	{
		$host=str_replace($this->pg_dir.$this->pg_name,'',$this->full_script_path);
		$rel=$this->pg_dir==''?'':'../';

		return str_replace(
						array('src="'.$rel,'href="'.$rel,'url('.$rel),
						array('src="'.$host,'href="'.$host,'url('.$host),
						$src);
	}

	protected function data_array_entry($audioname,$id,$itemname,$itemcode,$itemcategory,
		$item_subname,$item_count,$item_price,$record_line,$itemvat,$vat_amount,$serial,
		$item_options,$bundle_arr='')
	{
		$data=array();
		$data['audioname']=$audioname;
		$data['id']=$id;
		$data['name']=$itemname;
		$data['code']=$itemcode;
		$data['category']=$itemcategory;
		$data['sname']=$item_subname;
		$data['count']=$item_count;
		$data['amount']=$this->nrf($item_price);
		$data['fields']=$record_line;
		$data['vat']=$itemvat;
		$data['vat_amount']=$vat_amount;
		$data['serial']=$serial;
		if(is_array($item_options))
			foreach($item_options as $k=>$v)
				$data['option'.$k]=$v;
		if(is_array($bundle_arr))
			foreach($bundle_arr as $k=>$v)
				$data[$k]=$v;
		return $data;
	}

	public function parse_vatdetail(&$result,$shop_shipping,$data_array)
	{
		$shipping_vat=$this->all_settings['g_ship_vat'];
		if($shop_shipping>0 && $shipping_vat>0)
		{
			if($this->pg_settings['g_excmode'])
			{
				if($this->pg_settings['g_tax_handling'])
					$vat_amount=round(($this->count_vat($shop_shipping*$shipping_vat)),$this->pg_settings['g_price_decimals']);
				else
					$vat_amount=round((($shop_shipping)*($shipping_vat/100)),$this->pg_settings['g_price_decimals']);
			}
			else
				$vat_amount=(($shop_shipping)*($shipping_vat))/($shipping_vat+100);

			$data_array[]=$this->data_array_entry('','','','','','',1,$shop_shipping,'',
					  $shipping_vat,$vat_amount,'','');
		}

		$vat_line=Formatter::GFS($result,'<VAT>','</VAT>');
		$vat_lines='';
		$var_ar=array();
		foreach($data_array as $k=>$v)
		{
			if(!isset($var_ar[$v['vat']]))
				$var_ar[$v['vat']]=$v['vat_amount'];
			else
				$var_ar[$v['vat']]+=$v['vat_amount'];
		}
		foreach($var_ar as $k=>$v)
			$vat_lines.=str_replace(array('%VAT%','%VAT_AMOUNT%'),array($this->nrf($k),$this->nrf($v)),$vat_line).F_BR;
		$result=str_replace($vat_line,$vat_lines,$result);
	}

	public function parse_returnpage($pt,$order_id,$payer_id,$foremail,$bwconfirmed,$page='')//$page option to send email from settings
	{
		global $db;

		$coupon=$result=$avatar='';
		$itemcounter=$bcounter=$downloadsIncluded=0;
		$downloadsAllowed=$callback_on=$bwconfirmed ||
						(isset($this->pg_settings['g_checkout_callback_on'][$pt]))
							&&($this->pg_settings['g_checkout_callback_on'][$pt]=='TRUE');
		$products_array=array();

		$data=$this->get_pending_order($order_id);

		if(!is_array($data))
			return 'error order: ['.$order_id.'], '.$this->lang_l('contact administrator');
		else
		{
			if($downloadsAllowed && $pt=='2checkout')
				$downloadsAllowed=$this->paymentModule->downloadAllowed($data[0]);
			$coupon=$data[0]['coupon'];
			$invoiceNr=$data[0]['invoicenr'];
			$invStatus=$this->get_invoice_status($data[0]['invoice_status']);

			$userId=$this->lang_l('guest');
			if($data[0]['userid']!=NULL)
			{
				 $userId=$data[0]['userid'];
				 $avatar=user::getAvatar($userId,$db,$this->site_base);
			}

			if($invoiceNr==NULL)
				$invoiceNr='';

			$shop_shipping=$price_total=$vat_total=0;

			$id=1;
			$data_array=array();
			foreach($data as $k=>$v)
			{
				$item_id=$v['ol_pid'];
				$item_count=$v['ol_amount'];
				$serial=$v['ol_serial'];
				$item_price=str_replace(',','',$v['ol_price']);
				$item_subname=$this->optionsModule->selectedOptions($v);

				if($v['ol_userdata']!='')
					$item_subname.=' '.urldecode(str_replace('**',' ',$v['ol_userdata']));
				$itemvat=$v['ol_vat'];

				if($item_id != '1000000') //getting record values
				{
					$is_bundle=isset($v['ol_bundle_id'])&&$v['ol_bundle_id']!=0&&$v['ol_bundle_name']!=''&&$v['ol_bundle_primary']!=0&&$v['ol_bundle_discount']!=''&&$v['ol_bundle_count']!=0;
					if($is_bundle){
						$bundle_arr=array(
							'ol_bundle_id'=>$v['ol_bundle_id'],
							'ol_bundle_name'=>$v['ol_bundle_name'],
							'ol_bundle_primary'=>$v['ol_bundle_primary'],
							'ol_bundle_discount'=>$v['ol_bundle_discount'],
							'ol_bundle_count'=>$v['ol_bundle_count']
						);
					}
					$itemname=$v['name'];
					$itemcode=$v['code'];
					$parentid=-1;
					$itemcategory=$this->categoriesModule->get_category_name($v['cid'],$parentid);
					$itemcounter+=$item_count;
					$bcounter++;

					$price_total+=($item_price*$item_count);
					$vat_amount=0;
	 				if($itemvat > 0)
					{
						if($this->pg_settings['g_excmode'])
						{
							if($this->pg_settings['g_tax_handling'])
								$vat_amount=round(($this->count_vat($item_price*$item_count)),$this->pg_settings['g_price_decimals']);
							else
								$vat_amount=round((($item_price*$item_count)*($itemvat/100)),$this->pg_settings['g_price_decimals']);
						}
						else
							$vat_amount=(($item_price*$item_count)*($itemvat))/($itemvat+100);
						$vat_total+=$vat_amount;
					}

					if($downloadsAllowed && $v['download']!='')
						$downloadsIncluded=true;

					$pa=array(
						'ol_orderid'=>$order_id,
						'ol_pid'=>$item_id,
						'ol_amount'=>$item_count,
						'ol_price'=>$item_price,
						'ol_vat'=>$itemvat,
						'ol_shipping'=>'',
						'ol_userdata'=>'');

					$optionaA=array();
					foreach($this->optionsModule->options as $k=>$opt)
					{
						if(isset($v['ol_'.$opt]))
						{
							$optionaA[]=$v['ol_'.$opt];
							$pa['ol_'.$opt]=$v['ol_'.$opt];
						}
					}

					$products_array[]=$pa;

					$data_array[]=$this->data_array_entry($v['download'],$item_id,$itemname,$itemcode,$itemcategory,
							  $item_subname,$item_count,$item_price,$v,$itemvat,$vat_amount,$serial,
							  $optionaA,$is_bundle?$bundle_arr:'');
					$id++;
				}
				else
					$shop_shipping=$item_price;
			}
		}

		if($page!='')
			$return_data='<LISTER>'.$page.'</LISTER>';
		else
			$return_data=$this->get_pagetemplate(($this->pg_id+2).".html");

		$return_data=$this->couponsModule->coupon_macro_return($return_data,$coupon!=''?$coupon:null);

		if($downloadsIncluded && !$foremail)
			 setcookie(sha1($order_id.'_'.$this->pg_id),sha1($order_id),time()+$this->pg_settings['g_downloadexpire']*86400);

		if($foremail)  //needed for worldpay callback
		{
			if(strpos($return_data,'<style type="text/css">') !== false)
			{
				$return_data='<head>'.Formatter::GFSAbi($return_data,'<style type="text/css">','</style>')
						  .'</head><body>'.Formatter::GFSAbi($return_data,'<LISTER>','</LISTER>').'</body>';
				$return_data=$this->replace_rel_paths($return_data);
			}
			else
				$return_data=Formatter::GFSAbi($return_data,'<head>','</head>').
					'<body>'.Formatter::GFSAbi($return_data,'<LISTER>','</LISTER>').
					'</body>';

			$return_data=str_replace(Formatter::GFSAbi($return_data,'<!--menu_java-->','<!--/menu_java-->'),'',$return_data);
		}

		if(($pt=='worldpay')||($pt=='authorize.net'))
			 $return_data=$this->replace_rel_paths($return_data);
		$payment_section=$downloadsIncluded?Formatter::GFS($return_data,'<download>','</download>'):'';

		if($payment_section=='')
			 $payment_section=Formatter::GFS($return_data,'<'.$pt.'>','</'.$pt.'>');
		if(($payment_section=='')&&(strtolower($pt)=='bankwire'))
			 $payment_section=Formatter::GFS($return_data,'<BANKWIRE>','</BANKWIRE>');
		if($payment_section=='')
			 $payment_section=Formatter::GFS($return_data,'<default>','</default>');
		if($payment_section != '')
		{
			if($foremail)
			{
				$payment_section=str_replace(Formatter::GFSAbi($payment_section,'<header>','</header>'),'',$payment_section);
				$payment_section=str_replace(Formatter::GFSAbi($payment_section,'<footer>','</footer>'),'',$payment_section);
			}
			$return_data=str_replace(Formatter::GFS($return_data,'<LISTER>','</LISTER>'),$payment_section,$return_data);
		}
		elseif(strtolower($pt)!='bankwire')
			$return_data=str_replace(Formatter::GFS($return_data,'<BANKWIRE>','</BANKWIRE>'),'',$return_data);

		$return_data=str_replace(array('<SHOP_BODY>','</SHOP_BODY>'),array('<LISTER_BODY>','</LISTER_BODY>'),$return_data);
		$product_body=Formatter::GFS($return_data,'<LISTER_BODY>','</LISTER_BODY>');

		$bundle_primary_line=$bundle_description='';
		$this_bundle_count=$bundle_price=$bundle_vat=$bundle_vat_amount=0;
		$itemid_bw=0;
		foreach($data_array as $k=>$v)
		{
			$is_bundle=isset($v['ol_bundle_id'])&&$v['ol_bundle_id']!=0&&$v['ol_bundle_name']!=''&&$v['ol_bundle_primary']!=0&&$v['ol_bundle_discount']!=''&&$v['ol_bundle_count']!=0;
			if($is_bundle)
			{
				$bundle_id=$v['ol_bundle_id'];
				$bundle_discount=$v['ol_bundle_discount'];
				$bundle_primary=$v['ol_bundle_primary'];
				$bundle_name=$v['ol_bundle_name'];
				$bundle_count=$v['ol_bundle_count'];
				$this_bundle_count++;
				$is_primary=$bundle_primary==$v['id'];
				$bundle_description.=$v['name'].($this_bundle_count==$bundle_count?'':' + ');
				$p_line=$is_primary?$product_body:'';
				$itemid_bw=$is_primary?$itemid_bw+1:$itemid_bw;
			}
			else
			{
				$bundle_primary_line=$bundle_description='';
				$this_bundle_count=$bundle_price=$bundle_vat=$bundle_vat_amount=0;
				$p_line=$product_body;
				$itemid_bw++;
			}

			if($downloadsAllowed && $v['audioname']!='')
			{
				$dlink_caption=Formatter::GFS($p_line,'%SHOP_ITEM_DOWNLOAD_LINK(',')%');
				$dlink_string='%SHOP_ITEM_DOWNLOAD_LINK('.$dlink_caption.')%';
				$pa_id='';
				if($foremail)
					$pa_id='&amp;pa_id='.$payer_id;
				$d_url=$this->full_script_path.'?action=download&id='.$v['id'].'&trid='.$order_id.$pa_id;
				$item_string='<a class="rvts4" href="'.$d_url.'">'.$this->pg_settings['lang_u']['download'].'</a>';
				$p_line=str_replace($dlink_string,$item_string,$p_line);
				$p_line=str_replace('%SHOP_ITEM_DOWNLOAD_URL%',$d_url,$p_line);
			}
			else
			{
				$temp=Formatter::GFSAbi($p_line,'%SHOP_ITEM_DOWNLOAD_LINK(',')%');
				$p_line=str_replace($temp,$this->lang_l('download not allowed'),$p_line);
			}

			if($is_bundle)
			{
				$bundle_vat+=$this->nrf2($v['vat']);
				$bundle_vat_amount+=$this->nrf2($v['vat_amount']);
				$bundle_price+=$this->nrf2($this->float_val($v['amount'])*$v['count']);
				$bundle_primary_line=$is_primary?$p_line:$bundle_primary_line.$p_line;
				if($bundle_count==$this_bundle_count)
				{
					$bundle_primary_line=str_replace(array('%LINEPRICE%','%ITEM_VAT%','%ITEM_VAT_AMOUNT%','%SHOP_ORDER_ITEM_COUNT%','%SHOP_ORDER_ITEM_NAME%','%bundle_description%',
					'%LISTER_COUNTER%','%SHOP_COUNTER%'),
					array($bundle_price, $bundle_vat,$bundle_vat_amount,'',$bundle_name,$bundle_description,
					$itemid_bw,$itemid_bw),$bundle_primary_line);
					$bundle_primary_line=str_replace(array('%SHOP_ORDER_ITEM(P_Code)%','%SHOP_ORDER_ITEM_CATEGORY%','%SHOP_ORDER_ITEM_SUBNAME%','%CARTPRICE%','%SERIAL%','SHOP_ORDER_ITEM_AMOUNT'),'',$bundle_primary_line);
				
					foreach($this->optionsModule->options as $opid=>$opt)
					{
						$opt='option'.($opid-1); //options in db are 0 based, in array 1 based
						$optVal=isset($v[$opt])?$v[$opt]:'';
						$bundle_primary_line=str_replace(array('%SHOP_ORDER_ITEM_OPTION'.$opid.'%','%option'.$opid.'%'),'',$bundle_primary_line);
					}
					if(isset($v['fields']))
					{
						foreach($v['fields'] as $fname=>$fvalue)
						{
							if($fname=='image1'&&strpos($bundle_primary_line,'%image1_path%')!==false)
							{
								$full_image=Linker::buildSelfURL('',true,$this->rel_path).str_replace('../','',$fvalue);
								$bundle_primary_line=str_replace(array('%'.$fname.'%','%image1_path%'),array($fvalue,$full_image),$bundle_primary_line);
							}
							else
								$bundle_primary_line=str_replace('%'.$fname.'%','',$bundle_primary_line);
						}
					}
					$this->ReplaceMacros($bundle_primary_line);
					$result.=$bundle_primary_line;
					$bundle_primary_line=$bundle_description='';
					$this_bundle_count=$bundle_price=$bundle_vat=$bundle_vat_amount=0;
				}
			}
			else
			{
				$count = $this->all_settings['allow_float_amount']?$this->nrf($this->float_val($v['count'])):intval($v['count']);
				$p_line=str_replace(array(
				'%SHOP_ORDER_ITEM_NAME%','%SHOP_ORDER_ITEM(P_Code)%','%SHOP_ORDER_ITEM_CATEGORY%','%SHOP_ORDER_ITEM_SUBNAME%',
				'%SHOP_ORDER_ITEM_COUNT%','%QUANTITY%',
				'%SHOP_ORDER_ITEM_AMOUNT%','%CARTPRICE%','%LINEPRICE%',
				'%LISTER_COUNTER%','%SHOP_COUNTER%','%ITEM_VAT%','%ITEM_VAT_AMOUNT%',
				'%SERIAL%'
				 ),
				array($v['name'],$v['code'],$v['category'],$v['sname'],
				 $count,$count,
				 $v['amount'],$v['amount'],$this->nrf($this->float_val($v['amount'])*$v['count']),
				 $itemid_bw,$itemid_bw,$this->nrf($v['vat']),$this->nrf($v['vat_amount']),
				 $v['serial']
				)
				,$p_line);
				$p_line=str_replace('%bundle_description%','',$p_line);
				
				foreach($this->optionsModule->options as $opid=>$opt)
				{
					$opt='option'.($opid-1); //options in db are 0 based, in array 1 based
					$optVal=isset($v[$opt])?$v[$opt]:'';
					$p_line=str_replace(array('%SHOP_ORDER_ITEM_OPTION'.$opid.'%','%option'.$opid.'%'),$optVal,$p_line);
				}

				if(isset($v['fields']))
				{
					foreach($v['fields'] as $fname=>$fvalue)
					{
						if($fname=='image1'&&strpos($p_line,'%image1_path%')!==false)
						{
							$full_image=Linker::buildSelfURL('',true,$this->rel_path).str_replace('../','',$fvalue);
							$p_line=str_replace(array('%'.$fname.'%','%image1_path%'),array($fvalue,$full_image),$p_line);
						}
						else
							$p_line=str_replace('%'.$fname.'%',$fvalue,$p_line);
					}
				}
				$this->ReplaceMacros($p_line);
				$result.=$p_line;
			}
		}
		$result=str_replace($product_body,$result,$return_data);
		//parsing fields
		$order_fields=explode('|',$data[0]['form_fields']);
		foreach($order_fields as $fv)
		{
			$field=explode('=',$fv);
			$result=str_replace('%'.$field[0].'%',$field[1],$result);
		}

		if($this->pg_settings['g_excmode']&&$this->pg_settings['g_tax_handling'])
			$vat_total=floatval($data[0]['vat1']);

		$coupon_used=$this->couponsModule->is_coupon();
		if($coupon!='' && !$coupon_used)
		{
			$val1=explode('|',$coupon);
			$coupon_procent=strpos($val1[1],'%');
			$coupon_amount = floatval($val1[1]);
			if($coupon_procent){
				$price_total = round(($price_total - $price_total*($coupon_amount/100)),$this->pg_settings['g_price_decimals']);
				$vat_total = round(($vat_total - $vat_total*($coupon_amount/100)),$this->pg_settings['g_price_decimals']);
			}
			else {
				$price_total = round(($price_total - $coupon_amount),$this->pg_settings['g_price_decimals']);
				$vat_total = round(($vat_total - $coupon_amount),$this->pg_settings['g_price_decimals']);
			}
			if($price_total<0)
				$price_total = round(0,$this->pg_settings['g_price_decimals']);
			if($vat_total<0)
				$vat_total = round(0,$this->pg_settings['g_price_decimals']);
		}

		$result=$this->parse_totals($result,
								$price_total,
								$vat_total,
								$shop_shipping,
								$bcounter,
								$itemcounter,
								$this->pg_settings['g_excmode']&&$this->pg_settings['g_tax_handling']&&$vat_total>0,
								$products_array);

		$result=str_replace(
				array('%SHOP_ORDER_ID%','%ORDER_ID%','%SHOP_TRANS_ID%',
					 '%SHOP_ORDER_DATE%','%SHOP_ITEMS_COUNT%','%SHOP_ITEMS_COUNT2%',
					 '%ec_PaymentMethod%','%invoice_status%','%invoice_number%','%user_id%','%user:avatar%'),
				array($order_id,$order_id,$this->session_transaction_id,
					 $this->format_dateSql($data[0]['pdate'],'long','auto'),$bcounter,$itemcounter,$this->lang_l($pt),$invStatus,$invoiceNr,$userId,$avatar),
				$result);

		if(strpos($result,'<VAT>')!==false)
			$this->parse_vatdetail($result,$shop_shipping,$data_array);

		if($this->paymentModule!==false)
			$this->paymentModule->parseReturnMacros($result,$order_id);

		$this->parse_page_id($result);
		$this->ReplaceMacros($result);
		$result=$this->build_category_lists(0,$result);

		$cleanuplink=$this->full_script_path.'?cleanup=&amp;action=list';
		if(strpos($result,'%SHOP_CLEANUP_BUTTON%"')!==false)
			$result=str_replace('%SHOP_CLEANUP_BUTTON%"','javascript:void(0);" onclick="remove_item_ajax(\''.$cleanuplink.'\')" ',$result);
		$result=preg_replace('/%SHOP_CLEANUP_BUTTON%+[^"]+/i','javascript:void(0);" onclick="remove_item_ajax(\''.$cleanuplink.'\')',$result);

		$result=str_replace($this->int_tags,'',$result);

		return $result;
	}

	protected function count_shipping($price_total,$itemcounter,$itembased_shipping,$shippingweight_total)
	{
		$result=0;
		$shipping_type=$this->all_settings['g_ship_type'];
		if($shipping_type==4)
			return $result;
		$shop_shipping_settings=explode("|",$this->all_settings['g_ship_settings']);
		$ship_amount=(float)$this->all_settings['g_ship_amount'];
		$ship_above_limit=(float)$this->all_settings['g_ship_above_limit'];
		if($this->g_ship_usefield)
		{
			$result=$itembased_shipping;
			if($this->all_settings['g_ship_above_on'] && ($price_total >= $ship_above_limit))
				$result=0;
			elseif($shipping_type==6)
			{
				$count=count($shop_shipping_settings);
				for($i=0;$i<$count-1;$i++)
				{
					$limits=explode("-",Formatter::GFS($shop_shipping_settings[$i],'','='));
					if(($result >= (float)$limits[0])&&($result <= (float)$limits[1])) {
						 $result=(float)str_replace(',','.',Formatter::GFS($shop_shipping_settings[$i],'=',''));
					}
				}
			}
		}
		else
		{
			if($this->all_settings['g_ship_above_on'] &&($price_total >= $ship_above_limit))
				$result=0;
			elseif($shipping_type==3)
				$result=$ship_amount;
			elseif($shipping_type==2)
				$result=($ship_amount*$itemcounter);
			elseif($shipping_type==7)
			{
				$country_id=strpos($this->all_settings['g_ship_settings'],$this->pg_settings['g_check_country'].'=')!==false?
						  $this->pg_settings['g_check_country']:'other';

				$count=count($shop_shipping_settings);
				for($i=0;$i<$count-1;$i++)
				{
					$id=Formatter::GFS($shop_shipping_settings[$i],'','=');
					if($id==$country_id)
					{
						$result=Formatter::GFS($shop_shipping_settings[$i],'=','');
						break;
					}
				}
			}
			elseif($shipping_type==8)
			{
				$count=count($shop_shipping_settings);
				for($i=0;$i<$count-1;$i++)
				{
					$limits=explode("-",Formatter::GFS($shop_shipping_settings[$i],'','='));
					if(($shippingweight_total >= (float)$limits[0])&&($shippingweight_total <= (float)$limits[1]))
					{
						$shv=str_replace(',','.',Formatter::GFS($shop_shipping_settings[$i],'=',''));
						$result=(float)$shv;
					}
				}
			}
			elseif($shipping_type==9) //custom shipping list
			{
				$shipping_id=strpos($this->all_settings['g_ship_settings'],$this->pg_settings['g_check_shipping_list'].'=')!==false?
					$this->pg_settings['g_check_shipping_list']:'other';

				$count=count($shop_shipping_settings);
				for($i=0;$i<$count-1;$i++)
				{
					$id=Formatter::GFS($shop_shipping_settings[$i],'','=');
					if($id==$shipping_id)
					{
						$result=Formatter::GFS($shop_shipping_settings[$i],'=','');
						break;
					}
				}
			}
			elseif($shipping_type==1)
			{
				$count=count($shop_shipping_settings);
				for($i=0;$i<$count-1;$i++)
				{
					$limits=explode("-",Formatter::GFS($shop_shipping_settings[$i],'','='));
					if(($itemcounter >= (float)$limits[0])&&($itemcounter <= (float)$limits[1]))
						$result=(float)str_replace(',','.',Formatter::GFS($shop_shipping_settings[$i],'=',''));
				}
			}
			elseif($shipping_type==0)
			{
				$count=count($shop_shipping_settings);
				for($i=0;$i<$count-1;$i++)
				{
					$limits=explode("-",Formatter::GFS($shop_shipping_settings[$i],'','='));
					if(($price_total >= (float)$limits[0])&&($price_total <= (float)$limits[1]))
					{
						$shv=str_replace(',','.',Formatter::GFS($shop_shipping_settings[$i],'=',''));
						if(strpos($shv,'%')!==false)
							$result=($price_total*(float)$shv)/100;
						else
							$result=(float)$shv;
					}
				}
			}
		}
		return $result;
	}

	protected function create_option($id,$src,$default,$recordid,$required,$price)
	{
		if($default=='')
		{
			$items=explode(";",$src);
			$count=count($items);
			$price_offset=strpos($src,'[')!==false;
			$color_picker=strpos($src,'})')!==false;

			if($count>1)
			{
				$onchange=$price_offset?' onchange="update_price(this,'.$recordid.','.$price.')"':'';
				if(trim($items[$count-1])=='radio')
				{
					 array_pop($items);
					 $result='<div rel="0" name="'.$id.'" class="options '.($price_offset?'ups ':'').$id.'">';
					 foreach($items as $k=>$v)
					 {
						  $key=ListerFunctions::GetSubKey($v);
						  $val=($k==0 && $required)?'':$v;
						  $result.='<input type="radio"'.($k==0?' checked=checked" ':'').($k==0 && $required?' style="display:none" ':'').' name="'.$id.'" value="'.$val.'" '.$onchange.'><span>'.$key.'</span><br>';
					 }
					 $result.='</div>';
				}
				else
				{
					 $result='<select rel="0" name="'.$id.'" class="'.($color_picker?'niceSelect ':'').'input1 '.($price_offset?'ups ':'').$id.'"'.$onchange.'>';
					 foreach($items as $k=>$v)
					 {
						 list($c_name,$c_code)=$color_picker && strpos($v,'})')!==false?explode('{',str_replace(array('(','})'),'',$v)):array('','');
						 $key=ListerFunctions::GetSubKey($v);
						 $val=($k==0 && $required)?'':$v;
						 $result.='<option value="'.$val.'"'.($color_picker?' data-style="background-color:#'.$c_code.';"':'').'>'.($c_name!=''?$c_name:$key).'</option>';
					 }
					 $result.='</select>';
				}
			}
			elseif($count==1)
				$result=$src.(!$required?'<input type="hidden" class="'.$id.'" name="'.$id.'" value="'.$src.'">':'');
			else
				$result='';
		}
		elseif(strpos($default,'=')!==false)
		{
			$temp=explode("=",$default);
			$result=$temp[0];
		}
		else
			$result=$default;
		return $result;
	}

	protected function GetRecordLine($id)
	{
		global $db;

		$record=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->g_datapre.'data
			WHERE pid='.intval($id));
		return $record;
	}

	protected function GetBundleLine($id)
	{
		global $db;

		$record=$db->query_first('
			SELECT *, LENGTH(`bundlelist`) - LENGTH(REPLACE(`bundlelist`, ";", "")) as bundle_count
			FROM '.$db->pre.$this->g_datapre.'bundles
			WHERE id='.intval($id).' AND publish_status="published"');
		return $record;
	}

	protected function GetRecordLineRel($id)
	{
		global $db;

		$temp=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.$this->g_datapre.'data
			WHERE pid='.$id.' OR rel='.$id.'
			ORDER BY rel');
		$records=array();
		foreach($temp as $v)
			$records[$v['pid']]=$v;
		return $records;
	}

	public function ReplaceFieldsData($ttasurl,$data,$src,$srcIsFull,$recordid,$subascombo,$include_meta=false,$show_item=false)
	{
		$result=$this->replaceFieldsFinal($ttasurl,$data,$src,$srcIsFull,$recordid,'','',$subascombo,$include_meta,$show_item);
		$this->ReplaceMacros($result);
		return $result;
	}

	public function ReplaceFieldsFromData($ttasurl,$src,$srcIsFull,$recordid,$data,$subascombo)
	{
		$result=$this->replaceFieldsFinal($ttasurl,$data,$src,$srcIsFull,$recordid,'','',$subascombo);
		$this->ReplaceMacros($result);
		return $result;
	}

	protected function get_subfieldvalue($src)
	{
		$res=strpos($src,'})')!==false?Formatter::GFS($src,'(','{'):$src;
		$res=Formatter::GFS($res,'','=');
		$res=Formatter::GFS($res,'','[');
		return $res;
	}

	protected function get_userdata($ud,$field)
	{
		$ud_array=explode('**',$ud);
		$result='';
		foreach($ud_array as $v)
			if(strpos($v,$field.':')===0)
				$result=Formatter::GFS($v,':','');
		return $result;
	}

	public function replaceFieldsFinal($ttasurl,$data,$src,$srcIsFull,$recordid,$item_options,$record_userdata,
			$subascombo,$use_rs=false,$show_item=false)
	{
		$vplayer=strpos($src,'single.swf')!==false;
		$can_edit=$this->user->userCanEdit($this->page_info,$this->rel_path,$this->edit_own_posts_only);
		$contentbuilder=$this->all_settings['contentbuilder'];
		$front_edit=$this->all_settings['front_edit'];
		if($show_item&&$can_edit&&$front_edit)
			$this->innova_on_output=true;
		$logged_user=$this->user->getId();

		if(is_array($data))
		{
			$psrc=$src;

			foreach($data as $k=>$v)    //pushing editor fields to top
			{
				$ftype=(array_key_exists($k,$this->pg_settings['g_fields_array']))?$this->pg_settings['g_fields_array'][$k]['itype']:'';
				if($ftype=='editor')
					$data=array($k=>$data[$k])+$data;
			}

			if(strpos($psrc,'%category_parent%')!==false)
			{
				$parentid=$temp=-1;
				$category=$this->categoriesModule->get_category_name($data['cid'],$parentid);
				$parent_category=$parentid>-1?$this->categoriesModule->get_category_name($parentid,$temp):$category;
					if($temp>-1)
						$parent_category=$this->categoriesModule->get_category_name($temp,$parentid);
				$psrc=str_replace('%category_parent%',$parent_category,$psrc);
			}

			foreach($data as $k=>$v)
			{
				if(is_array($v))
					continue;
				$fname=$k;
				$ftype=(array_key_exists($k,$this->pg_settings['g_fields_array']))?$this->pg_settings['g_fields_array'][$k]['itype']:'';
				if(trim($v)=='')
					$val='';
				$realval=$v;

				if($ftype=='editor' || $ftype=='area')
				{
					$v=str_replace('rel="lightbox"','class="multibox LB'.$recordid.' mbox" rel="lightbox['.$recordid.'],noDesc"',$v);  //innova Live
					if($this->protected && $this->f->ca_settings['protect_downloads'])
					{
						$v=str_replace($this->site_base.$this->rel_path.'innovaeditor/',$this->rel_path.'innovaeditor/',$v);
						$v=Formatter::encodeForDownload($v);
					}
					if(strpos($v,'{%SLIDESHOW_ID(') !== false)
					{
						$slideshow=new Slideshow();
						$slideshow->replaceSlideshowMacro($v,$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
					}
					if(strpos($v,'{%HTML5PLAYER_ID(') !== false)
					{
						$slideshow=new Slideshow();
						$slideshow->replace_html5playerMacro($v,$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
					}
					$realval=$v;
					if($ftype=='editor')
					{
						$post_content='<div class="post_content'.($show_item&&$can_edit?' post_editable':'').'" '.(isset($data['pid'])?'id="product_'.$data['pid'].'"':'').'>';
						$can_edit_item=($this->limit_own_post>0&&$data['posted_by']==$logged_user)||$this->limit_own_post==1000000;
						if($show_item && $can_edit && $front_edit && $can_edit_item)
						{
							$textarea_content=Formatter::sth2($v);
							if($contentbuilder)
								$editor_parsed=Editor::getContentBuilder_js($this->rel_path,$data['pid']);
							else{
								$editor_parsed=str_replace('oEdit1','oEdit1'.$data['pid'],$this->innova_def);
								$editor_parsed=str_replace('htmlarea','txtContent'.$data['pid'],$editor_parsed);
							}
							$form='
							<form method="post" action="'.$this->script_path.'?action=save_product_simple"'.($contentbuilder?' class="cb_form_'.$data['pid'].'" onsubmit="return save_cb(\''.$data['pid'].'\')"':'').'>
								<input class="input1" type="hidden" name="pid" value="'.$data['pid'].'">
								<input class="input1" type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">
								<div id="edit_content'.$data['pid'].'" class="content_editor" style="display:block;text-align:left;clear:both;">'.
								($contentbuilder?
									'<div id="contentarea'.$data['pid'].'" class="containerCB" style="width:100%">'.$textarea_content.'</div>'.$editor_parsed
									:'<textarea class="mceEditor" id="txtContent'.$data['pid'].'" name="content" style="width:100%" rows="4" cols="30">'.$textarea_content.'</textarea>'
									.$editor_parsed)
									.'<input class="input1'.($contentbuilder?' save_button':'').'" type="submit" name="submit" value="'.$this->lang_l('save').'">&nbsp;
									<input class="input1'.($contentbuilder?' close_button':'').'" type="button" value="'.$this->lang_l('cancel').'" onclick="javascript:swap(\'post_init_content_'.$data['pid'].'\',\'edit_post_'.$data['pid'].'\');'.($contentbuilder?' hide_contentbuilder();':'').'"><br><br>
								</div>
							</form>';
							$content_line='<div class="edit_post" id="edit_post_'.$data['pid'].'" style="display:none;">'.$form.'</div>
							<div id="post_init_content_'.$data['pid'].'" style="display:block;">'.($contentbuilder?'<div class="containerCB">'.$textarea_content.'</div>':$textarea_content).'</div>';
							$post_content.=$content_line.'</div>'.
							'<div class="rvps2">
								<input type="button" rel="'.$data['pid'].'" value="" title="'.$this->lang_l('edit').' +" class="edit_inline ui_shandle_ic6" style="background-color: #d7d7d7;margin: 1px 0;">
								<input type="button" onclick="document.location=\''.$this->script_path.'?action=products&amp;prod_select='.$data['pid'].'&amp;cat_select='.$data['cid'].Linker::buildReturnURL().'\'" value="" title="'.$this->lang_l('edit').'" class="ui_shandle_ic4" style="background-color: #d7d7d7;margin: 1px 0;background-position:-32px -80px">
							</div>';
							$v=$post_content;
						}
						else
							$v=$post_content.($contentbuilder?'<div class="containerCB">'.Formatter::sth2($v).'</div>':Formatter::sth2($v)).'</div>';
					}
				}
				elseif($ftype=='file')
				{
					$xval=($v=='')?'':$this->g_abs_path.$this->pg_name.'?action=fdownload&amp;fname='.$k.'&amp;id='.$recordid;
					$psrc=str_replace(array('%DOWNLOAD(%'.$fname.'%)%',$fname.'_url%'),$xval,$psrc);
				}
				elseif($ftype=='mp3')
				{
					if($v!=='')
					{
						$vx=str_replace('../','',$v);
						if(strpos(strtolower($vx),'http')===false)
							$vx=(strpos(strtolower($vx),'.flv')!==false)?'../'.$vx:$this->rel_path.$vx;
						$ima='';
						if($vplayer)
						{
							$ima=str_replace('../','',$data['image1']);
							if($ima!=='')
								$ima='&image='.$this->rel_path.$ima;
						}
						$psrc=str_replace('audurl=%'.$fname.'%','audurl='.$vx.$ima,$psrc);
					}
					else
						$vx='';

					if(strpos($psrc,'%html5player[%'.$fname.'%,')!==false)
					{
						$media_ext=substr($vx,strrpos($vx,'.'));
						$psrc=str_replace('%html5player[%'.$fname.'%,','%html5player[',$psrc);
						$av_object=new audio_video($this->page);
						$dummy='';

						if(Video::youtube_vimeo_check($vx))
							$av_object->handle_youtube_vimeo_player($psrc,1,$vx,$v,$dummy,1);
						else
							$av_object->parse_html5_audiovideo($psrc,$media_ext,$vx);
					}

					$psrc=str_replace('"%'.$fname.'%"','"'.str_replace(' ','%20',$vx).'"',$psrc);
				}

				if($ftype=='userinput')
				{
					$xval=($subascombo && $record_userdata=='')?Builder::buildInput($fname,'','','','text','','','','',false,'ui_'.$fname.'_qua_'.$recordid):$this->get_userdata($record_userdata,$fname);
					$psrc=str_replace('%'.$fname.'%',$xval,$psrc);
				}
				elseif($ftype=='timestamp' || $ftype=='datetime' || $ftype=='date')
				{
					$isnull=$v=='0000-00-00 00:00:00';
					while(strpos($psrc,'%DATE[%'.$fname.'%,')!==false)
					{
						$param=Formatter::GFSAbi($psrc,'%DATE[%'.$fname.'%,',']%');
						$dparam=Formatter::GFS($param,'%DATE[%'.$fname.'%,',']%');
						if($isnull)
							$date='';
						else
							$date=Date::format(strtotime($v),$dparam,$this->month_name,$this->day_name,'short',false);
						$psrc=str_replace($param,$date,$psrc);
					}
					if($isnull)
						$psrc=str_replace('%'.$fname.'%','',$psrc);
					else
						$psrc=str_replace('%'.$fname.'%',$this->format_dateSql($v,'long','auto'),$psrc);
				}
				else
				{
					$parentid=-1;
					if($fname=='category' || $fname=='subcategory')
					{
						$val=$this->categoriesModule->get_category_name($data['cid'],$parentid);
						if($fname=='subcategory' && $parentid==-1)
							$val='';
					}
					else
						$val=$v;
					$val=str_replace('%1310',F_BR,$val);
					while(strpos($psrc,'%SCALE['.'%'.$fname.'%')!==false)
					{

						if($val!='' && $this->all_settings['watermark'])
							$val=$this->rel_path.'documents/utils.php?w='.Crypt::encrypt(($this->rel_path==''?'../':'').$val.'|'.$this->get_setting('watermark_position')).'&i='.basename($val);

						Images::parseScale($data,$psrc,$fname,$use_rs,$ttasurl,$val);
						$val=$v;
					}

					if($ftype=='subname' || in_array($fname,$this->optionsModule->options))
					{
						$id=intval(str_replace('option','',$fname));
						$xval=isset($item_options[$id])?$item_options[$id]:'';

						$req=$this->get_setting('option'.$id.'_req')=='1';
						$val=($subascombo && isset($data[$this->pg_settings['g_pricefield']]))?
								$this->create_option('subtype'.$id,$realval,$xval,$recordid,$req,$this->get_price_value($data))
								:($xval==''?$this->create_option('subtype'.$id,$realval,$xval,$recordid,$req,0):$this->get_subfieldvalue($xval));
					}

					if($fname=='stock' && $this->shop)
					{
						$session_stock=$this->stockModule->get_stock_session($data['pid']);

						if($session_stock>0)
							$val-=$session_stock;
						if(intval($v) < 0 || $val<0)
							$val=0;
					}

					if(strpos($val,'<!--scripts2-->') !== false)
					{
						$scripts=Formatter::GFSAbi($val,'<!--scripts2-->','<!--endscripts-->');
						$val=str_replace($scripts,'',$val);
						$scripts=str_replace(array('<!--//','//-->','// -->'),'',$scripts);
					}
					else
						$scripts='';

					if(strpos($psrc,'%'.$fname) !== false || strpos($psrc,'#'.$fname) !== false)		//		 %vat&amp;decimals=2%
					{
						$pricefield=isset($this->pg_settings['g_fields_array'][$fname])&&($this->pg_settings['g_fields_array'][$fname]['itype']=='price');
						if($pricefield)
						{
							if($realval=='')
								$realval=0;
							if($fname=='sale_price' && $realval==0)
								$psrc=str_replace(array('%%'.$fname.'%%','%'.$fname.'%'),'',$psrc);
							$val_dec=$this->nrf($realval,true);
							$psrc=str_replace(
								array('%%'.$fname.'%%','%'.$fname.'%'),
								array($val_dec,'<span class="price_value_'.$recordid.'">'.$val_dec.'</span>'),
								$psrc);
						}
						elseif($fname==$this->pg_settings['g_vatfield'])
						{
							if($realval=='')
								$realval=0;
							$val_dec=$this->nrf($realval);
							$psrc=str_replace('%'.$fname.'%',$val_dec,$psrc);
						}

						if((strpos($psrc,'%'.$fname.'&amp;decimals=') !== false))
						{
							$dec=intval(Formatter::GFS($psrc,'%'.$fname.'&amp;decimals=','%'));
							if($dec=='')
								$dec=0;
							$val_dec=number_format(floatval($realval),$dec,$this->pg_settings['g_decimal_sign'],$this->pg_settings['g_thousands_sep']);
							$psrc=str_replace('%'.$fname.'&amp;decimals='.$dec.'%',$val_dec,$psrc);
						}

						if($fname=='shippingweight'&&strpos($psrc,'%'.$fname.'%') !== false)
						{
							if($realval=='')
								$realval=0;
							$val_dec=number_format(floatval($realval),2,'.',' ');
							$psrc=str_replace('%'.$fname.'%',$val_dec,$psrc);
						}

						if(strpos($psrc,'%COPY[%'.$fname.'%') !== false)
							Formatter::replaceCopyMacro($psrc,'%'.$fname.'%',$val);

						if(strpos($psrc,'COPY(%'.$fname.'%') !== false)  //deprecated, use above
						{
							$m=Formatter::GFSAbi($psrc,'COPY(%'.$fname.'%',')');
							$d=explode(',',Formatter::GFS($m,'COPY(',')'));
							$v=mb_substr(strip_tags($val),$d[1]-1,isset($d[2])?$d[2]-$d[1]+1:1000000,'UTF-8');
							$psrc=str_replace(array('%'.$m.'%',$m),$v,$psrc);
						}
						elseif(strpos($psrc,'LOCATION(%'.$fname.'%') !== false)
						{
							$v=Gmaps::resolve_location_visual($v,'450x200');
							$m=Formatter::GFSAbi($psrc,'LOCATION(%'.$fname.'%',')');
							$psrc=str_replace($m,$v,$psrc);
						}

						$psrc=str_replace('<condition>%'.$fname.'%','<condition>'.$realval,$psrc);
						if(strpos($val,'<p') !== false)
							$psrc=str_replace('<p>%'.$fname.'%</p>',$val,$psrc);

						$psrc=str_replace(array('#%'.$fname.'%#','%'.$fname.'%','#'.$fname.'#'),
										array(str_replace('"','&quot;',$val),$val,$realval),$psrc);

						if($scripts !== '')
						{
							if($srcIsFull)
								$psrc=str_replace('<!--scripts-->','<!--scripts-->'.$scripts,$psrc);
							elseif(strpos($this->global_pagescr,$scripts)===false)
								$this->global_pagescr .=$scripts;
						}
					}
				}
			}
		}
		else if($this->all_settings['error_404_page']!='') {
			$url_404_page = Linker::buildSelfURL('',false,$this->rel_path).$this->all_settings['error_404_page'];
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
			header('Location: '.$url_404_page);
			exit;
		} else {
			$psrc='Invalid URL';
		}
		if(strpos($psrc,'font-family')!==false)
			$this->parseGfonts=true;
		return $psrc;
	}

	public function replaceExchangeRates(&$src)
	{
		$exch_rate_settings=explode("|",$this->all_settings['exch_rate_settings']);
		foreach($exch_rate_settings as $v)
		{
			if(strpos($v,'=')!==false)
			{
				list($key, $value)=explode('=',$v);
				if($key!=''&&$value!='')
				{
					while (strpos($src,'%'.$key.'%') !== false)
					{
						$temp=Formatter::GFSAbi($src,'%'.$key,'%');
						$src=str_replace($temp,$value,$src);
					}
				}
			}
		}
	}

	public function ReplaceMacros(&$src)
	{
		$this->replaceExchangeRates($src);
		while (strpos($src,'%age(') !== false) :
			$temp=Formatter::GFSAbi($src,'%age(',')%');
			$cond=Formatter::GFS($temp,'%age(',')%');
			$year=0;$month=0;$day=0;
			if($cond!='')
			{
				$tday=intval(date("d"));$tmonth=intval(date("m"));$tyear=intval(date("Y"));
				ListerFunctions::DecDate($cond,$year,$month,$day);
				if($tmonth>$month)
					$res=$tyear-$year;
				elseif($tmonth<$month)
					$res=$tyear-$year-1;
				else
					$res=($tday<$day)?$tyear-$year-1:$tyear-$year;
			}
			else
				$res='';
			$src=str_replace($temp,$res,$src);
		endwhile;
		Formatter::replaceIfMacro($src);
		Formatter::replaceEvalMacro($src,$this->pg_settings['g_price_decimals'],$this->pg_settings['g_decimal_sign'],$this->pg_settings['g_thousands_sep']);
	}

	protected function get_product_hidden_fields($r,$action,$item_id,$cat_id,$page_id,$searchstr,$stock,$session_stock)
	{
		$ret='
			<input type="hidden" name="action" value="'.$action.'">
			<input type="hidden" name="iid" value="'.$item_id.'">'.
			($r?'
			<input type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">':'').'
			<input type="hidden" name="category_id" value="'.$cat_id.'">
			<input type="hidden" name="page" value="'.$page_id.'">
			<input type="hidden" name="q" value="'.$searchstr.'">
			<input type="hidden" class="stock" name="stock" value="'.($stock-$session_stock).'">';
		return $ret;
	}

	protected function get_orderbyarray()
	{
		$ob=explode("#",$this->get_setting('user_orderby'));
		$ob_val=array();
		foreach($ob as $k=>$v)
			$ob_val[$k]=explode("|",$v);
		return $ob_val;
	}

	protected function parse_orderby($page)
	{
		$ob_val=array();
		$ob=$this->get_orderbyarray();
		foreach($ob as $k=>$v) $ob_val[$k]=$v[0];
		if($this->tag_object!=null)
			$abs_url=$this->tag_object->build_permalink().($this->use_alt_plinks?'-/1/':'&orderbyid=');
		elseif(isset($_REQUEST['category']))
			$abs_url=$this->build_permalink_cat($this->g_catid,true,false,'','&',
				  $this->use_alt_plinks?'':'&orderbyid=','all',$this->g_pagenr);
		else
			$abs_url=$this->full_script_path.'?orderbyid=';

		//fix for ORDERBY_COMBO change producing URL like this: /category/asd/-/11 instead of /category/asd/-/1/1 for alt URLS
		if($this->use_alt_plinks && $abs_url[strlen($abs_url)-1] !== '/' )
			$abs_url .= '/';

		$js='onchange="javascript:document.location=\''.$abs_url.'\'+this.options[this.selectedIndex].value;"';
		return str_replace('%ORDERBY_COMBO%',str_replace('id="orderby"','',Builder::buildSelect('orderby',$ob_val,$this->g_orderbyid_user,'','key',$js)),$page);
	}

	public function get_orderby($use_cookie=true,$table='')
	{
		$orderby='';$ob_id='';$co=false;

		if($use_cookie)
		{
			if(isset($_REQUEST['orderbyid']))
			{
				$ob_id=$_REQUEST['orderbyid'];
				if($ob_id!=='')
					intval($ob_id)>0?setcookie($this->pg_id.'_orderbyid',$ob_id,time()+3600,'/'):setcookie($this->pg_id.'_orderbyid','',time()-3600,'/');
			}
			elseif(isset($_COOKIE[$this->pg_id.'_orderbyid']))
			{
				$co=true;
				$ob_id=$_COOKIE[$this->pg_id.'_orderbyid'];
			}
			if($ob_id!=='')
			{
				$ob=$this->get_orderbyarray();
				if(isset($ob[$ob_id]) && $ob[$ob_id][1]!='')
				{
					$this->g_orderbyid_user=$ob_id;
					$field=$ob[$ob_id][1]=='ranking'?'(ranking_count/ranking_total)':$ob[$ob_id][1];
					$orderby='ORDER BY '.$table.$field.' '.(strtolower($ob[$ob_id][2])=='desc'?'desc':'');
				}
			}
		}

		if($orderby=='')
		{
			if($this->pg_settings['g_orderby']!='none')
			{
				$orderby.='ORDER BY '.$table.$this->pg_settings['g_orderby'];
				if($this->pg_settings['g_orderbydesc'])
					$orderby.=' DESC';
			}
			if($this->pg_settings['g_orderby2']!='none')
			{
				if($orderby!='')
					$orderby.=',';
				else
					$orderby='ORDER BY ';
				$orderby.=$table.$this->pg_settings['g_orderby2'];
				if($this->pg_settings['g_orderbydesc2'])
					$orderby.=' DESC';
			}
		}
		return $orderby;
	}

	protected function replace_quantityForm($r,$page,$xaction,$item_id,$cat_id,$page_id,$stock,$session_stock)
	{
		if(strpos($page,'<QUANTITY>') !== false)
		{
			$session_stock=$this->stockModule->get_stock_session($item_id);
			$macro=Formatter::GFS($page,'<QUANTITY>','</QUANTITY>');

			if($this->stockModule->hide_empty() && ($stock-$session_stock)<1)
				 $macro_parsed=$this->get_setting('stock_mess');
			else
			{
				$macro_parsed=str_replace('%QUANTITY%','1',$macro);
				if(strpos($macro_parsed,'"addandcheckout"')!==false)
				{
					$xaction="addandcheckout";
					$macro_parsed=str_replace('<input type="hidden" name="action" value="addandcheckout">','',$macro_parsed);
				}
				$hidden_fields=$this->get_product_hidden_fields($r,$xaction,$item_id,$cat_id,$page_id,'',$stock,$session_stock);
				$macro_parsed=$hidden_fields.$macro_parsed;
				$macro_parsed=str_replace(array('%FORMNAME%','%QFORMNAME%'),'qua_'.$item_id,$macro_parsed);
				$macro_parsed='
					<form id="qua_'.$item_id.'" name="qua_'.$item_id.'" method="get" onsubmit="return validate_qua(\'qua_'.$item_id.'\',true)" action="'.$this->g_abs_path.$this->pg_name.'">'
								.$macro_parsed.'
					</form>';
				if(strpos($macro_parsed,'<SHOP_BUY_BUTTON>')!==false) //buy button
				{
					$btn_string=Formatter::GFS($macro_parsed,'<SHOP_BUY_BUTTON>','</SHOP_BUY_BUTTON>');
					$btn_parsed=$this->parseBuyOneButton($btn_string);
					$macro_parsed=str_replace('<SHOP_BUY_BUTTON>'.$btn_string.'</SHOP_BUY_BUTTON>',$btn_parsed,$macro_parsed);
				}
			}
			$page=str_replace($macro,$macro_parsed,$page);
		}
		return $page;
	}

	public function replace_MultiBuyButton(&$src)
	{
		$btn_string=Formatter::GFS($src,'<MULTI_BUY_BUTTON>','</MULTI_BUY_BUTTON>');
		$btn_parsed='';
		if($this->all_settings['use_ajax']&&(strpos($src,'id="minicart"')!==false||strpos($src,'id="basketcart"')!==false)){
			$btn_parsed.=$this->parseBuyOneButton($btn_string,true);
			$btn_parsed.='<div class="multibuy_loader" style="display:none" ><img src="'.$this->rel_path.'extimages/scripts/loader.gif"></div>';
		}
		$src=str_replace('<MULTI_BUY_BUTTON>'.$btn_string.'</MULTI_BUY_BUTTON>',$btn_parsed,$src);
	}

	public function parseBuyOneButton($btn_string,$multi_buy=false)
	{
		if(strpos($btn_string,'<img')!==false)
		{
			$img_src=Formatter::GFS($btn_string,'src="','"');
			$parsed='<input type="image" style="text-align:center" src="'.$img_src.'">';
		}
		else
			$parsed=str_replace('%URL%','javascript:void(0);" onclick="'.($multi_buy?'multibuy_ajax();':'$(this).parents(\'form:first\').submit();'),$btn_string);

		return ($multi_buy?'':'<input name="quantity" type="hidden" value="1">').$parsed;
	}

	public function replace_buybuttons($r,$src,$xaction,$p_id,$cat_id,$page_id,$stock)
	{
		$session_stock=$this->stockModule->get_stock_session($p_id);
		$src=$this->replace_quantityForm($r,$src,$xaction,$p_id,$cat_id,$page_id,$stock,$session_stock);

		if(strpos($src,'<SHOP_BUY_BUTTON>')!==false) //buy button
		{
			$btn_string=Formatter::GFS($src,'<SHOP_BUY_BUTTON>','</SHOP_BUY_BUTTON>');
			if($this->stockModule->hide_empty() && ($stock-$session_stock)<1)
				 $btn_parsed=$this->get_setting('stock_mess');
			else
			{
				$hidden_fields=$this->get_product_hidden_fields($r,$xaction,$p_id,$cat_id,$page_id,'',$stock,$session_stock);

				$btn_parsed=$this->parseBuyOneButton($btn_string);
				$btn_parsed='
					<form method="get" action="'.$this->g_abs_path.$this->pg_name.'" onsubmit="return add_item_ajax($(this))">
						'.$hidden_fields.$btn_parsed.'
					</form>';
			}
			$src=str_replace('<SHOP_BUY_BUTTON>'.$btn_string.'</SHOP_BUY_BUTTON>',$btn_parsed,$src);
		}

		return $src;
	}

	protected function add_editor_functions(&$page)
	{
		$can_edit=$this->user->userCanEdit($this->page_info,$this->rel_path,$this->edit_own_posts_only);
		$contentbuilder=$this->all_settings['contentbuilder'];
		$front_edit=$this->all_settings['front_edit'];
		if($can_edit&&$front_edit)
		{
			$this->page_scripts.='
			$(document).bind("ready",function(){
				$(".edit_inline").on("click", function(){
					var th=$(this).prev();
					th.addClass("edit_mode");
					entry_id=$(this).attr("rel");
					$("#post_init_content_"+entry_id).hide();'.
					($this->f->tiny&&!$contentbuilder?'tinyMCE.execCommand("mceAddEditor",true,"txtContent"+entry_id);':'').'
					$("#edit_post_"+entry_id).show();
				});});
				function swap(vis_id,hid_id){
					$("#"+hid_id).hide();
					$("#"+vis_id).show().parent().removeClass("edit_mode");
				};';
		}
		//content-builder
		if(strpos($page,'init_contentbuilder(')!==false)
		{
			Editor::getContentBuilder_scripts($this->page_scripts,$this->page_css,$this->page_dependencies,true,true);
			$lang=isset($this->pg_settings['ed_lang'])?$this->pg_settings['ed_lang']:'english';
			Editor::addSlideshow_Plugin_contentBuilder($this->page_scripts,$this->page_dependencies,$this->rel_path,$lang,$this->lang_l('slideshow'),$this->pg_id);
		}
		if(strpos($page,'<!--cbuilder_slideshow-->')!==false)
		{
			$slideshow=new Slideshow();
			$slideshow->replaceSlideshow_contentBuilder($page,$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
		}
		if($contentbuilder)
			Editor::getContentBuilder_scripts($this->page_scripts,$this->page_css,$this->page_dependencies,false);
	}

	protected function show_item($item_id,$template='')		//shows product detail page
	{
		global $db;

		$this->mode=MODE_DETAIL;
		$page=$template==''?$this->get_pagetemplate($this->resolve_template($this->mode)):$template;
		$use_rs=(strpos($page,'<SCOPE_')!=false);
		if($use_rs)
			item_snippets::parse_scope($page);

		$item_id_rel=0;

		if($this->g_use_rel)
		{
			if(strpos($page,'<VARIATIONS>')==false)
				$page=str_replace('<SHOP_FOOTER>','<SHOP_FOOTER><VARIATIONS>
				%SCALE[%image1%,50,0,url,%SHOP_DETAIL%,left]%
				</VARIATIONS>',$page);


			$old_rel=Formatter::GFS($page,'<VARIATIONS>','</VARIATIONS>');
			$new_rel='';
			$recline_rel=$this->GetRecordLineRel($item_id);

			if($recline_rel[$item_id]['rel']>0)
				$recline_rel=$this->GetRecordLineRel($recline_rel[$item_id]['rel']);

			if(isset($_REQUEST['rel']) && isset($recline_rel[$_REQUEST['rel']])) //handle relative product
			{
				$item_id_rel=intval($_REQUEST['rel']);
				$recline=$recline_rel[$item_id_rel];
				$recline_src=reset($recline_rel);
				foreach($recline as $k=>$v)
					if(trim($v)=='' && $recline_src[$k]!='')
						$recline[$k]=$recline_src[$k];
			}
			else
				$recline=reset($recline_rel);


			if(count($recline_rel)>1)
			{
				foreach($recline_rel as $k=>$v)
				{
					$data=$v;
					$data['pid']=$item_id;
					$plink=$this->build_permalink($data,false,'',true);
					$pline=$this->replaceFieldsFinal(true,$v,$old_rel,false,$v['pid'],'','',true);
					$pline=str_replace(array('%SHOP_DETAIL%','%permalink%'),
						$plink.($v['rel']>0?($this->use_alt_plinks?'?':'&').'rel='.$v['pid']:''),
						$pline);
					if($this->shop)
						$pline=$this->replace_buybuttons(0,$pline,'add',$v['pid'],$v['cid'],$this->g_pagenr,$v['stock']);
					$new_rel.=$pline;
				}
			}
			else
				$new_rel='';
			$page=str_replace($old_rel,$new_rel.'<div class="clear"></div>',$page);
		}
		else
			$recline=$this->GetRecordLine($item_id);
		$cat_id=$recline['cid'];

		$cdata=$this->categoriesModule->get_categoryData($cat_id);
		if(isset($cdata['linked']))
			$cdata=$cdata['linked'];

		$this->replace_category_header($page,$cdata,$cat_id);

		$this->g_code=isset($recline['code'])?$recline['code']:'';
		$stock=isset($recline['stock'])?intval($recline['stock']):1;

		//comments
		if($this->pg_settings['enable_comments'])
		{
			if(isset($_POST['sign_id']))
				$this->commentModule->save_comment($item_id);
			$page=$this->commentModule->replace_comments_macros($page,true);

			$can_edit=$this->user->userCanEdit($this->page_info,$this->rel_path,$this->edit_own_posts_only);
			$page=$this->commentModule->display_comments(true,$item_id,$page,$can_edit);
		}

		if($this->g_use_com)
			$this->rankingVisitsModule->save_ranking_visits($item_id,0,isset($recline['visits_count']));

		if(isset($_REQUEST['xview']))
			$page=$this->get_xviewpage($page);

		$page=$this->recentlyViewedModule->parse_recently_viewed($page);
		$page=Builder::buildLoggedInfo($page,$this->pg_id,$this->rel_path);
		$cat_search=strpos($page,'%SHOP_CAT_SEARCH%')!==false;

		//code for filters

		list($kids_count,$kids_ids)=$this->categoriesModule->get_category_kids_count($cat_id,true);
		if(is_array($kids_ids))
		  $kids_ids=implode(',',array_merge(array($cat_id),$kids_ids));
		$cat_que=$kids_ids!=''?' d.cid IN ('.$kids_ids.') AND ':'';

		$fdata=array();
		$where=$cat_que.' '.$this->where_public;
		$where_all=($this->get_setting('var_filters'))?str_replace(' AND (rel=0) ','',$where):$where;
		$page=$this->filtersModule->replace_filters($page,$cat_id,$where_all,$fdata);

		//code for filters

		if(strpos($page,'<RANDOM')!==false)
		{
			$where=$this->where_public;
			if(strpos($page,'<RANDOM_INCATEGORY')!==false)
			{
				$page=str_replace('<RANDOM_INCATEGORY','<RANDOM',$page);
				if($cat_id<>'')
					$where.=' AND cid= '.$cat_id;
			}
			if($item_id<>'')
				$where.=' AND pid <> '.$item_id;
			$que='
				SELECT pid, 0 as "used"
				FROM '.$db->pre.$this->g_datapre.'data
				WHERE '.$where;
			$items_a=$db->fetch_all_array($que);
			$page=$this->replace_random($page,$items_a,$item_id);
		}

		if(strpos($page,'%RELATED')!==false)
		{
			$page=str_replace('%RELATED%','%RELATED()%',$page);
			$params=Formatter::GFS($page,'%RELATED(',')%');
			$paramsFormatted=$params==''?'%SCALE[%image1%,80,0,url,%permalink%]%':$params;

			$entries_related=$this->RelatedPostsHandler->get_related_posts($recline,$this->where_public);
			$page=str_replace('%RELATED('.$params.')%',
				$this->SidebarBuilder->entries_sidebar($entries_related,$paramsFormatted,false,false,'entries_related'),
				$page);
		}

		$parent=-1;
		$sname='';
		$curl=$csurl=$this->build_permalink_cat($cat_id,false);
		$cname=$this->categoriesModule->get_category_name($cat_id,$parent);

		if($parent>-1)
		{
			$sname=$cname;
			$par=-1;
			$cname=$this->categoriesModule->get_category_name($parent,$par);
			$curl=$this->build_permalink_cat($parent,false);
		}

		$page=str_replace('%NAVIGATION%','%SHOP_PREVIOUS% %SHOP_NEXT%',$page);
		$page=str_replace(array('%LISTER_PREVIOUS','%LISTER_NEXT','%LISTER_PREVIOUS_URL%','%LISTER_NEXT_URL%'),
				array('%SHOP_PREVIOUS','%SHOP_NEXT','%SHOP_PREVIOUS_URL%','%SHOP_NEXT_URL%'),
				$page);
		$this->permalink=$plink=$this->build_permalink($recline,false,'',true);
		$this->recentlyViewedModule->add_recently_viewed($item_id);

		if($this->g_use_com)
			$page=str_replace('%ranking%',$this->rankingVisitsModule->build_ranking($recline),$page);
		if($this->g_use_com)
			$page=str_replace(array('%viewed%','%visits%'),$recline['visits_count'],$page);
		if(strpos($page,'<REMOVE_FROM_WISHLIST>')!==false)
			$this->wishlistModule->replace_WishlistButtons($page,$item_id);
		if(strpos($page,'<ADD_TO_WISHLIST>')!==false)
			$this->wishlistModule->replace_WishlistButtons($page,$item_id,true);

		if(strpos($page,'%SHOP_PREVIOUS')!==false)
		{
			$orderby=$this->get_orderby();
			$data=$db->fetch_all_array('
				SELECT pid,name,cid '.($this->use_friendly?',friendlyurl':'').'
				FROM '.$db->pre.$this->g_datapre.'data
				WHERE '.($cat_id>0?'cid='.$cat_id.' AND ':'').$this->where_public.$orderby);

			$cnt=count($data);
			if($cnt>0)
			{
				$lnnr=-1;
				$prev=$next=0;
				for($i=0;$i<$cnt;$i++)
				{
					$it_ar[$i]=$data[$i];
					if($it_ar[$i]['pid']==$item_id)
						$lnnr=$i;
				}
				if($lnnr>-1)
				{
					$prev=($lnnr>0)?$it_ar[$lnnr-1]:0;
					$next=($lnnr<$cnt && isset($it_ar[$lnnr+1]))?$it_ar[$lnnr+1]:0;
				}
				$stopitem=($next==0)?$cnt:$cnt-2;
				$page=$this->parse_prevnext($page,$lnnr+1,$stopitem,$cnt-1,$prev,$next);

				if(strpos($page,'%PRODUCTS_MENU%')!==false)
				{
					$parentid=-1;
					$menu='
						<ul class="ssmenu">
							<li class="smenuhead">
								<a href="'.$this->build_permalink_cat(0,false).'" class="tmenu">All &gt; </a>
								<a href="'.$curl.'" class="tmenu"> '.$this->categoriesModule->get_category_name($cat_id,$parentid).'</a>
							</li>
							<li style="clear:left">
							</li>';
					foreach($data as $k=>$v)
						$menu.='<li><a target="_self" href="'.$this->build_permalink($v).'" class="stmenu">'.$v['name'].'</a></li>';

					$menu.='</ul>';
					$page=str_replace('%PRODUCTS_MENU%',$menu,$page);
				}
			}
			else
			{
				$result=$this->show_list();
				print $this->prepare_output($result);
			}
		}

		$permalink=$this->build_permalink($recline);
		if($this->shop)
			$page=$this->parse_minicart($page,0,0);

		Meta::replaceTitle($page,$recline,'name','title');
		Meta::replaceDesc($page,$recline,'description','html_description');
		$keywordsMacros=Formatter::GFS($page,'<meta name="keywords" content="','"');
		if(strpos($keywordsMacros,'%')===false)
			$page=str_replace(
					'<meta name="keywords" content="'.$keywordsMacros.'"',
					'<meta name="keywords" content="#%keywords%#"',$page);

		$page=str_replace(
			array('%25','%catname%','%category%','%subcategory%',
				'%category_description%','%category_description2%'),
			array('%',$cname,$cname,$sname,
				$this->categoriesModule->get_category_info($cat_id,'description',$parent),
				$this->categoriesModule->get_category_info($cat_id,'description2',$parent)),
			$page);

		$page=$this->ReplaceFieldsData(false,$recline,$page,true,$item_id,true,$use_rs,true);
		$this->add_editor_functions($page);

		$page=str_replace(array('%SELF_URL%','%permalink%'),$permalink,$page);

		$page=str_replace(
			array('%shorturl%','%permalink%','%SHOP_DETAIL%','%caturl%','%subcaturl%','%COMPARE%'),
			array(urlencode($plink),$plink,$curl,$curl,$csurl, $this->featuresModule->compare_list()),
			$page);

		$category_section2=Formatter::GFSAbi($page,'<CATEGORIES_BODY>','</CATEGORIES_BODY>');
		if($category_section2!='' || $cat_search)
		{
			$page=str_replace($category_section2,$this->parse_cat_list($category_section2),$page);
			$page=$this->searchModule->parse_searchmacros($page);
		}

		if($this->shop)
		{
			$page=$this->replace_buybuttons(strpos($page,'id="minicart"')!==false,$page,'add',$item_id_rel>0?$item_id_rel:$item_id,$cat_id,$this->g_pagenr,$stock);
			$page=str_replace('%CURRENCY%',$this->pg_settings['g_currency'],$page);
		}

		$fb_api_id='';
		if(strpos($template,'FB.init')!==false)
			$fb_api_id=Formatter::GFS($template,"appId:'","'");
		$og_tags['title']=$recline['name'];
		$og_tags['type']='Website';
		if($recline['short_description']!='')
			$og_tags['description']=str_replace('"','&quot;',preg_replace("'<[/!]*?[^<>]*?>'si"," ",$recline['short_description']));
		elseif($recline['description']!='')
			$og_tags['description']=str_replace('"','&quot;',preg_replace("'<[/!]*?[^<>]*?>'si"," ",$recline['description']));
		else
		{
			$description=preg_replace("'<[/!]*?[^<>]*?>'si"," ",$recline['html_description']);
			Formatter::replacePollMacro_null($description);
			if(strlen($description)>120) $description=trim(Formatter::splitHtmlContent($description,120));
			$og_tags['description']=str_replace('"','&quot;',$description);
		}

		$og_tags['url']=$plink;
		if($recline['image1']!='')
			$og_tags['image']=$this->f->site_url.str_replace('../','',$recline['image1']);

		$this->parse_page_id($page);
		$page=$this->build_category_lists($cat_id,$page);
		$page=str_replace($this->int_tags,'',$page);
		$page=str_replace(array('%LISTER_COUNTER%','%COUNTER%'),'1',$page);
		if(strpos($page,'%FEATURES%')!==false)
		{
			$cnt=0;

			if(strpos($page,'cluetip({className:"hhint"')!==false)
				$this->page_scripts.='$(document).ready(function(){$("label.hhint").cluetip({className:"hhint",width:200,arrows:true,dir:"top"});});';
			$cnt=0;
			$page=str_replace('%FEATURES%',$this->featuresModule->options_list($item_id,$cat_id,1,'',$cnt),$page);
		}

		$page=$this->finalize_output($page);
		$page=Builder::ogMeta($page,$og_tags,$fb_api_id);
		$this->setState('show_item',array(&$page));
		if($this->innova_on_output)
			$page=Builder::appendScript($this->innova_js,$page);
		return $page;
	}

	public function get_file_url($image_field,$rel_path=false)
	{
		$result='';
		if(!empty($image_field))
		{
			if((strpos($image_field,'http://')!==false) || (strpos($image_field,'www.')!==false)  ||
					(strpos($image_field,'https://')!==false)) $result=$image_field;
			else
				$result=Formatter::sth($image_field);
		}
		return $result;
	}

	protected function parse_products(&$incolcount,&$counter,$pbody,$cid,$cols,$colmax,$colwidth,$products_a,$xaction)
	{
		$c=array();
		if(is_array($cid))
		{
			foreach($cid as $k=>$v)
				if(isset($products_a[$v]))
					$c=array_merge($c,$products_a[$v]);
		}
		else if($cid==0)
		{
			foreach($products_a as $k=>$v)
				$c=array_merge($c,$v);
		}
		else
			$c=$products_a[$cid];

		$stand_alone=$cols==0;   //stand alone (not within category)
		$hor=$this->pg_settings['g_fron_hor']!=LAY_VERTICAL;
	//counting columns

		if($stand_alone)
		{
			$cols=$this->page_is_mobile?1:$this->pg_settings['g_listcols'];
			$count=count($c);
			$colmax=intval($count/$cols);
			if($colmax>0)
			{
				if($hor)
					$colmax=$cols;
				elseif($count%$cols>0)
					$colmax++;
			}
			else
				$colmax=1;
			$colwidth=floor(99/$cols);
		}

		$count=count($c);
		$mod=$count % $cols;
		if($mod > 0 && $this->pg_settings['g_fron_hor']==LAY_HORIZONTAL_PLUS)
		{
			$x=(floor($count/$cols)+1)*$cols-$count;
			for($i=0;$i<$x;$i++)
				array_push($c,'*null*');
		}

		$p_list='';
		foreach($c as $p)
		{
			if($incolcount==0 && $counter==0)
				$p_list.='<div class="'.($hor?'front_row':'front_col').'" '.($hor?'style="clear:left;"':'style="float:left;width:'.$colwidth.'%;"').'>';
			if($p=='*null*')
			{
				$temp_pbody='';
				$product='';
			}
			else
			{
				$product=$this->replaceFieldsFinal(true,$p,$pbody,false,$p['pid'],'','',true);
				$plink=$this->build_permalink($p);
				$plink_abs=$this->build_permalink($p,false,'',true);
				$product=str_replace(array('%SHOP_DETAIL%','%SHOP_COUNTER%','%shorturl%','id="table_','id="div_'),
					array($plink,$counter,urlencode($plink_abs),'id="table_'.$p['pid'],'id="div_'.$p['pid']),$product);
				if($this->shop)
					$product=$this->replace_buybuttons(1,$product,$xaction,$p['pid'],$p['cid'],$this->pg_id,$p['stock']);
			}

			$product='<div class="post_content" id="product_'.$p['pid'].'">'.$product.'</div>';
			$incolcount++;
			$counter++;

			if($hor)
			{
				$plus=$this->pg_settings['g_fron_hor']==LAY_HORIZONTAL_PLUS;
				$temp_pbody='<div class="front_item'.($plus?' plus':'').'">'.$product.'</div>';
				if(!$stand_alone && $plus && ($cols>1)&&($incolcount==$colmax))
				{
					$temp_pbody.='</div><div class="'.($hor?'front_row':'front_col').'" '.($hor?'style="clear:left;"':'style="display:inline-block;width:'.$colwidth.'%;"').'>';
					$incolcount=0;
				}
			}
			else
			{
				$temp_pbody='<div rel="'.$counter.'-'.$incolcount.'" class="front_item">'.$product.'</div>';
				if($incolcount==$colmax)
				{
					 $temp_pbody.='</div><div class="front_col" style="display:inline-block;width:'.$colwidth.'%;">';
					 $incolcount=0;
				}
			}

			$temp_pbody=str_replace('%ranking%',$this->rankingVisitsModule->build_ranking($p),$temp_pbody);

			$p_list.=$temp_pbody;
		}

		return $p_list;
	}

	public function parse_cat_list($page_body,$products_a='',$xaction='addandcheckout')
	{
		$page='';
		$showSubcategories=$this->all_settings['subconfront'];
		$cols=$this->page_is_mobile?1:$this->pg_settings['g_listcols'];
		$hor=$this->pg_settings['g_fron_hor']>LAY_VERTICAL;

		$categories_a=array();
		foreach($this->categoriesModule->category_array as $c=>$v)
		{
			if($v['pid']==-1 || $showSubcategories)
				if($v['count']>0 || $this->categoriesModule->get_category_kids_count($c))
					 $categories_a[$c]=$v;
		}

		$count=isset($categories_a)?count($categories_a):0;
		$productCount=0;
		if($products_a!=='')
		{
			foreach($products_a as $ct)
				$productCount+=count($ct);
			$count+=$productCount;
		}

		if($count>0)
		{
			$counter=$incolcount=0;
			$p_macro=strpos($page_body,'<PRODUCTS>')!==false?'<PRODUCTS>':'%PRODUCTS(';
			$p_macroend=$p_macro=='<PRODUCTS>'?'</PRODUCTS>':')%';

			$colmax=intval($count/$cols);

			if($colmax>0)
			{
				if($hor)
					$colmax=$cols;
				elseif($count%$cols>0)
					$colmax++;
			}
			else
				$colmax=1;

			for($i=$count;$i<($cols*$colmax);$i++)
				array_push($categories_a,'*null*');
			$colwidth=floor(99/$cols);

			$page.='<div class="'.($hor?'front_row':'front_col').'" '.($hor?'style="clear:left;"':'style="float:left;width:'.$colwidth.'%;"').'>';

			foreach($categories_a as $c=>$v)
			{
				$p_list='';
				if($v=='')
					continue;

				$counter++;
				if($this->pg_settings['g_fron_hor']!=LAY_HORIZONTAL_PLUS)
					$incolcount++;

				if($v!=='*null*')
				{
					$cv=isset($v['linked'])?$v['linked']:$v;
					$cid=$cv['id'];

					$categoryHtml=str_replace(array('%URL=Detailpage%','%SHOP_DETAIL%'),$this->build_permalink_cat($cid,false),$page_body);
					$cat_fields=array();
					$cat_fields[0]=$cv['name'];
					$cat_fields[2]=$v['count'];
					if($cat_fields[0]=='')
						$cat_fields[0]='&nbsp;&nbsp;&nbsp;&nbsp;';

					$categoryHtml=str_replace(array('%CATEGORY_COUNT%','%LISTER_COUNTER%','%SHOP_COUNTER%','%SHOP_CATEGORY%','%LISTER_CATEGORY%','<div id="tab','id="table_','id="div_')
						,array(trim($cat_fields[2]),$counter,$counter,$cat_fields[0],$cat_fields[0],'<div id="tab'.$counter,'id="table_'.$cid,'id="div_'.$cid),$categoryHtml);
					$categoryHtml=$this->replaceFieldsFinal(false,$cv,$categoryHtml,true,$cid,'','',true);

					$products_section=Formatter::GFS($page_body,$p_macro,$p_macroend);
					if($this->pg_settings['g_fron_hor']!=LAY_VERTICAL)
						$categoryHtml=str_replace(Formatter::GFSAbi($categoryHtml,$p_macro,$p_macroend),'',$categoryHtml);

					if($products_section !='' && is_array($products_a))
					{
						$cid_a=array();
						if(isset($products_a[$cid]))
							$cid_a[]=$cid;
						if(!$showSubcategories && count($v['kids'])>0)
						{
							foreach($v['kids'] as $kv)
								if(isset($products_a[$kv['cid']]))
									$cid_a[]=$kv['cid'];
						}

						$p_list=$this->parse_products($incolcount,$counter,$products_section,$cid_a,$cols,$colmax,$colwidth,$products_a,$xaction);
					}
				}
				else
					$categoryHtml='&nbsp';

				if(strpos($categoryHtml,'ToggleBody') > 0)
					$categoryHtml=Formatter::parseDropdown($categoryHtml,$i);

				if($this->pg_settings['g_fron_hor']!=LAY_VERTICAL)
				{
					$cw=($this->pg_settings['g_fron_hor']==LAY_HORIZONTAL_PLUS)?'100':$colwidth;
					$categoryHtml='<div class="front_item a'.(isset($v['pid']) && $v['pid']>-1?' a2':'').'">'
							  .$categoryHtml.'
							</div>';
				}

				if($this->pg_settings['g_fron_hor']==LAY_VERTICAL)
					$categoryHtml=str_replace(Formatter::GFSAbi($categoryHtml,$p_macro,$p_macroend),$p_list,$categoryHtml);
				else
					$categoryHtml.=$p_list;
				$page.=$categoryHtml;

				if($p_list!=='' && $this->pg_settings['g_fron_hor']==LAY_HORIZONTAL && $incolcount>0)
				{
					while($incolcount % $colmax > 0 )
					{
						$page.='
							<div class="front_item">
								<div class="post_content"></div>
							</div>';
						$incolcount++;
						$counter++;
					}
				}

				if($incolcount==$colmax)
				{
					$page.='</div><div class="'.($hor?'front_row':'front_col').'" '.($hor?'style="clear:left;"':'style="float:left;width:'.$colwidth.'%;"').'>';
					$incolcount=0;
				}
			}

			if($hor)
				$this->page_css.='
.front_item{display:inline-block;box-sizing:border-box;vertical-align:top;width:'.$colwidth.'%;}
.front_item.a{display:inline-block;width:'.$cw.'%;}';

			$page='<div class="front_cont">'.$page.'</div><div class="clear"></div></div>';
		}
		return $page;
	}

	//show categories list (shop main page)
	protected function show_list()
	{
		global $db;

		$this->mode=MODE_LIST;
		$items_a=array();
		$page=$this->get_pagetemplate($this->resolve_template($this->mode),true);

		$p_macro=strpos($page,'<PRODUCTS>')!==false?'<PRODUCTS>':'%PRODUCTS(';
		$p_macroend=$p_macro=='<PRODUCTS>'?'</PRODUCTS>':')%';

		$add_items=strpos($page,$p_macro)!==false;
		$page=Builder::buildLoggedInfo($page,$this->pg_id,$this->rel_path);
		$page_head=Formatter::GFS($page,'','<LISTER_BODY>');
		$page_foot=Formatter::GFS($page,'</LISTER_BODY>','');
		$page_body=Formatter::GFS($page,'<LISTER_BODY>','</LISTER_BODY>');
		if($page_body=='' && strpos($page,'<LISTER_BODY>') !== false)
			$page_body=' ';

		$rand=strpos($page,'<RANDOM>') !== false;
		$cat_search=strpos($page,'%SHOP_CAT_SEARCH%')!==false;
		$products_object='';

		if($page_body!=='' || $rand || $cat_search)
		{
			if($add_items)
			{
				$dt=$db->pre.$this->g_datapre.'data';
				$order_by=$this->get_orderby();
				$products_object=Formatter::GFS($page,$p_macro,$p_macroend);
				$p_fields=array('pid','cid','subcategory');
				if($this->g_use_com)
				{
					$p_fields[]='ranking_count';
					$p_fields[]='ranking_total';
				}
				if($this->use_friendly)
					$p_fields[]='friendlyurl';
				if($this->shop)
				{
					$p_fields[]='price';
					$p_fields[]='stock';
				}
				if($this->g_linked && $this->pg_settings['g_pricefield']!='')
					$p_fields[]=$this->pg_settings['g_pricefield'];
				foreach($this->pg_settings['g_fields_array'] as $k=>$v)
					if(strpos($products_object,'%'.$k.'%')!==false && !in_array($k,$p_fields))
						$p_fields[]=$k;

				$p=$db->fetch_all_array('
					SELECT '.implode(',',$p_fields).'
					FROM '.$dt.'
					WHERE '.$this->g_data_where2.$order_by);
				$p_percategory=array();
				foreach($p as $k=>$v)
				{
					if(!isset($p_percategory[$v['cid']]))
						$p_percategory[$v['cid']]=array();
					$p_percategory[$v['cid']][]=$v;
				}
				$xaction=(strpos($page,'MINI_CART')>0)?'add2':'addandcheckout';
				$xpage=$this->parse_cat_list($page_body,$p_percategory,$xaction);
			}
			else
			{
				$order_by=$this->get_orderby();
				$xpage=$this->parse_cat_list($page_body);
			}

			if($xpage=='')
				$page=str_replace(Formatter::GFSAbi($page,'<LISTER>','</LISTER>'),
								$this->get_setting('empty_db_msg').'<a href="?action=stock">Administrator</a>',
								$page);
			else
			{
				$page=$page_head.$xpage.$page_foot;
				//when <PRODUCTS> outside <LISTER_OBJECT>
				if($products_object!=='' && strpos($page,$products_object)!==false && is_array($p_percategory))
				{
					$incolcount=$counter=0;
					$p_list='<div class="front_cont">'
									.$this->parse_products($incolcount,$counter,$products_object,0,0,0,0,$p_percategory,$xaction).'
									</div>
									<div class="clear"></div>
								</div>';
					$page=str_replace(Formatter::GFSAbi($page,$p_macro,$p_macroend),$p_list,$page);
				}

				$page=$this->parse_minicart($page,0,0);
				$page=$this->build_category_lists(0,$page);

				if(strpos($page,'%SHOP_CART%') !== false)
				{
					$cartstring=$this->cart('show',0,'',0,'','','','','',0,'','');
					$page=str_replace('%SHOP_CART%','<div id="shop_cart">'.$cartstring.'</div>',$page);
				}
				$page=str_replace('%CURRENCY%',$this->pg_settings['g_currency'],$page);
				$this->parse_page_id($page);
				$page=$this->parse_categories($page);

				if($rand)
				{
					$items_a=$db->fetch_all_array('
						SELECT pid, 0 as "used"
						FROM '.$db->pre.$this->g_datapre.'data
						WHERE '.$this->where_public);
					$page=$this->replace_random($page,$items_a);
				}
				$page=str_replace($this->int_tags,'',$page);
			}
		}
		else
			 $page=str_replace(Formatter::GFSAbi($page,'<LISTER_BODY>','</LISTER_BODY>'),'',$page);

		$this->ReplaceMacros($page);
		$page=$this->searchModule->parse_searchmacros($page);
		$page=$this->recentlyViewedModule->parse_recently_viewed($page);

		if(strpos($page,'{%SLIDESHOW_ID(') !== false)
		{
			$slideshow=new Slideshow();
			$slideshow->replaceSlideshowMacro($page,$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
		}

		if(strpos($page,'{%HTML5PLAYER_ID(') !== false)
		{
			$slideshow=new Slideshow();
			$slideshow->replace_html5playerMacro($page,$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
		}

		$this->setState('show_list',array(&$page));
		$page=$this->finalize_output($page);
		return $page;
	}

	public function replace_random($page,$items_a,$item_id=0)
	{
		global $db;

		if($item_id>0)
		{
			foreach($items_a as $k=>$v)
				if($v['pid']==$item_id)
				{
					unset($items_a[$k]);
					break;
				}
		}
		$count=count($items_a);
		if($count>0&&$count<4)
		{
			for($i=$count;$i<5;$i++)
				$items_a[$i]=$items_a[0];
			$count=count($items_a);
		}

		$pid_a=array();
		while (count($items_a)>0 && (strpos($page,'<RANDOM>') !== false))
		{
			$rnd_string=Formatter::GFSAbi($page,'<RANDOM>','</RANDOM>');
			if($rnd_string=='')
				break;
			$rnd_key=array_rand($items_a);
			$p_id=$items_a[$rnd_key]['pid'];
			$pid_a[$p_id]=$rnd_string;
			$page=Formatter::str_replace_once($rnd_string,'<RND>'.$p_id.'</RND>',$page);
			unset($items_a[$rnd_key]);
		}

		$que='';
		foreach($pid_a as $k=>$v)
			$que.='pid='.intval($k).' OR ';
		$que.='pid=0';
		$data=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.$this->g_datapre.'data
			WHERE ('.$que.') AND '.$this->where_public);

		foreach($data as $k=>$v)
		{
			$p_id=$v['pid'];
			$cat_id=$v['cid'];
			$rnd_string=$pid_a[$p_id];
			if($rnd_string=='') break;

			$rnd_string_par=$this->replaceFieldsFinal(true,$v,$rnd_string,false,$p_id,'','',true);
			$this->ReplaceMacros($rnd_string_par);

			if($this->shop)
			{
				$stock=$v['stock'];
				$rnd_string_par=$this->replace_buybuttons(1,$rnd_string_par,'add',$p_id,$cat_id,$this->g_pagenr,$stock);
			}

			$plink=$this->build_permalink($v);
			$plink_abs=$this->build_permalink($v,false,'',true);
			if($this->g_use_com && strpos($rnd_string_par,'%ranking%') !== false)
				 $rnd_string_par=str_replace('%ranking%',$this->rankingVisitsModule->build_ranking($v),$rnd_string_par);
			$rnd_string_par=str_replace(array('%SHOP_DETAIL%','%shorturl%','<RANDOM>','</RANDOM>'),array($plink,urlencode($plink_abs),'',''),$rnd_string_par);
			$page=Formatter::str_replace_once('<RND>'.$p_id.'</RND>',$rnd_string_par,$page);
		}


		if(strpos($page,'<RND>') !== false)
			$page=preg_replace("'<RND>.*?</RND>'si","",$page);
		if(strpos($page,'<RANDOM>') !== false)
			$page=preg_replace("'<RANDOM>.*?</RANDOM>'si","",$page);

		if(strpos($page,'%ORDERBY_COMBO%')!==false)
			$page=$this->parse_orderby($page);
		return $page;
	}

	protected function return_random()
	{
		global $db;

		if(!isset($_SERVER['HTTP_REFERER']))
			Linker::redirect($this->g_abs_path.$this->pg_name,false);
		else
		{
			$items_a=array();
			$path=(isset($_GET['file']))?$_GET['file']:'';
			$path=$path!=''&&((stripos('htm',pathinfo($path, PATHINFO_EXTENSION))!==false)&&(substr_count($path,'.')==1))?$path:'';
			if($path!='')
			{
				if(file_exists($path))
				{
					$rnd_tag=$this->get_pagetemplate($path,true);
					$rnd_tag=Formatter::GFS($rnd_tag,Formatter::GFSAbi($rnd_tag,'<body','>'),'</body>');
					$rnd_tag=str_replace(Formatter::GFSAbi($rnd_tag,'<!--footer-->','<!--/footer-->'),'',$rnd_tag);
				}
				else
					$rnd_tag='';
			}
			elseif($this->tag_object!='')
				$rnd_tag=$this->tag_object->tag;
			else
			{
				$page=$this->get_pagetemplate($this->pg_id.'.html',true);
				if(strpos($page,'<RANDOM>')===false)
					$page=$this->get_pagetemplate($this->pg_settings['g_cid'].".html");
				$rnd_tag=Formatter::GFSAbi($page,'<RANDOM>','</RANDOM>');
				if($rnd_tag=='')
					$rnd_tag='source undefined'.F_BR;
			}

			$rnd_count=(isset($_GET['count']))?intval($_GET['count']):1;
			$root=(isset($_GET['root']))?Formatter::stripTags($_GET['root']):'-1';
			$dir=(isset($_GET['dir']))?Formatter::stripTags($_GET['dir']):'v';

			$items_a=$db->fetch_all_array('
				SELECT pid, 0 as "used"
				FROM '.$db->pre.$this->g_datapre.'data
				WHERE '.$this->where_public);

			$rnd_src='';
			if($dir=='h')
				$rnd_tag='<td class="random_item">'.$rnd_tag.'</td>';
			for($i=0;$i<$rnd_count;$i++)
				$rnd_src.='<RANDOM>'.$rnd_tag.'</RANDOM>';

			$result=$this->replace_random($rnd_src,$items_a);
			$result=str_replace(
					array("\r\n","\n","\r","'","&amp;#","&amp;","&#60;","&lt;","&gt;"),
					array(" "," "," ","\'","&#","&","<","<",">"),$result);
			if($dir=='h')
				$result='<table class="random_body"><tr>'.$result.'</tr></table>';
			if($root=='1')
				$result=str_replace(array('src="','href="'),array('src="'.$this->pg_dir,'href="'.$this->pg_dir),$result);
			elseif($root=='0')
				$result=str_replace(array('href="','src="'),array('href="'.$this->pg_dir,'src="'.$this->pg_dir),$result);
			print "document.write('".$result."');";
		}
	}

	protected function build_categories_combo_user($cat_id,$width_param='147',$parents_only=false)
	{

		$jstring="onchange=\"document.location='".
			str_replace('//-','/',$this->build_permalink_cat(0,false,false,'','&amp;','',''))
			."' + this.options[this.selectedIndex].value;\"";
		$output=$this->categoriesModule->build_category_combo($this->lang_l('all categories'),'Selected_Category',$cat_id,$jstring,' style="width:'.$width_param.'px"',false,3,'name',$this->use_alt_plinks,false,false,false,$parents_only);
		return $output;
	}

	public function build_category_lists($cat_id,$srcpage)
	{
		$parentid=-1;$temp=1;
		$cat_id=stripslashes($cat_id);

		$cat_name=$this->categoriesModule->get_category_name($cat_id,$parentid);

		$combo_param='%CATEGORYCOMBO';
		$srcpage=str_replace(array('%SHOP_CATEGORYCOMBO','%LISTER_CATEGORYCOMBO'),$combo_param,$srcpage);

		if(strpos($srcpage,$combo_param)!==false)
		{
			$result='<select class="input1" name="catlist" onchange="javascript: location.href=this.options[this.selectedIndex].value;return true;">';

			$param=Formatter::GFS($srcpage,$combo_param.'(',')%');
			$selected=$parentid==-1?$cat_name:$this->categoriesModule->get_category_name($parentid,$temp).($this->use_alt_plinks?'/':'&subcat=').$cat_name;
			$srcpage=str_replace(array($combo_param.'('.$param.')%',$combo_param.'%'),$this->build_categories_combo_user($selected,$param),$srcpage);
		}
		if(strpos($srcpage,'%CATEGORYCOMBO_PARENTS%')!==false)
		{
			$result='<select class="input1" name="catlist" onchange="javascript: location.href=this.options[this.selectedIndex].value;return true;">';
			$selected=$parentid==-1?$cat_name:$this->categoriesModule->get_category_name($parentid,$temp);
			$srcpage=str_replace('%CATEGORYCOMBO_PARENTS%',$this->build_categories_combo_user($selected,$param,true),$srcpage);
		}
		if(strpos($srcpage,'%SUBCATEGORIES')!==false)
		{
			$kids_h=$kids_h2=$kids_v=$kids_v2='';
			$kids_a=$kids_a2=array();
			if($cat_id>0)
			{
				$ct=$this->categoriesModule->get_categoryData($cat_id);
				if($ct['pid']!=-1)
					$ct=$this->categoriesModule->get_categoryData($ct['pid']);
				if($ct['pid']!=-1)
				{
					$cat_id=$ct['id'];
					$ct=$this->categoriesModule->get_categoryData($ct['pid']);
				}

				if(count($ct['kids'])>0)
					foreach($ct['kids'] as $k=>$v)
					{
						$temp='';$parentid=-1;
						$kid_name=$this->categoriesModule->get_category_name($v['cid'],$parentid);
						$kids_a[]=$kid_name;
						$temp.='<a class="subcat_list_href'.($kid_name==$cat_name?' active rvts8':' rvts12').'" id="subcat_'.$v['cid'].'" href="'.$this->build_permalink_cat($v['cid'],false).'">'.$kid_name.'</a>';
						$kids_h.=$temp.' ';
						$kids_v.=$temp.F_BR;

						if($v['cid']==$cat_id)
						{
							$ct2=$this->categoriesModule->get_categoryData($v['cid']);
							if(count($ct2['kids'])>0)
							{
								$ct2['kids']=array_reverse($ct2['kids']);
								foreach($ct2['kids'] as $v2)
								{
									$temp='';$parentid2=-1;
									$kid_name=$this->categoriesModule->get_category_name($v2['cid'],$parentid2);
									$kids_a2[]=$kid_name;
									$temp.='<a class="subcat_list_href'.($kid_name==$cat_name?' active rvts8':' rvts12').'" id="subcat_'.$v2['cid'].'" href="'.$this->build_permalink_cat($v2['cid'],false).'">'.$kid_name.'</a>';
									$kids_h2.=$temp.' ';
									$kids_v2.=$temp.F_BR;
								}
							}
						}
					}
			}

			$kids_b=array(''=>$this->lang_l('none'));
			foreach($kids_a as $k=>$v)
				$kids_b[($this->use_alt_plinks? '/':'&amp;subcat=').urlencode($v)]=($v=='')?$this->lang_l('none'):$v;

			$kids_b2=array(''=>$this->lang_l('none'));
			foreach($kids_a2 as $k=>$v)
				$kids_b2[($this->use_alt_plinks? '/':'&amp;subcat=').urlencode($v)]=($v=='')?$this->lang_l('none'):$v;

			$parent=-1;$sname='';
			$cname=$cat_name;
			if($parentid>-1)
			{
				$sname=$cname;
				$cname=$this->categoriesModule->get_category_name($parentid,$parent);
			}

			$srcpage=str_replace(
				array('%SUBCATEGORIES%','%SUBCATEGORIES_VER%','%SUBCATEGORIESCOMBO%'),
				array($kids_h,$kids_v,
				Builder::buildSelect('subc',$kids_b,($this->use_alt_plinks? '/':'&amp;subcat=').$cat_name,
				' onchange="javascript: location.href=\''.
				($this->use_alt_plinks? dirname($this->full_script_path).'/category/'.$cname:$this->g_abs_path.$this->pg_name.'?category='.$cname).'\' + this.options[this.selectedIndex].value'.
				($this->use_alt_plinks?'+ \'/\'':'').';return true;"'
				,'key')),
				$srcpage);

			$srcpage=str_replace(
				array('%SUBCATEGORIES2%','%SUBCATEGORIES_VER2%','%SUBCATEGORIESCOMBO2%'),
				array($kids_h2,$kids_v2,
				Builder::buildSelect('subc2',$kids_b2,($this->use_alt_plinks? '/':'&amp;subcat=').$cat_name,
				' onchange="javascript: location.href=\''.
				($this->use_alt_plinks? dirname($this->full_script_path).'/category/'.$cname:$this->g_abs_path.$this->pg_name.'?category='.$cname).'\' + this.options[this.selectedIndex].value'.
				($this->use_alt_plinks?'+ \'/\'':'').';return true;"'
				,'key')),
				$srcpage);

			if(strpos($srcpage,'%SUBCATEGORIES(')!==false)
			{
				$obj_content_t=Formatter::GFS($srcpage,'%SUBCATEGORIES(',')%');
				$obj_content=Formatter::pTagClearing($obj_content_t);
				$kids=array();
				if(isset($ct['kids']))
					foreach($ct['kids'] as $k=>$v)
						$kids[]=$v['cid'];

				$srcpage=str_replace(
					'%SUBCATEGORIES('.$obj_content_t.')%',
					$this->categoriesModule->category_sidebar(false,'ver',$obj_content,false,'',false,'',false,false,-1,$kids),
					$srcpage);
			}
		}

		if(strpos($srcpage,'%CATEGORY_VLIST')!==false)
		{
			$collaps=strpos($srcpage,'%CATEGORY_VLIST_COLLAPS')!==false;
			$result=$result_full=$this->categoriesModule->category_sidebar(false,'ver','',false,'',false,'',$collaps,false,$cat_id);

			$srcpage=str_replace(array('%CATEGORY_VLIST%','%CATEGORY_VLIST_FULL%','%CATEGORY_VLIST_COLLAPS%','%CATEGORY_VLIST_COLLAPS_TOGGLE%'),
				array($result,$result_full,$result_full,$result_full),$srcpage);
		}
		if(strpos($srcpage,'%CATEGORY_VLIST(')!==false)
		{
			$obj_content_t=Formatter::GFS($srcpage,'%CATEGORY_VLIST(',')%');
			$obj_content=Formatter::pTagClearing($obj_content_t);
			$srcpage=str_replace(
								'%CATEGORY_VLIST('.$obj_content_t.')%',
								$this->categoriesModule->category_sidebar(false,'ver',$obj_content,false,'',false,'',true,false,$cat_id),
								$srcpage);
		}
		if(strpos($srcpage,'%CATEGORY_HLIST%')!==false)
		{
			$result=$this->categoriesModule->category_sidebar(false,'hor','',false,'',false,'',true,false,$cat_id);
			$srcpage=str_replace('%CATEGORY_HLIST%',$result,$srcpage);
		}
		if(strpos($srcpage,'%CATEGORY_HLIST_PARENTS%')!==false)
		{
			$result=$this->categoriesModule->category_sidebar(false,'hor','',false,'',false,'',true,true,$cat_id);
			$srcpage=str_replace('%CATEGORY_HLIST_PARENTS%',$result,$srcpage);
		}

		return $srcpage;
	}

	protected function parse_prevnext($src,$p_id,$stopitem,$count,$prev_a,$next_a)
	{
		$result=$src;

		$pre_url=$this->build_permalink($prev_a);
		$next_url=$this->build_permalink($next_a);

		$prevstring=Formatter::GFSAbi($result,'%SHOP_PREVIOUS','%');
		if($prevstring=="%SHOP_PREVIOUS_URL%")
		{
			if($p_id>1)
				$pre=$pre_url;
			else
			{
				$pre='';
				$temp=substr($result,ListerFunctions::strposReverse($result,'<',strpos($result,$prevstring)));
				if(strpos($temp,'<a')===0)
					 $prevstring='<'.Formatter::GFS($temp,'<',$prevstring).Formatter::GFSAbi($temp,$prevstring,strpos($temp,'<a')!==false?'</a>':'>');
				else
					 $prevstring=Formatter::GFSAbi($temp,'<','>').Formatter::GFSAbi($temp,$prevstring,'>');

				if($this->f->buttonhtml!='')
				{
					$btn_pre=str_replace('%BUTTON%',$prevstring,$this->f->buttonhtml);
					$result=str_replace($btn_pre,'',$result);
				}
			}
		}
		else
			$pre=($p_id>1)?sprintf('<a class="rvts4" href="%s">%s</a>',$pre_url,$this->nav_labels['prev']):'';

		$result=str_replace($prevstring,$pre,$result);

		$nextstring=Formatter::GFSAbi($result,'%SHOP_NEXT','%');
		if($nextstring=="%SHOP_NEXT_URL%")
		{
			if($stopitem<$count)
				$nex=$next_url;
			else
			{
				$nex='';

				$temp=substr($result,ListerFunctions::strposReverse($result,'<',strpos($result,$nextstring)));
				if(strpos($temp,'<a')===0)
					$nextstring='<'.Formatter::GFS($temp,'<',$nextstring).Formatter::GFSAbi($temp,$nextstring,strpos($temp,'<a')!==false?'</a>':'>');
				else
					$nextstring=Formatter::GFSAbi($temp,'<','>').Formatter::GFSAbi($temp,$nextstring,'>');

				if($this->f->buttonhtml!='')
				{
					$btn_nex=str_replace('%BUTTON%',$nextstring,$this->f->buttonhtml);
					$result=str_replace($btn_nex,'',$result);
				}

			}
		}
		else
			$nex=($stopitem<$count)?sprintf('<a class="rvts4" href="%s">%s</a>',$next_url,$this->nav_labels['next']):'';

		$result=str_replace(
			array($nextstring,'%next_name%','%prev_name%','%SHOP_NEXT_URL%','%SHOP_PREVIOUS_URL%'),
			array($nex,$next_a['name'],$prev_a['name'],'',''),$result);
		return $result;
	}

	protected function get_xviewpage($page) //jpc
	{
		$page2=File::read(strtolower($this->g_cat).'.html');
		if($page2!='')
		{
			$page2_body=Formatter::GFSAbi($page2,'<!--page-->','<!--/page-->');
			$page_body=Formatter::GFSAbi($page,'<!--page-->','<!--/page-->');
			$page=str_replace($page2_body,$page_body,$page2);
		}
		return $page;
	}

	public function category_table(&$counter,$data,$page_body,$ps_key,&$product_select)
	{
		global $db;

		$rating_array=array();
		if(strpos($page_body,'%rating%')!==false)
		{
			$pids=array();
			foreach($data as $v)
				$pids[]=$v['pid'];

			$rating_array=$db->fetch_all_array('
					SELECT entry_id,sum(rating) as sumrating,count(*) as votes
					FROM '.$db->pre.$this->g_datapre.'comments
					WHERE entry_id 	IN ('.implode(',',$pids).') AND approved=1 AND rating > 0
					GROUP BY entry_id',false,'entry_id');
		}

		$cols=$this->page_is_mobile || $this->livesearch?1:$this->pg_settings['g_catcols'];

		if(isset($_REQUEST['columns']))
		{
			$cols=intval($_REQUEST['columns']);
			setcookie('product_list_columns',$cols,time()+60*60*24,'/');
		}
		elseif(isset($_COOKIE['product_list_columns']))
			$cols=intval($_COOKIE['product_list_columns']);

		$useT=$this->internal || $this->pg_settings['g_catcolsauto']?0:1;

		$table='<div id="products_list" class="blog_container columns'.$cols.'">';
		if($cols>1)
		{
			$table.='<'.($useT?'table':'div').' class="category_body">';
			$incol=0;
		}
		$product_select=$first_url='';

		if($this->g_use_rel)
		{
			$rel_a=array();
			foreach($data as $k=>$pdata)
				$rel_a[]='rel='.$pdata['pid'];
			if(count($rel_a)>0)
				$rel_r=$db->fetch_all_array('
					SELECT *
					FROM '.$db->pre.$this->g_datapre.'data
					WHERE '.implode(' OR ',$rel_a).' AND publish=1');

			$dr=strpos($page_body,'<VARIATIONS>')!==false;

			if(isset($rel_r) && !$dr)
				$page_body.='<VARIATIONS>
				%SCALE[%image1%,50,0,url,%SHOP_DETAIL%,left]%
				</VARIATIONS>';
			elseif(!isset($rel_r) && $dr)
				$page_body=str_replace(Formatter::GFS($page_body,'<VARIATIONS>','</VARIATIONS>'),'',$page_body);
		}

		foreach($data as $k=>$pdata)
		{
			$p_id=$pdata['pid'];
			if($cols>1)
			{
				if($incol==0 && $useT)
					$table.='<tr>';
				$table.='<'.($useT?'td':'div').' class="category_item" id="product_ct_'.$p_id.'"'.(isset($pdata['filtered'])?' style="display:none"':'').'>';
			}
			else
				$table.='<div class="category_item" id="product_ct_'.$p_id.'">';

			$fvalues_a=array_values($pdata);

			$cat_body=$page_body;

			if($this->action_id=='wishlist' && strpos($cat_body,'<REMOVE_FROM_WISHLIST>')==false)
				$cat_body='<REMOVE_FROM_WISHLIST><a class="rvts4 wishRremove" href="%URL%">x</a></REMOVE_FROM_WISHLIST>'.$cat_body;

			if(isset($pdata['code'])) //image-line
				$this->g_code[]=$pdata['code'];

			if($this->g_use_rel && isset($rel_r))
			{
				$old_rel=Formatter::GFS($cat_body,'<VARIATIONS>','</VARIATIONS>');
				$new_rel='';
				$recline_rel=array();
				foreach($rel_r as $rv) if($rv['rel']==$p_id)
					$recline_rel[]=$rv;
				if(count($recline_rel)>0)
				{
					foreach($recline_rel as $k=>$v)
					{
						$data=$v;
						$data['pid']=$p_id;
						$plink=$this->build_permalink($data,false,'',true);
						$curl=$this->build_permalink_cat($data['cid'],false);
						$pline=str_replace(array('%SHOP_DETAIL%','%permalink%','%caturl%'),
								  $plink.($v['rel']>0?($this->use_alt_plinks?'?':'&').'rel='.$v['pid']:''),$old_rel,$curl);
						$temp=$this->replaceFieldsFinal(true,$v,$pline,false,$v['pid'],'','',true);
						if(strpos($temp,'<ADD_TO_WISHLIST>')!==false)
							$this->wishlistModule->replace_WishlistButtons($temp,$v['pid'],true);
						if($this->shop)
							$temp=$this->replace_buybuttons(0,$temp,'add',$v['pid'],$v['cid'],$this->g_pagenr,$v['stock']);
						$new_rel.=$temp;
					}

					$cat_body=str_replace($old_rel,'<div class="variations">'.$new_rel.'<div class="clear"></div></div>',$cat_body);
				}
				else
					$cat_body=str_replace('<VARIATIONS>'.$old_rel.'</VARIATIONS>','',$cat_body);
			}

			$x_catid=$pdata['cid'];
			$stock=isset($pdata['stock'])?intval($pdata['stock']):1;
			$cat_body=$this->ReplaceFieldsFromData(true,$cat_body,false,$p_id,$pdata,true);

			if(strpos($cat_body,'ToggleBody')>0)
				$cat_body=Formatter::parseDropdown($cat_body,$counter);		 //support for drop-down tables

			$cat_string=$this->build_permalink($pdata);

			if($ps_key!='')
			{
				 $fnames_a=array_keys($pdata);
				 $name_key=array_search($ps_key,$fnames_a);
				 $product_select.='<option value="'.$cat_string.'">'.$fvalues_a[$name_key].'</option>';
			}

			$plink=$this->build_permalink($pdata);
			$plink_abs=$this->build_permalink($pdata,false,'',true);
			$curl=$this->build_permalink_cat($pdata['cid'],false);
			$temp=str_replace(
					array('%SHOP_DETAIL%','%shorturl%','%permalink%','%caturl%'),
					array($plink,urlencode($plink_abs),$plink_abs,$curl),
					$cat_body);
			if($first_url=='')
				$first_url=$plink;

			$xaction=(strpos($temp,'<FROMCART>')>0)?'addandbasket':'add';
			if($this->shop)
				$temp=$this->replace_buybuttons(1,$temp,$xaction,$p_id,$x_catid,$this->g_pagenr,$stock);

			$temp=str_replace(array('%LISTER_COUNTER%','%SHOP_COUNTER%','%COUNTER%'),$counter,$temp);
			$counter++;
			$temp=str_replace(array('<div id="t','id="table_','id="div_'),
				array('<div id="t'.$counter,'id="table_'.$counter,'id="div_'.$counter),$temp);

			if($this->g_use_com)
				$temp=str_replace('%ranking%',$this->rankingVisitsModule->build_ranking($pdata),$temp);

			$votes=isset($rating_array[$p_id])?$rating_array[$p_id]['votes']:0;
			$cnt=isset($rating_array[$p_id])?$rating_array[$p_id]['sumrating']:0;
			$temp=str_replace('%rating%',Builder::buildRating($votes,($cnt>0? round($cnt/$votes,1): 0),$p_id),$temp);
			if(strpos($temp,'<REMOVE_FROM_WISHLIST>')!==false)
				$this->wishlistModule->replace_WishlistButtons($temp,$p_id);
			if(strpos($temp,'<ADD_TO_WISHLIST>')!==false)
				$this->wishlistModule->replace_WishlistButtons($temp,$p_id,true);
			$table.='<div class="post_content">'.$temp.'</div>';
			if($cols>1)
			{
				$table.=$useT?'</td>':'</div>';
				$incol++;
				if($incol==$cols)
				{
					if($useT)
						$table.='</tr>';
					$incol=0;
				}
			}
			else
				$table.='</div>';
		}
		if($cols>1)
		{
			if($incol>0)
			{
				for($i=$incol;$i<$cols;$i++)
					$table.='<'.($useT?'td':'div').' class="category_item"></'.($useT?'td>':'div>');
				if($useT)
				$table.='</tr>';
			}
			$table.=$useT?'</table>':'</div>';
		}
		$table.='</div>';
		$table=str_replace('%CURRENCY%',$this->pg_settings['g_currency'],$table);

		if(isset($_REQUEST['columns']))
		{
			echo $table;
			exit;
		}

		if(!$useT)
		{
			$colwidth=floor(99/$cols);
			$this->page_css.='.category_item{position:relative;display: inline-block;vertical-align: top;'.($this->pg_settings['g_catcolsauto']?'':'width:'.$colwidth.'%;').'}';
		}
		else
			$this->page_css.='.category_item{position:relative;}';
		if($this->action_id=='wishlist')
			$this->page_css.='.wishRremove{position:absolute;right:8px;top:8px;z-index:1000;}';
		return $table;
	}

	protected function replace_category_header(&$page,$cdata,$cat_id)
	{
		$category_section=Formatter::GFSAbi($page,'<CATEGORY_HEADER>','</CATEGORY_HEADER>');

		$cdata['cname']=$cdata['name'];
		if($category_section !== '')
		{
			$new_category_section=Formatter::GFS($category_section,'<CATEGORY_HEADER>','</CATEGORY_HEADER>');
			$new_category_section=$this->replaceFieldsFinal(false,$cdata,$new_category_section,false,$cat_id-1,'','',true);
			$page=str_replace($category_section,$new_category_section,$page);
		}
	}

	protected function show_category($cat_id)
	{
		global $db;

		$this->mode=MODE_CATEGORY;

		if(isset($_REQUEST['catpgmax']))
		{
			 $this->pg_settings['g_catpgmax']=intval($_REQUEST['catpgmax']);
			 setcookie('product_list_catpgmax',$this->pg_settings['g_catpgmax'],time()+60*60*24,'/');
		}
		elseif(isset($_COOKIE['product_list_catpgmax']))
			 $this->pg_settings['g_catpgmax']=intval($_COOKIE['product_list_catpgmax']);

		if(isset($_REQUEST['page'])&&$_REQUEST['page']=='all')
			$this->pg_settings['g_catpgmax']=0;

		$pLimit=intval($this->pg_settings['g_catpgmax']);
		$ajax=$this->action_id=='filterbyAjax' || $this->action_id=='catpgmaxAjax';

		$ajaxFast=$ajax && $this->internal;
		if($this->internal && !$ajaxFast)
			$this->filtersModule->cleanFilters();

		$parent=0;
		$cname=$this->categoriesModule->get_category_name($cat_id,$parent);
		if($cname=='all' || $cat_id===false)
			$cat_id='all';

		$nav_params='';
		$page=$this->get_pagetemplate($this->resolve_template($this->mode));
		if(isset($_REQUEST['xview']))
			$page=$this->get_xviewpage($page);

		$page=Builder::buildLoggedInfo($page,$this->pg_id,$this->rel_path);
		$rand=strpos($page,'<RANDOM>') !== false;

		$category_section2=Formatter::GFSAbi($page,'<CATEGORIES_BODY>','</CATEGORIES_BODY>');
		if($category_section2!='')
			$page=str_replace($category_section2,$this->parse_cat_list($category_section2),$page);
		$orderby=$this->get_orderby();
		$item_id=isset($_REQUEST['iid'])?intval($_REQUEST['iid']):0;

		if($cat_id===false)
		{
			if($item_id>0 || (isset($_REQUEST['category']) && $_REQUEST['category']=='all'))
				$cat_id='all';
		}
		elseif($cat_id!='all' && intval($cat_id)!=$cat_id)
			$cat_id=$this->get_lister_categoryID($cat_id,'1');

		if($this->tag_object!=null || $cat_id=='all' || $cat_id===false)
		{
			$category_section=Formatter::GFSAbi($page,'<CATEGORY_HEADER>','</CATEGORY_HEADER>');
			$page=str_replace($category_section,'',$page);
		}

		$fdata=array();
		$count=0;
		if($cat_id===false)
		{
			$data=array();
			$empty=true;
			$page=$this->filtersModule->replace_filters($page,$cat_id,$this->where_public,$fdata);
		}
		else
		{
			$cat_name='all';
			if($cat_id!='all')
			{
				list($kids_count,$kids_ids)=$this->categoriesModule->get_category_kids_count($cat_id,true);
				$kids_ids=implode(',',array_merge(array($cat_id),$kids_ids));
				$cat_que=$kids_ids!=''?' d.cid IN ('.$kids_ids.') AND ':'';
				$cat_name=$this->categoriesModule->category_array[$cat_id]['name'];
			}
			else
				$cat_que='';

			if($this->tag_object!=null)
				$where=$this->tag_object->where();
			else
				$where=$cat_que.' '.$this->where_public;

			$where_all=($this->get_setting('var_filters'))?str_replace(' AND (rel=0) ','',$where):$where;
			$page=$this->filtersModule->replace_filters($page,$cat_id,$where_all,$fdata);

			$filters=$this->filtersModule->get_filterQue();
			$filtered=$filters!='';
			$whereUnfilterd=$where;
			$where.=$filters;
			if($this->get_setting('var_filters') && $filtered)
			{
				$where_sub= str_replace(array('rel=0',' d.cid'),array('rel>0','cid'),$where);
				$where=' ( '.$where.' OR ( d.pid IN (SELECT rel FROM '.$db->pre.$this->g_datapre.'data WHERE '.$where_sub.')))';
			}

			$count=$db->query_singlevalue('
				SELECT COUNT(*)
				FROM '.$db->pre.$this->g_datapre.'data d
				WHERE '.$where.' '.$orderby);
			if($pLimit<1)
				$pLimit=$count;

			$start=($this->g_pagenr-1)*$pLimit;
			if($start<0)
				$start=0;

			$search_page=($item_id>0 && $pLimit<$count);
			$limit=$search_page?'':' LIMIT '.$start.', '.$pLimit;

			if($this->internal) //image-line site
			{
				$data=$db->fetch_all_array('
				SELECT pid
				FROM '.$db->pre.$this->g_datapre.'data as d
				WHERE '.$where,false,'pid');
				if(!$ajax)
				{
					 $dataFiltered=$data;
					 $data=$db->fetch_all_array('
					 SELECT d.*,c.description AS c_description,c.description2 AS c_description2,c.cid AS c_cid,c.cname AS c_cname,c.image1 AS c_image1
					 FROM '.$db->pre.$this->g_datapre.'data AS d, '.$db->pre.$this->g_datapre.'categories AS c
					 WHERE (d.cid=c.cid )  AND '.$whereUnfilterd.' '.$orderby);
					 foreach($data as $k=>$v)
						  if(!array_key_exists($v['pid'],$dataFiltered))
								$data[$k]['filtered']=1;
				}
			}
			else
				$data=$db->fetch_all_array('
				SELECT d.*,c.description AS c_description,c.description2 AS c_description2,c.cid AS c_cid,c.cname AS c_cname,c.image1 AS c_image1,c1.cname as c_parent
				FROM '.$db->pre.$this->g_datapre.'data AS d
				LEFT JOIN '.$db->pre.$this->g_datapre.'categories AS c ON d.cid=c.cid
				LEFT JOIN '.$db->pre.$this->g_datapre.'categories AS c1 ON c1.cid=c.parentid AND c.parentid>-1'
				.($this->action_id=='wishlist'?' RIGHT JOIN '.$db->pre.$this->g_datapre.'wishlist AS w ON w.uid=\''.$this->wishlistModule->user_id.
				'\' AND FIND_IN_SET(CONCAT(\'"\', d.pid, \'"\'),w.wishlist) > 0':'').
				' WHERE '.($this->action_id=='wishlist'?str_replace(' AND (rel=0) ','',$where):$where).' GROUP BY d.id '.$orderby.$limit);
			if($this->tag_object!=null)
				$this->tag_object->check_tag($data);

			if($search_page)
			{
				$inpage=0;
				$hit=false;
				$new_data=array();
				foreach($data as $v)
				{
					$new_data[]=$v;
					$inpage++;
					if($v['pid']==$item_id)
						$hit=true;
					if($inpage==$pLimit)
					{
					 	if($hit)
							break;
					 	else
							$new_data=array();
					}
				}
				$data=$new_data;
			}
			$empty=empty($data[0]);
		}

		$this->global_pagescr=$first_url='';

		$page_head=Formatter::GFS($page,'','<LISTER_BODY>');
		$page_foot=Formatter::GFS($page,'</LISTER_BODY>','');
		$page_body=Formatter::GFS($page,'<LISTER_BODY>','</LISTER_BODY>');
		$page=$page_head;

		$page=str_replace('%LISTER_PRODUCTS','%SHOP_PRODUCTS',$page);
		$ps_key=Formatter::GFS($page,'%SHOP_PRODUCTS(%','%)%');
		$product_select='';

		if($this->tag_object!=null)
			$pl=$this->tag_object->build_permalink();
		else
			$pl=$this->build_permalink_cat($cat_id,false);

		if($this->use_alt_plinks)
			$pl=str_replace('/-/','/',$pl);
		$nav_params=Formatter::GFS($page_head.$page_foot.$page_body,'%NAVIGATION(',')%');

		$nav=Navigation::page($count,
					$pl,
					$pLimit,
					$this->g_pagenr,
					$this->pg_settings['lang_u']['of'],
					'nav',
					$this->nav_labels,
					($this->use_alt_plinks?'':'&amp;'),
					'',
					$this->use_alt_plinks,
					false,
					'',
					false,
					$nav_params);

		$start=($this->g_pagenr*$pLimit)-$pLimit;
		$end=($this->g_pagenr*$pLimit);
		$end=($end>$count)?$count:$end;
		$counter=$start+1;

		if($ajaxFast)
		{
			$pids=array();
			foreach($data as $pr)
				$pids[]=$pr['pid'];

			 echo json_encode(array('products'=>$pids,'nav'=>$nav,'data'=>$fdata));
			 exit;
		}

		if($empty)
			$res='<div id="products_list">'.$this->lang_l('no products found').'</div>';
		else
			$res=$this->category_table($counter,$data,$page_body,$ps_key,$product_select);

		$this->setState('show_category',array(&$res));


		if($ajax)
		{
			 echo json_encode(array('page'=>$res,'nav'=>$nav,'data'=>$fdata));
			 exit;
		}

		$page.=$res;

		if($cat_id=='all' || $cat_id===false)
			$cdata=array('name'=>'','description'=>'','description2'=>'','ccolor'=>'');
		else
		{
			$cdata=$this->categoriesModule->get_categoryData($cat_id);
			if(isset($cdata['linked']))
				$cdata=$cdata['linked'];
		}

		$this->replace_category_header($page,$cdata,$cat_id);

	//parsing category meta
		if($cat_id!='all')
		{
			$this->categoriesModule->parse_category_meta($page,$cat_id);

			$old_keyw=Formatter::GFSAbi($page,'<meta name="keywords" content="','>');
			$new_keyw=str_replace(Formatter::GFSAbi($old_keyw,'content="','"'),'content="'.Formatter::stripTags($cdata['name']).'"',$old_keyw);
			$page=str_replace($old_keyw,$new_keyw,$page);
		}
	//end meta

		if($ps_key!='')
		{
			$product_select='
				 <select class="input1 prolist" name="prolist" onchange="javascript: location.href=this.options[this.selectedIndex].value;return true;">'
					  .$product_select.'
				 </select>';
			$page=str_replace('%SHOP_PRODUCTS(%'.$ps_key.'%)%',$product_select,$page);
		}

		$page.=$page_foot;
		$page=str_replace(array('%NAVIGATION%','%NAVIGATION('.$nav_params.')%'),'<span class="page_nav">'.$nav.'</span>',$page);
		$page=$this->build_category_lists($cat_id,$page);

		if(strpos($page,'%ORDERBY_COMBO%')!==false)
			$page=$this->parse_orderby($page);

		if(strpos($page,'%SHOP_CART%')!==false)
		{
			$cartstring=$this->cart('show',$cat_id,'',$this->g_pagenr,'','','','','',0,'','');
			$page=str_replace(array('%SHOP_CART%'),'<div id="shop_cart">'.$cartstring.'</div>',$page);
		}
		$page=$this->parse_minicart($page,$cat_id,$this->g_pagenr);

		if($this->global_pagescr !== '')
			$page=str_replace('<!--scripts-->','<!--scripts-->'.$this->global_pagescr,$page);
		$page=str_replace('%CURRENCY%',$this->pg_settings['g_currency'],$page);
		$page=$this->parse_categories($page);
		$page.='<!--<pagelink>/'.$this->build_permalink_cat($cat_id,false).'</pagelink>-->';
		$page=str_replace($this->int_tags,'',$page);
		$this->parse_page_id($page);

		$sname='';
		$parent=-1;
		$cname=$cdata['name'];
		$curl=$csurl=$this->build_permalink_cat($cat_id,false);

		if(isset($cdata['pid']) && $cdata['pid']>-1)
		{
			$sname=$cname;
			$cname=$this->categoriesModule->get_category_name($cdata['pid'],$parent);
			$curl=$this->build_permalink_cat($cdata['pid'],false);
			$cparams='category='.$cname.'&subcat='.$cdata['name'];
		}
		else
			$cparams=($cdata['name']!=''?'category='.$cdata['name']:'');

		$cparams.=($this->tag_object!=null?'&'.$this->tag_object->build_short_url():'');

		if(strpos($page,'%PRODUCTSONPAGE(')!==false)
		{
			 $macro=Formatter::GFSAbi($page,'%PRODUCTSONPAGE(',')%');
			 $params=Formatter::GFS($macro,'%PRODUCTSONPAGE(',')%');
			 $html=Builder::buildSelect('productsOnPage',explode(',',$params),$pLimit,'','value','onchange="updatecatpgmax(this)"');

			 $page=str_replace($macro,$html,$page);

				$this->page_scripts.='
			function updatecatpgmax(t){
				$.getJSON("'.$this->pg_name.'?action=catpgmaxAjax"+"&catpgmax="+$(t).val() + "&'.$cparams.'",function(d){
				console.log(d);
					 $("#products_list").replaceWith(d["page"]);
					 if(d["page"].indexOf(\'class="mbox\')!=-1)
						  $("#products_list .mbox").multibox({heff:false});
					 $("#products_list .ranking").ranking({rsw:55});
					if(typeof updateOnAjax == "function") {
						updateOnAjax(1);
					 }
					 $(".page_nav").html(d["nav"]);
		   })
			}
		  ';
		}

		$page=str_replace(
			array('%FIRST_PRODUCT_URL%','%ITEMSONPAGE%','%PAGE%','<FILTERS>','</FILTERS>','rel="ca_'.$cdata['name'].'"',
				 '%category%','%subcategory%','%cparams%','%COMPARE%'),
			array($first_url,$counter,$this->g_pagenr,'','','id="sa"',
				 $cname,$sname,$cparams,$this->featuresModule->compare_list()),
			$page);
		$page=$this->searchModule->parse_searchmacros($page);
		$page=$this->recentlyViewedModule->parse_recently_viewed($page);
		if(strpos($page,'<MULTI_BUY_BUTTON>')!==false)
			$this->replace_MultiBuyButton($page);
		$page=$this->finalize_output($page);
		//do not add page scripts after this
		$this->ReplaceMacros($page);

		if($rand)
		{
			$items_a=$db->fetch_all_array('
				SELECT pid, 0 as "used"
				FROM '.$db->pre.$this->g_datapre.'data
				WHERE '.$this->where_public);
			$page=$this->replace_random($page,$items_a);
		}

		if(strpos($cdata['description'],'{%SLIDESHOW_ID(') !== false)
		{
			$slideshow=new Slideshow();
			$slideshow->replaceSlideshowMacro($cdata['description'],$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
		}
		if(strpos($cdata['description'],'{%HTML5PLAYER_ID(') !== false)
		{
			$slideshow=new Slideshow();
			$slideshow->replace_html5playerMacro($cdata['description'],$this->rel_path,$this->page_scripts,$this->page_css,$this->page_dependencies);
		}

		$page=str_replace(
			array('%description%','%description2%','%cname%','%caturl%','%subcaturl%','%permalink%'),
			array($cdata['description'],$cdata['description2'],$cdata['name'],$curl,$csurl,$csurl),$page);

		return $page;
	}

	public function cart($flag,$cat_id,$item_id,$page_id,$searchstring,$item_options,$userdata,$item_count,$pt,$bundle_arr=array())
	{
		$result='';
		if($this->shop)
		{
	 		if($this->session_on==false)
		  	{
				Session::intStart();
				$this->session_on=true;
			}

		  $this->basket=new basket();
		  if(Session::isSessionSet("basket".$this->pg_id))
		  {
				$this->basket->fill_from_session(
					Session::getVar('basketcat'.$this->pg_id),
					Session::getVar('basketid'.$this->pg_id),
					Session::getVar('basketamount'.$this->pg_id),
					unserialize(Session::getVar('basketoptions'.$this->pg_id)),
					Session::getVar('basketuserdata'.$this->pg_id),
					array(
						'bas_bundle_id'=>Session::getVar('basketbundle_id'.$this->pg_id),
						'bas_bundle_primaryId'=>Session::getVar('basketbundle_primaryId'.$this->pg_id),
						'bas_bundle_discount'=>Session::getVar('basketbundle_discount'.$this->pg_id),
						'bas_bundle_name'=>Session::getVar('basketbundle_name'.$this->pg_id),
						'bas_bundle_count'=>Session::getVar('basketbundle_count'.$this->pg_id)
					)				
				);
				$this->session_transaction_id=Session::getVar('transaction_id'.$this->pg_id);
				$this->couponsModule->set_session_coupon(Session::getVar('coupon'.$this->pg_id));
				$this->session_total=Session::getVar('total'.$this->pg_id);
				if(Session::isSessionSet('session_country'.$this->pg_id))
					 $this->pg_settings['g_check_country']=Session::getVar('session_country'.$this->pg_id);
				if(Session::isSessionSet('session_shipping_list'.$this->pg_id))
					$this->pg_settings['g_check_shipping_list']=Session::getVar('session_shipping_list'.$this->pg_id);
				if($this->pg_settings['g_tax_handling'])
				{
					 $this->session_vat_1=Session::isSessionSet('vat1'.$this->pg_id)?Session::getVar('vat1'.$this->pg_id):0;
					 $this->session_vat_2=Session::isSessionSet('vat2'.$this->pg_id)?Session::getVar('vat2'.$this->pg_id):0;
					 $this->session_vat_c=Session::isSessionSet('vat_c'.$this->pg_id)?Session::getVar('vat_c'.$this->pg_id):0;
					 $this->session_vat_as=Session::isSessionSet('vat_as'.$this->pg_id)?Session::getVar('vat_as'.$this->pg_id):0;
				}
		  }
		  else
				Session::setVar('transaction_id'.$this->pg_id,0);
		}

		if($flag=='pay')
			$result=$this->process_order();
		elseif($flag=='return_ok')
			$result=$this->return_ok($pt);
		elseif($flag=='download')
			$result=$this->return_file();
		elseif($flag=='checkout')
			$result=$this->checkout();
		elseif($flag=='delete')
			$this->basket->delete_cart();
		elseif($flag=='remove'||$flag=='removeajax')
		{
			$this->basket->delete_item($item_id,$item_options,$userdata,$bundle_arr,!empty($bundle_arr)&&isset($bundle_arr['bundle_id'])?$this->GetBundleLine($bundle_arr['bundle_id']):array());

			$this->couponsModule->clean_coupon();
		}
		elseif($flag=='update' || $flag=='updateajax')
		{
			if($item_count==0)
			{
				$this->basket->delete_item($item_id,$item_options,$userdata,$bundle_arr,!empty($bundle_arr)&&isset($bundle_arr['bundle_id'])?$this->GetBundleLine($bundle_arr['bundle_id']):array());

				$this->couponsModule->clean_coupon();
			}
			else
			{
				if($this->basket->update_item_count($item_id,$item_options,$userdata,$item_count,$bundle_arr,!empty($bundle_arr)&&isset($bundle_arr['bundle_id'])?$this->GetBundleLine($bundle_arr['bundle_id']):array()))
					$this->couponsModule->clean_coupon();
			}
			$this->stockModule->set_stock_session($item_id,$item_count);
		}
		elseif($flag=='add' || $flag=='add2' || $flag=='addajax' || $flag=='multibuyajax')
		{
			 $this->basket->add_item($this->GetRecordLine($item_id),$cat_id,$item_id,$item_count,$item_options,$userdata,$bundle_arr,!empty($bundle_arr)&&isset($bundle_arr['bundle_id'])?$this->GetBundleLine($bundle_arr['bundle_id']):array());
			 $this->stockModule->update_stock_session($item_id,$item_count);
		}
		elseif($flag=='minicart')
			$result=$this->show_minicart($cat_id,$page_id,$item_options,$searchstring);
		elseif($flag=='addandbasket' || $flag=='addandbasket2')
		{
			$this->basket->add_item($this->GetRecordLine($item_id),$cat_id,$item_id,1,$item_options,$userdata,$bundle_arr,!empty($bundle_arr)&&isset($bundle_arr['bundle_id'])?$this->GetBundleLine($bundle_arr['bundle_id']):array());
			$this->stockModule->update_stock_session($item_id,1);
			if($flag=='addandbasket')
				$result=$this->show_cart(true,-1,$page_id,'basket',$searchstring);
		}
		elseif($flag=='addandcheckout')
		{
			$this->basket->add_item($this->GetRecordLine($item_id),$cat_id,$item_id,$item_count,$item_options,$userdata,$bundle_arr,!empty($bundle_arr)&&isset($bundle_arr['bundle_id'])?$this->GetBundleLine($bundle_arr['bundle_id']):array());
			$this->stockModule->update_stock_session($item_id,$item_count);
		}
		elseif(($flag=='show')||($flag=='show_final'))
			$result=$this->show_cart(true,$cat_id,$page_id,$flag,$searchstring);
		elseif($flag=='basket')
			$result=$this->show_cart(false,$cat_id,$page_id,$flag,$searchstring);
		elseif($flag=='item' || $flag=='comment' || $flag=='ranking')
			$result=$this->show_item($item_id);

		if($this->shop)
		{
	 		Session::setVar('basket'.$this->pg_id,$this->basket);
			Session::setVar('basketcat'.$this->pg_id,$this->basket->bas_catid);
			Session::setVar('basketid'.$this->pg_id,$this->basket->bas_itemid);
			Session::setVar('basketamount'.$this->pg_id,$this->basket->bas_amount);
			Session::setVar('basketoptions'.$this->pg_id,serialize($this->basket->bas_options));
			Session::setVar('basketuserdata'.$this->pg_id,$this->basket->bas_userdata);
			Session::setVar('transaction_id'.$this->pg_id,$this->session_transaction_id);
			Session::setVar('coupon'.$this->pg_id,$this->couponsModule->get_session_coupon());
			Session::setVar('total'.$this->pg_id,$this->session_total);
			Session::setVar('session_country'.$this->pg_id,$this->pg_settings['g_check_country']);
			Session::setVar('session_shipping_list'.$this->pg_id,$this->pg_settings['g_check_shipping_list']);

			Session::setVar('basketbundle_id'.$this->pg_id,$this->basket->bas_bundle_id);
			Session::setVar('basketbundle_primaryId'.$this->pg_id,$this->basket->bas_bundle_primaryId);
			Session::setVar('basketbundle_discount'.$this->pg_id,$this->basket->bas_bundle_discount);
			Session::setVar('basketbundle_name'.$this->pg_id,$this->basket->bas_bundle_name);
			Session::setVar('basketbundle_count'.$this->pg_id,$this->basket->bas_bundle_count);
			if($this->pg_settings['g_tax_handling'])
				 $this->set_vatSession($this->session_vat_1,$this->session_vat_2,$this->session_vat_c,$this->session_vat_as);
		}

		return $result;
	}

	public function get_fields()
	{
		$vars=($_SERVER['REQUEST_METHOD']=='POST')?$_POST:$_GET;
		foreach($vars as $k=>$v)
		{
			$vars[$k]=Formatter::stripTags(trim($v));
			$test=strtolower(urldecode($vars[$k]));
			if(strpos($test,'mime-version') !== false || strpos($test,'content-type:') !== false)
				die("Why ??");
		}
		return $vars;
	}

	public function get_shop_from()
	{
		$result=$this->pg_settings['from_email'];
		if(strpos($result,';') !== false)
			$result=Formatter::GFS($result,'',';');
		if(strpos($result,'<') === false && $this->pg_settings['g_shop_name']!=='')
			$result='"'.$this->pg_settings['g_shop_name'].'" <'.$result.'>';
		return $result;
	}

	public function GetPaymentType($line)
	{
		$payment='';
		foreach($this->pg_settings['g_callback_str'] as $ind=>$val)
		{
			if(isset($val['SHOP_RET_ORDERID'])&&(strpos($line,'|'.$val['SHOP_RET_ORDERID'].'=')!==false))
			{
				$payment=$ind;
				break;
			}
	 	}
		return $payment;
	}

	public function field_displayname($k)
	{
		$k_tran=$this->pg_settings['g_fields_array'][$k]['display'];
		$k_tran=$this->lang_l(strtolower($k_tran));
		return $k_tran;
	}

	public function get_nextid()
	{
		global $db;

		$record=$db->query_first('
			SELECT MAX(pid) AS maxpid
			FROM '.$db->pre.$this->g_datapre.'data');
		$pid=$record['maxpid']+1;
		return $pid;
	}

	public function get_nextcid()
	{
		global $db;

		$record=$db->query_first('
			SELECT MAX(cid) AS maxcid
			FROM '.$db->pre.$this->g_datapre.'categories');
		$cid=$record['maxcid']+1;
		return $cid;
	}

	public function duplicate_product($pid)
	{
		global $db;

		$data=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->g_datapre.'data
			WHERE pid='.intval($pid));
		$data['name'].=' ('.$this->lang_l('duplicate').')';
		$data['pid']=$this->get_nextid();
		if($this->shop && $data['vat']=='')
			$data['vat']=0;
		unset($data['id']);

		if(isset($data['visits_count']))
			unset($data['visits_count']);
		if(isset($data['ranking_count']))
			unset($data['ranking_count']);
		if(isset($data['ranking_total']))
			unset($data['ranking_total']);
		if(isset($data['salescount']))
			unset($data['salescount']);
		if(isset($data['created']))
			unset($data['created']);
		if(isset($data['updated']))
			unset($data['updated']);
		if(isset($data['stock']))
			unset($data['stock']);
		$db->query_insert($this->pg_settings['g_data'].'_data',$data);
		if($this->get_setting('features'))
		  $this->featuresModule->duplicate_product_pptions($pid,$data['pid'],$data['cid']);

		return $data['pid'];
	}

	protected function return_order($id,$pending)
	{
		global $db;

		$field=($pending)?'form_fields':'orderline';
		$data=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->pg_pre.($pending?'pending_orders':'orders').'
			WHERE orderid='.intval($id));
		$result=(is_array($data))?$data[$field]:'';
		return $result;
	}

	public function pdf_enabled()
	{
		return ($this->get_setting('use_pdf')=='1') && ini_get('allow_url_fopen')!='off';
	}

	public function build_status($confirmed,$id)
	{
		return '<div class="status_'.$id.' rvts8"'.(!$confirmed?' style="background:red;padding:2px;color:white;font-weight:bold"':'').'>'.
				  $this->lang_l($confirmed?'confirmed':'unconfirmed').
				  '</div>';
	}

	public function get_invoice_status($status)
	{
		$opt=explode('#',$this->get_setting('status_options'));
		return $status!=''?$status:$opt[0];
	}

	public function update_stock_fromsession($ordid)
	{
		global $db;
		$items=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.$this->pg_pre.'orderlines
			WHERE ol_orderid='.$ordid);
		foreach($items as $v)
			if($v['ol_pid']!='1000000')
				$this->stockModule->update_stock($v['ol_pid'],$v['ol_amount']);

		$data=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->pg_pre.'pending_orders
			WHERE orderid='.intval($ordid));

		if($data['coupon']!='')
			$this->couponsModule->update_coupon_used();
		if($this->serialsModule->enabled())
			$this->serialsModule->update_serials_used($items);
	}

	public function build_admin_menu($caption) //used from importer
	{
		 $dummy= new lister_admin_screens($this);
		 $this->action_id='settings';
		 return $dummy->build_menu($caption);
	}

	public function move_confirm($order_id,$resend,$redirect=true,$manual=false)
	{
		global $db;

		$data=$this->get_pending_order($order_id,false);
		$ex_check=is_array($data);

		if($ex_check)
		{
			$email=isset($_REQUEST['em']) && $_REQUEST['em'];
			$uid=$data['userid'];
			$payment_date=$this->format_dateSql($data['pdate'],'long','auto');

			if($data['invoicenr']==null)
				 $data['invoicenr']=$this->InvoiceModule->assign_invoicenumber($order_id);

			if($data['coupon']!='')
			{
				$ca=explode('|',$data['coupon']);
				$ca=floatval($ca[1]);
			}
			$order_email=Formatter::GFS($data['form_fields'],$this->pg_settings['g_check_email'].'=','|');
			$name=Formatter::GFS($data['form_fields'],'last_name=','|');

			$items=$db->fetch_all_array('
				SELECT *
				FROM '.$db->pre.$this->pg_pre.'orderlines
				WHERE ol_orderid='.$order_id);
			$price_total=0.00;

			foreach($items as $v)
			{
	   		$pid=$v['ol_pid'];
				$price=str_replace(',','',$v['ol_price']);
				$quantity=$v['ol_amount'];
				if($pid=='1000000')
					$price_total+=$price;
				else
				{
					$price_total+=($quantity*$price);
					$this->stockModule->update_stock($pid,$quantity);
				}
			}

			if($this->serialsModule->enabled())
				$this->serialsModule->update_serials_used($items);

			if($data['coupon']!='')
			{
				$this->couponsModule->update_coupon_used();
				$price_total-=$ca;
			}

			$status='moved';
			if($email && $order_email) //sending confirmation e-mail
			{
				$bwmess=str_replace(array('%SHOP_ORDER_ID%','%ORDER_ID%','%SHOP_ORDER_DATE%','%invoice_status%'),
						  array($order_id,$order_id,$payment_date,$this->get_invoice_status($data['invoice_status'])),
						  $this->get_setting('bwmess'));
				$bwsubject=str_replace(array('%SHOP_ORDER_ID%','%ORDER_ID%','%SHOP_ORDER_DATE%'),
						  array($order_id,$order_id,$payment_date),
						  $this->get_setting('bwsubject'));
				$cr=crypt($order_id,'jhsjdhj');
				$bwmess=strpos($bwmess,'<br')===false?str_replace(array("\r\n","\n"),'<br />',$bwmess):$bwmess;
				$parsed_return=$this->parse_returnpage('paypal',$order_id,$cr,true,true,$bwmess);
				$_send_from=$this->get_shop_from();
	//handling pdf
				$pdf=$this->pdf_enabled()?$this->InvoiceModule->generate_invoice($order_id):'';
				$result=$this->send_mail($parsed_return,$bwsubject,$_send_from,$order_email,$pdf,$this->all_settings['use_attachments']);

				if($result)
					$status='moved+email'.$cr;
			}

			if($resend)
			{
				$data_ord=$db->query_first('
					 SELECT *
					 FROM '.$db->pre.$this->pg_pre.'orders
					 WHERE orderid='.intval($order_id));
				$temp=Formatter::GFSAbi($data_ord['orderline'],'|payment_status=','|');
				$data=array();
				$data['orderline']=str_replace($temp,'|payment_status='.$status.'|',$data_ord['orderline']);
				$db->query_update($this->pg_id."_orders",$data,'orderid='.$order_id);

				echo $this->build_status(true,$order_id);
				Linker::redirect($this->g_abs_path.$this->pg_name."?action=orders",false);
				exit;
			}

			$parsed_fields='custom='.$order_id.'|payment_date='.$payment_date.'|mc_gross='.$price_total.'|mc_currency='.$this->pg_settings['g_currency'].'|payment_status='.$status.'|address_name='.$name.'|receiver_email='.$order_email.'|';

			$dt=array();
			$dt['orderid']=$order_id;
			$dt['orderline']=$parsed_fields;
			$dt['userid']=$uid;
			$dt['invoicenr']=$data['invoicenr'];
			$db->query_insert($this->pg_id.'_orders',$dt,false,true);
		}
		if($redirect)
			echo $this->build_status(true,$order_id);
	}

	public function delete_order($id,$pending=0)
	{
		global $db;

		$id=intval($id);
		if($id>0)
		{
		  if($pending)
				$db->query('DELETE FROM '.$db->pre.$this->pg_pre.'pending_orders WHERE orderid='.$id);
		  $db->query('DELETE FROM '.$db->pre.$this->pg_pre.'orders WHERE orderid='.$id);
		  $db->query('DELETE FROM '.$db->pre.$this->pg_pre.'orderlines WHERE ol_orderid='.$id);
		  $db->query('DELETE FROM '.$db->pre.$this->pg_pre.'bundles_orderlines WHERE ol_orderid='.$id);
		}
		$this->setState('delete_order');

		Linker::checkReturnURL();
	}

	//show user pendign and completed orders
	protected function show_user_orders()
	{
		global $db;

		Session::intStart();
		$user_data=$this->user->mGetLoggedUser($db,'',true);
		if($user_data===false)
		{
			Linker::redirect($this->rel_path.'documents/centraladmin.php?pageid='.$this->pg_id,false);
			exit;
		}

		$mng_orders = new mng_pendingorders_screen($this);
		$hideTpl = isset($_REQUEST['hidetpl']);
		$mng_orders->handle_screen($hideTpl,$this->user->getId());

		$this->setState('show_user_orders');
	}

	public function show_order_details($id,$admin)
	{
		global $db;
		if($admin==false)
		{
			Session::intStart();
			$temp_id=Formatter::GFS($id,'','_');
			if(Formatter::GFS($id,'_','')==crypt($temp_id,'jhjshdjhj98') )
				$id=$temp_id;
			elseif(!$this->user->userCookie() && !Cookie::isAdmin())
				return;
		}

		$data=$this->get_pending_order($id,true);
		$order_date=$data[0]['pdate'];
		$order_fields=$data[0]['form_fields'];
		$vat1=$data[0]['vat1'];
		$this->couponsModule->set_session_coupon($data[0]['coupon']);
		$downloadsAllowed=1;

		$payment_type=strtolower(Formatter::GFS($data[0]['form_fields'],'ec_PaymentMethod=','|'));

		if($this->paymentModule!==false && isset($this->pg_settings['g_checkout_callback_on'][$payment_type]) && $this->pg_settings['g_checkout_callback_on'][$payment_type]=='TRUE')
			$downloadsAllowed=$this->paymentModule->downloadAllowed($data[0]);

		$result='';
		if($this->get_setting('use_myo'))
		{
			$page=$this->get_setting('my_order');
			$page='<LISTER>'.ListerFunctions::replace_lister_tags($page).'</LISTER>';
			$page=str_replace(
					 array('%SHOP_ORDER_ID%','%SHOP_ORDER_DATE%','<div><LISTER_BODY></div>','<div></LISTER_BODY></div>','<div>[/FOOTER]</div>','<div>[FOOTER]</div>'),
					 array('%ORDER_ID%','%ORDER_DATE%','<LISTER_BODY>','</LISTER_BODY>','[/FOOTER]','[FOOTER]'),
					 $page);
		}
		else
		{
			$page=$this->get_pagetemplate(($this->pg_id+4).".html");
			$page=str_replace(
					 array('%CATEGORY_HLIST%','%CATEGORY_VLIST%','%CATEGORY_VLIST_FULL%','%CATEGORY_VLIST_COLLAPS%',
							'%CATEGORY_VLIST_COLLAPS_TOGGLE%',Formatter::GFSAbi($page,'<MINI_CART>','</MINI_CART>'),
							Formatter::GFSAbi($page,'<QUANTITY>','</QUANTITY>')),
					 '',$page);
		}

		$page_head=Formatter::GFS($page,'<LISTER>','<LISTER_BODY>');
		$page_foot=Formatter::GFS($page,'</LISTER_BODY>','</LISTER>');
		$page_body=str_replace(
						  array('%SHOP_ORDER_ITEM_AMOUNT%','%SHOP_ORDER_ITEM_NAME%','%SHOP_ORDER_ITEM_COUNT%',
										'%shorturl%','%SHOP_CARTCURRENCY%','%SHOP_CARTPRICE%','%SHOP_DELETE_BUTTON%',
									 Formatter::GFS($page_body,'<SHOP_DELETE_BUTTON>','</SHOP_DELETE_BUTTON>'),Formatter::GFS($page_body,'<a href="">','</a>')),
						  array('%CARTPRICE%','%name%','%QUANTITY%','%SHOP_DETAIL%','%CURRENCY%','%CARTPRICE%','','',''),
						  Formatter::GFS($page,'<LISTER_BODY>','</LISTER_BODY>'));
		$vat_total=$shop_shipping=$price_total=0.00;
		$bcounter=$itemcounter=$shop_shipping=0;
		$products_array=array();
		$count=count($data);
		$bundle_primary_line=$bundle_description='';
		$this_bundle_count=$bundle_price=$bundle_vatval=$bundle_linetotal=0;
		foreach($data as $k=>$v)
		{
			$item_id=$v['ol_pid'];
			$item_options=array();
			$price=str_replace(',','',$v['ol_price']);
			$quantity=$v['ol_amount'];
			foreach($this->optionsModule->options as $k=>$opt)
			  if(isset($v['ol_'.$opt]))
					$item_options[$k]=$v['ol_'.$opt];

			$userdata=$v['ol_userdata'];

			if($item_id=='1000000')
				$shop_shipping+=$price;
			else
			{
				$is_bundle=isset($v['ol_bundle_id'])&&$v['ol_bundle_id']!=0&&$v['ol_bundle_name']!=''&&$v['ol_bundle_primary']!=0&&$v['ol_bundle_discount']!=''&&$v['ol_bundle_count']!=0;
				if($is_bundle)
				{
					$bundle_id=$v['ol_bundle_id'];
					$bundle_discount=$v['ol_bundle_discount'];
					$bundle_primary=$v['ol_bundle_primary'];
					$bundle_name=$v['ol_bundle_name'];
					$bundle_count=$v['ol_bundle_count'];
					$this_bundle_count++;
					$is_primary=$bundle_primary==$v['pid'];
					$bundle_description.=$v['name'].($this_bundle_count==$bundle_count?'':' + ');
					$itemline=$is_primary?$page_body:'';
					if($is_primary)
					{
						$v['name']=$bundle_name;
						$itemline=str_replace(array('%code%','%category%','%parent_category%'),'',$itemline);
						if($image_pos=strpos($itemline,'%SCALE[')!==false)
						{
							$m=Formatter::GFSAbi($itemline,'%SCALE[',']%');
							$params_a=explode(',',Formatter::GFS($itemline,'%SCALE[',']%'));
							$cp=count($params_a);
							if($cp>2)
							{
								$h=($params_a[2]=='')?0:intval($params_a[2]);
								$w=($params_a[1]=='')?0:intval($params_a[1]);
								if($h>0&&$w>0&&$bundle_count>1)
								{
									$child_images_str='';
									$ch_w=round($w/2.5);
									$ch_h=round($h/2.5);
									for($j=2;$j<=$bundle_count;$j++)
										$child_images_str.='%CHILD_SCALE'.$j.'[%ch_image1%,'.$ch_w.','.$ch_h.']%';
									if($child_images_str!='')
										$itemline=str_replace($m,$m.$child_images_str,$itemline);
								}
							}
						}
					}
					else
					{
						$val_ch=$v['image1'];
						if($this->all_settings['watermark'])
							$val_ch=$this->rel_path.'documents/utils.php?w='.Crypt::encrypt(($this->rel_path==''?'../':'').$val_ch.'|'.$this->get_setting('watermark_position')).'&i='.basename($val_ch);
						if(strpos($bundle_primary_line,'%CHILD_SCALE'.$this_bundle_count.'[')!==false)
						{
							$bundle_primary_line=str_replace('%CHILD_SCALE'.$this_bundle_count.'[','%SCALE[',$bundle_primary_line);
							Images::parseScale(array(),$bundle_primary_line,'ch_image1',false,true,$val_ch);
						}
					}
				}
				else
				{
					$bundle_primary_line=$bundle_description='';
					$this_bundle_count=$bundle_price=$bundle_vatval=$bundle_linetotal=0;
					$itemline=$page_body;
				}
				$quantity_display = $this->all_settings['allow_float_amount']?$this->nrf($this->float_val($quantity)):intval($quantity);
				$itemline=str_replace(
						  array('%QUANTITY%','%SHOP_DETAIL%','%CURRENCY%'),
						  array($quantity_display,$this->build_permalink($v,false,$item_id,'',''),$this->pg_settings['g_currency']),$itemline);

				if($this->serialsModule->enabled())
				{
					if(strpos($itemline,'%SERIAL%')===false)
						$itemline=str_replace('%name%','%name% %SERIAL%',$itemline);
					$itemline=str_replace('%SERIAL%',$is_bundle?'':$v['ol_serial'],$itemline);
				}
				$itemline=$this->replaceFieldsFinal(true,$v,$itemline,false,$item_id,$item_options,$userdata,false);

				$itemvat=($this->pg_settings['g_vatfield'] != '')?(floatval($v[$this->pg_settings['g_vatfield']])):(0.00);

				$products_array[]=array(
					'ol_orderid'=>'',
					'ol_pid'=>$item_id,
					'ol_amount'=>$quantity,
					'ol_price'=>$price,
					'ol_vat'=>$itemvat,
					'ol_shipping'=>'',
					'ol_userdata'=>'');

				foreach($item_options as $oi=>$option)
					$products_array['ol_option'.$oi]=$option;

				$vat_val=0;
				if($itemvat>0)
				{
					if($this->pg_settings['g_excmode'])
						$vat_val=round((($quantity*$price)*($itemvat/100)),$this->pg_settings['g_price_decimals']);
					else
						$vat_val=(($quantity*$price)*($itemvat)) / ($itemvat+100);
					$vat_total+=$vat_val;
				}

				$temp=Formatter::GFSAbi($itemline,'%SHOP_ITEM_DOWNLOAD_LINK(',')%');
				if($downloadsAllowed && $v['download']!='')
				{
					 $dlink_caption=Formatter::GFS($itemline,'%SHOP_ITEM_DOWNLOAD_LINK(',')%');
					 $dlink_string='%SHOP_ITEM_DOWNLOAD_LINK('.$dlink_caption.')%';
					 $pa_id='';
					 $d_url=$this->full_script_path.'?action=download&id='.$v['id'].'&trid='.$id.$pa_id;
					 $item_string=sprintf('<a class="rvts4" href="%s">%s</a>',$d_url,$this->pg_settings['lang_u']['download']);
					 $itemline=str_replace($dlink_string,$is_bundle?'':$item_string,$itemline);
					 $itemline=str_replace('%SHOP_ITEM_DOWNLOAD_URL%',$is_bundle?'':$d_url,$itemline);
					 setcookie(sha1($id.'_'.$this->pg_id),sha1($id),time()+$this->pg_settings['g_downloadexpire']*86400);
				}
				else
					 $itemline=str_replace($temp,$this->lang_l('download not allowed'),$itemline);

				if($is_bundle)
				{
					$bundle_vatval+=$this->nrf2($vat_val);
					$bundle_price+=$this->nrf2($price);
					$bundle_linetotal+=$this->nrf2($quantity*$price);
					$bundle_primary_line=$is_primary?$itemline:$bundle_primary_line.$itemline;
					if($bundle_count==$this_bundle_count)
					{
						$bundle_primary_line=str_replace(
						array('%CARTPRICE%','%LINETOTAL%','%LINEVATTOTAL%','%SHOP_ORDER_ITEM_SUBNAME%','%SHOP_ORDER_ITEM_CATEGORY%','%bundle_description%'),
							array($bundle_price,$bundle_linetotal,$bundle_vatval,'','',$bundle_description),
						$bundle_primary_line);

						$result.=$bundle_primary_line;
						$bundle_primary_line=$bundle_description='';
						$this_bundle_count=$bundle_price=$bundle_vatval=$bundle_linetotal=0;
					}
				}
				else
				{
					$itemline=str_replace(array('%CARTPRICE%','%LINETOTAL%','%LINEVATTOTAL%','%SHOP_ORDER_ITEM_SUBNAME%','%SHOP_ORDER_ITEM_CATEGORY%','%bundle_description%'),
						array($this->nrf($price),$this->nrf($quantity*$price),$this->nrf($vat_val),$this->optionsModule->selectedOptions($v),$v['category'],''),$itemline);
					$result.=$itemline;
				}
				$itemcounter+=$quantity;
				$price_total+=($quantity*$price);
				$bcounter++;
			}
		}

		$result=$page_head.$result.$page_foot;

		if($this->pg_settings['g_excmode']&&$this->pg_settings['g_tax_handling'])
			$vat_total=floatval($vat1);

		$result=$this->parse_totals($result,$price_total,$vat_total,$shop_shipping,$count,$itemcounter,
												$this->pg_settings['g_excmode']&&$this->pg_settings['g_tax_handling']&&$vat_total>0,
												$products_array);

		$this->parse_vatdetail($result,$shop_shipping,$products_array);
		$this->ReplaceMacros($result);

	//footer section
		if(strpos($result,'[FOOTER]')!==false)
			$foot=Formatter::GFS($result,'[FOOTER]','[/FOOTER]');
		else
		{
			$foot='<br>
				<table width="100%"><tr valign="top">
				<td width="50%">
					<p><b>'.$this->lang_l('order data').'</b></p>
					<p>'.$this->lang_l('order').': %ORDER_ID%</p>
					<p>'.$this->lang_l('order date').': %ORDER_DATE%</p>
				</td>
				<td>
					<p><b>'.$this->lang_l('customer').'</b></p>
					%USER_DATA%
				</td>
				</tr></table>';
			$result.='<div>[FOOTER][/FOOTER]</div>';
		}

		$order_fields=explode('|',$order_fields);
		$user_data='';
		foreach($order_fields as $k=>$v)
		{
			$fid=trim(Formatter::GFS($v,'','='));
			if(!in_array($fid,array('REQUEST_SEND','cc','')))
				$user_data.='<p>%'.$fid.'%</p>';
		}

		$foot=str_replace('%USER_DATA%',$user_data,$foot);
		$result=str_replace(Formatter::GFSAbi($result,'[FOOTER]','[/FOOTER]'),$foot,$result);
		foreach($order_fields as $k=>$v)
		{
			$fid=trim(Formatter::GFS($v,'','='));
			$val=trim(Formatter::GFS($v,'=',''));
			$val=$fid=='payment'?$this->lang_l($val):$val;
			$flabel=isset($this->pg_settings['field_labels'][$fid])?$this->pg_settings['field_labels'][$fid]:$fid;
			$flabel=$this->lang_l($flabel);
			$result=str_replace(
					  array('%'.$fid.'%','#'.$fid.'#'),
					  array($flabel.'='.$val,$val),$result);
		}
		$userId=$data[0]['userid']==NULL?$this->lang_l('guest'):$data[0]['userid'];

		$userId=$this->lang_l('guest');
		if($data[0]['userid']!=NULL)
		{
			$userId=$data[0]['userid'];
			$avatar=user::getAvatar($userId,$db,$this->site_base);
		}
		$result=str_replace(
				array('ec_PaymentMethod','%ORDER_DATE%','%ORDER_ID%','%SHOP_NAME%','%user_id%','%user:avatar%'),
				array($this->lang_l('payment'),$this->format_dateSql($order_date,'long','auto'),$id,
						$this->pg_settings['g_shop_name'],$userId,$avatar),
				$result);
	//end footer
		if($admin)
		{
			if($this->g_callback2)
				 $result.='<p>go to <a class="rvts4" href="'.$this->g_abs_path.$this->pg_name.'?action=orders">'.$this-lang_l('orders').'</a></p>';
			//$result.='</div>';
		}
		$this->parse_page_id($result);
		$result=Builder::includeScript('$(document).ready(function(){$(".e_button,.w_button,.art-button,input.rover").hide();});',$result);
		print '<div>'.$result.'</div>';
		exit;
	}

	public function get_orderid_fromdata($df)
	{
		$payment=$this->GetPaymentType($df);
		$fx_id=(($payment=='')?'custom':$this->pg_settings['g_callback_str'][$payment]['SHOP_RET_ORDERID']);
		$res=0;
		if(strpos($df,$fx_id.'=') == 1)
			$res=Formatter::GFS($df,$fx_id.'=','|');
		elseif(strpos($df,$fx_id.'=') !== false)
			$res=Formatter::GFS($df,$fx_id.'=','_');
		return $res;
	}

	public function return_cancel_file($errors,$forceError=false)
	{
		$result=$this->get_pagetemplate(($this->pg_id+1).".html");
		if(strpos($result,'%ERRORS%')===false || $forceError)
			$result=str_replace(Formatter::GFS($result,'<!--page-->',"<!--/page-->"),'%ERRORS%',$result);
		$result=str_replace('%ERRORS%',$errors,$result);
		$result=$this->build_category_lists(0,$result);
		return $result;
	}


	protected function unreg_session()
	{
		Session::intStart();
		$this->session_on=true;
		Session::unsetVar("basket".$this->pg_id);
		session_write_close();
	}

	public function prepare_editor($id,$editor_html)
	{
		$editor_parsed=str_replace(array('oEdit1','450px','htmlarea'),array('oEdit1_'.$id,'250px','txtContent_'.$id),$editor_html);
		$mes=$this->get_setting($id);
		$mes=strpos($mes,'<br')===false?str_replace(F_LF,'<br />',$mes):$mes;
		$mes=str_replace(array('<SHOP_BODY>','</SHOP_BODY>','<VAT>','</VAT>'),array('[SHOP_BODY]','[/SHOP_BODY]','[VAT]','[/VAT]'),$mes);
		$entry='<textarea class="mceEditor" id="txtContent_'.$id.'" name="'.$id.'" style="width:100%" rows="4" cols="30">'.$mes.'</textarea>'.$editor_parsed;
		return $entry;
	}

	public function convert_editor_macros($src)
	{
		return str_replace(array('[SHOP_BODY]','[/SHOP_BODY]','[VAT]','[/VAT]'),
						array('<SHOP_BODY>','</SHOP_BODY>','<VAT>','</VAT>'),$src);
	}

	protected function convert_orderlines()
	{
		global $db;

		$data=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.$this->pg_pre.'orderlines',true);
		if(count($data)==0)
		{
			$order_lines=$db->fetch_all_array('
				SELECT orderid,items
				FROM '.$db->pre.$this->pg_pre.'pending_orders');
			foreach($order_lines as $v)
			{
				$line=$v['items'];
				$count=count(explode('><',$line));
				for($i=1;$i<($count+1);$i++)
				{
					$item=Formatter::GFS($line,'<'.$i.'>','</'.$i.'>');
					if($item!='')
					{
						$items=explode('|',$item);
						$pid=isset($items[0])?$items[0]:'0';
						$data=array();
						$data['ol_orderid']=$v['orderid'];
						$data['ol_pid']=$pid;
						$data['ol_amount']=isset($items[2])?$items[2]:1;
						$data['ol_price']=isset($items[3])?$this->nrf2($items[3],true):0;
						$data['ol_vat']=isset($items[5])?$items[5]:0;
						if($pid!='1000000')
						{
							$data['ol_option1']=isset($items[4])?$items[4]:'';
							$data['ol_shipping']=isset($items[6])?$items[6]:0;
							$data['ol_option2']=isset($items[7])?$items[7]:'';
							$data['ol_option3']=isset($items[8])?$items[8]:'';
							$data['ol_userdata']=isset($items[9])?$items[9]:'';
							foreach($this->optionsModule->options as $k=>$opt)
								if($k>3)
									$data['ol_'.$opt]=isset($items[$k+6])?$items[$k+6]:'';

						}
						else
							$data['ol_shipping']=0;
						$db->query_insert($this->pg_id."_orderlines",$data);
					}
				}
			}
	 }
	}

	protected function check_data()
	{
		global $db;

		$update_id='update_uid';
		$update_id_val=4;
		$this->db_fetch_settings();
		$this->internal=isset($this->all_settings['use_divs']);
		$update=intval($this->get_setting($update_id,'0'))<$update_id_val;

		$result=false;
		$tb_a=$db->get_tables($this->pg_id.'_');
		if(empty($tb_a))
		{
			include_once('data.php');
			Search::checkDB($db);
			$result=create_lister_db($db,$this->pg_id,$this->pg_settings['g_fields_array'],$this->shop,$db->pre.$this->pg_pre,false);
			$this->db_insert_settings(array('rel'=>'1','com'=>'1','com'=>'1',
			'WISHLIST_FIELDS_COUNT'=>WISHLIST_FIELDS_COUNT,
			'BUNDLES_FIELDS_COUNT'=>BUNDLES_FIELDS_COUNT,
			'BUNDLES_ORDERLINES_FIELDS_COUNT'=>BUNDLES_ORDERLINES_FIELDS_COUNT,
			'CATEGORY_FIELDS_COUNT'=>CATEGORY_FIELDS_COUNT));
		}
		elseif($update)
		{
			include_once('data.php');
			create_lister_db($db,$this->pg_id,$this->pg_settings['g_fields_array'],$this->shop,$db->pre.$this->pg_pre,true);
			$this->convert_orderlines();
			$this->db_insert_settings(array($update_id=>$update_id_val));
		}

		if(!$result && 
			( !isset($this->all_settings['WISHLIST_FIELDS_COUNT']) || $this->all_settings['WISHLIST_FIELDS_COUNT']!=WISHLIST_FIELDS_COUNT ||
			!isset($this->all_settings['BUNDLES_FIELDS_COUNT']) || $this->all_settings['BUNDLES_FIELDS_COUNT']!=BUNDLES_FIELDS_COUNT ||
			!isset($this->all_settings['CATEGORY_FIELDS_COUNT']) || $this->all_settings['CATEGORY_FIELDS_COUNT']!=CATEGORY_FIELDS_COUNT ||
			!isset($this->all_settings['BUNDLES_ORDERLINES_FIELDS_COUNT']) || $this->all_settings['BUNDLES_ORDERLINES_FIELDS_COUNT']!=BUNDLES_ORDERLINES_FIELDS_COUNT )
		)
		{
			include_once('data.php');
			create_lister_db($db,$this->pg_id,$this->pg_settings['g_fields_array'],$this->shop,$db->pre.$this->pg_pre,true);
			$this->db_insert_settings(
				array(
				'WISHLIST_FIELDS_COUNT'=>WISHLIST_FIELDS_COUNT,
				'BUNDLES_FIELDS_COUNT'=>BUNDLES_FIELDS_COUNT,
				'BUNDLES_ORDERLINES_FIELDS_COUNT'=>BUNDLES_ORDERLINES_FIELDS_COUNT,
				'CATEGORY_FIELDS_COUNT'=>CATEGORY_FIELDS_COUNT)
			);
		}
		$this->categoriesModule->build_categories_list(false,$this->g_linkedid);
	}

//$pp indicates if this is check of partly protected (only specific modules of the page are protected)
	public function ca_check($pp=false)
	{
		$page_info=CA::getPageParams($this->pg_id,$this->rel_path);
		$protection=Validator::checkProtection($page_info);
		$this->protected=$protection==2;
		if($protection !== false && ($protection == 2 || ($pp && $protection == 3)))
		{
			if($this->action_id=='rss' && $this->get_setting('public_rss')) return;
			Session::intStart();
			if($this->user->userCookie())  $user_account=User::getUser($this->user->getUserCookie(),$this->rel_path);
			if(!isset($user_account) || User::mHasReadAccess($user_account,$page_info)==false)
			{
				if(!Cookie::isAdmin() || Session::isSessionSet('HTTP_USER_AGENT') && (Session::getVarStr('HTTP_USER_AGENT')!=md5($_SERVER['HTTP_USER_AGENT'])) )
				{Linker::redirect($this->rel_path.'documents/centraladmin.php?pageid='.$this->pg_id.'&r=1',false);exit;}
			}
		}
	}

	public function db_count($table,$where='')
	{
		global $db;

		$count_raw=$db->fetch_all_array('
			SELECT COUNT(*)
			FROM '.($table!='site_search_index'? $db->pre.$this->pg_pre:$db->pre).$table.($where!=''? '
			WHERE '.$where:''));
		$count=isset($count_raw[0])?$count_raw[0]['COUNT(*)']:0;
		return $count;
	}
	public function db_fetch_entry($entry_id,$where='')
	{
		global $db;

		$records=$db->query_first('
			SELECT *
			FROM '.$db->pre.$this->g_datapre.'data
			WHERE pid='.intval($entry_id).($where!=''?$where:''));
		return $records;//false if not found
	}
	public function reindex_search($entry_id,$flag='add')
	{
		global $db;

		$hidden_fields=array('blackbar');
		$fields_to_index=$this->searchModule->get_searchable_fields();

		$data=$this->db_fetch_entry($entry_id);
		if($data===false || $data['publish']!=1)
			$flag='del';

		if($flag=='add')
		{
			$content='';
			foreach($fields_to_index as $fname)
				if(isset($data[$fname]) && !in_array($fname,$hidden_fields))
					$content.=$data[$fname].' ';

			$p_params=CA::getPageParams($this->pg_id,$this->rel_path);
			$p_lang=array_search($p_params[16],$this->f->site_languages_a)+1;
			Search::reindexDBAdd($db,$this->pg_id,$data['pid'],$p_lang,
					  $this->build_permalink($data,false,'',true),
					  $data['name'],$content,$data['updated'],$data['keywords'],$data['cid'],$this->page_type);
		}
		elseif($flag=='del')
			Search::reindexDBDel($db,'page_id='.$this->pg_id.' AND '.($entry_id>0? 'entry_id="'.$entry_id.'"': 'entry_id <> ""'));
	}

	public function build_permalink($data,$for_bmarks=false,$action='',$abs_url=false,$view='')
	{
		$pid=$data['pid'];
		$cid=$data['cid'];
		$name=isset($data['name'])?$data['name']:'';
		$name_for_url=Formatter::titleForLink(urlencode($name));

		if($this->get_setting('plink_type')=='category_name_short')
		{
			 $pl_format='%category%/%name%/';
			 $name_for_url=urlencode($name);
		}
		elseif($this->get_setting('plink_type')=='product_id')
			 $pl_format='product/%iid%/';
		else
			 $pl_format='product/%iid%/%category%/%subcat%/%name%/';

		$parent=-1;
		$sname='';
		$cname=$this->categoriesModule->get_category_name($cid,$parent);
		if($parent>-1)
		{
			$sname=$cname;
			$cname=$this->categoriesModule->get_category_name($parent,$parent);
		}
		$cname_for_url=urlencode($cname);
		$sname_for_url=urlencode($sname);

		if($this->use_alt_plinks)
		{
			if($this->use_friendly && !empty($data['friendlyurl']))
				$pl_format=urlencode($data['friendlyurl']).'/';

			$result='/'.str_replace(
				array('%name%','%iid%','%category%','%subcat%/'),
				array($name_for_url,$pid,$cname_for_url,($sname!=''?$sname_for_url.'/':'')),$pl_format);

			if(!$for_bmarks)
				$result=dirname($this->full_script_path).$result;
		}
		else
		{
			$result='?iid='.$pid.'&amp;category='.$cname_for_url
			.'&amp;action=item'.($sname!=''?'&amp;subcat='.$sname_for_url:'')
			.'&amp;title='.$name_for_url;
			if(!$for_bmarks)
				$result=($abs_url?$this->full_script_path:$this->g_abs_path.$this->pg_name).$result;
		}
		return $result;
	}

	public function build_permalink_cat($cid=0,$absurls=false,$force_view=false,$action='',$delim='&amp;',$more='',$def_cat='all',$page=0)
	{
		if($this->use_alt_plinks)
			$absurls=true;

		$url=$absurls?$this->full_script_path:$this->script_path;
		$cname=$sname='';
		$parent=-1;

		if($cid>0)
		{
			$cname=$this->categoriesModule->get_category_name($cid,$parent);
			if($parent>-1)
			{
				$sname=$cname;$p=$parent;
				$cname=$this->categoriesModule->get_category_name($p,$parent);
			}
		}
		else
		{
			$cname=$def_cat;
			$sname='';
		}
		if($this->use_alt_plinks && $cname!='')
		  $cname=$this->categoriesModule->get_category_plinkname($cname);
		$cname_for_url=urlencode($cname);
		if($this->use_alt_plinks && $sname!='')
		  $sname=$this->categoriesModule->get_category_plinkname($sname);
		$sname_for_url=urlencode($sname);

		if($this->use_alt_plinks)
		{
			$url=str_replace($this->pg_name,'',$url)."category/".($cname!=''?$cname_for_url.'/':'');
			$url_has_ap=$more!='' || $page>0;
			$url.=$sname!=''?$sname_for_url.'/':($url_has_ap?'-/':'');
			if($url_has_ap)
				$url.=$page>0?$page:'-/';
			if($more!='')
				$url.=$more;
			return $url;
		}
		else
			return $url.'?category='.$cname_for_url
				.($sname!=''?$delim.'subcat='.$sname_for_url:'').($page>1?$delim.'page='.$page:'').$more;
	}

	protected function prepare_output($output,$canonical='')
	{
		if(strpos($output,'type="application/rss+xml"')===false)
			$output=str_replace('<!--scripts-->','<link rel="alternate" title="'.$this->pg_settings['g_shop_name'].'" href="'.$this->g_abs_path.$this->pg_name.'?action=rss" type="application/rss+xml"><!--scripts-->',$output);
		if($this->use_alt_plinks)
		{
			$base_url=dirname($this->full_script_path).'/';
			$output=str_replace('</title>','</title>
<base href="'.$base_url.'">',$output);
			$output=str_replace($this->g_abs_path.$this->pg_name.'?action=rss"',$base_url.'rss/"',$output);
		}

		if($canonical!='')
			$output=str_replace('<!--scripts-->','
<link rel="canonical" href="'.$canonical.'"><!--scripts-->',$output);

		$output=str_replace(array('%25abs_path%25','%abs_path%'),$this->full_script_path,$output);

		$output=$this->categoriesModule->include_categories_inmenu($output,false);

		if(strpos($output,'%TAGS_CLOUD')!==false)
		{
			$tc=new tags_cloud($this);
			$tc->parse_tagcloud($output);
		}

		if(strpos($output,'%CATEGORY_CLOUD(')!==false)
			$output=str_replace(Formatter::GFSAbi($output,'%CATEGORY_CLOUD(',')%'),$this->categoriesModule->build_category_cloud(),$output);

		$this->relToAbs($output);
		return $output;
	}

	protected function set_vatSession($vat1=0,$vat2=0,$vat_c=0,$vat_as=0)
	{
		Session::setVar('vat1'.$this->pg_id,$vat1);
		Session::setVar('vat2'.$this->pg_id,$vat2);
		Session::setVar('vat_c'.$this->pg_id,$vat_c);
		Session::setVar('vat_as'.$this->pg_id,$vat_as);
	}

	protected function assign_vat()
	{
		global $db;

		if(($this->session_on==false))
		{
			Session::intStart();
			$this->session_on=true;
		}

		$country_code=isset($_REQUEST['country'])?addslashes($_REQUEST['country']):'US';
		if(isset($_REQUEST['country']))
		{
			 $this->pg_settings['g_check_country']=substr($_REQUEST['country'],0,2);
			 Session::setVar('session_country'.$this->pg_id,$this->pg_settings['g_check_country']);
		}

		if(isset($_REQUEST['shipping_list']))
		{
			$this->pg_settings['g_check_shipping_list']=substr($_REQUEST['shipping_list'],0,32);
			Session::setVar('session_shipping_list'.$this->pg_id,$this->pg_settings['g_check_shipping_list']);
		}

		$state_code=isset($_REQUEST['state'])?addslashes($_REQUEST['state']):'';

		$data=$db->query_first('
			SELECT *
			FROM '.$db->pre.'tax
			WHERE country="'.$country_code.'" AND state="'.$state_code.'"');

		if(!empty($data))
			$this->set_vatSession($data['vat1'],$data['vat2'],$data['cummulative'],$data['toshipping']);
		else
			$this->set_vatSession();

		$cartstring=$this->cart('show_final',0,'',0,'','','',0,'');
		print $cartstring;
		exit;
	}

	protected function assign_shipping()
	{
		if(($this->session_on==false))
		{
			Session::intStart();
			$this->session_on=true;
		}

		if(isset($_REQUEST['country']))
		{
			 $this->pg_settings['g_check_country']=substr($_REQUEST['country'],0,2);
			 Session::setVar('session_country'.$this->pg_id,$this->pg_settings['g_check_country']);
		}

		if(isset($_REQUEST['shipping_list']))
		{
			$this->pg_settings['g_check_shipping_list']=substr($_REQUEST['shipping_list'],0,32);
			Session::setVar('session_shipping_list'.$this->pg_id,$this->pg_settings['g_check_shipping_list']);
		}

		$cartstring=$this->cart('show_final',0,'',0,'','','',0,'');
		print $cartstring;
		exit;
	}

	protected function update_language_set($action,$admin_actions,$user_actions)
	{
		parent::update_language_set($action,$admin_actions,$user_actions);

		$this->nav_labels=array('first'=>$this->lang_l('first'),'prev'=>$this->lang_l('prev'),
			 'next'=>$this->lang_l('next'),'last'=>$this->lang_l('last'),'home'=>$this->lang_l('home'),
			 'load more'=>$this->lang_l('load more'));

		$this->entry_status=array(
				'unpublished'=>$this->lang_l('unpublished'),
				'published'=>$this->lang_l('published'),
				'pending'=>$this->lang_l('pending preview'),
				'scheduled'=>$this->lang_l('scheduled'),
				'template'=>$this->lang_l('template'));
		$this->entry_accessibility=array(
				'hidden'=>$this->lang_l('hidden'),
				'public'=>$this->lang_l('public'));
	}

	public function db_fetch_settings()
	{
		global $db;

		parent::db_fetch_settings();
		if(!isset($this->all_settings['catviewid']))
			$this->all_settings['catviewid']='0';
		if(!isset($this->all_settings['inv_id']))
			$this->all_settings['inv_id']=1;
		if(!isset($this->all_settings['stock_mess']))
			$this->all_settings['stock_mess']='Out Of Stock';
		if(!isset($this->all_settings['stock_dis']))
			$this->all_settings['stock_dis']='0';
		if(!isset($this->all_settings['stock_rem']))
			$this->all_settings['stock_rem']='0';
		if(!isset($this->all_settings['auto_confirm']))
			$this->all_settings['auto_confirm']='0';
		foreach($this->optionsModule->options as $opt)
			if(!isset($this->all_settings[$opt.'_req'])) $this->all_settings[$opt.'_req']='0';
		if(!isset($this->all_settings['ui_req']))
			$this->all_settings['ui_req']='0';
		if(!isset($this->all_settings['use_pdf']))
			$this->all_settings['use_pdf']='0';
		if(!isset($this->all_settings['use_attachments']))
			$this->all_settings['use_attachments']='0';
		if(!isset($this->all_settings['invoice_file_name']))
			$this->all_settings['invoice_file_name']='invoice';
		for($i=1;$i<=COUNT_ATTACMENTS;$i++){
			if(!isset($this->all_settings['attachment'.$i]))
				$this->all_settings['attachment'.$i]='';
		}
		if(!isset($this->all_settings['use_myo']))
			$this->all_settings['use_myo']='0';
		if(!isset($this->all_settings['coupon_allpages']))
			$this->all_settings['coupon_allpages']='0';
		if(!isset($this->all_settings['cross_filters']))
			$this->all_settings['cross_filters']='0';
		if(!isset($this->all_settings['features_type']))
			$this->all_settings['features_type']='0';// 0 check 1 radio 2 drop
		if(!isset($this->all_settings['filters_limit']))
			$this->all_settings['filters_limit']='0';
		if(!isset($this->all_settings['subconfront']))
			$this->all_settings['subconfront']='0';
		if(!isset($this->all_settings['quickproduct']))
			$this->all_settings['quickproduct']='0';
		if(!isset($this->all_settings['filters_type'])) // 0 check 1 radio 2 drop
		{
		  if(isset($this->all_settings['filters_dropdown']) && $this->all_settings['filters_dropdown']=='1')
				$f=F_DROPDOWN;
		  elseif(isset($this->all_settings['multi_filters']) && $this->all_settings['multi_filters']=='1')
				$f=F_CHECKBOX;
		  else
				$f=F_RADIO;
		  $this->all_settings['filters_type']=$f;
		}
		if(!isset($this->all_settings['cat_features']))
			$this->all_settings['cat_features']='0';
		if(!isset($this->all_settings['var_filters']))
			$this->all_settings['var_filters']='0';
		if(!isset($this->all_settings['rec_viewed']))
			$this->all_settings['rec_viewed']='10';
		if(!isset($this->all_settings['min_order']))
			$this->all_settings['min_order']='0';
		if(!isset($this->all_settings['min_order_alert']))
			$this->all_settings['min_order_alert']='Minimum order is not reached!';
		if(!isset($this->all_settings['status_options']))
			$this->all_settings['status_options']='being processed#paid#sent to customer';

		if(!isset($this->all_settings['rvc']))
			$this->all_settings['rvc']='6';
		if(!isset($this->all_settings['fullview_cf']))
			$this->all_settings['fullview_cf']='0';
		if(!isset($this->all_settings['serials']))
			$this->all_settings['serials']='0';
		if(!isset($this->all_settings['use_ajax']))
			$this->all_settings['use_ajax']='1';
		if(!isset($this->all_settings['features']))
			$this->all_settings['features']='1';
		if(!isset($this->all_settings['features_locked']))
			$this->all_settings['features_locked']='0';
		if(!isset($this->all_settings['rel']))
			$this->all_settings['rel']='0';
		if(!isset($this->all_settings['prod_cols']))
			$this->all_settings['prod_cols']=1;
		if(!isset($this->all_settings['hideHidden']))
			$this->all_settings['hideHidden']=1;
		if(!isset($this->all_settings['com']))
			$this->all_settings['com']='0';
		if(!isset($this->all_settings['pby']))
			$this->all_settings['pby']='0';
		if(!isset($this->all_settings['allow_null_cart']))
			$this->all_settings['allow_null_cart']='0';
		if(!isset($this->all_settings['return_to_stock']))
			$this->all_settings['return_to_stock']='0';
		if(!isset($this->all_settings['allow_float_amount']))
			$this->all_settings['allow_float_amount']='0';
		if(!isset($this->all_settings['empty_db_msg']))
			$this->all_settings['empty_db_msg']='<img src="http://www.ezgenerator.com/exticons/constructionkey.png">';
		if(!isset($this->all_settings['rss_pattern']))
			$this->all_settings['rss_pattern']='';
		if(!isset($this->all_settings['user_orderby']))
		{
			if($this->shop)
				$this->all_settings['user_orderby']='None||#Name (a to z)|name|asc#Name (z to a)|name|desc#Price (low to high)|price|asc#Price (high to low)|price|desc';
			else
				$this->all_settings['user_orderby']='None||#Name (a to z)|name|asc#Name (z to a)|name|desc';
		}
		if(!isset($this->all_settings['next_numeric_code']))
			$this->all_settings['next_numeric_code']='';
		if(!isset($this->all_settings['error_404_page']))
			$this->all_settings['error_404_page']='';
		if(!isset($this->all_settings['conversion_rate']))
			$this->all_settings['conversion_rate']=1;
		if(!isset($this->all_settings['contentbuilder']))
			$this->all_settings['contentbuilder']='0';
		if(!isset($this->all_settings['front_edit']))
			$this->all_settings['front_edit']='1';
		$this->g_use_com=$this->get_setting('com');
		$this->g_use_pby=$this->get_setting('pby');
		if($this->g_linked && !$this->g_use_com)
		{
			$val=$db->query_singlevalue('
				SELECT sval
				FROM '.$db->pre.$this->g_datapre.'settings
				WHERE skey="com"',true);
			if($val==='1')
			{
				$this->db_insert_settings(array('com'=>'1'));
				$this->g_use_com=true;
			}
		}

		if(!$this->g_use_com) $this->pg_settings['enable_comments']=false;

		$this->g_use_rel=$this->get_setting('rel');
		if($this->g_linked && !$this->g_use_rel)
		{
			$val=$db->query_singlevalue('
				SELECT sval
				FROM '.$db->pre.$this->g_datapre.'settings
				WHERE skey="rel"',true);
			if($val==='1')
			{
				$this->db_insert_settings(array('rel'=>'1'));
				$this->g_use_rel=true;
			}
		}

		if($this->pg_settings['g_data']!=$this->pg_id && isset($this->pg_settings['g_limitby']) && $this->pg_settings['g_limitby']!='')
		{
			$this->where_public=' publish=1 AND '.$this->pg_settings['g_limitby'].'=1 ';
			$this->g_data_where2=' publish=1 AND '.$this->pg_settings['g_limitby'].'=1 ';
		}
		else
			$this->g_data_where2=' '.$db->pre.$this->g_datapre.'data.publish=1 ';

		if($this->g_use_rel)
		{
			$this->where_public.=' AND (rel=0) ';
			$this->g_data_where2.=' AND (rel=0) ';
		}

		if($this->use_publish_dates)
		{
			$query=' AND if((published_date<>0 AND unpublished_date<>0 AND unpublished_date>=published_date AND unpublished_date>=NOW() AND published_date<=NOW()) OR (published_date=0 AND unpublished_date=0) OR (published_date<>0 AND unpublished_date=0 AND published_date<=NOW()),1,0)=1 ';
			$this->where_public.=$query;
			$this->g_data_where2.=$query;
		}

		$this->use_alt_plinks=$this->get_setting('plink_type','default')!=='default';
		if($this->use_alt_plinks)
			$this->g_abs_path=dirname($this->full_script_path).'/';
		//shipping

		if(!isset($this->all_settings['g_ship_settings']))
			$this->all_settings['g_ship_settings']=$this->pg_settings['g_ship_settings'];
    if(!isset($this->all_settings['watermark']))
			$this->all_settings['watermark']='0';
    if(!isset($this->all_settings['exch_rate_settings']))
			$this->all_settings['exch_rate_settings']='currency_name=1|';
		if(!isset($this->all_settings['g_ship_vat']))
			$this->all_settings['g_ship_vat']=$this->pg_settings['g_ship_vat'];
		if(!isset($this->all_settings['g_ship_type']))
			$this->all_settings['g_ship_type']=$this->pg_settings['g_ship_type'];
		$this->g_ship_usefield=$this->all_settings['g_ship_type'] == '5' || $this->all_settings['g_ship_type'] == '6';

		if(!isset($this->all_settings['g_ship_amount']))
			$this->all_settings['g_ship_amount']=$this->pg_settings['g_ship_amount'];
		if(!isset($this->all_settings['g_ship_above_limit']))
			$this->all_settings['g_ship_above_limit']=$this->pg_settings['g_ship_above_limit'];
		if(!isset($this->all_settings['g_ship_above_on']))
			$this->all_settings['g_ship_above_on']=$this->pg_settings['g_ship_above_on'];
	}

	public function cleanup_cart()
	{
		$this->unreg_session();
		$this->stockModule->clean_stock_session();
	}

	protected function resolve_permalink()
	{
		global $db;

		$req=Formatter::stripTags(Linker::requestUri());
		$req=Formatter::GFS($req,'','?');
		$url_data=explode("/",$req);
		$abs_url_data=explode("/",dirname($this->full_script_path).'/');
		$url_data_t=array();
		foreach($url_data as $v)
			if(!in_array($v,$abs_url_data) && $v!='')
				$url_data_t[]=$v;

		$url_data=$url_data_t;

		if(isset($url_data[0]) && !empty($url_data[0]) && strpos($url_data[0],'action=')===false)
		{
			if(isset($_REQUEST['category']) && !isset($_REQUEST['q']))
				Linker::customErrorRedirect($this->rel_path,$this->page_id);//only redirects when custom error page avail.

			if($url_data[0]=='rss')
			{
				$this->action_id='rss';
				array_shift($url_data);
			}

			if(isset($url_data[0]) && $url_data[0]=='category')
			{
				$c=$url_data[1];
				if($c=='Loop Content')
					$c='Loops';
				elseif($c=='Sample Fusion')
					$c='Samples';

				$c=$this->categoriesModule->get_category_from_plinkname($c,$this->g_catid);

				$_REQUEST['category']=$_GET['category']=$c;
				array_shift($url_data);
				array_shift($url_data);

				if(isset($url_data[0]) && !in_array($url_data[0],array('rss','tag','brands','-','all')) && !is_numeric($url_data[0]))
				{
					$subc=$this->categoriesModule->get_category_from_plinkname($url_data[0],$this->g_catid);
					if($subc!='')
						  $_REQUEST['subcat']=$_GET['subcat']=$subc;
					array_shift($url_data);
				}
				elseif(isset($url_data[0]) && $url_data[0]=='-')
					array_shift($url_data);

				if(isset($url_data[0]) && $url_data[0]=='rss')
				{
					$this->action_id='rss';
					array_shift($url_data);
				}

				if(isset($url_data[0]))
				{
					$_GET['page']=$_REQUEST['page']=intval($url_data[0]);
					array_shift($url_data);
				}

				if(isset($url_data[0]) && $url_data[0]!='')
				{
					$_REQUEST['orderbyid']=$url_data[0];
					array_shift($url_data);
				}
			}
			else if(isset($url_data[0]) && $url_data[0]=='by')
			{
				$_REQUEST['category']=$_GET['category']=$this->categoriesModule->get_category_from_plinkname('all',$this->g_catid);
				array_shift($url_data);
				$options=array();
				while(isset($url_data[0]))
				{
					$options[]=addSlashes($url_data[0]);
					array_shift($url_data);
				}

				if(count($options)>0)
				{
					$_REQUEST['features']=array();
					$options=$db->fetch_all_array('
						SELECT pr_id,value
						FROM '.$db->pre.$this->g_datapre.'options
						WHERE value IN ("'.implode('","',$options).'")');

					foreach($options as $v)
						$_REQUEST['features'][]=$v['pr_id'].'|'.$v['value'];

					$this->filtersModule->cleanFilters();
					$this->filtersModule->updateFilters('features',0,$this->g_catid);
					$this->filtersModule->initFilters();
					array_shift($url_data);
				}
			}

			if(isset($url_data[0]) && $url_data[0]=='tag')
			{
				$_REQUEST['tag']=$url_data[1];
				array_shift($url_data);
				array_shift($url_data);
			}

			if(isset($url_data[0]) && $url_data[0]=='brands')
			{
				$_REQUEST['brands']=$url_data[1];
				array_shift($url_data);
				array_shift($url_data);
			}

			if(isset($url_data[0]) && $url_data[0]=='product')
			{
				$item_id=intval($url_data[1]);
				$_REQUEST['iid']=$_GET['iid']=$item_id;
				$this->action_id='item';
				if(isset($item_id))
				{
					$product_data=$db->query_first('
						SELECT *
						FROM '.$db->pre.$this->g_datapre.'data
						WHERE pid='.$item_id);
					$_REQUEST['pid']=$item_id;
				}
				array_shift($url_data);
				array_shift($url_data);
			}

			if(isset($url_data[0]) && $url_data[0]=='-') //page id
			{
				array_shift($url_data);
			}

			if(isset($url_data[0])) //page id
			{
				if(is_numeric($url_data[0]))
					$_GET['page']=$_REQUEST['page']=intval($url_data[0]);
				elseif($url_data[0]=='all')
					$_GET['page']=$_REQUEST['page']='all';
				elseif($url_data[0]=='rss')
						$this->action_id='rss';
				elseif($this->use_friendly && !isset($url_data[1]))
				{
					$url_info = parse_url($this->full_script_path);
					$page_name = trim(urlencode(substr(strrchr($url_info['path'],"/"),1)));
					$pname=$url_data[0];
					$pnameEnc=trim(urlencode($pname));
					$product_data=$db->query_first('
						SELECT *
						FROM '.$db->pre.$this->g_datapre.'data
						WHERE friendlyurl = "'.addslashes($pnameEnc).'" OR friendlyurl = "'.addslashes($pname).'"');
					if(isset($product_data['pid']))
					{
						$_REQUEST['iid']=$_GET['iid']=$_REQUEST['pid']=$product_data['pid'];
						$this->action_id='item';
					}
					else if($this->all_settings['error_404_page']!='' && !isset($_REQUEST['search']) && !isset($_REQUEST['action']) && $page_name != $pnameEnc){
						$url_404_page = Linker::buildSelfURL('',false,$this->rel_path).$this->all_settings['error_404_page'];
						header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
						header('Location: '.$url_404_page);
					}
				}
				else
				{
					$url_info = parse_url($this->full_script_path);
					$page_name = trim(urlencode(substr(strrchr($url_info['path'],"/"),1)));
					$pname=isset($url_data[2])?trim(urldecode($url_data[2])):trim(urldecode($url_data[1]));
					$product_data=$db->query_first('
						SELECT *
						FROM '.$db->pre.$this->g_datapre.'data
						WHERE name="'.addslashes($pname). '"');
					if(isset($product_data['pid']))
					{
						$_REQUEST['iid']=$_GET['iid']=$_REQUEST['pid']=$product_data['pid'];
						$this->action_id='item';
					}
					else if($this->use_friendly && $this->all_settings['error_404_page']!='' && !isset($_REQUEST['search']) && !isset($_REQUEST['action']) && $page_name != $pname){
						$url_404_page = Linker::buildSelfURL('',false,$this->rel_path).$this->all_settings['error_404_page'];
						header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
						header('Location: '.$url_404_page);
					}
				}
			}
		}
		$this->setCurrPage();
	}

	public function get_minicart_htmlstr($basket=false)
	{
		$this->ca_check();
		$result=$this->cart(($basket?'show':'minicart'),$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'');
		$this->parse_page_id($result);
		$result=str_replace(array("\r\n","\n","\n")," ",$result);
		return $result;
	}

	public function item_action_cart($a,&$result,$iid=false,$q=false,$arr_get=array())
	{
		$item_options=array();
		$item_id=$iid!==false?$iid:(isset($_REQUEST['iid'])?intval($_REQUEST['iid']):0);
		$searchstring=$q!==false?$q:(isset($_REQUEST['q'])?Formatter::stripTags($_REQUEST['q']):'');
		foreach($this->optionsModule->options as $k=>$opt)
			if(isset($arr_get['subtype'.$k]))
				$item_options[$k]=strip_tags($arr_get['subtype'.$k]);
			elseif(isset($_GET['subtype'.$k]))
				$item_options[$k]=strip_tags($_GET['subtype'.$k]);

		$bundle_arr['bundle_id']=isset($arr_get['bundle_id'])?intval($arr_get['bundle_id']):0;
		$bundle_arr['bundle_primaryItem']=isset($arr_get['bundle_primaryItem'])?intval($arr_get['bundle_primaryItem']):0;
		$bundle_arr['bundle_discount']=isset($arr_get['bundle_discount'])?strip_tags($arr_get['bundle_discount']):'';
		$bundle_arr['bundle_name']=isset($arr_get['bundle_name'])?strip_tags($arr_get['bundle_name']):'';
		$bundle_arr['bundle_count']=isset($arr_get['bundle_count'])?intval($arr_get['bundle_count']):0;

		$userdata=isset($arr_get['userdata'])?strip_tags($arr_get['userdata']):(isset($_GET['userdata'])?strip_tags($_GET['userdata']):'');
		if($userdata=='')
			foreach($this->pg_settings['g_fields_array'] as $k=>$v)
			{
				if($this->pg_settings['g_fields_array'][$k]['itype']=='userinput')
					$userdata.=isset($arr_get[$k])?$k.':'.strip_tags($arr_get[$k]).'**':(isset($_GET[$k])?$k.':'.strip_tags($_GET[$k]).'**':'');
			}

		$item_count=isset($arr_get['quantity'])?floatval($arr_get['quantity']):(isset($_GET['quantity'])?floatval($_GET['quantity']):1);
		if($this->get_setting('allow_float_amount')==0)
			 $item_count=intval($item_count);
		if($item_count<0)
			$item_count=0;
		if(isset($_REQUEST['iid_arr'])&&isset($_REQUEST['bundle_info'])&&count($_REQUEST['iid_arr'])==count($_REQUEST['bundle_info']))
		{
			foreach($_REQUEST['iid_arr'] as $key=>$iid_bundle){
				$bundle_arr=json_decode(strip_tags($_REQUEST['bundle_info'][$key]),true);
				$r=$this->cart($a,$this->g_catid,intval($iid_bundle),$this->g_pagenr,$searchstring,$item_options,$userdata,$item_count,'',array_filter($bundle_arr));
			}
		}
		else
			$r=$this->cart($a,$this->g_catid,$item_id,$this->g_pagenr,$searchstring,$item_options,$userdata,$item_count,'',array_filter($bundle_arr));
		$result=$r;
	}

	protected function save_product_simple($db)
	{
		$data=array();
		if(isset($_REQUEST['pid']))
			$pid=intval($_REQUEST['pid']);
		else
			Linker::checkReturnURL();
		if(isset($_REQUEST['dt64']))
			$data['html_description']=base64_decode($_REQUEST['dt64']);
		elseif(isset($_REQUEST['dt']))
			$data['html_description']=$_REQUEST['dt'];
		else
			$data['html_description']=$_REQUEST['content'];

		if($this->all_settings['contentbuilder']){
			Session::intStart();
			$user=$this->user->mGetLoggedUser($db);
			$data['html_description']=Editor::replaceData64image_contentBuilder($data['html_description'],$this->rel_path,$user['username']);
		}

		if(!$this->f->tiny)
			Formatter::cleanWordInput($data['html_description']);

		$data['html_description']=str_replace('\\','&#92;',$data['html_description']);//jpc
		$data['html_description']=Editor::replaceClasses($data['html_description']);

		$db->query_update($this->pg_settings['g_data'].'_data',$data,'pid='.$pid);
		$tc=new tags_cloud($this);
		$tc->clean_tagcloud();

		History::add($this->pg_id,$this->pg_settings['g_data'].'_data',$pid,$this->user->getUname(),$data);
		Linker::checkReturnURL();
	}

	protected function set_limit_own_post($db)
	{
		$access_type='';
		$ret = User::mHasWriteAccess2($this->user->getUname(),$this->user->getAllData(),$this->page_info,$access_type);
		if($access_type=='3'&&$ret) //access is edit own posts
		{
			$this->edit_own_posts_only=User::userEditOwn($db,$this->user->getId(),$this->page_info);
			$users_access=User::mGetUserAccess($db,'user_id = '.$this->user->getId());
			$limit_a=array('1'=>50,'2'=>20,'3'=>10,'4'=>5,'5'=>1);  //change here will require change in check_section_range in centraladmin
			$limit_own_post=User::userGetLimitPost($users_access,$this->page_info,$this->user->getId());
			$this->limit_own_post=array_key_exists($limit_own_post, $limit_a)?$limit_a[$limit_own_post]:0;
			if($this->limit_own_post>0)
				$this->g_use_pby=true;	
		}
		else if($ret||$this->user->isAdmin()||$this->user->isAdminUser()) //logged as page editor, all editor, admin, admin user
			$this->limit_own_post=1000000;
	}

	public function process()
	{
		global $return_page,$db;

		$this->action_id=(isset($_REQUEST['action']))?Formatter::stripTags($_REQUEST['action']):'';
		if($this->action_id=='search')
			 $this->action_id='';

		if(!$this->shop) //ignore shop actions
		{
			if(in_array($this->action_id,array('login','cleanup','pay','callback','order','return'
				 ,'merchant_return_link','return_ok','cancel','minicart','minicartjs','checkout'
				 ,'basket','download','addandbasket','addandbasket2','addandcheckout','add2')))
			$this->action_id='products';
		}
		if(isset($_REQUEST['entry_id']))
			$_REQUEST['iid']=$_REQUEST['entry_id'];
		$this->page_info=CA::getPageParams($this->pg_id,$this->rel_path);

		if($this->action_id=="version")
			$this->version();

		$this->init_db();

		if($this->get_setting('stock_rem')=='1')
			$this->where_public.=' AND stock > 0 ';

		$this->update_language_set($this->action_id,$this->admin_actions,array());

		if($this->use_alt_plinks && !isset($_REQUEST['quantitybutton']) && !isset($_REQUEST['q']) && !isset($_REQUEST['cleanup'])
				  && !isset($_REQUEST['remove']) && ($this->action_id=='' || (!in_array($this->action_id,$this->admin_actions)
				  && !in_array($this->action_id,array('remove','random','download','fdownload','callback','return','category_vlist','category_hlist')))))
			$this->resolve_permalink();

		if(isset($_REQUEST['tag']))
			$this->tag_object= new Tags($this,'tag','keywords');
		elseif(isset($_REQUEST['brands']))
			$this->tag_object= new Tags($this,'brands','brand');

		$this->g_pagenr=(isset($_REQUEST['page']))?intval($_REQUEST['page']):1;
		if($this->g_pagenr<1)
			$this->g_pagenr=1;

		$this->g_catid=$this->categoriesModule->get_categoryid();
		$parentid =-1;
		$this->g_cat=$this->categoriesModule->get_category_name($this->g_catid,$parentid);

		$this->filtersModule->initFilters();

		if($this->action_id=='rss')
		{
			$rs = new listerrss($this);
			$rs->handle_rss($this);
		}
		elseif($this->action_id=='tagcloud')
		{
			$tc=new tags_cloud($this);
			$output=$tc->get_tagcloud('%TAGS_CLOUD%');
			$output=str_replace(array('href="?tag',"\r\n","\n"),
					  array('href="'.$this->full_script_path.'?tag','',''),$output);
			print "document.write('".$output."');";
			exit;
		}
		elseif($this->action_id=='compare')
			 $this->featuresModule->handle_compare();

		if(isset($_REQUEST['sort']) || isset($_REQUEST['toggle']))
			$this->action_id='addsorting';
		if(isset($_REQUEST['cleanup']))
		{
			$this->cleanup_cart();
			Linker::checkReturnURL(true)?Linker::checkReturnURL():$this->action_id='list';
		}

		if($this->paymentModule!==false)
		  $this->paymentModule->handleCallback();

		if(isset($_REQUEST['myorders']))
		{
			$this->ca_check();
			$this->show_user_orders();
		}
		elseif($this->action_id!='' || isset($_REQUEST['merchant_return_link']))
		{
			$a=$this->action_id!=''?$this->action_id:'merchant_return_link';
			if($a=='invoice')
			{
				 $this->ca_check();
				 $this->InvoiceModule->view_invoice();
			}
			elseif($a=='vc')
				$this->couponsModule->validate_coupon();
			elseif($a=='pay')
			{
				$this->ca_check(true);
				$this->cart($a,$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'');
			}
			elseif(in_array($a,$this->admin_actions))
			{
		  		$page_info=CA::getPageParams($this->pg_id,$this->rel_path);
				Session::intStart();

				if(isset($page_info[7]) && $page_info[7]!='')
				{
					$this->user->mGetLoggedUser($db,'');

					if($this->user->isEZGAdminLogged())
						  $this->edit_own_posts_only=false;
					else
					{
						  $this->set_limit_own_post($db);
						  $access_type='';
						  if(!$this->user->userCookie() || !User::mHasWriteAccess2($this->user->getUname(),$this->user->getAllData(),$page_info,$access_type))
						  {
								Linker::redirect($this->rel_path.'documents/centraladmin.php?pageid='.$this->pg_id.'&indexflag=index',false);
								exit;
						  }
					}

					if($this->edit_own_posts_only && !in_array($a,$this->editor_actions))
					{
						$a='stock';
						$this->action_id='stock';
					}

					if($a=='addsorting')
					{
						 $this->categoriesModule->sort_category();
						 exit;
					}
					elseif($a=='cleancatsorting')
						$this->clean_setting('sort_category');
					elseif($a=='cleanfieldsorting')
						$this->clean_setting('sort-1');
					elseif($a=='fvalues')
					{
						 $fid=Formatter::stripTags($_REQUEST['fid']);
						 $isFeature=strpos($fid,'option_')===0;

						 if($isFeature)
						 {
								$pr_id=intval(str_replace('option_','',$fid));
								$que=
									'SELECT value
									 FROM '.$db->pre.$this->g_datapre.'defaultoptions
									 WHERE pr_id = '.$pr_id.' AND value <> ""
									 ORDER BY order_by';
								$data=$db->fetch_all_array($que);
								if(count($data)==0)
								{
									 $que=
										  'SELECT DISTINCT value
										  FROM '.$db->pre.$this->g_datapre.'options
										  WHERE pr_id = '.$pr_id.'
										  ORDER BY value ASC';
									 $data=$db->fetch_all_array($que);
								}
						 }
						 else
						 {
								$que=
									'SELECT DISTINCT '.$fid.'
									 FROM '.$db->pre.$this->g_datapre.'data
									 WHERE '.$fid.' <> ""
									 ORDER BY '.$fid. ' ASC';
								$data=$db->fetch_all_array($que);
						 }
						 $fid=$isFeature?'value':$fid;

						 $res='';
						 foreach($data as $v)
							 $res.=$v[$fid].'#';
						 if(strlen($res)>1)
							 $res=substr($res,0,strlen($res)-1);
						 $this->setState('fvalues',array(&$res));
						 print $res;
						 exit;
					}

					if($a=='del_comment' && isset($_REQUEST['comment_id'])) //on frontpage
					{
						 $comment_id=intval($_REQUEST['comment_id']);
						 $this->commentModule->del_comment($comment_id);
					}

					if($a=='move_confirm')
						 $this->move_confirm(intval($_REQUEST['id']),false,true);
					elseif(in_array($a,array('new_product','products','toggle_cols','toggle_hidden')))
					{
						 $mng_products = new mng_products_screen($this);
						 $mng_products->handle_screen();
					}
					elseif($a=='categories')
					{
						 $mng_categories = new manage_categories_screen($this);
						 $mng_categories->handle_screen();
					}
					elseif($a=='stock')
					{
						 $mng_stock = new mng_stock_screen($this);
						 $mng_stock->handle_screen();
					}
					elseif($a=='taxes')
					{
						 $mng_taxes = new mng_taxes_screen($this);
						 $mng_taxes->handle_screen();
					}
					elseif($a=='coupons')
					{
						 $mng_coupons = new mng_coupons_screen($this);
						 $mng_coupons->handle_screen();
					}
					elseif($a=='features')
					{
						 $mng_prop = new mng_features_screen($this);
						 $mng_prop->handle_screen();
					}
					elseif($a=='comments')
					{
						 $mng_comments = new mng_comments_screen($this);
						 $mng_comments->handle_screen();
					}
					elseif($a=='orders')
					{
						 $mng_orders = new mng_orders_screen($this);
						 $mng_orders->handle_screen();
					}
					elseif(in_array($a,array('import','import2')))
					{
						 $import = new import($this);
						 $import->mng_import($a);
					}
					elseif($a=='settings')
					{
						 $mng_settings = new manage_settings_screen($this);
						 $mng_settings->handle_screen();
					}
					elseif(in_array($a,array('login','pending')))
					{
						 $mng_orders = new mng_pendingorders_screen($this);
						 $mng_orders->handle_screen();
					}
					elseif($a=='pp')
					{
						 $data=$db->fetch_all_array('SELECT * FROM '.$db->pre.$this->pg_pre.'paypal');
						 var_dump($data);
					}
					elseif($a=='bundles')
					{
						$mng_bundle = new mng_bundles_screen($this);
						$mng_bundle->handle_screen();
					}
				}
			}
			elseif($a=='order')
			{
				$this->ca_check();
				$this->show_order_details(intval($_REQUEST['id']),false);
			}
			elseif($a=='return' || $a=='merchant_return_link')
			{
				$paypal=(isset($_REQUEST['payer_status']));
				if(($paypal)&&($this->pg_settings['g_checkout_callback_on']['paypal']=='FALSE') && $this->paymentModule!==false)
					$this->paymentModule->handlePost();
				elseif($paypal)
					$this->cart('return_ok',$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'paypal');
				else
					$this->cart('return_ok',$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'');
			}
			elseif($a=='return_ok')
				$this->cart($a,$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'');
			elseif($a=='random')
				$this->return_random();
			elseif($a=='cancel')
			{
				$this->ca_check();
				$output = $this->return_cancel_file('');
				print $output;
				exit;
			}
			elseif(($a=='productjs'))
			{
				$this->ca_check();
				$item_id=intval($_REQUEST['iid']);
				$result=$this->show_item($item_id,Images::parse_scale_macros($this->get_setting('productjs','<b>%name%</b><br>%SCALE[%image1%,150,150,,,left]% %html_description%')));
				echo $result;
				exit;
			}
			elseif(($a=='minicart')||($a=='minicartjs'))
			{
				$result=$this->get_minicart_htmlstr();
				$js='';
				$this->shop_js_functions($result,$js);
				if($a=='minicartjs')
					print "document.write('".$result."');";
				else
					print '<html><head><script>'.$js.'</script><script src="https://ajax.googleapis.com/ajax/libs/jquery/'.$this->f->jquery_ver.'/jquery.min.js"></script><link type="text/css" href="'.$this->rel_path.'documents/textstyles_nf.css" rel="stylesheet"></head><body>'.$result.'</body></html>';
			}
			elseif($a=='wishlist')
			{
				if($this->wishlistModule->user_id===false){
					Linker::redirect($this->rel_path.'documents/centraladmin.php?pageid='.$this->pg_id.'&indexflag=index',false);
					exit;
				}
				$this->ca_check();
				$this->wishlistModule->process();
				$result=$this->show_category('all');
				print $this->prepare_output($result);
			}
			elseif($a=='checkout')
			{
				$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
				if($do=='update_tax')
					$this->assign_vat();
				elseif($do=='update_shipping')
					$this->assign_shipping();
				else
				{
					$this->ca_check(true);
					$page=$this->cart($a,$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'');
					$page=$this->recentlyViewedModule->parse_recently_viewed($page);
					$page=$this->finalize_output($page);
					$this->ReplaceMacros($page);
					print $this->prepare_output($page);
				}
			}
			elseif($a=='basket')
			{
				$this->ca_check();
				$page=$this->cart($a,-1,$this->pg_id,$this->pg_id,'','','',0,'');
				$page=$this->recentlyViewedModule->parse_recently_viewed($page);
				$page=$this->finalize_output($page);
				print $this->prepare_output($page);
			}
			elseif($a=='list')
			{
				$this->ca_check();
				$result=$this->show_list();
				print $this->prepare_output($result);
			}
			elseif($a=='downloadf')
			{
				$this->ca_check();
				if($this->protected)
					 output_generator::downloadFile($this->rel_path.str_replace('../','',$_REQUEST['url']));
				exit;
			}
			elseif($a=='download')
			{
				$this->ca_check();
				$this->cart($a,$this->pg_id,$this->pg_id,$this->pg_id,'','','',0,'');
			}
			elseif($a=='fdownload')
			{
				 $this->ca_check();
				 $this->fdownload();
			}
			elseif($a=='image1' || $a=='image2' || $a=='image3')
			{
				$records=$db->fetch_all_array('
					SELECT '.$a.'
					FROM '.$db->pre.$this->g_datapre.'data
					WHERE '.$this->where_public);
				$result='';
				foreach($records as $v)
					if($v[$a]!='')
						$result.= $this->f->site_url.str_replace('../','',$v[$a]).'|';
				print $result;
				exit;
			}
			elseif($a=="update_products")
			{
				$this->ca_check();
				$result=$this->show_category($this->g_catid);
				$can_url=$this->pg_settings['g_catpgmax']==0?'':$this->build_permalink_cat($this->g_catid,true,false,'','&amp;');
				$this->setState('update_products');
				print $this->prepare_output($result,$can_url);
			}
			elseif($a=="clean_filters")
			{
				$this->ca_check();
				$this->filtersModule->cleanFilters();
				$result=$this->show_category($this->g_catid,true);
				print $this->prepare_output($result);
			}
			elseif($a=="filterby" || $a=="filterbyAjax")
			{
				$this->ca_check();
				if(isset($_REQUEST['fn'])&&isset($_REQUEST['fn_id']))
					$this->filtersModule->updateFilters($_REQUEST['fn'],intval($_REQUEST['fn_id']),$this->g_catid);
				$result=$this->show_category($this->g_catid,true);
				print $this->prepare_output($result);
			}
			elseif($a=='catpgmaxAjax')
			{
				$this->ca_check();
				$result=$this->show_category($this->g_catid,true);
				print $this->prepare_output($result);
			}
			elseif($a=='item' || $a=='comment')
			{
				$this->ca_check();
				$this->set_limit_own_post($db);
				if(isset($_REQUEST['iid']))
				{
					$item_id=intval($_REQUEST['iid']);
					$result=$this->cart($a,$this->pg_id,$item_id,$this->pg_id,'','','',0,'');
				}
				else
					$result=$this->show_list();
				print $this->prepare_output($result,$this->permalink);
			}
			elseif($a=='save_product_simple')
			{
				$this->save_product_simple($db);
			}
			else
			{
				$this->ca_check();
				$result='';
				if($a=='multibuyajax')
				{
					if(isset($_POST['items']))
					{
						$items=json_decode($_POST['items']);
						foreach($items as $item)
						{
							$r='';
							$iid=false;$q=false;$arr_get=array();
							foreach($item as $key=>$value)
							{
								if($key=='iid')
									$iid=intval($value);
								elseif($key=='q')
									$q=Formatter::stripTags($value);
								else
									$arr_get[$key]=$value;
							}
							$this->item_action_cart($a,$r,$iid,$q,$arr_get);
						}
						$result=$r;
					}
				}
				else
					$this->item_action_cart($a,$result);

				if($a=='addandbasket')
					print $this->prepare_output($result);
				elseif($a=='addandbasket2')
					Linker::redirect($this->full_script_path.'?action=basket',false);
				elseif($a=='addandcheckout')
					Linker::redirect($this->full_script_path.'?action=checkout',false);
				elseif($a=='add2')
					Linker::redirect($this->full_script_path,false);
				elseif($a=='addajax'||$a=='updateajax'||$a=='multibuyajax'||$a=='removeajax'||$a=='cleanupajax')
				{
					$basket=isset($_REQUEST['basket_cart']);
					if($a=='cleanupajax')
						$this->cleanup_cart();
					$result=$this->get_minicart_htmlstr($basket);
					if(strpos($result,'id="minicart"')!==false||$basket){
						print(($basket?'<div id="basketcart">'.$result."</div>":$result));
						exit;
					}
				}
				else
				{
					if(Linker::checkReturnURL(true)!==false && ($a=='remove' || $a=='update' || $a=='cleanup' || $a=='add'))
						Linker::checkReturnURL();

					$searchstring=isset($_REQUEST['q'])?Formatter::stripTags($_REQUEST['q']):'';
					if($searchstring != '')
						$abs_url=$this->full_script_path.'?category='.$this->g_cat.'&page='.$this->g_pagenr.'&q='.$searchstring;
					elseif(($a=='remove')&&($this->g_pagenr=='0'))
						$abs_url=$this->full_script_path.'?action=checkout';
					elseif(($a=='update')&&($this->g_pagenr=='0'))
						$abs_url=$this->full_script_path.'?action=checkout';
					elseif($this->g_catid===false)
						$abs_url=$this->full_script_path;
					elseif($this->g_catid==-1)
						$abs_url=$this->full_script_path.'?action=basket';
					else
					{
						if(isset($_REQUEST['iid']))
							$abs_url=$this->build_permalink_cat($this->g_catid,true,false,'','&','&iid='.intval($_REQUEST['iid']));
						else
							$abs_url=$this->build_permalink_cat($this->g_catid,true,false,'','&');
					}
					Linker::redirect($abs_url,false);
				}
			}
		}
		elseif(isset($_REQUEST['q'])||isset($_REQUEST['search']))
		{
			$this->ca_check();
			if(!isset($_REQUEST['q']) && isset($_REQUEST['search']))
				$_REQUEST['q']=$_REQUEST['search'];

			if(strpos($return_page,'?q=')===false)
				$return_page.='?q='.$_REQUEST['q'];
			$result=$this->searchModule->search();
			$this->setState('search');
			print $this->prepare_output($result);
		}
		elseif(isset($_GET['category'])&&($_GET['category'] != '-1') || $this->g_catid>-1)
		{
			$this->ca_check();
			if($_GET['category']=='all')
			{
				$result=$this->show_category('all');
				print $this->prepare_output($result);
			}
			else
			{
				$result=$this->show_category($this->g_catid);
				$cpermalink=$this->g_pagenr<2?$this->build_permalink_cat($this->g_catid,true,false,'','&amp;'):'';
				print $this->prepare_output($result,$cpermalink);
			}
		}
		elseif($this->tag_object!=null)
		{
			$this->ca_check();
			$result=$this->show_category('all');
			print $this->prepare_output($result);
		}
		elseif(isset($_REQUEST['category_vlist']) || isset($_REQUEST['category_hlist']))
			 $this->categoriesModule->ext_category_list();
		elseif($this->pg_settings['skip_mainpage'])
		{
			$result=$this->show_category('all');
			print $this->prepare_output($result);
		}
		else
		{
			$this->ca_check();
			$result=$this->show_list();
			print $this->prepare_output($result);
		}
	}
}


class ListerSearch extends page_objects
{
	//search functions
	public function parse_searchmacros($page)
	{
		$page_scripts='';
		$page=str_replace(array('%LISTER_CAT_SEARCH%','%LISTER_SEARCH%'),array('%SHOP_CAT_SEARCH%','%SHOP_SEARCH%'),$page);
		$page=Formatter::objDivReplacing('%SHOP_CAT_SEARCH%',$page);
		$page=Formatter::objDivReplacing('%SHOP_SEARCH%',$page);
		$page=str_replace('%SHOP_SEARCH%',$this->search_box_html(),$page);
		if(strpos($page,'CAT_SEARCH%')!==false)
		{
			$page=str_replace(array('%SHOP_CAT_SEARCH%','%CAT_SEARCH%'),
					array(Search::catBox($this->page->pg_name,$this->page->pg_settings['lang_l'],$page_scripts),$this->page->categoriesModule->category_searchlist()),
					$page);
		}
		if($page_scripts!=='')
			$page=Builder::includeScript($page_scripts,$page);
		return $page;
	}

	protected function search_box_html()
	{
		$output='
			<form name="search_frm" method="post" action="'.$this->page->pg_name.'" onsubmit="return (document.search_frm.q.value!=\'\')">
			<input class="input1" type="text" name="q" value="" >
			<input class="input1 search_inp_lister" type="submit" name="search_btn" value="'.$this->page->pg_settings['lang_u']['search'].'" >
			</form>';

		$this->page->page_scripts.='
$(document).ready(function(){
$(".search").livesearch();
});
		  ';
		$this->page->page_css.='
.pre-search-content #products_list {padding: 2px;}
.pre-search-content{position:absolute;z-index:600;box-shadow:1px 1px 2px #ccc}
.lister_search_frm div{display:inline}';
		return $output;
	}

	public function get_searchable_fields()
	{
		$fields_to_index=array();
		$fields_limit=false;
		if(isset($_REQUEST['search_fields']))
			 $fields_limit=is_array($_REQUEST['search_fields'])?$_REQUEST['search_fields']:array($_REQUEST['search_fields']);

		foreach($this->page->pg_settings['g_fields_array'] as $k=>$v)
			if($v["itype"]=='editor' && (!isset($v["fhidesearch"])))
			{
				if($fields_limit===false || array_search($k,$fields_limit)!==false)
					 $fields_to_index[]=$k;
			}
		foreach($this->page->pg_settings['g_fields_array'] as $k=>$v)
			if(!in_array($v["itype"],
					  array("dbid","bool","image","timestamp","pid","cid","hidden","integer","checkbox",
								"stock","download","price","file","date","editor",
								"rel","userinput"))
				&& (!isset($v["fhidesearch"]))
					  )
			{
				if($fields_limit===false || array_search($k,$fields_limit)!==false)
					 $fields_to_index[]=$k;
			}
		return $fields_to_index;
	}

	protected function is_dublicate_cat($product_search,$data,$cid)
	{
		$dublicate=false;
		if($product_search||!$this->page->livesearch) return $dublicate;
		foreach($data as $v){
			if(is_array($v) && $cid==$v['cid']){
				$dublicate=true;
				break;
			}
		}
		return $dublicate;
	}

	public function search_indb(&$page,$que,$search_type,$searchstring,$searchstring_words,$search_subtype,$category_names,$product_search)
	{
		 global $db;

		 $data=$rel=array();
		 if($search_type!='' && $search_type!='conditions' && $search_type!='exact')
		 {
			  $_REQUEST['search_fields']=$search_type;
			  $search_type='';
		 }

		 $fields_to_index=$this->get_searchable_fields();
		 $records=$db->fetch_all_array($que);
		 foreach($records as $vv)
		 {
				$is_rel=$vv['rel']>0;
				$id=$vv['pid'];
				$record_line='';

				foreach($fields_to_index as $k=>$fname){
					if(isset($vv[$fname]))
						 $record_line.=$vv[$fname].'|';
				}

				if($search_type=='exact' || $search_subtype=='exact')
				{
					if(strpos(Formatter::strToLower(ListerFunctions::HtmlToText('|'.$record_line)),$searchstring) !== false)
					{
						if($is_rel){
							$rel[]=$vv['rel'];
							if($this->page->action_id=='stock'&&!$this->is_dublicate_cat($product_search,$data,$vv['cid']))
								$data[$id]=$vv;
						}
						elseif(!$this->is_dublicate_cat($product_search,$data,$vv['cid']))
							$data[$id]=$vv;
					}
				}
				elseif(in_array($id,$rel)!==false&&!$this->is_dublicate_cat($product_search,$data,$vv['cid'])){
					$data[$id]=$vv;
				}
				elseif($search_type=='conditions')
				{
					$cond_ok=true;
					foreach($category_names as $kkk=>$vvv)
					{
						if(isset($_REQUEST[$kkk]))
						{
							$rqval=Formatter::stripTags($_REQUEST[$kkk]);
							$cond_ok=$cond_ok && ( Formatter::strToLower($vvv)== Formatter::strToLower($rqval));
							$page=str_replace('<option value="'.$rqval.'">'.$rqval.'</option>','<option selected="selected" value="'.$rqval.'">'.$rqval.'</option>',$page);
						}
					}
					if($cond_ok)
					{
						if($searchstring !== '')
						{
							$lt=Formatter::strToLower(ListerFunctions::HtmlToText('|'.$record_line));$ct=0;
							foreach($searchstring_words as $k=>$v)
							{
								if(strpos($lt,$v) !== false)
									$ct++;
							}
							$meet=($ct==count($searchstring_words));
						}
						else
							$meet=true;
						if($meet){
							if($is_rel){
								$rel[]=$vv['rel'];
								if($this->page->action_id=='stock'&&!$this->is_dublicate_cat($product_search,$data,$vv['cid']))
									$data[$id]=$vv;
							}
							elseif(!$this->is_dublicate_cat($product_search,$data,$vv['cid']))
								$data[$id]=$vv;
						}
					}
				}
				else  // word search
				{
					$lt=Formatter::strToLower(ListerFunctions::HtmlToText('|'.$record_line));

					$ct=0;
					foreach($searchstring_words as $k=>$v)
					{
						if(strpos($lt,$v) !== false)
						  $ct++;
					}
					if($ct>0)
					{
						if($is_rel){
							$rel[]=$vv['rel'];
							if($this->page->action_id=='stock'&&!$this->is_dublicate_cat($product_search,$data,$vv['cid']))
								$data[$id]=$vv;
						}
						elseif(!$this->is_dublicate_cat($product_search,$data,$vv['cid']))
							$data[$id]=$vv;
					}
				}
		  }

		  return $data;
	}

	public function search()
	{
		global $db;

		$searchstring=Formatter::strToLower($_REQUEST['q']);
		$searchstring=trim(strip_tags(str_replace("\\",'',$searchstring)));

		$category_names=array();
		foreach($this->page->categoriesModule->category_array as $k=>$v)
			 $category_names[]=isset($v['linked'])?$v['linked']['name']:$v['name'];

		$cat_name=array();
		if(isset($_REQUEST['search_category']))
		{
			$cat_array=(is_array($_REQUEST['search_category']))?$_REQUEST['search_category']:array(0=>Formatter::stripTags($_REQUEST['search_category']));
			foreach($cat_array as $k=>$v)
			{
				$category=Formatter::stripTags($v);
				if(in_array($category,$category_names))
				{
					$category=Formatter::unEsc(urldecode($category));
					if($category=='All categories')
						break;
					else
						$cat_name[]=$category;
				}
			}
		}

		$this->page->global_pagescr='';
		$search_type=(isset($_REQUEST['search_type']))?Formatter::stripTags($_REQUEST['search_type']):'';
		$search_subtype=(isset($_REQUEST['search_subtype']))?Formatter::stripTags($_REQUEST['search_subtype']):'';
		$product_search=isset($_REQUEST['product_search']);
		$livesearch_id=isset($_REQUEST['livesearch_id'])?intval($_REQUEST['livesearch_id']):0;
		$this->page->livesearch=isset($_REQUEST['x']);

		if(($searchstring != '')||($search_type=='conditions'))
		{
			if($this->page->livesearch)
			{
				$page='<LISTER_BODY>
					 <p>%SCALE['.($product_search?'%image1%':'%cimage%').',50,50,,,left]%
						<b><a class="rvts12 livesearch_a" href="'.($product_search?'%SHOP_DETAIL%':$this->page->full_script_path.'?category=%cname%').'">'.($product_search?'%name%':'%cname%').'</a></b></p>
						<span class="rvts8 livesearch_content">%COPY('.($product_search?'%html_description%':'%cdescription%').',1,90)%...</span>
						</LISTER_BODY>'
						.'<br><a class="rvts12 livesearch_link" id="livesearch_link'.$livesearch_id.'" data-stype="'.($product_search?'category_search':'product_search').'" href="javascript:void(0);">'.$this->page->lang_l($product_search?'categories results':'full results').'</a>';
			}
			else
				 $page=$this->page->get_pagetemplate($this->page->pg_settings['g_cid'].".html");

			if(($search_type=='')&&(strpos($searchstring,'"') !== false))
			{
				$search_type='exact';
				$searchstring=str_replace('"','',$searchstring);
			}
			$searchstring_words=explode(" ",Formatter::strToLower($searchstring));

			if(empty($cat_name))
			{
				 $que='
					SELECT d.*, c.cname, c.description as cdescription, c.image1 as cimage
					FROM '.$db->pre.$this->page->g_datapre.'data as d
					LEFT JOIN '.$db->pre.$this->page->g_datapre.'categories as c ON c.cid=d.cid
					WHERE d.publish=1'.($this->page->use_publish_dates?' AND if((d.published_date<>0 AND d.unpublished_date<>0 AND d.unpublished_date>=d.published_date AND d.unpublished_date>=NOW() AND d.published_date<=NOW()) OR (d.published_date=0 AND d.unpublished_date=0) OR (d.published_date<>0 AND d.unpublished_date=0 AND d.published_date<=NOW()),1,0)=1':'')
					.($this->page->g_use_rel?' ORDER BY d.rel DESC':'');
			}
			else
			{
				$que_range=array();
				foreach($this->page->categoriesModule->category_array as $k=>$v)
				{
					$cname=isset($v['linked'])?$v['linked']['name']:$v['name'];
					if(in_array($cname,$cat_name))
						$que_range[]=$v['id'];
		 		}
				$que='
					 SELECT d.*, c.cname, c.description as cdescription, c.image1 as cimage
					 FROM '.$db->pre.$this->page->g_datapre.'data as d
					 LEFT JOIN '.$db->pre.$this->page->g_datapre.'categories as c ON c.cid=d.cid
					 WHERE d.cid IN ('.implode(',',$que_range).')
					 AND d.publish=1'.($this->page->use_publish_dates?' AND if((d.published_date<>0 AND d.unpublished_date<>0 AND d.unpublished_date>=d.published_date AND d.unpublished_date>=NOW() AND d.published_date<=NOW()) OR (d.published_date=0 AND d.unpublished_date=0) OR (d.published_date<>0 AND d.unpublished_date=0 AND d.published_date<=NOW()),1,0)=1':'')
					 .($this->page->g_use_rel?' ORDER BY d.rel DESC':'');
			}

			$data=$this->search_indb($page,$que,$search_type,$searchstring,$searchstring_words,$search_subtype,$category_names,$product_search);
			$page=str_replace(array('%LISTER_PRODUCTS','%COMPARE%'),
				array('%SHOP_PRODUCTS',$this->page->featuresModule->compare_list()),$page);
			$page=Builder::buildLoggedInfo($page,$this->page->pg_id,$this->page->rel_path);

			$page_head=Formatter::GFS($page,'','<LISTER_BODY>');
			$page_foot=Formatter::GFS($page,'</LISTER_BODY>','');
			$page_body=Formatter::GFS($page,'<LISTER_BODY>','</LISTER_BODY>');
			$page=$page_head;
			$page=str_replace(Formatter::GFSAbi($page,'<CATEGORY_HEADER>','</CATEGORY_HEADER>'),'',$page);
			$category_section2=Formatter::GFSAbi($page,'<CATEGORIES_BODY>','</CATEGORIES_BODY>');
			if($category_section2!='')
				$page=str_replace($category_section2,$this->page->parse_cat_list($category_section2),$page);
			$product_select='';

			$count=count($data);
			if($this->page->pg_settings['g_catpgmax']==0)
				$this->page->pg_settings['g_catpgmax']=$count;
			$nav_params=Formatter::GFS($page_head.$page_foot.$page_body,'%NAVIGATION(',')%');
			$nav=Navigation::page($count,$this->page->g_abs_path.$this->page->pg_name.'?q='.$searchstring,$this->page->pg_settings['g_catpgmax'],$this->page->g_pagenr,$this->page->pg_settings['lang_u']['of'],'nav',$this->page->nav_labels,'&amp;','',false,false,'',false,$nav_params);
			if($this->page->g_pagenr==0)
				$this->page->g_pagenr=1;
			$start=($this->page->g_pagenr*$this->page->pg_settings['g_catpgmax'])-$this->page->pg_settings['g_catpgmax'];
			$end=($this->page->g_pagenr*$this->page->pg_settings['g_catpgmax']);
			$end=($end>$count)?$count:$end;
			$counter=$start+1;

			$ps_key=Formatter::GFS($page,'%SHOP_PRODUCTS(%','%)%');
			$product_select='';
			$data=array_slice($data,$start,$end-$start,true);
			$page.=$this->page->category_table($counter,$data,$page_body,$ps_key,$product_select);

			if($counter==1)
			{
				$searchnomatch=str_replace('%SEARCHSTRING%','<b><i>'.$searchstring.'</i></b>','<div class="search_no_match">'.$this->page->pg_settings['lang_u']['search not found'].'</div>');
				$page=$page.$searchnomatch;
			}
			$page.=$page_foot;
			$page=str_replace(array('%NAVIGATION%','%NAVIGATION('.$nav_params.')%'),$nav,$page);
			$page=$this->page->build_category_lists(0,$page);

			$page=$this->page->parse_minicart($page,1,$this->page->g_pagenr);

			if($ps_key!='')
			{
				$product_select='
				 <select class="input1 prolist" name="prolist" onchange="javascript: location.href=this.options[this.selectedIndex].value;return true;">'
					  .$product_select.'
				 </select>';
				$page=str_replace('%SHOP_PRODUCTS(%'.$ps_key.'%)%',$product_select,$page);
			}
			if(strpos($page,'%SHOP_CART%') !== false)
			{
				$cartstring=$this->page->cart('show',0,'',$this->page->g_pagenr,$searchstring,'','','','',0,'','');
				$page=str_replace('%SHOP_CART%','<div id="shop_cart">'.$cartstring.'</div>',$page);
			}
			$page=str_replace('%CURRENCY%',$this->page->pg_settings['g_currency'],$page);
			$this->page->parse_page_id($page);
			$page=$this->page->parse_categories($page);

			if($this->page->global_pagescr !== '')
				 $page=str_replace('<!--scripts-->','<!--scripts-->'.$this->page->global_pagescr,$page);
			$page=$page.'<!--<pagelink>/'.$this->page->g_abs_path.$this->page->pg_name.'?q='.$searchstring.'&amp;page='.$this->page->g_pagenr.'</pagelink>-->';
			$page=str_replace(array('%SUBCATEGORIESCOMBO%','%SUBCATEGORIES%','%SUBCATEGORIES_VER%','%ORDERBY_COMBO%','%category%','%subcategory%'),'',$page);
			$page=$this->parse_searchmacros($page);
			if(strpos($page,'<RANDOM>') !== false)
			{
				$items_a=$db->fetch_all_array('
					SELECT pid, 0 as "used"
					FROM '.$db->pre.$this->page->g_datapre.'data
					WHERE '.$this->page->where_public);
				$page=$this->page->replace_random($page,$items_a);
			}
			$page=$this->page->recentlyViewedModule->parse_recently_viewed($page);
			$page=$this->page->finalize_output($page);
	//filters are not handled in search yet
			if(strpos($page,'<FILTERS>')!==false)
				$page=$this->page->filtersModule->clearfiltersOnPage($page);
			else
			{
				$match=array();
				$occurances=preg_match_all("/FILTER\(\%(.*)\%/Ui",$page,$match);
				if($occurances>0)
				{
					$all_ffields=$match[1];
					foreach($all_ffields as $f)
						$page=str_replace('%FILTER(%'.$f.'%)%','',$page);
				}
			}

			$this->page->ReplaceMacros($page);
			$page=str_replace(array(Formatter::GFSAbi($page,'<title>','</title>'),'%cname%','%description%'),
					  array('<title>'.$this->page->pg_settings['lang_u']['search'].'</title>','',''),
					  $page);
			if($this->page->livesearch)
				echo $page;
			else
				return $page;
		}
		else
		{
			$url=$this->page->g_abs_path.$this->page->pg_name;
			Linker::redirect($url,false);
		}
	}
}

class ListerFunctions
{
	public static function GetSubKey($fv)
	{
		$result=$fv;
		if(strpos($fv,'=')!==false)
		{
			 $t=explode("=",$fv);
			 $result=$t[0];
		}
		return $result;
	}

	public static function keyArray($ar,$key)
	{
		$res=array();
		foreach($ar as $v)
			$res[$v[$key]]=$v;
		return $res;
	}

	public static function parse_button($id,$itemline,$btn_url,$is_bundle,$is_primary=false,$bundle_id='')
	{
		$btn_string=Formatter::GFS($itemline,'<'.$id.'>','</'.$id.'>');
		$img_src=Formatter::GFS($btn_string,'src="','"');
		$url=$id=='SHOP_DELETE_BUTTON'?'javascript:void(0);" onclick="remove_item_ajax(\''.$btn_url.'\''.($is_bundle?', '.$bundle_id:'').');" '.($is_bundle&&!$is_primary?'style="display:none"':'').'':$btn_url.'" ';
		$btn_parsed=$img_src==''?str_replace('%URL%"',$url,$btn_string):'<a href="'.$url.'><img src="'.$img_src.'" align="bottom" alt=""></a>';
		return str_replace('<'.$id.'>'.$btn_string.'</'.$id.'>',$btn_parsed,$itemline);
	}

	public static function replace_lister_tags($src)
	{
		$src=str_ireplace(
				  array('<SHOP_BODY>','</SHOP_BODY>','<SHOP>','</SHOP>','%SHOP_CARTCURRENCY%'),
				  array('<LISTER_BODY>','</LISTER_BODY>','<LISTER>','</LISTER>','%CURRENCY%'),
				  $src);

		$src=Images::parse_scale_macros($src);
		return $src;
	}

	public static function strposReverse($str,$search,$pos)
	{
		$str=strrev($str);
		$search=strrev($search);
		$pos=(strlen($str)-1)-$pos;
		$posRev=strpos($str,$search,$pos);
		return (strlen($str)-1)-$posRev-(strlen($search)-1);
	}

	public static function is_valid_luhn($number)
	{
		settype($number,'string');
		$sumTable=array(array(0,1,2,3,4,5,6,7,8,9),array(0,2,4,6,8,1,3,5,7,9));
		$sum=0;
		$flip=0;
		for($i=strlen($number)-1;$i>=0;$i--)
			$sum+=$sumTable[$flip++ & 0x1][$number[$i]];

		return $sum % 10 === 0;
	}

	public static function HtmlToText($src)
	{
		$isText=true;
		$result='';
		for($i=1;$i<strlen($src);$i++)
		{
			if($src[$i]=='<')
				$isText=false;
			elseif($src[$i]=='>')
				$isText=true;
			elseif($isText)
				$result.=$src[$i];
		}
		$result=str_replace(array('&nbsp','P_ImageFull'),'',$result);
		return $result;
	}

	public static function CountHash($src)
	{
		$src=str_replace(" ","",$src);$src=str_replace("\t","",$src);
		$src=str_replace("\n","",$src);
		$src=str_replace("&amp;","&",$src);
		$src=str_replace("&lt;","<",$src);
		$src=str_replace("&gt;",">",$src);
		$src=str_replace("&quot;","\"",$src);
		return sha1($src);
	}

	public static function DivMod($num,$tel,&$Res,&$Rem)
	{
		$Res=floor($num / $tel);
		$Rem=$num-($Res*$tel);
	}

	public static function DecDate($days,&$Year,&$Month,&$Day)
	{
		$D1=365;$D4=($D1*4)+1;$D100=($D4*25)-1;$D400=($D100*4)+1;
		$MonthDays=array(array(31,28,31,30,31,30,31,31,30,31,30,31),array(31,29,31,30,31,30,31,31,30,31,30,31));
		$days+=693594;$days--;$Y=1;
		while($days >= $D400)
		{
			$days-=$D400;
			$Y+=400;
		}
		$I=0;$D=0;
		self::DivMod($days,$D100,$I,$D);
		if($I==4)
		{
			$I++;
			$D+=$D100;
		}
		$Y+=$I*100;self::DivMod($D,$D4,$I,$D);$Y+=$I*4;self::DivMod($D,$D1,$I,$D);
		if($I==4)
		{
			$I--;
			$D+=$D1;
		}
		$Y+=$I;

		$leap=($Y % 4 == 0)&&(($Y % 100 <> 0)||($Y % 400 == 0));
		$DayTable=$MonthDays[$leap];$M=1;
		while(true)
		{
			$I=$DayTable[$M-1];
			if($D<$I) break;
			$D-=$I;$M++;
		}
		$Year=$Y;$Month=$M;$Day=$D+1;
	}
}

class Filters extends page_objects
{
	protected $g_filters;
	protected $cookieId;
	protected $tempFilterCookie='';
	public $useajax;

	public function __construct($pg)
	{
		parent::__construct($pg);
		$this->cookieId=$this->page->pg_id.'_filters';
	}

	public function initFilters()
	{
		$this->g_filters=array();
		if($this->tempFilterCookie!='')  //image-line
			$_COOKIE[$this->cookieId]=$this->tempFilterCookie;

		if(isset($_COOKIE[$this->cookieId]))
		{
			$this->g_filters=unserialize(base64_decode($_COOKIE[$this->cookieId]));

			if($this->g_filters['-']==$this->page->g_catid.'_'.($this->page->tag_object!=''?$this->page->tag_object->tag:''))
				unset($this->g_filters['-']);
			else
				$this->g_filters=array();
		}
	}

	public function cleanFilters()
	{
		setcookie($this->cookieId,'',time()-3600,'/');
	}

	public function updateFilters($fname,$fn_id,$cid)
	{
		if(is_array($fname))
		{
			 $_REQUEST['features']=array();
			 foreach($fname as $fn)
				  $_REQUEST['features'][]=$_REQUEST[$fn][0];
			 $fname='features';
		}

		if(array_key_exists($fname,$this->page->pg_settings['g_fields_array']) || $fname=='features')
		{
			$fitype=$fname=='features'?'features':$this->page->pg_settings['g_fields_array'][$fname]['itype'];
			$old_filter=array();
			if(isset($this->g_filters[$fname]))
			{
				$old_filter=$this->g_filters[$fname];
				unset($this->g_filters[$fname]);
			}
			if(isset($_REQUEST[$fname]))
			{
				foreach($_REQUEST[$fname] as $v)
				{
					if(strpos($v,'|all')===false)
					{
						if(isset($this->g_filters[$fname]))
						  $this->g_filters[$fname][$v]=$fn_id;
						else
						  $this->g_filters[$fname]=array($v=>$fn_id);
					}
				}
			}
			elseif($fitype=='price')
			{
				if(isset($_REQUEST[$fname.'_min'])&&isset($_REQUEST[$fname.'_max']))
				{
					if(isset($this->g_filters[$fname]))
					{
						$this->g_filters[$fname]['min']=intval($_REQUEST[$fname.'_min']);
						$this->g_filters[$fname]['max']=intval($_REQUEST[$fname.'_max']);
					}
					else
						$this->g_filters[$fname]=array('min'=>intval($_REQUEST[$fname.'_min']),'max'=>intval($_REQUEST[$fname.'_max']));
				}
			}
			//remove lower filters
			if(!$this->page->get_setting('cross_filters') &&	isset($this->g_filters[$fname]) && count($old_filter)>0 && count($old_filter)>count($this->g_filters[$fname]))
			{
				foreach($this->g_filters as $fn=>$filter)
				{
					if($fn!=$fname && reset($filter)>$fn_id)
						unset($this->g_filters[$fn]);
				}
			}

			$parent=-1;$sname='';
			$cname=$this->page->categoriesModule->get_category_name($cid,$parent);
			if($parent>-1)
			{
	   		$sname=$cname;
	   		$cname=$this->page->categoriesModule->get_category_name($parent,$parent);
			}

			$this->g_filters['-']=$cid.'_'.($this->page->tag_object!=null?$this->page->tag_object->tag:'');
			$this->tempFilterCookie=base64_encode(serialize($this->g_filters));
			setcookie($this->cookieId,$this->tempFilterCookie,time()+3600*24,'/');
			unset($this->g_filters['-']);
		}
	}

	public function get_filterSubQuery($filters,&$cnt)
	{
		$q='';
		$fa=array();
		foreach($filters as $k=>$v)
		{
		  $fv=explode('|',$k);
		  $vs=addslashes($fv[1]);
		  isset($fa[$fv[0]])?$fa[$fv[0]][]=$vs:$fa[$fv[0]]=array($vs);
		}
		foreach($fa as $k=>$v)
		  $q.=($q==''?'':' OR ').'( op.pr_id ='.intval($k).' AND value in ("'.(implode('" , "',$v)).'" )) ';

		$cnt=count($fa);
		return $q;
	}

	public function get_filterQue($include_fields=array(),$ignore_features=false)
	{
		global $db;
		$que='';
		if(!empty($this->g_filters))
		{
			foreach($this->g_filters as $f=>$filters)
			{
				$fname=$f;
				$pque='';
				$ftype=$fname=='features'?'features':$this->page->pg_settings['g_fields_array'][$fname]['itype'];
				if(empty($include_fields) || array_search($fname,$include_fields)!==false)
				{
					if($ftype=='price')
					{
						$pque.=($pque==''?'':' AND ').' ( '.$fname.' >= '.$filters['min'].' AND '.$fname.' <= '.$filters['max'].' )';
					}
					elseif($ftype=='features')
					{
						if(!$ignore_features)
						{
							$cnt=0;
							$q=$this->get_filterSubQuery($filters,$cnt);
							$pque.=($pque==''?'':' AND ').'( pid in (SELECT pid FROM '.$db->pre.$this->page->g_datapre.'options op
							 WHERE '.$q.'
							 GROUP BY pid
							 HAVING COUNT(*) >= '.$cnt.' ) ) ';
						}
					}
					else
					foreach($filters as $k=>$v)
					{
						$fvalue=addslashes(urldecode($k));
						if($fvalue=='all')
						  continue;
						if($ftype=='subname')
						  $pque.=($pque==''?'':' OR ').' ( CONCAT('.$fname.',";") LIKE "%'.$fvalue.';%" )';
						elseif(array_key_exists($fname,$this->page->pg_settings['g_fields_array']) && $fvalue!==$this->page->lang_l('none'))
						  $pque.=($pque==''?'':' OR ').$fname.'="'.$fvalue.'"';
						elseif($fvalue==$this->page->lang_l('none'))
						  $pque.=($pque==''?'':' OR ').$fname.'=""';
					}
				}
				if($pque!='' )
					$que.=' AND ('.$pque.')';
			}
		}

		return $que;
	}

	public function clearfiltersOnPage($page)
	{
		$filters=Formatter::GFSAbi($page,'<FILTERS>','</FILTERS>');
		$page=str_replace($filters,'',$page);
		return $page;
	}

	public function get_featuresfilters($cid,&$data,&$featIds)
	{
	 	$filters=array();
		if(isset($this->g_filters['features']))
			$filters=$this->g_filters['features'];

		$filterType=$this->page->get_setting('features_type');

		$features=$this->page->featuresModule->get_categoryfeatureOptions($cid,$filters);
		$result='';

		foreach($features as $pr_id=>$v)
		{
		  $result.='
				<div class="ct_feature_container">
					<div class="ct_feature_innercontainer">
					 <p class="ct_feature"><span>'.$v[0].'</span></p>';
		  if($filterType==F_DROPDOWN)
				$result.='<select id="fe_'.$pr_id.'" class="ffilter_list" name="features[]" onchange="handleFilters(\'#ff_features\')">
					 <option value="'.$pr_id.'|all" class="forminput select_option">'
									.$this->page->lang_l('all').'
								</option>';
		  else
		  {
				$result.='
					 <ul style="list-style:none" id="fe_'.$pr_id.'" class="ffilter_list">';
				if($filterType==F_RADIO)
				{
					 $featIds[]='features'.$pr_id;
					 $result.='
						<li>
						  <label>
							<input type="radio" class="ffilter_opt" id="inp_opt_'.$pr_id.md5(strtolower('all')).'" name="features'.$pr_id.'[]" value="'.$pr_id.'|all" checked="checked">
								<span id="lab_opt_'.$pr_id.md5(strtolower('all')).'" class="ffilter_label">'.$this->page->lang_l('all').'</span>
						  </label>
						</li>';
				}
		  }
		  $filterLimit=$this->page->get_setting('filters_limit');
		  if(isset($counts))
				unset($counts);
		  if($filterLimit>0 && $filterLimit<count($v[1]))
		  {
				$counts=array();
				foreach($v[1] as $op_value=>$products)
					 $counts[$op_value]=count($products);
				asort($counts,SORT_NUMERIC);
				$counts=array_slice($counts,0,count($counts)-$filterLimit,true);
		  }

		  foreach($v[1] as $op_value=>$products)
		  {
				$opt_id=$pr_id.md5(strtolower($op_value));

				if($filterType==F_DROPDOWN)
				{
					 $desc=($op_value==''?$this->page->lang_l('none'):$op_value).' ('.count($products).')';
					 $data.=$opt_id.':'.count($products).':'.$desc.'|';
					 $result.='<option id="inp_opt_'.$opt_id.'" '.(isset($filters[$pr_id.'|'.$op_value])?' selected=selected':'').' value="'.$pr_id.'|'.$op_value.'" class="forminput select_option">'
									.$desc.'
								</option>';
				}
				else
				{
					 $data.=$opt_id.':'.count($products).'|';
					 $result.='
						<li'.(isset($counts) && array_key_exists($op_value,$counts)?' class="f_hidden hidef"':'').'>
						  <label>';
					 if($filterType==F_CHECKBOX)
						  $result.='
							<input type="checkbox" class="ffilter_opt" id="inp_opt_'.$opt_id.'" onchange="handleFilters(\'#ff_features\')" name="features[]" value="'.$pr_id.'|'.$op_value.'"'.(isset($filters[$pr_id.'|'.$op_value])?' checked=checked':'').'>';
					 elseif($filterType==F_RADIO)
						  $result.='
							<input type="radio" class="ffilter_opt" id="inp_opt_'.$opt_id.'" onchange="handleFilters(\'#ff_features\')" name="features'.$pr_id.'[]" value="'.$pr_id.'|'.$op_value.'"'.(isset($filters[$pr_id.'|'.$op_value])?' checked=checked':'').'>';
					 $result.='
						  <span id="lab_opt_'.$opt_id.'" class="ffilter_label">'.($op_value==''?$this->page->lang_l('none'):$op_value).' <span class="ffilter_braces">(</span><span class="ffilter_cnt" id="sp_opt_'.$opt_id.'">'.count($products).'</span><span class="ffilter_braces">)</span></span>
						  </label>
						</li>';
				}
		  }
		  if($filterType==F_DROPDOWN)
				$result.='</select>';
		  else
		  {
				if(isset($counts))
					 $result.='<a href="javascript:void(0);" class="rvts4 toggle_options" onclick="$(\'#fe_'.$pr_id.' .f_hidden\').toggleClass(\'hidef\');"><span class="f_hidden">'.$this->page->lang_l('more').'</span><span class="f_hidden hidef">'.$this->page->lang_l('less').'</span></a>';
				$result.='</ul>';
		  }
			$result.='</div></div>';
		}
		//$result.=($result==''?'':'<button type="button" onclick="handleFilters(\'#ff_features\')">'.$this->page->lang_l('apply').'</button>');
		return $result;
	}

	public function replace_filters($page,$cid,$where,&$fdata)
	{
		global $db;

		$result=$page;
		$this->useajax=false;
		$include_fields=array();
		$fn_id=0;
		$js='';
		$cross=$this->page->get_setting('cross_filters');
		$include_clickjs=false;
		if($this->page->tag_object!=null && $cid===false)
			$cid='all';
		if($cid===false || ($cid=='all' && $this->page->get_setting('fullview_cf')=='1' && strpos($result,'<FILTERS>') !== false))
			$result=$this->clearfiltersOnPage($result);
		if($this->page->tag_object!=null)
			$act=$this->page->tag_object->build_permalink();
		else
			$act=$this->page->build_permalink_cat($cid,false);
		$featIds=array();

		$occurances=preg_match_all("/FILTER\(\%(.*)\%/Ui",$result,$match);
		$color_picker=false;
		if(!isset($_REQUEST['iid'])&&$this->page->all_settings['use_ajax'])
			$this->useajax=true;

		if($occurances>0)
		{
			$all_ffields=$match[1];
			foreach($all_ffields as $fn_id=>$featureName)
			{
				$f=Formatter::GFS($featureName,'','|');
				if($f!='features' && array_key_exists($f,$this->page->pg_settings['g_fields_array'])===false)
					 unset($all_ffields[$fn_id]);
			}

			foreach($all_ffields as $fn_id=>$featureName)
			{
				$f=Formatter::GFS($featureName,'','|');
				$feval=$f!=$featureName?Formatter::GFS($featureName,$f,''):false;
				$fmultiplier=floatval($this->page->all_settings['conversion_rate']);
				if($feval!==false)
				{
					 $fcurrency=Formatter::GFS($feval,'|','');
					 $fmultiplier='%'.$fcurrency.'%';
					 $this->page->replaceExchangeRates($fmultiplier);
				}
				$fitype=$f=='features'?'features':$this->page->pg_settings['g_fields_array'][$f]['itype'];
				$list='';

				if($fitype!='features')
				{
					 $fq=empty($include_fields)||$cross?'':$this->get_filterQue($include_fields);
					 $include_fields[]=$f;
					 $other_fields=$all_ffields;unset($other_fields[$fn_id]);
					 $ccs='';
					 foreach($other_fields as $k=>$fnameOther)
					 {
						  $v=Formatter::GFS($fnameOther,'','|');
						  if($v!='features')
								$ccs.=' GROUP_CONCAT('.$v.') as c_'.$v.',';
					 }
					 if($fitype=='price')
						  $que='
								SELECT max('.$f.') as '.$f.', min('.$f.') as min_'.$f.', '.$ccs.' COUNT(*) as cnt
								FROM '.$db->pre.$this->page->g_datapre.'data d
								WHERE '.$where.$fq;
					 else
						  $que='
								SELECT '.$f.', '.$ccs.' COUNT(*) as cnt
								FROM '.$db->pre.$this->page->g_datapre.'data d
								WHERE '.$where.$fq.'
								GROUP BY '.$f.'
								ORDER BY '.$f;
					 $data=$db->fetch_all_array($que);
				}

				if($fitype=='price')
				{
					$max=ceil($data[0][$f]);
					$min=ceil($data[0]['min_'.$f]);
					$sl_id='slider-range'.$f;
					$list.='<div style="padding:4px 8px;">
					<p style="padding-bottom:2px"><span id="amount" class="rvts8"></span></p>
					<div id="'.$sl_id.'"></div>
					<input type="hidden" value="0" name="'.$f.'_min" id="'.$f.'_min"><input type="hidden" value="'.$data[0][$f].'" name="'.$f.'_max" id="'.$f.'_max">
					</div>';
					$minval=(isset($this->g_filters[$f])?($this->g_filters[$f]['min']):$min);
					$maxval=(isset($this->g_filters[$f])?($this->g_filters[$f]['max']):$max);

					if($maxval>$max)
						$maxval=$max;
					if($minval<$min)
						$minval=$min;

					$js='
						var fmultiplier='.$fmultiplier.';
						function sliderCaption(min,max){
							min=Math.round(parseFloat(min*fmultiplier));
							max=Math.round(parseFloat(max*fmultiplier));
							$("#amount").html(min+" - "+max + " '.($feval!==false?$fcurrency:$this->page->pg_settings['g_currency']).'");
						}
						$(function() {
						  $("#'.$sl_id.'").slider({
								range: true,min: '.$min.',max: '.$max.',values:['.$minval.','.$maxval.'],
								slide: function(event,ui) {
									sliderCaption(ui.values[0],ui.values[1]);
									$("#'.$f.'_min").val(ui.values[0]);
									$("#'.$f.'_max").val(ui.values[1]);
								},
								change: function(event, ui) {
									 handleFilters(\'#ff_'.$f.'\');
								}
						  });
						  sliderCaption('.$minval.','.$maxval.');
						});';

					$fdata[$f]='eval|slider|#'.$sl_id.'|'.$min.'|'.$max.'|'.$minval.'|'.$maxval.'|';
				}
				elseif($fitype=='features')
				{
					 $include_clickjs=true;
					 $include_fields[]=$f;
					 $dt='';
					 $list='
						 <li>
						 '.$this->get_featuresfilters($cid,$dt,$featIds).'
						 </li>';
					 $fdata[$f]='eval|'.$dt;
				}
				else
				{
					$include_clickjs=true;
					$fvalues=array();
					foreach($data as $k=>$v)
					{
						if($v[$f]=='')
						{
							$v[$f]=$this->page->lang_l('none');
							$temp=$v;

							unset($data[$k]);
							$data[]=$temp;
							if($fitype!='subname')
								 break;
						}
						elseif($fitype=='subname')
						{
							$v_a=explode(';',$v[$f]);
							foreach($v_a as $va)
							{
								 $va=trim($va);
								 if(isset($fvalues[$va]))
									 $fvalues[$va]=$fvalues[$va]+$v['cnt'];
								 else
									 $fvalues[$va]=$v['cnt'];
							}
						}
						$color_picker=strpos($v[$f],'})')!==false;
					}
					$disabled=false;
					$single_option=count($data)==1;
					$filterType=$this->page->get_setting('filters_type');
					$dropdown_list = '';

					foreach($data as $k=>$v)
					{
						//checking filter dependencies
						$disabled=false;
						if(!$cross)
						foreach($all_ffields as $xfn_id=>$xf)
						{
							if(($xfn_id>$fn_id ) && isset($this->g_filters[$xf]) && $this->page->pg_settings['g_fields_array'][$xf]['itype']!='price')
							{
								$disabled=true;
								foreach($this->g_filters[$xf] as $xv=>$xk)
								{
									$options_for_field=explode(',',$v['c_'.$xf]);
									if(array_search($xv,$options_for_field)!==false || $xv=='all')
										$disabled=false;
								}
							}
						}
						if(count($fvalues)==0)
						{
							if($single_option)
								$list.='<li class="filter_no_options">
											<input type="hidden" name="'.$f.'[]" value="'.urlencode($v[$f]).'">
											<span onclick="spanClick(this)" class="ffilter_label rvts8">'.$v[$f].' <span>('.$v['cnt'].')</span></span>
										</li>';
							elseif($filterType==F_CHECKBOX)
								$list.='<li>
											<input '.($disabled?'disabled=""':'').' onchange="handleFilters(\'#ff_'.$f.'\');" type="checkbox" '.(isset($this->g_filters[$f][urlencode($v[$f])])?'checked="checked"':'').' name="'.$f.'[]" value="'.urlencode($v[$f]).'" class="forminput">
											<span onclick="spanClick(this)" class="ffilter_label rvts8">'.$v[$f].' <span>('.$v['cnt'].')</span></span>
										</li>';
							elseif($filterType==F_DROPDOWN)
								$dropdown_list.='<option '.($disabled?'disabled=""':'').' '.(isset($this->g_filters[$f][urlencode($v[$f])])?'selected="selected"':'').' value="'.urlencode($v[$f]).'" class="forminput select_option">
											<span onclick="spanClick(this)" class="ffilter_label rvts8">'.$v[$f].' <span>('.$v['cnt'].')</span></span>
										</option>';
							else
								$list.='<li>
											<input '.($disabled?'disabled=""':'').' onchange="handleFilters(\'#ff_'.$f.'\');" type="radio" '.(isset($this->g_filters[$f][urlencode($v[$f])])?'checked="checked"':'').' name="'.$f.'[]" value="'.urlencode($v[$f]).'" class="forminput">
											<span onclick="spanClick(this)" class="ffilter_label rvts8">'.$v[$f].' <span>('.$v['cnt'].')</span></span>
										</li>';
						}
					}
					if(!$single_option && !$filterType==F_CHECKBOX)
					{
						if($filterType==F_DROPDOWN)
							$dropdown_list='<option value="all" '.(!isset($this->g_filters[$f]) || isset($this->g_filters[$f]['all'])?'selected="selected"':'').' class="forminput"><span class="rvts8">'.$this->page->lang_l('all').'</span></option>'.$dropdown_list;
						else
						  $list='
								<li>
									<input onchange="handleFilters(\'#ff_'.$f.'\')" type="radio" name="'.$f.'[]" value="all" '.(!isset($this->g_filters[$f]) || isset($this->g_filters[$f]['all'])?'checked="checked"':'').' class="forminput">
									<span onclick="spanClick(this)" class="ffilter_label rvts8">'.$this->page->lang_l('all').'</span>
								</li>'.$list;
					}
					if(count($fvalues) > 0)
					{
						$c_values=0;
						foreach($fvalues as $k=>$v)
						{
							list($c_name,$c_code)=$color_picker?array_pad(explode('{',str_replace(array('(','})'),'',$k)),2,''):array('','');
							$is_op_inc_1st=($this->page->get_setting('op_inc_1st')&&$c_values>0)||!$this->page->get_setting('op_inc_1st');
							if($filterType==F_CHECKBOX)
								$list.='<li>
										'.($is_op_inc_1st?'<input '.($disabled?'disabled=""':'').' onchange="handleFilters(\'#ff_'.$f.'\');" type="checkbox" '.(isset($this->g_filters[$f][urlencode($k)])?'checked="checked"':'').' name="'.$f.'[]" value="'.urlencode($k).'" class="forminput">':'').
											'<span onclick="spanClick(this)" class="ffilter_label rvts8"><span '.($c_code!=''?' style="background-color:#'.$c_code.'; width:10px; height:10px; display: inline-block; margin-right: 2px;"':'').'></span>'.($c_name!=''?$c_name:$k).($is_op_inc_1st?' ('.$v.')':'').'</span>
										</li>';
							elseif($filterType==F_DROPDOWN){
								$drop_option='<option '.($disabled||(!$is_op_inc_1st&&$c_values==0)?'disabled=""':'').($is_op_inc_1st?' onchange="handleFilters(\'#ff_'.$f.'\');" ':'').(isset($this->g_filters[$f][urlencode($k)])?'selected="selected"':'').' value="'.urlencode($k).'" class="forminput select_option"'.($color_picker?' data-style="background-color:#'.$c_code.'"':'').'>
											<span onclick="spanClick(this)" class="ffilter_label rvts8">'.($c_name!=''?$c_name:$k).($color_picker&&$c_values==0?'':($is_op_inc_1st?' ('.$v.')':'')).'</span>
										</option>';
								$dropdown_list=($color_picker&&$c_values==0)||!$is_op_inc_1st?$drop_option.$dropdown_list:$dropdown_list.$drop_option;
							}
							else{
								$radio='<li>
										'.($is_op_inc_1st?'<input '.($disabled?'disabled=""':'').' onchange="handleFilters(\'#ff_'.$f.'\');" type="radio" '.(isset($this->g_filters[$f][urlencode($k)])?'checked="checked"':'').' name="'.$f.'[]" value="'.urlencode($k).'" class="forminput">':'').
											'<span onclick="spanClick(this)" class="ffilter_label rvts8"><span '.($c_code!=''?' style="background-color:#'.$c_code.'; width:10px; height:10px; display: inline-block; margin-right: 2px;"':'').'></span>'.($c_name!=''?$c_name:$k).($is_op_inc_1st?' ('.$v.')':'').'</span>
										</li>';
								$list=$is_op_inc_1st?$list.$radio:$radio.$list;
							}
							$c_values++;
						}
 					}
					if($filterType==F_DROPDOWN)
					{
						$list .= '
							<li>
								<select class="'.($color_picker?'niceSelect ':'').'cat_filters_selector" name="'.$f.'[]"'.($color_picker?' multiple':'').' onchange="handleFilters(\'#ff_'.$f.'\');">'
								.$dropdown_list.'
								</select>
							</li>';
					}

					$list='<ul class="filter" style="list-style-type:none">
									 '.$list.'
							 </ul>';
				}
				$form='
				<form id="ff_'.$f.'" action="'.$act.'" method="post">
					<input type="hidden" name="r" value="'.Linker::buildReturnURL(false).'">
					<input type="hidden" name="action" value="'.($this->useajax?'filterbyAjax':'filterby').'">';
				if(!empty($featIds))
					 foreach($featIds as $fname)
						  $form.='
								<input type="hidden" name="fn[]" value="'.$fname.'">';
				else
					$form.='
					<input type="hidden" name="fn" value="'.$f.'">';
				$form.='
					<input type="hidden" name="fn_id" value="'.$fn_id.'">
					<ul class="filter" style="list-style-type:none">'.
					$list.'
					</ul>
				</form>';
				if($fitype!='price' && $f!='features')
	 				$fdata[$f]=$form;
				$result=str_replace('%FILTER(%'.$featureName.'%)%',$form,$result);
			}
		}

		$ajaxFast=$this->page->internal;

		$js.=(!$this->useajax)?'
function handleFilters(formId){
	$(formId).submit();
}
':'
var updateOn=1;

function handleFilters(formId){
	if(!updateOn) return;
	frm=$(formId);

	$.getJSON(frm.attr("action")+"'.($this->page->use_alt_plinks?'?':'&').'"+frm.serialize(),function(d){'.
	($ajaxFast?'':'
	$("#products_list").replaceWith(d["page"]);
	if(d["page"].indexOf(\'class="mbox\')!=-1)
		$("#products_list .mbox").multibox({heff:false});
	$("#products_list .ranking").ranking({rsw:55});').'
	if(typeof updateOnAjax == "function") {
		updateOnAjax(1);
	}'
	.($ajaxFast?'
	$(".category_item").hide();
	$.each(d["products"], function(i,v) {
		$("#product_ct_"+v).show();});':'').'
		$(".page_nav").html(d["nav"]);
		$.each(d["data"], function(i,v) {
			if(v.indexOf("eval|")==0)
			{
				r=v.split("|");
				if(r[1]=="slider"){
					updateOn=0;
					min=parseInt(r[3]);max=parseInt(r[4]);
					vmin=parseInt(r[5]);vmax=parseInt(r[6]);
					$(r[2]).slider("option","min",min);
					$(r[2]).slider("option","max",max);
					$(r[2]).slider("option","values",[vmin,vmax] );
					updateOn=1;
					sliderCaption(vmin,vmax);
				}
				else{
					r.shift();
					for(var j=0;j<r.length;j++) {
						rv=r[j].split(":");
						if($("#inp_opt_"+rv[0]).prop("tagName")=="OPTION"){
							if(rv[1]=="0")
								$("#inp_opt_"+rv[0]).attr("disabled","disabled");
							else
								$("#inp_opt_"+rv[0]).removeAttr("disabled");
							$("#inp_opt_"+rv[0]).html(rv[2]);
						}
						else if(rv[1]=="0")
							$("#lab_opt_"+rv[0]).css("opacity",0.7).addClass("disabled");
						else
							$("#lab_opt_"+rv[0]).css("opacity",1).removeClass("disabled");
						$("#sp_opt_"+rv[0]).html(rv[1]);
					}
				}
			}
			else $("#ff_"+i).replaceWith(v);
		});
		$(".niceSelect").niceSelect({lang_l:{confirm:"'.$this->page->lang_l('confirm').'",close:"'.$this->page->lang_l('close').'"}});
	})
}
';
		if($include_clickjs)
			 $js.='
function spanClick(el){
	chb=$(el).prev();
	$(chb).prop("checked",!chb.prop("checked")).trigger("change");
}';

		$this->page->page_css.='
.ffilter_label{cursor:pointer;}
.hidef{display:none;}
.toggle_options{padding:0 0 6px 23px;display: inline-block;}';

		if($js!='')
			$result=Builder::includeScript($js,$result,
							(strpos($result,'id="slider-range')!==false)?array('jquery-ui.css','jquery-ui.min.js'):array(),
							$this->page->rel_path);
		return $result;
	}

}

class Tags extends page_objects
{
	public $tag;
	protected $parName;
	protected $fieldName;

	public function __construct($pg,$pName,$fName)
	{
		$this->parName=$pName;
		$this->fieldName=$fName;
		$this->tag=Formatter::stripQuotes(Formatter::stripTags(urldecode($_REQUEST[$this->parName])));
		parent::__construct($pg);
	}

	public function where()
	{
		 return $this->tag=='all'?$this->page->where_public:$this->fieldName.' LIKE "%'.addslashes($this->tag).'%" AND '.$this->page->where_public;
	}

	public function build_short_url($tag='')
	{
		 return $this->parName.'='.urlencode($tag==''?$this->tag:$tag);
	}

	public function build_permalink($tag='')
	{
		if($this->page->use_alt_plinks)
			$result=str_replace($this->page->pg_name,'',$this->page->script_path).$this->parName."/".urlencode($tag==''?$this->tag:$tag)."/";
		else
			$result=$this->page->script_path.'?'.$this->build_short_url($tag==''?$this->tag:$tag);

		return $result;
	}

	public function check_tag($data)
	{
		if($this->tag!='')
		{
			$this->tag=strpos($data[0][$this->fieldName],$this->tag)!==false
						?$this->tag
						:$data[0][$this->fieldName];
		}
	}
}

class item_snippets extends rich_snippets
{
	public static function parse_scope(&$src)
	{
		 rich_snippets::prepare_scope('OFFER',
					'<div'.rich_snippets::get_prop('offers').rich_snippets::get_schema('Offer').'>',
					array('CURRENCY'=>'priceCurrency','price'=>'price'),$src);
		 rich_snippets::prepare_scope('PRODUCT',
					'<div'.rich_snippets::get_schema('Product').'>',
					array('name'=>'name','html_description'=>'description'),$src);
	}
}

class listerrss extends page_objects
{
	public function handle_rss()
	{
		global $db;

		$this->page->ca_check();
		$cid=$cname='';
		$googleM=(isset($_REQUEST['type']) && $_REQUEST['type']=='googlebase');
		$rss_image=(isset($_GET['rss_image']) && array_key_exists($_GET['rss_image'],$this->page->pg_settings['g_fields_array']))?$_GET['rss_image']:'image1';
		$enclosure=(isset($_GET['enclosure']) && array_key_exists($_GET['enclosure'],$this->page->pg_settings['g_fields_array']))?$_GET['enclosure']:'';
		$hide_price=(isset($_GET['p']) && $_GET['p']=='0');
		$hide_button=(isset($_GET['b']) && $_GET['b']=='0');
		$iid=(isset($_GET['iid']))?intval($_GET['iid']):'';

		if($this->page->all_settings['rss_pattern']!='')
		{
			$pattern=$this->page->all_settings['rss_pattern'];
			$matches=array();
			preg_match_all('/%(.*)%/imU',$pattern,$matches);
			$fields=array_unique($matches[1]);
			foreach($fields as $k=>$v)
				 if(!array_key_exists($v,$this->page->pg_settings['g_fields_array']))
						unset($fields[$k]);
		}
		else
		{
			$pattern='
			<table cellpadding="8">
				<tr><td style="min-width:'.$this->page->pg_settings['rss_image_width'].'px">
					<a href="%SHOP_DETAIL%">'.'%SCALE['.'%'.$rss_image.'%,'.$this->page->pg_settings['rss_image_width'].',0]%'.'</a>
				</td>
				<td>'
					.($this->page->shop && !$hide_price?'<strong>'.$this->page->pg_settings['g_currency'].' %price%</strong>'.F_BR:'').'
					%short_description%'
					.($this->page->shop && !$hide_button?'<a href="%SHOP_DETAIL%">'.F_BR.$this->page->pg_settings['lang_u']['buy now button'].'</a>':'').'
				</td></tr>
			</table>';
			$fields=array($rss_image,'short_description');
			if($enclosure!='')
				$fields[]=$enclosure;
			if($this->page->shop)
				$fields[]='price';
		}
		if(!in_array('name',$fields))
			$fields[]='name';
		if(!in_array('pid',$fields))
			$fields[]='pid';
		if(!in_array('cid',$fields))
			$fields[]='cid';

		if($this->page->use_friendly && !in_array('friendlyurl',$fields))
			$fields[]='friendlyurl';

		$fields='d.'.str_replace('%','',implode(',d.',$fields));

		$where='';
		if($this->page->g_catid>0)
		{
			$cid=$this->page->g_catid;
			list($kids_count,$kids_ids)=$this->page->categoriesModule->get_category_kids_count($cid,true);
			$cat_ids=implode(',',array_merge(array($cid),$kids_ids));
			$parentid=-1;
			$cname=($cid!='')?$this->page->categoriesModule->get_category_name($cid,$parentid):'';
		}
		else
		{
			if($this->page->tag_object!==null)
			{
				$tag=explode("|",Formatter::unEsc(urldecode($this->page->tag_object->tag)));
				if(count($tag)>0)
					foreach($tag as $k=>$v)
						$where.=($where==''?'':' OR ').'keywords like "%'.addslashes($v).'%"';

				$where=' ( '.$where.' ) AND';
			}
		}

		$rss_where=($iid!=''? 'id='.$iid.' AND ': '')
			 .($cid!=''? ' (c.cid IN ('.$cat_ids.')) AND ':'');
		foreach($this->page->pg_settings['g_fields_array'] as $k=>$v)
			 if($k!='category' && isset($_GET[$k]))
				  $rss_where.='d.'.$k.'="'.addslashes(Formatter::stripTags($_GET[$k])).'" AND ';

		$rss_where.=$where;

		$end_query=$this->page->get_orderby(false,'d.');
		if($this->page->pg_settings['max_items_in_rss']>0)
			 $end_query=$end_query.' LIMIT 0,'.$this->page->pg_settings['max_items_in_rss'];
		$que='SELECT c.image1 as c_image1, c.cname, c.description as c_description, c.description2 as c_description2, '.$fields.'
				FROM '.$db->pre.$this->page->g_datapre.'data AS d
			   LEFT JOIN '.$db->pre.$this->page->g_datapre.'categories AS c ON c.cid=d.cid
				WHERE '.$rss_where.$this->page->where_public.$end_query;

		$data=$db->fetch_all_array($que);

		if(strpos($this->page->rel_path,'../')!==false)
			$full_path_to_script_fixed=str_replace(substr($this->page->full_script_path2,strrpos($this->page->full_script_path2,'/')),'',$this->page->full_script_path2);
		$src_pref=(strpos($this->page->rel_path,'../')===false?$this->page->full_script_path2:$full_path_to_script_fixed);
		$src_pref='';

		$rss_data=array();
		foreach($data as $k=>$v)
		{

			$title=str_replace(array('&#039;','&'),array("'",'&amp;'),Formatter::sth2($v['name']));
			$permalink=$this->page->build_permalink($v,false,'',true);
			$description=$this->page->replaceFieldsFinal(false,$v,$pattern,false,$v['pid'],'','',true);
			$curl=$this->page->build_permalink_cat(Formatter::sth2($v['cid']),false);
			$description=str_replace(array(F_LF,'&','&quot;','&nbsp;','<','>','%SHOP_DETAIL%','%caturl%'),
					  array('','&amp;','"',' ','&lt;','&gt;',$permalink,$curl),$description);
			$description=Editor::fixInnovaPaths($description,$this->page->pg_name,$this->page->full_script_path,$this->page->rel_path);
			Formatter::replaceIfMacro($description);
			Formatter::replaceEvalMacro($description,$this->page->pg_settings['g_price_decimals'],$this->page->pg_settings['g_decimal_sign'],$this->page->pg_settings['g_thousands_sep']);

			$rss_item=array('title'=>$title,'description'=>$description,'link'=>$permalink,'guid'=>$permalink);

			if($enclosure!='' && $v[$enclosure]!='')
			{
				$img_path=str_replace('src="','',Editor::fixInnovaPaths('src="'.$v[$enclosure],$this->page->pg_name,$this->page->full_script_path,$this->page->rel_path));
				$x=explode(".",strtolower($img_path));
				$rss_item['enclosure']=array('url'=>$img_path,
				'length'=>filesize($v[$rss_image]),
				'type'=>Detector::getMime(end($x)));
			}
			elseif(!empty($v[$rss_image]))
			{
				$med_url=str_replace('src="','',Editor::fixInnovaPaths('src="'.$v[$rss_image],$this->page->pg_name,$this->page->full_script_path,$this->page->rel_path));
				$med_size=-1;
				$med_type='';
				if(strpos($v[$rss_image],'http')===false)
					$med_size=filesize($v[$rss_image]);
				if($med_url != '')
				{
					$rss_item['media:content']['url']=$med_url;
					$med_type=Detector::getMime(pathinfo($med_url,PATHINFO_EXTENSION),'');
					if($med_size > -1)
						$rss_item['media:content']['fileSize']=$med_size;
					if($med_type != '')
						$rss_item['media:content']['type']=$med_type;
				}
			}

			if($googleM)
			{
				$rss_item['g:image_link']=str_replace('src="','',Editor::fixInnovaPaths('src="'.$v[$rss_image],$this->page->pg_name,$this->page->full_script_path,$this->page->rel_path));
				$rss_item['g:price']=number_format($v['price'],$this->page->pg_settings['g_price_decimals'],'.','');
				$rss_item['g:condition']='new';
				$rss_item['g:id']=$v['pid'];
			}
			$rss_data[]=$rss_item;
		}
		$rss_title=($cname!='')?$this->page->lang_l('category').': '.$cname:$this->page->pg_settings['rss_settings']['Title'];
		$rss_url=$this->page->use_alt_plinks?str_replace($this->page->pg_name,'',$this->page->full_script_path).($cname!=''?$cname.'/':'').'rss/': $this->page->full_script_path.'?action=rss'.($cname!=''?'&amp;category='.$cname:'');
		$parentid=-1;
		$more_xmlns=($this->page->comments_feed_in_rss? 'xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/"': '');
		$more_xmlns .= ' xmlns:media="http://search.yahoo.com/mrss/"';
		$page_data=CA::getPageParams($this->page->pg_id,$this->page->rel_path);
		$new_data=RSS::build($rss_data,$this->page->pg_settings['rss_settings'],$page_data[17],($cid!=''? $this->page->build_permalink_cat($cid,true): $this->page->full_script_path),Date::tzone(time()),$more_xmlns,
			false,$rss_url,str_replace('&#039;',"'",$rss_title),$googleM);
		Formatter::replacePollMacro_null($new_data);
		header("Content-Type:text/xml; charset=".$page_data[17]);
		echo $new_data;
		exit;
	}
}

class import extends page_objects
{
	protected function convert_data($import_g_id) //from ezg V3
	{
		global $db;

		$page_id=Formatter::GFS($import_g_id,'','-');
		$dat_dir=Formatter::GFSAbi($import_g_id,'../','/');
		$dat_dir=str_replace('//','/',$dat_dir);
		if($this->page->rel_path=='')
			$dat_dir=str_replace('../','',$dat_dir);
		$import_g_id=$page_id;
		$data_ext='.ezg.php';
		$ezg_dir=$this->page->rel_path.'ezg_data/';

		$db->query('DELETE FROM '.$db->pre.$this->page->g_datapre.'data');
		$db->query('DELETE FROM '.$db->pre.$this->page->pg_pre.'categories');
		if($this->page->shop)
		{
			$db->query('DELETE FROM '.$db->pre.$this->page->pg_pre.'paypal');
			$db->query('DELETE FROM '.$db->pre.$this->page->pg_pre.'pending_orders');
			$db->query('DELETE FROM '.$db->pre.$this->page->pg_pre.'orders');
		}

		$new_page_name=$ezg_dir.$import_g_id.'_orders'.$data_ext;

		$content=File::read($new_page_name);
		$orders=Formatter::GFS($content,'<ezg_file>','</ezg_file>');
		$orders=str_replace('<?php echo "hi"; exit; "<ezg_file>','',$orders);
		$order_lines=explode("**",$orders);
		$count=count($order_lines);
		for($i=0;$i<$count;$i++)
		{
			$v=$order_lines[$i];
			if($v!='')
				$db->query_insert($this->page->pg_id.'_orders',array('orderid'=>$this->page->get_orderid_fromdata($v),'orderline'=>$v));
		}

		//paypal table
		$new_page_name=$ezg_dir.$import_g_id.'_paypal'.$data_ext;
		$content=File::read($new_page_name);
		$orders=Formatter::GFS($content,'<ezg_file>','</ezg_file>');
		$orders=str_replace('<?php echo "hi"; exit; "<ezg_file>','',$orders);
		$order_lines=explode("**",$orders);
		$count=count($order_lines);
		for($i=0;$i<$count;$i++)
		{
			$v=$order_lines[$i];
			if($v!='')
				$db->query_insert($this->page->pg_id.'_paypal',array('ipnline'=>$v));
		}

	//pending orders
		$new_page_name=$ezg_dir.$import_g_id.'_pending_orders'.$data_ext;
		$content=File::read($new_page_name);
		$orders=Formatter::GFS($content,'<ezg_file>','</ezg_file>');
		$orders=str_replace('<?php echo "hi"; exit; "<ezg_file>','',$orders);
		$order_lines=explode("**",$orders);
		$count=count($order_lines);
		$data=array();
		for($i=0;$i<$count;$i++)
		{
			$v=$order_lines[$i];
			if($v!='')
			{
				$data['orderid']=Formatter::GFS($v,'<order_','>');
				$data['pdate']=Formatter::GFS($v,'<date>','</date>');
				$data['items']=Formatter::GFS($v,'<items>','</items>');
				$data['form_fields']=Formatter::GFS($v,'<form_fields>','</form_fields>');
				$db->query_insert($this->page->pg_id.'_pending_orders',$data);
			}
		}
	//settings
		$new_page_name=$ezg_dir.$import_g_id.'_orderid'.$data_ext;
		$content=File::read($new_page_name);
		$orders=Formatter::GFS($content,'<ezg_file>','</ezg_file>');
		$orders=str_replace('<?php echo "hi"; exit; "<ezg_file>','',$orders);
		$data=array();
		$data['skey']='id';
		$data['sval']=Formatter::GFS($orders,'<id>','</id>');
		if(!empty($data['sval']))
			$db->query_update($this->page->pg_id.'_settings',$data,'skey="id"');
		$data=array();
		$data['skey']='bwsubject';
		$data['sval']=Formatter::GFS($orders,'<bwsubject>','</bwsubject>');
		if(!empty($data['sval']))
			$db->query_update($this->page->pg_id.'_settings',$data,'skey="bwsubject"');
		$data=array();
		$data['skey']='bwmess';
		$data['sval']=Formatter::GFS($orders,'<bwmess>','</bwmess>');
		if(!empty($data['sval']))
			$db->query_update($this->page->pg_id.'_settings',$data,'skey="bwmess"');

		$page_name=$dat_dir.$import_g_id.'_1.dat';
		$data=File::read($page_name); $data=str_replace('<%23>','#',$data);
		$data=explode("\n",$data);
		$this->f->labels=explode("|",$data[0]); array_pop($this->f->labels);
		$this->f->labels=array_reverse($this->f->labels); array_pop($this->f->labels); $this->f->labels=array_reverse($this->f->labels);

	//categories
		$categories='';
		$page_name=$dat_dir.$import_g_id."_0.dat";
		$categories=File::read($page_name);
		$categories_a=explode("\n",$categories);
		$count=count($categories_a);

		foreach($categories_a as $cv)
		{
			$data=array();
			$data['cid']=$i+1;
			$data['cname']=Formatter::GFS($cv,'','#');
			$fvalues=explode("|",Formatter::GFS($cv,'|',''));

			if(!empty($fvalues) && (count($fvalues)>1 || $fvalues[0]!=''))
			{
				array_pop($fvalues); $fvalues_ass=array();
				foreach($this->f->labels as $k=>$v)
				{
					$fvalues_ass[$v]=current($fvalues);
					next($fvalues);
				}

				foreach($fvalues_ass as $k=>$v)
				{
					if(strpos($k,'SCALE(%')!==false)
						$data['image1']=$v;
				}
				$data['description']=str_replace('%js1310',F_LF,$fvalues_ass['description_html']);
			}
			$db->query_insert($this->page->pg_id.'_categories',$data);
		}

		for($i=1;$i<$count;$i++) // convert data
		{
			$page_name=$dat_dir.$import_g_id.'_'.$i.'.dat';
			$catdata=File::read($page_name);
			$catdata=str_replace('<%23>','#',$catdata);
			$lines2=explode("\n",$catdata);

			$this->f->labels=explode("|",$lines2[0]);	array_pop($this->f->labels); // text db labels
			$db_f_labels=$db->db_fieldnames($this->page->pg_id.'_data'); // mysql db labels

			$lcount=count($lines2);
			for($j=2;$j<$lcount-1;$j++)
			{
				$fvalues=explode("|",$lines2[$j]);
				array_pop($fvalues);
				$fvalues_ass=array();
				foreach($this->f->labels as $k=>$v)
				{
					$fvalues_ass[$v]=current($fvalues);
					next($fvalues);
				}

				$data=array();
				foreach($db_f_labels as $k=>$v)
				{
					if(isset($_POST[$v.'_m']))
					{
						$this->f->ind=Formatter::stripTags($_POST[$v.'_m']);
						if($this->f->ind!='-none-')
						{
							if(strpos($this->page->pg_settings['g_fields_array'][$v]['type'],'int')!==false)
								$data[$v]=intval($fvalues_ass[$this->f->ind]);
							if(strpos($this->page->pg_settings['g_fields_array'][$v]['type'],'dec')!==false)
								$data[$v]=floatval($fvalues_ass[$this->f->ind]);
							else
								$data[$v]=str_replace('%js1310',F_LF,$fvalues_ass[$this->f->ind]);
						}
					}
				}

				$data['cid']=$i;
				$db->query_insert($this->page->pg_id.'_data',$data);
				if(!isset($data['publish']) || $data['publish']==1)
					$this->page->reindex_search($data['pid']);
			}
		}
	}

	public function mng_import($action)
	{
		if($action=='import')
		{
			$shop_pages_list=array();
			$pages_list=CA::getSitemap($this->page->rel_path);
			foreach($pages_list as $k=>$v)
			{
				$p_a=($this->page->shop)?array('21'):array('130','140');
				if(in_array($v[4],$p_a))
				{
					$id=$v[10];
					$shop_pages_list[$id.'-'.$v[1]]=$v[0].' ('. $v[1] .')';
				}
			}
			if(empty($shop_pages_list))
			{

				$settings=array(
					'script_path'=> $_SERVER['PHP_SELF'],
					'rel_path'=> $this->page->rel_path,
					'user_data'=> $this->page->user->getAllData(),
					'act_up'=> '?action=import',
					'act_imp'=> '?action=import2',
					'act_cancel'=> '?action=settings',
					'act_imp_redirect'=> '?action=products',
					'lang_l'=> $this->page->pg_settings['lang_l'],
					'db_system_fields'=> array('id','cid','subcategory','publish','updated','created'),
					'data_table'=> $this->page->pg_settings['g_data'].'_data',
					'lg_amp'=> '',
					'module_object'=> $this->page
				);
				$output=ImportHandler::import($settings,'LI');
			}
			else //import from V3 db
				$output=F_BR.F_BR.'<div class="rvps1">IMPORT step 1'.F_BR.F_BR.$this->page->lang_l('import warning')
				.'<form action="'.$this->page->pg_name.'" method="post" enctype="multipart/form-data">'
					  .Builder::buildSelect('shop_page_id',$shop_pages_list,'').F_BR.F_BR.'
	 					<input type="hidden" name="action" value="import2">
						<input type="submit" name="dbconvert" value="Submit">
						<input type="submit" name="pending" value="'.$this->page->lang_l('cancel').'">
					</div>';
			print $this->page->getHtmlTemplate($output);
			exit;
		}
		else
		{
			if(isset($_POST['dbconvert']) && isset($_POST['shop_page_id']))
			{
				$output=F_BR.F_BR.'
					 <div class="rvps1">IMPORT step 2'.F_BR.F_BR.'
					 <form action="'.$this->page->pg_name.'" method="post" enctype="multipart/form-data">
					 <table width="350px">
						<tr><td colspan="2" class="rvps1">
						  <span class="rvts8">'.$this->page->lang_l('mysql link message').'</span>'.F_BR.F_BR.'
						</td></tr>
						<tr><td colspan="2">
						  <input type="hidden" name="action" value="import2">
						  <input type="hidden" name="shop_page_id" value="'.$_POST['shop_page_id'].'">
						</td></tr>';

				$page_id=Formatter::GFS($_POST['shop_page_id'],'','-');
				$page_path=Formatter::GFSAbi($_POST['shop_page_id'],'../','/');
				$page_path=str_replace('//','/',$page_path);
				if($this->page->rel_path=='')
					$page_path=str_replace('../','',$page_path);
				$page_name=$page_path.$page_id.'_1.dat';
				$catdata=File::read($page_name);
				$catdata=str_replace('<%23>','#',$catdata);
				$lines2=explode("\n",$catdata);

				$oldfields_labels=explode("|",$lines2[0]);
				array_pop($oldfields_labels);
				$oldfields_labels=array_merge(array('-none-'),$oldfields_labels);

				$dbfields_labels=array();
				$dbfields_defaults=array();
				$hidden=array("id","cid","stock","salescount","publish");
				$linked=array("pid"=>"P_ID","name"=>"P_Name","category"=>"P_Category","html_description"=>"description_html","image1"=>"P_ImageFull","download"=>"mp3");
				foreach($this->page->pg_settings['g_fields_array'] as $k=>$v)
				{
					if(!in_array($k,$hidden))
					{
						$dbfields_labels[]=$k;
						if(isset($linked[$k]))
							$dbfields_defaults[]=$linked[$k];
						elseif(in_array($k,$oldfields_labels))
							$dbfields_defaults[]=$k;
						else
							$dbfields_defaults[]='-none-';
					}
				}

				foreach($dbfields_labels as $k=>$val)
					$output.='
						 <tr><td><input type="hidden" name="'.$val.'" value="'.$val.'">
							  <span class="rvts8">'.$val.'</span>
						</td><td class="rvps2">
								'.Builder::buildSelect($val.'_m',$oldfields_labels,$dbfields_defaults[$k],'','value').'
						</td></tr>';
				$output.='<tr><td></td><td class="rvps2"><input type="submit" value="import" name="dbconvert2">
					<input type="submit" name="pending" value="'.$this->page->lang_l('cancel').'"></td></tr></table></div>';
				print $this->page->getHtmlTemplate($output);
				exit;
			}
			elseif(isset($_POST['dbconvert2']) && isset($_POST['shop_page_id']))
				$this->convert_data($_POST['shop_page_id']);
		}
	}

}

class recentlyViewed extends page_objects
{

	public function add_recently_viewed($item_id)
	{
		$rvc=$this->page->get_setting('rvc');
		$rv_cookie='r_viewed';
		$cookie_a=(isset($_COOKIE[$rv_cookie]))?unserialize(stripcslashes($_COOKIE[$rv_cookie])):array();
		if(!array_search($item_id,$cookie_a))
		{
			if(count($cookie_a)>=$rvc)
				array_shift($cookie_a);
			$cookie_a[]=$item_id;
			setcookie($rv_cookie,serialize($cookie_a),time()+60*60*24,'/');
		}
	}

	public function parse_recently_viewed($src)
	{
		global $db;

		$rv_cookie='r_viewed';
		$section_id='%RECENTLY_VIEWED';
		$rec_viewed=$this->page->get_setting('rec_viewed');

		if(strpos($src,$section_id)!==false)
		{
			$html='';
			$param='';
			if(strpos($src,$section_id.'%')!==false)
				$section=$section_id.'%';
			else
			{
				$section=Formatter::GFSAbi($src,$section_id,')%');
				$param=Formatter::GFS($section,$section_id.'(',')%');
			}
			$cookie_a=(isset($_COOKIE[$rv_cookie]))?unserialize(stripcslashes($_COOKIE[$rv_cookie])):array();
			if(count($cookie_a)>0)
			{
				$que='';

				$cookie_b=array();
				foreach($cookie_a as $v)
					if(!in_array($v,$cookie_b))
						$cookie_b[]=$v;
				$cookie_a=$cookie_b;
				foreach($cookie_b as $v)
					$que.=' pid='.intval($v).' OR ';
				$que='( '.$que.' pid=-1 ) AND ';

				$items=$db->fetch_all_array('SELECT * FROM '.$db->pre.$this->page->g_datapre.'data WHERE '.$que.$this->page->where_public);
				$items_a=array();
				foreach($items as $v)
					$items_a[$v['pid']]=$v;
				$cookie_a=array_reverse($cookie_a);

				if($rec_viewed>0)
					$cookie_a=array_slice($cookie_a,0,$rec_viewed);
				foreach($cookie_a as $pid)
				{
					if(isset($items_a[$pid]))
					{
						$v=$items_a[$pid];
						$plink=$this->page->build_permalink($v,false,'',true);
						if($param!='')
							$html.='<li>'.$this->page->ReplaceFieldsData(false,$v,str_replace('%SHOP_DETAIL%',$plink,$param),true,$pid,true).'</li>';
						else
							$html.='<li><a class="rvts12" href="'.$plink.'">'.$v['name'].'</a></li>';
					}
				}
			}
			if($html!='')
				$html='<ul class="recent_items" style="list-style-type:none;">'.$html.'</ul>';
			$src=str_replace($section,$html,$src);
		}
		return $src;
	}

}

class lister_admin_screens extends page_admin_screens
{
	protected $dp_array=array();

	final public function output($output,$caption='',$template=true)
	{
		$output=Formatter::fmtAdminScreen($output,$this->build_menu($caption));
		if(!$template)
		{
			echo $output;
			exit;
		}
		$title=$this->page->page_info[0].' &raquo; '.$this->page->lang_l('administration panel');
		$output=Formatter::fmtInTemplate($this->page->pg_settings['template_path'],$output,$title);
		$output=Builder::includeDatepicker($output,$this->page->month_name,$this->page->day_name,$this->dp_array);
	 	$output=Builder::includeBrowseDialog($output,$this->page->rel_path,$this->page->pg_settings['ed_lang']);

		if($this->page->innova_on_output)
			$output=Builder::appendScript($this->page->innova_js,$output);
		parent::screen_output($output);
	}

	public function build_menu($caption)
	{
		global $db;

		$result='';
		$data=array();
		if(isset($_REQUEST['myorders']))
		{
			$caption=$this->page->lang_l('my orders');
			$data[]=Navigation::addEntry($this->page->lang_l('sitemap'),$this->page->ca_url_base.'?process=myprofile',$this->page->action_id=='myprofile','sitemap');
			$data[]=Navigation::addEntry($this->page->lang_l('profile'),$this->page->ca_url_base.'?process=editprofile',$this->page->action_id=='editprofile','profile');
			$data[]=Navigation::addEntry($this->page->lang_l('my orders'),$this->page->ca_url_base.'?process=vieworders',
					  in_array($this->page->action_id,array('vieworders','myorders')),'orders');
			$data[]=Navigation::addEntry($this->page->lang_l('change pass'),$this->page->ca_url_base.'?process=changepass',
						  $this->page->action_id=='changepass','changepass');
			$data[]=Navigation::addEntry($this->page->lang_l('logout'),$this->page->ca_url_base.'?process=logout',false,'logout',$this->page->user->getUname(),'a_right last');
			$type_id=2;
			$result='<div class="'.CA::getAdminScreenClass().'">';
		}
		else
		{
			$url_base=$this->page->script_path.'?action=';

			$data[]=Navigation::addEntry($this->page->lang_l('new product'),$url_base.'new_product',$this->page->action_id=='new_product','products');
			$data[]=Navigation::addEntry($this->page->lang_l(($this->page->shop?'stock':'browse')),
					  $url_base.'stock',$this->page->action_id=='stock' || $this->page->action_id=='products','stock');
			if(!$this->page->edit_own_posts_only)
				$data[]=Navigation::addEntry($this->page->lang_l('categories'),$url_base.'categories',$this->page->action_id=='categories','categories');

			if($this->page->pg_settings['enable_comments'])
				$data[]=Navigation::addEntry($this->page->lang_l('comments'),$url_base.'comments',
						  $this->page->action_id=='comments','comments');

			if($this->page->shop)
			{
				if($this->page->action_id=='detail') $this->page->action_id=(isset($_REQUEST['type'])&&$_REQUEST['type']=='confirmed')?'orders':'pending';
				$data[]=Navigation::addEntry($this->page->lang_l('all orders'),$url_base.'pending',
						  $this->page->action_id=='pending' || $this->page->action_id=='login','pending');
				$data[]=Navigation::addEntry($this->page->lang_l('confirmed orders'),$url_base.'orders',$this->page->action_id=='orders','orders');
			}

			if($this->page->shop && !$this->page->edit_own_posts_only)
			{
				if($this->page->pg_settings['g_tax_handling'])
					$data[]=Navigation::addEntry($this->page->lang_l('taxes'),$url_base.'taxes',$this->page->action_id=='taxes','taxes');
				$data[]=Navigation::addEntry($this->page->lang_l('coupons'),$url_base.'coupons',$this->page->action_id=='coupons','coupons');
				$data[]=Navigation::addEntry($this->page->lang_l('bundles'),$url_base.'bundles',$this->page->action_id=='bundles','bundles');
			}

			$type_id=0;
			$result='<div class="'.CA::getAdminScreenClass().'">';

			if($this->page->get_setting('features') && !$this->page->edit_own_posts_only)
				$data[]=Navigation::addEntry($this->page->lang_l('features'),$url_base.'features',$this->page->action_id=='features','features');
			$ca_url_ext=$this->page->ca_url_base.'?pageid='.$this->page->pg_id.'&amp;process=';
			if(CA::getDBSettings($db,'landing_page')==1)
				$sitemap_nav=Navigation::addEntry($this->page->lang_l('profile'),$ca_url_ext."editprofile",false,'profile','','last');
			else
				$sitemap_nav=Navigation::addEntry($this->page->lang_l('sitemap'),$ca_url_ext."myprofile",false,'sitemap','','last');
			if($this->page->user->isAdminOnPage($this->page->pg_id))
			{
				$data[]=Navigation::addEntry($this->page->lang_l('setup'),$url_base.'settings',$this->page->action_id=='settings','settings');
				$data[]=$this->page->user->isAdmin()?
						  Navigation::addEntry($this->page->lang_l('administration panel'),$this->page->ca_url_base.'?process=index',false,'administration','','last'):
						  $sitemap_nav;
				$data[]=Navigation::addEntry($this->page->lang_l('logout'),$this->page->ca_url_base.'?process=logoutadmin&amp;pageid='.$this->page->pg_id,false,'logout',$this->page->user->getUname(),'a_right');
			}
			else
			{
				$data[] = $sitemap_nav;
				$data[]=Navigation::addEntry($this->page->lang_l('logout'),$this->page->ca_url_base.'?process=logout&amp;pageid='.$this->page->pg_id,false,'logout',$this->page->user->getUname(),'a_right');
			}
		}
		$result.=Navigation::admin2($data,$caption,false,$this->page->page_info[0],$this->page->script_path);
		return $result.'<br class="ca_br" />';
	}

}

class mng_stock_screen extends lister_admin_screens
{
	public function handle_screen()
	{
		global $db;

		$pid=isset($_REQUEST['pid'])?$_REQUEST['pid']:0;
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';

		if($do=='delete_stockproduct')
		{
			$pid_a=is_array($pid)?$pid:array($pid);

			foreach($pid_a as $pid)
			{
				$pid=intval($pid);
				if($pid>0 && $this->checkProductOwner($pid)){
					$db->query('
						DELETE
						FROM '.$db->pre.$this->page->g_datapre.'data
						WHERE pid='.$pid);
					$this->page->wishlistModule->delete_wishlist($pid,$db,true);
				}
			}
		}
		elseif($do!=='' && $pid>0) //owner check
		{
			$pid=intval($pid);
			if(!$this->checkProductOwner($pid))
				$do='';
		}

		if($do=='edit_code')
		{
			$val=(isset($_REQUEST['val']))?$_REQUEST['val']:'';
			$data=$db->fetch_all_array('
				SELECT pid
				FROM '.$db->pre.$this->page->g_datapre.'data
				WHERE code="'.addslashes($val).'" AND pid <> '.$pid);
			if(count($data)>0)
				print $this->page->lang_l('code used');
			else
			{
				$db->query_update($this->page->pg_settings['g_data'].'_data',array('code'=>$val),'pid='.$pid);
				print '1';
			}
			exit;
		}
		elseif($do=='edit_stock')
		{
			$val=(isset($_REQUEST['val']))?intval($_REQUEST['val']):0;
			$db->query_update($this->page->pg_settings['g_data'].'_data',array('stock'=>$val),'pid='.$pid);
			print '1';
			exit;
		}
		elseif($do=='edit_price')
		{
			$val=isset($_REQUEST['val'])?$this->page->get_output_price($_REQUEST['val']):0;
			$db->query_update($this->page->pg_settings['g_data'].'_data',array($this->page->pg_settings['g_pricefield']=>$val),'pid='.$pid);
			print '1';
			exit;
		}
		elseif($do=='edit_sales')
		{
			$val=(isset($_REQUEST['val']))?intval($_REQUEST['val']):0;
			$db->query_update($this->page->pg_settings['g_data'].'_data',array('salescount'=>$val),'pid='.$pid);
			print '1';
			exit;
		}
		elseif($do=='toggle_publish')
		{
			$p=intval($_REQUEST['p']);
			$db->query_update($this->page->pg_settings['g_data'].'_data',array('publish'=>$p),'pid='.$pid);
			print $p.intval($_REQUEST['p'])?$this->page->lang_l('unpublish'):$this->page->lang_l('publish');
			exit;
		}
		elseif($do=='get_serials')
		{
			$ser=$this->page->serialsModule->get_serials($pid);
			$res=array();
			foreach($ser as $v)
				 $res[]=($v['issued']?'<b>':'').'<span class="ser rvts8">'.$v['serial'].'</span>'.($v['issued']?'</b>':'');
			print implode('|',$res);
			exit;
		}
		elseif($do=='delete_serial')
		{
			$this->page->serialsModule->delete_serial($pid,$_REQUEST['serial']);
			print '1';
			exit;
		}
		elseif($do=='save_serials')
		{
			$serials=explode("\n",$_REQUEST['serials']);
			$this->page->serialsModule->save_serials($pid,$serials);
			print '';
			exit;
		}
		elseif($do=='export')
		{
			$this->export();
			exit;
		}

		return $this->mng_stock($do);
	}


	protected function checkProductOwner($pid)
	{
		global $db;

		$editable=!$this->page->g_use_pby || !$this->page->edit_own_posts_only;
		if(!$editable)
		{
			$uid=$this->page->user->getId();
			$data=$db->query_first('
				SELECT *
				FROM '.$db->pre.$this->page->g_datapre.'data
				WHERE pid='.$pid);
			if(!empty($data))
				 $editable=$data['posted_by']==$uid;
		}
		return $editable;
	}


	protected function export()
	{
		global $db;

		$output='';
		$delim=',';
		$lf="\r\n";

		$where=$this->get_where();

		$products=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.$this->page->g_datapre.'data '.$where.'
			ORDER BY pid');

		if(!empty($products))
		{
			foreach($products as $value)
			{
				if($output=='')
				{
					$values=array();
					foreach($value as $k=>$v)
								$values[]='"'.$k.'"';
					$output=implode($delim,$values).$lf;
				}

				$values=array();
				foreach($value as $k=>$v)
					$values[]='"'.addslashes($v).'"';
				$output_line=implode($delim,$values);
				$output_line=str_replace(array("\r\n","\n"),'',$output_line);
				$output.=$output_line.$lf;
			}
		}
		output_generator::sendFileHeaders('stock.csv');
		print $output;
		exit;
	}

	protected function get_where()
	{
		 $where=$this->page->g_catid>0?
				' WHERE cid ="'.$this->page->g_catid.'"'.($this->page->g_use_rel?' AND rel=0':'')
				:($this->page->g_use_rel?' WHERE rel=0':'');
		 return $where;
	}

	protected function mng_stock($do)
	{
		global $db;

		$show_visits=$this->page->g_use_com && (array_key_exists('visits_count',$this->page->pg_settings['g_fields_array']));
		$uid=$this->page->user->getId();

		$filters=$do=='filter_stock';
		$filter=isset($_REQUEST['filter'])?$_REQUEST['filter']:'';
		$filter2=isset($_REQUEST['filter2'])?$_REQUEST['filter2']:'';

		$where=$this->get_where();

		$filter_params=array('name'=>$filter,'code'=>$filter2);
		Formatter::filterParamsToQuery($where,$filter_params);
		list($orderby,$asc)=Filter::orderBy($this->page->shop?'stock':'name','ASC');

		$where.=$this->page->g_use_pby && $this->page->edit_own_posts_only?($where==''?'WHERE ':' AND').' posted_by = '.$uid.' ':'';
		$limit_post_db=$this->page->g_use_pby && $this->page->limit_own_post>0?' LIMIT '.$this->page->limit_own_post:'';

		$search_string=isset($_REQUEST['q'])?Formatter::strToLower(trim(strip_tags(str_replace("\\",'',$_REQUEST['q'])))):'';
		if(!empty($search_string)) // search
		{
			$search_type='';
			$category_names=array();
			if(strpos($search_string,'"') !== false)
			{
				$search_type='exact';
				$search_string=str_replace('"','',$search_string);
			}
			$searchstring_words=explode(" ",Formatter::strToLower($search_string));
			foreach($this->page->categoriesModule->category_array as $k=>$v)
				 $category_names[]=isset($v['linked'])?$v['linked']['name']:$v['name'];
			$str='';
			$que='SELECT * FROM '.$db->pre.$this->page->g_datapre.'data '.$where.'
			ORDER BY '.($orderby=='status'?'publish':$orderby).' '.$asc.$limit_post_db;
			$products=$this->page->searchModule->search_indb($str,$que,$search_type,$search_string,$searchstring_words,'',$category_names,true);
			$products=array_values($products);
		}
		else{
			$products=$db->fetch_all_array('
			SELECT pid,cid,code,name,publish,category,'.($this->page->use_friendly?'friendlyurl,':'')
			.($this->page->g_use_pby?'posted_by,':'')
			.($this->page->g_use_com?'ranking_count, ranking_total,':'')
			.($show_visits?'visits_count,':'').'image1'
			.($this->page->shop?',salescount,stock,'.$this->page->pg_settings['g_pricefield']:'')
			.($this->page->use_publish_dates?',published_date, unpublished_date, if((published_date<>0 AND unpublished_date<>0 AND unpublished_date>=published_date AND unpublished_date>=NOW() 
				AND published_date<=NOW()) OR (published_date=0 AND unpublished_date=0) OR (published_date<>0 AND unpublished_date=0 AND published_date<=NOW()),1,0) as active_not_expired_item':'')
			.'
			FROM '.$db->pre.$this->page->g_datapre.'data '.$where.'
			ORDER BY '.($orderby=='status'?'publish':($this->page->use_publish_dates&&$orderby=='active'?'active_not_expired_item':$orderby)).' '.$asc.$limit_post_db);
		}

		if($this->page->g_use_rel)
		{
			if(!empty($search_string))
			{
				$que='SELECT * FROM '.$db->pre.$this->page->g_datapre.'data '.str_replace('rel=0','rel > 0',$where).'
				ORDER BY '.($orderby=='status'?'publish':$orderby).' '.$asc.$limit_post_db;
				$temp=$this->page->searchModule->search_indb($str,$que,$search_type,$search_string,$searchstring_words,'',$category_names,true);
				$temp=array_values($temp);
			}
			else{
				$temp=$db->fetch_all_array('
				SELECT rel,pid,cid,code,name,publish,category,'.($this->page->use_friendly?'friendlyurl,':'')
				.($this->page->g_use_pby?'posted_by,':'')
				.($this->page->g_use_com?'ranking_count, ranking_total,':'')
				.($show_visits?'visits_count,':'').'image1'
				.($this->page->shop?',salescount,stock,'.$this->page->pg_settings['g_pricefield']:'')
				.($this->page->use_publish_dates?',published_date, unpublished_date, if((published_date<>0 AND unpublished_date<>0 AND unpublished_date>=published_date AND unpublished_date>=NOW() 
					AND published_date<=NOW()) OR (published_date=0 AND unpublished_date=0) OR (published_date<>0 AND unpublished_date=0 AND published_date<=NOW()),1,0) as active_not_expired_item':'')
				.'
				FROM '.$db->pre.$this->page->g_datapre.'data '.str_replace('rel=0','rel > 0',$where).'
				ORDER BY '.($orderby=='status'?'publish':($this->page->use_publish_dates&&$orderby=='active'?'active_not_expired_item':$orderby)).' '.$asc.$limit_post_db);
			}
			$products_related=array();
			foreach($temp as $k=>$v)
			{
				if(isset($products_related[$v['rel']]))
					$products_related[$v['rel']][]=$v;
				else
					$products_related[$v['rel']]=array($v);
			}
		}

		$count=count($products);
		$cap_arrays=array(''=>'<input type="checkbox" onclick="$(\'.mng_entry_chck\').prop(\'checked\',$(this).is(\':checked\'));">',' ','name'=>array($this->page->pg_name.'?action=stock&amp;orderby=name','none',$this->page->lang_l('name'),Filter::build('filter_stock',$filter,'filter_stock();','width:20px',' onkeydown="filter_stock_enter(event);"')),
		'code'=>array($this->page->pg_name.'?action=stock&amp;orderby=code','none',$this->page->lang_l('code'),Filter::build('filter_stock2',$filter2,'filter_stock();','width:20px',' onkeydown="filter_stock_enter(event);"')));
		if($this->page->shop)
		{
			$cap_arrays['stock']=array($this->page->pg_name.'?action=stock&amp;orderby=stock','none',$this->page->lang_l('stock'));
			$cap_arrays['salescount']=array($this->page->pg_name.'?action=stock&amp;orderby=salescount','none',$this->page->lang_l('sold'));
			$cap_arrays[]=$this->page->lang_l('price');
		}
		if($show_visits)
			$cap_arrays['visits_count']=array($this->page->pg_name.'?action=stock&amp;orderby=visits_count','none',$this->page->lang_l('visits'));
		$cap_arrays['status']=array($this->page->pg_name.'?action=stock&amp;orderby=status','none',$this->page->lang_l('status'));
		$cap_arrays['category']=array($this->page->pg_name.'?action=stock&amp;orderby=category','none',$this->page->lang_l('category'));
		if($this->page->use_publish_dates)
			$cap_arrays['active']=array($this->page->pg_name.'?action=stock&amp;orderby=active','none',$this->page->lang_l('active'));

		$table_data=array();
		$cap_arrays[$orderby][1]='underline';
		if($asc=='ASC')
			$cap_arrays[$orderby][0]=$cap_arrays[$orderby][0].'&amp;asc=DESC';
		$url_suffix='&amp;orderby='.$orderby.'&amp;asc='.$asc;
		$nav=Navigation::pageCA($count,$this->page->g_abs_path.$this->page->pg_name.'?action=stock'.$url_suffix.($filter!=''?'&amp;filter='.$filter:'')
		.(isset($_REQUEST['category'])?'&amp;category='.$_REQUEST['category']:''),0,$this->page->g_pagenr,$this->page->nav_labels);

		$start=($this->page->g_pagenr*Navigation::recordsPerPage())-Navigation::recordsPerPage();
		$end=($this->page->g_pagenr*Navigation::recordsPerPage());
		$end=($end>$count)?$count:$end;
		for($i=$start;$i<$end;$i++)
		{
			$pid=$products[$i]['pid'];
			$published=$products[$i]['publish'];
			$entry_nav=array();
			$plink_edit=$this->page->pg_name.'?action=products&amp;cat_select='.$products[$i]['cid']
					  .'&amp;prod_select='.$products[$i]['pid'];
			if($this->page->get_setting('return_to_stock')=='1')
				$plink_edit.='&amp;'.Linker::buildReturnURL();

			$entry_nav[$this->page->lang_l('edit')]=$plink_edit;
			$entry_nav[$this->page->lang_l('duplicate')]=$this->page->pg_name.'?action=products&amp;status=duplicate&amp;cat_select='.$products[$i]['cid'].'&amp;pid='.$products[$i]['pid'];

			$entry_nav[$this->page->lang_l('delete')]=$this->page->g_abs_path.$this->page->pg_name.'?action=stock&amp;do=delete_stockproduct&amp;pid='.$products[$i]['pid'].$url_suffix.'" onclick="javascript:return confirm(\''.$this->page->lang_l('del_product_msg').'\');';
			$entry_nav[$this->page->lang_l($published?'unpublish':'publish')]=
					  array('url'=>'javascript: void(0)','class'=>'lock_a_'.$pid,'extra_tags'=>'onclick="toggle_publish('.$pid.')"');
			if($this->page->shop && $this->page->serialsModule->enabled())
				 $entry_nav[$this->page->lang_l('serials')]='javascript:void(0);" onclick="show_serials(this,\''.$products[$i]['pid'].'\');';

			$plink=$this->page->build_permalink($products[$i],false,'',true);
			$name_link='<p><a class="rvts8" style="text-decoration:none;line-height:22px;" target="_blank" href="'.$plink.'">'.$products[$i]['name'].'</a></p>';

			$ima=$products[$i]['image1'];
			if($ima!='')
			{
				$temp=Formatter::str_lreplace('/','/thumbs/',$ima);
				if(file_exists($temp)) $ima=$temp;
				$ima=$this->page->site_base.str_replace('../','',$ima);

				$ima='<img src="'.$ima.'" style="width:46px;margin:2px;float:left;border-radius:3px;">';
			}
			$codedata='<div><input type="text" value="'.$products[$i]['code'].'" name="val" id="code_'.$pid.'" class="input1 direct_edit">
			<span class="i_check fa fa-check-square" id="stcod_'.$pid.'" onclick="update_code('.$pid.');"></span>';
			if($this->page->shop)
			{
				$stockdata='
				<div>
					 <input type="text" style="width:30px;" value="'.($this->page->shop?$products[$i]['stock']:null).'" name="val" id="stock_'.$pid.'" class="input1 direct_edit">
					 <span class="i_check fa fa-check-square" id="stbtn_'.$pid.'" onclick="update_stock('.$pid.');"></span>
				</div>';
				$pval=$this->page->nrf($this->page->shop?$products[$i][$this->page->pg_settings['g_pricefield']]:null);
				$pricedata='
				<div>
					 <input type="text" style="width:70px;text-align:right" value="'.$pval.'" name="val" id="price_'.$pid.'" class="input1 direct_edit">
					 <span class="i_check fa fa-check-square" id="prbtn_'.$pid.'" onclick="update_price('.$pid.');"></span>
				</div>';
				$salesdata='
				<div>
					 <input type="text" style="width:30px;" value="'.($this->page->shop?$products[$i]['salescount']:null).'" name="val" id="sales_'.$pid.'" class="input1 direct_edit">
					 <span class="i_check fa fa-check-square" id="sabtn_'.$pid.'" onclick="update_sales('.$pid.');"></span>
				</div>';
			}
			$ct_color='';
			$ct_colorbar=$this->page->categoriesModule->get_category_colorbar($products[$i]['cid'],'stock',$url_suffix,$ct_color);

			if($this->page->g_use_rel && isset($products_related[$pid]))
				foreach($products_related[$pid] as $k=>$rel)
				{
					$codedata.='
					<div>
						<input type="text" value="'.$rel['code'].'" name="val" id="code_'.$rel['pid'].'" class="input1 direct_edit">
						<span class="i_check fa fa-check-square" id="stcod_'.$rel['pid'].'" onclick="update_code('.$rel['pid'].');"></span>
					</div>';
					$name_link.='<p><a class="rvts8" style="text-decoration:none;line-height:22px;" target="_blank" href="'.$plink.($this->page->use_alt_plinks?'?':'&').'rel='.$rel['pid'].'">'.$rel['name'].'</a></p>';
					if($this->page->shop)
					{
						$stockdata.='
						<div>
							<input type="text" style="width:30px;" value="'.($this->page->shop?$rel['stock']:null).'" name="val" id="stock_'.$rel['pid'].'" class="input1 direct_edit">
							<span class="i_check fa fa-check-square" id="stbtn_'.$rel['pid'].'" onclick="update_stock('.$rel['pid'].');"></span>
						</div>';
						$pval=$this->page->nrf($this->page->shop?$rel[$this->page->pg_settings['g_pricefield']]:null);
						$pricedata.='
						<div>
							<input type="text" style="width:70px;text-align:right" value="'.$pval.'" name="val" id="price_'.$rel['pid'].'" class="input1 direct_edit">
							<span class="i_check fa fa-check-square" id="prbtn_'.$rel['pid'].'" onclick="update_price('.$rel['pid'].');"></span>
						</div>';
						$salesdata.='
						<div>
							<input type="text" style="width:30px;" value="'.($this->page->shop?$rel['salescount']:null).'" name="val" id="sales_'.$rel['pid'].'" class="input1 direct_edit">
							<span class="i_check fa fa-check-square" id="sabtn_'.$rel['pid'].'" onclick="update_sales('.$rel['pid'].');"></span>
						</div>';
					}
				}

			if($this->page->g_use_com)
				$name_link.=F_BR.Builder::buildAdminRanking($this->page->g_use_com?$products[$i]['ranking_count']:null,$this->page->g_use_com?$products[$i]['ranking_total']:null,$ct_color,$this->page->lang_l('rankings'));

			$inp='<input class="mng_entry_chck" type="checkbox" name="pid[]" value="'.$products[$i]['pid'].'">&nbsp;';
			$row_data=array($inp,$ima,array($name_link,$entry_nav),$codedata);
			if($this->page->shop)
			{
				$row_data[]=$stockdata;
				$row_data[]=$salesdata;
				$row_data[]=$pricedata;
			}
			if($show_visits)
				$row_data[]='<span class="rvts8">'.($show_visits?$products[$i]['visits_count']:null).'</span>';
			$row_data[]='<div id="lock_'.$pid.'" class="i_lock fa '.($published?'fa-unlock':'fa-lock').'"></div>';
			$row_data[]=$ct_colorbar;
			if($this->page->use_publish_dates)
			{
				$p_date=$products[$i]['published_date'];
				$up_date=$products[$i]['unpublished_date'];
				$published_date=strtotime($p_date);
				$unpublished_date=strtotime($up_date);
				$today=time();
				$zero_date='0000-00-00 00:00:00';
				$active=$today > $published_date && $today < $unpublished_date || ($p_date==$zero_date&&$up_date==$zero_date) || $up_date==$zero_date;
				$row_data[]='<div id="lock_'.$pid.'" class="i_lock fa '.($active?'fa-unlock':'fa-lock').'"></div>';
			}
			$table_data[]=$row_data;
		}

		$append='<input class="ca_button" name="del_entry" type="button" value="&#xf00d" title=" '.$this->page->lang_l('delete checked products').' " onclick="if(confirm(\''.$this->page->lang_l('del_products_msg').'\')) $(\'#form_features\').submit(); else return false; ">';
		$append.='<input type="button" value="'.$this->page->lang_l('export').'" onclick="document.location=\''.$this->page->full_script_path.'?action=stock&amp;do=export\'">';

		$right_content='<form id="mngEntriesFrm" method="post" action="'.$this->page->script_path.'?action=stock&amp;" enctype="multipart/form-data">';
		$right_content.=' <input type="text" name="q" value=""><input class="ca_button" type="submit" value="&#xf002" title=" '.$this->page->lang_l('search').' "></form>';
		$prepend=Filter::adminBar(array(),'',$right_content);
		$output=Builder::adminTable($prepend.$nav,$cap_arrays,$table_data,$append.$nav,'','',
						array('id'=>"form_features",'method'=>"post",'action'=>$this->page->script_path.'?action=stock&amp;do=delete_stockproduct','enctype'=>"multipart/form-data")).'</div>';
		if($filters)
			$this->output($output,'',false);
		else
		{
		  $scripts='
			$(document).ready(function(){assign_edits();});
			function filter_stock_enter(event) {if(event.keyCode==13)filter_stock()}
			function filter_stock(){$.post("'.$this->page->pg_name.'?action=stock&do=filter_stock&filter="+$("#filter_stock").val()+"&filter2="+$("#filter_stock2").val(),function(re){$(".a_body").html(re);assign_edits()})}
			function update_stock(pid){$.post("'.$this->page->pg_name.'?action=stock&do=edit_stock&pid="+pid+"&val="+$("#stock_"+pid).val(),function(re){$("#stbtn_"+pid).hide();})}
			function update_code(pid){$.post("'.$this->page->pg_name.'?action=stock&do=edit_code&pid="+pid+"&val="+$("#code_"+pid).val(),function(re){if(re=="1")$("#stcod_"+pid).hide();else alert(re);})}
			function update_price(pid){$.post("'.$this->page->pg_name.'?action=stock&do=edit_price&pid="+pid+"&val="+$("#price_"+pid).val(),function(re){$("#prbtn_"+pid).hide();})}
			function update_sales(pid){$.post("'.$this->page->pg_name.'?action=stock&do=edit_sales&pid="+pid+"&val="+$("#sales_"+pid).val(),function(re){$("#sabtn_"+pid).hide();})}
			function toggle_publish(pid){
				$.post("'.$this->page->pg_name.'?action=stock&do=toggle_publish&pid="+pid+"&p="+($("#lock_"+pid).hasClass("fa-lock")?1:0),function(re){
					 $("#lock_"+pid).toggleClass("fa-lock fa-unlock");$(".lock_a_"+pid).html(re);
				})}
			function close_se(){$(\'.serials\').remove();};
			function save_se(pid){var r=encodeURI($(\'.serials_edit\').val());$.post("'.$this->page->pg_name.'?action=stock&do=save_serials&pid="+pid+"&serials="+r,function(re){close_se();})};
			function delete_se(th,pid){
			var p=$(th).parents("p");
			ser=$(p).find(".ser").html();
			$.post("'.$this->page->pg_name.'?action=stock&do=delete_serial&pid="+pid+"&serial="+ser,function(re){});
			$(p).remove();
			}

			function show_serials(th,pid){
			close_se();

			$.get("'.$this->page->pg_name.'?action=stock&do=get_serials&pid="+pid,function(re){
				arr=re.split("|");
				html=\'<div class="serials">\';
				for(var i=0;i<arr.length;i++)
					 if(arr[i]!="")
						  html=html+\'<p>\'+arr[i]+\'&nbsp;<i class="i_close fa fa-close" onclick="delete_se(this,\'+pid+\')"></i></p>\';
			html==\'<div class="sub_bg rvts8 ser_buffer" style="max-height:200px;overflow:auto">\'+html+\'</div>\'
			html=html+\'<div><textarea style="width:90%" class="serials_edit"></textarea></div><input type="button" value="'.$this->page->lang_l('save').'" onclick="save_se(\'+pid+\');"><input type="button" onclick="close_se()" value="'.$this->page->lang_l('cancel').'"></div>\';
			$(th).parents(\'td\').append(html);
			});
			};
			';
			$this->page->page_scripts=$scripts;
			$this->page->page_css.='
				.i_close{font-size:14px;cursor:pointer;}
				.i_lock{font-size:16px;}
				.i_lock.fa-lock{color:red;}
				.i_lock.fa-unlock{color:green;}';
			$this->output($output);
		}
	}
}

class Options extends page_objects
{
	public $options;

	public function __construct($pg)
	{
		parent::__construct($pg);
		for($i=1;$i<=10;$i++)
			if(isset($this->page->pg_settings['g_fields_array']['option'.$i]))
				$this->options[$i]='option'.$i;
	}

	public function selectedOptions($v)
	{
		$result='';
		foreach($this->options as $opt)
		  $result.=isset($v['ol_'.$opt]) ? ListerFunctions::GetSubKey($v['ol_'.$opt]).' ' : ' ';

		return $result;
	}

	public function getSelectedValue($data,$subtype_id,$selValue)
	{
		$options=explode(';',$data['option'.$subtype_id]);
		return ($selValue!='' && $this->page->get_setting('op_inc_1st'))?$options[0].':'.$selValue:$selValue;
	}
}

class Wishlist extends page_objects
{
	public $user_id=false;
	protected $where_public_wishlist=' publish = 1 ';

	public function __construct($pg)
	{
		global $db;

		parent::__construct($pg);
		Session::intStart();
		$user_data=$pg->user->mGetLoggedUser($db,'',true);
		if($user_data!==false)
			$this->user_id=$user_data['uid'];
		$this->where_public_wishlist=str_replace(' AND (rel=0) ','',$this->page->where_public);
	}

	public function is_added_toWishlist($pid)
	{
		global $db;

		$is_added=false;
		if($this->user_id===false)
			return $is_added;

		$result=$db->fetch_all_array('SELECT  uid
			FROM '.$db->pre.$this->page->g_datapre.'wishlist
			WHERE FIND_IN_SET(\'"'.$pid.'"\',wishlist)>0 ORDER BY uid="'.$this->user_id.'" desc');
		if(!empty($result)&&$result[0]['uid']==$this->user_id)
			$is_added=count($result);

		return $is_added;
	}

	public function delete_wishlist($pid,$db,$for_all_users=false)
	{
		$db->query('UPDATE '.$db->pre.$this->page->g_datapre.'wishlist
		SET wishlist = REPLACE(`wishlist`, \'"'.$pid.'",\', \'\')
		'.($for_all_users?'':' WHERE uid = "'.$this->user_id.'"'));
	}

	public function process()
	{
		global $db;

		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if($do=='remove_wishlist'||$do=='add_wishlist')
		{
			$pid=isset($_REQUEST['pid'])?intval($_REQUEST['pid']):0;
			if($pid!=0)
			{
				if($do=='add_wishlist') //add product to wishlist
				{
					$result=$db->query_singlevalue('
						SELECT COUNT(*)
						FROM '.$db->pre.$this->page->g_datapre.'data
						WHERE '.$this->where_public_wishlist.' AND pid="'.$pid.'"');
					if($result!==null)
						$db->query('INSERT INTO '.$db->pre.$this->page->g_datapre.'wishlist (uid,wishlist)
						VALUES (\''.$this->user_id.'\', \'"'.$pid.'",\')
						ON DUPLICATE KEY UPDATE wishlist=CONCAT(IFNULL(wishlist,""), IF(FIND_IN_SET(\'"'.$pid.'"\',wishlist)>0,"",\'"'.$pid.'",\'))');
				}
				else //remove product from wishlist
					$this->delete_wishlist($pid,$db);
			}
			if(Linker::checkReturnURL(true)!==false)
				Linker::checkReturnURL();
			else
				Linker::redirect($this->page->full_script_path.'?action=wishlist',false);
		}
	}

	public function get_WishlistCountForUser()
	{
		global $db;

		if($this->user_id===false)
			return 0;
		$result=$db->fetch_all_array('SELECT wishlist
		FROM '.$db->pre.$this->page->g_datapre.'wishlist as w
		RIGHT JOIN '.$db->pre.$this->page->g_datapre.'data as d ON FIND_IN_SET(CONCAT(\'"\', d.pid, \'"\'),w.wishlist) > 0 AND '.$this->where_public_wishlist.'
		WHERE w.uid="'.$this->user_id.'" LIMIT 1');
		if(!empty($result)&&$result[0]['wishlist']!='')
		{
			$arr_wishlist=explode(',',$result[0]['wishlist']);
			$arr_wishlist=array_filter($arr_wishlist, create_function('$a','return trim($a)!=="";'));
			return count($arr_wishlist);
		}

		return 0;
	}

	public function replace_WihslistLink(&$src)
	{
		if($this->page->action_id=='wishlist')
			$wishlist_link='';
		else
			$wishlist_link='<a class="rvts4 wishlist_counter_link" href="'
						.($this->user_id===false?'javascript:void(0)" onclick="ealert(\''.$this->page->lang_l('wishlist message').'\')':$this->page->g_abs_path.$this->page->pg_name.'?action=wishlist').'">'
						.$this->page->lang_l('wishlist').($this->user_id===false?'':' ('.$this->get_WishlistCountForUser().')').'</a>';
		$src=str_replace('%WISHLIST_LINK%',$wishlist_link,$src);
	}

	public function replace_WishlistButtons(&$src,$pid,$wishlist_add_btn=false)
	{
		$macro=$wishlist_add_btn?'ADD_TO_WISHLIST':'REMOVE_FROM_WISHLIST'; //add or remove wishlist buttons
		$btn_string=Formatter::GFS($src,'<'.$macro.'>','</'.$macro.'>');
		$added=false;
		$parsed='';
		if($wishlist_add_btn)
			$added=$this->is_added_toWishlist($pid);

		if(!$added)
		{
			if($this->user_id===false)
				$url='javascript:void(0)" onclick="ealert(\''.$this->page->lang_l('wishlist message').'\')';
			else
				$url=$this->page->g_abs_path.$this->page->pg_name.'?action=wishlist&do='.($wishlist_add_btn?'add_wishlist':'remove_wishlist')
				.'&pid='.$pid.'&r='.Linker::buildReturnURL(false);

			if($this->page->action_id!='wishlist'&&!$wishlist_add_btn)
				$parsed='';
			elseif(strpos($btn_string,'<img')!==false){
				$img_src=Formatter::GFS($btn_string,'src="','"');
				$parsed='<a href="'.$url.'"><input type="image" style="text-align:center" src="'.$img_src.'"></a>';
			}else
				$parsed=str_replace('%URL%',$url,$btn_string);
		}
		elseif($this->page->action_id!='wishlist')
		{
			$parsed='<a class="rvts4 wishlist_link" href="'.$this->page->g_abs_path.$this->page->pg_name.'?action=wishlist">'.$this->page->lang_l('wishlist').'</a>';
			if($added>1)
				$parsed='<span class="rvts0 wishlist_info">'.str_replace('%%count%%',$added-1,$this->page->lang_l('wishlist info')).' '.$parsed.'</span>';
		}
		$src=str_replace('<'.$macro.'>'.$btn_string.'</'.$macro.'>',$parsed,$src);
	}
}

class Features extends page_objects
{
	public $compareNewPage=1;
	public $path;

	public function get_options($pid,$cid)
	{
		 global $db;

		 $cp=$this->get_categoryfeatures($cid);
		 $values=array();
		 if(!empty($cp))
		 {
				$temp=array();
				if($pid>0)
					$temp=$db->fetch_all_array('
						SELECT *
						FROM '.$db->pre.$this->page->g_datapre.'options
						WHERE pid = '.$pid);

				foreach($temp as $k=>$v)
				{
					if(isset($values[$v['pr_id']]))
						$values[$v['pr_id']]['value'].=';'.$v['value'];
					else
						$values[$v['pr_id']]=$v;
				}

				foreach($cp as $k=>$v)
				{
					$pr_id=$v['pr_id'];
					$cp[$k]['value']=isset($values[$pr_id])?$values[$pr_id]['value']:'';
				}
		 }
		 return $cp;
	}

	public function duplicate_product_pptions($pid,$newPid,$cid)
	{
		global $db;

		$options=$this->get_options($pid,$cid);
		foreach($options as $option)
			 $db->query_insert($this->page->g_datapre."options",array('value'=>$option['value'],'pr_id'=>$option['pr_id'],'pid'=>$newPid));
	}

	public function delete_options($pid)
	{
		 global $db;
		 $db->query('
			DELETE
			FROM '.$db->pre.$this->page->g_datapre.'options
			WHERE pid='.intval($pid));
	}

	public function save_options($pid,$cid)
	{
		 global $db;

		 $opt=$this->get_options($pid,$cid);
		 $option_values=array();
		 foreach($opt as $v)
			if(isset($_REQUEST['option_'.$v['pr_id']]))
				$option_values[$v['pr_id']]=$_REQUEST['option_'.$v['pr_id']];

		 $this->delete_options($pid);

		 foreach($option_values as $k=>$v)
		 {
			 $values=explode(';',$v);
			 foreach($values as $value)
				$db->query_insert($this->page->g_datapre."options",array('value'=>$value,'pr_id'=>intval($k),'pid'=>$pid));
		 }
	}

	public function get_allfeatures()
	{
		global $db;
		return $db->fetch_all_array('
			 SELECT *
			 FROM '.$db->pre.$this->page->g_datapre.'features
			 ORDER BY position');
	}

	public function get_categoryfeatures($cid)
	{
		global $db;
		return $db->fetch_all_array('SELECT *
			 FROM '.$db->pre.$this->page->g_datapre.'features f,'
				 .$db->pre.$this->page->g_datapre.'cat_features as cf
			 WHERE f.pr_id = cf.pr_id
			 AND cf.cid = '.$cid.'
			 ORDER BY position',false,'pr_id');
	}

	public function get_categoryfeatureProducts($cid,$cids,$filters)
	{
		 global $db;

		 $cnt=0;
		 $q=$filters==''?'':$this->page->filtersModule->get_filterSubQuery($filters,$cnt);
		 $fq=$this->page->filtersModule->get_filterQue(array(),true);
		 $data=array();

		 $data=$db->fetch_all_array('
			SELECT op.pid,op.pr_id,op.value,ft.name
			FROM '.$db->pre.$this->page->g_datapre.'options op
			LEFT JOIN '.$db->pre.$this->page->g_datapre.'data dt ON op.pid=dt.pid
			LEFT JOIN '.$db->pre.$this->page->g_datapre.'features ft ON ft.pr_id=op.pr_id
			WHERE 1 '.$fq.' AND '.($cid=='all'?' 1 ':' dt.cid IN ('.$cids.')').' '
					.($q!=''?' AND '.$q:'')
					.' AND dt.rel = 0 AND dt.publish = 1 AND ft.filter = 1
		   ORDER BY ft.position,ft.pr_id,op.value');

		 return $data;
	}

	public function get_categoryfeatureOptions($cid,$filters)
	{
		list($kids_count,$kids_ids)=$this->page->categoriesModule->get_category_kids_count($cid,true);
		$cids=is_array($kids_ids)?implode(',',$kids_ids):'';
		$cids.=($cids!='')?$cids.','.$cid:$cid;
		$features_unfiltered=$this->get_categoryfeatureProducts($cid,$cids,'');

		$data=array();
		foreach($features_unfiltered as $k=>$v)
		{
			 if(!isset($data[$v['pr_id']]))
				  $data[$v['pr_id']]=array($v['name'],array($v['value']=>array($v['pid'])));
			 else
			 {
				  if(!isset( $data[$v['pr_id']][1][$v['value']] ))
						$data[$v['pr_id']][1][$v['value']]=array($v['pid']);
				  else
						$data[$v['pr_id']][1][$v['value']][]=$v['pid'];
			 }
		}
		unset($features_unfiltered);

		$filters=array_reverse($filters);
		$filtersA=array();
		foreach($filters as $k=>$v)
		{
			 $filter=explode('|',$k);
			 if(!isset($filtersA[$filter[0]]))
				 $filtersA[$filter[0]]=array($filter[1]);
			 else
				 $filtersA[$filter[0]][]=$filter[1];
		}

		foreach($filtersA as $k=>$v)
		{
			 $filtered_products=array();
			 foreach($v as $value)
				  $filtered_products=array_merge($filtered_products,$data[$k][1][$value]);
			 foreach($data as $pr_id=>$fc)
			 {
				  if($pr_id!=$k)
				  {
						foreach($fc[1] as $option=>$pids)
						  $data[$pr_id][1][$option]=array_intersect($filtered_products,$pids);
				  }
			 }

		}

		return $data;
	}

	public function detete_feature($pr_id)
	{
		global $db;

		$pr_ids=(is_array($pr_id))? $pr_id:array($pr_id);
		foreach($pr_ids as $v)
		{
			 $id=intval($v);
			 $db->query('DELETE FROM '.$db->pre.$this->page->g_datapre.'features WHERE pr_id='.$id);
			 $db->query('DELETE FROM '.$db->pre.$this->page->g_datapre.'cat_features WHERE pr_id='.$id);
			 $db->query('DELETE FROM '.$db->pre.$this->page->g_datapre.'options WHERE pr_id='.$id);
		}
	}

	public function detete_options($pid)
	{
		global $db;
		$db->query('DELETE FROM '.$db->pre.$this->page->g_datapre.'options WHERE pid='.intval($pid));
	}

	public function options_list($pid,$cid,$readonly,$iw,&$cnt)
	{
		 $opt=$this->get_options($pid,$cid);
		 $result='';
		 $cnt=count($opt);
		 $locked=$this->page->get_setting('features_locked') && $this->page->edit_own_posts_only;

		 foreach($opt as $k=>$v)
		 {
			  $xclass=Unknown::isOdd($k)?'odd':'even';
			  $result.='<li class="'.$xclass.'" id="op_'.$v['pr_id'].'">'.
				($readonly?
					'<span class="lister_option_label"><label class="'.($v['description']!=''?'hhint ':'').'"'.($v['description']!=''?' title="::'.str_replace('"','&quot;',$v['description']).'"':'').'>'.$v['name'].'</label></span>
					 <span class="lister_option_span">'.($v['value']==''?'-':str_replace(';',' ',$v['value'])).'</span>':
					'<span class="rvts8 a_editcaption">'.$v['name'].'</span><br>'.
					 Builder::buildInput('option_'.$v['pr_id'],
												$v['value'],
												$iw,
												'',
												'text',
												'id="option_'.$v['pr_id'].'"'.($locked?' readonly="readonly" placeholder="'.$this->page->lang_l('use arrow to select value').'"':''),
												'','','option_'.$v['pr_id'],$xclass)
				).'
				</li>';
		 }
		 if($result!='')
			  $result='<ul id="options_list"'.($readonly?' class="lister_options"':'').' style="list-style:none">'.$result.'</ul>';
		 return $result;
	}

	public function compare_list($cookie='')
	{
		 global $db;

		 $products=$cookie==''?$this->readCompareCookie():$cookie;
		 if(count($products)<1)
			 $result='';
		 else
		 {
			  $data=$db->fetch_all_array('SELECT pid,cid,name,image1
					FROM '.$db->pre.$this->page->g_datapre.'data WHERE pid in ('.implode(',',$products).')
					ORDER BY cid ',false,'pid');
			  $result='';
			  $ct_buttons='
					<span class="compare_btn cmp_compare" onclick="$.get(\''.$this->path.'?action=compare&cid=%s\',function(d){
						 $(d).hide().appendTo(\'body\').fadeIn();if(typeof updateOnAjax==\'function\') updateOnAjax(4);
						 })">'.$this->page->lang_l('compare').'
					</span>
					<span class="compare_btn cmp_remove" onclick="$.get(\''.$this->path.'?action=compare&do=remove&return=list&pid=%s\',function(d){$(\'.lister_clist\').replaceWith(d);if(typeof updateOnAjax==\'function\') updateOnAjax(5);;});">'.
						 $this->page->lang_l('compare_remove').'
					</span>';
			  $cid=0;
			  $p_a=array();
			  foreach($data as $pv)
				{
					if($this->page->get_setting('cat_features') && $cid!=$pv['cid'])
					{
						 if($cid>0)
							 $result.='<li class="list_head">'.sprintf($ct_buttons,$cid,implode('|',$p_a)).'</li>';
						 $p_a=array();

						 $parentid=-1;
						 $cname=$this->page->categoriesModule->get_category_name($pv['cid'],$parentid);
						 $result.='<li class="list_head">'.$cname.'</li>';
						 $cid=$pv['cid'];
					}
					$p_a[]=$pv['pid'];
					$result.='
					<li class="list_line" data-id="'.$pv['pid'].'">
					 <a class="clist_name" href="'.$this->page->build_permalink($pv,false,'',true).'">'.$pv['name'].'</a>
	 				 <span class="compare_btn cmp_remove" onclick="$.get(\''.$this->path.'?action=compare&do=remove&pid='.$pv['pid'].'&return=list\',function(d){$(\'.lister_clist\').replaceWith(d);if(typeof updateOnAjax==\'function\') updateOnAjax(5);});">'.$this->page->lang_l('compare_remove').'</span>
					 <a class="clist_name_img" href="'.$this->page->build_permalink($pv,false,'',true).'">'.
					 (isset($pv['image1'])?'<img src="'.$pv['image1'].'" style="width:50px;">':'').'
					 </a>
					</li>';
				}
				$result.='<li class="list_head">'.sprintf($ct_buttons,$cid,implode('|',$p_a)).'</li>';
		 }

		 $result='<ul class="lister_clist" style="list-style:none">'.$result.'</ul>';
		 return $result;
	}

	public function compare_screen($cookie='',$remove=0)
	{
		 global $db;

		 $cid=($this->page->get_setting('cat_features') && isset($_REQUEST['cid']))?intval($_REQUEST['cid']):0;
		 $products=$cookie==''?$this->readCompareCookie():$cookie;
		 if(count($products)<1)
			 return 'nothing to compare';

		 $data=$db->fetch_all_array('
			SELECT *
			FROM '.$db->pre.$this->page->g_datapre.'data
			WHERE pid in ('.implode(',',$products).')'.($cid>0?' AND cid = '.$cid:''),false,'pid');

		 if(count($data)<1) return 'nothing to compare';

		 $features=array();
		 foreach($data as $k=>$v)
		 {
			 $opt=$this->get_options($v['pid'],$v['cid']);
			 $data[$k]['xoptions']=$opt;
			 foreach($opt as $pr_id=>$val)
				if(!isset($features[$pr_id]))
					$features[$pr_id]=array('description'=>$val['description'],'name'=>$val['name']);
		 }
		 $result='<li class="head odd"><span class="lister_option_label">&nbsp;</span>';
		 foreach($data as $pv)
			  $result.='<a class="lister_option_span multi" href="'.$this->page->build_permalink($pv,false,'',true).'">'.$pv['name'].'</a>';

		 $result.='<li class="head odd"><span class="lister_option_label">&nbsp;</span>';
		 foreach($data as $pv)
			  $result.='<a class="lister_option_span multi" href="'.$this->page->build_permalink($pv,false,'',true).'">'.
					(isset($pv['image1'])?'<img src="'.$pv['image1'].'" style="width:50px;">':'').'</a>';

		 $result.='</li>';
		 $cnt=0;
		 foreach($features as $k=>$v)
		 {
			  $line='<span class="lister_option_label">
						  <label class="'.($v['description']!=''?'hhint ':'').'"'.($v['description']!=''?' title="::'.str_replace('"','&quot;',$v['description']).'"':'').'>'.$v['name'].'</label>
						</span>';

			  $values=array();
			  foreach($data as $pv)
			  {
					$val=(isset($pv['xoptions'][$k]) && $pv['xoptions'][$k]['value']!=''?$pv['xoptions'][$k]['value']:'-');
					if(!in_array($val,$values))
						$values[]=$val;
					$line.='<span class="lister_option_span multi">'.$val.'</span>';
			  }

			  $xclass=Unknown::isOdd($cnt++)?'odd':'even'.(count($values)==1?' equal':'');
			  $result.='<li class="'.$xclass.'" id="op_'.$k.'">'.$line.'</li>';
		 }
		 $cnt=0;

		 $result.='<li class="even"><span class="lister_option_label">&nbsp;</span>';
		 foreach($data as $pv)
		 {
			  $result.='<span class="lister_option_span multi">'
					.(count($products)==1?'':
						 '<span class="compare_btn cmp_remove" onclick="$.get(\''.$this->path.'?action=compare&do=remove&pid='.$pv['pid'].'&return=compare&cid='.$pv['cid'].'\',function(d){$(\'#compare_list\').replaceWith(d);if(typeof updateOnAjax==\'function\') updateOnAjax(5);});$(\'li[data-id='.$pv['pid'].']\').remove();">'.$this->page->lang_l('compare_remove').'</span> ')
					.'</span>';
		 }
		 $result.='</li>';

		 $result='<div><ul id="compare_list" class="lister_options" style="list-style:none">'.$result.'</ul>
			<style>
			.lister_option_span.multi{width:'.(70/count($products)).'%;display:block;float:left;}
			.head .multi{text-align:center;}
			</style>
			</div>';

		 if(!$remove)
		 $result='
			<div id="confirmOverlay" style="width:100%;height:100%;position:fixed;top:0;left:0;z-index:100000;text-align:center;background: rgba(0,0,0,0.2);">
				<div id="confirmBox" style="margin-top:100px;padding:5px;border: 6px solid #FFFFFF;border-radius:6px;display:inline-block;background: #e5e5e5;">
					 '.$result.'
					 <div id="confirmButtons">
						  <a href="javascript:void(0);" id="cpmare_close_btn" class="button" onclick="$(\'#confirmOverlay\').remove();if(typeof closeCompare==\'function\') closeCompare();" style="display:inline-block;position:relative;font:13px/18px arial;text-decoration:none;padding:5px;margin-top:5px;float:right;color:#fff;border-radius:5px;background:#34a4d1;">'.$this->page->lang_l('close').'</a>
					 </div>
				</div>
			</div>';
		 return $result;
	}

	public function readCompareCookie()
	{
		return (isset($_COOKIE['compare']))?unserialize(urldecode($_COOKIE['compare'])):array();
	}

	public function cleanCompareCookie()
	{
		unset($_COOKIE['compare']);
      setcookie('compare', NULL, -1);
	}

	public function saveCompareCookie($cookie)
	{
		if($cookie==='')
			$this->cleanCompareCookie();
		else
			setcookie('compare', urlencode(serialize($cookie)), time()+3600*24,'/');
	}

	public function addToCompareCookie()
	{
		$pid=intval($_REQUEST['pid']);
	 	$cookie=$this->readCompareCookie();
		if(!in_array($pid,$cookie))
			$cookie[]=$pid;
		$this->saveCompareCookie($cookie);
		if(isset($_REQUEST['return']))
			echo $this->compare_list($cookie);
		else
			echo json_encode($cookie);
	}

	protected function removeCompareCookie()
	{
		$pid=isset($_REQUEST['pid'])?explode('|',$_REQUEST['pid']):0;

		if(count($pid)>0)
	 	{
			$cookie=$this->readCompareCookie();
			foreach($pid as $v)
				if(($key=array_search($v,$cookie)) !== false)
					unset($cookie[$key]);
	 		$this->saveCompareCookie($cookie);
			if(isset($_REQUEST['return']))
				echo $_REQUEST['return']=='compare' ? $this->compare_screen($cookie,1) : $this->compare_list($cookie);
			else
				echo json_encode($cookie);
		}
		else
			$this->saveCompareCookie('');
	}

	protected function compareAddBtn()
	{
		  return '<span class="compare_btn cmp_add" onclick="$.get(\''.$this->path.'?action=compare&do=add&return&pid=%pid%\',function(e){$(\'.lister_clist\').replaceWith(e);if(typeof updateOnAjax==\'function\') updateOnAjax(3);} )" type="button">'.$this->page->lang_l('compare_add').'</span>';
	}

	public function handle_compare($do='')
	{
		$this->path=$this->page->g_abs_path.$this->page->pg_name;
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:$do;
		if($do=='add')
			$this->addToCompareCookie();
		elseif($do=='remove'||$do=='removeajax')
			$this->removeCompareCookie();
		elseif($do=='clean')
			$this->cleanCompareCookie();
		elseif($do=='list')
			echo $this->compare_list();
		elseif($do=='addbtn')
			return $this->compareAddBtn();
		else
		{
			$result=$this->compare_screen();
			if($this->compareNewPage) echo $result;
			else echo $result;
		}
		exit;
	}
}

class mng_features_screen extends lister_admin_screens
{
	public function handle_screen()
	{
		global $db;

		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		$pr_id=isset($_REQUEST['pr_id'])?$_REQUEST['pr_id']:0;
		if($do=='add' || $do=='save')
		{
		  $data=array();
		  $data['name']=$_REQUEST['name'];
		  $data['filter']=isset($_REQUEST['filter']);
		  $data['description']=$_REQUEST['description'];
		  if($do=='add')
		  {
				$record=$db->query_first('
					SELECT MAX(position) AS maxpos
					FROM '.$db->pre.$this->page->g_datapre.'features');
				$data['position']=$record['maxpos']+1;
				$pr_id=$db->query_insert($this->page->g_datapre.'features',$data);
		  }
		  else
		  {
				$db->query_update($this->page->g_datapre.'features',$data,' pr_id ='.$pr_id);
				$db->query('
					DELETE
					FROM '.$db->pre.$this->page->g_datapre.'cat_features
					WHERE pr_id='.intval($pr_id));
		  }

		  $db->query('
					DELETE
					FROM '.$db->pre.$this->page->g_datapre.'defaultoptions
					WHERE pr_id='.intval($pr_id));

		  $options=explode(',',$_REQUEST['options']);

		  foreach($options as $k=>$v)
				if($v!='')
					 $db->query_insert($this->page->g_datapre.'defaultoptions',
						  array('pr_id'=>intval($pr_id),'value'=>$v,'order_by'=>$k));

		  if(isset($_REQUEST['categories']) && is_array($_REQUEST['categories']) && count(is_array($_REQUEST['categories']))>0)
		  {
				$c_a=$_REQUEST['categories'][0]==='all'?array_keys($this->page->categoriesModule->category_array):$_REQUEST['categories'];
				foreach($c_a as $v)
					$db->query_insert($this->page->g_datapre.'cat_features',array('cid'=>$v,'pr_id'=>$pr_id));
		  }
		}
		elseif($do=='delete')
			 $this->page->featuresModule->detete_feature($pr_id);
		elseif($do=='update_features')
			 $this->updateFeaturesPosition();
		elseif($do=='edit')
		{
			 $data=$db->query_first('
				 SELECT *
				 FROM '.$db->pre.$this->page->g_datapre.'features
				 WHERE pr_id = '.$pr_id);
			 $ct=$db->fetch_all_array('
				 SELECT cid
				 FROM '.$db->pre.$this->page->g_datapre.'cat_features
				 WHERE pr_id='.$pr_id);
			 $data['categories']=array();
			 foreach($ct as $v)
				 $data['categories'][]=$v['cid'];
			 $ct=$db->fetch_all_array('
				 SELECT value
				 FROM '.$db->pre.$this->page->g_datapre.'defaultoptions
				 WHERE pr_id='.$pr_id.' AND value <> ""
				 ORDER BY order_by'
				 ,true,'value');

			 $data['options']=implode(',',array_keys($ct));
			 $form=$this->feature_editor($data);
			 $this->output($form);
			 exit;
		}

		return $this->mng_features();
	}

	protected function updateFeaturesPosition()
	{
		global $db;

		foreach($_REQUEST['position'] as $k=>$pos)
			 $db->query('
				 UPDATE '.$db->pre.$this->page->g_datapre.'features
			    SET position = '.$k.'
				 WHERE pr_id = '.intval($pos));
		exit;
	}

	protected function feature_editor($data=array())
	{
		$table_data=array();
		$table_data[]=array($this->page->lang_l('name'),'<input class="input1" id="feature_name" type="text" name="name" value="'.(empty($data)?'':$data['name']).'" style="width:360px;">');
		$table_data[]=array($this->page->lang_l('used in categories'),$this->page->categoriesModule->build_category_combo($this->page->lang_l('all categories'),
						 'categories[]',empty($data)?'':$data['categories'],
						 ' multiple="" style="width:360px;height:400px;"','',true,1,'id'));
		$table_data[]=array($this->page->lang_l('description'),Builder::buildInput('description',empty($data)?'':$data['description'],'width:400px;','','textarea',''));
		$table_data[]=array('',Builder::buildCheckbox('filter',empty($data)?'':$data['filter'],$this->page->lang_l('used in filters')));

		$table_data[]=array($this->page->lang_l('options'),Builder::buildInput('options',empty($data)?'':$data['options'],'width:400px;','','textarea',''));

		$end='<input class="input1" onclick="return vAddFeature(\'\',\''.$this->page->lang_l('feature msg').'\')" type="submit" name="submit" value="'.$this->page->lang_l('save').'">&nbsp;
	 		<input class="input1" type="button" value="'.$this->page->lang_l('cancel').'" onclick="'.
					  (empty($data)?'javascript:sv(\'reply_to_\');':'document.location=\'?action=features\'').'">';

		$output=Builder::addEntryTable($table_data,$end);

		return '
			<form method="post" action="'.$this->page->script_path.'?action=features&amp;do='.(empty($data)?'add':'save&amp;pr_id='.$data['pr_id']).'">'.
				  $output.'
			</form>';
	}

	public function mng_features()
	{
		$cap_arrays=array(''=>'<input type="checkbox" onclick="$(\'.mng_entry_chck\').prop(\'checked\',$(this).is(\':checked\'));">',
			 $this->page->lang_l('name'),$this->page->lang_l('description'),$this->page->lang_l('used in filters'),'');
		$move_btns=' <input type="button" value=" &uarr; " onclick="moveUpRow(\'tr_%s\');updateFeaturesOrder();">
						 <input type="button" value=" &darr; " onclick="moveDownRow(\'tr_%s\');updateFeaturesOrder();">';
		$table_data=array();
		$return_param=Linker::buildReturnURL();
		$features=$this->page->featuresModule->get_allfeatures();

		foreach($features as $k=>$v)
		{
			$row_data=array();
			$row_data[]='<input type="hidden" name="position[]" value="'.$v['pr_id'].'"><input class="mng_entry_chck" type="checkbox" name="pr_id[]" value="'.$v['pr_id'].'">&nbsp;';

			$dl=$this->page->script_path.'?action=features&amp;do=delete&amp;pr_id='.$v['pr_id'].$return_param.'" onclick="javascript:return confirm(\''.$this->page->lang_l('del feature msg').'\');';
			$el=$this->page->script_path.'?action=features&amp;do=edit&amp;pr_id='.$v['pr_id'].$return_param;
			$entry_nav=array($this->page->lang_l('edit')=>$el,$this->page->lang_l('delete')=>$dl);
			$row_data[]=array('<div style="min-width:150px;"><span class="rvts8">'.$v['name'].'</span></div>',$entry_nav);
			$row_data[]='<span class="rvts8">'.$v['description'].'</span>';
			$row_data[]='<span class="rvts8">'.($v['filter']?' + ':' - ').'</span>';//Builder::buildCheckbox('filter',$v['filter'],'');
			$row_data[]=sprintf($move_btns,$k+1,$k+1);
			$table_data[]=$row_data;
		}

		$nav='
		  <input type="button" value="'.$this->page->lang_l('add feature').'" onclick="javascript:sv(\'reply_to_\');">
		  <div class="'.$this->page->f->atbgr_class.'" id="reply_to_" style="display:none;">'.
				  str_replace('a_n a_listing','',$this->feature_editor('')).'
		  </div>';
		$append='<input class="ca_button" name="del_entry" type="button" value="&#xf00d" title=" '.$this->page->lang_l('delete checked features').' " onclick="if(confirm(\''.$this->page->lang_l('del_products_msg').'\')) $(\'#form_features\').submit(); else return false; ">';

		$output=Builder::adminTable($nav,
				  $cap_arrays,$table_data,$append,'','',
				  array('id'=>"form_features",'method'=>"post",'action'=>$this->page->script_path.'?action=features&amp;do=delete','enctype'=>"multipart/form-data")).'</div>';
		$this->page->page_scripts.='
		function updateFeaturesOrder(){
		  $.post("'.$this->page->script_path.'",$("#form_features").serialize()+"&action=features&do=update_features",function(data) {console.log(data);});
		};
		';
		$this->output($output);
	}
}

class manage_settings_screen extends lister_admin_screens
{
	public function handle_screen()
	{
		global $db;

		$caption='';
		$page=isset($_REQUEST['p'])?intval($_REQUEST['p']):1;
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if($do=='savesettings')
		{
			 $this->save_settings($page);
			 $this->page->db_fetch_settings();
		}
		if($do=='reindex')
		{
			$counter=1;
			$sm=ini_get('safe_mode');
			if(!$sm && function_exists('set_time_limit') && strpos(ini_get('disable_functions'),'set_time_limit')===false)
				set_time_limit(86400);

			$data=$db->fetch_all_array('
				SELECT pid
				FROM '.$db->pre.$this->page->g_datapre.'data');
			foreach($data as $v)
			{
				$this->page->reindex_search($v['pid']);
				$counter++;
			}

			$caption=array('','<span class="rvts8">'.$counter.' '.$this->page->lang_l('reindexed').'</span>');
		}
		elseif($do=='updatedb')
			$this->updateDb();
		elseif($do=='updatedbLinked')
			$this->updateDbLinked();

		return $this->mng_settings($page,$caption);
	}

	protected function updateOrderLinesOptions($fname)
	{
		global $db;

		if(in_array($fname,$this->page->optionsModule->options))
			$db->query('
				ALTER TABLE '.$db->pre.$this->page->pg_pre.'orderlines
				ADD `ol_'.$fname.'` text ');
	}

	protected function updateDbLinked()
	{
		 foreach($this->page->optionsModule->options as $v)
			  $this->updateOrderLinesOptions($v);
	}

	protected function updateDb()
	{
		global $db;

		$db_struct=$db->db_fields($this->page->pg_settings['g_data'].'_data');
		foreach($this->page->pg_settings['g_fields_array'] as $k=>$v)
		{
			$field_exists=false;
			foreach($db_struct as $vv)
				if($k==$vv['Field']) {$field_exists=true;break;}
			if(!$field_exists)
			{
				$type=$v['type'];
				$options=$v['opt'];
				if($type=='integer' && $options=='')
					$options='default 0';
				$db->query('
						ALTER TABLE '.$db->pre.$this->page->g_datapre.'data
						ADD `'.$k.'` '.$type.' '.$options);
				$this->updateOrderLinesOptions($k);
			}
		}

		$sf=array("id","pid","cid","created","updated","stock","salescount","publish",
					"code","name","category","subcategory","price","vat",
					"shipping","html_description","short_description","image1","image2","image3","mp3",
					"download","keywords","description","friendlyurl");
		foreach($this->page->optionsModule->options as $opt)
			$sf[]=$opt;

		foreach($db_struct as $k=>$v)
			if(!array_key_exists($v['Field'],$this->page->pg_settings['g_fields_array']) && array_search($v['Field'],$sf)===false)
				$db->query('
						ALTER TABLE '.$db->pre.$this->page->g_datapre.'data
						DROP `'.$v['Field'].'`');

		$this->page->db_insert_settings(array('rel'=>'1','com'=>'1','pby'=>'1'));
	}

	protected function save_settings($p=2)
	{
		global $db;

		$data=array();
		if($this->page->shop && $p==2)
		{
			$data['bwsubject']=$_REQUEST['bwsubject'];
			if(!isset($_REQUEST['bwmess'])) return;
			$data['bwmess']=$this->page->convert_editor_macros($_REQUEST['bwmess']);
			$data['bw_notif_subject']=$_REQUEST['bw_notif_subject'];
			$data['bw_notif_message']=$this->page->convert_editor_macros($_REQUEST['bw_notif_message']);
			$data['pay_conf_subject']=$_REQUEST['pay_conf_subject'];
			$data['pay_conf_message']=$this->page->convert_editor_macros($_REQUEST['pay_conf_message']);
			$data['invoice_pdf']=$this->page->convert_editor_macros($_REQUEST['invoice_pdf']);
			$data['invoice_file_name']=$_REQUEST['invoice_file_name']!=''?Formatter::stripTags($_REQUEST['invoice_file_name']):'invoice';
			for($i=1;$i<=COUNT_ATTACMENTS;$i++){
				$data['attachment'.$i]=$_REQUEST['attachment'.$i]!=''?Formatter::stripTags($_REQUEST['attachment'.$i]):'';
			}
			$data['my_order']=$this->page->convert_editor_macros($_REQUEST['my_order']);
			$data['use_pdf']=(isset($_REQUEST['use_pdf'])?'1':'0');
			$data['use_attachments']=(isset($_REQUEST['use_attachments'])?'1':'0');
			$data['use_myo']=(isset($_REQUEST['use_myo'])?'1':'0');
		}
		elseif($p==3)
		{
			$data['g_ship_type']=$_REQUEST['g_ship_type'];
			$data['g_ship_vat']=$_REQUEST['g_ship_vat'];
			$data['g_ship_amount']=$_REQUEST['g_ship_amount'];

			$data['g_ship_above_limit']=$_REQUEST['g_ship_above_limit'];
			$data['g_ship_above_on']=(isset($_REQUEST['g_ship_above_on'])?'1':'0');

			$g_ship_settings='';
			$key=$data['g_ship_type']==7?'country':'key';
			foreach($_REQUEST[$key] as $k=>$v)
				if($v!='')
					$g_ship_settings.=$v.'='.$_REQUEST['value'][$k].'|';
			$data['g_ship_settings']=$g_ship_settings;
		}
		elseif($p==4)
		{
			$exch_rate_settings='';
			foreach($_REQUEST['key'] as $k=>$v)
			{
				if($v!='')
				{
					$exch_rate_settings.=$v.'='.floatval($_REQUEST['value'][$k]).'|';
					if(mb_strtolower($v)==mb_strtolower($this->page->pg_settings['g_currency']))
						$data['conversion_rate']=floatval($_REQUEST['value'][$k]);
				}
			}
			$data['exch_rate_settings']=$exch_rate_settings;
		}
		else
		{
			$data['subconfront']=(isset($_REQUEST['subconfront']))?'1':'0';
			$data['quickproduct']=(isset($_REQUEST['quickproduct']))?'1':'0';
			$data['plink_type']=$_REQUEST['plink_type'];
			$data['user_orderby']=$_REQUEST['user_orderby'];
			$data['cross_filters']=isset($_REQUEST['cross_filters'])?'1':'0';
			$data['filters_type']=intval($_REQUEST['filters_type']);
			$data['filters_limit']=intval($_REQUEST['filters_limit']);
			$data['features_type']=intval($_REQUEST['features_type']);
			$data['cat_features']=isset($_REQUEST['cat_features'])?'1':'0';
			$data['var_filters']=isset($_REQUEST['var_filters'])?'1':'0';
			$data['fullview_cf']=isset($_REQUEST['fullview_cf'])?'1':'0';
			$data['features']=isset($_REQUEST['features'])?'1':'0';
			$data['features_locked']=isset($_REQUEST['features_locked'])?'1':'0';
			$data['rec_viewed']=intval($_REQUEST['rec_viewed']);
			$data['empty_db_msg']=$_REQUEST['empty_db_msg'];
			$data['rss_pattern']=$_REQUEST['rss_pattern'];
			$data['translit']=(isset($_REQUEST['translit']))?'1':'0';
			$data['watermark']=(isset($_REQUEST['watermark']))?'1':'0';
			$data['watermark_position']=intval($_REQUEST['watermark_position']);
			$data['contentbuilder']=(isset($_POST['contentbuilder']))?'1':'0';
			$data['front_edit']=(isset($_POST['front_edit']))?'1':'0';
			$data['return_to_stock']=isset($_REQUEST['return_to_stock'])?'1':'0';
			$data['error_404_page']=isset($_REQUEST['error_404_page'])?$_REQUEST['error_404_page']:'';

			if(count($_FILES))
			{
				if($_FILES['up_watermark']['type']="image/png")
					move_uploaded_file($_FILES['up_watermark']['tmp_name'],$this->page->rel_path.'innovaeditor/assets/watermark.png');
			}

			$data['status_options']=$_REQUEST['status_options'];

			if($this->page->shop)
			{
				$data['id']=intval($_REQUEST['setup_id']);
				$data['serials']=isset($_REQUEST['serials'])?'1':'0';
				$data['use_ajax']=isset($_REQUEST['use_ajax'])?'1':'0';
				$data['inv_id']=intval($_REQUEST['inv_id']);
				$data['stock_mess']=$_REQUEST['stock_mess'];
				$data['min_order']=intval($_REQUEST['min_order']);
				$data['min_order_alert']=$_REQUEST['min_order_alert'];
				$data['stock_dis']=(isset($_REQUEST['stock_dis'])?'1':'0');
				$data['stock_rem']=(isset($_REQUEST['stock_rem'])?'1':'0');
				$data['auto_confirm']=(isset($_REQUEST['auto_confirm'])?'1':'0');
				$data['down_path']=$_REQUEST['down_path'];
				$data['coupon_allpages']=isset($_REQUEST['coupon_allpages'])?'1':'0';
				$data['allow_null_cart']=isset($_REQUEST['allow_null_cart'])?'1':'0';
				$data['allow_float_amount']=isset($_REQUEST['allow_float_amount'])?'1':'0';
				$data['public_rss']=(isset($_REQUEST['public_rss'])?'1':'0');
				foreach($this->page->optionsModule->options as $opt)
					$data[$opt.'_req']=(isset($_REQUEST[$opt.'_req'])?'1':'0');
				$data['op_inc_1st']=(isset($_REQUEST['op_inc_1st'])?'1':'0');
				$data['ui_req']=(isset($_REQUEST['ui_req'])?'1':'0');
			}
		}

		if($this->page->shop && $data['allow_float_amount']=='1' && $this->page->get_setting('allow_float_amount')==0)
			 $db->query('ALTER TABLE '.$db->pre.$this->page->g_datapre.'orderlines
				  MODIFY ol_amount decimal(16,8)');
		if($this->page->shop && $data['allow_float_amount']=='0' && $this->page->get_setting('allow_float_amount')==1)
			 $db->query('ALTER TABLE '.$db->pre.$this->page->g_datapre.'orderlines
				  MODIFY ol_amount int(10)');

		$this->page->db_insert_settings($data);
	}

	protected function shipping_settings()
	{
		$shipping_options=array($this->page->lang_l('based on total cost'),$this->page->lang_l('based on number of products'),
			$this->page->lang_l('proportional to number of products in the order'),
			$this->page->lang_l('flat rate shipping charge'),
			$this->page->lang_l('do not use ezg shipping'),
			$this->page->lang_l('use shipping field'),
			$this->page->lang_l('based on shipping field total'),
			$this->page->lang_l('based on country'),
			9=>$this->page->lang_l('based on custom shipping list'));
		if(isset($this->page->pg_settings['g_fields_array']['shippingweight']))
			$shipping_options[8]=$this->page->lang_l('based on total weight');
		$countries=array_merge(array(''=>$this->page->lang_l('select country'),'other'=>'other'),Builder::getCountriesArray_fromtxt($this->page->rel_path));
		$table_data[]=array($this->page->lang_l('shipping type'),
			 Builder::buildSelect('g_ship_type',$shipping_options,$this->page->all_settings['g_ship_type'],'','key','onchange="handle_ship_type(this);"','',''));

		$shop_shipping_settings=explode("|",$this->page->all_settings['g_ship_settings']);
		$shipping_controls='
			<div id="val_table_cont">
			<table id="val_table">
				 <tr class="head">
				 <td><span class="rvts8 a_editcaption" id="option_title">Total Cost</span></td>
				 <td><span class="rvts8 a_editcaption">Amount</span></td>
				 <td></td></tr>';
		$cnt=count($shop_shipping_settings);
		foreach($shop_shipping_settings as $k=>$v)
			 $shipping_controls.='<tr class="trp'.($k<$cnt-1?'':' last').'">
				  <td id="ship_opt_'.$k.'">'
					 .Builder::buildSelect2('country[]','sel_'.$k,$countries,Formatter::GFS($v,'','='),'','','',' class="sel"')
					 .Builder::buildInput('key[]',Formatter::GFS($v,'','='),'width:150px','','text','','','','',0,'',' edi').'
					</td>
				  <td>'.Builder::buildInput('value[]',Formatter::GFS($v,'=',''),'width:120px').'</td>
				  <td><input class="rmv_button" type="button" value=" x " onclick="$(this).parents(\'.trp\').remove()">
				  '.($k<$cnt-1?'':'<input class="add_button" type="button" value="+" onclick="clone_last()">').'</td>
				  </tr>';

		$shipping_controls.='
			</table>
			</div>
			<div id="cpu">
				<span class="rvts8 a_editcaption" id="cpu_title">Cost Per Unit</span><br>'.
				  Builder::buildInput('g_ship_amount',$this->page->all_settings['g_ship_amount']).'
			</div>
		  ';

		$this->page->page_scripts='
			$(document).ready(function(){handle_ship_type();});

			function clone_last()
			{
				cl=$("#val_table tr:last" ).clone();
				$("#val_table tr:last" ).removeClass("last");
				$(cl).appendTo("#val_table");
			}
			function handle_ship_type()
			{
				v=$("#g_ship_type").val();
				$("#val_table_cont").toggle(v<2 || v>5);
				$("#cpu").toggle(v==2 || v==3);
				$(".sel").toggle(v==7);
				$(".edi").toggle(v!=7);
				if(v==2) $("#cpu_title").text("cost per unit");
				else if(v==3) $("#cpu_title").text("fixed cost per order");
				else if(v==0) $("#option_title").text("total cost");
				else if(v==1) $("#option_title").text("number of products");
				else if(v==6) $("#option_title").text("shipping field total");
				else if(v==7) $("#option_title").text("country");
				else if(v==8) $("#option_title").text("total weight");
				else if(v==9) $("#option_title").text("custom shipping list");
			};

		  ';
		$this->page->page_css.='
			 .last .rmv_button,.add_button{display:none;}
			 .last .add_button{display:block;}
			 #val_table{border:1px solid #555555;padding:0;}
			 #val_table .head{background: #555555;border: 1px solid #555555;}
			 .head span{padding:3px;}
			 ';

		$table_data[]=array($this->page->lang_l('shipping options').'<br>',$shipping_controls);

		$table_data[]=array('',Builder::buildCheckbox('g_ship_above_on',$this->page->all_settings['g_ship_above_on'],$this->page->lang_l('free shipping above'))
			 .'<br>'.Builder::buildInput('g_ship_above_limit',$this->page->all_settings['g_ship_above_limit'],'','','text'));
		$table_data[]=array($this->page->lang_l('shipping vat'),Builder::buildInput('g_ship_vat',$this->page->all_settings['g_ship_vat'],'','','text'));

		return $table_data;
	}

	protected function exchane_rate_settings()
	{
		$exch_rate_settings=explode("|",$this->page->all_settings['exch_rate_settings']);

		$controls='
			<div id="val_table_cont">
			<table id="val_table">
				 <tr class="head">
				 <td><span class="rvts8 a_editcaption" id="option_title">Currency</span></td>
				 <td><span class="rvts8 a_editcaption">Rate</span></td>
				 <td></td></tr>';
		$cnt=count($exch_rate_settings);
		foreach($exch_rate_settings as $k=>$v){
			list($key, $value)=explode('=',$v);
			$controls.='<tr class="trp'.($k<$cnt-1?'':' last').'">
				<td>'
				.Builder::buildInput('key[]',$key,'width:150px','','text','','','','',0,'',' edi').'
				</td>
				<td>'.Builder::buildInput('value[]',$value,'width:120px','','text','onkeyup="validate(this)"').'</td>
				<td><input class="rmv_button" type="button" value=" x " onclick="$(this).parents(\'.trp\').remove()">
				'.($k<$cnt-1?'':'<input class="add_button" type="button" value="+" onclick="clone_last()">').'</td>
				</tr>';
		}
		$this->page->page_scripts='function clone_last()
			{
				cl=$("#val_table tr:last" ).clone();
				$("#val_table tr:last" ).removeClass("last");
				$(cl).appendTo("#val_table");
			}
			function validate(input){
				type = parseInt(type);
				var tester= /^[0-9., ]*$/;
				if(!tester.test($(input).val())) {
					$(input).val($(input).val().substr(0,$(input).val().length-1));}
			};';
					$this->page->page_css.='
			 .last .rmv_button,.add_button{display:none;}
			 .last .add_button{display:block;}
			 #val_table{border:1px solid #555555;padding:0;}
			 #val_table .head{background: #555555;border: 1px solid #555555;}
			 .head span{padding:3px;}
			 ';
		return $table_data[]=array('   ',$controls.'</table>');
	}

	protected function mng_settings($page=2,$caption='')
	{
		$result=$this->f->navtop;

		if($this->page->shop)
		{
			$result.='
			<div><div style="display:inline">
					 <input class="settings_tab'.($page==1?' active':'').'" type="button" '.($page==1?'disabled="disabled"':'').' onclick="document.location=\''.$this->page->full_script_path.'?action=settings&amp;p=1\'" value="'.$this->page->lang_l('setup').'">
					 <input class="settings_tab'.($page==2?' active':'').'" type="button" '.($page==2?'disabled="disabled"':'').' onclick="document.location=\''.$this->page->full_script_path.'?action=settings&amp;p=2\'" value="'.$this->page->lang_l('notifications').'">
					 <input class="settings_tab'.($page==3?' active':'').'" type="button" '.($page==3?'disabled="disabled"':'').' onclick="document.location=\''.$this->page->full_script_path.'?action=settings&amp;p=3\'" value="'.$this->page->lang_l('shipping').'">
					 <input class="settings_tab'.($page==4?' active':'').'" type="button" '.($page==4?'disabled="disabled"':'').' onclick="document.location=\''.$this->page->full_script_path.'?action=settings&amp;p=4\'" value="'.$this->page->lang_l('exchange rates').'">
					</div>
					<div class="rvps2" style="float:right">';
		}
		$result.=($this->page->g_linked?'':'
			<input name="import" type="button" onclick="document.location=\''.$this->page->full_script_path.'?action=import\'" value="'.$this->page->lang_l('import').'">').'
			<input type="button" onclick="document.location=\''.$this->page->full_script_path.'?action=settings&amp;do=reindex\'" value=" '.$this->page->lang_l('reindex').' ">';
		if(!$this->page->g_linked || count($this->page->optionsModule->options)>3)
		$result.='
			<input type="button" onclick="document.location=\''.$this->page->full_script_path.'?action=settings&amp;do='.($this->page->g_linked?'updatedbLinked':'updatedb').'\'" value=" '.$this->page->lang_l('update database').' ">';
		if($this->page->shop)
			$result.='</div></div>';
		$result.='</div></div>
				<form name="frm" method="post" action="'.$this->page->g_abs_path.$this->page->pg_name.'?action=settings&amp;do=savesettings" enctype="multipart/form-data">
					<input type="hidden" name="p" value="'.$page.'"><br>';

		$table_data=array();
		if($caption!=='')
			$table_data[]=$caption;

		if($this->page->shop)
		{
			if($page==3)
				$table_data=$this->shipping_settings();
			elseif($page==4)
				$table_data=$this->exchane_rate_settings();
			elseif($page==2)
			{
				$table_data[]=array($this->page->lang_l('Bank Wire Confirmation Subject'),Builder::buildInput('bwsubject',$this->page->get_setting('bwsubject'),'width:99%;','','text',''));
				$entry=$this->page->prepare_editor('bwmess',$this->page->innova_def);
				$table_data[]=array($this->page->lang_l('Bank Wire Confirmation E-mail'),$entry);

				$table_data[]=array($this->page->lang_l('Bank Wire Notification Subject'),Builder::buildInput('bw_notif_subject',$this->page->get_setting('bw_notif_subject'),'width:99%;','','text',''));

				$entry=$this->page->prepare_editor('bw_notif_message',$this->page->innova_def);
				$table_data[]=array($this->page->lang_l('Bank Wire Notification E-mail'),$entry);
				$table_data[]=array($this->page->lang_l('Order Confirmation Subject'),Builder::buildInput('pay_conf_subject',$this->page->get_setting('pay_conf_subject'),'width:99%;','','text',''));

				$entry=$this->page->prepare_editor('pay_conf_message',$this->page->innova_def);
				$table_data[]=array($this->page->lang_l('Order Confirmation E-mail'),$entry);

				$table_data[]=array('',Builder::buildCheckbox('use_pdf',$this->page->get_setting('use_pdf'),$this->page->lang_l('use pdf')));
				$table_data[]=array($this->page->lang_l('Invoice file name'),Builder::buildInput('invoice_file_name',$this->page->get_setting('invoice_file_name'),'width:300px;','','text',''));
				$entry=$this->page->prepare_editor('invoice_pdf',$this->page->innova_def);
				$table_data[]=array($this->page->lang_l('pdf invoice'),$entry);

				$cap='<br><p class="rvts12">'.$this->page->lang_l('Up to '.COUNT_ATTACMENTS.' files can be attached to confirmation email. Only relative paths are allowed.').'</p>';
				$browse_input = ' <input class="input1" type="button" name="btnAsset" onclick="openAsset(\'attachment%s\')" value="'.$this->page->lang_l('browse').'"/>';
				$att_label = '<span class="rvts12">%s </span>';
				$attachments = array();
				for($i=1;$i<=COUNT_ATTACMENTS;$i++){
					$attachments[] = sprintf($att_label,$i).Builder::buildInput('attachment'.$i,$this->page->get_setting('attachment'.$i),'width:70%','','text',' id="attachment'.$i.'"').sprintf($browse_input,$i);
				}
				$table_data[]=array(Builder::buildCheckbox('use_attachments',$this->page->get_setting('use_attachments')).$this->page->lang_l('use attachments to Confirmation email'),implode('<br><br>',$attachments).$cap);

				$table_data[]=array('',Builder::buildCheckbox('use_myo',$this->page->get_setting('use_myo'),$this->page->lang_l('use_order_template')));
				$entry=$this->page->prepare_editor('my_order',$this->page->innova_def);
				$table_data[]=array($this->page->lang_l('order_template'),$entry);

				$this->page->innova_on_output=true;
			}
			else
			{
				$table_data[]=array('','<span class="rvts8 a_editcaption">'.$this->page->lang_l('Last Order ID').'</span><br>'.Builder::buildInput('setup_id',$this->page->get_setting('id'),'width:100px;','','text',''),
					 '<span class="rvts8 a_editcaption">'.$this->page->lang_l('next invoice').'</span><br>'.Builder::buildInput('inv_id',$this->page->get_setting('inv_id'),'width:100px;','','text',''));
				$table_data[]=array($this->page->lang_l('Downloads default path'),Builder::buildInput('down_path',$this->page->get_setting('down_path'),'width:99%;','','text',''));
				$options=array('');

				foreach($this->page->optionsModule->options as $opt)
						$options[]=Builder::buildCheckbox($opt.'_req',$this->page->get_setting($opt.'_req'),$this->page->lang_l($opt.' required'));

				$options[]=Builder::buildCheckbox('op_inc_1st',$this->page->get_setting('op_inc_1st'),$this->page->lang_l('first as label'));
				$table_data[]=$options;

				$table_data[]=array('',Builder::buildCheckbox('ui_req',$this->page->get_setting('ui_req'),$this->page->lang_l('user input required')));
				$table_data[]=array('',Builder::buildCheckbox('public_rss',$this->page->get_setting('public_rss'),$this->page->lang_l('public rss')));
				$table_data[]=array('',Builder::buildCheckbox('coupon_allpages',$this->page->get_setting('coupon_allpages'),$this->page->lang_l('coupon on all')));
				$table_data[]=array('',Builder::buildCheckbox('allow_null_cart',$this->page->get_setting('allow_null_cart'),$this->page->lang_l('allow null cart')));
				$table_data[]=array('',Builder::buildCheckbox('allow_float_amount',$this->page->get_setting('allow_float_amount'),$this->page->lang_l('allow decimal amount')));
				$table_data[]=array('',Builder::buildCheckbox('serials',$this->page->get_setting('serials'),$this->page->lang_l('serials')));
				$table_data[]=array('',Builder::buildCheckbox('use_ajax',$this->page->get_setting('use_ajax'),$this->page->lang_l('use ajax')));
				$table_data[]=array('',Builder::buildCheckbox('front_edit',$this->page->get_setting('front_edit'),$this->page->lang_l('front detail page editor')));
				//$table_data[]=array('',Builder::buildCheckbox('contentbuilder',$this->page->all_settings['contentbuilder'],$this->page->lang_l('contentbuilder')));
				$table_data[]=array($this->page->lang_l('minimum order'),Builder::buildInput('min_order',$this->page->get_setting('min_order'),'width:100px;','','text',''));
				$table_data[]=array($this->page->lang_l('minimum order alert'),Builder::buildInput('min_order_alert',$this->page->get_setting('min_order_alert'),'width:99%;','','text',''));
				$table_data[]=array('',
								Builder::buildCheckbox('stock_rem',$this->page->get_setting('stock_rem'),$this->page->lang_l('remove out of stock')).'<br>'.
								Builder::buildCheckbox('stock_dis',$this->page->get_setting('stock_dis'),$this->page->lang_l('disable out of stock')).
								'<div style="padding-left:40px;"><span class="rvts8 a_editcaption">'.$this->page->lang_l('out of stock msg').'</span><br>'
								.Builder::buildInput('stock_mess',$this->page->get_setting('stock_mess'),'width:99%;','','text','').'</div>');
				$table_data[]=array('',Builder::buildCheckbox('auto_confirm',$this->page->get_setting('auto_confirm'),$this->page->lang_l('auto_confirm')));
				$plink_type_value=$this->page->get_setting('plink_type');
				if($plink_type_value=='')
					$plink_type_value='default';
			}
		}
		else
			$plink_type_value=$this->page->get_setting('plink_type');

		if($page==1 || !$this->page->shop)
		{
			$f_array=array(F_CHECKBOX=>$this->page->lang_l('checkbox'),F_RADIO=>$this->page->lang_l('radio'),F_DROPDOWN=>$this->page->lang_l('dropdown'));
			$l_array=array(0=>$this->page->lang_l('none'),4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10);
			$table_data[]=array($this->page->lang_l('filters'),
				Builder::buildCheckbox('cross_filters',$this->page->get_setting('cross_filters'),$this->page->lang_l('cross filters')).'<br>'.
				Builder::buildCheckbox('var_filters',$this->page->get_setting('var_filters'),$this->page->lang_l('var filters')).'<br> '.
				Builder::buildCheckbox('fullview_cf',$this->page->get_setting('fullview_cf'),$this->page->lang_l('clean filters')),
				$this->page->lang_l('type'),Builder::buildSelect('filters_type',$f_array,$this->page->get_setting('filters_type'))
				 );
			$table_data[]=array('',
				Builder::buildCheckbox('features',$this->page->get_setting('features'),$this->page->lang_l('features')),
				Builder::buildCheckbox('cat_features',$this->page->get_setting('cat_features'),$this->page->lang_l('category features')).' ',
				Builder::buildCheckbox('features_locked',$this->page->get_setting('features_locked'),$this->page->lang_l('features locked')).' ',
				$this->page->lang_l('type').' ('.$this->page->lang_l('filters').')',Builder::buildSelect('features_type',$f_array,$this->page->get_setting('features_type')),
				$this->page->lang_l('limit').' ('.$this->page->lang_l('filters').')',Builder::buildSelect('filters_limit',$l_array,$this->page->get_setting('filters_limit'))
				);

			$table_data[]=array('',Builder::buildCheckbox('translit',$this->page->get_setting('translit'),$this->page->lang_l('translit')));
			$table_data[]=array('',Builder::buildCheckbox('return_to_stock',$this->page->get_setting('return_to_stock'),$this->page->lang_l('return to stock')));

			$wpArray=array(0=>$this->page->lang_l('bottom'),1=>$this->page->lang_l('center'));
			$table_data[]=array('',
					Builder::buildCheckbox('watermark',$this->page->get_setting('watermark'),$this->page->lang_l('watermark'))
					.' <input type="file" name="up_watermark" accept="image/x-png"> '
					.Builder::buildSelect('watermark_position',$wpArray,$this->page->get_setting('watermark_position')));

			$table_data[]=array('',
					Builder::buildCheckbox('subconfront',$this->page->all_settings['subconfront'],$this->page->lang_l('subcategories on frontpage')));

			$table_data[]=array('',
					Builder::buildCheckbox('quickproduct',$this->page->all_settings['quickproduct'],$this->page->lang_l('Quick product when create New Product')));

			$table_data[]=array($this->page->lang_l('recently viewed limit'),
					Builder::buildInput('rec_viewed',$this->page->get_setting('rec_viewed'),'width:100px;','','text',''));

			$table_data[]=array($this->page->lang_l('orderby combo'),
					Builder::buildInput('user_orderby',$this->page->get_setting('user_orderby'),'width:99%;','','text',''));
			$table_data[]=array($this->page->lang_l('status options'),
					Builder::buildInput('status_options',$this->page->get_setting('status_options'),'width:99%;','','text',''));

			$table_data[]=array($this->page->lang_l('empty db'),
					Builder::buildInput('empty_db_msg',$this->page->get_setting('empty_db_msg'),'width:99%;','','textarea',''));
			$table_data[]=array($this->page->lang_l('rss pattern'),
					Builder::buildInput('rss_pattern',$this->page->get_setting('rss_pattern'),'width:99%;','','textarea',''));

			$table_data[]=array($this->page->lang_l('error 404 page'),
					Builder::buildInput('error_404_page',$this->page->get_setting('error_404_page'),'width:99%;','','','').'<br/><span class="rvts8"><i>'
					.$this->page->lang_l('Redirect customers to 404 error page if product typed in browser is wrong. Only name of the page is needed which must be located in web root. Example: 404page.html').'</i></span>');
			$def_plink_type=(isset($plink_type_value))? $plink_type_value: 'default';

			$request=dirname(Linker::requestUri()).'/';
			$plink='
				<div style="text-align:left;height:80px;padding-left:10px;">
					 <input type="radio" name="plink_type" value="default" onclick="$(\'#htaccess_category_title\').hide();" ' .($def_plink_type=='default'?'checked="checked"':'').'>
					 <span class="rvts8">'.$this->page->lang_l('plink_default').' - <i>'.$this->page->full_script_path.'?category=2&amp;iid=23</i></span><br>
					 <input type="radio" name="plink_type" value="category_name_short" onclick="$(\'#htaccess_category_title\').show();" ' .($def_plink_type=='category_name_short'?'checked="checked"':'').'>
	 				 <span class="rvts8">'.$this->page->lang_l('plink_short').' - <i>'.$this->page->full_script_path.'/category/product-name/</i></span><br>
					 <input type="radio" name="plink_type" value="category_name" onclick="$(\'#htaccess_category_title\').show();" '.($def_plink_type=='category_name'?'checked="checked"':'').'>
					 <span class="rvts8">'.$this->page->lang_l('plink_ct').' - <i>' .str_replace('/'.$this->page->pg_name,'',$this->page->full_script_path).'/category/product-name/product/23/</i></span><br>
					 <input type="radio" name="plink_type" value="product_id" onclick="$(\'#htaccess_category_title\').show();" '.($def_plink_type=='product_id'?'checked="checked"':'').'>
					 <span class="rvts8">'.$this->page->lang_l('plink_product').' - <i>' .str_replace('/'.$this->page->pg_name,'',$this->page->full_script_path).'/product/23/</i></span>
				</div>
				<div id="htaccess_category_title" style="display:'.($def_plink_type=='default'?'none':'block').';padding:3px 0px 5px 25px;">
					 <p style="color: #fd6868;font-size:13px">To enable this option, manually create file .htaccess (in '.$request.' directory on server) with following content:</p>
					 <p><span class="rvts8">&lt;IfModule mod_rewrite.c&gt;<br>
						RewriteEngine On<br>
						RewriteBase '.$request.'<br>
						RewriteRule ^'.str_replace('.php','',$this->page->pg_name).'\.php$ - [L]'.'<br>
						RewriteCond %{REQUEST_FILENAME} !-f<br>
						RewriteCond %{REQUEST_FILENAME} !-d<br>
						RewriteRule . '.$request.$this->page->pg_name.' [L]<br>
						&lt;/IfModule&gt;</span>
				</p>
				</div>';
			$table_data[]=array($this->page->lang_l('product url'),$plink);
		}

		$end='<input name="save" type="submit" value="'.$this->page->lang_l('save').'">';
		$result.=Builder::addEntryTable($table_data,$end,' width="99%"').'</form></div>';

		$this->output($result);
	}
}

class mng_comments_screen extends lister_admin_screens
{
	public function handle_screen()
	{
		global $db;

		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		if(isset($_REQUEST['comment_id']))
			$comment_id=intval($_REQUEST['comment_id']);
		if(isset($_REQUEST['entry_id']))
			$entry_id=intval($_REQUEST['entry_id']);

		if($do=='blockip' || $do=='unblockip')
			$output=$this->page->blockedIpModule->ip_blocking();
		elseif($do=='check_blockedip')
		{
			$all_blocked_ips=$db->fetch_all_array('SELECT * FROM '.$db->pre.'blocked_ips');
			$output=Formatter::fmtBlockedIPs($all_blocked_ips,$this->page->script_path,$this->page->lang_l('unblock ip'),$this->page->lang_l('no blocked ips'));
			$this->output($output);
			exit;
		}
		elseif($do=='reply' && isset($_POST['submit']) && isset($entry_id))
			$this->page->commentModule->reply_comment($entry_id);
		elseif($do=='toggle' || $do=='approve' || $do=='unapprove')
			$this->page->commentModule->toggle_comment($comment_id,$do);
		elseif($do=='spam')
		{
			 $this->page->commentModule->spam_comment($comment_id);
			 exit;
		}
		elseif($do=='delete' && isset($comment_id))    // delete comment
			$this->page->commentModule->del_comment($comment_id,true);

		if(isset($_POST['Post']))
			$output=$this->page->commentModule->edit_comment($entry_id,'full_access');
		else
			$output=$this->page->manage_comments();

		$this->output($output);
	}
}

class mng_products_screen extends lister_admin_screens
{

	public function handle_screen()
	{
		global $db;

		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		$pid=(isset($_REQUEST['pid']))?intval($_REQUEST['pid']):0;

		if($do=='delete')
		{
			if($pid>0)
			{
				$db->query('
					 DELETE
					 FROM '.$db->pre.$this->page->g_datapre.'data
					 WHERE ( pid= '.$pid.' ) '.($this->page->g_use_rel?' OR ( rel = '.$pid.' )':''));
				$this->page->featuresModule->detete_options($pid);
				$this->page->wishlistModule->delete_wishlist($pid,$db,true);

				$tc=new tags_cloud($this->page);
				$tc->clean_tagcloud();

				$this->page->reindex_search($pid);
			}
		}
		elseif($do=='category_features')
		{
			 $cid=(isset($_REQUEST['cid']))?intval($_REQUEST['cid']):0;
			 $cols=$this->page->get_setting('prod_cols');
			 $iw=($cols==1?$this->page->inp_width:'width:70%;');
			 $cnt=0;
			 print $this->page->featuresModule->options_list($pid,$cid,0,$iw,$cnt);
			 exit;
		}

		if($this->page->action_id=='toggle_cols')
		{
	       $cols=($this->page->get_setting('prod_cols')==2)?1:2;
			 $this->page->db_insert_settings(array('prod_cols'=>$cols));
			 echo $this->page->get_setting('prod_cols');
			 exit;
		}
		if($this->page->action_id=='toggle_hidden')
		{
	      $hideHidden=$this->page->get_setting('hideHidden')=='1'?'0':'1';
			$this->page->db_insert_settings(array('hideHidden'=>$hideHidden));
			echo $this->page->get_setting('hideHidden');
			exit;
		}

		$this->mng_products();
	}

	protected function mng_products()
	{
		global $db;

		$sel_cat=0;$sel_prod='';$sel_related=0;$pid='';$pid_data='';
		$adding_new=$this->page->action_id=='new_product';
		$status=isset($_REQUEST['status'])?$_REQUEST['status']:'';
		$cols=$this->page->get_setting('prod_cols');
		$hideHidden=$this->page->get_setting('hideHidden');

		$count_itmes=$this->page->limit_own_post>0&&$this->page->g_use_pby?
			$db->db_count($this->page->pg_settings['g_data'].'_data','posted_by='.intval($this->page->user->getId())):0;
		$add_more_items=$this->page->limit_own_post>=($count_itmes+1)||$this->page->limit_own_post==0||$this->page->limit_own_post==1000000;

		if($status=='save_product')
		{
			$field_names=$db->db_fieldnames($this->page->pg_settings['g_data'].'_data');
			if(isset($_REQUEST['unpublished_date'])&&!isset($_REQUEST['unpublish_on']))
				unset($_REQUEST['unpublished_date']);
			$data=array();
			foreach($field_names as $k=>$v)
			{
				if(isset($_REQUEST[$v]))
				{
					$itype=$this->page->pg_settings['g_fields_array'][$v]['itype'];
					$type=$this->page->pg_settings['g_fields_array'][$v]['type'];
					if($itype=='datetime' || $this->page->pg_settings['g_fields_array'][$v]['itype']=='date')
						$data[$v]=date('Y-m-d H:i:s',Date::pareseInputDate($v,24,$this->page->month_name));
					elseif($itype=='editor')
						$data[$v]=Editor::replaceClasses($_REQUEST[$v]);
					elseif($itype=='price' || $type=='decimal(15,4)')
					{
						if($this->page->pg_settings['g_decimal_sign']==',')
							$data[$v]=number_format($this->page->get_output_price($_REQUEST[$v]),$this->page->pg_settings['g_price_decimals'],'.','');
						elseif(strpos($_REQUEST[$v],'.')!==false)
							$data[$v]=str_replace(',','',$_REQUEST[$v]);
						else
							$data[$v]=str_replace(',','.',$_REQUEST[$v]);
					}
					elseif(strpos($type,'int')!==false)
						$data[$v]=intval($_REQUEST[$v]);
					else
						$data[$v]=$_REQUEST[$v];
				}
			}

			$pid=(isset($data['pid']) && $data['pid']=='=')?0:intval($data['pid']);
			$cid=intval($data['cid']);
			if(!$this->page->categoriesModule->is_category_disabled($cid) || ($pid!=0&&$_REQUEST['old_cid']==$cid))
			{
				$parentid=-1;
				$cname=$this->page->categoriesModule->get_category_name($cid,$parentid);
				$data['category']=$cname;
				if(!isset($data['subcategory']))$data['subcategory']='';
				if($this->page->shop)
				{
					if(!isset($data['vat']))
						$data['vat']=0;
					if(!isset($data['price']))
						$data['price']=0;
					if(!isset($data['stock']))
						$data['stock']=0;
					if(!isset($data['shipping']))
						$data['shipping']=0;
				}
				$data['publish']=isset($data['publish'])?1:0;
				if(in_array('updated',$field_names))
					$data['updated']=date('Y-m-d H:i:s');

				if($pid==0&&$add_more_items) //adding new
				{
					$pid=$this->page->get_nextid();

					if(isset($data['code']))
					{
						$digits=preg_match('/[0-9]+$/',$data['code'],$matches);
						if($digits)
						{
							$next_value=$matches[0]+1;
							$diff=strlen($matches[0])-strlen($next_value);
							if($diff>0)
							{
								$zeros='';
								for($i=0;$i<$diff;$i++)
									$zeros.='0';
								$next_value=$zeros.$next_value;
							}
						}
						$this->page->db_insert_settings(array('next_numeric_code'=>($digits?(str_replace($matches[0],$next_value,$data['code'])):'')));
					}
					$data['pid']=$pid;
					if($this->page->g_use_pby)
						 $data['posted_by']=intval($this->page->user->getId());
					$db->query_insert($this->page->pg_settings['g_data'].'_data',$data);
				}
				elseif($pid!=0)
				{
					$db->query_update($this->page->pg_settings['g_data'].'_data',$data,'pid='.$pid);
					if($_REQUEST['old_cid']!=$cid) //category change, updated related
						 $db->query('
							UPDATE '.$db->pre.$this->page->g_datapre.'data
							SET cid = '.$cid.'
							WHERE rel = '.$pid);
				}

				$tc=new tags_cloud($this->page);
				$tc->clean_tagcloud();

				History::add($this->page->pg_id,$this->page->pg_settings['g_data'].'_data',$pid,$this->page->user->getUname(),$data);

				$sel_cat=$cid;
				$sel_prod=$pid;
				if(isset($data['rel']))
				{
					$sel_prod=$data['rel'];
					$sel_related=$pid;
				}

				$this->page->featuresModule->save_options($pid,$cid);
				if($data['publish']==1)
					$this->page->reindex_search($pid);
				$this->page->categoriesModule->build_categories_list(true,$this->page->g_linkedid,1);
				Linker::checkReturnURL();
			}
		}
		else
		{
			$sel_cat=(isset($_REQUEST['cat_select']))?intval($_REQUEST['cat_select']):'';
			if($status=='duplicate'&&$add_more_items)
				$sel_prod=$this->page->duplicate_product($_REQUEST['pid']);
			else
				$sel_prod=(isset($_REQUEST['prod_select']))?intval($_REQUEST['prod_select']):'';
			$sel_related=(isset($_REQUEST['rel_prod_select']))?intval($_REQUEST['rel_prod_select']):0;
		}

		$result=$this->f->navtop;

		$products_exist=false;
		$cid=0;
		$xcid='';

		foreach($this->page->categoriesModule->category_array as $k=>$v)
			if($xcid=='')
				$xcid=$v['id'];

		$cid=intval(($sel_cat=='')?$xcid:$sel_cat);

		$quick_product=$this->page->all_settings['quickproduct'];
		if($adding_new && $quick_product) {
		  //Quick Product functionality
		  $this->page->page_css.= '
			  .hide{display:none;} 
			  .quick_product_txt textarea{width:500px; height:200px;margin-top: 5px;} 
			  .quick_product_txt input{display:block}  
			  .quick_pr{right: 15px;position: relative;float: right;}
			  .quick_box{float:right}';
		  $this->page->page_scripts .= '
			var forbidden_types = ["hidden", "button", "submit"];
			function show_quick_textarea(elem) { 
			  if(elem.checked) {
				$(".quick_product_txt").removeClass("hide");
				textarea = $(".quick_product_txt textarea");
				if(textarea.val()=="") {
				  var frm2 = document.getElementById("frm2");
				  var str_elemets = "";
				  for (var i = 0; i < frm2.elements.length; i++) {
					var name_attr = frm2.elements[i].getAttribute("name");
					var type_attr = frm2.elements[i].getAttribute("type") || "";
					if(name_attr && $.inArray(type_attr, forbidden_types) == -1) {
					  str_elemets += name_attr+"=\n";
					}
				  }
				  if(str_elemets) {
					textarea.html(str_elemets);
				  }
				}
			  }
			  else {
				$(".quick_product_txt").addClass("hide");
			  }
			}
			var oldVal_quick = "";
			$(document).ready(function() {
			  $(".quick_product_txt textarea").on("change keyup paste", function() {
				setFields(this);
			  });
			});
			function setFields(elem) {
			  var currentVal = $(elem).val();
			  if(currentVal == oldVal_quick) {
				return true; //check to prevent multiple simultaneous triggers
			  }
			  oldVal_quick = currentVal;
			  if(currentVal) {
				var lines = currentVal.split("\n");
				if(lines.length) {
				  $.each(lines, function(){
					if(this!="") {
					  var i = this.indexOf("=");
					  var field_value = [this.slice(0,i), this.slice(i+1)];
					  if(field_value.length == 2) {
						var name = field_value[0].trim().toLowerCase();
						var value = field_value[1].trim();
						var field = $("*[name=\'"+name+"\']");
						if(name!="" && value!="" && field.length) {
						  var field_type = field.attr("type") || "";
						  if($.inArray(field_type, forbidden_types) == -1) {
							if((field_type=="checkbox" || field_type=="radio")) {
							  if (value == "1" && !field.is(":checked")) {
								field.trigger("click");
							  }else if(value=="0" && field.is(":checked") ) {
								field.trigger("click");
							  }
							}
							else if(field.hasClass("mceEditor")) {
							  if(typeof tinyMCE != "undefined") {
								field_id = field.attr("id");
								tinyMCE.get(field_id).setContent(value);
							  }else if (typeof window["oEdit1"+name] != "undefined") {
								window["oEdit1"+name].putHTML(value);
							  }
							}
							else if(field[0].tagName.toLowerCase()=="select"){
							  $.each(field[0].options, function(){
								if(this.innerHTML == value)
								  field.val($(this).val());
							  });
							}
							else {
							  field.val(value);
							}
						  }
						}
					  }
					}
				  });
				}
			  }
			}';
		}

		$result.='<form name="frm" method="post" action="'.$this->page->g_abs_path.$this->page->pg_name.'?action=products">';
		if(!$adding_new)
			$result.=$this->page->categoriesModule->build_category_combo($this->page->lang_l('all categories'),'cat_select',$cid,' onchange="document.frm.prod_select.selectedIndex=-1;submit();"','',true,1,'id');

		if($adding_new)
			$products_exist=false;
		else
		{
		  $rawdata=$db->fetch_all_array('
				SELECT name,pid'.($this->page->g_use_rel?',rel':'').'
				FROM '.$db->pre.$this->page->g_datapre.'data
				WHERE cid='.$cid.'
				ORDER BY name');
		  $products_exist=count($rawdata)!=0;
		}

		$data=array();
		$data_rel=array($this->page->lang_l('variations'));

		if($products_exist)
		{
			$pid=($sel_prod=='')?0:$sel_prod;
			if($pid==0)
			{
				if(!$this->page->g_use_rel)
					$pid=$rawdata[0]['pid'];
				else
					foreach($rawdata as $k=>$v)
						if($this->page->g_use_rel && $v['rel']==0)
						{
							$pid=$v['pid'];
							break;
						}
			}
			foreach($rawdata as $k=>$v)
			{
				if($this->page->g_use_rel && $v['rel']>0)
				{
					if($pid==$v['rel'])
					$data_rel[$v['pid']]=$v['name'];
				}
				else
					$data[$v['pid']]=$v['name'];
			}
		}
		else
			$data['0']=$this->page->lang_l('empty_category');

		if(!$adding_new)
		  $result.=' '.Builder::buildSelect('prod_select',$data,$sel_prod,'','key',' onchange="submit();"','');

		if($products_exist)
			 $data=$db->query_first('
					 SELECT *
					 FROM '.$db->pre.$this->page->g_datapre.'data
					 WHERE pid='.($sel_related>0?$sel_related:$pid));
		else
		{
			$fields=$db->db_fieldnames($this->page->pg_settings['g_data'].'_data');
			$data=array();
			foreach($fields as $k=>$v)
				$data[$v]='';
			$data['cid']=$cid;
			$data['publish']='1';
			if(isset($data['code'])&&$this->page->all_settings['next_numeric_code']!='')
				$data['code']=$this->page->all_settings['next_numeric_code'];
		}

		$editable=!$this->page->g_use_pby || !$this->page->edit_own_posts_only || $data['posted_by']==$this->page->user->getId();
		$enable_dis_save=($editable || $adding_new) && ($add_more_items || $editable)?'':'disabled';

		$result.='
				</form>
				  	<input class="editable" style="float:right" name="save_entry" onclick="$(\'#frm2\').submit();" type="button"'.$enable_dis_save.' value="'.$this->page->lang_l('save').'">'
					.($adding_new && $quick_product?
					  '<div class="quick_pr">
						  <div class="quick_box" title="Quick assign product fields with predefined values">
							<input type="checkbox" onclick="show_quick_textarea(this)"/>
							<span class="rvts8 a_editcaption">'.$this->page->lang_l('Quick product').'</span>
						  </div>
						<div class="quick_product_txt hide">
						  <textarea></textarea>
						</div>
					  </div>':'')
					.'<div style="clear:both"></div>
				</div></div>
				<span id="col_pick"></span>
				<script type="text/javascript">
					 var e=document.getElementById("col_pick"),col_pick=rzGetBg(e);
				</script>';

		$table_data=array();$dtyn=array('1'=>'yes','0'=>'no');
		$iw=($cols==1?$this->page->inp_width:'width:70%;');
		$iwa=$iws=($cols==1?$this->page->inp_width:'width:85%;');
		$iwis='width:'.($cols==1?'100px':'80px').';';

		$pid_data='
			 <div style="margin-top:3px;">
				<span class="rvts8 a_editnotice">
					 <b>ID</b> ['.$data['pid'].']'.Builder::buildInput('pid',$data['pid'],'','','hidden','').'      '.
					 $this->page->field_displayname('created').': '.$this->page->format_dateSql($data['created'],'long','auto').'      '.
					 $this->page->field_displayname('updated').': '.$this->page->format_dateSql($data['updated'],'long','auto').'
				</span>
			</div>';

		$variations='';
		if(!$adding_new && $this->page->g_use_rel)
		{
			$variations='   '.Builder::buildSelect('rel_prod_select',$data_rel,$sel_related,'','key',' onchange="variation('.$pid.','.$cid.',this.value);"','').'
				<input type="button" onclick="newpr('.$pid.');" value="'.$this->page->lang_l('add variation').'">';

	 		if($data['pid']>0 && $sel_related>0)
		  	  $variations.=' <input class="editable" type="button" class="delete_btn"'.($editable?'':' disabled').' value="'.$this->page->lang_l('delete').'" onclick="delrec(\'?action=products&amp;do=delete&amp;pid='.$data['pid'].'\');">';
		}

		$table_data[]=array(false,
				$this->page->field_displayname('name'),'',
				Builder::buildInput('name',
					$data['name'],
					$iw,
					'',
					'text',
					($this->page->use_friendly && $this->page->all_settings['translit']?' onkeyup="translitTo($(this).val(),\'friendlyurl\')" required':'required')
				).$pid_data,'',
				Builder::buildCheckbox('publish',$data['publish']=='1',$this->page->field_displayname('publish')).
				'<span class="col_toggler ui_icon fa fa-'.($cols==1?'stop':'pause').'" style="margin-right:18px;"></span>
				<span class="hide_toggler ui_icon fa fa-toggle-'.($hideHidden?'off':'on').'"></span>'.$variations);

		$table_data[]=array('ver',$this->page->lang_l('category'),'',
			$this->page->categoriesModule->build_category_combo('','cid',$data['cid'],'',($sel_related>0?' disabled ':'').' style="'.$iws.'"',true,1,'id',
			false,false,false,false,false,false,$adding_new,true)
			 .($sel_related>0?'<input type="hidden" name="cid" value="'.$data['cid'].'">':''));

		if($this->page->shop)
		{
			$td=array(true,
						 $this->page->field_displayname('price'),
						 $this->page->field_displayname('price'),
						 Builder::buildInput('price',number_format(floatval($data['price']),
						 $this->page->pg_settings['g_price_decimals'],
						 $this->page->pg_settings['g_decimal_sign'],''),$iwis,'','text',''));

			if(!isset($this->page->pg_settings['g_fields_array']['g_vatfield']['hidden']))
			{
				 $td[]=$this->page->field_displayname($this->page->pg_settings['g_vatfield']);
				 $td[]=Builder::buildInput($this->page->pg_settings['g_vatfield'],number_format(floatval($data[$this->page->pg_settings['g_vatfield']]),
						 $this->page->pg_settings['g_price_decimals'],
						 $this->page->pg_settings['g_decimal_sign'],''),$iwis,'','text','');
			}

			if(!isset($this->page->pg_settings['g_fields_array']['shipping']['hidden']))
			{
				 $td[]=$this->page->field_displayname('shipping');
				 $td[]=Builder::buildInput('shipping',number_format(floatval($data['shipping']),
						 $this->page->pg_settings['g_price_decimals'],
						 $this->page->pg_settings['g_decimal_sign'],''),$iwis,'','text','');
			}

			if(!isset($this->page->pg_settings['g_fields_array']['stock']['hidden']))
			{
				 $td[]=$this->page->field_displayname('stock');
				 $td[]=Builder::buildInput('stock',$data['stock'],$iwis,'','text','');
			}

			if(!isset($this->page->pg_settings['g_fields_array']['salescount']['hidden']))
			{
				 $td[]=$this->page->field_displayname('salescount');
				 $td[]=intval($data['salescount']);
			}

			if(isset($data['sale_price']))
				array_splice($td,4,0,array($this->page->field_displayname('sale_price'),Builder::buildInput('sale_price',number_format(floatval($data['sale_price']),$this->page->pg_settings['g_price_decimals'],$this->page->pg_settings['g_decimal_sign'],''),$iwis,'','text','')));
			$table_data[]=$td;
		}

		$ignore=array('name','cid','subcategory','visits_count',
						  'price',$this->page->pg_settings['g_vatfield'],$this->page->pg_settings['g_vatfield'],
						  'stock','shipping','created','updated','publish');
		foreach($this->page->optionsModule->options as $v)
				$ignore[]=$v;

		if(!isset($this->page->pg_settings['g_fields_array']['option1']['hidden']))
		{
		  $h=count($this->page->optionsModule->options)*42;
			$h=max(array(128,$h));
			$options=array(array('ver',$h),'');

		  foreach($this->page->optionsModule->options as $opt)
		  {
				$options[]=$this->page->field_displayname($opt);
				$options[]=Builder::buildInput($opt,$data[$opt],$iw,'','text','id="'.$opt.'"','','',$opt,'','',' options');
			}
			$table_data[]=$options;
			$this->page->page_dependencies[]='js/jscolor.js';
			$this->page->page_scripts.='
			$(document).ready(function(){
				var first_isLabel="";
				window.first_isLabel='.($this->page->get_setting('op_inc_1st')?'1':'0').';
				$(".options").each(function(){
					$("<a class=\'o_edit_icon\' id=\'o_edit_"+$(this).prop("id")+"\' onclick=\'optionBuilder($(this),true)\'><i class=\'fa fa-pencil-square-o\'></i></a>")
					.insertAfter($(this))
				}).change(function(){
					optionBuilder($(this).parent().find(".o_edit_icon"),false,true)
				});
			});';
		}

		$editor_js='';
		$end=($this->page->g_use_rel && $data['rel']>0)?'<input type="hidden" name="rel" value="'.$data['rel'].'">':'';

		$published_row=$k_tran_published='';
		if(array_key_exists('published_date',$data)&&array_key_exists('unpublished_date',$data)&&
		array_search('published_date',array_keys($data))>array_search('unpublished_date',array_keys($data))){
			$temp=$data['published_date'];
			unset($data['published_date']);
			$new=array();
			foreach ($data as $k=>$value) {
				if ($k==='unpublished_date')
					$new['published_date']=$temp;
				$new[$k]=$value;
			}
			$data=$new;
		}

		foreach($data as $k=>$v)
		{
			if(isset($this->page->pg_settings['g_fields_array'][$k]) && array_search($k,$ignore)===false)
			{
				$k_tran=$this->page->field_displayname($k);

				$itype=$this->page->pg_settings['g_fields_array'][$k]['itype'];
				$type=$this->page->pg_settings['g_fields_array'][$k]['type'];
				$idisplay=isset($this->page->pg_settings['g_fields_array'][$k]['hidden'])?'hidden':true;
				if($itype=='hidden' || $itype=='category' || $itype=='dbid' || $itype=='pid' ||
					$itype=='cid' || $itype=='userinput');
				elseif($itype=='image')
				{
					$ima='<br><img class="product_image" id="ima_'.$k.'" src="'.$v.'" alt="" style="'.(($v=='')?'display:none;':'').'height:60px;padding-top:3px;">';
					$table_data[]=array(array($idisplay,128),$k_tran,'',Builder::buildInput($k,$v,$iw,'','text',' id="'.$k.'" onchange="fixima(this.value,\''.$k.'\');"').' <input class="input1" type="button" name="btnAsset" onclick="openAsset(\''.$k.'\')" value="'.$this->page->lang_l('browse').'"/>'.$ima);
				}
				elseif($itype=='mp3' || $itype=='download' || $itype=='file')
				{
					$player='';
					if(strpos(strtolower($v),'.mp3')!==false)
					{
						$av_object=new audio_video($this->page);
						$player='<div class="player">%html5player[506,30]%</div>';
						$av_object->parse_html5_audiovideo($player,'.mp3',$v);
					}
					$cap=($itype=='download')?'<br><p class="rvts12">'.$this->page->lang_l('download_field_notice').'</p>':'';
					$table_data[]=array(array($idisplay,128),$k_tran,'',Builder::buildInput($k,$v,$iw,'','text',' id="'.$k.'"').' <input class="input1" type="button" name="btnAsset" onclick="openAsset(\''.$k.'\')" value="'.$this->page->lang_l('browse').'"/>'.$cap.$player);
				}
				elseif($itype=='editor')
				{
					$editor_parsed=$this->page->innova_def;
					Editor::addPolldropbox_InnovaEditor($editor_parsed,$this->page->innova_js);
					Editor::addSlideshow_Plugin_Editor($editor_parsed,$this->page->innova_js,$this->page->rel_path,$this->page->pg_settings['ed_lang'],$this->page->lang_l('slideshow'),$this->page->pg_id);
					Editor::addhtml5Player_Plugin_Editor($editor_parsed,$this->page->innova_js,$this->page->rel_path,$this->page->pg_settings['ed_lang'],$this->page->lang_l('html5player'),$this->page->pg_id);
					$editor_parsed=str_replace(array('oEdit1','htmlarea'),array('oEdit1'.$k,'txtContent'.$k),$editor_parsed);
					$v=Editor::replaceClassesEdit($v);
					$this->page->innova_on_output=true;
					$this->page->innova_js=Editor::addGoogleFontsToInnova($v,$this->page->innova_js);
					if($idisplay!=='hidden')
						$table_data[]=array(false,$k_tran,'','<textarea class="mceEditor" id="txtContent'.$k.'" name="'.$k.'" style="width:100%" rows="4" cols="30">'.$v.'</textarea>'.$editor_parsed);
               else
                  $end.=$editor_parsed;
				}
				elseif($itype=='area')
				{
					$exc='<textarea class="input1" name="'.$k.'" style="'.$iwa.'height:90px" id="'.$k.'">'.$v.'</textarea><br>';
					$table_data[]=array(array($idisplay,128),$k_tran,'',$exc);
				}
				elseif($itype=='bool' || $itype=='checkbox')
				{
					$xs=Builder::buildSelect($k,$dtyn,$v,'','key','');
					$table_data[]=array(array($idisplay,64),$k_tran,'',$xs);
				}
				elseif($itype=='price' || $type=='decimal(15,4)')
				{
					if($k!=='sale_price')
						 $table_data[]=array(array($idisplay,64),$k_tran,'',
							  Builder::buildInput($k,number_format(floatval($v),
										 $this->page->pg_settings['g_price_decimals'],
										 $this->page->pg_settings['g_decimal_sign'],''),$iw,'','text',''));
				}
				elseif($itype=='rel')
				{

				}
				elseif($itype=='datetime')
				{
					$is_nulldate=$v=='0000-00-00 00:00:00'||$v=='';
					if(($k=='published_date'||$k=='unpublished_date')&&$is_nulldate){
						if($k=='published_date'){
							$date_modify=strtotime($data['updated'])>strtotime($data['created'])?$data['updated']:$data['created'];
							$dt=$date_modify==''?time():strtotime($date_modify);
						}
						else
							$dt=strtotime(date("Y-m-d H:i:s").' + 7 day');
					}else
						$dt=$is_nulldate?time():strtotime($v);
					$cd=Builder::dateTimeInput($k,$dt,'24',$this->page->month_name);
					if($k=='unpublished_date'){
						$cd='<input type="checkbox" name="unpublish_on" value="true" onclick="if($(\'.date_unpub\').is(\':visible\'))$(\'.date_unpub\').hide();else $(\'.date_unpub\').css(\'\display\',\'inline-block\');" '.(!$is_nulldate?'checked="checked"':'').'  style="height: 27px;" />'
						.'<span class="rvts8">'.$this->page->lang_l('unpublish').'&nbsp;</span>'
						.'<div class="date_unpub" style="display:'.(!$is_nulldate?'inline-block':'none').'">'.$cd.'</div>';
					}

					if($k=='published_date'){
						$published_row=$cd;
						$k_tran_published=$k_tran;
					}else
						$table_data[]=array(array($idisplay,64),($k=='unpublished_date'?$k_tran_published.' / ':'').$k_tran,'',($k=='unpublished_date'?$published_row.$cd:$cd));

					$this->dp_array[]=$k;
				}
				elseif($itype=='date')
				{
					$dt=($v=='0000-00-00 00:00:00' || $v=='')?time():strtotime($v);
					$dateValue=Date::dp($this->page->month_name,$dt);
					$cd='<input class="input1 '.$k.'" name="'.$k.'" type="text" readonly="readonly" value="'.$dateValue.'">';

					$table_data[]=array(array($idisplay,64),$k_tran,'',$cd);
					$this->dp_array[]=$k;
				}
				else
					$table_data[]=array(array($idisplay,64),$k_tran,'',Builder::buildInput($k,$v,$iw,'','text','id="'.$k.'"','','',$k));
			}
		}

		$end.='<br><input class="editable" name="save_entry" type="submit"'.$enable_dis_save.' value="'.$this->page->lang_l('save').'">';
	 	$sort=$this->page->get_setting('sort'.$this->page->user->getId());
		if($this->page->user->getId()!='-1' && $sort=='')
			$sort=$this->page->get_setting('sort-1');
		$prepend='<form name="frm2" id="frm2" method="post" action="'.$this->page->g_abs_path.$this->page->pg_name.'?action=products">'.
				  Builder::buildInput('status','save_product','','','hidden','').
				  Builder::buildInput('old_cid',$cid,'','','hidden','').
				  (Linker::checkReturnURL(true)?'<input type="hidden" name="r" value="'.Linker::checkReturnURL(true).'">':'');

		if($this->page->get_setting('features'))
		{
		  $cnt=0;
		  $options=$this->page->featuresModule->options_list($pid,$cid,0,$iw,$cnt);
		  $options='<div class="xfeatures">'.$options.'</div>';
		  $table_data[]=array(array($idisplay,ceil((($cnt*50)/128))*128),$this->page->lang_l('features'),'',$options);
		}

		$result.=Builder::addEntryTable($table_data,$end,'','',true,$sort,$this->page->pg_name,$prepend,'</form>',$cols,$hideHidden)
		  .'</div>';
		$result=str_replace('%BACKGROUND%"','background:#"+col_pick',$result);

		$this->page->page_scripts.="
		$(document).ready(function(){
		  $('#cid').change(function(){
				$.get('?action=products&do=category_features&pid=".$pid."&cid='+ $('#cid').val(),function(data){
					 $('.xfeatures').html(data);
					 $('.xfeatures .ui_shandle_ic3').click(function(){
						  getOtherValues(this);});
					 });
		  });
		});
		function variation(id,cid,idr)
		{
		  var loc='?action=products&cat_select='+cid+'&prod_select=' + id;
		  if(idr>0)
				loc=loc+ '&rel_prod_select='+idr;
		  window.location=loc;
		}
		function newpr(rel){
		$('.editable').prop('disabled',false);
		$('#a_caption').html('".$this->page->lang_l('new variation')."');
		if(rel>0)
		{
		  $('#frm2').find('input[name=pid]').val('');
		  $('#frm2').append('<input type=\"hidden\" name=\"rel\" value=\"'+rel+'\">');
		  $('#cid').parents('li').hide();
		  var ov=$('#frm2').find('input[name=name]').val();
		  $('#frm2').find('input[name=name]').val(ov  + ' (".$this->page->lang_l('variation').")');
		}
		$('.delete_btn').hide();
		}
		function delrec(loc){
		  conf=confirm('".$this->page->lang_l('del_product_msg')."');
		  if(conf)window.location=loc;
		};
	 	";
		$this->page->page_scripts.=$editor_js;
		$this->page->setState('mng_products',array(&$result));
		$this->output($result);
	}
}

class manage_categories_screen extends lister_admin_screens
{

	public function handle_screen()
	{
		$do=isset($_REQUEST['do'])?$_REQUEST['do']:'';
		$this->mng_categories($do);
	}

	protected function mng_categories($do)
	{
		$output='';
		$err_on_submit='';
		$drag_mode=isset($_REQUEST['dragmode']) && $this->page->user->isAdminOnPage($this->page->pg_id);

		$adding=($do=='add');
		$filter=isset($_REQUEST['q'])?addslashes($_REQUEST['q']):'';
		$cap_arrays=array('',
			 'category'=>array($this->page->pg_name.'?action=categories','none',$this->page->lang_l('category'),'<form id="ct_form" method="post" action="?action=categories">'.Filter::build('q',$filter,'$(\'#ct_form\').submit()').'</form>'),
				  );
		if(!$drag_mode) $cap_arrays[]=$this->page->lang_l('products');

		if($do=='delete')
			 $this->page->categoriesModule->delete_category($output);
		elseif($do=='edit')
			 $this->page->categoriesModule->edit_category();
		elseif($do=='add')
			 $this->page->categoriesModule->add_category($output);
		elseif($do=='publish')
			 $this->page->categoriesModule->publish_category(true);
		elseif($do=='unpublish')
			 $this->page->categoriesModule->publish_category(false);
		elseif($do=='duplicate')
			 $this->page->categoriesModule->duplicate_category(false);

		$ima_path=($adding && isset($_POST['image1'])?Formatter::stripTags($_POST['image1']):'');
		$logged_as_admin=$this->page->user->isEZGAdminLogged();

		if(!$drag_mode)
		{
			$nav=$this->page->categoriesModule->get_admin_category_nav();
			$nav.=$this->page->categoriesModule->get_admin_category_entry(
					array('id'=>'',
							'name'=>($adding && isset($_POST['cname'])?Formatter::stripTags($_POST['cname']):''),
							'color'=>isset($_POST['ccolor']) && $_POST['ccolor']!="#"?$_POST['ccolor']:'#330033',
							'description'=>'',
							'pid'=>-1,
							'viewid'=>'',
							'image1'=>$ima_path,
							'description2'=>'',
							'disable_add_itmes'=>isset($_POST['disable_add_itmes'])?intval($_POST['disable_add_itmes']):0),
					$logged_as_admin,'',$err_on_submit);
			$this->page->innova_on_output=true;
		}

		$total_count=$this->page->categoriesModule->get_categoriesCount();
		$table_data=array();
		$table_data=$this->page->categoriesModule->get_admin_category_list($logged_as_admin,$drag_mode,'',$err_on_submit,$total_count);

		if($drag_mode)
		{
			$end='<input type="button" onclick="ca_order();" value="'.$this->page->lang_l('save').'"> '
			.'<input type="button" onclick="document.location=\''.$this->page->script_path.'?action=categories\'" value="'.$this->page->lang_l('cancel').'">';
			$output.=Builder::adminDraggableSection($cap_arrays,$table_data,$end,'',$this->page->script_path,'',true);
		}
		else
		{
			$nav.=Navigation::pageCA($total_count,$this->page->g_abs_path.$this->page->pg_name."?action=categories",0,$this->page->c_page);
			$output.=Builder::adminTable($nav,$cap_arrays,$table_data,'','','');
		}

		$this->page->page_scripts.='$(document).ready(function(){assign_edits();});';
		$output=Builder::appendScript($this->page->innova_js,$output);
		$this->output($output);
	}
}

$settings=array();
$settings['template_fname']=$lister_template_fname;
$settings['template_path']=$lister_template;
$settings['lang_l']=$lang_l;
$settings['g_excmode']=$g_excmode;
$settings['g_ssl_checkout']=$g_ssl_checkout;
$settings['g_tax_handling']=$g_tax_handling;
$settings['g_vatfield']=$g_vatfield;
$settings['g_pricefield']=$g_pricefield;
$settings['rtl']=$g_rtl;
$settings['g_fields_array']=$g_fields_array;
$settings['g_orderby']=$g_orderby;
$settings['g_orderbydesc']=$g_orderbydesc;
$settings['g_orderby2']=$g_orderby2;
$settings['g_orderbydesc2']=$g_orderbydesc2;
$settings['g_data']=$g_data;
$settings['g_limitby']=$g_limitby;
$settings['g_cid']=$g_cid;
$settings['g_did']=$g_did;
$settings['g_use_abs_path']=$g_use_abs_path;
$settings['g_currency']=$g_currency;
$settings['g_npfix']=$g_npfix;
$settings['g_fron_hor']=$g_fron_hor;
$settings['g_thousands_sep']=$g_thousands_sep;
$settings['g_decimal_sign']=$g_decimal_sign;
$settings['mobile_detect_mode']=$g_mobile_detect_mode;
$settings['g_coupon_onlycheck']=$g_coupon_onlycheck;

$settings['g_ship_settings']=$g_ship_settings;
$settings['g_ship_vat']=$g_ship_vat;
$settings['g_ship_type']=$g_ship_type;
$settings['g_ship_amount']=$g_ship_amount;
$settings['g_ship_above_limit']=$g_ship_above_limit;
$settings['g_ship_above_on']=$g_ship_above_on;
$settings['g_realname']=$g_realname;
$settings['g_listcols']=$g_listcols;
$settings['g_catcols']=$g_catcols;
$settings['g_catcolsauto']=isset($g_catcolsauto)?$g_catcolsauto:0;
$settings['g_catpgmax']=$g_catpgmax;
$settings['g_check_country']=isset($g_check_country)?substr($g_check_country,0,2):'US';
$settings['g_check_shipping_list']='';
$settings['g_checkout_str']=$g_checkout_str;
$settings['g_price_decimals']=$g_price_decimals;
$settings['g_check_name']=$g_check_name;
$settings['g_send_to']=$g_send_to;
$settings['from_email']=$from_email;
$settings['g_mail_fields']=$g_mail_fields;
$settings['g_check_email']=$g_check_email;
$settings['set_bankwire_email']=$set_bankwire_email;
$settings['re_fields']=$re_fields;
$settings['re_upfields']=$re_upfields;
$settings['field_labels']=$field_labels;
$settings['g_callback_mail']=$g_callback_mail;
$settings['send_admin_notification']=$send_admin_notification;
$settings['send_callback_notification']=$send_callback_notification;
$settings['g_checkout_callback_on']=$g_checkout_callback_on;
$settings['g_downloadexpire']=$g_downloadexpire;
$settings['max_items_in_rss']=$g_max_items_in_rss;
$settings['rss_image_width']=$rss_image_width;
$settings['g_callback_str']=$g_callback_str;
$settings['g_shop_name']=$g_shop_name;
$settings['inmenu']=$inmenu;
$settings['inmenu_sub']=isset($inmenu_sub)?$inmenu_sub:false;
$settings['lang_u']=$lang_u;
$settings['lang_uc']=$lang_uc;
$settings['rss_settings']=$rss_settings;
$settings['g_cback_notif_subject']=$g_cback_notif_subject;
$settings['g_cback_notif']=$g_cback_notif;
$settings['order_notification_subject']=$order_notification_subject;
$settings['order_notification_body']=$order_notification_body;
$settings['notification_subject']=$notification_subject;
$settings['notification_body']=$notification_body;
$settings['g_symlink_used']=$g_symlink_used;
$settings['views']=array();
$settings['page_encoding']=$g_encoding;
$settings['ed_lang']=$g_elang;
$settings['tiny_lang']=isset($tiny_lang)?$tiny_lang:'en';
$settings['ed_bg']=$ed_bg;
$settings['skip_mainpage']=isset($g_skip_mainpage)?$g_skip_mainpage:0;

$settings['enable_comments']=isset($enable_comments)?$enable_comments:false;
$settings['forbid_urls']=isset($forbid_urls)?$forbid_urls:false;
$settings['comments_require_approval']=$comments_require_approval;
$settings['rating']=isset($rating)?$rating:false;
$settings['comments_mustbelogged']=isset($comments_mustbelogged)?$comments_mustbelogged:false;
$settings['comments_hidenotlogged']=isset($comments_hidenotlogged)?$comments_hidenotlogged:false;
$settings['comments_require_email']=isset($comments_require_email)?$comments_require_email:false;
$settings['comments_email_enabled']=isset($comments_email_enabled)?$comments_email_enabled:false;
$settings['notify_comment']=isset($notify_comment)?$notify_comment:false;
$settings['admin_emails_arr']=$admin_emails_arr;
$settings['time_format']=$time_format;
$settings['page_type']=$g_shop_on?SHOP_PAGE:CATALOG_PAGE;
$settings['mini_editor']=false;

$lister=new ListerPageClass($g_id,$page_dir,$g_realname,$rel_path,$settings);
$lister->process();

?>

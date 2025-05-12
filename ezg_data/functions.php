<?php
$version="functions v4 - 5.9.48 mysql";
/*
  http://www.ezgenerator.com
  Copyright (c) 2004-2015 Image-line
  portion of code taken from Mobile_detect.php released under MIT License https://github.com/serbanghita/Mobile-Detect/blob/master/LICENSE.txt
*/

/* for easier debug via FTP
 * Add your own IP address in the list if you want to use it too */

define('MAX_RANKING',5);

function _dump($var,$exit=false) {
	if($_SERVER['REMOTE_ADDR']=='92.247.192.173' || $_SERVER['REMOTE_ADDR']=='195.144.71.12' || islocalhost())
	{
		 var_dump($var);
       if($exit) exit;
   }
}

function islocalhost()
{
    return $_SERVER['REMOTE_ADDR']=='localhost'||$_SERVER['REMOTE_ADDR']=='::1'||$_SERVER['REMOTE_ADDR']=='127.0.0.1';
}

function _backtrace() {
	if($_SERVER['REMOTE_ADDR']=='92.247.192.173' || $_SERVER['REMOTE_ADDR']=='195.144.71.12' || islocalhost())
	{
		echo '------ back trace (',microtime(),') --------', PHP_EOL;
		$bt = debug_backtrace();
		foreach($bt as $t) {
			echo $t['file'],' : ',$t['line'], ' - ', $t['function'],' (', $t['class'], ')', PHP_EOL;
		}
	}
}

if($_SERVER['REMOTE_ADDR']=='195.144.71.12'||$_SERVER['REMOTE_ADDR']=='92.247.192.173')
	 error_reporting(E_ALL);
else
	 error_reporting(E_ERROR);

define('SEARCH_TABLES_CNT',2);
define('COUNTER_DETAILS_FIELD_CNT',13);
define('SEARCH_INDEX_CNT',13);

define("NORMAL_PAGE",0);
define("HOME_PAGE",1);

define("BLOG_PAGE",137);
define("BLOG_VIEW",148);

define("PODCAST_PAGE",143);

define("PHOTOBLOG_PAGE",138);
define("PHOTOBLOG_GALLERY_PAGE",139);
define("PHOTOBLOG_VIEW",150);
define("GUESTBOOK_PAGE",144);
define("NEWSLETTER_PAGE",133);
define("CALENDAR_PAGE",136);
define("REQUEST_PAGE",18);
define("OEP_PAGE",20);
define("SURVEY_PAGE",147);

define("SHOP_PAGE",181);
define("SHOP_CATEGORY_PAGE",182);
define("SHOP_PRODUCT_PAGE",183);
define("SHOP_CART_PAGE",184);
define("SHOP_CHECK_PAGE",185);
define("SHOP_RETURN_PAGE",186);
define("SHOP_ERROR_PAGE",187);

define("CATALOG_PAGE",190);
define("CATALOG_CATEGORY_PAGE",191);
define("CATALOG_PRODUCT_PAGE",192);

//user access types
define('VIEW_ACCESS',0);
define('EDIT_ACCESS',1);
define('NO_ACCESS',2);
define('EDIT_OWN_ACCESS',3);
define('ADMIN_OEP_ACCESS',4);
define('ADMIN_ON_PAGE',5);
define('ADMIN_ACCESS',9);

// ErorHandler class constants
define ('ERR_MESSAGE', 'WE ARE SORRY! ERROR OCCURED!');

if(version_compare(PHP_VERSION,'5.4.0','<')&&get_magic_quotes_runtime()==1)
	set_magic_quotes_runtime(0);

abstract class admin_screens
{
	 abstract protected function screen_output($output);
}

class FuncConfig
{

	private $data=array();

	public function __set($name,$value)
	{
		$this->data[$name]=$value;
	}

	public function &__get($name)
	{
		if(array_key_exists($name,$this->data))
		{
			return $this->data[$name];
		}

		$trace=debug_backtrace();
		trigger_error(
			'Undefined property via __get(): '.$name.
			' in '.$trace[0]['file'].
			' on line '.$trace[0]['line'],E_USER_NOTICE);
		$result = null;
		return $result;
	}

	public function __isset($var)
	{
 		return isset($this->data[$var]);
	}

}

class FuncHolder
{

	protected static $f;
	private $userFlags;

	public function __construct()
	{
		global $f;
		if($f instanceof FuncConfig)
			self::$f=$f;
		else
			die('Settings handler not loaded properly!');
		$this->userFlags=0;
	}

	 protected function hasFlag($flag=0,$onlyFlag=false)
	{
		if($flag===0)
			return $this->userFlags>0;
		if($onlyFlag)
			return $this->userFlags==$flag;
		return (($this->userFlags&$flag)==$flag);
	}

	protected function setFlag($flag,$val)
	{
		if($val)
			$this->userFlags |= $flag;
		else
			$this->userFlags &= ~$flag;
	}
}

//extensions definitions start here
class Unknown extends FuncHolder
{

	public static function xtract($text,$num)
	{
		if(preg_match_all('/\s+/',$text,$junk)<=$num)
			return $text;
		$text=preg_replace_callback('/(<\/?[^>]+\s+[^>]*>)/','Unknown::_abstractProtect',$text);
		$words=0;
		$out=array();
		$stack=array();
		$tok=strtok($text,"\n\t ");
		while($tok!==false and strlen($tok))
		{
			if(preg_match_all('/<(\/?[^\x01>]+)([^>]*)>/',$tok,$matches,PREG_SET_ORDER))
			{
				foreach($matches as $tag)
					self::_recordTag($stack,$tag[1],$tag[2]);
			}
			$out[]=$tok;
			if(!preg_match('/^(<[^>]+>)+$/',$tok))
				++$words;
			if($words==$num)
				break;
			$tok=strtok("\n\t ");
		}
		$result=self::_abstractRestore(implode(' ',$out));
		$stack=array_reverse($stack);
		if($words==$num)
			$result.=' ...';
		foreach($stack as $tag)
			$result.="</$tag>";
		return $result;
	}

	public static function _abstractProtect($match)
	{
		return preg_replace('/\s/',"\x01",$match[0]);
	}

	public static function _abstractRestore($strings)
	{
		return preg_replace('/\x01/',' ',$strings);
	}

	public static function _recordTag(&$stack,$tag,$args)
	{
		if(strlen($args)&&$args[strlen($args)-1]=='/')
			return;
		elseif($tag[0]=='/')
		{
			$tag=substr($tag,1);
			for($i=count($stack)-1; $i>=0; $i--)
			{
				if($stack[$i]==$tag)
				{
					array_splice($stack,$i,1);
					return;
				}
			}
			return;
		}
		elseif(in_array(Formatter::strToLower($tag),array('h1','h2','h3','h4','h5','h6','p','li','ul','ol','div','span','a','strong','b','i','u','em','blockquote','font','h','td','tr','tbody','table')))
			$stack[]=$tag;
	}

	public static function strpos_multi($haystack,$needle_array)
	{
		foreach($needle_array as $v)
			if(strpos($haystack,$v)!==false)
				return true;
		return false;
	}

	public static function defPostPerDay($mon,$year,$all_posts,$date_field_name)  // define posts for each day in a month
	{
		$posts_per_day[]=array();
		for($i=1; $i<=Date::daysInMonth($mon,$year); $i++)
		{
			$st_i_ts=mktime(0,0,0,$mon,$i,$year);
			$end_i_ts=mktime(23,59,59,$mon,$i,$year);
			foreach($all_posts as $v)
			{
				if(strtotime($v[$date_field_name])>=$st_i_ts && strtotime($v[$date_field_name])<=$end_i_ts)
				{
					$posts_per_day[$i]=true;
					break;
				}
			}
		}
		return $posts_per_day;
	}

	public static function isOdd($int)
	{
		return($int&1);
	}

	public static function isSequen($arr)
	{
		return array_keys($arr) === range(0, count($arr) - 1);
	}
}

class Builder extends FuncHolder
{

	public static function multiboxImages($src,$rel_path,$force=false)
	{
		$src=str_replace('target="multibox"','class="multibox"',$src);
		$multibox=$force||strpos($src,'class="multibox')!==false;
		$mbox=$force||strpos($src,'class="mbox')!==false;

		if($multibox||$mbox)
		{
			$box=self::$f->nivo_box?'nivoLightbox':'multibox';
			$sc=self::$f->nivo_box?'nivo-lightbox':'fancybox';

			$mb=$multibox?'
				function getMbCl(el){
					 cl=$(el).attr("class").substring(9);return (cl=="")?"LB":cl;
				};
				$("a.multibox").each(function(){
					 img=$(this).children("img");
					 if(img.length>0) $(img).attr("class",($(this).attr("class")));
					 else {cl= getMbCl(this);$(this).addClass("mbox").attr("rel","noDesc["+cl+"]");}
				});
				$("img.multibox").each(function(){
					 cl= getMbCl(this);
					 fl=$(this).css(\'float\');
					 $(this).parent().addClass(\'mbox\').attr(\'rel\',\'lightbox[\'+cl+\'],noDesc\').css(\'float\',fl);
				});':'';

			if(strpos($src,'function(){$(".mbox").'.$box)!==false)
				$src=str_replace('$(".mbox").'.$box,$mb.'$(".mbox").'.$box,$src);
			else
			{
				$mb=((strpos($src,$sc.'.js')===false)?'
				<link rel="stylesheet" type="text/css" href="'.$rel_path.'extimages/scripts/'.$sc.'.css" media="screen" />
				<script type="text/javascript" src="'.$rel_path.'extimages/scripts/'.$sc.'.js"></script>
				':'').'
				<script type="text/javascript">
					$(document).ready(function(){
						 '.$mb.'
						 $(".mbox").'.$box.'({zicon:true});
					});
				</script>
				';
				$src=str_replace('<!--endscripts-->','<!--endscripts-->'.$mb,$src);
			}
		}

		return $src;
	}

	public static function includeCss($src,$css)
	{
		$ct='<style type="text/css">';
		$cte='</style>';
		if(strpos($css,$ct)!==false)
			$css=trim(Formatter::GFS($css,$ct,$cte));
		if(strpos($src,$ct)!==false)
		{
			$pos=strpos($src,$ct);
			if($pos!==false)
				$src=substr_replace($src,$ct.F_LF.$css,$pos,strlen($ct));
		}
		else
			$src=str_replace('<!--scripts-->',$ct.F_LF.$css.F_LF.$cte.'<!--scripts-->',$src);
		return $src;
	}

	public static function getBrowseDialog($src,$rel_path,$lang='english',$resize_chkbx=true,$plugin_slideshow=false)
	{
		if(strpos(self::$f->editor_js,'%XLANGUAGE%')!=false)
			$lang=isset(self::$f->innova_lang_list[$lang])?self::$f->innova_lang_list[$lang]:self::$f->innova_lang_list['english'];

		$md_dialog='
	var aMW;
	function mDialogShow(url,w,h){
	  aMW=window.open(url,"","width="+w+"px,height="+h);
	  window.onfocus=function(){if(aMW.closed==false) aMW.focus();};
	}
	function openAsset(id){
	  cmdAManager="'.(self::$f->tiny?'mDialogShow':'modalDialogShow').'(\'%sinnovaeditor/assetmanager/assetmanager.php?lang=%s&root=%s&id=\'+id,755,500)";
	 	eval(cmdAManager);

	}'
	.($plugin_slideshow?'':
	'
	function delMe(t)
	{
		$(t).prev().remove();$(t).remove();
	}
	function setAssetValue(val,id){
	  if(!$.isArray(val)) val=val.split();
	  $.each(val,function(i,c) {
	 	  if(id=="slideshow")
	 		  $("#"+id).append($("<input type=hidden name=\'slides[]\' value=\'"+c+"\'><span class=\'slides_wrap\' onclick=\'delMe(this)\'><img src=\'"+c+"\' class=slides style=\'height:60px;padding-top:5px;\'></span>"));
		  else{
				$("#"+id).val(c);$("#ima_"+id).attr("src",c).show();
			}
		  });
	}');

	 $sc=sprintf($md_dialog,$rel_path,$lang,$rel_path).
			  '
				function fixima(val,id){
					  $("#ima_"+id).attr("src",val).css("display",val==""?"none":"block");
				};';
	 if(isset($_SERVER['HTTP_USER_AGENT']))
		$ag=$_SERVER['HTTP_USER_AGENT'];
	 if(!$resize_chkbx)
		$sc=str_replace('assetmanager.php?lang=','assetmanager.php?resize=0&lang=',$sc);
		if((strpos($src,'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">')!==false)&&isset($ag)&&(strpos($ag,'Internet Explorer')!==false||strpos($ag,'MSIE 8')!==false))
		$sc=str_replace('modalDialogShow','mDialogShow',$sc);
		return $sc;
	}

	public static function includeBrowseDialog($src,$rel_path,$lang='english',$resize_chkbx=true)
	{
		$sc=self::getBrowseDialog($src,$rel_path,$lang='english',$resize_chkbx=true);
		$src=self::includeScript($sc,$src);
		return $src;
	}

	public static function includeScript($script,$output,$dependancies=array(),$r_path='')
	{
		if(!empty($script) && strpos($output,$script)===false)
		{
			if(self::$f->xhtml_on)
				$script_enclosed='<script type="text/javascript">'.F_LF.'/* <![CDATA[ */'.F_LF.$script.F_LF.'/* ]]> */'.F_LF.'</script>'.F_LF;
			else
				$script_enclosed='<script type="text/javascript">'.F_LF.$script.F_LF.'</script>'.F_LF;

			$start='<!--endscripts-->';
			if(strpos($output,$start)===false)
				$start='</head>';
			$output=str_replace($start,$script_enclosed.$start,$output);
		}
		if(is_array($dependancies))
		{
			if(!empty($dependancies))
				foreach($dependancies as $v)
				{

					$ext=strpos($v,'http')!==false;
					if($v=='jquery-ui.css')
					{
						if(strpos($output,$v)===false)
							$output=str_replace('<!--scripts-->','<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css">'.F_LF.'<!--scripts-->',$output);
					}
					elseif($v=='jquery-ui.min.js')
					{
						if(strpos($output,$v)===false)
							$output=str_replace('<!--scripts-->','<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>'.F_LF.'<!--scripts-->',$output);
					}
					elseif(strpos($v,'<script')!==false)
					{
						if(strpos($output,$v)===false)
							$output=str_replace('<!--scripts-->',$v.F_LF.'<!--scripts-->',$output);
					}
					elseif(strpos($v,'.css')!==false)
					{
						if(strpos($output,$v)===false)
							$output=str_replace('<!--scripts-->',
								'<link type="text/css" href="'.(strpos($v,'//')===false?$r_path:'').$v.'" rel="stylesheet">'.F_LF.'<!--scripts-->',$output);
					}
					elseif(strpos($v,'.js')!==false)
					{
						if(strpos($output,$v)===false)
							$output=str_replace('<!--scripts-->','<!--scripts-->'.F_LF.'<script type="text/javascript" src="'.($ext?'':$r_path).$v.'"></script>',$output);
					}
					elseif(strpos($output,$v.'.js')===false)
						$output=str_replace('<!--scripts-->','<!--scripts-->'.F_LF.'<script type="text/javascript" src="'.($ext?'':$r_path).$v.'.js"></script>',$output);
				}
		}
		elseif($dependancies!=='') //for hist. reasons, $dependancies can be text instead of array
		  $output=str_replace('<!--scripts-->','<!--scripts-->'.$dependancies,$output);

		return $output;
	}

	public static function includeGFonts($src)
	{
		if(strpos(self::$f->editor_js,'%XLANGUAGE%')!=false)
		{
			$fonts=join("|",self::$f->gfonts);
			$matches=array();
			if(preg_match_all('/'.$fonts.'/',$src,$matches))
			{
				$matches=array_unique($matches[0]);
				$l='<link href="https://fonts.googleapis.com/css?family=';
				if(strpos($src,$l)!==false)
				{
					$oldlink=Formatter::GFSAbi($src,$l,'"');
					$families=Formatter::GFS($oldlink,$l,'"').'|';
					foreach($matches as $v)
						if(strpos($families,$v.'|')===false)
							$families.=$v.'|';
					$src=str_replace($oldlink,$l.$families.'"',$src);
				}
				else
					$src=str_replace('</title>',
								'</title>'.F_LF.$l.implode('|',$matches).'" rel="stylesheet" type="text/css">',
								$src);
			}
		}
		return $src;
	}

	public static function appendScript($script,$page_src)
	{
		return str_replace(array('</HEAD>','</head>'),' '.$script.' </head>',$page_src);
	}

	public static function getDatepicker($field_name,$month_name,$day_name)
	{
		$m_t=$d_sh=array();
		foreach($month_name as $k=> $v)
			$m_t[]="'".$v."'";
		$mon_impl=implode(',',$m_t);
		foreach($day_name as $k=> $v)
			$d_sh[]="'".Formatter::mySubstr($v,0,2,self::$f->uni)."'";
		$day_sh_impl=implode(',',$d_sh);

		$result='$(document).ready(function(){$(".'.$field_name.'").datepicker({showOtherMonths:true,changeYear:true,monthNames:['.$mon_impl.'],dayNamesMin:['.$day_sh_impl.'],dateFormat:\'MM d, yy\'});}); ';
		return $result;
	}

	public static function includeDatepicker($output,$month_name,$day_name,$fields)
	{
		$pickers='';

		if(!is_array($fields))
			$fields=array($fields);

		foreach($fields as $field_name)
			$pickers.=F_LF.self::getDatepicker($field_name,$month_name,$day_name);

		$output=Builder::includeScript($pickers,$output,array('jquery-ui.css','jquery-ui.min.js'),'');
		return $output;
	}

	public static function buildButton($label,$url='javascript:void(0);',$class='',$id='',$rel='')
	{
		$class_p=$class!=''?'class="'.$class.'" ':'';
		$id=$id!=''?'id="'.$id.'" ':'';
		$rel=$rel!=''?'rel="'.$rel.'" ':'';
		$inner	='<a '.$class_p.$id.$rel.'href="'.$url.'">'.$label.'</a>';
		if(self::$f->buttonhtml=='')
			return '<a href="'.$url.'"><button '.$class_p.$id.$rel.'type="button">'.$label.'</button></a>';
		else
		{
			if(strpos(self::$f->buttonhtml,'<a class="')!==false)
				return str_replace(
						array('%BUTTON%','href=""','class="'),
						array($label,$rel.$id.'href="'.$url.'"',$class!=''?'class="'.$class.' ':''),
						self::$f->buttonhtml);
			else
				return str_replace('%BUTTON%',$inner,self::$f->buttonhtml);
		}
	}

	public static function buildRanking($data,$rank_value,$entry_id,$page_id,$state=-1)
	{
		$rr=$state>-1?$state:
			(Cookie::entryIsCookie($entry_id,$page_id,'ranking_')?0:(self::$f->direct_ranking?2:1));
		return '
			<span class="ranking" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
				<span class="ranking_result" rel="'.$entry_id.'" data="'.$rr.'">
					<span itemprop="ratingValue">'.$rank_value.'</span>
				</span>
				<span class="review_count" itemprop="reviewCount" title="'.$rank_value.'/'.MAX_RANKING.'">'.$data['ranking_count'].'</span>
			</span>';
	}

	public static function buildRating($votes,$rank_value,$entry_id)
	{
		$rating=self::buildRanking(array('ranking_count'=>$votes),$rank_value,$entry_id,0,0);
		return $rating;
	}

	public static function buildAdminRanking($ranking_voted,$ranking_total,$ct_color,$label)
	{
		$space=3;
		$w=5;
		$output='';
		if($ranking_voted>0)
		{
			$output='<div style="position:relative;width:200px;height:14px;">';
			if(self::$f->ranking_average)
			{
				$score=($ranking_total/$ranking_voted);
				$r_main=floor($score);
				for($i=0; $i<$r_main; $i++)
					$output.='<div style="position:absolute;width:'.$w.'px;left:'.($i*($w+$space)).'px;bottom:2px;height:10px;background:'.$ct_color.';">&nbsp;</div>';
				$r_reminder=($ranking_total%$ranking_voted);
				if($r_reminder==1)
					$r_reminder=2;
				if($r_reminder!=0)
					$output.='<div style="position:absolute;width:'.ceil($r_reminder/2).'px;left:'.(($r_main)*($w+$space)).'px;bottom:2px;height:10px;background:'.$ct_color.';">&nbsp;</div>';

				$output.='<div class="rank_text" style="position:absolute;width:160px;left:42px;bottom:0px;"><span class="rvts8">'
					.round($score,1).' \\ '.$ranking_voted.' '.$label.'</span></div></div>';
			}
			else
			{
				$score=$ranking_total;
				$r_main=floor($score);
				$output.='<div style="position:absolute;width:'.$r_main.'px;left:25px;bottom:2px;height:10px;background:'.$ct_color.';">&nbsp;</div>';
				$output.='<div class="rank_text" style="position:absolute;left:2px;bottom:0px;"><span class="rvts8">'
					.round($score,1).'</span></div></div>';
			}
		}
		return $output;
	}
	public static function tooltip($url,$class,$title,$text,$imgpath,$link,$im_height='',$im_width='',$more='')
	{
		$hint_id=($text!='')?'hhint':'ihint';
		$style=($im_height!='')?'height:'.$im_height.'px;':'';
		$style.=($im_width!='')?'width:'.$im_width.'px;':'';
		$style=($style!='')?' style=&quot;'.$style.'&quot;':'';
		$text=($text==''&&$imgpath!='')?'&lt;img alt=&quot;&quot; src=&quot;'.$imgpath.'&quot;'.$style.'&gt;':$text;
		$result='<a href="'.$url.'" class="'.$hint_id.($class!=''?' '.$class:'').'" title="'.$title.'::'.$text.'" '.$more.'>'.$link.'</a>';
		return $result;
	}

	public static function getEntryTableRows($tabledata,$script_name='')
	{
		$r='';
		foreach($tabledata as $value)
		{
			$r.='<tr class="'.self::$f->atbg_class.'"><td>';
			if(is_array($value))
			{
				$cnt=count($value);
				if($cnt>2)
				{
					foreach($value as $k=> $v)
					{
						if(!Unknown::isOdd($k))
						{
							$ctrl=isset($value[$k+1])?$value[$k+1]:'';
							$wrapIt=strpos($value[$k],'<div')===false;
							if($wrapIt)
								$r.='<div '.($ctrl==''?'':'style="display:inline;float:left;padding-right:5px;"').'>';
							if($value[$k]!='')
								$r.='<span class="rvts8 a_editcaption">'.$value[$k].'</span><br>';
							$r.=$ctrl;
							if($wrapIt)
								$r.='</div>';
						}
					}
					$v='';
				}
				else
				{
					if($value[0]!='')
						$r.='<span class="rvts8 a_editcaption">'.$value[0].'</span><br>';
					$r.=$value[1];
				}
			}
			else
				$r.=$value;
			$r.='</td></tr>';
		}

		if($script_name!='')
		{
			$act_param=strpos($script_name,'centraladmin.php')!==false?'process':'action';
			$r.='<script type="text/javascript">$(document).ready(function(){'
				.'$(".ui_shandle_ic3").click(function(){
					var pp=$(this).parent(),rel=$(this).attr("rel");
					$.post("'.$script_name.'?'.$act_param.'=fvalues&fid="+rel,function(data){
						ar=data.split("#");
						$(".xsel").remove();
						var s=$("<select />");
						s.addClass("input1 xsel");
						for(v in ar) {
							subar=ar[v].split("<><><>");
							$("<option />",{value:subar[0],text:subar[1]}).appendTo(s);
						};
						s.change(function(){$("input[name="+rel+"]").val($(this).val());});
						s.appendTo(pp);
					});
				});'
				.'});</script>';
		}
		return $r;
	}

	public static function getEntryTableRowsDrag($tabledata,$sort,$script_name,$col=1,$hideHidden=0,$ic_hidden=false, $notdrag_over_row=false)
	{
		$hover_class='ui_shandle_highlight';

		$sort_a=array_keys($tabledata);
		$dis=array();
		if($sort!='')
		{
			$s=explode('-',$sort);
			if(isset($s[1]))
				$dis=explode('|',$s[1]);
			if($s[0]!='')
			{
				$sort_a=explode('|',$s[0]);
				if($sort_a[count($sort_a)-1]=='')
					array_pop($sort_a);
				foreach($tabledata as $k=> $v)
				{
					if(array_search($k,$sort_a)===false)
						$sort_a[]=strval(count($sort_a));
				}
				$temp=array();
				foreach($sort_a as $k=> $v)
					if(isset($tabledata[$v]))
						$temp[]=$tabledata[$v];
				$tabledata=$temp;
			}
		}

		$r='';
		foreach($tabledata as $key=> $row)
		{
			$type=is_array($row[0])?$row[0][0]:$row[0];
			$height=is_array($row[0])?$row[0][1]:64;
			$ihidden=$type==='hidden';
			$draggable=!$ihidden&&$row[0]!==false;
			$ver=$type==='ver';
			$title=$row[1];
			$single=!(count($row)>5);
			$rowid=$sort_a[$key];
			$row_visible=$type==false||(is_array($dis)&&array_search($rowid,$dis)===false);
			$row_css=$col==1?'':($ihidden||!$draggable?'width:100%;float:left;':'width:50%;min-height:'.$height.'px;');
			$r.='<div id="sort_'.$rowid.'" class="sort_row '.($draggable?' dr':'').(!$row_visible?' hidden_row':'').'" style="position:relative;'
				.$row_css.($ihidden?'display:none;':'').'">
				<div class="'.($ihidden||$col==1||!$draggable?'':'sort_row_innner ').self::$f->atbg_class.'" style="clear:left;margin: 0 2px 2px 0;padding:3px;'.(!$draggable?'position:relative;':'').'">';
			if($title!=''){
				$r.='<div class="ui_shandle"><span class="rvts8 a_editcaption">'.$title.'</span>';
				if($ic_hidden === false)
					$r.= ($draggable?'<span class="fa fa-toggle-on ui_shandle_ic1"></span><span class="fa fa-arrows ui_shandle_ic2"></span>':'').'</div>';
				else
					$r.= ($draggable?'<span class="fa fa-arrows ui_shandle_ic2"></span>':'').'</div>';
			}
			$r.='<div class="ui_sdata"'.($row_visible?'':' style="display:none"').'>';

			foreach($row as $k=>$v)
			{
				if($k>1&&!Unknown::isOdd($k))
				{
					if($v=='rowEnd')
						$r.='<div class="clear"></div>';
					else
					{
						$r.='<div class="merged'.($single||$ver?'':' mergedcols').'">';
						if($v!='')
							$r.='<span class="rvts8 a_editcaption">'.$v.'</span><br>';
						if(isset($row[$k+1]))
							$r.=$row[$k+1].'</div>';
					}
				}
			}
			$r.='</div>';
			if(!$single)
				$r.='<div style="clear:left"></div>';
			$r.='</div></div>';
		}

		$sc='
			<script type="text/javascript">
			function getOtherValues(th)
			{
				var th=$(th),pp=$(th).parent(),rel=$(th).attr("rel");
				$.post("'.$script_name.'?action=fvalues&fid="+rel,function(data){
					 d=data="---#"+data;ar=d.split("#");
					 $(".xsel").remove();
					 var s=$("<select />");
					 s.addClass("input1 xsel");
					 for(v in ar) { $("<option />",{value:ar[v],text:ar[v]}).appendTo(s); };
					 s.change(function(){
						  var $val="";
						  if($(this).hasClass("appSel")) {$val=$("input[name="+rel+"]").val()+", ";}
						  if($val==", ") {$val="";}
						  $val+=$(this).val();
						  $("input[name="+rel+"]").val($val);
						  optionBuilder_xsel($(this));
					 });
					 if(pp.find(".colour_palette").length)
						s.insertBefore(".colour_palette");
					 else
						s.appendTo(pp);
				})
			}

			$(document).ready(function(){
			$(".col_toggler").click(function() {
			$.post("?action=toggle_cols",function(){location.reload();});});
			$(".hide_toggler").click(function() {
				$.post("?action=toggle_hidden",function(){location.reload();});
			});
			$(".ui_shandle").hover(function(){$(this).addClass("'.$hover_class.'");},function(){$(this).removeClass("'.$hover_class.'");});
			$(".ui_shandle_ic1").click(function(){
				$(this).parents(".dr").toggleClass("hidden_row");
				$(this).parent().next().toggle();
				var id=($(this).parent().next().is(":visible")?"+":"-")+$(this).parents(".sort_row").attr("id").substr(5);
				$.post("'.$script_name.'",{"toggle":id})});
			$("#sort_table").sortable({handle:".ui_shandle_ic2",placeholder:"ui-state-highlight",';
		if($notdrag_over_row!==false)
			$sc .=  'items: "div.dr",';
		$sc.='
			update:function(){
				$.post("'.$script_name.'",$("#sort_table").sortable("serialize") )
			}});
			$(".ui_shandle_ic3").click(function(){
				getOtherValues(this);
			});});
	 		</script>
		 	<style>
			.hidden_row{'.($hideHidden?'display:none !important;':'').'}';
		 if($col>1)
		  $sc.='
			.hidden_row{min-height:32px !important;width:25% !important;}
			#sort_table .dr{display:inline-block;float:left;width:50%;position:relative;}
			.ui_shandle_ic3{left: 68% !important;}
			.ui-state-highlight{float:left;height:32px}
			.sort_row_innner{position:absolute;left:0;right:0;top:0;bottom:0;}';
		  $sc.='
			</style>
			';

		$r='<tr><td>
				<div id="sort_table">'.$r.'</div>'.
				  $sc.'
			 </td></tr>';
		return $r;
	}

	public static function addEntryTable($tabledata,$apend='',$tag='',$prepend='',$addhandle=false,
		$sort='',$script_name='',$frm='',$frmend='</form>',$col=1,$hideHidden=0,$ic1_hidden=false,$notdrag_over_row=false)
	{
		$output='';
		if($prepend!=='')
			$output.=self::$f->navtop.$prepend.self::$f->navend.'<br class="ca_br" />';

		if($frm!=='')
			$output.=$frm;
		$output.=str_replace('a_navt','a_navn',self::$f->navlist).'<table class="atable '.self::$f->atbgr_class.'" cellspacing="1" cellpadding="3" '.$tag.'>';
		if($addhandle)
			$output.=self::getEntryTableRowsDrag($tabledata,$sort,$script_name,$col,$hideHidden,$ic1_hidden,$notdrag_over_row);
		else
			$output.=self::getEntryTableRows($tabledata,$script_name);
		if($apend!='')
			$output.='<tr><td>'.$apend.'</td></tr>';
		$output.='</table>';
		$output.=self::$f->navend;
		if($frm!=='')
			$output.=$frmend;
		return $output;
	}

	public static function adminTable($prepend,$captions,$tabledata,$apend='',
					$tag='',$sort='',$form_around_table=array())
	{
		$table_pre=$table_post=$page_top=$page_nav='';
		$page_top=$prepend;
		if(is_array($prepend))
		{
			 $page_top=$prepend[0];
			 $page_nav=$prepend[1];
		}
		if(count($form_around_table)>0)
		{
			$table_pre='<form ';
			foreach($form_around_table AS $fk=> $fv)
				$table_pre.=$fk.'="'.$fv.'" ';
			$table_pre.='>';
			$table_post='</form>';
		}
		$sort_a=array_keys($tabledata);
		if($sort!='')
		{
			$sort_a=explode('|',Formatter::GFS($sort,'','-'));
			if($sort_a[count($sort_a)-1]=='')
				array_pop($sort_a);
			foreach($tabledata as $k=> $v)
			{
				if(array_search($k,$sort_a)===false)
					$sort_a[$k]=strval(count($sort_a));
			}
			$temp=array();
			foreach($sort_a as $k=> $v)
				if(isset($tabledata[$v]))
					$temp[]=$tabledata[$v];
			$tabledata=$temp;
		}

		$cs=count($captions);
		if($cs==0)
			$cs=1;

		$table='<table class="atable '.self::$f->atbgr_class.' a_left" cellspacing="1" cellpadding="4" '.$tag.(empty($tabledata)?' width="500px"':'').'>';
		if(!empty($captions))
		{
			$table.='<tr id="tr_head" class="'.self::$f->atbgr_class.'">';
			foreach($captions as $key=> $value)
			{
				if(is_array($value))
				{
					$table.='<td><a class="a_tabletitle" href="'.$value[0].'" style="text-decoration:'.$value[1].'">'.$value[2].'</a>';
					if(isset($value[3]))
						$table.=$value[3];
					for($i=4; $i<=10; $i++)
					{
						if(isset($value[$i]))
						{
							if(is_array($value[$i]))
								$table.='<a class="a_tabletitle extra" href="'.$value[$i][0].'" style="margin-left: 10px; text-decoration:'.$value[$i][1].'">'.$value[$i][2].'</a>'.$value[$i][3];
							else
								$table.=$value[$i];
						}
					}
					$table.='</td>';
				}
				else
					$table.='<td><span class="a_tabletitle">'.$value.'</span></td>';
			}
			$table.='</tr>';
		}
		$i=1;
		if(!empty($tabledata))
		{
			foreach($tabledata as $key=>$row_data)
			{
				$row='';
				$j=0;
				$hglt_row=is_array($row_data);
				if($hglt_row)
				{
					foreach($row_data as $col_data)
					{
						if(is_array($col_data))
						{
							$row.='<td>';
							$j++;
							$inner='';
							if(is_array($col_data[1]))
								foreach($col_data[1] as $key3=> $col_links)
									if(is_array($col_links)&&count($col_links)>1)
										$inner.='<span class="rvts8">[</span><a class="'.$col_links['class'].' rvts12" '.$col_links['extra_tags'].' href="'.$col_links['url'].'">'.$key3.'</a><span class="rvts8">]</span> ';
									else
										$inner.='<span class="rvts8">[</span><a class="rvts12" href="'.$col_links.'">'.$key3.'</a><span class="rvts8">]</span> ';
							$row.=$col_data[0].'<div class="a_detail" id="aa'.$j.'_'.$i.'">'.$inner.'</div>';
						}
						else
						{
							$style='';
							if(strpos($col_data,'rel="cc:')!==false)
							{
								$ct_color=Formatter::GFS($col_data,'rel="cc:','"');
								$style=' style="border-right: 5px solid '.$ct_color.';"';
							}

							$row.='<td'.$style.'>'.$col_data;
						}
						$row.='</td>';
					}
				}
				else
					$row.='<td colspan="'.$cs.'">'.$row_data.'</td>';
				$row.='</tr>'.F_LF;
				$xclass=Unknown::isOdd($i)?' odd':' even';
				if($hglt_row)
				{
					$table.='<tr id="tr_'.$i.'" class="'.self::$f->atbg_class.$xclass;
					if($j>0)
					{
						$table.='" onmouseover="';
						for($w=1; $w<=$j; $w++)
							$table.='s_roll(\'aa'.$w.'_'.$i.'\',1,this,\''.self::$f->atbgc_class.'\');';

						$table.='" onmouseout="';
						for($w=1; $w<=$j; $w++)
							$table.='s_roll(\'aa'.$w.'_'.$i.'\',0,this,\''.self::$f->atbg_class.'\');';
					}
					else
						$table.='" onmouseover="s_roll(\'\',\'\',this,\''.self::$f->atbgc_class.'\');" onmouseout="s_roll(\'\',\'\',this,\''.self::$f->atbg_class.'\');';
				}
				else
					$table.='<tr class="'.self::$f->atbgc_class.$xclass;
				$table.='">'.$row;
				$i++;
			}
		}
		if($apend!='')
			$table.='<tr id="tr_foot"><td colspan="'.$cs.'">'.$apend.'</td></tr>'.F_LF;
		$table.='</table>';

		$output='<script type="text/javascript">
			$(document).ready(function(){
			$(".row_hidden").parents("tr").hide();
			 });
			</script>'
			.($page_nav.$page_top!==''?'
			<div class="a_n a_navtop">
				<div class="a_navt">'.$page_top.$page_nav.'</div>
			</div><br class="ca_br" />':'').'
			<div class="a_n a_listing">
				<div class="a_navn">'
					.$table_pre
					.$table
					.$table_post
					.($i>11&&$page_nav!=''&&strpos($page_nav,'<textarea')===false?'
					<div class="a_navt a_foot">'.$page_nav.'</div>':'').'
				</div>
			</div>';
		return $output;
	}

	public static function adminDraggableSection($captions,$tabledata,$apend,$tag,$script_path,$sort='',$keybased=false)
	{
		$sort_a=array_keys($tabledata);
		if($sort!='')
		{
			$sort_a=explode('|',Formatter::GFS($sort,'','-'));
			if($sort_a[count($sort_a)-1]=='')
				array_pop($sort_a);
			foreach($tabledata as $k=> $v)
			{
				if(array_search($k,$sort_a)===false)
					$sort_a[$k]=strval(count($sort_a));
			}
			$temp=array();
			foreach($sort_a as $k=> $v)
				if(isset($tabledata[$v]))
					$temp[]=$tabledata[$v];
			$tabledata=$temp;
		}

		$r=str_replace('a_navt','a_navn',self::$f->navlist).'<div id="sort_table" class="atable '.self::$f->atbgr_class.' a_left" '.$tag.(empty($tabledata)?' width="500px"':'').'><div class="head">';
		if(!empty($captions))
		{
			$r.='<div class="'.self::$f->atbgr_class.'" style="padding:10px;">';
			foreach($captions as $key=> $value)
			{
				$r.='<div>';
				if(is_array($value))
					$r.='<a class="a_tabletitle" href="'.$value[0].'" style="text-decoration:'.$value[1].';">'.$value[2].'</a>';
				else
					$r.='<span class="a_tabletitle">'.$value.'</span>';
				$r.='</div>';
			}
			$r.='</div></div>'.F_LF;
		}

		$r.='<div class="tbody">';
		$i=1;
		if(!empty($tabledata))
		{
			foreach($tabledata as $key=> $value)
			{
				$row='';
				$j=0;
				$pid=$value['pid'];
				$ppid=$value['ppid'];
				$hglt_row=is_array($value);

				if($hglt_row)
				{
					foreach($value as $key2=> $value2)
					{
						if($key2==='pid'||$key2==='ppid')
						  continue;

						$inner='';
						if(is_array($value2))
						{
							$j++;
							$inner.=$value2[0];//.'<div class="a_detail" id="aa'.$j.'_'.$i.'"></div>';
						}
						else
							$inner.=$value2;

						$row.='<div>'.$inner.'</div>';
					}
					$row.='<span class="fa fa-arrows ui_shandle_ic2"></span>
								</div>';
				}
				else
					$row.='<div>'.$value.'</div></div>'.F_LF;
				$xclass=Unknown::isOdd($i)?' odd':' even';
				$rowid=$keybased?$key:$sort_a[$key];

				$leftMargin = 0;
				if($pid>-1)
					$leftMargin+=20;
				if($ppid>-1)
					$leftMargin+=20;
				$r.='<div id="sort_'.$rowid.'" rel="'.$key.':'.$pid.':'.$ppid.'" class="ui_shandle '.self::$f->atbg_class.$xclass.' clear" style="margin-left: '.$leftMargin.'px;">'.$row;
				$i++;
			}
		}
		$r.='</div>'; //close the tbody
		if($apend!='')
			$r.='<div class="tfoot clear">'.$apend.'</div>'.F_LF;
		$r.='</div>'.self::$f->navend;

		$r.='
<script type="text/javascript">
		function ca_order() {
		  $.post("'.$script_path.'?ca_order=1",$(".tbody").sortable("serialize"),function(data){
				document.location=\''.$script_path.'?action=categories\'});
		 };
		 $(document).ready(function(){
				initDragging("'.self::$f->atbg_class.'","'.self::$f->atbgc_class.'");
		 });
</script>
<style type="text/css">
	.sub_b, .sub_t, .sub_bg, .topic_bg, .news_bg {width: auto !important;}
	.ui_shandle_ic2 {position: absolute;right: 10px;top: 6px;}
	.clear{height:auto;}
</style>';

		return $r;
	}

	public static function getCountriesArray_fromtxt($rel_path)
	{
		$dir_list=$rel_path."ezg_data/countries_list.php";
		if(is_file($dir_list))
		{
			require_once($dir_list);
			return $countries_list;
		}
		return array();
	}

	public static function getCountriesArray($first_item,$array_countries=array())
	{
		$res=array_merge(array('Select'=>$first_item),$array_countries);
		return $res;
	}

	public static function getCategoryInfo($category_name,$category_color,$category_id,$search_category,$flag)
	{
		settype($search_category,"integer");
		if(in_array($search_category,$category_id))
		{
			$buf=array_search($search_category,$category_id);
			$cat_res=($flag=='name')?Formatter::unEsc($category_name[$buf]):$category_color[$buf];
		}
		else
			$cat_res=($flag=='name')?Formatter::unEsc($category_name[array_search(1,$category_id)]):$category_color[array_search(1,$category_id)];

		return $cat_res;
	}

	# builds logged user menu (logout, edit profile), represented in EZG with %LOGGED_INFO% macro

	public static function buildLoggedInfo($content,$page_id,$root_path,$lg_amp='')
	{
		global $user,$db;

		if(Unknown::strpos_multi($content,array('%USER_COUNT%','%GUEST_COUNT%','%USERS%')))
			$content=User::getOnlineUsers($content);

		$code='%LOGGED_INFO';
		$content=str_replace(
				array($code.'%',"<?php if(function_exists('user_navigation')) user_navigation(); ?>"),
				$code.'()%',
				$content);

		if(strpos($content,$code)!==false)
		{
			$labels=CA::getMyprofileLabels($page_id,$root_path);
			Session::intStart();
			$logged_as_admin=Cookie::isAdmin();
			$logged_as_user=$user->userCookie();
			$logged=$logged_as_user||$logged_as_admin;
			if($logged)
			{
				$pageid_info=CA::getPageParams($page_id,$root_path);

				$logged_user=$logged_as_admin?self::$f->admin_nickname:$user->getUserCookie();
				while(strpos($content,$code)!==false)
				{
					$params_raw=Formatter::GFSAbi($content,$code,')%');
					if($params_raw!='')
					{
						$logged_info='';
						$params=Formatter::GFS($params_raw,'(',')');
						$params=explode(',',str_replace("'",'',$params));
						if(Formatter::strToLower(implode('',$params))=="username"||(isset($params[0])&&$params[0]=='true'))
							$logged_info=$logged_user;
						else
						{
							$captions=$urls=array();
							$thispage_dir=$root_path.((strpos($root_path,'documents')===false)?'documents/':'');
							$ca_url=$thispage_dir.'centraladmin.php?';
							$captions[]=strpos($labels['welcome'],'%%username%%')===false?$labels['welcome'].' ['.$logged_user.']':str_replace('%%username%%',$logged_user,$labels['welcome']);
							$urls[]='';
							if($logged_as_admin)
							{
								if(isset($pageid_info[4])&&in_array($pageid_info[4],self::$f->sp_pages_ids))
								{
									$captions[]=$labels['edit'];
									$urls[]=CA::defineAdminLink($pageid_info);
								}
								$captions[]=$labels['administration panel'];
								$urls[]=$ca_url.'process=index&amp;'.$lg_amp;
								$captions[]=$labels['logout'];
								$urls[]=$ca_url.'process=logoutadmin&amp;pageid='.$page_id.'&amp;'.$lg_amp;
							}
							else
							{
								if(isset($pageid_info[4])&&in_array($pageid_info[4],self::$f->sp_pages_ids)&&User::mHasWriteAccess($logged_user,$pageid_info,$db))
								{
									$captions[]=$labels['edit'];
									$urls[]=CA::defineAdminLink($pageid_info);
								}

								$ca_detailed_url=$ca_detailed_label='';
								CA::get_user_profile_link($page_id,$thispage_dir,str_replace('lang=','',$lg_amp),'',$ca_detailed_url,$ca_detailed_label);

								$captions[]=$labels[$ca_detailed_label];
								$urls[]=$ca_detailed_url;
								$captions[]=$labels['logout'];
								$urls[]=$ca_url.'process=logout&amp;pageid='.$page_id.'&amp;'.$lg_amp;
							}
							$logged_info=Navigation::user($captions,$urls);
						}
						$content=str_replace($params_raw,$logged_info,$content);
					}
				}
			}
			else
				$content=str_replace(Formatter::GFSAbi($content,$code,")%"),'',$content);
		}

		if(strpos($content,'%LOGGED')!==false)
		{
			Session::intStart();
			$logged_as_admin=Cookie::isAdmin();
			$logged_as_user=$user->userCookie();
			$logged=$logged_as_user||$logged_as_admin;
			$logged_name='';
			if($logged)
				$logged_name=$logged_as_user?$user->getUserCookie():self::$f->admin_nickname;
			$content=str_replace(array('%LOGGED%','%LOGGED_USER%'),array($logged,$logged_name),$content);
			if(strpos($content,'%LOGGED_')!==false) //parse other user params if needed
			{
				$user_data=$user->mGetLoggedValues($root_path,self::$f->db);
				foreach($user_data as $k=> $v)
					if(!is_array($v)&&$k!='password')
					{
						if($k=='avatar')
							$v=User::getAvatarFromData($user_data,$db,$user_data['username'],$root_path,'none');
						$content=str_replace('%LOGGED_'.$k.'%',$v,$content);
					}
				if(!$logged_as_user)
					$content=preg_replace('/\%LOGGED_\w+\%/',"",$content);
			}
		}

		return $content;
	}

	public static function getDirectEditJS($container_class,$script_path,$ignore_parents=false)
	{
		return '
	function closeTL(th){$(".ui_hidden").show();$(th).parent().find(".ui").remove();};
	function deleteC(th,idt,idp){$.get("'.$script_path.'",{action:\'del_comment\',\'cc\':1,comment_id:idt,entry_id:idp},function(){$(th).closest(".blog_comments_entry").remove();});}
	function updateTL(th,rel){
		var idt=$(th).prev().attr("name"),dt=$(th).prev().val(),rl=$(th).parent().attr("rel");
		$.get("'.$script_path.'",{action:\'updatetl\',id:idt,data:dt,rel:rl},function(r){
		$(".ui").remove();if(rel) $("."+idt).html(r).attr("rel",dt);else $("."+idt).html(dt);$(".ui_hidden").show();
		});}
	function editTL(id){
		var rel=$("."+id).hasClass("el_rel"),html=rel?$("."+id).attr("rel"):$("."+id).html(),cc=$(".'.$container_class.' ."+id);'.
			($ignore_parents?'':'if(cc.parents("a").length>0) cc=cc.parents("a");').
			'cc.after(\'<input class="ui ui_input" onclick="return false" type="text" name="\'+id+\'" value="\'+html+\'"><input type="button" onclick="updateTL(this,\'+rel+\');" class="ui ui_shandle_ic4"><input type="button" onclick="closeTL(this);" class="ui ui_shandle_ic5">\');
		$(".ui_shandle_ic5").next().addClass("ui_hidden").hide();
	};
	';
	}

	public static function getDirectEditCSS($rel_path)
	{
		return '
		.ui_hidden{display:none;}
		.ui_shandle_ic6,.ui_shandle_ic5,.ui_shandle_ic4{background-color:#fff;background-image: url("'.$rel_path.'extimages/scripts/ui-icons.png");background-position: -64px -144px;border: medium;border-radius:2px;cursor: pointer;height: 16px;margin-left: 2px;width: 16px;}
		.ui_shandle_ic5{background-position: -80px -368px;}.ui_shandle_ic6{background-position: -176px -352px;}.ui_input{width:90%}';
	}

	public static function printImgHtml($rel_path)
	{
		return '<img class="system_img" src="'.$rel_path.'ezg_data/print.png" alt="Print" style="vertical-align: middle;">';
	}

	public static function detailedStat($timestamp,$page_id,$uniq_flag,$firsttime_flag,$q='',$rc=0)
	{

		$frames_mode=(isset($_GET['frames'])&&$_GET['frames']=='1');
		$stat=array();
		$stat['page_id']=$page_id;
		$stat['date']=Date::buildMysqlTime($timestamp);

		$stat['ip']=Detector::getIP();
		$stat['host']=Detector::getRemoteHost();
		if($stat['ip']==$stat['host'])
			$stat['host']='';

		$agent=Detector::readUserAgent(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'',$stat['host']);
		$stat['browser']=(isset($agent['browser'])?$agent['browser']:'');
		$stat['os']=(isset($agent['platform'])?$agent['platform']:'');
		$stat['resolution']=($q!=''?$rc:(isset($_GET['w'])&&isset($_GET['h'])?intval($_GET['w']).'x'.intval($_GET['h']):''));

		$stat['referrer']=($q!='')?'documents/search.php?q='.$q:Detector::getReferer($frames_mode);
		$stat['visit_type']=(!$uniq_flag?'h':($firsttime_flag?'f':'r') );


		$mobile=isset($_REQUEST['m']) && intval($_REQUEST['m'])==1;
		if($mobile)
		  $stat['mobile']=1;

		return $stat;
	}

	//$items_list param must be an array and must has ['email'] index (column)
	public static function doubleSelector($items_list,$left_caption,$right_caption,$left_select_id,$right_select_id,$preselected_items_list=array())
	{

		$table='
			<table>
				<tr><td>
					 <span class="a_tabletitle">'.$left_caption.'</span><br>
					 <select id="left_select" class="input1" multiple size="20" style="width:230px" name="'.$left_select_id.'[]">';
		foreach($items_list as $k=> $v)
		{
			$em=Formatter::sth($v['email']);
			$em2=$v['uid'];
			if(!in_array($em,$preselected_items_list)&&!empty($em))
				$table.='<option value="'.$em2.'">'.$em.'</option>';
		}
		$table.= '</select></td>
			 <td><br>
				<input name="right" type="button" value="  >>  " onclick="moveOptionRight();"><br>
				<input name="left" type="button" value="  <<  " onclick="moveOptionLeft();"><br><br>
				<input name="all" type="button" value="*>>" onclick="moverightAll();">
			</td>
			<td>
				<span class="a_tabletitle">'.$right_caption.'</span><br>
				<select id="right_select" multiple class="input1" size="20" style="width:230px" name="'.$right_select_id.'[]">';
		foreach($preselected_items_list as $k=> $v)
			if(!empty($v))
				$table.='<option value="'.$k.'">'.Formatter::sth($v).'</option>';
		$table.='</select></td></tr></table>';

		return $table;
	}

	public static function ipLocator($ip)
	{
		return '<a class="rvts12" style="text-decoration:none;" href="http://en.utrace.de/?query='.$ip.'" target="_blank">'.$ip.'</a>';
	}

	public static function buildInput($name,$value,$style='',$max_len='',$type='text',
			  $misc='',$frmid='',$label='',$btn_id='',$appendSelected=false,$id='',$xclass='')
	{
		$id_attr=$id==''?'':'id="'.$id.'" ';
		if($type=='textarea')
			$output='<textarea name="'.$name.'" '.$id_attr;
		else
			$output='<input class="input1'.$xclass.'" type="'.$type.'" name="'.$name.'" value="'.str_replace('"','&quot;',$value).'" '.$id_attr;
		if(!empty($label))
			$output='<p><span class="rvts8 a_editcaption" style="line-height:16px">'.$label.'</span><p>'.$output;
		if(!empty($style))
			$output.='style="'.$style.'" ';
		if(!empty($max_len))
			$output.='maxlength="'.$max_len.'" ';
		if(!empty($misc))
			$output.=$misc.' ';
		if($type=='textarea')
			$output.='>'.str_replace('"','&quot;',$value).'</textarea>';
		else
			$output.='>';
		if(!empty($frmid))
			$output.='<span class="rvts12 frmhint" id="'.$frmid.'_'.$name.'"></span>';
		if($btn_id!='')
			$output='<div class="input_wrap" style="position:relative">'.
						  $output.
							'<a class="ui_shandle_ic3'.($appendSelected?' appSel':'').'" rel="'.$btn_id.'"><i class="fa fa-chevron-right"></i></a>
						</div>';
		return $output;
	}

	public static function buildCheckbox($name,$checked,$caption,$class='',$id='')
	{
		$output='<input '.($id!=''?'id="'.$id.'" ':'').'class="forminput'.($class!=''?' '.$class:'').'" type="checkbox" name="'.$name.'" value="1" '.($checked=='1'?' checked="checked" ':'').'style="vertical-align: middle;" > <span class="rvts8 a_editcaption">'.$caption.'</span>';
		return $output;
	}

	public static function buildSelect($name,&$data,$selected,$style='',$mode='key',$jstring='',$class=' class="input1"')
	{
		return self::buildSelect2($name,$name,$data,$selected,$style,$mode,$jstring,$class);
	}

	public static function buildSelect2($name,$id,&$data,$selected,$style='',$mode='key',$jstring='',$class=' class="input1"')
	{
		$r='';
		if(is_array($data)&&!empty($data))
		{
			$r='<select'.$class.' '.$jstring.' '.$style.' id="'.$id.'" name="'.$name."\">";
			foreach($data as $k=> $v)
			{
				$k=($mode=='value'?$v:$k);
				if($mode=='swap')
				{
					$tmp=$k;
					$k=$v;
					$v=$tmp;
				}
				$r.='<option value="'.$k.'"';
				if($k==$selected)
					$r.=' selected="selected"';
				$r.='>'.$v.'</option>';
			}
			$r.='</select>';
		}
		return $r;
	}

	public static function buildTagCloud($script_path,$all_records,$max_tags=50,
			$style='',$ccloud=false,$use_alt_urls=false,$min_occs=-1,$alpha_cols=0,$max_font_size=0)
	{
		$output='';
		$use_px=0;
		$tags_list=array();

		$action=$ccloud?'category':'tag';

		if($ccloud)
			$tags_list=$all_records;
		else
			foreach($all_records as $k=> $v)
			{
				$tags_per_record=explode(',',(urldecode(isset($v['Keywords'])?$v['Keywords']:$v['keywords'])));
				foreach($tags_per_record as $tag)
				{
					if($tag!='')
					{
						$tr_tag=Formatter::strToLower(trim($tag));
						if($tr_tag!==''&&array_key_exists($tr_tag,$tags_list))
							$tags_list[$tr_tag]=$tags_list[$tr_tag]+1;
						else
							$tags_list[$tr_tag]=1;
					}
				}
			}
		if($min_occs>1)
		{
			foreach($tags_list as $tname=> $tcount)
				if($tcount<$min_occs)
					unset($tags_list[$tname]);
		}
		if(!empty($tags_list))
		{
			if((count($tags_list)>$max_tags))
			{
				arsort($tags_list);
				$tags_count=0;
				$new_tags_list=array();
				foreach($tags_list as $k=> $v)
				{
					$new_tags_list[$k]=$v;
					$tags_count++;
					if($max_tags<$tags_count)
						break;
				}
				$tags_list=$new_tags_list;
			}
			$max_freq=max(array_values($tags_list));
			$min_freq=min(array_values($tags_list));
			$diff=$max_freq-$min_freq;
			if($diff<1)
				$diff=1;
			ksort($tags_list);

			$max_font_size=$max_font_size>0?$max_font_size:($use_px?24:200);
			$min_font_size=$use_px?13:100;

			$output='';
			if($alpha_cols>0)  //aplhabetical list
			{
				$tcnt=count($tags_list);
				$tags=array();
				foreach($tags_list as $tag=> $cnt)
				{
					$l=mb_substr($tag,0,1,'UTF-8');
					if(!isset($tags[$l]))
						$tags[$l]=array();
					$tags[$l][$tag]=$cnt;
				}
				$tcnt+=count($tags);
				$colmax=round($tcnt/$alpha_cols);
				$w='position:relative;float:left;width:'.round(100/$alpha_cols).'%;';
				$icnt=0;
				$ul_open=true;
				$output.='<li class="tcloud_column" style="'.$w.'"><ul>';
				foreach($tags as $l=> $la)
				{
					$icnt++;
					$output.='<li class="alpha tcloud_head"><span>'.$l.'</span></li>';

					foreach($la as $tag=> $cnt)
					{
						$tag_enc=htmlspecialchars(stripslashes($tag),ENT_QUOTES);
						$output.='<li class="alpha tcloud_line"><a href="'.$script_path.($use_alt_urls?'/'.$action.'/':$action.'=').urlencode($tag).($use_alt_urls?'/':"").'" title="'.$tag_enc.'('.$cnt.')">'.$tag_enc.'</a> </li>';
						$icnt++;
						if($colmax<=$icnt)
						{
							$icnt=0;
							$output.='</ul></li><li class="tcloud_column" style="'.$w.'"><ul>';
							$ul_open=true;
						}
					}
				}
				$output.='</ul></li>';
			}
			else
				foreach($tags_list as $k=> $v)
				{
					if($k!=='')
					{
						$size=((($max_font_size-$min_font_size)/$diff)*($v-$min_freq))+$min_font_size;
						$tag_enc=htmlspecialchars(stripslashes($k),ENT_QUOTES);
						$output.='<li><a '.$style.' href="'.$script_path.($use_alt_urls?'/'.$action.'/':$action.'=').urlencode($k).($use_alt_urls?'/':"").'" style="font-size:'.($use_px?$size.'px':round($size/100,2).'em').';" title="'.$tag_enc.'('.$v.')">'.$tag_enc.'</a> </li>';
					}
				}
		}
		$xclass=$alpha_cols?'':($use_px?' tcloud_px':' tcloud_em');
		if(!empty($output))
			$output='<div class="tcloud_container">
			<ul class="tcloud'.$xclass.'">'.$output.'</ul>
			</div><div style="clear:left"></div>';
		return $output;
	}

	public static function dateTimeInput($id,$date,$time_format,$month_names_ar,$xtrajs='',$iid=true)
	{
		return self::dateTimeInput2($id,$id,$date,$time_format,$month_names_ar,$xtrajs,$iid);
	}

	public static function dateTimeInput2($id,$name,$date,$time_format,$month_names_ar,$xtrajs,$iid,$time=true)
	{
		$tf=intval($time_format);
		$dateValue=Date::dp($month_names_ar,$date);
		$cd='<input class="input1 '.$id.'" '.($iid?'id="'.$id.'"':'').' name="'.$name.'" type="text" readonly="readonly" value="'.$dateValue.'"'.$xtrajs.'>';
		$cd.=$time?'@'.self::buildTimeSelect($id,$tf,$date):'';
		return $cd;
	}

	public static function buildTimeSelect($id,$tf,$date)
	{
		$f_min_sec=array();
		for($n=0; $n<60; $n++) $f_min_sec[]=($n<10)?'0'.strval($n):strval($n);
		$hour=date(($tf==12?'g':'G'),$date);
		$min=date('i',$date);

		if($tf==12)
		{
			$hours_array=array('0','1','2','3','4','5','6','7','8','9','10','11','12');
			$ampm_array=array('AM','PM');

		}
		else
			$hours_array=array('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23');
		$cd=self::buildSelect($id.'_hour',$hours_array,$hour,'','value').'<span class="rvts8">:</span>'
		.self::buildSelect($id.'_min',$f_min_sec,$min,'','value');

		if($tf==12)
			$cd.=self::buildSelect($id.'_ampm',$ampm_array,date('A',$date),'','value');

		return $cd;
	}

	public static function buildCalendar($mon,$year,$first_day_ofweek,$events_by_day,$url,$month_names,$day_names,$utf_fl=false,$suf='?')
	{
		$days_in_curr_mon=Date::daysInMonth($mon,$year);
		$month=$month_names[$mon-1];

		if($first_day_ofweek==1)
			$firstday=date('w',mktime(0,0,0,$mon,1,$year));
		else
		{
			$day=date('w',mktime(0,0,0,$mon,1,$year));
			$firstday=($day==0?6:$day-1);
			$temp=$day_names[0];
			$day_names_rev=$day_names;
			array_shift($day_names_rev);
			array_push($day_names_rev,$temp);
		}
		settype($firstday,'integer');
		$cal_pointer=$firstday;
		$row_counter=0;

		$nav_prev=Navigation::cal($mon,$year,'prev',$url.$suf);
		$nav_next=Navigation::cal($mon,$year,'next',$url.$suf);

		$html='
			<tr><td colspan="8" class="calh1">
				<div style="position:relative;height:16px;width:100%;">
					<div style="width:100%;text-align:center;">'.Formatter::mySubstr($month,0,3,$utf_fl).' '.$year.'</div>
					<div style="position:absolute;top:0px;left:0;">'.$nav_prev.'</div>
					<div style="position:absolute;top:0px;right:0px">'.$nav_next.'</div>
				</div>
			</td></tr>
			<tr>';

//weekday names
		foreach(($first_day_ofweek==1?$day_names:$day_names_rev) as $v)
			$html.='<td class="calh2">'.Formatter::mySubstr($v,0,1,$utf_fl).'</td>';
		$html.='</tr><tr>';
//last days from previous month
		if($firstday!=0||($mon==2&&$days_in_curr_mon==28))
		{
			$days_prev_mon=($mon==1)?Date::daysInMonth(12,$year):Date::daysInMonth(($mon-1),$year);
			if($firstday!=0)
			{
				$t=$days_prev_mon-$firstday+1;
				for($i=0; $i<$firstday; $i++)	$html.='<td class="day3">'.$t++.'</td>';
			}
			else
			{
				$t=$days_prev_mon-6;
				for($i=0; $i<7; $i++) $html.='<td class="day3">'.$t++.'</td>';
				$html.='</tr>';
			}
		}
//  displaying days from selected month
		for($i=1; $i<=$days_in_curr_mon; $i++)
		{
			if($cal_pointer>6)
			{
				$cal_pointer=0;
				$html.='</tr><tr>';
				$row_counter++;
			}
			$today=Date::isCurrentDay($i,$mon,$year);
			if(array_key_exists(($i),$events_by_day))
			{
				$html.='
					<td class="'.($today?'currday':'day2').'">
						<a style="position:relative;z-index:1;" class="'.($today?'currday':'calurl').'" href="'.$url.$suf.'mon='.$mon.'&amp;year='.$year.'&amp;day='.$i.'">'.$i.'</a>
					</td>';
			}
			else
				$html.='<td class="'.($today?'currday':'day1').'">'.$i.'</td>';
			$cal_pointer++;
		}
//  displaying first days from next month
		$next_month_days=1;
		while($cal_pointer<=6)
		{
			$html.='<td class="day3">'.$next_month_days.'</td>';
			$next_month_days++;
			$cal_pointer++;
		}
		$html.='</tr>';
		$row_counter++;
		if($row_counter<6)
		{
			$html.="<tr>";
			$cal_pointer=0;
			while($cal_pointer<=6)
			{
				$html.='<td class="day3">'.$next_month_days.'</td>';
				$next_month_days++;
				$cal_pointer++;
			}
			$html.='</tr>';
		}
		$html='
				<div class="cal_bg">
				<table class="calendar">'.
				$html.'
				</table>
				</div>
				<style>
				.calendar{border:1px solid #ccc;background:white;border-radius:4px;}
				.calendar td{padding:3px;font-size:10px;}
				.calendar .calh1{background: #f1f1f1;}
				</style>
			';
		return $html;
	}

	public static function ogMeta($page_src,$tags,$fb_api_id='')
	{
		$macro=strpos($page_src,'<!--rss_meta-->')!==false?'<!--rss_meta-->':'<!--scripts-->';
		$meta='';
		if($fb_api_id!='')
			$meta.='<meta property="fb:app_id" content="'.$fb_api_id.'">'.F_LF;
		foreach($tags as $k=> $v)
			$meta.='<meta property="og:'.$k.'" content="'.$v.'">'.F_LF;
		if($meta!='')
			$page_src=str_replace($macro,$macro.F_LF.$meta,$page_src);

		$html=Formatter::GFSAbi($page_src,'<html','>');
		if(strpos($html,'xmlns:og')==false)
			$page_src=str_replace($html,str_replace('>',' xmlns:og="http://opengraphprotocol.org/schema/">',$html),$page_src);

		return $page_src;
	}

}

class MailHandler extends FuncHolder
{

	public static function resolveMail($m,$def='')
	{
		$ma=array();
		$name=$def;
		if((strpos($m,'<')!==false))
		{
			$address=Formatter::GFS($m,'<','>');
			$name=stripslashes(Formatter::GFS($m,'"','"'));
		}
		else
			$address=$m;
		$ma[]=trim($address);
		$ma[]=trim($name);
		return $ma;
	}

	public static function sendMail($to,$from,$content_html,$content_text,$subject,$page_charset,
			  $att_content='',$att_file='',$att_filetype='',$send_to_author='',$author_data=array(),
			  $send_to_bcc='',$reply_to='')
	{
		include_once('mail5.php');

		$sendto=(is_array($to))?implode(";",$to):$to;
		if($subject=='')
		  $subject='Auro-reply from '.Detector::getRemoteHost();
		if($content_html!='' && strpos($content_html,'<body')===false)
		  $content_html='
<!DOCTYPE html>
<html>
<head>
<meta charset="'.$page_charset.'">
<style>
body{font: 16px Helvetica Neue,Helvetica,Lucida Grande,tahoma,verdana,arial,sans-serif;}
ul,ol,li{border:0px;padding:0px;margin:0px;}
div,li,h1,h2,h3,h4,h5,h6,p,form{margin:0;padding:0;}
img{vertical-align:bottom;}
a{outline:none;}
form {padding:0px;display:inline;margin:0px;}
tr,td{vertical-align:top;}
</style>
</head>
<body>'
.$content_html.'
<body>
</html>';

		$result=m_sendMail($sendto,$from,stripslashes($content_html),stripslashes($content_text),stripslashes($subject),
				$page_charset,$att_content,$att_file,$att_filetype,$send_to_author,$author_data,
				$send_to_bcc,$reply_to,
				self::$f->mail_type,self::$f->return_path,self::$f->sendmail_from,self::$f->use_linefeed,self::$f->SMTP_HOST,self::$f->SMTP_PORT,self::$f->SMTP_HELLO,self::$f->SMTP_AUTH,self::$f->SMTP_AUTH_USR,self::$f->SMTP_AUTH_PWD,self::$f->admin_nickname,self::$f->SMTP_SECURE);
		return $result;
	}

	public static function sendMailCA($db,$msg,$subject,$send_to='',$bcc='')
	{
		if($db==null)
			$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));

		$res=false;
		$admin_email=CA::getDBSettings($db,'sr_admin_email');

		$from=(self::$f->sendmail_from=='')?$admin_email:self::$f->sendmail_from;
		if($from=='')
			$from='admin@'.Detector::getRemoteHost();
		$to=array(($send_to=='')?$admin_email:$send_to);

		if($to=='')
			return '<div align="left">
				 <h1>Admin e-mail address not defined!</h1>
				 <h2>To solve the problem, go to Online Administration >> Registration Settings and define Admin Email!</h2>';

		if(in_array('UTF-8',self::$f->site_charsets_a))
			$page_charset='UTF-8';
		else
			$page_charset=(isset($_GET['charset'])?Formatter::stripTags($_GET['charset']):self::$f->site_charsets_a[0]);
		if($bcc!='')
			$res=self::sendMailStat($db,0,$to,$from,$msg,'',$subject,$page_charset,'','','','',array(),$bcc);
		else
			$res=self::sendMailStat($db,0,$to,$from,$msg,'',$subject,$page_charset);

		return $res;
	}

	public static function sendMailStat($db,$p_id,$send_to,$from,$content_html,$content_text,$subject,$page_charset,
			  $att_content='',$att_file='',$att_filetype='',$send_to_author='',$author_data=array(),$send_to_bcc='',
			  $reply_to='')//15 params
	{

		$result=self::sendMail($send_to,$from,$content_html,$content_text,$subject,$page_charset,
				  $att_content,$att_file,$att_filetype,$send_to_author,$author_data,$send_to_bcc,$reply_to);
		if($db==null)
			$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));
		$data=array();
		$data['page_id']=$p_id;
		$rec='';
		if(is_array($send_to))
			foreach($send_to as $k=> $v)
				$rec.=$v.' ';
		else
			$rec=$send_to;
		$data['send_to']=$rec;
		$data['bcc']=$send_to_bcc;
		$data['reply_to']=$reply_to;
		$data['msgfrom']=$from;
		$data['message_html']=$content_html;
		$data['message_text']=$content_text;
		$data['subject']=$subject;
		$data['success']=$result;
		$att='';
		if(is_array($att_content))
		{
			foreach($att_content as $k=> $v)
				$att.=$att_file[$k].' ';
		}
		else
			$att=$att_file;
		$data['attachments']=$att;
		$data['ip']=Detector::getIP();
		$data['referer']=Detector::getReferer();

		$fa=$db->get_tables('ca_email_data');
		if(!empty($fa))
		{
			$field_names=$db->db_fieldnames('ca_email_data');
			if(!in_array('ip',$field_names))
				$db->query('ALTER TABLE '.$db->pre."ca_email_data ADD ip varchar (255) NOT NULL default ''");
			$db->query_insert('ca_email_data',$data);
		}

		return $result;
	}

	public static function mailer($settings,$flag,$pg=null)
	{
		include_once('mailer.php');

		switch($flag)
		{
			case 'BL':$mailer=new BlogMailer($settings,$pg);
				break;
			case 'CA':$mailer=new CAMailer($settings);
				break;
			default: break;
		}
		$mailer->process();
		return $mailer->output();
	}

}

class ImportHandler extends FuncHolder
{

	public static function import($settings,$flag)
	{
		include_once('importer.php');
		switch($flag)
		{
			case 'LI':$importer=new ShopImporter($settings);
				break;
			case 'NL':$importer=new NewsImporter($settings);
				break;
			case 'CA':$importer=new CAImporter($settings);
				break;

			default: break;
		}
		$importer->process();
		return $importer->output();
	}

}

class Filter extends FuncHolder
{

	public static function adminBar($fast_nav,$left_content,$right_content)
	{
		$fast_nav_items=$fast_nav[0];
		$fast_nav_selected=$fast_nav[1];
		$output='';
		if(is_array($fast_nav_items))
		{
			foreach($fast_nav_items as $v)
			{
				$class=(((!isset($v['status'])&&$fast_nav_selected=='')||(isset($v['status'])&&$v['status']==$fast_nav_selected))?' class="selected"':'');
				$output.='<a'.$class.' href="'.$v['url'].'">'.$v['label'].' ('.$v['count'].')'.'</a>';
			}
		}
		$output='<div class="filter_bar">'.$output.'</div><div class="filter_bar2">'.$left_content.'<div class="filter_bar_search">'.$right_content.'</div></div>';
		return $output;
	}

	public static function build($id,$filter,$action,$style='width:20px',$jstring='')
	{
		return '<span style="float:right">
					 <input title="filter" type="text" id="'.$id.'" name="'.$id.'" class="input1 direct_edit autosize" value="'.$filter.'" style="font-size:11px;'.$style.'"'.$jstring.'>
					 <span class="i_check fa fa-check-square" style="display:none" onclick="'.$action.'"></span>
				</span>';
	}

	public static function multiUnique($array)
	{
		$new=$new1=array();

		foreach($array as $k=> $na)
			$new[$k]=serialize($na);
		$uniq=array_unique($new);
		foreach($uniq as $k=> $ser)
			$new1[$k]=unserialize($ser);
		return $new1;
	}

	public static function orderBy($defOrder,$defAsc)
	{
		$orderby=(isset($_REQUEST['orderby']))?Formatter::stripTags($_REQUEST['orderby']):$defOrder;
		$asc=(isset($_REQUEST['asc']))?Formatter::stripTags($_REQUEST['asc']):$defAsc;
		return array($orderby,$asc);
	}

	public static function imgAltTag($html)
	{
		$html=preg_replace('/<img[^>]*alt="([^"]*)"[^>]*>/i',"$1",$html);
		return $html;
	}
}

class Navigation extends FuncHolder
{

	public static function addEntry($caption,$url,$active,$id,$span='',$class='',$hidden=false)
	{
		 $icons=array('posts'=>'fa-tasks','write_post'=>'fa-pencil','categories'=>'fa-bars','comments'=>'fa-comments-o',
				'em_settings'=>'fa-cog','settings'=>'fa-cogs','administration'=>'fa-sitemap','logout'=>'fa-power-off',
				'sitemap'=>'fa-sitemap','users'=>'fa-user','groups'=>'fa-users','maillog'=>'fa-envelope-o','polls'=>'fa-bar-chart',
			  'site_history'=>'fa-briefcase','log'=>'fa-file-o','pic'=>'fa-picture-o','trackbacks'=>'fa-exchange',
				'page_view'=>'fa-desktop','manage'=>'fa-pencil','responses'=>'fa-tasks','analyze'=>'fa-bar-chart',
				'products'=>'fa-tag','stock'=>'fa-tags','pending'=>'fa-circle-o','orders'=>'fa-circle',
				'coupons'=>'fa-scissors','features'=>'fa-asterisk','analytics'=>'fa-google','newsletters'=> 'fa-envelope-square','bundles'=>'fa-plus-square',
				'subscribers'=>'fa-user','logs'=>'fa-file-o','log_errors'=>'fa-file-o',
				'profile'=>'fa-user','messenger'=>'fa-comments-o','mymessenger'=>'fa-comments-o','vieworders'=>'fa-shopping-cart','changepass'=>'fa-lock',
				'taxes'=>'fa-money');
		 return array('caption'=>$caption,'url'=>$url,'id'=>$icons[$id],'active'=>$active,'span'=>$span,'class'=>$class,'hidden'=>$hidden);
	}

	public static function admin2($data,$caption='',$page_view=false,$page_name='',$page_url='')
	{
		$sel=$sel_id='';
		$output=str_replace('a_navt','a_nav',self::$f->navtop).'<!--start_ca_header-->';
		foreach($data as $v)
		{
			if($v['url']=='')
				$output.=' <span>'.$v['caption'].'</span> ::';
			else
			{
				if($v['active'])
				{
					$sel=$page_view?'':$v['caption'];
					$sel_id=$v['id'];
				}
				if($v['active'] || !$v['hidden'])
				$output.='<span class="a_nav_l'.($v['class']!=''?' '.$v['class']:'').($v['active']?' active':'').'">
					<a class="nav_link'.($v['active']?' selected ':'').'" href="'.$v['url'].'">'.$v['caption'].'</a>'
					.($v['span']!=''?' <span class="nav_logout">[<span class="ca_user">'.$v['span'].'</span>]</span>':'')
					.'<a title="'.$v['caption'].'" href="'.$v['url'].'" class="ca_nav_icon '.$v['id'].'"></a></span>
					 <span class="a_nav_s'.($v['active']?' active':'').'"></span><span class="a_nav_r"></span>';
			}
		}
		if(self::$f->ca_fullscreen)
		  $output.='<span class="ca_toggle ca_nav_icon fa-chevron-left '.(CA::getCaMiniCookie()?'':'active').'"></span>
						<span class="ca_toggle ca_nav_icon fa-chevron-right '.(CA::getCaMiniCookie()?'active':'').'"></span>';

		$output.='<!--end_ca_header--></div>';

		if(!self::$f->ca_fullscreen)
			$output.='<div class="a_nav">
						  <span id="a_caption" class="a_caption">'.($caption==''?$sel:$caption)
						  .($page_name?' <a class="a_pagelink" href="'.$page_url.'">('.$page_name.')</a>':'').'
						  </span>'
				.self::$f->navend;
		else
		{
			$output.='<div class="a_nav">'.self::$f->navend;
			if($sel.$caption!='')
				$output.='<div class="a_navtitle a_navt">
					<span class="ca_title_icon '.$sel_id.'"></span><span id="a_caption" class="a_caption">'.$sel.$caption
					.($page_name?' <a style="font-size:15px;text-decoration:none" href="'.$page_url.'">('.$page_name.')</a>':'').'
					</span>
				</div>';
			$output=str_replace(array('<!--pre-nav-->','<!--post-nav-->'),array('<div class="a_header"></div>','<div class="a_footer"></div>'),$output);
		}
		return $output;
	}

	public static function user($captions,$urls,$selected='')
	{
		$output='<div class="logged_container" style="padding:2px;text-align:center;">';
		foreach($captions as $k=> $v)
		{
			$format_user='';
			$value=$v;
			if(empty($urls[$k]))
				$output.=' <span class="rvts8 logged_span">'.$value.'</span> |';
			elseif($k==$selected)
				$output.=' <a class="rvts8 logged_link" href="'.$urls[$k].'">'.$value.'</a> |';
			else
			{
				if(strpos($v,'[')!==false)
				{
					$user=Formatter::GFSAbi($v,'[',']');
					$format_user=' <span class="rvts8 logged_span">'.$user.'</span>';
					$value=str_replace($user,'',$v);
				}
				if(!empty($v)&&$v!=' ')
					$output.=' <a class="rvts12 logged_link" href="'.$urls[$k].'">'.$value.'</a>'.$format_user.' |';
			}
		}
		$output.='<!--end_ca_header--></div>';
		return $output;
	}

	public static function pageCA($rec_count,$page_url,$recordsPerPage,$page)
	{
		return self::page($rec_count,$page_url,$recordsPerPage>0?$recordsPerPage:self::recordsPerPage(),$page,' / ','nav',self::$f->ca_nav_labels,'&amp;','',false,false,'',true);
	}

	public static function recordsPerPage()
	{
		return self::$f->ca_settings['max_rec_on_admin'];
	}

	public static function page($rec_count,$page_url,$recordsPerPage,$page=1,$of_label='of',$class='rvts12',
			  $src_labels,$pg_prefix='&amp;',$lang='',$use_alt_plinks=false,$addhome=false,$homeurl='',
			  $ca=false,$params='',$view='')
	{
		if($recordsPerPage<0)
			$recordsPerPage=1;

		if($view!='')
			$view.='/';
		if($view!='' && strpos($page_url,$view))
			$page_url=str_replace($view,'',$page_url);
		$output='';
		if(substr($page_url,-1)=='?')
			$pg_prefix='';

		$purl=$use_alt_plinks?
				  $page_url:
				  $page_url.$pg_prefix.'page=';
		$cl_url=($use_alt_plinks?'/'.($view!=''?$view:''):$lang);

		if(!isset($src_labels['home']))
			$src_labels['home']='home';

		$prevnext=strpos($params,'prevnext')!==false;
		$compact=strpos($params,'compact')!==false || $prevnext; //comapct labels : same as ca labels + 123  >
		$loadMore=strpos($params,'loadmore')!==false; //adds "Load more button" instead of the 123 numbers
		$loadmore_search=$loadMore&&strpos($params,'search')!==false; //adds "Load more button" instead of the 123 numbers in search.php
		$labels=$compact?self::$f->ca_nav_labels:$src_labels;

		$lb=$compact?'':'<span class="'.$class.' nav_brackets left">[</span>';
		$rb=$compact?'':'<span class="'.$class.' nav_brackets right">]</span>';
		$class=strpos($class,'class=')!=false?Formatter::GFS($class,'"','"'):$class;
		$div_class=$ca?'class="ca_nav"':'class="user_nav"';

		$labels['home_title']=$src_labels['home'];
		$labels['prev_title']=$src_labels['prev'];
		$labels['next_title']=$src_labels['next'];
		$src_labels['load more']=isset($src_labels['load more'])?$src_labels['load more']:'Load More';

		$tabsmax=6;
		$pcount=round(($rec_count-1)/$recordsPerPage)+1;
		$pcount=ceil($rec_count/$recordsPerPage);
		$rel_path=Detector::getRelPath();

		if($rec_count>0)
		{
			if($recordsPerPage>0)
			{
				if($loadMore)
				{
					$next_url=($page<$pcount)?$purl.($page+1).$cl_url:'end';

					if($next_url=='end' && $page==1)
						return ' ';
					$hide=strpos(self::$f->buttonhtml,'<a class="')!==false?
							  '$(".user_nav").off("click").parent().hide();':
							  '$(".user_nav").off("click").hide();';

					$ct_class=$loadmore_search?'search_blocks':'blog_container';
					$captchaLoader=(self::$f->captcha_size=='sliding captcha')?'loadSlidingCaptcha()':'loadCaptcha("'.$rel_path.'")';
					$script_loadmore ='
<script type="text/javascript">
loadMore=function()
{
	var $loader=$(\'<div id="nav_loader"/>\');
	var $currData=$(".'.$ct_class.'").html();
	var $pageToLoad=$(".user_nav").attr("rel");
	$(".'.$ct_class.':first").append($loader);
	$.get($pageToLoad,function( data ) {
	 var dt=$(data).find(".'.$ct_class.'").html();
	 if(dt!=undefined){
		 $(".'.$ct_class.':first").append(dt);
		 url=$(data).find(".user_nav").attr("rel");
		 $(".user_nav").attr("rel",url);
		 if(url=="end") '.$hide.'
	 }
	 else '.$hide.'
	 $("#nav_loader").remove();
	 $(document).trigger("ready");
	 if($(".mbox").length>0) $(".mbox").multibox({heff:false});
	 $(".'.$ct_class.' .ranking").ranking({rsw:55});
	 if(typeof updateOnAjax == "function") {
	  updateOnAjax(2);
	 }
	 $("span.captcha").empty();
	 '.$captchaLoader.';
	});
};
$(".user_nav").click(function(){loadMore();});
</script>';
					return Builder::buildButton($src_labels['load more'],'javascript:void(0);','user_nav load_more','',$next_url).$script_loadmore;
				}
				$output.='<div '.$div_class.'><table '.($prevnext?'':'style="width:100%"').'><tr><td><span class="rvts8">';
				if($addhome)
					$output.=$lb.'<a class="'.$class.' nav_home" href="'.$homeurl.'" title="'.Formatter::replaceLG($labels['home_title']).'">'.Formatter::replaceLG($labels['home']).'</a>'.$rb.'&nbsp;';

				if($page>1)
				{
					if($prevnext)
						$output.='<a class="'.$class.' fa fa-long-arrow-left" href="'.$purl.($page-1).$cl_url.'" title="'.Formatter::replaceLG($labels['prev_title']).'"></a>';
					else
						$output.=$lb.'<a class="'.$class.' nav_prev" href="'.$purl.($page-1).$cl_url.'" title="'.Formatter::replaceLG($labels['prev_title']).'">'.Formatter::replaceLG($labels['prev']).'</a>'.$rb.'&nbsp;';
				}
				if($pcount<=$tabsmax)
				{
					$start=1;
					$stop=$pcount;
				}
				else
				{
					$start=$page-round($tabsmax/2);
					$start=max($start,1);
					$stop=$start+$tabsmax;
					if($stop>$pcount)
					{
						$stop=$pcount;
						$start=$stop-$tabsmax+1;
					}
				}
				if(!$prevnext)
				{
					if($start>1)
					{
						$output.='<a class="'.$class.'" href="'.$purl.'1'.$cl_url.'">1</a> ';
						if($start>2)
							$output.='<span class="'.$class.' nav_dots"> ... </span>';
					 }

					 if($start!=$stop)
						 for($i=$start; $i<$stop+1; $i++)
						 {
							if($i==$page&&$page<=$pcount)
								$output.=$lb.'<span class="'.$class.' nav_active">'.$i.'</span>'.$rb;
							else
								$output.=' <a class="'.$class.'" href="'.$purl.$i.$cl_url.'">'.$i.'</a> ';
						  }

					 if($stop<$pcount)
					 {
						  if($stop<$pcount-1)
								$output.='<span class="'.$class.' nav_dots"> ... </span>';
						  $output.=' <a class="'.$class.'" href="'.$purl.($pcount).$cl_url.'">'.$pcount.'</a>';
					 }
				}

				if($page<$pcount)
				{
					if($prevnext)
						$output.='<a class="'.$class.' fa fa-long-arrow-right" href="'.$purl.($page+1).$cl_url.'" title="'.Formatter::replaceLG($labels['next_title']).'"></a>';
					else
						$output.='&nbsp;'.$lb.'<a class="'.$class.' nav_next" href="'.$purl.($page+1).$cl_url.'" title="'.Formatter::replaceLG($labels['next_title']).'">'.Formatter::replaceLG($labels['next']).'</a>'.$rb;
				}

				$output.='</span></td>';
				if($rec_count>1)
				{
					$opt=array('10', '20','50','100');
					$output.='<td style="text-align:right"><span class="rvts8 '.$class.' nav_count">'.(($page-1)*$recordsPerPage+1).'-'
						.($recordsPerPage*$page>$rec_count?$rec_count:$recordsPerPage*$page).' '.$of_label.' '.$rec_count.'</span>
						'.($ca?Builder::buildSelect('rpp',$opt,$recordsPerPage,'style="font-size:10px;width:40px;"','value','onchange="setRPP(this,\''.$rel_path.'\')"'):'').'
						</td>';
				}
				$output.='</tr></table></div>';
			}
			else
			{
				$output='<div '.$div_class.' style="text-align:right;padding: 2px 0;">';
				if($addhome)
					$output.=$lb.'<a class="'.$class.' nav_home" href="'.$homeurl.'" title="'.$labels['home_title'].'">'.Formatter::strToUpper($labels['home']).'</a>'.$rb.'&nbsp;';
				$output.='<span class="rvts8 '.$class.' nav_count">1-'.$rec_count.' '.$of_label.' '.$rec_count.'</span></div>';
			}
		}
		return $output;
	}

	public static function entry($prev,$next,$prev_title,$next_title,$page_url,$labels,$params='')
	{
		$class='rvts12';
		if(strpos(self::$f->buttonhtml,'e_button')!==false)
			$class.=' e_button';
		elseif(strpos(self::$f->buttonhtml,'art-button')!==false)
			$class.=' art-button';
		else
			$class.=' nav-button';

		$compact=strpos($params,'compact')!==false;
		$floating=strpos($params,'floating')!==false;

		if($compact)
			$labels=self::$f->ca_nav_labels;

		$div_class='class="user_nav"';
		if(!isset($labels['home']))
			$labels['home']='home';

		$output='<div '.$div_class.' style="padding: 2px 0;display:inline-block;">
				<span class="rvts8">';
		if($floating)
		{
			if($prev!='')
				$output.='<div style="float:left;text-align:left;"><a class="'.$class.' nav_prev" href="'.$prev.'" title="'.Formatter::sth($prev_title).'">'.$labels['prev'].'</a><br>'.Formatter::sth($prev_title).'&nbsp;</div>';
			if($next!='')
				$output.='<div style="float:right;text-align:right;"><a class="'.$class.' nav_next" href="'.$next.'" title="'.Formatter::sth($next_title).'">'.$labels['next'].'</a><br>'.Formatter::sth($next_title).'</div>';
			$output.='<div style="width:20%;margin: 10px auto;text-align:center;"><a class="'.$class.' nav_home" href="'.$page_url.'" title="'.$labels['home'].'">'.$labels['home'].'</a>&nbsp;</div>';
			$output.='<div style="clear:both;"></div></span></div>';
		}
		else
		{
			$output.='<a class="'.$class.' nav_home" href="'.$page_url.'" title="'.$labels['home'].'">'.$labels['home'].'</a>&nbsp;';
			if($prev!='')
				$output.='<a class="'.$class.' nav_prev" href="'.$prev.'" title="'.Formatter::sth($prev_title).'">'.$labels['prev'].'</a>&nbsp;';
			if($next!='')
				$output.='<a class="'.$class.' nav_next" href="'.$next.'" title="'.Formatter::sth($next_title).'">'.$labels['next'].'</a>';
			$output.='</span></div>';
		}
		return $output;
	}

	public static function cal($mon,$year,$type,$url)  // calendar < > navigation
	{
		$prev_mon=$mon-1;
		$prev_year=$year;
		$next_mon=$mon+1;
		$next_year=$year;

		if($mon==1&&$year>1950)
		{
			$prev_mon=12;
			$prev_year=$year-1;
		}
		elseif($mon==1&&$year<=1950)
		{
			$prev_mon=1;
			$prev_year=1950;
		}
		elseif($mon==12&&$year<2050)
		{
			$next_mon=1;
			$next_year=$year+1;
		}
		elseif($mon==12&&$year>=2050)
		{
			$next_mon=12;
			$next_year=2050;
		}

		$output='<span style="background:transparent;width:12px;cursor:pointer;" onclick="document.location=\''.$url;
		if($type=='prev')
			$output.="mon=".$prev_mon."&amp;year=".$prev_year;
		else
			$output.="mon=".$next_mon."&amp;year=".$next_year;
		$output.='\';">'.($type=='prev'?'&lt;':'&gt;').'</span>';
		return $output;
	}
}

class Linker extends FuncHolder
{
	public static function getHost()
	{
		$host='';
		if(self::$f->use_hostname && isset($_SERVER['HTTP_HOST']))
			$host=$_SERVER['HTTP_HOST'];
		elseif(isset($_SERVER['SERVER_NAME']))
		{
			$host=$_SERVER['SERVER_NAME'];
			if(isset($_SERVER['SCRIPT_URI']) && strpos($_SERVER['SCRIPT_URI'],$host)===false && isset($_SERVER['HTTP_HOST']))
				$host=$_SERVER['HTTP_HOST'];
		}
		if($host=='')
			return $host; //host not found, return empty and get out
		if(isset($_SERVER['SERVER_PORT'])&&$_SERVER['SERVER_PORT']!="80"&&$_SERVER['SERVER_PORT']!="443")
			$host .= ':'.$_SERVER['SERVER_PORT'];

		return $host;
	}

	public static function buildFullURL()
	{
		$base_name=basename(dirname (__DIR__));
		return self::$f->http_prefix.self::getHost().(strpos($_SERVER['SCRIPT_NAME'],$base_name)!==false?'/'.$base_name.'/':'/');
	}

	public static function requestUri()
	{
		if(isset($_SERVER['REQUEST_URI']))
			$uri=$_SERVER['REQUEST_URI'];
		else
		{
			if(isset($_SERVER['argv']))
				$uri=$_SERVER['SCRIPT_NAME'].(isset($_SERVER['argv'][0])?'?'.$_SERVER['argv'][0]:'');
			elseif(isset($_SERVER['QUERY_STRING']))
				$uri=$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
			else
				$uri=$_SERVER['SCRIPT_NAME'];
		}
		$uri='/'.ltrim($uri,'/');
		return $uri;
	}

	public static function buildSelfURL($script_name,$use_alt_urls=false,$rel_path='')
	{
		if($script_name!=''&&(isset($_SERVER['SCRIPT_NAME']))&&(strpos($_SERVER['SCRIPT_NAME'],$script_name)!==false))
			return self::$f->http_prefix.self::getHost().$_SERVER['SCRIPT_NAME'];
		elseif(isset($_SERVER['SCRIPT_URI']) && $use_alt_urls==false&&
			//these additional checks added due to wrong SCRIPT_URI when rewrite rule used (on some servers only)
			(strpos($_SERVER['SCRIPT_URI'],'.html')!==false||strpos($_SERVER['SCRIPT_URI'],'.php')!==false)
		)
		{
			if(strpos($_SERVER['SCRIPT_URI'],'.php/')!==false)
			{
				 if(self::$f->wrongUri404)
				 {
					header("HTTP/1.0 404 Not Found");
					exit;
				 }
				 return Formatter::GFSAbi($_SERVER['SCRIPT_URI'],'','.php');
			}
			else
				return $_SERVER['SCRIPT_URI'];
		}
		else
			return self::$f->http_prefix.self::getHost().dirname($_SERVER['PHP_SELF']).(dirname($_SERVER['PHP_SELF'])=='/'?'':'/'.$rel_path).$script_name; 
	}

	public static function redirect($url,$temp_redirect_on=false)
	{
		if(self::$f->httpRedirect)
			echo '<meta http-equiv="refresh" content="0;url='.$url.'">';
		else
		{
			if($temp_redirect_on)
				header("HTTP/1.0 307 Temporary redirect");
			header('Location:'.str_replace('&amp;','&',$url));
		}
	}

	public static function load_curl($url, $data)
	{
		$result=false;
		if(function_exists('curl_init'))
		{
			$curl=curl_init($url);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS, $data);
			$result=curl_exec($curl);
			curl_close($curl);
		}
		elseif(ini_get('allow_url_fopen')==true)
			$result=file_get_contents($url);
		return $result;
	}

	public static function customErrorRedirect($rel_path,$pg_id)
	{
		if(array_sum(self::$f->error_template_a)>0)
		{
			$s_sitemap=CA::getSitemap($rel_path,false,true);
			$lang=isset($s_sitemap[$pg_id]['22'])?$s_sitemap[$pg_id]['22']:'EN';
			$lang_id=array_search($lang,self::$f->inter_languages_a);

			if(isset(self::$f->error_template_a[$lang_id]) && self::$f->error_template_a[$lang_id]!='0')
			{
				$url=$s_sitemap[self::$f->error_template_a[$lang_id]][1];
				if($rel_path=='')
					$url=str_replace('../','',$url);
				self::redirect($url,false);
			}
		}
		return false;
	}

	public static function buildReturnURL($has_param=true,$append='')
	{
		$r=base64_encode(self::currentPageUrl($append));
		if($has_param)
			$r='&amp;r='.$r;
		return $r;
	}

//redirects to given path or returns false if no such path is provided
	public static function checkReturnURL($check_only=false,$get_clean=false)
	{
		if(isset($_REQUEST['r'])&&$_REQUEST['r']!='')
		{
			$r=$_REQUEST['r'];
			if($check_only&&!$get_clean)
				return $r;  //check only and there is something to return to
			$r=base64_decode($r);
			//don't return to duplicate if coming from there
			$r=preg_replace('/action=duplicate&entry_id=(\d+)$/','action=index',$r);
			if($check_only&&$get_clean)
				return $r;  //checks and gets pure returning url
			self::redirect($r);
			exit;
		}
		return false;
	}

	public static function relPathBetweenURLs($path_1,$path_2)
	{
		//calculate rel path from symlinks folder to file dest folder
		if(strpos($path_1,'innovaeditor')!==false)
		{
			$common=Formatter::longestCommonSubsequence($path_1,$path_2);
			$common=substr($common,0,strrpos($common,'/')+1);
			$path_1_part=str_replace($common,'',$path_1);
			$path_2_part=str_replace($common,'',$path_2);
		}
		else
		{
			$path_1_part=str_replace('../','',$path_1); //assuming innovaeditor is always in root
			$path_2_part=$path_2;
		}
		$path_2_part_dirs=substr_count($path_2_part,'/');
		$pref_path='';
		for($i=$path_2_part_dirs; $i>0; $i--)
			$pref_path .= '../';
		return $pref_path.$path_1_part;
	}

	public static function url()
	{
		if(isset($_SERVER['SCRIPT_URI']))
			return $_SERVER['SCRIPT_URI'];
		elseif(isset($_SERVER['SCRIPT_NAME']))
			return self::getHost().$_SERVER['SCRIPT_NAME'];

		return self::getHost().$_SERVER['PHP_SELF'];
	}

	public static function cleanURL($url,$lower=true)
	{
		$url=preg_replace("`\[.*\]`U","",$url);
		$url=preg_replace('`&(amp;)?#?[a-z0-9_]+;`i','-',$url);
		$url=htmlentities($url,ENT_COMPAT,'utf-8');
		$url=preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1",$url);
		$url=preg_replace(array("`[^a-z0-9_]`i","`[-]+`"),"-",$url);
		if($lower)
			return Formatter::strToLower(trim($url,'-'));

		return trim($url,'-');
	}

	public static function currentPageUrl($append='')
	{
		$pageURL='http';
		$request_URI=self::requestUri();
		if(isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"]=="on")
			$pageURL.="s";
		$pageURL.="://";
		$pageURL.=self::getHost().$request_URI;
		if($append!='')
		{
			if(strpos($pageURL,'?')===false)
				$pageURL.='?'.$append;
			else
				$pageURL.='&'.$append;
		}

		return $pageURL;
	}

	public static function removeURLMultiSlash($url)
	{
		return preg_replace('%([^:])([/]{2,})%','\\1/',$url);
	}

	public static function relToAbs(&$output,$pgDir)
	{
                if(self::$f->site_url!='')
		{
			$site_url=self::$f->site_url;
			if(strpos($site_url,'www.')!==false&&strpos(Linker::buildSelfURL(''),'www.')===false)
				$site_url=str_replace('www.','',$site_url);
			if($pgDir!='')
				$output=str_replace(array('href="../','src="../','action="../'),
					  array('href="'.$site_url,'src="'.$site_url,'action="'.$site_url),
					  $output);
			else
				$output=str_replace(array('href="documents/','src="innovaeditor/','src="documents/','src="images/'),
					  array('href="'.$site_url.'documents/','src="'.$site_url.'innovaeditor/','src="'.$site_url.'documents/','src="'.$site_url.'images/'),
					  $output);
		}
	}
}

class File extends FuncHolder
{
	//read/write db files functions
	public static function is_image($path,$simple=true)
	{
		if($simple)
		{
		  $allowed=array('gif','png','jpg','jpeg');
		  $ext=strtolower(pathinfo($path, PATHINFO_EXTENSION));
		  if(in_array($ext,$allowed) )
				return true;
		}
		else
		{
		  if(!file_exists($path))
				$path='../'.$path;
		  $a = getimagesize($path);
		  $image_type = $a[2];

		  if(in_array($image_type ,array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
				return true;
		}
		return false;
	}

	public static function read($filename)
	{
		$contents='';
		clearstatcache();
		if(file_exists($filename))
		{
			$fsize=filesize($filename);
			if($fsize>0)
			{
				$fp=fopen($filename,'r');
				$contents=fread($fp,$fsize);
				fclose($fp);
			}
		}
		if(version_compare(PHP_VERSION,'5.4.0','<'))
			if(get_magic_quotes_runtime())
				$contents=stripslashes($contents);
		return $contents;
	}

	public static function write($filename,$data,$mode='w')
	{
		$fp=fopen($filename,$mode);
		if($fp)
		{
			fwrite($fp,$data);
			fclose($fp);
		}
	}

	public static function readLangSet($file,$lang,$page_type,$period_list=array())
	{

		$result=array();
		if(file_exists($file))
		{
			$content=file_get_contents($file);
			if($content!==false)
			{
				$en=Formatter::GFS($content,'[EN]','[END]');
				$ln=Formatter::GFS($content,'['.$lang.']','[END]');
				$content='';

				$lines_en=explode("\n",$en);
				$count_en=count($lines_en);
				for($i=1; $i<$count_en; $i++)
				{
					$label=explode("=",trim($lines_en[$i]));
					if(!empty($label[0]))
						$default_lang_l["{$label[0]}"]=trim($label[1]);
				}

				$lines_ln=explode("\n",$ln);
				$count_ln=count($lines_ln);
				for($i=1; $i<$count_ln; $i++)
				{
					$label=explode("=",trim($lines_ln[$i]));
					if(in_array($page_type,array('blog','podcast','photoblog','calendar','guestbook')))
					{
						if(in_array($label[0],self::$f->day_names))
							$new_day_name[]=trim($label[1]);
						elseif(in_array($label[0],self::$f->month_names))
							$new_month_name[]=trim($label[1]);
						if($page_type=='calendar')
						{
							if(in_array($label[0],$period_list))
								$new_period_list[]=trim($label[1]);
							elseif(in_array($label[0],array('year','month','week')))
								$new_repeatPeriod_list[]=trim($label[1]);
						}
					}
					if(!empty($label[0]))
						$new_lang_l["{$label[0]}"]=trim(isset($label[1])?$label[1]:$label[0]);
				}
			}

			if(isset($new_lang_l))
			{
				foreach($default_lang_l as $k=> $v)
				{
					if(isset($new_lang_l[$k]))
						$default_lang_l[$k]=$new_lang_l[$k];
				}
				$result['lang_l']=$default_lang_l;
			}
			else
			{
				$result['lang_l']=$default_lang_l;
			}

			if(isset($new_day_name))
				$result['day_name']=$new_day_name;
			if(isset($new_month_name))
				$result['month_name']=$new_month_name;
			if(isset($new_period_list))
				$result['period_list']=$new_period_list;
			if(isset($new_repeatPeriod_list))
				$result['repeatPeriod_list']=$new_repeatPeriod_list;
		}
		return $result;
	}

}

class PageHandler extends FuncHolder
{
	public static function getContent($fname,$include_earea=false,&$keywords,&$title)
	{
		if(!file_exists($fname))
			return '';
		$content=File::read($fname);
		$keywords=Formatter::GFS($content,'<meta name="keywords" content="','"');
		$title=Formatter::GFS($content,'<title>','</title>');
		$content=self::getArea($content,$include_earea);
		return $content;
	}

	public static function getArea($content,$include_earea=false,$exclude_body_tag=false)
	{
		if(strpos($content,'<!--page-->')!==false)
		{
			$earea_buff='';
			if($include_earea)
			{
				while(strpos($content,'<!--%areap')!==false)
				{
					$earea_st=Formatter::GFSAbi($content,'<!--%areap','%-->');
					$earea=Formatter::GFS($content,$earea_st,'<!--areaend-->');
					$earea_buff.=$earea.' ';
					$content=str_replace($earea_st.$earea.'<!--areaend-->','',$content);
				}
			}
			$content=Formatter::GFS($content,'<!--page-->','<!--/page-->');
			$content=$earea_buff.$content;
		}
		else
		{
			$content=str_replace(array('<BODY','</BODY'),array('<body','</body'),$content);
			$pattern=Formatter::GFSAbi($content,'<body','</body>');
			if($pattern=='<body</body>')
				$pattern=Formatter::GFSAbi($content,'</head>','</body>');

			$body_start_tag=substr($pattern,0,strpos($pattern,'>')+1);
			if($exclude_body_tag)
				$content=Formatter::GFS($content,$body_start_tag,'</body>');
			else
				$content=Formatter::GFSAbi($content,$body_start_tag,'</body>');
		}
		if(!$include_earea)
			while(strpos($content,'<!--%areap')!==false)
				$content=str_replace(Formatter::GFSAbi($content,'<!--%areap','<!--areaend-->'),'',$content);

		return $content;
	}
}

class DB extends FuncHolder
{

	public static function dbInit($dbcharset,$namescharset)
	{
		if(!isset(self::$f))
		{
			global $f;
			$fileLocation = 'ezg_data/mysql.php';
			for($i = 0; $i < 5; $i++){
				if(file_exists($fileLocation))
				{
					include_once($fileLocation);
					break;
				}
				else
					$fileLocation = '../'.$fileLocation;
			}
			self::$f = $f;
		}
		if(self::$f->db!=null&&$namescharset!=self::$f->db_namescharset)
			self::$f->db->close();
		if(self::$f->db==null)
		{
			self::$f->db=new Database(self::$f->mysql_host,self::$f->mysql_username,
					self::$f->mysql_password,self::$f->mysql_dbname,
					self::$f->proj_pre,$dbcharset,
					self::$f);
			self::$f->db->connect();
			if(self::$f->db->link_id&&$namescharset!='')
			{
				self::$f->db->query('SET NAMES "'.$namescharset.'"');
				if(self::$f->mysql_setcharset)
					self::$f->db->query('SET CHARACTER SET "'.$namescharset.'"');
			}
		}
		self::$f->db_createcharset=$dbcharset;
		self::$f->db_namescharset=$namescharset;
		if(self::$f->db->link_id&&self::$f->db->errno==0)
			return self::$f->db;
		else
			return false;
	}
}

class Editor extends FuncHolder
{

	public static function getEditor($lang,$rel,$rtl,$ed_bg,&$html,&$js,$buttons_set=0,$tlang='en',$basePath='',$is_mobile=false)
	{
		$langl=isset(self::$f->innova_lang_list[$lang])?self::$f->innova_lang_list[$lang]:self::$f->innova_lang_list['english'];

		$html=str_replace(array('%RELPATH%','%BACKGROUND%','%XLANGUAGE%'),array($rel,$ed_bg,$langl),self::$f->editor_html);
		$js=str_replace(array('%EDITOR_LANGUAGE%','%RELPATH%','%XLANGUAGE%'),array($lang,$rel,$langl),self::$f->editor_js);

		if(self::$f->tiny && $lang=='en')
			$html=str_replace("plugins :","gecko_spellcheck : true,plugins :",$html);
		if(self::$f->tiny)
			$js=str_replace('language : "en",','language : "'.$tlang.'",',$js);
		else
			$html=str_replace('%EDITOR_LANGUAGE%',$lang,$html);

		$rtl_code='';
		if($rtl)
			$rtl_code=self::$f->tiny?'directionality:"rtl",':'oEdit1.btnLTR=true;oEdit1.btnRTL=true;';
		$html=str_replace('%RTL%',$rtl_code,$html);
		if($buttons_set>0) // 0 = full editor 1 = guestbook 2 = oep 3 = comments
		{
			if(self::$f->tiny)
			{
			  	 $js=str_replace('menubar:true,','menubar:false,',$js);
				 if($buttons_set==1||$buttons_set==3)
					 $js=str_replace(
							array('autolink link image','print preview media fullpage |','file_browser_callback : uploadCallBack,'),
							'',
							$js);
				if($buttons_set==3)
					$js=str_replace(array(Formatter::GFSAbi($js,"{title: 'Headers'","]},"),'table'),'',$js);
			}
			else
			{
				if($buttons_set==1||$buttons_set==3)
					$new_set='oEdit1.groups= [["group1","",["Bold","Italic", "Underline","ForeColor","BackColor","FontName","FontSize","JustifyLeft","JustifyCenter","JustifyRight","Emoticons","Line","LinkDialog"]]];';
				else
					$new_set='oEdit1.groups= [["group1","",["Bold","Italic", "Underline","ForeColor","BackColor","FontName","FontSize","JustifyLeft","JustifyCenter","JustifyRight","Line","LinkDialog","ImageDialog","YoutubeDialog"]]];';

				$html=str_replace(
					Formatter::GFSAbi($html,'oEdit1.groups = [','];'),$new_set,$html);

				if($buttons_set==1)
					 $html=str_replace(Formatter::GFSAbi($html,'oEdit1.fileBrowser="','";'),'',$html);

				$html=str_replace('</script>','oEdit1.enableLightbox=false;oEdit1.enableCssButtons=false;oEdit1.enableFlickr= false;</script>',$html);
			}
		}
		$js=str_replace('%RTL%',$rtl_code,$js);

		if(self::$f->tiny && $basePath!='')
		  $js=str_replace('selector: ".mceEditor",','selector: ".mceEditor", document_base_url: "'.$basePath.'",',$js);
		if($basePath!='')
		  $html=str_replace('var dummy;','var dummy;oEdit1.publishingPath="'.$basePath.'";',$html);

		if(!self::$f->tiny && isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'Trident/7.0; rv:11.0')!==false)
		{
		 $js.='
			<script type="text/javascript">
				function emode(){$("[id^=idContentoEdit1]").each(function(){doc=$(this)[0].contentDocument;doc.designMode="off";doc.designMode="on";});}
				$(document).ready(function(){setTimeout(function(){emode()},2000);});
			</script>
			';
		}
		if($is_mobile)
			 $html=str_replace('oEdit1.width="100%";','oEdit1.width="94%";',$html);
	}

	public static function updateLang($pl,$rel_path,&$innova_js,&$innova_def)
	{
		$l=strtolower(self::$f->names_lang_sets[$pl]);
		if(in_array($l,self::$f->innova_lang_list))
		{
			$la=(strpos($innova_js,'istoolbar.js')!==false)?$l:self::$f->innova_lang_list[$l];
			$innova_js=str_replace(Formatter::GFSAbi($innova_js,'src="'.$rel_path.'innovaeditor/scripts/language/','/editor_lang.js"'),'src="'.$rel_path.'innovaeditor/scripts/language/'.$la.'/editor_lang.js"',$innova_js);
			$innova_def=str_replace(Formatter::GFSAbi($innova_def,'assetmanager.php?lang=','&root'),'assetmanager.php?lang='.$la.'&root',$innova_def);
		}
	}

	public static function replaceClassesEdit($src)
	{
		for($i=0; $i<count(self::$f->ext_styles); $i++)
		{
			$src=preg_replace('/(<a[^>]*?)class="rvts'.(($i*8)+4).'"?/is','\\1 class="'.self::$f->ext_styles[$i].'"',$src);
			$src=str_replace('class="rvts'.($i*8).'"','class="'.self::$f->ext_styles[$i].'"',$src);

		}

		$src=str_replace(array('class="mbox"','class="multibox"'),'target="multibox"',$src);
		//Joe: Next line protects code from xss vulnerability and prepares proper content
		if(!self::$f->tiny)
			$src=htmlspecialchars($src);
		return $src;
	}

	public static function replaceClasses($src)
	{
		if(get_magic_quotes_gpc())
		{
			$src=str_replace(array("\'","&#92;'"),"'",$src);
			$src=str_replace(array('\"','&#92;"'),'"',$src);
			if(!self::$f->uni)
				$src=str_replace(array('&#8217;','&#8216;','&#8221;','&#8220;','´','`','�?','“','’'),array('&rsquo;','&lsquo;','&rdquo;','&ldquo;','&rsquo;','&lsquo;','&rdquo;','&ldquo;',"'"),$src);
		}

		for($i=0; $i<count(self::$f->ext_styles); $i++)
		{
			$src=preg_replace('/(<a[^>]*?)(class="'.preg_quote(self::$f->ext_styles[$i]).')/s','$1 class="rvts'.(($i*8)+4),$src);
			$src=str_replace('class="'.self::$f->ext_styles[$i].'"','class="rvts'.($i*8).'"',$src);
		}
		return $src;
	}
	public static function addGoogleFontsToInnova($src,$js_innova)
	{
		if(self::$f->editor=="LIVE")
		{
			$js=$matches=array();
			$fonts=join("|",self::$f->gfonts);

			if(preg_match_all('/'.$fonts.'/',$src,$matches))
			{
				$matches=array_unique($matches[0]);
				foreach($matches as $v)
					$js[]='"'.$v.'"';
			}
			if(count($js)>0)
			{
				$js_innova.='
		<script type="text/javascript">
		if(typeof oEditFonts==="undefined") var oEditFonts=new Array();';
				foreach($js as $v)
					$js_innova.='oEditFonts.push('.$v.');';
				$js_innova.='</script>';
			}
		}
		return $js_innova;
	}

	public static function fixInnovaPaths($content,$script_name,$full_script_path,$rel_path)
	{
		$full_script_path2=str_replace("/".$script_name,'',$full_script_path);
		$abs_url=($rel_path==''?$full_script_path2:substr($full_script_path2,0,strrpos($full_script_path2,'/'))).'/innovaeditor/assets/';
		$content=str_replace('="../innovaeditor/assets/','="innovaeditor/assets/',$content);
		$content=str_replace('src="innovaeditor/assets/','src="'.$abs_url,$content);
		$content=str_replace('data-thumb="innovaeditor/assets/','data-thumb="'.$abs_url,$content);
		$content=str_replace('href="innovaeditor/assets/','href="'.$abs_url,$content);
		return $content;
	}

//these 2 functions were in the innova files,extracted as single function as used more than once.
//logged user and admin params are by reference, because they are declared before and used after the functions use in the script
	public static function innovaAuth(&$logged_user,&$logged_admin,&$is_adminUser,$check_innova_access_only=false)
	{
		global $user,$db;

		if($db==NULL)
			$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));
		Session::intStart();
		$err='';
		$oep_pass_mode=(Session::isSessionSet('page_id')&&Session::isSessionSet('cur_pwd'.Session::getVar('page_id'))?true:false);
		$pass_mode=(Session::isSessionSet('page_id')&&Session::isSessionSet('admin'.Session::getVar('page_id'))?true:false);
		if(!Cookie::isAdmin())
		{
			if(!$user->userCookie())
			{
				if(!$pass_mode&&!$oep_pass_mode)
					$err=self::innovaHandleError($check_innova_access_only);
			}
			elseif(self::innovaCheckAuth($user->getUserCookie())==false)
				$err=self::innovaHandleError($check_innova_access_only);
			else
			{
				$user->mGetLoggedUser($db);
				$logged_user=$user->getUserCookie();
				$is_adminUser=$user->isAdminUser();
			}
		}
		else
			$logged_admin='admin';
		if($err!='')
			return $err;
	}

	public static function innovaHandleError($check_innova_access_only=false)
	{
		if($check_innova_access_only)
			return 'forbidden';

		echo "Not allowed!";
		exit;
	}

	public static function innovaCheckAuth($username,$user_account=null)
	{
		$auth=false;
		if($user_account==null)
			$user_account=User::getUser($username,'../../');
		if(!empty($user_account))
		{
			if($user_account['access'][0]['section']!='ALL')
			{
				foreach($user_account['access'] as $v)
				{
					if($v['access_type']==EDIT_ACCESS || $v['access_type']==EDIT_OWN_ACCESS || $v['access_type']==ADMIN_OEP_ACCESS || $v['access_type']==ADMIN_ON_PAGE)
						{
							$auth=true;
							break;
						}
				}
			}
			else
			{
				if($user_account['access'][0]['access_type']>0)
					$auth=true;
			}
		}
		return $auth;
	}

	public static function addPolldropbox_InnovaEditor(&$html,&$js)
	{
		global $db;

		if($db==NULL)
			$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));

		$array_polls=$db->fetch_all_array('
			SELECT question, qid
			FROM '.$db->pre.'poll_questions
			WHERE qid > 5000
			ORDER BY created DESC',true);
		$poll_replace = array();

		if($array_polls!==false)
		{
			if(self::$f->tiny)
			{
				if(strpos($js,'var dummy;')==false) return;
				$xcode="ed.addButton('polls', {
				type: 'listbox',text: 'P_',icon: false,
				onselect: function(e) {	ed.insertContent(this.value());$('.mce-listbox').find('span').text('P'); },
				values: [%POLL_QUESTIONS%]
				});";
				$js=str_replace(array('var dummy;',' toggle"'),
					  array($xcode,' polls toggle"'),$js);
			}
			else
			{
				if(strpos($html,'var dummy;')==false)
					return;
				$html=str_replace(array(']]];','var dummy;'),
					  array(strpos($html,'group4')?']],["group5","",["CustomTag","BRK"]]];':',"CustomTag"]]];','oEdit1.arrCustomTag = [%POLL_QUESTIONS%];'),$html);
			}

			foreach($array_polls as $v)
			{
				if(self::$f->tiny)
					 $poll_replace[] = '{text: "'.$v['question'].'", value: "{%POLL_ID('.$v['qid'].')%}"}';
				else
					 $poll_replace[] = '["'.$v['question'].'", "{%POLL_ID('.$v['qid'].')%}"]';
			}
			if(self::$f->tiny)
				$js=str_replace('%POLL_QUESTIONS%',implode(',',$poll_replace),$js);
			else
				$html=str_replace('%POLL_QUESTIONS%',implode(',',$poll_replace),$html);
		}
	}

	public static function addhtml5Player_Plugin_Editor(&$html,&$js,$rel_path,$elang,$title_dialog,$pg_id,$flag=true)
	{
		if($flag==false)
			return;
		$html5_player_plugin=$rel_path.'ezg_data/html5player_plugin.php?lang='.$elang.'&pid='.$pg_id;
		$width_dialog=800;
		$height_dialog=580;
		if(self::$f->tiny)
		{
			if(strpos($js,'var html5Player_dummy;')==false)
				return;
			$xcode="
			ed.addButton('html5Player', {
				title : '".$title_dialog."',
				image : '".$rel_path."ezg_data/html5player_tiny_icon.png"."',
				onclick : function() {
					ed.windowManager.open({
					title: '".$title_dialog."',
					url: '".$html5_player_plugin."',
					width: {$width_dialog},
					height: {$height_dialog}
					});
				}
			});
			";
			$js=str_replace(array('var html5Player_dummy;',' toggle"'),
			array($xcode,' html5Player toggle"'),$js);
		}
		else
		{
			if(strpos($html,'.arrCustomButtons=[')===false)
				return;
			$m=Formatter::GFSAbi($html,'.arrCustomButtons=[','];');
			$btns=Formatter::GFS($m,'.arrCustomButtons=[','];');
			$xcode='["html5Player","modalDialog(\''.$html5_player_plugin.'\','.$width_dialog.','.$height_dialog.',\''
				.$title_dialog.'\');", "'.$title_dialog.'", "'.$rel_path.'ezg_data/html5player_innova_icon.png"],'.$btns;
			$html=str_replace(array(']]];',$btns),array(',"html5Player"]]];',$xcode),$html);
		}
	}

	public static function addSlideshow_Plugin_Editor(&$html,&$js,$rel_path,$elang,$title_dialog,$pg_id,$flag=true)
	{
		if($flag==false)
			return;

		$slideshow_plugin_path=$rel_path.'ezg_data/slideshow_plugin.php?lang='.$elang.'&pid='.$pg_id;
		$width_dialog=800;
		$height_dialog=580;

		if(self::$f->tiny)
		{
			if(strpos($js,'var slideshow_dummy;')==false)
				return;
			$xcode="
			ed.addButton('slideshow', {
				title : '".$title_dialog."',
				image : '".$rel_path."ezg_data/slideshow_tiny_icon.png"."',
				onclick : function() {
					ed.windowManager.open({
					title: '".$title_dialog."',
					url: '".$slideshow_plugin_path."',
					width: {$width_dialog},
					height: {$height_dialog}
					});
				}
			});
			";
			$js=str_replace(array('var slideshow_dummy;',' toggle"'),
			array($xcode,' slideshow toggle"'),$js);
		}
		else
		{
			if(strpos($html,'.arrCustomButtons=[')===false)
				return;
			$m=Formatter::GFSAbi($html,'.arrCustomButtons=[','];');
			$btns=Formatter::GFS($m,'.arrCustomButtons=[','];');
			$xcode='["Slideshow","modalDialog(\''.$slideshow_plugin_path.'\','.$width_dialog.','.$height_dialog.',\''
				.$title_dialog.'\');", "'.$title_dialog.'", "'.$rel_path.'ezg_data/slideshow_innova_icon.png"],'.$btns;
			$html=str_replace(array(']]];',$btns),array(',"Slideshow"]]];',$xcode),$html);
		}
	}

	public static function addSlideshow_Plugin_contentBuilder(&$sc,&$dependencies,$rel_path,$elang,$title_dialog,$pg_id)
	{
		$slideshow_plugin_path=$rel_path.'ezg_data/slideshow_plugin.php?lang='.$elang.'&pid='.$pg_id.'&cbuilder=true';
		$width_dialog=800;
		$height_dialog=580;
		$dependencies[]='innovaeditor/scripts/common/nlslightbox/nlslightbox.css';
		$dependencies[]='innovaeditor/scripts/common/nlslightbox/dialog.js';
		$dependencies[]='innovaeditor/scripts/common/nlslightbox/nlsanimation.js';
		$dependencies[]='innovaeditor/scripts/common/nlslightbox/nlslightbox.js';
		$sc.='
			var obj_cbuilder="";
			function getSlideshowdialog(element){
				window.obj_cbuilder=element;
				modalDialog(\''.$slideshow_plugin_path.'\','.$width_dialog.','.$height_dialog.',\''.$title_dialog.'\');
			}';
	}

	public static function replaceData64image_contentBuilder($src, $rel_path, $username)
	{
		$matches=false;
		if(preg_match_all('/(<img[^>]+>)/i', $src, $matches))
		{
			$images=$matches[0];
			foreach($images as $imgTag)
			{
				if(strpos($imgTag,'src="data:image/')!==false)
				{
					$dataPart=Formatter::GFSABi($imgTag,'src="data:image/','"');
					$n=Formatter::GFS($dataPart,'src="data:image/','"');
					list($ext,)=explode(';',$n);
					list(,$img_base64)=explode(',',$n);

					$image_name=pathinfo(Formatter::GFS($imgTag,'data-name="','"'),PATHINFO_FILENAME);
					$img_data=base64_decode($img_base64);
					$dir=$rel_path.'innovaeditor/assets/'.$username.'/';
					if (!file_exists($dir))
						mkdir($dir, 0755, true);

					$file=$dir.$image_name.'.'.$ext;
					$success=file_put_contents($file,$img_data);
					if($success)
						$src=str_replace($dataPart,'src="'.$file.'"',$src);
				}
			}
		}
		return $src;
	}

	public static function getContentBuilder_scripts(&$js,&$css,&$dependencies,$is_logged=true,$front_page=false)
	{
		$dependencies[]='contentbuilder/assets/default/content.css';
		if(!$is_logged)
			return false;
		$js.='
		var nivo_box=parseInt("'.self::$f->nivo_box.'");
		var front_page='.($front_page?'1':'0').';
		$(document).ready(function(){ run_slideshows() });
		function run_slideshows()
		{
			if($(".embed-slideshow").length==0) return;
			var js_scripts="";
			$(".embed-slideshow").each(function(i,v){
				var uniqueId=IDGenerator();
				var builded_ss_data = cbuilder_slideshow_structure(uniqueId, $(this).attr("data-slideshow"), $(this));
				$(this).html("<!--ss_content"+uniqueId+"-->"+builded_ss_data["ss_html"]+"<!--end_ss_content"+uniqueId+"-->");
				var data_slideshow_enc=$(this).attr("data-slideshow");
				var data_slideshow_dec=$.parseJSON(data_slideshow_enc);
				data_slideshow_dec["ss_sid"]=uniqueId;
				data_slideshow_enc=JSON.stringify(data_slideshow_dec);
				$(this).attr("data-slideshow",data_slideshow_enc);
				js_scripts+=builded_ss_data["ss_js"];
			});
			cbuilder_append_ss_js(js_scripts,document);
		}
		function save_cb(id,options)
		{
			var frm=$(".cb_form_"+id);
			if(frm.length==0)
				return false;
			var act=frm.attr("action"),xdata=$("#contentarea"+id).data("contentbuilder").html();
			'.(self::$f->dt64?'xdata = unescape(xdata.replace(/(\r\n|\n|\r)/gm,""));xdata = btoa(unescape(encodeURIComponent( xdata )));':'').'
			var paramObj = {};
			$.each(frm.serializeArray(), function(_, kv) {
				if (paramObj.hasOwnProperty(kv.name)) {
					paramObj[kv.name] = $.makeArray(paramObj[kv.name]);
					paramObj[kv.name].push(kv.value);
				}
				else
					paramObj[kv.name] = kv.value;
			});
			paramObj["dt'.(self::$f->dt64?'64':'').'"] = xdata;
			if(typeof options !== "undefined")
				paramObj = $.extend({}, options, paramObj);
			$.post(act,paramObj,function(r) {
				if(paramObj.save_simple!=undefined)
					location.reload();
				save_btn=frm.find(".save_button");
				if(save_btn.length==0)
					save_btn=$(".save_button");
				save_btn.addClass("saved");
				if(front_page){ //front pages only with edit content functionality
					$("#post_init_content_"+id).html("<div class=\'containerCB\'>"+$("#contentarea"+id).data("contentbuilder").html()+"</div>").show();
					$("#edit_post_"+id).hide();
					run_slideshows();
				}
				hide_contentbuilder();
			});
			return false;
		}
		function init_contentbuilder(id,rel_path)
		{
			var edit_button=$(".edit_inline[rel=\'"+id+"\']");
			var rel_path=rel_path==""?"":"../";
			$("#contentarea"+id).contentbuilder({
				pathPrefix: rel_path,
				enableZoom: false,
				snippetFile: rel_path+"contentbuilder/assets/'.(self::$f->cBuilderFull?'minimalist':'default').'/snippets.html",
				onRender: function () {
					frm=$(".cb_form_"+id);
					save_btn=frm.find(".save_button");
					if(save_btn.length==0)
						save_btn=$(".save_button");
					save_btn.removeClass("saved");
				}
			});
			if(edit_button.length)
				edit_button.click(function(){
					$("#divCb").show();
					$("#contentarea"+id).css("outline","rgba(228, 156, 90, 0.5) solid 1px");
				});
			hide_contentbuilder();
		}
		function hide_contentbuilder()
		{
			if(front_page&&$("input[name=\'save_simple\']").length==0&&$("#divCb").is(":visible")){ //front pages with no direct write post macro
				$("#divCb").hide();
				$(".containerCB").css("outline","none");
			}
		}';
		$css.='.save_button,.close_button{padding:7px;margin: 10px 0;display:inline-block;background:#d5d5d5;border-radius:5px;cursor:pointer;border:0;}
			.save_button.saved{background:#b0ce01;}';
		$dependencies[]='jquery-ui.css';
		$dependencies[]='jquery-ui.min.js';
		$dependencies[]='contentbuilder/scripts/contentbuilder.css';
		$dependencies[]='contentbuilder/scripts/contentbuilder.js';
		$dependencies[]='contentbuilder/scripts/contentbuilder_customfunc.js';
		$dependencies[]='extimages/scripts/'.(self::$f->nivo_box?'nivo-lightbox':'fancybox').'.js';
		$dependencies[]='extimages/scripts/'.(self::$f->nivo_box?'nivo-lightbox':'fancybox').'.css';
		$dependencies[]='extimages/scripts/slideshow2.js';
		$dependencies[]='extimages/scripts/slideshow2.css';
	}

	public static function getContentBuilder_js($rel_path,$id)
	{
		return '<script type="text/javascript">
			$(document).ready(function ($) {
				init_contentbuilder(\''.$id.'\',\''.$rel_path.'\');
			});
		</script>';
	}
}

//used when data is generated in a file or on the display
class output_generator extends FuncHolder
{

	public static function generate_pdf($html,$tmpFile,$outFile,$rel_path,$ascode=true,$paper='letter',$orientation='portrait')
	{
		if(!self::$f->url_fopen)
			return '';
		$html=str_replace('<head>','<head><meta http-equiv="content-type" content="text/html; charset='.self::$f->db_charset.'">',$html);
		//$html=str_replace($rel_path.'innovaeditor/assets/','',$html);  comment beacuse will break all images added with editors
		File::write($rel_path.'innovaeditor/assets/'.$tmpFile,$html);
		$url='http://miro.image-line.com/dompdf/dompdf.php?input_file='.self::$f->site_url.'/innovaeditor/assets/'.$tmpFile.'&paper='.$paper.'&orientation='.$orientation.'&output_file='.$outFile;
		return $ascode?self::file_get_url_contents($url):$url;
	}

	public static function file_get_url_contents($url) {
		if (preg_match('/^([a-z]+):\/\/([a-z0-9-.]+)(\/.*$)/i', $url, $matches)) {
			$protocol = strtolower($matches[1]);
			$host = $matches[2];
			$path = $matches[3];
		} else {
			// Bad url-format
			return FALSE;
		}

		if ($protocol == "http") {
			$socket = fsockopen($host, 80);
		} else {
			// Bad protocol
			return FALSE;
		}

		if (!$socket) {
			// Error creating socket
			return FALSE;
		}

		$request = "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n";
		$len_written = fwrite($socket, $request);

		if ($len_written === FALSE || $len_written != strlen($request)) {
			// Error sending request
			return FALSE;
		}

		$response = "";
		while (!feof($socket) && ($buf = fread($socket, 4096)) !== FALSE) {
			$response .= $buf;
		}

		if ($buf === FALSE) {
			// Error reading response
			return FALSE;
		}

		$end_of_header = strpos($response, "\r\n\r\n");
		return substr($response, $end_of_header + 4);
	}

	public static function timelineData($startdate,$title,$text,$media,$data)
	{
		$t=array();
		$t['timeline']['headline']=$title;
		$t['timeline']['type']='default';
		$t['timeline']['startDate']=$startdate;
		$t['timeline']['text']=$text;
		$t['timeline']['asset']=array('media'=>$media,'credit'=>'','caption'=>'');
		$t['timeline']['date']=array();
		foreach($data as $v)
		{
			$dt=array();
			$dt['startDate']=$v['date'];
			$dt['headline']=$v['title'];
			$dt['text']=$v['text'];
			$dt['asset']=array("media"=>$v['media'],"credit"=>$v['credit'],"caption"=>$v['caption']);
			$t['timeline']['date'][]=$dt;
		}

		echo json_encode($t);
		return;
	}

	public static function showTimeline($rel_path,$script_path,$action,$write=false,$init_zoom=0,
			  $lang='en',$timeline_reversed=false,$thumb_size=60,$c_sbars_on_thumbs=false,$height='100%',
			  $inline=0,$hide_container=0)
	{
//activate custom slidebars if custom slidebars on thumbs is active
		$head='
<meta http-equiv="content-type"  content="text/html; charset=UTF-8">
<title>Timeline</title>
<link rel="stylesheet" href="'.$rel_path.'ezg_data/timeline/timeline.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/'.self::$f->jquery_ver.'/jquery.min.js"></script>
<style>
#storyjs{margin-top:13px;}
</style>';

		$body='
			 <div id="timeline"></div>
<script type="text/javascript" src="'.$rel_path.'ezg_data/timeline/timeline-min.js"></script>
<script type="text/javascript" src="'.$rel_path.'ezg_data/timeline/storyjs-embed.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript" src="'.$rel_path.'ezg_data/timeline/mousewheel.min.js"></script>
<script type="text/javascript" src="'.$rel_path.'ezg_data/timeline/mCustomScrollbar.js"></script>'
.($thumb_size!=60?'
<style>
#timeline .thumb{max-width:'.($thumb_size+40).'px;cursor:pointer;}
.thumb_mask{height:'.$thumb_size.'px;width:'.$thumb_size.'px;}
</style>':'').'
<script type="text/javascript">
	$(document).ready(function() {
		createStoryJS({
			type:		"timeline",
			width:		"100%",
			height:		"'.$height.'",
			source:		"'.$script_path.$action.'",
			embed_id:	"timeline",
			'.($timeline_reversed?'':'start_at_end: true,').'
			'.($hide_container?'hide_container: true,':'').'
			start_zoom_adjust: '.$init_zoom.',
			lang: "'.$lang.'",
			debug:false
		});
	});'
	.($write?Builder::getDirectEditJS('text',$script_path):'').
	'
	function handleth(th){
		im=$(th).closest(".content").find(".media-container img");$(im).attr("alt",$(th).attr("title"));
		if(!$(im).hasClass("l_on")) {
			$(im).addClass("l_on").load(function(){
				$(".view-loader").remove();
				$(this).parent().siblings(".caption").html(this.alt);
				} );
		}
		$(th).closest(".content").find("div.media-image").append(\'<div class="view-loader"></div>\');
		$(".view-loader").css({"top":($(im).height()/2)-30,"left":($(im).width()/2)-30});
		$(im).attr("src",$(th).attr("rel"));
	};
	function handleEn(th){
		var $evId= $(th).attr("rel");
		var $evHolder= $("#ev_"+$evId);
		$evHolder.addClass("view-loader");
		$evHolder.show();
		if($evHolder.text()== ""){
			$evHolder.html("<p><span class=\'rvts8\'>Loading ...</span></p>");
			$currPage= window.location.toString().replace(window.location.search,"");
			var jqxhr= $.get($currPage+"?entry_id="+$evId, function (data) {
				$evHolder.html($(data).find(".post_content"));
			});
		}
		$evHolder.removeClass("view-loader");
	};
	function showFirstEn(th){
		return false; //function ignored
		$(th).closest(".container").children("div[id^=\'ev_\']").hide();
		$(th).closest(".container").children("p").show();
	};
	function isScrolledIntoView(elem){
		var docViewTop= $(window).scrollTop();
		var docViewBottom= docViewTop + $(window).height();
		var elemTop= $(elem).offset().top;
		var elemBottom= elemTop + $(elem).height();
		return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	};
	function scroller(){
		if(console) console.log("scroller launched");
		$(".slider-item").scroll(function(){
			var slider= this;
			detectForHandling(slider);
		});
		detectForHandling(null);
	};
	function detectForHandling(elem){
		$elem= elem== null ? '.($c_sbars_on_thumbs?'$(".slider-item").find(".thumb_con").attr("style","height:350px;")':'$(".slider-item")').': $(elem);
		$elem.children().find("span.entry").each(function(){
			if(isScrolledIntoView(this)) {handleEn(this);}
		});
		//if no media used, set some icon in the timeline
		$(".flag-content").each(function() {
			if($(this).children("div[class*=\'thumbnail\']").size()==0)
				$(this).prepend("<div class=\'thumbnail thumb-plaintext\'></div>");
		});
		if(elem== null){
			$elem.each(function() {
				if(!$(this).hasClass("mCustomScrollbar") && $(this).children().size() > 0){
					var slider= this;
					$(this).mCustomScrollbar({
						advanced:{updateOnBrowserResize:true, updateOnContentResize:true, autoExpandHorizontalScroll:false },
						callbacks:{onScroll:function(){}, onTotalScroll:function(){	detectForHandling(slider);}, onTotalScrollOffset:0}
					});
					VMM.fireEvent(window,"resize");
				}
			});
		}
	};
	VMM.bindEvent(VMM,scroller,"EZGBUILDSLIDE");
</script>';

		$doc='<!DOCTYPE html>
<html>
	<head>'.$head.'</head>
	<body>'.$body.'</body>
</html>';
		if($inline)
			return $body;
		echo $doc;
	}

	public static function printEntry($btn_id,$rel_path,$output,$template,$use_page_bg=true,$css='')
	{
		$print_html='<a id="'.$btn_id.'" href="javascript:void(0);" style="padding:2px;">'.Builder::printImgHtml($rel_path).'</a>'
			.($use_page_bg?'<div id="xm1" style="float:none;width:970px;"><div id="xm2">'.$output.'</div></div>':$output);
		$print_js='
<script type="text/javascript">
	$(document).ready(function(){$("link[media=\'print\']").remove();$("#'.$btn_id.'").click(function(){$(this).hide();window.print();$(this).show();});});
</script>
';

		$body_part=Formatter::GFSAbi($template,'<body','</body>');
		$template=str_replace($body_part,'<body class="print_preview" style="background:transparent">'.$print_html.'</body>',$template);
		$template=str_replace('<!--endscripts-->',$css.$print_js.'<!--endscripts-->',$template);
		$template=str_replace(array('<script type="text/javascript" src="documents/script.js"></script>',
			'<script type="text/javascript" src="../documents/script.js"></script>'),'',$template);
		print $template;
		exit;
	}

	public static function sendFileHeaders($fname,$c_type='application/octet-stream')
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: $c_type");
		header("Content-Disposition: attachment; filename=\"".$fname."\";");
		header("Content-Transfer-Encoding: binary");
	}

	public static function downloadFile($path,$new_filename='')
	{
		define('F_STREAM_BUFFER',4096);
		define('F_STREAM_TIMEOUT',86400);
		define('F_USE_OB',false);

		$AllowedTypes = "|gif|jpg|jpeg|png|mp3|mp4|swf|asf|avi|mpg|mpeg|wav|wma|mid|wmw|mov|ram|bmp|pdf|zip|rar|xml|doc|docx|flv|xls|xlsx|xlsb|ppt|pptx|dwg|gpx|";

		$ext=pathinfo($path,PATHINFO_EXTENSION);
		if((strpos($AllowedTypes,'|'.$ext.'|')===false) || (substr_count(str_replace('..','',$path),'.')!=1) || strpos($path,'ezg_data')!==false)
			die('ERROR: Illegal file format');

		$filesize=filesize($path);
		$filename=basename($path);
		if(empty($new_filename))
			$new_filename=$filename;

		$file=@fopen($path,'r') or die("can't open file");
		$sm=ini_get('safe_mode');
		if(!$sm&&function_exists('set_time_limit')&&strpos(ini_get('disable_functions'),'set_time_limit')===false)
			set_time_limit(F_STREAM_TIMEOUT);

		$partialContent=false;
		if(isset($_SERVER['HTTP_RANGE']))
		{
			$rangeHeader=explode('-',substr($_SERVER['HTTP_RANGE'],strlen('bytes=')));
			if($rangeHeader[0]>0)
			{
				$posStart=intval($rangeHeader[0]);
				$partialContent=true;
			}
			else
				$posStart=0;
			if($rangeHeader[1]>0)
			{
				$posEnd=intval($rangeHeader[1]);
				$partialContent=true;
			}
			else
				$posEnd=$filesize-1;
		}
		else
		{
			$posStart=0;
			$posEnd=$filesize-1;
		}

		$ext=end(explode(".",strtolower($new_filename)));
		$mime=Detector::getMime($ext);
		header("Content-type: ".$mime);
		header('Content-Disposition: attachment; filename="'.$new_filename.'"');
		header("Content-Length: ".($posEnd-$posStart+1));
		header('Date: '.gmdate('D, d M Y H:i:s \G\M\T',time()));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T',filemtime($path)));
		header('Accept-Ranges: bytes');
		header("Cache-Control: post-check=0, pre-check=0",false);
		header("Pragma: no-cache");
		header("Expires: ".gmdate("D, d M Y H:i:s \G\M\T",mktime(date("H")+2,date("i"),date("s"),date("m"),date("d"),date("Y"))));
		if($partialContent)
		{
			header("HTTP/1.0 206 Partial Content");
			header("Status: 206 Partial Content");
			header("Content-Range: bytes ".$posStart."-".$posEnd."/".$filesize);
		}
		if($sm)
			fpassthru($file);
		else
		{
			fseek($file,$posStart);
			if(F_USE_OB)
				ob_start();
			while(($posStart+F_STREAM_BUFFER<$posEnd)&&(connection_status()==0))
			{
				echo fread($file,F_STREAM_BUFFER);
				if(F_USE_OB)
					ob_flush();
				flush();
				$posStart+=F_STREAM_BUFFER;
			}
			if(connection_status()==0)
				echo fread($file,$posEnd-$posStart+1);
			if(F_USE_OB)
				ob_end_flush();
		}
		fclose($file);
	}

}

class Gmaps extends FuncHolder
{
	public static function resolve_location_visual($v,$size='150x120',$nolinks=0,$label='')
	{
		$loc=explode('|',$v);
		$l='';
		if(count($loc)<2)
			$loc[1]=$loc[0];
		$lab=$label!=''?$label.': <br>':'';

		if(strpos($v,'http')!==false)
		{
			$location=$lab.$loc[1];
			$l=$loc[1];
		}
		elseif($nolinks)
			$location=($v!=""? $lab.$v:'');
		else
		{
			$location=$v==""?'':$lab.$loc[1];
			$l=$loc[1];
		}

		if($l!='')
			$location='
				 <div class="gmaps_location thumbnail rvps1">
					 <a href="http://maps.google.com/maps?q='.$l.'&hl=en&z=15" class="thumbnail_map" rel="nofollow" target="_blank">
						  <img style="margin: 3px auto;" src="http://maps.google.com/maps/api/staticmap?zoom=14&size='.$size.'&maptype=roadmap&markers=color:blue|label: |'.$l.'&sensor=false"/>
					 </a><br>
					 <img class="thumbnail_img" style="margin: 3px auto;" src="http://maps.googleapis.com/maps/api/streetview?size='.$size.'&location='.$l.'&fov=90&sensor=false"/>'
	 			   .$location.'<br>
				</div>';

		return $location;
	}
}

class Slideshow extends FuncHolder
{
	public $lang_str=array();
	public $nivoSlider_settings=array();
	public $fancybox_settings=array();
	public $nivoLightbox_settings=array();
	public $slideshow2_settings=array();
	public $slideshow_types=array();
	public $url_target_types=array();
	public $html5player_settings=array();

	public function __construct()
	{
		parent::__construct();
		$this->html5player_settings=array(
			'type'=>array('audio','video'),
			'volume'=>array('min'=>0,'max'=>100,'value'=>30,'step'=>1),
			'current'=>array('min'=>1,'max'=>200,'value'=>1,'step'=>1),
			'loop'=>array('auto','none'),
			'width'=>array('min'=>390,'max'=>2000,'value'=>390,'step'=>5)
		);
		$this->nivoSlider_settings=array(
				'effect'=>array("sliceDownRight","sliceDownLeft","sliceUpRight","sliceUpLeft","sliceUpDown","sliceUpDownLeft","fold","fade",
				            "boxRandom","boxRain","boxRainReverse","boxRainGrow","boxRainGrowReverse","slideInRight","slideInLeft"),
				'slices'=>array('min'=>2,'max'=>30,'value'=>15,'step'=>1),
				'boxCols'=>array('min'=>2,'max'=>20,'value'=>8,'step'=>1),
				'boxRows'=>array('min'=>2,'max'=>10,'value'=>4,'step'=>1),
				'animSpeed'=>array('min'=>0.1,'max'=>20,'value'=>0.8,'step'=>0.1),
				'pauseTime'=>array('min'=>0.1,'max'=>20,'value'=>4,'step'=>0.1)
		);
		$this->fancybox_settings=array(
				'transitionIn'=>array("elastic","fade","none"),
				'transitionOut'=>array("elastic","fade","none"),
				'speedIn'=>array('min'=>0.1,'max'=>20,'value'=>0.3,'step'=>0.1),
				'speedOut'=>array('min'=>0.1,'max'=>20,'value'=>0.3,'step'=>0.1),
				'slideshowDelay'=>array('min'=>0.1,'max'=>20,'value'=>0.3,'step'=>0.1),
				'changeFade'=>array('fast','slow')
				);
		$this->nivoLightbox_settings=array('effect'=>array("fade","fadeScale","slideLeft","slideRight","slideUp","slideDown","fall"));
		$this->slideshow2_settings=array(
		'slideShowSpeed'=>array('min'=>0.1,'max'=>20,'value'=>5,'step'=>0.1),
		'swipeSpeed'=>array('min'=>0.1,'max'=>3,'value'=>0.5,'step'=>0.1),
		'autoRun'=>array("auto","none"),
		'useNavigation'=>array("auto","none"),
		'useCaption'=>array("auto","none")
		);
		$this->slideshow_types=array("slideshow","slideshow thumbnails","multibox","multibox single","slideshow II");
		$this->url_target_types=array("target"=>array("blank","self"));
	}

	public static function updateSlideshowDependencies($src,&$dependencies)
	{
		if(strpos($src,'.mboxp_')!==false)
		{
			$dependencies[]='extimages/scripts/'.(self::$f->nivo_box?'nivo-lightbox':'fancybox').'.css';
			$dependencies[]='extimages/scripts/'.(self::$f->nivo_box?'nivo-lightbox':'fancybox').'.js';
		}
		if(strpos($src,'SlideShow')!== false){
			$dependencies[]='extimages/scripts/slideshow2.css';
			$dependencies[]='extimages/scripts/slideshow2.js';
		}
		if(strpos($src,'class="fotorama"')!== false){
			$dependencies[]='<script src="http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.js"></script>';
			$dependencies[]='http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.css';
		}
	}

	public function replaceSlideshowMacro(&$src,$relpath,&$js,&$css,&$dependencies,$global_view=false)
	{
		global $db;

		$tb_a=$db->get_tables('slideshow');
		if(empty($tb_a))
			return;
		while(strpos($src,'{%SLIDESHOW_ID(')!==false)
		{
			$m=Formatter::GFSAbi($src,'{%SLIDESHOW_ID(',')%}');
			$sid=intval(Formatter::GFS($m,'{%SLIDESHOW_ID(',')%}'));
			$data=$db->fetch_all_array('
			    SELECT *
			    FROM '.$db->pre.'slideshow_settings as s
			    LEFT JOIN '.$db->pre.'slideshow_images as i ON s.id=i.sid
			    WHERE s.id='.$sid.($global_view?' AND s.global_view=1':'').'
			    ORDER BY i.id ASC');
			if(!empty($data))
			{
				$stype=$data[0]['type'];
				$scode=$this->parse_slideshow($relpath,$data,true,$src,$js,$css,$stype,$sid,true);
				$src=str_replace($m,$scode,$src);
			}
			else
				$src=str_replace($m,'',$src);
		}

		$this->updateSlideshowDependencies($js,$dependencies);
	}

	public function replaceSlideshow_contentBuilder(&$src,$relpath,&$js,&$css,&$dependencies)
	{
		if(preg_match_all('/<!--cbuilder_slideshow-->(.*)<!--end_cbuilder_slideshow-->/sU', $src, $matches))
		{
			$slideshows=$matches[0];
			foreach($slideshows as $ss_data)
			{
				$data_slideshow_enc=str_replace('&quot;','"',Formatter::GFS($ss_data,'data-slideshow="','}"')).'}';

				$data_slideshow_dec=json_decode($data_slideshow_enc,true);
				if($data_slideshow_dec!=null&&!empty($data_slideshow_dec))
				{
					$ss_images=explode('|',$data_slideshow_dec['ss_images']);
					$ss_titles=explode('|',$data_slideshow_dec['ss_titles']);
					$ss_captions=explode('|',$data_slideshow_dec['ss_captions']);
					$ss_hrefs=explode('|',$data_slideshow_dec['ss_hrefs']);
					$ss_options=preg_replace("/[^0-9,|]/i",'',$data_slideshow_dec['ss_options']);
					$ss_id=$data_slideshow_dec['ss_sid'];
					$ss_type=$data_slideshow_dec['ss_type'];
					$data=array();
					$count_images=count($ss_images);
					if($count_images>0&&$count_images==count($ss_titles)&&$count_images==count($ss_captions)&&$count_images==count($ss_hrefs))
					{
						foreach($ss_images as $key=>$img_url){
							$data[$key]=array(
								'settings'=>$ss_options,
								'url'=>$img_url,
								'title'=>$ss_titles[$key],
								'caption'=>$ss_captions[$key],
								'href'=>$ss_hrefs[$key],
								'url_target'=>isset($data_slideshow_dec['ss_targets'])?$data_slideshow_dec['ss_targets']:'',
								'sid'=>$ss_id,
								'type'=>$ss_type
							);
						}
						$slides=$this->parse_slideshow($relpath,$data,false,$src,$js,$css,$ss_type,$ss_id,false,true);
						$ss_content=Formatter::GFS($ss_data,'<!--ss_content'.$ss_id.'-->','<!--end_ss_content'.$ss_id.'-->');
						$src=str_replace($ss_content,$slides,$src);
					}
				}
			}
		}
		$this->updateSlideshowDependencies($js,$dependencies);
	}

	public function parse_slideshow($path,$data,$nav,&$page,&$js,&$css,$params=0,$id='x',$sh_plugin=false,$cbuilder=false)
	{
		$type=is_array($params)?$params[0]:$params;
		if($type==3)
			$scode=count($data)>0?$this->buildMboxSingleSlideshow($data,$js,$id,$sh_plugin,$path,$cbuilder):'';
		elseif($type==2)
			$scode=count($data)>0?$this->buildMboxSlideshow($data,$js,$css,$id,$sh_plugin,$path,$cbuilder):'';
		elseif($type==4)
			$scode=count($data)>0?$this->buildSlideshow2($data,$js,$id,$path,$sh_plugin,$cbuilder):'';
		elseif($type==5)
			$scode=count($data)>0?$this->buildFotorama($data,$id,$params):'';
		else
			$scode=count($data)>0?$this->buildSlideshow($path,$data,$nav,$type,$js,$css,$id,$sh_plugin,$cbuilder):'';
		if($sh_plugin||$cbuilder)
			return $scode;
		else
			$page=str_replace('%SLIDESHOW%',$scode,$page);
	}

	protected function buildFotorama($data,$id,$params)
	{
		$slides='';
		$lazyLoad=$titles=1;

		if(is_array($params))
		{
			if(isset($params[1]))
					$lazyLoad=intval($params[1]);
			if(isset($params[2]))
					$titles=intval($params[2]);
		}


		foreach($data as $v)
		{
			$dc=$titles?' data-caption="'.$v['title'].'"':'';
			$slides.=
						$lazyLoad?
							'<a href="'.$v['im_url'].'" title="'.$v['title'].'"'.$dc.'></a>':
							'<img src="'.$v['im_url'].'" title="'.$v['title'].'" alt="'.$v['title'].'"'.$dc.'>';
		}

		$slideshow='<div id="'.$id.'" class="fotorama" data-nav="thumbs" data-arrows="true" data-autoplay="true" data-width="100%">'
						.$slides.'</div>';

		return $slideshow;
	}

	protected function buildSlideshow2($data,&$js,$id,$path,$sh_plugin,$cbuilder)
	{
		$slides='';
		$id=$sh_plugin?(1000000+$id):($cbuilder?$id:(2000000+$id));
		$js_structore='var slides'.$id.'=new Array();';
		$thumb_structure='<tr>';
		$maxh=$maxw=600;
		$str_settings=$this->buildSlideshow_settings($data, $this->slideshow2_settings);
		$arr_str=explode(", ",$str_settings);
		$arr_r=$all_height=$all_width=array();
		foreach($arr_str as $value){
			if($value!=''){
				$arr=explode(":",$value);
				$arr_r[$arr[0]]=str_replace('"','',$arr[1]);
			}
		}
		foreach($data as $k=>$v)
		{
			$title=$img_url=$thumb_url='';
			$this->resolveImage($v,$path,$img_url,$thumb_url,$title);
			$title=Formatter::GFS($title,'title="','"');
			$caption =(isset($arr_r["useCaption"])&&$arr_r["useCaption"]=="auto")?($sh_plugin||$cbuilder?$v['caption']:$title):'';

			$image_info=@getimagesize($img_url);
			list($width,$height)=is_array($image_info)?$image_info:array(300,300);
			$all_height[$k]=$width;
			$all_width[$k]=$height;

			$thumb_structure.='
				<td>
					<div>
						<img class="s_'.$id.'_thumb tid_'.$k.' slideshow2_old_imgs" onclick="openCI(slides'.$id.'_obj,'.$k.','.$id.')" style="border-radius:3px;" alt="" src="'.$thumb_url.'" />
					</div>
				</td>';

			$js_structore.='slides'.$id.'['.$k.']= new Slide("'.$img_url.'",'.$height.','.$width.',"'.$title.'","'.$caption.'");';
		}

		$maxh=!empty($all_height)?max($all_height):$maxh;
		$maxw=!empty($all_width)?max($all_width):$maxw;
		$thumb_structure.='</tr>';
		$js_structore.='slides'.$id.'_options={'.$str_settings.'sl:5};';
		$js_structore.='$(document).ready(function(){ slides'.$id.'_obj = new SlideShow(slides'.$id.','.$maxw.','.$maxh.','.$id.',0,slides'.$id.'_options);';
		$auto_run='setTimeout("RunShow(slides'.$id.'_obj,'.$id.')", slides'.$id.'_options.slideShowSpeed);';
		$js_structore.=(isset($arr_r["autoRun"])&&$arr_r["autoRun"]=="auto")?$auto_run:'';
		$js_structore.='});';

		$table_structure='<table style="margin:0 auto;">
		<tr><td colspan="4"><table cellpadding="0" cellspacing="0">
		<tr><td colspan="2"><div class="rvps0"><img style="border-radius:6px;" alt="" name="mainview'.$id.'" id="arunmainview'.$id.'" src="" class="slideshow2_old_imgs"></div></td></tr>';
		$navigation='<tr><td><div class="rvps0"><a href="javascript:void Prev(slides'.$id.'_obj,'.$id.');"><img alt="" src=""></a>
		<a href="javascript:void Next(slides'.$id.'_obj,'.$id.');"><img alt="" src=""></a>
		<a href="javascript:void RunShow(slides'.$id.'_obj,'.$id.');"><img alt="" name="startstop'.$id.'" src=""></a>
		</div></td><td><div class="rvps0"><span id="captionDiv_'.$id.'"></span></div></td></tr>';

		$table_structure.=(isset($arr_r["useNavigation"])&&$arr_r["useNavigation"]=="auto")?$navigation:'';

		$table_structure.='</table></td></tr>'.$thumb_structure.'</table>';
		$slides=$table_structure;
		$js.=$js_structore;
		return $slides;
	}

	protected function buildMboxSingleSlideshow($data,&$js,$id,$sh_plugin,$path,$cbuilder)
	{
		$slides='';
		foreach($data as $k=>$v)
		{
			$title=$url=$thumb='';
			$this->resolveImage($v,$path,$url,$thumb,$title);
			if($cbuilder&&$k==0)
			{
				$image_info=@getimagesize($url);
				$first_image_width=is_array($image_info)?$image_info[0]:300;
			}
			$slides.='<a href="'.$url.'" '.$title.' class="'.($sh_plugin?'':'mbox ').'dummy mbox'.($sh_plugin||$cbuilder?'p_':'').$id.'" rel="lightbox[LB'.($sh_plugin||$cbuilder?'p_':'').$id.'],noDesc" '.($k>0?'style="display:none"':'').'>'.
							($k==0?'<img alt="" src="'.($cbuilder?$url:$thumb).'" '.($cbuilder?'style="width:'.$first_image_width.'px; border-radius:'.($first_image_width/3).'px;"':'').'>':'').'
				</a>';
		}
		if($cbuilder)
			$slides='<div class="thumb_mask">'.$slides.'</div>';
		if($sh_plugin||$cbuilder)
			$js.=$this->build_plugin_js_mbox($data,$id);

		return $slides;
	}

	protected function resolveImage($v,$path,&$url,&$thumb,&$title)
	{
		if(is_array($v))
		{
			$title='title="'.$v['title'].'"';
			if(isset($v['im_url']))
				$url=$v['im_url'];
			else
				$url=strpos($v['url'],'innovaeditor')!==false?
					substr_replace($v['url'],$path,0,strpos($v['url'],'innovaeditor')):
					substr_replace($v['url'],$path,0,strpos($v['url'],'contentbuilder'));
		}
		else
			$url=$v;

		$thumb=is_array($v) && isset($v['tmb_url'])?
							substr_replace($v['tmb_url'],$path,0,strpos($v['tmb_url'],'innovaeditor'))
							:(strpos($url,'innovaeditor')!==false?Formatter::str_lreplace('/','/thumbs/',$url):$url);
	}

	public function replace_html5playerMacro(&$src,$relpath,&$js,&$css,&$dependencies)
	{
		global $db;

		$tb_a=$db->get_tables('html5player');
		if(empty($tb_a))
			return;
		$is_music_meta=false;
		while(strpos($src,'{%HTML5PLAYER_ID(')!==false)
		{
			$m=Formatter::GFSAbi($src,'{%HTML5PLAYER_ID(',')%}');
			$player_id=intval(Formatter::GFS($m,'{%HTML5PLAYER_ID(',')%}'));
			$data=$db->fetch_all_array('
			    SELECT *
			    FROM '.$db->pre.'html5player_settings as s
			    LEFT JOIN '.$db->pre.'html5player_tracks as i ON s.id=i.player_id
			    WHERE s.id='.$player_id.'
			    ORDER BY i.id ASC');
			if(!empty($data))
			{
				$scode=$this->build_html5player($relpath,$data,true,$src,$js,$css,$player_id,$is_music_meta);
				$src=str_replace($m,$scode,$src);
			}
			else
				$src=str_replace($m,'',$src);
		}
		if(strpos($js,'.simpleplayer({')!==false)
		{
			$dependencies[]='ezg_data/simple_player.css';
			if($is_music_meta)
				$dependencies[]='ezg_data/musicmetadata.js';
		}
	}

	public function build_html5player($path,$data,$nav,&$page,&$js,&$css,$params=0,&$is_music_meta)
	{
		$str='';
		if(!empty($data))
		{
			$str_settings=$this->buildSlideshow_settings($data, $this->html5player_settings);
			$is_video_type=strpos($str_settings,'video')!==false;
			if($is_video_type)
				$str='<div class="simple_player video" id="simple_player_'.$params.'">'.
				'<div class="video-holder">'.
					'<video preload="metadata" tabindex="0" src="'.$data[0]['track_url'].'">'.
						'<source src="'.$data[0]['track_url'].'">'.
					'</video>'.
				'</div>'.
				'<ul>';
			else $str='<div class="simple_player" id="simple_player_'.$params.'">'.
				'<audio preload="metadata" tabindex="0" controls="" src="'.$data[0]['track_url'].'">'.
					'<source src="'.$data[0]['track_url'].'">'.
				'</audio>'.
				'<ul>';
			$li='';
			$count_data=count($data);
			$count_images=0;
			foreach($data as $k=>$v)
			{
				$track_url=$thumb_url='';
				$this->resolve_html5playerImage($v,$path,$track_url,$thumb_url);
				if($thumb_url!='')
					$count_images++;
				$li.=
				'<li>'.
					($is_video_type?'<div class="video_img" style="background-image:url('.$thumb_url.');"></div>':'').
					'<span>'.($k+1).'. </span>'.
					'<div class="sp_track_info sp_marquee_parent">'.
						'<a href="'.$track_url.'">'.$v['title'].'</a>'.
					'</div>'.
					($v['caption']!=''?'<a onclick="$(\'.sp_music_comment_'.$params.'_'.$k.'\').toggle();" class="sp_comments_icon"></a>':'').
					'<a href="'.$track_url.'" class="sp_download_link" title="download" download></a>'.
					'<div class="sp_music_comment_'.$params.'_'.$k.' sp_track_comment">'.$v['caption'].'</div>'.
					'<img src="'.($thumb_url!=''?$thumb_url:'').'" />'.
				'</li>';
			}
			$str.=$li.'</ul></div>';
			$js.='$(document).ready(function(){
				$("#simple_player_'.$params.'").simpleplayer({'.$str_settings.'});
			});';
			if($count_images!==$count_data)
				$is_music_meta=true;
		}
		return $str;
	}

	protected function resolve_html5playerImage($v,$path,&$track_url,&$thumb_url)
	{
		$track_url=strpos($v['track_url'],'innovaeditor')!==false?
			substr_replace($v['track_url'],$path,0,strpos($v['track_url'],'innovaeditor')):
			substr_replace($v['track_url'],$path,0,strpos($v['track_url'],'contentbuilder'));

		$thumb_url=$v['img_url']!=''?substr_replace($v['img_url'],$path,0,strpos($v['img_url'],'innovaeditor')):'';
	}

	protected function buildMboxSlideshow($data,&$js,&$css,$id,$sh_plugin,$path,$cbuilder)
	{
		$slides='';
		$thumb_size=isset($data[0]['thumbs_size'])&&$data[0]['thumbs_size']>0&&!$cbuilder?$data[0]['thumbs_size']:(!$cbuilder?150:0);
		foreach($data as $v)
		{
			$title=$url=$thumb='';
			$this->resolveImage($v,$path,$url,$thumb,$title);
			if($thumb_size>0)
				$image_info=@getimagesize($thumb);
			$slides.=($cbuilder?'':'<div class="thumb_mask">').'<a href="'.$url.'" '.$title.' class="'.($sh_plugin?'':'mbox ').'mbox'.($sh_plugin||$cbuilder?'p_':'').$id.'" rel="lightbox[LB'.($sh_plugin||$cbuilder?'p_':'').$id.'],noDesc">
							<img alt="" src="'.$thumb.'"'.($thumb_size>0&&$thumb_size>$image_info[1]?' style="height:'.$thumb_size.'px;"':'').'>'
							.(self::$f->nivo_box&&$cbuilder?'<div class="OverlayIcon" style="visibility: visible; opacity: 1;"></div>':'').
				'</a>'.($cbuilder?'':'</div>');
		}
		if($cbuilder)
			$slides='<div class="thumb_mask">'.$slides.'</div>';
		if($sh_plugin||$cbuilder)
			$js.=$this->build_plugin_js_mbox($data,$id);

		$css.='.mbox'.($sh_plugin||$cbuilder?'p_':'').$id.'{margin-right:1px;'.
		($thumb_size>0?' position:relative;display: inline-block; overflow: hidden; width:'.$thumb_size.'px; height:'.$thumb_size.'px;':'').'}
			.thumb_mask{display:inline-block}'.
			($thumb_size>0?'.mbox'.($sh_plugin||$cbuilder?'p_':'').$id.' img{position: absolute;left: 50%;top: 50%;max-width:none !important;-webkit-transform: translate(-50%,-50%);transform: translate(-50%,-50%);}':'');
		return $slides;
	}

	protected function buildSlideshow($path,$data,$nav,$type,&$js,&$css,$id,$sh_plugin,$cbuilder)
	{
		$slides='';
		$width_arr=array();
		foreach($data as $k=>$v)
		{
			if($sh_plugin||$cbuilder)
			{
				$img_url=strpos($v['url'],'innovaeditor')!==false?
					substr_replace($v['url'],$path,0,strpos($v['url'],'innovaeditor')):
					substr_replace($v['url'],$path,0,strpos($v['url'],'contentbuilder'));
				$url_target=$v['url_target']!=''?(array_key_exists($v['url_target'], $this->url_target_types['target'])?$this->url_target_types['target'][$v['url_target']]:'blank'):'blank';
				$d='<img src="'.$img_url.'" title="'.$v['title'].'~'.($v['caption']!=''?strip_tags($v['caption']):'').'" alt="'.$v['title'].'" style="display:none"'.($type==1?' data-thumb="'.(strpos($img_url,'innovaeditor')!==false?Formatter::str_lreplace('/','/thumbs/',$img_url):$img_url).'"':'').'>';
				if($v['href']!='')
					$d='<a href="http://'.$v['href'].'" target="_'.$url_target.'">'.$d.'</a>';
				$image_info=@getimagesize($img_url);
				$width_arr[]=is_array($image_info)?$image_info[0]:300;
			}
			elseif(is_array($v))
			{
				$d='<img src="'.$v['im_url'].'" title="'.$v['title'].'~'.($v['content']!=''?strip_tags($v['content']):'').'" alt="'.$v['title'].'" style="display:none">';
				if(isset($v['url']))
					$d='<a href="'.$v['url'].'" target="_blank">'.$d.'</a>';
			}
			else
				$d='<img src="'.$v.'" '.($k>0?'style="display:none" ':'').($type==1?'data-thumb="'.Formatter::str_lreplace('/','/thumbs/',$v).'"':'').' alt="">';

			$slides.=$d;
		}

		$style_width='';
		if($sh_plugin||$cbuilder)
		{
			$title_font_size='20px/24px';
			$caption_font_size='12px/16px';
			$min=min($width_arr)==0?200:min($width_arr);
			$style_width=$sh_plugin?'style="max-width:'.$min.'px;"':'style="width:'.$min.'px; height:auto; margin:0 auto;"';
			if($min<200)
			{
				$title_font_size='10px/12px';
				$caption_font_size='9px/12px';
			}
			elseif($min>=200&&$min<=300)
			{
				$title_font_size='12px/14px';
				$caption_font_size='11px/14px';
			}
			elseif($min>300&&$min<=400)
				$title_font_size='14px/16px';
			elseif($min>400&&$min<=500)
				$title_font_size='18px/20px';
		}
		$show='<div class="wraps'.($cbuilder?' wraps_'.$id.'"':'').'" '.$style_width.' '.($sh_plugin?'id="wraps_'.$id.'"':'').'>
					<div id="'.($sh_plugin||$cbuilder?'p_':'s_').$id.'" class="sshow">'.
					  $slides.'
					</div>
				</div>';
		$css.=$sh_plugin||$cbuilder?($sh_plugin?'#':'.').'wraps_'.$id.' .s_title{font: '.$title_font_size.' \'Lucida Grande\',Verdana,sans-serif !important;color:#FEFEFE;}
                '.($sh_plugin?'#':'.').'wraps_'.$id.' .s_slogan{font: '.$caption_font_size.' \'Lucida Grande\',Verdana,sans-serif !important;color:#FEFEFE;}':'';
		if(strpos($css,'.wraps{')===false)
		{
			$css.=$sh_plugin?'.nivo-slice img {max-width:none;}':'';
			$css.=$cbuilder?'':'.nivo-caption a,.nivo-caption p{display:inline-block !important;color:white;}
			.sshow{z-index:0;overflow:hidden;}
			.wraps{margin:5px;position:relative;background:url('.$path.'extimages/scripts/loader.gif) no-repeat '.($sh_plugin?'top':'center').';}
			.wraps .nivo-main-image{width:100%;}
			.wraps .s_title{font: 20px/24px \'Lucida Grande\',Verdana,sans-serif;color:#FEFEFE;}
			.wraps .s_slogan{font: 12px/16px \'Lucida Grande\',Verdana,sans-serif;color:#FEFEFE;}
			.wraps .nivo-caption{padding: 6px;background: rgba(0,0,0,0.6);position:absolute;bottom:0;height:auto;left:0px;right:0;z-index:89;display:block;}
			.nivo-directionNav a{height:30px;width:30px;text-indent:-9999px;position:absolute;top:15%;z-index:90;cursor:pointer;background:url('.$path.'images/arrows.png) no-repeat;}
			.nivo-bullets nivo-controlNav{height:20px;width:100%;background:white;}
			.nivo-bullets a{background: url('.$path.'extimages/bullets1.png) 0 0;display: block;float:left;height: 20px;text-indent: -9999px;width: 19px;}
			.nivo-bullets a.active{background-position: 0 -20px;}
			.nivo-thumbs-enabled img{border-radius:2px;cursor:pointer;display:inline;margin: 2px 2px 0 0;position: relative;width: 60px;}
			.nivo-thumbs-enabled .active{opacity: 0.8;}
			a.nivo-prevNav{left:5px;}
			a.nivo-nextNav{right:5px;background-position: -30px 0;}';
		}
		if($sh_plugin||$cbuilder)
			$str_settings=$this->buildSlideshow_settings($data, $this->nivoSlider_settings);

		$js.='
			$(document).ready(function(){
				$("'.($sh_plugin||$cbuilder?'#p_':'#s_').$id.'").nivoSlider({'.($sh_plugin||$cbuilder?$str_settings:'effect:"slideInRight",pauseTime:5000,')
				  .($type==0?
					  ($nav?'controlNav:true,':'').'captionAni:true':
					  'directionNav:false,controlNav:true,controlNavThumbs:true').'});
                                var td_details=$("#wraps_'.$id.'").closest("td.details");
                                if(td_details.length>0){
                                    td_details.find(".wraps").css("text-indent","0");
                                    td_details.find(".wraps img").css("margin","0");
                                    td_details.find(".nivo-thumbs-enabled img").css("margin","2px 2px 0 0");
                                }
                        });
			';
		 return $show;
	}

	public static function buildSlideShow_editor($data,$sortable_table,$lb1,$lb2)
	{
		$slides ='';
		if(isset($data['slideshow']) && $data['slideshow']!='')
		{
			 $slides_a=explode('|',$data['slideshow']);
			 foreach($slides_a as $v)
				  $slides.='<input type=hidden name="slides[]" value="'.$v.'"><span class="slides_wrap" onclick="delMe(this)"><img src="'.$v.'" class="slides" style="height:60px;padding-top:5px;"></span>';
		}
		$soptions=array('slideshow','slideshow thumbnails','multibox','multibox single',"slideshow II");
		$value_1 = $lb1;
		$value_2 = '<div id="slideshow" class="ui_shandle_highlight" style="min-height:10px;">'
				  .$slides.'
					</div>
					<input class="input1" type="button" name="btnAssepage-title" id="btnAsset2" onclick="openAsset(\'slideshow\')" value=" '.$lb2.' "> '
				  .Builder::buildSelect('slideshow_type',$soptions,isset($data['slideshow_type'])?$data['slideshow_type']:'');
		return $sortable_table?array(true,$value_1,'',$value_2):array($value_1,$value_2);
	}

	public function set_calang_file()
	{
		return $this->lang_str=File::readLangSet('ca_lang_set.txt',self::$f->ca_settings['language'],'ca');
	}

	public function lang_s($label)
	{
		$r=(isset($this->lang_str['lang_l'][$label])?$this->lang_str['lang_l'][$label]:$label);
		if($r=='-empty-')
			$r='';
		return $r;
	}

	public static function get_js_var($f_settings, $sh_name)
	{
		$arr=array();
		$str_js_vars='';
		foreach($f_settings as $k=>$v)
		{
			if(Unknown::isSequen($v))
				$arr[$k]='["'.implode('","',$v).'"]';
			else
			{
				$misc='';
				$i=0;
				foreach($v as $k_n=>$v_n)
				{
					$misc.=$k_n.':'.$v_n.($i!=(count($v)-1)?',':'');
					$i++;
				}
				$arr[$k]='{'.$misc.'}';
			}
		}
		foreach($arr as $k=>$v)
			$str_js_vars.='var arr_'.$sh_name.$k.'='.$v.';'."\n";

		return $str_js_vars;
	}

	public function buildSlideshow_settings_html($f_settings, $sh_name)
	{
		$html='';
		foreach($f_settings as $k=>$v)
		{
			$html.='<td><span>'.$this->lang_s($k).':</span></br>';
			if(Unknown::isSequen($v))
				$html.=Builder::buildSelect2($sh_name.'settings[]',$sh_name.$k,$v,0,'','key','',' class="'.$sh_name.'settings f_settings"').'</td>';
			else
			{
				$msic='';
				foreach($v as $k_n=>$v_n)
					$msic.=' '.$k_n.'="'.$v_n.'"';
				$html.=Builder::buildInput($sh_name.'settings[]',$v['value'],'','','number',$msic,'','','',false,$sh_name.$k," {$sh_name}settings f_settings").'</td>';
			}
		}
		return $html;
	}

	protected function buildSlideshow_settings($data, $f_settings)
	{
		$arr_settings=explode('|',$data[0]['settings']);
		$count_settings=count($arr_settings);
		$count_f_settings=count($f_settings);
		$str_settings='';

		$i=0;
		foreach($f_settings as $k_s=>$v_s)
		{
			if($count_settings==$count_f_settings)
			{
				$value=Unknown::isSequen($v_s)?'"'.$v_s[$arr_settings[$i]].'"':($v_s['step']==0.1&&$arr_settings[$i]<100?1000*$arr_settings[$i]:$arr_settings[$i]);
				$i++;
			}
			else
				$value=Unknown::isSequen($v_s)?'"'.$v_s[0].'"':($v_s['step']==0.1&&$v_s['value']<100?1000*$v_s['value']:$v_s['value']);
			$str_settings.=$k_s.':'.$value.', ';
		}
		return $str_settings;
	}

	protected function build_plugin_js_mbox($data,$id)
	{
		$str_settings=$this->buildSlideshow_settings($data, self::$f->nivo_box?$this->nivoLightbox_settings:$this->fancybox_settings);
		$box=self::$f->nivo_box?'nivoLightbox':'multibox';
		return '$(document).ready(function(){
						$(".mboxp_'.$id.'").css("text-decoration", "none");
						$(".mboxp_'.$id.'").find("img").css("margin","0");
						 $(".mboxp_'.$id.'").'.$box.'({
						 '.$str_settings.'
						 zicon:true

						 });
				});';
	}
}

class Translit extends FuncHolder
{
	public static function simpleTranslit($string)
	{
		$string=mb_strtolower($string,'UTF-8');
		$string = preg_replace('~[^-a-z0-9_]+~u', '-', $string);
		$string = trim($string, "-");
		return $string;
	}

	public static function rus2translit($string)
	{

		$string=mb_strtolower($string,'UTF-8');
		$converter = array(
		'а' => 'a', 'б' => 'b',  'в' => 'v',
		'г' => 'g', 'д' => 'd',  'е' => 'e',
		'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
		'и' => 'i', 'й' => 'y',  'к' => 'k',
		'л' => 'l', 'м' => 'm',  'н' => 'n',
		'о' => 'o', 'п' => 'p',  'р' => 'r',
		'с' => 's', 'т' => 't',  'у' => 'u',
		'ф' => 'f', 'х' => 'h',  'ц' => 'c',
		'ч' => 'ch','ш' => 'sh', 'щ' => 'sch',
		'ь' => '\'','ы' => 'y',  'ъ' => '\'',
		'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
		'ß' => 'ss','ö' => 'oe', 'ä' => 'ae',
		'ö' => 'oe','ü' => 'ue'
		);

		$string = strtr($string, $converter);
		$string = preg_replace('~[^-a-z0-9_]+~u', '-', $string);
		$string = trim($string, "-");
		return $string;
	}
}

class Formatter extends FuncHolder
{
	public static function strLReplace($search,$replace,$subject) //replace last occurrence
	{
		$pos=strrpos($subject,$search);
		if($pos!==false)
			$subject=substr_replace($subject,$replace,$pos,strlen($search));
		return $subject;
	}

	public static function intVal($v)
	{
		return intval(preg_replace("/[^0-9]/","",$v));
	}

	public static function getfloat($str)
	{
		if(strstr($str,","))
		{
			$str=str_replace(".","",$str);
			$str=str_replace(",",".",$str);
		}
		if(preg_match("#([0-9\.]+)#",$str,$match))
			return floatval($match[0]);
		else
			return floatval($str);
	}

	public static function parseDropdown($temp,$i)
	{
		for($ii=1; $ii<5; $ii++)
		{
			$drop_down_id='a'.$ii;
			$temp=str_replace("ToggleBody('".$drop_down_id."'","ToggleBody('".$drop_down_id."_".$i."'",$temp);
			$temp=str_replace('id="'.$drop_down_id.'Body','id="'.$drop_down_id.'_'.$i.'Body',$temp);
			$temp=str_replace('id="'.$drop_down_id.'Up','id="'.$drop_down_id.'_'.$i.'Up',$temp);
		}
		return $temp;
	}

	public static function longestCommonSubsequence($str_1,$str_2)
	{
		$str_1_len=strlen($str_1);
		$str_2_len=strlen($str_2);
		$result="";

		if($str_1_len===0||$str_2_len===0)
			return $result;
		$longest_common_subsequence=array();

		for($i=0; $i<$str_1_len; $i++)
		{
			$longest_common_subsequence[$i]=array();
			for($j=0; $j<$str_2_len; $j++)
				$longest_common_subsequence[$i][$j]=0;
		}
		$max_size=0;
		for($i=0; $i<$str_1_len; $i++)
		{
			for($j=0; $j<$str_2_len; $j++)
			{
				if($str_1[$i]===$str_2[$j])
				{
					if($i===0||$j===0)
						$longest_common_subsequence[$i][$j]=1;
					else
						$longest_common_subsequence[$i][$j]=$longest_common_subsequence[$i-1][$j-1]+1;

					if($longest_common_subsequence[$i][$j]>$max_size)
					{
						$max_size=$longest_common_subsequence[$i][$j];
						$result="";
					}
					if($longest_common_subsequence[$i][$j]===$max_size)
						$result=substr($str_1,$i-$max_size+1,$max_size);
				}
			}
		}
		return $result;
	}

	public static function replaceIfMacro(&$src)
	{
		$src=str_replace(array('%IF<condition>','</falsevalue>%'),array('<if><condition>','</falsevalue></if>'),$src);
		$ifc='<if>';
		$fval='</if>';
		while(strpos($src,$ifc)!==false)
		{
			$pre=self::GFS($src,$ifc,$fval);
			while(strpos($pre,$ifc)!==false)
				$pre=self::GFS($pre.$fval,$ifc,$fval);
			$temp=$ifc.$pre.$fval;
			$parsed=self::parseIf($temp);
			$src=str_replace($temp,$parsed,$src);
		}
	}

	public static function replaceEvalMacro(&$src,$decimals,$decimalSign,$thousands_sep)
	{
		$evals='%EVAL(';
		$evale=')%';
		while(strpos($src,$evals)!==false)
		{
			$pre=self::GFS($src,$evals,$evale);
			$temp=$evals.$pre.$evale;
			$parsed=self::parseEval($pre,$decimals,$decimalSign,$thousands_sep);
			$src=str_replace($temp,$parsed,$src);
		}
	}

	public static function replaceCopyMacro(&$src,$fieldName='',$fieldValue=false)
	{
		while(strpos($src,'%COPY['.$fieldName)!==false)
		{
			$m=self::GFSAbi($src,'%COPY['.$fieldName,']%');
			$n=self::GFS($m,'%COPY['.$fieldName,']%');
			$d=explode(',',$n);
			$val=$fieldValue===false?$d[0]:$fieldValue;
			if(strpos($val,'<')!==false) //get html
				$val=strip_tags($val);
			$v=mb_substr($val,$d[1]-1,isset($d[2])?$d[2]-$d[1]+1:1000000,'UTF-8');

			$src=str_replace($m,$v,$src);
		}
	}

	public static function str_lreplace($search, $replace, $subject)
	{
		$pos=strrpos($subject,$search);
		if($pos!==false)
			$subject=substr_replace($subject,$replace,$pos,strlen($search));
		return $subject;
	}

	public static function str_replace_once($needle,$replace,$haystack)
	{
		$pos=strpos($haystack,$needle);
		if($pos===false)
			return $haystack;
		return substr_replace($haystack,$replace,$pos,strlen($needle));
	}

	public static function parseEval($macro,$decimals,$decimalSign,$thousands_sep)
	{
		$as_int=strpos($macro,'<i>')!==false;
		$macro=self::getTextBetweenTags($macro,array('div','span','p'));
		$macro=str_replace(array($thousands_sep,','),array('','.'),$macro);
		$v='';
		eval('$v='.preg_replace('/[^0-9.,*,\-,+,\/,(,)]+/','',$macro).';');
		return $as_int?intval($v):number_format(floatval($v),$decimals,$decimalSign,$thousands_sep);
	}

	public static function getTextBetweenTags($string, $tags)
	{
		foreach($tags as $tagname)
		{
			$pattern = "/<$tagname(.*?)>(.*?)<\/$tagname>/i";
			preg_match($pattern, $string, $matches);
			if($matches[2]!=''){
				$string=str_replace($matches[0],$matches[2],$string);
				break;
			}
		}
		return $string;
	}

	public static function parseIf($macro) //moved from shop as used in survey also now
	{
		$macro = html_entity_decode($macro);
		$cond=self::GFS($macro,'<condition>','</condition>');
		if(strpos($cond,'</')!==false)
			$cond=self::getTextBetweenTags($cond,array('div','span','p'));
		if(strpos($cond,' <> ')!==false)
			$eq='<>';
		elseif(strpos($cond,' <= ')!==false)
			$eq='<=';
		elseif(strpos($cond,'=> ')!==false)
			$eq='=>';
		elseif(strpos($cond,'= ')!==false)
			$eq='=';
		elseif(strpos($cond,' < ')!==false)
			$eq='<';
		elseif(strpos($cond,' > ')!==false)
			$eq='>';
		elseif(strpos($cond,'<>')!==false)
			$eq='<>';
		elseif(strpos($cond,'<=')!==false)
			$eq='<=';
		elseif(strpos($cond,'=>')!==false)
			$eq='=>';
		elseif(strpos($cond,'=')!==false)
			$eq='=';
		elseif(strpos($cond,'<')!==false)
			$eq='<';
		elseif(strpos($cond,'>')!==false)
			$eq='>';
		elseif(strpos($cond,'||')!==false)
			$eq='||';
		elseif(strpos($cond,'&&')!==false)
			$eq='&&';
		else
			$eq='';

		$trueval=self::GFS($macro,'<truevalue>','</truevalue>');
		$falseval=self::GFS($macro,'<falsevalue>','</falsevalue>');
		$lc=trim(self::GFS($cond,'',$eq));
		$rc=trim(self::GFS($cond,$eq,''));

		if(strpos($lc,'int(')!==false)
			$lc=intval(preg_replace('/[^0-9\.\-]/','',self::GFS($lc,'int(',')')));
		elseif(strpos($lc,'float(')!==false)
			$lc=floatval(preg_replace('/[^0-9\.\-]/','',self::GFS($lc,'float(',')')));

		if(strpos($rc,'int(')!==false)
			$rc=intval(preg_replace('/[^0-9\.\-]/','',self::GFS($rc,'int(',')')));
		elseif(strpos($rc,'float(')!==false)
			$rc=floatval(preg_replace('/[^0-9\.\-]/','',self::GFS($rc,'float(',')')));

		$res=$falseval;
		if($eq=='=')
		{
			if($lc==$rc)
				$res=$trueval;
		}
		elseif($eq=='>')
		{
			if($lc>$rc)
				$res=$trueval;
		}
		elseif($eq=='<')
		{
			if($lc<$rc)
				$res=$trueval;
		}
		elseif($eq=='<=')
		{
			if($lc<=$rc)
				$res=$trueval;
		}
		elseif($eq=='=>')
		{
			if($lc>=$rc)
				$res=$trueval;
		}
		elseif($eq=='<>')
		{
			if($lc!=$rc)
				$res=$trueval;
		}
		elseif($eq=='||')
		{
			if($lc || $rc)
				$res=$trueval;
		}
		elseif($eq=='&&')
		{
			if($lc && $rc)
				$res=$trueval;
		}
		elseif(intval($lc))
			$res=$trueval;

		return $res;
	}

	public static function formatPageView($page,$apanel,$rel_path)
	{
		$body_tag=self::GFSAbi($page,'<body','>');
		$page=str_replace($body_tag,$body_tag.'<div class="'.CA::getAdminScreenClass().'" style="background:transparent">'.$apanel.'<div style="margin-left:205px">',$page);
		$page=str_replace('</body','</div></div></body',$page);
		$page=str_replace('</title>','</title>'.F_LF.'<link type="text/css" href="'.$rel_path.'documents/ca.css" rel="stylesheet">',$page);
		return $page;
	}

	public static function filterParamsToQuery(&$where,$params)
	{
		foreach($params as $pk=> $pv)
			if($pv!='')
				$where.=($where==''?' WHERE ':' AND ').' '.$pk.' LIKE "%'.addslashes($pv).'%" ';
	}

	public static function hideFromGuests(&$content)
	{
		global $user;
		if(strpos($content,'%hidden_text(')!==false)
		{
			$hid_cnt=self::GFS($content,'%hidden_text(',')%');
			if($user->userCookie()||Cookie::isAdmin())
				$content=str_replace('%hidden_text('.$hid_cnt.')%',$hid_cnt,$content);
			else
				$content=str_replace('%hidden_text('.$hid_cnt.')%','',$content);
		}
	}

	public static function parseMailMacros($str,$user_data=array(),$more_macros=array(),$get_perm_mcs=false)
	{
		$ip=Detector::getIP();
		$perm_macros_array=array('%ip%','%host%','%useremail%','%date%','%os%','%username%','%site%','%whois%','##');

		if($get_perm_mcs)
			return $perm_macros_array;

		$ca_site_url=str_replace('documents/centraladmin.php','',Linker::buildSelfURL('centraladmin.php'));

		$perm_macros_vals=array($ip,(isset($_SERVER['REMOTE_HOST'])?$_SERVER['REMOTE_HOST']:""),$user_data['email'],
			date('Y-m-d G:i',Date::tzone(time())),(isset($_SERVER['HTTP_USER_AGENT'])?Detector::defineOS($_SERVER['HTTP_USER_AGENT']):""),
			$user_data['username'],$ca_site_url,'http://en.utrace.de/?query='.$ip,'<br>');

		$str=str_replace('src="innovaeditor/assets/','src="../innovaeditor/assets/',$str);
		$str=str_replace('%%','%',$str); //backwards compatibility
		$str=str_ireplace($perm_macros_array,$perm_macros_vals,$str);
//message specific macros
		if(is_array($more_macros))
			foreach($more_macros as $k=> $v)
				$str=str_ireplace($k,$v,$str);
		//replacing the user data (if provided)
		if(is_array($user_data))
			foreach($user_data as $k=> $v)
				If(!is_array($v))
					$str=str_replace('%'.$k.'%',$v,$str);

		return $str;
	}

	public static function objDivReplacing($object,$replace_in)
	{
		$replace_in=str_replace("<p>$object</p>","<div>$object</div>",$replace_in);
		$replace_in=str_replace('<p class="rvps1">'.$object.'</p>','<div class="rvps1">'.$object.'</div>',$replace_in);
		$replace_in=str_replace('<p class="rvps2">'.$object.'</p>','<div class="rvps2">'.$object.'</div>',$replace_in);
		return $replace_in;
	}

	public static function objClearing($object,$replace_in)
	{
		$replace_in=str_replace("%".$object."(</p>","%".$object."(",$replace_in);
		$replace_in=str_replace("%".$object."(</span>","%".$object."(",$replace_in);
		$replace_in=str_replace("<span>)%",")%",$replace_in);
		$replace_in=str_replace('<p class="rvps1">)%',")%",$replace_in);
		$replace_in=str_replace('<p class="rvps2">)%',")%",$replace_in);

		while(strpos($replace_in,'%COPY(')!==false)
		{
			$m=self::GFSAbi($replace_in,'%COPY(',')%');
			$n=self::GFS($m,'%COPY(',')%');
			$replace_in=str_replace($m,'%COPY['.$n.']%',$replace_in);
		}
		return $replace_in;
	}

	public static function pTagClearing($replace_in)
	{
		$pos_p=strpos($replace_in,'<p');
		$pos_cp=strpos($replace_in,'</p>');
		if((($pos_cp!==false)&&($pos_p!==false)&&($pos_cp<$pos_p))||(($pos_cp!==false)&&($pos_p===false)))
		{
			$temp1=substr($replace_in,0,$pos_cp);
			$temp2=substr($replace_in,$pos_cp+4);
			$replace_in=$temp1.$temp2;
		}

		return $replace_in;
	}

	public static function dataSorting($records,$by_field='Id',$flag='desc',$prior_field=-1) // sorting info by date
	{
		if(!empty($records))
		{
			foreach($records as $key=> $row)
			{
				$ids[$key]=$row[$by_field];
				if($prior_field>0)
					$priors[$key]=$row[$prior_field];
			}
			if($prior_field>0)
			{
				if($flag=='desc')
					array_multisort($priors,SORT_DESC,SORT_NUMERIC,$ids,SORT_DESC,SORT_NUMERIC,$records);
				else
					array_multisort($priors,SORT_DESC,SORT_NUMERIC,$ids,SORT_ASC,SORT_NUMERIC,$records);
			}
			else
			{
				if($flag=='desc')
					array_multisort($ids,SORT_DESC,SORT_NUMERIC,$records);
				else
					array_multisort($ids,SORT_ASC,SORT_NUMERIC,$records);
			}
		}
		return $records;
	}

	public static function encodeForDownload($data)
	{
		return preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>]+)#", '$1?action=downloadf&url=$2$3',$data);
	}

	public static function cleanWordInput(&$data)
	{
		if(strpos($data,'Mso')!==false)
		{
			$data=preg_replace('/(\n|\r)/'," ",$data);
			$data=preg_replace('/( class=(")?Mso[a-zA-Z]+(")?)/',"",$data);
			$data=preg_replace('/<!--\[if[^\]]*]>.*?<!\[endif\]-->/i','',$data);
		}
	}

	public static function clearHtml($html)
	{

		if($html=='')
			return '';
		$html=str_replace(self::GFSAbi($html,'<div id="bkc"','</div>'),'',$html);
		$html=str_replace(self::GFSAbi($html,'<div id="bkf"','</div>'),'',$html);
		$html=Filter::imgAltTag($html);
		$search_main=array("'<\?php.*?\?>'si","'<script[^>]*?>.*?</script>'si","'<!--footer-->.*?<!--/footer-->'si","'<!--search-->.*?<!--/search-->'si","'<!--counter-->.*?<!--/counter-->'si","'<!--mmenu-->.*?<!--/mmenu-->'si","'<!--smenu-->.*?<!--/smenu-->'si","'<!--ssmenu-->.*?<!--/ssmenu-->'si","'<!--rand-->.*?<!--/rand-->'si","'<!--login-->.*?<!--/login-->'si");
		$result=preg_replace($search_main,array("","","","","","","","","",""),$html);

		if(!isset(self::$f->temp_erea_counter))
			self::$f->temp_erea_counter=1;
		if(strpos($result,'<div style="display:none" class="area1_x">')!==false)
			$result=preg_replace("'<!--%areap.*?<!--areaend-->'si","",$result);
		elseif(self::$f->temp_erea_counter>1)
			$result=preg_replace("'<!--%areap.*?<!--areaend-->'si","",$result);
		self::$f->temp_erea_counter++;

		$search_more=array("'<img.*?>'si","'<a .*?>'si","'<embed.*?<\/embed>'si","'<object.*?<\/object>'si","'<select[^>]*?>.*?<\/select>'si","'<[\/!]*?[^<>]*?>'si","'\n'","'\r\n'","'&(quot|#34);'i","'&(amp|#38);'i","'&(lt|#60);'i","'&(gt|#62);'i","'&(nbsp|#160);'i","'&(iexcl|#161);'i","'&(cent|#162);'i","'&(pound|#163);'i","'&(copy|#169);'i","'&#(d+);'e","'%%USER.*?%%'si","'%%HIDDEN.*?HIDDEN%%'si","'%%DLINE.*?DLINE%%'si","'%%KEYW.*?%%'si");
		$replace_more=array(" "," "," "," "," "," "," "," ","\"","&","<",">"," ",chr(161),chr(162),chr(163),chr(169),"chr(\1)","","","","");
		$result=preg_replace($search_more,$replace_more,$result);
		$result=str_replace('%%TEMPLATE1%%','',$result);
		return self::esc($result);
	}

	public static function clearMacros($content,$id,$fields=array())
	{
		if($id==CALENDAR_PAGE)
			$result=preg_replace(array("'%CALENDAR_OBJECT\(.*?\)%'si","'%CALENDAR_EVENTS\(.*?\)%'si","'%CALENDAR_.*?%'si"),array('','',''),$content);
		elseif($id==BLOG_PAGE)
			$result=preg_replace(array("'%BLOG_OBJECT\(.*?\)%'si","'%BLOG_ARCHIVE\(.*?\)%'si","'%BLOG_RECENT_COMMENTS\(.*?\)%'si","'%BLOG_RECENT_ENTRIES\(.*?\)%'si","'%BLOG_CATEGORY_FILTER\(.*?\)%'si","'%BLOG_.*?%'si"),array('','','','','',''),$content);
		elseif($id==PHOTOBLOG_PAGE)
			$result=preg_replace(array("'%BLOG_OBJECT\(.*?\)%'si","'%BLOG_EXIF_INFO\(.*?\)%'si","'%ARCHIVE_.*?%'si","'%BLOG_.*?%'si","'%PERIOD_.*?%'si","'%CATEGORY_.*?%'si","%GALLERY_LINK%","%CALENDAR%","'%BLOG_RECENT_COMMENTS\(.*?\)%'si","'%BLOG_RECENT_ENTRIES\(.*?\)%'si"),array('','','','','','','','','',''),$content);
		elseif($id==PODCAST_PAGE)
			$result=preg_replace(array("'%PODCAST_OBJECT\(.*?\)%'si","'%PODCAST_ARCHIVE\(.*?\)%'si","'%PODCAST_RECENT_COMMENTS\(.*?\)%'si","'%PODCAST_RECENT_EPISODES\(.*?\)%'si","'%PODCAST_CATEGORY_FILTER\(.*?\)%'si","'%PODCAST_OBJECT\(.*?\)%'si","'%PODCAST_.*?%'si"),array('','','','','','',''),$content);
		elseif($id==GUESTBOOK_PAGE)
		{
			$content=preg_replace(array("'%GUESTBOOK_OBJECT\(.*?\)%'si","'%GUESTBOOK_ARCHIVE\(.*?\)%'si","'%GUESTBOOK_ARCHIVE_VER\(.*?\)%'si","'%GUESTBOOK_.*?%'si"),array('','','',''),$content);
			$result=str_replace(array('%HOME_LINK%','%HOME_URL%'),array('',''),$content);
		}
		elseif(in_array($id,array(SHOP_PAGE,'192','191',CATALOG_PAGE)))  //lister
		{
			$a=array_fill(0,17,'');
			$content=preg_replace(array("'%HASH\(.*?\)%'si","'%ITEMS\(.*?\)%'si","'%SCALE\(.*?\)%'si","'%SHOP_ITEM_DOWNLOAD_LINK\(.*?\)%'si","'%SHOP_CATEGORYCOMBO\(.*?\)%'si","'%SHOP_PREVIOUS\(.*?\)%'si","'%SHOP_NEXT\(.*?\)%'si","'%LISTER_CATEGORYCOMBO\(.*?\)%'si","'%LISTER_PREVIOUS\(.*?\)%'si","'%LISTER_NEXT\(.*?\)%'si","'<!--menu_java-->.*?<!--/menu_java-->'si","'<!--scripts2-->.*?<!--endscripts-->'si","'<!--<pagelink>/.*?</pagelink>-->'si","'<LISTER_BODY>.*?</LISTER_BODY>'si","'<LISTERSEARCH>.*?</LISTERSEARCH>'si","'<SHOP_BODY>.*?</SHOP_BODY>'si","'<SHOPSEARCH>.*?</SHOPSEARCH>'si","'%SHOP_.*?%'si","'%LISTER_.*?%'si","'%SLIDESHOWCAPTION_.*?%'si"),$a,$content);
			$content=str_replace(array('%ERRORS%','%IDEAL_VALID%','%QUANTITY%','%LINETOTAL%','%LINETOTAL%','%URL=Detailpage%','%CATEGORY_COUNT%','%SEARCHSTRING%','%SUBCATEGORIES%','%NAVIGATION% '),'',$content);

			$a=array_fill(0,40,'');
			$result=str_replace(array('<ITEM_VARS>','</ITEM_VARS>','<ITEM_VARS_LINE>','</ITEM_VARS_LINE>','<ITEM_HASHVARS>','</ITEM_HASHVARS>','<SHOP_DELETE_BUTTON>','</SHOP_DELETE_BUTTON>','<MINI_CART>','</MINI_CART>','<SHOP_BUY_BUTTON>','</SHOP_BUY_BUTTON>','<QUANTITY>','<RANDOM>','</RANDOM>','<SHOP>','</SHOP>','<LISTER>','</LISTER>','<ITEM_INDEX>','<ITEM_ID>','<ITEM_QUANTITY>','<ITEM_AMOUNT>','<ITEM_AMOUNT_IDEAL>','<ITEM_VAT>','<ITEM_SHIPPING>','<ITEM_CODE>','<ITEM_SUBNAME>','<ITEM_SUBNAME1>','<ITEM_SUBNAME2>','<ITEM_NAME>','<ITEM_CATEGORY>','<ITEM_VARS>','</ITEM_VARS>','<SHOP_URL>','<BANKWIRE>','</BANKWIRE>','<CATEGORY_HEADER>','</CATEGORY_HEADER>','<FROMCART>'),$a,$content);
		}
		else
			$result=$content;
		if(!empty($fields))
			foreach($fields as $v)
				$result=str_replace('%'.$v.'%','',$result);

		$result=str_replace(array('%LINK_TO_ADMIN%','%TAGS_CLOUD%'),array('','',''),$result);
		return $result;
	}

	public static function GFS($src,$start,$stop)
	{
		if($start=='')
			$res=$src;
		elseif(strpos($src,$start)===false)
		{
			$res='';
			return $res;
		}
		else
			$res=substr($src,strpos($src,$start)+strlen($start));
		if(($stop!='')&&(strpos($res,$stop)!==false))
			$res=substr($res,0,strpos($res,$stop));
		return $res;
	}

	public static function GFSAbi($src,$start,$stop)
	{
		$res2=self::GFS($src,$start,$stop);
		return $start.$res2.$stop;
	}

	public static function mySubstr($string,$start,$stop,$utf_date_flag=false)
	{
		if(self::$f->use_mb)
			return mb_substr($string,$start,$stop,'UTF-8');
		else
		{
			$c=$string;
			$f=ord($c[0]);
			$nb=$stop;
			if($f>=0&&$f<=127)
				$nb=$stop;
			if($f>=192&&$f<=223&&!$utf_date_flag)
				$nb=$stop;
			if($f>=192&&$f<=223&&$utf_date_flag)
				$nb=$stop*2;
			if($f>=224&&$f<=239&&$utf_date_flag)
				$nb=$stop*3;
			if($f>=240&&$f<=247&&$utf_date_flag)
				$nb=$stop*4;
			if($f>=248&&$f<=251&&$utf_date_flag)
				$nb=$stop*5;
			if($f>=252&&$f<=253&&$utf_date_flag)
				$nb=$stop*6;
			return substr($string,$start,$nb);
		}
	}

	public static function substrUni($str,$s,$l=null)
	{
		if(self::$f->use_mb)
			return mb_substr($str,$s,$l,"UTF-8");
		return join("",array_slice(preg_split("//u",$str,-1,PREG_SPLIT_NO_EMPTY),$s,$l));
	}

	public static function strToLower($s)
	{
		return (self::$f->uni&&self::$f->use_mb)?mb_strtolower($s,"UTF-8"):strtolower($s);
	}

	public static function strToUpper($s)
	{
		return (self::$f->uni&&self::$f->use_mb)?mb_strtoupper($s,"UTF-8"):strtoupper($s);
	}

	public static function replaceLG($s)
	{
		return str_replace(array('<','>'),array('&lt;','&gt;'),$s);
	}

	public static function splitHtmlContent($string,$max_chr)
	{
		return Unknown::xtract($string,$max_chr/4);
	}

	public static function unEsc($s)
	{
		return str_replace(array('\\\\','\\\'','\"'),array('\\','\'','"'),$s);
	}

	public static function esc($s)
	{
		return (get_magic_quotes_gpc()?$s:str_replace(array('\\','\'','"'),array('\\\\','\\\'','\"'),$s));
	}

	public static function sth($s)
	{
		return htmlspecialchars(str_replace(array('\\\\','\\\'','\"'),array('\\','\'','"'),$s),ENT_QUOTES);
	}

	public static function sth2($s)
	{
		return str_replace(array('\\\\','\\\'','\"','<?','?>'),array('\\','\'','"','&lt;?','?&gt;'),$s);
	}

	public static function sth3($s)
	{
		return str_replace(array('\\\\','\\\'','\"'),array('\\','\'','"'),$s);
	}

	public static function stripTags($src,$tags='')
	{
		$src=urldecode($src);
		$src=strip_tags($src,$tags);
		return $src;
	}

	public static function stripQuotes($src)
	{
		$src=str_replace(array('"','\''),'',$src);
		return $src;
	}

	# formats admin screen output
	public static function fmtAdminScreen($content,$menu='')
	{
		$output='<div class="'.CA::getAdminScreenClass().'">';
		if(!empty($menu))
			$output.=$menu.'<br class="ca_br" />';
		$output.=$content.'</div>';
		return $output;
	}

	public static function fmtBlockedIPs($blocked_ips,$script_path,$unblock_label,$noblocked_label)
	{
		if(!empty($blocked_ips))
		{
			$output='<div class="a_n a_listing"><div class="a_navn">
				 <table class="'.self::$f->atbgr_class.'" cellpadding="8">';
			foreach($blocked_ips as $v)
				$output.='
					<tr><td>'.sprintf('<span class="rvts8">%s</span>',$v['ip']).'</td>
						  <td><a class="rvts12" href="'.$script_path."?action=index&amp;unblockip=".$v['ip'].'">['.$unblock_label.']</a>
					</td></tr>';
			$output.="</table></div></div>";
		}
		else
			$output='<span class="rvts8 empty_caption">'.$noblocked_label.'</span>';
		return $output;
	}

	# formats page output in template
	public static function fmtInTemplate($filename,$page_output,$title,$css='',$bg_tag='',$include_menu=true,
			  $include_counter=false,$miniform_in_earea=false,$grab_tpl_from_php=false,$ignore_fullScreen=false)
	{
		$root=!(((strpos($filename,'../')!==false)&&substr_count($filename,'/')>1&&(strpos(self::$f->template_source,'../')===false)));
		if(!$root)
			self::$f->template_source='../'.self::$f->template_source;
		if(file_exists(self::$f->template_source))
			$filename=self::$f->template_source;

		$contents=File::read($filename);
		$fs=$ignore_fullScreen?false:self::$f->ca_fullscreen;
		if($grab_tpl_from_php) //get template from php page (remove all the php code)
			$contents=str_replace(self::GFSAbi($contents,'<?php','?>'),'',$contents);

		if(!$fs&&strpos($filename,'template_source.html')!==false&&strpos($contents,'%CONTENT%')!==false)
			$pattern='%CONTENT%';
		elseif(!$fs&&strpos($contents,'<!--page-->')!==false&&$include_menu)
			$pattern=self::GFS($contents,'<!--page-->','<!--/page-->');
		else
		{
			$pattern=self::GFSAbi($contents,'<body','</body>');
			$body_part=substr($pattern,0,strpos($pattern,'>')+1);
			if($bg_tag!=='')
				$body_part=str_replace('<body','<body style="'.$bg_tag.'"',$body_part);
			$page_output=$body_part.'<!--page-->'.$page_output.'<!--/page--></body>';
		}
		$contents=str_replace($pattern,$page_output,$contents);
		if($include_counter==false)
			$contents=str_replace(self::GFS($contents,'<!--counter-->','<!--/counter-->'),'',$contents);
		if(!empty($css))
			$contents=str_replace('<!--scripts-->','<!--scripts-->'.F_LF.$css,$contents);
		if($root&&(strpos($filename,'template_source.html')!==false)&&!$miniform_in_earea)
			$contents=str_replace(array('src="../','href="../'),array('src="','href="'),$contents);
		if($fs)
			$contents=str_replace('documents/textstyles_nf.css"','documents/ca.css"',$contents);
		else
			$contents=str_replace('</title>','</title>'.F_LF.'<link type="text/css" href="'.($root&&!$miniform_in_earea?'':'../').'documents/ca.css" rel="stylesheet">',$contents);

		if(self::$f->ca_fullscreen&&strpos($contents,'script.js')!==false && strpos($contents,self::$f->art_prefix.'sheet')===false) //fix for conflict artisteer in full screen mode
			$contents=str_replace(array('<script type="text/javascript" src="../documents/script.js"></script>','<script type="text/javascript" src="documents/script.js"></script>'),'',$contents);

		if(self::$f->ca_fullscreen)
			$contents=str_replace(array('<script src="script.responsive.js"></script>','<script src="../script.responsive.js"></script>'),'',$contents);

		$contents=str_replace(array('<!--scripts-->','$(document).ready(function(){initialize();});'),
				  array('<!--scripts-->
						<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
						<script type="text/javascript" src="'.($root?'':'../').'documents/ca.js"></script>',''),
				  $contents);

		if(strpos($contents,'class="color ')!==false)
			$contents=str_replace('<!--scripts-->','<!--scripts--><script type="text/javascript" src="'.($root?'':'../').'js/jscolor.js"></script>',$contents);

		if($title!='')
			$contents=str_replace(Formatter::GFSAbi($contents,'<title>','</title>'), '<title>'.$title.'</title>',$contents);

		return $contents;
	}

	public static function fmtErrMsg($msg)
	{
		return '<div class="a_n a_navtop">
			<p><span class="rvts8"><em style="color:red;">'.$msg.'</em></span></p><br></div><br>';
	}

	public static function fmtErrorMsg($error_index)
	{
		$template='<h1>%s</h1>
			<h1>%s</h1>
			<span>%s</span>';

		if($error_index=='EMAIL_NOTSET')
			$output=sprintf($template,'Email FAILED','PROBLEM: You haven\'t defined your email yet',
				'To solve the problem, open page in EZGenerator and type email address in Send Notification to box.');
		elseif($error_index=='MAIL_FAILED')
			$output=sprintf($template,'Operation FAILED!','PROBLEM: Missing mail settings','To solve the problem, check with host provider if server uses MAIL or SMTP for sending mails.
			If SMTP is used, get the smtp settings from provider, go to Project Settings - PHP settings and set the smtp settings.
			If MAIL is used, check with your provider if mail settings are set correctly.');
		return $output;
	}

	public static function includeMetaArchives($page_src,$archive_entries)
	{
		$meta='';
		foreach($archive_entries as $v)
			$meta.='<link rel="archives" title="'.$v['title'].'" href="'.$v['href'].'">'.F_LF;
		$page_src=str_replace('<!--rss_meta-->','<!--rss_meta-->'.F_LF.$meta,$page_src);
		return $page_src;
	}

	public static function floatLogin($src)
	{
		$temp=self::GFS($src,'<!--login-->','<!--/login-->');
		$float_login=strpos($temp,'class="frm_login"')!==false;
		return $float_login;
	}

	public static function rawencode($data)
	{
		return rawurlencode(str_replace(array('&','@','"',"'",'/',':',';',',','?','.','!','$','|','<','>','=','^','#','\\'),'',$data));
	}

	public static function titleForLink($data)
	{
		$data=str_replace(' ','-',self::stripTags(self::strToLower(self::sth2(urldecode($data)))));
		return self::rawencode($data);
	}

	public static function replacePollMacro_null(&$src)
	{
		if(strpos($src,'{%POLL_ID(')!==false)
			$src=preg_replace("'{%POLL_ID(.*)\%}'" ,"",$src);
	}

	public static function replacePollMacro(&$src,$relpath,$lang_id)
	{
		$page_in_root=$relpath !== ''?'false':'true';
		$js_polls = '';
		while(strpos($src,'{%POLL_ID(')!==false)
		{
			$m=self::GFSAbi($src,'{%POLL_ID(',')%}');
			$qid=intval(self::GFS($m,'{%POLL_ID(',')%}'));
			$js_polls .= '$(document).ready(function(){ $("#poll_'.$qid.'").poll({id:"'.$qid.'",root:'.$page_in_root.',lang_id:"'.$lang_id.'"});});';
			$div = '<div id="poll_'.$qid.'"><div class="pc"></div><p class="loader">Loading...</p></div>';
			$src=str_replace($m,$div,$src);
		}
		return $js_polls;
	}

	public static function search_array($needle, $haystack)
	{
		if(in_array($needle, $haystack))
			return true;

		foreach($haystack as $element)
			if(is_array($element) && self::search_array($needle, $element))
				return true;

		return false;
	}
}

class CommentHandler extends FuncHolder
{

	public static function getTagAttr($string,$tag,$attr,$has_closing=false)
	{
		$pattern='/<'.$tag.' (.*?)'.$attr.'=((\'(.*?)\')|("(.*?)"))(.*?)'.($has_closing?'>(.*?)</'.$tag.'>':'(\/)?>').'/i';
		preg_match_all($pattern,$string,$tagAttrs,PREG_PATTERN_ORDER);
		$ret=array();
		foreach($tagAttrs[1] as $pos=> $tag)
		{
			if($tagAttrs[4][$pos]!='')
				$ret[]=$tagAttrs[4][$pos];
			elseif($tagAttrs[6][$pos]!='')
				$ret[]=$tagAttrs[6][$pos];
		}

		return $ret;
	}

	public static function parseComment($str,$full_access,$loggedUser,$canUseUrl=false,$mini_editor=false)
	{
		$htmlTags=($full_access||$mini_editor?implode('',self::$f->comments_allowed_tags['html_admin']):'').implode('',self::$f->comments_allowed_tags['html']);
		if($loggedUser)
			$htmlTags .= implode('',self::$f->comments_allowed_tags['extra']);
		$result=strip_tags($str,$htmlTags);
		if(!$mini_editor)
			$result=self::cleanInsideHtmlTags($result,implode('',self::$f->comments_allowed_tags['html']));
		else
			$result=self::parseOnlyStyleAtrribute($result,array_merge($html_admin_tags,self::$f->comments_allowed_tags['html'],self::$f->comments_allowed_tags['extra']));
		if($loggedUser)
			$result=self::parseTagsWithAttrs($result,self::$f->comments_allowed_tags['extra']);
		if($canUseUrl)
			self::parseContentX($result);

		return $result;
	}

	public static function parseOnlyStyleAtrribute($str,$allowed_tags)
	{
		preg_match_all('/<([^>]+)>/i',$str,$allNTags,PREG_PATTERN_ORDER);
		foreach($allNTags[1] as $tagInfo)
		{
			$arr_tag=explode(' ',$tagInfo);
			$tag=strtolower(str_replace(array('<','>'),'',$arr_tag[0]));
			if(in_array('<'.$tag.'>',$allowed_tags))
			{
				$style='';
				if(strpos($tagInfo,'style="')!==false)
					$style=preg_replace('/<[^>]*>/', '', Formatter::GFS($tagInfo,'style="','"'));
				$str=str_replace($tagInfo,$tag.(!empty($style)?' style="'.$style.'"':''),$str);
			}
		}

		return $str;
	}

	public static function parseContentX(&$str)
	{
		if(isset($_POST['content_x'])&&$_POST['content_x']!='')
			$str.=$_POST['content_x'];
	}

	//Clean the inside of the tags
	public static function cleanInsideHtmlTags($str,$tags)
	{
		preg_match_all('/<([^>]+)>/i',$tags,$allTags,PREG_PATTERN_ORDER);
		foreach($allTags[1] as $tag)
			$str=preg_replace('/<'.$tag.' [^>]*>/i','<'.$tag.'>',$str);

		return $str;
	}

	public static function parseTagsWithAttrs($str,$allowed_tags)
	{
		$allCTags=$allNTags=array();
		preg_match_all('/<([^>]+)>(.*?)<\/([^>]+)>/i',$str,$allCTags,PREG_PATTERN_ORDER);
		preg_match_all('/<([^>]+)>/i',$str,$allNTags,PREG_PATTERN_ORDER);
		foreach($allCTags[1] as $pos=> $tagInfo)
		{
			$tag=strtoupper($allCTags[3][$pos]);
			if(in_array('<'.$tag.'>',$allowed_tags))
			{
				if($tag=='A')
				{
					$url=$allCTags[2][$pos];
					if(strpos($tagInfo,'=')!==false)
						$url=self::getTagAttr($tagInfo,'a','href');
					$str=str_replace($allCTags[0][$pos],'<a href="'.$url.'">'.$allCTags[2][$pos].'</a>',$str);
				}
			}
		}

		foreach($allNTags[0] as $pos=> $tagInfo)
			if(strpos($tagInfo,'<img')!==false)
			{
				$imgScrs=self::getTagAttr($tagInfo,'img','src');
				if(!empty($imgScrs))
				{
					if(stripos($imgScrs[0],'http')===false) //ignore absolute paths
					{
						$imgSrc=Linker::relPathBetweenURLs($imgScrs[0],Linker::currentPageUrl());
						$str=str_replace($tagInfo,'<img class="img_comment_maxw" src="'.$imgSrc.'" />',$str);
					}
				}
			}
			elseif(strpos($tagInfo,'<span')!==false)
			{
				$spanStyles=self::getTagAttr($tagInfo,'span','style');
				if(!empty($spanStyles))
					$str=str_replace($tagInfo,'<span style="'.$spanStyles[0].'" />',$str);
			}
			elseif(strpos($tagInfo,'<div')!==false)
			{
				$divStyles=self::getTagAttr($tagInfo,'span','style');
				if(!empty($divStyles))
					$str=str_replace($tagInfo,'<span style="'.$divStyles[0].'" />',$str);
			}
		return $str;
	}

	public static function buildHintDiv($lang_l,$admin=false)
	{
		$hint='';
		$htmlTags=array_merge(self::$f->comments_allowed_tags['html'],self::$f->comments_allowed_tags['extra']);
		if($admin)
			$htmlTags=array_unique(array_merge($htmlTags,self::$f->comments_allowed_tags['html_admin']));

		foreach($htmlTags as $tag)
		{
			if($tag=='<a>')
				$hint.= '<span class="comment_tag_lbl rvts12" title="'.htmlspecialchars('<a href="http://some.url"></a>').'">'.htmlspecialchars($tag).' </span>';
			elseif($tag=='<img>')
				$hint.= '<span class="comment_tag_lbl rvts12" title="'.htmlspecialchars('<img src="http://some.url" >').'">'.htmlspecialchars($tag).' </span>';
			else
				$hint.= '<span class="comment_tag_lbl rvts12" title="'.htmlspecialchars($tag.str_replace('<','</',$tag)).'">'.htmlspecialchars($tag).' </span>';
		}

		return '<div class="rvts8 allowed_tags">'.$lang_l['comments tags allowed'].$hint.'</div>';
	}
}

class Video extends FuncHolder
{

	public static function getVideoImage($url)
	{
		$image_url=parse_url($url);
		if($image_url['host']=='www.youtube.com'||$image_url['host']=='youtube.com')
		{
			if(strpos($image_url['path'],'/v/')!==false)
			{
				$array=explode('/',$image_url['path']);
				return 'http://i3.ytimg.com/vi/'.$array[1].'/default.jpg';
			}
			else
			{
				$array=explode('&',$image_url['query']);
				return 'http://i3.ytimg.com/vi/'.substr($array[0],2).'/default.jpg';
			}
		}
		else if($image_url['host']=='www.youtu.be'||$image_url['host']=='youtu.be')
		{
			$array=explode('/',$image_url['path']);
			return 'http://i3.ytimg.com/vi/'.$array[1].'/default.jpg';
		}
		else if($image_url['host']=='www.vimeo.com'||$image_url['host']=='vimeo.com')
		{
			$ctx=stream_context_create(array('http'=>array('timeout'=>5)));
			$hash=unserialize(file_get_contents(
					'http://vimeo.com/api/v2/video/'.substr($image_url['path'],1).'.php',false,$ctx));
			return $hash[0]["thumbnail_small"];
		}
	}

	public static function youtube_vimeo_check($src)
	{
		$src=Formatter::strToLower($src);
		return strpos($src,'youtube.')!==false ||	strpos($src,'youtu.be')!==false || strpos($src,'.yimg/')!==false
				||strpos($src,'vimeo.com')!==false;
	}
}

class Crypt extends FuncHolder
{
    public static function encrypt($text) {
        $data = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,md5(self::$f->proj_id), $text, MCRYPT_MODE_ECB, 'keee');
        return strtr(base64_encode($data), '+/=', '-_,');
    }

    public static function decrypt($text) {
				$text = base64_decode(strtr($text, '-_,', '+/='));
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128,md5(self::$f->proj_id), $text, MCRYPT_MODE_ECB, 'keee');
    }
}

class Image extends FuncHolder
{
//flags: thumb, image, rescalethumb, rescaleimage
//thumb_scale could be 1=default or 2=crop
	public static function scale($fname,$max_image_size=600,$flag='image',$quality=100,$max_image_side=600,$max_thumb_size=300,$max_thumb_height=120,$thumb_scale=1)  // scale image/thumbnail
	{
		if(ini_get('memory_limit')<100)
			ini_set('memory_limit','100M');

		if($flag=='rescaleimage')
		{
			$full_fname=substr($fname,0,strrpos($fname,"."))."_full".substr($fname,strrpos($fname,"."));
			if(file_exists($full_fname))
				list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($full_fname);
			else
				list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($fname);
		}
		else
			list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($fname);

		$thumb=$flag=='thumb'||$flag=='rescalethumb';
		$max_size_param=$thumb?$max_thumb_size:$max_image_size;
		if($orig_width>$max_size_param||$orig_height>$max_size_param)
		{
			if($flag=='image')
			{
				$new_fname=$fname;
				$fname=substr($fname,0,strrpos($fname,"."))."_full".substr($fname,strrpos($fname,"."));
				rename($new_fname,$fname);
			}
			elseif($flag=='rescaleimage')
			{
				$final_name=$fname;
				$new_fname=substr($fname,0,strrpos($fname,"."))."_tempimg".substr($fname,strrpos($fname,"."));
				if(file_exists($full_fname))
					copy($full_fname,$fname);
			}
			elseif($flag=='rescalethumb')
			{
				$final_name=substr($fname,0,strrpos($fname,"."))."_thumb".substr($fname,strrpos($fname,"."));
				$new_fname=substr($fname,0,strrpos($fname,"."))."_thumb_tempimg".substr($fname,strrpos($fname,"."));
			}
			else
				$new_fname=substr($fname,0,strrpos($fname,"."))."_thumb".substr($fname,strrpos($fname,"."));

			$ratio=$orig_width/$orig_height;

			if($thumb_scale==2)
				$scaling='crop';
			else
				$scaling='default';
//scaling_mode
			$new_width=$orig_width;
			$new_height=$orig_height;
			if(!$thumb||$scaling=='default') //old scaling
			{
				if($orig_width>=$orig_height)
				{
					$new_width=$max_size_param;
					$new_height=intval($max_size_param/$ratio);
				}
				else
				{
					$new_width=intval($max_size_param*$ratio);
					$new_height=$max_size_param;
				}
			}
			else
			{
				if($orig_width>=$orig_height)
				{
					$new_height=$max_thumb_height;
					$new_width=intval($new_height*$ratio);
				}
				else
				{
					$new_width=$max_size_param;
					$new_height=intval($new_width/$ratio);
				}
			}
// Resample
			$image_p=imagecreatetruecolor($new_width, $new_height);
			$image=self::gdCreate($img_type,$fname);
//transparency
			if($img_type==1)  //gif
			{
				$trnprt_indx=imagecolortransparent($image);
				if($trnprt_indx>=0)
				{
					$trnprt_color=imagecolorsforindex($image,$trnprt_indx);
					$trnprt_indx=imagecolorallocate($image_p,$trnprt_color['red'],$trnprt_color['green'],$trnprt_color['blue']);
					imagefill($image_p,0,0,$trnprt_indx);
					imagecolortransparent($image_p,$trnprt_indx);
				}
			}
			elseif($img_type==3) //png
			{
				imagealphablending($image_p,false);
				$color=imagecolorallocatealpha($image_p,0,0,0,127);
				imagefill($image_p,0,0,$color);
				imagesavealpha($image_p,true);
			}
//end transparency
			if($image!='')
			{
				imagecopyresampled($image_p,$image,0,0,0,0,$new_width,$new_height,$orig_width,$orig_height);
				self::gdSave($image_p,$new_fname,$quality,$img_type); // Save image
				imagedestroy($image_p);
				imagedestroy($image);
				if($flag=='rescalethumb'||$flag=='rescaleimage')
				{
					unlink($final_name);
					rename($new_fname,$final_name);
				}
				return $new_fname;
			}
			else
				return false;
		}
		elseif($flag=='image')
		{
			$full_fname=substr($fname,0,strrpos($fname,"."))."_full".substr($fname,strrpos($fname,"."));
			copy($fname,$full_fname);
			return $fname;
		}
		else
			return $fname;
	}

	public static function gdSave($image_p,$new_fname,$quality,$img_type)
	{
		if($img_type==1)
			imagegif($image_p,$new_fname);
		elseif($img_type==3)
			imagepng($image_p,$new_fname);
		else
			imagejpeg($image_p,$new_fname,$quality);
	}

	public static function gdCreate($img_type,$fname)
	{
		if($img_type==1)
			$image=imagecreatefromgif($fname);
		elseif($img_type==3)
			$image=imagecreatefrompng($fname);
		else
			$image=imagecreatefromjpeg($fname);
		return $image;
	}

	public static function gdRotate($fname,$quality,$rotate_angle)
	{
		if(ini_get('memory_limit')<50)
			ini_set('memory_limit','50M');

		list($orig_width,$orig_height,$img_type,$img_attr)=@getimagesize($fname);
		if($rotate_angle>0&&function_exists("imagerotate"))
		{
			$image_r=self::gdCreate($img_type,$fname);
			if($image_r!='')
			{
				$image_r_new=imagerotate($image_r,intval($rotate_angle),0);
				self::gdSave($image_r_new,$fname,$quality,$img_type);
			}
		}
	}

	public static function buildYTImage($yt_url)
	{
		if(strpos($yt_url,'embed/')!==false)
			$id=Formatter::GFS($yt_url,'embed/','');
		elseif(strpos($yt_url,'watch?v=')!==false&&strpos($yt_url,'&')===false)
			$id=substr($yt_url,strpos($yt_url,'?v=')+3);
		elseif(strpos($yt_url,'watch?v=')!==false)
			$id=Formatter::GFS($yt_url,'?v=','&');
		elseif(strpos($yt_url,'?')!==false)
			$id=Formatter::GFS($yt_url,'/v/','?');
		elseif(strpos($yt_url,'&')!==false)
			$id=Formatter::GFS($yt_url,'/v/','&');
		else
			$id=substr($yt_url,strpos($yt_url,'/v/')+3);
		return 'http://img.youtube.com/vi/'.$id.'/0.jpg';//return 'http://i1.ytimg.com/vi/'.$id.'/default.jpg';
	}
}

class RSS extends FuncHolder
{

	public static function clearCache($script_dir)
	{
		$files=array();
		if($handle=opendir($script_dir.'innovaeditor/assets/'))
		{
			while(false!==($file=readdir($handle)))
			{
				if($file!="."&&$file!=".."&&strpos($file,'cache_')===0)
					$files[]=$file;
			}
		}
		closedir($handle);
		foreach($files as $v)
			unlink($script_dir.'innovaeditor/assets/'.$v);
	}

	public static function line($tag,$rss_setting,$fl_flag=false,$sth=false)
	{
		$t=($fl_flag)?' ':'';
		return $t."<$tag>".($sth?Formatter::sth($rss_setting):$rss_setting)."</$tag>".F_LF;
	}

	public static function lineSt($line,$fl_flag=false)
	{
		$t=($fl_flag)?' ':'';
		return $t.$line.F_LF;
	}

	public static function buildHeader($rss_settings,$page_charset,$page_url,$publish_date,$more_xmlns='',$fl_flag=false,$rss_url='',$title='',$googleM=false)
	{
		if($googleM)
			$rss_header='<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
		elseif(!isset($rss_settings['Subtitle (iTunes)']))
			$rss_header='<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" '.$more_xmlns.'>';
		else
			$rss_header='<rss version="2.0" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" '.$more_xmlns.'>';
		$pub_date=date('r',$publish_date);
		if($title=='')
			$title=empty($rss_settings['Title'])?'My site':$rss_settings['Title'];

		$output='<?xml version="1.0" encoding="'.$page_charset.'"?>'.F_LF;
		if(isset($_SERVER['HTTP_USER_AGENT'])&&strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')!==false)
			$output.='<?xml-stylesheet type="text/xsl" media="screen" href="'.self::$f->site_url.'documents/rss.xls"?>'.F_LF;
		$output.=self::lineSt($rss_header,$fl_flag).self::lineSt('<channel>',$fl_flag);
		if(strpos($rss_header,'xmlns:atom')!==false)
			$output.=self::lineSt('<atom:link href="'.($rss_url!=''?$rss_url:$page_url.'?action=rss').'" rel="self" type="application/rss+xml"/>',$fl_flag);
		$output.=self::line('title',$title,$fl_flag,true).self::line('link',$page_url,$fl_flag)
			.self::line('description',$rss_settings['Description'],$fl_flag,true)
			.self::line('language',$rss_settings['Language'],$fl_flag).self::line('pubDate',$pub_date,$fl_flag).self::line('lastBuildDate',$pub_date,$fl_flag)
			.self::line('docs','http://blogs.law.harvard.edu/tech/rss',$fl_flag);

		$tags_list=array('copyright','managingEditor','webMaster','category','ttl','cloud','image','rating','skipHours','skipDays');
		$settings_list=array('Copyright','Managing editor','Webmaster','Category','TTL','Cloud domain','Image','Rating','Skip hours','Skip days');
		foreach($settings_list as $k=> $v)
		{
			if(!empty($rss_settings[$v]))
			{
				$tag=$tags_list[$k];
				$value=$rss_settings[$v];
				if($v=='Category'&&empty($rss_settings['Category domain']))
					$output.=self::line($tag,$value,$fl_flag,true);
				elseif($v=='Category')
					$output.=self::lineSt('<'.$tag.' domain="'.$rss_settings['Category domain'].'">'.$value.'</'.$tag.'>',$fl_flag,true);
				elseif($v=='TTL'&&$value!=0)
					$output.=self::line($tag,$value,$fl_flag,true);
				elseif($v=='Cloud domain')
				{
					if($rss_settings['Cloud path']!='')
							$output.=self::lineSt('<'.$tag.' domain="'.$value.'" port="'.$rss_settings['Cloud port'].'" path="'.$rss_settings['Cloud path'].'" registerProcedure="'.$rss_settings['Cloud reg proc'].'" protocol="'.$rss_settings['Cloud protocol'].'"/>',$fl_flag);
				}
				elseif($v=='Image')
					$output.=self::lineSt('<'.$tag.'>',$fl_flag).self::lineSt('<title>'.$title.'</title>',$fl_flag).self::lineSt('<link>'.$page_url.'</link>',$fl_flag).self::lineSt('<url>'.$value.'</url>',$fl_flag).self::lineSt('</'.$tag.'>',$fl_flag);
				else
					$output.=self::line($tag,$value,$fl_flag,true);
			}
		}
// iTunes special tags
		if(isset($rss_settings['Subtitle (iTunes)']))
		{
			$tags_list=array('itunes:summary','itunes:subtitle','itunes:author','itunes:image','itunes:owner','itunes:keywords','itunes:explicit','itunes:block','itunes:new-feed-url');
			$settings_list=array('Description','Subtitle (iTunes)','Author (iTunes)','Image (iTunes)','Owner name (iTunes)','Keywords (iTunes)','Explicit (iTunes)','Block (iTunes)','New-feed-url (iTunes)');
			foreach($settings_list as $k=> $v)
			{
				$tag=$tags_list[$k];
				$value=$rss_settings[$v];
				if($v=='Description')
					$output.=self::line($tag,(empty($value)?'This is my podcast':$value),$fl_flag,true);
				elseif($v=='Owner name (iTunes)'&&(!empty($value)||!empty($rss_settings['Owner email (iTunes)'])))
				{
					$output.=self::lineSt('<'.$tag.'>',$fl_flag);
					if($rss_settings['Owner name (iTunes)']!='')
						$output.=self::line('itunes:name',$rss_settings['Owner name (iTunes)'],$fl_flag,true);
					if($rss_settings['Owner email (iTunes)']!='')
						$output.=self::line('itunes:email',$rss_settings['Owner email (iTunes)'],$fl_flag,true);
					$output.=self::lineSt('</'.$tag.'>',$fl_flag);
				}
				elseif(!empty($rss_settings[$v]))
				{
					if($v=='Image (iTunes)')
						$output.=self::lineSt('<'.$tag.' href="'.$value.'" />');
					else
						$output.=self::line($tag,$value,$fl_flag,true);
				}
			}
// iTunes categories
			$itunes_cats=array('Category (iTunes)','Category II (iTunes)','Category III (iTunes)');
			$itunes_subcats=array('Subcategory (iTunes)','Subcategory II (iTunes)','Subcategory III (iTunes)');
			foreach($itunes_cats as $k=> $cat)
			{
				$subcat=$itunes_subcats[$k];
				if(!empty($rss_settings[$cat])&&!empty($rss_settings[$subcat]))
				{
					$output.=self::lineSt('<itunes:category text="'.Formatter::sth($rss_settings[$cat]).'">',$fl_flag);
					$output.=self::lineSt('<itunes:category text="'.Formatter::sth($rss_settings[$subcat]).'" />',$fl_flag);
					$output.=self::lineSt('</itunes:category>',$fl_flag);
				}
				elseif(!empty($rss_settings[$cat]))
					$output.=self::lineSt('<itunes:category text="'.Formatter::sth($rss_settings[$cat]).'"/>',$fl_flag);
			}
		}
		return $output;
	}

	public static function buildItems($rss_data,$fl_flag=false)
	{
		$output='';
		if(!empty($rss_data))
		{
			foreach($rss_data as $item)
			{
				$output.=self::lineSt('<item>',$fl_flag);
				foreach($item as $tag=> $value)
				{
					if(!is_array($value))
					{
						if($tag=='guid')
							$output.=self::lineSt('<'.$tag.' isPermaLink="true">'.$value.'</'.$tag.'>',$fl_flag);
						else
							$output.=self::line($tag,$value,$fl_flag);
					}
					else
					{
						if($tag=='enclosure'||$tag=='media:content')
						{
							$line='<'.$tag;
							foreach($value as $attr=> $v)
								$line.=' '.$attr.'="'.$v.'"'; $line.='/>';
							$output.=self::lineSt($line,$fl_flag);
						}
						elseif($tag=='category')
							$output.=self::lineSt('<'.$tag.' domain="'.$value['domain'].'">'.$value['value'].'</'.$tag.'>',$fl_flag);
					}
				}
				$output.=self::lineSt('</item>',$fl_flag);
			}
		}
		return $output;
	}

	public static function build($rss_data,$rss_settings,$page_charset,$page_url,$publish_date,$more_xmlns='',$fl_flag=false,$rss_url=''
	,$title='',$googleM=false)
	{
		$output=self::buildHeader($rss_settings,$page_charset,$page_url,$publish_date,$more_xmlns,$fl_flag,$rss_url,$title,$googleM)
			.self::buildItems($rss_data,$fl_flag)
			.self::lineSt('</channel>',$fl_flag)
			.self::lineSt('</rss>',$fl_flag);
		return $output;
	}

}

//checks if given data is valid
class Validator extends FuncHolder
{

	public static function checkImgSrc($imgScr)
	{
		$imgExtsAllowed=array('JPG','JPEG','PNG','GIF');
		$imgFile=substr($imgScr,strrpos($imgScr,'/')+1);
		$imgExt=substr($imgFile,strpos($imgFile,'.')+1);
		return !(strpos($imgExt,'.')!==false||!in_array(Formatter::strToUpper($imgExt),$imgExtsAllowed));
	}

	public static function valdiateCommentsForm($name_field,$content_field,$email_field,$forbid_urls,$email_enabled,$require_email,$lang_uc,$blocked_ip=false,$must_be_logged=false,$used_in_blog_comments=false,$content_field_required=true)
	{
		global $thispage_id,$user;

		$ccheck=isset($_POST['cc'])&&$_POST['cc']=='1';

		$errors=array();
		$content=$_POST[$content_field];
		$name=(!is_array($name_field)?$_POST[$name_field]:'');
		$code_not_allowed=$used_in_blog_comments?false:(strlen($content)!==(strlen(strip_tags($content))));
		$invalid_img_url=false;
		if($used_in_blog_comments)
		{
			$imgSources=CommentHandler::getTagAttr($content,'img','src',false);
			foreach($imgSources as $imgScr)
			{
				$relImgScr=Linker::relPathBetweenURLs($imgScr,Linker::currentPageUrl());
				$content=str_replace($imgScr,$relImgScr,$content);
				if(!self::checkImgSrc($imgScr))
					$invalid_img_url=true;
			}
		}
		else
			$content=strip_tags($content);
		$name=strip_tags($name);

		$mail=(isset($_POST[$email_field]))?Formatter::stripTags($_POST[$email_field]):'';

		Session::intStart('private');

		$is_logged=Cookie::isAdmin()||$user->userCookie();
		$logged=($must_be_logged&&$is_logged);
		if($must_be_logged&&!$logged)
		{
			$errors[]='er_error|'.$lang_uc['login on comments'];
			return $errors;
		}

		$ct_dec=html_entity_decode($content);
		$content_invalid=($forbid_urls&&((strpos($ct_dec,'http')!==false)||(strpos($ct_dec,'href')!==false)||(strpos($ct_dec,'www.')!==false)));
		$mail_valid=(!$is_logged&&$email_enabled&&$require_email&&!self::validateEmail($mail))?false:true;

		if($name=='')
		{
			if(is_array($name_field))
			{
				foreach($name_field as $v)
				{
					if(!isset($_POST[$v]) || $_POST[$v]=='' || ($v=='country' && $_POST[$v]=='Select'))
						$errors[]=($ccheck?$v.'|':'').$lang_uc['Required Field'];
				}
			}
			else
				$errors[]=($ccheck?$name_field.'|':'').$lang_uc['Required Field'];
		}
		if($content_field_required&&$content=='')
			$errors[]=($ccheck?$content_field.'|':'').$lang_uc['Required Field'];
		elseif($code_not_allowed)
			$errors[]=($ccheck?'er_error|':'')."Not allowed to include HTML or other code! ";
		elseif($content_invalid)
			$errors[]=($ccheck?'er_error|':'')."Not allowed to include url! ";

		if($invalid_img_url)
			$errors[]=($ccheck?'er_error|':'')."Img src provided is not allowed !";

		if(!$mail_valid)
			$errors[]=($ccheck?$email_field.'|'.$lang_uc['Email not valid']:$lang_uc['Email not valid']);

		$captcha_invalid=(!isset($thispage_id)&&self::isAbleToBuildImg())?!Captcha::isValid('code'):false;
		if($captcha_invalid&&(Cookie::isAdmin()||$user->userCookie()))
			$captcha_invalid=false;
		if($captcha_invalid)
			$errors[]=($ccheck?'code|':'').$lang_uc['Captcha Message'];
		if($blocked_ip)
			$errors[]=($ccheck?'er_error|':'').$lang_uc['your IP is blocked'];
		elseif(!empty($errors))
			$errors[]=($ccheck?'er_error|':'').$lang_uc['validation failed'];

		return $errors;
	}

	public static function validateEmail($email)
	{
		$isValid=true;
		$atIndex=strrpos($email,"@");
		if(is_bool($atIndex)&&!$atIndex)
			$isValid=false;
		else
		{
			$domain=substr($email,$atIndex+1);
			$local=substr($email,0,$atIndex);
			$localLen=strlen($local);
			$domainLen=strlen($domain);
			if($localLen<1||$localLen>64)
				$isValid=false; // local part length exceeded
			else if($domainLen<1||$domainLen>255)
				$isValid=false; // domain part length exceeded
			else if($local[0]=='.'||$local[$localLen-1]=='.')
				$isValid=false; // local part starts or ends with '.'
			else if(preg_match('/\\.\\./',$local))
				$isValid=false;  // local part has two consecutive dots
			else if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/',$domain))
				$isValid=false;// character not valid in domain part
			else if(preg_match('/\\.\\./',$domain))
				$isValid=false; // domain part has two consecutive dots
			else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local)))
			{ // character not valid in local part unless local part is quoted
				if(!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local)))
					$isValid=false;
			}
			if(function_exists('checkdnsrr')&&$isValid&&!(checkdnsrr($domain,"MX")||checkdnsrr($domain,"A")))
				$isValid=false;// domain not found in DNS
		}
		return $isValid;
	}

	public static function isAbleToBuildImg()
	{
		if(self::$f->captcha_size!='none' && function_exists('imagecreate')&&(function_exists('imagegif')||function_exists('imagejpeg')||function_exists('imagepng')))
			return true;
		else
			return false;
	}

	public static function checkProtection($page_info)  // returns: 1:unprotected, 2:protected, 3:partly protected, false:error
	{
		if(!is_array($page_info)||!isset($page_info[6]))
			return false;
		if($page_info[6]=='TRUE') //page is protected
		{
			if(isset($page_info[25])&&$page_info[25]=='PP')
				return 3;
			else
				return 2;
		}

		return 1;
	}

}

class Captcha extends FuncHolder
{

	public static function isRecaptchaPosted()
	{
		return (isset($_POST['recaptcha_challenge_field'])&&isset($_POST['recaptcha_response_field']));
	}

	public static function isValid($inputName='captchacode')
	{
		$ccheck=isset($_POST['cc'])&&$_POST['cc']=='1'; //needed to know if it's check or post (not sure why it was outside before)
		//and also it's still outside for compatibility.
		if(self::$f->reCaptcha)  //we have reCaptcha here?
		{
			require_once('recaptchalib.php');
			$privatekey='6Ld8cskSAAAAAOCdGESm17P58trbl2PI-O5-BIry';
			$re_chall=isset($_POST['recaptcha_challenge_field'])?$_POST['recaptcha_challenge_field']:'';
			$re_resp=isset($_POST['recaptcha_response_field'])?$_POST['recaptcha_response_field']:'';
			$resp=recaptcha_check_answer($privatekey,$_SERVER['REMOTE_ADDR'],$re_chall,$re_resp);
			if($ccheck) //pre-check, if valid - set session
			{
				if($resp->is_valid)
					Session::setVar(self::$f->cap_id,md5('verified')); //indicator for the actual check
				return ($resp->is_valid);
			}
			else
			{//actual check
				if(Session::isSessionSet(self::$f->cap_id)&&Session::getVarStr(self::$f->cap_id)==md5('verified'))
				{ //looks like it was already validated in the pre-check.
					Session::unsetVar(self::$f->cap_id);   //we don't neet this anymore
					return true;
				}
				else
					return $resp->is_valid;  //no pre check (blog comment post, etc) just return the check
			}
		}
		else
		{
			$captcha=Session::getVar(self::$f->cap_id);
			if($captcha==''||$captcha==NULL)
				$check_failed=true;
			else
				$check_failed=(!isset($_POST[$inputName])||(md5(strtoupper($_POST[$inputName]))!=$captcha));
			return !$check_failed;
		}
	}

	public static function build($hintId='code',$verificationLabel='verification code',$label_class='rvts8',$desc_class='desc',$theme=false)
	{
		$output='';
		if(self::$f->captcha_size!='recaptcha')
		{
			if(self::$f->captcha_size=='sliding captcha')
			{
				$output='
						<label class="desc"><span id="fcaptchatitle" class="label_title">'.$verificationLabel.'</span><span class="req">*</span></label>
						<div>
							<input class="text" type="text" name="captchacode" size="4" maxlength="4" value="" autocomplete="off">&nbsp;
							<span class="captcha"></span>
							<span id="'.$hintId.'" class="rvts12 frmhint"></span>
						</div>
				';
				return $output;
			}
			else
				$output.='<label for="code" class="'.$desc_class.'"><span class="'.$label_class.'">'.$verificationLabel.'</span><span class="req req_on" id="req_1"> * </span></label><div>
					<input class="ed_captcha '.($theme?'text':'signguest_input input1').'" type="text" name="code" id="signguest_code" value="" autocomplete="off" size="4" maxlength="4">';
		}
		$output.='&nbsp<span class="captcha"></span></div><span id="'.$hintId.'" class="rvts12 frmhint"></span>';

		return $output;
	}

	public static function includeCaptchaJs($output,$relPath,&$page_scripts,&$dependencies)
	{

			if(strpos($output,'class="captcha')!==false && strpos($output,(self::$f->captcha_size=='sliding captcha'?'function loadSlidingCaptcha(':'function(){loadCaptcha('))===false)
				$page_scripts.=str_replace('%PATH%',$relPath,str_replace('loadReCaptcha();','',self::$f->captchajs));

			if(self::$f->captcha_size=='sliding captcha')
			{
				$dependencies[]='jquery-ui.css';
				$dependencies[]='jquery-ui.min.js';
			}
	}
}

class Password extends FuncHolder
{

	public static function checkStrenght($pwd,$thispage_id,$get_arr_only=false,$is_admin=false)
	{
		$lang=CA::getMyprofileLabels($thispage_id);
		$str=array(
			'short'=>$lang['short pwd'],//1
			'weak'=>$lang['weak'],//2
			'average'=>$lang['average'],//3
			'good'=>$lang['good'],//4
			'strong'=>$lang['strong'],//5
			'forbidden'=>$lang['forbidden']
		);
		if($get_arr_only)
			return $str; //added this to define the labels at one place only
//only longer than 8 chars
		$weak_passwords=array('firebird','password','12345678','steelers','mountain','computer','baseball','xxxxxxxx','football','qwertyui','jennifer','danielle','sunshine','starwars','whatever','nicholas','swimming','trustno1','midnight','princess','startrek','mercedes','superman','bigdaddy','maverick','einstein','dolphins','hardcore','redwings','cocacola','michelle','victoria','corvette','butthead','marlboro','srinivas','internet','redskins','11111111','access14','rush2112','scorpion','iloveyou','samantha','mistress');
		$ret_num_min=3;
		if(in_array($pwd,$weak_passwords))
		{
			$msg=$str['forbidden'];
			$ret_num=2;
		}  //block weak passwords
		else
		{
			$msg='';
			$ret_num=0;
			if(preg_match('/^.{1,7}$/',$pwd))
			{
				$msg=$str['short'];
				$ret_num=1;
			}
			elseif(preg_match('/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/',$pwd))
			{
				$msg=$str['strong'];
				$ret_num=5;
			}
			elseif(preg_match('/^.{12,}$/',$pwd))
			{
				$msg=$str['strong'];
				$ret_num=5;
			}
			elseif(preg_match('/(?=^.{8,}$)(?=.*\d)(?![.\n])(?=.*[a-zA-Z]).*$/',$pwd))
			{
				$msg=$str['good'];
				$ret_num=4;
			}
			elseif(preg_match('/^[^0-9]{8,11}$/',$pwd))
			{
				$msg=$str['average'];
				$ret_num=3;
			}
			elseif(preg_match('/^[0-9]{8,}$/',$pwd))
			{
				$msg=$str['weak'];
				$ret_num=2;
			}
			else
				$msg="That's not a password!";
		}
		$is_pass_ok=$is_admin?true:($ret_num>=$ret_num_min);

		return array('num'=>$ret_num,'msg'=>$msg,'pass_is_ok'=>$is_pass_ok);
	}

	public static function showMeter($pass_levels,$pos='right',$mt='6',$id='')
	{
		$outDivStart='<div class="out_pass_div'.$id.'" style="height:4px;position:relative;">';
		$innDivStart='<div class="inn_pass_div'.$id.'" style="height:4px;margin-top:'.$mt.'px">';
		$outDivEnd=$innDivEnd='</div>';
		$txtSpan='<span id="pwdptext_%s'.$id.'" style="display:none;line-height:4px;position:absolute;top:0;'.$pos.':0;" class="pass_progress_text'.$id.' rvts8 field_label">%s</span>';

		$output=$outDivStart.$innDivStart.$innDivEnd;
		foreach($pass_levels as $pk=>$pv)
			$output.=sprintf($txtSpan,$pk,$pv);

		return $output.$outDivEnd;
	}

}

class Cookie extends FuncHolder
{

	public static function entryID($page_id,$prefix)
	{
		return $prefix.self::$f->proj_id.$page_id;
	}

	public static function entryIsCookie($entry_id,$page_id,$prefix)
	{
		$cookie_id=self::entryID($page_id,$prefix);
		$name=$prefix.$page_id.$entry_id;
		if(isset($_COOKIE[$name]))
		{
			self::setEntryCookie($entry_id,$page_id,$prefix);
			setcookie($name,'',time()-3600);
		}
		if(isset($_COOKIE[$cookie_id]))
			return (strpos($_COOKIE[$cookie_id],$entry_id.'_')!==false);
		else
			return false;
	}

	public static function setEntryCookie($entry_id,$page_id,$prefix)
	{
		$timestamp=time();
		$expire_timestamp=mktime(23,59,59,date('n',$timestamp),date('j',$timestamp),2037);
		$cookie_id=self::entryID($page_id,$prefix);
		$cookie=(isset($_COOKIE[$cookie_id])?$_COOKIE[$cookie_id]:'');
		$cookie=substr($entry_id.'_'.$cookie,0,3999);
		setcookie($cookie_id,$cookie,$expire_timestamp);
		$_COOKIE[$cookie_id]=$cookie;
	}

	public static function isAdmin()
	{
		return User::isLogged(self::$f->admin_cookieid);
	}

	public static function getAdmin()
	{
		return Session::getVarStr(self::$f->admin_cookieid);
	}

	public static function setAdmin($c)
	{
		Session::setVar(self::$f->admin_cookieid,$c);
	}

	public static function setLongtimeLogin($user,$expire_time)
	{
		global $db;
		if(CA::getDBSettings($db,'cookie_login'))
		{
			$t_hash=md5(time());
			setcookie(self::$f->login_cb_str.'usr'.self::$f->proj_id,$user['username'],$expire_time,'/');
			setcookie(self::$f->login_cb_str.'t'.self::$f->proj_id,$t_hash,$expire_time,'/');
			$hash=self::makeCookieHash($user,$t_hash);
			setcookie(self::$f->login_cb_str.'hash'.self::$f->proj_id,$hash,$expire_time,'/');
			$db_hash=self::makeDBHash($user['username'],$hash);

			$r=self::$f->db->query_insert('login_cookiebased',array(
				'id'=>null,
				'hash'=>$db_hash,
				'exp'=>Date::buildMysqlTime($expire_time)),true);
			if(!$r)
					self::checkLoginCbTable();
		}
	}

	public static function checkLoginCookies()
	{
		$is_set_common_data=(isset($_COOKIE[self::$f->login_cb_str.'hash'.self::$f->proj_id])&&isset($_COOKIE[self::$f->login_cb_str.'t'.self::$f->proj_id])&&isset($_COOKIE[self::$f->login_cb_str.'usr'.self::$f->proj_id]));

		if($is_set_common_data)
		{
			$ret=array(
				'username'=>$_COOKIE[self::$f->login_cb_str.'usr'.self::$f->proj_id],
				'time'=>$_COOKIE[self::$f->login_cb_str.'t'.self::$f->proj_id],
				'hash'=>$_COOKIE[self::$f->login_cb_str.'hash'.self::$f->proj_id]);
			return $ret;
		}
		else
			return false;
	}

	//login cookiebased functions
	public static function makeCookieHash($user,$t_hash)
	{
		return sha1(self::$f->proj_id.$t_hash.$user['username'].$user['password'].$user['status']);
	}

	public static function makeDBHash($username,$cookie_hash)
	{
		return sha1(self::$f->proj_id.'for'.$username.'with'.$cookie_hash);
	}

	public static function checkLoginCbTable()
	{
		global $db;
		include_once('data.php');
		create_cb_login_db($db,'login_cookiebased');
	}

	public static function getLangCookie()
	{
		global $user;
		$lang='';
		Session::intStart('private');
		if($user->userCookie())
			$logged_user=$user->getUserCookie();
		elseif(self::isAdmin())
			$logged_admin=self::getAdmin();
		if(isset($logged_user)&&isset($_COOKIE[$logged_user.'_lang']))
			$lang=Formatter::strToUpper(Formatter::stripTags($_COOKIE[$logged_user.'_lang']));
		elseif(isset($logged_admin)&&isset($_COOKIE['ca_lang']))
			$lang=Formatter::strToUpper(Formatter::stripTags($_COOKIE['ca_lang']));
		return $lang;
	}

}

class Date extends FuncHolder
{

	public static function dateIsBetween($from,$to,$date = 'now')
	{
		$date = is_int($date) ? $date : strtotime($date);
		$from = is_int($from) ? $from : strtotime($from);
		$to = is_int($to) ? $to : strtotime($to);
		return ($date > $from) && ($date < $to);
	}

	public static function get_date_format($pageLang,$mode='long')
	{
		$lid=array_search($pageLang,self::$f->inter_languages_a);
		$params=str_replace('DD, d MM, yy','dd mmmm, yyyy',self::$f->date_format_a[$lid]);
		if($mode=='long')
				$params.=(self::$f->time_format_a[$lid]==12)?' h:i A':' H:i';
		return $params;
	}

	public static function format_date($timestamp,$pageLang,$month_name,$day_name,$mode='long',$params='')
	{
		if($params=='')
			$params=Date::get_date_format($pageLang,$mode);

		$res=$timestamp<0?'   ':self::format($timestamp,$params,$month_name,$day_name,$mode);
		return $res;
	}

	public static function dp($month_name,$ts)
	{
		$mon=date('n',$ts);
		$mon_name=$month_name[$mon-1];
		return $mon_name.date(' j, Y',$ts);
	}

	public static function is_dateset($v,$p)
	{
		return (isset($v[$p]) && $v[$p]!='0000-00-00 00:00:00');
	}

	public static function format($timestamp,$params,$month_names,$day_names,$mode,$use_tzone=true) # mode --> short, long
	{
		$res='';
		$ts=($use_tzone)?self::tzone($timestamp):$timestamp;

		if(!empty($params)) //params conversion to php style
		{
			$params=str_replace(
				array('dddd','ddd','DDDD','DDD' ,'dd' ,'d','mmmm','mmm','MMMM','MMM' ,'MM' ,'mm' ,'m','yyyy','yy','hh','nn','ss'),
				array('XX3' ,'XX4','XX32','XX42','XX5','j','XX2' ,'XX1','XX22','XX12','XX6','XX6','n','Y'   ,'y' ,'H' ,'i' ,'s'),
				$params);

			$res=str_replace('XX5','d',$params);
			$res=str_replace('XX6','m',$res);
			$res=date($res,$ts);
			$res=str_replace('XX12',Formatter::strToUpper(Formatter::mySubstr($month_names[date('n',$ts)-1],0,3)),$res);
			$res=str_replace('XX22',Formatter::strToUpper($month_names[date('n',$ts)-1]),$res);
			$res=str_replace('XX42',Formatter::strToUpper(Formatter::mySubstr($day_names[date('w',$ts)],0,3)),$res);
			$res=str_replace('XX32',Formatter::strToUpper($day_names[date('w',$ts)]),$res);
			$res=str_replace('XX1',Formatter::mySubstr($month_names[date('n',$ts)-1],0,3),$res);
			$res=str_replace('XX2',$month_names[date('n',$ts)-1],$res);
			$res=str_replace('XX4',Formatter::mySubstr($day_names[date('w',$ts)],0,3),$res);
			$res=str_replace('XX3',$day_names[date('w',$ts)],$res);
		}
		else
			$res=($mode=='short')?$month_names[date('n',$ts)-1].date(', Y',$ts):$month_names[date('n',$ts)-1].date(' d, Y',$ts);
		return $res;
	}

	public static function formatTimeSql($time,$time_format,$mode='short')
	{
		return self::formatTime(strtotime($time),$time_format,$mode);
	}

	public static function formatTime($timestamp,$time_format,$mode='short') # mode --> short, long
	{
		$ts=self::tzone($timestamp);
		$res=($mode=='short')?($time_format==12?date(' h:i A',$ts):date(' H:i',$ts)):($time_format==12?date(' d, Y h:i A',$ts):date(' d, Y H:i',$ts));
		return $res;
	}

	public static function tzoneNow()
	{
		$dt=date("Y-m-d_H:i:s",self::tzone(time()));
		return $dt;
	}

	public static function tzoneSql($date,$reversed=false)
	{
		return self::tzone(strtotime($date),$reversed);
	}

	public static function tzone($date,$reversed=false)
	{
		if(self::$f->tzone_offset==-10000)
		{
			if(empty(self::$f->ca_settings))
			{
				$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));
				CA::fetchDBSettings($db);
			}
			self::$f->tzone_offset=isset(self::$f->ca_settings['tzone_offset'])?intval(self::$f->ca_settings['tzone_offset']):0;
		}

		$fixed_date=$date;
		if(self::$f->tzone_offset!=0)
		{
			if($reversed)
				$fixed_date=$date-self::$f->tzone_offset*60*60;
			else
				$fixed_date=$date+self::$f->tzone_offset*60*60;
		}

		return $fixed_date;
	}

	public static function daysInFeb($year)
	{
		if($year<0)
			$year++;
		$year+=4800;
		if(($year%4)==0)
		{
			if(($year%100)==0)
			{
				if(($year%400)==0)
					return(29);
				else
					return(28);
			}
			else
				return(29);
		}
		else
			return(28);
	}

	public static function daysInMonth($month,$year)
	{
		if($month==0) //probably curr month is Jan and Dec of last year is checked
		{
			$month=12;
			$year -= 1;
		}
		if($month==2)
			return self::daysInFeb($year);
		else
		{
			if($month==1||$month==3||$month==5||$month==7||$month==8||$month==10||$month==12)
				return(31);
			else
				return(30);
		}
	}

	public static function pareseInputDate($fname,$time_format,$month_name)
	{
		if(isset($_POST[$fname]))
		{
			$postFname=trim($_POST[$fname]);
			$gethm=isset($_POST[$fname.'_hour']);
			$postFnameHour=$gethm?trim($_POST[$fname.'_hour']):'0';
			$postFnameMin=$gethm?trim($_POST[$fname.'_min']):'0';
			$postFnameAmPm=($gethm&&$time_format==12&&isset($_POST[$fname.'_ampm']))?trim($_POST[$fname.'_ampm']):'';
			list($tt,$yy)=explode(',',$postFname);
			list($mm,$dd)=explode(' ',$tt);
			$m=array_search($mm,$month_name);
			$start_hour=intval($time_format==12?($postFnameAmPm=='AM'?$postFnameHour:($postFnameHour+12)):($postFnameHour));
			$date=mktime($start_hour,intval($postFnameMin),0,($m+1),intval($dd),intval($yy));
			$date=self::tzone($date,true);
		}
		else
			$date=self::tzone(time());
		return $date;
	}

	public static function buildMysqlTime($ts='',$from_ico=false)
	{
		if($from_ico)
			return str_replace(array('T','+00:00'),array(' ',''),$ts);
		elseif($ts!='')
			return date('Y-m-d H:i:s',$ts);
		else
			return date('Y-m-d H:i:s');
	}

	public static function dateAgo($ago,$lang_l)
	{
		$d=time()-strtotime($ago);
		$i=array('year'=>31556926,'month'=>2629744,'week'=>604800,'day'=>86400,'hour'=>3600,'minute'=>60);

		if($d<60)
			return $d.' '.$lang_l['seconds'];
		if($d>=60 && $d<$i['hour'])
		{
			$d=floor($d/$i['minute']);
			return $d==1?$d.' '.$lang_l['minute']:$d.' '.$lang_l['minutes'];
		}
		elseif($d>=$i['hour'] && $d<$i['day'])
		{
			$d=floor($d/$i['hour']);
			return $d==1?$d.' '.$lang_l['hour']:$d.' '.$lang_l['hours'];
		}
		elseif($d>=$i['day'] && $d<$i['week'])
		{
			$d=floor($d/$i['day']);
			return $d==1?$d.' '.$lang_l['day']:$d.' '.$lang_l['days'];
		}
		elseif($d>=$i['week'] && $d<$i['month'])
		{
			$d=floor($d/$i['week']);
			return $d==1?$d.' '.$lang_l['week']:$d.' '.$lang_l['weeks'];
		}
		elseif($d>=$i['month'] && $d<$i['year'])
		{
			$d=floor($d/$i['month']);
			return $d==1?$d.' '.$lang_l['month']:$d.' '.$lang_l['months'];
		}
		elseif($d>=$i['year'])
		{
			$d=floor($d/$i['year']);
			return $d==1?$d.' '.$lang_l['year']:$d.' '.$lang_l['years'];
		}
	}

	public static function isCurrentDay($day,$mon,$year) //  current day check
	{
		$current_date=getdate(self::tzone(time()));
		$currday=$current_date['mday'];
		$currmon=$current_date['mon'];
		$curryear=$current_date['year'];
		if($day==$currday&&$mon==$currmon&&$year==$curryear)
			return true;
		else
			return false;
	}

	public static function microtimeFloat()
	{
		list($usec,$sec)=explode(" ",microtime());
		return ((float)$usec+(float)$sec);
	}

}

class Mobile extends FuncHolder
{
//taken from Mobile_detect.php
	protected static $tabletDevices = array(
		'iPad'              => 'iPad|iPad.*Mobile',
		'NexusTablet'       => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
		'SamsungTablet'     => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1003|GT-P1010|GT-P3105|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3108|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P7501|GT-N5100|GT-N5105|GT-N5110|SHV-E140K|SHV-E140L|SHV-E140S|SHV-E150S|SHV-E230K|SHV-E230L|SHV-E230S|SHW-M180K|SHW-M180L|SHW-M180S|SHW-M180W|SHW-M300W|SHW-M305W|SHW-M380K|SHW-M380S|SHW-M380W|SHW-M430W|SHW-M480K|SHW-M480S|SHW-M480W|SHW-M485W|SHW-M486W|SHW-M500W|GT-I9228|SCH-P739|SCH-I925|GT-I9200|GT-I9205|GT-P5200|GT-P5210|GT-P5210X|SM-T311|SM-T310|SM-T310X|SM-T210|SM-T210R|SM-T211|SM-P600|SM-P601|SM-P605|SM-P900|SM-P901|SM-T217|SM-T217A|SM-T217S|SM-P6000|SM-T3100|SGH-I467|XE500|SM-T110|GT-P5220|GT-I9200X|GT-N5110X|GT-N5120|SM-P905|SM-T111|SM-T2105|SM-T315|SM-T320|SM-T320X|SM-T321|SM-T520|SM-T525|SM-T530NU|SM-T230NU|SM-T330NU|SM-T900|XE500T1C|SM-P605V|SM-P905V|SM-P600X|SM-P900X|SM-T210X|SM-T230|SM-T230X|SM-T325|GT-P7503|SM-T531|SM-T330|SM-T530|SM-T705C|SM-T535|SM-T331|SM-T800',
		'Kindle'            => 'Kindle|Silk.*Accelerated|Android.*\b(KFTT|KFOTE)\b',
		'SurfaceTablet'     => 'Windows NT [0-9.]+; ARM;',
		'AsusTablet'        => '^.*PadFone((?!Mobile).)*$|Transformer|TF101|TF101G|TF300T|TF300TG|TF300TL|TF700T|TF700KL|TF701T|TF810C|ME171|ME301T|ME302C|ME371MG|ME370T|ME372MG|ME172V|ME173X|ME400C|Slider SL101|\bK00F\b|TX201LA',
		'BlackBerryTablet'  => 'PlayBook|RIM Tablet',
		'HTCtablet'         => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
		'MotorolaTablet'    => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
		'NookTablet'        => 'Android.*Nook|NookColor|nook browser|BNRV200|BNRV200A|BNTV250|BNTV250A|LogicPD Zoom2',
		'AcerTablet'        => 'Android.*; \b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71)\b',
		'ToshibaTablet'     => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)|TOSHIBA.*FOLIO',
		'LGTablet'          => '\bL-06C|LG-V900|LG-V909\b',
		'FujitsuTablet'     => 'Android.*\b(F-01D|F-05E|F-10D|M532|Q572)\b',
		'PrestigioTablet'   => 'PMP3170B|PMP3270B|PMP3470B|PMP7170B|PMP3370B|PMP3570C|PMP5870C|PMP3670B|PMP5570C|PMP5770D|PMP3970B|PMP3870C|PMP5580C|PMP5880D|PMP5780D|PMP5588C|PMP7280C|PMP7280|PMP7880D|PMP5597D|PMP5597|PMP7100D|PER3464|PER3274|PER3574|PER3884|PER5274|PER5474|PMP5097CPRO|PMP5097|PMP7380D',
		'LenovoTablet'      => 'IdeaTab|S2110|S6000|K3011|A3000|A1000|A2107|A2109|A1107',
		'YarvikTablet'      => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
		'MedionTablet'      => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
		'ArnovaTablet'      => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
		'IRUTablet'         => 'M702pro',
		'MegafonTablet'     => 'MegaFon V9|ZTE V9',
		'AllViewTablet'           => 'Allview.*(Viva|Alldro|City|Speed|All TV|Frenzy|Quasar|Shine|TX1|AX1|AX2)',
		'ArchosTablet'      => 'Android.*ARCHOS|\b101G9\b|\b80G9\b',
		'AinolTablet'       => 'NOVO7|Novo7Aurora|Novo7Basic|NOVO7PALADIN',
		'SonyTablet'        => 'Sony.*Tablet|Xperia Tablet|Sony Tablet S|SO-03E|SGPT12|SGPT121|SGPT122|SGPT123|SGPT111|SGPT112|SGPT113|SGPT211|SGPT213|SGP311|SGP312|SGP321|EBRD1101|EBRD1102|EBRD1201',
		'CubeTablet'        => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)|CUBE U8GT',
		'CobyTablet'        => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7015|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8024|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
		'MIDTablet'         => 'M9701|M9000|M9100|M806|M1052|M806|T703|MID701|MID713|MID710|MID727|MID760|MID830|MID728|MID933|MID125|MID810|MID732|MID120|MID930|MID800|MID731|MID900|MID100|MID820|MID735|MID980|MID130|MID833|MID737|MID960|MID135|MID860|MID736|MID140|MID930|MID835|MID733',
		'SMiTTablet'        => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
		'RockChipTablet'    => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
		'TelstraTablet'     => 'T-Hub2',
		'FlyTablet'         => 'IQ310|Fly Vision',
		'bqTablet'          => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)',
		'HuaweiTablet'      => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
		'NecTablet'         => '\bN-06D|\bN-08D',
		'PantechTablet'     => 'Pantech.*P4100',
		'BronchoTablet'     => 'Broncho.*(N701|N708|N802|a710)',
		'VersusTablet'      => 'TOUCHPAD.*[78910]|\bTOUCHTAB\b',
		'ZyncTablet'        => 'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900',
		'PositivoTablet'    => 'TB07STA|TB10STA|TB07FTA|TB10FTA',
		'NabiTablet'        => 'Android.*\bNabi',
		'KoboTablet'        => 'Kobo Touch|\bK080\b|\bVox\b Build|\bArc\b Build',
		'DanewTablet'       => 'DSlide.*\b(700|701R|702|703R|704|802|970|971|972|973|974|1010|1012)\b',
		'TexetTablet'       => 'NaviPad|TB-772A|TM-7045|TM-7055|TM-9750|TM-7016|TM-7024|TM-7026|TM-7041|TM-7043|TM-7047|TM-8041|TM-9741|TM-9747|TM-9748|TM-9751|TM-7022|TM-7021|TM-7020|TM-7011|TM-7010|TM-7023|TM-7025|TM-7037W|TM-7038W|TM-7027W|TM-9720|TM-9725|TM-9737W|TM-1020|TM-9738W|TM-9740|TM-9743W|TB-807A|TB-771A|TB-727A|TB-725A|TB-719A|TB-823A|TB-805A|TB-723A|TB-715A|TB-707A|TB-705A|TB-709A|TB-711A|TB-890HD|TB-880HD|TB-790HD|TB-780HD|TB-770HD|TB-721HD|TB-710HD|TB-434HD|TB-860HD|TB-840HD|TB-760HD|TB-750HD|TB-740HD|TB-730HD|TB-722HD|TB-720HD|TB-700HD|TB-500HD|TB-470HD|TB-431HD|TB-430HD|TB-506|TB-504|TB-446|TB-436|TB-416|TB-146SE|TB-126SE',
		'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
		'GalapadTablet'     => 'Android.*\bG1\b',
		'Hudl'							=> 'Hudl HT7S3',
		'MicromaxTablet'    => 'Funbook|Micromax.*\b(P250|P560|P360|P362|P600|P300|P350|P500|P275)\b',
		'KarbonnTablet'     => 'Android.*\b(A39|A37|A34|ST8|ST10|ST7|Smart Tab3|Smart Tab2)\b',
		'GUTablet'          => 'TX-A1301|TX-M9002|Q702',
		'GenericTablet'     => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|BNTV250A|MID-WCDMA|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|hp-tablet|rk30sdk',
	);
//end

	public static function isTablet($userAgent = null)
	{
		foreach(self::$tabletDevices as $k=>$regex)
		{
			$regex=str_replace('/','\/',$regex);
			if((bool) preg_match('/'.$regex.'/is',$userAgent ) )
				return $k;
		}
		return false;
	}

	public static function detect($mode)
	{
		$res=false;
		$fvc=false;

		if(isset($_REQUEST['fullview']))
		{
			setcookie('use_fullview','1',0,'/');
			$fvc=true;
		}
		elseif(isset($_COOKIE['use_fullview']))
			$fvc=true;
		if(isset($_REQUEST['mobileview']))
		{
			setcookie("use_fullview","",time()-3600,'/');
			$fvc=false;
		}
		if(!$fvc&&$mode!='0'&&isset($_SERVER['HTTP_USER_AGENT']))
		{
			$us_agent=$_SERVER['HTTP_USER_AGENT'];
			/*if($mode=='1')
			{
				if(strpos($us_agent,'iPhone')!==false||strpos($us_agent,'iPod')!==false)
					$res=true;
			}
			else */
			if($mode=='2' || $mode=='1')
			{
				if(self::isTablet($us_agent)!==false)
					$res=false;
				elseif(preg_match('/Googlebot-Mobile|android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$us_agent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($us_agent,0,4)))
					$res=true;
			}
		}
		return $res;
	}
}

//detects and returns the required data
class Detector extends FuncHolder
{

	public static function getMime($ext,$default='audio/mpeg3')
	{
		$ext=strtolower($ext);
		if(strpos($ext,'tif')!==false)
			$mime='image/tiff';
		elseif(strpos($ext,'png')!==false)
			$mime='image/png';
		elseif(strpos($ext,'gif')!==false)
			$mime='image/gif';
		elseif(strpos($ext,'jp')!==false)
			$mime='image/jpeg';
		elseif(strpos($ext,'pdf')!==false)
			$mime='application/pdf';
		elseif(strpos($ext,'swf')!==false)
			$mime='application/x-shockwave-flash';
		elseif(strpos($ext,'doc')!==false)
			$mime='application/msword';
		elseif(strpos($ext,'wav')!==false)
			$mime='audio/wav';
		elseif(strpos($ext,'avi')!==false)
			$mime='video/avi';
		elseif(strpos($ext,'mp4')!==false)
			$mime='video/mp4';
		elseif(strpos($ext,'zip')!==false)
			$mime='application/zip';
		elseif(strpos($ext,'rar')!==false)
			$mime='application/x-rar-compressed';
		else
			$mime=$default;
		return $mime;
	}

	public static function defineOS($agent)
	{
		$os=array(
			'1'=>'Windows 95|Win95|Windows_95',
			'2'=>'Windows 98|Win98',
			'4'=>'Windows NT 5.0|Windows 2000',
			'5'=>'Windows NT 5.1|Windows XP',
			'6'=>'Windows NT 5.2',
			'7'=>'Windows NT 6.0',
			'8'=>'Linux|X11|Ubuntu|Debian|FreeBSD',
			'9'=>'Mac_PowerPC|Macintosh',
			'11'=>'Windows NT 6.1',
			'12'=>'iPhone|Ipod|Ipad',
			'13'=>'nuhk|Googlebot|Yammybot|Openbot|Slurp\/cat|msnbot|ia_archiver',
			'14'=>'Android',
			'15'=>'Windows NT 6.2|Windows NT 6.3',
			'16'=>'BlackBerry|RIM Tablet OS',
			'17'=>'Windows NT 10.0'
		);
		foreach($os as $k=> $v)
		{
			if(preg_match('/'.$v.'/i',$agent))
				return self::$f->os[intval($k)];
		}
		return 'Unknown';
	}

	public static function readUserAgent($agent,$host)
	{
		$result=array();
		$p=array_search(self::defineOS($agent),self::$f->os);
		$b='0'; //Unknown
		if((strpos($agent,'Edge')!==false))
			$b='33';
		elseif(strpos($agent,'Trident/7.0;') !== false  && strpos($agent,'rv:11.0') !== false)
			$b='32';
		elseif(strpos($agent,'MSIE')!==false)
		{
			if(strpos($agent,'MSIE 10')!==false)
				$b='30';
			elseif(strpos($agent,'MSIE 9')!==false)
				$b='20';
			elseif(strpos($agent,'MSIE 8')!==false)
				$b='19';
			elseif(strpos($agent,'MSIE 7')!==false&&strpos($agent,'Trident/4.0')!==false)
				$b='19';
			elseif(strpos($agent,'MSIE 7')!==false)
				$b='10';
			elseif(strpos($agent,'MSIE 6')!==false)
				$b='9';
			else
				$b='1';
		}
		elseif(strpos($agent,'Firefox')!==false)
				$b='3';
		elseif(strpos($agent,'Opera')!==false)
			$b='2';
		elseif(strpos($agent,'Chrome')!==false)
			$b='18';
		elseif(strpos($agent,'Mercury')!==false)
			$b='31';
		elseif(strpos($agent,'Safari')!==false)
			$b='6';
		elseif((strpos($agent,'Konqueror')!==false)||(strpos($agent,'KHTML')!==false))
			$b='7';
		elseif((strpos($host,'googlebot.com')!==false))
			$b='4';

		$result['platform']=$p;
		$result['browser']=$b;
		return $result;
	}

	public static function getRemoteHost()
	{
		$host='unknown';
		if(isset($_SERVER['REMOTE_HOST']))
			$host=trim($_SERVER['REMOTE_HOST']);
		elseif(isset($_SERVER['REMOTE_ADDR']))
			$host=gethostbyaddr($_SERVER['REMOTE_ADDR']);
		return $host;
	}

	public static function getReferer($frames_mode=0)
	{
		$referer='NA';
		$http_ref=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'NA';
		if(!$frames_mode)
			$http_ref=isset($_REQUEST['referrer'])?Formatter::stripTags($_REQUEST['referrer']):$http_ref;
		if($http_ref!=='NA')
		{
			$h=Linker::getHost();
			$referer=(strpos($http_ref,$h)===0)?substr($http_ref,strpos($http_ref,$h)+strlen($h)):$http_ref;
		}
		return $referer;
	}

	public static function getIP()
	{
		if(isset($_SERVER["HTTP_CLIENT_IP"]))
			return $_SERVER["HTTP_CLIENT_IP"];
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		if(isset($_SERVER["HTTP_X_FORWARDED"]))
			return $_SERVER["HTTP_X_FORWARDED"];
		if(isset($_SERVER["HTTP_FORWARDED_FOR"]))
			return $_SERVER["HTTP_FORWARDED_FOR"];
		if(isset($_SERVER["HTTP_FORWARDED"]))
			return $_SERVER["HTTP_FORWARDED"];
		if(isset($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];
		if(isset($_SERVER["HTTP_PC_REMOTE_ADDR"]))
			return $_SERVER["HTTP_PC_REMOTE_ADDR"];
		return("unknown");
	}

	public static function check_cross_domain($login=false)
	{
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$ref_url=parse_url($_SERVER['HTTP_REFERER']);
			if($ref_url['host']!=Formatter::GFS($_SERVER['HTTP_HOST'],'',':'))
				return false;
			else
				return true;
		}
		else if($login)
			return true;
		else
			return false;
	}

	public static function defineSourcePage($root='../',$lang='',$use_mobile=false,$dont_use_php=false)
	{
		$result='';
		self::$f->template_source_f=$root.self::$f->template_source;
		if($use_mobile)
			self::$f->template_source_f=strpos(self::$f->template_source_f,'../')!==false?Formatter::strLReplace('/','/i_',self::$f->template_source_f):'i_'.self::$f->template_source_f;
		if(file_exists(self::$f->template_source_f))
			$result=self::$f->template_source_f;
		else
		{
			if(isset($_REQUEST['id']))
				$id=intval($_REQUEST['id']);
			$sitemap_arr=CA::getSitemap($root);

			if(isset($id)) //getting current page
			{
				$data=CA::getPageParamsArray($sitemap_arr,$id);
				if(!empty($data))
					$result=self::checkSourcePage($data,$id,$use_mobile);
				if(strpos($result,'.php')!==false && $dont_use_php) $result='';
			}
			if($result=='') //getting any page in current language
			{
				$found = false;
				foreach($sitemap_arr as $k=>$data)
				{
					if($found) break; //page already found, no need to look anymore
					if($lang!='')
					{
						if(isset($data[22])&&($data[22]==$lang))
							$result=self::checkSourcePage($data,$data[10],$use_mobile);
						if($result != '' && (strpos($result,'.php')!==false || strpos($result,'.html')!==false || strpos($result,'.htm')!==false) )
							$found = true; // if result is 'like a file' we seems to have found it. (it was javascript:void() in one site ?!?!)
					}
					else if(isset($data[10]))
						$result=self::checkSourcePage($data,$data[10],$use_mobile);
					if(strpos($result,'.php')!==false && $dont_use_php)
						$result='';
					elseif(strpos($result,'?')!==false)
						$result='';
					if($result!='')
						break;
				}
			}
			if($result=='') //getting any page
			{
				foreach($sitemap_arr as $k=>$data)
				{
					if(isset($data[10]))
						$result=self::checkSourcePage($data,$data[10],$use_mobile);
					if($result!='')
						break;
				}
			}

			if($result=='') //still nothing=> no html page in the project=> we use 1st php page as template
			{
				foreach($sitemap_arr as $k=>$data)
				{
					if(isset($data[10]) && strpos($data[1],'.php')!==false) //just for sure the page is php
					{
						$result=$data[1];
						break;
					}
				}
			}
		}
		return $result;
	}

	public static function checkSourcePage($data,$id,$use_mobile=false,$check_in_normal=false)
	{
		$fname='';
		$used_for_mob_search=isset($_REQUEST['mobile_search'])&&$_REQUEST['mobile_search']==1;
		if(strpos($data[1],'http:')===false&&strpos($data[1],'https:')===false)
		{
			if(in_array(intval($data[4]),array(CALENDAR_PAGE,BLOG_PAGE,PHOTOBLOG_PAGE,PODCAST_PAGE,GUESTBOOK_PAGE,OEP_PAGE,SURVEY_PAGE,BLOG_VIEW,PHOTOBLOG_VIEW))) //Special pages
			{
				self::$f->dir=(strpos($data[1],'../')===false)?'':'../'.Formatter::GFS($data[1],'../','/').'/';
				$fname=self::$f->dir.$id.(Validator::checkProtection($data)>1?'.php':'.html');
			}
			elseif(in_array(intval($data[4]),array(SHOP_PAGE,CATALOG_PAGE,REQUEST_PAGE)))
			{
				self::$f->dir=(strpos($data[1],'../')===false)?'':'../'.Formatter::GFS($data[1],'../','/').'/';
				$fname=self::$f->dir.(intval($data[4])==REQUEST_PAGE?($id+1):$id).'.html';
			}
			elseif(Validator::checkProtection($data)==1&&($data[4]=='0'||$data[4]=='1'||$data[4]>199) /* && strpos($data[1],'.html')!==false */)
				$fname=$data[1];  //normal page
		}
		$check_mobile=Mobile::detect($data[24])==true;
		if($check_in_normal)
			$check_mobile=$data[24]!='0'; //check only whether page has mobile or not

		if($use_mobile&&($check_mobile||$used_for_mob_search))
		{
			if(strpos($fname,'/')===false)
				$fname='i_'.$fname;
			else
			{
				$temp_name=substr($fname,strrpos($fname,'/')+1);
				$fname=str_replace($temp_name,'i_'.$temp_name,$fname);
			}
			self::$f->mobile_detected=true;
		}
		return $fname;
	}

	public static function fileExt($src)
	{
		$ext_pos=strrpos($src,".");
		$ext=substr($src,$ext_pos);
		return $ext;
	}

	public static function getRelPath()
	{
		return is_dir('documents') && !is_dir('../documents')?'':'../';
	}

}

class Session extends FuncHolder
{

	public static function isSessionSet($Var)
	{
		return isset($_SESSION[$Var]);
	}

	//using null isntead of ''. Formatter::stripTags(null) is string "", so it should not be a problem
	public static function getVar($Var)
	{
		return (isset($_SESSION[$Var])?$_SESSION[$Var]:NULL);
	}

	public static function getVarStr($var)
	{
		return Formatter::stripTags(self::getVar($var));
	}

	public static function setVar($Var,$varValue)
	{
		$_SESSION[$Var]=$varValue;
	}

	public static function setVarArr($Var,$varArrId,$arr)
	{
		$_SESSION[$Var][$varArrId]=$arr;
	}

	public static function unsetVar($Var)
	{
		unset($_SESSION[$Var]);
	}

	public static function unsetSession()
	{
		global $db;

		$_SESSION=array();
		if(isset($_COOKIE[session_name()]))
			setcookie(session_name(),'',time()+1,'/');

		if(CA::getDBSettings($db,'cookie_login'))
		{
			$login_cookies=Cookie::checkLoginCookies(); //get info for the user - i.e. username and hash
			if($login_cookies!==false)
			{
				setcookie(self::$f->login_cb_str.'usr'.self::$f->proj_id,'',time()+1,'/');
				setcookie(self::$f->login_cb_str.'hash'.self::$f->proj_id,'',time()+1,'/');
				setcookie(self::$f->login_cb_str.'t'.self::$f->proj_id,'',time()+1,'/');
				$hash=Cookie::makeDBHash($login_cookies['username'],$login_cookies['hash']);

				self::$f->db->query('DELETE FROM `'.self::$f->proj_pre.'login_cookiebased` WHERE `hash`= "'.$hash.'"');
			}
		}
		session_destroy();
	}

	//^^^^^^^^^^^^cookiebased session functions^^^^^^^^^^^^
	public static function f_sess_open()
	{
		return true;
	}

	public static function f_sess_close()
	{
		return true;
	}

	public static function f_sess_read($id)
	{
		global $db;

		if(!isset($db))
			return ''; //no database object - cannot read anything
		$sql=sprintf('SELECT `data` FROM `'.$db->pre.'sessions` WHERE id= "%s"',$id);

		$res=$db->fetch_all_array($sql,1);
		if($res===false)
		{
			include_once('data.php');
			create_sess_db($db);
			$res=$db->fetch_all_array($sql,1); //try the query again
		}
		if(count($res)>0)
			return $res[0]['data'];
		return '';
	}

	public static function f_sess_write($id,$data)
	{
		global $db;

		$sql=sprintf('REPLACE INTO `'.self::$f->proj_pre.'sessions` VALUES(\'%s\', \'%s\', \'%s\')',mysql_real_escape_string($id),mysql_real_escape_string($data),Date::buildMysqlTime());
		$res=$db->query($sql,1);
		if($res===false)
		{
			include_once('data.php');
			create_sess_db($db);
			$res=$db->query($sql,1); //try the query again
		}

		return $res;
	}

	public static function f_sess_destroy($id)
	{
		global $db;

		$sql=sprintf('DELETE FROM `'.self::$f->proj_pre.'sessions` WHERE `id`= \'%s\'',$id);
		return $db->query($sql);
	}

	public static function f_sess_gc($max)
	{
		global $db;

		$sql=sprintf('DELETE FROM `'.self::$f->proj_pre.'sessions` WHERE `timestamp` < \'%s\'',mysql_real_escape_string(time()-$max));
		return $db->query($sql);
	}

	public static function f_sess_regenerate_id()
	{
		global $db;

		$old=session_id();
		session_regenerate_id();
		$new=session_id();
		return $db->query_update('sessions',array('id'=>mysql_real_escape_string($new)),'`id`= "'.mysql_real_escape_string($old).'"');
	}

	//NORMAL SESSION FUNCTIONS
	public static function intStart($flag='',$regen_id=false,$sess_id=false)
	{

		$curr_sess_id=session_id();
		if(isset($_SESSION)&&($sess_id===false||$sess_id==$curr_sess_id))
			return false; //don't do anything if session is already started

		if(self::$f->session_databased)
		{
			ini_set('session.save_handler','user');
			//set above functions to handle the sessions, using database instead of files
			session_set_save_handler('self::f_sess_open','self::f_sess_close','self::f_sess_read','self::f_sess_write','self::f_sess_destroy','self::f_sess_gc');
			register_shutdown_function('session_write_close');
		}
		else
		{
			$ssp='%SESSIONS_SAVE_PATH%';
			if(($ssp!='')&&(strpos($ssp,'%SESSIONS_')===false))
				session_save_path($ssp);
			session_name('PHPSESSID'.self::$f->proj_id);
		}
		if($sess_id!==false&&$sess_id!=$curr_sess_id)
		{
			@session_start();
			@session_destroy();
			session_id($sess_id);
			session_start();
			if(self::isSessionSet('HTTP_USER_AGENT'))
				self::unsetVar('HTTP_USER_AGENT');
		}
		else
			session_start();

		if($flag=='private')
			header("Cache-control: private");
		if($regen_id&&$sess_id===false)
			self::regenerateID(); //don't allow regen id if specific sess id is set
	}

	public static function regenerateID()
	{

		if(function_exists('session_regenerate_id'))
		{
			if(self::$f->session_databased)
				self::f_sess_regenerate_id();
			else
				session_regenerate_id();  //miro disabled
		}
	}

}

class Search extends FuncHolder
{

	public static function reindexDBAdd($db,$p_id,$entry_id,$p_lang,$permalink,
			  $p_title,$p_content,$modifiedDate,$keywords,$cat_id,$p_type,
			  $user_id='',$expire='',$creationDate='')
	{
		$data=array();
		$data['page_lang']=$p_lang;
		$data['page_id']=$p_id;
		$data['page_title']=$p_title;
		$data['page_url_params']=$permalink;
		$data['page_content']=Formatter::clearHtml(urldecode(Formatter::unEsc($p_content)));
		$data['modified_date']=$modifiedDate==''?Date::buildMysqlTime():$modifiedDate;
		$data['creation_date']=$creationDate==''?Date::buildMysqlTime():$creationDate;
		$data['entry_id']=$entry_id;
		$data['cat_id']=$cat_id;
		if($user_id!='')
			$data['user_id']=$user_id;
		if($keywords!='')
			$data['page_keywords']=$keywords;
		if($expire!='')
			$data['expired_date']=$expire;

		$where='page_id='.$p_id.' AND entry_id="'.$data['entry_id'].'"';
		$exist_rec=$db->query_first('
			SELECT *
			FROM '.$db->pre.'site_search_index
			WHERE '.$where);

		if($exist_rec!==false)
			$db->query_update('site_search_index',$data,$where);
		else
			$db->query_insert('site_search_index',$data);

		if(self::$f->internal_id>1 && $permalink!='')
		{
		  $url=self::$f->internal_url;
		  $data='ptype='.$p_type.'&tid='.$p_id.'&etid='.$entry_id.'&x='.self::$f->internal_id.'&url='.base64_encode($permalink);
		  Linker::load_curl($url,$data);
		}
	}

	public static function reindexDBDel($db,$where)
	{
		$db->query('DELETE FROM '.$db->pre.'site_search_index WHERE '.$where);
	}

	public static function checkDB($db)
	{
		$fcnt=$db->db_fieldnames('site_search_index',true);
		if($fcnt!==SEARCH_INDEX_CNT)
		{
			include_once('data.php');
			create_searchdb($db,self::$f->db_charset);
		}
	}

	public static function catBox($action,$lang_l,&$js)
	{
		$js.='
		$(document).ready(function(){
		  $(".cat_chb").click(function(){ $(".allcat_chb").attr("checked",false); });
		  $(".allcat_chb").click(function(){ $(".cat_chb").attr("checked",false); });
		  $("#search_edit").focus(function() { $("#scb").fadeIn("fast"); })
				.click(function() { $("#scb").fadeIn("fast"); });
		  $("#category_search_ct").mouseleave(function(){ $("#scb").fadeOut("fast"); });
		});';

		$output='
		  <div id="category_search_ct" style="display:inline;position:relative;padding:0 0 12px 2px;">
			<form name="category_search" action="'.$action.'" method="post" onsubmit="return document.category_search.q.value!=\'\'">
				<input class="input1" id="search_edit" type="text" name="q" autocomplete="off" value="">
				<input class="input1" id="search_btn" type="submit" name="search" value="'.$lang_l['search'].'">
				<div class="input1" id="scb" style="display:none;text-align:left;position:absolute;z-index:100;min-width:180px;top:23px;left:2px;padding:4px;box-shadow:0 0 4px #606060;background:white;">
				%CAT_SEARCH%
				</div>
			</form>
		  </div>';
		return $output;
	}
}

class Counter extends FuncHolder
{
	public static function dbCheck($db)
	{
		$c_tb=$db->get_tables('counter_');
		if(empty($c_tb)||count($c_tb)==1)
		{
			include_once('data.php');
			create_counterdb($db,self::$f->max_chars,self::$f->db_charset,self::$f->uni,true);
			$db->query_insert('counter_totals',array("total_type"=>"loads","total"=>0));
			$db->query_insert('counter_totals',array("total_type"=>"unique","total"=>0));
			$db->query_insert('counter_totals',array("total_type"=>"returning","total"=>0));
			$db->query_insert('counter_totals',array("total_type"=>"first","total"=>0));
		}
		elseif(count($c_tb)==3)
		{
			include_once('data.php');
			create_counterdb($db,self::$f->max_chars,self::$f->db_charset,self::$f->uni,false);
		}
		elseif(count($db->db_fieldnames('counter_details'))<COUNTER_DETAILS_FIELD_CNT)
		{
			include_once('data.php');
			create_counterdb($db,self::$f->max_chars,self::$f->db_charset,self::$f->uni,false);
		}
	}

	public static function handleSearchSessionHit($db,$sd='',$sdID=-1)
	{
		Session::intStart(); //sess not started when ajax call to counter
		if($sdID>0) //search performed, get search log id
		{
			$ref=isset($sd['referrer'])?$sd['referrer']:'NA';
			if(strpos($ref,'q=')!==false)
				Session::setVar('last_search_id',$sdID);
		}
		else
		{ //normal page, check if search has been performed before that
			$serv_ref=$_SERVER['HTTP_REFERER'];
			if(strpos($serv_ref,'q=')===false&&strpos($serv_ref,'documents/search.php')!==false)
			{
				$lsid=Session::getVar('last_search_id');
				$parsed_lsid=isset($_REQUEST['lsid'])?intval($_REQUEST['lsid']):0;
				$hit_link=isset($_REQUEST['url'])?$_REQUEST['url']:'';
				if($lsid!==NULL&&$lsid==$parsed_lsid)
				{
					$db->query_update('counter_details',array('hit'=>1,'hit_link'=>$hit_link),'id= '.$lsid);
					Session::unsetVar('last_search_id');
				}
			}
		}
	}

	public static function updateEventCount($db,$p_id)
	{
		if($db==null)
			$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));
		$que='SELECT eventcount FROM '.$db->pre.'counter_pageloads WHERE page_id= '.$p_id;
		$eventcount=$db->query_singlevalue($que,true);
		if($eventcount===false)
		{
			self::dbCheck($db);
			$eventcount=$db->query_singlevalue($que,true);
		}
		if(is_null($eventcount))
			$db->query_insert('counter_pageloads',array("page_id"=>$p_id,"total"=>1,"eventcount"=>1));
		else
			$db->query('UPDATE '.$db->pre.'counter_pageloads SET eventcount=eventcount + 1 WHERE page_id= '.$p_id);
	}

}

class CA extends FuncHolder
{
	public static function getAdminScreenClass()
	{
		 return 'a_body'.(self::getCaMiniCookie()?' small':'');
	}

	public static function getCaMiniCookie()
	{
		return isset($_COOKIE['ca_folded']) && $_COOKIE['ca_folded']=='1';
	}

	public static function setCaMiniCookie($folded)
	{
		 $folded?setcookie('ca_folded','1',0,'/'):setcookie('ca_folded', '', time()-1000);
	}

	public static function insert_setting($data)
	{
		global $db;

		foreach($data as $k=> $v)
		{
			$exist_rec=$db->query_first('SELECT * FROM '.$db->pre.'ca_settings WHERE skey="'.$k.'"');
			if(empty($exist_rec))
				$db->query_insert("ca_settings",array('skey'=>$k,'sval'=>$v));
			else
				$db->query_update("ca_settings",array('skey'=>$k,'sval'=>$v,'lang'=>''),'skey="'.$k.'"');
		}
	}

	public static function create_ca_db($relpath)
	{
		global $db;

		include_once($relpath.'ezg_data/data.php');
		$tb_a=$db->get_tables('ca_users');
		create_cadb($db,$relpath,self::$f->db_folder,
					  self::$f->site_languages_a,self::$f->inter_languages_a,
					  self::$f->ca_users_fields_array,
					  count($tb_a)==0);
	}

	public static function fetchDBSettings($db)
	{
		if(empty(self::$f->ca_settings))
		{
			$records=$db->fetch_all_array('SELECT * FROM '.$db->pre.'ca_settings',1);
			if($records===false)
			{
				if(file_exists('ezg_data/data.php'))
					 self::create_ca_db('');
				else
					 self::create_ca_db('../');
			}

			if($records!==false)
			{
				if(!empty($records))
					foreach($records as $v)
						self::$f->ca_settings[$v['skey']]=$v['sval'];
			}

			if(!isset(self::$f->ca_settings['sr_disable_captcha']))
				self::$f->ca_settings['sr_disable_captcha']='0';
			if(!isset(self::$f->ca_settings['sr_cals_block']))
				self::$f->ca_settings['sr_cals_block']='1';
			if(!isset(self::$f->ca_settings['sr_users_seecounter']))
				self::$f->ca_settings['sr_users_seecounter']='0';
			if(!isset(self::$f->ca_settings['sr_users_see_all']))
				self::$f->ca_settings['sr_users_see_all']='0';
			if(!isset(self::$f->ca_settings['login_redirect_option']))
				self::$f->ca_settings['login_redirect_option']='admin';
			if(!isset(self::$f->ca_settings['auto_login_redirect_time']))
				self::$f->ca_settings['auto_login_redirect_time']='5';
			if(!isset(self::$f->ca_settings['auto_login_redirect_loc']))
				self::$f->ca_settings['auto_login_redirect_loc']='';
			if(!isset(self::$f->ca_settings['auto_login']))
				self::$f->ca_settings['auto_login']=0;
			if(!isset(self::$f->ca_settings['fb_login']))
				self::$f->ca_settings['fb_login']=0;
			if(!isset(self::$f->ca_settings['fb_key']))
				self::$f->ca_settings['fb_key']='';
			if(!isset(self::$f->ca_settings['ga_clientID']))
				self::$f->ca_settings['ga_clientID']='';
			if(!isset(self::$f->ca_settings['fb_secret']))
				self::$f->ca_settings['fb_secret']='';
			if(!isset(self::$f->ca_settings['stat_hide_ips']))
				self::$f->ca_settings['stat_hide_ips']=0;
			if(!isset(self::$f->ca_settings['protect_footer']))
				self::$f->ca_settings['protect_footer']=0;
			if(!isset(self::$f->ca_settings['protect_downloads']))
				self::$f->ca_settings['protect_downloads']=1;
			if(!isset(self::$f->ca_settings['landing_page']))
				self::$f->ca_settings['landing_page']='';
			if(!isset(self::$f->ca_settings['show_orders']))
				self::$f->ca_settings['show_orders']=1;
			if(!isset(self::$f->ca_settings['show_sitemap']))
				self::$f->ca_settings['show_sitemap']=1;

			if(!isset(self::$f->ca_settings['pwchange_enable']))
				self::$f->ca_settings['pwchange_enable']=1;

			if(!isset(self::$f->ca_settings['profilechange_enable']))
				self::$f->ca_settings['profilechange_enable']=1;

			if(!isset(self::$f->ca_settings['cookie_login']))
				self::$f->ca_settings['cookie_login']=1;

			if(!isset(self::$f->ca_settings['monitor_users']))
				self::$f->ca_settings['monitor_users']=0;

			if(!isset(self::$f->ca_settings['sr_enable']))
				self::$f->ca_settings['sr_enable']=1;

			if(!isset(self::$f->ca_settings['usr_blocking']))
				self::$f->ca_settings['usr_blocking']=1;

			if(!isset(self::$f->ca_settings['language']))
				self::$f->ca_settings['language']='EN';

			if(!isset(self::$f->ca_settings['max_rec_on_admin']))
				self::$f->ca_settings['max_rec_on_admin']=20;
		}
	}

	public static function getDBSettings($db,$key)
	{
		if(empty(self::$f->ca_settings))
			self::fetchDBSettings($db);
		$result=isset(self::$f->ca_settings[$key])?self::$f->ca_settings[$key]:'';
		return $result;
	}

	public static function get_user_profile_action(&$lp)
	{
		global $db;

		$lp=self::getDBSettings($db,'landing_page')==1 || !self::getDBSettings($db,'show_sitemap');
		$profile_template_available=false;
		foreach(self::$f->ca_profile_templates_a as $v)
			 if($v!='0')
				 $profile_template_available=true;

		$action=$profile_template_available?'showprofile':($lp?'editprofile':'myprofile');
		return $action;
	}

	public static function get_user_profile_link($pid,$page_dir,$lang,$ref_url,&$url,&$label)
	{
		$lp=false;
		$action=self::get_user_profile_action($lp);

		$url=$page_dir.'centraladmin.php?pageid='.$pid.'&amp;'
				.($ref_url!=''?'ref_url='.urlencode($ref_url).'&amp;':'')
				.($lang!=''?'lang='.$lang.'&amp;':'')
				.'process='.$action;
		$label=$lp?'profile':'site map';
	}

	/* ------------------ central admin functions ------------------- */

	public static function getSitemap($root_path,$incl_cats=false,$return_assoc=false)
	{
		$result=array();
		//Joe:check if we already have what we need
		if($incl_cats && $return_assoc && isset(self::$f->sitemapHolder['cats_assoc']))
			return self::$f->sitemapHolder['cats_assoc'];
		if($incl_cats && isset(self::$f->sitemapHolder['incl_cats']))
			return self::$f->sitemapHolder['incl_cats'];
		if($return_assoc && isset(self::$f->sitemapHolder['assoc']))
			return self::$f->sitemapHolder['assoc'];
		if(!$incl_cats && !$return_assoc && isset(self::$f->sitemapHolder['default']))
			return self::$f->sitemapHolder['default'];

		//Joe:still here? we don't have it, lets get it
		$filename=(strpos($root_path,'sitemap.php')!==false)?$root_path:$root_path.self::$f->sitemap_fname;

		//Joe: as it can calculate up to 4 different sitemaps (based on the function params)
		//and we don't want to read the sitemap file every time, we store it and re-use it
		if(isset(self::$f->sitemapHolder['fileLines']))
			$lines_a=self::$f->sitemapHolder['fileLines'];
		else
		{
			$content=File::read($filename);
			$lines_a=explode("\n",$content);
			self::$f->sitemapHolder['fileLines']=$lines_a;
		}
		$count=count($lines_a);
		for($i=1; $i<$count; $i++)
		{
			if(strpos($lines_a[$i],'<?php echo "hi"; exit; /*')===false&&strpos($lines_a[$i],'*/ ?>')===false)
			{
				if($incl_cats || strpos($lines_a[$i],'<id>')!==false)
				{
					$line_arr=explode("|",trim($lines_a[$i]));
					if(strpos($line_arr[0],'#')==0)
						$line_arr[0]=substr($line_arr[0],1);

					$buffer=array();
					if(isset($line_arr[10])) //it's page, not category
					{
						$line_arr[10]=str_replace('<id>','',$line_arr[10]);
						//pages with childrens
						$p_id=$line_arr[10];
						$ptype=intval($line_arr[4]);
						if($ptype==SHOP_PAGE)
							$buffer=array($p_id+1,$p_id+2,$p_id+3,$p_id+4,$p_id+5,$p_id+6);
						elseif($ptype==CATALOG_PAGE)
							$buffer=array($p_id+1,$p_id+2);
						elseif($ptype=='117' || $ptype==PHOTOBLOG_PAGE)
							$buffer=array($p_id+1);
					}

					$line_arr['sub_ids']=$buffer;

					if($return_assoc)
						$result["$line_arr[10]"]=$line_arr;
					else
						$result[]=$line_arr;
				}
			}
		}

		//Joe:ok, we got it, but let's store it for future re-use
		if($incl_cats && $return_assoc)
			self::$f->sitemapHolder['cats_assoc'] = $result;
		elseif($incl_cats)
			self::$f->sitemapHolder['incl_cats'] = $result;
		elseif($return_assoc)
			self::$f->sitemapHolder['assoc'] = $result;
		else
			self::$f->sitemapHolder['default'] = $result;
		return $result;
	}

	public static function getPageParamsArray($p_array,$pid)
	{
    $page=array();
		foreach($p_array as $v)
		{
			if($v[10]==$pid)
			{
				$page=$v;
				break;
			}
		}
		return $page;
	}

	public static function getPageParams($id,$root_path='../',$use_next_page=false)
	{
		$forms=array_merge(self::$f->subminiforms,self::$f->subminiforms_news);
		if(array_key_exists($id,$forms)||($id==0&&isset($_GET['pageid'])&&array_key_exists($_GET['pageid'],$forms)))
			$id=$forms[$id];

		if($id==0)
			return '';
		if(isset(self::$f->page_params[$id]))
			$result=self::$f->page_params[$id];
		else
		{
			$all_pages=self::getSitemap($root_path);
			$result=self::getPageParamsArray($all_pages,$id);

			if(empty($result))
			{
				$parent_id=self::getParentPage($id,$all_pages);
				if($parent_id!==false)
					$result=self::getPageParamsArray($all_pages,$parent_id);
			}

			if(!$use_next_page)
				self::$f->page_params[$id]=$result;
			else
			{
				if(!empty($result))
					self::$f->page_params[$id]=$result;
				else
				{
					$id--;
					while(empty($result)&&$id>0)
					{
						$result=self::getPageParamsArray($all_pages,$id);
						$id--;
					}
				}
			}
		}
		if(empty($result))
		{
			if(!isset($all_pages))
				$all_pages=self::getSitemap($root_path);
			$parPage = self::getParentPage($id,$all_pages);
			if($parPage)
				$result=self::getPageParams($parPage,$root_path,$use_next_page);
		}
		return $result;
	}

	public static function defineAdminLink($pinfo,$from_ca=false)
	{
		$admin_link='';
		$ptype=intval($pinfo[4]);
		if($ptype==REQUEST_PAGE)
		{
			$dir=(strpos($pinfo[1],'../')===false)?'':'../'.Formatter::GFS($pinfo[1],'../','/').'/';
			$admin_link=$dir.'ezgmail_'.$pinfo[10].'.php?action=index';
		}
		elseif($ptype==NEWSLETTER_PAGE)
		{
			$dir=(strpos($pinfo[1],'../')===false)?'':'../'.Formatter::GFS($pinfo[1],'../','/').'/';
			$admin_link=$dir.'newsletter_'.$pinfo[10].'.php?action=subscribers';
		}
		elseif($ptype==PODCAST_PAGE && strpos($pinfo[1],'?flag=podcast')!==false)
		{
			$admin_link=$pinfo[1].'&action=index';
		}
		elseif($ptype==SURVEY_PAGE)
			$admin_link=$pinfo[1].'?action=manage';
		elseif($ptype==CATALOG_PAGE)
			$admin_link=$pinfo[1].'?action=stock';
		elseif($ptype==SHOP_PAGE)
		{
			if(strpos($pinfo[1],'action=list')!==false)
				$admin_link=str_replace('action=list','action=login',$pinfo[1]);
			else
				$admin_link=$pinfo[1].'?action=login';
		}
		elseif($ptype==OEP_PAGE)
		{
			$r_with='action=doedit';
			if($from_ca&&Session::isSessionSet('cur_pwd'.intval($_GET['pageid'])))
				$r_with='action=remcookie';
			if(strpos($pinfo[1],'action=show')!==false)
				$admin_link=str_replace('action=show',$r_with,$pinfo[1]);
			else
				$admin_link=$pinfo[1].'?'.$r_with;
		}
		elseif($from_ca&&($ptype==NORMAL_PAGE || $ptype==HOME_PAGE))
		{
			global $user;
			if(self::$f->ca_settings['login_redirect_option']!='page')
			{
				$lp=false;
				$action=$user->isAdmin() || $user->isAdminUser()?'index':self::get_user_profile_action($lp);
				$admin_link=(strpos($pinfo[1],'../')===false?'documents/':'').'centraladmin.php?process='.$action;
			}
			else
				$admin_link=$pinfo[1];
		}
		elseif(!$from_ca&&in_array($ptype,self::$f->sp_pages_ids))
			$admin_link=$pinfo[1].'?action=index';
		else{
			if($from_ca)
				$admin_link=$pinfo[1].'?action=index&ptype='.$ptype;
			else
				$admin_link=$pinfo[1];
		}
		return $admin_link;
	}

	public static function formatCaption($caption,$p=false)
	{
		$result='<span class="rvts8 a_editcaption">'.$caption.'</span>';
		if($p)
			$result='<p>'.$result.'</p>';
		return $result;
	}

	public static function formatNotice($notice,$p=false)
	{
		$result='<span class="rvts8 a_editnotice">'.$notice.'</span>';
		if($p)
			$result='<p>'.$result.'</p>';
		return $result;
	}

	public static function getMyprofileLabels($thispage_id,$root_path='../',$lang='')
	{
		$labels=array();
		if($thispage_id!=''&&$thispage_id>0)
		{
			$pageid_info=self::getPageParams($thispage_id,$root_path);
			if(empty($pageid_info))
			{
				for($i=1; $i<=7; $i++)
				{
					$pageid_info=self::getPageParams(($thispage_id-$i),$root_path);
					if(!empty($pageid_info))
						break;
				}
			}
			if($lang=='')
				$lang=(isset($pageid_info[22]))?$pageid_info[22]:'EN';
			$key=array_search($lang,self::$f->inter_languages_a);
			if($key!==false)
				$labels=self::$f->lang_reg[$key];
			if(empty($labels)&&$lang=='EN')
				$labels=self::$f->lang_reg['EN'];
		}
		else
			$labels=self::$f->lang_reg['0'];
		return $labels;
	}

	public static function getParentPage($id,$pages_list)
	{
		foreach($pages_list as $v)
			if(isset($v['sub_ids']) && in_array($id,$v['sub_ids']))
				return isset($v['pageid'])?$v['pageid']:$v[10];
		return false;
	}

}

class History extends FuncHolder
{

	public static function add($page_id,$table_id,$entry_id,$user_id,$data)
	{
		global $db;

		$dump=var_export($data,true);
		$data=array();
		$data['page_id']=$page_id;
		$data['table_id']=$table_id;
		$data['entry_id']=$entry_id;
		$data['user_id']=$user_id;
		$data['dump']=$dump;
		$data['creation_date']=Date::buildMysqlTime();
		if(!$db->query_insert('site_history',$data,true))
		{
			include_once('data.php');
			create_historydb($db,'site_history');
			$db->query_insert('site_history',$data);
		}
	}

	public static function getPath($root)
	{
		return ($root?'':'../').'innovaeditor/assets/admin/history/';
	}

	public static function getFilePath($root,$page_id,$entry_id)
	{
		return self::getPath($root).$page_id.'_'.$entry_id;
	}

	public static function addFlat($root,$page_id,$entry_id,$user_id,$data)
	{
		$history_path=self::getPath($root);
		$history_filepath=self::getFilePath($root,$page_id,$entry_id);

		$go=true;
		if(!is_dir($history_path))
			if(!@mkdir($history_path,0700))
				$go=false;

		if($go)
		{
			$date=Date::buildMysqlTime();
			$file_contents='<entry date="'.$date.'" user="'.$user_id.'">'.$data.'</entry>'.F_LF;
			File::write($history_filepath,$file_contents,'a+');
		}
	}

}

class Symlink
{
	private $symlinkPath;
	private $uniqueId;
	private $pgSettings;
	private $pg;

	public function __construct($pg,$path,$id,$settings)
	{
		$this->pg=$pg;
		$this->symlinkPath=$path;
		$this->uniqueId=$id;
		$this->pgSettings=$settings;
	}
	//symlink functions
	public function make($f_path,$fname)
	{
		$target=$f_path;
		$link=$this->symlinkPath.$this->uniqueId.'_'.$fname;
		$this->checkSymlinkFolder();

		//symlink doesn't return the link on success, so we need to make extra check
		if(is_link($link))
			return $link; //already has such link
		if(is_file($link)) //it's a file, not a link. We don't need any other files here, so...
		{
			unlink($link);
			return false;
		}

		if(!symlink($target,$link)) return false;
		return ($link);
	}

	private function clearSymlinks()
	{
		if(!$this->checkSymlinkFolder()) return; //something's not right with the symlinks folder. Maybe we can inform the user about that.
		$time=time();
		$period=$this->pgSettings['g_downloadexpire']*60*60*24; //days to seconds
		$d=dir($this->symlinkPath);
		while(($file=$d->read()) !== false)
		{
			if($file=='.' || $file=='..') continue;
			if(linkinfo($file) && (filemtime($file) + $period <= $time) ) unlink($file);
		}
	}

	public function checkSymlinksDeletion()
	{
		//TODO: get and insert settings to not rely on current page
		$last_check=$this->pg->get_setting('last_symlinks_del');
		if($last_check == '' || $last_check < time()-60*60*24)
		{
			$this->clearSymlinks();
			$this->pg->db_insert_settings(array('last_symlinks_del'=>time()));
		}
	}

	private function checkSymlinkFolder()
	{
		if(!is_dir($this->symlinkPath))
			if(!mkdir($this->symlinkPath, 0775, true))
				return false;
		if(!is_readable($this->symlinkPath) || !is_writable($this->symlinkPath))
			return false;
		return true;
	}

}

class ErrorHandler extends FuncHolder
{
	const user_errors_tbl = 'user_errors';
	const cur_version = '0.2';
	static function alterTable($onlineVersion)
	{
		global $db;
		if($onlineVersion < self::cur_version)
		{
			$db->query('DELETE FROM '.$db->pre.self::user_errors_tbl);
			$db->query('ALTER TABLE '.$db->pre.self::user_errors_tbl.' ADD UNIQUE INDEX(err_level, err_desc(10), err_line, err_file)');
		}
	}

	static function triggerError($no, $msg, $debugMode=false)
	{
		 self::handleErrors($no, $msg, 0, 0, array(), $debugMode);
	}

	static function handleErrors($errorno, $errMsq, $errFile, $errLine, $errContext = array(), $debug=null)
	{
		global $db;
		$debugMode = false; //parsed
		$ca_data = array(self::user_errors_tbl => self::cur_version);

		if (!$debugMode && $debug)
			return true;

		//convert number to label (remove if not needed)
		$errornoLbl = self::errNoToLbl($errorno);

		if(isset($debug))
		{
			$trace = debug_backtrace();
			$errFile = $trace[1]['file'];
			$errLine = $trace[1]['line'];
			$debug = $debug?'DEBUG':'ERROR';
		}

		if(!$db)          //$db is not set, cannot store anything into the database
			return false;   //so we stop the script and return false to not prevent propagation (normal handling of errors continues)

		$in_tbl_version = CA::getDBSettings($db, self::user_errors_tbl);
		// if user_errors does not exist
		if ($in_tbl_version === '')
		{
			self::includePhpFile();
			if(!createUserErrors($db)) return false;// table was not created, continue propagation to show error in error logs
			CA::insert_setting($ca_data);
		}
		// alter user_errors table when the version is changed
		if ($in_tbl_version !== self::cur_version)
		{
			self::includePhpFile();
			self::alterTable($in_tbl_version);
			CA::insert_setting($ca_data);
		}

		$sql = 'INSERT INTO ' . $db->pre . self::user_errors_tbl . ' (err_level,err_type, err_desc, err_line, err_file, err_date) Values("' . $errornoLbl . '","' . $debug . '", "' . $errMsq . '", "' . $errLine. '", "' . $errFile . '", now()) ON DUPLICATE KEY UPDATE err_date = now()';

		$db->query($sql);

		self::terminateScript($errorno);
		return true; //stop propagation
	}

	static function includePhpFile($fname='data.php')
	{
		$rel_path = '';
		if (!is_file($rel_path . 'ezg_data/'.$fname))
		{
			$rel_path = '../';
			if (!is_file($rel_path . 'ezg_data/'.$fname))
			{
				echo $fname.' was not included in error handling';
				exit;
			}
		}
		include_once $rel_path . 'ezg_data/'.$fname;
	}

	static function errNoToLbl($errorno)
	{
		//to not show strange numebers in the table
		switch ($errorno)
		{
			case E_ERROR : return 'E_ERROR'; //1
			case E_WARNING : return 'E_WARNING';  //2
			case E_PARSE : return 'E_PARSE';  //4
			case E_NOTICE : return 'E_NOTICE';  //8
			case E_CORE_ERROR : return 'E_CORE_ERROR';  //16
			case E_CORE_WARNING : return 'E_CORE_WARNING';  //32
			case E_COMPILE_ERROR : return 'E_COMPILE_ERROR';  //64
			case E_COMPILE_WARNING : return 'E_COMPILE_WARNING';  //128
			case E_USER_ERROR : return 'E_USER_ERROR';  //256
			case E_USER_WARNING : return 'E_USER_WARNING';  //512
			case E_USER_NOTICE : return 'E_USER_NOTICE';  //1024
			case E_STRICT : return 'E_STRICT';  //2048
			case E_RECOVERABLE_ERROR : return 'E_RECOVERABLE_ERROR';  //4096
			case E_DEPRECATED : return 'E_DEPRECATED';  //8192
			case E_USER_DEPRECATED : return 'E_USER_DEPRECATED'; //16384
			case E_ALL : return 'E_ALL';  //32767
		}
	}

	static function terminateScript($errorno)
	{
		switch ($errorno)
		{
			case 1:
			case 16 :
			case 64 :
			case 256 :
				echo "<h2 style='background-color: black; color: white; text-align: center;'>";
				echo ERR_MESSAGE;
				echo "</h2>";
				exit();
		}
	}

}

class User extends FuncHolder
{
	const FLAG_VISITOR = 1; //read only access
	const FLAG_USER = 2; //user that can edit current page
	const FLAG_ADMIN = 4; //admin
	const FLAG_FB_USER = 8; //logged via FB Login

	private $id;
	private $uname;
	private $access;
	private $news;
	private $data;
	private $isAdmin;

	public function __construct()
	{
		parent::__construct();
		Session::intStart();
		$this->id=null;
		$this->uname=null;
		$this->data=null;
		$this->access=null;
		$this->news=null;
		$this->isAdmin=Cookie::isAdmin();
		$this->setFlag(self::FLAG_FB_USER,Session::isSessionSet('FBLogged'));
	}

	public function __destruct()
	{
		return;
	}

	public static function isLogged($Var)
	{
		global $db;

		$sessVar=Session::getVar($Var);
		$issetVar=($sessVar!=''||$sessVar!=NULL);


		if(!CA::getDBSettings($db,'cookie_login'))
			return $issetVar;

		//if this line is reached, cookiebased login is active so let's check if we need to read the cookie at all
		if($Var==self::$f->user_cookieid&&!$issetVar)
		{
			//looking for user, session is not set yet so check if there is a cookie left
			if($user_cookies=Cookie::checkLoginCookies())
			{
				$user_account=User::getUser($user_cookies['username'],self::$f->ca_rel_path);
				$someUser = new User();
				$someUser->setData($user_account);

				if($someUser->isDataEmpty() || $someUser->getData('status')!='1' || $someUser->getData('confirmed')!='1')
					return false; //function returns that the user is not logged in
				$hash=Cookie::makeCookieHash($user_account,$user_cookies['time']);
				$db_hash=Cookie::makeDBHash($user_account['username'],$hash);

				$res=$db->fetch_all_array('SELECT * FROM `'.self::$f->proj_pre.'login_cookiebased` WHERE `hash`= "'.$db_hash.'" AND `exp` > NOW()');
				if(count($res)!=1)
					return false; //there is something incorrect, require new login

				//user is correct - lets add the session info and let him proceed
				Session::intStart();
				Session::regenerateID();
				$someUser->setUserCookie();
				return true; //user is logged now
			}
			//no cookie - no user logged, so we don't do anything here
		}
		//it's not user we're checking for, so return if var is set or not
		return $issetVar;
	}

	public static function formatUsers($users,$user_as_index=false,$userid_as_index=false)  //flat only, also used in data.php for import
	{
		$users_array=array();
		$i=1;

		while(strpos($users,'<user id="')!==false)
		{
			$i=Formatter::GFS($users,'<user id="','" ');
			$all='<user id="'.$i.'" '.Formatter::GFS($users,'<user id="'.$i.'" ','</user>');
			$basic=Formatter::GFS($all,'<user id="'.$i.'" ','>').' ';
			$details=Formatter::GFS($all,'<details ','></details>').' ';
			$access=Formatter::GFS($all,'<access_data>','</access_data>').' ';
			$news=Formatter::GFS($all,'<news_data>','</news_data>').' '; // event manager

			list($username,$password)=explode(' ',$basic);
			$details_arr=array();
			$details_arr['email']=Formatter::GFS($details,'email="','"');
			$details_arr['first_name']=Formatter::GFS($details,'name="','"');
			$details_arr['surname']=Formatter::GFS($details,'sirname="','"');
			$details_arr['creation_date']=Formatter::GFS($details,'date="','"');
			$details_arr['self_registered']=Formatter::GFS($details,'sr="','"'); //self-registration flag

			$status_flag=Formatter::GFS($details,'status="','"');
			$details_arr['status']=($status_flag!='')?$status_flag:'1'; //status flag

			$access_arr=array();
			$j=1;
			while(strpos($access,'<access id="'.$j.'" ')!==false)
			{
				$access_full=Formatter::GFSAbi($access,'<access id="'.$j.'" ','</access>');
				$page_access_arr=array();
				$m=1;
				while(strpos($access_full,'<p id="'.$m.'" ')!==false)
				{
					$page_access_str=Formatter::GFSAbi($access_full,'<p id="'.$m.'" ','>');
					$page_access_arr []=array('page'=>Formatter::GFS($page_access_str,'page="','"'),'type'=>Formatter::GFS($page_access_str,'type="','"'));
					$m++;
				}
				$access_str=Formatter::GFS($access_full,'<access id="'.$j.'" ','>');
				list($section,$type)=explode(' ',$access_str);
				$access_arr[]=array(substr($section,0,strpos($section,'='))=>Formatter::GFS($section,'="','"'),substr($type,0,strpos($type,'='))=>Formatter::GFS($type,'="','"'),'page_access'=>$page_access_arr);
				$j++;
			}
			$news_arr=array();
			$j=1; // event manager
			while(strpos($news,'<news id="'.$j.'" ')!==false)
			{
				$news_str=Formatter::GFS($news,'<news id="'.$j.'" ','>');
				list($page,$cat)=explode(' ',$news_str);
				$news_arr []=array(substr($page,0,strpos($page,'='))=>Formatter::GFS($page,'="','"'),substr($cat,0,strpos($cat,'='))=>Formatter::GFS($cat,'="','"'));
				$j++;
			}

			$user=Formatter::GFS($username,'="','"');
			if($user_as_index)
			{
				$users_array[$user]=array('id'=>$i,'uid'=>$i,'username'=>$user,'password'=>Formatter::GFS($password,'="','"'),'access'=>$access_arr,'news'=>$news_arr);
				foreach($details_arr as $k=> $v)
					$users_array[$user][$k]=$v;
			}
			elseif($userid_as_index)
			{
				$users_array[$i]=array('id'=>$i,'uid'=>$i,'username'=>$user,'password'=>Formatter::GFS($password,'="','"'),'access'=>$access_arr,'news'=>$news_arr);
				foreach($details_arr as $k=> $v)
					$users_array[$i][$k]=$v;
			}
			else
			{
				$usr=array('id'=>$i,'uid'=>$i,'username'=>$user,'password'=>Formatter::GFS($password,'="','"'),'access'=>$access_arr,'news'=>$news_arr);
				foreach($details_arr as $k=> $v)
					$usr[$k]=$v;
				$users_array[]=$usr;
			}

			$users=str_replace($all,'',$users);
		}
		return $users_array;
	}

	public static function getAllUsers($user_as_index=false,$userid_as_index=false,$add_admin=false,$db=null)
	{
		$users_arr=array();

		if($db==null)
			$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));
		$users_arr=User::mGetAllUsers($db,'confirmed=1');

		if($user_as_index&&!empty($users_arr))
		{
			foreach($users_arr as $k=> $v)
				$temp[$v['username']]=$v; $users_arr=$temp;
		}
		elseif($userid_as_index&&!empty($users_arr))
		{
			foreach($users_arr as $k=> $v)
				$temp[$v['uid']]=$v;
			$users_arr=$temp;
		}
		if($add_admin)
			$users_arr[-1]=array('uid'=>'-1','username'=>self::$f->admin_nickname,'avatar'=>self::$f->admin_avatar);
		return $users_arr;
	}

	public static function getUser($username,$root_path,$by_email='',$by_id='')
	{
		$specific_user=false;

		$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));
		if($by_email!='')
			$specific_user=User::mGetUser($by_email,$db,'email');
		elseif($by_id!='')
			$specific_user=User::mGetUser($by_id,$db,'uid');
		else
			$specific_user=User::mGetUser($username,$db,'username');
		return $specific_user;
	}

	public static function mGetAllUsers($db,$where=1,$get_groups=false) // mysql version
	{
		global $db;
		$records=array();
		if($get_groups)
			$records=$db->fetch_all_array('
				SELECT u.*,g.name as group_name,u.creation_date
				FROM '.$db->pre.'ca_users u
				LEFT JOIN '.$db->pre.'ca_users_groups_links l ON u.uid= l.user_id
				LEFT JOIN '.$db->pre.'ca_users_groups g ON g.id= l.group_id
				WHERE '.$where.'
				ORDER BY username DESC',true);
		else
			$records=$db->fetch_all_array('
				SELECT * FROM '.$db->pre.'ca_users
				WHERE '.$where.'
				ORDER BY username DESC',true);
		return $records;
	}

	//TODO - optimize this function to use one query and joins for getting the user data
	public static function mGetUser($user,$db,$by_field='uid',$need_access=true,$need_news=true) // mysql version
	{
		if(array_key_exists($user,self::$f->checked_users)&&!empty(self::$f->checked_users[$user]))
			return self::$f->checked_users[$user];

		$user_data=array();

		$tb_ca=$db->get_tables('ca_');
		if(!empty($tb_ca))
		{
			$q=$by_field=='uid'?intval($user):'"'.addslashes($user).'"';
			$que='
				SELECT u.*,a.*,g.login_redirect AS group_login_redirect,g.logout_redirect AS group_logout_redirect
				FROM '.$db->pre.'ca_users u
				INNER JOIN '.$db->pre.'ca_users_access a ON a.user_id= u.uid
				LEFT JOIN '.$db->pre.'ca_users_groups_links l ON u.uid= l.user_id
				LEFT JOIN '.$db->pre.'ca_users_groups g ON g.id= l.group_id
				WHERE '.$by_field.'= '.$q;
			$ud=$db->fetch_all_array($que,true);
			if($ud===false)
			{
				$que=str_replace(array('g.login_redirect','g.logout_redirect'),'""',$que);
				$ud=$db->fetch_all_array($que,true);
			}
			if(!empty($ud))
			{
				$user_data=$ud[0];
				$access=array();
				foreach($ud as $v)
					$access[]=array('user_id'=>$v['user_id'],'section'=>$v['section'],'page_id'=>$v['page_id'],'access_type'=>$v['access_type']);
				$user_data['access']=$access;
				$user_data['user_admin']=($access[0]['section']=='ALL' && $access[0]['access_type']==ADMIN_ACCESS)?1:0;
				if(isset($user_data['uid']))
				{
					$where='user_id= '.$user_data['uid'];
					if($need_news)
					{
						$users_news=self::mGetUserNews($db,$where);
						if(isset($users_news[$user_data['uid']]))
							$user_data['news']=$users_news[$user_data['uid']];
					}
				}
			}
		}
		self::$f->checked_users[$user]=$user_data;
		return $user_data;
	}
	public static function mGetUserNews($db,$where=1) // mysql version
	{
		$records=array();
		$records_raw=$db->fetch_all_array('SELECT * FROM '.$db->pre.'ca_users_news WHERE '.$where);
		foreach($records_raw as $v)
			$records[$v['user_id']][]=$v;

		return $records;
	}

	public static function mGetUsersWithAccess($db,$root_path,$page_id,$exclude_view_access)
	{
		$result=array();
		$que='SELECT u.*, ua.* FROM '.$db->pre.'ca_users AS u
				LEFT JOIN '.$db->pre.'ca_users_access AS ua ON(ua.user_id= u.uid)
				WHERE u.confirmed= 1
				ORDER BY username DESC';
		$rec_raw=$db->fetch_all_array($que,1);
		if($rec_raw===false)
			Linker::redirect($root_path.'documents/centraladmin.php?process=dbcheck&url='.Linker::url());
		foreach($rec_raw as $user)
		{
			$uid=$user['uid'];
			if($user['section']=='ALL')
			{
				if(($exclude_view_access&&$user['access_type']>0)||!$exclude_view_access)
					$result[$uid]=$user['username'];
			}
			else
			{
				if($user['page_id']!=0)
				{
					if($page_id==$user['page_id'] && $user['access_type']!=NO_ACCESS)
					{
						if(($exclude_view_access&&$user['access_type']!=0)||!$exclude_view_access)
							$result[$uid]=$user['username'];
					}
				}
			}
		}
		return $result;
	}

	public static function getUserPG($page_id,$root_path)
	{
		return User::mGetUsersPG($page_id,$root_path);
	}

	public static function fetchUserID($username,$rel_path)
	{
		$user_data=User::getUser($username,$rel_path);
		return (!empty($user_data)?$user_data['uid']:0);
	}

	public static function fetchUserName($user_id,$rel_path)
	{
		$user_data=User::getUser($user_id,$rel_path,'',$user_id);
		return (isset($user_data['display_name'])&&!empty($user_data['display_name'])?$user_data['display_name']:(isset($user_data['username'])?$user_data['username']:''));
	}

	public static function mGetUsersPG($page_id,$root_path,$exclude_view_access=false) // mysql version
	{
		$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));
		$all_users=User::mGetAllUsers($db,'confirmed= 1');
		if(!empty($all_users))
		{
			$users_list=array();
			foreach($all_users as $v)
				$users_list[]=$v['uid'];
		}
		return User::mGetUsersWithAccess($db,$root_path,$page_id,$exclude_view_access);
	}

	public static function mHasAllReadAccess($user_account,$limited=false)
	{
		if(!empty($user_account) && ($user_account['status']==1) && ($user_account['confirmed']==1))
		{
			if(isset($user_account['access'][0]))
			{
				if($user_account['access'][0]['section']=='ALL')
				{
					 if($user_account['access'][0]['access_type']=='0') return true;
				}
				elseif($limited)
				{
					 foreach($user_account['access'] as $v)
							if(!($v['access_type']==VIEW_ACCESS || $v['access_type']==NO_ACCESS)) return false;
					 return true;
				}
			}
		}
		return false;
	}

	public static function mHasReadAccess($user_account,$prot_page_info) // mysql version
	{
		$auth=false;
		$section_flag=false;
		$write_flag=false;
		$access=false;

		if(!empty($user_account)&&($user_account['status']==1)&&($user_account['confirmed']==1)&&isset($prot_page_info[1]))
		{
			$page_id=$prot_page_info[10];
			settype($page_id,'integer');
			if(isset($user_account['access'][0])&&$user_account['access'][0]['section']!='ALL')
			{
				foreach($user_account['access'] as $v)
				{
					if(Validator::checkProtection($prot_page_info)==1)
						return true; //unprotected, user has access
					else
					{
						$section_flag=true;

						if($v['page_id']!=0)
						{
							if($page_id==$v['page_id'])
							{
								if($v['access_type']==EDIT_ACCESS || $v['access_type']==EDIT_OWN_ACCESS || $v['access_type']==ADMIN_OEP_ACCESS || $v['access_type']==ADMIN_ON_PAGE)
								{
									$write_flag=true;
									$access=true;
									break;
								}
								elseif($v['access_type']==0)
								{
									$access=true;
									break;
								}
								elseif($v['access_type']==NO_ACCESS)
									break;
							}
						}
					}
				}
			}
			else
			{
				$section_flag=true;
				$access=true;
				if(isset($user_account['access'][0]) && $user_account['access'][0]['access_type']>0)
					$write_flag=true;
			}
			if($section_flag)
				$auth=($write_flag||(!isset($_GET['indexflag'])&&$access));
		}
		return $auth;
	}

	public static function mHasWriteAccess_outsite($page_id)
	{
			if($page_id=='')
				return false;

			if(isset($_SESSION['SID_ADMIN'.self::$f->proj_id]))
				return true;

			if(!isset($_SESSION['cur_user'.self::$f->proj_id]))
				return false;
			$username=$_SESSION['cur_user'.self::$f->proj_id];
			$data_user=self::getUser($username,'');
			$page_info[10]=$page_id;
			$access='';
			return self::mHasWriteAccess2($username,$data_user,$page_info,$access);
	}

	public static function mHasWriteAccess2($username,$user_account,$page_info,&$access_type) // mysql version
	{
		$access=false;
		$page_id=$page_info[10];
		settype($page_id,'integer');

		if(!empty($user_account)&&($user_account['status']==1))
		{
			if(isset($user_account['access'][0])&&$user_account['access'][0]['section']!='ALL'&&$user_account['username']==$username)
			{
				foreach($user_account['access'] as $v)
				{
					if($v['page_id']!=0)
					{
						if($page_id==$v['page_id'])
						{
							$access_type=$v['access_type'];
							if($access_type==EDIT_ACCESS ||$access_type==EDIT_OWN_ACCESS ||$access_type==ADMIN_OEP_ACCESS ||$access_type==ADMIN_ON_PAGE )
							{
								$access=true;
								break;
							}
							elseif($access_type==NO_ACCESS)
								break;
						}
					}
				}
			}
			elseif($user_account['username']==$username)
			{
				if(isset($user_account['access'][0])&&$user_account['access'][0]['access_type']>0)
					$access=true;
			}
		}
		return $access;
	}

	public static function mHasWriteAccess($username,$page_info,$db)  // mysql version
	{
		$user_account=User::mGetUser($username,$db,'username',true,false);
		$access_type='';
		$access=self::mHasWriteAccess2($username,$user_account,$page_info,$access_type);
		return $access;
	}

	public static function hasRegisterAccess($username,$root_path='../')
	{
		$auth=false;
		$user_account=User::getUser($username,$root_path);
		if(!empty($user_account)&&($user_account['status']=='1'))
		{
			if(isset($user_account['access'][0])&&$user_account['access'][0]['section']!='ALL'&&$user_account['username']==$username)
			{
				foreach($user_account['access'] as $v)
				{
					$auth=true;
					break;
				}
			}
			elseif($user_account['username']==$username)
				$auth=true;
		}
		return $auth;
	}

	public static function userEditOwn($db,$uid,$page_info)
	{
		$ua=array();
		$uid=intval($uid);
		$where='user_id = '.$uid;
		$users_access=self::mGetUserAccess($db,$where);
		if(isset($users_access[$uid]))
			$ua['access']=$users_access[$uid];
		return User::userEditOwnCheck($ua,$page_info);
	}

	public static function mGetUserAccess($db,$where=1)
	{
		$records=array();
		$records_raw=$db->fetch_all_array('SELECT * FROM '.$db->pre.'ca_users_access WHERE '.$where);
		foreach($records_raw as $v)
			$records[$v['user_id']][]=$v;
		return $records;
	}

	public static function userEditOwnCheck($user_account,$page_info)
	{
		$result=false;
		$page_id=$page_info[10];

		if(!empty($user_account)&&isset($user_account['access'][0])&&$user_account['access'][0]['section']!='ALL')
		{
			foreach($user_account['access'] as $v)
			{
				if($v['page_id']!=0)
				{
					if($page_id==$v['page_id'] && $v['access_type']==EDIT_OWN_ACCESS)
					{
						$result=true;
						break;
					}
				}
			}
		}
		return $result;
	}

	public static function userGetLimitPost($user_account,$page_info,$user_id)
	{
		$result='0';
		$page_id=$page_info[10];
		if(!empty($user_account[$user_id]))
		{
			foreach($user_account[$user_id] as $v)
			{
				if($v['page_id']!=0)
				{
					if($page_id==$v['page_id'] && $v['access_type']==EDIT_OWN_ACCESS)
					{
						$result=$v['limit_own_post'];
						break;
					}
				}
			}
		}
		return $result;
	}

	public function userCanEdit($page_info,$rel_path,&$edit_own)
	{
		global $db;

		Session::intStart('private');
		$edit_own=false;
		self::mGetLoggedUser($db,'');
		if(self::isAdmin())
			$can_edit=true;
		else
		{
			$has_user_cookie=self::userCookie();
			$can_edit=($has_user_cookie && User::mHasWriteAccess(self::getUserCookie(),$page_info,$db));
			if($can_edit) //if can edit, then it has user cookie and logged_user info is already got
				$edit_own=self::userEditOwn($db,self::getId(),$page_info);
		}
    return $can_edit;
	}

	public static function delete($db,$user)
	{
		global $db;

		if(CA::getDBSettings($db,'monitor_users'))
		{
			$data=array();
			$data['username']='';
			$db->query_update('counter_users',$data,'username= "'.$user.'"');
		}
	}

	public static function append($db,$user)
	{
		global $db;

		if(CA::getDBSettings($db,'monitor_users'))
		{
			$data=array();
			$data['ip']=Detector::getIP();
			$data['created']=Date::buildMysqlTime();
			$data['username']=$user;
			if($user!='')
				$db->query('DELETE FROM '.$db->pre.'counter_users WHERE ip= "'.$data['ip'].'"');
			$db->query_insert('counter_users',$data);
		}
	}

	public static function getOnlineUsers($content)
	{
		global $db;

		$count_reg=0;
		$users='';
		$count=0;
		if(CA::getDBSettings($db,'monitor_users'))
		{
			$timeout=300;
			$db=DB::dbInit(self::$f->db_charset,(self::$f->uni?self::$f->db_charset:''));

			$ts=Date::buildMysqlTime(time()-$timeout);
			$db->query('DELETE FROM '.$db->pre.'counter_users WHERE created < "'.$ts.'"');
			$rawdata=$db->fetch_all_array('SELECT DISTINCT (ip),username FROM '.$db->pre.'counter_users ORDER BY username DESC');
			$users=array();

			foreach($rawdata as $v)
			{
				if($v['username']!=='')
				{
					$count_reg++;
					$users[]=$v['username'];
				}
			}
			$count=count($rawdata)-$count_reg;
			if($count==0&&$count_reg==0)
				$count=1;
		}
		$content=str_replace(array('%USER_COUNT%','%GUEST_COUNT%','%USERS%'),
				  array($count_reg,$count,(is_array($users)?implode(" | ",$users):'')),$content);
		return $content;
	}

	public static function getUserName($user_id,$rel_path='../')
	{
		if($user_id==-1)
			return self::$f->admin_nickname;
		else
		{
			$user_data=User::getUser($user_id,$rel_path,'',$user_id);
			return (!empty($user_data)?$user_data['username']:'');
		}
	}

	public static function getUserID($username,$rel_path='../')
	{
		$user_data=User::getUser($username,$rel_path);
		return (!empty($user_data)?$user_data['uid']:0);
	}

	public static function generateRandPass()
	{
		$alphabet = "abcdefghijkmnopqrstuwxyzABCDEFGHJKLMNPQRSTUWXYZ0123456789!@#$%^&*.,";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 12; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}

	public function isEZGAdminLogged() //true if admin logged
	{
		return Cookie::isAdmin()&&(!self::isLogged('HTTP_USER_AGENT')||Session::getVar('HTTP_USER_AGENT')==md5($_SERVER['HTTP_USER_AGENT']));
	}

	public function isEZGAdminNotLogged()
	{
		return (!Cookie::isAdmin()||self::isLogged('HTTP_USER_AGENT')&&($_SESSION['HTTP_USER_AGENT']!=md5($_SERVER['HTTP_USER_AGENT'])));
	}

	public function userCookie()
	{
		return self::isLogged(self::$f->user_cookieid);
	}

	public function getUserCookie()
	{
		return Session::getVarStr(self::$f->user_cookieid);
	}

	public function setUserCookie($c=null)
	{
		if($c===null)
			$c=$this->uname;
		Session::setVar(self::$f->user_cookieid,$c);
	}

	public function login($flag,$user_data,$admin_nickname='',$db=null)
	{
		Session::regenerateID();
		if($flag==self::FLAG_ADMIN)
		{
			$this->setData(array('uid'=>'-1','username'=>self::$f->admin_nickname));
			if(isset($_SERVER['HTTP_USER_AGENT']))
							Session::setVar('HTTP_USER_AGENT',md5($_SERVER['HTTP_USER_AGENT']));
			Cookie::setAdmin($admin_nickname);
		}
		elseif(is_array($user_data)&&!empty($user_data)
			&&isset($user_data['uid'])&&isset($user_data['username']))
		{
			$this->setData($user_data);
			self::append($db,$this->uname);
		}
		$this->setFlag($flag,true);
	}

	public function logout($db)
	{
		if($this->userCookie())
			self::delete($db,$this->getUserCookie());
		Session::unsetSession();
	}

	public function autoLogin($caObj,$redirect_time,$redirect_location)
	{//redirect time -1 means it redirects directly via PHP

		if($caObj->isFBLogged)
		{
			Session::setVar('FBLogged',true);
			$this->setFlag(self::FLAG_FB_USER,true);
		}
		if(!isset($this->data['username']))
			die('Auto login cannot be completed. Please, login!');
		$uname=$this->data['username'];
		$this->setUserCookie($uname);
		ca_log::write_log('login',$uname,'success');
		$caObj->lang_r=self::$f->lang_reg[$caObj->ca_ulang_id];
		$caObj->lang_r['first_name']=$caObj->lang_r['name'];

		$redirect_msg=str_replace('%%time%%','<span></span>',$caObj->lang_r['redirect in']);
		$output='<br><div class="redirect_timer"><h5>'.$caObj->lang_r['registration was completed'].'</h5>'.$redirect_msg.'</div>';
		$output=$caObj->GT($output,true,$caObj->lang_r['registration'],true);

		if(strpos($redirect_location,'http')!==false)
			$cur_page='';
		else
		{
			list($cur_page)=explode('?',Linker::currentPageUrl());
			$cur_page=str_replace('centraladmin.php','',$cur_page);
		}
		if($redirect_time==-1)
		{
			header('Location: '.$cur_page.$redirect_location);
			exit;
		}
		self::$f->redirect_js='$(document).ready(function() {
			function endCountdown() {
				window.location="'.$cur_page.$redirect_location.'";
			}
			function handleTimer() {
				if(count === 0) {
					clearInterval(timer);
					endCountdown();
				} else {
					$(".redirect_timer span").html(count);
					count--;
				}
			}
			var count='.$redirect_time.';
			var timer=setInterval(function() { handleTimer(count); }, 1000);

		});';
		$output=Builder::includeScript(self::$f->redirect_js,$output);

		print $output;
		exit;
	}

	public function setData($data=array())
	{
		if(is_array($data))
			$this->data=$data;
		if(isset($data['uid']))
			$this->id = $data['uid'];
		if(isset($data['username']))
			$this->uname = $data['username'];
		if(isset($data['user_admin'])&&$data['user_admin']=='1')
		{
			$this->isAdmin=true;
			$this->setFlag(self::FLAG_ADMIN,true);
		}
		if($this->id == '-1')
		{
			$this->isAdmin=true;
			$this->setFlag(self::FLAG_ADMIN,true);
		}
		else
			$this->setFlag(self::FLAG_USER,true);
	}

	public function setNews($news)
	{
		$this->news = $news;
	}

	public function setAccess($db,$access)
	{
		if(empty($access))
			$access_data[]=array('user_id'=>$this->id,'section'=>'ALL','access_type'=>'0');
		else
		{
			foreach($access as $k=> $v)
				$access_data[]=array('user_id'=>$this->id,'section'=>$v['section'],'access_type'=>$v['access_type'],'page_id'=>$v['page_id']);
		}

		foreach($access_data as $k=> $acc)
			$db->query_insert('ca_users_access',$acc);

	}

	public function getAllData()
	{
		if($this->data===null)
			return array();
		$data=$this->data;
		$data['uid']=$this->getId();
		return $data;
	}

	public function getData($key)
	{
		return isset($this->data[$key])?$this->data[$key]:null;
	}

	public function isDataEmpty()
	{
		$data = $this->getAllData();
		return empty($data);
	}
	public function getId()
	{
		if($this->id!==null)
			return $this->id;
		return isset($this->data['uid'])?$this->data['uid']:null;
	}

	public function getUname()
	{
		if($this->uname!==null)
			return $this->uname;
		return isset($this->data['username'])?$this->data['username']:null;
	}

	public function isCurrUserLogged()
	{
		return $this->hasFlag();
	}

	public function isVisitor()
	{
		return $this->hasFlag(self::FLAG_VISITOR,true);
	}

	public function isAdminUser()
	{
		return $this->hasFlag(self::FLAG_USER|self::FLAG_ADMIN);
	}

	public function isMainAdmin()
	{
		return $this->hasFlag(self::FLAG_ADMIN,true);
	}

	public function isAdmin()
	{
		return $this->isAdmin;
	}
	public function isAdminOnPage($this_page_id='')
	{
		if($this->isAdmin || $this->isAdminUser())
			return true;

		if(isset($this->data['access']) && is_array($this->data['access']))
		{
			  foreach($this->data['access'] as $v_access)
				{
					if($v_access['section'] == '0' && $v_access['page_id'] == $this_page_id && $v_access['access_type'] == ADMIN_ON_PAGE)
						return true;
            }
		}
		return false;
	}
	public function isUser()
	{
		return $this->hasFlag(self::FLAG_USER,true);
	}

	public function isFBLogged()
	{
		return $this->hasFlag(self::FLAG_FB_USER);
	}
	public function getLoggedData($rel_path,&$name_v,&$email_v,&$surname_v)
	{
		global $admin_email;
		if(Cookie::isAdmin())
		{
			$name_v='admin';
			$email_v=$admin_email;
		}
		elseif($this->userCookie())
		{
			$name_v=$this->getUserCookie();
			$user_data=User::getUser($name_v,$rel_path);
			$email_v=$user_data['email'];
			if(isset($user_data['first_name'])&&!empty($user_data['first_name']))
			{
				$name_v=Formatter::unEsc($user_data['first_name']);
				$surname_v=Formatter::unEsc($user_data['surname']);
			}
		}
		if(strpos($email_v,'<')!=false)
			$email_v=Formatter::GFS($email_v,'<','>');
	}

	public function replaceUserFields($page,$db)
	{
		$user=$this->getUserCookie();
		if($user!='')
		{
			$user_data=User::mGetUser($user,$db,'username',false,false);
			foreach($user_data as $k=> $v)
			{
    		if(!is_array($v) && strpos(self::$f->hidden_uf,'|'.$k.'|')==false)
				{
					$rep_array=array();
					$rep_array[]=$k;
					if($k=='surname')
						$rep_array[]='last_name';
					elseif($k=='address')
						$rep_array[]='address1';
					foreach($rep_array as $vv)
						$page=str_replace('name="'.$vv.'"','name="'.$vv.'" value="'.$v.'"',$page);
				}
			}
		}
		return $page;
	}

	public function mGetLoggedValues($rel_path,$db)
	{
		global $admin_email;
		$user_data=array();
		if(Cookie::isAdmin())
		{
			$user_data['name']=$user_data['username']=self::$f->admin_nickname;
			$user_data['email']=$admin_email;
			$user_data['avatar']=self::$f->admin_avatar;
		}
		elseif($this->userCookie())
		{
			$username=$this->getUserCookie();
			$user_data=User::mGetUser($username,$db,'username',false,false);
			$user_data['name']=isset($user_data['first_name'])?$user_data['first_name']:$username;
			foreach($user_data as $k=> $v)
			{
				if(strpos(self::$f->hidden_uf,'|'.$k.'|')!==false)
					unset($user_data[$k]);
			}
		}
		if(isset($user_data['email'])&&strpos($user_data['email'],'<')!=false)
			$user_data['email']=Formatter::GFS($user_data['email'],'<','>');
		return $user_data;
	}

	public function register($db)
	{
		$this->id = $db->query_insert('ca_users',$this->data);
		return $this->id;
	}

	public function registerNews($db)
	{
		if($this->news!==null)
			foreach($this->news as $news)
				$db->query_insert('ca_users_news',$news);
	}

	public function mGetLoggedAs() //gets logged user name (even if it's admin)
	{
		if($this->uname!==null)
			return $this->uname;
		$result='';
		if($this->userCookie())
			$result=$this->getUserCookie();
		elseif(Cookie::isAdmin())
			$result=self::$f->admin_nickname!=''?self::$f->admin_nickname:'admin';
		if($result!='')
			$this->uname=$result;
		return $result;
	}

	public function mGetLoggedUser($db,$admin_email=false,$full_data=false)
	{
		if($this->data!==null)
			return $this->data;
		if(self::$f->admin_email===false)
			$admin_email=self::$f->admin_email;
		$userData = false;
		if($this->userCookie())
			$userData = User::mGetUser($this->getUserCookie(),$db,'username',$full_data,$full_data);
		elseif($this->isEZGAdminLogged())
		{
			$ua=array();
			$ua['uid']=-1;
			$ua['username']=self::$f->admin_nickname;
			$ua['email']=$admin_email;
			$ua['avatar']=self::$f->admin_avatar;
			$ua['user_admin']=0;
			$ua['status']=1;
			$userData = $ua;
		}
		if($userData)
			$this->setData($userData);
		return $userData;
	}

	public function mGetUserID($db)
	{
		if($this->id!==null)
			return $this->id;
		$result=0;
		if($this->userCookie())
		{
			$user_account=User::mGetUser($this->getUserCookie(),$db,'username',false,false);
			$result=$user_account['uid'];
			$this->id = $result;
		}
		return $result;
	}

	public static function getAvatarImage($av_path,$username,$sitepath,$float='none')
	{
		$avatar='';
		if($av_path!='')
		{
			$av_path=strpos($av_path,'http')!==false?$av_path:$sitepath.$av_path;
			$avatar=($av_path!='')?'<img class="system_img user_avatar" src="'.$av_path.'" style="height:'.self::$f->avatar_size.'px;float:'.$float.';" alt="'.$username.'" title="'.$username.'">':'';
		}
		return $avatar;
	}

	public static function getAvatarFromData($data,$db,$userName,$sitepath,$float)
	{
		if(!isset($data['avatar']) || $data['avatar']=='')
		{
			if($userName==self::$f->admin_nickname || $userName=='admin')
				$av_path=self::$f->admin_avatar;
			else
				$av_path=CA::getDBSettings($db,'c_avatar');
		}
		else
			$av_path=$data['avatar'];
		return self::getAvatarImage($av_path,$userName,$sitepath,$float);
	}

	public static function getAvatar($uid,$db,$sitepath)
	{
		 $av_path=$username='';

		 if($uid!=false)
		 {
			  if($uid==-1)
			  {
					$av_path=self::$f->admin_avatar;
					$username='';
			  }
			  else
			  {
					$usr=self::mGetUser($uid,$db,'uid');
					if(!empty($usr))
					{
						$username=$usr['username'];
						$av_path=isset($usr['avatar'])&&$usr['avatar']!=''?$usr['avatar']:CA::getDBSettings($db,'c_avatar');
					}
			  }
		 }
		 $avatar=self::getAvatarImage($av_path,$username,$sitepath);
		 return $avatar;
	}

	public function getNews($db)
	{
		if($this->news!==null)
			return $this->news;
		$this->news=self::mGetUserNews($db,'user_id= '.$this->id);
		return $this->news;
	}

	public function getAccess($db)
	{
		if($this->access!==null)
			return $this->access;
		$this->access=self::mGetUserAccess($db,'user_id= '.$this->id);
		return $this->access;

	}
}

include_once('functions_settings.php');
$mysql_charset=($f->uni)?'utf8':'';
$db=DB::dbInit($mysql_charset,$mysql_charset);
$user=new User();
?>

<?php
$version="EZGenerator Rss Reader 1.24";
/*
	rssReader.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2014 Image-line
*/

define('CLASSIC',0);
define('TICKER',1);
define('PLAYLIST',2);
define('BOARD',3);

$lang = array(
'NO' => array('Januar','Februar','Mars','April','Mai','Juni','July','August','September','Oktober','November','Desember','Søndag','Mandag','Tirsdag','Onsdag','Torsdag','Fredag','Lørdag','Sø','Ma','Ti','On','To','Fr','Lø','Les mere'),
'NL' => array('Januari','Februari','Maart','April','Mei','Juni','July','Augustus','September','October','November','December','Zondag','Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zo','Ma','Di','Wo','Do','Vr','Za','Lees meer'),
'SL' => array('Januar','Februar','Marec','April','Maj','Junij','Julij','Avgust','September','Oktober','November','December','Nedelja','Ponedeljek','Torek','Sreda','Cetrtek','Petek','Sobota','Ne','Po','To','Sr','Ce','Pe','So','Read more'),
'PT' => array('Janeiro','Fevereiro','Marco','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro','domingo','segunda feira','terca feira','quarta feira','quinta feira','sexta feira','sabado','Dom','Seg','Ter','Qua','Qui','Sex','Sab','Read more'),
'FR' => array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre','Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Di','Lu','Ma','Me','Je','Ve','Sa','En savoir plus'),
'ES' => array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre','domingo','lunes','martes','miercoles','jueves','viernes','sabado','Do','Lu','Ma','Mi','Ju','Vi','Sa','Leer más'),
'CS' => array('leden','únor','březen','duben','květen','červen','červenec','srpen','září','říjen','listopad','prosinec','neděle','pondělí','úterý','středa','čtvrtek','pátek','sobota','ne','po','út','st','čt','pá','so','Read more'),
'RU' => array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь','Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Вс','Пн','Вт','Ср','Чт','Пт','Сб','читать далее'),
'DE' => array('Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember','Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','So','Mo','Di','Mi','Do','Fr','Sa','Mehr lesen'),
'HE' => array('ינואר','פבואר','מרץ','אפריל','מאי','יוני','יולי','אוגוסט','ספטמבר','אוקטובר','נובמבר','דצמבר','ראשון','שני','שלישי','רביעי','חמישי','שישי','שבת','א\'','ב\'','ג\'','ד\'','ה\'','ו\'','שבת','Read more'),
'PL' => array('Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec','Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień','Niedziela','Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota','N','Pn','Wt','Śr','Cz','Pt','So','Read more'),
'IT' => array('Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre','Domenica','Lunedi','Martedi','Mercoledi','Giovedi','Venerdi','Sabato','Do','Lu','Ma','Me','Gi','Ve','Sa','Read more'),
'BG' => array('Януари','Февруари','Март','Април','Май','Юни','Юли','Август','Септември','Октомври','Ноември','Декември','Неделя','Понеделник','Вторник','Сряда','Четвъртък','Петък','Събота','Не','По','Вт','Ср','Че','Пе','Съ','Read more'),
'HU' => array('Január','Február','Március','Április','Május','Június','Július','Augusztus','Szeptember','Október','November','December','Vasárnap','Hétfő','Kedd','Szerda','Csütörtök','Péntek','Szombat','Va','Hé','Ke','Sz','Cs','Pé','Sz','Read more')
);

$buttonhtml='<a class="e_button" href="">%BUTTON%</a>';
$php_timezone="";
if($php_timezone!=''&&function_exists('date_default_timezone_set')) date_default_timezone_set($php_timezone);

$page_charset='UTF-8';
$use_mb=function_exists('mb_strtolower');

class RSSCache
{
	var $BASEDIR='../innovaeditor/assets/';
	var $MA=3600;

	function set($url,$rss,$pg_root)
	{
		$cache_file=$this->file_name($url,$pg_root);
		$fp=@fopen($cache_file,'w');
		if(!$fp)
			return 0;
		$data=serialize($rss);
		fwrite($fp,$data);fclose($fp);
		return $cache_file;
	}

	function get($url,$pg_root)
	{
		$cache_file=$this->file_name($url,$pg_root);
		if(!file_exists($cache_file))
			return 0;
		$fp=@fopen($cache_file,'r');
		if(!$fp)
			return 0;
		if($filesize=filesize($cache_file))
		{
			$data=fread($fp,filesize($cache_file));
			$rss=unserialize($data);
			return $rss;
		}
		return 0;
	}

	function check_cache($url,$pg_root)
	{
		$res='';
		$fname=$this->file_name($url,$pg_root);
		if(file_exists($fname))
		{
			$mtime=filemtime($fname);
			$age=time()-$mtime;
			if($this->MA>$age)
				$res='OK';
		}
		return $res;
	}

	function file_name($url,$pg_root)
	{
		$fname='cache_'.md5($url);
		$d=$this->BASEDIR;
		$d=($pg_root)?str_replace('../','',$d):$d;
		return join(DIRECTORY_SEPARATOR,array($d,$fname));
	}
	function clearall()
	{
		$files=array();
		if($handle=opendir($this->BASEDIR))
		{
			while(false!==($file=readdir($handle)))
				if($file != "." && $file != ".." && strpos($file,'cache_')===0)
					$files[]=$file;
		}
		closedir($handle);
		foreach($files as $v)
			unlink($this->BASEDIR.$v);
	}
}

class FeedFormatter
{
	public static function splitHtmlContent($string,$max_chr)
	{
		return self::xtract($string,intval($max_chr/4));
	}

	public static function _abstractProtect($match)
	{
		return preg_replace('/\s/',"\x01",$match[0]);
	}

	public static function _abstractRestore($strings)
	{
		return preg_replace('/\x01/',' ',$strings);
	}

	public static function strToLower($s)
	{
	  global $use_mb;
		return $use_mb?mb_strtolower($s,"UTF-8"):strtolower($s);
	}

	public static function isOdd($int)
	{
		return($int&1);
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
		elseif(in_array(self::strToLower($tag),array('h1','h2','h3','h4','h5','h6','p','li','ul','ol','div','span','a','strong','b','i','u','em','blockquote','font','h','td','tr','tbody','table')))
			$stack[]=$tag;
	}


	public static function xtract($text,$num)
	{
		if(preg_match_all('/\s+/',$text,$junk)<=$num)
			return $text;
		$text=preg_replace_callback('/(<\/?[^>]+\s+[^>]*>)/','self::_abstractProtect',$text);
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

	public static function gfs($src,$start,$stop)
	{
		$src=is_array($src)?array_shift($src):$src;
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

	public static function gfsAbi($src,$start,$stop)
	{
		$res2=self::gfs($src,$start,$stop);
		return $start.$res2.$stop;
	}

	public static function gfsALL($src,$start,$stop)
	{
		$res='';
		$src=is_array($src)?array_shift($src):$src;
		while(strpos($src,$start)!==false):
			$temp=self::gfs($src,$start,$stop);
			$src=str_replace($start.$temp,'',$src);$res.=$temp;
		endwhile;
		return $res;
	}

	public static function gfsAllAbi($src,$start,$stop,&$items)
	{
		$src=is_array($src)?array_shift($src):$src;
		$res='';
		$temp=self::gfsAbi($src,'<items>','</items>');
		if($temp!=='')
			$src=str_replace($temp,'',$src);
		while(strpos($src,$start)!==false):
			$temp=self::gfsAbi($src,$start,$stop);
			$src=str_replace($temp,'',$src);
			$res.=$temp;
			$items[]=$temp;
		endwhile;
		return $res;
	}

	public static function fstrip_tags($src,$len=0)
	{
		$src=urldecode($src);
		$src=strip_tags($src);
		if($len!=0 && strlen(trim($src))>$len)
			$src='';
		return $src;
	}

	public static function encx($src,$feed_chrset)
	{
		global $page_charset;

		if($feed_chrset=='')$feed_chrset='UTF-8';
		$result=str_replace("\r\n"," ",$src);
		$result=str_replace("\n"," ",$result);
		$result=str_replace("\r"," ",$result);
		if(strpos($result,'src=&quot;')!==false)  //quotes around images should not be encoded
			$result=str_replace("&quot;",'"',$result);
		$result=str_replace("'","\'",$result);
		$result=str_replace("&amp;#","&#",$result);
		$result=str_replace("&amp;","&",$result);
		$result=str_replace("&#60;","<",$result);
		$result=str_replace("&lt;","<",$result);
		$result=str_replace("&gt;",">",$result);
		if($page_charset=='UTF-8')
		{
			if($feed_chrset=='iso-8859-1')
				$result=utf8_encode($result);
			elseif($feed_chrset!=='' && $feed_chrset!='UTF-8')
			{
				if(function_exists('iconv'))
					$result=iconv($feed_chrset,$page_charset,$result);
			}
		}
		elseif($page_charset=='iso-8859-1' && $feed_chrset=='UTF-8')
			$result=utf8_decode($result);
		if(strpos($result,'news.google.com')!==false)
			$result=str_replace(array('&quot;//',"&quot;"),array('"http://','"'),$result);
		return $result;
	}

	public static function my_substr($string,$start,$stop,$utf_date_flag=false)
	{
	  global $use_mb;
		if($use_mb)
			return mb_substr($string, $start, $stop, 'UTF-8');
		else
		{
			$c=$string;$f=ord($c[0]);$nb=$stop;
			if($f>=0 && $f<=127)	$nb=$stop;
			if($f>=192 && $f<=223 && !$utf_date_flag)	$nb=$stop;
			if($f>=192 && $f<=223 && $utf_date_flag)	$nb=$stop*2;
			if($f>=224 && $f<=239 && $utf_date_flag) $nb=$stop*3;
			if($f>=240 && $f<=247 && $utf_date_flag) $nb=$stop*4;
			if($f>=248 && $f<=251 && $utf_date_flag) $nb=$stop*5;
			if($f>=252 && $f<=253 && $utf_date_flag) $nb=$stop*6;
			return substr($string,$start,$nb);
		}
	}
}

class feedLoader
{
	private $ua='Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0';
	public function loadFeed($url)
	{
		if(strpos($url,'http')===false)
			return $this->load_fopen($url);
		elseif(function_exists('curl_init'))
			return $this->load_curl($url);
		elseif(ini_get('allow_url_fopen')==true)
			return $this->load_fopen($url);
		else
			throw new Exception('allow_url_fopen is disabled on your server. Either ask your provider to enable it or use "parse feed on ezgenerator.com"');
	}

	private function load_fopen($url)
	{
		$result=file_get_contents($url);
		if($result=='')
		{
			$options=array('http'=>array(
				'method'=>"GET",
				'user_agent'=>$this->ua));
			$context=stream_context_create($options);
			$result=file_get_contents($url,false,$context);
		}
	return $result;
	}

  private function load_curl($url)
	{
    $curl=curl_init($url);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	 curl_setopt($curl, CURLOPT_USERAGENT,$this->ua);
    $result=curl_exec($curl);
    curl_close($curl);
    return $result;
  }
}

class rssReader
{
	var $ch_tags=array('title','link','description','language','copyright','managingEditor','webMaster','lastBuildDate','rating','docs');
	var $it_tags=array('title','link','description','author','category','comments','enclosure','guid','pubDate','source','media');
	var $ima_tags=array('title','link','url','width','height');
	var $ti_tags=array('title','link','description','name');

	function getCdata($src)
	{
		$result=is_array($src)?array_shift($src):$src;
		if(strpos($src,'CDATA[')!==false)
			$result=FeedFormatter::gfsALL($result,'CDATA[',']]');
		if(strpos($result,'<script')!==false)
		{
			$script=FeedFormatter::gfsAbi($src,'<script','script>');
			$result=str_replace($script,'',$result);
		}
		return $result;
	}

	function page_navigation($total,$rss_limit,$rss_page_limit)
	{
		if($total==0) return '';
		$output='';
		$total=$rss_limit>0?$rss_limit:$total;
		$pcount=ceil($total/$rss_page_limit);
		for($i=1;$i<$pcount+1;$i++)
		  $output.=' <span class="pg_nav'.($i==1?' nav_active':'').'" rel="'.$i.'" style="cursor:pointer;box-shadow: 0 0 0 2px rgba(0,0,0,0.4);background:'.($i==1?'#000':'#fff').';display:inline-block;width:8px;height:8px;border-radius:4px;margin-left:2px;" onclick="open_p('.$i.',this)"></span> ';

		return '<div class="user_nav nav_header" id="nav_header" style="margin: 4px 1px;text-align:center;">'.$output.'</div><script type="text/javascript">function open_p(pid,th){var feed=$(th).parents(".feed");var fp=$(feed).find(".rs").hide().filter("[rel="+pid+"]");$(fp).find("img").each(function(){ $(this).attr("src",$(this).attr("rel"))  });$(fp).show();if($(th).parent().hasClass("nav_footer")) {var top=$(feed).offset().top;if(top<$(window).scrollTop()) $("html,body").animate({scrollTop: top},"fast");} $(feed).find(".nav_active").removeClass("nav_active").css("background","#fff");$(feed).find(".pg_nav[rel="+pid+"]").addClass("nav_active").css("background","#000");};</script>';
	}

	function get_video_image_from_embed($url) //this is same as in functions.php, but I don't want to include entire file for this only
	{
		$image_url = parse_url($url);
		if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'
				|| $image_url['host'] == 'www.youtu.be' || $image_url['host'] == 'youtu.be')
		{
			return 'http://i3.ytimg.com/vi/'.FeedFormatter::gfs($url,'embed/','?').'/default.jpg';
		}
		else if($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com')
		{
			$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/'.substr($image_url['path'], 1).'.php'));
			return $hash[0]["thumbnail_small"];
		}
	}

	function convert_vid_to_img(&$data)
	{
		$vid_iframe=FeedFormatter::gfsAbi($data,'&lt;iframe','&lt;/iframe&gt;');
		if($vid_iframe=='&lt;iframe&lt;/iframe&gt;')
			return;
		$vid_src=FeedFormatter::gfs($vid_iframe,'src="','"');
		$data=str_replace($vid_iframe,'<img src="'.self::get_video_image_from_embed($vid_src).'" />',$data);
	}

	function parse($url,$do_caching,$pg_root)
	{
		global $page_charset;
		$url=str_replace(array('-qm-','-htp-','-htps-'),array('?','http://','https://'),$url);
		if($do_caching)
		{
			$rcache=new RSSCache;
			$res=$rcache->check_cache($url,$pg_root);
		}

		if($do_caching && $res=='OK')
			$content=$rcache->get($url,$pg_root);
		else
		{
			$content='';

			if(strpos($url,'facebook.com')!==false)
				ini_set('user_agent','Mozilla/5.0 (Windows NT 6.0; rv:8.0) Gecko/20100101 Firefox/8.0');

			$feed=new feedLoader;
			$content=$feed->loadFeed($url);
		}

		if($content!=='')
		{
			if($do_caching && $res=='')
				$rcache->set($url,$content,$pg_root);
			$counter=0;$result['charset']='';$result['items']=array();
			$atom=strpos($content,'xmlns="http://www.w3.org/2005/Atom"')!==false;
			$cntmod=strpos($content,'xmlns:content="http://purl.org/rss/1.0/modules/content/')!==false;
			if($atom)
				$content=str_replace(array('<entry','</entry>','<content','</content','<published','</published'),array('<item','</item>','<description','</description','<pubDate','</pubDate'),$content);
			if($cntmod)
				$content=str_replace(array('<content:encoded','</content:encoded','<content','</content'),array('<description','</description','<description','</description'),$content);
			preg_match("'encoding=[\'\"](.*?)[\'\"]'si",$content,$matches);
			if(isset($matches[1]))
				$result['charset']=trim($matches[1]);
			$this->charset=($result['charset']!='')?$result['charset']:'UTF-8';

			if($page_charset=='UTF-8' && $result['charset']=='UTF-8')
				$content=preg_replace("/[\x{0340}-\x{0341}\x{17A3}\x{17D3}\x{2028}-\x{2029}\x{202A}-\x{202E}\x{206A}-\x{206B}\x{206C}-\x{206D}\x{206E}-\x{206F}\x{FFF9}-\x{FFFB}\x{FEFF}\x{FFFC}\x{1D173}-\x{1D17A}]+/u","",$content);

			$items=array();
			$content_items=FeedFormatter::gfsAllAbi($content,'<item','</item>',$items);
			$content=str_replace($content_items,'',$content);
			preg_match("'<channel.*?>(.*?)</channel>'si",$content,$res_channels);
			if(!isset($res_channels[1]))
				$res_channels[1]=FeedFormatter::gfsAbi($content,'<channel>','</channel>');
			if(isset($res_channels[1]))
			{
				foreach($this->ch_tags as $cht)
				{
					preg_match("'<$cht.*?>(.*?)</$cht>'si",$res_channels[1],$matches);
					if((isset($matches[1])) && ($matches[1] != ''))
						$result[$cht]=trim($matches[1]);
				}
			}

			preg_match("'<textinput(|[^>]*[^/])>(.*?)</textinput>'si",$content,$res_text);
			if(isset($res_text[2]))
			{
				foreach($this->ti_tags as $ti)
				{
					preg_match("'<$ti.*?>(.*?)</$ti>'si",$res_text[2],$matches);
					if((isset($matches[1]))&&($matches[1] != ''))
						$result['textinput_'.$ti]=trim($matches[1]);
				}
			}

			preg_match("'<image.*?>(.*?)</image>'si",$content,$res_image);
			if(isset($res_image[1]))
			{
				foreach($this->ima_tags as $it)
				{
					preg_match("'<$it.*?>(.*?)</$it>'si",$res_image[1],$matches);
					if((isset($matches[1]))&&($matches[1]!=''))
						$result['img_'.$it]=trim($matches[1]);
				}
			}

			foreach($items as $item)
			{
				foreach($this->it_tags as $itemtag)
				{
	//check <tag> </tag> pattern
					preg_match("'<$itemtag.*?>(.*?)</$itemtag>'si",$item,$matches);
					if(isset($matches[1]))
					{
						$result['items'][$counter][$itemtag]=trim($matches[1]);
						continue; //skip next part, it's for <tag /> patterns only
					}

	//check <tag /> pattern
					preg_match("'<$itemtag(.*?)/>'si",$item,$matches);
					if((isset($matches[1]))&&($matches[1]!=''))
					{
						preg_match_all('/( \\w{1,}="[^"]+"| \\w{1,}=\\w{1,}| \\w{1,})/i',$matches[0],$attr_res,PREG_PATTERN_ORDER);
						$attr_res=array_map('trim',$attr_res[0]);
						$result['items'][$counter][$itemtag]=$attr_res;
					}
				}
				$counter++;
			}

			$result['i_count']=$counter;
			return $result;
		}
		else return false;
	}

	public function youtube_vimeo_check($src)
	{
		$src=strtolower($src);
		return strpos($src,'youtube.')!==false ||	strpos($src,'youtu.be')!==false || strpos($src,'.yimg/')!==false
				||strpos($src,'vimeo.com')!==false;
	}

	public function get_youtube($media,$w=0,$h=0)
	{
		$url_you_embed=str_replace(array('http://','https://'),'',$media);
		$short_yt=strpos($url_you_embed,'youtu.be/')!==false;
		$url_id='';

		$watch=strpos($url_you_embed,'watch%3Fv%3D');
		if($watch!==false)
			$url_id = substr($url_you_embed,$watch+12,11);
		elseif(strpos($url_you_embed,'v=')!==false)
			$url_id=substr($url_you_embed, strpos($url_you_embed,'v=')+2);
		elseif(strpos($url_you_embed,'/v/')!==false)
			$url_id=substr($url_you_embed, strpos($url_you_embed,'/v/')+3);
		elseif($short_yt)
		{
			$url_id=str_replace('youtu.be/','',$url_you_embed);
			$url_you_embed='www.youtube.com/';
		}
		if(strpos($url_id,'&')!==false)
			$url_id=substr($url_id, 0, strpos($url_id,'&'));

		$url_you_embed_final='https://'.substr($url_you_embed, 0, strpos($url_you_embed,'/')) .'/embed/'.$url_id.'?wmode=opaque';
		if($h>0)
			$yt_size=array($w,$h);
		else
			$yt_size=array(640,480);

		return '<iframe class="youtube-player" type="text/html" style="max-width:100%;border:none;'.($this->page->page_is_mobile?'':'width:'.$yt_size[0].';height:'.$yt_size[1]).'" src="'.$url_you_embed_final.'"></iframe>';
	}

	protected function get_vimeo($media,$w=0,$h=0)
	{
		$url=parse_url($media);
		if($url['host'] !== 'vimeo.com'&&$url['host'] !== 'www.vimeo.com')
			return false;
		$id=(int) substr($url['path'], 1);
		if($h>0)
			$yt_size=array($w,$h);
		else
			$yt_size=array(640,480);

		return '<iframe class="yt_auto" style="border:none;'.($this->page->page_is_mobile?'':'width:'.$yt_size[0].';height:'.$yt_size[1]).'" src="http://player.vimeo.com/video/'.$id.'" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
	}

	public function convertOldParams(&$req,$p)
	{
		foreach($p as $old=>$new)
		  if(!isset($req[$new]) && isset($req[$old]))
				$req[$new]=$req[$old];
	}

	public function show_feed($req)
	{
		global $lang,$buttonhtml;

		$this->convertOldParams($req,array('xcss'=>'x','lm'=>'m'));

		$rve_styles=array('0','4','12','20','28','36');
		$urls=explode(';',strip_tags($req['url']));
		$etarget=(isset($req['etarget']))?FeedFormatter::fstrip_tags($req['etarget'],10):'_blank';
		$cache=(isset($req['cache']))?intval($req['cache']):1;
		$root=(isset($req['root']))?intval($req['root']):0;
		$df=(isset($req['df']))?FeedFormatter::fstrip_tags($req['df']):'';
		$loc=(isset($req['loc']))?FeedFormatter::fstrip_tags($req['loc']):'';
		if(!array_key_exists($loc,$lang))
			$loc='';

		$tic_h='';$tic_h_auto='0';
		$tic_c=(isset($req['tic_c'])&&($req['tic_c']!=''))?intval($req['tic_c']):'1';
		if(isset($req['tic_h'])&&($req['tic_h']!=''))
		{
			$tic_h_auto=($req['tic_h']=='auto')?'1':'0';
			$tic_h=$tic_h_auto=='1'?' ':intval($req['tic_h']);
		}
		$tic_h2='';$tic_h3=$tic_h_auto=='1'?'auto':$tic_h;

		$layout=CLASSIC;
		if(isset($req['l']))
			$layout=intval($req['l']);
		else
		{
			$playlist=(isset($req['playlist']))?FeedFormatter::fstrip_tags($req['playlist']):false;
			if($playlist)
				$layout=PLAYLIST;
			if($tic_h!='')
				$layout=TICKER;
		}
		$desc_on=(isset($req['descon'])&&($req['descon']=='false'))?false:true;
		$media_on=isset($req['media'])?true:false;
		$enc_on=isset($req['enc'])?false:true;
		$appid=isset($req['ai'])?preg_replace("/[^0-9]/", "",$req['ai']):'202070823191961';
		$rss_limit=isset($req['max'])?intval($req['max']):0;
		$rss_page_limit=isset($req['max_page'])?intval($req['max_page']):0;

		if($rss_limit!=0 && $rss_limit<$rss_page_limit)
			$rss_page_limit=$rss_limit;

		$no_vid_player=isset($req['novid'])&&$req['novid']=='1';

		$xwidth=isset($req['twidth'])?$req['twidth']:300;
		if((strpos($xwidth,'%')!==false))
		  $tawidth='width:'.intval($xwidth).'%;';
		else
		  $tawidth='width:'.intval($xwidth).'px;';
		$xwidth=intval($xwidth);

		$entry_limit=isset($req['m'])?intval($req['m']):0;
		$display_order=((isset($req['do']) && $req['do']!='')?substr($req['do'],0,5):'dtmse').'b';

		for($i=0;$i<(count($urls));$i++)
		{
			if($i==0)
				$rs=self::parse($urls[$i],$cache,$root);
			else
			{
				$rs1=self::parse($urls[$i],$cache,$root);
				$rs_merged = array();
				$count_rs = count($rs['items']);
				$count_rs1 = count($rs1['items']);
				$bigger_rs1 = $count_rs1>$count_rs;
				for($i=0;$i<($bigger_rs1?$count_rs1:$count_rs);$i++){
				    if($bigger_rs1){
					$rs_merged['items'][]=$rs1['items'][$i];
					if(isset($rs['items'][$i]))
					    $rs_merged['items'][]=$rs['items'][$i];
				    }else{
					$rs_merged['items'][]=$rs['items'][$i];
					if(isset($rs1['items'][$i]))
					    $rs_merged['items'][]=$rs1['items'][$i];
				    }
				}
				if(count($rs_merged['items'])){
				    $rs['items'] = $rs_merged['items'];
				}
			}
		}

		$p_navigation='';

		$tic_id=(isset($req['tic_id'])&&($req['tic_id']!=''))?FeedFormatter::fstrip_tags($req['tic_id'],10):'';
		$feed_id='container_'.$tic_id;
		if(isset($rs['items']) && $rss_page_limit>0 && count($rs['items'])>$rss_page_limit)
			$p_navigation=self::page_navigation(count($rs['items']),$rss_limit,$rss_page_limit);

		if($rs===false || count($rs['items'])==0)
			 $result='<div id="empty_feed">RSS feed not found or empty!</div>';
		elseif($layout==PLAYLIST)
		{
			if(isset($rs['items'])&&(count($rs['items'])>0))
			{
				$rnd=isset($req['rand'])&&($req['rand']=='true');
				if($rnd)
				{
					$rs_keys=array_rand($rs['items'],($rss_limit>count($rs['items'])) ? count($rs['items']):$rss_limit);
					$temp_rs=array();
					foreach($rs_keys as $k=>$v)
						$temp_rs[]=$rs['items'][$v];
					$rs['items']=$temp_rs;
				}

				$counter=0;
				$itc=count($rs['items']);

				$pda=array();
				foreach($rs['items'] as $k=>$v)
					$pda[$k]=(isset($v['pubDate']))?strtotime($v['pubDate']):'';
				array_multisort($pda,SORT_DESC,SORT_NUMERIC,$rs['items']);
				if(isset($req['rev'])&&($req['rev']=='true'))
					$rs['items']=array_reverse($rs['items']);

				$result='';
				foreach($rs['items'] as $k=>$item)
				{
					$tit=self::getCdata($item['title']);
					$tit=FeedFormatter::encx($tit,$rs['charset']);
					$itemlink=self::getCdata($item['link']);

					if($enc_on && isset($item['enclosure']))
					{
						$enc_src='';$enc_type='';$enc=$item['enclosure'];
						foreach($enc as $enc_attr)
						{
							if(strpos($enc_attr,'=')!==false)
							{
								list($enc_attr_name,$enc_attr_val)=explode('=',$enc_attr);
								if($enc_attr_name == 'url')
									$enc_src=($enc_attr_val[0] == '"')?substr($enc_attr_val,1,-1):$enc_attr_val;
								if($enc_attr_name == 'type')
									$enc_type=($enc_attr_val[0] == '"')?substr($enc_attr_val,1,-1):$enc_attr_val;
							}
						}
						if($enc_type == 'audio/mpeg')
						{
							if($desc_on && (isset($item['description'])))
							{
								$desc=$item['description'];
								if($no_vid_player)
									self::convert_vid_to_img($desc);
								if(strpos($desc,'CDATA[')!==false)
								{
									$desc=self::getCdata($desc);
									$desc=FeedFormatter::encx($desc,$rs['charset']);
								}
								else
									$desc=FeedFormatter::encx($desc,$rs['charset']);

								$result.='<li><a href="'.$enc_src.'">'.$tit.'</a><p class="desc">'.$desc.'</p></li>';
							}
							else
								$result.='<li><a href="'.$enc_src.'">'.$tit.'</a></li>';
						}
					}
					$counter++;
					if(($rss_limit>0)&&($rss_limit==$counter))
						break;
				}
			}

			$r=($root?'':'../');
			$skins=array('blue','dark','il','page-player');
			$skin=isset($req['skin']) && array_search($req['skin'],$skins)?$req['skin']:'blue';
			$sc='<link rel="stylesheet" type="text/css" href="'.($root?'':'../').'extdocs/'.$skin.'.css" />'
			.'<script src="'.$r.'extdocs/soundmanager2.js"></script>'
			.'<script type="text/javascript">'
			.'var PP_CONFIG={';
			if(isset($req['ap']))
				$sc.='autoStart:true,';
			if(isset($req['pn']))
				$sc.='playNext:true,';
			$sc.='urlf:"'.$r.'extdocs"'
			.'}</script><script src="'.$r.'extdocs/'.$skin.'.js"></script>';
			$result=$sc.'<ul class="playlist '.$skin.'" style="'.$tawidth.'">'.$result.'</ul>';

		}
		else
		{
			$tic_nb=isset($req['tic_nb']);
			$tic_d=(isset($req['tic_d'])&&($req['tic_d']!=''))?intval($req['tic_d']):'3';
			if($tic_d<10)
				$tic_d=$tic_d*1000;
			$tic_du=(isset($req['tic_du'])&&($req['tic_du']!=''))?intval($req['tic_du']):'1';
			if($tic_du<10)
				$tic_du=$tic_du*1000;
			$tic_dir=(isset($req['tic_dir'])&&($req['tic_dir']!=''))?intval($req['tic_dir']):0;
			$rows=($tic_h=='' && isset($req['rows'])&&($req['rows']!=''))?intval($req['rows']):'1';
			$rowh=($rows>1 && isset($req['e_h'])&&($req['e_h']!=''))?'height:'.intval($req['e_h']).'px;':'';

			$mod=$xmod=$layout==BOARD?$rss_page_limit % $rows:0;

			if($rows>1)
				$tawidth='';

			$result='<div id="'.$feed_id.'" class="feed '.$feed_id.'" style="'.$tawidth.'">';
			if($p_navigation!='')
				$result.='<div>'.$p_navigation.'</div>';
			$ali=(isset($req['align']))?FeedFormatter::fstrip_tags($req['align'],10):0;
			$al=($ali>0)?'<div class="rvps'.$ali.'">':'';
			$ale=($ali>0)?'</div>':'';
			$link=isset($rs['link'])?self::getCdata($rs['link']):'';

			if(isset($req['headeron'])&&($req['headeron']=='true'))
			{
				$result.='<div>';
	//main title
				$tit=(isset($rs['title']))?FeedFormatter::encx(self::getCdata($rs['title']),$rs['charset']):'';
				$imatit=(isset($rs['img_title']))?FeedFormatter::encx(self::getCdata($rs['img_title']),$rs['charset']):'';
				$result.=$al.'<a class="rvts'.$rve_styles[FeedFormatter::fstrip_tags($req['h'])].'" target="'.$etarget.'" href="'.$link.'">'.$tit.'</a><br>'.$ale;
	//main image
				if(($req['ima']=='true')&&((isset($rs['img_url']))&&($rs['img_url']!= '')))
				{
					$imalink=self::getCdata($rs['img_link']);
					$result.='<a href="'.$imalink.'"><img src="'.$rs['img_url'].'" alt="'.$imatit.'"></a><br>';
				}

				if((isset($rs['description']))&&($rs['description']!==$rs['title']))
				{
					$desc=$rs['description'];
					if(strpos($desc,'CDATA[')!==false)
					{
						$desc=self::getCdata($desc);
						$result.=FeedFormatter::encx($desc,$rs['charset']);
					}
					else
					{
						$desc=FeedFormatter::encx($desc,$rs['charset']);
						$result.=$al.'<span class="rvts0">'.$desc.'</span>'.$ale;
					}
				}
				$result.='</div>';
			}

			if(isset($rs['items'])&&(count($rs['items'])>0))
			{
				$tic_id2=($tic_id!='')?'id="'.$tic_id.'"':'';
				$rnd=isset($req['rand'])&&($req['rand']=='true');
				if($rnd)
				{
					$mk=($rss_limit>count($rs['items']) || $rss_limit<1)?count($rs['items']):$rss_limit;
					$rs_keys=array_rand($rs['items'],$mk);
					$temp_rs=array();

					if(is_array($rs_keys))
						foreach($rs_keys as $k=>$v)
							$temp_rs[]=$rs['items'][$v];
					else
						$temp_rs[]=$rs['items'][$rs_keys];
					$rs['items']=$temp_rs;
				}

				$counter=0;

				$title_on=(isset($req['titleon'])&&($req['titleon']=='false'))?false:true;
				$titlelink=(isset($req['titlelink'])&&($req['titlelink']=='false'))?false:true;

				$itc=count($rs['items']);

				if(!isset($req['cbb']))
					$req['cbb']='';
				if(!isset($req['cbwidth']))
					$req['cbwidth']=0;
				$left_bo=strpos($req['cbb'],'l')!==false;
				$right_bo=strpos($req['cbb'],'r')!==false;
				$top_bo=strpos($req['cbb'],'t')!==false;
				$bot_bo=strpos($req['cbb'],'b')!==false;
				$bwidth=intval($req['cbwidth']);

				$inn_h='';
				if($tic_h!='' && $tic_h_auto=='0')
				{
					$tic_h2=$tic_h*$tic_c;$boffs=$bwidth*$tic_c;
					$inn_h='height:'.$tic_h.'px;';
					if($top_bo || $req['cbb']=='')
					{
						$tic_h+=$boffs;
						$tic_h2+=$boffs;
					}
					if($bot_bo || $req['cbb']=='')
					{
						$tic_h+=$boffs;
						$tic_h2+=$boffs;
					}
					$tic_h='height:'.$tic_h.'px;';
					$tic_h2='height:'.$tic_h2.'px;';
				}

				$tww=intval(count($rs['items'])*$xwidth);
				if($tww==0)$tww=2000;

				$class=$layout==BOARD?' class="board"':'';
				$result.='<div><div style="'.($tic_dir==1?$tic_h.'overflow:hidden;':'').'display:block;position:relative;'.((($tic_h!='') && $tic_dir==1)?$tawidth:'').'">'.
					'<ul'.$class.' '.$tic_id2.' style="display:block;list-style:none;'.($tic_dir==1?'':$tic_h2.'overflow:hidden;').'margin:0;padding:0;'.((($tic_h!='') && $tic_dir==1)?'width:'.$tww.'px;':'').'">';

				if(strpos($tawidth,'%')!==false)
				{
					$iwidth='auto';
					$rwidth=($rows>1)?intval(100/$rows).'%':$iwidth;
				}
				else
				{
					$iwidth=$xwidth-8;
					if($left_bo || $req['cbb']=='')
						$iwidth-=FeedFormatter::fstrip_tags($req['cbwidth']);
					if($right_bo || $req['cbb']=='')
						$iwidth-=FeedFormatter::fstrip_tags($req['cbwidth']);
					$rwidth=(($rows>1)?(intval($iwidth/$rows)-4):$iwidth).'px';
					$iwidth.='px';
				}

				$pda=array();
				foreach($rs['items'] as $k=>$v)
					$pda[$k]=(isset($v['pubDate']))?strtotime($v['pubDate']):'';
				if(!$rnd && count($urls)>1)
					array_multisort($pda,SORT_DESC,SORT_NUMERIC,$rs['items']);
				if(isset($req['rev'])&&($req['rev']=='true'))
					$rs['items']=array_reverse($rs['items']);

	//enclosure preparation (not needed to be calculated each time in the loop)
				$enc_height = (($rwidth < 400)?$rwidth/1.23:$rwidth/1.65).'px';
				$row=0;
				$xpg=0;

				foreach($rs['items'] as $k=>$item)
				{
					$pg=$rss_page_limit>0?ceil(($k+1)/$rss_page_limit):1;
					if($xpg!=$pg)
					{
						$xpg=$pg;
						$mod=$xmod;$row=0;
					}
					$clear=($rows>1 && (($counter%$rows)==0))?'clear:both;':'';

					$full_width=$mod>0 && (intval($row)==$row);
					if($full_width)
						$mod--;
					$xclass=$full_width?' rs_full':'';
					$row+=$full_width? 1:(1 / $rows);

					if(isset($req['x']))
						$result.='<li class="rs'.$xclass.'" rel="'.$pg.'" style="position:relative;'.($pg>1?'display:none;':'').'">';
					else
						$result.='<li class="rs" rel="'.$pg.'" style="'.($pg>1?'display:none;':'').$inn_h.';overflow:hidden;'.(($tic_dir==1)?'float:left;':'').'width:'.$rwidth.';'.$clear.$rowh.'">';

					$sections=array('d'=>'','t'=>'','m'=>'','s'=>'','e'=>'','b'=>'');//feed entry sections

					$tit=self::getCdata($item['title']);
					$tit=FeedFormatter::encx($tit,$rs['charset']);
					$title_styleid=intval($req['style']);
					$h=$title_styleid>100000?$title_styleid-100000:0;
					$title_format=($h>0)?'%s':'<span class="rvts'.$title_styleid.'">%s</span>';

					$cnt='';
					if(isset($req['cnt']))
					{
						$cnts=intval(str_replace('true','1',$req['cnt']));
						if($cnts==1)
							$cnt=sprintf($title_format,strval($counter+1).'.');
						elseif($cnts==2 || $cnts==3)
							$cnt='<img class="bullet" src="'.($root?'':'../').'images/bullet'.($cnts-1).'.gif" alt="">';
					}
					$itemlink=self::getCdata($item['link']);

					if($title_on)
					{
						if($layout==BOARD)
							$title=$tit;
						else
							$title=($titlelink)?
								'<a'.($h>0?'':' class="rvts'.($title_styleid+4).'"').' target="'.$etarget.'" href="'.$itemlink.'">'.$tit.'</a>':
								sprintf($title_format,$tit);

						$sections['t']=$al.$cnt.$title.$ale;
						if($h>0)
							$sections['t']='<h'.$h.'>'.$sections['t'].'</'.$h.'>';
						$sections['t']='<div class="rss_title">'.$sections['t'].'</div>';
					}
					else
						$result.=$cnt;

					if(($req['dateon']=='true')&&(isset($item['pubDate'])))
					{
						$pd=$item['pubDate'];
						$dtx=strtotime((string)($pd));
						$mm=strtolower(date('M',$dtx));
						if(isset($item['description']) && strpos($item['description'],'&lt;!--date:')!==false)
							$pd=FeedFormatter::gfs($item['description'],'&lt;!--date:',':date--&gt;');
						else
						{

							if($df!=='')
							{
								if($loc!='')
								{
									$dfx=str_replace('l','%1%',$df); //full day
									$dfx=str_replace('D','%2%',$dfx); //3 letters day
									$dfx=str_replace('F','%3%',$dfx); //full month
									$dfx=str_replace('M','%4%',$dfx); //3 letters month

									$pd=date($dfx,$dtx);
									if(strpos($df,'l')!==false)
									{
										$day=intval(date('w',$dtx));
										$pd=str_replace('%1%',$lang[$loc][$day+12],$pd);
									}
									if(strpos($df,'D')!==false)
									{
										$day=intval(date('w',$dtx));
										$pd=str_replace('%2%',$lang[$loc][$day+19],$pd);
									}
									if(strpos($df,'F')!==false)
									{
										$month=intval(date('n',$dtx));
										$pd=str_replace('%3%',$lang[$loc][$month-1],$pd);
									}
									if(strpos($df,'M')!==false)
									{
										$month=intval(date('n',$dtx));
										$pd=str_replace('%4%',FeedFormatter::my_substr($lang[$loc][$month-1],0,3),$pd);
									}
								}
								else
									$pd=date($df,$dtx);
							}
							elseif(strpos($pd,'.')!==false)
							{
								$pd=FeedFormatter::gfs($pd,'','.');
								$pd=str_replace('T',' ',$pd);
							}
							else
							{
								if(strpos($pd,'+')!==false)
									$pd=FeedFormatter::gfs($pd,'','+');
								if(strpos($pd,'-')!==false)
									$pd=FeedFormatter::gfs($pd,'','-');
							}
						}

						if($layout==BOARD)
							$sections['d']='<div class="rss_date">'.$pd.'</div>';
						else
							$sections['d']='<div class="rss_date '.$mm.'">'.$al.'<span class="rvts'.intval($req['datestyle']).'">'.$pd.'</span>'.$ale.'</div>';
					}

					if($enc_on && isset($item['enclosure']))
					{
						$enc_src = '';
						$enc_size = '';
						$enc_type = '';
						$enc = $item['enclosure'];
						foreach($enc as $enc_attr)
						{
							list($enc_attr_name, $enc_attr_val) = explode('=',$enc_attr);
							if($enc_attr_name == 'url')
								$enc_src=($enc_attr_val[0] == '"')?substr($enc_attr_val,1,-1):$enc_attr_val; //removing the quotes
							if($enc_attr_name == 'type')
								$enc_type=($enc_attr_val[0] == '"')?substr($enc_attr_val,1,-1):$enc_attr_val;
							if($enc_attr_name == 'length')
								$enc_size=($enc_attr_val[0] == '"')?substr($enc_attr_val,1,-1):$enc_attr_val;
						}

						if($enc_type == 'video/quicktime')
						{

							if($this->youtube_vimeo_check($enc_src))
							{
								if(strpos($enc_src,'vimeo.com'))
									 $embed=$this->get_vimeo($enc_src,$rwidth,$enc_height);
								else
									 $embed=$this->get_youtube($enc_src,$rwidth,$enc_height);

								$sections['e']=$al.'<div class="rss_enclosure">'.$embed.'</div>'.$ale;
							}
						}
						elseif($enc_type == 'image/jpeg' || $enc_type == 'image/gif' || $enc_type == 'image/png')
						{
							$img='<img style="margin: 3px 4px 3px 0;max-width:120px;'.($desc_on?'float:left;':'').'" alt="" src="'.$enc_src.'">';
							if($desc_on)
							{
								$desc=isset($item['description'])?$item['description']:'';
								if(strpos($desc,'<![CDATA[')!==false)
									$desc=str_replace('<![CDATA[','<![CDATA['.$img,$desc);
								else
									$desc=$img.$desc;
								$item['description']=$desc;
							}
						}
					}
					if($media_on && isset($item['media']))
					{
						$media_url='';
						$media_type='';
						$media=$item['media'];
						foreach($media as $media_attr)
						{
							if(strpos($media_attr,'=')!==false)
							{
								list($media_attr_name,$media_attr_val)=explode('=',$media_attr);
								if($media_attr_name == 'url')
									$media_url=($media_attr_val[0] == '"')?substr($media_attr_val,1,-1):$media_attr_val;
								if($media_attr_name == 'type')
									$media_type=($media_attr_val[0] == '"')?substr($media_attr_val,1,-1):$media_attr_val;
							}
						}
						if($media_type=='image/jpeg' || $media_type=='image/gif' || $media_type=='image/png')
							$sections['m'].='<a target="'.$etarget.'" href="'.$itemlink.'"><img class="rss_media" style="'.($layout==BOARD?'':'max-').'width:100%" src="'.$media_url.'"></a>';
					}

					if($desc_on && (isset($item['description'])))
					{
						$desc=$item['description'];
						if($no_vid_player)
							self::convert_vid_to_img($desc);
						if(strpos($desc,'CDATA[')!==false)
						{
							$desc=self::getCdata($desc);
							$temp=FeedFormatter::encx($desc,$rs['charset']);
						}
						else
						{
							$desc=FeedFormatter::encx($desc,$rs['charset']);
							if($layout!=BOARD && isset($req['descstyle']))
							{
								$dsst=intval($req['descstyle']);
								$temp=$al.'<span class="rvts'.$dsst.'">'.$desc.'</span>'.$ale;
							}
							else
								$temp=$al.$desc.$ale;
						}
						$temp=str_replace('HTTP','http',$temp);
						$temp=str_replace(' src="http',' msrc="http',$temp);
						$temp=str_replace(' src="',' src="'.dirname($link).'/',$temp);
						$temp=str_replace(' msrc="http',' src="http',$temp);
						if($layout==TICKER)
							$temp=str_replace('img src=','img class="img_loader" data-src=',$temp);
						if($rss_page_limit>0 && $pg>1)
						{
							$temp=str_replace('\'',"'",$temp);
							$temp=str_replace(' src=',' rel=',$temp);
						}

						if($entry_limit>0)
						{
							$len=strlen($temp);
							if($len>$entry_limit)
								$temp=FeedFormatter::splitHtmlContent($temp,$entry_limit);
						}
						$sections['s'].='<div class="rss_desc">'.$temp.'</div>';
					}
					$fb=isset($req['fb']);
					$tw=isset($req['tw']);
					if(isset($req['s']))
					{
						$fb=1;
						$tw=1;
					} //bw compat.
					$rm=isset($req['rm']);

					if($fb || $tw || $rm)
					{
						$sections['b'].='<div class="rss_social" style="'.($layout==BOARD?'display:none;position:absolute;bottom: 2px;right:5px;':'position:relative').';height:22px;margin:7px 0;">';
						if($rm && $layout!=BOARD)
						{
							$bc=$loc!=''?$lang[$loc][26]:'Read more';
							$bc=isset($req['rmc'])&&($req['rmc']!='')?FeedFormatter::fstrip_tags($req['rmc']):$bc;
							if($buttonhtml!='')
							{
								if(strpos($buttonhtml,'class="art-button"')!==false)
									$sections['b'].=str_replace(array('%BUTTON%','class="art-button"'),array($bc,'class="art-button" target="'.$etarget.'" href="'.$itemlink.'"'),$buttonhtml);
								elseif(strpos($buttonhtml,'class="e_button"')!==false)
									$sections['b'].=str_replace(array('%BUTTON%','class="e_button"'),array($bc,'class="e_button" target="'.$etarget.'" href="'.$itemlink.'"'),$buttonhtml);
								else
									$sections['b'].=str_replace('%BUTTON%','<a target="'.$etarget.'" href="'.$itemlink.'">'.$bc.'</a>',$buttonhtml);
							}
							else
								$sections['b'].='<a class="input1 rss_more art-button" style="height:24px;width:80px;padding:1px 3px;text-decoration:none;" target="'.$etarget.'" href="'.$itemlink.'">'.$bc.'</a>';
						}
						$itemlink_enc=urlencode($itemlink);

						if($tw)
						{
							if($layout==BOARD)
								$sections['b'].='<a target="_blank" class="rss_btn rss_twitter" href="http://twitter.com/intent/tweet?text='.$tit.'&amp;url='.$itemlink_enc.'"> </a>';
							else
								$sections['b'].='<div style="position:absolute;top:0px;right:'.($fb?'100':'0').'px;width:85px">'
									.'<iframe allowtransparency="true" frameborder="0" scrolling="no" src="//platform.twitter.com/widgets/tweet_button.html?url='.$itemlink_enc.'" style="width:130px; height:20px;"></iframe></div>';
						}
						if($fb)
						{
							if($layout==BOARD)
								$sections['b'].='<a target="_blank" class="rss_btn rss_fb" href="http://www.facebook.com/sharer/sharer.php?u='.$itemlink_enc.'&amp;t='.$tit.'"> </a>';
							else
								$sections['b'].='<div style="position:absolute;top:0px;right:0px;width:100px;">'
										.'<iframe src="//www.facebook.com/plugins/like.php?send=false&amp;layout=button_count&amp;width=110&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId='.$appid.'&amp;href='.$itemlink_enc.'" scrolling="no" frameborder="0" style="border:none;overflow:hidden;width:100px;height:21px;" allowTransparency="true"></iframe></div>';
						}
						if($layout==BOARD)
							$sections['b'].='<a class="rss_btn rss_link" href="'.$itemlink.'"> </a>';
						$sections['b'].='</div>';
					}

					if($layout==BOARD)
					{
						$result.=$sections['m'].'<div class="rss_footer">'.$sections['t'].$sections['d'].'</div>'.$sections['b'].$sections['s'];
					}
					else
					{
						$do=str_split($display_order);
						foreach($do as $s)
							if(isset($sections[$s]))
								$result.=$sections[$s];
					}

					$result.='</li>';
					$counter++;
					if(($rss_limit>0)&&($rss_limit==$counter))
						break;
				}
				$result.='</ul></div></div>';
			}
			if($p_navigation!='')
				$result.='<div>'.str_replace('nav_header"','nav_footer"',$p_navigation).'</div>';
			$result.='</div>';
			if($tic_h!='')
			{
				$ap=1;if(isset($req['ap']) && $req['ap']=='0')$ap=0;
				$result.='<script type="text/javascript">$("#'.$tic_id.'").rssticker({speed:'.$tic_du.',height:"'.$tic_h3.'",width:"'.$xwidth
					.'",delay:'.$tic_d.',hor:'.(($tic_dir==1)?'true':'false')
					.($tic_c>1?',count:'.$tic_c:'')
					.($ap==0?',autoplay:false':'')
					.($tic_nb?',hidebtn:false':'').'});</script>';
			}

			if($layout==BOARD) //board css
			{
				$result.='<style>'.
				'.board .rs{padding: 0 !important;margin: 0 1px 1px 0 !important;color:white;}'.
				'.board .rss_title{position:absolute;left:4px;bottom:16px;font: bold 13pt "open sans","segoe ui",Arial,sans-serif;}'.
				'.board .rss_date{position:absolute;left:4px;bottom:4px;color: rgba(255,255,255,0.6);font: 8pt "open sans","segoe ui",Arial,sans-serif;}'.
				'.board .rss_footer{position:absolute;height:40px;width:100%;display:block;bottom:0px;background:rgba(0,0,0,0.4);}'.
				'.board .rss_desc{display:none;position: absolute;top:0;bottom:40px;overflow:hidden;padding:4px;background:rgba(0,0,0,0.3);}'.
				'.board .rss_twitter,.board .rss_link,.board .rss_fb{display:block;float:left;background: url("'.($root?'':'../').'extimages/scripts/ui-icons.png");height: 21px;margin-right: 1px;outline: medium none;width: 21px;}'.
				'.board .rss_twitter{background-position: -158px -78px;}'.
				'.board .rss_fb{background-position: -180px -78px;}'.
				'.board .rss_link{background-position: -203px -78px;}'.
				'.board p{font-size:inherit;color:inherit;}'.
				'</style>'.
				'<script>'.
					'$(document).ready(function(){'.
					'$(".board .rss_media").mouseenter(function(){$(".board .rss_social").hide();'.
					'$(this).parents(".rs").find(".rss_desc").show();$(".board .rss_desc").css("width",($(this).width()-8));});$(".board .rss_desc").mouseleave(function(){$(this).hide();});'.
					' $(".rss_footer").mouseenter(function(){$(".board .rss_social").hide();$(this).parents(".rs").find(".rss_social").show();}); });'.
				'</script>'
				;
			}
			else
			{
				$result.='<style>'.
				'.feed p{font-size:inherit;color:inherit;}'.
				'</style>';
				if($layout==TICKER) {
					$result.='<style>'.
						    '.img_loader {border: 2px solid #f3f3f3;border-top: 2px solid #3498db;border-radius: 50%;color: transparent;text-indent: 100vw;animation-name: spin;animation-duration: 2000ms; animation-iteration-count: infinite;   animation-timing-function: linear;width:10px;height:10px;}'.
						    '@keyframes spin {from {transform:rotate(0deg);}to {transform:rotate(360deg);}}'.
						'</style>';
				}
			}
		}
		return $result;
	}

} //end rssreader class

function process_feed()
{
	global $page_charset,$version;
	if(isset($_REQUEST['action']))
	{
		if($_REQUEST['action']=="version")
		{
			echo $version;
			exit;
		}
		elseif($_REQUEST['action']=="clearcache")
		{
			$rcache=new RSSCache;
			$rcache->clearall();
		}
		elseif($_REQUEST['action']=='true' || ($_REQUEST['action']=='false'))
		{
			if(isset($_REQUEST['chrset']))
				$page_charset=$_REQUEST['chrset'];
			$rss=new rssReader;
			$result=$rss->show_feed($_REQUEST);
			if(isset($_REQUEST['use_template']))
			{
				$pname='../documents/rss_template.html';
				$fs=filesize($pname);
				if($fs>0)
				{
					$fp=fopen($pname,"r");
					$page=fread($fp,$fs);
					fclose($fp);
					$page=str_replace('%FEED%',stripcslashes($result),$page);
					print $page;
				}
				else
					print $result;
			}
			else
				print "document.write('".$result."');";
		}
	}
}

function show_feed2($req)
{
	$rss=new rssReader;
	return $rss->show_feed($req);
}

process_feed();
?>

<?php
/*
  http://www.ezgenerator.com
  Copyright (c) 2004-2015 Image-line
  global settings set inside ezg
 */

$f=new FuncConfig();
$f->jquery_ver="1.11.2";
$f->jquery_ver_mob="2.1.3";
$f->counter_images=array('15|20','15|18','15|19','9|13','15|13','12|14','6|7','11|11','15|20','14|18');
$f->page_params=array();
$f->avatar_size='32';
$f->admin_email='';
$f->ca_settings=array();
$f->url_fopen=ini_get('allow_url_fopen')!='off';
$f->hidden_uf='|password|creation_date|status|confirmed|self_registered|self_registered_id|details|';
$f->browsers=array('Unknown','IE','Opera','Firefox','Search Bot','AOL','Safari','Konqueror','IE 5','IE 6','IE 7',
	 'Opera 7','Opera 8','Firefox 1','Firefox 2','Netscape 6','Netscape 7','Firefox 3','Chrome','IE 8','IE 9',
	 'Firefox 4','Firefox 5','Firefox 6','Firefox 7','Firefox 8','Firefox 9','Firefox 10','Firefox 11','Firefox 12','IE 10',
	 'Mercury','IE11','Edge');
$f->navtop='<div class="a_n a_navtop"><!--pre-nav--><div class="a_navt">';
$f->navlist='<div class="a_n a_listing"><div class="a_navt">';
$f->navend='</div><!--post-nav--></div>';
$f->ca_rel_path=(isset($rel_path)&&$rel_path==''?'':'../');
$f->proj_id='425655185872917';
$f->internal_id=1;
$f->internal_url="";
$f->admin_cookieid='SID_ADMIN'.$f->proj_id;
$f->user_cookieid='cur_user'.$f->proj_id;
$f->site_url='http://www.kayaspirits.com/'; //don't rely on this, it's user-defined
$f->proj_pre='';
$f->inmenu=true;
$f->db=null;
$f->db_createcharset='';
$f->db_namescharset='';
$f->db_folder='ezg_data/';
$f->ca_db_fname=$f->db_folder.'centraladmin.ezg.php';
$f->ca_settings_fname=$f->ca_rel_path.$f->db_folder.'centraladmin_conf.ezg.php';
$f->sitemap_fname='sitemap.php';
$f->template_source='documents/template_source.html';
$f->max_chars=25000;
$f->cap_id='CAPTCHA_CODE';
$f->home_page='products.php';
$f->intro_page='test.html';
$f->use_mysql=true;
$f->mysql_host='localhost';
$f->mysql_dbname='kayasmu3_kayaspirits';
$f->mysql_username='kayasmu3_kayaspi';
$f->mysql_password='kay!@#';
$f->mysql_setcharset=false;
$f->mail_type="mail";
$f->SMTP_HOST='%SMTP_HOST%';
$f->SMTP_PORT='%SMTP_PORT%';
$f->SMTP_HELLO='%SMTP_HELLO%';
$f->SMTP_AUTH=('%SMTP_AUTH%'=='TRUE');
$f->SMTP_AUTH_USR='%SMTP_AUTH_USR%';
$f->SMTP_SECURE='%SMTP_SECURE%';
$f->sitemapHolder=array();
$f->ca_nav_labels=array('home'=>'+','first'=>' &lt;&lt; ','prev'=>' &lt; ','next'=>' &gt; ','last'=>' &gt;&gt; ');
$f->uni=('TRUE'=='TRUE');
$f->use_mb=($f->uni&&function_exists('mb_strtolower'));
$f->SMTP_AUTH_PWD='%SMTP_AUTH_PWD%';
$f->return_path='';
$f->sendmail_from='';
if(isset($_SERVER['SERVER_SOFTWARE']))
	$f->use_linefeed=(strpos($_SERVER['SERVER_SOFTWARE'],'Microsoft')!==false)||(strpos($_SERVER['SERVER_SOFTWARE'],'Win')!==false);
else
	$f->use_linefeed=false;
$f->lf=($f->use_linefeed?"\r\n":"\n");
define('F_LF',$f->lf);
$f->xhtml_on=false;
$f->html='HTML5';
$f->ct=($f->xhtml_on?" />":">");
define('F_BR','<br>');
$f->js_st=($f->xhtml_on?"/* <![CDATA[ */":"<!--");
$f->js_end=($f->xhtml_on?"/* ]]> */":"//-->");
$f->php_timezone='';
$f->mysql_timezone='';
$f->def_tz_set=false;
if($f->php_timezone!=''&&function_exists('date_default_timezone_set'))
{
	date_default_timezone_set($f->php_timezone);
	$f->def_tz_set=true;
}
if($f->php_timezone==''&&function_exists('date_default_timezone_get'))
	$f->php_timezone=date_default_timezone_get();

$f->tzone_offset=-10000;
$f->names_lang_sets=array('BG'=>'Bulgarian','CS'=>'Czech','DA'=>'Danish','NL'=>'Dutch','EN'=>'English','ET'=>'Estonian','FI'=>'Finnish','FR'=>'French','DE'=>'German','EL'=>'Greek','HE'=>'Hebrew','HU'=>'Hungarian','IS'=>'Icelandic','IT'=>'Italian','NO'=>'Norwegian','PL'=>'Polish','PT'=>'Portuguese','RU'=>'Russian','SK'=>'Slovak','SL'=>'Slovenian','ES'=>'Spanish','SV'=>'Swedish','ZH'=>'Chinese','UK'=>'Ukrainian','BP'=>'Brazilian'); //'CA'=>'Catalan','RO'=>'Romanian',
$f->charset_lang_map=array('BG'=>'Windows-1251','CS'=>'Windows-1250','DA'=>'iso-8859-1','NL'=>'iso-8859-1','EN'=>'iso-8859-1','ET'=>'Windows-1257','FI'=>'iso-8859-1','FR'=>'iso-8859-1','DE'=>'iso-8859-1','EL'=>'Windows-1253','HE'=>'Windows-1255','HU'=>'Windows-1250','IS'=>'iso-8859-1','IT'=>'iso-8859-1','NO'=>'iso-8859-1','PL'=>'Windows-1250','PT'=>'iso-8859-1','RU'=>'Windows-1251','SK'=>'Windows-1250','SL'=>'windows-1250','ES'=>'iso-8859-1','SV'=>'iso-8859-1');
$f->innova_lang_list=array('english'=>'en-US','danish'=>'da-DK','german'=>'de-DE','spanish'=>'es-ES','finnish'=>'fi-FI','french'=>'fr-FR','norwegian'=>'nn-NO','italian'=>'it-IT','swedish'=>'sv-SE','dutch'=>'nl-NL');
$f->innova_asset_def_size='600';
$f->cBuilderFull=0;
$f->day_names=array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
$f->month_names=array("January","February","March","April","May","June","July","August","September","October","November","December");
$f->max_rec_on_admin=20;
$f->dt64=false;  //base encode ajax posts
$f->use_captcha=true;
$f->captcha_size='numbersgray';  //captcha size is actually captcha type in ezg since v 4.0.0.402
$f->bg_tag='background: #ffffff url(../images/Martell_sudy_1024x670.jpg) repeat fixed top center;';
$f->atbg_class='t1';
$f->atbgr_class='t3';
$f->atbgc_class='t2';
$f->ftm_title='<span class="rvts8 a_editcaption">%s</span><br>';//not used
$f->fmt_star='<em style="color:red;">*</em>';
$f->fmt_hidden='<input type="hidden" name="%s" value="%s">';
$f->editor="LIVE";
$f->wrongUri404=false;
//captcha size is actually captcha type in ezg
$f->reCaptcha=$f->captcha_size=='recaptcha';
$f->slidingCaptcha=$f->captcha_size=='sliding captcha';

$f->captchajs='var captcha=$(document).ready(function(){loadCaptcha("%PATH%");});';
$f->frmvalidation='$(document).ready(function(){
    $("form[id^=%ID%]").each(function(){
        var frl=$(this),frl_id=$(this).attr("id");
        if(frl!=null){
            frl.append(\'<input type="hidden" id="cc" name="cc" value="1"/>\'); 
            frl.submit(function(event){ 
                event.preventDefault(); 
                $(".frmhint").empty(); 
                $("#"+frl_id+" .input1").removeClass("inputerror"); 
                $.post( frl.attr("action"), frl.serialize(), function(re){ 
                    if(re.charAt(0)=="1"){ 
                        msg=re.substring(1).split("|"); 
                        if(msg[1]) 
                           alert(msg[1]);
                        cc=$("#"+frl_id+" #cc"); 
                        cc.val("0"); 
                        frl.unbind("submit"); 
                        frl.submit(); 
                        cc.val("1"); 
                    }
                    else if(re.charAt(0)=="0") { 
                        errors=re.substring(1).split("|"); 
                        for(i=0;i<errors.length;i=i+2) { 
                            $("#"+frl_id+"_"+errors[i]).append(errors[i+1]); 
                            $("#"+frl_id+" input[name="+errors[i]+"]").addClass("inputerror"); 
                        } 
                        if(typeof reloadReCaptcha=="function") reloadReCaptcha(); 
                    } 
                }); 
            }); 
        } 
    }); 
});';
$f->frmvalidation2='<script type="text/javascript">$(document).ready(function(){frl=$("#%ID%");if(frl!=null){frl.append(\'<input type="hidden" id="cc" name="cc" value="1"/>\');frl.submit(function(event){event.preventDefault();$(".frmhint").empty();$("#%ID% input").removeClass("inputerror");$("#div_%ID%").addClass("ajl").css("opacity","0.2").delay(500);$.post(frl.attr("action"),frl.serialize(),function(re){$("#div_%ID%").removeClass("ajl").css("opacity","1");if(re.charAt(0)=="1"){cc=$(".%ID% #cc");cc.val("0");frl.unbind("submit");frl.submit();cc.val("1");}else if(re.charAt(0)=="0") {errors=re.substring(1).split("|");for(i=0;i<errors.length;i=i+2) {$("#%ID%_"+errors[i]).append("<br />"+errors[i+1]);$("#%ID% input[name="+errors[i]+"]").addClass("inputerror");} if(typeof reloadReCaptcha == "function") reloadReCaptcha(); }else $("#%ID%").html(re);});})}});$(document).ready(function(){$(".passreginput").pmeter();})</script>';
$f->loginvalidation='<script type="text/javascript">$(document).ready(function(){frl=$("#%ID%");if(frl!=null){frl.append(\'<input type="hidden" id="cc" name="cc" value="1"/>\');frl.submit(function(event){event.preventDefault();$(".frmhint").empty();$("#%ID% input").removeClass("inputerror");$.post(frl.attr("action"),frl.serialize(),function(re){if(re.charAt(0)=="1"){cc=$(".%ID% #cc");cc.val("0");frl.unbind("submit");frl.submit();cc.val("1");}else if(re.charAt(0)=="0") {errors=re.substring(1).split("|");for(i=0;i<errors.length;i=i+2) {$("#%ID%_"+errors[i]).append("<br />"+errors[i+1]);$("#%ID% input[name="+errors[i]+"]").addClass("inputerror");} if(typeof reloadReCaptcha == "function") reloadReCaptcha();  }});})}});</script>';
$f->editor_js=<<<MSG
<script type="text/javascript" src="%RELPATH%innovaeditor/scripts/language/%XLANGUAGE%/editor_lang.js"></script>
<script type="text/javascript" src='%RELPATH%innovaeditor/scripts/innovaeditor.js'></script>
MSG;
$f->editor_html=<<<MSG
<script type="text/javascript">
var oEdit1=new InnovaEditor("oEdit1");oEdit1.width="100%";oEdit1.height="350px";%RTL%
var dummy;
oEdit1.arrCustomButtons=[["Snippets","modalDialog('%RELPATH%innovaeditor/bootstrap/snippets.htm',900,658,'Insert Snippets');", "Snippets", "btnContentBlock.gif"]];
oEdit1.groups = [
    ["group1","",["FontName","FontSize","Superscript","ForeColor","BackColor","FontDialog","BRK","Bold","Italic", "Underline", "Strikethrough", "CompleteTextDialog", "Styles", "RemoveFormat"]],
    ["group2","",["JustifyLeft", "JustifyCenter", "JustifyRight", "Paragraph", "BRK", "Bullets", "Numbering", "Indent", "Outdent"]],
    ["group3","",["TableDialog", "Emoticons", "FlashDialog","CharsDialog", "BRK", "LinkDialog", "ImageDialog", "YoutubeDialog","Line"]],
    ["group4","",["SearchDialog", "SourceDialog","Paste","BRK","Undo","Redo"]]];
oEdit1.arrStyle=[["BODY",false,"","font: 11px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;%BACKGROUND%"],["a",false,"","font: 11px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#808080;margin:0px;"],["p",false,"","text-indent:0px;padding:0px;margin:0px;"],["h1",false,"","font: bold 19px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;margin:0px;"],["h2",false,"","font: bold 16px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;margin:0px;"],["h3",false,"","font: bold 15px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;margin:0px;"],["h4",false,"","font: bold 11px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;margin:0px;"],["h5",false,"","font: bold 9px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;margin:0px;"],["h6",false,"","font: 9px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;margin:0px;"],["h6",false,"","font: 9px Verdana, Geneva, Arial, Helvetica, sans-serif;color:#000000;margin:0px;"]];
oEdit1.flickrUser="ezgenerator";
oEdit1.css=["%RELPATH%innovaeditor/styles/default.css"];
if(typeof oEditFonts!=="undefined") for(var i=0;i<oEditFonts.length;i++) oEdit1.css.push("http://fonts.googleapis.com/css?family="+oEditFonts[i]);
oEdit1.fileBrowser="../../assetmanager/assetmanager.php?lang=%XLANGUAGE%&root=%RELPATH%";
oEdit1.customColors=["#ff4500","#ffa500","#808000","#4682b4","#1e90ff","#9400d3","#ff1493","#a9a9a9"];
oEdit1.mode="HTMLBody";oEdit1.REPLACE("htmlarea");
</script>
MSG;

$f->gfonts=array('Abel','Abril Fatface','Aclonica','Actor','Aldrich','Alike','Alice','Allan','Allerta','Allerta Stencil','Amaranth','Andika','Anonymous Pro','Antic','Anton','Architects Daughter','Arimo','Artifika',
	'Arvo','Asset','Astloch','Aubrey','Bangers','Bentham','Bevan','Bigshot One','Black Ops One','Bowlby One','Bowlby One SC','Brawler','Cabin','Cabin Sketch','Calligraffitti','Candal',
	'Cantarell','Cardo','Carme','Carter One','Changa One','Cedarville Cursive','Cherry Cream Soda','Chewy','Coda','Comfortaa','Coming Soon','Copse','Corben','Cousine','Coustard',
	'Covered By Your Grace','Crafty Girls','Crimson Text','Crushed','Cuprum','Damion','Days One','Delius','Delius Swash Caps','Delius Unicase','Didact Gothic','Dorsa','Droid Sans',
	'Droid Sans Mono','Droid Serif','EB Garamond','Expletus Sans','Fanwood Text','Federo','Fontdiner Swanky','Forum','Francois One','Goblin One','Gentium Basic','Gentium Book Basic',
	'Geo','Geostar','Geostar Fill','Give You Glory','Gloria Hallelujah','Goudy Bookletter 1911','Gravitas One','Gruppo','Hammersmith One','Holtwood One SC','Homemade Apple',
	'IM Fell DW Pica','IM Fell DW Pica SC','IM Fell Double Pica','IM Fell Double Pica SC','IM Fell English','IM Fell English SC','IM Fell French Canon',
	'IM Fell French Canon SC','IM Fell Great Primer','IM Fell Great Primer SC','Inconsolata','Indie Flower','Istok Web','Irish Grover','Josefin Sans','Josefin Slab','Judson',
	'Just Another Hand','Just Me Again Down Here','Kameron','Kelly Slab','Kenia','Kranky','Kreon','Kristi','La Belle Aurore','Lato','League Script','Leckerli One','Lekton','Limelight',
	'Lobster','Lobster Two','Lora','Loved by the King','Love Ya Like A Sister','Luckiest Guy','Maiden Orange','Mako','Marvel','Maven Pro','Meddon','MedievalSharp','Megrim',
	'Merriweather','Metrophobic','Michroma','Miltonian','Miltonian Tattoo','Modern Antiqua','Molengo','Monofett','Monoton','Montez','Mountains of Christmas','Muli','Neucha','Neuton',
	'News Cycle','Nixie One','Nobile','Nothing You Could Do','Nova Cut','Nova Flat','Nova Mono','Nova Oval','Nova Round','Nova Script','Nova Slim','Numans','Nunito',
	'OFL Sorts Mill Goudy TT','Old Standard TT','Open Sans','Open Sans Condensed','Orbitron','Oswald','Ovo','Pacifico','Passero One','Paytone One','Patrick Hand','Permanent Marker',
	'Philosopher','Play','Playfair Display','Podkova','Pompiere','Prociono','PT Sans','PT Sans Caption','PT Sans Narrow','PT Serif','Puritan','Quattrocento','Quattrocento Sans',
	'Questrial','Radley','Raleway','Rationale','Redressed','Reenie Beanie','Rochester','Rock Salt','Rokkitt','Rosario','Schoolbell','Shadows Into Light','Shanti','Short Stack','Sigmar One',
	'Six Caps','Slackey','Smokum','Smythe','Sniglet','Snippet','Special Elite','Stardos Stencil','Sunshiney','Syncopate','Tangerine','Tenor Sans','Terminal Dosis Light','Tienne','Tinos',
	'Tulpen One','Ubuntu','Ultra','UnifrakturCook','UnifrakturMaguntia','Unkempt','Unna','Varela','Varela Round','Vibur','Vidaloka','Volkhov','Vollkorn','Voltaire','VT323','Waiting for the Sunrise',
	'Wallpoet','Walter Turncoat','Wire One','Yanone Kaffeesatz','Yellowtail','Yeseva One','Zeyada');

$f->buttonhtml='<a class="e_button" href="">%BUTTON%</a>';
$f->art_prefix='art-';
$f->smenu='<a class="smenu" href="%MenuItemUrl%">%MenuItemText%</a>';
$f->ssmenu='<a class="smenu" href="%MenuItemUrl%">&nbsp;&nbsp;%MenuItemText%</a>';
$f->http_prefix=(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on')?'https://':'http://';
$f->db_charset='utf8'; // used for CA,counter,search
$f->os=array('Unknown','Win95','Win98','WinNT','W2000','WinXP','W2003','Vista','Linux','Mac','Windows','Win 7','iOS','Search Bot','android','Win 8','BlackBerry','Win 10');
$f->admin_nickname='admin';
$f->admin_nickname=($f->admin_nickname=='')?'admin':$f->admin_nickname;
$f->admin_avatar='';
$f->innova_limited=false;
$f->tooltips_js=<<<MSG
\$(document).ready(function(){\$("a.hhint,td.hhint,label.hhint").cluetip({className:"hhint",width:200,arrows:true});});
MSG;
$f->sp_pages_ids=array(OEP_PAGE,NEWSLETTER_PAGE,CALENDAR_PAGE,BLOG_PAGE,PHOTOBLOG_PAGE,PODCAST_PAGE,GUESTBOOK_PAGE,SHOP_PAGE,
	CATALOG_PAGE,SURVEY_PAGE,REQUEST_PAGE);

$f->login_cb_str='vid';
$f->session_databased=false;
$f->nivo_box='1';

$f->mobile_detected=false;
$f->direct_ranking=false;
$f->ranking_script='$(document).ready(function(){$(".ranking").ranking({numbers:true});});';
$f->ranking_average=true;  //when disabled, ranking is total (and not average)

$f->checked_users=array(); //holding already checked users to prevent multi-queries on user check

$f->comments_allowed_tags=array(
	'html'=>array('<p>','<u>','<i>','<b>','<strong>','<del>','<code>','<hr>','<em>','<ul>','<li>','<ol>'),
	'html_admin'=>array('<a>','<img>','<span>','<div>'),
	'extra'=>array('<span>','<div>')
);
// <editor-fold defaultstate="collapsed" desc="countries list">
$f->chart_colors=array("ee8888","bb5555","cccccc","000000"); //chart1 color,chart2 color,grid colors,font color,
$f->poll_templates_classes = array('template1','template2','template3');
$f->ca_users_fields_array=array("uid"=>array("display"=>"uid","type"=>"bigint(20)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid","system"=>"1"),"creation_date"=>array("display"=>"Created","type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'","itype"=>"timestamp","system"=>"1"),"status"=>array("display"=>"status","type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1","itype"=>"bool","system"=>"1"),"confirmed"=>array("display"=>"confirmed","type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1","itype"=>"bool","system"=>"1"),"self_registered"=>array("display"=>"self registered","type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1","itype"=>"bool","system"=>"1"),"self_registered_id"=>array("display"=>"self reg id","type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"","system"=>"1"),"username"=>array("display"=>"username","type"=>"varchar(100)","opt"=>"NOT NULL default ''","itype"=>"","system"=>"1"),"display_name"=>array("display"=>"display name","type"=>"varchar(100)","opt"=>"NOT NULL default ''","itype"=>"","system"=>"1"),"first_name"=>array("display"=>"Name","type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"userinput","system"=>"0"),"surname"=>array("display"=>"Surname","type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"userinput","system"=>"0"),"email"=>array("display"=>"Email","type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"","system"=>"1"),"password"=>array("display"=>"Password","type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"","system"=>"1"));
$f->ca_earea_pages=array();
$f->ca_loginids=array('28');
$f->form_class='default';
$f->form_css='';
$f->form_labels_pos='top';
$f->ca_fullscreen=true;
$f->counter_on=true;
$f->site_charsets_a=array("UTF-8");
$f->site_languages_a=array("English");
$f->inter_languages_a=array("EN");
$f->time_format_a=array("24");
$f->date_format_a=array("dd.mm.yy");
$f->use_hostname=false;
$f->mail_usebcc=true;
$f->search_templates_a=array("0");
$f->ca_profile_templates_a=array("0");
$f->error_template_a=array("0");
$f->error_iframe=false; //the iframe html string or FALSE if not used.
$f->tiny=false;
$f->ext_styles=array("normal","sub","L","XL","XXL","XXXL","heading","news_heading","menu","footer","Heading_black","media_rss","big_Heading","dark_heding","black_heding");
$f->ttype=1;
$f->httpRedirect=false;
$f->lang_reg=array("0"=>array("welcome"=>"welcome [%%username%%]","profile"=>"profile","administration panel"=>"administration","protected area"=>"Protected area login","login form msg"=>"Please Login!","login"=>"log in","forgot password"=>"Forgot your password?","not a member"=>"Not a member yet?","register"=>"Register","welcome guest"=>"Welcome Guest","use correct username"=>"Please, use correct username and password to log in. You have %%attempt%% more attempts before account is blocked.","logout"=>"log out","username"=>"username","username exists"=>"such username already exists","unexisting"=>"This Username can't be found in the database","can contain only"=>"username can contain only A-Z, a-z, - _ @ . and 0-9","username equal password"=>"username can not be equal to password","name"=>"first name","surname"=>"last name","email"=>"email","email not found"=>"This Email address can't be found in the database","no email for user"=>"Email address is not defined for this Username. Please, contact the administrator.","password"=>"password","repeat password"=>"repeat password","password and repeated password"=>"password and repeated password don't match","change password"=>"change password","old password"=>"old password","new password"=>"new password","forgotten password"=>"forgotten password","forgot password message"=>"Enter Username OR Email address, and email with instructions for resetting password will be sent to you.","check email for new password"=>"Check your email to find the new password.","check email for instructions"=>"Check your email to find instructions for resetting password.","your password should be"=>"your password should be at least five symbols","registration"=>"Registration","registration was successful"=>"Your registration was successful. To complete it, check your email and follow the instructions.","registration was completed"=>"Your registration was successfully completed. ","you have to fill"=>"You have to fill either Email address or Username","required fields"=>"required fields","code"=>"verification code","I agree with terms"=>"I agree with the %%Terms of Use%%","you must agree with terms"=>"In order to proceed, you must agree with the Terms of Use","want to receive notification"=>"I want to receive notification for","site map"=>"sitemap","page name"=>"page name","admin link"=>"admin link","edit"=>"edit","save"=>"save","submit_btn"=>"submit","submit_register"=>"Register","submit_password"=>"Send","changes saved"=>"changes saved","close"=>"Close","my orders"=>"my orders","wrong_ext"=>"Only jpg/gif/png images are allowed!","short pwd"=>"Too short","weak"=>"Weak","average"=>"Average","good"=>"Good","strong"=>"Strong","forbidden"=>"Forbidden","email in use"=>"email in use","redirect in"=>"You will be redirected in %%time%% seconds","blocked_err_msg"=>"This account is blocked. Contact administrator!","temp_blocked_err_msg"=>"This account is temporarily blocked. Try again later.","unconfirmed_msg"=>"This account is not confirmed yet!","incorrect username/password"=>"incorrect username/password","require_approval"=>"self-registered users require activation from administrator","registration failed"=>"registration failed","incorrect credentials"=>"Please, use correct username and password to log in.","account_expired_msg"=>"your account expired!","remember me"=>"Remember me","fb login"=>"FB Login"));
$f->lang_f=array("0"=>array("Email not valid"=>"E-mail address is not valid. Please change it and try again...","Emails do not match"=>"Email confirmation does not match your Email","Required Field"=>"Required Field","Checkbox unchecked"=>"Field must be checked","Captcha Message"=>"Verification code does not match","validation failed"=>"Please correct the errors on this form.","post waiting approval"=>"Your message was posted, but waiting for approval. Once approved, it will appear on page.","login on comments"=>"Please Login to post comments!","dear"=>"Dear","email in use"=>"email in use","submit_btn"=>"submit","loading"=>"Loading...","total votes"=>"Total Votes","votes"=>"votes","ranking"=>"ranking","ranking mandatory"=>"Ranking is mandatory!"));
$f->subminiforms=array();
$f->subminiforms_news=array();
//custom handler error attached
//set_error_handler("ErrorHandler::handleErrors");  //don't remove, don't uncomment


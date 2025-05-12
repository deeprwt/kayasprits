<?php
/*
	mysql data tables definition  v5.5.2
	data.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/

function sth_4($s) //moved from functions
{
	$s=str_replace(array('%92','%91','%93','%94'),array('&rsquo;','&lsquo;','&rdquo;','&ldquo;'),$s);
	$s=Formatter::sth3(urldecode($s));
	return $s;
}

function unEsc($s)
{
	return str_replace(array('\\\\','\\\'','\"'),array('\\','\'','"'),$s);
}

function create_historydb($db,$db_history_table)
{
$d_history_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"table_id"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
	"entry_id"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
	"dump"=>array("type"=>"longtext","opt"=>"NOT NULL"),
	"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"user_id"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''")
	);

return $db->create_table($d_history_fields_array,$db_history_table);
}

function insert_settings($table,$data)
{
	global $db;

	foreach($data as $k=>$v)
	{
		$rec=$db->query_first('
			SELECT *
			FROM '.$db->pre.$table.'
			WHERE skey = "'.$k.'"');
		if(empty($rec) || !$rec)
			$db->query_insert($table,array('skey'=>$k,'sval'=>$v),false,true);
		else
			$db->query_update($table, array('sval'=>$v),'skey = "'.$k.'"');
	}
}

//SEARCH DATA STRUCTURE
function create_searchdb($db,$db_charset)
{
	$db_search_table='site_search_index';
	$db_priority_table='site_search_page_priority_index';

	$d_search_fields_array=array(
	"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"page_lang"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"page_title"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
	"page_url_params"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"page_content"=>array("type"=>"text","opt"=>"NOT NULL"),
	"page_keywords"=>array("type"=>"text","opt"=>"NOT NULL default ''"),
	"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),			
	"modified_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"expired_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"entry_id"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
	"cat_id"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"user_id"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''")
	);
	$g_search_indexes=", FULLTEXT KEY (page_content)";

	$r1 = $db->update_table($d_search_fields_array,$db_search_table,array(),$g_search_indexes,'',$db_charset);

	$d_page_priority_fields_array=array(
		"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"page_priority"=>array("type"=>"int(10)","opt"=>"unsigned default 4")
	);
	$r2 = $db->create_table($d_page_priority_fields_array,$db_priority_table, '','',$db_charset);

	if(!($r1 && $r2))
		return $r1 && $r2;
	return true;
}

function create_counterdb($db,$max_chars,$db_charset,$uni,$doimport)
{
//COUNTER DATA STRUCTURE
$d_counter_main_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"ip"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"host"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"browser"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"os"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"resolution"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"referrer"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"visit_type"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default ''"),
	"hit"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
	"hit_link"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
 	"mobile"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0")
	);

$d_counter_totals_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"total_type"=>array("type"=>"varchar (20)","opt"=>"NOT NULL"),
	"total"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"));

$d_counter_loads_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"total"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"eventcount"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0")
	);

$d_counter_users_fields=array(
	"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"ip"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
	"username"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"));

//create db
	$r1 = $db->update_table($d_counter_main_fields_array,'counter_details');
	$r2 = $db->create_table($d_counter_totals_fields_array,'counter_totals');
	$r3 = $db->create_table($d_counter_loads_fields_array,'counter_pageloads',', UNIQUE (page_id)');
	$r4 = $db->create_table($d_counter_users_fields,'counter_users');
	if(!($r1 && $r2 && $r3 && $r4))
		return false;

//db convert
	if($doimport)
	{
		if($uni) $db->query('SET NAMES "'.$db_charset.'"');
		$db_details_fname='../ezg_data/counter_db.ezg.php';
		$db_totals_fname='../ezg_data/counter_totals_db.ezg.php';
		if(file_exists($db_details_fname)&&(filesize($db_details_fname)>0))
		{
			$totals_data_raw=File::read($db_totals_fname);
			$_loads=Formatter::GFS($totals_data_raw,'<loads>','</loads>');
			$_unique=Formatter::GFS($totals_data_raw,'<unique>','</unique>');
			$_first=Formatter::GFS($totals_data_raw,'<first>','</first>');
			$_returning=Formatter::GFS($totals_data_raw,'<returning>','</returning>');

			$db->query_insert('counter_totals',array('total_type'=> 'loads', 'total'=> (int)$_loads));
			$db->query_insert('counter_totals',array('total_type'=> 'unique', 'total'=> (int)$_unique));
			$db->query_insert('counter_totals',array('total_type'=> 'first', 'total'=> (int)$_first));
			$db->query_insert('counter_totals',array('total_type'=> 'returning', 'total'=> (int)$_returning));

			while(strpos($totals_data_raw,'<l_')!==false)
			{
				$p_id=Formatter::GFS($totals_data_raw,'<l_','>');
				$count=Formatter::GFS($totals_data_raw,'<l_'.$p_id.'>','</l_'.$p_id.'>');
				$db->query_insert('counter_pageloads',array('page_id'=> $p_id, 'total'=> (int)$count,"eventcount"=>0));
				$totals_data_raw=str_replace('<l_'.$p_id.'>'.$count.'</l_'.$p_id.'>', '', $totals_data_raw);
			}

			$data_buffer=''; $buffer_count=1;
			$fp=fopen($db_details_fname, 'r');
			fgetcsv($fp,$max_chars);
			while($data=fgetcsv($fp,$max_chars,'|'))
			{
				$new_data['page_id']=(int)$data[0];
				$new_data['date']=Date::buildMysqlTime($data[1]);
				$new_data['ip']=$data[2];
				$new_data['host']=$data[3];
				$new_data['browser']=$data[4];
				$new_data['os']=$data[5];
				$new_data['resolution']=$data[6];
				$new_data['referrer']=isset($data[7])? $data[7]: '';
				$new_data['visit_type']=isset($data[8])? $data[8]: '';

				$v=$n='';
				foreach($new_data as $key=>$val)
				{
					$n.="`$key`, ";
					if(Formatter::strToLower($val)=='null')
						$v.="NULL, ";
					elseif(Formatter::strToLower($val)=='now()')
						$v.="NOW(), ";
					else
						$v.= "'".$db->escape($val)."', ";
				}
				$data_buffer.= "(". rtrim($v, ', ') .")";
				$data_buffer.=($buffer_count<1000)? ',': ';';

				if($buffer_count==1000)
				{
					$db->query("INSERT INTO `".$db->pre.'counter_details'."` (". rtrim($n, ', ') .") VALUES ".$data_buffer);
					$buffer_count=1; $data_buffer='';
				}
				else
					$buffer_count++;
			}
			if($data_buffer!='')
			{
				$data_buffer=substr($data_buffer,0,strlen($data_buffer)-1).';';
				$db->query("INSERT INTO `".$db->pre.'counter_details'."` (". rtrim($n, ', ') .") VALUES ".$data_buffer);
			}

			fclose($fp);
		}
	}

	return true;
}

function create_cadb($db,$ca_pref,$db_folder,$site_languages_a,$inter_languages_a,
		  $ca_users_fields_array,$doimport)
{
//CA DATA STRUCTURE
//default ca users:
	$ca_users_fields_array_default=array(
		"uid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0","itype"=>"dbid"),
		"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'","itype"=>"timestamp"),
		"expired_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"status"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1","itype"=>"bool"),
		"pass_changeable"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1","itype"=>"bool"),
		"confirmed"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1","itype"=>"bool"),
		"self_registered"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1","itype"=>"bool"),
		"self_registered_id"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"username"=>array("type"=>"varchar(100)","opt"=>"NOT NULL default ''"),
		"display_name"=>array("type"=>"varchar(100)","opt"=>"NOT NULL default ''"),
		"first_name"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"userinput"),
		"surname"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"userinput"),
		"email"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"password"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"avatar"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''","itype"=>"userinput"));

	$d_ca_access_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"user_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"section"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default ''"),
		"page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"access_type"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"limit_own_post"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"));

	$d_ca_groups_links_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"group_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"user_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"));

	$d_ca_groups_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"name"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"description"=>array("type"=>"text","opt"=>"NOT NULL"),
		"login_redirect"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"logout_redirect"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"modified_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"custom_data"=>array("type"=>"text","opt"=>"NOT NULL"));

	$d_ca_news_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"user_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"category"=>array("type"=>"int (11)","opt"=>"unsigned NOT NULL default 0"));

	$d_ca_log_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"activity"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"user"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"result"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"ip"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"uid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0","itype"=>"dbid"));

	$d_ca_settings_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"skey"=>array("type"=>"varchar(255)","opt"=>"NOT NULL"),"sval"=>array("type"=>"text","opt"=>"NOT NULL"),
		"lang"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default ''"),
		"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"));

	$d_ca_langlabels_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"lkey"=>array("type"=>"varchar(255)","opt"=>"NOT NULL"),
		"lval"=>array("type"=>"text","opt"=>"NOT NULL"),
		"lang"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default ''"));

	$d_ca_emaildata_fields_array=array(
		"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"page_id"=>array("type"=>"varchar (255)","opt"=>"","itype"=>""),
		"send_to"=>array("type"=>"text","opt"=>"","itype"=>""),
		"reply_to"=>array("type"=>"text","opt"=>"","itype"=>""),
		"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
		"bcc"=>array("type"=>"varchar (255)","opt"=>"","itype"=>""),
		"msgfrom"=>array("type"=>"varchar (255)","opt"=>"","itype"=>""),
		"message_html"=>array("type"=>"text","opt"=>"","itype"=>""),
		"message_text"=>array("type"=>"text","opt"=>"","itype"=>""),
		"subject"=>array("type"=>"text","opt"=>"","itype"=>""),
		"success"=>array("type"=>"text","opt"=>"","itype"=>""),
		"attachments"=>array("type"=>"text","opt"=>"","itype"=>""),
		"ip"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"referer"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"));

	$d_ca_blockedips_fields_array=array(
		"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"ip"=>array("type"=>"varchar(100)","opt"=>"NOT NULL default ''"));

	$d_ca_incorrect_logins_fields_array=array(
		"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"ip"=>array("type"=>"varchar(100)","opt"=>"NOT NULL default ''"),
		"user_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"attempt_time"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"));

	$d_ca_messenger_fields_array=array(
		"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"receiver_uid"=>array("type"=>"bigint (20)","opt"=>"NOT NULL default 0"),
		"sender_uid"=>array("type"=>"bigint (20)","opt"=>"NOT NULL default 0"),
		"subject"=>array("type"=>"text","opt"=>"NOT NULL default ''"),
		"message"=>array("type"=>"text","opt"=>"NOT NULL default ''"),
		"sender_ip"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP"),
		"read_receiver"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"del_receiver"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"del_sender"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"));
	$d_ca_messenger_frients_fields_array=array(
		"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"uid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"friend_uid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"my_status"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"friend_status"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP"));
	$d_ca_messenger_settings_fields_array=array(
		"id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"uid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"skey"=>array("type"=>"text","opt"=>"NOT NULL default ''"),
		"sval"=>array("type"=>"text","opt"=>"NOT NULL default ''"));

	$ca_db_file=$ca_pref.$db_folder.'centraladmin.ezg.php';
	$ca_db_settings_file=$ca_pref.$db_folder.'centraladmin_conf.ezg.php';
	//creating db
	if(empty($ca_users_fields_array))
		$ca_users_fields_array=$ca_users_fields_array_default;
	else
		 $ca_users_fields_array=array_merge($ca_users_fields_array_default,$ca_users_fields_array);

	$r1 = $db->update_table($ca_users_fields_array,'ca_users',array(),", UNIQUE (username)",'uid');
	$r2 = $db->update_table($d_ca_groups_links_array,'ca_users_groups_links',array(),", UNIQUE (user_id)");
	$r3 = $db->update_table($d_ca_groups_array,'ca_users_groups');
	$r4 = $db->update_table($d_ca_access_fields_array,'ca_users_access');
	$r5 = $db->update_table($d_ca_news_fields_array,'ca_users_news');
	$r6 = $db->update_table($d_ca_settings_fields_array,'ca_settings',array(),", UNIQUE (skey)");
	$r7 = $db->update_table($d_ca_log_fields_array,'ca_log');
	$r8 = $db->update_table($d_ca_langlabels_fields_array,'ca_lang_labels');
	$r9 = $db->update_table($d_ca_blockedips_fields_array,'blocked_ips');
	$r10 = $db->update_table($d_ca_emaildata_fields_array,'ca_email_data');
	$r11 = $db->update_table($d_ca_incorrect_logins_fields_array,'ca_users_incorrect_logins');
	$r12 = $db->update_table($d_ca_messenger_fields_array,'ca_messenger_messages');
	$r13 = $db->update_table($d_ca_messenger_frients_fields_array,'ca_messenger_friends');
	$r14 = $db->update_table($d_ca_messenger_settings_fields_array,'ca_messenger_settings');
	if(!($r1 && $r2 && $r3 && $r4 && $r5 && $r6 && $r7 && $r8 && $r9 && $r10 && $r11 && $r12 && $r13 && $r14))
		return false;
	$db->query('DROP TABLE IF EXISTS '.$db->pre.'ca_incorrect_logins');

	//db convert settings
	if($doimport)
	{
		if(!file_exists($ca_db_settings_file))
			$ca_db_settings_file=str_replace('../','',$ca_db_settings_file);
		if(file_exists($ca_db_settings_file) &&(filesize($ca_db_settings_file)>0))
			$set=File::read($ca_db_settings_file);
		else
		  	return;

		$sitemap_data=CA::getSitemap($ca_pref,false);
		$def_lang=$inter_languages_a[array_search($sitemap_data[0][16],$site_languages_a)];

		$lang=(strpos($set,'<language>')!==false)?Formatter::GFS($set,'<language>','</language>'):$def_lang;
		$max_len=(strpos($set,'<max_visit_len>')!==false)?Formatter::GFS($set,'<max_visit_len>','</max_visit_len>'):1800;
		$num_dig=(strpos($set,'<number_digits>')!==false)?Formatter::GFS($set,'<number_digits>','</number_digits>'):8;
		$size=(strpos($set,'<size>')!==false)?Formatter::GFS($set,'<size>','</size>'):'1';
		$display=(strpos($set,'<display>')!==false)?Formatter::GFS($set,'<display>','</display>'):0;
		$graph=(strpos($set,'<graphical>')!==false)?Formatter::GFS($set,'<graphical>','</graphical>'):'1';
		$st_count=(strpos($set,'<loads_start_value>')!==false)?Formatter::GFS($set,'<loads_start_value>','</loads_start_value>'):0;
		$un_count=(strpos($set,'<unique_start_value>')!==false)?Formatter::GFS($set,'<unique_start_value>','</unique_start_value>'):0;
		$req_appr=(strpos($set,'<require_approval>')!==false)?Formatter::GFS($set,'<require_approval>','</require_approval>'):'1';

		$old_ca_settings=array('language'=>$lang,'tzone_offset'=>Formatter::GFS($set,'<tzoneoffset>','</tzoneoffset>'),
			'logout_redirect_url'=>Formatter::GFS($set,'<logout_redirect_url>','</logout_redirect_url>'),'c_max_visit_len'=>$max_len,'c_number_digits'=>$num_dig,'c_size'=>$size,'c_display'=>$display,'c_loads_start_count'=>$st_count,'c_unique_start_count'=>$un_count,'c_graphical'=>$graph,
			'sr_admin_email'=>Formatter::GFS($set,'<admin_email>','</admin_email>'),'sr_terms_url'=>Formatter::GFS($set,'<terms_url>','</terms_url>'),
			'sr_notes'=>Formatter::GFS($set,'<notes>','</notes>'),'sr_confirm_message'=>Formatter::GFS($set,'<confirm_message>','</confirm_message>'),'sr_access'=>Formatter::GFS($set,'<access>','</access>'),'sr_require_approval'=>$req_appr,
			'c_cookie_suffix'=>Formatter::GFS($set,'<counter_cookie_suffix>','</counter_cookie_suffix>'));
		insert_settings(ca_settings,$old_ca_settings);

		$fpid=Formatter::GFS($set,'<fp_','>');
		$fp_raw=Formatter::GFS($set,'<fp_'.$fpid.'>','</fp_'.$fpid.'>');
		while($fp_raw!='')
		{
			insert_settings('ca_settings',array('skey'=>'fp_'.$fpid,'sval'=>$fp_raw));
			$set=str_replace('<fp_'.$fpid.'>'.$fp_raw.'</fp_'.$fpid.'>','',$set);
			$fpid=Formatter::GFS($set,'<fp_','>');
			$fp_raw=Formatter::GFS($set,'<fp_'.$fpid.'>','</fp_'.$fpid.'>');
		}

		$c_lang=Formatter::GFS($set,'<sr_language_','>');
		$reg_lang_raw=Formatter::GFS($set,'<sr_language_'.$c_lang.'>','</sr_language_'.$c_lang.'>');
		$ca_reg_lang_settings_keys=array('sr_email_subject','sr_email_msg','sr_notif_subject','sr_notif_msg','sr_forgotpass_subject0','sr_forgotpass_msg0','sr_forgotpass_subject','sr_forgotpass_msg','sr_activated_subject','sr_activated_msg','sr_blocked_subject','sr_blocked_msg');
		while($reg_lang_raw!='')
		{
			foreach($ca_reg_lang_settings_keys as $k=> $v)
			{
				$value=Formatter::GFS($reg_lang_raw,'<'.$v.'>','</'.$v.'>');
				if(!empty($value))
					$db->query_insert('ca_lang_labels',array('lkey'=>$v,'lval'=>$value,'lang'=>$c_lang));
			}
			$set=str_replace('<sr_language_'.$c_lang.'>'.$reg_lang_raw.'</sr_language_'.$c_lang.'>','',$set);
			$c_lang=Formatter::GFS($set,'<sr_language_','>');
			$reg_lang_raw=Formatter::GFS($set,'<sr_language_'.$c_lang.'>','</sr_language_'.$c_lang.'>');
		}

		//db convert users
		$filename=$ca_db_file;
		if(!file_exists($filename))
			$filename=str_replace('../','',$filename);
		$src=File::read($filename);
		$users=Formatter::GFS($src,'<users>','</users>');
		$users_array=($users!='')?User::formatUsers($users):array();

		$selfreg_users=Formatter::GFS($src,'<selfreg_users>','</selfreg_users>');
		$selfreg_users_array=($selfreg_users!='')?User::formatUsers($selfreg_users):array();
		foreach($selfreg_users_array as $k=> $v)
			$selfreg_users_array[$k]['confirmed']=0;
		if(!empty($selfreg_users_array))
			$users_array=array_merge($users_array,$selfreg_users_array);

		foreach($users_array as $k=> $v)
		{
			$users_rec=array();
			$users_rec['username']=unEsc(urldecode($v['username']));
			$users_rec['display_name']=unEsc(urldecode($v['first_name']));
			if(array_key_exists('first_name',$ca_users_fields_array))
				$users_rec['first_name']=unEsc(urldecode($v['first_name']));
			if(array_key_exists('surname',$ca_users_fields_array))
				$users_rec['surname']=unEsc(urldecode($v['surname']));
			$users_rec['email']=urldecode($v['email']);
			$users_rec['password']=$v['password'];
			$users_rec['creation_date']=Date::buildMysqlTime($v['creation_date']);
			$users_rec['status']=(int)$v['status'];
			$users_rec['self_registered']=(int)$v['self_registered'];
			$users_rec['confirmed']=isset($v['confirmed'])?(int)$v['confirmed']:1;
			if($users_rec['confirmed']==1)
				$users_rec['uid']=(int)$v['id'];
			else
				$users_rec['self_registered_id']=$v['id'];

			$exist_record=$db->query_first('SELECT * FROM '.$db->pre.'ca_users WHERE username="'.$users_rec['username'].'"');

			if(empty($exist_record))
			{
				$user_id=$db->query_insert('ca_users',$users_rec);

				if($user_id!==false)
				{
					foreach($v['access'] as $k=> $acc)
					{
						if($acc['type']=='2')
						{
							foreach($acc['page_access'] as $k=> $page_acc)
							{
								$access_rec=array();
								$access_rec['user_id']=$user_id;
								$access_rec['section']=$acc['section'];
								$access_rec['page_id']=(int)$page_acc['page'];
								$access_rec['access_type']=(int)$page_acc['type'];
								$db->query_insert('ca_users_access',$access_rec);
							}
						}
						else
						{
							$access_rec=array();
							$access_rec['user_id']=$user_id;
							$access_rec['section']=$acc['section'];
							$access_rec['access_type']=(int)$acc['type'];
							$db->query_insert('ca_users_access',$access_rec);
						}
					}

					foreach($v['news'] as $k=> $news)
					{
						$news_rec=array();
						$news_rec['user_id']=$user_id;
						$news_rec['page_id']=(int)$news['page'];
						$news_rec['category']=(int)$news['cat'];
						$db->query_insert('ca_users_news',$news_rec);
					}
				}
			}
			else
				print "User ".strtoupper($exist_record['username']).' was not imported as its username is duplicated!</br>';
		}
	}

	return true;
}

function pblog_extract_all_records($fname,$db_field_names)
{
	global $f;
	$result=array();
	if(file_exists($fname))
	{
		$handle=fopen($fname,"r");
		fgetcsv($handle,2048);
		fgetcsv($handle,2048);
		while($data=fgetcsv($handle,$f->max_chars))
		{
			if($data[0]!="*/ ?>")
				 $result[]=pblog_build_assoc_array($data,$db_field_names);
		}
		fclose($handle);
	}
	return $result;
}

function pblog_build_assoc_array($values,$keys)  // format data as associative array
{
	$output=array();
	if(!is_array($keys))
	{
		$temp=str_replace(array(F_LF,'"'),array('',''),$keys);
		$keys=explode(',',$temp);
	}
	$index=0;
	foreach($keys as $v)
	{
		if($v=='Title')
		{
			$cur_v=Formatter::sth3(urldecode($values[$index]));
			if(!isset($values[9]))
			{
				$us=Formatter::GFS($cur_v,'%%USER','%%');
				$cur_v=str_replace('%%USER'.$us.'%%','',$cur_v);
			}
			if(!isset($values[7]))
			{
				$kw=Formatter::GFS($cur_v,'%%KEYW','%%');
				$cur_v=str_replace('%%KEYW'.$kw.'%%','',$cur_v);
			}
			$output[$v]=urlencode(html_entity_decode($cur_v));
			if(!isset($values[9]))
				$output['User']=$us;
			if(!isset($values[7]))
				$output['Keywords']=$kw;
		}
		elseif($v=='Creation_Date')
		{
			$output[$v]=(!isset($values[$index]) || $values[$index]=='')? $values[array_search('Id',$keys)]: $values[$index];
		}
		else
		{
			if(isset($values[$index]) && $v=='Publish_Status' && $values[$index]=='')
				$output[$v]='1';
			elseif(isset($values[$index]))
				$output[$v]=$values[$index];
			elseif(!isset($output[$v]))
				$output[$v]=($v=='Publish_Status')?'1':'';
		}
		$index++;
	}
	return $output;
}

function blog_extract_all_records($fname,$db_field_names,$page_type)
{
	global $f;

	$result=array();
	if(file_exists($fname))
	{
		$handle=fopen($fname,"r");
		fgetcsv($handle,2048);
		fgetcsv($handle,2048);
		while($data=fgetcsv($handle,$f->max_chars)) {
			 if($data[0]!="*/ ?>")
				 $result[]=blog_build_assoc_array($data,$db_field_names,$page_type);
		}
		fclose($handle);
	}
	return $result;
}

function blog_build_assoc_array($values,$keys,$page_type)  //format data in associative array
{
	$output=array();
	if(!is_array($keys))
	{
		 $temp=str_replace(array(F_LF,'"'),array('',''),$keys);
		 $keys=explode(',',$temp);
	}
	$index=0;
	foreach($keys as $v)
	{
		if($v=='Title')
		{
			$cur_v=Formatter::sth3(urldecode($values[$index]));
			$us=Formatter::GFS($cur_v,'%%USER','%%'); $cur_v=str_replace('%%USER'.$us.'%%','',$cur_v);
			$kw=Formatter::GFS($cur_v,'%%KEYW','%%'); $cur_v=str_replace('%%KEYW'.$kw.'%%','',$cur_v);

			$output[$v]=urlencode(html_entity_decode($cur_v));
			if(array_search('Excerpt',$keys)===false)
			{
				if($page_type=='blog' && !isset($values[11]))
					$output['User']=$us;
				elseif($page_type=='podcast' && empty($values[15]))
					$output['User']=$us;
				if($page_type=='blog' && !isset($values[9]))
					$output['Keywords']=$kw;
			}
		}
		elseif($v=='Creation_Date')
		{
			$output[$v]=(!isset($values[$index]) || $values[$index]=='')? $values[array_search('Id',$keys)]: $values[$index];
		}
		else
		{
			if(isset($values[$index]) && ($v=='Publish_Status' || $v=='Accessibility') && $values[$index]=='')
				$output[$v]='1';
			elseif(isset($values[$index]))
				$output[$v]=$values[$index];
			elseif(!isset($output[$v]))
				$output[$v]=($v=='Publish_Status' || $v=='Accessibility')?'1':'';
		}
		$index++;
	}
	return $output;
}

function blog_get_comments_count($fname)
{
	global $f;

	$result=array();
	if(file_exists($fname))
	{
		$handle=fopen($fname,"r");
		fgetcsv($handle,2048);
		fgetcsv($handle,2048);
		while($data=fgetcsv($handle,$f->max_chars))
		{
			if(strpos($data[0],"*/ ?>")===false)
				$result[$data[0]]=$data[1];
		}
		fclose($handle);
	}
	return $result;
}

//BLOG PODCAST DATA STRUCTURE
function create_blogdb($db,$page_id,$page_type,$rel_path,$db_folder,$prefix)
{
	$d_blogpodcast_posts_fields_array=array(
		 "id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"category"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"title"=>array("type"=>"text","opt"=>"NOT NULL"),
		"excerpt"=>array("type"=>"text","opt"=>"NOT NULL"),
		"content"=>array("type"=>"longtext","opt"=>"NOT NULL"),
		"keywords"=>array("type"=>"text","opt"=>"NOT NULL"),
		"weight"=>array("type"=>"bigint (20)","opt"=>"NOT NULL default 100"),
		"image_url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"image_thumb"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"image_rss"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"), //10
		"modified_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"unpublished_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"allow_comments"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default 'true'"),
		"allow_pings"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default 'false'"),
		"publish_status"=>array("type"=>"varchar (15)","opt"=>"NOT NULL default 'published'"),
		"accessibility"=>array("type"=>"varchar (15)","opt"=>"NOT NULL default 'public'"),
		"posted_by"=>array("type"=>"bigint (20)","opt"=>"NOT NULL default 0"),
		"comment_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"trackback_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"pinged_url"=>array("type"=>"text","opt"=>"NOT NULL"),//20
		"visits_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"ranking_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"ranking_total"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"permalink"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"free_field"=>array("type"=>"text","opt"=>"NOT NULL"),
		"subtitle"=>array("type"=>"text","opt"=>"NOT NULL"),
		"author"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"mediafile_url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"mediafile_size"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"explicit"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default 'no'"),//30
		"duration"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default '00:00:00'"),
		"block"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default 'no'"),
		"download_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"allow_downloads"=>array("type"=>"tinyint(1)","opt"=>"DEFAULT 1"),
		"rss_domain_info"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"rss_link_info"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"slideshow"=>array("type"=>"text","opt"=>"NOT NULL"),
		"slideshow_type"=>array("type"=>"bigint (2)","opt"=>"unsigned NOT NULL default 0") //3*
		);
	$d_blogpodcast_comments_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"comment_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"parent_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL DEFAULT 0"),
		"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"uid"=>array("type"=>"bigint (20)","opt"=>"default NULL"),
		"visitor"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"email"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"comments"=>array("type"=>"text","opt"=>"NOT NULL"),
		"ip"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
		"host"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"agent"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"approved"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"));

	$d_blogpodcats_tb_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"trackback_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"blog_name"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"title"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"excerpt"=>array("type"=>"text","opt"=>"NOT NULL"),
		"ip"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
		"host"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"agent"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"approved"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"));

	$d_blogpodcast_cat_fields_array=array(
		"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment"),
		"cid"=>array("type"=>"int(10)","opt"=>"NOT NULL"),
		"cname"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"ccolor"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"image1"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"image"),
		"description"=>array("type"=>"text","opt"=>"","itype"=>"area"),
		"description2"=>array("type"=>"text","opt"=>"","itype"=>"area"),
		"parentid"=>array("type"=>"int(10)","opt"=>"NOT NULL default -1"),
		"viewid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"page_title"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"ct_permalink"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''")
		);

	$d_blogpodcast_settings_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"skey"=>array("type"=>"varchar(255)","opt"=>"NOT NULL"), "sval"=>array("type"=>"text","opt"=>"NOT NULL"));


//creating db
	 $r1 = $db->update_table($d_blogpodcast_posts_fields_array,$page_id.'_posts',array(),", UNIQUE (entry_id)");
	 $r2 = $db->update_table($d_blogpodcast_comments_fields_array,$page_id.'_comments');
	 $r3 = $db->update_table($d_blogpodcats_tb_fields_array,$page_id.'_trackbacks');
	 $r4 = $db->create_table($d_blogpodcast_settings_fields_array,$page_id.'_settings',", UNIQUE (skey)");
	 $r5 = $db->update_table($d_blogpodcast_cat_fields_array,$page_id.'_categories',array('cid'=>"UNIQUE (cid)"), ', UNIQUE (cid)');
	 if(!($r1&&$r2&&$r3&&$r4&&$r5)) return false;

	 if($db->db_count($page_id.'_posts')==0) //do conversion
	 {
//db convert
		$db_dir=$rel_path.$db_folder;
		$visits_fname=$db_dir.$prefix."db_blog_visits.ezg.php";
		$blockedips_fname=$db_dir.$prefix."blocked_ips.ezg.php";
		$db_entries_fname=$db_dir.$prefix."db_blog_entries.ezg.php";
		$db_comments_fname=$db_dir.$prefix."db_blog_comments.ezg.php";
		$db_map_fname=$db_dir.$prefix."db_entries_comments_map.ezg.php";
		$db_trackbacks_fname=$db_dir.$prefix."db_blog_trackbacks.ezg.php";
		$db_trackbacks_map_fname=$db_dir.$prefix."db_blog_trackbacks_map.ezg.php";
		$db_pinged_blogs_fname=$db_dir.$prefix."db_pinged_blogs.ezg.php";
		if($page_type=='podcast')
			$db_entries_fields='"Id","Category","Title","Subtitle","Author","Content","Explicit","Keywords","Duration","Block","Mediafile_Url","Mediafile_Size","Image_Url","Last_Modified","Publish_Status","User","Creation_Date","Accessibility","Permalink","Free_Field"'.F_LF;
		else
			$db_entries_fields='"Id","Category","Title","Content","Image_Url","Last_Modified","Allow_Comments","Allow_Pings","Entry_Excerpt","Keywords","Publish_Status","User","Creation_Date","Accessibility","Permalink","Free_Field"'.F_LF;
		$db_comments_fields='"Entry_Id","Timestamp","Visitor","EmailAddress","Url","Comments","IP","HOST","AGENT","Approved"'.F_LF;
		$db_trackbacks_fields='"Entry_Id","Timestamp","Blog_Name","Url","Title","Excerpt","Approved","IP","HOST","AGENT"'.F_LF; //tb
		$db_pinged_blogs_fields='"Entry_Id","Pinged_Url"'.F_LF;

		if(!file_exists($blockedips_fname))
		{
			$db->query_insert($page_id.'_categories', array('cid'=>0, 'cname'=>"General", 'ccolor'=>"#FFC18A"));
			return true;
		}

		$set=File::read($blockedips_fname);
	// categories
		if(strpos($set,'<cat_')!==false)
		{
			while(strpos($set,'<cat_')!==false)
			{
				$cat_id=Formatter::GFS($set,'<cat_','>'); settype($cat_id,'integer');
				$category_info=Formatter::GFS($set,'<cat_'.$cat_id.'>','</cat_'.$cat_id.'>');
				list($name,$color)=explode('%%',$category_info);
				$db->query_insert($page_id.'_categories', array('cid'=>$cat_id, 'cname'=>Formatter::unEsc($name), 'ccolor'=>$color));
				$set=str_replace('<cat_'.$cat_id.'>'.$category_info.'</cat_'.$cat_id.'>','',$set);
			}
		}
		else
		{
			if($db->db_count($page_id.'_categories')==0)
				$db->query_insert($page_id.'_categories', array('cid'=>0, 'cname'=>"General", 'ccolor'=>"#FFC18A"));
		}

	// settings
		$lang=(strpos($set,'<language>')!==false)? Formatter::GFS($set,'<language>','</language>'):'DEF';
		$public_rss=(strpos($set,'<public_rss>')!==false)? Formatter::GFS($set,'<public_rss>','</public_rss>'):'0';
		if($page_type=='podcast')
			$use_youtube_player=(strpos($set,'<use_youtube_player>')!==false)? Formatter::GFS($set,'<use_youtube_player>','</use_youtube_player>'):'1';

		$old_settings=array('language'=>$lang,'public_rss'=>$public_rss);
		if($page_type=='podcast')
			$old_settings['use_youtube_player']=$use_youtube_player;

	// posts
		$all_posts=blog_extract_all_records($db_entries_fname,$db_entries_fields,$page_type);
		$all_comments_count=blog_get_comments_count($db_map_fname);
		if($page_type=='blog')
			$all_tb_count=blog_get_comments_count($db_trackbacks_map_fname);

		$pinged=array();
		if($page_type=="blog")
		{
			$all_pinged=blog_extract_all_records($db_pinged_blogs_fname,$db_pinged_blogs_fields,$page_type);
			if(!empty($all_pinged))
			{
				foreach($all_pinged as $k=>$v)
					$pinged[$v['Entry_Id']][]=urldecode($v['Pinged_Url']);
			}
		}

		$users_data=User::getAllUsers(true);

		// comments
		$all_comments=blog_extract_all_records($db_comments_fname,$db_comments_fields,$page_type);
		if(!empty($all_comments))
		{
			foreach($all_comments as $k=>$v)
			{
				$com_rec=array();
				$com_rec['comment_id']=(int) $v['Timestamp'];
				$com_rec['entry_id']=(int) $v['Entry_Id'];
				$com_rec['date']=Date::buildMysqlTime($v['Timestamp']);
				foreach(array('Visitor','Email','Url','Comments','IP','HOST','AGENT','Approved') as $kk=>$index)
				{
					if($index=='Comments')
						$com_rec[Formatter::strToLower($index)]=sth_4($v[$index]);
					elseif($index=='Email')
						$com_rec[Formatter::strToLower($index)]=Formatter::sth3(urldecode($v['EmailAddress']));
					else
						$com_rec[Formatter::strToLower($index)]=Formatter::sth3(urldecode($v[$index]));
				}
				$db->query_insert($page_id.'_comments',$com_rec);
			}
		}
		// posts
		foreach($all_posts as $k=>$v)
		{
			$post_rec=array();
			$post_rec['entry_id']=(int) $v['Id'];
			$post_rec['category']=(int) $v['Category'];
			$post_rec['title']=sth_4($v['Title']);
			$post_rec['excerpt']=(isset($v['Entry_Excerpt']))?sth_4($v['Entry_Excerpt']):'';
			$post_rec['content']=sth_4($v['Content']);
			$post_rec['keywords']=sth_4($v['Keywords']);
			$post_rec['image_url']=Formatter::sth3(urldecode($v['Image_Url']));

			$posted_by_user=Formatter::sth3(urldecode($v['User']));
			if(isset($users_data[$posted_by_user]['uid']))
				$post_rec['posted_by']=$users_data[$posted_by_user]['uid'];
			elseif(isset($users_data[$posted_by_user]['id']))
				$post_rec['posted_by']=$users_data[$posted_by_user]['id'];
			else
				$post_rec['posted_by']=-1;

			$post_rec['creation_date']=Date::buildMysqlTime($v['Creation_Date']);
			$post_rec['modified_date']=Date::buildMysqlTime($v['Last_Modified']);
			if(isset($v['Allow_Comments']))
				$post_rec['allow_comments']=($v['Allow_Comments']=='0'? 'false': 'true');
			if(isset($v['Allow_Pings']))
				$post_rec['allow_pings']=($v['Allow_Pings']=='1'? 'true': 'false');
			if(isset($all_comments_count[$v['Id']]))
				$post_rec['comment_count']=$all_comments_count[$v['Id']];
			if(isset($all_tb_count[$v['Id']]))
				$post_rec['trackback_count']=$all_tb_count[$v['Id']];

			if(isset($pinged[$v['Id']]))
				$pinged_value=implode(' ',$pinged[$v['Id']]);
			$post_rec['pinged_url']=(isset($pinged_value)? $pinged_value: '');  $pinged_value='';

			if($page_type=='podcast')
			{
				$post_rec['subtitle']=sth_4($v['Subtitle']);
				$post_rec['author']=sth_4($v['Author']);
				$post_rec['mediafile_url']=Formatter::sth3(urldecode($v['Mediafile_Url']));
				$post_rec['mediafile_size']=Formatter::sth3(urldecode($v['Mediafile_Size']));
				$post_rec['explicit']=$v['Explicit'];
				$post_rec['duration']=$v['Duration'];
				$post_rec['block']=$v['Block'];
			}

			$post_rec['publish_status']=($v['Publish_Status']=='1'? 'published': ($v['Publish_Status']=='2'? 'pending': 'unpublished'));
			$post_rec['accessibility']=($v['Accessibility']=='1'? 'public': 'hidden');

			$set_v=$set; $visits_file_flag=Formatter::GFS($set,'<visits_file_flag>','</visits_file_flag>');
			if($visits_file_flag!='')
			{
				if(file_exists($visits_fname))
					$set_v=file_get_contents($visits_fname);
			}

			if(strpos($set_v,'<v_'.$v['Id'])!==false)
			{
				$v_v=Formatter::GFS($set_v,'<v_'.$v['Id'].'>','</v_'.$v['Id'].'>');
				$post_rec['visits_count']=(int) $v_v;
			}
			if(strpos($set_v,'<r_'.$v['Id'])!==false)
		   {
				$r_all=Formatter::GFS($set_v,'<r_'.$v['Id'].'>','</r_'.$v['Id'].'>');
				$r_c=Formatter::GFS($r_all,'<c>','</c>');
				$r_t=Formatter::GFS($r_all,'<t>','</t>');
				$post_rec['ranking_count']=(int) $r_c;
				$post_rec['ranking_total']=(int) $r_t;
			}
			if($page_type=='podcast' && strpos($set_v,'<d_'.$v['Id'])!==false)
			{
				$v_d=Formatter::GFS($set_v,'<d_'.$v['Id'].'>','</d_'.$v['Id'].'>');
				$post_rec['download_count']=(int) $v_d;
			}

			$post_rec['permalink']=(isset($v['Permalink'])?Formatter::sth3(urldecode($v['Permalink'])):'');
			$post_rec['free_field']=(isset($v['Free_Field'])?sth_4($v['Free_Field']):'');

			$db->query_insert($page_id.'_posts',$post_rec);
		}
	// trackbacks
		if($page_type=="blog")
		{
			$all_tb=blog_extract_all_records($db_trackbacks_fname,$db_trackbacks_fields,$page_type);
			if(!empty($all_tb))
			{
				foreach($all_tb as $k=>$v)
				{
					$com_rec=array();
					$com_rec['trackback_id']=(int) $v['Timestamp'];
					$com_rec['entry_id']=(int) $v['Entry_Id'];
					$com_rec['date']=Date::buildMysqlTime($v['Timestamp']);
					foreach(array('Blog_Name','Url','Title','Excerpt','IP','HOST','AGENT','Approved') as $kk=>$index)
						$com_rec[Formatter::strToLower($index)]=sth_4($v[$index]);
					$db->query_insert($page_id.'_trackbacks',$com_rec);
				}
			}
		}
	}

	return true;
}

//PHOTOBLOG DATA STRUCTURE

function photoblog_build_image_url($image_field,$rel_path)
{
	$result=Formatter::sth(urldecode($image_field));
	if(substr($result,0,4)!='php/')
		$result=str_replace('../','',$result); else $result=str_replace('../','',$rel_path.'photoblog/'.$result);
	return $result;
}

function create_photoblogdb($db,$page_id,$rel_path,$db_folder,$prefix,$max_chars)
{
	$d_photoblog_posts_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"category"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"title"=>array("type"=>"text","opt"=>"NOT NULL"),
		"content"=>array("type"=>"longtext","opt"=>"NOT NULL"),
		"keywords"=>array("type"=>"text","opt"=>"NOT NULL"),
		"image_url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"thumbnail_url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"modified_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"allow_comments"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default 'true'"),
		"publish_status"=>array("type"=>"varchar (15)","opt"=>"NOT NULL default 'published'"),
		"posted_by"=>array("type"=>"bigint (20)","opt"=>"NOT NULL default 0"),
		"comment_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"exif_data"=>array("type"=>"longtext","opt"=>"NOT NULL"),
		"visits_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"ranking_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"ranking_total"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"permalink"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"image_url_edit"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"slideshow"=>array("type"=>"text","opt"=>"NOT NULL"),
		"slideshow_type"=>array("type"=>"bigint (2)","opt"=>"unsigned NOT NULL default 0"));

	$d_photoblog_comments_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"comment_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"uid"=>array("type"=>"bigint (20)","opt"=>"default NULL"),
		"visitor"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"email"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"comments"=>array("type"=>"text","opt"=>"NOT NULL"),
		"ip"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
		"host"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"agent"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"approved"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"),
		"parent_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL DEFAULT 0"));

	$d_photoblog_cat_fields_array=array("id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment"),
		"cid"=>array("type"=>"int(10)","opt"=>"NOT NULL"),
		"cname"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"ccolor"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"image1"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"image"),
		"description"=>array("type"=>"text","opt"=>"","itype"=>"area"),
		"parentid"=>array("type"=>"int(10)","opt"=>"NOT NULL default -1"),
		"viewid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"page_title"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"ct_permalink"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''")
		);

	$d_photoblog_settings_fields_array=array(
		 "id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		 "skey"=>array("type"=>"varchar(255)","opt"=>"NOT NULL"),
		 "sval"=>array("type"=>"text","opt"=>"NOT NULL")
		);

	//creating db
	$r1 = $db->update_table($d_photoblog_posts_fields_array,$page_id.'_posts',array(),", UNIQUE (entry_id)");
	$r2 = $db->update_table($d_photoblog_comments_fields_array,$page_id.'_comments');
	$r3 = $db->create_table($d_photoblog_settings_fields_array,$page_id.'_settings',", UNIQUE (skey)");
	$r4 = $db->update_table($d_photoblog_cat_fields_array,$page_id.'_categories',array('cid'=>"UNIQUE (cid)"),', UNIQUE (cid)');
	if(!($r1&&$r2&&$r3&&$r4))
		return false;

	if($db->db_count($page_id.'_posts')==0) //do conversion
	{
		$blog_db_dir=$rel_path.$db_folder;
		$blockedips_fname=$blog_db_dir.$prefix."blocked_ips.ezg.php";
		$db_entries_fname=$blog_db_dir.$prefix."db_blog_entries.ezg.php";
		$db_comments_fname=$blog_db_dir.$prefix."db_blog_comments.ezg.php";
		$db_map_fname=$blog_db_dir.$prefix."db_entries_comments_map.ezg.php";
		$db_entries_fields='"Id","Category","Title","Content","Image_Url","Thumbnail_Url","Last_Modified","Keywords","Publish_Status","User","Creation_Date","Allow_Comments"'.F_LF;
		$db_comments_fields='"Photo_Id","Timestamp","Visitor","EmailAddress","Url","Comments","IP","HOST","AGENT","Approved"'.F_LF;

	// categories
		if(file_exists($blockedips_fname) &&(filesize($blockedips_fname)>0))
	 		$set=File::read($blockedips_fname);
		else
		{
			$db->query_insert($page_id.'_categories', array('cid'=>0, 'cname'=>"General", 'ccolor'=>"#FFC18A"));
		  	return true;
		}

		if(strpos($set,'<cat_')!==false)
		{
			while(strpos($set,'<cat_')!==false)
			{
				$cat_id=Formatter::GFS($set,'<cat_','>'); settype($cat_id,'integer');
				$category_info=Formatter::GFS($set,'<cat_'.$cat_id.'>','</cat_'.$cat_id.'>');
				list($name,$color)=explode('%%',$category_info);
				$db->query_insert($page_id.'_categories', array('cid'=>$cat_id, 'cname'=>Formatter::unEsc($name), 'ccolor'=>$color));
				$set=str_replace('<cat_'.$cat_id.'>'.$category_info.'</cat_'.$cat_id.'>','',$set);
			}
		}
		else
		{
			if($db->db_count($page_id.'_categories')==0)
				$db->query_insert($page_id.'_categories', array('cid'=>0, 'cname'=>"General", 'ccolor'=>"#FFC18A"));
		}

	// settings
		$lang=(strpos($set,'<language>')!==false)? Formatter::GFS($set,'<language>','</language>'):'EZG';
		$public_rss=(strpos($set,'<public_rss>')!==false)? Formatter::GFS($set,'<public_rss>','</public_rss>'):'0';
		$old_settings=array('language'=>$lang,'public_rss'=>$public_rss);

	//posts
		$all_posts=pblog_extract_all_records($db_entries_fname,$db_entries_fields);
		$all_comments_count=array();
		if(file_exists($db_map_fname))
		{
			$handle=fopen($db_map_fname,"r");
			fgetcsv($handle,2048);
			fgetcsv($handle,2048);
			while($data=fgetcsv($handle,$max_chars))
		   {
				if(strpos($data[0],"*/ ?>")===false)
					$all_comments_count[$data[0]]=$data[1];
			}
			fclose($handle);
		}
		$users_data=User::getAllUsers(true);

		// comments
		$all_comments=pblog_extract_all_records($db_comments_fname,$db_comments_fields);
		if(!empty($all_comments))
		{
			foreach($all_comments as $k=>$v)
			{
				$com_rec=array();
				$com_rec['comment_id']=(int) $v['Timestamp'];
				$com_rec['entry_id']=(int) $v['Photo_Id'];
				$com_rec['date']=Date::buildMysqlTime($v['Timestamp']);
				foreach(array('Visitor','Email','Url','Comments','IP','HOST','AGENT','Approved') as $index)
				{
					if($index=='Email')
						$com_rec[Formatter::strToLower($index)]=Formatter::sth3(urldecode($v['EmailAddress']));
					else
						$com_rec[Formatter::strToLower($index)]=sth_4($v[$index]);
				}
				$db->query_insert($page_id.'_comments',$com_rec);
			}
		}
		// posts
		foreach($all_posts as $k=>$v)
		{
			$post_rec=array();
			$post_rec['entry_id']=(int) $v['Id'];
			$post_rec['category']=(int) $v['Category'];
			$post_rec['title']=sth_4($v['Title']);
			$post_rec['content']=sth_4($v['Content']);
			$post_rec['keywords']=sth_4($v['Keywords']);
			$post_rec['image_url']=Formatter::sth3(urldecode($v['Image_Url']));
			$post_rec['thumbnail_url']=Formatter::sth3(urldecode($v['Thumbnail_Url']));
			$post_rec['creation_date']=Date::buildMysqlTime($v['Creation_Date']);
			$post_rec['modified_date']=Date::buildMysqlTime($v['Last_Modified']);
			$post_rec['publish_status']=($v['Publish_Status']=='1'? 'published': ($v['Publish_Status']=='2'? 'pending': 'unpublished'));

			$exif_fname=$rel_path.photoblog_build_image_url($post_rec['image_url'],$rel_path).".php"; // EXIF data
			if(file_exists($exif_fname))
				$exif_buffer=File::read($exif_fname);
			$post_rec['exif_data']=(isset($exif_buffer)? urlencode($exif_buffer): '');

			$posted_by_user=Formatter::sth3(urldecode($v['User']));
			if(isset($users_data[$posted_by_user]['uid']))
				$post_rec['posted_by']=$users_data[$posted_by_user]['uid'];
			elseif(isset($users_data[$posted_by_user]['id']))
				$post_rec['posted_by']=$users_data[$posted_by_user]['id'];
			else
				$post_rec['posted_by']=-1;

			if(isset($v['Allow_Comments']))
				$post_rec['allow_comments']=($v['Allow_Comments']=='0'? 'false': 'true');
			if(isset($all_comments_count[$v['Id']]))
				$post_rec['comment_count']=$all_comments_count[$v['Id']];

			$db->query_insert($page_id.'_posts',$post_rec);
		}
	}
	return true;
}

function cal_build_ass_array_record($value,$key)
{
	$output=array();
	if(!is_array($key))
	{
		$temp=str_replace(array(F_LF,'"'),array('',''),$key);
		$key=explode(',',$temp);
	}
	foreach($key as $v)
	{
		if($v=='Location')
		{
			$current_v=urldecode(current($value));
			$t=Formatter::GFS($current_v,'%%USER','%%');  $h=Formatter::GFS($current_v,'%%HIDDEN','HIDDEN%%'); $d=Formatter::GFS($current_v,'%%DLINE','DLINE%%');
			$output[$v]=str_replace(array('%%USER'.$t.'%%','%%HIDDEN'.$h.'HIDDEN%%','%%DLINE'.$d.'DLINE%%'),array('','',''),$current_v);
			$output['USER']=$t; $output['HIDDEN']=$h; $output['DEADLINE']=$d;
		}
		else
			$output[$v]=current($value);
		next($value);
	}
	return $output;
}

function cal_db_get_all($db_fname,$id_as_index=true)
{
	global $f;
	$result=array();
	if(file_exists($db_fname))
	{
		$handle=fopen($db_fname,"r");
		fgetcsv($handle,2048);
		$db_field_names=fgetcsv($handle,2048);
		while($data=fgetcsv($handle,$f->max_chars))
		{
			if($data[0]!="*/ ?>")
			{
				if($id_as_index)
					$result[$data[0]]=cal_build_ass_array_record($data,$db_field_names);
				else
					$result[]=cal_build_ass_array_record($data,$db_field_names);
			}
		}
		fclose($handle);
		return $result;
	}
}

//CALENDAR DATA STRUCTURE
function create_calendardb($db,$page_id,$rel_path,$db_folder,$prefix,$mails_list)
{
	global $user;
	$d_calendar_posts_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"event_id"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default '0'"),
		"category"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"short_description"=>array("type"=>"text","opt"=>"NOT NULL"),
		"details"=>array("type"=>"longtext","opt"=>"NOT NULL"),
		"location"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"place_name"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"streetAddress"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"addressLocality"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"addressRegion"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"postalCode"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default ''"),
		"hidden_info"=>array("type"=>"text","opt"=>"NOT NULL"),
		"start_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"end_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"continuous"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"deadline"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"repeat_until"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"posted_by"=>array("type"=>"bigint (20)","opt"=>"NOT NULL default 0"),
		"event_type"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default 'once'"),
		"event_schematype"=>array("type"=>"tinyint(3)","opt"=>"unsigned NOT NULL default 0"),
		"repeat_every"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default ''"),
		"repeat_freq"=>array("type"=>"tinyint(3)","opt"=>"unsigned NOT NULL default 1"),
		"use_as_template"=>array("type"=>"tinyint(3)","opt"=>"unsigned NOT NULL default 0"),
		"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"modified_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"limit_reg"=>array("type"=>"tinyint(5)","opt"=>"unsigned NOT NULL default 0"),
		"publish_status"=>array("type"=>"varchar (15)","opt"=>"NOT NULL default 'published'"),
		"allow_comments"=>array("type"=>"varchar (10)","opt"=>"NOT NULL default 'true'"),
		"hide_in_rss"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"image_url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"image_thumb"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"mediafile_url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"comment_count"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"author"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"slideshow"=>array("type"=>"text","opt"=>"NOT NULL"),
		"slideshow_type"=>array("type"=>"bigint (2)","opt"=>"unsigned NOT NULL default 0")
		);
	$d_calendar_posts_indexes=", UNIQUE (event_id)";

	$d_calendar_registered_fields_array=array("id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment"),
		"event_id"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default '0'"),
		"user"=>array("type"=>"bigint (20)","opt"=>"NOT NULL default 0"),
		"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"notes"=>array("type"=>"text","opt"=>"NOT NULL"));

	$d_calendar_cat_fields_array=array("id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment"),
		"cid"=>array("type"=>"int(10)","opt"=>"NOT NULL"),
		"cname"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"ccolor"=>array("type"=>"varchar(10)","opt"=>"NOT NULL default ''"),
		"cmark"=>array("type"=>"varchar(10)","opt"=>"NOT NULL default ''"),
		"image1"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"image"),
		"cmark_color"=>array("type"=>"varchar(10)","opt"=>"NOT NULL default ''"),
		"visible"=>array("type"=>"varchar(10)","opt"=>"NOT NULL default 'yes'"),
		"restricted"=>array("type"=>"tinyint(3)","opt"=>"unsigned NOT NULL default 0"),
		"parentid"=>array("type"=>"int(10)","opt"=>"NOT NULL default -1"),
		"description"=>array("type"=>"text","opt"=>"","itype"=>"area"),
		"page_title"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"category_type"=>array("type"=>"tinyint(3)","opt"=>"NOT NULL default '0'"),
		);

	$d_calendar_comments_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"comment_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"parent_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL DEFAULT 0"),
		"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"uid"=>array("type"=>"bigint (20)","opt"=>"default NULL"),
		"visitor"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"email"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"comments"=>array("type"=>"text","opt"=>"NOT NULL"),
		"ip"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
		"host"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"agent"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"approved"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"));


	$d_calendar_settings_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"skey"=>array("type"=>"varchar(255)","opt"=>"NOT NULL"), "sval"=>array("type"=>"text","opt"=>"NOT NULL"));

//creating db
	$r1 = $db->update_table($d_calendar_posts_fields_array,$page_id.'_events',array(),$d_calendar_posts_indexes);
	$r2 = $db->create_table($d_calendar_registered_fields_array,$page_id.'_registration');
	$r3 = $db->update_table($d_calendar_cat_fields_array,$page_id.'_categories',array('cid'=>"UNIQUE (cid)"),', UNIQUE (cid)');
	$r4 = $db->create_table($d_calendar_settings_fields_array,$page_id.'_settings',", UNIQUE (skey)");
	$r5 = $db->update_table($d_calendar_comments_fields_array,$page_id.'_comments');
	if(!($r1&&$r2&&$r3&&$r4&&$r5)) return false;

	if($db->db_count($page_id.'_events')==0) //do conversion
	{
		$cal_db_dir=$rel_path.$db_folder;
		$cal_settings_fname=$cal_db_dir.$prefix.'settings.ezg.php';
		if(file_exists($cal_settings_fname) &&(filesize($cal_settings_fname)>0))
			$set=File::read($cal_settings_fname);
		else
		{
			if($db->db_count($page_id.'_categories')==0)
			{
				$db->query_insert($page_id.'_categories', array('cid'=>0,'cname'=>"Multiple selection",'ccolor'=>"#CDDEEE"));
				$db->query_insert($page_id.'_categories', array('cid'=>1,'cname'=>"General",'ccolor'=>"#808080"));
			}
			return;
		}
// categories
		if(strpos($set,'<cat_')!==false)
		{
			while(strpos($set,'<cat_')!==false)
			{
				$cat_id=Formatter::GFS($set,'<cat_','>'); settype($cat_id,'integer');
				$category_info=Formatter::GFS($set,'<cat_'.$cat_id.'>','</cat_'.$cat_id.'>');
				$info_arr=explode('%%',$category_info);

				$data_to_write=array('cid'=>$cat_id, 'cname'=>Formatter::unEsc($info_arr[0]),
					'ccolor'=>(!empty($info_arr[1]) && strpos($info_arr[1],'#')===false?'#':'').$info_arr[1]);
				if(isset($info_arr[2]))
					$data_to_write['visible']=($info_arr[2]=='0'? 'no': 'yes');
				if(isset($info_arr[3]))
					$data_to_write['cmark']=$info_arr[3];
				if(isset($info_arr[4]))
					$data_to_write['cmark_color']=(!empty($info_arr[4]) && strpos($info_arr[4],'#')===false?'#':'').$info_arr[4];
				$db->query_insert($page_id.'_categories', $data_to_write);
				$set=str_replace('<cat_'.$cat_id.'>'.$category_info.'</cat_'.$cat_id.'>','',$set);
			}
		}

	// settings
		$lang=(strpos($set,'<language>')!==false)? Formatter::GFS($set,'<language>','</language>'):'EN';
		$public_rss=(strpos($set,'<public_rss>')!==false)? Formatter::GFS($set,'<public_rss>','</public_rss>'):'0';
		$free_field=(strpos($set,'<free_field_label>')!==false)? Formatter::GFS($set,'<free_field_label>','</free_field_label>'):'Location';
		$date_format=(strpos($set,'<date_format>')!==false)? Formatter::GFS($set,'<date_format>','</date_format>'):'mmm d, yyyy (ddd)';
		$old_settings=array('language'=>$lang,'public_rss'=>$public_rss,'free_field_label'=>$free_field,'date_format'=>$date_format);
		foreach($mails_list as $k=>$v)
		{
			$em_set_raw=Formatter::GFS($set,'<'.$k.'>','</'.$k.'>');
			if(strpos($em_set_raw,'<subject>')!==false)
				$old_settings[$k.'_subject']=Formatter::sth3(Formatter::GFS($em_set_raw,'<subject>','</subject>'));
			if(strpos($em_set_raw,'<message>')!==false)
				$old_settings[$k.'_message']=Formatter::sth3(Formatter::GFS($em_set_raw,'<message>','</message>'));
		}
		insert_settings($page_id.'_settings',$old_settings);

	// posts
		$cal_db_dir=$rel_path.$db_folder;
		$db_events_fname=$cal_db_dir.$prefix."cal_events.ezg.php";
		$db_recur_fname=$cal_db_dir.$prefix."cal_recurring.ezg.php";
		$db_reg_fname=$cal_db_dir.$prefix.'cal_registered.ezg.php';

		$all_events=cal_db_get_all($db_events_fname);
		$all_recur=cal_db_get_all($db_recur_fname);
		$users_data=User::getAllUsers(true);

		if(!empty($all_events))
		{
			foreach($all_events as $k=>$v)
			{
				$event_rec=array();
				$event_rec['event_id']=$v['Id'];
				$event_rec['category']=(int) $v['Category'];

				$desc=sth_4($v['Short_description']);
				if(strpos($desc,'%%TEMPLATE1%%')!==false)
				{
					$event_rec['use_as_template']=1;
					$desc=str_replace('%%TEMPLATE1%%','',$desc);
				}
				$event_rec['short_description']=$desc;

				$event_rec['details']=sth_4($v['Details']);
				$event_rec['start_date']=Date::buildMysqlTime($v['Start_date']);
				$event_rec['end_date']=Date::buildMysqlTime($v['End_date']);
				$event_rec['location']=sth_4($v['Location']);
				$event_rec['hidden_info']=(isset($v['HIDDEN']))?sth_4($v['HIDDEN']):'';
				if(isset($v['DEADLINE']) && !empty($v['DEADLINE']))
					$event_rec['deadline']=Date::buildMysqlTime($v['DEADLINE']);

				if(isset($all_recur[$v['Id']]))
				{
					$event_rec['event_type']='repeating';
					$repeat=$all_recur[$v['Id']]['repeatPeriod'];
					$event_rec['repeat_every']=($repeat=='0'? 'year': ($repeat=='1'? 'month': 'week'));
				}

				$posted_by_user=(isset($v['USER']))? Formatter::sth3(urldecode($v['USER'])):'admin';
				if(isset($users_data[$posted_by_user]['uid']))
					$event_rec['posted_by']=$users_data[$posted_by_user]['uid'];
				elseif(isset($users_data[$posted_by_user]['id']))
					$event_rec['posted_by']=$users_data[$posted_by_user]['id'];
				else
					$event_rec['posted_by']=-1;

				$db->query_insert($page_id.'_events',$event_rec);
			}
		}
	// EM registration
		$all_registered=cal_db_get_all($db_reg_fname,false);
		if(!empty($all_registered))
		{
			foreach($all_registered as $k=>$v)
			{
				$reg_rec=array();
				$reg_rec['event_id']=$v['Event_id'];
				$reg_rec['date']=Date::buildMysqlTime($v['Timestamp']);
				$reg_rec['notes']=sth_4($v['Notes']);

				$user=Formatter::sth3(urldecode($v['User']));
				if(isset($users_data[$user]['uid']))
					$reg_rec['user']=$users_data[$user]['uid'];
				elseif(isset($users_data[$user]['id']))
					$reg_rec['user']=$users_data[$user]['id'];
				else
					$reg_rec['user']=$r1 = -1;

				$db->query_insert($page_id.'_registration',$reg_rec);
			}
		}
	}

	return true;
}

function data_build_assoc_array($data)
{
	$output=array();
	foreach($data as $k=>$v)
		if ($k%2==0 && isset($data[$k+1]))
			 $output[$v]=$data[$k+1];

	return $output;
}

function dbReadSubscribers($fname)
{
	$result=array();
	if($handle=@fopen($fname,'r'))
	{
		fgetcsv($handle,2048);
		while($data=fgetcsv($handle,25000))
			if($data[0]!="*/ ?>")
				 $result[]=data_build_assoc_array($data);

		fclose($handle);
	}
	return $result;
}

function dbReadSubscriber($fname,$user_id,$search_by='id')
{
	$result=array();
	if(!$handle=fopen($fname,'r'))
		echo 'failed to open subscribers';
	fgetcsv($handle,2048);
	while($data=fgetcsv($handle,25000))
	{
		$ass_data=data_build_assoc_array($data);
		if($data[0]!="*/ ?>" && !empty($ass_data))
		{
			if($search_by=='id' && $ass_data['Id']==$user_id)
			{
				$result=$ass_data;
				break;
			}
			elseif($search_by=='email' && $ass_data['EmailAddress']==$user_id)
			{
				$result=$ass_data;
				break;
			}
		}
	}
	fclose($handle);
	return $result;
}

function getLog($fname,$pg_id,$rel_path)
{
	$db_dir=$rel_path.'ezg_data/';
	$unsub=$db_dir.$pg_id."_unsubscribed.ezg.php";
	$unconf=$db_dir.$pg_id."_unconfirmed_sub.ezg.php";
	$conf=$db_dir.$pg_id."_confirmed_sub.ezg.php";
	$sub_log=$db_dir.$pg_id."_log.ezg.php";

	$logcontent=array();
	if(file_exists($fname))
	{
		$handle=fopen($fname,'r');
		while($data=fgetcsv($handle, 8192, '|'))
		{
			if($data[0]!="<?php echo 'hi'; exit; /*" && (!empty($data[0])) && $data[0]!='*/ ?>')
			{
				$raw_data=explode('==>',$data[0]);
				$td=$raw_data[0]; $temp=urldecode($raw_data[1]);
				if(isset($raw_data[2]))
					$result=urldecode($raw_data[2]);
				else
				{
					$data=fgetcsv($handle, 8192,'|');
					$result=urldecode($data[0]);
				}
				$activity=substr($temp, 0, strpos($temp,':'));
				if(strpos($activity,'import')===false && strpos($activity,'Import')===false && $fname==$sub_log)
				{
					if(strpos($temp,'EmailAddress')!==false)
						$object=Formatter::GFS($temp,'EmailAddress","','"');
					elseif(strpos($temp,'@')!==false && strpos($temp,'"')===false)
						$object=substr($temp, strpos($temp,':')+1);
					elseif(strpos($temp,'@')!==false && strpos($temp,'"')!==false)
					{
						$part1=substr($temp, 0, strpos($temp,'@')); $part1=substr($part1, strrpos($part1,'"')+1);
						$part2=substr($temp, strpos($temp,'@')); $part2=substr($part2, 0, strpos($part2,'"'));
						$object=$part1.$part2;
					}
					else
					{
						$object=substr($temp, strpos($temp,':')+1); $object=trim($object);
						if(Validator::validateEmail($object) && !empty($object))
						{
							$record=dbReadSubscriber($conf,$object);
							if(!empty($record))
								$object=urldecode($record['EmailAddress']);
							else
							{
								$record=dbReadSubscriber($unsub,$object);
								if(!empty($record))
									$object=urldecode($record['EmailAddress']);
								else
								{
									$record=dbReadSubscriber($unconf,$object);
									if(!empty($record))
										$object=urldecode($record['EmailAddress']);
									else
										$object='NA';
								}
							}
						}
					}
				}
				else
					$object=substr($temp, strpos($temp,':')+1);

				$result=substr($result, strpos($result,'Result:'));
				$res_arr=explode('>>',$result);
				if(count($res_arr)==1)
					$result=$res_arr[0];
				elseif(strpos($res_arr[0], 'SUCCESS')!==false)
					$result='SUCCESS '.array_pop($res_arr);
				elseif(strpos($res_arr[0], 'FAIL')!==false)
					$result='FAILED '.array_pop($res_arr);
				else
					$result=array_pop($res_arr);
				$logcontent[]=array('date'=>trim($td),'activity'=>trim($activity),'subscriber'=>$object, 'result'=>str_replace('Result:','',$result));
			}
		}
		fclose($handle);
	}
	return $logcontent;
}

//--CA USERS SUBSCRIBERS TABLE--//
function create_users_subscribersdb($db)
{
	$d_users_subscriptions_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"user_id"=>array("type"=>"varchar(32)","opt"=>"NOT NULL default ''"),
		"page_id"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"sub_id"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"));
	$r1 = $db->create_table($d_users_subscriptions_fields_array,'ca_users_subscriber');
	return !$r1?false:true;
}

function create_newsletterdb($db,$m_pre,$page_id,$prefix,$rel_path,$db_folder,$email_field_name,$ezg_field_labels,$db_system_fields)
{
	$d_newsletter_subs_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"sub_id"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default '0'"),
		"ss_subscribe_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"ss_confirmed"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"ss_confirm_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"email"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"));

	$d_newsletter_groups_fields_array=array("id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment"),
		"group_id"=>array("type"=>"int(10)","opt"=>"NOT NULL"),
		"group_name"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"));

	$d_newsletter_groups_subs_fields_array=array("id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment"),
		"group_id"=>array("type"=>"int(10)","opt"=>"NOT NULL"),
		"sub_id"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default '0'"));

	$d_newsletter_settings_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"skey"=>array("type"=>"varchar(255)","opt"=>"NOT NULL"), "sval"=>array("type"=>"text","opt"=>"NOT NULL"));

	$d_newsletter_log_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"activity"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"affected_data"=>array("type"=>"text","opt"=>"NOT NULL"),
		"result"=>array("type"=>"text","opt"=>"NOT NULL"),
		"newsletter_id"=>array("type"=>"int(10)","opt"=>"NOT NULL"),
		"type"=>array("type"=>"varchar(10)","opt"=>"NOT NULL default ''"),
		"user_id"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''")
		);

	$d_newsletters_db_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"newsletter"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"data"=>array("type"=>"text","opt"=>"NOT NULL"),
		"subject"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"status"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
		"user_id"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
		"parent_page"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"userid"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
		"mdate"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"page_id"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''")
		);

		foreach($ezg_field_labels as $k=>$v)
		{
			$field_name=str_replace(' ','_',$k);
			if($k=='Notes')
				$d_newsletter_subs_fields_array[$field_name]=array("type"=>"text","opt"=>"NOT NULL");
			elseif($k==$email_field_name || $k=='Email')
				continue;
			else
				$d_newsletter_subs_fields_array[$field_name]=array("type"=>"varchar (255)","opt"=>"NOT NULL default ''");
		}

		$d_newsletter_subs_fields_array["ss_uploaded_files"]=array("type"=>"text","opt"=>"NOT NULL");
		$d_newsletter_subs_fields_array["ss_uploaded_filetypes"]=array("type"=>"text","opt"=>"NOT NULL");
	//creating db
		$r1 = $db->create_table($d_newsletter_subs_fields_array,$prefix.'subscribers','');
		$r2 = $db->create_table($d_newsletter_groups_fields_array,$prefix.'groups');
		$r3 = $db->create_table($d_newsletter_groups_subs_fields_array,$prefix.'groups_subs');
		$r4 = $db->create_table($d_newsletter_settings_fields_array,$prefix.'settings',", UNIQUE (skey)");
		$r5 = $db->update_table($d_newsletter_log_fields_array,$prefix.'log');
		$r6 = $db->create_table($d_newsletters_db_array,'newsletters');
		if(!($r1 && $r2 && $r3 && $r4 && $r5 && $r6))
			return false;

		if($db->db_count($prefix.'subscribers')==0) //do conversion
		{
			$db_dir=$rel_path.$db_folder;
			$unconf=$db_dir.$page_id."_unconfirmed_sub.ezg.php"; $conf=$db_dir.$page_id."_confirmed_sub.ezg.php";
			$sub_log=$db_dir.$page_id."_log.ezg.php"; $news_log=$db_dir.$page_id."_news_log.ezg.php";
			$sub_settings_fname=$db_dir.$page_id.'_settings.ezg.php';
		//settings
			if(file_exists($sub_settings_fname) &&(filesize($sub_settings_fname)>0))
			{
				$set=File::read($sub_settings_fname);

				$lang=(strpos($set,'<language>')!==false)? Formatter::GFS($set,'<language>','</language>'):'EZG';
				$notviewed=(strpos($set,'<notviewed>')!==false)? Formatter::GFS($set,'<notviewed>','</notviewed>'):'';
				$news_moved_flag=(strpos($set,'<news_moved_flag>')!==false)? Formatter::GFS($set,'<news_moved_flag>','</news_moved_flag>'):'0';
				$old_settings=array('language'=>$lang,'notviewed'=>$notviewed,'news_moved_flag'=>$news_moved_flag);
				insert_settings($page_id.'_settings',$old_settings);
			}
		//posts
			if(file_exists($unconf) &&(filesize($unconf)>0))
				$all_unconf=dbReadSubscribers($unconf);
			if(file_exists($conf) &&(filesize($conf)>0))
				$all_conf_subs=dbReadSubscribers($conf);
			else
				return;
			if(file_exists($all_unconf) &&(filesize($all_unconf)>0))
				$all_subs_conf_unconf=array($all_unconf, $all_conf_subs);

			$db_field_names=$db->db_fieldnames($prefix.'subscribers');

			foreach($all_subs_conf_unconf as $key=>$all_subs)
			{
				foreach($all_subs as $k=>$sub)
				{
					$extra_fields=array();
					$sub_rec=array();
					$sub_rec['sub_id']=$sub['Id'];
					$sub_rec['ss_subscribe_date']=Date::buildMysqlTime(time());
					$sub_rec['ss_confirm_date']=Date::buildMysqlTime(time());
					$sub_rec['ss_confirmed']=($key==0)?0:1;
					$sub_rec['ss_uploaded_files']=(isset($sub['UploadedFiles']))?urldecode($sub['UploadedFiles']):'';
					$sub_rec['ss_uploaded_filetypes']=(isset($sub['UploadedFiletypes']))?urldecode($sub['UploadedFiletypes']):'';
					$sub_rec['email']=Formatter::sth3(urldecode($sub['EmailAddress']));

					foreach($sub as $field=>$val)
					{
						if(in_array($field, $db_field_names))
						{
							if($field=='EmailAddress')
								$sub_rec['email']=Formatter::sth3(urldecode($val));
							elseif(!in_array($field, $db_system_fields))
								$sub_rec[$field]=Formatter::sth3(urldecode($val));
							elseif(!in_array($field, array('Id','UploadedFiles','UploadedFiletypes')))
							{
								$s_field='d_'.$field;
								$db->query('ALTER TABLE '.$m_pre."subscribers ADD `".$s_field."` varchar(255) NOT NULL default ''");
								$sub_rec[$s_field]=Formatter::sth3(urldecode($val));
							}
						}
						else
							$extra_fields[]=$field;
					}
					if(!empty($extra_fields))
					{
						foreach($extra_fields as $extra_f)
						{
							if(!in_array($extra_f,array('Subscribe_x','Subscribe_y','Unsubscribe_x','Unsubscribe_y','Edit','Id','EmailAddress')) && !in_array($field, $db_system_fields))
							{
								$extra_f_f=str_replace(' ','_',$extra_f);
								$db->query('ALTER TABLE '.$m_pre."subscribers ADD `".$extra_f_f."` varchar(255) NOT NULL default ''");
								$sub_rec[$extra_f_f]=$sub[$extra_f];
							}
						}
					}
					$db->query_insert($prefix.'subscribers',$sub_rec);
				}
			}

		//groups
			if(strpos($set,'<group_')!==false)
			{
				while(strpos($set,'<group_')!==false)
				{
					$group_id=Formatter::GFS($set,'<group_','>'); settype($group_id,'integer');
					$group_name=Formatter::GFS($set,'<group_'.$group_id.'>','</group_'.$group_id.'>');
					$db->query_insert($prefix.'groups', array('group_id'=>$group_id, 'group_name'=>Formatter::unEsc($group_name)));
					$set=str_replace('<group_'.$group_id.'>'.$group_name.'</group_'.$group_id.'>','',$set);
				}
			}
			if(strpos($set,'<sub_in_group_')!==false)
			{
				while(strpos($set,'<sub_in_group_')!==false)
				{
					$group_id=Formatter::GFS($set,'<sub_in_group_','>'); settype($group_id,'integer');
					$sub_in_group=Formatter::GFS($set,'<sub_in_group_'.$group_id.'>','</sub_in_group_'.$group_id.'>');
					$sub_in_group_arr=explode('|',$sub_in_group);

					foreach($sub_in_group_arr as $key=>$val)
					{
						if(!empty($val))
						{
							$sub_id=$db->query_singlevalue('SELECT sub_id FROM '.$m_pre.'subscribers WHERE email="'.urldecode($val).'"');
							$db->query_insert($prefix.'groups_subs', array('group_id'=>$group_id, 'sub_id'=>$sub_id));
						}
					}
					$set=str_replace('<sub_in_group_'.$group_id.'>'.$sub_in_group.'</sub_in_group_'.$group_id.'>','',$set);
				}
			}

		//log
			$news_log_content=getLog($news_log,$page_id,$rel_path);
			$sub_log_content=getLog($sub_log,$page_id,$rel_path);
			$mon_abbr=array("Jan"=>'01',"Feb"=>'02',"Mar"=>'03',"Apr"=>'04',"May"=>'05',"Jun"=>'06',"Jul"=>'07',"Aug"=>'08',"Sep"=>'09',"Oct"=>'10', "Nov"=>'11',"Dec"=>'12');
			foreach($news_log_content as $k=>$v)
			{
				if(!empty($v))
				{
					$date_arr=explode(' ',$v['date']);
					$mysql_date=$date_arr[2].'-'.(isset($mon_abbr[$date_arr[1]])?$mon_abbr[$date_arr[1]]:'').'-'.$date_arr[0]. '-'.' '.$date_arr[3];
					$db->query_insert($prefix.'log', array('date'=>$mysql_date, 'activity'=>$v['activity'], 'affected_data'=>$v['subscriber'], 'result'=>$v['result'],'type'=>'news'));
				}
			}
			foreach($sub_log_content as $k=>$v)
			{
				if(!empty($v))
				{
					$date_arr=explode(' ',$v['date']);
					$mysql_date=$date_arr[2].'-'.(isset($mon_abbr[$date_arr[1]])?$mon_abbr[$date_arr[1]]:'').'-'.$date_arr[0]. '-'.' '.$date_arr[3];
					$db->query_insert($prefix.'log', array('date'=>$mysql_date, 'activity'=>$v['activity'], 'affected_data'=>$v['subscriber'], 'result'=>$v['result'],'type'=>'sub'));
				}
			}
		}
		return true;
}

function gues_format_records_in_array($records)
{
	$entries_array=array();
	$i=1;
	while(strpos($records,'<entry id="'.$i.'">')!==false)
	{
		$comments_buff=array();
		$main_buffer ['id']=$i;
		$record='<entry id="'.$i.'">'. Formatter::GFS($records,'<entry id="'.$i.'">','</entry>').'</entry>';
		$entry_part=Formatter::GFS($record,'<entry id="'.$i.'">','<comments_data>');
		$comments_part=Formatter::GFS($record,'<comments_data>','</comments_data>');
		$entry_timetsamp=Formatter::GFS($entry_part,"<timestamp>","</timestamp>");
		while(strpos($entry_part,'<')!==false)
		{
			$element_name=Formatter::GFS($entry_part,'<','>');
			$element_value=Formatter::GFS($entry_part,"<$element_name>","</$element_name>");
			$main_buffer[$element_name]=$element_value;
			if(strpos($entry_part,"</$element_name>")!==false)
				$entry_part=str_replace("<$element_name>$element_value</$element_name>",'',$entry_part);
			else
				break;
		}
		$j=1;
		while(strpos($comments_part,'<comment id="'.$j.'">')!==false)
		{
			$buff=array();
			$comment_str=Formatter::GFS($comments_part,'<comment id="'.$j.'">','</comment>');
			while (strpos($comment_str,'<')!==false)
			{
				$element_name=Formatter::GFS($comment_str,'<','>');
				$element_value=Formatter::GFS($comment_str,"<$element_name>","</$element_name>");
				$buff [$element_name]=$element_value;
				if(strpos($comment_str,"</$element_name>")!==false)
					$comment_str=str_replace("<$element_name>$element_value</$element_name>",'',$comment_str);
				else
					break;
			}
			$buff['entry_id']=$entry_timetsamp;
			if(!isset($buff['approved']))
				$buff['approved']='1';
			$comments_buff []=$buff;
			$j++;
		}
		$main_buffer['comments']=$comments_buff;
		if(!isset($main_buffer['approved']))
			$main_buffer['approved']='1';
		$entries_array[]=$main_buffer;
		$i++;
	}
	return $entries_array;
}

function create_guestbookdb($db,$page_id,$rel_path,$db_folder)
{
//GUESTBOOK DATA STRUCTURE
	$d_guestbook_comments_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"comment_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"parent_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL DEFAULT 0"),
		"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
		"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
		"uid"=>array("type"=>"bigint (20)","opt"=>"default NULL"),
		"visitor"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"email"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"comments"=>array("type"=>"text","opt"=>"NOT NULL"),
		"ip"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
		"host"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"agent"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"approved"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"));

	$d_guestbook_cat_fields_array=array("id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment"),
		"cid"=>array("type"=>"int(10)","opt"=>"NOT NULL"),
		"cname"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"ccolor"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
		"image1"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"image"),
		"description"=>array("type"=>"text","opt"=>"","itype"=>"area"),
		"parentid"=>array("type"=>"int(10)","opt"=>"NOT NULL default -1"),
		"viewid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
		"page_title"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''")
		);

	 	$d_guestbook_settings_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
		"skey"=>array("type"=>"varchar(255)","opt"=>"NOT NULL"), "sval"=>array("type"=>"text","opt"=>"NOT NULL"));

//creating db
	$r1 = $db->update_table($d_guestbook_comments_fields_array,$page_id.'_comments');
	$r2 = $db->create_table($d_guestbook_settings_fields_array,$page_id.'_settings',", UNIQUE (skey)");
	$r3 = $db->update_table($d_guestbook_cat_fields_array,$page_id.'_categories',array('cid'=>"UNIQUE (cid)"),', UNIQUE (cid)');
	if(!($r1&&$r2&&$r3))
		return false;

	if($db->db_count($page_id.'_comments')==0) //do conversion
	{
		 $src=$rel_path.$db_folder.$page_id."_db_guestbook.ezg.php";
		 if(file_exists($src) &&(filesize($src)>0))
				$data_raw=File::read($src);
		 else
			  return;

			$db->query_insert($page_id.'_categories', array('cid'=>0, 'cname'=>"General", 'ccolor'=>"#FFC18A"));
//setting
		 $lang=(strpos($data_raw,'<language>')!==false)? Formatter::GFS($data_raw,'<language>','</language>'):'DEF';
		 $public_rss=(strpos($data_raw,'<public_rss>')!==false)? Formatter::GFS($data_raw,'<public_rss>','</public_rss>'):'0';
		 $default_country=(strpos($data_raw,'<default_country>')!==false)? Formatter::GFS($data_raw,'<default_country>','</default_country>'):'Select';
		 insert_settings($page_id.'_settings',array('language'=>$lang,'public_rss'=>$public_rss,'default_country'=>$default_country));
// posts
		 $records_arr=array();
		 $data_records=Formatter::GFS($data_raw,"<entries>","</entries>");
		 if($data_records!='')
			$records_arr=gues_format_records_in_array($data_records);

		 foreach($records_arr as $v)
		 {
			$post_rec=array();
			$post_rec['entry_id']=(int) $v['timestamp'];
			$post_rec['date']=Date::buildMysqlTime($v['timestamp']);
			$post_rec['name']=urldecode(Formatter::unEsc($v['name']));
			$post_rec['surname']=urldecode(Formatter::unEsc($v['surname']));
			$post_rec['email']=Formatter::sth3(urldecode($v['emailaddress']));
			$post_rec['country']=Formatter::sth3($v['country']);
			$post_rec['content']=sth_4($v['content']);
			$post_rec['ip']=Formatter::sth3($v['ip']);
			$post_rec['host']=Formatter::sth3($v['host']);
			$post_rec['agent']=Formatter::sth3($v['agent']);
			$post_rec['approved']=(isset($v['approved']))?$v['approved']:1;

			$com_count=0; $all_comments=array();
			if(isset($v['comments']))
			{
				$all_comments=$v['comments'];
				foreach($v['comments'] as $kk=>$com)
					if(!empty($com) && (!isset($com['approved']) || $com['approved']=='1'))
						$com_count++;
			}
			$post_rec['comment_count']=$com_count;
			$db->query_insert($page_id.'_posts',$post_rec);

			// comments
			if(!empty($all_comments))
			{
				foreach($all_comments as $comment)
				{
					$com_rec=array();
					$com_rec['comment_id']=(int) $comment['timestamp'];
					$com_rec['entry_id']=$post_rec['entry_id'];
					$com_rec['date']=Date::buildMysqlTime($comment['timestamp']);
					foreach(array('visitor','email','url','comments','ip','host','agent','approved') as $kk=>$index)
					{
						if($index=='comments')
							$com_rec[$index]=sth_4($comment[$index]);
						elseif($index=='email')
							$com_rec[$index]=Formatter::sth3(urldecode($comment['emailaddress']));
						elseif($index=='url')
						{
							if(isset($comment[$index]))
								$com_rec[$index]=Formatter::sth3(urldecode($comment[$index]));
						}
						else
							$com_rec[$index]=Formatter::sth3(urldecode($comment[$index]));
					}
					$db->query_insert($page_id.'_comments',$com_rec);
				}
			}
		  }
	}
	return true;
}

function insert_settingx($db,$g_pre,$key,$value)
{
	$db->query("INSERT INTO ".$g_pre."settings (id, skey, sval) VALUES (NULL, '$key', '$value')");
}

function create_lister_db($db,$g_id,$g_fields_array,$g_shop_on,$g_pre,$update)
{
$g_pending_fields_array=array("id"=>array("type"=>"int(10)",
	"opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"orderid"=>array("type"=>"int(10)","opt"=>"","itype"=>""),
	"orderline"=>array("type"=>"text","opt"=>"","itype"=>""),
	"pdate"=>array("type"=>"varchar(30)","opt"=>"","itype"=>""),
	"items"=>array("type"=>"text","opt"=>"","itype"=>""),
	"form_fields"=>array("type"=>"text","opt"=>"","itype"=>""),
	"vat1"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>""),
	"vat2"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>""),
	"coupon"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
	"userid"=>array("type"=>"int(10)","opt"=>"","itype"=>"uid"),
	"invoice_status"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"fraud_status"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"invoicenr"=>array("type"=>"int(20)","opt"=>"","itype"=>"")
	);

$g_orderslines_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"ol_orderid"=>array("type"=>"int(10)","opt"=>"","itype"=>""),
	"ol_pid"=>array("type"=>"int(10)","opt"=>"","itype"=>""),
	"ol_amount"=>array("type"=>"int(10)","opt"=>"","itype"=>""),
	"ol_price"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>"price"),
	"ol_vat"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>"price"),
	"ol_shipping"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>"price"),
	"ol_option1"=>array("type"=>"text","opt"=>"","itype"=>"subname"),
	"ol_option2"=>array("type"=>"text","opt"=>"","itype"=>"subname"),
	"ol_option3"=>array("type"=>"text","opt"=>"","itype"=>"subname"),
 	"ol_userdata"=>array("type"=>"text","opt"=>"","itype"=>""),
 	"ol_coupon"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
 	"ol_serial"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"")
	);

$g_orders_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"orderid"=>array("Order Id","type"=>"int(10)","opt"=>"","itype"=>""),
	"orderline"=>array("type"=>"text","opt"=>"","itype"=>""),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
	"userid"=>array("type"=>"int(10)","opt"=>"","itype"=>"uid"),
	"invoicenr"=>array("type"=>"int(20)","opt"=>"","itype"=>""));

$g_cat_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"cid"=>array("type"=>"int(10)","opt"=>"","itype"=>""),
	"cname"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"ccolor"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"color"),
	"image1"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"image"),
	"description"=>array("type"=>"text","opt"=>"","itype"=>"area"),
	"description2"=>array("type"=>"text","opt"=>"","itype"=>"area"),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
	"parentid"=>array("type"=>"int(10)","opt"=>"NOT NULL default -1"),
	"viewid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL default 0"),
	"page_title"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
	"ct_permalink"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
	"disable_add_items"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0")
	);

$g_settings_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"skey"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"sval"=>array("type"=>"text","opt"=>"","itype"=>""),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"));

$g_paypal_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"ipnline"=>array("type"=>"text","opt"=>"","itype"=>""),
	"orderline"=>array("type"=>"text","opt"=>"","itype"=>""),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
	"orderid"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"payment_status"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"status"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"reason"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"")
	);

$g_taxes_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"country"=>array("type"=>"varchar(10)","opt"=>"","itype"=>""),
	"state"=>array("type"=>"varchar(10)","opt"=>"","itype"=>""),
	"zip"=>array("type"=>"varchar(10)","opt"=>"","itype"=>""),
	"vat1"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>""),
	"vat2"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>""),
	"cummulative"=>array("type"=>"tinyint(1)","opt"=>"DEFAULT 1","itype"=>"bool"),
	"toshipping"=>array("type"=>"tinyint(1)","opt"=>"DEFAULT 1","itype"=>"bool")
	);

$g_coupons_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"),
	"userid"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>"uid"),
	"coupon_id"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"coupon_amount"=>array("type"=>"decimal(15,4)","opt"=>"","itype"=>""),
	"coupon_amount_procent"=>array("type"=>"tinyint(1)","opt"=>"DEFAULT 0","itype"=>"bool"),
	"order_minimum"=>array("type"=>"decimal(15,4)","opt"=>"DEFAULT 0","itype"=>""),
	"order_limit"=>array("type"=>"int(10)","opt"=>"DEFAULT 1","itype"=>""),
	"orders_count"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>""),
	"valid_from"=>array("type"=>"timestamp","opt"=>"","itype"=>"timestamp"),
	"valid_to"=>array("type"=>"timestamp","opt"=>"","itype"=>"timestamp"),
	"product_id"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>""),
	"category_id"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>""),
	"coupon_status"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>"")
	);

$g_serials_fields_array=array(
	"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
	"pid"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>"uid"),
	"uid"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>"uid"),
	"pageid"=>array("type"=>"int(10)","opt"=>"DEFAULT 0","itype"=>"uid"),
	"serial"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
	"issued"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
	"created"=>array("type"=>"timestamp","opt"=>"DEFAULT CURRENT_TIMESTAMP","itype"=>"timestamp"));

$g_lister_comments_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"comment_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"parent_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL DEFAULT 0"),
	"entry_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"uid"=>array("type"=>"bigint (20)","opt"=>"default NULL"),
	"visitor"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"email"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"url"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"comments"=>array("type"=>"text","opt"=>""),
	"ip"=>array("type"=>"varchar (100)","opt"=>"NOT NULL default ''"),
	"host"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"agent"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"rating"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
	"approved"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"));

$g_lister_features_array=array(
	"pr_id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment"),
	"name"=>array("type"=>"varchar(255)","opt"=>"NOT NULL default ''"),
	"description"=>array("type"=>"text","opt"=>""),
	"position"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL DEFAULT 0"),
	"filter"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"));

$g_lister_options_array=array(
	"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment"),
	"pid"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL"),
	"pr_id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL"),
	"value"=>array("type"=>"text","opt"=>"default ''"));

$g_lister_default_options_array=array(
	"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment"),
	"pr_id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL"),
	"order_by"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL"),
	"value"=>array("type"=>"text","opt"=>"default ''"));


$g_lister_category_features_array=array(
	"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment"),
	"cid"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL"),
	"pr_id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL"));

$g_lister_whishlist_array=array(
	"id"=>array("type"=>"bigint(20)","opt"=>"unsigned NOT NULL auto_increment"),
	"uid"=>array("type"=>"bigint(20)","opt"=>"NOT NULL UNIQUE"),
	"wishlist"=>array("type"=>"text","opt"=>"default ''"));

$g_lister_bundle_array=array(
	"id"=>array("type"=>"bigint(20)","opt"=>"unsigned NOT NULL auto_increment"),
	"bundle_name"=>array("type"=>"varchar(255)","opt"=>"NOT NULL UNIQUE"),
	"primary_item"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL"),
	"bundlelist"=>array("type"=>"text","opt"=>"NOT NULL default ''"),
	"publish_status"=>array("type"=>"varchar(15)","opt"=>"NOT NULL default 'published'"),
	"creation_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"modified_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"));

$g_lister_bundle_orderlines_array=array(
	"id"=>array("type"=>"bigint(20)","opt"=>"unsigned NOT NULL auto_increment"),
	"ol_orderid"=>array("type"=>"int(10)","opt"=>""),
	"ol_pid"=>array("type"=>"int(10)","opt"=>""),
	"ol_bundle_id"=>array("type"=>"int(10)","opt"=>""),
	"ol_bundle_primary"=>array("type"=>"int(10)","opt"=>""),
	"ol_bundle_name"=>array("type"=>"text","opt"=>""),
	"ol_bundle_discount"=>array("type"=>"varchar(255)","opt"=>""),
	"ol_bundle_count"=>array("type"=>"int(4)","opt"=>""));

	if($update)
	{
		 $rA=true;
		 if($g_shop_on)
		 {
			$db->update_table($g_pending_fields_array,$g_id.'_pending_orders',array('orderid'=>' INDEX(`orderid`)'),', KEY orderid (orderid)');
			$db->update_table($g_orders_fields_array,$g_id.'_orders',
					  array('PRIMARY'=>'PRIMARY KEY (orderid)','invoicenr'=>'UNIQUE invoicenr (invoicenr)'),
					  ', KEY orderid (orderid)');

			$db->update_table($g_paypal_fields_array,$g_id.'_paypal');
			$db->update_table($g_coupons_fields_array,$g_id.'_coupons');
			$r1 = $db->create_table($g_taxes_fields_array,'tax');
			$r2 = $db->create_table($g_coupons_fields_array,$g_id.'_coupons');
			$r3 = $db->create_table($g_orderslines_fields_array,$g_id.'_orderlines');
			$r4 = $db->create_table($g_serials_fields_array,'serials');
			$rA = $r1 && $r2 && $r3 && $r4;
		}
		$db->update_table($g_cat_fields_array,$g_id.'_categories',', UNIQUE (cid)');
		$db->update_table($g_lister_comments_fields_array,$g_id.'_comments');
		$db->update_table($g_lister_features_array,$g_id.'_features',array(),'','pr_id');
		$rC = $db->create_table($g_lister_options_array,$g_id.'_options');
		$rD = $db->create_table($g_lister_category_features_array,$g_id.'_cat_features');
		$rE = $db->update_table($g_lister_default_options_array,$g_id.'_defaultoptions');
		$rF = $db->update_table($g_lister_whishlist_array,$g_id.'_wishlist');
		$rG = $db->update_table($g_lister_bundle_array,$g_id.'_bundles');
		$rH = $db->update_table($g_lister_bundle_orderlines_array,$g_id.'_bundles_orderlines');
		return $rA && $rC && $rD && $rE && $rF && $rG && $rH;

	}
	else
	{
	//creating db
		$rA = $db->create_table($g_cat_fields_array,$g_id.'_categories',', UNIQUE (cid)');
		if($db->db_count($g_id.'_categories')==0) {
			$sql="INSERT INTO ".$g_pre."categories (id, cid, cname, ccolor) VALUES (NULL, 1, 'General','#330000')";
			$db->query($sql);
		}
		$rB = $db->create_table($g_fields_array,$g_id.'_data');
		if($g_shop_on)
		{
			$r1 = $db->create_table($g_orders_fields_array,$g_id.'_orders',', KEY orderid (orderid)');
			$r2 = $db->create_table($g_pending_fields_array,$g_id.'_pending_orders',', KEY orderid (orderid)');
			$r3 = $db->create_table($g_paypal_fields_array,$g_id.'_paypal');
			$r4 = $db->create_table($g_serials_fields_array,'serials');
			$r5 = $db->create_table($g_orderslines_fields_array,$g_id.'_orderlines');
			$r6 = $db->create_table($g_coupons_fields_array,$g_id.'_coupons');
			$rC = $r1 && $r2 && $r3 && $r4 && $r5 && $r6;
		}
		$rD = $db->create_table($g_lister_comments_fields_array,$g_id.'_comments');
		$rE = $db->create_table($g_lister_features_array,$g_id.'_features','','pr_id');
		$rF = $db->create_table($g_lister_options_array,$g_id.'_options');
		$rG = $db->create_table($g_lister_category_features_array,$g_id.'_cat_features');
		$rH = $db->create_table($g_lister_whishlist_array,$g_id.'_wishlist');
		$rI = $db->create_table($g_lister_bundle_array,$g_id.'_bundles');
		$rJ = $db->create_table($g_lister_bundle_orderlines_array,$g_id.'_bundles_orderlines');

		$rK = $db->create_table($g_settings_fields_array,$g_id.'_settings',", UNIQUE (skey)");
		if(!($rA && $rB && $rC && $rD && $rE && $rF && $rG && $rH && $rI && $rJ && $rK)) return false;
	//setting defaults
		if($g_shop_on)
		{
			insert_settingx($db,$g_pre,'id','1');
			insert_settingx($db,$g_pre,'bwsubject','Bank Wire Order Confirmation');
			insert_settingx($db,$g_pre,'bwmess','this is confirmation of order: %SHOP_ORDER_ID% made on %SHOP_ORDER_DATE% in our shop. Copy of order below.\r\n<SHOP_BODY>\r\n%SHOP_ORDER_ITEM_CATEGORY%\r\n%SHOP_ORDER_ITEM_NAME% %SHOP_ORDER_ITEM_SUBNAME% %SHOP_ORDER_ITEM_AMOUNT% %SHOP_ORDER_ITEM_COUNT%\r\n</SHOP_BODY>\r\nSubtotal : %SHOP_SUB_TOTAL%\r\nShipping : %SHOP_SHIPPING%\r\n------------------------------------------------\r\nTotal Order Amount : %SHOP_TOTAL%\r\n');
			insert_settingx($db,$g_pre,'bw_notif_message','thanks for your order\r\nyour order number is: %SHOP_ORDER_ID%\r\n\r\n<SHOP_BODY>\r\n%SHOP_ORDER_ITEM_CATEGORY%\r\n%SHOP_ORDER_ITEM_NAME% %SHOP_ORDER_ITEM_SUBNAME% %SHOP_ORDER_ITEM_AMOUNT% %SHOP_ORDER_ITEM_COUNT%\r\n</SHOP_BODY>\r\nSubtotal : %SHOP_SUB_TOTAL%\r\nShipping : %SHOP_SHIPPING%\r\n------------------------------------------------\r\nTotal Order Amount : %SHOP_TOTAL%\r\n');
			insert_settingx($db,$g_pre,'bw_notif_subject','Order Notification');
			insert_settingx($db,$g_pre,'pay_conf_subject','Order Notification');
			insert_settingx($db,$g_pre,'pay_conf_message','thanks for your order\r\nyour order number is: %SHOP_ORDER_ID%\r\n\r\n<SHOP_BODY>\r\n%SHOP_ORDER_ITEM_CATEGORY%\r\n%SHOP_ORDER_ITEM_COUNT% x %SHOP_ORDER_ITEM_NAME% %SHOP_ORDER_ITEM_SUBNAME% %SHOP_ORDER_ITEM_AMOUNT% %SHOP_CARTCURRENCY%  %SHOP_ITEM_DOWNLOAD_LINK(download)%\r\n</SHOP_BODY>\r\nSubtotal : %SHOP_SUB_TOTAL%\r\nShipping : %SHOP_SHIPPING%\r\n------------------------------------------------\r\nTotal Order Amount : %SHOP_TOTAL%\r\n');
			insert_settingx($db,$g_pre,'down_path','');
			insert_settingx($db,$g_pre,'lang','DEF');
		}
	}

	return true;
}

function create_oepdb($db,$p_id,$rel_path)
{
	$d_oep_fields_array=array(
		"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"pageid"=>array("type"=>"int(10)","opt"=>"","itype"=>""),
		"areatag"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
		"username"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
		"htmldata"=>array("type"=>"longtext","opt"=>"","itype"=>""));
	$r = $db->create_table($d_oep_fields_array,'oep');
	if(!$r)
		return $r;
	import_oep($db,$p_id,$rel_path);
	return true;
}

function create_oepimadb($db)
{
	$d_oepima_fields_array=array(
		"id"=>array("type"=>"int(10)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"pageid"=>array("type"=>"int(10)","opt"=>"","itype"=>""),
		"areatag"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
		"path"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""),
		"title"=>array("type"=>"text","opt"=>"","itype"=>""),
		"url"=>array("type"=>"text","opt"=>"","itype"=>""),
		"htmldata"=>array("type"=>"longtext","opt"=>"","itype"=>""));
	return $db->create_table($d_oepima_fields_array,'oep_ima');
}

function import_oep($db,$p_id,$rel_path)
{
	global $user;
	$exist_rec=$db->query_first('SELECT * FROM '.$db->pre.'oep WHERE pageid='.$p_id);
	if(empty($exist_rec))
	{
		$db_fname=$rel_path.'ezg_data/'.$p_id.'.ezg.php';

		if(file_exists($db_fname) &&(filesize($db_fname)>0))
		{
			$users_list=User::mGetUsersPG($p_id,$rel_path);asort($users_list);
			$db_content=File::read($db_fname);
			while(strpos($db_content,'<ea_main')!==false)
			{
				$user=Formatter::GFS($db_content,'<ea_main','>');
				$htmldata=Formatter::GFS($db_content,'<ea_main'.$user.'>','</ea_main'.$user.'>');

				$db_content=str_replace(Formatter::GFSAbi($db_content,'<ea_main'.$user.'>','</ea_main'.$user.'>'),'',$db_content);

				$exist_rec=$db->query_first('SELECT * FROM '.$db->pre.'oep WHERE pageid='.$p_id.' AND areatag="main" AND username="'.$user.'"');
				if(!empty($exist_rec))
					 continue;
				else
					 $db->query_insert('oep',array('pageid'=>$p_id,'areatag'=>'main','username'=>$user,'htmldata'=>Formatter::unEsc($htmldata)));
			}
			while(strpos($db_content,'<ea_')!==false)
			{
				$area_id=Formatter::GFS($db_content,'<ea_','>'); $user='';
				foreach($users_list as $v)
				{
					if(strpos($area_id,$v)!==false)
					{
						$user=$v;
						$area_id=str_replace($v,'',$area_id);
						break;
					}
				}
				$htmldata=Formatter::GFS($db_content,'<ea_'.$area_id.$user.'>','</ea_'.$area_id.$user.'>');
				$db_content=str_replace(Formatter::GFSAbi($db_content,'<ea_'.$area_id.$user.'>','</ea_'.$area_id.$user.'>'),'',$db_content);
				$exist_rec=$db->query_first('SELECT * FROM '.$db->pre.'oep WHERE pageid='.$p_id.' AND areatag="'.$area_id.'" AND username="'.$user.'"');
				if(!empty($exist_rec))
					continue;
				else
					$db->query_insert('oep',array('pageid'=>$p_id,'areatag'=>$area_id,'username'=>$user,'htmldata'=>Formatter::unEsc($htmldata)));
			}
		}
	}
}

// SURVEY TABLE
function create_surveydb($db,$check_only=0,$get_tot_columns=0)
{
	$g_survey_fields_array=array("sid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"s_name"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"s_description"=>array("type"=>"text","opt"=>"NOT NULL"),
	"s_launch_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"s_closed_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"s_status"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"),
	"s_latest_response"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"s_visits"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"s_ezg_pid"=>array("type"=>"mediumint (5)", "opt"=>"NOT NULL"),
	"s_allow_stats"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
	"s_stats_on_final"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
	"s_guest_answers_stored"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"));

	$g_pages_fields_array=array("pid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"sid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"p_title"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"p_description"=>array("type"=>"text","opt"=>"NOT NULL"),
	"p_number" => array("type"=>"smallint(3)","opt"=>"unsigned NOT NULL default 1"),
	"p_status"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 1"));

	$g_guestions_fields_array=array("qid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"pid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"q_type"=>array("type"=>"tinyint(2)","opt"=>"unsigned NOT NULL"),
	"q_title"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
	"q_required"=>array("type"=>"boolean","opt"=>"NOT NULL default false"),
	"q_randomize"=>array("type"=>"boolean","opt"=>"NOT NULL default false"),
	"q_help"=>array("type"=>"text","opt"=>"NOT NULL"),
	"q_other"=>array("type"=>"boolean","opt"=>"NOT NULL default false"),
	"q_number" => array("type"=>"smallint (3)","opt"=>"unsigned NOT NULL default 1"));

	$g_answers_fields_array=array("aid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"qid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"a_label"=>array("type"=>"text","opt"=>"NOT NULL"),
	"a_value"=>array("type"=>"mediumint (4)","opt"=>"NOT NULL default 0"),
	"a_type"=>array("type"=>"varchar(255)","opt"=>"default NULL"),
	"a_extra"=>array("type"=>"text","opt"=>"default NULL"),
	"lid"=>array("type"=>"bigint (20)","opt"=>"unsigned default NULL"));

	$g_labels_fields_array=array("lid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"qid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"l_name"=>array("type"=>"text","opt"=>"NOT NULL"),
	"l_value"=>array("type"=>"mediumint (4)","opt"=>"NOT NULL default 0"));

	$g_responses_fields_array=array("rid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"aid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"resp_id"=>array("type"=>"bigint(20)","opt"=>"unsigned NOT NULL"),
	"value"=>array("type"=>"text","opt"=>"DEFAULT NULL"),  //if answer has value insert the value too (user's input)
	"lid"=>array("type"=>"bigint (20)","opt"=>"unsigned DEFAULT NULL"),
	"r_date"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"));

	$g_responders_fields_array=array("resp_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"uid"=>array("type"=>"bigint (20)","opt"=>"unsigned DEFAULT NULL"),
	"uhash"=>array("type"=>"varchar(255)","opt"=>"default ''"),
	"uname"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"), //added because of the external functionality
	"stats_info"=>array("type"=>"text","opt"=>"NOT NULL"),
	"date_created"=>array("type"=>"datetime","opt"=>"NOT NULL default '0000-00-00 00:00:00'"),
	"is_active"=>array("type"=>"boolean","opt"=>"NOT NULL default false"),
	"ip"=>array("type"=>"varchar(255)","opt"=>"default ''"));

	$g_conditions_fields_array=array("cid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"pid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"tar_page_id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"));

	$g_conditions_links_fields_array=array("id"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL auto_increment"),
	"cid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"),
	"aid"=>array("type"=>"bigint (20)","opt"=>"unsigned NOT NULL"));


	$survey_tables=array(
		'survey_main'=>array('fields'=>$g_survey_fields_array,'key'=>'sid'),
		'survey_pages'=>array('fields'=>$g_pages_fields_array,'key'=>'pid'),
		'survey_questions'=>array('fields'=>$g_guestions_fields_array,'key'=>'qid'),
		'survey_questions_answers'=>array('fields'=>$g_answers_fields_array,'key'=>'aid'),
		'survey_questions_labels'=>array('fields'=>$g_labels_fields_array,'key'=>'lid'),
		'survey_responses'=>array('fields'=>$g_responses_fields_array,'key'=>'rid'),
		'survey_responders'=>array('fields'=>$g_responders_fields_array,'key'=>'resp_id'),
		'survey_conditions'=>array('fields'=>$g_conditions_fields_array,'key'=>'cid'),
		'survey_conditions_links'=>array('fields'=>$g_conditions_links_fields_array,'key'=>'id')
		);
	if($check_only == 1)
	{
		if($get_tot_columns==1)
		{
			$tot=0;
			foreach($survey_tables as $tb)
				$tot +=count($tb['fields']);
			return $tot;
		}
		return $survey_tables; //just returning the tables structure, without creating tables
	}
	foreach($survey_tables as $title => $info)
	{
		$r = $db->create_table($info['fields'],$title,'',$info['key']);
		if(!$r) return $r;
	}
	return $survey_tables;
}

function create_polldb($db)
{
	$d_pollquestions_fields_array=array(
		"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"question"=>array("type"=>"text","opt"=>"","itype"=>""),
		"qid"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"rev"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"created"=>array("type"=>"datetime","opt"=>"","itype"=>""),
		"online"=>array("type"=>"boolean","opt"=>"NOT NULL default false"),
		"template"=>array("type"=>"varchar(11)","opt"=>""),
		"bars"=>array("type"=>"varchar(255)","opt"=>"","itype"=>"")
			  );

	$d_polloptions_fields_array=array(
		"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"qid"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"oid"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"value"=>array("type"=>"varchar(255)","opt"=>"","itype"=>""));

	$d_pollvotes_fields_array=array(
		"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"qid"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"oid"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"voted_on"=>array("type"=>"datetime","opt"=>"","itype"=>""),
		"ip"=>array("type"=>"varchar(16)","opt"=>"","itype"=>"")
		);

	$r1 = $db->update_table($d_pollquestions_fields_array,'poll_questions');
	$r2 = $db->create_table($d_pollvotes_fields_array,'poll_votes');
	$r3 = $db->create_table($d_polloptions_fields_array,'poll_options');
	return $r1&&$r2&&$r3;
}

//SLIDESHOW PLUGIN TABLE////
function create_slideshowdb($db)
{
    $d_slideshow_images_array=array(
		"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"sid"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"title"=>array("type"=>"text","opt"=>"","itype"=>""),
		"href"=>array("type"=>"text","opt"=>"","itype"=>""),
		"url"=>array("type"=>"text","opt"=>"","itype"=>""),
		"caption"=>array("type"=>"text","opt"=>"","itype"=>""),
		"url_target"=>array("type"=>"text","opt"=>"","itype"=>"")
    );
    $d_slideshow_settings_array=array(
		"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"type"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"settings"=>array("type"=>"text","opt"=>"","itype"=>""),
		"date"=>array("type"=>"datetime","opt"=>"","itype"=>""),
		"date_modify"=>array("type"=>"datetime","opt"=>"","itype"=>""),
		"ip"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"pid"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"global_view"=>array("type"=>"tinyint(1)","opt"=>"unsigned NOT NULL default 0"),
		"thumbs_size"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL default 0")
    );
    
    $r1 = $db->update_table($d_slideshow_images_array,'slideshow_images');
    $r2 = $db->update_table($d_slideshow_settings_array,'slideshow_settings');
    return $r1&&$r2;
}
//HTML5 PLAYER PLUGIN TABLE////
function create_html5playerdb($db)
{
    $d_html5player_tracks_array=array(
		"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"player_id"=>array("type"=>"int(11)","opt"=>"","itype"=>""),
		"title"=>array("type"=>"text","opt"=>"","itype"=>""),
		"track_url"=>array("type"=>"text","opt"=>"","itype"=>""),
		"img_url"=>array("type"=>"text","opt"=>"","itype"=>""),
		"caption"=>array("type"=>"text","opt"=>"","itype"=>""),
    );
    $d_html5player_settings_array=array(
		"id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment","itype"=>"dbid"),
		"settings"=>array("type"=>"text","opt"=>"","itype"=>""),
		"date"=>array("type"=>"datetime","opt"=>"","itype"=>""),
		"date_modify"=>array("type"=>"datetime","opt"=>"","itype"=>""),
		"ip"=>array("type"=>"varchar (255)","opt"=>"NOT NULL default ''"),
		"pid"=>array("type"=>"int(11)","opt"=>"","itype"=>"")
    );
    
    $r1 = $db->update_table($d_html5player_tracks_array,'html5player_tracks');
    $r2 = $db->update_table($d_html5player_settings_array,'html5player_settings');
    return $r1&&$r2;
}
// COOKIEBASED LOGIN TABLE
function create_cb_login_db($db,$tbl_name='login_cookiebased')
{
	$l_cb_fields_array=array("id"=>array("type"=>"int(11)","opt"=>"unsigned NOT NULL auto_increment"),
      "hash"=>array("type"=>"varchar(128)","opt"=>"NOT NULL"),
		"exp"=>array("type"=>"datetime","opt"=>"","itype"=>""));
	return $db->create_table($l_cb_fields_array,$tbl_name,'','id');
}

function create_sess_db($db)
{
   $l_sess_fields_array=array("id"=>array("type"=>"varchar(128)","opt"=>"NOT NULL"),
        "data"=>array("type"=>"text","opt"=>"NOT NULL"),
		"timestamp"=>array("type"=>"datetime","opt"=>"NOT NULL"));
	 return $db->create_table($l_sess_fields_array,'sessions','','id','',1);
}
function createUserErrors($db) {
    $user_errors_fields = array(
        'id' => array('type' => 'int (11)', 'opt' => 'NOT NULL auto_increment'),
        'err_level' => array('type' => 'varchar (50)', 'opt' => 'NOT NULL DEFAULT "" '),
        'err_type' => array('type' => 'varchar (50)', 'opt' => 'NOT NULL DEFAULT "" '),
        'err_desc' => array('type' => 'TEXT', 'opt' => 'NOT NULL'),
        'err_line' => array('type' => 'int (11)', 'opt' => 'NOT NULL DEFAULT 0'),
        'err_file' => array('type' => 'varchar (256)', 'opt' => 'NOT NULL DEFAULT "" '),
        'err_date' => array('type' => 'datetime', 'opt' => 'NOT NULL DEFAULT "0000-00-00 00:00:00"')
    );
    return $db->create_table($user_errors_fields, 'user_errors');
}
?>

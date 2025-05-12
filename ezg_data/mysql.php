<?php
/*
   Mysql Database class v 1.3.18
	http://www.ezgenerator.com
	Copyright (c) 2004-2014 Image-line
*/

class Database
{
	private $server;
	private $user;
	private $pass;
	private $port=3306;
	private $mysql_socket='';
	private $debug_active;
	private $debug;
	private $record;
	private $error;
	private $query_result;
	private $charset;
	private $counter;

	public $database;
	public $pre;
	public $errno;
	public $affected_rows;
	public $link_id;
	public $testHash; //used to store the unique id for the SQL test via EZG
	public $f;

	public function __construct($server,$user,$pass,$database,$pre='',$charset='',$f)
	{

		$server_a=explode(':',$server);
		if(isset($server_a[1]) && !is_numeric($server_a[1]))
		{
			$this->server=$server_a[0];
			$this->mysql_socket=$server_a[1];
		}
		elseif(isset($server_a[1]))
		{
			$this->server=$server_a[0];
			$this->port=$server_a[1];
		}
		else
			$this->server=$server_a[0];
		$this->f=$f;
		$this->user=$user;
		$this->pass=$pass;
		$this->database=$database;
		$this->pre=$pre;
		$this->charset=$charset;
		$this->counter=0;
		$this->errno=0;
		$this->query_result=null;
		$this->debug_active=false;
		$this->testHash='%somehash%';
		$this->debug=(isset($_GET['sqldebug']) && $_GET['sqldebug']=='on1' && $this->debug_active);
	}

	public function connect()
	{
		if(!$this->checkExtensions())
			die ('MySQLi dll extension not loaded! Check phpinfo and enable/load mysqli.');

		$this->link_id=@mysqli_connect($this->server,$this->user,$this->pass,'',$this->port,$this->mysql_socket);

		if(!$this->link_id)
		{
			$this->oops("Could not connect to server!</b>.");
			exit;
		}
		$this->showConnectionsCount();
		if(!@mysqli_select_db($this->link_id,$this->database))
		{
			if(! $this->query("CREATE DATABASE ".$this->database))
				$this->oops("Could not create database: <b>$this->database</b>.");
			elseif(!@mysqli_select_db($this->link_id,$this->database))
				exit;
		}
		if(isset($this->f->mysql_timezone) && $this->f->mysql_timezone!='')
			$this->query('SET time_zone="'.$this->f->mysql_timezone.'"');
	}

	private function checkExtensions()
	{
		$extArr=get_loaded_extensions();
		return in_array('mysqli',$extArr);
	}

	public function close()
	{
		if(!mysqli_close($this->link_id))$this->oops("Connection close failed.");
	}

	public function escape($string)
	{
		if(get_magic_quotes_gpc())
			$string=stripslashes($string);
		return mysqli_real_escape_string($this->link_id,$string);
	}

	public function query($sql,$quiet=false)
	{
		if($this->debug)
			var_dump($sql);
		$this->query_result=@mysqli_query($this->link_id,$sql);
		if(!$this->query_result && !$quiet)
			$this->oops("<b>MySQL Query fail:</b> $sql");
		$this->affected_rows=@mysqli_affected_rows($this->link_id);
		$this->counter++;
		return $this->query_result;
	}

	public function db_count($table_name,$where='') //returns false if table do not exists
	{
		$res=$this->fetch_all_array('
			 SELECT COUNT(*)
			 FROM '.$this->pre.$table_name.($where!=''? ' WHERE '.$where:''),true);
		if($res===false)
			return false;
		$count=isset($res[0])?$res[0]['COUNT(*)']:0;
		return $count;
	}

	public function update_table($tablefields_array,$table_name,$alter_array=array(),$indexes='',$primary_key='',$table_charset='')
	{
		$fn=$this->db_fieldnames($table_name,true);

		if($fn===false)
			 return $this->create_table($tablefields_array,$table_name,$indexes,$primary_key,$table_charset);
		else
		{
			 foreach($tablefields_array as $k=>$v)
				  if(array_search($k,$fn)===false)
					 $this->query('ALTER TABLE '.$this->pre.$table_name.' ADD '.$k.' '.$v['type'].' '.$v['opt']);
		}

	//ADD PRIMARY KEY (column_list)
	//ADD UNIQUE index_name (column_list)
	//ADD INDEX index_name (column_list)
	//ADD FULLTEXT index_name (column_list)

		foreach($alter_array as $k=>$v)  //$k is index name
		{
			$res=$this->fetch_all_array('SHOW INDEX FROM '.$this->pre.$table_name.' WHERE Key_name = "'.$k.'"');
			if(count($res)==0)
				 $this->query('ALTER IGNORE TABLE '.$this->pre.$table_name.' ADD '.$v);
		}

		$field_names=$this->db_fieldnames($table_name);
		return count($field_names) >= count($tablefields_array);
}

	public function create_table($fields,$table,$indexes='',$primary_key='',$table_charset='',$innodb=0)
	{
		$fields_list='';
		foreach($fields as $k=>$v) $fields_list.='`'.$k.'` '.$v['type'].' '.$v['opt'].',';
		return
		  $this->query('
				CREATE TABLE IF NOT EXISTS '.$this->pre.$table.' ('.$fields_list.'
				PRIMARY KEY ('.($primary_key==''?'id': $primary_key).') '.$indexes.') '.($table_charset!=''? '
				DEFAULT CHARSET='.$table_charset: ($this->charset!=''? '
				DEFAULT CHARSET='.$this->charset :'')) .( ($innodb==1) ? '
				ENGINE=INNODB;' : '
				ENGINE=MYISAM;'));
	}

	public function query_singlevalue($query_string,$quiet=false)
	{
		$query_res=$this->query($query_string,$quiet);
		if($quiet && $query_res===false)
			return false;
		$out=$this->fetch_array($query_res);
		if(!empty($out))
		{
			$this->free_result($query_res);
			$res=array_values($out);
			return $res[0];
		}
		return null;
	}

	public function get_tables($pre_name='',$suffix='%')
	{
		$pre_name=str_replace('_','\_',$pre_name);
		return $this->fetch_all_array('
					 SHOW TABLES
					 FROM `'.$this->database.'`
					 LIKE "'.$this->pre.$pre_name.$suffix.'"');
	}

	public function check_tables($tables,$pre_name='')
	{
		 $tb_a=$this->get_tables($pre_name);
		 $tb=array();
		 foreach($tb_a as $k=>$v)
			 $tb[]=reset($v);
		 foreach($tables as $k=>$v)
			 if(!in_array($this->pre.$v,$tb))
				return false;
		 return true;
	}

	public function fetch_array($query_res=null)
	{

		if($query_res!==null)
			$this->query_result=$query_res;
		if($this->query_result)
			$this->record=@mysqli_fetch_assoc($this->query_result);
		else
		{
			$this->record=false;
			$this->oops("Invalid query_iresult. Records could not be fetched.");
		}
		if($this->record)
			$this->record=array_map("stripslashes", $this->record);

		return $this->record;
	}

	public function fetch_all_array($sql,$quiet=false,$as_key='')
	{
		$query_res=$this->query($sql,$quiet);
		$out=array();
		if($quiet && $query_res===false)
			return false;
		while($row=$this->fetch_array($query_res))
		{
			if($as_key!='')
				 $out[$row[$as_key]]=$row;
			else
				 $out[]=$row;
		}
		$this->free_result($query_res);
		return $out;
	}

	private function free_result($query_res=null)
	{
  	if($query_res!==null && $query_res!==false)
		{
			$this->query_result=$query_res;
			mysqli_free_result($this->query_result);
		}
	}

	public function db_fieldnames($table,$quiet=false)
	{
		$fields=array();
		$db_struct=$this->fetch_all_array('describe '.$this->pre.$table,$quiet);
		if($quiet && $db_struct===false)
			return false;
		foreach($db_struct as $v)
			$fields[]=$v['Field'];
		return $fields;
	}

	public function db_fields($table)
	{
		$db_struct=$this->fetch_all_array('describe '.$this->pre.$table);
		return $db_struct;
	}

	public function query_first($query_string)
	{
		$query_res=$this->query($query_string);
		$out=$this->fetch_array($query_res);
		$this->free_result($query_res);
		return $out==null?false:$out;
	}


	public function query_update($table,$data,$where='1')
	{
		$q="UPDATE `".$this->pre.$table."` SET ";

		foreach($data as $key=>$val)
		{
			if(strtolower($val)=='null')
				$q.= "`$key`=NULL, ";
			elseif(strtolower($val)=='now()')
				$q.= "`$key`=NOW(), ";
			else
				$q.= "`$key`='".$this->escape($val)."', ";
		}

		$q=rtrim($q, ', ') . ' WHERE '.$where.';';

		return $this->query($q);
	}

	public function query_insert($table,$data,$quiet=false,$ignore=false)
	{
		$q="INSERT ".($ignore?"IGNORE":"")." INTO `".$this->pre.$table."` ";
		$v=''; $n='';

		foreach($data as $key=>$val)
		{
			$n.="`$key`, ";
			if(strtolower($val)=='null')
				$v.="NULL, ";
			elseif(strtolower($val)=='now()')
				$v.="NOW(), ";
			else
				$v.= "'".$this->escape($val)."', ";
		}

		$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .")";

		if($this->query($q,$quiet))
			return mysqli_insert_id($this->link_id);
		else
			return false;
	}

	private function oops($msg='',$ignore=false)
	{
		$msg_div='<div style="background:white;color:black;font: 16px arial,helvetica,sans-serif;text-align:center;">Mysql Error: ';
		if($this->link_id)
		{
			$this->error=mysqli_error($this->link_id);
			$this->errno=mysqli_errno($this->link_id);
		}
		else 			//no link, connection failed
		{
			$this->error=mysqli_connect_error();
			$this->errno=mysqli_connect_errno();
		}
		if($this->debug_active)
		{
			?>
			<table style="width:80%;border:1px solid #000;">
			<tr><th colspan=2>Database Error</th></tr>
			<tr><td style="text-align:right;">Message:</td><td><?php echo $msg; ?></td></tr>
			<?php if(strlen($this->error)>0) echo '<tr><td style="text-align:right;" nowrap>MySQL Error:</td><td>'.$this->error.'</td></tr>'; ?>
			<tr><td style="text-align:right;">Date:</td><td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td></tr>
			<tr><td style="text-align:right;">Script:</td><td><a href="<?php echo @Linker::requestUri(); ?>"><?php echo @Linker::requestUri(); ?></a></td></tr>
			<?php if(isset($_SERVER['HTTP_REFERER'])) echo '<tr><td style="text-align:right;">Referer:</td><td><a href="'.$_SERVER['HTTP_REFERER'].'">'.$_SERVER['HTTP_REFERER'].'</a></td></tr>'; ?>
			</table>
			<?php
		}
		else
		{
			$do_trackback=true;
			if($this->errno==2006 || $this->errno==1007 || $this->errno==1006 || $this->errno==1005 || $this->errno==1004 || $this->errno==1046 ||  $this->errno==1298 || $this->errno==2005)
			{
				echo $msg_div.$this->error.'</div>';
				$do_trackback=false;
			}
			elseif($this->errno==2002 || $this->errno==2003 || $this->errno==1130 || $this->errno==1045 || $this->errno==2013)
			{
				echo $msg_div.' Could not connect to server!.</div>';
				$do_trackback=false;
			}
			elseif($this->errno==1044)
			{
				echo $msg_div.' Access denied!.</div>';
				$do_trackback=false;
			}
			elseif($this->errno==1042 || $this->errno==1227)
			{
				echo $msg_div.' Database user does not have rights to create tables!.</div>';
				$do_trackback=false;
			}
			elseif($this->errno==1030 || $this->errno==126 || $this->errno==145)
				echo $msg_div.$this->errno.'<br>'.$this->error.'</div>';
			//elseif($this->errno==126)
			//	$this->repairTables();

			if($do_trackback&&!$ignore)
			{

				$pageURL='http';
				if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
					$pageURL.="s";
				$pageURL.="://";
				$pageURL.=Linker::getHost().Linker::requestUri();
				$params='<br>Params: '.urlencode(var_export($_REQUEST,true));
				$query_string='title='.urlencode($pageURL)
						  .'&url='.urlencode($pageURL).'&blog_name=ezg&excerpt=<b>MySQL Error: '.$this->errno.'</b><br>'
						  .urlencode($this->error).'<br>'.urlencode($msg)
						  .$params.'<br>pid:'.$this->f->proj_id.
						  '<br>admin:'.$this->f->admin_email;
				$query_md5=md5($msg);

				$cache_file=(is_dir('innovaeditor/')?'':'../').'innovaeditor/assets/errors';

				$fp=@fopen($cache_file,'a+');
				if($fp)
				{
					$fs=filesize($cache_file);
					$data='-';
					if($fs>2048576)
						ftruncate($fp,0);
					else
						$data=fread($fp,$fs);
					if(strpos($data,$query_md5)===false)
					{
						$data=$data."\n".$query_md5;
						fwrite($fp,$data);
						fclose($fp);
						$errno='';$errstr='';
						$trackback_url=parse_url("http://miro.image-line.com/max4_jquery/documents/index_uk.php?action=trackback&entry_id=1345794433&title=trackbacks");
						$http_request='POST '.$trackback_url['path'].(isset($trackback_url['query'])? '?'.$trackback_url['query']:'')." HTTP/1.0\r\n"
							.'Host: '.$trackback_url['host']."\r\n"
							.'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'."\r\n"
							.'Content-Length: '.strlen($query_string)."\r\n"
							."User-Agent: EZGenerator/"
							."\r\n\r\n"
							.$query_string;
						$fs=@fsockopen($trackback_url['host'],80,$errno,$errstr,4);
						@fputs($fs,$http_request);
						@fclose($fs);
					}
					else
						fclose($fp);
				}
			}
		}
	}

	private function showConnectionsCount()
	{
		if($this->debug && isset($_GET['concnt']))
		{
			$rec = $this->query_first('SHOW STATUS WHERE `variable_name` = "Threads_connected"');
			$value = $rec['Value'];
			echo '<script>alert("Threads connected: '.$value.'");</script>';
			var_dump($value);
		}
	}

	public function activateDebug()
	{
		$this->debug_active=true;
	}

	public function repairTables($tables)
	{
		$result='';
		foreach ($tables as $table)
		{
			$check=$this->query_first('CHECK TABLE '.$table);
			if($check['Msg_text']=='OK')
				echo '<p>'.$table.' is OK</p>';
			else
			{
				echo '<p>Table '.$table.'Is broken  Error: '.$check['Msg_text'].'</p>';
				$check=$this->query_first('REPAIR TABLE '.$table);
				if($check['Msg_text']=='OK')
					$result.='<p> Table '.$table.' successfully repaired.';
				else
					$result.='<p>Table repair failed. Error: '.$check['Msg_text'].'</p>'.F_BR;
			}
		}
		return $result;
	}

	public function optimizeTables($tables)
	{
		foreach ($tables as $table)
		{
			$check=$this->query_first('CHECK TABLE '.$table);
   		if($check['Msg_text']=='OK')
   		{
				$check = $this->query_first('ANALYZE TABLE '.$table);
				if($check['Msg_text']=='Table is already up to date')
					echo '<p>The '.$table.' table is already optimized.</p>';
				else
				{
					$check=$this->query_first('OPTIMIZE TABLE '.$table);
					if ($check['Msg_text']=='OK' || $check['Msg_text']=='Table is already up to date')
						echo '<p>Table '.$table.' successfully optimized</p>';
					else
						echo '<p> Failed to optimize the table. Error: '.$check['Msg_text'].'</p>';
				}
			}
			else
				echo $check['Msg_text'];
		}
	}

}

if(isset($_REQUEST['test']))
{
	$db=new Database('%SERVER%','%USER%','%PASSWORD%','%DATABASE%','','',null);
	if(md5($_REQUEST['test'])===$db->testHash)
		$db->activateDebug();
	$db->connect();
	echo '<html><body style="background:black;color:white"><h1>MySQL connection OK!</h1></body></html>';
}

?>

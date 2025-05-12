<?php

$i_version="importer v4 - 1.1.18";
/*
	http://www.ezgenerator.com
	Copyright (c) 2012-2015 Image-line
  @author Atanas
*/

/*
	$settings param example (required elements are marked with *):

	$settings=array(
		* 'script_path'=> null,
		* 'rel_path'=> null,
		* 'user_data'=> null,
		* 'act_up'=> null,
		* 'act_imp'=> null,
		* 'act_cancel'=> null,
		* 'act_imp_redirect'=> null,
		* 'lang_l'=> null,
		* 'db_system_fields'=> null,
		  'db_boolean_fields'=> null,
		* 'data_table'=> null,
		* 'lg_amp'=> null,
          'module_object'=> null
	);
*/

class importer
{

	protected static $field_delimiter=array(',', ';', '|', '#', '/', '\\', '*', '_', 'space','');
	protected static $text_delimiter=array('&quot;', '&#039;','');
	protected $script_path;
	protected $rel_path;
	protected $lang_l;

	protected $user_data;
	protected $act_up; //upload preparation  ?action=import
	protected $act_imp; //import preparation ?action=import2
	protected $act_cancel; //'?action=setup'
	protected $act_imp_redirect; //'?action=products'
	protected $db_system_fields; //contains fields that are not included in the assign section, but added automatically
	protected $db_boolean_fields; //contains fields that are boolean and may need conversion to 0/1
	protected $data_table; //$g_data.'_data'
	protected $lg_amp;
	protected $module_object; //contains the caller module object(to be able to call some functions)

	//non-parsed vars
	protected $matching_fields;
	protected $csv_records;
	protected $empty_flag;
	protected $text_delim;
	protected $field_delim;
	protected $first_row_flag;
	protected $uploaded_file;
	protected $handle;
	protected $assets_dir;

	protected $output;

	protected $status; //0=>show init upload form, 1=>file uploaded show prepare form, 2=>import

	protected $f;

	public function __construct($settings)
	{
		global $f;
		$this->f=$f;
		$this->setStatus();
		$this->setup($settings);
	}

	protected function setup($settings)
	{
		foreach ($settings AS $key => $val)
			$this->{$key}=$val; //setup parsed params
		$this->matching_fields=array();
		$this->csv_records=array();
		$this->empty_flag=true;
		$this->text_delim=$this->status>0?Formatter::sth2($_POST['text_delimiter']):false;
		$this->field_delim=$this->status>0?Formatter::sth2($_POST['field_delimiter']):false;
		if($this->field_delim=='space') 
			$this->field_delim=' ';
		else if($this->field_delim=='') 
			$this->field_delim='\0';
		$this->first_row_flag=$this->status>0?$_POST['field_names']:false;
		$this->uploaded_file=$this->status>0?$_POST['up_file']:false;
		$this->handle=false;
		$this->output='';
		$this->set_assets_dir();
	}

	protected function setStatus()
	{
		if(isset($_POST['up_file_check']))
			$this->status=1; //
		elseif(isset($_POST['import']))
			$this->status=2; //actual import
		else
			$this->status=0;
	}

	protected function uploadFile($input_file, $path="", $id="")
	{
		$msg='ERROR: No file was uploaded';
		$errors=array(0 => "There is no error, the file uploaded with success.",
			1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini. Try with smaller file.",
			2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
			3 => "The uploaded file was only partially uploaded.",
			4 => "No file was uploaded. Try with smaller file.",
			6 => "Missing a temporary folder");
		if (isset($_FILES[$input_file]))
		{
			if ('' != $_FILES[$input_file]['name'])
			{
				$fname=(($id != "") ? $id . '_' : "") . basename($_FILES[$input_file]['name']);
				if (move_uploaded_file($_FILES[$input_file]['tmp_name'], $path . $fname))
				{
					$msg='true';
				}
			}
			if (0 != $_FILES[$input_file]['error'])
			{
				$errn=$_FILES[$input_file]['error'];
				if ($errn != 0)
					$msg="ERROR: {$errors[$errn]}";
			}
		}
		return $msg;
	}

	protected function build_import_form_upload()
	{
		$js='$.post(\'' . $this->script_path . $this->act_up.'\',$(\'#import\').serialize()+\'&import2=1\',function(re) {$(\'#output\').html(re)});';

		$end=$first_row='';
		$table_data=array();
		$table_data[]=array($this->lang_l('source file'),'
			<form action="' . $this->script_path . $this->act_up. '" method="post" enctype="multipart/form-data">
				<input  type="file" name="up_file"><input type="submit" value=" ' . $this->lang_l('upload') . ' " name="upload">
			</form>
			<form id="import" action="' . $this->script_path . $this->act_imp . '" method="post" enctype="multipart/form-data">');
		if (isset($_FILES['up_file']['name']))
		{
			$file=$_FILES['up_file']['name'];
			if (file_exists($this->assets_dir . basename($file)))
			{
				$prf=time();
				$up_res=$this->uploadFile('up_file', $this->assets_dir, $prf);
			}
			else
				$up_res=$this->uploadFile('up_file', $this->assets_dir);
			if ($up_res == 'true')
				$first_row='';
			if (file_exists($this->assets_dir . $file))
			{
				if (!$handle=fopen($this->assets_dir . $file, 'r'))
					echo 'failed to open uploaded file';
				else
				{
					$first_row=$this->shorten(fgets($handle));
					fclose($handle);
				}
			}
			$delimiter=$this->detectDelimiter($file);
			$detect_fields=$this->detectFirstRow_asFields($file,$delimiter);
			$table_data[]=array($this->lang_l('file'), $file . '<input type="hidden" name="up_file" value="' . $file . '"><input type="hidden" name="up_file_check" value="1">');
			$table_data[]='<input type="checkbox" name="field_names"'.($detect_fields?' checked="checked"':'').' value="true" onclick="'.$js.'">
				 <span class="rvts8 a_editcaption">'.$this->lang_l('first row contains field names').'</span><br>';
			$table_data[]='
				<div style="display:inline-block">
					 <span class="rvts8 a_editcaption">'.$this->lang_l('text delimiter').'</span><br>'
					 .Builder::buildSelect('text_delimiter', self::$text_delimiter, '', ' style="width:120px"', 'value').'
				</div>
				<div style="display:inline-block">
					 <span class="rvts8 a_editcaption">'.$this->lang_l('fields delimiter').'</span><br>'
					 .Builder::buildSelect('field_delimiter', self::$field_delimiter, $delimiter, ' style="width:120px" onclick="'.$js.'"', 'value').'
				</div>';
				$end='<script>$( document ).ready(function() {'.$js.'});</script>';
		}
		$end.='<div id="output"></div>'
				. ' <input type="button" value=" ' . $this->lang_l('cancel') . ' " onclick="document.location=\'' . $this->script_path . $this->act_cancel . $this->lg_amp . '\'" id="cancel_import">';

		$output=$this->build_menu();
		$output.='<div>';
		$output.=Builder::addEntryTable($table_data, $end, '', '') . "</form></div>";
		return $output;
	}

	protected function shorten($line)
	{
		if(strlen($line) > 100) $line=substr ($line, 0, 97).'...';
		return $line;
	}

	protected function detectDelimiter($csvFile)
	{
		$delimiters = self::$field_delimiter;
		foreach($delimiters as $key=>$value){
			if($value=='space') $value=' ';
			else if($value=='') $value='\0';
			$delimiters[$value]=0;
			unset($delimiters[$key]);
		}
		if (file_exists($this->assets_dir . $csvFile))
		{
			$handle = fopen($this->assets_dir . $csvFile, "r");
			$firstLine = fgets($handle);
			fclose($handle);
			foreach ($delimiters as $delimiter => &$count) {
				$count = count(str_getcsv($firstLine, $delimiter));
			}
			return max($delimiters)>1?array_search(max($delimiters), $delimiters):'';
		}
		return self::$field_delimiter[0];
	}
	
	protected function detectFirstRow_asFields($csvFile,$delimiter)
	{
		global $db;
		$detect_fields=false;
		$db_field_names=$db->db_fieldnames($this->data_table);
		$not_system_fields=array();
		foreach ($db_field_names as $v){
			if (!in_array($v, $this->db_system_fields)){
				$not_system_fields[]=$v;
			}
		}
		if (file_exists($this->assets_dir . $csvFile)){
			$handle=fopen($this->assets_dir . $csvFile, "r");
			$firstLine=fgets($handle);
			fclose($handle);
			$columns=str_getcsv($firstLine, $delimiter);
			foreach($columns as $value){
				if (in_array($value, $not_system_fields)){
					$detect_fields = true;
					break;
				}
			}
		}
		return  $detect_fields;
	}

	protected function build_import_form_assign()
	{
		global $db;

		$csv_fields=array();
		$text_delimiter=Formatter::sth2($_POST['text_delimiter']);
		$field_delimiter=Formatter::sth2($_POST['field_delimiter']);
		if ($field_delimiter == 'space')
			$field_delimiter=' ';
		else if($field_delimiter == '')
			$field_delimiter='\0';
		$first_row_flag=isset($_POST['field_names']);
		$file=$_POST['up_file'];

		$first_row='';
		if (file_exists($this->assets_dir . $file))
		{
			if (!$handle=fopen($this->assets_dir . $file, 'r'))
				echo 'failed to open uploaded file';
			else
			{
				setlocale(LC_ALL, 'en_US.utf8');
				if($text_delimiter!='')
					$first_row=fgetcsv($handle, $this->f->max_chars, $field_delimiter,$text_delimiter);
				else
					$first_row=fgetcsv($handle, $this->f->max_chars, $field_delimiter);
			}
		}
		if ($first_row_flag)
			$csv_fields=$first_row;
		else
			foreach ($first_row as $k => $v)
				$csv_fields[]='field' . $k;		
		foreach ($csv_fields as $k => $v)
			$csv_fields[$k]=Formatter::strToLower($v);

		$csv_records=array();
		if($handle)
		{
			if($text_delimiter!='')
			{
				while(!feof($handle)&&count($csv_records)<20)
				{
					$temp_arr=array();
					$each_line=fgetcsv($handle, $this->f->max_chars, $field_delimiter,$text_delimiter);
					foreach($each_line as $v){
						$temp_arr[]=htmlentities($this->shorten($v), ENT_QUOTES, 'UTF-8');
					}
					$csv_records[]=$this->build_assoc_array2($temp_arr,$csv_fields);
				}
			}
			else
			{
				while(!feof($handle)&&count($csv_records)<20)
				{
					$temp_arr=array();
					$each_line=fgetcsv($handle,$this->f->max_chars,$field_delimiter);
					foreach($each_line as $v){
						$temp_arr[]=htmlentities($this->shorten($v), ENT_QUOTES, 'UTF-8');
					}
					$csv_records[]=$this->build_assoc_array2($temp_arr,$csv_fields);
				}
			}
			fclose($handle);
		}

		$row_content='<div class="a_listing" id="import_preview" style="overflow:auto; margin:0; padding:0;">
			<table class="atable '.$this->f->atbgr_class.'">';
		$row1=$row2=$row3='';
		$db_field_names=$db->db_fieldnames($this->data_table);
		foreach($first_row as $key=>$value)
		{
			$frt_v=preg_replace('/[^\PC\s]/u', '', Formatter::strToLower($value));//remove not printable characters for Unicode strings
			$value=$first_row_flag?$frt_v:$csv_fields[$key];
			$not_system_fields=array($this->lang_l('unassigned')=>'');
			foreach ($db_field_names as $v){
				if (!in_array($v, $this->db_system_fields)){
					$not_system_fields[$v]=$v;
				}
			}

			$selected=in_array($value, $not_system_fields) ? $value : '';
			$row1.='<td>'.Builder::buildSelect($value.'_csvField', $not_system_fields, $selected, '', 'swap').'</td>';
			$row2.='<td><span class="rvts8'.($first_row_flag?' a_editcaption':'').'">'.$frt_v.'</span></td>';
		}
		foreach($csv_records as $k=>$v){
			$td='';
			foreach($v as $k_cvs=>$v_cvs){
				if($k_cvs!='')
					$td.= '<td><span class="rvts8">'.$v_cvs.'</span></td>';
			}
			if ($td!='')
				$row3.='<tr class="'.(Unknown::isOdd($k)?$this->f->atbgc_class:$this->f->atbg_class).'">'.$td.'</tr>';
		}
		$row_content.='<tr class="'.$this->f->atbgr_class.'">'.$row1.'</tr>'.'<tr class="'.$this->f->atbgr_class.'">'.$row2.'</tr>'.$row3.'</table></div>';

		$row_content.='
			<p>
				<input type="hidden" name="up_file" value="' . $file . '">
				<input type="hidden" name="text_delimiter" value="' . htmlspecialchars($this->text_delim, ENT_QUOTES) . '">
				<input type="hidden" name="field_names" value="' . $first_row_flag . '">
				<input type="hidden" name="field_delimiter" value="' . $this->field_delim. '">'.
				$this->build_import_form_assign_extra().'
			</p>
			<p>
				<input type="submit" value=" ' . $this->lang_l('import') . ' " name="import">
			</p>';

		$form='
			<form action="' . $this->script_path . $this->act_up . '" method="post" enctype="multipart/form-data">'
				  .$row_content.'
			</form>

			<script>
			function fixPreview(){$("#import_preview").width($("#a_caption").width());}
			$(document).ready( function(){
					fixPreview();
					$(window).resize(function() {fixPreview()});
					$("#cancel_import").insertAfter($("input[name=\'import\']")).css("margin-left","2px");
				});
			</script>
			';

		print $form;

	}

	protected function build_import_form_assign_extra()
	{
		return; //add extra input fields (overridden)
	}

	protected function build_assoc_array2($value, $key)
	{
		$output=array();
		foreach ($key as $v)
		{
			$output[$v]=current($value);
			next($value);
		}
		return $output;
	}

	protected function lang_l($lbl)
	{
		return isset($this->lang_l[$lbl]) ? $this->lang_l[$lbl] : $lbl;
	}

	protected function build_upload_form()
	{
		$out=$this->build_import_form_upload();
		$this->print_output($out);
		exit;
	}

	protected function build_assign_form()
	{
		$this->build_import_form_assign();
		exit;
	}

	protected function import_data()
	{
		$this->add_matching_fields();
		$error='';
		if($this->empty_flag)
			$error=$this->lang_l('assign one field');
		if(!$this->handle=fopen($this->assets_dir.$this->uploaded_file,'r'))
			$error=$this->lang_l('failed to open file');
		if($error==''){
			$this->add_csv_records();
			fclose($this->handle);
			$this->output=$this->handle_import();
		}else
			$this->output='<div class="a_n a_listing"><span style="color:red">'.$error.'</span></div>';
		return;
	}

	protected function add_matching_fields()
	{
		global $db;
		$db_field_names=$db->db_fieldnames($this->data_table);
		foreach($_POST as $key=>$value)
		{
			if(strpos($key,'_csvField')!==false)
			{
				$key=str_replace('_csvField','',$key);
				if(in_array($value,$db_field_names)){
					$this->matching_fields[$value]=Formatter::strToLower($key);
					if(!empty($value))
						$this->empty_flag=false;
				}
				else if(!empty($value)){
					$this->matching_fields[$value]='';
					$this->empty_flag=false;
				}
			}
		}
	}

	protected function add_csv_records()
	{
		if($this->text_delim!='')
			$first_row=fgetcsv($this->handle,$this->f->max_chars,$this->field_delim,$this->text_delim);
		else
			$first_row=fgetcsv($this->handle,$this->f->max_chars,$this->field_delim);
		if($this->first_row_flag)
		{
			$csv_field_labels=$first_row;
			foreach($csv_field_labels as $k=>$v)
				$csv_field_labels[$k]=Formatter::strToLower($v);
		}
		else
		{
			foreach($first_row as $k=>$v)
				$csv_field_labels[]='field'.$k;
			$this->csv_records[]=$this->build_assoc_array2($first_row,$csv_field_labels);
		}
		if($this->text_delim!='')
			while($data=fgetcsv($this->handle,$this->f->max_chars,$this->field_delim,$this->text_delim))
			{
				$temp_rec=$this->build_assoc_array2($data,$csv_field_labels);
				$this->csv_records[]=$temp_rec;
			}
		else
			while($data=fgetcsv($this->handle,$this->f->max_chars,$this->field_delim))
			{
				$temp_rec=$this->build_assoc_array2($data,$csv_field_labels);
				$this->csv_records[]=$temp_rec;
			}
	}

	public function output()
	{
		return $this->output;
	}

	public function process()
	{
		switch($this->status)
		{
			case 0: $this->build_upload_form(); break;
			case 1: $this->build_assign_form(); break;
			case 2: $this->import_data(); break;
		}
	}

	protected function handle_import()
	{
		return; //(overridden)
	}

	protected function build_menu()
	{
		return; //(overridden)
	}

	protected function print_output($out)
	{
		print $out;
	}

	protected function set_assets_dir()
	{
		$this->assets_dir=$this->rel_path . 'innovaeditor/assets/';
	}

}

class ShopImporter extends Importer
{

	final protected function build_menu()
	{
		return $this->module_object->build_admin_menu(' - import');
	}
	final protected function build_import_form_assign_extra()
	{
		return '
			<div style="margin:10px 0 40px 0;">
			<div class="merged" style="display:inline-block;">
				<input type="checkbox" name="cleardata" value="true">
				<span class="rvts8 a_editcaption">'.$this->lang_l('clear existing products').'</span>
			</div>
			<div class="merged" style="display:inline-block;">
				<input type="checkbox" name="clearcategories" value="true">
				<span class="rvts8 a_editcaption">'.$this->lang_l('clear existing categories').'</span>
			</div>
			</div>';
	}
	final protected function handle_import()
	{
		global $db;

		$counter=0;
		if(isset($_POST['cleardata']))
			$db->query('
				DELETE
				FROM '.$db->pre.$this->module_object->g_datapre.'data');

		$default_cid=$db->query_singlevalue('
			SELECT cid
			FROM '.$db->pre.$this->module_object->g_datapre.'categories
			WHERE cid > 1');

		$system_cid=$this->module_object->categoriesModule->get_categoryidbyname('system');

		if(isset($_POST['clearcategories']))
		{
			$db->query('
				DELETE
				FROM '.$db->pre.$this->module_object->g_datapre.'categories
				WHERE cid > 1');
   		$this->module_object->categoriesModule->build_categories_list();
		}
		else if(empty($this->module_object->categoriesModule->category_array))
			$this->module_object->categoriesModule->build_categories_list();

		$maxpid=$this->module_object->get_nextid();
		$maxcid=$this->module_object->get_nextcid();

		foreach($this->csv_records as $rec)
		{
			$single_record=array();

			foreach($this->matching_fields as $field=>$match)
			{
				$val=($match!='' && isset($rec[$match])?$rec[$match]:'');
				if($val!='')
				{
					 if($this->module_object->pg_settings['g_fields_array'][$field]['type']=='int(10)')
						 $single_record[$field]=intval($val);
					 elseif($this->module_object->pg_settings['g_fields_array'][$field]['type']=='decimal(15,4)')
						 $single_record[$field]=Formatter::getFloat($val);
					 else
						 $single_record[$field]=$val;
				}
			}

			$update=false;
			$updateWhere='';
			if(isset($single_record['pid']) && intval($single_record['pid'])>0)
			{
				 $exists=$db->query_singlevalue('
					 SELECT pid
					 FROM '.$db->pre.$this->module_object->g_datapre.'data
					 WHERE pid ='.intval($single_record['pid']));
				 if($exists!=null)
				 {
					 $update=true;
					 $updateWhere=' pid = '.$single_record['pid'];
				 }
				 else
					 $single_record['pid']=$maxpid++;
			}
			elseif(isset($single_record['code']) && $single_record['code']!='')
			{
				 $exists=$db->query_singlevalue('
					 SELECT code
					 FROM '.$db->pre.$this->module_object->g_datapre.'data
					 WHERE code = "'.$single_record['code'].'"');

				 if($exists!=null)
				 {
					 $update=true;
					 $updateWhere=' code = "'.$single_record['code'].'"';
				 }
				 else
					 $single_record['pid']=$maxpid++;
			}
			else
				$single_record['pid']=$maxpid++;

			$single_record['publish']='1';
			if(!isset($single_record['created']) && !$update)
				 $single_record['created']=Date::buildMysqlTime(time());
			if(!$update && array_key_exists('visits_count',$this->module_object->pg_settings['g_fields_array']))
				if(!isset($single_record['visits_count']) || $single_record['visits_count']=='')
					 $single_record['visits_count']=0;
			if(!$update)
			{
				if((!isset($single_record['category'])) || $single_record['category']=='')
				{
					 $single_record['cid']=$system_cid!==false?$system_cid:$default_cid;
					 if($system_cid!==false)
						 $single_record['publish']='0';
				}
				else
				{
					 $single_record['cid']='1';
					 $cid=0;
					 $cname=$single_record['category'];
					 $temp=$this->module_object->categoriesModule->get_categoryidbyname($cname);

					 if($temp!==false)
					 {
						  $cid=$temp;
						  $single_record['cid']=$temp;
					 }

					 if($cid==0)
					 {
						  $data=array();
						  $single_record['cid']=$maxcid;
						  $data['cid']=$maxcid++;
						  $data['cname']=$cname;
						  $data['ccolor']='#330000';
						  $db->query_insert($this->module_object->pg_settings['g_data'].'_categories',$data);

						  $this->module_object->categoriesModule->category_array[$data['cid']]=array(
								'id'=>$data['cid'],
								'pid'=>-1,
								'kids'=>array(),
								'name'=>$data['cname'],
								'color'=>$data['ccolor']);
					 }
				}
			}
			if($update)
				$db->query_update($this->data_table,$single_record,$updateWhere);
			else
				$db->query_insert($this->data_table,$single_record);
			$counter++;
		}
		unlink($this->assets_dir.$this->uploaded_file);
		return '<br><br><br><br><br><br><script type="text/javascript">alert("'.$counter.' '.$this->lang_l('records imported').'");window.location="'.$this->module_object->full_script_path.$this->act_imp_redirect.'";</script>';
	}
	final protected function print_output($out)
	{
		print $this->module_object->getHtmlTemplate($out,'');
	}
}

class NewsImporter extends Importer
{
	//overriding functions
	final protected function build_menu()
	{
		return $this->module_object->build_admin_menu('subscribers');
	}

	final protected function print_output($out)
	{
		$out=Formatter::fmtAdminScreen($out);
		print $this->module_object->fmt_in_template(false,'import', $out);
	}

	final protected function build_import_form_assign_extra()
	{
		global $db;
		$row='';
		$groups_raw=$db->fetch_all_array('SELECT * FROM '.$this->m_pre.'groups');
		if(!empty($groups_raw))
		{
			$groups['none']=$this->lang_l('none');
			foreach($groups_raw as $g)
				$groups[$g['group_id']]=$g['group_name'];
			$row='
				<tr>
				<td>
					 <span class="rvts8">'.$this->lang_l('add to group').'</span>
				</td>
				<td class="rvps2">'.
					  Builder::buildSelect('add_to_group',$groups,'none').'
				</td>
				</tr>';
		}

		return $row;
	}

	final protected function handle_import()
	{
		$output=$imported_list=$duplicated='';
		$counter=$duplicated_counter=0;
		foreach($this->csv_records as $rec)
		{
			if(empty($rec))
				continue;
			if(!isset($rec[$this->matching_fields['email']]) || $rec[$this->matching_fields['email']]=='')
				continue;
			$sub_exists=$this->module_object->db_fetch_subcriber($rec[$this->matching_fields['email']],'by_email');
			if(empty($sub_exists))
				$this->import_record($rec, $imported_list,$counter);
			else
			{
				$duplicated.=$rec[$this->matching_fields['email']].', ';
				$duplicated_counter++;
			}
		}
		if($imported_list!='')
			$this->module_object->db_write_log('IMP',$imported_list,'SUCCESS','sub');

		$output.='<div class="a_n a_listing">
						 <h2>'.$counter.' '.$this->lang_l('subscribers imported').'</h2><br>
						 <p><span class="rvts8">'.$imported_list.'</span></p><br>'.
						 (($duplicated!='')?'<h2>'.$duplicated_counter.' '.$this->lang_l('duplicated subscribers').'</h2><br>
						  <span class="rvts8">'.$this->lang_l('non-imported subscribers').':'.'</span>
						  <p><span class="rvts8">'.$duplicated.'</span></p>':'').'
					</div>';
		unlink($this->assets_dir.$this->uploaded_file);
		return $output;
	}

	//private functions
	final private function import_record($rec,&$imported_list,&$counter)
	{
		global $db;
		$single_record=array();
		foreach($this->matching_fields as $field=>$match)
			$single_record [$field]=($match!='' && isset($rec[$match])?$rec[$match]:'');
		$single_record['sub_id']=md5(uniqid(mt_rand(),true));
		$single_record['ss_subscribe_date']=Date::buildMysqlTime(time());
		$single_record['ss_confirm_date']=Date::buildMysqlTime(time());
		$single_record['ss_confirmed']=1;
		$single_record['ss_uploaded_files']=''; $single_record['ss_uploaded_filetypes']='';
		$db->query_insert($this->data_table,$single_record);
		if(isset($_REQUEST['add_to_group']) && $_REQUEST['add_to_group']!='none')
			$db->query_insert($this->module_object->pg_pre.'groups_subs', array('group_id'=>intval($_REQUEST['add_to_group']),'sub_id'=>$single_record['sub_id']));
		$counter++;
		$imported_list.=$rec[$this->matching_fields['email']].', ';
		return;
	}
}

class CAImporter extends Importer
{
//overriding functions
	protected function lang_l($lbl)
	{
		return $this->module_object->ca->lang_l($lbl);
	}

	final protected function build_menu()
	{
		return $this->module_object->build_menu();
	}
	final protected function print_output($out)
	{
		$out=Formatter::fmtAdminScreen($out);
		print $this->module_object->ca->GT($out);
	}

	final protected function build_import_form_assign_extra()
	{
		$real_pass_line='
			<div class="rp_holder" style="display:none;">
				<input type="checkbox" name="real_pass" value="real_pass" checked="checked"/>'.$this->lang_l('Use real passwords').'<br>
				<span style="font-style: italic; font-size:11px;">'.$this->lang_l('Check this when you have the actual password in the field').'</span>
			</div><br>';
		$access_line=$this->module_object->build_access_line(
			$this->module_object->ca->user->isAdminUser(),
			$this->module_object->build_extra_access_option(-1,false)
			);
		return $real_pass_line.$access_line;
	}

	protected function randomUsername($l=12)
	{
		 $chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		 $username="";
		 for($i=0;$i<$l;$i++)
			$username.=$chars[mt_rand(0, strlen($chars))];

		 return $username;
	}

	final protected function handle_import()
	{
		global $db;
		$output=$imported_list=$duplicated='';
		$counter=$duplicated_counter=0;
		if(!isset($this->matching_fields['username']))
			 $this->matching_fields['username']='username';

		foreach($this->csv_records as $rec)
		{
			if(empty($rec))
				continue;

			if(!isset($rec[$this->matching_fields['username']]))
			{
				 if(isset($this->matching_fields['email']))
						$rec[$this->matching_fields['username']]=$rec[$this->matching_fields['email']];
				 else
						$rec[$this->matching_fields['username']]='';
			}

			if($rec[$this->matching_fields['username']]!='')
			{
				$user_exists=User::mGetUser($rec[$this->matching_fields['username']],$db,'username');
				if(empty($user_exists))
					 $this->import_record($rec, $imported_list,$counter);
				else
				{
					 $duplicated.=$rec[$this->matching_fields['username']].', ';
					 $duplicated_counter++;
				}
			}
			elseif(isset($this->matching_fields['email']) && isset($rec[$this->matching_fields['email']]) && $rec[$this->matching_fields['email']]!='')
			{
				$user_exists=User::mGetUser($rec[$this->matching_fields['email']],$db,'email');
				if(empty($user_exists))
				{
					 $rec[$this->matching_fields['username']]=$this->randomUsername();
					 $user_exists=User::mGetUser($rec[$this->matching_fields['username']],$db,'username');
					 while ($user_exists)
					 {
						  $rec[$this->matching_fields['username']]=$this->randomUsername();
						  $user_exists=User::mGetUser($rec[$this->matching_fields['username']],$db,'username');
					 }
					 $this->import_record($rec,$imported_list,$counter);
				}
				else
				{
					 $duplicated.=$rec[$this->matching_fields['email']].', ';
					 $duplicated_counter++;
				}
			}
			else
				 continue;

		}

		if($imported_list!='')
			ca_log::write_log('imp',$this->module_object->ca->user->getId(),$this->module_object->ca->user->mGetLoggedAs(),'success');

		unlink($this->assets_dir.$this->uploaded_file);

		$output.='
			 <div class="a_n a_listing">
	 			 <h2>'.$counter.' '.$this->lang_l('users imported').'<br></h2>
				 <p><span class="rvts8">'.$imported_list.'</span></p><br>'.
		  		(($duplicated!='')?'<h2>'.$duplicated_counter.' '.$this->lang_l('duplicated users').'</h2><br>'.
							'<span class="rvts8">'.
							$this->lang_l('non-imported users').':<br>'.$duplicated.
							'</span>':'').'
			 </div>';
		return $output;
	}
	final protected function set_assets_dir()
	{
		parent::set_assets_dir();
		if(strpos($this->assets_dir, '../')===FALSE)
			$this->assets_dir='../'.$this->assets_dir;
	}

	//private functions
	final private function import_record($rec,&$imported_list,&$counter)
	{
		global $db;

		$user_info='';
		$single_record=array();
		foreach($this->matching_fields as $field=>$match)
		{
			if($match!='' && isset($rec[$match]))
			{
				if($field=='password' && isset($_POST['real_pass']))
					$single_record[$field]=crypt ($rec[$match]);
				elseif(in_array($field, $this->db_boolean_fields))
					$single_record[$field]=$this->boolasnum($rec[$match]);
				else
					$single_record[$field]=$rec[$match];

				if($user_info=='' || $field=='username') $user_info=$rec[$match];
			}
			else
				$single_record [$field]='';
		}
		$single_record['uid']='null';
		$single_record['creation_date']=Date::buildMysqlTime(time());
		$single_record['self_registered']=0;
		$single_record['self_registered_id']='';
		$single_record['confirmed']=1;
		$single_record['status']=1;
		$single_record['pass_changeable']=1;
		$user_id=$db->query_insert($this->data_table,$single_record);
		$access_data=$this->module_object->ca->users->build_access_array($user_id);
		foreach($access_data as $acc)
			$db->query_insert('ca_users_access',$acc);
		$counter++;
		$imported_list.=$user_info.', ';
		return;
	}

	final private function boolasnum($var)
	{//returns '0' or '1' - for database boolean
		if(($var===true)||$var==1||preg_match('/^yes$/i',$var)===1||preg_match('/^y$/i',$var)===1)
			return 1;
		return 0;
	}

}


?>

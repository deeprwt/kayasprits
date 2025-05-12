<?php
#### THESE HELPS TO MANAGE THE ADDSLASHES AND STRIPSLASHES
#### IF YOU USE PHP FUNCTIONS IT IS DEPENDENT ON SERVER CONFIGURATION.
#### SO IT SHOULD BE AVOID.

##################popup after close the site
/*function leave() {
    window.open('form1.php','','toolbar=no,menubar=no,location=no, height=400, width=400','');
}*/

############# SAN FUNCTIONS 
function imageResize($width, $height, $target) {

		//takes the larger size of the width and height and applies the
		if ($width > $height) {
		$percentage = ($target / $width);
		} else {
		$percentage = ($target / $height);
		}

		//gets the new value and applies the percentage, then rounds the value
		echo $width = round($width * $percentage);
		echo $height = round($height * $percentage);

	//returns the new sizes in html image tag format...this is so you can plug this function inside an image tag and just get the

		return "width=\"$width\" height=\"$height\"";
}


function getNumberComp($Id){ 
##3 online users
$mysql_db = new DB();
  $qry = "select * from comp_subcat where `SubCat_Id` = '$Id'";
  $mysql_db->query( $qry );
 return $mysql_db->numRows();
}

function getuser($Id){ 
##3 online users
//echo $Id;
$mysql_db = new DB();
  $qry = "select * from users where `id` = '$Id'";
  $mysql_db->query( $qry );
 $data= $mysql_db->fetchArray();
 return $data['name'];
} 
############# USED TO REDIRECT TO ANOTHER PAGES
function redirect($page) {
		if(!headers_sent())
			header("location:$page");
		else
			echo "<script>location.href='$page'</script>";
}
########## USE WHEN PUTTING DATA INTO DATABASE
function putData( $inputValue ) {
	if( get_magic_quotes_gpc() ) {
		return trim($inputValue);  
	} else {
		return addslashes( trim($inputValue) );  
	}
}
##### THIS FUNCTION SPECAILLY USED WHEN TO CREATE THE WEBPAGE FROM THE HTML FILE
##### WE NEED TO ADD THE SLASHES
function addSlashesTo( $inputValue ) {
	return addslashes( trim($inputValue) );  
}


######## THIS FUNCTION SPECIALLY IS USED TO GET THE VALUES FROM STATE, REGION AREA, AND COMMUNITIES.

function getValuesArr( $tableName, $dataValueField, $dataTextField, $defaultValue = "", $whereClause = "" ) {
		
    $mysql_db = new DB();
	
	if( $defaultValue != "" ) {
		$valueArr = array( "0" => $defaultValue );
	} 
	
	if( $tableName == "") {
		die("ERROR: Invalid Operation.");
	}
	$query = "select $dataValueField, $dataTextField from $tableName ";		
	if( $whereClause != "" ) {
		$query .= " where ".$whereClause;
	}
	$mysql_db->query($query);
	if( $mysql_db->numRows() > 0 ) {
		while( $data = $mysql_db->fetchArray() ) {
				$valueArr[$data[$dataValueField]] = $data[$dataTextField];			
		}
		return $valueArr;
	} else {
		return array( "0" => $defaultValue);
	}

} ######## ENDOOF THE FUNCTION
function getValueDistinctsArr( $tableName, $dataValueField, $dataTextField, $defaultValue = "", $whereClause = "" ) {
		
    $mysql_db = new DB();
	
	if( $defaultValue != "" ) {
		$valueArr = array( "0" => $defaultValue );
	} 
	
	if( $tableName == "") {
		die("ERROR: Invalid Operation.");
	}
	$query = "select distinct($dataValueField) from $tableName ";		
	if( $whereClause != "" ) {
		 $query .= " where ".$whereClause;
	}

	$mysql_db->query($query);
	if( $mysql_db->numRows() > 0 ) {
		while( $data = $mysql_db->fetchArray() ) {
				$valueArr[$data[$dataValueField]] = $data[$dataValueField];			
		}
		return $valueArr;
	} else {
		return array( "0" => $defaultValue);
	}

} ######## ENDOOF THE FUNCTION
###############FUNCTION TO FIND MINIMUM VALUES
function getValueMinArr( $tableName, $dataValueField, $dataTextField, $defaultValue = "", $whereClause = "" ) {
		
    $mysql_db = new DB();
	
	if( $defaultValue != "" ) {
		$valueArr = array( "0" => $defaultValue );
	} 
	
	if( $tableName == "") {
		die("ERROR: Invalid Operation.");
	}
	 $query = "select $dataValueField from $tableName order by Mileage";		
	if( $whereClause != "" ) {
		 $query .= " where ".$whereClause;
	}
	$mysql_db->query($query);
	if( $mysql_db->numRows() > 0 ) {
		while( $data = $mysql_db->fetchArray() ) {
				$valueArr[$data[$dataValueField]] = $data[$dataValueField];			
		}
		return $valueArr;
	} else {
		return array( "0" => $defaultValue);
	}

} ######## ENDOOF THE FUNCTION

###### USED IN CASE OF PASSING THE VALUES TO PAYMENT GATEAY AS HIDDEN VALUES	
function getData( $outputValue ) {
	if( get_magic_quotes_gpc() ) 
		return htmlentities( stripslashes(trim($outputValue)) );  
	
	return htmlentities( trim($outputValue) );  
}

function useHTMLEntities( $vl ) {
		return htmlentities( trim($vl) );  
}


##### WE NEED TO REMOVE THE SLASHES
function removeSlashesFrom( $inputValue ) {
	if( get_magic_quotes_gpc() ) {
		return stripslashes( trim($inputValue) );  
	} else {
		return trim($inputValue);  
	}
}

###### THIS TELLS THAT THE VALUE IS VALID INTEGRE TYPE
function isValidInteger( $str ) {
	if(preg_match("/^([0-9]+)$/",$str,$reg) ) { ####  BE VALID INTEGER
		return true;
	} else {
		return false;
	}
} # END OF FUNCTION

########### USED TO FIRE THE INSERT QUERY
function insertData($arr, $tableName) {
	$mysql_db = new DB();
	$query = "";		

	if(! is_array($arr) ) {
		die("ERROR: Invalid Operation.");
	}
	foreach($arr as $key => $value ) {
	  $query .= "$key='$value',";   		
	}
	$query = " insert into $tableName set ".substr($query,0,-1);
	$mysql_db->query($query);
	return $mysql_db->insertID(); ######## RETURNS LAST INSERTED ID
} # END OF FUNCTION




########### USED TO FIRE THE UPDATE QUERY
function updateData($arr, $tableName, $whereClause) {
	$mysql_db = new DB();
	$query = "";		

	if(! is_array($arr) ) {
		die("ERROR: Invalid Operation.");
	}
	foreach($arr as $key => $value ) {
	   $query .= "$key='$value',";   		
	}
	if( $whereClause == "" ) {
		die("ERROR: Invalid Operation in where clause.");
	}

	$whereClause = " where ".$whereClause;
	$query = " update $tableName set ".substr($query,0,-1).$whereClause;
	$mysql_db->query($query);
} # END OF FUNCTION



######### FUNCTION USED TO KNOW WHETHER RECORD EXISTS OR NOT
function matchExists($tableName, $whereClause) {
	$mysql_db = new DB();
	
	if( $tableName == "" || $whereClause == "") {
		die("ERROR: Invalid Operation.");
	}
	$query = "select count(*) as cnt from $tableName where ".$whereClause;		
	$mysql_db->query($query);
	$data = $mysql_db->fetchArray();
	if( $data['cnt']>0 ) {
		return true;
	} else {
		return false;
	}
} ### END OF FUNCTION

######### CREATE THE SINGLE SELECTED DROP DOWN BOX
function createComboBox($arr, $name, $sltValue, $extraInfo="") {
	if(! is_array($arr) ) {
		die("ERROR: Incorect Information");
	}
	
	$data = "<select name='$name' $extraInfo>";
	foreach( $arr as $key => $value ) {
		$sel = "";
		if( $key == $sltValue )
			$sel = " selected ";
		$data .= "<option value='$key' $sel>$value</option>";
	}
	return $data .= "</select>";
} ########### END OF FUNCTION


######### CREATE THE MULTIPLE SELECTED DROP DOWN BOX(LIST BOX)
function createListBox($arr, $name, $sltValueArr, $extraInfo="") {
	
	if(! is_array($arr) ) {
		die("ERROR: Incorect Information");
	}
	if(! is_array($sltValueArr) ) {
		die("ERROR: Incorect Information");
	}
	
	$data = "<select name='$name' $extraInfo>";
	foreach( $arr as $key => $value ) {
		$sel = "";
		if( in_array($key, $sltValue) )
			$sel = " selected ";
		$data .= "<option value='$key' $sel>$value</option>";
	}
	return $data .= "</select>";
} ############ END OF FUNCTION


######## THIS FUNCTION HELPS TO KNOW THAT URL IS ACCESSIBLE OR NOT ######################

function urlExists($url) {
	$urlParts = parse_url($url);  
	$host = $urlParts['host']; 
	$fsocket_timeout = 10; 
	$port = (isset($urlParts['port'])) ? $urlParts['port'] : 80;  

	if( !$fp = @fsockopen( $host, $port, $errno, $errstr, $fsocket_timeout ))
	return false; // url not available
	else 
	return true; // yes url exists
} # END OF FUNCTION



if (!function_exists('location')) {

    function location($parentid = '', $type = 0,$sltValue=0) {
        /* type 0=country, 1=state, 2=city */
		$whereClause='';
        $mysql_db = new DB();		
        $types = array('country', 'State', 'City');
		 if (!empty($parentid)) {
           $whereClause .=" parent_id='".$parentid."'";
        }
		
		 if (!empty($type)) {
           $whereClause .=" and location_type='".$type."'";
        } 
		
		$query = "select * from tbl_location where ".$whereClause." order by name asc";		
	    $mysql_db->query($query);
	 
		$data = "<select name='location_id' class='form-control select2 required' style='width: 100%;' id='ajax-state' onchange='ajax_call({location_id:this.value,location_type:2},\'ajax-city\')'";
	   while($data_arr=$mysql_db->fetchArray()) {
		$sel = "";
		if($data_arr['name']==$sltValue)
			$sel = " selected ";
		$data .= "<option value='".$data_arr['name']."' $sel>".$data_arr['name']."</option>";
	    }
	   return $data .= "</select>";
      
    }

}

if (!function_exists('location1')) {

    function location1($parentid = '', $type = 0) {
        /* type 0=country, 1=state, 2=city */
		$whereClause='';
        $mysql_db = new DB();		
        $types = array('country', 'State', 'City');
		 if (!empty($parentid)) {
           $whereClause .=" parent_id='".$parentid."'";
        }
		
		 if (!empty($type)) {
           $whereClause .=" and location_type='".$type."'";
        } 
		
		$query = "select * from tbl_location where ".$whereClause." order by name asc";		
	    $mysql_db->query($query);
	 
		$data = "<select name='location_id' class='form-control select2 required' style='width: 100%;' id='ajax-city'";
	   while($data_arr=$mysql_db->fetchArray()) {
		$sel = "";
		if( in_array($key, $sltValue) )
			$sel = " selected ";
		$data .= "<option value='".$data_arr['name']."' $sel>".$data_arr['name']."</option>";
	    }
	   return $data .= "</select>";
      
    }

}

function userName($userid) {
	$mysql_db = new DB();
	if($userid == "") {
		die("ERROR: Invalid Operation.");
	}
	 $query = "select * from admin where id='".$userid."'";		
	$mysql_db->query($query);
	$data = $mysql_db->fetchArray();
	return $data['username'];
} ### END OF FUNCTION

?>
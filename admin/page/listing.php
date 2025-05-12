<?php
##########################################################
#####     CREATED BY   : rajeshkumar.maurya@gmail.com    ######
#####          CREATION DATE: PUT DATE              ######
#####     CODE BRIEFING: PUT THE PAGE FUNCTIONALITY ######
#####                                              ######
#####     COMPANY           : baratotravels    ######
#####                                               ######
##########################################################
include_once("session.php");
include_once("../config.inc.php");
include_once("../functions/functions.php");
include_once("../functions/functions.inc.php");
include_once("../includes/db.inc.php");
include_once("../includes/page.inc.php");
include_once("../includes/upload.inc.php");

$qryString = $_REQUEST['module'];
$firstName = removeSlashesFrom( $_REQUEST['name'] );
$membership= removeSlashesFrom( $_REQUEST['member'] );
///////Drop Down
$makeArr = array();
$makeArr = getValuesArr(page, "Id", "Link", "---Top Level---", "");


######## TO DELETE SINGLE RECORD
if( $_REQUEST['task'] == "delete_single" && preg_match("/^([0-9]+)$/", $_REQUEST['id'], $reg) ) {
  $qry = " delete from page where Id=".$_REQUEST['id'];
        $db->query( $qry );
        $errorMsg = "<div align='center' class='error-message'>Record deleted successfully!</div>";
}


########## DE-ACTIVATE THE ACCOUNT
if( $_REQUEST['task'] == "deactivate" && $_REQUEST['id'] != "" ) {
       $qry = " update page set status='0' where Id=".$_REQUEST['id'];
        $db->query( $qry );
        $errorMsg = "<div align='center' class='warning-message'>Account has been de-activated successfully!</div>";
}
if( $_REQUEST['task'] == "delete" ) {
        $qry = " delete from page where Id in(".@implode( ",", $_POST['chk'] ).")";
        $db->query( $qry );
        $errorMsg = "<div align='center' class='error-message'>Record deleted successfully!</div>";
}
########## ACTIVATE THE ACCOUNT
if( $_REQUEST['task'] == "activate" && $_REQUEST['id'] != "" ) {
        $qry = " update page set status='1' where id=".$_REQUEST['id'];
        $db->query( $qry );
        $errorMsg = "<div align='center' class='notice-message'>Account has been activated successfully!</div>";
}

########## UP AND DOWN RANK
if( $_REQUEST['task'] == "UP" && $_REQUEST['id'] != "" && $_REQUEST['rank'] != "" ) {
 $rank1 = $_REQUEST['rank'];
   $rank = $rank1+1;
        $qry = " update page set Rank='$rank' where id=".$_REQUEST['id'];
        $db->query( $qry );
        $errorMsg = "<div align='center' class='warning-message'>Rank has been Move one up now!</div>";
}
if( $_REQUEST['task'] == "Down" && $_REQUEST['id'] != "" && $_REQUEST['rank'] != "") {
$rank1 = $_REQUEST['rank'];
  $rank = $rank1-1;
        $qry = " update page set Rank='$rank' where id=".$_REQUEST['id'];
        $db->query( $qry );
        $errorMsg = "<div align='center' class='warning-message'>Rank has been Move one Down now!</div>";
}

?>
<table width="95%"  cellpadding="0" cellspacing="0"  border="0">
  <tr><td valign="top" height="35" align="left" class="td_heading1">Manage Pages</td></tr>
  </table>
<table width="95%"  cellpadding="0" cellspacing="0"  border="0">
<?php if( $errorMsg != "" ) { ?>
<tr ><td  valign="top" align="center"><?php echo $errorMsg ?></td></tr>
<?php } ?>
<?php if( $_REQUEST['msg'] != "" ) { ?>
<tr ><td  valign="top" align="center"><?php echo $_REQUEST['msg'] ?></td></tr>
<?php } ?>
 </table>

   <form name="frm1" action="" method="post">
    <table border="0" cellpadding="0" cellspacing="0" align="center" width="90%" >
        <tr><td height="25" align="left" class="gridcellalt"><b>Search Criteria</b></td></tr>
        <tr><td align="center">
                <table border="0" cellpadding="2" cellspacing="2" align="center" width="100%" id="loginform">
 			
				<tr>
                <td class="text_label">Title</td>
                <td ><input type="text" name="name" value="" class="textfield"></td>
                <td  colspan="2"></td>
                </tr>

               <tr>
                
                <td class="Heading"></td>
                <td class="Heading" colspan="2"></td>
                </tr>

	
                <td class="text_label">From</td>
                <td class="Heading"><input type="text" name="sendingFromDate" value="" class="textfield_admin">
                <a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.frm1.sendingFromDate);return false;" HIDEFOCUS><img name="popcal" align="absmiddle" src="images/dlcalendar_2.gif" width="13" height="13" border="0" alt=""></a>
                </td>
                <td class="text_label">To <input type="text" name="sendingToDate" value="" class="textfield_admin">
                <a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.frm1.sendingToDate);return false;" ><img name="popcal" align="absmiddle" src="images/dlcalendar_2.gif" width="13" height="13" border="0" alt=""></a>
                </td>
                <td class="text_label"><input class="button1" type="submit" name="submit" value="Search" />
                </td>
                </tr>
                 </form>
                </table>
                </td></tr></table><br>

<table width="90%"  cellpadding="0" cellspacing="0"  border="0">

        <tr><td align="center">&nbsp;</td></tr>
 <form name="frm" action="" method="post" onSubmit="return validate(this);">
<input type="hidden" name="task" value="">
<input type="hidden" name="qryString" value="<?php echo $qryString;?>">
<tr>
<td  align="left">
<input type="button" name="deleteAll" value="Delete Selected" class="button" onClick="DeleteMore();">
</td>
<td  align="right" width="30%">
<a href="admin_setting.php?module=pagenew" class="button2">&nbsp;Add New Page&nbsp;</a>
</td>
</tr>
<tr>
<td colspan="2">
                  <table width="100%"  cellpadding="1" cellspacing="1"  border="0" class="grid">
                  <tr>
                  <td width="5%" align="center" class="gridhead"><input type="checkbox" name="chkAll" onclick="checkAll(document.frm, this.checked);"></td>
						<td width="25%" align="center" class="gridhead">Title</td>
				        <td width="25%" align="center" class="gridhead">Url</td>             
                  		<!--<td width="15%" align="center" class="gridhead">Position</td> 
                  		<td width="10%" align="center" class="gridhead">Rank</td>-->
                  		<td  width="20%" colspan="2" align="center" class="gridhead">Action </td>
                                    </tr>
                  <?php
                  $qryStr = array(); ######## USED TO PASS AS QUERY STRING
                  $qry = "select * from page ";
                  $name = putData( $_REQUEST['name'] );
                  // $membership = $_REQUEST['Member'];
                  $from = $_POST['sendingFromDate'];
                  $to = $_POST['sendingToDate'];



                  $whereClause = "";
                  if( $name != "" ) {
                          if( $whereClause == "") {
                                  $whereClause .= "Where Title like '%$name%' ";
                          }else{
                        $whereClause .= " AND Title like '%$name%' ";
                        }

                  }

               /*   
                   if( $membership != "" AND $membership != '0'  )  {
                          if($whereClause == "") {
                                  $whereClause .= " Where  Level  ='$membership' ";
                          } else {
                                  $whereClause .= " and  Level  ='$membership'  ";
                          }

                  }     */



                 if ( $from != "" && $to != "" ){
                          if($whereClause == "") {
                                  $whereClause .= " Where Date  BETWEEN '$from' and '$to' ";
                          }else{
                                  $whereClause .= " and Date  BETWEEN '$from' and '$to' ";
                          }
                  }
             $whereClause .= "";
             $qry = $qry.$whereClause;
                  $db->query( $qry );
                  $page = new Page();
                  $page->set_page_data('', $db->numRows(), PAGING, 0, true, false, false);
                  $page->set_qry_string("module=$qryString");
                  if( count($qryStr) > 0 ) ###### ADD EXTRA QUERY STRING
                         $page->set_qry_string( "module=$qryString&".implode("&", $qryStr) );

                  $qry = $page->get_limit_query($qry);
                  $db->query( $qry );
                  if( $db->numRows() > 0 ) {
                          while( $data = $db->fetchArray() ) {
                  ?>
				  <?php //if($data['Position']=='0'){ $pos="Common"; }elseif($data['Position']=='1'){$pos="Footer"; }?>
                          <tr  class="gridcell">
                          <td  align="center" ><input type="checkbox" name="chk[]" value="<?php echo $data['Id']; ?>"></td>
							<td  align="left" >&nbsp;<?php echo $data['Title'];?></td>
							<td  align="left" >&nbsp;<?php echo $data['Url'];?></td>
						   <!--<td  align="left" >&nbsp;<?php echo $pos;?></td> -->
                         <!--<td  align="left" >&nbsp;<?php echo $data['Rank']; ?></td>
                    <td  align="left" >&nbsp;<a href="admin_setting.php?module=<?=$qryString?>&task=Down&id=<?PHP echo $data['Id']; ?>&rank=<?php echo $data['Rank'];?>"><img src="images/down.png" border="0" alt="Rank Down" title="Move Down"  /></a>&nbsp;
                    <a href="admin_setting.php?module=<?=$qryString?>&task=UP&id=<?PHP echo $data['Id']; ?>&rank=<?php echo $data['Rank'];?>"><img src="images/up.png" border="0" alt="Rank UP" title="Move UP"  /></a></td> -->
                     <td  align="left" >&nbsp;
                    <?php

                     if( $data['Status'] == "1" ){  ?>
                          <a  href="admin_setting.php?module=<?=$qryString?>&task=deactivate&id=<?PHP echo $data['Id']; ?>"><img src="images/ok.gif" border="0" alt="Make De-activate" title="Make De-activate"></a>
                          <?php }   elseif( $data['Status'] == "0") {?>
                                  <a href="admin_setting.php?module=<?=$qryString?>&task=activate&id=<?PHP echo $data['Id']; ?>"><img src="images/b_drop.png" border="0" alt="Make Activate" title="Make Activate"></a>
                         <?php } ?>

                         &nbsp;<a href="admin_setting.php?module=pageedit&id=<?PHP echo $data['Id']; ?>"><img src="images/b_edit.png" border="0" alt="Account Details" title="Account Details"  /></a>&nbsp;<a href="#" onclick="return confirmDelete(<?php echo $data['Id'];?>);"><img src="images/delete.gif" border="0"  title="Delete"></a>
                              </td>
                  </tr>

                  <?php
                           } ### END OF WHILE
                  ?>
                  <tr height="20" bgcolor="White">
                  <td  class="gridcellalt" align="right" colspan="7"><span class="path">&nbsp;<?php $page->get_page_nav(); ?></span>&nbsp;</td>
                     </tr>
                  <?PHP
                  }        ##### ENF OF IF
                  else {
                          echo "<tr bgcolor='White'><td colspan='7' class='error-message' align='center'>NO RECORD FOUND!!!</td></tr>";
                  }
                  ?>
                  </table>

                  <iframe width=102 height=100 name="gToday:supermini:agenda.js" id="gToday:supermini:agenda.js" src="ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
                </iframe>
</form>
<script language="javascript">
function confirmDelete( id ) {
        if( confirm("Are you sure to delete this Record?") ) {
                        document.frm.task.value = "delete_single";
                        document.frm.action = "?module="+document.frm.qryString.value+"&id="+id;
                        document.frm.submit();
                }
}
function validate( frm ) {


                var str = "";
        if( trim(frm.sendingFromDate.value) == "" && trim(frm.sendingToDate.value) != "" )
        str += "Start Date should not be blank!\n";
        if( trim(frm.sendingFromDate.value) != "" && trim(frm.sendingToDate.value) == "" )
        str += "End Date should not be blank!\n";

        if( trim(frm.sendingFromDate.value) != "" ) {
                if(! dateValidate( trim(frm.sendingFromDate.value) )  )
                        str += "Start Date should be in valid format (yyyy-mm-dd)!\n";
        }
        if( trim(frm.sendingToDate.value) != "" ) {
                if(! dateValidate( trim(frm.sendingToDate.value) )  )
                        str += "End Date should be in valid format (yyyy-mm-dd)!\n";
        }

        if( trim(frm.sendingFromDate.value) != "" && trim(frm.sendingToDate.value) != "" ) {
                if( dateValidate( trim(frm.sendingFromDate.value) ) && dateValidate( trim(frm.sendingToDate.value) )  )        {
                        if(! dateComparison(  trim(frm.sendingToDate.value), trim(frm.sendingFromDate.value) ) )
                                str += "End Date should be greater than Start Date!\n";
                }
        }




        if( str != "" ) {
                alert("Please follow the instructions!\n\n"+str);
                return false;
        }
        return true;
}


// check all the check boxes
function checkAll( frm, chkAllV ) {

        for( var i=0; i<frm.elements.length; i++ ) {
                if( frm.elements[i].name == "chk[]")
                                frm.elements[i].checked = chkAllV;
        }

}

// delete confirmation
function DeleteMore( frm ) {
        if ( isChecked() ) {
                if( confirm("Are you sure to delete this Record?") ) {
                        document.frm.task.value = "delete";
                        document.frm.action = "?module="+document.frm.qryString.value;
                        document.frm.submit();
                }
        } else {
                alert("Please select the check boxes to delete the records!");
        }
}


function isChecked() {
        var flag = false;
        for( var i=0; i<document.frm.elements.length; i++ ) {
                if( document.frm.elements[i].name == "chk[]" && document.frm.elements[i].checked ) {
                                flag = true;
                                break;
                }
        }
        return flag;
}
</script>
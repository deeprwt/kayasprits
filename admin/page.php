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
include_once("../includes/upload.inc.php");
include_once("fckeditor/fckeditor.php");
///////Drop Down
$makeArr = array();
$makeArr = getValuesArr(page, "Id", "Link", "---Top Level---", "");

$errorMsg = "";
if( $_REQUEST['submit'] == "Update"  ) {


        $updateArr = array(
		
		                            // "Position"                  	=>  putData( $_POST['Position'] ),
									 "Title"                        =>  putData( $_POST['Title'] ),
                                     "Content"                   	=>  putData( $_POST['gseditor'] ),
									   "Url"                  		=>  putData( $_POST['Url'] ),
									   "Rank"                  		=>  putData( $_POST['Rank'] ),
                                     "Description"                  =>  putData( $_POST['Description'] ),
									 "Position"                     =>  putData( $_POST['Position'] ),
									  "Link"                         =>  putData( $_POST['Link'] ),
                                     "Date"                      	=>  date('Y-m-d')
									
                                                );



             
           $whereClause = "Url='".putData( $_POST['Url'] )."' and Id != '".$_POST['id']."' ";
        if(matchExists(page, $whereClause) ) {
                  $errorMsg = "<div class='warning-message'>URL (".removeSlashesFrom($_POST['Url'] ).") already exists!</div>";
        } else {
               $whereClause = "Id = '".$_POST['id']."' ";
                updateData($updateArr, page, $whereClause);
                //$_SESSION['admin_user']['name'] = removeSlashesFrom( $_POST['username'] );
                $errorMsg = "<div class='notice-message'>Details updated successfully!</div> ";
                ###########generate message
        }

}


$qry = "select * from page where Id='".$_GET['id']."' " ;
$db->query( $qry );
if( $db->numRows() > 0 ) {
        $data = $db->fetchArray();
}
?>
 <table width="95%"  cellpadding="0" cellspacing="0"  border="0" >
  <tr><td valign="top" height="35" align="left" class="td_heading1">Edit Page Detail</td></tr>
  </table>
<form name="frmlogin" action="" method="post" onSubmit="return validate();">
<input type="hidden" name="id" value="<?php echo $data['Id'];?>">
<table width="95%" cellpadding="0" cellspacing="0"  border="0">
<?php if( $errorMsg ) {?>
<tr bgcolor="#FFFFFF" ><td  colspan="6" valign="top" align="center"><?php echo $errorMsg;?><br> </td></tr>
<?php } ?>
<tr><td>
          <table width="95%" cellpadding="4" cellspacing="1"  border="0" class="grid">
        <tr >
          <td height="15" colspan="2"  align="center" ><b>New Page Details</b> </td>
          </tr>
            <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Title<font color="red">*</font>&nbsp;</td>
          <td  align="left"> <input name="Title" type="text" class="textfield" id="Title" size="35" value="<?php echo $data['Title'] ;?>">         </td>
        </tr>
		
		<tr class="gridcell">
			<td  align="right" height="20" class="gridhead">Position<font color="red">*</font>&nbsp;</td>
          <td  align="left"><?php echo createComboBox($pos, "Position", $data['Position'], "");?> </td>
        </tr>

<tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Rank<font color="red">*</font>&nbsp;</td>
          <td  align="left"> <input name="Rank" type="text" class="textfield" id="Title" value="<?php echo $data['Rank'] ;?>">         </td>
        </tr>
		
  <tr class="gridcell" >

                    <td  align="center"  class="gridhead" colspan="2"> Page Details &nbsp;</td>
           </td>
        </tr >
<tr class="gridcell">

           <td colspan="4"> 
 <?php
$oFCKeditor = new FCKeditor('gseditor') ;
$oFCKeditor->BasePath =$websiteurl.'admin/fckeditor/';
$oFCKeditor->Height=450;
$oFCKeditor->Value = $data['Content'];
$oFCKeditor->Create() ;
?> 
         
          </td>
        </tr >
		
 		
  <tr class="gridcellalt">

                    <td  align="left" height="20" colspan="2"><strong>SEO Properties (Mod Re-write)</strong> &nbsp;</td>
              </tr >
			  
  <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Seo Title</font>&nbsp;</td>
          <td  align="left"> <input name="Link" type="text" size="35" class="textfield" id="Link"  value="<?php echo getData( $data['Link'] )?>" size="60" />         </td>
        </tr >
			  
  <tr class="gridcell">

                     <td  align="right" height="20" class="gridhead">Url &nbsp;</td>
          <td  align="left"><input name="Url" size="35" onblur="this.value=removeSpaces(this.value);" type="text" class="textfield"   value="<?php echo $data['Url'] ;?>" ><br>
         		 <font color="red">Url should be like(abc-xyz-123), Don't use space between 2 words</font>
																	</td>        						
          </tr >

   <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Description</font>&nbsp;</td>
          <td  align="left"> <input name="Description" type="text" class="textfield" id="Description"  value="<?php echo getData( $data['Description'] )?>" size="60">         </td>
        </tr >
 
        <tr class="gridcellalt">
             <td align="center" colspan="2">
            <input type="submit" name="submit" value="Update" class="button">&nbsp;&nbsp;

                        </td>
        </tr>
 
</table>
</td> </tr>
</table>

</form>

<script language="javascript" type="text/javascript">
function removeSpaces(string) {
 var tstring = "";
 string = '' + string;
 splitstring = string.split(" ");
 for(i = 0; i < splitstring.length; i++)
 tstring += splitstring[i];
 return tstring;
}
</script>

<script language="javascript">
function openwindow(path)
{
        artclasses = window.open (  path, "artclasses", " location = 1, resizable = yes, status = 1, scrollbars = 1, width = 500, height=300 ");
        artclasses.moveTo (200,100);
}
</script>
<SCRIPT language=JavaScript>
        function validate(form) {

     if (form.Name.value.length < 2) {
                        alert('Please enter Name.');
                        return false;
                }
 if (form.LastName.value.length < 2) {
                        alert('Please enter LastName.');
                        return false;
                }

    var myString = form.Email.value;
                if ((myString.indexOf(".") < 2) || (myString.indexOf("@") < 0) || (myString.lastIndexOf(".") < myString.indexOf("@"))) {
                        alert('Please enter a valid Email address.');
                        return false;
                }
     if (form.Telephone.value.length < 2) {
                        alert('Please enter Telephone.');
                        return false;
                }
 if (form.CurrentProperty.value.length < 2) {
                        alert('Please enter Current no.of Property.');
                        return false;
                }

 if (form.PropertyType.value.length < 2) {
                        alert('Please enter PropertyType.');
                        return false;
                }

 if (form.Bedroom.value.length < 2) {
                        alert('Please enter No.of Bedroom');
                        return false;
                }

 if (form.Area.value.length < 2) {
                        alert('Please enter Area.');
                        return false;
                }
 if (form.OtherInfo.value.length < 2) {
                        alert('Please enter OtherInfo.');
                        return false;
                }
 if (form.ReadyWithin.value.length < 2) {
                        alert('Please enter Ready to buy.');
                        return false;
                }
 if (form.ContactBy.value.length < 2) {
                        alert('Please enter ContactBy.');
                        return false;
                }


              return true;
        }
//-->
</SCRIPT>

</html>
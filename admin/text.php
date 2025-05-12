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
include_once("fckeditor/fckeditor.php");

$errorMsg = "";

if( $_REQUEST['submit'] == "Update"  ) {

        $updateArr = array(
                           				 "Title"                    =>  $_POST['Title'] ,
										 "Description"                    =>  $_POST['gseditor'] ,
										  "SeoDescription"                    =>  $_POST['SeoDescription'] ,
										"SeoTitle"                    =>  $_POST['SeoTitle'] 
                                          
                                                );

                $whereClause = "Id ='".$_GET['id']."' ";
          updateData($updateArr, text ,$whereClause);

                $errorMsg = "<div class='notice-message'>Details updated successfully!</div> ";

        ###########generate message
 }
 $qry = " select * from text where Id='".$_GET['id']."'" ;
$db->query($qry);
         $data = $db->fetchArray();
  ?>

 <table width="95%"  cellpadding="0" cellspacing="0"  border="0" >
  <tr><td valign="top" height="35" align="left" class="td_heading1">Manage Page Content</td></tr>
  </table>
<form name="frmlogin" action="" method="post" onSubmit="return validate();">
 
<table width="95%" cellpadding="0" cellspacing="0"  border="0">
<?php if( $errorMsg ) {?>
<tr bgcolor="#FFFFFF" ><td  colspan="6" valign="top" align="center"><?php echo $errorMsg;?><br> </td></tr>
<?php } ?>
<tr><td>
          <table width="95%" cellpadding="4" cellspacing="1"  border="0" class="grid">
        <tr >
          <td height="15" align="center" colspan="2" ><b>Content Area</b> </td>
          </tr>

       <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Title &nbsp;</td>
          <td  align="left"><input name="Title" type="text" class="textfield"  size="60" value="<?php echo $data['Title'] ;?>" >
          </td>
        </tr >
		 
		 <tr class="grid">
		<td height="15" align="left" class="gridhead" colspan="2" ><b>Description : </b> </td></tr>
          <tr class="grid"><td  align="center" colspan="2" >
		   <?php
$oFCKeditor = new FCKeditor('gseditor') ;
$oFCKeditor->BasePath =$websiteurl.'admin/fckeditor/';
$oFCKeditor->Height=450;
$oFCKeditor->Value = $data['Description'];
$oFCKeditor->Create() ;
?> 
          <script language="javascript1.2">
  generate_wysiwyg('Description');
  </script>
          </td>
        </tr >
		

<tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Meta Title &nbsp;</td>
          <td  align="left"><input name="SeoTitle" type="text" class="textfield"  size="60" value="<?php echo $data['SeoTitle'] ;?>" >
          </td>
        </tr >
<tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Meta Description &nbsp;</td>
          <td  align="left"><textarea name="SeoDescription" cols="46" rows="2" ><?php echo $data['SeoDescription'] ;?></textarea>
          </td>
        </tr >
        <tr class="gridcellalt">
          <td align="center" colspan="2" >
            <input type="submit" name="submit" value="Update" class="button2">&nbsp;&nbsp;

                        </td>
        </tr>
      </table>
</td> </tr>
</table>

</form>
<script language="javascript" type="text/javascript">
function validate(){
        if(trim( document.frmlogin.Url.value ) == ""){
                alert("Url can not be blank.");
                document.frmlogin.Url.focus();
                return false;
        }
        if( trim( document.frmlogin.Title.value ) == ""){
                alert("Title can not be blank.");
                document.frmlogin.Title.focus();
                return false;
        }
        if( trim( document.frmlogin.Description.value ) == ""){
                alert("Description can not be blank.");
                document.frmlogin.Description.focus();
                return false;
				}
}
</script>
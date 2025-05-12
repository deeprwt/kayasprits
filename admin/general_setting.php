<?php

##########################################################
#####     CREATED BY   : rajeshkumar.maurya@gmail.com    ######
#####          CREATION DATE: PUT DATE              ######
#####     CODE BRIEFING: PUT THE PAGE FUNCTIONALITY ######
#####                                               ######
#####     COMPANY           : baratotravels     ######
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
                           							"Title"                     =>  $_POST['Title'] ,
													"Detail"              		=>  $_POST['Detail'] ,
													"SeoDesc"                  	=>  $_POST['SeoDesc'] ,
												    "SeoTitle"                  =>  $_POST['SeoTitle'] ,
                                                  	"Email"                	    =>  $_POST['Email']
													  
                                                );

                $whereClause = "Id ='1'";
          updateData($updateArr, general_setting, $whereClause);

                $errorMsg = "<div class='notice-message'>Details updated successfully!</div> ";

        ###########generate message
 }
 $qry = " select * from general_setting where Id='1'" ;
$db->query($qry);
         $data = $db->fetchArray();
  ?>

 <table width="100%"  cellpadding="0" cellspacing="0"  border="0" >
  <tr><td valign="top" height="35" align="left" class="td_heading1">Manage Home Page</td></tr>
  </table>
<form name="frmlogin" action="" method="post" onSubmit="return validate();">
 
<table width="100%" cellpadding="0" cellspacing="0"  border="0">
<?php if( $errorMsg ) {?>
<tr bgcolor="#FFFFFF" ><td  colspan="6" valign="top" align="center"><?php echo $errorMsg;?><br> </td></tr>
<?php } ?>
<tr><td>
          <table width="100%" cellpadding="4" cellspacing="1"  border="0" class="grid">
       

       <tr class="gridcell">
                    <td  align="right" height="20" class="gridhead">Title &nbsp;</td>
          <td  align="left"><input name="Title" type="text" class="textfield"  size="60" value="<?php echo $data['Title'] ;?>" >
          </td>
        </tr>
		
		<tr class="gridcell">
                    <td  align="right" height="20" class="gridhead">Contact Email&nbsp;</td>
          <td  align="left"><input name="Email" type="text" class="textfield"  size="60" value="<?php echo $data['Email'] ;?>" >
          </td>
        </tr >
		 
		 <tr class="grid">
		<td height="15" align="right"><b>Home Text : </b> </td></tr>
          <tr class="grid"><td  align="center" colspan="2" >
		   <?php
$oFCKeditor = new FCKeditor('Detail') ;
$oFCKeditor->BasePath =$websiteurl.'admin/fckeditor/';
$oFCKeditor->Height=450;
$oFCKeditor->Value = $data['Detail'];
$oFCKeditor->Create() ;
?> 
          </td>
        </tr >
 	 <tr class="gridcellalt">

                    <td  align="left" height="20" colspan="2"><strong>SEO Properties </strong> &nbsp;</td>
              </tr >
<tr class="gridcell">


                    <td  align="right" height="20" class="gridhead">MetaTitle &nbsp;</td>
          <td  align="left"><input name="SeoTitle" type="text" class="textfield"  size="60" value="<?php echo getData( $data['SeoTitle'] )?>" >
          </td>
        </tr >
 

<tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">MetaDescription &nbsp;</td>
          <td  align="left"><input name="SeoDesc" type="text" class="textfield"  size="60" value="<?php echo getData( $data['SeoDesc'] )?>" >
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
</script>
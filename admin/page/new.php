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
///////////////Drop Down
$makeArr = array();
$makeArr = getValuesArr(page, "Id", "Link", "---Top Level---", "");


$errorMsg = "";

if( $_REQUEST['submit'] == "Submit"  ) {


        $insertArr = array(

									 "Title"                        =>  putData( $_POST['Title'] ),
                                     "Detail"                   	=>  putData( $_POST['gseditor'] ),
									 "Url"                  		=>  putData( $_POST['Url'] ),
                                     "SeoDesc"                  	=>  putData( $_POST['SeoDesc'] ),
									 "SeoTitle"                     =>  putData( $_POST['SeoTitle'] ),
                                     "Date"                      	=>  date('Y-m-d')
                                     );


        
           $whereClause = " Url='".putData( $_POST['Url'] )."' ";
        if(matchExists(page, $whereClause) ) {
                  $errorMsg = "<div class='warning-message'>URL (".removeSlashesFrom( $_POST['Url'] ).") already exists. Please re-enter details!</div>";
        } else {
          $last_insert_id =  insertData($insertArr, page);
              $errorMsg = "<div align=center class=notice-message>New Page has been added successfully!</div>";
			  //print_r($insertArr);
            redirect("admin_setting.php?module=page&msg=$errorMsg");
  ###########generate message
        }
        }?>

 <table width="95%"  cellpadding="0" cellspacing="0"  border="0" >
  <tr><td valign="top" height="35" align="left" class="td_heading1">Add New Page</td></tr>
  </table>
<form name="frmlogin" action="" method="post" enctype="multipart/form-data" onSubmit="return validate();">
<table width="95%" cellpadding="0" cellspacing="0"  border="0">
<?php if( $errorMsg ) {?>
<tr bgcolor="#FFFFFF" ><td  colspan="6" valign="top" align="center"><?php echo $errorMsg;?><br> </td></tr>
<?php } ?>
<tr><td> <table width="95%" cellpadding="4" cellspacing="1"  border="0" class="grid">
        <tr >
          <td height="15" align="center" colspan="2" ><b>New Page Details</b> </td>
          </tr>
 

          <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Title<font color="red">*</font>&nbsp;</td>
          <td  align="left"> <input name="Title" type="text" class="textfield" id="Title" size="35">         </td>
        </tr >
		
		
		
	<!-- <tr class="gridcell">
			<td  align="right" height="20" class="gridhead">Position<font color="red">*</font>&nbsp;</td>
          <td  align="left"> Common(Header & Footer) <input type="radio" name="Position" checked="checked" value="0" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Footer <input type="radio" name="Position" value="1" /></td>
        </tr> -->

	 
  <tr class="gridcell" >

                    <td  align="left"  class="gridhead" colspan="2"> Page Details &nbsp;</td>
           </td>
        </tr >
<tr class="gridcell">

           <td colspan="4"> 
<?php
$oFCKeditor = new FCKeditor('gseditor') ;
$oFCKeditor->BasePath =$websiteurl.'admin/fckeditor/';
$oFCKeditor->Height=450;
$oFCKeditor->Value = $data['Detail'];
$oFCKeditor->Create() ;
?> 
   

          </td>
        </tr >
 
	    <tr class="gridcellalt">

                    <td  align="left" height="20" colspan="2"><strong>SEO Properties (Mod Re-write)</strong> &nbsp;</td>
              </tr >
			  
			  <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Seo Title</font>&nbsp;</td>
          <td  align="left"> <input name="SeoTitle"type="text" class="textfield"  size="35">         </td>
        </tr >
			  
  <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Url &nbsp;</td>
          <td  align="left"><input name="Url" onblur="this.value=removeSpaces(this.value);" type="text" class="textfield" size="35" value="" ><br>
          <font color="red">Url should be like(abc-xyz-123), Don't use space between 2 words</font>

          </td>
        </tr >
 
 <tr class="gridcell">

                    <td  align="right" height="20" class="gridhead">Description</font>&nbsp;</td>
          <td  align="left"> <input name="SeoDesc" type="text" class="textfield"  size="60">         </td>
        </tr >

 


         <tr class="gridcellalt">
             <td align="center" colspan="2">
            <input type="submit" name="submit" value="Submit" class="button2">&nbsp;&nbsp;

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

<script language="javascript" type="text/javascript">
function validate(){

        if( trim( document.frmlogin.Title.value ) == 0 ){
                alert("Please select Title ");
                document.frmlogin.Title.value = "";
                document.frmlogin.Title.focus();
                return false;
        }

        if( trim( document.frmlogin.Name.value ) == ""){
                alert(" Name can not be blank.");
                document.frmlogin.Name.value = "";
                document.frmlogin.Name.focus();
                return false;
        }

           if( trim( document.frmlogin.LastName.value ) == ""){
                alert("Last Name can not be blank.");
                document.frmlogin.LastName.value = "";
                document.frmlogin.LastName.focus();
                return false;
        }
            var myString = form.Email.value;
                if ((myString.indexOf(".") < 2) || (myString.indexOf("@") < 0) || (myString.lastIndexOf(".") < myString.indexOf("@"))) {
                        alert('Please enter a valid Email address.');
                        return false;
                }
if( trim( document.frmlogin.Telephone.value ) == ""){
                alert("Telephone can not be blank.");
                document.frmlogin.Telephone.value = "";
                document.frmlogin.Telephone.focus();
                return false;
        }


        if( trim( document.frmlogin.CurrentProperty.value ) == 0 ){
                alert("Please select Current Property No.");
                document.frmlogin.CurrentProperty.value = "";
                document.frmlogin.CurrentProperty.focus();
                return false;
        }
if( trim( document.frmlogin.PropertyType.value ) == 0){
                alert("Property Type can not be blank.");
                document.frmlogin.PropertyType.value = "";
                document.frmlogin.PropertyType.focus();
                return false;
        }
           if( trim( document.frmlogin.Bedroom.value ) == 0){
                alert("No.of Bedroom  can not be blank.");
                document.frmlogin.Bedroom.value = "";
                document.frmlogin.Bedroom.focus();
                return false;
        }
           if( trim( document.frmlogin.Area.value ) == 0){
                alert("Area can not be blank.");
                document.frmlogin.Area.value = "";
                document.frmlogin.Area.focus();
                return false;
        }
           if( trim( document.frmlogin.ReadyWithin.value ) == 0){
                alert("Ready to buy can not be blank.");
                document.frmlogin.ReadyWithin.value = "";
                document.frmlogin.ReadyWithin.focus();
                return false;
        }
         if( trim( document.frmlogin.ContactBy.value ) == 0){
                alert("Contact you can not be blank.");
                document.frmlogin.ContactBy.value = "";
                document.frmlogin.ContactBy.focus();
                return false;
        }
                  return true;
}
</script>

</html>
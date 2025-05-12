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
?>
<html>
<head><title><?php include_once("includes/admintitle.php");?>: Administrative Section</title>
<script language="JavaScript" type="text/javascript" src="html.js"></script>
<link href="css/stylesheet.css" rel="stylesheet" type="text/css">
<script language="javascript" src="../javascript/validation.js"></script>
</head>
<body >
<table width="100%" border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td><?php  include_once("includes/header.php"); ?></td>
  </tr>
  <tr>
    <td valign="top">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-border">
        <tr>
          <td valign="top" id="loginform"><?php  include_once("includes/menu.php"); ?></td>
                  <td width="80%" valign="top" align="center" style="padding-top:40px">
                  <table width="90%" height="150" border="0" cellpadding="0" cellspacing="0">
          <tr>
          <td valign="top" align="center"><?php
                 if ( $_GET['module'] == "new" )
          include_once("admin_setting/new.php");
                  elseif ( $_GET['module'] == "Admin" )
          include_once("admin_setting/listing.php");
                  elseif ( $_GET['module'] == "edit" )
          include_once("admin_setting/edit.php");
 //--------------------------------Text
                                 elseif ( $_GET['module'] == "text" )
          include_once("text.php");
                 
//--------------------------------Category
                          elseif ( $_GET['module'] == "customer" )
        
          include_once("customer/listing.php");
          elseif ( $_GET['module'] == "customeredit" )
          include_once("customer/edit.php");
   
///---------------------------Product
          elseif ( $_GET['module'] == "payment" )
          include_once("payment/listing.php"); 
         
///---------------------------Product
                                 elseif ( $_GET['module'] == "productnew" )
            include_once("product/new.php");
                  elseif ( $_GET['module'] == "product" )
          include_once("product/listing.php");
                  elseif ( $_GET['module'] == "productedit" )
          include_once("product/edit.php");
		  
		  
///---------------------------Page Management
	               elseif ( $_GET['module'] == "About_Us" )
          include_once("page/aboutus.php");
                   elseif ( $_GET['module'] == "Contact_Us" )
          include_once("page/edit.php");
		           elseif ( $_GET['module'] == "Catalogue" )
          include_once("page/catalogue.php");
		  		   elseif ( $_GET['module'] == "Certification" )
          include_once("page/edit.php");


//--------------------------------Sub catgeory
                                 elseif ( $_GET['module'] == "subcatnew" )
          include_once("subcat/new.php");
                  elseif ( $_GET['module'] == "subcat" )
          include_once("subcat/listing.php");
                  elseif ( $_GET['module'] == "subcatedit" )
          include_once("subcat/edit.php");

//----------------------------------------general_setting
elseif ( $_GET['module'] == "editmail" )
          include_once("editmail.php");
 				//----------------------------------------general_setting
elseif ( $_GET['module'] == "general_setting" )
          include_once("general_setting.php");
//--------------------------------Pages
                                 elseif ( $_GET['module'] == "pagenew" )
          include_once("page/new.php");
                  elseif ( $_GET['module'] == "page" )
          include_once("page/listing.php");
                  elseif ( $_GET['module'] == "pageedit" )
          include_once("page/edit.php");
//---------------------------------News

                  elseif ( $_GET['module'] == "news_new" )
          include_once("news/new.php");
                  elseif ( $_GET['module'] == "news" )
          include_once("news/listing.php");
                  elseif ( $_GET['module'] == "news_edit" )
          include_once("news/edit.php");
//--------------------------------
else
          redirect("home.php");

                  ?></td>
          </tr>
         </table>
                 </td>
        </tr>
      </table>
</td>
  </tr>
</table>
<?php  include_once("includes/footer.php"); ?>
</body>
</html>
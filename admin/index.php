<?php
session_start();
include_once("../config.inc.php");
include_once("../functions/functions.php");
include_once("../functions/functions.inc.php");
include_once("../includes/db.inc.php");



if( $_POST['login']!="" ) {

        $errMsg = "";



        $qry = "select * from ".ADMIN. " where username='".putData( $_POST['username'] )."' and password='".putData( $_POST['password'] )."' and status ='1'" ;



        $db->query( $qry );
		  $db->numRows();
		
        if( $db->numRows() > 0 ) {

                $data = $db->fetchArray();

                $_SESSION['admin_user']['id'] = $data['id'];

				$_SESSION['admin_user']['type'] = $data['user_type'];

                $_SESSION['admin_user']['name'] = $data['username'];

                $_SESSION['admin_user']['email'] = $data['email'];

                redirect("inquiry.php"); #### PRESENT IN functions.inc.php

        }

        $errMsg = "<span class='error-message'>Invalid login. Try again!</span>";

}



?>

<html>

<head><title><?php include_once("includes/admintitle.php");?>: Administrative Section</title>

<link href="css/stylesheet.css" rel="stylesheet" type="text/css">

</head>



<body id="loginpage" >



<table width="100%" border="0" cellpadding="0" cellspacing="0" style=" padding-top:50px;">

  <tr>

     <td height="100" align="center"><img src="<?php echo $websiteurl;?>/admin/dist/img/Logo.png"/></td>

  </tr>

  <?php if( $errMsg != "" ) { ?>

<tr ><td  valign="top" align="center"><?php echo $errMsg ?></td></tr>

<?php } ?>

  <tr>

    <td><div align="center">

        <form name="frmlogin" action="" method="post" onSubmit="return validate();">



      <table width="438" height="150" cellpadding="0" cellspacing="0" id="loginform" border="0" >

        <tr >

          <td height="15" colspan="3"  align="center" ><b>Admin Panel</b> </td>

          </tr>

        <tr>

          <td width="164" align="right" height="30" class="text_label">Username:&nbsp;</td>

          <td width="265" align="left"><input name="username" type="text" class="textfield_admin" id="username">

          </td>

        </tr>

        <tr>

          <td align="right" height="20" class="text_label">Password:&nbsp;</td>

          <td align="left"><input name="password" type="password" class="textfield_admin" id="password">

          </td>

        </tr>

        <tr>

          <td height="30">&nbsp;</td>

          <td align="left" >

            <input type="submit" name="login" value="Submit" class="button">&nbsp;&nbsp;

                        <input type="reset" name="reset" value="Reset" class="button">

                        </td>

        </tr>

      </table></form>

    </div></td>

  </tr>

</table>

</body>

</html>

<script language="javascript" src="../javascript/validation.js"></script>

<script language="javascript" type="text/javascript">

function validate(){

        if(trim( document.frmlogin.username.value ) == ""){

                alert("User name can not be blank.");

                document.frmlogin.username.value = "";

                document.frmlogin.username.focus();

                return false;

        }

        if( trim( document.frmlogin.password.value ) == ""){

                alert("Password can not be blank.");

                document.frmlogin.password.value = "";

                document.frmlogin.password.focus();

                return false;

        }

        return true;

}

</script>
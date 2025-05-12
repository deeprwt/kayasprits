<?php
session_start();
$_SESSION['admin_user'] == "";
unset( $_SESSION );
session_destroy();
include_once("../functions/functions.php"); ### contains the redirect() function
redirect("index.php");
?>
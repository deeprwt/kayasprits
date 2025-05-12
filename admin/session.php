<?php
session_start();
######### ADMIN SESSION FILE. PLEASE INCLUDE THIS FILE ON THE TOP OF THE CODE IF NEEDED
include_once("../functions/functions.php"); ### contains the redirect() function
if( count( $_SESSION['admin_user'] ) <= 0) {
	redirect("index.php");
}
?>
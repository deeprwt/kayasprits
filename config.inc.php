<?php
##########################################################
#####     CREATED BY   : rajeshkumar.maurya@gmail.com    ######
#####          CREATION DATE: PUT DATE              ######
#####     CODE BRIEFING: PUT THE PAGE FUNCTIONALITY ######
#####                                              ######
#####     COMPANY           : mybusiinfo.com    ######
#####                                               ######
##########################################################
//session_start();

###  THIS FILE CONTAINS ALL POSSIBLE CONFIGURE SETTINGS OF THE SITE
### IN CASE OF SITE UPLOADING PLEASE MAKE THE CHANGES INTO THIS CONFIGURE FILE
### AND ONE CONNECTION FILE(db.inc.php) AVAILABLE INTO INCLUDES FOLDER. JUST CHANGE THE CONNECTION SETTINGS ONLY.
########

$SANwebsite = "Event";
$SANDeveloper = "ranadrivingschool.com.au/demo/";
######## NUMBER OF RECORDS PER PAGE ######
define("PAGING", 10);

####### FROM EMAIL AID THAT IS USED IN CASE OF SENDING OUT THE MAIL ###########


$websiteurl="https://ranadrivingschool.com.au/demo/";

// user full url with http://www.adnet.co.in/
// setting up the web root and server root for
// this shopping cart application
$thisFile = str_replace('\\', '/', __FILE__);
$docRoot = $_SERVER['DOCUMENT_ROOT'];

$GoogleMapKey = "ABQIAAAAnFBD5Aws_58OMAp1EIk57xRozwDkYkCXXH2rWb6NmXlOUkrudhSxV_VU6tOv5EVZv15mTSn4HPc47A";

######## TABLE DEFINITIONS #######
define("ADMIN", "admin");


########### smtp mail sending details
define("HOST_NAME", "localhost");
define("LOCALHOST", "localhost");
define("SMTP_USER", "");
define("SMTP_PWD", "");


###### used to avoid the error
error_reporting(0);

?>
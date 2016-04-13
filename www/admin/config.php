<?php
// If FreePBX is installed, use its database auth system by default
// To set up usernames and passwords 
define('USE_FREEPBX_AUTH',false);

// User/Pass to Log into FOP2Manager. If we detect a FreePBX session
// or a fop2 sessions with the "manager" permission, the authentication
// will be asumed as ok.

$ADMINUSER = "fop2admin";
$ADMINPWD  = "fop2admin";

// This database parameters are only needed if you are not using FreePBX, 
// PBX in a Flash, Elastix or Thirdlane 7
// If any of the above systems config files is found, connections details 
// on those config files will be used instead of what you set manually here

$DBHOST="localhost";
$DBUSER="ombutel";
$DBPASS="ombutel";
$DBNAME="fop2";
include_once("/etc/fop2/webadmin/admin_dbpass.php");


// This is the preference sqlite database for FOP2 User and Context Preferences
$SQLITEDB="/etc/fop2/fop2settings.db";


// If you have a PBX that cannot be auto detected, like MiRTA, specify the engine
// here. Otherwise leave this line commented. Available options: mirtapbx, custom
//
// $ENGINE="mirtapbx";
$ENGINE="ombutel";

// Branding Settings
$APPNAME          = "FOP2 Manager";
$LOGONAME         = "<span style='font-weight:bold; color:#000;'>FOP2</span> <span style='color:#4EB855'>Manager</span>";
$LOGO             = "images/fop2managerlogo.png";

// General Application Settings
$DEBUG            = 0;
$BUTTONS_PER_PAGE = 150;

<?php
require_once("config.php");
require_once("functions.php");
require_once("system.php");
require_once("dblib.php");
require_once("asmanager.php");
require_once("dbconn.php");
require_once("secure/secure-functions.php");

if(isset($_REQUEST['reload'])) {
    if($_REQUEST['reload']==1) {
        if(isLoggedIn()) {
            reload_asterisk();
        }
    }
}

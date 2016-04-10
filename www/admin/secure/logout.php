<?php
require_once("../functions.php");
require_once("secure-functions.php");
require_once("../system.php");
flushSession();
if(isset($_GET['redirect'])) {
    Header("Location: ".$_GET['redirect']);
}

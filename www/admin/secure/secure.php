<?php

if(isset($logout) && $logout==1) {
   flushSession();
   header("Location: ".SELF);
}

if(!isLoggedIn()) {
    include("login_form.php");
    exit;
}

<?php	 	

function isLoggedIn(){
    global $config_engine;

    if($config_engine=='freepbx' && USE_FREEPBX_AUTH==true) {
        if(isset($_SESSION['AMP_user'])) {
            return true;
        }
    }

    if(is_file("/etc/elastix.conf")) {
        if(isset($_SESSION['elastix_user']) && USE_FREEPBX_AUTH==true) {
            return true;
        }
    }

    if(isset($_SESSION[MYAP]['AUTHVAR']["loggedIn"]) && $_SESSION[MYAP]['AUTHVAR']["loggedIn"] == 1 ){
        return true;
    } else {
        return false;
    }
}

function checkPass($login, $password) {

    global $db, $config_engine, $conf, $ADMINUSER, $ADMINPWD;
   
    if($config_engine=='freepbx' && USE_FREEPBX_AUTH==true) {

        $query="SELECT username AS login,IF(sections='*','admin','user') AS level,username AS name FROM ".$conf['DBNAME'].".ampusers WHERE username='%s' AND password_sha1=sha1('%s')";

        $res=$db->consulta($query,Array($login,$password));

        if($db->num_rows($res)==1) {
            $row = $db->fetch_assoc($result);
            $row['ok']=1;
        } else {
            $row['error']=__('Invalid Credentials');
        }

    } else { 
        if($login==$ADMINUSER && $password==$ADMINPWD) {
            $row['ok']=1;
        } else {
            $row['error']=__('Invalid Credentials');
        }
    }
    return $row;
} 

function initSession($row) {
    $_SESSION[MYAP]['AUTHVAR']["loggedIn"] = true;
    $_SESSION[MYAP]['AUTHVAR']["ip"] = $_SERVER['REMOTE_ADDR'];
}

function flushSession() {
    unset($_SESSION[MYAP]["AUTHVAR"]);
    unset($_COOKIE['context']);
    setcookie("context", "0", time()-3600);
    return true;
}


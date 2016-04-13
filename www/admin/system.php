<?php
require_once('gettextinc.php');

$version="1.1.1";

set_error_handler("fop2manager_error",E_ALL);

$langs = array();
$langs['en_US']="English";
$langs['es_ES']="Español";
$langs['fr_FR']="Français";
$langs['da_DK']="Dansk";
$langs['el_GR']="Ελληνικά";

if(isset($_SERVER['PATH_INFO'])) {
    define("SELF",  substr($_SERVER['PHP_SELF'], 0, (strlen($_SERVER['PHP_SELF']) - @strlen($_SERVER['PATH_INFO']))));
} else {
    define("SELF",  $_SERVER['PHP_SELF']);
}

define('PROJECT_DIR', realpath('./'));
define('LOCALE_DIR', PROJECT_DIR .'/i18n');
define('DEFAULT_LOCALE', 'en_US');
define("MYAP", "FOP2Manager");

$encoding = 'UTF-8';

$locale = (isset($_COOKIE['lang']))? $_COOKIE['lang'] : DEFAULT_LOCALE;

$panelcontextdefault='';
$panelcontext = (isset($_COOKIE['context']))? $_COOKIE['context'] : $panelcontextdefault;
$panelcontext = intval($panelcontext);

T_setlocale(LC_MESSAGES, $locale);
$domain = 'fop2manager';
_bindtextdomain($domain, LOCALE_DIR);
// bind_textdomain_codeset is supported only in PHP 4.2.0+
_bind_textdomain_codeset($domain, $encoding);
_textdomain($domain);

$predefined_groups = array(
    array('id' => -1, 'name' =>"All Buttons"),
    array('id' => -2, 'name' =>"All Extensions"),
    array('id' => -3, 'name' =>"All Queues"),
    array('id' => -4, 'name' =>"All Conferences"),
    array('id' => -5, 'name' =>"All Trunks")
);

$conf = array();
$conf['DBHOST'] = $DBHOST;
$conf['DBUSER'] = $DBUSER;
$conf['DBPASS'] = $DBPASS;
$conf['DBNAME'] = $DBNAME;


if(is_file("/var/thirdlane_load/pbxportal-ast.sysconfig")) {
    $config_engine = 'thirdlane_db';
} else
if(is_file("/etc/freepbx/freepbx.conf")) {
    $config_engine = 'freepbx';
} else
if(is_file("/etc/amportal.conf")) {
    $config_engine = 'freepbx_old';
} else
if(is_file("/etc/asterisk/users.txt")) {
    $config_engine = 'thirdlane_old';
} else
if(is_file("/etc/pbxware/pbxware.ini")) {
    $config_engine = 'pbxware';
} else
if(is_file("/etc/kamailio/kamailio-mhomed-elastix.cfg")) {
    $config_engine = 'elastix_mt';
} else {
    $config_engine = "custom";
}

if(isset($ENGINE)) {
    $config_engine = $ENGINE;
}

if
  ($config_engine=='thirdlane_db') {
      require_once("functions-thirdlane.php");
      $conf = parse_conf("/var/thirdlane_load/pbxportal-ast.sysconfig");
} else if
  ($config_engine=='thirdlane_old') {
      require_once("functions-thirdlane.php");
} else if
  ($config_engine=='freepbx') {
      require_once("functions-freepbx.php");
      $conf = parse_conf("/etc/freepbx/freepbx.conf");
} else if
  ($config_engine=='freepbx_old') {
      require_once("functions-freepbx.php");
      $conf = parse_conf("/etc/amportal.conf");
      $config_engine = "freepbx";
} else if
  ($config_engine=='pbxware') {
      require_once("functions-astdb.php");
      $conf = parse_conf("/etc/pbxware/pbxware.ini");
      $conf['DBHOST'] = $conf['pw_mysql_host'];
      $conf['DBUSER'] = $conf['pw_mysql_username'];
      $conf['DBPASS'] = $conf['pw_mysql_password'];
      $conf['DBNAME'] = 'pbxware';
      $conf['fop2port']=4445;   // as pbxware runs chroot it cannot read fop2.cfg to take manager data
} else if
  ($config_engine=='elastix_mt') {
      require_once("functions-elastix.php");
      $conf = parse_conf("/etc/elastix.conf");
      $conf['DBHOST'] = 'localhost';
      $conf['DBUSER'] = 'root';
      $conf['DBPASS'] = $conf['mysqlrootpwd'];
      $conf['DBNAME'] = 'elxpbx';
} else if
  ($config_engine=='mirtapbx') {
      require_once("functions-mirta.php");
} else if
  ($config_engine=='custom') {
      require_once("functions-custom.php");
} else if($config_engine=='ombutel') {
      require_once("functions-ombutel.php");
} else {
      require_once("functions-astdb.php");
}

//if(is_file("/usr/local/fop2/fop2.cfg") || is_file("/etc/asterisk/fop2/fop2.cfg")) {
//   if(is_file("/usr/local/fop2/fop2.cfg")) {
if(is_file("/etc/asterisk/fop2.cfg") || is_file("/etc/asterisk/fop2/fop2.cfg")) {
   if(is_file("/etc/asterisk/fop2.cfg")) {
       $fop2conf = parse_conf("/etc/asterisk/fop2.cfg");
//        $fop2conf = parse_conf("/usr/local/fop2/fop2.cfg");
   }
   if(is_file("/etc/asterisk/fop2/fop2.cfg")) {
       $fop2conf = parse_conf("/etc/asterisk/fop2/fop2.cfg");
   }
   if(isset($fop2conf['listen_port'])) {
       $conf['fop2port']=$fop2conf['listen_port'];
   } else {
       $conf['fop2port']=4445;
   }
   if(isset($fop2conf['manager_port'])) {
       $conf['MGRPORT']=$fop2conf['manager_port'];
   } else {
       $conf['MGRPORT']=5038;
   }
   if(isset($fop2conf['manager_user'])) {
       $conf['MGRUSER']=$fop2conf['manager_user'];
   } else {
       $conf['MGRUSER']=5038;
   }
   if(isset($fop2conf['manager_host'])) {
       $conf['MGRHOST']=$fop2conf['manager_host'];
   } else {
       $conf['MGRHOST']='127.0.0.1';
   }
   if(isset($fop2conf['manager_secret'])) {
       $conf['MGRPASS']=$fop2conf['manager_secret'];
   } else {
       $conf['MGRPASS']=5038;
   }
}

if(!isset($conf['fop2port'])) { $conf['fop2port']='4445'; }

// Number of records per page in users/groups/templates/permissions tables
$perpage=13;

// If this is Elastix, use correct session name
if(is_file("/etc/elastix.conf")) {
    if($config_engine=='freepbx' && USE_FREEPBX_AUTH==true) {
        session_name("elastixSession");
        // Set password to be the same as admin in elastix
        $file = file('/etc/elastix.conf');
        foreach ($file as $line) {
            if (preg_match("/^\s*([\w]+)\s*=\s*\"?([\w\/\:\.\*\%!-]*)\"?\s*([;#].*)?/",$line,$matches)) {
                if($matches[1]=='amiadminpwd') {  $ADMINPWD = $matches[2]; };
            }
        }
    }
}

session_start();

if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
    header('X-UA-Compatible: IE=edge,chrome=1');
}

$extramenu=array();

if (!function_exists('json_encode')) {
    function json_encode($content) {
        require_once 'JSON.php';
        $json = new Services_JSON;
        return $json->encode($content);
    }
    function json_decode($content) {
        require_once 'JSON.php';
        $json = new Services_JSON;
        return $json->decode($content);
    }
}

header("Content-type: text/html; charset=$encoding");


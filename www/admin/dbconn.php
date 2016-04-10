<?php

$db     = new dbcon($conf['DBHOST'], $conf['DBUSER'], $conf['DBPASS'], $conf['DBNAME'], true);
$query  = "SET NAMES utf8";
$res    = $db->consulta($query);

$astman = new AsteriskManager();

switch ($config_engine) {
    case "thidlane_db":
        break;
    case "freepbx":
        set_freepbx_active_modules();
        break;
    case "astdb":
        break;
    default:
        break;
}



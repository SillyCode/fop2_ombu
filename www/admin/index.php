<?php
require_once("config.php");
require_once("functions.php");
require_once("system.php");
require_once("dblib.php");
require_once("asmanager.php");
require_once("http.php");
require_once("secure/secure-functions.php");
require_once("secure/secure.php");

include("headerbs.php");

echo "<div class='wrap'>\n";

require_once("dbconn.php");

include("menu.php");

echo "<div class='content'>\n";

$fop2_mirror   = getFastestMirror();

// Check online version
$fop2managerxml = uHttp::sendHttpRequest("http://".$fop2_mirror."/plugins/fop2manager.xml", 5);
$versiononline  = value_in('version',$fop2managerxml);
$changelog      = value_in('changelog',$fop2managerxml);
$rawname        = value_in('rawname',$fop2managerxml);

if($versiononline=='') {
    $versiononline = $version;
    $rawname       = "fop2manager";
    $changelog     = "";
    $adminfile     = $rawname."-".$versiononline;
} else {
    $changelog     = preg_replace("/\n/","<br/>",$changelog);
    $adminfile     = $rawname."-".$versiononline;
}

$versioncompare='';
$version_partes = preg_split("/\./",$version);
foreach($version_partes as $part) {
    $versioncompare.=sprintf("%02d",$part);
}

$versiononlinecompare='';
$versiononline_partes = preg_split("/\./",$versiononline);
foreach($versiononline_partes as $part) {
    $versiononlinecompare.=sprintf("%02d",$part);
}

// Routines to setup database tables and initial data
include("dbsetup.php");

echo "<h2>".__("Welcome")."</h2>";
echo "<p>";
echo __("The FOP2 Manager lets you configure users, permissions, button details and options. It will also let you install, uninstall and configure FOP2 plugins. It will read data from your configuration backend (FreePBX, Thirdlane, etc) and populate its own tables with your preferences.");
echo "</p>";
echo "<hr/>";

$licensedplugins=explode(',',plugin_get_licensed());

list ($fop2version,$fop2registername,$fop2licensetype) = fop2_get_version();
$_SESSION[MYAP]['fop2version']=$fop2version;

echo "<div class='row'>\n";

// Check for new versions
if($versiononlinecompare > $versioncompare) {
    echo "<div class='col-md-12'>\n";
    echo "<div class='panel panel-warning'>\n";
    echo "<div class='panel-heading'>\n";
    echo "<h3 class='panel-title'>\n";
    echo __("Upgrade Available");
    echo "</h3>\n";
    echo "</div>\n";
    echo "<div class='panel-body'>\n";
    echo "<a class='ttip' href='#' id='chlogbtn' onclick='togglechangelog()' data-toggle='popover' data-trigger='hover' data-placement='bottom' data-content='";
    echo __('Show Changelog');
    echo "'><span class='glyphicon glyphicon-collapse-down' id='chlogicon'></span></a>&nbsp;";
    echo sprintf(__('New %s version %s available for download'),$APPNAME,$versiononline);
    echo " ";
    echo sprintf(__('(Your current version is %s)'),$version);
    echo "<button class='btn btn-warning pull-right' id='upgradeBtn' >".__('Upgrade')."</button>";
    echo "<div id='changelog' style='display:none;'>";
    echo "<hr/>\n";
    echo __('Changelog');
    echo $changelog;
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
    echo "</div>\n";
}

echo "<div class='col-md-6'>\n";
// This is to check if FOP2 Server is running
if($licensedplugins[0] == '0') {
    echo "<div class='alert alert-danger'>";
    echo __("FOP2 Server is not responding. Be sure the service is running!");
    echo "</div>\n";
} else {
    echo "<div class='alert alert-success'>";
    echo "<strong>".__("FOP2 Server Status: OK");
    if($fop2version<>'') {
        echo " - ".__("Version").": $fop2version";
    }
    echo "</strong>";
    echo "</div>\n";
}
echo "</div>";

// This shows the backend engine
echo "<div class='col-md-6'>\n";
echo "<div class='alert alert-success'><strong>";
echo sprintf( __('Backend Engine: %s'), $config_engine );
echo "</strong></div>\n";
echo "</div>";
echo "</div>";

if($licensedplugins[0] <> '0') {

    // FOP2 Server is running, list licensed plugins if any

    $plugin_online = plugin_get_online($fop2_mirror);

    if (isset($plugin_online)) {
	    foreach ($plugin_online as $idx=>$arrdata) {
            $plugin_name[$arrdata['rawname']]=$arrdata['name'];
        }
    }

    if($licensedplugins[0]<>'') {
        echo "<div class='panel panel-success'>\n";
        echo "<div class='panel-heading'><h3 class='panel-title'>\n";
        echo __("Licensed Plugins");
        echo "</h3></div>";
        echo "<div class='panel-body'>";
        $allplugins = array();
        foreach($licensedplugins as $rawname) {
            $nameprint = isset($plugin_name[$rawname])?$plugin_name[$rawname]:$rawname;
            $allplugins[]=$nameprint;
        }
        $nameprint = implode(", ",$allplugins);
        echo $nameprint."<br>";
        echo "</div>";
        echo "</div>";
    } else {
        echo  "<div class='alert alert-warning'>";
        echo __("There are no licensed plugins");
        echo "</div>\n";
    }

}

if($db->is_connected()) {
    // Information on Home

    if($panelcontext<>'') {
        $where = " WHERE context_id='$panelcontext' ";
        $whererc = " AND context_id='$panelcontext' ";
    } else {
        $where = '';
        $whererc = '';
    }

    fop2_recreate_default_groups($predefined_groups,$panelcontext,$whererc);

    echo "<div class='row'>\n";

    echo "<div class='col-md-3'>\n";
    $res = $db->consulta("SELECT count(*) FROM fop2users $where");
    $row = $db->fetch_row($res);
    $cont = $row[0];
    echo "<div class='panel panel-info'>\n";
    echo "<div class='panel-heading'><h3 class='panel-title'>".__('Users')."</h3></div>\n";;
    echo "<div class='panel-body'><h2 class='text-center'>";
    echo $cont;
    echo "</h2></div>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-3'>\n";
    $res = $db->consulta("SELECT count(*) FROM fop2buttons $where");
    $row = $db->fetch_row($res);
    $cont = $row[0];
    echo "<div class='panel panel-info'>\n";
    echo "<div class='panel-heading'><h3 class='panel-title'>".__('Buttons')."</h3></div>\n";;
    echo "<div class='panel-body'><h2 class='text-center'>";
    echo $cont;
    echo "</h2></div>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-3'>\n";
    $res = $db->consulta("SELECT count(*) FROM fop2templates $where");
    $row = $db->fetch_row($res);
    $cont = $row[0];
    echo "<div class='panel panel-info'>\n";
    echo "<div class='panel-heading'><h3 class='panel-title'>".__('Templates')."</h3></div>\n";;
    echo "<div class='panel-body'><h2 class='text-center'>";
    echo $cont;
    echo "</h2></div>";
    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-3'>\n";
    $res = $db->consulta("SELECT count(*) FROM fop2groups $where");
    $row = $db->fetch_row($res);
    $cont = $row[0];
    echo "<div class='panel panel-info'>\n";
    echo "<div class='panel-heading'><h3 class='panel-title'>".__('Groups')."</h3></div>\n";;
    echo "<div class='panel-body'><h2 class='text-center'>";
    echo $cont;
    echo "</h2></div>";
    echo "</div>";
    echo "</div>";

    echo "</div>\n";
}
?>

</div>

<div id="upgradeModal" class="modal fade" aria-hidden="true" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3><?php echo __('Please wait...'); ?></h3>
      </div>
      <div class="modal-body">
        <iframe id='modaliframe' src="#" style="width:99.6%; height:200px; border:0;"></iframe>
      </div>
      <div class="modal-footer">
          <button type="button" id='modalclose' class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
      </div>
    </div>
  </div>
</div>
<div class="push"></div>
</div>
<script>
$('#modalclose').hide();
frameSrc='downloadfile.php?file=<?php echo $adminfile;?>';

$('#upgradeModal').on('show.bs.modal', function () {
    $('iframe').attr("src",frameSrc);
});

$('#upgradeModal').on('hidden.bs.modal', function () {
    window.location.reload();
});

$('#upgradeBtn').click(function(){
   $('#upgradeModal').modal('show');
});

function togglechangelog() {
    $('#changelog').toggle();
    if($('#changelog').is(':visible')) {
        $('#chlogicon').removeClass('glyphicon-collapse-down').addClass('glyphicon-collapse-up');;
        $('#chlogbtn').attr('data-content','<?php echo __('Hide Changelog');?>');
    } else {
        $('#chlogicon').removeClass('glyphicon-collapse-up').addClass('glyphicon-collapse-down');;
        $('#chlogbtn').attr('data-content','<?php echo __('Show Changelog');?>');
    }
}

</script>

<?php

include("footerbs.php");

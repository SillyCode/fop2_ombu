<?php
$xml      = simplexml_load_file('module.xml');
$xmlarray = simpleXMLToArray($xml);
$menu     = $xmlarray['menu']['item'];
$rootdir  = dirname(SELF);
$contextname = array();
$contextfullname = array();

if(!isset($_SESSION[MYAP]['fop2version'])) {
    list ($fop2version,$fop2registername,$fop2licensetype) = fop2_get_version();
    $_SESSION[MYAP]['fop2version']=$fop2version;
}

$fop2version = $_SESSION[MYAP]['fop2version'];
$fop2version   = normalize_version($fop2version);
$fop2version   = intval($fop2version);

$cuantoscontexts=1;
$panelcontextdefault='';
if(isset($db)) {
    $res = $db->consulta("DESC fop2contexts");
    if($res) {

        $panel_contexts = fop2_populate_contexts();

        $results = $db->consulta("SELECT * FROM fop2contexts");
        $cuantoscontexts = $db->num_rows($results);

        $results = $db->consulta("SELECT * FROM fop2contexts WHERE exclude=0 ORDER BY context");
        $cont=0;
        while ($re = $db->fetch_assoc($results)) {
            $contextname[$re['id']]=$re['context'];
            $contextfullname[$re['id']]=$re['name'];
            if($cont==0) { $panelcontextdefault = $re['id']; }
            $cont++;
        }
        $db->seek($results,0);
    }
}

$panelcontext = (isset($_COOKIE['context']))? $_COOKIE['context'] : $panelcontextdefault;
$panelcontext = intval($panelcontext);


$extramenu['pagebs.fop2buttons.php']['Actions']['Sort by Number']='?action=sortnumber';
$extramenu['pagebs.fop2buttons.php']['Actions']['Sort by Name']='?action=sortname';
$extramenu['pagebs.fop2users.php']['Actions']['Recreate Users']='?action=create';
$extramenu['pagebs.fop2buttons.php']['Actions']['Mass Update']='onclick=\'$("#uploadcontainer").modal(); return false;\'';

if($config_engine=='freepbx') {
    // Sync Labels only available in FreePBX for now
    $extramenu['pagebs.fop2buttons.php']['Actions']['Synchronize Labels']='?action=refresh';
}

if (!isset($_COOKIE['lang'])) {
    $_COOKIE['lang'] = 'en_US';
} else {
    setcookie('lang', $_COOKIE['lang'], time()+365*24*60*60);
}

?>
<!-- Fixed navbar -->
<div class='navbar navbar-default navbar-fixed-top' role='navigation' id='fop2navbar'>
  <div class='container-fluid'>
    <div class='navbar-header'>
      <button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>
        <span class='sr-only'>Toggle navigation</span>
        <span class='icon-bar'></span>
        <span class='icon-bar'></span>
        <span class='icon-bar'></span>
      </button>
<?php
if(isLoggedIn()) {
?>
      <div class='navbar-brand'>
        <img src='<?php echo $LOGO;?>' style='border:0; margin-left:-10px; margin-top:-10px;' class='pull-left' alt='logo'/>&nbsp;<a href='<?php echo $rootdir;?>'><div class='pull-right'><?php echo $LOGONAME;?></div></a>
      </div>
<?php } ?>
    </div>
    <div class='navbar-collapse collapse'>
      <ul class='nav navbar-nav'>

<?php

// Adds settings menu if FOP2 is version 2.30.00 or higher
if($fop2version>=23000) {
    $menu[] = array('name'=>__('Settings'),'action'=>'pagebs.fop2settings.php');
}

if($cuantoscontexts>1) {
    $menu[] = array('name'=>__('Tenants'),'action'=>'pagebs.fop2contexts.php');
}

if(isLoggedIn()) {

    foreach($menu as $idx=>$arrdata) {
        if(!isset($arrdata['menu'])) {
            // Simple Action
            if(preg_match("/{$arrdata['action']}/",SELF)) { $active=' class=\'active\' '; } else { $active=''; }
            echo "<li $active><a href='".$arrdata['action']."'>".__($arrdata['name'])."</a></li>\n";
        } else {
            // Drop Down
    
            // If there is only one submenu item, make it a deeper array
            if(!isset($arrdata['menu']['item'][0])) {
                $temparray = $arrdata['menu']['item'];
                unset($arrdata['menu']['item']);
                $arrdata['menu']['item'][0] = $temparray;
            }
    
            $menuname = $arrdata['name'];

            echo "  <li class='dropdown'>\n";
            echo "    <a href='#' class='dropdown-toggle' data-toggle='dropdown'>".__($arrdata['name'])."<b class='caret'></b></a>\n";
            echo "    <ul class='dropdown-menu animated flipInX'>\n";

            if(isset($extramenu[basename(SELF)])) {
                if(count($extramenu[basename(SELF)][$arrdata['name']]>0)) {
                    foreach($extramenu[basename(SELF)][$arrdata['name']] as $name=>$link) {

                        if(substr($link,0,7)=="onclick") {
                            $href="href='#' ".$link;
                        } else {
                            $href="href='".$link."'";
                        }

                        echo "<li><a $href>".__($name)."</a></li>\n";
                    }
                    echo "<li class='divider'></li>\n";
                }
            }

            foreach($arrdata['menu']['item'] as $subidx=>$subarrdata) {

                if(substr($subarrdata['action'],0,7)=="onclick") {
                    $href="href='#' ".$subarrdata['action'];
                } else {
                    $href="href='".$subarrdata['action']."'";
                }

                if(preg_match("/{$subarrdata['action']}/",SELF)) { $active=' class=\'active\' '; } else { $active=''; }

                echo "<li $active><a $href>".__($subarrdata['name'])."</a></li>\n";
            }

            echo "</ul>\n</li>\n";
        }
    }
}

?>

</ul>
<ul class='nav navbar-nav navbar-right'>

<?php

if(isloggedin()) {
    if(count($contextname)>0) {
        $selectedtenant = ($panelcontext<>'')?$contextfullname[$panelcontext]:__('tenant');

        echo "<li class='dropdown'>\n";
        echo "<a href='#' class='dropdown-toggle' data-toggle='dropdown'>".$selectedtenant."<b class='caret'></b></a>\n";
        echo "<ul class='dropdown-menu animated flipInX scrollable-menu'>\n";

        foreach($contextname as $ctx_id=>$ctx_name) {
            $fullname = $contextfullname[$ctx_id];
            echo '<li>';
            echo '<a href="#" onclick="setContext(\''.$ctx_id.'\'); return false;">';
            if($panelcontext==$ctx_id) {
                echo "<i class='glyphicon glyphicon-ok' style='float: right; margin-top: 2px; margin-right: -6px;'></i> ";
            }
            echo $fullname;
            echo "</a>";
            echo "</li>\n";
        }
        echo "</ul>\n";
        echo "</li>\n";
    }
}

?>

  <li class='dropdown'>
    <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='glyphicon glyphicon-globe'></i> <?php echo __('Language');?> <b class='caret'></b></a>
    <ul class='dropdown-menu animated flipInX'>
<?php
foreach($langs as $iso=>$langname) {
    echo '<li>';
    echo '<a href="#" onclick="setLang(\''.trim($iso).'\'); return false;">';
    echo "<img src='images/blank.gif'class='flag flag-$iso' alt='$langname'>\n";
    if($_COOKIE['lang']==$iso) {
        echo "<i class='glyphicon glyphicon-ok' style='float: right; margin-top: 2px; margin-right: -6px;'></i> ";
    }
    echo $langname;
    echo "</a>";
    echo "</li>\n";
}
?>
    </ul>
  </li>
<?php
if(isLoggedIn()) {
    if($config_engine=='freepbx') {
        if(!isset($_SESSION['AMP_user']) && !isset($_SESSION['elastix_user'])) {
            echo "<li><a href='secure/logout.php?redirect=$rootdir'><i class='glyphicon glyphicon-log-out'></i> ".__('Logout')."</a></li>\n";
        } else {
            if(USE_FREEPBX_AUTH==false) {
                echo "<li><a href='secure/logout.php?redirect=$rootdir'><i class='glyphicon glyphicon-log-out'></i> ".__('Logout')."</a></li>\n";
            }
        }
    } else {
        echo "<li><a href='secure/logout.php?redirect=$rootdir'><i class='glyphicon glyphicon-log-out'></i> ".__('Logout')."</a></li>\n";
    }
}
?>

</ul>

    </div><!--/.nav-collapse -->
  </div>
</div>
<div class='container-fluid'>

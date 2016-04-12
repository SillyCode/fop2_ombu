<?php
require_once("config.php");
require_once("functions.php");
require_once("system.php");
require_once("dblib.php");
require_once("asmanager.php");
require_once("dbconn.php");
require_once("secure/secure-functions.php");
require_once("secure/secure.php");
include("headerbs.php");

echo "<div class='wrap'>\n";
include("menu.php");

if(!isset($panelcontext)) { $panelcontext=0; }
if(!isset($contextname[$panelcontext])) {
    $contextname[$panelcontext]='GENERAL';
}
$mycontext = $contextname[$panelcontext];
$mycontext = strtoupper($mycontext);

$action  = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$itemid  = isset($_REQUEST['itemid'])?$_REQUEST['itemid']:'';
$numpage = isset($_REQUEST['numpage'])?$_REQUEST['numpage']:'1';
$numpage = intval($numpage);
$error   = 0;

$helptext = Array();
$helptext['desktopNotify']="To enable or disable desktop notifications (notification boxes that popup outside the browser, so you can see them with the browser minimized). This does not work with all browsers.";
$helptext['disablePresenceOther']="To enable or disable the 'Other' option on the FOP2 Presence selection, that lets users chose the reason for their state besides the predefined ones.";
$helptext['disableQueueFilter']="When you click on a queue button, the extension display will hide all extensions but the ones matching their names with its queue members. If this is enabled, then queue filtering won't be active.";
$helptext['disableVoicemail']='Lets you control if you want to enable or disable the Voicemail Explorer feature. Requires a voicemail license to be effective.';
$helptext['disableWebSocket']='Lets you disable the use of HTML5 Websocket as the communications protocol. If disable FOP2 will use Adobe Flash instead.';
$helptext['dynamicLineDisplay']='When enabled, extension buttons won\'t show any inactive lines, making them shorter. But also the display will be irregular as some buttons will be taller than others depending on the number of active lines.';
$helptext['enableDragTransfer']='This setting lets you control if you want drag&amp;drop transfers to be enabled in FOP2.';
$helptext['hideUnregistered']='When enabled, this option will hide all extension buttons that are lagged or unregistered.';
$helptext['noExtenInLabel']='If you do not want to show the extension number in a button label, then enable this setting.';
$helptext['soundChat']='To control if you want sounds for chat events.';
$helptext['soundQueue']='To control if you want sounds for queue events.';
$helptext['soundRing']='To enable or disable the RING sound when your extension receives a call.';
$helptext['startNotRegistered']='With this setting, all extension buttons will start in unregistered state (greyed out).';
$helptext['warnClose']='To control if you want a warning when the FOP2 page is about to be closed.';
$helptext['warnHangup']='To control if you want a confirmation when a hangup action is performed from FOP2.';
$helptext['displayQueue']='Mode in which you want queue buttons to be displayed by default. The summary view will show only the number of logged in agents and number of waiting calls, while the full view will show every queue member and all waiting calls in detail.';
$helptext['language']='Main Language';
$helptext['logoutUrl']='URL to use when the logout button is clicked in the FOP2 UI.';
$helptext['pdateFormat']='Date format to use on Chat events.';
$helptext['voicemailFormat']='Format in which voicemail files are stored, this must be the same as configured in /etc/asterisk/voicemail.conf';
$helptext['notifyDuration']='Duration in seconds you want notifications to be displayed.';
$helptext['showLines']='Number of lines to show per button.';
$helptext['dialPrefix']='Dial prefix to use on click to call from phonebook or when using the dial box.';
$helptext['consoleDebug']='Enable javascript debugging on the browser.';

$type = Array();

$type['consoleDebug']='bool';
$type['desktopNotify']='bool';
$type['disablePresenceOther']='bool';
$type['disableQueueFilter']='bool';
$type['disableVoicemail']='bool';
$type['disableWebSocket']='bool';
$type['dynamicLineDisplay']='bool';
$type['enableDragTransfer']='bool';
$type['hideUnregistered']='bool';
$type['noExtenInLabel']='bool';
$type['soundChat']='bool';
$type['soundQueue']='bool';
$type['soundRing']='bool';
$type['startNotRegistered']='bool';
$type['warnClose']='bool';
$type['warnHangup']='bool';

$type['displayQueue']='enum';
$type['language']='enum';
$type['logoutUrl']='text';
$type['pdateFormat']='text';
$type['voicemailFormat']='text';
$type['notifyDuration']='integer';
$type['showLines']='integer';
$type['dialPrefix']='text';

$enum = Array();

$enum['displayQueue']["'min'"]=__('Summary');
$enum['displayQueue']["'max'"]=__('Full');

$enum['language']["'ca'"]    = 'Català';
$enum['language']["'cr'"]    = 'Hrvatski';
$enum['language']["'da'"]    = 'Dansk';
$enum['language']["'de'"]    = 'Deutsch';
$enum['language']["'he'"]    = 'עברית';
$enum['language']["'el'"]    = 'Ελληνικά';
$enum['language']["'en'"]    = 'English';
$enum['language']["'es'"]    = 'Español';
$enum['language']["'fr_FR'"] = 'Francais';
$enum['language']["'hu'"]    = 'Magyar';
$enum['language']["'it'"]    = 'Italiano';
$enum['language']["'nl'"]    = 'Dutch';
$enum['language']["'pl'"]    = 'Polski';
$enum['language']["'pt_BR'"] = 'Português';
$enum['language']["'ru'"]    = 'Русский';
$enum['language']["'se'"]    = 'Svenska';
$enum['language']["'tr'"]    = 'Türkçe';
$enum['language']["'zh'"]    = '简体中文';



switch ($action) {
    case "save":
        $valkey = 'val'.$itemid;
        if($type[$itemid]=='bool') {
            if(isset($_POST[$valkey])) {
                $saveval = 'true';
            } else {
                $saveval = 'false';;
            }
        } else if($type[$itemid]=='enum') {
            $saveval = "'".$_POST[$valkey]."'";
        } else {
            $saveval = $_POST[$valkey];
            $saveval = preg_replace("/[^a-zA-Z0-9 ,:-]+\/&\./","",$saveval);
            $saveval = "'".$saveval."'";
        }
        $result = fop2_edit_setting($itemid,$saveval,$mycontext);
        if (!$result) { $error=1; }
        break;
}

$db2 = new dbcon("sqlite:$SQLITEDB");
$result = $db2->consulta("SELECT * FROM setup WHERE extension='SETTINGS' AND context='$mycontext'");

$settings = array();
while($row = $db2->fetch_assoc($result)) {
    $settings[$row['context']][$row['parameter']]=$row['value'];
}

?>

<div class='row' style='background-color:#78a300; padding-bottom:10px;'>
<div class="content">
<div class="col-md-8">
<span class='h2'><?php echo __('Settings');?></span>
<i style='vertical-align:super; top:-5px; color:#333;' class='ttip glyphicon glyphicon-info-sign' data-toggle='popover' data-trigger='hover' data-placement='bottom' data-content='<?php echo __("Settings let you change some default behaviour for the Switchboard, like enabling drag&amp;drop, webSocket, changing the number of lines to display per button, etc."); ?>'></i>
</div>
<div class='col-md-4 text-right'>
</div>
</div>
</div>

<div class='row'>

<!-- left side menu -->
<div class="col-md-3">
<table class='table table-striped table-hover' style='margin-top:20px;'>
<tbody id='tablesettings'>
<?php

$cont=0;
if (isset($settings[$mycontext])) {
    foreach ($settings[$mycontext] as $param=>$val) {
        $cont++;
        echo "<td id='td_".$param."' class='pointer clickable ".($itemid==$param ? 'open ':'')."' >{$param}</td>";
        echo "<td class='".($itemid==$param ? 'open ':'')."text-right'>";
        echo display_value_for($param,$val);
        echo "</td></tr>";
    }

$dif = $perpage - ($cont % $perpage);
if($dif == $perpage) { $dif=0; }
if($cont>0) { $span='colspan=2'; } else { $span=''; }
if($dif>0) {
   for($i=0;$i<$dif;$i++) {
       echo "<tr id='no_${i}no'><td $span>&nbsp;</td></tr>\n";
   }
}
}

if($itemid<>'') {

$valor = $settings[$mycontext][$itemid];
$valor = preg_replace("/^'/","",$valor);
$valor = preg_replace("/'$/","",$valor);
}

?>
</table>
<div class="text-center">
<ul class="pagination" id="myPager"></ul>
</div>
</div>

<div class='col-md-9'>

<form autocomplete="off" name="edit" action="<?php echo SELF; ?>" method="post" class="form-horizontal">
    <input type="hidden" name="action" id="faction" value="<?php echo ($itemid<>'' ? 'save' : '') ?>">
    <input type="hidden" name="itemid" id="fitemid" value="<?php echo $itemid; ?>">
    <input type="hidden" name="itemvalue" id="fitemvalue" value="<?php echo ($valor<>'' ? $valor : ''); ?>">
    <input type="hidden" id='fnumpage' name="numpage" value="<?php echo $numpage; ?>">

<div class='section-title-container'>
<?php
if($itemid<>'') {
?>
<h2 class='fhead' style='height: 55px; z-index: 1000;'><?php echo __('Edit Setting'); ?>
<div class="button-group pull-right">
<?php if($itemid<>'') { ?>
    <button type="submit" style="margin-bottom:40px;" class="btn btn-success" onclick="return edit_onsubmit();"><?php echo __('Submit Changes')?></button>
<?php  } ?>
</div>
</h2>
<?php } ?>
</div>

<div class='row'>
<div class="col-md-12">

<div class='fieldset'>
<?php
if($itemid<>'') {

  if(isset($helptext[$itemid])) {
  echo "<p>";
  echo __($helptext[$itemid]);
  echo "</p>";
  }

  echo "<div class='form-group'>";

  echo "<label for='item' class='col-sm-3 control-label'>".$itemid."</label>";
  echo "<div class='col-xs-8'>";
  if($type[$itemid]=="bool") {

      echo "<input type='checkbox' data-on-text='".__('Yes')."' data-off-text='".__('No')."' ";
      echo "data-off-color='danger' data-on-color='success' class='chk' ";
      if($valor=='true') { echo " checked "; }
      echo "name='val$itemid' id='val$itemid'>";

  } else if($type[$itemid]=="enum"){
      echo "<select class='chosen-select' name='val$itemid' id='val$itemid'>";
      foreach($enum[$itemid] as $key=>$val) {
          $key = preg_replace("/^'/","",$key);
          $key = preg_replace("/'$/","",$key);
          echo "<option value='$key' ";
          if($valor==$key) { echo " selected "; }
          echo ">$val</option>\n";
      }
  } else {
      echo "<input type='text' class='form-control' name='val$itemid' value='$valor'>";
  }
  echo "</div></div>";
}
?>
</div>
</div>
</div>

<hr/>

</form>


</div>
</div>


<script>
<!--

var theForm = document.edit;

function setSave(userid) {
     numpage = $('#myPager').find('li.active')[0].innerText;
     $('#faction').val('save');
     $('#fnumpage').val(numpage);
     theForm.submit();
}

function setEdit(userid) {
     numpage = $('#myPager').find('li.active')[0].innerText;
     debug('set edit '+userid);
     $('#faction').val('edit');
     $('#fitemid').val(userid);
     $('#fnumpage').val(numpage);
     theForm.submit();
}

function edit_onsubmit() {
    return true;
}

$(document).ready(function() {

  $(".chk").bootstrapSwitch();
  $('.chosen-select').chosen({disable_search: true, skip_no_results: true});

  $('#tablesettings').pageMe({pagerSelector:'#myPager',showPrevNext:true,hidePageNumbers:false,perPage:<?php echo $perpage;?>,numbersPerPage:4,curPage:<?php echo $numpage;?>});

<?php
if($action=='save' && $error==0) {
?>
    alertify.success('<?php echo __('Changes saved successfully');?>');
    $('#fop2reload').show();
    $('#fop2reload').twinkle( { "effect": "drops", "effectOptions": { color: "rgba(255,0,0,0.5)", radius: 200, duration: 2000, width: 2, count: 10, delay: 700 }});
<?php
} else {
if(isset($_SESSION[MYAP]['needsreload'])) {
?>
    $('#fop2reload').show();
    $('#fop2reload').twinkle( { "effect": "drops", "effectOptions": { color: "rgba(255,0,0,0.5)", radius: 200, duration: 2000, width: 2, count: 10, delay: 700 }});
<?php
}
}
?>
});
//-->
</script>

<div class="push"></div>
</div>
<?php

function display_value_for($itemid,$val) {
    global $type;
    global $enum;
    if($type[$itemid]=="bool") {
        $valprint=array();
        $ret = "<span class='label ";
        if($val=='true') { $ret.=" label-success"; } else { $ret.=" label-danger"; }
        $valprint['true']=__('Yes');
        $valprint['false']=__('No');
        $ret .="'>".$valprint[$val]."</span>";
    } else if ($type[$itemid]=="enum") {
        $ret = $enum[$itemid][$val];
    } else {
        $ret = $val;
        $ret = preg_replace("/^'/","",$ret);
        $ret = preg_replace("/'$/","",$ret);
    }
    return $ret;
}
include("footerbs.php");


</div>
<div class='footer'>
<?php echo $APPNAME;?> - <?php echo sprintf(__("Version %s"), $version);?>
</div>

<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-switch.min.js"></script>
<script src="js/bootstrap.file.input.js"></script>

<button type='button' class='btn btn-default ttip navbar-btn fixed-bottom-left' id='fop2reload' style='display:none;' data-toggle='popover' data-trigger='hover' data-placement='right' data-content='<?php echo __('Reload FOP2');?>' onclick='fop2Reload();'>
<span class='glyphicon glyphicon-cog spin'>
</span></button>

<?php
if(isset($before_end_body)) {
    echo $before_end_body;
}
?>
<script>
function fop2Reload() {
    var myhost = 'fop2reload.php';

     alertify.confirm('','<?php echo __('Are you sure?'); ?>', function() {
             $('#statusmodal').modal();
             $.post(myhost,'reload=1',function() {alertify.success(lang['FOP2 Reloaded']); $('#statusmodal').modal('hide'); $('#fop2reload').hide();});
             return true;
     }, function() {
        // cancel
     }).set({
        labels: {
            ok: '<?php echo __('Accept');?>',
            cancel: '<?php echo __('Cancel');?>'
        },
        closable: false
     });

}

function asteriskReload() {
    var myhost = 'asteriskreload.php';

    alertify.confirm('','<?php echo __('Are you sure?'); ?>', function() {
             $('#statusmodal').modal();
             $.post(myhost,'reload=1',function() {alertify.success(lang['Asterisk Reloaded']); $('#statusmodal').modal('hide'); $('#fop2reload').hide();});
             return true;
     }, function() { 
       // cancel 
     }).set({
        labels: {
            ok: '<?php echo __('Accept');?>',
            cancel: '<?php echo __('Cancel');?>'
        },
        closable: false
     });


    return false;
}

</script>
</body>
</html>

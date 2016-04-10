$AMI_Event_Handler{'configonreload'}{'RELOAD'} = sub {
    my @allreturn;
    my $script = defined($main::pluginconfig{'configonreload'}{'scriptReload'}{''})?$main::pluginconfig{'configonreload'}{'scriptReload'}{''}:'/usr/bin/php -f /var/www/html/fop2/admin/update_conf.php';
    my $final_script = "$script &";
    system($final_script);
    return @allreturn;
};

$AMI_Event_Handler{'configonreload'}{'USEREVENT'} = sub {
    my $event = shift;
    my @allreturn;
    my @keys =  keys %$event;

    my $userevent   = ${$event}{UserEvent};
    if ($userevent eq "Reload") {
        my $script = defined($main::pluginconfig{'configonreload'}{'scriptReload'}{''})?$main::pluginconfig{'configonreload'}{'scriptReload'}{''}:'/usr/bin/php -f /var/www/html/fop2/admin/update_conf.php';
        my $final_script = "$script &";
        system($final_script);
    }
    return @allreturn;
};

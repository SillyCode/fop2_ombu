$Client_Post_Command_Handler{'conferencebutton'}{'customconference'} = sub { 
    my @allreturn = ();
    my $origen   = shift;
    my $destino  = shift;
    my $contexto = shift;
    my $socket   = shift;
    my $mychannel = main::get_btn_config( "$contexto", $origen, 'MAINCHANNEL');
    my $extension_to_dial = $destino;

    if ( !main::hasPermChannel( $socket, "dial", $mychannel ) && !main::hasPerm( $socket, "all" ) ) {
        # No 'dial' permission, abort action
        print "No permissions for conference\n";
        return @allreturn;
    }

    my $return  = "Action: Originate\r\n";
    $return .= "Channel: Local/$extension_to_dial\@from-internal\r\n";
    $return .= "Application: ChanSpy\r\n";
    $return .= "Data: $mychannel,BEq\r\n";
    $return .= "\r\n";
    push @allreturn, $return;
    return @allreturn;
};

$Client_Pre_Command_Handler{'conferencebutton'}{'ping'} = sub {
    print "pong pong conferencebutton!\n";
};

$AMI_Event_Handler{'conferencebutton'}{'QUEUEMEMBER'} = sub {
    my $event = shift;

    #my @keys =  keys %$event;
    #foreach my $key (@keys) {
    #    print "$key = ".${$event}{$key}."\n";
    #}
    #print "\n";
};


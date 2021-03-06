# The perl part of a plugin will execute commands on the server side.
# As such, this commands will apply to any user, regardless if the plugin
# is assigned to any user (plugin assignment only affects the client side
# part, or .js files)

# AMI_Event_Handler lets you intercept AMI events and add your own actions/code
# The first parameter is a hash with the key=>value paris as received
#
# The function should return an array with valid AMI cmmands/actions to send
# If you want to see the possible AMI events, you can start fop2_server in debug
# level 1.
#
$AMI_Event_Handler{'sampleplugin'}{'HANGUP'} = sub {
    my $event = shift;
    my @allreturn;

    # Retrieve config data as set in the plugin ini file
    my $var1 = $main::pluginconfig{'sampleplugin'}{'sampleConfig'}{''};
    my $var2 = $main::pluginconfig{'sampleplugin'}{'sampleConfig'}{'samplesection'};

    # This will print out the complete manager event as received
    my @keys =  keys %$event;
    foreach my $key (@keys) {
        print "$key = ".${$event}{$key}."\n";
    }
    print "\n";

    # We return an array containing valid manager Actions
    $return  = "Action: DBPut\r\n";
    $return .= "Family: SOMETHING\r\n";
    $return .= "Key: TO\r\n";
    $return .= "Val: WRITE\r\n";
    $return .= "\r\n";
    push @allreturn, $return;

    return @allreturn;
};

# Client_Pre_Command_Handler is called when an action is received from
# a FOP2 Client (from the browser), like changing the presence, initiating
# a transfer, etc. You also can return an AMI command to be sent. 
# The Pre handler will issue your commands BEFORE the standard FOP2
# response. The Post handler will issue the commands AFTER the standard
# FOP2 response.
#
# The first key is the name of the plugin, the second the action received
# from a FOP2 client. You can catch here custom actions defined in the .js
# part of a plugin too.
#
# A good way to see the commands that can be recieved from the client is
# to start fop2_server in debug level 4: /usr/local/fop2/fop2_server -X 4
#
$Client_Pre_Command_Handler{'sampleplugin'}{'ping'} = sub {
    print "pong pong!\n";
    my $pluginconf   = defined($main::pluginconfig{'sampleplugin'}{'sampleconfig'}{'section'})?$main::pluginconfig{'sampleplugin'}{'sampleconfig'}{'section'}:$main::pluginconfig{'sampleplugin'}{'sampleconfig'}{''};
    my @allreturn;
    $return  = "Action: DBDel\r\n";
    $return .= "Family: SOMETHING\r\n";
    $return .= "Key: TO\r\n";
    $return .= "\r\n";
    push @allreturn, $return;
    return @allreturn;
};

# sampleaction is a custom action sent from the js part of the plugin

$Client_Post_Command_Handler{'sampleplugin'}{'sampleaction'} = sub {
    my @allreturn;
    $return  = "Action: DBDel\r\n";
    $return .= "Family: SOMETHING\r\n";
    $return .= "Key: TO\r\n";
    $return .= "\r\n";
    push @allreturn, $return;
    return @allreturn;
};


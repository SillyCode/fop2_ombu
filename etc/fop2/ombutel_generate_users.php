#!/usr/bin/php

<?php

$all_buttons = array();

//EXTENSIONS
$extensions = json_decode(file_get_contents('http://localhost/api/extensions'));
foreach($extensions->data as $extension) {
        $all_buttons['buttons'][] = "SIP/{$extension->extension}";
}

$queues = json_decode(file_get_contents('http://localhost/api/queues'));
foreach($queues->data as $queue) {
        $all_buttons['queues'][] = "QUEUE/{$queue->extension}";
}

foreach($all_buttons as $group_name => $members) {
        $group = implode(',', $all_buttons[$group_name]);
        $group_name = ucfirst($group_name);
        echo "group=All {$group_name}:{$group}\n";
}

//EXTENSIONS
$extensions = json_decode(file_get_contents('http://localhost/api/extensions'));
foreach($extensions->data as $extension) {
	echo "user={$extension->extension}:{$extension->extension}:all:All Buttons,All Trunks,All Conferences,All Queues,clock\n";
}
echo "buttonfile=ombutel_autobuttons.cfg";

?>

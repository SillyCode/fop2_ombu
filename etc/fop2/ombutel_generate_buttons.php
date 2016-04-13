#!/usr/bin/php

<?php

//EXTENSIONS
$extensions = json_decode(file_get_contents('http://localhost/api/extensions'));
foreach($extensions->data as $extension) {
	echo "[SIP/{$extension->extension}]\n";
	echo "type=extension\n";
	echo "extension={$extension->extension}\n";
	echo "context=cos-all\n"; //TODO: need to change this
	echo "label={$extension->name}\n";
	echo "mailbox={$extension->mailbox}\n";
// 	extenvoicemail=*621@from-internal
// 	external=5554444@from-internal
	echo "privacy=monitor\n";
	echo "customastdb=CF/{$extension->extension}\n";
	echo "\n";
}


$queues = json_decode(file_get_contents('http://localhost/api/queues'));
foreach($queues->data as $queue) {
	echo "[QUEUE/{$queue->extension}]\n";
	echo "type=queue\n";
	echo "extension={$queue->extension}\n";
	echo "label={$queue->description}\n";
	echo "context=ext-queue\n";
	echo "queuecontext=cos-all\n";
	echo "rtmp=0\n";
	echo "\n";
}

?>




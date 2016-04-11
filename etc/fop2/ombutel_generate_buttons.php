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

?>




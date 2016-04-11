#!/usr/bin/php

<?php

//EXTENSIONS
$extensions = json_decode(file_get_contents('http://localhost/api/extensions'));
foreach($extensions->data as $extension) {
	echo "user={$extension->extension}:{$extension->extension}:all\n";
}

?>

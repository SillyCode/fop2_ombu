<?php

function ombutel_populate_contexts_from_tenants($exten) {
	$tenants = json_decode(file_get_contents('http://localhost/api/tenants'));
	$contexts = array();
	foreach($tenants->data as $tenant) {
		$contexts[$tenant->tenant_id] = $$tenant->name;
	}
	return $contexts;
}

function ombutel_check_extension_usage() {
	$extenlist = array();

	//EXTENSIONS
	$extensions = json_decode(file_get_contents('http://localhost/api/extensions'));
	foreach($extensions->data as $extension) {
		$data = array();
		$data['type']    = 'extension';
		$data['context'] = 'from-internal';
		$data['name'] = $extension->name;
		$data['channel'] = $extension->extension;
		$data['mailbox'] = $extension->mailbox;
		$data['exten']   = $extension->extension;
		$data['email']   = $extension->email;
		$data['accountcode'] = $extension->accountcode;
		$data['context_id'] = 1;
		$extenlist[$data['channel']] = $data;
	}

	//VOICEMAIL FOR EXTENSION

	//QUEUES
	//TRUNKS
	//CONFERENCES
	//RING GROUPS
	return $extenlist;
}

?>

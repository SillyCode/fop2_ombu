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
		$data['type']    = "extension";
		$data['context'] = "";
		$data['name'] = $extension->name;
		$data['channel'] = $extension->extension;
		$data['mailbox'] = $extension->mailbox;
		$data['exten']   = $extension->extension;
		$data['email']   = $extension->email;
		$data['accountcode'] = $extension->accountcode;
		$data['context_id'] = 1; //Current tenant
		$extenlist[$data['channel']] = $data;
	}

	//QUEUES
	$queues = json_decode(file_get_contents('http://localhost/api/queues'));
	foreach($queues->data as $queue) {
		$data = array();
		$data['name'] = $queue->description;
		$data['channel'] = "QUEUE/{$queue->extension}";
		$data['context'] = "";
		$data['type'] = "queue";
		$data['exten']   = $queue->extension;
		$data['context_id'] = 1; // Current tenant
		$extenlist[$data['channel']]  = $data;
	}

	//TRUNKS
	$trunks = json_decode(file_get_contents('http://localhost/api/trunks'));
	foreach($trunks->data as $trunk) {
		$data = array();
		$data['name']    = "{$trunk->technology}/{$trunk->outgoing_username}";
		$data['channel'] = "{$trunk->technology}/{$trunk->trunk_id}";
		$data['type']    = "trunk";
		$data['exten']   = "{$trunk->group_mode}_{$trunk->trunk_id}";
		$data['context'] = "";
		$data['context_id'] = 1;
		$extenlist[$data['channel']] = $data;
	}

	//CONFERENCES
	$conferences = json_decode(file_get_contents('http://localhost/api/conferences'));
	foreach($conferences->data as $conference) {
		$data = array();
		$data['name'] = $conference->description;
		$data['channel'] = "CONFERENCE/$conference->extension";
		$data['context'] = "";
		$data['type']    = "conference";
		$data['exten']   = $conference->extension;
		$data['context_id'] = 1;
		$extenlist[$data['channel']]  = $data;
	}

	//RING GROUPS
	$ring_groups = json_decode(file_get_contents('http://localhost/api/ring_groups'));
	foreach($ring_groups->data as $ring_group) {
		$data = array();
		$data['name'] = $ring_group->description;
		$data['channel'] = "RINGGROUP/{$ring_group->extension}";
		$data['type']    = "ringgroup";
		$data['context'] = "";
		$data['exten']   = $ring_group->extension;
		$data['context_id'] = 1;
		$extenlist[$data['channel']]  = $data;
	}

	//PARKING LOT
	$parking_lots = json_decode(file_get_contents('http://localhost/api/parking_lots'));
	foreach($parking_lots->data as $parking_lot) {
		$data = array();
		$data['name']   = "Default";
		$data['channel'] = "PARK/default";
		$data['type']    = "park";
		$data['exten']   = $parking_lot->extension;
		$data['context'] = "parkedcalls";
		$data['context_id'] = 1;
		$extenlist[$data['channel']]  = $data;
	}

	//CONTACTS

	return $extenlist;
}

?>

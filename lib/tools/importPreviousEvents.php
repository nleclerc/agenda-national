<?php

require __DIR__.'/../common.php';
require __DIR__.'/OldCalendarDBConnector.php';

puts('Importing events from previous database');


$oldDb = new OldCalendarDbConnector();
$newDb = new CalendarDbConnector();


$events = $oldDb->listEvents();

foreach ($events as $event) {
	puts ('** '.json_encode($event));
	$event['region_id'] = 'IDF';
	$event['location'] = 'Paris, France';
	$newDb->importEvent($event);
}

$participations = $oldDb->findParticipations();

foreach ($participations as $part) {
	puts("## ".$part['event_id'].'::'.$part['member_id']);
	$newDb->addParticipant($part['event_id'], $part['member_id']);
}
<?php

namespace My\Events;

include './../../../wp-load.php';

$invitees = Model::getInviteesByUser(1);

$events = [];

foreach ($invitees as $invitee) {
    $event_id = get_field('event', $invitee, false);
    $events[] = $event_id;
}

$calendar = ICal::createCalendar($events);

// 4. Set HTTP headers
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="cal.ics"');

// 5. Output
echo $calendar->get();

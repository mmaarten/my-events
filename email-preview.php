<?php

namespace My\Events;

use My\Events\Posts\Event;

if (! defined('ABSPATH')) {
    require_once './../../../wp-load.php';
}

$event_id = isset($_GET['event']) ? $_GET['event'] : 0;
$message = isset($_GET['message']) ? $_GET['message'] : '';
$message = wpautop(esc_html(stripcslashes(base64_decode($message))));

if (! $event_id || get_post_type($event_id) !== 'event') {
    esc_html_e('Invalid event.', 'my-events');
    return;
}

$event = new Event($event_id);

$message = Helpers::loadTemplate('emails/event-send-email', [
    'event'   => $event,
    'message' => $message,
], true);

$args = apply_filters('wp_mail', [
    'to'          => '',
    'subject'     => '',
    'message'     => $message,
    'headers'     => '',
    'attachments' => '',
]);

echo $args['message'];

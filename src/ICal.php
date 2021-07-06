<?php

namespace My\Events;

use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\Classification;
use Spatie\IcalendarGenerator\Enums\EventStatus;

class ICal
{
    public static function init()
    {
        add_action('acf/save_post', function ($post_id) {
            if (get_post_type($post_id) == 'event') {
                $calendar = self::createCalendar($post_id);
                file_put_contents(self::getEventFile($post_id), $calendar->get());
            }
        });

        add_action('template_redirect', [__CLASS__, 'maybeOutputUserCalendar']);
    }

    public static function getEventFile($event_id, $url = false)
    {
        $upload_dir = wp_get_upload_dir();

        return $upload_dir[$url ? 'baseurl' : 'basedir'] . '/calendar-' . get_post_field('post_name', $event_id) . '.ics';
    }

    protected static function createEvent($post_id, $user_id = 0)
    {
        $post = new \My\Events\Posts\Event($post_id);

        $timezone = new \DateTimeZone(wp_timezone_string());

        $event = Event::create();
        $event->name($post->post_title);
        $event->description(Helpers::unautop($post->getDescription()));
        $event->uniqueIdentifier("my-events-{$post->ID}");
        $event->createdAt(new \DateTime($post->post_date, $timezone));
        $event->startsAt(new \DateTime($post->getStartTime('Y-m-d H:i:s'), $timezone));
        $event->endsAt(new \DateTime($post->getEndTime('Y-m-d H:i:s'), $timezone));
        $event->url(get_permalink($post->ID));

        if ($post->isPrivate()) {
            $event->classification(Classification::private());
        } else {
            $event->classification(Classification::public());
        }

        if ($user_id) {
            $invitee = $post->getInviteeByUser($user_id);
            if ($invitee) {
                if ($invitee->getStatus() == 'pending') {
                    $event->status(EventStatus::tentative());
                }
                if ($invitee->getStatus() == 'accepted') {
                    $event->status(EventStatus::confirmed());
                }
                if ($invitee->getStatus() == 'declined') {
                    $event->status(EventStatus::cancelled());
                }
            }
        }

        return $event;
    }

    public static function getOutputUserCalendarFileURL()
    {
        global $wp;

        return add_query_arg([
            MY_EVENTS_NONCE_NAME => wp_create_nonce('user_ical_file'),
        ], home_url($wp->request));
    }

    public static function maybeOutputUserCalendar($user_id)
    {
        if (empty($_GET[MY_EVENTS_NONCE_NAME])) {
            return;
        }

        if (! wp_verify_nonce($_GET[MY_EVENTS_NONCE_NAME], 'user_ical_file')) {
            return;
        }

        if (! is_user_logged_in()) {
            wp_die(__('Invalid user', 'my-events'));
        }

        $user_id = get_current_user_id();

        $events = Model::getUserEvents($user_id, 'accepted', ['fields', 'ids']);

        if (! $events) {
            wp_die(__('No events found.', 'my-events'));
        }

        $calendar = self::createCalendar($events, $user_id);

        //

        header('Content-Description: File Transfer');
        header('Content-Type: text/calendar');
        header(
            sprintf('Content-Disposition: attachment; filename=%s.ics', sanitize_title(__('calendar', 'my-events')))
        );

        echo $calendar->get();

        exit;
    }

    public static function createCalendar($post_ids, $user_id = 0)
    {
        $calendar = Calendar::create(get_bloginfo('name'));

        $events = [];
        foreach ((array) $post_ids as $post_id) {
            if ($post_id && get_post_type($post_id) == 'event') {
                $events[] = self::createEvent($post_id, $user_id);
            }
        }

        $calendar->event($events);

        return $calendar;
    }
}

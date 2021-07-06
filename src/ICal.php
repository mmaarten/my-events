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
        $event->description($post->getDescription());
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

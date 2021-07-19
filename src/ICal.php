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
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('before_delete_post', [__CLASS__, 'deletePost']);

        add_action('template_redirect', [__CLASS__, 'maybeOutputUserCalendar']);
        add_filter('my_events/notification_args', [__CLASS__, 'notificationArgs'], 10, 2);

        add_shortcode('my-events-download-user-calendar-file-url', function () {

            if (! is_user_logged_in()) {
                return false;
            }

            $user_id = get_current_user_id();

            return self::getDownloadURL($user_id);
        });
    }

    public static function getFile($event_id, $url = false)
    {
        $upload_dir = wp_get_upload_dir();

        $base = trailingslashit($upload_dir[$url ? 'baseurl' : 'basedir']);

        return $base . sanitize_title(__('calendar', 'my-events')) . '/' . get_post_field('post_name', $event_id) . '.ics';
    }

    public static function savePost($post_id)
    {
        if (get_post_type($post_id) != 'event') {
            return;
        }

        self::createFile($post_id);
    }

    public static function deletePost($post_id)
    {
        if (get_post_type($post_id) != 'event') {
            return;
        }

        self::removeFile($post_id);
    }

    public static function createFile($event_id)
    {
        $calendar = self::createCalendar($event_id);

        return file_put_contents(self::getFile($post_id), $calendar->render());
    }

    public static function removeFile($event_id)
    {
        $file = self::getFile($event_id);

        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    public static function getDownloadURL($user_id = 0)
    {
        global $wp;

        return add_query_arg([
            MY_EVENTS_NONCE_NAME => wp_create_nonce('user_ical_file'),
            'user' => $user_id,
        ], home_url($wp->request));
    }

    protected static function createEvent($post_id, $user_id = 0)
    {
        $post = new \My\Events\Posts\Event($post_id);

        $created = new \DateTime($post->post_date);
        $start   = new \DateTime($post->getStartTime('Y-m-d H:i:s'));
        $end     = new \DateTime($post->getEndTime('Y-m-d H:i:s'));

        if (! $post->isAllDay()) {
            // Fix: 10:00 will be set to 12:00. So convert it back to 8:00 (UTC)
            $timezone_str = str_replace('+', '-', wp_timezone_string());
            $created->setTimeZone(new \DateTimeZone($timezone_str));
            $start->setTimeZone(new \DateTimeZone($timezone_str));
            $end->setTimeZone(new \DateTimeZone($timezone_str));
        } else {
            $end->modify('+1 day');
        }

        $event = Event::create();
        $event->name($post->post_title);
        $event->description(Helpers::unautop($post->getDescription()));
        $event->uniqueIdentifier("my-events-{$post->ID}");
        $event->createdAt($created);
        $event->startsAt($start);
        $event->endsAt($end);
        $event->url(get_permalink($post->ID));

        if ($post->isPrivate()) {
            $event->classification(Classification::private());
        } else {
            $event->classification(Classification::public());
        }

        if ($post->isAllDay()) {
            $event->fullDay();
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

    public static function maybeOutputUserCalendar()
    {
        if (empty($_GET[MY_EVENTS_NONCE_NAME])) {
            return;
        }

        if (! wp_verify_nonce($_GET[MY_EVENTS_NONCE_NAME], 'user_ical_file')) {
            return;
        }

        $user_id = isset($_GET['user']) ? $_GET['user'] : 0;

        $start = new \DateTime(date('Y-m-d'), new \DateTimeZone(wp_timezone_string()));
        $end   = clone $start;
        $end->modify('+1 year');

        $posts = Model::getCalendarEvents($start->format('Y-m-d'), $end->format('Y-m-d'), $user_id);

        if (is_wp_error($posts)) {
            wp_die($posts->get_error_message());
        }

        if (! $posts) {
            wp_die(__('No events found.', 'my-events'));
        }

        $calendar = self::createCalendar($posts, $user_id);

        //

        header('Content-Description: File Transfer');
        header('Content-Type: text/calendar');
        header(
            sprintf('Content-Disposition: attachment; filename=%s.ics', sanitize_title(__('calendar', 'my-events')))
        );

        echo $calendar->get();

        exit;
    }

    public static function notificationArgs($args, $event)
    {
        $file = self::getFile($event->ID, false);

        if (file_exists($file)) {
            $args['attachments'][] = $file;
        }

        return $args;
    }
}

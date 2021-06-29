<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class AdminColumns
{
    const NO_VALUE = '–';

    public static function init()
    {
        add_filter('manage_event_posts_columns', [__CLASS__, 'addEventColumns']);
        add_action('manage_event_posts_custom_column', [__CLASS__, 'renderEventColumns'], 10, 2);

        add_filter('manage_invitee_posts_columns', [__CLASS__, 'addInviteeColumns']);
        add_action('manage_invitee_posts_custom_column', [__CLASS__, 'renderInviteeColumns'], 10, 2);
    }

    public static function addEventColumns($columns)
    {
        return [
            'cb'               => $columns['cb'],
            'title'            => $columns['title'],
            'time'             => __('Time of day', 'my-events'),
            'organisers'       => __('Organisers', 'my-events'),
            'participants'     => __('Participants', 'my-events'),
            'location'         => __('Location', 'my-events'),
            'over'             => __('Over', 'my-events'),
        ] + $columns;
    }

    public static function renderEventColumns($column, $post_id)
    {
        $event = new Event($post_id);

        $time         = $event->getTimeFromUntil();
        $organisers   = Helpers::renderUsers($event->getOrganisers(['fields' => 'ID']));
        $participants = Helpers::renderUsers($event->getParticipants(['fields' => 'ID']));
        $location     = $event->getLocation();

        switch ($column) {
            case 'time':
                echo $time ? esc_html($time) : esc_html(self::NO_VALUE);
                break;
            case 'organisers':
                echo $organisers ? $organisers : esc_html(self::NO_VALUE);
                break;
            case 'participants':
                echo $participants ? $participants : esc_html(self::NO_VALUE);
                break;
            case 'location':
                if ($location) {
                    printf(
                        '<a href="%1$s" target="_blank">%2$s</a>',
                        esc_url(Helpers::getMapURL($location)),
                        esc_html($location)
                    );
                } else {
                    echo esc_html(self::NO_VALUE);
                }
                break;
            case 'over':
                echo Helpers::renderBoolean($event->isOver());
                break;
        }
    }

    public static function addInviteeColumns($columns)
    {
        return [
            'cb'         => $columns['cb'],
            'title'      => $columns['title'],
            'user'       => __('User', 'my-events'),
            'event'      => __('Event', 'my-events'),
            'status'     => __('Status', 'my-events'),
            'email_sent' => __('Email sent', 'my-events'),
        ] + $columns;
    }

    public static function renderInviteeColumns($column, $post_id)
    {
        $invitee = new Invitee($post_id);

        $user      = Helpers::renderUsers($invitee->getUser());
        $event     = Helpers::renderPosts($invitee->getEvent());
        $status    = $invitee->getStatus();
        $statusses = Helpers::getInviteeStatusses();

        switch ($column) {
            case 'user':
                echo $user ? $user : esc_html(self::NO_VALUE);
                break;
            case 'event':
                echo $event ? $event : esc_html(self::NO_VALUE);
                break;
            case 'status':
                echo isset($statusses[$status]) ? esc_html($statusses[$status]) : esc_html(self::NO_VALUE);
                break;
            case 'email_sent':
                echo Helpers::renderBoolean($invitee->getEmailSent());
                break;
        }
    }
}

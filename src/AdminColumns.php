<?php

namespace My\Events;

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
            'participants_num' => __('Number of participants', 'my-events'),
            'location'         => __('Location', 'my-events'),
            'private'          => __('Private', 'my-events'),
            'over'             => __('Over', 'my-events'),
        ] + $columns;
    }

    public static function renderEventColumns($column, $post_id)
    {
        $event = new Event($post_id);

        $time         = $event->getTimeFromUntil();
        $organisers   = self::renderUsers($event->getOrganisers(['fields' => 'ID']));
        $participants = self::renderUsers($event->getParticipants(['fields' => 'ID']));
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
            case 'participants_num':
                if ($event->isLimitedParticipants()) {
                    printf('%1$d/%2$d', count($event->getParticipants()), $event->getMaxParticipants());
                } else {
                    echo count($event->getParticipants());
                }
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
            case 'private':
                echo self::renderBoolean($event->isPrivate());
                break;
            case 'over':
                echo self::renderBoolean($event->isOver());
                break;
        }
    }

    public static function addInviteeColumns($columns)
    {
        return [
            'cb'     => $columns['cb'],
            'title'  => $columns['title'],
            'user'   => __('User', 'my-events'),
            'event'  => __('Event', 'my-events'),
            'status' => __('Status', 'my-events'),
        ] + $columns;
    }

    public static function renderInviteeColumns($column, $post_id)
    {
        $invitee = new Invitee($post_id);

        $user      = self::renderUsers($invitee->getUser());
        $event     = self::renderPosts($invitee->getEvent());
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
        }
    }

    protected static function renderUsers($user_ids, $seperator = ', ')
    {
        $return = [];

        foreach ((array) $user_ids as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $return[] = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url(get_edit_user_link($user->ID)),
                    esc_html($user->display_name)
                );
            }
        }

        return implode($seperator, $return);
    }

    protected static function renderPosts($post_ids, $seperator = ', ')
    {
        $return = [];

        foreach ((array) $post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                $return[] = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url(get_edit_post_link($post->ID)),
                    esc_html($post->post_title)
                );
            }
        }

        return implode($seperator, $return);
    }

    protected static function renderBoolean($value)
    {
        return $value ? esc_html__('yes', 'my-events') : esc_html__('no', 'my-events');
    }
}

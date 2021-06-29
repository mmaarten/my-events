<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class AdminColumns
{
    const NO_VALUE = '–';

    /**
     * Initialize
     */
    public static function init()
    {
        add_filter('manage_event_posts_columns', [__CLASS__, 'addEventColumns']);
        add_action('manage_event_posts_custom_column', [__CLASS__, 'renderEventColumns'], 10, 2);

        add_filter('manage_invitee_posts_columns', [__CLASS__, 'addInviteeColumns']);
        add_action('manage_invitee_posts_custom_column', [__CLASS__, 'renderInviteeColumns'], 10, 2);

        add_filter('manage_event_location_posts_columns', [__CLASS__, 'addEventLocationColumns']);
        add_action('manage_event_location_posts_custom_column', [__CLASS__, 'renderEventLocationColumns'], 10, 2);

        add_filter('manage_invitee_group_posts_columns', [__CLASS__, 'addInviteeGroupColumns']);
        add_action('manage_invitee_group_posts_custom_column', [__CLASS__, 'renderInviteeGroupColumns'], 10, 2);
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
            'is_private'       => __('Private', 'my-events'),
            'is_over'          => __('Over', 'my-events'),
        ] + $columns;
    }

    public static function renderEventColumns($column, $post_id)
    {
        $event = new Event($post_id);

        $time         = $event->getTimeFromUntil();
        $location     = $event->getLocation();
        $organisers   = Helpers::renderUsers($event->getOrganisers(['fields' => 'ID']));
        $participants = Helpers::renderUsers($event->getParticipants(['fields' => 'ID']));

        switch ($column) {
            case 'time':
                echo $time ? esc_html($time) : self::NO_VALUE;
                break;
            case 'organisers':
                echo $organisers ? $organisers : self::NO_VALUE;
                break;
            case 'participants':
                echo $participants ? $participants : self::NO_VALUE;
                break;
            case 'participants_num':
                if ($event->areSubscriptionLimited()) {
                    printf('%1$d/%2$d', count($event->getParticipants()), $event->getMaxSubscriptions());
                } else {
                    echo count($event->getParticipants());
                }
                break;
            case 'location':
                if (trim($location)) {
                    printf('<a href="%1$s" target="_blank">%2$s</a>', esc_url(Helpers::getMapURL($location)), esc_html($location));
                } else {
                    echo self::NO_VALUE;
                }
                break;
            case 'is_private':
                echo $event->isPrivate() ? esc_html__('yes', 'my-events') : esc_html__('no', 'my-events');
                break;
            case 'is_over':
                echo $event->isOver() ? esc_html__('yes', 'my-events') : esc_html__('no', 'my-events');
                break;
        }
    }

    public static function addInviteeColumns($columns)
    {
        return [
            'cb'         => $columns['cb'],
            'title'      => $columns['title'],
            'event'      => __('Event', 'my-events'),
            'user'       => __('User', 'my-events'),
            'status'     => __('Status', 'my-events'),
            'email_sent' => __('Email sent', 'my-events')
        ] + $columns;
    }

    public static function renderInviteeColumns($column, $post_id)
    {
        $invitee = new Invitee($post_id);

        $event     = Helpers::renderPosts([$invitee->getEvent()], 'event');
        $user      = Helpers::renderUsers([$invitee->getUser()]);
        $statusses = Helpers::getInviteeStatusses();
        $status    = $invitee->getStatus();

        switch ($column) {
            case 'event':
                echo $event ? $event : self::NO_VALUE;
                break;
            case 'user':
                echo $user ? $user : self::NO_VALUE;
                break;
            case 'status':
                echo isset($statusses[$status]) ? esc_html($statusses[$status]) : self::NO_VALUE;
                break;
            case 'email_sent':
                echo $invitee->isEmailSent() ? esc_html__('yes', 'my-events') : esc_html__('no', 'my-events');
                break;
        }
    }

    public static function addEventLocationColumns($columns)
    {
        return [
            'cb'      => $columns['cb'],
            'title'   => $columns['title'],
            'address' => __('Address', 'my-events'),
        ] + $columns;
    }

    public static function renderEventLocationColumns($column, $post_id)
    {
        $post = new Post($post_id);

        $address = $post->getMeta('address', true);

        switch ($column) {
            case 'address':
                if (trim($address)) {
                    printf('<a href="%1$s" target="_blank">%2$s</a>', esc_url(Helpers::getMapURL($address)), esc_html($address));
                } else {
                    echo self::NO_VALUE;
                }
                break;
        }
    }

    public static function addInviteeGroupColumns($columns)
    {
        return [
            'cb'    => $columns['cb'],
            'title' => $columns['title'],
            'users' => __('Invitees', 'my-events'),
        ] + $columns;
    }

    public static function renderInviteeGroupColumns($column, $post_id)
    {
        $post = new Post($post_id);

        $user_ids = $post->getMeta('users', true);
        $users = Helpers::renderUsers($user_ids);

        switch ($column) {
            case 'users':
                echo $users ? $users : self::NO_VALUE;
                break;
        }
    }
}

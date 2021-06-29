<?php

namespace My\Events;

use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class AdminColumns
{
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
            'cb'           => $columns['cb'],
            'title'        => $columns['title'],
            'time'         => __('Time of day', 'my-events'),
            'organisers'   => __('Organisers', 'my-events'),
            'participants' => __('Participants', 'my-events'),
            'location'     => __('Location', 'my-events'),
        ] + $columns;
    }

    public static function renderEventColumns($column, $post_id)
    {
        $event = new Event($post_id);

        switch ($column) {
            case 'time':
                break;
            case 'organisers':
                break;
            case 'participants':
                break;
            case 'location':
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

        switch ($column) {
            case 'user':
                break;
            case 'event':
                break;
            case 'status':
                break;
        }
    }
}

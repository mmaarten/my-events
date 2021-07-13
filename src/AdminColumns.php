<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Invitee;
use My\Events\Posts\Event;

class AdminColumns
{
    const NO_VALUE = '–';

    /**
     * Init
     */
    public static function init()
    {
        add_filter('manage_event_posts_columns', [__CLASS__, 'eventColumns']);
        add_action('manage_event_posts_custom_column', [__CLASS__, 'eventColumnContent'], 10, 2);

        add_filter('manage_invitee_posts_columns', [__CLASS__, 'inviteeColumns']);
        add_action('manage_invitee_posts_custom_column', [__CLASS__, 'inviteeColumnContent'], 10, 2);

        add_filter('manage_invitee_group_posts_columns', [__CLASS__, 'inviteeGroupColumns']);
        add_action('manage_invitee_group_posts_custom_column', [__CLASS__, 'inviteeGroupColumnContent'], 10, 2);

        add_filter('manage_event_location_posts_columns', [__CLASS__, 'locationColumns']);
        add_action('manage_event_location_posts_custom_column', [__CLASS__, 'locationColumnContent'], 10, 2);
    }

    /**
     * Event columns
     *
     * @param array $columns
     * @return array
     */
    public static function eventColumns($columns)
    {
        return [
            'cb'           => $columns['cb'],
            'title'        => $columns['title'],
            'time'         => __('Time', 'my-events'),
            'organizers'   => __('Organizers', 'my-events'),
            'participants' => __('Participants', 'my-events'),
            'private'      => __('Private', 'my-events'),
            'over'         => __('Over', 'my-events'),
        ] + $columns;
    }

    /**
     * Event column content
     *
     * @param string $column
     * @param int    $post_id
     * @return array
     */
    public static function eventColumnContent($column, $post_id)
    {
        $event = new Event($post_id);

        switch ($column) {
            case 'time':
                $time = $event->getTimeFromUntil();
                echo $time ? esc_html($time) : esc_html(self::NO_VALUE);
                break;
            case 'organizers':
                $organizers = $event->getOrganizers(['fields' => 'ID', 'orderby' => 'display_name', 'order' => 'ASC']);
                echo $organizers ? Helpers::renderUsers($organizers) : esc_html(self::NO_VALUE);
                break;
            case 'participants':
                $participants = $event->getParticipants(['fields' => 'ID', 'orderby' => 'display_name', 'order' => 'ASC']);
                echo $participants ? Helpers::renderUsers($participants) : esc_html(self::NO_VALUE);
                break;
            case 'private':
                echo Helpers::renderBoolean($event->isPrivate());
                break;
            case 'over':
                echo Helpers::renderBoolean($event->isOver());
                break;
        }
    }

    /**
     * Invitee columns
     *
     * @param array $columns
     * @return array
     */
    public static function inviteeColumns($columns)
    {
        return [
            'cb'         => $columns['cb'],
            'title'      => $columns['title'],
            'event'      => __('Event', 'my-events'),
            'user'       => __('User', 'my-events'),
            'status'     => __('Status', 'my-events'),
            'email_sent' => __('Email sent', 'my-events'),
        ] + $columns;
    }

    /**
     * Invitee column content
     *
     * @param string $column
     * @param int    $post_id
     * @return array
     */
    public static function inviteeColumnContent($column, $post_id)
    {
        $invitee = new Invitee($post_id);

        switch ($column) {
            case 'event':
                $event = Helpers::renderPosts($invitee->getEvent());
                echo $event ? $event : esc_html(self::NO_VALUE);
                break;
            case 'user':
                $user = Helpers::renderUsers($invitee->getUser());
                echo $user ? $user : esc_html(self::NO_VALUE);
                break;
            case 'status':
                $status = $invitee->getStatus();
                $statuses = Helpers::getInviteeStatuses();
                echo isset($statuses[$status]) ? esc_html($statuses[$status]) : esc_html(self::NO_VALUE);
                break;
            case 'email_sent':
                echo Helpers::renderBoolean($invitee->getEmailSent());
                break;
        }
    }

    /**
     * Invitee group columns
     *
     * @param array $columns
     * @return array
     */
    public static function inviteeGroupColumns($columns)
    {
        return [
            'cb'    => $columns['cb'],
            'title' => $columns['title'],
            'users' => __('Users', 'my-events'),
        ] + $columns;
    }

    /**
     * Invitee group column content
     *
     * @param string $column
     * @param int    $post_id
     * @return array
     */
    public static function inviteeGroupColumnContent($column, $post_id)
    {
        $group = new Post($post_id);

        switch ($column) {
            case 'users':
                $users = Helpers::renderUsers($group->getField('users', false));
                echo $users ? $users : esc_html(self::NO_VALUE);
                break;
        }
    }

    /**
     * Location columns
     *
     * @param array $columns
     * @return array
     */
    public static function locationColumns($columns)
    {
        return [
            'cb'      => $columns['cb'],
            'title'   => $columns['title'],
            'address' => __('Address', 'my-events'),
        ] + $columns;
    }

    /**
     * Location column content
     *
     * @param string $column
     * @param int    $post_id
     * @return array
     */
    public static function locationColumnContent($column, $post_id)
    {
        $location = new Post($post_id);

        switch ($column) {
            case 'address':
                $address = $location->getField('address', false);
                if (trim($address)) {
                    printf('<a href="%1$s" target="_blank">%2$s</a>', Helpers::getMapURL($address), esc_html($address));
                } else {
                    echo esc_html(self::NO_VALUE);
                }
                break;
        }
    }
}

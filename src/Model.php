<?php

namespace My\Events;

class Model
{
    public static function getPosts($args = [])
    {
        return get_posts($args + [
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => 999,
        ]);
    }

    public static function getEvents($args = [])
    {
        return self::getPosts($args + [
            'post_type' => 'event',
        ]);
    }

    public static function getPrivateEvents($args = [])
    {
        return self::getEvents($args + [
            'meta_key'     => 'private',
            'meta_compare' => '=',
            'meta_value'   => true,
        ]);
    }

    public static function getInvitees($args = [])
    {
        return self::getPosts($args + [
            'post_type' => 'invitee',
        ]);
    }

    public static function getInviteesByEvent($event_id, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'event',
            'meta_compare' => '=',
            'meta_value'   => $event_id,
        ]);
    }

    public static function getInviteesByUser($user_id, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'user',
            'meta_compare' => '=',
            'meta_value'   => $user_id,
        ]);
    }

    public static function getInviteesByStatus($status, $args = [])
    {
        return self::getInvitees($args + [
            'meta_key'     => 'status',
            'meta_compare' => '=',
            'meta_value'   => $status,
        ]);
    }
}

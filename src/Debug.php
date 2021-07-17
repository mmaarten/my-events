<?php

namespace My\Events;

class Debug
{
    /**
     * Post types
     *
     * @var array
     */
    protected static $post_types = [];

    /**
     * Init
     */
    public static function init()
    {
        self::$post_types = ['event', 'event_invitee', 'event_invitee_group', 'event_location', 'poll', 'poll_time', 'poll_invitee'];

        if (! self::isActive()) {
            return;
        }

        add_action('save_post', [__CLASS__, 'savePost'], 0);
        add_action('wp_trash_post', [__CLASS__, 'trashPost'], 0);
        add_action('before_delete_post', [__CLASS__, 'deletePost'], 0);
        add_action('delete_user', [__CLASS__, 'deleteUser'], 0);
        add_action('added_post_meta', [__CLASS__, 'addedPostMeta'], 0, 2);
        add_action('updated_post_meta', [__CLASS__, 'updatedPostMeta'], 0, 3);
        add_action('transition_post_status', [__CLASS__, 'transitionPostStatus'], 0, 3);
        add_action('my_events/notification_sent', function ($send, $args) {
            self::log(current_action() . ' ' . var_export($args, true));
        }, 0, 2);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public static function isActive()
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }

    /**
     * Save post
     *
     * @param int $post_id
     */
    public static function savePost($post_id)
    {
        if (! in_array(get_post_type($post_id), self::$post_types)) {
            return;
        }

        self::log(sprintf('Save %1$s #%2$s.', get_post_type($post_id), $post_id));
    }

    /**
     * Trash post
     *
     * @param int $post_id
     */
    public static function trashPost($post_id)
    {
        if (! in_array(get_post_type($post_id), self::$post_types)) {
            return;
        }

        self::log(sprintf('Trash %1$s #%2$s.', get_post_type($post_id), $post_id));
    }

    /**
     * Delete post
     *
     * @param int $post_id
     */
    public static function deletePost($post_id)
    {
        if (! in_array(get_post_type($post_id), self::$post_types)) {
            return;
        }

        self::log(sprintf('Delete %1$s #%2$s.', get_post_type($post_id), $post_id));
    }

    /**
     * Delete user
     *
     * @param int $user_id
     */
    public static function deleteUser($user_id)
    {
        self::log(sprintf('Delete user #%1$s.', $user_id));
    }

    /**
     * Added post meta
     *
     * @param int    $post_id
     * @param string $meta_key
     */
    public static function addedPostMeta($post_id, $meta_key)
    {
        if (! in_array(get_post_type($post_id), self::$post_types)) {
            return;
        }

        if (strpos($meta_key, '_') === 0) {
            return;
        }

        self::log(
            sprintf(
                'Added %1$s #%2$s meta \'%3$s\': %4$s.',
                get_post_type($post_id),
                $post_id,
                $meta_key,
                var_export(get_post_meta($post_id, $meta_key, true), true)
            )
        );
    }

    /**
     * Updated post meta
     *
     * @param int    $meta_id
     * @param int    $post_id
     * @param string $meta_key
     */
    public static function updatedPostMeta($meta_id, $post_id, $meta_key)
    {
        if (! in_array(get_post_type($post_id), self::$post_types)) {
            return;
        }

        if (strpos($meta_key, '_') === 0) {
            return;
        }

        self::log(
            sprintf(
                'Updated %1$s #%2$s meta \'%3$s\' to %4$s',
                get_post_type($post_id),
                $post_id,
                $meta_key,
                var_export(get_post_meta($post_id, $meta_key, true), true)
            )
        );
    }

    /**
     * Transition post status
     *
     * @param string  $new_status
     * @param string  $old_status
     * @param WP_Post $post
     */
    public static function transitionPostStatus($new_status, $old_status, $post)
    {
        if (! in_array($post->post_type, self::$post_types)) {
            return;
        }

        self::log(
            sprintf(
                'Transition %1$s #%2$s status from \'%3$s\' to \'%4$s\'.',
                $post->post_type,
                $post->ID,
                $old_status,
                $new_status
            )
        );
    }

    /**
     * Log
     *
     * @param string $message
     * @return bool
     */
    public static function log($message)
    {
        if (! self::isActive()) {
            return false;
        }

        return error_log($message);
    }
}

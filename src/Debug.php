<?php

namespace My\Events;

use My\Events\Posts\Event;

class Debug
{
    public static function init()
    {
        if (! self::isActive()) {
            return;
        }

        add_action('save_post', [__CLASS__, 'savePost'], 0);
        add_action('wp_trash_post', [__CLASS__, 'trashPost'], 0);
        add_action('before_delete_post', [__CLASS__, 'beforeDeletePost'], 0);
        add_action('delete_post', [__CLASS__, 'deletePost'], 0, 2);
        add_action('transition_post_status', [__CLASS__, 'transitionPostStatus'], 0, 3);
        add_action('updated_post_meta', [__CLASS__, 'updatedPostMeta'], 0, 4);
    }

    public static function savePost($post_id)
    {
        self::log(sprintf('Save %2$s #%1$s.', $post_id, get_post_type($post_id)));
    }

    public static function trashPost($post_id)
    {
        self::log(sprintf('Trash %2$s #%1$s.', $post_id, get_post_type($post_id)));
    }

    public static function beforeDeletePost($post_id)
    {
        self::log(sprintf('Before delete %2$s #%1$s.', $post_id, get_post_type($post_id)));
    }

    public static function deletePost($post_id, $post)
    {
        self::log(sprintf('Delete %2$s #%1$s.', $post_id, $post->post_type));
    }

    public static function transitionPostStatus($new_status, $old_status, $post)
    {
        self::log(
            sprintf(
                'Transition %2$s #%1$s status. "%3$s" => "%4$s".',
                $post->ID,
                $post->post_type,
                $old_status,
                $new_status
            )
        );
    }

    public static function updatedPostMeta($meta_id, $object_id, $meta_key, $meta_value)
    {
        if (get_post_type($object_id) && strpos($meta_key, '_') !== 0) {
            self::log(
                sprintf(
                    'Updated %2$s #%1$s meta "%3$s" to %4$s',
                    $object_id,
                    get_post_type($object_id),
                    $meta_key,
                    var_export(get_post_meta($object_id, $meta_key, true), true)
                )
            );
        }
    }

    public static function isActive()
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }

    public static function log($message)
    {
        if (self::isActive()) {
            error_log($message);
        }
    }
}

<?php

namespace My\Events;

class Debug
{
    private static $post_types = [];

    public static function init()
    {
        if (! self::isEnabled()) {
            return;
        }

        self::$post_types = ['event', 'invitee'];

        add_action('save_post', function ($post_id) {
            if (in_array(get_post_type($post_id), self::$post_types)) {
                self::log(sprintf('Save %2$s #%1$s.', $post_id, get_post_type($post_id)));
            }
        }, 0);

        add_action('wp_trash_post', function ($post_id) {
            if (in_array(get_post_type($post_id), self::$post_types)) {
                self::log(sprintf('Trash %2$s #%1$s.', $post_id, get_post_type($post_id)));
            }
        }, 0);

        add_action('before_delete_post', function ($post_id) {
            if (in_array(get_post_type($post_id), self::$post_types)) {
                self::log(sprintf('Before delete %2$s #%1$s.', $post_id, get_post_type($post_id)));
            }
        }, 0);

        add_action('delete_post', function ($post_id) {
            if (in_array(get_post_type($post_id), self::$post_types)) {
                self::log(sprintf('Delete %2$s #%1$s .', $post_id, get_post_type($post_id)));
            }
        }, 0);

        add_action('wp_insert_post', function ($post_id, $post, $update) {
            if (in_array(get_post_type($post_id), self::$post_types)) {
                if ($update) {
                    self::log(sprintf('Update %2$s #%1$s.', $post_id, get_post_type($post_id)));
                } else {
                    self::log(sprintf('Create %2$s #%1$s.', $post_id, get_post_type($post_id)));
                }
            }
        }, 0, 3);

        add_action('transition_post_status', function ($new_status, $old_status, $post) {
            if (in_array(get_post_type($post), self::$post_types) && $new_status !== $old_status) {
                self::log(
                    sprintf(
                        '%4$s #%1$s status changed from "%2$s" to "%3$s".',
                        $post->ID,
                        $old_status,
                        $new_status,
                        $post->post_type
                    )
                );
            }
        }, 0, 3);

        add_action('updated_post_meta', function ($meta_id, $object_id, $meta_key, $_meta_value) {
            if (in_array(get_post_type($object_id), self::$post_types) && strpos($meta_key, '_') !== 0) {
                self::log(
                    sprintf(
                        'Updated %4$s #%1$s meta "%2$s" %3$s.',
                        $object_id,
                        $meta_key,
                        var_export(get_post_meta($object_id, $meta_key, true), true),
                        get_post_type($object_id)
                    )
                );
            }
        }, 0, 4);

        add_filter('pre_wp_mail', function ($return, $args) {
            self::log(sprintf('Email to %1$s: "%2$s".', implode(', ', (array)$args['to']), $args['subject']));
            return $return;
        }, 0, 2);
    }

    public static function isEnabled()
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }

    public static function log($message)
    {
        if (self::isEnabled()) {
            error_log($message);
        }
    }
}

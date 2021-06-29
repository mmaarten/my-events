<?php

namespace My\Events;

class Helpers
{
    public static function adminNotice($message, $type = 'info', $inline = false, $html = false)
    {
        printf(
            '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            sanitize_html_class($type),
            $inline ? 'inline' : '',
            $html ? $message : esc_html($message)
        );
    }

    public static function loadTemplate($name, $args = [], $return = false)
    {
        $file = plugin_dir_path(MY_EVENTS_PLUGIN_FILE) . 'templates/' . $name . '.php';

        if (! file_exists($file)) {
            return false;
        }

        if ($return) {
            ob_start();
        }

        include $file;

        if ($return) {
            return ob_get_clean();
        }

        return true;
    }

    public static function getMapURL($address)
    {
        return add_query_arg('q', $address, 'https://maps.google.com');
    }

    public static function getInviteeStatusses()
    {
        return [
            'pending'  => __('Pending', 'my-events'),
            'accepted' => __('Accepted', 'my-events'),
            'declined' => __('Declined', 'my-events'),
        ];
    }

    public static function renderUsers($user_ids, $seperator = ', ')
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

    public static function renderPosts($post_ids, $seperator = ', ')
    {
        $return = [];

        foreach ((array) $post_ids as $post_id) {
            $post = $post_id ? get_post($post_id) : null;
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

    public static function renderBoolean($value)
    {
        return $value ? esc_html__('yes', 'my-events') : esc_html__('no', 'my-events');
    }
}

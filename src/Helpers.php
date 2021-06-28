<?php

namespace My\Events;

class Helpers
{
    public static function renderUsers($user_ids)
    {
        if (! $user_ids) {
            return false;
        }

        $users = get_users([
            'include' => $user_ids,
        ]);

        $return = [];

        foreach ($users as $user) {
            $return[] = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url(get_edit_user_link($user->ID)),
                esc_html($user->display_name)
            );
        }

        return implode(', ', $return);
    }

    public static function renderPosts($post_ids, $post_type = 'post')
    {
        if (! $post_ids) {
            return false;
        }

        $posts = get_posts([
            'include'   => $post_ids,
            'post_type' => $post_type,
        ]);

        $return = [];

        foreach ($posts as $post) {
            $return[] = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url(get_edit_post_link($post->ID)),
                esc_html($post->post_title)
            );
        }

        return implode(', ', $return);
    }

    public static function getMapURL($address)
    {
        return add_query_arg('q', $address, 'https://maps.google.com');
    }

    public static function adminNotice($message, $type = 'info', $inline = false)
    {
        printf(
            '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            $type,
            $inline ? 'inline' : '',
            esc_html($message)
        );
    }

    public static function getInviteeStatusses()
    {
        return [
            'pending'  => __('Pending', 'my-events'),
            'accepted' => __('Accepted', 'my-events'),
            'declined' => __('Declined', 'my-events'),
        ];
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
}

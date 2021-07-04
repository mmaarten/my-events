<?php

namespace My\Events;

class Helpers
{
    public static function getPostByName($post_name, $post_type = 'post')
    {
        return current(get_posts([
            'name'        => $post_name,
            'post_type'   => $post_type,
            'post_status' => 'publish',
            'numberposts' => 1,
        ]));
    }

    public static function adminNotice($message, $type = 'info', $inline = false, $html = false)
    {
        return sprintf(
            '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            sanitize_html_class($type),
            $inline ? 'inline' : '',
            $html ? $message : esc_html($message)
        );
    }

    public static function loadTemplate($name, $args = [], $return = false)
    {
        $file = locate_template('events/' . $name . '.php', false, false);

        if (! $file) {
            $file = plugin_dir_path(MY_EVENTS_PLUGIN_FILE) . 'templates/' . $name . '.php';
        }

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

    public static function isDateInbetween($date, $start, $end)
    {
        $dates = is_array($date) ? $date : (array) $date;

        $start = strtotime(date('Y-m-d', strtotime($start)));
        $end   = strtotime(date('Y-m-d', strtotime($end)));

        foreach ($dates as $date) {
            $date = strtotime($date);
            if ($start <= $date && $date <= $end) {
                return true;
            }
        }

        return false;
    }

    public static function getTimesRepeat($start, $end, $end_repeat, $modifier, $exclude = [])
    {
        $start      = new \DateTime($start);
        $end        = new \DateTime($end);
        $end_repeat = new \DateTime($end_repeat);

        // Check for valid dates and modifier.
        if (! $start || ! $end || ! $end_repeat) {
            return [];
        }

        $_start = new \DateTime($start->format('Y-m-d'));

        $times = [];

        while ($_start->format('U') <= $end_repeat->format('U') && $_start && $start && $end) {
            $time = [
                'start' => $start->format('Y-m-d H:i:s'),
                'end'   => $end->format('Y-m-d H:i:s'),
            ];

            if (! self::isDateInbetween($exclude, $time['start'], $time['end'])) {
                $times[] = $time;
            }

            $_start->modify($modifier);
            $start->modify($modifier);
            $end->modify($modifier);
        }

        return $times;
    }
}

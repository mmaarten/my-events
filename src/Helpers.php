<?php

namespace My\Events;

class Helpers
{
    public static function unautop($value)
    {
        //remove any new lines already in there
        $value = str_replace('\n', '', $value);

        //remove all <p>
        $value = str_replace('<p>', '', $value);

        //replace <br /> with \n
        $value = str_replace(['<br />', '<br>', '<br/>'], '\n', $value);

        //replace </p> with \n\n
        $value = str_replace('</p>', '\n\n', $value);

        return $value;
    }

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
        if ($value) {
            return sprintf(
                '<span class="dashicons-before dashicons-yes"><span class="screen-reader-text">%s</span></span>',
                esc_html('yes', 'my-events')
            );
        }

        return sprintf(
            '<span class="dashicons-before dashicons-no-alt"><span class="screen-reader-text">%s</span></span>',
            esc_html('no', 'my-events')
        );
    }

    public static function generateDates($start, $end, $end_repeat, $modifier, $exclude = [])
    {
        $start      = new \DateTime($start);
        $end        = new \DateTime($end);
        $end_repeat = new \DateTime($end_repeat);

        $dates = [];

        while ($start->format('U') <= $end_repeat->format('U') && $start && $end && $end_repeat) {
            $date = [
                'start' => $start->format('Y-m-d'),
                'end'   => $end->format('Y-m-d'),
            ];

            $include = true;
            foreach ($exclude as $value) {
                if (self::doDatesIntersect($date['start'], $date['end'], $value, $value)) {
                    $include = false;
                    break;
                }
            }

            if ($include) {
                $dates[] = $date;
            }

            $start->modify($modifier);
            $end->modify($modifier);
        }

        return $dates;
    }

    /**
     * @link https://stackoverflow.com/questions/325933/determine-whether-two-date-ranges-overlap
     */
    public static function doDatesIntersect($a_start, $a_end, $b_start, $b_end)
    {
        if ($a_start <= $b_end && $b_start <= $a_end && $a_start <= $a_end && $b_start <= $b_end) {
            return true;
        }

        if ($a_start <= $b_end && $a_start <= $a_end && $b_start <= $a_end && $b_start <= $b_end) {
            return true;
        }

        return false;
    }
}

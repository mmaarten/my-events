<?php

namespace My\Events;

class Helpers
{
    public static function getIcon($key)
    {
        return sprintf('<span class="dashicons-before dashicons-%s"></span>', sanitize_html_class($key));
    }

    public static function adminNotice($message, $type = 'info', $inline = false, $icon = '')
    {
        echo self::getAdminNotice($message, $type, $inline, $icon);
    }

    public static function getAdminNotice($message, $type = 'info', $inline = false, $icon = '')
    {
        return sprintf(
            '<div class="notice notice-%1$s %2$s"><p>%4$s %3$s</p></div>',
            sanitize_html_class($type),
            $inline ? 'inline' : '',
            esc_html($message),
            $icon ? self::getIcon($icon) : ''
        );
    }

    public static function alert($message, $type = 'info')
    {
        printf(
            '<div class="alert alert-%1$s" role="alert">%2$s</div>',
            sanitize_html_class($type),
            esc_html($message)
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

    /**
     * Get invitee statuses
     *
     * @return array
     */
    public static function getInviteeStatuses()
    {
        return [
            'pending'  => __('Pending', 'my-events'),
            'accepted' => __('Accepted', 'my-events'),
            'declined' => __('Declined', 'my-events'),
        ];
    }

    /**
     * Get map URL
     *
     * @link https://stackoverflow.com/a/1300922
     * @param string $address
     * @return string
     */
    public static function getMapURL($address)
    {
        return add_query_arg('q', $address, 'https://maps.google.com');
    }

    /**
     * Render posts
     *
     * @param array  $post_ids
     * @param string $separator
     * @return string
     */
    public static function renderPosts($post_ids, $separator = ', ', $permalink = false)
    {
        $return = [];

        foreach ((array) $post_ids as $post_id) {
            if ($post_id && get_post_type($post_id)) {
                $post = get_post($post_id);
                $return[] = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    $permalink ? get_permalink($post) : get_edit_post_link($post),
                    esc_html($post->post_title)
                );
            }
        }

        return implode($separator, $return);
    }

    /**
     * Render users
     *
     * @param array  $user_ids
     * @param string $separator
     * @return string
     */
    public static function renderUsers($user_ids, $separator = ', ')
    {
        $return = [];

        foreach ((array) $user_ids as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $return[] = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    get_edit_user_link($user->ID),
                    esc_html($user->display_name)
                );
            }
        }

        return implode($separator, $return);
    }

    /**
     * Render boolean
     *
     * @param bool $value
     * @return string
     */
    public static function renderBoolean($value)
    {
        return sprintf(
            '<span class="dashicons-before dashicons-%1$s" title="%2$s"></span>',
            $value ? 'yes' : 'no-alt',
            $value ? esc_attr__('yes', 'my-events') : esc_attr__('no', 'my-events')
        );
    }

    /**
     * Do dates overlap
     *
     * @link https://stackoverflow.com/a/325964
     * @param mixed $a_start
     * @param mixed $a_end
     * @param mixed $b_start
     * @param mixed $b_end
     * @return bool
     */
    public static function doDatesOverlap($a_start, $a_end, $b_start, $b_end)
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

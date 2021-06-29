<?php

namespace My\Events;

class Helpers
{
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

    public static function adminNotice($message, $type = 'info', $inline = false)
    {
        printf(
            '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
            sanitize_html_class($type),
            $inline ? 'inline' : '',
            esc_html($message)
        );
    }

    public static function repeatDates($start, $end, $repeat_end, $modifier)
    {
        $start      = new \DateTime($start);
        $end        = new \DateTime($end);
        $repeat_end = new \DateTime($repeat_end);

        $times = [];

        while ($start->format('U') < $repeat_end->format('U')) {
            $times[] = [
                'start' => $start->format('Y-m-d H:i:s'),
                'end'   => $end->format('Y-m-d H:i:s'),
            ];

            $start = $start->modify($modifier);
            $end   = $end->modify($modifier);
        }

        return $times;
    }
}

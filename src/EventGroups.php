<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;

class EventGroups
{
    public static function init()
    {
        add_action('acf/init', [__CLASS__, 'addFields']);
        add_filter('acf/load_field/key=my_events_event_group_events', [__CLASS__, 'renderEvents']);
    }

    public static function getDetachEventFromGroupURL($event_id)
    {
        return add_query_arg([
            MY_EVENTS_NONCE_NAME => wp_create_nonce('detach_event_from_group'),
            'event'    => $event_id,
            'redirect' => get_edit_post_link($event_id),
        ], get_admin_url());
    }

    public static function addFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_event_group_group',
            'title'    => __('Events', 'my-events'),
            'position' => 'side',
            'fields'   => [],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'event_group',
                    ],
                ],
            ],
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_group_events',
            'label'         => __('', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'type'          => 'message',
            'menu_order'    => 100,
            'parent'        => 'my_events_event_group_group',
        ]);
    }

    public static function renderEvents($field)
    {
        $screen = get_current_screen();

        if ($screen->id !== 'event_group') {
            return $field;
        }

        $group_id = $_GET['post'];

        $field['message'] = Helpers::loadTemplate('event-group-edit-events', [
            'events' => Model::getEventsByEventGroup($group_id),
        ], true);

        return $field;
    }
}

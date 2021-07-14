<?php

namespace My\Events;

use My\Events\Posts\Event;

class EventsCreator
{
    /**
     * Options page
     *
     * @var array
     */
    protected static $options_page = [];

    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/init', [__CLASS__, 'addOptionsPage']);
        add_action('acf/init', [__CLASS__, 'addGeneralFields']);
        add_action('acf/init', [__CLASS__, 'addRepeatFields']);
        add_action('acf/save_post', [__CLASS__, 'savePost']);

        add_filter('acf/get_field_group', function ($field_group) {
            switch ($field_group['key']) {
                case 'my_events_event_group':
                    $field_group['location'][] = [
                        [
                            'param'    => 'options_page',
                            'operator' => '==',
                            'value'    => self::$options_page['menu_slug'],
                        ],
                    ];
                    break;
            }
            return $field_group;
        });
    }

    /**
     * Add options page
     */
    public static function addOptionsPage()
    {
        self::$options_page = acf_add_options_page([
            'page_title'    => __('Events Creator', 'my-events'),
            'menu_title'    => __('Events Creator', 'my-events'),
            'menu_slug'     => 'my-events-event-creator',
            'capability'    => 'edit_posts',
            'parent_slug'   => 'my-events',
            'post_id'       => 'my_events_event_creator',
        ]);
    }

    /**
     * Render menu page
     */
    public static function renderMenuPage()
    {
    }

    /**
     * Add general fields
     */
    public static function addGeneralFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_events_creator_group',
            'title'    => __('General', 'my-events'),
            'fields'   => [],
            'menu_order' => 0,
            'location' => [
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => self::$options_page['menu_slug'],
                    ],
                ],
            ],
        ]);

        // Post title
        acf_add_local_field([
            'key'           => 'my_events_events_creator_post_title_field',
            'label'         => __('Events title', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'post_title',
            'type'          => 'text',
            'required'      => true,
            'parent'        => 'my_events_events_creator_group',
        ]);

        // Post status
        acf_add_local_field([
            'key'           => 'my_events_events_creator_post_status_field',
            'label'         => __('Events status', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'post_status',
            'type'          => 'select',
            'choices'       => get_post_statuses(),
            'default_value' => 'draft',
            'required'      => true,
            'parent'        => 'my_events_events_creator_group',
        ]);

        // Invitee status
        acf_add_local_field([
            'key'           => 'my_events_events_creator_invitee_status_field',
            'label'         => __('Invitee status', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'post_status',
            'type'          => 'select',
            'choices'       => Helpers::getInviteeStatuses(),
            'default_value' => 'pending',
            'required'      => true,
            'parent'        => 'my_events_events_creator_group',
        ]);
    }

    /**
     * Add repeat fields
     */
    public static function addRepeatFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_repeat_group',
            'title'    => __('Repeat', 'my-events'),
            'fields'   => [],
            'position' => 'side',
            'location' => [
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => self::$options_page['menu_slug'],
                    ],
                ],
            ],
        ]);

        // Interval
        acf_add_local_field([
            'key'           => 'my_events_repeat_repeat_interval_field',
            'label'         => __('Interval', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'repeat_interval',
            'type'          => 'select',
            'choices'       => [
                '+1 week'  => __('Every week', 'my-events'),
                '+2 weeks' => __('Every two weeks', 'my-events'),
            ],
            'default_value' => '+1 week',
            'required'      => true,
            'parent'        => 'my_events_repeat_group',
        ]);

        // End
        acf_add_local_field([
            'key'            => 'my_events_repeat_end_field',
            'label'          => __('End', 'my-events'),
            'instructions'   => __('', 'my-events'),
            'name'           => 'end_repeat',
            'type'           => 'date_picker',
            'display_format' => get_option('date_format'),
            'return_format'  => 'Y-m-d',
            'first_day'      => get_option('start_of_week', 0),
            'default_value'  => '',
            'required'       => true,
            'parent'         => 'my_events_repeat_group',
        ]);

        // Exclude
        acf_add_local_field([
            'key'           => 'my_events_repeat_exclude_field',
            'label'         => __('Exclude', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'repeat_exclude',
            'type'          => 'repeater',
            'sub_fields'    => [
                [
                    'key'            => 'my_events_repeat_exclude_date_field',
                    'label'          => __('Date', 'my-events'),
                    'instructions'   => __('', 'my-events'),
                    'name'           => 'date',
                    'type'           => 'date_picker',
                    'display_format' => get_option('date_format'),
                    'return_format'  => 'Y-m-d',
                    'first_day'      => get_option('start_of_week', 0),
                    'default_value'  => '',
                    'required'       => true,
                ],
            ],
            'required'      => false,
            'parent'        => 'my_events_repeat_group',
        ]);
    }

    public static function getField($selector, $format_value = true)
    {
        return get_field($selector, self::$options_page['post_id'], $format_value);
    }

    /**
     * Save post
     *
     * @param mixed $post_id
     */
    public static function savePost($post_id)
    {
        if ($post_id != self::$options_page['post_id']) {
            return;
        }

        $start           = self::getField('start');
        $end             = self::getField('end');
        $end_repeat      = self::getField('end_repeat');
        $repeat_interval = self::getField('repeat_interval');
        $repeat_exclude  = self::getField('repeat_exclude');

        if (is_array($repeat_exclude)) {
            $repeat_exclude = wp_list_pluck($repeat_exclude, 'date');
        } else {
            $repeat_exclude = [];
        }

        $times = Helpers::getDatesRepeat($start, $end, $end_repeat, $repeat_interval, $repeat_exclude);
        $times = array_slice($times, 0, 100);

        foreach ($times as $time) {
            $post_id = wp_insert_post([
                'post_title'   => self::getField('post_title'),
                'post_content' => '',
                'post_type'    => 'event',
                'post_status'  => self::getField('post_status'),
            ]);

            $event = new Event($post_id);
            $event->updateField('start', $time['start']);
            $event->updateField('end', $time['end']);
            $event->updateField('description', self::getField('description', false));
            $event->updateField('all_day', self::getField('all_day', false));
            $event->updateField('organizers', self::getField('organizers', false));
            $event->updateField('invitee_type', self::getField('invitee_type', false));
            $event->updateField('individual_invitees', self::getField('individual_invitees', false));
            $event->updateField('invitee_group', self::getField('invitee_group', false));
            $event->updateField('max_participants', self::getField('max_participants', false));
            $event->updateField('location_type', self::getField('location_type', false));
            $event->updateField('custom_location', self::getField('custom_location', false));
            $event->updateField('location_id', self::getField('location_id', false));
            $event->updateField('private', self::getField('private', false));

            // Update post name.
            wp_update_post([
                'ID'        => $event->ID,
                'post_name' => sanitize_title($event->post_title . '-' . $event->getStartTime()),
            ]);

            // Apply event settings
            Events::applySettingsToEvent($event->ID);
        }
    }
}

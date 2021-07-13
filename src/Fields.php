<?php

namespace My\Events;

class Fields
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/init', [__CLASS__, 'addEventFields']);
        add_action('acf/init', [__CLASS__, 'addInviteeFields']);
        add_action('acf/init', [__CLASS__, 'addInviteeGroupFields']);
        add_action('acf/init', [__CLASS__, 'addLocationFields']);
    }

    /**
     * Add event fields
     */
    public static function addEventFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_event_group',
            'title'    => __('General', 'my-events'),
            'fields'   => [],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'event',
                    ],
                ],
            ],
        ]);

        // Description
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Start time
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // End time
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Organizers
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Invitee type
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Individual invitees
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Invitee group
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Location type
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Custom location
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Location id
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Private
        acf_add_local_field([
            'key'           => 'my_events_event_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);
    }

    /**
     * Add invitee fields
     */
    public static function addInviteeFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_invitee_group',
            'title'    => __('General', 'my-events'),
            'fields'   => [],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'invitee',
                    ],
                ],
            ],
        ]);

        // Event
        acf_add_local_field([
            'key'           => 'my_events_invitee_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_invitee_group',
        ]);

        // User
        acf_add_local_field([
            'key'           => 'my_events_invitee_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_invitee_group',
        ]);

        // Status
        acf_add_local_field([
            'key'           => 'my_events_invitee_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_invitee_group',
        ]);
    }

    /**
     * Add invitee group fields
     */
    public static function addInviteeGroupFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_invitee_group_group',
            'title'    => __('General', 'my-events'),
            'fields'   => [],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'invitee_group',
                    ],
                ],
            ],
        ]);

        // Users
        acf_add_local_field([
            'key'           => 'my_events_invitee_group_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_invitee_group_group',
        ]);
    }

    /**
     * Add location fields
     */
    public static function addLocationFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_location_group',
            'title'    => __('General', 'my-events'),
            'fields'   => [],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'event_location',
                    ],
                ],
            ],
        ]);

        // Address
        acf_add_local_field([
            'key'           => 'my_events_location_xxx_field',
            'label'         => __('xxx', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'xxx',
            'type'          => '',
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_location_group',
        ]);
    }
}

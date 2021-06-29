<?php

namespace My\Events;

use My\Events\Posts\Event;

class Fields
{
    /**
     * Initialize
     */
    public static function init()
    {
        add_action('acf/init', [__CLASS__, 'addEventFields']);
        add_action('acf/init', [__CLASS__, 'addInviteeFields']);
        add_action('acf/init', [__CLASS__, 'addLocationFields']);
        add_action('acf/init', [__CLASS__, 'addInviteeGroupFields']);
    }

    public static function addEventFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_event_group',
            'title'    => __('Settings', 'my-events'),
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

        acf_add_local_field([
            'key'          => 'my_events_event_description',
            'label'        => __('Description', 'my-events'),
            'instructions' => '',
            'name'         => 'description',
            'type'         => 'textarea',
            'rows'         => 3,
            'new_lines'    => 'wpautop',
            'required'     => false,
            'menu_order'   => 100,
            'parent'       => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_start',
            'label'        => __('Start', 'my-events'),
            'instructions'   => __('', 'my-events'),
            'name'           => 'start',
            'type'           => 'date_time_picker',
            'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
            'return_format'  => 'Y-m-d H:i:s',
            'first_day'      => get_option('start_of_week'),
            'required'       => true,
            'wrapper'        => ['width' => '50%'],
            'menu_order'     => 200,
            'parent'         => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'            => 'my_events_event_end',
            'label'          => __('End', 'my-events'),
            'instructions'   => __('', 'my-events'),
            'name'           => 'end',
            'type'           => 'date_time_picker',
            'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
            'return_format'  => 'Y-m-d H:i:s',
            'first_day'      => get_option('start_of_week'),
            'required'       => true,
            'wrapper'        => ['width' => '50%'],
            'menu_order'     => 201,
            'parent'         => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_organisers',
            'label'         => __('Organisers', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'organisers',
            'type'          => 'user',
            'multiple'      => 1,
            'return_format' => 'id',
            'required'      => true,
            'menu_order'    => 300,
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_invitees_type',
            'label'         => __('Invitees', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'invitees_type',
            'type'         => 'select',
            'choices'      => [
                'individual' => __('Individual', 'my-events'),
                'group'      => __('Choose from a group', 'my-events'),
            ],
            'required'      => true,
            'menu_order'    => 400,
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_invitees_individual',
            'label'         => __('Individual', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'invitees_individual',
            'type'          => 'user',
            'multiple'      => true,
            'return_format' => 'id',
            'required'      => true,
            'menu_order'    => 400,
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_invitees_type',
                        'operator' => '==',
                        'value'    => 'individual'
                    ],
                ],
            ],
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_invitees_group',
            'label'         => __('Group', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'invitees_group',
            'type'          => 'post_object',
            'post_type'     => 'invitee_group',
            'multiple'      => false,
            'return_format' => 'id',
            'required'      => true,
            'menu_order'    => 400,
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_invitees_type',
                        'operator' => '==',
                        'value'    => 'group'
                    ],
                ],
            ],
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_limit_subscriptions',
            'label'         => __('Limit the amount of subscriptions', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'limit_subscriptions',
            'type'         => 'true_false',
            'required'      => false,
            'menu_order'    => 400,
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_max_subscriptions',
            'label'         => __('Amount', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'max_subscriptions',
            'type'          => 'number',
            'required'      => true,
            'default_value' => 10,
            'menu_order'    => 400,
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_limit_subscriptions',
                        'operator' => '==',
                        'value'    => '1',
                    ],
                ],
            ],
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_location_type',
            'label'        => __('Location', 'my-events'),
            'instructions' => __('', 'my-events'),
            'name'         => 'location_type',
            'type'         => 'select',
            'choices'      => [
                'input' => __('Custom', 'my-events'),
                'id'    => __('Choose from a list', 'my-events'),
            ],
            'required'     => true,
            'menu_order'   => 500,
            'parent'       => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_location_input',
            'label'        => __('Custom', 'my-events'),
            'instructions' => __('The geographical location.', 'my-events'),
            'name'         => 'location_input',
            'type'         => 'text',
            'required'     => true,
            'menu_order'   => 600,
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_location_type',
                        'operator' => '==',
                        'value'    => 'input'
                    ],
                ],
            ],
            'parent'       => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_location_id',
            'label'         => __('List', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'location_id',
            'type'          => 'post_object',
            'post_type'     => 'event_location',
            'multiple'      => false,
            'return_format' => 'id',
            'required'      => true,
            'menu_order'    => 700,
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_location_type',
                        'operator' => '==',
                        'value'    => 'id'
                    ],
                ],
            ],
            'parent'       => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_is_private',
            'label'         => __('Private', 'my-events'),
            'instructions'  => __('Only organisers and participants of this event have access to this event.', 'my-events'),
            'name'          => 'is_private',
            'type'         => 'true_false',
            'required'      => false,
            'menu_order'    => 400,
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field_group([
            'key'      => 'my_events_event_invitees_group',
            'title'    => __('Invitees', 'my-events'),
            'fields'   => [],
            'position' => 'side',
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

        acf_add_local_field([
            'key'           => 'my_events_event_invitees_list',
            'label'         => __('', 'my-events'),
            'instructions'  => __(''),
            'type'         => 'message',
            'parent'        => 'my_events_event_invitees_group',
        ]);
    }

    public static function addInviteeFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_invitee_group',
            'title'    => __('Settings', 'my-events'),
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

        acf_add_local_field([
            'key'           => 'my_events_invitee_event',
            'label'         => __('Event', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'event',
            'type'          => 'post_object',
            'post_type'     => 'event',
            'multiple'      => false,
            'return_format' => 'id',
            'required'      => true,
            'menu_order'    => 100,
            'parent'        => 'my_events_invitee_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_invitee_user',
            'label'         => __('User', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'user',
            'type'          => 'user',
            'multiple'      => false,
            'return_format' => 'id',
            'required'      => true,
            'menu_order'    => 200,
            'parent'        => 'my_events_invitee_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_invitee_status',
            'label'         => __('Status', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'status',
            'type'          => 'select',
            'choices'       => Helpers::getInviteeStatusses(),
            'default_value' => 'pending',
            'required'      => true,
            'menu_order'    => 300,
            'parent'        => 'my_events_invitee_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_invitee_email_sent',
            'label'         => __('Email Sent', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'email_sent',
            'type'          => 'true_false',
            'required'      => false,
            'parent'        => 'my_events_invitee_group',
        ]);
    }

    public static function addLocationFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_event_location_group',
            'title'    => __('Settings', 'my-events'),
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

        acf_add_local_field([
            'key'          => 'my_events_event_location',
            'label'        => __('Address', 'my-events'),
            'instructions' => __('The geographical location.', 'my-events'),
            'name'         => 'address',
            'type'         => 'text',
            'required'     => false,
            'parent'       => 'my_events_event_location_group',
        ]);
    }

    public static function addInviteeGroupFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_invitee_group_group',
            'title'    => __('Settings', 'my-events'),
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

        acf_add_local_field([
            'key'           => 'my_events_invitee_group_users',
            'label'         => __('Invitees', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'users',
            'type'          => 'user',
            'multiple'      => true,
            'return_format' => 'id',
            'required'      => true,
            'menu_order'    => 300,
            'parent'        => 'my_events_invitee_group_group',
        ]);
    }
}

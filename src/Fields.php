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
            'key'           => 'my_events_event_description_field',
            'label'         => __('Description', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'description',
            'type'          => 'textarea',
            'new_lines'     => 'wpautop',
            'rows'          => 3,
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Start time
        acf_add_local_field([
            'key'            => 'my_events_event_start_field',
            'label'          => __('Start time', 'my-events'),
            'instructions'   => __('', 'my-events'),
            'name'           => 'start',
            'type'           => 'date_time_picker',
            'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
            'return_format'  => 'Y-m-d H:i:s',
            'first_day'      => get_option('start_of_week', 0),
            'default_value'  => date_i18n('Y-m-d H:00:00'),
            'required'       => true,
            'wrapper'        => ['width' => 50],
            'parent'         => 'my_events_event_group',
        ]);

        // End time
        acf_add_local_field([
            'key'           => 'my_events_event_end_field',
            'label'         => __('End time', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'end',
            'type'           => 'date_time_picker',
            'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
            'return_format'  => 'Y-m-d H:i:s',
            'first_day'      => get_option('start_of_week', 0),
            'default_value'  => date_i18n('Y-m-d H:00:00'),
            'required'       => true,
            'wrapper'        => ['width' => 50],
            'parent'         => 'my_events_event_group',
        ]);

        // Organizers
        acf_add_local_field([
            'key'           => 'my_events_event_organizers_field',
            'label'         => __('Organizers', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'organizers',
            'type'          => 'user',
            'multiple'      => true,
            'return_format' => 'id',
            'required'      => true,
            'parent'        => 'my_events_event_group',
        ]);

        // Invitee type
        acf_add_local_field([
            'key'           => 'my_events_event_invitee_type_field',
            'label'         => __('Invitees', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'invitee_type',
            'type'          => 'select',
            'choices'       => [
                'individual' => __('Individual', 'my-events'),
                'group'      => __('Group', 'my-events'),
            ],
            'default_value' => 'individual',
            'required'      => true,
            'parent'        => 'my_events_event_group',
        ]);

        // Individual invitees
        acf_add_local_field([
            'key'               => 'my_events_event_individual_invitees_field',
            'label'             => __('Individual invitees', 'my-events'),
            'instructions'      => __('', 'my-events'),
            'name'              => 'individual_invitees',
            'type'              => 'user',
            'multiple'          => true,
            'return_format'     => 'id',
            'required'          => true,
            'parent'            => 'my_events_event_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_invitee_type_field',
                        'operator' => '==',
                        'value'    => 'individual',
                    ],
                ],
            ],
        ]);

        // Invitee group
        acf_add_local_field([
            'key'               => 'my_events_event_invitee_group_field',
            'label'             => __('Invitee group', 'my-events'),
            'instructions'      => __('', 'my-events'),
            'name'              => 'invitee_group',
            'type'              => 'post_object',
            'post_type'         => 'invitee_group',
            'multiple'          => false,
            'required'          => true,
            'parent'            => 'my_events_event_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_invitee_type_field',
                        'operator' => '==',
                        'value'    => 'group',
                    ],
                ],
            ],
        ]);

        // Location type
        acf_add_local_field([
            'key'           => 'my_events_event_location_type_field',
            'label'         => __('Location', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'location_type',
            'type'          => 'select',
            'choices'       => [
                'custom' => __('Custom', 'my-events'),
                'id'     => __('Preset', 'my-events'),
            ],
            'default_value' => 'custom',
            'required'      => true,
            'parent'        => 'my_events_event_group',
        ]);

        // Custom location
        acf_add_local_field([
            'key'           => 'my_events_event_custom_location_field',
            'label'         => __('Custom location', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'custom_location',
            'type'          => 'text',
            'default_value' => '',
            'required'      => true,
            'parent'        => 'my_events_event_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_location_type_field',
                        'operator' => '==',
                        'value'    => 'custom',
                    ],
                ],
            ],
        ]);

        // Location id
        acf_add_local_field([
            'key'           => 'my_events_event_location_id_field',
            'label'         => __('Preset location', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'location_id',
            'type'          => 'post_object',
            'post_type'     => 'event_location',
            'multiple'      => false,
            'return_format' => 'id',
            'default_value' => '',
            'required'      => true,
            'parent'        => 'my_events_event_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_location_type_field',
                        'operator' => '==',
                        'value'    => 'id',
                    ],
                ],
            ],
        ]);

        // Private
        acf_add_local_field([
            'key'           => 'my_events_event_private_field',
            'label'         => __('Private', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'private',
            'type'          => 'true_false',
            'default_value' => false,
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

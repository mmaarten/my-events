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

        add_filter('acf/settings/l10n_textdomain', function () {
            return 'my-events';
        });
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
            'instructions'  => __('A brief description about this event.', 'my-events'),
            'name'          => 'description',
            'type'          => 'textarea',
            'new_lines'     => 'wpautop',
            'rows'          => 3,
            'default_value' => '',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Time
        acf_add_local_field([
            'key'    => 'my_events_event_time_field',
            'label'  => __('Time', 'my-events'),
            'name'   => 'time',
            'type'   => 'message',
            'parent' => 'my_events_event_group',
        ]);

        // Start time
        acf_add_local_field([
            'key'            => 'my_events_event_start_field',
            'label'          => __('Start', 'my-events'),
            'instructions'   => __('The time the event starts.', 'my-events'),
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
            'label'         => __('End', 'my-events'),
            'instructions'  => __('The time the event ends.', 'my-events'),
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

        // All day
        acf_add_local_field([
            'key'           => 'my_events_event_all_day_field',
            'message'       => __('This event takes one or more full days.', 'my-events'),
            'name'          => 'all_day',
            'type'          => 'true_false',
            'default_value' => false,
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Organizers
        acf_add_local_field([
            'key'           => 'my_events_event_organizers_field',
            'label'         => __('Organizers', 'my-events'),
            'instructions'  => __('Organisers recieve notifications by email when someone accepts or declines the invitation.', 'my-events'),
            'name'          => 'organizers',
            'type'          => 'user',
            'multiple'      => true,
            'return_format' => 'id',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Organizers can edit
        acf_add_local_field([
            'key'           => 'my_events_event_organizers_can_edit_field',
            'message'         => __('Allow organizers to edit this event.', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'organizers_can_edit',
            'type'          => 'true_false',
            'default_value' => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Invitee type
        acf_add_local_field([
            'key'           => 'my_events_event_invitee_type_field',
            'label'         => __('Invitees', 'my-events'),
            'instructions'  => __('Select the people you would like to invite.', 'my-events'),
            'name'          => 'invitee_type',
            'type'          => 'select',
            'choices'       => [
                'individual' => __('Individual', 'my-events'),
                'group'      => __('Choose from a group', 'my-events'),
            ],
            'default_value' => 'individual',
            'required'      => false,
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
            'required'          => false,
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
            'required'          => false,
            'allow_null'        => true,
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

        // Default invitee status
        acf_add_local_field([
            'key'           => 'my_events_event_invitee_default_status_field',
            'label'         => __('Default status', 'my-events'),
            'instructions'  => __('Invitees with a "pending" status receive an invitation email about this event.', 'my-events'),
            'name'          => 'invitee_default_status',
            'type'          => 'select',
            'choices'       => Helpers::getInviteeStatuses(),
            'default_value' => 'pending',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        // Max participants
        acf_add_local_field([
            'key'          => 'my_events_event_max_participants_field',
            'label'        => __('Limit the amount of participants', 'my-events'),
            'instructions' => __('Leave empty for unlimited participants.', 'my-events'),
            'name'         => 'max_participants',
            'type'         => 'number',
            'min'          => 2,
            'required'     => false,
            'parent'       => 'my_events_event_group',
        ]);

        // Location type
        acf_add_local_field([
            'key'           => 'my_events_event_location_type_field',
            'label'         => __('Location', 'my-events'),
            'instructions'  => __('The geographical location were the event takes place.', 'my-events'),
            'name'          => 'location_type',
            'type'          => 'select',
            'choices'       => [
                'custom' => __('Custom', 'my-events'),
                'id'     => __('Preset', 'my-events'),
            ],
            'default_value' => 'custom',
            'required'      => false,
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
            'required'      => false,
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
            'allow_null'    => true,
            'required'      => false,
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
            'message'       => __('Only organizers and invitees of this event have access to this event.', 'my-events'),
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
                        'value'    => 'event_invitee',
                    ],
                ],
            ],
        ]);

        // Event
        acf_add_local_field([
            'key'           => 'my_events_invitee_event_field',
            'label'         => __('Event', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'event',
            'type'          => 'post_object',
            'post_type'     => 'event',
            'return_format' => 'id',
            'multiple'      => false,
            'required'      => true,
            'parent'        => 'my_events_invitee_group',
        ]);

        // User
        acf_add_local_field([
            'key'           => 'my_events_invitee_user_field',
            'label'         => __('User', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'user',
            'type'          => 'user',
            'return_format' => 'id',
            'multiple'      => false,
            'required'      => true,
            'parent'        => 'my_events_invitee_group',
        ]);

        // Status
        acf_add_local_field([
            'key'           => 'my_events_invitee_status_field',
            'label'         => __('Status', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'status',
            'type'          => 'select',
            'choices'       => Helpers::getInviteeStatuses(),
            'multiple'      => false,
            'default_value' => 'pending',
            'required'      => true,
            'parent'        => 'my_events_invitee_group',
        ]);

        // Invitation sent
        acf_add_local_field([
            'key'           => 'my_events_invitee_invitation_sent_field',
            'label'         => __('Invitation sent', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'invitation_sent',
            'type'          => 'true_false',
            'default_value' => false,
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
                        'value'    => 'event_invitee_group',
                    ],
                ],
            ],
        ]);

        // Users
        acf_add_local_field([
            'key'           => 'my_events_invitee_group_users_field',
            'label'         => __('Users', 'my-events'),
            'instructions'  => __('', 'my-events'),
            'name'          => 'users',
            'type'          => 'user',
            'return_format' => 'id',
            'multiple'      => true,
            'required'      => true,
            'parent'        => 'my_events_invitee_group_group',
        ]);
    }

    /**
     * Add event location fields
     */
    public static function addLocationFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_event_location_group',
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
            'key'           => 'my_events_event_location_address_field',
            'label'         => __('Address', 'my-events'),
            'instructions'  => __('The geographical location.', 'my-events'),
            'name'          => 'address',
            'type'          => 'text',
            'required'      => true,
            'parent'        => 'my_events_event_location_group',
        ]);
    }
}

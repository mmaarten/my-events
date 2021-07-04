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
        add_action('acf/init', [__CLASS__, 'addEventCreatorFields']);
    }

    public static function addEventCreatorFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_event_creator_event_extra_group',
            'title'    => __('Extra', 'my-events'),
            'fields'   => [],
            'location' => [
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'my-events-event-creator',
                    ],
                ],
            ],
            'menu_order' => 0,
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_creator_event_extra_event_title',
            'label'        => __('Events title', 'my-events'),
            'instructions' => __('The title of the events.', 'my-events'),
            'name'         => 'event_title',
            'type'         => 'text',
            'required'     => true,
            'parent'       => 'my_events_event_creator_event_extra_group',
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_creator_event_extra_post_status',
            'label'        => __('Event status', 'my-events'),
            'instructions' => __('', 'my-events'),
            'name'         => 'post_status',
            'type'         => 'select',
            'choices'      => [
                'draft'   => __('Draft', 'my-events'),
                'publish' => __('Publish', 'my-events'),
            ],
            'default_value' => 'draft',
            'required'      => true,
            'parent'        => 'my_events_event_creator_event_extra_group',
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_creator_event_extra_invitee_status',
            'label'        => __('Invitee status', 'my-events'),
            'instructions' => __('Pending invitees will receive an invitation email per created event.', 'my-events'),
            'name'         => 'invitee_status',
            'type'         => 'select',
            'choices'      => [
                'pending'   => __('pending', 'my-events'),
                'accepted' => __('Accepted', 'my-events'),
            ],
            'default_value' => 'pending',
            'required'     => true,
            'parent'       => 'my_events_event_creator_event_extra_group',
        ]);

        acf_add_local_field_group(array(
            'key' => 'group_60db37be51d92',
            'title' => __('Repeat', 'my-events'),
            'fields' => array(
                array(
                    'key' => 'my_events_event_group_repeat_message',
                    'label' => __('', 'my-events'),
                    'type' => 'message',
                    'message' => esc_html__('Create events based on the following settings:', 'my-events'),
                ),
                array(
                    'key' => 'field_60db37cb348ad',
                    'label' => __('Repeat', 'my-events'),
                    'name' => 'repeat',
                    'type' => 'select',
                    'instructions' => __('Repeat the start and end time.', 'my-events'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        '+1 week' => 'Every week',
                        '+2 week' => 'Every 2 weeks',
                    ),
                    'default_value' => '+1 week',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 0,
                    'return_format' => 'value',
                    'ajax' => 0,
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_60db380c348ae',
                    'label' => __('End repeat', 'my-events'),
                    'name' => 'repeat_end',
                    'type' => 'date_picker',
                    'instructions' => __('', 'my-events'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'first_day' => 1,
                ),
                array(
                    'key' => 'field_60db3842348af',
                    'label' => __('Exclude', 'my-events'),
                    'name' => 'repeat_exclude',
                    'type' => 'repeater',
                    'instructions' => __('Exclude days from repeat settings.', 'my-events'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'table',
                    'button_label' => __('Add day', 'my-events'),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_60db3850348b0',
                            'label' => __('Date', 'my-events'),
                            'name' => 'date',
                            'type' => 'date_picker',
                            'instructions' => '',
                            'required' => 1,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'display_format' => 'd/m/Y',
                            'return_format' => 'Y-m-d',
                            'first_day' => 1,
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'my-events-event-creator',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));
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
                [
                    [
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'my-events-event-creator',
                    ],
                ],
            ],
            'menu_order' => 10,
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_description',
            'label'        => __('Description', 'my-events'),
            'instructions' => __('A brief description of this event.', 'my-events'),
            'name'         => 'description',
            'type'         => 'textarea',
            'rows'         => 3,
            'new_lines'    => 'wpautop',
            'required'     => false,
            'parent'       => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'            => 'my_events_event_is_all_day',
            'label'          => __('All day', 'my-events'),
            'instructions'   => __('This event takes place a whole day.', 'my-events'),
            'name'           => 'is_all_day',
            'type'           => 'true_false',
            'required'       => false,
            'parent'         => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'            => 'my_events_event_date',
            'label'          => __('Date', 'my-events'),
            'instructions'   => __('The day the event takes place.', 'my-events'),
            'name'           => 'date',
            'type'           => 'date_picker',
            'display_format' => get_option('date_format'),
            'return_format'  => 'Y-m-d',
            'first_day'      => get_option('start_of_week'),
            'required'       => true,
            'parent'         => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'            => 'my_events_event_start_time',
            'label'          => __('Start time', 'my-events'),
            'instructions'   => __('The time when the event starts.', 'my-events'),
            'name'           => 'start_time',
            'type'           => 'time_picker',
            'display_format' => get_option('time_format'),
            'return_format'  => 'H:i:s',
            'required'       => true,
            'wrapper'        => ['width' => '50%'],
            'parent'         => 'my_events_event_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_is_all_day',
                        'operator' => '==',
                        'value'    => 0
                    ],
                ],
            ],
        ]);

        acf_add_local_field([
            'key'            => 'my_events_event_end_time',
            'label'          => __('End time', 'my-events'),
            'instructions'   => __('The time when the event ends.', 'my-events'),
            'name'           => 'end_time',
            'type'           => 'time_picker',
            'display_format' => get_option('time_format'),
            'return_format'  => 'H:i:s',
            'required'       => true,
            'wrapper'        => ['width' => '50%'],
            'parent'         => 'my_events_event_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_event_is_all_day',
                        'operator' => '==',
                        'value'    => 0
                    ],
                ],
            ],
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_organisers',
            'label'         => __('Organisers', 'my-events'),
            'instructions'  => __('The organizers receive an email when an invitee accepts or declines an invitation.', 'my-events'),
            'name'          => 'organisers',
            'type'          => 'user',
            'multiple'      => 1,
            'return_format' => 'id',
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_invitees_type',
            'label'         => __('Invitees', 'my-events'),
            'instructions'  => __('The invitees will receive an email about the event when published. They can accept or decline the invitation.', 'my-events'),
            'name'          => 'invitees_type',
            'type'         => 'select',
            'choices'      => [
                'individual' => __('Individual', 'my-events'),
                'group'      => __('Choose from a group', 'my-events'),
            ],
            'required'      => false,
            'parent'        => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'           => 'my_events_event_invitees_individual',
            'label'         => __('Individual', 'my-events'),
            'instructions'  => __('A list of people you want to invite.', 'my-events'),
            'name'          => 'invitees_individual',
            'type'          => 'user',
            'multiple'      => true,
            'return_format' => 'id',
            'required'      => false,
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
            'instructions'  => __('A group of people you like to invite.', 'my-events'),
            'name'          => 'invitees_group',
            'type'          => 'post_object',
            'post_type'     => 'invitee_group',
            'multiple'      => false,
            'return_format' => 'id',
            'required'      => false,
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
            'label'         => __('Limited subscriptions', 'my-events'),
            'instructions'  => __('Limit the amount of invitees who can subscribe.', 'my-events'),
            'name'          => 'limit_subscriptions',
            'type'         => 'true_false',
            'required'      => false,
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
            'instructions' => __('The geographical location of the event.', 'my-events'),
            'name'         => 'location_type',
            'type'         => 'select',
            'choices'      => [
                'input' => __('Custom', 'my-events'),
                'id'    => __('Choose from a list', 'my-events'),
            ],
            'required'     => false,
            'parent'       => 'my_events_event_group',
        ]);

        acf_add_local_field([
            'key'          => 'my_events_event_location_input',
            'label'        => __('Custom', 'my-events'),
            'instructions' => __('', 'my-events'),
            'name'         => 'location_input',
            'type'         => 'text',
            'required'     => false,
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
            'required'      => false,
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
            'parent'        => 'my_events_invitee_group_group',
        ]);
    }
}

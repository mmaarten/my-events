<?php

namespace My\Events;

use My\Events\Posts\Poll;

class Polls
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('init', [__CLASS__, 'registerPostTypes']);
        add_action('admin_menu', [__CLASS__, 'addMenuPage']);
        add_action('acf/init', [__CLASS__, 'addFields']);
        add_action('template_redirect', [__CLASS__, 'processForm']);
        add_action('wp_trash_post', [__CLASS__, 'trashPost']);
        add_action('before_delete_post', [__CLASS__, 'deletePost']);

        add_action('my_events/invitee_group_change', function ($group, $curr_users, $prev_users) {
            $polls = get_posts([
                'post_type'    => 'poll',
                'post_status'  => 'any',
                'numberposts'  => 999,
                'meta_key'     => 'invitee_group',
                'meta_compare' => '=',
                'meta_value'   => $group->ID,
            ]);

            foreach ($polls as $poll) {
                $poll = new Poll($poll);
                $poll->setInvitees($curr_users);
            }
        }, 10, 3);

        add_filter('the_content', function ($return) {
            if (is_singular('poll')) {
                ob_start();
                self::form();
                $return .= ob_get_clean();
            }
            return $return;
        });
    }

    /**
     * Trash post
     *
     * @param int $post_id
     */
    public static function trashPost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event_invitee_group':
                // Switch polls invitee type setting.
                $polls = get_posts([
                    'post_type'    => 'poll',
                    'post_status'  => 'any',
                    'numberposts'  => 999,
                    'meta_key'     => 'invitee_group',
                    'meta_compare' => '=',
                    'meta_value'   => $group->ID,
                ]);
                foreach ($polls as $poll) {
                    $poll = new Poll($poll);
                    $event->updateField('invitee_type', 'individual');
                }
                break;
        }
    }

    /**
     * Delete post
     *
     * @param int $post_id
     */
    public static function deletePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'poll':
                // Remove all event related invitees.
                $poll = new Poll($post_id);
                $poll->setInvitees([]);
                break;
        }
    }

    public static function form($post = null)
    {
        if (! is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();

        $poll = new Poll($post);

        if (! $poll->isInvitee($user_id)) {
            return;
        }

        $times = $poll->getField('times');

        ?>

        <form id="poll-form" method="post">

            <?php wp_nonce_field('poll_form', MY_EVENTS_NONCE_NAME); ?>

            <input type="hidden" name="_poll" value="<?php echo esc_attr($poll->ID); ?>">
            <input type="hidden" name="_user" value="<?php echo esc_attr($user_id); ?>">

            <ul class="list-unstyled">
                <?php

                foreach ($times as $index => $time) {
                    $users = $poll->getTimeUsers($time['start'], $time['end']);
                    $users = wp_list_pluck($users, 'display_name', 'ID');

                    printf(
                        '<li class="mb-3"><label class="mb-0"><input type="checkbox" name="times[]" value="%1$s"%2$s> %3$s</label>%4$s</li>',
                        esc_attr($index),
                        checked(isset($users[$user_id]), true, false),
                        esc_html__($poll->getTimeFromUntil($index)),
                        $users ? sprintf('<br><small>%s</small>', esc_html(implode(', ', $users))) : ''
                    );
                }

                ?>
            </ul>

            <p>
                <input type="submit" class="btn btn-primary" value="<?php esc_attr_e('Submit', 'my-events'); ?>">
            </p>

        </form>


        <?php
    }

    public static function processForm()
    {
        if (empty($_POST[MY_EVENTS_NONCE_NAME])) {
            return;
        }

        if (! wp_verify_nonce($_POST[MY_EVENTS_NONCE_NAME], 'poll_form')) {
            return;
        }

        if (! is_user_logged_in()) {
            return;
        }

        $poll_id = $_POST['_poll'];
        $user_id = $_POST['_user'];
        $selected_times = isset($_POST['times']) && is_array($_POST['times']) ? $_POST['times'] : [];

        if (! $user_id || $user_id != get_current_user_id()) {
            return;
        }

        if (! $poll_id || get_post_type($poll_id) != 'poll') {
            return;
        }

        $poll = new Poll($poll_id);
        $times = $poll->getField('times');

        foreach ($times as $index => $time) {
            if (in_array($index, $selected_times)) {
                $poll->addTimeUser($user_id, $time['start'], $time['end']);
            } else {
                $poll->removeTimeUser($user_id, $time['start'], $time['end']);
            }
        }
    }

    public static function savePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'poll':
                $poll = new Poll($post_id);

                // Invitees
                $invitee_type = $poll->getField('invitee_type');

                $invitees = [];

                if ($invitee_type == 'individual') {
                    $invitees = $poll->getField('individual_invitees');
                }

                if ($invitee_type == 'group') {
                    $group_id = $poll->getField('invitee_group');
                    if ($group_id && get_post_type($group_id)) {
                        $group = new Post($group);
                        $invitees = $group->getField('users');
                    }
                }

                $poll->setInvitees($invitees);

                break;
        }
    }

    /**
     * Add fields
     */
    public static function addFields()
    {
        acf_add_local_field_group([
            'key'      => 'my_events_poll_group',
            'title'    => __('General', 'my-events'),
            'fields'   => [],
            'location' => [
                [
                    [
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'poll',
                    ],
                ],
            ],
        ]);

        // Invitee type
        acf_add_local_field([
            'key'           => 'my_events_poll_invitee_type_field',
            'label'         => __('Invitees', 'my-events'),
            'instructions'  => __('Select the people you would like to invite.', 'my-events'),
            'name'          => 'invitee_type',
            'type'          => 'select',
            'choices'       => [
                'individual' => __('Individual', 'my-events'),
                'group'      => __('Choose from a group', 'my-events'),
            ],
            'default_value' => 'individual',
            'required'      => true,
            'parent'        => 'my_events_poll_group',
        ]);

        // Individual invitees
        acf_add_local_field([
            'key'               => 'my_events_poll_individual_invitees_field',
            'label'             => __('Individual invitees', 'my-events'),
            'instructions'      => __('', 'my-events'),
            'name'              => 'individual_invitees',
            'type'              => 'user',
            'multiple'          => true,
            'return_format'     => 'id',
            'required'          => true,
            'parent'            => 'my_events_poll_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_poll_invitee_type_field',
                        'operator' => '==',
                        'value'    => 'individual',
                    ],
                ],
            ],
        ]);

        // Invitee group
        acf_add_local_field([
            'key'               => 'my_events_poll_invitee_group_field',
            'label'             => __('Invitee group', 'my-events'),
            'instructions'      => __('', 'my-events'),
            'name'              => 'invitee_group',
            'type'              => 'post_object',
            'post_type'         => 'invitee_group',
            'multiple'          => false,
            'required'          => true,
            'allow_null'        => true,
            'parent'            => 'my_events_poll_group',
            'conditional_logic' => [
                [
                    [
                        'field'    => 'my_events_poll_invitee_type_field',
                        'operator' => '==',
                        'value'    => 'group',
                    ],
                ],
            ],
        ]);

        // Times
        acf_add_local_field([
            'key'          => 'my_events_poll_times_field',
            'label'        => __('Times', 'my-events'),
            'instructions' => __('', 'my-events'),
            'name'         => 'times',
            'type'         => 'repeater',
            'required'     => true,
            'parent'       => 'my_events_poll_group',
        ]);

        // Start time
        acf_add_local_field([
            'key'            => 'my_events_poll_time_start_field',
            'label'          => __('Start', 'my-events'),
            'instructions'   => __('', 'my-events'),
            'name'           => 'start',
            'type'           => 'date_time_picker',
            'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
            'return_format'  => 'Y-m-d H:i:s',
            'first_day'      => get_option('start_of_week', 0),
            'required'       => true,
            'parent'         => 'my_events_poll_times_field',
        ]);

        // End time
        acf_add_local_field([
            'key'            => 'my_events_poll_times_end_field',
            'label'          => __('End', 'my-events'),
            'instructions'   => __('', 'my-events'),
            'name'           => 'end',
            'type'           => 'date_time_picker',
            'display_format' => get_option('date_format') . ' ' . get_option('time_format'),
            'return_format'  => 'Y-m-d H:i:s',
            'first_day'      => get_option('start_of_week', 0),
            'required'       => true,
            'parent'         => 'my_events_poll_times_field',
        ]);
    }

    /**
     * Add menu page
     */
    public static function addMenuPage()
    {
        add_menu_page(
            __('Polls', 'my-events'),
            __('Polls', 'my-events'),
            'edit_posts',
            'my-events-polls',
            '',
            'dashicons-admin-post',
            40
        );
    }

    /**
     * Register post types
     */
    public static function registerPostTypes()
    {
        register_post_type('poll', [
            'labels'             => PostTypes::getLabels(__('Polls', 'my-events'), __('Poll', 'my-events')),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'my-events-polls',
            'query_var'          => true,
            'rewrite'            => ['event'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'thumbnail', 'comments'],
            'taxonomies'         => ['event_tag'],
        ]);

        register_post_type('poll_invitee', [
            'labels'             => PostTypes::getLabels(__('Invitees', 'my-events'), __('Invitee', 'my-events')),
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => current_user_can('administrator') ? 'my-events-polls' : false,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title'],
            'capabilities'       => [
                'edit_post'          => 'update_core',
                'read_post'          => 'update_core',
                'delete_post'        => 'update_core',
                'edit_posts'         => 'update_core',
                'edit_others_posts'  => 'update_core',
                'delete_posts'       => 'update_core',
                'publish_posts'      => 'update_core',
                'read_private_posts' => 'update_core'
            ],
        ]);
    }
}

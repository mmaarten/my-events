<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Events
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('wp_trash_post', [__CLASS__, 'trashPost']);
        add_action('before_delete_post', [__CLASS__, 'deletePost']);
        add_action('delete_user', [__CLASS__, 'deleteUser']);
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);

        add_filter('acf/load_value/key=my_events_event_individual_invitees_field', [__CLASS__, 'populateIndividualInviteesField'], 10, 3);
    }

    /**
     * Add meta boxes
     *
     * @param string $post_type
     */
    public static function addMetaBoxes($post_type)
    {
        switch ($post_type) {
            case 'event':
                add_meta_box(
                    'my-events-invitees',
                    __('Invitees', 'my-events'),
                    [__CLASS__, 'renderInvitees'],
                    $post_type,
                    'side',
                    'high'
                );
                add_meta_box(
                    'my-events-overlapping-events',
                    __('Overlapping events', 'my-events'),
                    [__CLASS__, 'renderOverlappingEvents'],
                    $post_type,
                    'side',
                    'high'
                );
                break;
        }
    }

    /**
     * Render overlapping events
     *
     * @param WP_Post $post
     */
    public static function renderOverlappingEvents($post)
    {
        $event = new Event($post);

        $start = $event->getStartTime('Y-m-d H:i:s');
        $end   = $event->getEndTime('Y-m-d H:i:s');

        $events = Model::getEventsBetween($start, $end, [
            'exclude' => [$event->ID],
            'fields'  => 'ids',
        ]);

        if (! $events) {
            Helpers::adminNotice(__('No overlapping events found.', 'my-events'), 'info', true);
            return;
        }

        echo '<ul>';

        foreach ($events as $event) {
            $event = new Event($event);
            printf(
                '<li><a href="%1$s">%2$s</a><br><small>%3$s</small></li>',
                get_permalink($event->ID),
                esc_html($event->post_title),
                esc_html($event->getTimeFromUntil())
            );
        }

        echo '</ul>';
    }

    /**
     * Render invitees
     *
     * @param WP_Post $post
     */
    public static function renderInvitees($post)
    {
        $event = new Event($post);

        if (! $event->hasInvitees()) {
            Helpers::adminNotice(__('No invitees found.', 'my-events'), 'info', true);
            return;
        }

        $statuses     = Helpers::getInviteeStatuses();
        $status_order = ['accepted', 'declined', 'pending'];
        $statuses     = array_merge(array_flip($status_order), $statuses);

        echo '<ul>';

        foreach ($statuses as $status => $status_display) {
            $users = $event->getInviteesUsers($status, ['orderby' => 'display_name', 'order' => 'ASC']);
            foreach ($users as $user) {
                $invitee = $event->getInviteeByUser($user->ID);
                printf(
                    '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
                    get_edit_post_link($invitee->ID),
                    esc_html($user->display_name),
                    esc_html($status_display)
                );
            }
        }

        echo '</ul>';
    }

    /**
     * Populate individual invitees field
     *
     * @param array $value
     * @param int   $post_id
     * @param array $field
     * @return array
     */
    public static function populateIndividualInviteesField($value, $post_id, $field)
    {
        if (get_post_type($post_id) != 'event') {
            return $value;
        }

        if (did_action('acf/save_post')) {
            return $value;
        }

        $event = new Event($post_id);

        return $event->getInviteesUsers(null, ['fields' => 'ID']);
    }

    /**
     * Save post
     *
     * @param int $post_id
     */
    public static function savePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                $event = new Event($post_id);
                $invitee_type = $event->getField('invitee_type');
                $invitees = [];

                if ($invitee_type == 'individual') {
                    $invitees = $event->getField('individual_invitees', false);
                }

                if ($invitee_type == 'group') {
                    $group = new Post($event->getField('invitee_group', false));
                    $invitees = $group->getField('users', false);
                }

                $event->setInvitees($invitees);
                $event->deleteField('individual_invitees');
                break;
            case 'invitee_group':
                $group = new Post($post_id);
                $prev_users = $group->getField('_prev_users');
                $curr_users = $group->getField('users', false);

                if (! is_array($prev_users)) {
                    $prev_users = $curr_users;
                }

                $group->updateField('_prev_users', $curr_users);

                if ($prev_users !== $curr_users) {
                    $events = self::getEventsByInviteeGroup($group->ID);
                    foreach ($events as $event) {
                        $event = new Event($event);
                        if (! $event->isOver()) {
                            $event->setInvitees($curr_users);
                        }
                    }
                }
                break;
        }
    }

    /**
     * Trash post
     *
     * @param int $post_id
     */
    public static function trashPost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'invitee_group':
                // Switch events invitee type setting.
                $events = Model::getEventsByInviteeGroup($post_id, ['post_status' => 'any']);
                foreach ($events as $event) {
                    $event = new Event($event);
                    $event->updateField('invitee_type', 'individual');
                }
                break;
            case 'event_location':
                // Switch events location type setting.
                $location = new Post($post_id);
                $events = Model::getEventsByLocation($location->ID, ['post_status' => 'any']);
                foreach ($events as $event) {
                    $event = new Event($event);
                    $event->updateField('location_type', 'custom');
                    $event->updateField('custom_location', $location->getField('address', false));
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
            case 'event':
                // Remove all event related invitees.
                $event = new Event($post_id);
                $event->setInvitees([]);
                break;
        }
    }

    /**
     * Delete user
     *
     * @param int $user_id
     */
    public static function deleteUser($user_id)
    {
        // Remove all user related invitees.
        $invitees = Model::getInviteesByUser($user_id, ['post_status' => 'any']);
        foreach ($invitees as $invitee) {
            $invitee = new Invitee($invitee);
            $event_id = $invitee->getEvent();
            if ($event_id) {
                $event = new Event($event_id);
                $event->removeInvitee($user_id);
            } else {
                wp_delete_post($invitee, true);
            }
        }

        // TODO : update invitee group 'users' setting?
    }
}

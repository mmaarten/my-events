<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Events
{
    public static function init()
    {
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('wp_trash_post', [__CLASS__, 'trashPost']);
        add_action('before_delete_post', [__CLASS__, 'beforeDeletePost']);
        add_action('transition_post_status', [__CLASS__, 'transitionPostStatus'], 10, 3);
        add_action('delete_user', [__CLASS__, 'beforeDeleteUser'], 10, 3);
        add_action('admin_notices', [__CLASS__, 'adminNotices']);
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
        add_action('admin_init', [__CLASS__, 'maybeDetachEventFromGroup']);

        add_filter('acf/load_field/key=my_events_event_group_events', [__CLASS__, 'renderEventGroupEvents']);
        add_filter('acf/load_value/key=my_events_event_invitees_individual', [__CLASS__, 'updateInvitiesField'], 10, 3);
        add_filter('acf/load_field/key=my_events_event_invitees_list', [__CLASS__, 'renderInvities'], 10, 2);
        add_filter('post_class', [__CLASS__, 'postClass'], 10, 3);
        add_filter('admin_body_class', [__CLASS__, 'adminBodyClass']);

        add_filter('my_events/invitee_default_status', [__CLASS__, 'inviteeDefaultStatus'], 10, 2);
    }

    public static function inviteeDefaultStatus($status, $event)
    {
        if ($event->isGrouped()) {
            $status = 'accepted';
        }

        return $status;
    }

    public static function isEditableEvent($event_id)
    {
        $event = new Event($event_id);

        return ! $event->isGrouped();
    }

    public static function addMetaBoxes($post_type)
    {
        $screen = get_current_screen();

        if ($screen->base === 'post') {

            $post_id = $_GET['post'];

            switch ($screen->post_type) {
                case 'event':
                    if (! self::isEditableEvent($post_id)) {
                        remove_meta_box('submitdiv', $post_type, 'side');
                    }
                    break;
            }
        }
    }

    public static function getEventClasses($post_id)
    {
        $event = new Event($post_id);

        $classes = [];

        if ($event->isOver()) {
            $classes[] = 'is-event-over';
        }

        if ($event->isPrivate()) {
            $classes[] = 'is-private-event';
        }

        if ($event->isGrouped()) {
            $classes[] = 'is-grouped-event';
        }

        if (is_admin() && self::isEditableEvent($event->ID)) {
            $classes[] = 'is-editable-event';
        }

        if (is_user_logged_in()) {
            $invitee = $event->getInviteeByUser(get_current_user_id());
            if ($invitee) {
                $classes[] = 'is-invitee';
                $classes[] = sprintf('is-invitee-%s', $invitee->getStatus());
            }
        }

        return apply_filters('my_events/event_class', $classes, $event->ID);
    }

    public static function getInviteeClasses($post_id)
    {
        $invitee = new Invitee($post_id);

        $event = null;
        $event_id = $invitee->getEvent();
        if ($event_id && get_post_type($event_id)) {
            $event = new Event($event_id);
        }

        $classes[] = sprintf('is-invitee-%s', $invitee->getStatus());

        if ($event) {
            if ($event->isGrouped()) {
                $classes[] = 'is-grouped-event';
            }
        }

        return $classes;
    }

    public static function savePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                // Update invitees
                self::setInviteesFromSettingsFields($post_id);
                break;
            case 'invitee_group':
                // Update invitees
                self::updateInviteesFromInviteeGroup($post_id);
                break;
            case 'event_group':
                $group = new Post($post_id);
                $start      = $group->getField('start');
                $end        = $group->getField('end');
                $repeat     = $group->getField('repeat');
                $repeat_end = $group->getField('repeat_end');
                $repeat_exclude = $group->getField('repeat_exclude');

                if (! is_array($repeat_exclude)) {
                    $repeat_exclude = [];
                }

                $repeat_exclude = wp_list_pluck($repeat_exclude, 'date', 'date');

                $times = Helpers::getTimesRepeat($start, $end, $repeat_end, $repeat, $repeat_exclude);
                $times = array_slice($times, 0, 50);

                $processed = [];

                if (is_array($times)) {
                    foreach ($times as $time) {
                        $processed[] = Model::createGroupEvent($group->ID, $time['start'], $time['end']);
                    }
                }

                $delete = Model::getEventsByEventGroup($group->ID, [
                    'exclude'      => $processed,
                    'post_status'  => 'any',
                ]);

                foreach ($delete as $event) {
                    wp_delete_post($event->ID, true);
                }
                break;
        }
    }

    public static function trashPost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                $event = new Event($post_id);
                if (! $event->isOver() && $event->getField('was_published')) {
                    do_action('my_events/event_cancelled', $event);
                }
                break;

            case 'invitee_group':
                $events = Model::getEventsByInviteeGroup($post_id);
                foreach ($events as $event) {
                    $event = new Event($event);
                    // Set invitee type to 'individual'.
                    // The field 'invitees_individual' is automatically populated. So we dont need to update it.
                    $event->updateField('invitees_type', 'individual');
                }
                $groups = Model::getEventGroupsByInviteeGroup($post_id);
                foreach ($groups as $group) {
                    $group = new Post($group);
                    // Set invitee type to 'individual'.
                    // The field 'invitees_individual' is automatically populated. So we dont need to update it.
                    $group->updateField('invitees_type', 'individual');
                }
                break;
            case 'event_location':
                // Get address setting from location
                $location = new Post($post_id);
                $address = $location->getField('address');

                $events = Model::getEventsByLocation($post_id);
                foreach ($events as $event) {
                    // Switch to 'input' and save location address.
                    $event = new Event($event);
                    $event->updateField('location_type', 'input');
                    $event->updateField('location_input', $address);
                }

                $groups = Model::getEventGroupsByLocation($post_id);
                foreach ($groups as $group) {
                    // Switch to 'input' and save location address.
                    $group = new Post($group);
                    $group->updateField('location_type', 'input');
                    $group->updateField('location_input', $address);
                }

                break;
            case 'event_group':
                // Get all events related to this post.
                $events = Model::getEventsByEventGroup($post_id, ['post_status' => 'any']);
                // Move events to trash.
                foreach ($events as $event) {
                    wp_trash_post($event->ID);
                }
                break;
        }
    }

    public static function beforeDeletePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                // Remove all event related invitees.
                $event = new Event($post_id);
                $event->setInvitees([]);
                break;
            case 'event_group':
                // Get all events related to this post.
                $events = Model::getEventsByEventGroup($post_id, ['post_status' => 'any']);
                // Delete events.
                foreach ($events as $event) {
                    wp_delete_post($event->ID);
                }
                break;
        }
    }

    public static function transitionPostStatus($new_status, $old_status, $post)
    {
        switch (get_post_type($post)) {
            case 'event':
                $event = new Event($post);
                if ($new_status === 'publish' || $old_status === 'publish') {
                    $event->updateField('was_published', true);
                }
                break;
            case 'event_group':
                if ($new_status !== 'trash') {
                    $events = Model::getEventsByEventGroup($post->ID, ['post_status' => $old_status]);
                    foreach ($events as $event) {
                        wp_update_post(['ID' => $event->ID, 'post_status' => $new_status]);
                    }
                }
                break;
        }
    }

    public static function beforeDeleteUser($user_id, $reassign, $user)
    {
        // Get invitees by user.
        $invitees = Model::getInviteesByUser($user_id);

        // Remove user related invitees.
        foreach ($invitees as $invitee) {
            $invitee = new Invitee($invitee);
            $event_id = $invitee->getEvent();
            if ($event_id && get_post_type($event_id)) {
                $event = new Event($event_id);
                $event->removeInvitee($invitee->ID);
            } else {
                wp_delete_post($invitee->ID, true);
            }
        }
    }

    public static function getDetachEventFromGroupURL($event_id)
    {
        return add_query_arg([
            MY_EVENTS_NONCE_NAME => wp_create_nonce('detach_event_from_group'),
            'event'    => $event_id,
            'redirect' => get_edit_post_link($event_id),
        ], get_admin_url());
    }

    public static function maybeDetachEventFromGroup()
    {
        if (empty($_GET[MY_EVENTS_NONCE_NAME])) {
            return;
        }

        if (! wp_verify_nonce($_GET[MY_EVENTS_NONCE_NAME], 'detach_event_from_group')) {
            return;
        }

        $event_id = $_GET['event'];
        $redirect = add_query_arg('action', 'edit', $_GET['redirect']);

        self::detachEventFromGroup($event_id);

        wp_safe_redirect($redirect);
    }

    public static function detachEventFromGroup($event_id)
    {
        $event = new Event($event_id);

        if ($event->isOver() || ! $event->isGrouped()) {
            return false;
        }

        $group = new Post($event->getGroup());

        $exclude = $event->getStartTime('Y-m-d');
        $repeat_exclude = $group->getField('repeat_exclude');

        if (! is_array($repeat_exclude)) {
            $repeat_exclude = [];
        }

        // Check if already added
        if (wp_filter_object_list($repeat_exclude, ['date' => $exclude])) {
            return;
        }

        $repeat_exclude[] = [
            'date' => $exclude,
        ];

        $group->updateField('repeat_exclude', $repeat_exclude);

        $event->updateField('group', '');

        return true;
    }

    public static function getInviteesFromSettingsField($event_id)
    {
        // Get event.
        $event = new Event($event_id);

        // Get invitee type.
        $type = $event->getField('invitees_type');

        $user_ids = [];

        // Get user ids from individual invitees field
        if ($type === 'individual') {
            $user_ids = $event->getField('invitees_individual');
        }

        // Get user ids from invitees group
        if ($type === 'group') {
            $group_id = $event->getField('invitees_group');
            $group = new Post($group_id);
            $user_ids = $group->getField('users');
        }

        if (! $user_ids || ! is_array($user_ids)) {
            $user_ids = [];
        }

        // Make sure all users exist.
        return get_users([
            'include' => $user_ids,
            'fields'  => 'ID',
        ]);
    }

    public static function setInviteesFromSettingsFields($event_id)
    {
        // Get event.
        $event = new Event($event_id);

        $user_ids = self::getInviteesFromSettingsField($event->ID);

        // Create invitees
        $event->setInvitees($user_ids);

        // Remove settings (will be refilled with invitees from our custom post type).
        $event->deleteField('invitees_individual');
    }

    public static function updateInvitiesField($value, $post_id, $field)
    {
        // Stop when value. We need to access it on post save.
        if ($value) {
            return $value;
        }

        // Populates field with invitees from our post type.
        if (get_post_type($post_id) === 'event') {
            $event = new Event($post_id);
            return $event->getInviteesUsers(null, ['fields' => 'ID']);
        }

        return $value;
    }

    public static function updateInviteesFromInviteeGroup($group_id)
    {
        $group = new Post($group_id);

        // Get previous users

        $prev_users = $group->getField('prev_users');

        if (! is_array($prev_users)) {
            $prev_users = [];
        }

        // Get current users

        $current_users = $group->getField('users');

        if (! is_array($current_users)) {
            $current_users = [];
        }

        // Check for differences

        $added_users   = array_diff($current_users, $prev_users);
        $removed_users = array_diff($prev_users, $current_users);

        // Add invitees

        foreach ($added_users as $user_id) {
            $events = Model::getEventsByInviteeGroup($group->ID);

            foreach ($events as $event) {
                $event = new Event($event);

                if (! $event->isOver()) {
                    $event->addInvitee($user_id);
                }
            }
        }

        // Remove invitees

        foreach ($removed_users as $user_id) {
            $events = Model::getEventsByInviteeGroup($group->ID);

            foreach ($events as $event) {
                $event = new Event($event);

                if (! $event->isOver()) {
                    $event->removeInviteeByUser($user_id);
                }
            }
        }

        // Save current users

        $group->updateField('prev_users', $current_users);
    }

    public static function renderInvities($field)
    {
        $screen = get_current_screen();

        if ($screen->id !== 'event') {
            return $field;
        }

        $event = new Event($_GET['post']);

        $field['message'] = Helpers::loadTemplate('event-edit-invitees', [
            'event' => $event,
        ], true);

        return $field;
    }

    public static function renderEventGroupEvents($field)
    {
        $screen = get_current_screen();

        if ($screen->id !== 'event_group' || ! isset($_GET['post'])) {
            return $field;
        }

        $group_id = $_GET['post'];

        $events = Model::getEventsByEventGroup($group_id, ['fields' => 'ids']);
        $events = Model::orderEventsByStartTime($events);

        $field['message'] = Helpers::loadTemplate('event-group-edit-events', [
            'events' => $events,
        ], true);

        return $field;
    }

    public static function adminNotices()
    {
        $screen = get_current_screen();

        switch ($screen->id) {
            case 'event':
                $event = new Event($_GET['post']);

                if ($event->isOver()) {
                    echo Helpers::adminNotice(__('This event is over.', 'my-events'), 'warning');
                }

                if ($event->isGrouped()) {
                    $group = new Post($event->getGroup());

                    $message = sprintf(
                        esc_html__('This event belongs to the group %s.', 'my-events'),
                        sprintf('<a href="%1$s">%2$s</a>', get_edit_post_link($group->ID), esc_html($group->post_title))
                    );

                    if (! $event->isOver()) {
                        $button = sprintf(
                            '<a href="%1$s">%2$s</a>',
                            esc_url(self::getDetachEventFromGroupURL($event->ID)),
                            esc_html__('here', 'my-events')
                        );

                        $message .= ' ' . sprintf(
                            esc_html__('Click %s to detach from group.', 'my-events'),
                            $button
                        );
                    }

                    echo Helpers::adminNotice($message, 'warning', false, true);
                }

                break;
        }
    }

    public static function postClass($classes, $class, $post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                $classes = array_merge($classes, self::getEventClasses($post_id));
                break;
            case 'invitee':
                $classes = array_merge($classes, self::getInviteeClasses($post_id));
                break;
        }

        return $classes;
    }

    public static function adminBodyClass($classes)
    {
        $screen = get_current_screen();

        if ($screen->base !== 'post') {
            return $classes;
        }

        $post_id = $_GET['post'];

        switch ($screen->post_type) {
            case 'event':
                $classes .= ' ' . implode(' ', self::getEventClasses($post_id));
                break;
            case 'invitee':
                $classes .= ' ' . implode(' ', self::getInviteeClasses($post_id));
                break;
        }

        return $classes;
    }
}

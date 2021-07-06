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
        add_action('delete_user', [__CLASS__, 'beforeDeleteUser'], 10, 3);
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
        add_action('admin_notices', [__CLASS__, 'adminNotices']);

        add_filter('acf/load_value/key=my_events_event_invitees_individual', [__CLASS__, 'updateInvitiesField'], 10, 3);
        add_filter('post_class', [__CLASS__, 'postClass'], 10, 3);
        add_filter('admin_body_class', [__CLASS__, 'adminBodyClass']);
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

        if (is_user_logged_in()) {
            $invitee = $event->getInviteeByUser(get_current_user_id());
            if ($invitee) {
                $classes[] = 'is-invitee';
                $classes[] = sprintf('is-invitee-%s', $invitee->getStatus());
            }
        }

        return apply_filters('my_events/event_class', $classes, $event);
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

        return apply_filters('my_events/invitee_class', $classes, $invitee, $event);
    }

    public static function updateEventTime($post_id)
    {
        $event = new Event($post_id);

        // Update time.
        $date       = $event->getField('date');
        $start_time = $event->getField('start_time');
        $end_time   = $event->getField('end_time');

        $event->updateField('start', "$date $start_time");
        $event->updateField('end', "$date $end_time");
    }

    public static function savePost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'event':
                self::updateEventTime($post_id);
                // Update invitees.
                self::setInviteesFromSettingsFields($post_id);
                break;
            case 'invitee_group':
                // Update invitees.
                self::updateInviteesFromInviteeGroup($post_id);
                break;
        }
    }

    public static function trashPost($post_id)
    {
        switch (get_post_type($post_id)) {
            case 'invitee_group':
                $events = Model::getEventsByInviteeGroup($post_id, ['post_status' => 'any']);
                foreach ($events as $event) {
                    $event = new Event($event);
                    // Set invitee type to 'individual'.
                    // The field 'invitees_individual' is automatically populated. So we dont need to update it.
                    $event->updateField('invitees_type', 'individual');
                }
                break;
            case 'event_location':
                // Get address setting from location
                $location = new Post($post_id);
                $address = $location->getField('address');
                $events = Model::getEventsByLocation($post_id, ['post_status' => 'any']);
                foreach ($events as $event) {
                    // Switch to 'input' and save location address.
                    $event = new Event($event);
                    $event->updateField('location_type', 'input');
                    $event->updateField('location_input', $address);
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
        }
    }

    public static function beforeDeleteUser($user_id, $reassign, $user)
    {
        // Get invitees by user.
        $invitees = Model::getInviteesByUser($user_id);

        // Remove all user related invitees.
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

    public static function getInviteesFromSettingsField($event_id)
    {
        // Get event.
        $event = new Event($event_id);

        // Get invitee type.
        $type = $event->getField('invitees_type');

        $user_ids = [];

        // Get user ids from individual invitees field
        if ($type == 'individual') {
            $user_ids = $event->getField('invitees_individual');
        }

        // Get user ids from invitees group
        if ($type == 'group') {
            $group_id = $event->getField('invitees_group');
            if ($group_id && get_post_type($group_id)) {
                $group = new Post($group_id);
                $user_ids = $group->getField('users');
            }
        }

        if (! $user_ids || ! is_array($user_ids)) {
            return [];
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
        // Don't change field value on post save. We need to access the field settings.
        if (did_action('acf/save_post')) {
            return $value;
        }

        // Populates field with invitees from our post type.
        if (get_post_type($post_id) == 'event') {
            $event = new Event($post_id);
            return $event->getInviteesUsers(null, ['fields' => 'ID']);
        }

        // Return.
        return $value;
    }

    public static function updateInviteesFromInviteeGroup($group_id)
    {
        $group = new Post($group_id);

        // Get previous users

        $prev_users = $group->getMeta('prev_users', true);

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

        $events = Model::getEventsByInviteeGroup($group->ID, ['post_status' => 'any']);

        // Add invitees

        foreach ($added_users as $user_id) {
            foreach ($events as $event) {
                $event = new Event($event);
                if (! $event->isOver()) {
                    $event->addInvitee($user_id);
                }
            }
        }

        // Remove invitees

        foreach ($removed_users as $user_id) {
            foreach ($events as $event) {
                $event = new Event($event);
                if (! $event->isOver()) {
                    $event->removeInviteeByUser($user_id);
                }
            }
        }

        // Save current users

        $group->updateMeta('prev_users', $current_users);
    }

    public static function renderInvities($post)
    {
        $event = new Event($post);

        Helpers::loadTemplate('event-edit-invitees', [
            'event' => $event,
        ]);
    }

    public static function addMetaBoxes($post_type)
    {
        $screen = get_current_screen();

        // Only show metabox when there are invitees.
        if ($screen->base = 'post' && $screen->post_type == 'event') {
            if ($screen->action == 'add') {
                return;
            }

            if (isset($_GET['post'])) {
                $event = new Event($_GET['post']);

                $invitees = $event->getInvitees();

                if (! $invitees) {
                    return;
                }
            }

            add_meta_box(
                'my-events-invitees',
                __('Invitees', 'my-events'),
                [__CLASS__, 'renderInvities'],
                $screen,
                'side'
            );
        }
    }

    public static function adminNotices()
    {
        $screen = get_current_screen();

        if ($screen->base != 'post' || ! isset($_GET['post'])) {
            return;
        }

        switch ($screen->post_type) {
            case 'event':
                $event = new Event($_GET['post']);

                if ($event->isOver()) {
                    echo Helpers::adminNotice(__('This event is over.', 'my-events'), 'warning');
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

        if ($screen->base != 'post' || ! isset($_GET['post'])) {
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

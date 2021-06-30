<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;

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

        add_action('pre_get_posts', [__CLASS__, 'excludePrivateEvents']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'checkAccessEventEdit']);
       
        add_filter('post_class', [__CLASS__, 'postClass'], 10, 3);
        add_filter('admin_body_class', [__CLASS__, 'adminBodyClass']);
        
        add_action('add_meta_boxes', [__CLASS__, 'addMetaBoxes']);
        add_action('admin_init', [__CLASS__, 'maybeDetachEventFromGroup']);

        add_filter('acf/load_value/key=my_events_event_invitees_individual', [__CLASS__, 'updateInvitiesField'], 10, 3);
        add_filter('acf/load_field/key=my_events_event_invitees_list', [__CLASS__, 'renderInvities'], 10, 2);
    }

    public static function addMetaBoxes($post_type) {
        switch ($post_type) {
            case 'event':
                $event = new Event($_GET['post']);
                if ($event->getMeta('group', true)) {
                    remove_meta_box('submitdiv', $post_type, 'side');
                }
                break;
        }
    }

    public static function getEventClasses($post_id)
    {
        $event = new Event($post_id);

        $classes = [];

        $classes[] = 'event-id-' . $event->ID;

        if ($event->isOver()) {
            $classes[] = 'is-event-over';
        }

        if ($event->isPrivate()) {
            $classes[] = 'is-private-event';
        }

        if ($event->getMeta('group', true)) {
            $classes[] = 'is-grouped-event';
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
                $start      = $group->getMeta('start', true);
                $end        = $group->getMeta('end', true);
                $repeat     = $group->getMeta('repeat', true);
                $repeat_end = $group->getMeta('repeat_end', true);
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
                if (! $event->isOver() && $event->getMeta('was_published', true)) {
                    do_action('my_events/event_cancelled', $event);
                }
                break;

            case 'invitee_group':
                $events = Model::getEventsByInviteeGroup($post_id);
                foreach ($events as $event) {
                    $event = new Event($event);
                    // Set invitee type to 'individual'.
                    // The field 'invitees_individual' is automatically populated. So we dont need to update it.
                    $event->updateMeta('invitees_type', 'individual');
                }
                break;
            case 'event_location':
                // Get address setting from location
                $location = new Post($post_id);
                $address = $location->getMeta('address', true);
                $events = Model::getEventsByLocation($post_id);
                foreach ($events as $event) {
                    // Switch to 'input' and save location address.
                    $event = new Event($event);
                    $event->updateMeta('location_type', 'input');
                    $event->updateMeta('location_input', $address);
                }
                break;
            case 'event_group':
                $events = Model::getEventsByEventGroup($post_id, ['post_status' => 'any']);
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
                $events = Model::getEventsByEventGroup($post_id, ['post_status' => 'any']);
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
                    $event->updateMeta('was_published', true);
                }
                break;
            case 'event_group':
                if ($new_status !== 'trash') {
                    $events = Model::getEventsByEventGroup($post->ID, ['post_status' => 'any']);
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

    public static function setInviteesFromSettingsFields($event_id)
    {
        // Get event.
        $event = new Event($event_id);

        $type = $event->getMeta('invitees_type', true);

        $user_ids = [];

        // Get user ids from individual invitees field
        if ($type === 'individual') {
            $user_ids = $event->getMeta('invitees_individual', true);
        }

        // Get user ids from invitees group
        if ($type === 'group') {
            $group_id = $event->getMeta('invitees_group', true);
            $group = new Post($group_id);
            $user_ids = $group->getMeta('users', true);
        }

        if (! is_array($user_ids)) {
            $user_ids = [];
        }

        // Create invitees
        $event->setInvitees($user_ids);

        // Remove settings (will be refilled with invitees from our custom post type).
        $event->deleteMeta('invitees_individual');
    }

    public static function updateInvitiesField($value, $post_id, $field)
    {
        // Stop when value. We need to access it on post save.
        if ($value) {
            return $value;
        }

        // Populates field with invitees from our post type.

        $event = new Event($post_id);

        return $event->getInviteesUsers(null, ['fields' => 'ID']);
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

        $current_users = $group->getMeta('users', true);

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

        $group->updateMeta('prev_users', $current_users);
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

    public static function adminNotices()
    {
        $screen = get_current_screen();

        switch ($screen->id) {
            case 'event':
                $event = new Event($_GET['post']);

                if ($event->isOver()) {
                    Helpers::adminNotice(__('This event is over.', 'my-events'), 'warning');
                }

                if ($event->isPrivate()) {
                    Helpers::adminNotice(__('This event is only accessible to organisers and invitees of this event.', 'my-events'));
                }

                if ($event->getMeta('group', true)) {
                    $group = new Post($event->getMeta('group', true));

                    $message = sprintf(
                        esc_html__('This event belongs to the group %s.', 'my-events'),
                        sprintf('<a href="%1$s">%2$s</a>', get_edit_post_link($group->ID), esc_html($group->post_title))
                    );

                    if (! $event->isOver()) {
                        $detach_url = add_query_arg([
                            MY_EVENTS_NONCE_NAME => wp_create_nonce('detach_event_from_group'),
                            'event'    => $event->ID,
                            'redirect' => get_edit_post_link($event->ID),
                        ], get_admin_url());

                        $button = sprintf(
                            '<a href="%1$s">%2$s</a>',
                            esc_url($detach_url),
                            esc_html__('here', 'my-events')
                        );

                        $message .= ' ' . sprintf(
                            esc_html__('Click %s to detach from group.', 'my-events'),
                            $button
                        );
                    }

                    Helpers::adminNotice($message, 'warning', false, true);
                }

                break;
        }
    }

    public static function excludePrivateEvents($query)
    {
        // Check role

        if (current_user_can('administrator')) {
            return;
        }

        // Check post type.

        if (! in_array('event', (array) $query->get('post_type'))) {
            return;
        }

        remove_action(current_action(), [__CLASS__, __FUNCTION__]);

        $private_events = Model::getPrivateEvents(['fields' => 'ids']);

        add_action(current_action(), [__CLASS__, __FUNCTION__]);

        $exclude = $query->get('post__not_in');

        if (! is_array($exclude)) {
            $exclude = [];
        }

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            foreach ($private_events as $event_id) {
                $event = new Event($event_id);

                if (is_admin() && $user_id == $event->post_author) {
                    continue;
                }

                if (! $event->hasAccess($user_id)) {
                    $exclude[] = $event->ID;
                }
            }
        } else {
            $exclude = $private_events;
        }

        $query->set('post__not_in', $exclude);
    }

    public static function checkAccessEventEdit()
    {
        $screen = get_current_screen();

        if ($screen->id !== 'event') {
            return;
        }

        $event = new Event($_GET['post']);

        if (current_user_can('administrator')) {
            return;
        }

        $user_id = get_current_user_id();

        if ($user_id == $event->post_author) {
            return;
        }

        if ($event->hasAccess($user_id)) {
            return;
        }

        status_header(403);

        wp_die(__('You are not allowed to access this page', 'my-events'));

        exit;
    }

    public static function postClass($classes, $class, $post_id)
    {
        if (get_post_type($post_id) == 'event') {
            return self::getEventClasses($post_id);
        }

        return $classes;
    }

    public static function adminBodyClass($classes)
    {
        $screen = get_current_screen();

        if ($screen->id === 'event') {
            $classes .= ' ' . implode(' ', self::getEventClasses($_GET['post']));
        }

        return $classes;
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

        if ($event->isOver() || ! $event->getGroup() || ! get_post_type($event->getGroup())) {
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
}

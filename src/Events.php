<?php

namespace My\Events;

use My\Events\Posts\Post;
use My\Events\Posts\Event;
use My\Events\Posts\Invitee;

class Events
{
    /**
     * Initialize
     */
    public static function init()
    {
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('wp_trash_post', [__CLASS__, 'trashPost']);
        add_action('before_delete_post', [__CLASS__, 'beforeDeletePost']);
        add_action('admin_notices', [__CLASS__, 'adminNotices']);
        add_action('pre_get_posts', [__CLASS__, 'excludePrivateEvents'], PHP_INT_MAX);
        add_action('add_meta_boxes', [__CLASS__, 'metaBoxes']);
        add_filter('acf/load_value/key=my_events_event_invitees_individual', [__CLASS__, 'updateInviteesField'], 10, 2);
        add_filter('acf/load_field/key=my_events_event_invitees_list', [__CLASS__, 'renderEventInvitees']);
        add_filter('admin_body_class', [__CLASS__, 'adminBodyClass']);
        add_filter('post_class', [__CLASS__, 'postClass'], 10, 3);
    }

    public static function getEventClasses($post_id)
    {
        $event = new Event($post_id);

        $classes = [];

        $classes[] = 'is-event';

        if ($event->isOver()) {
            $classes[] = 'is-event-over';
        }

        if ($event->isPrivate()) {
            $classes[] = 'is-private-event';
        }

        if (is_user_logged_in()) {
            $invitee = $event->getInvitee(get_current_user_id());
            if ($invitee) {
                $classes[] = 'is-invitee';
                $classes[] = sprintf('is-invitee-%s', $invitee->getStatus());
            }
        }

        if (is_admin() && self::isEditableEvent($event->ID)) {
            $classes[] = 'is-editable-event';
        }

        return $classes;
    }

    public static function isEditableEvent($post_id)
    {
        $event = new Event($post_id);

        return apply_filters('my_events/is_editable_event', ! $event->isOver(), $event);
    }

    /**
     * Get events
     *
     * @param array $args
     * @return array
     */
    public static function getEvents($args = [])
    {
        return get_posts([
            'post_type'    => 'event',
            'post_status'  => 'publish',
            'numberposts'  => 999,
        ] + $args);
    }

    public static function getPrivateEvents($args = [])
    {
        return self::getEvents([
            'meta_query' => [
                [
                    'key'     => 'is_private',
                    'compare' => '=',
                    'value'   => true,
                ],
            ],
        ] + $args);
    }

    /**
     * Get invitees
     *
     * @param array $args
     * @return array
     */
    public static function getInvitees($args = [])
    {
        return get_posts([
            'post_type'    => 'invitee',
            'post_status'  => 'publish',
            'numberposts'  => 999,
        ] + $args);
    }

    public static function getEventsByInviteesGroup($group_id, $args = [])
    {
        return self::getEvents([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'invitees_type',
                    'compare' => '=',
                    'value'   => 'group',
                ],
                [
                    'key'     => 'invitees_group',
                    'compare' => '=',
                    'value'   => $group_id,
                ],
            ],
        ] + $args);
    }

    public static function getInviteesFromGroup($group_id, $args = [])
    {
        if (get_post_type($group_id) !== 'invitee_group') {
            return false;
        }

        $group = new Post($group_id);

        $user_ids = $group->getMeta('users', true);

        return is_array($user_ids) ? $user_ids : [];
    }

    /**
     * Save post
     *
     * @param int $post_id
     */
    public static function savePost($post_id)
    {
        // Event
        if (get_post_type($post_id) === 'event') {

            // Get event.
            $event = new Event($post_id);

            /**
             * Update invitees
             * ---------------------------------------------------
             */

            // Get invitees from settings fields.
            $type = $event->getMeta('invitees_type', true);
            $user_ids = [];
            if ($type == 'individual') {
                $user_ids = $event->getMeta('invitees_individual', true);
            } elseif ($type == 'group') {
                $group_id = $event->getMeta('invitees_group', true);
                $user_ids = self::getInviteesFromGroup($group_id);
            }

            if (! is_array($user_ids)) {
                $user_ids = [];
            }

            // Create invitees.
            $event->setInvitees($user_ids);

            // Delete individual settings. (they are re-populated by invitees from custom post type)
            $event->deleteMeta('invitees_individual');

            /* -------------------------------------------------- */

            // Notify
            do_action('my_events/save_event', $event);
        }

        // Invitees group
        if (get_post_type($post_id) === 'invitee_group') {

            $group = new Post($post_id);

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

                $events = self::getEventsByInviteesGroup($group->ID);

                foreach ($events as $event) {
                    $event = new Event($event);

                    if (! $event->isOver()) {
                        $event->addInvitee($user_id);
                    }
                }
            }

            // Remove invitees

            foreach ($removed_users as $user_id) {

                $events = self::getEventsByInviteesGroup($group->ID);

                foreach ($events as $event) {
                    $event = new Event($event);

                    if (! $event->isOver()) {
                        $event->removeInvitee($user_id);
                    }
                }
            }

            // Save current users

            $group->updateMeta('prev_users', $current_users);
        }
    }

    /**
     * Trash post
     *
     * @param int $post_id
     */
    public static function trashPost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) !== 'event') {
            return;
        }

        // Get event.
        $event = new Event($post_id);

        // Notify
        do_action('my_events/trash_event', $event);

        // TODO : Check if event was published.
        if (! $event->isOver()) {
            do_action('my_events/event_cancelled', $event);
        }
    }

    /**
     * Before delete post
     *
     * @param int $post_id
     */
    public static function beforeDeletePost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) !== 'event') {
            return;
        }

        // Get event.
        $event = new Event($post_id);

        // Notify
        do_action('my_events/before_delete_event', $event);

        // Remove all invitees.
        $event->setInvitees([]);
    }

    public static function adminNotices()
    {
        $screen = get_current_screen();

        if ($screen->id !== 'event') {
            return;
        }

        $event = new Event($_GET['post']);

        if ($event->isOver()) {
            Helpers::adminNotice(__('This event is over.', 'my-events'), 'error');
        }

        if ($event->isPrivate()) {
            Helpers::adminNotice(__('This event is only accessible to organisers and invitees of this event.', 'my-events'), 'info');
        }
    }

    /**
     * Update invitees field
     *
     * @param array $value
     * @param int   $post_id
     * @return array
     */
    public static function updateInviteesField($value, $post_id)
    {
        // Get event.
        $event = new Event($post_id);

        // Skip when field has value (need to be accessed when saved)
        if ($value) {
            return $value;
        }

        // Populate field with invitees.
        return $event->getInviteesUsers(null, ['fields' => 'ID']);
    }

    public static function excludePrivateEvents($query)
    {
        // Check role
        if (current_user_can('administrator')) {
            return;
        }

        // Check post type
        if (! in_array('event', (array) $query->get('post_type'))) {
            return;
        }

        // Get private events.

        remove_action('pre_get_posts', [__CLASS__, __FUNCTION__], PHP_INT_MAX);

        $private_events = self::getPrivateEvents(['fields' => 'ids']);

        add_action('pre_get_posts', [__CLASS__, __FUNCTION__], PHP_INT_MAX);

        // Exclude private events.

        $exclude = $query->get('post__not_in');

        if (! is_array($exclude)) {
            $exclude = [];
        }

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();

            foreach ($private_events as $post_id) {
                $event = new Event($post_id);
                if (! $event->hasAccess($user_id)) {
                    $exclude[] = $post_id;
                }
            }
        } else {
            $exclude = $private_events;
        }

        $query->set('post__not_in', $exclude);
    }

    public static function renderEventInvitees($field)
    {
        $event = new Event($_GET['post']);

        $accepted = $event->getInviteesUsers('accepted');
        $declined = $event->getInviteesUsers('declined');
        $pending  = $event->getInviteesUsers('pending');

        $str = '<ul>';

        foreach ($accepted as $user) {
            $invitee = $event->getInvitee($user->ID);
            $str .= sprintf(
                '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
                esc_url(get_edit_post_link($invitee->ID)),
                esc_html($user->display_name),
                esc_html__('accepted', 'my-events')
            );
        }

        foreach ($pending as $user) {
            $invitee = $event->getInvitee($user->ID);
            $str .= sprintf(
                '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
                esc_url(get_edit_post_link($invitee->ID)),
                esc_html($user->display_name),
                esc_html__('pending', 'my-events')
            );
        }

        foreach ($declined as $user) {
            $invitee = $event->getInvitee($user->ID);
            $str .= sprintf(
                '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
                esc_url(get_edit_post_link($invitee->ID)),
                esc_html($user->display_name),
                esc_html__('declined', 'my-events')
            );
        }

        $str .= '</ul>';

        $field['message'] = $str;

        return $field;
    }

    public static function adminBodyClass($classes)
    {
        $screen = get_current_screen();

        if ($screen->id == 'event') {
            $classes .= ' ' . implode(' ', self::getEventClasses($_GET['post']));
        }

        return $classes;
    }

    public static function postClass($classes, $class, $post_id)
    {
        if (get_post_type($post_id) === 'event') {
            $classes = array_merge($classes, self::getEventClasses($post_id));
        }

        return $classes;
    }

    public static function metaBoxes($post_type)
    {
        $screen = get_current_screen();

        if ($screen->id === 'event') {
            if (! self::isEditableEvent($_GET['post'])) {
                remove_meta_box('submitdiv', 'event', 'side');
            }
        }
    }
}

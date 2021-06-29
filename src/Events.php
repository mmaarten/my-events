<?php

namespace My\Events;

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
        add_action('pre_get_posts', [__CLASS__, 'excludePrivateEvents']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'checkAccessEventEdit']);

        add_filter('acf/load_value/key=field_60dacb8f3a3ca', [__CLASS__, 'updateInviteesField'], 10, 3);
        add_filter('acf/load_field/key=field_60daf8ac12fca', [__CLASS__, 'renderInvitees']);
    }

    public static function getEvents($args = [])
    {
        return get_posts([
            'post_type'   => 'event',
            'post_status' => 'publish',
            'numberposts' => 999,
        ] + $args);
    }

    public static function getPrivateEvents($args = [])
    {
        return self::getEvents([
            'meta_key'     => 'is_private',
            'meta_compare' => '=',
            'meta_value'   => true,
        ] + $args);
    }

    public static function getInvitees($args = [])
    {
        return get_posts([
            'post_type'   => 'invitee',
            'post_status' => 'publish',
            'numberposts' => 999,
        ] + $args);
    }

    public static function getInviteesByUser($user_id, $args = [])
    {
        return self::getInvitees([
            'meta_key'     => 'user',
            'meta_compare' => '=',
            'meta_value'   => $user_id,
        ] + $args);
    }

    public static function savePost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) === 'event') {
            // Get event.
            $event = new Event($post_id);

            // Get invitees from settings field
            $invitees = $event->getMeta('invitees', true);

            // Create/delete invitees.
            $event->setInvitees($invitees);

            // Empty settings field.
            $event->updateMeta('invitees', []);
        }
    }

    public static function trashPost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) === 'event') {
            $event = new Event($post_id);
        }
    }

    public static function beforeDeletePost($post_id)
    {
        // Check post type.
        if (get_post_type($post_id) === 'event') {
            // Get event.
            $event = new Event($post_id);

            // Remove all invitees.
            $event->setInvitees([]);
        }
    }

    public static function beforeDeleteUser($user_id, $reassign, $user)
    {
        // Get invitees by user.
        $invitees = self::getInviteesByUser($user_id);

        // Remove invitees.
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

    public static function updateInviteesField($value, $post_id, $field)
    {
        if (! $value) {
            // Populates field with invitees comming from our post type.
            $event = new Event($post_id);
            return $event->getInviteesUsers(null, ['fields' => 'ID']);
        }

        // Return.
        return $value;
    }

    public static function renderInvitees($field)
    {
        $event = new Event($_GET['post']);

        $accepted = $event->getInviteesUsers('accepted');
        $pending  = $event->getInviteesUsers('pending');
        $declined = $event->getInviteesUsers('declined');

        if (! $accepted && ! $pending && ! $declined) {
            Helpers::adminNotice(__('No invitees found.', 'my-events'), 'info', true);
            return;
        }

        $return = '<ul>';

        foreach ($accepted as $user) {
            $invitee = $event->getInviteeByUser($user->ID);
            $return .= sprintf(
                '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
                get_edit_post_link($invitee->ID),
                esc_html($user->display_name),
                esc_html__('accepted', 'my-events')
            );
        }

        foreach ($pending as $user) {
            $invitee = $event->getInviteeByUser($user->ID);
            $return .= sprintf(
                '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
                get_edit_post_link($invitee->ID),
                esc_html($user->display_name),
                esc_html__('pending', 'my-events')
            );
        }

        foreach ($declined as $user) {
            $invitee = $event->getInviteeByUser($user->ID);
            $return .= sprintf(
                '<li><a href="%1$s">%2$s</a> (%3$s)</li>',
                get_edit_post_link($invitee->ID),
                esc_html($user->display_name),
                esc_html__('declined', 'my-events')
            );
        }

        $return .= '</ul>';

        $field['message'] = $return;

        return $field;
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

        $private_events = self::getPrivateEvents(['fields' => 'ids']);

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
}

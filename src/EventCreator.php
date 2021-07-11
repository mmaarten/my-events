<?php

namespace My\Events;

use My\Events\Posts\Event;

class EventCreator
{
    const POST_ID = 'my_events_event_creator';

    public static function init()
    {
        add_action('init', [__CLASS__, 'removeFields']);
        add_action('acf/init', [__CLASS__, 'addOptionsPage']);
        add_action('acf/save_post', [__CLASS__, 'savePost']);
        add_action('admin_notices', [__CLASS__, 'adminNotices']);
    }

    public static function adminNotices()
    {
        $screen = get_current_screen();

        if ($screen->id !== 'tools_page_my-events-event-creator') {
            return;
        }

        echo Helpers::adminNotice(__('Create multiple events based on repeat settings.', 'my-events'));
    }

    public static function addOptionsPage()
    {
        $x = acf_add_options_page([
            'page_title'    => __('Event Creator', 'my-events'),
            'menu_title'    => __('Event Creator', 'my-events'),
            'menu_slug'     => 'my-events-event-creator',
            'capability'    => 'edit_posts',
            'parent_slug'   => 'my-events',
            'post_id'       => self::POST_ID,
        ]);
    }

    public static function getField($selector, $format_value = true)
    {
        return get_field($selector, self::POST_ID, $format_value);
    }

    public static function savePost($post_id)
    {
        if ($post_id !== self::POST_ID) {
            return;
        }

        $is_all_day     = self::getField('is_all_day');
        $repeat_end     = self::getField('repeat_end');
        $repeat_exclude = self::getField('repeat_exclude');
        $repeat         = self::getField('repeat');

        if ($is_all_day) {
            $start = self::getField('all_day_start');
            $end   = self::getField('all_day_end');
        } else {
            $start = self::getField('start');
            $end   = self::getField('end');
        }

        if (! is_array($repeat_exclude)) {
            $repeat_exclude = [];
        }

        $repeat_exclude = wp_list_pluck($repeat_exclude, 'date');

        $dates = Helpers::generateDates($start, $end, $repeat_end, $repeat, $repeat_exclude);
        $dates = array_slice($dates, 0, 50);

        foreach ($dates as $date) {
            $postdata = [
                'post_title'   => self::getField('event_title'),
                'post_content' => '',
                'post_type'    => 'event',
                'post_status'  => self::getField('post_status'),
            ];

            $post_id = wp_insert_post($postdata);

            $event = new Event($post_id);

            $start_time = date('H:i:s', strtotime(self::getField('start')));
            $end_time = date('H:i:s', strtotime(self::getField('end')));

            $event->updateField('is_all_day', self::getField('is_all_day', false));
            $event->updateField('start', "{$date['start']} $start_time");
            $event->updateField('end', "{$date['end']} $end_time");
            $event->updateField('description', self::getField('description', false));
            $event->updateField('enable_subscriptions', self::getField('enable_subscriptions', false));
            $event->updateField('organisers', self::getField('organisers', false));
            $event->updateField('invitee_type', self::getField('invitee_type', false));
            $event->updateField('individual_invitees', self::getField('individual_invitees', false));
            $event->updateField('invitee_group', self::getField('invitee_group', false));
            $event->updateField('invitee_default_status', self::getField('invitee_default_status', false));
            $event->updateField('max_participants', self::getField('max_participants', false));
            $event->updateField('is_private', self::getField('is_private', false));
            $event->updateField('location_type', self::getField('location_type', false));
            $event->updateField('custom_location', self::getField('custom_location', false));
            $event->updateField('location_id', self::getField('location_id', false));

            wp_update_post([
                'ID'        => $event->ID,
                'post_name' => sanitize_title($event->post_title . '-' . $event->getTimeFromUntil()),
            ]);

            Events::updateEventFields($event->ID);

            // TODO : Use hook.
            ICal::createCalendarFile($event->ID);
        }
    }

    public static function removeFields()
    {
        if (did_action('acf/save_post')) {
            return;
        }

        $fields = get_field_objects(self::POST_ID);

        if (! is_array($fields)) {
            return;
        }

        $keep = ['repeat_end', 'repeat_exclude'];

        $fields = array_keys($fields);

        foreach ($fields as $field) {
            if (! in_array($field, $keep)) {
                delete_field($field, self::POST_ID);
            }
        }
    }
}

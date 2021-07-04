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
    }

    public static function addOptionsPage()
    {
        acf_add_options_page([
            'page_title'    => __('Event Creator', 'my-events'),
            'menu_title'    => __('Event Creator', 'my-events'),
            'menu_slug'     => 'my-events-event-creator',
            'capability'    => 'edit_posts',
            'parent_slug'   => 'tools.php',
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

        $start_date     = self::getField('date');
        $repeat_end     = self::getField('repeat_end');
        $repeat_exclude = self::getField('repeat_exclude');
        $repeat         = self::getField('repeat');

        if (! is_array($repeat_exclude)) {
            $repeat_exclude = [];
        }

        $dates = Helpers::generateDates($start_date, $repeat_end, $repeat, $repeat_exclude);
        $dates = array_slice($dates, 0, 50);

        foreach ($dates as $date) {
            $postdata = [
                'post_title'   => self::getField('event_title'),
                'post_content' => '',
                'post_type'    => 'event',
                'post_status'  => 'publish',
            ];

            $post_id = wp_insert_post($postdata);

            $event = new Event($post_id);

            $event->updateField('date', $date);
            $event->updateField('start_time', self::getField('start_time', false));
            $event->updateField('end_time', self::getField('end_time', false));
            $event->updateField('description', self::getField('description', false));
            $event->updateField('organisers', self::getField('organisers', false));
            $event->updateField('invitees_type', self::getField('invitees_type', false));
            $event->updateField('invitees_individual', self::getField('invitees_individual', false));
            $event->updateField('invitees_group', self::getField('invitees_group', false));
            $event->updateField('is_private', self::getField('is_private', false));
            $event->updateField('location_type', self::getField('location_type', false));
            $event->updateField('location_input', self::getField('location_input', false));
            $event->updateField('location_id', self::getField('location_id', false));

            Events::updateEventTime($event->ID);
            Events::setInviteesFromSettingsFields($event->ID, 'accepted');

            wp_update_post([
                'ID'        => $event->ID,
                'post_name' => sanitize_title($event->post_title . '-' . $event->getTimeFromUntil()),
            ]);
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

        $fields = array_keys($fields);

        foreach ($fields as $field) {
            delete_field($field, self::POST_ID);
        }
    }
}

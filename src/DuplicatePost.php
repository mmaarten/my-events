<?php
/**
 * @link https://developer.yoast.com/duplicate-post/
 */
namespace My\Events;

use My\Events\Posts\Event;

class DuplicatePost
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('dp_duplicate_post', [__CLASS__, 'duplicatePost'], 10, 3);
    }

    /**
     * Duplicate post
     *
     * @link https://developer.yoast.com/duplicate-post/filters-actions
     * @param int $new_post_id The newly created post's ID.
     * @param WP_Post $post The original post's object.
     * @param string $status The destination status as set by the calling method: e.g. ‘draft’ if the function has been called using the “New Draft” links. Empty otherwise.
     */
    public static function duplicatePost($new_post_id, $post, $status)
    {
        if ($post->post_type != 'event' || get_post_type($new_post_id) != 'event') {
            return;
        }

        // Copy invitees.

        $event = new Event($post);
        $invitees = $event->getInviteesUsers(null, ['fields' => 'ID']);

        $new_event = new Event($new_post_id);
        $new_event->setInvitees($invitees, $event->getInviteeDefaultStatus());
    }
}

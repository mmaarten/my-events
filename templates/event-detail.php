<?php

namespace My\Events;

use My\Events\Posts\Event;

$event = new Event();
$description = $event->getDescription();
$location = $event->getLocation();
$organisers = wp_list_pluck($event->getOrganisers(), 'display_name', 'ID');
$participants = wp_list_pluck($event->getParticipants(), 'display_name', 'ID');

$file = null;
if (file_exists(ICal::getEventFile($event->ID))) {
    $file = ICal::getEventFile($event->ID, true);
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <h1><?php echo esc_html($event->post_title); ?><?php edit_post_link(__('Edit', 'my-events'), ' <small>', '</small>'); ?></h1>

    <section id="event-notices">
        <?php Subscriptions::notices(); ?>
    </section>

    <?php if (trim($description)) : ?>
    <section id="event-description">
        <h2><?php esc_html_e('Description', 'my-events'); ?></h2>
        <?php echo $description; ?>
    </section>
    <?php endif; ?>

    <section id="event-time">
        <h2><?php esc_html_e('Time', 'my-events'); ?></h2>
        <p>
            <time datetime="<?php echo esc_attr($event->getStartTime('Y-m-d H:i')); ?>"><?php echo esc_html($event->getTimeFromUntil()); ?></time>
        </p>
    </section>

    <?php if (trim($location)) : ?>
    <section id="event-location">
        <h2><?php esc_html_e('Location', 'my-events'); ?></h2>
        <p>
            <a href="<?php echo esc_url(Helpers::getMapURL($location)); ?>" target="_blank"><?php echo $location; ?></a>
        </p>
    </section>
    <?php endif; ?>

    <?php if ($organisers) : ?>
    <section id="event-organisers">
        <h2><?php esc_html_e('Organisers', 'my-events'); ?></h2>
        <p>
            <?php echo esc_html(implode(', ', $organisers)); ?>
        </p>
    </section>
    <?php endif; ?>

    <?php if ($participants) : ?>
    <section id="event-participants">
        <h2><?php esc_html_e('Participants', 'my-events'); ?></h2>
        <p>
            <?php echo esc_html(implode(', ', $participants)); ?>
        </p>
    </section>
    <?php endif; ?>

    <section id="event-accessibility">
        <h2><?php esc_html_e('Accessibility', 'my-events'); ?></h2>
        <?php if ($event->isPrivate()) : ?>
        <p><?php esc_html_e('This event is only accessible to organisers and invitees of this event.', 'my-events'); ?></p>
        <?php else : ?>
        <p><?php esc_html_e('Anyone has access to this event.', 'my-events'); ?></p>
        <?php endif; ?>
    </section>

    <section id="event-subscriptions">
        <h2><?php esc_html_e('Subscriptions', 'my-events'); ?></h2>
        <?php

        if ($event->subscriptionsEnabled()) {
            if ($event->isLimitedParticipants()) {
                $max_available_places = $event->getMaxParticipants();
                $available_places = $max_available_places - count($event->getParticipants());

                if ($available_places) {
                    $limited_text = sprintf(esc_html__('%1$s of %2$s places available.', 'my-events'), $available_places, $max_available_places);
                } else {
                    $limited_text = esc_html__('No available places.', 'my-events');
                }

                printf(
                    '<p>%s</p>',
                    sprintf(esc_html__('Subscriptions are limited: %s', 'my-events'), $limited_text)
                );
            } else {
                printf('<p>%s</p>', esc_html__('Subscriptions are enabled.', 'my-events'));
            }
        } else {
            printf('<p>%s</p>', esc_html__('Subscriptions are disabled.', 'my-events'));
        }

        ?>
    </section>

    <?php if ($file) : ?>
    <section id="event-file">
        <h2><?php esc_html_e('Calendar file', 'my-events'); ?></h2>
        <p><a href="<?php echo esc_url($file); ?>"><?php esc_html_e('Download', 'my-events'); ?></a></p>
    </section>
    <?php endif; ?>

    <section id="event-subscription">
        <?php Subscriptions::form(); ?>
    </section>

</article>

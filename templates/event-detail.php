<?php

namespace My\Events;

use My\Events\Posts\Event;

$event = new Event();
$description = $event->getDescription();
$location = $event->getLocation();
$organisers = wp_list_pluck($event->getOrganisers(), 'display_name', 'ID');
$participants = wp_list_pluck($event->getParticipants(), 'display_name', 'ID');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <?php the_title('<h1>', '</h1>'); ?>

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

    <section id="event-subscription">
        <?php Subscriptions::form(); ?>
    </section>

    <?php edit_post_link(__('Edit this event', 'my-events', '<p>', '</p>')); ?>

</article>

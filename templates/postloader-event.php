<?php

namespace My\Events;

use My\Events\Posts\Event;

$event = new Event();
$organizers = wp_list_pluck($event->getOrganizers(['orderby' => 'display_name', 'order' => 'ASC']), 'display_name');
$location   = $event->getLocation();

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('card'); ?>>

    <div class="card-body">

        <?php the_title(sprintf('<h3 class="card-title h4"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h3>'); ?>

        <div class="card-text">

            <ul class="entry-meta list-unstyled small">

                <li class="list-unstyled-item event-time">
                    <?php esc_html_e('Time:', 'de-keerkring-theme'); ?>
                    <?php echo esc_html($event->getTimeFromUntil()); ?>
                </li>

                <?php if ($organizers) : ?>
                <li class="list-unstyled-item event-organisers">
                    <?php esc_html_e('Organizers:', 'de-keerkring-theme'); ?>
                    <?php echo esc_html(implode(', ', $organizers)); ?>
                </li>
                <?php endif; ?>

                <?php if (trim($location)) : ?>
                <li class="list-unstyled-item event-location">
                    <?php esc_html_e('Location:', 'de-keerkring-theme'); ?>
                    <address><a href="<?php echo esc_url(Helpers::getMapURL($location)); ?>" target="_blank"><?php echo esc_html($location); ?></a></address>
                    <div class="wp-block-de-keerkring-theme-spacer alignfull has-spacing-5 has-spacing-md-6" aria-hidden="true"></div>
                </li>
                <?php endif; ?>

            </ul>

            <div class="entry-summary">
                <?php the_excerpt(); ?>
            </div>

            <div class="entry-footer">
                <a href="<?php the_permalink(); ?>" class="btn btn-primary"><?php esc_html_e('Read More', 'de-keerkring-theme'); ?></a>
            </div>

        </div>

    </div>

</article><!-- #post-<?php the_ID(); ?> -->

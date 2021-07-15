<?php

namespace My\Events\Postloaders;

use \My\Postloaders\Postloader;
use \My\Events\Helpers;
use \My\Events\Model;

class Events extends Postloader
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct('events');
    }

    /**
     * Render form
     *
     * @param array $args
     */
    public function form($args = [])
    {
        parent::form($args);

        ?>

        <h3 class="h6"><?php esc_html_e('Order by', 'events'); ?></h3>

        <ul class="list-inline">
            <li class="list-inline-item">
                <label><input type="radio" class="autoload" name="order_by" value="start" checked> <?php esc_html_e('Start time', 'events'); ?></label>
            </li>
            <li class="list-inline-item">
                <label><input type="radio" class="autoload" name="order_by" value="publish"> <?php esc_html_e('Publish date', 'events'); ?></label>
            </li>
        </ul>

        <?php
    }

    /**
     * Alter query arguments.
     *
     * @param array $query_args
     * @return array
     */
    public function queryArgs($query_args)
    {
        $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : '';

        $query_args = array_merge($query_args, [
            'post_type' => 'event',
            'meta_query' => [
                [
                    'key'     => 'end',
                    'compare' => '>=',
                    'value'   => date_i18n('Y-m-d H:i:s'),
                    'type'    => 'DATETIME',
                ],
            ],
        ]);

        switch ($order_by) {
            case 'start':
                $query_args = array_merge($query_args, [
                    'orderby'   => 'meta_value',
                    'meta_key'  => 'start',
                    'meta_type' => 'DATETIME',
                    'order'     => 'ASC',
                ]);
                break;

            case 'publish':
                $query_args = array_merge($query_args, [
                    'orderby'   => 'post_date',
                    'order'     => 'ASC',
                ]);
                break;
        }

        return $query_args;
    }

    /**
     * Render content
     *
     * @param WP_Query $query
     */
    public function content($query)
    {
        if ($query->have_posts()) {
            echo '<div class="row">';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<div class="col-md-4">';
                Helpers::loadTemplate('postloader-event');
                echo '</div>';
            }
            echo '</div>';
        } else {
            Helpers::Alert(__('No events found.', 'my-events'));
        }

        wp_reset_postdata();
    }
}

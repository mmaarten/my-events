<?php

namespace My\Events;

?>

<p><?php printf(esc_html__('%1$s declined your invitation for:', 'my-events'), $args['user']->display_name); ?></p>

<?php Helpers::loadTemplate('emails/event-meta', $args); ?>

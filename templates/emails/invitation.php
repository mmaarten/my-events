<?php

namespace My\Events;

?>

<p><?php esc_html_e('You are invited for following event:', 'my-events'); ?></p>

<?php Helpers::loadTemplate('emails/event-meta', $args); ?>

(function(){
  "use strict";

  jQuery('.my-events-send-email').each(function(){
    var $elem = jQuery(this);

    $elem.on('click', '.my-events-submit', function() {
      var data = {
        action  : $elem.data('action'),
        event   : $elem.data('event'),
        message : $elem.find('#my_events_send_email_message_field').val(),
      };

      data[$elem.data('noncename')] = $elem.data('nonce');

      $elem.addClass('is-loading');
      jQuery.post(ajaxurl, data, function(response){
        $elem.removeClass('is-loading');
        $elem.find('.my-events-output').html(response.data);
        $elem.find(':input').val('');
      });
    });
  });

})();

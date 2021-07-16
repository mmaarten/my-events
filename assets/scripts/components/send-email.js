(function(){
  "use strict";

  jQuery('.my-events-send-email').each(function(){
    var $elem = jQuery(this);
    var $message = $elem.find('#my-events-send-email-message');
    var $output = $elem.find('.my-events-send-email-output');
    var $fields = $elem.find(':input:not([disabled])');

    $elem.on('click', '.my-events-send-email-submit', function() {
      $elem.addClass('is-loading');
      $output.html('');
      $fields.prop('disabled', true);

      var recipients = [];
      $elem.find(':input[name="recipients[]"]:checked').each(function(){
        recipients.push(jQuery(this).val());
      });

      var data = {
        action  : $elem.data('action'),
        event   : $elem.data('event'),
        message : $message.val(),
        recipients : recipients,
      };

      data[$elem.data('noncename')] = $elem.data('nonce');

      jQuery.post(ajaxurl, data, function(response) {
        $elem.removeClass('is-loading');
        $fields.prop('disabled', false);
        $message.val('');
        $output.html(response.data);
      });
    });

    $elem.on('click', '.check-all', function(event) {
      event.preventDefault();
      $elem.find('.my-events-send-email-recipients input[type="checkbox"]').prop('checked', true);
    });

    $elem.on('click', '.uncheck-all', function(event) {
      event.preventDefault();
      $elem.find('.my-events-send-email-recipients input[type="checkbox"]').prop('checked', false);
    });

  });
})();

(function(){
  "use strict";

  jQuery('.my-events-search-overlapping-events').each(function(){
    var $elem = jQuery(this);

    $elem.on('click', '.my-events-submit', function(){
      var data = {
        action : $elem.data('action'),
        event  : $elem.data('event'),
        start  : $elem.find('#my-events-overlapping-start').val(),
        end    : $elem.find('#my-events-overlapping-end').val(),
        offset : $elem.find('#my-events-overlapping-offset').val(),
      };

      data[$elem.data('noncename')] = $elem.data('nonce');

      $elem.addClass('is-loading');
      jQuery.post(ajaxurl, data, function(response){
        $elem.removeClass('is-loading');
        $elem.find('.my-events-output').html(response.data);
      });
    });

    $elem.find('.my-events-submit').trigger('click');
  });

})();

(function(){
  jQuery('.my-events-search-overlapping-events').each(function(){
    var $elem = jQuery(this);

    $elem.on('click', '.button', function(){
      var dataset = Object.assign({}, $elem.get(0).dataset);

      var data = {
        action : dataset.action,
        event  : dataset.event,
        start  : $elem.find('#my-events-overlapping-start').val(),
        end    : $elem.find('#my-events-overlapping-end').val(),
        offset : $elem.find('#my-events-overlapping-offset').val(),
      };

      data[dataset.noncename] = dataset.nonce;

      $elem.addClass('is-loading');
      jQuery.post(MyEvents.ajaxurl, data, function(response){
        $elem.removeClass('is-loading');
        $elem.find('.my-events-output').html(response.data);
      });
    });

    $elem.find('.button').trigger('click');
  });

})(window);

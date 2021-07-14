(function(window){

  function AjaxForm (elem, options) {
    this.$elem = jQuery(elem);
    this.options = jQuery.extend({}, options, this.$elem.data());

    this.$elem.on('submit', this.onSubmit.bind(this));
    this.$elem.on('click', '.my-ajax-form-submit', this.onSubmit.bind(this));
  }

  AjaxForm.prototype.onSubmit = function(event) {
    event.preventDefault();

    var data = this.$elem.find(':input').serialize();

    if (this.$elem.get(0).dataset) {
      data += '&' + jQuery.param(this.$elem.get(0).dataset);
    }

    this.$elem.addClass('is-loading');
    jQuery.post(MyEvents.ajaxurl, data, function(response) {
      console.log(response);
      this.$elem.find('.my-ajax-form-output').html(response.data);
      this.$elem.removeClass('is-loading');
    }.bind(this));
  }

  jQuery.fn.ajaxForm = function(options) {
    return this.each(function(){
      if (! jQuery(this).data('ajaxform')) {
        jQuery(this).data('my-ajaxform', new AjaxForm(this, options));
      }
    });
  }

})(window);

window.addEventListener('DOMContentLoaded', function(){
  jQuery('.my-ajax-form').ajaxForm();
});

window.addEventListener('DOMContentLoaded', function () {
  jQuery('.my-datepicker').each(function(){
    var options = Object.assign({}, this.dataset);
    jQuery(this).datepicker(options);
  });
});

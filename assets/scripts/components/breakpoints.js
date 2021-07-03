(function(window, document){
  var Breakpoints = {
    breakpoint : null,
    breakpoints : {},
    init : function() {
      this.breakpoints = {
        xs: 0,
        sm: 576,
        md: 768,
        lg: 992,
        xl: 1200,
      };

      jQuery(window).on('resize', this.update.bind(this));

      this.update();
    },
    setBreakpoint : function(breakpoint) {
      if (breakpoint == this.breakpoint) {
        return;
      }
      this.breakpoint = breakpoint;
      jQuery(document).trigger('myEvents.breakpointChange', [this.breakpoint]);
    },
    update : function() {
      var width = jQuery(window).width();
      if (width < this.breakpoints.sm) {
        this.setBreakpoint('xs');
      } else if (width >= this.breakpoints.sm && width < this.breakpoints.md) {
        this.setBreakpoint('sm');
      } else if (width >= this.breakpoints.md && width < this.breakpoints.lg) {
        this.setBreakpoint('md');
      } else if (width >= this.breakpoints.lg && width < this.breakpoints.xl) {
        this.setBreakpoint('lg');
      } else if (width >= this.breakpoints.xl) {
        this.setBreakpoint('xl');
      }
    }
  };

  window.addEventListener('load', Breakpoints.init.bind(Breakpoints));
  window.Breakpoints = Breakpoints;
})(window, document);

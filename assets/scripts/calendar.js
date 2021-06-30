import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from "@fullcalendar/interaction";
import bootstrapPlugin from '@fullcalendar/bootstrap';
import listPlugin from '@fullcalendar/list';
import googleCalendarPlugin from '@fullcalendar/google-calendar';
import nlLocale from '@fullcalendar/core/locales/nl';
import './components/breakpoints';

(function(window, document){

  "use strict";

  var App = {

    init : function() {
      this.$elem = jQuery('#calendar');
      this.options = jQuery.extend({
        plugins: [ interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin, bootstrapPlugin, googleCalendarPlugin ],
        themeSystem: 'bootstrap',
        locale: nlLocale,
        initialView: 'dayGridMonth',
        eventTimeFormat: {hour: '2-digit', minute: '2-digit'},
        headerToolbar: { center: 'dayGridMonth,timeGridWeek,timeGridDay' },
      }, this.$elem.data('options'));

      this.loadedEvents = {};

      this.options.datesSet   = this.datesSet.bind(this);
      this.options.dateClick  = this.onMonthDayClick.bind(this);
      this.options.eventClick = this.eventClick.bind(this);

      this.calendar = new Calendar(this.$elem.get(0), this.options);
      this.calendar.render();

      this.$elem.on('click', '.fc-timeGridWeek-view .fc-col-header-cell-cushion', this.onWeekDayClick.bind(this));
      jQuery(document).on('myEvents.breakpointChange', this.breakpointChange.bind(this));
      window.addEventListener('DOMContentLoaded', this.maybeShowEventDetail.bind(this));
      window.addEventListener('hashchange', this.maybeShowEventDetail.bind(this), false);
      jQuery(this).on('MyEventsCalendar.eventsLoaded', this.maybeShowEventDetail.bind(this));

      jQuery.featherlight.defaults.namespace = 'my-events-modal';
      jQuery.featherlight.defaults.afterClose = this.removeWindowLocationHash.bind(this);
    },

    getEventIdFromWindowLocation : function() {
      var matches = /^#calendar\/event\/(.*)/.exec(window.location.hash || '');

      if (matches) {
        return matches[1];
      }

      return false;
    },

    datesSet : function(info) {
      this.addEvents(info.startStr, info.endStr);
    },

    onMonthDayClick : function(info) {
      // Switch to day view
      this.calendar.changeView('timeGridDay');
      this.calendar.gotoDate(info.date);
    },

    onWeekDayClick : function(event) {
      // Switch to day view
      var date = jQuery(event.target).closest('.fc-col-header-cell').data('date');
      this.calendar.changeView('timeGridDay');
      this.calendar.gotoDate(date);
    },

    eventClick : function(info) {
      if (window.location.href == info.event.url) {
        var eventId = this.getEventIdFromWindowLocation();
        if (eventId) {
          this.showEventDetail(eventId);
        }
      }
    },

    breakpointChange : function(event, breakpoint) {
      // Set alternate view for mobile.
      if (breakpoint == 'xs' || breakpoint == 'sm') {
        // Set week list view.
        this.restoreView = this.calendar.view.type;
        this.calendar.changeView('listWeek');
      } else if (this.restoreView) {
        // Restore previous view
        this.calendar.changeView(this.restoreView);
        this.restoreView = null;
      }
    },

    addEvents : function(start, end) {

      this.start = start;
      this.end = end;

      var data = { action: 'my_events_get_events', start: start, end: end };

      this.$elem.addClass('is-loading');

      jQuery.post(MyEvents.ajaxurl, data ,function(response){

        // Remove previous loaded events.
        jQuery.each(this.loadedEvents, function(index, event){
          this.calendar.getEventById(event.id).remove();
        }.bind(this));

        // Store loaded events
        this.loadedEvents = response.events;

        // Add loaded events
        jQuery.each(this.loadedEvents, function(index, event) {
          this.calendar.addEvent(event);
        }.bind(this));

        jQuery(this).trigger('MyEventsCalendar.eventsLoaded', this.loadedEvents);

        this.$elem.removeClass('is-loading');

      }.bind(this));
    },

    loadEventDetail : function(eventId, callback) {
      this.eventDetailLoaded = eventId;
      var data = { action: 'my_events_render_calendar_event_detail', event: eventId };
      jQuery.post(MyEvents.ajaxurl, data, callback);
    },

    showEventDetail : function(eventId) {
      var modal = jQuery.featherlight.current();

      if (modal) {
        modal.close();
      }

      jQuery.featherlight('<div></div>', {
        afterContent: function () {
          var modal = this;
          modal.$content.addClass('is-loading');
          App.loadEventDetail(eventId, function(content){
            modal.$content.removeClass('is-loading');
            modal.$content.html(content);
            jQuery(App).trigger('MyEvents.eventDetailLoaded', [modal.$content, eventId]);
          });
        },
      });
    },

    removeWindowLocationHash : function(event) {
      // Remove hash from window location.
      var uri = window.location.href.substr(0, window.location.href.indexOf('#'));
      window.history.replaceState({}, document.title, uri);
    },

    maybeShowEventDetail : function(event) {
      var eventId = this.getEventIdFromWindowLocation();

      if (eventId) {
        this.showEventDetail(eventId);

        if (event.type == 'DOMContentLoaded') {
          scrollTo(this.$elem);
        }
      }
    },
  };

  window.MyEventsCalendar = App;
  window.addEventListener('DOMContentLoaded', App.init.bind(App));

})(window, document);

(function(){
  
  // Event subscription form
  jQuery(MyEventsCalendar).on('MyEvents.eventDetailLoaded', function(event, $content, eventId){
    
    var $form = $content.find('#event-subscription-form');

    $form.on('change', ':input[name="request"]', function (event){
      $form.trigger('submit');
    });

    $form.on('submit', function(event) {
      event.preventDefault();
      jQuery.post(MyEvents.ajaxurl, jQuery(this).serialize(), function(response){
        console.log(response);
        MyEventsCalendar.loadEventDetail(eventId, function(content){
          $content.html(content);
          jQuery(MyEventsCalendar).trigger('MyEvents.eventDetailLoaded', [$content, eventId]);
          $form.off();
        });
      });
    });

  });

})();
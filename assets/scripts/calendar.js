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
        height: 'auto',
      }, this.$elem.data('options'));

      this.loadedEvents = {};

      this.options.datesSet   = this.datesSet.bind(this);
      this.options.dateClick  = this.onMonthDayClick.bind(this);
      this.options.eventClick = this.eventClick.bind(this);
      this.options.eventDidMount = this.eventDidMount.bind(this);

      this.calendar = new Calendar(this.$elem.get(0), this.options);
      this.calendar.render();

      this.$elem.on('click', '.fc-timeGridWeek-view .fc-col-header-cell-cushion', this.onWeekDayClick.bind(this));
      jQuery(document).on('myEvents.breakpointChange', this.breakpointChange.bind(this));
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

    eventDidMount: function (info) {
      // Set event ID.
      jQuery(info.el).attr('data-id', info.event.id);
      // Set title attribute.
      jQuery(info.el).attr('title', info.event.title);
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

      // Remove previous loaded events.
      jQuery.each(this.loadedEvents, function(index, event){
        this.calendar.getEventById(event.id).remove();
      }.bind(this));

      var data = { action: 'my_events_get_events', start: start, end: end };

      this.$elem.addClass('is-loading');

      jQuery.post(MyEvents.ajaxurl, data ,function(response){

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
  };

  window.MyEventsCalendar = App;
  window.addEventListener('DOMContentLoaded', App.init.bind(App));

})(window, document);
(function(){

  function gotoDate() {
    var hash = window.location.hash;

    if (! hash) {
      return;
    }

    var matches = hash.match(/#calendar\/date\/(\d{4}-\d{2}-\d{2})/);

    if (! matches) {
      return;
    }

    var date = matches[1];

    MyEventsCalendar.calendar.gotoDate(date);
    MyEventsCalendar.$elem.find('.fc-day').filter(function(){
      return jQuery(this).data('date') == date;
    }).addClass('highlight');

    // Remove hash
    //window.history.replaceState({}, document.title, window.location.origin + window.location.pathname);
  }

  window.addEventListener('DOMContentLoaded', gotoDate);
  window.addEventListener('hashchange', gotoDate, false);

})();

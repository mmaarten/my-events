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

  function App(elem, options) {
    this.$elem = jQuery(elem);
    this.context = this.$elem.data('context');
    this.options = jQuery.extend({
      plugins: [ interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin, bootstrapPlugin, googleCalendarPlugin ],
      themeSystem: 'bootstrap',
      locale: nlLocale,
      initialView: 'dayGridMonth',
      eventTimeFormat: {hour: '2-digit', minute: '2-digit'},
      headerToolbar: { center: 'dayGridMonth,timeGridWeek,timeGridDay' },
      height: 'auto',
    }, this.$elem.data('options'), options);

    this.loadedEvents = {};

    this.options.datesSet      = this.datesSet.bind(this);
    this.options.dateClick     = this.onMonthDayClick.bind(this);
    this.options.eventDidMount = this.eventDidMount.bind(this);

    this.calendar = new Calendar(this.$elem.get(0), this.options);
    this.calendar.render();

    this.$elem.on('click', '.fc-timeGridWeek-view .fc-col-header-cell-cushion', this.onWeekDayClick.bind(this));
    jQuery(document).on('myEvents.breakpointChange', this.breakpointChange.bind(this));
  }

  App.prototype.datesSet = function(info) {
    // Remove all events
    jQuery.each(this.calendar.getEvents(), function(index, event){
      if (event.id && !event.source) {
        this.calendar.getEventById(event.id).remove();
      }
    }.bind(this));

    // load events
    this.loadEvents(info.startStr, info.endStr);
  };

  App.prototype.onMonthDayClick = function(info) {
    // Switch to day view
    this.calendar.changeView('timeGridDay');
    this.calendar.gotoDate(info.date);
  };

  App.prototype.onWeekDayClick = function(event) {
    // Switch to day view
    var date = jQuery(event.target).closest('.fc-col-header-cell').data('date');
    this.calendar.changeView('timeGridDay');
    this.calendar.gotoDate(date);
  };

  App.prototype.eventDidMount = function (info) {
    // Set event ID.
    jQuery(info.el).attr('data-id', info.event.id);
    // Set title attribute.
    jQuery(info.el).attr('title', info.event.title);
  };

  App.prototype.breakpointChange = function(event, breakpoint) {
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
  };

  App.prototype.addEvents = function(events) {
    jQuery.each(events, function(index, event) {
      this.calendar.addEvent(event);
    }.bind(this));
  };

  App.prototype.loadEvents = function(start, end) {
    var cacheKey = start + '|' + end;

    // Check cache
    if (this.loadedEvents.hasOwnProperty(cacheKey)) {
      // Add events
      this.addEvents(this.loadedEvents[cacheKey]);
      return;
    }

    // Load events
    this.$elem.addClass('is-loading');
    jQuery.post(MyEventsCalendarSettings.ajaxurl, { action: 'my_events_get_events', start: start, end: end, context: this.context }, function(response){
      // Save to cache.
      this.loadedEvents[cacheKey] = response.events;
      // Add loaded events.
      this.addEvents(response.events);
      // Remove loading class.
      this.$elem.removeClass('is-loading');
    }.bind(this));
  };

  window.MyEventsCalendar = App;

})(window, document);

(function(){

  jQuery.fn.calendar = function(options) {
    return this.each(function() {
      if (! jQuery(this).data('calendar')) {
        jQuery(this).data('calendar', new MyEventsCalendar(this, options));
      }
    });
  }

  jQuery('.calendar').calendar();
})();

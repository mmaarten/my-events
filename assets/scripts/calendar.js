import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from "@fullcalendar/interaction";
import bootstrapPlugin from '@fullcalendar/bootstrap';
import listPlugin from '@fullcalendar/list';
import googleCalendarPlugin from '@fullcalendar/google-calendar';
import allLocales from '@fullcalendar/core/locales-all';
import './components/breakpoints';

(function(){
  "use strict";

  var $elem = jQuery('#calendar');

  var calendar = new Calendar($elem.get(0), jQuery.extend({
    plugins: [ interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin, bootstrapPlugin, googleCalendarPlugin ],
    themeSystem: 'bootstrap',
    locales: allLocales,
    initialView: 'dayGridMonth',
    eventTimeFormat: {hour: '2-digit', minute: '2-digit'},
    headerToolbar: { center: 'dayGridMonth,timeGridWeek,timeGridDay' },
    datesSet : function(info){
      // Remove all events
      this.getEvents().forEach(event => {
        if (event.id && !event.source) {
          this.getEventById(event.id).remove();
        }
      });

      // Load events
      this.el.classList.add('is-loading');
      var data = { action: 'my_events_get_events', start: info.startStr, end: info.endStr};
      jQuery.post(MyEventsCalendarSettings.ajaxurl, data, function(response){
        // Add loaded events.
        response.events.forEach(event => calendar.addEvent(event));
        // Remove loading class.
        calendar.el.classList.remove('is-loading');
      });

    },
    // dayGridMonth date click
    dateClick : function(info){
      this.changeView('timeGridDay');
      this.gotoDate(info.date);
    },
    eventDidMount : function(info){
      info.el.setAttribute('data-id', info.event.id);
      info.el.setAttribute('title', info.event.title);
    },
  }, $elem.data('options')));

  calendar.render();

  // timeGridWeek date click
  jQuery(calendar.el).on('click', '.fc-timeGridWeek-view .fc-col-header-cell-cushion', function(event){
    // Switch to timeGridDay
    var date = jQuery(event.target).closest('.fc-col-header-cell').data('date');
    calendar.changeView('timeGridDay');
    calendar.gotoDate(date);
  });

  // Set alternate view for mobile.
  var restoreView;
  jQuery(document).on('myEvents.breakpointChange', function(event,breakpoint){
    if (breakpoint == 'xs' || breakpoint == 'sm') {
      // Set week list view.
      restoreView = calendar.view.type;
      calendar.changeView('listWeek');
    } else if (restoreView) {
      // Restore previous view
      calendar.changeView(restoreView);
      restoreView = null;
    }
  });
})();

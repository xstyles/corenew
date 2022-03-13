;(function () {
  jQuery(document).ready(function ($) {
    var calendarEl = $('.calendar')?.[0]

    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      initialDate: '2022-03-07',

      // eventDidMount: function(info) {
      //     var tooltip = new Tooltip(info.el, {
      //         title: info.event.extendedProps.description,
      //         placement: 'top',
      //         trigger: 'hover',
      //         container: 'body'
      //     });
      // },

      dateClick: function (dateClickInfo) {
        console.log('dateClickInfo =', dateClickInfo)

        const content = `<pre>${JSON.stringify(dateClickInfo)}</pre>`
        $('#my-content-id').html(content)
        $('#schedule-form').trigger('click')
      },

      eventClick: function (event) {
        console.log('event =', event)
      },

      events: [
        {
          title: 'Math',
          description: 'description of topic',
          start: '2022-03-07T14:00:00',
          end: '2022-03-07T17:00:00',
          // url: 'http://google.com/',
        },
        {
          title: 'ELA',
          description: 'description of topic',
          start: '2022-03-07T17:30:00',
          end: '2022-03-07T19:30:00',
          // url: 'http://google.com/',
        },
        {
          title: 'Math',
          description: 'description of topic',
          start: '2022-03-08T14:00:00',
          end: '2022-03-08T17:00:00',
          // url: 'http://google.com/',
        },
        {
          title: 'ELA',
          description: 'description of topic',
          start: '2022-03-08T17:30:00',
          end: '2022-03-08T19:30:00',
          // url: 'http://google.com/',
        },
        {
          title: 'Math',
          description: 'description of topic',
          start: '2022-03-09T14:00:00',
          end: '2022-03-09T17:00:00',
          // url: 'http://google.com/',
        },
        {
          title: 'ELA',
          description: 'description of topic',
          start: '2022-03-09T17:30:00',
          end: '2022-03-09T19:30:00',
          // url: 'http://google.com/',
        },
        {
          title: 'Math',
          description: 'description of topic',
          start: '2022-03-10T14:00:00',
          end: '2022-03-10T17:00:00',
          // url: 'http://google.com/',
        },
        {
          title: 'ELA',
          description: 'description of topic',
          start: '2022-03-10T17:30:00',
          end: '2022-03-10T19:30:00',
          // url: 'http://google.com/',
        },
      ],
    })

    calendar.render()
  })
})(jQuery)

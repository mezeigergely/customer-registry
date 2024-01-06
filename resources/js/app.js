import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin ],
        timeZone: 'UTC',
        initialView: 'timeGridDay',
        events: '/events',
        selectable: true,
        selectMirror: true,
        unselectAuto: true,
        select: function (reservation) {
            var customerName = window.prompt('Ügyfél neve:');
            var startDate = reservation.start;
            var endDate = reservation.end;
            
            fetch('/create-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: customerName,
                    start: startDate,
                    end: endDate,
                }),
            })
            .then(response => response.json())
            .then(data => {
                calendar.refetchEvents();
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
            });
            
            calendar.unselect();
        },
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
    });
    calendar.render();
});
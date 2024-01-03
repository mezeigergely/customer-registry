import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin ],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,dayGridDay'
        }
    });
    calendar.render();
});
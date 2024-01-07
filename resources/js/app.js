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
        businessHours: {
            startTime: '10:00',
            endTime: '19:00',
            daysOfWeek: [1, 2, 3, 4, 5]
        },
        weekNumbers: true,
        selectable: true,
        selectMirror: true,
        unselectAuto: true,
        select: function (reservation) {
            var customerName;
            var startDate = reservation.start;
            var endDate = reservation.end;
            var daysOfWeek = reservation.daysOfWeek;
            var recurrence;
            var customerNameSwal = {
                title: 'Ügyfél neve:',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Mégse'
            };
            Swal.fire(customerNameSwal).then((result) => {
                if (result.isConfirmed) {
                  customerName = result.value;
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
                        daysOfWeek: [daysOfWeek],
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
                }
              });
            var setRecurrenceSwal = {
                title: 'Válasszon egy lehetőséget:',
                input: 'select',
                inputOptions: {
                  'option1': 'Lehetőség 1',
                  'option2': 'Lehetőség 2',
                  'option3': 'Lehetőség 3'
                },
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Mégse'
            };
            /*
            Swal.fire(customerName).then((result) => {
                if (result.isConfirmed) {
                  const selectedOption = result.value;
                  Swal.fire(setRecurrenceSwal).then((result) => {
                    if (result.isConfirmed) {
                      const selectedOption = result.value;
                      Swal.fire({
                        title: `Ön a következőt választotta: ${selectedOption}`,
                        icon: 'success'
                      });
                    }
                  });
                }
              });
            */
            
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
import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction';
import '../css/app.css';

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin],
        timeZone: 'UTC',
        initialView: 'timeGridWeek',
        events: '/events',
        slotMinTime: '08:00',
        slotMaxTime: '20:00',
        weekNumbers: true,
        weekends: false,
        selectable: true,
        selectMirror: true,
        unselectAuto: true,
        select: function (reservation) {
          var setRecurranceSwal = {
            title: formatTitle(reservation.startStr, reservation.endStr),
            html: `  
                <div class="inputStyle">
                  <label for="title">Ügyfél neve:*</label>
                  <input type="text" name="title" class="swal2-input" id="title">
                </div>
                <div class="inputStyle">
                  <label for="freq">Ismétlődés:</label>
                  <select name="freq" id="freqSelect">
                    <option value="">Soha</option>
                    <option value="WEEKLY">Minden héten</option>
                    <option value="EVEN_WEEKLY">Minden páros héten</option>
                    <option value="ODD_WEEKLY">Minden páratlan héten</option>
                  </select>
                </div>
                <div class="inputStyle">
                  <div id="untilDiv">
                    <label for="until" id="untilLabel">Eddig:*</label>
                    <input type="date" id="until" name="until"/>
                  </div>
                </div>
                `,
            willOpen: () => {
              const freqSelect = document.getElementById('freqSelect');
              var untilDiv = document.getElementById('untilDiv');
              var untilInput = document.getElementById('until');
              untilInput.min = untilMin(reservation.start);
              untilDiv.style.display = 'none';

              freqSelect.addEventListener('change', (event) => {
                if(event.target.value){
                  untilDiv.style.display = 'flex';
                }
                else{
                  untilDiv.style.display = 'none';
                  untilInput.value = '';
                }
              });
            },
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Mégse'
          };

          Swal.fire(setRecurranceSwal).then((result) => {
            if (result.isConfirmed) {
              const title = document.getElementById('title').value;
              const start = reservation.start;
              const end = reservation.end;

              fetch('/create-event', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                  title: title,
                  start: start,
                  end: end,
                  recurrance: freqSelect.value,
                  until: until.value,
                }),
              })
              .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Hiba');
                }
              })
              .then(data => {
                  calendar.refetchEvents();
                  Swal.fire({
                    title: data.message,
                    text: formatSwalText(title, start, end),
                    icon: "success"
                  });
              })
              .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Hiba',
                    text: error.message,
                    icon: 'error'
                });
              })
            }
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

    const days = ['Vasárnap','Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat'];

    function formatSwalText(title, start, end) {
      return '(' + title + ') ' + formatTitle(start, end)
    }

    function formatTitle(startStr, endStr) {
      var startDate = new Date(startStr);
      var endDate = new Date(endStr);

      var formattedStartDate = formatDate(startDate);
      var formattedEndDate = formatDate(endDate);

      var startDayName = getDayName(startDate, days);
      var endDayName = getDayName(endDate, days);

      if (isNextDay(startDate, endDate)) {
        return formattedStartDate + ' - ' + formattedEndDate + '\n' + '(' + startDayName + ')';
      } else {
        return formattedStartDate + ' - ' + formattedEndDate + '\n' + '(' + startDayName + ' - ' + endDayName + ')';
      }
    }

    function formatDate(date) {
      var year = date.getFullYear();
      var month = (date.getMonth() + 1).toString().padStart(2, '0');
      var day = date.getDate().toString().padStart(2, '0');
      var hours = date.getHours().toString().padStart(2, '0');
      var minutes = date.getMinutes().toString().padStart(2, '0');
    
      return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes;
    }

    function getDayName(date, days) {
      var dayIndex = date.getDay();
      
      return days[dayIndex];
    }

    function isNextDay(date1, date2) {
      return date1.toDateString() === date2.toDateString();
    }

    function untilMin(starDate){
      return new Date(starDate).toISOString().split('T')[0];
    }
});
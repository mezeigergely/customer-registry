import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction';
import rrulePlugin from '@fullcalendar/rrule';
import '../css/app.css';

document.addEventListener('DOMContentLoaded', function() {
  

    const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin, timeGridPlugin, interactionPlugin, rrulePlugin ],
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
          var setRecurrenceSwal = {
            title: formatTitle(reservation.startStr, reservation.endStr),
            html: `  
                <div class="inputStyle">
                  <label for="title">Ügyfél neve:</label>
                  <input type="text" name="title" class="swal2-input" id="title">
                </div>
                <div class="inputStyle">
                  <label for="freq">Ismétlődés:</label>
                  <select name="freq" id="freqSelect">
                    <option value="">Soha</option>
                    <option value="DAILY">Naponta</option>
                    <option value="WEEKLY">Hetente</option>
                  </select>
                </div>
                <div class="" id="specFreq">
                  <label for="byDay">Speciális napokon:</label>
                  <input type="checkbox" id="byDayMo" name="byDayMo" value="MO">
                  <label for="byDayMo">H</label>
                  <input type="checkbox" id="byDayTu" name="byDayTu" value="TU">
                  <label for="byDayTu">K</label>
                  <input type="checkbox" id="byDayWe" name="byDayWe" value="WE">
                  <label for="byDayWe">Sz</label>
                  <input type="checkbox" id="byDayTh" name="byDayTh" value="TH">
                  <label for="byDayTh">Cs</label>
                  <input type="checkbox" id="byDayFr" name="byDayFr" value="FR">
                  <label for="byDayFr">P</label>
                </div>
                <div class="inputStyle">
                  <div class="" id="specWeek">
                    <label for="specWeek">Speciális heteken:</label>
                    <select name="specWeek" id="specWeekSelect">
                      <option value="" disabled selected>Válassz egy lehetőséget</option>
                      <option value="ODD_WEEKLY">Páratlan hetente</option>
                      <option value="EVEN_WEEKLY">Páros hetente</option>
                    </select>
                  </div>
                </div>
                `,
            willOpen: () => {
              const freqSelect = document.getElementById('freqSelect');
              const specFreq = document.getElementById('specFreq');
              const checkboxes = document.querySelectorAll('#specFreq input[type="checkbox"]');
              const specWeek = document.getElementById('specWeek');
              var specWeekSelect = document.getElementById('specWeekSelect');
              specFreq.style.display = 'none';
              specWeek.style.display = 'none';
              freqSelect.addEventListener('change', (event) => {
                if(event.target.value == 'DAILY'){
                  specFreq.style.display = 'flex';
                  checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                  });
                  specWeek.style.display = 'none';
                  specWeekSelect.value = '';
                }
                else if (event.target.value == 'WEEKLY'){
                  specFreq.style.display = 'flex';
                  specWeek.style.display = 'flex';
                }
                else{
                  specFreq.style.display = 'none';
                  checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                  });
                  specWeek.style.display = 'none';
                  specWeekSelect.value = '';
                }
              });
            },
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Mégse'
          };

          Swal.fire(setRecurrenceSwal).then((result) => {
            if (result.isConfirmed) {
              const checkboxes = document.querySelectorAll('#specFreq input[type="checkbox"]');
              const selectedDays = [];
              checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                  selectedDays.push(checkbox.value);
                } else {
                  const index = selectedDays.indexOf(checkbox.value);
                  if (index !== -1) {
                    selectedDays.splice(index, 1);
                  }
                }
              });

              const title = document.getElementById('title').value;
              const start = reservation.start;
              const end = reservation.end;
              var freq = freqSelect.value;
              var specWeek = specWeekSelect.value;
              /*fetch('/events')
              .then(response => response.json())
              .then(data => {
                // Itt kezeld meg a visszakapott adatokat
                console.log(data);
              })
              .catch(error => {
                console.error('Error:', error);
              });*/
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
                    freq: freq,
                    byDay: selectedDays,
                    specWeek: specWeek
                }),
              })
              .then(response => response.json())
              .then(data => {
                  calendar.refetchEvents();
                  alert(data.message);
              })
              .catch(error => {
                  console.error('Error:', error);
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

    function formatTitle(startStr, endStr) {
      var startDate = new Date(startStr);
      var endDate = new Date(endStr);

      var formattedStartDate = formatDate(startDate);
      var formattedEndDate = formatDate(endDate);

      var startDayName = getDayName(startDate);
      var endDayName = getDayName(endDate);

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

    function getDayName(date) {
      var days = ['Vasárnap','Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat'];
      var dayIndex = date.getDay();
      
      return days[dayIndex];
    }

    function isNextDay(date1, date2) {
      return date1.toDateString() === date2.toDateString();
    }
});
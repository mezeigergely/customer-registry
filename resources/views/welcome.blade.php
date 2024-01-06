<!DOCTYPE html>
<html lang='en'>
  <head>
    <meta charset='utf-8' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Customer Registry</title>
  </head>
  <body>
    <div id='calendar'></div>
  </body>
</html>
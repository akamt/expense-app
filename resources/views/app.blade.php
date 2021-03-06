<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>経費精算</title>

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <link rel="shortcut icon" href="/favicon.png">

        <script>
            window.Laravel = {csrfToken: "{{ csrf_token() }}"};
        </script>
    </head>
    <body>
        <div id="app"></div>
    </body>
    <script src="{{ mix('js/app.js') }}"></script>
</html>
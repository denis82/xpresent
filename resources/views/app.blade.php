<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Xpresent</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @vite(['resources/css/app.css'])
        <link href="{{ asset('assets/admin/css/admin.css') }}" rel="stylesheet">
    </head>
    <body>
        <div id="app">
            <booking-form></booking-form>
        </div>

        @vite(['resources/js/app.js'])
        <link href="{{ asset('assets/admin/js/admin.js') }}" rel="stylesheet">
    </body>
</html>

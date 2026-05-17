<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="antialiased bg-gray-100 dark:bg-gray-900">
    <a href="{{ url('/admin') }}" class="absolute top-5 left-5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
        <i class="fas fa-home text-2xl"></i>
    </a>
    <div class="min-h-screen flex flex-col justify-center items-center py-6 sm:pt-0">
        <div class="mb-2">
            <a href="/">
                <img src="{{ asset('images/logo.png') }}" class="w-20 h-20 fill-current text-gray-500 shadow-lg rounded-full border-4 border-blue-600" alt="Logo" />
            </a>
        </div>

        <div class="w-full max-w-4xl p-6 bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg shadow-md">
            {{ $slot }}
        </div>
    </div>

    @filamentScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div id="success-message" data-message="{{ session('success') }}" style="display: none;"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successElement = document.getElementById('success-message');
            const successMessage = successElement ? successElement.getAttribute('data-message') : null;

            if (successMessage) {
                Swal.fire({
                    title: 'Success!',
                    text: successMessage,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
    @livewire('notifications')
</body>

</html>
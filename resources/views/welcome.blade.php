@php
    $vite = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Контроль ДГУ') }}</title>
        @if ($vite)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; background: #f9fafb; color: #111827; }
                .nav { width: 100%; max-width: 28rem; display: flex; justify-content: flex-end; gap: 1rem; margin-bottom: 2rem; }
                .nav a { font-size: 0.875rem; color: #4f46e5; text-decoration: none; }
                .nav a:hover { text-decoration: underline; }
                .card { width: 100%; max-width: 28rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 2rem; text-align: center; box-shadow: 0 1px 2px rgb(0 0 0 / 0.05); }
                h1 { font-size: 1.5rem; font-weight: 600; margin: 0 0 1rem; }
                p { margin: 0; color: #4b5563; font-size: 0.875rem; line-height: 1.5; }
                .btn { display: inline-block; margin-top: 1.5rem; padding: 0.5rem 1.25rem; background: #111827; color: #fff; font-size: 0.875rem; font-weight: 500; border-radius: 0.375rem; text-decoration: none; }
                .btn:hover { background: #1f2937; }
            </style>
        @endif
    </head>
    <body @if($vite) class="min-h-screen bg-gray-50 text-gray-900 antialiased flex flex-col items-center justify-center p-6" @endif>
            @if (Route::has('login'))
            <header @if($vite) class="w-full max-w-md mb-8 flex justify-end" @else class="nav" @endif>
                <nav class="flex items-center gap-4 text-sm">
                    @auth
                        <a href="{{ url('/dashboard') }}" @if($vite) class="text-indigo-600 hover:underline" @endif>{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" @if($vite) class="text-indigo-600 hover:underline" @endif>{{ __('Log in') }}</a>
                        @if (config('app.allow_public_registration'))
                            <a href="{{ route('register') }}" @if($vite) class="text-indigo-600 hover:underline" @endif>{{ __('Register') }}</a>
                        @endif
                    @endauth
                </nav>
        </header>
        @endif

        <main @if($vite) class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-8 shadow-sm text-center" @else class="card" @endif>
            <h1 @if($vite) class="text-2xl font-semibold text-gray-900" @endif>{{ config('app.name', 'Контроль ДГУ') }}</h1>
            <p @if($vite) class="mt-4 text-gray-600 text-sm" @endif>Система мониторинга дизель-генераторных установок.</p>
            @guest
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" @if($vite) class="mt-6 inline-block rounded-md bg-gray-900 px-5 py-2 text-sm font-medium text-white hover:bg-gray-800" @else class="btn" @endif>{{ __('Log in') }}</a>
                @endif
            @endguest
        </main>
    </body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSV Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ✅ Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ✅ Bootstrap Icons (optional, for nice icons in buttons) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- ✅ Your Custom CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Optional: Your legacy table styles -->
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container mt-4">
         <!-- ✅ App Name Display -->
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">{{ config('app.name', 'CSV Manager') }}</h2>
        </div>
        @auth
            <div class="mb-3">
                <p>Logged in as <strong>{{ auth()->user()->name }}</strong> |
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                </p>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                    @csrf
                </form>
            </div>
        @endauth

        @if(session('success') || $errors->any())
            @php
                $successMessage = session('success');
                $isDeleteMessage = $successMessage && str_contains(strtolower($successMessage), 'delete');
            @endphp

            <div class="alert {{ $isDeleteMessage ? 'alert-danger' : 'alert-success' }}">
                @if($successMessage)
                    {{ $successMessage }}
                @else
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif


        @yield('content')
    </div>

    <!-- ✅ Bootstrap JS (at the end for performance) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

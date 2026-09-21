<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'CCTV CRM')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<nav class="bg-white shadow mb-6">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex gap-6">
            <a href="{{ route('leads.index') }}" class="font-bold text-lg text-blue-700">CCTV CRM</a>
            <a href="{{ route('leads.index') }}" class="text-gray-600 hover:text-blue-600">Leads</a>
            <a href="{{ route('jobs.index') }}" class="text-gray-600 hover:text-blue-600">Jobs</a>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-gray-500 hover:text-red-600">Logout ({{ auth()->user()->name }})</button>
        </form>
    </div>
</nav>

<div class="container mx-auto px-4 pb-12">
    @if (session('status'))
        <div class="bg-blue-100 text-blue-800 p-3 rounded mb-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>
</body>
</html>
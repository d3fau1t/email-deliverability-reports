<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Delivered') }}</title>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Bootstrap CSS (for modals) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<nav class="bg-white shadow">
    <div class="container mx-auto px-4 py-4">
        <a class="text-2xl font-semibold text-gray-800" href="{{ url('/') }}">{{ config('app.name', 'Delivered') }}</a>
    </div>
</nav>

<div class="container mx-auto my-8">
    @yield('content')
</div>

<!-- Bootstrap JS (for modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

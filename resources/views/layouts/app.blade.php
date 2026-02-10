<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Design Lab')</title>
    <style>
        body { margin:0; font-family: Inter, Arial, sans-serif; background:#f6f7fb; color:#222; }
        .shell { display:grid; grid-template-columns: 280px 1fr 260px; gap:12px; padding:12px; min-height:100vh; }
        .panel { background:#fff; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.08); padding:10px; }
        .toolbar button, .toolbar select, .toolbar input { margin-bottom:8px; width:100%; }
        #canvas-wrap { background:#fff; border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.08); padding:10px; overflow:auto; }
        .layer-item { display:flex; justify-content:space-between; padding:4px; border-bottom:1px solid #eee; }
    </style>
    @stack('head')
</head>
<body>
@yield('content')
@stack('scripts')
</body>
</html>

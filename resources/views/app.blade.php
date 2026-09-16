<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Chivo:wght@400;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2">
  <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}?v=2">

  <link rel="manifest" href="/manifest.json?v=2">
  <meta name="theme-color" content="#006194">

  @isset($page['props']['metaTitle'])
    <title>{{ $page['props']['metaTitle'] }}</title>
    <meta property="og:title" content="{{ $page['props']['metaTitle'] }}">
    <meta name="twitter:title" content="{{ $page['props']['metaTitle'] }}">
  @else
    <title>{{ config('app.name', 'Simbiosis News') }}</title>
  @endisset

  @isset($page['props']['metaDescription'])
    <meta name="description" content="{{ $page['props']['metaDescription'] }}">
    <meta property="og:description" content="{{ $page['props']['metaDescription'] }}">
    <meta name="twitter:description" content="{{ $page['props']['metaDescription'] }}">
  @endisset

  @isset($page['props']['ogImage'])
    <meta property="og:image" content="{{ url($page['props']['ogImage']) }}">
    <meta name="twitter:image" content="{{ url($page['props']['ogImage']) }}">
  @else
    <meta property="og:image" content="{{ url('/brand/fallback-image.png') }}">
  @endisset

  @isset($page['props']['canonicalUrl'])
    <link rel="canonical" href="{{ $page['props']['canonicalUrl'] }}">
    <meta property="og:url" content="{{ $page['props']['canonicalUrl'] }}">
  @endisset

  <meta property="og:type" content="article">
  <meta name="twitter:card" content="summary_large_image">

  @routes
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @inertiaHead
</head>
<body class="min-h-full bg-background text-on-background">
  @inertia
</body>
</html>

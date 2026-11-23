@props(['tenant', 'title' => null])
@include('layouts.agreement-public', ['tenant' => $tenant, 'title' => $title, 'slot' => $slot])

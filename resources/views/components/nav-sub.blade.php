@props(['href', 'active' => false])

<a href="{{ $href }}" @if($active) aria-current="page" @endif {{ $attributes->class(['ds-nav-sub']) }}>
    <span class="truncate">{{ $slot }}</span>
</a>

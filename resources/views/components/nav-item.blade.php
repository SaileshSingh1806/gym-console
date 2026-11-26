@props(['href', 'active' => false, 'icon' => null, 'count' => null])

<a href="{{ $href }}" @if($active) aria-current="page" @endif {{ $attributes->class(['ds-nav-item']) }}>
    @if($icon)
        <x-icon :name="$icon" class="ds-nav-icon" />
    @endif
    <span class="truncate">{{ $slot }}</span>
    @if($count)
        <span class="ds-nav-count">{{ $count }}</span>
    @endif
</a>

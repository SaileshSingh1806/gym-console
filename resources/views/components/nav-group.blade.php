@props(['label', 'icon' => null, 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button type="button"
            @click="open = !open"
            :aria-expanded="open.toString()"
            @if($active) data-active="true" @endif
            class="ds-nav-item w-full cursor-pointer">
        @if($icon)
            <x-icon :name="$icon" class="ds-nav-icon" />
        @endif
        <span class="truncate text-left">{{ $label }}</span>
        <span class="ds-nav-chevron" :class="open ? 'rotate-90' : ''">
            <x-icon name="chevron-right" :size="14" />
        </span>
    </button>
    <div x-show="open" x-cloak class="ds-nav-children space-y-0.5">
        {{ $slot }}
    </div>
</div>

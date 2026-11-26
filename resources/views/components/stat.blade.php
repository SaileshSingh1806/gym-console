@props(['label', 'value', 'href' => null, 'tone' => null, 'flag' => false])

{{--
    Single KPI cell. Lives inside a `.ds-stat-grid` (cells separated by 1px hairlines, no per-cell shadows or tiles).
    - Pass `href` for a link, omit it for a button (use @click on the component).
    - `flag` + `tone` (warning|danger) shows a small status dot beside the label. Colour is only used for status.
--}}
@php
    $tag = $href ? 'a' : 'button';
    $dot = ['warning' => 'text-warning', 'danger' => 'text-danger'][$tone] ?? 'text-fg-subtle';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @else type="button" @endif
    {{ $attributes->class(['block w-full bg-surface p-4 text-left transition-colors hover:bg-raised/60 cursor-pointer']) }}>
    <span class="flex items-center gap-1.5 ds-caption">
        <span class="truncate">{{ $label }}</span>
        @if($flag)
            <span class="ds-dot {{ $dot }} shrink-0" aria-hidden="true"></span>
        @endif
    </span>
    <span class="ds-kpi mt-1 block truncate">{{ $value }}</span>
</{{ $tag }}>

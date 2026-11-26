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
    {{ $attributes->class(['ds-stat']) }}>
    <span class="ds-stat-label">
        <span class="truncate">{{ $label }}</span>
        @if($flag)
            <span class="ds-dot {{ $dot }} shrink-0" aria-hidden="true"></span>
        @endif
    </span>
    <span class="ds-stat-value">{{ $value }}</span>
</{{ $tag }}>

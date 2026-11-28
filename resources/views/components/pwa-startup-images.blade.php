@props(['tenant', 'role', 'version'])
@php
    $screens = [[440,956,3],[402,874,3],[430,932,3],[393,852,3],[390,844,3],[375,812,3],[428,926,3],[414,896,3],[414,896,2],[375,667,2],[414,736,3]];
@endphp
@foreach($screens as [$width, $height, $ratio])
    @foreach(['portrait', 'landscape'] as $orientation)
        @php
            $imageWidth = ($orientation === 'portrait' ? $width : $height) * $ratio;
            $imageHeight = ($orientation === 'portrait' ? $height : $width) * $ratio;
        @endphp
        <link rel="apple-touch-startup-image" type="image/png" href="{{ route('pwa.startup', [$tenant->slug, $role, $imageWidth, $imageHeight, 'v' => $version], false) }}" media="(device-width: {{ $width }}px) and (device-height: {{ $height }}px) and (-webkit-device-pixel-ratio: {{ $ratio }}) and (orientation: {{ $orientation }})">
    @endforeach
@endforeach

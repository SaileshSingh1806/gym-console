@props(['tenant', 'role', 'version'])
@php
    $ios = preg_match('/iPhone|iPad|iPod/i', request()->userAgent() ?? '') === 1;
    $screens = [[440,956,3],[402,874,3],[430,932,3],[393,852,3],[390,844,3],[375,812,3],[428,926,3],[414,896,3],[414,896,2],[375,667,2],[414,736,3]];
@endphp
@foreach($screens as [$width, $height, $ratio])
    @foreach(['portrait', 'landscape'] as $orientation)
        @php
            $imageWidth = ($orientation === 'portrait' ? $width : $height) * $ratio;
            $imageHeight = ($orientation === 'portrait' ? $height : $width) * $ratio;
            // Pre-generate the affected device's assets before Safari captures install metadata.
            $url = $ios && $width === 440
                ? app(\App\Http\Controllers\Web\PwaController::class)->startupImageUrl($tenant, $role, $imageWidth, $imageHeight)
                : route('pwa.startup', [$tenant->slug, $role, $imageWidth, $imageHeight, 'v' => $version.'-2'], false);
        @endphp
        <link rel="apple-touch-startup-image" type="image/png" sizes="{{ $imageWidth }}x{{ $imageHeight }}" href="{{ $url }}" media="(device-width: {{ $width }}px) and (device-height: {{ $height }}px) and (-webkit-device-pixel-ratio: {{ $ratio }}) and (orientation: {{ $orientation }})">
    @endforeach
@endforeach

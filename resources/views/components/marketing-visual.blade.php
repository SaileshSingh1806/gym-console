@props(['image', 'alt' => '', 'kind' => 'dashboard', 'eager' => false])
@php
    $available = file_exists(public_path('assets/img/'.$image.'.webp'));
    [$imageWidth,$imageHeight] = str_starts_with($image,'phone-') ? [900,1800] : (in_array($image,['trainer-tablet','biometric-entry'],true) ? [1600,1100] : [1800,1130]);
@endphp
@if($available)
    <div class="gd-frame"><img src="{{ asset('assets/img/'.$image.'.webp') }}" width="{{ $imageWidth }}" height="{{ $imageHeight }}" alt="{{ $alt }}" @if($eager) fetchpriority="high" @else loading="lazy" decoding="async" @endif></div>
@elseif(in_array($kind,['checkin','payment','whatsapp'],true))
    <div class="gd-phone" aria-label="{{ $alt }}">
        <p class="gd-demo-example">Illustrative product preview</p>
        @if($kind === 'checkin')
            <h4>Check-in</h4><div class="gd-phone-card"><strong style="color:#4abe8c">✓</strong><h4>Checked in</h4><p>Rohan · 6:42 am</p></div><div class="gd-phone-card"><p>Membership</p><h4>Active</h4></div><div class="gd-phone-action">Done</div>
        @elseif($kind === 'payment')
            <h4>Record payment</h4><div class="gd-phone-card"><p>Amount</p><strong>₹2,400</strong></div><div class="gd-phone-card"><p>Payment method</p><h4>UPI</h4><p>Remaining balance ₹0</p></div><div class="gd-phone-action">Save payment</div>
        @else
            <h4>Renewal reminder</h4><div class="gd-phone-card"><p>Your membership ends in 3 days. Renew now to keep your plan.</p><p style="margin-top:12px;color:#4abe8c">Sent ✓</p></div><div class="gd-phone-action">View membership</div>
        @endif
    </div>
@else
    <div class="gd-frame" aria-label="{{ $alt }}">
        <div class="gd-browser"><i></i><i></i><i></i><span>GymDeskHub / Owner workspace</span></div>
        <div class="gd-demo">
            <aside><strong>GymDesk<span style="display:inline;color:#f5a524">Hub</span></strong>@foreach(['Dashboard','Members','Attendance','Payments','Trainers','Reports'] as $item)<span>{{ $item }}</span>@endforeach</aside>
            <div class="gd-demo-body"><p class="gd-demo-example">Illustrative product preview</p><h4>{{ $kind === 'members' ? 'Members' : ($kind === 'trainer' ? 'Coaching workspace' : ($kind === 'biometric' ? 'Access records' : 'Dashboard')) }}</h4>
                @if($kind === 'dashboard')
                <div class="gd-demo-metrics"><div><p>Collected today</p><strong>₹18,400</strong></div><div><p>Active members</p><strong>238</strong></div><div><p>Check-ins</p><strong>126</strong></div></div>
                <svg class="gd-demo-chart" viewBox="0 0 320 100" preserveAspectRatio="none" role="img" aria-label="Illustrative revenue trend"><path d="M0 20H320 M0 50H320 M0 80H320" stroke="#26282d" fill="none"/><path d="M0 79 L28 72 56 77 84 49 112 57 140 37 168 52 196 30 224 40 252 18 280 24 320 8" stroke="#f5a524" stroke-width="2" fill="none" vector-effect="non-scaling-stroke"/></svg>
                @endif
                @php($rows = $kind === 'trainer' ? [['Strength training','Assigned'],['Workout plan','Updated'],['Nutrition plan','Ready']] : ($kind === 'biometric' ? [['Front entrance','Connected'],['Check-in','Recorded'],['Access logs','Available']] : [['Rohan Patel','Active'],['Neha Sharma','Active'],['Amit Rathod','Renewal due']]))
                @foreach($rows as [$name,$status])<div class="gd-demo-row"><span>{{ $name }}</span><span>{{ $status }}</span></div>@endforeach
            </div>
        </div>
        <div class="gd-note">Sample data for illustration only</div>
    </div>
@endif
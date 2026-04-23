{{-- 5. Feature Flags --}}
<div class="pm-card">
    <div class="pm-card-title"><i class="fas fa-toggle-on"></i> Feature Flags</div>

    <div class="feat-grid">
        @php
            $flags = [
                'has_trainers'       => ['👨‍🏫', 'Trainer Management',   'Allow trainer accounts & management'],
                'has_assessments'    => ['📝',  'Assessments',          'Trainer-led competency assessments'],
                'has_certificates'   => ['🏅',  'Certificates',         'Issue & download certificates'],
                'has_custom_reports' => ['📊',  'Custom Reports',       'Custom report builder & analytics'],
                'has_branding'       => ['🎨',  'Custom Branding',      'Custom logo, colors & tagline'],
            ];
        @endphp

        @foreach($flags as $flag => [$ficon, $flagLabel, $flagHint])
            @php $isChecked = (bool) old($flag, $plan->{$flag} ?? false); @endphp
            <div class="feat-toggle">
                <input type="checkbox" name="{{ $flag }}" value="1"
                       id="flag-{{ $flag }}" {{ $isChecked ? 'checked' : '' }}
                       onchange="syncFeatCheck(this)">
                <label for="flag-{{ $flag }}" title="{{ $flagHint }}">
                    <span class="feat-check" id="check-{{ $flag }}">✓</span>
                    <span style="font-size:15px;line-height:1;flex-shrink:0;">{{ $ficon }}</span>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--sa-text);">{{ $flagLabel }}</div>
                        <div style="font-size:11px;font-weight:400;color:var(--sa-muted);margin-top:1px;">{{ $flagHint }}</div>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
</div>

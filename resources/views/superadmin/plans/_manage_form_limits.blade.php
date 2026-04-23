{{-- 3. Usage Limits --}}
<div class="pm-card">
    <div class="pm-card-title">
        <i class="fas fa-sliders-h"></i> Usage Limits
        <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--sa-muted);">
            — toggle "Unlimited" to remove the cap (null = unlimited, 0 = not allowed)
        </span>
    </div>

    <div class="form-grid-2">
        @php
            $limits = [
                ['max_trainees',        'Max Trainees',          'Max simultaneous trainees enrolled'],
                ['max_trainers',        'Max Trainers',          'Trainer accounts (0 = no trainers on this plan)'],
                ['max_users',           'Max Admin Users',       'Admin user accounts'],
                ['max_courses',         'Max Courses',           'Active course records'],
                ['max_exports_monthly', 'Monthly Export Records', 'Records exported per calendar month (0 = no exports)'],
            ];
        @endphp

        @foreach($limits as [$field, $label, $hint])
            @php
                $val         = old($field, $plan->{$field} ?? null);
                $isUnlimited = $val === null;
            @endphp
            <div class="limit-row">
                <label style="font-size:11px;font-weight:700;color:var(--sa-muted);text-transform:uppercase;letter-spacing:.4px;">
                    {{ $label }}
                </label>
                <div class="limit-input-wrap">
                    <input type="number" name="{{ $field }}" id="inp-{{ $field }}"
                           value="{{ $isUnlimited ? '' : $val }}" min="0"
                           {{ $isUnlimited ? 'disabled' : '' }} placeholder="e.g. 100">
                    <label class="unlimited-toggle">
                        <input type="checkbox" id="ulim-{{ $field }}"
                               {{ $isUnlimited ? 'checked' : '' }}
                               onchange="toggleUnlimited('{{ $field }}', this.checked)">
                        ∞ Unlimited
                    </label>
                </div>
                <span class="hint">{{ $hint }}</span>
            </div>
        @endforeach
    </div>
</div>

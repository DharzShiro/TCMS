{{-- 6. Availability Window --}}
<div class="pm-card">
    <div class="pm-card-title"><i class="fas fa-calendar-alt"></i> Availability Window</div>

    @php
        $from  = old('available_from',  isset($plan->available_from)  ? $plan->available_from  : null);
        $until = old('available_until', isset($plan->available_until) ? $plan->available_until : null);
        $always = !$from && !$until;
    @endphp

    <div class="date-row">
        <label class="always-available-wrap">
            <input type="checkbox" id="always-avail"
                   {{ $always ? 'checked' : '' }}
                   onchange="toggleAvailability(this.checked)">
            Always available (no date restriction)
        </label>

        <div class="fi" id="wrap-from" style="{{ $always ? 'display:none' : '' }}">
            <label>Available From</label>
            <input type="date" name="available_from" id="inp-from"
                   value="{{ $from ? \Carbon\Carbon::parse($from)->format('Y-m-d') : '' }}">
            <span class="hint">Plan appears on upgrade page starting this date.</span>
        </div>

        <div class="fi" id="wrap-until" style="{{ $always ? 'display:none' : '' }}">
            <label>Available Until</label>
            <input type="date" name="available_until" id="inp-until"
                   value="{{ $until ? \Carbon\Carbon::parse($until)->format('Y-m-d') : '' }}">
            <span class="hint">Plan is hidden after this date. Leave blank = no end date.</span>
        </div>
    </div>
</div>

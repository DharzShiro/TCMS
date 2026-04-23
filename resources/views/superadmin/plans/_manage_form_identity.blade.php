{{-- 1. Identity --}}
<div class="pm-card">
    <div class="pm-card-title"><i class="fas fa-id-card"></i> Plan Identity</div>

    <div class="form-grid-2" style="margin-bottom:18px;">
        <div class="fi">
            <label>Display Name *</label>
            <input type="text" name="name" id="inp-name"
                   value="{{ old('name', $plan->name ?? '') }}"
                   placeholder="e.g. Gold Plan" required maxlength="100"
                   oninput="autoSlug()">
        </div>

        <div class="fi">
            <label>Plan Icon</label>
            <div class="icon-picker" id="icon-picker">
                @php
                    $iconOptions = ['🌱','🚀','💎','⭐','🔥','👑','🏆','🎯','💡','🛡️','⚡','🌟'];
                    $currentIcon = old('icon', $plan->icon ?? '🌱');
                @endphp
                @foreach($iconOptions as $ico)
                    <div class="icon-opt">
                        <input type="radio" name="icon" value="{{ $ico }}"
                               id="icon-{{ $loop->index }}"
                               {{ $currentIcon === $ico ? 'checked' : '' }}>
                        <label for="icon-{{ $loop->index }}">{{ $ico }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="fi">
            <label>Plan Slug (ID) *</label>
            <input type="text" name="slug" id="inp-slug"
                   value="{{ old('slug', $plan->slug ?? '') }}"
                   placeholder="e.g. gold-plan"
                   {{ isset($plan) ? '' : '' }}
                   oninput="sanitizeSlug(this)"
                   maxlength="50" required>
            <span class="hint">
                Unique identifier — lowercase letters, numbers, hyphens only.
                @if(isset($plan))
                    ⚠️ Changing the slug will break existing tenant subscriptions on this plan.
                @endif
            </span>
            <div id="slug-preview" class="slug-preview" style="{{ old('slug', $plan->slug ?? '') ? '' : 'display:none' }}">
                <i class="fas fa-tag" style="font-size:10px;"></i>
                <span id="slug-preview-text">{{ old('slug', $plan->slug ?? '') }}</span>
            </div>
        </div>

        <div class="fi">
            <label>Sort Order</label>
            <input type="number" name="sort_order"
                   value="{{ old('sort_order', $plan->sort_order ?? 0) }}" min="0" max="99">
            <span class="hint">Lower numbers appear first on plan cards (0, 1, 2…).</span>
        </div>

        <div class="fi fi-full">
            <label>Description</label>
            <textarea name="description" rows="2"
                      placeholder="Brief description shown on the upgrade page…">{{ old('description', $plan->description ?? '') }}</textarea>
        </div>
    </div>
</div>

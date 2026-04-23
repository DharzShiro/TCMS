<div class="up-plans">
    @foreach($plans as $plan)
        @php
            $planIndex      = array_search($plan->slug, $planSlugs);
            $isCurrent      = $plan->slug === $currentPlan;
            $isFeatured     = $plan->slug === 'standard';
            $canUpgrade     = !$isCurrent && $planIndex > $currentIndex;
            $basePrice      = (float) $plan->price;

            // Automatic discount for this plan (resolved in controller, no code needed)
            $autoDiscount   = $autoDiscounts[$plan->slug] ?? null;
            $finalPrice     = $autoDiscount ? $autoDiscount->applyTo($basePrice) : $basePrice;
            $savedAmount    = $basePrice - $finalPrice;

            $formattedBase  = number_format($basePrice, 0);
            $formattedFinal = number_format($finalPrice, 0);

            // ── Use icon from DB, fall back to slug-based default ──────────
            $planIcon = $plan->icon ?? match($plan->slug) {
                'basic'    => '🌱',
                'standard' => '🚀',
                'premium'  => '💎',
                default    => '📦',
            };

            $features = [];
            $features[] = [
                'label'  => $plan->max_trainees ? 'Up to ' . number_format($plan->max_trainees) . ' trainees' : 'Unlimited trainees',
                'locked' => false,
            ];
            $features[] = [
                'label'  => $plan->max_courses ? 'Up to ' . number_format($plan->max_courses) . ' courses' : 'Unlimited courses',
                'locked' => false,
            ];
            $features[] = ['label' => 'Course & enrollment management', 'locked' => false];
            $features[] = ['label' => 'Attendance tracking',            'locked' => false];
            $features[] = [
                'label'  => $plan->max_users
                    ? ($plan->max_users === 1 ? '1 admin account' : 'Up to ' . $plan->max_users . ' user accounts')
                    : 'Unlimited user accounts',
                'locked' => false,
            ];
            $features[] = ['label' => 'Trainer management',              'locked' => !$plan->has_trainers];
            $features[] = ['label' => 'Assessments & training schedules','locked' => !$plan->has_assessments];

            $exportFormats = $plan->allowed_export_formats ?? [];
            if (count($exportFormats) === 0) {
                $features[] = ['label' => 'Data exports', 'locked' => true];
            } elseif ($plan->max_exports_monthly) {
                $features[] = ['label' => number_format($plan->max_exports_monthly) . ' exports/mo (' . strtoupper(implode(', ', $exportFormats)) . ')', 'locked' => false];
            } else {
                $features[] = ['label' => 'Unlimited exports (' . strtoupper(implode(', ', $exportFormats)) . ')', 'locked' => false];
            }

            $features[] = ['label' => 'Certifications & competency tracking', 'locked' => !$plan->has_certificates];
            $features[] = ['label' => 'Custom reports & analytics',           'locked' => !$plan->has_custom_reports];
            $features[] = ['label' => 'Custom branding',                      'locked' => !$plan->has_branding];
        @endphp

        <div class="up-card {{ $isFeatured && !$isCurrent ? 'featured' : '' }} {{ $isCurrent ? 'current-plan' : '' }}"
             onclick="{{ $canUpgrade ? "selectPlan('{$plan->slug}', '{$plan->name}', {$basePrice}, {$finalPrice})" : '' }}">

            @if($isCurrent)
                <div class="up-current-badge"><i class="fas fa-check"></i> Current</div>
            @elseif($isFeatured && !$isCurrent)
                <div class="up-popular-badge">⭐ Most Popular</div>
            @endif

            <div class="up-card-inner">
                {{-- ── Icon from DB ── --}}
                <div class="up-plan-icon">{{ $planIcon }}</div>

                <div class="up-duration-badge">
                    <i class="fas fa-clock"></i> {{ $plan->duration_label }} access
                </div>

                <div class="up-plan-name">{{ $plan->name }}</div>

                {{-- Price block --}}
                <div class="up-plan-price">
                    @if($autoDiscount && $canUpgrade)
                        <div style="display:flex;flex-direction:column;align-items:flex-start;gap:2px;">
                            <span class="up-price-original">₱{{ $formattedBase }}</span>
                            <div style="display:flex;align-items:baseline;gap:4px;">
                                <span class="up-price-amount" style="color:{{ $isFeatured ? '#fff' : '#22c55e' }};">₱{{ $formattedFinal }}</span>
                                <span class="up-price-period">/plan</span>
                            </div>
                            <span class="up-auto-discount-badge">
                                🏷 {{ $autoDiscount->formatted_value }} off — {{ $autoDiscount->label }}
                            </span>
                        </div>
                    @else
                        <span class="up-price-amount">₱{{ $formattedBase }}</span>
                        <span class="up-price-period">/plan</span>
                    @endif
                </div>

                @if($plan->description)
                    <div class="up-plan-desc">{{ $plan->description }}</div>
                @endif

                <div class="up-card-divider"></div>

                <ul class="up-features">
                    @foreach($features as $feat)
                        <li class="up-feat-item {{ $feat['locked'] ? 'locked' : '' }}">
                            <div class="up-feat-icon">
                                <i class="fas {{ $feat['locked'] ? 'fa-lock' : 'fa-check' }}"></i>
                            </div>
                            {{ $feat['label'] }}
                        </li>
                    @endforeach
                </ul>

                @if($isCurrent)
                    <button class="up-cta-btn current" disabled>
                        <i class="fas fa-check-circle"></i> Current Plan
                    </button>
                    @if($plan->slug !== 'basic')
                    <button class="up-cta-btn primary"
                            style="margin-top:10px;background:linear-gradient(135deg,#0a7c3e,#16a34a);
                                box-shadow:0 4px 20px rgba(22,163,74,.30);"
                            onclick="event.stopPropagation(); selectRenewal('{{ $plan->slug }}', '{{ $plan->name }}', {{ $basePrice }}, {{ $finalPrice }})">
                        <i class="fas fa-rotate"></i> Renew This Plan
                    </button>
                    @endif
                @elseif($canUpgrade)
                    <button class="up-cta-btn {{ $isFeatured ? 'on-dark' : 'primary' }}"
                            onclick="event.stopPropagation(); selectPlan('{{ $plan->slug }}', '{{ $plan->name }}', {{ $basePrice }}, {{ $finalPrice }})">
                        <i class="fas fa-arrow-up"></i> Upgrade to {{ $plan->name }}
                    </button>
                @else
                    <button class="up-cta-btn {{ $isFeatured ? 'on-dark' : 'primary' }}" disabled style="opacity:0.4;cursor:not-allowed;">
                        <i class="fas fa-arrow-down"></i> Downgrade Not Allowed
                    </button>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="up-guarantee">
    <i class="fas fa-shield-check" style="color:#22c55e;"></i>
    Simulation only — no payment required &nbsp;·&nbsp;
    <i class="fas fa-headset" style="color:#0057B8; margin-left:4px;"></i>
    &nbsp;Contact your system administrator for billing
</div>
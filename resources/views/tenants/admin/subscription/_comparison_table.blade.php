<div class="up-compare">
    <h2 class="up-compare-title">Compare all features</h2>
    <div class="up-table-wrap">
        <table class="up-table">
            <thead>
                <tr>
                    <th style="width:40%">Feature</th>
                    @foreach($plans as $plan)
                        <th class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            {{ $plan->name }}
                            @if($plan->slug === $currentPlan)
                                <div style="font-size:10px;font-weight:600;color:#22c55e;margin-top:2px;">✓ Current</div>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:500;">Price</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}" style="font-weight:700;">
                            ₱{{ number_format($plan->price, 0) }}
                            <div style="font-size:11px;color:#5a7aaa;font-weight:400;">{{ $plan->duration_label }}</div>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Trainees</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            {{ $plan->max_trainees ? number_format($plan->max_trainees) : 'Unlimited' }}
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Courses</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            {{ $plan->max_courses ? number_format($plan->max_courses) : 'Unlimited' }}
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Enrollments & Attendance</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            <i class="fas fa-check up-check"></i>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Trainer Management</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            @if($plan->has_trainers) <i class="fas fa-check up-check"></i>
                            @else <i class="fas fa-times up-cross"></i> @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Assessments</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            @if($plan->has_assessments) <i class="fas fa-check up-check"></i>
                            @else <i class="fas fa-times up-cross"></i> @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">User Accounts</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            {{ $plan->max_users ? ($plan->max_users === 1 ? '1 (Admin)' : 'Up to ' . $plan->max_users) : 'Unlimited' }}
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Monthly Exports</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            @if(($plan->max_exports_monthly ?? -1) === 0 || count($plan->allowed_export_formats ?? []) === 0)
                                <i class="fas fa-times up-cross"></i>
                            @elseif($plan->max_exports_monthly === null)
                                Unlimited
                            @else
                                {{ number_format($plan->max_exports_monthly) }} records
                            @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Export Formats</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}" style="font-size:12px;">
                            @if(count($plan->allowed_export_formats ?? []) === 0)
                                <i class="fas fa-times up-cross"></i>
                            @else
                                {{ strtoupper(implode(', ', $plan->allowed_export_formats)) }}
                            @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Certifications</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            @if($plan->has_certificates) <i class="fas fa-check up-check"></i>
                            @else <i class="fas fa-times up-cross"></i> @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Custom Reports</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            @if($plan->has_custom_reports) <i class="fas fa-check up-check"></i>
                            @else <i class="fas fa-times up-cross"></i> @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td style="font-weight:500;">Custom Branding</td>
                    @foreach($plans as $plan)
                        <td class="{{ $plan->slug === $currentPlan ? 'highlight' : '' }}">
                            @if($plan->has_branding) <i class="fas fa-check up-check"></i>
                            @else <i class="fas fa-times up-cross"></i> @endif
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>
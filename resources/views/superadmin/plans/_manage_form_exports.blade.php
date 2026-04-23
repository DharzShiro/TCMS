{{-- 4. Export Formats --}}
<div class="pm-card">
    <div class="pm-card-title"><i class="fas fa-file-export"></i> Allowed Export Formats</div>

    @php $currentFormats = old('allowed_export_formats', $plan->allowed_export_formats ?? []); @endphp

    <div class="export-group">
        @foreach(['csv' => ['📄','CSV','Spreadsheet-compatible'], 'excel' => ['📊','Excel','.xlsx format'], 'pdf' => ['📑','PDF','Printable reports']] as $fmt => [$fmtIcon, $fmtLabel, $fmtDesc])
            <div class="exp-toggle">
                <input type="checkbox" name="allowed_export_formats[]" value="{{ $fmt }}"
                       id="fmt-{{ $fmt }}" {{ in_array($fmt, $currentFormats) ? 'checked' : '' }}>
                <label for="fmt-{{ $fmt }}">
                    <span>{{ $fmtIcon }}</span> {{ strtoupper($fmt) }}
                    <span style="font-weight:400;font-size:10px;text-transform:none;letter-spacing:0;">— {{ $fmtDesc }}</span>
                </label>
            </div>
        @endforeach
    </div>
    <p class="hint" style="margin-top:10px;">Leave all unchecked = no exports allowed on this plan.</p>
</div>

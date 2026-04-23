<script>
// ── Slug auto-generate from name ──────────────────────────────────────────────
let slugManuallyEdited = {{ isset($plan) ? 'true' : 'false' }};

function autoSlug() {
    if (slugManuallyEdited) return;
    const name = document.getElementById('inp-name').value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    document.getElementById('inp-slug').value = slug;
    updateSlugPreview(slug);
}

function sanitizeSlug(input) {
    slugManuallyEdited = true;
    let val = input.value.toLowerCase()
        .replace(/[^a-z0-9-]/g, '')
        .replace(/-+/g, '-');
    input.value = val;
    updateSlugPreview(val);
}

function updateSlugPreview(slug) {
    const preview = document.getElementById('slug-preview');
    const text    = document.getElementById('slug-preview-text');
    if (slug) {
        text.textContent     = slug;
        preview.style.display = 'inline-flex';
    } else {
        preview.style.display = 'none';
    }
}

// ── Price preview ─────────────────────────────────────────────────────────────
function updatePreview() {
    const price    = parseFloat(document.getElementById('inp-price').value)  || 0;
    const duration = parseInt(document.getElementById('inp-duration').value)  || 30;

    document.getElementById('preview-price').textContent =
        '₱' + price.toLocaleString('en-PH', { minimumFractionDigits: 2 });

    let durLabel = duration + ' days';
    if (duration === 30)  durLabel = '1 month (30 days)';
    if (duration === 90)  durLabel = '3 months (90 days)';
    if (duration === 180) durLabel = '6 months (180 days)';
    if (duration === 365) durLabel = '1 year (365 days)';
    if (duration === 730) durLabel = '2 years (730 days)';
    document.getElementById('preview-dur').textContent = 'for ' + durLabel;

    const ppd = duration > 0 ? (price / duration) : 0;
    document.getElementById('preview-ppd').textContent =
        ppd > 0 ? '≈ ₱' + ppd.toFixed(2) + ' / day' : '';
}

// ── Limit toggles ─────────────────────────────────────────────────────────────
function toggleUnlimited(field, isUnlimited) {
    const inp = document.getElementById('inp-' + field);
    inp.disabled = isUnlimited;
    if (isUnlimited) inp.value = '';
}

// ── Availability ──────────────────────────────────────────────────────────────
function toggleAvailability(always) {
    ['wrap-from', 'wrap-until'].forEach(function(id) {
        document.getElementById(id).style.display = always ? 'none' : '';
    });
    if (always) {
        document.getElementById('inp-from').value  = '';
        document.getElementById('inp-until').value = '';
    }
}

// ── Active switch ─────────────────────────────────────────────────────────────
document.getElementById('sw-active').addEventListener('change', function() {
    document.getElementById('active-label').textContent = this.checked ? 'Active' : 'Inactive';
});

// ── Feature check sync ────────────────────────────────────────────────────────
function syncFeatCheck(cb) {
    const check = document.getElementById('check-' + cb.id.replace('flag-', ''));
    if (!check) return;
    check.style.background  = cb.checked ? 'var(--sa-accent)' : 'var(--sa-bg)';
    check.style.borderColor = cb.checked ? 'var(--sa-accent)' : 'var(--sa-border)';
    check.style.color       = cb.checked ? '#fff' : 'transparent';
}

// ── Init ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
    updateSlugPreview(document.getElementById('inp-slug').value);

    document.querySelectorAll('[id^="flag-"]').forEach(function(cb) {
        syncFeatCheck(cb);
    });

    @php $limitFields = ['max_trainees','max_trainers','max_users','max_courses','max_exports_monthly']; @endphp
    @foreach($limitFields as $f)
        (function() {
            var cb = document.getElementById('ulim-{{ $f }}');
            if (cb) toggleUnlimited('{{ $f }}', cb.checked);
        })();
    @endforeach
});
</script>

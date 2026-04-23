<script>
function switchTab(tab) {
    document.getElementById('tab-plans').style.display    = tab === 'plans'    ? '' : 'none';
    document.getElementById('tab-discounts').style.display = tab === 'discounts' ? '' : 'none';
    document.getElementById('tab-plans-btn').classList.toggle('active',    tab === 'plans');
    document.getElementById('tab-discounts-btn').classList.toggle('active', tab === 'discounts');
    sessionStorage.setItem('planTab', tab);
}

document.addEventListener('DOMContentLoaded', function () {
    const saved = sessionStorage.getItem('planTab');
    if (saved) switchTab(saved);
});

function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function (e) {
        if (e.target === this) closeModal(this.id);
    });
});

function openEditDiscount(id) {
    const d = document.getElementById('disc-data-' + id).dataset;
    document.getElementById('edit-discount-form').action = d.updateUrl;

    const isAuto = d.isAutomatic === '1';
    document.getElementById('ed-radio-automatic').checked = isAuto;
    document.getElementById('ed-radio-code').checked      = !isAuto;
    document.getElementById('ed-radio-automatic').dispatchEvent(new Event('change'));

    document.getElementById('ed-code').value        = d.code;
    document.getElementById('ed-label').value       = d.label;
    document.getElementById('ed-type').value        = d.type;
    document.getElementById('ed-value').value       = d.value;
    document.getElementById('ed-valid-from').value  = d.validFrom;
    document.getElementById('ed-valid-until').value = d.validUntil;
    document.getElementById('ed-active').checked    = d.active === '1';

    let planSlugs = [];
    try { planSlugs = JSON.parse(d.planSlugs || '[]'); } catch(e) {}
    ['basic', 'standard', 'premium'].forEach(slug => {
        const cb = document.getElementById('ed-plan-' + slug);
        if (cb) {
            cb.checked = planSlugs.includes(slug);
            syncPlanRow('ed-', slug,
                { basic: '#5a7aaa', standard: '#0057B8', premium: '#a07800' }[slug],
                { basic: 'rgba(90,122,170', standard: 'rgba(0,87,184', premium: 'rgba(161,122,0' }[slug]
            );
        }
    });

    let tenantIds = [];
    try { tenantIds = JSON.parse(d.tenantIds || '[]'); } catch(e) {}
    const searchEl = document.getElementById('ed-tenant-search');
    if (searchEl) { searchEl.value = ''; filterTenants('ed-'); }
    document.querySelectorAll('#ed-tenant-list input[type="checkbox"]').forEach(cb => {
        cb.checked = tenantIds.includes(cb.value);
        syncTenantRow('ed-', cb.value);
    });

    openModal('modal-edit-discount');
}

@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('_old_input.label') || session('_old_input.code'))
            switchTab('discounts');
        @endif
    });
@endif
</script>

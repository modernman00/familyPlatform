@php
    $selectName = $name ?? 'title';
    $containerId = 'titleContainer_' . uniqid();
    $selectId = $id ?? 'titleSelect_' . uniqid();
    $customInputId = 'customTitle_' . uniqid();
    $currentVal = $selected ?? '';
    $classes = $class ?? 'form-select';
    $label = $label ?? 'Title / Honorific (Optional)';

    // Predefined standard list to determine if currentVal is custom
    $standardOptions = [
        'Mr', 'Mrs', 'Ms', 'Miss', 'Mx', 'Master',
        'Dr.', 'Doctor', 'Prof.', 'Professor', 'Engr.', 'Barrister', 'Architect', 'Advocate',
        'King', 'Queen', 'Prince', 'Princess', 'Lord', 'Lady', 'Sir', 'Dame', 'Duke', 'Duchess', 'Baron', 'Baroness', 'Count', 'Countess', 'Sultan', 'Emir', 'Raja',
        'Father', 'Sister', 'Brother', 'Rev.', 'Reverend', 'Pastor', 'Bishop', 'Rabbi', 'Imam', 'Sheikh', 'Elder', 'Deacon', 'Deaconess', 'Alhaji', 'Alhaja',
        'Chief', 'High Chief', 'Elder', 'Clan Head', 'Chieftain', 'Oba', 'Eze', 'Igwe', 'Otunba', 'Oloye',
        'Hon.', 'Judge', 'Justice', 'Senator', 'Governor', 'Ambassador', 'President'
    ];
    $isCustom = !empty($currentVal) && !in_array($currentVal, $standardOptions, true);
@endphp
<div class="title-select-container" id="{{ $containerId }}">
    @if(!empty($showLabel))
        <label class="form-label small fw-semibold" for="{{ $selectId }}">{{ $label }}</label>
    @endif
    <div class="d-flex gap-1 align-items-center">
        <select name="{{ $selectName }}" id="{{ $selectId }}" class="{{ $classes }}" style="min-height: 40px;" onchange="handleTitleSelectChange(this, '{{ $customInputId }}')">
            <option value="" {{ empty($currentVal) ? 'selected' : '' }}>No Title</option>

            <optgroup label="🌐 General & Everyday">
                <option value="Mr" {{ $currentVal === 'Mr' ? 'selected' : '' }}>Mr</option>
                <option value="Mrs" {{ $currentVal === 'Mrs' ? 'selected' : '' }}>Mrs</option>
                <option value="Ms" {{ $currentVal === 'Ms' ? 'selected' : '' }}>Ms</option>
                <option value="Miss" {{ $currentVal === 'Miss' ? 'selected' : '' }}>Miss</option>
                <option value="Mx" {{ $currentVal === 'Mx' ? 'selected' : '' }}>Mx (Gender Neutral)</option>
                <option value="Master" {{ $currentVal === 'Master' ? 'selected' : '' }}>Master</option>
            </optgroup>

            <optgroup label="🎓 Academic & Professional">
                <option value="Dr." {{ $currentVal === 'Dr.' ? 'selected' : '' }}>Dr. (Doctor)</option>
                <option value="Prof." {{ $currentVal === 'Prof.' ? 'selected' : '' }}>Prof. (Professor)</option>
                <option value="Engr." {{ $currentVal === 'Engr.' ? 'selected' : '' }}>Engr. (Engineer)</option>
                <option value="Barrister" {{ $currentVal === 'Barrister' ? 'selected' : '' }}>Barrister / Counsel</option>
                <option value="Architect" {{ $currentVal === 'Architect' ? 'selected' : '' }}>Architect</option>
            </optgroup>

            <optgroup label="🏰 Royalty & Nobility">
                <option value="King" {{ $currentVal === 'King' ? 'selected' : '' }}>King</option>
                <option value="Queen" {{ $currentVal === 'Queen' ? 'selected' : '' }}>Queen</option>
                <option value="Prince" {{ $currentVal === 'Prince' ? 'selected' : '' }}>Prince</option>
                <option value="Princess" {{ $currentVal === 'Princess' ? 'selected' : '' }}>Princess</option>
                <option value="Lord" {{ $currentVal === 'Lord' ? 'selected' : '' }}>Lord</option>
                <option value="Lady" {{ $currentVal === 'Lady' ? 'selected' : '' }}>Lady</option>
                <option value="Sir" {{ $currentVal === 'Sir' ? 'selected' : '' }}>Sir</option>
                <option value="Dame" {{ $currentVal === 'Dame' ? 'selected' : '' }}>Dame</option>
                <option value="Duke" {{ $currentVal === 'Duke' ? 'selected' : '' }}>Duke</option>
                <option value="Duchess" {{ $currentVal === 'Duchess' ? 'selected' : '' }}>Duchess</option>
                <option value="Baron" {{ $currentVal === 'Baron' ? 'selected' : '' }}>Baron</option>
                <option value="Baroness" {{ $currentVal === 'Baroness' ? 'selected' : '' }}>Baroness</option>
                <option value="Count" {{ $currentVal === 'Count' ? 'selected' : '' }}>Count</option>
                <option value="Countess" {{ $currentVal === 'Countess' ? 'selected' : '' }}>Countess</option>
                <option value="Sultan" {{ $currentVal === 'Sultan' ? 'selected' : '' }}>Sultan</option>
                <option value="Emir" {{ $currentVal === 'Emir' ? 'selected' : '' }}>Emir</option>
                <option value="Raja" {{ $currentVal === 'Raja' ? 'selected' : '' }}>Raja</option>
            </optgroup>

            <optgroup label="🕊️ Spiritual & Religious">
                <option value="Father" {{ $currentVal === 'Father' ? 'selected' : '' }}>Father</option>
                <option value="Sister" {{ $currentVal === 'Sister' ? 'selected' : '' }}>Sister</option>
                <option value="Brother" {{ $currentVal === 'Brother' ? 'selected' : '' }}>Brother</option>
                <option value="Rev." {{ $currentVal === 'Rev.' ? 'selected' : '' }}>Rev. (Reverend)</option>
                <option value="Pastor" {{ $currentVal === 'Pastor' ? 'selected' : '' }}>Pastor</option>
                <option value="Bishop" {{ $currentVal === 'Bishop' ? 'selected' : '' }}>Bishop</option>
                <option value="Rabbi" {{ $currentVal === 'Rabbi' ? 'selected' : '' }}>Rabbi</option>
                <option value="Imam" {{ $currentVal === 'Imam' ? 'selected' : '' }}>Imam</option>
                <option value="Sheikh" {{ $currentVal === 'Sheikh' ? 'selected' : '' }}>Sheikh</option>
                <option value="Elder" {{ $currentVal === 'Elder' ? 'selected' : '' }}>Elder</option>
                <option value="Deacon" {{ $currentVal === 'Deacon' ? 'selected' : '' }}>Deacon</option>
                <option value="Deaconess" {{ $currentVal === 'Deaconess' ? 'selected' : '' }}>Deaconess</option>
                <option value="Alhaji" {{ $currentVal === 'Alhaji' ? 'selected' : '' }}>Alhaji</option>
                <option value="Alhaja" {{ $currentVal === 'Alhaja' ? 'selected' : '' }}>Alhaja</option>
            </optgroup>

            <optgroup label="🌿 Traditional, Cultural & Clan">
                <option value="Chief" {{ $currentVal === 'Chief' ? 'selected' : '' }}>Chief</option>
                <option value="High Chief" {{ $currentVal === 'High Chief' ? 'selected' : '' }}>High Chief</option>
                <option value="Clan Head" {{ $currentVal === 'Clan Head' ? 'selected' : '' }}>Clan Head</option>
                <option value="Chieftain" {{ $currentVal === 'Chieftain' ? 'selected' : '' }}>Chieftain</option>
                <option value="Oba" {{ $currentVal === 'Oba' ? 'selected' : '' }}>Oba</option>
                <option value="Eze" {{ $currentVal === 'Eze' ? 'selected' : '' }}>Eze</option>
                <option value="Igwe" {{ $currentVal === 'Igwe' ? 'selected' : '' }}>Igwe</option>
                <option value="Otunba" {{ $currentVal === 'Otunba' ? 'selected' : '' }}>Otunba</option>
                <option value="Oloye" {{ $currentVal === 'Oloye' ? 'selected' : '' }}>Oloye</option>
            </optgroup>

            <optgroup label="🏛️ Civil, State & Diplomatic">
                <option value="Hon." {{ $currentVal === 'Hon.' ? 'selected' : '' }}>Hon. (Honourable)</option>
                <option value="Judge" {{ $currentVal === 'Judge' ? 'selected' : '' }}>Judge</option>
                <option value="Justice" {{ $currentVal === 'Justice' ? 'selected' : '' }}>Justice</option>
                <option value="Senator" {{ $currentVal === 'Senator' ? 'selected' : '' }}>Senator</option>
                <option value="Governor" {{ $currentVal === 'Governor' ? 'selected' : '' }}>Governor</option>
                <option value="Ambassador" {{ $currentVal === 'Ambassador' ? 'selected' : '' }}>Ambassador</option>
                <option value="President" {{ $currentVal === 'President' ? 'selected' : '' }}>President</option>
            </optgroup>

            <option value="__custom__" {{ $isCustom ? 'selected' : '' }}>✍️ Other / Custom Title...</option>
        </select>
        <input type="text" id="{{ $customInputId }}" class="form-control {{ $isCustom ? '' : 'd-none' }}" placeholder="e.g. Monsieur, Sensei, Laird, etc." value="{{ $isCustom ? htmlspecialchars($currentVal) : '' }}" style="min-height: 40px; max-width: 160px;" oninput="handleCustomTitleInput(this, '{{ $selectId }}')">
    </div>
</div>
<script>
    if (typeof window.handleTitleSelectChange === 'undefined') {
        window.handleTitleSelectChange = function(selectEl, customInputId) {
            const customInput = document.getElementById(customInputId);
            if (!customInput) return;
            if (selectEl.value === '__custom__') {
                customInput.classList.remove('d-none');
                customInput.focus();
            } else {
                customInput.classList.add('d-none');
                customInput.value = '';
            }
        };
    }
    if (typeof window.handleCustomTitleInput === 'undefined') {
        window.handleCustomTitleInput = function(inputEl, selectId) {
            const selectEl = document.getElementById(selectId);
            if (!selectEl) return;
            // Let the form submit the custom title value
            if (inputEl.value.trim() !== '') {
                // Ensure a custom option with the typed value exists
                let opt = selectEl.querySelector('option[data-is-user-custom="true"]');
                if (!opt) {
                    opt = document.createElement('option');
                    opt.setAttribute('data-is-user-custom', 'true');
                    selectEl.appendChild(opt);
                }
                opt.value = inputEl.value.trim();
                opt.textContent = inputEl.value.trim();
                selectEl.value = opt.value;
            }
        };
    }
</script>

@php
    // 1) Decide the display name safely across all your node types
    $displayName =
        $dataDB['fullName']
            ?? $dataDB['name']
            ?? trim(($dataDB['firstName'] ?? '') . ' ' . ($dataDB['lastName'] ?? ''))
            ?: 'Name not provided';

    // 2) Gender class/icon safety
    $gender = $dataDB['gender'] ?? 'neutral';
    $genderClass = in_array($gender, ['Male', 'Female'], true) ? $gender : 'neutral';

    // 3) Image safety (fallback avatar)
    $img = $dataDB['img'] ?? null;
    if (empty($img)) {
        $img = ($gender === 'Male')
            ? '/resources/images/profile/avatarM.png'
            : '/resources/images/profile/avatarF.png';
    }

    // 4) Relationship label safety
    $relationshipLabel = $dataDB['relationship'] ?? ucfirst($type);
@endphp

<a class="tree-node {{ $genderClass }}" data-role="{{ $type }}">
    {{-- Data for modal (safe fallbacks everywhere) --}}
    <span class="node-email" data-id="{{ $dataDB['email'] ?? '' }}"></span>
    <span class="node-id" data-id="{{ $dataDB['id'] ?? '' }}"></span>
    <span class="node-img" data-id="{{ $img }}"></span>
    <span class="node-fullName" data-id="{{ $displayName }}"></span>
    <span class="node-maritalStatus" data-id="{{ $dataDB['maritalStatus'] ?? $dataDB['marital_status'] ?? '' }}"></span>
    <span class="node-occupation" data-id="{{ $dataDB['occupation'] ?? '' }}"></span>
    <span class="node-country" data-id="{{ $dataDB['country'] ?? '' }}"></span>
    <span class="node-spouseName" data-id="{{ $dataDB['spouse_name'] ?? '' }}"></span>

    <div class="profile-image-container">
        <img src="{{ $img }}" alt="{{ $displayName }}" class="profile-image">
    </div>

    <span class="node-title">{{ ucfirst($type) }}</span>
    <span class="node-name">{{ $displayName }}</span>

    <span class="node-relation" data-id="{{ $relationshipLabel }}">
        {{ $relationshipLabel }}
    </span>

    <span class="node-icon">
        {{-- Bootstrap icons expect: male, female, neutral --}}
        <i class="bi bi-gender-{{ strtolower($genderClass) }}"></i>
    </span>
</a>

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
    $isDeceased = !empty($dataDB['is_deceased']);
@endphp

<a class="tree-node {{ $genderClass }} {{ $isDeceased ? 'deceased' : '' }}" 
   data-role="{{ $type }}" 
   data-registered="{{ empty($dataDB['email']) && empty($dataDB['id']) ? 'false' : 'true' }}" 
   data-familycode="{{ $data['famCode'] ?? ($data['familyCode'] ?? '') }}"
   data-personid="{{ $dataDB['id'] ?? '' }}"
   data-deceased="{{ $isDeceased ? '1' : '0' }}">
    
    {{-- Data for modal & slide-out dossier --}}
    <span class="node-email" data-id="{{ $dataDB['email'] ?? '' }}"></span>
    <span class="node-id" data-id="{{ $dataDB['id'] ?? '' }}"></span>
    <span class="node-img" data-id="{{ $img }}"></span>
    <span class="node-fullName" data-id="{{ $displayName }}"></span>
    <span class="node-maritalStatus" data-id="{{ $dataDB['maritalStatus'] ?? $dataDB['marital_status'] ?? '' }}"></span>
    <span class="node-occupation" data-id="{{ $dataDB['occupation'] ?? '' }}"></span>
    <span class="node-country" data-id="{{ $dataDB['country'] ?? ($dataDB['location'] ?? '') }}"></span>
    <span class="node-spouseName" data-id="{{ $dataDB['spouse_name'] ?? '' }}"></span>
    <span class="node-bio" data-id="{{ $dataDB['bio'] ?? '' }}"></span>

    <span class="node-icon">
        @if($isDeceased)
            <i class="bi bi-flower1" title="Deceased Ancestor"></i>
        @else
            <i class="bi bi-gender-{{ strtolower($genderClass) }}"></i>
        @endif
    </span>

    <div class="profile-image-container">
        <img src="{{ $img }}" alt="{{ $displayName }}" class="profile-image" loading="lazy">
    </div>

    <div class="node-content">
        <span class="node-title">{{ $relationshipLabel }}</span>
        <span class="node-name">{{ $displayName }}</span>
        
        @if(!empty($dataDB['occupation']))
            <span class="node-relation">{{ $dataDB['occupation'] }}</span>
        @else
            <span class="node-relation">{{ ucfirst($gender) }}</span>
        @endif
    </div>

    <span class="node-relation-hidden" data-id="{{ $relationshipLabel }}"></span>
</a>

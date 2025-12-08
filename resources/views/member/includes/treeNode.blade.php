
<a class="tree-node {{ $dataDB['gender'] }}" data-role="{{ $type }}">


    <span class="node-email" data-id="{{ $dataDB['email'] }}" ></span>
      <span class="node-id" data-id="{{ $dataDB['id'] ?? '' }}"></span>
      <span class="node-img" data-id="{{ $dataDB['img'] ?? '' }}"></span>
    <span class="node-fullName" data-id="{{ $dataDB['fullName'] ?? '' }}"></span>
    <span class="node-maritalStatus" data-id="{{ $dataDB['maritalStatus'] ?? '' }}"></span>
    <span class="node-occupation" data-id="{{ $dataDB['occupation'] ?? '' }}"></span>
    <span class="node-country" data-id="{{ $dataDB['country'] ?? '' }}"></span>
    <span class="node-spouseName" data-id="{{ $dataDB['spouse_name'] ?? '' }}"></span>
    
    
           <div class="profile-image-container">
                <img src="{{ $dataDB['img']}}" alt="{{$dataDB['fullName']}}" class="profile-image">
            </div>
    <span class="node-title">{{ ucfirst($type) }}</span>
    <span class="node-name">{{ $dataDB['fullName'] ?? 'Name not provided' }}</span>
    <span class="node-relation" data-id="{{ $dataDB['relationship'] ?? '' }}">{{ $dataDB['relationship'] ?? ucfirst($type) ?? 'Unspecified' }}</span>
    <span class="node-icon">
        <i class="bi bi-gender-{{ strtolower($dataDB['gender'] ?? 'neutral') }}"></i>
    </span>
</a>


  

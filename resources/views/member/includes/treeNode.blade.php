
<a class="tree-node {{ $data['gender'] }}" data-role="{{ $type }}">


    <span class="node-email" data-email="{{ $data['email'] }}" data-id="{{ $data['id'] ?? '' }}"></span>
      <span class="node-id" data-id="{{ $data['id'] ?? '' }}"></span>
      <span class="node-img" data-id="{{ $data['img'] ?? '' }}"></span>
    <span class="node-fullName" data-id="{{ $data['fullName'] ?? '' }}"></span>
    <span class="node-maritalStatus" data-id="{{ $data['maritalStatus'] ?? '' }}"></span>
    <span class="node-occupation" data-id="{{ $data['occupation'] ?? '' }}"></span>
    <span class="node-country" data-id="{{ $data['country'] ?? '' }}"></span>
    <span class="node-spouseName" data-id="{{ $data['spouse_name'] ?? '' }}"></span>
    
      
      





           <div class="profile-image-container">
                <img src="{{ $data['img']}}" alt="{{$data['name']}}" class="profile-image">
            </div>
    <span class="node-title">{{ ucfirst($type) }}</span>
    <span class="node-name">{{ $data['name'] ?? 'Unnamed' }}</span>
    <span class="node-relation">{{ $data['relationship'] ?? ucfirst($type) ?? 'Unspecified' }}</span>
    <span class="node-icon">
        <i class="bi bi-gender-{{ strtolower($data['gender'] ?? 'neutral') }}"></i>
    </span>
</a>


  

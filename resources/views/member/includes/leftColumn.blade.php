        <div class="profile-column">

            <div class="card profile-card text-center">
                <div class="card-header position-relative">
                    <div class="cover-photo"
                        style="height: 100px; background-color: #0a526f; border-radius: 8px 8px 0 0;"></div>
                    <img src="/public/img/profile/{{ $data['img'] }}" alt="Avatar" class="avatar profileImg">
                </div>

                <div class="card-body">

                    <h5 class="card-title"> {{ $data['firstName'] }}
                        {{ $data['lastName'] }} </h5>

                    <p class="card-text">{{ $data['country'] }}</p>
                    <hr>

                    <div class="d-flex justify-content-around">

                        @isset($data['spouseName'])

                            <div>
                                <h6 class="mb-0">{{ $data['spouseName'] }}</h6>
                                <small>Spouse</small>
                            </div>
                            
                        @endisset


                        <div>
                            <h6 class="mb-0">{{ $data['famCode'] }}</h6>
                            <small><b>Family code</b></small>
                        </div>

                        {{-- <div>
                            <h6 class="mb-0">1.2K</h6>
                            <small>Posts</small>
                        </div> --}}
                    </div>
                    <hr>

                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile
                    </button>

                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#createEventModal">
                        <i class="bi bi-plus-circle"></i> Create Event
                    </button>

                    {{-- <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">Open</button> --}}
                </div>

            </div>

            <!-- Friend Requests Card (replaced Trending Topics) -->
            <div class="card">
                
                <div class="card-header d-flex justify-content-between align-items-center">
                    @if (count($requestData) > 1)
                        <h6 class="mb-0"> {{ count($requestData) }} Friend Requests</h6>
                        <a href="#" class="btn btn-sm btn-outline-primary">See All</a>
                    @else
                        <p><b>Friend Request</b></p><br>
                    @endif
                </div>

                <div class="card-body requestFriendClass">

                </div>
            </div>
        </div>


        <script>
            const profile = {
                requesterFirstName: @json($data['firstName']),
                requesterLastName: @json($data['lastName']),
                requesterId: @json($data['id']),
                requesterEmail: @json($data['email']),
                requesterProfileImg: @json($data['img']),
                requesterFamCode: @json($data['famCode'])

            };
            localStorage.setItem('requesterFamCode', @json($data['famCode']));
            localStorage.setItem('requesterId', @json($data['id']));

            // Convert the object to a JSON string and store it in localStorage
            localStorage.setItem('profile', JSON.stringify(profile));
        </script>

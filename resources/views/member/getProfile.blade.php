@extends ('layouts.profileBase')
@section('title', 'Profile')
@section('data-page-id', 'getProfilePage')
{{-- custom css --}}
@push('styles')
    <link rel="stylesheet" href="/public/css/seeProfile.css">
@endpush


@section('data-page-id', 'seeProfile')
@section('content')

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-info">
                <img src="/resources/images/profile/{{ $data['profilePics'] }}" alt="{{ $data['fullName'] }}" class="profile-avatar">
                <div class="profile-details">
                    <h1>{{ $data['fullName'] }}</h1>
                    <div class="family-tree-name"> 
                      
                    </div>
                    <div class="profile-location">
                        <i class="bi bi-geo-alt-fill me-2"></i> Lives in {{ $data['country'] }}
                    </div>
                    <span class="badge badge-custom">{{ $data['famCode'] }}</span>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Basic Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-info-circle-fill me-2"></i>Basic Information</h3>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="{{ $data['gender'] == 'Female' ? 'bi bi-gender-female' : 'bi bi-gender-male' }}"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value">{{$data['gender']}}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-calendar-event"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Date of Birth</div>
                            <div class="detail-value">{{ $data['day'] }} / {{ $data['month'] }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-heart"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Relationship Status</div>
                            <div class="detail-value">{{$data['maritalStatus']}}</div>
                        </div>
                    </div>
                </div>

                <!-- Family Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-people-fill me-2"></i>Family Information</h3>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-person"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Father</div>
                            <div class="detail-value">{{ $data['father']['fullName'] }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-person"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Mother</div>
                            <div class="detail-value">{{ $data['mother']['fullName'] }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-person-heart"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Spouse/Partner</div>
                            <div class="detail-value">{{ $data['spouse']['fullName'] }}</div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-telephone-fill me-2"></i>Contact Information</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="bi bi-phone"></i> {{ $data['mobile'] }}
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-envelope"></i> {{ $data['email'] }}
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-house"></i> {{ $data['country'] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Immediate Family Card -->
                <div class="info-card">
                    <h3><i class="bi bi-diagram-3-fill me-2"></i>Immediate Family</h3>
                    <div class="family-members">
                        @foreach ($relativesWithImgs as $relative)
                        <a href="{{ route('allMembers/seeProfile/'.$relative['id']) }}">    
                            <div class="family-member">
                                <img src="{{ $relative['img'] }}" alt="Father" class="member-avatar">
                                <div class="member-name">{{ $relative['fullName'] }}</div>
                                <div class="member-relation">{{ $relative['relationship'] }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Life Events Card -->
                {{-- <div class="info-card">
                    <h3><i class="bi bi-clock-history me-2"></i>Life Events</h3>
                    <div class="timeline">
                        <div class="timeline-event">
                            <div class="event-date">1998</div>
                            <div class="event-desc">Graduated from University of Lagos</div>
                        </div>
                        <div class="timeline-event">
                            <div class="event-date">2002</div>
                            <div class="event-desc">Relocated to the United Kingdom</div>
                        </div>
                        <div class="timeline-event">
                            <div class="event-date">2005</div>
                            <div class="event-desc">Married</div>
                        </div>
                        <div class="timeline-event">
                            <div class="event-date">2010</div>
                            <div class="event-desc">Started OLAOGUN Family Business</div>
                        </div>
                    </div>
                </div> --}}

                <!-- Additional Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-plus-circle-fill me-2"></i>Additional Information</h3>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-briefcase"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Occupation</div>
                            <div class="detail-value">{{$data['occupation']}}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-book"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Education</div>
                            <div class="detail-value">{{$data['education']}}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-star"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Interests</div>
                            <div class="detail-value">{{$data['interests']}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        {{-- <div class="action-buttons">
            <button class="btn-primary-custom"><i class="bi bi-pencil-fill me-2"></i>Edit Profile</button>
            <button class="btn-outline-custom"><i class="bi bi-printer me-2"></i>Family Tree</button>
            <button class="btn-outline-custom"><i class="bi bi-share me-2"></i>Share Profile</button>
        </div> --}}
    </div>

  @endsection
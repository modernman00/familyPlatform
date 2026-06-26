        <!-- Right Column - Sidebar -->
        <div class="sidebar-column">
            <!-- Events -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background-color: var(--card-bg);">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4" id="eventHeader">
                    <h5 class="mb-0 fw-bold" style="font-family: 'Playfair Display', serif; color: var(--text-color);">Upcoming Events</h5>
                    <a href="#" class="text-decoration-none" style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">See All <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i></a>
                </div>

                <div class="card-body eventList px-4" id="eventList">

                @foreach($eventData as $event)

                    @php
                    $dateDiff = dateDifferenceInt(date('Y-m-d'), $event['eventDate']);
                    $getDateDiff = number2word($dateDiff);

                    if($getDateDiff == 'Zero') {
                    $dateDifference = 'Today';
                    } else if($getDateDiff == 'One') {
                    $dateDifference = 'Tomorrow';
                    } else{
                    $dateDifference = "in $getDateDiff Days";
                    }
                    @endphp

                    <div class="event-card mb-4" id="linkNotification{{ $event['no'] }}">
                        <div class="p-3" style="background-color: var(--hover-color); border-radius: 12px; border: 1px solid var(--border-color);">
                            <div class="d-flex flex-column">
                                <h6 class="fw-bold mb-1" style="color: var(--text-color);">{{ $event['eventName'] }}</h6>
                                
                                <div class="d-flex align-items-center mb-1" style="color: var(--text-muted); font-size: 0.85rem;">
                                    <i class="bi bi-calendar3 me-2"></i> {{ dateFormat($event['eventDate']) }}
                                </div>
                                
                                <div class="d-flex align-items-center mb-2" style="color: var(--text-muted); font-size: 0.85rem;">
                                    <i class="bi bi-geo-alt me-2"></i> {{ $event['eventType'] }}
                                </div>

                                <div class="d-flex gap-2 mt-2">
                                    <span class="badge" style="background-color: var(--secondary-color); color: var(--text-color); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">Or</span>
                                    <span class="badge" style="background-color: var(--secondary-color); color: var(--text-color); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">Si</span>
                                    <span class="badge" style="background-color: var(--secondary-color); color: var(--text-color); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">Ba</span>
                                    <span style="color: var(--text-muted); font-size: 0.8rem; margin-top: 2px;">+1</span>
                                </div>

                                <input type='hidden' name='event_no' id='event{{ $event['no'] }}' value='{{ $event['no'] }}'>
                            </div>
                        </div>
                    </div>

                @endforeach
                </div>
            </div>

            <!-- Family Members Metric Card -->
            <div class="card border-0 text-white text-center shadow-sm" style="background-color: var(--primary-color); border-radius: 16px; padding: 30px 20px;">
                <h1 class="display-4 fw-bold mb-0 text-white">47</h1>
                <p class="mb-3" style="color: rgba(255, 255, 255, 0.8); font-size: 1.1rem;">Family Members</p>
                <button class="btn btn-light rounded-pill fw-semibold mx-auto" style="color: var(--primary-color); padding: 8px 24px; background-color: rgba(255,255,255,0.9); border: none;" onclick="window.location.href='/allMembers'">
                    View Tree
                </button>
            </div>

            <!-- Friends -->
            {{-- <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Friend Suggestions</h6>
                    <a href="#" class="btn btn-sm btn-outline-primary">See All</a>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://randomuser.me/api/portraits/women/6.jpg" alt="Profile" class="rounded-circle me-3" width="40" height="40">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Emily Rodriguez</h6>
                            <small class="text-muted">12 mutual friends</small>
                        </div>
                        <button class="btn btn-sm btn-primary">Add Friend</button>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://randomuser.me/api/portraits/men/7.jpg" alt="Profile" class="rounded-circle me-3" width="40" height="40">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">David Kim</h6>
                            <small class="text-muted">5 mutual friends</small>
                        </div>
                        <button class="btn btn-sm btn-primary">Add Friend</button>
                    </div>
                    <div class="d-flex align-items-center">
                        <img src="https://randomuser.me/api/portraits/women/8.jpg" alt="Profile" class="rounded-circle me-3" width="40" height="40">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Michelle Lee</h6>
                            <small class="text-muted">9 mutual friends</small>
                        </div>
                        <button class="btn btn-sm btn-primary">Add Friend</button>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>

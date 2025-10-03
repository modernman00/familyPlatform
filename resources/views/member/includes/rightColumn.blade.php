        <!-- Right Column - Sidebar -->
        <div class="sidebar-column">
            <!-- Events -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center" id="eventHeader">
                    <h6 class="mb-0">Upcoming Events</h6>
                    <a href="#" class="btn btn-sm btn-outline-primary">See All</a>
                </div>

                
                <div class="card-body eventList" id="eventList">

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

                    <div class="event-card card mb-3" id="linkNotification{{ $event['no'] }}">
                        <div class="card-body">
                            <div class="d-flex">
                           
                                <div class="flex-grow-1">

                                    <small class="eventInfo"><strong>RSVP:</strong> {{ $event['firstName'] }} {{ $event['lastName'] }}  </small><br>

                                      <small class="eventInfo"><strong>Event: </strong>{{ $event['eventName'] }}</small><br>

                                     <small class="eventInfo"><strong>Type: </strong>{{ $event['eventType'] }}</small><br>

                                    <small class="eventInfo"><strong>Description: </strong> {{ $event['eventDescription'] }}</small><br>

                                    <small class="eventInfo"> <strong>Date:</strong> {{ dateFormat($event['eventDate']) }} </small>
                                 
                                    <small style="color: rgba(11, 11, 201, 0.631)"> {{ $dateDifference }}</small>

                                     <input type='hidden' name='event_no' id='event{{ $event['no'] }}' value='{{ $event['no'] }}'>

                                    <div class="mt-2 rsvp-buttons d-flex">
                                        <button class="btn btn-sm btn-outline-primary">Going</button>
                                        <button class="btn btn-sm btn-outline-secondary">Maybe</button>
                                        <button class="btn btn-sm btn-outline-danger">Decline</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endforeach

                
                </div>
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

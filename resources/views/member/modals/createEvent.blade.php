   <!-- Create Event Modal -->
   @php
       $token = urlencode(base64_encode(random_bytes(32)));
       $_SESSION['token'] = $token;
           setcookie('XSRF-TOKEN', $token, [
            'expires' => time() + 900,
            'path' => '/',
            'samesite' => 'Lax',
            'secure' => ($_ENV['APP_ENV'] ?? 'production') === 'production',
            'httponly' => false,
        ]);
        $_SESSION['CREATE_EVENT_ID'] = $data['id'];
   @endphp



   <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="createEventModalLabel">Create Event</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <form id="eventModalForm" class="eventModalForm">




                       <div id="loader"></div>

                       <div id="eventModalForm_notification">
                           <p id="error"></p>
                       </div>
                       <div class="mb-3">
                           <label for="eventName" class="form-label">Event Name</label>
                           <input type="text" class="form-control" id="eventName" name="eventName"
                               placeholder="What's the event name?">
                           <small id="eventName_error"></small>
                       </div>

                       <div class="row mb-3">
                           <div class="col-md-6">
                               <label for="eventDate" class="form-label">Date</label>
                               <input type="date" class="form-control" name="eventDate" id="eventDate">
                               <small id="eventDate_error"></small>
                           </div>
                           {{-- <div class="col-md-6">
                               <label for="eventTime" class="form-label">Time</label>
                               <input type="time" class="form-control" id="eventTime" name="eventTime">
                               <small id="eventTime_error"></small>
                           </div> --}}
                       </div>
                       {{-- <div class="mb-3">
                           <label for="eventLocation" class="form-label">Location</label>
                           <input type="text" class="form-control" id="eventLocation" name="eventLocation"
                               placeholder="Where is the event?">
                           <small id="eventLocation_error"></small>
                       </div> --}}
                       <div class="mb-3">
                           <label for="eventDescription" class="form-label">event Type</label>
                           <select name="eventType" id="eventType" class="form-select">
                               <option value="" disabled selected>Choose event frequency</option>
                               <option value="Birthday">Birthday</option>
                               <option value="Anniversary">Anniversary</option>
                               <option value="Reunion">Reunion</option>
                                 <option value="House_warming">House Warming</option>
                               <option value="Wedding">Wedding</option>
                               <option value="Party">Party</option>
                               <option value="Meeting">Meeting</option>
                               <option value="Memorial">Memorial</option>
                               <option value="Graduation">Graduation</option>
                               <option value="Holiday">Holiday</option>
                               <option value="Other">Other</option>
                               <option value="Other">Other</option>
                           </select>
                           <small id="eventType_error"></small>

                       </div>
                       <div class="mb-3">
                           <label for="eventDescription" class="form-label">Description</label>
                           <textarea class="form-control" id="eventDescription" rows="3" placeholder="Describe your event..."></textarea>
                           <small id="eventDescription_error"></small>
                       </div>

                       <div class="mb-3">
                           <label for="eventFrequency" class="form-label">Frequency</label>
                           <select class="form-select" name="eventFrequency" id="eventFrequency">
                               <option value="" disabled selected>Choose event frequency</option>
                               <option value="One-off">One-off</option>
                               <option value="Annually">Annually</option>
                               <option value="Monthly">Monthly</option>
                               <option value="Weekly">Weekly</option>

                           </select>
                           <small id="eventFrequency_error"></small>
                       </div>

                       <input type="hidden" name='token' value={{ $token }}>

                       <div class="g-recaptcha" data-sitekey="{{ $_ENV['RECAPTCHA_KEY'] }}" data-theme="dark"></div>
                   </form>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                   <button type="button" name="submit" class="btn btn-primary" id="submitEventModal">Create
                       Event</button>
               </div>
           </div>
       </div>
   </div>

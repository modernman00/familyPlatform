   <!-- Create Event Modal -->
   @php
       $token = urlencode(base64_encode(random_bytes(32)));
       $_SESSION['token'] = $token;
   @endphp



   <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="createEventModalLabel">Create Event</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <form id="eventForm" class="eventModalForm">
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
                               <p id="eventDate_error"></p>
                           </div>
                           <div class="col-md-6">
                               <label for="eventTime" class="form-label">Time</label>
                               <input type="time" class="form-control" id="eventTime" name="eventTime">
                               <p id="eventTime_error"></p>
                           </div>
                       </div>
                       <div class="mb-3">
                           <label for="eventLocation" class="form-label">Location</label>
                           <input type="text" class="form-control" id="eventLocation" name="eventLocation"
                               placeholder="Where is the event?">
                           <p id="eventLocation_error"></p>
                       </div>
                       <div class="mb-3">
                           <label for="eventDescription" class="form-label">Description</label>
                           <textarea class="form-control" id="eventDescription" rows="3" placeholder="Describe your event..."></textarea>
                           <p id="eventDescription_error"></p>
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
                           <p id="eventFrequency_error"></p>
                       </div>

                       <input type="hidden" name='token' value={{ $token }}>
                   </form>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                   <button type="button" name="submit" class="btn btn-primary" id="createEventBtn">Create Event</button>
               </div>
           </div>
       </div>
   </div>

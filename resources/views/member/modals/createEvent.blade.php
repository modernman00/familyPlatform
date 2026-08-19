   <!-- Create Event Modal -->
   @php
       $token = $_SESSION['token'] ?? '';
       $_SESSION['CREATE_EVENT_ID'] = $data['id'];
   @endphp



   <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content glass-modal glass-overlay shadow-lg border-0" style="border-radius: 20px; background-color: var(--card-bg);">
               
               <div class="modal-header border-0 pb-0 px-4 pt-4">
                   <h4 class="modal-title fw-bold" id="createEventModalLabel" style="font-family: 'Playfair Display', serif; color: var(--text-color);">
                       Create Event
                   </h4>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--close-btn-filter);"></button>
               </div>

               <div class="modal-body px-4 pt-4">
                   {{-- Deliberately OUTSIDE the form: FormHelper's massValidate() iterates
                        every named control inside #eventModalForm and treats an empty one
                        as a validation error, which a hidden "not editing" field would be. --}}
                   <input type="hidden" id="editEventNo" value="">
                   <small id="editEventNotice" class="d-none text-muted d-block mb-2 px-1">Editing this event.</small>
                   <form id="eventModalForm" class="eventModalForm">

                       <div id="loader" class="text-center mb-3"></div>
                       <div id="eventModalForm_notification">
                           <p id="error" class="text-danger small"></p>
                       </div>

                       <div class="mb-4">
                           <label for="eventName" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Event Name</label>
                           <input type="text" class="form-control rounded-pill px-3" id="eventName" name="eventName"
                               placeholder="What's the event name?" style="background-color: var(--bg-color);">
                           <small id="eventName_error" class="text-danger ps-2"></small>
                       </div>

                       <div class="row mb-4">
                           <div class="col-md-12">
                               <label for="eventDate" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Event Date</label>
                               <input type="date" class="form-control rounded-pill px-3" name="eventDate" id="eventDate" style="background-color: var(--bg-color);">
                               <small id="eventDate_error" class="text-danger ps-2"></small>
                           </div>
                       </div>

                       <div class="mb-4">
                           <label for="eventType" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Event Type</label>
                           <select name="eventType" id="eventType" class="form-select rounded-pill px-3" style="background-color: var(--bg-color);">
                               <option value="" disabled selected>Choose event type</option>
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
                           </select>
                           <small id="eventType_error" class="text-danger ps-2"></small>
                       </div>

                       <div class="mb-4">
                           <label for="eventDescription" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Description</label>
                           <textarea class="form-control px-3" id="eventDescription" name="eventDescription" rows="3" 
                               placeholder="Describe your event..." style="border-radius: 15px; background-color: var(--bg-color);"></textarea>
                           <small id="eventDescription_error" class="text-danger ps-2"></small>
                       </div>

                       <div class="mb-4">
                           <label for="eventFrequency" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Frequency</label>
                           <select class="form-select rounded-pill px-3" name="eventFrequency" id="eventFrequency" style="background-color: var(--bg-color);">
                               <option value="" disabled selected>Choose event frequency</option>
                               <option value="One-off">One-off</option>
                               <option value="Annually">Annually</option>
                               <option value="Monthly">Monthly</option>
                               <option value="Weekly">Weekly</option>
                           </select>
                           <small id="eventFrequency_error" class="text-danger ps-2"></small>
                       </div>

                       <input type="hidden" name="token" value="{{ $token }}">
                   </form>
               </div>

               <div class="modal-footer border-0 px-4 pb-4">
                   <button type="button" class="btn fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 20px; color: var(--text-muted);">Cancel</button>
                   <button type="button" name="submit" class="btn text-white fw-semibold px-5" id="submitEventModal" 
                       style="background-color: var(--primary-color); border-radius: 20px;">
                       Create Event
                   </button>
               </div>
           </div>
       </div>
   </div>

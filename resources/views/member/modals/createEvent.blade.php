@php
    $token = $_SESSION['token'] ?? '';
    $_SESSION['CREATE_EVENT_ID'] = $data['id'];
@endphp

<!-- Create Event Modal Style overrides -->
<style>
    #createEventModal .modal-content {
        border-radius: 24px;
        background-color: #121212;
        border: none;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
    }
    #createEventModal .modal-header {
        border-bottom: none;
        padding: 1.5rem 1.5rem 0.5rem;
        background-color: transparent;
    }
    #createEventModal .modal-title {
        font-family: var(--font-family), sans-serif;
        font-weight: 600;
        color: #ffffff;
        font-size: 1.5rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #createEventModal .modal-title .back-btn {
        color: #ffffff;
        cursor: pointer;
        text-decoration: none;
        background: none;
        border: none;
        padding: 0;
        display: inline-flex;
        align-items: center;
        font-size: 1.5rem;
    }
    #createEventModal .modal-body {
        padding: 1.5rem;
        background-color: transparent;
    }
    #createEventModal .form-container-box {
        background-color: #1a1a1a;
        border: 1px solid #333;
        border-radius: 24px;
        padding: 1.5rem;
    }
    #createEventModal .form-label {
        color: #a1a1aa;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    #createEventModal .form-control, 
    #createEventModal .form-select {
        background-color: #27272a;
        border: 1px solid transparent;
        color: #ffffff;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.25s;
    }
    #createEventModal .form-control:focus, 
    #createEventModal .form-select:focus {
        border-color: #4ade80;
        box-shadow: none;
        background-color: #27272a;
        color: #ffffff;
        outline: none;
    }
    #createEventModal .form-control::placeholder {
        color: #a1a1aa;
    }
    #createEventModal select.form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }
    #createEventModal .modal-footer {
        border-top: none;
        padding: 1.5rem 0 0;
        background-color: transparent;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }
    #createEventModal .btn-cancel {
        border-radius: 50px;
        padding: 0.8rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        color: #ffffff;
        border: 1px solid #444;
        background-color: transparent;
        transition: all 0.2s;
        flex: 1;
    }
    #createEventModal .btn-cancel:hover {
        background-color: #27272a;
        color: #ffffff;
    }
    #createEventModal .btn-save {
        border-radius: 50px;
        padding: 0.8rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        color: #000000 !important;
        background-color: #4ade80;
        border: none;
        transition: all 0.2s;
        flex: 1;
    }
    #createEventModal .btn-save:hover {
        background-color: #22c55e;
        opacity: 1;
    }
    #createEventModal .btn-close {
        display: none;
    }
    /* Make date input calendar icon white */
    #createEventModal input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        opacity: 0.6;
        cursor: pointer;
    }
    #createEventModal input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
</style>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="createEventModalLabel">
                    <button type="button" class="back-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    Create Event
                </h5>
            </div>

            <div class="modal-body">
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

                    <div class="form-container-box">
                        <div class="mb-4">
                            <label for="eventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="eventName" name="eventName" placeholder="What's the event name?">
                            <small id="eventName_error" class="text-danger ps-2"></small>
                        </div>

                        <div class="mb-4">
                            <label for="eventDate" class="form-label">Event Date</label>
                            <input type="date" class="form-control" name="eventDate" id="eventDate">
                            <small id="eventDate_error" class="text-danger ps-2"></small>
                        </div>

                        <div class="mb-4">
                            <label for="eventType" class="form-label">Event Type</label>
                            <select name="eventType" id="eventType" class="form-select">
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
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="3" placeholder="Describe your event..."></textarea>
                            <small id="eventDescription_error" class="text-danger ps-2"></small>
                        </div>

                        <div class="mb-2">
                            <label for="eventFrequency" class="form-label">Frequency</label>
                            <select class="form-select" name="eventFrequency" id="eventFrequency">
                                <option value="" disabled selected>Choose event frequency</option>
                                <option value="One-off">One-off</option>
                                <option value="Annually">Annually</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Weekly">Weekly</option>
                            </select>
                            <small id="eventFrequency_error" class="text-danger ps-2"></small>
                        </div>
                    </div>

                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" name="submit" class="btn btn-save" id="submitEventModal">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $token = $_SESSION['token'] ?? '';
    $_SESSION['CREATE_EVENT_ID'] = $data['id'];
@endphp

<!-- Create Event Modal Style overrides -->
<style>
    #createEventModal .modal-content {
        border-radius: 20px;
        background-color: var(--card-bg, var(--bg-white, #ffffff));
        color: var(--text-main, #0f172a);
        border: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
        overflow: hidden;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.15);
    }
    #createEventModal .modal-header {
        border-bottom: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
        padding: 0.85rem 1.25rem;
        background-color: var(--card-bg, var(--bg-white, #ffffff));
    }
    #createEventModal .modal-title {
        font-family: var(--font-family), sans-serif;
        font-weight: 700;
        color: var(--text-main, #0f172a);
        font-size: 1.25rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #createEventModal .modal-title .back-btn {
        color: var(--text-main, #0f172a);
        cursor: pointer;
        text-decoration: none;
        background: none;
        border: none;
        padding: 0;
        display: inline-flex;
        align-items: center;
        font-size: 1.25rem;
    }
    #createEventModal .modal-body {
        padding: 0.85rem 1rem;
        background-color: var(--card-bg, var(--bg-white, #ffffff));
    }
    #createEventModal .form-container-box {
        background-color: var(--bg-light, #f8fafc);
        border: 1px solid var(--border-color, rgba(0, 0, 0, 0.06));
        border-radius: 16px;
        padding: 0.85rem;
    }
    #createEventModal .form-label {
        color: var(--text-muted, #64748b);
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }
    #createEventModal .form-control, 
    #createEventModal .form-select {
        background-color: var(--bg-white, #ffffff);
        border: 1px solid var(--border-color, #cbd5e1);
        color: var(--text-main, #0f172a);
        border-radius: 10px;
        padding: 0.45rem 0.75rem;
        font-size: 0.9rem;
        transition: all 0.25s;
    }
    #createEventModal .form-control:focus, 
    #createEventModal .form-select:focus {
        border-color: var(--brand-primary, #00bfa5);
        box-shadow: 0 0 0 3px rgba(0, 191, 165, 0.15);
        background-color: var(--bg-white, #ffffff);
        color: var(--text-main, #0f172a);
        outline: none;
    }
    #createEventModal .form-control::placeholder {
        color: var(--text-muted, #94a3b8);
    }
    #createEventModal select.form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }
    #createEventModal .modal-footer {
        border-top: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
        padding: 0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom, 0px));
        background-color: var(--card-bg, var(--bg-white, #ffffff));
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        position: sticky;
        bottom: 0;
        z-index: 10;
        margin-top: 0.75rem;
    }
    #createEventModal .btn-cancel {
        border-radius: 50px;
        padding: 0.55rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main, #334155);
        border: 1px solid var(--border-color, #cbd5e1);
        background-color: transparent;
        transition: all 0.2s;
        flex: 1;
    }
    #createEventModal .btn-cancel:hover {
        background-color: var(--bg-light, #f1f5f9);
        color: var(--text-main, #0f172a);
    }
    #createEventModal .btn-save {
        border-radius: 50px;
        padding: 0.55rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: #ffffff !important;
        background-color: var(--brand-primary, #00bfa5);
        border: none;
        transition: all 0.2s;
        flex: 1;
    }
    #createEventModal .btn-save:hover {
        background-color: var(--brand-secondary, #004182);
        opacity: 1;
    }
    #createEventModal .btn-close {
        display: none;
    }
    /* Date picker indicator styling */
    #createEventModal input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0.6;
        cursor: pointer;
    }
    #createEventModal input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }

    /* Dark mode support */
    body.dark-mode #createEventModal .modal-content,
    body.dark-mode #createEventModal .modal-header,
    body.dark-mode #createEventModal .modal-body,
    body.dark-mode #createEventModal .modal-footer {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    body.dark-mode #createEventModal .modal-title,
    body.dark-mode #createEventModal .modal-title .back-btn {
        color: #f8fafc !important;
    }
    body.dark-mode #createEventModal .form-container-box {
        background-color: #0f172a !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    body.dark-mode #createEventModal .form-control,
    body.dark-mode #createEventModal .form-select {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    body.dark-mode #createEventModal input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
    body.dark-mode #createEventModal .btn-cancel {
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }
    body.dark-mode #createEventModal .btn-cancel:hover {
        background-color: #334155 !important;
        color: #ffffff !important;
    }
</style>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
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

                    <div id="loader" class="text-center mb-2"></div>
                    <div id="eventModalForm_notification">
                        <p id="error" class="text-danger small mb-1"></p>
                    </div>

                    <div class="form-container-box">
                        <div class="mb-2.5">
                            <label for="eventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="eventName" name="eventName" placeholder="What's the event name?">
                            <small id="eventName_error" class="text-danger ps-2"></small>
                        </div>

                        <div class="row g-2 mb-2.5">
                            <div class="col-6">
                                <label for="eventDate" class="form-label">Event Date</label>
                                <input type="date" class="form-control" name="eventDate" id="eventDate">
                                <small id="eventDate_error" class="text-danger ps-2"></small>
                            </div>

                            <div class="col-6">
                                <label for="eventType" class="form-label">Event Type</label>
                                <select name="eventType" id="eventType" class="form-select">
                                    <option value="" disabled selected>Choose type</option>
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
                        </div>

                        <div class="mb-2.5">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="2" placeholder="Describe your event..."></textarea>
                            <small id="eventDescription_error" class="text-danger ps-2"></small>
                        </div>

                        <div class="mb-1">
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

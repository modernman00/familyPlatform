<div id="id_event_modal" class="w3-modal">

    <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">

        <br><br>

        <h3 class="w3-center modal-h1">
            Enter the event details below
        </h3>

        <div id="loader"></div>

        <div id="eventModalForm_notification">
            <p id="error"></p>
        </div>

        @php 
            $token = urlencode(base64_encode((random_bytes(32))));
            $_SESSION['token'] = $token;
        @endphp

        {{--  SHOW ERROR   --}}

        {{--  <p id="errorEventModal"></p>  --}}

        <div class="w3-center"><br>
            <span id="cancelModal"
                class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
        </div>



        <form class="w3-container eventModalForm" action="" id='eventModalForm' method="post"
            enctype='multipart/form-data'>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><em class="w3-xxlarge fa fa-glass"></em></div>
                <div class="w3-rest">
                    <input class="w3-input w3-border" name="eventName" id="eventName" type="text"
                        placeholder="Event Name">
                    <p id="eventName_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><em class="w3-xxlarge fa fa-user"></em></div>
                <div class="w3-rest">
                    <input class="w3-input w3-border" name="eventDate" id="eventDate" type="date">
                    <p id="eventDate_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><em class="w3-xxlarge fa fa-glass"></em></div>
                <div class="w3-rest">
                    <select class="w3-select" id="eventType" name="eventType" id="eventType">
                        <option value="" disabled selected>Choose event type</option>
                        <option value="Wedding">Wedding</option>
                        <option value="House Warming">House Warming</option>
                        <option value="Anniversary">Anniversary</option>
                        <option value="Barbecue">Barbecue</option>
                        <option value="Holidays">Holidays</option>
                        <option value="Naming">Naming</option>
                        <option value="Remembrance">Remembrance</option>
                        <option value="Education">Education</option>
                          <option value="Birthday">Birthday</option>
                        <option value="Milestones">Milestones</option>
                        <option value="Relationship">Relationship</option>
                        <option value="Work">Work</option>
                        <option value="Others">Others</option>
                    </select>
                    <p id="eventType_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><em class="w3-xxlarge fa fa-location-arrow"></em></div>
                <div class="w3-rest">
                    <select class="w3-select" name="eventGroup" id="eventGroup">
                        <option value="" disabled selected>Choose event group</option>
                        <option value="Global">Global</option>
                        <option value="UK">UK</option>
                        <option value="Canada">Canada</option>
                        <option value="Europe">Europe</option>
                        <option value="Outside Nigeria">Outside Nigeria</option>

                    </select>
                    <p id="eventGroup_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><em class="w3-xxlarge fa fa-comments"></em></div>
                <div class="w3-rest">
                    <input class="w3-input w3-border" name="eventDescription" type="text"
                        placeholder="Describe what the event is all about" id="eventDescription">
                        <p id="eventDescription_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><em class="w3-xxlarge fa fa-pencil"></em></div>
                <div class="w3-rest">
                    <select class="w3-select" name="eventFrequency" id="eventFrequency">
                        <option value="" disabled selected>Choose event frequency</option>
                        <option value="One-off">One-off</option>
                        <option value="Annually">Annually</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Weekly">Weekly</option>

                    </select>
                    <p id="eventFrequency_error"></p>
                    <br>
                </div>
            </div>

              <input type="hidden" name= 'token' value = {{ $token }} >

            <button id="submitEventModal"  type="submit" name="submit" class="w3-button w3-block w3-section w3-blue  w3-padding submitEventModal"> Create Event
            </button>

            {{-- <button id="cancelModal" type="button" name="cancel"
            class="w3-button w3-red eventModalCancel">Cancel</button> --}}
            <br><br>

          

        </form>

    </div>
</div>

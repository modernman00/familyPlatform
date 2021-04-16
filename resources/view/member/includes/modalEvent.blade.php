<div id="id_event_modal" class="w3-modal">

    <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">

        <br><br>

        <h3 class="w3-center modal-h1">
            Enter the event details below
        </h3>

        @php 

            $token = urlencode(base64_encode((random_bytes(32))));


        @endphp



        {{--  SHOW ERROR   --}}

        <p id="errorEventModal"></p>

        {{-- @isset($error)
        <p> {{ $error }}</p>
        @endisset --}}

        <div class="w3-center"><br>
            <span id="cancel"
                class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
        </div>

        <form class="w3-container eventModalForm" action="/member/createEvent" id='eventModalForm' method="post"
            enctype='multipart/form-data'>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><i class="w3-xxlarge fa fa-glass"></i></div>
                <div class="w3-rest">
                    <input class="w3-input w3-border" name="eventName" id="eventName" type="text"
                        placeholder="Event Name">
                    <p id="eventName_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><i class="w3-xxlarge fa fa-user"></i></div>
                <div class="w3-rest">
                    <input class="w3-input w3-border" name="eventDate" id="eventDate" type="date">
                    <p id="eventDate_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><i class="w3-xxlarge fa fa-glass"></i></div>
                <div class="w3-rest">
                    <select class="w3-select" name="eventType" id="eventType">
                        <option value="" disabled selected>Choose event type</option>
                        <option value="Wedding">Wedding</option>
                        <option value="House Warming">House Warming</option>
                        <option value="Anniversary">Anniversary</option>
                        <option value="Barbecue">Barbecue</option>
                        <option value="Holidays">Holidays</option>
                        <option value="Naming">Naming</option>
                        <option value="Remembrance">Remembrance</option>
                        <option value="Others">Others</option>
                    </select>
                    <p id="eventType_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><i class="w3-xxlarge fa fa-location-arrow"></i></div>
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
                <div class="w3-col" style="width:50px"><i class="w3-xxlarge fa fa-comments"></i></div>
                <div class="w3-rest">
                    <input class="w3-input w3-border" name="eventDescription" type="eventDescription"
                        placeholder="Describe what the event is all about" id="eventDescription">
                        <p id="eventDescription_error"></p>
                </div>
            </div>

            <div class="w3-row w3-section">
                <div class="w3-col" style="width:50px"><i class="w3-xxlarge fa fa-pencil"></i></div>
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

            <button type="button" id="submitEventModal"
                class="w3-button w3-block w3-section w3-blue w3-ripple w3-padding">
                <i class="fa fa-pencil"></i>Send
            </button>

            <button id="cancel" type="button"
            class="w3-button w3-red eventModalCancel">Cancel</button>
            <br><br>

          

        </form>

    </div>
</div>

<script>

    try {

        const id = (x) => document.getElementById(x)

        const displayNone = () => id('id_event_modal').style.display='none'

          id('cancel').addEventListener('click', displayNone)
    


    const process = () => {
    const formInput = document.querySelectorAll('.eventModalForm');
    const formInputArr = Array.from(formInput);
    const errorArr = [];

    const reg = /[a-zA-Z0-9./@]/g;

    formInputArr.forEach(el => {
        for (let post of el ) {
            


        
            // error variable
            let getErrorHTML = `${post.name}_error`
            let errorMsg = id(getErrorHTML)
        

            // rid off the submit button

            if (post.type == 'button' || post.name == 'token' ) {
                    continue;
                }

            const postNameCleaned = post.name.replace('event', 'Event ')

            // check post value 

            if (post.value === '') {

                    errorMsg.innerHTML = `${postNameCleaned} cannot be left empty`
                    errorArr.push(`${postNameCleaned} cannot be left empty`)
                    errorMsg.style.color ='red'
                
            // regex the input
            } else if (post.value.match(reg) === null) {

                    errorMsg.innerHTML = ` only letters and numbers are allowed`
                    errorMsg.style.color ='red'
                    errorArr.push(` only letters and numbers are allowed`);

                }
        }
       
    })

    if(errorArr.length <= 0) {
        id('submitEventModal').type = 'submit'
    }
    
    }

    id('submitEventModal').addEventListener('click', process)
    
    } catch (e) {
        
    }

    

   

</script>
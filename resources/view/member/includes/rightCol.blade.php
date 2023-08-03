 <div class="w3-col m2">
    <div class="w3-card w3-round w3-white w3-center">
      <div class="w3-container" id="upComingEvent">
        <p id="upComingEventHeader"><b>UPCOMING EVENTS:</b></p>
        <hr>
        {{--  <img src="/w3images/forest.jpg" alt="Forest" style="width:100%;">  --}}

        <div class="eventList" id="eventList">

           @foreach($eventData as $event) 

        @php $dateDiff = dateDifferenceInt(date('Y-m-d'), $event['eventDate']); @endphp

          @if($dateDiff > 0 && $dateDiff <= 30)
  
            <p class="eventInfo"><strong>RSVP: </strong>{{ $event['firstName'] }} {{ $event['lastName'] }}</p>
            <p class="eventInfo"><strong>Event: </strong>{{ $event['eventName'] }}</p>
            <p class="eventInfo"><strong>Date: </strong>{{ dateFormat($event['eventDate']) }} |<i style="color: rgba(11, 11, 201, 0.631)"> In {{ number2word($dateDiff) }} Day(s)</i> </p>
            <p class="eventInfo"><strong>Type: </strong>{{ $event['eventType'] }}</p>
            <p class="eventInfo"><strong>Description: </strong> {{ $event['eventDescription'] }}</p>
           <hr>
          @endif
       
        @endforeach


        </div>

       
        
        {{--  <p><button class="w3-button w3-block w3-theme-l4">Info</button></p>  --}}
      </div>
    </div>
    <br>

    <div class="w3-card w3-round w3-white w3-center">
        <div class="w3-container">
          <p>Friend Request</p>
          <img src="/w3images/avatar6.png" alt="Avatar" style="width:50%"><br>
          <span>Jane Doe</span>
          <div class="w3-row w3-opacity">
            <div class="w3-half">
              <button class="w3-button w3-block w3-green w3-section" title="Accept"><i class="fa fa-check"></i></button>
            </div>
            <div class="w3-half">
              <button class="w3-button w3-block w3-red w3-section" title="Decline"><i class="fa fa-remove"></i></button>
            </div>
          </div>
        </div>
      </div>

       <br>

       <div class="w3-card w3-round w3-white w3-padding-16 w3-center">
        <p>ADS</p>
      </div>

      <br>

         <form action="/photos/postImages" method="POST" enctype="multipart/form-data">
         
          <div class="custom-file-container" data-upload-id="myUniqueUploadId">
  
               <label>Upload File  </label>
              <a href="javascript:void(0)" 
              class="custom-file-container__image-clear" 
              title="Clear Image"><em class="fa fa-ban" style="color: blue" arial-hidden="true"></em></a>
           
           
            <label for="postMessage_uploadPics" class="custom-file-container__custom-file">
              <input type="file" accept="image/*, image/heif, image/heic, video/*" class="custom-file-container__custom-file__custom-file-input" aria-label="Choose File"
                id="postMessage_uploadPics" name="photo[]" multiple />
              <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
              <span class="custom-file-container__custom-file__custom-file-control"></span>
            </label>

            <div class="custom-file-container__image-preview"></div>
            <button class="w3-btn w3-round w3-red" name="uploadPhotos" id="uploadPhotos" style="width: 240px;">Upload
              pictures </button>
          </div>

        </form>


    <!-- End Right Column -->
  </div>

  <script src="https://unpkg.com/file-upload-with-preview@4.1.0/dist/file-upload-with-preview.min.js"></script>
<script>
  const upload = new FileUploadWithPreview("myUniqueUploadId", {
                showDeleteButtonOnImages: true
            });
</script>
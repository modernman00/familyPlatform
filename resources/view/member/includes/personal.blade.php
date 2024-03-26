<!-- Profile -->

<style>
  /* Reset default file input styles */
input[type="file"] {
    display: none;
}

.custom-file-upload {
    display: inline-block;
    {{--  padding: 10px 20px;  --}}
    background-color: #3498db;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease-in-out;
}

.custom-file-upload:hover {
    background: #2e88c1;
}

/* Style the file input text */
.custom-file-upload span {
    font-size: 16px;
    font-weight: bold;
}

/* Style the file input to look like a button */
.custom-file-upload input[type="file"] {
    display: none;
}

.custom-file-upload::before {
    content: '(1) Upload';
    display: inline-block;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    outline: none;
    white-space: nowrap;
    cursor: pointer;
    font-weight: bold;
}

.custom-file-upload input[type="file"]::file-selector-button {
    display: none;
}

.custom-file-upload input[type="file"]::-webkit-file-upload-button {
    display: none;
}

.custom-file-upload:hover::before {
    background: #2e88c1;
}

/* Style the selected file name */
.custom-file-upload input[type="file"]:not([value=""]) + button {
    display: inline-block;
    margin-left: 10px;
    font-weight: normal;
    color: #3498db;
}

.changeProfileButton{
  font-size: 0.9em;
  font-weight: bold;
   box-shadow: 0px -1px 0px 0px #3dc21b;
    background-color: #44c767;
    border-radius: 1.5rem;
    border: 1px solid #18ab29;
    display: inline-block;
    cursor: pointer;
    color: #ffffff;
    width: 8rem;
    padding: 0.5rem 1.1rem;
    text-decoration: none;
    text-shadow: 0px 0px 0px #2f6627;
}

.changeProfileButton:hover {
    background-color: #5cbf2a;
}

.changeProfileButton:active {
    position: relative;
    top: 1px;
}

/* Mobile Optimization */
@media (max-width: 768px) {
    .custom-file-upload {
        padding: 8px 16px; /* Adjust padding for smaller screens */
        font-size: 14px; /* Reduce font size for smaller screens */
    }
}

</style>
<div class="w3-card w3-round w3-white">
  <div class="w3-container">

    {{-- NAME --}}

    <h4 class="w3-center">
      <b>
        {{ $data['firstName'] }} 
        {{ $data['lastName'] }} 
        {{-- <br> FAMILY CODE:  
        {{ $data['famCode'] }} 
        {{-- {{ $data['id'] }} </i>  --}}
        </b> 
      {{-- {{ $data['id'] }} --}}

    </h4>

    {{-- PROFILE PICS --}}

    <p class="w3-center">
      <a href="#profilePics" id="profilePics">

        @isset($data['img'])

        <img src="/img/profile/{{ $data['img'] }}" class="w3-circle profileImg" alt="Avatar">
      

        @else
        <img src="/avatar/avatarF.png" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top">

        @endisset
        <br>
  <i class="fa fa-camera fa-lg" style="color:#e30d0d;"></i>
        {{-- @empty($data['img'])
        <img src="/avatar/avatarF.png" alt="Avatar" class="avatar">
        @endempty --}}

      </a>
    </p>

    {{-- ADD / CHANGE PROFILE PICS --}}

    <form action='/member/profilePage/profileImg' method='post' enctype='multipart/form-data' id="formProfilePics"
      style="display: none;">

     
      <div class="changeProfileDisplay">

          {{-- <label for="profileImageFile" class="custom-file-upload">
        <span>Choose a File</span>
        <input type="file" id="profileImageFile" name="file" accept=".jpg, .jpeg, .png">
    </label> --}}



         <label for="profileImageFile" class="custom-file-upload"></label>
            <button class='changeProfileButton' name='submit' type="submit">(2)  Save </button>

        <input type="file" id="profileImageFile"
          name="profileImageFile" accept=".jpg, .jpeg, .png">

      


      </div> 


    </form>
    <hr>

    {{-- SPOUSE --}}
    <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> Spouse: {{ $data['spouseName'] }}</p>

    {{-- COUNTRY --}}
    <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> {{ $data['country'] }}</p>

     {{-- famcode --}}
    <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> Family code: {{ $data['famCode'] }}</p>

       {{-- id --}}
    {{-- <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> Membership ID: {{ $data['id'] }}</p> --}}


    {{-- DATE OF BIRTH --}}
    <p><i class="fa fa-birthday-cake fa-fw w3-margin-right w3-text-theme"></i> {{ $data['day'] }} /
      {{ $data['month'] }} /{{ $data['year'] }}
    </p><br>

  </div>
</div>
<br>

<!-- ACCORDION -->


@includeif('member/includes/accordion')
<br>

<br>

@includeif('member/includes/modalEvent')

{{--  FRIEND REQUESTS  --}}

  <div class="w3-card w3-round w3-white w3-center">
     @if (count($requestData) > 1)
     <p><b>Friend Requests</b></p><br>
     @else
     <p><b>Friend Request</b></p><br>
     @endif
     <div class="w3-container requestFriendClass">
     </div>
   </div>

<div class="w3-card w3-round w3-white w3-padding-16 w3-center">
  <p>ADS</p>
</div>

 

<script>
  const profile = {
          requesterFirstName: @json($data['firstName']),
          requesterLastName: @json($data['lastName']),
           requesterId: @json($data['id']),
          requesterEmail: @json($data['email']),
          requesterProfileImg: @json($data['img']),
          requesterFamCode: @json($data['famCode'])

        };
     localStorage.setItem('requesterFamCode', @json($data['famCode']));
      localStorage.setItem('requesterId', @json($data['id']));

        // Convert the object to a JSON string and store it in localStorage
localStorage.setItem('profile', JSON.stringify(profile));




</script>


<!-- End Left Column -->
<!-- End Left Column -->
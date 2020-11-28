@extends ('layouts.w3s_member')
@section('title', 'PROFILE_PAGE')
@section('data-page-id', 'profilePage')
@section('content')

<h1>WELCOME TO THE PERSON PAGE</h1>


<!-- Page Container -->
<div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
  <!-- The Grid -->
  <div class="w3-row">
    <!-- Left Column -->
    <div class="w3-col m3">
      <!-- Profile -->
      <div class="w3-card w3-round w3-white">
        <div class="w3-container">
          <h4 class="w3-center">
            {{ $data['firstName'] }}'s
            Profile</h4>
          <p class="w3-center"><img src="/img/Ore 6th.jpeg" class="w3-circle" style="height:106px;width:106px"
              alt="Avatar"></p>
          <hr>
          <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> {{ $data['jobTitle'] }}</p>
          <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> {{ $data['country'] }}</p>
          <p><i class="fa fa-birthday-cake fa-fw w3-margin-right w3-text-theme"></i> {{ $data['birthDate'] }}</p>
        </div>
      </div>
      <br>

      <!-- Accordion -->
      <div class="w3-card w3-round">
        <div class="w3-white">
          <button onclick="myFunction('Demo1')" class="w3-button w3-block w3-theme-l1 w3-left-align"><i
              class="fa fa-circle-o-notch fa-fw w3-margin-right"></i> My Groups</button>
          <div id="Demo1" class="w3-hide w3-container">
            <p>Some text..</p>
          </div>
          <button onclick="myFunction('Demo2')" class="w3-button w3-block w3-theme-l1 w3-left-align"><i
              class="fa fa-calendar-check-o fa-fw w3-margin-right"></i> My Events</button>
          <div id="Demo2" class="w3-hide w3-container">
            <p>Some other text..</p>
          </div>
          <button onclick="myFunction('Demo3')" class="w3-button w3-block w3-theme-l1 w3-left-align"><i
              class="fa fa-users fa-fw w3-margin-right"></i> My Photos</button>
          <div id="Demo3" class="w3-hide w3-container">
            <div class="w3-row-padding">
              <br>
              <div class="w3-half">
                <img src="/w3images/lights.jpg" style="width:100%" class="w3-margin-bottom">
              </div>
              <div class="w3-half">
                <img src="/w3images/nature.jpg" style="width:100%" class="w3-margin-bottom">
              </div>
              <div class="w3-half">
                <img src="/w3images/mountains.jpg" style="width:100%" class="w3-margin-bottom">
              </div>
              <div class="w3-half">
                <img src="/w3images/forest.jpg" style="width:100%" class="w3-margin-bottom">
              </div>
              <div class="w3-half">
                <img src="/w3images/nature.jpg" style="width:100%" class="w3-margin-bottom">
              </div>
              <div class="w3-half">
                <img src="/w3images/snow.jpg" style="width:100%" class="w3-margin-bottom">
              </div>
            </div>
          </div>
        </div>
      </div>
      <br>

      <!-- Interests -->
      <div class="w3-card w3-round w3-white w3-hide-small">
        <div class="w3-container">
          <p>Interests</p>
          <p>
            <span class="w3-tag w3-small w3-theme-d5">{{ $data['favSport'] }}</span>
            <span class="w3-tag w3-small w3-theme-d4">{{ $data['footballTeam'] }}</span>
            <span class="w3-tag w3-small w3-theme-d3">{{ $data['passion'] }} </span>
            <span class="w3-tag w3-small w3-theme-d2">Games</span>
            <span class="w3-tag w3-small w3-theme-d1">Friends</span>
            <span class="w3-tag w3-small w3-theme">Games</span>
            <span class="w3-tag w3-small w3-theme-l1">Friends</span>
            <span class="w3-tag w3-small w3-theme-l2">Food</span>
            <span class="w3-tag w3-small w3-theme-l3">Design</span>
            <span class="w3-tag w3-small w3-theme-l4">Art</span>
            <span class="w3-tag w3-small w3-theme-l5">Photos</span>
          </p>
        </div>
      </div>
      <br>

      <!-- Alert Box -->
      <div
        class="w3-container w3-display-container w3-round w3-theme-l4 w3-border w3-theme-border w3-margin-bottom w3-hide-small">
        <span onclick="this.parentElement.style.display='none'" class="w3-button w3-theme-l3 w3-display-topright">
          <i class="fa fa-remove"></i>
        </span>
        <p><strong>Hey!</strong></p>
        <p>People are looking at your profile. Find out who.</p>
      </div>

      <!-- End Left Column -->
    </div>

    <!-- Middle Column -->
    <div class="w3-col m7">

      <div class="w3-row-padding">
        <div class="w3-col m12">
          <div class="w3-card w3-round w3-white">
            <div class="w3-container w3-padding">


              <form action="/member/profilePage/post" id='formPostMessage' method="post">

                <input type="text" name="postMessage" id="postMessage"
                  placeholder="What is on your mind {{ $data['firstName'] }}" class="w3-input w3-border w3-padding ">


                <br>

                <button type="submit" name="submit" class="w3-button w3-theme w3-button w3-green w3-large"><i
                    class="fa fa-pencil"></i>
                   Post</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      @for ($x = 0; $x < count($allData); $x++) <div class="w3-container w3-card w3-white w3-round w3-margin"><br>
        <img src="/public/img/seyi/seyi3.jpeg" alt="Avatar" class="w3-left w3-circle w3-margin-right"
          style="width:60px">
        
        <span class="w3-right w3-opacity">{{ $allData[$x]['post_time']  }}</span>

        {{-- <h4 id="fullName"> {{ $allData[$x]['fullName'] }}</h4><br> --}}
        <canvas id="user-icon" width="125" height="125"></canvas>

        <hr class="w3-clear">

        <p> {{ $allData[$x]['postMessage'] }} </p>

        <div class="w3-row-padding" style="margin:0 -16px">
          <div class="w3-half">
            <img src="/w3images/lights.jpg" style="width:100%" alt="Northern Lights" class="w3-margin-bottom">
          </div>
          <div class="w3-half">
            <img src="/w3images/nature.jpg" style="width:100%" alt="Nature" class="w3-margin-bottom">
          </div>
        </div>
        <button type="button" class="w3-button w3-theme-d1 w3-margin-bottom"><i class="fa fa-thumbs-up"></i>
           Like</button>
        <button type="button" class="w3-button w3-theme-d2 w3-margin-bottom"><i class="fa fa-comment"></i>
           Comment</button>
    </div>

    @endfor


  </div>

  <!-- Right Column -->
  <div class="w3-col m2">
    <div class="w3-card w3-round w3-white w3-center">
      <div class="w3-container">
        <p>Upcoming Events:</p>
        <img src="/w3images/forest.jpg" alt="Forest" style="width:100%;">
        <p><strong>Holiday</strong></p>
        <p>Friday 15:00</p>
        <p><button class="w3-button w3-block w3-theme-l4">Info</button></p>
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

    <div class="w3-card w3-round w3-white w3-padding-32 w3-center">
      <p><i class="fa fa-bug w3-xxlarge"></i></p>
    </div>

    <!-- End Right Column -->
  </div>

  <!-- End Grid -->
</div>

<!-- End Page Container -->
</div>
<br>

{{-- MODAL --}}
<div id="id01" class="w3-modal">
  <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">

    <div class="w3-center"><br>
      <span onclick="document.getElementById('id01').style.display='none'"
        class="w3-button w3-xlarge w3-hover-red w3-display-topright" title="Close Modal">&times;</span>
      <img src="/img/seyi/seyi1.jpeg" alt="Avatar" style="width:30%" class="w3-circle w3-margin-top">
    </div>

    <form class="w3-container" action="/member/profilePage/post" id='formPostMessage' method="post">
      <div class="w3-section">

        {{-- <input class="w3-input w3-border w3-margin-bottom" type="text" placeholder="Enter Username" name="usrname"
          required> --}}

        <textarea name="postMessage" id="postMessage" cols="50" rows="10">Post here</textarea>

        <button type="submit" name="submit" class="w3-button w3-theme w3-button w3-green w3-large"><i
            class="fa fa-pencil"></i>
           Post</button>

      </div>
    </form>

    <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
      <button onclick="document.getElementById('id01').style.display='none'" type="button"
        class="w3-button w3-red">Cancel</button>

    </div>

  </div>
</div>

{{-- MODAL --}}
<script>
  try {
    const showModal = ()=> {
        return document.getElementById('id01').style.display = 'block'
    }
    document.getElementById('postMessage').addEventListener('click', showModal)
} catch (e) {
    console.log(e.message)
}

// Avatar FOR NAMES 

const colours = ["#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50", "#f1c40f", "#e67e22", "#e74c3c", "#95a5a6", "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"];

const name = "{{ $data['firstName'] }} {{ $data['lastName'] }}",
    nameSplit = name.split(" "),
    initials = nameSplit[0].charAt(0).toUpperCase() + nameSplit[1].charAt(0).toUpperCase();

const charIndex = initials.charCodeAt(0) - 65,
    colourIndex = charIndex % 19;

const canvas = document.getElementById("user-icon");
const context = canvas.getContext("2d");

const canvasWidth = $(canvas).attr("width"),
    canvasHeight = $(canvas).attr("height"),
    canvasCssWidth = canvasWidth,
    canvasCssHeight = canvasHeight;

if (window.devicePixelRatio) {
    $(canvas).attr("width", canvasWidth * window.devicePixelRatio);
    $(canvas).attr("height", canvasHeight * window.devicePixelRatio);
    $(canvas).css("width", canvasCssWidth);
    $(canvas).css("height", canvasCssHeight);
    context.scale(window.devicePixelRatio, window.devicePixelRatio);
}

context.fillStyle = colours[colourIndex];
context.fillRect (0, 0, canvas.width, canvas.height);
context.font = "128px Arial";
context.textAlign = "center";
context.fillStyle = "#FFF";
context.fillText(initials, canvasCssWidth / 2, canvasCssHeight / 1.5);



</script>



@endsection
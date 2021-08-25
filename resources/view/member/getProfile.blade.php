@extends ('layouts.w3s_member')
@section('title', 'Get Profile')

  <!-- About section -->
  <div class="w3-container w3-dark-grey w3-center w3-text-light-grey w3-padding-32" id="about">
      {{-- <div class="w3-panel w3-blue w3-round-xlarge">



  </div> --}}

    <h4> <a
        href="/organogram?id={{ $data['id'] }}"> SEE {{ $data['firstName'] }} {{ $data['lastName'] }} FAMILY TREE</a></h4>
       

    <img src="/img/profile/{{ $data['img'] }}" alt="Me" class="w3-image w3-padding-32" width="600" height="650">

    <div class="w3-content w3-justify" style="max-width:600px">
      <h4>{{ $data['firstName'] }} {{ $data['lastName'] }}</h4>
      <p>
        {{ $data['firstName'] }} {{ $data['lastName'] }} lives in {{ $data['country'] }}. 
          @if ( $data['gender'] == "male") 
            He 
          @else 
        She 
        @endif 

        loves {{ $data['passion'] }} and {{ $data['favSport'] }}

        @isset($data['footballTeam'])
           ,  and also support {{ $data['footballTeam'] }} football team. 
        @endisset

      </p>
      <br>

      Contact details:
      <p>mail: {{ $data['email'] }}</p>
      <p>tel: {{ $data['mobile'] }}</p>
      <hr class="w3-opacity">

      <div class="w3-white">

        <hr class="w3-opacity">


      </div>
    </div>
{{--  
    <!-- Contact section -->
    <div class="w3-container w3-light-grey w3-padding-32 w3-padding-large" id="contact">
      <div class="w3-content" style="max-width:600px">
        <h4 class="w3-center"><b>Contact Me</b></h4>

        <form action="/action_page.php" target="_blank">
          <div class="w3-section">
            <label>Name</label>
            <input class="w3-input w3-border" type="text" name="Name" required>
          </div>
          <div class="w3-section">
            <label>Email</label>
            <input class="w3-input w3-border" type="text" name="Email" required>
          </div>
          <div class="w3-section">
            <label>Message</label>
            <input class="w3-input w3-border" type="text" name="Message" required>
          </div>
          <button type="submit" class="w3-button w3-block w3-black w3-margin-bottom">Send Message</button>
        </form>
      </div>
    </div>  --}}



    <!-- End page content -->
  </div>

  <script>
    // Script to open and close sidebar
function w3_open() {
  document.getElementById("mySidebar").style.display = "block";
  document.getElementById("myOverlay").style.display = "block";
}

function w3_close() {
  document.getElementById("mySidebar").style.display = "none";
  document.getElementById("myOverlay").style.display = "none";
}

// Modal Image Gallery
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
  var captionText = document.getElementById("caption");
  captionText.innerHTML = element.alt;
}

  </script>
{{-- 
@endsection --}}
@extends ('layouts.w3s_member')
@section('title', 'Get Profile')
@section('content')

<!-- About section -->
<div class="w3-container w3-dark-grey w3-center w3-text-light-grey w3-margin-top w3-padding-32" id="about">
  {{-- <div class="w3-panel w3-blue w3-round-xlarge">



  </div> --}}
<br><br>
  <h4> <a href="/organogram?id={{ $data['id'] }}"> SEE {{ $data['firstName'] }} {{ $data['lastName'] }} FAMILY TREE</a>
  </h4>


  <img src="/img/profile/{{ $data['img'] }}" alt="Me" class="w3-image w3-padding-32" width="600" height="650">

  <div class="w3-content w3-justify" style="max-width:600px">
    <h4><b>{{ $data['firstName'] }} {{ $data['lastName'] }}</b></h4>
    <p>
      {{ $data['firstName'] }} {{ $data['lastName'] }} lives in {{ $data['country'] }}.
      {{-- @if ( $data['gender'] == "male")
      He
      @else
      She  is a/an 
      @endif --}}



    </p>
    <br>

    Contact details:
    <p>email: <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a></p>
    <p>tel:  {{ $data['mobile'] }}</p>
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
  </div> --}}



  <!-- End page content -->
</div>
@endsection



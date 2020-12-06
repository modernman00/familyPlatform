<!-- Left Column -->
<div class="w3-col m3">
  <!-- Profile -->
  <div class="w3-card w3-round w3-white">
    <div class="w3-container">
      
      <h5 class="w3-center">
        {{ $data['firstName']  }} {{ $data['lastName'] }}</h5>
      <p class="w3-center">

        @if (isset($data['img']))

        <a href="#profilePics" id="profilePics">
          <img src="/img/profile/{{ $data['img'] }}" class="w3-circle" style="height:106px;width:106px" alt="Avatar">

        </a>

      
      
        @else
        <img src="/img/profile/avatar" class="w3-circle" id = "profilePics1" style="height:106px;width:106px" alt="Avatar">

        @endif



      </p>

      <form action='/member/profilePage/profileImg' method='post' enctype='multipart/form-data' id="formProfilePics">

        <button class='w3-button'>
          <input type="file" id="profileImage" name="profileImage">
          <button class='w3-button'>

            <button name='submit' type="submit">submit</button>

      </form>
      <hr>
      <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> Spouse: {{ $data['spouse'] }}</p>
      <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> {{ $data['country'] }}</p>
      <p><i class="fa fa-birthday-cake fa-fw w3-margin-right w3-text-theme"></i> {{ $data['birthDate'] }}</p>
    </div>
  </div>
  <br>

  <!-- Accordion -->


  @include('member/includes/accordion')
  <br>

  <!-- Interests -->

  @include('member/includes/interest')

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
<!-- Left Column -->
<div class="w3-col m3">
  <!-- Profile -->
  <div class="w3-card w3-round w3-white">
    <div class="w3-container">

      <h5 class="w3-center">
        {{ $data['firstName']  }} {{ $data['lastName'] }} {{$data['id']}}</h5>
      <p class="w3-center">
        <a href="#profilePics" id="profilePics">
          <img src="/img/profile/{{ $data['img'] }}" class="w3-circle" style="height:180px;width:180px" alt="Avatar">
        </a>
      </p>

      <form action='/member/profilePage/profileImg' method='post' enctype='multipart/form-data' id="formProfilePics"
        style="display: none;">


        <label for="profileImage">Browse</label><br>

        <input for="profileImage" type="file" id="profileImage" name="profileImage">
        <button class='w3-button'>


          <button class='w3-button' name='submit' type="submit">submit</button>

      </form>
      <hr>

      {{-- spouse --}}
      <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> Spouse: {{ $data['spouseName'] }}</p>

      {{-- country --}}
      <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> {{ $data['country'] }}</p>

      {{-- date of birth --}}
      <p><i class="fa fa-birthday-cake fa-fw w3-margin-right w3-text-theme"></i> {{ $data['day'] }} /
        {{ $data['month'] }} /{{ $data['year'] }}</p>

    </div>
  </div>
  <br>

  <!-- Accordion -->


  @include('member/includes/accordion')
  <br>

  <!-- Interests -->

  @include('member/includes/interest')

  <br>


  <!-- End Left Column -->
</div>
 
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

              <form action='/member/profilePage/profileImg' method='post' enctype='multipart/form-data' >
              
              <button class='w3-button'>
              <input type="file" id="profileImage" name="profileImage[]" multiple=true>
              <button class='w3-button'>

              <button name='submit' type="submit">submit</button>
              
              </form>
          <hr>
          <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> {{ $data['spouse'] }}</p>
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

 
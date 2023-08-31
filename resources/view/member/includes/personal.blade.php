<!-- Profile -->
<div class="w3-card w3-round w3-white">
  <div class="w3-container">

    {{-- NAME --}}

    <h4 class="w3-center">
      <b><i>{{ $data['firstName'] }} {{ $data['lastName'] }} <br> FAMILY CODE:  {{ $data['famCode'] }} </i> </b>
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

        {{-- @empty($data['img'])
        <img src="/avatar/avatarF.png" alt="Avatar" class="avatar">
        @endempty --}}

      </a>
    </p>

    {{-- ADD / CHANGE PROFILE PICS --}}

    <form action='/member/profilePage/profileImg' method='post' enctype='multipart/form-data' id="formProfilePics"
      style="display: none;">

      <div class="changeProfileDisplay">

        <label for="profileImageFile"></label><br>

        <input for="profileImageFile" type="file" id="profileImageFile" class="profileImageFile"
          name="profileImageFile">

        <button class='changeProfileButton' name='submit' type="submit">Change pics</button>
      </div>


    </form>
    <hr>

    {{-- SPOUSE --}}
    <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> Spouse: {{ $data['spouseName'] }}</p>

    {{-- COUNTRY --}}
    <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> {{ $data['country'] }}</p>


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

        // Convert the object to a JSON string and store it in localStorage
localStorage.setItem('profile', JSON.stringify(profile));




</script>


<!-- End Left Column -->
<!-- End Left Column -->
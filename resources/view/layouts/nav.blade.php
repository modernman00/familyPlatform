<div class="w3-top">


  <div class="w3-bar w3-light-grey w3-left-align w3-medium">

    <a href="#" class="w3-bar-item w3-button w3-padding-large "><em
        class="fa fa-home w3-margin-right w3-mobile w3-hide-small"></em><img src={{ getenv("IMG_CONTRACT") }}
        alt="logo"></a>

    <a href="/allMembers" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white w3-mobile allMemberNav"
      title="All members"><em class="fa fa-users "></em> All Members</a>

        <a href="/member/ProfilePage" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white w3-mobile profilePageNav"
      title="All members"><em class="fa fa-users "></em> My page</a>

    @isset($data)
    {{-- @isset($record)
        
    @endisset --}}
          <a href="/organogram?id={{ $data['id'] }}"
      class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white familyTreeNav" title="Account Settings"><i
        class="fa fa-tree"></i>Family Tree</a>

          @if (isset($data['mobile']) && !empty($data['email']) && !empty($data['country']) && !empty($data['famCode']))

            <a href="/accountSetting?id={{ $data['id'] }}&mobile={{ $data['mobile'] }}&email={{ $data['email'] }}&country={{ $data['country'] }}&famCode={{$data['famCode']}}"
              class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" title="Account Settings"><i
                class="fa fa-cog"></i>Account Setting</a>

         @endif
        
    @endisset

    {{-- <a href="#" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" title="Messages"><i
        class="fa fa-envelope"></i></a> --}}
    <div class="w3-dropdown-hover w3-hide-small">
      <button class="w3-button w3-padding-large" title="Notifications"><i class="fa fa-bell"></i><span
          class="w3-badge w3-right w3-small w3-green">3</span></button>
      <div class="w3-dropdown-content w3-card-4 w3-bar-block" style="width:300px">
        <a href="#" class="w3-bar-item w3-button">One new friend request</a>
        <a href="#" class="w3-bar-item w3-button">John Doe posted on your wall</a>
        <a href="#" class="w3-bar-item w3-button">Jane likes your post</a>
      </div>
    </div>
    <a href="/signout" class="w3-bar-item w3-button w3-hide-small w3-right w3-padding-large w3-mobile w3-hover-white"
      title="Sign out"><em class="fa fa-sign-out"></em> Sign Out</a>

    <a href="javascript:void(0);" class="w3-bar-item w3-button w3-hide-medium w3-right w3-hide-large" onclick="openNavBar()"><em class="fa fa-bars"></em></a>


  </div>
</div>


<!-- Navbar on small screens -->
<div id="navDemo" class="w3-bar-block w3-theme-d2 w3-hide w3-hide-large w3-hide-medium w3-large">
  <br><br>
  <a href="/allMembers" class="w3-bar-item w3-mobile w3-button w3-padding-large">All Members</a>
  <a href="/signout" class="w3-bar-item w3-mobile w3-button w3-padding-large">Sign out</a>

  {{-- <a href="/organogram?id={{ $data['id'] }}" class="w3-bar-item w3-button w3-padding-large"
    title="Account Settings"><i class="fa fa-tree"></i>Family Tree</a>

  <a href="/setting?id={{ $data['id'] }}" class="w3-bar-item w3-button w3-padding-large" title="Account Settings"><i
      class="fa fa-cog"></i>Account Setting</a> --}}

</div>


<script>
  const openNavBar = () => {
    const x = document.getElementById("navDemo");
    if(x.className.indexOf("w3-show") == -1){
      x.className += " w3-show";
    }else{
      x.className = x.className.replace(" w3-show", "");
    }
  }


</script>
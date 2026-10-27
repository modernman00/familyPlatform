<!-- Updated Navbar HTML -->
<style>
  /* Add to your CSS */
@media (max-width: 992px) {
  #navDemo {
    display: none;
    position: absolute;
    width: 80%;
    z-index: 1000;
  }
  #navDemo.w3-show {
    display: block;
  }


}
</style>
<div class="w3-top w3-white">
  <div class="w3-bar w3-left-align w3-medium navbar-container">
    <!-- Logo -->
    <a href="#" class="w3-bar-item w3-button w3-padding-large w3-hover-none">
      <img src="{{ getenv('IMG_CONTRACT') }}" alt="logo" class="nav-logo">
    </a>

     <!-- Main Navigation -->
    <a href="/allMembers" class="w3-bar-item w3-button nav-link allMemberNav w3-mobile w3-hide-small" title="{{ __('nav.all_members', 'All Members') }}">
      <i class="fa fa-users"></i>
      <span class="nav-link-text">{{ __('nav.all_members', 'All Members') }}</span>
    </a>

        <a href="/profilePage" class="w3-hide-small w3-mobile w3-bar-item w3-button nav-link profilePageNav" title="{{ __('nav.my_page', 'My Page') }}">
      <i class="fa fa-user"></i>
      <span class="nav-link-text">{{ __('nav.my_page', 'My Page') }}</span>
    </a>

    @isset($data)
      <a href="/organogram?id={{ $data['id'] }}&mode=canvas" class="w3-bar-item w3-button nav-link familyTreeNav w3-hide-small w3-mobile" title="{{ __('nav.tree', 'Family Tree') }}">
        <i class="fa fa-tree"></i>
        <span class="nav-link-text">{{ __('nav.tree', 'Family Tree') }}</span>
      </a>

      @if (isset($data['mobile']) && !empty($data['email']) && !empty($data['country']) && !empty($data['famCode']))
        <a href="/accountSetting/{{ urlencode(trim($data['id'])) }}/{{ urlencode(trim($data['mobile'])) }}/{{ trim($data['email']) }}/{{ str_replace(' ', '', trim($data['country'])) }}/{{ urlencode(trim($data['famCode'])) }}" 
           class="w3-bar-item w3-button nav-link w3-hide-small w3-mobile" title="{{ __('nav.settings', 'Settings') }}">
          <i class="fa fa-cog"></i>
          <span class="nav-link-text">{{ __('nav.settings', 'Settings') }}</span>
        </a>
      @endif
    @endisset

    <!-- Notifications -->
    <div class="w3-dropdown-hover">
      <button class="w3-button notification_count" title="{{ __('nav.notifications', 'Notifications') }}">
        <i class="fa fa-bell"></i>
        <span class="w3-badge w3-green" id="notification_count"></span>
      </button>
      <div class="w3-dropdown-content w3-card-4 w3-bar-block notification_tab" id="notification_tab" style="width:300px">
        <!-- Notification content will be inserted here -->
      </div>
    </div>

    <!-- Language Switcher Dropdown -->
    <div class="w3-dropdown-hover w3-right">
      @php
        $activeLocale = \App\classes\I18n::getLocale();
        $supportedLocales = \App\classes\I18n::getSupportedLocales();
        $currLocale = $supportedLocales[$activeLocale] ?? $supportedLocales['en'];
      @endphp
      <button class="w3-button" title="{{ __('nav.change_language', 'Change Language') }}" style="font-size: 0.9rem;">
        <span>{{ $currLocale['flag'] }}</span> <span class="w3-hide-small">{{ $currLocale['name'] }}</span> <i class="fa fa-caret-down"></i>
      </button>
      <div class="w3-dropdown-content w3-card-4 w3-bar-block" style="min-width: 140px; right: 0;">
        @foreach ($supportedLocales as $locKey => $locMeta)
          <a href="/locale/{{ $locKey }}" class="w3-bar-item w3-button {{ $locKey === $activeLocale ? 'w3-light-grey fw-bold' : '' }}">
            <span class="me-1">{{ $locMeta['flag'] }}</span> {{ $locMeta['native'] }}
          </a>
        @endforeach
      </div>
    </div>

    <!-- Sign Out -->
    <a href="signout/login" class="w3-bar-item w3-button sign-out-btn w3-right" title="{{ __('nav.logout', 'Sign Out') }}">
      <i class="fa fa-sign-out-alt"></i>
      <span class="w3-hide-small">{{ __('nav.logout', 'Sign Out') }}</span>
    </a>

    <!-- Mobile menu button -->
    <a href="javascript:void(0);" class="w3-bar-item w3-button w3-right w3-hide-large w3-hide-medium" onclick="openNavBar()">
      <i class="fa fa-bars"></i>
    </a>
  </div>
</div>



<!-- Mobile Menu - Simplified Version -->
<div id="navDemo" class="w3-bar-block w3-white w3-hide w3-hide-large w3-hide-medium w3-card-4">
  <a href="/allMembers" class="w3-bar-item w3-button w3-padding-16">
    <i class="fa fa-users w3-margin-right"></i>{{ __('nav.all_members', 'All Members') }}
  </a>
  <a href="/member/ProfilePage" class="w3-bar-item w3-button w3-padding-16">
    <i class="fa fa-user w3-margin-right"></i>{{ __('nav.my_page', 'My Page') }}
  </a>
  
  @isset($data)
    <a href="/organogram?id={{ $data['id'] }}&mode=canvas" class="w3-bar-item w3-button w3-padding-16">
      <i class="fa fa-tree w3-margin-right"></i>{{ __('nav.tree', 'Family Tree') }}
    </a>
    
    @if (isset($data['mobile']) && !empty($data['email']) && !empty($data['country']) && !empty($data['famCode']))
      <a href="/accountSetting/{{ urlencode(trim($data['id'])) }}/{{ urlencode(trim($data['mobile'])) }}/{{ trim($data['email']) }}/{{ str_replace(' ', '', trim($data['country'])) }}/{{ urlencode(trim($data['famCode'])) }}" 
         class="w3-bar-item w3-button w3-padding-16">
         <i class="fa fa-cog w3-margin-right"></i>{{ __('nav.settings', 'Account Settings') }}
      </a>
    @endif
  @endisset
  
  <a href="/signout" class="w3-bar-item w3-button w3-padding-16">
    <i class="fas fa-sign-out-alt w3-margin-right"></i>{{ __('nav.logout', 'Sign Out') }}
  </a>
</div>

<script defer>
  const openNavBar = () => {
    const x = document.getElementById("navDemo");
    if(x.className.indexOf("w3-show") == -1){
      x.className += " w3-show";
    }else{
      x.className = x.className.replace(" w3-show", "");
    }
  }





</script>

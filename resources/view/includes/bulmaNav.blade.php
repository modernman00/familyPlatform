<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="navbar-brand">
    <a class="navbar-item" href="/">
      <img src={{ getenv("IMG_CONTRACT") }} alt="logo" >
    </a>

    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>

  <div id="navbarBasicExample" class="navbar-menu">
    <div class="navbar-start">
      <a href="/" class="navbar-item">
        Home
      </a>

      <a href="/aboutus" class="navbar-item">
        About us
      </a>

      {{--  <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link">
          More
        </a>

        <div class="navbar-dropdown">
          <a class="navbar-item">
            About
          </a>
          <a class="navbar-item">
            Jobs
          </a>
          <a class="navbar-item">
            Contact
          </a>
          <hr class="navbar-divider">
          <a class="navbar-item">
            Report an issue
          </a>
        </div>
      </div>  --}}
  </div>

    <div class="navbar-end">
      <div class="navbar-item">
        <div class="buttons signup_login">
          <a href="/register" class="button is-primary signUp">
            <strong>Sign up</strong>
          </a>
          <a href="/login" class="button is-info login">
            Log in
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>


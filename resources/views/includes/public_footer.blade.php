<footer class="public-footer">
  <div class="container">
    <div class="row">
      <div class="col-lg-4 mb-4 mb-lg-0">
        <a class="navbar-brand d-flex align-items-center mb-3" href="/">
          <img src="{{ getenv('APP_LOGO') }}" alt="Logo" style="height: 30px;" class="me-2">
          <span class="fs-5" style="font-weight: 800; letter-spacing: -0.02em;"><span style="color: #2a0a0d;">Family</span><span style="color: var(--brand-primary);">Platform</span></span>
        </a>
        <p class="text-muted pe-lg-5">The ultimate social media site for families to connect, strengthen bonds, and share memories safely.</p>
      </div>
      <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
        <h4 class="footer-heading">Platform</h4>
        <a href="/why-familyplatform" class="footer-link fw-semibold" style="color: var(--brand-primary, #a31621);"><i class="fas fa-shield-alt me-1"></i>Why Us vs WhatsApp</a>
        <a href="/playbook" class="footer-link fw-semibold" style="color: var(--brand-primary, #a31621);"><i class="fas fa-book-open me-1"></i>Interactive Playbook</a>
        <a href="/how-to-use" class="footer-link">How to Use Guide</a>
        <a href="/blogs" class="footer-link"><i class="fas fa-newspaper me-1 text-primary"></i>Blog &amp; Stories</a>
        <a href="/register" class="footer-link">Register</a>
        <a href="/login" class="footer-link">Log In</a>
        <a href="/aboutus" class="footer-link">About Us</a>
      </div>
      <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
        <h4 class="footer-heading">Legal</h4>
        <a href="/privacy" class="footer-link">Privacy Policy</a>
        <a href="/terms" class="footer-link">Terms of Use</a>
      </div>
      <div class="col-lg-2 col-md-4">
        <h4 class="footer-heading">Support</h4>
        <a href="/contact" class="footer-link">Contact Us</a>
        <a href="/help" class="footer-link">Help Centre</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2023-{{ date('Y') }} {{ getenv('APP_NAME') }}. All rights reserved.</p>
    </div>
  </div>
</footer>

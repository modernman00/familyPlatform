@extends('layouts.landing_layout')

@section('title', 'Terms of Use')
@section('data-page-id', 'terms')

@section('content')
  <!-- Header Banner -->
  <header class="doc-center-header">
    <div class="container">
      <h1>Terms of Use</h1>
      <p class="fs-5 text-white-50">Last updated: {{ date('F Y') }}</p>
    </div>
  </header>

  <!-- Content Container -->
  <main class="container">
    <div class="doc-center-content">
      
      <p class="lead fw-bold text-dark">These terms and conditions ("Terms") govern your access to and use of our platform, including any subdomains, features, content, and services offered by Our Family Network ("we," "our," or "us").</p>
      
      <p>By accessing or using the Website, you agree to be bound by these Terms. If you do not agree with these Terms, you must not use the Website.</p>

      <hr class="my-5">

      <h2>1. Use of the Website</h2>
      <p><strong>a. Age Restrictions:</strong> The Website is intended for users who are 18 years of age or older. By using the Website, you represent and warrant that you are at least 18 years old. If you are under 18, you may only use the Website with the consent and supervision of a parent or legal guardian.</p>
      
      <p><strong>b. Account Creation:</strong> Some features of the Website may require you to create an account. You are responsible for providing accurate and complete information during the account registration process. You must maintain the confidentiality of your account credentials and are solely responsible for all activities that occur under your account.</p>

      <p><strong>c. Prohibited Activities:</strong> You agree not to:</p>
      <ul>
        <li>Use the Website for any illegal or unauthorized purpose.</li>
        <li>Violate any applicable laws, rules, or regulations.</li>
        <li>Interfere with or disrupt the Website or servers or networks connected to the Website.</li>
        <li>Engage in any conduct that could harm the Website's reputation.</li>
      </ul>

      <h2>2. Intellectual Property</h2>
      <p><strong>a. Content Ownership:</strong> The Website and its contents, including but not limited to text, images, graphics, logos, and software, are the property of Our Family Network and are protected by copyright and other intellectual property laws.</p>
      <p><strong>b. Limited License:</strong> We grant you a limited, non-exclusive, and non-transferable license to access and use the Website for personal and non-commercial purposes only. You may not reproduce, modify, distribute, or otherwise use the Website or its contents without our prior written consent.</p>

      <h2>3. Privacy Policy</h2>
      <p>Your use of the Website is subject to our <a href="/privacy">Privacy Policy</a>, which explains how we collect, use, and disclose information about you. By using the Website, you consent to the practices described in the Privacy Policy.</p>

      <h2>4. Disclaimer of Warranties</h2>
      <p>The Website is provided "as is" and without warranties of any kind, whether express or implied. We do not warrant that the Website will be error-free, secure, or uninterrupted.</p>

      <h2>5. Limitation of Liability</h2>
      <p>In no event shall Our Family Network be liable for any indirect, consequential, or incidental damages arising out of or related to the use of the Website.</p>

      <h2>6. Changes to the Terms</h2>
      <p>We may update these Terms from time to time, and any changes will be effective upon posting the revised Terms on the Website. Your continued use of the Website after such changes will constitute your acceptance of the revised Terms.</p>

      <h2>7. Governing Law and Jurisdiction</h2>
      <p>These Terms shall be governed by and construed in accordance with the laws of the United Kingdom. Any disputes arising under or in connection with these Terms shall be subject to the exclusive jurisdiction of the courts in the United Kingdom.</p>

      <h2>8. Contact Us</h2>
      <p>If you have any questions or concerns regarding these Terms, please contact us at <a href="mailto:enquiries@myfamilyplatform.com">enquiries@myfamilyplatform.com</a>.</p>

    </div>
  </main>
@endsection
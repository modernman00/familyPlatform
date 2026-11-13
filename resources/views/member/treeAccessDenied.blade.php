@extends('layouts.profileBase')

@section('title', 'Family tree restricted — FamilyPlatform')

@section('content')
<main class="container py-5" style="min-height:50vh;">
  <div class="mx-auto text-center border rounded-4 p-4 p-md-5" style="max-width:560px;">
    <h1 class="h4 fw-bold mb-3">Family tree restricted</h1>
    <p class="mb-4" id="treeAccessDeniedMessage">{{ $message ?? 'You do not have access to this family tree.' }}</p>
    <a href="{{ $backUrl ?? '/profilePage' }}" class="btn btn-primary px-4 d-inline-flex align-items-center justify-content-center" style="min-height:44px; white-space:nowrap;">Go back</a>
  </div>
</main>
@include('component.swalAlert')
@endsection

@push('scripts')
<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function () {
  var backUrl = @json($backUrl ?? '/profilePage');
  var message = @json($message ?? 'You do not have access to this family tree.');
  function show() {
    if (typeof window.swalError === 'function') {
      window.swalError(message, true, 'Family tree restricted').then(function () {
        window.location.href = backUrl;
      });
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(show, 50); });
  } else {
    setTimeout(show, 50);
  }
})();
</script>
@endpush

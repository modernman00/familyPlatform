@extends('layouts.profileBase')
@section('title', 'PROFILE_PAGE')
@section('data-page-id', 'profilePage')
@push('styles')
    <link rel="stylesheet" href="/public/css/profilepage.css">
@endpush
@section('content')


    {{-- @php

    $url = getenv("APP_URL");
    $url = $url;
    loggedDetection($url, $data['email']);


    @endphp --}}
    <div class="container-main" style="min-height: 100vh;">

        @includeIf('member.includes.leftColumn')

        @includeIf('member.includes.middleColumn')

        @includeIf('member.includes.rightColumn')

        @includeIf('member.modals.post')
    
        @includeIf('member.modals.share')
        @includeIf('member.modals.createEvent')
        @includeIf('member.modals.shareModal2')


    </div>

    <script>
        // Defensive frontend: Check authentication status silently if needed without page refresh
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                // We can silently check session status via api without blocking the UI
                console.log('Page visible, auth checking delegated to API calls');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('img'); // Hidden file input
            const previewImg = document.getElementById('profilePreview'); // Current avatar image

            if (!fileInput || !previewImg) return; // Defensive check

            fileInput.addEventListener('change', function () {
                const file = this.files[0]; // Get the first selected file
                if (!file) return;

                const reader = new FileReader(); // Create a FileReader to read the image
                reader.onload = function (e) {
                    previewImg.src = e.target.result; // Update the image src with base64 data
                };
                reader.readAsDataURL(file); // Read the file as a data URL
            });
        });

        window.appUrl = "{{ env('MIX_APP_URL') }}";

        //        // Image Modal functionality
        // function openModal(src) {
        //     document.getElementById('imageModal').style.display = 'flex';
        //     document.getElementById('modalImage').src = src;
        //     document.body.style.overflow = 'hidden';
        // }

        // function closeModal() {
        //     document.getElementById('imageModal').style.display = 'none';
        //     document.body.style.overflow = 'auto';
        // }

        // // Close modal when clicking outside the image
        // window.onclick = function(event) {
        //     const modal = document.getElementById('imageModal');
        //     if (event.target === modal) {
        //         closeModal();
        //     }
        // }

    </script>



@endsection
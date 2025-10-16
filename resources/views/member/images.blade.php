@extends('layouts.profileBase')
@section('title', 'images')
@section('data-page-id', 'images')
@push('styles')

    <link rel="stylesheet" href="{{ route('public/css/images.css') }}"
    >
@endpush
@section('content')
<div class="container">

    {{-- upload form --}}
    {{-- <section class="upload-form upload-section" id="upload">

        <h2>Share Your Memory </h2>

        <p id="memoryUploadForm_notification"></p>

        <form id="memoryUploadForm" enctype="multipart/form-data" method="POST" action="/memory">

            <div class="upload-row">
                <input type="file" id="memoryImages" name="memory[]" accept="image/*" multiple required>
                <input type="text" name="caption" placeholder="Caption or Contributor Name" required>
                <button type="button" id="submitMemoryBtn">Upload 📤</button>
        
            </div>

            <p class="note"><i>Maximum five pictures and not more than 1mb</i></p>

        </form>
    </section> --}}

    <!-- Slideshow Container -->
    <section class="photo-grid" id="photoGrid">

        @foreach ($images as $image)
        <div class="grid-item">

            <img src="/resources/images/{{ $image['img'] }}" alt={{ $image['img'] }} data-id="{{ $image['id'] }}">

            <div class="img-meta">
                <p class="description">{{ $image['caption'] }}</p>
                
                <button class="likeBtn" id="likeBtn{{ $image['id'] }}">❤️  {{ $image['likes'] }}</button>
                <div class="timestamp">{{ humanTiming($image['created_at']) }} ago</div>
            </div>

        </div>

        @endforeach


    </section>

    <!-- Modal Viewer -->



    <!-- Pagination -->
    <section class="pagination">
        @for ($i = 1; $i <= $totalPages; $i++) <a href="?page={{ $i }}" class="@php $i == $page ? 'active' : '' @endphp">
            {{ $i }}
            </a>
            @endfor
    </section>


</div>

@endsection